<?php
/**
 * ============================================
 * CONVERSATION LOGGER - Log de Conversas IA
 * ============================================
 * 
 * Biblioteca para registrar conversas dos agentes de IA:
 * - SDR (pré-venda): captura IP, atualiza com dados do cliente após checkout
 * - Onboarding (pós-venda): associa ao pedido/projeto do cliente
 * 
 * Tabelas:
 * - {PREFIX}sdr_conversations / {PREFIX}sdr_messages
 * - {PREFIX}onboarding_conversations / {PREFIX}onboarding_messages
 */

// Prevenir acesso direto
if (!defined('DB_CONFIG_ACCESS') && !defined('SECURE_CONFIG_ACCESS')) {
    http_response_code(403);
    die('Acesso negado.');
}

require_once __DIR__ . '/database.php';

// ============================================
// FUNÇÕES AUXILIARES
// ============================================

/**
 * Obtém o IP real do cliente (tratando proxies/CloudFlare)
 */
function get_client_ip(): string {
    $headers = [
        'HTTP_CF_CONNECTING_IP',     // CloudFlare
        'HTTP_X_FORWARDED_FOR',      // Proxy padrão
        'HTTP_X_REAL_IP',            // Nginx
        'HTTP_CLIENT_IP',            // Alternativo
        'REMOTE_ADDR'                // Direto
    ];
    
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            // X-Forwarded-For pode conter múltiplos IPs (client, proxy1, proxy2)
            $ip = explode(',', $_SERVER[$header])[0];
            $ip = trim($ip);
            
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Gera um UUID v4 para identificar conversas
 */
function generate_conversation_id(): string {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // version 4
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variant
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// ============================================
// SDR CONVERSATIONS
// ============================================

/**
 * Cria ou obtém uma conversa SDR
 * 
 * @param string|null $conversationId ID existente ou null para criar nova
 * @return array ['id' => int, 'conversation_id' => string] ou false
 */
function sdr_get_or_create_conversation($conversationId = null) {
    $pdo = get_db_connection();
    if (!$pdo) return false;
    
    $tableName = DB_PREFIX . 'sdr_conversations';
    
    // Se tem conversation_id, buscar existente
    if ($conversationId) {
        $stmt = $pdo->prepare("SELECT id, conversation_id FROM `{$tableName}` WHERE conversation_id = ?");
        $stmt->execute([$conversationId]);
        $row = $stmt->fetch();
        
        if ($row) {
            return $row;
        }
    }
    
    // Criar nova conversa
    $newId = generate_conversation_id();
    $ip = get_client_ip();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO `{$tableName}` 
            (conversation_id, ip_address, user_agent, started_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$newId, $ip, substr($userAgent, 0, 500)]);
        
        return [
            'id' => (int)$pdo->lastInsertId(),
            'conversation_id' => $newId
        ];
    } catch (PDOException $e) {
        error_log("❌ [conversation-logger] Erro ao criar conversa SDR: " . $e->getMessage());
        return false;
    }
}

/**
 * Registra uma mensagem na conversa SDR
 * 
 * @param int $conversationDbId ID interno da conversa (PK)
 * @param string $role 'user' ou 'assistant'
 * @param string $content Conteúdo da mensagem
 * @param string|null $stage Stage atual do SDR
 * @param array|null $metadata Dados extras (pricing, clientData, etc)
 */
function sdr_log_message(int $conversationDbId, string $role, string $content, ?string $stage = null, ?array $metadata = null): bool {
    $pdo = get_db_connection();
    if (!$pdo) return false;
    
    $tableName = DB_PREFIX . 'sdr_messages';
    $convTableName = DB_PREFIX . 'sdr_conversations';
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO `{$tableName}` 
            (conversation_id, role, content, stage, metadata, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $conversationDbId,
            $role,
            $content,
            $stage,
            $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null
        ]);
        
        // Atualizar contador e timestamp da conversa
        $pdo->prepare("
            UPDATE `{$convTableName}` 
            SET total_messages = total_messages + 1, updated_at = NOW()
            WHERE id = ?
        ")->execute([$conversationDbId]);
        
        return true;
    } catch (PDOException $e) {
        error_log("❌ [conversation-logger] Erro ao logar mensagem SDR: " . $e->getMessage());
        return false;
    }
}

/**
 * Atualiza a conversa SDR com dados do estágio/IA
 * 
 * @param int $conversationDbId ID interno da conversa
 * @param array $data Dados a atualizar (last_stage, client_data, suggested_plan, finished)
 */
function sdr_update_conversation(int $conversationDbId, array $data): bool {
    $pdo = get_db_connection();
    if (!$pdo) return false;
    
    $tableName = DB_PREFIX . 'sdr_conversations';
    $sets = [];
    $params = [];
    
    if (isset($data['last_stage'])) {
        $sets[] = 'last_stage = ?';
        $params[] = $data['last_stage'];
    }
    if (isset($data['client_data'])) {
        $sets[] = 'client_data = ?';
        $params[] = json_encode($data['client_data'], JSON_UNESCAPED_UNICODE);
    }
    if (isset($data['suggested_plan'])) {
        $sets[] = 'suggested_plan = ?';
        $params[] = json_encode($data['suggested_plan'], JSON_UNESCAPED_UNICODE);
    }
    if (isset($data['finished'])) {
        $sets[] = 'finished = ?';
        $params[] = $data['finished'] ? 1 : 0;
    }
    
    if (empty($sets)) return true;
    
    $sets[] = 'updated_at = NOW()';
    $params[] = $conversationDbId;
    
    try {
        $sql = "UPDATE `{$tableName}` SET " . implode(', ', $sets) . " WHERE id = ?";
        $pdo->prepare($sql)->execute($params);
        return true;
    } catch (PDOException $e) {
        error_log("❌ [conversation-logger] Erro ao atualizar conversa SDR: " . $e->getMessage());
        return false;
    }
}

/**
 * Atualiza a conversa SDR com os dados do cliente (após checkout)
 * 
 * @param string $conversationId UUID da conversa
 * @param string $customerName Nome do cliente
 * @param string $customerEmail Email do cliente
 * @param string|null $customerPhone Telefone
 * @param int|null $orderId ID do pedido criado
 */
function sdr_update_client_data(string $conversationId, string $customerName, string $customerEmail, ?string $customerPhone = null, ?int $orderId = null): bool {
    $pdo = get_db_connection();
    if (!$pdo) return false;
    
    $tableName = DB_PREFIX . 'sdr_conversations';
    
    try {
        $stmt = $pdo->prepare("
            UPDATE `{$tableName}` 
            SET customer_name = ?, customer_email = ?, customer_phone = ?, order_id = ?, updated_at = NOW()
            WHERE conversation_id = ?
        ");
        $stmt->execute([$customerName, $customerEmail, $customerPhone, $orderId, $conversationId]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("❌ [conversation-logger] Erro ao atualizar dados do cliente SDR: " . $e->getMessage());
        return false;
    }
}

// ============================================
// ONBOARDING CONVERSATIONS
// ============================================

/**
 * Cria ou obtém uma conversa de Onboarding
 * 
 * @param string|null $conversationId ID existente ou null para criar nova
 * @param array $orderData Dados do pedido (order_id, token, customer_name, email, plan_name, purchased_pages)
 * @return array ['id' => int, 'conversation_id' => string] ou false
 */
function onboarding_get_or_create_conversation($conversationId = null, array $orderData = []) {
    $pdo = get_db_connection();
    if (!$pdo) return false;
    
    $tableName = DB_PREFIX . 'onboarding_conversations';
    
    // Se tem conversation_id, buscar existente
    if ($conversationId) {
        $stmt = $pdo->prepare("SELECT id, conversation_id FROM `{$tableName}` WHERE conversation_id = ?");
        $stmt->execute([$conversationId]);
        $row = $stmt->fetch();
        
        if ($row) {
            return $row;
        }
    }
    
    // Criar nova conversa com dados do pedido
    $newId = generate_conversation_id();
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO `{$tableName}` 
            (conversation_id, order_id, onboarding_token, customer_name, customer_email, 
             plan_name, purchased_pages, started_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $newId,
            $orderData['order_id'] ?? null,
            $orderData['onboarding_token'] ?? null,
            $orderData['customer_name'] ?? null,
            $orderData['customer_email'] ?? null,
            $orderData['plan_name'] ?? null,
            isset($orderData['purchased_pages']) ? json_encode($orderData['purchased_pages'], JSON_UNESCAPED_UNICODE) : null
        ]);
        
        return [
            'id' => (int)$pdo->lastInsertId(),
            'conversation_id' => $newId
        ];
    } catch (PDOException $e) {
        error_log("❌ [conversation-logger] Erro ao criar conversa onboarding: " . $e->getMessage());
        return false;
    }
}

/**
 * Registra uma mensagem na conversa de Onboarding
 * 
 * @param int $conversationDbId ID interno da conversa (PK)
 * @param string $role 'user' ou 'assistant'
 * @param string $content Conteúdo da mensagem
 * @param array|null $metadata Dados extras
 */
function onboarding_log_message(int $conversationDbId, string $role, string $content, ?array $metadata = null): bool {
    $pdo = get_db_connection();
    if (!$pdo) return false;
    
    $tableName = DB_PREFIX . 'onboarding_messages';
    $convTableName = DB_PREFIX . 'onboarding_conversations';
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO `{$tableName}` 
            (conversation_id, role, content, metadata, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $conversationDbId,
            $role,
            $content,
            $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null
        ]);
        
        // Atualizar contador e timestamp da conversa
        $pdo->prepare("
            UPDATE `{$convTableName}` 
            SET total_messages = total_messages + 1, updated_at = NOW()
            WHERE id = ?
        ")->execute([$conversationDbId]);
        
        return true;
    } catch (PDOException $e) {
        error_log("❌ [conversation-logger] Erro ao logar mensagem onboarding: " . $e->getMessage());
        return false;
    }
}

/**
 * Atualiza a conversa de Onboarding (finished, extracted_data)
 * 
 * @param int $conversationDbId ID interno da conversa
 * @param array $data Dados a atualizar
 */
function onboarding_update_conversation(int $conversationDbId, array $data): bool {
    $pdo = get_db_connection();
    if (!$pdo) return false;
    
    $tableName = DB_PREFIX . 'onboarding_conversations';
    $sets = [];
    $params = [];
    
    if (isset($data['finished'])) {
        $sets[] = 'finished = ?';
        $params[] = $data['finished'] ? 1 : 0;
    }
    if (isset($data['extracted_data'])) {
        $sets[] = 'extracted_data = ?';
        $params[] = json_encode($data['extracted_data'], JSON_UNESCAPED_UNICODE);
    }
    
    if (empty($sets)) return true;
    
    $sets[] = 'updated_at = NOW()';
    $params[] = $conversationDbId;
    
    try {
        $sql = "UPDATE `{$tableName}` SET " . implode(', ', $sets) . " WHERE id = ?";
        $pdo->prepare($sql)->execute($params);
        return true;
    } catch (PDOException $e) {
        error_log("❌ [conversation-logger] Erro ao atualizar conversa onboarding: " . $e->getMessage());
        return false;
    }
}
