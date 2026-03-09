<template>
  <div class="quick-checkout">
    <!-- Fundo -->
    <div class="checkout-background">
      <div class="gradient-orb orb-1"></div>
      <div class="gradient-orb orb-2"></div>
    </div>

    <!-- Header -->
    <header class="checkout-header">
      <button class="btn-back" @click="$emit('go-back')">
        <i class="fas fa-arrow-left"></i>
        <span>Voltar</span>
      </button>
      <div class="header-title">
        <h1>Finalizar Pedido</h1>
        <p>Checkout rápido e seguro</p>
      </div>
      <div class="header-trust">
        <i class="fas fa-shield-alt"></i>
        <span>100% Seguro</span>
      </div>
    </header>

    <!-- Main Content -->
    <main class="checkout-main">
      <div class="checkout-container">
        <!-- Coluna Esquerda: Formulário -->
        <div class="checkout-form-section">
          <form @submit.prevent="submitCheckout" class="checkout-form">
            <!-- Dados do Cliente -->
            <div class="form-section">
              <h3 class="section-title">
                <i class="fas fa-user"></i>
                Seus Dados
              </h3>
              
              <div class="form-group">
                <label>Nome Completo *</label>
                <input 
                  type="text" 
                  v-model="formData.name"
                  placeholder="Ex: João Silva"
                  required
                  :class="{ 'error': errors.name }"
                >
                <span v-if="errors.name" class="error-message">{{ errors.name }}</span>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>E-mail *</label>
                  <input 
                    type="email" 
                    v-model="formData.email"
                    placeholder="seu@email.com"
                    required
                    :class="{ 'error': errors.email }"
                  >
                  <span v-if="errors.email" class="error-message">{{ errors.email }}</span>
                </div>

                <div class="form-group">
                  <label>WhatsApp *</label>
                  <input 
                    type="tel" 
                    v-model="formData.whatsapp"
                    @input="formatWhatsApp"
                    placeholder="(00) 00000-0000"
                    maxlength="15"
                    required
                    :class="{ 'error': errors.whatsapp }"
                  >
                  <span v-if="errors.whatsapp" class="error-message">{{ errors.whatsapp }}</span>
                </div>
              </div>
            </div>

            <!-- Forma de Pagamento -->
            <div class="form-section">
              <h3 class="section-title">
                <i class="fas fa-credit-card"></i>
                Forma de Pagamento
              </h3>

              <div class="payment-options">
                <label 
                  class="payment-option"
                  :class="{ 'selected': paymentMethod === 'cash' }"
                >
                  <input 
                    type="radio" 
                    v-model="paymentMethod" 
                    value="cash"
                  >
                  <div class="option-content">
                    <div class="option-icon">
                      <i class="fas fa-bolt"></i>
                    </div>
                    <div class="option-info">
                      <span class="option-title">PIX à Vista</span>
                      <span class="option-price">{{ formatCurrency(pricing.cashPrice) }}</span>
                      <span class="option-badge">Sem acréscimos</span>
                    </div>
                  </div>
                </label>

                <label 
                  class="payment-option"
                  :class="{ 'selected': paymentMethod === 'installment' }"
                >
                  <input 
                    type="radio" 
                    v-model="paymentMethod" 
                    value="installment"
                  >
                  <div class="option-content">
                    <div class="option-icon">
                      <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="option-info">
                      <span class="option-title">12x no Cartão</span>
                      <span class="option-price">12x {{ formatCurrency(pricing.installmentPrice) }}</span>
                      <span class="option-total">Total: {{ formatCurrency(pricing.installmentTotal) }}</span>
                    </div>
                  </div>
                </label>
              </div>
            </div>

            <!-- Atendimento com Especialista -->
            <div class="form-section">
              <h3 class="section-title">
                <i class="fas fa-user-tie"></i>
                Atendimento Pós-Compra
              </h3>
              <div class="specialist-toggle-card" :class="{ 'specialist-active': localSpecialist }">
                <label class="specialist-toggle-label">
                  <div class="specialist-info">
                    <div class="specialist-header">
                      <span class="specialist-title">Quero ser atendido por um especialista</span>
                    <span class="specialist-badge">+R$ {{ pricing.specialistMonthlyBadge }}/mês</span>
                    </div>
                    <p class="specialist-desc">
                      Nosso time entra em contato pelo WhatsApp para coletar as informações do seu site com você, ao invés de preencher um formulário sozinho.
                    </p>
                  </div>
                  <div class="toggle-switch">
                    <input type="checkbox" id="specialist-toggle-qc" v-model="localSpecialist" />
                    <span class="toggle-slider"></span>
                  </div>
                </label>
              </div>
            </div>

            <!-- Botão de Comprar -->
            <button 
              type="submit" 
              class="btn-checkout"
              :disabled="isSubmitting"
            >
              <template v-if="isSubmitting">
                <i class="fas fa-spinner fa-spin"></i>
                Processando...
              </template>
              <template v-else-if="isManualMode">
                <i class="fab fa-whatsapp"></i>
                Falar com Especialista
              </template>
              <template v-else>
                <i class="fas fa-lock"></i>
                Pagar {{ paymentMethod === 'cash' ? formatCurrency(pricing.cashPrice) : formatCurrency(pricing.installmentTotal) }}
              </template>
            </button>

            <p class="checkout-note">
              <i class="fas fa-info-circle"></i>
              <template v-if="isManualMode">
                Você será redirecionado para o WhatsApp para finalizar com nosso especialista.
              </template>
              <template v-else>
                Você será redirecionado para o gateway de pagamento seguro para finalizar.
              </template>
            </p>
          </form>
        </div>

        <!-- Coluna Direita: Resumo -->
        <div class="checkout-summary-section">
          <div class="summary-card">
            <div class="summary-header">
              <span class="package-icon">{{ packageInfo.icon }}</span>
              <div class="package-info">
                <h3>{{ packageInfo.name }}</h3>
                <p>{{ packageInfo.tagline }}</p>
              </div>
            </div>

            <div class="summary-items">
              <div class="summary-item main-product">
                <span class="item-name">
                  <i class="fas fa-globe"></i>
                  Site Completo PRO
                </span>
                <span class="item-price">{{ formatCurrency(pricing.basePrice) }}</span>
              </div>

              <div class="pages-list">
                <span class="pages-label">Páginas incluídas:</span>
                <div class="pages-tags">
                  <span 
                    v-for="page in packageInfo.pages" 
                    :key="page"
                    class="page-tag"
                  >
                    {{ getPageName(page) }}
                  </span>
                </div>
              </div>

              <div class="summary-item pages-total">
                <span class="item-name">
                  <i class="fas fa-file-alt"></i>
                  {{ packageInfo.pages.length }} Páginas Adicionais
                </span>
                <span class="item-price">{{ formatCurrency(pricing.pagesTotal) }}</span>
              </div>

              <div v-if="localSpecialist" class="summary-item specialist-item">
                <span class="item-name">
                  <i class="fas fa-user-tie"></i>
                  Atendimento com Especialista
                </span>
                <span class="item-price">+R$ {{ pricing.specialistMonthlyBadge }}/mês</span>
              </div>
            </div>

            <div class="summary-divider"></div>

            <div class="summary-totals">
              <div class="total-row final">
                <span>Total</span>
                <span class="final-price">
                  {{ paymentMethod === 'cash' 
                    ? formatCurrency(pricing.cashPrice) 
                    : formatCurrency(pricing.installmentTotal) 
                  }}
                </span>
              </div>
            </div>

            <!-- Benefícios -->
            <div class="summary-benefits">
              <div class="benefit">
                <i class="fas fa-check-circle"></i>
                <span>Hospedagem inclusa por 1 ano</span>
              </div>
              <div class="benefit">
                <i class="fas fa-check-circle"></i>
                <span>Domínio .com.br grátis</span>
              </div>
              <div class="benefit">
                <i class="fas fa-check-circle"></i>
                <span>Certificado SSL (HTTPS)</span>
              </div>
              <div class="benefit">
                <i class="fas fa-check-circle"></i>
                <span>Suporte dedicado</span>
              </div>
            </div>
          </div>

          <!-- Promo Banner -->
          <div class="promo-mini-banner">
            <span class="promo-icon">🎉</span>
            <span class="promo-text">Promoção "Iniciando 2026 Online" ativa!</span>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
export default {
  name: 'QuickCheckout',
  
  props: {
    // Produto base (landing ou site_complete)
    product: {
      type: String,
      default: 'site_complete'
    },
    // Pacote selecionado (essential, authority, enterprise)
    packageKey: {
      type: String,
      default: 'essential'
    },
    // Configuração de preços
    pricingConfig: {
      type: Object,
      default: () => ({})
    },
    // Opção de atendimento com especialista (pré-selecionada pelo usuário)
    specialistOnboarding: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      formData: {
        name: '',
        email: '',
        whatsapp: ''
      },
      errors: {},
      paymentMethod: 'cash', // 'cash' ou 'installment'
      isSubmitting: false,
      localSpecialist: this.specialistOnboarding, // cópia editável localmente
      
      // Configuração local de preços (fallback)
      localPricing: {
        basePrice: 619,
        cashDiscountPercent: 15,
        installmentMarkupPercent: 15,
        promoDiscountPercent: 30
      },
      
      // Pacotes pré-definidos (fallback)
      packages: {
        essential: {
          name: 'Essencial',
          tagline: 'Para quem presta serviços e precisa ser encontrado',
          icon: '🏢',
          pages: ['about', 'services', 'contact']
        },
        authority: {
          name: 'Autoridade',
          tagline: 'Mostre seu trabalho e tire dúvidas para fechar contratos',
          icon: '🚀',
          pages: ['about', 'services', 'contact', 'portfolio', 'faq']
        },
        enterprise: {
          name: 'Ecossistema Digital',
          tagline: 'Atraia tráfego com Blog e exiba seus produtos online',
          icon: '💎',
          pages: ['about', 'services', 'contact', 'portfolio', 'faq', 'blog', 'showcase']
        }
      },
      
      // Preços das páginas (fallback)
      pagesPricing: {
        about: 59,
        services: 119,
        portfolio: 159,
        faq: 89,
        contact: 129,
        blog: 349,
        showcase: 449
      }
    };
  },

  computed: {
    isManualMode() {
      return process.env.VUE_APP_MANUAL_MODE === 'true';
    },
    packageInfo() {
      // Usar config se disponível, senão fallback
      if (this.pricingConfig?.predefined_packages?.[this.packageKey]) {
        return this.pricingConfig.predefined_packages[this.packageKey];
      }
      return this.packages[this.packageKey] || this.packages.essential;
    },
    
    pricing() {
      // Calcular preços
      const config = this.pricingConfig;
      
      // Base price do produto
      const basePrice = config?.products?.[this.product]?.base_price || this.localPricing.basePrice;
      
      // Preço das páginas
      let pagesTotal = 0;
      const pages = this.packageInfo.pages || [];
      pages.forEach(page => {
        const pagePrice = config?.page_addons?.[page]?.price || this.pagesPricing[page] || 0;
        pagesTotal += pagePrice;
      });
      
      // Subtotal (preços do pricing.json JÁ TEM a promoção aplicada)
      const subtotal = basePrice + pagesTotal;
      
      // Atendimento com especialista
      // R$169 JÁ É o preço parcelado — NÃO aplica 15% a mais
      const specialistBasePrice = this.pricingConfig?.service_addons?.specialist_onboarding?.price || 169;
      const specialistMonthlyBadge = Math.ceil(specialistBasePrice / 12); // exibição no badge
      const specialistParcelado = this.localSpecialist ? specialistBasePrice : 0;
      const specialistAvista = this.localSpecialist ? Math.round((specialistBasePrice / 1.15) * 100) / 100 : 0;
      
      // NOTA: NÃO aplicar 30% de desconto - os preços já são promocionais!
      // À vista (PIX) = subtotal base + specialist revertido (sem markup)
      // Parcelado (12x) = subtotal base × 1.15 + specialist (já parcelado)
      
      // Parcelado: acréscimo de 15% para cobrir taxa do gateway (SÓ no base)
      const installmentPercent = config?.pricing_rules?.installments_12_markup_percent || this.localPricing.installmentMarkupPercent;
      const installmentTotal = subtotal * (1 + installmentPercent / 100) + specialistParcelado;
      const installmentPrice = installmentTotal / 12;
      
      const cashPrice = subtotal + specialistAvista; // PIX = base + specialist sem markup
      
      return {
        basePrice,
        pagesTotal,
        subtotal,
        specialistPrice: specialistParcelado,
        specialistMonthlyBadge,
        cashPrice,
        installmentTotal,
        installmentPrice
      };
    }
  },

  methods: {
    formatCurrency(value) {
      return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
      }).format(value);
    },
    
    getPageName(pageKey) {
      const names = {
        about: 'Sobre Nós',
        services: 'Serviços',
        portfolio: 'Portfólio',
        faq: 'FAQ',
        contact: 'Contato',
        blog: 'Blog',
        showcase: 'Vitrine'
      };
      return names[pageKey] || pageKey;
    },
    
    formatWhatsApp() {
      let value = this.formData.whatsapp.replace(/\D/g, '');
      if (value.length > 11) value = value.slice(0, 11);
      
      if (value.length > 6) {
        value = `(${value.slice(0, 2)}) ${value.slice(2, 7)}-${value.slice(7)}`;
      } else if (value.length > 2) {
        value = `(${value.slice(0, 2)}) ${value.slice(2)}`;
      } else if (value.length > 0) {
        value = `(${value}`;
      }
      
      this.formData.whatsapp = value;
    },
    
    validateForm() {
      this.errors = {};
      let isValid = true;
      
      if (!this.formData.name || this.formData.name.length < 3) {
        this.errors.name = 'Digite seu nome completo';
        isValid = false;
      }
      
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!this.formData.email || !emailRegex.test(this.formData.email)) {
        this.errors.email = 'Digite um e-mail válido';
        isValid = false;
      }
      
      const phoneDigits = this.formData.whatsapp.replace(/\D/g, '');
      if (phoneDigits.length < 10) {
        this.errors.whatsapp = 'Digite um WhatsApp válido';
        isValid = false;
      }
      
      return isValid;
    },
    
    async submitCheckout() {
      if (!this.validateForm()) return;
      
      // === MODO MANUAL: Redirecionar para WhatsApp ===
      if (process.env.VUE_APP_MANUAL_MODE === 'true') {
        const { redirectToWhatsApp } = require('@/core/composables/useWhatsAppRedirect').useWhatsAppRedirect();
        const pages = this.packageInfo.pages || [];
        const productLabel = this.product === 'landing' ? 'Landing Page' : 'Site Completo';
        redirectToWhatsApp(productLabel, pages);
        return;
      }
      
      this.isSubmitting = true;
      
      try {
        // Montar dados do pedido
        const pagesAsObject = {};
        this.packageInfo.pages.forEach(page => {
          pagesAsObject[page] = 1;
        });
        
        const orderData = {
          product: this.product,
          pages: pagesAsObject,
          content: [],
          custom_pages: [],
          video_basic_quantity: 0,
          video_pro_quantity: 0,
          service_addons: {
            specialist_onboarding: this.localSpecialist
          },
          briefing: {
            customer_name: this.formData.name,
            email: this.formData.email,
            whatsapp: this.formData.whatsapp
          },
          payment_method: this.paymentMethod === 'cash' ? 'avista' : 'prazo'
        };
        
        console.log('📦 [QuickCheckout] Criando pedido:', orderData);
        
        // Criar pedido
        const orderResponse = await fetch('/api/order_create.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(orderData)
        });
        
        const orderResult = await orderResponse.json();
        console.log('📦 [QuickCheckout] Resultado do pedido:', orderResult);
        
        if (!orderResult.ok) {
          throw new Error(orderResult.error || 'Erro ao criar pedido');
        }
        
        // Criar link de pagamento no Pagar.me
        const preferenceData = {
          order_id: orderResult.order_id,
          payer_name: this.formData.name,
          payer_email: this.formData.email,
          payment_type: this.paymentMethod === 'cash' ? 'avista' : 'prazo'
        };
        
        console.log('💳 [QuickCheckout] Criando link Pagar.me:', preferenceData);
        
        const prefResponse = await fetch('/api/create_preference.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(preferenceData)
        });
        
        const prefResult = await prefResponse.json();
        console.log('💳 [QuickCheckout] Resultado Pagar.me:', prefResult);
        
        // payment_url = Pagar.me | init_point = compatibilidade
        const checkoutUrl = prefResult.payment_url || prefResult.init_point;
        
        if (prefResult.success && checkoutUrl) {
          // Redirecionar para checkout do Pagar.me
          console.log('🚀 [QuickCheckout] Redirecionando para:', checkoutUrl);
          window.location.href = checkoutUrl;
        } else {
          throw new Error(prefResult.message || 'Erro ao criar link de pagamento');
        }
        
      } catch (error) {
        console.error('❌ [QuickCheckout] Erro:', error);
        alert(`Erro ao processar pagamento: ${error.message}\n\nPor favor, tente novamente ou entre em contato.`);
        this.isSubmitting = false;
      }
    }
  }
};
</script>

<style lang="scss" scoped>
// Variables
$primary: #8B5CF6;
$primary-light: #A78BFA;
$secondary: #10B981;
$accent: #F59E0B;
$dark: #1A1A2E;
$darker: #0F0F1A;
$white: #FFFFFF;
$gray-light: #E5E7EB;
$gray-medium: #9CA3AF;
$text-primary: #1F2937;
$text-secondary: #6B7280;
$error: #EF4444;

// Base
.quick-checkout {
  min-height: 100vh;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  position: relative;
  overflow: hidden;
}

.checkout-background {
  position: absolute;
  inset: 0;
  pointer-events: none;
  overflow: hidden;
  
  .gradient-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(100px);
    opacity: 0.4;
    
    &.orb-1 {
      width: 500px;
      height: 500px;
      background: radial-gradient(circle, rgba($primary, 0.3), transparent);
      top: -200px;
      right: -100px;
    }
    
    &.orb-2 {
      width: 400px;
      height: 400px;
      background: radial-gradient(circle, rgba($secondary, 0.2), transparent);
      bottom: -100px;
      left: -100px;
    }
  }
}

// Header
.checkout-header {
  position: relative;
  z-index: 10;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 40px;
  background: $white;
  border-bottom: 1px solid $gray-light;
  
  @media (max-width: 768px) {
    padding: 16px 20px;
    flex-wrap: wrap;
    gap: 12px;
  }
  
  .btn-back {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: transparent;
    border: 1px solid $gray-light;
    border-radius: 10px;
    color: $text-secondary;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
    
    &:hover {
      border-color: $primary;
      color: $primary;
    }
  }
  
  .header-title {
    text-align: center;
    
    h1 {
      font-size: 20px;
      font-weight: 700;
      color: $text-primary;
      margin: 0;
    }
    
    p {
      font-size: 13px;
      color: $text-secondary;
      margin: 4px 0 0;
    }
  }
  
  .header-trust {
    display: flex;
    align-items: center;
    gap: 6px;
    color: $secondary;
    font-size: 13px;
    font-weight: 500;
    
    i { font-size: 16px; }
  }
}

// Main
.checkout-main {
  position: relative;
  z-index: 1;
  padding: 40px 20px;
  
  @media (max-width: 768px) {
    padding: 20px 16px;
  }
}

.checkout-container {
  max-width: 1100px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 32px;
  
  @media (max-width: 968px) {
    grid-template-columns: 1fr;
  }
}

// Form Section
.checkout-form-section {
  background: $white;
  border-radius: 20px;
  padding: 32px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  
  @media (max-width: 768px) {
    padding: 24px 20px;
  }
}

.form-section {
  margin-bottom: 32px;
  
  &:last-of-type {
    margin-bottom: 24px;
  }
}

.section-title {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 16px;
  font-weight: 600;
  color: $text-primary;
  margin: 0 0 20px;
  
  i {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba($primary, 0.1), rgba($secondary, 0.1));
    border-radius: 8px;
    color: $primary;
    font-size: 14px;
  }
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  
  @media (max-width: 600px) {
    grid-template-columns: 1fr;
  }
}

.form-group {
  margin-bottom: 16px;
  
  label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: $text-primary;
    margin-bottom: 6px;
  }
  
  input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid $gray-light;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.2s;
    
    &:focus {
      outline: none;
      border-color: $primary;
      box-shadow: 0 0 0 3px rgba($primary, 0.1);
    }
    
    &.error {
      border-color: $error;
    }
    
    &::placeholder {
      color: $gray-medium;
    }
  }
  
  .error-message {
    display: block;
    font-size: 12px;
    color: $error;
    margin-top: 4px;
  }
}

// Payment Options
.payment-options {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.payment-option {
  display: block;
  cursor: pointer;
  
  input {
    display: none;
  }
  
  .option-content {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    background: #f8fafc;
    border: 2px solid transparent;
    border-radius: 12px;
    transition: all 0.2s;
  }
  
  &.selected .option-content {
    background: rgba($primary, 0.05);
    border-color: $primary;
  }
  
  &:hover:not(.selected) .option-content {
    background: #f1f5f9;
  }
  
  .option-icon {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: $white;
    border-radius: 10px;
    
    i {
      font-size: 18px;
      color: $primary;
    }
  }
  
  .option-info {
    flex: 1;
    
    .option-title {
      display: block;
      font-size: 14px;
      font-weight: 600;
      color: $text-primary;
    }
    
    .option-price {
      display: block;
      font-size: 18px;
      font-weight: 700;
      color: $primary;
      margin-top: 2px;
    }
    
    .option-badge {
      display: inline-block;
      padding: 2px 8px;
      background: linear-gradient(135deg, $secondary, darken($secondary, 10%));
      border-radius: 4px;
      font-size: 10px;
      font-weight: 600;
      color: $white;
      margin-top: 4px;
    }
    
    .option-total {
      display: block;
      font-size: 12px;
      color: $text-secondary;
      margin-top: 2px;
    }
  }
}

// Checkout Button
.btn-checkout {
  width: 100%;
  padding: 16px 24px;
  background: linear-gradient(135deg, $primary, darken($primary, 10%));
  border: none;
  border-radius: 12px;
  color: $white;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  
  &:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba($primary, 0.3);
  }
  
  &:disabled {
    opacity: 0.7;
    cursor: not-allowed;
  }
}

.checkout-note {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  font-size: 12px;
  color: $text-secondary;
  margin-top: 16px;
  
  i { color: $gray-medium; }
}

// Summary Section
.checkout-summary-section {
  @media (max-width: 968px) {
    order: -1;
  }
}

.summary-card {
  background: $white;
  border-radius: 20px;
  padding: 24px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  position: sticky;
  top: 20px;
}

.summary-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 1px solid $gray-light;
  
  .package-icon {
    font-size: 40px;
  }
  
  .package-info {
    h3 {
      font-size: 18px;
      font-weight: 700;
      color: $text-primary;
      margin: 0;
    }
    
    p {
      font-size: 13px;
      color: $text-secondary;
      margin: 4px 0 0;
    }
  }
}

.summary-items {
  margin-bottom: 20px;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  
  .item-name {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: $text-primary;
    
    i {
      color: $primary;
      font-size: 12px;
    }
  }
  
  .item-price {
    font-size: 14px;
    font-weight: 600;
    color: $text-primary;
  }
  
  &.main-product {
    .item-name { font-weight: 600; }
  }
}

.pages-list {
  padding: 12px 0;
  
  .pages-label {
    display: block;
    font-size: 12px;
    color: $text-secondary;
    margin-bottom: 8px;
  }
  
  .pages-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
  }
  
  .page-tag {
    display: inline-block;
    padding: 4px 10px;
    background: rgba($primary, 0.1);
    border-radius: 6px;
    font-size: 11px;
    color: $primary;
    font-weight: 500;
  }
}

.summary-divider {
  height: 1px;
  background: $gray-light;
  margin: 16px 0;
}

.summary-totals {
  .total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    font-size: 14px;
    color: $text-secondary;
    
    &.discount {
      color: $secondary;
      
      .discount-value {
        font-weight: 600;
      }
    }
    
    &.final {
      padding-top: 16px;
      margin-top: 8px;
      border-top: 2px solid $gray-light;
      font-size: 16px;
      font-weight: 700;
      color: $text-primary;
      
      .final-price {
        font-size: 24px;
        color: $primary;
      }
    }
  }
}

.summary-benefits {
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid $gray-light;
  
  .benefit {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 0;
    font-size: 13px;
    color: $text-secondary;
    
    i {
      color: $secondary;
      font-size: 14px;
    }
  }
}

.promo-mini-banner {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 16px;
  background: linear-gradient(135deg, rgba($accent, 0.1), rgba($accent, 0.05));
  border: 1px solid rgba($accent, 0.2);
  border-radius: 12px;
  margin-top: 16px;
  
  .promo-icon {
    font-size: 18px;
  }
  
  .promo-text {
    font-size: 12px;
    font-weight: 600;
    color: darken($accent, 10%);
  }
}

// ==================
// SPECIALIST TOGGLE
// ==================
.specialist-toggle-card {
  border: 2px solid $gray-light;
  border-radius: 12px;
  background: #f9fafb;
  transition: all 0.3s ease;
  
  &:hover {
    border-color: lighten($primary, 20%);
    background: lighten($primary, 48%);
  }
  
  &.specialist-active {
    border-color: $primary;
    background: lighten($primary, 46%);
    box-shadow: 0 0 0 4px rgba($primary, 0.08);
  }
  
  .specialist-toggle-label {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 14px 16px;
    cursor: pointer;
    
    .specialist-info {
      flex: 1;
      
      .specialist-header {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 6px;
        
        .specialist-title {
          font-size: 14px;
          font-weight: 600;
          color: $text-primary;
        }
        
        .specialist-badge {
          font-size: 12px;
          font-weight: 700;
          color: darken($primary, 10%);
          background: lighten($primary, 40%);
          padding: 3px 9px;
          border-radius: 20px;
          white-space: nowrap;
        }
      }
      
      .specialist-desc {
        font-size: 12px;
        color: $text-secondary;
        line-height: 1.5;
        margin: 0;
      }
    }
    
    .toggle-switch {
      position: relative;
      flex-shrink: 0;
      width: 48px;
      height: 26px;
      margin-top: 2px;
      
      input {
        opacity: 0;
        width: 0;
        height: 0;
        position: absolute;
        
        &:checked + .toggle-slider {
          background: $primary;
          
          &::before {
            transform: translateX(22px);
          }
        }
      }
      
      .toggle-slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background: #d1d5db;
        border-radius: 26px;
        transition: all 0.3s ease;
        
        &::before {
          content: '';
          position: absolute;
          height: 20px;
          width: 20px;
          left: 3px;
          bottom: 3px;
          background: white;
          border-radius: 50%;
          transition: all 0.3s ease;
          box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
      }
    }
  }
}

.specialist-item {
  color: darken($primary, 5%);
  
  .item-name {
    color: darken($primary, 5%);
    
    i { color: $primary; }
  }
  
  .item-price {
    color: darken($primary, 5%);
    font-weight: 700;
  }
}
</style>
