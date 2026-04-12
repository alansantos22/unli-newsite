<template>
  <div class="admin-clients">
    <!-- Filters -->
    <div class="filters-bar">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input v-model="search" placeholder="Buscar por nome, empresa ou email..." @input="debouncedLoad" />
      </div>
    </div>

    <!-- Bulk Action Bar -->
    <div class="bulk-bar" v-if="selected.length > 0">
      <span class="bulk-count"><i class="fas fa-check-square"></i> {{ selected.length }} selecionado{{ selected.length > 1 ? 's' : '' }}</span>
      <button class="btn-delete-bulk" @click="confirmDelete()">
        <i class="fas fa-trash"></i> Excluir selecionados
      </button>
      <button class="btn-clear" @click="selected = []">
        <i class="fas fa-times"></i> Limpar seleção
      </button>
    </div>

    <!-- Table -->
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th class="check-col">
              <input type="checkbox" :checked="allChecked" :indeterminate.prop="someChecked" @change="toggleAll" />
            </th>
            <th>ID</th>
            <th>Cliente</th>
            <th>Empresa</th>
            <th>Email</th>
            <th>Status</th>
            <th>Valor</th>
            <th>Afiliado</th>
            <th>SDR</th>
            <th>Data</th>
            <th class="action-col"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in clients" :key="c.id" :class="{ 'row-selected': selected.includes(c.id) }">
            <td class="check-col">
              <input type="checkbox" :value="c.id" v-model="selected" />
            </td>
            <td class="id-col">#{{ c.id }}</td>
            <td class="name-col">{{ c.customer_name || '—' }}</td>
            <td>{{ c.company_name || '—' }}</td>
            <td>{{ c.customer_email || '—' }}</td>
            <td>
              <span class="status-pill" :class="statusClass(c.payment_status)">{{ statusLabel(c.payment_status) }}</span>
            </td>
            <td class="money-col">R$ {{ formatMoney(c.total_amount) }}</td>
            <td>{{ c.affiliate_name || '—' }}</td>
            <td>{{ c.sdr_name || '—' }}</td>
            <td class="date-col">{{ formatDate(c.created_at) }}</td>
            <td class="action-col">
              <button class="btn-delete-row" @click="confirmDelete(c)" title="Excluir">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
          <tr v-if="loadError">
            <td colspan="11" class="empty-state" style="color:#f44">
              <i class="fas fa-exclamation-circle"></i> {{ loadError }}
            </td>
          </tr>
          <tr v-else-if="!clients.length">
            <td colspan="11" class="empty-state">Nenhum cliente/lead encontrado</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination" v-if="totalPages > 1">
      <button :disabled="page <= 1" @click="page--; loadClients()">
        <i class="fas fa-chevron-left"></i>
      </button>
      <span>{{ page }} / {{ totalPages }}</span>
      <button :disabled="page >= totalPages" @click="page++; loadClients()">
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>

    <!-- KPIs -->
    <div class="kpi-row">
      <div class="kpi-card">
        <span class="kpi-label">Total de pedidos</span>
        <span class="kpi-value">{{ total }}</span>
      </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div class="modal-overlay" v-if="deleteModal.show" @click.self="deleteModal.show = false">
      <div class="modal-box">
        <div class="modal-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <h3>Confirmar exclusão</h3>
        <p v-if="deleteModal.single">
          Excluir o pedido <strong>#{{ deleteModal.single.id }}</strong> de <strong>{{ deleteModal.single.customer_name || 'cliente s/nome' }}</strong>?
        </p>
        <p v-else>
          Excluir <strong>{{ deleteModal.ids.length }} pedido{{ deleteModal.ids.length > 1 ? 's' : '' }}</strong> permanentemente?
        </p>
        <p class="modal-warning">Esta ação não pode ser desfeita.</p>
        <div class="modal-actions">
          <button class="btn-cancel" @click="deleteModal.show = false" :disabled="deleting">Cancelar</button>
          <button class="btn-confirm" @click="executeDelete" :disabled="deleting">
            <i class="fas fa-spinner fa-spin" v-if="deleting"></i>
            <i class="fas fa-trash" v-else></i>
            {{ deleting ? 'Excluindo...' : 'Excluir' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useAdminAuth } from '@/core/composables/useAdminAuth';

export default {
  name: 'AdminClients',
  data() {
    return {
      clients: [],
      page: 1,
      perPage: 20,
      total: 0,
      totalPages: 1,
      search: '',
      debounceTimer: null,
      loadError: '',
      selected: [],
      deleting: false,
      deleteModal: {
        show: false,
        single: null,
        ids: []
      }
    };
  },
  computed: {
    allChecked() {
      return this.clients.length > 0 && this.clients.every(c => this.selected.includes(c.id));
    },
    someChecked() {
      return this.selected.length > 0 && !this.allChecked;
    }
  },
  async created() {
    await this.loadClients();
  },
  methods: {
    debouncedLoad() {
      clearTimeout(this.debounceTimer);
      this.debounceTimer = setTimeout(() => { this.page = 1; this.loadClients(); }, 350);
    },
    async loadClients() {
      const { adminFetch } = useAdminAuth();
      const params = new URLSearchParams({
        action: 'list_clients',
        page: this.page,
        per_page: this.perPage
      });
      if (this.search) params.set('search', this.search);
      const res = await adminFetch(`/api/admin/panel-mgmt.php?${params}`);
      if (res.ok) {
        this.clients = res.data;
        this.total = res.total || res.pagination?.total || 0;
        this.totalPages = Math.ceil(this.total / this.perPage) || 1;
        this.loadError = '';
        // Limpar seleções que saíram da página
        const visibleIds = this.clients.map(c => c.id);
        this.selected = this.selected.filter(id => visibleIds.includes(id));
      } else {
        this.loadError = res.error || 'Erro ao carregar clientes';
      }
    },
    toggleAll(e) {
      if (e.target.checked) {
        this.clients.forEach(c => { if (!this.selected.includes(c.id)) this.selected.push(c.id); });
      } else {
        const visibleIds = this.clients.map(c => c.id);
        this.selected = this.selected.filter(id => !visibleIds.includes(id));
      }
    },
    confirmDelete(client = null) {
      if (client) {
        this.deleteModal = { show: true, single: client, ids: [client.id] };
      } else {
        this.deleteModal = { show: true, single: null, ids: [...this.selected] };
      }
    },
    async executeDelete() {
      this.deleting = true;
      const { adminFetch } = useAdminAuth();
      const res = await adminFetch('/api/admin/panel-mgmt.php?action=delete_clients', {
        method: 'POST',
        body: JSON.stringify({ ids: this.deleteModal.ids })
      });
      this.deleting = false;
      if (res.ok) {
        this.deleteModal.show = false;
        this.selected = this.selected.filter(id => !this.deleteModal.ids.includes(id));
        this.deleteModal.ids = [];
        await this.loadClients();
      } else {
        alert(res.error || 'Erro ao excluir clientes');
      }
    },
    formatMoney(val) {
      return Number(val || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    },
    formatDate(d) {
      if (!d) return '—';
      return new Date(d).toLocaleDateString('pt-BR');
    },
    statusClass(s) {
      const map = { paid: 'paid', approved: 'paid', pending: 'pending', refused: 'refused', refunded: 'refused' };
      return map[s] || 'pending';
    },
    statusLabel(s) {
      const labels = { paid: 'Pago', approved: 'Aprovado', pending: 'Pendente', refused: 'Recusado', refunded: 'Reembolsado' };
      return labels[s] || s || '—';
    }
  }
};
</script>

<style lang="scss" scoped>
.filters-bar {
  display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;
}
.search-box {
  flex: 1; min-width: 260px; position: relative;
  i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 13px; }
  input {
    width: 100%; padding: 10px 12px 10px 36px;
    background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);
    border-radius: 10px; color: #f1f5f9; font-size: 13px; outline: none;
    &:focus { border-color: #f59e0b; }
  }
}

.bulk-bar {
  display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
  background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.25);
  border-radius: 10px; padding: 10px 16px; margin-bottom: 16px;
  .bulk-count { color: #f1f5f9; font-size: 13px; font-weight: 600; flex: 1; }
  .btn-delete-bulk {
    background: #ef4444; color: #fff; border: none; border-radius: 8px;
    padding: 7px 16px; font-size: 13px; font-weight: 600; cursor: pointer;
    display: flex; align-items: center; gap: 6px; transition: background 0.2s;
    &:hover { background: #dc2626; }
  }
  .btn-clear {
    background: transparent; color: #94a3b8; border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px; padding: 7px 14px; font-size: 13px; cursor: pointer;
    display: flex; align-items: center; gap: 6px; transition: all 0.2s;
    &:hover { color: #f1f5f9; border-color: rgba(255,255,255,0.3); }
  }
}

.table-wrap {
  overflow-x: auto; border-radius: 12px;
  border: 1px solid rgba(255,255,255,0.06);
}
table {
  width: 100%; border-collapse: collapse;
  th, td { padding: 12px 14px; text-align: left; font-size: 13px; white-space: nowrap; }
  thead th { background: rgba(255,255,255,0.03); color: #64748b; font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
  tbody tr { border-bottom: 1px solid rgba(255,255,255,0.04); transition: background 0.15s;
    &:hover { background: rgba(255,255,255,0.02); }
    &.row-selected { background: rgba(239,68,68,0.06); }
  }
  td { color: #cbd5e1; }
}
.check-col { width: 40px; padding-left: 16px !important;
  input[type="checkbox"] { cursor: pointer; accent-color: #ef4444; width: 15px; height: 15px; }
}
.action-col { width: 48px; text-align: center !important; }
.id-col { color: #64748b; }
.name-col { color: #f1f5f9; font-weight: 600; }
.money-col { color: #22c55e; font-weight: 600; }
.date-col { color: #64748b; }

.btn-delete-row {
  background: transparent; border: none; color: #64748b; cursor: pointer;
  padding: 6px 8px; border-radius: 6px; font-size: 13px; transition: all 0.2s;
  &:hover { color: #ef4444; background: rgba(239,68,68,0.1); }
}

.status-pill {
  font-size: 11px; padding: 3px 10px; border-radius: 20px; font-weight: 600;
  &.paid { background: rgba(34,197,94,0.15); color: #22c55e; }
  &.pending { background: rgba(245,158,11,0.15); color: #f59e0b; }
  &.refused { background: rgba(239,68,68,0.15); color: #ef4444; }
}

.empty-state { text-align: center; color: #64748b; padding: 40px !important; }

.pagination {
  display: flex; align-items: center; justify-content: center; gap: 16px; margin-top: 20px;
  span { color: #94a3b8; font-size: 13px; font-weight: 600; }
  button {
    background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px; color: #94a3b8; width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 13px; transition: all 0.2s;
    &:hover:not(:disabled) { color: #f59e0b; border-color: #f59e0b; }
    &:disabled { opacity: 0.3; cursor: not-allowed; }
  }
}

.kpi-row {
  display: flex; gap: 12px; margin-top: 20px;
}
.kpi-card {
  background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);
  border-radius: 12px; padding: 16px 20px; display: flex; flex-direction: column; gap: 4px;
  .kpi-label { font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; }
  .kpi-value { font-size: 22px; color: #f1f5f9; font-weight: 700; }
}

// Modal
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 1000;
  display: flex; align-items: center; justify-content: center; padding: 20px;
}
.modal-box {
  background: #1e293b; border: 1px solid rgba(255,255,255,0.1);
  border-radius: 16px; padding: 32px 28px; max-width: 440px; width: 100%;
  text-align: center;
  .modal-icon { font-size: 2.5rem; color: #f59e0b; margin-bottom: 12px; }
  h3 { font-size: 1.2rem; color: #f1f5f9; margin-bottom: 12px; font-weight: 700; }
  p { color: #94a3b8; font-size: 14px; margin-bottom: 8px; line-height: 1.5;
    strong { color: #f1f5f9; }
  }
  .modal-warning { color: #ef4444; font-size: 12px; font-weight: 600; }
  .modal-actions {
    display: flex; gap: 12px; margin-top: 24px; justify-content: center;
    .btn-cancel {
      flex: 1; padding: 10px; border-radius: 10px; font-size: 14px; font-weight: 600;
      background: transparent; color: #94a3b8; border: 1px solid rgba(255,255,255,0.1); cursor: pointer;
      &:hover:not(:disabled) { color: #f1f5f9; }
      &:disabled { opacity: 0.5; }
    }
    .btn-confirm {
      flex: 1; padding: 10px; border-radius: 10px; font-size: 14px; font-weight: 600;
      background: #ef4444; color: #fff; border: none; cursor: pointer;
      display: flex; align-items: center; justify-content: center; gap: 6px;
      &:hover:not(:disabled) { background: #dc2626; }
      &:disabled { opacity: 0.6; cursor: not-allowed; }
    }
  }
}
</style>
