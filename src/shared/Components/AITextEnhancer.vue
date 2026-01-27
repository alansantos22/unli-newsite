<template>
  <div class="ai-text-enhancer">
    <!-- Campo de texto original -->
    <div class="enhancer-field">
      <label v-if="label" class="field-label">
        {{ label }}
        <span v-if="required" class="required-indicator">*</span>
      </label>
      
      <div class="textarea-wrapper" :class="{ 'is-enhanced': hasEnhancement }">
        <textarea
          ref="textareaRef"
          v-model="internalValue"
          :placeholder="placeholder"
          :rows="rows"
          :disabled="isEnhancing"
          class="enhancer-textarea"
          @input="handleInput"
        />
        
        <!-- Botão de melhorar com IA -->
        <button
          v-if="aiEnhance && (internalValue || '').trim().length >= 10"
          type="button"
          class="enhance-button"
          :class="{ 'is-loading': isEnhancing }"
          :disabled="isEnhancing || (internalValue || '').trim().length < 10"
          @click="enhanceWithAI"
        >
          <span v-if="isEnhancing" class="spinner"></span>
          <span v-else>✨</span>
          {{ isEnhancing ? 'Melhorando...' : 'Melhorar com IA' }}
        </button>
      </div>
      
      <p v-if="hint" class="field-hint">{{ hint }}</p>
    </div>
    
    <!-- Preview da melhoria -->
    <Transition name="slide-fade">
      <div v-if="enhancedText && showEnhancement" class="enhancement-preview">
        <div class="preview-header">
          <span class="preview-title">✨ Versão melhorada</span>
          <div class="preview-actions">
            <button 
              type="button" 
              class="action-btn accept"
              @click="acceptEnhancement"
            >
              ✓ Usar esta versão
            </button>
            <button 
              type="button" 
              class="action-btn reject"
              @click="rejectEnhancement"
            >
              ✗ Manter original
            </button>
          </div>
        </div>
        
        <div class="preview-content">
          <p>{{ enhancedText }}</p>
        </div>
        
        <!-- Comparação lado a lado em telas maiores -->
        <div v-if="showComparison" class="comparison-view">
          <div class="comparison-column original">
            <span class="column-label">Original</span>
            <p>{{ originalText }}</p>
          </div>
          <div class="comparison-column enhanced">
            <span class="column-label">Melhorado</span>
            <p>{{ enhancedText }}</p>
          </div>
        </div>
      </div>
    </Transition>
    
    <!-- Mensagem de erro -->
    <Transition name="fade">
      <div v-if="error" class="error-message">
        <span>⚠️</span> {{ error }}
        <button type="button" class="retry-btn" @click="enhanceWithAI">
          Tentar novamente
        </button>
      </div>
    </Transition>
  </div>
</template>

<script>
export default {
  name: 'AITextEnhancer',
  
  props: {
    modelValue: {
      type: String,
      default: ''
    },
    label: {
      type: String,
      default: ''
    },
    placeholder: {
      type: String,
      default: ''
    },
    hint: {
      type: String,
      default: ''
    },
    rows: {
      type: Number,
      default: 4
    },
    required: {
      type: Boolean,
      default: false
    },
    aiEnhance: {
      type: Boolean,
      default: true
    },
    aiPrompt: {
      type: String,
      default: 'Melhore este texto mantendo a essência, apenas refinando a gramática, clareza e impacto'
    },
    voiceTone: {
      type: String,
      default: 'profissional'
    },
    context: {
      type: String,
      default: 'texto para site institucional'
    },
    apiEndpoint: {
      type: String,
      default: '/api/ai/enhance-text.php'
    },
    showComparison: {
      type: Boolean,
      default: false
    }
  },
  
  emits: ['update:modelValue', 'enhanced', 'error'],
  
  data() {
    return {
      internalValue: this.modelValue || '',
      originalText: '',
      enhancedText: '',
      showEnhancement: false,
      isEnhancing: false,
      error: null,
      hasEnhancement: false
    };
  },
  
  watch: {
    modelValue(newVal) {
      this.internalValue = newVal || '';
    }
  },
  
  methods: {
    handleInput() {
      this.$emit('update:modelValue', this.internalValue);
      // Limpa a sugestão quando o usuário edita
      if (this.showEnhancement) {
        this.showEnhancement = false;
      }
    },
    
    async enhanceWithAI() {
      if (this.internalValue.trim().length < 10) {
        this.error = 'Digite pelo menos 10 caracteres para melhorar com IA';
        return;
      }
      
      this.isEnhancing = true;
      this.error = null;
      this.originalText = this.internalValue;
      
      try {
        const response = await fetch(this.apiEndpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            text: this.internalValue,
            prompt: this.aiPrompt,
            voice_tone: this.voiceTone,
            context: this.context
          })
        });
        
        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.error || 'Erro ao processar texto');
        }
        
        const data = await response.json();
        
        if (data.success && data.enhanced_text) {
          this.enhancedText = data.enhanced_text;
          this.showEnhancement = true;
          this.$emit('enhanced', {
            original: this.originalText,
            enhanced: this.enhancedText
          });
        } else {
          throw new Error('Resposta inválida da IA');
        }
        
      } catch (err) {
        console.error('AI Enhancement error:', err);
        this.error = err.message || 'Erro ao melhorar texto. Tente novamente.';
        this.$emit('error', err);
      } finally {
        this.isEnhancing = false;
      }
    },
    
    acceptEnhancement() {
      this.internalValue = this.enhancedText;
      this.$emit('update:modelValue', this.enhancedText);
      this.showEnhancement = false;
      this.hasEnhancement = true;
      this.enhancedText = '';
    },
    
    rejectEnhancement() {
      this.showEnhancement = false;
      this.enhancedText = '';
    },
    
    focus() {
      this.$refs.textareaRef?.focus();
    }
  }
};
</script>

<style lang="scss" scoped>
.ai-text-enhancer {
  width: 100%;
}

.enhancer-field {
  position: relative;
}

.field-label {
  display: block;
  font-weight: 600;
  font-size: 0.95rem;
  color: #333;
  margin-bottom: 0.5rem;
  
  .required-indicator {
    color: #e74c3c;
    margin-left: 2px;
  }
}

.textarea-wrapper {
  position: relative;
  
  &.is-enhanced .enhancer-textarea {
    border-color: #27ae60;
    background: linear-gradient(135deg, #f0fff4 0%, #fff 100%);
  }
}

.enhancer-textarea {
  width: 100%;
  padding: 1rem;
  padding-bottom: 3rem; // Espaço para o botão
  font-size: 1rem;
  line-height: 1.6;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  resize: vertical;
  min-height: 120px;
  font-family: inherit;
  transition: border-color 0.3s, box-shadow 0.3s;
  
  &:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
  }
  
  &:disabled {
    background: #f8f9fa;
    cursor: not-allowed;
  }
  
  &::placeholder {
    color: #999;
  }
}

.enhance-button {
  position: absolute;
  bottom: 10px;
  right: 10px;
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  font-size: 0.85rem;
  font-weight: 600;
  color: #fff;
  background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
  border: none;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 10px rgba(155, 89, 182, 0.3);
  
  &:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(155, 89, 182, 0.4);
  }
  
  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
  }
  
  &.is-loading {
    background: linear-gradient(135deg, #7f8c8d 0%, #6c7a7a 100%);
  }
  
  .spinner {
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
  }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.field-hint {
  margin-top: 0.5rem;
  font-size: 0.85rem;
  color: #666;
}

// Enhancement Preview
.enhancement-preview {
  margin-top: 1rem;
  background: linear-gradient(135deg, #fef9e7 0%, #fff 100%);
  border: 2px solid #f39c12;
  border-radius: 12px;
  overflow: hidden;
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: rgba(243, 156, 18, 0.1);
  border-bottom: 1px solid rgba(243, 156, 18, 0.2);
  flex-wrap: wrap;
  gap: 0.5rem;
}

.preview-title {
  font-weight: 700;
  color: #c27c0e;
  font-size: 0.95rem;
}

.preview-actions {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 8px 16px;
  font-size: 0.85rem;
  font-weight: 600;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  
  &.accept {
    background: #27ae60;
    color: #fff;
    
    &:hover {
      background: #219a52;
    }
  }
  
  &.reject {
    background: #e74c3c;
    color: #fff;
    
    &:hover {
      background: #c0392b;
    }
  }
}

.preview-content {
  padding: 1rem;
  
  p {
    margin: 0;
    font-size: 1rem;
    line-height: 1.6;
    color: #333;
  }
}

// Comparison View
.comparison-view {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1px;
  background: #e0e0e0;
  margin-top: 1px;
  
  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
}

.comparison-column {
  padding: 1rem;
  background: #fff;
  
  &.original {
    background: #fff5f5;
    
    .column-label {
      color: #c0392b;
    }
  }
  
  &.enhanced {
    background: #f0fff4;
    
    .column-label {
      color: #27ae60;
    }
  }
}

.column-label {
  display: block;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.5rem;
}

// Error Message
.error-message {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.75rem;
  padding: 0.75rem 1rem;
  background: #fff5f5;
  border: 1px solid #fed7d7;
  border-radius: 8px;
  color: #c53030;
  font-size: 0.9rem;
  
  .retry-btn {
    margin-left: auto;
    padding: 4px 12px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #c53030;
    background: transparent;
    border: 1px solid currentColor;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
    
    &:hover {
      background: #c53030;
      color: #fff;
    }
  }
}

// Animations
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
