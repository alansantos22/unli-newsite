<?php
/**
 * ============================================
 * WEBHOOK PROPOSTAS - Consulta de Pedidos/Sites
 * ============================================
 * 
 * Endpoint para um agente externo consultar propostas.
 * O agente bate nesse endpoint, verifica se há sites 
 * novos vendidos (pagos) e, se houver, inicia o 
 * desenvolvimento automático.
 * 
 * Endpoints disponíveis (via query string ?action=):
 * 
 *   GET ?action=all              → Todas as propostas
 *   GET ?action=paid             → Somente propostas pagas
 *   GET ?action=unpaid           → Somente propostas não pagas (pending/failed)
 *   GET ?action=new_sites        → Sites pagos ainda não desenvolvidos (paid + onboarding pendente/preenchendo)
 *   GET ?action=summary          → Resumo com contadores
 * 
 * Autenticação: Header "X-Webhook-Token" obrigatório
 * 
 * @version 1.0.0
 */

// ============================================
// CONFIGURAÇÃO
// ============================================

define('SECURE_CONFIG_ACCESS', true);
define('DB_CONFIG_ACCESS', true);

$configFile = __DIR__ . '/config.secure.php';

if (!file_exists($configFile)) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'error' => 'Erro de configuração do servidor.'
    ], JSON_UNESCAPED_UNICODE));
}

require_once $configFile;
require_once __DIR__ . '/lib/cors.php';
require_once __DIR__ . '/lib/database.php';

header('Content-Type: application/json; charset=utf-8');

// ============================================
// TOKEN DE AUTENTICAÇÃO DO WEBHOOK
// ============================================
// Defina este token no config.secure.php ou aqui.
// O agente externo deve enviar no header X-Webhook-Token.

if (!defined('WEBHOOK_PROPOSALS_TOKEN')) {
    define('WEBHOOK_PROPOSALS_TOKEN', hash('sha256', (defined('ENCRYPTION_KEY') ? ENCRYPTION_KEY : 'fallback') . '_proposals_webhook'));
}

// ============================================
// LOG
// ============================================
function proposalLog($message, $data = null) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [PROPOSALS-WEBHOOK] $message";

    if ($data !== null) {
        $logMessage .= " | " . json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    error_log($logMessage);

    $logFile = __DIR__ . '/logs/proposals-webhook.log';
    $logDir = dirname($logFile);

    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    @file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
}

// ============================================
// VALIDAR MÉTODO (apenas GET)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método não permitido. Use GET.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================
// AUTENTICAÇÃO VIA TOKEN
// ============================================
$receivedToken = $_SERVER['HTTP_X_WEBHOOK_TOKEN'] ?? '';

if (empty($receivedToken) || !hash_equals(WEBHOOK_PROPOSALS_TOKEN, $receivedToken)) {
    proposalLog('Tentativa de acesso não autorizado', [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'token_recebido' => !empty($receivedToken) ? substr($receivedToken, 0, 10) . '...' : '(vazio)'
    ]);

    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Token de autenticação inválido ou ausente. Envie o header X-Webhook-Token.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================
// FUNÇÕES DE CONSULTA
// ============================================

/**
 * Busca todas as propostas/pedidos
 */
function get_all_proposals($limit = 50, $offset = 0) {
    return list_orders_from_db([], $limit, $offset);
}

/**
 * Busca propostas pagas
 */
function get_paid_proposals($limit = 50, $offset = 0) {
    return list_orders_from_db(['payment_status' => 'paid'], $limit, $offset);
}

/**
 * Busca propostas NÃO pagas (pending + failed)
 */
function get_unpaid_proposals($limit = 50, $offset = 0) {
    try {
        $pdo = get_db_connection();

        if ($pdo === null) {
            return [];
        }

        $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
        $sql = "SELECT * FROM {$prefix}orders 
                WHERE payment_status IN ('pending', 'failed') 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();

    } catch (PDOException $e) {
        error_log("Error fetching unpaid proposals: " . $e->getMessage());
        return [];
    }
}

/**
 * Busca sites novos vendidos que precisam ser desenvolvidos
 * (pagos + onboarding ainda não concluído)
 */
function get_new_sites_to_build($limit = 50, $offset = 0) {
    try {
        $pdo = get_db_connection();

        if ($pdo === null) {
            return [];
        }

        $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
        $sql = "SELECT * FROM {$prefix}orders 
                WHERE payment_status = 'paid' 
                  AND (onboarding_status IS NULL 
                       OR onboarding_status IN ('pendente', 'preenchendo'))
                ORDER BY created_at ASC 
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();

    } catch (PDOException $e) {
        error_log("Error fetching new sites to build: " . $e->getMessage());
        return [];
    }
}

/**
 * Retorna um resumo com contadores de cada status
 */
function get_proposals_summary() {
    try {
        $pdo = get_db_connection();

        if ($pdo === null) {
            return null;
        }

        $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';

        // Total de propostas
        $stmtTotal = $pdo->query("SELECT COUNT(*) as total FROM {$prefix}orders");
        $total = (int) $stmtTotal->fetch()['total'];

        // Por status de pagamento
        $stmtByPayment = $pdo->query("
            SELECT payment_status, COUNT(*) as count 
            FROM {$prefix}orders 
            GROUP BY payment_status
        ");
        $byPayment = [];
        foreach ($stmtByPayment->fetchAll() as $row) {
            $byPayment[$row['payment_status']] = (int) $row['count'];
        }

        // Sites pagos aguardando desenvolvimento
        $stmtNewSites = $pdo->query("
            SELECT COUNT(*) as count FROM {$prefix}orders 
            WHERE payment_status = 'paid' 
              AND (onboarding_status IS NULL 
                   OR onboarding_status IN ('pendente', 'preenchendo'))
        ");
        $newSites = (int) $stmtNewSites->fetch()['count'];

        // Último pedido
        $stmtLast = $pdo->query("
            SELECT created_at FROM {$prefix}orders 
            ORDER BY created_at DESC LIMIT 1
        ");
        $lastOrder = $stmtLast->fetch();

        return [
            'total_proposals' => $total,
            'by_payment_status' => [
                'paid' => $byPayment['paid'] ?? 0,
                'pending' => $byPayment['pending'] ?? 0,
                'failed' => $byPayment['failed'] ?? 0,
                'refunded' => $byPayment['refunded'] ?? 0,
            ],
            'new_sites_to_build' => $newSites,
            'last_order_at' => $lastOrder ? $lastOrder['created_at'] : null,
        ];

    } catch (PDOException $e) {
        error_log("Error fetching proposals summary: " . $e->getMessage());
        return null;
    }
}

/**
 * Formata uma proposta para resposta da API (remove campos sensíveis)
 */
function format_proposal($order) {
    $details = json_decode($order['order_details'] ?? '{}', true);

    return [
        'id' => $order['id'] ?? null,
        'order_id' => $details['order_id'] ?? null,
        'customer_name' => $order['customer_name'] ?? null,
        'email' => $order['email'] ?? null,
        'phone' => $order['phone'] ?? null,
        'product' => $details['product'] ?? null,
        'pages' => $details['pages'] ?? null,
        'content' => $details['content'] ?? null,
        'pricing' => $details['pricing'] ?? null,
        'selection' => $details['selection'] ?? null,
        'total_amount' => $order['total_amount'] ?? null,
        'payment_status' => $order['payment_status'] ?? null,
        'payment_method' => $order['payment_method'] ?? null,
        'onboarding_status' => $order['onboarding_status'] ?? null,
        'briefing_data' => json_decode($order['briefing_data'] ?? 'null', true),
        'created_at' => $order['created_at'] ?? null,
        'updated_at' => $order['updated_at'] ?? null,
    ];
}

// ============================================
// ROTEAMENTO POR ACTION
// ============================================

$action = $_GET['action'] ?? 'summary';
$limit  = min((int) ($_GET['limit'] ?? 50), 100); // Max 100 por consulta
$offset = max((int) ($_GET['offset'] ?? 0), 0);

proposalLog("Consulta recebida", [
    'action' => $action,
    'limit' => $limit,
    'offset' => $offset,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
]);

switch ($action) {

    // ─── Todas as propostas ───
    case 'all':
        $proposals = get_all_proposals($limit, $offset);
        $formatted = array_map('format_proposal', $proposals);

        echo json_encode([
            'success' => true,
            'action' => 'all',
            'count' => count($formatted),
            'limit' => $limit,
            'offset' => $offset,
            'proposals' => $formatted,
            'timestamp' => date('c')
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        break;

    // ─── Propostas pagas ───
    case 'paid':
        $proposals = get_paid_proposals($limit, $offset);
        $formatted = array_map('format_proposal', $proposals);

        echo json_encode([
            'success' => true,
            'action' => 'paid',
            'count' => count($formatted),
            'limit' => $limit,
            'offset' => $offset,
            'proposals' => $formatted,
            'timestamp' => date('c')
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        break;

    // ─── Propostas não pagas ───
    case 'unpaid':
        $proposals = get_unpaid_proposals($limit, $offset);
        $formatted = array_map('format_proposal', $proposals);

        echo json_encode([
            'success' => true,
            'action' => 'unpaid',
            'count' => count($formatted),
            'limit' => $limit,
            'offset' => $offset,
            'proposals' => $formatted,
            'timestamp' => date('c')
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        break;

    // ─── Sites novos vendidos aguardando desenvolvimento ───
    case 'new_sites':
        $proposals = get_new_sites_to_build($limit, $offset);
        $formatted = array_map('format_proposal', $proposals);

        echo json_encode([
            'success' => true,
            'action' => 'new_sites',
            'message' => count($formatted) > 0
                ? count($formatted) . ' site(s) novo(s) vendido(s) aguardando desenvolvimento.'
                : 'Nenhum site novo para desenvolver no momento.',
            'has_new_sites' => count($formatted) > 0,
            'count' => count($formatted),
            'limit' => $limit,
            'offset' => $offset,
            'proposals' => $formatted,
            'timestamp' => date('c')
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        break;

    // ─── Resumo geral ───
    case 'summary':
        $summary = get_proposals_summary();

        if ($summary === null) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Não foi possível acessar o banco de dados.'
            ], JSON_UNESCAPED_UNICODE);
            break;
        }

        echo json_encode([
            'success' => true,
            'action' => 'summary',
            'data' => $summary,
            'timestamp' => date('c')
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        break;

    // ─── Action inválida ───
    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => "Action '$action' não reconhecida.",
            'available_actions' => ['all', 'paid', 'unpaid', 'new_sites', 'summary']
        ], JSON_UNESCAPED_UNICODE);
        break;
}

proposalLog("Consulta finalizada", ['action' => $action]);
