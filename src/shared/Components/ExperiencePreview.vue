<template>
  <section class="experience-preview-section">
    <div class="experience-container">
      <!-- Header -->
      <div class="experience-header" data-scroll>
        <h2 class="experience-title">
          Veja como é trabalhar <span class="highlight-text">com a gente</span>
        </h2>
        <p class="experience-description">
          Toque nos elementos abaixo e descubra a experiência UNLI
        </p>
        <p class="experience-disclaimer">
          * Resultados baseados em expectativas do mercado de gamificação
        </p>
      </div>

      <!-- Interactive Dashboard -->
      <div class="interactive-dashboard" data-scroll>
        <!-- Central Hub (Núcleo) -->
        <div class="central-hub" @mouseenter="activateHub" @mouseleave="deactivateHub">
          <div class="hub-core" :class="{ active: hubActive }">
            <div class="core-glow"></div>
            <div class="core-inner">
              <div class="hub-icon">
                <i class="icon icons-work"></i>
              </div>
            </div>
            <div class="hub-pulse"></div>
          </div>
          <p class="hub-label">UNLI Studio</p>
        </div>

        <!-- Cristais Orbitando -->
        <div 
          v-for="(item, index) in interactiveItems" 
          :key="index"
          class="crystal-orbit"
          :class="{ active: activeCard === index, rotating: hubActive }"
          :style="getOrbitPosition(index)"
          @mouseenter="activateCard(index)"
          @mouseleave="deactivateCard"
          @click="triggerCardAction(item)"
        >
          <!-- Cristal 3D com Three.js -->
          <Crystal3D 
            :color="item.color" 
            :isActive="activeCard === index"
          />

          <!-- Info Card (aparece no hover) -->
          <div class="crystal-info">
            <div class="info-icon">
              <i :class="['icon', item.icon]"></i>
            </div>
            <h4 class="info-title">{{ item.title }}</h4>
            <p class="info-description">{{ item.description }}</p>
            <div class="info-stat" v-if="item.stat">
              <span class="stat-value">{{ item.stat }}</span>
            </div>
          </div>

          <!-- Click Ripple Effect -->
          <div class="click-ripple" v-if="clickedCard === index"></div>
        </div>

        <!-- Achievement Toast -->
        <transition name="toast-slide">
          <div class="achievement-toast" v-if="showAchievement">
            <i class="icon icons-confirm"></i>
            <span>{{ achievementText }}</span>
          </div>
        </transition>
      </div>
    </div>
  </section>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';
import Crystal3D from './Crystal3D.vue';

export default {
  name: 'ExperiencePreview',
  components: {
    Crystal3D
  },
  setup() {
    const hubActive = ref(false);
    const activeCard = ref(null);
    const clickedCard = ref(null);
    const showAchievement = ref(false);
    const achievementText = ref('');

    const interactiveItems = ref([
      {
        icon: 'icons-cart',
        title: 'Conversões',
        description: 'Taxas de conversão aumentadas em até 40%*',
        stat: '+40%',
        color: 'blue',
        achievement: '🎯 Descobridor de Conversões!'
      },
      {
        icon: 'icons-growth',
        title: 'Engajamento',
        description: 'Usuários ativos diários crescem 3x*',
        stat: '3x',
        color: 'green',
        achievement: '📈 Mestre do Engajamento!'
      },
      {
        icon: 'icons-viralMarketing',
        title: 'Viralização',
        description: 'Compartilhamentos orgânicos aumentados*',
        stat: '+250%',
        color: 'purple',
        achievement: '🚀 Viral Expert!'
      },
      {
        icon: 'icons-newDigital',
        title: 'Inovação',
        description: 'Gamificação com tecnologia de ponta',
        stat: '100%',
        color: 'orange',
        achievement: '💡 Inovador Digital!'
      },
      {
        icon: 'icons-work',
        title: 'Resultados',
        description: 'Entrega em tempo recorde com qualidade',
        stat: 'Fast',
        color: 'red',
        achievement: '⚡ Velocista de Resultados!'
      }
    ]);

    const activateHub = () => {
      hubActive.value = true;
    };

    const deactivateHub = () => {
      hubActive.value = false;
    };

    const activateCard = (index) => {
      activeCard.value = index;
    };

    const deactivateCard = () => {
      activeCard.value = null;
    };

    const triggerCardAction = (item) => {
      clickedCard.value = interactiveItems.value.indexOf(item);
      
      // Show achievement
      achievementText.value = item.achievement;
      showAchievement.value = true;

      // Hide ripple and achievement
      setTimeout(() => {
        clickedCard.value = null;
      }, 600);

      setTimeout(() => {
        showAchievement.value = false;
      }, 3000);
    };

    const getOrbitPosition = (index) => {
      const total = interactiveItems.value.length;
      const angle = (360 / total) * index - 90; // Start from top
      const radius = 320; // Distance from center

      const x = Math.cos((angle * Math.PI) / 180) * radius;
      const y = Math.sin((angle * Math.PI) / 180) * radius;

      const baseTransform = `translate(${x}px, ${y}px) translate(-50%, -50%)`;

      return {
        transform: baseTransform,
        '--base-transform': baseTransform,
        '--orbit-delay': `${index * 0.15}s`
      };
    };

    // Scroll animations
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
          rootMargin: '0px 0px -100px 0px'
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

    return {
      hubActive,
      activeCard,
      clickedCard,
      showAchievement,
      achievementText,
      interactiveItems,
      activateHub,
      deactivateHub,
      activateCard,
      deactivateCard,
      triggerCardAction,
      getOrbitPosition
    };
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/__colors.scss';
@import '@/assets/sass/settings/__fonts.scss';
@import '@/assets/sass/settings/__breakpoints.scss';
@import '@/assets/sass/icons/icons-base/iconsBase.scss';

// ============================================
// EXPERIENCE PREVIEW SECTION
// ============================================

.experience-preview-section {
  padding: 140px 0;
  background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
  position: relative;

  @media (max-width: $mobile) {
    padding: 100px 0;
  }
}

.experience-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 20px;
}

// ============================================
// HEADER
// ============================================

.experience-header {
  text-align: center;
  max-width: 700px;
  margin: 0 auto 100px;
  opacity: 0;
  transform: translateY(30px);
  transition: all 0.8s ease;

  &.is-visible {
    opacity: 1;
    transform: translateY(0);
  }
}

.experience-badge {
  display: inline-block;
  padding: 10px 24px;
  background: linear-gradient(135deg, rgba(231, 126, 34, 0.15) 0%, rgba(211, 84, 0, 0.15) 100%);
  border: 2px solid $p-color;
  border-radius: 50px;
  color: $p-color;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  margin-bottom: 24px;
}

.experience-title {
  font-size: 52px;
  font-weight: 900;
  line-height: 1.2;
  color: $gray-darkness;
  margin-bottom: 16px;

  @media (max-width: $tablet) {
    font-size: 38px;
  }

  @media (max-width: $mobile) {
    font-size: 30px;
  }
}

.highlight-text {
  color: $p-color;
  position: relative;

  &::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, $p-color, $p-dark);
    border-radius: 2px;
  }
}

.experience-description {
  font-size: 18px;
  color: $gray-medium;
  line-height: 1.6;
  margin-bottom: 12px;
}

.experience-disclaimer {
  font-size: 13px;
  color: rgba(0, 0, 0, 0.4);
  font-style: italic;
  margin-top: 8px;
}

// ============================================
// INTERACTIVE DASHBOARD
// ============================================

.interactive-dashboard {
  position: relative;
  height: 800px;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transform: scale(0.9);
  transition: all 1s cubic-bezier(0.34, 1.56, 0.64, 1);
  overflow: visible;
  z-index: 1;

  &.is-visible {
    opacity: 1;
    transform: scale(1);
  }

  @media (max-width: $tablet) {
    height: 700px;
  }

  @media (max-width: $mobile) {
    height: 600px;
  }
}

// ============================================
// CENTRAL HUB (NÚCLEO)
// ============================================

.central-hub {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  z-index: 10;
}

.hub-core {
  position: relative;
  width: 160px;
  height: 160px;
  cursor: pointer;
  transition: all 0.5s ease;

  &.active {
    transform: scale(1.1);
    
    .core-glow {
      opacity: 1;
      transform: scale(1.3);
    }

    .core-inner {
      transform: rotate(180deg);
    }
  }

  @media (max-width: $mobile) {
    width: 120px;
    height: 120px;
  }
}

.core-inner {
  position: relative;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, $p-color 0%, $p-dark 100%);
  clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 
    0 15px 50px rgba(231, 126, 34, 0.4),
    inset 0 5px 15px rgba(255, 255, 255, 0.3),
    inset 0 -5px 15px rgba(0, 0, 0, 0.2);
  transition: transform 0.8s ease;

  &::before {
    content: '';
    position: absolute;
    inset: 8px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, transparent 100%);
    clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
  }
}

.core-glow {
  position: absolute;
  inset: -30px;
  background: radial-gradient(circle, rgba(231, 126, 34, 0.6) 0%, transparent 70%);
  opacity: 0;
  transition: all 0.6s ease;
  filter: blur(30px);
  z-index: -1;
}

.hub-icon {
  width: 70px;
  height: 70px;
  display: inline-block;
  filter: brightness(0) saturate(100%) invert(100%) sepia(0%) saturate(0%) hue-rotate(0deg) brightness(100%) contrast(100%);
  position: relative;
  z-index: 2;

  @media (max-width: $mobile) {
    width: 50px;
    height: 50px;
  }
}

.hub-pulse {
  position: absolute;
  inset: -15px;
  border: 3px solid rgba(231, 126, 34, 0.6);
  clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
  opacity: 0;
  animation: hexPulse 2.5s ease infinite;
}

@keyframes hexPulse {
  0% {
    opacity: 1;
    transform: scale(1);
  }
  100% {
    opacity: 0;
    transform: scale(1.4);
  }
}

.hub-label {
  margin-top: 24px;
  font-size: 18px;
  font-weight: 800;
  color: $gray-darkness;
  letter-spacing: 2px;
  text-transform: uppercase;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);

  @media (max-width: $mobile) {
    font-size: 14px;
  }
}

// ============================================
// CRISTAIS ORBITANDO COM THREE.JS
// ============================================

.crystal-orbit {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 120px;
  height: 140px;
  cursor: pointer;
  opacity: 0;
  transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
  transition-delay: var(--orbit-delay, 0s);
  perspective: 1000px;

  .interactive-dashboard.is-visible & {
    opacity: 1;
  }

  &.rotating {
    animation: gentleFloat 5s ease-in-out infinite;
  }

  &:hover,
  &.active {
    transform: var(--base-transform) scale(1.15) !important;
    z-index: 15;

    .crystal-info {
      opacity: 1;
      transform: translateY(0);
      pointer-events: all;
    }
  }

  @media (max-width: $tablet) {
    width: 100px;
    height: 120px;
  }

  @media (max-width: $mobile) {
    width: 80px;
    height: 100px;
  }
}

@keyframes gentleFloat {
  0%, 100% {
    transform: var(--base-transform) translateY(0);
  }
  50% {
    transform: var(--base-transform) translateY(-15px);
  }
}

// ============================================
// INFO CARD (HOVER)
// ============================================

.crystal-info {
  position: absolute;
  top: 120%;
  left: 50%;
  transform: translate(-50%, 20px);
  width: 220px;
  padding: 20px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  opacity: 0;
  pointer-events: none;
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  z-index: 100;

  &::before {
    content: '';
    position: absolute;
    top: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 10px solid transparent;
    border-right: 10px solid transparent;
    border-bottom: 10px solid white;
  }

  @media (max-width: $tablet) {
    width: 200px;
    padding: 16px;
  }

  @media (max-width: $mobile) {
    width: 180px;
    padding: 14px;
    top: 100%;
  }
}

.info-icon {
  width: 48px;
  height: 48px;
  display: inline-block;
  margin: 0 auto 12px;
  filter: brightness(0) saturate(100%) invert(56%) sepia(89%) saturate(1567%) hue-rotate(359deg) brightness(95%) contrast(88%);

  @media (max-width: $mobile) {
    width: 36px;
    height: 36px;
  }
}

.info-title {
  font-size: 18px;
  font-weight: 700;
  color: $gray-darkness;
  margin: 0 0 10px;
  text-align: center;

  @media (max-width: $mobile) {
    font-size: 16px;
  }
}

.info-description {
  font-size: 14px;
  color: $gray-medium;
  line-height: 1.6;
  text-align: center;
  margin: 0 0 12px;

  @media (max-width: $mobile) {
    font-size: 13px;
  }
}

.info-stat {
  text-align: center;
  margin-top: 12px;
  padding-top: 12px;
  border-top: 2px solid rgba(231, 126, 34, 0.1);
}

.stat-value {
  display: inline-block;
  padding: 8px 20px;
  background: linear-gradient(135deg, rgba(231, 126, 34, 0.15), rgba(211, 84, 0, 0.15));
  border: 2px solid $p-color;
  border-radius: 25px;
  color: $p-color;
  font-size: 18px;
  font-weight: 900;
  letter-spacing: 1px;

  @media (max-width: $mobile) {
    font-size: 16px;
    padding: 6px 16px;
  }
}

// Click ripple effect
.click-ripple {
  position: absolute;
  inset: -20px;
  background: radial-gradient(circle, var(--crystal-main) 0%, transparent 70%);
  border-radius: 50%;
  opacity: 0.6;
  animation: crystalRipple 0.6s ease;
  pointer-events: none;
}

@keyframes crystalRipple {
  0% {
    opacity: 0.6;
    transform: scale(0.5);
  }
  100% {
    opacity: 0;
    transform: scale(2);
  }
}

// ============================================
// ACHIEVEMENT TOAST
// ============================================

.achievement-toast {
  position: fixed;
  top: 100px;
  right: 40px;
  padding: 16px 24px;
  background: linear-gradient(135deg, $p-color, $p-dark);
  color: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(231, 126, 34, 0.4);
  display: flex;
  align-items: center;
  gap: 12px;
  font-weight: 600;
  z-index: 1000;

  i {
    font-size: 24px;
  }

  @media (max-width: $mobile) {
    right: 20px;
    padding: 12px 16px;
    font-size: 14px;
  }
}

.toast-slide-enter-active,
.toast-slide-leave-active {
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.toast-slide-enter-from {
  opacity: 0;
  transform: translateX(100px);
}

.toast-slide-leave-to {
  opacity: 0;
  transform: translateY(-50px);
}
</style>
