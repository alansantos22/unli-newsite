<?php
/**
 * GET /api/config.php
 * 
 * Retorna catálogo de produtos e configurações
 * para renderização da UI do configurador
 */

// CORS - Configuração segura
require_once __DIR__ . '/lib/cors.php';

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/lib/pricing.php';

try {
    $cfg = load_pricing_config(__DIR__ . '/pricing.json');
    
    // Retornar apenas dados necessários para UI
    // (Preços podem vir para transparência, mas não são "verdade")
    $response = [
        'ok' => true,
        'version' => $cfg['version'],
        'currency' => $cfg['currency'],
        'products' => $cfg['products'],
        'page_addons' => $cfg['page_addons'],
        'content_addons' => $cfg['content_addons'],
        'pricing_rules' => $cfg['pricing_rules'],
        'limits' => $cfg['limits']
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
