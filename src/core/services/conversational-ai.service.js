/**
 * ============================================
 * CONVERSATIONAL AI SERVICE
 * Gerencia o estado do chat + formulário
 * ============================================
 * 
 * Fonte única de verdade para o onboarding conversacional.
 * Sincroniza dados entre chat (IA) e formulário (edição manual).
 */

import { reactive, computed, readonly, watch, nextTick } from 'vue';
import { buildOnboardingSteps } from '@/core/config/onboarding-steps.config.js';

// ============================================
// CONFIGURAÇÃO DOS STEPS (BASE)
// ============================================

// Steps básicos - usados como fallback ou quando sem purchasedPages
export const ONBOARDING_STEPS_BASE = [
  {
    id: 'identity',
    name: 'Identidade da Marca',
    icon: '🎨',
    description: 'Nome, ramo, cores e personalidade',
    requiredFields: ['companyName', 'businessType'],
    optionalFields: ['frase', 'primaryColor', 'secondaryColor', 'voiceTone', 'logo'],
    xpReward: 15,
    achievement: {
      id: 'identity_unlocked',
      name: 'Identidade Desbloqueada',
      icon: '🎨'
    }
  },
  {
    id: 'contact',
    name: 'Contato e Localização',
    icon: '📞',
    description: 'WhatsApp, redes e endereço',
    requiredFields: ['whatsapp'],
    optionalFields: ['email', 'additionalPhones', 'socialNetworks', 'hasPhysicalLocation', 'addressCep', 'addressStreet', 'addressNumber', 'addressNeighborhood', 'addressCity', 'addressState', 'businessHours'],
    xpReward: 10,
    achievement: {
      id: 'connection_established',
      name: 'Conexão Estabelecida',
      icon: '📞'
    }
  },
  {
    id: 'leads',
    name: 'Configuração de Leads',
    icon: '📨',
    description: 'Formulário de contato e captação',
    requiredFields: ['leadEmail'],
    optionalFields: ['leadEmailCC', 'formFields', 'whatsappFloatingEnabled', 'whatsappPosition', 'whatsappGreeting', 'showMap', 'enableCaptcha', 'autoReply', 'autoReplyMessage'],
    xpReward: 10,
    achievement: {
      id: 'leads_configured',
      name: 'Leads Configurados',
      icon: '📨'
    }
  },
  {
    id: 'about',
    name: 'Sobre a Empresa',
    icon: '🏢',
    description: 'Quem vocês são e como começaram',
    requiredFields: [],
    optionalFields: ['companyBio', 'foundingYear', 'founders', 'aboutImage', 'companyHighlights', 'showMissionVision', 'mission', 'vision', 'values'],
    xpReward: 20,
    achievement: {
      id: 'story_mastered',
      name: 'História Masterizada',
      icon: '📖'
    }
  },
  {
    id: 'services',
    name: 'Serviços e Soluções',
    icon: '⚙️',
    description: 'O que vocês fazem de melhor',
    requiredFields: [],
    optionalFields: ['servicesIntro', 'services', 'hasGuarantee', 'guaranteeDetails'],
    xpReward: 15,
    achievement: {
      id: 'services_catalog',
      name: 'Catálogo de Serviços',
      icon: '⚙️'
    }
  },
  {
    id: 'portfolio',
    name: 'Portfólio',
    icon: '🖼️',
    description: 'Trabalhos realizados e cases',
    requiredFields: [],
    optionalFields: ['portfolioIntro', 'projects', 'bigClients', 'metrics'],
    xpReward: 15,
    achievement: {
      id: 'portfolio_showcase',
      name: 'Portfólio Completo',
      icon: '🖼️'
    }
  },
  {
    id: 'faq',
    name: 'Perguntas Frequentes',
    icon: '❓',
    description: 'Dúvidas comuns dos clientes',
    requiredFields: [],
    optionalFields: ['faqItems', 'faqCategories'],
    xpReward: 10,
    achievement: {
      id: 'faq_strategic',
      name: 'FAQ Estratégico',
      icon: '🎯'
    }
  },
  {
    id: 'finalization',
    name: 'Finalização',
    icon: '🚀',
    description: 'Últimos detalhes e preferências',
    requiredFields: [],
    optionalFields: ['additionalNotes', 'urgency', 'inspirationUrls', 'inspirationImages'],
    xpReward: 10,
    achievement: null
  }
];

// ============================================
// STEPS OPCIONAIS (aparecem baseado em purchasedPages)
// ============================================

export const OPTIONAL_STEPS = {
  testimonials: {
    id: 'testimonials',
    name: 'Depoimentos',
    icon: '⭐',
    description: 'O que seus clientes dizem',
    requiredFields: [],
    optionalFields: ['testimonialsIntro', 'testimonials', 'averageRating', 'totalReviews'],
    xpReward: 10,
    achievement: {
      id: 'social_proof',
      name: 'Prova Social',
      icon: '⭐'
    },
    pageKey: 'testimonials' // chave do purchasedPages
  },
  blog: {
    id: 'blog',
    name: 'Blog / Notícias',
    icon: '📝',
    description: 'Conteúdo para atrair visitantes',
    requiredFields: [],
    optionalFields: ['blogPurpose', 'mainTopics', 'targetKeywords', 'postFrequency'],
    xpReward: 10,
    achievement: {
      id: 'content_strategy',
      name: 'Estratégia de Conteúdo',
      icon: '📝'
    },
    pageKey: 'blog'
  },
  showcase: {
    id: 'showcase',
    name: 'Vitrine de Produtos',
    icon: '🛍️',
    description: 'Seus produtos em destaque',
    requiredFields: [],
    optionalFields: ['storeIntro', 'products', 'shippingInfo'],
    xpReward: 15,
    achievement: {
      id: 'catalog_ready',
      name: 'Catálogo Pronto',
      icon: '🛍️'
    },
    pageKey: 'showcase'
  },
  video: {
    id: 'video',
    name: 'Vídeos',
    icon: '🎬',
    description: 'Vídeos do site',
    requiredFields: [],
    optionalFields: ['videoIntro', 'videos', 'youtubeVideos'],
    xpReward: 10,
    achievement: {
      id: 'multimedia_ready',
      name: 'Multimídia Pronta',
      icon: '🎬'
    },
    pageKey: 'video_basic' // ou video_pro
  },
  documents: {
    id: 'documents',
    name: 'Documentos PDF',
    icon: '📄',
    description: 'Catálogos e materiais para download',
    requiredFields: [],
    optionalFields: ['documentPurpose', 'documents', 'downloadPageTitle', 'downloadPageIntro'],
    xpReward: 10,
    achievement: {
      id: 'materials_ready',
      name: 'Materiais Prontos',
      icon: '📄'
    },
    pageKey: 'pdf'
  }
};

// ============================================
// SISTEMA DE CONQUISTAS
// ============================================

export const ACHIEVEMENTS = {
  identity_unlocked: { id: 'identity_unlocked', name: 'Identidade Desbloqueada', icon: '🎨', xp: 15 },
  connection_established: { id: 'connection_established', name: 'Conexão Estabelecida', icon: '📞', xp: 10 },
  leads_configured: { id: 'leads_configured', name: 'Leads Configurados', icon: '📨', xp: 10 },
  story_mastered: { id: 'story_mastered', name: 'História Masterizada', icon: '📖', xp: 20 },
  services_catalog: { id: 'services_catalog', name: 'Catálogo de Serviços', icon: '⚙️', xp: 15 },
  portfolio_showcase: { id: 'portfolio_showcase', name: 'Portfólio Completo', icon: '🖼️', xp: 15 },
  faq_strategic: { id: 'faq_strategic', name: 'FAQ Estratégico', icon: '🎯', xp: 10 },
  social_proof: { id: 'social_proof', name: 'Prova Social', icon: '⭐', xp: 10 },
  content_strategy: { id: 'content_strategy', name: 'Estratégia de Conteúdo', icon: '📝', xp: 10 },
  catalog_ready: { id: 'catalog_ready', name: 'Catálogo Pronto', icon: '🛍️', xp: 15 },
  multimedia_ready: { id: 'multimedia_ready', name: 'Multimídia Pronta', icon: '🎬', xp: 10 },
  materials_ready: { id: 'materials_ready', name: 'Materiais Prontos', icon: '📄', xp: 10 },
  profile_complete: { id: 'profile_complete', name: 'Perfil Completo', icon: '💎', xp: 30 },
  launch_ready: { id: 'launch_ready', name: 'Pronto pro Lançamento', icon: '🚀', xp: 50 }
};

export const SITE_LEVELS = [
  { level: 1, name: 'Rascunho', minXP: 0, maxXP: 24, icon: '📝', color: '#6c757d' },
  { level: 2, name: 'Básico', minXP: 25, maxXP: 49, icon: '🌱', color: '#28a745' },
  { level: 3, name: 'Estruturado', minXP: 50, maxXP: 74, icon: '🏗️', color: '#17a2b8' },
  { level: 4, name: 'Profissional', minXP: 75, maxXP: 99, icon: '💼', color: '#007bff' },
  { level: 5, name: 'Premium', minXP: 100, maxXP: 149, icon: '💎', color: '#6f42c1' },
  { level: 6, name: 'Lendário', minXP: 150, maxXP: Infinity, icon: '🚀', color: '#fd7e14' }
];

// ============================================
// ESTADO REATIVO GLOBAL
// ============================================

const state = reactive({
  // Identificação da sessão
  sessionId: null,
  
  // Step atual
  currentStepIndex: 0,
  
  // Modo de visualização
  mode: 'chat', // 'chat' | 'review' (Mesa de Edição)
  
  // Dados do formulário (fonte única de verdade)
  formData: {
    // Identity
    companyName: '',
    businessType: '',
    frase: '',
    primaryColor: null,      // null = não definido (diferente de valor default)
    secondaryColor: null,    // null = não definido
    voiceTone: 'profissional',
    logo: null,
    hasNoLogo: false,
    
    // Contact
    whatsapp: '',
    email: '',  // E-mail institucional do site
    additionalPhones: [],
    socialNetworks: [],
    hasPhysicalLocation: null,  // null = não perguntado, false = não tem, true = tem
    addressCep: '',
    addressStreet: '',
    addressNumber: '',
    addressComplement: '',
    addressNeighborhood: '',
    addressCity: '',
    addressState: '',
    businessHours: '',
    
    // About
    aboutSectionTitle: 'Sobre Nós',
    companyBio: '',
    foundingYear: null,
    founders: '',
    aboutImage: null,
    companyHighlights: [],
    showMissionVision: false,
    mission: '',
    vision: '',
    values: [],
    
    // Services
    servicesSectionTitle: 'Nossos Serviços',
    servicesIntro: '',
    services: [],
    hasGuarantee: false,
    guaranteeDetails: '',
    
    // FAQ
    faqItems: [],
    
    // Lead Config
    leadEmail: '',
    leadEmailCC: '',
    formFields: {
      phone: { enabled: true, required: false },
      message: { enabled: true, required: true },
      subject: { enabled: false, required: false },
      company: { enabled: false, required: false },
      city: { enabled: false, required: false },
      service: { enabled: false, required: false },
      attachment: { enabled: false, required: false }
    },
    whatsappFloatingEnabled: true,
    whatsappPosition: 'bottom-right',
    whatsappGreeting: '',
    showMap: true,
    enableCaptcha: true,
    autoReply: false,
    autoReplyMessage: '',
    
    // Portfolio
    portfolioIntro: '',
    projects: [],
    bigClients: [],
    metrics: [],
    
    // Testimonials (opcional - baseado em purchasedPages)
    testimonialsIntro: '',
    testimonials: [],
    averageRating: '',
    totalReviews: '',
    
    // Blog (opcional - baseado em purchasedPages)
    blogPurpose: '',
    mainTopics: [],
    targetKeywords: [],
    postFrequency: '',
    
    // Showcase/Vitrine (opcional - baseado em purchasedPages)
    storeIntro: '',
    products: [],
    shippingInfo: '',
    
    // Video (opcional - baseado em purchasedPages)
    videoIntro: '',
    videos: [],
    youtubeVideos: [],
    
    // Documents/PDF (opcional - baseado em purchasedPages)
    documentPurpose: [],
    documents: [],
    downloadPageTitle: 'Downloads',
    downloadPageIntro: '',
    
    // Finalization
    additionalNotes: '',
    urgency: 'normal',
    inspirationUrls: [],
    inspirationImages: []
  },
  
  // Campos que o usuário JÁ RESPONDEU (independente do valor)
  // Isso permite diferenciar "não perguntado" de "respondeu vazio/false"
  answeredFields: [],
  
  // Histórico de mensagens do chat
  messages: [],
  
  // Estado do chat
  isTyping: false,
  isRecording: false,
  isProcessing: false,
  
  // Gamificação
  xp: 0,
  unlockedAchievements: [],
  pendingAchievement: null, // Para mostrar popup
  
  // Campos com shimmer (carregando)
  shimmeringFields: [],
  
  // Campos recém-preenchidos (para animação de check)
  recentlyFilledFields: [],
  
  // Erros de validação (só aparecem na Mesa de Edição)
  validationErrors: {},
  
  // Campos pendentes por step
  pendingFieldsByStep: {},
  
  // Histórico para Undo/Redo
  formHistory: [],
  historyIndex: -1,
  maxHistorySize: 50,
  
  // Flag para modo demo (sem token real)
  isDemoMode: false,
  
  // Contador de falhas da IA
  aiFailureCount: 0,
  maxAiFailures: 3,
  
  // Páginas compradas pelo cliente (para steps dinâmicos)
  purchasedPages: [],
  
  // Avisos de formatação já mostrados (evita duplicação)
  shownFormatWarnings: new Set(),
  
  // Flag para evitar inicialização duplicada
  _isInitialized: false,
  _initializingSessionId: null
});

// ============================================
// COMPUTED PROPERTIES
// ============================================

// Steps dinâmicos baseados nas páginas compradas
const activeSteps = computed(() => {
  if (state.purchasedPages && state.purchasedPages.length > 0) {
    return buildOnboardingSteps(state.purchasedPages);
  }
  // Fallback para steps básicos
  return ONBOARDING_STEPS_BASE;
});

const currentStep = computed(() => {
  const steps = activeSteps.value;
  const maxIndex = steps.length - 1;
  // Garante que o índice esteja dentro do range válido
  const safeIndex = Math.min(Math.max(0, state.currentStepIndex), maxIndex);
  return steps[safeIndex] || steps[0];
});

const currentStepId = computed(() => currentStep.value?.id || 'identity');

const totalSteps = computed(() => activeSteps.value.length);

const progressPercentage = computed(() => {
  const totalFields = activeSteps.value.reduce((acc, step) => {
    const requiredCount = step.requiredFields?.length || 0;
    const optionalCount = step.optionalFields?.length || 0;
    return acc + requiredCount + optionalCount;
  }, 0);
  
  // eslint-disable-next-line no-unused-vars
  const filledFields = Object.entries(state.formData).filter(([key, value]) => {
    if (value === null || value === '' || value === false) return false;
    if (Array.isArray(value) && value.length === 0) return false;
    if (typeof value === 'object' && Object.keys(value).length === 0) return false;
    return true;
  }).length;
  
  return Math.min(100, Math.round((filledFields / totalFields) * 100));
});

const currentLevel = computed(() => {
  return SITE_LEVELS.find(level => state.xp >= level.minXP && state.xp <= level.maxXP) || SITE_LEVELS[0];
});

const nextLevel = computed(() => {
  const currentIndex = SITE_LEVELS.findIndex(l => l.level === currentLevel.value.level);
  return SITE_LEVELS[currentIndex + 1] || null;
});

const xpToNextLevel = computed(() => {
  if (!nextLevel.value) return 0;
  return nextLevel.value.minXP - state.xp;
});

const isReviewMode = computed(() => state.mode === 'review');

const canUndo = computed(() => state.historyIndex > 0);

const canRedo = computed(() => state.historyIndex < state.formHistory.length - 1);

const canPublish = computed(() => {
  // Verificar campos obrigatórios de todos os steps
  for (const step of activeSteps.value) {
    if (!step.requiredFields) continue;
    for (const field of step.requiredFields) {
      const value = state.formData[field];
      if (!value || (Array.isArray(value) && value.length === 0)) {
        return false;
      }
    }
  }
  return true;
});

const pendingRequiredFields = computed(() => {
  const pending = [];
  for (const step of activeSteps.value) {
    if (!step.requiredFields) continue;
    for (const field of step.requiredFields) {
      const value = state.formData[field];
      if (!value || (Array.isArray(value) && value.length === 0)) {
        pending.push({ stepId: step.id, stepName: step.name, field });
      }
    }
  }
  return pending;
});

// ============================================
// ACTIONS
// ============================================

/**
 * Inicializa uma nova sessão de onboarding
 * @param {string} sessionId - ID da sessão
 * @param {object} initialData - Dados iniciais do formulário
 * @param {string[]} purchasedPages - Array com IDs das páginas compradas
 */
function initSession(sessionId, initialData = {}, purchasedPages = [], initialStepFromBackend = null) {
  const targetSessionId = sessionId || `session_${Date.now()}`;
  
  // Evitar inicialização duplicada para a mesma sessão
  if (state._isInitialized && state.sessionId === targetSessionId) {
    console.log('[InitSession] Sessão já inicializada, ignorando chamada duplicada');
    return;
  }
  
  // Marcar como em processo de inicialização
  state._initializingSessionId = targetSessionId;
  
  state.sessionId = targetSessionId;
  
  // Detectar modo demo (IDs locais ou que começam com 'demo-' ou 'session_')
  state.isDemoMode = !sessionId || sessionId.startsWith('demo-') || sessionId.startsWith('session_');
  
  // Resetar contador de falhas da IA
  state.aiFailureCount = 0;
  
  // Limpar avisos de formatação já mostrados (nova sessão = novos avisos)
  state.shownFormatWarnings.clear();
  
  // Resetar campos respondidos (será populado com dados do backend/localStorage)
  state.answeredFields = [];
  
  // Definir páginas compradas (para steps dinâmicos)
  state.purchasedPages = purchasedPages || [];
  
  // Cores default que não devem ser consideradas como preenchidas
  const defaultColors = ['#0066CC', '#28A745'];
  
  // PRIMEIRO: Aplicar dados do backend (initialData tem precedência máxima)
  if (initialData && typeof initialData === 'object' && Object.keys(initialData).length > 0) {
    console.log('[InitSession] Carregando dados do backend:', Object.keys(initialData).filter(k => initialData[k]).length, 'campos');
    
    // Verificar se o backend retornou answered_fields salvos anteriormente
    if (Array.isArray(initialData._answered_fields)) {
      state.answeredFields = [...initialData._answered_fields];
      console.log('[InitSession] Restaurando answered_fields do backend:', state.answeredFields.length, 'campos');
    }
    
    Object.keys(initialData).forEach(key => {
      // Ignorar campo interno de metadados
      if (key === '_answered_fields') return;
      
      if (key in state.formData) {
        // Ignorar cores default - tratá-las como null
        if ((key === 'primaryColor' || key === 'secondaryColor') && 
            defaultColors.includes(initialData[key]?.toUpperCase?.())) {
          console.log(`[InitSession] Ignorando cor default: ${key} = ${initialData[key]}`);
          state.formData[key] = null;
        } else {
          state.formData[key] = initialData[key];
          
          // IMPORTANTE: Marcar campos com valor como "já respondidos"
          // (apenas se não foram já carregados do _answered_fields)
          // Isso evita que a IA pergunte novamente sobre dados que já existem
          const val = initialData[key];
          
          // Verifica se o campo tem um valor significativo
          // CORREÇÃO: Booleanos (true ou false) SÃO valores válidos - ambos contam como "respondido"
          // A diferença é entre "false" (resposta) e "null/undefined" (não perguntado)
          const isBoolean = typeof val === 'boolean';
          const hasValue = isBoolean || (
            val !== null && 
            val !== '' && 
            val !== undefined && 
            !(Array.isArray(val) && val.length === 0)
          );
          
          if (hasValue && !state.answeredFields.includes(key)) {
            state.answeredFields.push(key);
          }
        }
      }
    });
    console.log('[InitSession] answeredFields total:', state.answeredFields.length, 'campos');
  }
  
  // DEPOIS: Se não tiver dados significativos do backend, tentar localStorage
  const hasBackendData = initialData && Object.keys(initialData).some(k => {
    const val = initialData[k];
    // Ignorar cores default na verificação
    if ((k === 'primaryColor' || k === 'secondaryColor') && defaultColors.includes(val?.toUpperCase?.())) {
      return false;
    }
    return val !== null && val !== '' && !(Array.isArray(val) && val.length === 0);
  });
  
  if (!hasBackendData) {
    const savedDraft = loadDraft(state.sessionId, true);
    if (savedDraft !== null) {
      console.log('[InitSession] Sem dados do backend, usando rascunho local');
    }
  } else {
    console.log('[InitSession] Dados do backend carregados, ignorando localStorage');
    
    // Se o backend forneceu um step, usar ele (tem precedência sobre localStorage)
    if (typeof initialStepFromBackend === 'number' && initialStepFromBackend >= 0) {
      state.currentStepIndex = initialStepFromBackend;
      console.log('[InitSession] Aplicando step do backend:', initialStepFromBackend);
    }
  }
  
  // Inicializar histórico com estado atual
  state.formHistory = [JSON.parse(JSON.stringify(state.formData))];
  state.historyIndex = 0;
  
  // Tentar carregar chat salvo
  const savedChat = loadSavedChat(state.sessionId);
  
  if (savedChat && savedChat.messages && savedChat.messages.length > 0) {
    // Restaurar mensagens salvas
    state.messages = savedChat.messages;
    console.log('[InitSession] Chat restaurado com', savedChat.messages.length, 'mensagens');
    
    // 🔄 MELHORIA: Re-gerar a primeira mensagem do assistente com IA
    // Isso corrige mensagens antigas que podem ter informações desatualizadas
    // (ex: listando "WhatsApp" como pendente quando já foi preenchido)
    if (state.messages.length > 0 && state.messages[0].type === 'assistant') {
      console.log('[InitSession] Re-gerando primeira mensagem com IA...');
      // Remove a primeira mensagem antiga
      state.messages.shift();
      // Solicita nova mensagem da IA (será inserida no início)
      requestInitialMessage();
    }
  } else {
    // Primeira vez OU chat foi limpo - solicitar mensagem inicial da IA
    // Reset messages para garantir array limpo
    state.messages = [];
    
    // 🚀 Chamar IA para gerar mensagem inicial inteligente
    requestInitialMessage();
  }
  
  // O current_step já foi restaurado pelo loadDraft()
  // NÃO calculamos automaticamente - respeitamos o step salvo
  // O usuário decide quando avançar
  
  // Calcular XP inicial baseado nos dados existentes
  recalculateXP();
  
  // Marcar como inicializado
  state._isInitialized = true;
  state._initializingSessionId = null;
  console.log('[InitSession] Sessão inicializada com sucesso:', state.sessionId, 'step:', state.currentStepIndex);
}

/**
 * Salva o chat no localStorage
 */
function saveChat() {
  if (!state.sessionId) return;
  
  const chatData = {
    sessionId: state.sessionId,
    messages: state.messages,
    lastUpdated: Date.now()
  };
  
  try {
    localStorage.setItem(`chat_${state.sessionId}`, JSON.stringify(chatData));
  } catch (e) {
    console.warn('[ConversationalAI] Erro ao salvar chat:', e);
  }
}

/**
 * Carrega chat salvo do localStorage
 */
function loadSavedChat(sessionId) {
  try {
    const saved = localStorage.getItem(`chat_${sessionId}`);
    if (saved) {
      return JSON.parse(saved);
    }
  } catch (e) {
    console.warn('[ConversationalAI] Erro ao carregar chat:', e);
  }
  return null;
}

/**
 * Verifica se existe uma sessão anterior não finalizada
 * Retorna informações da sessão se encontrada
 */
function checkPreviousSession() {
  try {
    // Procurar todas as chaves de chat no localStorage
    const keys = Object.keys(localStorage).filter(key => key.startsWith('chat_'));
    
    if (keys.length === 0) return null;
    
    // Pegar a sessão mais recente
    let latestSession = null;
    let latestTime = 0;
    
    for (const key of keys) {
      const data = JSON.parse(localStorage.getItem(key));
      if (data.lastUpdated > latestTime) {
        latestTime = data.lastUpdated;
        latestSession = data;
      }
    }
    
    // Verificar se a sessão tem menos de 7 dias
    const sevenDaysAgo = Date.now() - (7 * 24 * 60 * 60 * 1000);
    if (latestSession && latestSession.lastUpdated > sevenDaysAgo) {
      return {
        sessionId: latestSession.sessionId,
        messageCount: latestSession.messages?.length || 0,
        lastUpdated: latestSession.lastUpdated,
        data: latestSession
      };
    }
  } catch (e) {
    console.warn('[ConversationalAI] Erro ao verificar sessão anterior:', e);
  }
  return null;
}

/**
 * Limpa uma sessão específica
 */
function clearSession(sessionId) {
  try {
    localStorage.removeItem(`chat_${sessionId}`);
    localStorage.removeItem(`draft_${sessionId}`);
    
    // Resetar flag de inicialização se for a sessão atual
    if (state.sessionId === sessionId) {
      state._isInitialized = false;
    }
  } catch (e) {
    console.warn('[ConversationalAI] Erro ao limpar sessão:', e);
  }
}

/**
 * Restaura uma sessão anterior
 */
function restoreSession(sessionData) {
  if (!sessionData || !sessionData.data) return false;
  
  try {
    state.sessionId = sessionData.sessionId;
    state.messages = sessionData.data.messages || [];
    
    // Detectar modo demo
    state.isDemoMode = !sessionData.sessionId || 
      sessionData.sessionId.startsWith('demo-') || 
      sessionData.sessionId.startsWith('session_');
    
    // Tentar carregar draft
    loadDraft();
    
    // 🔄 MELHORIA: Re-gerar a primeira mensagem do assistente com IA
    // Isso corrige mensagens antigas que podem ter informações desatualizadas
    if (state.messages.length > 0 && state.messages[0].type === 'assistant') {
      console.log('[RestoreSession] Re-gerando primeira mensagem com IA...');
      // Remove a primeira mensagem antiga
      state.messages.shift();
      // Solicita nova mensagem da IA (será inserida no início)
      requestInitialMessage();
    }
    
    // Marcar como inicializado
    state._isInitialized = true;
    console.log('[RestoreSession] Sessão restaurada:', state.sessionId);
    
    return true;
  } catch (e) {
    console.warn('[ConversationalAI] Erro ao restaurar sessão:', e);
    return false;
  }
}

/**
 * Verifica se já tem dados preenchidos
 */
function checkHasExistingData() {
  const { formData } = state;
  return !!(formData.companyName || formData.businessType || formData.whatsapp);
}

/**
 * Retorna informações que ainda faltam preencher
 */
// eslint-disable-next-line no-unused-vars
function getMissingInfo() {
  const { formData } = state;
  const missing = [];
  
  // Step Identity
  if (!formData.companyName) missing.push('nome da empresa');
  if (!formData.businessType) missing.push('ramo de atuação');
  if (!formData.primaryColor) missing.push('cores do site');
  
  // Step Contact  
  if (!formData.whatsapp) missing.push('WhatsApp');
  
  // Step Services
  if (!formData.services || formData.services.length === 0) missing.push('serviços oferecidos');
  
  return missing.length > 0 ? missing : null;
}

/**
 * Retorna o primeiro campo pendente (não preenchido) do step atual
 * Isso permite uma mensagem de boas-vindas mais precisa
 */
function getNextPendingFieldForCurrentStep() {
  const { formData } = state;
  const stepData = currentStep.value;
  
  if (!stepData) return null;
  
  // Mapeia campos para nomes amigáveis
  const fieldLabels = {
    // Identity
    companyName: 'nome da empresa',
    businessType: 'ramo de atuação',
    frase: 'frase de efeito',
    primaryColor: 'cor principal',
    secondaryColor: 'cor secundária',
    voiceTone: 'tom de voz',
    logo: 'logo',
    // Contact
    whatsapp: 'WhatsApp',
    email: 'e-mail',
    additionalPhones: 'telefones adicionais',
    socialNetworks: 'redes sociais',
    hasPhysicalLocation: 'se tem endereço físico',
    addressCep: 'CEP',
    addressStreet: 'rua',
    addressNumber: 'número',
    addressNeighborhood: 'bairro',
    addressCity: 'cidade',
    addressState: 'estado',
    businessHours: 'horário de funcionamento',
    // Leads
    leadEmail: 'e-mail para receber leads',
    leadEmailCC: 'e-mail com cópia',
    formFields: 'campos do formulário',
    whatsappFloatingEnabled: 'botão flutuante do WhatsApp',
    // About
    companyBio: 'história da empresa',
    foundingYear: 'ano de fundação',
    founders: 'fundadores',
    // Services
    services: 'serviços oferecidos',
    servicesIntro: 'descrição dos serviços',
    // Portfolio
    projects: 'projetos realizados',
    portfolioIntro: 'descrição do portfólio',
    // FAQ
    faqItems: 'perguntas frequentes'
  };
  
  // Verifica primeiro os campos obrigatórios, depois os opcionais
  const allFields = [...(stepData.requiredFields || []), ...(stepData.optionalFields || [])];
  
  for (const field of allFields) {
    const value = formData[field];
    const isEmpty = value === undefined || value === null || value === '' || 
                   (Array.isArray(value) && value.length === 0);
    
    if (isEmpty && fieldLabels[field]) {
      return fieldLabels[field];
    }
  }
  
  return null;
}

/**
 * Retorna a mensagem de abertura do assistente (LEGACY)
 * @deprecated Substituída por requestInitialMessage() que usa IA
 * Mantida como referência para getFallbackOpeningMessage()
 * @param {boolean} hasExistingData - Se já tem dados preenchidos
 * @param {number} currentStepIndex - Índice do step atual (0-based)
 * @param {object} currentStepData - Dados do step atual
 */
// eslint-disable-next-line no-unused-vars
function getOpeningMessage(hasExistingData = false, currentStepIndex = 0, currentStepData = null) {
  // Se tem dados E está em um step > 0, significa que estava no meio do processo
  if (hasExistingData && currentStepIndex > 0 && currentStepData && currentStepData.name) {
    const { formData } = state;
    const parts = [];
    
    if (formData.companyName) parts.push(`**${formData.companyName}**`);
    if (formData.businessType) parts.push(`ramo de **${formData.businessType}**`);
    
    // Verifica o próximo campo pendente do step atual
    const nextPending = getNextPendingFieldForCurrentStep();
    
    if (nextPending) {
      return `Oi! 👋 Que bom ter você de volta!

Vi que já temos algumas informações: ${parts.join(', ')}.

Enquanto a gente conversa, o formulário ao lado vai sendo atualizado automaticamente. ✨

**Vamos continuar?** O próximo passo é definir: **${nextPending}**.`;
    }
    
    // Step atual está completo
    return `Oi! 👋 Que bom ter você de volta!

Vi que já temos algumas informações: ${parts.join(', ')}.

Você estava em: **${currentStepData.name}** ${currentStepData.icon}

**Essa parte parece completa!** Podemos avançar para o próximo passo?`;
  }
  
  // Se tem dados mas está no step 0, pergunta se quer continuar de onde parou
  if (hasExistingData) {
    const { formData } = state;
    const parts = [];
    
    if (formData.companyName) parts.push(`**${formData.companyName}**`);
    if (formData.businessType) parts.push(`ramo de **${formData.businessType}**`);
    
    // Usa o próximo campo pendente do step atual em vez de lista genérica
    const nextPending = getNextPendingFieldForCurrentStep();
    const pendingText = nextPending || 'revisar os detalhes';
    
    return `Oi! 👋 Que bom ter você de volta!

Vi que já temos algumas informações: ${parts.join(', ')}.

Enquanto a gente conversa, o formulário ao lado vai sendo atualizado automaticamente. ✨

**Vamos continuar?** O próximo passo é definir: **${pendingText}**.`;
  }
  
  return `Oi! 👋 Eu sou o **Unli**, seu assistente de criação de sites.

Vou te ajudar a montar um site incrível em poucos minutos. É só conversar comigo naturalmente!

Enquanto a gente conversa, o formulário ao lado vai sendo preenchido automaticamente. ✨

**Vamos começar?** Me conta o **nome da sua empresa**, o que vocês fazem e, se tiver, uma frase que resuma o seu negócio.`;
}

/**
 * Mensagem de continuação após carregar chat salvo
 * (Mantida para uso futuro - não adiciona mais a cada F5)
 */
// eslint-disable-next-line no-unused-vars
function getContinueMessage(missingInfo) {
  const missingText = missingInfo.slice(0, 3).join(', ');
  
  return `Continuando de onde paramos! 🚀

Ainda precisamos definir: **${missingText}**.

Me conta mais sobre isso!`;
}

/**
 * Adiciona uma mensagem ao histórico
 */
function addMessage(message) {
  const msg = {
    id: `msg_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
    timestamp: Date.now(),
    ...message
  };
  state.messages.push(msg);
  
  // Salvar chat após cada mensagem
  saveChat();
  
  return msg;
}

/**
 * Envia mensagem do usuário e processa com IA
 */
async function sendUserMessage(content, isAudio = false) {
  if (!content.trim()) return;
  
  // Adiciona mensagem do usuário
  addMessage({
    type: isAudio ? 'user_audio' : 'user_text',
    content: content.trim()
  });
  
  // Ativa estados de loading
  state.isTyping = true;
  state.isProcessing = true;
  
  try {
    // Chama API de extração
    const response = await processWithAI(content);
    
    // DEBUG: Log da resposta completa
    console.log('📥 [sendUserMessage] Resposta da API:', {
      success: response.success,
      hasNewStep: !!response.new_step,
      newStep: response.new_step,
      stepComplete: response.step_complete,
      extractedFields: Object.keys(response.extracted_fields || {})
    });
    
    if (response.success) {
      // Atualiza campos extraídos
      if (response.extracted_fields) {
        await updateFieldsWithAnimation(response.extracted_fields);
      }
      
      // Verifica conquistas
      if (response.achievements) {
        response.achievements.forEach(ach => unlockAchievement(ach));
      }
      
      // DEBUG: Log das sugestões antes de adicionar mensagem
      if (response.suggestions && response.suggestions.length > 0) {
        console.log('🔍 [Service] Sugestões da API:', response.suggestions);
      }
      
      // =============================================
      // TRANSIÇÃO DE STEP (controlada pelo PHP)
      // Se a API retornou new_step, significa que o step anterior
      // foi completado e devemos avançar automaticamente
      // =============================================
      if (response.new_step) {
        // Usar activeSteps dinâmico em vez de array fixa
        const stepsOrder = activeSteps.value.map(s => s.id);
        const newStepIndex = stepsOrder.indexOf(response.new_step);
        
        console.log(`🔄 [Step Transition] Steps disponíveis: ${stepsOrder.join(', ')}`);
        console.log(`🔄 [Step Transition] Novo step requisitado: ${response.new_step} (índice: ${newStepIndex})`);
        console.log(`🔄 [Step Transition] Step atual: ${stepsOrder[state.currentStepIndex]} (índice: ${state.currentStepIndex})`);
        
        if (newStepIndex !== -1 && newStepIndex !== state.currentStepIndex) {
          console.log(`✅ [Step Transition] Avançando de ${stepsOrder[state.currentStepIndex]} para ${response.new_step}`);
          state.currentStepIndex = newStepIndex;
          // Salva imediatamente
          saveDraft();
        } else if (newStepIndex === -1) {
          console.warn(`⚠️ [Step Transition] Step '${response.new_step}' não encontrado nos steps ativos!`);
        }
      }
      
      // Adiciona resposta do assistente
      addMessage({
        type: 'assistant',
        content: response.assistant_message,
        suggestions: response.suggestions || null,
        actions: response.actions || null
      });
      
      // Verifica se step foi completado (para conquistas)
      checkStepCompletion();
      
      // =============================================
      // AÇÃO DE FINALIZAÇÃO TOTAL
      // Se a API retornou action finish_onboarding, tratar
      // =============================================
      if (response.actions && response.actions.some(a => a.type === 'finish_onboarding')) {
        console.log('🚀 [Finish] Pronto para finalizar onboarding');
        // O componente Vue vai tratar essa action
      }
    } else {
      addMessage({
        type: 'assistant',
        content: response.error || 'Ops, tive um probleminha. Pode repetir?'
      });
    }
  } catch (error) {
    console.error('[ConversationalAI] Erro:', error);
    
    // Incrementa contador de falhas
    const exceededLimit = incrementAIFailure();
    
    if (exceededLimit) {
      // Excedeu limite - oferece alternativas
      addMessage({
        type: 'assistant',
        content: `Desculpe, estou com dificuldades técnicas no momento. 😓

Você tem duas opções:
1. **Tentar mais tarde** - o problema pode ser temporário
2. **Usar o formulário tradicional** - você pode preencher manualmente sem depender de mim

Seus dados estão salvos e não serão perdidos! 💾`,
        actions: [
          {
            id: 'retry_later',
            label: '🔄 Tentar de novo',
            type: 'retry'
          },
          {
            id: 'go_to_form',
            label: '📝 Ir para o formulário',
            type: 'go_to_form'
          }
        ]
      });
    } else {
      // Ainda tem tentativas
      addMessage({
        type: 'assistant',
        content: `Ops, tive um probleminha. Pode tentar de novo? 🙈`
      });
    }
  } finally {
    state.isTyping = false;
    state.isProcessing = false;
  }
}

/**
 * 🚀 Solicita mensagem inicial inteligente da IA
 * Envia ação 'start' para o backend, que usa o Prompt Blindado
 * para gerar uma saudação contextualizada baseada nos dados já preenchidos
 */
async function requestInitialMessage() {
  console.log('[requestInitialMessage] Solicitando mensagem inicial inteligente da IA...');
  
  // Ativa estado de typing enquanto espera a IA
  state.isTyping = true;
  
  try {
    // Aguardar nextTick para garantir que o Vue sincronizou
    await nextTick();
    
    // Criar cópia profunda do formData
    const formDataSnapshot = JSON.parse(JSON.stringify(state.formData));
    
    console.log('[requestInitialMessage] Dados atuais:', {
      step: currentStepId.value,
      companyName: formDataSnapshot.companyName,
      whatsapp: formDataSnapshot.whatsapp,
      email: formDataSnapshot.email,
      hasData: Object.keys(formDataSnapshot).filter(k => formDataSnapshot[k]).length
    });
    
    const payload = {
      session_id: state.sessionId,
      step: currentStepId.value,
      messages: [], // Sem histórico - é a primeira mensagem
      user_message: '', // Vazio - backend vai usar a action
      current_form_data: formDataSnapshot,
      voice_tone: formDataSnapshot.voiceTone || 'profissional',
      purchased_pages: state.purchasedPages || [],
      action: 'start' // 🔑 Gatilho para mensagem inicial
    };
    
    const response = await fetch('/api/ai/conversational-onboarding.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }
    
    const result = await response.json();
    
    // Backend retorna assistant_message, não reply
    const aiMessage = result.reply || result.assistant_message;
    
    if (result.success && aiMessage) {
      // Adicionar mensagem da IA
      addMessage({
        type: 'assistant',
        content: aiMessage
      });
      console.log('[requestInitialMessage] ✅ Mensagem inicial recebida da IA');
    } else {
      // Fallback caso a IA falhe - usar mensagem genérica
      console.warn('[requestInitialMessage] ⚠️ Falha na IA, usando fallback', result);
      addMessage({
        type: 'assistant',
        content: getFallbackOpeningMessage()
      });
    }
  } catch (error) {
    console.error('[requestInitialMessage] ❌ Erro:', error);
    // Fallback em caso de erro de rede
    addMessage({
      type: 'assistant',
      content: getFallbackOpeningMessage()
    });
  } finally {
    state.isTyping = false;
  }
}

/**
 * Mensagem de fallback caso a IA não responda
 * Usada apenas em caso de erro de rede ou falha da API
 */
function getFallbackOpeningMessage() {
  const hasData = checkHasExistingData();
  
  if (hasData && state.formData.companyName) {
    return `Oi! 👋 Que bom ter você de volta!

Vi que estamos trabalhando no site da **${state.formData.companyName}**.

Vamos continuar de onde paramos?`;
  }
  
  return `Oi! 👋 Eu sou o **Assistente Unli**, especialista em criação de sites.

Vou te ajudar a montar um site incrível! Enquanto conversamos, o formulário ao lado vai sendo preenchido automaticamente. ✨

**Vamos começar?** Me conta sobre o seu negócio!`;
}

/**
 * Processa mensagem com a API do Gemini
 */
async function processWithAI(userMessage) {
  // CRÍTICO: Aguardar nextTick para garantir que o Vue sincronizou todas as mudanças de estado
  // Isso evita race conditions onde o formData pode estar desatualizado
  await nextTick();
  
  // Criar cópia profunda do formData para evitar problemas de referência
  const formDataSnapshot = JSON.parse(JSON.stringify(state.formData));
  
  // DEBUG: Log para verificar se os dados estão corretos
  console.log('📤 [processWithAI] Enviando formData para API:', {
    step: currentStepId.value,
    companyName: formDataSnapshot.companyName,
    businessType: formDataSnapshot.businessType,
    frase: formDataSnapshot.frase,
    primaryColor: formDataSnapshot.primaryColor,
    secondaryColor: formDataSnapshot.secondaryColor,
    hasLogo: !!formDataSnapshot.logo,
    hasNoLogo: formDataSnapshot.hasNoLogo
  });
  
  const payload = {
    session_id: state.sessionId,
    step: currentStepId.value,
    messages: state.messages.slice(-10), // Últimas 10 mensagens para contexto
    user_message: userMessage,
    current_form_data: formDataSnapshot,
    answered_fields: [...state.answeredFields], // Campos que o usuário já respondeu (mesmo se vazio/false)
    voice_tone: formDataSnapshot.voiceTone || 'profissional',
    purchased_pages: state.purchasedPages || [] // Páginas compradas para steps dinâmicos
  };
  
  const response = await fetch('/api/ai/conversational-onboarding.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  
  if (!response.ok) {
    throw new Error(`HTTP ${response.status}`);
  }
  
  return response.json();
}

/**
 * Normaliza o campo socialNetworks para o formato esperado (array de objetos)
 * O Gemini às vezes retorna em formatos diferentes (string, array de strings, etc)
 */
function normalizeSocialNetworks(value) {
  // Se já está no formato correto (array de objetos com type e url)
  if (Array.isArray(value) && value.length > 0 && typeof value[0] === 'object' && value[0].type) {
    return value;
  }
  
  // Se é null/undefined/vazio, retorna array vazio
  if (!value || (Array.isArray(value) && value.length === 0)) {
    return [];
  }
  
  const result = [];
  
  // Função auxiliar para detectar tipo de rede pelo conteúdo
  const detectNetworkType = (str) => {
    const lower = str.toLowerCase();
    if (lower.includes('instagram') || lower.includes('instagram.com')) return 'instagram';
    if (lower.includes('linkedin') || lower.includes('linkedin.com')) return 'linkedin';
    if (lower.includes('twitter') || lower.includes('twitter.com') || lower.includes('x.com')) return 'twitter';
    if (lower.includes('facebook') || lower.includes('fb.com')) return 'facebook';
    if (lower.includes('youtube') || lower.includes('youtube.com')) return 'youtube';
    if (lower.includes('tiktok') || lower.includes('tiktok.com')) return 'tiktok';
    // Se começa com @, assume Instagram (padrão no Brasil)
    if (str.startsWith('@')) return 'instagram';
    return null;
  };
  
  // Se é uma string simples
  if (typeof value === 'string') {
    const type = detectNetworkType(value);
    if (type) {
      result.push({ type, url: value });
    }
  }
  
  // Se é um array de strings
  if (Array.isArray(value) && value.length > 0 && typeof value[0] === 'string') {
    value.forEach(str => {
      const type = detectNetworkType(str);
      if (type) {
        result.push({ type, url: str });
      }
    });
  }
  
  // Se é um array de objetos mas sem o campo 'type' (ex: {url: "@user"})
  if (Array.isArray(value) && value.length > 0 && typeof value[0] === 'object' && !value[0].type) {
    value.forEach(obj => {
      const url = obj.url || obj.link || obj.username || '';
      const type = obj.network || obj.platform || detectNetworkType(url);
      if (type && url) {
        result.push({ type, url });
      }
    });
  }
  
  console.log('[normalizeSocialNetworks] Input:', value, '→ Output:', result);
  return result;
}

/**
 * Busca endereço pelo CEP via ViaCEP API
 */
async function fetchAddressByCep(cep) {
  const cleanCep = cep.replace(/\D/g, '');
  
  if (cleanCep.length !== 8) {
    console.log('[ViaCEP] CEP inválido:', cep);
    return null;
  }
  
  try {
    console.log('[ViaCEP] Buscando CEP:', cleanCep);
    const response = await fetch(`https://viacep.com.br/ws/${cleanCep}/json/`);
    const data = await response.json();
    
    if (data.erro) {
      console.log('[ViaCEP] CEP não encontrado:', cleanCep);
      return null;
    }
    
    console.log('[ViaCEP] Endereço encontrado:', data);
    return {
      addressStreet: data.logradouro || '',
      addressNeighborhood: data.bairro || '',
      addressCity: data.localidade || '',
      addressState: data.uf || ''
    };
  } catch (error) {
    console.error('[ViaCEP] Erro ao buscar:', error);
    return null;
  }
}

/**
 * Atualiza campos com animação de shimmer e check
 */
async function updateFieldsWithAnimation(fields) {
  const fieldKeys = Object.keys(fields);
  
  // Normaliza socialNetworks se existir
  if (fields.socialNetworks !== undefined) {
    fields.socialNetworks = normalizeSocialNetworks(fields.socialNetworks);
  }
  
  // Sanitiza campos de email - extrai apenas o primeiro email válido
  // Evita duplicações como "email@a.comemail@a.com"
  const emailFields = ['email', 'leadEmail', 'leadEmailCC'];
  emailFields.forEach(field => {
    if (fields[field] && typeof fields[field] === 'string') {
      const emailMatch = fields[field].match(/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/);
      if (emailMatch) {
        console.log(`🧹 [Sanitize] Email sanitizado: "${fields[field]}" -> "${emailMatch[1]}"`);
        fields[field] = emailMatch[1];
      }
    }
  });
  
  // Se recebeu CEP, buscar dados do endereço via ViaCEP
  if (fields.addressCep || fields.cep) {
    const cep = fields.addressCep || fields.cep;
    const addressData = await fetchAddressByCep(cep);
    if (addressData) {
      // Mesclar dados do ViaCEP com os campos
      Object.assign(fields, addressData);
      // Garantir que o CEP está no campo correto
      fields.addressCep = cep;
      delete fields.cep;
      // Adicionar os novos campos à lista de shimmer
      fieldKeys.push(...Object.keys(addressData).filter(k => !fieldKeys.includes(k)));
    }
  }
  
  // Ativa shimmer em todos os campos
  state.shimmeringFields = [...fieldKeys];
  
  // Aguarda efeito visual
  await sleep(300);
  
  // Atualiza cada campo com delay para efeito cascata
  for (const [key, value] of Object.entries(fields)) {
    if (key in state.formData) {
      state.formData[key] = value;
      
      // IMPORTANTE: Marca o campo como "respondido" para evitar re-perguntar
      // Isso é crucial para campos que podem ter valor vazio/false (ex: hasPhysicalLocation, socialNetworks)
      if (!state.answeredFields.includes(key)) {
        state.answeredFields.push(key);
      }
      
      // Remove do shimmer, adiciona ao recently filled
      state.shimmeringFields = state.shimmeringFields.filter(f => f !== key);
      state.recentlyFilledFields.push(key);
      
      // Adiciona XP por campo preenchido
      addXP(2);
      
      await sleep(150);
    }
  }
  
  // Limpa recently filled após 2 segundos
  setTimeout(() => {
    state.recentlyFilledFields = [];
  }, 2000);
  
  // Valida formatos e adiciona avisos se necessário
  validateFormats(fields);
}

/**
 * Valida formatos de campos específicos e adiciona aviso gentil
 * NÃO bloqueia, apenas avisa
 * Evita mostrar o mesmo aviso mais de uma vez por sessão
 */
function validateFormats(fields) {
  const warnings = [];
  
  // Padrões de validação
  const patterns = {
    // Telefone brasileiro (aceita vários formatos)
    phone: /^[\s()]*-(\d{2})[\s)-]*(\d{4,5})[\s-]*(\d{4})[\s]*$/,
    // Email simples
    email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    // CPF (aceita com ou sem pontos/traços)
    cpf: /^\d{3}\.?\d{3}\.?\d{3}[-.]?\d{2}$/,
    // CNPJ
    cnpj: /^\d{2}\.?\d{3}\.?\d{3}\/?\d{4}[-.]?\d{2}$/,
    // CEP
    cep: /^\d{5}[-.]?\d{3}$/
  };
  
  // Formatos esperados para mensagem amigável
  const expectedFormats = {
    whatsapp: '(11) 99999-9999',
    phone: '(11) 99999-9999 ou (11) 3333-3333',
    email: 'exemplo@email.com',
    cpf: '123.456.789-00',
    cnpj: '12.345.678/0001-00',
    cep: '01234-567'
  };
  
  // Mapeamento de campos para tipo de validação
  const fieldValidations = {
    whatsapp: 'phone',
    leadEmail: 'email',
    addressCep: 'cep'
  };
  
  Object.entries(fields).forEach(([fieldId, value]) => {
    if (!value || typeof value !== 'string') return;
    
    const validationType = fieldValidations[fieldId];
    if (!validationType) return;
    
    const pattern = patterns[validationType];
    const cleanValue = value.trim();
    
    // Criar chave única para este aviso (campo + valor)
    const warningKey = `${fieldId}:${cleanValue}`;
    
    // Se não passar na validação E ainda não mostramos esse aviso
    if (cleanValue && !pattern.test(cleanValue) && !state.shownFormatWarnings.has(warningKey)) {
      warnings.push({
        field: fieldId,
        value: cleanValue,
        expectedFormat: expectedFormats[validationType] || expectedFormats[fieldId],
        warningKey: warningKey
      });
    }
  });
  
  // Se tiver avisos novos (não duplicados), adiciona mensagem informativa
  if (warnings.length > 0) {
    // Marcar estes avisos como já mostrados
    warnings.forEach(w => state.shownFormatWarnings.add(w.warningKey));
    
    const warningMessages = warnings.map(w => 
      `• **${getFieldLabel(w.field)}**: formato esperado: \`${w.expectedFormat}\``
    ).join('\n');
    
    addMessage({
      type: 'assistant',
      content: `💡 **Dica de formatação:**\n\n${warningMessages}\n\nMas não se preocupe, salvei como você digitou! Você pode corrigir depois se quiser.`,
      isWarning: true
    });
  }
}

/**
 * Retorna label amigável para o campo
 */
function getFieldLabel(fieldId) {
  const labels = {
    whatsapp: 'WhatsApp',
    leadEmail: 'Email',
    addressCep: 'CEP',
    cpf: 'CPF',
    cnpj: 'CNPJ'
  };
  return labels[fieldId] || fieldId;
}

/**
 * Atualiza um campo manualmente (edição no formulário)
 */
function updateField(fieldId, value) {
  console.log('[updateField] Attempting to update:', fieldId, '=', value);
  console.log('[updateField] Field exists in formData:', fieldId in state.formData);
  
  // Permitir adicionar campos que existem no schema original OU novos campos
  // Salvar estado anterior no histórico antes de alterar
  pushToHistory();
  
  state.formData[fieldId] = value;
  console.log('[updateField] Updated formData:', fieldId, '→', state.formData[fieldId]);
  
  // IMPORTANTE: Marca o campo como "respondido" para evitar re-perguntar
  // Isso é crucial para campos que podem ter valor vazio/false (ex: hasPhysicalLocation, socialNetworks)
  if (!state.answeredFields.includes(fieldId)) {
    state.answeredFields.push(fieldId);
    console.log('[updateField] Added to answeredFields:', fieldId);
  }
  
  // Limpa erro de validação se existir
  if (state.validationErrors[fieldId]) {
    delete state.validationErrors[fieldId];
  }
}

/**
 * Salva estado atual no histórico (para undo)
 */
function pushToHistory() {
  // Remove itens após o índice atual (se fizemos undo e agora estamos editando)
  if (state.historyIndex < state.formHistory.length - 1) {
    state.formHistory = state.formHistory.slice(0, state.historyIndex + 1);
  }
  
  // Adiciona estado atual
  state.formHistory.push(JSON.parse(JSON.stringify(state.formData)));
  state.historyIndex = state.formHistory.length - 1;
  
  // Limita tamanho do histórico
  if (state.formHistory.length > state.maxHistorySize) {
    state.formHistory.shift();
    state.historyIndex--;
  }
}

/**
 * Desfaz última alteração
 */
function undo() {
  if (state.historyIndex > 0) {
    state.historyIndex--;
    const previousState = state.formHistory[state.historyIndex];
    Object.assign(state.formData, JSON.parse(JSON.stringify(previousState)));
    console.log('[Undo] Voltou para estado', state.historyIndex);
  }
}

/**
 * Refaz alteração desfeita
 */
function redo() {
  if (state.historyIndex < state.formHistory.length - 1) {
    state.historyIndex++;
    const nextState = state.formHistory[state.historyIndex];
    Object.assign(state.formData, JSON.parse(JSON.stringify(nextState)));
    console.log('[Redo] Avançou para estado', state.historyIndex);
  }
}

/**
 * Adiciona XP
 */
function addXP(amount) {
  const previousLevel = currentLevel.value.level;
  state.xp += amount;
  
  // Verifica level up
  if (currentLevel.value.level > previousLevel) {
    // Trigger de animação de level up
    console.log(`🎉 Level Up! Agora você é ${currentLevel.value.name}`);
  }
}

/**
 * Desbloqueia uma conquista
 */
function unlockAchievement(achievementId) {
  if (state.unlockedAchievements.includes(achievementId)) return;
  
  const achievement = ACHIEVEMENTS[achievementId];
  if (!achievement) return;
  
  state.unlockedAchievements.push(achievementId);
  state.pendingAchievement = achievement;
  addXP(achievement.xp);
  
  // Limpa popup após 4 segundos
  setTimeout(() => {
    if (state.pendingAchievement?.id === achievementId) {
      state.pendingAchievement = null;
    }
  }, 4000);
}

/**
 * Fecha popup de conquista manualmente
 */
function dismissAchievementPopup() {
  state.pendingAchievement = null;
}

/**
 * Verifica se o step atual tem campos obrigatórios preenchidos
 * NÃO avança automaticamente - apenas desbloqueia conquistas se aplicável
 * O avanço é controlado pelo usuário via nextStep()
 */
function checkStepCompletion() {
  const step = currentStep.value;
  if (!step) return;
  
  // Verifica se todos os campos obrigatórios estão preenchidos
  const requiredFields = step.requiredFields || [];
  const allRequiredFilled = requiredFields.length === 0 || requiredFields.every(field => {
    const value = state.formData[field];
    if (value === null || value === undefined || value === '') return false;
    if (Array.isArray(value)) return value.length > 0;
    return true;
  });
  
  // Apenas desbloqueia conquista se houver
  // O avanço de step é decidido pelo USUÁRIO, não automático
  if (allRequiredFilled && step.achievement) {
    unlockAchievement(step.achievement.id);
  }
}

/**
 * Avança para o próximo step
 * Chamado quando o USUÁRIO confirma que terminou o step atual
 */
function nextStep() {
  if (state.currentStepIndex < activeSteps.value.length - 1) {
    state.currentStepIndex++;
    
    // Salva o step atual imediatamente
    saveDraft();
    
    // Mensagem de transição do assistente
    const step = currentStep.value;
    addMessage({
      type: 'assistant',
      content: getStepTransitionMessage(step)
    });
  } else {
    // Último step - entra no modo de revisão
    enterReviewMode();
  }
}

/**
 * Volta para o step anterior
 */
function previousStep() {
  if (state.currentStepIndex > 0) {
    state.currentStepIndex--;
    saveDraft();
  }
}

/**
 * Vai para um step específico
 */
function goToStep(stepIndex) {
  if (stepIndex >= 0 && stepIndex < activeSteps.value.length) {
    state.currentStepIndex = stepIndex;
    saveDraft();
  }
}

/**
 * Retorna mensagem de transição entre steps
 */
function getStepTransitionMessage(step) {
  const transitions = {
    contact: `Perfeito! Agora vamos configurar como os clientes vão te encontrar. 📞\n\nMe passa seu **WhatsApp principal** e suas redes sociais (Instagram, Facebook, etc).`,
    about: `Show! Agora vem a parte mais legal: a **história da sua empresa**! 🏢\n\nMe conta livremente como tudo começou, o que te motivou, sua trajetória... Eu transformo em um texto profissional!`,
    services: `Agora vamos pros seus **serviços**! ⚙️\n\nMe lista o que vocês fazem. Pode ser simples mesmo, tipo: "instalação elétrica, manutenção, projetos residenciais..." Eu estruturo tudo certinho!`,
    faq: `Quase lá! Vamos criar as **perguntas frequentes**. ❓\n\nQuais dúvidas seus clientes mais perguntam? Ou, se preferir, me conta seu nicho que eu sugiro perguntas que quebram objeções!`,
    finalization: `Última etapa! 🚀\n\nTem mais alguma observação, preferência de design, ou sites que você gosta como referência?`
  };
  
  return transitions[step.id] || `Vamos para ${step.name}!`;
}

/**
 * Entra no modo de revisão (Mesa de Edição)
 */
function enterReviewMode() {
  state.mode = 'review';
  
  // Valida todos os campos
  validateAllFields();
  
  // Gera resumo conversacional dos dados
  const summary = generateConversationalSummary();
  addMessage({
    type: 'assistant',
    content: summary
  });
  
  // Após o resumo, mostra o veredito com potencial
  setTimeout(() => {
    const verdict = generateVerdict();
    addMessage({
      type: 'assistant',
      content: verdict
    });
  }, 500);
}

/**
 * Volta para o modo chat
 */
function exitReviewMode() {
  state.mode = 'chat';
}

/**
 * Gera o veredito final da IA
 */
function generateVerdict() {
  const pending = pendingRequiredFields.value;
  const percentage = progressPercentage.value;
  
  if (pending.length === 0 && percentage >= 80) {
    return `🎉 **Parabéns!** Analisei todas as suas respostas e seu site tem **${percentage}% de potencial de conversão**!

Tá tudo pronto pro lançamento! Dá uma olhada nos cards abaixo e ajusta o que quiser antes de publicar.`;
  } else if (pending.length > 0) {
    const pendingList = pending.map(p => `• ${p.stepName}: ${p.field}`).join('\n');
    return `📊 Analisei suas respostas e seu site tem **${percentage}% de potencial**!

Para atingir 100%, só falta:
${pendingList}

Clique nos cards abaixo para completar ou editar qualquer informação.`;
  } else {
    return `📊 Seu site tem **${percentage}% de potencial de conversão**!

Tá bom, mas pode ficar ainda melhor! Veja os cards abaixo e adicione mais informações para maximizar suas conversões.`;
  }
}

/**
 * Gera um resumo conversacional de todos os dados preenchidos
 * Sem usar IA - apenas formata os dados de forma amigável
 */
function generateConversationalSummary() {
  const data = state.formData;
  let summary = `📋 **Vamos revisar tudo que você me contou?**\n\n`;
  
  // === IDENTIDADE DA MARCA ===
  if (data.companyName || data.businessType || data.frase) {
    summary += `**🏢 Sobre sua empresa:**\n`;
    if (data.companyName) summary += `• Nome: **${data.companyName}**\n`;
    if (data.businessType) summary += `• Tipo de negócio: ${data.businessType}\n`;
    if (data.frase) summary += `• Slogan: "${data.frase}"\n`;
    if (data.voiceTone) summary += `• Tom de voz: ${data.voiceTone}\n`;
    summary += `\n`;
  }
  
  // === CONTATO ===
  if (data.whatsapp || data.leadEmail || (data.socialNetworks && data.socialNetworks.length > 0)) {
    summary += `**📞 Contato:**\n`;
    if (data.whatsapp) summary += `• WhatsApp: ${data.whatsapp}\n`;
    if (data.leadEmail) summary += `• E-mail para leads: ${data.leadEmail}\n`;
    if (data.socialNetworks && data.socialNetworks.length > 0) {
      const networks = data.socialNetworks.filter(n => n.url).map(n => n.type).join(', ');
      if (networks) summary += `• Redes sociais: ${networks}\n`;
    }
    if (data.hasPhysicalLocation && data.addressCity) {
      summary += `• Endereço: ${data.addressCity}${data.addressState ? ', ' + data.addressState : ''}\n`;
    }
    if (data.businessHours) summary += `• Horário: ${data.businessHours}\n`;
    summary += `\n`;
  }
  
  // === SOBRE A EMPRESA ===
  if (data.companyBio || data.foundingYear || (data.companyHighlights && data.companyHighlights.length > 0)) {
    summary += `**📖 História:**\n`;
    if (data.companyBio) {
      const bioPreview = data.companyBio.length > 150 
        ? data.companyBio.substring(0, 150) + '...' 
        : data.companyBio;
      summary += `• Bio: "${bioPreview}"\n`;
    }
    if (data.foundingYear) summary += `• Fundação: ${data.foundingYear}\n`;
    if (data.companyHighlights && data.companyHighlights.length > 0) {
      summary += `• Diferenciais: ${data.companyHighlights.join(', ')}\n`;
    }
    summary += `\n`;
  }
  
  // === SERVIÇOS ===
  if (data.services && data.services.length > 0) {
    summary += `**⚙️ Serviços:**\n`;
    const serviceNames = data.services.map(s => s.title || s.name).filter(Boolean).slice(0, 5);
    if (serviceNames.length > 0) {
      summary += `• ${serviceNames.join(', ')}`;
      if (data.services.length > 5) summary += ` (+${data.services.length - 5} mais)`;
      summary += `\n`;
    }
    if (data.hasGuarantee && data.guaranteeDetails) {
      summary += `• Garantia: ${data.guaranteeDetails}\n`;
    }
    summary += `\n`;
  }
  
  // === FAQ ===
  if (data.faqItems && data.faqItems.length > 0) {
    summary += `**❓ FAQ:**\n`;
    summary += `• ${data.faqItems.length} perguntas cadastradas\n`;
    summary += `\n`;
  }
  
  // === FINALIZAÇÃO ===
  if (data.additionalNotes || (data.inspirationUrls && data.inspirationUrls.length > 0)) {
    summary += `**🚀 Observações finais:**\n`;
    if (data.additionalNotes) {
      const notesPreview = data.additionalNotes.length > 100 
        ? data.additionalNotes.substring(0, 100) + '...' 
        : data.additionalNotes;
      summary += `• Notas: "${notesPreview}"\n`;
    }
    if (data.inspirationUrls && data.inspirationUrls.length > 0) {
      summary += `• Sites de referência: ${data.inspirationUrls.length}\n`;
    }
    summary += `\n`;
  }
  
  // === RODAPÉ ===
  summary += `---\n`;
  summary += `✅ **Tudo certo?** Se quiser mudar algo, é só clicar no campo ao lado ou me falar aqui no chat.\n`;
  summary += `🚀 Quando estiver pronto, clique em **"Finalizar"** para enviar!`;
  
  return summary;
}

/**
 * Valida todos os campos
 */
function validateAllFields() {
  state.validationErrors = {};
  
  // Validações específicas
  if (state.formData.whatsapp && !/^\d{10,11}$/.test(state.formData.whatsapp.replace(/\D/g, ''))) {
    state.validationErrors.whatsapp = 'WhatsApp deve ter 10 ou 11 dígitos';
  }
  
  if (state.formData.leadEmail && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(state.formData.leadEmail)) {
    state.validationErrors.leadEmail = 'E-mail inválido';
  }
  
  // Campos obrigatórios vazios
  for (const step of activeSteps.value) {
    if (!step.requiredFields) continue;
    for (const field of step.requiredFields) {
      const value = state.formData[field];
      if (!value || (Array.isArray(value) && value.length === 0)) {
        state.validationErrors[field] = 'Campo obrigatório';
      }
    }
  }
}

/**
 * Define as páginas compradas pelo cliente
 * Isso afeta quais steps são mostrados no onboarding
 * @param {string[]} pages - Array com IDs das páginas (ex: ['sobre_nos', 'servicos', 'faq'])
 */
function setPurchasedPages(pages) {
  state.purchasedPages = pages || [];
}

/**
 * Recalcula XP baseado nos dados existentes
 */
function recalculateXP() {
  let xp = 0;
  
  // XP por campos preenchidos
  // eslint-disable-next-line no-unused-vars
  Object.entries(state.formData).forEach(([key, value]) => {
    if (value && value !== '' && !(Array.isArray(value) && value.length === 0)) {
      xp += 2;
    }
  });
  
  state.xp = xp;
}

/**
 * Sugere cores baseado no ramo
 */
async function suggestColors() {
  const businessType = state.formData.businessType;
  const companyName = state.formData.companyName;
  
  if (!businessType) return null;
  
  try {
    const response = await fetch('/api/ai/suggest-colors.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ business_type: businessType, company_name: companyName })
    });
    
    return response.json();
  } catch (error) {
    console.error('[ConversationalAI] Erro ao sugerir cores:', error);
    return null;
  }
}

/**
 * Salva rascunho automaticamente
 * Em modo demo: salva no localStorage
 * Com token real: salva no backend
 */
async function saveDraft() {
  if (!state.sessionId) return;
  
  // Incluir answered_fields dentro do data para que seja persistido no backend
  const dataWithMeta = {
    ...state.formData,
    _answered_fields: [...state.answeredFields] // Prefixo _ para não conflitar com campos do formulário
  };
  
  const draftData = {
    session_id: state.sessionId,
    current_step: state.currentStepIndex,
    data: dataWithMeta,
    answered_fields: [...state.answeredFields], // Mantém no nível raiz para localStorage
    is_draft: true,
    timestamp: Date.now()
  };
  
  console.log(`[SaveDraft] Salvando step ${state.currentStepIndex} no localStorage para sessão ${state.sessionId}`);
  console.log(`[SaveDraft] answeredFields: ${state.answeredFields.length} campos`);
  
  // Modo demo: salvar no localStorage
  if (state.isDemoMode) {
    try {
      localStorage.setItem(`draft_${state.sessionId}`, JSON.stringify(draftData));
      console.log('[Autosave] Salvo localmente (modo demo)');
    } catch (e) {
      console.warn('[Autosave] Erro ao salvar localmente:', e);
    }
    return;
  }
  
  // Token real: salvar no backend
  try {
    const response = await fetch('/api/onboarding/save-draft.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(draftData)
    });
    
    if (response.ok) {
      console.log('[Autosave] Salvo no servidor');
    } else {
      console.warn('[Autosave] Servidor retornou erro, salvando localmente');
      // Fallback para localStorage
      localStorage.setItem(`draft_${state.sessionId}`, JSON.stringify(draftData));
    }
  } catch (error) {
    console.error('[Autosave] Erro ao salvar no servidor:', error);
    // Fallback para localStorage
    localStorage.setItem(`draft_${state.sessionId}`, JSON.stringify(draftData));
  }
}

/**
 * Carrega rascunho salvo do localStorage
 * @param {string} [sessionId] - ID da sessão (usa state.sessionId se não fornecido)
 * @param {boolean} [applyToState=true] - Se true, aplica os dados no state.formData
 * @returns {object|null} Dados do rascunho ou null
 */
function loadDraft(sessionId = null, applyToState = true) {
  const sid = sessionId || state.sessionId;
  if (!sid) return null;
  
  // Tentar localStorage primeiro
  try {
    const saved = localStorage.getItem(`draft_${sid}`);
    if (saved) {
      const data = JSON.parse(saved);
      if (data && data.data) {
        console.log('[LoadDraft] Rascunho encontrado no localStorage');
        
        // Só aplica no state se solicitado
        if (applyToState) {
          console.log('[LoadDraft] Aplicando dados no state...');
          Object.keys(data.data).forEach(key => {
            if (key in state.formData) {
              state.formData[key] = data.data[key];
            }
          });
          
          // Restaurar answeredFields se disponível
          if (Array.isArray(data.answered_fields)) {
            state.answeredFields = [...data.answered_fields];
            console.log(`[LoadDraft] Restaurando ${data.answered_fields.length} campos respondidos`);
          }
          
          // Restaurar step se disponível E dentro do range válido
          if (typeof data.current_step === 'number') {
            // Garantir que o step está dentro do range de steps ativos
            const maxStep = activeSteps.value.length - 1;
            const validStep = Math.min(data.current_step, maxStep);
            console.log(`[LoadDraft] Restaurando step ${validStep} do localStorage (original: ${data.current_step}, max: ${maxStep})`);
            state.currentStepIndex = validStep >= 0 ? validStep : 0;
          } else {
            console.warn('[LoadDraft] current_step não encontrado no draft, mantendo step 0');
          }
        }
        
        return data.data;
      }
    }
  } catch (e) {
    console.warn('[LoadDraft] Erro ao carregar do localStorage:', e);
  }
  return null;
}

/**
 * Incrementa contador de falhas da IA
 * Retorna true se excedeu o limite
 */
function incrementAIFailure() {
  state.aiFailureCount++;
  return state.aiFailureCount >= state.maxAiFailures;
}

/**
 * Reseta contador de falhas da IA
 */
function resetAIFailures() {
  state.aiFailureCount = 0;
}

// ============================================
// HELPERS
// ============================================

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

// ============================================
// AUTO-SAVE
// ============================================

let saveTimeout = null;
let chatSaveTimeout = null;

// Auto-save do formData (com debounce de 2 segundos)
watch(
  () => state.formData,
  () => {
    if (saveTimeout) clearTimeout(saveTimeout);
    saveTimeout = setTimeout(() => {
      saveDraft();
      console.log('[AutoSave] FormData salvo após alteração');
    }, 2000);
  },
  { deep: true }
);

// Auto-save do chat (mensagens) - com debounce de 1 segundo
watch(
  () => state.messages,
  () => {
    if (chatSaveTimeout) clearTimeout(chatSaveTimeout);
    chatSaveTimeout = setTimeout(() => {
      saveChat();
      console.log('[AutoSave] Chat salvo após nova mensagem');
    }, 1000);
  },
  { deep: true }
);

// ============================================
// EXPORTS
// ============================================

export function useConversationalAI() {
  // Computed para formData direto (sem readonly wrapper)
  const formData = computed(() => state.formData);
  
  // Computed para campos respondidos (para debug/UI)
  const answeredFields = computed(() => state.answeredFields);
  
  return {
    // Estado (readonly para prevenir mutações diretas)
    state: readonly(state),
    
    // FormData reativo direto (para componentes que precisam atualizar)
    formData,
    
    // Campos respondidos pelo usuário (para debug/verificação)
    answeredFields,
    
    // Computed
    currentStep,
    currentStepId,
    totalSteps,
    progressPercentage,
    currentLevel,
    nextLevel,
    xpToNextLevel,
    isReviewMode,
    canPublish,
    pendingRequiredFields,
    canUndo,
    canRedo,
    
    // Actions
    initSession,
    addMessage,
    sendUserMessage,
    updateField,
    addXP,
    unlockAchievement,
    dismissAchievementPopup,
    nextStep,
    previousStep,
    goToStep,
    enterReviewMode,
    exitReviewMode,
    validateAllFields,
    suggestColors,
    saveDraft,
    loadDraft,
    undo,
    redo,
    incrementAIFailure,
    resetAIFailures,
    setPurchasedPages,
    generateConversationalSummary,
    checkPreviousSession,
    clearSession,
    restoreSession,
    
    // Constants
    ONBOARDING_STEPS: activeSteps,
    ACHIEVEMENTS,
    SITE_LEVELS
  };
}

// Export default para uso direto
export default useConversationalAI;
