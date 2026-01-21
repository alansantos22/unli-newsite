<template>
  <div class="site-configurator">
    <!-- Debug Mode Indicator -->
    <div v-if="$store.state.ConfigModule?.debug" class="debug-badge">
      <i class="fas fa-tools"></i>
      <span>DEBUG MODE</span>
      <button @click="$store.dispatch('ConfigModule/disableDebug')" class="debug-toggle">
        Desativar
      </button>
    </div>
    
    <!-- Progress Bar -->
    <div class="progress-bar">
      <div 
        v-for="(step, index) in steps" 
        :key="index"
        :class="['progress-step', { 
          active: currentStep === index + 1,
          completed: currentStep > index + 1 
        }]"
      >
        <div class="step-number">{{ index + 1 }}</div>
        <div class="step-label">{{ step }}</div>
      </div>
    </div>

    <!-- Configurator Body -->
    <div class="configurator-body">
      <!-- Etapa 1: Escolha do Produto -->
      <transition name="fade" mode="out-in">
        <div v-if="currentStep === 1" class="step-content step-product">
          <h2 class="step-title">Escolha Seu Produto</h2>
          <p class="step-description">
            Selecione a opção ideal para seu negócio. 
            Você poderá adicionar itens no próximo passo.
          </p>

          <div class="product-cards">
            <div
              v-for="(product, key) in config.products"
              :key="key"
              :class="['product-card', { selected: selectedProduct === key }]"
              @click="selectProduct(key)"
            >
              <div class="product-header">
                <div class="product-icon">
                  <i :class="getProductIcon(key)"></i>
                </div>
                <h3 class="product-name">{{ product.name }}</h3>
                <div class="product-price">
                  <span class="price-label">A partir de</span>
                  <span class="price-value">{{ formatPrice(product.base_price) }}</span>
                </div>
              </div>
              <p class="product-description">{{ product.description }}</p>
              <div class="product-action">
                <button class="btn-select">
                  <i class="fas fa-check"></i>
                  Selecionar
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Etapa 2: Monte Seu Site -->
        <div v-else-if="currentStep === 2" class="step-content step-addons">
          <h2 class="step-title">Monte Seu Site</h2>
          <p class="step-description">
            Adicione páginas e conteúdo conforme sua necessidade.
          </p>

          <!-- Páginas Adicionais (somente Site Completo) -->
          <div v-if="selectedProduct === 'site_complete'" class="addon-section">
            <h3 class="section-title">
              <i class="fas fa-file-alt"></i>
              Páginas Adicionais
            </h3>
            <div class="addon-grid">
              <div
                v-for="(page, key) in config.page_addons"
                :key="key"
                class="addon-item"
              >
                <div class="addon-info">
                  <span class="addon-name">
                    {{ page.name }}
                    <span class="addon-tooltip">
                      <i class="fas fa-info-circle"></i>
                      <span class="tooltip-text">{{ getPageTooltip(key) }}</span>
                    </span>
                  </span>
                  <span class="addon-price">{{ formatPrice(page.price) }}</span>
                </div>
                <div class="addon-counter">
                  <button 
                    class="counter-btn"
                    :disabled="getPageCount(key) === 0"
                    @click="decrementPage(key)"
                  >
                    <i class="fas fa-minus"></i>
                  </button>
                  <span class="counter-value">{{ getPageCount(key) }}</span>
                  <button 
                    class="counter-btn"
                    @click="incrementPage(key)"
                  >
                    <i class="fas fa-plus"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Conteúdo Pesado -->
          <div class="addon-section">
            <h3 class="section-title">
              <i class="fas fa-photo-video"></i>
              Conteúdo Especial
            </h3>
            <div class="addon-toggles">
              <label
                v-for="(content, key) in config.content_addons"
                :key="key"
                class="toggle-item"
              >
                <input
                  type="checkbox"
                  :value="key"
                  v-model="selectedContentAddons"
                >
                <div class="toggle-content">
                  <div class="toggle-info">
                    <span class="toggle-name">
                      {{ content.name }}
                      <span class="addon-tooltip">
                        <i class="fas fa-info-circle"></i>
                        <span class="tooltip-text">{{ getContentTooltip(key) }}</span>
                      </span>
                    </span>
                    <span class="toggle-price">+{{ formatPrice(content.price) }}</span>
                  </div>
                  <div class="toggle-switch">
                    <span class="switch"></span>
                  </div>
                </div>
              </label>
            </div>
          </div>

          <!-- Limites do Plano -->
          <div class="plan-info">
            <div class="info-icon">
              <i class="fas fa-info-circle"></i>
            </div>
            <div class="info-content">
              <h4>Incluído no Plano:</h4>
              <ul>
                <li><i class="fas fa-check"></i> Suporte de disponibilidade (site fora do ar)</li>
                <li><i class="fas fa-check"></i> Acesso exclusivo ao nosso sistema para alterações</li>
                <li><i class="fas fa-check"></i> Domínio grátis (.com.br)</li>
                <li><i class="fas fa-check"></i> Certificado SSL grátis (HTTPS)</li>
                <li><i class="fas fa-check"></i> Otimização de SEO simples grátis</li>
                <li><i class="fas fa-check"></i> Entrega em até 10 dias</li>
              </ul>
            </div>
          </div>

          <!-- Pergunta Final -->
          <!-- Upsell Box para Landing Page -->
          <div v-if="selectedProduct === 'landing'" class="upsell-box">
            <div class="upsell-header">
              <i class="fas fa-arrow-up"></i>
              <h3>Quer o site mais personalizado por apenas mais R$20 por ano?</h3>
            </div>
            <p class="upsell-description">
              Adicione páginas extras como Sobre, Serviços, Portfólio e muito mais!
              Transforme seu site simples em uma presença completa na web.
            </p>
            <div class="upsell-actions">
              <button class="btn-upsell-accept" @click="upgradeToComplete">
                <i class="fas fa-star"></i>
                Sim, quero o site completo
              </button>
              <button class="btn-upsell-decline" @click="proceedToCheckout">
                Não, quero um site mais simples
              </button>
            </div>
          </div>

          <!-- Pergunta de Customização para Site Completo -->
          <div v-else class="custom-question">
            <h3>Precisa de algo mais personalizado?</h3>
            <p>E-commerce, integrações avançadas, área de login, etc.</p>
            <div class="question-actions">
              <button class="btn-standard" @click="proceedToCheckout">
                Não, quero um site mais simples
              </button>
              <button class="btn-custom" @click="openCustomForm">
                Sim, quero um site mais completo
              </button>
            </div>
            <div class="back-to-landing">
              <a @click="selectedProduct = 'landing'">
                ← Na verdade, prefiro a landing page simples
              </a>
            </div>
          </div>
        </div>

        <!-- Etapa 3: Dados e Arquivos -->
        <div v-else-if="currentStep === 3" class="step-content step-briefing">
          <h2 class="step-title">Dados e Arquivos</h2>
          <p class="step-description">
            Preencha as informações para criarmos seu site.
          </p>

          <form class="briefing-form" @submit.prevent="submitOrder">
            <!-- Dados Básicos -->
            <div class="form-section">
              <h3 class="form-section-title">Informações da Empresa</h3>
              
              <div class="form-row">
                <div class="form-group">
                  <label>Nome da Empresa *</label>
                  <input 
                    type="text" 
                    v-model="briefing.company_name"
                    placeholder="Ex: UNLI Studio"
                    required
                  >
                </div>

                <div class="form-group">
                  <label>WhatsApp *</label>
                  <input 
                    type="tel" 
                    v-model="briefing.whatsapp"
                    placeholder="(00) 00000-0000"
                    required
                  >
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>E-mail *</label>
                  <input 
                    type="email" 
                    v-model="briefing.email"
                    placeholder="contato@empresa.com"
                    required
                  >
                </div>

                <div class="form-group">
                  <label>Endereço</label>
                  <input 
                    type="text" 
                    v-model="briefing.address"
                    placeholder="Rua, Número, Cidade - UF"
                  >
                </div>
              </div>
            </div>

            <!-- Links -->
            <div class="form-section">
              <h3 class="form-section-title">Links e Redes Sociais</h3>
              
              <div class="form-row">
                <div class="form-group">
                  <label>Instagram</label>
                  <input 
                    type="url" 
                    v-model="briefing.instagram"
                    placeholder="https://instagram.com/sua_empresa"
                  >
                </div>

                <div class="form-group">
                  <label>Google Maps</label>
                  <input 
                    type="url" 
                    v-model="briefing.google_maps"
                    placeholder="Link do Google Maps"
                  >
                </div>
              </div>
            </div>

            <!-- Estilo -->
            <div class="form-section">
              <h3 class="form-section-title">Estilo Visual</h3>
              <div class="style-options">
                <label
                  v-for="style in styles"
                  :key="style.value"
                  :class="['style-card', { selected: briefing.style === style.value }]"
                >
                  <input 
                    type="radio" 
                    name="style"
                    :value="style.value"
                    v-model="briefing.style"
                  >
                  <div class="style-content">
                    <i :class="style.icon"></i>
                    <span>{{ style.label }}</span>
                  </div>
                </label>
              </div>
            </div>

            <!-- Conteúdo -->
            <div class="form-section">
              <h3 class="form-section-title">Conteúdo do Site</h3>
              
              <div class="form-group">
                <label>Texto da Página Principal *</label>
                <textarea 
                  v-model="briefing.main_content"
                  rows="6"
                  placeholder="Descreva sua empresa, serviços, diferenciais..."
                  required
                ></textarea>
                <small>Inclua: apresentação, serviços, diferenciais, CTA</small>
              </div>

              <!-- Textos de Páginas Adicionais -->
              <div 
                v-for="(count, pageKey) in selectedPages"
                :key="pageKey"
                v-show="count > 0"
                class="form-group"
              >
                <label>Texto: {{ config.page_addons[pageKey].name }}</label>
                <textarea 
                  v-model="briefing.page_contents[pageKey]"
                  rows="4"
                  :placeholder="`Conteúdo para a página ${config.page_addons[pageKey].name}...`"
                ></textarea>
              </div>
            </div>

            <!-- Uploads -->
            <div class="form-section">
              <h3 class="form-section-title">Arquivos</h3>
              
              <!-- Vídeo (se selecionado) -->
              <div v-if="selectedContentAddons.includes('video')" class="form-group">
                <label>Upload de Vídeo</label>
                <input 
                  type="file" 
                  accept="video/*"
                  @change="handleVideoUpload"
                  class="file-input"
                >
                <small>Formato aceito: MP4, MOV, AVI (máx. 50MB)</small>
              </div>

              <!-- PDF (se selecionado) -->
              <div v-if="selectedContentAddons.includes('pdf')" class="form-group">
                <label>Upload de PDF</label>
                <input 
                  type="file" 
                  accept="application/pdf"
                  @change="handlePdfUpload"
                  class="file-input"
                >
                <small>Formato aceito: PDF (máx. 10MB)</small>
              </div>

              <!-- Imagens (links) -->
              <div class="form-group">
                <label>Links de Imagens</label>
                <textarea 
                  v-model="briefing.image_links"
                  rows="3"
                  placeholder="Cole os links das imagens (um por linha)"
                ></textarea>
                <small>Pode ser de Google Drive, Dropbox, etc.</small>
              </div>
            </div>

            <!-- Resumo do Pedido -->
            <div class="order-summary">
              <h3>Resumo do Pedido</h3>
              <div class="summary-items">
                <div class="summary-item">
                  <span>{{ currentProductName }}</span>
                  <span>{{ formatPrice(basePrice) }}</span>
                </div>
                <div 
                  v-for="(count, pageKey) in selectedPages"
                  :key="pageKey"
                  v-show="count > 0"
                  class="summary-item"
                >
                  <span>{{ config.page_addons[pageKey].name }} ({{ count }}x)</span>
                  <span>{{ formatPrice(config.page_addons[pageKey].price * count) }}</span>
                </div>
                <div 
                  v-for="addonKey in selectedContentAddons"
                  :key="addonKey"
                  class="summary-item"
                >
                  <span>{{ config.content_addons[addonKey].name }}</span>
                  <span>{{ formatPrice(config.content_addons[addonKey].price) }}</span>
                </div>
              </div>
              <div class="summary-total">
                <div class="total-row subtotal">
                  <span>Subtotal</span>
                  <span>{{ formatPrice(subtotal) }}</span>
                </div>
                <div class="total-row installments">
                  <span>12x no cartão</span>
                  <span class="highlight">12x de {{ formatPrice(installmentValue) }}</span>
                </div>
                <div class="total-row cash">
                  <span>À vista (10% desc.)</span>
                  <span class="highlight-success">{{ formatPrice(cashPrice) }}</span>
                </div>
              </div>
            </div>

            <div class="step-navigation">
              <button type="button" class="btn-back" @click="currentStep = 1">
                <i class="fas fa-undo"></i>
                Recomeçar
              </button>
              <button type="submit" class="btn-submit" :disabled="isSubmitting">
                <i class="fas fa-check-circle"></i>
                {{ isSubmitting ? 'Processando...' : 'Finalizar Pedido' }}
              </button>
            </div>
          </form>
        </div>
      </transition>
    </div>

    <!-- Price Sidebar (sempre visível) -->
    <div class="price-sidebar">
      <div class="sidebar-header">
        <h3>Seu Pedido</h3>
      </div>
      
      <div class="sidebar-items">
        <div v-if="selectedProduct" class="sidebar-item">
          <span class="item-name">{{ currentProductName }}</span>
          <span class="item-price">{{ formatPrice(basePrice) }}</span>
        </div>
        
        <div 
          v-for="(count, pageKey) in selectedPages"
          :key="pageKey"
          v-show="count > 0"
          class="sidebar-item"
        >
          <span class="item-name">
            {{ config.page_addons[pageKey].name }} 
            <span class="item-qty">({{ count }}x)</span>
          </span>
          <span class="item-price">{{ formatPrice(config.page_addons[pageKey].price * count) }}</span>
        </div>

        <div 
          v-for="addonKey in selectedContentAddons"
          :key="addonKey"
          class="sidebar-item"
        >
          <span class="item-name">{{ config.content_addons[addonKey].name }}</span>
          <span class="item-price">{{ formatPrice(config.content_addons[addonKey].price) }}</span>
        </div>
      </div>

      <div class="sidebar-total">
        <div class="total-line">
          <span>Subtotal</span>
          <span>{{ formatPrice(subtotal) }}</span>
        </div>
        <div class="total-line installment">
          <span>12x no cartão</span>
          <span class="price-big">{{ formatPrice(installmentValue) }}</span>
        </div>
        <div class="total-line cash">
          <span>À vista</span>
          <span class="price-big success">{{ formatPrice(cashPrice) }}</span>
        </div>
      </div>

      <!-- Indicador de validação -->
      <div class="sidebar-validation" v-if="selectedProduct">
        <div v-if="isValidating" class="validation-status validating">
          <i class="fas fa-sync fa-spin"></i>
          <span>Validando...</span>
        </div>
        <div v-else-if="priceSource === 'server'" class="validation-status validated">
          <i class="fas fa-check-circle"></i>
          <span>Preços validados</span>
        </div>
        <div v-else class="validation-status local">
          <i class="fas fa-calculator"></i>
          <span>Cálculo local</span>
        </div>
      </div>

      <div class="sidebar-footer">
        <i class="fas fa-shield-alt"></i>
        <span>Pagamento 100% seguro</span>
      </div>
    </div>

    <!-- Modal: Orçamento Personalizado -->
    <transition name="modal">
      <div v-if="showCustomModal" class="modal-overlay" @click.self="closeCustomForm">
        <div class="modal-content">
          <button class="modal-close" @click="closeCustomForm">
            <i class="fas fa-times"></i>
          </button>
          
          <h2 class="modal-title">Orçamento Personalizado</h2>
          <p class="modal-description">
            Preencha suas informações e descreva o que você precisa. 
            Entraremos em contato em até 24h.
          </p>

          <form @submit.prevent="submitCustomRequest" class="custom-form">
            <div class="form-group">
              <label>Nome *</label>
              <input 
                type="text" 
                v-model="customRequest.name"
                placeholder="Seu nome"
                required
              >
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>WhatsApp *</label>
                <input 
                  type="tel" 
                  v-model="customRequest.whatsapp"
                  placeholder="(00) 00000-0000"
                  required
                >
              </div>

              <div class="form-group">
                <label>E-mail *</label>
                <input 
                  type="email" 
                  v-model="customRequest.email"
                  placeholder="seu@email.com"
                  required
                >
              </div>
            </div>

            <div class="form-group">
              <label>O que você precisa? *</label>
              <textarea 
                v-model="customRequest.description"
                rows="5"
                placeholder="Descreva o projeto: e-commerce, área de login, integrações, etc."
                required
              ></textarea>
            </div>

            <div class="form-group">
              <label>Prazo / Urgência</label>
              <select v-model="customRequest.urgency">
                <option value="">Selecione...</option>
                <option value="normal">Normal (até 30 dias)</option>
                <option value="urgent">Urgente (até 15 dias)</option>
                <option value="very_urgent">Muito Urgente (até 7 dias)</option>
              </select>
            </div>

            <div class="modal-actions">
              <button type="button" class="btn-cancel" @click="closeCustomForm">
                Cancelar
              </button>
              <button type="submit" class="btn-submit-modal" :disabled="isSubmittingCustom">
                <i class="fas fa-paper-plane"></i>
                {{ isSubmittingCustom ? 'Enviando...' : 'Enviar Solicitação' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
// Configuração carregada de JSON
import configData from '@/pages/SiteVitrine/config/site-configurator.json';
import PricingService from '@/core/services/PricingService';

export default {
  name: 'SiteConfigurator',
  data() {
    return {
      config: configData,
      currentStep: 1,
      steps: ['Produto', 'Personalize', 'Checkout'],
      
      // Seleções
      selectedProduct: null,
      selectedPages: {}, // { about: 1, services: 2, ... }
      selectedContentAddons: [], // ['video', 'pdf']
      
      // Briefing
      briefing: {
        company_name: '',
        whatsapp: '',
        email: '',
        address: '',
        instagram: '',
        google_maps: '',
        style: '',
        main_content: '',
        page_contents: {},
        image_links: '',
        video_file: null,
        pdf_file: null
      },
      
      // Estilos disponíveis
      styles: [
        { value: 'modern', label: 'Moderno', icon: 'fas fa-rocket' },
        { value: 'elegant', label: 'Elegante', icon: 'fas fa-gem' },
        { value: 'tech', label: 'Tech/Cyber', icon: 'fas fa-microchip' }
      ],
      
      // Modal personalizado
      showCustomModal: false,
      customRequest: {
        name: '',
        whatsapp: '',
        email: '',
        description: '',
        urgency: ''
      },
      
      // Estados
      isSubmitting: false,
      isSubmittingCustom: false,
      
      // Validação server-side
      serverValidatedPricing: null,
      serverAvailable: null, // null = não testado, true = online, false = offline
      isValidating: false
    };
  },
  async mounted() {
    // Verificar modo debug
    const debugMode = this.$store.state.ConfigModule?.debug;
    
    if (debugMode) {
      console.log('%c🔧 DEBUG MODE ATIVO', 'background: #ff9800; color: white; padding: 8px 16px; font-size: 14px; font-weight: bold; border-radius: 4px;');
      console.log('%c- API PHP desabilitada', 'color: #ff9800; font-weight: bold;');
      console.log('%c- Todos os cálculos são locais', 'color: #ff9800; font-weight: bold;');
      console.log('%c- Pedidos simulados (mock)', 'color: #ff9800; font-weight: bold;');
      console.log('%cPara desabilitar: $store.dispatch("ConfigModule/disableDebug")', 'color: #999; font-style: italic;');
    } else {
      console.log('%c✅ MODO PRODUÇÃO', 'background: #4caf50; color: white; padding: 8px 16px; font-size: 14px; font-weight: bold; border-radius: 4px;');
      console.log('%c- API PHP habilitada', 'color: #4caf50; font-weight: bold;');
      console.log('%c- Validações server-side ativas', 'color: #4caf50; font-weight: bold;');
    }
    
    // Testar disponibilidade da API
    this.serverAvailable = await PricingService.checkServerAvailability();
    
    if (this.serverAvailable) {
      console.info('✅ API Server disponível');
    } else {
      console.warn('🟡 API Server offline - usando cálculos locais');
    }
  },
  watch: {
    // Validar no servidor sempre que seleções mudarem
    selectedProduct() {
      this.validatePriceOnServer();
    },
    selectedPages: {
      deep: true,
      handler() {
        this.validatePriceOnServer();
      }
    },
    selectedContentAddons() {
      this.validatePriceOnServer();
    }
  },
  computed: {
    currentProductName() {
      return this.selectedProduct ? this.config.products[this.selectedProduct].name : '';
    },
    
    basePrice() {
      return this.selectedProduct ? this.config.products[this.selectedProduct].base_price : 0;
    },
    
    pagesTotal() {
      return Object.entries(this.selectedPages).reduce((sum, [key, count]) => {
        return sum + (this.config.page_addons[key].price * count);
      }, 0);
    },
    
    contentTotal() {
      return this.selectedContentAddons.reduce((sum, key) => {
        return sum + this.config.content_addons[key].price;
      }, 0);
    },
    
    subtotal() {
      // Se servidor validou, usar valor do servidor; senão, calcular localmente
      if (this.serverValidatedPricing && this.serverValidatedPricing.source === 'server') {
        return this.serverValidatedPricing.subtotal;
      }
      return this.basePrice + this.pagesTotal + this.contentTotal;
    },
    
    cashPrice() {
      if (this.serverValidatedPricing && this.serverValidatedPricing.source === 'server') {
        return this.serverValidatedPricing.avista;
      }
      // 10% desconto à vista (cálculo local)
      const discount = this.config.pricing_rules.cash_discount_percent / 100;
      return Math.round(this.subtotal * (1 - discount) * 100) / 100;
    },
    
    installmentTotal() {
      if (this.serverValidatedPricing && this.serverValidatedPricing.source === 'server') {
        return this.serverValidatedPricing.parcelado_total;
      }
      // 10% acréscimo no parcelado (cálculo local)
      const markup = this.config.pricing_rules.installments_12_markup_percent / 100;
      return Math.round(this.subtotal * (1 + markup) * 100) / 100;
    },
    
    installmentValue() {
      if (this.serverValidatedPricing && this.serverValidatedPricing.source === 'server') {
        return this.serverValidatedPricing.parcela_12;
      }
      // Valor de cada parcela (cálculo local)
      return Math.round((this.installmentTotal / this.config.pricing_rules.installments) * 100) / 100;
    },
    
    priceSource() {
      return this.serverValidatedPricing?.source || 'local';
    }
  },
  methods: {
    // Navegação
    nextStep() {
      if (this.currentStep < 3) {
        this.currentStep++;
      }
    },
    
    previousStep() {
      if (this.currentStep > 1) {
        this.currentStep--;
      }
    },
    
    proceedToCheckout() {
      this.currentStep = 3;
    },

    // Upgrade de Landing para Site Completo
    upgradeToComplete() {
      this.selectedProduct = 'site_complete';
      // Manter na etapa 2 para adicionar páginas
    },

    // Abrir formulário de contato para projetos customizados
    openCustomForm() {
      // Redirecionar para seção de contato
      window.location.hash = 'contact';
      this.$emit('close');
    },
    
    // Seleção de produto
    selectProduct(key) {
      this.selectedProduct = key;
      // Ir automaticamente para próxima etapa (menos cliques)
      setTimeout(() => {
        this.nextStep();
      }, 300); // Delay para feedback visual
    },
    
    getProductIcon(key) {
      return key === 'landing' ? 'fas fa-file-alt' : 'fas fa-layer-group';
    },
    
    // Páginas adicionais
    getPageCount(key) {
      return this.selectedPages[key] || 0;
    },
    
    incrementPage(key) {
      if (!this.selectedPages[key]) {
        this.selectedPages[key] = 0;
      }
      this.selectedPages[key]++;
    },
    
    decrementPage(key) {
      if (this.selectedPages[key] && this.selectedPages[key] > 0) {
        this.selectedPages[key]--;
      }
    },
    
    // Upload handlers
    handleVideoUpload(event) {
      const file = event.target.files[0];
      if (file && file.size <= 50 * 1024 * 1024) { // 50MB
        this.briefing.video_file = file;
      } else {
        alert('Arquivo muito grande. Máximo 50MB.');
        event.target.value = '';
      }
    },
    
    handlePdfUpload(event) {
      const file = event.target.files[0];
      if (file && file.size <= 10 * 1024 * 1024) { // 10MB
        this.briefing.pdf_file = file;
      } else {
        alert('Arquivo muito grande. Máximo 10MB.');
        event.target.value = '';
      }
    },
    
    // Validar preço no servidor (background)
    async validatePriceOnServer() {
      if (!this.selectedProduct) return;
      
      this.isValidating = true;
      
      // Debounce (evitar muitas chamadas)
      clearTimeout(this._validateTimeout);
      this._validateTimeout = setTimeout(async () => {
        const selection = {
          product: this.selectedProduct,
          pages: this.selectedPages,
          content: this.selectedContentAddons
        };
        
        const result = await PricingService.calculatePrice(selection, this.config);
        
        if (result.serverValidated) {
          this.serverValidatedPricing = result.pricing;
          this.serverAvailable = true;
          
          // Se servidor normalizou seleções, aplicar
          if (result.normalized && result.normalized !== selection) {
            console.info('🔄 Servidor normalizou seleções');
            this.selectedProduct = result.normalized.product;
            this.selectedPages = result.normalized.pages;
          }
        } else {
          this.serverValidatedPricing = result.pricing; // Usar valor local
          this.serverAvailable = false;
        }
        
        this.isValidating = false;
      }, 500); // 500ms debounce
    },
    
    // Submit final
    async submitOrder() {
      this.isSubmitting = true;
      
      try {
        // Montar dados do pedido (sem incluir preço - servidor calcula)
        const orderData = {
          product: this.selectedProduct,
          pages: this.selectedPages,
          content: this.selectedContentAddons,
          briefing: this.briefing,
          payment_method: 'avista' // TODO: adicionar seletor de forma de pagamento
        };
        
        // Tentar criar pedido no servidor
        const result = await PricingService.createOrder(orderData);
        
        if (result.ok) {
          // Sucesso: pedido criado no servidor
          console.log('✅ Pedido criado:', result.order_id);
          
          // Emitir evento com dados oficiais do servidor
          this.$emit('order-submitted', {
            ...result,
            local_pricing: {
              subtotal: this.subtotal,
              cash_price: this.cashPrice,
              installment_total: this.installmentTotal
            }
          });
          
          alert(`✅ Pedido criado com sucesso!\n\nID: ${result.order_id}\n\nEm produção, você seria redirecionado para o gateway de pagamento.`);
        } else if (result.offline) {
          // API offline: modo desenvolvimento
          console.warn('🟡 API offline - pedido simulado:', result.mockData);
          
          const mockPayload = {
            ...result.mockData,
            product: this.selectedProduct,
            pages: this.selectedPages,
            content_addons: this.selectedContentAddons,
            briefing: this.briefing,
            pricing: {
              subtotal: this.subtotal,
              cash_price: this.cashPrice,
              installment_total: this.installmentTotal,
              installment_value: this.installmentValue,
              source: 'local'
            },
            timestamp: new Date().toISOString()
          };
          
          this.$emit('order-submitted', mockPayload);
          
          alert('⚠️ Modo Desenvolvimento\n\nAPI offline - pedido simulado localmente.\n\nEm produção, o servidor validaria todos os valores.');
        } else {
          // Erro no servidor
          throw new Error(result.error || 'Erro desconhecido');
        }
      } catch (error) {
        console.error('🔴 Erro ao enviar pedido:', error);
        alert('❌ Erro ao enviar pedido. Tente novamente ou entre em contato.');
      } finally {
        this.isSubmitting = false;
      }
    },
    
    async submitCustomRequest() {
      this.isSubmittingCustom = true;
      
      try {
        const result = await PricingService.submitCustomRequest({
          ...this.customRequest,
          current_config: {
            product: this.selectedProduct,
            pages: this.selectedPages,
            content: this.selectedContentAddons
          }
        });
        
        if (result.ok) {
          this.$emit('custom-request', this.customRequest);
          alert(result.message);
          this.closeCustomForm();
        } else if (result.offline) {
          alert('⚠️ API offline\n\nEntre em contato via WhatsApp: (11) 99999-9999');
        }
      } catch (error) {
        console.error('🔴 Erro:', error);
        alert('Erro ao enviar solicitação.');
      } finally {
        this.isSubmittingCustom = false;
      }
    },
    
    // Utils
    formatPrice(value) {
      return `R$ ${value.toFixed(2).replace('.', ',')}`;
    },
    
    // Tooltips informativos
    getPageTooltip(key) {
      const tooltips = {
        about: 'Página institucional sobre sua empresa, história, missão e valores. Uma única página completa.',
        services: 'Página apresentando todos os seus serviços/produtos. Uma única página com cards ou seções.',
        portfolio: 'Galeria de trabalhos realizados, projetos ou produtos. Uma única página com grid de imagens.',
        faq: 'Página de perguntas frequentes com accordion/expansível. Uma única página.',
        contact: 'Página de contato com formulário, mapa e informações. Uma única página.'
      };
      return tooltips[key] || 'Página adicional para seu site';
    },
    
    getContentTooltip(key) {
      const tooltips = {
        video: 'Incorporação de vídeo institucional no site (YouTube, Vimeo, ou upload direto)',
        pdf: 'Catálogo, menu, folder ou documento PDF disponível para download no site'
      };
      return tooltips[key] || 'Conteúdo adicional';
    }
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/__colors.scss';

// Seção introdutória (renderizada no pai)
.configurator-intro {
  text-align: center;
  margin-bottom: 40px;

  .section-badge {
    display: inline-block;
    padding: 8px 16px;
    background: rgba($p-color, 0.1);
    color: $p-color;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 16px;
  }

  h2 {
    font-size: 2.5rem;
    font-weight: 700;
    color: $gray-darkness;
    margin-bottom: 16px;
    line-height: 1.2;

    @media (max-width: 768px) {
      font-size: 2rem;
    }
  }

  p {
    font-size: 1.1rem;
    color: $gray-medium;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.6;

    strong {
      color: $p-color;
      font-weight: 600;
    }
  }
}

// Container principal
.site-configurator {
  position: relative;
  display: grid;
  grid-template-columns: 1fr 350px;
  gap: 40px;
  max-width: 1400px;
  margin: 0 auto;
  padding: 40px 20px;

  @media (max-width: 1024px) {
    grid-template-columns: 1fr;
  }
}

// Debug Mode Badge
.debug-badge {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 10000;
  background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%);
  color: white;
  padding: 12px 20px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 8px 24px rgba(255, 152, 0, 0.4);
  animation: pulse 2s ease-in-out infinite;
  font-weight: 600;
  font-size: 0.9rem;

  i {
    font-size: 1.1rem;
    animation: spin 3s linear infinite;
  }

  .debug-toggle {
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;

    &:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: scale(1.05);
    }
  }

  @keyframes pulse {
    0%, 100% {
      box-shadow: 0 8px 24px rgba(255, 152, 0, 0.4);
    }
    50% {
      box-shadow: 0 8px 32px rgba(255, 152, 0, 0.6);
    }
  }

  @keyframes spin {
    from {
      transform: rotate(0deg);
    }
    to {
      transform: rotate(360deg);
    }
  }

  @media (max-width: 768px) {
    top: 10px;
    right: 10px;
    padding: 8px 12px;
    font-size: 0.8rem;

    span {
      display: none;
    }
  }
}

// Progress Bar
.progress-bar {
  grid-column: 1 / -1;
  display: flex;
  justify-content: center;
  gap: 40px;
  margin-bottom: 40px;

  .progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    opacity: 0.4;
    transition: opacity 0.3s;

    &.active,
    &.completed {
      opacity: 1;
    }

    .step-number {
      width: 48px;
      height: 48px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      background: $gray-light;
      color: $gray-medium;
      font-weight: 700;
      font-size: 1.2rem;
      transition: all 0.3s;
    }

    &.active .step-number {
      background: $p-color;
      color: $white;
      box-shadow: 0 4px 14px rgba($p-color, 0.4);
    }

    &.completed .step-number {
      background: $success;
      color: $white;

      &::after {
        content: '\f00c';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
      }
    }

    .step-label {
      font-size: 0.9rem;
      font-weight: 600;
      color: $gray-medium;
    }
  }
}

// Body do configurador
.configurator-body {
  min-height: 600px;
}

.step-content {
  animation: fadeIn 0.3s;
}

.step-title {
  font-size: 2rem;
  font-weight: 800;
  color: $gray-darkness;
  margin-bottom: 12px;
}

.step-description {
  font-size: 1.1rem;
  color: $gray-medium;
  margin-bottom: 40px;
}

// Etapa 1: Produtos
.product-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
  margin-bottom: 40px;
}

.product-card {
  padding: 32px;
  background: $white;
  border: 3px solid $gray-light;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.3s;

  &:hover {
    border-color: $p-color;
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba($p-color, 0.15);
  }

  &.selected {
    border-color: $p-color;
    background: linear-gradient(135deg, rgba($p-color, 0.05) 0%, rgba($p-dark, 0.02) 100%);
    box-shadow: 0 0 0 4px rgba($p-color, 0.1);
  }

  .product-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;

    .product-icon {
      width: 64px;
      height: 64px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: $gradient-primary;
      border-radius: 16px;

      i {
        font-size: 2rem;
        color: $white;
      }
    }

    .product-name {
      font-size: 1.5rem;
      font-weight: 700;
      color: $gray-darkness;
    }

    .product-price {
      display: flex;
      flex-direction: column;
      align-items: center;

      .price-value {
        font-size: 2rem;
        font-weight: 900;
        color: $p-color;
      }

      .price-label {
        font-size: 0.85rem;
        color: $gray-medium;
      }
    }
  }

  .product-description {
    text-align: center;
    color: $gray-medium;
    line-height: 1.6;
    margin-bottom: 24px;
  }

  .product-action {
    .btn-select {
      width: 100%;
      padding: 12px;
      background: $p-color;
      color: $white;
      border: none;
      border-radius: 12px;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: all 0.3s;

      &:hover {
        background: $p-dark;
        transform: translateY(-2px);
      }
    }
  }
}

// Etapa 2: Add-ons
.addon-section {
  margin-bottom: 40px;

  .section-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1.3rem;
    font-weight: 700;
    color: $gray-darkness;
    margin-bottom: 20px;

    i {
      color: $p-color;
    }
  }
}

.addon-grid {
  display: grid;
  gap: 16px;
}

.addon-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  background: $white;
  border: 2px solid $gray-light;
  border-radius: 12px;

  .addon-info {
    display: flex;
    flex-direction: column;
    gap: 4px;

    .addon-name {
      font-weight: 600;
      color: $gray-darkness;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .addon-price {
      font-size: 0.9rem;
      color: $p-color;
      font-weight: 700;
    }
  }

  // Tooltip styling
  .addon-tooltip {
    position: relative;
    display: inline-flex;
    align-items: center;
    cursor: help;

    i {
      color: $gray-medium;
      font-size: 0.9rem;
      transition: color 0.2s;
    }

    &:hover i {
      color: $p-color;
    }

    .tooltip-text {
      visibility: hidden;
      opacity: 0;
      position: absolute;
      bottom: 125%;
      left: 50%;
      transform: translateX(-50%);
      background: $gray-darkness;
      color: $white;
      padding: 12px 16px;
      border-radius: 8px;
      font-size: 0.85rem;
      font-weight: 400;
      line-height: 1.5;
      width: 280px;
      z-index: 1000;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      transition: opacity 0.3s, visibility 0.3s;
      text-align: left;
      pointer-events: none;

      &::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 6px solid transparent;
        border-top-color: $gray-darkness;
      }
    }

    &:hover .tooltip-text {
      visibility: visible;
      opacity: 1;
    }
  }

  .addon-counter {
    display: flex;
    align-items: center;
    gap: 16px;

    .counter-btn {
      width: 36px;
      height: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: $gray-lightness;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s;

      i {
        color: $p-color;
      }

      &:hover:not(:disabled) {
        background: $p-color;

        i {
          color: $white;
        }
      }

      &:disabled {
        opacity: 0.3;
        cursor: not-allowed;
      }
    }

    .counter-value {
      min-width: 32px;
      text-align: center;
      font-size: 1.2rem;
      font-weight: 700;
      color: $gray-darkness;
    }
  }
}

.addon-toggles {
  display: flex;
  flex-direction: column;
  gap: 12px;

  .toggle-item {
    display: block;
    padding: 20px;
    background: $white;
    border: 2px solid $gray-light;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;

    &:has(input:checked) {
      border-color: $p-color;
      background: rgba($p-color, 0.05);
    }

    input[type="checkbox"] {
      display: none;
    }

    .toggle-content {
      display: flex;
      justify-content: space-between;
      align-items: center;

      .toggle-info {
        display: flex;
        flex-direction: column;
        gap: 4px;

        .toggle-name {
          font-weight: 600;
          color: $gray-darkness;
          display: flex;
          align-items: center;
          gap: 8px;
        }

        .toggle-price {
          font-size: 0.9rem;
          color: $p-color;
          font-weight: 700;
        }
      }

      .toggle-switch {
        width: 52px;
        height: 28px;
        background: $gray-light;
        border-radius: 14px;
        position: relative;
        transition: background 0.3s;

        .switch {
          position: absolute;
          width: 24px;
          height: 24px;
          background: $white;
          border-radius: 50%;
          top: 2px;
          left: 2px;
          transition: left 0.3s;
          box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
      }
    }

    input:checked ~ .toggle-content .toggle-switch {
      background: $p-color;

      .switch {
        left: 26px;
      }
    }
  }
}

.plan-info {
  display: flex;
  gap: 16px;
  padding: 20px;
  background: rgba($accent-blue, 0.05);
  border-left: 4px solid $accent-blue;
  border-radius: 12px;
  margin-bottom: 32px;

  .info-icon {
    font-size: 1.5rem;
    color: $accent-blue;
  }

  .info-content {
    h4 {
      font-size: 1rem;
      font-weight: 700;
      color: $gray-darkness;
      margin-bottom: 12px;
    }

    ul {
      list-style: none;
      padding: 0;
      margin: 0;

      li {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 0;
        color: $gray-medium;
        font-size: 0.95rem;

        i {
          color: $success;
        }
      }
    }
  }
}

// Upsell Box
.upsell-box {
  padding: 40px;
  background: linear-gradient(135deg, rgba($p-color, 0.1) 0%, rgba($accent-blue, 0.05) 100%);
  border: 2px solid rgba($p-color, 0.3);
  border-radius: 16px;
  text-align: center;
  margin-bottom: 40px;
  position: relative;
  overflow: hidden;

  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: $gradient-primary;
  }

  .upsell-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;

    i {
      font-size: 2.5rem;
      color: $p-color;
      animation: bounce 2s infinite;
    }

    h3 {
      font-size: 1.75rem;
      font-weight: 800;
      color: $gray-darkness;
      line-height: 1.3;
    }
  }

  .upsell-description {
    font-size: 1.1rem;
    color: $gray-medium;
    line-height: 1.7;
    margin-bottom: 32px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
  }

  .upsell-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;

    button {
      padding: 16px 36px;
      border: none;
      border-radius: 12px;
      font-size: 1.05rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .btn-upsell-accept {
      background: $gradient-primary;
      color: $white;
      box-shadow: 0 4px 12px rgba($p-color, 0.3);

      &:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba($p-color, 0.4);
      }
    }

    .btn-upsell-decline {
      background: $white;
      color: $gray-medium;
      border: 2px solid $gray-lightness;

      &:hover {
        background: $gray-lightness;
        border-color: $gray-medium;
      }
    }
  }

  @keyframes bounce {
    0%, 100% {
      transform: translateY(0);
    }
    50% {
      transform: translateY(-10px);
    }
  }
}

.custom-question {
  padding: 32px;
  background: $gray-lightness;
  border-radius: 16px;
  text-align: center;
  margin-bottom: 40px;

  h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: $gray-darkness;
    margin-bottom: 8px;
  }

  p {
    color: $gray-medium;
    margin-bottom: 24px;
  }

  .question-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 20px;

    button {
      padding: 14px 28px;
      border: none;
      border-radius: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
    }

    .btn-standard {
      background: $p-color;
      color: $white;

      &:hover {
        background: $p-dark;
        transform: translateY(-2px);
      }
    }

    .btn-custom {
      background: $white;
      color: $p-color;
      border: 2px solid $p-color;

      &:hover {
        background: $p-color;
        color: $white;
      }
    }
  }

  .back-to-landing {
    padding-top: 16px;
    border-top: 1px solid rgba($gray-medium, 0.2);

    a {
      font-size: 0.6rem;
      color: $gray-medium;
      cursor: pointer;
      transition: color 0.2s;

      &:hover {
        color: $p-color;
        text-decoration: underline;
      }
    }
  }
}

// Navegação
.step-navigation {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  margin-top: 40px;

  button {
    padding: 16px 32px;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s;

    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  }

  .btn-back {
    background: $white;
    color: $gray-darkness;
    border: 2px solid $gray-light;

    &:hover:not(:disabled) {
      border-color: $gray-darkness;
    }
  }

  .btn-next {
    background: $gradient-primary;
    color: $white;
    box-shadow: 0 4px 14px rgba($p-color, 0.4);

    &:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba($p-color, 0.5);
    }
  }
}

// Etapa 3: Briefing Form
.briefing-form {
  .form-section {
    margin-bottom: 40px;
    padding-bottom: 40px;
    border-bottom: 2px solid $gray-light;

    &:last-of-type {
      border-bottom: none;
    }

    .form-section-title {
      font-size: 1.3rem;
      font-weight: 700;
      color: $gray-darkness;
      margin-bottom: 24px;
    }
  }

  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;

    @media (max-width: 768px) {
      grid-template-columns: 1fr;
    }
  }

  .form-group {
    margin-bottom: 24px;

    label {
      display: block;
      font-weight: 600;
      color: $gray-darkness;
      margin-bottom: 8px;
    }

    input,
    textarea,
    select {
      width: 100%;
      padding: 14px 16px;
      font-size: 1rem;
      color: $gray-darkness;
      background: $white;
      border: 2px solid $gray-light;
      border-radius: 12px;
      transition: all 0.3s;

      &:focus {
        outline: none;
        border-color: $p-color;
        box-shadow: 0 0 0 4px rgba($p-color, 0.1);
      }
    }

    small {
      display: block;
      margin-top: 6px;
      font-size: 0.85rem;
      color: $gray-medium;
    }

    .file-input {
      padding: 12px;
      font-size: 0.95rem;
    }
  }

  .style-options {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;

    @media (max-width: 768px) {
      grid-template-columns: 1fr;
    }

    .style-card {
      padding: 24px;
      background: $white;
      border: 3px solid $gray-light;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s;
      text-align: center;

      &:hover {
        border-color: $p-color;
      }

      &.selected {
        border-color: $p-color;
        background: rgba($p-color, 0.05);
      }

      input[type="radio"] {
        display: none;
      }

      .style-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;

        i {
          font-size: 2.5rem;
          color: $p-color;
        }

        span {
          font-weight: 600;
          color: $gray-darkness;
        }
      }
    }
  }
}

.order-summary {
  padding: 32px;
  background: $gray-lightness;
  border-radius: 16px;
  margin-bottom: 40px;

  h3 {
    font-size: 1.3rem;
    font-weight: 700;
    color: $gray-darkness;
    margin-bottom: 20px;
  }

  .summary-items {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 2px solid $gray-light;

    .summary-item {
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

  .summary-total {
    .total-row {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;

      &.subtotal {
        font-size: 1rem;
        color: $gray-medium;
      }

      &.installments {
        padding: 16px;
        background: $white;
        border-radius: 12px;
        margin: 8px 0;

        .highlight {
          font-size: 1.3rem;
          font-weight: 800;
          color: $p-color;
        }
      }

      &.cash {
        padding: 16px;
        background: rgba($success, 0.1);
        border-radius: 12px;

        .highlight-success {
          font-size: 1.3rem;
          font-weight: 800;
          color: $success;
        }
      }
    }
  }
}

.btn-submit {
  width: 100%;
  padding: 18px;
  background: $gradient-primary;
  color: $white;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: all 0.3s;

  &:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba($p-color, 0.5);
  }

  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
}

// Price Sidebar
.price-sidebar {
  position: sticky;
  top: 100px;
  height: fit-content;
  background: $white;
  border-radius: 20px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  overflow: hidden;

  @media (max-width: 1024px) {
    display: none;
  }

  .sidebar-header {
    padding: 24px;
    background: $gradient-primary;
    color: $white;

    h3 {
      font-size: 1.3rem;
      font-weight: 700;
    }
  }

  .sidebar-items {
    padding: 20px;
    max-height: 300px;
    overflow-y: auto;

    .sidebar-item {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid $gray-light;

      &:last-child {
        border-bottom: none;
      }

      .item-name {
        font-size: 0.95rem;
        color: $gray-medium;

        .item-qty {
          color: $p-color;
          font-weight: 600;
        }
      }

      .item-price {
        font-weight: 600;
        color: $gray-darkness;
      }
    }
  }

  .sidebar-total {
    padding: 20px;
    background: $gray-lightness;

    .total-line {
      display: flex;
      justify-content: space-between;
      padding: 8px 0;
      color: $gray-medium;

      &.installment {
        padding: 12px 0;
        border-top: 2px solid $gray-light;
        margin-top: 8px;

        .price-big {
          font-size: 1.3rem;
          font-weight: 800;
          color: $p-color;
        }
      }

      &.cash {
        padding: 12px 0;

        .price-big {
          font-size: 1.3rem;
          font-weight: 800;

          &.success {
            color: $success;
          }
        }
      }
    }
  }

  .sidebar-validation {
    padding: 12px 20px;
    background: rgba($gray-light, 0.3);
    border-top: 1px solid rgba($gray-light, 0.5);
    
    .validation-status {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.85rem;
      font-weight: 500;
      
      i {
        font-size: 1rem;
      }
      
      &.validating {
        color: $accent;
        
        i {
          animation: spin 1s linear infinite;
        }
      }
      
      &.validated {
        color: $success;
      }
      
      &.local {
        color: $gray-medium;
      }
    }
  }

  .sidebar-footer {
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: rgba($success, 0.1);
    color: $success;
    font-size: 0.9rem;
    font-weight: 600;

    i {
      font-size: 1.1rem;
    }
  }
}

// Modal
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 20px;
}

.modal-content {
  position: relative;
  max-width: 600px;
  width: 100%;
  background: $white;
  border-radius: 20px;
  padding: 40px;
  max-height: 90vh;
  overflow-y: auto;

  .modal-close {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: $gray-lightness;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s;

    &:hover {
      background: $gray-light;
      transform: rotate(90deg);
    }
  }

  .modal-title {
    font-size: 1.8rem;
    font-weight: 800;
    color: $gray-darkness;
    margin-bottom: 12px;
  }

  .modal-description {
    color: $gray-medium;
    margin-bottom: 32px;
    line-height: 1.6;
  }

  .custom-form {
    .form-group {
      margin-bottom: 20px;
    }

    .modal-actions {
      display: flex;
      gap: 12px;
      margin-top: 32px;

      button {
        flex: 1;
        padding: 14px;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
      }

      .btn-cancel {
        background: $white;
        color: $gray-darkness;
        border: 2px solid $gray-light;

        &:hover {
          border-color: $gray-darkness;
        }
      }

      .btn-submit-modal {
        background: $gradient-primary;
        color: $white;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;

        &:hover:not(:disabled) {
          transform: translateY(-2px);
          box-shadow: 0 8px 20px rgba($p-color, 0.4);
        }

        &:disabled {
          opacity: 0.6;
          cursor: not-allowed;
        }
      }
    }
  }
}

// Animations
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s, transform 0.3s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s;
}
.modal-enter-from, .modal-leave-to {
  opacity: 0;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
