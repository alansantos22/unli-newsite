<template>
  <section :class="['timeline-section-slides', `timeline-${variant}`]">
    <div class="slides-container">
      <!-- Header -->
      <div class="timeline-header" data-scroll>
        <span v-if="badge" class="badge-glow">{{ badge }}</span>
        <h2 class="section-title-big" v-html="title"></h2>
        <p v-if="description" class="section-description">{{ description }}</p>
      </div>

      <!-- Timeline com linha conectora -->
      <div class="timeline-wrapper">
        <!-- Linha vertical conectora -->
        <div class="timeline-connector-line"></div>

        <!-- Slides Timeline -->
        <div class="timeline-slides">
          <div 
            v-for="(step, index) in steps" 
            :key="index"
            :class="['slide-item', `slide-${index + 1}`]"
            data-scroll
          >
            <!-- Ponto na linha -->
            <div class="timeline-dot">
              <div class="dot-inner"></div>
              <div class="dot-ring"></div>
            </div>

            <!-- Card do conteúdo -->
            <div class="slide-card">
              <!-- Número da etapa -->
              <div class="slide-number">
                <span class="number-big">0{{ index + 1 }}</span>
              </div>

              <!-- Conteúdo -->
              <div class="slide-content">
                <h3 class="slide-title" v-html="step.title"></h3>
                <p class="slide-description">{{ step.description }}</p>
              </div>

              <!-- Decorações do card -->
              <div class="card-decoration"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Key Benefits com efeitos gamificados -->
      <div v-if="keyBenefits && keyBenefits.length" class="benefits-game" data-scroll>
        <h3 class="benefits-title">Por que isso importa?</h3>
        <div class="benefits-grid">
          <div 
            v-for="(benefit, index) in keyBenefits" 
            :key="index"
            class="benefit-card-game"
            data-scroll
          >
            <div class="benefit-icon-wrapper">
              <i :class="['benefit-icon-large', benefit.icon]"></i>
              <div class="icon-glow-effect"></div>
            </div>
            <p class="benefit-text-bold">{{ benefit.text }}</p>
            <div class="card-shine"></div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { onMounted, onUnmounted } from 'vue';

export default {
  name: 'TimelineSection',
  props: {
    variant: {
      type: String,
      default: 'dark',
      validator: (value) => ['light', 'dark'].includes(value)
    },
    badge: {
      type: String,
      default: ''
    },
    title: {
      type: String,
      required: true
    },
    description: {
      type: String,
      default: ''
    },
    steps: {
      type: Array,
      required: true,
      validator: (steps) => {
        return steps.every(step => 
          step.title && step.description
        );
      }
    },
    keyBenefits: {
      type: Array,
      default: () => []
    }
  },
  setup() {
    const observeElements = () => {
      const observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              entry.target.classList.add('is-visible');
            }
          });
        },
        {
          threshold: 0.1,
          rootMargin: '0px 0px -100px 0px'
        }
      );

      const elements = document.querySelectorAll('[data-scroll]');
      elements.forEach((el) => observer.observe(el));

      return observer;
    };

    let observer = null;

    onMounted(() => {
      // Garantir que os elementos aparecem
      setTimeout(() => {
        observer = observeElements();
      }, 100);

      // Fallback: se após 3s ainda não aparecer, força a visibilidade
      setTimeout(() => {
        const hiddenElements = document.querySelectorAll('[data-scroll]:not(.is-visible)');
        hiddenElements.forEach(el => el.classList.add('is-visible'));
      }, 3000);
    });

    onUnmounted(() => {
      if (observer) {
        observer.disconnect();
      }
    });

    return {};
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/__colors.scss';
@import '@/assets/sass/settings/__fonts.scss';
@import '@/assets/sass/settings/__breakpoints.scss';
@import '@/assets/sass/settings/__keyframes.scss';
@import '@/assets/sass/settings/__animation.scss';

// ============================================
// TIMELINE ESTILO APRESENTAÇÃO DE SLIDES
// Efeitos dramáticos e impactantes
// ============================================

.timeline-section-slides {
  padding: 100px 0;
  position: relative;
  overflow: hidden;
  background: linear-gradient(180deg, #0a0e27 0%, #050810 50%, #0a0e27 100%);

  @media (max-width: $mobile) {
    padding: 60px 0;
  }

  // Efeito de grid de fundo (cyber)
  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: 
      linear-gradient(rgba(231, 126, 34, 0.03) 1px, transparent 1px),
      linear-gradient(90deg, rgba(231, 126, 34, 0.03) 1px, transparent 1px);
    background-size: 50px 50px;
    opacity: 0.5;
    animation: gridMove 20s linear infinite;
  }
}

@keyframes gridMove {
  0% { transform: translate(0, 0); }
  100% { transform: translate(50px, 50px); }
}

.slides-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 20px;
  position: relative;
  z-index: 1;
}

// ============================================
// HEADER COM EFEITOS
// ============================================

.timeline-header {
  text-align: center;
  margin-bottom: 100px;
  position: relative;
  opacity: 0;
  transform: translateY(30px);
  transition: all 0.8s ease;

  &.is-visible {
    opacity: 1;
    transform: translateY(0);
  }

  @media (max-width: $mobile) {
    margin-bottom: 60px;
  }
}

.badge-glow {
  display: inline-block;
  padding: 12px 30px;
  background: linear-gradient(135deg, rgba(231, 126, 34, 0.2) 0%, rgba(211, 84, 0, 0.2) 100%);
  border: 2px solid $p-color;
  border-radius: 50px;
  color: $p-color;
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  margin-bottom: 30px;
  box-shadow: 
    0 0 20px rgba(231, 126, 34, 0.3),
    inset 0 0 20px rgba(231, 126, 34, 0.1);
  animation: badgePulse 3s ease-in-out infinite;
}

@keyframes badgePulse {
  0%, 100% { 
    box-shadow: 
      0 0 20px rgba(231, 126, 34, 0.3),
      inset 0 0 20px rgba(231, 126, 34, 0.1);
  }
  50% { 
    box-shadow: 
      0 0 40px rgba(231, 126, 34, 0.6),
      inset 0 0 30px rgba(231, 126, 34, 0.2);
  }
}

.section-title-big {
  font-size: 64px;
  font-weight: 900;
  line-height: 1.1;
  color: #ffffff;
  margin-bottom: 20px;
  text-transform: uppercase;
  background: linear-gradient(135deg, #ffffff 0%, $p-color 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: -2px;

  @media (max-width: $tablet) {
    font-size: 48px;
  }

  @media (max-width: $mobile) {
    font-size: 32px;
    letter-spacing: -1px;
  }
}

.section-description {
  font-size: 20px;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.7);
  max-width: 700px;
  margin: 0 auto;

  @media (max-width: $mobile) {
    font-size: 16px;
  }
}

// ============================================
// TIMELINE COM LINHA CONECTORA
// ============================================

.timeline-wrapper {
  position: relative;
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 0;
}

// Linha vertical conectora NO CENTRO
.timeline-connector-line {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  top: 0;
  bottom: 0;
  width: 3px;
  background: linear-gradient(180deg, 
    transparent 0%,
    rgba(231, 126, 34, 0.5) 10%,
    rgba(231, 126, 34, 0.5) 90%,
    transparent 100%
  );
  z-index: 0;

  @media (max-width: $tablet) {
    left: 60px;
    transform: none;
  }

  @media (max-width: $mobile) {
    left: 30px;
  }

  // Efeito de progresso animado
  &::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 0;
    background: linear-gradient(180deg, 
      $p-color 0%,
      $p-dark 100%
    );
    box-shadow: 0 0 20px rgba(231, 126, 34, 0.6);
    animation: lineProgress 3s ease-out forwards;
  }
}

@keyframes lineProgress {
  to { height: 100%; }
}

.timeline-slides {
  display: flex;
  flex-direction: column;
  gap: 80px;
  position: relative;
  z-index: 1;

  @media (max-width: $mobile) {
    gap: 60px;
  }
}

.slide-item {
  position: relative;
  display: flex;
  align-items: center;
  opacity: 0;
  transition: all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);

  @media (max-width: $tablet) {
    align-items: flex-start;
  }

  // ÍMPARES (1, 3, 5) - Card à DIREITA
  &:nth-child(odd) {
    justify-content: flex-end;
    transform: translateX(50px);

    @media (max-width: $tablet) {
      justify-content: flex-start;
      transform: translateX(-50px);
    }

    .timeline-dot {
      order: 1;
    }

    .slide-card {
      order: 2;
      margin-left: 40px;

      @media (max-width: $tablet) {
        margin-left: 20px;
      }
    }
  }

  // PARES (2, 4, 6) - Card à ESQUERDA
  &:nth-child(even) {
    justify-content: flex-start;
    transform: translateX(-50px);

    @media (max-width: $tablet) {
      justify-content: flex-start;
      transform: translateX(-50px);
    }

    .timeline-dot {
      order: 2;
    }

    .slide-card {
      order: 1;
      margin-right: 40px;

      @media (max-width: $tablet) {
        margin-right: 0;
        margin-left: 20px;
      }
    }
  }

  // Animação de entrada quando scroll
  &[data-scroll].is-visible {
    opacity: 1;
    transform: translateX(0);
  }

  // Delay escalonado para cada item
  @for $i from 1 through 6 {
    &.slide-#{$i}[data-scroll].is-visible {
      transition-delay: #{$i * 0.15}s;
    }
  }
}

// ============================================
// PONTO NA LINHA (DOT) - FIXO NO CENTRO
// ============================================

.timeline-dot {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  flex-shrink: 0;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;

  @media (max-width: $tablet) {
    left: 60px;
    transform: none;
  }

  @media (max-width: $mobile) {
    left: 30px;
    width: 40px;
    height: 40px;
  }
}

.dot-inner {
  width: 20px;
  height: 20px;
  background: $p-color;
  border-radius: 50%;
  box-shadow: 0 0 20px rgba(231, 126, 34, 0.6);
  transition: all 0.4s ease;
  position: relative;
  z-index: 2;

  @media (max-width: $mobile) {
    width: 14px;
    height: 14px;
  }
}

.dot-ring {
  position: absolute;
  width: 40px;
  height: 40px;
  border: 3px solid rgba(231, 126, 34, 0.3);
  border-radius: 50%;
  z-index: 1;
  animation: pulseRing 2s ease-out infinite;

  @media (max-width: $mobile) {
    width: 30px;
    height: 30px;
    border-width: 2px;
  }
}

@keyframes pulseRing {
  0%, 100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.3);
    opacity: 0.3;
  }
}

// ============================================
// CARD DO SLIDE - METADE DA LARGURA
// ============================================

.slide-card {
  flex: 0 0 calc(50% - 60px);
  max-width: calc(50% - 60px);
  position: relative;
  background: rgba(255, 255, 255, 0.03);
  border: 2px solid rgba(231, 126, 34, 0.2);
  border-radius: 20px;
  padding: 40px;
  overflow: hidden;
  transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);

  @media (max-width: $tablet) {
    flex: 1;
    max-width: calc(100% - 100px);
    margin-left: 100px !important;
    margin-right: 0 !important;
  }

  @media (max-width: $mobile) {
    padding: 24px;
    max-width: calc(100% - 70px);
    margin-left: 70px !important;
  }

  // Hover effect
  &:hover {
    transform: translateY(-8px);
    border-color: rgba(231, 126, 34, 0.6);
    background: rgba(255, 255, 255, 0.06);
    box-shadow: 
      0 20px 60px rgba(0, 0, 0, 0.4),
      0 0 40px rgba(231, 126, 34, 0.2);

    ~ .timeline-dot .dot-inner {
      transform: scale(1.4);
      box-shadow: 0 0 30px rgba(231, 126, 34, 0.9);
    }

    .number-big {
      transform: scale(1.05);
      text-shadow: 
        0 0 30px rgba(231, 126, 34, 0.8),
        0 0 60px rgba(231, 126, 34, 0.4);
    }

    .card-decoration {
      opacity: 1;
      transform: scale(1.2);
    }
  }
}

// Decoração do card
.card-decoration {
  position: absolute;
  top: -50%;
  right: -20%;
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, rgba(231, 126, 34, 0.15) 0%, transparent 70%);
  border-radius: 50%;
  opacity: 0;
  transition: all 0.8s ease;
  pointer-events: none;
  filter: blur(40px);
}

// ============================================
// NÚMERO DA ETAPA
// ============================================

.slide-number {
  margin-bottom: 20px;
}

.number-big {
  font-size: 72px;
  font-weight: 900;
  background: linear-gradient(135deg, $p-color 0%, $p-dark 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  display: inline-block;
  line-height: 1;
  transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
  text-shadow: 
    0 0 20px rgba(231, 126, 34, 0.4);

  @media (max-width: $mobile) {
    font-size: 48px;
  }
}

// ============================================
// CONTEÚDO DO SLIDE
// ============================================

.slide-content {
  position: relative;
  z-index: 2;
}

.slide-title {
  font-size: 28px;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: 16px;
  line-height: 1.3;

  @media (max-width: $mobile) {
    font-size: 22px;
  }
}

.slide-description {
  font-size: 17px;
  line-height: 1.8;
  color: rgba(255, 255, 255, 0.75);

  @media (max-width: $mobile) {
    font-size: 16px;
  }
}

// ============================================
// BENEFITS GAMIFICADOS
// ============================================

.benefits-game {
  margin-top: 120px;
  padding-top: 80px;
  border-top: 2px solid rgba(231, 126, 34, 0.2);
  opacity: 0;
  transform: translateY(50px);
  transition: all 0.8s ease;

  &.is-visible {
    opacity: 1;
    transform: translateY(0);
  }

  @media (max-width: $mobile) {
    margin-top: 80px;
    padding-top: 60px;
  }
}

.benefits-title {
  font-size: 42px;
  font-weight: 900;
  text-align: center;
  color: #ffffff;
  margin-bottom: 60px;
  text-transform: uppercase;
  letter-spacing: -1px;

  @media (max-width: $mobile) {
    font-size: 28px;
  }
}

.benefits-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 40px;

  @media (max-width: $mobile) {
    grid-template-columns: 1fr;
    gap: 30px;
  }
}

.benefit-card-game {
  position: relative;
  padding: 40px;
  background: rgba(255, 255, 255, 0.03);
  border: 2px solid rgba(231, 126, 34, 0.2);
  border-radius: 16px;
  text-align: center;
  overflow: hidden;
  cursor: pointer;
  opacity: 0;
  transform: translateY(30px);
  transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);

  // Animação de entrada
  &[data-scroll].is-visible {
    opacity: 1;
    transform: translateY(0);
  }

  // Delay escalonado
  @for $i from 1 through 3 {
    &:nth-child(#{$i})[data-scroll].is-visible {
      transition-delay: #{$i * 0.15}s;
    }
  }

  &:hover {
    transform: translateY(-10px) scale(1.05);
    border-color: $p-color;
    background: rgba(255, 255, 255, 0.08);
    box-shadow: 
      0 20px 50px rgba(0, 0, 0, 0.5),
      0 0 40px rgba(231, 126, 34, 0.3);

    .benefit-icon-large {
      transform: scale(1.2) rotate(10deg);
      color: $p-color;
    }

    .icon-glow-effect {
      transform: scale(1.5);
      opacity: 1;
    }

    .card-shine {
      transform: translateX(100%);
    }
  }
}

.benefit-icon-wrapper {
  position: relative;
  display: inline-block;
  margin-bottom: 20px;
}

.benefit-icon-large {
  font-size: 64px;
  color: rgba(231, 126, 34, 0.8);
  transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
  position: relative;
  z-index: 2;

  @media (max-width: $mobile) {
    font-size: 48px;
  }
}

.icon-glow-effect {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 100px;
  height: 100px;
  background: radial-gradient(circle, rgba(231, 126, 34, 0.4) 0%, transparent 70%);
  transform: translate(-50%, -50%) scale(0.5);
  opacity: 0;
  transition: all 0.5s ease;
  filter: blur(20px);
  z-index: 1;
}

.benefit-text-bold {
  font-size: 18px;
  font-weight: 600;
  color: #ffffff;
  line-height: 1.5;
  margin: 0;

  @media (max-width: $mobile) {
    font-size: 16px;
  }
}

.card-shine {
  position: absolute;
  top: 0;
  left: -100%;
  width: 50%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  transform: skewX(-20deg);
  transition: transform 0.8s ease;
}
</style>
