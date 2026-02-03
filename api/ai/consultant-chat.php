<?php
/**
 * ============================================
 * CONSULTANT CHAT - API ENDPOINT
 * POST /api/ai/consultant-chat.php
 * ============================================
 * 
 * Endpoint para o chat consultivo do Unli.
 * Recebe o histórico de mensagens e retorna
 * a próxima resposta do consultor.
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
$rateLimit = 30; // requisições
$ratePeriod = 60; // segundos

if (!isset($_SESSION['consultant_requests'])) {
    $_SESSION['consultant_requests'] = [];
}

$_SESSION['consultant_requests'] = array_filter($_SESSION['consultant_requests'], function($timestamp) use ($ratePeriod) {
    return $timestamp > (time() - $ratePeriod);
});

if (count($_SESSION['consultant_requests']) >= $rateLimit) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'error' => 'Limite de requisições excedido. Aguarde 1 minuto.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$_SESSION['consultant_requests'][] = time();

// Obter input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['messages'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'JSON inválido ou campo "messages" ausente.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Extrair contexto do pedido (páginas/serviços comprados)
$orderContext = $input['orderContext'] ?? [];
$purchasedPages = $orderContext['purchasedPages'] ?? [];
$planName = $orderContext['planName'] ?? 'Site Vitrine';

// Carregar System Prompt
$systemPromptPath = __DIR__ . '/prompts/consultant-system-prompt.md';
$systemPrompt = file_exists($systemPromptPath) 
    ? file_get_contents($systemPromptPath) 
    : getDefaultSystemPrompt();

// Adicionar contexto das páginas/serviços comprados ao prompt
$systemPrompt = addPurchasedPagesContext($systemPrompt, $purchasedPages, $planName);

try {
    // Obter API Key do Gemini
    $geminiApiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : getenv('GEMINI_API_KEY');
    
    if (!$geminiApiKey) {
        throw new Exception('GEMINI_API_KEY não configurada no servidor.');
    }
    
    // Formatar mensagens para a API do Gemini
    $contents = formatMessagesForGemini($input['messages'], $systemPrompt);
    
    // Chamar API do Gemini
    $rawResponse = callGeminiChat($geminiApiKey, $contents);
    
    // Processar resposta JSON do Gemini
    $parsedResponse = parseGeminiResponse($rawResponse);
    
    echo json_encode([
        'success' => true,
        'response' => $parsedResponse['message'],
        'finished' => $parsedResponse['finished']
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Formata as mensagens para o formato do Gemini
 */
function formatMessagesForGemini(array $messages, string $systemPrompt): array {
    $contents = [];
    
    // Adicionar system prompt como primeira mensagem do modelo
    // (Gemini não tem system role nativo, então usamos um workaround)
    $contents[] = [
        'role' => 'user',
        'parts' => [['text' => $systemPrompt]]
    ];
    $contents[] = [
        'role' => 'model',
        'parts' => [['text' => 'Entendido. Vou atuar como o Unli, especialista em Arquitetura de Informação. Estou pronto para iniciar a reunião de briefing.']]
    ];
    
    // Processar mensagens do histórico
    foreach ($messages as $msg) {
        // Pular a mensagem de sistema (já tratamos acima)
        if ($msg['role'] === 'system') continue;
        
        $role = $msg['role'] === 'model' || $msg['role'] === 'assistant' ? 'model' : 'user';
        $contents[] = [
            'role' => $role,
            'parts' => [['text' => $msg['content']]]
        ];
    }
    
    return $contents;
}

/**
 * Chama a API do Gemini para chat
 */
function callGeminiChat(string $apiKey, array $contents): string {
    $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    
    $payload = [
        'contents' => $contents,
        'generationConfig' => [
            'temperature' => 0.8,
            'topK' => 40,
            'topP' => 0.95,
            'maxOutputTokens' => 1024,
        ],
        'safetySettings' => [
            [
                'category' => 'HARM_CATEGORY_HARASSMENT',
                'threshold' => 'BLOCK_ONLY_HIGH'
            ],
            [
                'category' => 'HARM_CATEGORY_HATE_SPEECH',
                'threshold' => 'BLOCK_ONLY_HIGH'
            ]
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
    
    return $data['candidates'][0]['content']['parts'][0]['text'];
}

/**
 * Processa a resposta do Gemini que deve vir em formato JSON
 * Extrai message e finished do JSON retornado
 */
function parseGeminiResponse(string $rawResponse): array {
    // Tentar fazer parse do JSON
    $decoded = json_decode($rawResponse, true);
    
    if ($decoded && isset($decoded['message'])) {
        return [
            'message' => $decoded['message'],
            'finished' => $decoded['finished'] ?? false
        ];
    }
    
    // Se não for JSON válido, tentar extrair JSON do texto
    // (às vezes o Gemini adiciona texto antes/depois do JSON)
    if (preg_match('/\{[^{}]*"message"\s*:\s*"[^"]*"[^{}]*\}/s', $rawResponse, $matches)) {
        $extracted = json_decode($matches[0], true);
        if ($extracted && isset($extracted['message'])) {
            return [
                'message' => $extracted['message'],
                'finished' => $extracted['finished'] ?? false
            ];
        }
    }
    
    // Fallback: se não conseguir parsear, retorna a resposta como texto
    // Limpa possíveis marcadores de código
    $cleanResponse = preg_replace('/^```json\s*/i', '', $rawResponse);
    $cleanResponse = preg_replace('/\s*```$/i', '', $cleanResponse);
    
    $decoded = json_decode($cleanResponse, true);
    if ($decoded && isset($decoded['message'])) {
        return [
            'message' => $decoded['message'],
            'finished' => $decoded['finished'] ?? false
        ];
    }
    
    // Último fallback: retorna texto puro
    return [
        'message' => $rawResponse,
        'finished' => false
    ];
}

/**
 * System Prompt padrão caso o arquivo não exista
 */
function getDefaultSystemPrompt(): string {
    return <<<PROMPT
Você é o Unli, um especialista sênior em Arquitetura de Informação e Design de Interfaces. Sua postura é profissional, objetiva e prestativa. Você não usa gírias. Você transmite segurança técnica.

FORMATO DE RESPOSTA: Sempre responda em JSON válido:
{"message": "sua resposta aqui", "finished": false}
Use finished: true APENAS na mensagem final de encerramento.

REGRAS:
1. Faça UMA pergunta por vez
2. Use escuta ativa - valide o que ouviu antes de perguntar
3. Tom: profissional, cordial e culto. Evite gírias como "Legal!", "Animal!". Use "Compreendo", "Entendido", "Perfeito".
4. NÃO mencione que é IA ou está "coletando dados"
5. Siga o fluxo: Resumo da Empresa → Produto/Público → Identidade Visual → Autoridade → Diferencial
6. Sempre traduza conceitos em soluções visuais concretas

Comece pedindo uma apresentação da empresa: nome, origem e história.
PROMPT;
}

/**
 * Adiciona contexto das páginas/serviços comprados ao prompt do sistema
 * Isso ajuda a IA a personalizar as sugestões baseadas no que o cliente realmente comprou
 */
function addPurchasedPagesContext(string $systemPrompt, array $purchasedPages, string $planName): string {
    if (empty($purchasedPages)) {
        return $systemPrompt;
    }
    
    // Mapeamento de páginas para descrições amigáveis e usos
    $pageDescriptions = [
        'home' => 'Página Inicial - vitrine principal com apresentação da marca e CTA',
        'about' => 'Sobre/Quem Somos - história, missão, valores e equipe',
        'services' => 'Serviços - detalhamento do que é oferecido (pode ser usado como catálogo de produtos, jogos, etc.)',
        'portfolio' => 'Portfólio/Trabalhos - galeria de projetos realizados',
        'testimonials' => 'Depoimentos - prova social com feedbacks de clientes',
        'contact' => 'Contato - formulário de contato e informações de localização',
        'blog' => 'Blog/Notícias - artigos e conteúdos para SEO',
        'faq' => 'FAQ - perguntas frequentes',
        'team' => 'Equipe - apresentação dos membros',
        'pricing' => 'Preços/Planos - tabela de preços e pacotes',
        'gallery' => 'Galeria - fotos e imagens do negócio',
        'products' => 'Produtos - catálogo de produtos'
    ];
    
    // Construir lista de páginas compradas
    $purchasedList = [];
    foreach ($purchasedPages as $page) {
        $pageLower = strtolower($page);
        $description = $pageDescriptions[$pageLower] ?? ucfirst($page);
        $purchasedList[] = "- {$description}";
    }
    
    // Determinar páginas NÃO compradas
    $allCommonPages = ['home', 'about', 'services', 'portfolio', 'testimonials', 'contact', 'blog', 'faq'];
    $purchasedLower = array_map('strtolower', $purchasedPages);
    $notPurchased = array_diff($allCommonPages, $purchasedLower);
    
    $notPurchasedList = [];
    foreach ($notPurchased as $page) {
        $notPurchasedList[] = "- " . ucfirst($page);
    }
    
    // Construir contexto adicional
    $contextAddition = <<<CONTEXT

# CONTEXTO DO PEDIDO DO CLIENTE
O cliente adquiriu o plano **"{$planName}"**.

## Páginas/Recursos INCLUÍDOS no site:
CONTEXT;
    
    $contextAddition .= "\n" . implode("\n", $purchasedList);
    
    if (!empty($notPurchasedList)) {
        $contextAddition .= "\n\n## Páginas/Recursos NÃO INCLUÍDOS (não sugira estes):";
        $contextAddition .= "\n" . implode("\n", $notPurchasedList);
    }
    
    $contextAddition .= <<<RULES

## REGRAS IMPORTANTES BASEADAS NO PEDIDO:
1. **APENAS sugira funcionalidades e páginas que o cliente COMPROU**
2. Ao visualizar o site para o cliente, mencione APENAS as páginas disponíveis
3. Se o cliente mencionar algo que precisaria de uma página não comprada, seja honesto e sugira um upgrade ou adapte para o que ele tem
4. Ao dar o resumo final, descreva como cada página comprada será usada para o negócio específico dele
5. Se ele comprou "Contato" com formulário, explique como vai usar (ex: "O formulário de contato pode receber solicitações de orçamento")
6. Se ele comprou "Serviços", pode usar para: catálogo de jogos, lista de produtos, tipos de serviço oferecidos, etc.
7. Personalize a visualização: se é uma empresa de games, a página de "Serviços" vira "Nossos Jogos"; se é advocacia, vira "Áreas de Atuação"

## No resumo final:
- Liste cada página comprada e como ela será usada no contexto do negócio do cliente
- Traduza o nome genérico (ex: "Serviços") para o contexto específico (ex: "Catálogo de Jogos")
- Seja visual e concreto sobre o que cada seção vai conter
RULES;
    
    return $systemPrompt . $contextAddition;
}
