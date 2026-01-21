<?php
/**
 * STORAGE - Persistência de pedidos
 * 
 * Por enquanto usa JSON (para desenvolvimento)
 * Em produção, migrar para MySQL
 */

/**
 * Gera ID único para pedido
 */
function generate_order_id(): string {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
}

/**
 * Salva pedido em arquivo JSON
 */
function save_order(array $order): bool {
    $ordersDir = __DIR__ . '/../orders';
    if (!is_dir($ordersDir)) {
        mkdir($ordersDir, 0755, true);
    }
    
    $filename = $ordersDir . '/' . $order['order_id'] . '.json';
    $json = json_encode($order, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
    return file_put_contents($filename, $json) !== false;
}

/**
 * Carrega pedido por ID
 */
function load_order(string $order_id): ?array {
    $ordersDir = __DIR__ . '/../orders';
    $filename = $ordersDir . '/' . $order_id . '.json';
    
    if (!file_exists($filename)) {
        return null;
    }
    
    $json = file_get_contents($filename);
    return json_decode($json, true);
}

/**
 * Atualiza status do pedido
 */
function update_order_status(string $order_id, string $status, array $extra = []): bool {
    $order = load_order($order_id);
    if (!$order) {
        return false;
    }
    
    $order['status'] = $status;
    $order['updated_at'] = date('Y-m-d H:i:s');
    
    if (!empty($extra)) {
        $order = array_merge($order, $extra);
    }
    
    return save_order($order);
}

/**
 * Lista todos os pedidos (para admin)
 */
function list_orders(int $limit = 100): array {
    $ordersDir = __DIR__ . '/../orders';
    if (!is_dir($ordersDir)) {
        return [];
    }
    
    $files = glob($ordersDir . '/ORD-*.json');
    rsort($files); // Mais recentes primeiro
    
    $orders = [];
    foreach (array_slice($files, 0, $limit) as $file) {
        $json = file_get_contents($file);
        $order = json_decode($json, true);
        if ($order) {
            $orders[] = $order;
        }
    }
    
    return $orders;
}
