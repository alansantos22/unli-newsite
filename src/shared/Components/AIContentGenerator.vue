<template>
  <div class="ai-content-generator">
    <!-- Header -->
    <div class="generator-header">
      <div class="header-icon">✨</div>
      <h2>{{ title }}</h2>
      <p>{{ subtitle }}</p>
    </div>

    <!-- Step 1: Escolher Tipo de Página -->
    <div v-if="currentStep === 1" class="generator-step">
      <h3>1. Que tipo de conteúdo você precisa?</h3>
      
      <div class="page-type-grid">
        <div
          v-for="(config, key) in pageTypes"
          :key="key"
          class="page-type-card"
          :class="{ active: selectedPageType === key }"
          @click="selectPageType(key)"
        >
          <span class="card-icon">{{ config.icon }}</span>
          <span class="card-label">{{ config.label }}</span>
          <span class="card-desc">{{ config.description }}</span>
        </div>
      </div>

      <div class="step-actions">
        <button 
          class="btn-primary"
          :disabled="!selectedPageType"
          @click="nextStep"
        >
          Continuar →
        </button>
      </div>
    </div>

    <!-- Step 2: Tom de Voz -->
    <div v-if="currentStep === 2" class="generator-step">
      <h3>2. Qual tom de voz combina com sua marca?</h3>
      
      <div class="voice-tone-grid">
        <div
          v-for="(config, key) in voiceTones"
          :key="key"
          class="voice-tone-card"
          :class="{ active: selectedVoiceTone === key }"
          @click="selectVoiceTone(key)"
        >
          <span class="card-icon">{{ config.icon }}</span>
          <span class="card-label">{{ config.label }}</span>
          <span class="card-desc">{{ config.description }}</span>
        </div>
      </div>

      <div class="step-actions">
        <button class="btn-secondary" @click="previousStep">
          ← Voltar
        </button>
        <button 
          class="btn-primary"
          :disabled="!selectedVoiceTone"
          @click="nextStep"
        >
          Continuar →
        </button>
      </div>
    </div>

    <!-- Step 3: Input do Usuário -->
    <div v-if="currentStep === 3" class="generator-step">
      <h3>3. Conte sobre {{ selectedPageTypeConfig?.label }}</h3>
      
      <div class="input-section">
        <div class="input-header">
          <span class="input-icon">{{ selectedPageTypeConfig?.icon }}</span>
          <span class="input-title">{{ selectedPageTypeConfig?.label }}</span>
        </div>

        <!-- Campos extras opcionais -->
        <div class="extra-fields">
          <div class="form-group">
            <label for="company-name">Nome da Empresa (opcional)</label>
            <input
              id="company-name"
              v-model="companyName"
              type="text"
              placeholder="Ex: Padaria Dona Maria"
            />
          </div>
          <div class="form-group">
            <label for="niche">Nicho/Segmento (opcional)</label>
            <input
              id="niche"
              v-model="niche"
              type="text"
              placeholder="Ex: Alimentação saudável, Tecnologia..."
            />
          </div>
        </div>

        <!-- Textarea principal -->
        <div class="form-group main-input">
          <label for="user-input">
            {{ selectedPageTypeConfig?.input_placeholder?.split(':')[0] }} *
          </label>
          <textarea
            id="user-input"
            v-model="userInput"
            rows="6"
            :placeholder="selectedPageTypeConfig?.input_placeholder"
          ></textarea>
          <small class="input-hint">{{ selectedPageTypeConfig?.input_hint }}</small>
        </div>

        <!-- Exemplo -->
        <div class="example-section" v-if="selectedPageTypeConfig?.exemplo">
          <button 
            class="btn-example"
            @click="showExample = !showExample"
          >
            {{ showExample ? '🙈 Esconder exemplo' : '👀 Ver exemplo' }}
          </button>
          <div v-if="showExample" class="example-content">
            <strong>Exemplo de input:</strong>
            <p>{{ selectedPageTypeConfig?.exemplo }}</p>
          </div>
        </div>
      </div>

      <div class="step-actions">
        <button class="btn-secondary" @click="previousStep">
          ← Voltar
        </button>
        <button 
          class="btn-magic"
          :disabled="!userInput.trim() || generating"
          @click="generateContent"
        >
          <span v-if="!generating">✨ Gerar com IA</span>
          <span v-else class="loading-text">
            <span class="spinner"></span>
            {{ loadingMessage }}
          </span>
        </button>
      </div>
    </div>

    <!-- Step 4: Resultado -->
    <div v-if="currentStep === 4" class="generator-step result-step">
      <div class="result-header">
        <div class="result-icon">🎉</div>
        <h3>Conteúdo Gerado!</h3>
        <p>Revise e edite conforme necessário. Você tem total controle.</p>
      </div>

      <!-- Error State -->
      <div v-if="error" class="error-container">
        <div class="error-icon">⚠️</div>
        <h4>Ops! Algo deu errado</h4>
        <p>{{ error }}</p>
        <button class="btn-secondary" @click="retryGeneration">
          🔄 Tentar Novamente
        </button>
      </div>

      <!-- Success State -->
      <div v-else-if="generatedContent" class="generated-content">
        <!-- SEO Metadata Badge -->
        <div v-if="generatedContent.seo_metadata" class="seo-badge">
          <span class="badge-icon">🔍</span>
          <span>SEO Incluído</span>
        </div>

        <!-- Dynamic Content Renderer -->
        <AIContentRenderer
          :content="generatedContent"
          :pageType="selectedPageType"
          @update="handleContentUpdate"
        />

        <!-- SEO Preview -->
        <div v-if="generatedContent.seo_metadata" class="seo-preview">
          <h4>📊 Preview no Google</h4>
          <div class="google-preview">
            <div class="google-title">{{ generatedContent.seo_metadata.meta_title }}</div>
            <div class="google-url">www.suaempresa.com.br › {{ selectedPageType }}</div>
            <div class="google-description">{{ generatedContent.seo_metadata.meta_description }}</div>
          </div>
          <div class="keywords-list">
            <strong>Palavras-chave:</strong>
            <span 
              v-for="(keyword, i) in generatedContent.seo_metadata.keywords" 
              :key="i"
              class="keyword-tag"
            >
              {{ keyword }}
            </span>
          </div>
        </div>
      </div>

      <div class="step-actions">
        <button class="btn-secondary" @click="startOver">
          ↩️ Gerar Outro
        </button>
        <button 
          class="btn-success"
          :disabled="!generatedContent"
          @click="applyContent"
        >
          ✅ Aplicar ao Site
        </button>
      </div>
    </div>

    <!-- Progress Indicator -->
    <div class="progress-indicator">
      <div 
        v-for="step in 4" 
        :key="step"
        class="progress-dot"
        :class="{ 
          active: currentStep === step,
          completed: currentStep > step
        }"
      >
        <span class="dot-number">{{ step }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import AIContentRenderer from './AIContentRenderer.vue';

export default {
  name: 'AIContentGenerator',
  
  components: {
    AIContentRenderer
  },
  
  props: {
    title: {
      type: String,
      default: 'Gerador de Conteúdo com IA'
    },
    subtitle: {
      type: String,
      default: 'Crie textos profissionais em segundos'
    },
    apiBaseUrl: {
      type: String,
      default: '/api/ai'
    }
  },
  
  emits: ['content-generated', 'content-applied'],
  
  data() {
    return {
      currentStep: 1,
      
      // Page Types (carregado da API)
      pageTypes: {},
      voiceTones: {},
      loading: true,
      
      // Seleções
      selectedPageType: null,
      selectedVoiceTone: 'profissional',
      
      // Input
      companyName: '',
      niche: '',
      userInput: '',
      showExample: false,
      
      // Generation
      generating: false,
      loadingMessage: 'Contratando copywriter...',
      loadingMessages: [
        'Contratando copywriter...',
        'Analisando seu nicho...',
        'Escrevendo rascunho...',
        'Refinando o texto...',
        'Otimizando para SEO...',
        'Quase lá...'
      ],
      loadingIndex: 0,
      loadingInterval: null,
      
      // Result
      generatedContent: null,
      error: null
    };
  },
  
  computed: {
    selectedPageTypeConfig() {
      return this.pageTypes[this.selectedPageType] || null;
    },
    
    selectedVoiceToneConfig() {
      return this.voiceTones[this.selectedVoiceTone] || null;
    }
  },
  
  mounted() {
    this.loadPageTypes();
  },
  
  beforeUnmount() {
    if (this.loadingInterval) {
      clearInterval(this.loadingInterval);
    }
  },
  
  methods: {
    async loadPageTypes() {
      try {
        const response = await fetch(`${this.apiBaseUrl}/page-types.php`);
        const data = await response.json();
        
        if (data.success) {
          this.pageTypes = data.page_types;
          this.voiceTones = data.voice_tones;
        }
      } catch (err) {
        console.error('Erro ao carregar tipos de página:', err);
        // Fallback com tipos básicos
        this.pageTypes = {
          sobre_nos: { label: 'Sobre Nós', icon: '🏢', description: 'História da empresa' },
          servicos: { label: 'Serviços', icon: '⚙️', description: 'Lista de serviços' },
          faq: { label: 'FAQ', icon: '❓', description: 'Perguntas frequentes' }
        };
        this.voiceTones = {
          profissional: { label: 'Profissional', icon: '💼', description: 'Tom formal' }
        };
      } finally {
        this.loading = false;
      }
    },
    
    selectPageType(type) {
      this.selectedPageType = type;
    },
    
    selectVoiceTone(tone) {
      this.selectedVoiceTone = tone;
    },
    
    nextStep() {
      if (this.currentStep < 4) {
        this.currentStep++;
      }
    },
    
    previousStep() {
      if (this.currentStep > 1) {
        this.currentStep--;
      }
    },
    
    startLoading() {
      this.generating = true;
      this.loadingIndex = 0;
      this.loadingMessage = this.loadingMessages[0];
      
      this.loadingInterval = setInterval(() => {
        this.loadingIndex = (this.loadingIndex + 1) % this.loadingMessages.length;
        this.loadingMessage = this.loadingMessages[this.loadingIndex];
      }, 2000);
    },
    
    stopLoading() {
      this.generating = false;
      if (this.loadingInterval) {
        clearInterval(this.loadingInterval);
        this.loadingInterval = null;
      }
    },
    
    async generateContent() {
      this.error = null;
      this.startLoading();
      
      try {
        const response = await fetch(`${this.apiBaseUrl}/generate-content.php`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            page_type: this.selectedPageType,
            user_input: this.userInput,
            voice_tone: this.selectedVoiceTone,
            company_name: this.companyName || null,
            niche: this.niche || null
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          this.generatedContent = data;
          this.currentStep = 4;
          this.$emit('content-generated', data);
        } else {
          this.error = data.error || 'Erro ao gerar conteúdo';
          this.currentStep = 4;
        }
      } catch (err) {
        console.error('Erro na geração:', err);
        this.error = 'Erro de conexão. Verifique sua internet e tente novamente.';
        this.currentStep = 4;
      } finally {
        this.stopLoading();
      }
    },
    
    retryGeneration() {
      this.error = null;
      this.currentStep = 3;
    },
    
    startOver() {
      this.currentStep = 1;
      this.selectedPageType = null;
      this.selectedVoiceTone = 'profissional';
      this.userInput = '';
      this.companyName = '';
      this.niche = '';
      this.generatedContent = null;
      this.error = null;
      this.showExample = false;
    },
    
    handleContentUpdate(updatedContent) {
      this.generatedContent = { ...this.generatedContent, ...updatedContent };
    },
    
    applyContent() {
      this.$emit('content-applied', {
        pageType: this.selectedPageType,
        voiceTone: this.selectedVoiceTone,
        content: this.generatedContent
      });
    }
  }
};
</script>

<style scoped lang="scss">
.ai-content-generator {
  max-width: 900px;
  margin: 0 auto;
  padding: 2rem;
}

// Header
.generator-header {
  text-align: center;
  margin-bottom: 3rem;
  
  .header-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
  }
  
  h2 {
    font-size: 2rem;
    color: #212529;
    margin-bottom: 0.5rem;
  }
  
  p {
    color: #6c757d;
    font-size: 1.1rem;
  }
}

// Steps
.generator-step {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  margin-bottom: 2rem;
  
  h3 {
    font-size: 1.25rem;
    color: #212529;
    margin-bottom: 1.5rem;
  }
}

// Page Type Grid
.page-type-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.page-type-card,
.voice-tone-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1.25rem 1rem;
  border: 2px solid #e9ecef;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: center;
  
  &:hover {
    border-color: #0066CC;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 102, 204, 0.15);
  }
  
  &.active {
    border-color: #0066CC;
    background: rgba(0, 102, 204, 0.05);
    
    .card-label {
      color: #0066CC;
    }
  }
  
  .card-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
  }
  
  .card-label {
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.25rem;
  }
  
  .card-desc {
    font-size: 0.8rem;
    color: #6c757d;
    line-height: 1.3;
  }
}

// Voice Tone Grid
.voice-tone-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

// Input Section
.input-section {
  .input-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    
    .input-icon {
      font-size: 1.5rem;
    }
    
    .input-title {
      font-weight: 600;
      color: #212529;
    }
  }
}

.extra-fields {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1.5rem;
  
  @media (max-width: 600px) {
    grid-template-columns: 1fr;
  }
}

.form-group {
  label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #212529;
    font-size: 0.95rem;
  }
  
  input, textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s;
    font-family: inherit;
    
    &:focus {
      outline: none;
      border-color: #0066CC;
      box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
    }
  }
  
  textarea {
    resize: vertical;
    min-height: 120px;
  }
  
  .input-hint {
    display: block;
    margin-top: 0.5rem;
    color: #6c757d;
    font-size: 0.9rem;
  }
}

.main-input {
  margin-bottom: 1rem;
}

// Example Section
.example-section {
  margin-top: 1rem;
  
  .btn-example {
    background: none;
    border: none;
    color: #0066CC;
    cursor: pointer;
    font-size: 0.95rem;
    padding: 0.5rem 0;
    
    &:hover {
      text-decoration: underline;
    }
  }
  
  .example-content {
    margin-top: 0.75rem;
    padding: 1rem;
    background: #f0f9ff;
    border-radius: 8px;
    border-left: 3px solid #0066CC;
    
    strong {
      display: block;
      margin-bottom: 0.5rem;
      color: #0066CC;
    }
    
    p {
      color: #4a5568;
      font-size: 0.95rem;
      line-height: 1.5;
      margin: 0;
    }
  }
}

// Step Actions
.step-actions {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e9ecef;
}

// Buttons
.btn-primary,
.btn-secondary,
.btn-magic,
.btn-success {
  padding: 0.875rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  
  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
}

.btn-primary {
  background: #0066CC;
  color: white;
  margin-left: auto;
  
  &:hover:not(:disabled) {
    background: #0052a3;
  }
}

.btn-secondary {
  background: #6c757d;
  color: white;
  
  &:hover:not(:disabled) {
    background: #5a6268;
  }
}

.btn-magic {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  margin-left: auto;
  padding: 1rem 2rem;
  
  &:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
  }
  
  .loading-text {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }
  
  .spinner {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
  }
}

.btn-success {
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
  margin-left: auto;
  
  &:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
  }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

// Result Step
.result-step {
  .result-header {
    text-align: center;
    margin-bottom: 2rem;
    
    .result-icon {
      font-size: 3rem;
      margin-bottom: 1rem;
    }
    
    h3 {
      margin-bottom: 0.5rem;
    }
    
    p {
      color: #6c757d;
    }
  }
}

// Error Container
.error-container {
  text-align: center;
  padding: 2rem;
  background: #fff5f5;
  border-radius: 12px;
  border: 1px solid #fed7d7;
  
  .error-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
  }
  
  h4 {
    color: #c53030;
    margin-bottom: 0.5rem;
  }
  
  p {
    color: #742a2a;
    margin-bottom: 1.5rem;
  }
}

// Generated Content
.generated-content {
  position: relative;
  
  .seo-badge {
    position: absolute;
    top: -12px;
    right: 20px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
    
    .badge-icon {
      font-size: 1rem;
    }
  }
}

// SEO Preview
.seo-preview {
  margin-top: 2rem;
  padding: 1.5rem;
  background: #f8f9fa;
  border-radius: 12px;
  
  h4 {
    margin-bottom: 1rem;
    color: #212529;
  }
  
  .google-preview {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    margin-bottom: 1rem;
    
    .google-title {
      color: #1a0dab;
      font-size: 1.1rem;
      margin-bottom: 0.25rem;
      
      &:hover {
        text-decoration: underline;
      }
    }
    
    .google-url {
      color: #006621;
      font-size: 0.85rem;
      margin-bottom: 0.25rem;
    }
    
    .google-description {
      color: #545454;
      font-size: 0.9rem;
      line-height: 1.4;
    }
  }
  
  .keywords-list {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
    
    strong {
      color: #6c757d;
      font-size: 0.9rem;
    }
    
    .keyword-tag {
      padding: 0.25rem 0.75rem;
      background: #e9ecef;
      border-radius: 20px;
      font-size: 0.85rem;
      color: #495057;
    }
  }
}

// Progress Indicator
.progress-indicator {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-top: 2rem;
  
  .progress-dot {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
    
    .dot-number {
      font-size: 0.85rem;
      font-weight: 600;
      color: #6c757d;
    }
    
    &.active {
      background: #0066CC;
      transform: scale(1.1);
      
      .dot-number {
        color: white;
      }
    }
    
    &.completed {
      background: #28a745;
      
      .dot-number {
        color: white;
      }
    }
  }
}

// Responsive
@media (max-width: 768px) {
  .ai-content-generator {
    padding: 1rem;
  }
  
  .generator-step {
    padding: 1.5rem;
  }
  
  .page-type-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .voice-tone-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .step-actions {
    flex-direction: column;
    
    .btn-primary,
    .btn-magic,
    .btn-success {
      margin-left: 0;
      width: 100%;
    }
    
    .btn-secondary {
      width: 100%;
    }
  }
}
</style>
