<template>
  <div class="price-calculator">
    <div class="calculator-container">
      <!-- Header -->
      <div class="calculator-header">
        <h2 class="calculator-title">
          <i class="fas fa-calculator"></i>
          Calcule o Investimento do Seu Projeto
        </h2>
        <p class="calculator-description">
          Configure as opções e veja o preço em tempo real. 
          Transparência total, sem surpresas.
        </p>
      </div>

      <!-- Calculator Body -->
      <div class="calculator-body">
        <!-- Tipo de Site -->
        <div class="calc-section">
          <label class="calc-label">
            <i class="fas fa-laptop-code"></i>
            Tipo de Site
          </label>
          <div class="calc-options">
            <button
              v-for="type in siteTypes"
              :key="type.id"
              :class="['calc-option', { active: selectedType === type.id }]"
              @click="selectType(type.id)"
            >
              <i :class="type.icon"></i>
              <span class="option-name">{{ type.name }}</span>
              <span class="option-price">{{ formatPrice(type.price) }}</span>
            </button>
          </div>
        </div>

        <!-- Páginas Extras (se aplicável) -->
        <div class="calc-section" v-if="showExtraPages">
          <label class="calc-label">
            <i class="fas fa-file-alt"></i>
            Páginas Extras
          </label>
          <div class="calc-counter">
            <button 
              class="counter-btn" 
              @click="decrementPages"
              :disabled="extraPages === 0"
            >
              <i class="fas fa-minus"></i>
            </button>
            <div class="counter-display">
              <span class="counter-value">{{ extraPages }}</span>
              <span class="counter-label">páginas</span>
            </div>
            <button 
              class="counter-btn" 
              @click="incrementPages"
              :disabled="extraPages >= 10"
            >
              <i class="fas fa-plus"></i>
            </button>
          </div>
          <p class="calc-hint">
            R$ 199 por página adicional • Máximo 10 páginas
          </p>
        </div>

        <!-- Add-ons -->
        <div class="calc-section">
          <label class="calc-label">
            <i class="fas fa-puzzle-piece"></i>
            Add-ons Opcionais
          </label>
          <div class="calc-addons">
            <label
              v-for="addon in addons"
              :key="addon.id"
              :class="['addon-item', { disabled: addon.requiresCustom && selectedType === 'basic' }]"
            >
              <input
                type="checkbox"
                :value="addon.id"
                v-model="selectedAddons"
                :disabled="addon.requiresCustom && selectedType === 'basic'"
              >
              <div class="addon-content">
                <div class="addon-header">
                  <i :class="addon.icon"></i>
                  <span class="addon-name">{{ addon.name }}</span>
                </div>
                <span class="addon-price">{{ addon.price }}</span>
              </div>
              <span class="addon-description">{{ addon.description }}</span>
            </label>
          </div>
        </div>

        <!-- Funcionalidades Complexas -->
        <div class="calc-section">
          <label class="calc-label">
            <i class="fas fa-cogs"></i>
            Necessidades Especiais
          </label>
          <div class="calc-checkboxes">
            <label
              v-for="feature in complexFeatures"
              :key="feature.id"
              class="checkbox-item"
            >
              <input
                type="checkbox"
                :value="feature.id"
                v-model="selectedComplexFeatures"
              >
              <span class="checkbox-label">{{ feature.name }}</span>
            </label>
          </div>
          <div v-if="needsProposal" class="calc-alert">
            <i class="fas fa-info-circle"></i>
            <div class="alert-content">
              <strong>Projeto Customizado Detectado</strong>
              <p>
                Sua necessidade requer orçamento personalizado. 
                Entre em contato para proposta detalhada.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Total e CTA -->
      <div class="calculator-footer">
        <div class="total-section">
          <div class="total-label">Investimento Total</div>
          <div class="total-price">
            <span v-if="!needsProposal" class="price-value">
              {{ formatPrice(totalPrice) }}
            </span>
            <span v-else class="price-custom">Sob Proposta</span>
            <span class="price-period">/ano</span>
          </div>
          <div class="total-breakdown" v-if="!needsProposal && breakdown.length > 0">
            <div 
              v-for="item in breakdown" 
              :key="item.label"
              class="breakdown-item"
            >
              <span>{{ item.label }}</span>
              <span>{{ formatPrice(item.value) }}</span>
            </div>
          </div>
        </div>

        <div class="cta-section">
          <button 
            v-if="!needsProposal"
            class="calc-btn-primary"
            @click="handleContract"
          >
            <i class="fas fa-shopping-cart"></i>
            Contratar Agora
          </button>
          <button 
            v-else
            class="calc-btn-secondary"
            @click="handleRequestProposal"
          >
            <i class="fas fa-comment-dots"></i>
            Solicitar Proposta Customizada
          </button>
          <button class="calc-btn-outline" @click="handleContact">
            <i class="fas fa-whatsapp"></i>
            Falar no WhatsApp
          </button>
        </div>

        <div class="guarantee-note">
          <i class="fas fa-shield-alt"></i>
          <span>Satisfação garantida ou seu dinheiro de volta</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PriceCalculator',
  data() {
    return {
      affiliateRef: null,
      // Tipos de site
      siteTypes: [
        {
          id: 'basic',
          name: 'Site Vitrine',
          icon: 'fas fa-laptop',
          price: 500,
          description: 'Single page profissional'
        },
        {
          id: 'multi',
          name: 'Multi-páginas',
          icon: 'fas fa-layer-group',
          price: 800,
          description: 'Até 3 páginas incluídas'
        },
        {
          id: 'custom',
          name: 'Customizado',
          icon: 'fas fa-magic',
          price: 0,
          description: 'Sob proposta'
        }
      ],
      selectedType: 'basic',
      extraPages: 0,

      // Add-ons
      addons: [
        {
          id: 'seo',
          name: 'SEO Básico',
          icon: 'fas fa-search',
          price: 'R$ 200',
          value: 200,
          description: 'Otimização para Google'
        },
        {
          id: 'language',
          name: 'Idioma Extra',
          icon: 'fas fa-language',
          price: 'R$ 300',
          value: 300,
          description: 'Versão em inglês ou espanhol'
        },
        {
          id: 'analytics',
          name: 'Integrações',
          icon: 'fas fa-chart-line',
          price: 'R$ 150',
          value: 150,
          description: 'GA + Facebook Pixel'
        },
        {
          id: 'copy',
          name: 'Revisão de Copy',
          icon: 'fas fa-pen-fancy',
          price: 'R$ 250',
          value: 250,
          description: 'Textos otimizados'
        }
      ],
      selectedAddons: [],

      // Funcionalidades complexas
      complexFeatures: [
        { id: 'ecommerce', name: 'E-commerce (loja virtual)' },
        { id: 'backend', name: 'Painel administrativo' },
        { id: 'login', name: 'Área do cliente com login' },
        { id: 'integration', name: 'Integrações avançadas (APIs, CRM)' },
        { id: 'payment', name: 'Sistema de pagamentos customizado' },
        { id: 'automation', name: 'Automações e workflows' }
      ],
      selectedComplexFeatures: []
    };
  },
  created() {
    // Detectar cookie de afiliado
    const match = document.cookie.match(/(?:^|; )unli_aff=([a-f0-9]{16})/);
    if (match) this.affiliateRef = match[1];
  },
  computed: {
    showExtraPages() {
      return this.selectedType === 'multi';
    },
    
    needsProposal() {
      return this.selectedType === 'custom' || this.selectedComplexFeatures.length > 0;
    },
    
    basePrice() {
      const type = this.siteTypes.find(t => t.id === this.selectedType);
      return type ? type.price : 0;
    },
    
    extraPagesPrice() {
      return this.extraPages * 199;
    },
    
    addonsPrice() {
      return this.selectedAddons.reduce((total, addonId) => {
        const addon = this.addons.find(a => a.id === addonId);
        return total + (addon ? addon.value : 0);
      }, 0);
    },
    
    totalPrice() {
      if (this.needsProposal) return 0;
      return this.basePrice + this.extraPagesPrice + this.addonsPrice;
    },
    
    breakdown() {
      if (this.needsProposal) return [];
      
      const items = [];
      
      // Base
      const type = this.siteTypes.find(t => t.id === this.selectedType);
      if (type && type.price > 0) {
        items.push({ label: type.name, value: type.price });
      }
      
      // Páginas extras
      if (this.extraPages > 0) {
        items.push({ 
          label: `${this.extraPages} Página${this.extraPages > 1 ? 's' : ''} Extra${this.extraPages > 1 ? 's' : ''}`, 
          value: this.extraPagesPrice 
        });
      }
      
      // Add-ons
      this.selectedAddons.forEach(addonId => {
        const addon = this.addons.find(a => a.id === addonId);
        if (addon) {
          items.push({ label: addon.name, value: addon.value });
        }
      });
      
      return items;
    }
  },
  methods: {
    selectType(typeId) {
      this.selectedType = typeId;
      if (typeId !== 'multi') {
        this.extraPages = 0;
      }
      this.trackCalculatorChange('type', typeId);
    },
    
    incrementPages() {
      if (this.extraPages < 10) {
        this.extraPages++;
        this.trackCalculatorChange('extra_pages', this.extraPages);
      }
    },
    
    decrementPages() {
      if (this.extraPages > 0) {
        this.extraPages--;
        this.trackCalculatorChange('extra_pages', this.extraPages);
      }
    },
    
    formatPrice(value) {
      if (!value) return 'R$ 0';
      return `R$ ${value.toLocaleString('pt-BR')}`;
    },
    
    handleContract() {
      this.trackCalculatorEvent('click_contract', this.totalPrice);
      this.$emit('contract', {
        type: this.selectedType,
        extraPages: this.extraPages,
        addons: this.selectedAddons,
        total: this.totalPrice
      });
      // Redirecionar para checkout ou abrir modal
    },
    
    handleRequestProposal() {
      this.trackCalculatorEvent('request_proposal', 'custom');
      this.$emit('request-proposal', {
        type: this.selectedType,
        extraPages: this.extraPages,
        addons: this.selectedAddons,
        complexFeatures: this.selectedComplexFeatures
      });
      // Redirecionar para formulário ou abrir modal
    },
    
    handleContact() {
      this.trackCalculatorEvent('contact_whatsapp', this.totalPrice);
      const message = this.buildWhatsAppMessage();
      const whatsappNumber = process.env.VUE_APP_WHATSAPP_SDR || '5511911019666';
      window.open(`https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`, '_blank');
    },
    
    buildWhatsAppMessage() {
      let message = '👋 Olá! Vim da calculadora de preços.\n\n';
      
      const type = this.siteTypes.find(t => t.id === this.selectedType);
      message += `📋 *Tipo:* ${type.name}\n`;
      
      if (this.extraPages > 0) {
        message += `📄 *Páginas extras:* ${this.extraPages}\n`;
      }
      
      if (this.selectedAddons.length > 0) {
        message += `\n✨ *Add-ons selecionados:*\n`;
        this.selectedAddons.forEach(addonId => {
          const addon = this.addons.find(a => a.id === addonId);
          if (addon) message += `• ${addon.name}\n`;
        });
      }
      
      if (this.selectedComplexFeatures.length > 0) {
        message += `\n⚙️ *Funcionalidades especiais:*\n`;
        this.selectedComplexFeatures.forEach(featureId => {
          const feature = this.complexFeatures.find(f => f.id === featureId);
          if (feature) message += `• ${feature.name}\n`;
        });
      }
      
      if (this.needsProposal) {
        message += `\n💰 *Investimento:* Sob proposta\n`;
      } else {
        message += `\n💰 *Investimento calculado:* ${this.formatPrice(this.totalPrice)}/ano\n`;
      }
      
      message += `\nGostaria de mais informações!`;
      
      // Incluir código de afiliado se existir
      if (this.affiliateRef) {
        message += `\n\n📎 Indicado pelo afiliado: ${this.affiliateRef}`;
      }
      
      return message;
    },
    
    trackCalculatorChange(field, value) {
      // Google Analytics / Facebook Pixel
      // eslint-disable-next-line no-undef
      if (typeof gtag !== 'undefined') {
        // eslint-disable-next-line no-undef
        gtag('event', 'calculator_change', {
          event_category: 'Price Calculator',
          event_label: field,
          value: value
        });
      }
    },
    
    trackCalculatorEvent(action, value) {
      // eslint-disable-next-line no-undef
      if (typeof gtag !== 'undefined') {
        // eslint-disable-next-line no-undef
        gtag('event', action, {
          event_category: 'Price Calculator',
          value: value
        });
      }
      
      // eslint-disable-next-line no-undef
      if (typeof fbq !== 'undefined') {
        // eslint-disable-next-line no-undef
        fbq('track', 'AddToCart', { 
          value: this.totalPrice, 
          currency: 'BRL' 
        });
      }
    }
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/__colors.scss';
@import '@/assets/sass/settings/__fonts.scss';

.price-calculator {
  width: 100%;
  padding: 60px 20px;
  background: $gray-lightness;
}

.calculator-container {
  max-width: 900px;
  margin: 0 auto;
  background: $white;
  border-radius: 24px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

// Header
.calculator-header {
  padding: 40px;
  background: linear-gradient(135deg, $p-color 0%, $p-dark 100%);
  color: $white;
  text-align: center;

  .calculator-title {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 12px;

    i {
      font-size: 2rem;
    }
  }

  .calculator-description {
    font-size: 1.1rem;
    opacity: 0.95;
    line-height: 1.6;
  }
}

// Body
.calculator-body {
  padding: 40px;
}

.calc-section {
  margin-bottom: 40px;

  &:last-child {
    margin-bottom: 0;
  }
}

.calc-label {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 1.2rem;
  font-weight: 700;
  color: $gray-darkness;
  margin-bottom: 16px;

  i {
    color: $p-color;
  }
}

.calc-hint {
  margin-top: 8px;
  font-size: 0.9rem;
  color: $gray-medium;
}

// Opções de tipo
.calc-options {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.calc-option {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 24px;
  background: $gray-lightness;
  border: 2px solid transparent;
  border-radius: 16px;
  cursor: pointer;
  transition: all 0.3s ease;

  i {
    font-size: 2.5rem;
    color: $gray-medium;
    transition: color 0.3s ease;
  }

  .option-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: $gray-darkness;
  }

  .option-price {
    font-size: 1.3rem;
    font-weight: 800;
    color: $p-color;
  }

  &:hover {
    background: $white;
    border-color: $p-color;
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba($p-color, 0.2);

    i {
      color: $p-color;
    }
  }

  &.active {
    background: linear-gradient(135deg, rgba($p-color, 0.1) 0%, rgba($p-dark, 0.05) 100%);
    border-color: $p-color;
    box-shadow: 0 0 0 4px rgba($p-color, 0.1);

    i {
      color: $p-color;
    }
  }
}

// Counter
.calc-counter {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 24px;
  padding: 24px;
  background: $gray-lightness;
  border-radius: 16px;
}

.counter-btn {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: $white;
  border: 2px solid $gray-light;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;

  i {
    font-size: 1.2rem;
    color: $p-color;
  }

  &:hover:not(:disabled) {
    border-color: $p-color;
    box-shadow: 0 4px 12px rgba($p-color, 0.2);
  }

  &:disabled {
    opacity: 0.4;
    cursor: not-allowed;
  }
}

.counter-display {
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 100px;

  .counter-value {
    font-size: 3rem;
    font-weight: 900;
    color: $p-color;
    line-height: 1;
  }

  .counter-label {
    font-size: 0.9rem;
    color: $gray-medium;
    margin-top: 4px;
  }
}

// Add-ons
.calc-addons {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.addon-item {
  position: relative;
  display: flex;
  align-items: start;
  gap: 16px;
  padding: 20px;
  background: $gray-lightness;
  border: 2px solid transparent;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;

  input[type="checkbox"] {
    margin-top: 4px;
    width: 20px;
    height: 20px;
    cursor: pointer;
  }

  .addon-content {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: start;
  }

  .addon-header {
    display: flex;
    align-items: center;
    gap: 10px;

    i {
      font-size: 1.3rem;
      color: $p-color;
    }

    .addon-name {
      font-size: 1.1rem;
      font-weight: 600;
      color: $gray-darkness;
    }
  }

  .addon-price {
    font-size: 1rem;
    font-weight: 700;
    color: $p-color;
  }

  .addon-description {
    position: absolute;
    bottom: 8px;
    left: 56px;
    font-size: 0.85rem;
    color: $gray-medium;
  }

  &:hover:not(.disabled) {
    background: $white;
    border-color: $p-color;
  }

  &.disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
}

// Checkboxes
.calc-checkboxes {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 12px;
  margin-bottom: 16px;
}

.checkbox-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px;
  background: $gray-lightness;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;

  input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
  }

  .checkbox-label {
    font-size: 0.95rem;
    color: $gray-darkness;
  }

  &:hover {
    background: $white;
  }
}

// Alert
.calc-alert {
  display: flex;
  gap: 16px;
  padding: 20px;
  background: linear-gradient(135deg, rgba($accent-purple, 0.1) 0%, rgba($accent-purple, 0.05) 100%);
  border-left: 4px solid $accent-purple;
  border-radius: 12px;

  i {
    flex-shrink: 0;
    font-size: 1.5rem;
    color: $accent-purple;
    margin-top: 2px;
  }

  .alert-content {
    strong {
      display: block;
      font-size: 1.1rem;
      color: $gray-darkness;
      margin-bottom: 8px;
    }

    p {
      font-size: 0.95rem;
      color: $gray-medium;
      line-height: 1.6;
      margin: 0;
    }
  }
}

// Footer
.calculator-footer {
  padding: 40px;
  background: $gray-lightness;
  border-top: 2px solid $gray-light;
}

.total-section {
  margin-bottom: 32px;
  text-align: center;

  .total-label {
    font-size: 1rem;
    color: $gray-medium;
    margin-bottom: 8px;
  }

  .total-price {
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: 4px;
    margin-bottom: 24px;

    .price-value,
    .price-custom {
      font-size: 4rem;
      font-weight: 900;
      color: $p-color;
      line-height: 1;
    }

    .price-period {
      font-size: 1.5rem;
      font-weight: 600;
      color: $gray-medium;
    }
  }

  .total-breakdown {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 20px;
    background: $white;
    border-radius: 12px;
  }

  .breakdown-item {
    display: flex;
    justify-content: space-between;
    font-size: 0.95rem;
    color: $gray-medium;

    span:last-child {
      font-weight: 600;
      color: $gray-darkness;
    }
  }
}

.cta-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 24px;

  button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 18px;
    font-size: 1.1rem;
    font-weight: 600;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;

    i {
      font-size: 1.2rem;
    }
  }

  .calc-btn-primary {
    background: linear-gradient(135deg, $p-color 0%, $p-dark 100%);
    color: $white;
    box-shadow: 0 4px 14px rgba($p-color, 0.4);

    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba($p-color, 0.5);
    }
  }

  .calc-btn-secondary {
    background: $white;
    color: $p-color;
    border: 2px solid $p-color;

    &:hover {
      background: $p-color;
      color: $white;
    }
  }

  .calc-btn-outline {
    background: transparent;
    color: $gray-darkness;
    border: 2px solid $gray-light;

    &:hover {
      border-color: $gray-darkness;
      background: $gray-darkness;
      color: $white;
    }
  }
}

.guarantee-note {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding-top: 24px;
  border-top: 1px solid $gray-light;
  font-size: 0.95rem;
  color: $gray-medium;

  i {
    color: $p-color;
    font-size: 1.2rem;
  }
}

// Responsive
@media (max-width: 768px) {
  .calculator-header {
    padding: 32px 24px;

    .calculator-title {
      font-size: 1.5rem;

      i {
        font-size: 1.5rem;
      }
    }

    .calculator-description {
      font-size: 1rem;
    }
  }

  .calculator-body {
    padding: 32px 24px;
  }

  .calculator-footer {
    padding: 32px 24px;
  }

  .total-price {
    .price-value {
      font-size: 3rem !important;
    }
  }

  .calc-checkboxes {
    grid-template-columns: 1fr;
  }
}
</style>
