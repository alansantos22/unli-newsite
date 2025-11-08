<template>
  <section class="contact-section">
    <div class="contact-container">
      <div class="contact-content" ref="contentRef" :class="{ 'animate-in': contentVisible }">
        <!-- Left Side: Info -->
        <div class="contact-info">
          <h2 class="contact-title">{{ title }}</h2>
          <p class="contact-description">{{ description }}</p>

          <!-- Contact Items -->
          <div class="contact-items" v-if="contactItems.length > 0">
            <div 
              v-for="(item, index) in contactItems" 
              :key="index"
              class="contact-item"
            >
              <div class="item-icon" :style="{ background: item.iconBg || iconBgDefault }">
                <i :class="item.icon"></i>
              </div>
              <div class="item-content">
                <div class="item-label">{{ item.label }}</div>
                <div class="item-value">{{ item.value }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side: Form -->
        <div class="contact-form">
          <form @submit.prevent="handleSubmit">
            <!-- Name -->
            <div class="form-group">
              <label for="name" class="form-label">Nome</label>
              <input 
                id="name"
                v-model="formData.name"
                type="text" 
                class="form-input"
                placeholder="Seu nome completo"
                required
              />
            </div>

            <!-- Email -->
            <div class="form-group">
              <label for="email" class="form-label">E-mail</label>
              <input 
                id="email"
                v-model="formData.email"
                type="email" 
                class="form-input"
                placeholder="seu@email.com"
                required
              />
            </div>

            <!-- Phone (optional) -->
            <div class="form-group" v-if="includePhone">
              <label for="phone" class="form-label">Telefone</label>
              <input 
                id="phone"
                v-model="formData.phone"
                type="tel" 
                class="form-input"
                placeholder="(00) 00000-0000"
              />
            </div>

            <!-- Message -->
            <div class="form-group">
              <label for="message" class="form-label">Mensagem</label>
              <textarea 
                id="message"
                v-model="formData.message"
                class="form-textarea"
                rows="4"
                placeholder="Conte-nos sobre seu projeto..."
                required
              ></textarea>
            </div>

            <!-- Submit Button -->
            <button 
              type="submit" 
              class="form-submit"
              :disabled="isSubmitting"
            >
              <span v-if="!isSubmitting">{{ submitText }}</span>
              <span v-else>Enviando...</span>
              <svg v-if="!isSubmitting" class="submit-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>

            <!-- Success/Error Messages -->
            <div v-if="submitStatus" class="submit-message" :class="submitStatus">
              {{ submitMessage }}
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Background Shapes -->
    <div class="contact-shapes">
      <div class="shape shape-1"></div>
      <div class="shape shape-2"></div>
    </div>
  </section>
</template>

<script>
import { ref, reactive } from 'vue';
import { useIntersectionObserver } from '@/core/composables/useIntersectionObserver';

export default {
  name: 'ContactSection',
  props: {
    title: {
      type: String,
      default: 'Share Your Idea + Let\'s Collaborate',
    },
    description: {
      type: String,
      default: 'Looking for exciting ideas or need help transforming content that drives action.',
    },
    contactItems: {
      type: Array,
      default: () => [],
      // Example: [{ icon: '📧', label: 'Email', value: 'contact@unli.com', iconBg: '#e67e22' }]
    },
    iconBgDefault: {
      type: String,
      default: 'linear-gradient(135deg, #e67e22 0%, #d35400 100%)',
    },
    includePhone: {
      type: Boolean,
      default: true,
    },
    submitText: {
      type: String,
      default: 'Enviar Mensagem',
    },
  },
  emits: ['submit'],
  setup(props, { emit }) {
    const { targetRef: contentRef, isVisible: contentVisible } = useIntersectionObserver({
      threshold: 0.2,
      triggerOnce: true,
    });

    const formData = reactive({
      name: '',
      email: '',
      phone: '',
      message: '',
    });

    const isSubmitting = ref(false);
    const submitStatus = ref(''); // 'success' or 'error'
    const submitMessage = ref('');

    const handleSubmit = async () => {
      isSubmitting.value = true;
      submitStatus.value = '';
      submitMessage.value = '';

      try {
        // Emit form data to parent
        emit('submit', { ...formData });

        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1500));

        // Success
        submitStatus.value = 'success';
        submitMessage.value = 'Mensagem enviada com sucesso! Entraremos em contato em breve.';
        
        // Reset form
        formData.name = '';
        formData.email = '';
        formData.phone = '';
        formData.message = '';

      } catch (error) {
        submitStatus.value = 'error';
        submitMessage.value = 'Erro ao enviar mensagem. Tente novamente.';
      } finally {
        isSubmitting.value = false;
        
        // Clear message after 5 seconds
        setTimeout(() => {
          submitStatus.value = '';
          submitMessage.value = '';
        }, 5000);
      }
    };

    return {
      contentRef,
      contentVisible,
      formData,
      isSubmitting,
      submitStatus,
      submitMessage,
      handleSubmit,
    };
  },
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/__colors.scss';
@import '@/assets/sass/settings/__fonts.scss';
@import '@/assets/sass/settings/__keyframes.scss';

.contact-section {
  position: relative;
  padding: 120px 0;
  background: $p-color;
  overflow: hidden;

  @media (max-width: 768px) {
    padding: 80px 0;
  }
}

.contact-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 40px;
  position: relative;
  z-index: 2;

  @media (max-width: 768px) {
    padding: 0 24px;
  }
}

.contact-content {
  display: grid;
  grid-template-columns: 1fr 1.2fr;
  gap: 80px;
  align-items: center;
  opacity: 0;
  transform: translateY(30px);
  transition: all 0.8s $ease-out-smooth;

  &.animate-in {
    opacity: 1;
    transform: translateY(0);
  }

  @media (max-width: 1024px) {
    grid-template-columns: 1fr;
    gap: 60px;
  }
}

// === INFO SECTION ===
.contact-info {
  color: $white;
}

.contact-title {
  font-family: $font-display;
  font-size: $text-display-md;
  font-weight: $weight-black;
  line-height: $leading-tight;
  margin-bottom: 24px;
  letter-spacing: $tracking-tight;

  @media (max-width: 768px) {
    font-size: $text-display-sm;
  }
}

.contact-description {
  font-family: $font-primary;
  font-size: $text-lg;
  line-height: $leading-relaxed;
  margin-bottom: 48px;
  opacity: 0.95;

  @media (max-width: 768px) {
    font-size: $text-base;
  }
}

.contact-items {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 20px;

  .item-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
  }

  .item-label {
    font-family: $font-primary;
    font-size: $text-sm;
    opacity: 0.8;
    margin-bottom: 4px;
  }

  .item-value {
    font-family: $font-secondary;
    font-size: $text-lg;
    font-weight: $weight-semibold;
  }
}

// === FORM SECTION ===
.contact-form {
  background: $white;
  padding: 48px;
  border-radius: $card-radius-lg;
  box-shadow: $shadow-2xl;

  @media (max-width: 768px) {
    padding: 32px 24px;
  }
}

.form-group {
  margin-bottom: 24px;
}

.form-label {
  display: block;
  font-family: $font-primary;
  font-size: $text-sm;
  font-weight: $weight-semibold;
  color: $gray-darkness;
  margin-bottom: 8px;
}

.form-input,
.form-textarea {
  width: 100%;
  padding: 14px 16px;
  border: 2px solid $gray-light;
  border-radius: 10px;
  font-family: $font-primary;
  font-size: $text-base;
  color: $gray-darkness;
  background: $white;
  transition: all $duration-fast $ease-out-smooth;

  &:focus {
    outline: none;
    border-color: $p-color;
    box-shadow: 0 0 0 3px rgba($p-color, 0.1);
  }

  &::placeholder {
    color: $gray-medium;
    opacity: 0.6;
  }
}

.form-textarea {
  resize: vertical;
  min-height: 120px;
}

.form-submit {
  width: 100%;
  padding: 16px 32px;
  background: $gradient-primary;
  color: $white;
  border: none;
  border-radius: 12px;
  font-family: $font-primary;
  font-size: $text-base;
  font-weight: $weight-semibold;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: all $duration-normal $ease-out-smooth;

  &:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: $shadow-xl;
  }

  &:active:not(:disabled) {
    transform: translateY(0);
  }

  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  .submit-icon {
    width: 20px;
    height: 20px;
    transition: transform $duration-normal $ease-out-smooth;
  }

  &:hover:not(:disabled) .submit-icon {
    transform: translateX(4px);
  }
}

.submit-message {
  margin-top: 16px;
  padding: 12px 16px;
  border-radius: 8px;
  font-family: $font-primary;
  font-size: $text-sm;
  font-weight: $weight-medium;
  animation: $slideInDown $duration-normal $ease-out-smooth;

  &.success {
    background: rgba($success, 0.1);
    color: $success;
    border: 1px solid rgba($success, 0.3);
  }

  &.error {
    background: rgba($error, 0.1);
    color: $error;
    border: 1px solid rgba($error, 0.3);
  }
}

// === BACKGROUND SHAPES ===
.contact-shapes {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
  z-index: 1;
  opacity: 0.1;
}

.shape {
  position: absolute;
  border-radius: 50%;
  background: $white;

  &-1 {
    width: 400px;
    height: 400px;
    top: -100px;
    right: -100px;
  }

  &-2 {
    width: 300px;
    height: 300px;
    bottom: -80px;
    left: -80px;
  }
}
</style>
