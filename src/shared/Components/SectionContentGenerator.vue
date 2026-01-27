<template>
  <div class="section-content-generator">
    <!-- Header com progresso -->
    <header class="generator-header">
      <div class="header-info">
        <span class="page-icon">{{ pageConfig?.icon }}</span>
        <div class="header-text">
          <h3>{{ pageConfig?.title }}</h3>
          <p>Gerando conteúdo seção por seção</p>
        </div>
      </div>
      
      <!-- AI Usage indicator -->
      <div class="ai-usage-badge" :class="{ 'low': aiStatus.remaining <= 3 }">
        <span class="usage-icon">✨</span>
        <span class="usage-text">{{ aiStatus.remaining }}/{{ aiStatus.limit }} restantes</span>
      </div>
    </header>
    
    <!-- Lista de Seções -->
    <div class="sections-list">
      <div 
        v-for="(section, index) in sections" 
        :key="section.id"
        class="section-card"
        :class="{ 
          'is-generated': section.generated,
          'is-active': activeSectionIndex === index
        }"
      >
        <!-- Section Header -->
        <div class="section-header" @click="toggleSection(index)">
          <div class="section-info">
            <span class="section-number">{{ index + 1 }}</span>
            <span class="section-title">{{ section.title || `Seção ${index + 1}` }}</span>
            <span v-if="section.generated" class="generated-badge">✓ Gerado</span>
          </div>
          <div class="section-actions">
            <button 
              v-if="!section.generated && canGenerate"
              type="button"
              class="btn-generate-section"
              :disabled="isGenerating"
              @click.stop="generateSection(index)"
            >
              ✨ Gerar
            </button>
            <button 
              v-if="section.generated"
              type="button"
              class="btn-regenerate"
              :disabled="isGenerating || !canGenerate"
              @click.stop="regenerateSection(index)"
            >
              🔄
            </button>
            <button 
              type="button"
              class="btn-toggle"
              :class="{ 'is-open': activeSectionIndex === index }"
            >
              ▼
            </button>
          </div>
        </div>
        
        <!-- Section Content (Expandable) -->
        <Transition name="slide">
          <div v-if="activeSectionIndex === index" class="section-content">
            <!-- Input com limite de caracteres -->
            <div class="input-group">
              <label>{{ section.inputLabel }}</label>
              <textarea
                v-model="section.input"
                :placeholder="section.placeholder"
                :maxlength="MAX_CHARS"
                rows="3"
                :disabled="isGenerating"
              />
              <div class="char-counter" :class="{ 'near-limit': section.input?.length > MAX_CHARS - 50 }">
                {{ section.input?.length || 0 }}/{{ MAX_CHARS }}
              </div>
            </div>
            
            <!-- Output gerado -->
            <div v-if="section.generated" class="output-group">
              <label>Resultado</label>
              <textarea
                v-model="section.output"
                rows="4"
                placeholder="Conteúdo gerado aparecerá aqui..."
              />
            </div>
            
            <!-- Loading state -->
            <div v-if="isGenerating && generatingIndex === index" class="generating-indicator">
              <span class="spinner"></span>
              <span>{{ loadingMessage }}</span>
            </div>
          </div>
        </Transition>
      </div>
      
      <!-- Add Section (para FAQ) -->
      <button 
        v-if="allowAddSections && sections.length < maxSections"
        type="button"
        class="btn-add-section"
        @click="addSection"
      >
        + Adicionar {{ sectionLabel }}
      </button>
    </div>
    
    <!-- Generate All Button -->
    <div class="generator-footer">
      <div class="footer-info">
        <span class="sections-count">
          {{ generatedCount }}/{{ sections.length }} seções geradas
        </span>
      </div>
      
      <div class="footer-actions">
        <button 
          type="button"
          class="btn-generate-all"
          :disabled="isGenerating || !canGenerate || allGenerated"
          @click="generateAll"
        >
          <span v-if="!isGenerating">✨ Gerar Todas ({{ pendingCount }})</span>
          <span v-else class="loading">
            <span class="spinner"></span>
            Gerando {{ generatingIndex + 1 }}/{{ sections.length }}...
          </span>
        </button>
      </div>
    </div>
    
    <!-- Modal de Limite Atingido -->
    <BaseModal
      v-model="showLimitModal"
      title="Limite Diário Atingido"
      icon="⚠️"
      type="warning"
      size="small"
      :show-confirm="false"
      cancel-text="Entendi"
    >
      <div class="limit-modal-content">
        <div class="limit-icon">🤖</div>
        <p class="limit-message">
          Você já utilizou suas <strong>{{ aiStatus.limit }} gerações</strong> de IA disponíveis hoje.
        </p>
        <div class="limit-info">
          <span class="info-icon">⏰</span>
          <span>Novo limite disponível em <strong>{{ aiStatus.resetTime }}</strong></span>
        </div>
        <p class="limit-tip">
          💡 <em>Enquanto isso, você pode editar manualmente os textos já gerados.</em>
        </p>
      </div>
    </BaseModal>
  </div>
</template>

<script>
import BaseModal from './BaseModal.vue';
import { checkAILimit, recordAIUsage } from '@/core/services/ai-rate-limiter.js';

export default {
  name: 'SectionContentGenerator',
  
  components: {
    BaseModal
  },
  
  props: {
    // Configuração da página (sobre_nos, servicos, faq, etc)
    pageType: {
      type: String,
      required: true
    },
    // Dados coletados no onboarding
    onboardingData: {
      type: Object,
      default: () => ({})
    },
    // Tom de voz selecionado
    voiceTone: {
      type: String,
      default: 'profissional'
    },
    // Permite adicionar seções (FAQ)
    allowAddSections: {
      type: Boolean,
      default: false
    },
    // Máximo de seções
    maxSections: {
      type: Number,
      default: 10
    },
    // Label para adicionar seção
    sectionLabel: {
      type: String,
      default: 'Seção'
    },
    // Seções iniciais
    initialSections: {
      type: Array,
      default: () => []
    }
  },
  
  emits: ['update', 'complete'],
  
  data() {
    return {
      MAX_CHARS: 500,
      sections: [],
      activeSectionIndex: 0,
      isGenerating: false,
      generatingIndex: -1,
      loadingMessage: 'Processando...',
      showLimitModal: false,
      aiStatus: {
        canUse: true,
        remaining: 10,
        limit: 10,
        resetTime: ''
      }
    };
  },
  
  computed: {
    pageConfig() {
      const configs = {
        sobre_nos: {
          icon: '🏢',
          title: 'Sobre a Empresa',
          defaultSections: [
            { id: 'historia', title: 'Nossa História', inputLabel: 'Conte brevemente sua história', placeholder: 'Ex: Começamos em 2015 quando...' },
            { id: 'missao', title: 'Missão', inputLabel: 'Qual a missão da empresa?', placeholder: 'Ex: Transformar a vida dos clientes...' },
            { id: 'visao', title: 'Visão', inputLabel: 'Onde querem chegar?', placeholder: 'Ex: Ser referência no mercado de...' },
            { id: 'valores', title: 'Valores', inputLabel: 'Quais são os valores?', placeholder: 'Ex: Qualidade, Transparência, Inovação' }
          ]
        },
        servicos: {
          icon: '⚙️',
          title: 'Serviços',
          defaultSections: [
            { id: 'intro', title: 'Introdução', inputLabel: 'Descreva seu negócio', placeholder: 'Ex: Somos especialistas em...' }
          ]
        },
        faq: {
          icon: '❓',
          title: 'Perguntas Frequentes',
          defaultSections: [
            { id: 'q1', title: 'Pergunta 1', inputLabel: 'Qual a pergunta?', placeholder: 'Ex: Como funciona o pagamento?' }
          ]
        },
        portfolio: {
          icon: '🖼️',
          title: 'Portfólio',
          defaultSections: [
            { id: 'intro', title: 'Apresentação', inputLabel: 'Introdução do portfólio', placeholder: 'Ex: Conheça nossos melhores trabalhos...' }
          ]
        }
      };
      return configs[this.pageType] || { icon: '📄', title: 'Conteúdo', defaultSections: [] };
    },
    
    generatedCount() {
      return this.sections.filter(s => s.generated).length;
    },
    
    pendingCount() {
      return this.sections.filter(s => !s.generated && s.input?.trim()).length;
    },
    
    allGenerated() {
      return this.sections.every(s => s.generated);
    },
    
    canGenerate() {
      return this.aiStatus.canUse;
    }
  },
  
  watch: {
    sections: {
      deep: true,
      handler() {
        this.emitUpdate();
      }
    }
  },
  
  mounted() {
    this.initializeSections();
    this.refreshAIStatus();
  },
  
  methods: {
    initializeSections() {
      if (this.initialSections.length > 0) {
        this.sections = this.initialSections.map((s, i) => ({
          ...s,
          id: s.id || `section_${i}`,
          input: s.input || '',
          output: s.output || '',
          generated: !!s.output
        }));
      } else {
        // Usa seções default baseadas no tipo de página
        this.sections = this.pageConfig.defaultSections.map(s => ({
          ...s,
          input: this.prefillFromOnboarding(s.id),
          output: '',
          generated: false
        }));
      }
      
      // Para FAQ, adiciona seções dos dados do onboarding
      if (this.pageType === 'faq' && this.onboardingData?.customQuestions) {
        this.onboardingData.customQuestions.forEach((q, i) => {
          this.sections.push({
            id: `q_${i + 2}`,
            title: `Pergunta ${i + 2}`,
            inputLabel: 'Qual a pergunta?',
            placeholder: 'Ex: Vocês entregam em todo o Brasil?',
            input: q.question || '',
            output: q.answer || '',
            generated: !!q.answer
          });
        });
      }
    },
    
    prefillFromOnboarding(sectionId) {
      const data = this.onboardingData;
      if (!data) return '';
      
      const mapping = {
        historia: data.foundingStory || '',
        missao: data.mission || '',
        visao: data.vision || '',
        valores: Array.isArray(data.companyValues) ? data.companyValues.join(', ') : ''
      };
      
      return mapping[sectionId] || '';
    },
    
    refreshAIStatus() {
      this.aiStatus = { ...checkAILimit(), limit: 10 };
    },
    
    toggleSection(index) {
      this.activeSectionIndex = this.activeSectionIndex === index ? -1 : index;
    },
    
    addSection() {
      const num = this.sections.length + 1;
      this.sections.push({
        id: `section_${Date.now()}`,
        title: `${this.sectionLabel} ${num}`,
        inputLabel: this.pageType === 'faq' ? 'Qual a pergunta?' : 'Descreva esta seção',
        placeholder: 'Digite aqui...',
        input: '',
        output: '',
        generated: false
      });
      this.activeSectionIndex = this.sections.length - 1;
    },
    
    async generateSection(index) {
      // Verifica limite
      this.refreshAIStatus();
      if (!this.aiStatus.canUse) {
        this.showLimitModal = true;
        return;
      }
      
      const section = this.sections[index];
      if (!section.input?.trim()) return;
      
      this.isGenerating = true;
      this.generatingIndex = index;
      this.loadingMessage = 'Escrevendo...';
      
      try {
        const response = await fetch('/api/ai/generate-section.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            page_type: this.pageType,
            section_id: section.id,
            section_title: section.title,
            input: section.input.substring(0, this.MAX_CHARS),
            voice_tone: this.voiceTone,
            company_name: this.onboardingData?.companyName || ''
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          section.output = data.content;
          section.generated = true;
          
          // Registra uso
          this.aiStatus = { ...recordAIUsage(), limit: 10 };
        } else {
          throw new Error(data.error || 'Erro ao gerar');
        }
        
      } catch (error) {
        console.error('Generation error:', error);
        section.output = `[Erro: ${error.message}]`;
      } finally {
        this.isGenerating = false;
        this.generatingIndex = -1;
      }
    },
    
    async regenerateSection(index) {
      this.sections[index].generated = false;
      this.sections[index].output = '';
      await this.generateSection(index);
    },
    
    async generateAll() {
      const pendingSections = this.sections
        .map((s, i) => ({ section: s, index: i }))
        .filter(({ section }) => !section.generated && section.input?.trim());
      
      for (const { index } of pendingSections) {
        // Verifica limite antes de cada geração
        this.refreshAIStatus();
        if (!this.aiStatus.canUse) {
          this.showLimitModal = true;
          break;
        }
        
        await this.generateSection(index);
        
        // Pequeno delay entre requisições
        await new Promise(r => setTimeout(r, 500));
      }
      
      if (this.allGenerated) {
        this.$emit('complete', this.sections);
      }
    },
    
    emitUpdate() {
      this.$emit('update', {
        pageType: this.pageType,
        sections: this.sections.map(s => ({
          id: s.id,
          title: s.title,
          input: s.input,
          output: s.output,
          generated: s.generated
        }))
      });
    }
  }
};
</script>

<style lang="scss" scoped>
.section-content-generator {
  background: #fff;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

// Header
.generator-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: #fff;
  
  .header-info {
    display: flex;
    align-items: center;
    gap: 1rem;
  }
  
  .page-icon {
    font-size: 2rem;
  }
  
  .header-text {
    h3 {
      margin: 0 0 0.25rem 0;
      font-size: 1.15rem;
    }
    
    p {
      margin: 0;
      font-size: 0.85rem;
      opacity: 0.9;
    }
  }
}

.ai-usage-badge {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  font-size: 0.85rem;
  
  &.low {
    background: rgba(231, 76, 60, 0.3);
    animation: pulse 2s infinite;
  }
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

// Sections List
.sections-list {
  padding: 1rem;
}

.section-card {
  background: #f8f9fa;
  border: 2px solid #e9ecef;
  border-radius: 12px;
  margin-bottom: 0.75rem;
  overflow: hidden;
  transition: all 0.2s;
  
  &.is-active {
    border-color: #667eea;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);
  }
  
  &.is-generated {
    border-color: #27ae60;
    
    .section-number {
      background: #27ae60;
    }
  }
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  cursor: pointer;
  
  &:hover {
    background: rgba(0, 0, 0, 0.02);
  }
}

.section-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.section-number {
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #667eea;
  color: #fff;
  border-radius: 50%;
  font-size: 0.85rem;
  font-weight: 600;
}

.section-title {
  font-weight: 600;
  color: #333;
}

.generated-badge {
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
  background: #d4edda;
  color: #155724;
  border-radius: 12px;
}

.section-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-generate-section {
  padding: 0.5rem 1rem;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  
  &:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  }
  
  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
}

.btn-regenerate {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #e9ecef;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  
  &:hover:not(:disabled) {
    background: #dee2e6;
  }
}

.btn-toggle {
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  color: #666;
  font-size: 0.75rem;
  transition: transform 0.2s;
  
  &.is-open {
    transform: rotate(180deg);
  }
}

// Section Content
.section-content {
  padding: 0 1rem 1rem;
}

.input-group,
.output-group {
  margin-bottom: 1rem;
  
  label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #555;
    margin-bottom: 0.5rem;
  }
  
  textarea {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 0.95rem;
    font-family: inherit;
    resize: vertical;
    transition: border-color 0.2s;
    
    &:focus {
      outline: none;
      border-color: #667eea;
    }
    
    &:disabled {
      background: #f0f0f0;
    }
  }
}

.char-counter {
  text-align: right;
  font-size: 0.75rem;
  color: #999;
  margin-top: 0.25rem;
  
  &.near-limit {
    color: #e74c3c;
    font-weight: 600;
  }
}

.output-group textarea {
  background: #f0fff4;
  border-color: #27ae60;
}

.generating-indicator {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background: #f0f8ff;
  border-radius: 8px;
  color: #3498db;
  font-size: 0.9rem;
}

// Add Section Button
.btn-add-section {
  width: 100%;
  padding: 1rem;
  background: none;
  border: 2px dashed #ccc;
  border-radius: 12px;
  color: #666;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  
  &:hover {
    border-color: #667eea;
    color: #667eea;
    background: rgba(102, 126, 234, 0.05);
  }
}

// Footer
.generator-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background: #f8f9fa;
  border-top: 1px solid #e9ecef;
}

.sections-count {
  font-size: 0.9rem;
  color: #666;
}

.btn-generate-all {
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, #27ae60, #219a52);
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  
  &:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
  }
  
  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
  
  .loading {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
}

.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

// Limit Modal
.limit-modal-content {
  text-align: center;
  padding: 1rem 0;
  
  .limit-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
  }
  
  .limit-message {
    font-size: 1.1rem;
    color: #333;
    margin-bottom: 1.5rem;
    
    strong {
      color: #e67e22;
    }
  }
  
  .limit-info {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    background: #fff8e1;
    border-radius: 10px;
    color: #856404;
    margin-bottom: 1rem;
    
    .info-icon {
      font-size: 1.25rem;
    }
  }
  
  .limit-tip {
    font-size: 0.9rem;
    color: #666;
    margin: 0;
  }
}

// Slide Transition
.slide-enter-active,
.slide-leave-active {
  transition: all 0.25s ease;
  max-height: 500px;
  overflow: hidden;
}

.slide-enter-from,
.slide-leave-to {
  max-height: 0;
  opacity: 0;
  padding-top: 0;
  padding-bottom: 0;
}

// Responsive
@media (max-width: 600px) {
  .generator-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
    
    .header-info {
      flex-direction: column;
    }
  }
  
  .generator-footer {
    flex-direction: column;
    gap: 1rem;
    
    .btn-generate-all {
      width: 100%;
    }
  }
}
</style>
