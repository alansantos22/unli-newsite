<?php
/**
 * ============================================
 * CONTENT GENERATOR FACTORY
 * Geração de Conteúdo via Gemini AI
 * ============================================
 * 
 * Factory Pattern para gerar conteúdo estruturado
 * baseado no tipo de página selecionado.
 * 
 * Usa Gemini 1.5 Flash com JSON Mode para
 * garantir output estruturado e consistente.
 */

class ContentGeneratorFactory
{
    private string $apiKey;
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    private int $timeout = 30;
    private int $connectTimeout = 10;
    
    /**
     * Tipos de página suportados e suas configurações
     */
    private array $pageTypes = [
        'sobre_nos' => [
            'label' => 'Sobre Nós',
            'input_placeholder' => 'Conte a história da sua empresa: quando começou, por que você abriu, o que te motiva...',
            'input_hint' => '💡 Quanto mais detalhes você der, melhor será o texto gerado.',
        ],
        'servicos' => [
            'label' => 'Serviços',
            'input_placeholder' => 'Liste seus serviços: Ex: Instalação elétrica, manutenção preventiva, projetos residenciais...',
            'input_hint' => '💡 Mencione também diferenciais como garantia, rapidez, etc.',
        ],
        'faq' => [
            'label' => 'FAQ - Perguntas Frequentes',
            'input_placeholder' => 'Digite dúvidas comuns ou descreva seu nicho: Ex: "Clínica odontológica em São Paulo"',
            'input_hint' => '💡 A IA vai criar perguntas que quebram objeções de venda.',
        ],
        'portfolio' => [
            'label' => 'Portfólio',
            'input_placeholder' => 'Descreva projetos que você já fez: cliente, problema, solução e resultado...',
            'input_hint' => '💡 Inclua números se tiver (Ex: aumentou 30% as vendas).',
        ],
        'blog_noticias' => [
            'label' => 'Blog / Notícias',
            'input_placeholder' => 'Qual seu nicho? Ex: "Marketing digital para dentistas" ou "Receitas veganas"',
            'input_hint' => '💡 A IA vai sugerir artigos otimizados para SEO.',
        ],
        'vitrine_produtos' => [
            'label' => 'Vitrine de Produtos',
            'input_placeholder' => 'Liste seus produtos: nome, preço, características principais...',
            'input_hint' => '💡 A IA vai criar descrições persuasivas para vender.',
        ],
        'depoimentos' => [
            'label' => 'Depoimentos',
            'input_placeholder' => 'Cole feedbacks de clientes ou descreva casos de sucesso...',
            'input_hint' => '💡 A IA vai estruturar para máximo impacto.',
        ],
        'contato' => [
            'label' => 'Contato',
            'input_placeholder' => 'Informe: endereço, telefone, horário de funcionamento, formas de atendimento...',
            'input_hint' => '💡 A IA vai criar textos convidativos para entrar em contato.',
        ],
        'personalizada' => [
            'label' => 'Página Personalizada',
            'input_placeholder' => 'Descreva exatamente o que você precisa: tipo de página, tom de voz, conteúdo...',
            'input_hint' => '💡 Flexibilidade total - descreva sua necessidade.',
        ],
    ];
    
    /**
     * Tons de voz disponíveis
     */
    private array $voiceTones = [
        'profissional' => 'Profissional e Confiante - Transmite autoridade e credibilidade',
        'amigavel' => 'Amigável e Acolhedor - Tom caloroso e próximo do cliente',
        'premium' => 'Sofisticado e Premium - Para marcas de alto padrão',
        'jovem' => 'Jovem e Dinâmico - Linguagem descontraída e moderna',
        'tecnico' => 'Técnico e Preciso - Foco em especificações e detalhes',
        'inspirador' => 'Inspirador e Motivacional - Conecta emocionalmente',
    ];
    
    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? getenv('GEMINI_API_KEY') ?? '';
        
        if (empty($this->apiKey)) {
            throw new Exception('GEMINI_API_KEY não configurada. Defina a variável de ambiente ou passe como parâmetro.');
        }
    }
    
    /**
     * Retorna os tipos de página disponíveis
     */
    public function getPageTypes(): array
    {
        return $this->pageTypes;
    }
    
    /**
     * Retorna os tons de voz disponíveis
     */
    public function getVoiceTones(): array
    {
        return $this->voiceTones;
    }
    
    /**
     * Gera conteúdo baseado no tipo de página
     */
    public function generate(string $pageType, string $userInput, string $voiceTone = 'profissional', array $extra = []): array
    {
        // Validar tipo de página
        if (!isset($this->pageTypes[$pageType])) {
            return [
                'success' => false,
                'error' => 'Tipo de página inválido: ' . $pageType,
                'valid_types' => array_keys($this->pageTypes)
            ];
        }
        
        // Validar input
        if (empty(trim($userInput))) {
            return [
                'success' => false,
                'error' => 'O campo de descrição não pode estar vazio.'
            ];
        }
        
        // Obter prompt específico para o tipo de página
        $systemInstruction = $this->getSystemInstruction($pageType, $voiceTone, $extra);
        
        // Chamar API do Gemini
        return $this->callGeminiAPI($systemInstruction, $userInput);
    }
    
    /**
     * Monta a instrução de sistema baseada no tipo de página
     */
    private function getSystemInstruction(string $pageType, string $voiceTone, array $extra): string
    {
        $toneDescription = $this->voiceTones[$voiceTone] ?? $this->voiceTones['profissional'];
        $companyName = $extra['company_name'] ?? 'a empresa';
        $niche = $extra['niche'] ?? '';
        
        $baseContext = "
CONTEXTO: Você é um Copywriter Sênior especializado em websites brasileiros.
IDIOMA DE SAÍDA: Português do Brasil (PT-BR). NUNCA responda em inglês.
TOM DE VOZ: {$toneDescription}.
EMPRESA: {$companyName}" . ($niche ? " - Nicho: {$niche}" : "") . "

REGRAS GERAIS:
1. Textos persuasivos focados em BENEFÍCIOS, não funcionalidades.
2. Use linguagem clara e acessível.
3. Evite jargões técnicos excessivos.
4. Foque em resolver a DOR do cliente.
5. Se o input contiver conteúdo impróprio, obsceno ou ilegal, retorne: {\"error\": \"Conteúdo não permitido\", \"success\": false}

FORMATO: Retorne APENAS JSON válido, sem markdown, sem explicações extras.
";

        $specificInstructions = match($pageType) {
            'sobre_nos' => $this->getSobreNosInstruction(),
            'servicos' => $this->getServicosInstruction(),
            'faq' => $this->getFaqInstruction(),
            'portfolio' => $this->getPortfolioInstruction(),
            'blog_noticias' => $this->getBlogInstruction(),
            'vitrine_produtos' => $this->getVitrineInstruction(),
            'depoimentos' => $this->getDepoimentosInstruction(),
            'contato' => $this->getContatoInstruction(),
            'personalizada' => $this->getPersonalizadaInstruction(),
            default => $this->getPersonalizadaInstruction()
        };
        
        return $baseContext . "\n" . $specificInstructions;
    }
    
    private function getSobreNosInstruction(): string
    {
        return "
TAREFA: Criar conteúdo para a seção 'Sobre Nós' do site.
OBJETIVO: Gerar autoridade, confiança e conexão emocional com o visitante.

ESTRUTURA JSON OBRIGATÓRIA:
{
    \"success\": true,
    \"page_type\": \"sobre_nos\",
    \"content\": {
        \"titulo_h1\": \"Título impactante (máx 60 caracteres)\",
        \"subtitulo\": \"Frase complementar que gera curiosidade\",
        \"historia\": \"Texto de 2-3 parágrafos contando a jornada da empresa de forma inspiradora. Inclua o porquê de existir.\",
        \"missao\": \"Frase curta e memorável sobre o propósito\",
        \"visao\": \"Onde a empresa quer chegar\",
        \"valores\": [\"Valor 1\", \"Valor 2\", \"Valor 3\", \"Valor 4\", \"Valor 5\"],
        \"diferencial_destaque\": \"O que torna esta empresa única\"
    },
    \"seo_metadata\": {
        \"meta_title\": \"Título SEO otimizado (máx 60 chars)\",
        \"meta_description\": \"Descrição para Google (máx 155 chars)\",
        \"keywords\": [\"palavra1\", \"palavra2\", \"palavra3\"]
    }
}";
    }
    
    private function getServicosInstruction(): string
    {
        return "
TAREFA: Criar conteúdo para a seção de Serviços do site.
OBJETIVO: Apresentar serviços de forma clara, destacando BENEFÍCIOS para o cliente.

ESTRUTURA JSON OBRIGATÓRIA:
{
    \"success\": true,
    \"page_type\": \"servicos\",
    \"content\": {
        \"titulo_secao\": \"Título chamativo para a seção\",
        \"subtitulo\": \"Frase que destaca o valor entregue\",
        \"intro\": \"Parágrafo introdutório (2-3 linhas)\",
        \"servicos\": [
            {
                \"nome\": \"Nome comercial do serviço\",
                \"descricao_curta\": \"Descrição focada em benefícios (máx 100 chars)\",
                \"descricao_completa\": \"Texto mais detalhado para página individual\",
                \"beneficios\": [\"Benefício 1\", \"Benefício 2\", \"Benefício 3\"],
                \"icone_sugestao\": \"nome-do-icone (ex: rocket, handshake, shield)\"
            }
        ],
        \"cta_text\": \"Texto do botão de ação\",
        \"garantia\": \"Texto sobre garantia ou compromisso\"
    },
    \"seo_metadata\": {
        \"meta_title\": \"Título SEO otimizado (máx 60 chars)\",
        \"meta_description\": \"Descrição para Google (máx 155 chars)\",
        \"keywords\": [\"palavra1\", \"palavra2\", \"palavra3\"]
    }
}";
    }
    
    private function getFaqInstruction(): string
    {
        return "
TAREFA: Criar FAQ (Perguntas Frequentes) que quebra objeções de venda.
OBJETIVO: Antecipar dúvidas e convencer o visitante a comprar/contratar.

REGRAS ESPECIAIS:
1. Crie 6-10 perguntas estratégicas.
2. Perguntas devem abordar: preço, qualidade, prazo, garantia, processo.
3. Respostas devem ser convincentes e eliminar receios.

ESTRUTURA JSON OBRIGATÓRIA:
{
    \"success\": true,
    \"page_type\": \"faq\",
    \"content\": {
        \"titulo_secao\": \"Título da seção FAQ\",
        \"subtitulo\": \"Frase acolhedora\",
        \"intro\": \"Texto introdutório curto\",
        \"perguntas\": [
            {
                \"pergunta\": \"A pergunta que o cliente faria\",
                \"resposta\": \"Resposta clara, objetiva e convincente\",
                \"categoria\": \"preco|qualidade|prazo|garantia|processo|outros\"
            }
        ],
        \"cta_duvida\": \"Texto convidando a entrar em contato para mais dúvidas\"
    },
    \"seo_metadata\": {
        \"meta_title\": \"Título SEO otimizado (máx 60 chars)\",
        \"meta_description\": \"Descrição para Google (máx 155 chars)\",
        \"keywords\": [\"palavra1\", \"palavra2\", \"palavra3\"]
    }
}";
    }
    
    private function getPortfolioInstruction(): string
    {
        return "
TAREFA: Criar conteúdo para seção de Portfólio/Cases de Sucesso.
OBJETIVO: Prova social - mostrar resultados reais para gerar confiança.

ESTRUTURA JSON OBRIGATÓRIA:
{
    \"success\": true,
    \"page_type\": \"portfolio\",
    \"content\": {
        \"titulo_secao\": \"Título impactante\",
        \"subtitulo\": \"Frase que destaca resultados\",
        \"intro\": \"Parágrafo introdutório\",
        \"projetos\": [
            {
                \"nome_projeto\": \"Nome ou cliente (pode ser genérico)\",
                \"segmento\": \"Área de atuação do cliente\",
                \"desafio\": \"O problema que existia antes\",
                \"solucao\": \"O que foi feito para resolver\",
                \"resultado\": \"O ganho mensurável (use números se possível)\",
                \"destaque\": true
            }
        ],
        \"metricas_gerais\": {
            \"projetos_entregues\": \"100+\",
            \"clientes_satisfeitos\": \"98%\",
            \"anos_mercado\": \"10+\"
        },
        \"cta_text\": \"Texto do botão de ação\"
    },
    \"seo_metadata\": {
        \"meta_title\": \"Título SEO otimizado (máx 60 chars)\",
        \"meta_description\": \"Descrição para Google (máx 155 chars)\",
        \"keywords\": [\"palavra1\", \"palavra2\", \"palavra3\"]
    }
}";
    }
    
    private function getBlogInstruction(): string
    {
        return "
TAREFA: Criar sugestões de artigos de blog otimizados para SEO.
OBJETIVO: Gerar tráfego orgânico e posicionar como autoridade.

REGRAS ESPECIAIS:
1. Crie 3-5 artigos sugeridos.
2. Títulos devem ser chamativos e conter palavras-chave.
3. O conteúdo HTML deve usar tags <p>, <h2>, <h3>, <ul>, <li>.

ESTRUTURA JSON OBRIGATÓRIA:
{
    \"success\": true,
    \"page_type\": \"blog_noticias\",
    \"content\": {
        \"titulo_secao\": \"Título para a seção de blog\",
        \"subtitulo\": \"Frase convidativa\",
        \"artigos\": [
            {
                \"titulo\": \"Título otimizado para SEO\",
                \"slug\": \"url-amigavel-do-artigo\",
                \"resumo\": \"Lead do artigo (2-3 linhas)\",
                \"conteudo_html\": \"<h2>Subtítulo</h2><p>Parágrafo...</p>\",
                \"tempo_leitura\": \"5 min\",
                \"categoria\": \"Nome da categoria\",
                \"tags\": [\"tag1\", \"tag2\", \"tag3\"]
            }
        ]
    },
    \"seo_metadata\": {
        \"meta_title\": \"Título SEO otimizado (máx 60 chars)\",
        \"meta_description\": \"Descrição para Google (máx 155 chars)\",
        \"keywords\": [\"palavra1\", \"palavra2\", \"palavra3\"]
    }
}";
    }
    
    private function getVitrineInstruction(): string
    {
        return "
TAREFA: Criar descrições persuasivas para vitrine de produtos.
OBJETIVO: Vender! Textos que destacam benefícios e geram desejo.

ESTRUTURA JSON OBRIGATÓRIA:
{
    \"success\": true,
    \"page_type\": \"vitrine_produtos\",
    \"content\": {
        \"titulo_secao\": \"Título chamativo\",
        \"subtitulo\": \"Frase de impacto\",
        \"produtos\": [
            {
                \"nome\": \"Nome do produto\",
                \"chamada_venda\": \"Descrição curta e persuasiva (máx 140 chars)\",
                \"descricao_completa\": \"Texto detalhado com benefícios\",
                \"beneficios\": [\"Benefício 1\", \"Benefício 2\"],
                \"specs_tecnicas\": [\"Spec 1\", \"Spec 2\"],
                \"destaque\": true,
                \"badge\": \"Mais Vendido|Novidade|Promoção|null\"
            }
        ],
        \"cta_text\": \"Texto do botão\"
    },
    \"seo_metadata\": {
        \"meta_title\": \"Título SEO otimizado (máx 60 chars)\",
        \"meta_description\": \"Descrição para Google (máx 155 chars)\",
        \"keywords\": [\"palavra1\", \"palavra2\", \"palavra3\"]
    }
}";
    }
    
    private function getDepoimentosInstruction(): string
    {
        return "
TAREFA: Estruturar depoimentos de clientes para máximo impacto.
OBJETIVO: Prova social através de histórias reais de sucesso.

ESTRUTURA JSON OBRIGATÓRIA:
{
    \"success\": true,
    \"page_type\": \"depoimentos\",
    \"content\": {
        \"titulo_secao\": \"Título impactante\",
        \"subtitulo\": \"Frase que reforça credibilidade\",
        \"depoimentos\": [
            {
                \"texto\": \"O depoimento editado/melhorado\",
                \"texto_destaque\": \"Frase mais impactante para destaque\",
                \"autor_nome\": \"Nome do cliente\",
                \"autor_cargo\": \"Cargo ou empresa\",
                \"autor_foto\": \"placeholder\",
                \"nota\": 5,
                \"destaque\": true
            }
        ],
        \"estatisticas\": {
            \"nota_media\": \"4.9\",
            \"total_avaliacoes\": \"200+\",
            \"recomendacao\": \"98%\"
        }
    },
    \"seo_metadata\": {
        \"meta_title\": \"Título SEO otimizado (máx 60 chars)\",
        \"meta_description\": \"Descrição para Google (máx 155 chars)\",
        \"keywords\": [\"palavra1\", \"palavra2\", \"palavra3\"]
    }
}";
    }
    
    private function getContatoInstruction(): string
    {
        return "
TAREFA: Criar conteúdo para página de contato.
OBJETIVO: Incentivar o visitante a entrar em contato.

ESTRUTURA JSON OBRIGATÓRIA:
{
    \"success\": true,
    \"page_type\": \"contato\",
    \"content\": {
        \"titulo_secao\": \"Título convidativo\",
        \"subtitulo\": \"Frase acolhedora\",
        \"intro\": \"Texto incentivando o contato\",
        \"info_contato\": {
            \"endereco_formatado\": \"Endereço completo formatado\",
            \"telefone_formatado\": \"(XX) XXXXX-XXXX\",
            \"email\": \"contato@empresa.com\",
            \"horario\": \"Seg-Sex: 9h às 18h\"
        },
        \"canais\": [
            {
                \"tipo\": \"whatsapp|telefone|email|presencial\",
                \"titulo\": \"Título do canal\",
                \"descricao\": \"Quando usar este canal\",
                \"cta\": \"Texto do botão\"
            }
        ],
        \"frase_final\": \"Frase motivacional para fechar\"
    },
    \"seo_metadata\": {
        \"meta_title\": \"Título SEO otimizado (máx 60 chars)\",
        \"meta_description\": \"Descrição para Google (máx 155 chars)\",
        \"keywords\": [\"palavra1\", \"palavra2\", \"palavra3\"]
    }
}";
    }
    
    private function getPersonalizadaInstruction(): string
    {
        return "
TAREFA: Criar conteúdo personalizado conforme solicitação do usuário.
OBJETIVO: Flexibilidade total para necessidades específicas.

ESTRUTURA JSON OBRIGATÓRIA:
{
    \"success\": true,
    \"page_type\": \"personalizada\",
    \"content\": {
        \"titulo_secao\": \"Título principal\",
        \"subtitulo\": \"Subtítulo complementar\",
        \"blocos\": [
            {
                \"tipo\": \"texto|lista|destaque|cta\",
                \"titulo\": \"Título do bloco (opcional)\",
                \"conteudo\": \"Conteúdo do bloco\"
            }
        ]
    },
    \"seo_metadata\": {
        \"meta_title\": \"Título SEO otimizado (máx 60 chars)\",
        \"meta_description\": \"Descrição para Google (máx 155 chars)\",
        \"keywords\": [\"palavra1\", \"palavra2\", \"palavra3\"]
    }
}";
    }
    
    /**
     * Chama a API do Gemini
     */
    private function callGeminiAPI(string $systemInstruction, string $userInput): array
    {
        $url = $this->apiUrl . '?key=' . $this->apiKey;
        
        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $userInput]
                    ]
                ]
            ],
            'systemInstruction' => [
                'parts' => [
                    ['text' => $systemInstruction]
                ]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature' => 0.7,
                'topP' => 0.9,
                'maxOutputTokens' => 4096
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
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        // Erro de conexão
        if ($error) {
            return [
                'success' => false,
                'error' => 'Erro de conexão com a API: ' . $error
            ];
        }
        
        // Erro HTTP
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            return [
                'success' => false,
                'error' => 'Erro da API (HTTP ' . $httpCode . '): ' . ($errorData['error']['message'] ?? 'Erro desconhecido'),
                'details' => $errorData
            ];
        }
        
        // Parse response
        $data = json_decode($response, true);
        
        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return [
                'success' => false,
                'error' => 'Resposta inesperada da API',
                'raw_response' => $data
            ];
        }
        
        // Parse do JSON gerado pela AI
        $generatedText = $data['candidates'][0]['content']['parts'][0]['text'];
        $generatedContent = json_decode($generatedText, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'error' => 'Erro ao parsear JSON gerado pela AI',
                'raw_text' => $generatedText
            ];
        }
        
        // Verificar se a AI retornou erro de conteúdo impróprio
        if (isset($generatedContent['error'])) {
            return [
                'success' => false,
                'error' => $generatedContent['error']
            ];
        }
        
        return $generatedContent;
    }
}
