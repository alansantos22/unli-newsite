<template>
  <div class="sdr-dashboard">
    <header class="dash-header">
      <div class="header-left">
        <span class="logo-icon">🎯</span>
        <div>
          <h1>Painel SDR</h1>
          <p class="welcome">Olá, {{ sdrUser?.name || 'SDR' }}</p>
        </div>
      </div>
      <button class="btn-logout" @click="handleLogout">
        <i class="fas fa-sign-out-alt"></i>
        Sair
      </button>
    </header>

    <main class="dash-main">
      <div class="quick-actions">
        <router-link to="/sdr/calculadora" class="action-card">
          <div class="action-icon">🧮</div>
          <h3>Calculadora</h3>
          <p>Calcular preço do site</p>
        </router-link>

        <router-link to="/sdr/nova-venda" class="action-card accent">
          <div class="action-icon">💰</div>
          <h3>Nova Venda</h3>
          <p>Registrar uma venda</p>
        </router-link>

        <router-link to="/sdr/clientes" class="action-card">
          <div class="action-icon">👥</div>
          <h3>Meus Clientes</h3>
          <p>Ver lista de clientes</p>
        </router-link>
      </div>

      <!-- Últimos clientes -->
      <section class="recent-section">
        <h2><i class="fas fa-clock"></i> Últimos Clientes</h2>

        <div v-if="loadingClients" class="loading-state">
          <i class="fas fa-spinner fa-spin"></i> Carregando...
        </div>

        <div v-else-if="recentClients.length === 0" class="empty-state">
          <p>Nenhum cliente cadastrado ainda.</p>
          <router-link to="/sdr/nova-venda" class="btn-primary-sm">
            <i class="fas fa-plus"></i> Registrar primeira venda
          </router-link>
        </div>

        <div v-else class="clients-mini-list">
          <router-link
            v-for="client in recentClients"
            :key="client.id"
            :to="`/sdr/cliente/${client.id}`"
            class="client-mini-card"
          >
            <div class="client-info">
              <strong>{{ client.customer_name }}</strong>
              <span class="company">{{ client.company_name || '—' }}</span>
            </div>
            <div class="client-meta">
              <span class="badge" :class="client.payment_status">
                {{ client.payment_status === 'paid' ? 'Pago' : 'Pendente' }}
              </span>
              <span class="date">{{ formatDate(client.created_at) }}</span>
            </div>
          </router-link>
        </div>
      </section>
    </main>
  </div>
</template>

<script>
import { useSDRAuth } from '@/core/composables/useSDRAuth';

export default {
  name: 'SDRDashboard',
  data() {
    return {
      recentClients: [],
      loadingClients: true
    };
  },
  computed: {
    sdrUser() {
      const { sdrUser } = useSDRAuth();
      return sdrUser.value;
    }
  },
  async mounted() {
    await this.loadRecentClients();
  },
  methods: {
    async loadRecentClients() {
      this.loadingClients = true;
      try {
        const { authHeaders } = useSDRAuth();
        const res = await fetch('/api/sdr/clients.php?page=1', {
          headers: authHeaders()
        });
        const data = await res.json();
        if (data.ok) {
          this.recentClients = data.clients.slice(0, 5);
        }
      } catch (e) {
        console.error('Erro ao carregar clientes:', e);
      } finally {
        this.loadingClients = false;
      }
    },
    handleLogout() {
      const { logout } = useSDRAuth();
      logout();
      this.$router.push('/sdr');
    },
    formatDate(dateStr) {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
    }
  }
};
</script>

<style lang="scss" scoped>
$primary: #8B5CF6;
$dark: #0F0F1A;
$card-bg: rgba(255,255,255,0.05);
$border: rgba(255,255,255,0.1);

.sdr-dashboard {
  min-height: calc(100vh - 80px);
  background: linear-gradient(135deg, $dark 0%, #1A1A2E 50%, #16213E 100%);
  color: #fff;
  margin-top: 80px;
}

.dash-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid $border;

  .header-left {
    display: flex;
    align-items: center;
    gap: 12px;

    .logo-icon { font-size: 28px; }
    h1 { font-size: 20px; margin: 0; }
    .welcome { font-size: 13px; color: rgba(255,255,255,0.5); margin: 0; }
  }

  .btn-logout {
    background: rgba(239,68,68,0.15);
    color: #EF4444;
    border: 1px solid rgba(239,68,68,0.3);
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
    &:hover { background: rgba(239,68,68,0.25); }
  }
}

.dash-main {
  max-width: 900px;
  margin: 0 auto;
  padding: 24px;
}

.quick-actions {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 32px;

  @media (max-width: 640px) {
    grid-template-columns: 1fr;
  }
}

.action-card {
  background: $card-bg;
  border: 1px solid $border;
  border-radius: 16px;
  padding: 24px;
  text-decoration: none;
  color: #fff;
  text-align: center;
  transition: all 0.2s;

  &:hover {
    transform: translateY(-2px);
    border-color: $primary;
    background: rgba($primary, 0.08);
  }

  &.accent {
    border-color: rgba($primary, 0.4);
    background: rgba($primary, 0.1);
  }

  .action-icon { font-size: 36px; margin-bottom: 8px; }
  h3 { margin: 0 0 4px; font-size: 16px; }
  p { margin: 0; font-size: 13px; color: rgba(255,255,255,0.5); }
}

.recent-section {
  h2 {
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: rgba(255,255,255,0.8);
  }
}

.loading-state, .empty-state {
  text-align: center;
  padding: 32px;
  color: rgba(255,255,255,0.5);
  font-size: 14px;
}

.btn-primary-sm {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-top: 12px;
  padding: 8px 16px;
  background: $primary;
  color: #fff;
  border-radius: 8px;
  text-decoration: none;
  font-size: 13px;
  &:hover { opacity: 0.9; }
}

.clients-mini-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.client-mini-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 16px;
  background: $card-bg;
  border: 1px solid $border;
  border-radius: 12px;
  text-decoration: none;
  color: #fff;
  transition: all 0.2s;

  &:hover {
    border-color: rgba($primary, 0.4);
    background: rgba($primary, 0.05);
  }

  .client-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
    strong { font-size: 14px; }
    .company { font-size: 12px; color: rgba(255,255,255,0.4); }
  }

  .client-meta {
    display: flex;
    align-items: center;
    gap: 10px;

    .badge {
      font-size: 11px;
      padding: 3px 8px;
      border-radius: 6px;
      font-weight: 600;
      &.paid { background: rgba(16,185,129,0.2); color: #10B981; }
      &.pending { background: rgba(245,158,11,0.2); color: #F59E0B; }
    }

    .date { font-size: 12px; color: rgba(255,255,255,0.3); }
  }
}
</style>
