<?php
/**
 * Client handler functions for admin panel.
 * Included by api/admin/panel-mgmt.php — do NOT access directly via HTTP.
 * Variables in scope: $pdo, $prefix, $action, $admin
 */

function handle_list_clients($pdo, $prefix) {
    try {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(100, max(10, (int)($_GET['per_page'] ?? $_GET['limit'] ?? 25)));
        $offset = ($page - 1) * $limit;

        // Verificar se customer_email existe na tabela orders
        $colCheck = $pdo->prepare("SHOW COLUMNS FROM {$prefix}orders LIKE 'customer_email'");
        $colCheck->execute();
        $hasEmail = $colCheck->fetch() ? true : false;

        // Verificar se tabelas opcionais existem
        $hasSdrTable = false;
        $hasAffTable = false;
        try {
            $pdo->query("SELECT 1 FROM {$prefix}sdr_users LIMIT 0");
            $hasSdrTable = true;
        } catch (\Exception $e) {}
        try {
            $pdo->query("SELECT 1 FROM {$prefix}affiliate_users LIMIT 0");
            $hasAffTable = true;
        } catch (\Exception $e) {}

        $search = trim($_GET['search'] ?? '');
        $where = '';
        $params = [];

        if ($search) {
            $searchParam = '%' . $search . '%';
            if ($hasEmail) {
                $where = "WHERE o.customer_name LIKE ? OR o.customer_email LIKE ? OR o.company_name LIKE ?";
                $params = [$searchParam, $searchParam, $searchParam];
            } else {
                $where = "WHERE o.customer_name LIKE ? OR o.company_name LIKE ?";
                $params = [$searchParam, $searchParam];
            }
        }

        $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$prefix}orders o $where");
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];

        $emailCol  = $hasEmail    ? 'o.customer_email,' : '';
        $sdrJoin   = $hasSdrTable ? "LEFT JOIN {$prefix}sdr_users s ON s.id = o.sdr_id" : '';
        $affJoin   = $hasAffTable ? "LEFT JOIN {$prefix}affiliate_users a ON a.id = o.affiliate_id" : '';
        $sdrCol    = $hasSdrTable ? 's.name as sdr_name,' : "'' as sdr_name,";
        $affCol    = $hasAffTable ? "CONCAT(a.first_name, ' ', a.last_name) as affiliate_name" : "'' as affiliate_name";

        $stmt = $pdo->prepare("
            SELECT
                o.id, o.customer_name, $emailCol o.company_name,
                o.total_amount, o.payment_status, o.payment_method_manual,
                o.sdr_id, o.affiliate_id, o.created_at,
                $sdrCol
                $affCol
            FROM {$prefix}orders o
            $sdrJoin
            $affJoin
            $where
            ORDER BY o.created_at DESC
            LIMIT $limit OFFSET $offset
        ");
        $stmt->execute($params);
        $clients = $stmt->fetchAll();

        echo json_encode([
            'ok'    => true,
            'data'  => $clients,
            'total' => (int)$total,
            'pagination' => [
                'page'  => $page,
                'limit' => $limit,
                'total' => (int)$total,
                'pages' => (int)ceil($total / $limit)
            ]
        ]);
    } catch (\Throwable $e) {
        error_log('[ADMIN-USERS] handle_list_clients error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro ao carregar clientes: ' . $e->getMessage()]);
    }
}

function handle_delete_clients($pdo, $prefix, $admin) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
        return;
    }

    // Apenas super_admin pode excluir
    if (($admin['role'] ?? '') !== 'super_admin') {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Permissão negada. Apenas super_admin pode excluir clientes.']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $ids = $input['ids'] ?? [];

    if (!is_array($ids) || empty($ids)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Lista de IDs obrigatória']);
        return;
    }

    // Garantir que todos os IDs são inteiros positivos
    $ids = array_values(array_filter(array_map('intval', $ids), function($id) { return $id > 0; }));

    if (empty($ids)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'IDs inválidos']);
        return;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("DELETE FROM {$prefix}orders WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $deleted = $stmt->rowCount();

    admin_audit_log($admin['admin_id'], 'delete_clients', 'order', null, null, ['ids' => $ids, 'count' => $deleted]);

    echo json_encode(['ok' => true, 'deleted' => $deleted, 'message' => "$deleted pedido(s) excluído(s) com sucesso"]);
}

// Dispatch
switch ($action) {
    case 'list_clients':   handle_list_clients($pdo, $prefix); break;
    case 'delete_clients': handle_delete_clients($pdo, $prefix, $admin); break;
}
