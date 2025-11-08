<template>
  <div 
    ref="targetRef"
    class="modern-card"
    :class="[variant, { 'animate-in': isVisible, 'has-hover': hoverable }]"
    @click="handleClick"
  >
    <!-- Image/Icon Section -->
    <div v-if="image || icon" class="card-media">
      <LazyImage 
        v-if="image" 
        :src="image" 
        :alt="title"
        class="card-image"
      />
      <div v-else-if="icon" class="card-icon" :style="{ background: iconBg }">
        <component :is="icon" />
      </div>
    </div>

    <!-- Content Section -->
    <div class="card-content">
      <!-- Category Badge -->
      <span v-if="category" class="card-category">{{ category }}</span>

      <!-- Title -->
      <h3 class="card-title">{{ title }}</h3>

      <!-- Description -->
      <p class="card-description">{{ description }}</p>

      <!-- Action Button -->
      <button v-if="actionText" class="card-action">
        {{ actionText }}
        <svg class="action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <circle cx="12" cy="12" r="10" stroke-width="2"/>
          <path d="M12 8l4 4-4 4M8 12h8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>

    <!-- Hover Overlay -->
    <div v-if="hoverable" class="card-overlay"></div>
  </div>
</template>

<script>
import { useIntersectionObserver } from '@/core/composables/useIntersectionObserver';
import LazyImage from '@/shared/Components/LazyImage.vue';

export default {
  name: 'ModernCard',
  components: {
    LazyImage,
  },
  props: {
    variant: {
      type: String,
      default: 'light', // light, dark, gradient
      validator: (value) => ['light', 'dark', 'gradient'].includes(value),
    },
    image: {
      type: String,
      default: '',
    },
    icon: {
      type: String,
      default: '',
    },
    iconBg: {
      type: String,
      default: '',
    },
    category: {
      type: String,
      default: '',
    },
    title: {
      type: String,
      required: true,
    },
    description: {
      type: String,
      required: true,
    },
    actionText: {
      type: String,
      default: '',
    },
    hoverable: {
      type: Boolean,
      default: true,
    },
  },
  emits: ['click'],
  setup(props, { emit }) {
    const { targetRef, isVisible } = useIntersectionObserver({
      threshold: 0.1,
      triggerOnce: true,
    });

    const handleClick = () => {
      emit('click');
    };

    return {
      targetRef,
      isVisible,
      handleClick,
    };
  },
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/__colors.scss';
@import '@/assets/sass/settings/__fonts.scss';
@import '@/assets/sass/settings/__keyframes.scss';

.modern-card {
  position: relative;
  border-radius: $card-radius-lg;
  overflow: hidden;
  transition: all $duration-normal $ease-out-smooth;
  opacity: 0;
  transform: translateY(30px);

  &.animate-in {
    opacity: 1;
    transform: translateY(0);
  }

  &.has-hover {
    cursor: pointer;

    &:hover {
      transform: translateY(-8px);
      box-shadow: $shadow-2xl;

      .card-overlay {
        opacity: 1;
      }

      .card-image {
        transform: scale(1.1);
      }

      .action-icon {
        transform: translateX(4px);
      }
    }
  }

  // === VARIANTS ===
  &.light {
    background: $white;
    box-shadow: $shadow-lg;

    .card-title {
      color: $gray-darkness;
    }

    .card-description {
      color: $gray-medium;
    }
  }

  &.dark {
    background: $gray-darkness;
    box-shadow: $shadow-xl;

    .card-title {
      color: $white;
    }

    .card-description {
      color: $gray;
    }

    .card-category {
      background: rgba($white, 0.1);
      color: $white;
    }

    .card-action {
      color: $white;

      &:hover {
        background: rgba($p-color, 0.2);
      }
    }
  }

  &.gradient {
    background: $gradient-primary;
    box-shadow: $shadow-glow;

    .card-title,
    .card-description,
    .card-category,
    .card-action {
      color: $white;
    }
  }
}

// === MEDIA SECTION ===
.card-media {
  position: relative;
  width: 100%;
  height: 240px;
  overflow: hidden;
  background: $gray-lightness;

  @media (max-width: 768px) {
    height: 180px;
  }
}

.card-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform $duration-slow $ease-out-smooth;
}

.card-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  font-size: 64px;

  svg {
    width: 64px;
    height: 64px;
  }
}

// === CONTENT SECTION ===
.card-content {
  padding: 32px;

  @media (max-width: 768px) {
    padding: 24px;
  }
}

.card-category {
  display: inline-block;
  padding: 6px 12px;
  background: rgba($p-color, 0.1);
  color: $p-dark;
  font-family: $font-primary;
  font-size: $text-xs;
  font-weight: $weight-semibold;
  text-transform: uppercase;
  letter-spacing: $tracking-wider;
  border-radius: 6px;
  margin-bottom: 16px;
}

.card-title {
  font-family: $font-secondary;
  font-size: $text-h4;
  font-weight: $weight-bold;
  line-height: $leading-tight;
  margin-bottom: 12px;

  @media (max-width: 768px) {
    font-size: $text-h5;
  }
}

.card-description {
  font-family: $font-primary;
  font-size: $text-base;
  line-height: $leading-relaxed;
  margin-bottom: 24px;

  @media (max-width: 768px) {
    font-size: $text-sm;
  }
}

.card-action {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: transparent;
  border: none;
  color: $p-color;
  font-family: $font-primary;
  font-size: $text-sm;
  font-weight: $weight-semibold;
  border-radius: 50px;
  cursor: pointer;
  transition: all $duration-fast $ease-out-smooth;

  &:hover {
    background: rgba($p-color, 0.1);
  }

  .action-icon {
    width: 24px;
    height: 24px;
    transition: transform $duration-normal $ease-out-smooth;
  }
}

// === HOVER OVERLAY ===
.card-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(
    135deg,
    rgba($p-color, 0.1) 0%,
    rgba($p-dark, 0.05) 100%
  );
  opacity: 0;
  transition: opacity $duration-normal $ease-out-smooth;
  pointer-events: none;
}
</style>
