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
    
    // DEBUG: Log detalhado do formData recebido
    error_log("📥 [Request] Step: $currentStep | User: " . substr($userMessage, 0, 50));
    error_log("📥 [Request] FormData keys: " . json_encode(array_keys($currentFormData)));
    error_log("📥 [Request] companyName: " . ($currentFormData['companyName'] ?? 'VAZIO'));
    error_log("📥 [Request] businessType: " . ($currentFormData['businessType'] ?? 'VAZIO'));
    error_log("📥 [Request] Frase atual: " . ($currentFormData['frase'] ?? 'VAZIO'));
    error_log("📥 [Request] Cores: " . ($currentFormData['primaryColor'] ?? 'VAZIO') . ' / ' . ($currentFormData['secondaryColor'] ?? 'VAZIO'));
    error_log("📥 [Request] Logo: " . (isset($currentFormData['logo']) && !empty($currentFormData['logo']) ? 'SIM (base64)' : 'NAO'));
    error_log("📥 [Request] hasNoLogo: " . ($currentFormData['hasNoLogo'] ?? 'false'));
    
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
    
    // Chamar API do Gemini (passa step e formData para validação)
    $response = callGeminiAPI($apiKey, $systemPrompt, $conversationHistory, $userMessage, $step, $formData);
    
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
    
    // Cores default que NÃO devem ser consideradas como preenchidas
    $defaultColors = ['#0066CC', '#28A745'];
    
    // Campos que contêm dados binários/base64 - NÃO enviar conteúdo ao Gemini
    $binaryFields = ['logo', 'images', 'photos', 'avatar'];
    
    // Filtrar apenas campos do step atual (economia de tokens)
    $filledFields = [];
    $missingFields = [];
    
    foreach ($stepFields as $field) {
        $value = $formData[$field] ?? null;
        
        // Verificar se é campo de cor com valor default
        if (in_array($field, ['primaryColor', 'secondaryColor'])) {
            if ($value === null || in_array(strtoupper($value), array_map('strtoupper', $defaultColors))) {
                $missingFields[] = $field;
                continue;
            }
        }
        
        if (isset($value) && !empty($value)) {
            if (is_array($value) && count($value) === 0) {
                $missingFields[] = $field;
                continue;
            }
            
            // Para campos binários (logo, imagens), apenas indicar que existe
            // NÃO enviar o conteúdo base64/URL para economizar tokens
            if (in_array($field, $binaryFields)) {
                $filledFields[$field] = '[ENVIADO]';
            } else {
                $filledFields[$field] = $value;
            }
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

REGRAS FUNDAMENTAIS (NUNCA QUEBRE):
*** VOCE NUNCA PODE PULAR CAMPOS DO STEP ATUAL! ***
*** VOCE NUNCA PODE PERGUNTAR CAMPOS DE OUTRO STEP! ***
- SEMPRE verifique quais campos do step atual FALTAM antes de perguntar
- NUNCA pergunte WhatsApp se ainda falta cores/logo no step identity
- NUNCA avance para o proximo step sem completar o atual
- Toda resposta DEVE terminar com uma pergunta clara sobre o PROXIMO campo DO STEP ATUAL
- Se nao souber o que perguntar, olhe {$missingFieldsList} e pergunte o primeiro que falta
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
  "suggestions": [
    {"field": "frase", "value": "Opcao 1 criativa"},
    {"field": "frase", "value": "Opcao 2 emocional"},
    {"field": "frase", "value": "Opcao 3 impactante"}
  ],
  "ui_action": null,
  "next_question": {
    "field": "proximoCampo",
    "question": "Pergunta especifica"
  },
  "achievements": [],
  "step_complete": false
}

UI_ACTION (gatilhos de interface):
- "upload_logo": Quando usuario concordar em enviar logo
- "show_color_picker": Quando for escolher cores
- null: Quando nao precisar de acao especial

SUGESTOES (Copywriting Profissional):
- Use tecnicas AIDA: Atencao, Interesse, Desejo, Acao
- Crie 3 opcoes: 1 curta/impactante (punchy), 1 emocional, 1 profissional
- NUNCA use frases genericas como "Qualidade e servico" ou "Excelencia garantida"
- Exemplos por ramo:
  * Padaria: "O pao que abraca seu dia", "Sabor de infancia em cada fatia"
  * Tech: "Codigo que transforma negocios", "Inovacao que escala"
  * Advocacia: "Seu direito, nossa missao", "Justica acessivel"
  * Saude: "Cuidar e nossa essencia", "Saude que inspira vida"
- Envie sugestoes quando: usuario pedir ajuda, resposta vaga, campo criativo (frase, bio, missao)

SUGESTOES DE CORES (OBRIGATORIO quando perguntar sobre cores):
- SEMPRE envie cores como sugestoes estruturadas no array "suggestions"
- Use PREFERENCIALMENTE as paletas Flat UI Colors (testadas em milhares de sites no mundo todo)
- Explique que essas cores foram aprovadas por designers profissionais e garantem harmonia visual
- Para cores, crie 3 paletas diferentes no formato:
  [
    {"field": "primaryColor", "value": "#0066CC"},
    {"field": "secondaryColor", "value": "#28A745"}
  ]
- Cada paleta deve ter primaryColor E secondaryColor
- PALETAS FLAT UI COLORS RECOMENDADAS (use estas preferencialmente):
  * Profissional/Tech: #3498DB (azul sereno) + #2ECC71 (verde energia)
  * Confianca/Saude: #1ABC9C (turquesa) + #16A085 (verde-mar)
  * Energia/Criativo: #E74C3C (vermelho vibrante) + #C0392B (carmesim)
  * Sofisticado/Elegante: #34495E (cinza-azulado) + #E67E22 (laranja)
  * Moderno/Inovador: #9B59B6 (roxo) + #8E44AD (purpura)
  * Amigavel/Acolhedor: #F39C12 (amarelo ouro) + #E67E22 (laranja)
  * Seguranca/Juridico: #2C3E50 (azul marinho) + #3498DB (azul claro)
  * Luxo/Premium: #8E44AD (purpura) + #2C3E50 (marinho escuro)
- ABORDAGEM CONSULTIVA: Explique que essas cores foram testadas em sites ao redor do mundo
- Exemplo de resposta com cores:
  "assistant_message": "Otimo! Para transmitir profissionalismo e confianca, vou recomendar uma paleta que e sucesso em sites do mundo todo: **#3498DB** (azul sereno) como cor principal e **#2ECC71** (verde energia) como secundaria. Essas cores da paleta Flat UI foram testadas por designers e garantem harmonia visual perfeita. Que tal?",
  "suggestions": [
    {"field": "primaryColor", "value": "#3498DB"},
    {"field": "secondaryColor", "value": "#2ECC71"},
    {"field": "primaryColor", "value": "#1ABC9C"},
    {"field": "secondaryColor", "value": "#16A085"},
    {"field": "primaryColor", "value": "#34495E"},
    {"field": "secondaryColor", "value": "#E67E22"}
  ]
- Use ui_action: "show_color_picker" quando perguntar sobre cores

IMPORTANTE:
- assistant_message DEVE ter confirmacao + pergunta na mesma mensagem
- Se nao extrair campo, extracted_fields = {}
- Nao invente dados que o usuario nao forneceu
- next_question SEMPRE deve estar preenchido (exceto se step_complete = true)
- suggestions so envia quando usuario pedir ajuda ou der resposta muito vaga
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
3. Se tem frase mas nao tem cores: Pergunte cores com abordagem consultiva
4. Se tem cores mas nao perguntou sobre logo: Ofereca opcao de enviar logo
5. Se tudo preenchido: Avance para proximo step

EXEMPLO DE FLUXO:
User: Unli Games, desenvolvimento de games
You: Entendi! **Unli Games** e o ramo e **desenvolvimento de games**. Agora me conta, qual e a frase ou slogan que representa a empresa?

User: A revolucao do mundo dos games no Brasil
You: Que frase impactante! Vou usar **Unli Games: A revolucao do mundo dos games no Brasil**. 

Agora vamos escolher as cores que vao transmitir a identidade visual da empresa. Como consultor de design, recomendo paletas da **Flat UI Colors** - cores testadas e aprovadas em milhares de sites profissionais ao redor do mundo.

Para games e tecnologia, sugiro:
🎨 **Paleta 1 (Inovador)**: #9B59B6 (roxo vibrante) + #2ECC71 (verde energia)
🎨 **Paleta 2 (Moderno)**: #3498DB (azul sereno) + #E74C3C (vermelho impacto)
🎨 **Paleta 3 (Eletrizante)**: #E67E22 (laranja) + #8E44AD (purpura)

Qual dessas paletas combina mais com a Unli Games? Ou prefere personalizar?

PERGUNTA SOBRE LOGO (apos cores definidas):
- SEMPRE inclua ui_action: "upload_logo" quando perguntar sobre logo
- Pergunte de forma consultiva: "Voce ja tem um logo para a [EMPRESA]? Se tiver, pode enviar agora! Caso ainda nao tenha, sem problemas - podemos criar um visual moderno usando o **nome estilizado** da empresa. Muitos sites profissionais usam essa abordagem clean. Qual prefere por enquanto?"
- NAO pergunte simplesmente "tem logo ou nao" - sempre ofereca a alternativa do nome estilizado como opcao valida e bonita

IMPORTANTE: 
- SEMPRE pergunte o PROXIMO campo na mesma mensagem da confirmacao!
- Ao sugerir cores, use tom de consultoria profissional
- Mencione que são paletas testadas mundialmente (Flat UI Colors)
- Envie sempre 3 paletas como sugestões estruturadas
- Logo: sempre mostre que nome estilizado é uma opção válida e elegante
- NUNCA extraia o campo "logo" - o upload de logo é feito pelo frontend automaticamente
- Apenas extraia hasNoLogo: true quando usuario disser que prefere nome estilizado/nao tem logo

Extraia: companyName, businessType, frase, primaryColor, secondaryColor, voiceTone, hasNoLogo.
Cores Flat UI: Tech/Games:#3498DB+#2ECC71 ou #9B59B6+#8E44AD, Saude:#1ABC9C+#16A085, Profissional:#34495E+#E67E22, Energia:#E74C3C+#C0392B, Premium:#8E44AD+#2C3E50, Juridico:#2C3E50+#3498DB, Acolhedor:#F39C12+#E67E22.
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
 * Limita a 10 interações (20 mensagens) para evitar estouro de tokens
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
    
    // Limitar janela de contexto: pegar apenas as últimas 20 mensagens
    // (10 interações usuário-bot) para não estourar limite de tokens
    if (count($history) > 20) {
        $history = array_slice($history, -20);
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
    string $userMessage,
    string $step = 'identity',
    array $formData = []
): array {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";
    
    // Adicionar mensagem atual ao histórico
    $conversationHistory[] = [
        'role' => 'user',
        'parts' => [['text' => $userMessage]]
    ];
    
    // Temperature mais alta para steps criativos (frase, bio, serviços)
    $isCreativeStep = in_array($step, ['identity', 'services', 'about']);
    $temperature = $isCreativeStep ? 0.9 : 0.7;
    
    $payload = [
        'contents' => $conversationHistory,
        'systemInstruction' => [
            'parts' => [['text' => $systemPrompt]]
        ],
        'generationConfig' => [
            'temperature' => $temperature,
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
    
    // Limpar markdown blocks que o Gemini às vezes adiciona
    // Remove ```json e ``` no início/fim
    $generatedText = preg_replace('/^```json\s*|\s*```$/s', '', trim($generatedText));
    $generatedText = preg_replace('/^```\s*|\s*```$/s', '', trim($generatedText));
    
    // Parse do JSON gerado
    $result = json_decode($generatedText, true);
    
    // DEBUG: Log do que o Gemini retornou
    error_log("🤖 [Gemini Response] Raw text: " . substr($generatedText, 0, 200));
    if ($result && isset($result['suggestions'])) {
        error_log("🔍 [Gemini Response] Suggestions: " . json_encode($result['suggestions']));
    }
    
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
    
    // Processar ui_action e converter em actions para o Vue
    // IMPORTANTE: Só adiciona actions se faz sentido no contexto atual
    $actions = null;
    if (isset($result['ui_action'])) {
        switch ($result['ui_action']) {
            case 'upload_logo':
                // Só mostra botão de upload se não tem logo ainda
                $hasLogo = !empty($formData['logo']);
                $hasNoLogo = !empty($formData['hasNoLogo']);
                if (!$hasLogo && !$hasNoLogo) {
                    $actions = [[
                        'id' => 'upload_logo',
                        'label' => '📁 Carregar Logo',
                        'type' => 'trigger_upload',
                        'variant' => 'primary'
                    ]];
                }
                break;
            case 'show_color_picker':
                // Só mostra botão de cores se não tem cores definidas
                $hasColors = !empty($formData['primaryColor']) && !empty($formData['secondaryColor']);
                if (!$hasColors) {
                    $actions = [[
                        'id' => 'color_picker',
                        'label' => '🎨 Escolher Cores',
                        'type' => 'show_color_picker',
                        'variant' => 'secondary'
                    ]];
                }
                break;
        }
    }
    
    // VALIDAÇÃO CRÍTICA: Verificar se a IA está respeitando o step atual
    // Previne que pergunte campos de outros steps OU que fique repetindo o mesmo campo
    if (isset($result['next_question']['field'])) {
        $nextField = $result['next_question']['field'];
        $currentStepFields = getStepFields($step);
        
        // Função auxiliar para verificar se campo está preenchido
        // Considera logo/hasNoLogo como um par (se um estiver preenchido, ambos contam)
        $isFieldFilled = function($field) use ($formData) {
            // Para logo: considera preenchido se tem logo OU se marcou hasNoLogo
            if ($field === 'logo' || $field === 'hasNoLogo') {
                return !empty($formData['logo']) || !empty($formData['hasNoLogo']);
            }
            return !empty($formData[$field]) && $formData[$field] !== '' && $formData[$field] !== null;
        };
        
        // Verificação 1: Campo pertence a outro step?
        if (!in_array($nextField, $currentStepFields)) {
            error_log("⚠️ [Step Validation] IA tentou perguntar '$nextField' no step '$step' (inválido!)");
            
            // Encontrar primeiro campo que falta no step atual
            $foundReplacement = false;
            foreach ($currentStepFields as $field) {
                if (!$isFieldFilled($field)) {
                    error_log("✅ [Step Validation] Corrigido para perguntar '$field' (step correto)");
                    $result['next_question']['field'] = $field;
                    $foundReplacement = true;
                    
                    // Reescrever a mensagem para perguntar o campo correto
                    if ($field === 'primaryColor' || $field === 'secondaryColor') {
                        $result['assistant_message'] = $result['assistant_message'] . "\n\nE as cores da sua marca? Tem preferência ou quer sugestões?";
                    } elseif ($field === 'logo' || $field === 'hasNoLogo') {
                        $result['assistant_message'] = $result['assistant_message'] . "\n\nVocê tem um logo para enviar? Se tiver, clique no 📎 para anexar.";
                    }
                    break;
                }
            }
            
            // Se não encontrou campo vazio, step está completo
            if (!$foundReplacement) {
                error_log("✅ [Step Validation] Nenhum campo vazio encontrado - step completo!");
                $result['step_complete'] = true;
                $result['next_question'] = null;
            }
        }
        
        // Verificação 2: Está tentando perguntar um campo que JÁ FOI PREENCHIDO?
        elseif ($isFieldFilled($nextField)) {
            error_log("⚠️ [Step Validation] IA tentou perguntar '$nextField' que JÁ está preenchido!");
            error_log("⚠️ [Step Validation] Valor atual de $nextField: " . json_encode($formData[$nextField] ?? 'N/A'));
            
            // Buscar PRÓXIMO campo que falta (na ordem do step)
            $foundNext = false;
            foreach ($currentStepFields as $field) {
                if (!$isFieldFilled($field)) {
                    error_log("✅ [Step Validation] Corrigido para perguntar '$field' (próximo campo vazio)");
                    $result['next_question']['field'] = $field;
                    $foundNext = true;
                    
                    // Reescrever a mensagem
                    if ($field === 'primaryColor' || $field === 'secondaryColor') {
                        $result['assistant_message'] = "Perfeito! Agora vamos definir as cores da sua marca. Tem alguma preferência ou quer que eu sugira baseado no ramo?";
                        $result['suggestions'] = []; // Limpar sugestões antigas
                    } elseif ($field === 'logo' || $field === 'hasNoLogo') {
                        $result['assistant_message'] = "Ótimo! Você tem um logo para enviar? Se tiver, pode clicar no 📎 abaixo.";
                        $result['suggestions'] = [];
                        $result['ui_action'] = 'upload_logo';
                    } elseif ($field === 'voiceTone') {
                        $result['assistant_message'] = "Legal! Qual tom de voz prefere para o site? Profissional, amigável ou descontraído?";
                        $result['suggestions'] = [];
                    }
                    break;
                }
            }
            
            // Se todos os campos do step estão preenchidos, marcar como completo
            if (!$foundNext) {
                error_log("✅ [Step Validation] Todos os campos do step '$step' estão preenchidos!");
                $result['step_complete'] = true;
                $result['next_question'] = null;
            }
        }
    }
    
    // Sanitizar extracted_fields - remover campos binários/base64 que não devem vir da IA
    // (logo é gerenciado pelo frontend via upload direto)
    $binaryFieldsToRemove = ['logo', 'images', 'photos', 'avatar'];
    $extractedFields = $result['extracted_fields'] ?? [];
    
    foreach ($binaryFieldsToRemove as $fieldToRemove) {
        if (isset($extractedFields[$fieldToRemove])) {
            $value = $extractedFields[$fieldToRemove];
            // Remover se for base64 ou URL longa (dados binários não devem vir da IA)
            if (is_string($value) && (strpos($value, 'data:') === 0 || strlen($value) > 500)) {
                unset($extractedFields[$fieldToRemove]);
                error_log("🧹 [Sanitize] Removido campo binário '$fieldToRemove' dos extracted_fields");
            }
        }
    }
    
    // Sanitizar assistant_message - remover qualquer JSON ou base64 que a IA possa ter incluído
    $assistantMessage = $result['assistant_message'] ?? $generatedText;
    
    // Se a mensagem parece ser JSON puro (começa com { ou [), extrair só o texto
    if (preg_match('/^\s*[\{\[]/', $assistantMessage)) {
        // Tentar extrair assistant_message de dentro do JSON
        $innerParsed = json_decode($assistantMessage, true);
        if ($innerParsed && isset($innerParsed['assistant_message'])) {
            $assistantMessage = $innerParsed['assistant_message'];
            error_log("🧹 [Sanitize] Extraído assistant_message de JSON aninhado");
        }
    }
    
    // Remover qualquer ocorrência de data:image/... base64 da mensagem
    $assistantMessage = preg_replace('/data:image\/[^;]+;base64,[a-zA-Z0-9+\/=]+/i', '[imagem recebida]', $assistantMessage);
    
    // Garantir estrutura completa
    return [
        'success' => true,
        'assistant_message' => $assistantMessage,
        'extracted_fields' => $extractedFields,
        'suggestions' => $result['suggestions'] ?? [],
        'actions' => $actions,
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
    // DETECÇÃO: USUÁRIO DIZ QUE FINALIZOU O STEP
    // Se o usuário diz "finalizei", "terminei", "concluí", etc.
    // =============================================
    if (preg_match('/(finalizei|terminei|conclu[ií]|completei|pronto|acabei|tudo (certo|pronto|ok)|j[aá] (finalizei|terminei|preenchi)).*step|etapa|identidade|marca|contato|hist[oó]ria|servi[cç]os/i', $messageLower) ||
        preg_match('/^(finalizei|terminei|conclu[ií]|completei|pronto|acabei|j[aá] terminei|tudo certo|tudo pronto|pode avan[cç]ar|pr[oó]xim[oa])/i', $messageLower)) {
        
        // Verificar se step está realmente completo baseado nos campos
        $isComplete = isStepComplete($step, $formData);
        
        if ($isComplete) {
            // Step completo - avançar para próximo
            $nextStepName = getNextStepName($step);
            return [
                'success' => true,
                'assistant_message' => "Perfeito! ✅ O step de **" . ucfirst($step) . "** está completo!\n\nVamos para o próximo: **$nextStepName**. 🚀",
                'extracted_fields' => [],
                'suggestions' => [],
                'achievements' => ['step_' . $step . '_complete'],
                'step_complete' => true,
                'skip_ai' => true
            ];
        } else {
            // Step não está completo - informar o que falta
            $missingFields = getMissingFieldsForStep($step, $formData);
            $missingLabels = array_map('getFieldLabel', $missingFields);
            $missingList = implode(', ', $missingLabels);
            
            return [
                'success' => true,
                'assistant_message' => "Quase lá! 👀 Ainda falta preencher: **$missingList**.\n\nQuer que eu te ajude com isso?",
                'extracted_fields' => [],
                'suggestions' => [],
                'achievements' => [],
                'skip_ai' => true
            ];
        }
    }
    
    // =============================================
    // CONSCIÊNCIA DE ESTADO - O QUE O BOT PERGUNTOU?
    // Se já temos nome+ramo mas falta frase, a próxima resposta É a frase
    // =============================================
    if ($step === 'identity') {
        // CASO: Usuário está respondendo a pergunta sobre FRASE
        $hasName = !empty($formData['companyName']);
        $hasType = !empty($formData['businessType']);
        $hasFrase = !empty($formData['frase']);
        $messageLen = mb_strlen($message);
        
        // Se a mensagem parece ser uma frase (5-150 chars, não é só "sim/não")
        $isLikelyFrase = $messageLen >= 5 && $messageLen <= 150 
            && !preg_match('/^(sim|nao|ok|gostei|pode ser|legal|isso|exato|perfeito|nope|nada)$/i', $messageLower);
        
        if ($hasName && $hasType && !$hasFrase && $isLikelyFrase) {
            $frase = preg_replace('/^["\']|["\']$/u', '', $message);
            
            return [
                'success' => true,
                'assistant_message' => "Adorei a frase: **\"$frase\"** 🚀\n\nAgora vamos definir as cores da sua marca. Tem alguma preferência ou quer que eu sugira baseado no seu ramo?",
                'extracted_fields' => ['frase' => $frase],
                'suggestions' => [],
                'achievements' => [],
                'skip_ai' => true
            ];
        }
        
        // CASO: Usuário quer enviar logo
        if (preg_match('/(tenho|sim|vou|quero|pode|manda|enviar|upload).*(logo|imagem|arquivo)/i', $messageLower)) {
            return [
                'success' => true,
                'assistant_message' => "Perfeito! Clique no botão abaixo para enviar seu logo. 📁",
                'extracted_fields' => [],
                'suggestions' => [],
                'achievements' => [],
                'actions' => [
                    [
                        'id' => 'upload_logo',
                        'label' => '📁 Carregar Logo',
                        'type' => 'trigger_upload',
                        'variant' => 'primary'
                    ]
                ],
                'skip_ai' => true
            ];
        }
    }
    
    // =============================================
    // CONFIRMAÇÕES SIMPLES - NÃO TRAVAR O FLUXO
    // =============================================
    // NÃO INTERCEPTAR: sim, ok, gostei, etc. - deixar IA decidir próximo passo
    
    // =============================================
    // FRASES PERSONALIZADAS (quando o cliente escreve manualmente)
    // =============================================
    if (preg_match('/^(minha frase|quero escrever|a frase|usar essa frase)/i', $messageLower)) {
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

/**
 * Verifica se um step está completo baseado nos campos obrigatórios
 */
function isStepComplete(string $step, array $formData): bool {
    // Campos OBRIGATÓRIOS por step (outros são opcionais)
    $requiredFields = [
        'identity' => ['companyName', 'businessType'],
        'contact' => ['whatsapp'],
        'about' => ['companyBio'],
        'services' => ['services'],
        'faq' => ['faqItems'],
        'finalization' => []
    ];
    
    // Para identity: logo OU hasNoLogo deve estar preenchido para considerar completo
    // Cores também são importantes mas não bloqueiam
    $required = $requiredFields[$step] ?? [];
    
    foreach ($required as $field) {
        if (empty($formData[$field])) {
            return false;
        }
    }
    
    // Para identity, verificar se tem logo OU hasNoLogo
    if ($step === 'identity') {
        $hasLogo = !empty($formData['logo']);
        $hasNoLogo = !empty($formData['hasNoLogo']);
        $hasColors = !empty($formData['primaryColor']) && !empty($formData['secondaryColor']);
        
        // Se não tem nem logo nem marcou "sem logo", não está completo
        // MAS se tem cores definidas, é mais flexível
        if (!$hasLogo && !$hasNoLogo && $hasColors) {
            // Tem cores mas não definiu logo - perguntar sobre logo
            return false;
        }
    }
    
    return true;
}

/**
 * Retorna campos faltantes para um step
 */
function getMissingFieldsForStep(string $step, array $formData): array {
    $requiredFields = [
        'identity' => ['companyName', 'businessType', 'primaryColor', 'secondaryColor'],
        'contact' => ['whatsapp'],
        'about' => ['companyBio'],
        'services' => ['services'],
        'faq' => ['faqItems'],
        'finalization' => []
    ];
    
    $missing = [];
    $required = $requiredFields[$step] ?? [];
    
    foreach ($required as $field) {
        if (empty($formData[$field])) {
            $missing[] = $field;
        }
    }
    
    // Para identity, verificar logo também
    if ($step === 'identity') {
        $hasLogo = !empty($formData['logo']);
        $hasNoLogo = !empty($formData['hasNoLogo']);
        if (!$hasLogo && !$hasNoLogo) {
            $missing[] = 'logo';
        }
    }
    
    return $missing;
}

/**
 * Retorna label amigável para um campo
 */
function getFieldLabel(string $field): string {
    $labels = [
        'companyName' => 'Nome da empresa',
        'businessType' => 'Ramo de atuação',
        'frase' => 'Frase/slogan',
        'primaryColor' => 'Cor principal',
        'secondaryColor' => 'Cor secundária',
        'logo' => 'Logo',
        'voiceTone' => 'Tom de voz',
        'whatsapp' => 'WhatsApp',
        'companyBio' => 'História da empresa',
        'services' => 'Serviços',
        'faqItems' => 'Perguntas frequentes'
    ];
    
    return $labels[$field] ?? $field;
}

/**
 * Retorna nome do próximo step
 */
function getNextStepName(string $currentStep): string {
    $steps = [
        'identity' => 'Contato',
        'contact' => 'História da Empresa',
        'about' => 'Serviços',
        'services' => 'FAQ',
        'faq' => 'Finalização',
        'finalization' => 'Revisão Final'
    ];
    
    return $steps[$currentStep] ?? 'Próximo passo';
}
