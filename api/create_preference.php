<?php
/**
 * ============================================
 * MERCADO PAGO - CHECKOUT PRO MVP SEGURO
 * Criação de Preferências de Pagamento
 * ============================================
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
// Isso evita discrepâncias entre o que foi salvo e o que vai para o Mercado Pago

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
    
    // Separar Nome e Sobrenome
    $parts = explode(' ', trim($payerName), 2);
    $firstName = $parts[0];
    $lastName = isset($parts[1]) ? $parts[1] : 'Cliente'; 

    // === CORREÇÃO DE URL (Back URLs) ===
    $domain = "https://unli.com.br"; 
    $rawUrl = defined('MP_SUCCESS_URL') ? MP_SUCCESS_URL : '/sucesso'; 
    
    // Se a URL não começar com http, adicionamos o domínio
    if (strpos($rawUrl, 'http') !== 0) {
        $returnUrl = $domain . '/' . ltrim($rawUrl, '/');
    } else {
        $returnUrl = $rawUrl;
    }

    // Adiciona o order_id como parâmetro
    $separator = (strpos($returnUrl, '?') === false) ? '?' : '&';
    $finalReturnUrl = $returnUrl . $separator . "order_id=" . $orderId;

    debugLog("URL de Retorno Gerada: " . $finalReturnUrl);

    // Montar preferência de pagamento
    $preference = [
        "items" => [
            [
                "title" => $descricao,
                "description" => "Site profissional desenvolvido pela UNLI",
                "quantity" => 1,
                "currency_id" => "BRL",
                "unit_price" => round($precoFinal, 2)
            ]
        ],
        "payer" => [
            "name" => $firstName,
            "surname" => $lastName,
            "email" => $payerEmail
        ],
        "back_urls" => [
            "success" => $finalReturnUrl,
            "failure" => $finalReturnUrl,
            "pending" => $finalReturnUrl
        ],
        "auto_return" => "approved",
        "external_reference" => $externalReference,
        "payment_methods" => [
            "installments" => $maxParcelas,
            "default_installments" => 1
        ],
        "statement_descriptor" => "UNLI SITES"
    ];

    debugLog('Preferência montada', $preference);

    // ============================================
    // ENVIAR PARA MERCADO PAGO
    // ============================================
    $response = createMercadoPagoPreference($preference);
    
    if ($response['success']) {
        debugLog('Preferência criada com sucesso', [
            'preference_id' => $response['data']['id']
        ]);
        
        savePendingOrder($orderId, $externalReference, $precoFinal, $descricao, $payerEmail);
        
        // IMPORTANTE: Em modo DEBUG/TESTE, usar sandbox_init_point
        // Em produção, usar init_point
        $checkoutUrl = DEBUG_MODE 
            ? ($response['data']['sandbox_init_point'] ?? $response['data']['init_point'])
            : $response['data']['init_point'];
        
        debugLog('URL de checkout selecionada', [
            'debug_mode' => DEBUG_MODE,
            'using_sandbox' => DEBUG_MODE,
            'url' => $checkoutUrl
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Preferência criada com sucesso',
            'preference_id' => $response['data']['id'],
            'init_point' => $checkoutUrl, // Retorna a URL correta baseado no modo
            'sandbox_init_point' => $response['data']['sandbox_init_point'] ?? null,
            'production_init_point' => $response['data']['init_point'],
            'is_sandbox' => DEBUG_MODE,
            'external_reference' => $externalReference,
            'amount' => $precoFinal,
            'payment_type' => $paymentType
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
    } else {
        throw new Exception($response['message'] ?? 'Erro ao criar preferência');
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
// FUNÇÃO: CRIAR PREFERÊNCIA NO MERCADO PAGO
// ============================================
function createMercadoPagoPreference($preference) {
    $url = "https://api.mercadopago.com/checkout/preferences";
    
    $ch = curl_init($url);
    
    if ($ch === false) {
        return ['success' => false, 'message' => 'Erro interno: cURL não disponível'];
    }
    
    $headers = [
        'Authorization: Bearer ' . MP_ACCESS_TOKEN,
        'Content-Type: application/json'
    ];
    
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($preference),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false 
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    curl_close($ch);
    
    if ($curlError) {
        return ['success' => false, 'message' => "Erro de conexão: $curlError"];
    }
    
    $responseData = json_decode($response, true);
    
    if (($httpCode === 200 || $httpCode === 201) && isset($responseData['id'])) {
        return ['success' => true, 'data' => $responseData];
    } else {
        $msg = $responseData['message'] ?? 'Erro desconhecido do Mercado Pago';
        debugLog('Erro MP Detalhado', $responseData);
        return ['success' => false, 'message' => "Erro MP ($httpCode): $msg"];
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