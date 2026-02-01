/**
 * ============================================
 * SMART ONBOARDING - STATE MANAGER
 * ============================================
 * 
 * Gerenciamento de estado centralizado para o onboarding.
 * Sistema de checklist para controle preciso de cada campo.
 */

import { reactive, computed, readonly } from 'vue';
import { 
  ONBOARDING_STEPS, 
  FIELD_STATUS, 
  SITE_LEVELS, 
  ACHIEVEMENTS,
  getLevelByXP,
  getLevelProgress
} from '../types/onboarding.types.js';

// ============================================
// STORAGE KEYS
// ============================================

const STORAGE_KEY_PREFIX = 'smart_onboarding_';
const STORAGE_SESSION = `${STORAGE_KEY_PREFIX}session`;

// ============================================
// ESTADO INICIAL
// ============================================

function createInitialFormData() {
  const data = {};
  ONBOARDING_STEPS.forEach(step => {
    step.fields.forEach(field => {
      if (field.multiple) {
        data[field.id] = [];
      } else if (field.type === 'boolean') {
        data[field.id] = false;
      } else {
        data[field.id] = '';
      }
    });
  });
  return data;
}

function createInitialFieldStatus() {
  const status = {};
  ONBOARDING_STEPS.forEach(step => {
    step.fields.forEach(field => {
      status[field.id] = FIELD_STATUS.EMPTY;
    });
  });
  return status;
}

// ============================================
// STATE REATIVO
// ============================================

const state = reactive({
  // Sessão
  sessionId: null,
  initialized: false,
  
  // Navegação
  currentStepIndex: 0,
  isStepConfirmed: {}, // { stepId: boolean }
  
  // Dados do formulário
  formData: createInitialFormData(),
  
  // Checklist de status de cada campo
  fieldStatus: createInitialFieldStatus(),
  
  // Chat
  messages: [],
  isTyping: false,
  isProcessing: false,
  
  // Gamificação
  xp: 0,
  achievements: [],
  pendingAchievement: null,
  
  // UI State
  shimmeringFields: [],
  recentlyFilledFields: [],
  editingField: null,
  
  // Resumo pendente
  pendingStepSummary: null,
  
  // Flags para evitar repetição
  askedOptionalFields: {} // { fieldId: true } - campos que já foram perguntados
});

// ============================================
// COMPUTEDS
// ============================================

const currentStep = computed(() => ONBOARDING_STEPS[state.currentStepIndex]);
const currentStepId = computed(() => currentStep.value?.id);
const totalSteps = computed(() => ONBOARDING_STEPS.length);

/**
 * Campos do step atual com seu status
 */
const currentStepFields = computed(() => {
  if (!currentStep.value) return [];
  
  return currentStep.value.fields.map(field => ({
    ...field,
    status: state.fieldStatus[field.id],
    value: state.formData[field.id],
    isRequired: field.required,
    isFilled: state.fieldStatus[field.id] === FIELD_STATUS.ANSWERED || 
              state.fieldStatus[field.id] === FIELD_STATUS.CONFIRMED,
    isSkipped: state.fieldStatus[field.id] === FIELD_STATUS.SKIPPED
  }));
});

/**
 * Próximo campo a ser perguntado no step atual
 * Respeita a ordem e pula campos já respondidos/skipados
 */
const nextFieldToAsk = computed(() => {
  if (!currentStep.value) return null;
  
  for (const field of currentStep.value.fields) {
    // Pular campos ocultos
    if (field.hidden) continue;
    
    const status = state.fieldStatus[field.id];
    
    // Se ainda não foi respondido e não foi skipado
    if (status === FIELD_STATUS.EMPTY) {
      // Verificar se já foi perguntado (para opcionais)
      if (!field.required && state.askedOptionalFields[field.id]) {
        continue; // Já perguntamos e usuário não respondeu
      }
      return field;
    }
  }
  
  return null; // Todos os campos foram tratados
});

/**
 * Verifica se o step atual está completo
 * Um step está completo quando todos os campos obrigatórios estão respondidos
 * E todos os opcionais foram perguntados ou respondidos
 */
const isCurrentStepComplete = computed(() => {
  if (!currentStep.value) return false;
  
  for (const field of currentStep.value.fields) {
    if (field.hidden) continue;
    
    const status = state.fieldStatus[field.id];
    
    // Campos obrigatórios devem estar respondidos ou confirmados
    if (field.required) {
      if (status !== FIELD_STATUS.ANSWERED && status !== FIELD_STATUS.CONFIRMED) {
        return false;
      }
    }
  }
  
  // Verifica se não há mais campos para perguntar
  return nextFieldToAsk.value === null;
});

/**
 * Verifica se o step atual foi confirmado pelo usuário
 */
const isCurrentStepConfirmed = computed(() => {
  return !!state.isStepConfirmed[currentStepId.value];
});

/**
 * XP total e nível atual
 */
const currentLevel = computed(() => getLevelByXP(state.xp));
const levelProgress = computed(() => getLevelProgress(state.xp));
const nextLevel = computed(() => {
  const current = currentLevel.value;
  return SITE_LEVELS.find(l => l.level === current.level + 1);
});

/**
 * Progresso geral (porcentagem de campos preenchidos)
 */
const overallProgress = computed(() => {
  let filled = 0;
  let total = 0;
  
  ONBOARDING_STEPS.forEach(step => {
    step.fields.forEach(field => {
      if (field.hidden) return;
      
      total++;
      const status = state.fieldStatus[field.id];
      if (status === FIELD_STATUS.ANSWERED || status === FIELD_STATUS.CONFIRMED) {
        filled++;
      } else if (status === FIELD_STATUS.SKIPPED) {
        // Skipped conta como 50% para não prejudicar muito o progresso
        filled += 0.5;
      }
    });
  });
  
  return total > 0 ? Math.round((filled / total) * 100) : 0;
});

/**
 * Campos obrigatórios pendentes
 */
const pendingRequiredFields = computed(() => {
  const pending = [];
  
  ONBOARDING_STEPS.forEach(step => {
    step.fields.forEach(field => {
      if (field.required) {
        const status = state.fieldStatus[field.id];
        if (status !== FIELD_STATUS.ANSWERED && status !== FIELD_STATUS.CONFIRMED) {
          pending.push({
            stepId: step.id,
            stepName: step.name,
            fieldId: field.id,
            fieldLabel: field.label
          });
        }
      }
    });
  });
  
  return pending;
});

/**
 * Pode publicar o site?
 */
const canPublish = computed(() => pendingRequiredFields.value.length === 0);

// ============================================
// ACTIONS
// ============================================

/**
 * Inicializa a sessão
 * @param {string} sessionId - ID da sessão
 * @param {object} initialData - Dados iniciais do briefing
 * @param {object} fieldChecklist - Checklist de campos do backend (opcional)
 */
function initSession(sessionId, initialData = {}, fieldChecklist = null) {
  state.sessionId = sessionId || generateSessionId();
  
  // Tentar restaurar do localStorage
  const savedSession = loadFromStorage();
  
  if (savedSession && savedSession.sessionId === state.sessionId) {
    // Restaurar sessão anterior
    Object.assign(state.formData, savedSession.formData);
    Object.assign(state.fieldStatus, savedSession.fieldStatus);
    state.messages = savedSession.messages || [];
    state.xp = savedSession.xp || 0;
    state.achievements = savedSession.achievements || [];
    state.currentStepIndex = savedSession.currentStepIndex || 0;
    state.isStepConfirmed = savedSession.isStepConfirmed || {};
    state.askedOptionalFields = savedSession.askedOptionalFields || {};
  } else {
    // Nova sessão
    resetState();
    
    // Aplicar dados iniciais se fornecidos
    if (initialData && Object.keys(initialData).length > 0) {
      Object.entries(initialData).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          // Tratar arrays vazios
          if (Array.isArray(value) && value.length === 0) return;
          
          state.formData[key] = value;
          state.fieldStatus[key] = FIELD_STATUS.ANSWERED;
        }
      });
    }
    
    // Aplicar fieldChecklist do backend se fornecido
    // Isso sincroniza o estado com o que está no banco
    if (fieldChecklist && Object.keys(fieldChecklist).length > 0) {
      Object.entries(fieldChecklist).forEach(([, fields]) => {
        Object.entries(fields).forEach(([fieldId, fieldData]) => {
          // Só aplica se o status do backend for diferente de empty
          // e ainda não tiver sido setado pelos initialData
          if (fieldData.status && fieldData.status !== 'empty') {
            if (state.fieldStatus[fieldId] === FIELD_STATUS.EMPTY) {
              state.fieldStatus[fieldId] = fieldData.status;
            }
          }
        });
      });
    }
  }
  
  state.initialized = true;
  saveToStorage();
}

/**
 * Reseta o estado para valores iniciais
 */
function resetState() {
  state.formData = createInitialFormData();
  state.fieldStatus = createInitialFieldStatus();
  state.messages = [];
  state.xp = 0;
  state.achievements = [];
  state.currentStepIndex = 0;
  state.isStepConfirmed = {};
  state.askedOptionalFields = {};
  state.shimmeringFields = [];
  state.recentlyFilledFields = [];
  state.editingField = null;
  state.pendingStepSummary = null;
}

/**
 * Atualiza um campo do formulário
 */
function updateField(fieldId, value) {
  const oldValue = state.formData[fieldId];
  const oldStatus = state.fieldStatus[fieldId];
  
  // Atualizar valor
  state.formData[fieldId] = value;
  
  // Verificar se é um campo boolean
  const field = findFieldById(fieldId);
  const isBoolean = field && field.type === 'boolean';
  
  // Atualizar status
  // Para booleans: marcar como ANSWERED quando o usuário interagir (qualquer valor é válido)
  // Para outros: só marcar como ANSWERED se tiver um valor não-vazio
  if (isBoolean) {
    // Boolean foi explicitamente setado pelo usuário
    state.fieldStatus[fieldId] = FIELD_STATUS.ANSWERED;
  } else if (value !== null && value !== undefined && value !== '' && 
      !(Array.isArray(value) && value.length === 0)) {
    state.fieldStatus[fieldId] = FIELD_STATUS.ANSWERED;
  }
  
  // Adicionar XP se é primeira vez preenchendo
  if (oldStatus === FIELD_STATUS.EMPTY && state.fieldStatus[fieldId] === FIELD_STATUS.ANSWERED) {
    if (field && field.xp > 0) {
      addXP(field.xp, `Preencheu ${field.label}`);
    }
    
    // Achievement de primeiro campo
    if (state.achievements.length === 0) {
      unlockAchievement('first_field');
    }
  }
  
  // Efeito visual
  addShimmerEffect(fieldId);
  addRecentlyFilled(fieldId);
  
  // Salvar
  saveToStorage();
  
  return { oldValue, newValue: value };
}

/**
 * Marca um campo como skippado (usuário disse que não tem/não quer)
 */
function skipField(fieldId) {
  state.fieldStatus[fieldId] = FIELD_STATUS.SKIPPED;
  state.askedOptionalFields[fieldId] = true;
  saveToStorage();
}

/**
 * Marca que um campo opcional já foi perguntado
 */
function markFieldAsAsked(fieldId) {
  state.askedOptionalFields[fieldId] = true;
  saveToStorage();
}

/**
 * Avança para o próximo step
 */
function nextStep() {
  if (state.currentStepIndex < ONBOARDING_STEPS.length - 1) {
    state.currentStepIndex++;
    saveToStorage();
  }
}

/**
 * Volta para step anterior
 */
function previousStep() {
  if (state.currentStepIndex > 0) {
    state.currentStepIndex--;
    saveToStorage();
  }
}

/**
 * Vai para um step específico
 */
function goToStep(index) {
  if (index >= 0 && index < ONBOARDING_STEPS.length) {
    state.currentStepIndex = index;
    saveToStorage();
  }
}

/**
 * Confirma o step atual (após usuário ver resumo e aprovar)
 */
function confirmCurrentStep() {
  const stepId = currentStepId.value;
  state.isStepConfirmed[stepId] = true;
  
  // Marcar todos os campos do step como confirmados
  currentStep.value.fields.forEach(field => {
    if (state.fieldStatus[field.id] === FIELD_STATUS.ANSWERED) {
      state.fieldStatus[field.id] = FIELD_STATUS.CONFIRMED;
    }
  });
  
  // Achievement do step
  const achievementMap = {
    'identity': 'identity_complete',
    'contact': 'contact_complete',
    'about': 'story_complete',
    'services': 'services_complete',
    'faq': 'faq_complete',
    'finalization': 'site_complete'
  };
  
  const achievementId = achievementMap[stepId];
  if (achievementId && !state.achievements.includes(achievementId)) {
    unlockAchievement(achievementId);
  }
  
  // XP do step
  addXP(currentStep.value.xpReward, `Completou ${currentStep.value.name}`);
  
  saveToStorage();
}

/**
 * Adiciona XP
 */
function addXP(amount, reason = '') {
  const oldLevel = currentLevel.value;
  state.xp += amount;
  const newLevel = currentLevel.value;
  
  // Level up?
  if (newLevel.level > oldLevel.level) {
    state.pendingAchievement = {
      type: 'level_up',
      level: newLevel,
      xp: amount,
      reason
    };
    playSound('levelUp');
    
    // Auto-dismiss após 5 segundos
    setTimeout(() => {
      if (state.pendingAchievement?.type === 'level_up' && state.pendingAchievement?.level?.level === newLevel.level) {
        state.pendingAchievement = null;
      }
    }, 5000);
  } else {
    playSound('fieldComplete');
  }
  
  saveToStorage();
}

/**
 * Desbloqueia uma conquista
 */
function unlockAchievement(achievementId) {
  if (state.achievements.includes(achievementId)) return;
  
  const achievement = ACHIEVEMENTS[achievementId];
  if (!achievement) return;
  
  state.achievements.push(achievementId);
  state.xp += achievement.xp;
  
  state.pendingAchievement = {
    type: 'achievement',
    achievement,
    xp: achievement.xp
  };
  
  // Auto-dismiss após 5 segundos
  setTimeout(() => {
    if (state.pendingAchievement?.achievement?.id === achievementId) {
      state.pendingAchievement = null;
    }
  }, 5000);
  
  playSound('achievement');
  saveToStorage();
}

/**
 * Dispensa o popup de conquista
 */
function dismissAchievementPopup() {
  state.pendingAchievement = null;
}

/**
 * Adiciona mensagem ao chat
 */
function addMessage(message) {
  const msg = {
    id: Date.now() + Math.random(),
    timestamp: Date.now(),
    ...message
  };
  state.messages.push(msg);
  saveToStorage();
  return msg;
}

/**
 * Define estado de digitação
 */
function setTyping(isTyping) {
  state.isTyping = isTyping;
}

/**
 * Define estado de processamento
 */
function setProcessing(isProcessing) {
  state.isProcessing = isProcessing;
}

/**
 * Gera resumo do step atual
 */
function generateStepSummary() {
  if (!currentStep.value) return null;
  
  const summary = {
    stepId: currentStep.value.id,
    stepName: currentStep.value.name,
    fields: []
  };
  
  currentStep.value.fields.forEach(field => {
    if (field.hidden) return;
    
    const status = state.fieldStatus[field.id];
    const value = state.formData[field.id];
    
    if (status === FIELD_STATUS.ANSWERED || status === FIELD_STATUS.CONFIRMED) {
      summary.fields.push({
        id: field.id,
        label: field.label,
        value: formatValueForSummary(value, field.type),
        status: 'filled'
      });
    } else if (status === FIELD_STATUS.SKIPPED) {
      summary.fields.push({
        id: field.id,
        label: field.label,
        value: '(não informado)',
        status: 'skipped'
      });
    }
  });
  
  return summary;
}

// ============================================
// HELPERS PRIVADOS
// ============================================

function generateSessionId() {
  return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
}

function findFieldById(fieldId) {
  for (const step of ONBOARDING_STEPS) {
    const field = step.fields.find(f => f.id === fieldId);
    if (field) return { ...field, stepId: step.id };
  }
  return null;
}

function addShimmerEffect(fieldId) {
  if (!state.shimmeringFields.includes(fieldId)) {
    state.shimmeringFields.push(fieldId);
    setTimeout(() => {
      const index = state.shimmeringFields.indexOf(fieldId);
      if (index > -1) state.shimmeringFields.splice(index, 1);
    }, 1500);
  }
}

function addRecentlyFilled(fieldId) {
  if (!state.recentlyFilledFields.includes(fieldId)) {
    state.recentlyFilledFields.push(fieldId);
    setTimeout(() => {
      const index = state.recentlyFilledFields.indexOf(fieldId);
      if (index > -1) state.recentlyFilledFields.splice(index, 1);
    }, 3000);
  }
}

function formatValueForSummary(value, type) {
  if (value === null || value === undefined) return '';
  
  if (Array.isArray(value)) {
    if (value.length === 0) return '';
    if (type === 'services') {
      return value.map(s => s.name || s).join(', ');
    }
    if (type === 'faq') {
      return `${value.length} pergunta(s)`;
    }
    return value.join(', ');
  }
  
  if (type === 'image') {
    return '📷 Imagem enviada';
  }
  
  if (type === 'boolean') {
    return value ? 'Sim' : 'Não';
  }
  
  return String(value);
}

function playSound(soundType) {
  // Implementar reprodução de som
  // Por enquanto, apenas log
  console.log(`[Sound] ${soundType}`);
}

// ============================================
// PERSISTÊNCIA
// ============================================

function saveToStorage() {
  if (!state.sessionId) return;
  
  const data = {
    sessionId: state.sessionId,
    formData: state.formData,
    fieldStatus: state.fieldStatus,
    messages: state.messages,
    xp: state.xp,
    achievements: state.achievements,
    currentStepIndex: state.currentStepIndex,
    isStepConfirmed: state.isStepConfirmed,
    askedOptionalFields: state.askedOptionalFields,
    savedAt: Date.now()
  };
  
  try {
    localStorage.setItem(STORAGE_SESSION, JSON.stringify(data));
  } catch (e) {
    console.error('[Storage] Erro ao salvar:', e);
  }
}

function loadFromStorage() {
  try {
    const data = localStorage.getItem(STORAGE_SESSION);
    return data ? JSON.parse(data) : null;
  } catch (e) {
    console.error('[Storage] Erro ao carregar:', e);
    return null;
  }
}

function clearStorage() {
  try {
    localStorage.removeItem(STORAGE_SESSION);
  } catch (e) {
    console.error('[Storage] Erro ao limpar:', e);
  }
}

// ============================================
// EXPORTS
// ============================================

export function useOnboardingState() {
  return {
    // State (readonly para evitar mutações diretas)
    state: readonly(state),
    
    // Computeds
    currentStep,
    currentStepId,
    currentStepFields,
    nextFieldToAsk,
    isCurrentStepComplete,
    isCurrentStepConfirmed,
    currentLevel,
    levelProgress,
    nextLevel,
    overallProgress,
    pendingRequiredFields,
    canPublish,
    totalSteps,
    
    // Actions
    initSession,
    resetState,
    updateField,
    skipField,
    markFieldAsAsked,
    nextStep,
    previousStep,
    goToStep,
    confirmCurrentStep,
    addXP,
    unlockAchievement,
    dismissAchievementPopup,
    addMessage,
    setTyping,
    setProcessing,
    generateStepSummary,
    saveToStorage,
    clearStorage,
    
    // Constants
    ONBOARDING_STEPS,
    FIELD_STATUS,
    SITE_LEVELS,
    ACHIEVEMENTS
  };
}

export default useOnboardingState;
