<template>
  <section class="clients-section" :class="`variant-${variant}`">
    <div class="clients-container">
      <!-- Header -->
      <div class="section-header" :class="{ 'is-visible': isVisible }" ref="headerRef">
        <span v-if="badge" class="section-badge">{{ badge }}</span>
        <h2 class="section-title">{{ title }}</h2>
        <p v-if="description" class="section-description">{{ description }}</p>
      </div>

      <!-- Clients Grid -->
      <div class="clients-grid">
        <div 
          v-for="(client, index) in clients" 
          :key="client.name"
          class="client-card"
          :class="{ 'is-visible': isVisible }"
          :style="{ animationDelay: `${index * 0.1}s` }"
        >
          <div class="client-logo-wrapper">
            <img 
              :src="getClientImage(client.image)" 
              :alt="client.name"
              class="client-logo"
            />
          </div>
          <p class="client-name">{{ client.name }}</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { useIntersectionObserver } from '@/core/composables';

export default {
  name: 'ClientsSection',
  props: {
    variant: {
      type: String,
      default: 'light',
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
    clients: {
      type: Array,
      required: true,
      validator: (value) => {
        return value.every(client => client.name && client.image);
      }
    }
  },
  setup() {
    const { targetRef: headerRef, isVisible } = useIntersectionObserver({
      threshold: 0.2,
      triggerOnce: true
    });

    const getClientImage = (imageName) => {
      try {
        return require(`@/assets/img/marcas/${imageName}`);
      } catch (e) {
        console.warn(`Image not found: ${imageName}`);
        return '';
      }
    };

    return {
      headerRef,
      isVisible,
      getClientImage
    };
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/_colors.scss';
@import '@/assets/sass/settings/_fonts.scss';
@import '@/assets/sass/settings/_keyframes.scss';

.clients-section {
  padding: 6rem 2rem;
  position: relative;
  overflow: hidden;

  @media (max-width: 768px) {
    padding: 4rem 1rem;
  }

  &.variant-light {
    background: $white;
  }

  &.variant-dark {
    background: $gray-lightness;
  }
}

.clients-container {
  max-width: 1400px;
  margin: 0 auto;
}

// Section Header
.section-header {
  text-align: center;
  margin-bottom: 4rem;
  opacity: 0;
  transform: translateY(30px);
  transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);

  &.is-visible {
    opacity: 1;
    transform: translateY(0);
  }

  @media (max-width: 768px) {
    margin-bottom: 3rem;
  }
}

.section-badge {
  display: inline-block;
  padding: 0.5rem 1.25rem;
  background: rgba($p-color, 0.1);
  color: $p-color;
  font-family: $font-primary;
  font-size: $text-sm;
  font-weight: $weight-semibold;
  border-radius: 50px;
  margin-bottom: 1.5rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.section-title {
  font-family: $font-secondary;
  font-size: $text-display-sm;
  font-weight: $weight-bold;
  color: $gray-darkness;
  margin: 0 0 1rem;
  line-height: 1.2;

  @media (max-width: 768px) {
    font-size: $text-h1;
  }
}

.section-description {
  font-family: $font-primary;
  font-size: $text-lg;
  color: $gray-medium;
  max-width: 600px;
  margin: 0 auto;
  line-height: 1.6;

  @media (max-width: 768px) {
    font-size: $text-base;
  }
}

// Clients Grid
.clients-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 2rem;
  align-items: center;

  @media (max-width: 768px) {
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
  }
}

.client-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem 1.5rem;
  background: $white;
  border-radius: 1rem;
  border: 1px solid $gray-light;
  transition: all 0.3s ease;
  opacity: 0;
  transform: translateY(20px);

  &.is-visible {
    animation: fadeInUp 0.6s ease forwards;
  }

  &:hover {
    transform: translateY(-8px);
    box-shadow: $shadow-lg;
    border-color: rgba($p-color, 0.3);

    .client-logo {
      transform: scale(1.05);
    }
  }

  @media (max-width: 768px) {
    padding: 1.5rem 1rem;
  }
}

.client-logo-wrapper {
  width: 100%;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
}

.client-logo {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  transition: transform 0.3s ease;
  filter: grayscale(100%);
  opacity: 0.7;

  .client-card:hover & {
    filter: grayscale(0%);
    opacity: 1;
  }
}

.client-name {
  font-family: $font-primary;
  font-size: $text-sm;
  font-weight: $weight-medium;
  color: $gray-medium;
  text-align: center;
  margin: 0;
}
</style>
