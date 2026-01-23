<?php
/**
 * ============================================
 * API - VERIFICAR STATUS DO PAGAMENTO
 * ============================================
 * 
 * Endpoint para verificar o status de um pagamento
 * Usado para polling no frontend (especialmente PIX)
 * 
 * GET /api/check_payment_status.php?payment_id=123&order_id=ORD-123
 */

header('Content-Type: application/json; charset=utf-8');

// CORS - Configuração segura
require_once __DIR__ . '/lib/cors.php';

// Configuração e banco
define('SECURE_CONFIG_ACCESS', true);
require_once __DIR__ . '/config.secure.php';
require_once __DIR__ . '/lib/database.php';

// ============================================
// FUNCTION: LOG DE VERIFICAÇÃO
// ============================================
function logStatusCheck($message, $data = null) {
    $logFile = __DIR__ . '/logs/status_check_' . date('Y-m-d') . '.log';
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
// PROCESSAR REQUISIÇÃO
// ============================================
try {
    // Validar parâmetros
    $paymentId = $_GET['payment_id'] ?? null;
    $orderId = $_GET['order_id'] ?? null;
    
    if (!$paymentId && !$orderId) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'payment_id ou order_id é obrigatório'
        ]);
        exit;
    }
    
    logStatusCheck('Verificação de status solicitada', [
        'payment_id' => $paymentId,
        'order_id' => $orderId
    ]);
    
    // Buscar no banco de dados local primeiro
    $localStatus = null;
    
    if ($orderId) {
        $localStatus = get_order_payment_status($orderId);
    } elseif ($paymentId) {
        $localStatus = get_payment_status_by_id($paymentId);
    }
    
    // Se temos status local e não é pending, retornar imediatamente
    if ($localStatus && $localStatus['status'] !== 'pending') {
        echo json_encode([
            'ok' => true,
            'status' => $localStatus['status'],
            'payment_id' => $localStatus['payment_id'],
            'order_id' => $localStatus['order_id'],
            'source' => 'database',
            'updated_at' => $localStatus['updated_at']
        ]);
        exit;
    }
    
    // Se é pending ou não temos info local, consultar Mercado Pago
    if ($paymentId) {
        $mpStatus = getMercadoPagoPaymentStatus($paymentId);
        
        if ($mpStatus) {
            // Mapear status do MP para nosso sistema
            $dbStatus = mapPaymentStatus($mpStatus['status']);
            
            // Atualizar no banco se mudou
            if ($localStatus && $localStatus['status'] !== $dbStatus) {
                update_payment_status($orderId ?: $mpStatus['external_reference'], $dbStatus, $paymentId);
                logStatusCheck('Status atualizado via polling', [
                    'order_id' => $orderId ?: $mpStatus['external_reference'],
                    'old_status' => $localStatus['status'],
                    'new_status' => $dbStatus
                ]);
            }
            
            echo json_encode([
                'ok' => true,
                'status' => $dbStatus,
                'payment_id' => $paymentId,
                'order_id' => $orderId ?: $mpStatus['external_reference'],
                'source' => 'mercadopago',
                'mp_status' => $mpStatus['status'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
        } else {
            // Erro ao consultar MP, retornar status local
            echo json_encode([
                'ok' => true,
                'status' => $localStatus ? $localStatus['status'] : 'pending',
                'payment_id' => $paymentId,
                'order_id' => $orderId,
                'source' => 'database_fallback',
                'error' => 'Não foi possível consultar Mercado Pago'
            ]);
        }
        
    } else {
        // Só temos order_id, retornar status local
        echo json_encode([
            'ok' => true,
            'status' => $localStatus ? $localStatus['status'] : 'pending',
            'order_id' => $orderId,
            'source' => 'database_only'
        ]);
    }
    
} catch (Exception $e) {
    logStatusCheck('Erro na verificação', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Erro interno do servidor'
    ]);
}

// ============================================
// FUNÇÕES AUXILIARES
// ============================================

/**
 * Busca status do pagamento no Mercado Pago
 */
function getMercadoPagoPaymentStatus($paymentId) {
    $url = "https://api.mercadopago.com/v1/payments/{$paymentId}";
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . MP_ACCESS_TOKEN,
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 10
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
 * Busca status de pagamento por order_id
 */
function get_order_payment_status($orderId) {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception('Database connection failed');
        }
        
        $conn->set_charset('utf8mb4');
        
        $stmt = $conn->prepare("
            SELECT payment_status as status, payment_id, order_id, updated_at 
            FROM orders 
            WHERE order_id = ?
        ");
        
        $stmt->bind_param('s', $orderId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $stmt->close();
        $conn->close();
        
        return $row;
        
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Busca status de pagamento por payment_id
 */
function get_payment_status_by_id($paymentId) {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception('Database connection failed');
        }
        
        $conn->set_charset('utf8mb4');
        
        $stmt = $conn->prepare("
            SELECT payment_status as status, payment_id, order_id, updated_at 
            FROM orders 
            WHERE payment_id = ?
        ");
        
        $stmt->bind_param('s', $paymentId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $stmt->close();
        $conn->close();
        
        return $row;
        
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Atualiza status do pagamento no banco
 */
function update_payment_status($orderId, $status, $paymentId) {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception('Database connection failed');
        }
        
        $conn->set_charset('utf8mb4');
        
        $stmt = $conn->prepare("
            UPDATE orders 
            SET payment_status = ?, payment_id = ?, updated_at = NOW() 
            WHERE order_id = ?
        ");
        
        $stmt->bind_param('sss', $status, $paymentId, $orderId);
        $success = $stmt->execute();
        
        $stmt->close();
        $conn->close();
        
        return $success;
        
    } catch (Exception $e) {
        return false;
    }
}