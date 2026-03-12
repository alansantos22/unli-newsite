<template>
  <div class="aff-register">
    <div class="register-container">
      <div class="register-card">
        <div class="register-header">
          <div class="logo">
            <span class="logo-icon">🚀</span>
            <h1>Seja um Afiliado Unli</h1>
          </div>
          <p class="subtitle">Cadastre-se e comece a ganhar comissões indicando nossos serviços</p>
        </div>

        <!-- Benefits Banner -->
        <div class="benefits-banner">
          <div class="benefit-item">
            <i class="fas fa-percentage"></i>
            <span>Até <strong>15%</strong> de comissão</span>
          </div>
          <div class="benefit-item">
            <i class="fas fa-trophy"></i>
            <span>Sistema de <strong>ligas</strong></span>
          </div>
          <div class="benefit-item">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard <strong>completo</strong></span>
          </div>
        </div>

        <form @submit.prevent="handleRegister" class="register-form">
          <div class="form-row">
            <div class="form-group">
              <label for="first_name">Primeiro Nome *</label>
              <div class="input-wrapper">
                <i class="fas fa-user"></i>
                <input
                  id="first_name"
                  v-model="form.first_name"
                  type="text"
                  placeholder="João"
                  required
                  :disabled="loading"
                />
              </div>
            </div>
            <div class="form-group">
              <label for="last_name">Sobrenome *</label>
              <div class="input-wrapper">
                <i class="fas fa-user"></i>
                <input
                  id="last_name"
                  v-model="form.last_name"
                  type="text"
                  placeholder="Silva"
                  required
                  :disabled="loading"
                />
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="email">E-mail *</label>
            <div class="input-wrapper">
              <i class="fas fa-envelope"></i>
              <input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="joao@email.com"
                required
                :disabled="loading"
              />
            </div>
          </div>

          <div class="form-group">
            <label for="whatsapp">WhatsApp *</label>
            <div class="input-wrapper">
              <i class="fab fa-whatsapp"></i>
              <input
                id="whatsapp"
                v-model="form.whatsapp"
                type="tel"
                placeholder="(11) 99999-9999"
                required
                :disabled="loading"
              />
            </div>
          </div>

          <div class="form-group">
            <label for="pix_key">Chave PIX (para receber comissões)</label>
            <div class="input-wrapper">
              <i class="fas fa-key"></i>
              <input
                id="pix_key"
                v-model="form.pix_key"
                type="text"
                placeholder="CPF, e-mail, telefone ou chave aleatória"
                :disabled="loading"
              />
            </div>
          </div>

          <div class="form-group">
            <label for="password">Senha *</label>
            <div class="input-wrapper">
              <i class="fas fa-lock"></i>
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Mínimo 6 caracteres"
                required
                :disabled="loading"
              />
              <button type="button" class="toggle-password" @click="showPassword = !showPassword" tabindex="-1">
                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </button>
            </div>
          </div>

          <div class="form-group">
            <label for="password_confirm">Confirmar Senha *</label>
            <div class="input-wrapper">
              <i class="fas fa-lock"></i>
              <input
                id="password_confirm"
                v-model="form.password_confirm"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Repita a senha"
                required
                :disabled="loading"
              />
            </div>
          </div>

          <p v-if="errorMsg" class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            {{ errorMsg }}
          </p>

          <button type="submit" class="btn-register" :disabled="loading">
            <template v-if="loading">
              <i class="fas fa-spinner fa-spin"></i>
              Cadastrando...
            </template>
            <template v-else>
              <i class="fas fa-rocket"></i>
              Criar Conta de Afiliado
            </template>
          </button>
        </form>

        <div class="register-footer">
          <p>Já tem conta? <router-link to="/afiliados">Fazer login</router-link></p>
        </div>

        <!-- Info sobre comissões -->
        <div class="commission-info">
          <h3><i class="fas fa-info-circle"></i> Como funciona?</h3>
          <ul>
            <li>Indique clientes usando seus links exclusivos</li>
            <li>Acompanhe o progresso em tempo real no dashboard</li>
            <li>Comissões de <strong>5% a 15%</strong> dependendo do seu título</li>
            <li>Comissões pagas a partir do 5º dia útil do mês</li>
            <li>Prazo de pagamento: até 30 dias úteis após a venda</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useAffiliateAuth } from '@/core/composables/useAffiliateAuth';

export default {
  name: 'AffiliateRegister',
  data() {
    return {
      form: {
        first_name: '',
        last_name: '',
        email: '',
        whatsapp: '',
        pix_key: '',
        password: '',
        password_confirm: ''
      },
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
    async handleRegister() {
      this.errorMsg = '';

      // Validações locais
      if (this.form.password !== this.form.password_confirm) {
        this.errorMsg = 'As senhas não coincidem';
        return;
      }
      if (this.form.password.length < 6) {
        this.errorMsg = 'A senha deve ter pelo menos 6 caracteres';
        return;
      }

      this.loading = true;

      const { register } = useAffiliateAuth();
      const result = await register({
        first_name: this.form.first_name,
        last_name: this.form.last_name,
        email: this.form.email,
        whatsapp: this.form.whatsapp,
        pix_key: this.form.pix_key,
        password: this.form.password
      });

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
.aff-register {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0D1117 0%, #161B22 50%, #0D4429 100%);
  padding: 40px 20px;
}

.register-container {
  width: 100%;
  max-width: 520px;
}

.register-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 40px;
}

.register-header {
  text-align: center;
  margin-bottom: 24px;

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

.benefits-banner {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-bottom: 32px;
  padding: 16px;
  background: rgba(16, 185, 129, 0.1);
  border: 1px solid rgba(16, 185, 129, 0.2);
  border-radius: 12px;

  .benefit-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: rgba(255, 255, 255, 0.8);
    font-size: 13px;

    i {
      color: #10B981;
      font-size: 16px;
    }

    strong { color: #10B981; }
  }

  @media (max-width: 480px) {
    flex-direction: column;
    gap: 10px;
  }
}

.register-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;

  @media (max-width: 480px) {
    grid-template-columns: 1fr;
  }
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

.btn-register {
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

.register-footer {
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

.commission-info {
  margin-top: 32px;
  padding: 20px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;

  h3 {
    display: flex;
    align-items: center;
    gap: 8px;
    color: rgba(255, 255, 255, 0.8);
    font-size: 15px;
    margin: 0 0 12px;

    i { color: #10B981; }
  }

  ul {
    list-style: none;
    padding: 0;
    margin: 0;

    li {
      position: relative;
      padding: 6px 0 6px 20px;
      color: rgba(255, 255, 255, 0.6);
      font-size: 13px;
      line-height: 1.6;

      &::before {
        content: '✓';
        position: absolute;
        left: 0;
        color: #10B981;
        font-weight: bold;
      }

      strong { color: #10B981; }
    }
  }
}
</style>
