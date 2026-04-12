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
    'api/admin/panel-mgmt.php', 'api/admin/finance.php', 'api/admin/gamification.php',
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
        // Contar apenas linhas que são erros PHP reais
        $phpErrorLines = array_filter($lines, function($l) {
            return preg_match('/PHP\s+(Fatal|Parse|Warning|Notice|Deprecated|Strict|Catchable)/i', $l)
                || preg_match('/\[ADMIN-|\[SDR-|\[AFFILIATE-/i', $l) // logs internos da app
                || preg_match('/Stack trace:|Uncaught |Exception:|\bfailed\b/i', $l);
        });
        $debugLines = count($lines) - count($phpErrorLines);
        collect('Arquivo de log PHP', true,
            $candidate . ' (' . count($lines) . ' linhas total, ' . count($phpErrorLines) . ' erros reais, ' . $debugLines . ' debug/info ignorados)',
            'PHP Error Log');
        foreach (array_slice(array_values($phpErrorLines), -50) as $line) {
            if (preg_match('/^\[([^\]]+)\]\s+(.+)$/', $line, $m)) {
                $isAppLog = preg_match('/\[ADMIN-|\[SDR-|\[AFFILIATE-/i', $m[2]);
                collect($m[2], $isAppLog, $m[1], 'PHP Error Log');
            } else {
                collect(trim($line), false, '', 'PHP Error Log');
            }
        }
        if (empty($phpErrorLines)) {
            collect('Nenhum erro PHP real encontrado', true, 'apenas mensagens de debug/info no log', 'PHP Error Log');
        }
        break;
    }
}

// --- ETAPA 11: MySQL sql_mode ---
if ($pdo) {
    try {
        $sqlMode = $pdo->query("SELECT @@sql_mode")->fetchColumn();
        $hasStrict = strpos($sqlMode, 'ONLY_FULL_GROUP_BY') !== false;
        collect('@@sql_mode', true, $sqlMode, 'MySQL sql_mode');
        // $hasStrict=false é BOM: NOT having ONLY_FULL_GROUP_BY means GROUP BY is relaxed
        collect('ONLY_FULL_GROUP_BY ativo', !$hasStrict,
            $hasStrict ? 'SIM — GROUP BY sem todos os campos vai falhar!' : 'nao (OK — sem restricao)',
            'MySQL sql_mode');
    } catch (Exception $e) {
        collect('@@sql_mode', false, $e->getMessage(), 'MySQL sql_mode');
    }
}

// --- ETAPA 12: Testes diretos de SQL de todos os endpoints GET ---
function ep_test($pdo, $label, $sql, $params = array()) {
    global $allRows;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        collect($label, true, count($rows) . ' linha(s) retornada(s)', 'Endpoint SQL Tests');
    } catch (Exception $e) {
        collect($label, false, 'ERRO: ' . $e->getMessage(), 'Endpoint SQL Tests');
    }
}

if ($pdo) {
    $p = defined('DB_PREFIX') ? DB_PREFIX : '';

    // ── dashboard.php: overview ──────────────────────────────────────────────
    ep_test($pdo, 'dashboard/overview → affiliate_users count',
        "SELECT COUNT(*) as total, SUM(is_active = 1) as active FROM `{$p}affiliate_users`");
    ep_test($pdo, 'dashboard/overview → sdr_users count',
        "SELECT COUNT(*) as total, SUM(is_active = 1) as active FROM `{$p}sdr_users`");
    ep_test($pdo, 'dashboard/overview → affiliate_referrals stats',
        "SELECT COUNT(*) as total_referrals,
            SUM(CASE WHEN status IN ('closed','completed','onboarding') THEN 1 ELSE 0 END) as closed_sales,
            COALESCE(SUM(sale_amount),0) as total_revenue,
            COALESCE(SUM(commission_amount),0) as total_commissions
         FROM `{$p}affiliate_referrals`");
    ep_test($pdo, 'dashboard/overview → orders stats (sdr_id/affiliate_id)',
        "SELECT COUNT(*) as total_orders,
            COALESCE(SUM(CASE WHEN sdr_id IS NOT NULL THEN 1 ELSE 0 END),0) as sdr_sales,
            COALESCE(SUM(CASE WHEN affiliate_id IS NOT NULL THEN 1 ELSE 0 END),0) as affiliate_sales
         FROM `{$p}orders`");

    // ── dashboard.php: charts ────────────────────────────────────────────────
    ep_test($pdo, 'dashboard/charts → receita 12 meses',
        "SELECT DATE_FORMAT(created_at,'%Y-%m') as month, COALESCE(SUM(total_amount),0) as revenue
         FROM `{$p}orders`
         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) AND payment_status = 'paid'
         GROUP BY month ORDER BY month");

    // ── users.php: list_sdrs (query COMPLETA — alvo do erro 500) ────────────
    ep_test($pdo, 'users/list_sdrs → SELECT com GROUP BY (completo)',
        "SELECT
            s.id, s.name, s.email, s.whatsapp, s.is_active, s.created_at, s.last_login,
            COUNT(o.id) as total_sales,
            COALESCE(SUM(o.total_amount), 0) as total_revenue
         FROM `{$p}sdr_users` s
         LEFT JOIN `{$p}orders` o ON o.sdr_id = s.id
         GROUP BY s.id, s.name, s.email, s.whatsapp, s.is_active, s.created_at, s.last_login
         ORDER BY s.created_at DESC");

    // ── users.php: list_affiliates ───────────────────────────────────────────
    ep_test($pdo, 'users/list_affiliates → SELECT basico',
        "SELECT id, first_name, last_name, email, tier, is_active, created_at
         FROM `{$p}affiliate_users`
         ORDER BY created_at DESC LIMIT 5");

    // ── users.php: list_clients ──────────────────────────────────────────────
    ep_test($pdo, 'users/list_clients → SELECT com JOINs',
        "SELECT o.id, o.customer_name, o.customer_email, o.total_amount, o.payment_status,
            s.name AS sdr_name,
            CONCAT(a.first_name,' ',a.last_name) AS affiliate_name
         FROM `{$p}orders` o
         LEFT JOIN `{$p}sdr_users` s ON s.id = o.sdr_id
         LEFT JOIN `{$p}affiliate_users` a ON a.id = o.affiliate_id
         ORDER BY o.created_at DESC LIMIT 5");

    // ── finance.php: pending_commissions ────────────────────────────────────
    ep_test($pdo, 'finance/pending_commissions → comissoes pendentes',
        "SELECT a.id, CONCAT(a.first_name,' ',a.last_name) as name,
            COUNT(r.id) as pending_count, SUM(r.commission_amount) as pending_total
         FROM `{$p}affiliate_referrals` r
         JOIN `{$p}affiliate_users` a ON a.id = r.affiliate_id
         WHERE r.commission_paid = 0 AND r.commission_amount > 0
         GROUP BY a.id");

    // ── finance.php: audit_sales ─────────────────────────────────────────────
    ep_test($pdo, 'finance/audit_sales → referrals auditoria',
        "SELECT r.id, r.status, r.sale_amount, r.commission_amount, r.created_at
         FROM `{$p}affiliate_referrals` r
         ORDER BY r.created_at DESC LIMIT 5");

    // ── finance.php: payment_history ─────────────────────────────────────────
    ep_test($pdo, 'finance/payment_history → historico pagamentos',
        "SELECT r.id, r.commission_amount, r.commission_paid, r.updated_at
         FROM `{$p}affiliate_referrals` r
         WHERE r.commission_paid = 1
         ORDER BY r.updated_at DESC LIMIT 5");

    // ── gamification.php: list_missions ──────────────────────────────────────
    ep_test($pdo, 'gamification/list_missions → missoes',
        "SELECT id, title, description, icon_emoji, category, is_active FROM `{$p}affiliate_missions`
         ORDER BY created_at DESC LIMIT 5");

    // ── gamification.php: list_badges ────────────────────────────────────────
    ep_test($pdo, 'gamification/list_badges → medalhas',
        "SELECT id, title, description, icon_emoji, type, is_active FROM `{$p}affiliate_badges`
         ORDER BY created_at DESC LIMIT 5");
}

// --- ETAPA 13: EXPLAIN da query SDR (detectar causa do erro) ---
if ($pdo) {
    $p = defined('DB_PREFIX') ? DB_PREFIX : '';
    try {
        $explainRows = $pdo->query("
            EXPLAIN SELECT
                s.id, s.name, s.email, s.whatsapp, s.is_active, s.created_at, s.last_login,
                COUNT(o.id) as total_sales,
                COALESCE(SUM(o.total_amount), 0) as total_revenue
            FROM `{$p}sdr_users` s
            LEFT JOIN `{$p}orders` o ON o.sdr_id = s.id
            GROUP BY s.id, s.name, s.email, s.whatsapp, s.is_active, s.created_at, s.last_login
            ORDER BY s.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($explainRows as $row) {
            $detail = 'table=' . ($row['table'] ?? '?')
                    . ' type=' . ($row['type'] ?? '?')
                    . ' key=' . ($row['key'] ?? 'none')
                    . ' rows=' . ($row['rows'] ?? '?')
                    . ' Extra=' . ($row['Extra'] ?? '');
            collect('EXPLAIN sdr JOIN', true, $detail, 'EXPLAIN list_sdrs');
        }
    } catch (Exception $e) {
        collect('EXPLAIN list_sdrs', false, 'ERRO: ' . $e->getMessage(), 'EXPLAIN list_sdrs');
    }
}

// --- ETAPA 14: Simulação COMPLETA do fluxo auth + list_sdrs ---
// Reproduz EXATAMENTE o que users.php faz ao receber ?action=list_sdrs
// (sem depender de JWT externo — cria um token internamente)
if ($pdo) {
    $p = defined('DB_PREFIX') ? DB_PREFIX : '';

    // 14a: JWT_SECRET definido?
    $jwtOk = defined('JWT_SECRET') && JWT_SECRET !== '';
    collect('JWT_SECRET definido', $jwtOk,
        $jwtOk ? 'OK (len=' . strlen(JWT_SECRET) . ')' : 'NAO DEFINIDO — autenticacao vai falhar!',
        'Auth Flow Simulation');

    // 14b: Criar token para o admin #1 e validar de volta
    if ($jwtOk) {
        if (!function_exists('jwt_base64url_encode')) {
            // Carregar funcoes JWT do sdr/middleware.php (inclui db.config.php e lib/database.php tambem)
            $mwFile = __DIR__ . '/api/sdr/middleware.php';
            if (is_readable($mwFile)) {
                // Incluir em escopo isolado para nao colidir com variaveis do test
                require_once $mwFile;
            }
        }

        if (function_exists('jwt_create') && function_exists('jwt_validate')) {
            try {
                $testToken = jwt_create(['admin_id' => 1, 'role' => 'super_admin', 'scope' => 'admin'], 60);
                collect('jwt_create() executou', true, 'token gerado (60s)', 'Auth Flow Simulation');

                $payload = jwt_validate($testToken);
                $isValid = $payload !== false && isset($payload['admin_id']) && $payload['admin_id'] === 1;
                collect('jwt_validate() retornou payload correto', $isValid,
                    $isValid ? 'admin_id=' . $payload['admin_id'] . ' role=' . ($payload['role'] ?? '?') : 'FALHOU — token invalido ou expirado',
                    'Auth Flow Simulation');

                // Token expirado: testar com exp=1 (1 segundo no unix epoch)
                $expiredToken = jwt_create(['admin_id' => 1, 'role' => 'super_admin', 'scope' => 'admin'], -10);
                $expiredResult = jwt_validate($expiredToken);
                collect('jwt_validate() rejeita token expirado', $expiredResult === false,
                    $expiredResult === false ? 'OK — expirado corretamente rejeitado' : 'PROBLEMA: token expirado foi aceito!',
                    'Auth Flow Simulation');

            } catch (Exception $e) {
                collect('jwt_create/validate', false, 'EXCECAO: ' . $e->getMessage(), 'Auth Flow Simulation');
            }
        } else {
            collect('funcoes JWT disponiveis', false,
                'jwt_create/jwt_validate nao encontradas — sdr/middleware.php nao carregado',
                'Auth Flow Simulation');
        }
    }

    // 14c: Query via get_db_connection() (igual ao endpoint real, nao a PDO do test)
    if (function_exists('get_db_connection')) {
        $realPdo = get_db_connection();
        $realPdoOk = $realPdo !== null;
        collect('get_db_connection() retornou PDO', $realPdoOk,
            $realPdoOk ? 'OK — mesmo objeto PDO da aplicacao' : 'RETORNOU NULL — endpoints vao receber $pdo=null e travar!',
            'Auth Flow Simulation');

        if ($realPdoOk) {
            // 14d: handle_list_sdrs interno — simula EXATAMENTE o que o endpoint faz
            try {
                $stmt = $realPdo->query("
                    SELECT
                        s.id, s.name, s.email, s.whatsapp, s.is_active, s.created_at, s.last_login,
                        COUNT(o.id) as total_sales,
                        COALESCE(SUM(o.total_amount), 0) as total_revenue
                    FROM `{$p}sdr_users` s
                    LEFT JOIN `{$p}orders` o ON o.sdr_id = s.id
                    GROUP BY s.id
                    ORDER BY s.created_at DESC
                ");
                $sdrs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $json = json_encode(['ok' => true, 'data' => $sdrs]);
                $jsonOk = $json !== false;
                collect('handle_list_sdrs via get_db_connection() (query OLD sem GROUP BY completo)', $jsonOk,
                    $jsonOk ? count($sdrs) . ' SDR(s) — json_encode OK (' . strlen($json) . ' bytes)' : 'json_encode FALHOU: ' . json_last_error_msg(),
                    'Auth Flow Simulation');
            } catch (Exception $e) {
                collect('handle_list_sdrs via get_db_connection()', false,
                    'EXCECAO: ' . $e->getMessage() . ' — ESTE E O MOTIVO DO 500!',
                    'Auth Flow Simulation');
            }

            // 14e: Verificar se admin #1 pode ser buscado (como require_admin_auth faz)
            try {
                $stmt = $realPdo->prepare("SELECT id, name, email, role, is_active FROM `{$p}admin_users` WHERE id = ?");
                $stmt->execute([1]);
                $adminRow = $stmt->fetch(PDO::FETCH_ASSOC);
                collect('require_admin_auth → busca admin #1', $adminRow !== false,
                    $adminRow ? 'OK: ' . $adminRow['email'] . ' role=' . $adminRow['role'] . ' ativo=' . $adminRow['is_active'] : 'NAO ENCONTRADO',
                    'Auth Flow Simulation');
            } catch (Exception $e) {
                collect('require_admin_auth → busca admin_users', false, 'EXCECAO: ' . $e->getMessage(), 'Auth Flow Simulation');
            }
        }
    } else {
        collect('get_db_connection() disponivel', false,
            'funcao nao existe — sdr/middleware.php ou lib/database.php nao carregado',
            'Auth Flow Simulation');
    }

    // 14f: Verificar se users.php tem o fix Throwable deployado
    $usersPhpPath = __DIR__ . '/api/admin/panel-mgmt.php';
    $usersContent = file_exists($usersPhpPath) ? file_get_contents($usersPhpPath) : '';
    $fixDeployed = strpos($usersContent, 'Throwable') !== false;
    $hasTryCatch = strpos($usersContent, 'handle_list_sdrs') !== false && strpos($usersContent, 'try {') !== false;
    collect('users.php tem fix \\Throwable deployado', $fixDeployed,
        $fixDeployed
            ? 'OK — catch(\\Throwable) presente (' . number_format(strlen($usersContent)) . ' bytes)'
            : ($hasTryCatch
                ? 'PARCIAL — tem try-catch mas usa \\PDOException (nao captura \\Error de $pdo=null)'
                : 'FIX NAO DEPLOYADO! Arquivo antigo sem try-catch (500 vazio em caso de erro)'),
        'Auth Flow Simulation');

    // --- ETAPA 15: Requisição HTTP REAL ao endpoint ---
    // Faz GET real para users.php?action=list_sdrs com Bearer token gerado acima.
    // Mostra EXATAMENTE o que o servidor retorna — inclusive página de erro do Hostinger.
    $endpointUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'unli.com.br') . '/api/admin/panel-mgmt.php?action=list_sdrs';
    $httpToken   = isset($testToken) ? $testToken : null;

    if ($httpToken && function_exists('curl_init')) {
        $ch = curl_init($endpointUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $httpToken,
                'Accept: application/json',
                'Origin: https://unli.com.br',
            ],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HEADER         => true,
        ]);
        $rawResp  = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hdrSize  = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            collect('HTTP real → users.php?action=list_sdrs', false,
                'cURL erro: ' . $curlErr, 'Endpoint Real HTTP');
        } else {
            $respHeaders = substr($rawResp, 0, $hdrSize);
            $body        = substr($rawResp, $hdrSize);
            $bodyShort   = trim(substr($body, 0, 600));
            $isJson      = strlen($bodyShort) > 0 && ($bodyShort[0] === '{' || $bodyShort[0] === '[');

            $respCt = '';
            if (preg_match('/Content-Type:\s*([^\r\n]+)/i', $respHeaders, $m)) {
                $respCt = trim($m[1]);
            }

            collect('HTTP Status → users.php?action=list_sdrs', $httpCode === 200,
                'HTTP ' . $httpCode . ' | Content-Type: ' . ($respCt ?: '(nao informado)'),
                'Endpoint Real HTTP');

            collect('Corpo da resposta (600 chars)', $isJson,
                $isJson ? $bodyShort : 'NAO-JSON: ' . $bodyShort,
                'Endpoint Real HTTP');

            $decoded = json_decode($body, true);
            if ($decoded !== null) {
                $decOk = !empty($decoded['ok']);
                collect('Decoded JSON → ok', $decOk,
                    $decOk
                        ? 'OK — ' . count($decoded['data'] ?? []) . ' SDR(s)'
                        : 'ERRO retornado pelo endpoint: ' . ($decoded['error'] ?? json_encode($decoded)),
                    'Endpoint Real HTTP');
            }
        }
    } elseif ($httpToken) {
        // Fallback sem cURL — usa file_get_contents
        $context = stream_context_create(['http' => [
            'method'        => 'GET',
            'header'        => "Authorization: Bearer {$httpToken}\r\nAccept: application/json\r\nOrigin: https://unli.com.br",
            'timeout'       => 15,
            'ignore_errors' => true,
        ]]);
        $body       = @file_get_contents($endpointUrl, false, $context);
        $statusLine = $http_response_header[0] ?? 'Status desconhecido';
        collect('HTTP real (stream) → users.php?action=list_sdrs',
            $body !== false && strpos($body, '"ok":true') !== false,
            $body !== false ? $statusLine . ' | ' . substr($body, 0, 400) : 'FALHOU — sem resposta',
            'Endpoint Real HTTP');
    } else {
        collect('HTTP real → users.php?action=list_sdrs', false,
            'Pulado — JWT nao disponivel (falha nas etapas anteriores)',
            'Endpoint Real HTTP');
    }

    // --- ETAPA 16: Error log APÓS requisição HTTP ---
    // Lê as últimas 30 linhas do error log e filtra entradas dos últimos 60 segundos
    // Isso captura o erro EXATO que causou o 500 no endpoint acima.
    $errorLogPaths = [
        '/home/u969754391/.logs/error_log_unli_com_br',
        ini_get('error_log'),
        __DIR__ . '/api/logs/php_errors.log',
    ];
    $errorLogFile = null;
    foreach ($errorLogPaths as $p) {
        if ($p && file_exists($p) && is_readable($p)) {
            $errorLogFile = $p;
            break;
        }
    }

    if ($errorLogFile) {
        // Lê as últimas 80 linhas para ter margem suficiente
        $lines = [];
        $fp = fopen($errorLogFile, 'r');
        $buffer = '';
        fseek($fp, 0, SEEK_END);
        $pos = ftell($fp);
        $chunk = 8192;
        while ($pos > 0 && count($lines) < 80) {
            $read = min($chunk, $pos);
            $pos -= $read;
            fseek($fp, $pos);
            $buffer = fread($fp, $read) . $buffer;
            $lines = explode("\n", trim($buffer));
        }
        fclose($fp);
        $lines = array_filter(array_slice($lines, -80));

        // Filtrar apenas linhas dos últimos 60 segundos que contenham usuarios.php ou admin
        $now = time();
        $recentErrors = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line) continue;
            // Manter apenas PHP Fatal, Parse errors, e entradas relacionadas a admin/users
            if (preg_match('/PHP (Fatal|Parse|Error|Warning.*users|Warning.*admin)/i', $line) ||
                strpos($line, 'users.php') !== false ||
                strpos($line, 'admin/') !== false ||
                strpos($line, '[ADMIN-USERS]') !== false) {
                $recentErrors[] = $line;
            }
        }

        if (count($recentErrors) > 0) {
            // Mostrar as últimas 5 entradas relevantes
            $show = array_slice($recentErrors, -5);
            foreach ($show as $i => $errLine) {
                $short = substr($errLine, 0, 350);
                collect('Error log recente #' . ($i + 1), false,
                    $short, 'Error Log Pos-Request');
            }
        } else {
            collect('Error log pos-request', true,
                'Nenhum erro relacionado a admin/users nas ultimas entradas do log',
                'Error Log Pos-Request');
        }

        // Mostrar as ultimas 5 linhas brutas independente de filtro (para depuracao)
        $lastRaw = array_slice(array_values(array_filter($lines)), -5);
        foreach ($lastRaw as $i => $rawLine) {
            collect('Ultima linha log #' . ($i + 1), true,
                substr(trim($rawLine), 0, 300), 'Error Log Pos-Request (Raw)');
        }
    } else {
        collect('Error log pos-request', false,
            'Arquivo de log nao encontrado ou nao legivel', 'Error Log Pos-Request');
    }
}

// --- ETAPA 17: Inspeção cirúrgica do users.php no servidor ---
// Verifica se o arquivo está corrompido/truncado detectando funções esperadas
$usersPath = __DIR__ . '/api/admin/panel-mgmt.php';
if (file_exists($usersPath)) {
    $uc = file_get_contents($usersPath);
    $ucLen = strlen($uc);

    collect('users.php no servidor — tamanho', true,
        $ucLen . ' bytes | MD5: ' . md5($uc), 'Inspecao users.php');

    // Checar se o arquivo termina corretamente (não truncado)
    $lastBytes = substr($uc, -100);
    $ends_ok = strpos($lastBytes, '}') !== false;
    collect('users.php termina com "}" (nao truncado)', $ends_ok,
        $ends_ok ? 'OK — ultimos 100 bytes: ' . trim(preg_replace('/\s+/', ' ', $lastBytes))
                 : 'TRUNCADO! Ultimos bytes: ' . bin2hex(substr($uc, -20)),
        'Inspecao users.php');

    // Checar funções esperadas
    $expectedFunctions = [
        'handle_list_affiliates',
        'handle_get_affiliate',
        'handle_update_affiliate',
        'handle_toggle_affiliate',
        'handle_list_sdrs',
        'handle_create_sdr',
        'handle_update_sdr',
        'handle_toggle_sdr',
        'handle_list_clients',
    ];
    foreach ($expectedFunctions as $fn) {
        $found = strpos($uc, 'function ' . $fn) !== false;
        collect('function ' . $fn . '()', $found,
            $found ? 'presente' : 'AUSENTE — funcao nao encontrada no arquivo!',
            'Inspecao users.php');
    }

    // Checar catch type
    $hasThrowable  = strpos($uc, 'catch (\Throwable') !== false;
    $hasPDOEx      = strpos($uc, 'catch (\PDOException') !== false;
    collect('catch type em handle_list_sdrs', $hasThrowable,
        $hasThrowable  ? 'catch(\\Throwable) — versao correta' :
        ($hasPDOEx     ? 'catch(\\PDOException) — versao antiga (nao captura \\Error)' :
                         'SEM try-catch!'),
        'Inspecao users.php');

    // Tentar syntax-check via token_get_all (detecta erros de parse sem executar)
    $tokens = @token_get_all($uc);
    $parseErr = error_get_last();
    $hasSyntaxError = $parseErr && strpos($parseErr['message'], 'parse') !== false;
    collect('PHP syntax check (token_get_all)', !$hasSyntaxError,
        $hasSyntaxError ? 'ERRO DE SINTAXE: ' . $parseErr['message'] : 'OK — ' . count($tokens) . ' tokens',
        'Inspecao users.php');
} else {
    collect('users.php existe no servidor', false, 'ARQUIVO NAO ENCONTRADO!', 'Inspecao users.php');
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
  Tabelas affiliate/sdr faltando &rarr; <code>migrate-affiliates.php?password=AffMigrate2026</code> e <code>migrate-sdr.php?password=SdrMigrate2026</code><br>
  sdr_users inexistente &rarr; execute <code>migrate-sdr.php?password=SdrMigrate2026</code> para criar a tabela e adicionar colunas na orders<br>
  ONLY_FULL_GROUP_BY ativo &rarr; a query SDR j&aacute; foi corrigida com GROUP BY completo; se ainda falhar, verifique a vers&atilde;o do MySQL
</div>
<?php endif; ?>

<!-- =========================================================
     TESTES JS — FETCH REAL (visível no Network do browser)
     Os tokens são gerados no PHP acima e injetados aqui.
     ========================================================= -->
<?php
// Passar o token gerado no PHP para o JS (ou vazio se falhou)
$jsToken = isset($testToken) ? $testToken : '';
$jsPrefix = defined('DB_PREFIX') ? DB_PREFIX : 'unli_';
?>
<div id="js-tests" style="margin-top:1.6rem">
  <div class="stitle" style="border-radius:6px 6px 0 0;border-bottom:none">&#128279; FETCH JS — Endpoints reais (visível no Network)</div>
  <div class="rows" id="js-results">
    <div class="row"><i class="ic ok">&#8987;</i><span class="lbl">Aguardando fetch()...</span><span class="det">Abrindo Network tab agora vai mostrar as chamadas</span></div>
  </div>
  <div style="margin-top:.5rem;font-size:.72rem;color:#64748b;padding:0 4px">
    Abra DevTools → Network tab → filtre por <code style="background:#1e1e2e;padding:1px 5px;border-radius:3px">users.php</code> para ver a resposta completa
  </div>
</div>

<div class="danger" style="margin-top:1.2rem">&#9888; <strong>APAGUE ESTE ARQUIVO APOS OS TESTES!</strong></div>
</div>

<script>
(function() {
  var TOKEN = <?= json_encode($jsToken) ?>;
  var results = document.getElementById('js-results');
  results.innerHTML = '';

  var endpoints = [
    { label: 'users-diag.php (sem auth)',              url: '/api/admin/users-diag.php' },
    { label: 'panel-mgmt.php?action=list_sdrs',             url: '/api/admin/panel-mgmt.php?action=list_sdrs' },
    { label: 'panel-mgmt.php?action=list_affiliates',       url: '/api/admin/panel-mgmt.php?action=list_affiliates' },
    { label: 'dashboard.php?action=overview',          url: '/api/admin/dashboard.php?action=overview' },
    { label: 'gamification.php?action=list_missions',  url: '/api/admin/gamification.php?action=list_missions' },
    { label: 'finance.php?action=pending_commissions', url: '/api/admin/finance.php?action=pending_commissions' },
  ];

  function addRow(label, ok, detail, raw) {
    var row = document.createElement('div');
    row.className = 'row';
    var rawHtml = raw ? '<br><span style="font-size:.68rem;color:#94a3b8;font-family:monospace;word-break:break-all">' + escHtml(raw.substring(0, 500)) + '</span>' : '';
    row.innerHTML =
      '<i class="ic ' + (ok ? 'ok' : 'fail') + '">' + (ok ? '✓' : '✗') + '</i>' +
      '<span class="lbl">' + escHtml(label) + '</span>' +
      '<span class="det' + (ok ? '' : ' err') + '">' + escHtml(detail) + rawHtml + '</span>';
    results.appendChild(row);
  }

  function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  if (!TOKEN) {
    addRow('Token JWT', false, 'Token nao disponivel — ETAPA 14 falhou (ver secao acima)', '');
    return;
  }

  // Informa que o token está pronto
  addRow('Token JWT', true, 'Token gerado pelo PHP — fazendo fetch() dos endpoints abaixo...', '');

  endpoints.forEach(function(ep) {
    fetch(ep.url, {
      headers: {
        'Authorization': 'Bearer ' + TOKEN,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    })
    .then(function(res) {
      var status = res.status;
      var ct = res.headers.get('Content-Type') || '(sem Content-Type)';
      return res.text().then(function(body) {
        var ok = status === 200;
        var detail = 'HTTP ' + status + ' | ' + ct;
        var parsed = null;
        try { parsed = JSON.parse(body); } catch(e) {}

        if (parsed) {
          if (parsed.ok === false) {
            detail += ' | ERRO: ' + (parsed.error || JSON.stringify(parsed));
            addRow(ep.label, false, detail, null);
          } else {
            var count = parsed.data ? parsed.data.length : '?';
            addRow(ep.label, true, detail + ' | ok:true | ' + count + ' item(s)', null);
          }
        } else {
          // Resposta não é JSON — mostra o body bruto (primeiros 500 chars)
          addRow(ep.label, false, detail + ' | NAO-JSON — ver body abaixo:', body);
        }
      });
    })
    .catch(function(err) {
      addRow(ep.label, false, 'fetch() falhou: ' + err.message, '');
    });
  });
})();
</script>
</body>
</html>
<?php
echo ob_get_clean();
