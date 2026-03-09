<?php
/**
 * SDR Panel - Authentication API
 * 
 * Endpoints:
 *   POST /api/sdr/auth.php?action=login    → Login com email/senha
 *   POST /api/sdr/auth.php?action=validate → Valida token JWT existente
 */

ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Capturar erros fatais e retornar como JSON
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level()) ob_end_clean();
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => false,
            'error' => 'Erro interno',
            'debug' => $err['message'] . ' em ' . $err['file'] . ':' . $err['line']
        ]);
    }
});

set_exception_handler(function($e) {
    while (ob_get_level()) ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => false,
        'error' => 'Exceção não tratada',
        'debug' => $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine()
    ]);
});

// Carregar configurações seguras
if (!defined('SECURE_CONFIG_ACCESS')) {
    define('SECURE_CONFIG_ACCESS', true);
}
$secureConfig = __DIR__ . '/../config.secure.php';
if (file_exists($secureConfig)) {
    require_once $secureConfig;
}

// CORS
require_once __DIR__ . '/../lib/cors.php';

ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/middleware.php';

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
    exit;
}

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
// LOGIN
// ============================================

function handle_login() {
    // Rate limiting
    if (!check_login_rate_limit()) {
        http_response_code(429);
        echo json_encode([
            'ok' => false,
            'error' => 'Muitas tentativas de login. Aguarde 15 minutos.'
        ]);
        return;
    }
    
    $body = json_decode(file_get_contents('php://input'), true);
    $email = trim($body['email'] ?? '');
    $password = $body['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        record_login_attempt();
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'E-mail e senha são obrigatórios']);
        return;
    }
    
    // Validar formato do email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        record_login_attempt();
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Formato de e-mail inválido']);
        return;
    }
    
    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro interno do servidor']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT id, name, email, password_hash, whatsapp, is_active FROM sdr_users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Sempre verificar hash mesmo se usuário não existir (previne timing attack)
    $hash = $user ? $user['password_hash'] : '$2y$10$dummyhashfortimingattak000000000000000000000000000000';
    
    if (!$user || !password_verify($password, $hash)) {
        record_login_attempt();
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'E-mail ou senha incorretos']);
        return;
    }
    
    if (!$user['is_active']) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Conta desativada. Contate o administrador.']);
        return;
    }
    
    // Sucesso - gerar JWT
    clear_login_attempts();
    
    $token = jwt_create([
        'sdr_id' => (int)$user['id'],
        'email' => $user['email'],
        'name' => $user['name']
    ]);
    
    // Atualizar last_login
    $stmt = $pdo->prepare("UPDATE sdr_users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    echo json_encode([
        'ok' => true,
        'token' => $token,
        'user' => [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'whatsapp' => $user['whatsapp']
        ]
    ]);
}

// ============================================
// VALIDATE TOKEN
// ============================================

function handle_validate() {
    $sdr = require_sdr_auth(); // Encerra com 401 se inválido
    
    echo json_encode([
        'ok' => true,
        'user' => $sdr
    ]);
}
