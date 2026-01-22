<template>
  <div class="test-checkout-page">
    <div class="test-header">
      <h1>🧪 Teste de Checkout - Mercado Pago</h1>
      <p class="test-subtitle">Ambiente de Teste - Credenciais TEST ativas</p>
      
      <div class="test-info-box">
        <h3>📋 Cartões de Teste do Mercado Pago:</h3>
        <div class="test-cards">
          <div class="card-item">
            <strong>✅ Mastercard (Aprovado):</strong>
            <code>5031 4332 1540 6351</code>
          </div>
          <div class="card-item">
            <strong>✅ Visa (Aprovado):</strong>
            <code>4509 9535 6623 3704</code>
          </div>
          <div class="card-item">
            <strong>❌ Recusado:</strong>
            <code>5031 7557 3453 0604</code>
          </div>
        </div>
        <div class="card-details">
          <p><strong>Nome:</strong> Qualquer nome</p>
          <p><strong>CVV:</strong> 123</p>
          <p><strong>Validade:</strong> Qualquer data futura (ex: 12/28)</p>
          <p><strong>CPF:</strong> 123.456.789-09</p>
        </div>
      </div>

      <div class="test-status">
        <div class="status-item">
          <span class="status-label">🔑 Public Key:</span>
          <code>TEST-fbfd5955...</code>
        </div>
        <div class="status-item">
          <span class="status-label">🌐 Ambiente:</span>
          <span class="badge">TESTE</span>
        </div>
        <div class="status-item">
          <span class="status-label">🐛 Debug:</span>
          <span class="badge active">ATIVO</span>
        </div>
      </div>
    </div>

    <!-- Componente de Checkout -->
    <div class="checkout-container">
      <CheckoutPayment />
    </div>

    <div class="test-footer">
      <h3>💡 Dicas de Teste:</h3>
      <ul>
        <li><strong>Pix:</strong> Vai gerar um QR Code de teste (não precisa pagar de verdade)</li>
        <li><strong>Cartão:</strong> Use os números acima para testar aprovação/rejeição</li>
        <li><strong>Debug:</strong> Abra o Console (F12) para ver logs detalhados</li>
        <li><strong>Network:</strong> Na aba Network (F12), veja as requisições para a API PHP</li>
      </ul>

      <div class="test-links">
        <a href="https://www.mercadopago.com.br/developers/pt/docs/checkout-api/integration-test/test-cards" target="_blank" class="link-button">
          📖 Documentação Oficial
        </a>
        <a href="https://www.mercadopago.com.br/developers/panel/app/5978504079023879" target="_blank" class="link-button">
          🔧 Painel Desenvolvedor
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import CheckoutPayment from '@/shared/Components/CheckoutPayment.vue'

onMounted(() => {
  console.log('%c🧪 MODO DE TESTE ATIVO', 'background: #00b4d8; color: white; font-size: 16px; padding: 10px; font-weight: bold;')
  console.log('%c📋 Use estes cartões de teste:', 'font-size: 14px; font-weight: bold; color: #333;')
  console.log('✅ Mastercard (Aprovado): 5031 4332 1540 6351')
  console.log('✅ Visa (Aprovado): 4509 9535 6623 3704')
  console.log('❌ Recusado: 5031 7557 3453 0604')
  console.log('%c🔍 Acompanhe as requisições na aba Network', 'color: #0077b6; font-style: italic;')
})
</script>

<style scoped>
.test-checkout-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Header */
.test-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 2rem;
  border-radius: 12px;
  margin-bottom: 2rem;
  box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
}

.test-header h1 {
  margin: 0 0 0.5rem 0;
  font-size: 2rem;
}

.test-subtitle {
  margin: 0;
  opacity: 0.9;
  font-size: 1.1rem;
}

/* Info Box */
.test-info-box {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  border-radius: 8px;
  padding: 1.5rem;
  margin-top: 1.5rem;
}

.test-info-box h3 {
  margin: 0 0 1rem 0;
  font-size: 1.2rem;
}

.test-cards {
  display: grid;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.card-item {
  background: rgba(255, 255, 255, 0.2);
  padding: 0.75rem;
  border-radius: 6px;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.card-item strong {
  min-width: 180px;
}

.card-item code {
  background: rgba(0, 0, 0, 0.3);
  padding: 0.25rem 0.75rem;
  border-radius: 4px;
  font-family: 'Courier New', monospace;
  font-size: 1rem;
  letter-spacing: 1px;
}

.card-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 0.5rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.3);
}

.card-details p {
  margin: 0;
  font-size: 0.95rem;
}

/* Status Bar */
.test-status {
  display: flex;
  gap: 2rem;
  margin-top: 1.5rem;
  flex-wrap: wrap;
}

.status-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.status-label {
  font-weight: 600;
}

.badge {
  background: rgba(255, 255, 255, 0.3);
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 600;
}

.badge.active {
  background: #4ade80;
  color: #166534;
}

code {
  background: rgba(255, 255, 255, 0.2);
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-family: 'Courier New', monospace;
}

/* Checkout Container */
.checkout-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 16px rgba(0, 0, 0, 0.1);
  padding: 2rem;
  margin-bottom: 2rem;
}

/* Footer */
.test-footer {
  background: #f8f9fa;
  padding: 2rem;
  border-radius: 12px;
  border-left: 4px solid #00b4d8;
}

.test-footer h3 {
  margin: 0 0 1rem 0;
  color: #333;
}

.test-footer ul {
  margin: 0;
  padding-left: 1.5rem;
}

.test-footer li {
  margin-bottom: 0.75rem;
  line-height: 1.6;
  color: #555;
}

.test-footer li strong {
  color: #00b4d8;
}

.test-links {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
  flex-wrap: wrap;
}

.link-button {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: #00b4d8;
  color: white;
  text-decoration: none;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.link-button:hover {
  background: #0077b6;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 119, 182, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
  .test-checkout-page {
    padding: 1rem;
  }

  .test-header {
    padding: 1.5rem;
  }

  .test-header h1 {
    font-size: 1.5rem;
  }

  .card-item {
    flex-direction: column;
    align-items: flex-start;
  }

  .card-item strong {
    min-width: auto;
  }

  .test-status {
    flex-direction: column;
    gap: 0.75rem;
  }

  .checkout-container {
    padding: 1rem;
  }
}
</style>
