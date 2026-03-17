<?php
/**
 * ============================================
 * MIGRATION: Sistema de Medalhas e Missões
 * ============================================
 * 
 * Acesse: https://unli.com.br/migrate-badges.php?password=BadgeMigrate2026
 * 
 * Cria as tabelas:
 *   - affiliate_badges         (catálogo de medalhas)
 *   - affiliate_user_badges    (medalhas conquistadas)
 *   - affiliate_missions       (catálogo de missões)
 *   - affiliate_mission_progress (progresso das missões)
 * 
 * ⚠️ APAGUE ESTE ARQUIVO APÓS EXECUTAR!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================
// PROTEÇÃO COM SENHA
// ============================================
$MIGRATION_PASSWORD = 'BadgeMigrate2026';

if (!isset($_GET['password']) || !hash_equals($MIGRATION_PASSWORD, $_GET['password'])) {
    http_response_code(401);
    die('🚫 Acesso negado! Use: migrate-badges.php?password=SUA_SENHA');
}

// ============================================
// CONEXÃO
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

$prefix = defined('DB_PREFIX') ? DB_PREFIX : '';

// ============================================
// 1. TABELA: affiliate_badges
// ============================================

$tblBadges = $prefix . 'affiliate_badges';
log_msg("Verificando tabela {$tblBadges}...");

if (!table_exists($pdo, $tblBadges)) {
    log_msg("Criando tabela {$tblBadges}...", 'warning');
    try {
        $pdo->exec("
            CREATE TABLE `{$tblBadges}` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `title` VARCHAR(120) NOT NULL COMMENT 'Nome da conquista',
                `description` TEXT DEFAULT NULL COMMENT 'O que foi feito para ganhar',
                `image_url` VARCHAR(255) DEFAULT NULL COMMENT 'URL do ícone da medalha',
                `icon_emoji` VARCHAR(10) DEFAULT NULL COMMENT 'Emoji representativo',
                `type` ENUM('event', 'achievement', 'legacy') NOT NULL DEFAULT 'achievement' COMMENT 'Tipo: evento, conquista ou legado',
                `criteria_key` VARCHAR(80) DEFAULT NULL COMMENT 'Chave interna para verificação automática',
                `criteria_value` DECIMAL(12,2) DEFAULT NULL COMMENT 'Valor numérico do critério',
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `sort_order` INT(11) NOT NULL DEFAULT 0 COMMENT 'Ordem de exibição',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_badge_type` (`type`),
                INDEX `idx_badge_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Catálogo de medalhas e selos para afiliados'
        ");
        log_msg("Tabela {$tblBadges} criada com sucesso!", 'success');
    } catch (PDOException $e) {
        log_msg("Erro ao criar {$tblBadges}: " . $e->getMessage(), 'error');
    }
} else {
    log_msg("Tabela {$tblBadges} já existe", 'success');
}

// ============================================
// 2. TABELA: affiliate_user_badges
// ============================================

$tblUserBadges = $prefix . 'affiliate_user_badges';
$tblAffUsers = $prefix . 'affiliate_users';
log_msg("Verificando tabela {$tblUserBadges}...");

if (!table_exists($pdo, $tblUserBadges)) {
    log_msg("Criando tabela {$tblUserBadges}...", 'warning');
    try {
        $pdo->exec("
            CREATE TABLE `{$tblUserBadges}` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `affiliate_id` INT(11) NOT NULL COMMENT 'ID do afiliado',
                `badge_id` INT(11) NOT NULL COMMENT 'ID da medalha',
                `awarded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data da conquista',
                `awarded_by` VARCHAR(50) DEFAULT 'system' COMMENT 'Quem concedeu: system ou admin',
                `notes` TEXT DEFAULT NULL COMMENT 'Observação adicional',
                PRIMARY KEY (`id`),
                UNIQUE INDEX `idx_user_badge_unique` (`affiliate_id`, `badge_id`),
                INDEX `idx_user_badge_affiliate` (`affiliate_id`),
                INDEX `idx_user_badge_badge` (`badge_id`),
                CONSTRAINT `fk_user_badges_affiliate` FOREIGN KEY (`affiliate_id`)
                    REFERENCES `{$tblAffUsers}`(`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_user_badges_badge` FOREIGN KEY (`badge_id`)
                    REFERENCES `{$tblBadges}`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Medalhas conquistadas pelos afiliados'
        ");
        log_msg("Tabela {$tblUserBadges} criada com sucesso!", 'success');
    } catch (PDOException $e) {
        log_msg("Erro ao criar {$tblUserBadges}: " . $e->getMessage(), 'error');
    }
} else {
    log_msg("Tabela {$tblUserBadges} já existe", 'success');
}

// ============================================
// 3. TABELA: affiliate_missions
// ============================================

$tblMissions = $prefix . 'affiliate_missions';
log_msg("Verificando tabela {$tblMissions}...");

if (!table_exists($pdo, $tblMissions)) {
    log_msg("Criando tabela {$tblMissions}...", 'warning');
    try {
        $pdo->exec("
            CREATE TABLE `{$tblMissions}` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `title` VARCHAR(120) NOT NULL COMMENT 'Nome da missão',
                `description` TEXT DEFAULT NULL COMMENT 'Descrição detalhada',
                `icon_emoji` VARCHAR(10) DEFAULT NULL COMMENT 'Emoji representativo',
                `category` ENUM('explorer', 'lead_hunter', 'closer', 'consistency') NOT NULL COMMENT 'Categoria da missão',
                `target_value` DECIMAL(12,2) NOT NULL COMMENT 'Meta numérica a atingir',
                `target_unit` VARCHAR(30) NOT NULL DEFAULT 'count' COMMENT 'Unidade: count, currency, days',
                `reward_type` ENUM('xp', 'commission_bonus', 'badge') NOT NULL DEFAULT 'xp' COMMENT 'Tipo de recompensa',
                `reward_value` DECIMAL(8,2) DEFAULT NULL COMMENT 'Valor da recompensa (XP ou % bônus)',
                `frequency` ENUM('weekly', 'monthly', 'one_time') NOT NULL DEFAULT 'weekly' COMMENT 'Frequência de reset',
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `sort_order` INT(11) NOT NULL DEFAULT 0,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_mission_category` (`category`),
                INDEX `idx_mission_frequency` (`frequency`),
                INDEX `idx_mission_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Catálogo de missões para afiliados'
        ");
        log_msg("Tabela {$tblMissions} criada com sucesso!", 'success');
    } catch (PDOException $e) {
        log_msg("Erro ao criar {$tblMissions}: " . $e->getMessage(), 'error');
    }
} else {
    log_msg("Tabela {$tblMissions} já existe", 'success');
}

// ============================================
// 4. TABELA: affiliate_mission_progress
// ============================================

$tblProgress = $prefix . 'affiliate_mission_progress';
log_msg("Verificando tabela {$tblProgress}...");

if (!table_exists($pdo, $tblProgress)) {
    log_msg("Criando tabela {$tblProgress}...", 'warning');
    try {
        $pdo->exec("
            CREATE TABLE `{$tblProgress}` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `affiliate_id` INT(11) NOT NULL,
                `mission_id` INT(11) NOT NULL,
                `week_start` DATE NOT NULL COMMENT 'Início da semana (segunda-feira)',
                `current_value` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Progresso atual',
                `completed` TINYINT(1) NOT NULL DEFAULT 0,
                `completed_at` DATETIME DEFAULT NULL,
                `reward_claimed` TINYINT(1) NOT NULL DEFAULT 0,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE INDEX `idx_mission_progress_unique` (`affiliate_id`, `mission_id`, `week_start`),
                INDEX `idx_mission_progress_affiliate` (`affiliate_id`),
                INDEX `idx_mission_progress_week` (`week_start`),
                CONSTRAINT `fk_mission_progress_affiliate` FOREIGN KEY (`affiliate_id`)
                    REFERENCES `{$tblAffUsers}`(`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_mission_progress_mission` FOREIGN KEY (`mission_id`)
                    REFERENCES `{$tblMissions}`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Progresso das missões semanais dos afiliados'
        ");
        log_msg("Tabela {$tblProgress} criada com sucesso!", 'success');
    } catch (PDOException $e) {
        log_msg("Erro ao criar {$tblProgress}: " . $e->getMessage(), 'error');
    }
} else {
    log_msg("Tabela {$tblProgress} já existe", 'success');
}

log_msg("Migration de medalhas e missões concluída!", 'success');

// ============================================
// EXIBIR RESULTADOS (HTML)
// ============================================
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migration - Medalhas e Missões</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a; color: #e2e8f0;
            padding: 40px 20px;
        }
        .container {
            max-width: 800px; margin: 0 auto;
            background: #1e293b; border-radius: 16px;
            padding: 32px; box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }
        h1 { font-size: 1.8rem; margin-bottom: 8px; color: #10b981; }
        .subtitle { color: #94a3b8; margin-bottom: 24px; font-size: 0.95rem; }
        .log-item {
            padding: 10px 14px; margin-bottom: 6px;
            border-radius: 8px; font-size: 0.9rem;
            border-left: 3px solid #334155;
            background: #0f172a;
        }
        .log-item.success { border-left-color: #10b981; }
        .log-item.warning { border-left-color: #f59e0b; }
        .log-item.error { border-left-color: #ef4444; color: #fca5a5; }
        .log-item.info { border-left-color: #3b82f6; }
        .footer {
            margin-top: 24px; padding-top: 16px;
            border-top: 1px solid #334155;
            color: #64748b; font-size: 0.85rem;
            text-align: center;
        }
        .footer strong { color: #f59e0b; }
        .summary {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 12px; margin-bottom: 24px;
        }
        .summary-card {
            background: #0f172a; border-radius: 10px;
            padding: 16px; text-align: center;
        }
        .summary-card .number { font-size: 2rem; font-weight: 800; }
        .summary-card .label { font-size: 0.8rem; color: #94a3b8; margin-top: 4px; }
        .summary-card.ok .number { color: #10b981; }
        .summary-card.warn .number { color: #f59e0b; }
        .summary-card.fail .number { color: #ef4444; }
    </style>
</head>
<body>
<div class="container">
    <h1>🏅 Migration - Medalhas e Missões</h1>
    <p class="subtitle">Criação de tabelas para o sistema de badges e missões semanais</p>

    <?php
        $countSuccess = 0;
        $countWarning = 0;
        $countError = 0;
        foreach ($logs as $log) {
            if ($log['type'] === 'success') $countSuccess++;
            elseif ($log['type'] === 'warning') $countWarning++;
            elseif ($log['type'] === 'error') $countError++;
        }
    ?>

    <div class="summary">
        <div class="summary-card ok">
            <div class="number"><?= $countSuccess ?></div>
            <div class="label">Sucesso</div>
        </div>
        <div class="summary-card warn">
            <div class="number"><?= $countWarning ?></div>
            <div class="label">Ações</div>
        </div>
        <div class="summary-card fail">
            <div class="number"><?= $countError ?></div>
            <div class="label">Erros</div>
        </div>
    </div>

    <?php foreach ($logs as $log): ?>
        <div class="log-item <?= $log['type'] ?>">
            <?= htmlspecialchars($log['msg']) ?>
        </div>
    <?php endforeach; ?>

    <div class="footer">
        <p>⚠️ <strong>APAGUE este arquivo após executar!</strong></p>
        <p>Tabelas criadas: affiliate_badges, affiliate_user_badges, affiliate_missions, affiliate_mission_progress</p>
    </div>
</div>
</body>
</html>
