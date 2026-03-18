<?php
/**
 * Admin Panel - Dashboard API (Visão Global)
 * 
 * Endpoints:
 *   GET /api/admin/dashboard.php?action=overview   → KPIs e métricas gerais
 *   GET /api/admin/dashboard.php?action=charts      → Dados para gráficos
 *   GET /api/admin/dashboard.php?action=health      → Saúde do sistema
 */

ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error_log('[ADMIN-DASHBOARD] Fatal: ' . $err['message'] . ' in ' . $err['file'] . ':' . $err['line']);
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

switch ($action) {
    case 'overview':
        handle_overview($pdo, $prefix);
        break;
    case 'charts':
        handle_charts($pdo, $prefix);
        break;
    case 'health':
        handle_health($pdo, $prefix);
        break;
    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Ação inválida']);
}

function handle_overview($pdo, $prefix) {
    // Total de afiliados
    $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(is_active = 1) as active FROM {$prefix}affiliate_users");
    $affiliates = $stmt->fetch();
    
    // Total de SDRs
    $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(is_active = 1) as active FROM {$prefix}sdr_users");
    $sdrs = $stmt->fetch();
    
    // Receita total gerada por indicações
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_referrals,
            SUM(CASE WHEN status IN ('closed','completed','onboarding') THEN 1 ELSE 0 END) as closed_sales,
            COALESCE(SUM(sale_amount), 0) as total_revenue,
            COALESCE(SUM(commission_amount), 0) as total_commissions,
            COALESCE(SUM(CASE WHEN commission_paid = 0 AND commission_amount > 0 THEN commission_amount ELSE 0 END), 0) as pending_commissions
        FROM {$prefix}affiliate_referrals
    ");
    $referrals = $stmt->fetch();
    
    // Vendas diretas (SDR) - vendas com sdr_id
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_orders,
            COALESCE(SUM(CASE WHEN sdr_id IS NOT NULL THEN 1 ELSE 0 END), 0) as sdr_sales,
            COALESCE(SUM(CASE WHEN affiliate_id IS NOT NULL THEN 1 ELSE 0 END), 0) as affiliate_sales
        FROM {$prefix}orders
    ");
    $orders = $stmt->fetch();
    
    echo json_encode([
        'ok' => true,
        'data' => [
            'affiliates' => [
                'total' => (int)$affiliates['total'],
                'active' => (int)$affiliates['active'],
                'inactive' => (int)$affiliates['total'] - (int)$affiliates['active']
            ],
            'sdrs' => [
                'total' => (int)$sdrs['total'],
                'active' => (int)$sdrs['active']
            ],
            'revenue' => [
                'total_from_referrals' => (float)$referrals['total_revenue'],
                'total_commissions' => (float)$referrals['total_commissions'],
                'pending_commissions' => (float)$referrals['pending_commissions'],
                'closed_sales' => (int)$referrals['closed_sales']
            ],
            'orders' => [
                'total' => (int)$orders['total_orders'],
                'by_sdr' => (int)$orders['sdr_sales'],
                'by_affiliate' => (int)$orders['affiliate_sales']
            ]
        ]
    ]);
}

function handle_charts($pdo, $prefix) {
    // Receita dos últimos 12 meses
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as sales_count,
            COALESCE(SUM(sale_amount), 0) as revenue
        FROM {$prefix}affiliate_referrals 
        WHERE status IN ('closed','completed','onboarding')
          AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ");
    $monthlyRevenue = $stmt->fetchAll();
    
    // Top 10 afiliados por receita
    $stmt = $pdo->query("
        SELECT 
            a.id, CONCAT(a.first_name, ' ', a.last_name) as name, a.tier,
            a.total_sales_amount, a.commission_rate,
            COUNT(r.id) as total_referrals
        FROM {$prefix}affiliate_users a
        LEFT JOIN {$prefix}affiliate_referrals r ON r.affiliate_id = a.id
        GROUP BY a.id
        ORDER BY a.total_sales_amount DESC
        LIMIT 10
    ");
    $topAffiliates = $stmt->fetchAll();
    
    // Novos afiliados por semana (últimas 8 semanas)
    $stmt = $pdo->query("
        SELECT 
            YEARWEEK(created_at, 1) as week_num,
            MIN(DATE(created_at)) as week_start,
            COUNT(*) as new_affiliates
        FROM {$prefix}affiliate_users 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 8 WEEK)
        GROUP BY YEARWEEK(created_at, 1)
        ORDER BY week_num ASC
    ");
    $weeklyGrowth = $stmt->fetchAll();
    
    echo json_encode([
        'ok' => true,
        'data' => [
            'monthly_revenue' => $monthlyRevenue,
            'top_affiliates' => $topAffiliates,
            'weekly_growth' => $weeklyGrowth
        ]
    ]);
}

function handle_health($pdo, $prefix) {
    // Afiliados que não logaram nos últimos 30 dias
    $stmt = $pdo->query("
        SELECT COUNT(*) as inactive 
        FROM {$prefix}affiliate_users 
        WHERE is_active = 1 AND (last_login IS NULL OR last_login < DATE_SUB(NOW(), INTERVAL 30 DAY))
    ");
    $inactiveAffiliates = $stmt->fetch()['inactive'];
    
    // Novos afiliados esta semana
    $stmt = $pdo->query("
        SELECT COUNT(*) as cnt 
        FROM {$prefix}affiliate_users 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $newThisWeek = $stmt->fetch()['cnt'];
    
    // Comissões pendentes de pagamento
    $stmt = $pdo->query("
        SELECT COUNT(*) as cnt, COALESCE(SUM(commission_amount), 0) as total
        FROM {$prefix}affiliate_referrals 
        WHERE commission_paid = 0 AND commission_amount > 0
    ");
    $pendingPayments = $stmt->fetch();
    
    // Total de afiliados ativos
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM {$prefix}affiliate_users WHERE is_active = 1");
    $totalActive = $stmt->fetch()['cnt'];
    
    $inactivityRate = $totalActive > 0 ? round(($inactiveAffiliates / $totalActive) * 100, 1) : 0;
    
    echo json_encode([
        'ok' => true,
        'data' => [
            'new_affiliates_this_week' => (int)$newThisWeek,
            'inactive_affiliates_30d' => (int)$inactiveAffiliates,
            'inactivity_rate' => $inactivityRate,
            'pending_payments_count' => (int)$pendingPayments['cnt'],
            'pending_payments_total' => (float)$pendingPayments['total']
        ]
    ]);
}
