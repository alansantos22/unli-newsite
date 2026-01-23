<template>
  <div class="checkout-payment">
    <!-- Order Summary -->
    <div class="order-summary">
      <h2>🛒 Finalizar Pagamento</h2>
      <div class="order-info">
        <p><strong>Pedido:</strong> {{ orderId }}</p>
        <p><strong>Valor:</strong> R$ {{ transactionAmount.toFixed(2) }}</p>
        <p><strong>Descrição:</strong> {{ productDescription }}</p>
      </div>
    </div>

    <!-- Debug Panel -->
    <div v-if="debug" class="debug-panel">
      <h4>🐛 Debug Mode</h4>
      <pre>{{ debugInfo }}</pre>
    </div>

    <!-- Payment Method Selector -->
    <div class="payment-selector">
      <button 
        @click="selectPaymentMethod('credit_card')"
        :class="['selector-btn', { active: paymentMethod === 'credit_card' }]"
      >
        💳 Cartão de Crédito
      </button>
      <button 
        @click="selectPaymentMethod('pix')"
        :class="['selector-btn', { active: paymentMethod === 'pix' }]"
      >
        📱 Pix
      </button>
    </div>

    <!-- Card Payment Form -->
    <form id="form-checkout" v-show="paymentMethod === 'credit_card'" class="payment-form card-form">
      <h3>Dados do Cartão</h3>

      <!-- Card Number -->
      <div class="form-group">
        <label for="cardNumber">Número do Cartão</label>
        <div id="form-checkout__cardNumber" class="card-field"></div>
      </div>

      <!-- Expiration Date -->
      <div class="form-group">
        <label for="expirationDate">Validade</label>
        <div id="form-checkout__expirationDate" class="card-field"></div>
      </div>

      <!-- Security Code (CVV) -->
      <div class="form-group">
        <label for="securityCode">CVV</label>
        <div id="form-checkout__securityCode" class="card-field"></div>
      </div>

      <!-- Cardholder Name -->
      <div class="form-group">
        <label for="cardholderName">Nome no Cartão</label>
        <input 
          id="form-checkout__cardholderName" 
          v-model="cardholderName"
          type="text" 
          placeholder="Nome como está no cartão"
          class="form-input"
        />
      </div>

      <!-- Installments -->
      <div class="form-group">
        <label for="installments">Parcelas</label>
        <select 
          id="form-checkout__installments" 
          v-model="installments"
          class="form-select"
        >
          <option value="">Escolha o número de parcelas</option>
        </select>
      </div>

      <!-- Issuer (Bank) -->
      <select id="form-checkout__issuer" v-model="issuer" style="display:none;"></select>

      <!-- Payer Info -->
      <div class="form-group">
        <label for="email">E-mail</label>
        <input 
          id="form-checkout__email" 
          v-model="email"
          type="email" 
          placeholder="seu@email.com"
          class="form-input"
        />
      </div>

      <div class="form-group">
        <label for="docType">Tipo de Documento</label>
        <select 
          id="form-checkout__identificationType" 
          v-model="identificationType"
          class="form-select"
        >
          <option value="CPF">CPF</option>
          <option value="CNPJ">CNPJ</option>
        </select>
      </div>

      <div class="form-group">
        <label for="docNumber">Número do Documento</label>
        <input 
          id="form-checkout__identificationNumber" 
          v-model="identificationNumber"
          type="text" 
          placeholder="000.000.000-00"
          class="form-input"
        />
      </div>
    </form>

    <!-- Pix Payment Form -->
    <div v-show="paymentMethod === 'pix'" class="payment-form pix-form">
      <h3>Pagamento via Pix</h3>

      <!-- Payer Info for Pix -->
      <div class="form-group">
        <label for="pixEmail">E-mail</label>
        <input 
          v-model="email"
          type="email" 
          placeholder="seu@email.com"
          class="form-input"
        />
      </div>

      <div class="form-group">
        <label for="pixDocType">Tipo de Documento</label>
        <select 
          v-model="identificationType"
          class="form-select"
        >
          <option value="CPF">CPF</option>
          <option value="CNPJ">CNPJ</option>
        </select>
      </div>

      <div class="form-group">
        <label for="pixDocNumber">Número do Documento</label>
        <input 
          v-model="identificationNumber"
          type="text" 
          placeholder="000.000.000-00"
          class="form-input"
        />
      </div>

      <div class="pix-info">
        <p>💡 Após clicar em "Pagar", você receberá um QR Code para escanear com seu app bancário.</p>
      </div>
    </div>

    <!-- Order Summary -->
    <div class="order-summary">
      <h3>Resumo do Pedido</h3>
      <div class="summary-item">
        <span>Produto:</span>
        <strong>{{ productDescription }}</strong>
      </div>
      <div class="summary-item total">
        <span>Total:</span>
        <strong>R$ {{ transactionAmount.toFixed(2).replace('.', ',') }}</strong>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="errorMessage" class="error-message">
      ⚠️ {{ errorMessage }}
    </div>

    <!-- Success Message -->
    <div v-if="successMessage" class="success-message">
      ✅ {{ successMessage }}
    </div>

    <!-- Pix QR Code Display -->
    <div v-if="pixQrCode" class="pix-qrcode-container">
      <h3>Escaneie o QR Code</h3>
      <img :src="pixQrCodeImage" alt="QR Code Pix" class="qr-code-image" />
      
      <div class="pix-copy-paste">
        <p><strong>Ou copie o código:</strong></p>
        <div class="copy-container">
          <input 
            :value="pixQrCode" 
            readonly 
            class="pix-code-input"
            ref="pixCodeInput"
          />
          <button @click="copyPixCode" class="copy-btn">
            {{ copied ? '✓ Copiado' : '📋 Copiar' }}
          </button>
        </div>
      </div>

      <div class="pix-instructions">
        <p>💳 O pagamento será confirmado automaticamente após a leitura do QR Code.</p>
      </div>
    </div>

    <!-- Submit Button -->
    <button 
      v-if="!pixQrCode"
      @click="processPayment" 
      :disabled="loading"
      class="submit-btn"
    >
      {{ loading ? '⏳ Processando...' : '💰 Pagar' }}
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue'
import { useRoute } from 'vue-router'

// =============================================
// ROUTE DATA
// =============================================
const route = useRoute()
const orderId = route.params.orderId
const paymentId = route.query.paymentId
const orderAmount = parseFloat(route.query.amount) || 199.90

console.log('💳 Dados do checkout:', { orderId, paymentId, orderAmount })

// =============================================
// CONFIGURATION (via Environment Variables)
// =============================================
const PUBLIC_KEY = process.env.VUE_APP_MERCADOPAGO_PUBLIC_KEY || ''
const debug = process.env.VUE_APP_DEBUG_MODE === 'true' || false

// Validação: Verificar se a Public Key foi configurada
if (!PUBLIC_KEY) {
  console.error('❌ ERRO: VUE_APP_MERCADOPAGO_PUBLIC_KEY não configurada no arquivo .env')
  console.error('📋 Copie .env.example para .env e configure suas credenciais')
}

// =============================================
// STATE MANAGEMENT
// =============================================
const paymentMethod = ref('credit_card')
const cardholderName = ref('')
const email = ref('')
const identificationType = ref('CPF')
const identificationNumber = ref('')
const installments = ref('')
const issuer = ref('')
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const pixQrCode = ref('')
const pixQrCodeImage = ref('')
const copied = ref(false)

// Mercado Pago instances
let mp = null
let cardForm = null

// Debug info
const debugInfo = computed(() => ({
  orderId,
  paymentId,
  paymentMethod: paymentMethod.value,
  email: email.value,
  identificationType: identificationType.value,
  identificationNumber: identificationNumber.value,
  cardholderName: cardholderName.value,
  installments: installments.value,
  transactionAmount: transactionAmount.value
}))

// Order details - agora dinâmico baseado na rota
const transactionAmount = ref(orderAmount)
const productDescription = ref(`Pedido ${orderId}`)

// =============================================
// LIFECYCLE
// =============================================
onMounted(async () => {
  // Aguardar o DOM estar completamente renderizado
  await nextTick()
  console.log('🔍 CheckoutPayment mounted, inicializando Mercado Pago...')
  initMercadoPago()
})

// =============================================
// METHODS
// =============================================
function selectPaymentMethod(method) {
  paymentMethod.value = method
  errorMessage.value = ''
  successMessage.value = ''
  pixQrCode.value = ''
  
  if (debug) {
    console.log('🔄 Método de pagamento alterado:', method)
  }
}

async function initMercadoPago() {
  try {
    console.log('🔍 Inicializando Mercado Pago...')
    
    // Verificar se o elemento form-checkout existe
    const formElement = document.getElementById('form-checkout')
    if (!formElement) {
      throw new Error('Elemento form-checkout não encontrado no DOM')
    }
    console.log('✅ Elemento form-checkout encontrado:', formElement)
    
    // Load Mercado Pago SDK
    if (!window.MercadoPago) {
      throw new Error('Mercado Pago SDK não carregado. Verifique se o script está no index.html')
    }
    console.log('✅ SDK do Mercado Pago carregado')

    mp = new window.MercadoPago(PUBLIC_KEY)
    console.log('✅ Instância do Mercado Pago criada')

    // Initialize Card Form
    cardForm = mp.cardForm({
      amount: String(transactionAmount.value),
      iframe: true,
      form: {
        id: 'form-checkout',
        cardNumber: {
          id: 'form-checkout__cardNumber',
          placeholder: 'Número do cartão'
        },
        expirationDate: {
          id: 'form-checkout__expirationDate',
          placeholder: 'MM/AA'
        },
        securityCode: {
          id: 'form-checkout__securityCode',
          placeholder: 'CVV'
        },
        cardholderName: {
          id: 'form-checkout__cardholderName',
          placeholder: 'Titular do cartão'
        },
        issuer: {
          id: 'form-checkout__issuer',
          placeholder: 'Banco emissor'
        },
        installments: {
          id: 'form-checkout__installments',
          placeholder: 'Parcelas'
        },
        identificationType: {
          id: 'form-checkout__identificationType',
          placeholder: 'Tipo de documento'
        },
        identificationNumber: {
          id: 'form-checkout__identificationNumber',
          placeholder: 'Número do documento'
        },
        cardholderEmail: {
          id: 'form-checkout__email',
          placeholder: 'E-mail'
        }
      },
      callbacks: {
        onFormMounted: (error) => {
          if (error) {
            console.error('❌ Erro ao montar formulário:', error)
            errorMessage.value = 'Erro ao carregar campos do cartão'
          } else if (debug) {
            console.log('✅ Card Form montado com sucesso')
          }
        },
        onSubmit: (event) => {
          event.preventDefault()
        }
      }
    })

    if (debug) {
      console.log('✅ Mercado Pago inicializado')
    }

  } catch (error) {
    console.error('❌ Erro ao inicializar Mercado Pago:', error)
    errorMessage.value = 'Erro ao inicializar sistema de pagamento'
  }
}

async function processPayment() {
  // Reset messages
  errorMessage.value = ''
  successMessage.value = ''
  pixQrCode.value = ''
  loading.value = true

  try {
    // Validate basic fields
    if (!email.value || !identificationType.value || !identificationNumber.value) {
      throw new Error('Preencha todos os campos obrigatórios')
    }

    let payload = {}

    if (paymentMethod.value === 'pix') {
      // PIX Payment
      payload = {
        payment_method_id: 'pix',
        transaction_amount: transactionAmount.value,
        description: productDescription.value,
        payer: {
          email: email.value,
          identification: {
            type: identificationType.value,
            number: identificationNumber.value.replace(/\D/g, '')
          }
        }
      }

      if (debug) {
        console.log('📤 Payload PIX:', payload)
      }

    } else {
      // Credit Card Payment
      if (!cardholderName.value || !installments.value) {
        throw new Error('Preencha todos os campos do cartão')
      }

      // Get card token from Mercado Pago
      const cardData = await cardForm.createCardToken()

      if (!cardData || !cardData.id) {
        throw new Error('Erro ao processar dados do cartão')
      }

      payload = {
        payment_method_id: cardData.payment_method_id,
        token: cardData.id,
        transaction_amount: transactionAmount.value,
        installments: parseInt(installments.value),
        description: productDescription.value,
        issuer_id: issuer.value || cardData.issuer_id,
        payer: {
          email: email.value,
          identification: {
            type: identificationType.value,
            number: identificationNumber.value.replace(/\D/g, '')
          }
        }
      }

      if (debug) {
        console.log('📤 Payload Cartão:', payload)
        console.log('🔑 Token:', cardData.id)
      }
    }

    // Send to backend
    const response = await fetch('/api/process_payment.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(payload)
    })

    const result = await response.json()

    if (debug) {
      console.log('📥 Resposta do servidor:', result)
    }

    if (result.success) {
      if (paymentMethod.value === 'pix' && result.qr_code) {
        // Display Pix QR Code
        pixQrCode.value = result.qr_code
        pixQrCodeImage.value = result.qr_code_base64
        successMessage.value = 'QR Code gerado! Escaneie para pagar.'
      } else {
        successMessage.value = result.message || 'Pagamento aprovado com sucesso!'
      }
    } else {
      throw new Error(result.message || 'Erro ao processar pagamento')
    }

  } catch (error) {
    console.error('❌ Erro no pagamento:', error)
    errorMessage.value = error.message || 'Erro ao processar pagamento. Tente novamente.'
  } finally {
    loading.value = false
  }
}

function copyPixCode() {
  const input = document.querySelector('.pix-code-input')
  input.select()
  document.execCommand('copy')
  copied.value = true
  
  setTimeout(() => {
    copied.value = false
  }, 2000)
}
</script>

<style scoped>
/* =============================================
   RESET & BASE
   ============================================= */
.checkout-payment {
  max-width: 600px;
  margin: 0 auto;
  padding: 2rem;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

/* =============================================
   ORDER SUMMARY
   ============================================= */
.order-summary {
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: #f8f9fa;
  border-radius: 12px;
  border: 1px solid #e9ecef;
}

.order-summary h2 {
  margin: 0 0 1rem 0;
  color: #2c3e50;
  font-size: 1.5rem;
}

.order-info {
  display: grid;
  gap: 0.5rem;
}

.order-info p {
  margin: 0;
  color: #495057;
}

.order-info strong {
  color: #2c3e50;
}

/* =============================================
   DEBUG PANEL
   ============================================= */
.debug-panel {
  background: #1a1a1a;
  color: #00ff00;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 2rem;
  font-family: 'Courier New', monospace;
  font-size: 0.85rem;
}

.debug-panel h4 {
  margin: 0 0 0.5rem 0;
  color: #ffff00;
}

.debug-panel pre {
  margin: 0;
  white-space: pre-wrap;
  word-wrap: break-word;
}

/* =============================================
   PAYMENT METHOD SELECTOR
   ============================================= */
.payment-selector {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
}

.selector-btn {
  flex: 1;
  padding: 1rem;
  border: 2px solid #e0e0e0;
  background: white;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.selector-btn:hover {
  border-color: #00b4d8;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 180, 216, 0.15);
}

.selector-btn.active {
  border-color: #00b4d8;
  background: linear-gradient(135deg, #00b4d8 0%, #0077b6 100%);
  color: white;
}

/* =============================================
   FORM STYLES
   ============================================= */
.payment-form {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
}

.payment-form h3 {
  margin: 0 0 1.5rem 0;
  color: #333;
  font-size: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #555;
  font-weight: 500;
  font-size: 0.95rem;
}

.form-input,
.form-select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
  box-sizing: border-box;
}

.form-input:focus,
.form-select:focus {
  outline: none;
  border-color: #00b4d8;
  box-shadow: 0 0 0 3px rgba(0, 180, 216, 0.1);
}

.card-field {
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  background: white;
  min-height: 45px;
}

/* =============================================
   PIX SPECIFIC
   ============================================= */
.pix-info {
  background: #e3f2fd;
  padding: 1rem;
  border-radius: 8px;
  border-left: 4px solid #2196f3;
  margin-top: 1rem;
}

.pix-info p {
  margin: 0;
  color: #1565c0;
  font-size: 0.95rem;
}

/* =============================================
   ORDER SUMMARY
   ============================================= */
.order-summary {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 2rem;
}

.order-summary h3 {
  margin: 0 0 1rem 0;
  color: #333;
  font-size: 1.2rem;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  color: #666;
}

.summary-item.total {
  border-top: 2px solid #ddd;
  margin-top: 0.5rem;
  padding-top: 1rem;
  font-size: 1.2rem;
  color: #333;
}

/* =============================================
   MESSAGES
   ============================================= */
.error-message {
  background: #fee;
  color: #c00;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  border-left: 4px solid #c00;
}

.success-message {
  background: #efe;
  color: #070;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  border-left: 4px solid #070;
}

/* =============================================
   PIX QR CODE
   ============================================= */
.pix-qrcode-container {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
  text-align: center;
  margin-bottom: 2rem;
}

.pix-qrcode-container h3 {
  margin: 0 0 1.5rem 0;
  color: #333;
}

.qr-code-image {
  max-width: 300px;
  width: 100%;
  height: auto;
  margin-bottom: 1.5rem;
}

.pix-copy-paste {
  margin-top: 1.5rem;
}

.copy-container {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.pix-code-input {
  flex: 1;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-family: monospace;
  font-size: 0.9rem;
}

.copy-btn {
  padding: 0.75rem 1.5rem;
  background: #00b4d8;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: background 0.3s ease;
}

.copy-btn:hover {
  background: #0077b6;
}

.pix-instructions {
  margin-top: 1.5rem;
  padding: 1rem;
  background: #e3f2fd;
  border-radius: 8px;
  color: #1565c0;
}

.pix-instructions p {
  margin: 0;
}

/* =============================================
   SUBMIT BUTTON
   ============================================= */
.submit-btn {
  width: 100%;
  padding: 1rem;
  background: linear-gradient(135deg, #00b4d8 0%, #0077b6 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 119, 182, 0.3);
}

.submit-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
}

/* =============================================
   RESPONSIVE
   ============================================= */
@media (max-width: 640px) {
  .checkout-payment {
    padding: 1rem;
  }

  .payment-selector {
    flex-direction: column;
  }

  .payment-form {
    padding: 1.5rem;
  }

  .selector-btn {
    padding: 0.875rem;
  }
}
</style>
