<?php
/**
 * ============================================
 * TEMPLATE DE CONFIGURAÇÃO - BANCO DE DADOS
 * ============================================
 * 
 * 📋 INSTRUÇÕES DE INSTALAÇÃO:
 * 
 * 1. Copie este arquivo:
 *    cp db.config.example.php db.config.php
 * 
 * 2. Edite db.config.php com suas credenciais reais
 * 
 * 3. Configure permissões:
 *    chmod 600 db.config.php
 * 
 * 4. NUNCA faça commit do arquivo db.config.php
 */

// ============================================
// PROTEÇÃO
// ============================================
if (!defined('DB_CONFIG_ACCESS')) {
    die('🚫 Acesso negado!');
}

// ============================================
// BANCO DE DADOS
// ============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'seu_banco_de_dados');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha_segura');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');
define('DB_PORT', ''); // Deixe vazio para usar porta padrão (3306)
define('DB_PREFIX', 'unli_');

// ============================================
// CHAVES DE SEGURANÇA
// ============================================
// GERE CHAVES NOVAS COM:
// php -r "echo bin2hex(random_bytes(32));"

define('ENCRYPTION_KEY', 'GERE_UMA_CHAVE_ALEATORIA_AQUI_32_BYTES');
define('JWT_SECRET', 'GERE_UMA_CHAVE_ALEATORIA_AQUI_32_BYTES');
define('SECURITY_SALT', 'GERE_UMA_CHAVE_ALEATORIA_AQUI_32_BYTES');

// ============================================
// AMBIENTE
// ============================================
define('ENVIRONMENT', 'development'); // development | production

// ============================================
// CORS
// ============================================
define('CORS_ORIGINS', [
    'http://localhost:8080',
    'https://seudominio.com.br'
]);

// ============================================
// RATE LIMITING
// ============================================
define('RATE_LIMIT_REQUESTS', 60);
define('RATE_LIMIT_WINDOW', 60);

// ============================================
// E-MAIL
// ============================================
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_AUTH', true);
define('SMTP_USER', 'seu-email@gmail.com');
define('SMTP_PASS', 'sua-senha-app-gmail');
define('MAIL_FROM_EMAIL', 'contato@seudominio.com.br');
define('MAIL_FROM_NAME', 'Sua Empresa');

// ============================================
// SESSÃO
// ============================================
define('SESSION_LIFETIME', 7200);
define('SESSION_NAME', 'UNLI_SESSION');

// ============================================
// LIMITES
// ============================================
define('API_TIMEOUT', 30);
define('MAX_UPLOAD_SIZE', 10485760); // 10MB
define('MAX_EXECUTION_TIME', 60);

// ============================================
// DIRETÓRIOS
// ============================================
define('ROOT_DIR', dirname(__DIR__));
define('API_DIR', __DIR__);
define('LOGS_DIR', __DIR__ . '/logs');
define('UPLOADS_DIR', __DIR__ . '/uploads');
define('TEMP_DIR', __DIR__ . '/temp');

// Criar diretórios
$directories = [LOGS_DIR, UPLOADS_DIR, TEMP_DIR];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// ============================================
// FUNÇÕES
// ============================================
function getDatabase() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST;
            if (DB_PORT) $dsn .= ";port=" . DB_PORT;
            $dsn .= ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode(['success' => false, 'message' => 'Erro de conexão']));
        }
    }
    
    return $pdo;
}

function validateDatabaseConfig() {
    return true;
}

function testDatabaseConnection() {
    try {
        $pdo = getDatabase();
        $stmt = $pdo->query('SELECT 1');
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function logSecurityEvent($event, $details = []) {
    $logFile = LOGS_DIR . '/security.log';
    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'event' => $event,
        'details' => $details
    ];
    @file_put_contents($logFile, json_encode($entry) . PHP_EOL, FILE_APPEND);
}

validateDatabaseConfig();
date_default_timezone_set('America/Sao_Paulo');
set_time_limit(MAX_EXECUTION_TIME);
define('DB_CONFIG_LOADED', true);

?>
