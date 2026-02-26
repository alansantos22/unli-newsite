<template>
  <div class="configurador-page">
    <!-- FASE 0: Tela de Decisão (Chat vs Form) -->
    <template v-if="currentPhase === 'decision'">
      <EntryDecisionHero
        :pricing-config="pricingConfig"
        @select-chat="startChatFlow"
        @select-form="startQuickCheckout"
        @go-to-configurator="skipToConfigurator"
      />
    </template>

    <!-- FASE 1: Chat com Assistente SDR -->
    <template v-else-if="currentPhase === 'chat'">
      <SDRChatAssistant
        :api-base-url="apiBaseUrl"
        @proceed-to-wizard="handleChatComplete"
        @skip-to-form="goToQuickCheckout"
        @checkout-redirect="handleChatCheckoutRedirect"
        @close="goBackToDecision"
      />
    </template>

    <!-- FASE 2: Checkout Rápido (Direto para MP) -->
    <template v-else-if="currentPhase === 'checkout'">
      <QuickCheckout
        :product="selectedPlan"
        :package-key="selectedPackage"
        :pricing-config="pricingConfig"
        @go-back="goBackToDecision"
      />
    </template>

    <!-- FASE 3: Configurador Completo (para customização avançada) -->
    <template v-else-if="currentPhase === 'configurator'">
      <!-- Header Simples -->
      <header class="configurador-header">
        <div class="header-container">
          <div class="header-left">
            <router-link to="/site-vitrine" class="btn-back">
              <i class="fas fa-arrow-left"></i>
              <span>Voltar</span>
            </router-link>
          </div>
          
          <div class="header-center">
            <h1 class="page-title">Configure seu Site</h1>
            <p class="page-subtitle">Monte o site perfeito para o seu negócio</p>
          </div>
          
          <div class="header-right">
            <a href="https://wa.me/5511968354238?text=Olá! Estou no configurador e preciso de ajuda." 
               class="btn-help"
               target="_blank"
               rel="noopener noreferrer">
              <i class="fab fa-whatsapp"></i>
              <span>Ajuda</span>
            </a>
          </div>
        </div>
      </header>

      <!-- Configurador (componente isolado) -->
      <main class="configurador-main">
        <div class="configurador-wrapper">
          <SiteConfigurator 
            :initial-product="selectedPlan"
            :prefilled-data="prefilledData"
            @order-submitted="handleOrderSubmitted"
            @custom-request="handleCustomRequest"
            @close="handleClose"
          />
        </div>
      </main>

      <!-- Footer Minimalista -->
      <footer class="configurador-footer">
        <div class="footer-container">
          <p class="footer-trust">
            <i class="fas fa-shield-alt"></i>
            Pagamento 100% seguro • Suporte dedicado • Garantia de qualidade
          </p>
          <p class="footer-help">
            Dúvidas? <a href="https://wa.me/5511968354238" target="_blank">Fale conosco no WhatsApp</a>
          </p>
        </div>
      </footer>
    </template>

    <!-- Loading Screen Profissional -->
    <FullScreenLoading 
      :visible="isProcessingPayment"
      title="Processando pagamento..."
      subtitle="Redirecionando para o gateway de pagamento seguro."
    />
  </div>
</template>

<script>
import { SiteConfigurator, FullScreenLoading, EntryDecisionHero, SDRChatAssistant, QuickCheckout } from '@/shared/Components';

export default {
  name: 'ConfiguradorPage',
  components: {
    SiteConfigurator,
    FullScreenLoading,
    EntryDecisionHero,
    SDRChatAssistant,
    QuickCheckout
  },
  data() {
    return {
      selectedPlan: null,
      selectedPackage: 'essential', // Pacote padrão
      isProcessingPayment: false,
      // Controle de fases: 'decision' | 'chat' | 'checkout' | 'configurator'
      currentPhase: 'decision',
      // Dados extraídos do chat para pré-preencher o configurador
      prefilledData: null,
      // URL base da API
      apiBaseUrl: '/api/ai',
      // Configuração de preços (carregada da API)
      pricingConfig: null
    };
  },
  async created() {
    console.log('🔍 [ConfiguradorPage] Created - Query params:', this.$route.query);
    console.log('🔍 [ConfiguradorPage] Plan from query:', this.$route.query.plan);
    console.log('🔍 [ConfiguradorPage] Package from query:', this.$route.query.package);
    
    this.selectedPlan = this.$route.query.plan || null;
    this.selectedPackage = this.$route.query.package || 'essential';
    
    console.log('🔍 [ConfiguradorPage] selectedPlan set to:', this.selectedPlan);
    console.log('🔍 [ConfiguradorPage] selectedPackage set to:', this.selectedPackage);
    
    // Se não houver plano selecionado, redirecionar para página de vendas
    if (!this.selectedPlan) {
      console.warn('⚠️ [ConfiguradorPage] Nenhum plano selecionado, redirecionando...');
      this.$router.push('/site-vitrine#planos');
    } else {
      console.log('✅ [ConfiguradorPage] Plano válido, mostrando tela de decisão...');
      // Carregar preços e verificar progresso salvo
      await this.loadPricingConfig();
      this.checkSavedProgress();
    }
  },
  mounted() {
    console.log('🔍 [ConfiguradorPage] Mounted - selectedPlan final:', this.selectedPlan);
  },
  methods: {
    // ==================
    // CARREGAR CONFIGS
    // ==================
    
    async loadPricingConfig() {
      try {
        // Usar endpoint PHP em vez de acessar pricing.json diretamente (segurança)
        const response = await fetch('/api/config.php');
        if (response.ok) {
          const data = await response.json();
          if (data.ok) {
            this.pricingConfig = {
              version: data.version,
              currency: data.currency,
              products: data.products,
              page_addons: data.page_addons,
              content_addons: data.content_addons,
              pricing_rules: data.pricing_rules,
              limits: data.limits
            };
            console.log('💰 [ConfiguradorPage] Preços carregados:', this.pricingConfig);
          } else {
            console.warn('⚠️ [ConfiguradorPage] Erro na resposta:', data.error);
            this.loadFallbackPricing();
          }
        } else {
          console.warn('⚠️ [ConfiguradorPage] Erro HTTP ao carregar preços:', response.status);
          this.loadFallbackPricing();
        }
      } catch (error) {
        console.warn('⚠️ [ConfiguradorPage] Não foi possível carregar preços:', error);
        this.loadFallbackPricing();
      }
    },
    
    loadFallbackPricing() {
      // Preços de fallback caso o pricing.json não esteja disponível
      // Valores SEM desconto (desconto é aplicado dinamicamente)
      console.log('📋 [ConfiguradorPage] Usando preços de fallback');
      this.pricingConfig = {
        version: 'fallback',
        currency: 'BRL',
        products: {
          landing: { name: 'Landing Page', base_price: 599, description: 'Página única focada em conversão' },
          site_complete: { name: 'Site Completo', base_price: 619, description: 'Site institucional com múltiplas páginas' }
        },
        page_addons: {
          about: { name: 'Sobre Nós', price: 59 },
          services: { name: 'Serviços', price: 119 },
          portfolio: { name: 'Portfólio', price: 159 },
          faq: { name: 'FAQ', price: 89 },
          contact: { name: 'Contato', price: 129 },
          blog: { name: 'Blog de Notícias', price: 349, isPremium: true },
          showcase: { name: 'Vitrine de Produtos', price: 449, isPremium: true }
        },
        predefined_packages: {
          essential: { 
            name: 'Essencial', 
            icon: '🏢', 
            pages: ['about', 'services', 'contact'],
            ideal_for: 'Advogados, Clínicas, Consultores'
          },
          authority: { 
            name: 'Autoridade', 
            icon: '🚀', 
            pages: ['about', 'services', 'contact', 'portfolio', 'faq'],
            ideal_for: 'Arquitetos, Agências, Engenharia'
          },
          enterprise: { 
            name: 'Ecossistema Digital', 
            icon: '💎', 
            pages: ['about', 'services', 'contact', 'portfolio', 'faq', 'blog', 'showcase'],
            ideal_for: 'Lojas, Importadoras, Indústrias'
          }
        },
        pricing_rules: {
          cash_discount_percent: 15,
          installments_12_markup_percent: 15,
          installments: 12
        }
      };
    },
    
    // ==================
    // CONTROLE DE FASES
    // ==================
    
    checkSavedProgress() {
      // Verificar se usuário já passou pela decisão antes
      const savedPhase = sessionStorage.getItem('unli_configurator_phase');
      if (savedPhase === 'configurator') {
        this.currentPhase = 'configurator';
      } else if (savedPhase === 'checkout') {
        this.currentPhase = 'checkout';
      }
    },
    
    startChatFlow() {
      console.log('💬 [ConfiguradorPage] Iniciando chat com assistente');
      this.currentPhase = 'chat';
    },
    
    startQuickCheckout(payload) {
      console.log('⚡ [ConfiguradorPage] Iniciando checkout rápido');
      
      // Se payload contém pacote selecionado, usar
      if (payload && payload.package) {
        this.selectedPackage = payload.package;
      }
      
      console.log('📦 [ConfiguradorPage] Produto:', this.selectedPlan, 'Pacote:', this.selectedPackage);
      this.currentPhase = 'checkout';
      sessionStorage.setItem('unli_configurator_phase', 'checkout');
    },
    
    goToQuickCheckout() {
      // Usuário quer pular do chat direto para o checkout
      console.log('⏭️ [ConfiguradorPage] Pulando do chat para checkout');
      this.currentPhase = 'checkout';
      sessionStorage.setItem('unli_configurator_phase', 'checkout');
    },
    
    skipToConfigurator() {
      console.log('⏭️ [ConfiguradorPage] Pulando para configurador completo');
      this.currentPhase = 'configurator';
      sessionStorage.setItem('unli_configurator_phase', 'configurator');
    },
    
    goBackToDecision() {
      console.log('🔙 [ConfiguradorPage] Voltando para tela de decisão');
      this.currentPhase = 'decision';
      sessionStorage.removeItem('unli_configurator_phase');
    },
    
    handleChatComplete(payload) {
      console.log('✅ [ConfiguradorPage] Chat concluído, dados extraídos:', payload);
      
      // Salvar dados extraídos para pré-preencher o configurador
      if (payload && payload.extractedData) {
        this.prefilledData = this.mapExtractedDataToConfigurator(payload.extractedData);
        console.log('📝 [ConfiguradorPage] Dados mapeados para configurador:', this.prefilledData);
      }
      
      // Avançar para o configurador
      this.currentPhase = 'configurator';
      sessionStorage.setItem('unli_configurator_phase', 'configurator');
    },
    
    handleChatCheckoutRedirect(payload) {
      console.log('🛒 [ConfiguradorPage] Checkout via chat SDR:', payload);
      
      // Ativar loading de processamento
      this.isProcessingPayment = true;
      
      if (payload && (payload.payment_url || payload.init_point)) {
        const checkoutUrl = payload.payment_url || payload.init_point;
        console.log('🚀 [ConfiguradorPage] Redirecionando para Pagar.me via chat:', checkoutUrl);
        window.location.href = checkoutUrl;
      } else {
        console.error('❌ [ConfiguradorPage] Payload sem payment_url:', payload);
        this.isProcessingPayment = false;
        alert('❌ Erro ao redirecionar para o pagamento. Por favor, tente novamente.');
      }
    },
    
    mapExtractedDataToConfigurator(extractedData) {
      // Mapear dados do chat SDR para o formato do SiteConfigurator
      const mapped = {
        businessName: extractedData.business?.name || null,
        businessNiche: extractedData.business?.niche || null,
        businessDescription: extractedData.business?.description || null,
        suggestedPages: extractedData.site?.pages || [],
        suggestedPackage: extractedData.site?.suggestedPackage || null,
        stylePreferences: {
          tone: extractedData.site?.style?.tone || null,
          colors: extractedData.site?.style?.colors || []
        },
        contactInfo: {
          name: extractedData.contact?.name || null,
          email: extractedData.contact?.email || null,
          phone: extractedData.contact?.phone || null
        }
      };
      
      return mapped;
    },
    
    // ==================
    // HANDLERS EXISTENTES
    // ==================
    
    handleOrderSubmitted(orderPayload) {
      console.log('✅ [ConfiguradorPage] Pedido recebido do SiteConfigurator:', orderPayload);
      
      // Mostrar loading durante o processamento
      this.isProcessingPayment = true;
      
      // Verificar se há payment_url ou init_point para redirecionamento
      if (orderPayload && (orderPayload.payment_url || orderPayload.init_point)) {
        const checkoutUrl = orderPayload.payment_url || orderPayload.init_point;
        console.log('🚀 [ConfiguradorPage] Redirecionando para Pagar.me:', checkoutUrl);
        window.location.href = checkoutUrl;
      } else if (orderPayload && orderPayload.preference_error) {
        // Erro específico na criação da preferência
        console.error('❌ [ConfiguradorPage] Erro na preferência:', orderPayload.error_message);
        this.isProcessingPayment = false;
        alert(`❌ Erro ao processar pagamento:\n\n${orderPayload.error_message}\n\nPedido criado: ${orderPayload.order_id}\nPor favor, tente novamente ou entre em contato.`);
      } else if (orderPayload && orderPayload.order_id) {
        // Pedido criado mas sem preferência (caso não esperado)
        console.warn('⚠️ [ConfiguradorPage] Pedido criado mas sem payment_url. ID:', orderPayload.order_id);
        this.isProcessingPayment = false;
        alert(`⚠️ Pedido criado com sucesso mas falha no redirecionamento.\n\nID do Pedido: ${orderPayload.order_id}\n\nPor favor, entre em contato para continuar o pagamento.`);
      } else {
        // Payload inválido
        console.error('❌ [ConfiguradorPage] Payload inválido recebido:', orderPayload);
        this.isProcessingPayment = false;
        alert('❌ Erro inesperado ao processar pedido. Por favor, tente novamente.');
      }
    },
    
    handleCustomRequest(customData) {
      console.log('Orçamento personalizado:', customData);
      
      alert('Solicitação de orçamento enviada! Em produção, isso enviaria para o CRM.');
      
      // Redirecionar para página de contato
      this.$router.push('/site-vitrine#contact');
    },
    
    handleClose() {
      // Voltar para a página de vendas
      this.$router.push('/site-vitrine');
    }
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/__colors.scss';

// ==========================================
// PÁGINA DEDICADA DO CONFIGURADOR
// ==========================================

.configurador-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
  
  // Quando está na fase de decisão ou chat, ocupar toda a tela
  &:has(.entry-decision-hero),
  &:has(.sdr-chat-assistant) {
    background: transparent;
  }
}

// ==========================================
// HEADER
// ==========================================
.configurador-header {
  position: sticky;
  top: 0;
  z-index: 9999;
  background: $white;
  border-bottom: 1px solid $gray-light;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);

  .header-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px 40px;
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    gap: 24px;

    @media (max-width: 968px) {
      grid-template-columns: auto 1fr auto;
      padding: 16px 20px;
    }
  }

  .header-left {
    display: flex;
    justify-content: flex-start;
  }

  .header-center {
    text-align: center;

    @media (max-width: 968px) {
      text-align: left;
    }
  }

  .header-right {
    display: flex;
    justify-content: flex-end;
  }

  .btn-back {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: transparent;
    border: 2px solid $gray-light;
    border-radius: 12px;
    color: $gray-darkness;
    font-weight: 600;
    font-size: 0.95rem;
    text-decoration: none;
    transition: all 0.3s;

    i {
      font-size: 1rem;
    }

    &:hover {
      border-color: $p-color;
      color: $p-color;
      transform: translateX(-4px);
    }

    @media (max-width: 768px) {
      span {
        display: none;
      }
    }
  }

  .page-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: $gray-darkness;
    margin: 0;
    line-height: 1.2;

    @media (max-width: 768px) {
      font-size: 1.2rem;
    }
  }

  .page-subtitle {
    font-size: 0.9rem;
    color: $gray-medium;
    margin: 4px 0 0;

    @media (max-width: 768px) {
      display: none;
    }
  }

  .btn-help {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: linear-gradient(135deg, #25d366, #128c7e);
    border: none;
    border-radius: 12px;
    color: $white;
    font-weight: 600;
    font-size: 0.95rem;
    text-decoration: none;
    transition: all 0.3s;

    i {
      font-size: 1.1rem;
    }

    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
    }

    @media (max-width: 768px) {
      span {
        display: none;
      }
      padding: 10px 16px;
    }
  }
}

// ==========================================
// MAIN CONTENT
// ==========================================
.configurador-main {
  flex: 1;
  padding: 40px 20px;

  @media (max-width: 768px) {
    padding: 20px 10px;
  }
}

.configurador-wrapper {
  max-width: 1400px;
  margin: 0 auto;
}

// ==========================================
// FOOTER
// ==========================================
.configurador-footer {
  background: $white;
  border-top: 1px solid $gray-light;
  padding: 24px 20px;

  .footer-container {
    max-width: 1400px;
    margin: 0 auto;
    text-align: center;
  }

  .footer-trust {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    font-size: 0.95rem;
    color: $gray-medium;
    margin-bottom: 12px;

    i {
      color: $p-color;
    }

    @media (max-width: 768px) {
      flex-direction: column;
      gap: 8px;
      font-size: 0.85rem;
    }
  }

  .footer-help {
    font-size: 0.9rem;
    color: $gray-medium;

    a {
      color: $p-color;
      font-weight: 600;
      text-decoration: none;
      transition: color 0.2s;

      &:hover {
        color: $p-dark;
        text-decoration: underline;
      }
    }
  }
}
</style>
