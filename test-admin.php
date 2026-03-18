<?php
/**
 * TEST ADMIN - Diagnostico completo do painel Super Admin
 * Acesse: https://unli.com.br/test-admin.php?password=TestAdmin2026
 * Exportar CSV: https://unli.com.br/test-admin.php?password=TestAdmin2026&export=1
 * APAGUE ESTE ARQUIVO APOS OS TESTES!
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');

$TEST_PASSWORD = 'TestAdmin2026';
if (!isset($_GET['password']) || !hash_equals($TEST_PASSWORD, $_GET['password'])) {
    http_response_code(401);
    die('Acesso negado! Use: test-admin.php?password=' . $TEST_PASSWORD);
}

$EXPORT_MODE = isset($_GET['export']) && $_GET['export'] === '1';

// ============================================
// COLETA DE DADOS (usada tanto no HTML quanto no CSV)
// ============================================
$allRows = array(); // array de [timestamp, source, level, label, detail]

function collect($label, $ok, $detail, $section) {
    global $allRows;
    $allRows[] = array(
        'ts'      => date('Y-m-d H:i:s'),
        'section' => $section,
        'level'   => $ok ? 'OK' : 'ERROR',
        'label'   => $label,
        'detail'  => $detail,
    );
}

function qsafe($pdo, $sql, $params = array()) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return array('ok' => true, 'rows' => $stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        return array('ok' => false, 'error' => $e->getMessage());
    }
}

function tbl_exists($pdo, $table) {
    $s = $pdo->prepare("SHOW TABLES LIKE ?");
    $s->execute(array($table));
    return $s->rowCount() > 0;
}

function col_exists($pdo, $table, $col) {
    try {
        $s = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
        $s->execute(array($col));
        return $s->rowCount() > 0;
    } catch (Exception $e) { return false; }
}

// --- ETAPA 1: PHP ---
collect('Versao PHP', true, phpversion(), 'PHP');
collect('Extensao pdo_mysql', extension_loaded('pdo_mysql'), extension_loaded('pdo_mysql') ? 'OK' : 'FALTANDO', 'PHP');
collect('Extensao json', extension_loaded('json'), extension_loaded('json') ? 'OK' : 'FALTANDO', 'PHP');

// --- ETAPA 2: Arquivos de config ---
$depFiles = array(
    'api/db.config.php', 'api/lib/cors.php', 'api/lib/database.php', 'api/sdr/middleware.php',
);
foreach ($depFiles as $f) {
    $e = file_exists(__DIR__ . '/' . $f);
    collect($f, $e, $e ? 'OK' : 'NAO ENCONTRADO', 'Arquivos de config');
}

// --- ETAPA 3: Arquivos API Admin ---
$adminFiles = array(
    'api/admin/middleware.php', 'api/admin/auth.php', 'api/admin/dashboard.php',
    'api/admin/users.php', 'api/admin/finance.php', 'api/admin/gamification.php',
);
foreach ($adminFiles as $f) {
    $e = file_exists(__DIR__ . '/' . $f);
    collect($f, $e, $e ? number_format(filesize(__DIR__.'/'.$f)) . ' bytes' : 'NAO ENCONTRADO', 'Arquivos API Admin');
}

// --- ETAPA 4: Carregar db.config ---
$pdo = null;
$dbConf = __DIR__ . '/api/db.config.php';
if (!file_exists($dbConf)) {
    collect('api/db.config.php', false, 'Arquivo nao existe', 'Conexao com banco');
} else {
    if (!defined('DB_CONFIG_ACCESS')) define('DB_CONFIG_ACCESS', true);
    try {
        require_once $dbConf;
        collect('DB_HOST', true, defined('DB_HOST') ? DB_HOST : 'NAO DEFINIDO', 'Conexao com banco');
        collect('DB_NAME', true, defined('DB_NAME') ? DB_NAME : 'NAO DEFINIDO', 'Conexao com banco');
        collect('DB_PREFIX', true, defined('DB_PREFIX') ? '"' . DB_PREFIX . '"' : '(vazio)', 'Conexao com banco');
    } catch (Exception $e) {
        collect('Carregar db.config.php', false, $e->getMessage(), 'Conexao com banco');
    }
}

// --- ETAPA 5: Conexao PDO ---
if (defined('DB_HOST')) {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . (defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4');
        $pdo = new PDO($dsn, DB_USER, DB_PASS, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ));
        $ver = $pdo->query("SELECT VERSION()")->fetchColumn();
        collect('Conexao PDO', true, 'MySQL ' . $ver, 'Conexao com banco');
    } catch (Exception $e) {
        collect('Conexao PDO', false, $e->getMessage(), 'Conexao com banco');
    }
}

// --- ETAPA 6: Tabelas ---
if ($pdo) {
    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
    $tables = array(
        'orders', 'admin_users', 'admin_audit_log', 'sdr_users',
        'affiliate_users', 'affiliate_referrals', 'affiliate_tier_history',
        'affiliate_badges', 'affiliate_missions', 'affiliate_mission_progress', 'affiliate_user_badges',
    );
    foreach ($tables as $key) {
        $tbl    = $prefix . $key;
        $exists = tbl_exists($pdo, $tbl);
        $extra  = '';
        if (!$exists) {
            if ($prefix && tbl_exists($pdo, $key)) {
                $extra = 'ATENCAO: existe como "' . $key . '" SEM prefixo "' . $prefix . '"!';
            } else {
                $extra = 'NAO EXISTE';
            }
        } else {
            try { $extra = $pdo->query("SELECT COUNT(*) FROM `{$tbl}`")->fetchColumn() . ' registros'; } catch (Exception $e) { $extra = 'erro ao contar'; }
        }
        collect($tbl, $exists, $extra, 'Tabelas');
    }
}

// --- ETAPA 7: Colunas orders ---
if ($pdo) {
    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
    $ordTbl = $prefix . 'orders';
    if (tbl_exists($pdo, $ordTbl)) {
        $cols = array('id','customer_name','customer_email','company_name','total_amount',
                      'payment_status','payment_method_manual','sdr_id','affiliate_id','affiliate_hash','created_at');
        foreach ($cols as $col) {
            $ok = col_exists($pdo, $ordTbl, $col);
            collect('orders.' . $col, $ok, $ok ? 'presente' : 'FALTANDO — execute migration', 'Colunas orders');
        }
    } else {
        collect('Tabela orders inexistente', false, 'pulando verificacao de colunas', 'Colunas orders');
    }
}

// --- ETAPA 8: Admin users cadastrados ---
if ($pdo) {
    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
    $admTbl = $prefix . 'admin_users';
    if (tbl_exists($pdo, $admTbl)) {
        $cnt = $pdo->query("SELECT COUNT(*) FROM `{$admTbl}`")->fetchColumn();
        collect('Admins cadastrados', $cnt > 0, $cnt . ' admin(s)', 'Admin Users');
        if ($cnt > 0) {
            $rows = $pdo->query("SELECT id, name, email, role, is_active FROM `{$admTbl}`")->fetchAll();
            foreach ($rows as $a) {
                collect(
                    '#' . $a['id'] . ' ' . $a['name'],
                    (bool)$a['is_active'],
                    $a['email'] . ' | role: ' . $a['role'] . ' | ' . ($a['is_active'] ? 'ativo' : 'INATIVO'),
                    'Admin Users'
                );
            }
        }
    } else {
        collect('admin_users nao existe', false, 'execute migrate-admin.php', 'Admin Users');
    }
}

// --- ETAPA 9: Query list_clients (causa do 500) ---
if ($pdo) {
    $prefix  = defined('DB_PREFIX') ? DB_PREFIX : '';
    $ordTbl  = $prefix . 'orders';
    $sdrTbl  = $prefix . 'sdr_users';
    $affTbl  = $prefix . 'affiliate_users';
    $o = tbl_exists($pdo, $ordTbl);
    $s = tbl_exists($pdo, $sdrTbl);
    $a = tbl_exists($pdo, $affTbl);
    collect($ordTbl . ' disponivel', $o, $o ? 'OK' : 'AUSENTE', 'Query list_clients');
    collect($sdrTbl . ' disponivel', $s, $s ? 'OK' : 'AUSENTE', 'Query list_clients');
    collect($affTbl . ' disponivel', $a, $a ? 'OK' : 'AUSENTE', 'Query list_clients');

    if ($o && $s && $a) {
        $res = qsafe($pdo, "
            SELECT o.id, o.customer_name, s.name AS sdr_name,
                   CONCAT(a.first_name, ' ', a.last_name) AS affiliate_name
            FROM `{$ordTbl}` o
            LEFT JOIN `{$sdrTbl}` s ON s.id = o.sdr_id
            LEFT JOIN `{$affTbl}` a ON a.id = o.affiliate_id
            LIMIT 2
        ");
        collect('SELECT com JOINs executado', $res['ok'],
            $res['ok'] ? count($res['rows']) . ' linha(s) — OK!' : $res['error'],
            'Query list_clients');
    } else {
        collect('SELECT ignorado', false, 'corrija as tabelas ausentes primeiro', 'Query list_clients');
    }
}

// --- ETAPA 10: Cadeia de includes do admin ---
$sdrMW = __DIR__ . '/api/sdr/middleware.php';
collect('sdr/middleware.php legivel', is_readable($sdrMW), is_readable($sdrMW) ? 'OK' : 'Nao legivel/ausente', 'Cadeia de includes');
if (is_readable($sdrMW)) {
    $mwContent = file_get_contents($sdrMW);
    collect('funcao get_bearer_token()', strpos($mwContent, 'function get_bearer_token') !== false, '', 'Cadeia de includes');
    collect('funcao jwt_validate()', strpos($mwContent, 'function jwt_validate') !== false, '', 'Cadeia de includes');
    collect('JWT_SECRET definido', strpos($mwContent, 'JWT_SECRET') !== false, '', 'Cadeia de includes');
}
$dbLib = __DIR__ . '/api/lib/database.php';
if (is_readable($dbLib)) {
    $libContent = file_get_contents($dbLib);
    collect('funcao get_db_connection()', strpos($libContent, 'function get_db_connection') !== false, '', 'Cadeia de includes');
}

// --- Logs da aplicacao (api/logs) ---
$logDir = __DIR__ . '/api/logs';
if (is_dir($logDir)) {
    $logFiles = glob($logDir . '/*.log');
    if (!empty($logFiles)) {
        usort($logFiles, function($a, $b) { return filemtime($b) - filemtime($a); });
        foreach (array_slice($logFiles, 0, 3) as $lf) {
            $lines = file($lf, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (!$lines) continue;
            foreach (array_slice($lines, -100) as $line) {
                if (preg_match('/^\[([^\]]+)\]\s+(.+)$/', $line, $m)) {
                    collect($m[2], strpos($m[2], 'rror') === false && strpos($m[2], 'RROR') === false, $m[1], 'App Logs / ' . basename($lf));
                } else {
                    collect(trim($line), true, '', 'App Logs / ' . basename($lf));
                }
            }
        }
    } else {
        collect('api/logs/*.log', true, 'Nenhum arquivo de log encontrado (normal se sistema nao gerou erros)', 'App Logs');
    }
}

// --- Log de erros PHP do servidor ---
$phpLog = ini_get('error_log');
$phpLogCandidates = array($phpLog, '/var/log/php_errors.log', __DIR__ . '/php_errors.log', __DIR__ . '/../logs/error.log');
foreach ($phpLogCandidates as $candidate) {
    if ($candidate && is_readable($candidate)) {
        $lines = file($candidate, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) continue;
        collect('Arquivo de log PHP', true, $candidate . ' (' . count($lines) . ' linhas total)', 'PHP Error Log');
        foreach (array_slice($lines, -100) as $line) {
            if (preg_match('/^\[([^\]]+)\]\s+(.+)$/', $line, $m)) {
                collect($m[2], false, $m[1], 'PHP Error Log');
            } else {
                collect(trim($line), false, '', 'PHP Error Log');
            }
        }
        break;
    }
}

// ============================================
// MODO EXPORT CSV
// ============================================
if ($EXPORT_MODE) {
    $filename = 'unli-admin-diagnostic-' . date('Y-m-d_H-i-s') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store');
    header('Pragma: no-cache');

    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 para Excel
    fputcsv($out, array('timestamp', 'section', 'level', 'label', 'detail'), ';');
    fputcsv($out, array(date('Y-m-d H:i:s'), 'meta', 'INFO',
        'Exportado de: ' . ($_SERVER['HTTP_HOST'] ?? 'unknown'),
        'PHP ' . phpversion() . ' | DB: ' . (defined('DB_NAME') ? DB_NAME : 'N/A') . ' | Prefix: ' . (defined('DB_PREFIX') ? DB_PREFIX : 'N/A')
    ), ';');
    fputcsv($out, array('', '', '', '', ''), ';');
    foreach ($allRows as $r) {
        fputcsv($out, array($r['ts'], $r['section'], $r['level'], $r['label'], $r['detail']), ';');
    }
    fclose($out);
    exit;
}

// ============================================
// MODO HTML: renderizar com flush progressivo
// ============================================
ob_start();
$totalOK   = count(array_filter($allRows, function($r) { return $r['level'] === 'OK'; }));
$totalFail = count(array_filter($allRows, function($r) { return $r['level'] === 'ERROR'; }));

$exportUrl = '?password=' . urlencode($TEST_PASSWORD) . '&export=1';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Test Admin | Unli</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0a0a14;color:#e5e7eb;padding:2rem;min-height:100vh}
.wrap{max-width:940px;margin:0 auto}
.topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.8rem;flex-wrap:wrap;gap:12px}
h1{font-size:1.4rem;color:#f59e0b}
.sub{color:#64748b;font-size:.78rem;margin-top:2px}
.badge-row{display:flex;gap:10px;align-items:center}
.badge{padding:6px 16px;border-radius:8px;font-weight:700;font-size:.85rem}
.badge.ok{background:#065f4625;border:1px solid #10b981;color:#10b981}
.badge.fail{background:#7f1d1d25;border:1px solid #ef4444;color:#ef4444}
.btn-csv{display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:linear-gradient(135deg,#f59e0b,#d97706);color:#0f172a;border:none;border-radius:10px;font-weight:700;font-size:.82rem;cursor:pointer;text-decoration:none;transition:transform .15s,box-shadow .15s}
.btn-csv:hover{transform:translateY(-1px);box-shadow:0 4px 14px rgba(245,158,11,.35)}
.section{margin-bottom:1.1rem}
.stitle{font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:#64748b;padding:5px 12px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-bottom:none;border-radius:6px 6px 0 0}
.rows{border:1px solid rgba(255,255,255,.06);border-radius:0 0 8px 8px;overflow:hidden}
.row{display:flex;align-items:baseline;padding:7px 12px;gap:10px;border-bottom:1px solid rgba(255,255,255,.04);font-size:.79rem}
.row:last-child{border-bottom:none}
.row:hover{background:rgba(255,255,255,.02)}
.ic{width:14px;flex-shrink:0;font-style:normal}
.ic.ok{color:#10b981}.ic.fail{color:#ef4444}
.lbl{color:#cbd5e1;font-family:monospace;flex:1;min-width:0;word-break:break-all}
.det{color:#64748b;font-size:.73rem;flex:2;min-width:0;word-break:break-all}
.det.err{color:#fca5a5}
.danger{background:#7f1d1d25;border:1px solid #ef4444;border-radius:8px;padding:.9rem 1rem;margin-top:1.8rem;color:#fca5a5;font-size:.78rem}
.tip{background:#1c1917;border:1px solid #92400e;border-radius:8px;padding:.9rem 1rem;margin-top:.8rem;font-size:.76rem;color:#fbbf24;line-height:1.9}
.tip code{background:#0f0f1a;padding:2px 5px;border-radius:4px;font-size:.73rem}
</style>
</head>
<body>
<div class="wrap">

<div class="topbar">
  <div>
    <h1>&#128269; Test Admin &mdash; Diagnostico da Torre de Controle</h1>
    <div class="sub"><?= date('d/m/Y H:i:s') ?> &bull; DB: <?= defined('DB_NAME') ? htmlspecialchars(DB_NAME) : '?' ?> &bull; Prefix: "<?= defined('DB_PREFIX') ? htmlspecialchars(DB_PREFIX) : '' ?>"</div>
  </div>
  <div class="badge-row">
    <span class="badge ok">&#10003; <?= $totalOK ?> OK</span>
    <span class="badge fail">&#10007; <?= $totalFail ?> erro(s)</span>
    <a class="btn-csv" href="<?= htmlspecialchars($exportUrl) ?>">
      &#8595; Exportar CSV
    </a>
  </div>
</div>

<?php
$currentSection = '';
foreach ($allRows as $r) {
    if ($r['section'] !== $currentSection) {
        if ($currentSection !== '') echo '</div></div>';
        echo '<div class="section"><div class="stitle">' . htmlspecialchars($r['section']) . '</div><div class="rows">';
        $currentSection = $r['section'];
    }
    $ok   = $r['level'] === 'OK';
    $icon = $ok ? '<i class="ic ok">&#10003;</i>' : '<i class="ic fail">&#10007;</i>';
    $dcls = $ok ? '' : ' err';
    echo '<div class="row">' . $icon
       . '<span class="lbl">' . htmlspecialchars($r['label']) . '</span>'
       . '<span class="det' . $dcls . '">' . htmlspecialchars($r['detail']) . '</span></div>';
}
if ($currentSection !== '') echo '</div></div>';
?>

<?php if ($totalFail > 0): ?>
<div class="tip">
  <strong>Solucoes rapidas:</strong><br>
  Tabelas admin faltando &rarr; <code>migrate-admin.php?password=AdminMigrate2026</code><br>
  Nenhum admin cadastrado &rarr; <code>migrate-admin.php?password=AdminMigrate2026&create_admin=1&admin_name=Alan&admin_email=admin@unli.com.br&admin_pass=SenhaForte123</code><br>
  Tabelas affiliate/sdr faltando &rarr; <code>migrate-affiliates.php?password=AffMigrate2026</code> e <code>migrate-sdr.php?password=SdrMigrate2026</code>
</div>
<?php endif; ?>

<div class="danger">&#9888; <strong>APAGUE ESTE ARQUIVO APOS OS TESTES!</strong></div>
</div>
</body>
</html>
<?php
echo ob_get_clean();
