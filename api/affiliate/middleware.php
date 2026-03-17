<?php
/**
 * Affiliate Panel - JWT Authentication Middleware
 * 
 * Reutiliza as funções JWT do SDR middleware, mas com auth functions
 * específicas para afiliados.
 */

if (!defined('DB_CONFIG_ACCESS')) {
    define('DB_CONFIG_ACCESS', true);
}

require_once __DIR__ . '/../db.config.php';
require_once __DIR__ . '/../lib/database.php';

// Reutilizar funções JWT do SDR
require_once __DIR__ . '/../sdr/middleware.php';

// ============================================
// Affiliate Tier Configuration
// ============================================

function get_tier_config() {
    return [
        'bronze_1'   => ['name' => 'O Recruta',       'min_sales' => 0,     'commission' => 8.00,  'icon' => '🥉'],
        'bronze_2'   => ['name' => 'O Sobrevivente',   'min_sales' => 1000,  'commission' => 9.00,  'icon' => '🥉'],
        'prata_1'    => ['name' => 'O Especialista',   'min_sales' => 4000,  'commission' => 11.00, 'icon' => '🥈'],
        'prata_2'    => ['name' => 'O Estrategista',   'min_sales' => 8000,  'commission' => 12.00, 'icon' => '🥈'],
        'ouro_1'     => ['name' => 'O Elite',          'min_sales' => 15000, 'commission' => 13.50, 'icon' => '🥇'],
        'ouro_2'     => ['name' => 'O Influenciador',  'min_sales' => 30000, 'commission' => 14.00, 'icon' => '🥇'],
        'diamante_1' => ['name' => 'O Mestre',         'min_sales' => 50000, 'commission' => 14.50, 'icon' => '💎'],
        'diamante_2' => ['name' => 'A Lenda',          'min_sales' => 80000, 'commission' => 15.00, 'icon' => '💎'],
    ];
}

/**
 * Calcula o tier correto baseado no montante total de vendas
 */
function calculate_tier($totalSalesAmount) {
    $tiers = get_tier_config();
    $currentTier = 'bronze_1';
    
    foreach ($tiers as $tierKey => $tierData) {
        if ($totalSalesAmount >= $tierData['min_sales']) {
            $currentTier = $tierKey;
        }
    }
    
    return $currentTier;
}

/**
 * Gera um hash único para o afiliado (16 caracteres hex)
 */
function generate_affiliate_hash() {
    return bin2hex(random_bytes(8));
}

// ============================================
// Affiliate Authentication
// ============================================

/**
 * Autentica de forma obrigatória a requisição como afiliado.
 * Se falhar, envia 401 e encerra.
 * 
 * @return array Dados do afiliado
 */
function require_affiliate_auth() {
    $token = get_bearer_token();
    
    if (!$token) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Token não fornecido']);
        exit;
    }
    
    $payload = jwt_validate($token);
    
    if (!$payload || !isset($payload['affiliate_id'])) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Token inválido ou expirado']);
        exit;
    }
    
    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro de conexão com banco de dados']);
        exit;
    }
    
    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, email, whatsapp, affiliate_hash, 
               tier, commission_rate, total_sales_amount, is_active
        FROM {$prefix}affiliate_users WHERE id = ?
    ");
    $stmt->execute([$payload['affiliate_id']]);
    $affiliate = $stmt->fetch();
    
    if (!$affiliate || !$affiliate['is_active']) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Conta de afiliado desativada']);
        exit;
    }
    
    return [
        'affiliate_id' => (int)$affiliate['id'],
        'first_name' => $affiliate['first_name'],
        'last_name' => $affiliate['last_name'],
        'email' => $affiliate['email'],
        'whatsapp' => $affiliate['whatsapp'],
        'affiliate_hash' => $affiliate['affiliate_hash'],
        'tier' => $affiliate['tier'],
        'commission_rate' => (float)$affiliate['commission_rate'],
        'total_sales_amount' => (float)$affiliate['total_sales_amount']
    ];
}
