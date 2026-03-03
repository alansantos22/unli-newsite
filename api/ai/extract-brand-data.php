<?php
/**
 * ============================================
 * BRAND DATA EXTRACTOR - API ENDPOINT
 * POST /api/ai/extract-brand-data.php
 * ============================================
 * 
 * Endpoint para extrair dados estruturados da conversa consultiva.
 * Recebe o histórico do chat e retorna um JSON estruturado
 * com os dados da marca extraídos/inferidos.
 */

// Proteção para carregar config segura
define('SECURE_CONFIG_ACCESS', true);

// Headers e CORS
require_once __DIR__ . '/../lib/cors.php';
header('Content-Type: application/json; charset=utf-8');

// Carregar dependências
require_once __DIR__ . '/../config.secure.php';

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método não permitido. Use POST.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Rate Limiting
session_start();
$rateLimit = 10; // requisições (mais restritivo pois é mais caro)
$ratePeriod = 60; // segundos

if (!isset($_SESSION['extractor_requests'])) {
    $_SESSION['extractor_requests'] = [];
}

$_SESSION['extractor_requests'] = array_filter($_SESSION['extractor_requests'], function($timestamp) use ($ratePeriod) {
    return $timestamp > (time() - $ratePeriod);
});

if (count($_SESSION['extractor_requests']) >= $rateLimit) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'error' => 'Limite de requisições excedido. Aguarde 1 minuto.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$_SESSION['extractor_requests'][] = time();

// Obter input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['chat_history'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'JSON inválido ou campo "chat_history" ausente.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Carregar logger de conversas
require_once __DIR__ . '/../lib/conversation-logger.php';

// Carregar System Prompt do Extrator
$extractorPromptPath = __DIR__ . '/prompts/extractor-system-prompt.md';
$extractorPrompt = file_exists($extractorPromptPath) 
    ? file_get_contents($extractorPromptPath) 
    : getDefaultExtractorPrompt();

try {
    // Obter API Key do Gemini
    $geminiApiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : getenv('GEMINI_API_KEY');
    
    if (!$geminiApiKey) {
        throw new Exception('GEMINI_API_KEY não configurada no servidor.');
    }
    
    // Formatar a conversa como texto
    $conversationText = formatConversationAsText($input['chat_history']);
    
    // Chamar API do Gemini para extração
    $extractedJson = callGeminiExtraction($geminiApiKey, $extractorPrompt, $conversationText);
    
    // Validar e parsear o JSON
    $extractedData = parseExtractedJson($extractedJson);
    
    // Adicionar metadados
    $extractedData['extractionMeta']['conversationTurns'] = count($input['chat_history']);
    $extractedData['extractionMeta']['extractedAt'] = date('c');
    
    // Salvar dados extraídos na conversa de onboarding (se houver conversation_id)
    $extractConversationId = $input['conversation_id'] ?? null;
    if ($extractConversationId) {
        $conv = onboarding_get_or_create_conversation($extractConversationId);
        if ($conv) {
            onboarding_update_conversation($conv['id'], [
                'finished' => true,
                'extracted_data' => $extractedData
            ]);
            error_log('✅ [extract-brand-data] Dados extraídos salvos na conversa: ' . $extractConversationId);
        }
    }
    
    echo json_encode([
        'success' => true,
        'extracted' => $extractedData,
        'raw_json' => $extractedJson // Para debug
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Formata o histórico do chat como texto para o extrator
 */
function formatConversationAsText(array $chatHistory): string {
    $text = "=== INÍCIO DA CONVERSA ===\n\n";
    
    foreach ($chatHistory as $msg) {
        $role = $msg['role'] === 'assistant' ? 'Consultor Unli' : 'Cliente';
        $content = $msg['content'];
        $text .= "**{$role}:** {$content}\n\n";
    }
    
    $text .= "=== FIM DA CONVERSA ===";
    
    return $text;
}

/**
 * Chama a API do Gemini para extração de dados
 */
function callGeminiExtraction(string $apiKey, string $extractorPrompt, string $conversation): string {
    // Usar modelo mais capaz para extração complexa
    $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    
    $fullPrompt = $extractorPrompt . "\n\n" . $conversation;
    
    $payload = [
        'contents' => [
            [
                'role' => 'user',
                'parts' => [['text' => $fullPrompt]]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.3, // Mais determinístico para extração
            'topK' => 20,
            'topP' => 0.8,
            'maxOutputTokens' => 4096,
            'responseMimeType' => 'application/json' // Força output em JSON
        ]
    ];
    
    $ch = curl_init($apiUrl . '?key=' . $apiKey);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60, // Mais tempo para processamento
        CURLOPT_CONNECTTIMEOUT => 10
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception('Erro de conexão: ' . $error);
    }
    
    if ($httpCode !== 200) {
        $errorData = json_decode($response, true);
        $errorMessage = $errorData['error']['message'] ?? 'Erro desconhecido';
        throw new Exception('Erro na API Gemini: ' . $errorMessage);
    }
    
    $data = json_decode($response, true);
    
    if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        throw new Exception('Resposta inválida da API Gemini.');
    }
    
    return $data['candidates'][0]['content']['parts'][0]['text'];
}

/**
 * Parseia e valida o JSON extraído
 */
function parseExtractedJson(string $jsonString): array {
    // Limpeza robusta de artefatos de markdown e texto extra
    $jsonString = trim($jsonString);
    
    // Remove blocos de código markdown (```json ... ```)
    $jsonString = preg_replace('/^```(?:json)?\s*/i', '', $jsonString);
    $jsonString = preg_replace('/\s*```$/i', '', $jsonString);
    
    // Se ainda houver texto antes/depois do JSON, extrai apenas o objeto
    if (preg_match('/\{.*\}/s', $jsonString, $matches)) {
        $jsonString = $matches[0];
    }
    
    // Remove possíveis prefixos de texto ("Aqui está o JSON:" etc)
    $jsonString = preg_replace('/^[^{]*/', '', $jsonString);
    $jsonString = preg_replace('/[^}]*$/', '', $jsonString);
    
    $jsonString = trim($jsonString);
    
    $data = json_decode($jsonString, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Falha ao parsear JSON extraído: ' . json_last_error_msg());
    }
    
    // Garantir estrutura mínima
    $defaults = [
        'extractionMeta' => [
            'confidence' => 'medium',
            'conversationTurns' => 0,
            'missingFields' => [],
            'inferredFields' => []
        ],
        'companyInfo' => [
            'name' => null,
            'niche' => 'Não identificado',
            'targetAudience' => 'Não identificado',
            'mainProduct' => null
        ],
        'brandCore' => [
            'historySummary' => 'Não identificado',
            'mission' => 'Não identificado',
            'vision' => null,
            'values' => [],
            'foundingYear' => null,
            'founders' => null
        ],
        'authorityTriggers' => [
            'yearsInMarket' => null,
            'certifications' => [],
            'notableClients' => [],
            'keyAchievements' => null,
            'socialProofElements' => null
        ],
        'differentiation' => [
            'usp' => 'Não identificado',
            'uniqueMethodology' => null,
            'competitiveAdvantage' => null,
            'additionalContext' => null
        ],
        'visualIdentity' => [
            'suggestedStyle' => 'Moderno e Profissional',
            'colorVibe' => 'Tons neutros com acentos de cor',
            'primaryColorSuggestion' => null,
            'secondaryColorSuggestion' => null,
            'typographyMood' => 'Sans-serif moderna e legível',
            'layoutSuggestion' => 'Layout clean com boa hierarquia visual',
            'moodKeywords' => [],
            'heroSuggestion' => null,
            'specialSections' => []
        ],
        'aiSuggestions' => [
            'suggestedTagline' => 'Sua marca, nosso compromisso.',
            'alternativeTaglines' => [],
            'toneOfVoice' => 'Profissional',
            'toneDescription' => null,
            'suggestedKeywords' => [],
            'colorPaletteSuggestion' => null
        ],
        'contentSeeds' => [
            'aboutUsHook' => null,
            'servicesAngle' => null,
            'ctaSuggestion' => null
        ]
    ];
    
    // Merge com defaults (preservando dados extraídos)
    return array_replace_recursive($defaults, $data);
}

/**
 * Prompt padrão do extrator caso o arquivo não exista
 */
function getDefaultExtractorPrompt(): string {
    return <<<PROMPT
Você é um Compilador de Dados de Branding. Sua tarefa é ler o histórico de uma conversa de consultoria e transformá-lo em um JSON estruturado.

REGRAS:
1. Retorne APENAS um objeto JSON válido (sem markdown)
2. Infira informações quando não forem explícitas
3. Use português do Brasil
4. Se não conseguir extrair, use null

SCHEMA:
{
  "extractionMeta": { "confidence": "high|medium|low", "missingFields": [], "inferredFields": [] },
  "companyInfo": { "name": string, "niche": string, "targetAudience": string, "mainProduct": string },
  "brandCore": { "historySummary": string, "mission": string, "values": [string] },
  "authorityTriggers": { "yearsInMarket": string, "keyAchievements": string },
  "differentiation": { "usp": string, "uniqueMethodology": string },
  "aiSuggestions": { "suggestedTagline": string, "toneOfVoice": string, "suggestedKeywords": [string] }
}

ANALISE A CONVERSA ABAIXO:
PROMPT;
}
