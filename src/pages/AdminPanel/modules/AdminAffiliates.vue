<template>
  <div class="admin-affiliates">
    <!-- Filters -->
    <div class="filters-bar">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input v-model="search" placeholder="Buscar por nome ou email..." @input="debounceSearch" />
      </div>
      <select v-model="filterStatus" @change="loadAffiliates">
        <option value="">Todos</option>
        <option value="active">Ativos</option>
        <option value="inactive">Inativos</option>
      </select>
      <select v-model="filterTier" @change="loadAffiliates">
        <option value="">Todas as Ligas</option>
        <option value="bronze_1">Bronze I</option>
        <option value="bronze_2">Bronze II</option>
        <option value="prata_1">Prata I</option>
        <option value="prata_2">Prata II</option>
        <option value="ouro_1">Ouro I</option>
        <option value="ouro_2">Ouro II</option>
        <option value="diamante_1">Diamante I</option>
        <option value="diamante_2">Diamante II</option>
      </select>
    </div>

    <!-- Table -->
    <div class="table-wrapper">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Nome</th>
            <th>Email</th>
            <th>Liga</th>
            <th>Comissão</th>
            <th>Vendas</th>
            <th>Indicações</th>
            <th>Status</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="aff in affiliates" :key="aff.id" :class="{ inactive: !aff.is_active }">
            <td class="name-cell">{{ aff.first_name }} {{ aff.last_name }}</td>
            <td>{{ aff.email }}</td>
            <td><span class="tier-badge" :class="aff.tier">{{ tierLabel(aff.tier) }}</span></td>
            <td>{{ aff.commission_rate }}%</td>
            <td class="currency">{{ formatCurrency(aff.total_sales_amount) }}</td>
            <td>{{ aff.total_referrals || 0 }}</td>
            <td>
              <span class="status-dot" :class="aff.is_active ? 'active' : 'blocked'">
                {{ aff.is_active ? 'Ativo' : 'Bloqueado' }}
              </span>
            </td>
            <td class="actions">
              <button class="btn-icon" title="Ver detalhes" @click="viewDetail(aff)"><i class="fas fa-eye"></i></button>
              <button class="btn-icon" title="Editar liga" @click="openTierOverride(aff)"><i class="fas fa-medal"></i></button>
              <button class="btn-icon" :title="aff.is_active ? 'Bloquear' : 'Reativar'" @click="toggleAffiliate(aff)">
                <i :class="aff.is_active ? 'fas fa-ban' : 'fas fa-check-circle'"></i>
              </button>
            </td>
          </tr>
          <tr v-if="loadError">
            <td colspan="8" class="empty" style="color:#f44">
              <i class="fas fa-exclamation-circle"></i> {{ loadError }}
            </td>
          </tr>
          <tr v-else-if="!affiliates.length && !loading">
            <td colspan="8" class="empty">Nenhum afiliado encontrado</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination" v-if="pagination.pages > 1">
      <button :disabled="pagination.page <= 1" @click="goPage(pagination.page - 1)"><i class="fas fa-chevron-left"></i></button>
      <span>{{ pagination.page }} / {{ pagination.pages }}</span>
      <button :disabled="pagination.page >= pagination.pages" @click="goPage(pagination.page + 1)"><i class="fas fa-chevron-right"></i></button>
    </div>

    <!-- Modal: Detalhes do Afiliado -->
    <div class="modal-overlay" v-if="showDetail" @click.self="showDetail = false">
      <div class="modal">
        <div class="modal-header">
          <h3>{{ detail.affiliate?.first_name }} {{ detail.affiliate?.last_name }}</h3>
          <button class="modal-close" @click="showDetail = false"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
          <div class="detail-grid">
            <div><strong>Email:</strong> {{ detail.affiliate?.email }}</div>
            <div><strong>WhatsApp:</strong> {{ detail.affiliate?.whatsapp }}</div>
            <div><strong>PIX:</strong> {{ detail.affiliate?.pix_key || '—' }}</div>
            <div><strong>Hash:</strong> <code>{{ detail.affiliate?.affiliate_hash }}</code></div>
            <div><strong>Liga:</strong> {{ tierLabel(detail.affiliate?.tier) }} ({{ detail.affiliate?.commission_rate }}%)</div>
            <div><strong>Vendas:</strong> {{ formatCurrency(detail.affiliate?.total_sales_amount) }}</div>
            <div><strong>Cadastro:</strong> {{ formatDate(detail.affiliate?.created_at) }}</div>
            <div><strong>Último login:</strong> {{ formatDate(detail.affiliate?.last_login) || 'Nunca' }}</div>
          </div>

          <!-- Editar dados -->
          <div class="edit-section">
            <h4>Editar Dados</h4>
            <div class="edit-row">
              <input v-model="editForm.first_name" placeholder="Primeiro nome" />
              <input v-model="editForm.last_name" placeholder="Sobrenome" />
            </div>
            <div class="edit-row">
              <input v-model="editForm.whatsapp" placeholder="WhatsApp" />
              <input v-model="editForm.pix_key" placeholder="Chave PIX" />
            </div>
            <button class="btn-primary" @click="saveEdit" :disabled="saving">
              <i :class="saving ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i> Salvar Alterações
            </button>
          </div>

          <!-- Indicações -->
          <h4>Indicações ({{ detail.referrals?.length || 0 }})</h4>
          <div class="mini-table-wrapper">
            <table class="admin-table mini">
              <thead>
                <tr><th>Lead</th><th>Status</th><th>Valor</th><th>Comissão</th><th>Pago?</th></tr>
              </thead>
              <tbody>
                <tr v-for="ref in detail.referrals" :key="ref.id">
                  <td>{{ ref.lead_name || '—' }}</td>
                  <td><span class="status-tag" :class="ref.status">{{ ref.status }}</span></td>
                  <td>{{ formatCurrency(ref.sale_amount) }}</td>
                  <td>{{ formatCurrency(ref.commission_amount) }}</td>
                  <td>{{ ref.commission_paid ? '✅' : '⏳' }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Medalhas -->
          <h4>Medalhas ({{ detail.badges?.length || 0 }})</h4>
          <div class="badges-list">
            <span v-for="b in detail.badges" :key="b.title" class="badge-chip">
              {{ b.icon_emoji }} {{ b.title }}
            </span>
            <span v-if="!detail.badges?.length" class="empty-inline">Nenhuma medalha</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Override de Liga -->
    <div class="modal-overlay" v-if="showTierModal" @click.self="showTierModal = false">
      <div class="modal modal-sm">
        <div class="modal-header">
          <h3>Override de Liga — {{ tierTarget?.first_name }}</h3>
          <button class="modal-close" @click="showTierModal = false"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
          <p>Liga atual: <strong>{{ tierLabel(tierTarget?.tier) }}</strong></p>
          <label>Nova Liga:</label>
          <select v-model="tierOverride.tier" class="full-select">
            <option v-for="t in allTiers" :key="t.key" :value="t.key">{{ t.label }}</option>
          </select>
          <label>Motivo (opcional):</label>
          <input v-model="tierOverride.reason" placeholder="Ex: Parceria estratégica" class="full-input" />
          <button class="btn-primary" @click="submitTierOverride" :disabled="saving">
            <i :class="saving ? 'fas fa-spinner fa-spin' : 'fas fa-crown'"></i> Confirmar Override
          </button>
        </div>
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
  name: 'AdminAffiliates',
  data() {
    return {
      affiliates: [],
      pagination: { page: 1, pages: 1, total: 0 },
      search: '',
      filterStatus: '',
      filterTier: '',
      loading: false,
      loadError: '',
      saving: false,
      showDetail: false,
      detail: {},
      editForm: {},
      showTierModal: false,
      tierTarget: null,
      tierOverride: { tier: '', reason: '' },
      feedback: null,
      searchTimeout: null,
      allTiers: [
        { key: 'bronze_1', label: 'Bronze I — O Recruta' },
        { key: 'bronze_2', label: 'Bronze II — O Sobrevivente' },
        { key: 'prata_1', label: 'Prata I — O Especialista' },
        { key: 'prata_2', label: 'Prata II — O Estrategista' },
        { key: 'ouro_1', label: 'Ouro I — O Elite' },
        { key: 'ouro_2', label: 'Ouro II — O Influenciador' },
        { key: 'diamante_1', label: 'Diamante I — O Mestre' },
        { key: 'diamante_2', label: 'Diamante II — A Lenda' },
      ]
    };
  },
  async created() {
    await this.loadAffiliates();
  },
  methods: {
    async loadAffiliates() {
      this.loading = true;
      const { adminFetch } = useAdminAuth();
      const params = new URLSearchParams({
        action: 'list_affiliates',
        page: this.pagination.page,
        status: this.filterStatus,
        tier: this.filterTier,
        search: this.search
      });
      const res = await adminFetch(`/api/admin/panel-mgmt.php?${params}`);
      if (res.ok) {
        this.affiliates = res.data;
        this.pagination = res.pagination;
        this.loadError = '';
      } else {
        this.loadError = res.error || 'Erro ao carregar afiliados';
      }
      this.loading = false;
    },
    debounceSearch() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.pagination.page = 1;
        this.loadAffiliates();
      }, 400);
    },
    goPage(p) {
      this.pagination.page = p;
      this.loadAffiliates();
    },
    async viewDetail(aff) {
      const { adminFetch } = useAdminAuth();
      const res = await adminFetch(`/api/admin/panel-mgmt.php?action=get_affiliate&id=${aff.id}`);
      if (res.ok) {
        this.detail = res.data;
        this.editForm = {
          id: res.data.affiliate.id,
          first_name: res.data.affiliate.first_name,
          last_name: res.data.affiliate.last_name,
          whatsapp: res.data.affiliate.whatsapp,
          pix_key: res.data.affiliate.pix_key || ''
        };
        this.showDetail = true;
      }
    },
    async saveEdit() {
      this.saving = true;
      const { adminFetch } = useAdminAuth();
      const res = await adminFetch('/api/admin/panel-mgmt.php?action=update_affiliate', {
        method: 'POST',
        body: JSON.stringify(this.editForm)
      });
      this.saving = false;
      this.showFeedback(res.ok ? 'success' : 'error', res.message || res.error);
      if (res.ok) {
        this.showDetail = false;
        this.loadAffiliates();
      }
    },
    openTierOverride(aff) {
      this.tierTarget = aff;
      this.tierOverride = { tier: aff.tier, reason: '' };
      this.showTierModal = true;
    },
    async submitTierOverride() {
      this.saving = true;
      const { adminFetch } = useAdminAuth();
      const res = await adminFetch('/api/admin/panel-mgmt.php?action=override_tier', {
        method: 'POST',
        body: JSON.stringify({ id: this.tierTarget.id, ...this.tierOverride })
      });
      this.saving = false;
      this.showFeedback(res.ok ? 'success' : 'error', res.message || res.error);
      if (res.ok) {
        this.showTierModal = false;
        this.loadAffiliates();
      }
    },
    async toggleAffiliate(aff) {
      const action = aff.is_active ? 'bloquear' : 'reativar';
      if (!confirm(`Deseja ${action} o afiliado ${aff.first_name} ${aff.last_name}?`)) return;

      const { adminFetch } = useAdminAuth();
      const res = await adminFetch('/api/admin/panel-mgmt.php?action=toggle_affiliate', {
        method: 'POST',
        body: JSON.stringify({ id: aff.id })
      });
      this.showFeedback(res.ok ? 'success' : 'error', res.message || res.error);
      if (res.ok) this.loadAffiliates();
    },
    showFeedback(type, message) {
      this.feedback = { type, message };
      setTimeout(() => { this.feedback = null; }, 3500);
    },
    formatCurrency(v) {
      return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v || 0);
    },
    formatDate(d) {
      if (!d) return '';
      return new Date(d).toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    },
    tierLabel(tier) {
      const labels = { bronze_1:'Bronze I', bronze_2:'Bronze II', prata_1:'Prata I', prata_2:'Prata II', ouro_1:'Ouro I', ouro_2:'Ouro II', diamante_1:'Diamante I', diamante_2:'Diamante II' };
      return labels[tier] || tier;
    }
  }
};
</script>

<style lang="scss" scoped>
// Filters
.filters-bar {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;

  .search-box {
    flex: 1;
    min-width: 200px;
    position: relative;

    i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #64748b; }
    input {
      width: 100%;
      padding: 10px 12px 10px 40px;
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 10px;
      color: #f1f5f9;
      font-size: 14px;
      outline: none;
      &:focus { border-color: #f59e0b; }
    }
  }

  select {
    padding: 10px 14px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 10px;
    color: #f1f5f9;
    font-size: 13px;
    outline: none;
    cursor: pointer;
    &:focus { border-color: #f59e0b; }
  }
}

// Table
.table-wrapper { overflow-x: auto; border-radius: 12px; border: 1px solid rgba(255,255,255,0.06); }

.admin-table {
  width: 100%;
  border-collapse: collapse;

  th {
    background: rgba(255,255,255,0.04);
    padding: 12px 14px;
    text-align: left;
    font-size: 12px;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
  }

  td {
    padding: 12px 14px;
    font-size: 13px;
    border-top: 1px solid rgba(255,255,255,0.04);
  }

  tr.inactive td { opacity: 0.5; }
  .currency { color: #22c55e; font-weight: 600; }
  .empty { text-align: center; color: #64748b; padding: 24px; }

  &.mini {
    th, td { padding: 8px 10px; font-size: 12px; }
  }
}

.name-cell { font-weight: 600; color: #f1f5f9; }

.tier-badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;

  &.bronze_1, &.bronze_2 { background: rgba(205,127,50,0.2); color: #cd7f32; }
  &.prata_1, &.prata_2 { background: rgba(192,192,192,0.2); color: #c0c0c0; }
  &.ouro_1, &.ouro_2 { background: rgba(255,215,0,0.2); color: #ffd700; }
  &.diamante_1, &.diamante_2 { background: rgba(185,242,255,0.2); color: #b9f2ff; }
}

.status-dot {
  font-size: 12px;
  font-weight: 600;
  &.active { color: #22c55e; }
  &.blocked { color: #ef4444; }
}

.status-tag {
  font-size: 11px;
  padding: 2px 8px;
  border-radius: 8px;
  font-weight: 600;
  &.lead { background: rgba(99,102,241,0.2); color: #818cf8; }
  &.contacted { background: rgba(59,130,246,0.2); color: #60a5fa; }
  &.negotiating { background: rgba(245,158,11,0.2); color: #fbbf24; }
  &.closed, &.completed, &.onboarding { background: rgba(34,197,94,0.2); color: #4ade80; }
  &.lost { background: rgba(239,68,68,0.2); color: #f87171; }
}

.actions {
  display: flex;
  gap: 6px;
}

.btn-icon {
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 8px;
  color: #94a3b8;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 13px;
  transition: all 0.2s;

  &:hover { color: #f59e0b; border-color: #f59e0b; }
}

// Pagination
.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  margin-top: 20px;

  span { color: #94a3b8; font-size: 13px; }

  button {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    color: #f1f5f9;
    width: 36px;
    height: 36px;
    cursor: pointer;
    &:disabled { opacity: 0.3; cursor: not-allowed; }
    &:hover:not(:disabled) { border-color: #f59e0b; }
  }
}

// Modals
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal {
  background: #111827;
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 16px;
  width: 100%;
  max-width: 700px;
  max-height: 85vh;
  overflow-y: auto;

  &.modal-sm { max-width: 440px; }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid rgba(255,255,255,0.06);

  h3 { margin: 0; font-size: 18px; color: #f1f5f9; }

  .modal-close {
    background: none;
    border: none;
    color: #64748b;
    font-size: 18px;
    cursor: pointer;
    &:hover { color: #ef4444; }
  }
}

.modal-body {
  padding: 20px 24px;

  h4 {
    font-size: 14px;
    color: #f59e0b;
    margin: 20px 0 10px;
  }

  label {
    display: block;
    font-size: 13px;
    color: #94a3b8;
    margin: 12px 0 6px;
    font-weight: 600;
  }
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;

  div {
    font-size: 13px;
    color: #cbd5e1;

    strong { color: #94a3b8; margin-right: 4px; }
    code { background: rgba(255,255,255,0.06); padding: 2px 6px; border-radius: 4px; font-size: 12px; }
  }
}

.edit-section {
  background: rgba(255,255,255,0.02);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 12px;
  padding: 16px;
  margin-top: 16px;
}

.edit-row {
  display: flex;
  gap: 10px;
  margin-bottom: 10px;

  input {
    flex: 1;
    padding: 10px 12px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    color: #f1f5f9;
    font-size: 13px;
    outline: none;
    &:focus { border-color: #f59e0b; }
  }
}

.full-select, .full-input {
  width: 100%;
  padding: 10px 12px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 8px;
  color: #f1f5f9;
  font-size: 13px;
  outline: none;
  margin-bottom: 10px;
  &:focus { border-color: #f59e0b; }
}

.btn-primary {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: #0f172a;
  border: none;
  padding: 10px 20px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-top: 8px;

  &:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(245,158,11,0.3); }
  &:disabled { opacity: 0.6; cursor: not-allowed; }
}

.mini-table-wrapper { overflow-x: auto; max-height: 250px; overflow-y: auto; border-radius: 8px; border: 1px solid rgba(255,255,255,0.06); }

.badges-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.badge-chip {
  background: rgba(245,158,11,0.1);
  border: 1px solid rgba(245,158,11,0.2);
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  color: #f59e0b;
}

.empty-inline { color: #64748b; font-size: 13px; }

// Feedback Toast
.feedback-toast {
  position: fixed;
  bottom: 24px;
  right: 24px;
  padding: 14px 20px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  z-index: 2000;
  animation: slideIn 0.3s ease;

  &.success { background: rgba(34,197,94,0.15); color: #22c55e; border: 1px solid rgba(34,197,94,0.3); }
  &.error { background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); }

  i { margin-right: 8px; }
}

@keyframes slideIn {
  from { transform: translateX(100px); opacity: 0; }
  to { transform: translateX(0); opacity: 1; }
}

@media (max-width: 640px) {
  .detail-grid { grid-template-columns: 1fr; }
  .edit-row { flex-direction: column; }
}
</style>
