<?php
/**
 * Affiliate Panel - Badges & Missions API
 * 
 * Endpoints:
 *   GET  ?action=badges           → Medalhas do afiliado autenticado
 *   GET  ?action=all_badges       → Catálogo completo de medalhas
 *   GET  ?action=missions         → Missões da semana com progresso
 *   GET  ?action=profile&id=X     → Perfil público de um afiliado (badges + tier)
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

$action = $_GET['action'] ?? 'badges';

switch ($action) {
    case 'badges':
        handle_my_badges();
        break;
    case 'all_badges':
        handle_all_badges();
        break;
    case 'missions':
        handle_missions();
        break;
    case 'profile':
        handle_public_profile();
        break;
    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Ação inválida']);
}

// ============================================
// MEDALHAS DO AFILIADO AUTENTICADO
// ============================================

function handle_my_badges() {
    $affiliate = require_affiliate_auth();
    
    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro de conexão']);
        return;
    }

    $aid = $affiliate['affiliate_id'];
    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';

    $stmt = $pdo->prepare("
        SELECT b.id, b.title, b.description, b.image_url, b.icon_emoji, b.type,
               ub.awarded_at, ub.notes as award_notes
        FROM {$prefix}affiliate_user_badges ub
        JOIN {$prefix}affiliate_badges b ON b.id = ub.badge_id
        WHERE ub.affiliate_id = ? AND b.is_active = 1
        ORDER BY ub.awarded_at DESC
    ");
    $stmt->execute([$aid]);
    $badges = $stmt->fetchAll();

    echo json_encode([
        'ok' => true,
        'badges' => $badges,
        'total' => count($badges)
    ], JSON_UNESCAPED_UNICODE);
}

// ============================================
// CATÁLOGO COMPLETO DE MEDALHAS
// ============================================

function handle_all_badges() {
    require_affiliate_auth();
    
    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro de conexão']);
        return;
    }

    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';

    $stmt = $pdo->prepare("
        SELECT id, title, description, image_url, icon_emoji, type, criteria_key, criteria_value
        FROM {$prefix}affiliate_badges
        WHERE is_active = 1
        ORDER BY sort_order ASC, type ASC, title ASC
    ");
    $stmt->execute();
    $badges = $stmt->fetchAll();

    // Agrupar por tipo
    $grouped = ['event' => [], 'achievement' => [], 'legacy' => []];
    foreach ($badges as $badge) {
        $grouped[$badge['type']][] = $badge;
    }

    echo json_encode([
        'ok' => true,
        'badges' => $badges,
        'grouped' => $grouped,
        'total' => count($badges)
    ], JSON_UNESCAPED_UNICODE);
}

// ============================================
// MISSÕES DA SEMANA COM PROGRESSO
// ============================================

function handle_missions() {
    $affiliate = require_affiliate_auth();
    
    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro de conexão']);
        return;
    }

    $aid = $affiliate['affiliate_id'];
    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';

    // Calcular segunda-feira da semana atual
    $weekStart = get_current_week_start();

    // Buscar missões ativas com progresso do afiliado
    $stmt = $pdo->prepare("
        SELECT m.id, m.title, m.description, m.icon_emoji, m.category,
               m.target_value, m.target_unit, m.reward_type, m.reward_value, m.frequency,
               COALESCE(mp.current_value, 0) as current_value,
               COALESCE(mp.completed, 0) as completed,
               mp.completed_at,
               COALESCE(mp.reward_claimed, 0) as reward_claimed
        FROM {$prefix}affiliate_missions m
        LEFT JOIN {$prefix}affiliate_mission_progress mp 
            ON mp.mission_id = m.id 
            AND mp.affiliate_id = ? 
            AND mp.week_start = ?
        WHERE m.is_active = 1
        ORDER BY m.sort_order ASC, m.category ASC
    ");
    $stmt->execute([$aid, $weekStart]);
    $missions = $stmt->fetchAll();

    // Calcular progresso percentual
    foreach ($missions as &$mission) {
        $target = (float)$mission['target_value'];
        $current = (float)$mission['current_value'];
        $mission['progress_percent'] = $target > 0 ? min(100, round(($current / $target) * 100)) : 0;
    }
    unset($mission);

    // Agrupar por categoria
    $grouped = [
        'explorer' => [],
        'lead_hunter' => [],
        'closer' => [],
        'consistency' => []
    ];
    foreach ($missions as $mission) {
        $grouped[$mission['category']][] = $mission;
    }

    // Calcular dias restantes na semana
    $weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));
    $daysLeft = max(0, (int)((strtotime($weekEnd) - time()) / 86400) + 1);

    echo json_encode([
        'ok' => true,
        'missions' => $missions,
        'grouped' => $grouped,
        'week' => [
            'start' => $weekStart,
            'end' => $weekEnd,
            'days_left' => $daysLeft
        ],
        'total' => count($missions),
        'completed' => count(array_filter($missions, fn($m) => (int)$m['completed'] === 1))
    ], JSON_UNESCAPED_UNICODE);
}

// ============================================
// PERFIL PÚBLICO DE UM AFILIADO
// ============================================

function handle_public_profile() {
    require_affiliate_auth();
    
    $profileId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($profileId <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'ID do afiliado é obrigatório']);
        return;
    }

    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro de conexão']);
        return;
    }

    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
    $tiers = get_tier_config();

    // Buscar dados públicos do afiliado
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, tier, commission_rate, total_sales_amount, created_at
        FROM {$prefix}affiliate_users
        WHERE id = ? AND is_active = 1
    ");
    $stmt->execute([$profileId]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Afiliado não encontrado']);
        return;
    }

    $tierData = $tiers[$user['tier']] ?? $tiers['bronze_1'];

    // Buscar medalhas do afiliado
    $stmt = $pdo->prepare("
        SELECT b.id, b.title, b.description, b.image_url, b.icon_emoji, b.type,
               ub.awarded_at
        FROM {$prefix}affiliate_user_badges ub
        JOIN {$prefix}affiliate_badges b ON b.id = ub.badge_id
        WHERE ub.affiliate_id = ? AND b.is_active = 1
        ORDER BY ub.awarded_at DESC
    ");
    $stmt->execute([$profileId]);
    $badges = $stmt->fetchAll();

    // Calcular antiguidade
    $createdAt = new DateTime($user['created_at']);
    $now = new DateTime();
    $diff = $createdAt->diff($now);
    $memberSince = $diff->days;

    echo json_encode([
        'ok' => true,
        'profile' => [
            'id' => (int)$user['id'],
            'name' => $user['first_name'] . ' ' . $user['last_name'],
            'tier' => $user['tier'],
            'tier_name' => $tierData['name'],
            'tier_icon' => $tierData['icon'],
            'total_sales' => (float)$user['total_sales_amount'],
            'member_since' => $user['created_at'],
            'member_days' => $memberSince,
        ],
        'badges' => $badges,
        'badge_count' => count($badges)
    ], JSON_UNESCAPED_UNICODE);
}

// ============================================
// HELPERS
// ============================================

/**
 * Retorna a data da segunda-feira da semana atual (formato Y-m-d)
 */
function get_current_week_start() {
    $today = new DateTime();
    $dayOfWeek = (int)$today->format('N'); // 1=segunda, 7=domingo
    if ($dayOfWeek > 1) {
        $today->modify('-' . ($dayOfWeek - 1) . ' days');
    }
    return $today->format('Y-m-d');
}
