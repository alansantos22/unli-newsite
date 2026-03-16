<template>
  <div class="aff-login">
    <div class="login-container">
      <div class="login-card">
        <div class="login-header">
          <div class="logo">
            <span class="logo-icon">🤝</span>
            <h1>Painel de Afiliados</h1>
          </div>
          <p class="subtitle">Acesse sua área de parceiro</p>
        </div>

        <form @submit.prevent="handleLogin" class="login-form">
          <div class="form-group">
            <label for="email">E-mail</label>
            <div class="input-wrapper">
              <i class="fas fa-envelope"></i>
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

          <div class="form-group">
            <label for="password">Senha</label>
            <div class="input-wrapper">
              <i class="fas fa-lock"></i>
              <input
                id="password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Sua senha"
                required
                autocomplete="current-password"
                :disabled="loading"
              />
              <button type="button" class="toggle-password" @click="showPassword = !showPassword" tabindex="-1">
                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </button>
            </div>
          </div>

          <p v-if="errorMsg" class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            {{ errorMsg }}
          </p>

          <button type="submit" class="btn-login" :disabled="loading">
            <template v-if="loading">
              <i class="fas fa-spinner fa-spin"></i>
              Entrando...
            </template>
            <template v-else>
              <i class="fas fa-sign-in-alt"></i>
              Entrar
            </template>
          </button>
        </form>

        <div class="login-footer">
          <p>Não tem conta? <router-link to="/afiliados/registro">Cadastre-se gratuitamente</router-link></p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useAffiliateAuth } from '@/core/composables/useAffiliateAuth';

export default {
  name: 'AffiliateLogin',
  data() {
    return {
      email: '',
      password: '',
      showPassword: false,
      errorMsg: '',
      loading: false
    };
  },
  async created() {
    const { validate } = useAffiliateAuth();
    const valid = await validate();
    if (valid) {
      this.$router.push('/afiliados/dashboard');
    }
  },
  methods: {
    async handleLogin() {
      this.errorMsg = '';
      this.loading = true;

      const { login } = useAffiliateAuth();
      const result = await login(this.email, this.password);

      if (result.ok) {
        this.$router.push('/afiliados/dashboard');
      } else {
        this.errorMsg = result.error;
      }

      this.loading = false;
    }
  }
};
</script>

<style lang="scss" scoped>
.aff-login {
  min-height: calc(100vh - 80px);
  margin-top: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0D1117 0%, #161B22 50%, #0D4429 100%);
  padding: 20px;
}

.login-container {
  width: 100%;
  max-width: 420px;
}

.login-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 40px;
}

.login-header {
  text-align: center;
  margin-bottom: 32px;

  .logo {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-bottom: 8px;

    .logo-icon { font-size: 32px; }

    h1 {
      font-size: 24px;
      font-weight: 700;
      color: #fff;
      margin: 0;
    }
  }

  .subtitle {
    color: rgba(255, 255, 255, 0.6);
    font-size: 14px;
    margin: 0;
  }
}

.login-form {
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

    > i:first-child {
      position: absolute;
      left: 14px;
      color: rgba(255, 255, 255, 0.4);
      font-size: 14px;
    }

    input {
      width: 100%;
      padding: 12px 14px 12px 40px;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 10px;
      color: #fff;
      font-size: 15px;
      transition: all 0.2s;

      &::placeholder { color: rgba(255, 255, 255, 0.3); }

      &:focus {
        outline: none;
        border-color: #10B981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
      }
    }

    .toggle-password {
      position: absolute;
      right: 12px;
      background: none;
      border: none;
      color: rgba(255, 255, 255, 0.4);
      cursor: pointer;
      padding: 4px;

      &:hover { color: rgba(255, 255, 255, 0.7); }
    }
  }
}

.error-message {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #EF4444;
  font-size: 13px;
  margin: 0;
  padding: 10px 14px;
  background: rgba(239, 68, 68, 0.1);
  border-radius: 8px;
}

.btn-login {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 14px;
  background: linear-gradient(135deg, #10B981 0%, #059669 100%);
  color: #fff;
  font-size: 15px;
  font-weight: 600;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s;

  &:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.4);
  }

  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
}

.login-footer {
  margin-top: 24px;
  text-align: center;

  p {
    color: rgba(255, 255, 255, 0.5);
    font-size: 14px;
    margin: 0;

    a {
      color: #10B981;
      text-decoration: none;
      font-weight: 600;

      &:hover { text-decoration: underline; }
    }
  }
}
</style>
