# 🧪 Guia de Teste Local - Checkout Mercado Pago

## ✅ Configuração Concluída!

Suas credenciais foram movidas para variáveis de ambiente seguras:
- ✅ [.env](.env) - **Variáveis locais (NÃO commitado)**
- ✅ [.env.example](.env.example) - Template público
- ✅ [config.secure.php](../api/config.secure.php) - Backend
- ✅ [CheckoutPayment.vue](../src/shared/Components/CheckoutPayment.vue) - Usa process.env
- ✅ [index.html](../public/index.html) - SDK do Mercado Pago carregado

---

## 🚀 Como Testar Localmente (3 passos)

### **⚠️ PRIMEIRO: Configurar Variáveis de Ambiente**

Se ainda não tiver o arquivo `.env`, crie-o:

```bash
# Copiar template
cp .env.example .env
```

O arquivo `.env` já está configurado com credenciais de TESTE. Verifique se contém:

```env
VUE_APP_MERCADOPAGO_PUBLIC_KEY=TEST-fbfd5955-b86c-46e4-a7c1-f9adfb039d4a
VUE_APP_DEBUG_MODE=true
NODE_ENV=development
```

### **1️⃣ Iniciar Servidor PHP (Terminal 1)**

Abra um terminal no diretório raiz do projeto e execute:

```bash
php -S localhost:8000 -t api
```

**O que isso faz:**
- Inicia servidor PHP na porta 8000
- Raiz do servidor: pasta `/api`
- API disponível em: `http://localhost:8000/process_payment.php`

**Deixe este terminal aberto!** ⚠️

---

### **2️⃣ Iniciar Servidor Vue.js (Terminal 2)**

Abra **outro terminal** no diretório raiz e execute:

```bash
npm run serve
```

**O que isso faz:**
- Inicia servidor de desenvolvimento Vue.js
- Geralmente roda na porta 8080
- App disponível em: `http://localhost:8080`

**Deixe este terminal aberto também!** ⚠️

---

### **3️⃣ Acessar Página de Teste**

Abra seu navegador e acesse:

```
http://localhost:8080/test-checkout
```

Você verá uma página completa de teste com:
- 📋 Cartões de teste prontos para copiar
- 🔍 Status das configurações
- 💳 Formulário de checkout funcional
- 🐛 Logs no console do navegador

---

## 💳 Cartões de Teste (Copie e Cole)

### ✅ **Cartões que APROVAM:**

| Bandeira | Número | CVV | Validade |
|----------|--------|-----|----------|
| **Mastercard** | `5031 4332 1540 6351` | `123` | `12/28` |
| **Visa** | `4509 9535 6623 3704` | `123` | `12/28` |

### ❌ **Cartão que RECUSA:**

| Bandeira | Número | CVV | Validade |
|----------|--------|-----|----------|
| **Mastercard** | `5031 7557 3453 0604` | `123` | `12/28` |

### 📝 **Dados do Pagador:**
- **Nome:** Qualquer nome (ex: João Silva)
- **E-mail:** Qualquer e-mail válido (ex: teste@teste.com)
- **CPF:** `123.456.789-09`

---

## 📱 Testando Pix

1. Selecione a opção **Pix** no checkout
2. Preencha e-mail e CPF
3. Clique em **Pagar**
4. Um QR Code será gerado (é de teste, não precisa pagar!)
5. Você verá o código para copiar e colar

---

## 🐛 Debug e Troubleshooting

### **Console do Navegador (F12)**

Abra o console para ver logs detalhados:

```
🧪 MODO DE TESTE ATIVO
📋 Use estes cartões de teste:
✅ Mastercard (Aprovado): 5031 4332 1540 6351
✅ Visa (Aprovado): 4509 9535 6623 3704
❌ Recusado: 5031 7557 3453 0604
🔍 Acompanhe as requisições na aba Network
```

### **Aba Network (F12)**

Veja as requisições para a API:
1. Procure por `process_payment.php`
2. Veja o **Payload** (Request)
3. Veja a **Response** (Resposta do servidor)

---

## 🔍 Verificar se Está Funcionando

### **Teste 1: Servidor PHP**
```bash
curl http://localhost:8000/process_payment.php
```

**Resposta esperada:**
```json
{
  "success": false,
  "message": "Método não permitido. Use POST"
}
```
✅ Se viu isso, o servidor PHP está OK!

### **Teste 2: API Config**
```bash
curl http://localhost:8000/config.secure.php
```

**Resposta esperada:**
```
🚫 Acesso negado!
```
✅ Perfeito! O arquivo está protegido!

---

## ❌ Problemas Comuns

### **Erro: "CORS Policy"**

**Problema:** Navegador bloqueia requisições entre diferentes origens.

**Solução 1 - Ajustar URL da API:**

Edite [CheckoutPayment.vue](../src/shared/Components/CheckoutPayment.vue) e mude a linha da API:

```javascript
// De:
const response = await fetch('/api/process_payment.php', {

// Para:
const response = await fetch('http://localhost:8000/process_payment.php', {
```

**Solução 2 - Proxy no Vue (Recomendado):**

Crie `vue.config.js` na raiz do projeto:

```javascript
module.exports = {
  devServer: {
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        pathRewrite: {
          '^/api': ''
        }
      }
    }
  }
}
```

Depois reinicie o servidor Vue (`npm run serve`).

---

### **Erro: "Failed to load resource: net::ERR_CONNECTION_REFUSED"**

**Problema:** Servidor PHP não está rodando.

**Solução:**
```bash
# Certifique-se de que o servidor PHP está rodando
php -S localhost:8000 -t api
```

---

### **Erro: "config.secure.php não encontrado"**

**Problema:** Arquivo de configuração não existe.

**Solução:**
```bash
cd api
cp config.example.php config.secure.php
# Edite config.secure.php (as credenciais já estão configuradas!)
```

---

### **Erro: "MercadoPago is not defined"**

**Problema:** SDK do Mercado Pago não carregou.

**Solução:** Verifique se o script está no [index.html](../public/index.html):
```html
<script src="https://sdk.mercadopago.com/js/v2"></script>
```

Depois, limpe o cache e recarregue (Ctrl + Shift + R).

---

## 📊 Fluxo Completo de Teste

```
┌─────────────────────────────────────────────────┐
│  1. Usuário acessa http://localhost:8080        │
│     /test-checkout                              │
└──────────────────┬──────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────┐
│  2. Vue.js carrega CheckoutPayment.vue          │
│     • Mercado Pago SDK inicializa               │
│     • Campos de cartão são renderizados         │
└──────────────────┬──────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────┐
│  3. Usuário preenche cartão e clica "Pagar"     │
│     • SDK tokeniza os dados do cartão           │
│     • Token é gerado (ex: abc123...)            │
└──────────────────┬──────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────┐
│  4. Vue.js envia POST para API PHP              │
│     POST http://localhost:8000/process_payment  │
│     Body: { token, amount, email... }           │
└──────────────────┬──────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────┐
│  5. PHP (process_payment.php)                   │
│     • Carrega config.secure.php                 │
│     • Valida credenciais                        │
│     • Faz cURL para Mercado Pago                │
└──────────────────┬──────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────┐
│  6. Mercado Pago processa                       │
│     • Valida token                              │
│     • Processa pagamento                        │
│     • Retorna status (approved/rejected)        │
└──────────────────┬──────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────┐
│  7. PHP retorna JSON para Vue.js                │
│     { success: true, payment_id: 123... }       │
└──────────────────┬──────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────┐
│  8. Vue.js mostra mensagem ao usuário           │
│     ✅ "Pagamento aprovado com sucesso!"        │
└─────────────────────────────────────────────────┘
```

---

## 🎯 Checklist de Teste

Use este checklist para validar tudo:

- [ ] Servidor PHP rodando (`php -S localhost:8000 -t api`)
- [ ] Servidor Vue rodando (`npm run serve`)
- [ ] Página de teste aberta (`http://localhost:8080/test-checkout`)
- [ ] Console do navegador aberto (F12)
- [ ] Testado cartão APROVADO (5031 4332 1540 6351)
- [ ] Testado cartão RECUSADO (5031 7557 3453 0604)
- [ ] Testado pagamento Pix (QR Code gerado)
- [ ] Verificado logs no console
- [ ] Verificado requisições na aba Network

---

## 🚀 Colocar em Produção

Quando estiver pronto para produção:

### **1. Trocar Credenciais**

Edite o arquivo `.env` na raiz do projeto:

```env
# Comente a linha de TESTE:
# VUE_APP_MERCADOPAGO_PUBLIC_KEY=TEST-fbfd5955-b86c-46e4-a7c1-f9adfb039d4a

# Descomente e use a de PRODUÇÃO:
VUE_APP_MERCADOPAGO_PUBLIC_KEY=APP_USR-47d5fd7b-7d0d-4e1e-8408-84548c817127

# Desativar debug
VUE_APP_DEBUG_MODE=false

# Alterar ambiente
NODE_ENV=production
```

Também edite [config.secure.php](../api/config.secure.php) no backend:

```php
// Comente estas linhas (TESTE):
// define('MP_ACCESS_TOKEN', 'TEST-...');
// define('MP_PUBLIC_KEY', 'TEST-...');

// Descomente estas linhas (PRODUÇÃO):
define('MP_ACCESS_TOKEN', 'APP_USR-5978504079023879-012208-4b77eebae63f65d8a33a90b2730fab0d-487474845');
define('MP_PUBLIC_KEY', 'APP_USR-47d5fd7b-7d0d-4e1e-8408-84548c817127');
```

### **2. Desativar Debug**

Já configurado no `.env` acima (VUE_APP_DEBUG_MODE=false).

### **3. Build do Frontend**

### **3. Build do Frontend**

```bash
# Build do Vue.js (vai usar as variáveis do .env)
npm run build

# Enviar pasta /dist e /api para servidor
# ⚠️ NÃO envie o arquivo .env - configure as variáveis no servidor
```

### **4. Configurar .env no Servidor**

No servidor de produção, crie um novo arquivo `.env` com as credenciais de produção:

```bash
# SSH no servidor
ssh usuario@servidor.com
cd /var/www/html

# Criar .env com credenciais de produção
nano .env
```

---

## 📚 Documentação Oficial

- **Cartões de Teste:** https://www.mercadopago.com.br/developers/pt/docs/checkout-api/integration-test/test-cards
- **API de Pagamentos:** https://www.mercadopago.com.br/developers/pt/reference/payments/_payments/post
- **Seu Painel:** https://www.mercadopago.com.br/developers/panel/app/5978504079023879

---

## 💡 Dicas Finais

1. **Sempre teste com credenciais de TESTE antes de produção**
2. **Nunca commite o arquivo `config.secure.php`** (já está no .gitignore)
3. **Use o debug mode para ver o que está acontecendo**
4. **Acompanhe os logs no painel do Mercado Pago**

---

✅ **Tudo pronto! Bora testar!** 🚀

Acesse agora: http://localhost:8080/test-checkout
