<template>
  <div class="sdr-configuracoes">
    <header class="sdr-cfg-header">
      <router-link to="/sdr/dashboard" class="btn-back">
        <i class="fas fa-arrow-left"></i> Dashboard
      </router-link>
      <h1><i class="fas fa-cog"></i> Configurações</h1>
    </header>

    <main class="cfg-main">

      <!-- E-mail -->
      <section class="cfg-card">
        <div class="card-title">
          <i class="fas fa-envelope"></i>
          <h2>Alterar E-mail</h2>
        </div>

        <div v-if="emailSuccess" class="alert alert-success">
          <i class="fas fa-check-circle"></i> {{ emailSuccess }}
        </div>
        <div v-if="emailError" class="alert alert-error">
          <i class="fas fa-exclamation-circle"></i> {{ emailError }}
        </div>

        <form @submit.prevent="submitEmail" class="cfg-form">
          <div class="form-group">
            <label>Novo e-mail</label>
            <input
              v-model="emailForm.email"
              type="email"
              placeholder="novo@email.com"
              required
              autocomplete="email"
            />
          </div>
          <button type="submit" class="btn-save" :disabled="emailLoading">
            <i :class="emailLoading ? 'fas fa-spinner fa-spin' : 'fas fa-check'"></i>
            {{ emailLoading ? 'Salvando...' : 'Salvar e-mail' }}
          </button>
        </form>
      </section>

      <!-- Senha -->
      <section class="cfg-card">
        <div class="card-title">
          <i class="fas fa-lock"></i>
          <h2>Alterar Senha</h2>
        </div>

        <div v-if="passwordSuccess" class="alert alert-success">
          <i class="fas fa-check-circle"></i> {{ passwordSuccess }}
        </div>
        <div v-if="passwordError" class="alert alert-error">
          <i class="fas fa-exclamation-circle"></i> {{ passwordError }}
        </div>

        <form @submit.prevent="submitPassword" class="cfg-form">
          <div class="form-group">
            <label>Senha atual</label>
            <div class="input-eye">
              <input
                v-model="passwordForm.current"
                :type="showCurrent ? 'text' : 'password'"
                placeholder="••••••••"
                required
                autocomplete="current-password"
              />
              <button type="button" class="eye-btn" @click="showCurrent = !showCurrent">
                <i :class="showCurrent ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </button>
            </div>
          </div>
          <div class="form-group">
            <label>Nova senha</label>
            <div class="input-eye">
              <input
                v-model="passwordForm.new_password"
                :type="showNew ? 'text' : 'password'"
                placeholder="Mínimo 8 caracteres"
                required
                autocomplete="new-password"
                minlength="8"
              />
              <button type="button" class="eye-btn" @click="showNew = !showNew">
                <i :class="showNew ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </button>
            </div>
            <div class="password-strength" v-if="passwordForm.new_password">
              <div class="strength-bar">
                <div class="strength-fill" :class="strengthClass" :style="{ width: strengthPct + '%' }"></div>
              </div>
              <span class="strength-label" :class="strengthClass">{{ strengthLabel }}</span>
            </div>
          </div>
          <div class="form-group">
            <label>Confirmar nova senha</label>
            <div class="input-eye">
              <input
                v-model="passwordForm.confirm"
                :type="showConfirm ? 'text' : 'password'"
                placeholder="Repita a nova senha"
                required
                autocomplete="new-password"
              />
              <button type="button" class="eye-btn" @click="showConfirm = !showConfirm">
                <i :class="showConfirm ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </button>
            </div>
            <p v-if="passwordMismatch" class="field-error">As senhas não coincidem</p>
          </div>
          <button type="submit" class="btn-save" :disabled="passwordLoading || passwordMismatch">
            <i :class="passwordLoading ? 'fas fa-spinner fa-spin' : 'fas fa-key'"></i>
            {{ passwordLoading ? 'Salvando...' : 'Alterar senha' }}
          </button>
        </form>
      </section>

    </main>
  </div>
</template>

<script>
import { useSDRAuth } from '@/core/composables/useSDRAuth';

export default {
  name: 'SDRConfiguracoes',
  data() {
    return {
      // E-mail
      emailForm: { email: '' },
      emailLoading: false,
      emailSuccess: '',
      emailError: '',

      // Senha
      passwordForm: { current: '', new_password: '', confirm: '' },
      passwordLoading: false,
      passwordSuccess: '',
      passwordError: '',
      showCurrent: false,
      showNew: false,
      showConfirm: false
    };
  },
  computed: {
    passwordMismatch() {
      return (
        this.passwordForm.confirm.length > 0 &&
        this.passwordForm.new_password !== this.passwordForm.confirm
      );
    },
    strengthScore() {
      const p = this.passwordForm.new_password;
      if (!p) return 0;
      let score = 0;
      if (p.length >= 8) score++;
      if (p.length >= 12) score++;
      if (/[A-Z]/.test(p)) score++;
      if (/[0-9]/.test(p)) score++;
      if (/[^A-Za-z0-9]/.test(p)) score++;
      return score;
    },
    strengthPct() {
      return (this.strengthScore / 5) * 100;
    },
    strengthClass() {
      if (this.strengthScore <= 1) return 'weak';
      if (this.strengthScore <= 3) return 'medium';
      return 'strong';
    },
    strengthLabel() {
      if (this.strengthScore <= 1) return 'Fraca';
      if (this.strengthScore <= 3) return 'Média';
      return 'Forte';
    }
  },
  methods: {
    async submitEmail() {
      this.emailSuccess = '';
      this.emailError = '';
      this.emailLoading = true;

      const { authHeaders } = useSDRAuth();
      try {
        const res = await fetch('/api/sdr/settings.php?action=change_email', {
          method: 'POST',
          headers: authHeaders(),
          body: JSON.stringify({ email: this.emailForm.email.trim() })
        });
        const data = await res.json();
        if (data.ok) {
          this.emailSuccess = data.message || 'E-mail atualizado com sucesso!';
          this.emailForm.email = '';
        } else {
          this.emailError = data.error || 'Erro ao atualizar e-mail';
        }
      } catch {
        this.emailError = 'Erro de conexão com o servidor';
      } finally {
        this.emailLoading = false;
      }
    },

    async submitPassword() {
      this.passwordSuccess = '';
      this.passwordError = '';
      if (this.passwordMismatch) return;

      this.passwordLoading = true;
      const { authHeaders } = useSDRAuth();
      try {
        const res = await fetch('/api/sdr/settings.php?action=change_password', {
          method: 'POST',
          headers: authHeaders(),
          body: JSON.stringify({
            current_password: this.passwordForm.current,
            new_password: this.passwordForm.new_password,
            confirm_password: this.passwordForm.confirm
          })
        });
        const data = await res.json();
        if (data.ok) {
          this.passwordSuccess = data.message || 'Senha alterada com sucesso!';
          this.passwordForm = { current: '', new_password: '', confirm: '' };
          this.showCurrent = false;
          this.showNew = false;
          this.showConfirm = false;
        } else {
          this.passwordError = data.error || 'Erro ao alterar senha';
        }
      } catch {
        this.passwordError = 'Erro de conexão com o servidor';
      } finally {
        this.passwordLoading = false;
      }
    }
  }
};
</script>

<style lang="scss" scoped>
$primary: #8B5CF6;
$dark: #0F0F1A;
$border: rgba(255, 255, 255, 0.1);
$green: #10B981;
$red: #EF4444;

.sdr-configuracoes {
  min-height: calc(100vh - 80px);
  background: linear-gradient(135deg, $dark 0%, #1A1A2E 50%, #16213E 100%);
  color: #fff;
  margin-top: 80px;
}

.sdr-cfg-header {
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
    font-weight: 700;
    margin: 0;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 8px;

    i { color: $primary; }
  }
}

.cfg-main {
  max-width: 560px;
  margin: 0 auto;
  padding: 32px 20px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.cfg-card {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid $border;
  border-radius: 16px;
  padding: 28px;
}

.card-title {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;

  i {
    font-size: 18px;
    color: $primary;
  }

  h2 {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
  }
}

.alert {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  margin-bottom: 16px;

  &.alert-success {
    background: rgba($green, 0.12);
    border: 1px solid rgba($green, 0.3);
    color: $green;
  }

  &.alert-error {
    background: rgba($red, 0.12);
    border: 1px solid rgba($red, 0.3);
    color: $red;
  }
}

.cfg-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;

  label {
    font-size: 12px;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.6);
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  input {
    width: 100%;
    padding: 11px 14px;
    background: rgba(255, 255, 255, 0.07);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 8px;
    color: #fff;
    font-size: 14px;
    transition: border-color 0.2s;

    &::placeholder { color: rgba(255, 255, 255, 0.25); }
    &:focus {
      outline: none;
      border-color: $primary;
      background: rgba($primary, 0.06);
    }
  }
}

.field-error {
  font-size: 12px;
  color: $red;
  margin: 0;
}

.input-eye {
  position: relative;
  display: flex;
  align-items: center;

  input { padding-right: 40px; }

  .eye-btn {
    position: absolute;
    right: 10px;
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.4);
    cursor: pointer;
    padding: 4px;
    font-size: 14px;
    transition: color 0.2s;

    &:hover { color: rgba(255, 255, 255, 0.8); }
  }
}

.password-strength {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 4px;

  .strength-bar {
    flex: 1;
    height: 4px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 2px;
    overflow: hidden;
  }

  .strength-fill {
    height: 100%;
    border-radius: 2px;
    transition: width 0.3s, background 0.3s;

    &.weak   { background: $red; }
    &.medium { background: #F59E0B; }
    &.strong { background: $green; }
  }

  .strength-label {
    font-size: 11px;
    font-weight: 700;
    min-width: 40px;

    &.weak   { color: $red; }
    &.medium { color: #F59E0B; }
    &.strong { color: $green; }
  }
}

.btn-save {
  align-self: flex-start;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 11px 24px;
  background: linear-gradient(135deg, $primary, darken($primary, 10%));
  color: #fff;
  border: none;
  border-radius: 9px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;

  &:hover:not(:disabled) {
    box-shadow: 0 4px 16px rgba($primary, 0.35);
    transform: translateY(-1px);
  }

  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
  }
}
</style>
