<?php
/**
 * Admin Panel - Finance API (Comissões e Pagamentos)
 * 
 * Endpoints:
 *   GET  ?action=pending_commissions  → Listar comissões pendentes
 *   POST ?action=mark_paid            → Marcar comissão(ões) como paga(s)
 *   GET  ?action=audit_sales          → Auditoria de vendas detalhada
 *   GET  ?action=payment_history      → Histórico de pagamentos
 */

ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error_log('[ADMIN-FINANCE] Fatal: ' . $err['message'] . ' in ' . $err['file'] . ':' . $err['line']);
        while (ob_get_level()) ob_end_clean();
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => $err['message'] . ' (' . basename($err['file']) . ':' . $err['line'] . ')']);
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
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/middleware.php';

$admin = require_admin_auth();
$pdo = get_db_connection();
$prefix = defined('DB_PREFIX') ? DB_PREFIX : '';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'pending_commissions': handle_pending_commissions($pdo, $prefix); break;
    case 'mark_paid':           handle_mark_paid($pdo, $prefix, $admin); break;
    case 'audit_sales':         handle_audit_sales($pdo, $prefix); break;
    case 'payment_history':     handle_payment_history($pdo, $prefix); break;
    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Ação inválida']);
}

function handle_pending_commissions($pdo, $prefix) {
    // Agregar por afiliado
    $stmt = $pdo->query("
        SELECT 
            a.id as affiliate_id,
            CONCAT(a.first_name, ' ', a.last_name) as affiliate_name,
            a.pix_key,
            a.tier,
            COUNT(r.id) as pending_count,
            SUM(r.commission_amount) as pending_total
        FROM {$prefix}affiliate_referrals r
        JOIN {$prefix}affiliate_users a ON a.id = r.affiliate_id
        WHERE r.commission_paid = 0 AND r.commission_amount > 0
        GROUP BY a.id
        ORDER BY pending_total DESC
    ");
    $aggregated = $stmt->fetchAll();
    
    // Detalhes individuais
    $stmt = $pdo->query("
        SELECT 
            r.id, r.affiliate_id, 
            CONCAT(a.first_name, ' ', a.last_name) as affiliate_name,
            a.pix_key,
            r.lead_name, r.lead_email, r.status,
            r.sale_amount, r.commission_rate, r.commission_amount,
            r.created_at
        FROM {$prefix}affiliate_referrals r
        JOIN {$prefix}affiliate_users a ON a.id = r.affiliate_id
        WHERE r.commission_paid = 0 AND r.commission_amount > 0
        ORDER BY r.created_at ASC
    ");
    $details = $stmt->fetchAll();
    
    echo json_encode([
        'ok' => true,
        'data' => [
            'by_affiliate' => $aggregated,
            'details' => $details
        ]
    ]);
}

function handle_mark_paid($pdo, $prefix, $admin) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
        return;
    }
    
    if ($admin['role'] === 'viewer') {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Permissão insuficiente']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Pode receber array de IDs individuais ou affiliate_id para baixa em massa
    $referralIds = $input['referral_ids'] ?? [];
    $affiliateId = (int)($input['affiliate_id'] ?? 0);
    
    if (empty($referralIds) && !$affiliateId) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Informe referral_ids ou affiliate_id']);
        return;
    }
    
    $pdo->beginTransaction();
    try {
        if ($affiliateId) {
            // Baixa em massa - todas comissões pendentes de um afiliado
            $stmt = $pdo->prepare("
                UPDATE {$prefix}affiliate_referrals 
                SET commission_paid = 1, commission_paid_at = NOW()
                WHERE affiliate_id = ? AND commission_paid = 0 AND commission_amount > 0
            ");
            $stmt->execute([$affiliateId]);
            $affectedRows = $stmt->rowCount();
            
            admin_audit_log($admin['admin_id'], 'bulk_commission_paid', 'affiliate', $affiliateId, null, 
                ['count' => $affectedRows, 'mode' => 'bulk']
            );
        } else {
            // Baixa individual por IDs
            $affectedRows = 0;
            foreach ($referralIds as $refId) {
                $refId = (int)$refId;
                if ($refId <= 0) continue;
                
                $stmt = $pdo->prepare("
                    UPDATE {$prefix}affiliate_referrals 
                    SET commission_paid = 1, commission_paid_at = NOW()
                    WHERE id = ? AND commission_paid = 0
                ");
                $stmt->execute([$refId]);
                $affectedRows += $stmt->rowCount();
                
                admin_audit_log($admin['admin_id'], 'commission_paid', 'referral', $refId);
            }
        }
        
        $pdo->commit();
        echo json_encode(['ok' => true, 'message' => "$affectedRows comissão(ões) marcada(s) como paga(s)"]);
        
    } catch (\Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro ao processar pagamentos']);
    }
}

function handle_audit_sales($pdo, $prefix) {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(10, (int)($_GET['limit'] ?? 25)));
    $offset = ($page - 1) * $limit;
    
    $where = [];
    $params = [];
    
    // Filtro por afiliado
    $affiliateId = (int)($_GET['affiliate_id'] ?? 0);
    if ($affiliateId) {
        $where[] = 'r.affiliate_id = ?';
        $params[] = $affiliateId;
    }
    
    // Filtro por status
    $status = $_GET['status'] ?? '';
    if ($status && in_array($status, ['lead','contacted','negotiating','closed','onboarding','completed','lost'])) {
        $where[] = 'r.status = ?';
        $params[] = $status;
    }
    
    // Filtro por período
    $from = $_GET['from'] ?? '';
    $to = $_GET['to'] ?? '';
    if ($from && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
        $where[] = 'r.created_at >= ?';
        $params[] = $from . ' 00:00:00';
    }
    if ($to && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
        $where[] = 'r.created_at <= ?';
        $params[] = $to . ' 23:59:59';
    }
    
    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}affiliate_referrals r $whereClause");
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];
    
    $stmt = $pdo->prepare("
        SELECT 
            r.id, r.affiliate_id, r.order_id,
            CONCAT(a.first_name, ' ', a.last_name) as affiliate_name,
            a.tier as affiliate_tier,
            r.lead_name, r.lead_email, r.lead_phone,
            r.status, r.sale_amount, r.commission_rate, r.commission_amount,
            r.commission_paid, r.commission_paid_at,
            r.source_page, r.notes, r.created_at,
            o.sdr_id,
            s.name as sdr_name
        FROM {$prefix}affiliate_referrals r
        JOIN {$prefix}affiliate_users a ON a.id = r.affiliate_id
        LEFT JOIN {$prefix}orders o ON o.id = r.order_id
        LEFT JOIN {$prefix}sdr_users s ON s.id = o.sdr_id
        $whereClause
        ORDER BY r.created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);
    $sales = $stmt->fetchAll();
    
    echo json_encode([
        'ok' => true,
        'data' => $sales,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$total,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

function handle_payment_history($pdo, $prefix) {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(10, (int)($_GET['limit'] ?? 25)));
    $offset = ($page - 1) * $limit;
    
    $countStmt = $pdo->query("
        SELECT COUNT(*) as total FROM {$prefix}affiliate_referrals 
        WHERE commission_paid = 1
    ");
    $total = $countStmt->fetch()['total'];
    
    $stmt = $pdo->prepare("
        SELECT 
            r.id, r.affiliate_id,
            CONCAT(a.first_name, ' ', a.last_name) as affiliate_name,
            r.lead_name, r.sale_amount, r.commission_amount,
            r.commission_paid_at, r.created_at
        FROM {$prefix}affiliate_referrals r
        JOIN {$prefix}affiliate_users a ON a.id = r.affiliate_id
        WHERE r.commission_paid = 1
        ORDER BY r.commission_paid_at DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute();
    $history = $stmt->fetchAll();
    
    echo json_encode([
        'ok' => true,
        'data' => $history,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$total,
            'pages' => ceil($total / $limit)
        ]
    ]);
}
