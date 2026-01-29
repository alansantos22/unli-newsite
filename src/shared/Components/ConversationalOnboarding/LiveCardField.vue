<template>
  <div 
    class="live-card-field"
    :class="{
      'is-shimmering': isShimmering,
      'is-recently-filled': isRecentlyFilled,
      'is-editing': isEditing,
      'has-error': error,
      'is-empty': !value
    }"
  >
    <div class="field-row" @click="startEdit">
      <span class="field-label">{{ field.label }}:</span>
      
      <div class="field-value-area">
        <!-- Shimmer Effect -->
        <div v-if="isShimmering" class="shimmer-bar">
          <div class="shimmer-animation"></div>
        </div>
        
        <!-- Editing Mode -->
        <template v-else-if="isEditing">
          <input
            ref="inputRef"
            v-model="editValue"
            class="field-input"
            :type="field.inputType || 'text'"
            :placeholder="field.placeholder || field.label"
            @blur="saveEdit"
            @keyup.enter="saveEdit"
            @keyup.escape="cancelEdit"
          />
          <div class="edit-actions">
            <button class="btn-save" @click.stop="saveEdit" title="Salvar">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
            </button>
            <button class="btn-cancel" @click.stop="cancelEdit" title="Cancelar">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>
        </template>
        
        <!-- Display Value -->
        <template v-else>
          <span class="field-value">
            {{ displayValue }}
          </span>
          
          <!-- Recently Filled Indicator -->
          <Transition name="check-pop">
            <span v-if="isRecentlyFilled" class="filled-check">✓</span>
          </Transition>
          
          <!-- Edit Hint -->
          <span v-if="field.editable !== false" class="edit-hint">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
          </span>
        </template>
      </div>
    </div>
    
    <!-- Error Message -->
    <Transition name="error-slide">
      <span v-if="error" class="field-error">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"></path>
        </svg>
        {{ error }}
      </span>
    </Transition>
  </div>
</template>

<script>
import { ref, computed, nextTick, watch } from 'vue';

export default {
  name: 'LiveCardField',
  
  props: {
    field: {
      type: Object,
      required: true
      // { id, label, type, placeholder, editable, formatter }
    },
    value: {
      type: [String, Number, Array, Object],
      default: ''
    },
    isShimmering: {
      type: Boolean,
      default: false
    },
    isRecentlyFilled: {
      type: Boolean,
      default: false
    },
    error: {
      type: String,
      default: null
    }
  },
  
  emits: ['edit', 'focus'],
  
  setup(props, { emit }) {
    const isEditing = ref(false);
    const editValue = ref('');
    const inputRef = ref(null);
    
    // Valor formatado para exibição
    const displayValue = computed(() => {
      if (!props.value) return 'Não definido';
      
      // Aplicar formatter customizado se existir
      if (props.field.formatter && typeof props.field.formatter === 'function') {
        return props.field.formatter(props.value);
      }
      
      // Arrays
      if (Array.isArray(props.value)) {
        return props.value.length > 0 
          ? `${props.value.length} item(s)`
          : 'Não definido';
      }
      
      // Objetos
      if (typeof props.value === 'object') {
        return JSON.stringify(props.value);
      }
      
      return String(props.value);
    });
    
    function startEdit() {
      if (props.field.editable === false) return;
      if (isEditing.value) return;
      
      editValue.value = props.value || '';
      isEditing.value = true;
      emit('focus', props.field.id);
      
      nextTick(() => {
        inputRef.value?.focus();
        inputRef.value?.select();
      });
    }
    
    function saveEdit() {
      if (editValue.value !== props.value) {
        emit('edit', props.field.id, editValue.value);
      }
      isEditing.value = false;
    }
    
    function cancelEdit() {
      isEditing.value = false;
      editValue.value = props.value || '';
    }
    
    // Resetar estado de edição se valor externo mudar
    watch(() => props.value, () => {
      if (!isEditing.value) {
        editValue.value = props.value || '';
      }
    });
    
    return {
      isEditing,
      editValue,
      inputRef,
      displayValue,
      startEdit,
      saveEdit,
      cancelEdit
    };
  }
};
</script>

<style lang="scss" scoped>
// ============================================
// LIVE CARD FIELD
// ============================================

.live-card-field {
  padding: 0.5rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  transition: all 0.2s ease;
  
  &:last-child {
    border-bottom: none;
  }
  
  &:hover:not(.is-editing) {
    background: rgba(255, 255, 255, 0.02);
    margin: 0 -0.5rem;
    padding: 0.5rem;
    border-radius: 0.5rem;
    
    .edit-hint {
      opacity: 1;
    }
  }
  
  &.is-shimmering {
    pointer-events: none;
  }
  
  &.is-recently-filled {
    .field-value {
      color: #86efac;
    }
  }
  
  &.has-error {
    .field-value {
      color: #fca5a5;
    }
  }
  
  &.is-empty {
    .field-value {
      color: rgba(255, 255, 255, 0.3);
      font-style: italic;
    }
  }
}

.field-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
}

.field-label {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.5);
  min-width: 80px;
  flex-shrink: 0;
}

.field-value-area {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  min-height: 28px;
}

.field-value {
  font-size: 0.875rem;
  color: #fff;
  flex: 1;
  line-height: 1.4;
  transition: color 0.2s;
}

// ============================================
// SHIMMER
// ============================================

.shimmer-bar {
  flex: 1;
  height: 20px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 0.25rem;
  overflow: hidden;
  position: relative;
}

.shimmer-animation {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    90deg,
    transparent 0%,
    rgba(99, 102, 241, 0.15) 50%,
    transparent 100%
  );
  animation: shimmer-slide 1.5s ease-in-out infinite;
}

@keyframes shimmer-slide {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

// ============================================
// EDITING
// ============================================

.field-input {
  flex: 1;
  padding: 0.375rem 0.625rem;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(99, 102, 241, 0.5);
  border-radius: 0.375rem;
  color: #fff;
  font-size: 0.875rem;
  outline: none;
  
  &::placeholder {
    color: rgba(255, 255, 255, 0.3);
  }
  
  &:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
  }
}

.edit-actions {
  display: flex;
  gap: 0.25rem;
}

.btn-save,
.btn-cancel {
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-save {
  background: rgba(34, 197, 94, 0.2);
  color: #86efac;
  
  &:hover {
    background: rgba(34, 197, 94, 0.3);
  }
}

.btn-cancel {
  background: rgba(239, 68, 68, 0.15);
  color: #fca5a5;
  
  &:hover {
    background: rgba(239, 68, 68, 0.25);
  }
}

// ============================================
// INDICATORS
// ============================================

.filled-check {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 18px;
  height: 18px;
  background: rgba(34, 197, 94, 0.2);
  color: #22c55e;
  font-size: 0.7rem;
  border-radius: 50%;
  flex-shrink: 0;
}

.edit-hint {
  opacity: 0;
  color: rgba(255, 255, 255, 0.3);
  transition: opacity 0.2s;
  flex-shrink: 0;
  
  &:hover {
    color: #6366f1;
  }
}

// ============================================
// ERROR
// ============================================

.field-error {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  margin-top: 0.375rem;
  padding-left: calc(80px + 0.75rem); // Alinhado com o valor
  font-size: 0.75rem;
  color: #f87171;
  
  svg {
    flex-shrink: 0;
  }
}

// ============================================
// TRANSITIONS
// ============================================

.check-pop-enter-active {
  animation: pop-in 0.3s ease;
}

.check-pop-leave-active {
  animation: pop-out 0.2s ease;
}

@keyframes pop-in {
  0% {
    transform: scale(0);
    opacity: 0;
  }
  60% {
    transform: scale(1.2);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

@keyframes pop-out {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  100% {
    transform: scale(0);
    opacity: 0;
  }
}

.error-slide-enter-active,
.error-slide-leave-active {
  transition: all 0.25s ease;
}

.error-slide-enter-from,
.error-slide-leave-to {
  opacity: 0;
  transform: translateY(-5px);
}

// ============================================
// RESPONSIVE
// ============================================

@media (max-width: 768px) {
  .field-label {
    min-width: 70px;
    font-size: 0.75rem;
  }
  
  .field-value {
    font-size: 0.8rem;
  }
  
  .field-error {
    padding-left: calc(70px + 0.75rem);
  }
}
</style>
