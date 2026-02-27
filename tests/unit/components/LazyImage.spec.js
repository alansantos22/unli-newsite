/**
 * Testes Unitários - LazyImage.vue
 * 
 * Testa o componente de carregamento lazy de imagens
 */

import { mount } from '@vue/test-utils';
import { ref } from 'vue';

// Mock do composable antes de importar o componente
jest.mock('@/core/composables/useLazyImage', () => ({
  useLazyImage: jest.fn()
}));

import LazyImage from '@/shared/Components/LazyImage.vue';
import { useLazyImage } from '@/core/composables/useLazyImage';

// Helper para criar mocks com refs reais
const createMockReturn = (overrides = {}) => ({
  targetRef: ref(null),
  currentSrc: ref(overrides.currentSrc ?? ''),
  isLoaded: ref(overrides.isLoaded ?? false),
  isLoading: ref(overrides.isLoading ?? false),
  hasError: ref(overrides.hasError ?? false)
});

describe('LazyImage.vue', () => {
  beforeEach(() => {
    useLazyImage.mockClear();
    // Default mock
    useLazyImage.mockReturnValue(createMockReturn());
  });

  describe('Renderização básica', () => {
    it('deve renderizar o wrapper corretamente', () => {
      const wrapper = mount(LazyImage, {
        props: { src: '/test.jpg' }
      });

      expect(wrapper.find('.lazy-image-wrapper').exists()).toBe(true);
    });

    it('deve chamar useLazyImage com o src correto', () => {
      mount(LazyImage, {
        props: { src: '/my-image.jpg' }
      });

      expect(useLazyImage).toHaveBeenCalledWith('/my-image.jpg');
    });
  });

  describe('Estado de carregamento (skeleton)', () => {
    it('deve mostrar skeleton quando imagem não está carregada', () => {
      useLazyImage.mockReturnValue(createMockReturn({ isLoaded: false }));

      const wrapper = mount(LazyImage, {
        props: { src: '/test.jpg' }
      });

      expect(wrapper.find('.skeleton-loader').exists()).toBe(true);
    });

    it('deve esconder skeleton quando imagem está carregada', () => {
      useLazyImage.mockReturnValue(createMockReturn({ 
        currentSrc: '/test.jpg', 
        isLoaded: true 
      }));

      const wrapper = mount(LazyImage, {
        props: { src: '/test.jpg' }
      });

      expect(wrapper.find('.skeleton-loader').exists()).toBe(false);
    });
  });

  describe('Exibição da imagem', () => {
    it('deve renderizar img quando currentSrc tem valor', () => {
      useLazyImage.mockReturnValue(createMockReturn({ 
        currentSrc: '/loaded-image.jpg', 
        isLoaded: true 
      }));

      const wrapper = mount(LazyImage, {
        props: { src: '/test.jpg', alt: 'Test image' }
      });

      const img = wrapper.find('img.lazy-image');
      expect(img.exists()).toBe(true);
      expect(img.attributes('src')).toBe('/loaded-image.jpg');
    });

    it('deve passar alt corretamente para a imagem', () => {
      useLazyImage.mockReturnValue(createMockReturn({ 
        currentSrc: '/test.jpg', 
        isLoaded: true 
      }));

      const wrapper = mount(LazyImage, {
        props: { src: '/test.jpg', alt: 'Descrição da imagem' }
      });

      expect(wrapper.find('img').attributes('alt')).toBe('Descrição da imagem');
    });

    it('deve adicionar classe loaded quando isLoaded é true', () => {
      useLazyImage.mockReturnValue(createMockReturn({ 
        currentSrc: '/test.jpg', 
        isLoaded: true 
      }));

      const wrapper = mount(LazyImage, {
        props: { src: '/test.jpg' }
      });

      expect(wrapper.find('img.lazy-image').classes()).toContain('loaded');
    });

    it('não deve ter classe loaded quando isLoaded é false', () => {
      useLazyImage.mockReturnValue(createMockReturn({ 
        currentSrc: '/test.jpg', 
        isLoaded: false 
      }));

      const wrapper = mount(LazyImage, {
        props: { src: '/test.jpg' }
      });

      expect(wrapper.find('img.lazy-image').classes()).not.toContain('loaded');
    });
  });

  describe('Estado de erro', () => {
    it('deve mostrar placeholder de erro quando hasError é true', () => {
      useLazyImage.mockReturnValue(createMockReturn({ hasError: true }));

      const wrapper = mount(LazyImage, {
        props: { src: '/invalid.jpg' }
      });

      expect(wrapper.find('.error-placeholder').exists()).toBe(true);
    });

    it('deve conter SVG no placeholder de erro', () => {
      useLazyImage.mockReturnValue(createMockReturn({ hasError: true }));

      const wrapper = mount(LazyImage, {
        props: { src: '/invalid.jpg' }
      });

      expect(wrapper.find('.error-placeholder svg').exists()).toBe(true);
    });

    it('não deve mostrar placeholder de erro quando não há erro', () => {
      useLazyImage.mockReturnValue(createMockReturn({ 
        currentSrc: '/test.jpg', 
        isLoaded: true, 
        hasError: false 
      }));

      const wrapper = mount(LazyImage, {
        props: { src: '/test.jpg' }
      });

      expect(wrapper.find('.error-placeholder').exists()).toBe(false);
    });
  });

  describe('Props', () => {
    it('deve ter src como prop obrigatória', () => {
      const srcProp = LazyImage.props.src;
      expect(srcProp.required).toBe(true);
      expect(srcProp.type).toBe(String);
    });

    it('deve ter alt como prop opcional com padrão vazio', () => {
      const altProp = LazyImage.props.alt;
      expect(altProp.default).toBe('');
      expect(altProp.type).toBe(String);
    });
  });
});
