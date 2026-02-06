<?php
/**
 * ============================================
 * SDR EXTRACT DATA - SHADOW PROMPT API
 * POST /api/ai/sdr-extract-data.php
 * ============================================
 * 
 * Endpoint para extrair dados estruturados do chat SDR.
 * Usado internamente quando o usuário decide avançar
 * para o formulário ou finalizar a conversa.
 * 
 * Recebe o histórico completo da conversa e retorna
 * um JSON estruturado para preencher o Wizard.
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

// Obter input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['conversation'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'JSON inválido ou campo "conversation" ausente.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Prompt técnico para extração (o usuário não vê isso)
$extractionPrompt = getExtractionPrompt();

try {
    $geminiApiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : getenv('GEMINI_API_KEY');
    
    if (!$geminiApiKey) {
        throw new Exception('GEMINI_API_KEY não configurada no servidor.');
    }
    
    // Formatar conversa para análise
    $conversationText = formatConversationForExtraction($input['conversation']);
    
    // Chamar Gemini para extração
    $extractedData = callGeminiExtraction($geminiApiKey, $extractionPrompt, $conversationText);
    
    // Validar e sanitizar dados extraídos
    $validatedData = validateExtractedData($extractedData);
    
    echo json_encode([
        'success' => true,
        'data' => $validatedData
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Prompt técnico para extração de dados
 */
function getExtractionPrompt(): string {
    return <<<PROMPT
Você é um sistema de extração de dados. Analise a conversa de vendas abaixo e extraia as informações estruturadas.

TAREFA: Extrair dados do chat para preencher automaticamente um formulário de criação de site.

RETORNE APENAS um JSON válido com a seguinte estrutura:

{
  "business": {
    "name": "Nome do negócio/empresa (string ou null)",
    "niche": "Nicho/segmento (ex: advocacia, restaurante, barbearia)",
    "description": "Breve descrição do negócio baseada na conversa",
    "targetAudience": "Público-alvo identificado",
    "location": "Localização se mencionada (cidade/estado)"
  },
  "site": {
    "type": "landing | site_complete",
    "suggestedPackage": "essential | authority | enterprise | custom",
    "pages": ["about", "services", "contact", "portfolio", "blog", "faq", "showcase"],
    "features": ["agendamento", "catalogo", "formulario", "blog", "depoimentos"],
    "style": {
      "tone": "profissional | moderno | jovem | luxuoso | acolhedor",
      "colors": ["#hexcode1", "#hexcode2"],
      "inspiration": "Qualquer referência visual mencionada"
    }
  },
  "contact": {
    "name": "Nome do contato se mencionado",
    "email": "Email se mencionado",
    "phone": "Telefone se mencionado",
    "whatsapp": "WhatsApp se mencionado"
  },
  "sales": {
    "budget": "Orçamento mencionado ou null",
    "urgency": "baixa | media | alta",
    "paymentPreference": "pix | cartao | boleto | null",
    "objections": ["Lista de objeções levantadas"],
    "temperature": "frio | morno | quente"
  },
  "notes": "Observações importantes para a equipe de design/desenvolvimento"
}

REGRAS:
1. Use apenas informações EXPLÍCITAS da conversa
2. Para campos não mencionados, use null ou array vazio
3. Seja conservador - não invente dados
4. "temperature" deve ser:
   - "frio": apenas curiosidade, sem intenção clara
   - "morno": interessado mas precisa pensar
   - "quente": pronto para comprar
5. Em "pages", inclua apenas páginas que façam sentido para o negócio
6. Retorne APENAS o JSON, sem texto adicional
PROMPT;
}

/**
 * Formata a conversa para enviar ao Gemini
 */
function formatConversationForExtraction(array $conversation): string {
    $formatted = "=== CONVERSA DE VENDAS ===\n\n";
    
    foreach ($conversation as $msg) {
        $role = $msg['role'] === 'user' ? 'CLIENTE' : 'ASSISTENTE';
        $content = $msg['content'] ?? '';
        $formatted .= "[{$role}]: {$content}\n\n";
    }
    
    return $formatted;
}

/**
 * Chama o Gemini para extrair dados
 */
function callGeminiExtraction(string $apiKey, string $systemPrompt, string $conversation): array {
    $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    
    $contents = [
        [
            'role' => 'user',
            'parts' => [['text' => $systemPrompt . "\n\n" . $conversation]]
        ]
    ];
    
    $payload = [
        'contents' => $contents,
        'generationConfig' => [
            'temperature' => 0.3, // Baixa temperatura para extração precisa
            'topK' => 20,
            'topP' => 0.8,
            'maxOutputTokens' => 1500,
        ]
    ];
    
    $ch = curl_init($apiUrl . '?key=' . $apiKey);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
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
    
    $rawText = $data['candidates'][0]['content']['parts'][0]['text'];
    
    // Limpar e parsear JSON
    $cleanJson = preg_replace('/^```json\s*/i', '', $rawText);
    $cleanJson = preg_replace('/\s*```$/i', '', $cleanJson);
    $cleanJson = trim($cleanJson);
    
    $parsed = json_decode($cleanJson, true);
    
    if (!$parsed) {
        throw new Exception('Falha ao parsear JSON de extração.');
    }
    
    return $parsed;
}

/**
 * Valida e sanitiza os dados extraídos
 */
function validateExtractedData(array $data): array {
    // Estrutura padrão
    $default = [
        'business' => [
            'name' => null,
            'niche' => null,
            'description' => null,
            'targetAudience' => null,
            'location' => null
        ],
        'site' => [
            'type' => 'site_complete',
            'suggestedPackage' => 'essential',
            'pages' => ['about', 'services', 'contact'],
            'features' => [],
            'style' => [
                'tone' => 'profissional',
                'colors' => [],
                'inspiration' => null
            ]
        ],
        'contact' => [
            'name' => null,
            'email' => null,
            'phone' => null,
            'whatsapp' => null
        ],
        'sales' => [
            'budget' => null,
            'urgency' => 'media',
            'paymentPreference' => null,
            'objections' => [],
            'temperature' => 'morno'
        ],
        'notes' => null
    ];
    
    // Mesclar com dados extraídos
    return array_replace_recursive($default, $data);
}
