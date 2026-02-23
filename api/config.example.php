<?php
/**
 * ============================================
 * ARQUIVO DE EXEMPLO - CONFIGURAÇÕES
 * ============================================
 * 
 * 📋 INSTRUÇÕES DE INSTALAÇÃO
 * 
 * 1. Copie este arquivo e renomeie para: config.secure.php
 *    Comando: cp config.example.php config.secure.php
 * 
 * 2. Configure permissões seguras:
 *    Comando: chmod 600 config.secure.php
 * 
 * 3. Edite config.secure.php e substitua os valores de exemplo
 * 
 * 4. Obtenha suas credenciais do gateway de pagamento (Pagar.me)
 *    https://dash.pagar.me/
 * 
 * 5. Verifique se config.secure.php está no .gitignore
 * 
 * 6. NUNCA faça commit do arquivo config.secure.php!
 */

// ============================================
// PROTEÇÃO
// ============================================
if (!defined('SECURE_CONFIG_ACCESS')) {
    die('🚫 Acesso negado!');
}

// ============================================
// CREDENCIAIS GATEWAY DE PAGAMENTO (Pagar.me)
// ============================================

// 🔑 API Key (Backend)
// Obtenha em: Dashboard Pagar.me > Configurações > API Keys
define('PAGARME_API_KEY', 'SUA_API_KEY_PAGARME_AQUI');

// 🔓 Public Key (Frontend)
define('PAGARME_PUBLIC_KEY', 'SUA_PUBLIC_KEY_PAGARME_AQUI');

// ============================================
// PARA TESTES (Ambiente de Desenvolvimento)
// ============================================
// Descomente estas linhas para usar credenciais de teste:

// define('PAGARME_API_KEY', 'TEST-SUA_API_KEY_PAGARME_AQUI');
// define('PAGARME_PUBLIC_KEY', 'TEST-SUA_PUBLIC_KEY_PAGARME_AQUI');

// ============================================
// CONFIGURAÇÕES GERAIS
// ============================================

// Debug (true = desenvolvimento | false = produção)
define('DEBUG_MODE', true);

// URL da API
define('PAGARME_API_URL', 'https://api.pagar.me/core/v5');

// Timeouts
define('PAGARME_TIMEOUT', 30);
define('PAGARME_CONNECT_TIMEOUT', 10);

// Webhook Secret (gere com: openssl rand -base64 32)
define('PAGARME_WEBHOOK_SECRET', 'GERE_UMA_STRING_ALEATORIA_COMPLEXA_AQUI');

// ============================================
// SSL (Segurança)
// ============================================
// ⚠️ SEMPRE true em produção!
define('SSL_VERIFY_PEER', true);
define('SSL_VERIFY_HOST', 2);

// ============================================
// BANCO DE DADOS (Opcional)
// ============================================
// Descomente se for usar banco de dados:

// define('DB_HOST', 'localhost');
// define('DB_NAME', 'seu_banco');
// define('DB_USER', 'seu_usuario');
// define('DB_PASS', 'sua_senha');
// define('DB_CHARSET', 'utf8mb4');

// ============================================
// E-MAIL SMTP (Opcional)
// ============================================
// Descomente para enviar e-mails de confirmação:

// define('SMTP_HOST', 'smtp.gmail.com');
// define('SMTP_PORT', 587);
// define('SMTP_USER', 'seu@email.com');
// define('SMTP_PASS', 'sua_senha_app');
// define('SMTP_FROM_NAME', 'Sua Empresa');

// ============================================
// FUNÇÕES DE VALIDAÇÃO
// ============================================

function validateCredentials() {
    $placeholders = ['SUA_API_KEY_PAGARME_AQUI', 'SUA_PUBLIC_KEY_PAGARME_AQUI'];
    
    foreach ($placeholders as $placeholder) {
        if (strpos(PAGARME_API_KEY, $placeholder) !== false) {
            return false;
        }
        if (strpos(PAGARME_PUBLIC_KEY, $placeholder) !== false) {
            return false;
        }
    }
    
    return true;
}

function isProduction() {
    return !DEBUG_MODE && strpos(PAGARME_API_KEY, 'TEST-') === false;
}

function isSecureConnection() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || $_SERVER['SERVER_PORT'] == 443
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
}

// Forçar HTTPS em produção
if (isProduction() && !isSecureConnection() && php_sapi_name() !== 'cli') {
    header('HTTP/1.1 403 Forbidden');
    die('🚫 Use HTTPS em produção!');
}

define('CONFIG_LOADED', true);

?>
