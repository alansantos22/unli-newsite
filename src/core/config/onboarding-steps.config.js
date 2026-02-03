/**
 * ============================================
 * ONBOARDING STEPS CONFIG
 * Configura├º├úo Din├ómica do Wizard de Onboarding
 * ============================================
 * 
 * Define quais perguntas aparecem para cada tipo de p├ígina
 * que o usu├írio comprou. O Wizard renderiza apenas os steps
 * relevantes para o pacote adquirido.
 */

/**
 * Tipos de campos dispon├¡veis:
 * - text: Input de texto simples
 * - textarea: ├ürea de texto (com op├º├úo AI Enhance)
 * - number: Input num├®rico
 * - select: Dropdown de op├º├Áes
 * - checkbox: Checkbox simples
 * - checkbox-group: Grupo de checkboxes
 * - color: Color picker
 * - tags: Input de tags (m├║ltiplos valores)
 * - repeater: Campo repet├¡vel (adicionar m├║ltiplos itens)
 * - upload: Upload de arquivo
 * - conditional: Campo condicional (aparece baseado em outro)
 */

export const voiceToneOptions = [
  { value: 'profissional', label: 'Profissional', description: 'S├®rio, confi├ível, corporativo' },
  { value: 'amigavel', label: 'Amig├ível', description: 'Pr├│ximo, acolhedor, simp├ítico' },
  { value: 'descontraido', label: 'Descontra├¡do', description: 'Leve, divertido, jovem' },
  { value: 'luxuoso', label: 'Luxuoso', description: 'Sofisticado, premium, exclusivo' },
  { value: 'tecnico', label: 'T├®cnico', description: 'Preciso, detalhista, especialista' },
  { value: 'inspirador', label: 'Inspirador', description: 'Motivacional, empoderador, vision├írio' }
];

export const colorPresets = [
  { value: '#0066CC', name: 'Azul Profissional' },
  { value: '#28A745', name: 'Verde Sa├║de' },
  { value: '#DC3545', name: 'Vermelho Energia' },
  { value: '#FF6B35', name: 'Laranja Criativo' },
  { value: '#6F42C1', name: 'Roxo Inova├º├úo' },
  { value: '#212529', name: 'Preto Elegante' },
  { value: '#E83E8C', name: 'Rosa Moderno' },
  { value: '#17A2B8', name: 'Azul Claro' }
];

/**
 * Step de Identidade Global - SEMPRE APARECE
 * Coleta informa├º├Áes b├ísicas da marca
 */
export const identityStep = {
  id: 'identity',
  title: 'Ô£¿ Identidade da Marca',
  subtitle: 'Como seus clientes v├úo reconhecer voc├¬',
  icon: '­ƒÄ¿',
  required: true, // Sempre aparece
  fields: [
    {
      id: 'companyName',
      type: 'text',
      label: 'Nome da Empresa / Projeto',
      placeholder: 'Ex: Padaria Dona Maria',
      required: true,
      hint: 'O nome que aparecer├í no site'
    },
    {
      id: 'tagline',
      type: 'text',
      label: 'Em uma frase, o que voc├¬ faz?',
      placeholder: 'Ex: Transformamos casas em lares desde 2010',
      hint: 'Ser├í o destaque do seu site',
      aiEnhance: true // Habilita bot├úo "Melhorar com IA"
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
      label: 'Cor secund├íria (opcional)',
      presets: colorPresets,
      default: '#28A745'
    },
    {
      id: 'voiceTone',
      type: 'select',
      label: 'Qual o tom de voz da sua marca?',
      options: voiceToneOptions,
      required: true,
      hint: 'Isso define como os textos do site ser├úo escritos'
    },
    {
      id: 'logo',
      type: 'upload',
      label: 'Logotipo',
      accept: 'image/*',
      maxSize: 5 * 1024 * 1024, // 5MB
      hint: 'PNG, JPG ou SVG (m├íx. 5MB)'
    },
    {
      id: 'hasNoLogo',
      type: 'checkbox',
      label: 'Ainda n├úo tenho logo (Criaremos um texto estilizado)'
    }
  ]
};

/**
 * Step de Contato - SEMPRE APARECE
 * Coleta informa├º├Áes de contato
 */
export const contactStep = {
  id: 'contact',
  title: '­ƒô× Contato e Localiza├º├úo',
  subtitle: 'Como seus clientes v├úo te encontrar',
  icon: '­ƒôì',
  required: true, // Sempre aparece
  fields: [
    {
      id: 'whatsapp',
      type: 'text',
      label: 'WhatsApp Principal',
      placeholder: '(11) 99999-9999',
      required: true,
      hint: '­ƒÆí Este ser├í o bot├úo flutuante do site',
      mask: 'phone'
    },
    {
      id: 'additionalPhones',
      type: 'repeater',
      label: 'Outros Telefones de Contato',
      addButtonText: '+ Adicionar telefone',
      maxItems: 5,
      hint: 'Telefones adicionais que aparecer├úo na p├ígina de contato',
      fields: [
        {
          id: 'type',
          type: 'select',
          label: 'Tipo',
          options: [
            { value: 'whatsapp', label: '­ƒô▒ WhatsApp' },
            { value: 'celular', label: '­ƒô▓ Celular' },
            { value: 'fixo', label: 'ÔÿÄ´©Å Telefone Fixo' },
            { value: 'comercial', label: '­ƒÅó Comercial' },
            { value: 'suporte', label: '­ƒøƒ Suporte' },
            { value: 'vendas', label: '­ƒÆ╝ Vendas' }
          ]
        },
        {
          id: 'number',
          type: 'text',
          label: 'N├║mero',
          placeholder: '(11) 99999-9999'
        },
        {
          id: 'label',
          type: 'text',
          label: 'R├│tulo (opcional)',
          placeholder: 'Ex: Atendimento, Suporte t├®cnico...'
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
            { value: 'instagram', label: '­ƒô© Instagram' },
            { value: 'facebook', label: '­ƒæÑ Facebook' },
            { value: 'linkedin', label: '­ƒÆ╝ LinkedIn' },
            { value: 'twitter', label: '­ƒÉª Twitter/X' },
            { value: 'tiktok', label: '­ƒÄÁ TikTok' },
            { value: 'youtube', label: 'ÔûÂ´©Å YouTube' },
            { value: 'discord', label: '­ƒÄ« Discord' },
            { value: 'other', label: '­ƒöù Outro' }
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
      label: 'Tenho endere├ºo f├¡sico para atendimento',
      default: false
    },
    {
      id: 'addressCep',
      type: 'cep',
      label: 'CEP',
      placeholder: '00000-000',
      conditional: { field: 'hasPhysicalLocation', value: true },
      hint: '­ƒÆí Digite o CEP e clique em Buscar para preencher automaticamente'
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
      label: 'N├║mero',
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
      label: 'Hor├írio de Funcionamento',
      placeholder: 'Seg-Sex: 9h ├ás 18h | S├íb: 9h ├ás 13h',
      hint: 'Deixe em branco se n├úo quiser exibir'
    }
  ]
};

/**
 * Steps espec├¡ficos por tipo de p├ígina
 * S├│ aparecem se o usu├írio comprou a p├ígina
 */
export const pageSteps = {
  
  // ===== SOBRE N├ôS (REFORMULADO) =====
  sobre_nos: {
    id: 'sobre_nos',
    pageType: 'sobre_nos',
    title: '­ƒÅó A Empresa',
    subtitle: 'Crie conte├║do institucional que gera autoridade',
    icon: '­ƒÅó',
    fields: [
      // ---- Se├º├úo: T├¡tulo ----
      {
        id: 'aboutSectionTitle',
        type: 'select',
        label: 'T├¡tulo da Se├º├úo "Sobre"',
        options: [
          { value: 'Sobre N├│s', label: 'Sobre N├│s' },
          { value: 'Nossa Hist├│ria', label: 'Nossa Hist├│ria' },
          { value: 'Quem Somos', label: 'Quem Somos' },
          { value: 'A Empresa', label: 'A Empresa' },
          { value: 'Nossa Jornada', label: 'Nossa Jornada' }
        ],
        default: 'Sobre N├│s',
        hint: 'Como a se├º├úo ser├í chamada no site'
      },
      // ---- Se├º├úo: Hist├│ria ----
      {
        id: 'companyBio',
        type: 'textarea',
        label: 'Hist├│ria / Bio da Empresa',
        placeholder: 'Escreva livremente sobre como a empresa come├ºou, o que motivou a cria├º├úo, a trajet├│ria at├® aqui...',
        rows: 6,
        aiEnhance: true,
        aiPrompt: 'Transforme em uma hist├│ria de marca inspiradora com 2-3 par├ígrafos persuasivos. Use o tom de voz definido e destaque a jornada, valores e diferenciais.',
        aiContext: 'hist├│ria institucional para p├ígina Sobre N├│s',
        hint: 'Ô£¿ Escreva de qualquer jeito - depois clique no bot├úo "Melhorar com IA" para transformar em algo profissional'
      },
      {
        id: 'foundingYear',
        type: 'number',
        label: 'Ano de Funda├º├úo',
        placeholder: '2015',
        min: 1900,
        max: new Date().getFullYear(),
        hint: 'Usaremos para mostrar "X anos de experi├¬ncia"'
      },
      {
        id: 'founders',
        type: 'text',
        label: 'Fundador(es) (opcional)',
        placeholder: 'Jo├úo Silva e Maria Santos',
        hint: 'Deixe em branco se n├úo quiser exibir'
      },
      // ---- Se├º├úo: Imagem de Destaque ----
      {
        id: 'aboutImage',
        type: 'upload',
        label: 'Imagem de Destaque',
        accept: 'image/*',
        hint: 'Foto da equipe, fachada, fundador ou ambiente de trabalho. Gera confian├ºa!'
      },
      // ---- Se├º├úo: Diferenciais (Repeater) ----
      {
        id: 'companyHighlights',
        type: 'repeater',
        label: 'Diferenciais / Destaques',
        addButtonText: '+ Adicionar Diferencial',
        minItems: 0,
        maxItems: 6,
        hint: 'Adicione 3-4 diferenciais que ser├úo exibidos como cards ou ├¡cones',
        fields: [
          {
            id: 'icon',
            type: 'select',
            label: '├ìcone',
            options: [
              { value: '­ƒÅå', label: '­ƒÅå Trof├®u' },
              { value: 'ÔÅ░', label: 'ÔÅ░ Rel├│gio (Tempo)' },
              { value: 'Ô£à', label: 'Ô£à Check (Garantia)' },
              { value: '­ƒñØ', label: '­ƒñØ Parceria' },
              { value: '­ƒÆí', label: '­ƒÆí Inova├º├úo' },
              { value: '­ƒøí´©Å', label: '­ƒøí´©Å Seguran├ºa' },
              { value: 'Ô¡É', label: 'Ô¡É Qualidade' },
              { value: '­ƒÜÇ', label: '­ƒÜÇ Agilidade' },
              { value: '­ƒÆ¼', label: '­ƒÆ¼ Atendimento' },
              { value: '­ƒôì', label: '­ƒôì Localiza├º├úo' },
              { value: '­ƒÄ»', label: '­ƒÄ» Precis├úo' },
              { value: '­ƒÆ░', label: '­ƒÆ░ Economia' }
            ]
          },
          {
            id: 'title',
            type: 'text',
            label: 'T├¡tulo',
            placeholder: 'Ex: 10 Anos de Mercado',
            required: true
          },
          {
            id: 'description',
            type: 'text',
            label: 'Breve Descri├º├úo',
            placeholder: 'Ex: Uma d├®cada de experi├¬ncia atendendo nossos clientes'
          }
        ]
      },
      // ---- Se├º├úo: Miss├úo/Vis├úo/Valores ----
      {
        id: 'showMissionVision',
        type: 'switch',
        label: 'Incluir Miss├úo, Vis├úo e Valores',
        default: false
      },
      {
        id: 'mission',
        type: 'textarea',
        label: 'Miss├úo (O que voc├¬s fazem)',
        placeholder: 'Nossa miss├úo ├®...',
        rows: 2,
        aiEnhance: true,
        conditional: { field: 'showMissionVision', value: true }
      },
      {
        id: 'vision',
        type: 'textarea',
        label: 'Vis├úo (Onde querem chegar)',
        placeholder: 'Queremos ser refer├¬ncia em...',
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
        suggestions: ['Qualidade', 'Transpar├¬ncia', 'Inova├º├úo', 'Compromisso', 'Excel├¬ncia', 'Agilidade', '├ëtica', 'Respeito'],
        conditional: { field: 'showMissionVision', value: true }
      }
    ]
  },

  // ===== SERVI├çOS (REFORMULADO) =====
  servicos: {
    id: 'servicos',
    pageType: 'servicos',
    title: 'ÔÜÖ´©Å Nossas Solu├º├Áes',
    subtitle: 'Estruture seus servi├ºos de forma profissional',
    icon: 'ÔÜÖ´©Å',
    fields: [
      // ---- Introdu├º├úo ----
      {
        id: 'servicesSectionTitle',
        type: 'select',
        label: 'T├¡tulo da Se├º├úo',
        options: [
          { value: 'Nossos Servi├ºos', label: 'Nossos Servi├ºos' },
          { value: 'Nossas Solu├º├Áes', label: 'Nossas Solu├º├Áes' },
          { value: 'O Que Fazemos', label: 'O Que Fazemos' },
          { value: '├üreas de Atua├º├úo', label: '├üreas de Atua├º├úo' },
          { value: 'Como Podemos Ajudar', label: 'Como Podemos Ajudar' }
        ],
        default: 'Nossos Servi├ºos'
      },
      {
        id: 'servicesIntro',
        type: 'textarea',
        label: 'Texto de Apresenta├º├úo',
        placeholder: 'Oferecemos solu├º├Áes completas para... Somos especialistas em...',
        rows: 3,
        aiEnhance: true,
        aiPrompt: 'Crie um texto de introdu├º├úo profissional para a se├º├úo de servi├ºos, destacando expertise e benef├¡cios para o cliente',
        hint: 'Uma vis├úo geral antes de listar os servi├ºos'
      },
      // ---- Lista de Servi├ºos (Repeater Melhorado) ----
      {
        id: 'services',
        type: 'repeater',
        label: 'Lista de Servi├ºos',
        addButtonText: '+ Adicionar Servi├ºo',
        minItems: 1,
        maxItems: 12,
        hint: 'Cada servi├ºo vira um card clic├ível no site',
        fields: [
          {
            id: 'name',
            type: 'text',
            label: 'Nome do Servi├ºo',
            placeholder: 'Ex: Consultoria Empresarial',
            required: true
          },
          {
            id: 'image',
            type: 'upload',
            label: 'Imagem de Capa (opcional)',
            accept: 'image/*',
            hint: 'Imagem representativa do servi├ºo'
          },
          {
            id: 'shortDescription',
            type: 'textarea',
            label: 'Descri├º├úo do Servi├ºo',
            placeholder: 'Descreva o servi├ºo brevemente...',
            rows: 3,
            aiEnhance: true,
            aiPrompt: 'Reescreva como uma descri├º├úo profissional e persuasiva de servi├ºo, destacando benef├¡cios e resultados para o cliente'
          },
          {
            id: 'priceType',
            type: 'select',
            label: 'Exibi├º├úo de Pre├ºo',
            options: [
              { value: 'hidden', label: 'N├úo exibir pre├ºo' },
              { value: 'fixed', label: 'Pre├ºo fixo' },
              { value: 'from', label: 'A partir de...' },
              { value: 'consult', label: 'Sob consulta' }
            ],
            default: 'hidden'
          },
          {
            id: 'price',
            type: 'text',
            label: 'Valor (se aplic├ível)',
            placeholder: 'R$ 150,00 ou A partir de R$ 99,00',
            hint: 'Deixe em branco se n├úo quiser exibir pre├ºo'
          },
          {
            id: 'ctaButton',
            type: 'select',
            label: 'Bot├úo de A├º├úo',
            options: [
              { value: 'quote', label: '­ƒô® Pedir Or├ºamento' },
              { value: 'whatsapp', label: '­ƒÆ¼ Falar no WhatsApp' },
              { value: 'more', label: 'Ô×í´©Å Saiba Mais' },
              { value: 'schedule', label: '­ƒôà Agendar' },
              { value: 'none', label: 'Sem bot├úo' }
            ],
            default: 'whatsapp'
          }
        ]
      },
      // ---- Garantia ----
      {
        id: 'hasGuarantee',
        type: 'switch',
        label: 'Oferecemos garantia nos servi├ºos',
        default: false
      },
      {
        id: 'guaranteeDetails',
        type: 'text',
        label: 'Detalhes da Garantia',
        placeholder: 'Ex: 90 dias de garantia em todos os servi├ºos',
        conditional: { field: 'hasGuarantee', value: true }
      }
    ]
  },

  // ===== CONFIGURA├ç├âO DE CONTATO/LEADS (NOVO) =====
  config_contato: {
    id: 'config_contato',
    pageType: 'config_contato',
    title: 'Configura├º├úo de Leads',
    subtitle: 'Defina como voc├¬ vai receber contatos do site',
    icon: '­ƒô¿',
    required: true, // Sempre aparece ap├│s contato b├ísico
    fields: [
      // ---- E-mail para Leads ----
      {
        id: 'leadEmail',
        type: 'text',
        label: 'E-mail para Recebimento de Leads',
        placeholder: 'contato@suaempresa.com.br',
        required: true,
        hint: 'Onde os formul├írios do site ser├úo enviados'
      },
      {
        id: 'leadEmailCC',
        type: 'text',
        label: 'E-mail em C├│pia (opcional)',
        placeholder: 'vendas@suaempresa.com.br',
        hint: 'Um segundo e-mail para receber c├│pia dos contatos'
      },
      // ---- Campos do Formul├írio ----
      {
        id: 'formFields',
        type: 'checkbox-group',
        label: 'Campos do Formul├írio de Contato',
        hint: 'Marque o que voc├¬ quer perguntar para seu cliente',
        options: [
          { value: 'name', label: '­ƒæñ Nome (sempre obrigat├│rio)' },
          { value: 'email', label: '­ƒôº E-mail' },
          { value: 'phone', label: '­ƒô▒ Telefone/WhatsApp' },
          { value: 'subject', label: '­ƒôî Assunto' },
          { value: 'message', label: '­ƒÆ¼ Mensagem' },
          { value: 'company', label: '­ƒÅó Nome da Empresa' },
          { value: 'city', label: '­ƒôì Cidade' },
          { value: 'service', label: 'ÔÜÖ´©Å Servi├ºo de Interesse (select)' },
          { value: 'attachment', label: '­ƒôÄ Anexar Arquivo' }
        ]
      },
      {
        id: 'formRequiredFields',
        type: 'checkbox-group',
        label: 'Quais campos s├úo obrigat├│rios?',
        options: [
          { value: 'email', label: '­ƒôº E-mail' },
          { value: 'phone', label: '­ƒô▒ Telefone/WhatsApp' },
          { value: 'message', label: '­ƒÆ¼ Mensagem' }
        ]
      },
      // ---- WhatsApp Flutuante ----
      {
        id: 'whatsappFloatingEnabled',
        type: 'switch',
        label: 'Bot├úo Flutuante de WhatsApp',
        default: true,
        hint: 'Bot├úo fixo no canto da tela para contato direto'
      },
      {
        id: 'whatsappGreeting',
        type: 'text',
        label: 'Mensagem de Sauda├º├úo Autom├ítica',
        placeholder: 'Ol├í! Vi seu site e gostaria de mais informa├º├Áes...',
        conditional: { field: 'whatsappFloatingEnabled', value: true },
        hint: 'Texto que aparecer├í pr├®-preenchido no WhatsApp do cliente'
      },
      {
        id: 'whatsappPosition',
        type: 'select',
        label: 'Posi├º├úo do Bot├úo',
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
        label: 'Mostrar Mapa de Localiza├º├úo no Site',
        default: true,
        hint: 'Exibe o Google Maps com seu endere├ºo (se informado)'
      },
      // ---- Configura├º├Áes Avan├ºadas ----
      {
        id: 'enableCaptcha',
        type: 'switch',
        label: 'Ativar Prote├º├úo Anti-Spam (reCAPTCHA)',
        default: true
      },
      {
        id: 'autoReply',
        type: 'switch',
        label: 'Enviar e-mail autom├ítico de confirma├º├úo para o cliente',
        default: false
      },
      {
        id: 'autoReplyMessage',
        type: 'textarea',
        label: 'Mensagem de Confirma├º├úo',
        placeholder: 'Recebemos sua mensagem e entraremos em contato em at├® 24 horas...',
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
    title: 'ÔØô Perguntas Frequentes',
    subtitle: 'Quais d├║vidas seus clientes sempre perguntam?',
    icon: 'ÔØô',
    fields: [
      {
        id: 'faqIntro',
        type: 'textarea',
        label: 'Texto de introdu├º├úo do FAQ (opcional)',
        placeholder: 'Reunimos aqui as principais d├║vidas...',
        rows: 2,
        aiEnhance: true
      },
      {
        id: 'commonQuestions',
        type: 'checkbox-group',
        label: 'Marque as d├║vidas comuns no seu neg├│cio:',
        hint: 'A IA vai gerar respostas personalizadas para cada uma',
        options: [
          { value: 'pricing', label: '­ƒÆ░ Formas de pagamento / Pre├ºos' },
          { value: 'delivery', label: '­ƒÜÜ Prazos de entrega' },
          { value: 'warranty', label: '­ƒøí´©Å Garantia e trocas' },
          { value: 'support', label: '­ƒô× Suporte e atendimento' },
          { value: 'process', label: '­ƒôï Como funciona o processo' },
          { value: 'coverage', label: '­ƒôì ├ürea de atendimento' }
        ]
      },
      {
        id: 'pricingDetails',
        type: 'textarea',
        label: 'Detalhes sobre pagamento/pre├ºos',
        placeholder: 'Aceitamos cart├úo, pix, boleto... Parcelamos em at├®...',
        conditional: { field: 'commonQuestions', contains: 'pricing' },
        aiEnhance: true
      },
      {
        id: 'deliveryDetails',
        type: 'textarea',
        label: 'Detalhes sobre prazos',
        placeholder: 'O prazo m├®dio ├® de... depende de...',
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
        label: 'Outras d├║vidas frequentes',
        addButtonText: '+ Adicionar Pergunta',
        maxItems: 10,
        fields: [
          {
            id: 'question',
            type: 'text',
            label: 'Pergunta',
            placeholder: 'Ex: Voc├¬s atendem aos finais de semana?'
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

  // ===== PORTF├ôLIO =====
  portfolio: {
    id: 'portfolio',
    pageType: 'portfolio',
    title: '­ƒû╝´©Å Portf├│lio / Trabalhos Realizados',
    subtitle: 'Mostre o que voc├¬ j├í fez de incr├¡vel',
    icon: '­ƒû╝´©Å',
    fields: [
      {
        id: 'portfolioIntro',
        type: 'textarea',
        label: 'Apresenta├º├úo do portf├│lio',
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
            placeholder: 'Ex: Reforma Resid├¬ncia Silva',
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
            label: 'Breve descri├º├úo',
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
        label: 'Grandes clientes que voc├¬ j├í atendeu (opcional)',
        placeholder: 'Digite e pressione Enter',
        hint: 'Nomes de empresas conhecidas geram autoridade'
      },
      {
        id: 'metrics',
        type: 'repeater',
        label: 'M├®tricas de sucesso (opcional)',
        addButtonText: '+ Adicionar m├®trica',
        maxItems: 4,
        hint: 'Ex: "500+ projetos entregues", "98% de satisfa├º├úo"',
        fields: [
          {
            id: 'value',
            type: 'text',
            label: 'N├║mero',
            placeholder: '500+'
          },
          {
            id: 'label',
            type: 'text',
            label: 'Descri├º├úo',
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
    title: '­ƒøì´©Å Vitrine de Produtos',
    subtitle: 'Apresente seus produtos de forma irresist├¡vel',
    icon: '­ƒøì´©Å',
    fields: [
      {
        id: 'storeIntro',
        type: 'textarea',
        label: 'Apresenta├º├úo da loja/produtos',
        placeholder: 'Conhe├ºa nossa linha de produtos...',
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
            label: 'Pre├ºo',
            placeholder: 'R$ 99,90'
          },
          {
            id: 'description',
            type: 'textarea',
            label: 'Descri├º├úo',
            placeholder: 'Caracter├¡sticas, benef├¡cios...',
            rows: 2,
            aiEnhance: true,
            aiPrompt: 'Crie uma descri├º├úo persuasiva de produto para e-commerce'
          },
          {
            id: 'specs',
            type: 'tags',
            label: 'Especifica├º├Áes',
            placeholder: 'Ex: 100% algod├úo, Tamanho M...'
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
        label: 'Informa├º├Áes de frete/entrega',
        placeholder: 'Frete gr├ítis acima de R$...',
        aiEnhance: true
      }
    ]
  },

  // ===== DEPOIMENTOS =====
  depoimentos: {
    id: 'depoimentos',
    pageType: 'depoimentos',
    title: 'Ô¡É Depoimentos de Clientes',
    subtitle: 'O que seus clientes falam de voc├¬?',
    icon: 'Ô¡É',
    fields: [
      {
        id: 'testimonialsIntro',
        type: 'textarea',
        label: 'Introdu├º├úo da se├º├úo',
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
            aiPrompt: 'Melhore este depoimento mantendo a autenticidade, apenas refinando a gram├ítica e tornando mais impactante'
          },
          {
            id: 'authorName',
            type: 'text',
            label: 'Nome do cliente',
            placeholder: 'Jo├úo Silva'
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
            label: 'Avalia├º├úo',
            options: [
              { value: 5, label: 'Ô¡ÉÔ¡ÉÔ¡ÉÔ¡ÉÔ¡É (5 estrelas)' },
              { value: 4, label: 'Ô¡ÉÔ¡ÉÔ¡ÉÔ¡É (4 estrelas)' },
              { value: 3, label: 'Ô¡ÉÔ¡ÉÔ¡É (3 estrelas)' }
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
        label: 'Nota m├®dia no Google/Reclame Aqui (opcional)',
        placeholder: '4.9'
      },
      {
        id: 'totalReviews',
        type: 'text',
        label: 'Total de avalia├º├Áes (opcional)',
        placeholder: '200+'
      }
    ]
  },

  // ===== BLOG =====
  blog_noticias: {
    id: 'blog_noticias',
    pageType: 'blog_noticias',
    title: '­ƒôØ Blog / Not├¡cias',
    subtitle: 'Sobre o que voc├¬ quer escrever?',
    icon: '­ƒôØ',
    fields: [
      {
        id: 'blogPurpose',
        type: 'select',
        label: 'Qual o objetivo do blog?',
        options: [
          { value: 'seo', label: '­ƒöì Atrair visitantes do Google (SEO)' },
          { value: 'authority', label: '­ƒÅå Mostrar autoridade no assunto' },
          { value: 'news', label: '­ƒô░ Divulgar novidades da empresa' },
          { value: 'education', label: '­ƒôÜ Educar clientes sobre o produto/servi├ºo' }
        ]
      },
      {
        id: 'mainTopics',
        type: 'tags',
        label: 'Sobre quais assuntos voc├¬ quer escrever?',
        placeholder: 'Ex: Marketing Digital, Receitas, Decora├º├úo...',
        hint: 'A IA vai sugerir artigos baseados nesses temas'
      },
      {
        id: 'targetKeywords',
        type: 'tags',
        label: 'Palavras-chave que voc├¬ quer ranquear no Google',
        placeholder: 'Ex: "dentista em SP", "reforma de apartamento"...'
      },
      {
        id: 'existingContent',
        type: 'textarea',
        label: 'Voc├¬ j├í tem algum conte├║do pronto? (opcional)',
        placeholder: 'Cole aqui textos que voc├¬ j├í tem...',
        rows: 4
      },
      {
        id: 'postFrequency',
        type: 'select',
        label: 'Com que frequ├¬ncia pretende postar?',
        options: [
          { value: 'weekly', label: 'Toda semana' },
          { value: 'biweekly', label: 'A cada 15 dias' },
          { value: 'monthly', label: 'Uma vez por m├¬s' },
          { value: 'sporadic', label: 'Sem frequ├¬ncia definida' }
        ]
      }
    ]
  },

  // ===== V├ìDEO B├üSICO =====
  video_basico: {
    id: 'video_basico',
    pageType: 'video_basico',
    title: '­ƒÄ¼ V├¡deos do Site',
    subtitle: 'Envie v├¡deos para deixar seu site mais din├ómico',
    icon: '­ƒÄ¼',
    fields: [
      {
        id: 'videoIntro',
        type: 'textarea',
        label: 'Como voc├¬ quer usar os v├¡deos no site?',
        placeholder: 'Quero um v├¡deo de apresenta├º├úo na home, v├¡deos explicativos dos servi├ºos...',
        rows: 2,
        hint: 'Isso nos ajuda a posicionar os v├¡deos corretamente'
      },
      {
        id: 'videos',
        type: 'repeater',
        label: 'Seus V├¡deos',
        addButtonText: '+ Adicionar V├¡deo',
        minItems: 0,
        maxItems: 5,
        hint: 'Formatos aceitos: MP4, WebM, MOV (m├íx. 100MB por v├¡deo)',
        fields: [
          {
            id: 'title',
            type: 'text',
            label: 'T├¡tulo do V├¡deo',
            placeholder: 'Ex: Apresenta├º├úo da Empresa',
            required: true
          },
          {
            id: 'file',
            type: 'upload',
            label: 'Arquivo de V├¡deo',
            accept: 'video/mp4,video/webm,video/quicktime',
            maxSize: 100 * 1024 * 1024, // 100MB
            hint: 'MP4, WebM ou MOV (m├íx. 100MB)'
          },
          {
            id: 'placement',
            type: 'select',
            label: 'Onde usar este v├¡deo?',
            options: [
              { value: 'hero', label: '­ƒÅá Banner Principal (Home)' },
              { value: 'about', label: '­ƒÅó P├ígina Sobre N├│s' },
              { value: 'services', label: 'ÔÜÖ´©Å P├ígina de Servi├ºos' },
              { value: 'background', label: '­ƒÄ¿ V├¡deo de Fundo' },
              { value: 'gallery', label: '­ƒû╝´©Å Galeria de M├¡dia' },
              { value: 'other', label: '­ƒôì Outro local' }
            ],
            default: 'hero'
          },
          {
            id: 'description',
            type: 'text',
            label: 'Descri├º├úo / Legenda (opcional)',
            placeholder: 'Breve descri├º├úo do conte├║do do v├¡deo'
          }
        ]
      },
      {
        id: 'youtubeVideos',
        type: 'repeater',
        label: 'V├¡deos do YouTube (opcional)',
        addButtonText: '+ Adicionar link do YouTube',
        maxItems: 10,
        hint: 'Voc├¬ tamb├®m pode usar v├¡deos que j├í est├úo no YouTube',
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
            label: 'T├¡tulo',
            placeholder: 'T├¡tulo descritivo do v├¡deo'
          }
        ]
      }
    ]
  },

  // ===== V├ìDEO PRO =====
  video_pro: {
    id: 'video_pro',
    pageType: 'video_pro',
    title: '­ƒÄÑ V├¡deos Profissionais',
    subtitle: 'Upload de v├¡deos em alta qualidade com mais recursos',
    icon: '­ƒÄÑ',
    fields: [
      {
        id: 'videoStrategy',
        type: 'select',
        label: 'Qual o objetivo principal dos v├¡deos?',
        options: [
          { value: 'presentation', label: '­ƒÄñ Apresenta├º├úo institucional' },
          { value: 'product', label: '­ƒôª Demonstra├º├úo de produtos' },
          { value: 'tutorial', label: '­ƒôÜ Tutoriais e explica├º├Áes' },
          { value: 'testimonial', label: 'Ô¡É Depoimentos em v├¡deo' },
          { value: 'ambient', label: '­ƒÄ¿ V├¡deos de ambiente/fundo' }
        ]
      },
      {
        id: 'videos',
        type: 'repeater',
        label: 'V├¡deos Profissionais',
        addButtonText: '+ Adicionar V├¡deo',
        minItems: 0,
        maxItems: 15,
        hint: 'Formatos aceitos: MP4, WebM, MOV (m├íx. 1GB por v├¡deo)',
        fields: [
          {
            id: 'title',
            type: 'text',
            label: 'T├¡tulo do V├¡deo',
            placeholder: 'Ex: Tour pela Empresa',
            required: true
          },
          {
            id: 'file',
            type: 'upload',
            label: 'Arquivo de V├¡deo',
            accept: 'video/mp4,video/webm,video/quicktime',
            maxSize: 1024 * 1024 * 1024, // 1GB
            hint: 'MP4, WebM ou MOV (m├íx. 1GB) - Suporta 4K'
          },
          {
            id: 'thumbnail',
            type: 'upload',
            label: 'Thumbnail/Capa (opcional)',
            accept: 'image/*',
            hint: 'Imagem de capa para o v├¡deo'
          },
          {
            id: 'placement',
            type: 'select',
            label: 'Onde usar este v├¡deo?',
            options: [
              { value: 'hero', label: '­ƒÅá Banner Principal (Home)' },
              { value: 'hero-background', label: '­ƒÄ¼ Fundo do Banner (Autoplay)' },
              { value: 'about', label: '­ƒÅó P├ígina Sobre N├│s' },
              { value: 'services', label: 'ÔÜÖ´©Å P├ígina de Servi├ºos' },
              { value: 'products', label: '­ƒøì´©Å P├ígina de Produtos' },
              { value: 'testimonials', label: 'Ô¡É Depoimentos' },
              { value: 'gallery', label: '­ƒû╝´©Å Galeria de M├¡dia' },
              { value: 'popup', label: '­ƒÄ» Popup/Modal' },
              { value: 'other', label: '­ƒôì Outro local' }
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
            label: 'Descri├º├úo do V├¡deo',
            placeholder: 'Descri├º├úo detalhada do conte├║do...',
            rows: 2,
            aiEnhance: true
          }
        ]
      },
      {
        id: 'youtubeVideos',
        type: 'repeater',
        label: 'V├¡deos do YouTube/Vimeo',
        addButtonText: '+ Adicionar link externo',
        maxItems: 20,
        fields: [
          {
            id: 'url',
            type: 'text',
            label: 'Link do V├¡deo',
            placeholder: 'YouTube, Vimeo ou outro'
          },
          {
            id: 'title',
            type: 'text',
            label: 'T├¡tulo',
            placeholder: 'T├¡tulo do v├¡deo'
          },
          {
            id: 'placement',
            type: 'select',
            label: 'Onde exibir',
            options: [
              { value: 'gallery', label: '­ƒû╝´©Å Galeria' },
              { value: 'inline', label: '­ƒôä Inline na p├ígina' }
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
    title: '­ƒôä Documentos e PDFs',
    subtitle: 'Cat├ílogos, portf├│lios, tabelas de pre├ºo e materiais para download',
    icon: '­ƒôä',
    fields: [
      {
        id: 'documentPurpose',
        type: 'checkbox-group',
        label: 'Que tipo de documentos voc├¬ quer disponibilizar?',
        options: [
          { value: 'catalog', label: '­ƒôÜ Cat├ílogo de Produtos/Servi├ºos' },
          { value: 'price', label: '­ƒÆ░ Tabela de Pre├ºos' },
          { value: 'portfolio', label: '­ƒû╝´©Å Portf├│lio em PDF' },
          { value: 'manual', label: '­ƒôï Manuais e Instru├º├Áes' },
          { value: 'contract', label: '­ƒôØ Modelos de Contrato' },
          { value: 'ebook', label: '­ƒôû E-book / Material Rico' },
          { value: 'certificate', label: '­ƒÅå Certifica├º├Áes e Licen├ºas' },
          { value: 'other', label: '­ƒôÄ Outros documentos' }
        ],
        hint: 'Selecione os tipos que voc├¬ pretende disponibilizar'
      },
      {
        id: 'documents',
        type: 'repeater',
        label: 'Seus Documentos',
        addButtonText: '+ Adicionar Documento',
        minItems: 0,
        maxItems: 20,
        hint: 'PDFs at├® 150MB cada. Limite total: 500MB',
        fields: [
          {
            id: 'title',
            type: 'text',
            label: 'Nome do Documento',
            placeholder: 'Ex: Cat├ílogo de Produtos 2026',
            required: true
          },
          {
            id: 'file',
            type: 'upload',
            label: 'Arquivo PDF',
            accept: 'application/pdf',
            maxSize: 150 * 1024 * 1024, // 150MB
            hint: 'Apenas PDF (m├íx. 150MB)'
          },
          {
            id: 'category',
            type: 'select',
            label: 'Categoria',
            options: [
              { value: 'catalog', label: '­ƒôÜ Cat├ílogo' },
              { value: 'price', label: '­ƒÆ░ Tabela de Pre├ºos' },
              { value: 'portfolio', label: '­ƒû╝´©Å Portf├│lio' },
              { value: 'manual', label: '­ƒôï Manual' },
              { value: 'ebook', label: '­ƒôû E-book' },
              { value: 'certificate', label: '­ƒÅå Certificado' },
              { value: 'other', label: '­ƒôÄ Outro' }
            ]
          },
          {
            id: 'description',
            type: 'text',
            label: 'Descri├º├úo (aparece no bot├úo de download)',
            placeholder: 'Ex: Baixe nosso cat├ílogo completo'
          },
          {
            id: 'requireEmail',
            type: 'checkbox',
            label: 'Exigir e-mail para download (captura de leads)'
          },
          {
            id: 'isPublic',
            type: 'checkbox',
            label: 'Dispon├¡vel para download p├║blico no site',
            default: true
          }
        ]
      },
      {
        id: 'downloadPageTitle',
        type: 'text',
        label: 'T├¡tulo da P├ígina de Downloads (se houver)',
        placeholder: 'Ex: Materiais, Downloads, Documentos...',
        default: 'Downloads'
      },
      {
        id: 'downloadPageIntro',
        type: 'textarea',
        label: 'Texto de introdu├º├úo da p├ígina de downloads',
        placeholder: 'Baixe nossos materiais gratuitamente...',
        rows: 2,
        aiEnhance: true
      }
    ]
  }
};

/**
 * Step de Finaliza├º├úo - SEMPRE APARECE POR ├ÜLTIMO
 */
export const finalizationStep = {
  id: 'finalization',
  title: 'Quase L├í!',
  subtitle: '├Ültimos detalhes antes de come├ºarmos',
  icon: '­ƒÜÇ',
  required: true,
  fields: [
    {
      id: 'additionalNotes',
      type: 'textarea',
      label: 'Tem algo mais que devemos saber?',
      placeholder: 'Refer├¬ncias de sites que voc├¬ gosta, prefer├¬ncias especiais, observa├º├Áes...',
      rows: 4
    },
    {
      id: 'referenceUrls',
      type: 'repeater',
      label: 'Sites que voc├¬ gosta como refer├¬ncia',
      addButtonText: '+ Adicionar refer├¬ncia',
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
          label: 'O que voc├¬ gosta nele?',
          placeholder: 'Ex: As cores, o layout, as anima├º├Áes...'
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
  // P├íginas principais
  about: 'sobre_nos',
  services: 'servicos',
  portfolio: 'portfolio',
  faq: 'faq',
  contact: 'config_contato', // Contact j├í ├® coberto pelo contactStep + config_contato
  blog: 'blog_noticias',
  showcase: 'vitrine_produtos',
  
  // Conte├║do de m├¡dia
  video_basic: 'video_basico',
  video_pro: 'video_pro',
  pdf: 'documentos_pdf',
  
  // Depoimentos (caso exista)
  testimonials: 'depoimentos'
};

/**
 * Fun├º├úo para montar os steps baseado nas p├íginas compradas
 * @param {Array} purchasedPages - Array de page_types que o usu├írio comprou (chaves do order)
 * @returns {Array} - Array de steps ordenados para o wizard
 */
export function buildOnboardingSteps(purchasedPages = []) {
  const steps = [];
  
  // Garantir que purchasedPages ├® um array
  const pages = Array.isArray(purchasedPages) ? purchasedPages : [];
  
  // 1. Sempre come├ºa com Identidade
  steps.push(identityStep);
  
  // 2. Sempre inclui Contato
  steps.push(contactStep);
  
  // 3. Sempre inclui Configura├º├úo de Leads (ap├│s contato b├ísico)
  steps.push(pageSteps.config_contato);
  
  // 4. Adiciona steps espec├¡ficos das p├íginas compradas
  pages.forEach(pageKey => {
    // Mapeia a chave do order para o ID do step
    const stepId = pageKeyToStepId[pageKey] || pageKey;
    
    // Pula config_contato e contact pois j├í foram adicionados
    if (stepId === 'config_contato' || pageKey === 'contact') {
      return;
    }
    
    // Adiciona se o step existir
    if (pageSteps[stepId]) {
      steps.push(pageSteps[stepId]);
    } else {
      console.warn(`[buildOnboardingSteps] Step n├úo encontrado para: ${pageKey} (mapeado: ${stepId})`);
    }
  });
  
  // 5. Sempre termina com Finaliza├º├úo
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
