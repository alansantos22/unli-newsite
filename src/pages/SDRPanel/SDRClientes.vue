<template>
  <div class="sdr-clientes">
    <header class="page-header">
      <router-link to="/sdr/dashboard" class="btn-back">
        <i class="fas fa-arrow-left"></i>
      </router-link>
      <h1>Meus Clientes</h1>
    </header>

    <main class="list-main">
      <!-- Filtros -->
      <div class="filters-bar">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input v-model="search" type="text" placeholder="Buscar por nome ou e-mail..." @input="debouncedSearch" />
        </div>
        <select v-model="filterPayment" @change="loadClients">
          <option value="">Todos pagamentos</option>
          <option value="paid">Pago</option>
          <option value="pending">Pendente</option>
        </select>
        <select v-model="filterOnboarding" @change="loadClients">
          <option value="">Todos onboarding</option>
          <option value="pendente">Pendente</option>
          <option value="preenchendo">Preenchendo</option>
          <option value="concluido">Concluído</option>
        </select>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="loading-state">
        <i class="fas fa-spinner fa-spin"></i> Carregando...
      </div>

      <!-- Empty -->
      <div v-else-if="clients.length === 0" class="empty-state">
        <div class="empty-icon">👥</div>
        <p>Nenhum cliente encontrado</p>
      </div>

      <!-- Lista -->
      <div v-else class="clients-table-wrapper">
        <table class="clients-table">
          <thead>
            <tr>
              <th>Cliente</th>
              <th>Empresa</th>
              <th>Contato</th>
              <th>Pagamento</th>
              <th>Onboarding</th>
              <th>Data</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="client in clients"
              :key="client.id"
              @click="$router.push(`/sdr/cliente/${client.id}`)"
              class="client-row"
            >
              <td>
                <strong>{{ client.customer_name }}</strong>
              </td>
              <td>{{ client.company_name || '—' }}</td>
              <td>
                <div class="contact-cell">
                  <span class="email">{{ client.email }}</span>
                  <span class="phone">{{ client.phone || '—' }}</span>
                </div>
              </td>
              <td>
                <span class="badge" :class="client.payment_status">
                  {{ paymentLabel(client.payment_status) }}
                </span>
              </td>
              <td>
                <span class="badge onb" :class="client.onboarding_status">
                  {{ onboardingLabel(client.onboarding_status) }}
                </span>
              </td>
              <td class="date-cell">{{ formatDate(client.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginação -->
      <div v-if="pagination.total_pages > 1" class="pagination">
        <button :disabled="pagination.page <= 1" @click="goPage(pagination.page - 1)">
          <i class="fas fa-chevron-left"></i>
        </button>
        <span>{{ pagination.page }} / {{ pagination.total_pages }}</span>
        <button :disabled="pagination.page >= pagination.total_pages" @click="goPage(pagination.page + 1)">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </main>
  </div>
</template>

<script>
import { useSDRAuth } from '@/core/composables/useSDRAuth';

export default {
  name: 'SDRClientes',
  data() {
    return {
      clients: [],
      loading: true,
      search: '',
      filterPayment: '',
      filterOnboarding: '',
      pagination: { page: 1, total_pages: 1, total: 0 },
      searchTimeout: null
    };
  },
  async mounted() {
    await this.loadClients();
  },
  methods: {
    debouncedSearch() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => this.loadClients(), 400);
    },
    async loadClients(page = 1) {
      this.loading = true;
      try {
        const { authHeaders } = useSDRAuth();
        const params = new URLSearchParams({ page });
        if (this.search) params.set('search', this.search);
        if (this.filterPayment) params.set('payment_status', this.filterPayment);
        if (this.filterOnboarding) params.set('onboarding_status', this.filterOnboarding);

        const res = await fetch(`/api/sdr/clients.php?${params}`, { headers: authHeaders() });
        const data = await res.json();

        if (data.ok) {
          this.clients = data.clients;
          this.pagination = data.pagination;
        }
      } catch (e) {
        console.error('Erro:', e);
      } finally {
        this.loading = false;
      }
    },
    goPage(page) {
      this.loadClients(page);
    },
    paymentLabel(status) {
      const m = { paid: 'Pago', pending: 'Pendente', failed: 'Falhou', refunded: 'Reembolsado' };
      return m[status] || status;
    },
    onboardingLabel(status) {
      const m = { pendente: 'Pendente', preenchendo: 'Preenchendo', concluido: 'Concluído' };
      return m[status] || status;
    },
    formatDate(dateStr) {
      if (!dateStr) return '';
      return new Date(dateStr).toLocaleDateString('pt-BR');
    }
  }
};
</script>

<style lang="scss" scoped>
$primary: #8B5CF6;
$dark: #0F0F1A;
$card-bg: rgba(255,255,255,0.05);
$border: rgba(255,255,255,0.1);
$green: #10B981;

.sdr-clientes {
  min-height: calc(100vh - 80px);
  background: linear-gradient(135deg, $dark 0%, #1A1A2E 50%, #16213E 100%);
  color: #fff;
  margin-top: 80px;
}

.page-header {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px 24px;
  border-bottom: 1px solid $border;
  .btn-back { color: rgba(255,255,255,0.6); text-decoration: none; font-size: 18px; &:hover { color: #fff; } }
  h1 { font-size: 20px; margin: 0; }
}

.list-main {
  max-width: 1000px;
  margin: 0 auto;
  padding: 24px;
}

.filters-bar {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  flex-wrap: wrap;

  .search-box {
    flex: 1;
    min-width: 200px;
    position: relative;
    i {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: rgba(255,255,255,0.3);
      font-size: 13px;
    }
    input {
      width: 100%;
      padding: 10px 12px 10px 36px;
      background: rgba(255,255,255,0.08);
      border: 1px solid $border;
      border-radius: 8px;
      color: #fff;
      font-size: 13px;
      &::placeholder { color: rgba(255,255,255,0.3); }
      &:focus { outline: none; border-color: $primary; }
    }
  }

  select {
    padding: 10px 12px;
    background: rgba(255,255,255,0.08);
    border: 1px solid $border;
    border-radius: 8px;
    color: #fff;
    font-size: 13px;
    cursor: pointer;
    option { background: #1A1A2E; }
  }
}

.loading-state, .empty-state {
  text-align: center;
  padding: 48px;
  color: rgba(255,255,255,0.5);
  .empty-icon { font-size: 48px; margin-bottom: 12px; }
}

.clients-table-wrapper {
  overflow-x: auto;
}

.clients-table {
  width: 100%;
  border-collapse: collapse;

  th {
    text-align: left;
    padding: 10px 12px;
    font-size: 11px;
    font-weight: 700;
    color: rgba(255,255,255,0.4);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid $border;
  }

  .client-row {
    cursor: pointer;
    transition: background 0.15s;
    &:hover { background: rgba($primary, 0.05); }

    td {
      padding: 12px;
      font-size: 13px;
      border-bottom: 1px solid rgba(255,255,255,0.05);
      vertical-align: middle;
    }
  }
}

.contact-cell {
  display: flex;
  flex-direction: column;
  gap: 2px;
  .email { font-size: 12px; }
  .phone { font-size: 11px; color: rgba(255,255,255,0.4); }
}

.badge {
  display: inline-block;
  font-size: 11px;
  padding: 3px 8px;
  border-radius: 6px;
  font-weight: 600;
  &.paid { background: rgba($green, 0.2); color: $green; }
  &.pending { background: rgba(245,158,11,0.2); color: #F59E0B; }
  &.failed { background: rgba(239,68,68,0.2); color: #EF4444; }

  &.onb {
    &.pendente { background: rgba(156,163,175,0.2); color: #9CA3AF; }
    &.preenchendo { background: rgba(59,130,246,0.2); color: #3B82F6; }
    &.concluido { background: rgba($green, 0.2); color: $green; }
  }
}

.date-cell {
  white-space: nowrap;
  color: rgba(255,255,255,0.4);
  font-size: 12px !important;
}

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  margin-top: 24px;

  span { font-size: 13px; color: rgba(255,255,255,0.5); }

  button {
    padding: 8px 12px;
    background: $card-bg;
    border: 1px solid $border;
    border-radius: 8px;
    color: #fff;
    cursor: pointer;
    &:disabled { opacity: 0.3; cursor: not-allowed; }
    &:hover:not(:disabled) { border-color: $primary; }
  }
}
</style>
