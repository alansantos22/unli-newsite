<template>
  <transition name="chat-slide-up">
  <div class="sdr-chat-assistant">
    <!-- Fundo Animado -->
    <div class="ambient-background"></div>

    <!-- Header do Chat -->
    <div class="chat-header glass-effect">
      <div class="header-content">
        <div class="assistant-info">
          <div class="avatar-ring pulse-ring">
            <div class="avatar-icon">
              <i class="fas fa-headset"></i>
            </div>
          </div>
          <div class="text-container">
            <h2 class="assistant-name">
              Assistente Unli 
              <span class="badge">Consultor Digital</span>
            </h2>
            <div class="status-text">
              <span class="pulse-dot online"></span>
              {{ statusText }}
            </div>
          </div>
        </div>

        <!-- Actions: Fechar -->
        <div class="header-actions">
          <button
            class="btn-close-chat"
            @click="closeChat"
            title="Fechar e voltar"
            aria-label="Fechar chat"
          >
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>

      <!-- Stage Indicator -->
      <div class="stage-tracker" v-if="currentStage">
        <div 
          v-for="stage in stages" 
          :key="stage.id"
          class="stage-item"
          :class="{ 
            'active': currentStage === stage.id, 
            'completed': stageOrder.indexOf(stage.id) < stageOrder.indexOf(currentStage) 
          }"
        >
          <div class="stage-dot">
            <i :class="stage.icon"></i>
          </div>
          <span class="stage-label">{{ stage.label }}</span>
        </div>
        <div class="stage-line">
          <div class="stage-fill" :style="{ width: stageProgress + '%' }"></div>
        </div>
      </div>
    </div>

    <!-- Área do Chat -->
    <div class="chat-container" ref="chatContainer">
      <div class="chat-messages-container">
        <transition-group name="message-slide" tag="div" class="chat-messages">
          <!-- Mensagem de Boas-vindas -->
          <div 
            v-if="messages.length === 0 && !isLoading"
            key="welcome"
            class="welcome-card glass-panel"
          >
            <h3>Olá! Sou o Assistente Unli</h3>
            <p>Vou te ajudar a encontrar o site ideal para o seu negócio. 
               Me conta: você já sabe o que precisa ou quer uma consultoria?</p>
            <div class="quick-actions">
              <button 
                class="quick-reply-bubble primary" 
                @click="sendQuickMessage('Quero ajuda para escolher o melhor site para mim')"
              >
                <i class="fas fa-compass"></i>
                <span>Quero ajuda para escolher</span>
              </button>
              <button 
                class="quick-reply-bubble" 
                @click="sendQuickMessage('Já sei o que preciso')"
              >
                <i class="fas fa-check-circle"></i>
                <span>Já sei o que preciso</span>
              </button>
            </div>
          </div>

          <!-- Mensagens do Chat -->
          <div 
            v-for="(message, index) in messages" 
            :key="'msg-' + index"
            :class="['message-row', message.role]"
          >
            <div class="message-bubble glass-panel">
              <div class="message-content" v-html="formatMessage(message.content)"></div>
              <div class="message-time" v-if="message.timestamp">
                {{ formatTime(message.timestamp) }}
              </div>
            </div>
            <div v-if="message.role === 'assistant'" class="bot-mini-avatar">
              <i class="fas fa-headset"></i>
            </div>
          </div>

          <!-- Indicador de Digitação -->
          <div v-if="isTyping" key="typing" class="message-row assistant">
            <div class="message-bubble glass-panel typing">
              <span class="typing-text">{{ typingIndicatorText }}</span>
              <span class="dot"></span>
              <span class="dot"></span>
              <span class="dot"></span>
            </div>
            <div class="bot-mini-avatar">
              <i class="fas fa-headset"></i>
            </div>
          </div>
        </transition-group>
      </div>
    </div>

    <!-- Área de Input Fixa -->
    <div class="input-area-fixed">
      <div class="input-area glass-effect">
        <textarea 
          ref="messageInput"
          v-model="userInput" 
          @keydown.enter.exact="handleEnterKey"
          @input="autoResize"
          placeholder="Digite sua mensagem..."
          :disabled="isLoading || finished || isExtracting"
          rows="1"
        ></textarea>
        <button 
          class="btn-send" 
          @click="sendMessage" 
          :disabled="!userInput.trim() || isLoading || finished || isExtracting"
          :class="{ 'ready': userInput.trim().length > 0 }"
        >
          <i class="fas fa-paper-plane"></i>
        </button>
      </div>
      

      
      <!-- Botão WhatsApp para Desenvolvimento Customizado (e-commerce/apps) -->
      <transition name="fade">
        <button 
          v-if="needsCustomDev && !finished"
          class="btn-whatsapp-custom-dev"
          @click="openWhatsAppCustomDev"
        >
          <i class="fab fa-whatsapp"></i>
          Falar com especialista em sistemas
        </button>
      </transition>
    </div>

    <!-- Modal de Extração -->
    <transition name="modal-fade">
      <div v-if="showExtractionModal" class="extraction-modal-overlay">
        <div class="extraction-modal glass-panel">
          <div v-if="isExtracting" class="extraction-loading">
            <div class="loading-animation">
              <div class="pulse-ring"></div>
              <div class="brain-icon">🧠</div>
            </div>
            <h3>Analisando nossa conversa...</h3>
            <p>Estou montando seu orçamento personalizado</p>
            <div class="progress-bar-extraction">
              <div class="progress-fill" :style="{ width: extractionProgress + '%' }"></div>
            </div>
          </div>

          <div v-else-if="extractedData" class="extraction-success">
            <div class="success-icon">✨</div>
            <h3>Entendi seu projeto!</h3>
            <p>Preparei tudo para você. Vamos revisar?</p>
            
            <div class="extracted-summary">
              <div class="summary-item" v-if="extractedData.business?.name">
                <i class="fas fa-building"></i>
                <span>{{ extractedData.business.name }}</span>
              </div>
              <div class="summary-item" v-if="extractedData.business?.niche">
                <i class="fas fa-tag"></i>
                <span>{{ extractedData.business.niche }}</span>
              </div>
              <div class="summary-item" v-if="extractedData.site?.suggestedPackage">
                <i class="fas fa-box"></i>
                <span>Pacote: {{ packageLabels[extractedData.site.suggestedPackage] || extractedData.site.suggestedPackage }}</span>
              </div>
              <div class="summary-item" v-if="extractedData.site?.pages?.length">
                <i class="fas fa-file-alt"></i>
                <span>{{ extractedData.site.pages.length }} páginas</span>
              </div>
            </div>

            <div class="modal-actions">
              <button class="btn-primary" @click="proceedToWizard">
                <i class="fas fa-arrow-right"></i>
                Continuar para o site
              </button>
              <button class="btn-secondary" @click="continueChat">
                <i class="fas fa-comment"></i>
                Continuar conversando
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Modal de Checkout Direto (quando finished=true via chat) -->
    <transition name="modal-fade">
      <div v-if="showCheckoutModal" class="extraction-modal-overlay">
        <div class="extraction-modal glass-panel checkout-modal">
          <!-- Loading: Processando pedido -->
          <div v-if="isProcessingCheckout" class="extraction-loading">
            <div class="loading-animation">
              <div class="pulse-ring"></div>
              <div class="brain-icon">💳</div>
            </div>
            <h3>Processando seu pedido...</h3>
            <p>Estamos preparando o checkout seguro</p>
            <div class="progress-bar-extraction">
              <div class="progress-fill" :style="{ width: checkoutProgress + '%' }"></div>
            </div>
          </div>

          <!-- Formulário de contato + resumo -->
          <div v-else class="checkout-modal-content">
            <div class="success-icon">🚀</div>
            <h3>Finalizar Pedido</h3>
            <p>Preencha seus dados para prosseguir ao pagamento seguro</p>

            <!-- Resumo do plano -->
            <div class="extracted-summary">
              <div class="summary-item" v-if="checkoutPlanData.packageLabel">
                <i class="fas fa-box-open"></i>
                <span>{{ checkoutPlanData.packageLabel }}</span>
              </div>
              <div class="summary-item" v-if="checkoutPlanData.pages && checkoutPlanData.pages.length">
                <i class="fas fa-layer-group"></i>
                <span>{{ checkoutPlanData.pages.length }} {{ checkoutPlanData.pages.length === 1 ? 'página' : 'páginas' }}</span>
              </div>
              <div class="summary-item">
                <i class="fas fa-credit-card"></i>
                <span>{{ checkoutPlanData.paymentLabel }}</span>
              </div>
            </div>

            <!-- Formulário mínimo -->
            <form @submit.prevent="submitDirectCheckout" class="checkout-mini-form">
              <div class="mini-form-group">
                <input
                  type="text"
                  v-model="checkoutForm.name"
                  placeholder="Seu nome completo *"
                  required
                  :class="{ 'has-error': checkoutErrors.name }"
                />
                <span v-if="checkoutErrors.name" class="mini-error">{{ checkoutErrors.name }}</span>
              </div>
              <div class="mini-form-group">
                <input
                  type="email"
                  v-model="checkoutForm.email"
                  placeholder="Seu melhor e-mail *"
                  required
                  :class="{ 'has-error': checkoutErrors.email }"
                />
                <span v-if="checkoutErrors.email" class="mini-error">{{ checkoutErrors.email }}</span>
              </div>
              <div class="mini-form-group">
                <input
                  type="tel"
                  v-model="checkoutForm.whatsapp"
                  @input="formatCheckoutWhatsApp"
                  placeholder="WhatsApp (00) 00000-0000 *"
                  maxlength="15"
                  required
                  :class="{ 'has-error': checkoutErrors.whatsapp }"
                />
                <span v-if="checkoutErrors.whatsapp" class="mini-error">{{ checkoutErrors.whatsapp }}</span>
              </div>

              <div class="modal-actions">
                <button type="submit" class="btn-primary" :disabled="isProcessingCheckout">
                  <i class="fas fa-lock"></i>
                  Ir para pagamento seguro
                </button>
                <button type="button" class="btn-secondary" @click="closeCheckoutModal">
                  <i class="fas fa-arrow-left"></i>
                  Voltar ao chat
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>
  </div>
  </transition>
</template>

<script>
export default {
  name: 'SDRChatAssistant',
  
  props: {
    // Callback quando usuário decide ir para o wizard
    onProceedToWizard: {
      type: Function,
      default: null
    },
    // Callback quando pula direto para o formulário
    onSkipToForm: {
      type: Function,
      default: null
    },
    // URL base da API
    apiBaseUrl: {
      type: String,
      default: '/api/ai'
    }
  },

  data() {
    return {
      // Estado do chat
      messages: [],
      userInput: '',
      isLoading: false,
      isTyping: false,
      finished: false,
      
      // Flag para desenvolvimento customizado (e-commerce/app)
      needsCustomDev: false,
      
      // Extração de dados
      isExtracting: false,
      showExtractionModal: false,
      extractedData: null,
      extractionProgress: 0,
      
      // Stage tracking
      currentStage: 'ABERTURA',
      stageOrder: ['ABERTURA', 'EXPLORACAO', 'PROPOSTA', 'PRECO', 'FECHAMENTO'],
      stages: [
        { id: 'ABERTURA', label: 'Início', icon: 'fas fa-hand-wave' },
        { id: 'EXPLORACAO', label: 'Conhecendo', icon: 'fas fa-search' },
        { id: 'PROPOSTA', label: 'Proposta', icon: 'fas fa-lightbulb' },
        { id: 'PRECO', label: 'Preço', icon: 'fas fa-tag' },
        { id: 'FECHAMENTO', label: 'Fechamento', icon: 'fas fa-handshake' }
      ],
      
      // Labels
      packageLabels: {
        essential: 'Essencial',
        authority: 'Autoridade',
        enterprise: 'Ecossistema Digital',
        custom: 'Personalizado'
      },
      
      // Latência artificial para humanização
      typingDelay: { min: 1500, max: 2500 },
      
      // WhatsApp para projetos customizados
      whatsappCustomDev: '5511999999999', // Substituir pelo número real
      
      // Checkout direto via chat
      showCheckoutModal: false,
      isProcessingCheckout: false,
      checkoutProgress: 0,
      checkoutForm: {
        name: '',
        email: '',
        whatsapp: ''
      },
      checkoutErrors: {},
      // Dados do plano selecionado no chat (preenchido quando finished=true)
      checkoutPlanData: {
        type: null,          // 'site_complete' ou 'landing'
        pages: [],           // ['about', 'services', ...]
        paymentMethod: null, // 'parcelado' ou 'pix_avista'
        packageLabel: '',
        paymentLabel: '',
        specialistOnboarding: false // se comprou addon de especialista
      }
    }
  },

  computed: {
    statusText() {
      if (this.isTyping) return 'Digitando...';
      if (this.isLoading) return 'Pensando...';
      if (this.finished) return 'Conversa finalizada';
      return 'Online agora';
    },
    
    typingIndicatorText() {
      return 'Assistente está digitando';
    },
    
    canFinish() {
      // Permite finalizar após 3 trocas de mensagens (6 mensagens total)
      return this.messages.length >= 4;
    },
    
    stageProgress() {
      const currentIndex = this.stageOrder.indexOf(this.currentStage);
      return ((currentIndex + 1) / this.stageOrder.length) * 100;
    }
  },

  mounted() {
    // Lock body scroll (fullscreen takeover)
    document.body.style.overflow = 'hidden';

    // Auto-focus no input
    this.$nextTick(() => {
      if (this.$refs.messageInput) {
        this.$refs.messageInput.focus();
      }
    });
    
    // Carregar conversa salva (se existir)
    this.loadSavedConversation();
  },

  beforeUnmount() {
    document.body.style.overflow = '';
  },

  methods: {
    // ==================
    // ENVIO DE MENSAGENS
    // ==================
    
    async sendMessage() {
      const content = this.userInput.trim();
      if (!content || this.isLoading) return;
      
      // Adicionar mensagem do usuário
      this.messages.push({
        role: 'user',
        content: content,
        timestamp: new Date()
      });
      
      this.userInput = '';
      this.autoResize();
      this.scrollToBottom();
      
      // Salvar conversa
      this.saveConversation();
      
      // Chamar API
      await this.callSDRChat();
    },
    
    sendQuickMessage(content) {
      this.userInput = content;
      this.sendMessage();
    },

    closeChat() {
      this.$emit('close');
    },

    
    handleEnterKey(event) {
      if (!event.shiftKey) {
        event.preventDefault();
        this.sendMessage();
      }
    },
    
    // ==================
    // INTEGRAÇÃO COM API
    // ==================
    
    async callSDRChat(retryCount = 0) {
      this.isLoading = true;
      const maxRetries = 2; // até 2 retries no frontend (total 3 tentativas)
      
      try {
        // Simular latência humana
        await this.simulateTyping();
        
        const response = await fetch(`${this.apiBaseUrl}/sdr-chat.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            messages: this.messages.map(m => ({
              role: m.role === 'assistant' ? 'model' : 'user',
              content: m.content
            }))
          })
        });
        
        const data = await response.json();
        
        if (!data.success) {
          throw new Error(data.error || 'Erro na API');
        }
        
        // Atualizar stage (apenas se avançar, nunca voltar)
        if (data.stage) {
          const newStageIndex = this.stageOrder.indexOf(data.stage);
          const currentStageIndex = this.stageOrder.indexOf(this.currentStage);
          
          // Só atualiza se o novo stage for maior ou igual ao atual
          if (newStageIndex >= currentStageIndex) {
            this.currentStage = data.stage;
          } else {
            console.warn(`⚠️ [SDRChat] Stage ignorado: tentativa de voltar de ${this.currentStage} para ${data.stage}`);
          }
        }
        
        // Adicionar resposta do assistente
        this.messages.push({
          role: 'assistant',
          content: data.response,
          timestamp: new Date(),
          clientData: data.clientData,
          suggestedPlan: data.suggestedPlan
        });
        
        // Verificar se precisa de desenvolvimento customizado (e-commerce/app)
        if (data.clientData?.needs_custom_dev) {
          this.needsCustomDev = true;
        }
        
        // Verificar se conversa finalizou
        if (data.finished) {
          this.finished = true;
          
          // Se tem plano sugerido com método de pagamento, abrir checkout direto
          if (data.suggestedPlan && data.suggestedPlan.paymentMethod) {
            this.openDirectCheckout(data.suggestedPlan, data.pricing);
          }
        } else {
          // Fallback: detectar mensagem de fechamento mesmo sem finished=true
          // (às vezes o Gemini esquece de marcar)
          this.detectClosingMessageFallback(data);
        }
        
        this.scrollToBottom();
        this.saveConversation();
        
      } catch (error) {
        console.warn(`[SDRChat] Tentativa ${retryCount + 1} falhou:`, error.message);
        
        // Retry automático para erros de rede/servidor (429, 500, 502, 503)
        const isRetryable = error.message?.includes('Resource exhausted') 
          || error.message?.includes('500')
          || error.message?.includes('502')
          || error.message?.includes('503')
          || error.message?.includes('429')
          || error.message?.includes('Failed to fetch')
          || error.message?.includes('NetworkError');
        
        if (isRetryable && retryCount < maxRetries) {
          const waitMs = (retryCount + 1) * 3000; // 3s, 6s
          console.log(`[SDRChat] Retry em ${waitMs / 1000}s... (tentativa ${retryCount + 2})`);
          await new Promise(resolve => setTimeout(resolve, waitMs));
          return this.callSDRChat(retryCount + 1);
        }
        
        console.error('Erro no SDR Chat após tentativas:', error);
        this.messages.push({
          role: 'assistant',
          content: 'Ops! Tive um problema técnico momentâneo. Pode tentar enviar novamente em alguns segundos? 😊',
          timestamp: new Date(),
          isError: true
        });
      } finally {
        this.isLoading = false;
        this.isTyping = false;
      }
    },
    
    // ==================
    // EXTRAÇÃO DE DADOS
    // ==================
    
    async finishAndExtract() {
      this.showExtractionModal = true;
      this.isExtracting = true;
      this.extractionProgress = 0;
      
      // Animação de progresso
      const progressInterval = setInterval(() => {
        if (this.extractionProgress < 90) {
          this.extractionProgress += Math.random() * 15;
        }
      }, 300);
      
      try {
        const response = await fetch(`${this.apiBaseUrl}/sdr-extract-data.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            conversation: this.messages.map(m => ({
              role: m.role,
              content: m.content
            }))
          })
        });
        
        const data = await response.json();
        
        clearInterval(progressInterval);
        this.extractionProgress = 100;
        
        if (!data.success) {
          throw new Error(data.error || 'Erro na extração');
        }
        
        this.extractedData = data.data;
        
      } catch (error) {
        console.error('Erro na extração:', error);
        clearInterval(progressInterval);
        // Fallback: ir para wizard sem dados
        this.proceedToWizard();
      } finally {
        this.isExtracting = false;
      }
    },
    
    proceedToWizard() {
      this.showExtractionModal = false;
      
      // Emitir dados para o componente pai
      this.$emit('proceed-to-wizard', {
        extractedData: this.extractedData,
        conversation: this.messages
      });
      
      // Callback se fornecido
      if (this.onProceedToWizard) {
        this.onProceedToWizard(this.extractedData);
      }
    },
    
    continueChat() {
      this.showExtractionModal = false;
      this.extractedData = null;
    },
    
    skipToForm() {
      this.$emit('skip-to-form');
      
      if (this.onSkipToForm) {
        this.onSkipToForm();
      }
    },
    
    openWhatsAppCustomDev() {
      // Monta mensagem com contexto da conversa
      const clientData = this.getLatestClientData();
      let message = 'Olá! Vim do chat do site e tenho interesse em um projeto de desenvolvimento customizado.';
      
      if (clientData?.niche) {
        message += `\n\nMeu nicho: ${clientData.niche}`;
      }
      if (clientData?.businessName) {
        message += `\nEmpresa: ${clientData.businessName}`;
      }
      if (clientData?.needs?.length) {
        message += `\nO que preciso: ${clientData.needs.join(', ')}`;
      }
      
      const encodedMessage = encodeURIComponent(message);
      window.open(`https://wa.me/${this.whatsappCustomDev}?text=${encodedMessage}`, '_blank');
      
      // Marcar conversa como finalizada
      this.finished = true;
      this.$emit('redirect-to-whatsapp', { type: 'custom_dev', clientData });
    },
    
    getLatestClientData() {
      // Pega o clientData mais recente das mensagens do assistente
      for (let i = this.messages.length - 1; i >= 0; i--) {
        if (this.messages[i].clientData) {
          return this.messages[i].clientData;
        }
      }
      return null;
    },
    
    // ==================
    // UTILITÁRIOS
    // ==================
    
    async simulateTyping() {
      this.isTyping = true;
      const delay = Math.random() * 
        (this.typingDelay.max - this.typingDelay.min) + 
        this.typingDelay.min;
      await new Promise(resolve => setTimeout(resolve, delay));
    },
    
    formatMessage(content) {
      if (!content) return '';
      // Converter quebras de linha
      let formatted = content.replace(/\n/g, '<br>');
      // Converter **bold**
      formatted = formatted.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
      return formatted;
    },
    
    formatTime(date) {
      if (!date) return '';
      const d = new Date(date);
      return d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    },
    
    autoResize() {
      const textarea = this.$refs.messageInput;
      if (!textarea) return;
      textarea.style.height = 'auto';
      textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
    },
    
    scrollToBottom() {
      this.$nextTick(() => {
        const container = this.$refs.chatContainer;
        if (container) {
          container.scrollTop = container.scrollHeight;
        }
      });
    },
    
    // ==================
    // PERSISTÊNCIA
    // ==================
    
    saveConversation() {
      try {
        localStorage.setItem('unli_sdr_conversation', JSON.stringify({
          messages: this.messages,
          stage: this.currentStage,
          timestamp: new Date().toISOString()
        }));
      } catch (e) {
        console.warn('Erro ao salvar conversa:', e);
      }
    },
    
    loadSavedConversation() {
      try {
        const saved = localStorage.getItem('unli_sdr_conversation');
        if (saved) {
          const data = JSON.parse(saved);
          // Só restaurar se for recente (últimas 24h)
          const savedTime = new Date(data.timestamp);
          const now = new Date();
          const hoursDiff = (now - savedTime) / (1000 * 60 * 60);
          
          if (hoursDiff < 24) {
            this.messages = data.messages || [];
            this.currentStage = data.stage || 'ABERTURA';
          } else {
            localStorage.removeItem('unli_sdr_conversation');
          }
        }
      } catch (e) {
        console.warn('Erro ao carregar conversa:', e);
      }
    },
    
    clearConversation() {
      this.messages = [];
      this.currentStage = 'ABERTURA';
      this.finished = false;
      localStorage.removeItem('unli_sdr_conversation');
    },
    
    // ==================
    // CHECKOUT DIRETO
    // ==================
    
    detectClosingMessageFallback(data) {
      const message = (data.response || '').toLowerCase();
      const closingPhrases = [
        'vou te direcionar',
        'direcionar agora',
        'finalizar o pedido',
        'finalizar seu pedido',
        'prosseguir para o pagamento',
        'fechar o pedido',
        'concluir o pedido',
      ];
      
      const isClosing = closingPhrases.some(phrase => message.includes(phrase));
      const hasPrice = /r\$\s*[\d.,]+/.test(message);
      const hasPlan = data.suggestedPlan && data.suggestedPlan.pages && data.suggestedPlan.pages.length > 0;
      
      if (isClosing && hasPrice && hasPlan) {
        console.warn('⚠️ [SDRChat] Fallback: Mensagem de fechamento detectada sem finished=true. Forçando checkout.');
        this.finished = true;
        this.currentStage = 'FECHAMENTO';
        
        // Tentar inferir paymentMethod se não veio
        if (!data.suggestedPlan.paymentMethod) {
          if (message.includes('pix') || message.includes('à vista') || message.includes('a vista')) {
            data.suggestedPlan.paymentMethod = 'pix_avista';
          } else if (message.includes('mensal') || message.includes('mensais') || message.includes('parcel') || message.includes('cartão') || message.includes('12x')) {
            data.suggestedPlan.paymentMethod = 'parcelado';
          }
        }
        
        if (data.suggestedPlan.paymentMethod) {
          this.openDirectCheckout(data.suggestedPlan, data.pricing);
        }
      }
    },
    
    openDirectCheckout(suggestedPlan, pricing = null) {
      console.log('🛒 [SDRChat] Abrindo checkout direto com plano:', suggestedPlan, '| Pricing backend:', pricing);
      
      // Mapear dados do plano
      const pages = suggestedPlan.pages || [];
      const paymentMethod = suggestedPlan.paymentMethod; // 'parcelado' ou 'pix_avista'
      const type = suggestedPlan.type || 'site_complete';
      
      // Determinar label do pacote
      let packageLabel = 'Personalizado';
      const pageCount = pages.length;
      if (pageCount <= 3) packageLabel = 'Essencial';
      else if (pageCount <= 5) packageLabel = 'Autoridade';
      else if (pageCount >= 6) packageLabel = 'Ecossistema Digital';
      
      // Label de pagamento: usar preço real do backend se disponível
      let paymentLabel;
      if (pricing) {
        const isParcelado = paymentMethod !== 'pix_avista';
        if (isParcelado && pricing.parcela_12) {
          const parcela = pricing.parcela_12.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
          paymentLabel = `12x de ${parcela} no Cartão`;
        } else if (!isParcelado && pricing.avista) {
          const avista = pricing.avista.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
          paymentLabel = `PIX à Vista ${avista}`;
        }
      }
      // Fallback genérico se backend não enviou pricing
      if (!paymentLabel) {
        paymentLabel = paymentMethod === 'pix_avista' ? 'PIX à Vista' : '12x no Cartão';
      }
      
      this.checkoutPlanData = {
        type,
        pages,
        paymentMethod,
        packageLabel,
        paymentLabel,
        specialistOnboarding: suggestedPlan.specialistOnboarding || false
      };
      
      // Limpar form e erros
      this.checkoutForm = { name: '', email: '', whatsapp: '' };
      this.checkoutErrors = {};
      
      // Mostrar modal após breve delay para o usuário ler a mensagem de fechamento
      setTimeout(() => {
        this.showCheckoutModal = true;
      }, 2000);
    },
    
    closeCheckoutModal() {
      this.showCheckoutModal = false;
      this.isProcessingCheckout = false;
      this.checkoutProgress = 0;
      // Reativar chat para continuar conversando se quiser
      this.finished = false;
    },
    
    formatCheckoutWhatsApp() {
      let value = this.checkoutForm.whatsapp.replace(/\D/g, '');
      if (value.length > 11) value = value.slice(0, 11);
      
      if (value.length > 6) {
        value = `(${value.slice(0, 2)}) ${value.slice(2, 7)}-${value.slice(7)}`;
      } else if (value.length > 2) {
        value = `(${value.slice(0, 2)}) ${value.slice(2)}`;
      } else if (value.length > 0) {
        value = `(${value}`;
      }
      
      this.checkoutForm.whatsapp = value;
    },
    
    validateCheckoutForm() {
      this.checkoutErrors = {};
      let isValid = true;
      
      if (!this.checkoutForm.name || this.checkoutForm.name.trim().length < 3) {
        this.checkoutErrors.name = 'Digite seu nome completo';
        isValid = false;
      }
      
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!this.checkoutForm.email || !emailRegex.test(this.checkoutForm.email)) {
        this.checkoutErrors.email = 'Digite um e-mail válido';
        isValid = false;
      }
      
      const phoneDigits = this.checkoutForm.whatsapp.replace(/\D/g, '');
      if (phoneDigits.length < 10) {
        this.checkoutErrors.whatsapp = 'Digite um WhatsApp válido';
        isValid = false;
      }
      
      return isValid;
    },
    
    async submitDirectCheckout() {
      if (!this.validateCheckoutForm()) return;
      
      this.isProcessingCheckout = true;
      this.checkoutProgress = 0;
      
      // Animação de progresso
      const progressInterval = setInterval(() => {
        if (this.checkoutProgress < 85) {
          this.checkoutProgress += Math.random() * 12;
        }
      }, 400);
      
      try {
        // 1. Montar dados do pedido (igual ao QuickCheckout)
        const pagesAsObject = {};
        this.checkoutPlanData.pages.forEach(page => {
          pagesAsObject[page] = 1;
        });
        
        const paymentMethod = this.checkoutPlanData.paymentMethod === 'pix_avista'
          ? 'avista'
          : 'prazo';
        
        const orderData = {
          product: this.checkoutPlanData.type || 'site_complete',
          pages: pagesAsObject,
          content: [],
          custom_pages: [],
          video_basic_quantity: 0,
          video_pro_quantity: 0,
          service_addons: {
            specialist_onboarding: this.checkoutPlanData.specialistOnboarding || false
          },
          briefing: {
            customer_name: this.checkoutForm.name.trim(),
            email: this.checkoutForm.email.trim(),
            whatsapp: this.checkoutForm.whatsapp
          },
          payment_method: paymentMethod
        };
        
        console.log('📦 [SDRChat→Checkout] Criando pedido:', orderData);
        
        // 2. Criar pedido no backend (recalcula preço do zero)
        const orderResponse = await fetch('/api/order_create.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(orderData)
        });
        
        const orderResult = await orderResponse.json();
        console.log('📦 [SDRChat→Checkout] Resultado do pedido:', orderResult);
        
        if (!orderResult.ok) {
          throw new Error(orderResult.error || 'Erro ao criar pedido');
        }
        
        // Atualizar paymentLabel com preço real calculado pelo backend
        if (orderResult.pricing) {
          const pricing = orderResult.pricing;
          const isParcelado = paymentMethod === 'prazo';
          if (isParcelado && pricing.parcela_12) {
            const parcela = pricing.parcela_12.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            this.checkoutPlanData.paymentLabel = `12x de ${parcela} no Cartão`;
          } else if (!isParcelado && pricing.avista) {
            const avista = pricing.avista.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            this.checkoutPlanData.paymentLabel = `PIX à Vista ${avista}`;
          }
        }
        
        this.checkoutProgress = 60;
        
        // 3. Criar link de pagamento no Pagar.me
        const preferenceData = {
          order_id: orderResult.order_id,
          payer_name: this.checkoutForm.name.trim(),
          payer_email: this.checkoutForm.email.trim(),
          payer_phone: this.checkoutForm.whatsapp.replace(/\D/g, ''), // Apenas números
          payment_type: paymentMethod
        };
        
        console.log('💳 [SDRChat→Checkout] Criando link Pagar.me:', preferenceData);
        
        const prefResponse = await fetch('/api/create_preference.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(preferenceData)
        });
        
        const prefResult = await prefResponse.json();
        console.log('💳 [SDRChat→Checkout] Resultado Pagar.me:', prefResult);
        
        clearInterval(progressInterval);
        this.checkoutProgress = 100;
        
        // payment_url = Pagar.me | init_point = compatibilidade
        const checkoutUrl = prefResult.payment_url || prefResult.init_point;
        
        if (prefResult.success && checkoutUrl) {
          // 4. Emitir evento para o pai redirecionar (ou redirecionar direto)
          console.log('🚀 [SDRChat→Checkout] Redirecionando para:', checkoutUrl);
          
          this.$emit('checkout-redirect', {
            payment_url: checkoutUrl,
            init_point: checkoutUrl, // compatibilidade
            order_id: orderResult.order_id,
            payment_method: paymentMethod,
            pricing: orderResult.pricing
          });
          
          // Redirecionar para checkout do Pagar.me
          window.location.href = checkoutUrl;
        } else {
          throw new Error(prefResult.message || 'Erro ao criar link de pagamento');
        }
        
      } catch (error) {
        console.error('❌ [SDRChat→Checkout] Erro:', error);
        clearInterval(progressInterval);
        this.isProcessingCheckout = false;
        this.checkoutProgress = 0;
        alert(`Erro ao processar pagamento: ${error.message}\n\nPor favor, tente novamente ou entre em contato.`);
      }
    }
  }
}
</script>

<style lang="scss" scoped>
// ==================
// VARIABLES
// ==================
$primary: #8B5CF6;
$primary-light: #A78BFA;
$secondary: #10B981;
$dark: #1A1A2E;
$darker: #0F0F1A;
$glass-bg: rgba(255, 255, 255, 0.05);
$glass-border: rgba(255, 255, 255, 0.1);
$text-primary: #FFFFFF;
$text-secondary: rgba(255, 255, 255, 0.7);
$text-muted: rgba(255, 255, 255, 0.5);

// ==================
// BASE
// ==================
.sdr-chat-assistant {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  height: 100dvh;
  max-height: 100dvh;
  background: linear-gradient(135deg, $darker 0%, $dark 100%);
  overflow: hidden;
  font-family: 'Inter', -apple-system, sans-serif;
}

// Chat slide-up transition
.chat-slide-up-enter-active {
  transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
}
.chat-slide-up-leave-active {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s ease;
}
.chat-slide-up-enter-from {
  transform: translateY(100%);
  opacity: 0;
}
.chat-slide-up-leave-to {
  transform: translateY(100%);
  opacity: 0;
}

.ambient-background {
  position: absolute;
  inset: 0;
  background: 
    radial-gradient(circle at 20% 20%, rgba($primary, 0.15) 0%, transparent 40%),
    radial-gradient(circle at 80% 80%, rgba($secondary, 0.1) 0%, transparent 40%);
  pointer-events: none;
  z-index: 0;
}

// ==================
// GLASS EFFECTS
// ==================
.glass-effect, .glass-panel {
  background: $glass-bg;
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid $glass-border;
  border-radius: 16px;
}

// ==================
// HEADER
// ==================
.chat-header {
  position: relative;
  z-index: 10;
  padding: 20px 24px;
  border-radius: 0 0 24px 24px;
  
  .header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
  }
  
  .assistant-info {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  
  .avatar-ring {
    position: relative;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, $primary, $secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    
    &.pulse-ring::before {
      content: '';
      position: absolute;
      inset: -4px;
      border-radius: 50%;
      border: 2px solid $primary;
      animation: pulse-ring 2s infinite;
    }
  }
  
  .avatar-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: $darker;
    display: flex;
    align-items: center;
    justify-content: center;
    
    i {
      font-size: 18px;
      color: $primary-light;
    }
  }
  
  .text-container {
    .assistant-name {
      font-size: 18px;
      font-weight: 600;
      color: $text-primary;
      margin: 0;
      
      .badge {
        font-size: 10px;
        font-weight: 500;
        padding: 3px 8px;
        background: linear-gradient(135deg, $primary, $secondary);
        border-radius: 20px;
        margin-left: 8px;
        vertical-align: middle;
      }
    }
    
    .status-text {
      font-size: 13px;
      color: $text-secondary;
      display: flex;
      align-items: center;
      gap: 6px;
    }
  }
  
  .pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: $secondary;
    animation: pulse 2s infinite;
    
    &.online {
      background: $secondary;
    }
  }
  
  .header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
  }

  .btn-skip-to-form {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: rgba($primary, 0.2);
    border: 1px solid rgba($primary, 0.3);
    border-radius: 12px;
    color: $primary-light;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    
    &:hover {
      background: rgba($primary, 0.3);
      transform: translateY(-2px);
    }
    
    .btn-text {
      @media (max-width: 600px) {
        display: none;
      }
    }
  }

  .btn-close-chat {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 50%;
    color: $text-secondary;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.25s ease;
    flex-shrink: 0;

    &:hover {
      background: rgba(255,255,255,0.14);
      color: $text-primary;
      transform: scale(1.08);
    }

    &:active {
      transform: scale(0.96);
    }
  }
}

// ==================
// STAGE TRACKER
// ==================
.stage-tracker {
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
  padding: 0 20px;
  
  .stage-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    z-index: 2;
    
    .stage-dot {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: rgba($primary, 0.2);
      border: 2px solid rgba($primary, 0.3);
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
      
      i {
        font-size: 12px;
        color: $text-muted;
      }
    }
    
    .stage-label {
      font-size: 11px;
      color: $text-muted;
      white-space: nowrap;
      
      @media (max-width: 500px) {
        display: none;
      }
    }
    
    &.active .stage-dot {
      background: linear-gradient(135deg, $primary, $secondary);
      border-color: $primary;
      transform: scale(1.1);
      
      i { color: white; }
    }
    
    &.active .stage-label {
      color: $primary-light;
      font-weight: 600;
    }
    
    &.completed .stage-dot {
      background: $secondary;
      border-color: $secondary;
      
      i { color: white; }
    }
  }
  
  .stage-line {
    position: absolute;
    left: 40px;
    right: 40px;
    top: 16px;
    height: 4px;
    background: rgba($primary, 0.2);
    border-radius: 2px;
    z-index: 1;
    
    .stage-fill {
      height: 100%;
      background: linear-gradient(90deg, $secondary, $primary);
      border-radius: 2px;
      transition: width 0.5s ease;
    }
  }
}

// ==================
// CHAT CONTAINER
// ==================
.chat-container {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  z-index: 1;
  
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

.chat-messages {
  display: flex;
  flex-direction: column;
  gap: 16px;
  max-width: 800px;
  margin: 0 auto;
}

// ==================
// WELCOME CARD
// ==================
.welcome-card {
  text-align: center;
  padding: 40px 30px;
  max-width: 500px;
  margin: 40px auto;
  
  h3 {
    font-size: 24px;
    font-weight: 600;
    color: $text-primary;
    margin: 0 0 12px;
  }
  
  p {
    font-size: 15px;
    color: $text-secondary;
    margin: 0 0 24px;
    line-height: 1.6;
  }
  
  .quick-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }
  
  // Quick-Reply bubbles – parecem bolhas de mensagem interativas
  .quick-reply-bubble {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    align-self: flex-start;
    padding: 12px 18px;
    background: rgba($primary, 0.12);
    border: 1.5px solid rgba($primary, 0.35);
    border-radius: 20px 20px 20px 4px;
    color: $primary-light;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.25s ease;
    animation: bubble-pulse 2.5s ease-in-out infinite;

    &:nth-child(2) { animation-delay: 0.4s; }

    &:hover, &:focus-visible {
      background: rgba($primary, 0.25);
      border-color: $primary-light;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba($primary, 0.25);
      outline: none;
      animation: none;
    }

    &:active {
      transform: translateY(0);
    }

    i {
      font-size: 15px;
      color: $primary-light;
    }
  }

  @keyframes bubble-pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba($primary, 0); }
    50% { box-shadow: 0 0 0 5px rgba($primary, 0.12); }
  }
}

// ==================
// MESSAGE BUBBLES
// ==================
.message-row {
  display: flex;
  align-items: flex-end;
  gap: 10px;
  animation: message-slide-in 0.3s ease-out;
  
  &.user {
    flex-direction: row-reverse;
    
    .message-bubble {
      background: linear-gradient(135deg, $primary, darken($primary, 10%));
      border: none;
      border-radius: 18px 18px 4px 18px;
    }
  }
  
  &.assistant {
    .message-bubble {
      border-radius: 18px 18px 18px 4px;
    }
  }
}

.message-bubble {
  max-width: 75%;
  padding: 14px 18px;
  
  .message-content {
    font-size: 14px;
    line-height: 1.6;
    color: $text-primary;
    word-wrap: break-word;
  }
  
  .message-time {
    font-size: 11px;
    color: $text-muted;
    margin-top: 6px;
    text-align: right;
  }
  
  &.typing {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 16px 20px;
    
    .typing-text {
      font-size: 13px;
      color: $text-muted;
      margin-right: 4px;
    }
    
    .dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: $primary-light;
      animation: typing-bounce 1.4s infinite ease-in-out;
      
      &:nth-child(2) { animation-delay: 0.2s; }
      &:nth-child(3) { animation-delay: 0.4s; }
      &:nth-child(4) { animation-delay: 0.6s; }
    }
  }
}

.bot-mini-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: linear-gradient(135deg, $primary, $secondary);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  
  i {
    font-size: 12px;
    color: white;
  }
}

// ==================
// INPUT AREA
// ==================
.input-area-fixed {
  position: relative;
  z-index: 10;
  padding: 16px 20px 24px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.input-area {
  display: flex;
  align-items: flex-end;
  gap: 12px;
  padding: 12px 16px;
  
  textarea {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    color: $text-primary;
    font-size: 14px;
    font-family: inherit;
    resize: none;
    line-height: 1.5;
    max-height: 120px;
    
    &::placeholder {
      color: $text-muted;
    }
    
    &:disabled {
      opacity: 0.5;
    }
  }
  
  .btn-send {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba($primary, 0.3);
    border: none;
    color: $text-muted;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    
    &.ready {
      background: linear-gradient(135deg, $primary, $secondary);
      color: white;
      
      &:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 15px rgba($primary, 0.4);
      }
    }
    
    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  }
}

.btn-finish-floating {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 14px 24px;
  background: linear-gradient(135deg, $secondary, darken($secondary, 10%));
  border: none;
  border-radius: 12px;
  color: white;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  
  &:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba($secondary, 0.3);
  }
  
  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
}

// Botão WhatsApp para desenvolvimento customizado
.btn-whatsapp-custom-dev {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 14px 24px;
  background: linear-gradient(135deg, #25D366, #128C7E);
  border: none;
  border-radius: 12px;
  color: white;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  animation: pulse-whatsapp 2s ease-in-out infinite;
  
  i {
    font-size: 18px;
  }
  
  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(#25D366, 0.4);
  }
}

@keyframes pulse-whatsapp {
  0%, 100% {
    box-shadow: 0 4px 15px rgba(#25D366, 0.3);
  }
  50% {
    box-shadow: 0 4px 25px rgba(#25D366, 0.5);
  }
}

// ==================
// EXTRACTION MODAL & CHECKOUT MODAL
// ==================
.extraction-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  padding: 20px;
}

.extraction-modal {
  max-width: 450px;
  width: 100%;
  padding: 40px 30px;
  text-align: center;
}

// Checkout modal overrides
.checkout-modal {
  max-width: 480px;
  
  .checkout-modal-content {
    .success-icon {
      font-size: 52px;
      margin-bottom: 20px;
      animation: float 2s ease-in-out infinite;
    }
    
    h3 {
      font-size: 26px;
      font-weight: 700;
      color: $text-primary;
      margin: 0 0 12px;
      letter-spacing: -0.5px;
    }
    
    p {
      font-size: 15px;
      color: $text-secondary;
      margin: 0 0 24px;
      line-height: 1.5;
    }
    
    // Resumo do plano - card destacado
    .extracted-summary {
      background: linear-gradient(135deg, rgba($primary, 0.15), rgba($secondary, 0.1));
      border: 1px solid rgba($primary, 0.3);
      border-radius: 16px;
      padding: 20px;
      margin-bottom: 28px;
      text-align: left;
      box-shadow: 0 4px 16px rgba($primary, 0.15);
      
      .summary-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 10px 0;
        color: $text-primary;
        font-size: 15px;
        font-weight: 500;
        transition: all 0.2s ease;
        
        i {
          width: 24px;
          height: 24px;
          display: flex;
          align-items: center;
          justify-content: center;
          color: $primary-light;
          font-size: 16px;
          background: rgba($primary, 0.2);
          border-radius: 8px;
          flex-shrink: 0;
        }
        
        span {
          color: $text-primary;
          font-weight: 600;
          flex: 1;
        }
        
        &:not(:last-child) {
          border-bottom: 1px solid rgba($glass-border, 0.5);
        }
        
        // Highlight para pagamento
        &:last-child {
          margin-top: 4px;
          padding-top: 14px;
          
          i {
            background: rgba($secondary, 0.25);
            color: $secondary;
          }
          
          span {
            color: $secondary;
            font-weight: 700;
            font-size: 16px;
          }
        }
      }
    }
  }
  
  .checkout-mini-form {
    margin-top: 16px;
    
    .mini-form-group {
      margin-bottom: 14px;
      text-align: left;
      
      input {
        width: 100%;
        padding: 14px 18px;
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid $glass-border;
        border-radius: 12px;
        color: $text-primary;
        font-size: 15px;
        font-weight: 500;
        font-family: inherit;
        transition: all 0.3s ease;
        box-sizing: border-box;
        
        &::placeholder {
          color: rgba($text-muted, 0.8);
          font-weight: 400;
        }
        
        &:focus {
          outline: none;
          border-color: $primary;
          background: rgba(255, 255, 255, 0.15);
          box-shadow: 0 0 0 4px rgba($primary, 0.2);
        }
        
        &.has-error {
          border-color: #EF4444;
          background: rgba(239, 68, 68, 0.1);
        }
      }
      
      .mini-error {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: #EF4444;
        margin-top: 6px;
        padding-left: 6px;
      }
    }
    
    .modal-actions {
      margin-top: 24px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      
      button {
        width: 100%;
      }
      
      .btn-primary, button[type="submit"] {
        padding: 16px 28px;
        background: linear-gradient(135deg, $primary, $secondary);
        border: none;
        border-radius: 14px;
        color: white;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        box-shadow: 0 4px 16px rgba($primary, 0.3);
        
        &:hover:not(:disabled) {
          transform: translateY(-3px);
          box-shadow: 0 8px 24px rgba($primary, 0.4);
        }
        
        &:active:not(:disabled) {
          transform: translateY(-1px);
        }
        
        &:disabled {
          opacity: 0.6;
          cursor: not-allowed;
        }
        
        i {
          font-size: 18px;
        }
      }
      
      .btn-secondary, button[type="button"] {
        padding: 14px 24px;
        background: transparent;
        border: 2px solid $glass-border;
        border-radius: 14px;
        color: $text-secondary;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        
        &:hover {
          background: $glass-bg;
          border-color: rgba($primary, 0.4);
          color: $text-primary;
          transform: translateY(-1px);
        }
      }
    }
  }
}

.extraction-loading {
  .loading-animation {
    position: relative;
    width: 100px;
    height: 100px;
    margin: 0 auto 24px;
    
    .pulse-ring {
      position: absolute;
      inset: 0;
      border-radius: 50%;
      border: 3px solid $primary;
      animation: pulse-ring 2s infinite;
    }
    
    .brain-icon {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 40px;
      animation: float 2s ease-in-out infinite;
    }
  }
  
  h3 {
    font-size: 20px;
    font-weight: 600;
    color: $text-primary;
    margin: 0 0 8px;
  }
  
  p {
    font-size: 14px;
    color: $text-secondary;
    margin: 0 0 24px;
  }
  
  .progress-bar-extraction {
    height: 6px;
    background: rgba($primary, 0.2);
    border-radius: 3px;
    overflow: hidden;
    
    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, $primary, $secondary);
      transition: width 0.3s ease;
    }
  }
}

.extraction-success {
  .success-icon {
    font-size: 48px;
    margin-bottom: 16px;
  }
  
  h3 {
    font-size: 22px;
    font-weight: 600;
    color: $text-primary;
    margin: 0 0 8px;
  }
  
  p {
    font-size: 14px;
    color: $text-secondary;
    margin: 0 0 24px;
  }
  
  .extracted-summary {
    background: rgba($primary, 0.1);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 24px;
    text-align: left;
    
    .summary-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 8px 0;
      color: $text-primary;
      font-size: 14px;
      
      i {
        width: 20px;
        color: $primary-light;
      }
      
      &:not(:last-child) {
        border-bottom: 1px solid $glass-border;
      }
    }
  }
  
  .modal-actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
    
    .btn-primary {
      padding: 16px 28px;
      background: linear-gradient(135deg, $primary, $secondary);
      border: none;
      border-radius: 14px;
      color: white;
      font-size: 16px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      box-shadow: 0 4px 16px rgba($primary, 0.3);
      
      &:hover:not(:disabled) {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba($primary, 0.4);
      }
      
      &:active:not(:disabled) {
        transform: translateY(-1px);
      }
      
      &:disabled {
        opacity: 0.6;
        cursor: not-allowed;
      }
      
      i {
        font-size: 18px;
      }
    }
    
    .btn-secondary {
      padding: 14px 24px;
      background: transparent;
      border: 2px solid $glass-border;
      border-radius: 14px;
      color: $text-secondary;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      
      &:hover {
        background: $glass-bg;
        border-color: rgba($primary, 0.4);
        color: $text-primary;
        transform: translateY(-1px);
      }
    }
  }
}

// ==================
// ANIMATIONS
// ==================
@keyframes pulse-ring {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  100% {
    transform: scale(1.3);
    opacity: 0;
  }
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

@keyframes typing-bounce {
  0%, 80%, 100% {
    transform: translateY(0);
  }
  40% {
    transform: translateY(-6px);
  }
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}

@keyframes message-slide-in {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

// Transition classes
.message-slide-enter-active,
.message-slide-leave-active {
  transition: all 0.3s ease;
}

.message-slide-enter-from,
.message-slide-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: all 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
  
  .extraction-modal {
    transform: scale(0.9);
  }
}

// ==================
// RESPONSIVE
// ==================
@media (max-width: 600px) {
  .chat-header {
    padding: 16px;
    
    .assistant-name .badge {
      display: none;
    }
  }
  
  .stage-tracker {
    padding: 0 10px;
    
    .stage-dot {
      width: 28px;
      height: 28px;
    }
  }
  
  .message-bubble {
    max-width: 85%;
  }
  
  .welcome-card {
    padding: 30px 20px;
    margin: 20px auto;
    
    h3 { font-size: 20px; }
    p { font-size: 14px; }
  }
}
</style>
