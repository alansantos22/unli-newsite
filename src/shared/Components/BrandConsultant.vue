<template>
  <div class="brand-consultant">
    <!-- Fundo Animado -->
    <div class="ambient-background"></div>

    <!-- Header com Progress Tracker -->
    <div class="consultant-header glass-effect">
      <div class="header-content">
        <div class="consultant-info">
          <div class="avatar-ring">
            <div class="avatar-icon"><i class="fas fa-cube"></i></div>
          </div>
          <div class="text-container">
            <h2 class="consultant-name">Unli <span class="badge">AI Architect</span></h2>
            <div class="status-text">
              <span class="pulse-dot"></span> Construindo seu projeto
            </div>
          </div>
          <!-- Botão de Nova Conversa -->
          <button 
            v-if="hasRestoredSession || chatHistory.length > 1"
            class="btn-new-conversation"
            @click="startNewConversation"
            title="Iniciar nova conversa"
          >
            <i class="fas fa-redo"></i>
          </button>
        </div>

        <div class="progress-tracker">
          <div 
            v-for="(step, index) in steps" 
            :key="index"
            class="step-item"
            :class="{ 'active': currentStep === index, 'completed': currentStep > index }"
          >
            <div class="step-dot"></div>
            <span class="step-label">{{ step }}</span>
          </div>
          <div class="progress-line">
            <div class="progress-fill" :style="{ width: progressPercentage + '%' }"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Área do Chat -->
    <div class="chat-container" ref="chatContainer">
      <transition-group name="message-slide" tag="div" class="chat-messages">
        <!-- Mensagens -->
        <div 
          v-for="(message, index) in chatHistory" 
          :key="'msg-' + index"
          :class="['message-row', message.role]"
        >
          <div class="message-bubble glass-panel">
            <div class="message-content" v-html="formatMessage(message.content)"></div>
          </div>
          <div v-if="message.role === 'assistant'" class="bot-mini-avatar">
            <i class="fas fa-cube"></i>
          </div>
        </div>

        <!-- Indicador de Digitação -->
        <div v-if="isLoading || isTyping" key="loading" class="message-row assistant">
          <div class="message-bubble glass-panel typing">
            <span class="dot"></span><span class="dot"></span><span class="dot"></span>
          </div>
          <div class="bot-mini-avatar">
            <i class="fas fa-cube"></i>
          </div>
        </div>
      </transition-group>
    </div>

    <!-- Input Flutuante -->
    <div class="input-area-container">
      <div class="input-area glass-effect">
        <textarea 
          ref="messageInput"
          v-model="userInput" 
          @keydown.enter.exact="handleEnterKey"
          @input="autoResize"
          placeholder="Digite sua resposta..."
          :disabled="isLoading || isExtracting || conversationFinished || isTyping"
          rows="1"
        ></textarea>
        <button 
          class="btn-send" 
          @click="sendMessage" 
          :disabled="!userInput.trim() || isLoading || isExtracting || conversationFinished || isTyping"
          :class="{ 'ready': userInput.trim().length > 0 }"
        >
          <i class="fas fa-arrow-up"></i>
        </button>
      </div>
      
      <transition name="fade">
        <button 
          v-if="canFinish && !conversationFinished"
          class="btn-finish-floating"
          :disabled="isLoading || isExtracting || isTyping"
          @click="finishConversation"
        >
          <i class="fas fa-check"></i> Finalizar Briefing
        </button>
      </transition>
    </div>

    <!-- Modal de Extração/Resultados -->
    <transition name="modal-fade">
      <div v-if="showResultsModal" class="results-modal-overlay" @click.self="closeResultsModal">
        <div class="results-modal">
          <!-- Loading State -->
          <div v-if="isExtracting" class="extraction-loading">
            <div class="loading-animation">
              <div class="pulse-ring"></div>
              <div class="brain-icon">🧠</div>
            </div>
            <h3>Analisando nossa conversa...</h3>
            <p>Estou extraindo os insights sobre sua marca</p>
            <div class="progress-bar-extraction">
              <div class="progress-fill" :style="{ width: extractionProgress + '%' }"></div>
            </div>
          </div>

          <!-- Results State -->
          <div v-else-if="extractedData" class="extraction-results">
            <div class="results-header">
              <div class="results-icon">✨</div>
              <h3>Perfil da Marca Extraído!</h3>
              <p>Revise os dados capturados da nossa conversa</p>
              <div 
                class="confidence-badge"
                :class="extractedData.extractionMeta?.confidence || 'medium'"
              >
                <i class="fas fa-chart-line"></i>
                Confiança: {{ getConfidenceLabel(extractedData.extractionMeta?.confidence) }}
              </div>
              
              <!-- Warning de Confidence -->
              <div v-if="confidenceWarning" class="confidence-warning">
                <i class="fas fa-exclamation-triangle"></i>
                {{ confidenceWarning }}
              </div>
            </div>

            <div class="results-sections">
              <!-- Informações da Empresa -->
              <div class="result-section">
                <h4><i class="fas fa-building"></i> Informações da Empresa</h4>
                <div class="result-grid">
                  <div class="result-item" v-if="extractedData.companyInfo?.name">
                    <label>Nome</label>
                    <span>{{ extractedData.companyInfo.name }}</span>
                  </div>
                  <div class="result-item">
                    <label>Nicho</label>
                    <span>{{ extractedData.companyInfo?.niche || 'Não identificado' }}</span>
                  </div>
                  <div class="result-item full-width">
                    <label>Público-Alvo</label>
                    <span>{{ extractedData.companyInfo?.targetAudience || 'Não identificado' }}</span>
                  </div>
                </div>
              </div>

              <!-- Essência da Marca -->
              <div class="result-section">
                <h4><i class="fas fa-heart"></i> Essência da Marca</h4>
                <div class="result-grid">
                  <div class="result-item full-width">
                    <label>História</label>
                    <span>{{ extractedData.brandCore?.historySummary || 'Não identificado' }}</span>
                  </div>
                  <div class="result-item full-width">
                    <label>Missão</label>
                    <span class="highlight">{{ extractedData.brandCore?.mission || 'Não identificado' }}</span>
                  </div>
                  <div class="result-item full-width" v-if="extractedData.brandCore?.values?.length">
                    <label>Valores</label>
                    <div class="tags">
                      <span 
                        v-for="(value, i) in extractedData.brandCore.values" 
                        :key="i"
                        class="tag"
                      >{{ value }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Gatilhos de Autoridade -->
              <div class="result-section" v-if="hasAuthorityData">
                <h4><i class="fas fa-award"></i> Gatilhos de Autoridade</h4>
                <div class="result-grid">
                  <div class="result-item" v-if="extractedData.authorityTriggers?.yearsInMarket">
                    <label>Tempo de Mercado</label>
                    <span>{{ extractedData.authorityTriggers.yearsInMarket }}</span>
                  </div>
                  <div class="result-item full-width" v-if="extractedData.authorityTriggers?.keyAchievements">
                    <label>Principais Conquistas</label>
                    <span>{{ extractedData.authorityTriggers.keyAchievements }}</span>
                  </div>
                </div>
              </div>

              <!-- Diferenciação -->
              <div class="result-section">
                <h4><i class="fas fa-gem"></i> Diferenciação</h4>
                <div class="result-grid">
                  <div class="result-item full-width">
                    <label>Proposta Única de Valor (USP)</label>
                    <span class="highlight">{{ extractedData.differentiation?.usp || 'Não identificado' }}</span>
                  </div>
                  <div class="result-item full-width" v-if="extractedData.differentiation?.uniqueMethodology">
                    <label>Metodologia Própria</label>
                    <span>{{ extractedData.differentiation.uniqueMethodology }}</span>
                  </div>
                </div>
              </div>

              <!-- 🎨 NOVA SEÇÃO: Identidade Visual -->
              <div class="result-section visual-identity">
                <h4><i class="fas fa-palette"></i> Visão Criativa do Site</h4>
                
                <!-- Card de Preview Visual -->
                <div class="visual-preview-card">
                  <div class="visual-header">
                    <span class="style-badge">{{ extractedData.visualIdentity?.suggestedStyle || 'Moderno' }}</span>
                  </div>
                  
                  <div class="color-preview" v-if="extractedData.visualIdentity?.colorVibe">
                    <label>Paleta de Cores</label>
                    <p class="color-description">{{ extractedData.visualIdentity.colorVibe }}</p>
                    <div class="color-swatches" v-if="extractedData.visualIdentity.primaryColorSuggestion">
                      <div 
                        class="swatch primary"
                        :style="{ backgroundColor: getColorValue(extractedData.visualIdentity.primaryColorSuggestion) }"
                        :title="extractedData.visualIdentity.primaryColorSuggestion"
                      ></div>
                      <div 
                        v-if="extractedData.visualIdentity.secondaryColorSuggestion"
                        class="swatch secondary"
                        :style="{ backgroundColor: getColorValue(extractedData.visualIdentity.secondaryColorSuggestion) }"
                        :title="extractedData.visualIdentity.secondaryColorSuggestion"
                      ></div>
                    </div>
                  </div>

                  <div class="result-grid">
                    <div class="result-item" v-if="extractedData.visualIdentity?.typographyMood">
                      <label><i class="fas fa-font"></i> Tipografia</label>
                      <span>{{ extractedData.visualIdentity.typographyMood }}</span>
                    </div>
                    <div class="result-item" v-if="extractedData.visualIdentity?.layoutSuggestion">
                      <label><i class="fas fa-th-large"></i> Layout</label>
                      <span>{{ extractedData.visualIdentity.layoutSuggestion }}</span>
                    </div>
                  </div>

                  <div class="result-item full-width" v-if="extractedData.visualIdentity?.heroSuggestion">
                    <label><i class="fas fa-image"></i> Hero Section</label>
                    <span>{{ extractedData.visualIdentity.heroSuggestion }}</span>
                  </div>

                  <div class="mood-keywords" v-if="extractedData.visualIdentity?.moodKeywords?.length">
                    <label>Mood</label>
                    <div class="keywords-list">
                      <span 
                        v-for="(keyword, i) in extractedData.visualIdentity.moodKeywords" 
                        :key="i"
                        class="mood-tag"
                      >{{ keyword }}</span>
                    </div>
                  </div>

                  <div class="special-sections" v-if="extractedData.visualIdentity?.specialSections?.length">
                    <label>Seções Recomendadas</label>
                    <ul>
                      <li v-for="(section, i) in extractedData.visualIdentity.specialSections" :key="i">
                        <i class="fas fa-check-circle"></i> {{ section }}
                      </li>
                    </ul>
                  </div>
                </div>
              </div>

              <!-- Sugestões da IA -->
              <div class="result-section ai-suggestions">
                <h4><i class="fas fa-magic"></i> Sugestões da IA</h4>
                <div class="suggestion-card tagline">
                  <label>Tagline Sugerida</label>
                  <span class="tagline-text">"{{ extractedData.aiSuggestions?.suggestedTagline }}"</span>
                </div>
                <div class="result-grid">
                  <div class="result-item">
                    <label>Tom de Voz</label>
                    <span class="tone-badge">{{ extractedData.aiSuggestions?.toneOfVoice }}</span>
                  </div>
                  <div class="result-item" v-if="extractedData.aiSuggestions?.colorPaletteSuggestion">
                    <label>Paleta Sugerida</label>
                    <span>{{ extractedData.aiSuggestions.colorPaletteSuggestion }}</span>
                  </div>
                </div>
                <div class="result-item full-width" v-if="extractedData.aiSuggestions?.suggestedKeywords?.length">
                  <label>Keywords para SEO</label>
                  <div class="tags keywords">
                    <span 
                      v-for="(keyword, i) in extractedData.aiSuggestions.suggestedKeywords" 
                      :key="i"
                      class="tag"
                    >{{ keyword }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Ações -->
            <div class="results-actions">
              <button class="btn-secondary" @click="closeResultsModal">
                <i class="fas fa-edit"></i>
                Editar Manualmente
              </button>
              <button class="btn-primary" @click="confirmAndProceed">
                <i class="fas fa-check"></i>
                Confirmar e Avançar
              </button>
            </div>
          </div>

          <!-- Error State -->
          <div v-else-if="extractionError" class="extraction-error">
            <div class="error-icon">❌</div>
            <h3>Ops! Algo deu errado</h3>
            <p>{{ extractionError }}</p>
            <button class="btn-primary" @click="retryExtraction">
              <i class="fas fa-redo"></i>
              Tentar Novamente
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import { useBrandConsultant } from '@/core/composables/useBrandConsultant';

export default {
  name: 'BrandConsultant',
  
  props: {
    // Session ID (token do onboarding)
    sessionId: {
      type: String,
      required: true
    },
    // Nome do cliente para personalização
    customerName: {
      type: String,
      default: ''
    },
    // Contexto adicional do pedido
    initialContext: {
      type: Object,
      default: () => ({})
    },
    // Dados iniciais (se houver)
    initialData: {
      type: Object,
      default: () => ({})
    },
    // API Base URL
    apiBaseUrl: {
      type: String,
      default: '/api/ai'
    }
  },
  
  emits: ['data-extracted', 'conversation-saved'],

  data() {
    return {
      // Chat State
      chatHistory: [],
      userInput: '',
      isLoading: false,
      isTyping: false, // Novo: controla o delay de digitação
      conversationFinished: false,
      hasRestoredSession: false,

      // Progress Tracker (Gamificação)
      steps: ['Empresa', 'Produto', 'Visual', 'Autoridade', 'Diferencial'],
      typingDelay: 2500, // Delay de 2.5 segundos para simular digitação

      // Extraction State
      showResultsModal: false,
      isExtracting: false,
      extractionProgress: 0,
      extractedData: null,
      extractionError: null,

      // System Prompt (carregado do backend ou inline)
      systemPrompt: `Você é o Unli, um especialista sênior em Arquitetura de Informação e Design de Interfaces. Sua postura é profissional, objetiva e prestativa. Você não usa gírias. Você transmite segurança técnica.

REGRAS:
1. Faça UMA pergunta por vez
2. Use escuta ativa - valide o que ouviu antes de perguntar
3. Tom: profissional, cordial e culto. Evite gírias como "Legal!", "Animal!". Use "Compreendo", "Entendido", "Perfeito".
4. NÃO mencione que é IA ou está "coletando dados"
5. Siga o fluxo: Resumo da Empresa → Produto/Público → Identidade Visual → Autoridade → Diferencial
6. Sempre traduza conceitos em soluções visuais concretas

Comece pedindo uma apresentação da empresa: nome, origem e história.`,

      // Composable instance
      consultant: null
    };
  },
  
  // Expose methods for parent component access
  expose: ['saveConversation'],

  computed: {
    inputPlaceholder() {
      if (this.conversationFinished) return 'Conversa finalizada';
      if (this.isLoading || this.isTyping) return 'Aguardando resposta...';
      return 'Digite sua mensagem...';
    },

    // Calcula o step atual baseado no número de mensagens
    currentStep() {
      // A cada mensagem do usuário, avança 1 step
      const userMessages = this.chatHistory.filter(m => m.role === 'user').length;
      return userMessages > 4 ? 4 : userMessages;
    },

    // Porcentagem de progresso para a barra
    progressPercentage() {
      if (this.steps.length <= 1) return 100;
      return (this.currentStep / (this.steps.length - 1)) * 100;
    },

    hasAuthorityData() {
      const auth = this.extractedData?.authorityTriggers;
      return auth && (auth.yearsInMarket || auth.keyAchievements || auth.certifications?.length);
    },

    // Verifica se pode finalizar a conversa (mínimo de interações ou deixa do assistente)
    canFinish() {
      if (this.chatHistory.length < 4) return false;
      
      const lastAssistantMsg = [...this.chatHistory]
        .reverse()
        .find(m => m.role === 'assistant');
      
      if (!lastAssistantMsg) return this.chatHistory.length >= 6;
      
      // Detecta se o assistente deu a deixa de encerramento
      const endPhrases = ['compilar', 'perfil completo', 'informações suficientes', 'informações necessárias', 'obrigado pela conversa', 'vou montar'];
      const lowerContent = lastAssistantMsg.content.toLowerCase();
      const assistantSignaledEnd = endPhrases.some(phrase => lowerContent.includes(phrase));
      
      return this.chatHistory.length >= 6 || assistantSignaledEnd;
    },

    // Verifica se o botão deve pulsar (assistente sinalizou fim)
    shouldPulseFinish() {
      if (this.chatHistory.length < 4) return false;
      
      const lastMsg = this.chatHistory[this.chatHistory.length - 1];
      if (lastMsg?.role !== 'assistant') return false;
      
      const endPhrases = ['compilar', 'perfil completo', 'vou montar', 'informações necessárias'];
      return endPhrases.some(phrase => lastMsg.content.toLowerCase().includes(phrase));
    },

    // Mensagem de alerta baseada na confiança
    confidenceWarning() {
      const confidence = this.extractedData?.extractionMeta?.confidence;
      if (confidence === 'low') {
        return 'Consegui extrair a maioria dos dados, mas dê uma olhadinha nos campos para garantir que peguei tudo certo!';
      }
      if (confidence === 'medium') {
        return 'Alguns campos foram inferidos. Revise para garantir precisão.';
      }
      return null;
    }
  },

  mounted() {
    this.initializeSession();
  },

  beforeUnmount() {
    // Save conversation state before component is destroyed
    this.saveConversation();
    
    // Remove beforeunload listener
    window.removeEventListener('beforeunload', this.handleBeforeUnload);
  },

  methods: {
    // =====================
    // SESSION MANAGEMENT
    // =====================
    
    initializeSession() {
      // Initialize composable with session ID
      this.consultant = useBrandConsultant();
      const restored = this.consultant.initSession(this.sessionId);
      
      if (restored.hasRestoredData && restored.messageCount > 0) {
        // Restore chat history from composable (chatHistory is a computed ref)
        const historyRef = this.consultant.chatHistory;
        // Access .value if it's a computed ref, otherwise use directly
        const history = historyRef?.value !== undefined ? historyRef.value : historyRef;
        this.chatHistory = Array.isArray(history) ? [...history] : [];
        this.hasRestoredSession = true;
        
        // Check if conversation was finished (also computed refs)
        const isFinishedRef = this.consultant.isConversationFinished;
        const isFinished = isFinishedRef?.value !== undefined ? isFinishedRef.value : isFinishedRef;
        
        if (isFinished) {
          this.conversationFinished = true;
          const extractedRef = this.consultant.extractedData;
          this.extractedData = extractedRef?.value !== undefined ? extractedRef.value : extractedRef;
        }
        
        // Scroll to bottom after restoring
        this.$nextTick(() => this.scrollToBottom());
        
        // Show toast that session was restored
        this.showToast('Conversa restaurada! Continue de onde parou.', 'info');
      } else {
        // Start fresh conversation
        this.startConversation();
      }
      
      // Add beforeunload listener to save on page close
      window.addEventListener('beforeunload', this.handleBeforeUnload);
    },
    
    handleBeforeUnload() {
      this.saveConversation();
    },
    
    saveConversation() {
      if (this.consultant) {
        this.consultant.saveConversation();
        this.$emit('conversation-saved', this.chatHistory);
      }
    },

    /**
     * Inicia uma nova conversa, limpando o histórico
     */
    startNewConversation() {
      if (confirm('Tem certeza que deseja iniciar uma nova conversa? O histórico atual será perdido.')) {
        // Limpar localStorage
        if (this.consultant && this.consultant.startSession) {
          this.consultant.startSession();
        }
        
        // Limpar estado local
        this.chatHistory = [];
        this.conversationFinished = false;
        this.hasRestoredSession = false;
        this.extractedData = null;
        this.isTyping = false;
        this.isLoading = false;
        
        // Limpar localStorage manualmente também
        this.clearLocalStorageForSession();
        
        // Iniciar nova conversa
        this.startConversation();
        
        this.showToast('Nova conversa iniciada!', 'success');
      }
    },

    /**
     * Limpa o localStorage para a sessão atual
     */
    clearLocalStorageForSession() {
      try {
        const keysToRemove = [];
        for (let i = 0; i < localStorage.length; i++) {
          const key = localStorage.key(i);
          if (key && (key.includes('unli_consultant_') || key.includes('unli_chat_history_'))) {
            keysToRemove.push(key);
          }
        }
        keysToRemove.forEach(key => localStorage.removeItem(key));
      } catch (err) {
        console.warn('Failed to clear localStorage:', err);
      }
    },

    // =====================
    // CHAT METHODS
    // =====================
    
    async startConversation() {
      // Adicionar mensagem inicial do assistente com delay de digitação
      this.isTyping = true;
      this.$nextTick(() => this.scrollToBottom());
      
      // Personalizar saudação se tiver nome do cliente
      const customerGreeting = this.customerName 
        ? `, ${this.customerName.split(' ')[0]}`
        : '';
      
      // Mensagem inicial fixa para garantir consistência
      // A API será chamada apenas a partir da primeira resposta do usuário
      const initialMessage = `Olá${customerGreeting}! Que bom te receber por aqui. Sou o Assistente Unli e vou conduzir a construção da identidade digital do seu negócio.

    Para começar, me conta um pouco sobre a sua empresa, o que vocês fazem, quem atendem e como essa história começou. Se tiver algo que considere um diferencial importante, também vale compartilhar.

    Com esses detalhes, consigo montar uma base mais precisa para o seu site.`;
      
      // Simula delay de digitação (2.5 segundos)
      await this.simulateTypingDelay();
      
      this.isTyping = false;
      this.addMessage('assistant', initialMessage);
    },

    /**
     * Simula delay de digitação para dar sensação de que a IA está pensando
     */
    simulateTypingDelay() {
      return new Promise(resolve => {
        setTimeout(resolve, this.typingDelay);
      });
    },

    async sendMessage() {
      if (!this.userInput.trim() || this.isLoading || this.conversationFinished || this.isTyping) return;

      const message = this.userInput.trim();
      this.userInput = '';
      this.resetTextareaHeight();
      
      // Adicionar mensagem do usuário
      this.addMessage('user', message);
      
      // Scroll para baixo
      this.$nextTick(() => this.scrollToBottom());

      // Obter resposta do assistente com delay de digitação
      this.isLoading = true;
      
      try {
        const response = await this.callChatAPI(this.chatHistory);
        
        // Muda para o estado de "digitando"
        this.isLoading = false;
        this.isTyping = true;
        this.$nextTick(() => this.scrollToBottom());
        
        // Simula delay de digitação (2.5 segundos)
        await this.simulateTypingDelay();
        
        this.isTyping = false;
        this.addMessage('assistant', response);
        
        // Verificar se a conversa deve terminar (IA encerrou)
        if (this.shouldEndConversation(response)) {
          this.conversationFinished = true;
          setTimeout(() => this.startExtraction(), 1500);
        }
      } catch (error) {
        console.error('Erro ao enviar mensagem:', error);
        this.isLoading = false;
        this.isTyping = false;
        this.addMessage('assistant', 'Desculpe, tive um problema ao processar sua resposta. Pode repetir?');
      } finally {
        this.$nextTick(() => this.scrollToBottom());
      }
    },

    addMessage(role, content) {
      const message = {
        role,
        content,
        timestamp: new Date().toISOString()
      };
      
      this.chatHistory.push(message);
      
      // Sync with composable for persistence
      if (this.consultant) {
        this.consultant.addMessage(role, content);
      }
    },

    async callChatAPI(history) {
      // Formatar histórico para a API
      const messages = [
        { role: 'system', content: this.systemPrompt },
        ...history.map(msg => ({
          role: msg.role === 'assistant' ? 'model' : 'user',
          content: msg.content
        }))
      ];

      const response = await fetch(`${this.apiBaseUrl}/consultant-chat.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ messages })
      });

      if (!response.ok) throw new Error('Falha na API');

      const data = await response.json();
      return data.response || data.message || 'Pode me contar mais?';
    },

    shouldEndConversation(response) {
      const endPhrases = [
        'vou compilar',
        'vou montar',
        'perfil completo',
        'informações suficientes',
        'obrigado pela conversa'
      ];
      const lowerResponse = response.toLowerCase();
      return endPhrases.some(phrase => lowerResponse.includes(phrase));
    },

    async finishConversation() {
      this.conversationFinished = true;
      
      // Mostra indicador de digitação
      this.isTyping = true;
      this.$nextTick(() => this.scrollToBottom());
      
      // Simula delay de digitação
      await this.simulateTypingDelay();
      
      this.isTyping = false;
      this.addMessage('assistant', 'Excelente. Tenho todas as informações necessárias. Irei compilar os dados para gerar a estrutura, o conteúdo e as diretrizes visuais do seu site agora.');
      
      this.$nextTick(() => {
        this.scrollToBottom();
        setTimeout(() => this.startExtraction(), 1500);
      });
    },

    // =====================
    // EXTRACTION METHODS
    // =====================

    async startExtraction() {
      this.showResultsModal = true;
      this.isExtracting = true;
      this.extractionProgress = 0;
      this.extractionError = null;
      this.extractedData = null;

      // Simular progresso
      const progressInterval = setInterval(() => {
        if (this.extractionProgress < 90) {
          this.extractionProgress += Math.random() * 15;
        }
      }, 300);

      try {
        const response = await fetch(`${this.apiBaseUrl}/extract-brand-data.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            chat_history: this.chatHistory.map(msg => ({
              role: msg.role,
              content: msg.content
            }))
          })
        });

        clearInterval(progressInterval);
        this.extractionProgress = 100;

        if (!response.ok) throw new Error('Falha na extração');

        const data = await response.json();
        
        if (data.success && data.extracted) {
          this.extractedData = data.extracted;
        } else {
          throw new Error(data.error || 'Erro ao extrair dados');
        }
      } catch (error) {
        clearInterval(progressInterval);
        console.error('Erro na extração:', error);
        this.extractionError = error.message || 'Não foi possível extrair os dados. Tente novamente.';
      } finally {
        this.isExtracting = false;
      }
    },

    retryExtraction() {
      this.startExtraction();
    },

    // =====================
    // UI HELPERS
    // =====================

    formatMessage(content) {
      // Converter quebras de linha
      let formatted = content.replace(/\n/g, '<br>');
      
      // Converter links
      formatted = formatted.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank">$1</a>');
      
      // Detectar códigos hexadecimais e adicionar boxes de cor
      // Regex para capturar hex de 3 ou 6 dígitos (com ou sem #)
      const hexRegex = /#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})\b/g;
      
      formatted = formatted.replace(hexRegex, (match, hex) => {
        const fullHex = hex.length === 3 
          ? `#${hex.split('').map(c => c + c).join('')}` 
          : `#${hex}`;
        
        return `${match} <span class="color-box" style="background-color: ${fullHex};" title="Cor: ${fullHex}"></span>`;
      });
      
      return formatted;
    },

    formatTime(date) {
      if (!date) return '';
      const d = new Date(date);
      return d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    },

    getConfidenceLabel(level) {
      const labels = {
        high: 'Alta',
        medium: 'Média',
        low: 'Baixa'
      };
      return labels[level] || 'Média';
    },

    // Converte nome de cor ou hex para valor CSS válido
    getColorValue(colorInput) {
      if (!colorInput) return '#6366f1';
      
      // Se já for um hex válido, retorna direto
      if (/^#[0-9A-Fa-f]{3,6}$/.test(colorInput)) {
        return colorInput;
      }
      
      // Mapa de cores comuns em português para hex
      const colorMap = {
        // Azuis
        'azul': '#3B82F6',
        'azul marinho': '#1E3A5F',
        'azul escuro': '#1E40AF',
        'azul claro': '#60A5FA',
        'azul royal': '#4169E1',
        // Verdes
        'verde': '#22C55E',
        'verde escuro': '#166534',
        'verde água': '#2DD4BF',
        'verde musgo': '#4A5D23',
        // Vermelhos
        'vermelho': '#EF4444',
        'vermelho escuro': '#991B1B',
        'bordô': '#722F37',
        // Roxos
        'roxo': '#8B5CF6',
        'roxo escuro': '#5B21B6',
        'lavanda': '#E9D5FF',
        // Laranjas
        'laranja': '#F97316',
        'coral': '#FF7F50',
        // Amarelos
        'amarelo': '#EAB308',
        'dourado': '#D4AF37',
        'gold': '#FFD700',
        // Neutros
        'preto': '#171717',
        'cinza': '#6B7280',
        'cinza escuro': '#374151',
        'cinza claro': '#D1D5DB',
        'branco': '#FFFFFF',
        // Tons de pele/nude
        'nude': '#E8C4A2',
        'rosa': '#EC4899',
        'rosa antigo': '#BC8F8F',
        'rose': '#FF007F'
      };
      
      // Busca no mapa (case insensitive)
      const lowerInput = colorInput.toLowerCase();
      for (const [name, hex] of Object.entries(colorMap)) {
        if (lowerInput.includes(name)) {
          return hex;
        }
      }
      
      // Fallback: retorna a string como está (pode ser um nome CSS válido)
      return colorInput;
    },

    scrollToBottom() {
      const container = this.$refs.chatContainer;
      if (container) {
        container.scrollTop = container.scrollHeight;
      }
    },

    handleEnterKey(e) {
      if (!e.shiftKey) {
        e.preventDefault();
        this.sendMessage();
      }
    },

    autoResize() {
      const textarea = this.$refs.messageInput;
      if (textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 150) + 'px';
      }
    },

    resetTextareaHeight() {
      const textarea = this.$refs.messageInput;
      if (textarea) {
        textarea.style.height = 'auto';
      }
    },

    closeResultsModal() {
      this.showResultsModal = false;
      this.$emit('edit-manually', this.extractedData);
    },

    confirmAndProceed() {
      // Store in composable
      if (this.consultant) {
        this.consultant.setExtractedData(this.extractedData);
        this.consultant.finishConversation();
      }
      
      // Emit event to parent with extracted data
      this.$emit('data-extracted', this.extractedData);
      this.$emit('data-confirmed', this.extractedData);
      
      this.showResultsModal = false;
    },
    
    /**
     * Show toast notification
     */
    showToast(message, type = 'success') {
      // Simple toast implementation
      const toast = document.createElement('div');
      toast.className = `brand-consultant-toast toast-${type}`;
      toast.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-times-circle' : 'fa-info-circle'}"></i>
        <span>${message}</span>
      `;
      toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 24px;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#6366f1'};
        color: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        z-index: 10000;
        animation: slideIn 0.3s ease;
      `;
      
      document.body.appendChild(toast);
      
      setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease forwards';
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    }
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/__colors.scss';

// ===========================================
// CONFIG & VARS - Design Premium
// ===========================================
$bg-gradient: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
$glass-bg: rgba(255, 255, 255, 0.7);
$glass-border: 1px solid rgba(255, 255, 255, 0.5);
$blur: blur(12px);
$primary: #2563EB; // Azul Royal moderno
$accent: #6366f1; // Índigo
$text-dark: #1e293b;
$shadow-soft: 0 8px 32px 0 rgba(31, 38, 135, 0.1);

// ===========================================
// BRAND CONSULTANT - Layout Principal
// ===========================================
.brand-consultant {
  display: flex;
  flex-direction: column;
  height: calc(100vh - 70px);
  margin-top: 70px;
  position: relative;
  overflow: hidden;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  color: $text-dark;
}

// Fundo Animado (Sutil)
.ambient-background {
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle at 50% 50%, #e0e7ff 0%, #f3f4f6 50%, #eef2ff 100%);
  z-index: 0;
  animation: bgMove 20s ease infinite;
}

@keyframes bgMove {
  0% { transform: translate(0, 0); }
  50% { transform: translate(-2%, -2%); }
  100% { transform: translate(0, 0); }
}

// ===========================================
// HEADER & PROGRESS TRACKER
// ===========================================
.consultant-header {
  position: relative;
  z-index: 10;
  padding: 16px 32px;
  background: $glass-bg;
  backdrop-filter: $blur;
  border-bottom: $glass-border;
  display: flex;
  justify-content: center;

  &.glass-effect {
    background: rgba(255, 255, 255, 0.85);
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
  }
}

.header-content {
  width: 100%;
  max-width: 1000px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 24px;

  @media (max-width: 768px) {
    flex-direction: column;
    gap: 16px;
  }
}

.consultant-info {
  display: flex;
  align-items: center;
  gap: 12px;
  
  .avatar-ring {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: linear-gradient(45deg, $primary, $accent);
    padding: 2px;
    
    .avatar-icon {
      width: 100%;
      height: 100%;
      background: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: $primary;
      font-size: 1rem;
    }
  }

  .text-container {
    display: flex;
    flex-direction: column;
  }
  
  .consultant-name {
    font-size: 1rem;
    font-weight: 700;
    margin: 0;
    color: $text-dark;
    
    .badge {
      font-size: 0.65rem;
      background: rgba($primary, 0.1);
      color: $primary;
      padding: 3px 8px;
      border-radius: 4px;
      margin-left: 8px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-weight: 600;
    }
  }

  .status-text {
    font-size: 0.8rem;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 2px;
    
    .pulse-dot {
      width: 6px;
      height: 6px;
      background: #10b981;
      border-radius: 50%;
      box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
      animation: pulse-green 2s infinite;
    }
  }
}

// Botão de Nova Conversa
.btn-new-conversation {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: none;
  background: rgba($primary, 0.1);
  color: $primary;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-left: 12px;
  transition: all 0.2s;
  font-size: 0.8rem;

  &:hover {
    background: rgba($primary, 0.2);
    transform: rotate(-30deg);
  }
}

// Barra de Progresso Visual (Gamificação)
.progress-tracker {
  display: flex;
  align-items: center;
  position: relative;
  width: 320px;
  justify-content: space-between;
  flex-shrink: 0;

  @media (max-width: 768px) {
    width: 100%;
    max-width: 320px;
  }

  .progress-line {
    position: absolute;
    top: 50%;
    left: 0;
    width: 100%;
    height: 2px;
    background: rgba(0,0,0,0.05);
    z-index: 1;
    transform: translateY(-50%);
    
    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, $primary, $accent);
      transition: width 0.5s ease;
      border-radius: 2px;
    }
  }

  .step-item {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    
    .step-dot {
      width: 12px;
      height: 12px;
      background: #cbd5e1;
      border-radius: 50%;
      transition: all 0.3s;
      border: 2px solid white;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .step-label {
      position: absolute;
      top: 20px;
      font-size: 0.65rem;
      font-weight: 600;
      color: #94a3b8;
      transition: all 0.3s;
      white-space: nowrap;
    }

    &.active, &.completed {
      .step-dot {
        background: $primary;
        transform: scale(1.2);
        box-shadow: 0 0 0 4px rgba($primary, 0.15);
      }
      .step-label { 
        color: $primary; 
        font-weight: 700;
      }
    }

    &.completed .step-dot {
      background: $accent;
    }
  }
}

@keyframes pulse-green {
  0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
  70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
  100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

// ===========================================
// CHAT CONTAINER & MESSAGES
// ===========================================
.chat-container {
  flex: 1;
  overflow-y: auto;
  padding: 40px 20px 140px 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  z-index: 1;
  scroll-behavior: smooth;
}

.chat-messages {
  width: 100%;
  max-width: 800px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.message-row {
  display: flex;
  align-items: flex-end;
  gap: 12px;
  width: 100%;
  
  &.user {
    justify-content: flex-end;
    flex-direction: row-reverse;

    .message-bubble {
      background: linear-gradient(135deg, $primary, $accent);
      color: white;
      border-bottom-right-radius: 4px;
      box-shadow: 0 4px 15px rgba($primary, 0.3);
    }
  }
  
  &.assistant {
    justify-content: flex-start;

    .message-bubble {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(8px);
      color: $text-dark;
      border-bottom-left-radius: 4px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.03);
      border: 1px solid rgba(0,0,0,0.05);
    }
  }
}

.bot-mini-avatar {
  width: 28px;
  height: 28px;
  background: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  color: $primary;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  margin-bottom: 4px;
  flex-shrink: 0;
}

.message-bubble {
  max-width: 80%;
  padding: 16px 20px;
  border-radius: 18px;
  font-size: 0.95rem;
  line-height: 1.6;
  position: relative;

  &.glass-panel {
    backdrop-filter: blur(8px);
  }
  
  &.typing {
    padding: 16px 24px;
    display: flex;
    gap: 5px;
    align-items: center;
    
    .dot {
      width: 8px;
      height: 8px;
      background: #94a3b8;
      border-radius: 50%;
      animation: bounce 1.4s infinite ease-in-out both;
      
      &:nth-child(1) { animation-delay: -0.32s; }
      &:nth-child(2) { animation-delay: -0.16s; }
    }
  }

  .message-content {
    word-wrap: break-word;

    :deep(a) {
      color: inherit;
      text-decoration: underline;
    }
  }
}

// ===========================================
// INPUT AREA (Flutuante)
// ===========================================
.input-area-container {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  z-index: 20;
  padding: 0 20px 30px 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  background: linear-gradient(to top, rgba(243, 244, 246, 1) 0%, rgba(243, 244, 246, 0.95) 70%, rgba(243, 244, 246, 0) 100%);
  pointer-events: none;
}

.input-area {
  pointer-events: auto;
  width: 100%;
  max-width: 800px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(16px);
  padding: 8px 8px 8px 20px;
  border-radius: 24px;
  box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.8);
  display: flex;
  align-items: flex-end;
  gap: 12px;
  transition: transform 0.3s, box-shadow 0.3s;

  &.glass-effect {
    background: rgba(255, 255, 255, 0.9);
  }
  
  &:focus-within {
    transform: translateY(-2px);
    box-shadow: 0 15px 50px -10px rgba($primary, 0.2);
    border-color: rgba($primary, 0.3);
  }

  textarea {
    flex: 1;
    background: transparent;
    border: none;
    padding: 14px 0;
    max-height: 120px;
    resize: none;
    font-family: inherit;
    font-size: 1rem;
    outline: none;
    color: $text-dark;
    line-height: 1.4;
    
    &::placeholder { 
      color: #94a3b8; 
    }

    &:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
  }

  .btn-send {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: none;
    background: #e2e8f0;
    color: #94a3b8;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1rem;
    
    &.ready {
      background: $primary;
      color: white;
      transform: scale(1.05);
      
      &:hover {
        transform: scale(1.1) rotate(-10deg);
        box-shadow: 0 5px 15px rgba($primary, 0.4);
      }
    }

    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      transform: none;
    }
  }
}

.btn-finish-floating {
  pointer-events: auto;
  margin-top: 12px;
  background: $text-dark;
  color: white;
  border: none;
  padding: 12px 28px;
  border-radius: 30px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s;
  
  &:hover:not(:disabled) {
    background: black;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
  }

  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  i {
    font-size: 0.85rem;
  }
}

// ===========================================
// ANIMATIONS
// ===========================================
.message-slide-enter-active,
.message-slide-leave-active {
  transition: all 0.4s cubic-bezier(0.25, 1, 0.5, 1);
}

.message-slide-enter-from {
  opacity: 0;
  transform: translateY(20px) scale(0.98);
}

.message-slide-leave-to {
  opacity: 0;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@keyframes bounce {
  0%, 80%, 100% { transform: scale(0); }
  40% { transform: scale(1); }
}

// ===========================================
// RESULTS MODAL (mantido do original)
// ===========================================

.results-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 20px;
}

.results-modal {
  width: 100%;
  max-width: 700px;
  max-height: 90vh;
  background: $white;
  border-radius: 20px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

// Extraction Loading
.extraction-loading {
  padding: 60px 40px;
  text-align: center;

  .loading-animation {
    position: relative;
    width: 100px;
    height: 100px;
    margin: 0 auto 24px;

    .pulse-ring {
      position: absolute;
      inset: 0;
      border: 3px solid $p-color;
      border-radius: 50%;
      animation: pulse 1.5s ease-out infinite;
    }

    .brain-icon {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 48px;
    }
  }

  h3 {
    font-size: 1.5rem;
    margin-bottom: 8px;
  }

  p {
    color: $gray-medium;
  }

  .progress-bar-extraction {
    margin-top: 24px;
    height: 6px;
    background: $gray-light;
    border-radius: 3px;
    overflow: hidden;

    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, $p-color, lighten($p-color, 10%));
      transition: width 0.3s;
    }
  }
}

@keyframes pulse {
  0% {
    transform: scale(0.8);
    opacity: 1;
  }
  100% {
    transform: scale(1.5);
    opacity: 0;
  }
}

// Extraction Results
.extraction-results {
  display: flex;
  flex-direction: column;
  max-height: 90vh;

  .results-header {
    padding: 24px 32px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    text-align: center;
    border-bottom: 1px solid $gray-light;

    .results-icon {
      font-size: 48px;
      margin-bottom: 12px;
    }

    h3 {
      font-size: 1.5rem;
      margin-bottom: 8px;
    }

    p {
      color: $gray-medium;
      margin-bottom: 16px;
    }

    .confidence-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;

      &.high {
        background: rgba(34, 197, 94, 0.1);
        color: #16a34a;
      }

      &.medium {
        background: rgba(234, 179, 8, 0.1);
        color: #ca8a04;
      }

      &.low {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
      }
    }

    .confidence-warning {
      margin-top: 12px;
      padding: 12px 16px;
      background: rgba(234, 179, 8, 0.1);
      border: 1px solid rgba(234, 179, 8, 0.3);
      border-radius: 8px;
      color: #92400e;
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      gap: 8px;

      i {
        color: #ca8a04;
      }
    }
  }

  .results-sections {
    flex: 1;
    overflow-y: auto;
    padding: 24px 32px;

    .result-section {
      margin-bottom: 24px;
      padding-bottom: 24px;
      border-bottom: 1px solid $gray-light;

      &:last-child {
        border-bottom: none;
        margin-bottom: 0;
      }

      h4 {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 1rem;
        color: $gray-darkness;
        margin-bottom: 16px;

        i {
          color: $p-color;
        }
      }

      &.ai-suggestions {
        background: linear-gradient(135deg, rgba($p-color, 0.05) 0%, rgba($p-color, 0.1) 100%);
        margin: 0 -32px -24px;
        padding: 24px 32px;
        border-radius: 0 0 20px 20px;
        border-bottom: none;
      }
    }

    .result-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px;

      @media (max-width: 600px) {
        grid-template-columns: 1fr;
      }
    }

    .result-item {
      &.full-width {
        grid-column: 1 / -1;
      }

      label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: $gray-medium;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
      }

      span {
        display: block;
        color: $gray-darkness;
        line-height: 1.5;

        &.highlight {
          background: rgba($p-color, 0.1);
          padding: 8px 12px;
          border-radius: 8px;
          border-left: 3px solid $p-color;
        }
      }
    }

    .tags {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;

      .tag {
        padding: 6px 12px;
        background: $p-color;
        color: $white;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
      }

      &.keywords .tag {
        background: transparent;
        color: $p-color;
        border: 1px solid $p-color;
      }
    }

    .suggestion-card.tagline {
      background: $white;
      padding: 16px;
      border-radius: 12px;
      margin-bottom: 16px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);

      .tagline-text {
        display: block;
        font-size: 1.25rem;
        font-weight: 600;
        font-style: italic;
        color: $p-color;
        margin-top: 8px;
      }
    }

    .tone-badge {
      display: inline-block;
      padding: 4px 10px;
      background: $gray-darkness;
      color: $white;
      border-radius: 6px;
      font-weight: 600;
    }

    // ==========================================
    // VISUAL IDENTITY SECTION
    // ==========================================
    
    &.visual-identity {
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
      margin: 0 -32px;
      padding: 24px 32px;
      border-radius: 0;

      h4 {
        color: $white;

        i {
          color: #f472b6;
        }
      }

      .result-item label {
        color: rgba(255, 255, 255, 0.7);
      }

      .result-item span {
        color: $white;
      }
    }

    .visual-preview-card {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 16px;
      padding: 20px;
      
      .visual-header {
        margin-bottom: 16px;
      }

      .style-badge {
        display: inline-block;
        padding: 8px 16px;
        background: linear-gradient(135deg, #8B5CF6 0%, #EC4899 100%);
        color: $white;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
      }

      .color-preview {
        margin-bottom: 20px;

        label {
          display: block;
          font-size: 0.8rem;
          font-weight: 600;
          color: rgba(255, 255, 255, 0.7);
          text-transform: uppercase;
          letter-spacing: 0.5px;
          margin-bottom: 8px;
        }

        .color-description {
          color: $white;
          font-size: 0.95rem;
          margin-bottom: 12px;
        }

        .color-swatches {
          display: flex;
          gap: 12px;

          .swatch {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            border: 3px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            transition: transform 0.2s;

            &:hover {
              transform: scale(1.1);
            }

            &.primary {
              width: 64px;
              height: 64px;
            }
          }
        }
      }

      .result-grid {
        margin-bottom: 16px;
      }

      .mood-keywords {
        margin-top: 16px;

        label {
          display: block;
          font-size: 0.8rem;
          font-weight: 600;
          color: rgba(255, 255, 255, 0.7);
          text-transform: uppercase;
          margin-bottom: 8px;
        }

        .keywords-list {
          display: flex;
          flex-wrap: wrap;
          gap: 8px;
        }

        .mood-tag {
          padding: 6px 14px;
          background: rgba(255, 255, 255, 0.1);
          border: 1px solid rgba(255, 255, 255, 0.2);
          color: $white;
          border-radius: 20px;
          font-size: 0.85rem;
          font-weight: 500;
        }
      }

      .special-sections {
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);

        label {
          display: block;
          font-size: 0.8rem;
          font-weight: 600;
          color: rgba(255, 255, 255, 0.7);
          text-transform: uppercase;
          margin-bottom: 12px;
        }

        ul {
          list-style: none;
          padding: 0;
          margin: 0;

          li {
            display: flex;
            align-items: center;
            gap: 8px;
            color: $white;
            font-size: 0.9rem;
            padding: 6px 0;

            i {
              color: #4ade80;
              font-size: 0.85rem;
            }
          }
        }
      }
    }
  }

  .results-actions {
    display: flex;
    gap: 12px;
    padding: 20px 32px;
    background: $white;
    border-top: 1px solid $gray-light;

    button {
      flex: 1;
      padding: 14px 24px;
      border-radius: 12px;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .btn-secondary {
      background: transparent;
      border: 2px solid $gray-light;
      color: $gray-darkness;

      &:hover {
        border-color: $gray-medium;
      }
    }

    .btn-primary {
      background: $p-color;
      border: none;
      color: $white;

      &:hover {
        background: darken($p-color, 10%);
      }
    }
  }
}

// Extraction Error
.extraction-error {
  padding: 60px 40px;
  text-align: center;

  .error-icon {
    font-size: 64px;
    margin-bottom: 16px;
  }

  h3 {
    font-size: 1.5rem;
    margin-bottom: 8px;
  }

  p {
    color: $gray-medium;
    margin-bottom: 24px;
  }

  .btn-primary {
    padding: 12px 32px;
    background: $p-color;
    border: none;
    border-radius: 12px;
    color: $white;
    font-weight: 600;
    cursor: pointer;

    &:hover {
      background: darken($p-color, 10%);
    }
  }
}

// Modal Transition
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

// ==========================================
// RESPONSIVE
// ==========================================

@media (max-width: 768px) {
  .consultant-header {
    padding: 12px 16px;

    .header-content {
      flex-direction: column;
      gap: 12px;
    }

    .consultant-info {
      .avatar-ring {
        width: 36px;
        height: 36px;
      }

      .consultant-name {
        font-size: 0.9rem;

        .badge {
          font-size: 0.55rem;
          padding: 2px 6px;
        }
      }

      .status-text {
        font-size: 0.75rem;
      }
    }

    .progress-tracker {
      width: 100%;
      max-width: 280px;

      .step-label {
        font-size: 0.55rem;
      }
    }
  }

  .chat-container {
    padding: 20px 16px 160px 16px;
  }

  .chat-messages {
    gap: 16px;
  }

  .message-row {
    gap: 8px;

    .message-bubble {
      max-width: 85%;
      padding: 12px 16px;
      font-size: 0.9rem;
    }

    .bot-mini-avatar {
      width: 24px;
      height: 24px;
      font-size: 0.7rem;
    }
  }

  .input-area-container {
    padding: 0 12px 20px 12px;
  }

  .input-area {
    padding: 6px 6px 6px 16px;
    border-radius: 20px;

    textarea {
      padding: 12px 0;
      font-size: 0.95rem;
    }

    .btn-send {
      width: 40px;
      height: 40px;
    }
  }

  .btn-finish-floating {
    padding: 10px 20px;
    font-size: 0.85rem;
  }

  .results-modal {
    max-height: 95vh;
    border-radius: 16px;
  }

  .extraction-results {
    .results-header,
    .results-sections {
      padding-left: 20px;
      padding-right: 20px;
    }

    .results-actions {
      flex-direction: column;
      padding: 16px 20px;
    }
  }
}

// Color box styles for hex colors in chat messages
.color-box {
  display: inline-block;
  width: 16px;
  height: 16px;
  border-radius: 3px;
  border: 1px solid rgba(0, 0, 0, 0.1);
  margin-left: 4px;
  margin-right: 2px;
  vertical-align: middle;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
  cursor: help;
  position: relative;
  transition: all 0.2s ease;
  
  &:hover {
    transform: scale(1.2);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    border-color: rgba(0, 0, 0, 0.2);
  }
}
</style>
