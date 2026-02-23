<?php
/**
 * ============================================
 * WEBHOOK PAGAR.ME - Notificações de Pagamento
 * ============================================
 * 
 * Endpoint para receber webhooks do Pagar.me.
 * O Pagar.me envia notificações automáticas quando
 * o status de um pedido muda (ex: paid, failed).
 * 
 * Configure no painel Pagar.me:
 * URL: https://unli.com.br/api/webhook_pagarme.php
 * Eventos: order.paid, order.canceled, order.payment_failed
 * 
 * SEGURANÇA:
 * - Não confia no payload: consulta API direto
 * - Verifica assinatura do webhook (quando configurada)
 * - Evita processamento duplicado
 * - Log de todas as operações
 * 
 * @version 1.0.0 - Pagar.me V5
 */

// ============================================
// CONFIGURAÇÃO SEGURA
// ============================================

define('SECURE_CONFIG_ACCESS', true);
define('DB_CONFIG_ACCESS', true);

$configFile = __DIR__ . '/config.secure.php';

if (!file_exists($configFile)) {
    http_response_code(500);
    exit;
}

require_once $configFile;

header('Content-Type: application/json; charset=utf-8');

// ============================================
// FUNÇÃO: DEBUG LOG
// ============================================
function webhookLog($message, $data = null) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [WEBHOOK] $message";
    
    if ($data !== null) {
        $logMessage .= " | " . json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    
    error_log($logMessage);
    
    // Log em arquivo dedicado
    $logFile = __DIR__ . '/logs/webhook.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    @file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
}

// ============================================
// VALIDAR MÉTODO (apenas POST)
// ============================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// ============================================
// RECEBER PAYLOAD DO WEBHOOK
// ============================================
$payload = file_get_contents('php://input');
$evento = json_decode($payload, true);

webhookLog('Webhook recebido', [
    'raw_length' => strlen($payload),
    'type' => $evento['type'] ?? 'unknown',
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
]);

// Se não for um evento válido, encerrar
if (!$evento || !isset($evento['type']) || !isset($evento['data'])) {
    webhookLog('Payload inválido recebido');
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

// ============================================
// VERIFICAR ASSINATURA (OPCIONAL - se configurado)
// ============================================
// O Pagar.me pode enviar um header x-hub-signature para validação
$webhookSignature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? null;

if ($webhookSignature && defined('PAGARME_WEBHOOK_SECRET') && PAGARME_WEBHOOK_SECRET !== 'sua-chave-secreta-webhook-aqui-use-openssl-rand') {
    $expectedSignature = hash_hmac('sha256', $payload, PAGARME_WEBHOOK_SECRET);
    
    if (!hash_equals($expectedSignature, $webhookSignature)) {
        webhookLog('Assinatura do webhook inválida', [
            'received' => substr($webhookSignature, 0, 20) . '...',
            'expected_prefix' => substr($expectedSignature, 0, 20) . '...'
        ]);
        http_response_code(401);
        echo json_encode(['error' => 'Invalid signature']);
        exit;
    }
    
    webhookLog('Assinatura do webhook válida');
}

// ============================================
// PROCESSAR EVENTO
// ============================================
$eventType = $evento['type'];
$eventData = $evento['data'];

webhookLog('Processando evento', [
    'type' => $eventType,
    'data_id' => $eventData['id'] ?? 'unknown'
]);

// Eventos que nos interessam
$relevantEvents = [
    'order.paid',           // Pagamento confirmado
    'order.canceled',       // Pedido cancelado
    'order.payment_failed', // Pagamento falhou
    'charge.paid',          // Charge paga (redundância)
    'charge.refunded'       // Estorno
];

if (!in_array($eventType, $relevantEvents)) {
    webhookLog('Evento ignorado (não relevante)', ['type' => $eventType]);
    http_response_code(200);
    echo json_encode(['status' => 'ignored', 'reason' => 'Event type not relevant']);
    exit;
}

// ============================================
// SEGURANÇA: CONSULTAR PAGAR.ME DIRETAMENTE
// ============================================
// NUNCA confiar no payload do webhook - sempre verificar na API

$pagarmeOrderId = null;

// Para eventos de order, o ID está no data
if (strpos($eventType, 'order.') === 0) {
    $pagarmeOrderId = $eventData['id'] ?? null;
} 
// Para eventos de charge, precisamos do order_id
elseif (strpos($eventType, 'charge.') === 0) {
    $pagarmeOrderId = $eventData['order']['id'] ?? $eventData['order_id'] ?? null;
}

if (!$pagarmeOrderId) {
    webhookLog('ID do pedido Pagar.me não encontrado no payload');
    http_response_code(400);
    echo json_encode(['error' => 'Order ID not found in payload']);
    exit;
}

webhookLog('Consultando API Pagar.me para verificação', ['pagarme_order_id' => $pagarmeOrderId]);

// Consultar API do Pagar.me diretamente
$url = PAGARME_API_URL . '/orders/' . $pagarmeOrderId;
$authHeader = 'Basic ' . base64_encode(PAGARME_SECRET_KEY . ':');

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: ' . $authHeader,
        'Content-Type: application/json'
    ],
    CURLOPT_TIMEOUT => defined('PAGARME_TIMEOUT') ? PAGARME_TIMEOUT : 30,
    CURLOPT_SSL_VERIFYPEER => defined('SSL_VERIFY_PEER') ? SSL_VERIFY_PEER : true,
    CURLOPT_USERAGENT => 'UNLI-Webhook/1.0-Pagarme'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError || $httpCode !== 200) {
    webhookLog('Erro ao consultar Pagar.me', [
        'http_code' => $httpCode,
        'curl_error' => $curlError,
        'pagarme_order_id' => $pagarmeOrderId
    ]);
    // Retornar 500 para que o Pagar.me tente novamente
    http_response_code(500);
    echo json_encode(['error' => 'Failed to verify with Pagar.me API']);
    exit;
}

$pedidoReal = json_decode($response, true);

if (!$pedidoReal || !isset($pedidoReal['status'])) {
    webhookLog('Resposta inválida da API Pagar.me');
    http_response_code(500);
    echo json_encode(['error' => 'Invalid response from Pagar.me']);
    exit;
}

$statusVerdadeiro = $pedidoReal['status'];

webhookLog('Status real do pedido no Pagar.me', [
    'pagarme_order_id' => $pagarmeOrderId,
    'status_real' => $statusVerdadeiro,
    'status_webhook' => $eventData['status'] ?? 'unknown'
]);

// ============================================
// EXTRAIR ORDER_ID INTERNO DO METADATA
// ============================================
$orderId = null;

if (isset($pedidoReal['metadata']['order_id'])) {
    $orderId = $pedidoReal['metadata']['order_id'];
} elseif (isset($pedidoReal['metadata']['external_reference'])) {
    // Formato: UNLI-{ORDER_ID}-{TIMESTAMP}
    $ref = $pedidoReal['metadata']['external_reference'];
    if (preg_match('/^UNLI-(ORD-\d{8}-[A-Z0-9]+)-\d+$/', $ref, $matches)) {
        $orderId = $matches[1];
    }
}

if (!$orderId) {
    // Tentar extrair do código dos items
    if (isset($pedidoReal['items'][0]['code'])) {
        $code = $pedidoReal['items'][0]['code'];
        if (strpos($code, 'ORD-') === 0) {
            $orderId = $code;
        }
    }
}

webhookLog('Order ID interno extraído', ['order_id' => $orderId]);

if (!$orderId) {
    webhookLog('Não foi possível extrair order_id interno. Pedido apenas no Pagar.me.', [
        'pagarme_order_id' => $pagarmeOrderId
    ]);
    // Ainda retornamos 200 para não ficar repetindo webhook
    http_response_code(200);
    echo json_encode(['status' => 'warning', 'message' => 'Internal order ID not found']);
    exit;
}

// ============================================
// ATUALIZAR BANCO DE DADOS
// ============================================
try {
    require_once __DIR__ . '/lib/database.php';
    
    // Mapear status do Pagar.me para interno
    $statusMap = [
        'paid' => 'paid',
        'pending' => 'pending',
        'processing' => 'pending',
        'failed' => 'failed',
        'canceled' => 'failed',
        'cancelled' => 'failed',
        'refunded' => 'refunded',
        'charged_back' => 'refunded'
    ];
    
    $internalStatus = $statusMap[$statusVerdadeiro] ?? 'pending';
    
    // Extrair dados do pagamento
    $amount = 0;
    $paymentMethod = '';
    $payerEmail = $pedidoReal['customer']['email'] ?? '';
    
    if (isset($pedidoReal['charges']) && !empty($pedidoReal['charges'])) {
        $lastCharge = end($pedidoReal['charges']);
        $amount = ($lastCharge['amount'] ?? 0) / 100;
        $paymentMethod = $lastCharge['payment_method'] ?? '';
    }
    
    // Verificar se já foi processado (evita duplicatas)
    $previousStatus = getOrderPaymentStatus($orderId);
    $alreadyProcessed = ($previousStatus === 'paid');
    
    webhookLog('Status anterior no banco', [
        'order_id' => $orderId,
        'previous_status' => $previousStatus,
        'new_status' => $internalStatus,
        'already_processed' => $alreadyProcessed
    ]);
    
    // Atualizar banco
    $pdo = get_db_connection();
    
    if ($pdo) {
        $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
        
        $sql = "UPDATE " . $prefix . "orders 
                SET payment_status = :status,
                    payment_id = :payment_id,
                    total_amount = :amount,
                    updated_at = NOW()
                WHERE JSON_UNQUOTE(JSON_EXTRACT(order_details, '$.order_id')) = :order_id";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':status' => $internalStatus,
            ':payment_id' => $pagarmeOrderId,
            ':amount' => $amount,
            ':order_id' => $orderId
        ]);
        
        $rowsAffected = $stmt->rowCount();
        
        webhookLog('Banco atualizado', [
            'rows_affected' => $rowsAffected,
            'order_id' => $orderId,
            'status' => $internalStatus
        ]);
        
        // Se pagamento aprovado E não processado antes → ações pós-venda
        if ($internalStatus === 'paid' && !$alreadyProcessed && $rowsAffected > 0) {
            
            // Atualizar onboarding_status para ativo
            $sql2 = "UPDATE " . $prefix . "orders 
                     SET onboarding_status = 'ativo'
                     WHERE JSON_UNQUOTE(JSON_EXTRACT(order_details, '$.order_id')) = :order_id
                     AND onboarding_status = 'pendente'";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->execute([':order_id' => $orderId]);
            
            webhookLog('Onboarding ativado');
            
            // Criar ticket no Fila Chamados + enviar Magic Link
            try {
                require_once __DIR__ . '/lib/fila-chamados.php';
                require_once __DIR__ . '/lib/onboarding-helpers.php';
                
                $orderData = getOrderDataForTicket($orderId);
                
                if ($orderData) {
                    $orderData['payment_id'] = $pagarmeOrderId;
                    $orderData['amount'] = $amount;
                    $orderData['payment_method'] = $paymentMethod;
                    $orderData['payer_email'] = $payerEmail;
                    
                    // 1. Criar ticket
                    $ticketResult = createTicketForSale($orderData);
                    
                    webhookLog('Ticket criado', [
                        'success' => $ticketResult['success'] ?? false,
                        'ticket_id' => $ticketResult['ticket_id'] ?? null
                    ]);
                    
                    // 2. Enviar Magic Link
                    if (!empty($orderData['onboarding_token']) && !empty($orderData['customer_email'])) {
                        $baseUrl = defined('SITE_BASE_URL') ? SITE_BASE_URL : 'https://unli.com.br';
                        $magicLink = $baseUrl . '/setup?token=' . $orderData['onboarding_token'];
                        
                        $hasSpecialistOnboarding = $orderData['has_specialist_onboarding'] ?? false;
                        
                        $emailSent = sendOnboardingEmail(
                            $orderData['customer_email'],
                            $orderData['customer_name'],
                            $magicLink,
                            $orderId,
                            $orderData['plan_name'] ?? 'Site Vitrine',
                            $hasSpecialistOnboarding
                        );
                        
                        webhookLog('Magic Link enviado', [
                            'email' => $orderData['customer_email'],
                            'sent' => $emailSent
                        ]);
                    }
                }
            } catch (Exception $e) {
                webhookLog('Erro em ações pós-venda', ['error' => $e->getMessage()]);
                // Não falhar o webhook por causa de ações secundárias
            }
        }
    } else {
        webhookLog('Banco de dados não disponível');
    }
    
} catch (Exception $e) {
    webhookLog('Erro ao processar webhook', ['error' => $e->getMessage()]);
    // Ainda retornamos 200 se o erro é nosso (DB), para não ficar repetindo
}

// Também atualizar o arquivo JSON da ordem
try {
    require_once __DIR__ . '/lib/storage.php';
    update_order_status($orderId, $internalStatus, [
        'pagarme_order_id' => $pagarmeOrderId,
        'pagarme_status' => $statusVerdadeiro,
        'webhook_processed_at' => date('Y-m-d H:i:s')
    ]);
    webhookLog('Arquivo JSON da ordem atualizado');
} catch (Exception $e) {
    webhookLog('Erro ao atualizar JSON', ['error' => $e->getMessage()]);
}

// ============================================
// RESPOSTA: SEMPRE 200 para o Pagar.me parar de reenviar
// ============================================
webhookLog('Webhook processado com sucesso', [
    'order_id' => $orderId,
    'pagarme_order_id' => $pagarmeOrderId,
    'status' => $statusVerdadeiro
]);

http_response_code(200);
echo json_encode([
    'status' => 'ok',
    'message' => 'Webhook processado com sucesso',
    'order_id' => $orderId,
    'pagarme_status' => $statusVerdadeiro
], JSON_UNESCAPED_UNICODE);

// ============================================
// FUNÇÕES AUXILIARES (Importadas do validate_payment.php)
// ============================================

/**
 * Busca o status de pagamento atual do pedido no banco
 * (Duplicada aqui para independência do webhook)
 */
function getOrderPaymentStatus($orderId) {
    try {
        $pdo = get_db_connection();
        if ($pdo === null) return null;
        
        $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
        
        $sql = "SELECT payment_status 
                FROM " . $prefix . "orders 
                WHERE JSON_UNQUOTE(JSON_EXTRACT(order_details, '$.order_id')) = :order_id
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? ($row['payment_status'] ?? null) : null;
        
    } catch (PDOException $e) {
        webhookLog('Erro ao verificar status', ['error' => $e->getMessage()]);
        return null;
    }
}

/**
 * Busca dados completos do pedido para criação de ticket
 */
function getOrderDataForTicket($orderId) {
    try {
        $pdo = get_db_connection();
        if ($pdo === null) return null;
        
        $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
        
        $sql = "SELECT id, customer_name, email, phone, order_details, 
                       total_amount, payment_method, payment_status, 
                       onboarding_token, created_at
                FROM " . $prefix . "orders 
                WHERE JSON_UNQUOTE(JSON_EXTRACT(order_details, '$.order_id')) = :order_id
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        
        $orderDetails = [];
        if (!empty($row['order_details'])) {
            $orderDetails = json_decode($row['order_details'], true) ?: [];
        }
        
        $planName = $orderDetails['product'] ?? $orderDetails['plan'] ?? 'Site Vitrine';
        
        $hasSpecialistOnboarding = false;
        if (isset($orderDetails['selection']['service_addons']['specialist_onboarding'])) {
            $hasSpecialistOnboarding = (bool) $orderDetails['selection']['service_addons']['specialist_onboarding'];
        }
        
        return [
            'order_id' => $orderId,
            'customer_name' => $row['customer_name'],
            'customer_email' => $row['email'],
            'customer_phone' => $row['phone'] ?? null,
            'plan_name' => $planName,
            'total_amount' => $row['total_amount'],
            'payment_method' => $row['payment_method'],
            'onboarding_token' => $row['onboarding_token'] ?? null,
            'selection' => $orderDetails['selection'] ?? null,
            'briefing' => $orderDetails['briefing'] ?? null,
            'has_specialist_onboarding' => $hasSpecialistOnboarding,
            'created_at' => $row['created_at']
        ];
        
    } catch (PDOException $e) {
        webhookLog('Erro ao buscar pedido', ['error' => $e->getMessage()]);
        return null;
    }
}
?>
