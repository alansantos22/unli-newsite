<?php
/**
 * Diagnostic for panel-mgmt.php routing.
 * Acesse: /api/admin/mgmt-diag.php  (com o token JWT no header Authorization: Bearer <token>)
 * Cada etapa testa um pedaço do fluxo real.
 */

ini_set('display_errors', '1');
error_reporting(E_ALL);

if (!defined('SECURE_CONFIG_ACCESS')) define('SECURE_CONFIG_ACCESS', true);
$secureConfig = __DIR__ . '/../config.secure.php';
if (file_exists($secureConfig)) require_once $secureConfig;

require_once __DIR__ . '/../lib/cors.php';
header('Content-Type: application/json; charset=utf-8');

$steps = [];

// ── ETAPA 1: middleware.php carregou? ──────────────────────────────────────
try {
    require_once __DIR__ . '/middleware.php';
    $steps[] = ['etapa' => '1_middleware', 'ok' => true,
        'funcoes' => [
            'require_admin_auth' => function_exists('require_admin_auth'),
            'get_db_connection'  => function_exists('get_db_connection'),
        ]
    ];
} catch (\Throwable $e) {
    $steps[] = ['etapa' => '1_middleware', 'ok' => false, 'erro' => $e->getMessage(), 'arquivo' => $e->getFile(), 'linha' => $e->getLine()];
    echo json_encode(['ok' => false, 'steps' => $steps], JSON_PRETTY_PRINT);
    exit;
}

// ── ETAPA 2: JWT / autenticação ────────────────────────────────────────────
try {
    $admin = require_admin_auth();
    $steps[] = ['etapa' => '2_auth', 'ok' => true,
        'admin_id' => $admin['admin_id'] ?? 'n/a',
        'role'     => $admin['role']     ?? 'n/a',
    ];
} catch (\Throwable $e) {
    $steps[] = ['etapa' => '2_auth', 'ok' => false, 'erro' => $e->getMessage()];
    echo json_encode(['ok' => false, 'steps' => $steps], JSON_PRETTY_PRINT);
    exit;
}

// ── ETAPA 3: conexão PDO ───────────────────────────────────────────────────
try {
    $pdo    = get_db_connection();
    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
    $steps[] = ['etapa' => '3_pdo', 'ok' => true, 'prefix' => $prefix];
} catch (\Throwable $e) {
    $steps[] = ['etapa' => '3_pdo', 'ok' => false, 'erro' => $e->getMessage()];
    echo json_encode(['ok' => false, 'steps' => $steps], JSON_PRETTY_PRINT);
    exit;
}

// ── ETAPA 4: arquivos handler existem? ────────────────────────────────────
$handlers = [
    'affiliates' => __DIR__ . '/../lib/admin-handlers/affiliates.php',
    'sdrs'       => __DIR__ . '/../lib/admin-handlers/sdrs.php',
    'clients'    => __DIR__ . '/../lib/admin-handlers/clients.php',
];
foreach ($handlers as $nome => $caminho) {
    $existe = file_exists($caminho);
    $steps[] = [
        'etapa'   => "4_arquivo_$nome",
        'ok'      => $existe,
        'caminho' => $caminho,
        'bytes'   => $existe ? filesize($caminho) : 0,
        'legivel' => $existe ? is_readable($caminho) : false,
    ];
}

// ── ETAPA 5: panel-mgmt.php tamanho atual ─────────────────────────────────
$panelPath = __DIR__ . '/panel-mgmt.php';
$steps[] = [
    'etapa'      => '5_panel_mgmt_atual',
    'ok'         => file_exists($panelPath),
    'bytes'      => file_exists($panelPath) ? filesize($panelPath) : 0,
    'esperado'   => '< 5000 bytes (somente router)',
    'suspeito'   => file_exists($panelPath) && filesize($panelPath) > 5000,
];

// ── ETAPA 6: require sdrs.php + chamar handle_list_sdrs ──────────────────
try {
    $action = 'list_sdrs';
    ob_start();
    require __DIR__ . '/../lib/admin-handlers/sdrs.php';
    $saida = ob_get_clean();
    $json  = json_decode($saida, true);
    $steps[] = [
        'etapa'        => '6_handler_list_sdrs',
        'ok'           => isset($json['ok']) && $json['ok'] === true,
        'json_valido'  => $json !== null,
        'chave_ok'     => $json['ok'] ?? null,
        'total_sdrs'   => isset($json['data']) ? count($json['data']) : null,
        'preview_saida' => substr($saida, 0, 400),
    ];
} catch (\Throwable $e) {
    if (ob_get_level()) ob_end_clean();
    $steps[] = [
        'etapa'   => '6_handler_list_sdrs',
        'ok'      => false,
        'erro'    => $e->getMessage(),
        'arquivo' => $e->getFile(),
        'linha'   => $e->getLine(),
        'trace'   => substr($e->getTraceAsString(), 0, 600),
    ];
}

// ── ETAPA 7: require affiliates.php + chamar handle_list_affiliates ───────
try {
    $action = 'list_affiliates';
    ob_start();
    require __DIR__ . '/../lib/admin-handlers/affiliates.php';
    $saida = ob_get_clean();
    $json  = json_decode($saida, true);
    $steps[] = [
        'etapa'            => '7_handler_list_affiliates',
        'ok'               => isset($json['ok']) && $json['ok'] === true,
        'json_valido'      => $json !== null,
        'chave_ok'         => $json['ok'] ?? null,
        'total_afiliados'  => isset($json['data']) ? count($json['data']) : null,
        'preview_saida'    => substr($saida, 0, 400),
    ];
} catch (\Throwable $e) {
    if (ob_get_level()) ob_end_clean();
    $steps[] = [
        'etapa'   => '7_handler_list_affiliates',
        'ok'      => false,
        'erro'    => $e->getMessage(),
        'arquivo' => $e->getFile(),
        'linha'   => $e->getLine(),
        'trace'   => substr($e->getTraceAsString(), 0, 600),
    ];
}

// ── ETAPA 8: require clients.php + chamar handle_list_clients ─────────────
try {
    $action = 'list_clients';
    ob_start();
    require __DIR__ . '/../lib/admin-handlers/clients.php';
    $saida = ob_get_clean();
    $json  = json_decode($saida, true);
    $steps[] = [
        'etapa'          => '8_handler_list_clients',
        'ok'             => isset($json['ok']) && $json['ok'] === true,
        'json_valido'    => $json !== null,
        'chave_ok'       => $json['ok'] ?? null,
        'total_clientes' => isset($json['data']) ? count($json['data']) : null,
        'preview_saida'  => substr($saida, 0, 400),
    ];
} catch (\Throwable $e) {
    if (ob_get_level()) ob_end_clean();
    $steps[] = [
        'etapa'   => '8_handler_list_clients',
        'ok'      => false,
        'erro'    => $e->getMessage(),
        'arquivo' => $e->getFile(),
        'linha'   => $e->getLine(),
        'trace'   => substr($e->getTraceAsString(), 0, 600),
    ];
}

// ── ETAPA 9: panel-mgmt.php atual tem "password_hash" ou "DELETE FROM"? ──
// (indica que o arquivo antigo ainda não foi enviado ao servidor)
if (file_exists($panelPath)) {
    $conteudo = file_get_contents($panelPath);
    $temPassHash  = strpos($conteudo, 'password_hash') !== false;
    $temDelete    = stripos($conteudo, 'DELETE FROM')   !== false;
    $steps[] = [
        'etapa'           => '9_panel_mgmt_conteudo',
        'ok'              => !$temPassHash && !$temDelete,
        'tem_password_hash' => $temPassHash,
        'tem_DELETE_FROM'   => $temDelete,
        'aviso'           => ($temPassHash || $temDelete)
            ? 'ARQUIVO ANTIGO NO SERVIDOR – envie o novo panel-mgmt.php (70 linhas)'
            : 'Arquivo novo OK (somente router)',
    ];
}

// ── ETAPA 10: último erro do PHP error_log ─────────────────────────────────
$logPath = ini_get('error_log');
$ultimasLinhas = [];
if ($logPath && file_exists($logPath) && is_readable($logPath)) {
    $linhas = file($logPath);
    $ultimasLinhas = array_slice($linhas, -20);
    $ultimasLinhas = array_values(array_filter($ultimasLinhas, fn($l) => stripos($l, 'panel-mgmt') !== false || stripos($l, 'admin-handler') !== false || stripos($l, 'ADMIN-USERS') !== false));
}
$steps[] = [
    'etapa'       => '10_error_log',
    'ok'          => true,
    'log_path'    => $logPath ?: 'não configurado',
    'ultimas_linhas_relevantes' => $ultimasLinhas ?: ['(nenhuma linha relacionada encontrada)'],
];

// ── Resultado final ────────────────────────────────────────────────────────
$todoOk = !in_array(false, array_column($steps, 'ok'), true);
echo json_encode([
    'ok'     => $todoOk,
    'resumo' => $todoOk ? 'Todos os passos OK' : 'Falha detectada — veja steps abaixo',
    'steps'  => $steps,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
