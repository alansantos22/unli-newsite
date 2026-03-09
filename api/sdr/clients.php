<?php
/**
 * SDR Panel - Clients API
 * 
 * Endpoints:
 *   GET  /api/sdr/clients.php              → Lista clientes do SDR logado
 *   GET  /api/sdr/clients.php?id=123       → Detalhe de um cliente
 */

ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);

if (!defined('SECURE_CONFIG_ACCESS')) {
    define('SECURE_CONFIG_ACCESS', true);
}
$secureConfig = __DIR__ . '/../config.secure.php';
if (file_exists($secureConfig)) {
    require_once $secureConfig;
}

require_once __DIR__ . '/../lib/cors.php';

ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/middleware.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
    exit;
}

// Autenticar SDR
$sdr = require_sdr_auth();
$sdrId = $sdr['sdr_id'];

$pdo = get_db_connection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Erro de conexão']);
    exit;
}

$prefix = defined('DB_PREFIX') ? DB_PREFIX : '';

// Se tem ID, buscar detalhe de um cliente
$clientId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($clientId) {
    handle_detail($pdo, $prefix, $sdrId, $clientId);
} else {
    handle_list($pdo, $prefix, $sdrId);
}

// ============================================
// LISTA DE CLIENTES
// ============================================

function handle_list($pdo, $prefix, $sdrId) {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20;
    $offset = ($page - 1) * $perPage;
    
    $search = trim($_GET['search'] ?? '');
    $statusPayment = $_GET['payment_status'] ?? '';
    $statusOnboarding = $_GET['onboarding_status'] ?? '';
    
    $where = "WHERE o.sdr_id = ?";
    $params = [$sdrId];
    
    if ($search !== '') {
        $where .= " AND (o.customer_name LIKE ? OR o.email LIKE ? OR o.company_name LIKE ?)";
        $searchLike = "%{$search}%";
        $params[] = $searchLike;
        $params[] = $searchLike;
        $params[] = $searchLike;
    }
    
    if ($statusPayment !== '' && in_array($statusPayment, ['pending', 'paid', 'failed', 'refunded'])) {
        $where .= " AND o.payment_status = ?";
        $params[] = $statusPayment;
    }
    
    if ($statusOnboarding !== '' && in_array($statusOnboarding, ['pendente', 'preenchendo', 'concluido'])) {
        $where .= " AND o.onboarding_status = ?";
        $params[] = $statusOnboarding;
    }
    
    // Contar total
    $countSql = "SELECT COUNT(*) FROM {$prefix}orders o {$where}";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();
    
    // Buscar registros
    $sql = "SELECT 
                o.id,
                o.customer_name,
                o.company_name,
                o.email,
                o.phone,
                o.total_amount,
                o.payment_status,
                o.payment_method_manual,
                o.onboarding_status,
                o.onboarding_token,
                o.created_at,
                o.updated_at
            FROM {$prefix}orders o
            {$where}
            ORDER BY o.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $clients = $stmt->fetchAll();
    
    echo json_encode([
        'ok' => true,
        'clients' => $clients,
        'pagination' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => ceil($total / $perPage)
        ]
    ], JSON_UNESCAPED_UNICODE);
}

// ============================================
// DETALHE DO CLIENTE
// ============================================

function handle_detail($pdo, $prefix, $sdrId, $clientId) {
    $stmt = $pdo->prepare("
        SELECT 
            o.*
        FROM {$prefix}orders o
        WHERE o.id = ? AND o.sdr_id = ?
        LIMIT 1
    ");
    $stmt->execute([$clientId, $sdrId]);
    $client = $stmt->fetch();
    
    if (!$client) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Cliente não encontrado']);
        return;
    }
    
    // Decodificar JSON fields
    if (!empty($client['order_details'])) {
        $client['order_details'] = json_decode($client['order_details'], true);
    }
    if (!empty($client['briefing_data'])) {
        $client['briefing_data'] = json_decode($client['briefing_data'], true);
    }
    
    // Gerar link de onboarding se token existir
    $baseUrl = defined('SITE_BASE_URL') ? SITE_BASE_URL : 'https://unli.com.br';
    $client['onboarding_link'] = !empty($client['onboarding_token'])
        ? "{$baseUrl}/setup?token={$client['onboarding_token']}"
        : null;
    
    echo json_encode([
        'ok' => true,
        'client' => $client
    ], JSON_UNESCAPED_UNICODE);
}
