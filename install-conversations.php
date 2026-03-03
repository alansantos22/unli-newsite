<?php
/**
 * ============================================
 * INSTALADOR - Tabelas de Log de Conversas IA
 * ============================================
 * 
 * Acesse pelo navegador:
 * https://seu-dominio.com/install-conversations.php?password=Unli2026secure
 * 
 * Cria 4 tabelas:
 * - unli_sdr_conversations     (sessões do chat SDR pré-venda)
 * - unli_sdr_messages           (mensagens individuais SDR)
 * - unli_onboarding_conversations (sessões do chat onboarding)
 * - unli_onboarding_messages    (mensagens individuais onboarding)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================
// PROTEÇÃO COM SENHA
// ============================================
$INSTALL_PASSWORD = 'Unli2026secure';

if (!isset($_GET['password']) || $_GET['password'] !== $INSTALL_PASSWORD) {
    http_response_code(401);
    die('🚫 Acesso negado! Use: install-conversations.php?password=SUA_SENHA');
}

// ============================================
// CONEXÃO COM O BANCO
// ============================================
define('DB_CONFIG_ACCESS', true);
require_once __DIR__ . '/api/db.config.php';

$logs = [];

function log_msg($msg, $type = 'info') {
    global $logs;
    $logs[] = ['msg' => $msg, 'type' => $type];
}

try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    log_msg('✅ Conectado ao banco: ' . DB_NAME, 'success');
} catch (PDOException $e) {
    die('❌ Erro de conexão: ' . $e->getMessage());
}

$prefix = DB_PREFIX; // unli_

// ============================================
// VERIFICAR SE TABELA JÁ EXISTE
// ============================================
function table_exists($pdo, $name) {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$name]);
    return $stmt->rowCount() > 0;
}

// ============================================
// 1. TABELA: SDR_CONVERSATIONS
// ============================================
$t = $prefix . 'sdr_conversations';
if (table_exists($pdo, $t)) {
    log_msg("⏭️ Tabela {$t} já existe — pulando");
} else {
    try {
        $pdo->exec("CREATE TABLE `{$t}` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `conversation_id` VARCHAR(36) NOT NULL COMMENT 'UUID público',
            `ip_address` VARCHAR(45) NOT NULL COMMENT 'IP do visitante',
            `user_agent` VARCHAR(500) DEFAULT NULL,
            `customer_name` VARCHAR(255) DEFAULT NULL COMMENT 'Preenchido no checkout',
            `customer_email` VARCHAR(255) DEFAULT NULL COMMENT 'Preenchido no checkout',
            `customer_phone` VARCHAR(50) DEFAULT NULL,
            `last_stage` VARCHAR(30) DEFAULT 'ABERTURA',
            `client_data` JSON DEFAULT NULL,
            `suggested_plan` JSON DEFAULT NULL,
            `finished` TINYINT(1) DEFAULT 0,
            `order_id` INT(11) DEFAULT NULL COMMENT 'FK pedido criado',
            `total_messages` INT(11) DEFAULT 0,
            `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_conversation_id` (`conversation_id`),
            KEY `idx_ip` (`ip_address`),
            KEY `idx_email` (`customer_email`),
            KEY `idx_order_id` (`order_id`),
            KEY `idx_stage` (`last_stage`),
            KEY `idx_finished` (`finished`),
            KEY `idx_started_at` (`started_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        log_msg("✅ Tabela {$t} criada!", 'success');
    } catch (PDOException $e) {
        log_msg("❌ Erro ao criar {$t}: " . $e->getMessage(), 'error');
    }
}

// ============================================
// 2. TABELA: SDR_MESSAGES
// ============================================
$t = $prefix . 'sdr_messages';
$convTable = $prefix . 'sdr_conversations';
if (table_exists($pdo, $t)) {
    log_msg("⏭️ Tabela {$t} já existe — pulando");
} else {
    try {
        $pdo->exec("CREATE TABLE `{$t}` (
            `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
            `conversation_id` INT(11) NOT NULL COMMENT 'FK conversa SDR',
            `role` ENUM('user','assistant') NOT NULL,
            `content` TEXT NOT NULL,
            `stage` VARCHAR(30) DEFAULT NULL,
            `metadata` JSON DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_conversation` (`conversation_id`),
            KEY `idx_role` (`role`),
            KEY `idx_created_at` (`created_at`),
            CONSTRAINT `fk_sdr_msg_conv` FOREIGN KEY (`conversation_id`)
                REFERENCES `{$convTable}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        log_msg("✅ Tabela {$t} criada!", 'success');
    } catch (PDOException $e) {
        log_msg("❌ Erro ao criar {$t}: " . $e->getMessage(), 'error');
    }
}

// ============================================
// 3. TABELA: ONBOARDING_CONVERSATIONS
// ============================================
$t = $prefix . 'onboarding_conversations';
if (table_exists($pdo, $t)) {
    log_msg("⏭️ Tabela {$t} já existe — pulando");
} else {
    try {
        $pdo->exec("CREATE TABLE `{$t}` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `conversation_id` VARCHAR(36) NOT NULL COMMENT 'UUID público',
            `order_id` INT(11) DEFAULT NULL,
            `onboarding_token` VARCHAR(64) DEFAULT NULL,
            `customer_name` VARCHAR(255) DEFAULT NULL,
            `customer_email` VARCHAR(255) DEFAULT NULL,
            `plan_name` VARCHAR(100) DEFAULT NULL,
            `purchased_pages` JSON DEFAULT NULL,
            `finished` TINYINT(1) DEFAULT 0,
            `extracted_data` JSON DEFAULT NULL COMMENT 'Dados extraídos pela IA',
            `total_messages` INT(11) DEFAULT 0,
            `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_conversation_id` (`conversation_id`),
            KEY `idx_order_id` (`order_id`),
            KEY `idx_token` (`onboarding_token`),
            KEY `idx_email` (`customer_email`),
            KEY `idx_finished` (`finished`),
            KEY `idx_started_at` (`started_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        log_msg("✅ Tabela {$t} criada!", 'success');
    } catch (PDOException $e) {
        log_msg("❌ Erro ao criar {$t}: " . $e->getMessage(), 'error');
    }
}

// ============================================
// 4. TABELA: ONBOARDING_MESSAGES
// ============================================
$t = $prefix . 'onboarding_messages';
$convTable = $prefix . 'onboarding_conversations';
if (table_exists($pdo, $t)) {
    log_msg("⏭️ Tabela {$t} já existe — pulando");
} else {
    try {
        $pdo->exec("CREATE TABLE `{$t}` (
            `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
            `conversation_id` INT(11) NOT NULL COMMENT 'FK conversa onboarding',
            `role` ENUM('user','assistant') NOT NULL,
            `content` TEXT NOT NULL,
            `metadata` JSON DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_conversation` (`conversation_id`),
            KEY `idx_role` (`role`),
            KEY `idx_created_at` (`created_at`),
            CONSTRAINT `fk_onb_msg_conv` FOREIGN KEY (`conversation_id`)
                REFERENCES `{$convTable}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        log_msg("✅ Tabela {$t} criada!", 'success');
    } catch (PDOException $e) {
        log_msg("❌ Erro ao criar {$t}: " . $e->getMessage(), 'error');
    }
}

// ============================================
// VERIFICAÇÃO FINAL
// ============================================
$allTables = [
    $prefix . 'sdr_conversations',
    $prefix . 'sdr_messages',
    $prefix . 'onboarding_conversations',
    $prefix . 'onboarding_messages'
];

$allOk = true;
foreach ($allTables as $tbl) {
    if (table_exists($pdo, $tbl)) {
        $count = $pdo->query("SELECT COUNT(*) FROM `{$tbl}`")->fetchColumn();
        log_msg("📊 {$tbl} — OK ({$count} registros)", 'success');
    } else {
        log_msg("❌ {$tbl} — NÃO EXISTE!", 'error');
        $allOk = false;
    }
}

if ($allOk) {
    log_msg("🎉 Todas as 4 tabelas estão prontas!", 'success');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador - Tabelas de Conversas IA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #1a1a2e; color: #eee; min-height: 100vh; padding: 20px; }
        .container { max-width: 700px; margin: 0 auto; }
        h1 { text-align: center; margin-bottom: 8px; font-size: 24px; }
        .sub { text-align: center; color: #888; margin-bottom: 24px; font-size: 14px; }
        .log { padding: 12px 16px; margin-bottom: 6px; border-radius: 8px; font-family: monospace; font-size: 13px; border-left: 4px solid; }
        .log.info { background: #1e293b; border-color: #3b82f6; color: #93c5fd; }
        .log.success { background: #0f2a1a; border-color: #22c55e; color: #86efac; }
        .log.error { background: #2a0f0f; border-color: #ef4444; color: #fca5a5; }
        .footer { text-align: center; margin-top: 24px; color: #555; font-size: 12px; }
        .footer a { color: #8b5cf6; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Instalador de Tabelas</h1>
        <p class="sub">Log de Conversas IA — Banco: <?= DB_NAME ?></p>
        
        <?php foreach ($logs as $l): ?>
            <div class="log <?= $l['type'] ?>"><?= $l['msg'] ?></div>
        <?php endforeach; ?>
        
        <p class="footer">
            Após instalar, <strong>delete este arquivo</strong> do servidor por segurança.<br>
            <a href="install-conversations.php?password=<?= $INSTALL_PASSWORD ?>">🔄 Executar novamente</a>
        </p>
    </div>
</body>
</html>
