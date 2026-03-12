<?php
/**
 * Affiliate Panel - Authentication API
 * 
 * Endpoints:
 *   POST /api/affiliate/auth.php?action=register  → Cadastrar novo afiliado
 *   POST /api/affiliate/auth.php?action=login     → Login
 *   POST /api/affiliate/auth.php?action=validate  → Validar token
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

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        handle_register();
        break;
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
// REGISTRO
// ============================================

function handle_register() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
        return;
    }

    // Rate limit
    if (!check_login_rate_limit()) {
        http_response_code(429);
        echo json_encode(['ok' => false, 'error' => 'Muitas tentativas. Aguarde 15 minutos.']);
        return;
    }

    $body = json_decode(file_get_contents('php://input'), true) ?: [];

    // Validar campos obrigatórios
    $errors = [];
    
    $firstName = trim($body['first_name'] ?? '');
    $lastName = trim($body['last_name'] ?? '');
    $email = trim($body['email'] ?? '');
    $password = $body['password'] ?? '';
    $whatsapp = trim($body['whatsapp'] ?? '');
    $pixKey = trim($body['pix_key'] ?? '');

    if (mb_strlen($firstName) < 2) $errors[] = 'Primeiro nome deve ter pelo menos 2 caracteres';
    if (mb_strlen($lastName) < 2) $errors[] = 'Sobrenome deve ter pelo menos 2 caracteres';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'E-mail inválido';
    if (mb_strlen($password) < 6) $errors[] = 'Senha deve ter pelo menos 6 caracteres';
    if (mb_strlen($whatsapp) < 10) $errors[] = 'WhatsApp inválido';
    
    // Validar nome seguro
    if (!preg_match('/^[\p{L}\s\'-]+$/u', $firstName)) $errors[] = 'Primeiro nome contém caracteres inválidos';
    if (!preg_match('/^[\p{L}\s\'-]+$/u', $lastName)) $errors[] = 'Sobrenome contém caracteres inválidos';

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'errors' => $errors]);
        return;
    }

    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro de conexão com banco']);
        return;
    }

    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';

    // Verificar se email já existe
    $stmt = $pdo->prepare("SELECT id FROM {$prefix}affiliate_users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['ok' => false, 'error' => 'Este e-mail já está cadastrado']);
        return;
    }

    // Gerar hash único
    $affiliateHash = generate_affiliate_hash();
    
    // Garantir unicidade do hash
    $stmt = $pdo->prepare("SELECT id FROM {$prefix}affiliate_users WHERE affiliate_hash = ?");
    $stmt->execute([$affiliateHash]);
    while ($stmt->fetch()) {
        $affiliateHash = generate_affiliate_hash();
        $stmt->execute([$affiliateHash]);
    }

    // Sanitizar
    $firstName = htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');
    $lastName = htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8');
    $whatsapp = preg_replace('/[^0-9+() -]/', '', $whatsapp);
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    $tiers = get_tier_config();
    $initialTier = 'bronze_1';
    $initialCommission = $tiers[$initialTier]['commission'];

    $stmt = $pdo->prepare("
        INSERT INTO {$prefix}affiliate_users (
            first_name, last_name, email, password_hash, whatsapp, pix_key,
            affiliate_hash, tier, commission_rate, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $firstName,
        $lastName,
        $email,
        $passwordHash,
        $whatsapp,
        $pixKey ?: null,
        $affiliateHash,
        $initialTier,
        $initialCommission
    ]);

    $affiliateId = (int)$pdo->lastInsertId();

    // Registrar no histórico de tiers
    $stmt = $pdo->prepare("
        INSERT INTO {$prefix}affiliate_tier_history (affiliate_id, old_tier, new_tier, reason, sales_amount_at_change)
        VALUES (?, '', ?, 'Cadastro inicial', 0)
    ");
    $stmt->execute([$affiliateId, $initialTier]);

    // Gerar token JWT
    $token = jwt_create([
        'affiliate_id' => $affiliateId,
        'email' => $email,
        'role' => 'affiliate'
    ]);

    echo json_encode([
        'ok' => true,
        'token' => $token,
        'user' => [
            'id' => $affiliateId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'affiliate_hash' => $affiliateHash,
            'tier' => $initialTier,
            'tier_name' => $tiers[$initialTier]['name'],
            'commission_rate' => $initialCommission
        ],
        'message' => 'Cadastro realizado com sucesso!'
    ], JSON_UNESCAPED_UNICODE);
}

// ============================================
// LOGIN
// ============================================

function handle_login() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
        return;
    }

    // Rate limit
    if (!check_login_rate_limit()) {
        http_response_code(429);
        echo json_encode(['ok' => false, 'error' => 'Muitas tentativas. Aguarde 15 minutos.']);
        return;
    }

    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $email = trim($body['email'] ?? '');
    $password = $body['password'] ?? '';

    if (!$email || !$password) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'E-mail e senha são obrigatórios']);
        return;
    }

    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro de conexão com banco']);
        return;
    }

    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, email, password_hash, whatsapp, 
               affiliate_hash, tier, commission_rate, total_sales_amount, is_active
        FROM {$prefix}affiliate_users WHERE email = ?
    ");
    $stmt->execute([$email]);
    $affiliate = $stmt->fetch();

    if (!$affiliate || !password_verify($password, $affiliate['password_hash'])) {
        record_login_attempt();
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Credenciais inválidas']);
        return;
    }

    if (!$affiliate['is_active']) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Conta desativada. Entre em contato com o suporte.']);
        return;
    }

    // Atualizar último login
    $stmt = $pdo->prepare("UPDATE {$prefix}affiliate_users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$affiliate['id']]);

    $tiers = get_tier_config();
    $tierData = $tiers[$affiliate['tier']] ?? $tiers['bronze_1'];

    $token = jwt_create([
        'affiliate_id' => (int)$affiliate['id'],
        'email' => $affiliate['email'],
        'role' => 'affiliate'
    ]);

    echo json_encode([
        'ok' => true,
        'token' => $token,
        'user' => [
            'id' => (int)$affiliate['id'],
            'first_name' => $affiliate['first_name'],
            'last_name' => $affiliate['last_name'],
            'email' => $affiliate['email'],
            'whatsapp' => $affiliate['whatsapp'],
            'affiliate_hash' => $affiliate['affiliate_hash'],
            'tier' => $affiliate['tier'],
            'tier_name' => $tierData['name'],
            'tier_icon' => $tierData['icon'],
            'commission_rate' => (float)$affiliate['commission_rate'],
            'total_sales_amount' => (float)$affiliate['total_sales_amount']
        ]
    ], JSON_UNESCAPED_UNICODE);
}

// ============================================
// VALIDAR TOKEN
// ============================================

function handle_validate() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
        return;
    }

    $affiliate = require_affiliate_auth();
    $tiers = get_tier_config();
    $tierData = $tiers[$affiliate['tier']] ?? $tiers['bronze_1'];

    echo json_encode([
        'ok' => true,
        'user' => [
            'id' => $affiliate['affiliate_id'],
            'first_name' => $affiliate['first_name'],
            'last_name' => $affiliate['last_name'],
            'email' => $affiliate['email'],
            'whatsapp' => $affiliate['whatsapp'],
            'affiliate_hash' => $affiliate['affiliate_hash'],
            'tier' => $affiliate['tier'],
            'tier_name' => $tierData['name'],
            'tier_icon' => $tierData['icon'],
            'commission_rate' => $affiliate['commission_rate'],
            'total_sales_amount' => $affiliate['total_sales_amount']
        ]
    ], JSON_UNESCAPED_UNICODE);
}
