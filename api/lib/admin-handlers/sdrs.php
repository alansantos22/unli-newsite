<?php
/**
 * SDR handler functions for admin panel.
 * Included by api/admin/panel-mgmt.php — do NOT access directly via HTTP.
 * Variables in scope: $pdo, $prefix, $action, $admin
 */

function handle_list_sdrs($pdo, $prefix) {
    try {
        $stmt = $pdo->query("
            SELECT 
                s.id, s.name, s.email, s.whatsapp, s.is_active, s.created_at, s.last_login,
                COUNT(o.id) as total_sales,
                COALESCE(SUM(o.total_amount), 0) as total_revenue
            FROM {$prefix}sdr_users s
            LEFT JOIN {$prefix}orders o ON o.sdr_id = s.id
            GROUP BY s.id, s.name, s.email, s.whatsapp, s.is_active, s.created_at, s.last_login
            ORDER BY s.created_at DESC
        ");
        $sdrs = $stmt->fetchAll();
        echo json_encode(['ok' => true, 'data' => $sdrs]);
    } catch (\Throwable $e) {
        error_log('[ADMIN-USERS] handle_list_sdrs error: ' . get_class($e) . ': ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro ao carregar SDRs: ' . $e->getMessage()]);
    }
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

// Dispatch
switch ($action) {
    case 'list_sdrs':          handle_list_sdrs($pdo, $prefix); break;
    case 'create_sdr':         handle_create_sdr($pdo, $prefix, $admin); break;
    case 'update_sdr':         handle_update_sdr($pdo, $prefix, $admin); break;
    case 'reset_sdr_password': handle_reset_sdr_password($pdo, $prefix, $admin); break;
    case 'toggle_sdr':         handle_toggle_sdr($pdo, $prefix, $admin); break;
}
