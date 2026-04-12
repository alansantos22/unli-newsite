<?php
/**
 * MGMT-DIAG — Diagnóstico para panel-mgmt.php + handlers
 * Acesse: https://unli.com.br/mgmt-diag.php?password=DiagMgmt2026
 * APAGUE ESTE ARQUIVO APÓS OS TESTES!
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');

$PASS = 'DiagMgmt2026';
if (!isset($_GET['password']) || !hash_equals($PASS, $_GET['password'])) {
    http_response_code(401);
    die('Acesso negado. Use: ?password=' . $PASS);
}

// ── helpers ────────────────────────────────────────────────────────────────
$rows = [];
function row($section, $label, $ok, $detail) {
    global $rows;
    $rows[] = ['section' => $section, 'label' => $label, 'ok' => $ok, 'detail' => $detail];
}

// ── ETAPA 1: dependências de arquivo ──────────────────────────────────────
$files = [
    'api/db.config.php'                          => 'db config',
    'api/config.secure.php'                      => 'config.secure',
    'api/lib/cors.php'                           => 'cors',
    'api/lib/database.php'                       => 'lib/database',
    'api/sdr/middleware.php'                     => 'sdr/middleware (JWT)',
    'api/admin/middleware.php'                   => 'admin/middleware',
    'api/admin/panel-mgmt.php'                   => 'panel-mgmt.php',
    'api/lib/admin-handlers/affiliates.php'      => 'handler affiliates',
    'api/lib/admin-handlers/sdrs.php'            => 'handler sdrs',
    'api/lib/admin-handlers/clients.php'         => 'handler clients',
];
foreach ($files as $path => $label) {
    $full   = __DIR__ . '/' . $path;
    $existe = file_exists($full);
    $detail = $existe ? number_format(filesize($full)) . ' bytes' : 'NÃO ENCONTRADO';
    row('1 Arquivos', $label, $existe, $detail);
}

// ── ETAPA 2: conteúdo do panel-mgmt.php no servidor ───────────────────────
$pmPath = __DIR__ . '/api/admin/panel-mgmt.php';
if (file_exists($pmPath)) {
    $pmContent    = file_get_contents($pmPath);
    $temPwdHash   = strpos($pmContent, 'password_hash') !== false;
    $temDelFrom   = stripos($pmContent, 'DELETE FROM')  !== false;
    $temHandlers  = strpos($pmContent, 'admin-handlers') !== false;
    $bytes        = filesize($pmPath);

    row('2 panel-mgmt conteúdo', 'Tamanho', $bytes < 5000,
        $bytes . ' bytes — ' . ($bytes < 5000 ? 'OK (router)' : '⚠ GRANDE DEMAIS — arquivo antigo?'));
    row('2 panel-mgmt conteúdo', 'tem password_hash', !$temPwdHash,
        $temPwdHash ? '⚠ SIM — arquivo ANTIGO no servidor, envie o novo!' : 'Não — OK');
    row('2 panel-mgmt conteúdo', 'tem DELETE FROM',   !$temDelFrom,
        $temDelFrom  ? '⚠ SIM — arquivo ANTIGO no servidor, envie o novo!' : 'Não — OK');
    row('2 panel-mgmt conteúdo', 'aponta para admin-handlers', $temHandlers,
        $temHandlers ? 'Sim — OK (arquivo novo)' : '⚠ NÃO — arquivo antigo ou incorreto');
}

// ── ETAPA 3: carregar db.config e conectar ─────────────────────────────────
$pdo    = null;
$prefix = '';
$dbConf = __DIR__ . '/api/db.config.php';
if (file_exists($dbConf)) {
    if (!defined('DB_CONFIG_ACCESS')) define('DB_CONFIG_ACCESS', true);
    try {
        require_once $dbConf;
        $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
        row('3 Banco', 'db.config.php carregou', true, 'DB_HOST=' . (defined('DB_HOST') ? DB_HOST : '?') . ' DB_NAME=' . (defined('DB_NAME') ? DB_NAME : '?') . ' DB_PREFIX="' . $prefix . '"');
    } catch (\Throwable $e) {
        row('3 Banco', 'db.config.php carregou', false, $e->getMessage());
    }
}
if (defined('DB_HOST')) {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . (defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4');
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        $ver = $pdo->query('SELECT VERSION()')->fetchColumn();
        row('3 Banco', 'Conexão PDO', true, 'MySQL ' . $ver);
    } catch (\Throwable $e) {
        row('3 Banco', 'Conexão PDO', false, $e->getMessage());
    }
}

// ── ETAPA 4: middleware.php carrega ───────────────────────────────────────
$middlewareOk = false;
if (!defined('SECURE_CONFIG_ACCESS')) define('SECURE_CONFIG_ACCESS', true);
$sc = __DIR__ . '/api/config.secure.php';
if (file_exists($sc)) require_once $sc;

try {
    require_once __DIR__ . '/api/admin/middleware.php';
    $middlewareOk = true;
    row('4 Middleware', 'require_once admin/middleware.php', true, 'carregou sem erro');
    row('4 Middleware', 'function require_admin_auth', function_exists('require_admin_auth'), function_exists('require_admin_auth') ? 'existe' : 'NÃO EXISTE');
    row('4 Middleware', 'function get_db_connection', function_exists('get_db_connection'),   function_exists('get_db_connection')  ? 'existe' : 'NÃO EXISTE');
    row('4 Middleware', 'function admin_audit_log',   function_exists('admin_audit_log'),       function_exists('admin_audit_log')    ? 'existe' : 'NÃO EXISTE — handlers vão falhar');
} catch (\Throwable $e) {
    row('4 Middleware', 'require_once admin/middleware.php', false, $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
}

// ── ETAPA 5: chamar handler sdrs.php diretamente ──────────────────────────
if ($middlewareOk && $pdo) {
    // monta $admin mock e $action para o handler usar
    $admin  = ['admin_id' => 0, 'role' => 'super_admin'];
    $action = 'list_sdrs';
    try {
        ob_start();
        require __DIR__ . '/api/lib/admin-handlers/sdrs.php';
        $saida = ob_get_clean();
        $json  = json_decode($saida, true);
        $ok    = isset($json['ok']) && $json['ok'] === true;
        row('5 Handler SDRs', 'list_sdrs retornou JSON ok=true', $ok,
            $ok ? 'total=' . count($json['data']) . ' SDRs' : 'ok=' . var_export($json['ok'] ?? null, true) . ' saida=' . substr($saida, 0, 200));
    } catch (\Throwable $e) {
        if (ob_get_level()) ob_end_clean();
        row('5 Handler SDRs', 'list_sdrs lançou exceção', false,
            $e->getMessage() . ' em ' . basename($e->getFile()) . ':' . $e->getLine() . "\n" . substr($e->getTraceAsString(), 0, 400));
    }
} else {
    row('5 Handler SDRs', 'list_sdrs (pulado)', false, 'middleware ou PDO não disponível');
}

// ── ETAPA 6: chamar handler affiliates.php diretamente ────────────────────
if ($middlewareOk && $pdo) {
    $admin  = ['admin_id' => 0, 'role' => 'super_admin'];
    $action = 'list_affiliates';
    try {
        ob_start();
        require __DIR__ . '/api/lib/admin-handlers/affiliates.php';
        $saida = ob_get_clean();
        $json  = json_decode($saida, true);
        $ok    = isset($json['ok']) && $json['ok'] === true;
        row('6 Handler Affiliates', 'list_affiliates retornou JSON ok=true', $ok,
            $ok ? 'total=' . count($json['data']) . ' afiliados' : 'ok=' . var_export($json['ok'] ?? null, true) . ' saida=' . substr($saida, 0, 200));
    } catch (\Throwable $e) {
        if (ob_get_level()) ob_end_clean();
        row('6 Handler Affiliates', 'list_affiliates lançou exceção', false,
            $e->getMessage() . ' em ' . basename($e->getFile()) . ':' . $e->getLine());
    }
} else {
    row('6 Handler Affiliates', 'list_affiliates (pulado)', false, 'middleware ou PDO não disponível');
}

// ── ETAPA 7: chamar handler clients.php diretamente ───────────────────────
if ($middlewareOk && $pdo) {
    $admin  = ['admin_id' => 0, 'role' => 'super_admin'];
    $action = 'list_clients';
    try {
        ob_start();
        require __DIR__ . '/api/lib/admin-handlers/clients.php';
        $saida = ob_get_clean();
        $json  = json_decode($saida, true);
        $ok    = isset($json['ok']) && $json['ok'] === true;
        row('7 Handler Clients', 'list_clients retornou JSON ok=true', $ok,
            $ok ? 'total=' . $json['total'] . ' clientes' : 'ok=' . var_export($json['ok'] ?? null, true) . ' saida=' . substr($saida, 0, 200));
    } catch (\Throwable $e) {
        if (ob_get_level()) ob_end_clean();
        row('7 Handler Clients', 'list_clients lançou exceção', false,
            $e->getMessage() . ' em ' . basename($e->getFile()) . ':' . $e->getLine());
    }
} else {
    row('7 Handler Clients', 'list_clients (pulado)', false, 'middleware ou PDO não disponível');
}

// ── ETAPA 8: error_log entries recentes ───────────────────────────────────
$logPath   = ini_get('error_log');
$logLines  = [];
if ($logPath && file_exists($logPath) && is_readable($logPath)) {
    $all      = file($logPath);
    $recentes = array_slice($all, -100);
    $logLines = array_values(array_filter($recentes, fn($l) =>
        stripos($l, 'panel-mgmt')    !== false ||
        stripos($l, 'admin-handler') !== false ||
        stripos($l, 'ADMIN-USERS')   !== false
    ));
}
row('8 Error Log', 'Caminho do error_log', true, $logPath ?: 'não configurado (ini)');
if ($logLines) {
    foreach (array_slice($logLines, -10) as $linha) {
        row('8 Error Log', 'Entrada', false, trim($linha));
    }
} else {
    row('8 Error Log', 'Entradas relacionadas panel-mgmt', true, '(nenhuma — WAF bloqueia antes do PHP, normal)');
}

// ── Renderizar HTML ────────────────────────────────────────────────────────
$sections = array_unique(array_column($rows, 'section'));
$totalOk  = count(array_filter($rows, fn($r) => $r['ok']));
$totalErr = count(array_filter($rows, fn($r) => !$r['ok']));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>MGMT-DIAG — <?= date('d/m/Y H:i:s') ?></title>
<style>
body{font-family:monospace;background:#0f0f0f;color:#e0e0e0;margin:0;padding:16px}
h1{color:#fff;margin:0 0 4px}
.sub{color:#888;font-size:13px;margin-bottom:20px}
.summary{display:flex;gap:16px;margin-bottom:24px}
.badge{padding:8px 18px;border-radius:6px;font-size:15px;font-weight:bold}
.badge.ok{background:#1a3a1a;color:#4caf50;border:1px solid #4caf50}
.badge.err{background:#3a1a1a;color:#f44336;border:1px solid #f44336}
h2{color:#90caf9;font-size:14px;margin:20px 0 6px;border-bottom:1px solid #333;padding-bottom:4px}
table{width:100%;border-collapse:collapse;font-size:13px;margin-bottom:8px}
th{text-align:left;padding:6px 8px;background:#1e1e1e;color:#aaa;font-weight:normal;border-bottom:1px solid #333}
td{padding:5px 8px;border-bottom:1px solid #222;vertical-align:top;word-break:break-all}
tr.ok td:first-child{color:#4caf50}
tr.err td:first-child{color:#f44336}
.dot{display:inline-block;width:10px;height:10px;border-radius:50%;margin-right:6px}
.dot.ok{background:#4caf50}.dot.err{background:#f44336}
.detail{color:#9e9e9e;max-width:700px}
.warn{color:#ff9800}
</style>
</head>
<body>
<h1>🔍 MGMT-DIAG</h1>
<div class="sub">panel-mgmt.php + handlers — <?= date('d/m/Y H:i:s') ?> — <span class="warn">APAGUE APÓS OS TESTES</span></div>

<div class="summary">
  <div class="badge ok">✅ <?= $totalOk ?> OK</div>
  <div class="badge err">❌ <?= $totalErr ?> ERRO<?= $totalErr !== 1 ? 'S' : '' ?></div>
</div>

<?php foreach ($sections as $sec): ?>
<h2><?= htmlspecialchars($sec) ?></h2>
<table>
<tr><th style="width:260px">Verificação</th><th style="width:60px">Status</th><th>Detalhe</th></tr>
<?php foreach (array_filter($rows, fn($r) => $r['section'] === $sec) as $r): ?>
<tr class="<?= $r['ok'] ? 'ok' : 'err' ?>">
  <td><span class="dot <?= $r['ok'] ? 'ok' : 'err' ?>"></span><?= htmlspecialchars($r['label']) ?></td>
  <td><?= $r['ok'] ? '✅ OK' : '❌ ERRO' ?></td>
  <td class="detail"><?= nl2br(htmlspecialchars($r['detail'])) ?></td>
</tr>
<?php endforeach ?>
</table>
<?php endforeach ?>

</body>
</html>
