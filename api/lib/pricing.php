<?php
/**
 * PRICING CORE - Server Authoritative Pricing System
 * 
 * Regra de ouro: O servidor é a fonte de verdade.
 * O frontend NUNCA envia preços, apenas escolhas.
 */

/**
 * Carrega configuração de preços do JSON
 */
function load_pricing_config(string $path): array {
    if (!file_exists($path)) {
        throw new Exception("Pricing config not found: $path");
    }
    
    $raw = file_get_contents($path);
    if ($raw === false) {
        throw new Exception("Cannot read pricing config");
    }
    
    $cfg = json_decode($raw, true);
    if (!is_array($cfg)) {
        throw new Exception("Invalid pricing config JSON");
    }
    
    return $cfg;
}

/**
 * Limita valor inteiro entre min e max
 */
function clamp_int($v, int $min, int $max): int {
    $n = intval($v);
    if ($n < $min) return $min;
    if ($n > $max) return $max;
    return $n;
}

/**
 * Normaliza e valida seleções do cliente
 * Aplica whitelist rígida e limites
 * 
 * @param array $input Seleções enviadas pelo cliente (não confiável)
 * @param array $cfg Configuração oficial do servidor
 * @return array Seleções normalizadas e seguras
 */
function normalize_selection(array $input, array $cfg): array {
    $products = $cfg['products'] ?? [];
    $pageAddons = $cfg['page_addons'] ?? [];
    $contentAddons = $cfg['content_addons'] ?? [];
    $limits = $cfg['limits'] ?? ['extra_pages_max_for_checkout' => 5];
    
    // Validar produto (whitelist)
    $product = $input['product'] ?? '';
    if (!array_key_exists($product, $products)) {
        // Fallback seguro para primeiro produto válido
        $product = array_key_first($products);
    }
    
    // Validar páginas adicionais
    $pagesIn = $input['pages'] ?? [];
    $pages = [];
    $allowsExtraPages = $products[$product]['allows_extra_pages'] ?? false;
    
    foreach ($pageAddons as $key => $_) {
        if ($allowsExtraPages) {
            // Limitar quantidade
            $pages[$key] = clamp_int(
                $pagesIn[$key] ?? 0, 
                0, 
                $limits['extra_pages_max_for_checkout']
            );
        } else {
            // Landing Page não permite páginas extras
            $pages[$key] = 0;
        }
    }
    
    // Validar conteúdo pesado (aceita tanto array quanto object)
    $contentIn = $input['content'] ?? [];
    $content = [];
    
    // Se é array ['pdf', 'video'], converter para object { pdf: true, video: true }
    if (is_array($contentIn) && array_keys($contentIn) === range(0, count($contentIn) - 1)) {
        // É array indexado: ['pdf', 'video']
        $contentInAsObject = [];
        foreach ($contentIn as $item) {
            if (is_string($item)) {
                $contentInAsObject[$item] = true;
            }
        }
        $contentIn = $contentInAsObject;
    }
    
    // Validar cada addon de conteúdo
    foreach ($contentAddons as $key => $_) {
        $content[$key] = !empty($contentIn[$key]);
    }
    
    return [
        'product' => $product,
        'pages' => $pages,
        'content' => $content
    ];
}

/**
 * Calcula preço com base nas seleções normalizadas
 * Esta é a ÚNICA fonte de verdade para preços
 * 
 * @param array $selection Seleções já normalizadas
 * @param array $cfg Configuração oficial
 * @return array Breakdown completo de preços
 */
function compute_price(array $selection, array $cfg): array {
    $products = $cfg['products'];
    $pageAddons = $cfg['page_addons'];
    $contentAddons = $cfg['content_addons'];
    $rules = $cfg['pricing_rules'];
    
    // Preço base do produto
    $basePrice = floatval($products[$selection['product']]['base_price']);
    $subtotal = $basePrice;
    
    // Breakdown detalhado para transparência
    $breakdown = [
        'base' => [
            'name' => $products[$selection['product']]['name'],
            'price' => $basePrice
        ],
        'pages' => [],
        'content' => []
    ];
    
    // Páginas adicionais
    foreach ($selection['pages'] as $key => $qty) {
        if ($qty <= 0) continue;
        
        $pagePrice = floatval($pageAddons[$key]['price']);
        $totalPagePrice = $pagePrice * $qty;
        $subtotal += $totalPagePrice;
        
        $breakdown['pages'][] = [
            'key' => $key,
            'name' => $pageAddons[$key]['name'],
            'qty' => $qty,
            'unit_price' => $pagePrice,
            'total' => $totalPagePrice
        ];
    }
    
    // Conteúdo pesado
    foreach ($selection['content'] as $key => $enabled) {
        if (!$enabled) continue;
        
        $contentPrice = floatval($contentAddons[$key]['price']);
        $subtotal += $contentPrice;
        
        $breakdown['content'][] = [
            'key' => $key,
            'name' => $contentAddons[$key]['name'],
            'price' => $contentPrice
        ];
    }
    
    // Cálculos finais
    $cashDiscount = floatval($rules['cash_discount_percent']); // 10%
    $markup12 = floatval($rules['installments_12_markup_percent']); // 10%
    $installments = intval($rules['installments']); // 12
    
    $avista = round($subtotal * (1.0 - $cashDiscount / 100.0), 2);
    $parcelado_total = round($subtotal * (1.0 + $markup12 / 100.0), 2);
    $parcela_12 = round($parcelado_total / $installments, 2);
    
    // Ajuste de centavos na última parcela
    $parcela_12_adjusted = $parcela_12;
    $total_parcelas = $parcela_12 * $installments;
    $diferenca = round($parcelado_total - $total_parcelas, 2);
    
    return [
        'subtotal' => round($subtotal, 2),
        'avista' => $avista,
        'parcelado_total' => $parcelado_total,
        'parcela_12' => $parcela_12_adjusted,
        'installments' => $installments,
        'breakdown' => $breakdown,
        'adjustments' => [
            'cash_discount_percent' => $cashDiscount,
            'installments_markup_percent' => $markup12,
            'last_installment_diff' => $diferenca
        ]
    ];
}

/**
 * Valida briefing (dados do formulário)
 * 
 * @param array $briefing Dados enviados pelo cliente
 * @return array Erros de validação (vazio se OK)
 */
function validate_briefing(array $briefing): array {
    $errors = [];
    
    // Campos obrigatórios
    $required = ['company_name', 'whatsapp', 'email', 'style', 'main_content'];
    foreach ($required as $field) {
        if (empty($briefing[$field])) {
            $errors[] = "Campo obrigatório: $field";
        }
    }
    
    // Validar email
    if (!empty($briefing['email']) && !filter_var($briefing['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "E-mail inválido";
    }
    
    // Validar WhatsApp (apenas números)
    if (!empty($briefing['whatsapp']) && !preg_match('/^\d{10,11}$/', preg_replace('/\D/', '', $briefing['whatsapp']))) {
        $errors[] = "WhatsApp inválido (deve ter 10 ou 11 dígitos)";
    }
    
    // Validar estilo (whitelist)
    $validStyles = ['modern', 'elegant', 'tech'];
    if (!empty($briefing['style']) && !in_array($briefing['style'], $validStyles)) {
        $errors[] = "Estilo inválido";
    }
    
    return $errors;
}

/**
 * Sanitiza dados do briefing
 */
function sanitize_briefing(array $briefing): array {
    return [
        'company_name' => strip_tags(trim($briefing['company_name'] ?? '')),
        'whatsapp' => preg_replace('/\D/', '', $briefing['whatsapp'] ?? ''),
        'email' => filter_var($briefing['email'] ?? '', FILTER_SANITIZE_EMAIL),
        'address' => strip_tags(trim($briefing['address'] ?? '')),
        'instagram' => filter_var($briefing['instagram'] ?? '', FILTER_SANITIZE_URL),
        'google_maps' => filter_var($briefing['google_maps'] ?? '', FILTER_SANITIZE_URL),
        'style' => in_array($briefing['style'] ?? '', ['modern', 'elegant', 'tech']) 
            ? $briefing['style'] 
            : 'modern',
        'main_content' => strip_tags(trim($briefing['main_content'] ?? '')),
        'page_contents' => is_array($briefing['page_contents'] ?? null) 
            ? array_map('strip_tags', $briefing['page_contents']) 
            : [],
        'image_links' => strip_tags(trim($briefing['image_links'] ?? ''))
    ];
}
