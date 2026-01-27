<template>
  <div class="dynamic-onboarding-wizard" :class="{ 'is-desktop': !isMobile }">
    <!-- DESKTOP SIDEBAR -->
    <aside class="wizard-sidebar" v-if="!isMobile && !showContentGeneration">
      <div class="sidebar-brand">
        <span class="brand-icon">✨</span>
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
          <span class="nav-label">{{ step.title.replace(/[🎨📞🏢⚙️❓🖼️⭐📝🛍️]/g, '').trim() }}</span>
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
          :class="{ 'layout-cards': step.layout === 'cards' }"
        >
          <!-- AI Assistant (se configurado) -->
          <div v-if="step.aiAssistant?.enabled && shouldShowAiAssistant(step)" class="ai-assistant-panel">
            <div class="assistant-header">
              <span class="assistant-icon">🤖</span>
              <h4>{{ step.aiAssistant.suggestions.title }}</h4>
            </div>
            <div class="assistant-message" v-html="getAiSuggestionMessage(step)"></div>
            <div class="assistant-actions">
              <button @click="dismissAiAssistant(step.id)">Depois</button>
              <button class="btn-apply" @click="applyAiSuggestions(step)">✨ Aplicar Sugestões</button>
            </div>
          </div>
          
          <!-- Step Header -->
          <div class="step-header">
            <span class="step-icon">{{ step.icon }}</span>
            <div class="step-titles">
              <h2 class="step-title">{{ step.title }}</h2>
              <p v-if="step.subtitle" class="step-subtitle">{{ step.subtitle }}</p>
            </div>
          </div>
          
          <!-- LAYOUT CARDS (para config_contato) -->
          <div v-if="step.layout === 'cards' && step.cards" class="cards-container">
            <div 
              v-for="card in step.cards" 
              :key="card.id"
              class="config-card"
              :class="{ 
                'collapsible': card.collapsible,
                'collapsed': collapsedCards[step.id + '_' + card.id]
              }"
            >
              <div 
                class="card-header"
                @click="card.collapsible ? toggleCard(step.id, card.id) : null"
              >
                <span v-if="card.icon" class="card-icon">{{ card.icon }}</span>
                <div class="card-title-wrapper">
                  <h3>{{ card.title }}</h3>
                  <p v-if="card.subtitle" class="card-subtitle">{{ card.subtitle }}</p>
                </div>
                <span v-if="card.collapsible" class="card-toggle" :class="{ 'collapsed': collapsedCards[step.id + '_' + card.id] }">
                  ▼
                </span>
              </div>
              
              <div class="card-body">
                <!-- FIELD LIST (Smart Cards) -->
                <template v-if="card.type === 'field-list'">
                  <!-- PRESETS: Atalhos rápidos (Lei de Hick) -->
                  <div class="form-presets">
                    <p class="presets-label">Escolha um modelo para começar:</p>
                    <div class="presets-grid">
                      <button
                        v-for="preset in formPresets"
                        :key="preset.id"
                        class="preset-card"
                        :class="{ 'is-selected': selectedPreset === preset.id }"
                        @click="applyPreset(preset.id)"
                      >
                        <span class="preset-icon">{{ preset.icon }}</span>
                        <div class="preset-info">
                          <h4 class="preset-name">{{ preset.name }}</h4>
                          <span class="preset-subtitle">{{ preset.subtitle }}</span>
                        </div>
                        <span v-if="selectedPreset === preset.id" class="preset-check">✓</span>
                      </button>
                    </div>
                    <p class="presets-hint">💡 Você pode personalizar os campos abaixo após escolher</p>
                  </div>
                  
                  <!-- Separador visual -->
                  <div class="fields-divider">
                    <span>Campos do Formulário</span>
                  </div>
                  
                  <div class="field-list">
                    <div 
                      v-for="item in getFieldListItems(card)" 
                      :key="item.id"
                      class="field-list-item"
                      :class="{ 
                        'is-active': isFieldEnabled(step.id, item.id),
                        'is-inactive': !isFieldEnabled(step.id, item.id)
                      }"
                    >
                      <!-- BLOCO ESQUERDO: Ativa/Desativa (80% - clicável) -->
                      <div 
                        class="field-activation-block"
                        @click="toggleFieldEnabled(step.id, item.id, !isFieldEnabled(step.id, item.id))"
                      >
                        <!-- Checkbox Visual (Fake) -->
                        <div class="field-checkbox">
                          <svg v-if="isFieldEnabled(step.id, item.id)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="check-icon">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                          </svg>
                        </div>
                        
                        <!-- Ícone e Textos -->
                        <span class="field-icon">{{ item.icon }}</span>
                        <div class="field-label-wrapper">
                          <h4 class="field-label">{{ item.label }}</h4>
                          <p class="field-description">{{ item.description }}</p>
                        </div>
                      </div>
                      
                      <!-- BLOCO DIREITO: Obrigatório (20% - separado) -->
                      <div 
                        class="field-required-block"
                        :class="{ 'is-disabled': !isFieldEnabled(step.id, item.id) }"
                        @click="isFieldEnabled(step.id, item.id) && toggleFieldRequired(step.id, item.id, !isFieldRequired(step.id, item.id))"
                      >
                        <div 
                          class="required-pill"
                          :class="{ 'is-checked': isFieldRequired(step.id, item.id) }"
                        >
                          Obrigatório
                        </div>
                      </div>
                    </div>
                  </div>
                </template>
                
                <!-- CAMPOS NORMAIS -->
                <template v-else>
                  <template v-for="field in visibleFields({ ...step, fields: card.fields })" :key="field.id">
                    <div class="field-wrapper" :class="getFieldSizeClass(field)">
                      <DynamicField
                        :field="field"
                        :modelValue="formData[step.id]?.[field.id]"
                        :voice-tone="formData.identity?.voiceTone || 'profissional'"
                        :company-name="formData.identity?.companyName || ''"
                        :niche="formData.identity?.niche || formData.identity?.businessType || ''"
                        @update:modelValue="updateField(step.id, field.id, $event)"
                        @cep-found="handleCepFound(step.id, $event)"
                      />
                    </div>
                  </template>
                </template>
              </div>
            </div>
          </div>
          
          <!-- LAYOUT TRADICIONAL (Grid 2 colunas) -->
          <div v-else class="step-fields">
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
          <div class="preparing-icon">🚀</div>
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
        
        <p class="ai-credits-hint">💡 <strong>10 gerações de IA</strong> por dia</p>
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
        ← {{ showContentGeneration && currentGenerationPage ? 'Voltar' : 'Anterior' }}
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
      isMobile: true,
      // Controle de Cards e IA
      collapsedCards: {},
      dismissedAiAssistants: {},
      // Field List (para config_contato)
      fieldListStates: {}, // { stepId: { fieldId: { enabled: true, required: false } } }
      // Preset selecionado para formulário de contato (Lei de Hick)
      selectedPreset: null // 'rapido' | 'padrao' | 'orcamento'
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
    
    // Presets de formulário (Lei de Hick)
    formPresets() {
      return [
        {
          id: 'rapido',
          icon: '⚡',
          name: 'Contato Rápido',
          subtitle: 'Foco em WhatsApp',
          description: 'Ideal para: negócios locais, delivery, emergências',
          fields: {
            phone: { enabled: true, required: true },
            message: { enabled: false, required: false },
            subject: { enabled: false, required: false },
            company: { enabled: false, required: false },
            city: { enabled: false, required: false },
            service: { enabled: false, required: false },
            attachment: { enabled: false, required: false },
            insurance: { enabled: false, required: false },
            address: { enabled: false, required: false }
          }
        },
        {
          id: 'padrao',
          icon: '📝',
          name: 'Fale Conosco',
          subtitle: 'Formulário padrão',
          description: 'Ideal para: escritórios, consultórios, prestadores de serviço',
          fields: {
            phone: { enabled: true, required: false },
            message: { enabled: true, required: true },
            subject: { enabled: false, required: false },
            company: { enabled: false, required: false },
            city: { enabled: false, required: false },
            service: { enabled: false, required: false },
            attachment: { enabled: false, required: false },
            insurance: { enabled: false, required: false },
            address: { enabled: false, required: false }
          }
        },
        {
          id: 'orcamento',
          icon: '💼',
          name: 'Orçamento Detalhado',
          subtitle: 'Leads qualificados',
          description: 'Ideal para: construtoras, eventos, B2B, projetos personalizados',
          fields: {
            phone: { enabled: true, required: false },
            message: { enabled: true, required: false },
            subject: { enabled: false, required: false },
            company: { enabled: false, required: false },
            city: { enabled: true, required: false },
            service: { enabled: true, required: true },
            attachment: { enabled: true, required: false },
            insurance: { enabled: false, required: false },
            address: { enabled: false, required: false }
          }
        }
      ];
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
    }
  },
  
  mounted() {
    this.initializeFormData();
    this.initializeFieldListStates();
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
      const fullWidthTypes = ['textarea', 'repeater', 'checkbox-group', 'upload', 'faq-builder'];
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
    
    initializeFieldListStates() {
      // Inicializa estados dos field lists para cada step com cards
      this.steps.forEach(step => {
        if (step.cards) {
          step.cards.forEach(card => {
            if (card.type === 'field-list') {
              this.initFieldListState(step.id, card);
            }
          });
        }
      });
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
        '📊 Estudando seu mercado...',
        '✍️ Especialistas criando textos...',
        '🎨 Preparando sua identidade visual...',
        '✨ Finalizando detalhes...'
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
        this.preparingMessage = `✍️ Criando conteúdo para ${pageName}...`;
        
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
      this.preparingMessage = '✨ Finalizando detalhes...';
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
      if (identity.description) userInput += `Descrição: ${identity.description}\n`;
      if (identity.differentials) userInput += `Diferenciais: ${identity.differentials}\n`;
      if (identity.targetAudience) userInput += `Público-alvo: ${identity.targetAudience}\n`;
      
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
    
    // Helpers para geração de conteúdo
    getPageIcon(pageType) {
      const icons = {
        sobre_nos: '🏢',
        servicos: '⚙️',
        faq: '❓',
        portfolio: '🖼️',
        vitrine_produtos: '🛍️',
        depoimentos: '⭐',
        blog_noticias: '📝'
      };
      return icons[pageType] || '📄';
    },
    
    getPageName(pageType) {
      const names = {
        sobre_nos: 'Sobre Nós',
        servicos: 'Serviços',
        faq: 'FAQ',
        portfolio: 'Portfólio',
        vitrine_produtos: 'Vitrine',
        depoimentos: 'Depoimentos',
        blog_noticias: 'Blog'
      };
      return names[pageType] || pageType;
    },
    
    getPageOnboardingData(pageType) {
      // Retorna dados do onboarding relevantes para a página
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
    
    // ========================================
    // MÉTODOS PARA CARDS E AI ASSISTANT
    // ========================================
    
    toggleCard(stepId, cardId) {
      const key = `${stepId}_${cardId}`;
      this.collapsedCards[key] = !this.collapsedCards[key];
    },
    
    shouldShowAiAssistant(step) {
      // Não mostra se já foi dispensado
      if (this.dismissedAiAssistants[step.id]) return false;
      
      // Só mostra se tem businessType definido
      const businessType = this.formData.identity?.businessType;
      return !!businessType;
    },
    
    getAiSuggestionMessage(step) {
      const businessType = this.formData.identity?.businessType || 'default';
      const templates = step.aiAssistant.suggestions.templates;
      const template = templates[businessType] || templates.default;
      return template?.message || '';
    },
    
    dismissAiAssistant(stepId) {
      this.dismissedAiAssistants[stepId] = true;
    },
    
    applyAiSuggestions(step) {
      const businessType = this.formData.identity?.businessType || 'default';
      const templates = step.aiAssistant.suggestions.templates;
      const template = templates[businessType] || templates.default;
      
      if (!template) return;
      
      // Aplica campos recomendados e obrigatórios
      if (template.recommendedFields && step.cards) {
        const fieldListCard = step.cards.find(c => c.type === 'field-list');
        if (fieldListCard) {
          this.initFieldListState(step.id, fieldListCard);
          
          template.recommendedFields.forEach(fieldId => {
            this.toggleFieldEnabled(step.id, fieldId, true);
          });
          
          if (template.requiredFields) {
            template.requiredFields.forEach(fieldId => {
              this.toggleFieldRequired(step.id, fieldId, true);
            });
          }
        }
      }
      
      this.dismissAiAssistant(step.id);
      alert('✅ Sugestões aplicadas! Você pode ajustar conforme necessário.');
    },
    
    // ========================================
    // FIELD LIST (Lista Inteligente de Campos)
    // ========================================
    
    getFieldListItems(card) {
      const field = card.fields?.find(f => f.type === 'field-list');
      if (!field || !field.items) return [];
      
      // Filtra itens condicionais (ex: seguro saúde só aparece se businessType = saude)
      return field.items.filter(item => {
        if (!item.conditional) return true;
        const condValue = this.formData.identity?.[item.conditional.field];
        return condValue === item.conditional.value;
      });
    },
    
    initFieldListState(stepId, card) {
      if (!this.fieldListStates[stepId]) {
        this.fieldListStates[stepId] = {};
      }
      
      const items = this.getFieldListItems(card, { id: stepId, cards: [card] });
      items.forEach(item => {
        if (!this.fieldListStates[stepId][item.id]) {
          this.fieldListStates[stepId][item.id] = {
            enabled: item.enabledByDefault || false,
            required: item.requiredByDefault || false
          };
        }
      });
    },
    
    isFieldEnabled(stepId, fieldId) {
      return this.fieldListStates[stepId]?.[fieldId]?.enabled || false;
    },
    
    isFieldRequired(stepId, fieldId) {
      return this.fieldListStates[stepId]?.[fieldId]?.required || false;
    },
    
    toggleFieldEnabled(stepId, fieldId, enabled) {
      if (!this.fieldListStates[stepId]) {
        this.fieldListStates[stepId] = {};
      }
      if (!this.fieldListStates[stepId][fieldId]) {
        this.fieldListStates[stepId][fieldId] = { enabled: false, required: false };
      }
      
      this.fieldListStates[stepId][fieldId].enabled = enabled;
      
      // Se desabilitar, remove obrigatório também
      if (!enabled) {
        this.fieldListStates[stepId][fieldId].required = false;
      }
      
      // Atualiza formData para salvar estado
      this.updateField(stepId, `formFieldsList_${fieldId}_enabled`, enabled);
    },
    
    toggleFieldRequired(stepId, fieldId, required) {
      if (!this.fieldListStates[stepId]?.[fieldId]) return;
      
      this.fieldListStates[stepId][fieldId].required = required;
      this.updateField(stepId, `formFieldsList_${fieldId}_required`, required);
    },
    
    // ========================================
    // PRESETS DE FORMULÁRIO (Lei de Hick)
    // ========================================
    
    applyPreset(presetId) {
      const preset = this.formPresets.find(p => p.id === presetId);
      if (!preset) return;
      
      // Atualiza preset selecionado
      this.selectedPreset = presetId;
      
      // Garante que o step config_contato tem estado inicializado
      const stepId = 'config_contato';
      if (!this.fieldListStates[stepId]) {
        this.fieldListStates[stepId] = {};
      }
      
      // Aplica as configurações do preset a cada campo
      Object.entries(preset.fields).forEach(([fieldId, config]) => {
        // Inicializa estado do campo se não existe
        if (!this.fieldListStates[stepId][fieldId]) {
          this.fieldListStates[stepId][fieldId] = { enabled: false, required: false };
        }
        
        // Aplica enabled e required do preset
        this.fieldListStates[stepId][fieldId].enabled = config.enabled;
        this.fieldListStates[stepId][fieldId].required = config.required;
        
        // Atualiza formData para persistir
        this.updateField(stepId, `formFieldsList_${fieldId}_enabled`, config.enabled);
        this.updateField(stepId, `formFieldsList_${fieldId}_required`, config.required);
      });
      
      // Limpa preset ao modificar manualmente um campo
      // (será resetado no próximo toggleFieldEnabled/Required chamado pelo usuário)
    },
    
    // Detecta se preset ainda está ativo (campos batem com configuração)
    isPresetActive(presetId) {
      const preset = this.formPresets.find(p => p.id === presetId);
      if (!preset) return false;
      
      const stepId = 'config_contato';
      
      // Verifica se todos os campos principais batem com o preset
      const mainFields = ['phone', 'message', 'city', 'service'];
      return mainFields.every(fieldId => {
        const expected = preset.fields[fieldId];
        const current = this.fieldListStates[stepId]?.[fieldId];
        if (!expected || !current) return true;
        return expected.enabled === current.enabled;
      });
    },
    
    // === AUTOSAVE INTELIGENTE ===
    // Salva a cada 30 segundos SE houver mudanças
    
    startAutosaveInterval() {
      // Não inicia sem sessionId válido ou se já existe intervalo
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
      // Só salva se:
      // 1. Tem sessionId válido
      // 2. Tem mudanças não salvas
      // 3. Não está salvando no momento
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
  min-height: 70vh;
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
// FASE 2: Geração de Conteúdo
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
    min-height: calc(100vh - 70px);
    background: linear-gradient(180deg, #1a1f36 0%, #252b48 100%);
    padding: 0;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 100;
    margin-top: 70px;
    
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

// ======================================
// CARDS LAYOUT (UX PREMIUM)
// ======================================

// Container principal com fundo cinza claro
.step-section.layout-cards {
  background: #f8f9fa;
  padding: 2rem;
  border-radius: 12px;
  
  .step-header {
    background: transparent;
    margin-bottom: 1.5rem;
  }
}

// Card individual (Card 1, Card 2, etc.)
.config-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  border: 1px solid #e9ecef;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  transition: all 0.2s ease;
  
  &:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  }
  
  // Cabeçalho do card
  .card-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.25rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f1f3f5;
    
    .card-icon {
      font-size: 1.75rem;
      flex-shrink: 0;
    }
    
    .card-title-wrapper {
      flex: 1;
      
      h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #212529;
        margin: 0 0 0.25rem 0;
        line-height: 1.3;
      }
      
      .card-subtitle {
        font-size: 0.9rem;
        color: #6c757d;
        margin: 0;
      }
    }
    
    // Toggle no cabeçalho (para cards colapsáveis)
    .card-toggle {
      cursor: pointer;
      font-size: 1.25rem;
      color: #6c757d;
      transition: transform 0.2s;
      
      &.collapsed {
        transform: rotate(-90deg);
      }
    }
  }
  
  // Conteúdo do card
  .card-body {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.25rem;
  }
  
  // Field-list: força coluna única
  &[class*="field-list"] .card-body,
  .card-body:has(.form-presets),
  .card-body:has(.field-list) {
    display: flex;
    flex-direction: column;
    gap: 0;
  }
  
  // Card colapsável
  &.collapsible {
    .card-header {
      cursor: pointer;
      user-select: none;
      
      &:hover {
        background: #f8f9fa;
        margin: -0.5rem -1rem 1rem;
        padding: 0.5rem 1rem 1rem;
        border-radius: 8px 8px 0 0;
      }
    }
    
    &.collapsed .card-body {
      display: none;
    }
  }
}

// ======================================
// PRESETS DE FORMULÁRIO (Lei de Hick)
// ======================================

.form-presets {
  width: 100%;
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  border-radius: 16px;
  border: 1px solid #bae6fd;
  
  .presets-label {
    margin: 0 0 1rem 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #0369a1;
    text-align: center;
  }
  
  .presets-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    
    @media (max-width: 900px) {
      grid-template-columns: 1fr;
      gap: 0.75rem;
    }
  }
  
  .presets-hint {
    margin: 1rem 0 0 0;
    font-size: 0.85rem;
    color: #0284c7;
    text-align: center;
  }
}

.preset-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 1.25rem 1rem;
  background: white;
  border: 3px solid #e2e8f0;
  border-radius: 16px;
  cursor: pointer;
  transition: all 0.25s ease;
  text-align: center;
  position: relative;
  min-height: 120px;
  
  &:hover {
    border-color: #3b82f6;
    background: white;
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(59, 130, 246, 0.2);
  }
  
  &.is-selected {
    border-color: #3b82f6;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    box-shadow: 0 8px 24px rgba(59, 130, 246, 0.3);
    transform: translateY(-2px);
    
    .preset-icon {
      transform: scale(1.1);
    }
    
    .preset-name {
      color: #1d4ed8;
    }
  }
  
  .preset-icon {
    font-size: 2.5rem;
    flex-shrink: 0;
    transition: transform 0.25s ease;
  }
  
  .preset-info {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
  }
  
  .preset-name {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #1f2937;
    line-height: 1.3;
  }
  
  .preset-subtitle {
    display: block;
    font-size: 0.85rem;
    color: #64748b;
    margin-top: 0;
  }
  
  .preset-check {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 26px;
    height: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #3b82f6;
    color: white;
    border-radius: 50%;
    font-size: 0.85rem;
    font-weight: bold;
    animation: checkPop 0.3s ease;
  }
}

.fields-divider {
  display: flex;
  align-items: center;
  margin: 1.5rem 0 1rem;
  
  &::before,
  &::after {
    content: '';
    flex: 1;
    height: 2px;
    background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
  }
  
  span {
    padding: 0 1.25rem;
    font-size: 0.9rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }
}

// ======================================
// FIELD LIST (Smart Cards Selecionáveis)
// ======================================

.field-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  width: 100%;
  margin-top: 0.5rem;
  
  .field-list-item {
    display: flex;
    align-items: stretch;
    border-radius: 10px;
    overflow: hidden;
    border: 2px solid #dee2e6;
    background: white;
    transition: all 0.2s ease;
    min-height: 80px;
    
    // Estado ATIVO: borda azul, fundo levemente azulado
    &.is-active {
      border-color: #0066CC;
      background: linear-gradient(to right, #f0f7ff 0%, #ffffff 50%);
      box-shadow: 0 2px 8px rgba(0, 102, 204, 0.15);
      
      .field-activation-block {
        .field-checkbox {
          background: #0066CC;
          border-color: #0066CC;
          color: white;
        }
        
        .field-icon {
          filter: grayscale(0);
          transform: scale(1.05);
        }
        
        .field-label {
          color: #0066CC;
          font-weight: 600;
        }
      }
    }
    
    // Estado INATIVO: cinza, opaco
    &.is-inactive {
      background: #fafbfc;
      
      .field-activation-block {
        .field-checkbox {
          background: white;
          border-color: #dee2e6;
        }
        
        .field-icon {
          filter: grayscale(0.5);
          opacity: 0.6;
        }
        
        .field-label {
          color: #6c757d;
        }
        
        .field-description {
          color: #adb5bd;
        }
      }
    }
    
    &:hover:not(.is-active) {
      border-color: #adb5bd;
      background: white;
    }
    
    // ========================================
    // BLOCO ESQUERDO (80%): Ativação do Campo
    // ========================================
    .field-activation-block {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1rem 1.25rem;
      cursor: pointer;
      user-select: none;
      transition: all 0.2s ease;
      
      &:hover {
        background: rgba(0, 102, 204, 0.03);
      }
      
      &:active {
        transform: scale(0.995);
      }
      
      // Checkbox Visual (Fake)
      .field-checkbox {
        width: 26px;
        height: 26px;
        border-radius: 6px;
        border: 2px solid #dee2e6;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.2s ease;
        
        .check-icon {
          width: 16px;
          height: 16px;
          animation: checkPop 0.2s ease;
        }
      }
      
      // Ícone do campo
      .field-icon {
        font-size: 1.75rem;
        flex-shrink: 0;
        transition: all 0.2s ease;
      }
      
      // Textos
      .field-label-wrapper {
        flex: 1;
        min-width: 0;
        
        .field-label {
          font-size: 0.95rem;
          font-weight: 500;
          color: #212529;
          margin: 0 0 0.25rem 0;
          line-height: 1.3;
          transition: all 0.2s ease;
        }
        
        .field-description {
          font-size: 0.825rem;
          color: #6c757d;
          margin: 0;
          line-height: 1.4;
          transition: all 0.2s ease;
        }
      }
    }
    
    // ========================================
    // BLOCO DIREITO (20%): Campo Obrigatório
    // ========================================
    .field-required-block {
      width: 140px;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
      background: linear-gradient(to left, #f8f9fa 0%, #ffffff 100%);
      border-left: 1px solid #e9ecef;
      cursor: pointer;
      transition: all 0.2s ease;
      
      &:hover:not(.is-disabled) {
        background: linear-gradient(to left, #fff5f5 0%, #ffffff 100%);
      }
      
      &.is-disabled {
        opacity: 0.4;
        cursor: not-allowed;
        background: #f8f9fa;
        
        .required-pill {
          pointer-events: none;
        }
      }
      
      // Pill "Obrigatório"
      .required-pill {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        border: 2px solid #dee2e6;
        background: white;
        color: #6c757d;
        transition: all 0.2s ease;
        text-align: center;
        white-space: nowrap;
        
        &.is-checked {
          background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
          border-color: #ff6b6b;
          color: white;
          font-weight: 600;
          box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3);
          animation: pillPop 0.2s ease;
        }
      }
    }
  }
}

// Animações
@keyframes checkPop {
  0% {
    transform: scale(0);
    opacity: 0;
  }
  50% {
    transform: scale(1.2);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

@keyframes pillPop {
  0% {
    transform: scale(0.9);
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    transform: scale(1);
  }
}

// ======================================
// AI ASSISTANT (Assistente de IA)
// ======================================

.ai-assistant-panel {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 1.5rem;
  border-radius: 12px;
  margin-bottom: 1.5rem;
  box-shadow: 0 4px 16px rgba(102, 126, 234, 0.25);
  
  .assistant-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
    
    .assistant-icon {
      font-size: 1.75rem;
    }
    
    h4 {
      margin: 0;
      font-size: 1.125rem;
      font-weight: 600;
    }
  }
  
  .assistant-message {
    font-size: 0.95rem;
    line-height: 1.6;
    opacity: 0.95;
    margin-bottom: 1rem;
    
    strong {
      font-weight: 600;
      text-decoration: underline;
    }
  }
  
  .assistant-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    
    button {
      padding: 0.625rem 1.25rem;
      background: rgba(255, 255, 255, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: white;
      border-radius: 6px;
      font-size: 0.875rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
      
      &:hover {
        background: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.5);
      }
      
      &.btn-apply {
        background: white;
        color: #667eea;
        border-color: white;
        
        &:hover {
          background: #f8f9fa;
        }
      }
    }
  }
}

// ======================================
// MELHORIAS TIPOGRÁFICAS E INPUTS
// ======================================

// Importar Inter (adicionar no head ou aqui)
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

.dynamic-onboarding-wizard {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  
  // Labels mais próximos dos inputs
  .field-wrapper {
    label {
      margin-bottom: 0.5rem;
      font-size: 0.9rem;
      font-weight: 500;
    }
    
    input:not([type="checkbox"]):not([type="radio"]),
    textarea,
    select {
      min-height: 48px;
      font-size: 0.95rem;
      
      &::placeholder {
        color: #adb5bd;
      }
    }
    
    textarea {
      min-height: 100px;
    }
  }
  
  // Títulos maiores
  .step-title {
    font-size: 1.75rem !important;
    font-weight: 700 !important;
  }
  
  .card-header h3 {
    font-weight: 600 !important;
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
    
    // Field List responsivo
    .field-list-item {
      flex-direction: column !important;
      min-height: auto !important;
      
      .field-activation-block {
        width: 100%;
        padding: 1rem;
      }
      
      .field-required-block {
        width: 100% !important;
        border-left: none !important;
        border-top: 1px solid #e9ecef;
        padding: 0.75rem 1rem;
        background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%) !important;
      }
    }
  }
}
</style>
