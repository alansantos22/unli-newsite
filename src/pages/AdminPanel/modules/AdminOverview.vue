<template>
  <div class="admin-overview">
    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <i class="fas fa-spinner fa-spin"></i> Carregando dados...
    </div>

    <template v-else>
      <!-- KPI Cards -->
      <div class="kpi-grid">
        <div class="kpi-card">
          <div class="kpi-icon yellow"><i class="fas fa-users"></i></div>
          <div class="kpi-data">
            <span class="kpi-value">{{ overview.affiliates?.active || 0 }}</span>
            <span class="kpi-label">Afiliados Ativos</span>
          </div>
          <span class="kpi-sub">{{ overview.affiliates?.total || 0 }} total</span>
        </div>

        <div class="kpi-card">
          <div class="kpi-icon blue"><i class="fas fa-headset"></i></div>
          <div class="kpi-data">
            <span class="kpi-value">{{ overview.sdrs?.active || 0 }}</span>
            <span class="kpi-label">SDRs Ativos</span>
          </div>
          <span class="kpi-sub">{{ overview.sdrs?.total || 0 }} total</span>
        </div>

        <div class="kpi-card">
          <div class="kpi-icon green"><i class="fas fa-dollar-sign"></i></div>
          <div class="kpi-data">
            <span class="kpi-value">{{ formatCurrency(overview.revenue?.total_from_referrals) }}</span>
            <span class="kpi-label">Receita por Indicações</span>
          </div>
          <span class="kpi-sub">{{ overview.revenue?.closed_sales || 0 }} vendas fechadas</span>
        </div>

        <div class="kpi-card">
          <div class="kpi-icon red"><i class="fas fa-clock"></i></div>
          <div class="kpi-data">
            <span class="kpi-value">{{ formatCurrency(overview.revenue?.pending_commissions) }}</span>
            <span class="kpi-label">Comissões Pendentes</span>
          </div>
          <span class="kpi-sub">a pagar</span>
        </div>
      </div>

      <!-- Health Bar -->
      <div class="health-section">
        <h3><i class="fas fa-heartbeat"></i> Saúde do Sistema</h3>
        <div class="health-grid">
          <div class="health-item">
            <span class="health-number green">{{ health.new_affiliates_this_week }}</span>
            <span class="health-label">Novos afiliados esta semana</span>
          </div>
          <div class="health-item">
            <span class="health-number" :class="health.inactivity_rate > 50 ? 'red' : 'yellow'">{{ health.inactivity_rate }}%</span>
            <span class="health-label">Taxa de inatividade (30d)</span>
          </div>
          <div class="health-item">
            <span class="health-number red">{{ health.pending_payments_count }}</span>
            <span class="health-label">Pagamentos pendentes</span>
          </div>
          <div class="health-item">
            <span class="health-number blue">{{ overview.orders?.total || 0 }}</span>
            <span class="health-label">Total de pedidos</span>
          </div>
        </div>
      </div>

      <!-- Top Affiliates -->
      <div class="top-section">
        <h3><i class="fas fa-ranking-star"></i> Top 10 Afiliados (Receita)</h3>
        <div class="table-wrapper">
          <table class="admin-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Nome</th>
                <th>Liga</th>
                <th>Indicações</th>
                <th>Receita Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(aff, i) in charts.top_affiliates" :key="aff.id">
                <td class="rank">{{ i + 1 }}</td>
                <td>{{ aff.name }}</td>
                <td><span class="tier-badge" :class="aff.tier">{{ tierLabel(aff.tier) }}</span></td>
                <td>{{ aff.total_referrals }}</td>
                <td class="currency">{{ formatCurrency(aff.total_sales_amount) }}</td>
              </tr>
              <tr v-if="!charts.top_affiliates?.length">
                <td colspan="5" class="empty">Nenhum dado disponível</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Orders Breakdown -->
      <div class="breakdown-section">
        <h3><i class="fas fa-chart-pie"></i> Origem das Vendas</h3>
        <div class="breakdown-grid">
          <div class="breakdown-card">
            <span class="breakdown-value">{{ overview.orders?.by_sdr || 0 }}</span>
            <span class="breakdown-label">Via SDR</span>
          </div>
          <div class="breakdown-card">
            <span class="breakdown-value">{{ overview.orders?.by_affiliate || 0 }}</span>
            <span class="breakdown-label">Via Afiliado</span>
          </div>
          <div class="breakdown-card">
            <span class="breakdown-value">{{ directSales }}</span>
            <span class="breakdown-label">Diretas / Orgânicas</span>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import { useAdminAuth } from '@/core/composables/useAdminAuth';

export default {
  name: 'AdminOverview',
  data() {
    return {
      loading: true,
      overview: {},
      charts: {},
      health: {}
    };
  },
  computed: {
    directSales() {
      const total = this.overview.orders?.total || 0;
      const sdr = this.overview.orders?.by_sdr || 0;
      const aff = this.overview.orders?.by_affiliate || 0;
      return Math.max(0, total - sdr - aff);
    }
  },
  async created() {
    await this.loadData();
  },
  methods: {
    async loadData() {
      this.loading = true;
      const { adminFetch } = useAdminAuth();

      try {
        const [overviewRes, chartsRes, healthRes] = await Promise.all([
          adminFetch('/api/admin/dashboard.php?action=overview'),
          adminFetch('/api/admin/dashboard.php?action=charts'),
          adminFetch('/api/admin/dashboard.php?action=health')
        ]);

        if (overviewRes.ok) this.overview = overviewRes.data;
        if (chartsRes.ok) this.charts = chartsRes.data;
        if (healthRes.ok) this.health = healthRes.data;
      } catch (e) {
        console.error('Erro ao carregar dashboard:', e);
      } finally {
        this.loading = false;
      }
    },
    formatCurrency(value) {
      return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0);
    },
    tierLabel(tier) {
      const labels = {
        bronze_1: 'Bronze I', bronze_2: 'Bronze II',
        prata_1: 'Prata I', prata_2: 'Prata II',
        ouro_1: 'Ouro I', ouro_2: 'Ouro II',
        diamante_1: 'Diamante I', diamante_2: 'Diamante II'
      };
      return labels[tier] || tier;
    }
  }
};
</script>

<style lang="scss" scoped>
.loading-state {
  text-align: center;
  padding: 60px;
  color: #64748b;
  font-size: 16px;
  i { margin-right: 8px; }
}

// KPI Grid
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
  margin-bottom: 32px;
}

.kpi-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 14px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  position: relative;
}

.kpi-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  flex-shrink: 0;

  &.yellow { background: rgba(245,158,11,0.15); color: #f59e0b; }
  &.blue   { background: rgba(59,130,246,0.15); color: #3b82f6; }
  &.green  { background: rgba(34,197,94,0.15); color: #22c55e; }
  &.red    { background: rgba(239,68,68,0.15); color: #ef4444; }
}

.kpi-data {
  display: flex;
  flex-direction: column;

  .kpi-value { font-size: 22px; font-weight: 700; color: #f1f5f9; }
  .kpi-label { font-size: 12px; color: #94a3b8; margin-top: 2px; }
}

.kpi-sub {
  position: absolute;
  top: 12px;
  right: 16px;
  font-size: 11px;
  color: #64748b;
}

// Health
.health-section, .top-section, .breakdown-section {
  margin-bottom: 32px;

  h3 {
    font-size: 16px;
    font-weight: 600;
    color: #e2e8f0;
    margin: 0 0 16px;
    i { margin-right: 8px; color: #f59e0b; }
  }
}

.health-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 12px;
}

.health-item {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 12px;
  padding: 16px;
  text-align: center;

  .health-number {
    display: block;
    font-size: 28px;
    font-weight: 700;
    &.green { color: #22c55e; }
    &.yellow { color: #f59e0b; }
    &.red { color: #ef4444; }
    &.blue { color: #3b82f6; }
  }

  .health-label {
    font-size: 12px;
    color: #94a3b8;
    margin-top: 4px;
    display: block;
  }
}

// Table
.table-wrapper {
  overflow-x: auto;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.06);
}

.admin-table {
  width: 100%;
  border-collapse: collapse;

  th {
    background: rgba(255, 255, 255, 0.04);
    padding: 12px 16px;
    text-align: left;
    font-size: 12px;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
  }

  td {
    padding: 12px 16px;
    font-size: 14px;
    border-top: 1px solid rgba(255, 255, 255, 0.04);
  }

  .rank { color: #f59e0b; font-weight: 700; }
  .currency { color: #22c55e; font-weight: 600; }
  .empty { text-align: center; color: #64748b; padding: 24px; }
}

.tier-badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;

  &.bronze_1, &.bronze_2 { background: rgba(205,127,50,0.2); color: #cd7f32; }
  &.prata_1, &.prata_2 { background: rgba(192,192,192,0.2); color: #c0c0c0; }
  &.ouro_1, &.ouro_2 { background: rgba(255,215,0,0.2); color: #ffd700; }
  &.diamante_1, &.diamante_2 { background: rgba(185,242,255,0.2); color: #b9f2ff; }
}

// Breakdown
.breakdown-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}

.breakdown-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 12px;
  padding: 20px;
  text-align: center;

  .breakdown-value {
    display: block;
    font-size: 32px;
    font-weight: 700;
    color: #f59e0b;
  }

  .breakdown-label {
    font-size: 13px;
    color: #94a3b8;
    margin-top: 4px;
    display: block;
  }
}

@media (max-width: 640px) {
  .breakdown-grid { grid-template-columns: 1fr; }
}
</style>
