<?php
/**
 * POST /api/price.php
 * 
 * Recalcula preço com base nas seleções do cliente
 * FONTE DE VERDADE - O servidor calcula, o cliente aceita
 * 
 * Input: { product, pages, content }
 * Output: { normalized, pricing }
 */

// CORS - Configuração segura
require_once __DIR__ . '/lib/cors.php';

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/lib/pricing.php';

try {
    // Carregar configuração oficial
    $cfg = load_pricing_config(__DIR__ . '/pricing.json');
    
    // Ler input do cliente (não confiável)
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) {
        $body = [];
    }
    
    // LOG: Input recebido - COMPLETO
    error_log('========== API price.php CHAMADA ==========');
    error_log('📥 [price.php] INPUT RAW: ' . json_encode($body, JSON_PRETTY_PRINT));
    
    // Normalizar e validar (aplicar whitelist e limites)
    $selection = normalize_selection($body, $cfg);
    
    // LOG: Seleção normalizada - COMPLETO
    error_log('✅ [price.php] SELEÇÃO NORMALIZADA: ' . json_encode($selection, JSON_PRETTY_PRINT));
    
    // Calcular preço oficial (source of truth)
    $pricing = compute_price($selection, $cfg);
    
    // LOG: Preço calculado - RESUMO
    error_log('💰 [price.php] RESULTADO: subtotal=' . $pricing['subtotal'] . ', avista=' . $pricing['avista'] . ', parcelado=' . $pricing['parcelado_total']);
    error_log('====================================================');
    
    // Retornar seleções normalizadas + preços oficiais
    $response = [
        'ok' => true,
        'normalized' => $selection,
        'pricing' => $pricing,
        'timestamp' => time()
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
