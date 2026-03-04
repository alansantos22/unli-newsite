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

// ============================================
// HANDLER GLOBAL DE ERROS
// Garante que QUALQUER erro retorne JSON válido
// ============================================
set_error_handler(function($severity, $message, $file, $line) {
    // Converter warnings/notices em exceções apenas para erros graves
    if ($severity & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR)) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
    // Logar warnings sem matar o script
    error_log("⚠️ [sdr-chat] PHP Warning [{$severity}]: {$message} em {$file}:{$line}");
    return true; // Não propagar
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Limpar qualquer output anterior
        if (ob_get_level()) ob_end_clean();
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => 'Erro interno do servidor. Tente novamente em alguns segundos.',
            'debug_hint' => $error['message'] . ' em ' . basename($error['file']) . ':' . $error['line']
        ], JSON_UNESCAPED_UNICODE);
    }
});

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

// Carregar logger de conversas (graceful — não impede o funcionamento se falhar)
$conversationLoggerAvailable = false;
try {
    require_once __DIR__ . '/../lib/conversation-logger.php';
    $conversationLoggerAvailable = true;
} catch (Throwable $e) {
    error_log('⚠️ [sdr-chat] Falha ao carregar conversation-logger (não crítico): ' . $e->getMessage());
}

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

// Obter ou criar conversa para logging (graceful — funciona sem DB)
$inputConversationId = $input['conversation_id'] ?? null;
$conversationDbId = null;
$conversationPublicId = $inputConversationId;

if ($conversationLoggerAvailable && function_exists('sdr_get_or_create_conversation')) {
    try {
        $conversation = sdr_get_or_create_conversation($inputConversationId);
        $conversationDbId = $conversation ? $conversation['id'] : null;
        $conversationPublicId = $conversation ? $conversation['conversation_id'] : ($inputConversationId ?? null);
    } catch (Throwable $e) {
        error_log('⚠️ [sdr-chat] Falha ao criar/obter conversa (não crítico): ' . $e->getMessage());
    }
}

// Extrair a última mensagem do usuário (é sempre a última do array)
$lastUserMessage = null;
$messages = $input['messages'];
for ($i = count($messages) - 1; $i >= 0; $i--) {
    if ($messages[$i]['role'] === 'user') {
        $lastUserMessage = $messages[$i]['content'];
        break;
    }
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
    // FONTE DE VERDADE: O agente envia as páginas e addons que o
    // cliente quer — idêntico ao formulário/configurador.
    // Aqui convertemos o suggestedPlan para o mesmo formato que
    // price.php recebe do formulário, e chamamos as mesmas funções.
    // ============================================================
    $realPricing = null;
    $suggestedPlan = $parsedResponse['suggestedPlan'] ?? null;
    
    // ============================================================
    // 🛡️ SAFETY NET: Quando a IA esquece de incluir suggestedPlan.pages
    // (ex: responde sobre forma de pagamento sem repetir o plano),
    // usar o último suggestedPlan enviado pelo frontend como fallback.
    // ============================================================
    $lastSuggestedPlan = $input['last_suggested_plan'] ?? null;
    
    if (empty($suggestedPlan['pages']) && !empty($lastSuggestedPlan['pages'])) {
        error_log('🔧 [sdr-chat] SAFETY NET: suggestedPlan.pages vazio na resposta da IA. Usando last_suggested_plan do frontend como fallback.');
        // Mesclar: manter campos que a IA atualizou (paymentMethod, specialistOnboarding)
        // mas recuperar pages e type do plano anterior
        $fallback = $lastSuggestedPlan;
        if (!is_array($suggestedPlan)) {
            $suggestedPlan = $fallback;
        } else {
            $suggestedPlan['pages'] = $suggestedPlan['pages'] ?: $fallback['pages'];
            $suggestedPlan['type'] = $suggestedPlan['type'] ?: ($fallback['type'] ?? 'site_complete');
            $suggestedPlan['content'] = $suggestedPlan['content'] ?: ($fallback['content'] ?? []);
            $suggestedPlan['video_basic_quantity'] = $suggestedPlan['video_basic_quantity'] ?? ($fallback['video_basic_quantity'] ?? 0);
            $suggestedPlan['video_pro_quantity'] = $suggestedPlan['video_pro_quantity'] ?? ($fallback['video_pro_quantity'] ?? 0);
            // specialistOnboarding e paymentMethod: manter o da resposta atual (pode ter mudado)
            if (!isset($suggestedPlan['specialistOnboarding'])) {
                $suggestedPlan['specialistOnboarding'] = $fallback['specialistOnboarding'] ?? false;
            }
        }
        $parsedResponse['suggestedPlan'] = $suggestedPlan;
    }
    
    // ============================================================
    // 🛡️ SAFETY NET: Auto-detectar specialist pela mensagem da IA
    // Se a IA diz "incluir o especialista" mas JSON tem specialistOnboarding=false,
    // forçar para true (Gemini frequentemente esquece de setar o campo)
    // ============================================================
    $msgLower = mb_strtolower($parsedResponse['message'] ?? '', 'UTF-8');
    $specialistTextPatterns = [
        'incluir o atendimento com especialista',
        'incluir o especialista',
        'adicionar o atendimento com especialista',
        'com o atendimento com especialista',
        'incluir o atendente',
        'vou incluir o atendimento',
        'atendimento com especialista no seu pedido',
        'especialista incluso',
        'com especialista incluso',
    ];
    $msgMentionsSpecialist = false;
    foreach ($specialistTextPatterns as $pattern) {
        if (mb_strpos($msgLower, $pattern) !== false) {
            $msgMentionsSpecialist = true;
            break;
        }
    }
    
    if ($msgMentionsSpecialist && is_array($suggestedPlan) && empty($suggestedPlan['specialistOnboarding'])) {
        error_log('🔧 [sdr-chat] SAFETY NET: IA mencionou especialista na mensagem mas specialistOnboarding=false. Forçando para true.');
        $suggestedPlan['specialistOnboarding'] = true;
        $parsedResponse['suggestedPlan']['specialistOnboarding'] = true;
    }
    
    // Log de specialist para diagnóstico
    error_log('🎯 [sdr-chat] SPECIALIST STATE: suggestedPlan.specialistOnboarding=' 
        . json_encode($suggestedPlan['specialistOnboarding'] ?? null)
        . ' | msgMentionsSpecialist=' . ($msgMentionsSpecialist ? 'YES' : 'NO'));
    
    // ============================================================
    // SAFETY NET: Se a IA usou placeholders de preço mas NÃO incluiu
    // suggestedPlan.pages, tentar extrair as páginas da mensagem.
    // Isso resolve o bug "valor sob consulta" quando Gemini esquece
    // de popular o campo pages no JSON.
    // ============================================================
    $msgHasPlaceholders = strpos($parsedResponse['message'], '{{PRECO_MENSAL}}') !== false
                       || strpos($parsedResponse['message'], '{{PRECO_AVISTA}}') !== false;
    
    if ($msgHasPlaceholders && empty($suggestedPlan['pages'])) {
        error_log('🔧 [sdr-chat] Safety net: mensagem tem placeholders mas suggestedPlan.pages vazio. Tentando extrair páginas da mensagem...');
        
        $extractedPages = extractPagesFromMessage($parsedResponse['message']);
        
        if (!empty($extractedPages)) {
            if (!is_array($suggestedPlan)) {
                $suggestedPlan = ['type' => 'site_complete'];
                $parsedResponse['suggestedPlan'] = $suggestedPlan;
            }
            $suggestedPlan['pages'] = $extractedPages;
            $parsedResponse['suggestedPlan']['pages'] = $extractedPages;
            error_log('🔧 [sdr-chat] Safety net: páginas extraídas da mensagem: ' . json_encode($extractedPages));
        } else {
            error_log('⚠️ [sdr-chat] Safety net: não foi possível extrair páginas da mensagem. Placeholders ficarão sem substituição.');
        }
    }
    
    if (!empty($suggestedPlan['pages'])) {
        try {
            require_once __DIR__ . '/../lib/pricing.php';
            $cfg = load_pricing_config(__DIR__ . '/../pricing.json');
            
            // Converter suggestedPlan → mesmo formato que o formulário envia
            $formSelection = sdrPlanToFormSelection($suggestedPlan);
            
            error_log('📋 [sdr-chat] Seleção convertida (igual formulário): ' . json_encode($formSelection));
            
            // Chamar EXATAMENTE as mesmas funções que price.php usa
            $normalizedSel = normalize_selection($formSelection, $cfg);
            $realPricing   = compute_price($normalizedSel, $cfg);
            
            // Calcular economia à vista (diferença entre total parcelado e à vista)
            if (isset($realPricing['parcelado_total']) && isset($realPricing['avista'])) {
                $realPricing['economia_avista'] = round($realPricing['parcelado_total'] - $realPricing['avista'], 2);
            }
            
            // Remover campos de preço inventados pela IA (o backend é a fonte de verdade)
            unset($parsedResponse['suggestedPlan']['estimatedPrice']);
            unset($parsedResponse['suggestedPlan']['monthlyPrice']);
            unset($parsedResponse['suggestedPlan']['totalPrice']);
            unset($parsedResponse['suggestedPlan']['price']);
            
            // ============================================================
            // SUBSTITUIÇÃO SEGURA POR PLACEHOLDERS
            // A IA foi instruída a usar {{PRECO_MENSAL}} e {{PRECO_AVISTA}}
            // no lugar de valores monetários do plano. Aqui substituímos
            // esses placeholders pelos valores REAIS calculados pelo backend.
            // Isso NUNCA toca em valores contextuais do cliente (ex: faturamento).
            // ============================================================
            $msg = $parsedResponse['message'];
            
            if (isset($realPricing['parcela_12'])) {
                $labelMensal = 'R$ ' . number_format($realPricing['parcela_12'], 2, ',', '.');
                $msg = str_replace('{{PRECO_MENSAL}}', $labelMensal, $msg);
            }
            
            if (isset($realPricing['avista'])) {
                $labelAvista = 'R$ ' . number_format($realPricing['avista'], 2, ',', '.');
                $msg = str_replace('{{PRECO_AVISTA}}', $labelAvista, $msg);
            }
            
            if (isset($realPricing['economia_avista'])) {
                $labelEconomia = 'R$ ' . number_format($realPricing['economia_avista'], 2, ',', '.');
                $msg = str_replace('{{ECONOMIA_AVISTA}}', $labelEconomia, $msg);
            }
            
            $parsedResponse['message'] = $msg;
            
            error_log('💰 [sdr-chat] Preço real computado: parcela=' . ($realPricing['parcela_12'] ?? '-') . ' avista=' . ($realPricing['avista'] ?? '-'));
            error_log('📋 [sdr-chat] Páginas usadas: ' . json_encode(array_keys(array_filter($normalizedSel['pages']))));
            
        } catch (Exception $pricingEx) {
            error_log('⚠️ [sdr-chat] Falha ao computar preço real: ' . $pricingEx->getMessage());
        }
    }
    
    // ============================================================
    // LOG DA CONVERSA NO BANCO DE DADOS
    // ============================================================
    if ($conversationDbId && $conversationLoggerAvailable) {
        try {
            // Logar mensagem do usuário
            if ($lastUserMessage) {
                sdr_log_message($conversationDbId, 'user', $lastUserMessage, $correctedStage);
            }
            
            // Logar resposta da IA
            sdr_log_message($conversationDbId, 'assistant', $parsedResponse['message'], $correctedStage, [
                'pricing' => $realPricing,
                'suggested_plan' => $parsedResponse['suggestedPlan'] ?? null
            ]);
            
            // Atualizar dados da conversa
            sdr_update_conversation($conversationDbId, [
                'last_stage' => $correctedStage,
                'client_data' => $parsedResponse['clientData'] ?? null,
                'suggested_plan' => $parsedResponse['suggestedPlan'] ?? null,
                'finished' => $correctedFinished
            ]);
        } catch (Exception $logEx) {
            error_log('⚠️ [sdr-chat] Erro ao logar conversa (não crítico): ' . $logEx->getMessage());
        }
    }
    
    echo json_encode([
        'success' => true,
        'response' => $parsedResponse['message'],
        'stage' => $correctedStage,
        'clientData' => $parsedResponse['clientData'] ?? null,
        'suggestedPlan' => $parsedResponse['suggestedPlan'] ?? null,
        'pricing' => $realPricing, // Preço real calculado pelo backend
        'finished' => $correctedFinished,
        'conversation_id' => $conversationPublicId // ID para rastreamento
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
    // Especialista: R$169 é o preço FIXO do addon — sem desconto para PIX, sem acréscimo para cartão
    // Sempre 169/12 = mensal equivalente, independente da forma de pagamento
    $especialistaTotalAnual = $pricing['service_addons']['specialist_onboarding']['price'] ?? 169;
    $especialistaMensalCartao = round($especialistaTotalAnual / 12, 2);
    $especialistaMensalPix = $especialistaMensalCartao; // Mesmo valor — sem desconto
    $precoEspecialista = "R$ " . number_format($especialistaMensalCartao, 2, ',', '.');
    $precoEspecialistaPix = $precoEspecialista; // Mesmo placeholder
    $precoEspecialistaTotal = "R$ " . number_format($especialistaTotalAnual, 0, ',', '.');
    
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
    $prompt = str_replace('{{PRECO_ESPECIALISTA_PIX}}', $precoEspecialistaPix, $prompt);
    $prompt = str_replace('{{PRECO_ESPECIALISTA_TOTAL}}', $precoEspecialistaTotal, $prompt);
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
    // ⚠️ O preço listado em service_addons JÁ É o preço PARCELADO (cartão)
    // Para PIX à vista: preço ÷ 1.15
    if (isset($pricing['service_addons'])) {
        $tabela .= "## Add-ons de Serviço (REGRA ESPECIAL: preço já é o parcelado, NÃO aplicar 15%)\n";
        foreach ($pricing['service_addons'] as $key => $addon) {
            $precoParcelado = $addon['price'] ?? 0;
            $precoAvista = round($precoParcelado / 1.15, 2);
            $precoMensalCartao = round($precoParcelado / 12, 2);
            $precoMensalPix = round($precoAvista / 12, 2);
            $tabela .= "- **{$addon['name']}**: PARCELADO +R$ {$precoParcelado} total (R$ {$precoMensalCartao}/mês) | PIX À VISTA +R$ {$precoAvista}\n";
        }
        $tabela .= "\n";
    }
    
    // Tabela auxiliar de combinações comuns para a IA NÃO precisar calcular
    $tabela .= "## COMBINAÇÕES COMUNS PRÉ-CALCULADAS\n\n";
    $tabela .= "⚠️ Se o cliente escolher uma combinação que está aqui, use os valores EXATOS.\n";
    $tabela .= "Se não estiver aqui, some os valores ANUAIS das páginas + base e calcule PARCELADO = (total * 1.15) / 12.\n\n";
    
    if (isset($pricing['page_addons'])) {
        // Gerar combinações frequentes
        $commonCombos = [
            ['about', 'contact'],
            ['about', 'services', 'contact'],
            ['about', 'contact', 'showcase'],
            ['about', 'services', 'contact', 'showcase'],
            ['about', 'services', 'contact', 'faq'],
            ['about', 'services', 'contact', 'portfolio'],
            ['about', 'contact', 'blog'],
            ['about', 'contact', 'blog', 'showcase'],
        ];
        
        foreach ($commonCombos as $combo) {
            $comboTotal = $siteBasePrice;
            $comboNames = [];
            $valid = true;
            foreach ($combo as $pageKey) {
                if (!isset($pagesPrices[$pageKey])) { $valid = false; break; }
                $comboTotal += $pagesPrices[$pageKey];
                $comboNames[] = $pricing['page_addons'][$pageKey]['name'] ?? $pageKey;
            }
            if (!$valid) continue;
            
            $comboAvista = $comboTotal;
            $comboParcelado = round(($comboTotal * 1.15) / 12, 2);
            
            $tabela .= "- **Site Completo + " . implode(' + ', $comboNames) . "**: ";
            $tabela .= "À vista R$ {$comboAvista} | Mensal cartão R$ {$comboParcelado}/mês\n";
        }
        $tabela .= "\n";
    }
    
    $tabela .= "## REGRAS DE USO DESTA TABELA:\n";
    $tabela .= "1. NUNCA faça cálculos matemáticos. Use APENAS os valores acima.\n";
    $tabela .= "2. À vista PIX = valor anual listado (pagamento único — não mencione que é 'anual' separadamente, já está implícito).\n";
    $tabela .= "3. Parcelado cartão = valor mensal listado (12 parcelas que compõem a assinatura anual). SEMPRE diga que é 'assinatura anual de R$ X/mês'.\n";
    $tabela .= "4. NUNCA mencione 'desconto de 15% no PIX' — à vista é o preço normal.\n";
    $tabela .= "5. NUNCA mostre o total anual parcelado (ex: 'total R$ X.XXX ao fim de 12 meses') — o cliente verá isso na plataforma de pagamento.\n";
    $tabela .= "6. Se o cliente pedir combinação customizada NÃO listada acima, some os valores ANUAIS das páginas + produto base para obter o avista, e calcule PARCELADO = (avista * 1.15) / 12.\n";
    $tabela .= "\n## ⚠️ REGRA CRÍTICA DO JSON suggestedPlan:\n";
    $tabela .= "- SEMPRE que sua mensagem mencionar um plano, páginas ou preço, o campo `suggestedPlan.pages` DEVE conter TODAS as páginas propostas\n";
    $tabela .= "- NUNCA omita suggestedPlan quando falar de preço — o backend usa as páginas para calcular o preço real\n";
    $tabela .= "- NÃO use campo \"addons\" — TUDO vai em \"pages\"\n";
    
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
        'parts' => [['text' => $systemPrompt . "\n\n---\nLEMBRETES FINAIS CRÍTICOS:\n\n## FORMATO DE RESPOSTA (ANTI-VAZAMENTO):\n- Sua resposta deve ser UM ÚNICO JSON válido. NADA antes, NADA depois.\n- O campo \"message\" contém APENAS o texto visível ao cliente. ZERO dados técnicos.\n- NUNCA escreva texto fora do JSON. NUNCA separe texto e JSON. O cliente VÊ tudo que você escreve fora do JSON.\n- NUNCA inclua estimatedPrice, monthlyPrice ou cálculos de preço no JSON.\n- suggestedPlan.type DEVE ser \"site_complete\" ou \"landing\". NUNCA \"vitrine\", \"blog\" ou outro valor.\n\n## STAGES:\n- Se minha resposta SUGERE UM PLANO/PACOTE ou LISTA PÁGINAS = stage OBRIGATÓRIO: \"PROPOSTA\"\n- Se minha resposta MENCIONA VALORES ou PLACEHOLDERS de preço = stage OBRIGATÓRIO: \"PRECO\"\n- EXPLORACAO é APENAS quando estou perguntando sobre o negócio, SEM sugerir plano nenhum\n\n## PLACEHOLDERS DE PREÇO (OBRIGATÓRIO):\n- NUNCA escreva valores em R$ para o preço do plano. Use SEMPRE {{PRECO_MENSAL}} para parcela 12x, {{PRECO_AVISTA}} para valor à vista, {{ECONOMIA_AVISTA}} para economia.\n- O backend substitui os placeholders pelo valor real calculado.\n- NUNCA JAMAIS escreva frases como 'valor sob consulta', 'preço sob consulta', 'a confirmar' ou similares. SEMPRE use {{PRECO_MENSAL}} ou {{PRECO_AVISTA}}.\n\n## suggestedPlan.pages (CRÍTICO):\n- SEMPRE que mencionar preço, plano ou páginas, suggestedPlan.pages DEVE conter TODAS as páginas propostas\n- NUNCA omita suggestedPlan quando falar de preço\n- NUNCA omita suggestedPlan.pages — sem pages, o preço não será calculado"]]
    ];
    $contents[] = [
        'role' => 'model',
        'parts' => [['text' => json_encode([
            'message' => 'Entendido! Estou pronto para conversar como o Assistente Unli. Vou ser natural e humanizado. Confirmo: resposta sempre em JSON único, message só com texto do cliente, nunca vazar dados técnicos. Preços sempre com placeholders. suggestedPlan.type sempre "site_complete" ou "landing". Pages sempre preenchido quando falar de plano/preço.',
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
        'tudo certo ou tem mais alguma',
        'tudo certo ou tem alguma',
        'vou te encaminhar',
        'direcionar para o pagamento',
    ];
    
    $isClosingMessage = false;
    foreach ($closingPatterns as $pattern) {
        if (mb_strpos($messageLower, $pattern) !== false) {
            $isClosingMessage = true;
            break;
        }
    }
    
    // Se é mensagem de fechamento E tem preço/valor/menção monetária E tem suggestedPlan com paymentMethod
    // → forçar finished=true e stage=FECHAMENTO
    $hasPrice = preg_match('/R\$\s*[\d.,]+/', $message) || 
                preg_match('/\{\{PRECO_(MENSAL|AVISTA|ECONOMIA_AVISTA)\}\}/', $message) ||
                mb_strpos($messageLower, 'valor sob consulta') !== false ||
                mb_strpos($messageLower, '/mês') !== false ||
                mb_strpos($messageLower, 'parcela') !== false;
    $hasPaymentMethod = !empty($suggestedPlan['paymentMethod']);
    $hasPlanPages = !empty($suggestedPlan['pages']);
    
    if ($isClosingMessage && $hasPaymentMethod) {
        // Mensagem de fechamento + método de pagamento = FECHAMENTO
        error_log('🔧 [validateStage] Forçando FECHAMENTO + finished=true (mensagem de fechamento + paymentMethod)');
        return ['stage' => 'FECHAMENTO', 'finished' => true];
    }
    
    if ($isClosingMessage && ($hasPrice || $hasPlanPages)) {
        // Só permitir fechamento se paymentMethod estiver presente
        if ($hasPaymentMethod) {
            error_log('🔧 [validateStage] Forçando FECHAMENTO + finished=true (mensagem de fechamento + preço/plano + paymentMethod)');
            return ['stage' => 'FECHAMENTO', 'finished' => true];
        } else {
            error_log('⚠️ [validateStage] Mensagem de fechamento detectada MAS sem paymentMethod. Bloqueando finished.');
            return ['stage' => 'PRECO', 'finished' => false];
        }
    }
    
    // Se é mensagem de fechamento E já falou preço antes (stage=PRECO) → forçar APENAS se tem paymentMethod
    if ($isClosingMessage && ($stage === 'PRECO' || $stage === 'FECHAMENTO')) {
        if ($hasPaymentMethod) {
            error_log('🔧 [validateStage] Forçando FECHAMENTO + finished=true (fechamento após preço + paymentMethod)');
            return ['stage' => 'FECHAMENTO', 'finished' => true];
        } else {
            error_log('⚠️ [validateStage] Fechamento sem paymentMethod. Mantendo PRECO.');
            return ['stage' => 'PRECO', 'finished' => false];
        }
    }
    
    // ====== REGRA 1: FECHAMENTO (Gemini mandou finished=true) ======
    // ⚠️ BLOQUEIO: Não permitir finished=true sem paymentMethod definido
    if ($finished) {
        if ($hasPaymentMethod) {
            return ['stage' => 'FECHAMENTO', 'finished' => true];
        } else {
            error_log('⚠️ [validateStage] Gemini marcou finished=true MAS paymentMethod está vazio. Bloqueando.');
            return ['stage' => 'PRECO', 'finished' => false];
        }
    }
    
    // ====== REGRA 2: PRECO ======
    $hasPriceTerms = preg_match('/(à vista|parcel|pagamento|pix|12x|valor anual|por mês|por ano|mensais|mensal)/iu', $messageLower);
    $hasPricePlaceholders = preg_match('/\{\{PRECO_(MENSAL|AVISTA)\}\}/', $message);
    if (($hasPrice && $hasPriceTerms) || $hasPricePlaceholders) {
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
        return sanitizeSDRResponse($decoded);
    }
    
    // Limpar possíveis marcadores de código no início e fim
    $cleanResponse = preg_replace('/^```json\s*/i', '', $rawResponse);
    $cleanResponse = preg_replace('/\s*```$/i', '', $cleanResponse);
    $cleanResponse = trim($cleanResponse);
    
    $decoded = json_decode($cleanResponse, true);
    if ($decoded && isset($decoded['message'])) {
        return sanitizeSDRResponse($decoded);
    }
    
    // Tentar extrair JSON de dentro de bloco ```json ... ``` (pode estar no meio do texto)
    if (preg_match('/```json\s*(\{[\s\S]*?\})\s*```/i', $rawResponse, $matches)) {
        $extracted = json_decode($matches[1], true);
        if ($extracted && isset($extracted['message'])) {
            return sanitizeSDRResponse($extracted);
        }
    }
    
    // Tentar extrair JSON de dentro de bloco ``` ... ``` (sem especificar json)
    if (preg_match('/```\s*(\{[\s\S]*?"message"[\s\S]*?\})\s*```/i', $rawResponse, $matches)) {
        $extracted = json_decode($matches[1], true);
        if ($extracted && isset($extracted['message'])) {
            return sanitizeSDRResponse($extracted);
        }
    }
    
    // ============================================================
    // SAFETY NET: IA retornou texto + JSON separados (sem "message" no JSON)
    // Exemplo: "Texto da mensagem aqui...\n{"stage": "FECHAMENTO", ...}"
    // Separa o texto (= message) do JSON (= metadata)
    // ============================================================
    $metadataJson = extractMetadataJsonFromText($rawResponse);
    if ($metadataJson !== null) {
        error_log('🔧 [parseSDR] Safety net: texto + JSON separados detectado. Recombinando.');
        return sanitizeSDRResponse($metadataJson);
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
    
    // Fallback: retorna texto puro como mensagem (limpando JSON residual)
    $cleanMessage = cleanJsonFromMessage($rawResponse);
    return [
        'message' => $cleanMessage,
        'stage' => 'EXPLORACAO',
        'clientData' => null,
        'suggestedPlan' => null,
        'finished' => false
    ];
}

/**
 * Extrai JSON de metadados que a IA enviou separado do texto.
 * Quando a IA retorna "Texto da mensagem\n{\"stage\": \"X\", ...}" sem campo "message" no JSON.
 * Recombina o texto como message e os metadados do JSON.
 */
function extractMetadataJsonFromText(string $rawResponse): ?array {
    // Procurar por um bloco JSON que NÃO tenha "message" mas tenha "stage" ou "clientData"
    // Isso indica que a IA separou texto e metadados
    $jsonStart = -1;
    $len = strlen($rawResponse);
    
    // Procurar de trás para frente o último '{' que abre um JSON de metadados
    for ($i = $len - 1; $i >= 0; $i--) {
        if ($rawResponse[$i] === '}') {
            // Encontrou o fim de um possível JSON, agora buscar o início correspondente
            $depth = 0;
            for ($j = $i; $j >= 0; $j--) {
                if ($rawResponse[$j] === '}') $depth++;
                if ($rawResponse[$j] === '{') $depth--;
                if ($depth === 0) {
                    $jsonStart = $j;
                    break;
                }
            }
            break;
        }
    }
    
    if ($jsonStart < 0) return null;
    
    $jsonStr = substr($rawResponse, $jsonStart);
    $metadata = json_decode($jsonStr, true);
    
    if (!$metadata || !is_array($metadata)) return null;
    
    // Verificar que é um JSON de metadados (tem stage, clientData ou suggestedPlan mas NÃO tem message)
    $hasMetadataFields = isset($metadata['stage']) || isset($metadata['clientData']) || isset($metadata['suggestedPlan']) || isset($metadata['finished']);
    
    if (!$hasMetadataFields) return null;
    
    // Extrair a parte de texto (antes do JSON)
    $textPart = trim(substr($rawResponse, 0, $jsonStart));
    
    if (empty($textPart)) {
        // Se não tem texto antes, pode ser que o message está dentro do JSON afinal
        if (isset($metadata['message'])) return $metadata;
        return null;
    }
    
    // Recombinar: texto vira message, merge com metadados
    $result = $metadata;
    $result['message'] = $textPart;
    
    return $result;
}

/**
 * Remove blocos JSON residuais que possam ter vazado para o texto da mensagem.
 * Usado como último fallback para garantir que o cliente nunca veja JSON.
 */
function cleanJsonFromMessage(string $message): string {
    // Remover blocos que parecem JSON (começam com { e têm campos como stage, clientData, etc)
    $cleaned = preg_replace('/\{[\s]*"(?:stage|clientData|suggestedPlan|finished|niche|type)"[^}]*(?:\{[^}]*\}[^}]*)*\}/s', '', $message);
    return trim($cleaned);
}

/**
 * Garante que o campo message não contém JSON vazado.
 * Limpa metadados que a IA possa ter incluído dentro do texto da mensagem.
 */
function sanitizeSDRResponse(array $response): array {
    if (isset($response['message'])) {
        // Remover JSON de metadados que possam estar dentro do campo message
        $msg = $response['message'];
        
        // Detectar se o message contém um bloco JSON de metadados no final
        $lastBrace = strrpos($msg, '}');
        if ($lastBrace !== false) {
            // Procurar o '{' correspondente
            $depth = 0;
            $jsonStart = -1;
            for ($i = $lastBrace; $i >= 0; $i--) {
                if ($msg[$i] === '}') $depth++;
                if ($msg[$i] === '{') $depth--;
                if ($depth === 0) {
                    $jsonStart = $i;
                    break;
                }
            }
            
            if ($jsonStart > 0) {
                $possibleJson = substr($msg, $jsonStart);
                $decoded = json_decode($possibleJson, true);
                if ($decoded && is_array($decoded)) {
                    $hasMetadata = isset($decoded['stage']) || isset($decoded['clientData']) || isset($decoded['suggestedPlan']) || isset($decoded['finished']) || isset($decoded['niche']);
                    if ($hasMetadata) {
                        $response['message'] = trim(substr($msg, 0, $jsonStart));
                        error_log('🧹 [sanitizeSDR] Removido JSON de metadados do campo message');
                        
                        // Merge dados do JSON interno se os campos principais estiverem ausentes
                        if (!isset($response['stage']) && isset($decoded['stage'])) {
                            $response['stage'] = $decoded['stage'];
                        }
                        if (!isset($response['clientData']) && isset($decoded['clientData'])) {
                            $response['clientData'] = $decoded['clientData'];
                        }
                        if (!isset($response['suggestedPlan']) && isset($decoded['suggestedPlan'])) {
                            $response['suggestedPlan'] = $decoded['suggestedPlan'];
                        }
                        if (!isset($response['finished']) && isset($decoded['finished'])) {
                            $response['finished'] = $decoded['finished'];
                        }
                    }
                }
            }
        }
    }
    return $response;
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

/**
 * Extrai nomes de páginas mencionadas na mensagem da IA.
 * Safety net para quando o Gemini esquece de popular suggestedPlan.pages
 * mas menciona as páginas no texto da mensagem.
 * 
 * @param string $message Mensagem da IA
 * @return array Lista de page keys (ex: ['about', 'contact', 'showcase'])
 */
function extractPagesFromMessage(string $message): array {
    $msg = mb_strtolower($message);
    
    // Mapa de termos em PT-BR → page key do pricing.json
    $pagePatterns = [
        'about'     => ['sobre n[óo]s', 'sobre a empresa', 'p[áa]gina sobre', 'quem somos'],
        'services'  => ['servi[çc]os', 'p[áa]gina de servi'],
        'portfolio' => ['portf[óo]lio', 'portif[óo]lio', 'galeria de projetos', 'trabalhos realizados'],
        'faq'       => ['\bfaq\b', 'perguntas frequentes', 'd[úu]vidas frequentes'],
        'contact'   => ['contato', 'fale conosco', 'formul[áa]rio de contato'],
        'blog'      => ['\bblog\b', 'not[íi]cias', 'artigos'],
        'showcase'  => ['vitrine', 'cat[áa]logo', 'vitrine de produtos'],
    ];
    
    $foundPages = [];
    
    foreach ($pagePatterns as $pageKey => $patterns) {
        foreach ($patterns as $pattern) {
            if (preg_match('/' . $pattern . '/ui', $msg)) {
                $foundPages[] = $pageKey;
                break; // uma vez encontrado, não precisa checar outros patterns
            }
        }
    }
    
    return $foundPages;
}

/**
 * Converte suggestedPlan do agente SDR para o MESMO formato
 * que o formulário/configurador envia para price.php.
 * 
 * O agente funciona como se estivesse preenchendo o formulário:
 * ele envia as páginas, content addons, quantidades de vídeo, etc.
 * 
 * Formato do formulário (SiteConfigurator):
 *   { product, pages: {about:1, contact:1}, content: ['video_basic','pdf'],
 *     video_basic_quantity: 3, video_pro_quantity: 0, custom_pages: [],
 *     service_addons: { specialist_onboarding: true } }
 * 
 * @param array $suggestedPlan Dados do agente SDR
 * @return array Seleção no formato idêntico ao formulário
 */
function sdrPlanToFormSelection(array $suggestedPlan): array {
    // pages: ["about","contact","showcase"] → {about:1, contact:1, showcase:1}
    $pagesMap = [];
    $allPages = $suggestedPlan['pages'] ?? [];
    foreach ($allPages as $pageKey) {
        if (is_string($pageKey)) {
            $pagesMap[$pageKey] = 1;
        }
    }
    
    // content: ["video_basic","pdf"] (array de keys ativados)
    $content = $suggestedPlan['content'] ?? [];
    
    // ============================================================
    // NORMALIZAR TIPO DO PRODUTO
    // A IA às vezes envia "vitrine", "blog", "site" etc. como type.
    // Os únicos tipos válidos são: "site_complete" e "landing".
    // Qualquer outro valor é mapeado para "site_complete".
    // ============================================================
    $rawType = $suggestedPlan['type'] ?? 'site_complete';
    $validTypes = ['site_complete', 'landing'];
    $productType = in_array($rawType, $validTypes) ? $rawType : 'site_complete';
    
    if ($rawType !== $productType) {
        error_log('🔧 [sdrPlanToForm] Tipo normalizado: "' . $rawType . '" → "' . $productType . '"');
    }
    
    return [
        'product'              => $productType,
        'pages'                => $pagesMap,
        'content'              => $content,
        'custom_pages'         => [],
        'video_basic_quantity' => intval($suggestedPlan['video_basic_quantity'] ?? 0),
        'video_pro_quantity'   => intval($suggestedPlan['video_pro_quantity'] ?? 0),
        'service_addons'       => [
            'specialist_onboarding' => !empty($suggestedPlan['specialistOnboarding'])
        ]
    ];
}
