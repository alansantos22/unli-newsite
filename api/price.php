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
    
    // Normalizar e validar (aplicar whitelist e limites)
    $selection = normalize_selection($body, $cfg);
    
    // Calcular preço oficial (source of truth)
    $pricing = compute_price($selection, $cfg);
    
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
