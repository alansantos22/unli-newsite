<template>
  <div class="project-dashboard">
    <!-- Header com título e progresso -->
    <header class="dashboard-header">
      <div class="header-content">
        <h2 class="dashboard-title">
          <span class="title-icon">🚀</span>
          Seu Projeto
        </h2>
        <div class="progress-ring-mini">
          <svg viewBox="0 0 36 36" class="circular-chart">
            <path class="circle-bg"
              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
            />
            <path class="circle"
              :stroke-dasharray="`${progressPercent}, 100`"
              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
            />
          </svg>
          <span class="progress-text">{{ progressPercent }}%</span>
        </div>
      </div>
      <p class="dashboard-subtitle">
        Acompanhe o progresso enquanto conversamos
      </p>
    </header>

    <!-- Stepper Vertical (Navegação clara) -->
    <nav class="stepper-nav">
      <button
        v-for="(step, index) in steps"
        :key="step.id"
        class="stepper-item"
        :class="{
          'is-active': index === currentStepIndex,
          'is-completed': isStepComplete(step.id),
          'is-partial': isStepPartial(step.id) && !isStepComplete(step.id),
          'is-locked': index > currentStepIndex + 1
        }"
        @click="$emit('step-click', index)"
      >
        <div class="step-indicator">
          <span v-if="isStepComplete(step.id)" class="step-check">✓</span>
          <span v-else class="step-number">{{ index + 1 }}</span>
        </div>
        <div class="step-info">
          <span class="step-name">{{ step.name }}</span>
          <span class="step-status">
            {{ getStepStatusText(step.id, index) }}
          </span>
        </div>
        <span class="step-icon">{{ step.icon }}</span>
      </button>
    </nav>

    <!-- Área Principal: Campos do Step Atual -->
    <div class="current-step-area">
      <header class="current-step-header">
        <div class="step-badge">
          <span class="badge-icon">{{ currentStep?.icon }}</span>
          <span class="badge-text">{{ currentStep?.name }}</span>
        </div>
        <span class="fields-count">
          {{ getFilledFieldsCount(currentStep?.id) }}/{{ getTotalFieldsCount(currentStep?.id) }} campos
        </span>
      </header>
      
      <!-- Fields com Scroll -->
      <div class="fields-scroll-area">
        <div class="fields-grid">
          <!-- Campos Simples -->
          <template v-for="field in currentFields" :key="field.id">
            <!-- Array Fields (services, faq, phones) -->
            <ArrayFieldCard
              v-if="isArrayField(field.type)"
              :field="field"
              :value="formData[field.id]"
              @edit="(value) => $emit('field-edit', field.id, value)"
              @image-upload="(data) => $emit('image-upload', { fieldId: field.id, ...data })"
            />
            
            <!-- Simple Fields -->
            <FieldCard
              v-else
              :field="field"
              :value="formData[field.id]"
              :status="fieldStatus[field.id]"
              :is-shimmering="shimmeringFields.includes(field.id)"
              :is-recently-filled="recentlyFilled.includes(field.id)"
              @click="$emit('field-click', field.id)"
              @edit="(value) => $emit('field-edit', field.id, value)"
              @image-upload="(data) => $emit('image-upload', { fieldId: field.id, ...data })"
            />
          </template>
        </div>
      </div>
    </div>

    <!-- Footer Fixo -->
    <footer class="dashboard-footer">
      <button 
        class="btn-action btn-secondary"
        :disabled="currentStepIndex === 0"
        @click="$emit('step-click', currentStepIndex - 1)"
      >
        ← Anterior
      </button>
      <div class="footer-stats">
        <span class="stat-xp">⭐ {{ filledCount }} preenchidos</span>
      </div>
      <button 
        class="btn-action btn-primary"
        :disabled="currentStepIndex >= steps.length - 1"
        @click="$emit('step-click', currentStepIndex + 1)"
      >
        Próximo →
      </button>
    </footer>
  </div>
</template>

<script>
import { computed } from 'vue';
import { ONBOARDING_STEPS, FIELD_STATUS } from '../types/onboarding.types.js';
import FieldCard from './FieldCard.vue';
import ArrayFieldCard from './ArrayFieldCard.vue';

export default {
  name: 'FormPanel',
  
  components: {
    FieldCard,
    ArrayFieldCard
  },
  
  props: {
    formData: {
      type: Object,
      required: true
    },
    fieldStatus: {
      type: Object,
      required: true
    },
    currentStep: {
      type: Object,
      default: null
    },
    currentStepIndex: {
      type: Number,
      default: 0
    },
    shimmeringFields: {
      type: Array,
      default: () => []
    },
    recentlyFilled: {
      type: Array,
      default: () => []
    }
  },
  
  emits: ['field-click', 'field-edit', 'step-click', 'image-upload'],
  
  setup(props) {
    const steps = ONBOARDING_STEPS;
    
    const currentFields = computed(() => {
      if (!props.currentStep) return [];
      return props.currentStep.fields.filter(f => !f.hidden);
    });
    
    const filledCount = computed(() => {
      let count = 0;
      Object.values(props.fieldStatus).forEach(status => {
        if (status === FIELD_STATUS.ANSWERED || status === FIELD_STATUS.CONFIRMED) {
          count++;
        }
      });
      return count;
    });
    
    const totalCount = computed(() => {
      let count = 0;
      ONBOARDING_STEPS.forEach(step => {
        step.fields.forEach(field => {
          if (!field.hidden) count++;
        });
      });
      return count;
    });
    
    const progressPercent = computed(() => {
      if (totalCount.value === 0) return 0;
      return Math.round((filledCount.value / totalCount.value) * 100);
    });
    
    function getStepFields(stepId) {
      const step = ONBOARDING_STEPS.find(s => s.id === stepId);
      return step ? step.fields.filter(f => !f.hidden) : [];
    }
    
    function getFilledFieldsCount(stepId) {
      if (!stepId) return 0;
      const fields = getStepFields(stepId);
      return fields.filter(f => 
        props.fieldStatus[f.id] === FIELD_STATUS.ANSWERED || 
        props.fieldStatus[f.id] === FIELD_STATUS.CONFIRMED
      ).length;
    }
    
    function getTotalFieldsCount(stepId) {
      if (!stepId) return 0;
      return getStepFields(stepId).length;
    }
    
    function isStepComplete(stepId) {
      const fields = getStepFields(stepId);
      const requiredFields = fields.filter(f => f.required);
      
      // Se há campos obrigatórios, todos devem estar preenchidos
      if (requiredFields.length > 0) {
        const allRequiredFilled = requiredFields.every(f => 
          props.fieldStatus[f.id] === FIELD_STATUS.ANSWERED || 
          props.fieldStatus[f.id] === FIELD_STATUS.CONFIRMED
        );
        if (!allRequiredFilled) return false;
      }
      
      // Para steps sem campos obrigatórios, considerar completo apenas se:
      // - Pelo menos 50% dos campos foram preenchidos OU
      // - Todos os campos foram perguntados (respondidos ou skipped)
      const filledOrSkipped = fields.filter(f => 
        props.fieldStatus[f.id] === FIELD_STATUS.ANSWERED || 
        props.fieldStatus[f.id] === FIELD_STATUS.CONFIRMED ||
        props.fieldStatus[f.id] === FIELD_STATUS.SKIPPED
      );
      
      const filledOnly = fields.filter(f => 
        props.fieldStatus[f.id] === FIELD_STATUS.ANSWERED || 
        props.fieldStatus[f.id] === FIELD_STATUS.CONFIRMED
      );
      
      // Precisa ter pelo menos 1 campo preenchido E:
      // - Ou todos os campos foram tratados (respondidos ou skipados)
      // - Ou mais de 50% estão preenchidos
      const hasAtLeastOneFilled = filledOnly.length > 0;
      const allFieldsTreated = filledOrSkipped.length === fields.length;
      const majorityFilled = filledOnly.length >= Math.ceil(fields.length / 2);
      
      return hasAtLeastOneFilled && (allFieldsTreated || majorityFilled);
    }
    
    function isStepPartial(stepId) {
      const fields = getStepFields(stepId);
      const hasFilled = fields.some(f => 
        props.fieldStatus[f.id] === FIELD_STATUS.ANSWERED || 
        props.fieldStatus[f.id] === FIELD_STATUS.CONFIRMED
      );
      return hasFilled && !isStepComplete(stepId);
    }
    
    function getStepStatusText(stepId, index) {
      if (isStepComplete(stepId)) return 'Completo';
      if (isStepPartial(stepId)) return 'Em progresso';
      if (index === props.currentStepIndex) return 'Atual';
      if (index > props.currentStepIndex + 1) return 'Bloqueado';
      return 'Pendente';
    }
    
    function isArrayField(type) {
      return ['services', 'faq', 'phones'].includes(type);
    }
    
    return {
      steps,
      currentFields,
      filledCount,
      totalCount,
      progressPercent,
      getStepFields,
      getFilledFieldsCount,
      getTotalFieldsCount,
      isStepComplete,
      isStepPartial,
      getStepStatusText,
      isArrayField
    };
  }
};
</script>

<style lang="scss" scoped>
// ============================================
// VARIABLES (Flat UI Colors)
// ============================================

$primary: #6C5CE7;
$primary-light: #A29BFE;
$secondary: #00CEC9;
$background-dark: #0f0f1a;
$background-card: #1a1a2e;
$background-elevated: #252540;
$text-primary: #ffffff;
$text-secondary: rgba(255, 255, 255, 0.6);
$text-muted: rgba(255, 255, 255, 0.4);
$border-color: rgba(255, 255, 255, 0.08);
$success: #00B894;
$warning: #FDCB6E;
$error: #E17055;

// ============================================
// MAIN CONTAINER
// ============================================

.project-dashboard {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: linear-gradient(180deg, $background-dark 0%, darken($background-dark, 3%) 100%);
  overflow: hidden;
}

// ============================================
// HEADER
// ============================================

.dashboard-header {
  padding: 0.875rem 1.25rem 0.75rem;
  background: rgba(0, 0, 0, 0.3);
  border-bottom: 1px solid $border-color;
  flex-shrink: 0;
  
  .header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.25rem;
  }
  
  .dashboard-title {
    font-size: 1rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: $text-primary;
    
    .title-icon {
      font-size: 1rem;
    }
  }
  
  .dashboard-subtitle {
    font-size: 0.75rem;
    color: $text-secondary;
    margin: 0;
  }
}

// Progress Ring
.progress-ring-mini {
  position: relative;
  width: 44px;
  height: 44px;
  
  .circular-chart {
    width: 100%;
    height: 100%;
    
    .circle-bg {
      fill: none;
      stroke: rgba(255, 255, 255, 0.1);
      stroke-width: 3;
    }
    
    .circle {
      fill: none;
      stroke: $success;
      stroke-width: 3;
      stroke-linecap: round;
      transform: rotate(-90deg);
      transform-origin: 50% 50%;
      transition: stroke-dasharray 0.5s ease;
    }
  }
  
  .progress-text {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.6875rem;
    font-weight: 700;
    color: $text-primary;
  }
}

// ============================================
// STEPPER NAVIGATION
// ============================================

.stepper-nav {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
  padding: 0.5rem;
  background: rgba(0, 0, 0, 0.15);
  flex-shrink: 0;
  max-height: 160px;
  overflow-y: auto;
  border-bottom: 1px solid $border-color;
  
  &::-webkit-scrollbar {
    width: 3px;
  }
  
  &::-webkit-scrollbar-thumb {
    background: rgba($primary, 0.3);
    border-radius: 2px;
  }
}

.stepper-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  background: transparent;
  border: 1px solid transparent;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: left;
  
  &:hover:not(.is-locked) {
    background: rgba(255, 255, 255, 0.04);
  }
  
  // Step Indicator (círculo com número)
  .step-indicator {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
    border: 2px solid $text-muted;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    color: $text-secondary;
    flex-shrink: 0;
    transition: all 0.2s;
  }
  
  .step-info {
    flex: 1;
    min-width: 0;
    
    .step-name {
      display: block;
      font-size: 0.8125rem;
      font-weight: 500;
      color: $text-secondary;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      transition: color 0.2s;
    }
    
    .step-status {
      display: block;
      font-size: 0.6875rem;
      color: $text-muted;
      margin-top: 0.125rem;
    }
  }
  
  .step-icon {
    font-size: 1rem;
    opacity: 0.5;
    transition: opacity 0.2s;
  }
  
  // Estados
  &.is-active {
    background: linear-gradient(135deg, rgba($primary, 0.15), rgba($primary, 0.05));
    border-color: rgba($primary, 0.4);
    
    .step-indicator {
      background: $primary;
      border-color: $primary;
      color: white;
      box-shadow: 0 0 12px rgba($primary, 0.4);
    }
    
    .step-name {
      color: $text-primary;
      font-weight: 600;
    }
    
    .step-status {
      color: $primary-light;
    }
    
    .step-icon {
      opacity: 1;
    }
  }
  
  &.is-completed {
    .step-indicator {
      background: $success;
      border-color: $success;
      color: white;
    }
    
    .step-name {
      color: $success;
    }
    
    .step-status {
      color: rgba($success, 0.7);
    }
  }
  
  &.is-partial {
    .step-indicator {
      border-color: $warning;
      color: $warning;
    }
    
    .step-status {
      color: $warning;
    }
  }
  
  &.is-locked {
    opacity: 0.4;
    cursor: not-allowed;
    
    .step-indicator {
      background: rgba(255, 255, 255, 0.04);
    }
  }
}

// ============================================
// CURRENT STEP AREA
// ============================================

.current-step-area {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-height: 0;
  overflow: hidden;
}

.current-step-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.875rem 1.25rem;
  background: linear-gradient(180deg, rgba($primary, 0.08) 0%, transparent 100%);
  border-bottom: 1px solid $border-color;
  flex-shrink: 0;
  
  .step-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    
    .badge-icon {
      font-size: 1.25rem;
    }
    
    .badge-text {
      font-size: 0.9375rem;
      font-weight: 600;
      color: $text-primary;
    }
  }
  
  .fields-count {
    font-size: 0.75rem;
    color: $text-secondary;
    background: rgba(255, 255, 255, 0.05);
    padding: 0.25rem 0.625rem;
    border-radius: 1rem;
  }
}

// Fields Scroll Area
.fields-scroll-area {
  flex: 1;
  overflow-y: auto;
  padding: 1rem 1.25rem;
  max-height: 580px;
  
  &::-webkit-scrollbar {
    width: 4px;
  }
  
  &::-webkit-scrollbar-track {
    background: transparent;
  }
  
  &::-webkit-scrollbar-thumb {
    background: rgba($primary, 0.3);
    border-radius: 2px;
    
    &:hover {
      background: rgba($primary, 0.5);
    }
  }
}

.fields-grid {
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
}

// ============================================
// FOOTER
// ============================================

.dashboard-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  background: rgba(0, 0, 0, 0.4);
  border-top: 1px solid $border-color;
  flex-shrink: 0;
  
  .footer-stats {
    flex: 1;
    display: flex;
    justify-content: center;
    
    .stat-xp {
      font-size: 0.75rem;
      color: $text-secondary;
    }
  }
}

// Action Buttons
.btn-action {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.8125rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 1px solid transparent;
  white-space: nowrap;
  
  &:disabled {
    opacity: 0.3;
    cursor: not-allowed;
  }
  
  &.btn-primary {
    background: linear-gradient(135deg, $primary, darken($primary, 10%));
    color: white;
    
    &:hover:not(:disabled) {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba($primary, 0.4);
    }
  }
  
  &.btn-secondary {
    background: rgba(255, 255, 255, 0.05);
    border-color: $border-color;
    color: $text-secondary;
    
    &:hover:not(:disabled) {
      background: rgba(255, 255, 255, 0.08);
      color: $text-primary;
    }
  }
}
</style>
