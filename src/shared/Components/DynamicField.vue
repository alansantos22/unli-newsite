<template>
  <div 
    class="dynamic-field" 
    :class="[`field-type-${field.type}`, { 'has-error': hasError }]"
  >
    <!-- TEXT INPUT -->
    <template v-if="field.type === 'text'">
      <label class="field-label">
        {{ field.label }}
        <span v-if="field.required" class="required">*</span>
      </label>
      <input
        type="text"
        :value="modelValue"
        :placeholder="field.placeholder"
        :disabled="field.disabled"
        class="field-input"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="handleBlur"
      />
      <p v-if="field.hint" class="field-hint">{{ field.hint }}</p>
    </template>
    
    <!-- NUMBER INPUT -->
    <template v-else-if="field.type === 'number'">
      <label class="field-label">
        {{ field.label }}
        <span v-if="field.required" class="required">*</span>
      </label>
      <input
        type="number"
        :value="modelValue"
        :placeholder="field.placeholder"
        :min="field.min"
        :max="field.max"
        class="field-input field-input-number"
        @input="$emit('update:modelValue', Number($event.target.value))"
      />
      <p v-if="field.hint" class="field-hint">{{ field.hint }}</p>
    </template>
    
    <!-- TEXTAREA (com ou sem AI Enhance) -->
    <template v-else-if="field.type === 'textarea'">
      <template v-if="field.aiEnhance">
        <AITextEnhancer
          :modelValue="modelValue"
          :label="field.label"
          :placeholder="field.placeholder"
          :hint="field.hint"
          :rows="field.rows || 4"
          :required="field.required"
          :ai-enhance="true"
          :ai-prompt="field.aiPrompt"
          :voice-tone="voiceTone"
          :context="field.aiContext || 'texto para site institucional'"
          @update:modelValue="$emit('update:modelValue', $event)"
        />
      </template>
      <template v-else>
        <label class="field-label">
          {{ field.label }}
          <span v-if="field.required" class="required">*</span>
        </label>
        <textarea
          :value="modelValue"
          :placeholder="field.placeholder"
          :rows="field.rows || 4"
          class="field-textarea"
          @input="$emit('update:modelValue', $event.target.value)"
        />
        <p v-if="field.hint" class="field-hint">{{ field.hint }}</p>
      </template>
    </template>
    
    <!-- SELECT -->
    <template v-else-if="field.type === 'select'">
      <label class="field-label">
        {{ field.label }}
        <span v-if="field.required" class="required">*</span>
      </label>
      <select
        :value="modelValue"
        class="field-select"
        @change="$emit('update:modelValue', $event.target.value)"
      >
        <option value="" disabled>Selecione...</option>
        <option 
          v-for="option in field.options" 
          :key="option.value" 
          :value="option.value"
        >
          {{ option.label }}
        </option>
      </select>
      <p v-if="field.hint" class="field-hint">{{ field.hint }}</p>
    </template>
    
    <!-- CHECKBOX -->
    <template v-else-if="field.type === 'checkbox'">
      <label class="field-checkbox">
        <input
          type="checkbox"
          :checked="modelValue"
          @change="$emit('update:modelValue', $event.target.checked)"
        />
        <span class="checkbox-custom"></span>
        <span class="checkbox-label">{{ field.label }}</span>
      </label>
    </template>
    
    <!-- SWITCH TOGGLE -->
    <template v-else-if="field.type === 'switch'">
      <div class="field-switch">
        <label class="switch-container">
          <span class="switch-label">{{ field.label }}</span>
          <div class="switch-toggle" :class="{ 'is-active': modelValue }">
            <input
              type="checkbox"
              :checked="modelValue"
              @change="$emit('update:modelValue', $event.target.checked)"
            />
            <span class="switch-slider"></span>
          </div>
        </label>
        <p v-if="field.hint" class="field-hint">{{ field.hint }}</p>
      </div>
    </template>
    
    <!-- CEP com botão de busca -->
    <template v-else-if="field.type === 'cep'">
      <label class="field-label">
        {{ field.label }}
        <span v-if="field.required" class="required">*</span>
      </label>
      <div class="cep-input-wrapper">
        <input
          type="text"
          :value="modelValue"
          :placeholder="field.placeholder || '00000-000'"
          class="field-input cep-input"
          maxlength="9"
          @input="handleCepInput($event.target.value)"
        />
        <button
          type="button"
          class="cep-search-btn"
          :class="{ 'is-loading': isLoadingCep }"
          :disabled="!canSearchCep || isLoadingCep"
          @click="searchCep"
        >
          <span v-if="isLoadingCep" class="btn-spinner"></span>
          <span v-else>🔍 Buscar</span>
        </button>
      </div>
      <p v-if="cepError" class="field-error">{{ cepError }}</p>
      <p v-else-if="cepSuccess" class="field-success">✅ Endereço encontrado!</p>
      <p v-else-if="field.hint" class="field-hint">{{ field.hint }}</p>
    </template>
    
    <!-- CHECKBOX GROUP -->
    <template v-else-if="field.type === 'checkbox-group'">
      <fieldset class="checkbox-group">
        <legend class="field-label">
          {{ field.label }}
          <span v-if="field.required" class="required">*</span>
        </legend>
        <p v-if="field.hint" class="field-hint">{{ field.hint }}</p>
        <div class="checkbox-options">
          <label 
            v-for="option in field.options" 
            :key="option.value"
            class="checkbox-option"
          >
            <input
              type="checkbox"
              :value="option.value"
              :checked="(modelValue || []).includes(option.value)"
              @change="toggleCheckboxGroup(option.value, $event.target.checked)"
            />
            <span class="checkbox-custom"></span>
            <span class="option-label">{{ option.label }}</span>
          </label>
        </div>
      </fieldset>
    </template>
    
    <!-- COLOR PICKER -->
    <template v-else-if="field.type === 'color'">
      <label class="field-label">
        {{ field.label }}
        <span v-if="field.required" class="required">*</span>
      </label>
      <div class="color-picker">
        <div class="color-presets" v-if="field.presets">
          <button
            v-for="preset in field.presets"
            :key="preset.value"
            type="button"
            class="color-preset"
            :class="{ 'is-selected': modelValue === preset.value }"
            :style="{ backgroundColor: preset.value }"
            :title="preset.name"
            @click="$emit('update:modelValue', preset.value)"
          />
        </div>
        <div class="color-custom">
          <input
            type="color"
            :value="modelValue || '#000000'"
            class="color-input"
            @input="$emit('update:modelValue', $event.target.value)"
          />
          <span class="color-value">{{ modelValue || '#000000' }}</span>
        </div>
      </div>
    </template>
    
    <!-- TAGS INPUT -->
    <template v-else-if="field.type === 'tags'">
      <label class="field-label">
        {{ field.label }}
        <span v-if="field.required" class="required">*</span>
      </label>
      <div class="tags-input">
        <div class="tags-list">
          <span 
            v-for="(tag, index) in (modelValue || [])" 
            :key="index"
            class="tag"
          >
            {{ tag }}
            <button 
              type="button" 
              class="tag-remove" 
              @click="removeTag(index)"
            >×</button>
          </span>
        </div>
        <input
          v-if="!field.maxTags || (modelValue || []).length < field.maxTags"
          type="text"
          :placeholder="field.placeholder"
          class="tags-input-field"
          @keydown.enter.prevent="addTag"
          @keydown.tab.prevent="addTag"
          @keydown="handleTagKeydown"
        />
      </div>
      <p v-if="field.hint" class="field-hint">{{ field.hint }}</p>
      <div v-if="field.suggestions" class="tags-suggestions">
        <span class="suggestion-label">Sugestões:</span>
        <button
          v-for="sug in availableSuggestions"
          :key="sug"
          type="button"
          class="suggestion-btn"
          @click="addSuggestion(sug)"
        >
          + {{ sug }}
        </button>
      </div>
    </template>
    
    <!-- UPLOAD -->
    <template v-else-if="field.type === 'upload'">
      <label class="field-label">
        {{ field.label }}
        <span v-if="field.required" class="required">*</span>
      </label>
      <div 
        class="upload-area"
        :class="{ 'has-file': hasUploadedFile, 'is-dragging': isDragging }"
        @dragover.prevent="isDragging = true"
        @dragleave="isDragging = false"
        @drop.prevent="handleDrop"
      >
        <input
          type="file"
          :accept="field.accept"
          :multiple="field.multiple"
          class="upload-input"
          @change="handleFileSelect"
        />
        <div v-if="!hasUploadedFile" class="upload-placeholder">
          <span class="upload-icon">📁</span>
          <span class="upload-text">
            Clique ou arraste {{ field.multiple ? 'arquivos' : 'arquivo' }}
          </span>
          <span class="upload-hint">{{ field.hint || 'Formatos aceitos: ' + field.accept }}</span>
        </div>
        <div v-else class="upload-preview">
          <template v-if="field.multiple">
            <div 
              v-for="(file, index) in modelValue" 
              :key="index"
              class="uploaded-file"
            >
              <span class="file-name">{{ file.name || file }}</span>
              <button 
                type="button" 
                class="file-remove" 
                @click="removeFile(index)"
              >×</button>
            </div>
          </template>
          <template v-else>
            <img 
              v-if="isImage(modelValue)"
              :src="getFileUrl(modelValue)"
              class="preview-image"
            />
            <span v-else class="file-name">{{ getFileName(modelValue) }}</span>
            <button 
              type="button" 
              class="file-remove" 
              @click="$emit('update:modelValue', null)"
            >× Remover</button>
          </template>
        </div>
      </div>
    </template>
    
    <!-- REPEATER -->
    <template v-else-if="field.type === 'repeater'">
      <fieldset class="repeater-field">
        <legend class="field-label">
          {{ field.label }}
          <span v-if="field.required" class="required">*</span>
        </legend>
        <p v-if="field.hint" class="field-hint">{{ field.hint }}</p>
        
        <div class="repeater-items">
          <TransitionGroup name="list">
            <div 
              v-for="(item, index) in (modelValue || [])" 
              :key="item._id || index"
              class="repeater-item"
            >
              <div class="repeater-item-header">
                <span class="item-number">#{{ index + 1 }}</span>
                <button
                  type="button"
                  class="item-remove"
                  :disabled="field.minItems && (modelValue || []).length <= field.minItems"
                  @click="removeRepeaterItem(index)"
                >
                  🗑️ Remover
                </button>
              </div>
              
              <div class="repeater-item-fields">
                <DynamicField
                  v-for="subField in field.fields"
                  :key="`${index}-${subField.id}`"
                  :field="subField"
                  :modelValue="item[subField.id]"
                  :voice-tone="voiceTone"
                  @update:modelValue="updateRepeaterItem(index, subField.id, $event)"
                />
              </div>
            </div>
          </TransitionGroup>
        </div>
        
        <button
          v-if="!field.maxItems || (modelValue || []).length < field.maxItems"
          type="button"
          class="repeater-add-btn"
          @click="addRepeaterItem"
        >
          {{ field.addButtonText || '+ Adicionar' }}
        </button>
      </fieldset>
    </template>
    
    <!-- Fallback para tipos desconhecidos -->
    <template v-else>
      <div class="unknown-field">
        Campo não suportado: {{ field.type }}
      </div>
    </template>
    
    <!-- Error message -->
    <p v-if="hasError" class="field-error">{{ errorMessage }}</p>
  </div>
</template>

<script>
import AITextEnhancer from './AITextEnhancer.vue';

export default {
  name: 'DynamicField',
  
  components: {
    AITextEnhancer
  },
  
  props: {
    field: {
      type: Object,
      required: true
    },
    modelValue: {
      default: null
    },
    voiceTone: {
      type: String,
      default: 'profissional'
    }
  },
  
  emits: ['update:modelValue'],
  
  data() {
    return {
      isDragging: false,
      hasError: false,
      errorMessage: '',
      // CEP
      isLoadingCep: false,
      cepError: null,
      cepSuccess: false
    };
  },
  
  computed: {
    hasUploadedFile() {
      if (this.field.multiple) {
        return Array.isArray(this.modelValue) && this.modelValue.length > 0;
      }
      return !!this.modelValue;
    },
    
    availableSuggestions() {
      const current = this.modelValue || [];
      return (this.field.suggestions || []).filter(s => !current.includes(s));
    },
    
    canSearchCep() {
      const cep = (this.modelValue || '').replace(/\D/g, '');
      return cep.length === 8;
    }
  },
  
  methods: {
    handleBlur() {
      // Trigger CEP lookup if configured
      if (this.field.apiLookup === 'viacep' && this.modelValue) {
        this.searchCep();
      }
    },
    
    // CEP Methods
    handleCepInput(value) {
      // Formata o CEP enquanto digita (00000-000)
      let cep = value.replace(/\D/g, '');
      if (cep.length > 5) {
        cep = cep.slice(0, 5) + '-' + cep.slice(5, 8);
      }
      this.cepError = null;
      this.cepSuccess = false;
      this.$emit('update:modelValue', cep);
    },
    
    async searchCep() {
      const cep = (this.modelValue || '').replace(/\D/g, '');
      
      if (cep.length !== 8) {
        this.cepError = 'CEP deve ter 8 dígitos';
        return;
      }
      
      this.isLoadingCep = true;
      this.cepError = null;
      this.cepSuccess = false;
      
      try {
        const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const data = await response.json();
        
        if (data.erro) {
          this.cepError = 'CEP não encontrado';
          return;
        }
        
        // Emite evento especial para preencher campos relacionados
        this.$emit('cep-found', {
          street: data.logradouro || '',
          neighborhood: data.bairro || '',
          city: data.localidade || '',
          state: data.uf || ''
        });
        
        this.cepSuccess = true;
      } catch (error) {
        console.error('CEP lookup failed:', error);
        this.cepError = 'Erro ao buscar CEP. Tente novamente.';
      } finally {
        this.isLoadingCep = false;
      }
    },
    
    // eslint-disable-next-line no-unused-vars
    async fetchAddressByCep(cep) {
      // Mantido para compatibilidade
      this.searchCep();
    },
    
    // Checkbox Group
    toggleCheckboxGroup(value, checked) {
      const current = [...(this.modelValue || [])];
      
      if (checked) {
        if (!current.includes(value)) {
          current.push(value);
        }
      } else {
        const index = current.indexOf(value);
        if (index > -1) {
          current.splice(index, 1);
        }
      }
      
      this.$emit('update:modelValue', current);
    },
    
    // Tags
    handleTagKeydown(event) {
      // Adiciona tag com vírgula
      if (event.key === ',') {
        event.preventDefault();
        this.addTag(event);
      }
    },
    
    addTag(event) {
      const value = event.target.value.trim();
      if (!value) return;
      
      const current = [...(this.modelValue || [])];
      
      if (!current.includes(value)) {
        if (!this.field.maxTags || current.length < this.field.maxTags) {
          current.push(value);
          this.$emit('update:modelValue', current);
        }
      }
      
      event.target.value = '';
    },
    
    addSuggestion(value) {
      const current = [...(this.modelValue || [])];
      
      if (!current.includes(value)) {
        if (!this.field.maxTags || current.length < this.field.maxTags) {
          current.push(value);
          this.$emit('update:modelValue', current);
        }
      }
    },
    
    removeTag(index) {
      const current = [...(this.modelValue || [])];
      current.splice(index, 1);
      this.$emit('update:modelValue', current);
    },
    
    // Upload
    handleFileSelect(event) {
      const files = event.target.files;
      this.processFiles(files);
    },
    
    handleDrop(event) {
      this.isDragging = false;
      const files = event.dataTransfer.files;
      this.processFiles(files);
    },
    
    processFiles(files) {
      if (!files || files.length === 0) return;
      
      if (this.field.multiple) {
        const current = [...(this.modelValue || [])];
        const maxFiles = this.field.maxFiles || 10;
        
        for (let i = 0; i < files.length && current.length < maxFiles; i++) {
          current.push(files[i]);
        }
        
        this.$emit('update:modelValue', current);
      } else {
        this.$emit('update:modelValue', files[0]);
      }
    },
    
    removeFile(index) {
      const current = [...(this.modelValue || [])];
      current.splice(index, 1);
      this.$emit('update:modelValue', current);
    },
    
    isImage(file) {
      if (typeof file === 'string') {
        return /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(file);
      }
      return file?.type?.startsWith('image/');
    },
    
    getFileUrl(file) {
      if (typeof file === 'string') return file;
      return URL.createObjectURL(file);
    },
    
    getFileName(file) {
      if (typeof file === 'string') return file.split('/').pop();
      return file?.name || 'Arquivo';
    },
    
    // Repeater
    addRepeaterItem() {
      const current = [...(this.modelValue || [])];
      const newItem = { _id: Date.now() };
      
      // Aplica defaults dos sub-campos
      this.field.fields?.forEach(subField => {
        if (subField.default !== undefined) {
          newItem[subField.id] = subField.default;
        }
      });
      
      current.push(newItem);
      this.$emit('update:modelValue', current);
    },
    
    removeRepeaterItem(index) {
      const current = [...(this.modelValue || [])];
      current.splice(index, 1);
      this.$emit('update:modelValue', current);
    },
    
    updateRepeaterItem(index, fieldId, value) {
      const current = [...(this.modelValue || [])];
      if (!current[index]) current[index] = {};
      current[index][fieldId] = value;
      this.$emit('update:modelValue', current);
    }
  }
};
</script>

<style lang="scss" scoped>
.dynamic-field {
  width: 100%;
  
  &.has-error {
    .field-input,
    .field-textarea,
    .field-select {
      border-color: #e74c3c;
    }
  }
}

// Labels & Hints
.field-label {
  display: block;
  font-weight: 600;
  font-size: 0.95rem;
  color: #333;
  margin-bottom: 0.5rem;
  
  .required {
    color: #e74c3c;
    margin-left: 2px;
  }
}

.field-hint {
  margin: 0.5rem 0 0 0;
  font-size: 0.85rem;
  color: #666;
}

.field-error {
  margin: 0.5rem 0 0 0;
  font-size: 0.85rem;
  color: #e74c3c;
}

// Text Input & Textarea
.field-input,
.field-textarea,
.field-select {
  width: 100%;
  padding: 0.875rem 1rem;
  font-size: 1rem;
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  transition: border-color 0.2s, box-shadow 0.2s;
  font-family: inherit;
  
  &:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
  }
  
  &::placeholder {
    color: #999;
  }
}

.field-input-number {
  max-width: 200px;
}

.field-textarea {
  resize: vertical;
  min-height: 100px;
  line-height: 1.5;
}

.field-select {
  cursor: pointer;
  background-color: #fff;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  appearance: none;
}

// Checkbox
.field-checkbox {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  padding: 0.75rem 1rem;
  background: #f8f9fa;
  border-radius: 10px;
  transition: background 0.2s;
  
  &:hover {
    background: #e9ecef;
  }
  
  input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
  }
  
  .checkbox-custom {
    width: 22px;
    height: 22px;
    border: 2px solid #ccc;
    border-radius: 6px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
    
    &::after {
      content: '✓';
      color: #fff;
      font-size: 14px;
      opacity: 0;
      transition: opacity 0.2s;
    }
  }
  
  input:checked + .checkbox-custom {
    background: #27ae60;
    border-color: #27ae60;
    
    &::after {
      opacity: 1;
    }
  }
  
  .checkbox-label {
    font-size: 0.95rem;
    color: #333;
  }
}

// Checkbox Group
.checkbox-group {
  border: none;
  padding: 0;
  margin: 0;
  
  legend {
    padding: 0;
    margin-bottom: 0.5rem;
  }
}

.checkbox-options {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 0.5rem;
}

.checkbox-option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s;
  
  &:hover {
    background: #e9ecef;
  }
  
  input[type="checkbox"] {
    position: absolute;
    opacity: 0;
  }
  
  .checkbox-custom {
    width: 18px;
    height: 18px;
    border: 2px solid #ccc;
    border-radius: 4px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    
    &::after {
      content: '✓';
      color: #fff;
      font-size: 12px;
      opacity: 0;
    }
  }
  
  input:checked + .checkbox-custom {
    background: #9b59b6;
    border-color: #9b59b6;
    
    &::after {
      opacity: 1;
    }
  }
  
  .option-label {
    font-size: 0.9rem;
  }
}

// Color Picker
.color-picker {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.color-presets {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.color-preset {
  width: 40px;
  height: 40px;
  border: 3px solid transparent;
  border-radius: 50%;
  cursor: pointer;
  transition: transform 0.2s, border-color 0.2s;
  
  &:hover {
    transform: scale(1.1);
  }
  
  &.is-selected {
    border-color: #333;
    box-shadow: 0 0 0 3px #fff, 0 0 0 5px currentColor;
  }
}

.color-custom {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.color-input {
  width: 50px;
  height: 40px;
  padding: 0;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  cursor: pointer;
}

.color-value {
  font-family: monospace;
  font-size: 0.9rem;
  color: #666;
}

// Tags Input
.tags-input {
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  padding: 0.5rem;
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  min-height: 50px;
  align-items: center;
  transition: border-color 0.2s;
  
  &:focus-within {
    border-color: #3498db;
  }
}

.tags-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.tag {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.375rem 0.75rem;
  background: linear-gradient(135deg, #3498db, #9b59b6);
  color: #fff;
  font-size: 0.85rem;
  font-weight: 500;
  border-radius: 20px;
}

.tag-remove {
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.8);
  font-size: 1.1rem;
  cursor: pointer;
  padding: 0;
  margin-left: 0.25rem;
  line-height: 1;
  
  &:hover {
    color: #fff;
  }
}

.tags-input-field {
  flex: 1;
  min-width: 120px;
  border: none;
  outline: none;
  padding: 0.5rem;
  font-size: 0.95rem;
  
  &::placeholder {
    color: #999;
  }
}

.tags-suggestions {
  margin-top: 0.5rem;
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  align-items: center;
}

.suggestion-label {
  font-size: 0.8rem;
  color: #666;
}

.suggestion-btn {
  padding: 0.25rem 0.5rem;
  font-size: 0.8rem;
  background: #f0f0f0;
  border: 1px dashed #ccc;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
  
  &:hover {
    background: #e0e0e0;
    border-style: solid;
  }
}

// Upload
.upload-area {
  position: relative;
  border: 2px dashed #ccc;
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  transition: all 0.2s;
  background: #fafafa;
  
  &:hover,
  &.is-dragging {
    border-color: #3498db;
    background: #f0f8ff;
  }
  
  &.has-file {
    border-style: solid;
    border-color: #27ae60;
    background: #f0fff4;
  }
}

.upload-input {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
}

.upload-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  pointer-events: none;
}

.upload-icon {
  font-size: 2.5rem;
  opacity: 0.5;
}

.upload-text {
  font-weight: 600;
  color: #666;
}

.upload-hint {
  font-size: 0.85rem;
  color: #999;
}

.upload-preview {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  align-items: center;
  justify-content: center;
}

.preview-image {
  max-width: 150px;
  max-height: 100px;
  border-radius: 8px;
  object-fit: cover;
}

.uploaded-file {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  background: #fff;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
}

.file-name {
  font-size: 0.9rem;
  color: #333;
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.file-remove {
  background: #e74c3c;
  color: #fff;
  border: none;
  padding: 0.25rem 0.5rem;
  font-size: 0.8rem;
  border-radius: 4px;
  cursor: pointer;
  
  &:hover {
    background: #c0392b;
  }
}

// Repeater
.repeater-field {
  border: none;
  padding: 0;
  margin: 0;
  
  legend {
    padding: 0;
    margin-bottom: 0.5rem;
  }
}

.repeater-items {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 1rem;
}

.repeater-item {
  background: #f8f9fa;
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  padding: 1rem;
}

.repeater-item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #e0e0e0;
}

.item-number {
  font-weight: 700;
  font-size: 0.85rem;
  color: #666;
  background: #e9ecef;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
}

.item-remove {
  background: none;
  border: none;
  color: #e74c3c;
  font-size: 0.85rem;
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  transition: background 0.2s;
  
  &:hover:not(:disabled) {
    background: rgba(231, 76, 60, 0.1);
  }
  
  &:disabled {
    opacity: 0.4;
    cursor: not-allowed;
  }
}

.repeater-item-fields {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.repeater-add-btn {
  width: 100%;
  margin-top: 1rem;
  padding: 0.875rem;
  font-size: 1rem;
  font-weight: 600;
  color: #3498db;
  background: #fff;
  border: 2px dashed #3498db;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s;
  
  &:hover {
    background: #f0f8ff;
    border-style: solid;
  }
}

// List Transition
.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}

.list-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.list-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}

// Unknown Field
.unknown-field {
  padding: 1rem;
  background: #fff3cd;
  color: #856404;
  border-radius: 8px;
  font-size: 0.9rem;
}

// Switch Toggle
.field-switch {
  .switch-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    background: #f8f9fa;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
    
    &:hover {
      border-color: #3498db;
      background: #fff;
    }
  }
  
  .switch-label {
    font-weight: 600;
    font-size: 0.95rem;
    color: #333;
  }
  
  .switch-toggle {
    position: relative;
    width: 52px;
    height: 28px;
    flex-shrink: 0;
    
    input {
      opacity: 0;
      width: 0;
      height: 0;
      position: absolute;
    }
    
    .switch-slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #ccc;
      transition: 0.3s;
      border-radius: 28px;
      
      &::before {
        position: absolute;
        content: "";
        height: 22px;
        width: 22px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.3s;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
      }
    }
    
    &.is-active .switch-slider {
      background-color: #27ae60;
      
      &::before {
        transform: translateX(24px);
      }
    }
  }
}

// CEP Input with Search Button
.cep-input-wrapper {
  display: flex;
  gap: 0.5rem;
  
  .cep-input {
    flex: 1;
    max-width: 180px;
  }
  
  .cep-search-btn {
    padding: 0.875rem 1.25rem;
    font-size: 0.95rem;
    font-weight: 600;
    color: #fff;
    background: #3498db;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    white-space: nowrap;
    
    &:hover:not(:disabled) {
      background: #2980b9;
      transform: translateY(-1px);
    }
    
    &:disabled {
      background: #bdc3c7;
      cursor: not-allowed;
    }
    
    &.is-loading {
      pointer-events: none;
    }
    
    .btn-spinner {
      width: 16px;
      height: 16px;
      border: 2px solid rgba(255, 255, 255, 0.3);
      border-top-color: #fff;
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
    }
  }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.field-success {
  margin: 0.5rem 0 0 0;
  font-size: 0.85rem;
  color: #27ae60;
  font-weight: 500;
}
</style>
