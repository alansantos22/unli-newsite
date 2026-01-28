<template>
  <div class="conversational-wizard" :class="{ 
    'split-view': !isMobile && !isReviewMode,
    'review-mode': isReviewMode,
    'mobile': isMobile
  }">
    
    <!-- ============================================ -->
    <!-- ACHIEVEMENT POPUP -->
    <!-- ============================================ -->
    <Transition name="achievement-slide">
      <div v-if="pendingAchievement" class="achievement-popup" @click="dismissAchievementPopup">
        <div class="achievement-glow"></div>
        <span class="achievement-icon">{{ pendingAchievement.icon }}</span>
        <div class="achievement-info">
          <span class="achievement-label">Conquista Desbloqueada!</span>
          <h4 class="achievement-name">{{ pendingAchievement.name }}</h4>
        </div>
        <span class="achievement-xp">+{{ pendingAchievement.xp }} XP</span>
      </div>
    </Transition>
    
    <!-- ============================================ -->
    <!-- CHAT SECTION -->
    <!-- ============================================ -->
    <section v-if="!isReviewMode" class="chat-section">
      <!-- Header do Chat -->
      <header class="chat-header">
        <div class="header-left">
          <div class="assistant-avatar">
            <span class="avatar-emoji">🤖</span>
            <span class="avatar-status"></span>
          </div>
          <div class="assistant-info">
            <h3 class="assistant-name">Jules</h3>
            <span class="assistant-status">{{ isTyping ? 'digitando...' : 'Online' }}</span>
          </div>
        </div>
        
        <div class="header-center">
          <div class="step-badge">
            <span class="step-icon">{{ currentStep?.icon }}</span>
            <span class="step-name">{{ currentStep?.name }}</span>
          </div>
        </div>
        
        <div class="header-right">
          <button 
            v-if="!isMobile"
            class="btn-toggle-form"
            @click="showFormPreview = !showFormPreview"
          >
            {{ showFormPreview ? '📋 Ocultar' : '📋 Ver Formulário' }}
          </button>
        </div>
      </header>
      
      <!-- Área de Mensagens -->
      <div class="chat-messages" ref="messagesContainer">
        <TransitionGroup name="message-fade">
          <ChatMessage 
            v-for="msg in messages" 
            :key="msg.id"
            :message="msg"
            @action="handleMessageAction"
            @suggestion-accept="handleSuggestionAccept"
            @custom-input="handleCustomInput"
          />
        </TransitionGroup>
        
        <!-- Typing Indicator -->
        <div v-if="isTyping" class="typing-indicator">
          <div class="typing-avatar">🤖</div>
          <div class="typing-dots">
            <span></span>
            <span></span>
            <span></span>
          </div>
        </div>
      </div>
      
      <!-- Área de Input -->
      <footer class="chat-input-area">
        <!-- Quick Actions (context-aware) -->
        <div v-if="quickActions.length && !userInput" class="quick-actions">
          <button 
            v-for="action in quickActions" 
            :key="action.id"
            class="quick-action-btn"
            @click="handleQuickAction(action)"
          >
            <span class="action-icon">{{ action.icon }}</span>
            <span class="action-label">{{ action.label }}</span>
          </button>
        </div>
        
        <!-- Input Principal -->
        <div class="input-wrapper">
          <!-- Textarea -->
          <textarea 
            ref="inputTextarea"
            v-model="userInput"
            @keydown.enter.exact.prevent="sendMessage"
            @input="autoResizeTextarea"
            placeholder="Digite sua mensagem..."
            rows="1"
            :disabled="isProcessing"
          />
          
          <!-- Botões de Ação -->
          <div class="input-actions">
            <!-- Botão de Áudio - DESABILITADO POR ENQUANTO
            <button 
              class="btn-audio"
              :class="{ 
                'is-recording': isRecording,
                'is-disabled': !speechSupported
              }"
              @click="toggleRecording"
              :disabled="isProcessing"
              :title="speechSupported ? 'Gravar áudio' : 'Áudio não suportado neste navegador'"
            >
              <span v-if="isRecording" class="recording-pulse"></span>
              {{ isRecording ? '⏹️' : '🎤' }}
            </button>
            -->
            
            <!-- Botão Enviar -->
            <button 
              class="btn-send"
              @click="sendMessage"
              :disabled="!userInput.trim() || isProcessing"
            >
              ➤
            </button>
          </div>
        </div>
        
        <!-- Interim Result (enquanto grava) -->
        <div v-if="interimResult" class="interim-result">
          <span class="interim-icon">🎤</span>
          <span class="interim-text">{{ interimResult }}</span>
        </div>
        
        <!-- Progress Steps (Mobile) -->
        <div v-if="isMobile" class="mobile-progress">
          <div class="progress-steps">
            <button
              v-for="(step, index) in ONBOARDING_STEPS"
              :key="step.id"
              class="progress-step"
              :class="{
                'is-current': index === currentStepIndex,
                'is-completed': index < currentStepIndex
              }"
              @click="goToStep(index)"
              :disabled="index > currentStepIndex"
            >
              {{ step.icon }}
            </button>
          </div>
        </div>
      </footer>
    </section>
    
    <!-- ============================================ -->
    <!-- FORM PREVIEW (Desktop Sidebar) -->
    <!-- ============================================ -->
    <aside 
      v-if="!isMobile && showFormPreview && !isReviewMode" 
      class="form-preview-section"
    >
      <FormPreview
        :form-data="formData"
        :current-step-id="currentStepId"
        :shimmering-fields="shimmeringFields"
        :recently-filled-fields="recentlyFilledFields"
        :validation-errors="validationErrors"
        @field-click="focusField"
        @field-edit="editFieldManually"
      />
      
      <!-- Gamification Bar -->
      <div class="gamification-bar">
        <!-- Level Display -->
        <div class="level-display">
          <span class="level-icon">{{ currentLevel?.icon }}</span>
          <div class="level-info">
            <span class="level-name">{{ currentLevel?.name }}</span>
            <span class="level-xp">{{ xp }} XP</span>
          </div>
        </div>
        
        <!-- Progress to Next Level -->
        <div class="xp-progress">
          <div class="xp-bar">
            <div 
              class="xp-fill"
              :style="{ 
                width: xpProgressPercentage + '%',
                backgroundColor: currentLevel?.color
              }"
            ></div>
          </div>
          <span v-if="nextLevel" class="xp-next">
            {{ xpToNextLevel }} XP para {{ nextLevel.name }}
          </span>
        </div>
        
        <!-- Site Potential -->
        <div class="site-potential">
          <div class="potential-header">
            <span class="potential-label">⚡ Potencial do Site</span>
            <span class="potential-value">{{ progressPercentage }}%</span>
          </div>
          <div class="potential-bar">
            <div 
              class="potential-fill"
              :style="{ width: progressPercentage + '%' }"
            ></div>
          </div>
        </div>
      </div>
    </aside>
    
    <!-- ============================================ -->
    <!-- REVIEW MODE (Mesa de Edição) -->
    <!-- ============================================ -->
    <section v-if="isReviewMode" class="review-section">
      <header class="review-header">
        <button class="btn-back-to-chat" @click="exitReviewMode">
          ← Voltar ao Chat
        </button>
        <h2 class="review-title">🏁 Mesa de Edição</h2>
        <div class="review-stats">
          <span class="stat-item">
            <span class="stat-icon">{{ currentLevel?.icon }}</span>
            <span class="stat-value">{{ currentLevel?.name }}</span>
          </span>
          <span class="stat-item">
            <span class="stat-icon">⚡</span>
            <span class="stat-value">{{ progressPercentage }}%</span>
          </span>
        </div>
      </header>
      
      <!-- Verdict Message -->
      <div class="review-verdict" v-if="lastAssistantMessage">
        <div class="verdict-avatar">🤖</div>
        <div class="verdict-content" v-html="formatMarkdown(lastAssistantMessage)"></div>
      </div>
      
      <!-- Review Cards -->
      <div class="review-cards">
        <ReviewCard
          v-for="step in ONBOARDING_STEPS"
          :key="step.id"
          :step="step"
          :form-data="formData"
          :validation-errors="validationErrors"
          @edit="openStepEditor"
        />
      </div>
      
      <!-- Pending Items Warning -->
      <div v-if="pendingRequiredFields.length" class="pending-warning">
        <span class="warning-icon">⚠️</span>
        <div class="warning-content">
          <strong>{{ pendingRequiredFields.length }} pendência(s) para publicar:</strong>
          <ul>
            <li v-for="field in pendingRequiredFields" :key="field.field">
              {{ field.stepName }}: {{ field.field }}
            </li>
          </ul>
        </div>
      </div>
      
      <!-- Publish Button -->
      <div class="review-actions">
        <button 
          class="btn-secondary"
          @click="handleGoToForm"
        >
          📝 Revisar no Formulário
        </button>
        <button 
          class="btn-publish"
          :class="{ 'is-disabled': !canPublish }"
          :disabled="!canPublish"
          @click="handlePublish"
        >
          🚀 Publicar Site
        </button>
        <p v-if="!canPublish" class="publish-hint">
          Complete os campos obrigatórios para publicar
        </p>
      </div>
    </section>
    
    <!-- ============================================ -->
    <!-- MOBILE: Floating Form Button -->
    <!-- ============================================ -->
    <button 
      v-if="isMobile && !isReviewMode"
      class="mobile-form-fab"
      @click="showMobileFormSheet = true"
    >
      📋 Ver Formulário
    </button>
    
    <!-- Mobile Form Bottom Sheet -->
    <Transition name="sheet-slide">
      <div v-if="showMobileFormSheet" class="mobile-form-sheet">
        <div class="sheet-overlay" @click="showMobileFormSheet = false"></div>
        <div class="sheet-content">
          <div class="sheet-handle" @click="showMobileFormSheet = false"></div>
          <FormPreview
            :form-data="formData"
            :current-step-id="currentStepId"
            :shimmering-fields="shimmeringFields"
            :recently-filled-fields="recentlyFilledFields"
            :validation-errors="validationErrors"
            @field-click="focusField"
            @field-edit="editFieldManually"
          />
        </div>
      </div>
    </Transition>
    
  </div>
</template>

<script>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import { useConversationalAI } from '@/core/services/conversational-ai.service';
import speechToTextService from '@/core/services/speech-to-text.service';
import ChatMessage from './ConversationalOnboarding/ChatMessage.vue';
import FormPreview from './ConversationalOnboarding/FormPreview.vue';
import ReviewCard from './ConversationalOnboarding/ReviewCard.vue';
import { marked } from 'marked';
import DOMPurify from 'dompurify';

export default {
  name: 'ConversationalOnboardingWizard',
  
  components: {
    ChatMessage,
    FormPreview,
    ReviewCard
  },
  
  props: {
    sessionId: {
      type: String,
      default: null
    },
    initialData: {
      type: Object,
      default: () => ({})
    }
  },
  
  emits: ['complete', 'save', 'error', 'go-to-form'],
  
  setup(props, { emit }) {
    // ============================================
    // COMPOSABLES
    // ============================================
    
    const {
      state,
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
      initSession,
      sendUserMessage,
      updateField,
      dismissAchievementPopup,
      nextStep,
      goToStep,
      exitReviewMode,
      ONBOARDING_STEPS
    } = useConversationalAI();
    
    // ============================================
    // REFS
    // ============================================
    
    const messagesContainer = ref(null);
    const inputTextarea = ref(null);
    const userInput = ref('');
    const isRecording = ref(false);
    const interimResult = ref('');
    const showFormPreview = ref(true);
    const showMobileFormSheet = ref(false);
    
    // ============================================
    // COMPUTED
    // ============================================
    
    const isMobile = computed(() => {
      if (typeof window === 'undefined') return false;
      return window.innerWidth < 1024;
    });
    
    const messages = computed(() => state.messages);
    const formData = computed(() => state.formData);
    const isTyping = computed(() => state.isTyping);
    const isProcessing = computed(() => state.isProcessing);
    const pendingAchievement = computed(() => state.pendingAchievement);
    const shimmeringFields = computed(() => state.shimmeringFields);
    const recentlyFilledFields = computed(() => state.recentlyFilledFields);
    const validationErrors = computed(() => state.validationErrors);
    const xp = computed(() => state.xp);
    const currentStepIndex = computed(() => state.currentStepIndex);
    
    const speechSupported = computed(() => speechToTextService.isSupported);
    
    const xpProgressPercentage = computed(() => {
      if (!nextLevel.value) return 100;
      const currentMin = currentLevel.value?.minXP || 0;
      const nextMin = nextLevel.value.minXP;
      const range = nextMin - currentMin;
      const progress = xp.value - currentMin;
      return Math.min(100, Math.round((progress / range) * 100));
    });
    
    const quickActions = computed(() => {
      const stepId = currentStepId.value;
      
      const actions = {
        identity: [
          { id: 'skip_colors', icon: '🎨', label: 'Sugerir cores pra mim' },
          { id: 'no_logo', icon: '📝', label: 'Não tenho logo ainda' }
        ],
        contact: [
          { id: 'no_address', icon: '🏠', label: 'Não tenho endereço físico' }
        ],
        about: [
          { id: 'generate_story', icon: '✨', label: 'Me ajuda a escrever' }
        ],
        services: [
          { id: 'example_services', icon: '💡', label: 'Ver exemplos' }
        ],
        faq: [
          { id: 'suggest_faq', icon: '🤖', label: 'Sugerir perguntas do meu nicho' }
        ],
        finalization: [
          { id: 'finish_quick', icon: '🚀', label: 'Finalizar rápido' }
        ]
      };
      
      return actions[stepId] || [];
    });
    
    const lastAssistantMessage = computed(() => {
      const assistantMsgs = messages.value.filter(m => m.type === 'assistant');
      return assistantMsgs.length ? assistantMsgs[assistantMsgs.length - 1].content : '';
    });
    
    // ============================================
    // METHODS
    // ============================================
    
    async function sendMessage() {
      const content = userInput.value.trim();
      if (!content || isProcessing.value) return;
      
      userInput.value = '';
      await sendUserMessage(content, false);
      scrollToBottom();
    }
    
    async function toggleRecording() {
      if (!speechSupported.value) return;
      
      if (isRecording.value) {
        // Parar gravação e enviar resultado
        const transcript = speechToTextService.stop();
        isRecording.value = false;
        interimResult.value = '';
        
        if (transcript && transcript.trim()) {
          await sendUserMessage(transcript.trim(), true);
          scrollToBottom();
        }
      } else {
        // Iniciar gravação
        isRecording.value = true;
        
        // Configura handler para preview
        speechToTextService.onInterimResult = (text) => {
          interimResult.value = text;
        };
        
        try {
          // Apenas iniciar - não esperar resultado automático
          speechToTextService._initRecognition();
          speechToTextService.recognition.start();
        } catch (error) {
          isRecording.value = false;
          interimResult.value = '';
          console.error('[Speech] Erro:', error.message);
        }
      }
    }
    
    function handleQuickAction(action) {
      const actionMessages = {
        skip_colors: 'Pode sugerir cores que combinem com meu ramo de atuação?',
        no_logo: 'Ainda não tenho logo, pode criar um texto estilizado.',
        no_address: 'Não tenho endereço físico, trabalho apenas online.',
        generate_story: 'Me ajuda a escrever a história da empresa? Posso te dar alguns detalhes.',
        example_services: 'Pode me dar exemplos de como descrever meus serviços?',
        suggest_faq: 'Sugere perguntas frequentes baseadas no meu ramo de atuação?',
        finish_quick: 'Acho que tá tudo certo, pode finalizar!'
      };
      
      const message = actionMessages[action.id];
      if (message) {
        userInput.value = message;
        sendMessage();
      }
    }
    
    function handleMessageAction(action) {
      if (action.type === 'next_step') {
        nextStep();
      } else if (action.type === 'update_field') {
        updateField(action.field, action.value);
      }
    }
    
    async function handleSuggestionAccept(suggestion) {
      if (suggestion.field && suggestion.value) {
        updateField(suggestion.field, suggestion.value);
        
        // Enviar confirmação ao chat para continuar o fluxo
        await sendUserMessage(`Gostei! Vou usar: "${suggestion.value}"`, false);
        scrollToBottom();
      }
    }
    
    async function handleCustomInput(field, label) {
      // Quando o cliente quer escrever sua própria frase
      userInput.value = `Quero escrever minha própria ${label}. `;
      inputTextarea.value?.focus();
    }
    
    function focusField(fieldId) {
      // Scroll para o campo no formulário
      console.log('Focus field:', fieldId);
    }
    
    function editFieldManually(fieldId, value) {
      updateField(fieldId, value);
    }
    
    function openStepEditor(stepId) {
      const stepIndex = ONBOARDING_STEPS.findIndex(s => s.id === stepId);
      if (stepIndex !== -1) {
        goToStep(stepIndex);
        exitReviewMode();
      }
    }
    
    function handlePublish() {
      if (!canPublish.value) return;
      emit('complete', formData.value);
    }
    
    function handleGoToForm() {
      emit('go-to-form', formData.value);
    }
    
    function autoResizeTextarea() {
      const textarea = inputTextarea.value;
      if (textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
      }
    }
    
    function scrollToBottom() {
      nextTick(() => {
        const container = messagesContainer.value;
        if (container) {
          container.scrollTop = container.scrollHeight;
        }
      });
    }
    
    function formatMarkdown(text) {
      if (!text) return '';
      const html = marked.parse(text);
      return DOMPurify.sanitize(html);
    }
    
    // ============================================
    // LIFECYCLE
    // ============================================
    
    onMounted(() => {
      initSession(props.sessionId, props.initialData);
      scrollToBottom();
    });
    
    // Auto-scroll quando novas mensagens chegam
    watch(messages, () => {
      scrollToBottom();
    }, { deep: true });
    
    // ============================================
    // EXPOSE
    // ============================================
    
    return {
      // Refs
      messagesContainer,
      inputTextarea,
      userInput,
      isRecording,
      interimResult,
      showFormPreview,
      showMobileFormSheet,
      
      // Computed
      isMobile,
      messages,
      formData,
      isTyping,
      isProcessing,
      pendingAchievement,
      shimmeringFields,
      recentlyFilledFields,
      validationErrors,
      xp,
      currentStep,
      currentStepId,
      currentStepIndex,
      totalSteps,
      progressPercentage,
      currentLevel,
      nextLevel,
      xpToNextLevel,
      xpProgressPercentage,
      isReviewMode,
      canPublish,
      pendingRequiredFields,
      speechSupported,
      quickActions,
      lastAssistantMessage,
      ONBOARDING_STEPS,
      
      // Methods
      sendMessage,
      toggleRecording,
      handleQuickAction,
      handleMessageAction,
      handleSuggestionAccept,
      handleCustomInput,
      focusField,
      editFieldManually,
      openStepEditor,
      handlePublish,
      handleGoToForm,
      autoResizeTextarea,
      formatMarkdown,
      dismissAchievementPopup,
      goToStep,
      exitReviewMode
    };
  }
};
</script>

<style lang="scss" scoped>
.conversational-wizard {
  display: flex;
  min-height: 100vh;
  background: linear-gradient(135deg, #0a0a1a 0%, #1a1a3a 100%);
  color: #fff;
  
  &.split-view {
    .chat-section {
      flex: 1;
      max-width: 60%;
    }
    
    .form-preview-section {
      flex: 0 0 40%;
      max-width: 40%;
    }
  }
  
  &.review-mode {
    justify-content: center;
    padding: 2rem;
  }
  
  &.mobile {
    flex-direction: column;
    
    .chat-section {
      max-width: 100%;
    }
  }
}

// ============================================
// ACHIEVEMENT POPUP
// ============================================

.achievement-popup {
  position: fixed;
  top: 1.5rem;
  right: 1.5rem;
  z-index: 1000;
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border-radius: 1rem;
  box-shadow: 0 10px 40px rgba(99, 102, 241, 0.4);
  cursor: pointer;
  animation: achievement-bounce 0.5s ease-out;
  
  .achievement-glow {
    position: absolute;
    inset: -2px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6, #6366f1);
    border-radius: inherit;
    z-index: -1;
    filter: blur(8px);
    opacity: 0.6;
    animation: glow-pulse 2s ease-in-out infinite;
  }
  
  .achievement-icon {
    font-size: 2rem;
  }
  
  .achievement-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    
    .achievement-label {
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      opacity: 0.9;
    }
    
    .achievement-name {
      font-size: 1.1rem;
      font-weight: 600;
      margin: 0;
    }
  }
  
  .achievement-xp {
    font-size: 1.25rem;
    font-weight: 700;
    color: #fbbf24;
  }
}

@keyframes achievement-bounce {
  0% { transform: translateX(100%) scale(0.8); opacity: 0; }
  50% { transform: translateX(-10px) scale(1.05); }
  100% { transform: translateX(0) scale(1); opacity: 1; }
}

@keyframes glow-pulse {
  0%, 100% { opacity: 0.4; }
  50% { opacity: 0.8; }
}

.achievement-slide-enter-active,
.achievement-slide-leave-active {
  transition: all 0.4s ease;
}

.achievement-slide-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

// ============================================
// CHAT SECTION
// ============================================

.chat-section {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: #0d0d1a;
}

.chat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  background: rgba(255, 255, 255, 0.03);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  
  .header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }
  
  .assistant-avatar {
    position: relative;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border-radius: 50%;
    
    .avatar-emoji {
      font-size: 1.25rem;
    }
    
    .avatar-status {
      position: absolute;
      bottom: 2px;
      right: 2px;
      width: 10px;
      height: 10px;
      background: #22c55e;
      border: 2px solid #0d0d1a;
      border-radius: 50%;
    }
  }
  
  .assistant-info {
    display: flex;
    flex-direction: column;
    
    .assistant-name {
      font-size: 1rem;
      font-weight: 600;
      margin: 0;
    }
    
    .assistant-status {
      font-size: 0.75rem;
      color: rgba(255, 255, 255, 0.5);
    }
  }
  
  .step-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 2rem;
    
    .step-icon {
      font-size: 1rem;
    }
    
    .step-name {
      font-size: 0.875rem;
      font-weight: 500;
    }
  }
  
  .btn-toggle-form {
    padding: 0.5rem 1rem;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 0.5rem;
    color: #fff;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    
    &:hover {
      background: rgba(255, 255, 255, 0.12);
    }
  }
}

// ============================================
// CHAT MESSAGES
// ============================================

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  
  &::-webkit-scrollbar {
    width: 6px;
  }
  
  &::-webkit-scrollbar-track {
    background: transparent;
  }
  
  &::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 3px;
  }
}

.message-fade-enter-active,
.message-fade-leave-active {
  transition: all 0.3s ease;
}

.message-fade-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

// ============================================
// TYPING INDICATOR
// ============================================

.typing-indicator {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 1rem;
  width: fit-content;
  
  .typing-avatar {
    font-size: 1.25rem;
  }
  
  .typing-dots {
    display: flex;
    gap: 4px;
    
    span {
      width: 8px;
      height: 8px;
      background: rgba(255, 255, 255, 0.4);
      border-radius: 50%;
      animation: typing-bounce 1.4s ease-in-out infinite;
      
      &:nth-child(2) { animation-delay: 0.2s; }
      &:nth-child(3) { animation-delay: 0.4s; }
    }
  }
}

@keyframes typing-bounce {
  0%, 60%, 100% { transform: translateY(0); }
  30% { transform: translateY(-4px); }
}

// ============================================
// CHAT INPUT AREA
// ============================================

.chat-input-area {
  padding: 1rem 1.5rem 1.5rem;
  background: rgba(255, 255, 255, 0.02);
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.quick-actions {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
  overflow-x: auto;
  padding-bottom: 0.5rem;
  
  &::-webkit-scrollbar {
    height: 4px;
  }
  
  .quick-action-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: rgba(99, 102, 241, 0.15);
    border: 1px solid rgba(99, 102, 241, 0.3);
    border-radius: 2rem;
    color: #a5b4fc;
    font-size: 0.875rem;
    white-space: nowrap;
    cursor: pointer;
    transition: all 0.2s;
    
    &:hover {
      background: rgba(99, 102, 241, 0.25);
      border-color: rgba(99, 102, 241, 0.5);
    }
  }
}

.input-wrapper {
  display: flex;
  align-items: flex-end;
  gap: 0.75rem;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 1rem;
  
  textarea {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    color: #fff;
    font-size: 1rem;
    line-height: 1.5;
    resize: none;
    max-height: 120px;
    
    &::placeholder {
      color: rgba(255, 255, 255, 0.4);
    }
  }
  
  .input-actions {
    display: flex;
    gap: 0.5rem;
  }
  
  .btn-audio,
  .btn-send {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    font-size: 1.25rem;
    transition: all 0.2s;
  }
  
  .btn-audio {
    position: relative;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    
    &:hover:not(.is-disabled) {
      background: rgba(255, 255, 255, 0.15);
    }
    
    &.is-recording {
      background: #ef4444;
      
      .recording-pulse {
        position: absolute;
        inset: -4px;
        border: 2px solid #ef4444;
        border-radius: 50%;
        animation: pulse-ring 1.5s ease-out infinite;
      }
    }
    
    &.is-disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  }
  
  .btn-send {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff;
    
    &:hover:not(:disabled) {
      transform: scale(1.05);
    }
    
    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  }
}

@keyframes pulse-ring {
  0% { transform: scale(1); opacity: 1; }
  100% { transform: scale(1.5); opacity: 0; }
}

.interim-result {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.5rem;
  padding: 0.5rem 0.75rem;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  border-radius: 0.5rem;
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.7);
  
  .interim-icon {
    animation: pulse 1s ease-in-out infinite;
  }
}

// ============================================
// FORM PREVIEW SECTION
// ============================================

.form-preview-section {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: rgba(255, 255, 255, 0.02);
  border-left: 1px solid rgba(255, 255, 255, 0.06);
  overflow-y: auto;
}

.gamification-bar {
  padding: 1rem 1.5rem;
  background: rgba(255, 255, 255, 0.03);
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  
  .level-display {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    
    .level-icon {
      font-size: 1.5rem;
    }
    
    .level-info {
      display: flex;
      flex-direction: column;
      
      .level-name {
        font-weight: 600;
        font-size: 1rem;
      }
      
      .level-xp {
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.5);
      }
    }
  }
  
  .xp-progress {
    margin-bottom: 1rem;
    
    .xp-bar {
      height: 6px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 3px;
      overflow: hidden;
      
      .xp-fill {
        height: 100%;
        border-radius: inherit;
        transition: width 0.5s ease;
      }
    }
    
    .xp-next {
      display: block;
      margin-top: 0.5rem;
      font-size: 0.75rem;
      color: rgba(255, 255, 255, 0.5);
    }
  }
  
  .site-potential {
    .potential-header {
      display: flex;
      justify-content: space-between;
      margin-bottom: 0.5rem;
      
      .potential-label {
        font-size: 0.875rem;
        font-weight: 500;
      }
      
      .potential-value {
        font-weight: 700;
        color: #22c55e;
      }
    }
    
    .potential-bar {
      height: 8px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 4px;
      overflow: hidden;
      
      .potential-fill {
        height: 100%;
        background: linear-gradient(90deg, #22c55e, #16a34a);
        border-radius: inherit;
        transition: width 0.5s ease;
      }
    }
  }
}

// ============================================
// REVIEW SECTION
// ============================================

.review-section {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
}

.review-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 2rem;
  
  .btn-back-to-chat {
    padding: 0.5rem 1rem;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 0.5rem;
    color: #fff;
    cursor: pointer;
    transition: all 0.2s;
    
    &:hover {
      background: rgba(255, 255, 255, 0.12);
    }
  }
  
  .review-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
  }
  
  .review-stats {
    display: flex;
    gap: 1rem;
    
    .stat-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1rem;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 2rem;
      
      .stat-icon {
        font-size: 1rem;
      }
      
      .stat-value {
        font-weight: 600;
      }
    }
  }
}

.review-verdict {
  display: flex;
  gap: 1rem;
  padding: 1.5rem;
  background: rgba(99, 102, 241, 0.1);
  border: 1px solid rgba(99, 102, 241, 0.2);
  border-radius: 1rem;
  margin-bottom: 2rem;
  
  .verdict-avatar {
    font-size: 2rem;
    flex-shrink: 0;
  }
  
  .verdict-content {
    line-height: 1.6;
    
    :deep(strong) {
      color: #a5b4fc;
    }
  }
}

.review-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.pending-warning {
  display: flex;
  gap: 1rem;
  padding: 1rem 1.5rem;
  background: rgba(245, 158, 11, 0.1);
  border: 1px solid rgba(245, 158, 11, 0.3);
  border-radius: 0.75rem;
  margin-bottom: 2rem;
  
  .warning-icon {
    font-size: 1.5rem;
  }
  
  .warning-content {
    strong {
      display: block;
      margin-bottom: 0.5rem;
    }
    
    ul {
      margin: 0;
      padding-left: 1.25rem;
      
      li {
        color: rgba(255, 255, 255, 0.7);
      }
    }
  }
}

.review-actions {
  margin-top: 2rem;
  text-align: center;
  display: flex;
  gap: 1rem;
  flex-direction: column;
  align-items: center;
  
  @media (min-width: 640px) {
    flex-direction: row;
    justify-content: center;
  }
  
  .btn-secondary {
    padding: 0.875rem 2.5rem;
    background: rgba(255, 255, 255, 0.1);
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 0.75rem;
    color: #fff;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    
    &:hover {
      background: rgba(255, 255, 255, 0.2);
      border-color: rgba(255, 255, 255, 0.5);
      transform: translateY(-1px);
    }
  }
  
  .btn-publish {
    padding: 1rem 3rem;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    border: none;
    border-radius: 0.75rem;
    color: #fff;
    font-size: 1.25rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    
    &:hover:not(.is-disabled) {
      transform: scale(1.02);
      box-shadow: 0 10px 40px rgba(34, 197, 94, 0.3);
    }
    
    &.is-disabled {
      background: rgba(255, 255, 255, 0.1);
      cursor: not-allowed;
    }
  }
  
  .publish-hint {
    margin-top: 0.75rem;
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.5);
  }
}

// ============================================
// MOBILE STYLES
// ============================================

.mobile-form-fab {
  position: fixed;
  bottom: 100px;
  right: 1rem;
  padding: 0.75rem 1.25rem;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  border: none;
  border-radius: 2rem;
  color: #fff;
  font-size: 0.875rem;
  font-weight: 600;
  box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
  cursor: pointer;
  z-index: 50;
}

.mobile-form-sheet {
  position: fixed;
  inset: 0;
  z-index: 100;
  
  .sheet-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
  }
  
  .sheet-content {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    max-height: 80vh;
    background: #1a1a2e;
    border-radius: 1.5rem 1.5rem 0 0;
    overflow-y: auto;
  }
  
  .sheet-handle {
    width: 40px;
    height: 4px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 2px;
    margin: 1rem auto;
    cursor: pointer;
  }
}

.sheet-slide-enter-active,
.sheet-slide-leave-active {
  transition: all 0.3s ease;
}

.sheet-slide-enter-from,
.sheet-slide-leave-to {
  .sheet-overlay {
    opacity: 0;
  }
  
  .sheet-content {
    transform: translateY(100%);
  }
}

.mobile-progress {
  margin-top: 0.75rem;
  
  .progress-steps {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
  }
  
  .progress-step {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.05);
    border: 2px solid transparent;
    border-radius: 50%;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s;
    
    &.is-current {
      background: rgba(99, 102, 241, 0.2);
      border-color: #6366f1;
    }
    
    &.is-completed {
      background: rgba(34, 197, 94, 0.2);
      border-color: #22c55e;
    }
    
    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  }
}
</style>
