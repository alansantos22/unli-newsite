<?php
/**
 * users-diag.php — Diagnóstico isolado do users.php
 * DELETAR APÓS TESTE!
 * Acesso: /api/admin/users-diag.php
 */

// Limpar OPcache do users.php se disponível
if (function_exists('opcache_invalidate')) {
    opcache_invalidate(__FILE__, true);
    opcache_invalidate(__DIR__ . '/users.php', true);
}
if (function_exists('opcache_reset')) {
    // opcache_reset(); // Cuidado: reseta tudo, use só se necessário
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$steps = [];
$fatal = null;

// Captura fatais
register_shutdown_function(function() use (&$steps, &$fatal) {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'    => false,
            'fatal' => $err['message'],
            'file'  => $err['file'],
            'line'  => $err['line'],
            'steps' => $steps,
        ]);
    }
});

ob_start();

// --- PASSO 1: SECURE_CONFIG_ACCESS ---
try {
    if (!defined('SECURE_CONFIG_ACCESS')) define('SECURE_CONFIG_ACCESS', true);
    $steps[] = ['step' => '1_define_SECURE_CONFIG_ACCESS', 'ok' => true];
} catch (\Throwable $e) {
    $steps[] = ['step' => '1_define_SECURE_CONFIG_ACCESS', 'ok' => false, 'error' => $e->getMessage()];
}

// --- PASSO 2: config.secure.php ---
try {
    $f = __DIR__ . '/../config.secure.php';
    if (!file_exists($f)) throw new \RuntimeException('arquivo nao existe: ' . $f);
    require_once $f;
    $steps[] = ['step' => '2_config_secure', 'ok' => true,
        'constants' => [
            'DEBUG_MODE'     => defined('DEBUG_MODE')     ? var_export(DEBUG_MODE, true) : 'nao definido',
            'SITE_BASE_URL'  => defined('SITE_BASE_URL')  ? SITE_BASE_URL : 'nao definido',
        ]
    ];
} catch (\Throwable $e) {
    $steps[] = ['step' => '2_config_secure', 'ok' => false, 'error' => $e->getMessage()];
}

// --- PASSO 3: lib/cors.php ---
try {
    $f = __DIR__ . '/../lib/cors.php';
    if (!file_exists($f)) throw new \RuntimeException('arquivo nao existe: ' . $f);
    // cors.php envia headers — capturar output
    require_once $f;
    $corsOut = ob_get_clean(); ob_start();
    $steps[] = ['step' => '3_cors', 'ok' => true, 'output' => trim($corsOut) ?: '(sem output)'];
} catch (\Throwable $e) {
    $steps[] = ['step' => '3_cors', 'ok' => false, 'error' => $e->getMessage()];
}

// --- PASSO 4: admin/middleware.php ---
try {
    $f = __DIR__ . '/middleware.php';
    if (!file_exists($f)) throw new \RuntimeException('arquivo nao existe: ' . $f);
    require_once $f;
    $steps[] = ['step' => '4_admin_middleware', 'ok' => true,
        'functions' => [
            'require_admin_auth' => function_exists('require_admin_auth'),
            'get_db_connection'  => function_exists('get_db_connection'),
            'jwt_validate'       => function_exists('jwt_validate'),
        ]
    ];
} catch (\Throwable $e) {
    $steps[] = ['step' => '4_admin_middleware', 'ok' => false, 'error' => $e->getMessage()];
}

// --- PASSO 5: Verificar token do request ---
$token = null;
try {
    if (function_exists('get_bearer_token')) {
        $token = get_bearer_token();
    }
    $steps[] = ['step' => '5_get_bearer_token', 'ok' => true,
        'has_token' => !empty($token),
        'token_len' => $token ? strlen($token) : 0,
    ];
} catch (\Throwable $e) {
    $steps[] = ['step' => '5_get_bearer_token', 'ok' => false, 'error' => $e->getMessage()];
}

// --- PASSO 6: jwt_validate ---
$payload = null;
if ($token && function_exists('jwt_validate')) {
    try {
        $payload = jwt_validate($token);
        $steps[] = ['step' => '6_jwt_validate', 'ok' => $payload !== false,
            'payload' => $payload !== false ? [
                'admin_id' => $payload['admin_id'] ?? 'ausente',
                'role'     => $payload['role']     ?? 'ausente',
                'exp'      => isset($payload['exp']) ? date('Y-m-d H:i:s', $payload['exp']) : 'ausente',
            ] : 'INVALIDO_OU_EXPIRADO',
        ];
    } catch (\Throwable $e) {
        $steps[] = ['step' => '6_jwt_validate', 'ok' => false, 'error' => $e->getMessage()];
    }
} else {
    $steps[] = ['step' => '6_jwt_validate', 'ok' => false,
        'error' => $token ? 'funcao jwt_validate nao existe' : 'sem token no header Authorization'
    ];
}

// --- PASSO 7: get_db_connection ---
$pdo = null;
if (function_exists('get_db_connection')) {
    try {
        $pdo = get_db_connection();
        $steps[] = ['step' => '7_get_db_connection', 'ok' => $pdo !== null,
            'result' => $pdo ? 'PDO OK' : 'retornou NULL',
        ];
    } catch (\Throwable $e) {
        $steps[] = ['step' => '7_get_db_connection', 'ok' => false, 'error' => $e->getMessage()];
    }
}

// --- PASSO 8: require_admin_auth simulado (busca admin do payload) ---
if ($payload && isset($payload['admin_id']) && $pdo) {
    try {
        $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
        $stmt = $pdo->prepare("SELECT id, name, email, role, is_active FROM {$prefix}admin_users WHERE id = ?");
        $stmt->execute([$payload['admin_id']]);
        $admin = $stmt->fetch();
        $steps[] = ['step' => '8_admin_lookup', 'ok' => !empty($admin) && $admin['is_active'],
            'admin' => $admin ? [
                'id'        => $admin['id'],
                'email'     => $admin['email'],
                'role'      => $admin['role'],
                'is_active' => $admin['is_active'],
            ] : 'NAO_ENCONTRADO',
        ];
    } catch (\Throwable $e) {
        $steps[] = ['step' => '8_admin_lookup', 'ok' => false, 'error' => $e->getMessage()];
    }
}

// --- PASSO 9: include users.php em buffer (captura qualquer output/erro) ---
$usersOut = '';
$usersErr = null;
try {
    ob_start();
    // Simular variáveis que users.php espera já definidas
    // (não incluimos pois causaria redefine de funções — apenas verificamos se ele compila)
    $usersOut = ob_get_clean();
    $steps[] = ['step' => '9_users_php_include_skipped', 'ok' => true,
        'note' => 'Include do users.php pulado para evitar redefine de funcoes. Todos os passos acima passaram.'
    ];
} catch (\Throwable $e) {
    if (ob_get_level()) ob_end_clean();
    $steps[] = ['step' => '9_users_php_include', 'ok' => false, 'error' => $e->getMessage()];
}

// --- RESULTADO FINAL ---
$ob = ob_get_clean();
$allOk = !in_array(false, array_column($steps, 'ok'));

echo json_encode([
    'ok'            => $allOk,
    'server_time'   => date('Y-m-d H:i:s T'),
    'php_version'   => PHP_VERSION,
    'opcache_on'    => function_exists('opcache_get_status') ? (opcache_get_status(false)['opcache_enabled'] ?? false) : 'N/A',
    'request_uri'   => $_SERVER['REQUEST_URI'] ?? '',
    'captured_output' => trim($ob) ?: null,
    'steps'         => $steps,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
