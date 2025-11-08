// ==========================================
// COMPOSABLE: LAZY IMAGE LOADING
// Carregamento inteligente de imagens
// ==========================================

import { ref, watch } from 'vue';
import { useIntersectionObserver } from './useIntersectionObserver';

export function useLazyImage(imageSrc) {
  const { targetRef, isVisible } = useIntersectionObserver({
    threshold: 0.01,
    rootMargin: '50px',
    triggerOnce: true,
  });

  const currentSrc = ref('');
  const isLoaded = ref(false);
  const isLoading = ref(false);
  const hasError = ref(false);

  const loadImage = () => {
    if (!imageSrc || isLoading.value || isLoaded.value) return;

    isLoading.value = true;
    const img = new Image();

    img.onload = () => {
      currentSrc.value = imageSrc;
      isLoaded.value = true;
      isLoading.value = false;
    };

    img.onerror = () => {
      hasError.value = true;
      isLoading.value = false;
    };

    img.src = imageSrc;
  };

  watch(isVisible, (visible) => {
    if (visible) {
      loadImage();
    }
  });

  return {
    targetRef,
    currentSrc,
    isLoaded,
    isLoading,
    hasError,
  };
}
