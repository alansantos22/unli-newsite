<template>
  <teleport to="body">
    <transition name="loading" appear>
      <div v-if="visible" class="fullscreen-loading">
        <div class="loading-overlay"></div>
        <div class="loading-content">
          <div class="loading-spinner">
            <div class="spinner-ring"></div>
            <div class="spinner-inner">
              <i class="fas fa-credit-card"></i>
            </div>
          </div>
          
          <div class="loading-text">
            <h3 class="loading-title">{{ title || 'Processando...' }}</h3>
            <p class="loading-subtitle">{{ subtitle || 'Aguarde enquanto preparamos tudo para você.' }}</p>
          </div>
          
          <div class="loading-progress">
            <div class="progress-bar">
              <div class="progress-fill" :style="{ width: progress + '%' }"></div>
            </div>
            <p class="progress-text">{{ progressText }}</p>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script>
export default {
  name: 'FullScreenLoading',
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: 'Processando pagamento...'
    },
    subtitle: {
      type: String,
      default: 'Redirecionando para o gateway de pagamento seguro.'
    }
  },
  data() {
    return {
      progress: 0,
      progressText: 'Validando dados...',
      progressSteps: [
        { progress: 20, text: 'Validando dados...' },
        { progress: 40, text: 'Criando preferência de pagamento...' },
        { progress: 60, text: 'Conectando com Mercado Pago...' },
        { progress: 80, text: 'Preparando redirecionamento...' },
        { progress: 100, text: 'Redirecionando...' }
      ],
      currentStep: 0,
      progressInterval: null
    };
  },
  watch: {
    visible(newVal) {
      if (newVal) {
        this.startProgressAnimation();
      } else {
        this.resetProgress();
      }
    }
  },
  methods: {
    startProgressAnimation() {
      this.progress = 0;
      this.currentStep = 0;
      this.progressText = this.progressSteps[0].text;
      
      this.progressInterval = setInterval(() => {
        if (this.currentStep < this.progressSteps.length - 1) {
          this.currentStep++;
          const step = this.progressSteps[this.currentStep];
          this.progress = step.progress;
          this.progressText = step.text;
        } else {
          // Parar no último step para dar sensação de "carregando"
          clearInterval(this.progressInterval);
        }
      }, 800); // 800ms entre cada step
    },
    
    resetProgress() {
      if (this.progressInterval) {
        clearInterval(this.progressInterval);
        this.progressInterval = null;
      }
      this.progress = 0;
      this.currentStep = 0;
      this.progressText = 'Validando dados...';
    },
    
    forceComplete() {
      // Método para forçar conclusão imediata (útil para debugging)
      this.progress = 100;
      this.progressText = 'Redirecionando...';
      this.resetProgress();
    }
  },
  beforeUnmount() {
    this.resetProgress();
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/_colors.scss';

// ==========================================
// FULLSCREEN LOADING COMPONENT
// ==========================================

.fullscreen-loading {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 99999;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(74, 144, 226, 0.95), rgba(80, 39, 224, 0.95));
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.loading-content {
  position: relative;
  z-index: 2;
  text-align: center;
  padding: 40px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 24px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
  max-width: 400px;
  width: 90%;
  
  @media (max-width: 480px) {
    padding: 30px 20px;
    border-radius: 16px;
  }
}

// ==========================================
// SPINNER ANIMATION
// ==========================================
.loading-spinner {
  position: relative;
  width: 80px;
  height: 80px;
  margin: 0 auto 32px;
}

.spinner-ring {
  position: absolute;
  width: 100%;
  height: 100%;
  border: 3px solid rgba(255, 255, 255, 0.2);
  border-top: 3px solid #ffffff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.spinner-inner {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  
  i {
    font-size: 2rem;
    color: #ffffff;
    animation: pulse 1.5s ease-in-out infinite;
  }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes pulse {
  0%, 100% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
  50% { opacity: 0.7; transform: translate(-50%, -50%) scale(1.1); }
}

// ==========================================
// TEXT CONTENT
// ==========================================
.loading-text {
  margin-bottom: 32px;
}

.loading-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 8px;
  letter-spacing: -0.02em;
  
  @media (max-width: 480px) {
    font-size: 1.3rem;
  }
}

.loading-subtitle {
  font-size: 0.95rem;
  color: rgba(255, 255, 255, 0.8);
  margin: 0;
  line-height: 1.5;
  
  @media (max-width: 480px) {
    font-size: 0.9rem;
  }
}

// ==========================================
// PROGRESS BAR
// ==========================================
.loading-progress {
  margin-top: 24px;
}

.progress-bar {
  width: 100%;
  height: 4px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 2px;
  overflow: hidden;
  margin-bottom: 12px;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #ffffff, rgba(255, 255, 255, 0.8));
  border-radius: 2px;
  transition: width 0.8s ease-out;
  box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
}

.progress-text {
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
  font-weight: 500;
  
  @media (max-width: 480px) {
    font-size: 0.8rem;
  }
}

// ==========================================
// TRANSITIONS
// ==========================================
.loading-enter-active,
.loading-leave-active {
  transition: all 0.4s ease-out;
}

.loading-enter-from {
  opacity: 0;
  transform: scale(0.9);
}

.loading-leave-to {
  opacity: 0;
  transform: scale(0.9);
}

.loading-enter-active .loading-content,
.loading-leave-active .loading-content {
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.loading-enter-from .loading-content {
  transform: scale(0.8) translateY(20px);
  opacity: 0;
}

.loading-leave-to .loading-content {
  transform: scale(0.8) translateY(-20px);
  opacity: 0;
}
</style>