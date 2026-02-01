<template>
  <div 
    class="field-card"
    :class="{
      'is-filled': isFilled,
      'is-skipped': isSkipped,
      'is-required': field.required,
      'is-shimmering': isShimmering,
      'is-recently-filled': isRecentlyFilled,
      'is-editing': isEditing
    }"
    @click="handleClick"
  >
    <!-- Status Indicator -->
    <div class="field-status-indicator">
      <span v-if="isFilled" class="status-icon filled">✓</span>
      <span v-else-if="isSkipped" class="status-icon skipped">—</span>
      <span v-else class="status-icon empty">○</span>
    </div>
    
    <!-- Field Content -->
    <div class="field-content">
      <div class="field-header">
        <span class="field-label">
          {{ field.label }}
          <span v-if="field.required" class="required-badge">*</span>
        </span>
        <span v-if="field.xp" class="field-xp">+{{ field.xp }} XP</span>
      </div>
      
      <!-- Display Value or Edit Mode -->
      <div v-if="!isEditing" class="field-value-display">
        <!-- Image Preview -->
        <div v-if="field.type === 'image' && value" class="image-preview">
          <img :src="value" :alt="field.label" />
          <button class="btn-remove-image" @click.stop="clearValue">✕</button>
        </div>
        
        <!-- Color Preview -->
        <div v-else-if="field.type === 'color' && value" class="color-preview">
          <div class="color-swatch" :style="{ background: value }"></div>
          <span class="color-code">{{ value }}</span>
        </div>
        
        <!-- Boolean Display (when filled) -->
        <div v-else-if="field.type === 'boolean' && isFilled" class="boolean-display">
          <span class="boolean-badge" :class="{ 'is-true': value, 'is-false': !value }">
            {{ value ? '✓ Sim' : '✗ Não' }}
          </span>
        </div>
        
        <!-- Text Value -->
        <span v-else-if="value" class="field-value">{{ displayValue }}</span>
        
        <!-- Empty State -->
        <span v-else class="field-placeholder">
          {{ isSkipped ? 'Não informado' : 'Clique para preencher' }}
        </span>
      </div>
      
      <!-- Edit Mode -->
      <div v-else class="field-edit-mode" @click.stop>
        <!-- Text Input -->
        <input
          v-if="isTextInput"
          ref="editInput"
          v-model="editValue"
          :type="inputType"
          :placeholder="field.label"
          class="edit-input"
          @keydown.enter="saveEdit"
          @keydown.esc="cancelEdit"
          @blur="saveEdit"
        />
        
        <!-- Textarea -->
        <textarea
          v-else-if="field.type === 'textarea'"
          ref="editInput"
          v-model="editValue"
          :placeholder="field.label"
          class="edit-textarea"
          rows="3"
          @keydown.esc="cancelEdit"
          @blur="saveEdit"
        ></textarea>
        
        <!-- Select -->
        <select
          v-else-if="field.type === 'select'"
          ref="editInput"
          v-model="editValue"
          class="edit-select"
          @change="saveEdit"
          @blur="saveEdit"
        >
          <option value="">Selecione...</option>
          <option 
            v-for="option in field.options" 
            :key="option" 
            :value="option"
          >
            {{ formatOption(option) }}
          </option>
        </select>
        
        <!-- Color Picker -->
        <div v-else-if="field.type === 'color'" class="color-picker-wrapper">
          <input
            ref="editInput"
            type="color"
            v-model="editValue"
            class="color-input"
          />
          <input
            v-model="editValue"
            type="text"
            placeholder="#000000"
            class="color-text-input"
            @keydown.enter="saveEdit"
          />
          <button class="btn-save-color" @click="saveEdit">✓</button>
        </div>
        
        <!-- Image Upload -->
        <div v-else-if="field.type === 'image'" class="image-upload-wrapper">
          <input
            type="file"
            ref="fileInput"
            accept="image/*"
            class="hidden"
            @change="handleFileUpload"
          />
          <button class="btn-upload" @click="$refs.fileInput.click()">
            📷 Escolher Imagem
          </button>
        </div>
        
        <!-- Boolean Switch -->
        <div v-else-if="field.type === 'boolean'" class="boolean-switch-wrapper">
          <label class="switch-container">
            <input
              type="checkbox"
              v-model="editValue"
              class="switch-input"
              @change="saveEdit"
            />
            <span class="switch-slider"></span>
          </label>
          <span class="switch-label">{{ editValue ? 'Sim' : 'Não' }}</span>
        </div>
      </div>
    </div>
    
    <!-- Edit Button -->
    <button 
      v-if="isFilled && !isEditing" 
      class="btn-edit"
      @click.stop="startEdit"
    >
      ✏️
    </button>
    
    <!-- Shimmer Effect -->
    <div v-if="isShimmering" class="shimmer-overlay"></div>
    
    <!-- Recently Filled Animation -->
    <div v-if="isRecentlyFilled" class="filled-animation"></div>
  </div>
</template>

<script>
import { ref, computed, nextTick } from 'vue';
import { FIELD_STATUS } from '../types/onboarding.types.js';

export default {
  name: 'FieldCard',
  
  props: {
    field: {
      type: Object,
      required: true
    },
    value: {
      type: [String, Number, Boolean, Array, Object],
      default: null
    },
    status: {
      type: String,
      default: FIELD_STATUS.EMPTY
    },
    isShimmering: {
      type: Boolean,
      default: false
    },
    isRecentlyFilled: {
      type: Boolean,
      default: false
    }
  },
  
  emits: ['click', 'edit', 'image-upload'],
  
  setup(props, { emit }) {
    const isEditing = ref(false);
    const editValue = ref('');
    const editInput = ref(null);
    const fileInput = ref(null);
    
    const isFilled = computed(() => {
      return props.status === FIELD_STATUS.ANSWERED || props.status === FIELD_STATUS.CONFIRMED;
    });
    
    const isSkipped = computed(() => {
      return props.status === FIELD_STATUS.SKIPPED;
    });
    
    const isTextInput = computed(() => {
      return ['text', 'phone', 'email', 'cep', 'year', 'social'].includes(props.field.type);
    });
    
    const inputType = computed(() => {
      const typeMap = {
        'email': 'email',
        'phone': 'tel',
        'year': 'number'
      };
      return typeMap[props.field.type] || 'text';
    });
    
    const displayValue = computed(() => {
      const val = props.value;
      
      if (val === null || val === undefined) return '';
      
      if (Array.isArray(val)) {
        if (val.length === 0) return '';
        if (props.field.type === 'services') {
          return val.map(s => s.name || s).join(', ');
        }
        return val.join(', ');
      }
      
      if (props.field.type === 'boolean') {
        return val ? 'Sim' : 'Não';
      }
      
      const str = String(val);
      return str.length > 100 ? str.substring(0, 97) + '...' : str;
    });
    
    function handleClick() {
      if (!isEditing.value) {
        emit('click');
        // Boolean fields should always show the switch for editing
        if (props.field.type === 'boolean') {
          startEdit();
        } else if (!isFilled.value) {
          startEdit();
        }
      }
    }
    
    function startEdit() {
      // Handle boolean values properly (false is a valid value)
      if (props.field.type === 'boolean') {
        editValue.value = props.value === true;
      } else {
        editValue.value = props.value || '';
      }
      isEditing.value = true;
      
      nextTick(() => {
        if (editInput.value) {
          editInput.value.focus();
        }
      });
    }
    
    function saveEdit() {
      if (editValue.value !== props.value) {
        emit('edit', editValue.value);
      }
      // Keep boolean fields in edit mode to show switch
      if (props.field.type !== 'boolean') {
        isEditing.value = false;
      }
    }
    
    function cancelEdit() {
      isEditing.value = false;
      // Handle boolean values properly
      if (props.field.type === 'boolean') {
        editValue.value = props.value === true;
      } else {
        editValue.value = props.value || '';
      }
    }
    
    function clearValue() {
      emit('edit', null);
    }
    
    function formatOption(option) {
      // Check if field has custom optionLabels
      if (props.field.optionLabels && props.field.optionLabels[option]) {
        return props.field.optionLabels[option];
      }
      
      // Capitalize and format option text
      const labels = {
        'alimentacao': 'Alimentação',
        'saude': 'Saúde',
        'beleza': 'Beleza',
        'educacao': 'Educação',
        'tecnologia': 'Tecnologia',
        'construcao': 'Construção',
        'moda': 'Moda',
        'servicos': 'Serviços',
        'juridico': 'Jurídico',
        'eventos': 'Eventos',
        'turismo': 'Turismo',
        'imoveis': 'Imóveis',
        'automotivo': 'Automotivo',
        'pets': 'Pets',
        'comercio': 'Comércio',
        'industria': 'Indústria',
        'outro': 'Outro',
        'profissional': 'Profissional',
        'amigavel': 'Amigável',
        'descontraido': 'Descontraído',
        'luxuoso': 'Luxuoso',
        'tecnico': 'Técnico',
        'inspirador': 'Inspirador',
        'urgent': 'Urgente',
        'normal': 'Normal',
        'relaxed': 'Tranquilo',
        // Site Objectives
        'essential': '🏢 Essencial',
        'authority': '🚀 Autoridade',
        'enterprise': '💎 Ecossistema Digital'
      };
      return labels[option] || option;
    }
    
    function handleFileUpload(event) {
      const file = event.target.files?.[0];
      if (!file) return;
      
      // Validar tamanho (max 5MB)
      if (file.size > 5 * 1024 * 1024) {
        alert('Imagem muito grande. Máximo 5MB.');
        return;
      }
      
      // Converter para base64 temporariamente (ou fazer upload)
      const reader = new FileReader();
      reader.onload = (e) => {
        emit('image-upload', { 
          file, 
          base64: e.target.result,
          url: e.target.result // Temporário, substituir por URL do upload
        });
        isEditing.value = false;
      };
      reader.readAsDataURL(file);
    }
    
    return {
      isEditing,
      editValue,
      editInput,
      fileInput,
      isFilled,
      isSkipped,
      isTextInput,
      inputType,
      displayValue,
      handleClick,
      startEdit,
      saveEdit,
      cancelEdit,
      clearValue,
      formatOption,
      handleFileUpload
    };
  }
};
</script>

<style lang="scss" scoped>
$primary: #6C5CE7;
$primary-light: #A29BFE;
$secondary: #00CEC9;
$background-card: #1a1a2e;
$text-primary: #ffffff;
$text-secondary: rgba(255, 255, 255, 0.7);
$border-color: rgba(255, 255, 255, 0.1);
$success: #00B894;
$warning: #FDCB6E;
$error: #E17055;

.field-card {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid $border-color;
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
  overflow: hidden;
  
  &:hover {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba($primary, 0.3);
  }
  
  &.is-filled {
    border-color: rgba($success, 0.3);
    
    .field-status-indicator .status-icon {
      color: $success;
    }
  }
  
  &.is-skipped {
    opacity: 0.6;
  }
  
  &.is-required:not(.is-filled) {
    border-left: 3px solid $warning;
  }
  
  &.is-editing {
    background: rgba($primary, 0.1);
    border-color: $primary;
  }
  
  &.is-shimmering {
    .shimmer-overlay {
      display: block;
    }
  }
  
  &.is-recently-filled {
    .filled-animation {
      display: block;
    }
  }
}

.field-status-indicator {
  flex-shrink: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  
  .status-icon {
    font-size: 0.875rem;
    
    &.filled {
      color: $success;
      font-weight: 700;
    }
    
    &.skipped {
      color: $text-secondary;
    }
    
    &.empty {
      color: $text-secondary;
    }
  }
}

.field-content {
  flex: 1;
  min-width: 0;
}

.field-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.25rem;
}

.field-label {
  font-size: 0.75rem;
  color: $text-secondary;
  
  .required-badge {
    color: $warning;
    margin-left: 0.125rem;
  }
}

.field-xp {
  font-size: 0.625rem;
  color: $warning;
  background: rgba($warning, 0.15);
  padding: 0.125rem 0.375rem;
  border-radius: 0.25rem;
}

.field-value-display {
  min-height: 1.5rem;
}

.field-value {
  font-size: 0.9375rem;
  color: $text-primary;
  line-height: 1.4;
  word-break: break-word;
}

.field-placeholder {
  font-size: 0.875rem;
  color: rgba($text-secondary, 0.5);
  font-style: italic;
}

// Image Preview
.image-preview {
  position: relative;
  display: inline-block;
  
  img {
    max-width: 100%;
    max-height: 80px;
    border-radius: 0.5rem;
    object-fit: cover;
  }
  
  .btn-remove-image {
    position: absolute;
    top: -6px;
    right: -6px;
    width: 20px;
    height: 20px;
    background: $error;
    border: none;
    border-radius: 50%;
    color: white;
    font-size: 0.75rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
  }
}

// Color Preview
.color-preview {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  
  .color-swatch {
    width: 24px;
    height: 24px;
    border-radius: 0.375rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
  }
  
  .color-code {
    font-size: 0.8125rem;
    font-family: monospace;
    color: $text-primary;
  }
}

// Edit Mode
.field-edit-mode {
  margin-top: 0.25rem;
}

.edit-input,
.edit-textarea,
.edit-select {
  width: 100%;
  padding: 0.5rem 0.75rem;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba($primary, 0.5);
  border-radius: 0.5rem;
  color: $text-primary;
  font-size: 0.875rem;
  outline: none;
  
  &:focus {
    border-color: $primary;
  }
  
  &::placeholder {
    color: $text-secondary;
  }
}

.edit-textarea {
  resize: vertical;
  min-height: 60px;
}

.edit-select {
  cursor: pointer;
  
  option {
    background: $background-card;
    color: $text-primary;
  }
}

.color-picker-wrapper {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  
  .color-input {
    width: 40px;
    height: 40px;
    padding: 0;
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
  }
  
  .color-text-input {
    flex: 1;
    padding: 0.5rem;
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid rgba($primary, 0.5);
    border-radius: 0.5rem;
    color: $text-primary;
    font-family: monospace;
    font-size: 0.875rem;
  }
  
  .btn-save-color {
    width: 36px;
    height: 36px;
    background: $success;
    border: none;
    border-radius: 0.5rem;
    color: white;
    cursor: pointer;
    
    &:hover {
      filter: brightness(1.1);
    }
  }
}

.image-upload-wrapper {
  .hidden {
    display: none;
  }
  
  .btn-upload {
    padding: 0.75rem 1rem;
    background: rgba($primary, 0.2);
    border: 1px dashed rgba($primary, 0.5);
    border-radius: 0.5rem;
    color: $text-primary;
    cursor: pointer;
    width: 100%;
    
    &:hover {
      background: rgba($primary, 0.3);
    }
  }
}

// Boolean Switch
.boolean-switch-wrapper {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.25rem 0;
  
  .switch-container {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 26px;
    cursor: pointer;
  }
  
  .switch-input {
    opacity: 0;
    width: 0;
    height: 0;
    
    &:checked + .switch-slider {
      background: linear-gradient(135deg, $success 0%, darken($success, 10%) 100%);
    }
    
    &:checked + .switch-slider::before {
      transform: translateX(22px);
    }
    
    &:focus + .switch-slider {
      box-shadow: 0 0 0 3px rgba($primary, 0.3);
    }
  }
  
  .switch-slider {
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 26px;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.2);
    
    &::before {
      content: '';
      position: absolute;
      width: 20px;
      height: 20px;
      left: 2px;
      bottom: 2px;
      background: white;
      border-radius: 50%;
      transition: transform 0.3s ease;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }
  }
  
  .switch-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: $text-primary;
    user-select: none;
  }
}

// Boolean Display (when filled but not editing)
.boolean-display {
  .boolean-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.8125rem;
    font-weight: 500;
    
    &.is-true {
      background: rgba($success, 0.2);
      color: $success;
    }
    
    &.is-false {
      background: rgba(255, 255, 255, 0.1);
      color: $text-secondary;
    }
  }
}

.btn-edit {
  flex-shrink: 0;
  width: 28px;
  height: 28px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid $border-color;
  border-radius: 0.375rem;
  color: $text-secondary;
  cursor: pointer;
  font-size: 0.75rem;
  opacity: 0;
  transition: all 0.2s;
  
  .field-card:hover & {
    opacity: 1;
  }
  
  &:hover {
    background: rgba($primary, 0.2);
    border-color: rgba($primary, 0.4);
  }
}

// Shimmer Effect
.shimmer-overlay {
  display: none;
  position: absolute;
  inset: 0;
  background: linear-gradient(
    90deg,
    transparent,
    rgba($primary, 0.3),
    transparent
  );
  animation: shimmer 1.5s infinite;
  pointer-events: none;
}

@keyframes shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

// Filled Animation
.filled-animation {
  display: none;
  position: absolute;
  inset: 0;
  border: 2px solid $success;
  border-radius: 0.75rem;
  animation: filledPulse 1s ease-out forwards;
  pointer-events: none;
}

@keyframes filledPulse {
  0% {
    opacity: 1;
    transform: scale(1);
  }
  100% {
    opacity: 0;
    transform: scale(1.05);
  }
}
</style>
