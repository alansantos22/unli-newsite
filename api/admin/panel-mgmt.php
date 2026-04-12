<?php
/**
 * Admin Panel - User Management Router (Afiliados, SDRs, Clientes)
 * Thin router — logic lives in api/lib/admin-handlers/ (HTTP-blocked via .htaccess).
 *
 * Endpoints:
 *   GET  ?action=list_affiliates       → Listar afiliados com filtros
 *   GET  ?action=get_affiliate&id=X    → Detalhes de um afiliado
 *   POST ?action=update_affiliate      → Editar afiliado (dados, PIX, status)
 *   POST ?action=override_tier         → Promover/rebaixar liga manualmente
 *   POST ?action=toggle_affiliate      → Ativar/desativar (banir) afiliado
 *
 *   GET  ?action=list_sdrs             → Listar SDRs
 *   POST ?action=create_sdr            → Criar novo SDR
 *   POST ?action=update_sdr            → Editar SDR
 *   POST ?action=reset_sdr_password    → Resetar senha do SDR
 *   POST ?action=toggle_sdr            → Ativar/desativar SDR
 *
 *   GET  ?action=list_clients          → Listar clientes (orders)
 *   POST ?action=delete_clients        → Excluir pedidos (super_admin only)
 */

ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error_log('[ADMIN-USERS] Fatal: ' . $err['message'] . ' in ' . $err['file'] . ':' . $err['line']);
        while (ob_get_level()) ob_end_clean();
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'Erro interno']);
    }
});

if (!defined('SECURE_CONFIG_ACCESS')) {
    define('SECURE_CONFIG_ACCESS', true);
}
$secureConfig = __DIR__ . '/../config.secure.php';
if (file_exists($secureConfig)) {
    require_once $secureConfig;
}

require_once __DIR__ . '/../lib/cors.php';
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/middleware.php';

$admin = require_admin_auth();
$pdo = get_db_connection();
$prefix = defined('DB_PREFIX') ? DB_PREFIX : '';

$action = $_GET['action'] ?? '';

$affiliateActions = ['list_affiliates', 'get_affiliate', 'update_affiliate', 'override_tier', 'toggle_affiliate'];
$sdrActions       = ['list_sdrs', 'create_sdr', 'update_sdr', 'reset_sdr_password', 'toggle_sdr'];
$clientActions    = ['list_clients', 'delete_clients'];

if (in_array($action, $affiliateActions)) {
    require __DIR__ . '/../lib/admin-handlers/affiliates.php';
} elseif (in_array($action, $sdrActions)) {
    require __DIR__ . '/../lib/admin-handlers/sdrs.php';
} elseif (in_array($action, $clientActions)) {
    require __DIR__ . '/../lib/admin-handlers/clients.php';
} else {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Ação inválida']);
}

