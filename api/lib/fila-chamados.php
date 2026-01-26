<?php
/**
 * ============================================
 * FILA CHAMADOS - HELPER DE INTEGRAÇÃO
 * ============================================
 * 
 * Funções para criar tickets automaticamente
 * no sistema Fila Chamados a cada venda realizada.
 * 
 * Documentação: Veja EXTERNAL_API_DOCUMENTATION.md
 * 
 * @version 1.0.0
 * @author Sistema UNLI
 */

/**
 * Cria um ticket no Fila Chamados para uma nova venda
 * 
 * @param array $orderData Dados do pedido com informações do cliente e produto
 * @return array Resultado da criação do ticket (success, ticket_id, error)
 */
function createTicketForSale($orderData) {
    // Verificar se a integração está habilitada
    if (!defined('FILA_CHAMADOS_ENABLED') || !FILA_CHAMADOS_ENABLED) {
        return [
            'success' => false,
            'error' => 'Fila Chamados integration is disabled',
            'skipped' => true
        ];
    }
    
    // Validar API Key
    if (!defined('FILA_CHAMADOS_API_KEY') || FILA_CHAMADOS_API_KEY === 'SUA_API_KEY_AQUI') {
        error_log('⚠️ FILA CHAMADOS: API Key não configurada');
        return [
            'success' => false,
            'error' => 'API Key not configured',
            'skipped' => true
        ];
    }
    
    // Validar URL da API
    if (!defined('FILA_CHAMADOS_API_URL')) {
        error_log('⚠️ FILA CHAMADOS: URL da API não configurada');
        return [
            'success' => false,
            'error' => 'API URL not configured',
            'skipped' => true
        ];
    }
    
    // Extrair dados do pedido
    $customerName = $orderData['customer_name'] ?? $orderData['name'] ?? 'Cliente';
    $customerEmail = $orderData['customer_email'] ?? $orderData['email'] ?? '';
    $orderId = $orderData['order_id'] ?? $orderData['id'] ?? 'N/A';
    $planName = $orderData['plan_name'] ?? $orderData['product'] ?? 'Site';
    $amount = $orderData['amount'] ?? $orderData['total_amount'] ?? 0;
    $paymentMethod = $orderData['payment_method'] ?? 'desconhecido';
    $paymentId = $orderData['payment_id'] ?? null;
    
    // Validar email
    if (empty($customerEmail) || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        error_log('⚠️ FILA CHAMADOS: Email inválido ou não fornecido');
        return [
            'success' => false,
            'error' => 'Invalid or missing email address',
            'skipped' => true
        ];
    }
    
    // Construir assunto do ticket
    $subject = sprintf(
        '🎉 Nova Venda - %s (#%s)',
        $planName,
        $orderId
    );
    
    // Construir mensagem detalhada
    $message = buildTicketMessage($orderData, [
        'customer_name' => $customerName,
        'customer_email' => $customerEmail,
        'order_id' => $orderId,
        'plan_name' => $planName,
        'amount' => $amount,
        'payment_method' => $paymentMethod,
        'payment_id' => $paymentId
    ]);
    
    // Montar payload para API
    $payload = [
        'name' => $customerName,
        'email' => $customerEmail,
        'subject' => $subject,
        'message' => $message
    ];
    
    // Adicionar categoria se configurada
    if (defined('FILA_CHAMADOS_CATEGORY_SALE') && FILA_CHAMADOS_CATEGORY_SALE !== null) {
        $payload['category_id'] = (int) FILA_CHAMADOS_CATEGORY_SALE;
    } elseif (defined('FILA_CHAMADOS_CATEGORY_ID') && FILA_CHAMADOS_CATEGORY_ID !== null) {
        $payload['category_id'] = (int) FILA_CHAMADOS_CATEGORY_ID;
    }
    
    // Adicionar username se configurado
    if (defined('FILA_CHAMADOS_USERNAME') && !empty(FILA_CHAMADOS_USERNAME)) {
        $payload['username'] = FILA_CHAMADOS_USERNAME;
    }
    
    // Fazer requisição para API
    $result = makeFilaChamadosRequest($payload);
    
    // Log do resultado
    if ($result['success']) {
        error_log(sprintf(
            '✅ FILA CHAMADOS: Ticket #%s criado para pedido #%s (%s)',
            $result['ticket_id'],
            $orderId,
            $customerEmail
        ));
    } else {
        error_log(sprintf(
            '❌ FILA CHAMADOS: Falha ao criar ticket para pedido #%s - %s',
            $orderId,
            $result['error']
        ));
    }
    
    return $result;
}

/**
 * Constrói a mensagem detalhada do ticket
 * 
 * @param array $orderData Dados completos do pedido
 * @param array $summary Resumo extraído dos dados
 * @return string Mensagem formatada em HTML
 */
function buildTicketMessage($orderData, $summary) {
    $message = "<h2>🎉 Nova Venda Realizada</h2>\n\n";
    
    // Informações do Cliente
    $message .= "<h3>👤 Dados do Cliente</h3>\n";
    $message .= "<ul>\n";
    $message .= "<li><strong>Nome:</strong> {$summary['customer_name']}</li>\n";
    $message .= "<li><strong>Email:</strong> {$summary['customer_email']}</li>\n";
    
    if (isset($orderData['customer_phone']) && !empty($orderData['customer_phone'])) {
        $message .= "<li><strong>Telefone:</strong> {$orderData['customer_phone']}</li>\n";
    }
    
    $message .= "</ul>\n\n";
    
    // Informações do Pedido
    $message .= "<h3>📦 Detalhes do Pedido</h3>\n";
    $message .= "<ul>\n";
    $message .= "<li><strong>ID do Pedido:</strong> #{$summary['order_id']}</li>\n";
    $message .= "<li><strong>Produto/Plano:</strong> {$summary['plan_name']}</li>\n";
    $message .= "<li><strong>Valor:</strong> R$ " . number_format($summary['amount'], 2, ',', '.') . "</li>\n";
    $message .= "<li><strong>Forma de Pagamento:</strong> " . formatPaymentMethod($summary['payment_method']) . "</li>\n";
    
    if ($summary['payment_id']) {
        $message .= "<li><strong>ID do Pagamento:</strong> {$summary['payment_id']}</li>\n";
    }
    
    $message .= "<li><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</li>\n";
    $message .= "</ul>\n\n";
    
    // Detalhes Adicionais do Produto
    if (isset($orderData['selection'])) {
        $selection = $orderData['selection'];
        $message .= "<h3>🎨 Especificações do Produto</h3>\n";
        $message .= "<ul>\n";
        
        // Páginas - mostrar detalhadamente
        if (isset($selection['pages']) && is_array($selection['pages'])) {
            $pagesDetails = [];
            foreach ($selection['pages'] as $pageKey => $qty) {
                if ($qty > 0) {
                    $pagesDetails[] = "{$pageKey} (x{$qty})";
                }
            }
            if (!empty($pagesDetails)) {
                $message .= "<li><strong>Páginas Adicionais:</strong> " . implode(', ', $pagesDetails) . "</li>\n";
            } else {
                $message .= "<li><strong>Páginas Adicionais:</strong> Nenhuma</li>\n";
            }
        } elseif (isset($selection['pages'])) {
            $message .= "<li><strong>Páginas:</strong> {$selection['pages']}</li>\n";
        }
        
        // Conteúdo - mostrar detalhadamente
        if (isset($selection['content']) && is_array($selection['content'])) {
            $contentDetails = [];
            foreach ($selection['content'] as $contentKey => $enabled) {
                if ($enabled) {
                    $contentDetails[] = $contentKey;
                }
            }
            if (!empty($contentDetails)) {
                $message .= "<li><strong>Conteúdo Adicional:</strong> " . implode(', ', $contentDetails) . "</li>\n";
            } else {
                $message .= "<li><strong>Conteúdo Adicional:</strong> Nenhum</li>\n";
            }
        } elseif (isset($selection['content']) && !empty($selection['content'])) {
            $message .= "<li><strong>Conteúdo:</strong> " . ucfirst($selection['content']) . "</li>\n";
        }
        
        // Produto base
        if (isset($selection['product'])) {
            $message .= "<li><strong>Produto Base:</strong> {$selection['product']}</li>\n";
        }
        
        $message .= "</ul>\n\n";
    }
    
    // Mostrar order_details completo para referência
    if (isset($orderData['selection']) || isset($orderData['briefing'])) {
        $message .= "<h3>📋 Dados Completos do Pedido (JSON)</h3>\n";
        $message .= "<pre style=\"background:#f5f5f5; padding:10px; border-radius:5px; overflow:auto; font-size:12px;\">\n";
        $message .= htmlspecialchars(json_encode([
            'selection' => $orderData['selection'] ?? null,
            'briefing' => $orderData['briefing'] ?? null
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $message .= "\n</pre>\n\n";
    }
    
    // Briefing/Informações Adicionais
    if (isset($orderData['briefing']) && is_array($orderData['briefing']) && !empty($orderData['briefing'])) {
        $message .= "<h3>📝 Briefing do Cliente</h3>\n";
        $message .= "<ul>\n";
        
        $briefing = $orderData['briefing'];
        
        if (isset($briefing['companyName'])) {
            $message .= "<li><strong>Empresa:</strong> {$briefing['companyName']}</li>\n";
        }
        
        if (isset($briefing['businessType'])) {
            $message .= "<li><strong>Ramo:</strong> {$briefing['businessType']}</li>\n";
        }
        
        if (isset($briefing['description'])) {
            $message .= "<li><strong>Descrição:</strong> {$briefing['description']}</li>\n";
        }
        
        if (isset($briefing['targetAudience'])) {
            $message .= "<li><strong>Público-Alvo:</strong> {$briefing['targetAudience']}</li>\n";
        }
        
        if (isset($briefing['goals'])) {
            $message .= "<li><strong>Objetivos:</strong> {$briefing['goals']}</li>\n";
        }
        
        if (isset($briefing['hasLogo']) && $briefing['hasLogo'] === true) {
            $message .= "<li><strong>Possui Logo:</strong> Sim</li>\n";
            
            if (isset($briefing['logoUrl'])) {
                $message .= "<li><strong>URL do Logo:</strong> <a href='{$briefing['logoUrl']}' target='_blank'>{$briefing['logoUrl']}</a></li>\n";
            }
        }
        
        if (isset($briefing['colorPreferences'])) {
            $message .= "<li><strong>Preferências de Cores:</strong> {$briefing['colorPreferences']}</li>\n";
        }
        
        if (isset($briefing['referenceWebsites'])) {
            $message .= "<li><strong>Sites de Referência:</strong> {$briefing['referenceWebsites']}</li>\n";
        }
        
        $message .= "</ul>\n\n";
    }
    
    // Próximos Passos
    $message .= "<h3>⏭️ Próximos Passos</h3>\n";
    $message .= "<ol>\n";
    $message .= "<li>Confirmar recebimento do pagamento</li>\n";
    $message .= "<li>Entrar em contato com o cliente para alinhar detalhes</li>\n";
    $message .= "<li>Iniciar desenvolvimento do site</li>\n";
    $message .= "<li>Agendar reunião de apresentação</li>\n";
    $message .= "</ol>\n\n";
    
    $message .= "<hr>\n";
    $message .= "<p><em>Este ticket foi criado automaticamente pelo sistema de vendas.</em></p>";
    
    return $message;
}

/**
 * Formata o nome do método de pagamento para exibição
 * 
 * @param string $method Identificador do método de pagamento
 * @return string Nome formatado
 */
function formatPaymentMethod($method) {
    $methods = [
        'pix' => '💰 Pix',
        'credit_card' => '💳 Cartão de Crédito',
        'debit_card' => '💳 Cartão de Débito',
        'avista' => '💵 À Vista',
        '12x' => '💳 Parcelado (12x)',
        'boleto' => '🧾 Boleto'
    ];
    
    return $methods[$method] ?? ucfirst(str_replace('_', ' ', $method));
}

/**
 * Faz requisição HTTP para API do Fila Chamados
 * 
 * @param array $payload Dados a serem enviados
 * @return array Resultado da requisição (success, ticket_id, error, http_code)
 */
function makeFilaChamadosRequest($payload) {
    $apiUrl = FILA_CHAMADOS_API_URL;
    $apiKey = FILA_CHAMADOS_API_KEY;
    
    // Inicializar cURL
    $ch = curl_init($apiUrl);
    
    if ($ch === false) {
        return [
            'success' => false,
            'error' => 'cURL initialization failed',
            'http_code' => 0
        ];
    }
    
    // Configurar headers
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ];
    
    // Configurar opções do cURL
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 10
    ]);
    
    // Executar requisição
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    
    curl_close($ch);
    
    // Verificar erro de conexão
    if ($curlErrno !== 0) {
        return [
            'success' => false,
            'error' => 'cURL error: ' . $curlError,
            'http_code' => $httpCode,
            'errno' => $curlErrno
        ];
    }
    
    // Decodificar resposta
    $decodedResponse = json_decode($response, true);
    
    // Verificar sucesso (HTTP 200 ou 201)
    if ($httpCode === 200 || $httpCode === 201) {
        return [
            'success' => true,
            'ticket_id' => $decodedResponse['ticket_id'] ?? null,
            'status' => $decodedResponse['status'] ?? 'pending',
            'http_code' => $httpCode,
            'message' => $decodedResponse['message'] ?? 'Ticket created successfully'
        ];
    }
    
    // Extrair mensagem de erro
    $errorMessage = 'Unknown error';
    
    if (is_array($decodedResponse)) {
        if (isset($decodedResponse['error'])) {
            $errorMessage = $decodedResponse['error'];
        } elseif (isset($decodedResponse['message'])) {
            $errorMessage = $decodedResponse['message'];
        }
    } else {
        $errorMessage = "HTTP {$httpCode}: " . substr($response, 0, 100);
    }
    
    return [
        'success' => false,
        'error' => $errorMessage,
        'http_code' => $httpCode,
        'response' => $decodedResponse
    ];
}

/**
 * Cria um ticket no Fila Chamados quando o onboarding é concluído
 * Envia o briefing completo do cliente para iniciar a criação do site
 * 
 * @param array $orderData Dados do pedido
 * @param array $briefingData Dados do briefing preenchido pelo cliente
 * @return array Resultado da criação do ticket
 */
function createTicketForOnboarding($orderData, $briefingData) {
    // Verificar se a integração está habilitada
    if (!defined('FILA_CHAMADOS_ENABLED') || !FILA_CHAMADOS_ENABLED) {
        return [
            'success' => false,
            'error' => 'Fila Chamados integration is disabled',
            'skipped' => true
        ];
    }
    
    // Validar API Key
    if (!defined('FILA_CHAMADOS_API_KEY') || FILA_CHAMADOS_API_KEY === 'SUA_API_KEY_AQUI') {
        error_log('⚠️ FILA CHAMADOS: API Key não configurada');
        return [
            'success' => false,
            'error' => 'API Key not configured',
            'skipped' => true
        ];
    }
    
    // Extrair dados
    $customerName = $orderData['customer_name'] ?? 'Cliente';
    $customerEmail = $orderData['email'] ?? '';
    $orderId = $orderData['id'] ?? 'N/A';
    
    // Validar email
    if (empty($customerEmail) || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        error_log('⚠️ FILA CHAMADOS ONBOARDING: Email inválido ou não fornecido');
        return [
            'success' => false,
            'error' => 'Invalid or missing email address',
            'skipped' => true
        ];
    }
    
    // Construir assunto do ticket
    $subject = sprintf(
        '📋 Briefing Recebido - %s (#%s)',
        $customerName,
        $orderId
    );
    
    // Construir mensagem com o briefing
    $message = buildOnboardingTicketMessage($orderData, $briefingData);
    
    // Montar payload para API
    $payload = [
        'name' => $customerName,
        'email' => $customerEmail,
        'subject' => $subject,
        'message' => $message
    ];
    
    // Usar categoria de onboarding
    if (defined('FILA_CHAMADOS_CATEGORY_ONBOARDING') && FILA_CHAMADOS_CATEGORY_ONBOARDING !== null) {
        $payload['category_id'] = (int) FILA_CHAMADOS_CATEGORY_ONBOARDING;
    }
    
    // Adicionar username se configurado
    if (defined('FILA_CHAMADOS_USERNAME') && !empty(FILA_CHAMADOS_USERNAME)) {
        $payload['username'] = FILA_CHAMADOS_USERNAME;
    }
    
    // Fazer requisição para API
    $result = makeFilaChamadosRequest($payload);
    
    // Log do resultado
    if ($result['success']) {
        error_log(sprintf(
            '✅ FILA CHAMADOS ONBOARDING: Ticket #%s criado para pedido #%s (%s)',
            $result['ticket_id'],
            $orderId,
            $customerEmail
        ));
    } else {
        error_log(sprintf(
            '❌ FILA CHAMADOS ONBOARDING: Falha ao criar ticket para pedido #%s - %s',
            $orderId,
            $result['error']
        ));
    }
    
    return $result;
}

/**
 * Constrói a mensagem do ticket de onboarding com os dados do briefing
 * 
 * @param array $orderData Dados do pedido
 * @param array $briefingData Dados do briefing
 * @return string Mensagem formatada em HTML
 */
function buildOnboardingTicketMessage($orderData, $briefingData) {
    $customerName = $orderData['customer_name'] ?? 'Cliente';
    $customerEmail = $orderData['email'] ?? '';
    $orderId = $orderData['id'] ?? 'N/A';
    
    $message = "<h2>📋 Briefing do Site Recebido</h2>\n\n";
    $message .= "<p><strong>O cliente completou o onboarding e está pronto para iniciar o desenvolvimento!</strong></p>\n\n";
    
    // Informações do Cliente
    $message .= "<h3>👤 Dados do Cliente</h3>\n";
    $message .= "<ul>\n";
    $message .= "<li><strong>Nome:</strong> {$customerName}</li>\n";
    $message .= "<li><strong>Email:</strong> {$customerEmail}</li>\n";
    $message .= "<li><strong>ID do Pedido:</strong> #{$orderId}</li>\n";
    $message .= "</ul>\n\n";
    
    // Dados do Briefing
    $message .= "<h3>📝 Informações do Briefing</h3>\n";
    $message .= "<ul>\n";
    
    // Mapear campos do briefing para exibição amigável
    $fieldLabels = [
        'companyName' => 'Nome da Empresa',
        'businessType' => 'Ramo de Atuação',
        'description' => 'Descrição do Negócio',
        'targetAudience' => 'Público-Alvo',
        'goals' => 'Objetivos do Site',
        'hasLogo' => 'Possui Logo',
        'logoUrl' => 'URL do Logo',
        'colorPreferences' => 'Preferências de Cores',
        'primaryColor' => 'Cor Primária',
        'secondaryColor' => 'Cor Secundária',
        'referenceWebsites' => 'Sites de Referência',
        'socialMedia' => 'Redes Sociais',
        'contactPhone' => 'Telefone de Contato',
        'contactEmail' => 'Email de Contato',
        'address' => 'Endereço',
        'additionalInfo' => 'Informações Adicionais',
        'services' => 'Serviços Oferecidos',
        'products' => 'Produtos',
        'differentials' => 'Diferenciais',
        'deadline' => 'Prazo Desejado',
        'domain' => 'Domínio'
    ];
    
    foreach ($briefingData as $key => $value) {
        if (empty($value) || $key === 'step' || $key === 'lastSaved') continue;
        
        $label = $fieldLabels[$key] ?? ucfirst(str_replace(['_', '-'], ' ', $key));
        
        if (is_bool($value)) {
            $displayValue = $value ? 'Sim' : 'Não';
        } elseif (is_array($value)) {
            $displayValue = '<ul>' . implode('', array_map(function($item) {
                if (is_array($item)) {
                    return '<li>' . json_encode($item, JSON_UNESCAPED_UNICODE) . '</li>';
                }
                return '<li>' . htmlspecialchars($item) . '</li>';
            }, $value)) . '</ul>';
        } else {
            $displayValue = nl2br(htmlspecialchars($value));
        }
        
        $message .= "<li><strong>{$label}:</strong> {$displayValue}</li>\n";
    }
    
    $message .= "</ul>\n\n";
    
    // JSON completo para referência
    $message .= "<h3>📦 Dados Completos (JSON)</h3>\n";
    $message .= "<details>\n";
    $message .= "<summary>Clique para expandir</summary>\n";
    $message .= "<pre style=\"background:#f5f5f5; padding:10px; border-radius:5px; overflow:auto; font-size:12px; max-height:400px;\">\n";
    $message .= htmlspecialchars(json_encode($briefingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $message .= "\n</pre>\n";
    $message .= "</details>\n\n";
    
    // Próximos Passos
    $message .= "<h3>⏭️ Próximos Passos</h3>\n";
    $message .= "<ol>\n";
    $message .= "<li>Revisar as informações do briefing</li>\n";
    $message .= "<li>Preparar wireframe/layout baseado nas preferências</li>\n";
    $message .= "<li>Entrar em contato para alinhar detalhes se necessário</li>\n";
    $message .= "<li>Iniciar desenvolvimento do site</li>\n";
    $message .= "</ol>\n\n";
    
    $message .= "<hr>\n";
    $message .= "<p><em>Este ticket foi criado automaticamente quando o cliente finalizou o onboarding.</em></p>\n";
    $message .= "<p><em>Data: " . date('d/m/Y H:i:s') . "</em></p>";
    
    return $message;
}

/**
 * Testa a conexão com a API do Fila Chamados
 * Útil para validar configuração
 * 
 * @return array Resultado do teste
 */
function testFilaChamadosConnection() {
    if (!defined('FILA_CHAMADOS_ENABLED') || !FILA_CHAMADOS_ENABLED) {
        return [
            'success' => false,
            'message' => 'Integration is disabled in config.secure.php'
        ];
    }
    
    if (!defined('FILA_CHAMADOS_API_KEY') || FILA_CHAMADOS_API_KEY === 'SUA_API_KEY_AQUI') {
        return [
            'success' => false,
            'message' => 'API Key not configured in config.secure.php'
        ];
    }
    
    // Tentar criar um ticket de teste
    $testPayload = [
        'name' => 'Teste de Integração',
        'email' => 'teste@unli.com.br',
        'subject' => 'Teste de Conexão com API',
        'message' => 'Este é um ticket de teste gerado automaticamente para validar a integração.'
    ];
    
    // Adicionar categoria se configurada
    if (defined('FILA_CHAMADOS_CATEGORY_ID') && FILA_CHAMADOS_CATEGORY_ID !== null) {
        $testPayload['category_id'] = (int) FILA_CHAMADOS_CATEGORY_ID;
    }
    
    // Adicionar username se configurado
    if (defined('FILA_CHAMADOS_USERNAME') && !empty(FILA_CHAMADOS_USERNAME)) {
        $testPayload['username'] = FILA_CHAMADOS_USERNAME;
    }
    
    $result = makeFilaChamadosRequest($testPayload);
    
    if ($result['success']) {
        return [
            'success' => true,
            'message' => 'Connection successful! Ticket created.',
            'ticket_id' => $result['ticket_id']
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Connection failed: ' . $result['error'],
            'http_code' => $result['http_code']
        ];
    }
}

?>
