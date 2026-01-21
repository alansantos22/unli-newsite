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
      
      this.serverAvailable = false;
      return false;
    } catch (error) {
      console.warn('🟡 API não disponível (modo desenvolvimento):', error.message);
      this.serverAvailable = false;
      return false;
    }
  }

  calculateLocal(selection, config) {
    const { product, pages, content } = selection;
    const { products, page_addons, content_addons, pricing_rules } = config;

    let subtotal = products[product]?.base_price || 0;

    Object.entries(pages || {}).forEach(([key, qty]) => {
      if (qty > 0 && page_addons[key]) {
        subtotal += page_addons[key].price * qty;
      }
    });

    (content || []).forEach(key => {
      if (content_addons[key]) {
        subtotal += content_addons[key].price;
      }
    });

    const cashDiscount = pricing_rules.cash_discount_percent / 100;
    const installmentMarkup = pricing_rules.installments_12_markup_percent / 100;
    const installments = pricing_rules.installments;

    const avista = Math.round(subtotal * (1 - cashDiscount) * 100) / 100;
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
