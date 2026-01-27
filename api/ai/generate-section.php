<?php
/**
 * ============================================
 * GENERATE SECTION
 * Endpoint otimizado para gerar seções individuais
 * ============================================
 * 
 * POST /api/ai/generate-section.php
 * 
 * Gera conteúdo para UMA seção por vez (mais performático)
 * Limite de 500 caracteres de input
 * Rate limit de 10 requisições por dia
 */

define('SECURE_CONFIG_ACCESS', true);
require_once __DIR__ . '/../config.secure.php';
require_once __DIR__ . '/../lib/cors.php';

header('Content-Type: application/json; charset=utf-8');

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Rate limiting (10 por dia)
session_start();
$today = date('Y-m-d');
$rateKey = 'ai_daily_' . session_id();

if (!isset($_SESSION[$rateKey]) || $_SESSION[$rateKey]['date'] !== $today) {
    $_SESSION[$rateKey] = ['date' => $today, 'count' => 0];
}

if ($_SESSION[$rateKey]['count'] >= 10) {
    http_response_code(429);
    echo json_encode([
        'error' => 'Limite diário atingido (10 gerações)',
        'retry_after' => strtotime('tomorrow') - time()
    ]);
    exit;
}

// Parse input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'JSON inválido']);
    exit;
}

// Validação
$pageType = trim($input['page_type'] ?? '');
$sectionId = trim($input['section_id'] ?? '');
$sectionTitle = trim($input['section_title'] ?? '');
$userInput = trim($input['input'] ?? '');
$voiceTone = trim($input['voice_tone'] ?? 'profissional');
$companyName = trim($input['company_name'] ?? '');

if (empty($userInput)) {
    http_response_code(400);
    echo json_encode(['error' => 'Input é obrigatório']);
    exit;
}

// Limite de 500 caracteres
$userInput = mb_substr($userInput, 0, 500);

// Mapeamento de tons
$toneStyles = [
    'profissional' => 'formal, confiável e corporativo',
    'amigavel' => 'acolhedor, próximo e simpático',
    'descontraido' => 'leve, jovem e informal',
    'luxuoso' => 'sofisticado, premium e exclusivo',
    'tecnico' => 'preciso, detalhista e especializado',
    'inspirador' => 'motivacional, empoderador e visionário'
];

$tone = $toneStyles[$voiceTone] ?? $toneStyles['profissional'];

// Prompts específicos por tipo de página/seção
$sectionPrompts = [
    'sobre_nos' => [
        'historia' => 'Transforme esta descrição em uma história envolvente de origem da empresa. Seja inspirador mas conciso.',
        'missao' => 'Reescreva esta missão de forma impactante e memorável. Uma frase poderosa.',
        'visao' => 'Transforme em uma declaração de visão ambiciosa e inspiradora.',
        'valores' => 'Expanda estes valores em descrições curtas (1 linha cada).'
    ],
    'servicos' => [
        'intro' => 'Crie uma introdução persuasiva para a seção de serviços.',
        'default' => 'Descreva este serviço de forma clara e persuasiva, destacando benefícios.'
    ],
    'faq' => [
        'default' => 'Crie uma resposta clara, útil e completa para esta pergunta. Seja objetivo.'
    ],
    'portfolio' => [
        'intro' => 'Crie uma apresentação impactante para o portfólio.',
        'default' => 'Descreva este projeto destacando o desafio e o resultado alcançado.'
    ]
];

// Seleciona o prompt adequado
$pagePrompts = $sectionPrompts[$pageType] ?? ['default' => 'Melhore e expanda este texto de forma profissional.'];
$sectionPrompt = $pagePrompts[$sectionId] ?? $pagePrompts['default'] ?? 'Melhore este texto.';

// Monta o prompt do sistema (compacto para economizar tokens)
$systemPrompt = "Você é um copywriter expert. Tarefa: {$sectionPrompt}

REGRAS:
- Tom: {$tone}
- Máximo 150 palavras
- Sem introduções como \"Aqui está\" ou \"Claro\"
- Retorne APENAS o texto final" . ($companyName ? "\n- Empresa: {$companyName}" : "");

// API Key
$apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';

if (empty($apiKey)) {
    http_response_code(500);
    echo json_encode(['error' => 'API key não configurada']);
    exit;
}

// Request para Gemini (configuração otimizada)
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";

$requestBody = [
    'contents' => [
        [
            'parts' => [
                ['text' => $systemPrompt . "\n\nINPUT:\n" . $userInput]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 300, // Limite baixo para economia
        'topK' => 40,
        'topP' => 0.9
    ]
];

// Chamada à API com retry logic para erro 429 (Rate Limit)
$maxRetries = 3;
$retryDelay = 2; // segundos
$attempt = 0;
$response = null;
$httpCode = 0;
$curlError = null;
$data = null;

do {
    $attempt++;
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($requestBody),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 15
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
        error_log("[AI Section] Rate limit hit, tentativa {$attempt}/{$maxRetries}. Aguardando {$retryDelay}s...");
        sleep($retryDelay);
        $retryDelay *= 2; // Exponential backoff
        continue;
    }
    
    // Outros erros ou esgotou tentativas
    break;
    
} while ($attempt < $maxRetries);

if ($curlError) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro de conexão: ' . $curlError]);
    exit;
}

$data = json_decode($response, true);

if ($httpCode === 429) {
    http_response_code(429);
    echo json_encode([
        'error' => 'O sistema de IA está sobrecarregado. Aguarde alguns segundos e tente novamente.',
        'retry_after' => 5
    ]);
    exit;
}

if ($httpCode !== 200) {
    $errorMsg = $data['error']['message'] ?? 'Erro na API';
    http_response_code(500);
    echo json_encode(['error' => $errorMsg]);
    exit;
}

// Extrai conteúdo
$content = '';
if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
    $content = trim($data['candidates'][0]['content']['parts'][0]['text']);
}

if (empty($content)) {
    http_response_code(500);
    echo json_encode(['error' => 'Resposta vazia da IA']);
    exit;
}

// Limpa aspas extras
$content = preg_replace('/^["\']+|["\']+$/u', '', $content);

// Incrementa contador
$_SESSION[$rateKey]['count']++;

// Retorna sucesso
echo json_encode([
    'success' => true,
    'content' => $content,
    'section_id' => $sectionId,
    'tokens_used' => $data['usageMetadata']['totalTokenCount'] ?? null,
    'remaining_today' => 10 - $_SESSION[$rateKey]['count']
]);
