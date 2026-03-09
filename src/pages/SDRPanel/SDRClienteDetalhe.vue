<template>
  <div class="sdr-cliente-detalhe">
    <header class="page-header">
      <router-link to="/sdr/clientes" class="btn-back">
        <i class="fas fa-arrow-left"></i>
      </router-link>
      <h1>Detalhe do Cliente</h1>
    </header>

    <div v-if="loading" class="loading-state">
      <i class="fas fa-spinner fa-spin"></i> Carregando...
    </div>

    <div v-else-if="!client" class="empty-state">
      <p>Cliente não encontrado.</p>
    </div>

    <main v-else class="detail-main">
      <!-- Info Card -->
      <section class="info-card">
        <div class="info-header">
          <div>
            <h2>{{ client.customer_name }}</h2>
            <p class="company">{{ client.company_name || 'Empresa não informada' }}</p>
          </div>
          <span class="badge-lg" :class="client.payment_status">
            {{ client.payment_status === 'paid' ? '✅ Pago' : '⏳ Pendente' }}
          </span>
        </div>

        <div class="info-grid">
          <div class="info-item">
            <i class="fas fa-envelope"></i>
            <span>{{ client.email }}</span>
          </div>
          <div class="info-item">
            <i class="fab fa-whatsapp"></i>
            <a v-if="client.phone" :href="`https://wa.me/${cleanPhone(client.phone)}`" target="_blank" rel="noopener">
              {{ client.phone }}
            </a>
            <span v-else>Não informado</span>
          </div>
          <div class="info-item">
            <i class="fas fa-calendar"></i>
            <span>{{ formatDateTime(client.created_at) }}</span>
          </div>
          <div class="info-item" v-if="client.total_amount">
            <i class="fas fa-dollar-sign"></i>
            <span>{{ formatCurrency(client.total_amount) }}</span>
          </div>
        </div>
      </section>

      <!-- Ações -->
      <section class="actions-section">
        <h3>Ações</h3>
        <div class="actions-grid">
          <!-- Marcar como Pago -->
          <button
            v-if="client.payment_status !== 'paid'"
            class="action-btn green"
            @click="markAsPaid"
            :disabled="actionLoading"
          >
            <i class="fas fa-check-circle"></i>
            Marcar como Pago
          </button>

          <!-- Enviar Email -->
          <button
            v-if="client.payment_status === 'paid'"
            class="action-btn purple"
            @click="sendOnboardingEmail"
            :disabled="actionLoading"
          >
            <i class="fas fa-envelope"></i>
            Enviar E-mail de Onboarding
            <small v-if="client.onboarding_email_sent_at">(reenviado)</small>
          </button>

          <!-- Copiar Link -->
          <button
            v-if="client.onboarding_link"
            class="action-btn outline"
            @click="copyLink(client.onboarding_link)"
          >
            <i :class="copied ? 'fas fa-check' : 'fas fa-link'"></i>
            {{ copied ? 'Copiado!' : 'Copiar Link de Onboarding' }}
          </button>

          <!-- Abrir WhatsApp -->
          <a
            v-if="client.phone"
            :href="whatsappLink"
            target="_blank"
            rel="noopener"
            class="action-btn whatsapp"
          >
            <i class="fab fa-whatsapp"></i>
            Abrir WhatsApp
          </a>
        </div>

        <p v-if="actionMessage" class="action-message" :class="actionMessageType">
          <i :class="actionMessageType === 'success' ? 'fas fa-check' : 'fas fa-exclamation-circle'"></i>
          {{ actionMessage }}
        </p>
      </section>

      <!-- Link de Onboarding -->
      <section v-if="client.onboarding_link" class="link-section">
        <h3>Link de Onboarding</h3>
        <div class="link-display">
          <input type="text" :value="client.onboarding_link" readonly ref="onboardingLinkInput" />
          <button @click="copyLink(client.onboarding_link)" class="btn-copy-sm">
            <i class="fas fa-copy"></i>
          </button>
        </div>
        <div class="status-row">
          <span>Status onboarding:</span>
          <span class="badge onb" :class="client.onboarding_status">
            {{ onboardingLabel(client.onboarding_status) }}
          </span>
        </div>
        <div class="status-row" v-if="client.onboarding_email_sent_at">
          <span>E-mail enviado em:</span>
          <span>{{ formatDateTime(client.onboarding_email_sent_at) }}</span>
        </div>
      </section>

      <!-- Detalhes do Pedido -->
      <section v-if="orderDetails" class="order-section">
        <h3>Detalhes do Pedido</h3>
        <div class="order-info">
          <div class="order-line" v-if="orderDetails.product">
            <span>Produto:</span>
            <span>{{ productLabel(orderDetails.product) }}</span>
          </div>
          <div class="order-line" v-if="orderDetails.selection && orderDetails.selection.pages">
            <span>Páginas:</span>
            <span>{{ Object.keys(orderDetails.selection.pages).join(', ') || 'Nenhuma' }}</span>
          </div>
          <div class="order-line" v-if="orderDetails.pricing">
            <span>À vista:</span>
            <span>{{ formatCurrency(orderDetails.pricing.avista) }}</span>
          </div>
          <div class="order-line" v-if="orderDetails.pricing">
            <span>12x:</span>
            <span>{{ formatCurrency(orderDetails.pricing.parcela_12) }}/mês</span>
          </div>
          <div class="order-line" v-if="client.payment_method_manual">
            <span>Método:</span>
            <span>{{ client.payment_method_manual }}</span>
          </div>
        </div>
      </section>

      <!-- Notas do SDR -->
      <section class="notes-section">
        <h3>Observações</h3>
        <textarea
          v-model="notes"
          placeholder="Anotações sobre o cliente..."
          rows="4"
          @blur="saveNotes"
        ></textarea>
        <small v-if="notesSaved" class="saved-indicator">
          <i class="fas fa-check"></i> Salvo
        </small>
      </section>
    </main>
  </div>
</template>

<script>
import { useSDRAuth } from '@/core/composables/useSDRAuth';

export default {
  name: 'SDRClienteDetalhe',
  data() {
    return {
      client: null,
      loading: true,
      actionLoading: false,
      actionMessage: '',
      actionMessageType: 'success',
      copied: false,
      notes: '',
      notesSaved: false,
      notesSaveTimeout: null
    };
  },
  computed: {
    orderDetails() {
      return this.client?.order_details || null;
    },
    whatsappLink() {
      if (!this.client?.phone) return '#';
      const phone = this.cleanPhone(this.client.phone);
      const msg = encodeURIComponent(
        `Olá ${this.client.customer_name}! Sou da Unli, estou entrando em contato sobre seu site.`
      );
      return `https://wa.me/${phone}?text=${msg}`;
    }
  },
  async mounted() {
    await this.loadClient();
  },
  methods: {
    async loadClient() {
      this.loading = true;
      try {
        const { authHeaders } = useSDRAuth();
        const id = this.$route.params.id;
        const res = await fetch(`/api/sdr/clients.php?id=${id}`, { headers: authHeaders() });
        const data = await res.json();
        if (data.ok) {
          this.client = data.client;
          this.notes = data.client.sdr_notes || '';
        }
      } catch (e) {
        console.error('Erro:', e);
      } finally {
        this.loading = false;
      }
    },

    async markAsPaid() {
      this.actionLoading = true;
      this.actionMessage = '';
      try {
        const { authHeaders } = useSDRAuth();
        const res = await fetch('/api/sdr/sale.php?action=mark_paid', {
          method: 'POST',
          headers: authHeaders(),
          body: JSON.stringify({
            order_id: this.client.id,
            payment_method_manual: this.client.payment_method_manual
          })
        });
        const data = await res.json();
        if (data.ok) {
          this.client.payment_status = 'paid';
          if (data.onboarding_link) {
            this.client.onboarding_link = data.onboarding_link;
          }
          this.actionMessage = data.message;
          this.actionMessageType = 'success';
        } else {
          this.actionMessage = data.error;
          this.actionMessageType = 'error';
        }
      } catch (e) {
        this.actionMessage = 'Erro de conexão';
        this.actionMessageType = 'error';
      } finally {
        this.actionLoading = false;
      }
    },

    async sendOnboardingEmail() {
      this.actionLoading = true;
      this.actionMessage = '';
      try {
        const { authHeaders } = useSDRAuth();
        const res = await fetch('/api/sdr/sale.php?action=send_onboarding_email', {
          method: 'POST',
          headers: authHeaders(),
          body: JSON.stringify({ order_id: this.client.id })
        });
        const data = await res.json();
        if (data.ok) {
          this.actionMessage = data.message;
          this.actionMessageType = 'success';
          this.client.onboarding_email_sent_at = new Date().toISOString();
        } else {
          this.actionMessage = data.error;
          this.actionMessageType = 'error';
        }
      } catch (e) {
        this.actionMessage = 'Erro de conexão';
        this.actionMessageType = 'error';
      } finally {
        this.actionLoading = false;
      }
    },

    async saveNotes() {
      clearTimeout(this.notesSaveTimeout);
      try {
        const { authHeaders } = useSDRAuth();
        await fetch('/api/sdr/sale.php?action=update_notes', {
          method: 'POST',
          headers: authHeaders(),
          body: JSON.stringify({ order_id: this.client.id, notes: this.notes })
        });
        this.notesSaved = true;
        this.notesSaveTimeout = setTimeout(() => { this.notesSaved = false; }, 2000);
      } catch (e) {
        console.error('Erro ao salvar notas:', e);
      }
    },

    copyLink(link) {
      navigator.clipboard.writeText(link).then(() => {
        this.copied = true;
        setTimeout(() => { this.copied = false; }, 2000);
      });
    },

    cleanPhone(phone) {
      return phone.replace(/\D/g, '');
    },

    productLabel(key) {
      const m = { landing: 'Landing Page', site_complete: 'Site Completo PRO' };
      return m[key] || key;
    },

    onboardingLabel(status) {
      const m = { pendente: 'Pendente', preenchendo: 'Preenchendo', concluido: 'Concluído' };
      return m[status] || status;
    },

    formatCurrency(value) {
      if (!value) return '—';
      return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
    },

    formatDateTime(dateStr) {
      if (!dateStr) return '';
      return new Date(dateStr).toLocaleString('pt-BR');
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

.sdr-cliente-detalhe {
  min-height: 100vh;
  background: linear-gradient(135deg, $dark 0%, #1A1A2E 50%, #16213E 100%);
  color: #fff;
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

.loading-state, .empty-state {
  text-align: center;
  padding: 48px;
  color: rgba(255,255,255,0.5);
}

.detail-main {
  max-width: 720px;
  margin: 0 auto;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

section {
  background: $card-bg;
  border: 1px solid $border;
  border-radius: 14px;
  padding: 20px;

  h3 {
    font-size: 14px;
    font-weight: 600;
    color: rgba(255,255,255,0.7);
    margin: 0 0 14px;
  }
}

.info-card {
  .info-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
    h2 { font-size: 20px; margin: 0; }
    .company { font-size: 13px; color: rgba(255,255,255,0.5); margin: 4px 0 0; }
  }

  .badge-lg {
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
    &.paid { background: rgba($green, 0.2); color: $green; }
    &.pending { background: rgba(245,158,11,0.2); color: #F59E0B; }
  }
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  @media (max-width: 480px) { grid-template-columns: 1fr; }
}

.info-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: rgba(255,255,255,0.7);
  i { width: 16px; text-align: center; color: rgba(255,255,255,0.4); }
  a { color: $green; text-decoration: none; &:hover { text-decoration: underline; } }
}

.actions-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.action-btn {
  padding: 10px 18px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s;
  text-decoration: none;
  border: none;
  color: #fff;

  &.green { background: rgba($green, 0.2); border: 1px solid rgba($green, 0.4); color: $green; }
  &.purple { background: rgba($primary, 0.2); border: 1px solid rgba($primary, 0.4); color: $primary; }
  &.outline { background: transparent; border: 1px solid $border; }
  &.whatsapp { background: rgba(37,211,102,0.2); border: 1px solid rgba(37,211,102,0.4); color: #25D366; }

  &:hover { filter: brightness(1.2); }
  &:disabled { opacity: 0.5; cursor: not-allowed; }

  small { font-size: 10px; opacity: 0.7; }
}

.action-message {
  font-size: 13px;
  margin: 10px 0 0;
  display: flex;
  align-items: center;
  gap: 6px;
  &.success { color: $green; }
  &.error { color: #EF4444; }
}

.link-section {
  .link-display {
    display: flex;
    gap: 6px;
    margin-bottom: 12px;
    input {
      flex: 1;
      padding: 8px 12px;
      background: rgba(255,255,255,0.06);
      border: 1px solid $border;
      border-radius: 8px;
      color: #fff;
      font-size: 12px;
    }
    .btn-copy-sm {
      padding: 8px 12px;
      background: rgba($primary, 0.2);
      border: 1px solid rgba($primary, 0.3);
      border-radius: 8px;
      color: $primary;
      cursor: pointer;
    }
  }

  .status-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 0;
    font-size: 12px;
    color: rgba(255,255,255,0.5);
  }

  .badge.onb {
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 6px;
    font-weight: 600;
    &.pendente { background: rgba(156,163,175,0.2); color: #9CA3AF; }
    &.preenchendo { background: rgba(59,130,246,0.2); color: #3B82F6; }
    &.concluido { background: rgba($green, 0.2); color: $green; }
  }
}

.order-section {
  .order-info { display: flex; flex-direction: column; gap: 6px; }
  .order-line {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    color: rgba(255,255,255,0.7);
  }
}

.notes-section {
  textarea {
    width: 100%;
    padding: 10px 12px;
    background: rgba(255,255,255,0.06);
    border: 1px solid $border;
    border-radius: 8px;
    color: #fff;
    font-size: 13px;
    resize: vertical;
    &::placeholder { color: rgba(255,255,255,0.3); }
    &:focus { outline: none; border-color: $primary; }
  }
  .saved-indicator {
    display: block;
    margin-top: 6px;
    font-size: 11px;
    color: $green;
  }
}
</style>
