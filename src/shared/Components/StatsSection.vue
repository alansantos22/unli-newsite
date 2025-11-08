<template>
  <section class="stats-section" :class="variant">
    <div class="stats-container">
      <!-- Header -->
      <div v-if="title || description" class="stats-header">
        <h2 v-if="title" class="stats-title">{{ title }}</h2>
        <p v-if="description" class="stats-description">{{ description }}</p>
      </div>

      <!-- Stats Grid -->
      <div class="stats-grid">
        <div 
          v-for="(stat, index) in stats" 
          :key="index"
          class="stat-item"
          :style="{ animationDelay: `${index * 0.1}s` }"
        >
          <div class="stat-icon" v-if="stat.icon" :style="{ background: stat.iconBg || iconBgDefault }">
            {{ stat.icon }}
          </div>
          
          <div ref="counterRefs[index]" class="stat-value">
            {{ formatValue(displayValues[index] || 0, stat.prefix, stat.suffix) }}
          </div>
          
          <div class="stat-label">{{ stat.label }}</div>
          
          <div v-if="stat.change" class="stat-change" :class="stat.changeType">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path 
                :d="stat.changeType === 'increase' ? 'M5 15l7-7 7 7' : 'M5 9l7 7 7-7'"
                stroke-width="2" 
                stroke-linecap="round" 
                stroke-linejoin="round"
              />
            </svg>
            {{ stat.change }}
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { ref, onMounted, watch } from 'vue';
import { useCounter } from '@/core/composables/useCounter';

export default {
  name: 'StatsSection',
  props: {
    variant: {
      type: String,
      default: 'light', // light, dark, gradient
      validator: (value) => ['light', 'dark', 'gradient'].includes(value),
    },
    title: {
      type: String,
      default: '',
    },
    description: {
      type: String,
      default: '',
    },
    stats: {
      type: Array,
      required: true,
      // Example: [{ value: 158, label: 'Projects', icon: '🎮', prefix: '', suffix: '+', iconBg: '#ff6b35' }]
    },
    iconBgDefault: {
      type: String,
      default: 'linear-gradient(135deg, #e67e22 0%, #d35400 100%)',
    },
  },
  setup(props) {
    const counterRefs = ref([]);
    const displayValues = ref([]);

    onMounted(() => {
      props.stats.forEach((stat, index) => {
        const { targetRef, displayValue } = useCounter(stat.value, {
          duration: 2500,
          decimals: stat.decimals || 0,
        });

        counterRefs.value[index] = targetRef;
        
        watch(displayValue, (newValue) => {
          displayValues.value[index] = newValue;
        });
      });
    });

    const formatValue = (value, prefix = '', suffix = '') => {
      return `${prefix}${value}${suffix}`;
    };

    return {
      counterRefs,
      displayValues,
      formatValue,
    };
  },
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/__colors.scss';
@import '@/assets/sass/settings/__fonts.scss';
@import '@/assets/sass/settings/__keyframes.scss';

.stats-section {
  padding: 100px 0;
  transition: all $duration-normal $ease-out-smooth;

  &.light {
    background: $white;
  }

  &.dark {
    background: $gray-darkness;

    .stats-title {
      color: $white;
    }

    .stats-description {
      color: $gray;
    }

    .stat-label {
      color: $gray;
    }
  }

  &.gradient {
    background: $gradient-primary;

    .stats-title,
    .stats-description,
    .stat-value,
    .stat-label {
      color: $white;
    }
  }

  @media (max-width: 768px) {
    padding: 60px 0;
  }
}

.stats-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 40px;

  @media (max-width: 768px) {
    padding: 0 24px;
  }
}

// === HEADER ===
.stats-header {
  text-align: center;
  margin-bottom: 80px;

  @media (max-width: 768px) {
    margin-bottom: 60px;
  }
}

.stats-title {
  font-family: $font-display;
  font-size: $text-display-md;
  font-weight: $weight-black;
  color: $gray-darkness;
  margin-bottom: 16px;
  letter-spacing: $tracking-tight;

  @media (max-width: 768px) {
    font-size: $text-display-sm;
  }
}

.stats-description {
  font-family: $font-primary;
  font-size: $text-lg;
  color: $gray-medium;
  max-width: 600px;
  margin: 0 auto;

  @media (max-width: 768px) {
    font-size: $text-base;
  }
}

// === STATS GRID ===
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 48px;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
    gap: 40px;
  }
}

.stat-item {
  text-align: center;
  padding: 40px 24px;
  background: transparent;
  border-radius: $card-radius-lg;
  transition: all $duration-normal $ease-out-smooth;
  opacity: 0;
  animation: $fadeInUp $duration-slow $ease-out-smooth forwards;

  &:hover {
    transform: translateY(-8px);
    background: rgba($white, 0.05);

    .stat-icon {
      transform: scale(1.1) rotate(5deg);
    }
  }

  @media (max-width: 768px) {
    padding: 32px 20px;
  }
}

.stat-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 80px;
  height: 80px;
  border-radius: 20px;
  font-size: 40px;
  margin-bottom: 24px;
  transition: all $duration-normal $ease-bounce;
  box-shadow: $shadow-lg;

  @media (max-width: 768px) {
    width: 64px;
    height: 64px;
    font-size: 32px;
  }
}

.stat-value {
  font-family: $font-display;
  font-size: $text-display-sm;
  font-weight: $weight-black;
  color: $p-color;
  line-height: 1;
  margin-bottom: 12px;
  letter-spacing: $tracking-tight;

  @media (max-width: 768px) {
    font-size: $text-h1;
  }
}

.stat-label {
  font-family: $font-primary;
  font-size: $text-base;
  font-weight: $weight-medium;
  color: $gray-medium;
  margin-bottom: 8px;

  @media (max-width: 768px) {
    font-size: $text-sm;
  }
}

.stat-change {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 12px;
  border-radius: 20px;
  font-family: $font-primary;
  font-size: $text-xs;
  font-weight: $weight-semibold;
  margin-top: 8px;

  svg {
    width: 16px;
    height: 16px;
  }

  &.increase {
    background: rgba($success, 0.1);
    color: $success;
  }

  &.decrease {
    background: rgba($error, 0.1);
    color: $error;
  }
}
</style>
