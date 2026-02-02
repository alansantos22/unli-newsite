<?php
/**
 * ============================================
 * SMART ONBOARDING API
 * ============================================
 * 
 * Novo endpoint limpo para o chat com IA.
 * O Gemini é o ÚNICO cérebro - não usamos regex!
 * 
 * POST /api/ai/smart-onboarding.php
 */

// PRIMEIRO: Garantir que headers básicos são enviados IMEDIATAMENTE
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// DEBUG: Teste rápido para ver se chegamos aqui
// Se você receber esse response, o PHP está funcionando
// Remover depois de testar
if (isset($_GET['test'])) {
    echo json_encode(['success' => true, 'message' => 'PHP funcionando!']);
    exit;
}

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// DEFINIR FUNÇÕES UTILITÁRIAS PRIMEIRO (antes de qualquer uso!)
function jsonResponse($success, $error = null, $data = null) {
    $response = ['success' => $success];
    
    if ($error) {
        $response['error'] = $error;
    }
    
    if ($data) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Error handler global para capturar TUDO
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("[Smart Onboarding PHP ERROR] {$errstr} in {$errfile}:{$errline}");
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error_log("[Smart Onboarding FATAL ERROR] {$error['message']} in {$error['file']}:{$error['line']}");
        // Headers já foram enviados no início
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Erro fatal no servidor: ' . $error['message'],
            'data' => [
                'type' => 'FATAL',
                'file' => basename($error['file']),
                'line' => $error['line']
            ]
        ]);
    }
});

// Proteção para carregar config segura
define('SECURE_CONFIG_ACCESS', true);

error_log('[Smart Onboarding] ========== NOVA REQUISIÇÃO ==========');
error_log('[Smart Onboarding] Method: ' . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'));
error_log('[Smart Onboarding] URI: ' . ($_SERVER['REQUEST_URI'] ?? 'UNKNOWN'));

// Carregar CORS lib (pode adicionar headers extras mas os básicos já foram)
require_once __DIR__ . '/../lib/cors.php';

// Carregar dependências
require_once __DIR__ . '/../config.secure.php';

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    jsonResponse(false, 'Método não permitido. Use POST.');
}

// Rate limiting
session_start();
$rateKey = 'smart_onboarding_rate_' . session_id();
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
    jsonResponse(false, 'Limite de mensagens excedido. Aguarde um momento.', [
        'retry_after' => $_SESSION[$rateKey]['reset'] - $now
    ]);
}

// Obter input
$rawInput = file_get_contents('php://input');
error_log('[Smart Onboarding] Raw input received: ' . substr($rawInput, 0, 500));

$input = json_decode($rawInput, true);

if (!$input) {
    $jsonError = json_last_error_msg();
    error_log('[Smart Onboarding ERROR] JSON decode failed: ' . $jsonError);
    http_response_code(400);
    jsonResponse(false, 'JSON inválido no body da requisição. Erro: ' . $jsonError);
}

// Validar campos
$sessionId = $input['session_id'] ?? null;
$userMessage = $input['user_message'] ?? '';
$context = $input['context'] ?? [];
$messages = $input['messages'] ?? [];
$images = $input['images'] ?? [];

if (empty(trim($userMessage)) && $userMessage !== '__STEP_START__') {
    error_log('[Smart Onboarding ERROR] Mensagem vazia. Input: ' . json_encode($input));
    http_response_code(400);
    jsonResponse(false, 'Mensagem do usuário é obrigatória.');
}

error_log('[Smart Onboarding] Processing message. IsStepStart: ' . ($userMessage === '__STEP_START__' ? 'YES' : 'NO'));

// Sanitizar
$userMessage = strip_tags($userMessage);
$userMessage = mb_substr($userMessage, 0, 2000);

// Detectar se é início de etapa (sinal especial do frontend)
$isStepStart = ($userMessage === '__STEP_START__');
if ($isStepStart) {
    $userMessage = ''; // Limpar para o Gemini saber que deve puxar o assunto
}

try {
    // Obter API Key do Gemini
    $geminiApiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : getenv('GEMINI_API_KEY');
    
    if (!$geminiApiKey) {
        throw new Exception('GEMINI_API_KEY não configurada no servidor.');
    }
    
    // Adicionar contexto do trigger ao context
    $context['triggerContext'] = $isStepStart ? 'step_start' : 'user_message';
    $context['triggerDetails'] = $context['triggerDetails'] ?? ['hour' => (int)date('H')];
    
    // Se é início de etapa, usar mensagem especial
    $messageToSend = $isStepStart 
        ? '[INÍCIO DE ETAPA - Não há mensagem do usuário. Você deve puxar o assunto e fazer a primeira pergunta.]'
        : $userMessage;
    
    error_log('[Smart Onboarding] Calling processWithGemini. Message: ' . substr($messageToSend, 0, 100));
    
    // Processar com Gemini
    $result = processWithGemini($geminiApiKey, $messageToSend, $context, $messages, $images);
    
    error_log('[Smart Onboarding] processWithGemini completed successfully');
    
    // Salvar no banco se tiver session_id
    if ($sessionId) {
        saveToDatabase($sessionId, $userMessage, $result, $context);
    }
    
    jsonResponse(true, null, $result);
    
} catch (Exception $e) {
    error_log('[Smart Onboarding ERROR] Exception caught: ' . $e->getMessage());
    error_log('[Smart Onboarding ERROR] Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    jsonResponse(false, 'Erro interno: ' . $e->getMessage(), [
        'error_type' => get_class($e),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}

// =============================================
// FUNÇÕES PRINCIPAIS
// =============================================

/**
 * Processa mensagem com o Gemini
 */
function processWithGemini($apiKey, $userMessage, $context, $previousMessages, $images) {
    error_log('[processWithGemini] Starting. User message: ' . substr($userMessage, 0, 100));
    error_log('[processWithGemini] Context step: ' . ($context['currentStep']['id'] ?? 'none'));
    error_log('[processWithGemini] Previous messages count: ' . count($previousMessages));
    
    $systemPrompt = buildSystemPrompt($context);
    error_log('[processWithGemini] System prompt built. Length: ' . strlen($systemPrompt));
    
    $conversationHistory = buildConversationHistory($previousMessages);
    error_log('[processWithGemini] Conversation history built. Entries: ' . count($conversationHistory));
    
    $result = callGeminiAPI($apiKey, $systemPrompt, $conversationHistory, $userMessage, $images);
    error_log('[processWithGemini] Gemini API returned successfully');
    
    return $result;
}

/**
 * Constrói o prompt de sistema para o Gemini
 * VERSÃO 3.0 - O CONSULTOR TOTAL (Sem Scripts Prontos!)
 * 
 * O Gemini controla 100% da conversa, desde o "Oi".
 * Nada de frases genéricas ou templates.
 */
function buildSystemPrompt($context) {
    $currentStep = $context['currentStep'] ?? null;
    $currentField = $context['currentField'] ?? null;
    $formData = $context['formData'] ?? [];
    $fieldStatus = $context['fieldStatus'] ?? [];
    $triggerContext = $context['triggerContext'] ?? 'user_message';
    $triggerDetails = $context['triggerDetails'] ?? [];
    
    // Nome da empresa (tratado para nunca mostrar "undefined")
    $companyName = !empty($formData['companyName']) ? $formData['companyName'] : '';
    $companyLabel = $companyName ?: 'seu novo negócio';
    $businessType = $formData['businessType'] ?? '';
    
    // Tipos de campo que são arquivos (não enviar para IA)
    $fileFieldTypes = ['image', 'file', 'video', 'audio', 'pdf'];
    
    // Formatar campos do step (APENAS campos de texto, nunca arquivos)
    $stepFields = [];
    if ($currentStep && isset($currentStep['fields'])) {
        foreach ($currentStep['fields'] as $field) {
            $fieldType = $field['type'] ?? 'text';
            $isFileField = in_array($fieldType, $fileFieldTypes);
            
            $stepFields[] = [
                'id' => $field['id'],
                'label' => $field['label'],
                'type' => $fieldType,
                'required' => $field['required'] ?? false,
                'status' => $fieldStatus[$field['id']] ?? 'empty',
                // Para campos de arquivo, apenas indicar se foi enviado ou não
                'currentValue' => $isFileField 
                    ? (!empty($formData[$field['id']]) ? '[arquivo enviado]' : 'vazio')
                    : formatValueForPrompt($formData[$field['id']] ?? null, $fieldType)
            ];
        }
    }
    
    // Separar campos pendentes
    $pendingFields = array_values(array_filter($stepFields, fn($f) => $f['status'] === 'empty'));
    $filledFields = array_values(array_filter($stepFields, fn($f) => $f['status'] === 'answered' || $f['status'] === 'confirmed'));
    
    $stepId = $currentStep['id'] ?? 'identity';
    $stepName = $currentStep['name'] ?? 'Identidade da Marca';
    
    // Determinar hora do dia para saudação
    $hour = (int)($triggerDetails['hour'] ?? date('H'));
    $greeting = $hour < 12 ? 'Bom dia' : ($hour < 18 ? 'Boa tarde' : 'Boa noite');
    
    // Lista de campos pendentes como string
    $pendingFieldsJson = json_encode(array_map(fn($f) => $f['id'], $pendingFields), JSON_UNESCAPED_UNICODE);
    $pendingFieldsLabels = implode(', ', array_map(fn($f) => $f['label'], array_slice($pendingFields, 0, 3)));
    
    // Primeiro campo pendente
    $firstPendingField = $pendingFields[0] ?? null;
    $firstPendingLabel = $firstPendingField['label'] ?? 'próximo dado';
    $firstPendingId = $firstPendingField['id'] ?? '';
    
    // =============================================
    // O PROMPT DO CONSULTOR TOTAL
    // =============================================
    
    $prompt = <<<PROMPT
ATUE COMO: 'O Consultor Criativo da Unli'.
Você é um especialista Sênior em Branding conversando pelo WhatsApp com um cliente que acabou de comprar um site.
Sua meta é coletar informações para o briefing, mas de forma que o cliente se sinta num brainstorming empolgante.

## CONTEXTO ATUAL
- Etapa do formulário: '{$stepName}' ({$stepId})
- Nome da Empresa: '{$companyLabel}'
- Ramo: '{$businessType}'
- Horário: {$hour}h ({$greeting})
- Campos pendentes: {$pendingFieldsJson}
- Próximo campo: '{$firstPendingId}' ({$firstPendingLabel})
- Tipo de trigger: '{$triggerContext}'

## REGRAS DE OURO (LEIA COM MUITA ATENÇÃO!)

### 1. ZERO MENSAGENS ROBÓTICAS
❌ PROIBIDO USAR:
- "Olá, sou seu assistente..."
- "Vamos preencher o formulário..."
- "Por favor, insira os dados..."
- "Analisando as informações..."
- "Com base nos dados fornecidos..."
- "Vejo que você preencheu..."

✅ EXEMPLOS PERMITIDOS:
- "E aí! Bora dar uma cara pro {$companyLabel}?"
- "Agora a parte divertida: as cores!"
- "Me conta, qual o nome da empresa?"

### 2. SE O USUÁRIO NÃO FALOU NADA (INÍCIO DE ETAPA)
Quando não há mensagem do usuário (é início de chat ou mudança de etapa), você DEVE:
1. Puxar o assunto de forma engajadora
2. Olhar para o primeiro campo pendente ('{$firstPendingLabel}')
3. Fazer uma introdução curta sobre esse tópico

EXEMPLOS BONS (para início):
- Se for o nome da empresa: "{$greeting}! Bora começar pelo mais importante: qual vai ser o nome da marca?"
- Se forem cores: "A identidade visual muda tudo! Você já imaginou quais cores vão representar {$companyLabel}?"
- Se for WhatsApp: "Agora vamos deixar fácil pros clientes te acharem! Qual o WhatsApp de atendimento?"

### 3. PERSONALIZAÇÃO EXTREMA
- Use '{$companyLabel}' nas frases naturalmente
- Se a empresa já tem nome, use: "Pensando na {$companyLabel}, que tal..."
- Se não tem nome ainda, use: "Pensando no seu negócio..." ou "na sua marca..."
- NUNCA mostre 'undefined', '{undefined}' ou variáveis vazias

### 4. NÃO SEJA UM INQUÉRITO
❌ RUIM: "Qual é o seu ramo de atuação?"
✅ BOM: "Me conta, {$companyLabel} atua em qual mercado? Assim calibro as sugestões!"

❌ RUIM: "Insira o WhatsApp:"
✅ BOM: "Qual o zap pra galera entrar em contato?"

### 5. REAÇÃO ANTES DE PERGUNTAR
Quando o usuário responder algo, faça um breve comentário positivo ANTES da próxima pergunta:
- Nome dado → "TechNova? Nome futurista, curti! 🚀 Agora me conta..."
- Ramo dado → "Cafeteria? Que delícia de mercado! 😋 E as cores..."
- Cor escolhida → "Azul transmite confiança, ótima escolha! Agora..."

### 6. SER BREVE
- Máximo 2-3 frases por mensagem
- Use emojis com moderação (1-2 por mensagem)
- Nada de textão

### 7. QUANDO USUÁRIO DISSER "NÃO SEI" OU "NÃO TENHO"
- Campos obrigatórios → Sugira algo e pergunte se concorda
- Campos opcionais → "Tranquilo, podemos pular por enquanto!"
- Nunca insista ou faça o usuário se sentir mal

PROMPT;

    // Adicionar contexto de sugestões por ramo
    $prompt .= <<<'SUGGESTIONS'

## SUGESTÕES CRIATIVAS POR RAMO (use quando apropriado)

### Slogans:
- Tecnologia: "Inovação sem limites", "O futuro começa aqui"
- Alimentação: "Sabor que marca", "Feito com amor"
- Beleza: "Sua beleza, nossa arte", "Transformação começa aqui"
- Saúde: "Cuidando de você", "Seu bem-estar é prioridade"
- Jurídico: "Seu direito, nossa missão", "Justiça com excelência"
- Educação: "Conhecimento que transforma", "Aprender é crescer"

### Paletas de Cores (sempre ofereça 2-3 opções):
- Alimentação: Laranja #E67E22 + Amarelo #F1C40F (cores que abrem apetite)
- Saúde: Turquesa #1ABC9C + Azul #3498DB (transmite cuidado)
- Tecnologia: Azul #3498DB + Cinza escuro #2C3E50 (moderno e confiável)
- Jurídico: Preto #2C3E50 + Dourado #D4AF37 (seriedade e prestígio)
- Beleza: Roxo #9B59B6 + Rosa #E91E63 (sofisticação feminina)

SUGGESTIONS;

    $prompt .= <<<'EXTRACTION'

## EXTRAÇÃO INTELIGENTE
- "Sou advogado" → businessType: "juridico"
- "meu zap é 11 99999-1234" → whatsapp: "11999991234"
- "cor azul" → primaryColor: "#3498DB"
- "@empresa no insta" → instagram: "@empresa"
- "não tenho logo" → hasNoLogo: true

EXTRACTION;

    $prompt .= <<<'JSONFORMAT'

## FORMATO DE RESPOSTA (JSON OBRIGATÓRIO)
Retorne ESTRITAMENTE este JSON (sem markdown, sem ```json):
{
  "message": "Sua fala de consultor aqui. Seja humano e termine com pergunta se houver campos pendentes.",
  "extracted": {
    "FIELD_ID": "VALOR_LIMPO"
  },
  "skipped": ["fieldId1"],
  "next_field": "proximoCampoId",
  "suggestions": [
    {"field": "frase", "value": "Sugestão aqui", "label": "Estilo", "reason": "Por que funciona"}
  ],
  "color_palettes": [
    {"primary": "#3498DB", "secondary": "#2ECC71", "name": "Profissional", "reason": "Por que combina"}
  ],
  "step_summary_ready": false,
  "confidence": 0.95,
  "next_step_hint": "O que você está perguntando/fazendo agora"
}

JSONFORMAT;

    return $prompt;
}

/**
 * Formata valor para o prompt
 * IMPORTANTE: NUNCA enviar imagens, vídeos ou arquivos binários!
 */
function formatValueForPrompt($value, $type) {
    // Tipos de arquivo que NUNCA devem ser enviados
    $fileTypes = ['image', 'file', 'video', 'audio', 'pdf'];
    if (in_array($type, $fileTypes)) {
        return '[arquivo enviado]';
    }
    
    if ($value === null || $value === '') return 'vazio';
    if ($type === 'boolean') return $value ? 'sim' : 'não';
    if (is_array($value)) return '[' . count($value) . ' itens]';
    
    // Detectar base64 ou URLs de arquivo (mesmo que type não seja 'image')
    if (is_string($value)) {
        if (strpos($value, 'data:image') === 0) return '[imagem enviada]';
        if (strpos($value, 'data:video') === 0) return '[vídeo enviado]';
        if (strpos($value, 'data:audio') === 0) return '[áudio enviado]';
        if (strpos($value, 'data:application') === 0) return '[arquivo enviado]';
        // URLs de upload
        if (preg_match('/\.(jpg|jpeg|png|gif|webp|svg|mp4|mp3|pdf)$/i', $value)) {
            return '[arquivo enviado]';
        }
    }
    
    $str = (string)$value;
    return mb_strlen($str) > 100 ? mb_substr($str, 0, 97) . '...' : $str;
}

/**
 * Monta histórico de conversa
 */
function buildConversationHistory($messages) {
    $history = [];
    
    // Pegar apenas as últimas 10 mensagens
    $recentMessages = array_slice($messages, -10);
    
    foreach ($recentMessages as $msg) {
        $role = ($msg['role'] ?? $msg['type']) === 'user' ? 'user' : 'model';
        $content = $msg['content'] ?? '';
        
        if (!empty($content)) {
            $history[] = [
                'role' => $role,
                'parts' => [['text' => $content]]
            ];
        }
    }
    
    return $history;
}

/**
 * Chama a API do Gemini
 * IMPORTANTE: NUNCA enviamos imagens/arquivos - apenas texto!
 */
function callGeminiAPI($apiKey, $systemPrompt, $conversationHistory, $userMessage, $images = []) {
    error_log('[callGeminiAPI] Starting Gemini API call');
    
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";
    
    // Adicionar mensagem atual (APENAS TEXTO, nunca imagens!)
    $userParts = [['text' => $userMessage]];
    
    // NÃO ENVIAMOS IMAGENS PARA O GEMINI!
    // Isso economiza tokens e dinheiro.
    // O Gemini não precisa ver as imagens, apenas saber que foram enviadas.
    
    $conversationHistory[] = [
        'role' => 'user',
        'parts' => $userParts
    ];
    
    error_log('[callGeminiAPI] Conversation history entries: ' . count($conversationHistory));
    
    $payload = [
        'contents' => $conversationHistory,
        'systemInstruction' => [
            'parts' => [['text' => $systemPrompt]]
        ],
        'generationConfig' => [
            'temperature' => 0.8,
            'maxOutputTokens' => 800,
            'responseMimeType' => 'application/json'
        ],
        'safetySettings' => [
            ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_ONLY_HIGH'],
            ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_ONLY_HIGH'],
            ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_ONLY_HIGH'],
            ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_ONLY_HIGH']
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
    
    error_log('[callGeminiAPI] Sending request to Gemini...');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    error_log('[callGeminiAPI] Response received. HTTP Code: ' . $httpCode);
    
    if ($error) {
        error_log('[callGeminiAPI ERROR] CURL Error: ' . $error);
        throw new Exception("Erro de conexão com Gemini: {$error}");
    }
    
    if ($httpCode !== 200) {
        error_log('[callGeminiAPI ERROR] HTTP ' . $httpCode . ': ' . substr($response, 0, 500));
        throw new Exception("Erro na API do Gemini (HTTP {$httpCode}). Detalhes: " . substr($response, 0, 200));
    }
    
    error_log('[callGeminiAPI] Response OK. Length: ' . strlen($response));
    
    $data = json_decode($response, true);
    
    if (!$data) {
        error_log('[callGeminiAPI ERROR] Failed to decode Gemini response JSON: ' . json_last_error_msg());
        error_log('[callGeminiAPI ERROR] Raw response: ' . substr($response, 0, 500));
        throw new Exception('Resposta inválida do Gemini (JSON decode failed)');
    }
    
    error_log('[callGeminiAPI] Gemini response decoded successfully');
    
    // Extrair resposta
    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    
    if (!$text) {
        error_log('[callGeminiAPI ERROR] No text in Gemini response. Data: ' . json_encode($data));
        throw new Exception("Resposta vazia do Gemini");
    }
    
    error_log('[callGeminiAPI] Extracted text from Gemini. Length: ' . strlen($text));
    error_log('[callGeminiAPI] Gemini text preview: ' . substr($text, 0, 200));
    
    // Parse JSON da resposta
    $parsed = json_decode($text, true);
    
    if (!$parsed) {
        $jsonError = json_last_error_msg();
        error_log('[callGeminiAPI ERROR] Failed to parse Gemini text as JSON: ' . $jsonError);
        error_log('[callGeminiAPI ERROR] Gemini text: ' . $text);
        
        // Se não conseguiu parsear, retornar mensagem básica
        return [
            'message' => $text,
            'extracted' => [],
            'skipped' => [],
            'next_field' => null,
            'suggestions' => [],
            'color_palettes' => [],
            'step_summary_ready' => false,
            'confidence' => 0.5
        ];
    }
    
    error_log('[callGeminiAPI] Gemini response parsed successfully as JSON');
    
    // Garantir estrutura completa
    return [
        'message' => $parsed['message'] ?? 'Desculpe, não entendi. Pode reformular?',
        'extracted' => $parsed['extracted'] ?? [],
        'skipped' => $parsed['skipped'] ?? [],
        'next_field' => $parsed['next_field'] ?? null,
        'suggestions' => $parsed['suggestions'] ?? [],
        'color_palettes' => $parsed['color_palettes'] ?? [],
        'step_summary_ready' => $parsed['step_summary_ready'] ?? false,
        'confidence' => $parsed['confidence'] ?? 0.8
    ];
}

/**
 * Salva dados no banco de dados
 */
function saveToDatabase($sessionId, $userMessage, $result, $context) {
    try {
        // Carregar config do banco
        if (!defined('DB_HOST')) {
            require_once __DIR__ . '/../db.config.php';
        }
        
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Salvar ou atualizar sessão
        $formData = $context['formData'] ?? [];
        $formDataJson = json_encode($formData, JSON_UNESCAPED_UNICODE);
        
        $stmt = $pdo->prepare("
            INSERT INTO onboarding_sessions (session_id, form_data, updated_at)
            VALUES (:session_id, :form_data, NOW())
            ON DUPLICATE KEY UPDATE 
                form_data = :form_data2,
                updated_at = NOW()
        ");
        
        $stmt->execute([
            ':session_id' => $sessionId,
            ':form_data' => $formDataJson,
            ':form_data2' => $formDataJson
        ]);
        
    } catch (Exception $e) {
        error_log("[Database Error] " . $e->getMessage());
        // Não propagar erro - salvar no banco é secundário
    }
}

// jsonResponse já foi definida no início do arquivo
