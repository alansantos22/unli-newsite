/**
 * ============================================
 * CONVERSATIONAL AI SERVICE
 * Gerencia o estado do chat + formulário
 * ============================================
 * 
 * Fonte única de verdade para o onboarding conversacional.
 * Sincroniza dados entre chat (IA) e formulário (edição manual).
 */

import { reactive, computed, readonly, watch } from 'vue';
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
    optionalFields: ['instagram', 'facebook', 'linkedin', 'hasPhysicalLocation', 'address', 'businessHours'],
    xpReward: 10,
    achievement: {
      id: 'connection_established',
      name: 'Conexão Estabelecida',
      icon: '📞'
    }
  },
  {
    id: 'about',
    name: 'História da Empresa',
    icon: '🏢',
    description: 'Quem vocês são e como começaram',
    requiredFields: [],
    optionalFields: ['companyBio', 'foundingYear', 'founders', 'aboutImage', 'companyHighlights', 'mission', 'vision', 'values'],
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
// SISTEMA DE CONQUISTAS
// ============================================

export const ACHIEVEMENTS = {
  identity_unlocked: { id: 'identity_unlocked', name: 'Identidade Desbloqueada', icon: '🎨', xp: 15 },
  connection_established: { id: 'connection_established', name: 'Conexão Estabelecida', icon: '📞', xp: 10 },
  story_mastered: { id: 'story_mastered', name: 'História Masterizada', icon: '📖', xp: 20 },
  services_catalog: { id: 'services_catalog', name: 'Catálogo de Serviços', icon: '⚙️', xp: 15 },
  faq_strategic: { id: 'faq_strategic', name: 'FAQ Estratégico', icon: '🎯', xp: 10 },
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
    primaryColor: '#0066CC',
    secondaryColor: '#28A745',
    voiceTone: 'profissional',
    logo: null,
    hasNoLogo: false,
    
    // Contact
    whatsapp: '',
    additionalPhones: [],
    socialNetworks: [],
    hasPhysicalLocation: false,
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
    
    // Finalization
    additionalNotes: '',
    urgency: 'normal',
    inspirationUrls: [],
    inspirationImages: []
  },
  
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
  purchasedPages: []
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

const currentStep = computed(() => activeSteps.value[state.currentStepIndex]);

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
function initSession(sessionId, initialData = {}, purchasedPages = []) {
  state.sessionId = sessionId || `session_${Date.now()}`;
  
  // Detectar modo demo (IDs locais ou que começam com 'demo-' ou 'session_')
  state.isDemoMode = !sessionId || sessionId.startsWith('demo-') || sessionId.startsWith('session_');
  
  // Resetar contador de falhas da IA
  state.aiFailureCount = 0;
  
  // Definir páginas compradas (para steps dinâmicos)
  state.purchasedPages = purchasedPages || [];
  
  // Mesclar dados iniciais
  if (initialData && typeof initialData === 'object') {
    Object.keys(initialData).forEach(key => {
      if (key in state.formData) {
        state.formData[key] = initialData[key];
      }
    });
  }
  
  // Inicializar histórico com estado atual
  state.formHistory = [JSON.parse(JSON.stringify(state.formData))];
  state.historyIndex = 0;
  
  // Tentar carregar chat salvo
  const savedChat = loadSavedChat(state.sessionId);
  
  if (savedChat && savedChat.messages && savedChat.messages.length > 0) {
    // Restaurar mensagens salvas
    state.messages = savedChat.messages;
    
    // Verificar o que falta e adicionar mensagem de continuação
    const missingInfo = getMissingInfo();
    if (missingInfo) {
      addMessage({
        type: 'assistant',
        content: getContinueMessage(missingInfo)
      });
    }
  } else {
    // Primeira vez - mensagem de abertura
    const hasExistingData = checkHasExistingData();
    addMessage({
      type: 'assistant',
      content: getOpeningMessage(hasExistingData)
    });
  }
  
  // Calcular XP inicial baseado nos dados existentes
  recalculateXP();
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
 * Retorna a mensagem de abertura do assistente
 */
function getOpeningMessage(hasExistingData = false) {
  if (hasExistingData) {
    const { formData } = state;
    const parts = [];
    
    if (formData.companyName) parts.push(`**${formData.companyName}**`);
    if (formData.businessType) parts.push(`ramo de **${formData.businessType}**`);
    
    const missing = getMissingInfo();
    const missingText = missing ? missing.slice(0, 3).join(', ') : 'alguns detalhes';
    
    return `Oi! 👋 Que bom ter você de volta!

Vi que já temos algumas informações: ${parts.join(', ')}.

Enquanto a gente conversa, o formulário ao lado vai sendo atualizado automaticamente. ✨

**Vamos continuar?** Ainda precisamos de: ${missingText}.`;
  }
  
  return `Oi! 👋 Eu sou o **Unli**, seu assistente de criação de sites.

Vou te ajudar a montar um site incrível em poucos minutos. É só conversar comigo naturalmente!

Enquanto a gente conversa, o formulário ao lado vai sendo preenchido automaticamente. ✨

**Vamos começar?** Me conta o **nome da sua empresa**, o que vocês fazem e, se tiver, uma frase que resuma o seu negócio.`;
}

/**
 * Mensagem de continuação após carregar chat salvo
 */
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
    
    if (response.success) {
      // Atualiza campos extraídos
      if (response.extracted_fields) {
        await updateFieldsWithAnimation(response.extracted_fields);
      }
      
      // Verifica conquistas
      if (response.achievements) {
        response.achievements.forEach(ach => unlockAchievement(ach));
      }
      
      // Adiciona resposta do assistente
      addMessage({
        type: 'assistant',
        content: response.assistant_message,
        suggestions: response.suggestions || null,
        actions: response.actions || null
      });
      
      // Verifica se step foi completado
      checkStepCompletion();
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
 * Processa mensagem com a API do Gemini
 */
async function processWithAI(userMessage) {
  const payload = {
    session_id: state.sessionId,
    step: currentStepId.value,
    messages: state.messages.slice(-10), // Últimas 10 mensagens para contexto
    user_message: userMessage,
    current_form_data: state.formData,
    voice_tone: state.formData.voiceTone || 'profissional'
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
 * Atualiza campos com animação de shimmer e check
 */
async function updateFieldsWithAnimation(fields) {
  const fieldKeys = Object.keys(fields);
  
  // Ativa shimmer em todos os campos
  state.shimmeringFields = [...fieldKeys];
  
  // Aguarda efeito visual
  await sleep(300);
  
  // Atualiza cada campo com delay para efeito cascata
  for (const [key, value] of Object.entries(fields)) {
    if (key in state.formData) {
      state.formData[key] = value;
      
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
    
    // Se não passar na validação, adiciona aviso
    if (cleanValue && !pattern.test(cleanValue)) {
      warnings.push({
        field: fieldId,
        value: cleanValue,
        expectedFormat: expectedFormats[validationType] || expectedFormats[fieldId]
      });
    }
  });
  
  // Se tiver avisos, adiciona mensagem informativa (não bloqueante)
  if (warnings.length > 0) {
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
  if (fieldId in state.formData) {
    // Salvar estado anterior no histórico antes de alterar
    pushToHistory();
    
    state.formData[fieldId] = value;
    
    // Limpa erro de validação se existir
    if (state.validationErrors[fieldId]) {
      delete state.validationErrors[fieldId];
    }
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
 * Verifica se o step atual foi completado
 */
function checkStepCompletion() {
  const step = currentStep.value;
  if (!step || !step.requiredFields) return;
  
  // Verifica se todos os campos obrigatórios estão preenchidos
  const allRequiredFilled = step.requiredFields.every(field => {
    const value = state.formData[field];
    return value && (Array.isArray(value) ? value.length > 0 : true);
  });
  
  if (allRequiredFilled && step.achievement) {
    unlockAchievement(step.achievement.id);
  }
}

/**
 * Avança para o próximo step
 */
function nextStep() {
  if (state.currentStepIndex < activeSteps.value.length - 1) {
    state.currentStepIndex++;
    
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
  }
}

/**
 * Vai para um step específico
 */
function goToStep(stepIndex) {
  if (stepIndex >= 0 && stepIndex < activeSteps.value.length) {
    state.currentStepIndex = stepIndex;
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
  
  const draftData = {
    session_id: state.sessionId,
    current_step: state.currentStepIndex,
    data: state.formData,
    is_draft: true,
    timestamp: Date.now()
  };
  
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
 * Carrega rascunho salvo
 */
function loadDraft(sessionId) {
  // Tentar localStorage primeiro
  try {
    const saved = localStorage.getItem(`draft_${sessionId}`);
    if (saved) {
      const data = JSON.parse(saved);
      if (data && data.data) {
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

watch(
  () => state.formData,
  () => {
    if (saveTimeout) clearTimeout(saveTimeout);
    saveTimeout = setTimeout(saveDraft, 2000);
  },
  { deep: true }
);

// ============================================
// EXPORTS
// ============================================

export function useConversationalAI() {
  return {
    // Estado (readonly para prevenir mutações diretas)
    state: readonly(state),
    
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
