<template>
  <div class="image-upload-btn">
    <input
      type="file"
      ref="fileInput"
      accept="image/*"
      class="hidden"
      @change="handleFileSelect"
    />
    
    <button 
      class="upload-trigger"
      :class="{ 'is-uploading': isUploading }"
      @click="$refs.fileInput.click()"
      :disabled="isUploading"
    >
      <span v-if="isUploading" class="loading-spinner"></span>
      <span v-else class="upload-icon">📷</span>
      <span class="upload-text">{{ isUploading ? 'Enviando...' : 'Enviar Imagem' }}</span>
    </button>
    
    <!-- Preview -->
    <div v-if="previewUrl" class="upload-preview">
      <img :src="previewUrl" alt="Preview" />
      <button class="btn-remove" @click="clearPreview">✕</button>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';

export default {
  name: 'ImageUploadButton',
  
  props: {
    fieldId: {
      type: String,
      required: true
    },
    uploadEndpoint: {
      type: String,
      default: '/api/upload/image.php'
    },
    maxSizeMB: {
      type: Number,
      default: 5
    }
  },
  
  emits: ['upload'],
  
  setup(props, { emit }) {
    const fileInput = ref(null);
    const isUploading = ref(false);
    const previewUrl = ref(null);
    
    async function handleFileSelect(event) {
      const file = event.target.files?.[0];
      if (!file) return;
      
      // Validar tipo
      if (!file.type.startsWith('image/')) {
        alert('Por favor, selecione uma imagem.');
        return;
      }
      
      // Validar tamanho
      const maxSize = props.maxSizeMB * 1024 * 1024;
      if (file.size > maxSize) {
        alert(`Imagem muito grande. Máximo ${props.maxSizeMB}MB.`);
        return;
      }
      
      // Mostrar preview
      previewUrl.value = URL.createObjectURL(file);
      
      // Fazer upload
      isUploading.value = true;
      
      try {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('field', props.fieldId);
        
        const response = await fetch(props.uploadEndpoint, {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success && result.url) {
          emit('upload', {
            fieldId: props.fieldId,
            url: result.url,
            file
          });
        } else {
          // Fallback para base64
          const base64 = await fileToBase64(file);
          emit('upload', {
            fieldId: props.fieldId,
            url: base64,
            file
          });
        }
      } catch (error) {
        console.error('[Upload Error]', error);
        
        // Fallback para base64
        const base64 = await fileToBase64(file);
        emit('upload', {
          fieldId: props.fieldId,
          url: base64,
          file
        });
      } finally {
        isUploading.value = false;
      }
      
      // Limpar input
      if (fileInput.value) {
        fileInput.value.value = '';
      }
    }
    
    function fileToBase64(file) {
      return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = reject;
        reader.readAsDataURL(file);
      });
    }
    
    function clearPreview() {
      previewUrl.value = null;
      if (fileInput.value) {
        fileInput.value.value = '';
      }
    }
    
    return {
      fileInput,
      isUploading,
      previewUrl,
      handleFileSelect,
      clearPreview
    };
  }
};
</script>

<style lang="scss" scoped>
$primary: #6C5CE7;
$text-primary: #ffffff;
$text-secondary: rgba(255, 255, 255, 0.7);
$border-color: rgba(255, 255, 255, 0.1);
$error: #E17055;

.image-upload-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  
  .hidden {
    display: none;
  }
}

.upload-trigger {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: rgba($primary, 0.2);
  border: 1px solid rgba($primary, 0.3);
  border-radius: 2rem;
  color: $text-primary;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
  
  &:hover:not(:disabled) {
    background: rgba($primary, 0.3);
  }
  
  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
  
  &.is-uploading {
    .upload-icon {
      display: none;
    }
  }
  
  .upload-icon {
    font-size: 1rem;
  }
  
  .loading-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid transparent;
    border-top-color: $text-primary;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
  }
}

.upload-preview {
  position: relative;
  
  img {
    width: 48px;
    height: 48px;
    object-fit: cover;
    border-radius: 0.5rem;
    border: 2px solid rgba($primary, 0.3);
  }
  
  .btn-remove {
    position: absolute;
    top: -6px;
    right: -6px;
    width: 20px;
    height: 20px;
    background: $error;
    border: none;
    border-radius: 50%;
    color: white;
    font-size: 0.75rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    
    &:hover {
      filter: brightness(1.1);
    }
  }
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
