<template>
  <div class="sdr-nova-venda" :class="{ 'in-configurator': selectedProduct }">
    <header class="sdr-nv-header">
      <router-link to="/sdr/dashboard" class="btn-back">
        <i class="fas fa-arrow-left"></i> Dashboard
      </router-link>
      <h1><i class="fas fa-plus-circle"></i> Nova Venda</h1>
    </header>

    <!-- FASE 1: Escolha do produto -->
    <div v-if="!selectedProduct" class="product-choice">
      <h2>Qual tipo de site o cliente precisa?</h2>
      <div class="choice-cards">
        <button class="choice-card" @click="selectProduct('site_complete')">
          <div class="card-icon"><i class="fas fa-globe"></i></div>
          <h3>Site Completo</h3>
          <p>Site institucional com múltiplas páginas, ideal para empresas que precisam de presença completa.</p>
          <span class="card-badge">Mais vendido</span>
        </button>
        <button class="choice-card" @click="selectProduct('landing')">
          <div class="card-icon"><i class="fas fa-rocket"></i></div>
          <h3>Landing Page</h3>
          <p>Página única focada em conversão, perfeita para campanhas e lançamentos.</p>
        </button>
      </div>
    </div>

    <!-- FASE 2: SiteConfigurator com sdrMode -->
    <SiteConfigurator
      v-else
      :sdr-mode="true"
      :initial-product="selectedProduct"
    />
  </div>
</template>

<script>
import SiteConfigurator from '@/shared/Components/SiteConfigurator.vue';

export default {
  name: 'SDRNovaVenda',
  components: { SiteConfigurator },
  data() {
    return {
      selectedProduct: null
    };
  },
  methods: {
    selectProduct(product) {
      this.selectedProduct = product;
    }
  }
};
</script>

<style lang="scss" scoped>
$primary: #8B5CF6;
$dark: #0F0F1A;
$card-bg: rgba(255, 255, 255, 0.05);
$border: rgba(255, 255, 255, 0.1);
$green: #10B981;

.sdr-nova-venda {
  min-height: calc(100vh - 80px);
  background: linear-gradient(135deg, $dark 0%, #1A1A2E 50%, #16213E 100%);
  color: #fff;
  margin-top: 80px;

  // Quando o SiteConfigurator está ativo, muda para fundo claro
  &.in-configurator {
    background: #f3f4f6;
    color: #1a1d23;
  }
}

.sdr-nv-header {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px 24px;
  background: linear-gradient(135deg, #0F0F1A 0%, #1A1A2E 100%);
  border-bottom: 3px solid $primary;

  .btn-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    padding: 6px 12px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 8px;
    transition: all 0.2s;
    white-space: nowrap;
    &:hover {
      color: #fff;
      border-color: rgba(255, 255, 255, 0.4);
      background: rgba(255, 255, 255, 0.08);
    }
  }

  h1 {
    font-size: 18px;
    margin: 0;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
    i { color: $primary; }
  }
}

.product-choice {
  max-width: 700px;
  margin: 0 auto;
  padding: 48px 24px;
  text-align: center;

  h2 {
    font-size: 22px;
    font-weight: 700;
    margin: 0 0 32px;
    color: rgba(255, 255, 255, 0.9);
  }
}

.choice-cards {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;

  @media (max-width: 540px) {
    grid-template-columns: 1fr;
  }
}

.choice-card {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 32px 24px;
  background: $card-bg;
  border: 2px solid $border;
  border-radius: 16px;
  color: #fff;
  cursor: pointer;
  transition: all 0.25s;
  text-align: center;

  &:hover {
    border-color: $primary;
    background: rgba($primary, 0.08);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba($primary, 0.15);
  }

  .card-icon {
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba($primary, 0.15);
    border-radius: 14px;
    font-size: 24px;
    color: $primary;
  }

  h3 {
    font-size: 18px;
    font-weight: 700;
    margin: 0;
  }

  p {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.5);
    margin: 0;
    line-height: 1.4;
  }

  .card-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 3px 10px;
    background: rgba($green, 0.2);
    color: $green;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
  }
}
</style>
