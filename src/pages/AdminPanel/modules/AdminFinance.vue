<template>
  <div class="admin-finance">
    <!-- Tabs -->
    <div class="sub-tabs">
      <button :class="{ active: subTab === 'pending' }" @click="subTab = 'pending'">
        <i class="fas fa-clock"></i> Pendentes
      </button>
      <button :class="{ active: subTab === 'audit' }" @click="subTab = 'audit'">
        <i class="fas fa-search-dollar"></i> Auditoria
      </button>
      <button :class="{ active: subTab === 'history' }" @click="subTab = 'history'">
        <i class="fas fa-history"></i> Histórico de Pagamentos
      </button>
    </div>

    <!-- Pending Commissions -->
    <div v-if="subTab === 'pending'">
      <div v-if="loading" class="loading-state"><i class="fas fa-spinner fa-spin"></i> Carregando...</div>
      <template v-else>
        <!-- Aggregated by affiliate -->
        <h3><i class="fas fa-wallet"></i> Comissões Pendentes por Afiliado</h3>
        <div class="table-wrapper" v-if="pending.by_affiliate?.length">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Afiliado</th>
                <th>Liga</th>
                <th>Chave PIX</th>
                <th>Qtd Pendente</th>
                <th>Total a Pagar</th>
                <th>Ação</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="aff in pending.by_affiliate" :key="aff.affiliate_id">
                <td class="name-cell">{{ aff.affiliate_name }}</td>
                <td><span class="tier-badge" :class="aff.tier">{{ tierLabel(aff.tier) }}</span></td>
                <td><code v-if="aff.pix_key">{{ aff.pix_key }}</code><span v-else class="muted">—</span></td>
                <td>{{ aff.pending_count }}</td>
                <td class="currency">{{ formatCurrency(aff.pending_total) }}</td>
                <td>
                  <button class="btn-pay" @click="bulkPay(aff)" :disabled="paying">
                    <i :class="paying ? 'fas fa-spinner fa-spin' : 'fas fa-check-double'"></i>
                    Baixa em Massa
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-else class="empty-state"><i class="fas fa-party-horn"></i> Nenhuma comissão pendente! 🎉</p>

        <!-- Individual details -->
        <h3><i class="fas fa-list"></i> Detalhamento Individual</h3>
        <div class="table-wrapper" v-if="pending.details?.length">
          <table class="admin-table">
            <thead>
              <tr>
                <th><input type="checkbox" v-model="selectAll" @change="toggleSelectAll" /></th>
                <th>Afiliado</th>
                <th>Lead</th>
                <th>Valor Venda</th>
                <th>Taxa</th>
                <th>Comissão</th>
                <th>Data</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in pending.details" :key="item.id">
                <td><input type="checkbox" :value="item.id" v-model="selectedIds" /></td>
                <td>{{ item.affiliate_name }}</td>
                <td>{{ item.lead_name || '—' }}</td>
                <td>{{ formatCurrency(item.sale_amount) }}</td>
                <td>{{ item.commission_rate }}%</td>
                <td class="currency">{{ formatCurrency(item.commission_amount) }}</td>
                <td>{{ formatDate(item.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="batch-actions" v-if="selectedIds.length">
          <span>{{ selectedIds.length }} selecionada(s)</span>
          <button class="btn-primary" @click="paySelected" :disabled="paying">
            <i :class="paying ? 'fas fa-spinner fa-spin' : 'fas fa-check'"></i>
            Marcar como Pago
          </button>
        </div>
      </template>
    </div>

    <!-- Audit -->
    <div v-if="subTab === 'audit'">
      <div class="filters-bar">
        <select v-model="auditFilter.status" @change="loadAudit">
          <option value="">Todos status</option>
          <option value="lead">Lead</option>
          <option value="contacted">Contatado</option>
          <option value="negotiating">Negociando</option>
          <option value="closed">Fechado</option>
          <option value="completed">Completo</option>
          <option value="lost">Perdido</option>
        </select>
        <input type="date" v-model="auditFilter.from" @change="loadAudit" class="date-input" />
        <input type="date" v-model="auditFilter.to" @change="loadAudit" class="date-input" />
      </div>

      <div class="table-wrapper">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Afiliado</th>
              <th>Liga</th>
              <th>Lead</th>
              <th>Status</th>
              <th>Valor</th>
              <th>Comissão</th>
              <th>SDR</th>
              <th>Pago?</th>
              <th>Data</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in auditData" :key="item.id">
              <td class="name-cell">{{ item.affiliate_name }}</td>
              <td><span class="tier-badge" :class="item.affiliate_tier">{{ tierLabel(item.affiliate_tier) }}</span></td>
              <td>{{ item.lead_name || '—' }}</td>
              <td><span class="status-tag" :class="item.status">{{ item.status }}</span></td>
              <td>{{ formatCurrency(item.sale_amount) }}</td>
              <td class="currency">{{ formatCurrency(item.commission_amount) }}</td>
              <td>{{ item.sdr_name || '—' }}</td>
              <td>{{ item.commission_paid ? '✅' : '⏳' }}</td>
              <td>{{ formatDate(item.created_at) }}</td>
            </tr>
            <tr v-if="!auditData.length">
              <td colspan="9" class="empty">Nenhum registro encontrado</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="pagination" v-if="auditPagination.pages > 1">
        <button :disabled="auditPagination.page <= 1" @click="auditPage(auditPagination.page - 1)"><i class="fas fa-chevron-left"></i></button>
        <span>{{ auditPagination.page }} / {{ auditPagination.pages }}</span>
        <button :disabled="auditPagination.page >= auditPagination.pages" @click="auditPage(auditPagination.page + 1)"><i class="fas fa-chevron-right"></i></button>
      </div>
    </div>

    <!-- Payment History -->
    <div v-if="subTab === 'history'">
      <div class="table-wrapper">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Afiliado</th>
              <th>Lead</th>
              <th>Valor Venda</th>
              <th>Comissão Paga</th>
              <th>Data Pagamento</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in historyData" :key="item.id">
              <td class="name-cell">{{ item.affiliate_name }}</td>
              <td>{{ item.lead_name || '—' }}</td>
              <td>{{ formatCurrency(item.sale_amount) }}</td>
              <td class="currency">{{ formatCurrency(item.commission_amount) }}</td>
              <td>{{ formatDate(item.commission_paid_at) }}</td>
            </tr>
            <tr v-if="!historyData.length">
              <td colspan="5" class="empty">Nenhum pagamento registrado</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="pagination" v-if="historyPagination.pages > 1">
        <button :disabled="historyPagination.page <= 1" @click="historyPage(historyPagination.page - 1)"><i class="fas fa-chevron-left"></i></button>
        <span>{{ historyPagination.page }} / {{ historyPagination.pages }}</span>
        <button :disabled="historyPagination.page >= historyPagination.pages" @click="historyPage(historyPagination.page + 1)"><i class="fas fa-chevron-right"></i></button>
      </div>
    </div>

    <!-- Feedback -->
    <div v-if="feedback" class="feedback-toast" :class="feedback.type">
      <i :class="feedback.type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
      {{ feedback.message }}
    </div>
  </div>
</template>

<script>
import { useAdminAuth } from '@/core/composables/useAdminAuth';

export default {
  name: 'AdminFinance',
  data() {
    return {
      subTab: 'pending',
      loading: false,
      paying: false,
      feedback: null,
      pending: { by_affiliate: [], details: [] },
      selectedIds: [],
      selectAll: false,
      auditData: [],
      auditPagination: { page: 1, pages: 1 },
      auditFilter: { status: '', from: '', to: '' },
      historyData: [],
      historyPagination: { page: 1, pages: 1 }
    };
  },
  watch: {
    subTab(tab) {
      if (tab === 'pending') this.loadPending();
      if (tab === 'audit') this.loadAudit();
      if (tab === 'history') this.loadHistory();
    }
  },
  async created() {
    await this.loadPending();
  },
  methods: {
    async loadPending() {
      this.loading = true;
      const { adminFetch } = useAdminAuth();
      const res = await adminFetch('/api/admin/finance.php?action=pending_commissions');
      if (res.ok) this.pending = res.data;
      this.loading = false;
    },
    toggleSelectAll() {
      this.selectedIds = this.selectAll ? this.pending.details.map(d => d.id) : [];
    },
    async bulkPay(aff) {
      if (!confirm(`Marcar todas as ${aff.pending_count} comissões de ${aff.affiliate_name} como pagas?`)) return;
      this.paying = true;
      const { adminFetch } = useAdminAuth();
      const res = await adminFetch('/api/admin/finance.php?action=mark_paid', {
        method: 'POST',
        body: JSON.stringify({ affiliate_id: aff.affiliate_id })
      });
      this.paying = false;
      this.showFeedback(res.ok ? 'success' : 'error', res.message || res.error);
      if (res.ok) this.loadPending();
    },
    async paySelected() {
      if (!confirm(`Marcar ${this.selectedIds.length} comissão(ões) como paga(s)?`)) return;
      this.paying = true;
      const { adminFetch } = useAdminAuth();
      const res = await adminFetch('/api/admin/finance.php?action=mark_paid', {
        method: 'POST',
        body: JSON.stringify({ referral_ids: this.selectedIds })
      });
      this.paying = false;
      this.selectedIds = [];
      this.selectAll = false;
      this.showFeedback(res.ok ? 'success' : 'error', res.message || res.error);
      if (res.ok) this.loadPending();
    },
    async loadAudit() {
      const { adminFetch } = useAdminAuth();
      const params = new URLSearchParams({
        action: 'audit_sales',
        page: this.auditPagination.page,
        ...this.auditFilter
      });
      const res = await adminFetch(`/api/admin/finance.php?${params}`);
      if (res.ok) { this.auditData = res.data; this.auditPagination = res.pagination; }
    },
    auditPage(p) { this.auditPagination.page = p; this.loadAudit(); },
    async loadHistory() {
      const { adminFetch } = useAdminAuth();
      const res = await adminFetch(`/api/admin/finance.php?action=payment_history&page=${this.historyPagination.page}`);
      if (res.ok) { this.historyData = res.data; this.historyPagination = res.pagination; }
    },
    historyPage(p) { this.historyPagination.page = p; this.loadHistory(); },
    showFeedback(type, message) {
      this.feedback = { type, message };
      setTimeout(() => { this.feedback = null; }, 3500);
    },
    formatCurrency(v) { return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v || 0); },
    formatDate(d) { if (!d) return ''; return new Date(d).toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }); },
    tierLabel(tier) {
      const labels = { bronze_1:'Bronze I', bronze_2:'Bronze II', prata_1:'Prata I', prata_2:'Prata II', ouro_1:'Ouro I', ouro_2:'Ouro II', diamante_1:'Diamante I', diamante_2:'Diamante II' };
      return labels[tier] || tier;
    }
  }
};
</script>

<style lang="scss" scoped>
.sub-tabs {
  display: flex;
  gap: 8px;
  margin-bottom: 24px;

  button {
    padding: 10px 18px;
    border: 1px solid rgba(255,255,255,0.1);
    background: rgba(255,255,255,0.03);
    border-radius: 10px;
    color: #94a3b8;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;

    &:hover { color: #f1f5f9; border-color: rgba(255,255,255,0.15); }
    &.active { color: #f59e0b; border-color: #f59e0b; background: rgba(245,158,11,0.08); }
  }
}

.loading-state { text-align: center; padding: 40px; color: #64748b; i { margin-right: 8px; } }
.empty-state { text-align: center; padding: 40px; color: #64748b; font-size: 15px; }

h3 {
  font-size: 15px; color: #e2e8f0; margin: 24px 0 12px;
  i { margin-right: 8px; color: #f59e0b; }
  &:first-child { margin-top: 0; }
}

.filters-bar {
  display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;

  select, .date-input {
    padding: 10px 14px; background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1); border-radius: 10px;
    color: #f1f5f9; font-size: 13px; outline: none;
    &:focus { border-color: #f59e0b; }
  }
}

.table-wrapper { overflow-x: auto; border-radius: 12px; border: 1px solid rgba(255,255,255,0.06); }

.admin-table {
  width: 100%; border-collapse: collapse;
  th { background: rgba(255,255,255,0.04); padding: 12px 14px; text-align: left; font-size: 12px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
  td { padding: 12px 14px; font-size: 13px; border-top: 1px solid rgba(255,255,255,0.04); }
  .currency { color: #22c55e; font-weight: 600; }
  .empty { text-align: center; color: #64748b; padding: 24px; }

  input[type="checkbox"] { cursor: pointer; accent-color: #f59e0b; }
  code { background: rgba(255,255,255,0.06); padding: 2px 6px; border-radius: 4px; font-size: 12px; color: #f1f5f9; }
  .muted { color: #475569; }
}

.name-cell { font-weight: 600; color: #f1f5f9; }

.tier-badge {
  display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;
  &.bronze_1, &.bronze_2 { background: rgba(205,127,50,0.2); color: #cd7f32; }
  &.prata_1, &.prata_2 { background: rgba(192,192,192,0.2); color: #c0c0c0; }
  &.ouro_1, &.ouro_2 { background: rgba(255,215,0,0.2); color: #ffd700; }
  &.diamante_1, &.diamante_2 { background: rgba(185,242,255,0.2); color: #b9f2ff; }
}

.status-tag {
  font-size: 11px; padding: 2px 8px; border-radius: 8px; font-weight: 600;
  &.lead { background: rgba(99,102,241,0.2); color: #818cf8; }
  &.contacted { background: rgba(59,130,246,0.2); color: #60a5fa; }
  &.negotiating { background: rgba(245,158,11,0.2); color: #fbbf24; }
  &.closed, &.completed, &.onboarding { background: rgba(34,197,94,0.2); color: #4ade80; }
  &.lost { background: rgba(239,68,68,0.2); color: #f87171; }
}

.btn-pay {
  background: rgba(34,197,94,0.15); color: #22c55e; border: 1px solid rgba(34,197,94,0.3);
  padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600;
  cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
  &:hover:not(:disabled) { background: rgba(34,197,94,0.25); }
  &:disabled { opacity: 0.5; cursor: not-allowed; }
}

.batch-actions {
  display: flex; align-items: center; gap: 16px; margin-top: 16px;
  padding: 12px 16px; background: rgba(245,158,11,0.08);
  border: 1px solid rgba(245,158,11,0.2); border-radius: 12px;
  span { color: #f59e0b; font-size: 13px; font-weight: 600; }
}

.btn-primary {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: #0f172a; border: none; padding: 10px 20px; border-radius: 10px;
  font-weight: 700; font-size: 13px; cursor: pointer;
  display: inline-flex; align-items: center; gap: 6px;
  &:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(245,158,11,0.3); }
  &:disabled { opacity: 0.6; cursor: not-allowed; }
}

.pagination {
  display: flex; align-items: center; justify-content: center; gap: 16px; margin-top: 20px;
  span { color: #94a3b8; font-size: 13px; }
  button {
    background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px; color: #f1f5f9; width: 36px; height: 36px; cursor: pointer;
    &:disabled { opacity: 0.3; cursor: not-allowed; }
    &:hover:not(:disabled) { border-color: #f59e0b; }
  }
}

.feedback-toast {
  position: fixed; bottom: 24px; right: 24px;
  padding: 14px 20px; border-radius: 12px;
  font-size: 14px; font-weight: 600; z-index: 2000;
  animation: slideIn 0.3s ease;
  &.success { background: rgba(34,197,94,0.15); color: #22c55e; border: 1px solid rgba(34,197,94,0.3); }
  &.error { background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); }
  i { margin-right: 8px; }
}

@keyframes slideIn { from { transform: translateX(100px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
</style>
