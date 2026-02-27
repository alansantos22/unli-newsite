/**
 * Testes Unitários - ModernButton.vue
 * 
 * Testa o componente de botão moderno com suas variantes,
 * estados de loading, disabled e ícones.
 */

import { mount } from '@vue/test-utils';
import ModernButton from '@/shared/Components/ModernButton.vue';

describe('ModernButton.vue', () => {
  describe('Renderização básica', () => {
    it('deve renderizar o botão corretamente', () => {
      const wrapper = mount(ModernButton, {
        props: { text: 'Clique aqui' }
      });
      
      expect(wrapper.exists()).toBe(true);
      expect(wrapper.text()).toContain('Clique aqui');
    });

    it('deve renderizar com slot padrão', () => {
      const wrapper = mount(ModernButton, {
        slots: {
          default: 'Texto do Slot'
        }
      });
      
      expect(wrapper.text()).toContain('Texto do Slot');
    });

    it('deve usar tag button por padrão', () => {
      const wrapper = mount(ModernButton);
      expect(wrapper.element.tagName.toLowerCase()).toBe('button');
    });

    it('deve usar tag customizada quando especificada', () => {
      const wrapper = mount(ModernButton, {
        props: { tag: 'a' }
      });
      expect(wrapper.element.tagName.toLowerCase()).toBe('a');
    });
  });

  describe('Variantes', () => {
    const variants = ['primary', 'secondary', 'outline', 'ghost', 'gradient', 'danger'];

    variants.forEach(variant => {
      it(`deve aplicar classe da variante ${variant}`, () => {
        const wrapper = mount(ModernButton, {
          props: { variant }
        });
        
        expect(wrapper.classes()).toContain(variant);
      });
    });

    it('deve usar primary como variante padrão', () => {
      const wrapper = mount(ModernButton);
      expect(wrapper.classes()).toContain('primary');
    });
  });

  describe('Tamanhos', () => {
    const sizes = ['small', 'medium', 'large'];

    sizes.forEach(size => {
      it(`deve aplicar classe do tamanho ${size}`, () => {
        const wrapper = mount(ModernButton, {
          props: { size }
        });
        
        expect(wrapper.classes()).toContain(size);
      });
    });

    it('deve usar medium como tamanho padrão', () => {
      const wrapper = mount(ModernButton);
      expect(wrapper.classes()).toContain('medium');
    });
  });

  describe('Estado Loading', () => {
    it('deve mostrar spinner quando loading=true', () => {
      const wrapper = mount(ModernButton, {
        props: { loading: true }
      });
      
      expect(wrapper.find('.button-spinner').exists()).toBe(true);
      expect(wrapper.classes()).toContain('is-loading');
    });

    it('deve desabilitar o botão quando loading=true', () => {
      const wrapper = mount(ModernButton, {
        props: { loading: true }
      });
      
      expect(wrapper.attributes('disabled')).toBeDefined();
    });

    it('não deve emitir click quando loading=true', async () => {
      const wrapper = mount(ModernButton, {
        props: { loading: true }
      });
      
      await wrapper.trigger('click');
      expect(wrapper.emitted('click')).toBeFalsy();
    });
  });

  describe('Estado Disabled', () => {
    it('deve aplicar classe is-disabled quando disabled=true', () => {
      const wrapper = mount(ModernButton, {
        props: { disabled: true }
      });
      
      expect(wrapper.classes()).toContain('is-disabled');
    });

    it('deve ter atributo disabled quando disabled=true', () => {
      const wrapper = mount(ModernButton, {
        props: { disabled: true }
      });
      
      expect(wrapper.attributes('disabled')).toBeDefined();
    });

    it('não deve emitir click quando disabled=true', async () => {
      const wrapper = mount(ModernButton, {
        props: { disabled: true }
      });
      
      await wrapper.trigger('click');
      expect(wrapper.emitted('click')).toBeFalsy();
    });
  });

  describe('Ícones', () => {
    it('deve renderizar ícone à esquerda', () => {
      const wrapper = mount(ModernButton, {
        props: { iconLeft: '🚀' }
      });
      
      const iconLeft = wrapper.find('.icon-left');
      expect(iconLeft.exists()).toBe(true);
      expect(iconLeft.text()).toContain('🚀');
    });

    it('deve renderizar ícone à direita', () => {
      const wrapper = mount(ModernButton, {
        props: { iconRight: '→' }
      });
      
      const iconRight = wrapper.find('.icon-right');
      expect(iconRight.exists()).toBe(true);
      expect(iconRight.text()).toContain('→');
    });

    it('não deve mostrar ícones quando loading=true', () => {
      const wrapper = mount(ModernButton, {
        props: { 
          iconLeft: '🚀',
          iconRight: '→',
          loading: true
        }
      });
      
      expect(wrapper.find('.icon-left').exists()).toBe(false);
      expect(wrapper.find('.icon-right').exists()).toBe(false);
    });
  });

  describe('Eventos', () => {
    it('deve emitir evento click quando clicado', async () => {
      const wrapper = mount(ModernButton);
      
      await wrapper.trigger('click');
      
      expect(wrapper.emitted('click')).toBeTruthy();
      expect(wrapper.emitted('click')).toHaveLength(1);
    });

    it('deve passar o evento original no click', async () => {
      const wrapper = mount(ModernButton);
      
      await wrapper.trigger('click');
      
      const clickEvent = wrapper.emitted('click')[0][0];
      expect(clickEvent).toBeInstanceOf(MouseEvent);
    });
  });

  describe('Props type', () => {
    it('deve ter type="button" por padrão', () => {
      const wrapper = mount(ModernButton);
      expect(wrapper.attributes('type')).toBe('button');
    });

    it('deve aceitar type customizado', () => {
      const wrapper = mount(ModernButton, {
        props: { type: 'submit' }
      });
      expect(wrapper.attributes('type')).toBe('submit');
    });
  });
});
