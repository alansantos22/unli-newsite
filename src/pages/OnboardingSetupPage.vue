<template>
  <div class="onboarding-setup-page">
    <!-- Loading State -->
    <div v-if="isLoading" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Carregando seus dados...</p>
    </div>
    
    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <div class="error-icon">⚠️</div>
      <h2>Ops! Algo deu errado</h2>
      <p>{{ error }}</p>
      <button @click="retryValidation" class="retry-btn">Tentar Novamente</button>
    </div>
    
    <!-- Wizard (só renderiza após carregar dados) -->
    <template v-else>
      <ConversationalOnboardingWizard
        v-if="!showTraditionalForm"
        :purchased-pages="purchasedPages"
        :initial-data="initialData"
        :session-id="sessionId"
        :initial-step="currentStepFromBackend"
        @complete="handleConversationalComplete"
        @go-to-form="showTraditionalForm = true"
        @error="handleError"
      />
      
      <!-- Traditional Form (after conversation) -->
      <DynamicOnboardingWizard
        v-else
        :purchased-pages="purchasedPages"
        :initial-data="conversationalData"
        :session-id="sessionId"
        @complete="handleComplete"
        @back-to-conversation="showTraditionalForm = false"
        @error="handleError"
      />
    </template>
  </div>
</template>

<script>
import DynamicOnboardingWizard from '@/shared/Components/DynamicOnboardingWizard.vue';
import ConversationalOnboardingWizard from '@/shared/Components/ConversationalOnboardingWizard.vue';

export default {
  name: 'OnboardingSetupPage',
  
  components: {
    DynamicOnboardingWizard,
    ConversationalOnboardingWizard
  },
  
  data() {
    return {
      isLoading: true,
      error: null,
      purchasedPages: [],
      initialData: {},
      sessionId: '',
      orderId: null,
      showTraditionalForm: false,
      conversationalData: {},
      currentStepFromBackend: 0
    };
  },
  
  async created() {
    await this.validateToken();
  },
  
  methods: {
    async validateToken() {
      this.isLoading = true;
      this.error = null;
      
      const token = this.$route.query.token;
      
      // Se não tem token, usa modo demo/teste
      if (!token) {
        console.log('[OnboardingSetup] Modo demo - sem token');
        this.loadDemoMode();
        return;
      }
      
      try {
        const response = await fetch(`/api/onboarding/validate.php?token=${encodeURIComponent(token)}`);
        const data = await response.json();
        
        if (!data.success) {
          this.error = data.message || 'Token inválido.';
          this.isLoading = false;
          return;
        }
        
        // Extrair dados do pedido
        this.orderId = data.orderId;
        this.sessionId = token;
        
        // Carregar páginas compradas do orderDetails
        let pages = [];
        
        // Páginas principais
        if (data.orderDetails && data.orderDetails.pages) {
          const pagesObj = data.orderDetails.pages;
          // Converte objeto para array de chaves com valor truthy
          Object.entries(pagesObj).forEach(([key, value]) => {
            if (value) pages.push(key);
          });
        }
        
        // Conteúdo de mídia (video_basic, video_pro, pdf)
        if (data.orderDetails && data.orderDetails.content) {
          const contentObj = data.orderDetails.content;
          Object.entries(contentObj).forEach(([key, value]) => {
            if (value) pages.push(key);
          });
        }
        
        // Se não encontrou nada, usa fallback
        if (pages.length === 0 && data.orderDetails && data.orderDetails.purchased_pages) {
          pages = data.orderDetails.purchased_pages;
        }
        
        this.purchasedPages = pages.length > 0 ? pages : ['sobre_nos', 'servicos'];
        
        console.log('[OnboardingSetup] Páginas compradas:', this.purchasedPages);
        
        // Carregar briefing existente se houver
        if (data.briefing) {
          this.initialData = data.briefing;
        }
        
        // Carregar current_step do backend se houver
        if (typeof data.currentStep === 'number') {
          this.currentStepFromBackend = data.currentStep;
          console.log('[OnboardingSetup] Step do backend:', data.currentStep);
        }
        
        this.isLoading = false;
        
      } catch (err) {
        console.error('[OnboardingSetup] Erro:', err);
        this.error = 'Erro ao conectar com o servidor. Tente novamente.';
        this.isLoading = false;
      }
    },
    
    loadDemoMode() {
      // Modo demo para desenvolvimento/teste
      this.purchasedPages = ['sobre_nos', 'servicos', 'faq'];
      this.initialData = {};
      this.sessionId = 'demo-' + Date.now();
      this.isLoading = false;
    },
    
    retryValidation() {
      this.validateToken();
    },
    
    handleConversationalComplete(data) {
      console.log('[OnboardingSetup] Conversa concluída:', data);
      
      // Salvar dados da conversa para passar ao formulário tradicional
      this.conversationalData = {
        ...this.initialData,
        ...data
      };
      
      // Perguntar se quer revisar no formulário ou finalizar direto
      const wantToReview = confirm(
        '🎉 Conversa finalizada!\n\n' +
        'Suas informações foram coletadas com sucesso.\n\n' +
        '✅ Clique em "OK" para revisar no formulário tradicional\n' +
        '❌ Clique em "Cancelar" para finalizar direto'
      );
      
      if (wantToReview) {
        // Ir para o formulário tradicional para revisão
        this.showTraditionalForm = true;
      } else {
        // Finalizar direto
        this.handleComplete(this.conversationalData);
      }
    },
    
    handleComplete(data) {
      console.log('[OnboardingSetup] Onboarding completo:', data);
      // Redirecionar para página de sucesso ou dashboard
      this.$router.push({
        name: 'PaymentSuccess',
        query: { from: 'onboarding' }
      });
    },
    
    handleError(error) {
      console.error('[OnboardingSetup] Erro no wizard:', error);
      this.error = error.message || 'Erro durante o preenchimento.';
    }
  }
};
</script>

<style lang="scss" scoped>
.onboarding-setup-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.loading-state,
.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  color: white;
  text-align: center;
  padding: 2rem;
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-state {
  .error-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
  }
  
  h2 {
    margin: 0 0 0.5rem 0;
    font-size: 1.5rem;
  }
  
  p {
    margin: 0 0 1.5rem 0;
    opacity: 0.9;
  }
}

.retry-btn {
  padding: 0.75rem 2rem;
  background: white;
  color: #667eea;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s;
  
  &:hover {
    transform: scale(1.05);
  }
}
</style>
