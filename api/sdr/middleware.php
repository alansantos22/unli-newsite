<?php
/**
 * SDR Panel - JWT Authentication Middleware
 * 
 * Funções para criar e validar tokens JWT para o painel SDR.
 * Usa HMAC-SHA256 com a chave JWT_SECRET já existente no db.config.php.
 * Sem dependências externas — 100% PHP nativo.
 */

if (!defined('DB_CONFIG_ACCESS')) {
    define('DB_CONFIG_ACCESS', true);
}

require_once __DIR__ . '/../db.config.php';
require_once __DIR__ . '/../lib/database.php';

// ============================================
// JWT Functions
// ============================================

/**
 * Codifica em base64url (sem padding)
 */
function jwt_base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Decodifica de base64url
 */
function jwt_base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

/**
 * Cria um token JWT
 * 
 * @param array $payload Dados a incluir no token
 * @param int $expiry Tempo de expiração em segundos (padrão: 8 horas)
 * @return string Token JWT
 */
function jwt_create($payload, $expiry = 28800) {
    $header = jwt_base64url_encode(json_encode([
        'alg' => 'HS256',
        'typ' => 'JWT'
    ]));

    $payload['iat'] = time();
    $payload['exp'] = time() + $expiry;
    
    $payloadEncoded = jwt_base64url_encode(json_encode($payload));
    
    $signature = hash_hmac('sha256', "$header.$payloadEncoded", JWT_SECRET, true);
    $signatureEncoded = jwt_base64url_encode($signature);
    
    return "$header.$payloadEncoded.$signatureEncoded";
}

/**
 * Valida e decodifica um token JWT
 * 
 * @param string $token Token JWT
 * @return array|false Payload decodificado ou false se inválido
 */
function jwt_validate($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;
    
    [$header, $payload, $signature] = $parts;
    
    // Verificar assinatura
    $expectedSig = jwt_base64url_encode(
        hash_hmac('sha256', "$header.$payload", JWT_SECRET, true)
    );
    
    if (!hash_equals($expectedSig, $signature)) return false;
    
    // Decodificar payload
    $data = json_decode(jwt_base64url_decode($payload), true);
    if (!$data) return false;
    
    // Verificar expiração
    if (isset($data['exp']) && $data['exp'] < time()) return false;
    
    return $data;
}

// ============================================
// Authentication Helpers
// ============================================

/**
 * Extrai o token JWT do header Authorization
 * 
 * @return string|null Token ou null
 */
function get_bearer_token() {
    $headers = null;
    
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $headers = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        $headers = $requestHeaders['Authorization'] ?? $requestHeaders['authorization'] ?? null;
    }
    
    if ($headers && preg_match('/Bearer\s+(.+)$/i', $headers, $matches)) {
        return $matches[1];
    }
    
    return null;
}

/**
 * Autentica a requisição e retorna dados do SDR.
 * Se falhar, envia 401 e encerra.
 * 
 * @return array Dados do SDR do JWT payload
 */
function require_sdr_auth() {
    $token = get_bearer_token();
    
    if (!$token) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Token não fornecido']);
        exit;
    }
    
    $payload = jwt_validate($token);
    
    if (!$payload || !isset($payload['sdr_id'])) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Token inválido ou expirado']);
        exit;
    }
    
    // Verificar se o SDR ainda está ativo no banco
    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro de conexão com banco de dados']);
        exit;
    }
    
    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
    $stmt = $pdo->prepare("SELECT id, name, email, whatsapp, is_active FROM {$prefix}sdr_users WHERE id = ?");
    $stmt->execute([$payload['sdr_id']]);
    $sdr = $stmt->fetch();
    
    if (!$sdr || !$sdr['is_active']) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Conta SDR desativada']);
        exit;
    }
    
    return [
        'sdr_id' => (int)$sdr['id'],
        'name' => $sdr['name'],
        'email' => $sdr['email'],
        'whatsapp' => $sdr['whatsapp']
    ];
}

// ============================================
// Rate Limiting (Login)
// ============================================

/**
 * Verifica rate limit para login (5 tentativas / 15 min por IP)
 * Usa arquivo temporário simples (compatível com Hostinger)
 * 
 * @return bool true se permitido, false se bloqueado
 */
function check_login_rate_limit() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $limitFile = sys_get_temp_dir() . '/sdr_login_' . md5($ip) . '.json';
    
    $data = ['attempts' => [], 'blocked_until' => 0];
    
    if (file_exists($limitFile)) {
        $raw = file_get_contents($limitFile);
        $data = json_decode($raw, true) ?: $data;
    }
    
    // Se bloqueado, verificar se já expirou
    if ($data['blocked_until'] > time()) {
        return false;
    }
    
    // Limpar tentativas antigas (> 15 min)
    $cutoff = time() - 900;
    $data['attempts'] = array_filter($data['attempts'], function($t) use ($cutoff) { return $t > $cutoff; });
    
    // Se muitas tentativas, bloquear
    if (count($data['attempts']) >= 5) {
        $data['blocked_until'] = time() + 900; // bloquear por 15 min
        file_put_contents($limitFile, json_encode($data));
        return false;
    }
    
    return true;
}

/**
 * Registra uma tentativa de login
 */
function record_login_attempt() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $limitFile = sys_get_temp_dir() . '/sdr_login_' . md5($ip) . '.json';
    
    $data = ['attempts' => [], 'blocked_until' => 0];
    
    if (file_exists($limitFile)) {
        $raw = file_get_contents($limitFile);
        $data = json_decode($raw, true) ?: $data;
    }
    
    $data['attempts'][] = time();
    file_put_contents($limitFile, json_encode($data));
}

/**
 * Limpa as tentativas de login após sucesso
 */
function clear_login_attempts() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $limitFile = sys_get_temp_dir() . '/sdr_login_' . md5($ip) . '.json';
    
    if (file_exists($limitFile)) {
        @unlink($limitFile);
    }
}
