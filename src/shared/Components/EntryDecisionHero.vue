<template>
  <div class="entry-decision-hero">
    <!-- Fundo com Partículas Animadas -->
    <div class="hero-background">
      <div class="gradient-orb orb-1"></div>
      <div class="gradient-orb orb-2"></div>
      <div class="gradient-orb orb-3"></div>
      <div class="particles">
        <div 
          v-for="n in 20" 
          :key="n" 
          class="particle"
          :style="getParticleStyle(n)"
        ></div>
      </div>
    </div>

    <!-- Conteúdo Principal - Decisão -->
    <div class="hero-content" v-if="!showPackageSelector">
      <!-- Logo ou Ícone -->
      <div class="hero-logo">
        <div class="logo-ring">
          <div class="logo-inner">
            <i class="fas fa-rocket"></i>
          </div>
        </div>
      </div>

      <!-- Título -->
      <h1 class="hero-title">
        Vamos criar o 
        <span class="highlight">site perfeito</span> 
        para você
      </h1>

      <!-- Subtítulo -->
      <p class="hero-subtitle">
        Como você prefere começar?
      </p>

      <!-- Quick Replies -->
      <div class="quick-replies">
        <!-- Opção 1: Consultoria com IA -->
        <button 
          class="quick-reply-btn primary"
          @click="startChat"
        >
          <div class="qr-icon-wrap">
            <i class="fas fa-comments"></i>
          </div>
          <div class="qr-text">
            <span class="qr-label">
              Quero ajuda para escolher
              <span class="qr-badge"><i class="fas fa-sparkles"></i> Recomendado</span>
            </span>
            <span class="qr-sub">Consultoria gratuita • ~3 min</span>
          </div>
          <i class="fas fa-chevron-right qr-arrow"></i>
        </button>

        <!-- Opção 2: Self-Service -->
        <button 
          class="quick-reply-btn secondary"
          @click="showPackageSelector = true"
        >
          <div class="qr-icon-wrap secondary">
            <i class="fas fa-bolt"></i>
          </div>
          <div class="qr-text">
            <span class="qr-label">Já sei o que quero</span>
            <span class="qr-sub">Escolha um pacote e pague • ~2 min</span>
          </div>
          <i class="fas fa-chevron-right qr-arrow"></i>
        </button>
      </div>

      <!-- Promoção Badge -->
      <div class="promo-banner">
        <div class="promo-icon">🎉</div>
        <div class="promo-text">
          <strong>Promoção "Iniciando 2026 Online"</strong>
          <span>30% OFF em todos os planos + 15% OFF se pagar no pix</span>
        </div>
      </div>
    </div>

    <!-- Seletor de Pacotes (Exibido quando escolhe "Já sei o que quero") -->
    <div class="hero-content package-selector-content" v-else>
      <button class="btn-back-packages" @click="showPackageSelector = false">
        <i class="fas fa-arrow-left"></i>
        <span>Voltar</span>
      </button>

      <h2 class="packages-title">
        Escolha o pacote ideal
        <span class="highlight">para você</span>
      </h2>
      
      <p class="packages-subtitle">
        Todos incluem Site Completo PRO + Hospedagem + Domínio + SSL
      </p>

      <!-- Cards de Pacotes -->
      <div class="packages-grid">
        <!-- Essencial -->
        <div 
          class="package-card"
          :class="{ 'selected': selectedPackage === 'essential' }"
          @click="selectedPackage = 'essential'"
        >
          <div class="package-icon">🏢</div>
          <h3 class="package-name">Essencial</h3>
          <p class="package-tagline">Para prestadores de serviço</p>
          <div class="package-pages">
            <span class="page-tag">Sobre</span>
            <span class="page-tag">Serviços</span>
            <span class="page-tag">Contato</span>
          </div>
          <div class="package-price">
            <span class="price-from">De <s>{{ essentialPricing.formattedOriginal }}/mês</s></span>
            <span class="price-now">{{ essentialPricing.formattedMonthly }}/mês</span>
            <span class="price-pix">ou {{ essentialPricing.formattedCash }} à vista</span>
          </div>
        </div>

        <!-- Autoridade -->
        <div 
          class="package-card featured"
          :class="{ 'selected': selectedPackage === 'authority' }"
          @click="selectedPackage = 'authority'"
        >
          <div class="package-badge">Mais vendido</div>
          <div class="package-icon">🚀</div>
          <h3 class="package-name">Autoridade</h3>
          <p class="package-tagline">Mostre seu trabalho e tire dúvidas</p>
          <div class="package-pages">
            <span class="page-tag">Sobre</span>
            <span class="page-tag">Serviços</span>
            <span class="page-tag">Portfólio</span>
            <span class="page-tag">FAQ</span>
            <span class="page-tag">Contato</span>
          </div>
          <div class="package-price">
            <span class="price-from">De <s>{{ authorityPricing.formattedOriginal }}/mês</s></span>
            <span class="price-now">{{ authorityPricing.formattedMonthly }}/mês</span>
            <span class="price-pix">ou {{ authorityPricing.formattedCash }} à vista</span>
          </div>
        </div>

        <!-- Ecossistema Digital -->
        <div 
          class="package-card"
          :class="{ 'selected': selectedPackage === 'enterprise' }"
          @click="selectedPackage = 'enterprise'"
        >
          <div class="package-icon">💎</div>
          <h3 class="package-name">Ecossistema</h3>
          <p class="package-tagline">Blog + Vitrine de produtos</p>
          <div class="package-pages">
            <span class="page-tag">Sobre</span>
            <span class="page-tag">Serviços</span>
            <span class="page-tag">Portfólio</span>
            <span class="page-tag">FAQ</span>
            <span class="page-tag">Blog</span>
            <span class="page-tag">Vitrine</span>
            <span class="page-tag">Contato</span>
          </div>
          <div class="package-price">
            <span class="price-from">De <s>{{ enterprisePricing.formattedOriginal }}/mês</s></span>
            <span class="price-now">{{ enterprisePricing.formattedMonthly }}/mês</span>
            <span class="price-pix">ou {{ enterprisePricing.formattedCash }} à vista</span>
          </div>
        </div>
      </div>

      <!-- Toggle: Atendimento com Especialista -->
      <div class="specialist-addon-toggle" :class="{ 'specialist-selected': withSpecialist }">
        <label class="specialist-toggle-label">
          <div class="specialist-toggle-info">
            <div class="specialist-addon-header">
              <i class="fas fa-user-tie"></i>
              <span class="specialist-addon-title">Quero ser atendido por um especialista</span>
              <span class="specialist-addon-price">+R$ {{ Math.ceil(169 / 12) }}/mês</span>
            </div>
            <p class="specialist-addon-desc">
              Após a compra, nosso time entra em contato pelo WhatsApp para coletar as informações do seu site com você. Ao invés de preencher um formulário sozinho.
            </p>
          </div>
          <div class="toggle-switch">
            <input type="checkbox" v-model="withSpecialist" />
            <span class="toggle-slider"></span>
          </div>
        </label>
      </div>

      <!-- Botão de Checkout -->
      <button 
        class="btn-checkout-now"
        @click="proceedToCheckout"
        :disabled="!selectedPackage"
      >
        <i class="fas fa-lock"></i>
        <span>Pagar agora com {{ selectedPackage ? getPackageName(selectedPackage) : '...' }}</span>
        <i class="fas fa-arrow-right"></i>
      </button>

      <!-- Link para Customização -->
      <p class="customize-link">
        Quer personalizar? 
        <a href="#" @click.prevent="goToCustomConfigurator">Acesse o configurador completo</a>
      </p>
    </div>

    <!-- Footer discreto -->
    <div class="hero-footer">
      <p>
        <i class="fas fa-lock"></i>
        Seus dados estão seguros. Não compartilhamos com terceiros.
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'EntryDecisionHero',
  
  props: {
    // Callback quando usuário escolhe a opção de chat
    onSelectChat: {
      type: Function,
      default: null
    },
    // Callback quando usuário escolhe ir direto para o form
    onSelectForm: {
      type: Function,
      default: null
    },
    // Configuração de preços carregada do servidor
    pricingConfig: {
      type: Object,
      default: () => ({})
    }
  },

  data() {
    return {
      showPackageSelector: false,
      selectedPackage: 'authority', // Pré-selecionar o mais vendido
      withSpecialist: false, // Adicionar atendimento com especialista
      // Fallback de preços caso pricingConfig não esteja disponível
      fallbackPricing: {
        basePrice: 805,
        pages: {
          about: 77,
          services: 155,
          portfolio: 207,
          faq: 116,
          contact: 168,
          blog: 454,
          showcase: 584
        },
        packages: {
          essential: ['about', 'services', 'contact'],
          authority: ['about', 'services', 'contact', 'portfolio', 'faq'],
          enterprise: ['about', 'services', 'contact', 'portfolio', 'faq', 'blog', 'showcase']
        }
      }
    }
  },

  computed: {
    // Calcula preço de um pacote específico
    essentialPricing() {
      return this.calculatePackagePricing('essential');
    },
    authorityPricing() {
      return this.calculatePackagePricing('authority');
    },
    enterprisePricing() {
      return this.calculatePackagePricing('enterprise');
    }
  },

  methods: {
    startChat() {
      this.$emit('select-chat');
      if (this.onSelectChat) this.onSelectChat();
    },
    
    getPackageName(key) {
      const names = {
        essential: 'Essencial',
        authority: 'Autoridade',
        enterprise: 'Ecossistema'
      };
      return names[key] || key;
    },
    
    // Calcula os preços de um pacote específico
    calculatePackagePricing(packageKey) {
      const config = this.pricingConfig;
      
      // Base price do produto "site_complete"
      const basePrice = config?.products?.site_complete?.base_price || this.fallbackPricing.basePrice;
      
      // Páginas do pacote
      const packagePages = config?.predefined_packages?.[packageKey]?.pages 
        || this.fallbackPricing.packages[packageKey] 
        || [];
      
      // Calcular total das páginas
      let pagesTotal = 0;
      packagePages.forEach(page => {
        const pagePrice = config?.page_addons?.[page]?.price || this.fallbackPricing.pages[page] || 0;
        pagesTotal += pagePrice;
      });
      
      // Subtotal anual (preços do JSON já são os finais)
      const subtotal = basePrice + pagesTotal;
      
      // Preço à vista (PIX) = subtotal sem taxa
      const cashPrice = subtotal;
      
      // Preço parcelado = subtotal + 15% (taxa do cartão)
      const installmentTotal = subtotal * 1.15;
      
      // Valor mensal (12x)
      const monthlyPrice = installmentTotal / 12;
      
      // "De" original (inflacionado 30% para ancoragem)
      const originalMonthly = monthlyPrice * 1.3;
      
      return {
        subtotal,
        cashPrice,
        installmentTotal,
        monthlyPrice,
        originalMonthly,
        // Formatados para exibição
        formattedOriginal: this.formatCurrency(originalMonthly),
        formattedMonthly: this.formatCurrency(monthlyPrice),
        formattedCash: this.formatCurrency(cashPrice)
      };
    },
    
    // Formata valor como moeda BRL
    formatCurrency(value) {
      return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      }).format(value);
    },
    
    proceedToCheckout() {
      if (!this.selectedPackage) return;
      
      // Atualizar URL com o pacote selecionado
      const currentQuery = { ...this.$route?.query };
      currentQuery.package = this.selectedPackage;
      
      // Emitir evento com pacote selecionado e opção de especialista
      this.$emit('select-form', { package: this.selectedPackage, specialist: this.withSpecialist });
      if (this.onSelectForm) this.onSelectForm({ package: this.selectedPackage, specialist: this.withSpecialist });
    },
    
    goToCustomConfigurator() {
      // Emitir evento especial para ir ao configurador completo
      this.$emit('go-to-configurator');
    },
    
    getParticleStyle(index) {
      const random = (min, max) => Math.random() * (max - min) + min;
      // Usar index para seed consistente (evita warning de unused var)
      const seed = index * 0.1;
      return {
        left: `${random(0, 100)}%`,
        top: `${random(0, 100)}%`,
        width: `${random(2, 6)}px`,
        height: `${random(2, 6)}px`,
        animationDelay: `${random(0, 5) + seed}s`,
        animationDuration: `${random(10, 20)}s`
      };
    }
  }
}
</script>

<style lang="scss" scoped>
// ==================
// VARIABLES
// ==================
$primary: #8B5CF6;
$primary-light: #A78BFA;
$secondary: #10B981;
$accent: #F59E0B;
$dark: #1A1A2E;
$darker: #0F0F1A;
$glass-bg: rgba(255, 255, 255, 0.05);
$glass-border: rgba(255, 255, 255, 0.1);
$text-primary: #FFFFFF;
$text-secondary: rgba(255, 255, 255, 0.7);
$text-muted: rgba(255, 255, 255, 0.5);

// ==================
// BASE
// ==================
.entry-decision-hero {
  position: relative;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 40px 20px;
  overflow: hidden;
  background: linear-gradient(135deg, $darker 0%, $dark 100%);
  font-family: 'Inter', -apple-system, sans-serif;
}

// ==================
// BACKGROUND EFFECTS
// ==================
.hero-background {
  position: absolute;
  inset: 0;
  overflow: hidden;
  pointer-events: none;
}

.gradient-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.6;
  animation: float-orb 15s ease-in-out infinite;
  
  &.orb-1 {
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba($primary, 0.4), transparent);
    top: -100px;
    left: -100px;
    animation-delay: 0s;
  }
  
  &.orb-2 {
    width: 350px;
    height: 350px;
    background: radial-gradient(circle, rgba($secondary, 0.3), transparent);
    bottom: -50px;
    right: -50px;
    animation-delay: -5s;
  }
  
  &.orb-3 {
    width: 250px;
    height: 250px;
    background: radial-gradient(circle, rgba($accent, 0.2), transparent);
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    animation-delay: -10s;
  }
}

.particles {
  position: absolute;
  inset: 0;
  
  .particle {
    position: absolute;
    background: rgba($primary-light, 0.3);
    border-radius: 50%;
    animation: float-particle linear infinite;
  }
}

@keyframes float-orb {
  0%, 100% {
    transform: translate(0, 0) scale(1);
  }
  25% {
    transform: translate(30px, -30px) scale(1.1);
  }
  50% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  75% {
    transform: translate(20px, 30px) scale(1.05);
  }
}

@keyframes float-particle {
  0% {
    transform: translateY(0) rotate(0deg);
    opacity: 0;
  }
  10% {
    opacity: 1;
  }
  90% {
    opacity: 1;
  }
  100% {
    transform: translateY(-100vh) rotate(720deg);
    opacity: 0;
  }
}

// ==================
// CONTENT
// ==================
.hero-content {
  position: relative;
  z-index: 1;
  max-width: 900px;
  width: 100%;
  text-align: center;
}

.hero-logo {
  margin-bottom: 32px;
  
  .logo-ring {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, $primary, $secondary);
    position: relative;
    
    &::before {
      content: '';
      position: absolute;
      inset: -6px;
      border-radius: 50%;
      border: 2px solid rgba($primary, 0.3);
      animation: pulse-ring 2s infinite;
    }
  }
  
  .logo-inner {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    background: $darker;
    display: flex;
    align-items: center;
    justify-content: center;
    
    i {
      font-size: 28px;
      color: $primary-light;
    }
  }
}

.hero-title {
  font-size: clamp(28px, 5vw, 42px);
  font-weight: 700;
  color: $text-primary;
  margin: 0 0 16px;
  line-height: 1.3;
  
  .highlight {
    background: linear-gradient(135deg, $primary, $secondary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
}

.hero-subtitle {
  font-size: 18px;
  color: $text-secondary;
  margin: 0 0 40px;
}

// ==================
// QUICK REPLIES
// ==================
.quick-replies {
  display: flex;
  flex-direction: column;
  gap: 14px;
  width: 100%;
  max-width: 560px;
  margin: 0 auto 32px;
}

.quick-reply-btn {
  display: flex;
  align-items: center;
  gap: 16px;
  width: 100%;
  padding: 20px 22px;
  background: $glass-bg;
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 2px solid $glass-border;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  text-align: left;
  position: relative;
  overflow: hidden;

  &::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 0% 50%, rgba($primary, 0.12) 0%, transparent 60%);
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  &:hover, &:focus-visible {
    border-color: rgba($primary, 0.5);
    background: rgba($primary, 0.08);
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba($primary, 0.2);
    outline: none;

    &::before { opacity: 1; }

    .qr-arrow {
      transform: translateX(4px);
      color: $primary-light;
    }
  }

  &:active {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba($primary, 0.15);
  }

  &.primary {
    border-color: rgba($primary, 0.35);
    animation: pulse-border 2.5s ease-in-out infinite;
  }

  &.secondary {
    .qr-icon-wrap {
      background: rgba($secondary, 0.2);
      i { color: $secondary; }
    }

    &:hover {
      border-color: rgba($secondary, 0.5);
      background: rgba($secondary, 0.08);
      box-shadow: 0 8px 24px rgba($secondary, 0.15);
    }
  }
}

.qr-icon-wrap {
  flex-shrink: 0;
  width: 48px;
  height: 48px;
  border-radius: 14px;
  background: rgba($primary, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;

  i {
    font-size: 20px;
    color: $primary-light;
  }
}

.qr-text {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.qr-label {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  font-size: 16px;
  font-weight: 600;
  color: $text-primary;
}

.qr-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 10px;
  background: linear-gradient(135deg, $accent, darken($accent, 10%));
  border-radius: 20px;
  font-size: 10px;
  font-weight: 600;
  color: $darker;

  i { font-size: 9px; }
}

.qr-sub {
  font-size: 12px;
  color: $text-muted;
}

.qr-arrow {
  flex-shrink: 0;
  font-size: 13px;
  color: rgba(255,255,255,0.3);
  transition: all 0.25s ease;
}

@keyframes pulse-border {
  0%, 100% { box-shadow: 0 0 0 0 rgba($primary, 0); }
  50% { box-shadow: 0 0 0 4px rgba($primary, 0.15); }
}

// ==================
// PROMO BANNER
// ==================
.promo-banner {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  padding: 16px 24px;
  background: rgba($accent, 0.1);
  border: 1px solid rgba($accent, 0.3);
  border-radius: 16px;
  margin-bottom: 40px;
  
  .promo-icon {
    font-size: 28px;
  }
  
  .promo-text {
    text-align: left;
    
    strong {
      display: block;
      font-size: 14px;
      font-weight: 600;
      color: $accent;
      margin-bottom: 2px;
    }
    
    span {
      font-size: 12px;
      color: $text-secondary;
    }
  }
}

// ==================
// FOOTER
// ==================
.hero-footer {
  position: absolute;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  
  p {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: $text-muted;
    margin: 0;
    
    i {
      font-size: 11px;
    }
  }
}

// ==================
// ANIMATIONS
// ==================
@keyframes pulse-ring {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  100% {
    transform: scale(1.4);
    opacity: 0;
  }
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.4s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

// ==================
// RESPONSIVE
// ==================
@media (max-width: 700px) {
  .entry-decision-hero {
    padding: 120px 16px 80px;
  }
  
  .hero-logo .logo-ring {
    width: 64px;
    height: 64px;
    
    .logo-inner {
      width: 54px;
      height: 54px;
      
      i { font-size: 22px; }
    }
  }
  
  .quick-replies {
    max-width: 100%;
  }

  .quick-reply-btn {
    padding: 16px 18px;
  }

  .qr-label {
    font-size: 15px;
  }
  
  .promo-banner {
    flex-direction: column;
    text-align: center;
    
    .promo-text {
      text-align: center;
    }
  }
  
  .hero-footer {
    position: relative;
    bottom: auto;
    left: auto;
    transform: none;
    margin-top: 40px;
  }
  
  // Package selector mobile
  .packages-grid {
    grid-template-columns: 1fr !important;
  }
  
  .package-card {
    padding: 20px !important;
  }
}

// ==================
// PACKAGE SELECTOR
// ==================
.package-selector-content {
  max-width: 1000px;
}

.btn-back-packages {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background: $glass-bg;
  border: 1px solid $glass-border;
  border-radius: 10px;
  color: $text-secondary;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
  margin-bottom: 32px;
  
  &:hover {
    background: rgba(255, 255, 255, 0.1);
    color: $text-primary;
  }
}

.packages-title {
  font-size: 32px;
  font-weight: 700;
  color: $text-primary;
  text-align: center;
  margin: 0 0 12px;
  
  .highlight {
    background: linear-gradient(135deg, $primary, $secondary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
}

.packages-subtitle {
  font-size: 16px;
  color: $text-secondary;
  text-align: center;
  margin: 0 0 40px;
}

.packages-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-bottom: 32px;
}

.package-card {
  position: relative;
  background: $glass-bg;
  border: 2px solid transparent;
  border-radius: 20px;
  padding: 28px 24px;
  cursor: pointer;
  transition: all 0.3s ease;
  
  &:hover {
    background: rgba(255, 255, 255, 0.08);
    transform: translateY(-4px);
  }
  
  &.selected {
    border-color: $primary;
    background: rgba($primary, 0.1);
  }
  
  &.featured {
    border-color: rgba($accent, 0.5);
    
    &.selected {
      border-color: $primary;
    }
  }
  
  .package-badge {
    position: absolute;
    top: -10px;
    left: 50%;
    transform: translateX(-50%);
    padding: 4px 12px;
    background: linear-gradient(135deg, $accent, darken($accent, 10%));
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    color: white;
    white-space: nowrap;
  }
  
  .package-icon {
    font-size: 36px;
    margin-bottom: 12px;
    text-align: center;
  }
  
  .package-name {
    font-size: 18px;
    font-weight: 700;
    color: $text-primary;
    text-align: center;
    margin: 0 0 4px;
  }
  
  .package-tagline {
    font-size: 13px;
    color: $text-secondary;
    text-align: center;
    margin: 0 0 16px;
  }
  
  .package-pages {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 6px;
    margin-bottom: 20px;
    
    .page-tag {
      padding: 4px 10px;
      background: rgba($primary, 0.15);
      border-radius: 6px;
      font-size: 11px;
      color: $primary-light;
    }
  }
  
  .package-price {
    text-align: center;
    
    .price-from {
      display: block;
      font-size: 12px;
      color: $text-muted;
      margin-bottom: 4px;
      
      s {
        color: $text-muted;
      }
    }
    
    .price-now {
      display: block;
      font-size: 28px;
      font-weight: 700;
      color: $text-primary;
      margin-bottom: 4px;
    }
    
    .price-pix {
      display: block;
      font-size: 13px;
      color: $secondary;
      font-weight: 600;
    }
  }
}

.btn-checkout-now {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  width: 100%;
  max-width: 400px;
  padding: 18px 32px;
  background: linear-gradient(135deg, $primary, darken($primary, 15%));
  border: none;
  border-radius: 14px;
  color: white;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-bottom: 20px;
  
  &:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba($primary, 0.4);
  }
  
  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
}

.customize-link {
  font-size: 14px;
  color: $text-secondary;
  text-align: center;
  
  a {
    color: $primary-light;
    text-decoration: none;
    
    &:hover {
      text-decoration: underline;
    }
  }
}

// ==================
// SPECIALIST TOGGLE
// ==================
.specialist-addon-toggle {
  width: 100%;
  margin: 0 0 16px 0;
  border: 2px solid $glass-border;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.04);
  transition: all 0.3s ease;
  
  &:hover {
    border-color: rgba($primary, 0.35);
    background: rgba(255, 255, 255, 0.07);
  }
  
  &.specialist-selected {
    border-color: $primary;
    background: rgba($primary, 0.1);
    box-shadow: 0 0 0 4px rgba($primary, 0.1);
  }
  
  .specialist-toggle-label {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 14px 16px;
    cursor: pointer;
    
    .specialist-toggle-info {
      flex: 1;
      text-align: left;
      
      .specialist-addon-header {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 6px;
        
        i {
          color: $primary-light;
          font-size: 16px;
        }
        
        .specialist-addon-title {
          font-size: 14px;
          font-weight: 600;
          color: $text-primary;
        }
        
        .specialist-addon-price {
          font-size: 12px;
          font-weight: 700;
          color: $secondary;
          background: rgba($secondary, 0.15);
          padding: 3px 9px;
          border-radius: 20px;
          white-space: nowrap;
        }
      }
      
      .specialist-addon-desc {
        font-size: 12px;
        color: $text-muted;
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
          background: linear-gradient(135deg, $primary, darken($primary, 10%));
          
          &::before {
            transform: translateX(22px);
          }
        }
      }
      
      .toggle-slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background: rgba(255, 255, 255, 0.15);
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
          box-shadow: 0 2px 5px rgba(0, 0, 0, 0.25);
        }
      }
    }
  }
}
</style>
