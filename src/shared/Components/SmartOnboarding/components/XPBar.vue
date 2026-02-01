<template>
  <div class="xp-bar">
    <!-- Level Badge -->
    <div class="level-badge">
      <span class="level-icon">{{ level?.icon || '📝' }}</span>
      <div class="level-info">
        <span class="level-name">{{ level?.name || 'Rascunho' }}</span>
        <span class="level-number">Nível {{ level?.level || 1 }}</span>
      </div>
    </div>
    
    <!-- XP Progress -->
    <div class="xp-progress">
      <div class="xp-header">
        <span class="xp-current">{{ xp }} XP</span>
        <span v-if="nextLevel" class="xp-next">/ {{ nextLevel.minXP }} XP</span>
      </div>
      
      <div class="progress-track">
        <div 
          class="progress-fill" 
          :style="{ 
            width: progress + '%',
            background: level?.color || '#6C5CE7'
          }"
        >
          <div class="progress-glow"></div>
        </div>
      </div>
      
      <div v-if="nextLevel" class="next-level-hint">
        <span class="hint-icon">{{ nextLevel.icon }}</span>
        <span class="hint-text">{{ nextLevel.name }} em {{ nextLevel.minXP - xp }} XP</span>
      </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="quick-stats">
      <div class="stat-item">
        <span class="stat-value">{{ filledFields }}</span>
        <span class="stat-label">campos</span>
      </div>
      <div class="stat-divider"></div>
      <div class="stat-item">
        <span class="stat-value">{{ completedSteps }}</span>
        <span class="stat-label">etapas</span>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';
import { useOnboardingState } from '../composables/useOnboardingState.js';
import { FIELD_STATUS, ONBOARDING_STEPS } from '../types/onboarding.types.js';

export default {
  name: 'XPBar',
  
  props: {
    xp: {
      type: Number,
      required: true
    },
    level: {
      type: Object,
      default: null
    },
    progress: {
      type: Number,
      default: 0
    },
    nextLevel: {
      type: Object,
      default: null
    }
  },
  
  setup() {
    const { state } = useOnboardingState();
    
    const filledFields = computed(() => {
      let count = 0;
      Object.values(state.fieldStatus).forEach(status => {
        if (status === FIELD_STATUS.ANSWERED || status === FIELD_STATUS.CONFIRMED) {
          count++;
        }
      });
      return count;
    });
    
    const completedSteps = computed(() => {
      let count = 0;
      ONBOARDING_STEPS.forEach(step => {
        const requiredFields = step.fields.filter(f => f.required);
        if (requiredFields.length === 0) {
          // Step sem campos obrigatórios - conta como completo se tem algum preenchido
          const hasFilled = step.fields.some(f => 
            state.fieldStatus[f.id] === FIELD_STATUS.ANSWERED ||
            state.fieldStatus[f.id] === FIELD_STATUS.CONFIRMED
          );
          if (hasFilled) count++;
        } else {
          // Verifica se todos os obrigatórios estão preenchidos
          const allRequiredFilled = requiredFields.every(f => 
            state.fieldStatus[f.id] === FIELD_STATUS.ANSWERED ||
            state.fieldStatus[f.id] === FIELD_STATUS.CONFIRMED
          );
          if (allRequiredFilled) count++;
        }
      });
      return count;
    });
    
    return {
      filledFields,
      completedSteps
    };
  }
};
</script>

<style lang="scss" scoped>
$primary: #6C5CE7;
$primary-light: #A29BFE;
$secondary: #00CEC9;
$background-card: #1a1a2e;
$text-primary: #ffffff;
$text-secondary: rgba(255, 255, 255, 0.7);
$border-color: rgba(255, 255, 255, 0.1);
$warning: #FDCB6E;

.xp-bar {
  padding: 1.25rem;
  background: rgba(0, 0, 0, 0.3);
  border-top: 1px solid $border-color;
}

// ============================================
// LEVEL BADGE
// ============================================

.level-badge {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
  
  .level-icon {
    font-size: 2rem;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
  }
  
  .level-info {
    display: flex;
    flex-direction: column;
  }
  
  .level-name {
    font-size: 1rem;
    font-weight: 600;
    color: $text-primary;
  }
  
  .level-number {
    font-size: 0.75rem;
    color: $text-secondary;
  }
}

// ============================================
// XP PROGRESS
// ============================================

.xp-progress {
  margin-bottom: 1rem;
}

.xp-header {
  display: flex;
  align-items: baseline;
  gap: 0.25rem;
  margin-bottom: 0.5rem;
  
  .xp-current {
    font-size: 1.125rem;
    font-weight: 700;
    color: $warning;
  }
  
  .xp-next {
    font-size: 0.8125rem;
    color: $text-secondary;
  }
}

.progress-track {
  height: 8px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
  overflow: hidden;
  position: relative;
}

.progress-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.5s ease;
  position: relative;
  
  .progress-glow {
    position: absolute;
    top: 0;
    right: 0;
    width: 20px;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4));
    animation: progressGlow 2s infinite;
  }
}

@keyframes progressGlow {
  0%, 100% { opacity: 0; }
  50% { opacity: 1; }
}

.next-level-hint {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  margin-top: 0.5rem;
  
  .hint-icon {
    font-size: 0.875rem;
  }
  
  .hint-text {
    font-size: 0.6875rem;
    color: $text-secondary;
  }
}

// ============================================
// QUICK STATS
// ============================================

.quick-stats {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1.5rem;
  padding-top: 0.75rem;
  border-top: 1px solid $border-color;
}

.stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  
  .stat-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: $primary-light;
  }
  
  .stat-label {
    font-size: 0.6875rem;
    color: $text-secondary;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
}

.stat-divider {
  width: 1px;
  height: 24px;
  background: $border-color;
}
</style>
