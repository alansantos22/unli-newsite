// ==========================================
// COMPOSABLE: ANIMATED COUNTER
// Contador animado para estatísticas
// ==========================================

import { ref, watch } from 'vue';
import { useIntersectionObserver } from './useIntersectionObserver';

export function useCounter(endValue, options = {}) {
  const {
    duration = 2000,
    startValue = 0,
    decimals = 0,
  } = options;

  const displayValue = ref(startValue);
  const { targetRef, isVisible } = useIntersectionObserver({
    threshold: 0.5,
    triggerOnce: true,
  });

  const animateCounter = () => {
    const startTime = Date.now();
    const range = endValue - startValue;

    const updateCounter = () => {
      const currentTime = Date.now();
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);

      // Easing function (ease-out)
      const easedProgress = 1 - Math.pow(1 - progress, 3);
      
      const currentValue = startValue + (range * easedProgress);
      displayValue.value = Number(currentValue.toFixed(decimals));

      if (progress < 1) {
        requestAnimationFrame(updateCounter);
      } else {
        displayValue.value = endValue;
      }
    };

    requestAnimationFrame(updateCounter);
  };

  watch(isVisible, (visible) => {
    if (visible) {
      animateCounter();
    }
  });

  return {
    targetRef,
    displayValue,
  };
}
