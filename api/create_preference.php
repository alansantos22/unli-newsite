<?php
/**
 * ============================================
 * MERCADO PAGO - CHECKOUT PRO MVP SEGURO
 * Criação de Preferências de Pagamento
 * ============================================
 * 
 * Sistema simplificado que gera preferências
 * com lógica à vista vs prazo:
 * - À vista: R$ 599 (PIX + Cartão 1x)
 * - A prazo: R$ 688,85 (+15% sem juros até 12x)
 * 
 * @version 2.0.0 - MVP Seguro
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
    if (!DEBUG_MODE) return;
    
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
// LÓGICA DE NEGÓCIO: À VISTA vs A PRAZO
// ============================================
try {
    $orderId = $data['order_id'];
    $paymentType = $data['payment_type']; // 'vista' ou 'prazo'
    $payerEmail = $data['payer_email'];
    $payerName = $data['payer_name'] ?? 'Cliente';
    
    // Preço base do site
    $precoBase = 599.00;
    
    if ($paymentType === 'prazo') {
        // A PRAZO: +15% para absorver juros (parcelamento sem juros)
        $precoFinal = $precoBase * 1.15; // R$ 688,85
        $maxParcelas = 12;
        $descricao = "Site Completo UNLI - Parcelado Sem Juros";
        debugLog('Pagamento A PRAZO', [
            'preco_base' => $precoBase,
            'acrescimo' => '15%',
            'preco_final' => $precoFinal,
            'parcelas' => $maxParcelas
        ]);
    } else {
        // À VISTA: Preço original
        $precoFinal = $precoBase;
        $maxParcelas = 1; // BLOQUEIA parcelamento
        $descricao = "Site Completo UNLI - À Vista";
        debugLog('Pagamento À VISTA', [
            'preco_final' => $precoFinal,
            'parcelas' => 1
        ]);
    }
    
    // Gerar external_reference único
    $externalReference = "UNLI-" . $orderId . "-" . time();
    
    // Montar preferência de pagamento
    $preference = [
        "items" => [
            [
                "title" => $descricao,
                "description" => "Site profissional desenvolvido pela UNLI com todas as funcionalidades",
                "quantity" => 1,
                "currency_id" => "BRL",
                "unit_price" => round($precoFinal, 2) // Arredondar para evitar erros de float
            ]
        ],
        "payer" => [
            "name" => $payerName,
            "email" => $payerEmail
        ],
        "back_urls" => [
            "success" => MP_SUCCESS_URL . "?order_id=" . $orderId,
            "failure" => MP_SUCCESS_URL . "?order_id=" . $orderId,
            "pending" => MP_SUCCESS_URL . "?order_id=" . $orderId
        ],
        "auto_return" => "approved",
        "external_reference" => $externalReference,
        "expires" => true,
        "expiration_date_from" => date('c'),
        "expiration_date_to" => date('c', strtotime('+30 days')),
        
        // === A MÁGICA DO À VISTA vs A PRAZO ===
        "payment_methods" => [
            "installments" => $maxParcelas, // 1 = bloqueia / 12 = permite
            "default_installments" => 1
        ]
    ];

    debugLog('Preferência montada', $preference);

    // ============================================
    // ENVIAR PARA MERCADO PAGO
    // ============================================
    $response = createMercadoPagoPreference($preference);
    
    if ($response['success']) {
        debugLog('Preferência criada com sucesso', [
            'preference_id' => $response['data']['id'],
            'init_point' => $response['data']['init_point']
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Preferência criada com sucesso',
            'preference_id' => $response['data']['id'],
            'init_point' => $response['data']['init_point'],
            'sandbox_init_point' => $response['data']['sandbox_init_point'] ?? null,
            'external_reference' => $externalReference,
            'amount' => $precoFinal,
            'payment_type' => $paymentType
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
    } else {
        throw new Exception($response['message'] ?? 'Erro ao criar preferência');
    }

} catch (Exception $e) {
    debugLog('ERRO ao criar preferência', $e->getMessage());
    
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
    debugLog('Enviando preferência para Mercado Pago');
    
    // Inicializar cURL
    $ch = curl_init(MP_API_URL);
    
    if ($ch === false) {
        return ['success' => false, 'message' => 'Erro interno: cURL não disponível'];
    }
    
    // Configurar headers
    $headers = [
        'Authorization: Bearer ' . MP_ACCESS_TOKEN,
        'Content-Type: application/json'
    ];
    
    // Configurar cURL
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($preference),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => SSL_VERIFY_PEER,
        CURLOPT_SSL_VERIFYHOST => SSL_VERIFY_HOST,
        CURLOPT_USERAGENT => 'UNLI-CheckoutPro/2.0'
    ]);
    
    // Executar request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    curl_close($ch);
    
    // Verificar erro cURL
    if ($curlError) {
        debugLog('Erro cURL', $curlError);
        return ['success' => false, 'message' => "Erro de conexão: $curlError"];
    }
    
    // Verificar resposta
    if ($response === false) {
        return ['success' => false, 'message' => 'Falha na requisição'];
    }
    
    $responseData = json_decode($response, true);
    debugLog('Resposta do Mercado Pago', [
        'http_code' => $httpCode,
        'response' => $responseData
    ]);
    
    if ($httpCode === 201 && isset($responseData['id'])) {
        return ['success' => true, 'data' => $responseData];
    } else {
        $errorMessage = $responseData['message'] ?? 'Erro desconhecido';
        if (isset($responseData['cause'])) {
            $errorMessage .= ' - ' . json_encode($responseData['cause']);
        }
        return ['success' => false, 'message' => $errorMessage];
    }
}
?>

// ============================================
// FUNÇÃO: CRIAR PREFERÊNCIA NO MERCADO PAGO
// ============================================
function createMercadoPagoPreference($preference) {
    debugLog('Enviando preferência para Mercado Pago');
    
    // Inicializar cURL
    $ch = curl_init(MP_API_URL);
    
    if ($ch === false) {
        return ['success' => false, 'message' => 'Erro interno: cURL não disponível'];
    }
    
    // Configurar headers
    $headers = [
        'Authorization: Bearer ' . MP_ACCESS_TOKEN,
        'Content-Type: application/json'
    ];
    
    // Configurar cURL
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($preference),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => SSL_VERIFY_PEER,
        CURLOPT_SSL_VERIFYHOST => SSL_VERIFY_HOST,
        CURLOPT_USERAGENT => 'UNLI-CheckoutPro/1.0'
    ]);
    
    // Executar request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    curl_close($ch);
    
    // Verificar erro cURL
    if ($curlError) {
        debugLog('Erro cURL', $curlError);
        return ['success' => false, 'message' => "Erro de conexão: $curlError"];
    }
    
    // Verificar resposta
    if ($response === false) {
        return ['success' => false, 'message' => 'Falha na requisição'];
    }
    
    $responseData = json_decode($response, true);
    debugLog('Resposta do Mercado Pago', [
        'http_code' => $httpCode,
        'response' => $responseData
    ]);
    
    if ($httpCode === 201 && isset($responseData['id'])) {
        return ['success' => true, 'data' => $responseData];
    } else {
        $errorMessage = $responseData['message'] ?? 'Erro desconhecido';
        if (isset($responseData['cause'])) {
            $errorMessage .= ' - ' . json_encode($responseData['cause']);
        }
        return ['success' => false, 'message' => $errorMessage];
    }
}

// ============================================
// FUNÇÃO: SALVAR PEDIDO PENDENTE
// ============================================
function savePendingOrder($orderId, $externalReference, $amount, $description, $payerEmail) {
    try {
        if (!defined('DB_HOST')) {
            debugLog('Database não configurado, pulando salvamento');
            return;
        }
        
        $conn = getConnection();
        if (!$conn) {
            debugLog('Falha na conexão com banco de dados');
            return;
        }
        
        $stmt = $conn->prepare("
            INSERT INTO orders (
                order_id, external_reference, amount, description, 
                payer_email, status, created_at
            ) VALUES (?, ?, ?, ?, ?, 'pending', NOW())
            ON DUPLICATE KEY UPDATE
                external_reference = VALUES(external_reference),
                amount = VALUES(amount),
                updated_at = NOW()
        ");
        
        $stmt->execute([$orderId, $externalReference, $amount, $description, $payerEmail]);
        debugLog('Pedido salvo no banco de dados', ['order_id' => $orderId]);
        
    } catch (Exception $e) {
        debugLog('Erro ao salvar no banco', $e->getMessage());
        // Não falha a criação da preferência por erro no banco
    }
}
?>