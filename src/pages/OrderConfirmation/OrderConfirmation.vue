<template>
  <div class="order-confirmation-page">
    <div class="confirmation-container">
      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner-container">
          <div class="spinner"></div>
        </div>
        <h2>Processando seu pedido...</h2>
        <p class="loading-message">Estamos confirmando seu pagamento com o Mercado Pago</p>
        <div class="loading-info">
          <div class="info-item">
            <span class="icon">⏱️</span>
            <p>Este processo pode levar alguns minutos</p>
          </div>
          <div class="info-item">
            <span class="icon">🔒</span>
            <p>Por favor, não feche esta página</p>
          </div>
        </div>
      </div>

      <!-- Success State -->
      <div v-else-if="orderStatus === 'approved'" class="success-state">
        <div class="success-animation">
          <div class="checkmark-circle">
            <svg class="checkmark" viewBox="0 0 52 52">
              <circle class="checkmark-circle-bg" cx="26" cy="26" r="25" fill="none"/>
              <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
            </svg>
          </div>
        </div>

        <h1>🎉 Pagamento Confirmado!</h1>
        <p class="subtitle">Sua compra foi processada com sucesso</p>

        <div class="order-details-card">
          <h3>📋 Detalhes do Pedido</h3>
          <div class="detail-row">
            <span class="label">Pedido Nº:</span>
            <code>{{ paymentId }}</code>
          </div>
          <div class="detail-row">
            <span class="label">Valor Pago:</span>
            <strong class="amount">R$ {{ formatAmount(amount) }}</strong>
          </div>
          <div class="detail-row">
            <span class="label">E-mail:</span>
            <span>{{ email }}</span>
          </div>
          <div class="detail-row">
            <span class="label">Data:</span>
            <span>{{ formattedDate }}</span>
          </div>
        </div>

        <!-- Email Instruction -->
        <div class="email-instruction-card">
          <div class="email-icon-large">📧</div>
          <h3>Próximos Passos</h3>
          <p class="instruction-text">
            Em breve você receberá um <strong>e-mail</strong> no endereço <strong>{{ email }}</strong> 
            com um link para preencher o formulário de configuração do seu site.
          </p>
          
          <div class="timeline">
            <div class="timeline-item">
              <div class="timeline-icon">✅</div>
              <div class="timeline-content">
                <strong>Pagamento confirmado</strong>
                <span>Concluído agora</span>
              </div>
            </div>
            <div class="timeline-item">
              <div class="timeline-icon">📨</div>
              <div class="timeline-content">
                <strong>E-mail enviado</strong>
                <span>Pode levar alguns minutos</span>
              </div>
            </div>
            <div class="timeline-item">
              <div class="timeline-icon">📝</div>
              <div class="timeline-content">
                <strong>Preencher formulário</strong>
                <span>Através do link recebido</span>
              </div>
            </div>
            <div class="timeline-item">
              <div class="timeline-icon">🚀</div>
              <div class="timeline-content">
                <strong>Produção do site</strong>
                <span>Após envio do formulário</span>
              </div>
            </div>
          </div>

          <div class="tips-box">
            <h4>💡 Dicas Importantes:</h4>
            <ul>
              <li>Verifique sua <strong>caixa de entrada</strong> e <strong>spam</strong></li>
              <li>O e-mail pode levar até <strong>15 minutos</strong> para chegar</li>
              <li>O link do formulário é <strong>único e personalizado</strong> para você</li>
              <li>Guarde bem este e-mail para referência futura</li>
            </ul>
          </div>
        </div>

        <div class="actions">
          <button @click="goHome" class="btn-primary">
            🏠 Voltar ao Início
          </button>
        </div>
      </div>

      <!-- Pending State -->
      <div v-else-if="orderStatus === 'pending'" class="pending-state">
        <div class="pending-icon">
          <div class="clock-icon">⏳</div>
        </div>
        <h1>Pagamento em Análise</h1>
        <p class="subtitle">Aguardando confirmação do Mercado Pago</p>

        <div class="info-card">
          <div class="detail-row">
            <span class="label">Pedido Nº:</span>
            <code>{{ paymentId }}</code>
          </div>
          <div class="detail-row">
            <span class="label">Status:</span>
            <span class="badge pending">Em análise</span>
          </div>
        </div>

        <div class="pending-info-box">
          <h3>⏱️ O que está acontecendo?</h3>
          <p>Seu pagamento está sendo processado pelo Mercado Pago. Isso pode acontecer por alguns motivos:</p>
          <ul>
            <li>Pagamento via boleto ou Pix aguardando compensação</li>
            <li>Análise de segurança em andamento</li>
            <li>Verificação adicional necessária</li>
          </ul>
          
          <div class="pending-actions-info">
            <h4>📧 O que fazer agora?</h4>
            <p>Você receberá um e-mail assim que o pagamento for confirmado. Não é necessário fazer nada neste momento.</p>
          </div>
        </div>

        <div class="actions">
          <button @click="checkStatus" class="btn-primary" :disabled="checkingStatus">
            <span v-if="checkingStatus">🔄 Verificando...</span>
            <span v-else>🔄 Verificar Status Agora</span>
          </button>
          <button @click="goHome" class="btn-secondary">
            🏠 Voltar ao Início
          </button>
        </div>
      </div>

      <!-- Error State -->
      <div v-else class="error-state">
        <div class="error-icon">
          <div class="x-mark">❌</div>
        </div>
        <h1>Pagamento Não Aprovado</h1>
        <p class="subtitle">{{ errorMessage || 'Não foi possível processar seu pagamento' }}</p>

        <div class="error-card">
          <h3>🤔 Possíveis motivos:</h3>
          <ul>
            <li><strong>Cartão recusado</strong> pela operadora</li>
            <li><strong>Saldo insuficiente</strong> na conta</li>
            <li><strong>Dados incorretos</strong> do cartão</li>
            <li><strong>Limite de crédito</strong> excedido</li>
            <li><strong>Problema temporário</strong> no Mercado Pago</li>
          </ul>
        </div>

        <div class="help-box">
          <h3>💡 O que fazer?</h3>
          <div class="help-options">
            <div class="help-option">
              <span class="help-icon">💳</span>
              <div>
                <strong>Tente outro cartão</strong>
                <p>Use um cartão diferente</p>
              </div>
            </div>
            <div class="help-option">
              <span class="help-icon">📱</span>
              <div>
                <strong>Verifique com seu banco</strong>
                <p>Confirme se há algum bloqueio</p>
              </div>
            </div>
            <div class="help-option">
              <span class="help-icon">🔄</span>
              <div>
                <strong>Tente novamente</strong>
                <p>Às vezes é apenas temporário</p>
              </div>
            </div>
          </div>
        </div>

        <div class="actions">
          <button @click="tryAgain" class="btn-primary">
            🔄 Tentar Novamente
          </button>
          <button @click="goHome" class="btn-secondary">
            🏠 Voltar ao Início
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

// State
const loading = ref(true)
const orderStatus = ref('')
const paymentId = ref('')
const amount = ref('')
const email = ref('')
const errorMessage = ref('')
const checkingStatus = ref(false)

// Computed
const formattedDate = computed(() => {
  return new Date().toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
})

// Methods
function formatAmount(value) {
  if (!value) return '0,00'
  return parseFloat(value).toFixed(2).replace('.', ',')
}

onMounted(async () => {
  // Obter dados da URL
  paymentId.value = route.query.payment_id || ''
  orderStatus.value = route.query.status || ''
  amount.value = route.query.amount || ''
  email.value = route.query.email || ''

  // Se não tem dados, redirecionar
  if (!paymentId.value || !orderStatus.value) {
    router.push('/')
    return
  }

  // Simular processamento (mínimo 2 segundos para mostrar loading)
  await new Promise(resolve => setTimeout(resolve, 2000))

  loading.value = false

  // Se pagamento aprovado, registrar no console
  if (orderStatus.value === 'approved') {
    console.log('%c💰 PAGAMENTO APROVADO', 'background: #10B981; color: white; font-size: 16px; padding: 10px; font-weight: bold;')
    console.log('%c📧 E-mail será enviado para:', 'font-weight: bold; color: #059669;', email.value)
    console.log('%c🎫 Pedido Nº:', 'font-weight: bold; color: #059669;', paymentId.value)
    console.log('%c💵 Valor:', 'font-weight: bold; color: #059669;', `R$ ${formatAmount(amount.value)}`)
  }
})

async function checkStatus() {
  checkingStatus.value = true
  
  try {
    // Simular verificação (em produção, fazer requisição real)
    await new Promise(resolve => setTimeout(resolve, 2000))
    
    // Aqui você faria:
    // const response = await fetch(`/api/check_payment_status.php?payment_id=${paymentId.value}`)
    // const result = await response.json()
    // if (result.status === 'approved') {
    //   orderStatus.value = 'approved'
    // }
    
    alert('Status ainda pendente. Por favor, aguarde o e-mail de confirmação.')
  } catch (error) {
    console.error('Erro ao verificar status:', error)
    alert('Erro ao verificar status. Tente novamente mais tarde.')
  } finally {
    checkingStatus.value = false
  }
}

function tryAgain() {
  // Voltar para a página de checkout
  router.push('/checkout')
}

function goHome() {
  router.push('/')
}
</script>

<style scoped>
.order-confirmation-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.confirmation-container {
  max-width: 700px;
  width: 100%;
  background: white;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  padding: 3rem;
  animation: slideUp 0.5s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* ================================
   LOADING STATE
   ================================ */
.loading-state {
  text-align: center;
}

.spinner-container {
  margin-bottom: 2rem;
}

.spinner {
  width: 70px;
  height: 70px;
  border: 5px solid #f3f4f6;
  border-top: 5px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.loading-state h2 {
  margin: 0 0 1rem 0;
  color: #1f2937;
  font-size: 1.8rem;
}

.loading-message {
  color: #6b7280;
  font-size: 1.1rem;
  margin-bottom: 2rem;
}

.loading-info {
  background: #fef3c7;
  border-radius: 12px;
  padding: 1.5rem;
  border-left: 4px solid #f59e0b;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.info-item:last-child {
  margin-bottom: 0;
}

.info-item .icon {
  font-size: 1.5rem;
}

.info-item p {
  margin: 0;
  color: #92400e;
  font-weight: 500;
}

/* ================================
   SUCCESS STATE
   ================================ */
.success-state {
  text-align: center;
}

.success-animation {
  margin-bottom: 2rem;
}

.checkmark-circle {
  width: 100px;
  height: 100px;
  margin: 0 auto;
  position: relative;
}

.checkmark {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  display: block;
  stroke-width: 3;
  stroke: #10B981;
  stroke-miterlimit: 10;
  animation: fill 0.4s ease-in-out 0.4s forwards, scale 0.3s ease-in-out 0.9s both;
}

.checkmark-circle-bg {
  stroke: #10B981;
  stroke-dasharray: 166;
  stroke-dashoffset: 166;
  stroke-width: 3;
  animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.checkmark-check {
  transform-origin: 50% 50%;
  stroke-dasharray: 48;
  stroke-dashoffset: 48;
  animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
}

@keyframes stroke {
  100% {
    stroke-dashoffset: 0;
  }
}

@keyframes scale {
  0%, 100% {
    transform: none;
  }
  50% {
    transform: scale3d(1.1, 1.1, 1);
  }
}

@keyframes fill {
  100% {
    box-shadow: inset 0px 0px 0px 30px #10B981;
  }
}

h1 {
  margin: 0 0 0.5rem 0;
  color: #1f2937;
  font-size: 2.2rem;
}

.subtitle {
  color: #6b7280;
  margin: 0 0 2.5rem 0;
  font-size: 1.2rem;
}

/* Order Details Card */
.order-details-card {
  background: #f9fafb;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  text-align: left;
}

.order-details-card h3 {
  margin: 0 0 1rem 0;
  color: #374151;
  font-size: 1.1rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid #e5e7eb;
}

.detail-row:last-child {
  border-bottom: none;
}

.label {
  color: #6b7280;
  font-weight: 500;
}

code {
  background: #e5e7eb;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-family: 'Courier New', monospace;
  font-size: 0.9rem;
}

.amount {
  color: #10B981;
  font-size: 1.3rem;
}

/* Email Instruction Card */
.email-instruction-card {
  background: linear-gradient(135deg, #e0e7ff 0%, #dbeafe 100%);
  border-radius: 16px;
  padding: 2rem;
  margin-bottom: 2rem;
  border: 2px solid #818cf8;
}

.email-icon-large {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.email-instruction-card h3 {
  margin: 0 0 1rem 0;
  color: #1e40af;
  font-size: 1.4rem;
}

.instruction-text {
  color: #1e3a8a;
  line-height: 1.8;
  margin-bottom: 2rem;
  font-size: 1.05rem;
}

/* Timeline */
.timeline {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.timeline-item {
  display: flex;
  gap: 1rem;
  padding: 1rem 0;
  border-left: 2px solid #e5e7eb;
  padding-left: 1.5rem;
  position: relative;
}

.timeline-item:last-child {
  border-left-color: transparent;
}

.timeline-icon {
  position: absolute;
  left: -14px;
  background: white;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  border: 2px solid #e5e7eb;
}

.timeline-content {
  flex: 1;
}

.timeline-content strong {
  display: block;
  color: #374151;
  margin-bottom: 0.25rem;
}

.timeline-content span {
  color: #6b7280;
  font-size: 0.9rem;
}

/* Tips Box */
.tips-box {
  background: rgba(255, 255, 255, 0.7);
  border-radius: 12px;
  padding: 1.5rem;
  text-align: left;
}

.tips-box h4 {
  margin: 0 0 1rem 0;
  color: #1e40af;
  font-size: 1rem;
}

.tips-box ul {
  margin: 0;
  padding-left: 1.5rem;
  color: #1e3a8a;
}

.tips-box li {
  margin-bottom: 0.75rem;
  line-height: 1.6;
}

/* ================================
   PENDING STATE
   ================================ */
.pending-state {
  text-align: center;
}

.pending-icon {
  margin-bottom: 2rem;
}

.clock-icon {
  font-size: 5rem;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.7;
    transform: scale(1.05);
  }
}

.info-card {
  background: #fef3c7;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
}

.badge {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 600;
}

.badge.pending {
  background: #fbbf24;
  color: #78350f;
}

.pending-info-box {
  background: #eff6ff;
  border-radius: 12px;
  padding: 2rem;
  margin-bottom: 2rem;
  text-align: left;
}

.pending-info-box h3,
.pending-info-box h4 {
  margin: 0 0 1rem 0;
  color: #1e40af;
}

.pending-info-box ul {
  margin: 0 0 1.5rem 0;
  padding-left: 1.5rem;
  color: #1e3a8a;
}

.pending-info-box li {
  margin-bottom: 0.5rem;
}

.pending-actions-info {
  background: rgba(255, 255, 255, 0.7);
  border-radius: 8px;
  padding: 1rem;
  margin-top: 1.5rem;
}

/* ================================
   ERROR STATE
   ================================ */
.error-state {
  text-align: center;
}

.error-icon {
  margin-bottom: 2rem;
}

.x-mark {
  font-size: 5rem;
  animation: shake 0.5s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-10px); }
  75% { transform: translateX(10px); }
}

.error-card {
  background: #fef2f2;
  border-radius: 12px;
  padding: 2rem;
  margin-bottom: 2rem;
  text-align: left;
  border-left: 4px solid #ef4444;
}

.error-card h3 {
  margin: 0 0 1rem 0;
  color: #991b1b;
}

.error-card ul {
  margin: 0;
  padding-left: 1.5rem;
  color: #7f1d1d;
}

.error-card li {
  margin-bottom: 0.75rem;
}

.help-box {
  background: #f0fdf4;
  border-radius: 12px;
  padding: 2rem;
  margin-bottom: 2rem;
  text-align: left;
}

.help-box h3 {
  margin: 0 0 1.5rem 0;
  color: #065f46;
}

.help-options {
  display: grid;
  gap: 1rem;
}

.help-option {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
  background: white;
  padding: 1rem;
  border-radius: 8px;
}

.help-icon {
  font-size: 2rem;
  flex-shrink: 0;
}

.help-option strong {
  display: block;
  color: #065f46;
  margin-bottom: 0.25rem;
}

.help-option p {
  margin: 0;
  color: #047857;
  font-size: 0.9rem;
}

/* ================================
   ACTIONS
   ================================ */
.actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-primary,
.btn-secondary {
  flex: 1;
  padding: 1.1rem;
  border: none;
  border-radius: 12px;
  font-size: 1.05rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: #f3f4f6;
  color: #374151;
}

.btn-secondary:hover {
  background: #e5e7eb;
}

/* ================================
   RESPONSIVE
   ================================ */
@media (max-width: 640px) {
  .order-confirmation-page {
    padding: 1rem;
  }

  .confirmation-container {
    padding: 2rem;
  }

  h1 {
    font-size: 1.8rem;
  }

  .actions {
    flex-direction: column;
  }

  .timeline-item {
    padding-left: 1rem;
  }
}
</style>
