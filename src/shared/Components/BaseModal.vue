<template>
  <Teleport to="body">
    <Transition name="modal">
      <div 
        v-if="modelValue" 
        class="modal-overlay"
        @click.self="closeOnOverlay && close()"
      >
        <div 
          class="modal-container"
          :class="[`modal-${size}`, `modal-${type}`]"
          role="dialog"
          aria-modal="true"
        >
          <!-- Header -->
          <header v-if="!hideHeader" class="modal-header">
            <div class="header-content">
              <span v-if="icon" class="header-icon">{{ icon }}</span>
              <h3 class="modal-title">{{ title }}</h3>
            </div>
            <button 
              v-if="showClose"
              type="button" 
              class="close-btn"
              @click="close"
              aria-label="Fechar"
            >
              ×
            </button>
          </header>
          
          <!-- Body -->
          <div class="modal-body">
            <slot />
          </div>
          
          <!-- Footer -->
          <footer v-if="$slots.footer || showDefaultFooter" class="modal-footer">
            <slot name="footer">
              <button 
                type="button" 
                class="btn btn-secondary"
                @click="close"
              >
                {{ cancelText }}
              </button>
              <button 
                v-if="showConfirm"
                type="button" 
                class="btn btn-primary"
                :class="`btn-${type}`"
                @click="confirm"
              >
                {{ confirmText }}
              </button>
            </slot>
          </footer>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
export default {
  name: 'BaseModal',
  
  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: ''
    },
    icon: {
      type: String,
      default: ''
    },
    size: {
      type: String,
      default: 'medium', // small, medium, large
      validator: v => ['small', 'medium', 'large'].includes(v)
    },
    type: {
      type: String,
      default: 'default', // default, warning, error, success, info
      validator: v => ['default', 'warning', 'error', 'success', 'info'].includes(v)
    },
    closeOnOverlay: {
      type: Boolean,
      default: true
    },
    showClose: {
      type: Boolean,
      default: true
    },
    hideHeader: {
      type: Boolean,
      default: false
    },
    showDefaultFooter: {
      type: Boolean,
      default: false
    },
    showConfirm: {
      type: Boolean,
      default: true
    },
    cancelText: {
      type: String,
      default: 'Fechar'
    },
    confirmText: {
      type: String,
      default: 'Confirmar'
    }
  },
  
  emits: ['update:modelValue', 'close', 'confirm'],
  
  watch: {
    modelValue(open) {
      if (open) {
        document.body.style.overflow = 'hidden';
      } else {
        document.body.style.overflow = '';
      }
    }
  },
  
  beforeUnmount() {
    document.body.style.overflow = '';
  },
  
  methods: {
    close() {
      this.$emit('update:modelValue', false);
      this.$emit('close');
    },
    
    confirm() {
      this.$emit('confirm');
    }
  }
};
</script>

<style lang="scss" scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
}

.modal-container {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  
  // Sizes
  &.modal-small { width: 100%; max-width: 400px; }
  &.modal-medium { width: 100%; max-width: 560px; }
  &.modal-large { width: 100%; max-width: 800px; }
  
  // Types - header color
  &.modal-warning .modal-header { background: linear-gradient(135deg, #f39c12, #e67e22); }
  &.modal-error .modal-header { background: linear-gradient(135deg, #e74c3c, #c0392b); }
  &.modal-success .modal-header { background: linear-gradient(135deg, #27ae60, #219a52); }
  &.modal-info .modal-header { background: linear-gradient(135deg, #3498db, #2980b9); }
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: #fff;
  
  .header-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }
  
  .header-icon {
    font-size: 1.5rem;
  }
  
  .modal-title {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 600;
  }
  
  .close-btn {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    border-radius: 8px;
    color: #fff;
    font-size: 1.5rem;
    cursor: pointer;
    transition: background 0.2s;
    
    &:hover {
      background: rgba(255, 255, 255, 0.3);
    }
  }
}

.modal-body {
  padding: 1.5rem;
  overflow-y: auto;
  flex: 1;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid #e9ecef;
  background: #f8f9fa;
  
  .btn {
    padding: 0.75rem 1.5rem;
    font-size: 0.95rem;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    
    &.btn-secondary {
      background: #e9ecef;
      color: #495057;
      
      &:hover { background: #dee2e6; }
    }
    
    &.btn-primary {
      background: #3498db;
      color: #fff;
      
      &:hover { background: #2980b9; }
    }
    
    &.btn-warning {
      background: #f39c12;
      color: #fff;
    }
    
    &.btn-error {
      background: #e74c3c;
      color: #fff;
    }
    
    &.btn-success {
      background: #27ae60;
      color: #fff;
    }
  }
}

// Transition
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.25s ease;
  
  .modal-container {
    transition: transform 0.25s ease;
  }
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  
  .modal-container {
    transform: scale(0.9) translateY(-20px);
  }
}
</style>
