<template>
  <div 
    class="live-card"
    :class="{
      'is-open': isOpen,
      'is-active': isActive,
      'has-empty-fields': hasEmptyRequiredFields,
      'has-pending': pendingCount > 0,
      'is-complete': isComplete,
      'is-shimmering': isShimmering
    }"
  >
    <!-- Card Header -->
    <header class="card-header" @click="toggle">
      <div class="header-content">
        <span class="card-icon">{{ icon }}</span>
        <div class="card-info">
          <h4 class="card-title">{{ title }}</h4>
          <span class="card-meta">
            <template v-if="isComplete">
              <span class="status-complete">✓ Completo</span>
            </template>
            <template v-else-if="filledFieldsCount === 0">
              <span class="status-empty">Não preenchido</span>
            </template>
            <template v-else-if="pendingCount > 0">
              <span class="status-pending">{{ pendingCount }} pendência{{ pendingCount > 1 ? 's' : '' }}</span>
            </template>
            <template v-else>
              <span class="status-partial">Parcialmente preenchido</span>
            </template>
          </span>
        </div>
        
        <!-- Mini Preview (imagem) - visível quando card fechado -->
        <div v-if="hasImagePreview && !isOpen" class="mini-preview">
          <img 
            :src="imagePreviewUrl" 
            :alt="title"
            class="mini-preview-img"
            @error="handleImageError"
          />
          <span class="mini-preview-badge">📷</span>
        </div>
      </div>
      
      <div class="header-actions">
        <!-- Indicador de atividade atual -->
        <span v-if="isActive && isOpen" class="active-indicator">
          <span class="pulse-dot"></span>
          Editando
        </span>
        
        <!-- Chevron -->
        <span class="chevron-icon" :class="{ 'is-open': isOpen }">
          <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor">
            <path d="M2.5 4.5L6 8L9.5 4.5" stroke="currentColor" stroke-width="1.5" fill="none"/>
          </svg>
        </span>
      </div>
    </header>

    <!-- Card Body (Expandível) -->
    <Transition name="card-expand">
      <div v-if="isOpen" class="card-body">
        <!-- Preview de Imagem (quando card aberto) -->
        <div v-if="hasImagePreview" class="image-preview-section">
          <div class="image-preview-container">
            <img 
              :src="imagePreviewUrl" 
              :alt="title"
              class="main-preview-img"
              @error="handleImageError"
            />
            <div class="image-actions">
              <button class="btn-replace" @click.stop="triggerImageUpload">
                🔄 Trocar Imagem
              </button>
              <button class="btn-remove" @click.stop="removeImage">
                🗑️ Remover
              </button>
            </div>
          </div>
        </div>
        
        <!-- Upload Placeholder (quando não tem imagem) -->
        <div v-else-if="acceptsImage" class="image-upload-placeholder" @click.stop="triggerImageUpload">
          <span class="upload-icon">📷</span>
          <span class="upload-text">Clique para enviar {{ imageFieldLabel }}</span>
        </div>

        <!-- Campos do formulário -->
        <div class="card-fields">
          <slot name="fields">
            <!-- Campos dinâmicos baseados no tipo -->
            <LiveCardField
              v-for="field in visibleFields"
              :key="field.id"
              :field="field"
              :value="getFieldValue(field.id)"
              :is-shimmering="shimmeringFields.includes(field.id)"
              :is-recently-filled="recentlyFilledFields.includes(field.id)"
              :is-answered="answeredFields.includes(field.id)"
              :error="getFieldError(field.id)"
              @edit="handleFieldEdit"
              @focus="handleFieldFocus"
            />
          </slot>
        </div>
        
        <!-- Cores (especial para identity) -->
        <div v-if="type === 'identity'" class="colors-section">
          <span class="colors-label">Paleta de Cores:</span>
          <div class="color-swatches-large">
            <div 
              class="color-swatch-large editable"
              :style="{ backgroundColor: formData.primaryColor || '#6366F1' }"
              :class="{ 'is-shimmering': shimmeringFields.includes('primaryColor') }"
              @click.stop="openColorPicker('primaryColor')"
              title="Clique para trocar a cor principal"
            >
              <span class="color-label">Principal</span>
              <span class="color-edit-icon">✏️</span>
            </div>
            <div 
              class="color-swatch-large editable"
              :style="{ backgroundColor: formData.secondaryColor || '#A855F7' }"
              :class="{ 'is-shimmering': shimmeringFields.includes('secondaryColor') }"
              @click.stop="openColorPicker('secondaryColor')"
              title="Clique para trocar a cor secundária"
            >
              <span class="color-label">Secundária</span>
              <span class="color-edit-icon">✏️</span>
            </div>
          </div>
          
          <!-- Input de cor oculto -->
          <input
            ref="colorInput"
            type="color"
            :value="editingColorValue"
            style="position: absolute; visibility: hidden; width: 0; height: 0;"
            @input="handleColorChange"
            @change="handleColorChange"
          />
        </div>
        
        <!-- Feedback de edição inline -->
        <Transition name="feedback-fade">
          <div v-if="lastEditFeedback" class="edit-feedback">
            <span class="feedback-icon">✅</span>
            <span class="feedback-text">{{ lastEditFeedback }}</span>
          </div>
        </Transition>
      </div>
    </Transition>
    
    <!-- Input oculto para upload -->
    <input
      ref="fileInput"
      type="file"
      accept="image/*"
      style="display: none"
      @change="handleFileSelect"
    />
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue';
import LiveCardField from './LiveCardField.vue';

export default {
  name: 'LiveCard',
  
  components: {
    LiveCardField
  },
  
  props: {
    // Identidade do card
    id: {
      type: String,
      required: true
    },
    type: {
      type: String,
      required: true  // 'identity', 'contact', 'about', 'services', etc.
    },
    title: {
      type: String,
      required: true
    },
    icon: {
      type: String,
      default: '📋'
    },
    
    // Estado
    formData: {
      type: Object,
      required: true
    },
    currentStepId: {
      type: String,
      default: ''
    },
    
    // Campos do card
    fields: {
      type: Array,
      default: () => []
    },
    requiredFields: {
      type: Array,
      default: () => []
    },
    
    // Estados visuais
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
    },
    
    // Controle de abertura
    forceOpen: {
      type: Boolean,
      default: false
    },
    
    // Campo de imagem aceito (ex: 'logo', 'aboutImage')
    imageField: {
      type: String,
      default: null
    },
    imageFieldLabel: {
      type: String,
      default: 'uma imagem'
    },
    
    // Campos já respondidos pelo usuário (para indicador visual)
    answeredFields: {
      type: Array,
      default: () => []
    }
  },
  
  emits: [
    'toggle', 
    'field-edit', 
    'field-focus', 
    'image-upload', 
    'image-remove',
    'edit-feedback'
  ],
  
  setup(props, { emit }) {
    // ============================================
    // REFS
    // ============================================
    
    const internalOpen = ref(false);
    const lastEditFeedback = ref(null);
    const feedbackTimeout = ref(null);
    const fileInput = ref(null);
    const colorInput = ref(null);
    const editingColorField = ref(null);
    const editingColorValue = ref('#6366F1');
    const imageError = ref(false);
    
    // ============================================
    // COMPUTED
    // ============================================
    
    // Card está aberto?
    const isOpen = computed(() => {
      return props.forceOpen || internalOpen.value;
    });
    
    // Este card está ativo? (step atual)
    const isActive = computed(() => {
      return props.currentStepId === props.type;
    });
    
    // Campos visíveis
    const visibleFields = computed(() => {
      return props.fields.filter(f => !f.hidden);
    });
    
    // Helper para verificar se um valor está preenchido
    // IMPORTANTE: Booleanos (true ou false) SÃO considerados preenchidos
    const hasValue = (value) => {
      // Booleanos são sempre considerados preenchidos (true ou false)
      if (typeof value === 'boolean') return true;
      if (value === null || value === undefined) return false;
      if (typeof value === 'string') return value.trim().length > 0;
      if (Array.isArray(value)) return value.length > 0;
      if (typeof value === 'object') return Object.keys(value).length > 0;
      return Boolean(value);
    };
    
    // Campos obrigatórios vazios
    const emptyRequiredFields = computed(() => {
      return props.requiredFields.filter(fieldId => {
        return !hasValue(props.formData[fieldId]);
      });
    });
    
    // Conta quantos campos têm valor (para determinar se está vazio/parcial/completo)
    const filledFieldsCount = computed(() => {
      return props.fields.filter(f => hasValue(props.formData[f.id])).length;
    });
    
    const hasEmptyRequiredFields = computed(() => emptyRequiredFields.value.length > 0);
    
    const pendingCount = computed(() => {
      // Se tem campos obrigatórios, mostra os vazios
      if (props.requiredFields.length > 0) {
        return emptyRequiredFields.value.length;
      }
      // Se não tem obrigatórios, mostra campos totais não preenchidos
      return props.fields.filter(f => !hasValue(props.formData[f.id])).length;
    });
    
    // Card completo? (tem campos obrigatórios preenchidos E pelo menos algum conteúdo)
    const isComplete = computed(() => {
      // Se não tem campos, não está completo
      if (props.fields.length === 0) return false;
      
      // Verifica campos obrigatórios primeiro
      const requiredOk = props.requiredFields.every(fieldId => hasValue(props.formData[fieldId]));
      if (!requiredOk) return false;
      
      // Se não tem campos obrigatórios definidos, precisa ter pelo menos 1 campo preenchido
      if (props.requiredFields.length === 0) {
        return filledFieldsCount.value > 0;
      }
      
      return true;
    });
    
    // Shimmer ativo em algum campo?
    const isShimmering = computed(() => {
      return props.fields.some(f => props.shimmeringFields.includes(f.id));
    });
    
    // Preview de imagem
    const acceptsImage = computed(() => !!props.imageField);
    
    const hasImagePreview = computed(() => {
      if (!props.imageField) return false;
      const url = props.formData[props.imageField];
      return url && !imageError.value;
    });
    
    const imagePreviewUrl = computed(() => {
      if (!props.imageField) return null;
      return props.formData[props.imageField] || null;
    });
    
    // ============================================
    // WATCHERS
    // ============================================
    
    // Auto-abertura quando step atual muda para este card
    watch(() => props.currentStepId, (newStepId) => {
      if (newStepId === props.type) {
        // Auto-expandir com delay suave
        setTimeout(() => {
          internalOpen.value = true;
        }, 300);
      }
    }, { immediate: true });
    
    // Resetar erro de imagem quando URL mudar
    watch(() => props.formData[props.imageField], () => {
      imageError.value = false;
    });
    
    // ============================================
    // METHODS
    // ============================================
    
    function toggle() {
      internalOpen.value = !internalOpen.value;
      emit('toggle', { id: props.id, isOpen: internalOpen.value });
    }
    
    function getFieldValue(fieldId) {
      return props.formData[fieldId] || '';
    }
    
    function getFieldError(fieldId) {
      return props.validationErrors[fieldId] || null;
    }
    
    function handleFieldEdit(fieldId, value) {
      emit('field-edit', fieldId, value);
      showEditFeedback(fieldId);
    }
    
    function handleFieldFocus(fieldId) {
      emit('field-focus', fieldId);
    }
    
    function showEditFeedback(fieldId) {
      // Limpar timeout anterior
      if (feedbackTimeout.value) {
        clearTimeout(feedbackTimeout.value);
      }
      
      // Encontrar label do campo
      const field = props.fields.find(f => f.id === fieldId);
      const label = field?.label || fieldId;
      
      lastEditFeedback.value = `Alteração salva em "${label}"!`;
      emit('edit-feedback', { field: fieldId, label });
      
      // Limpar após 3 segundos
      feedbackTimeout.value = setTimeout(() => {
        lastEditFeedback.value = null;
      }, 3000);
    }
    
    function triggerImageUpload() {
      fileInput.value?.click();
    }
    
    async function handleFileSelect(event) {
      const file = event.target.files?.[0];
      if (!file) return;
      
      // Validar tipo
      if (!file.type.startsWith('image/')) {
        alert('Por favor, selecione uma imagem válida.');
        return;
      }
      
      // Validar tamanho (max 5MB)
      if (file.size > 5 * 1024 * 1024) {
        alert('A imagem deve ter no máximo 5MB.');
        return;
      }
      
      emit('image-upload', {
        field: props.imageField,
        file,
        cardId: props.id
      });
      
      // Limpar input
      event.target.value = '';
    }
    
    function removeImage() {
      emit('image-remove', {
        field: props.imageField,
        cardId: props.id
      });
    }
    
    function handleImageError() {
      imageError.value = true;
    }
    
    function openColorPicker(colorField) {
      editingColorField.value = colorField;
      editingColorValue.value = props.formData[colorField] || '#6366F1';
      // Delay para garantir que o valor foi atualizado
      setTimeout(() => {
        colorInput.value?.click();
      }, 50);
    }
    
    function handleColorChange(event) {
      const newColor = event.target.value;
      if (editingColorField.value) {
        emit('field-edit', editingColorField.value, newColor);
        showEditFeedback(editingColorField.value);
      }
    }
    
    return {
      // Refs
      fileInput,
      colorInput,
      editingColorValue,
      lastEditFeedback,
      
      // Computed
      isOpen,
      isActive,
      visibleFields,
      hasEmptyRequiredFields,
      pendingCount,
      filledFieldsCount,
      isComplete,
      isShimmering,
      acceptsImage,
      hasImagePreview,
      imagePreviewUrl,
      
      // Methods
      toggle,
      getFieldValue,
      getFieldError,
      handleFieldEdit,
      handleFieldFocus,
      triggerImageUpload,
      handleFileSelect,
      removeImage,
      handleImageError,
      openColorPicker,
      handleColorChange
    };
  }
};
</script>

<style lang="scss" scoped>
// ============================================
// LIVE CARD - "Cards Vivos"
// ============================================

.live-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 1rem;
  overflow: hidden;
  transition: all 0.3s ease;
  
  // Estados
  &.is-active {
    border-color: rgba(99, 102, 241, 0.5);
    box-shadow: 0 0 20px rgba(99, 102, 241, 0.15);
    
    .card-header {
      background: rgba(99, 102, 241, 0.08);
    }
  }
  
  &.is-open {
    border-color: rgba(255, 255, 255, 0.15);
  }
  
  &.has-pending {
    border-color: rgba(245, 158, 11, 0.4);
    
    .card-header {
      background: rgba(245, 158, 11, 0.05);
    }
  }
  
  &.is-complete {
    border-color: rgba(34, 197, 94, 0.3);
    
    .status-complete {
      color: #22c55e;
    }
  }
  
  &.is-shimmering {
    .card-header {
      position: relative;
      
      &::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(
          90deg,
          transparent,
          rgba(99, 102, 241, 0.1),
          transparent
        );
        animation: shimmer 2s infinite;
      }
    }
  }
}

// ============================================
// HEADER
// ============================================

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  cursor: pointer;
  transition: background 0.2s;
  
  &:hover {
    background: rgba(255, 255, 255, 0.05);
  }
}

.header-content {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  flex: 1;
}

.card-icon {
  font-size: 1.5rem;
  flex-shrink: 0;
}

.card-info {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.card-title {
  font-size: 1rem;
  font-weight: 600;
  margin: 0;
  color: #fff;
}

.card-meta {
  font-size: 0.75rem;
}

.status-complete {
  color: #22c55e;
}

.status-pending {
  color: #f59e0b;
}

.status-partial {
  color: rgba(255, 255, 255, 0.5);
}

.status-empty {
  color: rgba(255, 255, 255, 0.4);
  font-style: italic;
}

// Mini Preview (imagem thumbnail no header)
.mini-preview {
  position: relative;
  width: 36px;
  height: 36px;
  border-radius: 0.5rem;
  overflow: hidden;
  flex-shrink: 0;
  margin-left: auto;
  margin-right: 1rem;
  border: 2px solid rgba(99, 102, 241, 0.4);
  
  .mini-preview-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  
  .mini-preview-badge {
    position: absolute;
    bottom: -2px;
    right: -2px;
    font-size: 0.6rem;
    background: rgba(0, 0, 0, 0.7);
    border-radius: 50%;
    padding: 2px;
  }
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.active-indicator {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.7rem;
  color: #a5b4fc;
  padding: 0.25rem 0.5rem;
  background: rgba(99, 102, 241, 0.15);
  border-radius: 1rem;
}

.pulse-dot {
  width: 6px;
  height: 6px;
  background: #6366f1;
  border-radius: 50%;
  animation: pulse-glow 1.5s ease-in-out infinite;
}

.chevron-icon {
  color: rgba(255, 255, 255, 0.4);
  transition: transform 0.3s ease;
  
  &.is-open {
    transform: rotate(180deg);
  }
}

// ============================================
// BODY
// ============================================

.card-body {
  padding: 1.25rem;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}

// Seção de Preview de Imagem
.image-preview-section {
  margin-bottom: 1.25rem;
}

.image-preview-container {
  position: relative;
  display: inline-block;
  
  .main-preview-img {
    max-width: 120px;
    max-height: 120px;
    border-radius: 0.75rem;
    object-fit: contain;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
  }
  
  .image-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.75rem;
  }
}

.btn-replace,
.btn-remove {
  padding: 0.375rem 0.75rem;
  font-size: 0.75rem;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.btn-replace {
  background: rgba(99, 102, 241, 0.2);
  color: #a5b4fc;
  
  &:hover {
    background: rgba(99, 102, 241, 0.3);
  }
}

.btn-remove {
  background: rgba(239, 68, 68, 0.15);
  color: #fca5a5;
  
  &:hover {
    background: rgba(239, 68, 68, 0.25);
  }
}

// Upload Placeholder
.image-upload-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.02);
  border: 2px dashed rgba(255, 255, 255, 0.1);
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
  margin-bottom: 1.25rem;
  
  &:hover {
    background: rgba(99, 102, 241, 0.08);
    border-color: rgba(99, 102, 241, 0.3);
  }
  
  .upload-icon {
    font-size: 1.5rem;
    opacity: 0.5;
  }
  
  .upload-text {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.5);
  }
}

// Campos do formulário
.card-fields {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

// Seção de Cores
.colors-section {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.colors-label {
  display: block;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.5);
  margin-bottom: 0.5rem;
}

.color-swatches-large {
  display: flex;
  gap: 1rem;
}

.color-swatch-large {
  position: relative;
  width: 60px;
  height: 60px;
  border-radius: 0.75rem;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  padding-bottom: 0.25rem;
  border: 2px solid rgba(255, 255, 255, 0.1);
  transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
  
  &.editable {
    cursor: pointer;
    
    .color-edit-icon {
      position: absolute;
      top: 0.25rem;
      right: 0.25rem;
      font-size: 0.65rem;
      opacity: 0;
      transition: opacity 0.2s;
    }
    
    &:hover {
      border-color: rgba(255, 255, 255, 0.4);
      
      .color-edit-icon {
        opacity: 1;
      }
    }
  }
  
  &:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  }
  
  &.is-shimmering {
    animation: color-shimmer 1.5s ease-in-out infinite;
  }
  
  .color-label {
    font-size: 0.6rem;
    color: rgba(255, 255, 255, 0.9);
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
    background: rgba(0, 0, 0, 0.3);
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
  }
}

// Feedback de Edição
.edit-feedback {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 1rem;
  padding: 0.625rem 1rem;
  background: rgba(34, 197, 94, 0.1);
  border: 1px solid rgba(34, 197, 94, 0.2);
  border-radius: 0.5rem;
  font-size: 0.8rem;
  color: #86efac;
}

// ============================================
// TRANSITIONS
// ============================================

.card-expand-enter-active,
.card-expand-leave-active {
  transition: all 0.3s ease;
  overflow: hidden;
}

.card-expand-enter-from,
.card-expand-leave-to {
  opacity: 0;
  max-height: 0;
  padding-top: 0;
  padding-bottom: 0;
}

.card-expand-enter-to,
.card-expand-leave-from {
  max-height: 600px;
}

.feedback-fade-enter-active,
.feedback-fade-leave-active {
  transition: all 0.3s ease;
}

.feedback-fade-enter-from,
.feedback-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

// ============================================
// ANIMATIONS
// ============================================

@keyframes shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

@keyframes pulse-glow {
  0%, 100% { 
    opacity: 1;
    box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.4);
  }
  50% { 
    opacity: 0.7;
    box-shadow: 0 0 0 6px rgba(99, 102, 241, 0);
  }
}

@keyframes color-shimmer {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.6; }
}

// ============================================
// RESPONSIVE
// ============================================

@media (max-width: 768px) {
  .card-header {
    padding: 0.875rem 1rem;
  }
  
  .card-body {
    padding: 1rem;
  }
  
  .mini-preview {
    width: 32px;
    height: 32px;
  }
}
</style>
