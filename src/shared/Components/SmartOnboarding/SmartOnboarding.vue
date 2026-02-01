<template>
  <div class="smart-onboarding" :class="{ 'mobile': isMobile }">
    
    <!-- ============================================ -->
    <!-- ACHIEVEMENT POPUP -->
    <!-- ============================================ -->
    <Transition name="achievement-slide">
      <div v-if="pendingAchievement" class="achievement-popup" @click="dismissAchievement">
        <div class="achievement-glow"></div>
        <div class="achievement-content">
          <span class="achievement-icon">{{ pendingAchievement.achievement?.icon || '🎉' }}</span>
          <div class="achievement-info">
            <span class="achievement-label">
              {{ pendingAchievement.type === 'level_up' ? 'Level Up!' : 'Conquista Desbloqueada!' }}
            </span>
            <h4 class="achievement-name">
              {{ pendingAchievement.achievement?.name || pendingAchievement.level?.name }}
            </h4>
          </div>
          <span class="achievement-xp">+{{ pendingAchievement.xp }} XP</span>
        </div>
      </div>
    </Transition>
    
    <!-- ============================================ -->
    <!-- MAIN LAYOUT - Split View -->
    <!-- ============================================ -->
    <div class="onboarding-layout">
      
      <!-- LEFT: Chat Section -->
      <section class="chat-section">
        
        <!-- Header -->
        <header class="chat-header">
          <div class="header-left">
            <div class="assistant-avatar">
              <span class="avatar-emoji">🚀</span>
              <span class="avatar-status" :class="{ 'is-typing': isTyping }"></span>
            </div>
            <div class="assistant-info">
              <h3 class="assistant-name">Assistente Unli</h3>
              <span class="assistant-status">{{ isTyping ? 'digitando...' : 'Online' }}</span>
            </div>
          </div>
          
          <div class="header-center">
            <div class="step-badge">
              <span class="step-icon">{{ currentStep?.icon }}</span>
              <span class="step-name">{{ currentStep?.name }}</span>
              <span class="step-number">{{ currentStepIndex + 1 }}/{{ totalSteps }}</span>
            </div>
          </div>
          
          <div class="header-right">
            <button 
              v-if="!isMobile"
              class="btn-toggle-panel"
              @click="showFormPanel = !showFormPanel"
              :title="showFormPanel ? 'Ocultar formulário' : 'Ver formulário'"
            >
              {{ showFormPanel ? '◀ Ocultar' : 'Ver Formulário ▶' }}
            </button>
          </div>
        </header>
        
        <!-- Messages Area -->
        <div class="chat-messages" ref="messagesContainer">
          <TransitionGroup name="message-fade">
            <ChatBubble
              v-for="msg in messages"
              :key="msg.id"
              :message="msg"
              @action="handleMessageAction"
              @suggestion-click="handleSuggestionClick"
              @color-palette-click="handleColorPaletteClick"
            />
          </TransitionGroup>
          
          <!-- Typing Indicator -->
          <div v-if="isTyping" class="typing-indicator">
            <div class="typing-avatar">🤖</div>
            <div class="typing-bubble">
              <div class="typing-dots">
                <span></span>
                <span></span>
                <span></span>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Input Area -->
        <footer class="chat-input-area">
          <!-- Quick Suggestions -->
          <div v-if="quickSuggestions.length && !userInput" class="quick-suggestions">
            <button
              v-for="(suggestion, index) in quickSuggestions"
              :key="index"
              class="quick-suggestion-btn"
              @click="sendQuickSuggestion(suggestion)"
            >
              {{ suggestion }}
            </button>
          </div>
          
          <!-- Input Container -->
          <div class="input-container">
            <div class="input-wrapper">
              <textarea
                ref="inputTextarea"
                v-model="userInput"
                @keydown.enter.exact.prevent="sendMessage"
                @input="autoResize"
                placeholder="Digite sua mensagem..."
                rows="1"
                :disabled="isProcessing"
              />
              
              <!-- Image Upload (quando relevante) -->
              <div v-if="showImageUpload" class="image-upload-area">
                <ImageUploadButton
                  :field-id="currentFieldId"
                  @upload="handleImageUpload"
                />
              </div>
            </div>
            
            <button
              class="btn-send"
              @click="sendMessage"
              :disabled="!canSend"
            >
              <span v-if="isProcessing" class="loading-spinner"></span>
              <span v-else>➤</span>
            </button>
          </div>
          
          <!-- Progress Bar (Mobile) -->
          <div v-if="isMobile" class="mobile-progress">
            <div class="progress-bar">
              <div class="progress-fill" :style="{ width: overallProgress + '%' }"></div>
            </div>
            <span class="progress-text">{{ overallProgress }}% completo</span>
          </div>
        </footer>
        
      </section>
      
      <!-- RIGHT: Form Panel -->
      <aside v-if="showFormPanel && !isMobile" class="form-panel-wrapper">
        <FormPanel
          :form-data="formData"
          :field-status="fieldStatus"
          :current-step="currentStep"
          :current-step-index="currentStepIndex"
          :shimmering-fields="shimmeringFields"
          :recently-filled="recentlyFilledFields"
          @field-click="handleFieldClick"
          @field-edit="handleFieldEdit"
          @step-click="goToStep"
          @image-upload="handleFormImageUpload"
        />
        
        <!-- XP Bar (Fixed at bottom) -->
        <XPBar
          :xp="xp"
          :level="currentLevel"
          :progress="levelProgress"
          :next-level="nextLevel"
        />
      </aside>
      
    </div>
    
    <!-- ============================================ -->
    <!-- MOBILE: Floating Form Button -->
    <!-- ============================================ -->
    <button
      v-if="isMobile"
      class="mobile-form-fab"
      @click="showMobileSheet = true"
    >
      📋 {{ overallProgress }}%
    </button>
    
    <!-- Mobile Bottom Sheet -->
    <Transition name="sheet-slide">
      <div v-if="showMobileSheet" class="mobile-sheet-overlay" @click.self="showMobileSheet = false">
        <div class="mobile-sheet">
          <div class="sheet-handle" @click="showMobileSheet = false"></div>
          <FormPanel
            :form-data="formData"
            :field-status="fieldStatus"
            :current-step="currentStep"
            :current-step-index="currentStepIndex"
            :shimmering-fields="shimmeringFields"
            :recently-filled="recentlyFilledFields"
            @field-click="handleFieldClick"
            @field-edit="handleFieldEdit"
          />
        </div>
      </div>
    </Transition>
    
  </div>
</template>

<script>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import { useOnboardingState } from './composables/useOnboardingState.js';
import { useGeminiBrain } from './composables/useGeminiBrain.js';
import { soundManager } from './composables/useHelpers.js';
import ChatBubble from './components/ChatBubble.vue';
import FormPanel from './components/FormPanel.vue';
import XPBar from './components/XPBar.vue';
import ImageUploadButton from './components/ImageUploadButton.vue';

export default {
  name: 'SmartOnboarding',
  
  components: {
    ChatBubble,
    FormPanel,
    XPBar,
    ImageUploadButton
  },
  
  props: {
    sessionId: {
      type: String,
      default: null
    },
    initialData: {
      type: Object,
      default: () => ({})
    },
    fieldChecklist: {
      type: Object,
      default: () => ({})
    },
    purchasedPages: {
      type: Array,
      default: () => []
    },
    orderId: {
      type: [String, Number],
      default: null
    }
  },
  
  emits: ['complete', 'save', 'error'],
  
  setup(props) {
    // ============================================
    // COMPOSABLES
    // ============================================
    
    const {
      state,
      currentStep,
      currentStepId,
      nextFieldToAsk,
      currentLevel,
      levelProgress,
      nextLevel,
      overallProgress,
      totalSteps,
      initSession,
      updateField,
      dismissAchievementPopup,
      goToStep,
      ONBOARDING_STEPS
    } = useOnboardingState();
    
    const {
      processUserMessage,
      handleStepConfirmation,
      acceptSuggestion,
      acceptColorPalette,
      startChat
    } = useGeminiBrain();
    
    // ============================================
    // REFS
    // ============================================
    
    const messagesContainer = ref(null);
    const inputTextarea = ref(null);
    const userInput = ref('');
    const showFormPanel = ref(true);
    const showMobileSheet = ref(false);
    
    // ============================================
    // COMPUTED
    // ============================================
    
    const isMobile = computed(() => {
      if (typeof window === 'undefined') return false;
      return window.innerWidth < 1024;
    });
    
    const messages = computed(() => state.messages);
    const formData = computed(() => state.formData);
    const fieldStatus = computed(() => state.fieldStatus);
    const isTyping = computed(() => state.isTyping);
    const isProcessing = computed(() => state.isProcessing);
    const pendingAchievement = computed(() => state.pendingAchievement);
    const shimmeringFields = computed(() => state.shimmeringFields);
    const recentlyFilledFields = computed(() => state.recentlyFilledFields);
    const currentStepIndex = computed(() => state.currentStepIndex);
    const xp = computed(() => state.xp);
    
    const canSend = computed(() => {
      return userInput.value.trim().length > 0 && !isProcessing.value;
    });
    
    const currentFieldId = computed(() => nextFieldToAsk.value?.id || null);
    
    const showImageUpload = computed(() => {
      const field = nextFieldToAsk.value;
      return field && (field.type === 'image' || field.id === 'logo' || field.id === 'aboutImage');
    });
    
    // Sugestões rápidas baseadas no contexto
    const quickSuggestions = computed(() => {
      const stepId = currentStepId.value;
      const fieldId = currentFieldId.value;
      
      const suggestions = {
        identity: {
          frase: ['Me ajude a criar uma frase', 'Não tenho slogan ainda'],
          primaryColor: ['Sugira cores para meu ramo', 'Quero cores profissionais'],
          logo: ['Não tenho logo ainda', 'Prefiro texto estilizado']
        },
        contact: {
          hasPhysicalLocation: ['Não tenho endereço físico', 'Trabalho apenas online'],
          instagram: ['Não tenho Instagram', 'Ainda não tenho redes sociais']
        },
        about: {
          companyBio: ['Me ajude a escrever', 'Prefiro pular por enquanto']
        }
      };
      
      return suggestions[stepId]?.[fieldId] || [];
    });
    
    // ============================================
    // METHODS
    // ============================================
    
    async function sendMessage() {
      const content = userInput.value.trim();
      if (!content || isProcessing.value) return;
      
      userInput.value = '';
      resetTextareaHeight();
      
      soundManager.play('send');
      
      await processUserMessage(content);
      scrollToBottom();
    }
    
    function sendQuickSuggestion(text) {
      userInput.value = text;
      sendMessage();
    }
    
    function handleMessageAction(action) {
      if (action.id === 'confirm') {
        handleStepConfirmation();
        soundManager.play('stepComplete');
      } else if (action.id === 'edit') {
        // Foca no campo para editar
        showFormPanel.value = true;
      }
    }
    
    function handleSuggestionClick(suggestion) {
      acceptSuggestion(suggestion);
      soundManager.play('click');
    }
    
    function handleColorPaletteClick(palette) {
      acceptColorPalette(palette);
      soundManager.play('click');
    }
    
    function handleFieldClick(fieldId) {
      // Scroll para o campo no chat ou perguntar sobre ele
      console.log('[Field Click]', fieldId);
    }
    
    function handleFieldEdit(fieldId, value) {
      updateField(fieldId, value);
      soundManager.play('fieldComplete');
    }
    
    function handleImageUpload(data) {
      const { fieldId, url } = data;
      updateField(fieldId, url);
      processUserMessage(`Enviei uma imagem para ${fieldId}`);
    }
    
    function handleFormImageUpload(data) {
      handleImageUpload(data);
    }
    
    function dismissAchievement() {
      dismissAchievementPopup();
    }
    
    function autoResize() {
      const textarea = inputTextarea.value;
      if (textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
      }
    }
    
    function resetTextareaHeight() {
      const textarea = inputTextarea.value;
      if (textarea) {
        textarea.style.height = 'auto';
      }
    }
    
    function scrollToBottom() {
      nextTick(() => {
        const container = messagesContainer.value;
        if (container) {
          container.scrollTo({
            top: container.scrollHeight,
            behavior: 'smooth'
          });
        }
      });
    }
    
    // ============================================
    // LIFECYCLE
    // ============================================
    
    onMounted(() => {
      console.log('[SmartOnboarding] Componente montado!');
      console.log('[SmartOnboarding] SessionId:', props.sessionId);
      console.log('[SmartOnboarding] InitialData:', props.initialData);
      console.log('[SmartOnboarding] FieldChecklist:', props.fieldChecklist);
      console.log('[SmartOnboarding] PurchasedPages:', props.purchasedPages);
      console.log('[SmartOnboarding] OrderId:', props.orderId);
      
      // Pré-carregar sons
      soundManager.preload();
      
      // Inicializar sessão com dados do backend
      initSession(props.sessionId, props.initialData, props.fieldChecklist);
      
      // Iniciar chat com mensagem de boas-vindas
      nextTick(() => {
        console.log('[SmartOnboarding] Iniciando chat...');
        startChat();
        scrollToBottom();
      });
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
      showFormPanel,
      showMobileSheet,
      
      // Computed
      isMobile,
      messages,
      formData,
      fieldStatus,
      isTyping,
      isProcessing,
      pendingAchievement,
      shimmeringFields,
      recentlyFilledFields,
      currentStep,
      currentStepIndex,
      currentFieldId,
      totalSteps,
      overallProgress,
      currentLevel,
      levelProgress,
      nextLevel,
      xp,
      canSend,
      showImageUpload,
      quickSuggestions,
      
      // Methods
      sendMessage,
      sendQuickSuggestion,
      handleMessageAction,
      handleSuggestionClick,
      handleColorPaletteClick,
      handleFieldClick,
      handleFieldEdit,
      handleImageUpload,
      handleFormImageUpload,
      dismissAchievement,
      autoResize,
      goToStep,
      
      // Constants
      ONBOARDING_STEPS
    };
  }
};
</script>

<style lang="scss" scoped>
// ============================================
// VARIABLES
// ============================================

$primary: #6C5CE7;
$primary-light: #A29BFE;
$secondary: #00CEC9;
$background-dark: #0a0a1a;
$background-card: #1a1a2e;
$text-primary: #ffffff;
$text-secondary: rgba(255, 255, 255, 0.7);
$border-color: rgba(255, 255, 255, 0.1);
$success: #00B894;
$warning: #FDCB6E;
$error: #E17055;

// ============================================
// MAIN CONTAINER
// ============================================

.smart-onboarding {
  min-height: calc(100vh - 70px);
  background: linear-gradient(135deg, $background-dark 0%, #1a1a3a 100%);
  color: $text-primary;
  
  &.mobile {
    .onboarding-layout {
      flex-direction: column;
    }
    
    .chat-section {
      max-width: 100%;
      height: 100vh;
    }
  }
}

.onboarding-layout {
    display: flex;
    height: calc(100vh - 70px);
    max-height: calc(100vh - 70px);
    margin-top: 70px;
    padding: 0;
    gap: 0;
    overflow: hidden;
}

// ============================================
// CHAT SECTION
// ============================================

.chat-section {
  flex: 1;
  max-width: 65%;
  display: flex;
  flex-direction: column;
  background: rgba(0, 0, 0, 0.15);
  height: 100%;
  overflow: hidden;
}

.chat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  background: rgba(0, 0, 0, 0.3);
  border-bottom: 1px solid $border-color;
  
  .header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }
  
  .assistant-avatar {
    position: relative;
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, $primary 0%, $secondary 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    
    .avatar-emoji {
      font-size: 1.25rem;
    }
    
    .avatar-status {
      position: absolute;
      bottom: 2px;
      right: 2px;
      width: 10px;
      height: 10px;
      background: $success;
      border-radius: 50%;
      border: 2px solid $background-dark;
      
      &.is-typing {
        animation: pulse 1s infinite;
      }
    }
  }
  
  .assistant-info {
    .assistant-name {
      font-size: 1rem;
      font-weight: 600;
      margin: 0;
    }
    
    .assistant-status {
      font-size: 0.75rem;
      color: $text-secondary;
    }
  }
  
  .step-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: rgba($primary, 0.2);
    border-radius: 2rem;
    
    .step-icon {
      font-size: 1.25rem;
    }
    
    .step-name {
      font-weight: 500;
    }
    
    .step-number {
      font-size: 0.75rem;
      color: $text-secondary;
    }
  }
  
  .btn-toggle-panel {
    padding: 0.5rem 1rem;
    background: rgba($primary, 0.2);
    border: 1px solid rgba($primary, 0.3);
    border-radius: 0.5rem;
    color: $text-primary;
    cursor: pointer;
    transition: all 0.2s;
    
    &:hover {
      background: rgba($primary, 0.3);
    }
  }
}

// ============================================
// MESSAGES AREA
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
    background: rgba($primary, 0.3);
    border-radius: 3px;
  }
}

// ============================================
// TYPING INDICATOR
// ============================================

.typing-indicator {
  display: flex;
  align-items: flex-end;
  gap: 0.5rem;
  
  .typing-avatar {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, $primary 0%, $secondary 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
  }
  
  .typing-bubble {
    background: $background-card;
    padding: 1rem 1.25rem;
    border-radius: 1rem 1rem 1rem 0.25rem;
  }
  
  .typing-dots {
    display: flex;
    gap: 4px;
    
    span {
      width: 8px;
      height: 8px;
      background: $text-secondary;
      border-radius: 50%;
      animation: typingDot 1.4s infinite ease-in-out;
      
      &:nth-child(2) {
        animation-delay: 0.2s;
      }
      
      &:nth-child(3) {
        animation-delay: 0.4s;
      }
    }
  }
}

@keyframes typingDot {
  0%, 60%, 100% {
    transform: translateY(0);
    opacity: 0.5;
  }
  30% {
    transform: translateY(-6px);
    opacity: 1;
  }
}

// ============================================
// INPUT AREA
// ============================================

.chat-input-area {
  padding: 1rem 1.5rem;
  background: rgba(0, 0, 0, 0.3);
  border-top: 1px solid $border-color;
}

.quick-suggestions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
  
  .quick-suggestion-btn {
    padding: 0.5rem 1rem;
    background: rgba($primary, 0.15);
    border: 1px solid rgba($primary, 0.3);
    border-radius: 2rem;
    color: $text-primary;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    
    &:hover {
      background: rgba($primary, 0.25);
      transform: translateY(-1px);
    }
  }
}

.input-container {
  display: flex;
  gap: 0.75rem;
  align-items: flex-end;
}

.input-wrapper {
  flex: 1;
  background: $background-card;
  border-radius: 1.5rem;
  padding: 0.75rem 1.25rem;
  border: 1px solid $border-color;
  transition: border-color 0.2s;
  
  &:focus-within {
    border-color: $primary;
  }
  
  textarea {
    width: 100%;
    background: transparent;
    border: none;
    color: $text-primary;
    font-size: 1rem;
    resize: none;
    outline: none;
    line-height: 1.5;
    max-height: 120px;
    
    &::placeholder {
      color: $text-secondary;
    }
    
    &:disabled {
      opacity: 0.5;
    }
  }
}

.btn-send {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, $primary 0%, $secondary 100%);
  border: none;
  border-radius: 50%;
  color: white;
  font-size: 1.25rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  
  &:hover:not(:disabled) {
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba($primary, 0.4);
  }
  
  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
  
  .loading-spinner {
    width: 20px;
    height: 20px;
    border: 2px solid transparent;
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
  }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

// ============================================
// FORM PANEL WRAPPER (Project Dashboard)
// ============================================

.form-panel-wrapper {
  min-width: 340px;
  width: 35%;
  display: flex;
  flex-direction: column;
  background: linear-gradient(180deg, #0f0f1a 0%, #0a0a15 100%);
  border-left: 1px solid rgba(255, 255, 255, 0.08);
  overflow: hidden;
  height: 100%;
  
  // FormPanel ocupa o espaço disponível
  > :first-child {
    flex: 1;
    min-height: 0;
    overflow: hidden;
  }
  
  // XPBar fica fixo no final
  > :last-child {
    flex-shrink: 0;
  }
}

// ============================================
// ACHIEVEMENT POPUP
// ============================================

.achievement-popup {
  position: fixed;
  top: 10rem;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1000;
  background: linear-gradient(135deg, $primary 0%, $secondary 100%);
  padding: 1rem 2rem;
  border-radius: 1rem;
  box-shadow: 0 10px 40px rgba($primary, 0.4);
  cursor: pointer;
  
  .achievement-content {
    display: flex;
    align-items: center;
    gap: 1rem;
  }
  
  .achievement-glow {
    position: absolute;
    inset: -2px;
    background: linear-gradient(135deg, $primary, $secondary, $primary);
    border-radius: 1rem;
    z-index: -1;
    animation: glowPulse 2s infinite;
  }
  
  .achievement-icon {
    font-size: 2rem;
  }
  
  .achievement-info {
    .achievement-label {
      display: block;
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      opacity: 0.9;
    }
    
    .achievement-name {
      font-size: 1.125rem;
      font-weight: 600;
      margin: 0.25rem 0 0;
    }
  }
  
  .achievement-xp {
    font-size: 1.25rem;
    font-weight: 700;
    color: $warning;
  }
}

@keyframes glowPulse {
  0%, 100% { opacity: 0.5; }
  50% { opacity: 1; }
}

// ============================================
// MOBILE STYLES
// ============================================

.mobile-form-fab {
  position: fixed;
  bottom: 5rem;
  right: 1rem;
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, $primary 0%, $secondary 100%);
  border: none;
  border-radius: 50%;
  color: white;
  font-size: 0.875rem;
  font-weight: 600;
  box-shadow: 0 4px 15px rgba($primary, 0.4);
  cursor: pointer;
  z-index: 100;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.125rem;
}

.mobile-sheet-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  z-index: 200;
  display: flex;
  align-items: flex-end;
}

.mobile-sheet {
  width: 100%;
  max-height: 80vh;
  background: $background-card;
  border-radius: 1.5rem 1.5rem 0 0;
  overflow: hidden;
  
  .sheet-handle {
    width: 40px;
    height: 4px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 2px;
    margin: 0.75rem auto;
    cursor: pointer;
  }
}

.mobile-progress {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-top: 0.75rem;
  
  .progress-bar {
    flex: 1;
    height: 6px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 3px;
    overflow: hidden;
    
    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, $primary, $secondary);
      border-radius: 3px;
      transition: width 0.3s ease;
    }
  }
  
  .progress-text {
    font-size: 0.75rem;
    color: $text-secondary;
    white-space: nowrap;
  }
}

// ============================================
// TRANSITIONS
// ============================================

.message-fade-enter-active {
  transition: all 0.3s ease;
}

.message-fade-leave-active {
  transition: all 0.2s ease;
}

.message-fade-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.message-fade-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}

.achievement-slide-enter-active {
  transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.achievement-slide-leave-active {
  transition: all 0.3s ease;
}

.achievement-slide-enter-from {
  opacity: 0;
  transform: translate(-50%, -100px);
}

.achievement-slide-leave-to {
  opacity: 0;
  transform: translate(-50%, -50px);
}

.sheet-slide-enter-active {
  transition: all 0.3s ease;
}

.sheet-slide-leave-active {
  transition: all 0.2s ease;
}

.sheet-slide-enter-from,
.sheet-slide-leave-to {
  .mobile-sheet {
    transform: translateY(100%);
  }
}

// ============================================
// ANIMATIONS
// ============================================

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
</style>
