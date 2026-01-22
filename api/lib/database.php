<?php
/**
 * ============================================
 * DATABASE HELPER - Gerenciamento do MySQL
 * ============================================
 * 
 * Funções para conexão e operações no banco de dados
 */

// Proteção contra acesso direto
if (!defined('DB_CONFIG_ACCESS')) {
    define('DB_CONFIG_ACCESS', true);
}

require_once __DIR__ . '/../db.config.php';

/**
 * Obtém conexão PDO com o banco de dados
 * 
 * @return PDO Instância da conexão
 * @throws PDOException se falhar
 */
function get_db_connection() {
    static $pdo = null;
    
    if ($pdo !== null) {
        return $pdo;
    }
    
    try {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );
        
        if (DB_PORT) {
            $dsn .= ';port=' . DB_PORT;
        }
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE " . DB_COLLATE
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        
        return $pdo;
        
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        throw new Exception("Erro ao conectar ao banco de dados");
    }
}

/**
 * Salva um pedido no banco de dados (status inicial: pending)
 * 
 * @param array $orderData Dados do pedido
 * @return int|false ID do pedido inserido ou false em caso de erro
 */
function save_order_to_db($orderData) {
    try {
        $pdo = get_db_connection();
        
        // Gerar token único para onboarding
        $token = bin2hex(random_bytes(32));
        
        // Preparar dados para inserção
        $sql = "INSERT INTO " . DB_PREFIX . "orders (
            customer_name,
            email,
            phone,
            order_details,
            total_amount,
            payment_status,
            payment_method,
            payment_id,
            onboarding_token,
            onboarding_status
        ) VALUES (
            :customer_name,
            :email,
            :phone,
            :order_details,
            :total_amount,
            :payment_status,
            :payment_method,
            :payment_id,
            :onboarding_token,
            :onboarding_status
        )";
        
        $stmt = $pdo->prepare($sql);
        
        // Preparar order_details como JSON
        $orderDetailsJson = json_encode([
            'order_id' => $orderData['order_id'] ?? '',
            'product' => $orderData['selection']['product'] ?? '',
            'pages' => $orderData['selection']['pages'] ?? 0,
            'content' => $orderData['selection']['content'] ?? '',
            'pricing' => $orderData['pricing'] ?? [],
            'selection' => $orderData['selection'] ?? []
        ], JSON_UNESCAPED_UNICODE);
        
        // Executar inserção
        $stmt->execute([
            ':customer_name' => $orderData['briefing']['company_name'] ?? 'Cliente',
            ':email' => $orderData['briefing']['email'] ?? '',
            ':phone' => $orderData['briefing']['whatsapp'] ?? null,
            ':order_details' => $orderDetailsJson,
            ':total_amount' => $orderData['pricing']['final'] ?? 0,
            ':payment_status' => 'pending',
            ':payment_method' => $orderData['payment_method'] ?? 'avista',
            ':payment_id' => null,
            ':onboarding_token' => $token,
            ':onboarding_status' => 'pendente'
        ]);
        
        return [
            'id' => $pdo->lastInsertId(),
            'token' => $token
        ];
        
    } catch (PDOException $e) {
        error_log("Error saving order to database: " . $e->getMessage());
        return false;
    }
}

/**
 * Atualiza o status de pagamento de um pedido
 * 
 * @param string $orderId ID do pedido (ORD-...)
 * @param string $status Status do pagamento (pending, paid, failed, refunded)
 * @param string|null $paymentId ID da transação no gateway
 * @return bool Sucesso da operação
 */
function update_payment_status($orderId, $status, $paymentId = null) {
    try {
        $pdo = get_db_connection();
        
        $sql = "UPDATE " . DB_PREFIX . "orders 
                SET payment_status = :status,
                    payment_id = :payment_id,
                    updated_at = CURRENT_TIMESTAMP
                WHERE JSON_EXTRACT(order_details, '$.order_id') = :order_id";
        
        $stmt = $pdo->prepare($sql);
        
        return $stmt->execute([
            ':status' => $status,
            ':payment_id' => $paymentId,
            ':order_id' => $orderId
        ]);
        
    } catch (PDOException $e) {
        error_log("Error updating payment status: " . $e->getMessage());
        return false;
    }
}

/**
 * Busca pedido por order_id
 * 
 * @param string $orderId ID do pedido
 * @return array|false Dados do pedido ou false
 */
function get_order_by_id($orderId) {
    try {
        $pdo = get_db_connection();
        
        $sql = "SELECT * FROM " . DB_PREFIX . "orders 
                WHERE JSON_EXTRACT(order_details, '$.order_id') = :order_id
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);
        
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("Error fetching order: " . $e->getMessage());
        return false;
    }
}

/**
 * Busca pedido por token de onboarding
 * 
 * @param string $token Token único
 * @return array|false Dados do pedido ou false
 */
function get_order_by_token($token) {
    try {
        $pdo = get_db_connection();
        
        $sql = "SELECT * FROM " . DB_PREFIX . "orders 
                WHERE onboarding_token = :token
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':token' => $token]);
        
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("Error fetching order by token: " . $e->getMessage());
        return false;
    }
}

/**
 * Atualiza o status do onboarding
 * 
 * @param string $token Token de onboarding
 * @param string $status Status (pendente, preenchendo, concluido)
 * @param array|null $briefingData Dados do briefing (opcional)
 * @return bool Sucesso da operação
 */
function update_onboarding_status($token, $status, $briefingData = null) {
    try {
        $pdo = get_db_connection();
        
        $sql = "UPDATE " . DB_PREFIX . "orders 
                SET onboarding_status = :status,
                    updated_at = CURRENT_TIMESTAMP";
        
        $params = [':status' => $status, ':token' => $token];
        
        if ($briefingData !== null) {
            $sql .= ", briefing_data = :briefing_data";
            $params[':briefing_data'] = json_encode($briefingData, JSON_UNESCAPED_UNICODE);
        }
        
        if ($status === 'concluido') {
            $sql .= ", completed_at = CURRENT_TIMESTAMP";
        }
        
        $sql .= " WHERE onboarding_token = :token";
        
        $stmt = $pdo->prepare($sql);
        
        return $stmt->execute($params);
        
    } catch (PDOException $e) {
        error_log("Error updating onboarding status: " . $e->getMessage());
        return false;
    }
}

/**
 * Lista pedidos com filtros
 * 
 * @param array $filters Filtros (payment_status, onboarding_status, etc.)
 * @param int $limit Limite de resultados
 * @param int $offset Offset para paginação
 * @return array Lista de pedidos
 */
function list_orders($filters = [], $limit = 20, $offset = 0) {
    try {
        $pdo = get_db_connection();
        
        $sql = "SELECT * FROM " . DB_PREFIX . "orders WHERE 1=1";
        $params = [];
        
        if (!empty($filters['payment_status'])) {
            $sql .= " AND payment_status = :payment_status";
            $params[':payment_status'] = $filters['payment_status'];
        }
        
        if (!empty($filters['onboarding_status'])) {
            $sql .= " AND onboarding_status = :onboarding_status";
            $params[':onboarding_status'] = $filters['onboarding_status'];
        }
        
        if (!empty($filters['email'])) {
            $sql .= " AND email LIKE :email";
            $params[':email'] = '%' . $filters['email'] . '%';
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $pdo->prepare($sql);
        
        // Bind dos parâmetros
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error listing orders: " . $e->getMessage());
        return [];
    }
}
