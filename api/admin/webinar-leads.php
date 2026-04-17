<?php
/**
 * Webinar Leads - API pública (inscrição) + Admin (listagem e exportação)
 *
 * Endpoints públicos:
 *   POST ?action=register  → Cadastrar novo lead
 *
 * Endpoints admin (requerem JWT admin):
 *   GET  ?action=list      → Listar leads com paginação e filtro
 *   GET  ?action=export    → Exportar todos os leads (CSV)
 *   GET  ?action=stats     → Totais rápidos por ramo/objetivo/país
 */

ob_start();
ini_set('display_errors', '1');
error_reporting(E_ALL);

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level()) ob_end_clean();
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'    => false,
            'error' => $err['message'] . ' in ' . $err['file'] . ':' . $err['line']
        ]);
    }
});

if (!defined('SECURE_CONFIG_ACCESS')) {
    define('SECURE_CONFIG_ACCESS', true);
}
$secureConfig = __DIR__ . '/../config.secure.php';
if (file_exists($secureConfig)) {
    require_once $secureConfig;
}

require_once __DIR__ . '/../lib/cors.php';
if (!defined('DB_CONFIG_ACCESS')) define('DB_CONFIG_ACCESS', true);
require_once __DIR__ . '/../db.config.php';
require_once __DIR__ . '/../lib/database.php';

$action = $_GET['action'] ?? '';

// ── Rotas públicas ──────────────────────────────────────────────────────────
if ($action === 'register') {
    header('Content-Type: application/json; charset=utf-8');
    handle_register();
    exit;
}

if ($action === 'save_partial') {
    header('Content-Type: application/json; charset=utf-8');
    handle_save_partial();
    exit;
}

// ── Rotas admin ─────────────────────────────────────────────────────────────
require_once __DIR__ . '/middleware.php';
$admin = require_admin_auth();
$pdo    = get_db_connection();
$prefix = defined('DB_PREFIX') ? DB_PREFIX : '';

switch ($action) {
    case 'list':   handle_list($pdo, $prefix);   break;
    case 'export': handle_export($pdo, $prefix); break;
    case 'stats':  handle_stats($pdo, $prefix);  break;
    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Ação inválida']);
}

// ════════════════════════════════════════════════════════════════════════════
// HANDLERS
// ════════════════════════════════════════════════════════════════════════════

function handle_register() {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];

    // Validação básica
    $nome     = trim($body['nome']     ?? '');
    $email    = trim($body['email']    ?? '');
    $ramo     = trim($body['ramo']     ?? '');
    $objetivo = trim($body['objetivo'] ?? '');

    if (!$nome || !$email || !$ramo || !$objetivo) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'Campos obrigatórios ausentes']);
        return;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'E-mail inválido']);
        return;
    }

    $contato    = trim($body['contato']   ?? '');
    $cidade     = trim($body['cidade']    ?? '');
    $estado     = trim($body['estado']    ?? '');
    $pais       = trim($body['pais']      ?? 'Brasil');
    $mensagem   = trim($body['mensagem']  ?? '');
    $refCode    = trim($body['ref_code']  ?? '');
    // Sanitiza: apenas alfanumérico, hífen e underscore
    if ($refCode && !preg_match('/^[a-zA-Z0-9_-]{4,64}$/', $refCode)) {
        $refCode = '';
    }

    // IP seguro (suporte a proxies)
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['HTTP_CF_CONNECTING_IP']
        ?? $_SERVER['REMOTE_ADDR']
        ?? null;
    if ($ip) {
        $ip = explode(',', $ip)[0];
        $ip = trim($ip);
    }

    $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);

    if (!defined('DB_CONFIG_ACCESS')) define('DB_CONFIG_ACCESS', true);
    $pdo    = get_db_connection();
    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';

    // Verifica se já completou antes (para não reenviar e-mail)
    $check = $pdo->prepare("SELECT completed FROM {$prefix}webinar_leads WHERE email = :email");
    $check->execute([':email' => $email]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);

    // Se já estava completo, retorna sucesso sem fazer nada
    if ($existing && $existing['completed']) {
        echo json_encode(['ok' => true, 'message' => 'Inscrição realizada com sucesso!']);
        return;
    }

    // Upsert: insere lead novo ou atualiza lead parcial existente
    $stmt = $pdo->prepare("
        INSERT INTO {$prefix}webinar_leads
            (nome, email, contato, ramo, objetivo, cidade, estado, pais, mensagem, ip, user_agent, ref_code, completed)
        VALUES
            (:nome, :email, :contato, :ramo, :objetivo, :cidade, :estado, :pais, :mensagem, :ip, :ua, :ref_code, 1)
        ON DUPLICATE KEY UPDATE
            nome      = VALUES(nome),
            contato   = VALUES(contato),
            ramo      = VALUES(ramo),
            objetivo  = VALUES(objetivo),
            cidade    = VALUES(cidade),
            estado    = VALUES(estado),
            pais      = VALUES(pais),
            mensagem  = VALUES(mensagem),
            completed = 1
    ");
    $stmt->execute([
        ':nome'     => $nome,
        ':email'    => $email,
        ':contato'  => $contato ?: null,
        ':ramo'     => $ramo,
        ':objetivo' => $objetivo,
        ':cidade'   => $cidade ?: null,
        ':estado'   => $estado ?: null,
        ':pais'     => $pais,
        ':mensagem' => $mensagem ?: null,
        ':ip'       => $ip,
        ':ua'       => $userAgent ?: null,
        ':ref_code' => $refCode ?: null,
    ]);

    // Envia e-mail de confirmação (lead novo ou que estava incompleto)
    send_webinar_confirmation($email, $nome);

    echo json_encode(['ok' => true, 'message' => 'Inscrição realizada com sucesso!']);
}

// ── Salvar lead parcial (fire-and-forget pelo frontend a cada etapa) ─────────
function handle_save_partial() {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];

    $nome  = trim($body['nome']  ?? '');
    $email = trim($body['email'] ?? '');

    if (!$nome || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'Nome e e-mail são obrigatórios']);
        return;
    }

    $contato = trim($body['contato'] ?? '');
    $ramo    = trim($body['ramo']    ?? '');
    $cidade  = trim($body['cidade']  ?? '');
    $estado  = trim($body['estado']  ?? '');
    $pais    = trim($body['pais']    ?? 'Brasil');
    $refCode = trim($body['ref_code'] ?? '');
    if ($refCode && !preg_match('/^[a-zA-Z0-9_-]{4,64}$/', $refCode)) {
        $refCode = '';
    }

    $ip = $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['HTTP_CF_CONNECTING_IP']
        ?? $_SERVER['REMOTE_ADDR']
        ?? null;
    if ($ip) {
        $ip = trim(explode(',', $ip)[0]);
    }
    $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);

    if (!defined('DB_CONFIG_ACCESS')) define('DB_CONFIG_ACCESS', true);
    $pdo    = get_db_connection();
    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';

    // Upsert parcial: não sobrescreve lead já completo
    $stmt = $pdo->prepare("
        INSERT INTO {$prefix}webinar_leads
            (nome, email, contato, ramo, cidade, estado, pais, ip, user_agent, ref_code, completed)
        VALUES
            (:nome, :email, :contato, :ramo, :cidade, :estado, :pais, :ip, :ua, :ref_code, 0)
        ON DUPLICATE KEY UPDATE
            nome    = IF(completed = 0, VALUES(nome),    nome),
            contato = IF(completed = 0, VALUES(contato), contato),
            ramo    = IF(completed = 0 AND VALUES(ramo)   != '', VALUES(ramo),   ramo),
            cidade  = IF(completed = 0 AND VALUES(cidade) != '', VALUES(cidade), cidade),
            estado  = IF(completed = 0 AND VALUES(estado) != '', VALUES(estado), estado),
            pais    = IF(completed = 0, VALUES(pais), pais)
    ");
    $stmt->execute([
        ':nome'     => $nome,
        ':email'    => $email,
        ':contato'  => $contato ?: null,
        ':ramo'     => $ramo ?: null,
        ':cidade'   => $cidade ?: null,
        ':estado'   => $estado ?: null,
        ':pais'     => $pais,
        ':ip'       => $ip,
        ':ua'       => $userAgent ?: null,
        ':ref_code' => $refCode ?: null,
    ]);

    echo json_encode(['ok' => true]);
}

// ── Admin: listagem paginada ─────────────────────────────────────────────────
function handle_list($pdo, $prefix) {
    header('Content-Type: application/json; charset=utf-8');

    $page    = max(1, (int)($_GET['page']    ?? 1));
    $perPage = min(100, max(10, (int)($_GET['per_page'] ?? 25)));
    $search  = trim($_GET['search'] ?? '');
    $ramo    = trim($_GET['ramo']   ?? '');
    $offset  = ($page - 1) * $perPage;

    $where  = [];
    $params = [];

    if ($search !== '') {
        $where[]         = '(nome LIKE :s OR email LIKE :s2)';
        $params[':s']    = "%{$search}%";
        $params[':s2']   = "%{$search}%";
    }
    if ($ramo !== '') {
        $where[]        = 'ramo = :ramo';
        $params[':ramo'] = $ramo;
    }

    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $total = $pdo->prepare("SELECT COUNT(*) FROM {$prefix}webinar_leads {$whereSQL}");
    $total->execute($params);
    $totalRows = (int)$total->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT id, nome, email, contato, ramo, objetivo, cidade, estado, pais, ref_code, created_at
        FROM {$prefix}webinar_leads
        {$whereSQL}
        ORDER BY created_at DESC
        LIMIT {$perPage} OFFSET {$offset}
    ");
    $stmt->execute($params);
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'ok'    => true,
        'leads' => $leads,
        'pagination' => [
            'page'     => $page,
            'per_page' => $perPage,
            'total'    => $totalRows,
            'pages'    => (int)ceil($totalRows / $perPage),
        ],
    ]);
}

// ── Admin: exportação CSV ────────────────────────────────────────────────────
function handle_export($pdo, $prefix) {
    $search = trim($_GET['search'] ?? '');
    $ramo   = trim($_GET['ramo']   ?? '');

    $where  = [];
    $params = [];

    if ($search !== '') {
        $where[]       = '(nome LIKE :s OR email LIKE :s2)';
        $params[':s']  = "%{$search}%";
        $params[':s2'] = "%{$search}%";
    }
    if ($ramo !== '') {
        $where[]        = 'ramo = :ramo';
        $params[':ramo'] = $ramo;
    }

    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $stmt = $pdo->prepare("
        SELECT id, nome, email, contato, ramo, objetivo, cidade, estado, pais, mensagem, ip, ref_code, created_at
        FROM {$prefix}webinar_leads
        {$whereSQL}
        ORDER BY created_at ASC
    ");
    $stmt->execute($params);
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    while (ob_get_level()) ob_end_clean();

    $filename = 'webinar-leads-' . date('Y-m-d') . '.csv';

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // BOM para Excel reconhecer UTF-8
    echo "\xEF\xBB\xBF";

    $out = fopen('php://output', 'w');

    fputcsv($out, ['ID', 'Nome', 'E-mail', 'WhatsApp', 'Ramo', 'Objetivo', 'Cidade', 'Estado', 'País', 'Mensagem', 'IP', 'Afiliado (ref)', 'Data de inscrição'], ';');

    foreach ($leads as $row) {
        fputcsv($out, [
            $row['id'],
            $row['nome'],
            $row['email'],
            $row['contato'] ?? '',
            $row['ramo'],
            $row['objetivo'],
            $row['cidade'] ?? '',
            $row['estado'] ?? '',
            $row['pais'],
            $row['mensagem'] ?? '',
            $row['ip'] ?? '',
            $row['ref_code'] ?? '',
            $row['created_at'],
        ], ';');
    }

    fclose($out);
    exit;
}

// ── Admin: estatísticas rápidas ──────────────────────────────────────────────
function handle_stats($pdo, $prefix) {
    header('Content-Type: application/json; charset=utf-8');

    $total = (int)$pdo->query("SELECT COUNT(*) FROM {$prefix}webinar_leads")->fetchColumn();

    $today = (int)$pdo->query("
        SELECT COUNT(*) FROM {$prefix}webinar_leads
        WHERE DATE(created_at) = CURDATE()
    ")->fetchColumn();

    $week = (int)$pdo->query("
        SELECT COUNT(*) FROM {$prefix}webinar_leads
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ")->fetchColumn();

    $byRamo = $pdo->query("
        SELECT ramo, COUNT(*) as total
        FROM {$prefix}webinar_leads
        GROUP BY ramo ORDER BY total DESC LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

    $byObjetivo = $pdo->query("
        SELECT objetivo, COUNT(*) as total
        FROM {$prefix}webinar_leads
        GROUP BY objetivo ORDER BY total DESC LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'ok'          => true,
        'total'       => $total,
        'today'       => $today,
        'week'        => $week,
        'by_ramo'     => $byRamo,
        'by_objetivo' => $byObjetivo,
    ]);
}

// ── E-mail de confirmação ────────────────────────────────────────────────────
function send_webinar_confirmation(string $email, string $nome): void {
    $templatePath = __DIR__ . '/../emails/webinar-confirmacao.html';

    if (!file_exists($templatePath)) {
        error_log('[WEBINAR] Template de e-mail não encontrado: ' . $templatePath);
        return;
    }

    $body = file_get_contents($templatePath);
    $body = str_replace(['{{NOME}}', '{{EMAIL}}'], [htmlspecialchars($nome), htmlspecialchars($email)], $body);

    $subject = 'Sua inscrição está confirmada — Webinar IA e Automação, 25 de Abril';

    $fromName  = 'Foorge por Unli Studio';
    $fromEmail = defined('MAIL_FROM_EMAIL') ? MAIL_FROM_EMAIL : 'noreply@unli.com.br';

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
    $headers .= "Reply-To: contato@unli.com.br\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    $sent = mail($email, $subject, $body, $headers);

    if (!$sent) {
        error_log('[WEBINAR] Falha ao enviar e-mail de confirmação para: ' . $email);
    }
}
