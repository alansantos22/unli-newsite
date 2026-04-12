<template>
  <div class="aff-forgot">
    <div class="forgot-container">
      <div class="forgot-card">

        <!-- Header -->
        <div class="card-header">
          <div class="logo">
            <span class="logo-icon">🤝</span>
            <h1>Painel de Afiliados</h1>
          </div>
          <p class="subtitle">Redefinição de senha</p>
        </div>

        <!-- Sucesso -->
        <div v-if="success" class="success-state">
          <div class="success-icon">📬</div>
          <h2>Verifique seu e-mail</h2>
          <p>
            Se o e-mail <strong>{{ email }}</strong> estiver cadastrado no sistema,
            você receberá as instruções para redefinir a senha em instantes.
          </p>
          <p class="hint">Não esqueça de verificar a pasta de spam.</p>
          <router-link to="/afiliados" class="btn-back">← Voltar para o login</router-link>
        </div>

        <!-- Formulário -->
        <template v-else>
          <p class="description">
            Informe o e-mail cadastrado na sua conta. Enviaremos um link para você criar uma nova senha.
          </p>

          <form @submit.prevent="handleSubmit" class="forgot-form">
            <div class="form-group">
              <label for="email">E-mail</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="14" height="14" viewBox="0 0 512 512" fill="currentColor">
                  <path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z"/>
                </svg>
                <input
                  id="email"
                  v-model="email"
                  type="email"
                  placeholder="seu@email.com"
                  required
                  autocomplete="email"
                  :disabled="loading"
                />
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
                Enviando...
              </template>
              <template v-else>
                Enviar link de redefinição →
              </template>
            </button>
          </form>

          <div class="card-footer">
            <router-link to="/afiliados">← Voltar para o login</router-link>
          </div>
        </template>

      </div>
    </div>
  </div>
</template>

<script>
const API_BASE = process.env.VUE_APP_API_URL || '/api';

export default {
  name: 'AffiliateForgotPassword',
  data() {
    return {
      email: '',
      errorMsg: '',
      loading: false,
      success: false
    };
  },
  methods: {
    async handleSubmit() {
      this.errorMsg = '';
      this.loading = true;

      try {
        const res = await fetch(`${API_BASE}/affiliate/auth.php?action=forgot_password`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email: this.email })
        });

        const data = await res.json();

        if (res.ok && data.ok) {
          this.success = true;
        } else {
          this.errorMsg = data.error || 'Erro ao enviar. Tente novamente.';
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
.aff-forgot {
  min-height: calc(100vh - 80px);
  margin-top: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0D1117 0%, #161B22 50%, #0D4429 100%);
  padding: 20px;
}

.forgot-container {
  width: 100%;
  max-width: 420px;
}

.forgot-card {
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
}

.forgot-form {
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
      padding: 12px 14px 12px 40px;
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

.card-footer {
  text-align: center;
  margin-top: 20px;

  a {
    color: rgba(255, 255, 255, 0.5);
    font-size: 13px;
    text-decoration: none;
    transition: color 0.2s;

    &:hover { color: rgba(255, 255, 255, 0.8); }
  }
}

/* Estado de sucesso */
.success-state {
  text-align: center;

  .success-icon {
    font-size: 56px;
    margin-bottom: 16px;
  }

  h2 {
    color: #fff;
    font-size: 22px;
    margin: 0 0 14px;
  }

  p {
    color: rgba(255, 255, 255, 0.7);
    font-size: 14px;
    line-height: 1.6;
    margin: 0 0 10px;

    strong { color: #2ea043; }
  }

  .hint {
    color: rgba(255, 255, 255, 0.4);
    font-size: 13px !important;
    margin-top: 4px;
  }

  .btn-back {
    display: inline-block;
    margin-top: 24px;
    color: rgba(255, 255, 255, 0.5);
    font-size: 13px;
    text-decoration: none;
    transition: color 0.2s;

    &:hover { color: #fff; }
  }
}

.spin {
  animation: spin 0.8s linear infinite;
}
@keyframes spin {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}
</style>
