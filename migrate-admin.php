<?php
/**
 * ============================================
 * MIGRATION: Super Admin - Torre de Controle
 * ============================================
 * 
 * 1. Criar tabelas:
 *    https://seu-dominio.com/migrate-admin.php?password=AdminMigrate2026
 * 
 * 2. Criar primeiro Super Admin:
 *    https://seu-dominio.com/migrate-admin.php?password=AdminMigrate2026&create_admin=1&admin_name=Alan+Santos&admin_email=admin@unli.com.br&admin_pass=SenhaForte123
 * 
 * ⚠️ APAGUE ESTE ARQUIVO APÓS EXECUTAR!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================
// PROTEÇÃO COM SENHA
// ============================================
$MIGRATION_PASSWORD = 'AdminMigrate2026';

if (!isset($_GET['password']) || !hash_equals($MIGRATION_PASSWORD, $_GET['password'])) {
    http_response_code(401);
    die('🚫 Acesso negado! Use: migrate-admin.php?password=SUA_SENHA');
}

// ============================================
// CONEXÃO - Carrega config do api/
// ============================================
define('DB_CONFIG_ACCESS', true);
$config_path = __DIR__ . '/api/db.config.php';

if (!file_exists($config_path)) {
    die('❌ Arquivo api/db.config.php não encontrado!');
}

require_once $config_path;

try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die('❌ Erro de conexão: ' . $e->getMessage());
}

// ============================================
// HELPERS
// ============================================
$logs = [];

function log_msg($msg, $type = 'info') {
    global $logs;
    $icon = $type === 'success' ? '✅' : ($type === 'warning' ? '⚠️' : ($type === 'error' ? '❌' : 'ℹ️'));
    $logs[] = ['type' => $type, 'msg' => "{$icon} {$msg}"];
}

function table_exists($pdo, $table) {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$table]);
    return $stmt->rowCount() > 0;
}

function column_exists($pdo, $table, $column) {
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
        $stmt->execute([$column]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

function index_exists($pdo, $table, $index) {
    try {
        $stmt = $pdo->query("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$index}'");
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

// ============================================
// MIGRATION START
// ============================================
$version = $pdo->query("SELECT VERSION()")->fetchColumn();
log_msg("Conectado ao MySQL {$version} - DB: " . DB_NAME);

$adminTable = DB_PREFIX . 'admin_users';
$auditTable = DB_PREFIX . 'admin_audit_log';
$errors = 0;

// ------------------------------------------
// 1. Criar tabela admin_users
// ------------------------------------------
if (!table_exists($pdo, $adminTable)) {
    try {
        $pdo->exec("
            CREATE TABLE `{$adminTable}` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(100) NOT NULL COMMENT 'Nome completo do admin',
                `email` VARCHAR(150) NOT NULL COMMENT 'E-mail (usado para login)',
                `password_hash` VARCHAR(255) NOT NULL COMMENT 'Senha em bcrypt',
                `role` ENUM('super_admin', 'manager', 'viewer') NOT NULL DEFAULT 'manager' COMMENT 'Nível de acesso',
                `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=ativo, 0=desativado',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `last_login` DATETIME DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE INDEX `idx_admin_email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
              COMMENT='Administradores do sistema com acesso ao painel Super Admin'
        ");
        log_msg("Tabela {$adminTable} criada", 'success');
    } catch (PDOException $e) {
        log_msg("Erro ao criar {$adminTable}: " . $e->getMessage(), 'error');
        $errors++;
    }
} else {
    log_msg("Tabela {$adminTable} já existe, pulando...");
}

// ------------------------------------------
// 2. Criar tabela admin_audit_log
// ------------------------------------------
if (!table_exists($pdo, $auditTable)) {
    try {
        $pdo->exec("
            CREATE TABLE `{$auditTable}` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `admin_id` INT(11) NOT NULL COMMENT 'ID do admin que executou a ação',
                `action` VARCHAR(100) NOT NULL COMMENT 'Tipo de ação',
                `target_type` VARCHAR(50) DEFAULT NULL COMMENT 'Tipo do alvo: affiliate, sdr, order, mission, badge',
                `target_id` INT(11) DEFAULT NULL COMMENT 'ID do registro afetado',
                `old_value` TEXT DEFAULT NULL COMMENT 'Valor anterior (JSON)',
                `new_value` TEXT DEFAULT NULL COMMENT 'Valor novo (JSON)',
                `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IP do admin',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_audit_admin` (`admin_id`),
                INDEX `idx_audit_action` (`action`),
                INDEX `idx_audit_target` (`target_type`, `target_id`),
                INDEX `idx_audit_date` (`created_at`),
                CONSTRAINT `fk_audit_admin` FOREIGN KEY (`admin_id`) REFERENCES `{$adminTable}`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
              COMMENT='Log de auditoria de todas as ações administrativas'
        ");
        log_msg("Tabela {$auditTable} criada", 'success');
    } catch (PDOException $e) {
        log_msg("Erro ao criar {$auditTable}: " . $e->getMessage(), 'error');
        $errors++;
    }
} else {
    log_msg("Tabela {$auditTable} já existe, pulando...");
}

// ------------------------------------------
// 3. Criar Super Admin (opcional via GET)
// ------------------------------------------
// Uso: migrate-admin.php?password=AdminMigrate2026&create_admin=1&admin_name=Alan+Santos&admin_email=admin@unli.com.br&admin_pass=SenhaForte123
if (isset($_GET['create_admin']) && $_GET['create_admin'] === '1') {
    $adminName  = $_GET['admin_name']  ?? '';
    $adminEmail = $_GET['admin_email'] ?? '';
    $adminPass  = $_GET['admin_pass']  ?? '';
    $adminRole  = $_GET['admin_role']  ?? 'super_admin';

    // Validar role
    $validRoles = ['super_admin', 'manager', 'viewer'];
    if (!in_array($adminRole, $validRoles)) {
        $adminRole = 'super_admin';
    }

    if (empty($adminName) || empty($adminEmail) || empty($adminPass)) {
        log_msg("Parâmetros faltando para criar admin. Necessários: admin_name, admin_email, admin_pass", 'error');
        $errors++;
    } elseif (strlen($adminPass) < 8) {
        log_msg("Senha muito curta! Mínimo 8 caracteres.", 'error');
        $errors++;
    } elseif (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        log_msg("E-mail inválido: {$adminEmail}", 'error');
        $errors++;
    } else {
        // Verificar se já existe
        $stmt = $pdo->prepare("SELECT id FROM `{$adminTable}` WHERE email = ?");
        $stmt->execute([$adminEmail]);

        if ($stmt->rowCount() > 0) {
            // Se force=1, atualizar a senha
            if (isset($_GET['force']) && $_GET['force'] === '1') {
                try {
                    $hash = password_hash($adminPass, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE `{$adminTable}` SET password_hash = ?, name = ?, role = ? WHERE email = ?");
                    $stmt->execute([$hash, $adminName, $adminRole, $adminEmail]);
                    log_msg("Admin '{$adminName}' atualizado com nova senha! (email: {$adminEmail}, role: {$adminRole})", 'success');
                } catch (PDOException $e) {
                    log_msg("Erro ao atualizar admin: " . $e->getMessage(), 'error');
                    $errors++;
                }
            } else {
                log_msg("Admin com email {$adminEmail} já existe, pulando... (use &force=1 para atualizar)", 'warning');
            }
        } else {
            try {
                $hash = password_hash($adminPass, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO `{$adminTable}` (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$adminName, $adminEmail, $hash, $adminRole]);
                log_msg("Super Admin '{$adminName}' criado com sucesso! (ID: " . $pdo->lastInsertId() . ", role: {$adminRole})", 'success');
            } catch (PDOException $e) {
                log_msg("Erro ao criar admin: " . $e->getMessage(), 'error');
                $errors++;
            }
        }
    }
}

// ============================================
// OUTPUT HTML
// ============================================
$status = $errors === 0 ? 'success' : 'partial';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migration Super Admin | Unli</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #0f0f1a; color: #e5e7eb; padding: 2rem; min-height: 100vh; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { font-size: 1.5rem; margin-bottom: 1rem; color: #f59e0b; }
        .status { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 600; }
        .status.success { background: #065f4620; border: 1px solid #10b981; color: #10b981; }
        .status.partial { background: #f59e0b20; border: 1px solid #f59e0b; color: #f59e0b; }
        .log-item { padding: 0.5rem 0.75rem; border-bottom: 1px solid #1f2937; font-size: 0.875rem; font-family: monospace; }
        .log-item.success { color: #10b981; }
        .log-item.warning { color: #f59e0b; }
        .log-item.error { color: #ef4444; }
        .log-item.info { color: #9ca3af; }
        .logs { background: #1a1a2e; border-radius: 8px; overflow: hidden; margin-bottom: 1.5rem; }
        .tip { background: #1c1917; border: 1px solid #92400e; border-radius: 8px; padding: 1rem; font-size: 0.8rem; color: #fbbf24; line-height: 1.6; }
        .tip code { background: #0f0f1a; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; }
        .danger { background: #7f1d1d30; border: 1px solid #ef4444; border-radius: 8px; padding: 1rem; margin-top: 1rem; color: #fca5a5; font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏰 Migration Super Admin — Torre de Controle</h1>

        <div class="status <?= $status ?>">
            <?php if ($status === 'success'): ?>
                ✅ Migration executada com sucesso! Nenhum erro.
            <?php else: ?>
                ⚠️ Migration executada com <?= $errors ?> erro(s). Verifique os logs abaixo.
            <?php endif; ?>
        </div>

        <div class="logs">
            <?php foreach ($logs as $log): ?>
                <div class="log-item <?= $log['type'] ?>"><?= htmlspecialchars($log['msg']) ?></div>
            <?php endforeach; ?>
        </div>

        <?php if (!isset($_GET['create_admin'])): ?>
        <div class="tip">
            <strong>💡 Para criar o primeiro Super Admin, adicione na URL:</strong><br><br>
            <code>migrate-admin.php?password=<?= htmlspecialchars($MIGRATION_PASSWORD) ?>&create_admin=1&admin_name=Alan+Santos&admin_email=admin@unli.com.br&admin_pass=SenhaForte123</code><br><br>
            <strong>Parâmetros opcionais:</strong><br>
            <code>&admin_role=super_admin</code> (super_admin | manager | viewer)<br>
            <code>&force=1</code> (atualizar senha se o email já existir)
        </div>
        <?php endif; ?>

        <div class="danger">
            ⚠️ <strong>APAGUE ESTE ARQUIVO APÓS EXECUTAR!</strong> Ele contém acesso direto ao banco de dados.
        </div>
    </div>
</body>
</html>
