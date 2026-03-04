<?php
/**
 * GET /api/config.php
 * 
 * Retorna catálogo de produtos e configurações
 * para renderização da UI do configurador
 */

// Capturar qualquer output indesejado (warnings/notices)
ob_start();

// Garantir que erros PHP não sejam exibidos como HTML na response
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Carregar configurações seguras (define DEBUG_MODE para CORS)
if (!defined('SECURE_CONFIG_ACCESS')) {
    define('SECURE_CONFIG_ACCESS', true);
}
$secureConfig = __DIR__ . '/config.secure.php';
if (file_exists($secureConfig)) {
    require_once $secureConfig;
}

// CORS - Configuração segura
require_once __DIR__ . '/lib/cors.php';

// Limpar qualquer output gerado durante includes
ob_end_clean();

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
        'service_addons' => $cfg['service_addons'] ?? [],
        'predefined_packages' => $cfg['predefined_packages'] ?? [],
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
