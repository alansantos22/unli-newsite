<?php
/**
 * POST /api/payment_create.php
 * 
 * Cria pagamento no gateway com valor do servidor
 * BUSCA pedido salvo e usa o preço oficial
 * 
 * Input: { order_id }
 * Output: { payment_url, qr_code, ... }
 */

// CORS - Configuração segura
require_once __DIR__ . '/lib/cors.php';

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/lib/storage.php';

try {
    // Ler input do cliente
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) {
        throw new Exception("Invalid request body");
    }
    
    // 1. Validar order_id
    $orderId = $body['order_id'] ?? '';
    if (empty($orderId) || !preg_match('/^ORD-\d{8}-[A-Z0-9]{8}$/', $orderId)) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'Invalid order_id'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 2. Carregar pedido
    $order = load_order($orderId);
    if (!$order) {
        http_response_code(404);
        echo json_encode([
            'ok' => false,
            'error' => 'Order not found'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 3. Verificar se já foi pago
    if ($order['status'] === 'paid') {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'Order already paid'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 4. Extrair valor oficial do pedido
    $pricing = $order['pricing'];
    $paymentMethod = $order['payment_method'];
    
    $amount = ($paymentMethod === 'avista') 
        ? $pricing['avista'] 
        : $pricing['parcelado_total'];
    
    $description = sprintf(
        "Site Vitrine - %s - %s",
        $order['selection']['product'],
        $order['briefing']['company_name']
    );
    
    // 5. AQUI: Integrar com gateway real
    // Exemplos: Mercado Pago, PagSeguro, Stripe, Pagar.me
    
    // MOCK (substituir por integração real)
    $paymentData = create_payment_mock(
        $orderId,
        $amount,
        $description,
        $paymentMethod,
        $pricing['installments']
    );
    
    // 6. Atualizar status do pedido
    update_order_status($orderId, 'awaiting_payment', [
        'payment_id' => $paymentData['payment_id'],
        'payment_url' => $paymentData['payment_url']
    ]);
    
    // 7. Retornar dados do pagamento (para checkout integrado)
    $response = [
        'ok' => true,
        'order_id' => $orderId,
        'payment_id' => $paymentData['payment_id'],
        // Para checkout integrado - não retornar URL externa
        'checkout_data' => [
            'amount' => $amount,
            'description' => $description,
            'payment_method' => $paymentMethod,
            'installments' => $pricing['installments'],
            'external_reference' => $orderId
        ],
        'amount' => $amount,
        'payment_method' => $paymentMethod,
        'message' => 'Dados do pagamento preparados para checkout integrado'
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * MOCK - Simula criação de pagamento
 * SUBSTITUIR por integração real com gateway
 */
function create_payment_mock(
    string $orderId, 
    float $amount, 
    string $description,
    string $method,
    int $installments
): array {
    $paymentId = 'PAY-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
    
    // URL mock (em produção, seria a URL real do gateway)
    $paymentUrl = "https://payment-gateway.example.com/checkout/" . $paymentId;
    
    return [
        'payment_id' => $paymentId,
        'payment_url' => $paymentUrl,
        'qr_code' => null,
        'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
    ];
}

/**
 * EXEMPLO - Integração com Mercado Pago (comentado)
 */
/*
function create_mercadopago_payment($orderId, $amount, $description, $method, $installments) {
    require_once 'vendor/autoload.php'; // SDK do Mercado Pago
    
    MercadoPago\SDK::setAccessToken('YOUR_ACCESS_TOKEN');
    
    $preference = new MercadoPago\Preference();
    
    $item = new MercadoPago\Item();
    $item->title = $description;
    $item->quantity = 1;
    $item->unit_price = $amount;
    
    $preference->items = [$item];
    $preference->external_reference = $orderId;
    
    if ($method === '12x') {
        $preference->payment_methods = [
            'installments' => $installments
        ];
    }
    
    $preference->back_urls = [
        'success' => 'https://seusite.com/payment/success',
        'failure' => 'https://seusite.com/payment/failure',
        'pending' => 'https://seusite.com/payment/pending'
    ];
    
    $preference->save();
    
    return [
        'payment_id' => $preference->id,
        'payment_url' => $preference->init_point
    ];
}
*/
