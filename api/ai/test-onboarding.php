<?php
/**
 * TESTE SIMPLES para identificar onde está quebrando
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Teste 1: Básico
echo json_encode(['step' => 1, 'message' => 'Teste básico OK']);
exit;

// Se chegou aqui, vamos testar carregamentos

// Teste 2: Config segura
try {
    define('SECURE_CONFIG_ACCESS', true);
    require_once __DIR__ . '/../config.secure.php';
    echo json_encode(['step' => 2, 'message' => 'Config segura OK']);
} catch (Exception $e) {
    echo json_encode(['step' => 2, 'error' => $e->getMessage()]);
    exit;
}

// Teste 3: Verificar API Key
try {
    $geminiApiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : getenv('GEMINI_API_KEY');
    echo json_encode([
        'step' => 3, 
        'message' => 'API Key check',
        'hasKey' => !empty($geminiApiKey),
        'keyLength' => strlen($geminiApiKey ?? '')
    ]);
} catch (Exception $e) {
    echo json_encode(['step' => 3, 'error' => $e->getMessage()]);
}
