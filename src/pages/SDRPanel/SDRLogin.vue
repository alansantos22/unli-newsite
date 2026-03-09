<template>
  <div class="sdr-login">
    <div class="login-container">
      <div class="login-card">
        <div class="login-header">
          <div class="logo">
            <span class="logo-icon">🎯</span>
            <h1>Painel SDR</h1>
          </div>
          <p class="subtitle">Acesse sua área de vendas</p>
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
      </div>
    </div>
  </div>
</template>

<script>
import { useSDRAuth } from '@/core/composables/useSDRAuth';

export default {
  name: 'SDRLogin',
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
    // Se já está autenticado, redirecionar
    const { validate } = useSDRAuth();
    const valid = await validate();
    if (valid) {
      this.$router.push('/sdr/dashboard');
    }
  },
  methods: {
    async handleLogin() {
      this.errorMsg = '';
      this.loading = true;

      const { login } = useSDRAuth();
      const result = await login(this.email, this.password);

      if (result.ok) {
        this.$router.push('/sdr/dashboard');
      } else {
        this.errorMsg = result.error;
      }

      this.loading = false;
    }
  }
};
</script>

<style lang="scss" scoped>
.sdr-login {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0F0F1A 0%, #1A1A2E 50%, #16213E 100%);
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
      font-size: 28px;
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
        border-color: #8B5CF6;
        background: rgba(139, 92, 246, 0.08);
      }
      &:disabled { opacity: 0.5; }
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
  color: #EF4444;
  font-size: 13px;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 6px;
}

.btn-login {
  padding: 14px;
  background: linear-gradient(135deg, #8B5CF6, #7C3AED);
  color: #fff;
  font-size: 16px;
  font-weight: 600;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.2s;
  margin-top: 8px;

  &:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(139, 92, 246, 0.3);
  }
  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
}
</style>
