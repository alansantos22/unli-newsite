<template>
  <div class="form-preview">
    <header class="preview-header">
      <h3 class="preview-title">📋 Seu Site</h3>
      <span class="preview-subtitle">Atualiza em tempo real</span>
    </header>
    
    <!-- Identity Section -->
    <div class="preview-section">
      <div class="section-header">
        <span class="section-icon">🎨</span>
        <h4 class="section-title">Identidade</h4>
        <span 
          v-if="isSectionComplete('identity')" 
          class="section-status complete"
        >✓</span>
      </div>
      
      <div class="section-fields">
        <PreviewField
          field-id="companyName"
          label="Nome"
          :value="formData.companyName"
          :is-shimmering="shimmeringFields.includes('companyName')"
          :is-recently-filled="recentlyFilledFields.includes('companyName')"
          :error="validationErrors.companyName"
          @click="$emit('field-click', 'companyName')"
          @edit="(val) => $emit('field-edit', 'companyName', val)"
        />
        
        <PreviewField
          field-id="businessType"
          label="Ramo"
          :value="formatBusinessType(formData.businessType)"
          :is-shimmering="shimmeringFields.includes('businessType')"
          :is-recently-filled="recentlyFilledFields.includes('businessType')"
          :error="validationErrors.businessType"
          :editable="false"
          @click="$emit('field-click', 'businessType')"
        />
        
        <PreviewField
          field-id="tagline"
          label="Frase"
          :value="formData.tagline"
          :is-shimmering="shimmeringFields.includes('tagline')"
          :is-recently-filled="recentlyFilledFields.includes('tagline')"
          @click="$emit('field-click', 'tagline')"
          @edit="(val) => $emit('field-edit', 'tagline', val)"
        />
        
        <div class="color-preview">
          <span class="color-label">Cores:</span>
          <div class="color-swatches">
            <span 
              class="color-swatch"
              :class="{ 'is-shimmering': shimmeringFields.includes('primaryColor') }"
              :style="{ backgroundColor: formData.primaryColor || '#ccc' }"
              :title="formData.primaryColor"
            ></span>
            <span 
              class="color-swatch"
              :class="{ 'is-shimmering': shimmeringFields.includes('secondaryColor') }"
              :style="{ backgroundColor: formData.secondaryColor || '#ccc' }"
              :title="formData.secondaryColor"
            ></span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Contact Section -->
    <div class="preview-section">
      <div class="section-header">
        <span class="section-icon">📞</span>
        <h4 class="section-title">Contato</h4>
        <span 
          v-if="isSectionComplete('contact')" 
          class="section-status complete"
        >✓</span>
      </div>
      
      <div class="section-fields">
        <PreviewField
          field-id="whatsapp"
          label="WhatsApp"
          :value="formData.whatsapp"
          :is-shimmering="shimmeringFields.includes('whatsapp')"
          :is-recently-filled="recentlyFilledFields.includes('whatsapp')"
          :error="validationErrors.whatsapp"
          @click="$emit('field-click', 'whatsapp')"
          @edit="(val) => $emit('field-edit', 'whatsapp', val)"
        />
        
        <div v-if="socialNetworksCount" class="social-preview">
          <span class="social-label">Redes Sociais:</span>
          <span class="social-count">{{ socialNetworksCount }} configurada(s)</span>
        </div>
        
        <div v-if="formData.hasPhysicalLocation" class="address-preview">
          <span class="address-icon">📍</span>
          <span class="address-text">
            {{ formData.addressCity || 'Cidade não definida' }}
          </span>
        </div>
      </div>
    </div>
    
    <!-- About Section -->
    <div class="preview-section">
      <div class="section-header">
        <span class="section-icon">🏢</span>
        <h4 class="section-title">Sobre</h4>
        <span 
          v-if="isSectionComplete('about')" 
          class="section-status complete"
        >✓</span>
      </div>
      
      <div class="section-fields">
        <PreviewField
          v-if="formData.foundingYear"
          field-id="foundingYear"
          label="Desde"
          :value="formData.foundingYear"
          :is-recently-filled="recentlyFilledFields.includes('foundingYear')"
          @click="$emit('field-click', 'foundingYear')"
          @edit="(val) => $emit('field-edit', 'foundingYear', val)"
        />
        
        <div v-if="formData.companyBio" class="bio-preview">
          <span class="bio-label">História:</span>
          <p class="bio-text">{{ truncate(formData.companyBio, 100) }}</p>
        </div>
        
        <div v-if="highlightsCount" class="highlights-preview">
          <span class="highlights-label">Diferenciais:</span>
          <span class="highlights-count">{{ highlightsCount }} item(s)</span>
        </div>
      </div>
    </div>
    
    <!-- Services Section -->
    <div class="preview-section">
      <div class="section-header">
        <span class="section-icon">⚙️</span>
        <h4 class="section-title">Serviços</h4>
        <span 
          v-if="servicesCount > 0" 
          class="section-status complete"
        >✓</span>
      </div>
      
      <div class="section-fields">
        <div v-if="servicesCount" class="services-preview">
          <div 
            v-for="service in visibleServices" 
            :key="service.id || service.name"
            class="service-item"
          >
            <span class="service-icon">•</span>
            <span class="service-name">{{ service.name }}</span>
          </div>
          <span v-if="servicesCount > 3" class="services-more">
            +{{ servicesCount - 3 }} mais
          </span>
        </div>
        <div v-else class="empty-hint">
          Nenhum serviço cadastrado
        </div>
      </div>
    </div>
    
    <!-- Current Step Indicator -->
    <div 
      class="current-step-indicator"
      :class="{ 'is-active': true }"
    >
      <span class="indicator-icon">→</span>
      <span class="indicator-text">Você está em: {{ currentStepName }}</span>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';
import PreviewField from './PreviewField.vue';

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

const stepNames = {
  identity: 'Identidade',
  contact: 'Contato',
  about: 'História',
  services: 'Serviços',
  faq: 'FAQ',
  finalization: 'Finalização'
};

export default {
  name: 'FormPreview',
  
  components: {
    PreviewField
  },
  
  props: {
    formData: {
      type: Object,
      required: true
    },
    currentStepId: {
      type: String,
      default: 'identity'
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
  
  emits: ['field-click', 'field-edit'],
  
  setup(props) {
    const socialNetworksCount = computed(() => {
      return props.formData.socialNetworks?.length || 0;
    });
    
    const highlightsCount = computed(() => {
      return props.formData.companyHighlights?.length || 0;
    });
    
    const servicesCount = computed(() => {
      return props.formData.services?.length || 0;
    });
    
    const visibleServices = computed(() => {
      return (props.formData.services || []).slice(0, 3);
    });
    
    const currentStepName = computed(() => {
      return stepNames[props.currentStepId] || props.currentStepId;
    });
    
    function formatBusinessType(type) {
      return businessTypeLabels[type] || type || '';
    }
    
    function isSectionComplete(section) {
      const checks = {
        identity: () => props.formData.companyName && props.formData.businessType,
        contact: () => props.formData.whatsapp,
        about: () => props.formData.companyBio || props.formData.foundingYear,
        services: () => servicesCount.value > 0
      };
      
      return checks[section] ? checks[section]() : false;
    }
    
    function truncate(text, length) {
      if (!text) return '';
      if (text.length <= length) return text;
      return text.substring(0, length) + '...';
    }
    
    return {
      socialNetworksCount,
      highlightsCount,
      servicesCount,
      visibleServices,
      currentStepName,
      formatBusinessType,
      isSectionComplete,
      truncate
    };
  }
};
</script>

<style lang="scss" scoped>
.form-preview {
  padding: 1.5rem;
  height: 100%;
  overflow-y: auto;
}

.preview-header {
  margin-bottom: 1.5rem;
  
  .preview-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0 0 0.25rem;
  }
  
  .preview-subtitle {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.5);
  }
}

// ============================================
// SECTIONS
// ============================================

.preview-section {
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 0.75rem;
  
  .section-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
    
    .section-icon {
      font-size: 1rem;
    }
    
    .section-title {
      flex: 1;
      font-size: 0.875rem;
      font-weight: 600;
      margin: 0;
    }
    
    .section-status {
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(34, 197, 94, 0.2);
      border-radius: 50%;
      font-size: 0.7rem;
      color: #22c55e;
      
      &.complete {
        animation: check-pop 0.3s ease-out;
      }
    }
  }
  
  .section-fields {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }
}

@keyframes check-pop {
  0% { transform: scale(0); }
  50% { transform: scale(1.3); }
  100% { transform: scale(1); }
}

// ============================================
// SPECIAL PREVIEWS
// ============================================

.color-preview {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 0;
  
  .color-label {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.6);
  }
  
  .color-swatches {
    display: flex;
    gap: 0.5rem;
  }
  
  .color-swatch {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    border: 2px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s;
    
    &.is-shimmering {
      animation: shimmer 1.5s ease-in-out infinite;
    }
  }
}

@keyframes shimmer {
  0%, 100% { opacity: 0.5; }
  50% { opacity: 1; }
}

.social-preview,
.address-preview,
.bio-preview,
.highlights-preview,
.services-preview {
  padding: 0.5rem 0;
  font-size: 0.875rem;
}

.social-preview,
.highlights-preview {
  display: flex;
  justify-content: space-between;
  
  .social-label,
  .highlights-label {
    color: rgba(255, 255, 255, 0.6);
  }
  
  .social-count,
  .highlights-count {
    color: #22c55e;
  }
}

.address-preview {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  
  .address-icon {
    font-size: 0.875rem;
  }
  
  .address-text {
    color: rgba(255, 255, 255, 0.8);
  }
}

.bio-preview {
  .bio-label {
    display: block;
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.6);
    margin-bottom: 0.25rem;
  }
  
  .bio-text {
    margin: 0;
    font-size: 0.8rem;
    line-height: 1.5;
    color: rgba(255, 255, 255, 0.8);
    font-style: italic;
  }
}

.services-preview {
  .service-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0;
    
    .service-icon {
      color: #22c55e;
    }
    
    .service-name {
      font-size: 0.875rem;
    }
  }
  
  .services-more {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.5);
    padding-left: 1rem;
  }
}

.empty-hint {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.4);
  font-style: italic;
}

// ============================================
// CURRENT STEP INDICATOR
// ============================================

.current-step-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: rgba(99, 102, 241, 0.1);
  border: 1px solid rgba(99, 102, 241, 0.3);
  border-radius: 0.5rem;
  font-size: 0.875rem;
  
  .indicator-icon {
    color: #6366f1;
  }
  
  .indicator-text {
    color: rgba(255, 255, 255, 0.8);
  }
}
</style>
