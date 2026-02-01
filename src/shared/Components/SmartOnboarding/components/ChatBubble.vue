<template>
  <div 
    class="chat-bubble"
    :class="[
      `bubble-${message.type}`,
      { 
        'is-summary': message.isSummary,
        'has-suggestions': hasSuggestions,
        'has-actions': hasActions
      }
    ]"
  >
    <!-- Avatar (apenas para assistant) -->
    <div v-if="message.type === 'assistant'" class="bubble-avatar">
      🤖
    </div>
    
    <!-- Conteúdo -->
    <div class="bubble-content">
      <!-- Mensagem Principal -->
      <div class="bubble-text" v-html="formattedContent"></div>
      
      <!-- Sugestões de Texto -->
      <div v-if="hasSuggestions" class="bubble-suggestions">
        <p class="suggestions-label">💡 Sugestões:</p>
        <div class="suggestions-grid">
          <button
            v-for="(suggestion, index) in message.suggestions"
            :key="index"
            class="suggestion-card"
            @click="$emit('suggestion-click', suggestion)"
            :title="suggestion.reason"
          >
            <span class="suggestion-label">{{ suggestion.label || 'Opção ' + (index + 1) }}</span>
            <span class="suggestion-value">{{ suggestion.value }}</span>
            <span v-if="suggestion.reason" class="suggestion-reason">{{ suggestion.reason }}</span>
          </button>
        </div>
      </div>
      
      <!-- Paletas de Cores -->
      <div v-if="hasColorPalettes" class="bubble-color-palettes">
        <p class="palettes-label">🎨 Paletas Sugeridas:</p>
        <div class="palettes-grid">
          <button
            v-for="(palette, index) in message.colorPalettes"
            :key="index"
            class="palette-card"
            @click="$emit('color-palette-click', palette)"
            :title="palette.reason"
          >
            <div class="palette-preview">
              <div class="color-swatch primary" :style="{ background: palette.primary }"></div>
              <div class="color-swatch secondary" :style="{ background: palette.secondary }"></div>
            </div>
            <span class="palette-name">{{ palette.name }}</span>
            <span v-if="palette.reason" class="palette-reason">{{ palette.reason }}</span>
            <div class="palette-codes">
              <span>{{ palette.primary }}</span>
              <span>{{ palette.secondary }}</span>
            </div>
          </button>
        </div>
      </div>
      
      <!-- Ações (Confirmar/Editar no resumo) -->
      <div v-if="hasActions" class="bubble-actions">
        <button
          v-for="action in message.actions"
          :key="action.id"
          class="action-btn"
          :class="[`action-${action.type}`]"
          @click="$emit('action', action)"
        >
          {{ action.label }}
        </button>
      </div>
      
      <!-- Timestamp -->
      <div class="bubble-meta">
        <span class="bubble-time">{{ formatTime(message.timestamp) }}</span>
        <span v-if="message.confidence" class="bubble-confidence">
          {{ Math.round(message.confidence * 100) }}% confiança
        </span>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';
import { marked } from 'marked';
import DOMPurify from 'dompurify';

export default {
  name: 'ChatBubble',
  
  props: {
    message: {
      type: Object,
      required: true
    }
  },
  
  emits: ['action', 'suggestion-click', 'color-palette-click'],
  
  setup(props) {
    const formattedContent = computed(() => {
      if (!props.message.content) return '';
      
      // Converter markdown para HTML
      const html = marked.parse(props.message.content, {
        breaks: true,
        gfm: true
      });
      
      // Sanitizar HTML
      return DOMPurify.sanitize(html);
    });
    
    const hasSuggestions = computed(() => {
      return props.message.suggestions && props.message.suggestions.length > 0;
    });
    
    const hasColorPalettes = computed(() => {
      return props.message.colorPalettes && props.message.colorPalettes.length > 0;
    });
    
    const hasActions = computed(() => {
      return props.message.actions && props.message.actions.length > 0;
    });
    
    function formatTime(timestamp) {
      if (!timestamp) return '';
      const date = new Date(timestamp);
      return date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    }
    
    return {
      formattedContent,
      hasSuggestions,
      hasColorPalettes,
      hasActions,
      formatTime
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

.chat-bubble {
  display: flex;
  gap: 0.75rem;
  max-width: 85%;
  animation: fadeIn 0.3s ease;
  
  &.bubble-user {
    margin-left: auto;
    flex-direction: row-reverse;
    
    .bubble-content {
      background: linear-gradient(135deg, $primary 0%, mix($primary, $secondary, 50%) 100%);
      border-radius: 1.25rem 1.25rem 0.25rem 1.25rem;
    }
    
    .bubble-meta {
      text-align: right;
    }
  }
  
  &.bubble-assistant {
    .bubble-content {
      background: $background-card;
      border-radius: 1.25rem 1.25rem 1.25rem 0.25rem;
      border: 1px solid $border-color;
    }
  }
  
  &.is-summary {
    max-width: 100%;
    
    .bubble-content {
      background: linear-gradient(135deg, rgba($success, 0.1) 0%, rgba($primary, 0.1) 100%);
      border: 1px solid rgba($success, 0.3);
    }
  }
}

.bubble-avatar {
  width: 36px;
  height: 36px;
  background: linear-gradient(135deg, $primary 0%, $secondary 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.125rem;
  flex-shrink: 0;
}

.bubble-content {
  padding: 1rem 1.25rem;
  
  :deep(p) {
    margin: 0 0 0.5rem;
    
    &:last-child {
      margin-bottom: 0;
    }
  }
  
  :deep(strong) {
    color: $primary-light;
    font-weight: 600;
  }
  
  :deep(ul), :deep(ol) {
    margin: 0.5rem 0;
    padding-left: 1.25rem;
  }
  
  :deep(li) {
    margin: 0.25rem 0;
  }
}

.bubble-text {
  line-height: 1.6;
  font-size: 0.9375rem;
}

// ============================================
// SUGESTÕES
// ============================================

.bubble-suggestions {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid $border-color;
}

.suggestions-label {
  font-size: 0.8125rem;
  color: $text-secondary;
  margin: 0 0 0.75rem;
}

.suggestions-grid {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.suggestion-card {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  padding: 0.875rem 1rem;
  background: rgba($primary, 0.1);
  border: 1px solid rgba($primary, 0.2);
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
  text-align: left;
  color: $text-primary;
  
  &:hover {
    background: rgba($primary, 0.2);
    border-color: rgba($primary, 0.4);
    transform: translateX(4px);
  }
  
  .suggestion-label {
    font-size: 0.6875rem;
    color: $primary-light;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.25rem;
  }
  
  .suggestion-value {
    font-size: 0.9375rem;
    line-height: 1.4;
    font-weight: 500;
  }
  
  .suggestion-reason {
    font-size: 0.8125rem;
    color: $text-secondary;
    margin-top: 0.5rem;
    line-height: 1.4;
    font-style: italic;
    opacity: 0.9;
  }
}

// ============================================
// PALETAS DE CORES
// ============================================

.bubble-color-palettes {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid $border-color;
}

.palettes-label {
  font-size: 0.8125rem;
  color: $text-secondary;
  margin: 0 0 0.75rem;
}

.palettes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 0.75rem;
}

.palette-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid $border-color;
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
  color: $text-primary;
  
  &:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: $primary;
    transform: translateY(-2px);
  }
  
  .palette-preview {
    display: flex;
    gap: 0.25rem;
    margin-bottom: 0.5rem;
    
    .color-swatch {
      width: 40px;
      height: 40px;
      border-radius: 0.5rem;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
      
      &.primary {
        border-radius: 0.5rem 0 0 0.5rem;
      }
      
      &.secondary {
        border-radius: 0 0.5rem 0.5rem 0;
      }
    }
  }
  
  .palette-name {
    font-size: 0.8125rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
    text-align: center;
  }
  
  .palette-reason {
    font-size: 0.75rem;
    color: $text-secondary;
    margin: 0.5rem 0;
    line-height: 1.4;
    font-style: italic;
    opacity: 0.9;
    text-align: center;
  }
  
  .palette-codes {
    display: flex;
    gap: 0.5rem;
    
    span {
      font-size: 0.6875rem;
      color: $text-secondary;
      font-family: monospace;
    }
  }
}

// ============================================
// AÇÕES
// ============================================

.bubble-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid $border-color;
}

.action-btn {
  flex: 1;
  padding: 0.875rem 1.25rem;
  border-radius: 0.75rem;
  font-weight: 500;
  font-size: 0.9375rem;
  cursor: pointer;
  transition: all 0.2s;
  
  &.action-primary {
    background: linear-gradient(135deg, $success 0%, mix($success, $primary, 30%) 100%);
    border: none;
    color: white;
    
    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba($success, 0.4);
    }
  }
  
  &.action-secondary {
    background: transparent;
    border: 1px solid $border-color;
    color: $text-primary;
    
    &:hover {
      background: rgba(255, 255, 255, 0.05);
      border-color: rgba(255, 255, 255, 0.2);
    }
  }
}

// ============================================
// META
// ============================================

.bubble-meta {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-top: 0.5rem;
  
  .bubble-time {
    font-size: 0.6875rem;
    color: $text-secondary;
  }
  
  .bubble-confidence {
    font-size: 0.625rem;
    color: rgba($success, 0.8);
    padding: 0.125rem 0.5rem;
    background: rgba($success, 0.1);
    border-radius: 1rem;
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
