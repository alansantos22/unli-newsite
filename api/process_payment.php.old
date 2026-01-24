<?php
/**
 * ============================================
 * MERCADO PAGO - CHECKOUT TRANSPARENTE
 * Processamento de Pagamento (cURL Nativo)
 * ============================================
 * 
 * Sistema Stand-alone para hospedagem compartilhada
 * Sem dependências externas (Composer/SDK)
 * 
 * Suporta:
 * - Cartão de Crédito (com parcelamento)
 * - Pix (com QR Code)
 * 
 * @version 1.0.0
 * @author Sistema UNLI
 */

// ============================================
// CONFIGURAÇÃO SEGURA
// ============================================

// Carregar arquivo de configuração protegido
define('SECURE_CONFIG_ACCESS', true);

$configFile = __DIR__ . '/config.secure.php';

if (!file_exists($configFile)) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => '⚠️ ERRO DE CONFIGURAÇÃO: Arquivo config.secure.php não encontrado. Copie config.example.php e configure suas credenciais.',
        'instructions' => 'Execute: cp api/config.example.php api/config.secure.php'
    ], JSON_UNESCAPED_UNICODE));
}

require_once $configFile;

// Carregar helpers do banco de dados
require_once __DIR__ . '/lib/database.php';

// Carregar helpers do Fila Chamados
require_once __DIR__ . '/lib/fila-chamados.php';

// ============================================
// CORS - Configuração segura
// ============================================
require_once __DIR__ . '/lib/cors.php';

header('Content-Type: application/json; charset=utf-8');

// Verificar se as credenciais foram configuradas corretamente
if (!validateCredentials()) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => '⚠️ ERRO: Credenciais do Mercado Pago não configuradas. Edite config.secure.php com suas chaves reais.',
        'docs' => 'https://www.mercadopago.com.br/developers/panel/credentials'
    ], JSON_UNESCAPED_UNICODE));
}

// Aliases para compatibilidade
define('MERCADOPAGO_ACCESS_TOKEN', MP_ACCESS_TOKEN);
define('MERCADOPAGO_API_URL', MP_API_URL);

// ============================================
// FUNÇÃO: LOG DE DEBUG
// ============================================
function debugLog($message, $data = null) {
    if (DEBUG_MODE) {
        $log = [
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => $message
        ];
        
        if ($data !== null) {
            $log['data'] = $data;
        }
        
        error_log(json_encode($log, JSON_PRETTY_PRINT));
    }
}

// ============================================
// FUNÇÃO: RESPOSTA JSON
// ============================================
function jsonResponse($success, $message, $data = [], $httpCode = 200) {
    http_response_code($httpCode);
    
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('c')
    ];
    
    // Adicionar dados extras para Pix
    if (isset($data['qr_code'])) {
        $response['qr_code'] = $data['qr_code'];
        $response['qr_code_base64'] = $data['qr_code_base64'];
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// ============================================
// FUNÇÃO: VALIDAR ACCESS TOKEN (Removida - agora usa config.secure.php)
// ============================================
// Esta função foi movida para config.secure.php como validateCredentials()

// ============================================
// FUNÇÃO: GERAR IDEMPOTENCY KEY (evita duplicações)
// ============================================
function generateIdempotencyKey() {
    // Gera um UUID v4 único
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// ============================================
// FUNÇÃO: FAZER REQUEST COM cURL
// ============================================
function makeMercadoPagoRequest($payload) {
    $idempotencyKey = generateIdempotencyKey();
    
    debugLog('Iniciando request para Mercado Pago', [
        'idempotency_key' => $idempotencyKey,
        'payload' => $payload
    ]);
    
    // Inicializar cURL
    $ch = curl_init(MERCADOPAGO_API_URL);
    
    if ($ch === false) {
        debugLog('ERRO: Falha ao inicializar cURL');
        jsonResponse(false, 'Erro interno: cURL não disponível', [], 500);
    }
    
    // Configurar headers
    $headers = [
        'Authorization: Bearer ' . MERCADOPAGO_ACCESS_TOKEN,
        'Content-Type: application/json',
        'X-Idempotency-Key: ' . $idempotencyKey,
        'User-Agent: UNLI-Checkout/1.0'
    ];
    
    // Configurar opções do cURL
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => SSL_VERIFY_PEER, // Usa configuração do config.secure.php
        CURLOPT_SSL_VERIFYHOST => SSL_VERIFY_HOST,
        CURLOPT_TIMEOUT => MP_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => MP_CONNECT_TIMEOUT,
        CURLOPT_ENCODING => 'gzip,deflate',
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3
    ]);
    
    // Executar request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    
    curl_close($ch);
    
    // Verificar erro de conexão
    if ($curlErrno !== 0) {
        debugLog('ERRO cURL', [
            'errno' => $curlErrno,
            'error' => $curlError
        ]);
        
        jsonResponse(
            false,
            'Erro de conexão com Mercado Pago: ' . $curlError,
            [],
            500
        );
    }
    
    // Decodificar resposta
    $decodedResponse = json_decode($response, true);
    
    debugLog('Resposta do Mercado Pago', [
        'http_code' => $httpCode,
        'response' => $decodedResponse
    ]);
    
    return [
        'http_code' => $httpCode,
        'response' => $decodedResponse,
        'raw' => $response
    ];
}

// ============================================
// FUNÇÃO: PROCESSAR PAGAMENTO PIX
// ============================================
function processPixPayment($data) {
    debugLog('Processando pagamento PIX');
    
    // Validar campos obrigatórios
    if (empty($data['payer']['email'])) {
        jsonResponse(false, 'E-mail do pagador é obrigatório', [], 400);
    }
    
    if (empty($data['payer']['identification']['number'])) {
        jsonResponse(false, 'Documento do pagador é obrigatório', [], 400);
    }
    
    // Montar payload para Mercado Pago
    $payload = [
        'transaction_amount' => (float) $data['transaction_amount'],
        'description' => $data['description'] ?? 'Pagamento',
        'payment_method_id' => 'pix',
        'payer' => [
            'email' => filter_var($data['payer']['email'], FILTER_SANITIZE_EMAIL),
            'identification' => [
                'type' => $data['payer']['identification']['type'],
                'number' => preg_replace('/\D/', '', $data['payer']['identification']['number'])
            ]
        ]
    ];
    
    // Fazer request
    $result = makeMercadoPagoRequest($payload);
    
    // Verificar sucesso
    if ($result['http_code'] === 201 || $result['http_code'] === 200) {
        $response = $result['response'];
        
        // Extrair dados do QR Code
        $qrCode = $response['point_of_interaction']['transaction_data']['qr_code'] ?? null;
        $qrCodeBase64 = $response['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null;
        
        if (!$qrCode || !$qrCodeBase64) {
            debugLog('ERRO: QR Code não retornado pelo Mercado Pago');
            jsonResponse(false, 'Erro ao gerar QR Code', [], 500);
        }
        
        // 🔥 SALVAR/ATUALIZAR NO BANCO DE DADOS
        $orderId = $data['external_reference'] ?? null;
        if ($orderId) {
            update_payment_status($orderId, 'pending', $response['id']);
            debugLog('Status atualizado no BD', ['order_id' => $orderId, 'payment_id' => $response['id']]);
            
            // 🎫 NOTA: Ticket do Fila Chamados será criado quando o Pix for confirmado via webhook
            // (o status 'pending' ainda não é uma venda confirmada)
        }
        
        jsonResponse(
            true,
            'QR Code Pix gerado com sucesso',
            [
                'payment_id' => $response['id'],
                'status' => $response['status'],
                'qr_code' => $qrCode,
                'qr_code_base64' => 'data:image/png;base64,' . $qrCodeBase64
            ],
            200
        );
        
    } else {
        // Erro do Mercado Pago
        $errorMessage = $result['response']['message'] ?? 'Erro desconhecido';
        
        if (isset($result['response']['cause'])) {
            foreach ($result['response']['cause'] as $cause) {
                $errorMessage .= ' - ' . ($cause['description'] ?? '');
            }
        }
        
        jsonResponse(false, $errorMessage, [], $result['http_code']);
    }
}

// ============================================
// FUNÇÃO: PROCESSAR PAGAMENTO COM CARTÃO
// ============================================
function processCreditCardPayment($data) {
    debugLog('Processando pagamento com Cartão de Crédito');
    
    // Validar campos obrigatórios
    if (empty($data['token'])) {
        jsonResponse(false, 'Token do cartão é obrigatório', [], 400);
    }
    
    if (empty($data['payer']['email'])) {
        jsonResponse(false, 'E-mail do pagador é obrigatório', [], 400);
    }
    
    if (empty($data['installments']) || $data['installments'] < 1) {
        jsonResponse(false, 'Número de parcelas inválido', [], 400);
    }
    
    // Garantir que transaction_amount está em formato correto (float com ponto)
    $transactionAmount = (float) str_replace(',', '.', $data['transaction_amount']);
    
    // Montar payload para Mercado Pago
    $payload = [
        'transaction_amount' => $transactionAmount,
        'token' => $data['token'],
        'description' => $data['description'] ?? 'Pagamento',
        'installments' => (int) $data['installments'],
        'payment_method_id' => $data['payment_method_id'],
        'issuer_id' => $data['issuer_id'] ?? null,
        'payer' => [
            'email' => filter_var($data['payer']['email'], FILTER_SANITIZE_EMAIL),
            'identification' => [
                'type' => $data['payer']['identification']['type'],
                'number' => preg_replace('/\D/', '', $data['payer']['identification']['number'])
            ]
        ]
    ];
    
    // Remover issuer_id se estiver vazio
    if (empty($payload['issuer_id'])) {
        unset($payload['issuer_id']);
    }
    
    // Fazer request
    $result = makeMercadoPagoRequest($payload);
    
    // Verificar sucesso
    if ($result['http_code'] === 201 || $result['http_code'] === 200) {
        $response = $result['response'];
        $status = $response['status'];
        
        // Mapear status para mensagem amigável
        $statusMessages = [
            'approved' => 'Pagamento aprovado com sucesso!',
            'pending' => 'Pagamento em análise. Aguarde confirmação.',
            'in_process' => 'Pagamento em processamento.',
            'rejected' => 'Pagamento rejeitado: ' . ($response['status_detail'] ?? 'Verifique os dados do cartão')
        ];
        
        $message = $statusMessages[$status] ?? 'Pagamento processado';
        
        // 🔥 SALVAR/ATUALIZAR NO BANCO DE DADOS
        $orderId = $data['external_reference'] ?? null;
        if ($orderId) {
            $dbStatus = ($status === 'approved') ? 'paid' : 'pending';
            update_payment_status($orderId, $dbStatus, $response['id']);
            debugLog('Status atualizado no BD', [
                'order_id' => $orderId, 
                'payment_id' => $response['id'],
                'status' => $dbStatus
            ]);
            
            // 🎫 CRIAR TICKET NO FILA CHAMADOS se pagamento aprovado
            if ($status === 'approved') {
                $ticketData = [
                    'customer_name' => $data['payer']['first_name'] ?? 'Cliente',
                    'customer_email' => $data['payer']['email'],
                    'order_id' => $orderId,
                    'plan_name' => $data['description'] ?? 'Site',
                    'amount' => $data['transaction_amount'],
                    'payment_method' => 'credit_card',
                    'payment_id' => $response['id']
                ];
                
                $ticketResult = createTicketForSale($ticketData);
                
                if ($ticketResult['success']) {
                    debugLog('✅ Ticket criado no Fila Chamados', [
                        'ticket_id' => $ticketResult['ticket_id'],
                        'order_id' => $orderId
                    ]);
                } elseif (!isset($ticketResult['skipped'])) {
                    debugLog('⚠️ Falha ao criar ticket no Fila Chamados', [
                        'error' => $ticketResult['error'],
                        'order_id' => $orderId
                    ]);
                }
            }
        }
        
        jsonResponse(
            $status === 'approved',
            $message,
            [
                'payment_id' => $response['id'],
                'status' => $status,
                'status_detail' => $response['status_detail'] ?? null
            ],
            200
        );
        
    } else {
        // Erro do Mercado Pago
        $errorMessage = $result['response']['message'] ?? 'Erro desconhecido';
        
        if (isset($result['response']['cause'])) {
            foreach ($result['response']['cause'] as $cause) {
                $errorMessage .= ' - ' . ($cause['description'] ?? '');
            }
        }
        
        jsonResponse(false, $errorMessage, [], $result['http_code']);
    }
}

// ============================================
// MAIN: PROCESSAR REQUEST
// ============================================

try {
    // Validar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, 'Método não permitido. Use POST', [], 405);
    }
    
    // Obter payload do request
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        jsonResponse(false, 'JSON inválido: ' . json_last_error_msg(), [], 400);
    }
    
    debugLog('Request recebido', $data);
    
    // Validar campos básicos
    if (empty($data['payment_method_id'])) {
        jsonResponse(false, 'Método de pagamento não especificado', [], 400);
    }
    
    if (empty($data['transaction_amount']) || $data['transaction_amount'] <= 0) {
        jsonResponse(false, 'Valor da transação inválido', [], 400);
    }
    
    // Rotear para função específica
    if ($data['payment_method_id'] === 'pix') {
        processPixPayment($data);
    } else {
        processCreditCardPayment($data);
    }
    
} catch (Exception $e) {
    debugLog('EXCEÇÃO CAPTURADA', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    
    jsonResponse(
        false,
        'Erro interno do servidor: ' . $e->getMessage(),
        [],
        500
    );
}

// ============================================
// FIM DO ARQUIVO
// ============================================
?>
