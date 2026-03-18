<?php
/**
 * Fix Admin DB - Corrige estrutura do banco para o painel admin
 * 
 * Problemas detectados pelo diagnóstico:
 * 1. Coluna customer_email FALTANDO na tabela orders
 * 2. Tabelas de gamificação (affiliate_missions, affiliate_badges) não existem
 * 
 * Uso: https://unli.com.br/fix-admin-db.php?key=FixAdmin2026
 * 
 * REMOVA ESTE ARQUIVO APÓS EXECUTAR!
 */

// Proteção por chave
$key = isset($_GET['key']) ? $_GET['key'] : '';
if ($key !== 'FixAdmin2026') {
    http_response_code(403);
    echo 'Acesso negado. Use ?key=FixAdmin2026';
    exit;
}

header('Content-Type: text/html; charset=utf-8');
echo '<html><head><title>Fix Admin DB</title>';
echo '<style>body{font-family:monospace;padding:20px;background:#111;color:#eee}';
echo '.ok{color:#0f0}.err{color:#f44}.warn{color:#ff0}.section{color:#0af;margin-top:15px;font-weight:bold}</style>';
echo '</head><body>';
echo '<h2>Fix Admin DB - Correções de Estrutura</h2>';

function msg($text, $class = 'ok') {
    echo "<div class=\"$class\">$text</div>";
    flush();
}

// Conexão com DB
if (!defined('DB_CONFIG_ACCESS')) {
    define('DB_CONFIG_ACCESS', true);
}
require_once __DIR__ . '/api/db.config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        )
    );
    msg('Conexão OK — ' . $pdo->query('SELECT VERSION()')->fetchColumn());
} catch (Exception $e) {
    msg('ERRO de conexão: ' . htmlspecialchars($e->getMessage()), 'err');
    exit;
}

$prefix = defined('DB_PREFIX') ? DB_PREFIX : '';

// =============================================
// 1. Adicionar customer_email à tabela orders
// =============================================
msg('<br>═══ 1. Coluna customer_email em orders ═══', 'section');

$stmt = $pdo->prepare("SHOW COLUMNS FROM {$prefix}orders LIKE 'customer_email'");
$stmt->execute();
if ($stmt->fetch()) {
    msg('✅ customer_email já existe — nenhuma ação necessária');
} else {
    try {
        $pdo->exec("ALTER TABLE {$prefix}orders ADD COLUMN customer_email VARCHAR(150) DEFAULT NULL AFTER customer_name");
        msg('✅ customer_email ADICIONADA com sucesso!');
    } catch (Exception $e) {
        msg('❌ Erro ao adicionar customer_email: ' . htmlspecialchars($e->getMessage()), 'err');
    }
}

// =============================================
// 2. Criar tabelas de gamificação (medalhas/missões)
// =============================================
msg('<br>═══ 2. Tabelas de Gamificação ═══', 'section');

// 2a. affiliate_badges
$stmt = $pdo->prepare("SHOW TABLES LIKE ?");
$stmt->execute(array($prefix . 'affiliate_badges'));
if ($stmt->fetch()) {
    msg('✅ ' . $prefix . 'affiliate_badges já existe');
} else {
    try {
        $pdo->exec("
            CREATE TABLE {$prefix}affiliate_badges (
                id INT(11) NOT NULL AUTO_INCREMENT,
                title VARCHAR(120) NOT NULL,
                description TEXT DEFAULT NULL,
                image_url VARCHAR(255) DEFAULT NULL,
                icon_emoji VARCHAR(10) DEFAULT NULL,
                type ENUM('event', 'achievement', 'legacy') NOT NULL DEFAULT 'achievement',
                criteria_key VARCHAR(80) DEFAULT NULL,
                criteria_value DECIMAL(12,2) DEFAULT NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                sort_order INT(11) NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                INDEX idx_badge_type (type),
                INDEX idx_badge_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        msg('✅ ' . $prefix . 'affiliate_badges CRIADA!');
    } catch (Exception $e) {
        msg('❌ Erro: ' . htmlspecialchars($e->getMessage()), 'err');
    }
}

// 2b. affiliate_user_badges
$stmt = $pdo->prepare("SHOW TABLES LIKE ?");
$stmt->execute(array($prefix . 'affiliate_user_badges'));
if ($stmt->fetch()) {
    msg('✅ ' . $prefix . 'affiliate_user_badges já existe');
} else {
    try {
        $pdo->exec("
            CREATE TABLE {$prefix}affiliate_user_badges (
                id INT(11) NOT NULL AUTO_INCREMENT,
                affiliate_id INT(11) NOT NULL,
                badge_id INT(11) NOT NULL,
                awarded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                awarded_by VARCHAR(50) DEFAULT 'system',
                notes TEXT DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE INDEX idx_user_badge_unique (affiliate_id, badge_id),
                INDEX idx_user_badge_affiliate (affiliate_id),
                INDEX idx_user_badge_badge (badge_id),
                CONSTRAINT fk_user_badges_affiliate FOREIGN KEY (affiliate_id)
                    REFERENCES {$prefix}affiliate_users(id) ON DELETE CASCADE,
                CONSTRAINT fk_user_badges_badge FOREIGN KEY (badge_id)
                    REFERENCES {$prefix}affiliate_badges(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        msg('✅ ' . $prefix . 'affiliate_user_badges CRIADA!');
    } catch (Exception $e) {
        msg('❌ Erro: ' . htmlspecialchars($e->getMessage()), 'err');
    }
}

// 2c. affiliate_missions
$stmt = $pdo->prepare("SHOW TABLES LIKE ?");
$stmt->execute(array($prefix . 'affiliate_missions'));
if ($stmt->fetch()) {
    msg('✅ ' . $prefix . 'affiliate_missions já existe');
} else {
    try {
        $pdo->exec("
            CREATE TABLE {$prefix}affiliate_missions (
                id INT(11) NOT NULL AUTO_INCREMENT,
                title VARCHAR(120) NOT NULL,
                description TEXT DEFAULT NULL,
                icon_emoji VARCHAR(10) DEFAULT NULL,
                category ENUM('explorer', 'lead_hunter', 'closer', 'consistency') NOT NULL,
                target_value DECIMAL(12,2) NOT NULL,
                target_unit VARCHAR(30) NOT NULL DEFAULT 'count',
                reward_type ENUM('xp', 'commission_bonus', 'badge') NOT NULL DEFAULT 'xp',
                reward_value DECIMAL(8,2) DEFAULT NULL,
                frequency ENUM('weekly', 'monthly', 'one_time') NOT NULL DEFAULT 'weekly',
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                sort_order INT(11) NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                INDEX idx_mission_category (category),
                INDEX idx_mission_frequency (frequency),
                INDEX idx_mission_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        msg('✅ ' . $prefix . 'affiliate_missions CRIADA!');
    } catch (Exception $e) {
        msg('❌ Erro: ' . htmlspecialchars($e->getMessage()), 'err');
    }
}

// 2d. affiliate_mission_progress
$stmt = $pdo->prepare("SHOW TABLES LIKE ?");
$stmt->execute(array($prefix . 'affiliate_mission_progress'));
if ($stmt->fetch()) {
    msg('✅ ' . $prefix . 'affiliate_mission_progress já existe');
} else {
    try {
        $pdo->exec("
            CREATE TABLE {$prefix}affiliate_mission_progress (
                id INT(11) NOT NULL AUTO_INCREMENT,
                affiliate_id INT(11) NOT NULL,
                mission_id INT(11) NOT NULL,
                week_start DATE NOT NULL,
                current_value DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                completed TINYINT(1) NOT NULL DEFAULT 0,
                completed_at DATETIME DEFAULT NULL,
                reward_claimed TINYINT(1) NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE INDEX idx_mission_progress_unique (affiliate_id, mission_id, week_start),
                INDEX idx_mission_progress_affiliate (affiliate_id),
                INDEX idx_mission_progress_week (week_start),
                CONSTRAINT fk_mission_progress_affiliate FOREIGN KEY (affiliate_id)
                    REFERENCES {$prefix}affiliate_users(id) ON DELETE CASCADE,
                CONSTRAINT fk_mission_progress_mission FOREIGN KEY (mission_id)
                    REFERENCES {$prefix}affiliate_missions(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        msg('✅ ' . $prefix . 'affiliate_mission_progress CRIADA!');
    } catch (Exception $e) {
        msg('❌ Erro: ' . htmlspecialchars($e->getMessage()), 'err');
    }
}

// =============================================
// 3. Verificação final
// =============================================
msg('<br>═══ Verificação Final ═══', 'section');

// Check customer_email
$stmt = $pdo->prepare("SHOW COLUMNS FROM {$prefix}orders LIKE 'customer_email'");
$stmt->execute();
$hasCol = $stmt->fetch() ? true : false;
msg($hasCol ? '✅ orders.customer_email: OK' : '❌ orders.customer_email: FALTANDO', $hasCol ? 'ok' : 'err');

$tables = array('affiliate_badges', 'affiliate_user_badges', 'affiliate_missions', 'affiliate_mission_progress');
foreach ($tables as $t) {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute(array($prefix . $t));
    $exists = $stmt->fetch() ? true : false;
    msg($exists ? '✅ ' . $prefix . $t . ': OK' : '❌ ' . $prefix . $t . ': NÃO EXISTE', $exists ? 'ok' : 'err');
}

msg('<br><strong style="color:#0f0">✅ Migração concluída! Remova este arquivo do servidor.</strong>');
echo '</body></html>';
