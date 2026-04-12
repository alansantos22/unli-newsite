<template>
  <div class="admin-webinar-leads">

    <!-- Stats Cards -->
    <div class="stats-grid" v-if="stats">
      <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div class="stat-data">
          <span class="stat-value">{{ stats.total }}</span>
          <span class="stat-label">Total de inscritos</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-calendar-day"></i></div>
        <div class="stat-data">
          <span class="stat-value">{{ stats.today }}</span>
          <span class="stat-label">Hoje</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon yellow"><i class="fas fa-calendar-week"></i></div>
        <div class="stat-data">
          <span class="stat-value">{{ stats.week }}</span>
          <span class="stat-label">Últimos 7 dias</span>
        </div>
      </div>
    </div>

    <!-- Filters + Export -->
    <div class="filters-bar">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input v-model="search" placeholder="Buscar por nome ou e-mail..." @input="debounceSearch" />
      </div>
      <select v-model="filterRamo" @change="loadLeads(1)">
        <option value="">Todos os ramos</option>
        <option v-for="r in ramoOptions" :key="r" :value="r">{{ r }}</option>
      </select>
      <button class="btn-export" @click="exportCSV" title="Exportar para Excel (CSV)">
        <i class="fas fa-file-excel"></i>
        Exportar Excel
      </button>
    </div>

    <!-- Table -->
    <div class="table-wrapper">
      <div v-if="loading" class="loading-state">
        <i class="fas fa-spinner fa-spin"></i> Carregando...
      </div>
      <table v-else class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Ramo</th>
            <th>Objetivo</th>
            <th>Localidade</th>
            <th>Inscrição</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="lead in leads" :key="lead.id">
            <td class="id-cell">{{ lead.id }}</td>
            <td class="name-cell">{{ lead.nome }}</td>
            <td>
              <a :href="`mailto:${lead.email}`" class="email-link">{{ lead.email }}</a>
            </td>
            <td><span class="ramo-badge">{{ lead.ramo }}</span></td>
            <td class="objetivo-cell">{{ lead.objetivo }}</td>
            <td class="loc-cell">
              <span v-if="lead.cidade">{{ lead.cidade }}{{ lead.estado ? ` / ${lead.estado}` : '' }}</span>
              <span v-else class="muted">—</span>
              <span class="country-flag">{{ flagFor(lead.pais) }}</span>
            </td>
            <td class="date-cell">{{ formatDate(lead.created_at) }}</td>
            <td>
              <button class="btn-icon" title="Ver detalhes" @click="openDetail(lead)">
                <i class="fas fa-eye"></i>
              </button>
            </td>
          </tr>
          <tr v-if="!leads.length && !loading">
            <td colspan="8" class="empty">Nenhum inscrito encontrado</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination" v-if="pagination.pages > 1">
      <button :disabled="pagination.page <= 1" @click="loadLeads(pagination.page - 1)">
        <i class="fas fa-chevron-left"></i>
      </button>
      <span>{{ pagination.page }} / {{ pagination.pages }} &nbsp;·&nbsp; {{ pagination.total }} inscritos</span>
      <button :disabled="pagination.page >= pagination.pages" @click="loadLeads(pagination.page + 1)">
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>

    <!-- Modal: Detalhes -->
    <div class="modal-overlay" v-if="showDetail" @click.self="showDetail = false">
      <div class="modal">
        <div class="modal-header">
          <h3><i class="fas fa-user"></i> {{ selected?.nome }}</h3>
          <button class="modal-close" @click="showDetail = false">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body" v-if="selected">
          <div class="detail-grid">
            <div><strong>E-mail:</strong> <a :href="`mailto:${selected.email}`">{{ selected.email }}</a></div>
            <div><strong>Ramo:</strong> {{ selected.ramo }}</div>
            <div><strong>Objetivo:</strong> {{ selected.objetivo }}</div>
            <div><strong>País:</strong> {{ flagFor(selected.pais) }} {{ selected.pais }}</div>
            <div v-if="selected.cidade"><strong>Cidade:</strong> {{ selected.cidade }}{{ selected.estado ? ` / ${selected.estado}` : '' }}</div>
            <div><strong>Inscrição:</strong> {{ formatDate(selected.created_at) }}</div>
          </div>
          <div v-if="selected.mensagem" class="mensagem-block">
            <strong>Mensagem:</strong>
            <p>{{ selected.mensagem }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Feedback toast -->
    <div v-if="feedback" class="feedback-toast" :class="feedback.type">
      <i :class="feedback.type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
      {{ feedback.message }}
    </div>
  </div>
</template>

<script>
import { useAdminAuth } from '@/core/composables/useAdminAuth';

const RAMO_OPTIONS = [
  'Agência de Marketing Digital',
  'Publicidade e Comunicação',
  'E-commerce e Varejo',
  'Tecnologia e SaaS',
  'Consultoria Empresarial',
  'Saúde e Bem-estar',
  'Educação e Treinamentos',
  'Imobiliário',
  'Financeiro e Contabilidade',
  'Jurídico',
  'Indústria e Manufatura',
  'Alimentação e Gastronomia',
  'Logística e Transporte',
  'Autônomo / Freelancer',
  'Outro',
];

const FLAG_MAP = {
  'Brasil': '🇧🇷',
  'Portugal': '🇵🇹',
  'Estados Unidos': '🇺🇸',
  'Argentina': '🇦🇷',
  'Colômbia': '🇨🇴',
  'México': '🇲🇽',
};

export default {
  name: 'AdminWebinarLeads',

  data() {
    return {
      leads: [],
      stats: null,
      pagination: { page: 1, pages: 1, total: 0 },
      search: '',
      filterRamo: '',
      loading: false,
      showDetail: false,
      selected: null,
      feedback: null,
      searchTimeout: null,
      ramoOptions: RAMO_OPTIONS,
    };
  },

  async created() {
    await Promise.all([this.loadLeads(1), this.loadStats()]);
  },

  methods: {
    async loadLeads(page = 1) {
      this.loading = true;
      const { adminFetch } = useAdminAuth();
      const params = new URLSearchParams({
        action: 'list',
        page,
        per_page: 25,
        search: this.search,
        ramo: this.filterRamo,
      });
      const res = await adminFetch(`/api/admin/webinar-leads.php?${params}`);
      if (res?.ok) {
        this.leads = res.leads;
        this.pagination = res.pagination;
      }
      this.loading = false;
    },

    async loadStats() {
      const { adminFetch } = useAdminAuth();
      const res = await adminFetch('/api/admin/webinar-leads.php?action=stats');
      if (res?.ok) this.stats = res;
    },

    debounceSearch() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => this.loadLeads(1), 400);
    },

    exportCSV() {
      const { getToken } = useAdminAuth();
      const token = getToken();
      const params = new URLSearchParams({
        action: 'export',
        search: this.search,
        ramo: this.filterRamo,
      });
      // Dispara download direto via link com token no header não é suportado nativamente.
      // Usamos fetch + blob para manter o JWT no Authorization header.
      fetch(`/api/admin/webinar-leads.php?${params}`, {
        headers: { Authorization: `Bearer ${token}` },
      })
        .then(r => r.blob())
        .then(blob => {
          const url = URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = `webinar-leads-${new Date().toISOString().slice(0, 10)}.csv`;
          a.click();
          URL.revokeObjectURL(url);
        })
        .catch(() => this.showFeedback('error', 'Erro ao exportar. Tente novamente.'));
    },

    openDetail(lead) {
      this.selected = lead;
      this.showDetail = true;
    },

    flagFor(pais) {
      return FLAG_MAP[pais] ?? '🌎';
    },

    formatDate(d) {
      if (!d) return '—';
      return new Date(d).toLocaleDateString('pt-BR', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
      });
    },

    showFeedback(type, message) {
      this.feedback = { type, message };
      setTimeout(() => { this.feedback = null; }, 3500);
    },
  },
};
</script>

<style lang="scss" scoped>
// Stats
.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 24px;

  @media (max-width: 768px) { grid-template-columns: 1fr; }
}

.stat-card {
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
}
.stat-icon {
  width: 44px; height: 44px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 18px;

  &.blue   { background: rgba(59,130,246,0.15); color: #60a5fa; }
  &.green  { background: rgba(34,197,94,0.15);  color: #4ade80; }
  &.yellow { background: rgba(245,158,11,0.15); color: #fbbf24; }
}
.stat-data { display: flex; flex-direction: column; }
.stat-value { font-size: 28px; font-weight: 700; color: #f1f5f9; line-height: 1; }
.stat-label { font-size: 12px; color: #64748b; margin-top: 4px; }

// Filters
.filters-bar {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
  align-items: center;

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
      &:focus { border-color: #4ade80; }
    }
  }

  select {
    padding: 10px 14px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 10px;
    color: #f1f5f9;
    font-size: 14px;
    outline: none;
    cursor: pointer;
    &:focus { border-color: #4ade80; }
    option { background: #1e293b; }
  }
}

.btn-export {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 18px;
  background: rgba(34,197,94,0.15);
  border: 1px solid rgba(34,197,94,0.3);
  border-radius: 10px;
  color: #4ade80;
  font-size: 14px; font-weight: 600;
  cursor: pointer;
  transition: background 0.2s, border-color 0.2s;
  white-space: nowrap;

  &:hover {
    background: rgba(34,197,94,0.25);
    border-color: rgba(34,197,94,0.5);
  }

  i { font-size: 15px; }
}

// Table
.table-wrapper {
  overflow-x: auto;
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 12px;
  background: rgba(255,255,255,0.02);
}

.loading-state {
  padding: 40px;
  text-align: center;
  color: #64748b;
  font-size: 14px;

  i { margin-right: 8px; }
}

.admin-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;

  th {
    padding: 12px 14px;
    text-align: left;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    border-bottom: 1px solid rgba(255,255,255,0.06);
  }

  td {
    padding: 12px 14px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    color: #cbd5e1;
    vertical-align: middle;

    &.id-cell    { color: #475569; font-size: 12px; }
    &.name-cell  { font-weight: 500; color: #f1f5f9; }
    &.objetivo-cell { max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    &.date-cell  { font-size: 12px; color: #94a3b8; white-space: nowrap; }
    &.loc-cell   { font-size: 12px; white-space: nowrap; }
  }

  tr:last-child td { border-bottom: none; }
  tr:hover td { background: rgba(255,255,255,0.02); }

  .empty { text-align: center; padding: 40px; color: #475569; }
}

.email-link {
  color: #60a5fa;
  text-decoration: none;
  &:hover { text-decoration: underline; }
}

.ramo-badge {
  display: inline-block;
  padding: 3px 10px;
  background: rgba(99,102,241,0.15);
  border: 1px solid rgba(99,102,241,0.25);
  border-radius: 20px;
  font-size: 12px;
  color: #a5b4fc;
  white-space: nowrap;
}

.country-flag { margin-left: 6px; font-size: 15px; }
.muted { color: #475569; }

// Pagination
.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  padding: 20px 0 4px;
  font-size: 13px;
  color: #94a3b8;

  button {
    width: 34px; height: 34px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    color: #94a3b8;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background 0.2s;

    &:hover:not(:disabled) { background: rgba(255,255,255,0.08); }
    &:disabled { opacity: 0.3; cursor: not-allowed; }
  }
}

// Modal
.modal-overlay {
  position: fixed; inset: 0; z-index: 1000;
  background: rgba(0,0,0,0.7);
  display: flex; align-items: center; justify-content: center;
  padding: 20px;
}
.modal {
  background: #111827;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px;
  width: 100%; max-width: 560px;
  max-height: 90vh; overflow-y: auto;
}
.modal-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid rgba(255,255,255,0.06);

  h3 { font-size: 16px; font-weight: 600; color: #f1f5f9; }
}
.modal-close {
  width: 32px; height: 32px;
  background: rgba(255,255,255,0.06);
  border: none; border-radius: 8px;
  color: #64748b; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  &:hover { background: rgba(255,255,255,0.1); color: #f1f5f9; }
}
.modal-body {
  padding: 24px;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px 20px;
  margin-bottom: 20px;
  font-size: 14px;

  div { color: #94a3b8; }
  strong { color: #cbd5e1; }
  a    { color: #60a5fa; text-decoration: none; &:hover { text-decoration: underline; } }

  @media (max-width: 500px) { grid-template-columns: 1fr; }
}

.mensagem-block {
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 10px;
  padding: 14px 16px;
  font-size: 14px;

  strong { color: #cbd5e1; display: block; margin-bottom: 8px; }
  p { color: #94a3b8; line-height: 1.6; }
}

// Feedback toast
.feedback-toast {
  position: fixed;
  bottom: 28px; right: 28px; z-index: 9999;
  display: flex; align-items: center; gap: 10px;
  padding: 14px 20px;
  border-radius: 10px;
  font-size: 14px; font-weight: 500;
  box-shadow: 0 8px 24px rgba(0,0,0,0.3);

  &.success { background: #14532d; border: 1px solid #16a34a; color: #4ade80; }
  &.error   { background: #450a0a; border: 1px solid #dc2626; color: #f87171; }
}

.btn-icon {
  width: 32px; height: 32px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 8px;
  color: #94a3b8; cursor: pointer;
  display: inline-flex; align-items: center; justify-content: center;
  transition: background 0.2s, color 0.2s;
  font-size: 13px;

  &:hover { background: rgba(255,255,255,0.1); color: #f1f5f9; }
}
</style>
