<?php
/**
 * POST /api/order_create.php
 * 
 * Cria pedido no servidor e trava valores
 * RECALCULA preço do zero (não confia no cliente)
 * 
 * Input: { product, pages, content, briefing, payment_method }
 * Output: { order_id, pricing, ... }
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/lib/pricing.php';
require_once __DIR__ . '/lib/storage.php';

try {
    // Carregar configuração oficial
    $cfg = load_pricing_config(__DIR__ . '/pricing.json');
    
    // Ler input do cliente
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) {
        throw new Exception("Invalid request body");
    }
    
    // 1. Normalizar seleções (aplicar whitelist)
    $selection = normalize_selection($body, $cfg);
    
    // 2. RECALCULAR preço (não confiar no cliente)
    $pricing = compute_price($selection, $cfg);
    
    // 3. Validar briefing
    $briefing = $body['briefing'] ?? [];
    $briefingErrors = validate_briefing($briefing);
    if (!empty($briefingErrors)) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'errors' => $briefingErrors
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 4. Sanitizar briefing
    $briefing = sanitize_briefing($briefing);
    
    // 5. Validar método de pagamento
    $paymentMethod = $body['payment_method'] ?? 'avista';
    if (!in_array($paymentMethod, ['avista', '12x'])) {
        $paymentMethod = 'avista';
    }
    
    // 6. Gerar order_id e salvar
    $orderId = generate_order_id();
    
    $order = [
        'order_id' => $orderId,
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
        'selection' => $selection,
        'pricing' => $pricing,
        'payment_method' => $paymentMethod,
        'briefing' => $briefing,
        'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    if (!save_order($order)) {
        throw new Exception("Failed to save order");
    }
    
    // 7. Retornar resumo (sem dados sensíveis)
    $response = [
        'ok' => true,
        'order_id' => $orderId,
        'status' => 'pending',
        'selection' => $selection,
        'pricing' => $pricing,
        'payment_method' => $paymentMethod,
        'next_step' => 'payment_create',
        'message' => 'Pedido criado com sucesso! Prossiga para pagamento.'
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
