<template>
  <section class="benefits-game-section">
    <div class="benefits-container">
      <!-- Header -->
      <div class="benefits-header" data-scroll>
        <span class="benefits-badge">O Que Fazemos</span>
        <h2 class="benefits-title">
          Formas criativas de <span class="highlight-text">atrair novos clientes</span>
        </h2>
        <p class="benefits-description">
          Através da <strong>gamificação</strong> de softwares e produtos, conectamos sua empresa e seu
          cliente de uma maneira simplificada e divertida
        </p>
      </div>

      <!-- Cards Grid -->
      <div class="benefits-grid">
        <div 
          class="benefit-card-interactive" 
          data-scroll
          v-for="(benefit, index) in benefits" 
          :key="index"
          :style="{ '--delay': index * 0.1 + 's' }"
        >
          <!-- Ícone com efeitos de partículas -->
          <div class="card-icon-wrapper">
            <i :class="['benefit-icon', `icon ${benefit.icon}`]"></i>
            <div class="icon-particles">
              <span class="particle" v-for="i in 6" :key="i"></span>
            </div>
            <div class="icon-glow"></div>
          </div>

          <!-- Título -->
          <h5 class="card-title">{{ benefit.title }}</h5>

          <!-- Efeitos visuais de card de jogo -->
          <div class="card-border-glow"></div>
          <div class="card-shine"></div>
          <div class="card-corners">
            <span class="corner corner-tl"></span>
            <span class="corner corner-tr"></span>
            <span class="corner corner-bl"></span>
            <span class="corner corner-br"></span>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { onMounted, onUnmounted } from 'vue';

export default {
  name: 'BenefitsGameSection',
  props: {
    benefits: {
      type: Array,
      default: () => [
        { icon: 'icons-cart', title: 'Maiores taxas de conversões de vendas' },
        { icon: 'icons-growth', title: 'Aumento na geração de leads' },
        { icon: 'icons-viralMarketing', title: 'Receba mais atenção dos seus usuários' },
        { icon: 'icons-facilitySell', title: 'Aumento de adesão aos seus produtos' },
        { icon: 'icons-newDigital', title: 'Programas de indicação gamificados' },
        { icon: 'icons-work', title: 'Experiências interativas em campanhas e eventos' }
      ]
    }
  },
  setup() {
    let observer = null;

    const observeElements = () => {
      observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              entry.target.classList.add('is-visible');
            }
          });
        },
        {
          threshold: 0.1,
          rootMargin: '0px 0px -80px 0px'
        }
      );

      const elements = document.querySelectorAll('[data-scroll]');
      elements.forEach((el) => observer.observe(el));
    };

    onMounted(() => {
      setTimeout(() => {
        observeElements();
      }, 100);
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
@import '@/assets/sass/icons/icons-base/iconsBase.scss';

// ============================================
// BENEFITS GAME SECTION - ESTILO GAMING
// ============================================

.benefits-game-section {
  padding: 120px 0;
  background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
  position: relative;
  overflow: hidden;

  @media (max-width: $mobile) {
    padding: 80px 0;
  }
}

.benefits-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 20px;
}

// ============================================
// HEADER
// ============================================

.benefits-header {
  text-align: center;
  max-width: 800px;
  margin: 0 auto 80px;
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

.benefits-badge {
  display: inline-block;
  padding: 10px 24px;
  background: linear-gradient(135deg, rgba(231, 126, 34, 0.1) 0%, rgba(211, 84, 0, 0.1) 100%);
  border: 2px solid $p-color;
  border-radius: 50px;
  color: $p-color;
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  margin-bottom: 24px;
}

.benefits-title {
  font-size: 48px;
  font-weight: 900;
  line-height: 1.2;
  color: $gray-darkness;
  margin-bottom: 20px;

  @media (max-width: $tablet) {
    font-size: 36px;
  }

  @media (max-width: $mobile) {
    font-size: 28px;
  }
}

.highlight-text {
  color: $p-color;
  position: relative;
  
  &::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: linear-gradient(90deg, $p-color 0%, $p-dark 100%);
  }
}

.benefits-description {
  font-size: 18px;
  line-height: 1.7;
  color: $gray-medium;

  strong {
    color: $p-color;
    font-weight: 700;
  }

  @media (max-width: $mobile) {
    font-size: 16px;
  }
}

// ============================================
// GRID DE CARDS
// ============================================

.benefits-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 40px;

  @media (max-width: $mobile) {
    grid-template-columns: 1fr;
    gap: 30px;
  }
}

// ============================================
// CARD INTERATIVO ESTILO GAME
// ============================================

.benefit-card-interactive {
  position: relative;
  padding: 50px 30px;
  background: #ffffff;
  border: 2px solid rgba(231, 126, 34, 0.15);
  border-radius: 20px;
  text-align: center;
  overflow: hidden;
  cursor: pointer;
  opacity: 0;
  transform: translateY(50px) scale(0.95);
  transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
  transition-delay: var(--delay, 0s);

  // Animação de entrada
  &[data-scroll].is-visible {
    opacity: 1;
    transform: translateY(0) scale(1);
  }

  // Hover effect 3D
  &:hover {
    transform: translateY(-15px) scale(1.03) rotateX(2deg);
    border-color: $p-color;
    box-shadow: 
      0 30px 60px rgba(0, 0, 0, 0.15),
      0 0 40px rgba(231, 126, 34, 0.3),
      inset 0 1px 0 rgba(255, 255, 255, 0.6);

    .benefit-icon {
      transform: scale(1.2) rotate(5deg);
    }

    .icon-glow {
      opacity: 1;
      transform: scale(1.5);
    }

    .particle {
      animation-play-state: running;
    }

    .card-border-glow {
      opacity: 1;
    }

    .card-shine {
      transform: translateX(200%);
    }

    .corner {
      width: 30px;
      height: 30px;
      opacity: 1;
    }
  }

  @media (max-width: $mobile) {
    padding: 40px 24px;
  }
}

// ============================================
// ÍCONE COM PARTÍCULAS
// ============================================

.card-icon-wrapper {
  position: relative;
  width: 100px;
  height: 100px;
  margin: 0 auto 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.benefit-icon {
  width: 64px;
  height: 64px;
  display: inline-block;
  transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
  position: relative;
  z-index: 2;
  filter: brightness(0) saturate(100%) invert(56%) sepia(89%) saturate(1567%) hue-rotate(359deg) brightness(95%) contrast(88%);

  @media (max-width: $mobile) {
    width: 52px;
    height: 52px;
  }
}

.icon-particles {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.particle {
  position: absolute;
  width: 6px;
  height: 6px;
  background: $p-color;
  border-radius: 50%;
  opacity: 0;
  box-shadow: 0 0 10px $p-color;
  animation: particleFloat 3s ease-in-out infinite;
  animation-play-state: paused;

  @for $i from 1 through 6 {
    &:nth-child(#{$i}) {
      top: 50%;
      left: 50%;
      animation-delay: #{$i * 0.2}s;
      animation-duration: #{2 + random(2)}s;
    }
  }
}

@keyframes particleFloat {
  0%, 100% {
    opacity: 0;
    transform: translate(-50%, -50%) translateY(0) scale(0);
  }
  50% {
    opacity: 1;
  }
  100% {
    opacity: 0;
    transform: translate(
      calc(-50% + #{random(60) - 30}px), 
      calc(-50% - #{40 + random(40)}px)
    ) scale(1);
  }
}

.icon-glow {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 120px;
  height: 120px;
  background: radial-gradient(circle, rgba(231, 126, 34, 0.4) 0%, transparent 70%);
  transform: translate(-50%, -50%) scale(0.5);
  opacity: 0;
  transition: all 0.6s ease;
  filter: blur(20px);
  z-index: 1;
}

// ============================================
// TÍTULO DO CARD
// ============================================

.card-title {
  font-size: 18px;
  font-weight: 600;
  line-height: 1.5;
  color: $gray-darkness;
  margin: 0;
  position: relative;
  z-index: 2;

  @media (max-width: $mobile) {
    font-size: 17px;
  }
}

// ============================================
// EFEITOS VISUAIS DE GAME CARD
// ============================================

.card-border-glow {
  position: absolute;
  inset: -2px;
  background: linear-gradient(135deg, $p-color, $p-dark, $p-color);
  border-radius: 20px;
  opacity: 0;
  transition: opacity 0.5s ease;
  z-index: 0;
  filter: blur(8px);
}

.card-shine {
  position: absolute;
  top: 0;
  left: -100%;
  width: 50%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
  transform: skewX(-20deg);
  transition: transform 0.8s ease;
  z-index: 1;
}

// Cantos decorativos
.card-corners {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 3;
}

.corner {
  position: absolute;
  width: 0;
  height: 0;
  border: 2px solid $p-color;
  opacity: 0;
  transition: all 0.4s ease;

  &.corner-tl {
    top: 10px;
    left: 10px;
    border-right: none;
    border-bottom: none;
    border-radius: 4px 0 0 0;
  }

  &.corner-tr {
    top: 10px;
    right: 10px;
    border-left: none;
    border-bottom: none;
    border-radius: 0 4px 0 0;
  }

  &.corner-bl {
    bottom: 10px;
    left: 10px;
    border-right: none;
    border-top: none;
    border-radius: 0 0 0 4px;
  }

  &.corner-br {
    bottom: 10px;
    right: 10px;
    border-left: none;
    border-top: none;
    border-radius: 0 0 4px 0;
  }
}
</style>
