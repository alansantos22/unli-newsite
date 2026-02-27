/**
 * Testes Unitários - PricingService
 * 
 * Testa o serviço de cálculo de preços com dual-layer
 * (cálculo local + validação servidor)
 */

// Mock do store antes de importar o serviço
jest.mock('@/core/store/store', () => ({
  state: {
    ConfigModule: {
      debug: false
    }
  }
}));

import PricingService from '@/core/services/PricingService.js';

describe('PricingService', () => {
  // Configuração de teste padrão
  const mockConfig = {
    products: {
      site_express: { base_price: 820 },
      site_pro: { base_price: 1400 },
      site_premium: { base_price: 2200 }
    },
    page_addons: {
      about: { price: 99 },
      contact: { price: 99 },
      services: { price: 199 },
      portfolio: { price: 249 }
    },
    content_addons: {
      seo_optimization: { price: 299 },
      video_basic: { price_per_unit: 199 },
      video_pro: { price_per_unit: 399 },
      copy_ai: { price: 249 }
    },
    custom_pages: {
      base_price: 350,
      resources: {
        contact_form: { price: 99 },
        gallery: { price: 149 },
        calculator: { price: 299 }
      }
    },
    pricing_rules: {
      installments: 12,
      installments_12_markup_percent: 15
    }
  };

  beforeEach(() => {
    // Reset do serviço
    PricingService.serverAvailable = null;
    PricingService.config = null;
    
    // Reset fetch mock
    global.fetch = jest.fn();
  });

  describe('calculateLocal', () => {
    it('deve calcular preço base do produto', () => {
      const selection = {
        product: 'site_express',
        pages: [],
        content: []
      };

      const result = PricingService.calculateLocal(selection, mockConfig);

      expect(result.subtotal).toBe(820);
      expect(result.source).toBe('local');
    });

    it('deve adicionar páginas pré-definidas (array)', () => {
      const selection = {
        product: 'site_express',
        pages: ['about', 'contact'],
        content: []
      };

      const result = PricingService.calculateLocal(selection, mockConfig);

      // 820 + 99 + 99 = 1018
      expect(result.subtotal).toBe(1018);
    });

    it('deve adicionar páginas pré-definidas (objeto com quantidades)', () => {
      const selection = {
        product: 'site_express',
        pages: { about: 1, services: 2 },
        content: []
      };

      const result = PricingService.calculateLocal(selection, mockConfig);

      // 820 + 99 + (199 * 2) = 1317
      expect(result.subtotal).toBe(1317);
    });

    it('deve adicionar content addons', () => {
      const selection = {
        product: 'site_pro',
        pages: [],
        content: ['seo_optimization', 'copy_ai']
      };

      const result = PricingService.calculateLocal(selection, mockConfig);

      // 1400 + 299 + 249 = 1948
      expect(result.subtotal).toBe(1948);
    });

    it('deve calcular vídeos por quantidade', () => {
      const selection = {
        product: 'site_express',
        pages: [],
        content: ['video_basic', 'video_pro'],
        video_basic_quantity: 2,
        video_pro_quantity: 1
      };

      const result = PricingService.calculateLocal(selection, mockConfig);

      // 820 + (199 * 2) + (399 * 1) = 1617
      expect(result.subtotal).toBe(1617);
    });

    it('deve calcular páginas customizadas', () => {
      const selection = {
        product: 'site_express',
        pages: [],
        content: [],
        custom_pages: [
          { resources: { contact_form: true, gallery: true } },
          { resources: { calculator: true } }
        ]
      };

      const result = PricingService.calculateLocal(selection, mockConfig);

      // 820 + (350 + 99 + 149) + (350 + 299) = 2067
      expect(result.subtotal).toBe(2067);
    });

    it('deve calcular parcelas com markup de 15%', () => {
      const selection = {
        product: 'site_express',
        pages: [],
        content: []
      };

      const result = PricingService.calculateLocal(selection, mockConfig);

      // 820 * 1.15 = 943
      expect(result.parcelado_total).toBe(943);
      // 943 / 12 = 78.58
      expect(result.parcela_12).toBeCloseTo(78.58, 1);
    });

    it('deve retornar preço à vista igual ao subtotal', () => {
      const selection = {
        product: 'site_premium',
        pages: [],
        content: []
      };

      const result = PricingService.calculateLocal(selection, mockConfig);

      expect(result.avista).toBe(result.subtotal);
      expect(result.avista).toBe(2200);
    });

    it('deve arredondar valores corretamente', () => {
      const selection = {
        product: 'site_express',
        pages: ['about'],
        content: []
      };

      const result = PricingService.calculateLocal(selection, mockConfig);

      // Verificar que não há decimais excessivas
      expect(Number.isInteger(result.subtotal * 100)).toBe(true);
      expect(Number.isInteger(result.parcela_12 * 100)).toBe(true);
    });
  });

  describe('isDebugMode', () => {
    it('deve retornar false quando debug não está ativo', () => {
      require('@/core/store/store').state.ConfigModule.debug = false;
      
      expect(PricingService.isDebugMode()).toBe(false);
    });

    it('deve retornar true quando debug está ativo', () => {
      require('@/core/store/store').state.ConfigModule.debug = true;
      
      expect(PricingService.isDebugMode()).toBe(true);
      
      // Resetar
      require('@/core/store/store').state.ConfigModule.debug = false;
    });
  });

  describe('checkServerAvailability', () => {
    it('deve retornar false em modo debug', async () => {
      require('@/core/store/store').state.ConfigModule.debug = true;
      
      const result = await PricingService.checkServerAvailability();
      
      expect(result).toBe(false);
      expect(PricingService.serverAvailable).toBe(false);
      
      // Resetar
      require('@/core/store/store').state.ConfigModule.debug = false;
    });

    it('deve retornar true quando API responde ok', async () => {
      global.fetch.mockResolvedValueOnce({
        ok: true,
        json: async () => ({ ok: true, products: mockConfig.products })
      });

      const result = await PricingService.checkServerAvailability();

      expect(result).toBe(true);
      expect(PricingService.serverAvailable).toBe(true);
    });

    it('deve retornar false quando API falha', async () => {
      global.fetch.mockRejectedValueOnce(new Error('Network error'));

      const result = await PricingService.checkServerAvailability();

      expect(result).toBe(false);
      expect(PricingService.serverAvailable).toBe(false);
    });

    it('deve retornar false quando HTTP status não é 200', async () => {
      global.fetch.mockResolvedValueOnce({
        ok: false,
        status: 500,
        statusText: 'Internal Server Error'
      });

      const result = await PricingService.checkServerAvailability();

      expect(result).toBe(false);
    });
  });

  describe('validateOnServer', () => {
    it('deve retornar null em modo debug', async () => {
      require('@/core/store/store').state.ConfigModule.debug = true;
      
      const result = await PricingService.validateOnServer({});
      
      expect(result).toBeNull();
      
      // Resetar
      require('@/core/store/store').state.ConfigModule.debug = false;
    });

    it('deve retornar null quando servidor está offline', async () => {
      PricingService.serverAvailable = false;

      const result = await PricingService.validateOnServer({});

      expect(result).toBeNull();
    });

    it('deve retornar dados quando servidor valida com sucesso', async () => {
      PricingService.serverAvailable = true;
      
      global.fetch.mockResolvedValueOnce({
        ok: true,
        json: async () => ({
          ok: true,
          normalized: { product: 'site_express' },
          pricing: { subtotal: 820, avista: 820 }
        })
      });

      const result = await PricingService.validateOnServer({ product: 'site_express' });

      expect(result).not.toBeNull();
      expect(result.pricing.source).toBe('server');
      expect(result.normalized.product).toBe('site_express');
    });
  });

  describe('createOrder', () => {
    it('deve retornar mockData em modo debug', async () => {
      require('@/core/store/store').state.ConfigModule.debug = true;
      
      const result = await PricingService.createOrder({ test: true });
      
      expect(result.ok).toBe(false);
      expect(result.debugMode).toBe(true);
      expect(result.mockData.order_id).toContain('DEBUG-');
      
      // Resetar
      require('@/core/store/store').state.ConfigModule.debug = false;
    });

    it('deve retornar offline quando servidor está indisponível', async () => {
      PricingService.serverAvailable = false;

      const result = await PricingService.createOrder({ test: true });

      expect(result.ok).toBe(false);
      expect(result.offline).toBe(true);
      expect(result.mockData.order_id).toContain('MOCK-');
    });

    it('deve enviar pedido quando servidor está disponível', async () => {
      PricingService.serverAvailable = true;
      
      global.fetch.mockResolvedValueOnce({
        ok: true,
        json: async () => ({
          ok: true,
          order_id: 'ORD-123',
          status: 'created'
        })
      });

      const result = await PricingService.createOrder({ product: 'site_express' });

      expect(result.ok).toBe(true);
      expect(result.order_id).toBe('ORD-123');
      expect(global.fetch).toHaveBeenCalledWith(
        expect.stringContaining('order_create.php'),
        expect.objectContaining({
          method: 'POST',
          body: expect.any(String)
        })
      );
    });
  });

  describe('submitCustomRequest', () => {
    it('deve indicar offline em modo debug', async () => {
      require('@/core/store/store').state.ConfigModule.debug = true;
      
      const result = await PricingService.submitCustomRequest({ message: 'teste' });
      
      expect(result.ok).toBe(false);
      expect(result.debugMode).toBe(true);
      
      // Resetar
      require('@/core/store/store').state.ConfigModule.debug = false;
    });

    it('deve retornar sucesso mock quando servidor disponível', async () => {
      PricingService.serverAvailable = true;

      const result = await PricingService.submitCustomRequest({ 
        name: 'Teste',
        email: 'teste@email.com' 
      });

      expect(result.ok).toBe(true);
      expect(result.mock).toBe(true);
    });
  });
});
