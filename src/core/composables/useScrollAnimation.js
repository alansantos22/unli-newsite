import { onMounted, onUnmounted } from 'vue';

export function useScrollAnimation() {
  let observers = [];

  const initScrollAnimations = () => {
    // Configuração do IntersectionObserver
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate-in');
          
          // Para animações stagger (sequenciais)
          const children = entry.target.querySelectorAll('[data-stagger]');
          children.forEach((child, index) => {
            setTimeout(() => {
              child.classList.add('animate-in');
            }, index * 100); // 100ms entre cada elemento
          });
        }
      });
    }, observerOptions);

    // Observar todos os elementos com atributo data-scroll
    const elements = document.querySelectorAll('[data-scroll]');
    elements.forEach(el => {
      observer.observe(el);
    });

    observers.push(observer);
  };

  onMounted(() => {
    // Pequeno delay para garantir que o DOM está pronto
    setTimeout(initScrollAnimations, 100);
  });

  onUnmounted(() => {
    observers.forEach(observer => observer.disconnect());
    observers = [];
  });

  return {
    initScrollAnimations
  };
}
