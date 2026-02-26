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
    
    // Validar e corrigir o stage E finished baseado no conteúdo da mensagem
    $stageAndFinished = validateAndCorrectStageAndFinished($parsedResponse);
    $correctedStage = $stageAndFinished['stage'];
    $correctedFinished = $stageAndFinished['finished'];
    
    // ============================================================
    // FONTE DE VERDADE: Computar preço real pelo backend sempre que
    // houver um suggestedPlan com páginas definidas.
    // A IA NÃO é confiável para calcular preços — ela apenas envia
    // a seleção (páginas + forma de pagamento).
    // ============================================================
    $realPricing = null;
    $suggestedPlan = $parsedResponse['suggestedPlan'] ?? null;
    
    if (!empty($suggestedPlan['pages'])) {
        try {
            require_once __DIR__ . '/../lib/pricing.php';
            $cfg = load_pricing_config(__DIR__ . '/../pricing.json');
            
            // Montar seleção a partir do suggestedPlan da IA
            $pagesMap = [];
            foreach ($suggestedPlan['pages'] as $pageKey) {
                if (is_string($pageKey)) {
                    $pagesMap[$pageKey] = true;
                }
            }
            
            $selectionForPricing = [
                'product'               => $suggestedPlan['type'] ?? 'site_complete',
                'pages'                 => $pagesMap,
                'content'               => [],
                'custom_pages'          => [],
                'video_basic_quantity'  => 0,
                'video_pro_quantity'    => 0,
                'pdf'                   => false,
                'specialist_onboarding' => !empty($suggestedPlan['specialistOnboarding']),
                'payment_method'        => ($suggestedPlan['paymentMethod'] ?? '') === 'pix_avista'
                                            ? 'avista' : 'parcelado'
            ];
            
            $normalizedSel = normalize_selection($selectionForPricing, $cfg);
            $realPricing   = compute_price($normalizedSel, $cfg);
            
            // Remover estimatedPrice da IA (está errado)
            unset($parsedResponse['suggestedPlan']['estimatedPrice']);
            
            // Substituir preços mensais errados na mensagem pela parcela real
            $isParcelado = !in_array($suggestedPlan['paymentMethod'] ?? '', ['pix_avista', 'avista']);
            $msg = $parsedResponse['message'];
            
            if ($isParcelado && isset($realPricing['parcela_12'])) {
                $realLabel = 'R$ ' . number_format($realPricing['parcela_12'], 2, ',', '.');
                // Normalizar bold markdown em volta de valores monetários (IA às vezes usa **R$ X**)
                $msg = preg_replace('/\*{1,2}(R\$\s*[\d.,]+)\*{1,2}/u', '$1', $msg);
                // Substituir QUALQUER padrão de preço mensal (mensais, /mês, por mês, mensalmente, ao mês)
                $msg = preg_replace(
                    '/R\$\s*[\d.,]+\s*(mensais?|por\s*m[eê]s|\/m[eê]s|mensalmente|ao\s*m[eê]s)/ui',
                    $realLabel . '/mês',
                    $msg
                );
                // Substituir também padrões numéricos tipo "132,05/mês" sem "R$" explícito
                $msg = preg_replace(
                    '/\b[\d]+[,.][\d]+\s*(mensais?|\/m[eê]s|por\s*m[eê]s)/ui',
                    $realLabel . '/mês',
                    $msg
                );
            } elseif (!$isParcelado && isset($realPricing['avista'])) {
                $realLabel = 'R$ ' . number_format($realPricing['avista'], 2, ',', '.');
                // Normalizar bold markdown
                $msg = preg_replace('/\*{1,2}(R\$\s*[\d.,]+)\*{1,2}/u', '$1', $msg);
                // Para pagamento à vista, substituir apenas valores com 4+ caracteres numéricos (ex: 1.378)
                $msg = preg_replace(
                    '/R\$\s*[\d.,]{4,}/u',
                    $realLabel,
                    $msg
                );
            }
            
            $parsedResponse['message'] = $msg;
            
            error_log('💰 [sdr-chat] Preço real computado: parcela=' . ($realPricing['parcela_12'] ?? '-') . ' avista=' . ($realPricing['avista'] ?? '-'));
            
        } catch (Exception $pricingEx) {
            error_log('⚠️ [sdr-chat] Falha ao computar preço real: ' . $pricingEx->getMessage());
        }
    }
    
    echo json_encode([
        'success' => true,
        'response' => $parsedResponse['message'],
        'stage' => $correctedStage,
        'clientData' => $parsedResponse['clientData'] ?? null,
        'suggestedPlan' => $parsedResponse['suggestedPlan'] ?? null,
        'pricing' => $realPricing, // Preço real calculado pelo backend
        'finished' => $correctedFinished
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
**Sobre os Preços e Modelo de Assinatura:**
- Todos os valores já incluem a Promoção "Iniciando 2026 Online" (30% OFF)
- Válida para novos clientes
- O plano é uma **ASSINATURA ANUAL** — o cliente paga por 1 ano de hospedagem, suporte e manutenção
- **À vista no PIX:** pagamento único anual, sem acréscimos
- **Parcelado 12x cartão:** 12 parcelas mensais com acréscimo de 15% (taxa do gateway) — ainda é um plano anual, só dividido em parcelas
- ⚠️ NUNCA mostre o valor total anual parcelado (ex: "total R$ X.XXX em 12 meses") — o cliente verá isso no checkout da Pagar.me
PROMO;
    
    // Injetar preços de add-ons específicos (valores dinâmicos do pricing.json)
    $precoEspecialista = "R$ " . ($pricing['service_addons']['specialist_onboarding']['price'] ?? 169);
    
    // Vídeos
    $videoBasicPrice = $pricing['content_addons']['video_basic']['price_per_unit'] ?? 35;
    $videoProPrice = $pricing['content_addons']['video_pro']['price_per_unit'] ?? 120;
    $precosVideos = "- **Vídeo Básico** (até 50MB): R$ {$videoBasicPrice} por vídeo\n";
    $precosVideos .= "- **Vídeo Pro** (até 1GB): R$ {$videoProPrice} por vídeo";
    
    // PDF
    $pdfPrice = $pricing['content_addons']['pdf']['price'] ?? 15;
    $precoPdf = "- **Suporte a PDFs**: R$ {$pdfPrice} por projeto";
    
    // Recursos customizados (custom_pages resources)
    $recursosCustom = "";
    if (isset($pricing['custom_pages']['resources'])) {
        foreach ($pricing['custom_pages']['resources'] as $key => $resource) {
            $resourcePrice = $resource['price'] ?? 0;
            $recursosCustom .= "- **{$resource['name']}**: R$ {$resourcePrice} ({$resource['description']})\n";
        }
    }
    
    // Substituir placeholders
    $prompt = str_replace('{{DATA_ATUAL}}', $dataAtual, $prompt);
    $prompt = str_replace('{{TABELA_PRECOS}}', $tabelaPrecos, $prompt);
    $prompt = str_replace('{{PROMOCAO_INFO}}', $promocaoInfo, $prompt);
    $prompt = str_replace('{{PRECO_ESPECIALISTA}}', $precoEspecialista, $prompt);
    $prompt = str_replace('{{PRECOS_VIDEOS}}', $precosVideos, $prompt);
    $prompt = str_replace('{{PRECO_PDF}}', $precoPdf, $prompt);
    $prompt = str_replace('{{RECURSOS_CUSTOM}}', $recursosCustom, $prompt);
    
    return $prompt;
}

/**
 * Formata a tabela de preços de forma legível
 */
function formatPricingTable(array $pricing): string {
    if (empty($pricing)) {
        return "Preços competitivos de mercado. Consulte valores específicos.";
    }
    
    $tabela = "## TABELA DE PREÇOS OFICIAL (USE ESTES VALORES EXATOS — NÃO CALCULE NADA)\n\n";
    $tabela .= "Todos os valores abaixo já incluem a promoção \"Iniciando 2026 Online\" (30% OFF).\n";
    $tabela .= "**REGRA:** Leia e use os valores exatos desta tabela. NUNCA faça contas.\n\n";
    
    $tabela .= "## Produtos Base\n";
    
    // Preço base do site completo
    $siteBasePrice = 0;
    if (isset($pricing['products'])) {
        foreach ($pricing['products'] as $key => $product) {
            $preco = $product['base_price'] ?? 0;
            $precoMensalPix = round($preco / 12, 2);
            $precoMensalCartao = round(($preco * 1.15) / 12, 2);
            $tabela .= "- **{$product['name']}**:\n";
            $tabela .= "  - À vista PIX: R$ {$preco} (pagamento único anual)\n";
            $tabela .= "  - Mensal no cartão: R$ {$precoMensalCartao}/mês\n";
            
            if ($key === 'site_complete') {
                $siteBasePrice = $preco;
            }
        }
    }
    
    $tabela .= "\n## Páginas Adicionais (valores pré-calculados)\n";
    
    // Array para guardar preços das páginas (anuais)
    $pagesPrices = [];
    
    if (isset($pricing['page_addons'])) {
        foreach ($pricing['page_addons'] as $key => $addon) {
            $preco = $addon['price'] ?? 0;
            $pagesPrices[$key] = $preco;
            $precoMensalPix = round($preco / 12, 2);
            $precoMensalCartao = round(($preco * 1.15) / 12, 2);
            $tabela .= "- **{$addon['name']}** ({$key}):\n";
            $tabela .= "  - Anual: +R$ {$preco}\n";
            $tabela .= "  - Mensal PIX: +R$ {$precoMensalPix}/mês\n";
            $tabela .= "  - Mensal cartão: +R$ {$precoMensalCartao}/mês\n";
        }
    }
    
    $tabela .= "\n## Pacotes Recomendados (VALORES FINAIS — USE EXATAMENTE ESTES)\n\n";
    
    if (isset($pricing['predefined_packages'])) {
        foreach ($pricing['predefined_packages'] as $key => $package) {
            // Calcular preço total do pacote (ANUAL)
            $packageTotalAnual = $siteBasePrice;
            if (isset($package['pages'])) {
                foreach ($package['pages'] as $pageKey) {
                    $packageTotalAnual += $pagesPrices[$pageKey] ?? 0;
                }
            }
            
            $packageAvista = $packageTotalAnual;
            $packageParcelado = round(($packageTotalAnual * 1.15) / 12, 2);
            $packageTotalParcelado = round($packageParcelado * 12, 2);
            $economiaAvista = round($packageTotalParcelado - $packageAvista, 2);
            
            $tabela .= "- **{$package['name']}** ({$package['icon']})\n";
            $tabela .= "  - À VISTA PIX: R$ {$packageAvista} (pagamento único anual)\n";
            $tabela .= "  - PARCELADO 12x CARTÃO: R$ {$packageParcelado}/mês (assinatura anual — 12 parcelas)\n";
            $tabela .= "  - Economia à vista vs parcelado: R$ {$economiaAvista}\n";
            $tabela .= "  - Páginas: " . implode(', ', $package['pages'] ?? []) . "\n";
            $tabela .= "  - Ideal para: {$package['ideal_for']}\n\n";
        }
    }
    
    // Add-ons de conteúdo com valores pré-calculados
    if (isset($pricing['content_addons'])) {
        $tabela .= "## Add-ons de Conteúdo (valores unitários)\n";
        foreach ($pricing['content_addons'] as $key => $addon) {
            $precoUnit = $addon['price_per_unit'] ?? $addon['price'] ?? 0;
            $tabela .= "- **{$addon['name']}**: R$ {$precoUnit}";
            if (isset($addon['supports_quantity']) && $addon['supports_quantity']) {
                $tabela .= " por unidade (máx {$addon['max_quantity']})";
            }
            $tabela .= "\n";
        }
        $tabela .= "\n";
    }
    
    // Add-ons de serviço
    if (isset($pricing['service_addons'])) {
        $tabela .= "## Add-ons de Serviço\n";
        foreach ($pricing['service_addons'] as $key => $addon) {
            $preco = $addon['price'] ?? 0;
            $tabela .= "- **{$addon['name']}**: +R$ {$preco}\n";
        }
        $tabela .= "\n";
    }
    
    $tabela .= "## REGRAS DE USO DESTA TABELA:\n";
    $tabela .= "1. NUNCA faça cálculos matemáticos. Use APENAS os valores acima.\n";
    $tabela .= "2. À vista PIX = valor anual listado (pagamento único — não mencione que é 'anual' separadamente, já está implícito).\n";
    $tabela .= "3. Parcelado cartão = valor mensal listado (12 parcelas que compõem a assinatura anual). SEMPRE diga que é 'assinatura anual de R$ X/mês'.\n";
    $tabela .= "4. NUNCA mencione 'desconto de 15% no PIX' — à vista é o preço normal.\n";
    $tabela .= "5. NUNCA mostre o total anual parcelado (ex: 'total R$ X.XXX ao fim de 12 meses') — o cliente verá isso na plataforma de pagamento.\n";
    $tabela .= "6. Se o cliente pedir combinação customizada de páginas, some os valores ANUAIS das páginas + produto base para obter o avista, e calcule PARCELADO = (avista * 1.15) / 12.\n";
    
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
 * Valida e corrige o stage E o finished baseado no conteúdo real da mensagem.
 * O Gemini frequentemente erra o stage e esquece de marcar finished=true.
 * Esta função é a camada de segurança.
 * 
 * @return array ['stage' => string, 'finished' => bool]
 */
function validateAndCorrectStageAndFinished(array $parsedResponse): array {
    $stage = $parsedResponse['stage'] ?? 'EXPLORACAO';
    $message = $parsedResponse['message'] ?? '';
    $finished = $parsedResponse['finished'] ?? false;
    $suggestedPlan = $parsedResponse['suggestedPlan'] ?? null;
    $messageLower = mb_strtolower($message, 'UTF-8');
    
    // ====== REGRA 0: DETECTAR FECHAMENTO PELA MENSAGEM ======
    // Frases que indicam claramente que é uma mensagem de fechamento/encerramento
    $closingPatterns = [
        'vou te direcionar',
        'direcionar agora',
        'finalizar o pedido',
        'finalizar seu pedido',
        'direcionar para finalizar',
        'vamos finalizar',
        'fechar o pedido',
        'concluir o pedido',
        'prosseguir para o pagamento',
        'prosseguir com o pagamento',
        'encaminhar para o pagamento',
        'qualquer dúvida, é só chamar',
        'qualquer duvida, e so chamar',
    ];
    
    $isClosingMessage = false;
    foreach ($closingPatterns as $pattern) {
        if (mb_strpos($messageLower, $pattern) !== false) {
            $isClosingMessage = true;
            break;
        }
    }
    
    // Se é mensagem de fechamento E tem preço E tem suggestedPlan com paymentMethod
    // → forçar finished=true e stage=FECHAMENTO
    $hasPrice = preg_match('/R\$\s*[\d.,]+/', $message);
    $hasPaymentMethod = !empty($suggestedPlan['paymentMethod']);
    
    if ($isClosingMessage && $hasPrice && $hasPaymentMethod) {
        error_log('🔧 [validateStage] Forçando FECHAMENTO + finished=true (mensagem de fechamento detectada)');
        return ['stage' => 'FECHAMENTO', 'finished' => true];
    }
    
    // Se é mensagem de fechamento E já falou preço antes (stage=PRECO) → forçar
    if ($isClosingMessage && ($stage === 'PRECO' || $stage === 'FECHAMENTO' || $hasPrice)) {
        error_log('🔧 [validateStage] Forçando FECHAMENTO + finished=true (fechamento após preço)');
        return ['stage' => 'FECHAMENTO', 'finished' => true];
    }
    
    // ====== REGRA 1: FECHAMENTO (Gemini mandou finished=true) ======
    if ($finished) {
        return ['stage' => 'FECHAMENTO', 'finished' => true];
    }
    
    // ====== REGRA 2: PRECO ======
    $hasPriceTerms = preg_match('/(à vista|parcel|pagamento|pix|12x|valor anual|por mês|por ano|mensais|mensal)/iu', $messageLower);
    if ($hasPrice && $hasPriceTerms) {
        return ['stage' => 'PRECO', 'finished' => false];
    }
    
    // ====== REGRA 3: PROPOSTA ======
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
    
    $hasStructureProposal = ($hasPlan || $pageCount >= 2 || !empty($suggestedPlan));
    $proposalTerms = preg_match('/(ideal para voc[êe]|inclui|recomendo|estrutura|baseado no|pacote|configuração)/iu', $messageLower);
    
    if ($hasStructureProposal || ($hasPlan && $proposalTerms)) {
        if ($stage === 'EXPLORACAO' || $stage === 'ABERTURA') {
            return ['stage' => 'PROPOSTA', 'finished' => false];
        }
    }
    
    // ====== REGRA 4: Não regredir ======
    $validStages = ['ABERTURA', 'EXPLORACAO', 'PROPOSTA', 'PRECO', 'FECHAMENTO'];
    if (in_array($stage, $validStages)) {
        return ['stage' => $stage, 'finished' => $finished];
    }
    
    return ['stage' => 'EXPLORACAO', 'finished' => false];
}

/**
 * Wrapper de compatibilidade (caso algo ainda chame a função antiga)
 */
function validateAndCorrectStage(array $parsedResponse): string {
    $result = validateAndCorrectStageAndFinished($parsedResponse);
    return $result['stage'];
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
