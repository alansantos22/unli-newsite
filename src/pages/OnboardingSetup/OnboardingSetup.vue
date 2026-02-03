<template>
  <div class="onboarding-setup">
    <!-- Loading State -->
    <div v-if="isLoading" class="loading-overlay">
      <div class="loading-content">
        <div class="spinner"></div>
        <p>Validando seu acesso...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-container">
      <div class="error-content">
        <i class="fas fa-exclamation-triangle"></i>
        <h2>Ops! Algo deu errado</h2>
        <p>{{ error }}</p>
        <a href="mailto:contato@unli.com.br" class="btn-support">
          <i class="fas fa-envelope"></i>
          Contatar Suporte
        </a>
      </div>
    </div>

    <!-- Already Completed State -->
    <div v-else-if="orderStatus === 'concluido'" class="completed-container">
      <div class="completed-content">
        <i class="fas fa-check-circle"></i>
        <h2>Briefing já enviado!</h2>
        <p>Seu briefing foi recebido e nossa equipe já está trabalhando no seu site.</p>
        <p class="info">Qualquer dúvida, entre em contato conosco.</p>
      </div>
    </div>

    <!-- Main Flow -->
    <div v-else class="setup-flow">
      <!-- Phase 1: Brand Consultant (Pre-registration) -->
      <div v-if="currentPhase === 'consultant'" class="phase-consultant">
        <BrandConsultant
          ref="brandConsultant"
          :session-id="token"
          :customer-name="customerName"
          :initial-context="orderContext"
          @data-extracted="handleDataExtracted"
          @conversation-saved="handleConversationSaved"
        />
      </div>

      <!-- Phase 2: Form Wizard -->
      <div v-else-if="currentPhase === 'form'" class="phase-form">
        <!-- Botão flutuante para voltar ao chat -->
        <button class="btn-back-floating" @click="goBackToConsultant" title="Voltar para a conversa">
          <i class="fas fa-comments"></i>
          <span>Refinar com IA</span>
        </button>
        
        <!-- DynamicOnboardingWizard com dados pré-preenchidos -->
        <DynamicOnboardingWizard
          ref="onboardingWizard"
          :session-id="token"
          :initial-data="wizardInitialData"
          :purchased-pages="purchasedPages"
          :save-endpoint="apiBaseUrl + '/api/onboarding/save-draft.php'"
          :submit-endpoint="apiBaseUrl + '/api/onboarding/submit.php'"
          @complete="handleWizardComplete"
          @step-change="handleStepChange"
          @save="handleWizardSave"
          @error="handleWizardError"
        />
      </div>
    </div>
  </div>
</template>

<script>
import BrandConsultant from '@/shared/Components/BrandConsultant.vue';
import DynamicOnboardingWizard from '@/shared/Components/DynamicOnboardingWizard.vue';

// Cookie helpers
const COOKIE_NAME = 'unli_consultant_completed';
const COOKIE_EXPIRY_DAYS = 30;

function setCookie(name, value, days) {
  const date = new Date();
  date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
  document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/;SameSite=Lax`;
}

function getCookie(name) {
  const nameEQ = name + '=';
  const ca = document.cookie.split(';');
  for (let i = 0; i < ca.length; i++) {
    let c = ca[i].trim();
    if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length);
  }
  return null;
}

// LocalStorage keys
const STORAGE_KEYS = {
  CHAT_HISTORY: (token) => `unli_chat_history_${token}`,
  EXTRACTED_DATA: (token) => `unli_extracted_data_${token}`,
  CURRENT_PHASE: (token) => `unli_current_phase_${token}`
};

export default {
  name: 'OnboardingSetup',
  
  components: {
    BrandConsultant,
    DynamicOnboardingWizard
  },
  
  data() {
    return {
      // Token from URL
      token: null,
      
      // API Response
      isLoading: true,
      error: null,
      orderData: null,
      orderStatus: null,
      customerName: '',
      orderContext: {},
      
      // Flow control
      currentPhase: 'consultant', // 'consultant' | 'form'
      
      // Extracted data from consultant
      extractedData: null,
      
      // Purchased pages from order
      purchasedPages: [],
      
      // API base URL
      apiBaseUrl: process.env.VUE_APP_API_BASE_URL || ''
    };
  },
  
  computed: {
    /**
     * Check if user has already completed the consultant phase for this token
     */
    hasCompletedConsultant() {
      const cookieValue = getCookie(COOKIE_NAME);
      return cookieValue === this.token;
    },
    
    /**
     * Transform extracted data to wizard's initialData format
     * Maps BrandConsultant output to DynamicOnboardingWizard fields
     */
    wizardInitialData() {
      if (!this.extractedData) return {};
      
      const data = this.extractedData;
      
      return {
        // Step: Identidade
        identity: {
          companyName: data.companyInfo?.name || '',
          segment: data.companyInfo?.niche || '',
          niche: data.companyInfo?.niche || '',
          voiceTone: data.aiSuggestions?.toneOfVoice || 'profissional',
          slogan: data.aiSuggestions?.suggestedTagline || ''
        },
        
        // Step: Sobre (About)
        about: {
          history: data.brandCore?.historySummary || '',
          mission: data.brandCore?.mission || '',
          values: Array.isArray(data.brandCore?.values) 
            ? data.brandCore.values.join(', ') 
            : data.brandCore?.values || '',
          differentials: data.differentiation?.usp || '',
          methodology: data.differentiation?.uniqueMethodology || ''
        },
        
        // Step: Serviços (Services) 
        services: {
          mainService: data.companyInfo?.mainProduct || '',
          targetAudience: data.companyInfo?.targetAudience || ''
        },
        
        // Step: Autoridade/Prova Social
        authority: {
          yearsInMarket: data.authorityTriggers?.yearsInMarket || '',
          certifications: Array.isArray(data.authorityTriggers?.certifications)
            ? data.authorityTriggers.certifications.join(', ')
            : '',
          achievements: data.authorityTriggers?.keyAchievements || '',
          clients: Array.isArray(data.authorityTriggers?.notableClients)
            ? data.authorityTriggers.notableClients.join(', ')
            : ''
        },
        
        // Step: SEO/Marketing
        seo: {
          keywords: Array.isArray(data.aiSuggestions?.suggestedKeywords)
            ? data.aiSuggestions.suggestedKeywords.join(', ')
            : '',
          metaDescription: data.companyInfo?.mainProduct || ''
        },
        
        // Visual Identity (para uso futuro)
        visualIdentity: data.visualIdentity || {},
        
        // Meta info
        _consultantData: {
          confidence: data.extractionMeta?.confidence || 'medium',
          extractedAt: data.extractionMeta?.extractedAt || new Date().toISOString(),
          contentSeeds: data.contentSeeds || {}
        }
      };
    }
  },
  
  async mounted() {
    // Get token from URL
    this.token = this.$route.query.token;
    
    if (!this.token) {
      this.error = 'Link inválido. Verifique o e-mail recebido e tente novamente.';
      this.isLoading = false;
      return;
    }
    
    // Validate token with API
    await this.validateToken();
    
    // If valid, restore state from localStorage
    if (!this.error) {
      this.restoreState();
    }
  },
  
  methods: {
    /**
     * Validate onboarding token with API
     */
    async validateToken() {
      try {
        const response = await fetch(
          `${this.apiBaseUrl}/api/onboarding/validate.php?token=${this.token}`
        );
        
        const data = await response.json();
        
        if (!response.ok || !data.success) {
          this.error = data.message || 'Token inválido ou expirado.';
          return;
        }
        
        // Store order data
        this.orderData = data;
        this.orderStatus = data.status;
        this.customerName = data.customerName || '';
        
        // Build context for consultant
        this.orderContext = {
          orderId: data.orderId,
          planName: data.orderDetails?.planName || 'Site Vitrine',
          customerEmail: data.email
        };
        
        // Check if has saved briefing (partial or complete)
        if (data.briefing && Object.keys(data.briefing).length > 0) {
          this.extractedData = data.briefing;
        }
        
        // Extract purchased pages from order
        this.purchasedPages = this.extractPurchasedPages(data);
        
      } catch (err) {
        console.error('Validation error:', err);
        this.error = 'Erro ao validar seu acesso. Tente novamente mais tarde.';
      } finally {
        this.isLoading = false;
      }
    },
    
    /**
     * Restore state from localStorage
     */
    restoreState() {
      try {
        // Restore current phase
        const savedPhase = localStorage.getItem(STORAGE_KEYS.CURRENT_PHASE(this.token));
        if (savedPhase) {
          this.currentPhase = savedPhase;
        }
        
        // Restore extracted data
        const savedData = localStorage.getItem(STORAGE_KEYS.EXTRACTED_DATA(this.token));
        if (savedData) {
          this.extractedData = JSON.parse(savedData);
        }
        
        // If user completed consultant before, go directly to form (unless they want to go back)
        if (this.hasCompletedConsultant && this.extractedData) {
          this.currentPhase = 'form';
        }
        
      } catch (err) {
        console.error('Error restoring state:', err);
        // Continue with default state
      }
    },
    
    /**
     * Handle data extracted from BrandConsultant
     */
    async handleDataExtracted(data) {
      this.extractedData = data;
      
      // Save to localStorage
      localStorage.setItem(
        STORAGE_KEYS.EXTRACTED_DATA(this.token), 
        JSON.stringify(data)
      );
      
      // Set cookie to remember consultant was completed
      setCookie(COOKIE_NAME, this.token, COOKIE_EXPIRY_DAYS);
      
      // Save to backend via save-draft
      await this.saveDraftToBackend(data);
      
      // Move to form phase
      this.currentPhase = 'form';
      localStorage.setItem(STORAGE_KEYS.CURRENT_PHASE(this.token), 'form');
    },
    
    /**
     * Handle conversation saved (called periodically or on page unload)
     */
    handleConversationSaved(chatHistory) {
      // Save chat history to localStorage
      localStorage.setItem(
        STORAGE_KEYS.CHAT_HISTORY(this.token),
        JSON.stringify(chatHistory)
      );
    },
    
    /**
     * Save draft to backend
     */
    async saveDraftToBackend(data) {
      try {
        // Transform extracted data to briefing format
        const briefingData = this.transformToBriefingFormat(data);
        
        const response = await fetch(`${this.apiBaseUrl}/api/onboarding/save-draft.php`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            session_id: this.token,
            data: briefingData,
            current_step: 0 // Consultant phase completed
          })
        });
        
        const result = await response.json();
        
        if (!result.success) {
          console.warn('Failed to save draft:', result.message);
        }
        
      } catch (err) {
        console.error('Error saving draft:', err);
      }
    },
    
    /**
     * Transform extracted consultant data to briefing format
     */
    transformToBriefingFormat(data) {
      return {
        // Identity
        companyName: data.companyInfo?.name || '',
        businessType: data.companyInfo?.niche || '',
        frase: data.aiSuggestions?.suggestedTagline || '',
        voiceTone: data.aiSuggestions?.toneOfVoice || '',
        
        // Brand Core
        description: data.companyInfo?.mainProduct || '',
        about: data.brandCore?.historySummary || '',
        mission: data.brandCore?.mission || '',
        values: data.brandCore?.values || [],
        
        // Differentiation
        differentials: [
          data.differentiation?.usp,
          data.differentiation?.uniqueMethodology
        ].filter(Boolean),
        
        // Visual Identity
        visualIdentity: data.visualIdentity || {},
        
        // SEO & Marketing
        keywords: data.aiSuggestions?.suggestedKeywords || [],
        targetAudience: data.companyInfo?.targetAudience || '',
        
        // Meta
        consultantCompleted: true,
        consultantConfidence: data.extractionMeta?.confidence || null,
        extractedAt: data.extractionMeta?.extractedAt || new Date().toISOString(),
        
        // Content Seeds (for AI generation later)
        contentSeeds: data.contentSeeds || {}
      };
    },
    
    /**
     * Go back to consultant phase
     */
    goBackToConsultant() {
      this.currentPhase = 'consultant';
      localStorage.setItem(STORAGE_KEYS.CURRENT_PHASE(this.token), 'consultant');
    },
    
    /**
     * Get saved chat history for restoration
     */
    getSavedChatHistory() {
      try {
        const saved = localStorage.getItem(STORAGE_KEYS.CHAT_HISTORY(this.token));
        return saved ? JSON.parse(saved) : [];
      } catch {
        return [];
      }
    },
    
    /**
     * Extract purchased pages from order data
     */
    extractPurchasedPages(orderData) {
      // Default pages for basic plan
      const defaultPages = ['home', 'about', 'services', 'contact'];
      
      // Check if order has specific pages info
      if (orderData?.orderDetails?.pages) {
        const pages = orderData.orderDetails.pages;
        
        // If it's an array, use directly
        if (Array.isArray(pages)) {
          return pages.length > 0 ? pages : defaultPages;
        }
        
        // If it's an object {about: 1, services: 0}, convert
        if (typeof pages === 'object') {
          const activePages = Object.entries(pages)
            .filter(([, value]) => value)
            .map(([key]) => key);
          return activePages.length > 0 ? activePages : defaultPages;
        }
      }
      
      // Check plan type for defaults
      const planName = orderData?.orderDetails?.planName?.toLowerCase() || '';
      if (planName.includes('premium') || planName.includes('profissional')) {
        return ['home', 'about', 'services', 'portfolio', 'testimonials', 'contact', 'blog'];
      }
      
      return defaultPages;
    },
    
    /**
     * Handle wizard completion
     */
    handleWizardComplete(finalData) {
      console.log('[OnboardingSetup] Wizard completed:', finalData);
      
      // Clear localStorage for this session
      localStorage.removeItem(STORAGE_KEYS.CURRENT_PHASE(this.token));
      localStorage.removeItem(STORAGE_KEYS.EXTRACTED_DATA(this.token));
      localStorage.removeItem(STORAGE_KEYS.CHAT_HISTORY(this.token));
      
      // Redirect to success page or show completion
      this.$router.push({ 
        name: 'PaymentSuccess', 
        query: { 
          type: 'briefing',
          orderId: this.orderData?.orderId 
        } 
      });
    },
    
    /**
     * Handle wizard step change
     */
    handleStepChange(stepIndex) {
      console.log('[OnboardingSetup] Step changed to:', stepIndex);
      // Could track progress analytics here
    },
    
    /**
     * Handle wizard save event
     */
    handleWizardSave(savedData) {
      console.log('[OnboardingSetup] Wizard saved:', savedData);
      // Additional save handling if needed
    },
    
    /**
     * Handle wizard error
     */
    handleWizardError(error) {
      console.error('[OnboardingSetup] Wizard error:', error);
      // Could show toast notification
    }
  },
  
  /**
   * Save state before page unload
   */
  beforeUnmount() {
    // Trigger save in BrandConsultant if exists
    if (this.$refs.brandConsultant) {
      this.$refs.brandConsultant.saveConversation?.();
    }
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/__colors.scss';

// Variables locais
$white: #ffffff;

.onboarding-setup {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 50%, #16213e 100%);
}

// Loading
.loading-overlay {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  
  .loading-content {
    text-align: center;
    color: $white;
    
    .spinner {
      width: 50px;
      height: 50px;
      border: 3px solid rgba(255, 255, 255, 0.1);
      border-top-color: $p-color;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 0 auto 20px;
    }
    
    p {
      font-size: 1.1rem;
      opacity: 0.8;
    }
  }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

// Error
.error-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 20px;
  
  .error-content {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 107, 107, 0.3);
    border-radius: 24px;
    padding: 60px 40px;
    text-align: center;
    max-width: 500px;
    
    i {
      font-size: 4rem;
      color: #ff6b6b;
      margin-bottom: 20px;
    }
    
    h2 {
      color: $white;
      font-size: 1.8rem;
      margin-bottom: 12px;
    }
    
    p {
      color: rgba(255, 255, 255, 0.7);
      font-size: 1.1rem;
      margin-bottom: 24px;
    }
    
    .btn-support {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 14px 28px;
      background: linear-gradient(135deg, $p-color 0%, darken($p-color, 10%) 100%);
      color: $white;
      text-decoration: none;
      border-radius: 12px;
      font-weight: 600;
      transition: all 0.3s;
      
      &:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba($p-color, 0.4);
      }
    }
  }
}

// Completed
.completed-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 20px;
  
  .completed-content {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(74, 222, 128, 0.3);
    border-radius: 24px;
    padding: 60px 40px;
    text-align: center;
    max-width: 500px;
    
    i {
      font-size: 4rem;
      color: #4ade80;
      margin-bottom: 20px;
    }
    
    h2 {
      color: $white;
      font-size: 1.8rem;
      margin-bottom: 12px;
    }
    
    p {
      color: rgba(255, 255, 255, 0.7);
      font-size: 1.1rem;
      margin-bottom: 8px;
    }
    
    .info {
      font-size: 0.95rem;
      opacity: 0.6;
    }
  }
}

// Setup Flow
.setup-flow {
  min-height: 100vh;
}

// Phase Consultant
.phase-consultant {
  // BrandConsultant fills the space
}

// Phase Form - Wizard Container
.phase-form {
  position: relative;
  min-height: 100vh;
  
  // Botão flutuante para voltar ao chat
  .btn-back-floating {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 100;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 14px 20px;
    background: linear-gradient(135deg, $p-color 0%, darken($p-color, 15%) 100%);
    border: none;
    border-radius: 50px;
    color: $white;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba($p-color, 0.4);
    transition: all 0.3s;
    
    i {
      font-size: 1.1rem;
    }
    
    span {
      display: inline-block;
    }
    
    &:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 30px rgba($p-color, 0.5);
    }
    
    &:active {
      transform: translateY(-1px);
    }
  }
}

// Responsive
@media (max-width: 768px) {
  .error-container,
  .completed-container {
    .error-content,
    .completed-content {
      padding: 40px 24px;
      margin: 20px;
    }
  }
  
  .phase-form {
    .btn-back-floating {
      bottom: 16px;
      right: 16px;
      padding: 12px 16px;
      
      span {
        display: none; // Só mostra ícone no mobile
      }
    }
  }
}
</style>
