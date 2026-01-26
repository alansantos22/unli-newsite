<template>
  <div class="pdf-uploader">
    <div class="uploader-header" v-if="label">
      <label>{{ label }}</label>
      <span v-if="required" class="required">*</span>
    </div>

    <div class="uploaded-pdfs" v-if="uploadedFiles.length > 0">
      <div
        v-for="(file, index) in uploadedFiles"
        :key="index"
        class="pdf-preview-item"
      >
        <div class="pdf-icon-container">
          <div class="pdf-icon">📄</div>
          <div class="pdf-badge">PDF</div>
        </div>
        <div class="pdf-info">
          <span class="file-name" :title="file.name">{{ file.name }}</span>
          <span class="file-size">{{ formatFileSize(file.size) }}</span>
          <span v-if="file.pages" class="pdf-pages">{{ file.pages }} página{{ file.pages > 1 ? 's' : '' }}</span>
        </div>
        <button
          @click="removeFile(index)"
          class="btn-remove"
          type="button"
          title="Remover PDF"
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
        accept="application/pdf"
        :multiple="multiple"
        @change="handleFileSelect"
        @drop.prevent="handleDrop"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        hidden
      />
      <label :for="inputId" class="upload-label">
        <div class="upload-icon">📑</div>
        <span class="upload-text">
          {{ uploadText || 'Clique ou arraste PDFs aqui' }}
        </span>
        <small class="upload-hint">
          Apenas arquivos PDF (máx. {{ maxSizeMB }}MB{{ multiple ? ' cada' : '' }})
        </small>
        <small v-if="multiple" class="upload-hint">
          Você pode selecionar múltiplos PDFs
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
  name: 'PDFUploader',
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
      default: 10
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
      inputId: `pdf-upload-${Math.random().toString(36).substr(2, 9)}`
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
      const pdfFiles = files.filter(file => file.type === 'application/pdf');
      
      if (pdfFiles.length !== files.length) {
        this.error = 'Alguns arquivos não são PDFs e foram ignorados.';
        setTimeout(() => this.error = null, 3000);
      }
      
      this.processFiles(pdfFiles);
    },
    async processFiles(files) {
      this.error = null;
      
      if (!this.multiple && files.length > 1) {
        this.error = 'Selecione apenas um PDF.';
        return;
      }

      const maxSize = this.maxSizeMB * 1024 * 1024;
      const validFiles = [];

      for (const file of files) {
        if (file.type !== 'application/pdf') {
          this.error = `${file.name} não é um PDF válido.`;
          continue;
        }

        if (file.size > maxSize) {
          this.error = `${file.name} excede o tamanho máximo de ${this.maxSizeMB}MB.`;
          continue;
        }

        validFiles.push(file);
      }

      if (validFiles.length === 0) return;

      // Create file objects with metadata
      const fileObjects = await Promise.all(
        validFiles.map(async (file) => {
          const pages = await this.getPDFPages(file);
          return {
            file: file,
            name: file.name,
            size: file.size,
            pages: pages,
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
    async getPDFPages(file) {
      // Simple page count estimation (not accurate, just for display)
      // For accurate page count, you'd need to use a PDF library like pdf.js
      try {
        return Math.ceil(file.size / 50000); // Rough estimate
      } catch (error) {
        return null;
      }
    },
    async uploadFiles(fileObjects) {
      this.uploading = true;
      this.uploadProgress = 0;

      try {
        const formData = new FormData();
        fileObjects.forEach((fileObj) => {
          formData.append(`pdfs[]`, fileObj.file);
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
              this.error = response.message || 'Erro ao fazer upload dos PDFs.';
              this.$emit('upload-error', this.error);
            }
          } else {
            this.error = 'Erro ao fazer upload dos PDFs.';
            this.$emit('upload-error', this.error);
          }
        });

        xhr.addEventListener('error', () => {
          this.uploading = false;
          this.error = 'Erro de conexão ao fazer upload dos PDFs.';
          this.$emit('upload-error', this.error);
        });

        xhr.open('POST', '/api/upload/pdfs.php');
        xhr.send(formData);

      } catch (err) {
        this.uploading = false;
        this.error = 'Erro ao fazer upload dos PDFs.';
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
.pdf-uploader {
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

.uploaded-pdfs {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 16px;
}

.pdf-preview-item {
  position: relative;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
  background: #f7fafc;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  padding: 16px;
  gap: 12px;
}

.pdf-preview-item:hover {
  border-color: #00d4aa;
  box-shadow: 0 4px 12px rgba(0, 212, 170, 0.15);
}

.pdf-icon-container {
  position: relative;
  flex-shrink: 0;
}

.pdf-icon {
  font-size: 48px;
  opacity: 0.7;
}

.pdf-badge {
  position: absolute;
  bottom: -4px;
  right: -4px;
  background: #e53e3e;
  color: white;
  font-size: 9px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 4px;
  letter-spacing: 0.5px;
}

.pdf-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 0;
}

.file-name {
  font-size: 13px;
  font-weight: 600;
  color: #2d3748;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.file-size,
.pdf-pages {
  font-size: 11px;
  color: #718096;
}

.btn-remove {
  flex-shrink: 0;
  width: 28px;
  height: 28px;
  border: none;
  border-radius: 50%;
  background: rgba(229, 62, 62, 0.1);
  color: #e53e3e;
  font-size: 16px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.btn-remove:hover {
  background: #e53e3e;
  color: white;
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
  background: linear-gradient(90deg, #e53e3e 0%, #c53030 100%);
  transition: width 0.3s ease;
}

.progress-text {
  font-size: 13px;
  color: #718096;
  text-align: center;
  display: block;
}

@media (max-width: 768px) {
  .uploaded-pdfs {
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .pdf-preview-item {
    padding: 12px;
  }

  .pdf-icon {
    font-size: 36px;
  }

  .upload-area {
    padding: 24px 16px;
  }

  .upload-icon {
    font-size: 36px;
  }
}
</style>
