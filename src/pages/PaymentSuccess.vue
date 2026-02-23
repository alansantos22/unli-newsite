<template>
  <div class="payment-success">
    <div class="success-container">
      
      <!-- Loading/Validating State -->
      <div v-if="isLoading || isValidating" class="success-card loading">
        <div class="success-icon">
          <i class="fas fa-spinner fa-spin"></i>
        </div>
        <h1>{{ isValidating ? 'Confirmando seu pagamento...' : 'Carregando informações...' }}</h1>
        <p class="success-message">
          {{ isValidating ? 'Estamos verificando seu pagamento com o banco. Isso leva apenas alguns segundos.' : 'Aguarde enquanto organizamos seus dados.' }}
        </p>
        <div v-if="isValidating" class="validation-progress">
          <div class="progress-bar">
            <div class="progress-fill"></div>
          </div>
        </div>
      </div>

      <!-- Success State - Pagamento Aprovado -->
      <div v-else-if="orderData && orderData.status === 'approved'" class="success-card success">
        <div class="success-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <h1>🎉 Pagamento Aprovado!</h1>
        <p class="success-message">
          Parabéns! Sua compra foi realizada com sucesso. Em breve você receberá todas as informações por email.
        </p>
        
        <!-- Validation Badge -->
        <div v-if="orderData.validated" class="validation-badge success">
          <i class="fas fa-shield-alt"></i>
          Pagamento confirmado pelo servidor
        </div>
        
        <!-- Order Details -->
        <div class="order-details">
          <h2>Detalhes do Pedido</h2>
          
          <div class="detail-group">
            <div class="detail-item">
              <span class="label">Número do Pedido:</span>
              <span class="value">#{{ orderData.order_id }}</span>
            </div>
            <div class="detail-item">
              <span class="label">Status:</span>
              <span class="value status approved">{{ formatStatus(orderData.status) }}</span>
            </div>
            <div class="detail-item" v-if="orderData.amount">
              <span class="label">Valor Pago:</span>
              <span class="value">R$ {{ formatPrice(orderData.amount) }}</span>
            </div>
            <div class="detail-item">
              <span class="label">Forma de Pagamento:</span>
              <span class="value">{{ formatPaymentType(orderData.payment_type || orderData.payment_method) }}</span>
            </div>
          </div>

          <!-- Next Steps -->
          <div class="next-steps">
            <h3>🚀 Próximos Passos</h3>
            <div class="steps-grid">
              <div class="step-card">
                <div class="step-icon">📧</div>
                <div class="step-content">
                  <h4>Email de Confirmação</h4>
                  <p>Você receberá um email com todos os detalhes da compra e instruções para começar.</p>
                </div>
              </div>
              <div class="step-card">
                <div class="step-icon">⚙️</div>
                <div class="step-content">
                  <h4>Configuração</h4>
                  <p>Nossa equipe entrará em contato para agendar a configuração do seu projeto.</p>
                </div>
              </div>
              <div class="step-card">
                <div class="step-icon">🎯</div>
                <div class="step-content">
                  <h4>Entrega</h4>
                  <p>Acompanhe o progresso do seu projeto através do nosso sistema.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
          <button @click="goToHome" class="btn btn-primary">
            <i class="fas fa-home"></i>
            Voltar ao Início
          </button>
          <button @click="contactSupport" class="btn btn-secondary">
            <i class="fas fa-question-circle"></i>
            Falar com Suporte
          </button>
        </div>
      </div>

      <!-- Pending State - Pagamento Pendente -->
      <div v-else-if="orderData && (orderData.status === 'pending' || orderData.status === 'in_process')" class="success-card pending">
        <div class="success-icon pending-icon">
          <i class="fas fa-clock"></i>
        </div>
        <h1>⏳ Pagamento em Processamento</h1>
        <p class="success-message">
          Seu pagamento está sendo processado. Isso pode levar alguns minutos.
        </p>
        
        <div class="pending-info">
          <p><strong>Número do Pedido:</strong> #{{ orderData.order_id }}</p>
          <p v-if="orderData.pending_reason">{{ orderData.pending_reason }}</p>
          <p>Assim que o pagamento for confirmado, você receberá um email de confirmação.</p>
        </div>

        <div class="action-buttons">
          <button @click="retryValidation" class="btn btn-primary">
            <i class="fas fa-sync-alt"></i>
            Verificar Novamente
          </button>
          <button @click="contactSupport" class="btn btn-secondary">
            <i class="fas fa-whatsapp"></i>
            Falar com Suporte
          </button>
        </div>
      </div>

      <!-- Error State -->
      <div v-else class="success-card error">
        <div class="success-icon">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h1>Ops! Algo não está certo</h1>
        <p class="success-message">
          Não conseguimos carregar os detalhes do seu pedido, mas não se preocupe!
        </p>
        <div class="error-actions">
          <p><strong>O pagamento pode ter sido processado com sucesso.</strong></p>
          <p>Entre em contato conosco com o número do pedido: <strong>{{ getOrderIdFromUrl() }}</strong></p>
          
          <div class="action-buttons">
            <button @click="goToHome" class="btn btn-primary">
              <i class="fas fa-home"></i>
              Voltar ao Início
            </button>
            <button @click="contactSupport" class="btn btn-secondary">
              <i class="fas fa-whatsapp"></i>
              Chamar no WhatsApp
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
export default {
  name: 'PaymentSuccess',
  data() {
    return {
      isLoading: true,
      orderData: null,
      error: null,
      isValidating: false,
      validationComplete: false
    }
  },
  async mounted() {
    await this.loadOrderData()
  },
  methods: {
    async loadOrderData() {
      try {
        const urlParams = new URLSearchParams(window.location.search)
        
        // Capturar parâmetros da URL (compatível Pagar.me + legado MP)
        const orderData = {
          order_id: urlParams.get('order_id') || this.extractOrderIdFromReference(urlParams.get('external_reference')),
          payment_id: urlParams.get('payment_id') || urlParams.get('collection_id'),
          pagarme_order_id: urlParams.get('pagarme_order_id'),
          status: urlParams.get('status') || urlParams.get('collection_status'),
          payment_type: urlParams.get('payment_type')
        }

        console.log('📦 PaymentSuccess - Dados da URL:', orderData)

        // Determinar o ID para validação
        let validationId = orderData.pagarme_order_id || orderData.payment_id
        
        // Se não temos um ID de pagamento direto, buscar do JSON salvo
        if (!validationId && orderData.order_id) {
          try {
            const orderResponse = await fetch(`/api/orders/${orderData.order_id}.json`)
            if (orderResponse.ok) {
              const savedOrder = await orderResponse.json()
              validationId = savedOrder.pagarme_order_id
              console.log('💳 Pagar.me order ID encontrado no JSON:', validationId)
            }
          } catch (e) {
            console.warn('⚠️ Não foi possível buscar JSON da ordem')
          }
        }

        // Se temos um ID para validar, consultar servidor
        if (validationId) {
          this.isValidating = true
          
          try {
            const validationResult = await this.validatePaymentOnServer(validationId, orderData.order_id)
            
            console.log('✅ Resultado da validação:', validationResult)
            
            if (validationResult.success && validationResult.approved) {
              // Pagamento confirmado pelo servidor!
              orderData.status = 'approved'
              orderData.validated = true
              orderData.server_data = validationResult.payment_data
              
              // Mesclar dados do servidor
              if (validationResult.payment_data) {
                orderData.order_id = validationResult.payment_data.order_id || orderData.order_id
                orderData.amount = validationResult.payment_data.amount
                orderData.payment_method = validationResult.payment_data.payment_method
              }
            } else if (validationResult.success && !validationResult.approved) {
              // Pagamento ainda pendente ou rejeitado
              orderData.status = validationResult.payment_data?.status || 'pending'
              orderData.validated = true
              orderData.pending_reason = validationResult.message
            } else {
              // Erro na validação, mas continuar com dados da URL
              console.warn('⚠️ Validação falhou, usando dados da URL')
              orderData.validated = false
            }
            
            this.validationComplete = true
            
          } catch (validationError) {
            console.error('❌ Erro na validação:', validationError)
            // Em caso de erro, usar dados da URL
            orderData.validated = false
          }
          
          this.isValidating = false
        }

        // Se temos dados básicos, usar eles
        if (orderData.order_id && orderData.status) {
          this.orderData = orderData
        }

        this.isLoading = false
      } catch (error) {
        console.error('Erro ao carregar dados do pedido:', error)
        this.error = error.message
        this.isLoading = false
      }
    },
    
    async validatePaymentOnServer(paymentId, internalOrderId = null) {
      console.log('🔍 Validando pagamento no servidor:', { paymentId, internalOrderId })
      
      const response = await fetch('/api/validate_payment.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          payment_id: paymentId,
          internal_order_id: internalOrderId
        })
      })
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }
      
      return await response.json()
    },
    
    extractOrderIdFromReference(reference) {
      if (!reference) return null
      
      // Formato: UNLI-{ORDER_ID}-{TIMESTAMP}
      const match = reference.match(/UNLI-(ORD-\d{8}-[A-Z0-9]+)-\d+/)
      if (match) return match[1]
      
      // Se já é um ORDER_ID
      if (reference.startsWith('ORD-')) return reference
      
      return reference
    },
    
    formatStatus(status) {
      const statusMap = {
        'approved': 'Aprovado',
        'pending': 'Pendente',
        'in_process': 'Em processamento',
        'rejected': 'Rejeitado',
        'cancelled': 'Cancelado'
      }
      return statusMap[status] || status
    },
    
    formatPaymentType(paymentType) {
      const typeMap = {
        'credit_card': 'Cartão de Crédito',
        'debit_card': 'Cartão de Débito',
        'pix': 'PIX',
        'bank_transfer': 'Transferência Bancária',
        'ticket': 'Boleto',
        'account_money': 'Saldo Digital'
      }
      return typeMap[paymentType] || paymentType || 'Não informado'
    },
    
    formatPrice(amount) {
      if (!amount) return '0,00'
      return parseFloat(amount).toFixed(2).replace('.', ',')
    },
    
    async retryValidation() {
      const urlParams = new URLSearchParams(window.location.search)
      const paymentId = urlParams.get('payment_id') || urlParams.get('pagarme_order_id') || urlParams.get('collection_id')
      const orderId = urlParams.get('order_id')
      
      let validationId = paymentId
      
      // Se não temos ID de pagamento, tentar buscar do JSON
      if (!validationId && orderId) {
        try {
          const orderResponse = await fetch(`/api/orders/${orderId}.json`)
          if (orderResponse.ok) {
            const savedOrder = await orderResponse.json()
            validationId = savedOrder.pagarme_order_id
          }
        } catch (e) { /* ignore */ }
      }
      
      if (!validationId) {
        alert('Não foi possível encontrar o ID do pagamento para verificar.')
        return
      }
      
      this.isValidating = true
      
      try {
        const result = await this.validatePaymentOnServer(validationId, orderId)
        
        if (result.success && result.approved) {
          this.orderData.status = 'approved'
          this.orderData.validated = true
          if (result.payment_data) {
            this.orderData.amount = result.payment_data.amount
            this.orderData.payment_method = result.payment_data.payment_method
          }
        } else if (result.success) {
          this.orderData.status = result.payment_data?.status || 'pending'
          this.orderData.pending_reason = result.message
        }
      } catch (error) {
        console.error('Erro ao verificar novamente:', error)
        alert('Não foi possível verificar o pagamento. Tente novamente em alguns segundos.')
      }
      
      this.isValidating = false
    },
    
    getOrderIdFromUrl() {
      const urlParams = new URLSearchParams(window.location.search)
      return urlParams.get('order_id') || urlParams.get('external_reference') || 'Não disponível'
    },
    
    goToHome() {
      this.$router.push('/')
    },
    
    contactSupport() {
      // Número do WhatsApp da empresa (substitua pelo número real)
      const whatsappNumber = '5511999999999' // Substitua pelo seu número
      const message = `Olá! Acabei de realizar uma compra (Pedido: ${this.getOrderIdFromUrl()}) e preciso de ajuda.`
      const whatsappUrl = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`
      window.open(whatsappUrl, '_blank')
    }
  }
}
</script>

<style scoped>
.payment-success {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.success-container {
  width: 100%;
  max-width: 800px;
}

.success-card {
  background: white;
  border-radius: 20px;
  padding: 40px;
  text-align: center;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  animation: slideUp 0.6s ease-out;
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

.success-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.success-icon .fa-check-circle {
  color: #28a745;
}

.success-icon .fa-spinner {
  color: #007bff;
}

.success-icon .fa-exclamation-triangle {
  color: #ffc107;
}

.success-icon .fa-clock,
.pending-icon {
  color: #f59e0b;
}

/* Validation Progress Bar */
.validation-progress {
  margin-top: 1.5rem;
  width: 100%;
  max-width: 300px;
  margin-left: auto;
  margin-right: auto;
}

.progress-bar {
  height: 4px;
  background: #e5e7eb;
  border-radius: 2px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #007bff, #00d4ff);
  animation: progress 1.5s ease-in-out infinite;
  width: 40%;
}

@keyframes progress {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(350%); }
}

/* Validation Badge */
.validation-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;
  margin-bottom: 1.5rem;
}

.validation-badge.success {
  background: #d4edda;
  color: #155724;
}

/* Pending State Styles */
.success-card.pending {
  border-top: 4px solid #f59e0b;
}

.pending-info {
  background: #fef3c7;
  border: 1px solid #fcd34d;
  border-radius: 10px;
  padding: 1.5rem;
  margin: 1.5rem 0;
  text-align: left;
}

.pending-info p {
  margin-bottom: 0.5rem;
  color: #92400e;
}

.pending-info p:last-child {
  margin-bottom: 0;
}

h1 {
  color: #333;
  font-size: 2rem;
  margin-bottom: 1rem;
  font-weight: 700;
}

.success-message {
  color: #666;
  font-size: 1.1rem;
  margin-bottom: 2rem;
  line-height: 1.6;
}

.order-details {
  text-align: left;
  margin: 2rem 0;
}

.order-details h2 {
  color: #333;
  font-size: 1.4rem;
  margin-bottom: 1rem;
  text-align: center;
}

.detail-group {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid #dee2e6;
}

.detail-item:last-child {
  border-bottom: none;
}

.label {
  color: #6c757d;
  font-weight: 500;
}

.value {
  font-weight: 600;
  color: #333;
}

.status.approved {
  color: #28a745;
  background: #d4edda;
  padding: 0.25rem 0.5rem;
  border-radius: 15px;
  font-size: 0.85rem;
}

.next-steps {
  margin: 2rem 0;
}

.next-steps h3 {
  color: #333;
  font-size: 1.2rem;
  margin-bottom: 1rem;
  text-align: center;
}

.steps-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.step-card {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 1rem;
  text-align: center;
}

.step-icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.step-content h4 {
  color: #333;
  font-size: 1rem;
  margin-bottom: 0.5rem;
}

.step-content p {
  color: #666;
  font-size: 0.85rem;
  line-height: 1.4;
}

.action-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
  margin-top: 2rem;
}

.btn {
  padding: 12px 24px;
  border-radius: 25px;
  border: none;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
  text-decoration: none;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-primary:hover {
  background: #0056b3;
  transform: translateY(-2px);
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background: #545b62;
  transform: translateY(-2px);
}

.error-actions {
  text-align: left;
  background: #fff3cd;
  border: 1px solid #ffeaa7;
  border-radius: 10px;
  padding: 1.5rem;
  margin: 1rem 0;
}

.error-actions p {
  margin-bottom: 0.5rem;
  color: #856404;
}

.loading .success-icon .fa-spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
  .success-card {
    padding: 20px;
  }
  
  h1 {
    font-size: 1.6rem;
  }
  
  .success-icon {
    font-size: 3rem;
  }
  
  .steps-grid {
    grid-template-columns: 1fr;
  }
  
  .action-buttons {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>