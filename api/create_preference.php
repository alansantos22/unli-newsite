<?php
/**
 * ============================================
 * GATEWAY DE PAGAMENTO - CHECKOUT PAGAR.ME
 * Criação de Pedidos com Checkout Redirect
 * ============================================
 * 
 * Integração com Pagar.me API V5
 * Cria pedido no Pagar.me e retorna URL de checkout
 * para redirecionamento do cliente.
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
require_once __DIR__ . '/lib/pricing.php'; // Importar lógica de pricing
require_once __DIR__ . '/lib/storage.php'; // Para load_order() - consistência de dados

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

if (!$data) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Dados inválidos'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================
// FUNÇÃO: DEBUG LOG
// ============================================
function debugLog($message, $data = null) {
    if (!defined('DEBUG_MODE') || !DEBUG_MODE) return;
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    
    if ($data !== null) {
        $logMessage .= " | " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
    error_log($logMessage);
}

// ============================================
// VALIDAR DADOS OBRIGATÓRIOS
// ============================================
$requiredFields = ['order_id', 'payment_type', 'payer_email'];
foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => "Campo obrigatório ausente: $field"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

debugLog('Dados recebidos para criação de preferência', $data);

// ============================================
// CARREGAR ORDEM SALVA (FONTE DA VERDADE)
// ============================================

// GARANTIR CONSISTÊNCIA: Usar exatamente os mesmos dados da ordem criada
// Isso evita discrepâncias entre o que foi salvo e o que vai para o gateway

$orderId = $data['order_id'];

try {
    // Carregar ordem já validada e com preços calculados
    $order = load_order($orderId);
    
    if (!$order) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Ordem não encontrada: ' . $orderId
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // USAR DADOS EXATOS DA ORDEM (já normalizados e validados)
    $selection = $order['selection'];
    $pricing = $order['pricing'];
    $paymentMethodFromOrder = $order['payment_method'];
    
    debugLog('Dados carregados da ordem salva', [
        'order_id' => $orderId,
        'selection' => $selection,
        'pricing' => $pricing,
        'payment_method' => $paymentMethodFromOrder,
        'source' => 'saved_order'
    ]);
    
    // Verificar se payment_type do request bate com da ordem
    // Normalizar formatos: '12x' e 'prazo' são equivalentes para parcelado
    $requestedPaymentType = $data['payment_type'];
    $isOrderParcelado = in_array($paymentMethodFromOrder, ['12x', 'prazo', 'parcelado', 'installments']);
    $isRequestParcelado = in_array($requestedPaymentType, ['12x', 'prazo', 'parcelado', 'installments']);
    
    if ($isOrderParcelado !== $isRequestParcelado) {
        debugLog('AVISO: Método de pagamento inconsistente', [
            'requested' => $requestedPaymentType,
            'from_order' => $paymentMethodFromOrder,
            'order_parcelado' => $isOrderParcelado,
            'request_parcelado' => $isRequestParcelado
        ]);
    }
    
} catch (Exception $e) {
    debugLog('Erro ao carregar ordem', $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar ordem: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================
// LÓGICA DE NEGÓCIO
// ============================================
try {
    $orderId = $data['order_id'];
    $paymentType = $data['payment_type']; 
    $payerEmail = $data['payer_email'];
    $payerName = $data['payer_name'] ?? 'Cliente';
    
    // === USAR PREÇO CALCULADO PELO SERVIDOR (SEGURO) ===
    // O preço é recalculado baseado na seleção, não no que o frontend envia
    // Normalizar: aceitar múltiplos formatos de payment_type
    $isParcelado = in_array($paymentType, ['prazo', '12x', 'parcelado', 'installments']);
    
    if ($isParcelado) {
        $precoFinal = $pricing['parcelado_total']; // Preço parcelado
        $maxParcelas = 12;
        $descricao = "Site Completo UNLI - Parcelado Sem Juros";
    } else {
        $precoFinal = $pricing['avista']; // Preço à vista
        $maxParcelas = 1; 
        $descricao = "Site Completo UNLI - À Vista";
    }
    
    debugLog('Preço final determinado pelo servidor', [
        'payment_type' => $paymentType,
        'is_parcelado' => $isParcelado,
        'amount' => $precoFinal,
        'max_parcelas' => $maxParcelas,
        'source' => 'server_calculated'
    ]);
    
    $externalReference = "UNLI-" . $orderId . "-" . time();

    // === URL DE RETORNO (Success URL para Pagar.me) ===
    $domain = "https://unli.com.br"; 
    $rawUrl = defined('PAGARME_SUCCESS_URL') ? PAGARME_SUCCESS_URL : '/pagamento/validar'; 
    
    // Se a URL não começar com http, adicionamos o domínio
    if (strpos($rawUrl, 'http') !== 0) {
        $returnUrl = $domain . '/' . ltrim($rawUrl, '/');
    } else {
        $returnUrl = $rawUrl;
    }

    // Adiciona o order_id como parâmetro (interno)
    $separator = (strpos($returnUrl, '?') === false) ? '?' : '&';
    $finalReturnUrl = $returnUrl . $separator . "order_id=" . $orderId;

    debugLog("URL de Retorno Gerada: " . $finalReturnUrl);

    // ============================================
    // MONTAR PAYLOAD PAGAR.ME (API V5 - Orders + Checkout)
    // ============================================
    // Valores em CENTAVOS (Pagar.me exige inteiro em centavos)
    $amountInCents = (int) round($precoFinal * 100);
    
    // Configurar parcelas para cartão de crédito
    $installmentsConfig = [];
    if ($isParcelado && $maxParcelas > 1) {
        // Permitir parcelamento de 1 até $maxParcelas
        for ($i = 1; $i <= $maxParcelas; $i++) {
            $installmentsConfig[] = [
                "number" => $i,
                "total" => $amountInCents
            ];
        }
    } else {
        $installmentsConfig[] = [
            "number" => 1,
            "total" => $amountInCents
        ];
    }
    
    $pagarmeOrder = [
        "items" => [
            [
                "amount" => $amountInCents,
                "description" => $descricao,
                "quantity" => 1,
                "code" => $orderId
            ]
        ],
        "customer" => [
            "name" => $payerName,
            "email" => $payerEmail,
            "type" => "individual"
        ],
        "checkout" => [
            "expires_in" => 7200, // 2 horas
            "billing_address_editable" => false,
            "customer_editable" => true,
            "accepted_payment_methods" => ["credit_card", "pix", "boleto"],
            "credit_card" => [
                "installments" => $installmentsConfig,
                "statement_descriptor" => "UNLI SITES"
            ],
            "pix" => [
                "expires_in" => 3600 // PIX: 1 hora
            ],
            "boleto" => [
                "due_at" => date('Y-m-d\TH:i:s\Z', strtotime('+3 days')),
                "instructions" => "Pagamento referente ao site UNLI. Pedido: " . $orderId
            ],
            "success_url" => $finalReturnUrl,
            "skip_checkout_success_page" => true
        ],
        "metadata" => [
            "order_id" => $orderId,
            "external_reference" => $externalReference,
            "payment_type" => $paymentType
        ]
    ];

    debugLog('Payload Pagar.me montado', $pagarmeOrder);

    // ============================================
    // ENVIAR PARA PAGAR.ME API V5
    // ============================================
    $response = createPagarmeOrder($pagarmeOrder);
    
    if ($response['success']) {
        $pagarmeOrderId = $response['data']['id'];
        $paymentUrl = $response['data']['checkouts'][0]['payment_url'] ?? null;
        
        debugLog('Pedido Pagar.me criado com sucesso', [
            'pagarme_order_id' => $pagarmeOrderId,
            'payment_url' => $paymentUrl
        ]);
        
        // Salvar referência do Pagar.me no pedido local
        savePendingOrder($orderId, $externalReference, $precoFinal, $descricao, $payerEmail);
        
        // Atualizar ordem local com ID do Pagar.me
        update_order_status($orderId, 'awaiting_payment', [
            'pagarme_order_id' => $pagarmeOrderId,
            'payment_url' => $paymentUrl
        ]);
        
        if (!$paymentUrl) {
            throw new Exception('Pagar.me não retornou URL de pagamento');
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Link de pagamento criado com sucesso',
            'pagarme_order_id' => $pagarmeOrderId,
            'payment_url' => $paymentUrl,
            // COMPATIBILIDADE: manter init_point para frontend existente
            'init_point' => $paymentUrl,
            'external_reference' => $externalReference,
            'amount' => $precoFinal,
            'payment_type' => $paymentType
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
    } else {
        throw new Exception($response['message'] ?? 'Erro ao criar pedido no Pagar.me');
    }

} catch (Exception $e) {
    debugLog('ERRO CRÍTICO', $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

// ============================================
// FUNÇÃO: CRIAR PEDIDO NO PAGAR.ME (API V5)
// ============================================
function createPagarmeOrder($orderPayload) {
    $url = PAGARME_API_URL . '/orders';
    
    $ch = curl_init($url);
    
    if ($ch === false) {
        return ['success' => false, 'message' => 'Erro interno: cURL não disponível'];
    }
    
    // Autenticação Basic: Secret Key como usuário, senha vazia
    $authHeader = 'Basic ' . base64_encode(PAGARME_SECRET_KEY . ':');
    
    $headers = [
        'Content-Type: application/json',
        'Authorization: ' . $authHeader
    ];
    
    $timeout = defined('PAGARME_TIMEOUT') ? PAGARME_TIMEOUT : 30;
    $connectTimeout = defined('PAGARME_CONNECT_TIMEOUT') ? PAGARME_CONNECT_TIMEOUT : 10;
    
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($orderPayload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => $connectTimeout,
        CURLOPT_SSL_VERIFYPEER => defined('SSL_VERIFY_PEER') ? SSL_VERIFY_PEER : true,
        CURLOPT_SSL_VERIFYHOST => defined('SSL_VERIFY_HOST') ? SSL_VERIFY_HOST : 2,
        CURLOPT_USERAGENT => 'UNLI-Checkout/2.0-Pagarme'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    curl_close($ch);
    
    debugLog('Pagar.me API Response', [
        'http_code' => $httpCode,
        'curl_error' => $curlError,
        'response_preview' => substr($response, 0, 1000)
    ]);
    
    if ($curlError) {
        return ['success' => false, 'message' => "Erro de conexão com Pagar.me: $curlError"];
    }
    
    $responseData = json_decode($response, true);
    
    if (($httpCode === 200 || $httpCode === 201) && isset($responseData['id'])) {
        return ['success' => true, 'data' => $responseData];
    } else {
        // Extrair mensagem de erro detalhada do Pagar.me
        $errorMsg = 'Erro desconhecido do Pagar.me';
        if (isset($responseData['message'])) {
            $errorMsg = $responseData['message'];
        } elseif (isset($responseData['errors'])) {
            $errors = array_map(function($e) { return $e['message'] ?? $e['description'] ?? json_encode($e); }, $responseData['errors']);
            $errorMsg = implode('; ', $errors);
        }
        
        debugLog('Erro Pagar.me Detalhado', [
            'http_code' => $httpCode,
            'response' => $responseData
        ]);
        
        return ['success' => false, 'message' => "Erro Pagar.me ($httpCode): $errorMsg"];
    }
}

// ============================================
// FUNÇÃO: SALVAR PEDIDO (DB Opcional)
// ============================================
function savePendingOrder($orderId, $externalReference, $amount, $description, $payerEmail) {
    if (!defined('DB_HOST')) return; 
    
    try {
        $conn = getConnection();
        if (!$conn) return;

        $stmt = $conn->prepare("
            INSERT INTO orders (order_id, external_reference, amount, description, payer_email, status, created_at) 
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())
            ON DUPLICATE KEY UPDATE updated_at = NOW()
        ");
        $stmt->execute([$orderId, $externalReference, $amount, $description, $payerEmail]);
    } catch (Exception $e) {
        debugLog('Aviso DB', $e->getMessage());
    }
}