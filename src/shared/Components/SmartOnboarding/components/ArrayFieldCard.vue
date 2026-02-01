<template>
  <div 
    class="array-field-card"
    :class="{
      'is-filled': hasItems,
      'is-expanded': isExpanded
    }"
  >
    <!-- Header (Always Visible) -->
    <div class="field-header" @click="toggleExpand">
      <div class="header-left">
        <span class="status-icon" :class="{ 'filled': hasItems }">
          {{ hasItems ? '✓' : '○' }}
        </span>
        <span class="field-label">
          {{ field.label }}
          <span v-if="field.required" class="required-badge">*</span>
        </span>
      </div>
      <div class="header-right">
        <span class="items-count">{{ itemsCount }} item(s)</span>
        <span v-if="field.xp" class="field-xp">+{{ field.xp }} XP</span>
        <span class="expand-icon">{{ isExpanded ? '▲' : '▼' }}</span>
      </div>
    </div>
    
    <!-- Expanded Content -->
    <div v-if="isExpanded" class="field-content" @click.stop>
      
      <!-- Services Type -->
      <template v-if="field.type === 'services'">
        <div 
          v-for="(item, index) in localItems" 
          :key="index"
          class="item-box service-box"
        >
          <div class="item-header">
            <span class="item-number">Serviço {{ index + 1 }}</span>
            <button class="btn-remove" @click="removeItem(index)" title="Remover">✕</button>
          </div>
          
          <div class="item-fields">
            <div class="field-group">
              <label>Título *</label>
              <input 
                type="text" 
                v-model="item.name"
                placeholder="Ex: Consultoria de Marketing"
                @change="emitUpdate"
              />
            </div>
            
            <div class="field-group">
              <label>Descrição</label>
              <textarea 
                v-model="item.description"
                placeholder="Descreva o serviço..."
                rows="2"
                @change="emitUpdate"
              ></textarea>
            </div>
            
            <div class="field-group field-image">
              <label>Imagem (opcional)</label>
              <div class="image-upload-box">
                <img v-if="item.image" :src="item.image" alt="Preview" class="image-preview" />
                <input 
                  type="file" 
                  accept="image/*"
                  :id="`service-image-${index}`"
                  class="hidden"
                  @change="(e) => handleImageUpload(e, index)"
                />
                <label :for="`service-image-${index}`" class="btn-upload-image">
                  {{ item.image ? '📷 Trocar' : '📷 Adicionar' }}
                </label>
                <button v-if="item.image" class="btn-remove-image" @click="removeImage(index)">✕</button>
              </div>
            </div>
          </div>
        </div>
        
        <button class="btn-add-item" @click="addService">
          + Adicionar Serviço
        </button>
      </template>
      
      <!-- FAQ Type -->
      <template v-else-if="field.type === 'faq'">
        <div 
          v-for="(item, index) in localItems" 
          :key="index"
          class="item-box faq-box"
        >
          <div class="item-header">
            <span class="item-number">Pergunta {{ index + 1 }}</span>
            <button class="btn-remove" @click="removeItem(index)" title="Remover">✕</button>
          </div>
          
          <div class="item-fields">
            <div class="field-group">
              <label>Pergunta *</label>
              <input 
                type="text" 
                v-model="item.question"
                placeholder="Ex: Qual o prazo de entrega?"
                @change="emitUpdate"
              />
            </div>
            
            <div class="field-group">
              <label>Resposta *</label>
              <textarea 
                v-model="item.answer"
                placeholder="Digite a resposta..."
                rows="3"
                @change="emitUpdate"
              ></textarea>
            </div>
          </div>
        </div>
        
        <button class="btn-add-item" @click="addFaq">
          + Adicionar Pergunta
        </button>
      </template>
      
      <!-- Phones Type -->
      <template v-else-if="field.type === 'phones'">
        <div 
          v-for="(item, index) in localItems" 
          :key="index"
          class="item-box phone-box"
        >
          <div class="item-header">
            <span class="item-number">Telefone {{ index + 1 }}</span>
            <button class="btn-remove" @click="removeItem(index)" title="Remover">✕</button>
          </div>
          
          <div class="item-fields inline">
            <div class="field-group flex-1">
              <label>Rótulo</label>
              <input 
                type="text" 
                v-model="item.label"
                placeholder="Ex: Vendas, Suporte"
                @change="emitUpdate"
              />
            </div>
            
            <div class="field-group flex-1">
              <label>Número *</label>
              <input 
                type="tel" 
                v-model="item.number"
                placeholder="(00) 00000-0000"
                @change="emitUpdate"
              />
            </div>
          </div>
        </div>
        
        <button class="btn-add-item" @click="addPhone">
          + Adicionar Telefone
        </button>
      </template>
      
    </div>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue';

export default {
  name: 'ArrayFieldCard',
  
  props: {
    field: {
      type: Object,
      required: true
    },
    value: {
      type: Array,
      default: () => []
    }
  },
  
  emits: ['edit', 'image-upload'],
  
  setup(props, { emit }) {
    const isExpanded = ref(false);
    const localItems = ref([]);
    
    // Initialize local items from prop
    watch(() => props.value, (newVal) => {
      if (Array.isArray(newVal)) {
        localItems.value = JSON.parse(JSON.stringify(newVal));
      } else {
        localItems.value = [];
      }
    }, { immediate: true, deep: true });
    
    const hasItems = computed(() => localItems.value.length > 0);
    const itemsCount = computed(() => localItems.value.length);
    
    function toggleExpand() {
      isExpanded.value = !isExpanded.value;
    }
    
    function emitUpdate() {
      emit('edit', [...localItems.value]);
    }
    
    function removeItem(index) {
      localItems.value.splice(index, 1);
      emitUpdate();
    }
    
    // Services
    function addService() {
      localItems.value.push({
        name: '',
        description: '',
        image: null
      });
      emitUpdate();
    }
    
    function handleImageUpload(event, index) {
      const file = event.target.files[0];
      if (!file) return;
      
      const reader = new FileReader();
      reader.onload = (e) => {
        localItems.value[index].image = e.target.result;
        emitUpdate();
      };
      reader.readAsDataURL(file);
    }
    
    function removeImage(index) {
      localItems.value[index].image = null;
      emitUpdate();
    }
    
    // FAQ
    function addFaq() {
      localItems.value.push({
        question: '',
        answer: ''
      });
      emitUpdate();
    }
    
    // Phones
    function addPhone() {
      localItems.value.push({
        label: '',
        number: ''
      });
      emitUpdate();
    }
    
    return {
      isExpanded,
      localItems,
      hasItems,
      itemsCount,
      toggleExpand,
      emitUpdate,
      removeItem,
      addService,
      handleImageUpload,
      removeImage,
      addFaq,
      addPhone
    };
  }
};
</script>

<style lang="scss" scoped>
$primary: #6C5CE7;
$primary-light: #A29BFE;
$secondary: #00CEC9;
$background-dark: #0f0f1a;
$background-card: #1a1a2e;
$background-elevated: #252540;
$text-primary: #ffffff;
$text-secondary: rgba(255, 255, 255, 0.6);
$text-muted: rgba(255, 255, 255, 0.4);
$border-color: rgba(255, 255, 255, 0.1);
$success: #00B894;
$warning: #FDCB6E;

.array-field-card {
  background: $background-card;
  border-radius: 12px;
  border: 1px solid $border-color;
  overflow: hidden;
  transition: all 0.2s ease;
  
  &.is-filled {
    border-color: rgba($success, 0.3);
  }
  
  &.is-expanded {
    border-color: rgba($primary, 0.4);
    box-shadow: 0 4px 20px rgba($primary, 0.15);
  }
}

// Header
.field-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.875rem 1rem;
  cursor: pointer;
  transition: background 0.2s;
  
  &:hover {
    background: rgba(255, 255, 255, 0.03);
  }
  
  .header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }
  
  .header-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }
  
  .status-icon {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 0.75rem;
    background: rgba(255, 255, 255, 0.05);
    color: $text-muted;
    
    &.filled {
      background: $success;
      color: white;
    }
  }
  
  .field-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: $text-primary;
  }
  
  .required-badge {
    color: $warning;
    margin-left: 0.25rem;
  }
  
  .items-count {
    font-size: 0.75rem;
    color: $text-secondary;
    background: rgba(255, 255, 255, 0.05);
    padding: 0.25rem 0.5rem;
    border-radius: 1rem;
  }
  
  .field-xp {
    font-size: 0.6875rem;
    color: $warning;
    font-weight: 600;
  }
  
  .expand-icon {
    font-size: 0.625rem;
    color: $text-muted;
    transition: transform 0.2s;
  }
}

// Content
.field-content {
  padding: 0 1rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

// Item Box (Service, FAQ, Phone)
.item-box {
  background: rgba(0, 0, 0, 0.3);
  border-radius: 10px;
  padding: 0.875rem;
  border: 1px solid rgba(255, 255, 255, 0.05);
  
  .item-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    
    .item-number {
      font-size: 0.75rem;
      font-weight: 600;
      color: $primary-light;
    }
    
    .btn-remove {
      width: 24px;
      height: 24px;
      border-radius: 6px;
      background: rgba(231, 76, 60, 0.15);
      border: none;
      color: #e74c3c;
      font-size: 0.75rem;
      cursor: pointer;
      transition: all 0.2s;
      
      &:hover {
        background: rgba(231, 76, 60, 0.3);
      }
    }
  }
  
  .item-fields {
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
    
    &.inline {
      flex-direction: row;
      gap: 0.75rem;
    }
  }
}

// Field Groups
.field-group {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  
  &.flex-1 {
    flex: 1;
  }
  
  label {
    font-size: 0.6875rem;
    color: $text-secondary;
    text-transform: uppercase;
    letter-spacing: 0.03em;
  }
  
  input, textarea {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid $border-color;
    border-radius: 8px;
    padding: 0.625rem 0.75rem;
    font-size: 0.8125rem;
    color: $text-primary;
    transition: all 0.2s;
    
    &:focus {
      outline: none;
      border-color: $primary;
      background: rgba($primary, 0.05);
    }
    
    &::placeholder {
      color: $text-muted;
    }
  }
  
  textarea {
    resize: vertical;
    min-height: 60px;
  }
}

// Image Upload
.field-image {
  .image-upload-box {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    
    .image-preview {
      width: 48px;
      height: 48px;
      border-radius: 8px;
      object-fit: cover;
      border: 1px solid $border-color;
    }
    
    .btn-upload-image {
      padding: 0.5rem 0.75rem;
      background: rgba($primary, 0.15);
      border: 1px solid rgba($primary, 0.3);
      border-radius: 6px;
      font-size: 0.75rem;
      color: $text-primary;
      cursor: pointer;
      transition: all 0.2s;
      
      &:hover {
        background: rgba($primary, 0.25);
      }
    }
    
    .btn-remove-image {
      width: 24px;
      height: 24px;
      border-radius: 6px;
      background: rgba(231, 76, 60, 0.15);
      border: none;
      color: #e74c3c;
      font-size: 0.625rem;
      cursor: pointer;
      
      &:hover {
        background: rgba(231, 76, 60, 0.3);
      }
    }
  }
}

// Add Button
.btn-add-item {
  width: 100%;
  padding: 0.75rem;
  background: transparent;
  border: 1px dashed rgba($primary, 0.4);
  border-radius: 10px;
  font-size: 0.8125rem;
  font-weight: 500;
  color: $primary-light;
  cursor: pointer;
  transition: all 0.2s;
  
  &:hover {
    background: rgba($primary, 0.1);
    border-color: $primary;
  }
}

.hidden {
  display: none;
}
</style>
