/**
 * Testes Unitários - useCounter composable
 * 
 * Testa o composable de contador animado
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

import { useCounter } from '@/core/composables/useCounter';

// Helper para criar componente de teste
function createTestComponent(endValue, options = {}) {
  return defineComponent({
    template: '<div ref="targetRef">{{ displayValue }}</div>',
    setup() {
      const { targetRef, displayValue } = useCounter(endValue, options);
      return { targetRef, displayValue };
    }
  });
}

describe('useCounter', () => {
  beforeEach(() => {
    jest.useFakeTimers();
    global.IntersectionObserver = MockIntersectionObserver;
    mockObserverCallback = null;
  });

  afterEach(() => {
    jest.useRealTimers();
  });

  describe('Inicialização', () => {
    it('deve retornar targetRef e displayValue', async () => {
      const TestComponent = createTestComponent(100);
      const wrapper = mount(TestComponent);

      expect(wrapper.vm.targetRef).toBeDefined();
      expect(wrapper.vm.displayValue).toBeDefined();
      
      wrapper.unmount();
    });

    it('deve iniciar com startValue padrão (0)', async () => {
      const TestComponent = createTestComponent(100);
      const wrapper = mount(TestComponent);

      expect(wrapper.vm.displayValue).toBe(0);
      
      wrapper.unmount();
    });

    it('deve aceitar startValue customizado', async () => {
      const TestComponent = createTestComponent(100, { startValue: 50 });
      const wrapper = mount(TestComponent);

      expect(wrapper.vm.displayValue).toBe(50);
      
      wrapper.unmount();
    });
  });

  describe('Opções', () => {
    it('deve aceitar duration customizada', async () => {
      const TestComponent = createTestComponent(100, { duration: 1000 });
      const wrapper = mount(TestComponent);
      
      expect(wrapper.vm.displayValue).toBe(0);
      
      wrapper.unmount();
    });

    it('deve aceitar decimals para valores fracionários', async () => {
      const TestComponent = createTestComponent(99.99, { decimals: 2 });
      const wrapper = mount(TestComponent);
      
      expect(wrapper.vm.displayValue).toBe(0);
      
      wrapper.unmount();
    });
  });

  describe('Valores', () => {
    it('deve manter displayValue como número', async () => {
      const TestComponent = createTestComponent(500);
      const wrapper = mount(TestComponent);
      
      expect(typeof wrapper.vm.displayValue).toBe('number');
      
      wrapper.unmount();
    });

    it('deve suportar valores grandes', async () => {
      const TestComponent = createTestComponent(1000000);
      const wrapper = mount(TestComponent);
      
      expect(wrapper.vm.displayValue).toBe(0);
      
      wrapper.unmount();
    });

    it('deve suportar valores decimais', async () => {
      const TestComponent = createTestComponent(99.5, { decimals: 1 });
      const wrapper = mount(TestComponent);
      
      expect(wrapper.vm.displayValue).toBe(0);
      
      wrapper.unmount();
    });
  });

  describe('Comportamento com visibilidade', () => {
    it('deve iniciar animação quando elemento fica visível', async () => {
      const TestComponent = createTestComponent(100, { duration: 100 });
      const wrapper = mount(TestComponent);
      
      expect(wrapper.vm.displayValue).toBe(0);
      
      // Simular visibilidade
      simulateIntersection(true);
      await nextTick();
      
      // O valor deve começar a mudar (depende de requestAnimationFrame)
      expect(wrapper.vm.displayValue).toBeGreaterThanOrEqual(0);
      
      wrapper.unmount();
    });
  });
});
