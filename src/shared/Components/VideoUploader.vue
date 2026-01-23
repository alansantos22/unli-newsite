<template>
  <div class="video-uploader">
    <div class="uploader-header" v-if="label">
      <label>{{ label }}</label>
      <span v-if="required" class="required">*</span>
    </div>

    <div class="uploaded-videos" v-if="uploadedFiles.length > 0">
      <div
        v-for="(file, index) in uploadedFiles"
        :key="index"
        class="video-preview-item"
      >
        <div class="video-thumbnail">
          <video v-if="file.preview" :src="file.preview" class="video-preview"></video>
          <div v-else class="video-icon">🎬</div>
          <div class="play-overlay">▶</div>
        </div>
        <div class="video-info">
          <span class="file-name">{{ file.name }}</span>
          <span class="file-size">{{ formatFileSize(file.size) }}</span>
          <span v-if="file.duration" class="video-duration">{{ formatDuration(file.duration) }}</span>
        </div>
        <button
          @click="removeFile(index)"
          class="btn-remove"
          type="button"
          title="Remover vídeo"
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
        accept="video/mp4, video/webm, video/ogg, video/quicktime, video/x-msvideo"
        :multiple="multiple"
        @change="handleFileSelect"
        @drop.prevent="handleDrop"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        hidden
      />
      <label :for="inputId" class="upload-label">
        <div class="upload-icon">🎥</div>
        <span class="upload-text">
          {{ uploadText || 'Clique ou arraste vídeos aqui' }}
        </span>
        <small class="upload-hint">
          MP4, WebM, OGG, MOV ou AVI (máx. {{ maxSizeMB }}MB{{ multiple ? ' cada' : '' }})
        </small>
        <small v-if="multiple" class="upload-hint">
          Você pode selecionar múltiplos vídeos
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
  name: 'VideoUploader',
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
      default: 100
    },
    uploadText: {
      type: String,
      default: ''
    },
    modelValue: {
      type: Array,
      default: () => []
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
      inputId: `video-upload-${Math.random().toString(36).substr(2, 9)}`
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
      const videoFiles = files.filter(file => file.type.startsWith('video/'));
      
      if (videoFiles.length !== files.length) {
        this.error = 'Alguns arquivos não são vídeos e foram ignorados.';
        setTimeout(() => this.error = null, 3000);
      }
      
      this.processFiles(videoFiles);
    },
    async processFiles(files) {
      this.error = null;
      
      if (!this.multiple && files.length > 1) {
        this.error = 'Selecione apenas um vídeo.';
        return;
      }

      const maxSize = this.maxSizeMB * 1024 * 1024;
      const validFiles = [];

      for (const file of files) {
        if (!file.type.startsWith('video/')) {
          this.error = `${file.name} não é um vídeo válido.`;
          continue;
        }

        if (file.size > maxSize) {
          this.error = `${file.name} excede o tamanho máximo de ${this.maxSizeMB}MB.`;
          continue;
        }

        validFiles.push(file);
      }

      if (validFiles.length === 0) return;

      // Create preview URLs and get metadata
      const fileObjects = await Promise.all(
        validFiles.map(async (file) => {
          const preview = await this.createPreview(file);
          const duration = await this.getVideoDuration(file);
          return {
            file: file,
            name: file.name,
            size: file.size,
            preview: preview,
            duration: duration,
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
        const url = URL.createObjectURL(file);
        resolve(url);
      });
    },
    getVideoDuration(file) {
      return new Promise((resolve) => {
        const video = document.createElement('video');
        video.preload = 'metadata';
        video.onloadedmetadata = function() {
          window.URL.revokeObjectURL(video.src);
          resolve(video.duration);
        };
        video.onerror = function() {
          resolve(null);
        };
        video.src = URL.createObjectURL(file);
      });
    },
    async uploadFiles(fileObjects) {
      this.uploading = true;
      this.uploadProgress = 0;

      try {
        const formData = new FormData();
        fileObjects.forEach((fileObj) => {
          formData.append(`videos[]`, fileObj.file);
        });

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
              this.error = response.message || 'Erro ao fazer upload dos vídeos.';
              this.$emit('upload-error', this.error);
            }
          } else {
            this.error = 'Erro ao fazer upload dos vídeos.';
            this.$emit('upload-error', this.error);
          }
        });

        xhr.addEventListener('error', () => {
          this.uploading = false;
          this.error = 'Erro de conexão ao fazer upload dos vídeos.';
          this.$emit('upload-error', this.error);
        });

        xhr.open('POST', '/api/upload/videos.php');
        xhr.send(formData);

      } catch (err) {
        this.uploading = false;
        this.error = 'Erro ao fazer upload dos vídeos.';
        this.$emit('upload-error', err);
      }
    },
    removeFile(index) {
      const removedFile = this.uploadedFiles[index];
      // Revoke object URL to free memory
      if (removedFile.preview) {
        URL.revokeObjectURL(removedFile.preview);
      }
      this.uploadedFiles.splice(index, 1);
      this.$emit('update:modelValue', this.uploadedFiles);
      this.$emit('file-removed', removedFile);
    },
    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    },
    formatDuration(seconds) {
      if (!seconds) return '';
      const mins = Math.floor(seconds / 60);
      const secs = Math.floor(seconds % 60);
      return `${mins}:${secs.toString().padStart(2, '0')}`;
    },
    clearFiles() {
      // Revoke all object URLs
      this.uploadedFiles.forEach(file => {
        if (file.preview) {
          URL.revokeObjectURL(file.preview);
        }
      });
      this.uploadedFiles = [];
      this.$emit('update:modelValue', []);
    }
  },
  beforeUnmount() {
    // Clean up object URLs
    this.uploadedFiles.forEach(file => {
      if (file.preview) {
        URL.revokeObjectURL(file.preview);
      }
    });
  }
};
</script>

<style scoped>
.video-uploader {
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

.uploaded-videos {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 16px;
}

.video-preview-item {
  position: relative;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
  background: #f7fafc;
  transition: all 0.3s ease;
}

.video-preview-item:hover {
  border-color: #00d4aa;
  box-shadow: 0 4px 12px rgba(0, 212, 170, 0.15);
}

.video-thumbnail {
  position: relative;
  width: 100%;
  height: 150px;
  background: #1a202c;
  display: flex;
  align-items: center;
  justify-content: center;
}

.video-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.video-icon {
  font-size: 48px;
  opacity: 0.5;
}

.play-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 50px;
  height: 50px;
  background: rgba(0, 212, 170, 0.9);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 20px;
  padding-left: 4px;
  opacity: 0.8;
  transition: all 0.3s ease;
}

.video-preview-item:hover .play-overlay {
  opacity: 1;
  transform: translate(-50%, -50%) scale(1.1);
}

.video-info {
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.file-name {
  font-size: 13px;
  font-weight: 500;
  color: #2d3748;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.file-size,
.video-duration {
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
  z-index: 10;
}

.video-preview-item:hover .btn-remove {
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
  background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
  transition: width 0.3s ease;
}

.progress-text {
  font-size: 13px;
  color: #718096;
  text-align: center;
  display: block;
}

@media (max-width: 768px) {
  .uploaded-videos {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
  }

  .video-thumbnail {
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
