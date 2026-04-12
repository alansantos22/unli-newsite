<?php
/**
 * Affiliate handler functions for admin panel.
 * Included by api/admin/panel-mgmt.php — do NOT access directly via HTTP.
 * Variables in scope: $pdo, $prefix, $action, $admin
 */

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
    // Path: api/lib/admin-handlers/ => ../../affiliate/middleware.php => api/affiliate/middleware.php
    require_once __DIR__ . '/../../affiliate/middleware.php';
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

// Dispatch
switch ($action) {
    case 'list_affiliates':  handle_list_affiliates($pdo, $prefix); break;
    case 'get_affiliate':    handle_get_affiliate($pdo, $prefix); break;
    case 'update_affiliate': handle_update_affiliate($pdo, $prefix, $admin); break;
    case 'override_tier':    handle_override_tier($pdo, $prefix, $admin); break;
    case 'toggle_affiliate': handle_toggle_affiliate($pdo, $prefix, $admin); break;
}
