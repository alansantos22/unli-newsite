<?php
/**
 * ============================================
 * DEBUG COMPLETO - MIGRATION
 * ============================================
 * 
 * Arquivo para debugar erros 500 na migration
 * Acesse: https://unli.com.br/api/debug-migrate.php?password=Unli2026secure
 */

// CONFIGURAÇÃO DE ERROS - MOSTRAR TUDO
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Função para log detalhado
function debug_log($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    
    // Log no arquivo
    file_put_contents(__DIR__ . '/debug.log', $logMessage, FILE_APPEND | LOCK_EX);
    
    // Mostrar na tela também
    echo "<div style='margin: 5px 0; padding: 5px; border-left: 3px solid " . 
         ($level === 'ERROR' ? 'red' : ($level === 'WARNING' ? 'orange' : 'blue')) . 
         "; background: #f9f9f9;'>";
    echo "<strong>[{$level}]</strong> {$message}";
    echo "</div>";
}

// HTML Header
echo "<!DOCTYPE html>
<html>
<head>
    <title>Debug Migration</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; }
        h1, h2 { color: #333; }
        pre { background: #f5f5f5; padding: 15px; border: 1px solid #ddd; overflow-x: auto; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔍 Debug Completo - Migration</h1>";

// Proteção com senha
$MIGRATION_PASSWORD = 'Unli2026secure';

debug_log("Iniciando debug da migration...");

if (!isset($_GET['password']) || $_GET['password'] !== $MIGRATION_PASSWORD) {
    debug_log("Tentativa de acesso sem senha válida", 'ERROR');
    http_response_code(401);
    die('<div style="color: red;">🚫 Acesso negado! Use: debug-migrate.php?password=Unli2026secure</div></body></html>');
}

debug_log("Senha de acesso válida ✓");

// TESTE 1: Verificar arquivos essenciais
echo "<h2>📁 Teste 1: Verificação de Arquivos</h2>";

$files_to_check = [
    'db.config.php' => __DIR__ . '/db.config.php',
    'migrate.php' => __DIR__ . '/migrate.php',
    '.htaccess' => __DIR__ . '/.htaccess'
];

foreach ($files_to_check as $name => $path) {
    if (file_exists($path)) {
        debug_log("Arquivo {$name} encontrado ✓");
        debug_log("Permissões de {$name}: " . substr(sprintf('%o', fileperms($path)), -4));
    } else {
        debug_log("Arquivo {$name} NÃO encontrado!", 'ERROR');
    }
}

// TESTE 2: Verificar se pode incluir o config
echo "<h2>⚙️ Teste 2: Carregamento de Configuração</h2>";

try {
    debug_log("Tentando definir DB_CONFIG_ACCESS...");
    define('DB_CONFIG_ACCESS', true);
    debug_log("DB_CONFIG_ACCESS definido ✓");
    
    debug_log("Tentando incluir db.config.php...");
    require_once __DIR__ . '/db.config.php';
    debug_log("db.config.php incluído com sucesso ✓");
    
    debug_log("Verificando constantes definidas...");
    $constants = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_CHARSET'];
    foreach ($constants as $const) {
        if (defined($const)) {
            $value = constant($const);
            $display_value = ($const === 'DB_PASS') ? str_repeat('*', strlen($value)) : $value;
            debug_log("Constante {$const} = {$display_value} ✓");
        } else {
            debug_log("Constante {$const} NÃO definida!", 'ERROR');
        }
    }
    
} catch (Exception $e) {
    debug_log("ERRO ao carregar configuração: " . $e->getMessage(), 'ERROR');
    debug_log("Stack trace: " . $e->getTraceAsString(), 'ERROR');
    echo "</div></body></html>";
    exit;
} catch (Error $e) {
    debug_log("ERRO FATAL ao carregar configuração: " . $e->getMessage(), 'ERROR');
    debug_log("Stack trace: " . $e->getTraceAsString(), 'ERROR');
    echo "</div></body></html>";
    exit;
}

// TESTE 3: Teste de conexão com banco
echo "<h2>🗄️ Teste 3: Conexão com Banco de Dados</h2>";

try {
    debug_log("Tentando conectar ao MySQL...");
    debug_log("Host: " . DB_HOST);
    debug_log("Database: " . DB_NAME);
    debug_log("User: " . DB_USER);
    
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
    debug_log("DSN: " . $dsn);
    
    $pdo = new PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    debug_log("Conexão PDO estabelecida com sucesso ✓");
    
    // Testar uma query simples
    $stmt = $pdo->query("SELECT VERSION() as version, DATABASE() as db_name");
    $result = $stmt->fetch();
    
    debug_log("MySQL Version: " . $result['version'] . " ✓");
    debug_log("Database atual: " . $result['db_name'] . " ✓");
    
} catch (PDOException $e) {
    debug_log("ERRO de conexão PDO: " . $e->getMessage(), 'ERROR');
    debug_log("Código do erro: " . $e->getCode(), 'ERROR');
    echo "</div></body></html>";
    exit;
} catch (Exception $e) {
    debug_log("ERRO geral na conexão: " . $e->getMessage(), 'ERROR');
    echo "</div></body></html>";
    exit;
}

// TESTE 4: Verificar estrutura atual do banco
echo "<h2>📊 Teste 4: Estrutura Atual do Banco</h2>";

try {
    debug_log("Listando tabelas existentes...");
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        debug_log("Banco está vazio (nenhuma tabela encontrada)");
    } else {
        debug_log("Tabelas encontradas: " . count($tables));
        foreach ($tables as $table) {
            debug_log("• Tabela: {$table}");
        }
    }
    
} catch (PDOException $e) {
    debug_log("ERRO ao listar tabelas: " . $e->getMessage(), 'ERROR');
}

// TESTE 5: Teste de criação de tabela simples
echo "<h2>🛠️ Teste 5: Criação de Tabela de Teste</h2>";

try {
    $tableName = (defined('DB_PREFIX') ? DB_PREFIX : '') . 'test_migration';
    
    debug_log("Tentando criar tabela de teste: {$tableName}");
    
    // Primeiro, remover se existir
    $pdo->exec("DROP TABLE IF EXISTS `{$tableName}`");
    debug_log("Tabela de teste removida (se existia)");
    
    // Criar tabela simples
    $sql = "CREATE TABLE `{$tableName}` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `test_field` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    debug_log("Tabela de teste criada com sucesso ✓");
    
    // Testar inserção
    $stmt = $pdo->prepare("INSERT INTO `{$tableName}` (test_field) VALUES (?)");
    $stmt->execute(['Teste de funcionamento']);
    debug_log("Dados de teste inseridos ✓");
    
    // Testar seleção
    $stmt = $pdo->query("SELECT * FROM `{$tableName}`");
    $result = $stmt->fetch();
    debug_log("Dados lidos: ID=" . $result['id'] . ", Campo=" . $result['test_field'] . " ✓");
    
    // Limpar
    $pdo->exec("DROP TABLE `{$tableName}`");
    debug_log("Tabela de teste removida ✓");
    
} catch (PDOException $e) {
    debug_log("ERRO no teste de tabela: " . $e->getMessage(), 'ERROR');
    debug_log("SQL que falhou: " . (isset($sql) ? $sql : 'N/A'), 'ERROR');
}

// TESTE 6: Verificar conteúdo do arquivo .htaccess
echo "<h2>🔒 Teste 6: Verificação do .htaccess</h2>";

$htaccess_path = __DIR__ . '/.htaccess';
if (file_exists($htaccess_path)) {
    debug_log("Arquivo .htaccess encontrado");
    $content = file_get_contents($htaccess_path);
    debug_log("Tamanho do .htaccess: " . strlen($content) . " bytes");
    
    // Verificar regras que podem bloquear
    if (strpos($content, 'RewriteRule ^(lib|includes|onboarding)') !== false) {
        debug_log("⚠️ AVISO: .htaccess está bloqueando acesso a pastas lib/onboarding", 'WARNING');
    }
    
    if (strpos($content, 'db\.config\.php') !== false && strpos($content, 'Deny from all') !== false) {
        debug_log("⚠️ AVISO: .htaccess pode estar bloqueando db.config.php", 'WARNING');
    }
    
    echo "<h3>Conteúdo do .htaccess:</h3>";
    echo "<pre>" . htmlspecialchars($content) . "</pre>";
} else {
    debug_log("Arquivo .htaccess NÃO encontrado");
}

echo "<h2>🎯 Conclusão do Debug</h2>";
debug_log("Debug concluído! Verifique os logs acima para identificar problemas.");

echo "<p><strong>Próximos passos:</strong></p>";
echo "<ul>";
echo "<li>Se todos os testes passaram, o problema pode ser no arquivo migrate.php original</li>";
echo "<li>Se algum teste falhou, corrija o problema identificado</li>";
echo "<li>Verifique o arquivo debug.log para logs detalhados</li>";
echo "</ul>";

echo "<p><a href='debug.log' target='_blank'>📄 Ver arquivo debug.log</a></p>";

echo "</div></body></html>";
?>