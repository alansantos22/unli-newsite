<?php
/**
 * Admin Panel - User Management API (Afiliados, SDRs, Clientes)
 * 
 * Endpoints:
 *   GET  ?action=list_affiliates       → Listar afiliados com filtros
 *   GET  ?action=get_affiliate&id=X    → Detalhes de um afiliado
 *   POST ?action=update_affiliate      → Editar afiliado (dados, PIX, status)
 *   POST ?action=override_tier         → Promover/rebaixar liga manualmente
 *   POST ?action=toggle_affiliate      → Ativar/desativar (banir) afiliado
 *
 *   GET  ?action=list_sdrs             → Listar SDRs
 *   POST ?action=create_sdr            → Criar novo SDR
 *   POST ?action=update_sdr            → Editar SDR
 *   POST ?action=reset_sdr_password    → Resetar senha do SDR
 *   POST ?action=toggle_sdr            → Ativar/desativar SDR
 *
 *   GET  ?action=list_clients          → Listar clientes (orders)
 */

ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error_log('[ADMIN-USERS] Fatal: ' . $err['message'] . ' in ' . $err['file'] . ':' . $err['line']);
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
    // Afiliados
    case 'list_affiliates':   handle_list_affiliates($pdo, $prefix); break;
    case 'get_affiliate':     handle_get_affiliate($pdo, $prefix); break;
    case 'update_affiliate':  handle_update_affiliate($pdo, $prefix, $admin); break;
    case 'override_tier':     handle_override_tier($pdo, $prefix, $admin); break;
    case 'toggle_affiliate':  handle_toggle_affiliate($pdo, $prefix, $admin); break;
    
    // SDRs
    case 'list_sdrs':         handle_list_sdrs($pdo, $prefix); break;
    case 'create_sdr':        handle_create_sdr($pdo, $prefix, $admin); break;
    case 'update_sdr':        handle_update_sdr($pdo, $prefix, $admin); break;
    case 'reset_sdr_password': handle_reset_sdr_password($pdo, $prefix, $admin); break;
    case 'toggle_sdr':        handle_toggle_sdr($pdo, $prefix, $admin); break;
    
    // Clientes
    case 'list_clients':      handle_list_clients($pdo, $prefix); break;
    case 'delete_clients':    handle_delete_clients($pdo, $prefix, $admin); break;
    
    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Ação inválida']);
}

// =============================================
// AFILIADOS
// =============================================

function handle_list_affiliates($pdo, $prefix) {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(10, (int)($_GET['limit'] ?? 25)));
    $offset = ($page - 1) * $limit;
    
    $where = [];
    $params = [];
    
    // Filtro por status
    $status = $_GET['status'] ?? '';
    if ($status === 'active') { $where[] = 'a.is_active = 1'; }
    elseif ($status === 'inactive') { $where[] = 'a.is_active = 0'; }
    
    // Filtro por liga
    $tier = $_GET['tier'] ?? '';
    if ($tier && preg_match('/^[a-z0-9_]+$/', $tier)) {
        $where[] = 'a.tier = ?';
        $params[] = $tier;
    }
    
    // Busca por nome/email
    $search = trim($_GET['search'] ?? '');
    if ($search) {
        $where[] = '(a.first_name LIKE ? OR a.last_name LIKE ? OR a.email LIKE ?)';
        $searchParam = '%' . $search . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Contagem total
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}affiliate_users a $whereClause");
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];
    
    // Dados paginados
    $stmt = $pdo->prepare("
        SELECT 
            a.id, a.first_name, a.last_name, a.email, a.whatsapp, a.pix_key,
            a.affiliate_hash, a.tier, a.commission_rate, a.total_sales_amount,
            a.is_active, a.created_at, a.last_login,
            COUNT(r.id) as total_referrals,
            SUM(CASE WHEN r.status IN ('closed','completed','onboarding') THEN 1 ELSE 0 END) as closed_referrals
        FROM {$prefix}affiliate_users a
        LEFT JOIN {$prefix}affiliate_referrals r ON r.affiliate_id = a.id
        $whereClause
        GROUP BY a.id
        ORDER BY a.created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);
    $affiliates = $stmt->fetchAll();
    
    echo json_encode([
        'ok' => true,
        'data' => $affiliates,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$total,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

function handle_get_affiliate($pdo, $prefix) {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'ID obrigatório']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, email, whatsapp, pix_key,
               affiliate_hash, tier, commission_rate, total_sales_amount,
               is_active, created_at, last_login
        FROM {$prefix}affiliate_users WHERE id = ?
    ");
    $stmt->execute([$id]);
    $affiliate = $stmt->fetch();
    
    if (!$affiliate) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Afiliado não encontrado']);
        return;
    }
    
    // Indicações
    $stmt = $pdo->prepare("
        SELECT id, lead_name, lead_email, status, sale_amount, commission_amount,
               commission_paid, commission_paid_at, created_at
        FROM {$prefix}affiliate_referrals 
        WHERE affiliate_id = ? ORDER BY created_at DESC
    ");
    $stmt->execute([$id]);
    $referrals = $stmt->fetchAll();
    
    // Histórico de tiers
    $stmt = $pdo->prepare("
        SELECT old_tier, new_tier, reason, sales_amount_at_change, created_at
        FROM {$prefix}affiliate_tier_history 
        WHERE affiliate_id = ? ORDER BY created_at DESC
    ");
    $stmt->execute([$id]);
    $tierHistory = $stmt->fetchAll();
    
    // Medalhas
    $stmt = $pdo->prepare("
        SELECT b.title, b.icon_emoji, b.type, ub.awarded_at, ub.awarded_by
        FROM {$prefix}affiliate_user_badges ub
        JOIN {$prefix}affiliate_badges b ON b.id = ub.badge_id
        WHERE ub.affiliate_id = ? ORDER BY ub.awarded_at DESC
    ");
    $stmt->execute([$id]);
    $badges = $stmt->fetchAll();
    
    echo json_encode([
        'ok' => true,
        'data' => [
            'affiliate' => $affiliate,
            'referrals' => $referrals,
            'tier_history' => $tierHistory,
            'badges' => $badges
        ]
    ]);
}

function handle_update_affiliate($pdo, $prefix, $admin) {
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
    
    // Buscar dados atuais
    $stmt = $pdo->prepare("SELECT * FROM {$prefix}affiliate_users WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetch();
    
    if (!$current) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Afiliado não encontrado']);
        return;
    }
    
    $updates = [];
    $params = [];
    $allowed = ['first_name', 'last_name', 'email', 'whatsapp', 'pix_key'];
    
    foreach ($allowed as $field) {
        if (isset($input[$field]) && $input[$field] !== $current[$field]) {
            $updates[] = "$field = ?";
            $params[] = trim($input[$field]);
        }
    }
    
    if (empty($updates)) {
        echo json_encode(['ok' => true, 'message' => 'Nenhuma alteração']);
        return;
    }
    
    $params[] = $id;
    $stmt = $pdo->prepare("UPDATE {$prefix}affiliate_users SET " . implode(', ', $updates) . " WHERE id = ?");
    $stmt->execute($params);
    
    admin_audit_log($admin['admin_id'], 'update_affiliate', 'affiliate', $id, 
        array_intersect_key($current, array_flip($allowed)),
        array_intersect_key($input, array_flip($allowed))
    );
    
    echo json_encode(['ok' => true, 'message' => 'Afiliado atualizado']);
}

function handle_override_tier($pdo, $prefix, $admin) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    $newTier = $input['tier'] ?? '';
    $reason = trim($input['reason'] ?? 'Override manual pelo admin');
    
    $validTiers = ['bronze_1','bronze_2','prata_1','prata_2','ouro_1','ouro_2','diamante_1','diamante_2'];
    
    if (!$id || !in_array($newTier, $validTiers)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'ID e tier válido são obrigatórios']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT id, tier, commission_rate, total_sales_amount FROM {$prefix}affiliate_users WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetch();
    
    if (!$current) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Afiliado não encontrado']);
        return;
    }
    
    if ($current['tier'] === $newTier) {
        echo json_encode(['ok' => true, 'message' => 'Já está nesta liga']);
        return;
    }
    
    // Buscar comissão da nova liga
    require_once __DIR__ . '/../affiliate/middleware.php';
    $tierConfig = get_tier_config();
    $newCommission = $tierConfig[$newTier]['commission'] ?? 8.00;
    
    $pdo->beginTransaction();
    try {
        // Atualizar afiliado
        $stmt = $pdo->prepare("
            UPDATE {$prefix}affiliate_users SET tier = ?, commission_rate = ? WHERE id = ?
        ");
        $stmt->execute([$newTier, $newCommission, $id]);
        
        // Registrar histórico
        $stmt = $pdo->prepare("
            INSERT INTO {$prefix}affiliate_tier_history 
            (affiliate_id, old_tier, new_tier, reason, sales_amount_at_change)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$id, $current['tier'], $newTier, $reason, $current['total_sales_amount']]);
        
        $pdo->commit();
    } catch (\Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro ao atualizar liga']);
        return;
    }
    
    admin_audit_log($admin['admin_id'], 'tier_override', 'affiliate', $id,
        ['tier' => $current['tier'], 'commission' => $current['commission_rate']],
        ['tier' => $newTier, 'commission' => $newCommission, 'reason' => $reason]
    );
    
    echo json_encode(['ok' => true, 'message' => "Liga atualizada para $newTier", 'new_commission' => $newCommission]);
}

function handle_toggle_affiliate($pdo, $prefix, $admin) {
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
    
    $stmt = $pdo->prepare("SELECT id, is_active, CONCAT(first_name, ' ', last_name) as name FROM {$prefix}affiliate_users WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetch();
    
    if (!$current) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Afiliado não encontrado']);
        return;
    }
    
    $newStatus = $current['is_active'] ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE {$prefix}affiliate_users SET is_active = ? WHERE id = ?");
    $stmt->execute([$newStatus, $id]);
    
    $actionLabel = $newStatus ? 'reativado' : 'desativado';
    admin_audit_log($admin['admin_id'], $newStatus ? 'activate_affiliate' : 'block_affiliate', 'affiliate', $id,
        ['is_active' => $current['is_active']],
        ['is_active' => $newStatus]
    );
    
    echo json_encode(['ok' => true, 'message' => "Afiliado {$current['name']} $actionLabel", 'is_active' => $newStatus]);
}

// =============================================
// SDRs
// =============================================

function handle_list_sdrs($pdo, $prefix) {
    $stmt = $pdo->query("
        SELECT 
            s.id, s.name, s.email, s.whatsapp, s.is_active, s.created_at, s.last_login,
            COUNT(o.id) as total_sales,
            COALESCE(SUM(o.total_amount), 0) as total_revenue
        FROM {$prefix}sdr_users s
        LEFT JOIN {$prefix}orders o ON o.sdr_id = s.id
        GROUP BY s.id
        ORDER BY s.created_at DESC
    ");
    $sdrs = $stmt->fetchAll();
    
    echo json_encode(['ok' => true, 'data' => $sdrs]);
}

function handle_create_sdr($pdo, $prefix, $admin) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
        return;
    }
    
    // Apenas super_admin e manager podem criar SDRs
    if ($admin['role'] === 'viewer') {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Permissão insuficiente']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $whatsapp = trim($input['whatsapp'] ?? '');
    
    if (!$name || !$email || !$password) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Nome, e-mail e senha são obrigatórios']);
        return;
    }
    
    if (strlen($password) < 8) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Senha deve ter no mínimo 8 caracteres']);
        return;
    }
    
    // Verificar email único
    $stmt = $pdo->prepare("SELECT id FROM {$prefix}sdr_users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['ok' => false, 'error' => 'E-mail já cadastrado']);
        return;
    }
    
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    
    $stmt = $pdo->prepare("
        INSERT INTO {$prefix}sdr_users (name, email, password_hash, whatsapp)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$name, $email, $passwordHash, $whatsapp]);
    $newId = $pdo->lastInsertId();
    
    admin_audit_log($admin['admin_id'], 'create_sdr', 'sdr', (int)$newId, null, 
        ['name' => $name, 'email' => $email]
    );
    
    echo json_encode(['ok' => true, 'message' => "SDR $name criado com sucesso", 'id' => (int)$newId]);
}

function handle_update_sdr($pdo, $prefix, $admin) {
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
    
    $stmt = $pdo->prepare("SELECT * FROM {$prefix}sdr_users WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetch();
    
    if (!$current) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'SDR não encontrado']);
        return;
    }
    
    $updates = [];
    $params = [];
    $allowed = ['name', 'email', 'whatsapp'];
    
    foreach ($allowed as $field) {
        if (isset($input[$field]) && $input[$field] !== $current[$field]) {
            $updates[] = "$field = ?";
            $params[] = trim($input[$field]);
        }
    }
    
    if (empty($updates)) {
        echo json_encode(['ok' => true, 'message' => 'Nenhuma alteração']);
        return;
    }
    
    $params[] = $id;
    $stmt = $pdo->prepare("UPDATE {$prefix}sdr_users SET " . implode(', ', $updates) . " WHERE id = ?");
    $stmt->execute($params);
    
    admin_audit_log($admin['admin_id'], 'update_sdr', 'sdr', $id);
    
    echo json_encode(['ok' => true, 'message' => 'SDR atualizado']);
}

function handle_reset_sdr_password($pdo, $prefix, $admin) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
        return;
    }
    
    if ($admin['role'] !== 'super_admin') {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Apenas super_admin pode resetar senhas']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    $newPassword = $input['new_password'] ?? '';
    
    if (!$id || strlen($newPassword) < 8) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'ID e nova senha (min 8 chars) obrigatórios']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT id, name FROM {$prefix}sdr_users WHERE id = ?");
    $stmt->execute([$id]);
    $sdr = $stmt->fetch();
    
    if (!$sdr) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'SDR não encontrado']);
        return;
    }
    
    $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE {$prefix}sdr_users SET password_hash = ? WHERE id = ?");
    $stmt->execute([$passwordHash, $id]);
    
    admin_audit_log($admin['admin_id'], 'reset_sdr_password', 'sdr', $id);
    
    echo json_encode(['ok' => true, 'message' => "Senha do SDR {$sdr['name']} resetada"]);
}

function handle_toggle_sdr($pdo, $prefix, $admin) {
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
    
    $stmt = $pdo->prepare("SELECT id, name, is_active FROM {$prefix}sdr_users WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetch();
    
    if (!$current) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'SDR não encontrado']);
        return;
    }
    
    $newStatus = $current['is_active'] ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE {$prefix}sdr_users SET is_active = ? WHERE id = ?");
    $stmt->execute([$newStatus, $id]);
    
    $actionLabel = $newStatus ? 'reativado' : 'desativado';
    admin_audit_log($admin['admin_id'], $newStatus ? 'activate_sdr' : 'deactivate_sdr', 'sdr', $id);
    
    echo json_encode(['ok' => true, 'message' => "SDR {$current['name']} $actionLabel", 'is_active' => $newStatus]);
}

// =============================================
// CLIENTES
// =============================================

function handle_list_clients($pdo, $prefix) {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(10, (int)($_GET['limit'] ?? 25)));
    $offset = ($page - 1) * $limit;
    
    // Verificar se customer_email existe na tabela orders
    $colCheck = $pdo->prepare("SHOW COLUMNS FROM {$prefix}orders LIKE 'customer_email'");
    $colCheck->execute();
    $hasEmail = $colCheck->fetch() ? true : false;
    
    $search = trim($_GET['search'] ?? '');
    $where = '';
    $params = [];
    
    if ($search) {
        $searchParam = '%' . $search . '%';
        if ($hasEmail) {
            $where = "WHERE o.customer_name LIKE ? OR o.customer_email LIKE ? OR o.company_name LIKE ?";
            $params = [$searchParam, $searchParam, $searchParam];
        } else {
            $where = "WHERE o.customer_name LIKE ? OR o.company_name LIKE ?";
            $params = [$searchParam, $searchParam];
        }
    }
    
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}orders o $where");
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];
    
    $emailCol = $hasEmail ? 'o.customer_email,' : '';
    $stmt = $pdo->prepare("
        SELECT 
            o.id, o.customer_name, $emailCol o.company_name,
            o.total_amount, o.payment_status, o.payment_method_manual,
            o.sdr_id, o.affiliate_id, o.created_at,
            s.name as sdr_name,
            CONCAT(a.first_name, ' ', a.last_name) as affiliate_name
        FROM {$prefix}orders o
        LEFT JOIN {$prefix}sdr_users s ON s.id = o.sdr_id
        LEFT JOIN {$prefix}affiliate_users a ON a.id = o.affiliate_id
        $where
        ORDER BY o.created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);
    $clients = $stmt->fetchAll();
    
    echo json_encode([
        'ok' => true,
        'data' => $clients,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$total,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

function handle_delete_clients($pdo, $prefix, $admin) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
        return;
    }

    // Apenas super_admin pode excluir
    if (($admin['role'] ?? '') !== 'super_admin') {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Permissão negada. Apenas super_admin pode excluir clientes.']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $ids = $input['ids'] ?? [];

    if (!is_array($ids) || empty($ids)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Lista de IDs obrigatória']);
        return;
    }

    // Garantir que todos os IDs são inteiros positivos
    $ids = array_values(array_filter(array_map('intval', $ids), fn($id) => $id > 0));

    if (empty($ids)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'IDs inválidos']);
        return;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("DELETE FROM {$prefix}orders WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $deleted = $stmt->rowCount();

    admin_audit_log($admin['admin_id'], 'delete_clients', 'order', null, null, ['ids' => $ids, 'count' => $deleted]);

    echo json_encode(['ok' => true, 'deleted' => $deleted, 'message' => "$deleted pedido(s) excluído(s) com sucesso"]);
}
