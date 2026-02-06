<?php
/**
 * ============================================
 * SDR CHAT - API ENDPOINT
 * POST /api/ai/sdr-chat.php
 * ============================================
 * 
 * Endpoint para o chat SDR consultivo da Unli.
 * Implementa um assistente de vendas humanizado que:
 * - Qualifica leads através de conversa natural
 * - Oferece micro-consultoria por nicho
 * - Sugere o plano ideal baseado nas necessidades
 * - Extrai dados estruturados para preencher o formulário
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
$rateLimit = 30; // requisições por minuto
$ratePeriod = 60; // segundos

if (!isset($_SESSION['sdr_requests'])) {
    $_SESSION['sdr_requests'] = [];
}

$_SESSION['sdr_requests'] = array_filter($_SESSION['sdr_requests'], function($timestamp) use ($ratePeriod) {
    return $timestamp > (time() - $ratePeriod);
});

if (count($_SESSION['sdr_requests']) >= $rateLimit) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'error' => 'Limite de requisições excedido. Aguarde 1 minuto.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$_SESSION['sdr_requests'][] = time();

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

// Carregar System Prompt
$systemPromptPath = __DIR__ . '/prompts/sdr-consultant-prompt.md';
$systemPrompt = file_exists($systemPromptPath) 
    ? file_get_contents($systemPromptPath) 
    : getDefaultSDRPrompt();

// Injetar dados dinâmicos no prompt
$systemPrompt = injectDynamicData($systemPrompt);

try {
    // Obter API Key do Gemini
    $geminiApiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : getenv('GEMINI_API_KEY');
    
    if (!$geminiApiKey) {
        throw new Exception('GEMINI_API_KEY não configurada no servidor.');
    }
    
    // Formatar mensagens para a API do Gemini
    $contents = formatMessagesForGemini($input['messages'], $systemPrompt);
    
    // Chamar API do Gemini
    $rawResponse = callGeminiSDR($geminiApiKey, $contents);
    
    // Processar resposta JSON do Gemini
    $parsedResponse = parseSDRResponse($rawResponse);
    
    // Validar e corrigir o stage baseado no conteúdo da mensagem
    $correctedStage = validateAndCorrectStage($parsedResponse);
    
    echo json_encode([
        'success' => true,
        'response' => $parsedResponse['message'],
        'stage' => $correctedStage,
        'clientData' => $parsedResponse['clientData'] ?? null,
        'suggestedPlan' => $parsedResponse['suggestedPlan'] ?? null,
        'finished' => $parsedResponse['finished'] ?? false
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Injeta dados dinâmicos no prompt (data, preços, promoção)
 */
function injectDynamicData(string $prompt): string {
    // Data atual em português
    $meses = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
    ];
    $dataAtual = $meses[(int)date('n')] . ' de ' . date('Y');
    
    // Carregar tabela de preços
    $pricingPath = __DIR__ . '/../pricing.json';
    $pricing = file_exists($pricingPath) 
        ? json_decode(file_get_contents($pricingPath), true) 
        : [];
    
    // Formatar tabela de preços
    $tabelaPrecos = formatPricingTable($pricing);
    
    // Informações da promoção
    $promocaoInfo = <<<PROMO
**Promoção "Iniciando 2026 Online":**
- 30% de desconto JÁ APLICADO em todos os planos
- Válida para novos clientes
- Combinável com desconto PIX (15% adicional)
- Total de até 45% OFF para pagamento à vista no PIX
PROMO;
    
    // Substituir placeholders
    $prompt = str_replace('{{DATA_ATUAL}}', $dataAtual, $prompt);
    $prompt = str_replace('{{TABELA_PRECOS}}', $tabelaPrecos, $prompt);
    $prompt = str_replace('{{PROMOCAO_INFO}}', $promocaoInfo, $prompt);
    
    return $prompt;
}

/**
 * Formata a tabela de preços de forma legível
 */
function formatPricingTable(array $pricing): string {
    if (empty($pricing)) {
        return "Preços competitivos de mercado. Consulte valores específicos.";
    }
    
    $tabela = "## ATENÇÃO: TODOS OS VALORES SÃO ANUAIS (já com 30% de desconto aplicado)\n\n";
    $tabela .= "## Produtos Base\n";
    
    // Preço base do site completo (já com desconto)
    $siteBasePrice = 0;
    if (isset($pricing['products'])) {
        foreach ($pricing['products'] as $key => $product) {
            $preco = $product['base_price'] ?? 0;
            $precoComDesconto = round($preco * 0.7); // 30% OFF - VALOR ANUAL
            $precoMensal = round($precoComDesconto / 12, 2);
            $tabela .= "- **{$product['name']}**: R$ {$precoComDesconto}/ano (R$ {$precoMensal}/mês)\n";
            
            if ($key === 'site_complete') {
                $siteBasePrice = $precoComDesconto;
            }
        }
    }
    
    $tabela .= "\n## Páginas Adicionais (valores ANUAIS com 30% OFF)\n";
    
    // Array para guardar preços das páginas (anuais)
    $pagesPrices = [];
    
    if (isset($pricing['page_addons'])) {
        foreach ($pricing['page_addons'] as $key => $addon) {
            $preco = $addon['price'] ?? 0;
            $precoComDesconto = round($preco * 0.7); // 30% OFF - VALOR ANUAL
            $pagesPrices[$key] = $precoComDesconto;
            $precoMensal = round($precoComDesconto / 12, 2);
            $tabela .= "- {$addon['name']} ({$key}): +R$ {$precoComDesconto}/ano (+R$ {$precoMensal}/mês)\n";
        }
    }
    
    $tabela .= "\n## Pacotes Recomendados (VALORES ANUAIS CALCULADOS)\n";
    $tabela .= "**LÓGICA DE PAGAMENTO (igual ao pricing.php):**\n";
    $tabela .= "- À vista PIX = Valor anual (sem alteração)\n";
    $tabela .= "- Parcelado 12x = (Valor anual × 1.15) ÷ 12\n\n";
    
    if (isset($pricing['predefined_packages'])) {
        foreach ($pricing['predefined_packages'] as $key => $package) {
            // Calcular preço total do pacote (ANUAL)
            $packageTotalAnual = $siteBasePrice;
            if (isset($package['pages'])) {
                foreach ($package['pages'] as $pageKey) {
                    $packageTotalAnual += $pagesPrices[$pageKey] ?? 0;
                }
            }
            
            $packageAvista = $packageTotalAnual; // À vista = valor normal
            $packageParcelado = round(($packageTotalAnual * 1.15) / 12, 2); // Parcelado com 15% markup
            
            $tabela .= "- **{$package['name']}** ({$package['icon']})\n";
            $tabela .= "  - À VISTA PIX: R$ {$packageAvista} (pagamento único)\n";
            $tabela .= "  - PARCELADO 12x: R$ {$packageParcelado}/mês (total R$ " . round($packageParcelado * 12, 2) . ")\n";
            $tabela .= "  - Páginas: " . implode(', ', $package['pages'] ?? []) . "\n";
            $tabela .= "  - Ideal para: {$package['ideal_for']}\n\n";
        }
    }
    
    $tabela .= "## Instruções de Cálculo (SEGUIR ESTA LÓGICA):\n";
    $tabela .= "1. À vista PIX = Valor anual (sem desconto adicional)\n";
    $tabela .= "2. Parcelado 12x = (Valor anual × 1.15) ÷ 12\n";
    $tabela .= "3. NUNCA mencione 'desconto de 15% no PIX' - à vista é o preço normal!\n";
    
    return $tabela;
}

/**
 * Formata as mensagens para o formato do Gemini
 */
function formatMessagesForGemini(array $messages, string $systemPrompt): array {
    $contents = [];
    
    // Adicionar system prompt como primeira mensagem
    $contents[] = [
        'role' => 'user',
        'parts' => [['text' => $systemPrompt . "\n\n---\nLEMBRETE FINAL CRÍTICO SOBRE STAGES:\n- Se minha resposta SUGERE UM PLANO/PACOTE (Essencial, Autoridade, Ecossistema) ou LISTA PÁGINAS = stage OBRIGATÓRIO: \"PROPOSTA\"\n- Se minha resposta MENCIONA VALORES em R$ = stage OBRIGATÓRIO: \"PRECO\"\n- EXPLORACAO é APENAS quando estou perguntando sobre o negócio, SEM sugerir plano nenhum\n- O campo stage no meu JSON DEVE refletir o CONTEÚDO da minha mensagem, não a etapa anterior"]]
    ];
    $contents[] = [
        'role' => 'model',
        'parts' => [['text' => json_encode([
            'message' => 'Entendido. Vou atuar como o Assistente Unli, consultor de estratégia digital. Estou pronto para iniciar uma conversa consultiva e humanizada. Confirmo que seguirei rigorosamente as regras de stage: PROPOSTA quando sugerir plano/páginas, PRECO quando mencionar R$, EXPLORACAO apenas quando ainda estiver descobrindo o negócio.',
            'stage' => 'PRONTO',
            'finished' => false
        ], JSON_UNESCAPED_UNICODE)]]
    ];
    
    // Processar mensagens do histórico
    foreach ($messages as $msg) {
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
 * Chama a API do Gemini para o chat SDR com retry automático
 */
function callGeminiSDR(string $apiKey, array $contents): string {
    $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    
    $payload = [
        'contents' => $contents,
        'generationConfig' => [
            'temperature' => 0.9, // Mais criativo para conversa natural
            'topK' => 40,
            'topP' => 0.95,
            'maxOutputTokens' => 1500,
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
    
    $maxRetries = 3;
    $lastError = null;
    $lastHttpCode = 0;
    
    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        $ch = curl_init($apiUrl . '?key=' . $apiKey);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_CONNECTTIMEOUT => 15
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Erro de conexão (timeout, DNS, etc) — retry
        if ($curlError) {
            $lastError = 'Erro de conexão: ' . $curlError;
            if ($attempt < $maxRetries) {
                sleep($attempt * 2); // 2s, 4s
                continue;
            }
            throw new Exception($lastError);
        }
        
        // Sucesso
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            
            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                // Resposta inválida — retry (pode ser resposta vazia por safety filter)
                $lastError = 'Resposta inválida da API Gemini.';
                if ($attempt < $maxRetries) {
                    sleep($attempt);
                    continue;
                }
                throw new Exception($lastError);
            }
            
            return $data['candidates'][0]['content']['parts'][0]['text'];
        }
        
        // Erros retryable: 429 (rate limit), 500, 502, 503 (server errors)
        $retryableCodes = [429, 500, 502, 503];
        $lastHttpCode = $httpCode;
        
        if (in_array($httpCode, $retryableCodes) && $attempt < $maxRetries) {
            // Exponential backoff: 2s, 4s (Gemini rate limit se resolve rápido)
            $waitSeconds = $attempt * 2;
            
            // Para 429 especificamente, esperar um pouco mais
            if ($httpCode === 429) {
                $waitSeconds = $attempt * 3; // 3s, 6s
            }
            
            sleep($waitSeconds);
            continue;
        }
        
        // Erro não retryable ou última tentativa — lançar exceção
        $errorData = json_decode($response, true);
        $errorMessage = $errorData['error']['message'] ?? 'Erro desconhecido';
        $lastError = 'Erro na API Gemini: ' . $errorMessage;
    }
    
    throw new Exception($lastError ?? 'Erro desconhecido após ' . $maxRetries . ' tentativas.');
}

/**
 * Valida e corrige o stage baseado no conteúdo real da mensagem.
 * O Gemini frequentemente erra o stage — esta função é a camada de segurança.
 */
function validateAndCorrectStage(array $parsedResponse): string {
    $stage = $parsedResponse['stage'] ?? 'EXPLORACAO';
    $message = $parsedResponse['message'] ?? '';
    $finished = $parsedResponse['finished'] ?? false;
    $suggestedPlan = $parsedResponse['suggestedPlan'] ?? null;
    $messageLower = mb_strtolower($message, 'UTF-8');
    
    // ====== REGRA 1: FECHAMENTO ======
    // Se finished=true, é FECHAMENTO independente do que o Gemini disse
    if ($finished) {
        return 'FECHAMENTO';
    }
    
    // ====== REGRA 2: PRECO ======
    // Se menciona valores em R$, é PRECO
    $hasPrice = preg_match('/R\$\s*[\d.,]+/', $message);
    $hasPriceTerms = preg_match('/(à vista|parcel|pagamento|pix|12x|valor anual|por mês|por ano)/iu', $messageLower);
    if ($hasPrice && $hasPriceTerms) {
        return 'PRECO';
    }
    
    // ====== REGRA 3: PROPOSTA ======
    // Se menciona um plano/pacote específico ou lista páginas/funcionalidades
    $planPatterns = [
        'ecossistema digital',
        'autoridade online',
        'essencial',
        'plano essencial',
        'plano autoridade',
        'plano ecossistema',
        'vitrine de produtos',
        'landing page',
    ];
    
    $hasPlan = false;
    foreach ($planPatterns as $pattern) {
        if (mb_strpos($messageLower, $pattern) !== false) {
            $hasPlan = true;
            break;
        }
    }
    
    // Detectar lista de páginas/funcionalidades sendo sugeridas
    $pagePatterns = [
        'página sobre',
        'página de serviço',
        'página de contato',
        'portfólio',
        'portfolio',
        'blog',
        'vitrine',
        'sobre nós',
        'sobre n[óo]s',
    ];
    
    $pageCount = 0;
    foreach ($pagePatterns as $pattern) {
        if (preg_match('/' . $pattern . '/iu', $messageLower)) {
            $pageCount++;
        }
    }
    
    // Se tem plano sugerido OU lista 2+ páginas OU suggestedPlan existe = PROPOSTA
    $hasStructureProposal = ($hasPlan || $pageCount >= 2 || !empty($suggestedPlan));
    
    // Termos que reforçam ser uma proposta
    $proposalTerms = preg_match('/(ideal para voc[êe]|inclui|recomendo|estrutura|baseado no|pacote|configuração)/iu', $messageLower);
    
    if ($hasStructureProposal || ($hasPlan && $proposalTerms)) {
        // Só corrige para PROPOSTA se não está em estágio mais avançado
        if ($stage === 'EXPLORACAO' || $stage === 'ABERTURA') {
            return 'PROPOSTA';
        }
    }
    
    // ====== REGRA 4: Não regredir ======
    // Se o Gemini mandou um stage válido e mais avançado, manter
    $validStages = ['ABERTURA', 'EXPLORACAO', 'PROPOSTA', 'PRECO', 'FECHAMENTO'];
    if (in_array($stage, $validStages)) {
        return $stage;
    }
    
    return 'EXPLORACAO';
}

/**
 * Processa a resposta do Gemini (espera JSON estruturado)
 */
function parseSDRResponse(string $rawResponse): array {
    // Tentar fazer parse do JSON direto
    $decoded = json_decode($rawResponse, true);
    
    if ($decoded && isset($decoded['message'])) {
        return $decoded;
    }
    
    // Limpar possíveis marcadores de código no início e fim
    $cleanResponse = preg_replace('/^```json\s*/i', '', $rawResponse);
    $cleanResponse = preg_replace('/\s*```$/i', '', $cleanResponse);
    $cleanResponse = trim($cleanResponse);
    
    $decoded = json_decode($cleanResponse, true);
    if ($decoded && isset($decoded['message'])) {
        return $decoded;
    }
    
    // Tentar extrair JSON de dentro de bloco ```json ... ``` (pode estar no meio do texto)
    if (preg_match('/```json\s*(\{[\s\S]*?\})\s*```/i', $rawResponse, $matches)) {
        $extracted = json_decode($matches[1], true);
        if ($extracted && isset($extracted['message'])) {
            return $extracted;
        }
    }
    
    // Tentar extrair JSON de dentro de bloco ``` ... ``` (sem especificar json)
    if (preg_match('/```\s*(\{[\s\S]*?"message"[\s\S]*?\})\s*```/i', $rawResponse, $matches)) {
        $extracted = json_decode($matches[1], true);
        if ($extracted && isset($extracted['message'])) {
            return $extracted;
        }
    }
    
    // Tentar extrair JSON completo (com objetos aninhados) do texto
    // Procura por { e encontra o } correspondente usando parser de profundidade
    $startPos = strpos($rawResponse, '{"message"');
    if ($startPos === false) {
        $startPos = strpos($rawResponse, "{\n  \"message\"");
    }
    if ($startPos === false) {
        $startPos = strpos($rawResponse, "{\r\n  \"message\"");
    }
    if ($startPos === false) {
        // Tentar encontrar qualquer JSON que tenha "message" dentro
        $startPos = strpos($rawResponse, '{ "message"');
    }
    if ($startPos === false) {
        $startPos = strpos($rawResponse, "{\n\"message\"");
    }
    
    if ($startPos !== false) {
        $depth = 0;
        $jsonStr = '';
        $len = strlen($rawResponse);
        $inString = false;
        $escape = false;
        
        for ($i = $startPos; $i < $len; $i++) {
            $char = $rawResponse[$i];
            $jsonStr .= $char;
            
            if ($escape) {
                $escape = false;
                continue;
            }
            
            if ($char === '\\' && $inString) {
                $escape = true;
                continue;
            }
            
            if ($char === '"' && !$escape) {
                $inString = !$inString;
                continue;
            }
            
            if (!$inString) {
                if ($char === '{') {
                    $depth++;
                } elseif ($char === '}') {
                    $depth--;
                    if ($depth === 0) {
                        break;
                    }
                }
            }
        }
        
        if ($depth === 0 && $jsonStr) {
            $extracted = json_decode($jsonStr, true);
            if ($extracted && isset($extracted['message'])) {
                return $extracted;
            }
        }
    }
    
    // Fallback: retorna texto puro como mensagem
    return [
        'message' => $rawResponse,
        'stage' => 'EXPLORACAO',
        'clientData' => null,
        'suggestedPlan' => null,
        'finished' => false
    ];
}

/**
 * System Prompt padrão caso o arquivo não exista
 */
function getDefaultSDRPrompt(): string {
    return <<<PROMPT
Você é o Assistente Unli, um consultor de estratégia digital.
Sua missão é entender o negócio do cliente e sugerir o plano de site ideal.

REGRAS:
1. Faça UMA pergunta por vez
2. Use escuta ativa - valide o que ouviu antes de perguntar
3. Dê dicas de valor sobre o nicho do cliente
4. Tom: empático, profissional, consultivo

Responda em JSON:
{"message": "sua resposta", "stage": "ETAPA", "finished": false}

Comece perguntando o que o cliente precisa.
PROMPT;
}
