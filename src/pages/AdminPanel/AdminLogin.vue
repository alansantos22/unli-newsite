<template>
  <div class="admin-login">
    <div class="login-container">
      <div class="login-card">
        <div class="login-header">
          <div class="logo">
            <span class="logo-icon">🏰</span>
            <h1>Torre de Controle</h1>
          </div>
          <p class="subtitle">Painel Super Admin — Unli</p>
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
                placeholder="admin@unli.com.br"
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
              <i class="fas fa-shield-alt"></i>
              Acesso Seguro
            </template>
          </button>
        </form>
      </div>

      <p class="footer-note">Acesso restrito a administradores autorizados.</p>
    </div>
  </div>
</template>

<script>
import { useAdminAuth } from '@/core/composables/useAdminAuth';

export default {
  name: 'AdminLogin',
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
    const { validate } = useAdminAuth();
    const valid = await validate();
    if (valid) {
      this.$router.push('/admin/dashboard');
    }
  },
  methods: {
    async handleLogin() {
      this.errorMsg = '';
      this.loading = true;

      const { login } = useAdminAuth();
      const result = await login(this.email, this.password);

      if (result.ok) {
        this.$router.push('/admin/dashboard');
      } else {
        this.errorMsg = result.error;
      }

      this.loading = false;
    }
  }
};
</script>

<style lang="scss" scoped>
.admin-login {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0a0a14 0%, #111827 50%, #0f172a 100%);
  padding: 20px;
}

.login-container {
  width: 100%;
  max-width: 420px;
  text-align: center;
}

.login-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  padding: 40px 32px;
  backdrop-filter: blur(20px);
}

.login-header {
  margin-bottom: 32px;

  .logo {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-bottom: 8px;

    .logo-icon {
      font-size: 32px;
    }

    h1 {
      font-size: 24px;
      font-weight: 700;
      color: #f1f5f9;
      margin: 0;
    }
  }

  .subtitle {
    color: #94a3b8;
    font-size: 14px;
    margin: 0;
  }
}

.login-form {
  text-align: left;
}

.form-group {
  margin-bottom: 20px;

  label {
    display: block;
    color: #cbd5e1;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  transition: all 0.3s;

  &:focus-within {
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
  }

  i:first-child {
    color: #64748b;
    margin-left: 14px;
    font-size: 14px;
  }

  input {
    flex: 1;
    background: none;
    border: none;
    color: #f1f5f9;
    padding: 14px 12px;
    font-size: 15px;
    outline: none;

    &::placeholder {
      color: #475569;
    }
  }

  .toggle-password {
    background: none;
    border: none;
    color: #64748b;
    padding: 0 14px;
    cursor: pointer;
    font-size: 14px;

    &:hover {
      color: #f59e0b;
    }
  }
}

.error-message {
  color: #ef4444;
  font-size: 13px;
  margin: 0 0 16px;
  padding: 10px 14px;
  background: rgba(239, 68, 68, 0.1);
  border-radius: 8px;
  border: 1px solid rgba(239, 68, 68, 0.2);

  i { margin-right: 6px; }
}

.btn-login {
  width: 100%;
  padding: 14px;
  border: none;
  border-radius: 12px;
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: #0f172a;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.3s;

  &:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
  }

  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
}

.footer-note {
  color: #475569;
  font-size: 12px;
  margin-top: 20px;
}
</style>
