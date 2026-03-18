<?php
/**
 * Admin Panel - JWT Authentication Middleware
 * 
 * Reutiliza as funções JWT do SDR middleware, mas com auth functions
 * específicas para administradores com nível de privilégio máximo.
 */

if (!defined('DB_CONFIG_ACCESS')) {
    define('DB_CONFIG_ACCESS', true);
}

require_once __DIR__ . '/../db.config.php';
require_once __DIR__ . '/../lib/database.php';

// Reutilizar funções JWT do SDR
require_once __DIR__ . '/../sdr/middleware.php';

// ============================================
// Admin Authentication
// ============================================

/**
 * Autentica de forma obrigatória a requisição como admin.
 * Se falhar, envia 401 e encerra.
 * 
 * @param string|null $requiredRole Nível mínimo exigido (null = qualquer admin)
 * @return array Dados do admin
 */
function require_admin_auth($requiredRole = null) {
    $token = get_bearer_token();
    
    if (!$token) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Token não fornecido']);
        exit;
    }
    
    $payload = jwt_validate($token);
    
    if (!$payload || !isset($payload['admin_id'])) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Token inválido ou expirado']);
        exit;
    }
    
    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro de conexão com banco de dados']);
        exit;
    }
    
    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
    $stmt = $pdo->prepare("
        SELECT id, name, email, role, is_active
        FROM {$prefix}admin_users WHERE id = ?
    ");
    $stmt->execute([$payload['admin_id']]);
    $admin = $stmt->fetch();
    
    if (!$admin || !$admin['is_active']) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Conta admin desativada']);
        exit;
    }
    
    // Verificar nível de acesso se exigido
    if ($requiredRole) {
        $roleHierarchy = ['viewer' => 1, 'manager' => 2, 'super_admin' => 3];
        $userLevel = $roleHierarchy[$admin['role']] ?? 0;
        $requiredLevel = $roleHierarchy[$requiredRole] ?? 0;
        
        if ($userLevel < $requiredLevel) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Permissão insuficiente']);
            exit;
        }
    }
    
    return [
        'admin_id' => (int)$admin['id'],
        'name' => $admin['name'],
        'email' => $admin['email'],
        'role' => $admin['role']
    ];
}

// ============================================
// Admin Audit Log
// ============================================

/**
 * Registra uma ação administrativa no log de auditoria
 */
function admin_audit_log($adminId, $action, $targetType = null, $targetId = null, $oldValue = null, $newValue = null) {
    $pdo = get_db_connection();
    if (!$pdo) return false;
    
    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
    $stmt = $pdo->prepare("
        INSERT INTO {$prefix}admin_audit_log 
        (admin_id, action, target_type, target_id, old_value, new_value, ip_address)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    return $stmt->execute([
        $adminId,
        $action,
        $targetType,
        $targetId,
        $oldValue ? json_encode($oldValue) : null,
        $newValue ? json_encode($newValue) : null,
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);
}

// ============================================
// Admin Rate Limiting (Login)
// ============================================

function check_admin_login_rate_limit() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $limitFile = sys_get_temp_dir() . '/admin_login_' . md5($ip) . '.json';
    
    $data = ['attempts' => [], 'blocked_until' => 0];
    
    if (file_exists($limitFile)) {
        $raw = file_get_contents($limitFile);
        $data = json_decode($raw, true) ?: $data;
    }
    
    if ($data['blocked_until'] > time()) {
        return false;
    }
    
    $cutoff = time() - 900;
    $data['attempts'] = array_filter($data['attempts'], function($t) use ($cutoff) { return $t > $cutoff; });
    
    if (count($data['attempts']) >= 3) { // Admin: mais rigoroso - 3 tentativas
        $data['blocked_until'] = time() + 1800; // bloquear por 30 min
        file_put_contents($limitFile, json_encode($data));
        return false;
    }
    
    file_put_contents($limitFile, json_encode($data));
    return true;
}

function record_admin_login_attempt() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $limitFile = sys_get_temp_dir() . '/admin_login_' . md5($ip) . '.json';
    
    $data = ['attempts' => [], 'blocked_until' => 0];
    
    if (file_exists($limitFile)) {
        $raw = file_get_contents($limitFile);
        $data = json_decode($raw, true) ?: $data;
    }
    
    $data['attempts'][] = time();
    file_put_contents($limitFile, json_encode($data));
}

function clear_admin_login_attempts() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $limitFile = sys_get_temp_dir() . '/admin_login_' . md5($ip) . '.json';
    if (file_exists($limitFile)) {
        unlink($limitFile);
    }
}
