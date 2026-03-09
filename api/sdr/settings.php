<?php
/**
 * SDR Panel - Settings API
 *
 * Endpoints:
 *   POST /api/sdr/settings.php?action=change_email    → Troca e-mail do SDR
 *   POST /api/sdr/settings.php?action=change_password → Troca senha do SDR
 */

ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level()) ob_end_clean();
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'Erro interno', 'debug' => $err['message']]);
    }
});

set_exception_handler(function ($e) {
    while (ob_get_level()) ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Exceção não tratada', 'debug' => $e->getMessage()]);
});

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

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'change_email':
        handle_change_email();
        break;
    case 'change_password':
        handle_change_password();
        break;
    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Ação inválida']);
}

// ============================================
// TROCAR E-MAIL
// ============================================

function handle_change_email() {
    $sdr = require_sdr_auth();

    $body  = json_decode(file_get_contents('php://input'), true);
    $email = trim($body['email'] ?? '');

    if (empty($email)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'E-mail é obrigatório']);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Formato de e-mail inválido']);
        return;
    }

    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro ao conectar ao banco']);
        return;
    }

    // Verificar se o e-mail já está em uso por outro SDR
    $stmt = $pdo->prepare("SELECT id FROM sdr_users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $sdr['sdr_id']]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['ok' => false, 'error' => 'Este e-mail já está em uso por outro SDR']);
        return;
    }

    $stmt = $pdo->prepare("UPDATE sdr_users SET email = ? WHERE id = ?");
    $stmt->execute([$email, $sdr['sdr_id']]);

    echo json_encode(['ok' => true, 'message' => 'E-mail atualizado com sucesso']);
}

// ============================================
// TROCAR SENHA
// ============================================

function handle_change_password() {
    $sdr = require_sdr_auth();

    $body         = json_decode(file_get_contents('php://input'), true);
    $current      = $body['current_password'] ?? '';
    $new_pass     = $body['new_password'] ?? '';
    $confirm_pass = $body['confirm_password'] ?? '';

    if (empty($current) || empty($new_pass) || empty($confirm_pass)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Todos os campos são obrigatórios']);
        return;
    }

    if ($new_pass !== $confirm_pass) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'A nova senha e a confirmação não coincidem']);
        return;
    }

    if (strlen($new_pass) < 8) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'A nova senha deve ter pelo menos 8 caracteres']);
        return;
    }

    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro ao conectar ao banco']);
        return;
    }

    $stmt = $pdo->prepare("SELECT password_hash FROM sdr_users WHERE id = ?");
    $stmt->execute([$sdr['sdr_id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Senha atual incorreta']);
        return;
    }

    $new_hash = password_hash($new_pass, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE sdr_users SET password_hash = ? WHERE id = ?");
    $stmt->execute([$new_hash, $sdr['sdr_id']]);

    echo json_encode(['ok' => true, 'message' => 'Senha atualizada com sucesso']);
}
