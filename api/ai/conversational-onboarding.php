<?php
/**
 * ============================================
 * CONVERSATIONAL ONBOARDING API
 * Endpoint principal do chat com IA
 * ============================================
 * 
 * POST /api/ai/conversational-onboarding.php
 * 
 * Processa mensagens do usuário, extrai campos
 * e retorna resposta do assistente Jules.
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

// Rate limiting
session_start();
$rateKey = 'conversational_rate_' . session_id();
$now = time();

if (!isset($_SESSION[$rateKey])) {
    $_SESSION[$rateKey] = ['count' => 0, 'reset' => $now + 60];
}

if ($now > $_SESSION[$rateKey]['reset']) {
    $_SESSION[$rateKey] = ['count' => 0, 'reset' => $now + 60];
}

$_SESSION[$rateKey]['count']++;

if ($_SESSION[$rateKey]['count'] > 30) { // 30 mensagens por minuto
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'error' => 'Limite de mensagens excedido. Aguarde um momento.',
        'retry_after' => $_SESSION[$rateKey]['reset'] - $now
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

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

// Validar campos
$sessionId = $input['session_id'] ?? null;
$currentStep = $input['step'] ?? 'identity';
$userMessage = $input['user_message'] ?? '';
$currentFormData = $input['current_form_data'] ?? [];
$voiceTone = $input['voice_tone'] ?? 'profissional';
$messages = $input['messages'] ?? [];

if (empty(trim($userMessage))) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Mensagem do usuário é obrigatória.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Sanitizar
$userMessage = strip_tags($userMessage);
$userMessage = mb_substr($userMessage, 0, 2000);

// =============================================
// EXTRAÇÃO LOCAL (sem IA) para casos simples
// =============================================
$localResult = tryLocalExtraction($currentStep, $userMessage, $currentFormData);

if ($localResult !== null) {
    // Conseguiu resolver localmente, não precisa chamar IA
    echo json_encode($localResult, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

try {
    // Obter API Key do Gemini
    $geminiApiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : getenv('GEMINI_API_KEY');
    
    if (!$geminiApiKey) {
        throw new Exception('GEMINI_API_KEY não configurada no servidor.');
    }
    
    // Processar com Gemini
    $result = processConversation(
        $geminiApiKey,
        $currentStep,
        $userMessage,
        $currentFormData,
        $voiceTone,
        $messages
    );
    
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    
    error_log('[Conversational AI ERROR] ' . $e->getMessage());
}

/**
 * Processa a conversa com o Gemini
 */
function processConversation(
    string $apiKey,
    string $step,
    string $userMessage,
    array $formData,
    string $voiceTone,
    array $previousMessages
): array {
    // Montar System Prompt
    $systemPrompt = buildSystemPrompt($step, $formData, $voiceTone);
    
    // Montar histórico de conversa
    $conversationHistory = buildConversationHistory($previousMessages);
    
    // Chamar API do Gemini
    $response = callGeminiAPI($apiKey, $systemPrompt, $conversationHistory, $userMessage);
    
    if (!$response['success']) {
        return $response;
    }
    
    return $response;
}

/**
 * Monta o System Prompt baseado no step atual
 */
function buildSystemPrompt(string $step, array $formData, string $voiceTone): string {
    $filledFields = [];
    $missingFields = [];
    
    foreach ($formData as $key => $value) {
        if (!empty($value) && $value !== false) {
            if (is_array($value) && count($value) === 0) continue;
            $filledFields[$key] = $value;
        }
    }
    
    $filledFieldsJson = json_encode($filledFields, JSON_UNESCAPED_UNICODE);
    
    $stepFields = getStepFields($step);
    $stepFieldsList = implode(', ', $stepFields);
    
    // Calcular campos que ainda faltam
    foreach ($stepFields as $field) {
        if (!isset($filledFields[$field]) || empty($filledFields[$field])) {
            $missingFields[] = $field;
        }
    }
    $missingFieldsList = implode(', ', $missingFields);
    
    $basePrompt = <<<PROMPT
Você é o **Jules**, um assistente de criação de sites amigável e profissional da Unli.

PERSONALIDADE:
- Simpático, descontraído mas profissional
- Use emojis ocasionalmente (sem exagero)
- Fale em português brasileiro natural
- Seja conciso mas informativo
- Demonstre entusiasmo genuíno pelo projeto do cliente

CONTEXTO ATUAL:
- Step: {$step}
- Tom de voz do cliente: {$voiceTone}
- Campos já preenchidos: {$filledFieldsJson}
- CAMPOS QUE FALTAM: {$missingFieldsList}

CAMPOS DESTE STEP: {$stepFieldsList}

SUA TAREFA PRINCIPAL:
1. EXTRAIR informações da mensagem do usuário e mapear para os campos
2. CONFIRMAR os dados extraídos de forma amigável
3. SUGERIR melhorias quando aplicável (ex: frases mais impactantes)
4. **SEMPRE perguntar sobre o PRÓXIMO CAMPO que falta** - não deixe o fluxo morrer!
5. Se o usuário confirmar algo, PERGUNTE SOBRE O PRÓXIMO CAMPO PENDENTE

REGRA CRÍTICA: 
- NUNCA responda apenas "O que mais posso te ajudar?" sem direção
- SEMPRE guie o cliente para o próximo passo
- Se o step estiver completo, diga que está indo para o próximo step
- Seja proativo: sugira o próximo passo sempre

REGRAS DE EXTRAÇÃO:
- Para "businessType", mapeie para: alimentacao, saude, beleza, educacao, tecnologia, construcao, moda, servicos, juridico, eventos, turismo, imoveis, automotivo, pets, comercio, industria, outro
- Para "voiceTone", mapeie para: profissional, amigavel, descontraido, luxuoso, tecnico, inspirador
- Para telefones, extraia apenas números (aceite formatos brasileiros)
- Para cores, aceite nomes ("azul", "vermelho") ou hexadecimais (#0066CC)
- Para anos, extraia números de 4 dígitos entre 1900 e 2026

FORMATO DE RESPOSTA (JSON OBRIGATÓRIO):
{
  "success": true,
  "assistant_message": "Sua mensagem amigável aqui. Use **negrito** para destacar campos extraídos.",
  "extracted_fields": {
    "campo1": "valor1",
    "campo2": "valor2"
  },
  "suggestions": [
    {
      "field": "frase",
      "value": "Sugestão de frase melhorada",
      "reason": "Mais impactante e profissional"
    }
  ],
  "next_question": {
    "field": "primaryColor",
    "question": "Pergunta sobre o próximo campo"
  },
  "achievements": [],
  "step_complete": false
}

IMPORTANTE:
- Se não extrair nenhum campo, extracted_fields deve ser {}
- Se não tiver sugestões, suggestions deve ser []
- Se o step estiver completo, step_complete = true
- Não invente dados que o usuário não forneceu
PROMPT;

    // Adicionar instruções específicas por step
    $stepInstructions = getStepInstructions($step);
    
    return $basePrompt . "\n\n" . $stepInstructions;
}

/**
 * Retorna campos do step atual
 */
function getStepFields(string $step): array {
    $fields = [
        'identity' => ['companyName', 'businessType', 'frase', 'primaryColor', 'secondaryColor', 'voiceTone', 'logo', 'hasNoLogo'],
        'contact' => ['whatsapp', 'additionalPhones', 'socialNetworks', 'hasPhysicalLocation', 'addressCep', 'addressStreet', 'addressNumber', 'addressComplement', 'addressNeighborhood', 'addressCity', 'addressState', 'businessHours'],
        'about' => ['aboutSectionTitle', 'companyBio', 'foundingYear', 'founders', 'aboutImage', 'companyHighlights', 'showMissionVision', 'mission', 'vision', 'values'],
        'services' => ['servicesSectionTitle', 'servicesIntro', 'services', 'hasGuarantee', 'guaranteeDetails'],
        'faq' => ['faqItems'],
        'finalization' => ['additionalNotes', 'urgency', 'inspirationUrls', 'inspirationImages']
    ];
    
    return $fields[$step] ?? [];
}

/**
 * Instruções específicas por step
 */
function getStepInstructions(string $step): string {
    $instructions = [
        'identity' => <<<STEP
STEP: IDENTIDADE DA MARCA

Foco: Coletar nome da empresa, ramo, frase de destaque, cores e tom de voz.

COMPORTAMENTOS ESPECIAIS:
1. Se o usuário mencionar o ramo, SUGIRA cores que combinem:
   - Saúde: #28A745 (verde) + #17A2B8 (azul claro)
   - Tecnologia: #0066CC (azul) + #6F42C1 (roxo)
   - Alimentação: #FF6B35 (laranja) + #FFC107 (amarelo)
   - Beleza: #E83E8C (rosa) + #6F42C1 (roxo)
   - Jurídico: #212529 (preto) + #0066CC (azul)

2. Se o usuário der uma frase simples, sugira uma versão mais impactante.

3. Se o usuário disser "não tenho logo", defina hasNoLogo = true.

CONQUISTA: Quando preencher companyName + businessType + frase, adicione "identity_unlocked" em achievements.
STEP,

        'contact' => <<<STEP
STEP: CONTATO E LOCALIZAÇÃO

Foco: WhatsApp principal, redes sociais, endereço (se aplicável).

COMPORTAMENTOS:
1. Extraia números de telefone brasileiros (aceite com ou sem formatação)
2. Para redes sociais, aceite @usuario ou URLs completas
3. Se o usuário mencionar "não tenho loja física", defina hasPhysicalLocation = false

CONQUISTA: Quando preencher whatsapp + 1 rede social, adicione "connection_established".
STEP,

        'about' => <<<STEP
STEP: HISTÓRIA DA EMPRESA

Foco: Biografia, ano de fundação, diferenciais, missão/visão/valores.

COMPORTAMENTOS ESPECIAIS:
1. Se o usuário contar a história de forma desorganizada, REFORMULE em um texto profissional para companyBio
2. Extraia o ano de fundação se mencionado
3. Identifique diferenciais mencionados e sugira como companyHighlights
4. Se mencionar missão/visão/valores, extraia e sugira ativar showMissionVision = true

CONQUISTA: Quando tiver companyBio preenchido, adicione "story_mastered".
STEP,

        'services' => <<<STEP
STEP: SERVIÇOS E SOLUÇÕES

Foco: Lista de serviços com nome e descrição.

COMPORTAMENTOS:
1. Se o usuário listar serviços de forma simples, estruture em array:
   services: [{ name: "Nome", shortDescription: "Descrição gerada" }]
2. Crie descrições profissionais e persuasivas para cada serviço
3. Pergunte sobre preços e garantia

CONQUISTA: Quando tiver 3+ serviços, adicione "services_catalog".
STEP,

        'faq' => <<<STEP
STEP: PERGUNTAS FREQUENTES

Foco: Perguntas que os clientes fazem frequentemente.

COMPORTAMENTOS:
1. Se o usuário descrever o nicho, SUGIRA 5-7 perguntas que quebram objeções de venda
2. Estruture como: faqItems: [{ question: "...", answer: "..." }]
3. As respostas devem ser profissionais e persuasivas

CONQUISTA: Quando tiver 5+ FAQs, adicione "faq_strategic".
STEP,

        'finalization' => <<<STEP
STEP: FINALIZAÇÃO

Foco: Observações extras, urgência, referências.

COMPORTAMENTOS:
1. Pergunte sobre urgência: urgent (até 3 dias), normal (até 7 dias), relaxed
2. Aceite URLs de sites de inspiração
3. Agradeça e parabenize pelo progresso

Quando este step estiver completo, defina step_complete = true.
STEP
    ];
    
    return $instructions[$step] ?? '';
}

/**
 * Monta histórico de conversa para contexto
 */
function buildConversationHistory(array $messages): array {
    $history = [];
    
    foreach ($messages as $msg) {
        if ($msg['type'] === 'assistant') {
            $history[] = [
                'role' => 'model',
                'parts' => [['text' => $msg['content']]]
            ];
        } elseif (in_array($msg['type'], ['user_text', 'user_audio'])) {
            $history[] = [
                'role' => 'user',
                'parts' => [['text' => $msg['content']]]
            ];
        }
    }
    
    return $history;
}

/**
 * Chama a API do Gemini
 */
function callGeminiAPI(
    string $apiKey,
    string $systemPrompt,
    array $conversationHistory,
    string $userMessage
): array {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";
    
    // Adicionar mensagem atual ao histórico
    $conversationHistory[] = [
        'role' => 'user',
        'parts' => [['text' => $userMessage]]
    ];
    
    $payload = [
        'contents' => $conversationHistory,
        'systemInstruction' => [
            'parts' => [['text' => $systemPrompt]]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 1024,
            'responseMimeType' => 'application/json'
        ],
        'safetySettings' => [
            ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE']
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'error' => 'Erro de conexão: ' . $error
        ];
    }
    
    if ($httpCode !== 200) {
        $errorData = json_decode($response, true);
        $errorMessage = $errorData['error']['message'] ?? 'Erro desconhecido';
        return [
            'success' => false,
            'error' => "API Error ({$httpCode}): {$errorMessage}"
        ];
    }
    
    $data = json_decode($response, true);
    
    if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        return [
            'success' => false,
            'error' => 'Resposta inválida da API'
        ];
    }
    
    $generatedText = $data['candidates'][0]['content']['parts'][0]['text'];
    
    // Parse do JSON gerado
    $result = json_decode($generatedText, true);
    
    if (!$result) {
        // Se não for JSON válido, retorna como mensagem simples
        return [
            'success' => true,
            'assistant_message' => $generatedText,
            'extracted_fields' => [],
            'suggestions' => [],
            'achievements' => []
        ];
    }
    
    // Garantir estrutura completa
    return [
        'success' => true,
        'assistant_message' => $result['assistant_message'] ?? $generatedText,
        'extracted_fields' => $result['extracted_fields'] ?? [],
        'suggestions' => $result['suggestions'] ?? [],
        'next_question' => $result['next_question'] ?? null,
        'achievements' => $result['achievements'] ?? [],
        'step_complete' => $result['step_complete'] ?? false
    ];
}

/**
 * ============================================
 * EXTRAÇÃO LOCAL (SEM IA)
 * ============================================
 * Casos simples que não precisam de IA
 */
function tryLocalExtraction(string $step, string $message, array $formData): ?array {
    $message = trim($message);
    $messageLower = mb_strtolower($message);
    
    // =============================================
    // CONFIRMAÇÕES SIMPLES - NÃO TRAVAR O FLUXO
    // Deixar para a IA responder confirmações para manter o fluxo natural
    // =============================================
    // NÃO INTERCEPTAR: sim, ok, gostei, etc. - deixar IA decidir próximo passo
    
    // =============================================
    // FRASES PERSONALIZADAS (quando o cliente escreve manualmente)
    // =============================================
    if (preg_match('/^(minha frase|quero escrever|a frase|usar essa frase)/i', $messageLower)) {
        // Extrair a frase após os dois pontos ou entre aspas
        $frase = null;
        
        if (preg_match('/[:\-]\s*["\']?(.+?)["\']?\s*$/i', $message, $matches)) {
            $frase = trim($matches[1]);
        } elseif (preg_match('/["\'](.+?)["\']/i', $message, $matches)) {
            $frase = trim($matches[1]);
        }
        
        if ($frase && mb_strlen($frase) >= 5) {
            return [
                'success' => true,
                'assistant_message' => "Adorei sua frase: **\"$frase\"** 🎯\n\nVamos continuar?",
                'extracted_fields' => ['frase' => $frase],
                'suggestions' => [],
                'achievements' => [],
                'skip_ai' => true
            ];
        }
    }
    
    // =============================================
    // RESPOSTAS DIRETAS NO STEP IDENTITY
    // =============================================
    if ($step === 'identity') {
        // Nome da empresa simples (2-5 palavras, sem verbo)
        if (
            empty($formData['companyName']) && 
            mb_strlen($message) < 60 && 
            preg_match('/^[A-ZÀ-ÿ][a-zA-ZÀ-ÿ\s&\-\.\']+$/', $message) &&
            !preg_match('/(trabalhamos?|vendemos|somos|fazemos|atuo|meu|minha|nossa|nosso)/i', $messageLower)
        ) {
            return [
                'success' => true,
                'assistant_message' => "Prazer, **{$message}**! 👋\n\nQual é o ramo de atuação da empresa?",
                'extracted_fields' => ['companyName' => $message],
                'suggestions' => [],
                'achievements' => [],
                'skip_ai' => true
            ];
        }
        
        // WhatsApp simples
        if (preg_match('/(\d{2})[\s\-]?(\d{4,5})[\s\-]?(\d{4})/', $message, $matches)) {
            $phone = $matches[1] . $matches[2] . $matches[3];
            return [
                'success' => true,
                'assistant_message' => "Anotei o WhatsApp! 📱\n\nTem Instagram ou outras redes?",
                'extracted_fields' => ['whatsapp' => $phone],
                'suggestions' => [],
                'achievements' => [],
                'skip_ai' => true
            ];
        }
    }
    
    // =============================================
    // STEP CONTACT - Extrações simples
    // =============================================
    if ($step === 'contact') {
        // WhatsApp
        if (preg_match('/(\d{2})[\s\-\.]?(\d{4,5})[\s\-\.]?(\d{4})/', $message, $matches)) {
            $phone = $matches[1] . $matches[2] . $matches[3];
            return [
                'success' => true,
                'assistant_message' => "Perfeito! WhatsApp anotado: **{$phone}** 📱\n\nE as redes sociais? Tem Instagram?",
                'extracted_fields' => ['whatsapp' => $phone],
                'suggestions' => [],
                'achievements' => [],
                'skip_ai' => true
            ];
        }
        
        // Instagram
        if (preg_match('/@?([a-zA-Z0-9_\.]{3,30})/', $message) && preg_match('/(instagram|insta|ig)/i', $messageLower)) {
            preg_match('/@?([a-zA-Z0-9_\.]{3,30})/', $message, $matches);
            $instagram = $matches[1];
            return [
                'success' => true,
                'assistant_message' => "Anotado! Instagram: **@{$instagram}** 📸\n\nTem Facebook ou LinkedIn?",
                'extracted_fields' => ['instagram' => '@' . $instagram],
                'suggestions' => [],
                'achievements' => [],
                'skip_ai' => true
            ];
        }
        
        // Não tem endereço físico
        if (preg_match('/(não tenho|nao tenho|sem endereço|só online|trabalho de casa|home office|remoto)/i', $messageLower)) {
            return [
                'success' => true,
                'assistant_message' => "Entendido! Trabalho online, sem endereço físico. 🏠💻\n\nVamos para o próximo passo?",
                'extracted_fields' => ['hasPhysicalLocation' => false],
                'suggestions' => [],
                'achievements' => [],
                'skip_ai' => true
            ];
        }
    }
    
    // =============================================
    // CORES (respostas simples)
    // =============================================
    $cores = [
        'azul' => '#2563eb',
        'azul escuro' => '#1e40af',
        'azul claro' => '#3b82f6',
        'vermelho' => '#dc2626',
        'verde' => '#16a34a',
        'verde escuro' => '#15803d',
        'amarelo' => '#eab308',
        'laranja' => '#ea580c',
        'roxo' => '#7c3aed',
        'rosa' => '#db2777',
        'preto' => '#000000',
        'branco' => '#ffffff',
        'cinza' => '#6b7280',
        'dourado' => '#d4af37',
        'prata' => '#c0c0c0',
        'marrom' => '#78350f',
        'bege' => '#f5f5dc'
    ];
    
    foreach ($cores as $nome => $hex) {
        if (preg_match('/\b' . preg_quote($nome) . '\b/i', $messageLower)) {
            $field = empty($formData['primaryColor']) ? 'primaryColor' : 'secondaryColor';
            $label = $field === 'primaryColor' ? 'principal' : 'secundária';
            
            return [
                'success' => true,
                'assistant_message' => "Cor $label definida: **$nome** 🎨\n\n" . 
                    ($field === 'primaryColor' ? "E qual seria a cor secundária?" : "Perfeito! Vamos continuar?"),
                'extracted_fields' => [$field => $hex],
                'suggestions' => [],
                'achievements' => [],
                'skip_ai' => true
            ];
        }
    }
    
    // Não conseguiu resolver localmente
    return null;
}
