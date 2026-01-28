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

if ($_SESSION[$rateKey]['count'] > 20) { // 20 mensagens por minuto
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

// Sanitizar e limitar a 1000 caracteres (máximo para textos elaborados)
$userMessage = strip_tags($userMessage);
$userMessage = mb_substr($userMessage, 0, 1000);

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
    $stepFields = getStepFields($step);
    
    // Filtrar apenas campos do step atual (economia de tokens)
    $filledFields = [];
    $missingFields = [];
    
    foreach ($stepFields as $field) {
        if (isset($formData[$field]) && !empty($formData[$field])) {
            if (is_array($formData[$field]) && count($formData[$field]) === 0) {
                $missingFields[] = $field;
                continue;
            }
            $filledFields[$field] = $formData[$field];
        } else {
            $missingFields[] = $field;
        }
    }
    
    $filledFieldsJson = json_encode($filledFields, JSON_UNESCAPED_UNICODE);
    $stepFieldsList = implode(', ', $stepFields);
    $missingFieldsList = implode(', ', $missingFields);
    
    $basePrompt = <<<PROMPT
Voce e **Jules**, assistente de criacao de sites da Unli. Seja simpatico, conciso e profissional. Use emojis ocasionalmente.

CONTEXTO:
- Step: {$step}
- Tom: {$voiceTone}
- Preenchidos: {$filledFieldsJson}
- FALTAM: {$missingFieldsList}
- Campos do step: {$stepFieldsList}

REGRA FUNDAMENTAL (NUNCA QUEBRE):
*** VOCE NUNCA PODE PARAR SEM FAZER UMA PERGUNTA! ***
- Toda resposta DEVE terminar com uma pergunta clara sobre o proximo campo
- NUNCA termine com "O que mais?" ou "Pode me contar mais?"
- SEMPRE seja ESPECIFICO sobre qual informacao voce quer
- Se nao souber o que perguntar, olhe os campos que FALTAM e pergunte o proximo
- A conversa so para quando step_complete = true

TAREFA:
1. Extrair dados e mapear para campos
2. Confirmar de forma amigavel EM UMA MENSAGEM
3. NA MESMA MENSAGEM, perguntar o PROXIMO campo especifico
4. Sugerir melhorias quando aplicavel
5. Guiar o usuario passo a passo - ele nao sabe o que vem depois

EXTRACAO:
- businessType: alimentacao, saude, beleza, educacao, tecnologia, construcao, moda, servicos, juridico, eventos, turismo, imoveis, automotivo, pets, comercio, industria, outro
- voiceTone: profissional, amigavel, descontraido, luxuoso, tecnico, inspirador
- Telefones: so numeros
- Cores: nomes ou hex (#0066CC)
- Anos: 1900-2026

FORMATO DE RESPOSTA (JSON OBRIGATORIO):
{
  "success": true,
  "assistant_message": "Entendi! [confirmacao curta] **campo extraido**. Agora, [pergunta especifica sobre proximo campo]?",
  "extracted_fields": {
    "campo1": "valor1"
  },
  "suggestions": [],
  "next_question": {
    "field": "proximoCampo",
    "question": "Pergunta especifica"
  },
  "achievements": [],
  "step_complete": false
}

IMPORTANTE:
- assistant_message DEVE ter confirmacao + pergunta na mesma mensagem
- Se nao extrair campo, extracted_fields = {}
- Nao invente dados que o usuario nao forneceu
- next_question SEMPRE deve estar preenchido (exceto se step_complete = true)
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
 * Instruções específicas por step (versão compacta)
 */
function getStepInstructions(string $step): string {
    $identityInstructions = <<<'EOT'
Step: Identidade. 

SEQUENCIA OBRIGATORIA (depois de cada resposta do usuario, va para o proximo):
1. Se nao tem companyName: Pergunte nome + ramo juntos
2. Se tem companyName mas nao tem frase: Pergunte sobre frase/slogan
3. Se tem frase mas nao tem cores: Pergunte cores (sugira baseado no ramo)
4. Se tem cores mas nao perguntou sobre logo: Ofereca opcao de enviar logo
5. Se tudo preenchido: Avance para proximo step

EXEMPLO DE FLUXO:
User: Unli Games, desenvolvimento de games
You: Entendi! **Unli Games** e o ramo e **desenvolvimento de games**. Agora me conta, qual e a frase ou slogan que representa a empresa?

User: A revolucao do mundo dos games no Brasil
You: Que frase impactante! Vou usar **Unli Games: A revolucao do mundo dos games no Brasil**. E as cores? Que tal #0066CC (azul tech) e #6F42C1 (roxo vibrante) para transmitir inovacao?

IMPORTANTE: SEMPRE pergunte o PROXIMO campo na mesma mensagem da confirmacao!

Extraia: companyName, businessType, frase, primaryColor, secondaryColor, voiceTone, logo, hasNoLogo.
Cores sugeridas: Saude:#28A745+#17A2B8, Tech:#0066CC+#6F42C1, Alimentos:#FF6B35+#FFC107, Beleza:#E83E8C+#6F42C1, Juridico:#212529+#0066CC.
Conquista: companyName+businessType+frase = identity_unlocked.
EOT;

    $instructions = [
        'identity' => $identityInstructions,
        
        'contact' => "Step: Contato. Extraia: whatsapp, redes sociais, endereco. Aceite @usuario ou URLs. Nao tenho loja fisica = hasPhysicalLocation:false. Se mencionar horario de atendimento, extraia para businessHours. Se der detalhes sobre localizacao (bairro, regiao), use para addressNeighborhood/addressCity mesmo sem CEP completo. Conquista: whatsapp+1 rede = connection_established.",
        
        'about' => "Step: Historia. Reformule textos desorganizados em companyBio profissional. Extraia foundingYear (anos). Identifique diferenciais para companyHighlights. Missao/visao/valores = showMissionVision:true. Se mencionar SERVICOS/PRODUTOS na historia, pergunte se quer anotar para detalhar depois. Se falar de CLIENTES/CASES, sugira adicionar como depoimentos futuros. Conquista: companyBio = story_mastered.",
        
        'services' => "Step: Servicos. Estruture: services:[{name,shortDescription}]. Crie descricoes profissionais e persuasivas. Pergunte sobre garantia. Se mencionar DUVIDAS COMUNS dos clientes sobre os servicos, sugira adicionar ao FAQ. Se der PRECOS, extraia para pricing info. Conquista: 3+ servicos = services_catalog.",
        
        'faq' => "Step: FAQ. Sugira 5-7 perguntas anti-objecao baseadas no ramo. Estruture: faqItems:[{question,answer}]. Respostas persuasivas (max 1000 chars). Se a resposta for muito tecnica, sugira uma versao mais acessivel. Se mencionar POLITICAS (devolucao, garantia, prazo), extraia para campos especificos. Conquista: 5+ FAQs = faq_strategic.",
        
        'finalization' => "Step: Final. Urgencia: urgent/normal/relaxed. Aceite URLs inspiracao. Quando completo: step_complete=true."
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
            'maxOutputTokens' => 600,
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
        
    }
    
    // =============================================
    // STEP ABOUT - Anos específicos (MUITO óbvio)
    // =============================================
    if ($step === 'about') {
        // Ano de fundação - apenas quando é SÓ o ano
        if (preg_match('/^\s*(19[5-9]\d|20[0-2]\d)\s*$/', $message, $matches)) {
            $year = (int)$matches[1];
            $yearsOld = 2026 - $year;
            return [
                'success' => true,
                'assistant_message' => "Legal! Empresa fundada em **{$year}** ({$yearsOld} anos de experiência). 🎂\n\nConte um pouco da história da empresa?",
                'extracted_fields' => ['foundingYear' => $year],
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
