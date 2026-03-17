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
            <svg width="16" height="16" viewBox="0 0 448 512" fill="currentColor"><path d="M181.3 32.4c17.4 2.9 29.2 19.4 26.3 36.8l-9.8 58.8h95.1l11.5-69.3c2.9-17.4 19.4-29.2 36.8-26.3s29.2 19.4 26.3 36.8l-9.8 58.8H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H345.4l-13.3 80H392c17.7 0 32 14.3 32 32s-14.3 32-32 32h-68.6l-11.5 69.3c-2.9 17.4-19.4 29.2-36.8 26.3s-29.2-19.4-26.3-36.8l9.8-58.7h-95.1l-11.5 69.3c-2.9 17.4-19.4 29.2-36.8 26.3s-29.2-19.4-26.3-36.8l9.8-58.7H32c-17.7 0-32-14.3-32-32s14.3-32 32-32h60.6l13.3-80H48c-17.7 0-32-14.3-32-32s14.3-32 32-32h66.6l11.5-69.3c2.9-17.4 19.4-29.2 36.8-26.3zM222.6 192H168.3l-13.3 80h95.1l13.3-80h-40.8z"/></svg>
            <span>Até <strong>15%</strong> de comissão</span>
          </div>
          <div class="benefit-item">
            <svg width="16" height="16" viewBox="0 0 576 512" fill="currentColor"><path d="M400 0H176c-26.5 0-48.1 21.8-47.1 48.2c.2 5.3 .4 10.6 .7 15.8H24C10.7 64 0 74.7 0 88c0 92.6 33.5 157 78.5 200.7c44.3 43.1 98.3 64.8 138.1 75.8c23.4 6.5 39.4 26 39.4 45.6c0 20.9-17 37.9-37.9 37.9H192c-17.7 0-32 14.3-32 32s14.3 32 32 32h192c17.7 0 32-14.3 32-32s-14.3-32-32-32h-26.1c-20.9 0-37.9-17-37.9-37.9c0-19.6 15.9-39.2 39.4-45.6c39.9-11 93.9-32.7 138.2-75.8C542.5 245 576 180.6 576 88c0-13.3-10.7-24-24-24H446.4c.3-5.2 .5-10.4 .7-15.8C448.1 21.8 426.5 0 400 0z"/></svg>
            <span>Sistema de <strong>ligas</strong></span>
          </div>
          <div class="benefit-item">
            <svg width="16" height="16" viewBox="0 0 512 512" fill="currentColor"><path d="M64 64c0-17.7-14.3-32-32-32S0 46.3 0 64V400c0 44.2 35.8 80 80 80H480c17.7 0 32-14.3 32-32s-14.3-32-32-32H80c-8.8 0-16-7.2-16-16V64zm406.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L320 210.7l-57.4-57.4c-12.5-12.5-32.8-12.5-45.3 0l-112 112c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L240 221.3l57.4 57.4c12.5 12.5 32.8 12.5 45.3 0l128-128z"/></svg>
            <span>Dashboard <strong>completo</strong></span>
          </div>
        </div>

        <form @submit.prevent="handleRegister" class="register-form">
          <div class="form-row">
            <div class="form-group">
              <label for="first_name">Primeiro Nome *</label>
              <div class="input-wrapper">
                <svg class="input-icon" width="14" height="14" viewBox="0 0 448 512" fill="currentColor"><path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z"/></svg>
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
                <svg class="input-icon" width="14" height="14" viewBox="0 0 448 512" fill="currentColor"><path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z"/></svg>
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
              <svg class="input-icon" width="14" height="14" viewBox="0 0 512 512" fill="currentColor"><path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z"/></svg>
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
              <svg class="input-icon" width="14" height="14" viewBox="0 0 448 512" fill="currentColor"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6z"/></svg>
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
              <svg class="input-icon" width="14" height="14" viewBox="0 0 512 512" fill="currentColor"><path d="M336.3 34.8c-11-13.9-31.7-16.4-46.1-5.8L60.8 210.7c-10.2 7.5-16 19.3-16 31.9V464c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48V242.6c0-12.6-5.8-24.4-16-31.9L336.3 34.8z"/></svg>
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
              <svg class="input-icon" width="14" height="14" viewBox="0 0 448 512" fill="currentColor"><path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/></svg>
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Mínimo 6 caracteres"
                required
                :disabled="loading"
              />
              <button type="button" class="toggle-password" @click="showPassword = !showPassword" tabindex="-1">
                <svg v-if="showPassword" width="14" height="14" viewBox="0 0 640 512" fill="currentColor"><path d="M38.8 5.1C28.4-3.1 13.3-1.2 5.1 9.2S-1.2 34.7 9.2 42.9l592 464c10.4 8.2 25.5 6.3 33.7-4.1s6.3-25.5-4.1-33.7L525.6 386.7c39.6-40.6 66.4-86.1 79.9-118.4c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C465.5 68.8 400.8 32 320 32c-68.2 0-125 26.3-169.3 60.8L38.8 5.1zM223.1 149.5C248.6 126.2 282.7 112 320 112c79.5 0 144 64.5 144 144c0 24.9-6.3 48.3-17.4 68.7L223.1 149.5z"/></svg>
                <svg v-else width="14" height="14" viewBox="0 0 576 512" fill="currentColor"><path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM288 192a64 64 0 1 1 0 128 64 64 0 1 1 0-128z"/></svg>
              </button>
            </div>
          </div>

          <div class="form-group">
            <label for="password_confirm">Confirmar Senha *</label>
            <div class="input-wrapper">
              <svg class="input-icon" width="14" height="14" viewBox="0 0 448 512" fill="currentColor"><path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/></svg>
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
            <svg width="14" height="14" viewBox="0 0 512 512" fill="currentColor"><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zm0-384c13.3 0 24 10.7 24 24V264c0 13.3-10.7 24-24 24s-24-10.7-24-24V152c0-13.3 10.7-24 24-24zM232 352a24 24 0 1 1 48 0 24 24 0 1 1 -48 0z"/></svg>
            {{ errorMsg }}
          </p>

          <button type="submit" class="btn-register" :disabled="loading">
            <template v-if="loading">
              <svg class="spin" width="16" height="16" viewBox="0 0 512 512" fill="currentColor"><path d="M304 48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zm0 416a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM48 304a48 48 0 1 0 0-96 48 48 0 1 0 0 96zm464-48a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM142.9 437A48 48 0 1 0 75 369.1 48 48 0 1 0 142.9 437zm0-294.2A48 48 0 1 0 75 75a48 48 0 1 0 67.9 67.9zM369.1 437A48 48 0 1 0 437 369.1 48 48 0 1 0 369.1 437z"/></svg>
              Cadastrando...
            </template>
            <template v-else>
              🚀 Criar Conta de Afiliado
            </template>
          </button>
        </form>

        <div class="register-footer">
          <p>Já tem conta? <router-link to="/afiliados">Fazer login</router-link></p>
        </div>

        <!-- Info sobre comissões -->
        <div class="commission-info">
          <h3><svg width="15" height="15" viewBox="0 0 512 512" fill="currentColor"><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/></svg> Como funciona?</h3>
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
  margin-top: 80px;
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

    svg {
      fill: #10B981;
      flex-shrink: 0;
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
    svg { fill: #10B981; }
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

.spin {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
