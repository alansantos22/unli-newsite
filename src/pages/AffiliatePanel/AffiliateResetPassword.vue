<template>
  <div class="aff-reset">
    <div class="reset-container">
      <div class="reset-card">

        <!-- Header -->
        <div class="card-header">
          <div class="logo">
            <span class="logo-icon">🤝</span>
            <h1>Painel de Afiliados</h1>
          </div>
          <p class="subtitle">Criar nova senha</p>
        </div>

        <!-- Carregando (validando token) -->
        <div v-if="validating" class="loading-state">
          <svg class="spin" width="32" height="32" viewBox="0 0 512 512" fill="currentColor">
            <path d="M304 48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zm0 416a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM48 304a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm464-48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM142.9 437A48 48 0 1 0 75 369.1 48 48 0 1 0 142.9 437zm0-294.2A48 48 0 1 0 75 75a48 48 0 1 0 67.9 67.9zM369.1 437A48 48 0 1 0 437 369.1 48 48 0 1 0 369.1 437z"/>
          </svg>
          <p>Verificando link...</p>
        </div>

        <!-- Token inválido/expirado -->
        <div v-else-if="tokenError" class="error-state">
          <div class="state-icon">⏰</div>
          <h2>Link inválido ou expirado</h2>
          <p>Este link de redefinição não é mais válido. Os links expiram após 1 hora.</p>
          <router-link to="/afiliados/esqueci-senha" class="btn-retry">
            Solicitar novo link
          </router-link>
        </div>

        <!-- Senha alterada com sucesso -->
        <div v-else-if="success" class="success-state">
          <div class="state-icon">✅</div>
          <h2>Senha redefinida!</h2>
          <p>Sua nova senha foi salva com sucesso. Agora você pode fazer login.</p>
          <router-link to="/afiliados" class="btn-login">
            Ir para o login →
          </router-link>
        </div>

        <!-- Formulário de nova senha -->
        <template v-else>
          <p class="description">
            Olá, <strong>{{ firstName }}</strong>! Escolha uma nova senha para sua conta.
          </p>

          <form @submit.prevent="handleSubmit" class="reset-form">

            <div class="form-group">
              <label for="password">Nova senha</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="14" height="14" viewBox="0 0 448 512" fill="currentColor">
                  <path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/>
                </svg>
                <input
                  id="password"
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="Mínimo 6 caracteres"
                  required
                  autocomplete="new-password"
                  :disabled="loading"
                />
                <button type="button" class="toggle-password" @click="showPassword = !showPassword" tabindex="-1">
                  <svg v-if="showPassword" width="14" height="14" viewBox="0 0 640 512" fill="currentColor">
                    <path d="M38.8 5.1C28.4-3.1 13.3-1.2 5.1 9.2S-1.2 34.7 9.2 42.9l592 464c10.4 8.2 25.5 6.3 33.7-4.1s6.3-25.5-4.1-33.7L525.6 386.7c39.6-40.6 66.4-86.1 79.9-118.4c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C465.5 68.8 400.8 32 320 32c-68.2 0-125 26.3-169.3 60.8L38.8 5.1zM223.1 149.5C248.6 126.2 282.7 112 320 112c79.5 0 144 64.5 144 144c0 24.9-6.3 48.3-17.4 68.7L223.1 149.5z"/>
                  </svg>
                  <svg v-else width="14" height="14" viewBox="0 0 576 512" fill="currentColor">
                    <path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM288 192a64 64 0 1 1 0 128 64 64 0 1 1 0-128z"/>
                  </svg>
                </button>
              </div>
              <div v-if="password" class="password-strength">
                <div class="strength-bar">
                  <div class="strength-fill" :class="strengthClass" :style="{ width: strengthWidth }"></div>
                </div>
                <span class="strength-label" :class="strengthClass">{{ strengthLabel }}</span>
              </div>
            </div>

            <div class="form-group">
              <label for="confirm">Confirmar nova senha</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="14" height="14" viewBox="0 0 448 512" fill="currentColor">
                  <path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/>
                </svg>
                <input
                  id="confirm"
                  v-model="confirm"
                  :type="showConfirm ? 'text' : 'password'"
                  placeholder="Repita a senha"
                  required
                  autocomplete="new-password"
                  :disabled="loading"
                />
                <button type="button" class="toggle-password" @click="showConfirm = !showConfirm" tabindex="-1">
                  <svg v-if="showConfirm" width="14" height="14" viewBox="0 0 640 512" fill="currentColor">
                    <path d="M38.8 5.1C28.4-3.1 13.3-1.2 5.1 9.2S-1.2 34.7 9.2 42.9l592 464c10.4 8.2 25.5 6.3 33.7-4.1s6.3-25.5-4.1-33.7L525.6 386.7c39.6-40.6 66.4-86.1 79.9-118.4c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C465.5 68.8 400.8 32 320 32c-68.2 0-125 26.3-169.3 60.8L38.8 5.1zM223.1 149.5C248.6 126.2 282.7 112 320 112c79.5 0 144 64.5 144 144c0 24.9-6.3 48.3-17.4 68.7L223.1 149.5z"/>
                  </svg>
                  <svg v-else width="14" height="14" viewBox="0 0 576 512" fill="currentColor">
                    <path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM288 192a64 64 0 1 1 0 128 64 64 0 1 1 0-128z"/>
                  </svg>
                </button>
              </div>
            </div>

            <p v-if="errorMsg" class="error-message">
              <svg width="14" height="14" viewBox="0 0 512 512" fill="currentColor">
                <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zm0-384c13.3 0 24 10.7 24 24V264c0 13.3-10.7 24-24 24s-24-10.7-24-24V152c0-13.3 10.7-24 24-24zM232 352a24 24 0 1 1 48 0 24 24 0 1 1 -48 0z"/>
              </svg>
              {{ errorMsg }}
            </p>

            <button type="submit" class="btn-submit" :disabled="loading">
              <template v-if="loading">
                <svg class="spin" width="16" height="16" viewBox="0 0 512 512" fill="currentColor">
                  <path d="M304 48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zm0 416a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM48 304a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm464-48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM142.9 437A48 48 0 1 0 75 369.1 48 48 0 1 0 142.9 437zm0-294.2A48 48 0 1 0 75 75a48 48 0 1 0 67.9 67.9zM369.1 437A48 48 0 1 0 437 369.1 48 48 0 1 0 369.1 437z"/>
                </svg>
                Salvando...
              </template>
              <template v-else>
                Salvar nova senha →
              </template>
            </button>
          </form>
        </template>

      </div>
    </div>
  </div>
</template>

<script>
const API_BASE = process.env.VUE_APP_API_URL || '/api';

export default {
  name: 'AffiliateResetPassword',
  data() {
    return {
      token: '',
      firstName: '',
      password: '',
      confirm: '',
      showPassword: false,
      showConfirm: false,
      errorMsg: '',
      loading: false,
      validating: true,
      tokenError: false,
      success: false
    };
  },
  computed: {
    strengthScore() {
      const p = this.password;
      if (!p) return 0;
      let score = 0;
      if (p.length >= 8) score++;
      if (p.length >= 12) score++;
      if (/[A-Z]/.test(p)) score++;
      if (/[0-9]/.test(p)) score++;
      if (/[^A-Za-z0-9]/.test(p)) score++;
      return score;
    },
    strengthClass() {
      const s = this.strengthScore;
      if (s <= 1) return 'weak';
      if (s <= 3) return 'medium';
      return 'strong';
    },
    strengthWidth() {
      return `${(this.strengthScore / 5) * 100}%`;
    },
    strengthLabel() {
      const s = this.strengthScore;
      if (s <= 1) return 'Fraca';
      if (s <= 3) return 'Média';
      return 'Forte';
    }
  },
  async created() {
    this.token = this.$route.query.token || '';

    if (!this.token) {
      this.tokenError = true;
      this.validating = false;
      return;
    }

    try {
      const res = await fetch(`${API_BASE}/affiliate/auth.php?action=validate_reset_token`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token: this.token })
      });
      const data = await res.json();

      if (res.ok && data.ok) {
        this.firstName = data.first_name;
      } else {
        this.tokenError = true;
      }
    } catch {
      this.tokenError = true;
    } finally {
      this.validating = false;
    }
  },
  methods: {
    async handleSubmit() {
      this.errorMsg = '';

      if (this.password.length < 6) {
        this.errorMsg = 'A senha deve ter pelo menos 6 caracteres.';
        return;
      }

      if (this.password !== this.confirm) {
        this.errorMsg = 'As senhas não coincidem.';
        return;
      }

      this.loading = true;

      try {
        const res = await fetch(`${API_BASE}/affiliate/auth.php?action=reset_password`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ token: this.token, password: this.password })
        });
        const data = await res.json();

        if (res.ok && data.ok) {
          this.success = true;
        } else {
          this.errorMsg = data.error || 'Erro ao salvar senha. Tente novamente.';
        }
      } catch {
        this.errorMsg = 'Erro de conexão. Verifique sua internet.';
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>

<style lang="scss" scoped>
.aff-reset {
  min-height: calc(100vh - 80px);
  margin-top: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0D1117 0%, #161B22 50%, #0D4429 100%);
  padding: 20px;
}

.reset-container {
  width: 100%;
  max-width: 420px;
}

.reset-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 40px;
}

.card-header {
  text-align: center;
  margin-bottom: 28px;

  .logo {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-bottom: 8px;

    .logo-icon { font-size: 32px; }

    h1 {
      font-size: 22px;
      font-weight: 700;
      color: #fff;
      margin: 0;
    }
  }

  .subtitle {
    color: rgba(255, 255, 255, 0.5);
    font-size: 13px;
    margin: 0;
  }
}

.description {
  color: rgba(255, 255, 255, 0.7);
  font-size: 14px;
  line-height: 1.6;
  margin: 0 0 24px;
  text-align: center;

  strong { color: #2ea043; }
}

.reset-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  label {
    display: block;
    color: rgba(255, 255, 255, 0.8);
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 6px;
  }

  .input-wrapper {
    position: relative;
    display: flex;
    align-items: center;

    .input-icon {
      position: absolute;
      left: 14px;
      color: rgba(255, 255, 255, 0.4);
      flex-shrink: 0;
    }

    input {
      width: 100%;
      padding: 12px 40px 12px 40px;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 10px;
      color: #fff;
      font-size: 14px;
      outline: none;
      transition: border-color 0.2s;
      box-sizing: border-box;

      &::placeholder { color: rgba(255, 255, 255, 0.3); }
      &:focus { border-color: rgba(46, 160, 67, 0.7); }
      &:disabled { opacity: 0.5; cursor: not-allowed; }
    }

    .toggle-password {
      position: absolute;
      right: 14px;
      background: none;
      border: none;
      color: rgba(255, 255, 255, 0.4);
      cursor: pointer;
      padding: 0;
      display: flex;
      align-items: center;

      &:hover { color: rgba(255, 255, 255, 0.7); }
    }
  }
}

.password-strength {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 8px;

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
    transition: width 0.3s, background-color 0.3s;

    &.weak   { background: #f87171; }
    &.medium { background: #fbbf24; }
    &.strong { background: #2ea043; }
  }

  .strength-label {
    font-size: 11px;
    font-weight: 600;
    min-width: 36px;

    &.weak   { color: #f87171; }
    &.medium { color: #fbbf24; }
    &.strong { color: #2ea043; }
  }
}

.error-message {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #f87171;
  font-size: 13px;
  margin: 0;
  padding: 10px 14px;
  background: rgba(248, 113, 113, 0.1);
  border-radius: 8px;
  border: 1px solid rgba(248, 113, 113, 0.2);
}

.btn-submit {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 13px;
  background: linear-gradient(135deg, #238636, #2ea043);
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s, transform 0.1s;

  &:hover:not(:disabled) { opacity: 0.9; transform: translateY(-1px); }
  &:disabled { opacity: 0.5; cursor: not-allowed; }
}

/* Estados */
.loading-state {
  text-align: center;
  padding: 20px 0;
  color: rgba(255, 255, 255, 0.6);
  font-size: 14px;

  svg { color: #2ea043; margin-bottom: 12px; }
}

.error-state,
.success-state {
  text-align: center;

  .state-icon {
    font-size: 52px;
    margin-bottom: 16px;
  }

  h2 {
    color: #fff;
    font-size: 22px;
    margin: 0 0 12px;
  }

  p {
    color: rgba(255, 255, 255, 0.7);
    font-size: 14px;
    line-height: 1.6;
    margin: 0 0 24px;
  }
}

.btn-retry,
.btn-login {
  display: inline-block;
  padding: 12px 28px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  text-decoration: none;
  transition: opacity 0.2s;

  &:hover { opacity: 0.85; }
}

.btn-retry {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: #fff;
}

.btn-login {
  background: linear-gradient(135deg, #238636, #2ea043);
  color: #fff;
}

.spin {
  animation: spin 0.8s linear infinite;
}
@keyframes spin {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}
</style>
