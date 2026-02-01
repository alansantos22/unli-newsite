<template>
  <div class="page-conversational-test">
    <!-- Navbar simples -->
    <nav class="test-nav">
      <div class="container">
        <router-link to="/" class="logo">
          <img src="@/assets/img/logo.png" alt="Unli" />
        </router-link>
        <span class="badge">Modo Teste</span>
      </div>
    </nav>

    <!-- Conteúdo principal -->
    <main class="test-content">
      <ConversationalOnboardingWizard 
        v-if="showAssistant"
        :initial-data="initialData"
        @complete="onComplete"
        @save-draft="onSaveDraft"
      />
      
      <!-- Botão de iniciar -->
      <div v-else class="start-screen">
        <div class="start-card">
          <div class="start-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
          </div>
          <h1>Assistente Conversacional</h1>
          <p>Crie seu site conversando com o Assistente Unli, nossa assistente de IA.</p>
          
          <div class="features">
            <div class="feature">
              <span class="icon">🎤</span>
              <span>Fale ou digite</span>
            </div>
            <div class="feature">
              <span class="icon">✨</span>
              <span>Preenchimento automático</span>
            </div>
            <div class="feature">
              <span class="icon">🎮</span>
              <span>Gamificado</span>
            </div>
          </div>

          <button class="btn-start" @click="startAssistant">
            Iniciar Conversa
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="5" y1="12" x2="19" y2="12"/>
              <polyline points="12 5 19 12 12 19"/>
            </svg>
          </button>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import { ref } from 'vue'
import ConversationalOnboardingWizard from '@/shared/Components/ConversationalOnboardingWizard.vue'

export default {
  name: 'TesteConversational',
  components: {
    ConversationalOnboardingWizard
  },
  setup() {
    const showAssistant = ref(false)
    const initialData = ref({})

    const startAssistant = () => {
      showAssistant.value = true
    }

    const onComplete = (data) => {
      console.log('✅ Onboarding completo:', data)
      alert('Onboarding finalizado! Veja os dados no console.')
    }

    const onSaveDraft = (data) => {
      console.log('💾 Rascunho salvo:', data)
      // Aqui poderia salvar em localStorage também
      localStorage.setItem('conversational_draft', JSON.stringify(data))
    }

    return {
      showAssistant,
      initialData,
      startAssistant,
      onComplete,
      onSaveDraft
    }
  }
}
</script>

<style lang="scss" scoped>
.page-conversational-test {
  min-height: 100vh;
  background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 100%);
  color: #fff;
}

.test-nav {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 64px;
  background: rgba(10, 10, 10, 0.9);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  z-index: 100;

  .container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 24px;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .logo img {
    height: 32px;
    filter: brightness(0) invert(1);
  }

  .badge {
    background: #ff6b35;
    color: #fff;
    padding: 4px 12px;
    border-radius: 100px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
}

.test-content {
  padding-top: 64px;
  min-height: calc(100vh - 64px);
}

.start-screen {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: calc(100vh - 64px);
  padding: 24px;
}

.start-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 24px;
  padding: 48px;
  max-width: 480px;
  text-align: center;
  backdrop-filter: blur(12px);

  .start-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
    box-shadow: 0 20px 40px rgba(99, 102, 241, 0.3);

    svg {
      stroke: #fff;
    }
  }

  h1 {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 12px;
  }

  p {
    color: rgba(255, 255, 255, 0.6);
    font-size: 16px;
    line-height: 1.6;
    margin: 0 0 32px;
  }
}

.features {
  display: flex;
  gap: 16px;
  justify-content: center;
  margin-bottom: 32px;
  flex-wrap: wrap;

  .feature {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.05);
    padding: 8px 16px;
    border-radius: 100px;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.8);

    .icon {
      font-size: 16px;
    }
  }
}

.btn-start {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: #fff;
  border: none;
  padding: 16px 32px;
  border-radius: 100px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 20px rgba(99, 102, 241, 0.3);

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(99, 102, 241, 0.4);
  }

  &:active {
    transform: translateY(0);
  }

  svg {
    transition: transform 0.3s ease;
  }

  &:hover svg {
    transform: translateX(4px);
  }
}
</style>
