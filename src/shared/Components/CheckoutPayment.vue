<template>
  <div class="checkout-payment">
    <!-- Header -->
    <div class="checkout-header">
      <div class="header-content">
        <button @click="goBack" class="back-btn">
          <i class="fas fa-arrow-left"></i>
        </button>
        <h1>Finalizar Pagamento</h1>
        <div class="security-badge">
          <i class="fas fa-shield-alt"></i>
          Pagamento Seguro
        </div>
      </div>
    </div>

    <div class="checkout-container">
      <!-- Order Summary Card -->
      <div class="order-summary-card">
        <div class="summary-header">
          <h2><i class="fas fa-receipt"></i> Resumo do Pedido</h2>
          <span class="order-id">{{ orderId }}</span>
        </div>
        
        <div class="summary-content">
          <div class="product-info">
            <h3>{{ productDescription }}</h3>
            <p class="product-details">Site completo com todas as funcionalidades selecionadas</p>
          </div>
          
          <div class="price-breakdown">
            <div class="price-item">
              <span>Subtotal</span>
              <span>R$ {{ baseAmount.toFixed(2).replace('.', ',') }}</span>
            </div>
            <div v-if="paymentMethod === 'installments'" class="price-item">
              <span>Taxa do cartão (15%)</span>
              <span>R$ {{ ((transactionAmount - baseAmount)).toFixed(2).replace('.', ',') }}</span>
            </div>
            <div class="price-item total">
              <span>Total a pagar</span>
              <span class="total-amount">R$ {{ transactionAmount.toFixed(2).replace('.', ',') }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Section -->
      <div class="payment-section">
        <!-- Payment Method Selector (apenas se não vier da URL) -->
        <div v-if="shouldShowMethodSelector" class="payment-methods">
          <h3><i class="fas fa-credit-card"></i> Forma de Pagamento</h3>
          
          <div class="method-selector">
            <button 
              :class="['method-btn', { active: selectedMethod === 'credit' }]"
              @click="selectPaymentMethod('credit')"
            >
              <i class="fas fa-credit-card"></i>
              <div class="method-info">
                <span class="method-title">Cartão de Crédito</span>
                <span class="method-subtitle">12x sem juros</span>
              </div>
            </button>
            
            <button 
              :class="['method-btn', { active: selectedMethod === 'pix' }]"
              @click="selectPaymentMethod('pix')"
            >
              <i class="fas fa-mobile-alt"></i>
              <div class="method-info">
                <span class="method-title">PIX</span>
                <span class="method-subtitle">Aprovação instantânea</span>
              </div>
            </button>
          </div>
        </div>

        <!-- Credit Card Form -->
        <div v-if="selectedMethod === 'credit'" class="payment-form">
          <div class="form-header">
            <h4><i class="fas fa-lock"></i> Dados do Cartão</h4>
            <div class="cards-accepted">
              <i class="fab fa-cc-visa" title="Visa"></i>
              <i class="fab fa-cc-mastercard" title="Mastercard"></i>
              <i class="fab fa-cc-amex" title="American Express"></i>
            </div>
          </div>

          <!-- Seletor de Forma de Pagamento (apenas se não vier da URL) -->
          <div v-if="shouldShowMethodSelector" class="payment-type-selector">
            <h5><i class="fas fa-credit-card"></i> Como deseja pagar?</h5>
            <div class="payment-options">
              <label class="payment-option" :class="{ active: paymentMethod === 'cash' }">
                <input 
                  type="radio" 
                  v-model="paymentMethod" 
                  value="cash" 
                  name="paymentType"
                  @change="updateAmount"
                >
                <div class="option-content">
                  <div class="option-icon">
                    <i class="fas fa-credit-card"></i>
                  </div>
                  <div class="option-text">
                    <span class="option-title">À vista</span>
                    <span class="option-subtitle">Sem juros</span>
                  </div>
                </div>
              </label>

              <label class="payment-option" :class="{ active: paymentMethod === 'installments' }">
                <input 
                  type="radio" 
                  v-model="paymentMethod" 
                  value="installments" 
                  name="paymentType"
                  @change="updateAmount"
                >
                <div class="option-content">
                  <div class="option-icon">
                    <i class="fas fa-calendar-alt"></i>
                  </div>
                  <div class="option-text">
                    <span class="option-title">Parcelado (+15%)</span>
                    <span class="option-subtitle">Taxa do cartão</span>
                  </div>
                </div>
              </label>
            </div>
          </div>

          <form id="form-checkout" @submit.prevent="processPayment">
            <div class="form-grid">
              <div class="form-group full-width">
                <label>
                  <i class="fas fa-user"></i>
                  Nome do titular
                </label>
                <div class="input-wrapper">
                  <input 
                    id="form-checkout__cardholderName"
                    type="text" 
                    class="form-input"
                    placeholder="Nome como está no cartão"
                  />
                </div>
              </div>

              <div class="form-group full-width">
                <label>
                  <i class="fas fa-envelope"></i>
                  E-mail
                </label>
                <div class="input-wrapper">
                  <input 
                    id="form-checkout__cardholderEmail"
                    type="email" 
                    class="form-input"
                    placeholder="seu@email.com"
                  />
                </div>
              </div>

              <div class="form-group full-width">
                <label>
                  <i class="fas fa-credit-card"></i>
                  Número do cartão
                </label>
                <div class="input-wrapper card-input">
                  <div id="form-checkout__cardNumber" class="card-field"></div>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>
                    <i class="fas fa-calendar"></i>
                    Validade
                  </label>
                  <div class="input-wrapper">
                    <div id="form-checkout__expirationDate" class="card-field"></div>
                  </div>
                </div>

                <div class="form-group">
                  <label>
                    <i class="fas fa-lock"></i>
                    CVV
                  </label>
                  <div class="input-wrapper">
                    <div id="form-checkout__securityCode" class="card-field"></div>
                  </div>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>
                    <i class="fas fa-id-card"></i>
                    Tipo de documento
                  </label>
                  <div class="input-wrapper">
                    <select id="form-checkout__identificationType" class="form-select">
                      <option value="CPF">CPF</option>
                      <option value="CNPJ">CNPJ</option>
                    </select>
                  </div>
                </div>

                <div class="form-group">
                  <label>
                    <i class="fas fa-hashtag"></i>
                    Número do documento
                  </label>
                  <div class="input-wrapper">
                    <input 
                      id="form-checkout__identificationNumber"
                      type="text" 
                      class="form-input"
                      placeholder="000.000.000-00"
                    />
                  </div>
                </div>
              </div>

              <div class="form-group" v-if="paymentMethod === 'installments'">
                <label>
                  <i class="fas fa-calendar-alt"></i>
                  Parcelas
                </label>
                <div class="input-wrapper">
                  <select id="form-checkout__installments" class="form-select">
                    <option value="">Escolha o número de parcelas</option>
                    <option v-for="n in 11" :key="n + 1" :value="n + 1">
                      {{ n + 1 }}x de R$ {{ ((transactionAmount / (n + 1)).toFixed(2).replace('.', ',')) }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
          </form>
        </div>

        <!-- PIX Form -->
        <div v-if="selectedMethod === 'pix'" class="payment-form">
          <div class="form-header">
            <h4><i class="fas fa-mobile-alt"></i> Pagamento PIX</h4>
            <div class="pix-benefits">
              <span><i class="fas fa-shield-alt"></i> Seguro</span>
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group full-width">
              <label>
                <i class="fas fa-envelope"></i>
                E-mail
              </label>
              <div class="input-wrapper">
                <input 
                  v-model="formData.pix.email"
                  type="email" 
                  placeholder="seu@email.com"
                  class="form-input"
                />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>
                  <i class="fas fa-id-card"></i>
                  Tipo de documento
                </label>
                <div class="input-wrapper">
                  <select 
                    v-model="identificationType"
                    class="form-select"
                  >
                    <option value="CPF">CPF</option>
                    <option value="CNPJ">CNPJ</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label>
                  <i class="fas fa-hashtag"></i>
                  Número do documento
                </label>
                <div class="input-wrapper">
                  <input 
                    v-model="identificationNumber"
                    type="text" 
                    placeholder="000.000.000-00"
                    class="form-input"
                  />
                </div>
              </div>
            </div>

            <div class="pix-info">
              <div class="info-card">
                <h5><i class="fas fa-info-circle"></i> Como funciona?</h5>
                <ol>
                  <li>Clique em "Finalizar Pagamento"</li>
                  <li>Escaneie o QR Code com seu app bancário</li>
                  <li>Confirme o pagamento</li>
                  <li>Aprovação em segundos!</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="checkout-actions">
          <button 
            type="button" 
            @click="processPayment"
            :disabled="loading"
            class="pay-button"
          >
            <i v-if="loading" class="fas fa-spinner fa-spin"></i>
            <i v-else class="fas fa-lock"></i>
            <span v-if="loading">Processando...</span>
            <span v-else>Finalizar Pagamento - R$ {{ transactionAmount.toFixed(2).replace('.', ',') }}</span>
          </button>

          <div class="security-info">
            <i class="fas fa-shield-alt"></i>
            <span>Seus dados estão protegidos com criptografia SSL</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Status Messages -->
    <div v-if="errorMessage" class="status-message error">
      <i class="fas fa-exclamation-triangle"></i>
      {{ errorMessage }}
    </div>

    <div v-if="successMessage" class="status-message success">
      <i class="fas fa-check-circle"></i>
      {{ successMessage }}
    </div>

    <!-- PIX QR Code Modal -->
    <div v-if="showQRCode" class="qr-modal">
      <div class="qr-content">
        <div class="qr-header">
          <h3><i class="fas fa-qrcode"></i> Escaneie o QR Code</h3>
          <div class="status-indicator" :class="{ 
            'pending': !successMessage.includes('confirmado'),
            'checking': successMessage.includes('Aguardando')
          }">
            <div class="status-dot"></div>
            <span>{{ successMessage.includes('confirmado') ? 'Pagamento Confirmado' : 'Aguardando Pagamento' }}</span>
          </div>
        </div>
        
        <div class="qr-code">
          <img v-if="qrCodeUrl" :src="qrCodeUrl" alt="QR Code PIX" />
          <div v-if="successMessage.includes('Aguardando')" class="scanning-animation">
            <div class="scan-line"></div>
          </div>
        </div>
        
        <div class="qr-info">
          <div class="amount-info">
            <span class="amount-label">Valor:</span>
            <span class="amount-value">R$ {{ transactionAmount.toFixed(2).replace('.', ',') }}</span>
          </div>
          
          <div class="instructions">
            <h4><i class="fas fa-mobile-alt"></i> Como pagar:</h4>
            <ol>
              <li>Abra o app do seu banco</li>
              <li>Escaneie o QR Code acima</li>
              <li>Confirme o pagamento</li>
              <li>Aguarde a confirmação automática</li>
            </ol>
          </div>
          
          <div class="security-note">
            <i class="fas fa-shield-alt"></i>
            <span>PIX é instantâneo e seguro. O pagamento será confirmado automaticamente.</span>
          </div>
        </div>
        
        <div class="qr-actions">
          <button @click="closeQRModal" class="close-qr">
            <i class="fas fa-times"></i>
            Cancelar
          </button>
        </div>
      </div>
    </div>

    <!-- Debug Panel (apenas em desenvolvimento) -->
    <div v-if="debug" class="debug-panel">
      <details>
        <summary><i class="fas fa-bug"></i> Debug Info</summary>
        <pre>{{ debugInfo }}</pre>
      </details>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'

// =============================================
// ROUTE DATA
// =============================================
const route = useRoute()
const router = useRouter()
const orderId = route.params.orderId
const paymentId = route.query.paymentId
const orderAmount = parseFloat(route.query.amount) || 1189.00
const urlMethod = route.query.method || 'avista' // Lê método da URL

console.log('💳 Dados do checkout:', { orderId, paymentId, orderAmount, urlMethod })

// =============================================
// CONFIGURATION (via Environment Variables)
// =============================================
const PUBLIC_KEY = process.env.VUE_APP_MERCADOPAGO_PUBLIC_KEY || 'APP_USR-2346e6d1-c231-4289-bcb3-9f31e9f003ba'
const debug = process.env.VUE_APP_DEBUG_MODE === 'true' || false

// =============================================
// STATE MANAGEMENT
// =============================================
const selectedMethod = ref(urlMethod === 'avista' ? 'pix' : 'credit')
const cardholderName = ref('')
const email = ref('')
const identificationType = ref('CPF')
const identificationNumber = ref('')
const installments = ref('')
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const showQRCode = ref(false)
const qrCodeUrl = ref('')
const paymentMethod = ref(urlMethod === 'avista' ? 'cash' : 'installments')
const shouldShowMethodSelector = ref(false) // Oculta seletor se veio da URL
const baseAmount = ref(orderAmount) // Valor base sem taxa

// Form data for better organization
const formData = reactive({
  card: {
    holder_name: '',
    cvv: '',
    expiration: {
      month: '',
      year: ''
    }
  },
  pix: {
    name: '',
    email: '',
    document: '',
    phone: ''
  }
})

// Mercado Pago instances
let mp = null
let cardForm = null

// Debug info
const debugInfo = computed(() => ({
  orderId,
  paymentId,
  selectedMethod: selectedMethod.value,
  email: email.value,
  identificationType: identificationType.value,
  identificationNumber: identificationNumber.value,
  cardholderName: cardholderName.value,
  installments: installments.value,
  transactionAmount: transactionAmount.value,
  formData: formData
}))

// Order details - agora dinâmico baseado na rota e método de pagamento
const transactionAmount = computed(() => {
  if (paymentMethod.value === 'installments') {
    return baseAmount.value * 1.15 // Taxa de 15% para parcelado
  }
  return baseAmount.value
})
const productDescription = ref(`Desenvolvimento de Site - Pedido ${orderId}`)

// =============================================
// METHODS
// =============================================
const goBack = () => {
  router.push('/configurador')
}

const selectPaymentMethod = async (method) => {
  selectedMethod.value = method
  errorMessage.value = ''
  successMessage.value = ''
  
  // Re-initialize form if switching to credit card
  if (method === 'credit' && mp) {
    await nextTick()
    initializeCardForm()
  }
}

const updateAmount = () => {
  console.log('💰 Método alterado para:', paymentMethod.value)
  // transactionAmount já é um computed que recalcula automaticamente
}

const initializeMercadoPago = async () => {
  try {
    if (typeof window.MercadoPago === 'undefined') {
      throw new Error('Mercado Pago SDK não carregado. Verifique se o script está no index.html')
    }

    mp = new window.MercadoPago(PUBLIC_KEY)
    console.log('✅ Mercado Pago inicializado')
    
    // Só inicializar formulário de cartão se o método for cartão de crédito
    if (selectedMethod.value === 'credit') {
      await nextTick()
      initializeCardForm()
    }
    
  } catch (error) {
    console.error('❌ Erro ao inicializar Mercado Pago:', error)
    errorMessage.value = 'Erro ao carregar sistema de pagamento'
  }
}

const initializeCardForm = async () => {
  try {
    if (!mp) return
    
    const formElement = document.getElementById('form-checkout')
    if (!formElement) {
      console.warn('Elemento form-checkout não encontrado, tentando novamente...')
      setTimeout(initializeCardForm, 500)
      return
    }

    if (cardForm) {
      cardForm.unmount()
    }

    cardForm = mp.cardForm({
      amount: transactionAmount.value.toString(),
      form: {
        id: 'form-checkout',
        cardholderName: {
          id: 'form-checkout__cardholderName',
          placeholder: 'Nome do titular'
        },
        cardholderEmail: {
          id: 'form-checkout__cardholderEmail', 
          placeholder: 'E-mail'
        },
        cardNumber: {
          id: 'form-checkout__cardNumber',
          placeholder: 'Número do cartão'
        },
        expirationDate: {
          id: 'form-checkout__expirationDate',
          placeholder: 'MM/YY'
        },
        securityCode: {
          id: 'form-checkout__securityCode', 
          placeholder: 'CVV'
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
        }
      },
      callbacks: {
        onFormMounted: error => {
          if (error) {
            console.warn('Aviso no formulário:', error)
            return
          }
          console.log('✅ Formulário de cartão carregado')
        },
        onSubmit: event => {
          event.preventDefault()
          processPayment()
        },
        onFetching: (resource) => {
          console.log('Carregando:', resource)
          const progressBar = document.querySelector('.progress-bar')
          if (progressBar) {
            progressBar.removeAttribute('value')
          }

          return () => {
            if (progressBar) {
              progressBar.setAttribute('value', '0')
            }
          }
        }
      }
    })

  } catch (error) {
    console.error('❌ Erro ao inicializar formulário:', error)
    errorMessage.value = 'Erro ao carregar formulário de pagamento'
  }
}

const processPayment = async () => {
  try {
    loading.value = true
    errorMessage.value = ''
    successMessage.value = ''
    
    if (selectedMethod.value === 'credit') {
      await processCreditCardPayment()
    } else if (selectedMethod.value === 'pix') {
      await processPixPayment()
    }
    
  } catch (error) {
    console.error('❌ Erro no pagamento:', error)
    errorMessage.value = error.message || 'Erro no processamento do pagamento'
    loading.value = false
  }
}

const processCreditCardPayment = async () => {
  try {
    if (!cardForm) {
      throw new Error('Formulário de cartão não inicializado')
    }

    // Get card form data
    const formData = cardForm.getCardFormData()
    console.log('💳 Dados do cartão obtidos:', formData)

    // Preparar dados para envio ao backend
    const paymentData = {
      order_id: orderId,
      payment_method: 'credit_card',
      transaction_amount: transactionAmount.value,
      installments: paymentMethod.value === 'installments' ? (formData.installments || 1) : 1,
      token: formData.token,
      payment_method_id: formData.payment_method_id,
      payer: {
        name: formData.cardholderName,
        email: formData.cardholderEmail,
        identification: {
          type: formData.identificationType,
          number: formData.identificationNumber
        }
      }
    }

    successMessage.value = 'Processando pagamento...'
    
    // Enviar para o backend (API real)
    const response = await fetch('/api/process_payment.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(paymentData)
    })

    const result = await response.json()
    
    if (!response.ok) {
      throw new Error(result.message || 'Erro no processamento')
    }

    console.log('💳 Resposta do pagamento:', result)
    
    if (result.success && result.payment_id) {
      // Pagamento criado, iniciar polling para verificar status
      successMessage.value = 'Pagamento enviado. Aguardando confirmação...'
      
      // Iniciar verificação de status
      startPaymentStatusPolling(result.payment_id, 'credit')
      
    } else {
      throw new Error(result.message || 'Falha no processamento do pagamento')
    }

  } catch (error) {
    console.error('❌ Erro no pagamento com cartão:', error)
    throw error
  } finally {
    loading.value = false
  }
}

const processPixPayment = async () => {
  try {
    // Validar dados obrigatórios
    if (!formData.pix.email || !identificationType.value || !identificationNumber.value) {
      throw new Error('Preencha todos os campos obrigatórios')
    }

    // Preparar dados para envio ao backend
    const paymentData = {
      order_id: orderId,
      payment_method: 'pix',
      transaction_amount: transactionAmount.value,
      payer: {
        email: formData.pix.email,
        identification: {
          type: identificationType.value,
          number: identificationNumber.value
        }
      }
    }

    successMessage.value = 'Gerando código PIX...'

    // Enviar para o backend (API real)
    const response = await fetch('/api/process_payment.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(paymentData)
    })

    const result = await response.json()
    
    if (!response.ok) {
      throw new Error(result.message || 'Erro na geração do PIX')
    }

    console.log('📱 Resposta do PIX:', result)
    
    if (result.success && result.qr_code) {
      // PIX gerado com sucesso
      qrCodeUrl.value = result.qr_code_base64 || `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(result.qr_code)}`
      showQRCode.value = true
      
      successMessage.value = 'QR Code gerado! Escaneie com seu app bancário.'
      
      // Iniciar polling para verificar quando o pagamento for confirmado
      if (result.payment_id) {
        startPaymentStatusPolling(result.payment_id, 'pix')
      }
      
    } else {
      throw new Error(result.message || 'Falha na geração do código PIX')
    }

  } catch (error) {
    console.error('❌ Erro no pagamento PIX:', error)
    throw error
  } finally {
    loading.value = false
  }
}

const closeQRModal = () => {
  showQRCode.value = false
  qrCodeUrl.value = ''
  stopPaymentStatusPolling()
}

// =============================================
// POLLING DE STATUS DE PAGAMENTO
// =============================================
let statusPollingInterval = null
let pollingAttempts = 0
const maxPollingAttempts = 120 // 10 minutos (5s * 120)

const startPaymentStatusPolling = (paymentId, method) => {
  console.log('🔄 Iniciando polling de status:', { paymentId, method })
  
  pollingAttempts = 0
  
  statusPollingInterval = setInterval(async () => {
    pollingAttempts++
    
    try {
      const response = await fetch(`/api/check_payment_status.php?payment_id=${paymentId}&order_id=${orderId}`)
      const result = await response.json()
      
      console.log('📊 Status check:', result)
      
      if (result.ok && result.status) {
        if (result.status === 'paid') {
          // Pagamento aprovado
          stopPaymentStatusPolling()
          successMessage.value = '✅ Pagamento confirmado! Redirecionando...'
          errorMessage.value = ''
          
          setTimeout(() => {
            router.push({
              name: 'configurador',
              query: { 
                success: 'true',
                orderId: orderId,
                paymentId: paymentId,
                paymentType: method
              }
            })
          }, 2000)
          
        } else if (result.status === 'failed') {
          // Pagamento rejeitado
          stopPaymentStatusPolling()
          errorMessage.value = 'Pagamento foi rejeitado. Tente novamente.'
          successMessage.value = ''
          showQRCode.value = false
          
        } else if (result.status === 'pending') {
          // Ainda pendente, continuar polling
          if (method === 'pix') {
            successMessage.value = '⏳ Aguardando pagamento PIX...'
          } else {
            successMessage.value = '⏳ Pagamento em análise...'
          }
        }
      }
      
      // Parar polling após muitas tentativas
      if (pollingAttempts >= maxPollingAttempts) {
        stopPaymentStatusPolling()
        errorMessage.value = 'Tempo limite excedido. Verifique o status do pagamento em sua conta.'
        successMessage.value = ''
      }
      
    } catch (error) {
      console.error('❌ Erro no polling:', error)
      
      // Em caso de erro, continuar tentando por algumas vezes
      if (pollingAttempts >= 5) {
        stopPaymentStatusPolling()
        errorMessage.value = 'Erro ao verificar status do pagamento'
      }
    }
    
  }, 5000) // Verificar a cada 5 segundos
}

const stopPaymentStatusPolling = () => {
  if (statusPollingInterval) {
    clearInterval(statusPollingInterval)
    statusPollingInterval = null
    pollingAttempts = 0
    console.log('⏹️ Polling de status interrompido')
  }
}

// =============================================
// LIFECYCLE
// =============================================
onMounted(async () => {
  console.log('🚀 Iniciando CheckoutPayment...')
  console.log('📋 Método detectado na URL:', urlMethod)
  console.log('🎯 Método selecionado:', selectedMethod.value)
  
  // Wait a bit for the SDK to load
  setTimeout(async () => {
    await initializeMercadoPago()
  }, 500)
})

// Limpar polling quando componente for desmontado
onUnmounted(() => {
  stopPaymentStatusPolling()
})
</script>

<style lang="scss" scoped>
// =============================================
// VARIABLES
// =============================================
$primary-color: #e67e22;
$primary-dark: #d35400;
$success-color: #22c55e;
$error-color: #ef4444;
$warning-color: #f59e0b;
$gray-100: #f3f4f6;
$gray-200: #e5e7eb;
$gray-300: #9095a1;
$gray-600: #5a5d6a;
$gray-700: #2a2d35;
$gray-800: #1a1d23;
$gray-900: #0a0a0a;

// =============================================
// MAIN LAYOUT
// =============================================
.checkout-payment {
  min-height: 100vh;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

// =============================================
// HEADER
// =============================================
.checkout-header {
  background: white;
  border-bottom: 1px solid $gray-200;
  padding: 1rem 0;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);

  .header-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;

    h1 {
      margin: 0;
      font-size: 1.5rem;
      font-weight: 700;
      color: $gray-900;
      flex: 1;
    }

    .back-btn {
      padding: 0.5rem;
      border: none;
      background: $gray-100;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s ease;

      &:hover {
        background: $gray-200;
        transform: scale(1.05);
      }

      i {
        color: $gray-600;
      }
    }

    .security-badge {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1rem;
      background: linear-gradient(135deg, $success-color, #20c997);
      color: white;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;

      i {
        font-size: 1rem;
      }
    }
  }
}

// =============================================
// CONTAINER
// =============================================
.checkout-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1rem;
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 2rem;
  align-items: start;

  @media (max-width: 992px) {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
}

// =============================================
// ORDER SUMMARY
// =============================================
.order-summary-card {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid $gray-200;
  position: sticky;
  top: 100px;

  .summary-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid $gray-200;

    h2 {
      margin: 0;
      font-size: 1.2rem;
      font-weight: 700;
      color: $gray-900;
      display: flex;
      align-items: center;
      gap: 0.5rem;

      i {
        color: $primary-color;
      }
    }

    .order-id {
      font-size: 0.8rem;
      color: $gray-600;
      background: $gray-100;
      padding: 0.25rem 0.75rem;
      border-radius: 12px;
      font-weight: 600;
    }
  }

  .summary-content {
    .product-info {
      margin-bottom: 1.5rem;

      h3 {
        margin: 0 0 0.5rem 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: $gray-900;
      }

      .product-details {
        margin: 0;
        font-size: 0.9rem;
        color: $gray-600;
        line-height: 1.4;
      }
    }

    .price-breakdown {
      .price-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        font-size: 0.95rem;

        &:not(:last-child) {
          border-bottom: 1px solid $gray-200;
        }

        &.total {
          font-weight: 700;
          font-size: 1.1rem;
          color: $gray-900;
          margin-top: 0.5rem;
          padding-top: 1rem;
          border-top: 2px solid $gray-300;

          .total-amount {
            color: $primary-color;
            font-size: 1.3rem;
          }
        }
      }
    }
  }
}

// =============================================
// PAYMENT SECTION
// =============================================
.payment-section {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.payment-methods {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid $gray-200;

  h3 {
    margin: 0 0 1.5rem 0;
    font-size: 1.2rem;
    font-weight: 700;
    color: $gray-900;
    display: flex;
    align-items: center;
    gap: 0.5rem;

    i {
      color: $primary-color;
    }
  }

  .method-selector {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;

    @media (max-width: 640px) {
      grid-template-columns: 1fr;
    }
  }

  .method-btn {
    padding: 1.5rem;
    border: 2px solid $gray-200;
    background: white;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 1rem;
    text-align: left;

    &:hover {
      border-color: $primary-color;
      box-shadow: 0 4px 12px rgba($primary-color, 0.15);
    }

    &.active {
      border-color: $primary-color;
      background: linear-gradient(135deg, rgba($primary-color, 0.05) 0%, rgba($primary-color, 0.02) 100%);
      box-shadow: 0 4px 16px rgba($primary-color, 0.2);
    }

    i {
      font-size: 1.5rem;
      color: $primary-color;
      min-width: 24px;
    }

    .method-info {
      .method-title {
        display: block;
        font-weight: 600;
        color: $gray-900;
        margin-bottom: 0.25rem;
      }

      .method-subtitle {
        display: block;
        font-size: 0.85rem;
        color: $gray-600;
      }
    }
  }
}

// =============================================
// PAYMENT FORM
// =============================================
.payment-form {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid $gray-200;

  .form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid $gray-200;

    h4 {
      margin: 0;
      font-size: 1.1rem;
      font-weight: 700;
      color: $gray-900;
      display: flex;
      align-items: center;
      gap: 0.5rem;

      i {
        color: $primary-color;
      }
    }

    .cards-accepted {
      display: flex;
      gap: 0.75rem;

      i {
        font-size: 1.8rem;
        color: $gray-600;
        transition: color 0.2s ease;

        &:hover {
          color: $primary-color;
        }
      }
    }

    .pix-benefits {
      display: flex;
      gap: 1rem;

      span {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: $gray-100;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        color: $gray-700;

        i {
          color: $primary-color;
        }
      }
    }
  }

  .form-grid {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
  }

  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;

    @media (max-width: 640px) {
      grid-template-columns: 1fr;
    }
  }

  .form-group {
    display: flex;
    flex-direction: column;
    justify-content: space-between;

    &.full-width {
      grid-column: 1 / -1;
    }

    label {
      font-size: 0.9rem;
      font-weight: 600;
      color: $gray-800;
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;

      i {
        color: $primary-color;
        font-size: 0.85rem;
        width: 16px;
      }
    }

    .input-wrapper {
      position: relative;
      
      &.card-input {
        border: 2px solid $gray-200;
        border-radius: 8px;
        transition: all 0.3s ease;
        
        &:focus-within {
          border-color: $primary-color;
          box-shadow: 0 0 0 3px rgba($primary-color, 0.1);
        }
      }
    }

    .form-input,
    .form-select {
      width: 100%;
      padding: 0.875rem;
      border: 2px solid $gray-200;
      border-radius: 8px;
      font-size: 1rem;
      transition: all 0.3s ease;
      background: white;

      &:focus {
        outline: none;
        border-color: $primary-color;
        box-shadow: 0 0 0 3px rgba($primary-color, 0.1);
      }

      &::placeholder {
        color: $gray-600;
      }
    }

    .card-field {
      padding: 0.875rem;
      min-height: 3rem;
      display: flex;
      align-items: center;
    }
  }

  .payment-type-selector {
    margin-bottom: 1.5rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, rgba($primary-color, 0.03) 0%, rgba($primary-color, 0.01) 100%);
    border-radius: 12px;
    border: 1px solid rgba($primary-color, 0.1);

    h5 {
      margin: 0 0 1rem 0;
      font-size: 1rem;
      font-weight: 700;
      color: $gray-900;
      display: flex;
      align-items: center;
      gap: 0.5rem;

      i {
        color: $primary-color;
      }
    }

    .payment-options {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;

      @media (max-width: 640px) {
        grid-template-columns: 1fr;
      }
    }

    .payment-option {
      cursor: pointer;

      input[type="radio"] {
        display: none;
      }

      .option-content {
        padding: 1rem;
        border: 2px solid $gray-200;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s ease;
        background: white;

        .option-icon {
          width: 40px;
          height: 40px;
          background: $gray-100;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;

          i {
            color: $gray-600;
            font-size: 1.1rem;
          }
        }

        .option-text {
          flex: 1;

          .option-title {
            display: block;
            font-weight: 600;
            color: $gray-900;
            margin-bottom: 0.25rem;
          }

          .option-subtitle {
            display: block;
            font-size: 0.85rem;
            color: $gray-600;
          }
        }
      }

      &:hover .option-content {
        border-color: $primary-color;
        box-shadow: 0 2px 8px rgba($primary-color, 0.15);
      }

      &.active .option-content {
        border-color: $primary-color;
        background: linear-gradient(135deg, rgba($primary-color, 0.05) 0%, rgba($primary-color, 0.02) 100%);
        box-shadow: 0 4px 12px rgba($primary-color, 0.2);

        .option-icon {
          background: $primary-color;

          i {
            color: white;
          }
        }
      }
    }
  }
}

// =============================================
// PIX INFO STYLING
// =============================================
.pix-info {
  margin-top: 2rem;

  .info-card {
    background: linear-gradient(135deg, rgba(230, 126, 34, 0.05) 0%, rgba(211, 84, 0, 0.02) 100%);
    border: 1px solid rgba(230, 126, 34, 0.15);
    padding: 2rem;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    position: relative;
    overflow: hidden;

    &::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #e67e22 0%, #d35400 100%);
    }

    h5 {
      margin: 0 0 1.5rem 0;
      font-size: 1.1rem;
      font-weight: 700;
      color: #0a0a0a;
      display: flex;
      align-items: center;
      gap: 0.75rem;

      i {
        color: #e67e22;
        font-size: 1.2rem;
        background: rgba(230, 126, 34, 0.1);
        padding: 8px;
        border-radius: 8px;
      }
    }

    ol {
      margin: 0;
      padding-left: 0;
      list-style: none;
      counter-reset: step-counter;

      li {
        margin-bottom: 1rem;
        color: #2a2d35;
        line-height: 1.5;
        padding-left: 3rem;
        position: relative;
        counter-increment: step-counter;

        &::before {
          content: counter(step-counter);
          position: absolute;
          left: 0;
          top: 0;
          width: 2rem;
          height: 2rem;
          background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
          color: white;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 0.85rem;
          font-weight: 700;
        }

        &:last-child {
          margin-bottom: 0;
        }
      }
    }

    @media (max-width: 640px) {
      padding: 1.5rem;
      border-radius: 12px;

      h5 {
        font-size: 1rem;
        
        i {
          padding: 6px;
          font-size: 1rem;
        }
      }

      ol li {
        padding-left: 2.5rem;
        font-size: 0.9rem;

        &::before {
          width: 1.75rem;
          height: 1.75rem;
          font-size: 0.8rem;
        }
      }
    }
  }
}

// =============================================
// ACTION BUTTONS
// =============================================
.checkout-actions {
  text-align: center;

  .pay-button {
    width: 100%;
    padding: 1.25rem 2rem;
    background: linear-gradient(135deg, $primary-color 0%, $primary-dark 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 4px 16px rgba($primary-color, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    margin-bottom: 1rem;

    &:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba($primary-color, 0.4);
    }

    &:disabled {
      opacity: 0.7;
      cursor: not-allowed;
      transform: none;
    }

    i {
      font-size: 1.2rem;
    }

    @media (max-width: 640px) {
      padding: 1rem 1.5rem;
      font-size: 1rem;
    }
  }

  .security-info {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: $gray-600;
    font-size: 0.85rem;

    i {
      color: $success-color;
    }
  }
}

// =============================================
// STATUS MESSAGES
// =============================================
.status-message {
  position: fixed;
  top: 100px;
  right: 2rem;
  padding: 1rem 1.5rem;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-weight: 600;
  z-index: 1000;
  max-width: 400px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);

  &.success {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
    border: 1px solid #c3e6cb;

    i {
      color: $success-color;
    }
  }

  &.error {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    color: #721c24;
    border: 1px solid #f5c6cb;

    i {
      color: $error-color;
    }
  }
}

// =============================================
// QR CODE MODAL
// =============================================
.qr-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);

  .qr-content {
    background: white;
    padding: 2.5rem;
    border-radius: 24px;
    text-align: center;
    max-width: 480px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease-out;

    .qr-header {
      margin-bottom: 2rem;

      h3 {
        margin: 0 0 1rem 0;
        font-size: 1.4rem;
        font-weight: 700;
        color: $gray-900;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;

        i {
          color: #e91e63;
          font-size: 1.6rem;
        }
      }

      .status-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        padding: 0.75rem 1.5rem;
        border-radius: 25px;
        font-size: 0.9rem;
        font-weight: 600;
        margin: 0 auto;
        max-width: fit-content;

        .status-dot {
          width: 12px;
          height: 12px;
          border-radius: 50%;
          animation: pulse 2s infinite;
        }

        &.pending {
          background: linear-gradient(135deg, rgba(251, 191, 36, 0.1), rgba(245, 158, 11, 0.05));
          color: #d97706;
          border: 1px solid rgba(251, 191, 36, 0.3);

          .status-dot {
            background: #f59e0b;
          }
        }

        &.checking {
          background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.05));
          color: #2563eb;
          border: 1px solid rgba(59, 130, 246, 0.3);

          .status-dot {
            background: #3b82f6;
          }
        }
      }
    }

    .qr-code {
      margin: 2rem 0;
      display: flex;
      justify-content: center;
      position: relative;

      img {
        width: 240px;
        height: 240px;
        border: 3px solid $gray-200;
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      }

      .scanning-animation {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        pointer-events: none;

        .scan-line {
          position: absolute;
          top: 0;
          left: 50%;
          transform: translateX(-50%);
          width: 200px;
          height: 2px;
          background: linear-gradient(90deg, transparent, #e91e63, transparent);
          animation: scanAnimation 2s linear infinite;
        }
      }
    }

    .qr-info {
      text-align: left;
      margin: 2rem 0;

      .amount-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background: linear-gradient(135deg, rgba($primary-color, 0.05), rgba($primary-color, 0.02));
        border-radius: 12px;
        border: 1px solid rgba($primary-color, 0.15);
        margin-bottom: 1.5rem;

        .amount-label {
          font-size: 0.9rem;
          color: $gray-600;
          font-weight: 500;
        }

        .amount-value {
          font-size: 1.3rem;
          font-weight: 700;
          color: $primary-color;
        }
      }

      .instructions {
        margin-bottom: 1.5rem;

        h4 {
          margin: 0 0 1rem 0;
          font-size: 1rem;
          font-weight: 600;
          color: $gray-900;
          display: flex;
          align-items: center;
          gap: 0.5rem;

          i {
            color: $primary-color;
          }
        }

        ol {
          margin: 0;
          padding-left: 0;
          list-style: none;
          counter-reset: step;

          li {
            margin-bottom: 0.75rem;
            padding-left: 2.5rem;
            position: relative;
            color: $gray-700;
            line-height: 1.4;
            counter-increment: step;

            &::before {
              content: counter(step);
              position: absolute;
              left: 0;
              top: 0;
              width: 1.8rem;
              height: 1.8rem;
              background: linear-gradient(135deg, $primary-color, $primary-dark);
              color: white;
              border-radius: 50%;
              display: flex;
              align-items: center;
              justify-content: center;
              font-size: 0.8rem;
              font-weight: 700;
            }

            &:last-child {
              margin-bottom: 0;
            }
          }
        }
      }

      .security-note {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        background: linear-gradient(135deg, rgba($success-color, 0.05), rgba($success-color, 0.02));
        border: 1px solid rgba($success-color, 0.2);
        border-radius: 12px;
        font-size: 0.85rem;
        color: $gray-700;

        i {
          color: $success-color;
          font-size: 1rem;
        }
      }
    }

    .qr-actions {
      margin-top: 2rem;

      .close-qr {
        padding: 0.875rem 2rem;
        background: $gray-600;
        color: white;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0 auto;

        &:hover {
          background: $gray-800;
          transform: translateY(-1px);
        }

        i {
          font-size: 0.9rem;
        }
      }
    }
  }
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: scale(0.9) translateY(-20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.7;
    transform: scale(1.1);
  }
}

@keyframes scanAnimation {
  0% {
    top: 10px;
    opacity: 0;
  }
  10% {
    opacity: 1;
  }
  90% {
    opacity: 1;
  }
  100% {
    top: calc(100% - 10px);
    opacity: 0;
  }
}

// =============================================
// DEBUG PANEL
// =============================================
.debug-panel {
  margin: 2rem 0;
  background: #2d3748;
  border-radius: 8px;
  overflow: hidden;

  details {
    summary {
      padding: 1rem;
      background: #4a5568;
      color: white;
      cursor: pointer;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.5rem;

      i {
        color: #fbb6ce;
      }
    }

    pre {
      margin: 0;
      padding: 1rem;
      color: #e2e8f0;
      font-size: 0.8rem;
      white-space: pre-wrap;
      word-wrap: break-word;
      overflow-x: auto;
    }
  }
}

// =============================================
// RESPONSIVE
// =============================================
@media (max-width: 768px) {
  .checkout-header .header-content h1 {
    font-size: 1.2rem;
  }

  .checkout-container {
    grid-template-columns: 1fr;
  }

  .order-summary-card {
    position: static;
    order: 2;
  }

  .payment-section {
    order: 1;
  }

  .payment-methods .method-selector {
    grid-template-columns: 1fr;
  }
}
</style>