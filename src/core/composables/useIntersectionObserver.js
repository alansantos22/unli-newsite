// ==========================================
// COMPOSABLE: INTERSECTION OBSERVER
// Para animações ao scroll e lazy loading
// ==========================================

import { ref, onMounted, onUnmounted } from 'vue';

export function useIntersectionObserver(options = {}) {
  const {
    threshold = 0.1,
    rootMargin = '0px',
    triggerOnce = true,
  } = options;

  const isVisible = ref(false);
  const targetRef = ref(null);
  let observer = null;

  const initObserver = () => {
    if (!targetRef.value) return;

    observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            isVisible.value = true;
            
            if (triggerOnce && observer) {
              observer.unobserve(entry.target);
            }
          } else if (!triggerOnce) {
            isVisible.value = false;
          }
        });
      },
      {
        threshold,
        rootMargin,
      }
    );

    observer.observe(targetRef.value);
  };

  onMounted(() => {
    initObserver();
  });

  onUnmounted(() => {
    if (observer && targetRef.value) {
      observer.unobserve(targetRef.value);
      observer = null;
    }
  });

  return {
    targetRef,
    isVisible,
  };
}
