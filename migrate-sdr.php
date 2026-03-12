<?php
/**
 * ============================================
 * MIGRATION: Sistema SDR - Painel de Vendas
 * ============================================
 * 
 * Acesse: https://seu-dominio.com/migrate-sdr.php?password=SdrMigrate2026
 * 
 * ⚠️ APAGUE ESTE ARQUIVO APÓS EXECUTAR!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================
// PROTEÇÃO COM SENHA
// ============================================
$MIGRATION_PASSWORD = 'SdrMigrate2026';

if (!isset($_GET['password']) || $_GET['password'] !== $MIGRATION_PASSWORD) {
    http_response_code(401);
    die('🚫 Acesso negado! Use: migrate-sdr.php?password=SUA_SENHA');
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

function fk_exists($pdo, $table, $fk) {
    try {
        $stmt = $pdo->prepare("
            SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ");
        $stmt->execute([DB_NAME, $table, $fk]);
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

$ordersTable = DB_PREFIX . 'orders';
$sdrTable    = DB_PREFIX . 'sdr_users';  // ex: unli_sdr_users
$errors = 0;

// ------------------------------------------
// 1. Criar tabela sdr_users (com prefixo)
// ------------------------------------------

// Se a tabela foi criada sem prefixo por uma execução anterior, renomear
if (table_exists($pdo, 'sdr_users') && !table_exists($pdo, $sdrTable)) {
    try {
        $pdo->exec("RENAME TABLE `sdr_users` TO `{$sdrTable}`");
        log_msg("Tabela sdr_users renomeada para {$sdrTable}", 'success');
    } catch (PDOException $e) {
        log_msg("Erro ao renomear tabela: " . $e->getMessage(), 'error');
        $errors++;
    }
}

if (!table_exists($pdo, $sdrTable)) {
    try {
        $pdo->exec("
            CREATE TABLE `{$sdrTable}` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(100) NOT NULL COMMENT 'Nome do SDR',
                `email` VARCHAR(150) NOT NULL COMMENT 'E-mail (usado para login)',
                `password_hash` VARCHAR(255) NOT NULL COMMENT 'Senha em bcrypt',
                `whatsapp` VARCHAR(20) DEFAULT NULL COMMENT 'WhatsApp do SDR',
                `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=ativo, 0=desativado',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `last_login` DATETIME DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE INDEX `idx_sdr_email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Usuários SDR do painel de vendas'
        ");
        log_msg("Tabela {$sdrTable} criada", 'success');
    } catch (PDOException $e) {
        log_msg("Erro ao criar {$sdrTable}: " . $e->getMessage(), 'error');
        $errors++;
    }
} else {
    log_msg("Tabela {$sdrTable} já existe, pulando...");
}

// ------------------------------------------
// 2. Adicionar colunas na tabela orders
// ------------------------------------------
if (!table_exists($pdo, $ordersTable)) {
    log_msg("Tabela {$ordersTable} NÃO encontrada! Execute primeiro a migration principal (api/migrate.php)", 'error');
    $errors++;
} else {
    log_msg("Tabela {$ordersTable} encontrada, verificando colunas...");

    $columns = [
        ['sdr_id',                  "INT(11) DEFAULT NULL COMMENT 'SDR que registrou a venda'",                  'payment_id'],
        ['company_name',            "VARCHAR(200) DEFAULT NULL COMMENT 'Nome da empresa'",                       'customer_name'],
        ['payment_method_manual',   "VARCHAR(50) DEFAULT NULL COMMENT 'Método de pagamento (manual)'",           'payment_status'],
        ['sdr_notes',               "TEXT DEFAULT NULL COMMENT 'Observações do SDR'",                            null],
        ['onboarding_email_sent_at',"DATETIME DEFAULT NULL COMMENT 'Quando o email de onboarding foi enviado'",  null],
    ];

    foreach ($columns as [$col, $definition, $after]) {
        if (!column_exists($pdo, $ordersTable, $col)) {
            try {
                $sql = "ALTER TABLE `{$ordersTable}` ADD COLUMN `{$col}` {$definition}";
                if ($after && column_exists($pdo, $ordersTable, $after)) {
                    $sql .= " AFTER `{$after}`";
                }
                $pdo->exec($sql);
                log_msg("Coluna {$col} adicionada em {$ordersTable}", 'success');
            } catch (PDOException $e) {
                log_msg("Erro ao adicionar coluna {$col}: " . $e->getMessage(), 'error');
                $errors++;
            }
        } else {
            log_msg("Coluna {$col} já existe em {$ordersTable}, pulando...");
        }
    }

    // Índice sdr_id
    if (!index_exists($pdo, $ordersTable, 'idx_sdr_id')) {
        try {
            $pdo->exec("ALTER TABLE `{$ordersTable}` ADD INDEX `idx_sdr_id` (`sdr_id`)");
            log_msg("Índice idx_sdr_id criado", 'success');
        } catch (PDOException $e) {
            log_msg("Erro ao criar índice: " . $e->getMessage(), 'error');
            $errors++;
        }
    } else {
        log_msg("Índice idx_sdr_id já existe, pulando...");
    }

    // Foreign key
    if (!fk_exists($pdo, $ordersTable, 'fk_orders_sdr')) {
        try {
            $pdo->exec("ALTER TABLE `{$ordersTable}` ADD CONSTRAINT `fk_orders_sdr` FOREIGN KEY (`sdr_id`) REFERENCES `{$sdrTable}`(`id`) ON DELETE SET NULL");
            log_msg("Foreign key fk_orders_sdr criada", 'success');
        } catch (PDOException $e) {
            log_msg("Erro ao criar FK (pode ser ignorado se a coluna já tem FK): " . $e->getMessage(), 'warning');
        }
    } else {
        log_msg("Foreign key fk_orders_sdr já existe, pulando...");
    }
}

// ------------------------------------------
// 3. Criar primeiro SDR (opcional via GET)
// ------------------------------------------
// Uso: migrate-sdr.php?password=SdrMigrate2026&create_sdr=1&sdr_name=Alan&sdr_email=alan@unli.com.br&sdr_pass=MinhaS3nha&sdr_whatsapp=5511968354238
if (isset($_GET['create_sdr']) && $_GET['create_sdr'] === '1') {
    $sdrName     = $_GET['sdr_name']     ?? '';
    $sdrEmail    = $_GET['sdr_email']    ?? '';
    $sdrPass     = $_GET['sdr_pass']     ?? '';
    $sdrWhatsapp = $_GET['sdr_whatsapp'] ?? '';

    if (empty($sdrName) || empty($sdrEmail) || empty($sdrPass)) {
        log_msg("Parâmetros faltando para criar SDR. Necessários: sdr_name, sdr_email, sdr_pass", 'error');
        $errors++;
    } else {
        // Verificar se já existe
        $stmt = $pdo->prepare("SELECT id FROM {$sdrTable} WHERE email = ?");
        $stmt->execute([$sdrEmail]);
        if ($stmt->rowCount() > 0) {
            // Se force=1, atualizar a senha
            if (isset($_GET['force']) && $_GET['force'] === '1') {
                try {
                    $hash = password_hash($sdrPass, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE {$sdrTable} SET password_hash = ?, name = ?, whatsapp = ? WHERE email = ?");
                    $stmt->execute([$hash, $sdrName, $sdrWhatsapp, $sdrEmail]);
                    log_msg("SDR '{$sdrName}' atualizado com nova senha! (email: {$sdrEmail})", 'success');
                } catch (PDOException $e) {
                    log_msg("Erro ao atualizar SDR: " . $e->getMessage(), 'error');
                    $errors++;
                }
            } else {
                log_msg("SDR com email {$sdrEmail} já existe em {$sdrTable}, pulando... (use &force=1 para atualizar senha)", 'warning');
            }
        } else {
            try {
                $hash = password_hash($sdrPass, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO {$sdrTable} (name, email, password_hash, whatsapp) VALUES (?, ?, ?, ?)");
                $stmt->execute([$sdrName, $sdrEmail, $hash, $sdrWhatsapp]);
                log_msg("SDR '{$sdrName}' criado com sucesso! (ID: " . $pdo->lastInsertId() . ")", 'success');
            } catch (PDOException $e) {
                log_msg("Erro ao criar SDR: " . $e->getMessage(), 'error');
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
    <title>Migration SDR | Unli</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #0f0f1a; color: #e5e7eb; padding: 2rem; min-height: 100vh; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { font-size: 1.5rem; margin-bottom: 1rem; color: #a78bfa; }
        .status { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 600; }
        .status.success { background: #065f4620; border: 1px solid #10b981; color: #10b981; }
        .status.partial { background: #f59e0b20; border: 1px solid #f59e0b; color: #f59e0b; }
        .log-item { padding: 0.5rem 0.75rem; border-bottom: 1px solid #1f2937; font-size: 0.875rem; font-family: monospace; }
        .log-item.success { color: #10b981; }
        .log-item.warning { color: #f59e0b; }
        .log-item.error { color: #ef4444; }
        .log-item.info { color: #9ca3af; }
        .logs { background: #1a1a2e; border-radius: 8px; overflow: hidden; margin-bottom: 1.5rem; }
        .tip { background: #1e1b4b; border: 1px solid #4c1d95; border-radius: 8px; padding: 1rem; font-size: 0.8rem; color: #c4b5fd; line-height: 1.6; }
        .tip code { background: #0f0f1a; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; }
        .danger { background: #7f1d1d30; border: 1px solid #ef4444; border-radius: 8px; padding: 1rem; margin-top: 1rem; color: #fca5a5; font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Migration SDR — Painel de Vendas</h1>

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

        <?php if (!isset($_GET['create_sdr'])): ?>
        <div class="tip">
            <strong>💡 Para criar o primeiro SDR, adicione na URL:</strong><br><br>
            <code>migrate-sdr.php?password=<?= $MIGRATION_PASSWORD ?>&create_sdr=1&sdr_name=NomeDoSDR&sdr_email=email@dominio.com&sdr_pass=SenhaForte123&sdr_whatsapp=5511999999999</code>
        </div>
        <?php endif; ?>

        <div class="danger">
            ⚠️ <strong>APAGUE ESTE ARQUIVO APÓS EXECUTAR!</strong> Ele contém acesso direto ao banco de dados.
        </div>
    </div>
</body>
</html>
