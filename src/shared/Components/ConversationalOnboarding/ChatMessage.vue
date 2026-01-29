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
        
        <!-- Sugestões de Paletas de Cores (agrupadas) -->
        <template v-if="hasColorSuggestions">
          <div class="color-palettes">
            <div 
              v-for="(palette, idx) in colorPalettes" 
              :key="idx"
              class="color-palette-item"
            >
              <div class="palette-preview">
                <div 
                  class="color-swatch large" 
                  :style="{ backgroundColor: palette.primary }"
                  :title="palette.primary"
                ></div>
                <span class="palette-plus">+</span>
                <div 
                  class="color-swatch large" 
                  :style="{ backgroundColor: palette.secondary }"
                  :title="palette.secondary"
                ></div>
              </div>
              <div class="palette-info">
                <span class="palette-name">{{ getColorName(palette.primary) }} + {{ getColorName(palette.secondary) }}</span>
                <span class="palette-hex">{{ palette.primary }} / {{ palette.secondary }}</span>
              </div>
              <button 
                class="btn-accept"
                @click="acceptColorPalette(palette)"
              >
                ✓ Usar esta paleta
              </button>
            </div>
          </div>
          
          <!-- Color Picker Custom -->
          <div class="custom-color-picker">
            <span class="picker-label">🎨 Ou escolha suas próprias cores:</span>
            <div class="picker-inputs">
              <div class="picker-group">
                <label>Cor Principal:</label>
                <div class="color-input-wrapper">
                  <input 
                    type="color" 
                    v-model="customPrimaryColor"
                    class="color-input"
                  />
                  <span class="color-hex">{{ customPrimaryColor }}</span>
                </div>
              </div>
              <div class="picker-group">
                <label>Cor Secundária:</label>
                <div class="color-input-wrapper">
                  <input 
                    type="color" 
                    v-model="customSecondaryColor"
                    class="color-input"
                  />
                  <span class="color-hex">{{ customSecondaryColor }}</span>
                </div>
              </div>
              <button 
                class="btn-apply-colors"
                @click="applyCustomColors"
              >
                ✓ Usar estas cores
              </button>
            </div>
          </div>
        </template>
        
        <!-- Outras sugestões (não-cores) -->
        <template v-else>
          <div 
            v-for="(suggestion, idx) in message.suggestions" 
            :key="idx"
            class="suggestion-item"
          >
            <div class="suggestion-content">
              <div class="suggestion-text">
                <span class="suggestion-field">{{ formatFieldKey(suggestion.field) }}:</span>
                <span class="suggestion-value">{{ suggestion.value }}</span>
              </div>
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
        </template>
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
import { computed, ref } from 'vue';
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
  
  emits: ['action', 'suggestion-accept', 'custom-input', 'color-palette-accept'],
  
  setup(props, { emit }) {
    // DEBUG: Log das sugestões quando componente é montado
    if (props.message.suggestions && props.message.suggestions.length > 0) {
      console.log('🔍 [ChatMessage] Sugestões recebidas:', props.message.suggestions);
      props.message.suggestions.forEach((sug, idx) => {
        console.log(`  [${idx}] field: "${sug.field}", value: "${sug.value}"`);
      });
    }
    
    // Refs para color picker custom
    const customPrimaryColor = ref('#0066CC');
    const customSecondaryColor = ref('#28A745');
    
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
    
    // Verifica se as sugestões são de cores
    const hasColorSuggestions = computed(() => {
      if (!props.message.suggestions?.length) return false;
      return props.message.suggestions.some(s => 
        s.field === 'primaryColor' || s.field === 'secondaryColor'
      );
    });
    
    // Agrupa sugestões de cores em paletas (primary + secondary)
    const colorPalettes = computed(() => {
      if (!props.message.suggestions?.length) return [];
      
      const primaries = props.message.suggestions.filter(s => s.field === 'primaryColor');
      const secondaries = props.message.suggestions.filter(s => s.field === 'secondaryColor');
      
      // Combina primárias com secundárias em pares
      const palettes = [];
      const maxPairs = Math.max(primaries.length, secondaries.length);
      
      for (let i = 0; i < maxPairs; i++) {
        palettes.push({
          primary: primaries[i]?.value || '#0066CC',
          secondary: secondaries[i]?.value || '#28A745'
        });
      }
      
      return palettes;
    });
    
    const formattedTime = computed(() => {
      const date = new Date(props.message.timestamp);
      return date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    });
    
    const fieldKeyMap = {
      companyName: 'Nome',
      businessType: 'Ramo',
      frase: 'Frase de Destaque',
      primaryColor: 'Cor Principal',
      secondaryColor: 'Cor Secundária',
      voiceTone: 'Tom de Voz',
      whatsapp: 'WhatsApp',
      instagram: 'Instagram',
      facebook: 'Facebook',
      linkedin: 'LinkedIn',
      foundingYear: 'Ano de Fundação',
      companyBio: 'História',
      mission: 'Missão',
      vision: 'Visão',
      services: 'Serviços',
      faqItems: 'Perguntas Frequentes'
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
    
    function isColorField(field) {
      return field === 'primaryColor' || field === 'secondaryColor';
    }
    
    function getColorName(hexColor) {
      const colorNames = {
        '#2563eb': 'Azul',
        '#1e40af': 'Azul Escuro',
        '#3b82f6': 'Azul Claro',
        '#dc2626': 'Vermelho',
        '#16a34a': 'Verde',
        '#15803d': 'Verde Escuro',
        '#28A745': 'Verde Saúde',
        '#17A2B8': 'Azul Claro',
        '#0066CC': 'Azul',
        '#6F42C1': 'Roxo',
        '#FF6B35': 'Laranja',
        '#FFC107': 'Amarelo',
        '#E83E8C': 'Rosa',
        '#212529': 'Preto',
        '#eab308': 'Amarelo',
        '#ea580c': 'Laranja',
        '#7c3aed': 'Roxo',
        '#db2777': 'Rosa',
        '#000000': 'Preto',
        '#ffffff': 'Branco',
        '#6b7280': 'Cinza',
        '#22C55E': 'Verde Vibrante'
      };
      // Busca case-insensitive
      const normalized = hexColor?.toUpperCase();
      const found = Object.entries(colorNames).find(([hex]) => hex.toUpperCase() === normalized);
      return found ? found[1] : hexColor;
    }
    
    // Aceita uma paleta de cores
    function acceptColorPalette(palette) {
      emit('suggestion-accept', { field: 'primaryColor', value: palette.primary });
      // Pequeno delay para processar a primeira cor
      setTimeout(() => {
        emit('suggestion-accept', { field: 'secondaryColor', value: palette.secondary });
      }, 100);
    }
    
    // Aplica cores customizadas do color picker
    function applyCustomColors() {
      emit('suggestion-accept', { field: 'primaryColor', value: customPrimaryColor.value });
      setTimeout(() => {
        emit('suggestion-accept', { field: 'secondaryColor', value: customSecondaryColor.value });
      }, 100);
    }
    
    return {
      messageType,
      isAssistant,
      isAudio,
      formattedContent,
      hasSuggestions,
      hasColorSuggestions,
      colorPalettes,
      formattedTime,
      formatFieldKey,
      formatFieldValue,
      isColorField,
      getColorName,
      customPrimaryColor,
      customSecondaryColor,
      acceptColorPalette,
      applyCustomColors
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
    padding: 0.75rem;
    background: rgba(99, 102, 241, 0.1);
    border: 1px solid rgba(99, 102, 241, 0.3);
    border-radius: 0.75rem;
    transition: all 0.2s;
    
    &.is-color {
      background: rgba(99, 102, 241, 0.15);
    }
    
    &:hover {
      background: rgba(99, 102, 241, 0.15);
      border-color: rgba(99, 102, 241, 0.5);
      transform: translateX(4px);
    }
    
    &:not(:last-child) {
      margin-bottom: 0.5rem;
    }
  }
  
  .color-preview-box {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .color-swatch {
    width: 48px;
    height: 48px;
    border-radius: 0.5rem;
    border: 2px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    flex-shrink: 0;
  }
  
  .suggestion-text {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
  }
  
  .color-name {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.6);
    font-style: italic;
  }
  
  .suggestion-content {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    
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
// COLOR PALETTES
// ============================================

.color-palettes {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.color-palette-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  background: rgba(99, 102, 241, 0.1);
  border: 1px solid rgba(99, 102, 241, 0.3);
  border-radius: 0.75rem;
  transition: all 0.2s;
  
  &:hover {
    background: rgba(99, 102, 241, 0.15);
    border-color: rgba(99, 102, 241, 0.5);
    transform: translateX(4px);
  }
  
  .palette-preview {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .palette-plus {
    color: rgba(255, 255, 255, 0.5);
    font-size: 1rem;
    font-weight: 600;
  }
  
  .color-swatch.large {
    width: 48px;
    height: 48px;
    border-radius: 0.5rem;
    border: 2px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    flex-shrink: 0;
  }
  
  .palette-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
  }
  
  .palette-name {
    font-weight: 500;
    color: #fff;
  }
  
  .palette-hex {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.5);
    font-family: monospace;
  }
  
  .btn-accept {
    padding: 0.5rem 1rem;
    background: #22c55e;
    color: #fff;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
    
    &:hover {
      background: #16a34a;
    }
  }
}

// ============================================
// CUSTOM COLOR PICKER
// ============================================

.custom-color-picker {
  margin-top: 1rem;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px dashed rgba(255, 255, 255, 0.2);
  border-radius: 0.75rem;
  
  .picker-label {
    display: block;
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 0.75rem;
  }
  
  .picker-inputs {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: flex-end;
  }
  
  .picker-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    
    label {
      font-size: 0.75rem;
      color: rgba(255, 255, 255, 0.6);
    }
  }
  
  .color-input-wrapper {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .color-input {
    width: 48px;
    height: 48px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 0.5rem;
    cursor: pointer;
    padding: 0;
    background: transparent;
    
    &::-webkit-color-swatch-wrapper {
      padding: 0;
    }
    
    &::-webkit-color-swatch {
      border: none;
      border-radius: 0.35rem;
    }
    
    &::-moz-color-swatch {
      border: none;
      border-radius: 0.35rem;
    }
  }
  
  .color-hex {
    font-family: monospace;
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.7);
    text-transform: uppercase;
  }
  
  .btn-apply-colors {
    padding: 0.625rem 1.25rem;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    margin-left: auto;
    
    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
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
