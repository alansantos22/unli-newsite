<?php
/**
 * ============================================
 * MERCADO PAGO - VALIDAÇÃO DE PAGAMENTO
 * MVP Seguro - Double Check Server-Side
 * ============================================
 * 
 * Este script valida se um payment_id é realmente
 * válido consultando direto no servidor do MP.
 * 
 * Impede que usuários forjem URLs de sucesso.
 * 
 * @version 1.0.0 - MVP Seguro
 */

// ============================================
// CONFIGURAÇÃO SEGURA
// ============================================

define('SECURE_CONFIG_ACCESS', true);

$configFile = __DIR__ . '/config.secure.php';

if (!file_exists($configFile)) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => '⚠️ ERRO DE CONFIGURAÇÃO: Arquivo config.secure.php não encontrado.',
    ], JSON_UNESCAPED_UNICODE));
}

require_once $configFile;
require_once __DIR__ . '/lib/cors.php';

header('Content-Type: application/json; charset=utf-8');

// ============================================
// VALIDAR MÉTODO
// ============================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================
// RECEBER DADOS
// ============================================
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['payment_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Payment ID é obrigatório'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$paymentId = $data['payment_id'];

// ============================================
// FUNÇÃO: DEBUG LOG
// ============================================
function debugLog($message, $data = null) {
    if (!DEBUG_MODE) return;
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    
    if ($data !== null) {
        $logMessage .= " | " . json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    
    error_log($logMessage);
}

// ============================================
// VALIDAR PAGAMENTO NO MERCADO PAGO
// ============================================
try {
    debugLog('Validando pagamento', ['payment_id' => $paymentId]);
    
    // Consultar pagamento na API do Mercado Pago
    $paymentDetails = getPaymentDetails($paymentId);
    
    if (!$paymentDetails) {
        debugLog('Pagamento não encontrado ou erro na API', ['payment_id' => $paymentId]);
        
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'approved' => false,
            'message' => 'Pagamento não encontrado'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    debugLog('Detalhes do pagamento obtidos', $paymentDetails);
    
    // Extrair informações do pagamento
    $status = $paymentDetails['status'] ?? 'unknown';
    $statusDetail = $paymentDetails['status_detail'] ?? '';
    $amount = $paymentDetails['transaction_amount'] ?? 0;
    $externalReference = $paymentDetails['external_reference'] ?? '';
    $paymentMethod = $paymentDetails['payment_method_id'] ?? '';
    $payerEmail = $paymentDetails['payer']['email'] ?? '';
    
    // Extrair order_id do external_reference (UNLI-{ORDER_ID}-{TIMESTAMP})
    $orderId = extractOrderId($externalReference);
    
    // Regras de Ouro da Validação
    $isApproved = ($status === 'approved');
    
    debugLog('Resultado da validação', [
        'payment_id' => $paymentId,
        'order_id' => $orderId,
        'status' => $status,
        'amount' => $amount,
        'is_approved' => $isApproved
    ]);
    
    if ($isApproved) {
        // ======= PAGAMENTO APROVADO =======
        
        // Aqui você pode:
        // 1. Ativar o serviço no banco de dados
        // 2. Enviar e-mail de boas-vindas
        // 3. Integrar com sistemas externos
        
        // Exemplo de ativação:
        // activateService($orderId, $paymentDetails);
        
        echo json_encode([
            'success' => true,
            'approved' => true,
            'message' => 'Pagamento confirmado com sucesso',
            'payment_data' => [
                'payment_id' => $paymentId,
                'order_id' => $orderId,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'payer_email' => $payerEmail,
                'status' => $status
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } else {
        // ======= PAGAMENTO NÃO APROVADO =======
        
        $errorMessage = 'Pagamento não aprovado';
        
        switch ($status) {
            case 'pending':
            case 'in_process':
                $errorMessage = 'Pagamento ainda está sendo processado';
                break;
            case 'rejected':
                $errorMessage = 'Pagamento foi rejeitado: ' . $statusDetail;
                break;
            case 'cancelled':
                $errorMessage = 'Pagamento foi cancelado';
                break;
            case 'refunded':
                $errorMessage = 'Pagamento foi estornado';
                break;
        }
        
        echo json_encode([
            'success' => true,  // Request OK, mas pagamento não aprovado
            'approved' => false,
            'message' => $errorMessage,
            'payment_data' => [
                'payment_id' => $paymentId,
                'order_id' => $orderId,
                'status' => $status,
                'status_detail' => $statusDetail
            ]
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    debugLog('ERRO na validação', [
        'error' => $e->getMessage(),
        'payment_id' => $paymentId
    ]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'approved' => false,
        'message' => 'Erro interno na validação: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

// ============================================
// FUNÇÃO: BUSCAR DETALHES DO PAGAMENTO
// ============================================
function getPaymentDetails($paymentId) {
    $url = "https://api.mercadopago.com/v1/payments/" . $paymentId;
    
    debugLog('Consultando API do Mercado Pago', ['url' => $url]);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . MP_ACCESS_TOKEN,
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => SSL_VERIFY_PEER,
        CURLOPT_SSL_VERIFYHOST => SSL_VERIFY_HOST,
        CURLOPT_USERAGENT => 'UNLI-Validation/1.0'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    curl_close($ch);
    
    if ($curlError) {
        debugLog('Erro cURL na validação', [
            'error' => $curlError,
            'payment_id' => $paymentId
        ]);
        return false;
    }
    
    if ($httpCode !== 200) {
        debugLog('API retornou erro', [
            'http_code' => $httpCode,
            'response' => $response,
            'payment_id' => $paymentId
        ]);
        return false;
    }
    
    $responseData = json_decode($response, true);
    
    if (!$responseData || json_last_error() !== JSON_ERROR_NONE) {
        debugLog('Resposta inválida da API', [
            'response' => $response,
            'json_error' => json_last_error_msg()
        ]);
        return false;
    }
    
    return $responseData;
}

// ============================================
// FUNÇÃO: EXTRAIR ORDER ID
// ============================================
function extractOrderId($externalReference) {
    if (!$externalReference) return null;
    
    // Format: UNLI-{ORDER_ID}-{TIMESTAMP}
    $parts = explode('-', $externalReference);
    return isset($parts[1]) ? $parts[1] : null;
}

// ============================================
// FUNÇÃO: ATIVAR SERVIÇO (OPCIONAL)
// ============================================
function activateService($orderId, $paymentDetails) {
    // Aqui você implementa a lógica de ativação:
    // 
    // 1. Salvar no banco de dados
    // 2. Enviar e-mail de boas-vindas
    // 3. Ativar no sistema de chamados
    // 4. Integrar com outros sistemas
    
    debugLog('Ativando serviço', [
        'order_id' => $orderId,
        'payment_amount' => $paymentDetails['transaction_amount'] ?? 0
    ]);
    
    // Exemplo de implementação:
    /*
    try {
        if (defined('DB_HOST')) {
            $conn = getConnection();
            $stmt = $conn->prepare("
                UPDATE orders 
                SET status = 'active', payment_confirmed_at = NOW() 
                WHERE order_id = ?
            ");
            $stmt->execute([$orderId]);
        }
    } catch (Exception $e) {
        debugLog('Erro ao ativar serviço no banco', $e->getMessage());
    }
    */
}
?>