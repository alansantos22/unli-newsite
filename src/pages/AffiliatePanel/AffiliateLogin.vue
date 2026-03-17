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
              <svg class="input-icon" width="14" height="14" viewBox="0 0 512 512" fill="currentColor"><path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z"/></svg>
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
              <svg class="input-icon" width="14" height="14" viewBox="0 0 448 512" fill="currentColor"><path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/></svg>
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
                <svg v-if="showPassword" width="14" height="14" viewBox="0 0 640 512" fill="currentColor"><path d="M38.8 5.1C28.4-3.1 13.3-1.2 5.1 9.2S-1.2 34.7 9.2 42.9l592 464c10.4 8.2 25.5 6.3 33.7-4.1s6.3-25.5-4.1-33.7L525.6 386.7c39.6-40.6 66.4-86.1 79.9-118.4c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C465.5 68.8 400.8 32 320 32c-68.2 0-125 26.3-169.3 60.8L38.8 5.1zM223.1 149.5C248.6 126.2 282.7 112 320 112c79.5 0 144 64.5 144 144c0 24.9-6.3 48.3-17.4 68.7L223.1 149.5z"/></svg>
                <svg v-else width="14" height="14" viewBox="0 0 576 512" fill="currentColor"><path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM288 192a64 64 0 1 1 0 128 64 64 0 1 1 0-128z"/></svg>
              </button>
            </div>
          </div>

          <p v-if="errorMsg" class="error-message">
            <svg width="14" height="14" viewBox="0 0 512 512" fill="currentColor"><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zm0-384c13.3 0 24 10.7 24 24V264c0 13.3-10.7 24-24 24s-24-10.7-24-24V152c0-13.3 10.7-24 24-24zM232 352a24 24 0 1 1 48 0 24 24 0 1 1 -48 0z"/></svg>
            {{ errorMsg }}
          </p>

          <button type="submit" class="btn-login" :disabled="loading">
            <template v-if="loading">
              <svg class="spin" width="16" height="16" viewBox="0 0 512 512" fill="currentColor"><path d="M304 48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zm0 416a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM48 304a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm464-48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM142.9 437A48 48 0 1 0 75 369.1 48 48 0 1 0 142.9 437zm0-294.2A48 48 0 1 0 75 75a48 48 0 1 0 67.9 67.9zM369.1 437A48 48 0 1 0 437 369.1 48 48 0 1 0 369.1 437z"/></svg>
              Entrando...
            </template>
            <template v-else>
              Entrar →
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

.spin {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
