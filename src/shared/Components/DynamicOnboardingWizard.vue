<template>
  <div class="dynamic-onboarding-wizard" :class="{ 'is-desktop': !isMobile }">
    <!-- DESKTOP SIDEBAR -->
    <aside class="wizard-sidebar" v-if="!isMobile && !showContentGeneration">
      <div class="sidebar-brand">
        <span class="brand-icon">🎯</span>
        <span class="brand-text">Construtor de Site</span>
      </div>
      
      <nav class="sidebar-nav">
        <button
          v-for="(step, index) in steps"
          :key="step.id"
          class="nav-item"
          :class="{
            'is-active': index === currentStepIndex,
            'is-completed': index < currentStepIndex,
            'is-disabled': index > currentStepIndex
          }"
          @click="goToStep(index)"
          :disabled="index > currentStepIndex"
        >
          <span class="nav-indicator">
            <span v-if="index < currentStepIndex">✓</span>
            <span v-else>{{ index + 1 }}</span>
          </span>
          <span class="nav-label">{{ step.title }}</span>
        </button>
      </nav>
      
      <div class="sidebar-progress">
        <div class="progress-info">
          <span class="progress-value">{{ progressPercentage }}%</span>
          <span class="progress-label">completo</span>
        </div>
        <div class="progress-bar-mini">
          <div class="progress-fill" :style="{ width: progressPercentage + '%' }"></div>
        </div>
      </div>
    </aside>
    
    <!-- MAIN AREA -->
    <div class="wizard-main">
      <!-- Phase indicator -->
      <div v-if="showContentGeneration" class="phase-indicator">
        <span class="phase-badge">Fase 2: Geração de Conteúdo</span>
      </div>
      
      <!-- MOBILE: Progress Header -->
      <header class="wizard-header" v-if="!showContentGeneration && isMobile">
      <div class="progress-container">
        <div class="progress-bar">
          <div 
            class="progress-fill" 
            :style="{ width: progressPercentage + '%' }"
          />
        </div>
        <span class="progress-text">
          Passo {{ currentStepIndex + 1 }} de {{ steps.length }}
        </span>
      </div>
      
      <!-- Step Indicators -->
      <nav class="step-indicators">
        <button
          v-for="(step, index) in steps"
          :key="step.id"
          class="step-dot"
          :class="{
            'active': index === currentStepIndex,
            'completed': index < currentStepIndex,
            'clickable': index < currentStepIndex
          }"
          :title="step.title"
          @click="goToStep(index)"
          :disabled="index > currentStepIndex"
        >
          <span class="dot-icon">{{ step.icon }}</span>
        </button>
      </nav>
    </header>
    
    <!-- FASE 1: Coleta de Dados -->
    <main class="wizard-content" v-if="!showContentGeneration && !isPreparingContent">
      <TransitionGroup name="step-slide" mode="out-in">
        <section 
          v-for="(step, index) in steps"
          v-show="index === currentStepIndex"
          :key="step.id"
          class="step-section"
        >
          <!-- Step Header -->
          <div class="step-header">
            <span class="step-icon">{{ step.icon }}</span>
            <div class="step-titles">
              <h2 class="step-title">{{ step.title }}</h2>
              <p v-if="step.subtitle" class="step-subtitle">{{ step.subtitle }}</p>
            </div>
          </div>
          
          <!-- Step Fields (Grid 2 colunas no desktop) -->
          <div class="step-fields">
            <template v-for="field in visibleFields(step)" :key="field.id">
              <div class="field-wrapper" :class="getFieldSizeClass(field)">
                <DynamicField
                  :field="field"
                  :modelValue="formData[step.id]?.[field.id]"
                  :voice-tone="formData.identity?.voiceTone || 'profissional'"
                  :company-name="formData.identity?.companyName || ''"
                  :niche="formData.identity?.niche || formData.identity?.segment || ''"
                  @update:modelValue="updateField(step.id, field.id, $event)"
                  @cep-found="handleCepFound(step.id, $event)"
                />
              </div>
            </template>
          </div>
        </section>
      </TransitionGroup>
    </main>
    
    <!-- LOADING GAMIFICADO: Transição para fase 2 -->
    <main class="wizard-content preparing-content" v-else-if="isPreparingContent">
      <div class="preparing-container">
        <div class="preparing-animation">
          <div class="pulse-ring"></div>
          <div class="pulse-ring delay-1"></div>
          <div class="pulse-ring delay-2"></div>
          <div class="preparing-icon">⚙️</div>
        </div>
        
        <h2 class="preparing-title">Preparando seu site...</h2>
        <p class="preparing-message">{{ preparingMessage }}</p>
        
        <div class="preparing-progress">
          <div class="progress-bar">
            <div class="progress-fill" :style="{ width: preparingProgress + '%' }"></div>
          </div>
          <span class="progress-text">{{ preparingProgress }}%</span>
        </div>
        
        <p class="preparing-tip">Nossos especialistas estão trabalhando nos melhores textos para você</p>
      </div>
    </main>
    
    <!-- FASE 2: Geração de Conteúdo por Seções -->
    <main class="wizard-content content-generation-phase" v-else>
      <div class="generation-pages" v-if="currentGenerationPage === null">
        <h2 class="generation-title">🎉 Formulário concluído!</h2>
        <p class="generation-subtitle">Agora escolha uma página para gerar os textos com IA</p>
        
        <!-- Mostra páginas se existirem -->
        <div v-if="safePages.length > 0" class="pages-to-generate">
          <div 
            v-for="page in safePages" 
            :key="page"
            class="page-card"
            :class="{ 'is-done': generatedPages[page] }"
            @click="startPageGeneration(page)"
          >
            <span class="page-icon">{{ getPageIcon(page) }}</span>
            <span class="page-name">{{ getPageName(page) }}</span>
            <span v-if="generatedPages[page]" class="done-badge">✓</span>
          </div>
        </div>
        
        <!-- Fallback se não tiver páginas -->
        <div v-else class="no-pages-warning">
          <p>⚠️ Nenhuma página encontrada. Verifique seu pedido.</p>
          <p class="debug-info">Debug: purchasedPages = {{ purchasedPages }}</p>
        </div>
        
        <p class="ai-credits-hint">⚡ <strong>10 gerações de IA</strong> por dia</p>
      </div>
      
      <!-- Gerador de Seções para página selecionada -->
      <SectionContentGenerator
        v-if="currentGenerationPage"
        :page-type="currentGenerationPage"
        :onboarding-data="getPageOnboardingData(currentGenerationPage)"
        :voice-tone="formData.identity?.voiceTone || 'profissional'"
        :allow-add-sections="currentGenerationPage === 'faq'"
        :section-label="currentGenerationPage === 'faq' ? 'Pergunta' : 'Seção'"
        @update="handleSectionUpdate"
        @complete="handlePageComplete"
      />
    </main>
    
    <!-- Navigation Footer (esconde durante loading) -->
    <footer class="wizard-footer" v-if="!isPreparingContent">
      <button
        v-if="currentStepIndex > 0 || showContentGeneration"
        type="button"
        class="nav-btn prev"
        @click="handleBack"
      >
        ÔåÉ {{ showContentGeneration && currentGenerationPage ? 'Voltar' : 'Anterior' }}
      </button>
      <div v-else class="nav-spacer"></div>
      
      <div class="step-info">
        <span class="current-step-name">
          {{ showContentGeneration ? 'Geração de Conteúdo' : currentStep?.title }}
        </span>
      </div>
      
      <button
        type="button"
        class="nav-btn next"
        :class="{ 'is-final': isLastStep && !showContentGeneration }"
        :disabled="!canProceed && !showContentGeneration"
        @click="handleNext"
      >
        {{ getNextButtonText }}
      </button>
    </footer>
    
    <!-- Auto-save indicator -->
    <div v-if="isSaving" class="autosave-indicator">
      <span class="spinner-small"></span> Salvando...
    </div>
    </div><!-- /wizard-main -->
  </div>
</template>

<script>
import DynamicField from './DynamicField.vue';
import SectionContentGenerator from './SectionContentGenerator.vue';
import { buildOnboardingSteps } from '@/core/config/onboarding-steps.config.js';

export default {
  name: 'DynamicOnboardingWizard',
  
  components: {
    DynamicField,
    SectionContentGenerator
  },
  
  props: {
    // Tipos de páginas que o usuário comprou
    purchasedPages: {
      type: Array,
      default: () => []
    },
    // Dados pré-existentes (se retomando)
    initialData: {
      type: Object,
      default: () => ({})
    },
    // ID da sessão/pedido para auto-save
    sessionId: {
      type: String,
      default: ''
    },
    // Endpoint para salvar rascunho
    saveEndpoint: {
      type: String,
      default: '/api/onboarding/save-draft.php'
    },
    // Endpoint para submeter finalizado
    submitEndpoint: {
      type: String,
      default: '/api/onboarding/submit.php'
    }
  },
  
  emits: ['complete', 'step-change', 'save', 'error'],
  
  data() {
    return {
      currentStepIndex: 0,
      formData: {},
      isSaving: false,
      saveTimeout: null,
      autosaveInterval: null,
      hasUnsavedChanges: false,
      lastSavedData: null,
      validationErrors: {},
      // Fase 2: Geração de conteúdo
      showContentGeneration: false,
      isPreparingContent: false,
      preparingMessage: '',
      preparingProgress: 0,
      currentGenerationPage: null,
      generatedPages: {},
      generatedContent: {},
      // Layout responsivo
      isMobile: true
    };
  },
  
  computed: {
    // Array seguro de páginas compradas
    safePages() {
      // Se já é array, usa direto
      if (Array.isArray(this.purchasedPages)) {
        return this.purchasedPages;
      }
      
      // Se é objeto {about: 1, services: 0}, converte para array das chaves com valor truthy
      if (this.purchasedPages && typeof this.purchasedPages === 'object') {
        const pages = Object.entries(this.purchasedPages)
          .filter(([, value]) => value) // valor truthy (1, true, etc)
          .map(([pageKey]) => pageKey);
        console.log('[Wizard] Convertido objeto para array:', pages);
        return pages;
      }
      
      return [];
    },
    
    steps() {
      return buildOnboardingSteps(this.safePages);
    },
    
    currentStep() {
      return this.steps[this.currentStepIndex] || null;
    },
    
    isLastStep() {
      return this.currentStepIndex === this.steps.length - 1;
    },
    
    progressPercentage() {
      return Math.round(((this.currentStepIndex + 1) / this.steps.length) * 100);
    },
    
    getNextButtonText() {
      if (this.showContentGeneration) {
        if (this.currentGenerationPage) {
          return 'Concluir Página';
        }
        const allDone = this.safePages.length > 0 && this.safePages.every(p => this.generatedPages[p]);
        return allDone ? '🚀 Finalizar Site' : 'Pular Geração';
      }
      return this.isLastStep ? 'Continuar →' : 'Próximo →';
    },
    
    canProceed() {
      if (!this.currentStep) return false;
      
      // Verifica campos obrigatórios do step atual
      const stepData = this.formData[this.currentStep.id] || {};
      const requiredFields = this.currentStep.fields?.filter(f => f.required) || [];
      
      for (const field of requiredFields) {
        // Verifica se campo condicional deve ser validado
        if (field.conditional && !this.isFieldVisible(field)) {
          continue;
        }
        
        const value = stepData[field.id];
        if (value === undefined || value === null || value === '' || 
            (Array.isArray(value) && value.length === 0)) {
          return false;
        }
      }
      
      return true;
    }
  },
  
  watch: {
    formData: {
      deep: true,
      handler() {
        // Marca que tem mudanças pendentes (não salva imediatamente)
        this.hasUnsavedChanges = true;
      }
    },
    
    // Reagir a mudanças no initialData (dados vindo do consultor)
    initialData: {
      deep: true,
      handler(newData) {
        if (newData && Object.keys(newData).length > 0) {
          console.log('[DynamicWizard] Initial data changed, re-initializing:', newData);
          this.initializeFormData();
        }
      }
    }
  },
  
  mounted() {
    this.initializeFormData();
    this.startAutosaveInterval();
    this.checkMobile();
    window.addEventListener('resize', this.checkMobile);
  },
  
  beforeUnmount() {
    // Limpa intervalo e salva pendências ao sair
    this.stopAutosaveInterval();
    if (this.hasUnsavedChanges) {
      this.autosave();
    }
    window.removeEventListener('resize', this.checkMobile);
  },
  
  methods: {
    // Detecta se é mobile
    checkMobile() {
      this.isMobile = window.innerWidth < 900;
    },
    
    // Define se campo ocupa largura total ou metade
    getFieldSizeClass(field) {
      const fullWidthTypes = ['textarea', 'repeater', 'checkbox-group', 'upload'];
      if (fullWidthTypes.includes(field.type) || field.fullWidth) {
        return 'full-width';
      }
      return 'half-width';
    },
    
    initializeFormData() {
      // Inicializa estrutura para cada step
      const data = {};
      
      // Garantir que steps existe
      if (!this.steps || !Array.isArray(this.steps)) return;
      
      this.steps.forEach(step => {
        data[step.id] = {};
        
        // Aplica valores default
        step.fields?.forEach(field => {
          if (field.default !== undefined) {
            data[step.id][field.id] = field.default;
          }
        });
      });
      
      // Mescla com dados iniciais (se retomando)
      if (this.initialData && Object.keys(this.initialData).length > 0) {
        Object.keys(this.initialData).forEach(stepId => {
          if (data[stepId]) {
            data[stepId] = { ...data[stepId], ...this.initialData[stepId] };
          }
        });
      }
      
      this.formData = data;
    },
    
    visibleFields(step) {
      if (!step.fields) return [];
      
      return step.fields.filter(field => this.isFieldVisible(field, step.id));
    },
    
    isFieldVisible(field, stepId) {
      if (!field.conditional) return true;
      
      const stepData = this.formData[stepId] || {};
      const { field: condField, value, contains } = field.conditional;
      const condValue = stepData[condField];
      
      // Condição de valor exato
      if (value !== undefined) {
        return condValue === value;
      }
      
      // Condição de array contém valor
      if (contains !== undefined && Array.isArray(condValue)) {
        return condValue.includes(contains);
      }
      
      return true;
    },
    
    updateField(stepId, fieldId, value) {
      if (!this.formData[stepId]) {
        this.formData[stepId] = {};
      }
      this.formData[stepId][fieldId] = value;
    },
    
    // Preenche campos de endereço quando CEP é encontrado
    handleCepFound(stepId, addressData) {
      if (!this.formData[stepId]) {
        this.formData[stepId] = {};
      }
      
      // Mapeia os campos do ViaCEP para os IDs do form
      this.formData[stepId].addressStreet = addressData.street;
      this.formData[stepId].addressNeighborhood = addressData.neighborhood;
      this.formData[stepId].addressCity = addressData.city;
      this.formData[stepId].addressState = addressData.state;
    },
    
    prevStep() {
      if (this.currentStepIndex > 0) {
        this.currentStepIndex--;
        this.$emit('step-change', this.currentStep, this.currentStepIndex);
        this.scrollToTop();
      }
    },
    
    nextStep() {
      if (!this.canProceed) return;
      
      if (this.isLastStep) {
        // Vai direto para Fase 2 (seleção de páginas)
        console.log('[Wizard] Indo para Fase 2 - Páginas:', this.safePages);
        this.showContentGeneration = true;
        this.scrollToTop();
      } else {
        this.currentStepIndex++;
        this.$emit('step-change', this.currentStep, this.currentStepIndex);
        this.scrollToTop();
      }
    },
    
    // Navegação unificada
    handleBack() {
      if (this.showContentGeneration) {
        if (this.currentGenerationPage) {
          this.currentGenerationPage = null;
        } else {
          this.showContentGeneration = false;
        }
      } else {
        this.prevStep();
      }
      this.scrollToTop();
    },
    
    handleNext() {
      if (this.showContentGeneration) {
        if (this.currentGenerationPage) {
          // Volta para lista de páginas
          this.currentGenerationPage = null;
        } else {
          // Finaliza o wizard
          this.submitOnboarding();
        }
      } else {
        this.nextStep();
      }
    },
    
    // Loading gamificado + geração real de conteúdo
    async startPreparingContent() {
      this.isPreparingContent = true;
      this.preparingProgress = 0;
      this.scrollToTop();
      
      const messages = [
        '🔍 Analisando seu negócio...',
        '🎨 Estudando seu mercado...',
        '✨ IA preparando rascunhos de texto...',
        '🎨 Preparando sua identidade visual...',
        '🎯 Finalizando detalhes...'
      ];
      
      // Preparar dados para geração
      const identityData = this.formData.identity || {};
      const companyName = identityData.companyName || 'Empresa';
      const voiceTone = identityData.voiceTone || 'profissional';
      const niche = identityData.niche || identityData.segment || '';
      
      // Gerar conteúdo para cada página comprada
      const pagesToGenerate = this.safePages;
      const totalSteps = Math.max(1, messages.length + pagesToGenerate.length); // Evita divisão por zero
      let currentStep = 0;
      
      // Fase 1: Mensagens de animação inicial
      for (let i = 0; i < Math.min(messages.length, 3); i++) {
        this.preparingMessage = messages[i];
        this.preparingProgress = Math.round(((currentStep + 1) / totalSteps) * 100);
        currentStep++;
        await new Promise(resolve => setTimeout(resolve, 600));
      }
      
      // Fase 2: Gerar conteúdo real para cada página
      for (const pageType of pagesToGenerate) {
        const pageName = this.getPageName(pageType);
        this.preparingMessage = `✨ Criando conteúdo para ${pageName}...`;
        
        try {
          const content = await this.generatePageContent(pageType, {
            companyName,
            voiceTone,
            niche,
            pageData: this.formData[pageType] || {}
          });
          
          if (content) {
            this.generatedContent[pageType] = content;
            this.generatedPages[pageType] = true;
          }
        } catch (error) {
          console.warn(`[Onboarding] Erro ao gerar ${pageType}:`, error);
          // Continua mesmo com erro - usuário pode gerar manualmente depois
        }
        
        currentStep++;
        this.preparingProgress = Math.round((currentStep / totalSteps) * 100);
      }
      
      // Fase 3: Finalização
      this.preparingMessage = '🎯 Finalizando detalhes...';
      this.preparingProgress = 100;
      await new Promise(resolve => setTimeout(resolve, 500));
      
      // Finaliza e vai para fase 2
      this.isPreparingContent = false;
      this.showContentGeneration = true;
    },
    
    // Método para chamar API de geração de conteúdo
    async generatePageContent(pageType, context) {
      const { companyName, voiceTone, niche, pageData } = context;
      
      // Montar user_input baseado nos dados do formulário
      let userInput = `Nome da empresa: ${companyName}\n`;
      userInput += `Segmento/Nicho: ${niche}\n`;
      
      // Adicionar dados específicos da página
      if (pageData) {
        Object.entries(pageData).forEach(([key, value]) => {
          if (value && typeof value === 'string') {
            userInput += `${key}: ${value}\n`;
          }
        });
      }
      
      // Adicionar dados de identidade
      const identity = this.formData.identity || {};
      if (identity.description) userInput += `Descri├º├úo: ${identity.description}\n`;
      if (identity.differentials) userInput += `Diferenciais: ${identity.differentials}\n`;
      if (identity.targetAudience) userInput += `P├║blico-alvo: ${identity.targetAudience}\n`;
      
      try {
        const response = await fetch('/api/ai/generate-content.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            page_type: pageType,
            user_input: userInput,
            voice_tone: voiceTone,
            company_name: companyName,
            niche: niche
          })
        });
        
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.content) {
          return data.content;
        }
        
        return null;
      } catch (error) {
        console.error(`[generatePageContent] Erro para ${pageType}:`, error);
        throw error;
      }
    },
    
    // Helpers para gera├º├úo de conte├║do
    getPageIcon(pageType) {
      const icons = {
        sobre_nos: '­ƒÅó',
        servicos: 'ÔÜÖ´©Å',
        faq: 'ÔØô',
        portfolio: '­ƒû╝´©Å',
        vitrine_produtos: '­ƒøì´©Å',
        depoimentos: 'Ô¡É',
        blog_noticias: '­ƒôØ'
      };
      return icons[pageType] || '­ƒôä';
    },
    
    getPageName(pageType) {
      const names = {
        sobre_nos: 'Sobre N├│s',
        servicos: 'Servi├ºos',
        faq: 'FAQ',
        portfolio: 'Portf├│lio',
        vitrine_produtos: 'Vitrine',
        depoimentos: 'Depoimentos',
        blog_noticias: 'Blog'
      };
      return names[pageType] || pageType;
    },
    
    getPageOnboardingData(pageType) {
      // Retorna dados do onboarding relevantes para a p├ígina
      const identityData = this.formData.identity || {};
      const pageData = this.formData[pageType] || {};
      
      return {
        ...identityData,
        ...pageData,
        companyName: identityData.companyName
      };
    },
    
    startPageGeneration(pageType) {
      this.currentGenerationPage = pageType;
      this.scrollToTop();
    },
    
    handleSectionUpdate({ pageType, sections }) {
      this.generatedContent[pageType] = sections;
    },
    
    handlePageComplete(sections) {
      this.generatedPages[this.currentGenerationPage] = true;
      this.generatedContent[this.currentGenerationPage] = sections;
      this.currentGenerationPage = null;
      this.scrollToTop();
    },
    
    goToStep(index) {
      if (index < this.currentStepIndex) {
        this.currentStepIndex = index;
        this.$emit('step-change', this.currentStep, this.currentStepIndex);
        this.scrollToTop();
      }
    },
    
    scrollToTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    
    // === AUTOSAVE INTELIGENTE ===
    // Salva a cada 30 segundos SE houver mudan├ºas
    
    startAutosaveInterval() {
      // N├úo inicia sem sessionId v├ílido ou se j├í existe intervalo
      if (!this.sessionId || this.autosaveInterval) return;
      
      // Intervalo de 30 segundos
      this.autosaveInterval = setInterval(() => {
        this.checkAndSave();
      }, 30000);
      
      console.log('[Autosave] Intervalo iniciado (30s)');
    },
    
    stopAutosaveInterval() {
      if (this.autosaveInterval) {
        clearInterval(this.autosaveInterval);
        this.autosaveInterval = null;
        console.log('[Autosave] Intervalo parado');
      }
    },
    
    checkAndSave() {
      // S├│ salva se:
      // 1. Tem sessionId v├ílido
      // 2. Tem mudan├ºas n├úo salvas
      // 3. N├úo est├í salvando no momento
      if (!this.sessionId || !this.hasUnsavedChanges || this.isSaving) {
        return;
      }
      
      this.autosave();
    },
    
    async autosave() {
      if (!this.sessionId) return;
      
      this.isSaving = true;
      
      try {
        const response = await fetch(this.saveEndpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            session_id: this.sessionId,
            current_step: this.currentStepIndex,
            data: this.formData,
            is_draft: true
          })
        });
        
        if (response.ok) {
          // Marca como salvo
          this.hasUnsavedChanges = false;
          this.lastSavedData = JSON.stringify(this.formData);
          this.$emit('save', this.formData);
          console.log('[Autosave] Salvo com sucesso');
        }
      } catch (error) {
        console.error('[Autosave] Falha:', error);
      } finally {
        this.isSaving = false;
      }
    },
    
    async submitOnboarding() {
      this.isSaving = true;
      
      try {
        const response = await fetch(this.submitEndpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            session_id: this.sessionId,
            data: this.formData,
            purchased_pages: this.purchasedPages,
            is_draft: false
          })
        });
        
        if (!response.ok) {
          const error = await response.json();
          throw new Error(error.message || 'Erro ao enviar');
        }
        
        const result = await response.json();
        this.$emit('complete', result);
        
      } catch (error) {
        console.error('Submit failed:', error);
        this.$emit('error', error);
      } finally {
        this.isSaving = false;
      }
    }
  }
};
</script>

<style lang="scss" scoped>
.dynamic-onboarding-wizard {
  max-width: 800px;
  margin: 80px auto 0;
  padding: 1rem;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

// Header & Progress
.wizard-header {
  background: #fff;
  border-radius: 16px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.progress-container {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.progress-bar {
  flex: 1;
  height: 8px;
  background: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #3498db 0%, #9b59b6 100%);
  border-radius: 4px;
  transition: width 0.5s ease;
}

.progress-text {
  font-size: 0.85rem;
  font-weight: 600;
  color: #666;
  white-space: nowrap;
}

.step-indicators {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.step-dot {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 2px solid #e0e0e0;
  background: #fff;
  cursor: default;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  
  &.active {
    border-color: #3498db;
    background: linear-gradient(135deg, #3498db, #9b59b6);
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    
    .dot-icon {
      filter: grayscale(0);
    }
  }
  
  &.completed {
    border-color: #27ae60;
    background: #27ae60;
    
    .dot-icon {
      filter: brightness(0) invert(1);
    }
  }
  
  &.clickable {
    cursor: pointer;
    
    &:hover {
      transform: scale(1.05);
    }
  }
  
  &:disabled:not(.completed) {
    opacity: 0.5;
  }
}

.dot-icon {
  font-size: 1rem;
  filter: grayscale(1);
  transition: filter 0.3s;
}

// Content
.wizard-content {
  flex: 1;
  background: #fff;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  margin-bottom: 1.5rem;
  min-height: 0px;
}

.step-section {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.step-header {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #f0f0f0;
}

.step-icon {
  font-size: 2.5rem;
  flex-shrink: 0;
}

.step-titles {
  flex: 1;
}

.step-title {
  margin: 0 0 0.25rem 0;
  font-size: 1.5rem;
  font-weight: 700;
  color: #333;
}

.step-subtitle {
  margin: 0;
  font-size: 1rem;
  color: #666;
}

.step-fields {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

// Footer Navigation
.wizard-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  background: #fff;
  border-radius: 16px;
  padding: 1.25rem 1.5rem;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.nav-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.5rem;
  font-size: 1rem;
  font-weight: 600;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s;
  
  &.prev {
    background: #f8f9fa;
    color: #666;
    
    &:hover {
      background: #e9ecef;
    }
  }
  
  &.next {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: #fff;
    
    &:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(52, 152, 219, 0.4);
    }
    
    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      transform: none;
    }
    
    &.is-final {
      background: linear-gradient(135deg, #27ae60 0%, #219a52 100%);
      
      &:hover:not(:disabled) {
        box-shadow: 0 4px 15px rgba(39, 174, 96, 0.4);
      }
    }
  }
}

.nav-spacer {
  width: 120px;
}

.step-info {
  text-align: center;
  flex: 1;
  
  .current-step-name {
    font-size: 0.9rem;
    color: #999;
  }
}

// Autosave Indicator
.autosave-indicator {
  position: fixed;
  bottom: 1rem;
  right: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: rgba(0, 0, 0, 0.8);
  color: #fff;
  font-size: 0.85rem;
  border-radius: 20px;
  animation: fadeIn 0.2s;
}

.spinner-small {
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

// Step Transition
.step-slide-enter-active,
.step-slide-leave-active {
  transition: all 0.3s ease;
}

.step-slide-enter-from {
  opacity: 0;
  transform: translateX(20px);
}

.step-slide-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}

// Responsive
@media (max-width: 600px) {
  .wizard-header,
  .wizard-content,
  .wizard-footer {
    border-radius: 12px;
    padding: 1rem;
  }
  
  .step-dot {
    width: 32px;
    height: 32px;
    font-size: 0.85rem;
  }
  
  .step-header {
    flex-direction: column;
    text-align: center;
  }
  
  .step-title {
    font-size: 1.25rem;
  }
  
  .nav-btn {
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
  }
  
  .step-info {
    display: none;
  }
}

// ========================================
// FASE 2: Gera├º├úo de Conte├║do
// ========================================

.phase-indicator {
  text-align: center;
  margin-bottom: 1rem;
  
  .phase-badge {
    display: inline-block;
    padding: 0.5rem 1.5rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    font-size: 0.9rem;
    font-weight: 600;
    border-radius: 20px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
  }
}

.content-generation-phase {
  min-height: 400px;
}

// Loading gamificado
.preparing-content {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 400px;
}

.preparing-container {
  text-align: center;
  padding: 2rem;
}

.preparing-animation {
  position: relative;
  width: 120px;
  height: 120px;
  margin: 0 auto 2rem;
}

.pulse-ring {
  position: absolute;
  inset: 0;
  border-radius: 50%;
  border: 3px solid rgba(102, 126, 234, 0.3);
  animation: pulse-ring 2s ease-out infinite;
  
  &.delay-1 { animation-delay: 0.4s; }
  &.delay-2 { animation-delay: 0.8s; }
}

@keyframes pulse-ring {
  0% {
    transform: scale(0.5);
    opacity: 1;
  }
  100% {
    transform: scale(1.5);
    opacity: 0;
  }
}

.preparing-icon {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  animation: bounce-icon 1s ease-in-out infinite;
}

@keyframes bounce-icon {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.preparing-title {
  margin: 0 0 0.5rem 0;
  font-size: 1.5rem;
  color: #333;
}

.preparing-message {
  margin: 0 0 1.5rem 0;
  color: #667eea;
  font-weight: 500;
  font-size: 1.1rem;
  min-height: 1.5em;
}

.preparing-progress {
  display: flex;
  align-items: center;
  gap: 1rem;
  max-width: 300px;
  margin: 0 auto 1.5rem;
  
  .progress-bar {
    flex: 1;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
  }
  
  .progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 4px;
    transition: width 0.3s ease;
  }
  
  .progress-text {
    font-size: 0.9rem;
    font-weight: 600;
    color: #667eea;
    min-width: 40px;
  }
}

.preparing-tip {
  margin: 0;
  color: #666;
  font-size: 0.9rem;
}

.generation-pages {
  padding: 1.5rem 1rem;
  
  .generation-title {
    margin: 0 0 0.25rem 0;
    font-size: 1.3rem;
    color: #333;
    text-align: center;
  }
  
  .generation-subtitle {
    margin: 0 0 1.5rem 0;
    color: #666;
    text-align: center;
    font-size: 0.95rem;
  }
  
  .ai-credits-hint {
    text-align: center;
    margin-top: 1.5rem;
    color: #856404;
    font-size: 0.85rem;
  }
  
  .no-pages-warning {
    text-align: center;
    padding: 2rem;
    background: #fff3cd;
    border-radius: 12px;
    margin: 1rem 0;
    
    p {
      margin: 0.5rem 0;
      color: #856404;
    }
    
    .debug-info {
      font-size: 0.75rem;
      color: #999;
      font-family: monospace;
    }
  }
}

.pages-to-generate {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 1rem;
  max-width: 600px;
  margin: 0 auto;
}

.page-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1.5rem 1rem;
  background: #f8f9fa;
  border: 2px solid #e9ecef;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
  
  &:hover {
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);
  }
  
  &.is-done {
    border-color: #27ae60;
    background: #f0fff4;
    
    .page-icon {
      opacity: 0.5;
    }
  }
  
  .page-icon {
    font-size: 2rem;
  }
  
  .page-name {
    font-weight: 600;
    color: #333;
    font-size: 0.95rem;
  }
  
  .done-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #27ae60;
    color: #fff;
    border-radius: 50%;
    font-size: 0.8rem;
  }
}

// ========================================
// DESKTOP SIDEBAR LAYOUT
// ========================================

.wizard-sidebar {
  display: none; // Hidden on mobile
}

.wizard-main {
  width: 100%;
}

.dynamic-onboarding-wizard.is-desktop {
  max-width: 100%;
  margin: 0;
  padding: 0;
  flex-direction: row;
  min-height: 100vh;
  
  // Sidebar
  .wizard-sidebar {
    display: flex;
    flex-direction: column;
    width: 280px;
    min-height: 100vh;
    background: linear-gradient(180deg, #1a1f36 0%, #252b48 100%);
    padding: 0;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 100;
    
    .sidebar-brand {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 1.5rem;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      
      .brand-icon {
        font-size: 1.5rem;
      }
      
      .brand-text {
        font-size: 1.1rem;
        font-weight: 700;
        color: #fff;
      }
    }
    
    .sidebar-nav {
      flex: 1;
      padding: 1rem 0;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      
      .nav-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.875rem 1.5rem;
        color: rgba(255,255,255,0.5);
        font-size: 0.9rem;
        font-weight: 500;
        cursor: default;
        transition: all 0.2s;
        border: none;
        background: transparent;
        text-align: left;
        border-left: 3px solid transparent;
        
        .nav-indicator {
          width: 28px;
          height: 28px;
          display: flex;
          align-items: center;
          justify-content: center;
          background: rgba(255,255,255,0.1);
          border-radius: 6px;
          font-size: 0.8rem;
          font-weight: 600;
          color: rgba(255,255,255,0.6);
        }
        
        .nav-label {
          flex: 1;
        }
        
        &.is-active {
          background: rgba(255,255,255,0.08);
          color: #fff;
          border-left-color: #667eea;
          
          .nav-indicator {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
          }
        }
        
        &.is-completed {
          color: rgba(255,255,255,0.8);
          cursor: pointer;
          
          .nav-indicator {
            background: #27ae60;
            color: #fff;
          }
          
          &:hover {
            background: rgba(255,255,255,0.05);
            color: #fff;
          }
        }
        
        &.is-disabled {
          opacity: 0.4;
          cursor: not-allowed;
        }
      }
    }
    
    .sidebar-progress {
      padding: 1.25rem 1.5rem;
      border-top: 1px solid rgba(255,255,255,0.1);
      
      .progress-info {
        display: flex;
        align-items: baseline;
        gap: 0.35rem;
        margin-bottom: 0.5rem;
        
        .progress-value {
          font-size: 1.25rem;
          font-weight: 700;
          color: #fff;
        }
        
        .progress-label {
          font-size: 0.8rem;
          color: rgba(255,255,255,0.5);
        }
      }
      
      .progress-bar-mini {
        height: 6px;
        background: rgba(255,255,255,0.1);
        border-radius: 3px;
        overflow: hidden;
        
        .progress-fill {
          height: 100%;
          background: linear-gradient(90deg, #667eea, #764ba2);
          border-radius: 3px;
          transition: width 0.5s ease;
        }
      }
    }
  }
  
  // Main content area
  .wizard-main {
    flex: 1;
    margin: 70px 0px 0 280px;
    padding: 2rem 3rem;
    min-height: 100vh;
    background: #f5f7fa;
    
    .wizard-header {
      border-radius: 8px;
      margin-bottom: 2rem;
    }
    
    .wizard-content {
      border-radius: 8px;
      padding: 2.5rem;
      margin-bottom: 2rem;
    }
    
    .wizard-footer {
      border-radius: 8px;
      max-width: 100%;
    }
  }
  
  // Grid layout for fields
  .step-fields {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem 2rem;
    
    .field-wrapper {
      &.full-width {
        grid-column: 1 / -1;
      }
      
      &.half-width {
        grid-column: span 1;
      }
    }
  }
  
  // Tighter, more professional styling
  .step-header {
    padding-bottom: 1.25rem;
    margin-bottom: 1.75rem;
    border-bottom: 1px solid #e5e7eb;
  }
  
  .step-title {
    font-size: 1.35rem;
  }
  
  .step-subtitle {
    font-size: 0.95rem;
    color: #6b7280;
  }
}

// Responsive adjustments
@media (max-width: 1200px) {
  .dynamic-onboarding-wizard.is-desktop {
    .wizard-sidebar {
      width: 240px;
    }
    
    .wizard-main {
      margin-left: 240px;
      padding: 1.5rem 2rem;
    }
  }
}

@media (max-width: 900px) {
  .dynamic-onboarding-wizard.is-desktop {
    flex-direction: column;
    
    .wizard-sidebar {
      display: none;
    }
    
    .wizard-main {
      margin-left: 0;
      padding: 1rem;
    }
    
    .step-fields {
      grid-template-columns: 1fr;
      
      .field-wrapper {
        grid-column: 1 !important;
      }
    }
  }
}
</style>
