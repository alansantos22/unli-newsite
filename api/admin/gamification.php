<?php
/**
 * Admin Panel - Gamification API (Missões & Medalhas)
 * 
 * Endpoints:
 *   GET  ?action=list_missions        → Listar missões
 *   POST ?action=create_mission       → Criar nova missão
 *   POST ?action=update_mission       → Editar missão
 *   POST ?action=toggle_mission       → Ativar/desativar missão
 *
 *   GET  ?action=list_badges          → Listar medalhas
 *   POST ?action=create_badge         → Criar nova medalha
 *   POST ?action=update_badge         → Editar medalha
 *   POST ?action=award_badge          → Conceder medalha a afiliado
 *   POST ?action=revoke_badge         → Revogar medalha de afiliado
 */

ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error_log('[ADMIN-GAMIFICATION] Fatal: ' . $err['message'] . ' in ' . $err['file'] . ':' . $err['line']);
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
    // Missões
    case 'list_missions':   handle_list_missions($pdo, $prefix); break;
    case 'create_mission':  handle_create_mission($pdo, $prefix, $admin); break;
    case 'update_mission':  handle_update_mission($pdo, $prefix, $admin); break;
    case 'toggle_mission':  handle_toggle_mission($pdo, $prefix, $admin); break;
    
    // Medalhas
    case 'list_badges':     handle_list_badges($pdo, $prefix); break;
    case 'create_badge':    handle_create_badge($pdo, $prefix, $admin); break;
    case 'update_badge':    handle_update_badge($pdo, $prefix, $admin); break;
    case 'award_badge':     handle_award_badge($pdo, $prefix, $admin); break;
    case 'revoke_badge':    handle_revoke_badge($pdo, $prefix, $admin); break;
    
    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Ação inválida']);
}

// =============================================
// MISSÕES
// =============================================

function handle_list_missions($pdo, $prefix) {
    $stmt = $pdo->query("
        SELECT 
            m.*,
            COUNT(DISTINCT mp.affiliate_id) as total_participants,
            SUM(mp.completed = 1) as total_completed
        FROM {$prefix}affiliate_missions m
        LEFT JOIN {$prefix}affiliate_mission_progress mp ON mp.mission_id = m.id
        GROUP BY m.id
        ORDER BY m.sort_order ASC, m.created_at DESC
    ");
    $missions = $stmt->fetchAll();
    
    echo json_encode(['ok' => true, 'data' => $missions]);
}

function handle_create_mission($pdo, $prefix, $admin) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $title = trim($input['title'] ?? '');
    $description = trim($input['description'] ?? '');
    $iconEmoji = trim($input['icon_emoji'] ?? '🎯');
    $category = $input['category'] ?? '';
    $targetValue = (float)($input['target_value'] ?? 0);
    $targetUnit = $input['target_unit'] ?? 'count';
    $rewardType = $input['reward_type'] ?? 'xp';
    $rewardValue = (float)($input['reward_value'] ?? 0);
    $frequency = $input['frequency'] ?? 'weekly';
    
    $validCategories = ['explorer', 'lead_hunter', 'closer', 'consistency'];
    $validFrequencies = ['weekly', 'monthly', 'one_time'];
    $validRewardTypes = ['xp', 'commission_bonus', 'badge'];
    $validUnits = ['count', 'currency', 'days'];
    
    if (!$title || !in_array($category, $validCategories) || $targetValue <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Título, categoria válida e meta > 0 obrigatórios']);
        return;
    }
    
    if (!in_array($frequency, $validFrequencies)) $frequency = 'weekly';
    if (!in_array($rewardType, $validRewardTypes)) $rewardType = 'xp';
    if (!in_array($targetUnit, $validUnits)) $targetUnit = 'count';
    
    $stmt = $pdo->prepare("
        INSERT INTO {$prefix}affiliate_missions 
        (title, description, icon_emoji, category, target_value, target_unit, reward_type, reward_value, frequency)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$title, $description, $iconEmoji, $category, $targetValue, $targetUnit, $rewardType, $rewardValue, $frequency]);
    $newId = $pdo->lastInsertId();
    
    admin_audit_log($admin['admin_id'], 'create_mission', 'mission', (int)$newId, null, ['title' => $title]);
    
    echo json_encode(['ok' => true, 'message' => "Missão '$title' criada", 'id' => (int)$newId]);
}

function handle_update_mission($pdo, $prefix, $admin) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'ID obrigatório']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT id FROM {$prefix}affiliate_missions WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Missão não encontrada']);
        return;
    }
    
    $updates = [];
    $params = [];
    $allowed = ['title', 'description', 'icon_emoji', 'category', 'target_value', 'target_unit', 'reward_type', 'reward_value', 'frequency', 'sort_order'];
    
    foreach ($allowed as $field) {
        if (isset($input[$field])) {
            $updates[] = "$field = ?";
            $params[] = $input[$field];
        }
    }
    
    if (empty($updates)) {
        echo json_encode(['ok' => true, 'message' => 'Nenhuma alteração']);
        return;
    }
    
    $params[] = $id;
    $stmt = $pdo->prepare("UPDATE {$prefix}affiliate_missions SET " . implode(', ', $updates) . " WHERE id = ?");
    $stmt->execute($params);
    
    admin_audit_log($admin['admin_id'], 'update_mission', 'mission', $id);
    
    echo json_encode(['ok' => true, 'message' => 'Missão atualizada']);
}

function handle_toggle_mission($pdo, $prefix, $admin) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'ID obrigatório']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT id, title, is_active FROM {$prefix}affiliate_missions WHERE id = ?");
    $stmt->execute([$id]);
    $mission = $stmt->fetch();
    
    if (!$mission) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Missão não encontrada']);
        return;
    }
    
    $newStatus = $mission['is_active'] ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE {$prefix}affiliate_missions SET is_active = ? WHERE id = ?");
    $stmt->execute([$newStatus, $id]);
    
    admin_audit_log($admin['admin_id'], $newStatus ? 'activate_mission' : 'deactivate_mission', 'mission', $id);
    
    $actionLabel = $newStatus ? 'ativada' : 'desativada';
    echo json_encode(['ok' => true, 'message' => "Missão '{$mission['title']}' $actionLabel", 'is_active' => $newStatus]);
}

// =============================================
// MEDALHAS
// =============================================

function handle_list_badges($pdo, $prefix) {
    $stmt = $pdo->query("
        SELECT 
            b.*,
            COUNT(ub.id) as total_awarded
        FROM {$prefix}affiliate_badges b
        LEFT JOIN {$prefix}affiliate_user_badges ub ON ub.badge_id = b.id
        GROUP BY b.id
        ORDER BY b.sort_order ASC, b.created_at DESC
    ");
    $badges = $stmt->fetchAll();
    
    echo json_encode(['ok' => true, 'data' => $badges]);
}

function handle_create_badge($pdo, $prefix, $admin) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $title = trim($input['title'] ?? '');
    $description = trim($input['description'] ?? '');
    $iconEmoji = trim($input['icon_emoji'] ?? '🏅');
    $imageUrl = trim($input['image_url'] ?? '');
    $type = $input['type'] ?? 'achievement';
    $criteriaKey = trim($input['criteria_key'] ?? '');
    $criteriaValue = isset($input['criteria_value']) ? (float)$input['criteria_value'] : null;
    
    $validTypes = ['event', 'achievement', 'legacy'];
    
    if (!$title || !in_array($type, $validTypes)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Título e tipo válido obrigatórios']);
        return;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO {$prefix}affiliate_badges 
        (title, description, image_url, icon_emoji, type, criteria_key, criteria_value)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$title, $description, $imageUrl ?: null, $iconEmoji, $type, $criteriaKey ?: null, $criteriaValue]);
    $newId = $pdo->lastInsertId();
    
    admin_audit_log($admin['admin_id'], 'create_badge', 'badge', (int)$newId, null, ['title' => $title]);
    
    echo json_encode(['ok' => true, 'message' => "Medalha '$title' criada", 'id' => (int)$newId]);
}

function handle_update_badge($pdo, $prefix, $admin) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'ID obrigatório']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT id FROM {$prefix}affiliate_badges WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Medalha não encontrada']);
        return;
    }
    
    $updates = [];
    $params = [];
    $allowed = ['title', 'description', 'image_url', 'icon_emoji', 'type', 'criteria_key', 'criteria_value', 'is_active', 'sort_order'];
    
    foreach ($allowed as $field) {
        if (isset($input[$field])) {
            $updates[] = "$field = ?";
            $params[] = $input[$field];
        }
    }
    
    if (empty($updates)) {
        echo json_encode(['ok' => true, 'message' => 'Nenhuma alteração']);
        return;
    }
    
    $params[] = $id;
    $stmt = $pdo->prepare("UPDATE {$prefix}affiliate_badges SET " . implode(', ', $updates) . " WHERE id = ?");
    $stmt->execute($params);
    
    admin_audit_log($admin['admin_id'], 'update_badge', 'badge', $id);
    
    echo json_encode(['ok' => true, 'message' => 'Medalha atualizada']);
}

function handle_award_badge($pdo, $prefix, $admin) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $affiliateId = (int)($input['affiliate_id'] ?? 0);
    $badgeId = (int)($input['badge_id'] ?? 0);
    $notes = trim($input['notes'] ?? '');
    
    if (!$affiliateId || !$badgeId) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'affiliate_id e badge_id obrigatórios']);
        return;
    }
    
    // Verificar se já possui
    $stmt = $pdo->prepare("
        SELECT id FROM {$prefix}affiliate_user_badges 
        WHERE affiliate_id = ? AND badge_id = ?
    ");
    $stmt->execute([$affiliateId, $badgeId]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['ok' => false, 'error' => 'Afiliado já possui esta medalha']);
        return;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO {$prefix}affiliate_user_badges 
        (affiliate_id, badge_id, awarded_by, notes)
        VALUES (?, ?, 'admin', ?)
    ");
    $stmt->execute([$affiliateId, $badgeId, $notes ?: null]);
    
    admin_audit_log($admin['admin_id'], 'award_badge', 'badge', $badgeId, null, 
        ['affiliate_id' => $affiliateId, 'badge_id' => $badgeId]
    );
    
    echo json_encode(['ok' => true, 'message' => 'Medalha concedida com sucesso']);
}

function handle_revoke_badge($pdo, $prefix, $admin) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $affiliateId = (int)($input['affiliate_id'] ?? 0);
    $badgeId = (int)($input['badge_id'] ?? 0);
    
    if (!$affiliateId || !$badgeId) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'affiliate_id e badge_id obrigatórios']);
        return;
    }
    
    $stmt = $pdo->prepare("
        DELETE FROM {$prefix}affiliate_user_badges 
        WHERE affiliate_id = ? AND badge_id = ?
    ");
    $stmt->execute([$affiliateId, $badgeId]);
    
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Medalha não encontrada para este afiliado']);
        return;
    }
    
    admin_audit_log($admin['admin_id'], 'revoke_badge', 'badge', $badgeId, 
        ['affiliate_id' => $affiliateId], null
    );
    
    echo json_encode(['ok' => true, 'message' => 'Medalha revogada']);
}
