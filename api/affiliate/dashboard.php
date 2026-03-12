<?php
/**
 * Affiliate Panel - Dashboard API
 * 
 * Endpoints:
 *   GET /api/affiliate/dashboard.php              → Dados do dashboard
 *   GET /api/affiliate/dashboard.php?action=referrals&page=1 → Lista de indicações paginada
 *   GET /api/affiliate/dashboard.php?action=ranking          → Ranking de afiliados
 *   GET /api/affiliate/dashboard.php?action=tiers            → Configuração de tiers
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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
    exit;
}

$action = $_GET['action'] ?? 'summary';

switch ($action) {
    case 'summary':
        handle_summary();
        break;
    case 'referrals':
        handle_referrals();
        break;
    case 'ranking':
        handle_ranking();
        break;
    case 'tiers':
        handle_tiers();
        break;
    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Ação inválida']);
}

// ============================================
// RESUMO DO DASHBOARD
// ============================================

function handle_summary() {
    $affiliate = require_affiliate_auth();
    
    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro de conexão']);
        return;
    }

    $aid = $affiliate['affiliate_id'];
    $tiers = get_tier_config();
    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';

    // Total de indicações
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}affiliate_referrals WHERE affiliate_id = ?");
    $stmt->execute([$aid]);
    $totalReferrals = (int)$stmt->fetch()['total'];

    // Total de vendas fechadas
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total, COALESCE(SUM(sale_amount), 0) as amount
        FROM {$prefix}affiliate_referrals WHERE affiliate_id = ? AND status IN ('closed', 'onboarding', 'completed')
    ");
    $stmt->execute([$aid]);
    $salesData = $stmt->fetch();
    $totalSalesClosed = (int)$salesData['total'];
    $totalSalesAmount = (float)$salesData['amount'];

    // Comissão total pendente
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(commission_amount), 0) as pending
        FROM {$prefix}affiliate_referrals WHERE affiliate_id = ? AND commission_paid = 0 AND commission_amount > 0
    ");
    $stmt->execute([$aid]);
    $pendingCommission = (float)$stmt->fetch()['pending'];

    // Comissão total paga
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(commission_amount), 0) as paid
        FROM {$prefix}affiliate_referrals WHERE affiliate_id = ? AND commission_paid = 1
    ");
    $stmt->execute([$aid]);
    $paidCommission = (float)$stmt->fetch()['paid'];

    // Vendas nos últimos 3 meses (para manutenção de título)
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(sale_amount), 0) as amount
        FROM {$prefix}affiliate_referrals 
        WHERE affiliate_id = ? 
          AND status IN ('closed', 'onboarding', 'completed')
          AND created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
    ");
    $stmt->execute([$aid]);
    $last3MonthsSales = (float)$stmt->fetch()['amount'];

    // Verificar manutenção de título
    $currentTier = $affiliate['tier'];
    $currentTierConfig = $tiers[$currentTier] ?? $tiers['bronze_1'];
    $tierAtRisk = false;
    $tierMessage = null;

    if ($currentTier !== 'bronze_1' && $last3MonthsSales < $currentTierConfig['min_sales']) {
        $tierAtRisk = true;
        $amountNeeded = $currentTierConfig['min_sales'] - $last3MonthsSales;
        $tierMessage = "Atenção: você precisa vender mais R$ " . number_format($amountNeeded, 2, ',', '.') 
                     . " nos próximos meses para manter o título " . $currentTierConfig['name'];
    }

    // Próximo tier
    $nextTier = null;
    $tiersKeys = array_keys($tiers);
    $currentIdx = array_search($currentTier, $tiersKeys);
    if ($currentIdx !== false && $currentIdx < count($tiersKeys) - 1) {
        $nextTierKey = $tiersKeys[$currentIdx + 1];
        $nextTierConfig = $tiers[$nextTierKey];
        $nextTier = [
            'key' => $nextTierKey,
            'name' => $nextTierConfig['name'],
            'icon' => $nextTierConfig['icon'],
            'min_sales' => $nextTierConfig['min_sales'],
            'commission' => $nextTierConfig['commission'],
            'amount_needed' => max(0, $nextTierConfig['min_sales'] - $totalSalesAmount)
        ];
    }

    // Links de afiliado
    $baseUrl = defined('SITE_BASE_URL') ? SITE_BASE_URL : 'https://unli.com.br';
    $hash = $affiliate['affiliate_hash'];
    $links = [
        ['name' => 'Página Inicial',     'url' => "{$baseUrl}/?ref={$hash}",                   'page' => 'home'],
        ['name' => 'Consultoria',         'url' => "{$baseUrl}/consultoria-gamificacao?ref={$hash}", 'page' => 'consultoria'],
        ['name' => 'Site Vitrine',        'url' => "{$baseUrl}/site-vitrine?ref={$hash}",       'page' => 'site-vitrine'],
        ['name' => 'Configurador',        'url' => "{$baseUrl}/configurador?ref={$hash}",       'page' => 'configurador'],
    ];

    echo json_encode([
        'ok' => true,
        'affiliate' => [
            'id' => $affiliate['affiliate_id'],
            'first_name' => $affiliate['first_name'],
            'last_name' => $affiliate['last_name'],
            'hash' => $hash,
            'tier' => $currentTier,
            'tier_name' => $currentTierConfig['name'],
            'tier_icon' => $currentTierConfig['icon'],
            'commission_rate' => $affiliate['commission_rate'],
        ],
        'stats' => [
            'total_referrals' => $totalReferrals,
            'total_sales_closed' => $totalSalesClosed,
            'total_sales_amount' => $totalSalesAmount,
            'pending_commission' => $pendingCommission,
            'paid_commission' => $paidCommission,
            'last_3_months_sales' => $last3MonthsSales,
        ],
        'tier_status' => [
            'at_risk' => $tierAtRisk,
            'message' => $tierMessage,
            'next_tier' => $nextTier,
        ],
        'links' => $links
    ], JSON_UNESCAPED_UNICODE);
}

// ============================================
// LISTA DE INDICAÇÕES PAGINADA
// ============================================

function handle_referrals() {
    $affiliate = require_affiliate_auth();
    
    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro de conexão']);
        return;
    }

    $aid = $affiliate['affiliate_id'];
    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 10;
    $offset = ($page - 1) * $perPage;
    $statusFilter = $_GET['status'] ?? '';

    // Contar total
    $countSql = "SELECT COUNT(*) as total FROM {$prefix}affiliate_referrals WHERE affiliate_id = ?";
    $countParams = [$aid];
    
    if ($statusFilter && in_array($statusFilter, ['lead', 'contacted', 'negotiating', 'closed', 'onboarding', 'completed', 'lost'])) {
        $countSql .= " AND status = ?";
        $countParams[] = $statusFilter;
    }
    
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($countParams);
    $total = (int)$stmt->fetch()['total'];

    // Buscar indicações
    $sql = "
        SELECT id, lead_name, lead_email, lead_phone, status, 
               sale_amount, commission_rate, commission_amount, commission_paid,
               source_page, created_at, updated_at
        FROM {$prefix}affiliate_referrals 
        WHERE affiliate_id = ?
    ";
    $params = [$aid];
    
    if ($statusFilter && in_array($statusFilter, ['lead', 'contacted', 'negotiating', 'closed', 'onboarding', 'completed', 'lost'])) {
        $sql .= " AND status = ?";
        $params[] = $statusFilter;
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $referrals = $stmt->fetchAll();

    echo json_encode([
        'ok' => true,
        'referrals' => $referrals,
        'pagination' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => ceil($total / $perPage)
        ]
    ], JSON_UNESCAPED_UNICODE);
}

// ============================================
// RANKING DE AFILIADOS
// ============================================

function handle_ranking() {
    // Ranking é público para afiliados logados
    require_affiliate_auth();
    
    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro de conexão']);
        return;
    }

    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
    $stmt = $pdo->prepare("
        SELECT first_name, last_name, tier, commission_rate, total_sales_amount
        FROM {$prefix}affiliate_users 
        WHERE is_active = 1 AND total_sales_amount > 0
        ORDER BY total_sales_amount DESC
        LIMIT 50
    ");
    $stmt->execute();
    $ranking = $stmt->fetchAll();

    $tiers = get_tier_config();
    $result = [];
    $position = 1;
    
    foreach ($ranking as $row) {
        $tierData = $tiers[$row['tier']] ?? $tiers['bronze_1'];
        $result[] = [
            'position' => $position++,
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'tier' => $row['tier'],
            'tier_name' => $tierData['name'],
            'tier_icon' => $tierData['icon'],
            'score' => (float)$row['total_sales_amount']
        ];
    }

    echo json_encode([
        'ok' => true,
        'ranking' => $result
    ], JSON_UNESCAPED_UNICODE);
}

// ============================================
// CONFIGURAÇÃO DE TIERS (público para afiliados)
// ============================================

function handle_tiers() {
    $tiers = get_tier_config();
    
    $result = [];
    foreach ($tiers as $key => $data) {
        $result[] = [
            'key' => $key,
            'name' => $data['name'],
            'icon' => $data['icon'],
            'min_sales' => $data['min_sales'],
            'commission' => $data['commission']
        ];
    }

    echo json_encode([
        'ok' => true,
        'tiers' => $result
    ], JSON_UNESCAPED_UNICODE);
}
