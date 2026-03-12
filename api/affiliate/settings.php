<?php
/**
 * Affiliate Panel - Settings API
 * 
 * Endpoints:
 *   POST /api/affiliate/settings.php?action=update_profile → Atualizar perfil
 *   POST /api/affiliate/settings.php?action=change_password → Trocar senha
 */

ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);

if (!defined('SECURE_CONFIG_ACCESS')) {
    define('SECURE_CONFIG_ACCESS', true);
}
$secureConfig = __DIR__ . '/../config.secure.php';
if (file_exists($secureConfig)) {
    require_once $secureConfig;
}

require_once __DIR__ . '/../lib/cors.php';

ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/middleware.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
    exit;
}

$affiliate = require_affiliate_auth();

$action = $_GET['action'] ?? '';
$body = json_decode(file_get_contents('php://input'), true) ?: [];

switch ($action) {
    case 'update_profile':
        handle_update_profile($affiliate, $body);
        break;
    case 'change_password':
        handle_change_password($affiliate, $body);
        break;
    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Ação inválida']);
}

function handle_update_profile($affiliate, $body) {
    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro de conexão']);
        return;
    }

    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
    $whatsapp = trim($body['whatsapp'] ?? '');
    $pixKey = trim($body['pix_key'] ?? '');

    if ($whatsapp && mb_strlen($whatsapp) < 10) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'WhatsApp inválido']);
        return;
    }

    $whatsapp = preg_replace('/[^0-9+() -]/', '', $whatsapp);

    $stmt = $pdo->prepare("
        UPDATE {$prefix}affiliate_users SET whatsapp = ?, pix_key = ? WHERE id = ?
    ");
    $stmt->execute([$whatsapp ?: $affiliate['whatsapp'], $pixKey ?: null, $affiliate['affiliate_id']]);

    echo json_encode(['ok' => true, 'message' => 'Perfil atualizado com sucesso']);
}

function handle_change_password($affiliate, $body) {
    $currentPassword = $body['current_password'] ?? '';
    $newPassword = $body['new_password'] ?? '';

    if (mb_strlen($newPassword) < 6) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Nova senha deve ter pelo menos 6 caracteres']);
        return;
    }

    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro de conexão']);
        return;
    }

    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
    // Verificar senha atual
    $stmt = $pdo->prepare("SELECT password_hash FROM {$prefix}affiliate_users WHERE id = ?");
    $stmt->execute([$affiliate['affiliate_id']]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Senha atual incorreta']);
        return;
    }

    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE {$prefix}affiliate_users SET password_hash = ? WHERE id = ?");
    $stmt->execute([$newHash, $affiliate['affiliate_id']]);

    echo json_encode(['ok' => true, 'message' => 'Senha alterada com sucesso']);
}
