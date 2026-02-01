/**
 * ============================================
 * SMART ONBOARDING - INDEX
 * ============================================
 * 
 * Exporta todos os componentes e composables
 * do novo sistema de onboarding inteligente.
 */

// Componente principal
export { default as SmartOnboarding } from './SmartOnboarding.vue';

// Componentes filhos
export { default as ChatBubble } from './components/ChatBubble.vue';
export { default as FormPanel } from './components/FormPanel.vue';
export { default as FieldCard } from './components/FieldCard.vue';
export { default as XPBar } from './components/XPBar.vue';
export { default as ImageUploadButton } from './components/ImageUploadButton.vue';

// Composables
export { useOnboardingState } from './composables/useOnboardingState.js';
export { useGeminiBrain } from './composables/useGeminiBrain.js';
export { 
  fetchAddressByCEP,
  formatPhone,
  formatCEP,
  normalizeSocialUsername,
  soundManager
} from './composables/useHelpers.js';

// Types e constantes
export { 
  ONBOARDING_STEPS,
  FIELD_STATUS,
  SITE_LEVELS,
  ACHIEVEMENTS,
  WELCOME_MESSAGES,
  getStepById,
  getFieldById,
  getLevelByXP,
  getLevelProgress
} from './types/onboarding.types.js';
