<template>
  <div 
    class="preview-field"
    :class="{
      'is-shimmering': isShimmering,
      'is-filled': isFilled,
      'is-recently-filled': isRecentlyFilled,
      'has-error': error
    }"
    @click="$emit('click')"
  >
    <span class="field-label">{{ label }}:</span>
    
    <div class="field-value-wrapper">
      <!-- Shimmer Effect -->
      <div v-if="isShimmering" class="shimmer-bar"></div>
      
      <!-- Value or Placeholder -->
      <span v-else class="field-value" :class="{ 'is-empty': !value }">
        {{ value || 'Não definido' }}
      </span>
      
      <!-- Recently Filled Indicator -->
      <span v-if="isRecentlyFilled" class="filled-check">✓</span>
    </div>
    
    <!-- Error -->
    <span v-if="error" class="field-error">{{ error }}</span>
  </div>
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'PreviewField',
  
  props: {
    fieldId: {
      type: String,
      required: true
    },
    label: {
      type: String,
      required: true
    },
    value: {
      type: [String, Number],
      default: ''
    },
    isShimmering: {
      type: Boolean,
      default: false
    },
    isRecentlyFilled: {
      type: Boolean,
      default: false
    },
    error: {
      type: String,
      default: null
    }
  },
  
  emits: ['click', 'edit'],
  
  setup(props) {
    const isFilled = computed(() => {
      return props.value !== '' && props.value !== null && props.value !== undefined;
    });
    
    return { isFilled };
  }
};
</script>

<style lang="scss" scoped>
.preview-field {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  cursor: pointer;
  transition: all 0.2s;
  
  &:hover {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 0.25rem;
    margin: 0 -0.5rem;
    padding: 0.5rem;
  }
  
  &:last-child {
    border-bottom: none;
  }
  
  &.is-recently-filled {
    .field-value-wrapper {
      animation: highlight-pulse 1s ease-out;
    }
  }
  
  &.has-error {
    .field-value {
      color: #ef4444;
    }
  }
}

@keyframes highlight-pulse {
  0% { 
    background: rgba(34, 197, 94, 0.3);
    border-radius: 0.25rem;
  }
  100% { 
    background: transparent;
  }
}

.field-label {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.5);
  min-width: 60px;
}

.field-value-wrapper {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  min-height: 20px;
}

.field-value {
  font-size: 0.875rem;
  font-weight: 500;
  
  &.is-empty {
    color: rgba(255, 255, 255, 0.3);
    font-weight: 400;
    font-style: italic;
  }
}

.shimmer-bar {
  width: 100%;
  height: 16px;
  background: linear-gradient(
    90deg,
    rgba(255, 255, 255, 0.05) 0%,
    rgba(255, 255, 255, 0.15) 50%,
    rgba(255, 255, 255, 0.05) 100%
  );
  background-size: 200% 100%;
  border-radius: 4px;
  animation: shimmer-slide 1.5s ease-in-out infinite;
}

@keyframes shimmer-slide {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.filled-check {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 18px;
  height: 18px;
  background: #22c55e;
  color: #fff;
  border-radius: 50%;
  font-size: 0.7rem;
  animation: check-bounce 0.4s ease-out;
}

@keyframes check-bounce {
  0% { transform: scale(0); }
  50% { transform: scale(1.3); }
  100% { transform: scale(1); }
}

.field-error {
  font-size: 0.7rem;
  color: #ef4444;
  margin-left: auto;
}
</style>
