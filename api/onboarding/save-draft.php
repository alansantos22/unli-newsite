<?php
/**
 * Onboarding Draft Auto-Save API
 * 
 * Saves the briefing draft as the user fills the form
 * POST /api/onboarding/save-draft.php
 */

// CORS - Configuração segura
require_once __DIR__ . '/../lib/cors.php';

header('Content-Type: application/json');

// Carregar configuração do banco de dados
require_once __DIR__ . '/../lib/database.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido. Use POST.'
    ]);
    exit();
}

// Get POST data - suporta tanto form-data quanto JSON
$rawInput = file_get_contents('php://input');
$jsonInput = json_decode($rawInput, true);

// Prioriza JSON body, fallback para $_POST
if ($jsonInput !== null) {
    // JSON body (session_id + data)
    $token = isset($jsonInput['session_id']) ? trim($jsonInput['session_id']) : '';
    $briefingData = isset($jsonInput['data']) ? $jsonInput['data'] : null;
    $briefingJson = $briefingData ? json_encode($briefingData, JSON_UNESCAPED_UNICODE) : '';
} else {
    // Form data legado (token + briefing)
    $token = isset($_POST['token']) ? trim($_POST['token']) : '';
    $briefingJson = isset($_POST['briefing']) ? trim($_POST['briefing']) : '';
    $briefingData = json_decode($briefingJson, true);
}

if (empty($token)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Token/session_id não fornecido.'
    ]);
    exit();
}

if (empty($briefingJson) || $briefingData === null) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Dados do briefing não fornecidos.'
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
    
    // Obter prefixo da tabela
    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
    
    // Check if token exists and status is not 'concluido'
    $stmt = $conn->prepare("
        SELECT id, onboarding_status 
        FROM " . $prefix . "orders 
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
            'message' => 'Token inválido.'
        ]);
        exit();
    }
    
    $order = $result->fetch_assoc();
    
    // Don't allow saving if already completed
    if ($order['onboarding_status'] === 'concluido') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'O briefing já foi finalizado e não pode ser editado.'
        ]);
        exit();
    }
    
    // Update briefing data and status to 'preenchendo'
    $updateStmt = $conn->prepare("
        UPDATE " . $prefix . "orders 
        SET 
            briefing_data = ?,
            onboarding_status = IF(onboarding_status = 'pendente', 'preenchendo', onboarding_status),
            updated_at = NOW()
        WHERE onboarding_token = ?
    ");
    
    if (!$updateStmt) {
        throw new Exception('Erro ao preparar atualização.');
    }
    
    $updateStmt->bind_param('ss', $briefingJson, $token);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Erro ao salvar rascunho.');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Rascunho salvo com sucesso.',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
    $stmt->close();
    $updateStmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao salvar: ' . $e->getMessage()
    ]);
}
