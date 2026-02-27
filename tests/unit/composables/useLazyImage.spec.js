/**
 * Testes Unitários - useLazyImage composable
 * 
 * Testa o composable de carregamento lazy de imagens
 */

import { mount } from '@vue/test-utils';
import { defineComponent, nextTick } from 'vue';

// Mock do IntersectionObserver
let mockObserverCallback = null;

class MockIntersectionObserver {
  constructor(callback) {
    mockObserverCallback = callback;
  }
  observe() {}
  unobserve() {}
  disconnect() {}
}

function simulateIntersection(isIntersecting) {
  if (mockObserverCallback) {
    mockObserverCallback([{ isIntersecting, target: document.createElement('div') }]);
  }
}

import { useLazyImage } from '@/core/composables/useLazyImage';

// Helper para criar componente de teste
function createTestComponent(imageSrc) {
  return defineComponent({
    template: '<div ref="targetRef"><img v-if="currentSrc" :src="currentSrc" /></div>',
    setup() {
      const { targetRef, currentSrc, isLoaded, isLoading, hasError } = useLazyImage(imageSrc);
      return { targetRef, currentSrc, isLoaded, isLoading, hasError };
    }
  });
}

describe('useLazyImage', () => {
  beforeEach(() => {
    global.IntersectionObserver = MockIntersectionObserver;
    mockObserverCallback = null;
  });

  describe('Inicialização', () => {
    it('deve retornar propriedades corretas', async () => {
      const TestComponent = createTestComponent('/test-image.jpg');
      const wrapper = mount(TestComponent);

      expect(wrapper.vm.targetRef).toBeDefined();
      expect(wrapper.vm.currentSrc).toBeDefined();
      expect(wrapper.vm.isLoaded).toBeDefined();
      expect(wrapper.vm.isLoading).toBeDefined();
      expect(wrapper.vm.hasError).toBeDefined();
      
      wrapper.unmount();
    });

    it('deve iniciar com valores padrão', async () => {
      const TestComponent = createTestComponent('/test.jpg');
      const wrapper = mount(TestComponent);

      expect(wrapper.vm.currentSrc).toBe('');
      expect(wrapper.vm.isLoaded).toBe(false);
      expect(wrapper.vm.isLoading).toBe(false);
      expect(wrapper.vm.hasError).toBe(false);
      
      wrapper.unmount();
    });
  });

  describe('Sem src', () => {
    it('não deve tentar carregar quando src está vazio', async () => {
      const TestComponent = createTestComponent('');
      const wrapper = mount(TestComponent);

      expect(wrapper.vm.isLoading).toBe(false);
      expect(wrapper.vm.currentSrc).toBe('');
      
      wrapper.unmount();
    });
  });

  describe('Propriedades reativas', () => {
    it('deve ter currentSrc como string', async () => {
      const TestComponent = createTestComponent('/test.jpg');
      const wrapper = mount(TestComponent);
      
      expect(typeof wrapper.vm.currentSrc).toBe('string');
      
      wrapper.unmount();
    });

    it('deve ter isLoaded como boolean', async () => {
      const TestComponent = createTestComponent('/test.jpg');
      const wrapper = mount(TestComponent);
      
      expect(typeof wrapper.vm.isLoaded).toBe('boolean');
      
      wrapper.unmount();
    });

    it('deve ter hasError como boolean', async () => {
      const TestComponent = createTestComponent('/test.jpg');
      const wrapper = mount(TestComponent);
      
      expect(typeof wrapper.vm.hasError).toBe('boolean');
      
      wrapper.unmount();
    });
  });

  describe('targetRef', () => {
    it('deve fornecer targetRef', async () => {
      const TestComponent = createTestComponent('/test.jpg');
      const wrapper = mount(TestComponent);
      
      expect(wrapper.vm.targetRef).toBeDefined();
      
      wrapper.unmount();
    });
  });
});
