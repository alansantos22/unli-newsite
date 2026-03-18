<template>
  <div class="admin-sdrs">
    <!-- Actions Bar -->
    <div class="actions-bar">
      <button class="btn-primary" @click="showCreateModal = true">
        <i class="fas fa-plus"></i> Novo SDR
      </button>
    </div>

    <!-- Table -->
    <div class="table-wrapper">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Nome</th>
            <th>Email</th>
            <th>WhatsApp</th>
            <th>Vendas</th>
            <th>Receita</th>
            <th>Status</th>
            <th>Último Login</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="sdr in sdrs" :key="sdr.id" :class="{ inactive: !sdr.is_active }">
            <td class="name-cell">{{ sdr.name }}</td>
            <td>{{ sdr.email }}</td>
            <td>{{ sdr.whatsapp || '—' }}</td>
            <td>{{ sdr.total_sales || 0 }}</td>
            <td class="currency">{{ formatCurrency(sdr.total_revenue) }}</td>
            <td>
              <span class="status-dot" :class="sdr.is_active ? 'active' : 'blocked'">
                {{ sdr.is_active ? 'Ativo' : 'Desativado' }}
              </span>
            </td>
            <td>{{ formatDate(sdr.last_login) || 'Nunca' }}</td>
            <td class="actions">
              <button class="btn-icon" title="Editar" @click="openEdit(sdr)"><i class="fas fa-pen"></i></button>
              <button class="btn-icon" title="Resetar Senha" @click="openResetPassword(sdr)"><i class="fas fa-key"></i></button>
              <button class="btn-icon" :title="sdr.is_active ? 'Desativar' : 'Reativar'" @click="toggleSdr(sdr)">
                <i :class="sdr.is_active ? 'fas fa-user-slash' : 'fas fa-user-check'"></i>
              </button>
            </td>
          </tr>
          <tr v-if="!sdrs.length">
            <td colspan="8" class="empty">Nenhum SDR cadastrado</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal: Criar SDR -->
    <div class="modal-overlay" v-if="showCreateModal" @click.self="showCreateModal = false">
      <div class="modal modal-sm">
        <div class="modal-header">
          <h3>Novo SDR</h3>
          <button class="modal-close" @click="showCreateModal = false"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
          <label>Nome completo</label>
          <input v-model="createForm.name" placeholder="Nome do SDR" class="full-input" />
          <label>Email</label>
          <input v-model="createForm.email" type="email" placeholder="email@unli.com.br" class="full-input" />
          <label>Senha (min 8 caracteres)</label>
          <input v-model="createForm.password" type="password" placeholder="Senha inicial" class="full-input" />
          <label>WhatsApp (opcional)</label>
          <input v-model="createForm.whatsapp" placeholder="5511999999999" class="full-input" />
          <p v-if="createError" class="error-msg"><i class="fas fa-exclamation-circle"></i> {{ createError }}</p>
          <button class="btn-primary" @click="submitCreate" :disabled="saving">
            <i :class="saving ? 'fas fa-spinner fa-spin' : 'fas fa-user-plus'"></i> Criar SDR
          </button>
        </div>
      </div>
    </div>

    <!-- Modal: Editar SDR -->
    <div class="modal-overlay" v-if="showEditModal" @click.self="showEditModal = false">
      <div class="modal modal-sm">
        <div class="modal-header">
          <h3>Editar SDR — {{ editForm.name }}</h3>
          <button class="modal-close" @click="showEditModal = false"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
          <label>Nome</label>
          <input v-model="editForm.name" class="full-input" />
          <label>Email</label>
          <input v-model="editForm.email" type="email" class="full-input" />
          <label>WhatsApp</label>
          <input v-model="editForm.whatsapp" class="full-input" />
          <button class="btn-primary" @click="submitEdit" :disabled="saving">
            <i :class="saving ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i> Salvar
          </button>
        </div>
      </div>
    </div>

    <!-- Modal: Resetar Senha -->
    <div class="modal-overlay" v-if="showPasswordModal" @click.self="showPasswordModal = false">
      <div class="modal modal-sm">
        <div class="modal-header">
          <h3>Resetar Senha — {{ passwordTarget?.name }}</h3>
          <button class="modal-close" @click="showPasswordModal = false"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
          <label>Nova senha (min 8 caracteres)</label>
          <input v-model="newPassword" type="password" placeholder="Nova senha" class="full-input" />
          <button class="btn-primary" @click="submitResetPassword" :disabled="saving">
            <i :class="saving ? 'fas fa-spinner fa-spin' : 'fas fa-key'"></i> Confirmar Reset
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
  name: 'AdminSDRs',
  data() {
    return {
      sdrs: [],
      loading: false,
      saving: false,
      feedback: null,
      showCreateModal: false,
      createForm: { name: '', email: '', password: '', whatsapp: '' },
      createError: '',
      showEditModal: false,
      editForm: { id: 0, name: '', email: '', whatsapp: '' },
      showPasswordModal: false,
      passwordTarget: null,
      newPassword: ''
    };
  },
  async created() {
    await this.loadSDRs();
  },
  methods: {
    async loadSDRs() {
      this.loading = true;
      const { adminFetch } = useAdminAuth();
      const res = await adminFetch('/api/admin/users.php?action=list_sdrs');
      if (res.ok) this.sdrs = res.data;
      this.loading = false;
    },
    async submitCreate() {
      this.createError = '';
      if (!this.createForm.name || !this.createForm.email || !this.createForm.password) {
        this.createError = 'Preencha nome, email e senha';
        return;
      }
      if (this.createForm.password.length < 8) {
        this.createError = 'Senha deve ter no mínimo 8 caracteres';
        return;
      }
      this.saving = true;
      const { adminFetch } = useAdminAuth();
      const res = await adminFetch('/api/admin/users.php?action=create_sdr', {
        method: 'POST',
        body: JSON.stringify(this.createForm)
      });
      this.saving = false;
      if (res.ok) {
        this.showCreateModal = false;
        this.createForm = { name: '', email: '', password: '', whatsapp: '' };
        this.showFeedback('success', res.message);
        this.loadSDRs();
      } else {
        this.createError = res.error;
      }
    },
    openEdit(sdr) {
      this.editForm = { id: sdr.id, name: sdr.name, email: sdr.email, whatsapp: sdr.whatsapp || '' };
      this.showEditModal = true;
    },
    async submitEdit() {
      this.saving = true;
      const { adminFetch } = useAdminAuth();
      const res = await adminFetch('/api/admin/users.php?action=update_sdr', {
        method: 'POST',
        body: JSON.stringify(this.editForm)
      });
      this.saving = false;
      this.showFeedback(res.ok ? 'success' : 'error', res.message || res.error);
      if (res.ok) { this.showEditModal = false; this.loadSDRs(); }
    },
    openResetPassword(sdr) {
      this.passwordTarget = sdr;
      this.newPassword = '';
      this.showPasswordModal = true;
    },
    async submitResetPassword() {
      if (this.newPassword.length < 8) {
        this.showFeedback('error', 'Senha deve ter no mínimo 8 caracteres');
        return;
      }
      this.saving = true;
      const { adminFetch } = useAdminAuth();
      const res = await adminFetch('/api/admin/users.php?action=reset_sdr_password', {
        method: 'POST',
        body: JSON.stringify({ id: this.passwordTarget.id, new_password: this.newPassword })
      });
      this.saving = false;
      this.showFeedback(res.ok ? 'success' : 'error', res.message || res.error);
      if (res.ok) this.showPasswordModal = false;
    },
    async toggleSdr(sdr) {
      const action = sdr.is_active ? 'desativar' : 'reativar';
      if (!confirm(`Deseja ${action} o SDR ${sdr.name}?`)) return;

      const { adminFetch } = useAdminAuth();
      const res = await adminFetch('/api/admin/users.php?action=toggle_sdr', {
        method: 'POST',
        body: JSON.stringify({ id: sdr.id })
      });
      this.showFeedback(res.ok ? 'success' : 'error', res.message || res.error);
      if (res.ok) this.loadSDRs();
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
    }
  }
};
</script>

<style lang="scss" scoped>
.actions-bar {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 20px;
}

.table-wrapper { overflow-x: auto; border-radius: 12px; border: 1px solid rgba(255,255,255,0.06); }

.admin-table {
  width: 100%;
  border-collapse: collapse;
  th {
    background: rgba(255,255,255,0.04);
    padding: 12px 14px; text-align: left;
    font-size: 12px; color: #94a3b8;
    text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;
  }
  td { padding: 12px 14px; font-size: 13px; border-top: 1px solid rgba(255,255,255,0.04); }
  tr.inactive td { opacity: 0.5; }
  .currency { color: #22c55e; font-weight: 600; }
  .empty { text-align: center; color: #64748b; padding: 24px; }
}

.name-cell { font-weight: 600; color: #f1f5f9; }
.status-dot { font-size: 12px; font-weight: 600; &.active { color: #22c55e; } &.blocked { color: #ef4444; } }
.actions { display: flex; gap: 6px; }

.btn-icon {
  background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 8px; color: #94a3b8; width: 32px; height: 32px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; font-size: 13px; transition: all 0.2s;
  &:hover { color: #f59e0b; border-color: #f59e0b; }
}

.btn-primary {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: #0f172a; border: none; padding: 10px 20px; border-radius: 10px;
  font-weight: 700; font-size: 13px; cursor: pointer;
  display: inline-flex; align-items: center; gap: 6px;
  &:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(245,158,11,0.3); }
  &:disabled { opacity: 0.6; cursor: not-allowed; }
}

// Modal
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.7);
  display: flex; align-items: center; justify-content: center;
  z-index: 1000; padding: 20px;
}

.modal {
  background: #111827; border: 1px solid rgba(255,255,255,0.1);
  border-radius: 16px; width: 100%; max-height: 85vh; overflow-y: auto;
  &.modal-sm { max-width: 440px; }
}

.modal-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 20px 24px; border-bottom: 1px solid rgba(255,255,255,0.06);
  h3 { margin: 0; font-size: 18px; color: #f1f5f9; }
  .modal-close { background: none; border: none; color: #64748b; font-size: 18px; cursor: pointer; &:hover { color: #ef4444; } }
}

.modal-body {
  padding: 20px 24px;
  label { display: block; font-size: 13px; color: #94a3b8; margin: 12px 0 6px; font-weight: 600; }
}

.full-input {
  width: 100%; padding: 10px 12px;
  background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 8px; color: #f1f5f9; font-size: 13px; outline: none; margin-bottom: 4px;
  &:focus { border-color: #f59e0b; }
}

.error-msg { color: #ef4444; font-size: 13px; margin: 8px 0; i { margin-right: 4px; } }

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
