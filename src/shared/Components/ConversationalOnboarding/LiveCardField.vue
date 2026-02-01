<template>
  <div 
    class="live-card-field"
    :class="{
      'is-shimmering': isShimmering,
      'is-recently-filled': isRecentlyFilled,
      'is-editing': isEditing,
      'has-error': error,
      'is-empty': isEmpty,
      'is-answered': isAnswered,
      'is-boolean': field.type === 'switch' || field.type === 'addressToggle',
      'is-complex': field.type === 'socialNetworks' || field.type === 'addressToggle'
    }"
  >
    <div class="field-row" @click="handleFieldClick">
      <span class="field-label">
        {{ field.label }}:
        <span v-if="isAnswered && !isEmpty" class="answered-check" title="Respondido">✓</span>
      </span>
      
      <div class="field-value-area">
        <!-- Shimmer Effect -->
        <div v-if="isShimmering" class="shimmer-bar">
          <div class="shimmer-animation"></div>
        </div>
        
        <!-- Editing Mode - Campos de texto simples -->
        <template v-else-if="isEditing && !isComplexField">
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
        
        <!-- Editing Mode - Redes Sociais -->
        <template v-else-if="isEditing && field.type === 'socialNetworks'">
          <div class="social-networks-editor" @click.stop>
            <div class="social-network-item" v-for="(network, index) in editSocialNetworks" :key="index">
              <select v-model="network.type" class="social-select">
                <option value="instagram">📸 Instagram</option>
                <option value="linkedin">💼 LinkedIn</option>
                <option value="twitter">🐦 Twitter/X</option>
                <option value="facebook">📘 Facebook</option>
                <option value="youtube">🎬 YouTube</option>
                <option value="tiktok">🎵 TikTok</option>
              </select>
              <input 
                v-model="network.url" 
                class="social-input" 
                placeholder="@usuario ou URL"
              />
              <button class="btn-remove-social" @click="removeSocialNetwork(index)" title="Remover">×</button>
            </div>
            <button class="btn-add-social" @click="addSocialNetwork">+ Adicionar rede</button>
            <div class="edit-actions">
              <button class="btn-save" @click.stop="saveSocialNetworks" title="Salvar">
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
          </div>
        </template>
        
        <!-- Editing Mode - Toggle de Endereço -->
        <template v-else-if="isEditing && field.type === 'addressToggle'">
          <div class="address-toggle-editor" @click.stop>
            <div class="toggle-options">
              <button 
                class="toggle-btn" 
                :class="{ 'is-active': editAddressValue === true }"
                @click="editAddressValue = true"
              >
                📍 Tenho endereço
              </button>
              <button 
                class="toggle-btn"
                :class="{ 'is-active': editAddressValue === false }"
                @click="editAddressValue = false"
              >
                🌐 100% Online
              </button>
            </div>
            <div class="edit-actions">
              <button class="btn-save" @click.stop="saveAddressToggle" title="Salvar">
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
          </div>
        </template>
        
        <!-- Display Value -->
        <template v-else>
          <!-- Switch para campos booleanos -->
          <template v-if="field.type === 'switch'">
            <div class="switch-display" :class="{ 'is-on': value === true, 'is-off': value === false, 'is-undefined': value === null || value === undefined }">
              <div class="switch-toggle">
                <div class="switch-knob"></div>
              </div>
              <span class="switch-label">{{ switchLabel }}</span>
            </div>
          </template>
          
          <!-- AddressToggle (mostra como switch também) -->
          <template v-else-if="field.type === 'addressToggle'">
            <div class="switch-display clickable" :class="{ 'is-on': value === true, 'is-off': value === false, 'is-undefined': value === null || value === undefined }">
              <div class="switch-toggle">
                <div class="switch-knob"></div>
              </div>
              <span class="switch-label">{{ displayValue }}</span>
            </div>
          </template>
          
          <!-- Valor normal -->
          <template v-else>
            <span class="field-value" :class="{ 'is-clickable': field.editable !== false }">
              {{ displayValue }}
            </span>
          </template>
          
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
      type: [String, Number, Array, Object, Boolean],
      default: null
    },
    isShimmering: {
      type: Boolean,
      default: false
    },
    isRecentlyFilled: {
      type: Boolean,
      default: false
    },
    isAnswered: {
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
    
    // Para edição de redes sociais
    const editSocialNetworks = ref([]);
    
    // Para edição de toggle de endereço
    const editAddressValue = ref(null);
    
    // Verifica se é um campo complexo (redes sociais, toggle de endereço)
    const isComplexField = computed(() => {
      return ['socialNetworks', 'addressToggle'].includes(props.field.type);
    });
    
    // Valor formatado para exibição
    const displayValue = computed(() => {
      // Primeiro verifica se tem formatter customizado (funciona para qualquer valor, inclusive false)
      if (props.field.formatter && typeof props.field.formatter === 'function') {
        return props.field.formatter(props.value);
      }
      
      // Booleanos - tratar antes do check de !props.value
      if (typeof props.value === 'boolean') {
        return props.value ? 'Sim' : 'Não';
      }
      
      // Valores vazios/nulos
      if (props.value === null || props.value === undefined || props.value === '') {
        return 'Não definido';
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
    
    // Label para switches
    const switchLabel = computed(() => {
      if (props.value === true) {
        return props.field.labelOn || 'Sim';
      } else if (props.value === false) {
        return props.field.labelOff || 'Não';
      }
      return 'Não respondido';
    });
    
    // Verifica se o campo está vazio (booleanos false NÃO são considerados vazios)
    const isEmpty = computed(() => {
      // Booleanos nunca são "vazios" (tanto true quanto false são valores válidos)
      if (typeof props.value === 'boolean') return false;
      // Arrays vazios são considerados vazios
      if (Array.isArray(props.value)) return props.value.length === 0;
      // Null, undefined ou string vazia são vazios
      return props.value === null || props.value === undefined || props.value === '';
    });
    
    // Handler genérico de clique - decide qual modo de edição iniciar
    function handleFieldClick() {
      if (props.field.editable === false) return;
      if (isEditing.value) return;
      
      // Campos complexos têm lógica especial
      if (props.field.type === 'socialNetworks') {
        startSocialNetworksEdit();
      } else if (props.field.type === 'addressToggle') {
        startAddressToggleEdit();
      } else {
        startEdit();
      }
    }
    
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
    
    // Inicia edição de redes sociais
    function startSocialNetworksEdit() {
      emit('focus', props.field.id);
      
      // Clonar valor atual ou criar array vazio
      if (Array.isArray(props.value) && props.value.length > 0) {
        editSocialNetworks.value = props.value.map(n => ({ ...n }));
      } else {
        // Iniciar com uma rede vazia
        editSocialNetworks.value = [{ type: 'instagram', url: '' }];
      }
      
      isEditing.value = true;
    }
    
    // Inicia edição de toggle de endereço
    function startAddressToggleEdit() {
      emit('focus', props.field.id);
      editAddressValue.value = props.value;
      isEditing.value = true;
    }
    
    // Adiciona uma nova rede social
    function addSocialNetwork() {
      editSocialNetworks.value.push({ type: 'instagram', url: '' });
    }
    
    // Remove uma rede social
    function removeSocialNetwork(index) {
      editSocialNetworks.value.splice(index, 1);
    }
    
    // Salva as redes sociais
    function saveSocialNetworks() {
      // Filtrar redes vazias
      const networks = editSocialNetworks.value.filter(n => n.url && n.url.trim());
      emit('edit', props.field.id, networks);
      isEditing.value = false;
    }
    
    // Salva o toggle de endereço
    function saveAddressToggle() {
      emit('edit', props.field.id, editAddressValue.value);
      isEditing.value = false;
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
      editSocialNetworks.value = [];
      editAddressValue.value = null;
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
      switchLabel,
      isEmpty,
      isComplexField,
      editSocialNetworks,
      editAddressValue,
      handleFieldClick,
      startEdit,
      startSocialNetworksEdit,
      startAddressToggleEdit,
      addSocialNetwork,
      removeSocialNetwork,
      saveSocialNetworks,
      saveAddressToggle,
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
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.answered-check {
  color: #22c55e;
  font-size: 0.7rem;
  font-weight: bold;
  animation: checkPop 0.3s ease-out;
}

@keyframes checkPop {
  0% { transform: scale(0); opacity: 0; }
  50% { transform: scale(1.3); }
  100% { transform: scale(1); opacity: 1; }
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
// SWITCH DISPLAY
// ============================================

.switch-display {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  
  .switch-toggle {
    width: 36px;
    height: 20px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    position: relative;
    transition: all 0.25s ease;
    border: 1px solid rgba(255, 255, 255, 0.1);
  }
  
  .switch-knob {
    position: absolute;
    top: 2px;
    left: 2px;
    width: 14px;
    height: 14px;
    background: rgba(255, 255, 255, 0.4);
    border-radius: 50%;
    transition: all 0.25s ease;
  }
  
  .switch-label {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.5);
    font-style: italic;
  }
  
  // Estado ON (true)
  &.is-on {
    .switch-toggle {
      background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
      border-color: #22c55e;
    }
    
    .switch-knob {
      left: calc(100% - 16px);
      background: #fff;
    }
    
    .switch-label {
      color: #86efac;
      font-style: normal;
      font-weight: 500;
    }
  }
  
  // Estado OFF (false)
  &.is-off {
    .switch-toggle {
      background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
      border-color: #6366f1;
    }
    
    .switch-knob {
      left: calc(100% - 16px);
      background: #fff;
    }
    
    .switch-label {
      color: #a5b4fc;
      font-style: normal;
      font-weight: 500;
    }
  }
  
  // Estado indefinido (null/undefined)
  &.is-undefined {
    .switch-toggle {
      background: rgba(255, 255, 255, 0.05);
      border-color: rgba(255, 255, 255, 0.1);
    }
    
    .switch-knob {
      left: 50%;
      transform: translateX(-50%);
      background: rgba(255, 255, 255, 0.2);
    }
    
    .switch-label {
      color: rgba(255, 255, 255, 0.3);
    }
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

// ============================================
// SOCIAL NETWORKS EDITOR
// ============================================

.social-networks-editor {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  width: 100%;
  padding: 0.5rem;
  background: rgba(255, 255, 255, 0.03);
  border-radius: 0.5rem;
  border: 1px solid rgba(99, 102, 241, 0.3);
}

.social-network-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.social-select {
  padding: 0.375rem 0.5rem;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 0.375rem;
  color: #fff;
  font-size: 0.8rem;
  min-width: 110px;
  
  option {
    background: #1a1a2e;
    color: #fff;
  }
  
  &:focus {
    border-color: #6366f1;
    outline: none;
  }
}

.social-input {
  flex: 1;
  padding: 0.375rem 0.5rem;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 0.375rem;
  color: #fff;
  font-size: 0.8rem;
  
  &::placeholder {
    color: rgba(255, 255, 255, 0.3);
  }
  
  &:focus {
    border-color: #6366f1;
    outline: none;
  }
}

.btn-remove-social {
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(239, 68, 68, 0.15);
  border: none;
  border-radius: 0.25rem;
  color: #f87171;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s;
  
  &:hover {
    background: rgba(239, 68, 68, 0.3);
  }
}

.btn-add-social {
  padding: 0.375rem 0.75rem;
  background: rgba(99, 102, 241, 0.15);
  border: 1px dashed rgba(99, 102, 241, 0.4);
  border-radius: 0.375rem;
  color: #a5b4fc;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s;
  
  &:hover {
    background: rgba(99, 102, 241, 0.25);
    border-color: #6366f1;
  }
}

// ============================================
// ADDRESS TOGGLE EDITOR
// ============================================

.address-toggle-editor {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  width: 100%;
  padding: 0.5rem;
  background: rgba(255, 255, 255, 0.03);
  border-radius: 0.5rem;
  border: 1px solid rgba(99, 102, 241, 0.3);
}

.toggle-options {
  display: flex;
  gap: 0.5rem;
}

.toggle-btn {
  flex: 1;
  padding: 0.5rem 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.5rem;
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s;
  
  &:hover {
    background: rgba(255, 255, 255, 0.1);
  }
  
  &.is-active {
    background: rgba(99, 102, 241, 0.2);
    border-color: #6366f1;
    color: #a5b4fc;
    font-weight: 500;
  }
}

// ============================================
// CLICKABLE FIELD VALUE
// ============================================

.field-value.is-clickable,
.switch-display.clickable {
  cursor: pointer;
  
  &:hover {
    color: #a5b4fc;
  }
}
</style>
