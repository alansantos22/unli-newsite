/**
 * ============================================
 * ONBOARDING STEPS CONFIG
 * Configuração Dinâmica do Wizard de Onboarding
 * ============================================
 * 
 * Define quais perguntas aparecem para cada tipo de página
 * que o usuário comprou. O Wizard renderiza apenas os steps
 * relevantes para o pacote adquirido.
 */

/**
 * Tipos de campos disponíveis:
 * - text: Input de texto simples
 * - textarea: Área de texto (com opção AI Enhance)
 * - number: Input numérico
 * - select: Dropdown de opções
 * - checkbox: Checkbox simples
 * - checkbox-group: Grupo de checkboxes
 * - color: Color picker
 * - tags: Input de tags (múltiplos valores)
 * - repeater: Campo repetível (adicionar múltiplos itens)
 * - upload: Upload de arquivo
 * - conditional: Campo condicional (aparece baseado em outro)
 */

export const voiceToneOptions = [
  { value: 'profissional', label: 'Profissional', description: 'Sério, confiável, corporativo' },
  { value: 'amigavel', label: 'Amigável', description: 'Próximo, acolhedor, simpático' },
  { value: 'descontraido', label: 'Descontraído', description: 'Leve, divertido, jovem' },
  { value: 'luxuoso', label: 'Luxuoso', description: 'Sofisticado, premium, exclusivo' },
  { value: 'tecnico', label: 'Técnico', description: 'Preciso, detalhista, especialista' },
  { value: 'inspirador', label: 'Inspirador', description: 'Motivacional, empoderador, visionário' }
];

export const colorPresets = [
  { value: '#0066CC', name: 'Azul Profissional' },
  { value: '#28A745', name: 'Verde Saúde' },
  { value: '#DC3545', name: 'Vermelho Energia' },
  { value: '#FF6B35', name: 'Laranja Criativo' },
  { value: '#6F42C1', name: 'Roxo Inovação' },
  { value: '#212529', name: 'Preto Elegante' },
  { value: '#E83E8C', name: 'Rosa Moderno' },
  { value: '#17A2B8', name: 'Azul Claro' }
];

/**
 * Step de Identidade Global - SEMPRE APARECE
 * Coleta informações básicas da marca
 */
export const identityStep = {
  id: 'identity',
  title: '✨ Identidade da Marca',
  subtitle: 'Como seus clientes vão reconhecer você',
  icon: '🎨',
  required: true, // Sempre aparece
  fields: [
    {
      id: 'companyName',
      type: 'text',
      label: 'Nome da Empresa / Projeto',
      placeholder: 'Ex: Padaria Dona Maria',
      required: true,
      hint: 'O nome que aparecerá no site'
    },
    {
      id: 'tagline',
      type: 'text',
      label: 'Em uma frase, o que você faz?',
      placeholder: 'Ex: Transformamos casas em lares desde 2010',
      hint: 'Será o destaque do seu site',
      aiEnhance: true // Habilita botão "Melhorar com IA"
    },
    {
      id: 'primaryColor',
      type: 'color',
      label: 'Cor principal da sua marca',
      presets: colorPresets,
      default: '#0066CC'
    },
    {
      id: 'secondaryColor',
      type: 'color',
      label: 'Cor secundária (opcional)',
      presets: colorPresets,
      default: '#28A745'
    },
    {
      id: 'voiceTone',
      type: 'select',
      label: 'Qual o tom de voz da sua marca?',
      options: voiceToneOptions,
      required: true,
      hint: 'Isso define como os textos do site serão escritos'
    },
    {
      id: 'logo',
      type: 'upload',
      label: 'Logotipo',
      accept: 'image/*',
      maxSize: 5 * 1024 * 1024, // 5MB
      hint: 'PNG, JPG ou SVG (máx. 5MB)'
    },
    {
      id: 'hasNoLogo',
      type: 'checkbox',
      label: 'Ainda não tenho logo (Criaremos um texto estilizado)'
    }
  ]
};

/**
 * Step de Contato - SEMPRE APARECE
 * Coleta informações de contato
 */
export const contactStep = {
  id: 'contact',
  title: '📞 Contato e Localização',
  subtitle: 'Como seus clientes vão te encontrar',
  icon: '📍',
  required: true, // Sempre aparece
  fields: [
    {
      id: 'whatsapp',
      type: 'text',
      label: 'WhatsApp Principal',
      placeholder: '(11) 99999-9999',
      required: true,
      hint: '💡 Este será o botão flutuante do site',
      mask: 'phone'
    },
    {
      id: 'additionalPhones',
      type: 'repeater',
      label: 'Outros Telefones de Contato',
      addButtonText: '+ Adicionar telefone',
      maxItems: 5,
      hint: 'Telefones adicionais que aparecerão na página de contato',
      fields: [
        {
          id: 'type',
          type: 'select',
          label: 'Tipo',
          options: [
            { value: 'whatsapp', label: '📱 WhatsApp' },
            { value: 'celular', label: '📲 Celular' },
            { value: 'fixo', label: '☎️ Telefone Fixo' },
            { value: 'comercial', label: '🏢 Comercial' },
            { value: 'suporte', label: '🛟 Suporte' },
            { value: 'vendas', label: '💼 Vendas' }
          ]
        },
        {
          id: 'number',
          type: 'text',
          label: 'Número',
          placeholder: '(11) 99999-9999'
        },
        {
          id: 'label',
          type: 'text',
          label: 'Rótulo (opcional)',
          placeholder: 'Ex: Atendimento, Suporte técnico...'
        }
      ]
    },
    {
      id: 'socialNetworks',
      type: 'repeater',
      label: 'Redes Sociais',
      addButtonText: '+ Adicionar rede social',
      maxItems: 10,
      fields: [
        {
          id: 'type',
          type: 'select',
          label: 'Rede',
          options: [
            { value: 'instagram', label: '📸 Instagram' },
            { value: 'facebook', label: '👥 Facebook' },
            { value: 'linkedin', label: '💼 LinkedIn' },
            { value: 'twitter', label: '🐦 Twitter/X' },
            { value: 'tiktok', label: '🎵 TikTok' },
            { value: 'youtube', label: '▶️ YouTube' },
            { value: 'discord', label: '🎮 Discord' },
            { value: 'other', label: '🔗 Outro' }
          ]
        },
        {
          id: 'url',
          type: 'text',
          label: 'Link',
          placeholder: 'https://...'
        }
      ]
    },
    {
      id: 'hasPhysicalLocation',
      type: 'switch',
      label: 'Tenho endereço físico para atendimento',
      default: false
    },
    {
      id: 'addressCep',
      type: 'cep',
      label: 'CEP',
      placeholder: '00000-000',
      conditional: { field: 'hasPhysicalLocation', value: true },
      hint: '💡 Digite o CEP e clique em Buscar para preencher automaticamente'
    },
    {
      id: 'addressStreet',
      type: 'text',
      label: 'Rua / Logradouro',
      conditional: { field: 'hasPhysicalLocation', value: true }
    },
    {
      id: 'addressNumber',
      type: 'text',
      label: 'Número',
      conditional: { field: 'hasPhysicalLocation', value: true }
    },
    {
      id: 'addressComplement',
      type: 'text',
      label: 'Complemento',
      placeholder: 'Sala 101, Bloco A...',
      conditional: { field: 'hasPhysicalLocation', value: true }
    },
    {
      id: 'addressNeighborhood',
      type: 'text',
      label: 'Bairro',
      conditional: { field: 'hasPhysicalLocation', value: true }
    },
    {
      id: 'addressCity',
      type: 'text',
      label: 'Cidade',
      conditional: { field: 'hasPhysicalLocation', value: true }
    },
    {
      id: 'addressState',
      type: 'text',
      label: 'Estado',
      conditional: { field: 'hasPhysicalLocation', value: true }
    },
    {
      id: 'businessHours',
      type: 'text',
      label: 'Horário de Funcionamento',
      placeholder: 'Seg-Sex: 9h às 18h | Sáb: 9h às 13h',
      hint: 'Deixe em branco se não quiser exibir'
    }
  ]
};

/**
 * Steps específicos por tipo de página
 * Só aparecem se o usuário comprou a página
 */
export const pageSteps = {
  
  // ===== SOBRE NÓS (REFORMULADO) =====
  sobre_nos: {
    id: 'sobre_nos',
    pageType: 'sobre_nos',
    title: '🏢 A Empresa',
    subtitle: 'Crie conteúdo institucional que gera autoridade',
    icon: '🏢',
    fields: [
      // ---- Seção: Título ----
      {
        id: 'aboutSectionTitle',
        type: 'select',
        label: 'Título da Seção "Sobre"',
        options: [
          { value: 'Sobre Nós', label: 'Sobre Nós' },
          { value: 'Nossa História', label: 'Nossa História' },
          { value: 'Quem Somos', label: 'Quem Somos' },
          { value: 'A Empresa', label: 'A Empresa' },
          { value: 'Nossa Jornada', label: 'Nossa Jornada' }
        ],
        default: 'Sobre Nós',
        hint: 'Como a seção será chamada no site'
      },
      // ---- Seção: História ----
      {
        id: 'companyBio',
        type: 'textarea',
        label: 'História / Bio da Empresa',
        placeholder: 'Escreva livremente sobre como a empresa começou, o que motivou a criação, a trajetória até aqui...',
        rows: 6,
        aiEnhance: true,
        aiPrompt: 'Transforme em uma história de marca inspiradora com 2-3 parágrafos persuasivos. Use o tom de voz definido e destaque a jornada, valores e diferenciais.',
        aiContext: 'história institucional para página Sobre Nós',
        hint: '✨ Escreva de qualquer jeito - depois clique no botão "Melhorar com IA" para transformar em algo profissional'
      },
      {
        id: 'foundingYear',
        type: 'number',
        label: 'Ano de Fundação',
        placeholder: '2015',
        min: 1900,
        max: new Date().getFullYear(),
        hint: 'Usaremos para mostrar "X anos de experiência"'
      },
      {
        id: 'founders',
        type: 'text',
        label: 'Fundador(es) (opcional)',
        placeholder: 'João Silva e Maria Santos',
        hint: 'Deixe em branco se não quiser exibir'
      },
      // ---- Seção: Imagem de Destaque ----
      {
        id: 'aboutImage',
        type: 'upload',
        label: 'Imagem de Destaque',
        accept: 'image/*',
        hint: 'Foto da equipe, fachada, fundador ou ambiente de trabalho. Gera confiança!'
      },
      // ---- Seção: Diferenciais (Repeater) ----
      {
        id: 'companyHighlights',
        type: 'repeater',
        label: 'Diferenciais / Destaques',
        addButtonText: '+ Adicionar Diferencial',
        minItems: 0,
        maxItems: 6,
        hint: 'Adicione 3-4 diferenciais que serão exibidos como cards ou ícones',
        fields: [
          {
            id: 'icon',
            type: 'select',
            label: 'Ícone',
            options: [
              { value: '🏆', label: '🏆 Troféu' },
              { value: '⏰', label: '⏰ Relógio (Tempo)' },
              { value: '✅', label: '✅ Check (Garantia)' },
              { value: '🤝', label: '🤝 Parceria' },
              { value: '💡', label: '💡 Inovação' },
              { value: '🛡️', label: '🛡️ Segurança' },
              { value: '⭐', label: '⭐ Qualidade' },
              { value: '🚀', label: '🚀 Agilidade' },
              { value: '💬', label: '💬 Atendimento' },
              { value: '📍', label: '📍 Localização' },
              { value: '🎯', label: '🎯 Precisão' },
              { value: '💰', label: '💰 Economia' }
            ]
          },
          {
            id: 'title',
            type: 'text',
            label: 'Título',
            placeholder: 'Ex: 10 Anos de Mercado',
            required: true
          },
          {
            id: 'description',
            type: 'text',
            label: 'Breve Descrição',
            placeholder: 'Ex: Uma década de experiência atendendo nossos clientes'
          }
        ]
      },
      // ---- Seção: Missão/Visão/Valores ----
      {
        id: 'showMissionVision',
        type: 'switch',
        label: 'Incluir Missão, Visão e Valores',
        default: false
      },
      {
        id: 'mission',
        type: 'textarea',
        label: 'Missão (O que vocês fazem)',
        placeholder: 'Nossa missão é...',
        rows: 2,
        aiEnhance: true,
        conditional: { field: 'showMissionVision', value: true }
      },
      {
        id: 'vision',
        type: 'textarea',
        label: 'Visão (Onde querem chegar)',
        placeholder: 'Queremos ser referência em...',
        rows: 2,
        aiEnhance: true,
        conditional: { field: 'showMissionVision', value: true }
      },
      {
        id: 'values',
        type: 'tags',
        label: 'Valores da Empresa',
        placeholder: 'Digite e pressione Enter',
        maxTags: 6,
        suggestions: ['Qualidade', 'Transparência', 'Inovação', 'Compromisso', 'Excelência', 'Agilidade', 'Ética', 'Respeito'],
        conditional: { field: 'showMissionVision', value: true }
      }
    ]
  },

  // ===== SERVIÇOS (REFORMULADO) =====
  servicos: {
    id: 'servicos',
    pageType: 'servicos',
    title: '⚙️ Nossas Soluções',
    subtitle: 'Estruture seus serviços de forma profissional',
    icon: '⚙️',
    fields: [
      // ---- Introdução ----
      {
        id: 'servicesSectionTitle',
        type: 'select',
        label: 'Título da Seção',
        options: [
          { value: 'Nossos Serviços', label: 'Nossos Serviços' },
          { value: 'Nossas Soluções', label: 'Nossas Soluções' },
          { value: 'O Que Fazemos', label: 'O Que Fazemos' },
          { value: 'Áreas de Atuação', label: 'Áreas de Atuação' },
          { value: 'Como Podemos Ajudar', label: 'Como Podemos Ajudar' }
        ],
        default: 'Nossos Serviços'
      },
      {
        id: 'servicesIntro',
        type: 'textarea',
        label: 'Texto de Apresentação',
        placeholder: 'Oferecemos soluções completas para... Somos especialistas em...',
        rows: 3,
        aiEnhance: true,
        aiPrompt: 'Crie um texto de introdução profissional para a seção de serviços, destacando expertise e benefícios para o cliente',
        hint: 'Uma visão geral antes de listar os serviços'
      },
      // ---- Lista de Serviços (Repeater Melhorado) ----
      {
        id: 'services',
        type: 'repeater',
        label: 'Lista de Serviços',
        addButtonText: '+ Adicionar Serviço',
        minItems: 1,
        maxItems: 12,
        hint: 'Cada serviço vira um card clicável no site',
        fields: [
          {
            id: 'name',
            type: 'text',
            label: 'Nome do Serviço',
            placeholder: 'Ex: Consultoria Empresarial',
            required: true
          },
          {
            id: 'image',
            type: 'upload',
            label: 'Imagem de Capa (opcional)',
            accept: 'image/*',
            hint: 'Imagem representativa do serviço'
          },
          {
            id: 'shortDescription',
            type: 'textarea',
            label: 'Descrição do Serviço',
            placeholder: 'Descreva o serviço brevemente...',
            rows: 3,
            aiEnhance: true,
            aiPrompt: 'Reescreva como uma descrição profissional e persuasiva de serviço, destacando benefícios e resultados para o cliente'
          },
          {
            id: 'priceType',
            type: 'select',
            label: 'Exibição de Preço',
            options: [
              { value: 'hidden', label: 'Não exibir preço' },
              { value: 'fixed', label: 'Preço fixo' },
              { value: 'from', label: 'A partir de...' },
              { value: 'consult', label: 'Sob consulta' }
            ],
            default: 'hidden'
          },
          {
            id: 'price',
            type: 'text',
            label: 'Valor (se aplicável)',
            placeholder: 'R$ 150,00 ou A partir de R$ 99,00',
            hint: 'Deixe em branco se não quiser exibir preço'
          },
          {
            id: 'ctaButton',
            type: 'select',
            label: 'Botão de Ação',
            options: [
              { value: 'quote', label: '📩 Pedir Orçamento' },
              { value: 'whatsapp', label: '💬 Falar no WhatsApp' },
              { value: 'more', label: '➡️ Saiba Mais' },
              { value: 'schedule', label: '📅 Agendar' },
              { value: 'none', label: 'Sem botão' }
            ],
            default: 'whatsapp'
          }
        ]
      },
      // ---- Garantia ----
      {
        id: 'hasGuarantee',
        type: 'switch',
        label: 'Oferecemos garantia nos serviços',
        default: false
      },
      {
        id: 'guaranteeDetails',
        type: 'text',
        label: 'Detalhes da Garantia',
        placeholder: 'Ex: 90 dias de garantia em todos os serviços',
        conditional: { field: 'hasGuarantee', value: true }
      }
    ]
  },

  // ===== CONFIGURAÇÃO DE CONTATO/LEADS (NOVO) =====
  config_contato: {
    id: 'config_contato',
    pageType: 'config_contato',
    title: 'Configuração de Leads',
    subtitle: 'Defina como você vai receber contatos do site',
    icon: '📨',
    required: true, // Sempre aparece após contato básico
    fields: [
      // ---- E-mail para Leads ----
      {
        id: 'leadEmail',
        type: 'text',
        label: 'E-mail para Recebimento de Leads',
        placeholder: 'contato@suaempresa.com.br',
        required: true,
        hint: 'Onde os formulários do site serão enviados'
      },
      {
        id: 'leadEmailCC',
        type: 'text',
        label: 'E-mail em Cópia (opcional)',
        placeholder: 'vendas@suaempresa.com.br',
        hint: 'Um segundo e-mail para receber cópia dos contatos'
      },
      // ---- Campos do Formulário ----
      {
        id: 'formFields',
        type: 'checkbox-group',
        label: 'Campos do Formulário de Contato',
        hint: 'Marque o que você quer perguntar para seu cliente',
        options: [
          { value: 'name', label: '👤 Nome (sempre obrigatório)' },
          { value: 'email', label: '📧 E-mail' },
          { value: 'phone', label: '📱 Telefone/WhatsApp' },
          { value: 'subject', label: '📌 Assunto' },
          { value: 'message', label: '💬 Mensagem' },
          { value: 'company', label: '🏢 Nome da Empresa' },
          { value: 'city', label: '📍 Cidade' },
          { value: 'service', label: '⚙️ Serviço de Interesse (select)' },
          { value: 'attachment', label: '📎 Anexar Arquivo' }
        ]
      },
      {
        id: 'formRequiredFields',
        type: 'checkbox-group',
        label: 'Quais campos são obrigatórios?',
        options: [
          { value: 'email', label: '📧 E-mail' },
          { value: 'phone', label: '📱 Telefone/WhatsApp' },
          { value: 'message', label: '💬 Mensagem' }
        ]
      },
      // ---- WhatsApp Flutuante ----
      {
        id: 'whatsappFloatingEnabled',
        type: 'switch',
        label: 'Botão Flutuante de WhatsApp',
        default: true,
        hint: 'Botão fixo no canto da tela para contato direto'
      },
      {
        id: 'whatsappGreeting',
        type: 'text',
        label: 'Mensagem de Saudação Automática',
        placeholder: 'Olá! Vi seu site e gostaria de mais informações...',
        conditional: { field: 'whatsappFloatingEnabled', value: true },
        hint: 'Texto que aparecerá pré-preenchido no WhatsApp do cliente'
      },
      {
        id: 'whatsappPosition',
        type: 'select',
        label: 'Posição do Botão',
        options: [
          { value: 'bottom-right', label: 'Canto Inferior Direito' },
          { value: 'bottom-left', label: 'Canto Inferior Esquerdo' }
        ],
        default: 'bottom-right',
        conditional: { field: 'whatsappFloatingEnabled', value: true }
      },
      // ---- Mapa ----
      {
        id: 'showMap',
        type: 'switch',
        label: 'Mostrar Mapa de Localização no Site',
        default: true,
        hint: 'Exibe o Google Maps com seu endereço (se informado)'
      },
      // ---- Configurações Avançadas ----
      {
        id: 'enableCaptcha',
        type: 'switch',
        label: 'Ativar Proteção Anti-Spam (reCAPTCHA)',
        default: true
      },
      {
        id: 'autoReply',
        type: 'switch',
        label: 'Enviar e-mail automático de confirmação para o cliente',
        default: false
      },
      {
        id: 'autoReplyMessage',
        type: 'textarea',
        label: 'Mensagem de Confirmação',
        placeholder: 'Recebemos sua mensagem e entraremos em contato em até 24 horas...',
        rows: 3,
        aiEnhance: true,
        conditional: { field: 'autoReply', value: true }
      }
    ]
  },

  // ===== FAQ =====
  faq: {
    id: 'faq',
    pageType: 'faq',
    title: '❓ Perguntas Frequentes',
    subtitle: 'Quais dúvidas seus clientes sempre perguntam?',
    icon: '❓',
    fields: [
      {
        id: 'faqIntro',
        type: 'textarea',
        label: 'Texto de introdução do FAQ (opcional)',
        placeholder: 'Reunimos aqui as principais dúvidas...',
        rows: 2,
        aiEnhance: true
      },
      {
        id: 'commonQuestions',
        type: 'checkbox-group',
        label: 'Marque as dúvidas comuns no seu negócio:',
        hint: 'A IA vai gerar respostas personalizadas para cada uma',
        options: [
          { value: 'pricing', label: '💰 Formas de pagamento / Preços' },
          { value: 'delivery', label: '🚚 Prazos de entrega' },
          { value: 'warranty', label: '🛡️ Garantia e trocas' },
          { value: 'support', label: '📞 Suporte e atendimento' },
          { value: 'process', label: '📋 Como funciona o processo' },
          { value: 'coverage', label: '📍 Área de atendimento' }
        ]
      },
      {
        id: 'pricingDetails',
        type: 'textarea',
        label: 'Detalhes sobre pagamento/preços',
        placeholder: 'Aceitamos cartão, pix, boleto... Parcelamos em até...',
        conditional: { field: 'commonQuestions', contains: 'pricing' },
        aiEnhance: true
      },
      {
        id: 'deliveryDetails',
        type: 'textarea',
        label: 'Detalhes sobre prazos',
        placeholder: 'O prazo médio é de... depende de...',
        conditional: { field: 'commonQuestions', contains: 'delivery' },
        aiEnhance: true
      },
      {
        id: 'warrantyDetails',
        type: 'textarea',
        label: 'Detalhes sobre garantia',
        placeholder: 'Oferecemos garantia de... Para acionar...',
        conditional: { field: 'commonQuestions', contains: 'warranty' },
        aiEnhance: true
      },
      {
        id: 'customQuestions',
        type: 'repeater',
        label: 'Outras dúvidas frequentes',
        addButtonText: '+ Adicionar Pergunta',
        maxItems: 10,
        fields: [
          {
            id: 'question',
            type: 'text',
            label: 'Pergunta',
            placeholder: 'Ex: Vocês atendem aos finais de semana?'
          },
          {
            id: 'answer',
            type: 'textarea',
            label: 'Resposta',
            placeholder: 'Sim, atendemos...',
            aiEnhance: true
          }
        ]
      }
    ]
  },

  // ===== PORTFÓLIO =====
  portfolio: {
    id: 'portfolio',
    pageType: 'portfolio',
    title: '🖼️ Portfólio / Trabalhos Realizados',
    subtitle: 'Mostre o que você já fez de incrível',
    icon: '🖼️',
    fields: [
      {
        id: 'portfolioIntro',
        type: 'textarea',
        label: 'Apresentação do portfólio',
        placeholder: 'Confira alguns dos nossos melhores trabalhos...',
        rows: 2,
        aiEnhance: true
      },
      {
        id: 'projects',
        type: 'repeater',
        label: 'Projetos / Cases',
        addButtonText: '+ Adicionar Projeto',
        minItems: 1,
        maxItems: 12,
        fields: [
          {
            id: 'title',
            type: 'text',
            label: 'Nome do Projeto / Cliente',
            placeholder: 'Ex: Reforma Residência Silva',
            required: true
          },
          {
            id: 'category',
            type: 'text',
            label: 'Categoria',
            placeholder: 'Ex: Residencial, Comercial...'
          },
          {
            id: 'description',
            type: 'textarea',
            label: 'Breve descrição',
            placeholder: 'O que foi feito, desafios, resultados...',
            rows: 3,
            aiEnhance: true
          },
          {
            id: 'images',
            type: 'upload',
            label: 'Fotos do projeto',
            accept: 'image/*',
            multiple: true,
            maxFiles: 5
          }
        ]
      },
      {
        id: 'bigClients',
        type: 'tags',
        label: 'Grandes clientes que você já atendeu (opcional)',
        placeholder: 'Digite e pressione Enter',
        hint: 'Nomes de empresas conhecidas geram autoridade'
      },
      {
        id: 'metrics',
        type: 'repeater',
        label: 'Métricas de sucesso (opcional)',
        addButtonText: '+ Adicionar métrica',
        maxItems: 4,
        hint: 'Ex: "500+ projetos entregues", "98% de satisfação"',
        fields: [
          {
            id: 'value',
            type: 'text',
            label: 'Número',
            placeholder: '500+'
          },
          {
            id: 'label',
            type: 'text',
            label: 'Descrição',
            placeholder: 'Projetos entregues'
          }
        ]
      }
    ]
  },

  // ===== VITRINE / PRODUTOS =====
  vitrine_produtos: {
    id: 'vitrine_produtos',
    pageType: 'vitrine_produtos',
    title: '🛍️ Vitrine de Produtos',
    subtitle: 'Apresente seus produtos de forma irresistível',
    icon: '🛍️',
    fields: [
      {
        id: 'storeIntro',
        type: 'textarea',
        label: 'Apresentação da loja/produtos',
        placeholder: 'Conheça nossa linha de produtos...',
        rows: 2,
        aiEnhance: true
      },
      {
        id: 'products',
        type: 'repeater',
        label: 'Produtos',
        addButtonText: '+ Adicionar Produto',
        minItems: 1,
        maxItems: 20,
        fields: [
          {
            id: 'name',
            type: 'text',
            label: 'Nome do Produto',
            required: true
          },
          {
            id: 'price',
            type: 'text',
            label: 'Preço',
            placeholder: 'R$ 99,90'
          },
          {
            id: 'description',
            type: 'textarea',
            label: 'Descrição',
            placeholder: 'Características, benefícios...',
            rows: 2,
            aiEnhance: true,
            aiPrompt: 'Crie uma descrição persuasiva de produto para e-commerce'
          },
          {
            id: 'specs',
            type: 'tags',
            label: 'Especificações',
            placeholder: 'Ex: 100% algodão, Tamanho M...'
          },
          {
            id: 'image',
            type: 'upload',
            label: 'Foto do produto',
            accept: 'image/*'
          },
          {
            id: 'isHighlight',
            type: 'checkbox',
            label: 'Produto em destaque'
          }
        ]
      },
      {
        id: 'shippingInfo',
        type: 'textarea',
        label: 'Informações de frete/entrega',
        placeholder: 'Frete grátis acima de R$...',
        aiEnhance: true
      }
    ]
  },

  // ===== DEPOIMENTOS =====
  depoimentos: {
    id: 'depoimentos',
    pageType: 'depoimentos',
    title: '⭐ Depoimentos de Clientes',
    subtitle: 'O que seus clientes falam de você?',
    icon: '⭐',
    fields: [
      {
        id: 'testimonialsIntro',
        type: 'textarea',
        label: 'Introdução da seção',
        placeholder: 'Veja o que nossos clientes dizem...',
        rows: 2,
        aiEnhance: true
      },
      {
        id: 'testimonials',
        type: 'repeater',
        label: 'Depoimentos',
        addButtonText: '+ Adicionar Depoimento',
        minItems: 1,
        maxItems: 10,
        fields: [
          {
            id: 'text',
            type: 'textarea',
            label: 'O que o cliente disse?',
            placeholder: 'Cole aqui o feedback do cliente...',
            rows: 3,
            aiEnhance: true,
            aiPrompt: 'Melhore este depoimento mantendo a autenticidade, apenas refinando a gramática e tornando mais impactante'
          },
          {
            id: 'authorName',
            type: 'text',
            label: 'Nome do cliente',
            placeholder: 'João Silva'
          },
          {
            id: 'authorRole',
            type: 'text',
            label: 'Cargo/Empresa (opcional)',
            placeholder: 'CEO da TechCorp'
          },
          {
            id: 'rating',
            type: 'select',
            label: 'Avaliação',
            options: [
              { value: 5, label: '⭐⭐⭐⭐⭐ (5 estrelas)' },
              { value: 4, label: '⭐⭐⭐⭐ (4 estrelas)' },
              { value: 3, label: '⭐⭐⭐ (3 estrelas)' }
            ],
            default: 5
          },
          {
            id: 'photo',
            type: 'upload',
            label: 'Foto do cliente (opcional)',
            accept: 'image/*'
          }
        ]
      },
      {
        id: 'averageRating',
        type: 'text',
        label: 'Nota média no Google/Reclame Aqui (opcional)',
        placeholder: '4.9'
      },
      {
        id: 'totalReviews',
        type: 'text',
        label: 'Total de avaliações (opcional)',
        placeholder: '200+'
      }
    ]
  },

  // ===== BLOG =====
  blog_noticias: {
    id: 'blog_noticias',
    pageType: 'blog_noticias',
    title: '📝 Blog / Notícias',
    subtitle: 'Sobre o que você quer escrever?',
    icon: '📝',
    fields: [
      {
        id: 'blogPurpose',
        type: 'select',
        label: 'Qual o objetivo do blog?',
        options: [
          { value: 'seo', label: '🔍 Atrair visitantes do Google (SEO)' },
          { value: 'authority', label: '🏆 Mostrar autoridade no assunto' },
          { value: 'news', label: '📰 Divulgar novidades da empresa' },
          { value: 'education', label: '📚 Educar clientes sobre o produto/serviço' }
        ]
      },
      {
        id: 'mainTopics',
        type: 'tags',
        label: 'Sobre quais assuntos você quer escrever?',
        placeholder: 'Ex: Marketing Digital, Receitas, Decoração...',
        hint: 'A IA vai sugerir artigos baseados nesses temas'
      },
      {
        id: 'targetKeywords',
        type: 'tags',
        label: 'Palavras-chave que você quer ranquear no Google',
        placeholder: 'Ex: "dentista em SP", "reforma de apartamento"...'
      },
      {
        id: 'existingContent',
        type: 'textarea',
        label: 'Você já tem algum conteúdo pronto? (opcional)',
        placeholder: 'Cole aqui textos que você já tem...',
        rows: 4
      },
      {
        id: 'postFrequency',
        type: 'select',
        label: 'Com que frequência pretende postar?',
        options: [
          { value: 'weekly', label: 'Toda semana' },
          { value: 'biweekly', label: 'A cada 15 dias' },
          { value: 'monthly', label: 'Uma vez por mês' },
          { value: 'sporadic', label: 'Sem frequência definida' }
        ]
      }
    ]
  },

  // ===== VÍDEO BÁSICO =====
  video_basico: {
    id: 'video_basico',
    pageType: 'video_basico',
    title: '🎬 Vídeos do Site',
    subtitle: 'Envie vídeos para deixar seu site mais dinâmico',
    icon: '🎬',
    fields: [
      {
        id: 'videoIntro',
        type: 'textarea',
        label: 'Como você quer usar os vídeos no site?',
        placeholder: 'Quero um vídeo de apresentação na home, vídeos explicativos dos serviços...',
        rows: 2,
        hint: 'Isso nos ajuda a posicionar os vídeos corretamente'
      },
      {
        id: 'videos',
        type: 'repeater',
        label: 'Seus Vídeos',
        addButtonText: '+ Adicionar Vídeo',
        minItems: 0,
        maxItems: 5,
        hint: 'Formatos aceitos: MP4, WebM, MOV (máx. 100MB por vídeo)',
        fields: [
          {
            id: 'title',
            type: 'text',
            label: 'Título do Vídeo',
            placeholder: 'Ex: Apresentação da Empresa',
            required: true
          },
          {
            id: 'file',
            type: 'upload',
            label: 'Arquivo de Vídeo',
            accept: 'video/mp4,video/webm,video/quicktime',
            maxSize: 100 * 1024 * 1024, // 100MB
            hint: 'MP4, WebM ou MOV (máx. 100MB)'
          },
          {
            id: 'placement',
            type: 'select',
            label: 'Onde usar este vídeo?',
            options: [
              { value: 'hero', label: '🏠 Banner Principal (Home)' },
              { value: 'about', label: '🏢 Página Sobre Nós' },
              { value: 'services', label: '⚙️ Página de Serviços' },
              { value: 'background', label: '🎨 Vídeo de Fundo' },
              { value: 'gallery', label: '🖼️ Galeria de Mídia' },
              { value: 'other', label: '📍 Outro local' }
            ],
            default: 'hero'
          },
          {
            id: 'description',
            type: 'text',
            label: 'Descrição / Legenda (opcional)',
            placeholder: 'Breve descrição do conteúdo do vídeo'
          }
        ]
      },
      {
        id: 'youtubeVideos',
        type: 'repeater',
        label: 'Vídeos do YouTube (opcional)',
        addButtonText: '+ Adicionar link do YouTube',
        maxItems: 10,
        hint: 'Você também pode usar vídeos que já estão no YouTube',
        fields: [
          {
            id: 'url',
            type: 'text',
            label: 'Link do YouTube',
            placeholder: 'https://www.youtube.com/watch?v=...'
          },
          {
            id: 'title',
            type: 'text',
            label: 'Título',
            placeholder: 'Título descritivo do vídeo'
          }
        ]
      }
    ]
  },

  // ===== VÍDEO PRO =====
  video_pro: {
    id: 'video_pro',
    pageType: 'video_pro',
    title: '🎥 Vídeos Profissionais',
    subtitle: 'Upload de vídeos em alta qualidade com mais recursos',
    icon: '🎥',
    fields: [
      {
        id: 'videoStrategy',
        type: 'select',
        label: 'Qual o objetivo principal dos vídeos?',
        options: [
          { value: 'presentation', label: '🎤 Apresentação institucional' },
          { value: 'product', label: '📦 Demonstração de produtos' },
          { value: 'tutorial', label: '📚 Tutoriais e explicações' },
          { value: 'testimonial', label: '⭐ Depoimentos em vídeo' },
          { value: 'ambient', label: '🎨 Vídeos de ambiente/fundo' }
        ]
      },
      {
        id: 'videos',
        type: 'repeater',
        label: 'Vídeos Profissionais',
        addButtonText: '+ Adicionar Vídeo',
        minItems: 0,
        maxItems: 15,
        hint: 'Formatos aceitos: MP4, WebM, MOV (máx. 1GB por vídeo)',
        fields: [
          {
            id: 'title',
            type: 'text',
            label: 'Título do Vídeo',
            placeholder: 'Ex: Tour pela Empresa',
            required: true
          },
          {
            id: 'file',
            type: 'upload',
            label: 'Arquivo de Vídeo',
            accept: 'video/mp4,video/webm,video/quicktime',
            maxSize: 1024 * 1024 * 1024, // 1GB
            hint: 'MP4, WebM ou MOV (máx. 1GB) - Suporta 4K'
          },
          {
            id: 'thumbnail',
            type: 'upload',
            label: 'Thumbnail/Capa (opcional)',
            accept: 'image/*',
            hint: 'Imagem de capa para o vídeo'
          },
          {
            id: 'placement',
            type: 'select',
            label: 'Onde usar este vídeo?',
            options: [
              { value: 'hero', label: '🏠 Banner Principal (Home)' },
              { value: 'hero-background', label: '🎬 Fundo do Banner (Autoplay)' },
              { value: 'about', label: '🏢 Página Sobre Nós' },
              { value: 'services', label: '⚙️ Página de Serviços' },
              { value: 'products', label: '🛍️ Página de Produtos' },
              { value: 'testimonials', label: '⭐ Depoimentos' },
              { value: 'gallery', label: '🖼️ Galeria de Mídia' },
              { value: 'popup', label: '🎯 Popup/Modal' },
              { value: 'other', label: '📍 Outro local' }
            ],
            default: 'hero'
          },
          {
            id: 'autoplay',
            type: 'checkbox',
            label: 'Reproduzir automaticamente (sem som)'
          },
          {
            id: 'loop',
            type: 'checkbox',
            label: 'Repetir em loop'
          },
          {
            id: 'description',
            type: 'textarea',
            label: 'Descrição do Vídeo',
            placeholder: 'Descrição detalhada do conteúdo...',
            rows: 2,
            aiEnhance: true
          }
        ]
      },
      {
        id: 'youtubeVideos',
        type: 'repeater',
        label: 'Vídeos do YouTube/Vimeo',
        addButtonText: '+ Adicionar link externo',
        maxItems: 20,
        fields: [
          {
            id: 'url',
            type: 'text',
            label: 'Link do Vídeo',
            placeholder: 'YouTube, Vimeo ou outro'
          },
          {
            id: 'title',
            type: 'text',
            label: 'Título',
            placeholder: 'Título do vídeo'
          },
          {
            id: 'placement',
            type: 'select',
            label: 'Onde exibir',
            options: [
              { value: 'gallery', label: '🖼️ Galeria' },
              { value: 'inline', label: '📄 Inline na página' }
            ]
          }
        ]
      }
    ]
  },

  // ===== DOCUMENTOS PDF =====
  documentos_pdf: {
    id: 'documentos_pdf',
    pageType: 'documentos_pdf',
    title: '📄 Documentos e PDFs',
    subtitle: 'Catálogos, portfólios, tabelas de preço e materiais para download',
    icon: '📄',
    fields: [
      {
        id: 'documentPurpose',
        type: 'checkbox-group',
        label: 'Que tipo de documentos você quer disponibilizar?',
        options: [
          { value: 'catalog', label: '📚 Catálogo de Produtos/Serviços' },
          { value: 'price', label: '💰 Tabela de Preços' },
          { value: 'portfolio', label: '🖼️ Portfólio em PDF' },
          { value: 'manual', label: '📋 Manuais e Instruções' },
          { value: 'contract', label: '📝 Modelos de Contrato' },
          { value: 'ebook', label: '📖 E-book / Material Rico' },
          { value: 'certificate', label: '🏆 Certificações e Licenças' },
          { value: 'other', label: '📎 Outros documentos' }
        ],
        hint: 'Selecione os tipos que você pretende disponibilizar'
      },
      {
        id: 'documents',
        type: 'repeater',
        label: 'Seus Documentos',
        addButtonText: '+ Adicionar Documento',
        minItems: 0,
        maxItems: 20,
        hint: 'PDFs até 150MB cada. Limite total: 500MB',
        fields: [
          {
            id: 'title',
            type: 'text',
            label: 'Nome do Documento',
            placeholder: 'Ex: Catálogo de Produtos 2026',
            required: true
          },
          {
            id: 'file',
            type: 'upload',
            label: 'Arquivo PDF',
            accept: 'application/pdf',
            maxSize: 150 * 1024 * 1024, // 150MB
            hint: 'Apenas PDF (máx. 150MB)'
          },
          {
            id: 'category',
            type: 'select',
            label: 'Categoria',
            options: [
              { value: 'catalog', label: '📚 Catálogo' },
              { value: 'price', label: '💰 Tabela de Preços' },
              { value: 'portfolio', label: '🖼️ Portfólio' },
              { value: 'manual', label: '📋 Manual' },
              { value: 'ebook', label: '📖 E-book' },
              { value: 'certificate', label: '🏆 Certificado' },
              { value: 'other', label: '📎 Outro' }
            ]
          },
          {
            id: 'description',
            type: 'text',
            label: 'Descrição (aparece no botão de download)',
            placeholder: 'Ex: Baixe nosso catálogo completo'
          },
          {
            id: 'requireEmail',
            type: 'checkbox',
            label: 'Exigir e-mail para download (captura de leads)'
          },
          {
            id: 'isPublic',
            type: 'checkbox',
            label: 'Disponível para download público no site',
            default: true
          }
        ]
      },
      {
        id: 'downloadPageTitle',
        type: 'text',
        label: 'Título da Página de Downloads (se houver)',
        placeholder: 'Ex: Materiais, Downloads, Documentos...',
        default: 'Downloads'
      },
      {
        id: 'downloadPageIntro',
        type: 'textarea',
        label: 'Texto de introdução da página de downloads',
        placeholder: 'Baixe nossos materiais gratuitamente...',
        rows: 2,
        aiEnhance: true
      }
    ]
  }
};

/**
 * Step de Finalização - SEMPRE APARECE POR ÚLTIMO
 */
export const finalizationStep = {
  id: 'finalization',
  title: 'Quase Lá!',
  subtitle: 'Últimos detalhes antes de começarmos',
  icon: '🚀',
  required: true,
  fields: [
    {
      id: 'additionalNotes',
      type: 'textarea',
      label: 'Tem algo mais que devemos saber?',
      placeholder: 'Referências de sites que você gosta, preferências especiais, observações...',
      rows: 4
    },
    {
      id: 'referenceUrls',
      type: 'repeater',
      label: 'Sites que você gosta como referência',
      addButtonText: '+ Adicionar referência',
      maxItems: 5,
      fields: [
        {
          id: 'url',
          type: 'text',
          label: 'URL',
          placeholder: 'https://...'
        },
        {
          id: 'whatYouLike',
          type: 'text',
          label: 'O que você gosta nele?',
          placeholder: 'Ex: As cores, o layout, as animações...'
        }
      ]
    }
  ]
};

/**
 * Mapeamento das chaves do Order (banco de dados) para os IDs dos steps
 * Order usa: about, services, portfolio, faq, contact, blog, showcase
 * Steps usam: sobre_nos, servicos, portfolio, faq, config_contato, blog_noticias, vitrine_produtos
 */
export const pageKeyToStepId = {
  // Páginas principais
  about: 'sobre_nos',
  services: 'servicos',
  portfolio: 'portfolio',
  faq: 'faq',
  contact: 'config_contato', // Contact já é coberto pelo contactStep + config_contato
  blog: 'blog_noticias',
  showcase: 'vitrine_produtos',
  
  // Conteúdo de mídia
  video_basic: 'video_basico',
  video_pro: 'video_pro',
  pdf: 'documentos_pdf',
  
  // Depoimentos (caso exista)
  testimonials: 'depoimentos'
};

/**
 * Função para montar os steps baseado nas páginas compradas
 * @param {Array} purchasedPages - Array de page_types que o usuário comprou (chaves do order)
 * @returns {Array} - Array de steps ordenados para o wizard
 */
export function buildOnboardingSteps(purchasedPages = []) {
  const steps = [];
  
  // Garantir que purchasedPages é um array
  const pages = Array.isArray(purchasedPages) ? purchasedPages : [];
  
  // 1. Sempre começa com Identidade
  steps.push(identityStep);
  
  // 2. Sempre inclui Contato
  steps.push(contactStep);
  
  // 3. Sempre inclui Configuração de Leads (após contato básico)
  steps.push(pageSteps.config_contato);
  
  // 4. Adiciona steps específicos das páginas compradas
  pages.forEach(pageKey => {
    // Mapeia a chave do order para o ID do step
    const stepId = pageKeyToStepId[pageKey] || pageKey;
    
    // Pula config_contato e contact pois já foram adicionados
    if (stepId === 'config_contato' || pageKey === 'contact') {
      return;
    }
    
    // Adiciona se o step existir
    if (pageSteps[stepId]) {
      steps.push(pageSteps[stepId]);
    } else {
      console.warn(`[buildOnboardingSteps] Step não encontrado para: ${pageKey} (mapeado: ${stepId})`);
    }
  });
  
  // 5. Sempre termina com Finalização
  steps.push(finalizationStep);
  
  return steps;
}

/**
 * Exporta tudo para uso no Vue
 */
export default {
  identityStep,
  contactStep,
  pageSteps,
  finalizationStep,
  buildOnboardingSteps,
  pageKeyToStepId,
  voiceToneOptions,
  colorPresets
};
