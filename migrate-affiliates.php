<?php
/**
 * ============================================
 * MIGRATION: Sistema de Afiliados
 * ============================================
 * 
 * Acesse: https://unli.com.br/migrate-affiliates.php?password=AffMigrate2026
 * 
 * Cria as tabelas:
 *   - affiliate_users
 *   - affiliate_referrals
 *   - affiliate_tier_history
 *   - affiliate_quarterly_sales
 * 
 * Altera a tabela orders:
 *   - Adiciona coluna affiliate_id
 *   - Adiciona coluna affiliate_hash
 *   - Adiciona índice e foreign key
 * 
 * ⚠️ APAGUE ESTE ARQUIVO APÓS EXECUTAR!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================
// PROTEÇÃO COM SENHA
// ============================================
$MIGRATION_PASSWORD = 'AffMigrate2026';

if (!isset($_GET['password']) || $_GET['password'] !== $MIGRATION_PASSWORD) {
    http_response_code(401);
    die('🚫 Acesso negado! Use: migrate-affiliates.php?password=SUA_SENHA');
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

function index_exists($pdo, $table, $indexName) {
    try {
        $stmt = $pdo->prepare("SHOW INDEX FROM `{$table}` WHERE Key_name = ?");
        $stmt->execute([$indexName]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

function fk_exists($pdo, $table, $fkName) {
    try {
        $stmt = $pdo->prepare("
            SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ");
        $stmt->execute([DB_NAME, $table, $fkName]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

$prefix = defined('DB_PREFIX') ? DB_PREFIX : '';

// ============================================
// 1. TABELA: affiliate_users
// ============================================

$tblAffUsers = $prefix . 'affiliate_users';
log_msg("Verificando tabela {$tblAffUsers}...");

if (!table_exists($pdo, $tblAffUsers)) {
    log_msg("Criando tabela {$tblAffUsers}...", 'warning');
    try {
        $pdo->exec("
            CREATE TABLE `{$tblAffUsers}` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `first_name` VARCHAR(80) NOT NULL COMMENT 'Primeiro nome',
                `last_name` VARCHAR(80) NOT NULL COMMENT 'Sobrenome',
                `email` VARCHAR(150) NOT NULL COMMENT 'E-mail (usado para login)',
                `password_hash` VARCHAR(255) NOT NULL COMMENT 'Senha em bcrypt',
                `whatsapp` VARCHAR(20) NOT NULL COMMENT 'WhatsApp do afiliado',
                `pix_key` VARCHAR(150) DEFAULT NULL COMMENT 'Chave PIX para receber comissões',
                `affiliate_hash` VARCHAR(32) NOT NULL COMMENT 'Hash único do afiliado (usado nos links)',
                `tier` VARCHAR(30) NOT NULL DEFAULT 'bronze_1' COMMENT 'Título/liga atual',
                `commission_rate` DECIMAL(5,2) NOT NULL DEFAULT 8.00 COMMENT 'Percentual de comissão atual',
                `total_sales_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Montante total vendido (acumulado)',
                `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=ativo, 0=desativado',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `last_login` DATETIME DEFAULT NULL,
                `last_tier_check` DATETIME DEFAULT NULL COMMENT 'Última verificação de manutenção de título',
                PRIMARY KEY (`id`),
                UNIQUE INDEX `idx_affiliate_email` (`email`),
                UNIQUE INDEX `idx_affiliate_hash` (`affiliate_hash`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Usuários afiliados do sistema de indicação'
        ");
        log_msg("Tabela {$tblAffUsers} criada com sucesso!", 'success');
    } catch (PDOException $e) {
        log_msg("Erro ao criar {$tblAffUsers}: " . $e->getMessage(), 'error');
    }
} else {
    log_msg("Tabela {$tblAffUsers} já existe", 'success');
}

// ============================================
// 2. TABELA: affiliate_referrals
// ============================================

$tblAffReferrals = $prefix . 'affiliate_referrals';
log_msg("Verificando tabela {$tblAffReferrals}...");

if (!table_exists($pdo, $tblAffReferrals)) {
    log_msg("Criando tabela {$tblAffReferrals}...", 'warning');
    $tblOrders = $prefix . 'orders';
    try {
        $pdo->exec("
            CREATE TABLE `{$tblAffReferrals}` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `affiliate_id` INT(11) NOT NULL COMMENT 'ID do afiliado que indicou',
                `order_id` INT(11) DEFAULT NULL COMMENT 'ID do pedido na tabela orders',
                `lead_name` VARCHAR(200) DEFAULT NULL COMMENT 'Nome do lead indicado',
                `lead_email` VARCHAR(150) DEFAULT NULL COMMENT 'Email do lead',
                `lead_phone` VARCHAR(30) DEFAULT NULL COMMENT 'Telefone do lead',
                `status` ENUM('lead','contacted','negotiating','closed','onboarding','completed','lost','pending')
                    NOT NULL DEFAULT 'lead' COMMENT 'Status do progresso',
                `sale_amount` DECIMAL(12,2) DEFAULT NULL COMMENT 'Valor da venda (quando fechada)',
                `order_amount` DECIMAL(12,2) DEFAULT NULL COMMENT 'Valor do pedido',
                `commission_rate` DECIMAL(5,2) DEFAULT NULL COMMENT 'Taxa de comissão no momento da venda',
                `commission_amount` DECIMAL(12,2) DEFAULT NULL COMMENT 'Valor da comissão a pagar',
                `commission_paid` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=pendente, 1=paga',
                `commission_paid_at` DATETIME DEFAULT NULL COMMENT 'Data do pagamento da comissão',
                `source_page` VARCHAR(100) DEFAULT NULL COMMENT 'Página de origem',
                `notes` TEXT DEFAULT NULL COMMENT 'Observações',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_aff_referrals_affiliate` (`affiliate_id`),
                INDEX `idx_aff_referrals_order` (`order_id`),
                INDEX `idx_aff_referrals_status` (`status`),
                CONSTRAINT `fk_referrals_affiliate` FOREIGN KEY (`affiliate_id`)
                    REFERENCES `{$tblAffUsers}`(`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_referrals_order` FOREIGN KEY (`order_id`)
                    REFERENCES `{$tblOrders}`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Indicações e vendas dos afiliados'
        ");
        log_msg("Tabela {$tblAffReferrals} criada com sucesso!", 'success');
    } catch (PDOException $e) {
        log_msg("Erro ao criar {$tblAffReferrals}: " . $e->getMessage(), 'error');
    }
} else {
    log_msg("Tabela {$tblAffReferrals} já existe", 'success');
}

// ============================================
// 3. TABELA: affiliate_tier_history
// ============================================

$tblTierHistory = $prefix . 'affiliate_tier_history';
log_msg("Verificando tabela {$tblTierHistory}...");

if (!table_exists($pdo, $tblTierHistory)) {
    log_msg("Criando tabela {$tblTierHistory}...", 'warning');
    try {
        $pdo->exec("
            CREATE TABLE `{$tblTierHistory}` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `affiliate_id` INT(11) NOT NULL,
                `old_tier` VARCHAR(30) NOT NULL,
                `new_tier` VARCHAR(30) NOT NULL,
                `reason` VARCHAR(200) DEFAULT NULL COMMENT 'Motivo da mudança',
                `sales_amount_at_change` DECIMAL(12,2) DEFAULT NULL COMMENT 'Montante de vendas no momento',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_tier_history_affiliate` (`affiliate_id`),
                CONSTRAINT `fk_tier_history_affiliate` FOREIGN KEY (`affiliate_id`)
                    REFERENCES `{$tblAffUsers}`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Histórico de mudanças de título dos afiliados'
        ");
        log_msg("Tabela {$tblTierHistory} criada com sucesso!", 'success');
    } catch (PDOException $e) {
        log_msg("Erro ao criar {$tblTierHistory}: " . $e->getMessage(), 'error');
    }
} else {
    log_msg("Tabela {$tblTierHistory} já existe", 'success');
}

// ============================================
// 4. TABELA: affiliate_quarterly_sales
// ============================================

$tblQuarterly = $prefix . 'affiliate_quarterly_sales';
log_msg("Verificando tabela {$tblQuarterly}...");

if (!table_exists($pdo, $tblQuarterly)) {
    log_msg("Criando tabela {$tblQuarterly}...", 'warning');
    try {
        $pdo->exec("
            CREATE TABLE `{$tblQuarterly}` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `affiliate_id` INT(11) NOT NULL,
                `quarter_start` DATE NOT NULL COMMENT 'Início do trimestre',
                `quarter_end` DATE NOT NULL COMMENT 'Fim do trimestre',
                `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Total vendido no trimestre',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_quarterly_affiliate` (`affiliate_id`, `quarter_start`),
                CONSTRAINT `fk_quarterly_affiliate` FOREIGN KEY (`affiliate_id`)
                    REFERENCES `{$tblAffUsers}`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Registro de vendas trimestrais para manutenção de título'
        ");
        log_msg("Tabela {$tblQuarterly} criada com sucesso!", 'success');
    } catch (PDOException $e) {
        log_msg("Erro ao criar {$tblQuarterly}: " . $e->getMessage(), 'error');
    }
} else {
    log_msg("Tabela {$tblQuarterly} já existe", 'success');
}

// ============================================
// 5. ALTER TABLE orders - Adicionar colunas de afiliado
// ============================================

$tblOrders = $prefix . 'orders';
log_msg("Verificando colunas de afiliado na tabela {$tblOrders}...");

if (table_exists($pdo, $tblOrders)) {
    // Coluna affiliate_id
    if (!column_exists($pdo, $tblOrders, 'affiliate_id')) {
        log_msg("Adicionando coluna affiliate_id em {$tblOrders}...", 'warning');
        try {
            // Tentar adicionar AFTER sdr_id, se falhar, adicionar sem posição
            if (column_exists($pdo, $tblOrders, 'sdr_id')) {
                $pdo->exec("ALTER TABLE `{$tblOrders}` ADD COLUMN `affiliate_id` INT(11) DEFAULT NULL COMMENT 'Afiliado que indicou o cliente' AFTER `sdr_id`");
            } else {
                $pdo->exec("ALTER TABLE `{$tblOrders}` ADD COLUMN `affiliate_id` INT(11) DEFAULT NULL COMMENT 'Afiliado que indicou o cliente'");
            }
            log_msg("Coluna affiliate_id adicionada!", 'success');
        } catch (PDOException $e) {
            log_msg("Erro ao adicionar affiliate_id: " . $e->getMessage(), 'error');
        }
    } else {
        log_msg("Coluna affiliate_id já existe em {$tblOrders}", 'success');
    }

    // Coluna affiliate_hash
    if (!column_exists($pdo, $tblOrders, 'affiliate_hash')) {
        log_msg("Adicionando coluna affiliate_hash em {$tblOrders}...", 'warning');
        try {
            if (column_exists($pdo, $tblOrders, 'affiliate_id')) {
                $pdo->exec("ALTER TABLE `{$tblOrders}` ADD COLUMN `affiliate_hash` VARCHAR(32) DEFAULT NULL COMMENT 'Hash do afiliado (para registro manual)' AFTER `affiliate_id`");
            } else {
                $pdo->exec("ALTER TABLE `{$tblOrders}` ADD COLUMN `affiliate_hash` VARCHAR(32) DEFAULT NULL COMMENT 'Hash do afiliado (para registro manual)'");
            }
            log_msg("Coluna affiliate_hash adicionada!", 'success');
        } catch (PDOException $e) {
            log_msg("Erro ao adicionar affiliate_hash: " . $e->getMessage(), 'error');
        }
    } else {
        log_msg("Coluna affiliate_hash já existe em {$tblOrders}", 'success');
    }

    // Índice idx_affiliate_id
    if (!index_exists($pdo, $tblOrders, 'idx_affiliate_id')) {
        try {
            $pdo->exec("ALTER TABLE `{$tblOrders}` ADD INDEX `idx_affiliate_id` (`affiliate_id`)");
            log_msg("Índice idx_affiliate_id criado!", 'success');
        } catch (PDOException $e) {
            log_msg("Erro ao criar índice idx_affiliate_id: " . $e->getMessage(), 'error');
        }
    } else {
        log_msg("Índice idx_affiliate_id já existe", 'success');
    }

    // Foreign Key fk_orders_affiliate
    if (!fk_exists($pdo, $tblOrders, 'fk_orders_affiliate')) {
        try {
            $pdo->exec("ALTER TABLE `{$tblOrders}` ADD CONSTRAINT `fk_orders_affiliate` FOREIGN KEY (`affiliate_id`) REFERENCES `{$tblAffUsers}`(`id`) ON DELETE SET NULL");
            log_msg("Foreign key fk_orders_affiliate criada!", 'success');
        } catch (PDOException $e) {
            log_msg("Erro ao criar FK fk_orders_affiliate: " . $e->getMessage(), 'error');
        }
    } else {
        log_msg("Foreign key fk_orders_affiliate já existe", 'success');
    }
} else {
    log_msg("Tabela {$tblOrders} não encontrada! Execute a migration principal primeiro.", 'error');
}

log_msg("Migration de afiliados concluída!", 'success');

// ============================================
// EXIBIR RESULTADOS (HTML)
// ============================================
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migration - Sistema de Afiliados</title>
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
    <h1>🤝 Migration - Sistema de Afiliados</h1>
    <p class="subtitle">Criação de tabelas e alterações para o sistema de afiliados Unli</p>

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
        <div class="log-item <?= $log['type'] ?>"><?= htmlspecialchars($log['msg']) ?></div>
    <?php endforeach; ?>

    <div class="footer">
        <p><strong>⚠️ APAGUE ESTE ARQUIVO APÓS EXECUTAR!</strong></p>
        <p>Executado em <?= date('d/m/Y H:i:s') ?></p>
    </div>
</div>
</body>
</html>
