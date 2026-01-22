<?php
/**
 * Onboarding Token Validation API
 * 
 * Validates the magic link token and returns order information
 * GET /api/onboarding/validate.php?token={token}
 */

// CORS - Configuração segura
require_once __DIR__ . '/../lib/cors.php';

header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido. Use GET.'
    ]);
    exit();
}

// Get token from query string
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (empty($token)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Token não fornecido.'
    ]);
    exit();
}

try {
    // Connect to database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception('Erro de conexão com banco de dados.');
    }
    
    $conn->set_charset('utf8mb4');
    
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("
        SELECT 
            id,
            email,
            customer_name,
            order_details,
            onboarding_status,
            briefing_data,
            created_at
        FROM orders 
        WHERE onboarding_token = ? 
        LIMIT 1
    ");
    
    if (!$stmt) {
        throw new Exception('Erro ao preparar consulta.');
    }
    
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Token inválido ou expirado. Verifique o link no seu e-mail.'
        ]);
        exit();
    }
    
    $order = $result->fetch_assoc();
    
    // Check if token is too old (optional: 30 days expiration)
    $created = strtotime($order['created_at']);
    $now = time();
    $daysSinceCreation = ($now - $created) / (60 * 60 * 24);
    
    if ($daysSinceCreation > 30) {
        http_response_code(410);
        echo json_encode([
            'success' => false,
            'message' => 'Este link expirou. Entre em contato com nosso suporte.'
        ]);
        exit();
    }
    
    // Parse briefing data if exists
    $briefingData = null;
    if (!empty($order['briefing_data'])) {
        $briefingData = json_decode($order['briefing_data'], true);
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'orderId' => $order['id'],
        'customerName' => $order['customer_name'],
        'email' => $order['email'],
        'status' => $order['onboarding_status'],
        'briefing' => $briefingData,
        'message' => $order['onboarding_status'] === 'concluido' 
            ? 'Briefing já foi concluído.' 
            : 'Token válido. Pode prosseguir.'
    ]);
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
