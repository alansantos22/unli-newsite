<template>
  <section class="hero-modern" ref="targetRef">
    <div class="hero-container">
      <!-- Left Content -->
      <div class="hero-content" :class="{ 'animate-in': isVisible }">
        <div class="hero-badge">
          <span class="badge-icon">✨</span>
          <span class="badge-text">{{ badge }}</span>
        </div>
        
        <h1 class="hero-title">
          <span class="title-line" v-for="(line, index) in titleLines" :key="index">
            {{ line }}
            <span v-if="highlightWords[index]" class="highlight-circle">
              {{ highlightWords[index] }}
            </span>
          </span>
        </h1>

        <p class="hero-description">{{ description }}</p>

        <div class="hero-actions">
          <button class="btn-primary" @click="$emit('primary-action')">
            {{ primaryButtonText }}
            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M5 12h14M12 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
          <button class="btn-secondary" @click="$emit('secondary-action')">
            {{ secondaryButtonText }}
          </button>
        </div>

        <!-- Stats -->
        <div class="hero-stats" v-if="stats.length > 0">
          <div class="stat-item" v-for="(stat, index) in stats" :key="index">
            <div class="stat-value">+{{ animatedStats[index] }}</div>
            <div class="stat-label">{{ stat.label }}</div>
          </div>
        </div>
      </div>

      <!-- Right Side: Image Grid -->
      <div class="hero-images" :class="{ 'animate-in': isVisible }">
        <div class="images-grid">
          <div 
            v-for="(image, index) in images" 
            :key="index"
            class="grid-item"
            :class="`grid-item-${index + 1}`"
            :style="{ animationDelay: `${index * 0.1}s` }"
          >
            <LazyImage 
              :src="image.src" 
              :alt="image.alt"
              class="grid-image"
            />
          </div>
        </div>

        <!-- Floating Elements -->
        <div class="floating-elements">
          <div class="float-circle orange"></div>
          <div class="float-circle blue"></div>
          <div class="float-circle purple"></div>
        </div>
      </div>
    </div>

    <!-- Decorative Shapes -->
    <div class="hero-shapes">
      <div class="shape shape-1"></div>
      <div class="shape shape-2"></div>
      <div class="shape shape-3"></div>
    </div>
  </section>
</template>

<script>
import { ref, watch, onMounted } from 'vue';
import { useIntersectionObserver } from '@/core/composables/useIntersectionObserver';
import LazyImage from '@/shared/Components/LazyImage.vue';

export default {
  name: 'HeroModern',
  components: {
    LazyImage,
  },
  props: {
    badge: {
      type: String,
      default: 'Gamificação Premium',
    },
    titleLines: {
      type: Array,
      default: () => ['Create', 'High-Quality', 'Visual'],
    },
    highlightWords: {
      type: Array,
      default: () => [null, null, null],
    },
    description: {
      type: String,
      default: 'Busque uma melhor conexão com o seu cliente através de experiências gamificadas únicas e envolventes.',
    },
    primaryButtonText: {
      type: String,
      default: 'Começar Agora',
    },
    secondaryButtonText: {
      type: String,
      default: 'Ver Portfólio',
    },
    images: {
      type: Array,
      default: () => [],
    },
    stats: {
      type: Array,
      default: () => [
        { value: '158', label: 'Projetos' },
        { value: '625', label: 'Clientes Felizes' },
        { value: '730', label: 'Horas de Gamificação' },
      ],
    },
  },
  setup(props) {
    const { targetRef, isVisible } = useIntersectionObserver({
      threshold: 0.1,
      triggerOnce: true,
    });

    const animatedStats = ref(props.stats.map(() => '0'));

    // Função para animar números
    const animateValue = (index, start, end, duration) => {
      // Extrair apenas números do valor
      const numberMatch = String(end).match(/\d+/);
      if (!numberMatch) {
        animatedStats.value[index] = end;
        return;
      }

      const targetNumber = parseInt(numberMatch[0]);
      const prefix = String(end).split(numberMatch[0])[0] || '';
      const suffix = String(end).split(numberMatch[0])[1] || '';
      
      const startTime = performance.now();
      const startNumber = 0;

      const animate = (currentTime) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);

        // Easing function (ease-out)
        const easeOut = 1 - Math.pow(1 - progress, 3);
        const current = Math.floor(startNumber + (targetNumber - startNumber) * easeOut);

        animatedStats.value[index] = prefix + current.toLocaleString('pt-BR') + suffix;

        if (progress < 1) {
          requestAnimationFrame(animate);
        } else {
          animatedStats.value[index] = prefix + targetNumber.toLocaleString('pt-BR') + suffix;
        }
      };

      requestAnimationFrame(animate);
    };

    // Iniciar animação quando ficar visível
    watch(isVisible, (newValue) => {
      if (newValue) {
        props.stats.forEach((stat, index) => {
          setTimeout(() => {
            animateValue(index, 0, stat.value, 2000); // 2 segundos de animação
          }, index * 200); // Delay escalonado
        });
      }
    });

    // Garantir que stats iniciem em 0
    onMounted(() => {
      animatedStats.value = props.stats.map(() => '0');
    });

    return {
      targetRef,
      isVisible,
      animatedStats,
    };
  },
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/__colors.scss';
@import '@/assets/sass/settings/__fonts.scss';
@import '@/assets/sass/settings/__keyframes.scss';

.hero-modern {
  position: relative;
  min-height: 100vh;
  display: flex;
  align-items: center;
  background: $white;
  overflow: hidden;
  padding: 120px 0 80px;

  @media (max-width: 768px) {
    padding: 80px 0 60px;
    min-height: auto;
  }
}

.hero-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 40px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 80px;
  align-items: center;
  position: relative;
  z-index: 2;

  @media (max-width: 1024px) {
    grid-template-columns: 1fr;
    gap: 60px;
    padding: 0 24px;
  }
}

// === CONTENT SECTION ===
.hero-content {
  opacity: 0;
  transform: translateX(-40px);
  transition: all 0.8s $ease-out-smooth;

  &.animate-in {
    opacity: 1;
    transform: translateX(0);
  }
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 20px;
  background: linear-gradient(135deg, rgba($p-color, 0.1) 0%, rgba($p-light, 0.1) 100%);
  border: 1px solid rgba($p-color, 0.2);
  border-radius: 50px;
  margin-bottom: 24px;
  animation: $pulseGlow 3s ease-in-out infinite;

  .badge-icon {
    font-size: 18px;
  }

  .badge-text {
    font-family: $font-primary;
    font-size: $text-sm;
    font-weight: $weight-semibold;
    color: $p-dark;
  }
}

.hero-title {
  font-family: $font-display;
  font-size: $text-display-lg;
  font-weight: $weight-black;
  line-height: $leading-tight;
  color: $gray-darkness;
  margin-bottom: 24px;
  letter-spacing: $tracking-tight;

  .title-line {
    display: block;
  }

  .highlight-circle {
    position: relative;
    display: inline-block;
    
    &::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 120%;
      height: 120%;
      background: $p-color;
      border-radius: 50%;
      z-index: -1;
      opacity: 0.15;
    }
  }

  @media (max-width: 768px) {
    font-size: $text-display-md;
  }

  @media (max-width: 480px) {
    font-size: $text-display-sm;
  }
}

.hero-description {
  font-family: $font-primary;
  font-size: $text-lg;
  line-height: $leading-relaxed;
  color: $gray-medium;
  margin-bottom: 40px;
  max-width: 540px;

  @media (max-width: 768px) {
    font-size: $text-base;
  }
}

.hero-actions {
  display: flex;
  gap: 16px;
  margin-bottom: 60px;

  @media (max-width: 480px) {
    flex-direction: column;
  }
}

.btn-primary,
.btn-secondary {
  padding: 16px 32px;
  border-radius: 12px;
  font-family: $font-primary;
  font-size: $text-base;
  font-weight: $weight-semibold;
  cursor: pointer;
  transition: all $duration-normal $ease-out-smooth;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  
  &:hover {
    transform: translateY(-2px);
    box-shadow: $shadow-xl;
  }

  &:active {
    transform: translateY(0);
  }
}

.btn-primary {
  background: $gradient-primary;
  color: $white;
  
  .btn-icon {
    width: 20px;
    height: 20px;
    transition: transform $duration-normal $ease-out-smooth;
  }

  &:hover .btn-icon {
    transform: translateX(4px);
  }
}

.btn-secondary {
  background: transparent;
  color: $gray-darkness;
  border: 2px solid $gray-light;

  &:hover {
    border-color: $p-color;
    color: $p-color;
    background: rgba($p-color, 0.05);
  }
}

.hero-stats {
  display: flex;
  gap: 48px;
  padding-top: 40px;
  border-top: 1px solid $gray-light;

  @media (max-width: 768px) {
    gap: 32px;
  }

  @media (max-width: 480px) {
    flex-wrap: wrap;
    gap: 24px;
  }
}

.stat-item {
  .stat-value {
    font-family: $font-display;
    font-size: $text-display-sm;
    font-weight: $weight-bold;
    color: $p-color;
    line-height: 1;
    margin-bottom: 8px;
  }

  .stat-label {
    font-family: $font-primary;
    font-size: $text-sm;
    color: $gray-medium;
  }
}

// === IMAGE GRID ===
.hero-images {
  position: relative;
  opacity: 0;
  transform: translateX(40px);
  transition: all 0.8s $ease-out-smooth 0.2s;

  &.animate-in {
    opacity: 1;
    transform: translateX(0);
  }
}

.images-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  grid-template-rows: repeat(3, 140px);
  gap: 16px;
  position: relative;

  @media (max-width: 768px) {
    grid-template-rows: repeat(3, 100px);
    gap: 12px;
  }
}

.grid-item {
  border-radius: 16px;
  overflow: hidden;
  background: $gray-lightness;
  box-shadow: $shadow-md;
  transition: all $duration-normal $ease-out-smooth;
  opacity: 0;
  animation: $fadeInUp $duration-slow $ease-out-smooth forwards;

  &:hover {
    transform: scale(1.05);
    box-shadow: $shadow-xl;
    z-index: 2;
  }

  &-1 {
    grid-column: 1 / 2;
    grid-row: 1 / 3;
  }

  &-2 {
    grid-column: 2 / 3;
    grid-row: 1 / 2;
  }

  &-3 {
    grid-column: 3 / 4;
    grid-row: 1 / 3;
  }

  &-4 {
    grid-column: 2 / 3;
    grid-row: 2 / 4;
  }

  &-5 {
    grid-column: 1 / 2;
    grid-row: 3 / 4;
  }

  &-6 {
    grid-column: 3 / 4;
    grid-row: 3 / 4;
  }
}

.grid-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

// === FLOATING ELEMENTS ===
.floating-elements {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
  z-index: -1;
}

.float-circle {
  position: absolute;
  border-radius: 50%;
  filter: blur(40px);
  opacity: 0.15;
  animation: $float 6s ease-in-out infinite;

  &.orange {
    width: 200px;
    height: 200px;
    background: $p-color;
    top: 10%;
    right: 15%;
    animation-delay: 0s;
  }

  &.blue {
    width: 150px;
    height: 150px;
    background: $p-dark;
    bottom: 20%;
    left: 10%;
    animation-delay: 2s;
  }

  &.purple {
    width: 120px;
    height: 120px;
    background: $p-light;
    top: 60%;
    right: 25%;
    animation-delay: 4s;
  }
}

// === DECORATIVE SHAPES ===
.hero-shapes {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
  overflow: hidden;
  z-index: 1;
}

.shape {
  position: absolute;
  opacity: 0.03;

  &-1 {
    width: 400px;
    height: 400px;
    border-radius: 50%;
    background: $p-color;
    top: -100px;
    left: -100px;
  }

  &-2 {
    width: 300px;
    height: 300px;
    border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
    background: $accent-blue;
    bottom: -50px;
    right: -50px;
  }

  &-3 {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background: $accent-purple;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }
}
</style>
