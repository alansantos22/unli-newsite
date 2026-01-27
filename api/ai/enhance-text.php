<?php
/**
 * ============================================
 * ENHANCE TEXT WITH AI
 * Endpoint para melhorar/polir textos com Gemini
 * ============================================
 * 
 * POST /api/ai/enhance-text.php
 * 
 * Body:
 * {
 *   "text": "texto original",
 *   "prompt": "instrução customizada (opcional)",
 *   "voice_tone": "profissional|amigavel|descontraido|luxuoso|tecnico|inspirador",
 *   "context": "contexto adicional"
 * }
 */

// Configuração de segurança
define('SECURE_CONFIG_ACCESS', true);
require_once __DIR__ . '/../config.secure.php';
require_once __DIR__ . '/../lib/cors.php';

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Rate limiting simples (15 requests por minuto)
session_start();
$now = time();
$rateKey = 'enhance_rate_' . session_id();

if (!isset($_SESSION[$rateKey])) {
    $_SESSION[$rateKey] = ['count' => 0, 'reset' => $now + 60];
}

if ($now > $_SESSION[$rateKey]['reset']) {
    $_SESSION[$rateKey] = ['count' => 0, 'reset' => $now + 60];
}

$_SESSION[$rateKey]['count']++;

if ($_SESSION[$rateKey]['count'] > 15) {
    http_response_code(429);
    echo json_encode([
        'error' => 'Limite de requisições excedido. Aguarde 1 minuto.',
        'retry_after' => $_SESSION[$rateKey]['reset'] - $now
    ]);
    exit;
}

// Parse input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['text'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Texto é obrigatório']);
    exit;
}

// Validação
$text = trim($input['text']);
if (strlen($text) < 10) {
    http_response_code(400);
    echo json_encode(['error' => 'Texto deve ter pelo menos 10 caracteres']);
    exit;
}

if (strlen($text) > 5000) {
    http_response_code(400);
    echo json_encode(['error' => 'Texto não pode exceder 5000 caracteres']);
    exit;
}

// Parâmetros opcionais
$customPrompt = isset($input['prompt']) ? trim($input['prompt']) : null;
$voiceTone = isset($input['voice_tone']) ? trim($input['voice_tone']) : 'profissional';
$context = isset($input['context']) ? trim($input['context']) : 'texto para site institucional';

// Mapeamento de tons de voz
$toneDescriptions = [
    'profissional' => 'tom profissional e corporativo, transmitindo credibilidade',
    'amigavel' => 'tom amigável e acolhedor, criando proximidade com o leitor',
    'descontraido' => 'tom descontraído e jovem, com linguagem leve',
    'luxuoso' => 'tom sofisticado e premium, transmitindo exclusividade',
    'tecnico' => 'tom técnico e preciso, com detalhes específicos',
    'inspirador' => 'tom inspirador e motivacional, empoderador'
];

$toneDesc = $toneDescriptions[$voiceTone] ?? $toneDescriptions['profissional'];

// Monta o prompt do sistema
$systemPrompt = <<<PROMPT
Você é um copywriter especialista em textos para sites. Sua tarefa é melhorar textos mantendo a essência original.

REGRAS IMPORTANTES:
1. Mantenha o significado original - você está MELHORANDO, não reescrevendo
2. Corrija erros de gramática e ortografia
3. Melhore a clareza e fluidez do texto
4. Use {$toneDesc}
5. Mantenha um tamanho similar ao original (pode variar até 30%)
6. NÃO adicione informações que não existiam
7. NÃO use frases genéricas ou clichês excessivos
8. O texto é para: {$context}

FORMATO DE RESPOSTA:
Retorne APENAS o texto melhorado, sem explicações, sem aspas, sem prefixos como "Versão melhorada:".
PROMPT;

// Adiciona prompt customizado se fornecido
if ($customPrompt) {
    $systemPrompt .= "\n\nINSTRUÇÃO ADICIONAL: {$customPrompt}";
}

// Prepara request para Gemini
$apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';

if (empty($apiKey)) {
    http_response_code(500);
    echo json_encode(['error' => 'Chave da API não configurada']);
    exit;
}

$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";

$requestBody = [
    'contents' => [
        [
            'parts' => [
                ['text' => $systemPrompt . "\n\nTEXTO PARA MELHORAR:\n\n" . $text]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.4, // Menos criativo, mais fiel ao original
        'topK' => 40,
        'topP' => 0.95,
        'maxOutputTokens' => 2048,
    ],
    'safetySettings' => [
        ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
        ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
        ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
        ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE']
    ]
];

// Faz a chamada à API com retry logic para erro 429 (Rate Limit)
$maxRetries = 3;
$retryDelay = 2; // segundos (exponential backoff)
$attempt = 0;
$response = null;
$httpCode = 0;
$curlError = null;
$responseData = null;

do {
    $attempt++;
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($requestBody),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    // Se sucesso, sai do loop
    if ($httpCode === 200) {
        break;
    }
    
    // Se erro 429 (Rate Limit), espera e tenta novamente
    if ($httpCode === 429 && $attempt < $maxRetries) {
        error_log("[AI Enhance] Rate limit hit, tentativa {$attempt}/{$maxRetries}. Aguardando {$retryDelay}s...");
        sleep($retryDelay);
        $retryDelay *= 2; // Exponential backoff
        continue;
    }
    
    // Outros erros ou esgotou tentativas, sai do loop
    break;
    
} while ($attempt < $maxRetries);

// Verifica erros de curl
if ($curlError) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro de conexão com IA: ' . $curlError]);
    exit;
}

// Parse da resposta
$responseData = json_decode($response, true);

if ($httpCode === 429) {
    http_response_code(429);
    echo json_encode([
        'error' => 'O sistema de IA está sobrecarregado. Aguarde alguns segundos e tente novamente.',
        'retry_after' => 5
    ]);
    exit;
}

if ($httpCode !== 200) {
    $errorMsg = $responseData['error']['message'] ?? 'Erro na API da IA';
    http_response_code(500);
    echo json_encode(['error' => $errorMsg]);
    exit;
}

// Extrai o texto melhorado
$enhancedText = '';

if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    $enhancedText = trim($responseData['candidates'][0]['content']['parts'][0]['text']);
}

if (empty($enhancedText)) {
    http_response_code(500);
    echo json_encode(['error' => 'IA não retornou texto válido']);
    exit;
}

// Remove aspas no início/fim se a IA adicionou
$enhancedText = preg_replace('/^["\']+|["\']+$/u', '', $enhancedText);

// Retorna sucesso
echo json_encode([
    'success' => true,
    'enhanced_text' => $enhancedText,
    'original_length' => strlen($text),
    'enhanced_length' => strlen($enhancedText),
    'voice_tone' => $voiceTone
]);
