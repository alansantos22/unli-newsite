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
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    jsonResponse(false, 'JSON inválido no body da requisição.');
}

// Validar campos
$sessionId = $input['session_id'] ?? null;
$userMessage = $input['user_message'] ?? '';
$context = $input['context'] ?? [];
$messages = $input['messages'] ?? [];
$images = $input['images'] ?? [];

if (empty(trim($userMessage))) {
    http_response_code(400);
    jsonResponse(false, 'Mensagem do usuário é obrigatória.');
}

// Sanitizar
$userMessage = strip_tags($userMessage);
$userMessage = mb_substr($userMessage, 0, 2000);

try {
    // Obter API Key do Gemini
    $geminiApiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : getenv('GEMINI_API_KEY');
    
    if (!$geminiApiKey) {
        throw new Exception('GEMINI_API_KEY não configurada no servidor.');
    }
    
    // Processar com Gemini
    $result = processWithGemini($geminiApiKey, $userMessage, $context, $messages, $images);
    
    // Salvar no banco se tiver session_id
    if ($sessionId) {
        saveToDatabase($sessionId, $userMessage, $result, $context);
    }
    
    jsonResponse(true, null, $result);
    
} catch (Exception $e) {
    error_log('[Smart Onboarding ERROR] ' . $e->getMessage());
    http_response_code(500);
    jsonResponse(false, 'Erro interno: ' . $e->getMessage());
}

// =============================================
// FUNÇÕES PRINCIPAIS
// =============================================

/**
 * Processa mensagem com o Gemini
 */
function processWithGemini($apiKey, $userMessage, $context, $previousMessages, $images) {
    $systemPrompt = buildSystemPrompt($context);
    $conversationHistory = buildConversationHistory($previousMessages);
    
    return callGeminiAPI($apiKey, $systemPrompt, $conversationHistory, $userMessage, $images);
}

/**
 * Constrói o prompt de sistema para o Gemini
 */
function buildSystemPrompt($context) {
    $currentStep = $context['currentStep'] ?? null;
    $currentField = $context['currentField'] ?? null;
    $formData = $context['formData'] ?? [];
    $fieldStatus = $context['fieldStatus'] ?? [];
    $companyName = $context['companyName'] ?? '';
    $businessType = $context['businessType'] ?? '';
    
    // Formatar campos do step
    $stepFields = [];
    if ($currentStep && isset($currentStep['fields'])) {
        foreach ($currentStep['fields'] as $field) {
            $stepFields[] = [
                'id' => $field['id'],
                'label' => $field['label'],
                'type' => $field['type'],
                'required' => $field['required'] ?? false,
                'status' => $fieldStatus[$field['id']] ?? 'empty',
                'currentValue' => formatValueForPrompt($formData[$field['id']] ?? null, $field['type'])
            ];
        }
    }
    
    // Separar campos preenchidos e pendentes
    $filledFields = array_filter($stepFields, fn($f) => $f['status'] === 'answered' || $f['status'] === 'confirmed');
    $pendingFields = array_filter($stepFields, fn($f) => $f['status'] === 'empty');
    
    $stepId = $currentStep['id'] ?? 'identity';
    $stepName = $currentStep['name'] ?? 'Identidade da Marca';
    $currentFieldId = $currentField['id'] ?? 'companyName';
    
    $prompt = <<<PROMPT
Você é o **Assistente Unli**, um consultor especializado em criação de sites. Você guia o usuário como em uma consultoria de verdade, explicando decisões de forma simples sem usar termos técnicos. Seja amigável, acolhedor e profissional. Use emojis com moderação.

## SUA PERSONALIDADE
- Você é um consultor, não apenas um assistente
- Explique o "porquê" das coisas quando apropriado (ex: "Cores quentes como laranja transmitem energia e são ótimas para alimentação")
- Dê dicas práticas baseadas em experiência de mercado
- Valorize o que o usuário compartilha
- Nunca use jargões técnicos como "UX", "UI", "conversão" - fale de forma simples
- **IMPORTANTE**: Quando der sugestões, SEMPRE explique o raciocínio por trás delas. O cliente precisa entender POR QUE você está sugerindo aquilo.

## CONTEXTO ATUAL
- **Step**: {$stepId} ({$stepName})
- **Empresa**: {$companyName}
- **Ramo**: {$businessType}
- **Campo sendo perguntado**: {$currentFieldId}

## CAMPOS DO STEP ({$stepId})
PROMPT;

    $prompt .= "\n```json\n" . json_encode($stepFields, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n```\n";
    
    $prompt .= "\n## CAMPOS JÁ PREENCHIDOS\n";
    if (count($filledFields) > 0) {
        foreach ($filledFields as $f) {
            $prompt .= "- {$f['label']}: {$f['currentValue']}\n";
        }
    } else {
        $prompt .= "Nenhum\n";
    }
    
    $prompt .= "\n## CAMPOS PENDENTES (ainda vazios)\n";
    if (count($pendingFields) > 0) {
        foreach ($pendingFields as $f) {
            $req = $f['required'] ? 'OBRIGATÓRIO' : 'opcional';
            $prompt .= "- {$f['id']} ({$f['label']}) [{$req}]\n";
        }
    } else {
        $prompt .= "Nenhum - step completo!\n";
    }
    
    $prompt .= <<<'INSTRUCTIONS'

## SUAS RESPONSABILIDADES
1. **Extrair dados** da mensagem do usuário e mapear para os campos corretos
2. **Confirmar** o que você entendeu de forma amigável
3. **Perguntar** o próximo campo pendente (se houver)
4. **Sugerir** opções quando apropriado (cores, frases, etc.)
5. **Detectar** quando usuário diz que não tem/não quer algo

## REGRAS IMPORTANTES
- NUNCA invente dados que o usuário não forneceu
- NUNCA pule campos obrigatórios
- NUNCA pergunte algo que já foi respondido (verifique o status)
- Se usuário disser "não tenho", "não sei", "pular" → marque como skipped
- Se não conseguir extrair nada útil, peça esclarecimento educadamente
- Redes sociais: aceite @usuario OU URLs completas, normalize para @usuario
- Telefones: extraia apenas números, aceite formatos brasileiros
- Cores: aceite nomes ("azul") ou hex ("#0066CC"), converta nomes para hex
- Anos: aceite apenas 1900-2026
- CEP: quando receber CEP, indique que o frontend deve buscar os dados

## MAPEAMENTO DE CORES (FLAT UI COLORS)
Use APENAS estas cores validadas pelo mercado de design:

### Cores Quentes
- vermelho → #E74C3C (Alizarin)
- laranja → #E67E22 (Carrot)
- amarelo → #F1C40F (Sun Flower)
- abóbora → #D35400 (Pumpkin)
- romã → #C0392B (Pomegranate)

### Cores Frias
- azul → #3498DB (Peter River)
- azul escuro → #2980B9 (Belize Hole)
- turquesa → #1ABC9C (Turquoise)
- verde mar → #16A085 (Green Sea)
- verde → #2ECC71 (Emerald)
- verde escuro → #27AE60 (Nephritis)

### Cores Neutras
- roxo → #9B59B6 (Amethyst)
- roxo escuro → #8E44AD (Wisteria)
- cinza escuro → #34495E (Wet Asphalt)
- preto → #2C3E50 (Midnight Blue)
- branco → #ECF0F1 (Clouds)
- prata → #BDC3C7 (Silver)
- cinza → #95A5A6 (Concrete)

## FORMATO DE RESPOSTA (JSON OBRIGATÓRIO)
```json
{
  "message": "Sua resposta amigável aqui (com confirmação + próxima pergunta + EXPLICAÇÃO do porquê das sugestões)",
  "extracted": {
    "fieldId": "valor extraído"
  },
  "skipped": ["fieldId1", "fieldId2"],
  "next_field": "proximoCampoId",
  "suggestions": [
    {"field": "frase", "value": "Sugestão 1", "label": "Impactante", "reason": "Explique AQUI por que essa frase funciona bem"}
  ],
  "color_palettes": [
    {"primary": "#3498DB", "secondary": "#2ECC71", "name": "Profissional", "reason": "Explique AQUI por que essa combinação é boa"}
  ],
  "step_summary_ready": false,
  "confidence": 0.95
}
```

## EXPLICAÇÃO DOS CAMPOS DE RESPOSTA
- **message**: Sua resposta em texto (confirme o que entendeu + próxima pergunta + EXPLIQUE o raciocínio das sugestões)
- **extracted**: Objeto com fieldId → valor extraído da mensagem
- **skipped**: Array de fieldIds que usuário indicou não ter/não querer
- **next_field**: ID do próximo campo a perguntar (null se step completo)
- **suggestions**: Para campos criativos (frase, bio), sugira 2-3 opções. CADA sugestão DEVE ter um "reason" explicando por que funciona
- **color_palettes**: Quando perguntar sobre cores, sugira 3 paletas. CADA paleta DEVE ter um "reason" explicando por que é boa para o negócio
- **step_summary_ready**: true se TODOS os campos do step foram tratados
- **confidence**: 0-1, sua confiança na extração

## COMO DAR SUGESTÕES (MUITO IMPORTANTE!)
Quando você sugerir algo (cores, frases, texto, etc.), SEMPRE inclua na mensagem E no JSON o raciocínio:

**❌ RUIM - Sem explicação:**
"Aqui estão 3 opções de frase para você escolher"

**✅ BOM - Com explicação:**
"Vou sugerir 3 opções pensando no seu público. A primeira é mais direta e funciona bem para quem precisa decidir rápido. A segunda é mais emocional e cria conexão. A terceira foca em resultado, ideal para serviços."

### Exemplo de suggestions com reason:
```json
"suggestions": [
  {
    "field": "frase",
    "value": "Café que aquece seu dia",
    "label": "Emocional",
    "reason": "Cria uma conexão afetiva - pessoas associam café a momentos especiais"
  },
  {
    "field": "frase", 
    "value": "Qualidade em cada xícara",
    "label": "Profissional",
    "reason": "Foca no produto e transmite confiança na entrega"
  }
]
```

## PALETAS FLAT UI COLORS (Cores validadas pelo mercado)
Quando sugerir cores, use EXCLUSIVAMENTE estas combinações testadas:

### Por Sensação
- **Confiança & Profissionalismo**: #3498DB (Peter River) + #2C3E50 (Midnight Blue)
- **Energia & Dinamismo**: #E74C3C (Alizarin) + #E67E22 (Carrot)
- **Natureza & Saúde**: #2ECC71 (Emerald) + #27AE60 (Nephritis)
- **Inovação & Tecnologia**: #9B59B6 (Amethyst) + #3498DB (Peter River)
- **Elegância & Sofisticação**: #34495E (Wet Asphalt) + #ECF0F1 (Clouds)
- **Acolhimento & Calma**: #1ABC9C (Turquoise) + #16A085 (Green Sea)

### Por Ramo (sugerir baseado no businessType)
- Alimentação: #E67E22 (Carrot) + #F1C40F (Sun Flower) - cores que abrem o apetite
- Saúde: #1ABC9C (Turquoise) + #3498DB (Peter River) - transmitem cuidado e confiança
- Beleza: #9B59B6 (Amethyst) + #E74C3C (Alizarin) - sofisticação e feminilidade
- Tecnologia: #3498DB (Peter River) + #2C3E50 (Midnight Blue) - moderno e confiável
- Jurídico: #2C3E50 (Midnight Blue) + #34495E (Wet Asphalt) - seriedade e credibilidade
- Educação: #3498DB (Peter River) + #2ECC71 (Emerald) - aprendizado e crescimento
- Construção: #E67E22 (Carrot) + #34495E (Wet Asphalt) - força e profissionalismo

## EXEMPLOS DE EXTRAÇÃO
User: "Minha empresa é Café Aurora, trabalhamos com cafeteria"
→ extracted: { "companyName": "Café Aurora", "businessType": "alimentacao" }

User: "meu zap é 11 99988-7766"
→ extracted: { "whatsapp": "11999887766" }

User: "não tenho endereço físico, só trabalho online"
→ skipped: ["hasPhysicalLocation", "addressCep", "addressStreet", ...]
→ extracted: { "hasPhysicalLocation": false }

User: "@cafeaurora no instagram"
→ extracted: { "instagram": "@cafeaurora" }

User: "quero azul e laranja"
→ extracted: { "primaryColor": "#3498DB", "secondaryColor": "#E67E22" }

## CAMPO: siteObjective (Objetivo do Site)
Este é um campo MUITO IMPORTANTE que define qual pacote/template será usado.

### As 3 Opções
1. **essential** (🏢 Essencial) - Para quem presta serviços e precisa ser encontrado online
2. **authority** (🚀 Autoridade) - Para quem quer mostrar portfólio/trabalhos e fechar mais contratos
3. **enterprise** (💎 Ecossistema Digital) - Para quem quer blog, catálogo de produtos, atrair tráfego

### Como Identificar (extraia de acordo com o que o usuário disser)

**→ essential:**
- "preciso de um site simples", "cartão de visita digital"
- "quero ser encontrado no Google", "preciso de presença online"
- "site institucional básico", "só preciso mostrar meus serviços"
- Prestadores de serviço que querem ser localizados

**→ authority:**
- "quero mostrar meu portfólio", "exibir meus trabalhos"
- "preciso fechar mais contratos", "mostrar cases de sucesso"
- "site para profissional liberal", "quero me posicionar como autoridade"
- Fotógrafos, designers, advogados, consultores, arquitetos

**→ enterprise:**
- "quero vender online", "preciso de catálogo de produtos"
- "quero um blog", "produzir conteúdo", "atrair tráfego"
- "loja virtual", "e-commerce", "vender produtos"
- Lojas, e-commerces, produtores de conteúdo

### Exemplo de Extração
User: "preciso de um site para mostrar meu portfólio de arquitetura e fechar mais projetos"
→ extracted: { "siteObjective": "authority" }

User: "quero um site com blog e catálogo dos meus produtos artesanais"
→ extracted: { "siteObjective": "enterprise" }

User: "só preciso de um site básico para os clientes me encontrarem"
→ extracted: { "siteObjective": "essential" }

INSTRUCTIONS;

    return $prompt;
}

/**
 * Formata valor para o prompt
 */
function formatValueForPrompt($value, $type) {
    if ($value === null || $value === '') return 'vazio';
    if ($type === 'image') return '[imagem enviada]';
    if (is_array($value)) return '[' . count($value) . ' itens]';
    if ($type === 'boolean') return $value ? 'sim' : 'não';
    
    $str = (string)$value;
    return mb_strlen($str) > 50 ? mb_substr($str, 0, 47) . '...' : $str;
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
 */
function callGeminiAPI($apiKey, $systemPrompt, $conversationHistory, $userMessage, $images = []) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";
    
    // Adicionar mensagem atual
    $userParts = [['text' => $userMessage]];
    
    // Adicionar imagens se houver
    foreach ($images as $image) {
        if (strpos($image, 'data:image') === 0) {
            // Base64
            $parts = explode(',', $image);
            $mimeType = str_replace(['data:', ';base64'], '', $parts[0]);
            $userParts[] = [
                'inlineData' => [
                    'mimeType' => $mimeType,
                    'data' => $parts[1]
                ]
            ];
        }
    }
    
    $conversationHistory[] = [
        'role' => 'user',
        'parts' => $userParts
    ];
    
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
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception("Erro de conexão: {$error}");
    }
    
    if ($httpCode !== 200) {
        error_log("[Gemini API Error] HTTP {$httpCode}: {$response}");
        throw new Exception("Erro na API do Gemini (HTTP {$httpCode})");
    }
    
    $data = json_decode($response, true);
    
    // Extrair resposta
    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    
    if (!$text) {
        throw new Exception("Resposta vazia do Gemini");
    }
    
    // Parse JSON da resposta
    $parsed = json_decode($text, true);
    
    if (!$parsed) {
        // Se não conseguiu parsear, retornar mensagem básica
        error_log("[Gemini] Resposta não-JSON: " . $text);
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

/**
 * Resposta JSON padronizada
 */
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
