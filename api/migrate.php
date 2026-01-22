<?php
/**
 * ============================================
 * SISTEMA DE MIGRATIONS - BANCO DE DADOS
 * ============================================
 * 
 * Execute este arquivo acessando:
 * https://seu-dominio.com/api/migrate.php
 * 
 * Sistema inteligente que:
 * ✅ Cria tabelas se não existirem
 * ✅ Adiciona colunas se não existirem
 * ✅ Não apaga dados existentes
 * ✅ Suporta rollback
 */

// ============================================
// PROTEÇÃO COM SENHA (ALTERE ESTA SENHA!)
// ============================================
$MIGRATION_PASSWORD = 'unli2026secure';

// Verificar senha
if (!isset($_GET['password']) || $_GET['password'] !== $MIGRATION_PASSWORD) {
    http_response_code(401);
    die('🚫 Acesso negado! Use: migrate.php?password=SUA_SENHA');
}

// ============================================
// CONFIGURAÇÃO
// ============================================
define('DB_CONFIG_ACCESS', true);
require_once __DIR__ . '/db.config.php';

// Conectar ao banco
try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET),
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die('❌ Erro de conexão: ' . $e->getMessage());
}

// ============================================
// FUNÇÕES AUXILIARES
// ============================================

$logs = [];

function log_message($message, $type = 'info') {
    global $logs;
    $logs[] = [
        'type' => $type,
        'message' => $message,
        'time' => date('H:i:s')
    ];
}

function table_exists($pdo, $tableName) {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$tableName]);
    return $stmt->rowCount() > 0;
}

function column_exists($pdo, $tableName, $columnName) {
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$tableName}` LIKE ?");
        $stmt->execute([$columnName]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

function index_exists($pdo, $tableName, $indexName) {
    try {
        $stmt = $pdo->query("SHOW INDEX FROM `{$tableName}` WHERE Key_name = '{$indexName}'");
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

// ============================================
// MIGRATION: TABELA ORDERS
// ============================================

function migrate_orders_table($pdo) {
    $tableName = DB_PREFIX . 'orders';
    
    log_message("🔍 Verificando tabela {$tableName}...");
    
    if (!table_exists($pdo, $tableName)) {
        log_message("📦 Criando tabela {$tableName}...", 'warning');
        
        $sql = "CREATE TABLE `{$tableName}` (
            `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'ID único do pedido',
            
            -- Informações do Cliente
            `customer_name` VARCHAR(255) NOT NULL COMMENT 'Nome completo do cliente',
            `email` VARCHAR(255) NOT NULL COMMENT 'E-mail do cliente',
            `phone` VARCHAR(50) DEFAULT NULL COMMENT 'Telefone/WhatsApp do cliente',
            
            -- Informações do Pedido
            `order_details` TEXT DEFAULT NULL COMMENT 'JSON com detalhes do plano contratado',
            `total_amount` DECIMAL(10,2) NOT NULL COMMENT 'Valor total pago',
            `payment_status` ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending' COMMENT 'Status do pagamento',
            `payment_method` VARCHAR(50) DEFAULT NULL COMMENT 'Método de pagamento usado',
            `payment_id` VARCHAR(255) DEFAULT NULL COMMENT 'ID da transação no gateway de pagamento',
            
            -- Sistema de Onboarding
            `onboarding_token` VARCHAR(64) UNIQUE NOT NULL COMMENT 'Token único para acesso ao wizard',
            `onboarding_status` ENUM('pendente', 'preenchendo', 'concluido') DEFAULT 'pendente' COMMENT 'Status do preenchimento do briefing',
            `briefing_data` LONGTEXT DEFAULT NULL COMMENT 'JSON com dados do briefing preenchido pelo cliente',
            
            -- Timestamps
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação do pedido',
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última atualização',
            `completed_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Data de conclusão do briefing',
            
            -- Chave primária
            PRIMARY KEY (`id`),
            
            -- Índices
            INDEX `idx_token` (`onboarding_token`),
            INDEX `idx_email` (`email`),
            INDEX `idx_status` (`onboarding_status`),
            INDEX `idx_payment_status` (`payment_status`),
            INDEX `idx_payment_id` (`payment_id`)
            
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela de pedidos e onboarding'";
        
        try {
            $pdo->exec($sql);
            log_message("✅ Tabela {$tableName} criada com sucesso!", 'success');
            return true;
        } catch (PDOException $e) {
            log_message("❌ Erro ao criar tabela: " . $e->getMessage(), 'error');
            return false;
        }
        
    } else {
        log_message("✅ Tabela {$tableName} já existe");
        
        // Verificar e adicionar colunas que podem não existir
        $columns_to_check = [
            'customer_name' => "VARCHAR(255) NOT NULL COMMENT 'Nome completo do cliente'",
            'email' => "VARCHAR(255) NOT NULL COMMENT 'E-mail do cliente'",
            'phone' => "VARCHAR(50) DEFAULT NULL COMMENT 'Telefone/WhatsApp do cliente'",
            'order_details' => "TEXT DEFAULT NULL COMMENT 'JSON com detalhes do plano contratado'",
            'total_amount' => "DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Valor total pago'",
            'payment_status' => "ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending' COMMENT 'Status do pagamento'",
            'payment_method' => "VARCHAR(50) DEFAULT NULL COMMENT 'Método de pagamento usado'",
            'payment_id' => "VARCHAR(255) DEFAULT NULL COMMENT 'ID da transação no gateway de pagamento'",
            'onboarding_token' => "VARCHAR(64) DEFAULT NULL COMMENT 'Token único para acesso ao wizard'",
            'onboarding_status' => "ENUM('pendente', 'preenchendo', 'concluido') DEFAULT 'pendente' COMMENT 'Status do preenchimento do briefing'",
            'briefing_data' => "LONGTEXT DEFAULT NULL COMMENT 'JSON com dados do briefing preenchido pelo cliente'",
            'created_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação do pedido'",
            'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última atualização'",
            'completed_at' => "TIMESTAMP NULL DEFAULT NULL COMMENT 'Data de conclusão do briefing'"
        ];
        
        foreach ($columns_to_check as $column => $definition) {
            if (!column_exists($pdo, $tableName, $column)) {
                log_message("➕ Adicionando coluna {$column}...", 'warning');
                
                try {
                    $pdo->exec("ALTER TABLE `{$tableName}` ADD COLUMN `{$column}` {$definition}");
                    log_message("✅ Coluna {$column} adicionada!", 'success');
                } catch (PDOException $e) {
                    log_message("❌ Erro ao adicionar coluna {$column}: " . $e->getMessage(), 'error');
                }
            }
        }
        
        // Verificar e adicionar índices
        $indexes_to_check = [
            'idx_token' => '(`onboarding_token`)',
            'idx_email' => '(`email`)',
            'idx_status' => '(`onboarding_status`)',
            'idx_payment_status' => '(`payment_status`)',
            'idx_payment_id' => '(`payment_id`)'
        ];
        
        foreach ($indexes_to_check as $indexName => $columns) {
            if (!index_exists($pdo, $tableName, $indexName)) {
                log_message("📊 Adicionando índice {$indexName}...", 'warning');
                
                try {
                    $pdo->exec("ALTER TABLE `{$tableName}` ADD INDEX `{$indexName}` {$columns}");
                    log_message("✅ Índice {$indexName} adicionado!", 'success');
                } catch (PDOException $e) {
                    log_message("⚠️ Aviso: {$e->getMessage()}", 'warning');
                }
            }
        }
        
        // Garantir que onboarding_token seja UNIQUE
        try {
            $result = $pdo->query("SHOW INDEX FROM `{$tableName}` WHERE Column_name = 'onboarding_token' AND Non_unique = 0");
            if ($result->rowCount() == 0) {
                log_message("🔐 Tornando onboarding_token UNIQUE...", 'warning');
                $pdo->exec("ALTER TABLE `{$tableName}` ADD UNIQUE INDEX `unique_token` (`onboarding_token`)");
                log_message("✅ Constraint UNIQUE adicionada!", 'success');
            }
        } catch (PDOException $e) {
            log_message("⚠️ Aviso ao verificar UNIQUE: {$e->getMessage()}", 'warning');
        }
    }
    
    return true;
}

// ============================================
// ROLLBACK: REMOVER TABELA ORDERS
// ============================================

function rollback_orders_table($pdo) {
    $tableName = DB_PREFIX . 'orders';
    
    log_message("⚠️ ROLLBACK: Removendo tabela {$tableName}...", 'warning');
    
    if (table_exists($pdo, $tableName)) {
        try {
            $pdo->exec("DROP TABLE `{$tableName}`");
            log_message("✅ Tabela {$tableName} removida!", 'success');
            return true;
        } catch (PDOException $e) {
            log_message("❌ Erro ao remover tabela: " . $e->getMessage(), 'error');
            return false;
        }
    } else {
        log_message("ℹ️ Tabela {$tableName} não existe", 'info');
        return true;
    }
}

// ============================================
// EXECUTAR MIGRATIONS
// ============================================

$action = $_GET['action'] ?? 'migrate';

if ($action === 'rollback') {
    log_message("🔄 Iniciando ROLLBACK...", 'warning');
    rollback_orders_table($pdo);
    
} else {
    log_message("🚀 Iniciando MIGRATIONS...");
    migrate_orders_table($pdo);
    log_message("🎉 Migrations concluídas!");
}

// ============================================
// EXIBIR RESULTADOS
// ============================================
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Migrations - UNLI</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .content {
            padding: 30px;
        }
        
        .log-item {
            padding: 12px 16px;
            margin-bottom: 8px;
            border-radius: 8px;
            border-left: 4px solid;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .log-item.info {
            background: #e3f2fd;
            border-left-color: #2196F3;
            color: #1565c0;
        }
        
        .log-item.success {
            background: #e8f5e9;
            border-left-color: #4caf50;
            color: #2e7d32;
        }
        
        .log-item.warning {
            background: #fff3e0;
            border-left-color: #ff9800;
            color: #e65100;
        }
        
        .log-item.error {
            background: #ffebee;
            border-left-color: #f44336;
            color: #c62828;
        }
        
        .time {
            opacity: 0.6;
            font-size: 11px;
            min-width: 60px;
        }
        
        .actions {
            margin-top: 30px;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 8px;
            text-align: center;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 5px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: #f44336;
            color: white;
        }
        
        .btn-danger:hover {
            background: #d32f2f;
            transform: translateY(-2px);
        }
        
        .info-box {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2196F3;
        }
        
        .info-box strong {
            color: #1565c0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗄️ Sistema de Migrations</h1>
            <p>Banco de Dados: <?= DB_NAME ?> | Prefixo: <?= DB_PREFIX ?></p>
        </div>
        
        <div class="content">
            <div class="info-box">
                <strong>ℹ️ Informação:</strong> Este sistema cria/atualiza tabelas de forma inteligente sem perder dados existentes.
            </div>
            
            <?php foreach ($logs as $log): ?>
                <div class="log-item <?= $log['type'] ?>">
                    <span class="time"><?= $log['time'] ?></span>
                    <span><?= $log['message'] ?></span>
                </div>
            <?php endforeach; ?>
            
            <div class="actions">
                <a href="migrate.php?password=<?= $MIGRATION_PASSWORD ?>" class="btn btn-primary">
                    🔄 Executar Migrations
                </a>
                <a href="migrate.php?password=<?= $MIGRATION_PASSWORD ?>&action=rollback" 
                   class="btn btn-danger"
                   onclick="return confirm('⚠️ ATENÇÃO: Isso irá REMOVER a tabela orders e todos os dados! Continuar?')">
                    ⚠️ Rollback (Remover Tabela)
                </a>
            </div>
        </div>
    </div>
</body>
</html>
