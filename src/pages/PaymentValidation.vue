<template>
  <div class="payment-validation">
    <div class="validation-container">
      
      <!-- Loading State -->
      <div v-if="isValidating" class="validation-card loading">
        <div class="validation-icon">
          <i class="fas fa-spinner fa-spin"></i>
        </div>
        <h1>Validando Pagamento</h1>
        <p class="validation-message">
          Aguarde enquanto confirmamos seu pagamento com o banco...
        </p>
        <div class="progress-bar">
          <div class="progress-fill" :style="{ width: progress + '%' }"></div>
        </div>
      </div>

      <!-- Success State -->
      <div v-else-if="validationStatus === 'success'" class="validation-card success">
        <div class="validation-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <h1>Pagamento Confirmado!</h1>
        <p class="validation-message">
          Seu pagamento foi processado com sucesso. Redirecionando...
        </p>
        <div v-if="paymentData" class="payment-details">
          <div class="detail-item">
            <span class="label">Pedido:</span>
            <span class="value">#{{ paymentData.order_id }}</span>
          </div>
          <div class="detail-item">
            <span class="label">Valor:</span>
            <span class="value">R$ {{ formatPrice(paymentData.amount) }}</span>
          </div>
          <div class="detail-item">
            <span class="label">Método:</span>
            <span class="value">{{ formatPaymentMethod(paymentData.payment_method) }}</span>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="validationStatus === 'error'" class="validation-card error">
        <div class="validation-icon">
          <i class="fas fa-times-circle"></i>
        </div>
        <h1>{{ errorTitle }}</h1>
        <p class="validation-message">
          {{ errorMessage }}
        </p>
        
        <div v-if="paymentData && paymentData.status" class="error-details">
          <div class="detail-item">
            <span class="label">Status:</span>
            <span class="value status-badge" :class="paymentData.status">
              {{ formatStatus(paymentData.status) }}
            </span>
          </div>
          <div v-if="paymentData.order_id" class="detail-item">
            <span class="label">Pedido:</span>
            <span class="value">#{{ paymentData.order_id }}</span>
          </div>
        </div>

        <div class="error-actions">
          <button @click="tryAgain" class="btn-primary">
            <i class="fas fa-redo"></i>
            Tentar Novamente
          </button>
          <router-link to="/configurador" class="btn-secondary">
            <i class="fas fa-home"></i>
            Voltar ao Início
          </router-link>
        </div>

        <!-- Help Section -->
        <div class="help-section">
          <h3><i class="fas fa-question-circle"></i> Precisa de Ajuda?</h3>
          <p>Se o problema persistir, entre em contato conosco:</p>
          <div class="contact-options">
            <a href="mailto:contato@unli.com.br" class="contact-btn">
              <i class="fas fa-envelope"></i>
              E-mail
            </a>
            <a href="https://wa.me/5511999999999" class="contact-btn" target="_blank">
              <i class="fab fa-whatsapp"></i>
              WhatsApp
            </a>
          </div>
        </div>
      </div>

      <!-- Invalid Access -->
      <div v-else class="validation-card invalid">
        <div class="validation-icon">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h1>Acesso Inválido</h1>
        <p class="validation-message">
          Esta página só pode ser acessada após um pagamento válido.
        </p>
        <div class="error-actions">
          <router-link to="/configurador" class="btn-primary">
            <i class="fas fa-home"></i>
            Ir para o Configurador
          </router-link>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

// State
const isValidating = ref(true)
const validationStatus = ref('loading') // 'loading', 'success', 'error', 'invalid'
const errorTitle = ref('')
const errorMessage = ref('')
const paymentData = ref(null)
const progress = ref(0)

// Progress animation
let progressInterval = null

const startProgressAnimation = () => {
  progress.value = 0
  progressInterval = setInterval(() => {
    if (progress.value < 90) {
      progress.value += Math.random() * 15
    }
  }, 200)
}

const completeProgress = () => {
  if (progressInterval) {
    clearInterval(progressInterval)
    progressInterval = null
  }
  progress.value = 100
}

const validatePayment = async (paymentId) => {
  try {
    console.log('🔍 Validando pagamento:', paymentId)
    
    const response = await fetch('/api/validate_payment.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        payment_id: paymentId
      })
    })

    const result = await response.json()
    
    console.log('📥 Resposta da validação:', result)

    completeProgress()
    isValidating.value = false

    if (result.success && result.approved) {
      // Pagamento aprovado
      validationStatus.value = 'success'
      paymentData.value = result.payment_data
      
      // Redirecionar para dashboard após 3 segundos
      setTimeout(() => {
        router.push('/configurador?success=true&order=' + result.payment_data.order_id)
      }, 3000)
      
    } else {
      // Pagamento não aprovado
      validationStatus.value = 'error'
      paymentData.value = result.payment_data
      
      // Definir mensagens de erro específicas
      if (result.payment_data) {
        const status = result.payment_data.status
        
        switch (status) {
          case 'pending':
          case 'in_process':
            errorTitle.value = 'Pagamento Pendente'
            errorMessage.value = 'Seu pagamento ainda está sendo processado. Aguarde alguns minutos e tente novamente.'
            break
          case 'rejected':
            errorTitle.value = 'Pagamento Rejeitado'
            errorMessage.value = 'Seu pagamento foi rejeitado pelo banco. Verifique os dados do cartão e tente novamente.'
            break
          case 'cancelled':
            errorTitle.value = 'Pagamento Cancelado'
            errorMessage.value = 'O pagamento foi cancelado. Você pode tentar realizar um novo pagamento.'
            break
          default:
            errorTitle.value = 'Problema no Pagamento'
            errorMessage.value = result.message || 'Houve um problema com seu pagamento.'
        }
      } else {
        errorTitle.value = 'Pagamento Não Encontrado'
        errorMessage.value = result.message || 'Não foi possível encontrar este pagamento.'
      }
    }

  } catch (error) {
    console.error('❌ Erro na validação:', error)
    
    completeProgress()
    isValidating.value = false
    validationStatus.value = 'error'
    errorTitle.value = 'Erro de Conexão'
    errorMessage.value = 'Não foi possível validar seu pagamento. Verifique sua conexão e tente novamente.'
  }
}

const tryAgain = () => {
  const paymentId = route.query.collection_id || route.query.payment_id
  
  if (paymentId) {
    isValidating.value = true
    validationStatus.value = 'loading'
    startProgressAnimation()
    setTimeout(() => validatePayment(paymentId), 1000)
  } else {
    router.push('/configurador')
  }
}

const formatPrice = (amount) => {
  if (!amount) return '0,00'
  return parseFloat(amount).toFixed(2).replace('.', ',')
}

const formatPaymentMethod = (method) => {
  const methods = {
    'pix': 'PIX',
    'credit_card': 'Cartão de Crédito',
    'debit_card': 'Cartão de Débito',
    'ticket': 'Boleto',
    'bank_transfer': 'Transferência'
  }
  return methods[method] || method || 'Não informado'
}

const formatStatus = (status) => {
  const statuses = {
    'approved': 'Aprovado',
    'pending': 'Pendente',
    'in_process': 'Processando',
    'rejected': 'Rejeitado',
    'cancelled': 'Cancelado',
    'refunded': 'Estornado'
  }
  return statuses[status] || status || 'Desconhecido'
}

onMounted(() => {
  // Verificar se temos um payment ID na URL
  const paymentId = route.query.collection_id || route.query.payment_id
  
  console.log('💳 PaymentValidation iniciado:', {
    query: route.query,
    paymentId
  })
  
  if (!paymentId) {
    // Acesso inválido - sem payment ID
    isValidating.value = false
    validationStatus.value = 'invalid'
    return
  }
  
  // Iniciar animação de progresso
  startProgressAnimation()
  
  // Aguardar um pouco para dar sensação de validação
  setTimeout(() => {
    validatePayment(paymentId)
  }, 2000)
})
</script>

<style lang="scss" scoped>
$success-color: #22c55e;
$error-color: #ef4444;
$warning-color: #f59e0b;
$primary-color: #e67e22;
$gray-100: #f3f4f6;
$gray-200: #e5e7eb;
$gray-600: #5a5d6a;
$gray-700: #2a2d35;

.payment-validation {
  min-height: 100vh;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
}

.validation-container {
  max-width: 600px;
  width: 100%;
}

.validation-card {
  background: white;
  border-radius: 20px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  padding: 3rem 2rem;
  text-align: center;

  .validation-icon {
    margin-bottom: 2rem;
    
    i {
      font-size: 4rem;
    }
  }

  h1 {
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 1rem 0;
  }

  .validation-message {
    font-size: 1.125rem;
    color: $gray-600;
    margin: 0 0 2rem 0;
    line-height: 1.6;
  }

  &.loading {
    border-top: 5px solid $primary-color;
    
    .validation-icon i {
      color: $primary-color;
    }
    
    h1 {
      color: $primary-color;
    }
  }

  &.success {
    border-top: 5px solid $success-color;
    
    .validation-icon i {
      color: $success-color;
    }
    
    h1 {
      color: $success-color;
    }
  }

  &.error, &.invalid {
    border-top: 5px solid $error-color;
    
    .validation-icon i {
      color: $error-color;
    }
    
    h1 {
      color: $error-color;
    }
  }
}

.progress-bar {
  width: 100%;
  height: 8px;
  background: $gray-200;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 1rem;

  .progress-fill {
    height: 100%;
    background: linear-gradient(90deg, $primary-color, #d35400);
    border-radius: 4px;
    transition: width 0.3s ease;
  }
}

.payment-details, .error-details {
  background: $gray-100;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  text-align: left;

  .detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e5e7eb;

    &:last-child {
      border-bottom: none;
    }

    .label {
      font-weight: 600;
      color: $gray-700;
    }

    .value {
      font-weight: 500;
      color: $gray-600;
      
      &.status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 600;
        
        &.approved { background: #dcfce7; color: #166534; }
        &.pending { background: #fef3c7; color: #92400e; }
        &.rejected { background: #fecaca; color: #dc2626; }
        &.cancelled { background: $gray-200; color: $gray-700; }
      }
    }
  }
}

.error-actions {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 2rem;

  @media (min-width: 480px) {
    flex-direction: row;
    justify-content: center;
  }
}

.btn-primary, .btn-secondary {
  padding: 1rem 2rem;
  border-radius: 12px;
  font-weight: 600;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.2s ease;
  border: none;
  cursor: pointer;
  font-size: 1rem;
}

.btn-primary {
  background: linear-gradient(135deg, $primary-color, #d35400);
  color: white;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(230, 126, 34, 0.3);
  }
}

.btn-secondary {
  background: transparent;
  color: $gray-600;
  border: 2px solid $gray-600;

  &:hover {
    background: $gray-600;
    color: white;
  }
}

.help-section {
  background: #f8fafc;
  border: 1px solid $gray-200;
  border-radius: 12px;
  padding: 1.5rem;
  text-align: left;

  h3 {
    color: $gray-700;
    margin: 0 0 1rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.125rem;
  }

  p {
    color: $gray-600;
    margin: 0 0 1rem 0;
  }

  .contact-options {
    display: flex;
    gap: 1rem;
  }

  .contact-btn {
    padding: 0.75rem 1.5rem;
    background: white;
    color: $gray-700;
    border: 1px solid $gray-200;
    border-radius: 8px;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;

    &:hover {
      border-color: $primary-color;
      color: $primary-color;
    }
  }
}

@media (max-width: 480px) {
  .validation-card {
    padding: 2rem 1.5rem;
  }
  
  .validation-icon i {
    font-size: 3rem !important;
  }
  
  h1 {
    font-size: 1.5rem !important;
  }
  
  .contact-options {
    flex-direction: column;
    gap: 0.5rem;
  }
}
</style>