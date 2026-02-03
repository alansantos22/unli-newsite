/**
 * ============================================
 * USE BRAND CONSULTANT - Composable
 * Gerencia o estado e lógica do consultor de marca
 * ============================================
 */

import { reactive, computed } from 'vue';

// =====================
// STORAGE HELPERS
// =====================

const STORAGE_PREFIX = 'unli_consultant_';

function getStorageKey(sessionId, suffix) {
  return `${STORAGE_PREFIX}${sessionId}_${suffix}`;
}

function saveToStorage(sessionId, key, data) {
  try {
    const storageKey = getStorageKey(sessionId, key);
    localStorage.setItem(storageKey, JSON.stringify(data));
  } catch (err) {
    console.warn('Failed to save to localStorage:', err);
  }
}

function loadFromStorage(sessionId, key) {
  try {
    const storageKey = getStorageKey(sessionId, key);
    const data = localStorage.getItem(storageKey);
    if (!data) return null;
    
    const parsed = JSON.parse(data);
    
    // Ensure chatHistory is always an array
    if (key === 'chatHistory') {
      return Array.isArray(parsed) ? parsed : [];
    }
    
    return parsed;
  } catch (err) {
    console.warn('Failed to load from localStorage:', err);
    return null;
  }
}

function clearStorage(sessionId) {
  try {
    const keys = ['chatHistory', 'extractedData', 'formData', 'sessionMeta'];
    keys.forEach(key => {
      localStorage.removeItem(getStorageKey(sessionId, key));
    });
  } catch (err) {
    console.warn('Failed to clear localStorage:', err);
  }
}

// Estado global (singleton)
const state = reactive({
  // Session ID (token do onboarding)
  sessionId: null,
  
  // Histórico do chat
  chatHistory: [],
  
  // Dados extraídos
  extractedData: null,
  
  // Status
  isConversationActive: false,
  isConversationFinished: false,
  hasConfirmedData: false,
  
  // Mapeamento para v-models do formulário
  formData: {
    // Identidade
    companyName: '',
    slogan: '',
    
    // Conteúdo
    niche: '',
    targetAudience: '',
    description: '',
    about: '',
    
    // História e Missão
    history: '',
    mission: '',
    values: [],
    
    // Diferenciais
    usp: '',
    methodology: '',
    authority: '',
    
    // Tom e Estilo
    toneOfVoice: '',
    keywords: [],
    
    // Identidade Visual (extraída pelo Creative Director)
    visualIdentity: {
      suggestedStyle: '',
      colorVibe: '',
      primaryColorSuggestion: '',
      secondaryColorSuggestion: '',
      typographyMood: '',
      layoutSuggestion: '',
      moodKeywords: [],
      heroSuggestion: '',
      specialSections: []
    }
  }
});

export function useBrandConsultant() {
  // =====================
  // COMPUTED
  // =====================
  
  const hasExtractedData = computed(() => state.extractedData !== null);
  
  const extractionConfidence = computed(() => {
    return state.extractedData?.extractionMeta?.confidence || null;
  });
  
  const suggestedTagline = computed(() => {
    return state.extractedData?.aiSuggestions?.suggestedTagline || '';
  });
  
  const visualIdentity = computed(() => {
    return state.formData.visualIdentity || null;
  });
  
  const hasVisualIdentity = computed(() => {
    const vi = state.formData.visualIdentity;
    return vi && (vi.suggestedStyle || vi.primaryColorSuggestion);
  });
  
  // =====================
  // ACTIONS
  // =====================
  
  /**
   * Inicializa a sessão com um ID (token)
   * Restaura dados do localStorage se existirem
   */
  function initSession(sessionId) {
    state.sessionId = sessionId;
    
    // Try to restore from localStorage
    const savedHistory = loadFromStorage(sessionId, 'chatHistory');
    const savedExtracted = loadFromStorage(sessionId, 'extractedData');
    const savedMeta = loadFromStorage(sessionId, 'sessionMeta');
    
    if (savedHistory && savedHistory.length > 0) {
      state.chatHistory = savedHistory;
      state.isConversationActive = true;
    }
    
    if (savedExtracted) {
      state.extractedData = savedExtracted;
      mapExtractedDataToForm(savedExtracted);
    }
    
    if (savedMeta) {
      state.isConversationFinished = savedMeta.isConversationFinished || false;
      state.hasConfirmedData = savedMeta.hasConfirmedData || false;
    }
    
    return {
      hasRestoredData: !!(savedHistory && savedHistory.length > 0),
      messageCount: savedHistory?.length || 0
    };
  }
  
  /**
   * Inicia uma nova sessão de consultoria (limpa estado anterior)
   */
  function startSession() {
    state.chatHistory = [];
    state.extractedData = null;
    state.isConversationActive = true;
    state.isConversationFinished = false;
    state.hasConfirmedData = false;
    
    // Clear storage for this session
    if (state.sessionId) {
      clearStorage(state.sessionId);
    }
  }
  
  /**
   * Adiciona uma mensagem ao histórico e persiste
   */
  function addMessage(role, content) {
    state.chatHistory.push({
      role,
      content,
      timestamp: new Date().toISOString()
    });
    
    // Auto-save to localStorage
    if (state.sessionId) {
      saveToStorage(state.sessionId, 'chatHistory', state.chatHistory);
    }
  }
  
  /**
   * Salva o histórico manualmente (chamado em beforeunload, etc)
   */
  function saveConversation() {
    if (!state.sessionId) return;
    
    saveToStorage(state.sessionId, 'chatHistory', state.chatHistory);
    saveToStorage(state.sessionId, 'sessionMeta', {
      isConversationFinished: state.isConversationFinished,
      hasConfirmedData: state.hasConfirmedData,
      lastSaved: new Date().toISOString()
    });
    
    if (state.extractedData) {
      saveToStorage(state.sessionId, 'extractedData', state.extractedData);
    }
  }
  
  /**
   * Finaliza a conversa
   */
  function finishConversation() {
    state.isConversationFinished = true;
    state.isConversationActive = false;
    
    // Persist state
    saveConversation();
  }
  
  /**
   * Armazena os dados extraídos
   */
  function setExtractedData(data) {
    state.extractedData = data;
    
    // Mapear para o formulário
    mapExtractedDataToForm(data);
    
    // Persist extracted data
    if (state.sessionId) {
      saveToStorage(state.sessionId, 'extractedData', data);
    }
  }
  
  /**
   * Mapeia os dados extraídos para os v-models do formulário
   */
  function mapExtractedDataToForm(data) {
    if (!data) return;
    
    const form = state.formData;
    
    // Company Info
    form.companyName = data.companyInfo?.name || '';
    form.niche = data.companyInfo?.niche || '';
    form.targetAudience = data.companyInfo?.targetAudience || '';
    form.description = data.companyInfo?.mainProduct || '';
    
    // Brand Core
    form.history = data.brandCore?.historySummary || '';
    form.mission = data.brandCore?.mission || '';
    form.values = data.brandCore?.values || [];
    form.about = data.brandCore?.historySummary || '';
    
    // Differentiation
    form.usp = data.differentiation?.usp || '';
    form.methodology = data.differentiation?.uniqueMethodology || '';
    form.authority = data.authorityTriggers?.keyAchievements || '';
    
    // AI Suggestions
    form.slogan = data.aiSuggestions?.suggestedTagline || '';
    form.toneOfVoice = data.aiSuggestions?.toneOfVoice || '';
    form.keywords = data.aiSuggestions?.suggestedKeywords || [];
    
    // Visual Identity (from Creative Director)
    if (data.visualIdentity) {
      form.visualIdentity = {
        suggestedStyle: data.visualIdentity.suggestedStyle || '',
        colorVibe: data.visualIdentity.colorVibe || '',
        primaryColorSuggestion: data.visualIdentity.primaryColorSuggestion || '',
        secondaryColorSuggestion: data.visualIdentity.secondaryColorSuggestion || '',
        typographyMood: data.visualIdentity.typographyMood || '',
        layoutSuggestion: data.visualIdentity.layoutSuggestion || '',
        moodKeywords: data.visualIdentity.moodKeywords || [],
        heroSuggestion: data.visualIdentity.heroSuggestion || '',
        specialSections: data.visualIdentity.specialSections || []
      };
    }
  }
  
  /**
   * Confirma os dados e marca como pronto
   */
  function confirmData() {
    state.hasConfirmedData = true;
    return state.formData;
  }
  
  /**
   * Atualiza um campo específico do formulário
   */
  function updateFormField(field, value) {
    if (field in state.formData) {
      state.formData[field] = value;
    }
  }
  
  /**
   * Obtém o histórico do chat para envio à API
   */
  function getChatHistoryForAPI() {
    return state.chatHistory.map(msg => ({
      role: msg.role,
      content: msg.content
    }));
  }
  
  /**
   * Reseta todo o estado
   */
  function reset() {
    state.chatHistory = [];
    state.extractedData = null;
    state.isConversationActive = false;
    state.isConversationFinished = false;
    state.hasConfirmedData = false;
    
    // Reset form data
    Object.keys(state.formData).forEach(key => {
      if (key === 'visualIdentity') {
        // Reset visual identity object
        state.formData.visualIdentity = {
          suggestedStyle: '',
          colorVibe: '',
          primaryColorSuggestion: '',
          secondaryColorSuggestion: '',
          typographyMood: '',
          layoutSuggestion: '',
          moodKeywords: [],
          heroSuggestion: '',
          specialSections: []
        };
      } else if (Array.isArray(state.formData[key])) {
        state.formData[key] = [];
      } else {
        state.formData[key] = '';
      }
    });
  }
  
  /**
   * Exporta dados para o schema do briefing
   */
  function exportToBriefingSchema() {
    const form = state.formData;
    const extracted = state.extractedData;
    
    return {
      identidadeVital: {
        companyName: form.companyName,
        slogan: form.slogan,
        logo: { uploaded: false, url: '', filename: '' },
        hasNoLogo: false
      },
      conteudo: {
        description: form.description,
        about: form.about,
        services: [],
        differentials: [form.usp, form.methodology].filter(Boolean)
      },
      seo: {
        keywords: form.keywords,
        metaDescription: form.description,
        targetAudience: form.targetAudience
      },
      brandStrategy: {
        history: form.history,
        mission: form.mission,
        values: form.values,
        toneOfVoice: form.toneOfVoice,
        authority: form.authority,
        methodology: form.methodology
      },
      visualIdentity: {
        suggestedStyle: form.visualIdentity.suggestedStyle,
        colorVibe: form.visualIdentity.colorVibe,
        primaryColor: form.visualIdentity.primaryColorSuggestion,
        secondaryColor: form.visualIdentity.secondaryColorSuggestion,
        typographyMood: form.visualIdentity.typographyMood,
        layoutSuggestion: form.visualIdentity.layoutSuggestion,
        moodKeywords: form.visualIdentity.moodKeywords,
        heroSuggestion: form.visualIdentity.heroSuggestion,
        specialSections: form.visualIdentity.specialSections
      },
      aiGenerated: {
        extractedAt: extracted?.extractionMeta?.extractedAt || null,
        confidence: extracted?.extractionMeta?.confidence || null,
        suggestedTaglines: extracted?.aiSuggestions?.alternativeTaglines || [],
        contentSeeds: extracted?.contentSeeds || {}
      },
      // NOVO: Perfil da Marca Extraído completo
      brandProfile: extracted ? {
        extractionMeta: extracted.extractionMeta || {},
        companyInfo: extracted.companyInfo || {},
        brandCore: extracted.brandCore || {},
        authorityTriggers: extracted.authorityTriggers || {},
        differentiation: extracted.differentiation || {},
        visualIdentity: extracted.visualIdentity || {},
        aiSuggestions: extracted.aiSuggestions || {},
        conversationContext: {
          chatHistory: state.chatHistory || [],
          keyInsights: extracted.keyInsights || [],
          customerPainPoints: extracted.customerPainPoints || [],
          businessGoals: extracted.businessGoals || []
        }
      } : null
    };
  }
  
  // =====================
  // RETURN
  // =====================
  
  return {
    // State (readonly refs)
    sessionId: computed(() => state.sessionId),
    chatHistory: computed(() => state.chatHistory),
    extractedData: computed(() => state.extractedData),
    formData: computed(() => state.formData),
    isConversationActive: computed(() => state.isConversationActive),
    isConversationFinished: computed(() => state.isConversationFinished),
    hasConfirmedData: computed(() => state.hasConfirmedData),
    
    // Computed
    hasExtractedData,
    extractionConfidence,
    suggestedTagline,
    visualIdentity,
    hasVisualIdentity,
    
    // Actions
    initSession,
    startSession,
    addMessage,
    saveConversation,
    finishConversation,
    setExtractedData,
    confirmData,
    updateFormField,
    getChatHistoryForAPI,
    reset,
    exportToBriefingSchema
  };
}

export default useBrandConsultant;
