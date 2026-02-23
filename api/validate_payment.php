<?php
/**
 * ============================================
 * VALIDAÇÃO DE PAGAMENTO - PAGAR.ME
 * MVP Seguro - Double Check Server-Side
 * ============================================
 * 
 * Este script valida se um pagamento é realmente
 * válido consultando direto na API do Pagar.me V5.
 * 
 * Impede que usuários forjem URLs de sucesso.
 * 
 * Aceita tanto order_id (or_XXXX do Pagar.me) quanto
 * order_id interno (ORD-XXXXXXXX) via metadata.
 * 
 * @version 2.0.0 - Pagar.me V5
 */

// ============================================
// DEBUG TEMPORÁRIO - REMOVER EM PRODUÇÃO
// ============================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ============================================
// CONFIGURAÇÃO SEGURA
// ============================================

define('SECURE_CONFIG_ACCESS', true);
define('DB_CONFIG_ACCESS', true); // Permite acesso ao database.php

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

// Aceitar tanto payment_id (legado) quanto order_id (Pagar.me) ou pagarme_order_id
$paymentId = null;
$internalOrderId = null;

if ($data) {
    $paymentId = $data['payment_id'] ?? $data['pagarme_order_id'] ?? $data['order_id'] ?? null;
    $internalOrderId = $data['internal_order_id'] ?? null;
}

if (!$paymentId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Payment ID ou Order ID é obrigatório'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

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
// VALIDAR PAGAMENTO NO GATEWAY
// ============================================
try {
    debugLog('Validando pagamento', ['payment_id' => $paymentId]);
    
    // Consultar pagamento na API do Pagar.me V5
    $paymentDetails = getPagarmeOrderDetails($paymentId);
    
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
    
    // Extrair informações do pagamento (formato Pagar.me V5)
    $status = $paymentDetails['status'] ?? 'unknown';
    $statusDetail = '';
    $amount = 0;
    $paymentMethod = '';
    $payerEmail = '';
    
    // Pagar.me retorna amount em centavos nos charges
    if (isset($paymentDetails['charges']) && !empty($paymentDetails['charges'])) {
        $lastCharge = end($paymentDetails['charges']);
        $amount = ($lastCharge['amount'] ?? 0) / 100; // Converter centavos para reais
        $statusDetail = $lastCharge['last_transaction']['status'] ?? '';
        $paymentMethod = $lastCharge['payment_method'] ?? '';
        $payerEmail = $paymentDetails['customer']['email'] ?? '';
    } else {
        // Fallback: usar amount dos items
        if (isset($paymentDetails['items'])) {
            foreach ($paymentDetails['items'] as $item) {
                $amount += ($item['amount'] ?? 0) * ($item['quantity'] ?? 1);
            }
            $amount = $amount / 100;
        }
        $payerEmail = $paymentDetails['customer']['email'] ?? '';
    }
    
    // Extrair order_id interno do metadata ou external_reference
    $orderId = null;
    if (isset($paymentDetails['metadata']['order_id'])) {
        $orderId = $paymentDetails['metadata']['order_id'];
    } elseif (isset($paymentDetails['metadata']['external_reference'])) {
        $orderId = extractOrderId($paymentDetails['metadata']['external_reference']);
    } elseif ($internalOrderId) {
        $orderId = $internalOrderId;
    }
    
    // ID do Pagar.me (or_XXXXXXXX)
    $pagarmeOrderId = $paymentDetails['id'] ?? $paymentId;
    
    // Regras de Ouro da Validação (Pagar.me status)
    $isApproved = ($status === 'paid');
    
    debugLog('Resultado da validação', [
        'payment_id' => $paymentId,
        'pagarme_order_id' => $pagarmeOrderId,
        'order_id' => $orderId,
        'status' => $status,
        'amount' => $amount,
        'is_approved' => $isApproved
    ]);
    
    // ============================================
    // 🔥 CRÍTICO: ATUALIZAR STATUS NO BANCO
    // ============================================
    
    // Mapear status do Pagar.me para status interno
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
    
    $internalStatus = $statusMap[$status] ?? 'pending';
    
    // Variáveis para resultados
    $updateResult = false;
    $ticketResult = null;
    $orderData = null;
    $alreadyProcessed = false;
    
    // Atualizar banco de dados
    if ($orderId) {
        require_once __DIR__ . '/lib/database.php';
        
        // PRIMEIRO: Verificar se já foi processado (evita duplicatas)
        $previousStatus = getOrderPaymentStatus($orderId);
        $alreadyProcessed = ($previousStatus === 'paid');
        
        debugLog('Status anterior do pedido', [
            'order_id' => $orderId,
            'previous_status' => $previousStatus,
            'already_processed' => $alreadyProcessed
        ]);
        
        $updateResult = updatePaymentStatusFromValidation(
            $orderId,
            $internalStatus,
            $pagarmeOrderId,
            $paymentMethod,
            $amount
        );
        
        debugLog('Atualização do banco', [
            'order_id' => $orderId,
            'internal_status' => $internalStatus,
            'update_result' => $updateResult
        ]);
        
        // Se pagamento foi aprovado E NÃO foi processado antes, criar ticket e enviar email
        // Isso evita enviar múltiplos emails/tickets se o cliente atualizar a página
        if ($isApproved && $updateResult && !$alreadyProcessed) {
            require_once __DIR__ . '/lib/fila-chamados.php';
            require_once __DIR__ . '/lib/onboarding-helpers.php';
            
            // Buscar dados completos do pedido
            $orderData = getOrderDataForTicket($orderId);
            
            if ($orderData) {
                // Adicionar dados do pagamento ao orderData
                $orderData['payment_id'] = $pagarmeOrderId;
                $orderData['amount'] = $amount;
                $orderData['payment_method'] = $paymentMethod;
                $orderData['payer_email'] = $payerEmail;
                
                // 1. Criar ticket no Fila Chamados
                $ticketResult = createTicketForSale($orderData);
                
                debugLog('Resultado criação ticket Fila Chamados', [
                    'success' => $ticketResult['success'] ?? false,
                    'ticket_id' => $ticketResult['ticket_id'] ?? null,
                    'error' => $ticketResult['error'] ?? null
                ]);
                
                // 2. Enviar Magic Link por email
                $emailSent = false;
                if (!empty($orderData['onboarding_token']) && !empty($orderData['customer_email'])) {
                    // Montar URL do Magic Link
                    $baseUrl = defined('SITE_BASE_URL') ? SITE_BASE_URL : 'https://unli.com.br';
                    $magicLink = $baseUrl . '/setup?token=' . $orderData['onboarding_token'];
                    
                    // Verificar se comprou addon de especialista
                    $hasSpecialistOnboarding = $orderData['has_specialist_onboarding'] ?? false;
                    
                    $emailSent = sendOnboardingEmail(
                        $orderData['customer_email'],
                        $orderData['customer_name'],
                        $magicLink,
                        $orderId,
                        $orderData['plan_name'] ?? 'Site Vitrine',
                        $hasSpecialistOnboarding
                    );
                    
                    debugLog('Envio do Magic Link', [
                        'email' => $orderData['customer_email'],
                        'magic_link' => $magicLink,
                        'has_specialist' => $hasSpecialistOnboarding,
                        'sent' => $emailSent
                    ]);
                } else {
                    debugLog('Magic Link não enviado - token ou email ausente', [
                        'has_token' => !empty($orderData['onboarding_token']),
                        'has_email' => !empty($orderData['customer_email'])
                    ]);
                }
                
                // Salvar resultados para response
                $magicLinkSent = $emailSent;
            } else {
                debugLog('Pedido não encontrado para criar ticket', ['order_id' => $orderId]);
            }
        }
    }
    
    // ============================================
    
    if ($isApproved) {
        // ======= PAGAMENTO APROVADO =======
        
        echo json_encode([
            'success' => true,
            'approved' => true,
            'message' => $alreadyProcessed 
                ? 'Pagamento já confirmado anteriormente' 
                : 'Pagamento confirmado com sucesso',
            'payment_data' => [
                'payment_id' => $pagarmeOrderId,
                'pagarme_order_id' => $pagarmeOrderId,
                'order_id' => $orderId,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'payer_email' => $payerEmail,
                'status' => $status
            ],
            'db_updated' => $updateResult ?? false,
            'already_processed' => $alreadyProcessed,
            'ticket_created' => $alreadyProcessed ? false : ($ticketResult['success'] ?? false),
            'ticket_id' => $alreadyProcessed ? null : ($ticketResult['ticket_id'] ?? null),
            'magic_link_sent' => $alreadyProcessed ? false : ($magicLinkSent ?? false)
        ], JSON_UNESCAPED_UNICODE);
        
    } else {
        // ======= PAGAMENTO NÃO APROVADO =======
        
        $errorMessage = 'Pagamento não aprovado';
        
        switch ($status) {
            case 'pending':
            case 'processing':
                $errorMessage = 'Pagamento ainda está sendo processado';
                break;
            case 'failed':
                $errorMessage = 'Pagamento foi rejeitado: ' . $statusDetail;
                break;
            case 'canceled':
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
                'payment_id' => $pagarmeOrderId,
                'pagarme_order_id' => $pagarmeOrderId,
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
// FUNÇÃO: BUSCAR DETALHES DO PEDIDO NO PAGAR.ME
// ============================================
function getPagarmeOrderDetails($orderId) {
    $url = PAGARME_API_URL . '/orders/' . $orderId;
    
    debugLog('Consultando API Pagar.me', ['url' => $url, 'order_id' => $orderId]);
    
    $authHeader = 'Basic ' . base64_encode(PAGARME_SECRET_KEY . ':');
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: ' . $authHeader,
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => defined('PAGARME_TIMEOUT') ? PAGARME_TIMEOUT : 30,
        CURLOPT_CONNECTTIMEOUT => defined('PAGARME_CONNECT_TIMEOUT') ? PAGARME_CONNECT_TIMEOUT : 10,
        CURLOPT_SSL_VERIFYPEER => defined('SSL_VERIFY_PEER') ? SSL_VERIFY_PEER : true,
        CURLOPT_SSL_VERIFYHOST => defined('SSL_VERIFY_HOST') ? SSL_VERIFY_HOST : 2,
        CURLOPT_USERAGENT => 'UNLI-Validation/2.0-Pagarme'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    curl_close($ch);
    
    if ($curlError) {
        debugLog('Erro cURL na validação Pagar.me', [
            'error' => $curlError,
            'order_id' => $orderId
        ]);
        return false;
    }
    
    if ($httpCode !== 200) {
        debugLog('Pagar.me API retornou erro', [
            'http_code' => $httpCode,
            'response' => substr($response, 0, 500),
            'order_id' => $orderId
        ]);
        return false;
    }
    
    $responseData = json_decode($response, true);
    
    if (!$responseData || json_last_error() !== JSON_ERROR_NONE) {
        debugLog('Resposta inválida da API Pagar.me', [
            'response' => substr($response, 0, 500),
            'json_error' => json_last_error_msg()
        ]);
        return false;
    }
    
    debugLog('Pagar.me order details obtidos', [
        'order_id' => $responseData['id'] ?? 'unknown',
        'status' => $responseData['status'] ?? 'unknown'
    ]);
    
    return $responseData;
}

// ============================================
// FUNÇÃO: EXTRAIR ORDER ID
// ============================================
function extractOrderId($externalReference) {
    if (!$externalReference) return null;
    
    // Tentar extrair no formato UNLI-ORDER_ID-TIMESTAMP
    if (preg_match('/^UNLI-(ORD-\d{8}-[A-Z0-9]+)-\d+$/', $externalReference, $matches)) {
        return $matches[1];
    }
    
    // Tentar extrair order_id diretamente se começar com ORD-
    if (preg_match('/^(ORD-\d{8}-[A-Z0-9]+)/', $externalReference, $matches)) {
        return $matches[1];
    }
    
    // Formato antigo: UNLI-{ORDER_ID}-{TIMESTAMP}
    $parts = explode('-', $externalReference);
    if (count($parts) >= 2) {
        // Se segunda parte começa com ORD, retornar ORD-...
        if (strpos($parts[1], 'ORD') === 0) {
            return $parts[1] . '-' . $parts[2] . '-' . $parts[3];
        }
        return $parts[1];
    }
    
    return null;
}

// ============================================
// FUNÇÃO: ATUALIZAR STATUS DO PAGAMENTO NO BANCO
// ============================================
function updatePaymentStatusFromValidation($orderId, $status, $paymentId, $paymentMethod = null, $amount = null) {
    try {
        $pdo = get_db_connection();
        
        if ($pdo === null) {
            debugLog('AVISO: Banco de dados não disponível para atualização');
            return false;
        }
        
        $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
        
        // Atualizar pelo order_id no JSON (JSON_UNQUOTE remove as aspas do valor extraído)
        $sql = "UPDATE " . $prefix . "orders 
                SET payment_status = :status,
                    payment_id = :payment_id,
                    total_amount = :amount,
                    updated_at = NOW()
                WHERE JSON_UNQUOTE(JSON_EXTRACT(order_details, '$.order_id')) = :order_id";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':status' => $status,
            ':payment_id' => $paymentId,
            ':amount' => $amount,
            ':order_id' => $orderId
        ]);
        
        $rowsAffected = $stmt->rowCount();
        
        debugLog('SQL executado', [
            'sql' => $sql,
            'order_id_buscado' => $orderId,
            'prefix' => $prefix
        ]);
        
        debugLog('Resultado da atualização do banco', [
            'rows_affected' => $rowsAffected,
            'order_id' => $orderId,
            'status' => $status,
            'payment_id' => $paymentId
        ]);
        
        // Se pagamento foi aprovado, atualizar onboarding_status também
        if ($status === 'paid' && $rowsAffected > 0) {
            $sql2 = "UPDATE " . $prefix . "orders 
                     SET onboarding_status = 'ativo'
                     WHERE JSON_UNQUOTE(JSON_EXTRACT(order_details, '$.order_id')) = :order_id
                     AND onboarding_status = 'pendente'";
            
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->execute([
                ':order_id' => $orderId
            ]);
            
            debugLog('Onboarding status atualizado para ativo');
        }
        
        return $rowsAffected > 0;
        
    } catch (PDOException $e) {
        debugLog('Erro PDO ao atualizar', ['error' => $e->getMessage()]);
        return false;
    }
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

// ============================================
// FUNÇÃO: BUSCAR DADOS DO PEDIDO PARA TICKET
// ============================================
function getOrderDataForTicket($orderId) {
    try {
        $pdo = get_db_connection();
        
        if ($pdo === null) {
            debugLog('AVISO: Banco de dados não disponível para buscar pedido');
            return null;
        }
        
        $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
        
        // Colunas conforme o schema real da tabela orders
        $sql = "SELECT 
                    id,
                    customer_name,
                    email,
                    phone,
                    order_details,
                    total_amount,
                    payment_method,
                    payment_status,
                    onboarding_token,
                    created_at
                FROM " . $prefix . "orders 
                WHERE JSON_UNQUOTE(JSON_EXTRACT(order_details, '$.order_id')) = :order_id
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':order_id' => $orderId
        ]);
        
        debugLog('Buscando dados do pedido para ticket', ['order_id' => $orderId]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            debugLog('Pedido não encontrado no banco', ['order_id' => $orderId]);
            return null;
        }
        
        debugLog('Pedido encontrado', ['row' => $row]);
        
        // Decodificar order_details se for JSON
        $orderDetails = [];
        if (!empty($row['order_details'])) {
            $orderDetails = json_decode($row['order_details'], true) ?: [];
        }
        
        // Extrair nome do plano do order_details
        $planName = $orderDetails['product'] ?? $orderDetails['plan'] ?? 'Site Vitrine';
        
        // Verificar se comprou addon de especialista
        $hasSpecialistOnboarding = false;
        if (isset($orderDetails['selection']['service_addons']['specialist_onboarding'])) {
            $hasSpecialistOnboarding = (bool) $orderDetails['selection']['service_addons']['specialist_onboarding'];
        }
        
        // Montar dados para o ticket (mapeando colunas do banco para nomes esperados)
        return [
            'order_id' => $orderId,
            'customer_name' => $row['customer_name'],
            'customer_email' => $row['email'],  // Coluna real é 'email'
            'customer_phone' => $row['phone'] ?? null,  // Coluna real é 'phone'
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
        debugLog('Erro PDO ao buscar pedido para ticket', ['error' => $e->getMessage()]);
        return null;
    }
}

// ============================================
// FUNÇÃO: VERIFICAR STATUS ATUAL DO PEDIDO
// ============================================
// Usado para evitar processamento duplicado de tickets/emails
function getOrderPaymentStatus($orderId) {
    try {
        $pdo = get_db_connection();
        
        if ($pdo === null) {
            return null;
        }
        
        $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
        
        $sql = "SELECT payment_status 
                FROM " . $prefix . "orders 
                WHERE JSON_UNQUOTE(JSON_EXTRACT(order_details, '$.order_id')) = :order_id
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':order_id' => $orderId
        ]);
        
        debugLog('Buscando status do pedido', ['order_id' => $orderId, 'prefix' => $prefix]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row ? ($row['payment_status'] ?? null) : null;
        
    } catch (PDOException $e) {
        debugLog('Erro PDO ao verificar status', ['error' => $e->getMessage()]);
        return null;
    }
}
?>