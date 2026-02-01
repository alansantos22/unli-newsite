<template>
  <div class="cep-input-wrapper">
    <!-- CEP Input -->
    <div class="cep-input-container">
      <input
        ref="cepInput"
        v-model="cepValue"
        type="text"
        inputmode="numeric"
        :placeholder="placeholder"
        maxlength="9"
        class="cep-input"
        :class="{ 
          'is-loading': isLoading,
          'is-valid': isValid,
          'is-invalid': isInvalid
        }"
        @input="handleInput"
        @blur="handleBlur"
      />
      
      <div class="cep-status">
        <span v-if="isLoading" class="status-loading">
          <span class="spinner"></span>
        </span>
        <span v-else-if="isValid" class="status-valid">✓</span>
        <span v-else-if="isInvalid" class="status-invalid">✕</span>
      </div>
    </div>
    
    <!-- Resultado do CEP -->
    <Transition name="fade">
      <div v-if="addressData" class="address-result">
        <div class="address-line">
          <span class="address-icon">📍</span>
          <span class="address-text">
            {{ addressData.street }}, {{ addressData.neighborhood }}
          </span>
        </div>
        <div class="address-line">
          <span class="address-icon">🏙️</span>
          <span class="address-text">
            {{ addressData.city }} - {{ addressData.state }}
          </span>
        </div>
        
        <!-- Número e Complemento -->
        <div class="address-extras">
          <div class="extra-field">
            <label>Número</label>
            <input
              v-model="numberValue"
              type="text"
              placeholder="Nº"
              class="extra-input"
              @input="emitComplete"
            />
          </div>
          <div class="extra-field complement">
            <label>Complemento</label>
            <input
              v-model="complementValue"
              type="text"
              placeholder="Apto, Sala, etc."
              class="extra-input"
              @input="emitComplete"
            />
          </div>
        </div>
      </div>
    </Transition>
    
    <!-- Erro -->
    <p v-if="errorMessage" class="error-message">
      {{ errorMessage }}
    </p>
  </div>
</template>

<script>
import { ref, watch } from 'vue';
import { fetchAddressByCEP, formatCEP } from '../composables/useHelpers.js';

export default {
  name: 'CEPInput',
  
  props: {
    modelValue: {
      type: String,
      default: ''
    },
    placeholder: {
      type: String,
      default: 'Digite o CEP'
    },
    initialAddress: {
      type: Object,
      default: null
    }
  },
  
  emits: ['update:modelValue', 'address-found', 'complete'],
  
  setup(props, { emit }) {
    const cepInput = ref(null);
    const cepValue = ref(props.modelValue || '');
    const numberValue = ref('');
    const complementValue = ref('');
    const addressData = ref(props.initialAddress);
    const isLoading = ref(false);
    const isValid = ref(false);
    const isInvalid = ref(false);
    const errorMessage = ref('');
    
    // Formatar CEP enquanto digita
    function handleInput(event) {
      let value = event.target.value.replace(/\D/g, '');
      
      // Formatar como 00000-000
      if (value.length > 5) {
        value = value.slice(0, 5) + '-' + value.slice(5, 8);
      }
      
      cepValue.value = value;
      emit('update:modelValue', value.replace('-', ''));
      
      // Resetar estados
      isValid.value = false;
      isInvalid.value = false;
      errorMessage.value = '';
      
      // Buscar se tem 8 dígitos
      const cleanCep = value.replace(/\D/g, '');
      if (cleanCep.length === 8) {
        searchCEP(cleanCep);
      } else {
        addressData.value = null;
      }
    }
    
    function handleBlur() {
      const cleanCep = cepValue.value.replace(/\D/g, '');
      if (cleanCep.length > 0 && cleanCep.length < 8) {
        isInvalid.value = true;
        errorMessage.value = 'CEP incompleto';
      }
    }
    
    async function searchCEP(cep) {
      isLoading.value = true;
      errorMessage.value = '';
      
      try {
        const result = await fetchAddressByCEP(cep);
        
        if (result) {
          addressData.value = result;
          isValid.value = true;
          isInvalid.value = false;
          
          emit('address-found', result);
          emitComplete();
        } else {
          addressData.value = null;
          isValid.value = false;
          isInvalid.value = true;
          errorMessage.value = 'CEP não encontrado';
        }
      } catch (error) {
        console.error('[CEP Error]', error);
        isInvalid.value = true;
        errorMessage.value = 'Erro ao buscar CEP';
      } finally {
        isLoading.value = false;
      }
    }
    
    function emitComplete() {
      if (addressData.value) {
        emit('complete', {
          cep: cepValue.value.replace(/\D/g, ''),
          street: addressData.value.street,
          number: numberValue.value,
          complement: complementValue.value,
          neighborhood: addressData.value.neighborhood,
          city: addressData.value.city,
          state: addressData.value.state
        });
      }
    }
    
    // Observar mudanças na prop
    watch(() => props.modelValue, (newValue) => {
      if (newValue !== cepValue.value.replace(/\D/g, '')) {
        cepValue.value = formatCEP(newValue || '');
      }
    });
    
    return {
      cepInput,
      cepValue,
      numberValue,
      complementValue,
      addressData,
      isLoading,
      isValid,
      isInvalid,
      errorMessage,
      handleInput,
      handleBlur,
      emitComplete
    };
  }
};
</script>

<style lang="scss" scoped>
$primary: #6C5CE7;
$success: #00B894;
$error: #E17055;
$text-primary: #ffffff;
$text-secondary: rgba(255, 255, 255, 0.7);
$border-color: rgba(255, 255, 255, 0.1);
$background-card: #1a1a2e;

.cep-input-wrapper {
  width: 100%;
}

.cep-input-container {
  position: relative;
  display: flex;
  align-items: center;
}

.cep-input {
  width: 100%;
  padding: 0.75rem 2.5rem 0.75rem 1rem;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid $border-color;
  border-radius: 0.5rem;
  color: $text-primary;
  font-size: 1rem;
  font-family: monospace;
  letter-spacing: 0.1em;
  outline: none;
  transition: all 0.2s;
  
  &::placeholder {
    color: $text-secondary;
    font-family: inherit;
    letter-spacing: normal;
  }
  
  &:focus {
    border-color: $primary;
  }
  
  &.is-loading {
    border-color: $primary;
  }
  
  &.is-valid {
    border-color: $success;
  }
  
  &.is-invalid {
    border-color: $error;
  }
}

.cep-status {
  position: absolute;
  right: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  
  .status-loading .spinner {
    width: 16px;
    height: 16px;
    border: 2px solid transparent;
    border-top-color: $primary;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
  }
  
  .status-valid {
    color: $success;
    font-weight: 700;
  }
  
  .status-invalid {
    color: $error;
    font-weight: 700;
  }
}

.address-result {
  margin-top: 0.75rem;
  padding: 1rem;
  background: rgba($success, 0.1);
  border: 1px solid rgba($success, 0.2);
  border-radius: 0.5rem;
}

.address-line {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
  
  &:last-of-type {
    margin-bottom: 0;
  }
  
  .address-icon {
    font-size: 1rem;
  }
  
  .address-text {
    font-size: 0.875rem;
    color: $text-primary;
  }
}

.address-extras {
  display: flex;
  gap: 0.75rem;
  margin-top: 1rem;
  padding-top: 0.75rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.extra-field {
  flex: 1;
  
  &.complement {
    flex: 2;
  }
  
  label {
    display: block;
    font-size: 0.6875rem;
    color: $text-secondary;
    margin-bottom: 0.25rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
  
  .extra-input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid $border-color;
    border-radius: 0.375rem;
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
}

.error-message {
  margin: 0.5rem 0 0;
  font-size: 0.75rem;
  color: $error;
}

.fade-enter-active,
.fade-leave-active {
  transition: all 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
