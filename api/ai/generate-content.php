<?php
/**
 * ============================================
 * AI CONTENT GENERATOR - API ENDPOINT
 * POST /api/ai/generate-content.php
 * ============================================
 * 
 * Endpoint para geração de conteúdo via Gemini AI.
 * Recebe tipo de página e input do usuário,
 * retorna JSON estruturado pronto para o Vue.js.
 */

// Proteção para carregar config segura
define('SECURE_CONFIG_ACCESS', true);

// Headers e CORS
require_once __DIR__ . '/../lib/cors.php';
header('Content-Type: application/json; charset=utf-8');

// Carregar dependências
require_once __DIR__ . '/../config.secure.php';
require_once __DIR__ . '/../lib/content-generator.php';

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método não permitido. Use POST.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Rate Limiting simples (opcional - pode implementar com Redis depois)
session_start();
$rateLimit = 10; // requisições
$ratePeriod = 60; // segundos

if (!isset($_SESSION['ai_requests'])) {
    $_SESSION['ai_requests'] = [];
}

// Limpar requisições antigas
$_SESSION['ai_requests'] = array_filter($_SESSION['ai_requests'], function($timestamp) use ($ratePeriod) {
    return $timestamp > (time() - $ratePeriod);
});

if (count($_SESSION['ai_requests']) >= $rateLimit) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'error' => 'Limite de requisições excedido. Aguarde 1 minuto.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$_SESSION['ai_requests'][] = time();

// Obter input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'JSON inválido no body da requisição.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validar campos obrigatórios
$pageType = $input['page_type'] ?? null;
$userInput = $input['user_input'] ?? null;
$voiceTone = $input['voice_tone'] ?? 'profissional';
$extra = [
    'company_name' => $input['company_name'] ?? null,
    'niche' => $input['niche'] ?? null
];

if (!$pageType || !$userInput) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Campos obrigatórios: page_type, user_input'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Sanitizar input (básico - pode expandir)
$userInput = strip_tags($userInput);
$userInput = mb_substr($userInput, 0, 5000); // Limite de caracteres

try {
    // Obter API Key do Gemini
    $geminiApiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : getenv('GEMINI_API_KEY');
    
    if (!$geminiApiKey) {
        throw new Exception('GEMINI_API_KEY não configurada no servidor.');
    }
    
    // Instanciar factory e gerar conteúdo
    $generator = new ContentGeneratorFactory($geminiApiKey);
    $result = $generator->generate($pageType, $userInput, $voiceTone, $extra);
    
    // Log para debug (remover em produção)
    if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
        error_log('[AI Generator] Type: ' . $pageType . ' | Success: ' . ($result['success'] ? 'true' : 'false'));
    }
    
    // Retornar resultado
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    
    // Log do erro
    error_log('[AI Generator ERROR] ' . $e->getMessage());
}
