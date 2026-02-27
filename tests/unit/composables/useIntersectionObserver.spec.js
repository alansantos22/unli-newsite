/**
 * Testes Unitários - useIntersectionObserver composable
 * 
 * Testa o composable de Intersection Observer para lazy loading e animações
 */

import { mount, flushPromises } from '@vue/test-utils';
import { defineComponent, nextTick } from 'vue';
import { useIntersectionObserver } from '@/core/composables/useIntersectionObserver';

// Mock do IntersectionObserver
let mockObserverCallback = null;
let mockObservedElements = [];

class MockIntersectionObserver {
  constructor(callback) {
    mockObserverCallback = callback;
  }

  observe(element) {
    mockObservedElements.push(element);
  }

  unobserve(element) {
    mockObservedElements = mockObservedElements.filter(el => el !== element);
  }

  disconnect() {
    mockObservedElements = [];
  }
}

// Função helper para simular interseção
function simulateIntersection(isIntersecting) {
  if (mockObserverCallback) {
    mockObserverCallback([
      { 
        isIntersecting,
        target: mockObservedElements[0] || document.createElement('div')
      }
    ]);
  }
}

// Helper para criar componente de teste
function createTestComponent(options = {}) {
  return defineComponent({
    template: '<div ref="targetRef">Test</div>',
    setup() {
      const { targetRef, isVisible } = useIntersectionObserver(options);
      return { targetRef, isVisible };
    }
  });
}

describe('useIntersectionObserver', () => {
  beforeEach(() => {
    global.IntersectionObserver = MockIntersectionObserver;
    mockObserverCallback = null;
    mockObservedElements = [];
  });

  describe('Inicialização', () => {
    it('deve retornar targetRef e isVisible', async () => {
      const TestComponent = createTestComponent();
      const wrapper = mount(TestComponent);
      
      expect(wrapper.vm.targetRef).toBeDefined();
      expect(wrapper.vm.isVisible).toBeDefined();
      expect(wrapper.vm.isVisible).toBe(false);
      
      wrapper.unmount();
    });

    it('deve iniciar com isVisible = false', async () => {
      const TestComponent = createTestComponent();
      const wrapper = mount(TestComponent);
      
      expect(wrapper.vm.isVisible).toBe(false);
      
      wrapper.unmount();
    });
  });

  describe('Opções padrão', () => {
    it('deve aceitar threshold customizado', async () => {
      const TestComponent = createTestComponent({ threshold: 0.5 });
      const wrapper = mount(TestComponent);
      
      expect(wrapper.vm.targetRef).toBeDefined();
      
      wrapper.unmount();
    });

    it('deve aceitar rootMargin customizado', async () => {
      const TestComponent = createTestComponent({ rootMargin: '100px' });
      const wrapper = mount(TestComponent);
      
      expect(wrapper.vm.targetRef).toBeDefined();
      
      wrapper.unmount();
    });
  });

  describe('Comportamento de visibilidade', () => {
    it('deve definir isVisible como true quando elemento entra na viewport', async () => {
      const TestComponent = createTestComponent();
      const wrapper = mount(TestComponent);
      
      await nextTick();
      simulateIntersection(true);
      await nextTick();

      expect(wrapper.vm.isVisible).toBe(true);
      
      wrapper.unmount();
    });

    it('deve manter isVisible false quando elemento não está visível', async () => {
      const TestComponent = createTestComponent();
      const wrapper = mount(TestComponent);
      
      await nextTick();
      simulateIntersection(false);
      await nextTick();

      expect(wrapper.vm.isVisible).toBe(false);
      
      wrapper.unmount();
    });
  });

  describe('triggerOnce behavior', () => {
    it('deve respeitar triggerOnce=true por padrão', async () => {
      const TestComponent = createTestComponent({ triggerOnce: true });
      const wrapper = mount(TestComponent);
      
      await nextTick();
      simulateIntersection(true);
      await nextTick();

      expect(wrapper.vm.isVisible).toBe(true);
      
      wrapper.unmount();
    });

    it('deve permitir reverter quando triggerOnce=false', async () => {
      const TestComponent = createTestComponent({ triggerOnce: false });
      const wrapper = mount(TestComponent);
      
      await nextTick();
      
      // Elemento entra na viewport
      simulateIntersection(true);
      await nextTick();
      expect(wrapper.vm.isVisible).toBe(true);

      // Elemento sai da viewport
      simulateIntersection(false);
      await nextTick();
      expect(wrapper.vm.isVisible).toBe(false);
      
      wrapper.unmount();
    });
  });
});
