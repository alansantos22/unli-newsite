<?php
/**
 * Admin Panel - Authentication API
 * 
 * Endpoints:
 *   POST /api/admin/auth.php?action=login    → Login com email/senha
 *   POST /api/admin/auth.php?action=validate → Valida token JWT existente
 */

ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error_log('[ADMIN-AUTH] Fatal: ' . $err['message'] . ' in ' . $err['file'] . ':' . $err['line']);
        while (ob_get_level()) ob_end_clean();
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => $err['message'] . ' (' . basename($err['file']) . ':' . $err['line'] . ')']);
    }
});

set_exception_handler(function($e) {
    while (ob_get_level()) ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')']);
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

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        handle_login();
        break;
    case 'validate':
        handle_validate();
        break;
    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Ação inválida']);
}

// ============================================
// Login
// ============================================
function handle_login() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
        return;
    }
    
    // Rate limiting
    if (!check_admin_login_rate_limit()) {
        http_response_code(429);
        echo json_encode(['ok' => false, 'error' => 'Muitas tentativas. Tente novamente em 30 minutos.']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    
    if (!$email || !$password) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'E-mail e senha são obrigatórios']);
        return;
    }
    
    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro de conexão']);
        return;
    }
    
    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
    $stmt = $pdo->prepare("
        SELECT id, name, email, password_hash, role, is_active 
        FROM {$prefix}admin_users WHERE email = ?
    ");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    
    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        record_admin_login_attempt();
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Credenciais inválidas']);
        return;
    }
    
    if (!$admin['is_active']) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Conta desativada']);
        return;
    }
    
    // Limpar rate limit
    clear_admin_login_attempts();
    
    // Atualizar last_login
    $stmt = $pdo->prepare("UPDATE {$prefix}admin_users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$admin['id']]);
    
    // Gerar JWT
    $token = jwt_create([
        'admin_id' => (int)$admin['id'],
        'role' => $admin['role'],
        'scope' => 'admin'
    ], 28800); // 8 horas
    
    // Log de auditoria
    admin_audit_log($admin['id'], 'login', 'admin', $admin['id']);
    
    echo json_encode([
        'ok' => true,
        'token' => $token,
        'user' => [
            'id' => (int)$admin['id'],
            'name' => $admin['name'],
            'email' => $admin['email'],
            'role' => $admin['role']
        ]
    ]);
}

// ============================================
// Validate Token
// ============================================
function handle_validate() {
    $admin = require_admin_auth();
    
    echo json_encode([
        'ok' => true,
        'user' => $admin
    ]);
}
