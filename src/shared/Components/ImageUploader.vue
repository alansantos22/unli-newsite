<template>
  <div class="image-uploader">
    <div class="uploader-header" v-if="label">
      <label>{{ label }}</label>
      <span v-if="required" class="required">*</span>
    </div>

    <div class="uploaded-images" v-if="uploadedFiles.length > 0">
      <div
        v-for="(file, index) in uploadedFiles"
        :key="index"
        class="image-preview-item"
      >
        <img :src="file.preview" :alt="file.name" />
        <div class="image-info">
          <span class="file-name">{{ file.name }}</span>
          <span class="file-size">{{ formatFileSize(file.size) }}</span>
        </div>
        <button
          @click="removeFile(index)"
          class="btn-remove"
          type="button"
          title="Remover imagem"
        >
          ✕
        </button>
      </div>
    </div>

    <div class="upload-area" :class="{ 'drag-over': isDragging }">
      <input
        :id="inputId"
        ref="fileInput"
        type="file"
        accept="image/png, image/jpeg, image/jpg, image/gif, image/webp, image/svg+xml"
        :multiple="multiple"
        @change="handleFileSelect"
        @drop.prevent="handleDrop"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        hidden
      />
      <label :for="inputId" class="upload-label">
        <div class="upload-icon">📷</div>
        <span class="upload-text">
          {{ uploadText || 'Clique ou arraste imagens aqui' }}
        </span>
        <small class="upload-hint">
          PNG, JPG, GIF, SVG ou WebP (máx. {{ maxSizeMB }}MB{{ multiple ? ' cada' : '' }})
        </small>
        <small v-if="multiple" class="upload-hint">
          Você pode selecionar múltiplas imagens
        </small>
      </label>
    </div>

    <div v-if="error" class="upload-error">
      {{ error }}
    </div>

    <div v-if="uploading" class="upload-progress">
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: `${uploadProgress}%` }"></div>
      </div>
      <span class="progress-text">Enviando... {{ uploadProgress }}%</span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ImageUploader',
  props: {
    label: {
      type: String,
      default: ''
    },
    required: {
      type: Boolean,
      default: false
    },
    multiple: {
      type: Boolean,
      default: false
    },
    maxSizeMB: {
      type: Number,
      default: 5
    },
    uploadText: {
      type: String,
      default: ''
    },
    modelValue: {
      type: Array,
      default: () => []
    },
    companyName: {
      type: String,
      default: ''
    }
  },
  emits: ['update:modelValue', 'files-added', 'file-removed', 'upload-complete', 'upload-error'],
  data() {
    return {
      uploadedFiles: [],
      isDragging: false,
      error: null,
      uploading: false,
      uploadProgress: 0,
      inputId: `image-upload-${Math.random().toString(36).substr(2, 9)}`
    };
  },
  watch: {
    modelValue: {
      immediate: true,
      handler(newValue) {
        if (newValue && newValue.length > 0 && newValue !== this.uploadedFiles) {
          this.uploadedFiles = [...newValue];
        }
      }
    }
  },
  methods: {
    handleFileSelect(event) {
      const files = Array.from(event.target.files);
      this.processFiles(files);
      // Reset input
      event.target.value = '';
    },
    handleDrop(event) {
      this.isDragging = false;
      const files = Array.from(event.dataTransfer.files);
      const imageFiles = files.filter(file => file.type.startsWith('image/'));
      
      if (imageFiles.length !== files.length) {
        this.error = 'Alguns arquivos não são imagens e foram ignorados.';
        setTimeout(() => this.error = null, 3000);
      }
      
      this.processFiles(imageFiles);
    },
    async processFiles(files) {
      this.error = null;
      
      if (!this.multiple && files.length > 1) {
        this.error = 'Selecione apenas uma imagem.';
        return;
      }

      const maxSize = this.maxSizeMB * 1024 * 1024;
      const validFiles = [];

      for (const file of files) {
        if (!file.type.startsWith('image/')) {
          this.error = `${file.name} não é uma imagem válida.`;
          continue;
        }

        if (file.size > maxSize) {
          this.error = `${file.name} excede o tamanho máximo de ${this.maxSizeMB}MB.`;
          continue;
        }

        validFiles.push(file);
      }

      if (validFiles.length === 0) return;

      // Create preview URLs
      const fileObjects = await Promise.all(
        validFiles.map(async (file) => {
          const preview = await this.createPreview(file);
          return {
            file: file,
            name: file.name,
            size: file.size,
            preview: preview,
            uploaded: false
          };
        })
      );

      if (this.multiple) {
        this.uploadedFiles.push(...fileObjects);
      } else {
        this.uploadedFiles = fileObjects;
      }

      this.$emit('update:modelValue', this.uploadedFiles);
      this.$emit('files-added', fileObjects);
      
      // Upload files to server
      await this.uploadFiles(fileObjects);
    },
    createPreview(file) {
      return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => resolve(e.target.result);
        reader.readAsDataURL(file);
      });
    },
    async uploadFiles(fileObjects) {
      this.uploading = true;
      this.uploadProgress = 0;

      try {
        const formData = new FormData();
        fileObjects.forEach((fileObj) => {
          formData.append(`images[]`, fileObj.file);
        });
        
        // Adicionar nome da empresa para organização dos arquivos
        if (this.companyName) {
          formData.append('company_name', this.companyName);
        }

        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', (e) => {
          if (e.lengthComputable) {
            this.uploadProgress = Math.round((e.loaded / e.total) * 100);
          }
        });

        xhr.addEventListener('load', () => {
          this.uploading = false;
          if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
              // Update file objects with server URLs
              fileObjects.forEach((fileObj, index) => {
                if (response.files[index]) {
                  fileObj.url = response.files[index].url;
                  fileObj.uploaded = true;
                }
              });
              this.$emit('upload-complete', response.files);
            } else {
              this.error = response.message || 'Erro ao fazer upload das imagens.';
              this.$emit('upload-error', this.error);
            }
          } else {
            this.error = 'Erro ao fazer upload das imagens.';
            this.$emit('upload-error', this.error);
          }
        });

        xhr.addEventListener('error', () => {
          this.uploading = false;
          this.error = 'Erro de conexão ao fazer upload das imagens.';
          this.$emit('upload-error', this.error);
        });

        xhr.open('POST', '/api/upload/images.php');
        xhr.send(formData);

      } catch (err) {
        this.uploading = false;
        this.error = 'Erro ao fazer upload das imagens.';
        this.$emit('upload-error', err);
      }
    },
    removeFile(index) {
      const removedFile = this.uploadedFiles[index];
      this.uploadedFiles.splice(index, 1);
      this.$emit('update:modelValue', this.uploadedFiles);
      this.$emit('file-removed', removedFile);
    },
    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    },
    clearFiles() {
      this.uploadedFiles = [];
      this.$emit('update:modelValue', []);
    }
  }
};
</script>

<style scoped>
.image-uploader {
  width: 100%;
}

.uploader-header {
  margin-bottom: 12px;
}

.uploader-header label {
  font-size: 14px;
  font-weight: 600;
  color: #2d3748;
}

.required {
  color: #e53e3e;
  margin-left: 4px;
}

.uploaded-images {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 16px;
  margin-bottom: 16px;
}

.image-preview-item {
  position: relative;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
  background: #f7fafc;
  transition: all 0.3s ease;
}

.image-preview-item:hover {
  border-color: #00d4aa;
  box-shadow: 0 4px 12px rgba(0, 212, 170, 0.15);
}

.image-preview-item img {
  width: 100%;
  height: 150px;
  object-fit: cover;
}

.image-info {
  padding: 8px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.file-name {
  font-size: 12px;
  font-weight: 500;
  color: #2d3748;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.file-size {
  font-size: 11px;
  color: #718096;
}

.btn-remove {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 28px;
  height: 28px;
  border: none;
  border-radius: 50%;
  background: rgba(229, 62, 62, 0.9);
  color: white;
  font-size: 16px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  opacity: 0;
}

.image-preview-item:hover .btn-remove {
  opacity: 1;
}

.btn-remove:hover {
  background: #c53030;
  transform: scale(1.1);
}

.upload-area {
  border: 2px dashed #cbd5e0;
  border-radius: 12px;
  padding: 32px;
  text-align: center;
  transition: all 0.3s ease;
  background: #f7fafc;
}

.upload-area:hover,
.upload-area.drag-over {
  border-color: #00d4aa;
  background: rgba(0, 212, 170, 0.05);
}

.upload-label {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  cursor: pointer;
}

.upload-icon {
  font-size: 48px;
  opacity: 0.6;
}

.upload-text {
  font-size: 16px;
  font-weight: 600;
  color: #2d3748;
}

.upload-hint {
  font-size: 13px;
  color: #718096;
  display: block;
}

.upload-error {
  margin-top: 12px;
  padding: 12px;
  background: #fed7d7;
  color: #c53030;
  border-radius: 8px;
  font-size: 14px;
}

.upload-progress {
  margin-top: 16px;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background: #e2e8f0;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 8px;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #00d4aa 0%, #00b894 100%);
  transition: width 0.3s ease;
}

.progress-text {
  font-size: 13px;
  color: #718096;
  text-align: center;
  display: block;
}

@media (max-width: 768px) {
  .uploaded-images {
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 12px;
  }

  .image-preview-item img {
    height: 120px;
  }

  .upload-area {
    padding: 24px 16px;
  }

  .upload-icon {
    font-size: 36px;
  }
}
</style>
