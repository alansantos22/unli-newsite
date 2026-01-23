<?php
/**
 * ============================================
 * DATABASE HELPER - Gerenciamento do MySQL
 * ============================================
 * 
 * Funções para conexão e operações no banco de dados
 */

// Proteção contra acesso direto
if (!defined('DB_CONFIG_ACCESS')) {
    define('DB_CONFIG_ACCESS', true);
}

// Tentar carregar configuração do banco de dados (opcional)
$db_config_path = __DIR__ . '/../db.config.php';
if (file_exists($db_config_path)) {
    require_once $db_config_path;
    define('DB_AVAILABLE', true);
} else {
    // DB não configurado - modo somente arquivo
    define('DB_AVAILABLE', false);
    error_log("⚠️ [database] db.config.php não encontrado - operando sem banco de dados");
}

/**
 * Obtém conexão PDO com o banco de dados
 * 
 * @return PDO|null Instância da conexão ou null se não configurado
 * @throws PDOException se falhar
 */
function get_db_connection() {
    static $pdo = null;
    static $attempted = false;
    
    // Se DB não está disponível, retornar null
    if (!defined('DB_AVAILABLE') || !DB_AVAILABLE) {
        return null;
    }
    
    if ($pdo !== null) {
        return $pdo;
    }
    
    if ($attempted) {
        return null;
    }
    
    $attempted = true;
    
    try {
        // Verificar se as constantes básicas existem
        if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
            error_log("⚠️ [database] Constantes de conexão não definidas");
            return null;
        }
        
        $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
        $collate = defined('DB_COLLATE') ? DB_COLLATE : 'utf8mb4_unicode_ci';
        
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            $charset
        );
        
        if (defined('DB_PORT') && DB_PORT) {
            $dsn .= ';port=' . DB_PORT;
        }
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . $charset . " COLLATE " . $collate
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        
        return $pdo;
        
    } catch (PDOException $e) {
        error_log("⚠️ [database] Erro ao conectar: " . $e->getMessage());
        return null;
    }
}

/**
 * Salva um pedido no banco de dados (status inicial: pending)
 * 
 * @param array $orderData Dados do pedido
 * @return array|false ['id' => int, 'token' => string] ou false se não disponível/erro
 */
function save_order_to_db($orderData) {
    try {
        $pdo = get_db_connection();
        
        // Se DB não está disponível, retornar false silenciosamente
        if ($pdo === null) {
            error_log("⚠️ [database] Banco de dados não disponível - pedido salvo apenas em arquivo");
            return false;
        }
        
        // Gerar token único para onboarding
        $token = bin2hex(random_bytes(32));
        
        // Preparar dados para inserção
        $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
        $sql = "INSERT INTO " . $prefix . "orders (
            customer_name,
            email,
            phone,
            order_details,
            total_amount,
            payment_status,
            payment_method,
            payment_id,
            onboarding_token,
            onboarding_status
        ) VALUES (
            :customer_name,
            :email,
            :phone,
            :order_details,
            :total_amount,
            :payment_status,
            :payment_method,
            :payment_id,
            :onboarding_token,
            :onboarding_status
        )";
        
        $stmt = $pdo->prepare($sql);
        
        // Preparar order_details como JSON
        $orderDetailsJson = json_encode([
            'order_id' => $orderData['order_id'] ?? '',
            'product' => $orderData['selection']['product'] ?? '',
            'pages' => $orderData['selection']['pages'] ?? 0,
            'content' => $orderData['selection']['content'] ?? '',
            'pricing' => $orderData['pricing'] ?? [],
            'selection' => $orderData['selection'] ?? []
        ], JSON_UNESCAPED_UNICODE);
        
        // Executar inserção
        $stmt->execute([
            ':customer_name' => $orderData['briefing']['company_name'] ?? 'Cliente',
            ':email' => $orderData['briefing']['email'] ?? '',
            ':phone' => $orderData['briefing']['whatsapp'] ?? null,
            ':order_details' => $orderDetailsJson,
            ':total_amount' => $orderData['pricing']['final'] ?? 0,
            ':payment_status' => 'pending',
            ':payment_method' => $orderData['payment_method'] ?? 'avista',
            ':payment_id' => null,
            ':onboarding_token' => $token,
            ':onboarding_status' => 'pendente'
        ]);
        
        return [
            'id' => $pdo->lastInsertId(),
            'token' => $token
        ];
        
    } catch (PDOException $e) {
        error_log("Error saving order to database: " . $e->getMessage());
        return false;
    }
}

/**
 * Atualiza o status de pagamento de um pedido
 * 
 * @param string $orderId ID do pedido (ORD-...)
 * @param string $status Status do pagamento (pending, paid, failed, refunded)
 * @param string|null $paymentId ID da transação no gateway
 * @return bool Sucesso da operação
 */
function update_payment_status($orderId, $status, $paymentId = null) {
    try {
        $pdo = get_db_connection();
        
        if ($pdo === null) {
            return false;
        }
        
        $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
        $sql = "UPDATE " . $prefix . "orders 
                SET payment_status = :status,
                    payment_id = :payment_id,
                    updated_at = CURRENT_TIMESTAMP
                WHERE JSON_EXTRACT(order_details, '$.order_id') = :order_id";
        
        $stmt = $pdo->prepare($sql);
        
        return $stmt->execute([
            ':status' => $status,
            ':payment_id' => $paymentId,
            ':order_id' => $orderId
        ]);
        
    } catch (PDOException $e) {
        error_log("Error updating payment status: " . $e->getMessage());
        return false;
    }
}

/**
 * Busca pedido por order_id
 * 
 * @param string $orderId ID do pedido
 * @return array|false Dados do pedido ou false
 */
function get_order_by_id($orderId) {
    try {
        $pdo = get_db_connection();
        
        if ($pdo === null) {
            return false;
        }
        
        $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
        $sql = "SELECT * FROM " . $prefix . "orders 
                WHERE JSON_EXTRACT(order_details, '$.order_id') = :order_id
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);
        
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("Error fetching order: " . $e->getMessage());
        return false;
    }
}

/**
 * Busca pedido por token de onboarding
 * 
 * @param string $token Token único
 * @return array|false Dados do pedido ou false
 */
function get_order_by_token($token) {
    try {
        $pdo = get_db_connection();
        
        if ($pdo === null) {
            return false;
        }
        
        $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
        $sql = "SELECT * FROM " . $prefix . "orders 
                WHERE onboarding_token = :token
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':token' => $token]);
        
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("Error fetching order by token: " . $e->getMessage());
        return false;
    }
}

/**
 * Atualiza o status do onboarding
 * 
 * @param string $token Token de onboarding
 * @param string $status Status (pendente, preenchendo, concluido)
 * @param array|null $briefingData Dados do briefing (opcional)
 * @return bool Sucesso da operação
 */
function update_onboarding_status($token, $status, $briefingData = null) {
    try {
        $pdo = get_db_connection();
        
        if ($pdo === null) {
            return false;
        }
        
        $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
        $sql = "UPDATE " . $prefix . "orders 
                SET onboarding_status = :status,
                    updated_at = CURRENT_TIMESTAMP";
        
        $params = [':status' => $status, ':token' => $token];
        
        if ($briefingData !== null) {
            $sql .= ", briefing_data = :briefing_data";
            $params[':briefing_data'] = json_encode($briefingData, JSON_UNESCAPED_UNICODE);
        }
        
        if ($status === 'concluido') {
            $sql .= ", completed_at = CURRENT_TIMESTAMP";
        }
        
        $sql .= " WHERE onboarding_token = :token";
        
        $stmt = $pdo->prepare($sql);
        
        return $stmt->execute($params);
        
    } catch (PDOException $e) {
        error_log("Error updating onboarding status: " . $e->getMessage());
        return false;
    }
}

/**
 * Lista pedidos do banco de dados com filtros
 * 
 * @param array $filters Filtros (payment_status, onboarding_status, etc.)
 * @param int $limit Limite de resultados
 * @param int $offset Offset para paginação
 * @return array Lista de pedidos
 */
function list_orders_from_db($filters = [], $limit = 20, $offset = 0) {
    try {
        $pdo = get_db_connection();
        
        if ($pdo === null) {
            return [];
        }
        
        $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
        $sql = "SELECT * FROM " . $prefix . "orders WHERE 1=1";
        $params = [];
        
        if (!empty($filters['payment_status'])) {
            $sql .= " AND payment_status = :payment_status";
            $params[':payment_status'] = $filters['payment_status'];
        }
        
        if (!empty($filters['onboarding_status'])) {
            $sql .= " AND onboarding_status = :onboarding_status";
            $params[':onboarding_status'] = $filters['onboarding_status'];
        }
        
        if (!empty($filters['email'])) {
            $sql .= " AND email LIKE :email";
            $params[':email'] = '%' . $filters['email'] . '%';
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $pdo->prepare($sql);
        
        // Bind dos parâmetros
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error listing orders: " . $e->getMessage());
        return [];
    }
}
