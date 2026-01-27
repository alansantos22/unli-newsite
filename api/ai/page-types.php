<?php
/**
 * ============================================
 * AI CONTENT GENERATOR - GET PAGE TYPES
 * GET /api/ai/page-types.php
 * ============================================
 * 
 * Retorna os tipos de página disponíveis e suas configurações.
 * Usado pelo frontend para renderizar o formulário dinâmico.
 */

// Proteção para carregar config segura
define('SECURE_CONFIG_ACCESS', true);

// Headers e CORS
require_once __DIR__ . '/../lib/cors.php';
header('Content-Type: application/json; charset=utf-8');

// Carregar dependências
require_once __DIR__ . '/../config.secure.php';
require_once __DIR__ . '/../lib/content-generator.php';

// Apenas GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método não permitido. Use GET.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Instanciar factory (não precisa de API key para listar tipos)
    // Usamos reflection ou criamos um método estático
    $pageTypes = [
        'sobre_nos' => [
            'label' => 'Sobre Nós',
            'icon' => '🏢',
            'description' => 'História, missão, visão e valores da empresa',
            'input_placeholder' => 'Conte a história da sua empresa: quando começou, por que você abriu, o que te motiva...',
            'input_hint' => '💡 Quanto mais detalhes você der, melhor será o texto gerado.',
            'exemplo' => 'Comecei a empresa em 2015 na garagem de casa. Sempre tive paixão por tecnologia e queria ajudar pequenos negócios a crescerem...'
        ],
        'servicos' => [
            'label' => 'Serviços',
            'icon' => '⚙️',
            'description' => 'Lista de serviços com descrições persuasivas',
            'input_placeholder' => 'Liste seus serviços: Ex: Instalação elétrica, manutenção preventiva, projetos residenciais...',
            'input_hint' => '💡 Mencione também diferenciais como garantia, rapidez, etc.',
            'exemplo' => 'Faço instalação elétrica residencial e comercial. Manutenção preventiva mensal. Projetos personalizados. Tenho 10 anos de experiência e dou garantia de 1 ano.'
        ],
        'faq' => [
            'label' => 'FAQ',
            'icon' => '❓',
            'description' => 'Perguntas frequentes que quebram objeções',
            'input_placeholder' => 'Digite dúvidas comuns ou descreva seu nicho: Ex: "Clínica odontológica em São Paulo"',
            'input_hint' => '💡 A IA vai criar perguntas que quebram objeções de venda.',
            'exemplo' => 'Sou dentista especializado em implantes. Clientes perguntam muito sobre dor, preço, tempo de recuperação e se o plano cobre.'
        ],
        'portfolio' => [
            'label' => 'Portfólio',
            'icon' => '🖼️',
            'description' => 'Cases de sucesso com resultados mensuráveis',
            'input_placeholder' => 'Descreva projetos que você já fez: cliente, problema, solução e resultado...',
            'input_hint' => '💡 Inclua números se tiver (Ex: aumentou 30% as vendas).',
            'exemplo' => 'Fiz um site para uma loja de roupas que não vendia online. Depois do site, as vendas aumentaram 40% em 3 meses. Cliente ficou super satisfeito.'
        ],
        'blog_noticias' => [
            'label' => 'Blog',
            'icon' => '📝',
            'description' => 'Artigos otimizados para SEO',
            'input_placeholder' => 'Qual seu nicho? Ex: "Marketing digital para dentistas" ou "Receitas veganas"',
            'input_hint' => '💡 A IA vai sugerir artigos otimizados para SEO.',
            'exemplo' => 'Marketing digital para pequenas empresas. Foco em redes sociais e Google Ads. Público são donos de lojas e prestadores de serviço.'
        ],
        'vitrine_produtos' => [
            'label' => 'Vitrine',
            'icon' => '🛍️',
            'description' => 'Catálogo de produtos com descrições que vendem',
            'input_placeholder' => 'Liste seus produtos: nome, preço, características principais...',
            'input_hint' => '💡 A IA vai criar descrições persuasivas para vender.',
            'exemplo' => 'Vendo bolos artesanais: Bolo de chocolate R$80, Bolo de morango R$90, Torta de limão R$70. Todos feitos com ingredientes premium.'
        ],
        'depoimentos' => [
            'label' => 'Depoimentos',
            'icon' => '⭐',
            'description' => 'Estruturar feedbacks para máximo impacto',
            'input_placeholder' => 'Cole feedbacks de clientes ou descreva casos de sucesso...',
            'input_hint' => '💡 A IA vai estruturar para máximo impacto.',
            'exemplo' => 'Cliente João disse: "Muito bom o serviço, super recomendo". Maria falou que ficou muito satisfeita e já indicou pra amiga.'
        ],
        'contato' => [
            'label' => 'Contato',
            'icon' => '📞',
            'description' => 'Página de contato convidativa',
            'input_placeholder' => 'Informe: endereço, telefone, horário de funcionamento, formas de atendimento...',
            'input_hint' => '💡 A IA vai criar textos convidativos para entrar em contato.',
            'exemplo' => 'Fico na Rua das Flores 123, Centro. Atendo de segunda a sexta das 9h às 18h. WhatsApp, telefone e email.'
        ],
        'personalizada' => [
            'label' => 'Personalizada',
            'icon' => '✨',
            'description' => 'Página totalmente customizada',
            'input_placeholder' => 'Descreva exatamente o que você precisa: tipo de página, tom de voz, conteúdo...',
            'input_hint' => '💡 Flexibilidade total - descreva sua necessidade.',
            'exemplo' => 'Preciso de uma página de agradecimento pós-compra com instruções de uso do produto e links para suporte.'
        ]
    ];
    
    $voiceTones = [
        'profissional' => [
            'label' => 'Profissional',
            'icon' => '💼',
            'description' => 'Transmite autoridade e credibilidade'
        ],
        'amigavel' => [
            'label' => 'Amigável',
            'icon' => '😊',
            'description' => 'Tom caloroso e próximo do cliente'
        ],
        'premium' => [
            'label' => 'Premium',
            'icon' => '👑',
            'description' => 'Para marcas de alto padrão'
        ],
        'jovem' => [
            'label' => 'Jovem',
            'icon' => '🚀',
            'description' => 'Linguagem descontraída e moderna'
        ],
        'tecnico' => [
            'label' => 'Técnico',
            'icon' => '🔧',
            'description' => 'Foco em especificações e detalhes'
        ],
        'inspirador' => [
            'label' => 'Inspirador',
            'icon' => '✨',
            'description' => 'Conecta emocionalmente'
        ]
    ];
    
    echo json_encode([
        'success' => true,
        'page_types' => $pageTypes,
        'voice_tones' => $voiceTones
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
