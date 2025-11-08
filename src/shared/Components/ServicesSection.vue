<template>
  <section class="services-section" :class="variant">
    <div class="services-container">
      <!-- Header -->
      <div class="section-header" ref="headerRef" :class="{ 'animate-in': headerVisible }">
        <div class="header-badge" v-if="badge">
          <span>{{ badge }}</span>
        </div>
        <h2 class="section-title" v-html="title"></h2>
        <p class="section-description" v-if="description">{{ description }}</p>
      </div>

      <!-- Services Grid -->
      <div class="services-grid">
        <ModernCard
          v-for="(service, index) in services"
          :key="index"
          :variant="cardVariant"
          :image="service.image"
          :icon="service.icon"
          :iconBg="service.iconBg"
          :category="service.category"
          :title="service.title"
          :description="service.description"
          :actionText="service.actionText || 'Learn More'"
          @click="handleServiceClick(service, index)"
        />
      </div>
    </div>
  </section>
</template>

<script>
import { useIntersectionObserver } from '@/core/composables/useIntersectionObserver';
import ModernCard from '@/shared/Components/ModernCard.vue';

export default {
  name: 'ServicesSection',
  components: {
    ModernCard,
  },
  props: {
    variant: {
      type: String,
      default: 'light',
      validator: (value) => ['light', 'dark'].includes(value),
    },
    cardVariant: {
      type: String,
      default: 'light',
    },
    badge: {
      type: String,
      default: '',
    },
    title: {
      type: String,
      required: true,
    },
    description: {
      type: String,
      default: '',
    },
    services: {
      type: Array,
      required: true,
      // Example: [{ title: '', description: '', image: '', icon: '', category: '', actionText: '' }]
    },
  },
  emits: ['service-click'],
  setup(props, { emit }) {
    const { targetRef: headerRef, isVisible: headerVisible } = useIntersectionObserver({
      threshold: 0.3,
      triggerOnce: true,
    });

    const handleServiceClick = (service, index) => {
      emit('service-click', { service, index });
    };

    return {
      headerRef,
      headerVisible,
      handleServiceClick,
    };
  },
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/__colors.scss';
@import '@/assets/sass/settings/__fonts.scss';
@import '@/assets/sass/settings/__keyframes.scss';

.services-section {
  padding: 120px 0;
  position: relative;

  &.light {
    background: $gray-lightness;
  }

  &.dark {
    background: $gray-darkness;

    .section-title {
      color: $white;
    }

    .section-description {
      color: $gray;
    }

    .header-badge {
      background: rgba($white, 0.1);
      color: $white;
    }
  }

  @media (max-width: 768px) {
    padding: 80px 0;
  }
}

.services-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 40px;

  @media (max-width: 768px) {
    padding: 0 24px;
  }
}

// === HEADER ===
.section-header {
  text-align: center;
  margin-bottom: 80px;
  opacity: 0;
  transform: translateY(30px);
  transition: all 0.8s $ease-out-smooth;

  &.animate-in {
    opacity: 1;
    transform: translateY(0);
  }

  @media (max-width: 768px) {
    margin-bottom: 60px;
  }
}

.header-badge {
  display: inline-block;
  padding: 8px 20px;
  background: rgba($p-color, 0.1);
  color: $p-dark;
  font-family: $font-primary;
  font-size: $text-sm;
  font-weight: $weight-semibold;
  border-radius: 50px;
  margin-bottom: 24px;
  text-transform: uppercase;
  letter-spacing: $tracking-wider;
}

.section-title {
  font-family: $font-display;
  font-size: $text-display-md;
  font-weight: $weight-black;
  color: $gray-darkness;
  margin-bottom: 24px;
  line-height: $leading-tight;
  letter-spacing: $tracking-tight;

  @media (max-width: 768px) {
    font-size: $text-display-sm;
  }
}

.section-description {
  font-family: $font-primary;
  font-size: $text-lg;
  color: $gray-medium;
  max-width: 700px;
  margin: 0 auto;
  line-height: $leading-relaxed;

  @media (max-width: 768px) {
    font-size: $text-base;
  }
}

// === SERVICES GRID ===
.services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 32px;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
    gap: 24px;
  }
}
</style>
