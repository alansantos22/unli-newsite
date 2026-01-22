<template>
  <div class="configurador-page">
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
  </div>
</template>

<script>
import { SiteConfigurator } from '@/shared/Components';

export default {
  name: 'ConfiguradorPage',
  components: {
    SiteConfigurator
  },
  data() {
    return {
      selectedPlan: null
    };
  },
  created() {
    // IMPORTANTE: usar created() ao invés de mounted() 
    // para garantir que selectedPlan esteja disponível antes do render
    console.log('🔍 [ConfiguradorPage] Created - Query params:', this.$route.query);
    console.log('🔍 [ConfiguradorPage] Plan from query:', this.$route.query.plan);
    
    this.selectedPlan = this.$route.query.plan || null;
    console.log('🔍 [ConfiguradorPage] selectedPlan set to:', this.selectedPlan);
    
    // Se não houver plano selecionado, redirecionar para página de vendas
    if (!this.selectedPlan) {
      console.warn('⚠️ [ConfiguradorPage] Nenhum plano selecionado, redirecionando...');
      this.$router.push('/site-vitrine#planos');
    } else {
      console.log('✅ [ConfiguradorPage] Plano válido, continuando...');
    }
  },
  mounted() {
    console.log('🔍 [ConfiguradorPage] Mounted - selectedPlan final:', this.selectedPlan);
  },
  methods: {
    handleOrderSubmitted(orderPayload) {
      console.log('Pedido recebido:', orderPayload);
      
      // TODO: Enviar para backend
      // this.$http.post('/api/orders', orderPayload)
      //   .then(response => {
      //     window.location.href = response.data.payment_url;
      //   });
      
      // Simular redirecionamento para pagamento
      alert('Pedido recebido! Em produção, isso redirecionaria para o gateway de pagamento.');
      
      // Opcional: redirecionar para página de confirmação
      // this.$router.push({ name: 'OrderConfirmation', params: { orderId: response.data.id } });
    },
    
    handleCustomRequest(customData) {
      console.log('Orçamento personalizado:', customData);
      
      // TODO: Enviar para CRM/Email
      // this.$http.post('/api/custom-requests', customData)
      //   .then(() => {
      //     this.$router.push('/site-vitrine#contact');
      //   });
      
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
}

// ==========================================
// HEADER
// ==========================================
.configurador-header {
  position: sticky;
  top: 0;
  z-index: 100;
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
