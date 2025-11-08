<template>
  <section class="testimonials-section" :class="`variant-${variant}`">
    <div class="testimonials-container">
      <!-- Header -->
      <div class="section-header">
        <span v-if="badge" class="section-badge">{{ badge }}</span>
        <h2 class="section-title">{{ title }}</h2>
        <p v-if="description" class="section-description">{{ description }}</p>
      </div>

      <!-- Testimonials Slider -->
      <div class="testimonials-slider">
        <button 
          class="slider-btn slider-btn-prev"
          @click="previousSlide"
          :disabled="currentSlide === 0"
        >
          <i class="fas fa-chevron-left"></i>
        </button>

        <div class="testimonials-track" ref="sliderTrack">
          <div 
            v-for="(testimonial, index) in testimonials" 
            :key="index"
            class="testimonial-card"
            :class="{ active: index === currentSlide }"
          >
            <div class="quote-icon">
              <i class="fas fa-quote-left"></i>
            </div>
            <p class="testimonial-text">{{ testimonial.comment }}</p>
            <div class="testimonial-author">
              <div class="author-info">
                <h4 class="author-name">{{ testimonial.name }}</h4>
                <p class="author-position">
                  {{ testimonial.position }}<span v-if="testimonial.company"> - <strong>{{ testimonial.company }}</strong></span>
                </p>
              </div>
            </div>
          </div>
        </div>

        <button 
          class="slider-btn slider-btn-next"
          @click="nextSlide"
          :disabled="currentSlide === testimonials.length - 1"
        >
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>

      <!-- Dots Indicator -->
      <div class="slider-dots">
        <button 
          v-for="(testimonial, index) in testimonials" 
          :key="index"
          class="dot"
          :class="{ active: index === currentSlide }"
          @click="goToSlide(index)"
        ></button>
      </div>
    </div>
  </section>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';

export default {
  name: 'TestimonialsSection',
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
    testimonials: {
      type: Array,
      required: true,
      validator: (value) => {
        return value.every(testimonial => 
          testimonial.name && testimonial.comment
        );
      }
    },
    autoPlay: {
      type: Boolean,
      default: true
    },
    autoPlayInterval: {
      type: Number,
      default: 5000
    }
  },
  setup(props) {
    const currentSlide = ref(0);
    const sliderTrack = ref(null);
    let autoPlayTimer = null;

    const nextSlide = () => {
      if (currentSlide.value < props.testimonials.length - 1) {
        currentSlide.value++;
      } else if (props.autoPlay) {
        currentSlide.value = 0;
      }
    };

    const previousSlide = () => {
      if (currentSlide.value > 0) {
        currentSlide.value--;
      }
    };

    const goToSlide = (index) => {
      currentSlide.value = index;
    };

    const startAutoPlay = () => {
      if (props.autoPlay) {
        autoPlayTimer = setInterval(() => {
          nextSlide();
        }, props.autoPlayInterval);
      }
    };

    const stopAutoPlay = () => {
      if (autoPlayTimer) {
        clearInterval(autoPlayTimer);
      }
    };

    onMounted(() => {
      startAutoPlay();
    });

    onUnmounted(() => {
      stopAutoPlay();
    });

    return {
      currentSlide,
      sliderTrack,
      nextSlide,
      previousSlide,
      goToSlide
    };
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/_colors.scss';
@import '@/assets/sass/settings/_fonts.scss';

.testimonials-section {
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

.testimonials-container {
  max-width: 1200px;
  margin: 0 auto;
}

// Section Header
.section-header {
  text-align: center;
  margin-bottom: 4rem;

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

// Testimonials Slider
.testimonials-slider {
  position: relative;
  display: flex;
  align-items: center;
  gap: 2rem;
  margin-bottom: 2rem;

  @media (max-width: 768px) {
    gap: 1rem;
  }
}

.slider-btn {
  width: 48px;
  height: 48px;
  background: $white;
  border: 2px solid $gray-light;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  flex-shrink: 0;

  i {
    font-size: 18px;
    color: $gray-darkness;
    transition: color 0.3s ease;
  }

  &:hover:not(:disabled) {
    background: $p-color;
    border-color: $p-color;

    i {
      color: $white;
    }
  }

  &:disabled {
    opacity: 0.3;
    cursor: not-allowed;
  }

  @media (max-width: 768px) {
    width: 40px;
    height: 40px;

    i {
      font-size: 14px;
    }
  }
}

.testimonials-track {
  flex: 1;
  overflow: hidden;
  position: relative;
  min-height: 400px;

  @media (max-width: 768px) {
    min-height: 450px;
  }
}

.testimonial-card {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  padding: 3rem;
  background: $white;
  border-radius: 1.5rem;
  box-shadow: $shadow-lg;
  opacity: 0;
  transform: translateX(100px);
  transition: all 0.5s ease;
  pointer-events: none;

  &.active {
    opacity: 1;
    transform: translateX(0);
    pointer-events: auto;
  }

  @media (max-width: 768px) {
    padding: 2rem;
  }
}

.quote-icon {
  width: 60px;
  height: 60px;
  background: $gradient-primary;
  border-radius: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.5rem;

  i {
    font-size: 28px;
    color: $white;
  }
}

.testimonial-text {
  font-family: $font-primary;
  font-size: $text-lg;
  color: $gray-darkness;
  line-height: 1.8;
  margin: 0 0 2rem;
  font-style: italic;

  @media (max-width: 768px) {
    font-size: $text-base;
  }
}

.testimonial-author {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding-top: 1.5rem;
  border-top: 1px solid $gray-light;
}

.author-info {
  flex: 1;
}

.author-name {
  font-family: $font-secondary;
  font-size: $text-lg;
  font-weight: $weight-semibold;
  color: $gray-darkness;
  margin: 0 0 0.25rem;
}

.author-position {
  font-family: $font-primary;
  font-size: $text-sm;
  color: $gray-medium;
  margin: 0;

  strong {
    color: $p-color;
    font-weight: $weight-semibold;
  }
}

// Slider Dots
.slider-dots {
  display: flex;
  justify-content: center;
  gap: 0.75rem;
}

.dot {
  width: 12px;
  height: 12px;
  background: $gray-light;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.3s ease;
  padding: 0;

  &.active {
    background: $p-color;
    width: 32px;
    border-radius: 6px;
  }

  &:hover:not(.active) {
    background: $gray-medium;
  }
}
</style>
