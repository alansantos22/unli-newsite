/**
 * PRICING SERVICE
 * 
 * Dual-layer pricing:
 * 1. Calcula localmente (feedback visual imediato)
 * 2. Valida no servidor quando disponível (segurança)
 * 
 * DEBUG MODE:
 * - Quando store.state.ConfigModule.debug = true, força modo local
 * - Útil para desenvolvimento sem rodar PHP
 */

import store from '@/core/store/store';

const API_BASE_URL = '/api';

class PricingService {
  constructor() {
    this.serverAvailable = null;
    this.config = null;
  }
  
  isDebugMode() {
    return store.state.ConfigModule?.debug === true;
  }

  async checkServerAvailability() {
    if (this.isDebugMode()) {
      console.info('🔧 DEBUG MODE: API desabilitada (forçando modo local)');
      this.serverAvailable = false;
      return false;
    }
    
    try {
      const response = await fetch(`${API_BASE_URL}/config.php`, {
        method: 'GET',
        headers: { 'Accept': 'application/json' }
      });
      
      if (response.ok) {
        const data = await response.json();
        this.serverAvailable = data.ok === true;
        if (this.serverAvailable && data.products) {
          this.config = data;
        }
        return this.serverAvailable;
      }
      
      // Se não é 200 OK, é erro do servidor (403, 404, 500, etc)
      console.error(`🔴 ERRO NA API: HTTP ${response.status} - ${response.statusText}`);
      console.error('🔴 Verifique .htaccess e permissões de arquivos');
      this.serverAvailable = false;
      return false;
    } catch (error) {
      // Erros de rede ou parsing JSON
      if (error.message.includes('JSON') || error.message.includes('<!doctype')) {
        console.error('🔴 API retornou HTML em vez de JSON - Verifique configuração do servidor');
      } else {
        console.warn('🟡 API não disponível (modo desenvolvimento):', error.message);
      }
      this.serverAvailable = false;
      return false;
    }
  }

  calculateLocal(selection, config) {
    const { product, pages, content, custom_pages, video_basic_quantity, video_pro_quantity } = selection;
    const { products, page_addons, content_addons, custom_pages: customPagesConfig, pricing_rules } = config;

    let subtotal = products[product]?.base_price || 0;

    // Páginas pré-definidas (aceitar tanto array quanto objeto)
    if (Array.isArray(pages)) {
      // Se for array, converter para objeto
      pages.forEach(pageKey => {
        if (page_addons[pageKey]) {
          subtotal += page_addons[pageKey].price;
        }
      });
    } else {
      // Se for objeto, usar as quantidades
      Object.entries(pages || {}).forEach(([key, qty]) => {
        if (qty > 0 && page_addons[key]) {
          subtotal += page_addons[key].price * qty;
        }
      });
    }

    // Content addons (sem vídeos, pois eles têm quantidade)
    (content || []).forEach(key => {
      if (content_addons[key]) {
        // Se for vídeo, usar price_per_unit * quantidade
        if (key === 'video_basic') {
          const qty = video_basic_quantity || 0;
          subtotal += (content_addons[key].price_per_unit || 0) * qty;
        } else if (key === 'video_pro') {
          const qty = video_pro_quantity || 0;
          subtotal += (content_addons[key].price_per_unit || 0) * qty;
        } else {
          // Outros addons usam price fixo
          subtotal += content_addons[key].price || 0;
        }
      }
    });

    // Páginas customizadas
    if (custom_pages && custom_pages.length > 0 && customPagesConfig) {
      custom_pages.forEach(page => {
        let pageTotal = customPagesConfig.base_price || 0;
        
        if (page.resources) {
          Object.entries(page.resources).forEach(([resource, enabled]) => {
            if (enabled && customPagesConfig.resources[resource]) {
              pageTotal += customPagesConfig.resources[resource].price || 0;
            }
          });
        }
        
        subtotal += pageTotal;
      });
    }

    // Cálculos finais
    // IMPORTANTE: Lógica simples de preços
    // - À VISTA: subtotal (preço normal)
    // - PARCELADO (12x): subtotal + 15% (taxa do gateway de pagamento)
    const installmentMarkup = pricing_rules.installments_12_markup_percent / 100;
    const installments = pricing_rules.installments;

    const avista = Math.round(subtotal * 100) / 100;
    const parcelado_total = Math.round(subtotal * (1 + installmentMarkup) * 100) / 100;
    const parcela = Math.round((parcelado_total / installments) * 100) / 100;

    return {
      subtotal: Math.round(subtotal * 100) / 100,
      avista,
      parcelado_total,
      parcela_12: parcela,
      installments,
      source: 'local'
    };
  }

  async validateOnServer(selection) {
    if (this.isDebugMode()) {
      return null;
    }
    
    if (this.serverAvailable === false) {
      return null;
    }

    try {
      const response = await fetch(`${API_BASE_URL}/price.php`, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(selection)
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const data = await response.json();
      
      if (data.ok) {
        this.serverAvailable = true;
        return {
          normalized: data.normalized,
          pricing: {
            ...data.pricing,
            source: 'server'
          }
        };
      }

      return null;
    } catch (error) {
      console.warn('🟡 Validação server-side falhou:', error.message);
      this.serverAvailable = false;
      return null;
    }
  }

  async calculatePrice(selection, config) {
    const localPricing = this.calculateLocal(selection, config);
    const serverResult = await this.validateOnServer(selection);

    if (serverResult) {
      const { normalized, pricing } = serverResult;
      
      const diff = Math.abs(pricing.subtotal - localPricing.subtotal);
      if (diff > 0.01) {
        console.warn('⚠️ DISCREPÂNCIA: Local vs Server', {
          local: localPricing.subtotal,
          server: pricing.subtotal,
          diff
        });
      }

      return {
        pricing,
        normalized,
        serverValidated: true
      };
    } else {
      console.info('ℹ️ Usando cálculo local (servidor offline)');
      return {
        pricing: localPricing,
        normalized: selection,
        serverValidated: false
      };
    }
  }

  async createOrder(orderData) {
    if (this.isDebugMode()) {
      console.info('🔧 DEBUG MODE: Pedido simulado (API desabilitada)');
      return {
        ok: false,
        error: 'Debug mode ativo',
        offline: true,
        debugMode: true,
        mockData: {
          order_id: 'DEBUG-' + Date.now(),
          status: 'pending_api',
          message: 'DEBUG: Pedido simulado localmente'
        }
      };
    }
    
    if (this.serverAvailable === false) {
      console.warn('🟡 Servidor offline - pedido não será enviado');
      return {
        ok: false,
        error: 'API offline',
        offline: true,
        mockData: {
          order_id: 'MOCK-' + Date.now(),
          status: 'pending_api',
          message: 'Pedido salvo localmente (API offline)'
        }
      };
    }

    try {
      const response = await fetch(`${API_BASE_URL}/order_create.php`, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(orderData)
      });

      const data = await response.json();
      
      if (data.ok) {
        this.serverAvailable = true;
        return data;
      } else {
        return {
          ok: false,
          error: data.error || 'Erro desconhecido',
          data
        };
      }
    } catch (error) {
      console.error('🔴 Erro ao criar pedido:', error);
      this.serverAvailable = false;
      return {
        ok: false,
        error: error.message,
        offline: true
      };
    }
  }

  async createPayment(orderId) {
    if (this.isDebugMode() || this.serverAvailable === false) {
      return {
        ok: false,
        error: this.isDebugMode() ? 'Debug mode ativo' : 'API offline',
        offline: true,
        debugMode: this.isDebugMode()
      };
    }

    try {
      const response = await fetch(`${API_BASE_URL}/payment_create.php`, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ order_id: orderId })
      });

      const data = await response.json();
      return data;
    } catch (error) {
      console.error('🔴 Erro ao criar pagamento:', error);
      this.serverAvailable = false;
      return {
        ok: false,
        error: error.message,
        offline: true
      };
    }
  }

  async submitCustomRequest(customData) {
    if (this.isDebugMode()) {
      console.info('🔧 DEBUG MODE: Orçamento simulado');
      return {
        ok: false,
        offline: true,
        debugMode: true,
        message: 'DEBUG: API offline. Por favor, entre em contato via WhatsApp.'
      };
    }
    
    if (this.serverAvailable === false) {
      console.warn('🟡 API offline - orçamento não será enviado');
      return {
        ok: false,
        offline: true,
        message: 'API offline. Por favor, entre em contato via WhatsApp.'
      };
    }

    console.info('📧 Orçamento personalizado:', customData);
    
    return {
      ok: true,
      message: 'Orçamento recebido! Entraremos em contato em breve.',
      mock: true
    };
  }
}

export default new PricingService();
