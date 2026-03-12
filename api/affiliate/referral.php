<?php
/**
 * Affiliate - Referral Management API (used by SDR)
 * 
 * Endpoints:
 *   POST /api/affiliate/referral.php?action=attach   → Vincular afiliado a um pedido (SDR)
 *   GET  /api/affiliate/referral.php?action=validate_hash&hash=xxx → Validar hash de afiliado
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
    case 'attach':
        handle_attach();
        break;
    case 'validate_hash':
        handle_validate_hash();
        break;
    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Ação inválida']);
}

// ============================================
// VINCULAR AFILIADO A PEDIDO (usado pelo SDR)
// ============================================

function handle_attach() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
        return;
    }

    // Requer auth SDR
    require_once __DIR__ . '/../sdr/middleware.php';
    $sdr = require_sdr_auth();

    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $orderId = (int)($body['order_id'] ?? 0);
    $affiliateHash = trim($body['affiliate_hash'] ?? '');

    if ($orderId <= 0 || !$affiliateHash) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'order_id e affiliate_hash são obrigatórios']);
        return;
    }

    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro de conexão']);
        return;
    }

    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';

    // Buscar afiliado pelo hash
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, commission_rate FROM {$prefix}affiliate_users WHERE affiliate_hash = ? AND is_active = 1");
    $stmt->execute([$affiliateHash]);
    $affiliate = $stmt->fetch();

    if (!$affiliate) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Código de afiliado inválido ou desativado']);
        return;
    }

    // Buscar pedido
    $stmt = $pdo->prepare("SELECT id, total_amount, affiliate_id FROM {$prefix}orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if (!$order) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Pedido não encontrado']);
        return;
    }

    if ($order['affiliate_id']) {
        http_response_code(409);
        echo json_encode(['ok' => false, 'error' => 'Este pedido já tem um afiliado vinculado']);
        return;
    }

    // Vincular afiliado ao pedido
    $stmt = $pdo->prepare("UPDATE {$prefix}orders SET affiliate_id = ?, affiliate_hash = ? WHERE id = ?");
    $stmt->execute([$affiliate['id'], $affiliateHash, $orderId]);

    // Criar referral
    $commissionAmount = ((float)$order['total_amount']) * ($affiliate['commission_rate'] / 100);

    $stmt = $pdo->prepare("
        INSERT INTO {$prefix}affiliate_referrals (
            affiliate_id, order_id, lead_name, status, sale_amount, 
            commission_rate, commission_amount, source_page, created_at
        ) VALUES (?, ?, ?, 'closed', ?, ?, ?, 'sdr', NOW())
    ");
    $stmt->execute([
        $affiliate['id'],
        $orderId,
        '', // lead_name será preenchido do pedido
        $order['total_amount'],
        $affiliate['commission_rate'],
        $commissionAmount
    ]);

    // Atualizar totais do afiliado
    $stmt = $pdo->prepare("
        UPDATE {$prefix}affiliate_users 
        SET total_sales_amount = total_sales_amount + ?
        WHERE id = ?
    ");
    $stmt->execute([$order['total_amount'], $affiliate['id']]);

    // Recalcular tier
    $stmt = $pdo->prepare("SELECT total_sales_amount FROM {$prefix}affiliate_users WHERE id = ?");
    $stmt->execute([$affiliate['id']]);
    $updatedAffiliate = $stmt->fetch();
    $newTier = calculate_tier((float)$updatedAffiliate['total_sales_amount']);
    
    $tiers = get_tier_config();
    $newCommission = $tiers[$newTier]['commission'];

    $stmt = $pdo->prepare("UPDATE {$prefix}affiliate_users SET tier = ?, commission_rate = ? WHERE id = ?");
    $stmt->execute([$newTier, $newCommission, $affiliate['id']]);

    echo json_encode([
        'ok' => true,
        'message' => 'Afiliado vinculado com sucesso!',
        'affiliate_name' => $affiliate['first_name'] . ' ' . $affiliate['last_name'],
        'commission_amount' => $commissionAmount,
        'commission_rate' => $affiliate['commission_rate']
    ], JSON_UNESCAPED_UNICODE);
}

// ============================================
// VALIDAR HASH DE AFILIADO (público)
// ============================================

function handle_validate_hash() {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
        return;
    }

    $hash = trim($_GET['hash'] ?? '');
    
    if (!$hash || !preg_match('/^[a-f0-9]{16}$/', $hash)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Hash inválido']);
        return;
    }

    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro de conexão']);
        return;
    }

    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM {$prefix}affiliate_users WHERE affiliate_hash = ? AND is_active = 1");
    $stmt->execute([$hash]);
    $affiliate = $stmt->fetch();

    if (!$affiliate) {
        echo json_encode(['ok' => false, 'valid' => false]);
        return;
    }

    echo json_encode([
        'ok' => true,
        'valid' => true,
        'affiliate_name' => $affiliate['first_name'] . ' ' . mb_substr($affiliate['last_name'], 0, 1) . '.'
    ], JSON_UNESCAPED_UNICODE);
}
