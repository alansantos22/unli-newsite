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

// CORS - Configuração segura
require_once __DIR__ . '/lib/cors.php';

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/lib/pricing.php';
require_once __DIR__ . '/lib/storage.php';
require_once __DIR__ . '/lib/database.php';

try {
    // Carregar configuração oficial
    $cfg = load_pricing_config(__DIR__ . '/pricing.json');
    
    // Ler input do cliente
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) {
        throw new Exception("Invalid request body");
    }
    
    // LOG: Input recebido
    error_log('========== order_create.php CHAMADA ==========');
    error_log('📥 [order_create] INPUT: ' . json_encode($body, JSON_PRETTY_PRINT));
    
    // 1. Normalizar seleções (aplicar whitelist)
    $selection = normalize_selection($body, $cfg);
    
    error_log('✅ [order_create] SELEÇÃO NORMALIZADA: ' . json_encode($selection, JSON_PRETTY_PRINT));
    
    // 2. RECALCULAR preço (não confiar no cliente)
    $pricing = compute_price($selection, $cfg);
    
    error_log('💰 [order_create] PREÇO CALCULADO: ' . json_encode($pricing, JSON_PRETTY_PRINT));
    
    // 3. Validar briefing
    $briefing = $body['briefing'] ?? [];
    
    error_log('📋 [order_create] VALIDANDO BRIEFING...');
    error_log('📋 [order_create] Briefing recebido: ' . json_encode($briefing, JSON_PRETTY_PRINT));
    
    // Normalizar campos para formato padrão antes da validação
    if (!isset($briefing['company_name'])) {
        $briefing['company_name'] = $briefing['name'] ?? 
                                   $briefing['full_name'] ?? 
                                   $briefing['customer_name'] ?? '';
    }
    
    if (!isset($briefing['email'])) {
        $briefing['email'] = $briefing['company_email'] ?? 
                            $briefing['customer_email'] ?? '';
    }
    
    if (!isset($briefing['whatsapp'])) {
        $briefing['whatsapp'] = $briefing['phone'] ?? 
                               $briefing['company_phone'] ?? 
                               $briefing['customer_phone'] ?? '';
    }
    
    error_log('📋 [order_create] Briefing normalizado: ' . json_encode($briefing, JSON_PRETTY_PRINT));
    
    // Usar validação simplificada para checkout (não onboarding completo)
    $briefingErrors = validate_checkout_briefing($briefing);
    if (!empty($briefingErrors)) {
        error_log('❌ [order_create] ERROS NO BRIEFING: ' . json_encode($briefingErrors));
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'errors' => $briefingErrors
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    error_log('✅ [order_create] Briefing válido');
    
    // 4. Sanitizar briefing
    $briefing = sanitize_briefing($briefing);
    
    error_log('🧹 [order_create] Briefing sanitizado: ' . json_encode($briefing, JSON_PRETTY_PRINT));
    
    // 5. Validar método de pagamento
    // CORREÇÃO: Aceitar múltiplos formatos e normalizar
    $rawPaymentMethod = $body['payment_method'] ?? 'avista';
    
    // Mapeamento de todos os formatos aceitos para formato interno
    $paymentMethodMap = [
        // Formato interno
        'avista' => 'avista',
        '12x' => '12x',
        // Formato do frontend
        'cash' => 'avista',
        'installments' => '12x',
        // Formato alternativo
        'prazo' => '12x',
        'parcelado' => '12x',
        'a_vista' => 'avista',
        'à vista' => 'avista'
    ];
    
    $paymentMethod = $paymentMethodMap[strtolower($rawPaymentMethod)] ?? 'avista';
    
    error_log('💳 [order_create] Método de pagamento recebido: ' . $rawPaymentMethod . ' -> normalizado para: ' . $paymentMethod);
    
    // 6. Gerar order_id e salvar
    error_log('🔢 [order_create] Gerando order_id...');
    $orderId = generate_order_id();
    error_log('✅ [order_create] Order ID gerado: ' . $orderId);
    
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
    
    error_log('📦 [order_create] Order preparado: ' . json_encode([
        'order_id' => $orderId,
        'status' => 'pending',
        'payment_method' => $paymentMethod,
        'subtotal' => $pricing['subtotal']
    ]));
    
    // Salvar no arquivo (compatibilidade)
    error_log('💾 [order_create] Salvando no arquivo...');
    if (!save_order($order)) {
        error_log('❌ [order_create] FALHA ao salvar no arquivo!');
        throw new Exception("Failed to save order to file");
    }
    error_log('✅ [order_create] Salvo no arquivo com sucesso');
    
    // 🔥 NOVO: Salvar no banco de dados MySQL
    error_log('💾 [order_create] Salvando no banco de dados...');
    $dbResult = save_order_to_db($order);
    if (!$dbResult) {
        error_log("⚠️ [order_create] Warning: Failed to save order to database: " . $orderId);
        // Não falhar completamente, continuar com o fluxo
    } else {
        error_log('✅ [order_create] Salvo no banco de dados com sucesso');
    }
    
    // 7. Retornar resumo (sem dados sensíveis)
    error_log('📊 [order_create] Preparando resposta final...');
    
    $response = [
        'ok' => true,
        'order_id' => $orderId,
        'status' => 'pending',
        'selection' => $selection,
        'pricing' => $pricing,
        'payment_method' => $paymentMethod,
        'next_step' => 'payment_create',
        'message' => 'Pedido criado com sucesso! Prossiga para pagamento.',
        'db_saved' => $dbResult !== false
    ];
    
    error_log('📝 [order_create] Response preparado: ' . json_encode([
        'ok' => true,
        'order_id' => $orderId,
        'db_saved' => $dbResult !== false
    ]));
    
    // Se salvou no DB, incluir o token de onboarding
    if ($dbResult !== false) {
        $response['onboarding_token'] = $dbResult['token'];
        $response['db_id'] = $dbResult['id'];
        error_log('🎫 [order_create] Token de onboarding incluído: ' . $dbResult['token']);
    }
    
    error_log('📤 [order_create] Enviando resposta JSON...');
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
    error_log('✅ [order_create] PEDIDO CRIADO COM SUCESSO: ' . $orderId);
    error_log('====================================================');
    
} catch (Throwable $e) { // Captura TODOS os erros, incluindo Fatal Errors
    error_log('❌ [order_create] FATAL ERROR CAPTURADO!');
    error_log('❌ [order_create] Tipo: ' . get_class($e));
    error_log('❌ [order_create] Mensagem: ' . $e->getMessage());
    error_log('❌ [order_create] Arquivo: ' . $e->getFile());
    error_log('❌ [order_create] Linha: ' . $e->getLine());
    error_log('🔍 [order_create] STACK TRACE:');
    error_log($e->getTraceAsString());
    error_log('====================================================');
    
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ], JSON_UNESCAPED_UNICODE);
}
