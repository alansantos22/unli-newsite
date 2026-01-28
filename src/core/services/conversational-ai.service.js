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

// ============================================
// CONFIGURAÇÃO DOS STEPS
// ============================================

export const ONBOARDING_STEPS = [
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
  pendingFieldsByStep: {}
});

// ============================================
// COMPUTED PROPERTIES
// ============================================

const currentStep = computed(() => ONBOARDING_STEPS[state.currentStepIndex]);

const currentStepId = computed(() => currentStep.value?.id || 'identity');

const totalSteps = computed(() => ONBOARDING_STEPS.length);

const progressPercentage = computed(() => {
  const totalFields = ONBOARDING_STEPS.reduce((acc, step) => {
    return acc + step.requiredFields.length + step.optionalFields.length;
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

const canPublish = computed(() => {
  // Verificar campos obrigatórios de todos os steps
  for (const step of ONBOARDING_STEPS) {
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
  for (const step of ONBOARDING_STEPS) {
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
 */
function initSession(sessionId, initialData = {}) {
  state.sessionId = sessionId || `session_${Date.now()}`;
  
  // Mesclar dados iniciais
  if (initialData && typeof initialData === 'object') {
    Object.keys(initialData).forEach(key => {
      if (key in state.formData) {
        state.formData[key] = initialData[key];
      }
    });
  }
  
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
    addMessage({
      type: 'assistant',
      content: 'Eita, algo deu errado aqui. Tenta de novo? 🙈'
    });
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
}

/**
 * Atualiza um campo manualmente (edição no formulário)
 */
function updateField(fieldId, value) {
  if (fieldId in state.formData) {
    state.formData[fieldId] = value;
    
    // Limpa erro de validação se existir
    if (state.validationErrors[fieldId]) {
      delete state.validationErrors[fieldId];
    }
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
  if (!step) return;
  
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
  if (state.currentStepIndex < ONBOARDING_STEPS.length - 1) {
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
  if (stepIndex >= 0 && stepIndex < ONBOARDING_STEPS.length) {
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
  
  // Mensagem final do assistente
  const verdict = generateVerdict();
  addMessage({
    type: 'assistant',
    content: verdict
  });
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
  for (const step of ONBOARDING_STEPS) {
    for (const field of step.requiredFields) {
      const value = state.formData[field];
      if (!value || (Array.isArray(value) && value.length === 0)) {
        state.validationErrors[field] = 'Campo obrigatório';
      }
    }
  }
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
 */
async function saveDraft() {
  try {
    await fetch('/api/onboarding/save-draft.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        session_id: state.sessionId,
        current_step: state.currentStepIndex,
        data: state.formData,
        xp: state.xp,
        achievements: state.unlockedAchievements,
        is_draft: true
      })
    });
  } catch (error) {
    console.error('[ConversationalAI] Erro ao salvar rascunho:', error);
  }
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
    
    // Constants
    ONBOARDING_STEPS,
    ACHIEVEMENTS,
    SITE_LEVELS
  };
}

// Export default para uso direto
export default useConversationalAI;
