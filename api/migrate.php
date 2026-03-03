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

// CONFIGURAÇÃO DE ERROS - SEMPRE MOSTRAR
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/migration-error.log');

// LOG INICIAL - TESTAR SE PHP ESTÁ FUNCIONANDO
$debug_log = __DIR__ . '/migration-debug.log';
file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] INICIO: PHP executando\n", FILE_APPEND);

// ============================================
// PROTEÇÃO COM SENHA (ALTERE ESTA SENHA!)
// ============================================
file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] PASSO 1: Definindo senha\n", FILE_APPEND);
$MIGRATION_PASSWORD = 'Unli2026secure';

file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] PASSO 2: Verificando parâmetros GET\n", FILE_APPEND);
file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] GET password: " . ($_GET['password'] ?? 'NÃO DEFINIDO') . "\n", FILE_APPEND);
file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] Senha esperada: {$MIGRATION_PASSWORD}\n", FILE_APPEND);

// Verificar senha
if (!isset($_GET['password']) || $_GET['password'] !== $MIGRATION_PASSWORD) {
    file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] ERRO: Senha inválida ou não fornecida\n", FILE_APPEND);
    http_response_code(401);
    die('🚫 Acesso negado! Use: migrate.php?password=SUA_SENHA');
}

file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] PASSO 3: Senha validada com sucesso\n", FILE_APPEND);

// ============================================
// CONFIGURAÇÃO
// ============================================
file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] PASSO 4: Iniciando configuração do banco\n", FILE_APPEND);

file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] PASSO 4.1: Definindo DB_CONFIG_ACCESS\n", FILE_APPEND);
define('DB_CONFIG_ACCESS', true);

file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] PASSO 4.2: Tentando incluir db.config.php\n", FILE_APPEND);
$config_path = __DIR__ . '/db.config.php';
file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] Caminho config: {$config_path}\n", FILE_APPEND);
file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] Arquivo existe: " . (file_exists($config_path) ? 'SIM' : 'NÃO') . "\n", FILE_APPEND);

if (!file_exists($config_path)) {
    file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] ERRO FATAL: db.config.php não encontrado!\n", FILE_APPEND);
    die('❌ Erro: Arquivo db.config.php não encontrado em: ' . $config_path);
}

try {
    require_once $config_path;
    file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] PASSO 4.3: db.config.php incluído com sucesso\n", FILE_APPEND);
} catch (Exception $e) {
    file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] ERRO ao incluir config: " . $e->getMessage() . "\n", FILE_APPEND);
    die('❌ Erro ao incluir configuração: ' . $e->getMessage());
} catch (Error $e) {
    file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] ERRO FATAL ao incluir config: " . $e->getMessage() . "\n", FILE_APPEND);
    die('❌ Erro fatal ao incluir configuração: ' . $e->getMessage());
}

file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] PASSO 4.4: Verificando constantes definidas\n", FILE_APPEND);
$required_constants = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_CHARSET', 'DB_PREFIX'];
foreach ($required_constants as $const) {
    if (!defined($const)) {
        file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] ERRO: Constante {$const} não definida\n", FILE_APPEND);
        die("❌ Erro: Constante {$const} não definida no arquivo de configuração");
    } else {
        $value = ($const === 'DB_PASS') ? '***' : constant($const);
        file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] Constante {$const} = {$value}\n", FILE_APPEND);
    }
}

// Conectar ao banco
file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] PASSO 5: Tentando conectar ao banco de dados\n", FILE_APPEND);
file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] Host: " . DB_HOST . "\n", FILE_APPEND);
file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] Database: " . DB_NAME . "\n", FILE_APPEND);
file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] User: " . DB_USER . "\n", FILE_APPEND);

try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
    file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] DSN: {$dsn}\n", FILE_APPEND);
    
    $pdo = new PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] PASSO 5.1: Conexão PDO estabelecida com sucesso!\n", FILE_APPEND);
    
    // Testar conexão
    $version = $pdo->query("SELECT VERSION()")->fetchColumn();
    file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] MySQL Version: {$version}\n", FILE_APPEND);
    
} catch (PDOException $e) {
    file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] ERRO PDO: " . $e->getMessage() . "\n", FILE_APPEND);
    die('❌ Erro de conexão: ' . $e->getMessage());
} catch (Exception $e) {
    file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] ERRO geral na conexão: " . $e->getMessage() . "\n", FILE_APPEND);
    die('❌ Erro geral na conexão: ' . $e->getMessage());
}

// ============================================
// FUNÇÕES AUXILIARES
// ============================================

file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] PASSO 6: Definindo funções auxiliares\n", FILE_APPEND);

$logs = [];

function log_message($message, $type = 'info') {
    global $logs, $debug_log;
    $logs[] = [
        'type' => $type,
        'message' => $message,
        'time' => date('H:i:s')
    ];
    
    // Log também no arquivo de debug
    file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] LOG[{$type}]: {$message}\n", FILE_APPEND);
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
// MIGRATION: TABELA SDR_CONVERSATIONS
// ============================================

function migrate_sdr_conversations_table($pdo) {
    $tableName = DB_PREFIX . 'sdr_conversations';
    
    log_message("🔍 Verificando tabela {$tableName}...");
    
    if (!table_exists($pdo, $tableName)) {
        log_message("📦 Criando tabela {$tableName}...", 'warning');
        
        $sql = "CREATE TABLE `{$tableName}` (
            `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'ID interno',
            `conversation_id` VARCHAR(36) NOT NULL COMMENT 'UUID público da conversa',
            
            -- Rastreamento do visitante
            `ip_address` VARCHAR(45) NOT NULL COMMENT 'IP do visitante (IPv4/IPv6)',
            `user_agent` VARCHAR(500) DEFAULT NULL COMMENT 'User-Agent do navegador',
            
            -- Dados do cliente (preenchidos após checkout)
            `customer_name` VARCHAR(255) DEFAULT NULL COMMENT 'Nome do cliente (atualizado no checkout)',
            `customer_email` VARCHAR(255) DEFAULT NULL COMMENT 'Email do cliente (atualizado no checkout)',
            `customer_phone` VARCHAR(50) DEFAULT NULL COMMENT 'Telefone/WhatsApp (atualizado no checkout)',
            
            -- Estado da conversa
            `last_stage` VARCHAR(30) DEFAULT 'ABERTURA' COMMENT 'Último estágio do SDR',
            `client_data` JSON DEFAULT NULL COMMENT 'Dados extraídos do cliente (negócio, nicho, etc)',
            `suggested_plan` JSON DEFAULT NULL COMMENT 'Plano sugerido pela IA',
            `finished` TINYINT(1) DEFAULT 0 COMMENT 'Se a conversa foi finalizada',
            
            -- Vínculo com pedido
            `order_id` INT(11) DEFAULT NULL COMMENT 'FK: pedido criado a partir desta conversa',
            
            -- Estatísticas
            `total_messages` INT(11) DEFAULT 0 COMMENT 'Total de mensagens na conversa',
            
            -- Timestamps
            `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Início da conversa',
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última interação',
            
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_conversation_id` (`conversation_id`),
            INDEX `idx_ip` (`ip_address`),
            INDEX `idx_email` (`customer_email`),
            INDEX `idx_order_id` (`order_id`),
            INDEX `idx_stage` (`last_stage`),
            INDEX `idx_finished` (`finished`),
            INDEX `idx_started_at` (`started_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log de conversas do SDR (pré-venda)'";
        
        try {
            $pdo->exec($sql);
            log_message("✅ Tabela {$tableName} criada com sucesso!", 'success');
        } catch (PDOException $e) {
            log_message("❌ Erro ao criar tabela {$tableName}: " . $e->getMessage(), 'error');
            return false;
        }
    } else {
        log_message("✅ Tabela {$tableName} já existe");
    }
    
    return true;
}

// ============================================
// MIGRATION: TABELA SDR_MESSAGES
// ============================================

function migrate_sdr_messages_table($pdo) {
    $tableName = DB_PREFIX . 'sdr_messages';
    
    log_message("🔍 Verificando tabela {$tableName}...");
    
    if (!table_exists($pdo, $tableName)) {
        log_message("📦 Criando tabela {$tableName}...", 'warning');
        
        $convTableName = DB_PREFIX . 'sdr_conversations';
        
        $sql = "CREATE TABLE `{$tableName}` (
            `id` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'ID da mensagem',
            `conversation_id` INT(11) NOT NULL COMMENT 'FK: conversa SDR',
            
            -- Conteúdo
            `role` ENUM('user', 'assistant') NOT NULL COMMENT 'Quem enviou a mensagem',
            `content` TEXT NOT NULL COMMENT 'Conteúdo da mensagem',
            `stage` VARCHAR(30) DEFAULT NULL COMMENT 'Estágio do SDR neste momento',
            `metadata` JSON DEFAULT NULL COMMENT 'Dados extras (pricing, clientData, etc)',
            
            -- Timestamps
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Quando a mensagem foi enviada',
            
            PRIMARY KEY (`id`),
            INDEX `idx_conversation` (`conversation_id`),
            INDEX `idx_role` (`role`),
            INDEX `idx_created_at` (`created_at`),
            CONSTRAINT `fk_sdr_msg_conv` FOREIGN KEY (`conversation_id`) 
                REFERENCES `{$convTableName}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Mensagens individuais das conversas SDR'";
        
        try {
            $pdo->exec($sql);
            log_message("✅ Tabela {$tableName} criada com sucesso!", 'success');
        } catch (PDOException $e) {
            log_message("❌ Erro ao criar tabela {$tableName}: " . $e->getMessage(), 'error');
            return false;
        }
    } else {
        log_message("✅ Tabela {$tableName} já existe");
    }
    
    return true;
}

// ============================================
// MIGRATION: TABELA ONBOARDING_CONVERSATIONS
// ============================================

function migrate_onboarding_conversations_table($pdo) {
    $tableName = DB_PREFIX . 'onboarding_conversations';
    
    log_message("🔍 Verificando tabela {$tableName}...");
    
    if (!table_exists($pdo, $tableName)) {
        log_message("📦 Criando tabela {$tableName}...", 'warning');
        
        $sql = "CREATE TABLE `{$tableName}` (
            `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'ID interno',
            `conversation_id` VARCHAR(36) NOT NULL COMMENT 'UUID público da conversa',
            
            -- Vínculo com pedido/projeto
            `order_id` INT(11) DEFAULT NULL COMMENT 'FK: pedido associado',
            `onboarding_token` VARCHAR(64) DEFAULT NULL COMMENT 'Token do magic-link de onboarding',
            
            -- Dados do cliente (já disponíveis via pedido)
            `customer_name` VARCHAR(255) DEFAULT NULL COMMENT 'Nome do cliente',
            `customer_email` VARCHAR(255) DEFAULT NULL COMMENT 'Email do cliente',
            
            -- Contexto do projeto
            `plan_name` VARCHAR(100) DEFAULT NULL COMMENT 'Nome do plano contratado',
            `purchased_pages` JSON DEFAULT NULL COMMENT 'Páginas compradas',
            
            -- Estado da conversa
            `finished` TINYINT(1) DEFAULT 0 COMMENT 'Se a conversa foi finalizada',
            `extracted_data` JSON DEFAULT NULL COMMENT 'Dados extraídos pela IA (briefing)',
            
            -- Estatísticas
            `total_messages` INT(11) DEFAULT 0 COMMENT 'Total de mensagens na conversa',
            
            -- Timestamps
            `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Início da conversa',
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última interação',
            
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_conversation_id` (`conversation_id`),
            INDEX `idx_order_id` (`order_id`),
            INDEX `idx_token` (`onboarding_token`),
            INDEX `idx_email` (`customer_email`),
            INDEX `idx_finished` (`finished`),
            INDEX `idx_started_at` (`started_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log de conversas do onboarding (pós-venda)'";
        
        try {
            $pdo->exec($sql);
            log_message("✅ Tabela {$tableName} criada com sucesso!", 'success');
        } catch (PDOException $e) {
            log_message("❌ Erro ao criar tabela {$tableName}: " . $e->getMessage(), 'error');
            return false;
        }
    } else {
        log_message("✅ Tabela {$tableName} já existe");
    }
    
    return true;
}

// ============================================
// MIGRATION: TABELA ONBOARDING_MESSAGES
// ============================================

function migrate_onboarding_messages_table($pdo) {
    $tableName = DB_PREFIX . 'onboarding_messages';
    
    log_message("🔍 Verificando tabela {$tableName}...");
    
    if (!table_exists($pdo, $tableName)) {
        log_message("📦 Criando tabela {$tableName}...", 'warning');
        
        $convTableName = DB_PREFIX . 'onboarding_conversations';
        
        $sql = "CREATE TABLE `{$tableName}` (
            `id` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'ID da mensagem',
            `conversation_id` INT(11) NOT NULL COMMENT 'FK: conversa onboarding',
            
            -- Conteúdo
            `role` ENUM('user', 'assistant') NOT NULL COMMENT 'Quem enviou a mensagem',
            `content` TEXT NOT NULL COMMENT 'Conteúdo da mensagem',
            `metadata` JSON DEFAULT NULL COMMENT 'Dados extras',
            
            -- Timestamps
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Quando a mensagem foi enviada',
            
            PRIMARY KEY (`id`),
            INDEX `idx_conversation` (`conversation_id`),
            INDEX `idx_role` (`role`),
            INDEX `idx_created_at` (`created_at`),
            CONSTRAINT `fk_onb_msg_conv` FOREIGN KEY (`conversation_id`) 
                REFERENCES `{$convTableName}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Mensagens individuais das conversas de onboarding'";
        
        try {
            $pdo->exec($sql);
            log_message("✅ Tabela {$tableName} criada com sucesso!", 'success');
        } catch (PDOException $e) {
            log_message("❌ Erro ao criar tabela {$tableName}: " . $e->getMessage(), 'error');
            return false;
        }
    } else {
        log_message("✅ Tabela {$tableName} já existe");
    }
    
    return true;
}

// ============================================
// ROLLBACK: TABELAS DE CONVERSAS
// ============================================

function rollback_conversation_tables($pdo) {
    $tables = [
        DB_PREFIX . 'sdr_messages',
        DB_PREFIX . 'sdr_conversations',
        DB_PREFIX . 'onboarding_messages',
        DB_PREFIX . 'onboarding_conversations'
    ];
    
    foreach ($tables as $tableName) {
        log_message("⚠️ ROLLBACK: Removendo tabela {$tableName}...", 'warning');
        
        if (table_exists($pdo, $tableName)) {
            try {
                $pdo->exec("DROP TABLE `{$tableName}`");
                log_message("✅ Tabela {$tableName} removida!", 'success');
            } catch (PDOException $e) {
                log_message("❌ Erro ao remover {$tableName}: " . $e->getMessage(), 'error');
            }
        } else {
            log_message("ℹ️ Tabela {$tableName} não existe", 'info');
        }
    }
}

// ============================================
// EXECUTAR MIGRATIONS
// ============================================

file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] PASSO 7: Iniciando execução das migrations\n", FILE_APPEND);

$action = $_GET['action'] ?? 'migrate';
file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] Ação selecionada: {$action}\n", FILE_APPEND);

if ($action === 'rollback') {
    file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] Executando ROLLBACK\n", FILE_APPEND);
    log_message("🔄 Iniciando ROLLBACK...", 'warning');
    rollback_orders_table($pdo);
    rollback_conversation_tables($pdo);
    
} else {
    file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] Executando MIGRATIONS\n", FILE_APPEND);
    log_message("🚀 Iniciando MIGRATIONS...");
    migrate_orders_table($pdo);
    migrate_sdr_conversations_table($pdo);
    migrate_sdr_messages_table($pdo);
    migrate_onboarding_conversations_table($pdo);
    migrate_onboarding_messages_table($pdo);
    log_message("🎉 Migrations concluídas!");
}

file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] PASSO 8: Migrations executadas, iniciando exibição HTML\n", FILE_APPEND);

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
