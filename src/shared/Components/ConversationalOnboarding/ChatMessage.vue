<template>
  <div 
    class="chat-message"
    :class="[
      `message-${messageType}`,
      { 'has-suggestions': hasSuggestions }
    ]"
  >
    <!-- Avatar -->
    <div class="message-avatar">
      <span v-if="isAssistant">🤖</span>
      <span v-else-if="isAudio">🎤</span>
      <span v-else>👤</span>
    </div>
    
    <!-- Content -->
    <div class="message-body">
      <!-- Main Content -->
      <div class="message-content" v-html="formattedContent"></div>
      
      <!-- Extracted Fields Preview -->
      <div v-if="message.extractedFields" class="extracted-fields">
        <div class="fields-header">
          <span class="fields-icon">✨</span>
          <span class="fields-label">Campos capturados:</span>
        </div>
        <div class="fields-list">
          <div 
            v-for="(value, key) in message.extractedFields" 
            :key="key"
            class="field-item"
          >
            <span class="field-check">✓</span>
            <span class="field-key">{{ formatFieldKey(key) }}:</span>
            <span class="field-value">{{ formatFieldValue(value) }}</span>
          </div>
        </div>
      </div>
      
      <!-- Suggestions -->
      <div v-if="hasSuggestions" class="message-suggestions">
        <div class="suggestions-header">
          <span class="suggestions-icon">💡</span>
          <span class="suggestions-label">Sugestão da IA:</span>
        </div>
        <div 
          v-for="(suggestion, idx) in message.suggestions" 
          :key="idx"
          class="suggestion-item"
        >
          <div class="suggestion-content">
            <span class="suggestion-field">{{ formatFieldKey(suggestion.field) }}:</span>
            <span class="suggestion-value">{{ suggestion.value }}</span>
          </div>
          <div class="suggestion-actions">
            <button 
              class="btn-accept"
              @click="$emit('suggestion-accept', suggestion)"
            >
              ✓ Usar esta
            </button>
          </div>
        </div>
        
        <!-- Botão "Nenhuma opção" -->
        <button 
          v-if="message.suggestions.length > 0"
          class="btn-custom-input"
          @click="$emit('custom-input', message.suggestions[0]?.field, formatFieldKey(message.suggestions[0]?.field))"
        >
          ✏️ Nenhuma me agrada, quero escrever minha própria
        </button>
      </div>
      
      <!-- Quick Actions -->
      <div v-if="message.actions?.length" class="message-actions">
        <button 
          v-for="action in message.actions" 
          :key="action.id"
          class="action-btn"
          :class="action.variant || 'default'"
          @click="$emit('action', action)"
        >
          {{ action.icon }} {{ action.label }}
        </button>
      </div>
      
      <!-- Timestamp -->
      <span class="message-time">{{ formattedTime }}</span>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';
import { marked } from 'marked';
import DOMPurify from 'dompurify';

export default {
  name: 'ChatMessage',
  
  props: {
    message: {
      type: Object,
      required: true
    }
  },
  
  emits: ['action', 'suggestion-accept', 'custom-input'],
  
  setup(props) {
    const messageType = computed(() => {
      if (props.message.type === 'assistant') return 'assistant';
      if (props.message.type === 'user_audio') return 'user-audio';
      return 'user';
    });
    
    const isAssistant = computed(() => props.message.type === 'assistant');
    const isAudio = computed(() => props.message.type === 'user_audio');
    
    const formattedContent = computed(() => {
      const content = props.message.content || '';
      // Parse markdown e sanitiza
      const html = marked.parse(content);
      return DOMPurify.sanitize(html);
    });
    
    const hasSuggestions = computed(() => {
      return props.message.suggestions?.length > 0;
    });
    
    const formattedTime = computed(() => {
      const date = new Date(props.message.timestamp);
      return date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    });
    
    const fieldKeyMap = {
      companyName: 'Nome',
      businessType: 'Ramo',
      tagline: 'Frase',
      primaryColor: 'Cor Principal',
      secondaryColor: 'Cor Secundária',
      voiceTone: 'Tom de Voz',
      whatsapp: 'WhatsApp',
      instagram: 'Instagram',
      facebook: 'Facebook',
      foundingYear: 'Ano de Fundação',
      companyBio: 'História',
      mission: 'Missão',
      vision: 'Visão'
    };
    
    function formatFieldKey(key) {
      return fieldKeyMap[key] || key;
    }
    
    function formatFieldValue(value) {
      if (typeof value === 'boolean') return value ? 'Sim' : 'Não';
      if (Array.isArray(value)) return value.join(', ');
      if (typeof value === 'object') return JSON.stringify(value);
      return String(value);
    }
    
    return {
      messageType,
      isAssistant,
      isAudio,
      formattedContent,
      hasSuggestions,
      formattedTime,
      formatFieldKey,
      formatFieldValue
    };
  }
};
</script>

<style lang="scss" scoped>
.chat-message {
  display: flex;
  gap: 0.75rem;
  max-width: 85%;
  animation: message-in 0.3s ease-out;
  
  &.message-user,
  &.message-user-audio {
    flex-direction: row-reverse;
    margin-left: auto;
    
    .message-body {
      background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
      border-radius: 1.25rem 1.25rem 0.25rem 1.25rem;
    }
    
    .message-time {
      text-align: right;
    }
  }
  
  &.message-assistant {
    .message-body {
      background: rgba(255, 255, 255, 0.08);
      border-radius: 1.25rem 1.25rem 1.25rem 0.25rem;
    }
  }
}

@keyframes message-in {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.message-avatar {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.message-body {
  padding: 0.875rem 1rem;
  max-width: 100%;
}

.message-content {
  line-height: 1.6;
  word-wrap: break-word;
  
  :deep(p) {
    margin: 0 0 0.5rem;
    
    &:last-child {
      margin-bottom: 0;
    }
  }
  
  :deep(strong) {
    font-weight: 600;
    color: #a5b4fc;
  }
  
  :deep(code) {
    background: rgba(0, 0, 0, 0.2);
    padding: 0.1rem 0.3rem;
    border-radius: 0.25rem;
    font-size: 0.9em;
  }
  
  :deep(ul), :deep(ol) {
    margin: 0.5rem 0;
    padding-left: 1.5rem;
  }
}

// ============================================
// EXTRACTED FIELDS
// ============================================

.extracted-fields {
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  
  .fields-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: rgba(255, 255, 255, 0.6);
  }
  
  .fields-list {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
  }
  
  .field-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    
    .field-check {
      color: #22c55e;
      font-size: 0.75rem;
    }
    
    .field-key {
      color: rgba(255, 255, 255, 0.7);
    }
    
    .field-value {
      font-weight: 500;
    }
  }
}

// ============================================
// SUGGESTIONS
// ============================================

.message-suggestions {
  margin-top: 0.75rem;
  padding: 0.75rem;
  background: rgba(245, 158, 11, 0.1);
  border: 1px solid rgba(245, 158, 11, 0.3);
  border-radius: 0.75rem;
  
  .suggestions-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.8rem;
    color: #fbbf24;
  }
  
  .suggestion-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: 0.5rem 0;
    
    &:not(:last-child) {
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
  }
  
  .suggestion-content {
    flex: 1;
    
    .suggestion-field {
      font-size: 0.75rem;
      color: rgba(255, 255, 255, 0.6);
      display: block;
    }
    
    .suggestion-value {
      font-weight: 500;
    }
  }
  
  .suggestion-actions {
    display: flex;
    gap: 0.5rem;
    
    button {
      padding: 0.25rem 0.75rem;
      border-radius: 0.5rem;
      border: none;
      font-size: 0.75rem;
      cursor: pointer;
      transition: all 0.2s;
    }
    
    .btn-accept {
      background: #22c55e;
      color: #fff;
      
      &:hover {
        background: #16a34a;
      }
    }
    
    .btn-dismiss {
      background: rgba(255, 255, 255, 0.1);
      color: rgba(255, 255, 255, 0.7);
      
      &:hover {
        background: rgba(255, 255, 255, 0.15);
      }
    }
  }
  
  .btn-custom-input {
    width: 100%;
    margin-top: 0.75rem;
    padding: 0.625rem 1rem;
    background: transparent;
    border: 1px dashed rgba(255, 255, 255, 0.3);
    border-radius: 0.5rem;
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s;
    
    &:hover {
      border-color: rgba(255, 255, 255, 0.5);
      color: #fff;
      background: rgba(255, 255, 255, 0.05);
    }
  }
}

// ============================================
// MESSAGE ACTIONS
// ============================================

.message-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 0.75rem;
  
  .action-btn {
    padding: 0.5rem 1rem;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 0.5rem;
    color: #fff;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    
    &:hover {
      background: rgba(255, 255, 255, 0.15);
    }
    
    &.primary {
      background: linear-gradient(135deg, #6366f1, #8b5cf6);
      border-color: transparent;
    }
    
    &.success {
      background: #22c55e;
      border-color: transparent;
    }
  }
}

// ============================================
// TIMESTAMP
// ============================================

.message-time {
  display: block;
  margin-top: 0.5rem;
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.4);
}
</style>
