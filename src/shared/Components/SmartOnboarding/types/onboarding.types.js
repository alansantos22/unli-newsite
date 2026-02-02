/**
 * ============================================
 * SMART ONBOARDING - TYPES & CONSTANTS
 * ============================================
 * 
 * Definição de tipos, constantes e estruturas
 * para o sistema de onboarding inteligente.
 */

// ============================================
// CAMPO STATUS - Checklist de preenchimento
// ============================================

/**
 * Status de cada campo do formulário
 * @typedef {'empty' | 'answered' | 'skipped' | 'confirmed'} FieldStatus
 * 
 * empty     - Nunca foi respondido
 * answered  - Usuário forneceu um valor
 * skipped   - Usuário disse que não tem/não quer
 * confirmed - Confirmado no resumo do step
 */
export const FIELD_STATUS = {
  EMPTY: 'empty',
  ANSWERED: 'answered',
  SKIPPED: 'skipped',
  CONFIRMED: 'confirmed'
};

// ============================================
// STEPS DO ONBOARDING
// ============================================

export const ONBOARDING_STEPS = [
  {
    id: 'identity',
    name: 'Identidade da Marca',
    icon: '🎨',
    description: 'Nome, ramo, cores e personalidade',
    xpReward: 100,
    fields: [
      { id: 'companyName', label: 'Nome da Empresa', type: 'text', required: true, xp: 15 },
      { id: 'businessType', label: 'Ramo de Atuação', type: 'select', required: true, xp: 10, 
        options: ['alimentacao', 'saude', 'beleza', 'educacao', 'tecnologia', 'construcao', 'moda', 'servicos', 'juridico', 'eventos', 'turismo', 'imoveis', 'automotivo', 'pets', 'comercio', 'industria', 'outro'] },
      { id: 'siteObjective', label: 'Objetivo do Site', type: 'select', required: false, xp: 20,
        options: ['essential', 'authority', 'enterprise'],
        optionLabels: {
          'essential': '🏢 Essencial - Ser encontrado online',
          'authority': '🚀 Autoridade - Mostrar trabalho e fechar contratos',
          'enterprise': '💎 Ecossistema Digital - Blog e vitrine de produtos'
        }
      },
      { id: 'frase', label: 'Frase/Slogan', type: 'text', required: false, xp: 15 },
      { id: 'primaryColor', label: 'Cor Principal', type: 'color', required: false, xp: 10 },
      { id: 'secondaryColor', label: 'Cor Secundária', type: 'color', required: false, xp: 5 },
      { id: 'voiceTone', label: 'Tom de Voz', type: 'select', required: false, xp: 5,
        options: ['profissional', 'amigavel', 'descontraido', 'luxuoso', 'tecnico', 'inspirador'] },
      { id: 'logo', label: 'Logo', type: 'image', required: false, xp: 20 },
      { id: 'hasNoLogo', label: 'Sem Logo', type: 'boolean', required: false, xp: 0, hidden: true }
    ]
  },
  {
    id: 'contact',
    name: 'Contato e Localização',
    icon: '📞',
    description: 'WhatsApp, redes e endereço',
    xpReward: 80,
    fields: [
      { id: 'whatsapp', label: 'WhatsApp Principal', type: 'phone', required: true, xp: 15 },
      { id: 'additionalPhones', label: 'Telefones Adicionais', type: 'phones', required: false, xp: 5, multiple: true },
      { id: 'email', label: 'E-mail', type: 'email', required: false, xp: 10 },
      { id: 'instagram', label: 'Instagram', type: 'social', required: false, xp: 5 },
      { id: 'facebook', label: 'Facebook', type: 'social', required: false, xp: 5 },
      { id: 'linkedin', label: 'LinkedIn', type: 'social', required: false, xp: 5 },
      { id: 'youtube', label: 'YouTube', type: 'social', required: false, xp: 5 },
      { id: 'tiktok', label: 'TikTok', type: 'social', required: false, xp: 5 },
      { id: 'hasPhysicalLocation', label: 'Tem Endereço Físico', type: 'boolean', required: false, xp: 0 },
      { id: 'addressCep', label: 'CEP', type: 'cep', required: false, xp: 5 },
      { id: 'addressStreet', label: 'Rua', type: 'text', required: false, xp: 0 },
      { id: 'addressNumber', label: 'Número', type: 'text', required: false, xp: 0 },
      { id: 'addressComplement', label: 'Complemento', type: 'text', required: false, xp: 0 },
      { id: 'addressNeighborhood', label: 'Bairro', type: 'text', required: false, xp: 0 },
      { id: 'addressCity', label: 'Cidade', type: 'text', required: false, xp: 0 },
      { id: 'addressState', label: 'Estado', type: 'text', required: false, xp: 0 },
      { id: 'businessHours', label: 'Horário de Funcionamento', type: 'text', required: false, xp: 10 }
    ]
  },
  {
    id: 'about',
    name: 'História da Empresa',
    icon: '🏢',
    description: 'Quem vocês são e como começaram',
    xpReward: 100,
    fields: [
      { id: 'aboutSectionTitle', label: 'Título da Seção', type: 'text', required: false, xp: 5 },
      { id: 'companyBio', label: 'Sobre a Empresa', type: 'textarea', required: false, xp: 25 },
      { id: 'foundingYear', label: 'Ano de Fundação', type: 'year', required: false, xp: 5 },
      { id: 'founders', label: 'Fundadores', type: 'text', required: false, xp: 10 },
      { id: 'aboutImage', label: 'Imagem da Empresa', type: 'image', required: false, xp: 15 },
      { id: 'companyHighlights', label: 'Destaques', type: 'list', required: false, xp: 10 },
      { id: 'showMissionVision', label: 'Mostrar Missão/Visão', type: 'boolean', required: false, xp: 0 },
      { id: 'mission', label: 'Missão', type: 'textarea', required: false, xp: 10 },
      { id: 'vision', label: 'Visão', type: 'textarea', required: false, xp: 10 },
      { id: 'values', label: 'Valores', type: 'list', required: false, xp: 10 }
    ]
  },
  {
    id: 'services',
    name: 'Serviços e Soluções',
    icon: '⚙️',
    description: 'O que vocês fazem de melhor',
    xpReward: 100,
    fields: [
      { id: 'servicesSectionTitle', label: 'Título da Seção', type: 'text', required: false, xp: 5 },
      { id: 'servicesIntro', label: 'Introdução', type: 'textarea', required: false, xp: 10 },
      { id: 'services', label: 'Serviços', type: 'services', required: false, xp: 50, multiple: true },
      { id: 'hasGuarantee', label: 'Tem Garantia', type: 'boolean', required: false, xp: 5 },
      { id: 'guaranteeDetails', label: 'Detalhes da Garantia', type: 'textarea', required: false, xp: 10 }
    ]
  },
  {
    id: 'faq',
    name: 'Perguntas Frequentes',
    icon: '❓',
    description: 'Dúvidas comuns dos clientes',
    xpReward: 80,
    fields: [
      { id: 'faqItems', label: 'Perguntas e Respostas', type: 'faq', required: false, xp: 60, multiple: true }
    ]
  },
  {
    id: 'finalization',
    name: 'Finalização',
    icon: '🚀',
    description: 'Últimos detalhes e preferências',
    xpReward: 50,
    fields: [
      { id: 'additionalNotes', label: 'Observações', type: 'textarea', required: false, xp: 10 },
      { id: 'urgency', label: 'Urgência', type: 'select', required: false, xp: 5,
        options: ['urgent', 'normal', 'relaxed'] },
      { id: 'inspirationUrls', label: 'URLs de Inspiração', type: 'urls', required: false, xp: 10, multiple: true }
    ]
  }
];

// ============================================
// NÍVEIS DO SITE (Gamificação)
// ============================================

export const SITE_LEVELS = [
  { level: 1, name: 'Rascunho', minXP: 0, maxXP: 49, icon: '📝', color: '#6c757d', description: 'Começando a dar forma' },
  { level: 2, name: 'Esboço', minXP: 50, maxXP: 99, icon: '✏️', color: '#17a2b8', description: 'Ganhando contornos' },
  { level: 3, name: 'Estruturado', minXP: 100, maxXP: 199, icon: '🏗️', color: '#28a745', description: 'Base sólida' },
  { level: 4, name: 'Profissional', minXP: 200, maxXP: 349, icon: '💼', color: '#007bff', description: 'Visual profissional' },
  { level: 5, name: 'Premium', minXP: 350, maxXP: 499, icon: '💎', color: '#6f42c1', description: 'Alto padrão' },
  { level: 6, name: 'Lendário', minXP: 500, maxXP: Infinity, icon: '🚀', color: '#fd7e14', description: 'Perfeição alcançada' }
];

// ============================================
// CONQUISTAS
// ============================================

export const ACHIEVEMENTS = {
  first_field: { id: 'first_field', name: 'Primeiro Passo', icon: '👣', xp: 5, description: 'Preencheu o primeiro campo' },
  identity_complete: { id: 'identity_complete', name: 'Identidade Definida', icon: '🎨', xp: 20, description: 'Completou a identidade' },
  contact_complete: { id: 'contact_complete', name: 'Conectado', icon: '📞', xp: 15, description: 'Completou os contatos' },
  story_complete: { id: 'story_complete', name: 'Contador de Histórias', icon: '📖', xp: 20, description: 'Contou sua história' },
  services_complete: { id: 'services_complete', name: 'Catálogo Pronto', icon: '⚙️', xp: 20, description: 'Listou seus serviços' },
  faq_complete: { id: 'faq_complete', name: 'Tira-Dúvidas', icon: '❓', xp: 15, description: 'Criou o FAQ' },
  site_complete: { id: 'site_complete', name: 'Site Completo', icon: '🚀', xp: 50, description: 'Finalizou todo o site' },
  speed_demon: { id: 'speed_demon', name: 'Veloz', icon: '⚡', xp: 10, description: 'Completou um step em menos de 2 minutos' },
  detailed: { id: 'detailed', name: 'Detalhista', icon: '🔍', xp: 15, description: 'Preencheu todos os campos opcionais de um step' }
};

// ============================================
// NOTA: WELCOME_MESSAGES FOI REMOVIDO!
// ============================================
// 
// O Gemini agora é o ÚNICO cérebro que controla a conversa.
// Nada de frases prontas ou templates.
// A IA cria mensagens personalizadas em tempo real baseadas no:
// - Nome da empresa (quando disponível)
// - Etapa atual
// - Campos pendentes
// - Horário do dia
// 
// Isso garante uma experiência de CONSULTOR REAL, não de robô.
// ============================================

// ============================================
// SONS DO SISTEMA
// ============================================

export const SOUNDS = {
  fieldComplete: '/sounds/field-complete.mp3',
  stepComplete: '/sounds/step-complete.mp3',
  achievement: '/sounds/achievement.mp3',
  levelUp: '/sounds/level-up.mp3',
  error: '/sounds/error.mp3',
  send: '/sounds/send.mp3'
};

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Encontra o step atual pelo ID
 */
export function getStepById(stepId) {
  return ONBOARDING_STEPS.find(s => s.id === stepId);
}

/**
 * Encontra o campo pelo ID dentro de um step
 */
export function getFieldById(stepId, fieldId) {
  const step = getStepById(stepId);
  return step?.fields.find(f => f.id === fieldId);
}

/**
 * Retorna todos os campos de todos os steps
 */
export function getAllFields() {
  return ONBOARDING_STEPS.flatMap(step => 
    step.fields.map(field => ({ ...field, stepId: step.id }))
  );
}

/**
 * Calcula o nível baseado no XP
 */
export function getLevelByXP(xp) {
  return SITE_LEVELS.find(level => xp >= level.minXP && xp <= level.maxXP) || SITE_LEVELS[0];
}

/**
 * Calcula progresso para o próximo nível
 */
export function getLevelProgress(xp) {
  const level = getLevelByXP(xp);
  const nextLevel = SITE_LEVELS.find(l => l.level === level.level + 1);
  
  if (!nextLevel) return 100; // Nível máximo
  
  const levelXP = xp - level.minXP;
  const levelRange = level.maxXP - level.minXP + 1;
  
  return Math.min(100, Math.round((levelXP / levelRange) * 100));
}
