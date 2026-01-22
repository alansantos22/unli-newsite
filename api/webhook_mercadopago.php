<?php
/**
 * ============================================
 * WEBHOOK DO MERCADO PAGO
 * Processamento de Notificações de Pagamento
 * ============================================
 * 
 * Este arquivo recebe notificações do Mercado Pago
 * quando o status de um pagamento muda.
 * 
 * Configure a URL deste webhook no painel do Mercado Pago:
 * https://www.mercadopago.com.br/developers/panel/webhooks
 * 
 * URL do Webhook: https://seu-dominio.com/api/webhook_mercadopago.php
 */

// ============================================
// CONFIGURAÇÃO E INCLUDES
// ============================================

header('Content-Type: application/json; charset=utf-8');

define('SECURE_CONFIG_ACCESS', true);
require_once __DIR__ . '/config.secure.php';
require_once __DIR__ . '/lib/database.php';

// ============================================
// LOG DE WEBHOOKS
// ============================================

function logWebhook($message, $data = null) {
    $logFile = __DIR__ . '/logs/webhook_' . date('Y-m-d') . '.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => $message,
        'data' => $data
    ];
    
    file_put_contents(
        $logFile,
        json_encode($logEntry, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL,
        FILE_APPEND
    );
}

// ============================================
// PROCESSAR WEBHOOK
// ============================================

try {
    // Obter dados do webhook
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    logWebhook('Webhook recebido', [
        'raw' => $input,
        'parsed' => $data,
        'headers' => getallheaders()
    ]);
    
    // Verificar se é notificação de pagamento
    if (!isset($data['type']) || $data['type'] !== 'payment') {
        logWebhook('Tipo de notificação ignorada', ['type' => $data['type'] ?? 'unknown']);
        http_response_code(200);
        echo json_encode(['ok' => true, 'message' => 'Notification type ignored']);
        exit;
    }
    
    // Extrair ID do pagamento
    $paymentId = $data['data']['id'] ?? null;
    
    if (!$paymentId) {
        logWebhook('Erro: Payment ID não encontrado', $data);
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Payment ID not found']);
        exit;
    }
    
    // Buscar detalhes do pagamento na API do Mercado Pago
    $paymentDetails = getPaymentDetails($paymentId);
    
    if (!$paymentDetails) {
        logWebhook('Erro ao buscar detalhes do pagamento', ['payment_id' => $paymentId]);
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Failed to fetch payment details']);
        exit;
    }
    
    logWebhook('Detalhes do pagamento obtidos', $paymentDetails);
    
    // Extrair informações relevantes
    $status = $paymentDetails['status'] ?? 'unknown';
    $orderId = $paymentDetails['external_reference'] ?? null;
    
    if (!$orderId) {
        logWebhook('Warning: external_reference não encontrado', $paymentDetails);
        // Não podemos atualizar o pedido sem o order_id
        http_response_code(200);
        echo json_encode(['ok' => true, 'message' => 'No external_reference to update']);
        exit;
    }
    
    // Mapear status do Mercado Pago para status do banco
    $dbStatus = mapPaymentStatus($status);
    
    // Atualizar status no banco de dados
    $updated = update_payment_status($orderId, $dbStatus, $paymentId);
    
    if ($updated) {
        logWebhook('Status atualizado com sucesso', [
            'order_id' => $orderId,
            'payment_id' => $paymentId,
            'mp_status' => $status,
            'db_status' => $dbStatus
        ]);
        
        // Se o pagamento foi aprovado, enviar e-mail de boas-vindas / onboarding
        if ($dbStatus === 'paid') {
            $order = get_order_by_id($orderId);
            
            if ($order && !empty($order['onboarding_token'])) {
                // Enviar e-mail com magic link para onboarding
                // TODO: Implementar envio de e-mail
                logWebhook('Pagamento aprovado - enviar e-mail de onboarding', [
                    'order_id' => $orderId,
                    'token' => $order['onboarding_token'],
                    'email' => $order['email']
                ]);
            }
        }
        
    } else {
        logWebhook('Erro ao atualizar status no banco', [
            'order_id' => $orderId,
            'payment_id' => $paymentId
        ]);
    }
    
    // Sempre retornar 200 OK para o Mercado Pago
    http_response_code(200);
    echo json_encode(['ok' => true, 'processed' => true]);
    
} catch (Exception $e) {
    logWebhook('Exceção capturada', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Internal server error']);
}

// ============================================
// FUNÇÕES AUXILIARES
// ============================================

/**
 * Busca detalhes do pagamento na API do Mercado Pago
 */
function getPaymentDetails($paymentId) {
    $url = "https://api.mercadopago.com/v1/payments/{$paymentId}";
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . MP_ACCESS_TOKEN,
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return null;
    }
    
    return json_decode($response, true);
}

/**
 * Mapeia status do Mercado Pago para status do banco
 */
function mapPaymentStatus($mpStatus) {
    $statusMap = [
        'approved' => 'paid',
        'pending' => 'pending',
        'in_process' => 'pending',
        'rejected' => 'failed',
        'cancelled' => 'failed',
        'refunded' => 'refunded',
        'charged_back' => 'refunded'
    ];
    
    return $statusMap[$mpStatus] ?? 'pending';
}

/**
 * Envia e-mail de onboarding (placeholder)
 */
function sendOnboardingEmail($email, $name, $token) {
    // TODO: Implementar envio real de e-mail
    // Pode usar PHPMailer, SMTP, ou API de e-mail
    
    $magicLink = "https://seu-dominio.com/onboarding?token={$token}";
    
    // Por enquanto, apenas loga
    logWebhook('E-mail de onboarding (simulado)', [
        'to' => $email,
        'name' => $name,
        'magic_link' => $magicLink
    ]);
    
    return true;
}
