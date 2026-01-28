<template>
  <div 
    class="review-card"
    :class="{
      'is-complete': isComplete,
      'has-warnings': hasWarnings,
      'has-errors': hasErrors
    }"
  >
    <!-- Card Header -->
    <div class="card-header" @click="toggleExpanded">
      <div class="header-left">
        <span class="card-icon">{{ step.icon }}</span>
        <div class="card-info">
          <h4 class="card-title">{{ step.name }}</h4>
          <span class="card-status">
            <template v-if="isComplete">✓ Completo</template>
            <template v-else-if="hasErrors">⚠️ {{ errorCount }} pendência(s)</template>
            <template v-else>Parcialmente preenchido</template>
          </span>
        </div>
      </div>
      
      <div class="header-right">
        <span class="expand-icon" :class="{ 'is-expanded': isExpanded }">▼</span>
      </div>
    </div>
    
    <!-- Card Body -->
    <Transition name="expand">
      <div v-if="isExpanded" class="card-body">
        <!-- Preview das informações -->
        <div class="card-preview">
          <template v-if="step.id === 'identity'">
            <div class="preview-row">
              <span class="preview-label">Nome:</span>
              <span class="preview-value" :class="{ 'is-empty': !formData.companyName }">
                {{ formData.companyName || 'Não definido' }}
              </span>
            </div>
            <div class="preview-row">
              <span class="preview-label">Ramo:</span>
              <span class="preview-value" :class="{ 'is-empty': !formData.businessType }">
                {{ formatBusinessType(formData.businessType) || 'Não definido' }}
              </span>
            </div>
            <div class="preview-row">
              <span class="preview-label">Frase:</span>
              <span class="preview-value" :class="{ 'is-empty': !formData.tagline }">
                {{ formData.tagline || 'Não definido' }}
              </span>
            </div>
            <div class="preview-row colors">
              <span class="preview-label">Cores:</span>
              <div class="color-swatches">
                <span 
                  class="color-swatch" 
                  :style="{ backgroundColor: formData.primaryColor }"
                ></span>
                <span 
                  class="color-swatch" 
                  :style="{ backgroundColor: formData.secondaryColor }"
                ></span>
              </div>
            </div>
          </template>
          
          <template v-else-if="step.id === 'contact'">
            <div class="preview-row">
              <span class="preview-label">WhatsApp:</span>
              <span class="preview-value" :class="{ 'is-empty': !formData.whatsapp }">
                {{ formData.whatsapp || 'Não definido' }}
              </span>
            </div>
            <div v-if="formData.socialNetworks?.length" class="preview-row">
              <span class="preview-label">Redes:</span>
              <span class="preview-value">{{ formData.socialNetworks.length }} configurada(s)</span>
            </div>
            <div v-if="formData.hasPhysicalLocation" class="preview-row">
              <span class="preview-label">Local:</span>
              <span class="preview-value">{{ formData.addressCity }}, {{ formData.addressState }}</span>
            </div>
          </template>
          
          <template v-else-if="step.id === 'about'">
            <div v-if="formData.foundingYear" class="preview-row">
              <span class="preview-label">Fundação:</span>
              <span class="preview-value">{{ formData.foundingYear }}</span>
            </div>
            <div class="preview-row">
              <span class="preview-label">História:</span>
              <span class="preview-value" :class="{ 'is-empty': !formData.companyBio }">
                {{ truncate(formData.companyBio, 80) || 'Não definido' }}
              </span>
            </div>
            <div v-if="formData.companyHighlights?.length" class="preview-row">
              <span class="preview-label">Diferenciais:</span>
              <span class="preview-value">{{ formData.companyHighlights.length }} item(s)</span>
            </div>
          </template>
          
          <template v-else-if="step.id === 'services'">
            <div class="preview-row">
              <span class="preview-label">Serviços:</span>
              <span class="preview-value" :class="{ 'is-empty': !formData.services?.length }">
                {{ formData.services?.length || 0 }} cadastrado(s)
              </span>
            </div>
            <div v-for="service in visibleServices" :key="service.name" class="service-item">
              • {{ service.name }}
            </div>
            <span v-if="formData.services?.length > 3" class="more-items">
              +{{ formData.services.length - 3 }} mais
            </span>
          </template>
          
          <template v-else-if="step.id === 'faq'">
            <div class="preview-row">
              <span class="preview-label">Perguntas:</span>
              <span class="preview-value" :class="{ 'is-empty': !formData.faqItems?.length }">
                {{ formData.faqItems?.length || 0 }} cadastrada(s)
              </span>
            </div>
          </template>
          
          <template v-else-if="step.id === 'finalization'">
            <div class="preview-row">
              <span class="preview-label">Urgência:</span>
              <span class="preview-value">{{ formatUrgency(formData.urgency) }}</span>
            </div>
            <div v-if="formData.additionalNotes" class="preview-row">
              <span class="preview-label">Notas:</span>
              <span class="preview-value">{{ truncate(formData.additionalNotes, 50) }}</span>
            </div>
          </template>
        </div>
        
        <!-- Edit Button -->
        <button class="btn-edit" @click="$emit('edit', step.id)">
          ✏️ Editar
        </button>
      </div>
    </Transition>
  </div>
</template>

<script>
import { ref, computed } from 'vue';

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

const urgencyLabels = {
  urgent: '🏃 Urgente (até 3 dias)',
  normal: '📅 Normal (até 7 dias)',
  relaxed: '🧘 Tranquilo'
};

export default {
  name: 'ReviewCard',
  
  props: {
    step: {
      type: Object,
      required: true
    },
    formData: {
      type: Object,
      required: true
    },
    validationErrors: {
      type: Object,
      default: () => ({})
    }
  },
  
  emits: ['edit'],
  
  setup(props) {
    const isExpanded = ref(true);
    
    const isComplete = computed(() => {
      // Verificar campos obrigatórios do step
      return props.step.requiredFields.every(field => {
        const value = props.formData[field];
        return value && (Array.isArray(value) ? value.length > 0 : true);
      });
    });
    
    const hasWarnings = computed(() => {
      // Campos opcionais vazios
      const optionalEmpty = props.step.optionalFields.filter(field => {
        const value = props.formData[field];
        return !value || (Array.isArray(value) && value.length === 0);
      });
      return optionalEmpty.length > 0 && !hasErrors.value;
    });
    
    const hasErrors = computed(() => {
      // Campos obrigatórios vazios ou com erro de validação
      return props.step.requiredFields.some(field => {
        return props.validationErrors[field] || !props.formData[field];
      });
    });
    
    const errorCount = computed(() => {
      return props.step.requiredFields.filter(field => {
        return props.validationErrors[field] || !props.formData[field];
      }).length;
    });
    
    const visibleServices = computed(() => {
      return (props.formData.services || []).slice(0, 3);
    });
    
    function toggleExpanded() {
      isExpanded.value = !isExpanded.value;
    }
    
    function formatBusinessType(type) {
      return businessTypeLabels[type] || type;
    }
    
    function formatUrgency(urgency) {
      return urgencyLabels[urgency] || urgency || 'Normal';
    }
    
    function truncate(text, length) {
      if (!text) return '';
      if (text.length <= length) return text;
      return text.substring(0, length) + '...';
    }
    
    return {
      isExpanded,
      isComplete,
      hasWarnings,
      hasErrors,
      errorCount,
      visibleServices,
      toggleExpanded,
      formatBusinessType,
      formatUrgency,
      truncate
    };
  }
};
</script>

<style lang="scss" scoped>
.review-card {
  background: rgba(255, 255, 255, 0.03);
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 1rem;
  overflow: hidden;
  transition: all 0.3s;
  
  &.is-complete {
    border-color: rgba(34, 197, 94, 0.4);
    
    .card-header {
      background: rgba(34, 197, 94, 0.05);
    }
  }
  
  &.has-warnings {
    border-color: rgba(245, 158, 11, 0.4);
  }
  
  &.has-errors {
    border-color: rgba(239, 68, 68, 0.4);
    
    .card-header {
      background: rgba(239, 68, 68, 0.05);
    }
  }
  
  &:hover {
    border-color: rgba(99, 102, 241, 0.5);
  }
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  cursor: pointer;
  transition: background 0.2s;
  
  &:hover {
    background: rgba(255, 255, 255, 0.03);
  }
  
  .header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }
  
  .card-icon {
    font-size: 1.5rem;
  }
  
  .card-info {
    .card-title {
      font-size: 1rem;
      font-weight: 600;
      margin: 0 0 0.25rem;
    }
    
    .card-status {
      font-size: 0.75rem;
      color: rgba(255, 255, 255, 0.5);
    }
  }
  
  .expand-icon {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.4);
    transition: transform 0.3s;
    
    &.is-expanded {
      transform: rotate(180deg);
    }
  }
}

.card-body {
  padding: 1rem 1.25rem 1.25rem;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.card-preview {
  margin-bottom: 1rem;
}

.preview-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  
  &:last-child {
    border-bottom: none;
  }
  
  &.colors {
    .color-swatches {
      display: flex;
      gap: 0.5rem;
    }
    
    .color-swatch {
      width: 24px;
      height: 24px;
      border-radius: 6px;
      border: 2px solid rgba(255, 255, 255, 0.2);
    }
  }
  
  .preview-label {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.5);
  }
  
  .preview-value {
    font-size: 0.875rem;
    font-weight: 500;
    text-align: right;
    max-width: 60%;
    
    &.is-empty {
      color: rgba(255, 255, 255, 0.3);
      font-style: italic;
      font-weight: 400;
    }
  }
}

.service-item {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.7);
  padding: 0.25rem 0;
}

.more-items {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.4);
}

.btn-edit {
  width: 100%;
  padding: 0.75rem;
  background: rgba(99, 102, 241, 0.15);
  border: 1px solid rgba(99, 102, 241, 0.3);
  border-radius: 0.5rem;
  color: #a5b4fc;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  
  &:hover {
    background: rgba(99, 102, 241, 0.25);
    border-color: rgba(99, 102, 241, 0.5);
  }
}

// Expand transition
.expand-enter-active,
.expand-leave-active {
  transition: all 0.3s ease;
  overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
  opacity: 0;
  max-height: 0;
  padding-top: 0;
  padding-bottom: 0;
}

.expand-enter-to,
.expand-leave-from {
  max-height: 500px;
}
</style>
