<template>
  <div 
    class="preview-field"
    :class="{
      'is-shimmering': isShimmering,
      'is-filled': isFilled,
      'is-recently-filled': isRecentlyFilled,
      'has-error': error,
      'is-editing': isEditing
    }"
    @click="handleClick"
  >
    <span class="field-label">{{ label }}:</span>
    
    <div class="field-value-wrapper">
      <!-- Shimmer Effect -->
      <div v-if="isShimmering" class="shimmer-bar"></div>
      
      <!-- Editing Mode -->
      <template v-else-if="isEditing">
        <input
          ref="inputRef"
          v-model="editValue"
          class="field-input"
          :placeholder="label"
          @blur="saveEdit"
          @keyup.enter="saveEdit"
          @keyup.escape="cancelEdit"
        />
        <button class="btn-save" @click.stop="saveEdit" title="Salvar">✓</button>
        <button class="btn-cancel" @click.stop="cancelEdit" title="Cancelar">✕</button>
      </template>
      
      <!-- Value or Placeholder -->
      <template v-else>
        <span class="field-value" :class="{ 'is-empty': !value }">
          {{ value || 'Não definido' }}
        </span>
        
        <!-- Edit hint on hover -->
        <span class="edit-hint" title="Clique para editar">✏️</span>
      </template>
      
      <!-- Recently Filled Indicator -->
      <span v-if="isRecentlyFilled && !isEditing" class="filled-check">✓</span>
    </div>
    
    <!-- Error -->
    <span v-if="error" class="field-error">{{ error }}</span>
  </div>
</template>

<script>
import { ref, computed, nextTick } from 'vue';

export default {
  name: 'PreviewField',
  
  props: {
    fieldId: {
      type: String,
      required: true
    },
    label: {
      type: String,
      required: true
    },
    value: {
      type: [String, Number],
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
    },
    editable: {
      type: Boolean,
      default: true
    }
  },
  
  emits: ['click', 'edit'],
  
  setup(props, { emit }) {
    const isEditing = ref(false);
    const editValue = ref('');
    const inputRef = ref(null);
    
    const isFilled = computed(() => {
      return props.value !== '' && props.value !== null && props.value !== undefined;
    });
    
    function handleClick() {
      if (props.editable && !isEditing.value) {
        startEdit();
      } else {
        emit('click');
      }
    }
    
    function startEdit() {
      editValue.value = props.value || '';
      isEditing.value = true;
      nextTick(() => {
        inputRef.value?.focus();
        inputRef.value?.select();
      });
    }
    
    function saveEdit() {
      if (editValue.value !== props.value) {
        emit('edit', editValue.value);
      }
      isEditing.value = false;
    }
    
    function cancelEdit() {
      isEditing.value = false;
      editValue.value = props.value || '';
    }
    
    return { 
      isFilled,
      isEditing,
      editValue,
      inputRef,
      handleClick,
      startEdit,
      saveEdit,
      cancelEdit
    };
  }
};
</script>

<style lang="scss" scoped>
.preview-field {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  cursor: pointer;
  transition: all 0.2s;
  
  &:hover:not(.is-editing) {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 0.25rem;
    margin: 0 -0.5rem;
    padding: 0.5rem;
    
    .edit-hint {
      opacity: 1;
    }
  }
  
  &.is-editing {
    background: rgba(99, 102, 241, 0.1);
    border-radius: 0.25rem;
    margin: 0 -0.5rem;
    padding: 0.5rem;
    cursor: default;
  }
  
  &:last-child {
    border-bottom: none;
  }
  
  &.is-recently-filled {
    .field-value-wrapper {
      animation: highlight-pulse 1s ease-out;
    }
  }
  
  &.has-error {
    .field-value {
      color: #ef4444;
    }
  }
}

@keyframes highlight-pulse {
  0% { 
    background: rgba(34, 197, 94, 0.3);
    border-radius: 0.25rem;
  }
  100% { 
    background: transparent;
  }
}

.field-label {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.5);
  min-width: 60px;
}

.field-value-wrapper {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  min-height: 20px;
}

.field-value {
  font-size: 0.875rem;
  font-weight: 500;
  
  &.is-empty {
    color: rgba(255, 255, 255, 0.3);
    font-weight: 400;
    font-style: italic;
  }
}

.field-input {
  flex: 1;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(99, 102, 241, 0.5);
  border-radius: 0.25rem;
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
  color: #fff;
  outline: none;
  
  &:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
  }
  
  &::placeholder {
    color: rgba(255, 255, 255, 0.3);
  }
}

.btn-save,
.btn-cancel {
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  border-radius: 50%;
  font-size: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-save {
  background: #22c55e;
  color: #fff;
  
  &:hover {
    background: #16a34a;
    transform: scale(1.1);
  }
}

.btn-cancel {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
  
  &:hover {
    background: rgba(239, 68, 68, 0.3);
    transform: scale(1.1);
  }
}

.edit-hint {
  opacity: 0;
  font-size: 0.75rem;
  transition: opacity 0.2s;
}

.shimmer-bar {
  width: 100%;
  height: 16px;
  background: linear-gradient(
    90deg,
    rgba(255, 255, 255, 0.05) 0%,
    rgba(255, 255, 255, 0.15) 50%,
    rgba(255, 255, 255, 0.05) 100%
  );
  background-size: 200% 100%;
  border-radius: 4px;
  animation: shimmer-slide 1.5s ease-in-out infinite;
}

@keyframes shimmer-slide {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.filled-check {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 18px;
  height: 18px;
  background: #22c55e;
  color: #fff;
  border-radius: 50%;
  font-size: 0.7rem;
  animation: check-bounce 0.4s ease-out;
}

@keyframes check-bounce {
  0% { transform: scale(0); }
  50% { transform: scale(1.3); }
  100% { transform: scale(1); }
}

.field-error {
  font-size: 0.7rem;
  color: #ef4444;
  margin-left: auto;
}
</style>
