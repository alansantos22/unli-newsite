<template>
  <div class="form-preview-live">
    <!-- Header -->
    <header class="preview-header">
      <div class="header-content">
        <h3 class="preview-title">📋 Seu Site</h3>
        <span class="preview-subtitle">Cards Vivos - Atualiza em tempo real</span>
      </div>
      <div class="header-stats">
        <span class="stat" :class="{ 'is-complete': overallProgress === 100 }">
          {{ Math.round(overallProgress) }}% completo
        </span>
      </div>
    </header>
    
    <!-- Cards Vivos -->
    <div class="live-cards-container">
      <!-- Card Identidade -->
      <LiveCard
        id="identity"
        type="identity"
        title="Identidade da Marca"
        icon="🎨"
        :form-data="formData"
        :current-step-id="currentStepId"
        :fields="identityFields"
        :required-fields="['companyName', 'businessType']"
        :shimmering-fields="shimmeringFields"
        :recently-filled-fields="recentlyFilledFields"
        :validation-errors="validationErrors"
        image-field="logo"
        image-field-label="seu logotipo"
        @field-edit="handleFieldEdit"
        @field-focus="handleFieldFocus"
        @image-upload="handleImageUpload"
        @image-remove="handleImageRemove"
        @edit-feedback="handleEditFeedback"
      />
      
      <!-- Card Contato -->
      <LiveCard
        id="contact"
        type="contact"
        title="Informações de Contato"
        icon="📞"
        :form-data="formData"
        :current-step-id="currentStepId"
        :fields="contactFields"
        :required-fields="['whatsapp']"
        :shimmering-fields="shimmeringFields"
        :recently-filled-fields="recentlyFilledFields"
        :validation-errors="validationErrors"
        @field-edit="handleFieldEdit"
        @field-focus="handleFieldFocus"
        @edit-feedback="handleEditFeedback"
      />
      
      <!-- Card Sobre -->
      <LiveCard
        id="about"
        type="about"
        title="Sobre a Empresa"
        icon="🏢"
        :form-data="formData"
        :current-step-id="currentStepId"
        :fields="aboutFields"
        :required-fields="[]"
        :shimmering-fields="shimmeringFields"
        :recently-filled-fields="recentlyFilledFields"
        :validation-errors="validationErrors"
        image-field="aboutImage"
        image-field-label="foto da empresa"
        @field-edit="handleFieldEdit"
        @field-focus="handleFieldFocus"
        @image-upload="handleImageUpload"
        @image-remove="handleImageRemove"
        @edit-feedback="handleEditFeedback"
      />
      
      <!-- Card Serviços -->
      <LiveCard
        id="services"
        type="services"
        title="Serviços"
        icon="⚙️"
        :form-data="formData"
        :current-step-id="currentStepId"
        :fields="servicesFields"
        :required-fields="[]"
        :shimmering-fields="shimmeringFields"
        :recently-filled-fields="recentlyFilledFields"
        :validation-errors="validationErrors"
        @field-edit="handleFieldEdit"
        @field-focus="handleFieldFocus"
        @edit-feedback="handleEditFeedback"
      >
        <template #fields>
          <!-- Serviços têm layout customizado -->
          <div class="services-list">
            <div v-if="!formData.services?.length" class="empty-state">
              <span class="empty-icon">📦</span>
              <span class="empty-text">Nenhum serviço cadastrado</span>
            </div>
            <div 
              v-for="(service, index) in formData.services?.slice(0, 3)" 
              :key="service.id || index"
              class="service-item"
            >
              <span class="service-bullet">•</span>
              <span class="service-name">{{ service.name }}</span>
            </div>
            <span v-if="formData.services?.length > 3" class="more-items">
              +{{ formData.services.length - 3 }} mais serviços
            </span>
          </div>
        </template>
      </LiveCard>
      
      <!-- Card FAQ -->
      <LiveCard
        id="faq"
        type="faq"
        title="Perguntas Frequentes"
        icon="❓"
        :form-data="formData"
        :current-step-id="currentStepId"
        :fields="faqFields"
        :required-fields="[]"
        :shimmering-fields="shimmeringFields"
        :recently-filled-fields="recentlyFilledFields"
        :validation-errors="validationErrors"
        @field-edit="handleFieldEdit"
        @field-focus="handleFieldFocus"
        @edit-feedback="handleEditFeedback"
      >
        <template #fields>
          <div class="faq-list">
            <div v-if="!formData.faqItems?.length" class="empty-state">
              <span class="empty-icon">💬</span>
              <span class="empty-text">Nenhuma pergunta cadastrada</span>
            </div>
            <div 
              v-for="(item, index) in formData.faqItems?.slice(0, 2)" 
              :key="item.id || index"
              class="faq-item"
            >
              <span class="faq-question">{{ item.question }}</span>
            </div>
            <span v-if="formData.faqItems?.length > 2" class="more-items">
              +{{ formData.faqItems.length - 2 }} mais perguntas
            </span>
          </div>
        </template>
      </LiveCard>
      
      <!-- Card Finalização -->
      <LiveCard
        id="finalization"
        type="finalization"
        title="Finalização"
        icon="🚀"
        :form-data="formData"
        :current-step-id="currentStepId"
        :fields="finalizationFields"
        :required-fields="[]"
        :shimmering-fields="shimmeringFields"
        :recently-filled-fields="recentlyFilledFields"
        :validation-errors="validationErrors"
        @field-edit="handleFieldEdit"
        @field-focus="handleFieldFocus"
        @edit-feedback="handleEditFeedback"
      />
    </div>
    
    <!-- Indicador de Step Atual -->
    <div class="current-step-indicator">
      <span class="indicator-pulse"></span>
      <span class="indicator-text">Conversando sobre: <strong>{{ currentStepName }}</strong></span>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';
import LiveCard from './LiveCard.vue';

// Mapeamento de business types
const businessTypeLabels = {
  alimentacao: '🍔 Alimentação',
  saude: '🏥 Saúde',
  beleza: '💅 Beleza',
  educacao: '📚 Educação',
  tecnologia: '💻 Tecnologia',
  construcao: '🏗️ Construção',
  moda: '👗 Moda',
  servicos: '🔧 Serviços',
  juridico: '⚖️ Jurídico',
  eventos: '🎉 Eventos',
  turismo: '✈️ Turismo',
  imoveis: '🏡 Imóveis',
  automotivo: '🚗 Automotivo',
  pets: '🐾 Pets',
  comercio: '🏪 Comércio',
  industria: '🏭 Indústria',
  outro: '📦 Outro'
};

// Mapeamento de urgência
const urgencyLabels = {
  urgent: '🔥 Urgente',
  normal: '📅 Normal',
  relaxed: '🧘 Tranquilo'
};

// Mapeamento de steps para nomes
const stepNames = {
  identity: 'Identidade da Marca',
  contact: 'Contato',
  about: 'Sobre a Empresa',
  services: 'Serviços',
  faq: 'Perguntas Frequentes',
  finalization: 'Finalização'
};

export default {
  name: 'FormPreviewLive',
  
  components: {
    LiveCard
  },
  
  props: {
    formData: {
      type: Object,
      required: true
    },
    currentStepId: {
      type: String,
      default: ''
    },
    shimmeringFields: {
      type: Array,
      default: () => []
    },
    recentlyFilledFields: {
      type: Array,
      default: () => []
    },
    validationErrors: {
      type: Object,
      default: () => ({})
    }
  },
  
  emits: [
    'field-click',
    'field-edit', 
    'image-upload',
    'image-remove',
    'edit-feedback',
    'send-chat-message'
  ],
  
  setup(props, { emit }) {
    // ============================================
    // COMPUTED - Definição de campos por card
    // ============================================
    
    const identityFields = computed(() => [
      { id: 'companyName', label: 'Nome', placeholder: 'Nome da empresa' },
      { 
        id: 'businessType', 
        label: 'Ramo',
        editable: false,
        formatter: (val) => businessTypeLabels[val] || val
      },
      { id: 'frase', label: 'Frase', placeholder: 'Slogan ou frase de efeito' },
      { id: 'voiceTone', label: 'Tom de Voz', placeholder: 'Formal, descontraído...' }
    ]);
    
    const contactFields = computed(() => [
      { id: 'whatsapp', label: 'WhatsApp', placeholder: '(11) 99999-9999' },
      { id: 'email', label: 'E-mail', placeholder: 'contato@empresa.com' },
      { 
        id: 'socialNetworks', 
        label: 'Redes Sociais',
        editable: false,
        formatter: (val) => {
          if (!val || !Array.isArray(val)) return 'Não configuradas';
          return `${val.length} rede(s) configurada(s)`;
        }
      },
      {
        id: 'hasPhysicalLocation',
        label: 'Endereço',
        editable: false,
        formatter: (val) => {
          if (!val) return 'Somente online';
          return props.formData.addressCity 
            ? `${props.formData.addressCity}, ${props.formData.addressState}`
            : 'Com endereço físico';
        }
      }
    ]);
    
    const aboutFields = computed(() => [
      { id: 'foundingYear', label: 'Fundação', placeholder: 'Ano de fundação' },
      { id: 'companyBio', label: 'História', placeholder: 'Conte a história da empresa' },
      {
        id: 'companyHighlights',
        label: 'Diferenciais',
        editable: false,
        formatter: (val) => {
          if (!val || !Array.isArray(val)) return 'Não definidos';
          return `${val.length} diferencial(is)`;
        }
      }
    ]);
    
    const servicesFields = computed(() => [
      {
        id: 'services',
        label: 'Serviços',
        editable: false,
        formatter: (val) => {
          if (!val || !Array.isArray(val)) return 'Nenhum serviço';
          return `${val.length} serviço(s) cadastrado(s)`;
        }
      }
    ]);
    
    const faqFields = computed(() => [
      {
        id: 'faqItems',
        label: 'Perguntas',
        editable: false,
        formatter: (val) => {
          if (!val || !Array.isArray(val)) return 'Nenhuma pergunta';
          return `${val.length} pergunta(s)`;
        }
      }
    ]);
    
    const finalizationFields = computed(() => [
      {
        id: 'urgency',
        label: 'Urgência',
        editable: false,
        formatter: (val) => urgencyLabels[val] || 'Não definida'
      },
      { id: 'additionalNotes', label: 'Observações', placeholder: 'Notas adicionais' }
    ]);
    
    // Nome do step atual
    const currentStepName = computed(() => {
      return stepNames[props.currentStepId] || 'Onboarding';
    });
    
    // Progresso geral
    const overallProgress = computed(() => {
      const requiredFields = [
        'companyName', 'businessType', 'whatsapp'
      ];
      const optionalFields = [
        'frase', 'primaryColor', 'secondaryColor', 'logo',
        'email', 'socialNetworks', 'foundingYear', 'companyBio',
        'services', 'faqItems', 'urgency'
      ];
      
      let filled = 0;
      let total = requiredFields.length;
      
      // Campos obrigatórios (peso maior)
      requiredFields.forEach(field => {
        const val = props.formData[field];
        if (val && (Array.isArray(val) ? val.length > 0 : true)) {
          filled += 1;
        }
      });
      
      // Campos opcionais (peso menor - 0.5 cada)
      optionalFields.forEach(field => {
        const val = props.formData[field];
        if (val && (Array.isArray(val) ? val.length > 0 : true)) {
          filled += 0.5;
        }
        total += 0.5;
      });
      
      return (filled / total) * 100;
    });
    
    // ============================================
    // METHODS
    // ============================================
    
    function handleFieldEdit(fieldId, value) {
      emit('field-edit', fieldId, value);
    }
    
    function handleFieldFocus(fieldId) {
      emit('field-click', fieldId);
    }
    
    function handleImageUpload(data) {
      emit('image-upload', data);
    }
    
    function handleImageRemove(data) {
      emit('image-remove', data);
    }
    
    function handleEditFeedback(data) {
      emit('edit-feedback', data);
      
      // Emitir mensagem para o chat
      emit('send-chat-message', `Perfeito, alteração salva no campo "${data.label}"!`);
    }
    
    return {
      // Fields config
      identityFields,
      contactFields,
      aboutFields,
      servicesFields,
      faqFields,
      finalizationFields,
      
      // Computed
      currentStepName,
      overallProgress,
      
      // Methods
      handleFieldEdit,
      handleFieldFocus,
      handleImageUpload,
      handleImageRemove,
      handleEditFeedback
    };
  }
};
</script>

<style lang="scss" scoped>
// ============================================
// FORM PREVIEW LIVE - Container Principal
// ============================================

.form-preview-live {
  display: flex;
  flex-direction: column;
  height: 100%;
  padding: 1rem;
  overflow-y: auto;
}

// ============================================
// HEADER
// ============================================

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.25rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.header-content {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.preview-title {
  font-size: 1.125rem;
  font-weight: 700;
  margin: 0;
  color: #fff;
}

.preview-subtitle {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.5);
}

.header-stats {
  .stat {
    display: inline-block;
    padding: 0.375rem 0.75rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 2rem;
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.7);
    
    &.is-complete {
      background: rgba(34, 197, 94, 0.15);
      color: #86efac;
    }
  }
}

// ============================================
// CARDS CONTAINER
// ============================================

.live-cards-container {
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
  flex: 1;
}

// ============================================
// CUSTOM CARD CONTENT (Services, FAQ)
// ============================================

.services-list,
.faq-list {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.empty-state {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.02);
  border-radius: 0.5rem;
  
  .empty-icon {
    font-size: 1rem;
    opacity: 0.5;
  }
  
  .empty-text {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.4);
    font-style: italic;
  }
}

.service-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.375rem 0;
  
  .service-bullet {
    color: #6366f1;
  }
  
  .service-name {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.8);
  }
}

.faq-item {
  padding: 0.5rem;
  background: rgba(255, 255, 255, 0.02);
  border-radius: 0.375rem;
  
  .faq-question {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.7);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
}

.more-items {
  font-size: 0.75rem;
  color: #a5b4fc;
  padding: 0.25rem 0;
}

// ============================================
// CURRENT STEP INDICATOR
// ============================================

.current-step-indicator {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  margin-top: auto;
  padding: 1rem;
  background: rgba(99, 102, 241, 0.1);
  border: 1px solid rgba(99, 102, 241, 0.2);
  border-radius: 0.75rem;
}

.indicator-pulse {
  width: 8px;
  height: 8px;
  background: #6366f1;
  border-radius: 50%;
  animation: pulse-glow 1.5s ease-in-out infinite;
}

.indicator-text {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.7);
  
  strong {
    color: #a5b4fc;
  }
}

// ============================================
// ANIMATIONS
// ============================================

@keyframes pulse-glow {
  0%, 100% { 
    opacity: 1;
    box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.4);
  }
  50% { 
    opacity: 0.6;
    box-shadow: 0 0 0 8px rgba(99, 102, 241, 0);
  }
}

// ============================================
// SCROLLBAR (aplicado ao container principal)
// ============================================

.form-preview-live {
  &::-webkit-scrollbar {
    width: 4px;
  }

  &::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.02);
  }

  &::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 2px;
    
    &:hover {
      background: rgba(255, 255, 255, 0.2);
    }
  }
}

// ============================================
// RESPONSIVE
// ============================================

@media (max-width: 768px) {
  .form-preview-live {
    padding: 0.75rem;
  }
  
  .preview-header {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .live-cards-container {
    gap: 0.75rem;
  }
}
</style>
