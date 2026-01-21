# 🧪 Guia de Testes - Dual-Layer Pricing

## 🎯 O Que Foi Implementado

### Frontend (Vue)
- ✅ **PricingService.js** - Wrapper para API com fallback
- ✅ **SiteConfigurator.vue** - Calcula local + valida server
- ✅ Indicador visual de validação (verde/cinza)
- ✅ Debounce de 500ms nas validações

### Backend (PHP)
- ✅ **pricing.json** - Configuração oficial
- ✅ **lib/pricing.php** - Core de cálculo server-side
- ✅ **lib/storage.php** - Persistência de pedidos
- ✅ **config.php** - GET catálogo
- ✅ **price.php** - POST calcular preço
- ✅ **order_create.php** - POST criar pedido
- ✅ **payment_create.php** - POST criar pagamento

---

## 🧑‍💻 Teste 1: Sem PHP (Modo Desenvolvimento)

### Executar

```bash
# Apenas o frontend
npm run serve
```

### Acessar

```
http://localhost:8080/site-vitrine
```

### Verificar

1. **Hero:** Clicar em "Monte Seu Site Agora"
2. **Etapa 1:** Selecionar "Landing Page"
3. **Sidebar:** Deve mostrar:
   - 🔵 "Validando..." (por 500ms)
   - ⚪ "Cálculo local" (API offline)
   - Preço: R$ 599,00

4. **Etapa 2:** Adicionar "Vídeo"
   - ⚪ "Cálculo local"
   - Preço: R$ 678,00

5. **Etapa 3:** Preencher formulário e clicar "Finalizar"
   - ⚠️ Alert: "Modo Desenvolvimento - API offline"
   - ✅ Console mostra mock data

### Console Esperado

```
🟡 API não disponível (modo desenvolvimento): ...
ℹ️ Usando cálculo local (servidor offline)
🟡 Servidor offline - pedido simulado: { order_id: 'MOCK-...', ... }
```

---

## 🐘 Teste 2: Com PHP (Produção Simulada)

### Pré-requisitos

- Apache/Nginx com PHP 7.4+
- Ou usar PHP built-in server

### Executar

```bash
# Terminal 1: Frontend
npm run serve

# Terminal 2: PHP Server
cd api
php -S localhost:8000
```

### Ajustar Base URL

**Se usar porta diferente de 80:**

```javascript
// src/core/services/PricingService.js
const API_BASE_URL = 'http://localhost:8000'; // Adicionar porta
```

### Acessar

```
http://localhost:8080/site-vitrine
```

### Verificar

1. **Etapa 1:** Selecionar "Site Completo"
   - 🔵 "Validando..."
   - 🟢 "Preços validados" (servidor respondeu!)
   - Preço: R$ 799,00

2. **Etapa 2:** Adicionar "Sobre Nós" (199) + "Vídeo" (79)
   - 🔵 "Validando..."
   - 🟢 "Preços validados"
   - Preço: R$ 1.077,00

3. **Etapa 3:** Finalizar pedido
   - ✅ Alert: "Pedido criado com sucesso! ID: ORD-20260121-..."
   - ✅ Console mostra dados do servidor

### Console Esperado

```
✅ API Server disponível
⚠️ DISCREPÂNCIA: Local vs Server (se houver diferença)
✅ Pedido criado: ORD-20260121-A3F7B9C1
```

### Verificar Arquivo

```bash
cat api/orders/ORD-20260121-*.json
```

**Deve conter:**
```json
{
  "order_id": "ORD-20260121-A3F7B9C1",
  "status": "pending",
  "selection": { "product": "site_complete", ... },
  "pricing": { "subtotal": 1077, "avista": 969.30, ... },
  "briefing": { ... }
}
```

---

## 🧪 Teste 3: Validação de Limites

### Objetivo
Verificar que servidor aplica whitelist e limites.

### Passo a Passo

1. **Produto Inválido (via DevTools)**

```javascript
// Console do navegador:
fetch('/api/price.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ 
    product: 'HACK', // ❌ Inválido
    pages: {},
    content: []
  })
}).then(r => r.json()).then(console.log);
```

**Esperado:**
```json
{
  "normalized": {
    "product": "landing", // ✅ Fallback para primeiro válido
    "pages": {},
    "content": []
  }
}
```

2. **Quantidade Excessiva**

```javascript
fetch('/api/price.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ 
    product: 'site_complete',
    pages: { about: 999 }, // ❌ Muito!
    content: []
  })
}).then(r => r.json()).then(console.log);
```

**Esperado:**
```json
{
  "normalized": {
    "product": "site_complete",
    "pages": { "about": 5 }, // ✅ Limitado a max
    "content": []
  }
}
```

3. **Landing Page com Páginas Extras**

```javascript
fetch('/api/price.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ 
    product: 'landing',
    pages: { about: 2 }, // ❌ Landing não permite
    content: []
  })
}).then(r => r.json()).then(console.log);
```

**Esperado:**
```json
{
  "normalized": {
    "product": "landing",
    "pages": { "about": 0 }, // ✅ Bloqueado
    "content": []
  },
  "pricing": {
    "subtotal": 599 // ✅ Apenas base
  }
}
```

---

## 🔍 Teste 4: Comparação Local vs Server

### Script de Teste

```javascript
// Console do navegador (na página do configurador)
const testCalculations = async () => {
  const selection = {
    product: 'site_complete',
    pages: { about: 1, services: 1 },
    content: ['video']
  };
  
  // Cálculo local
  const local = {
    subtotal: 799 + 199 + 249 + 79, // 1326
    avista: 1326 * 0.90, // 1193.40
    parcelado: 1326 * 1.10 // 1458.60
  };
  
  // Cálculo servidor
  const server = await fetch('/api/price.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(selection)
  }).then(r => r.json());
  
  console.table({
    Local: local,
    Server: server.pricing
  });
  
  // Verificar diferenças
  const diff = Math.abs(local.subtotal - server.pricing.subtotal);
  if (diff > 0.01) {
    console.error('❌ DISCREPÂNCIA:', diff);
  } else {
    console.log('✅ Valores idênticos');
  }
};

testCalculations();
```

---

## 📊 Checklist de Validação

### Funcionalidades Básicas
- [ ] Configurador carrega sem erros
- [ ] Sidebar mostra preços
- [ ] Botões +/- funcionam
- [ ] Toggles de conteúdo funcionam
- [ ] Navegação entre etapas funciona

### Modo Offline (Sem PHP)
- [ ] Indicador mostra "Cálculo local"
- [ ] Preços são calculados localmente
- [ ] Submit mostra "Modo Desenvolvimento"
- [ ] Console mostra "API offline"

### Modo Online (Com PHP)
- [ ] Indicador mostra "Preços validados"
- [ ] Servidor valida seleções
- [ ] Pedido é salvo em `/api/orders/`
- [ ] Console mostra "API disponível"

### Segurança
- [ ] Servidor rejeita produtos inválidos
- [ ] Servidor limita quantidades
- [ ] Landing Page não permite páginas extras
- [ ] Servidor recalcula preços (não confia no cliente)

### UX
- [ ] Debounce funciona (500ms)
- [ ] Spinner aparece durante validação
- [ ] Transições suaves
- [ ] Alertas informativos

---

## 🐛 Debug

### API não responde

```bash
# Verificar se PHP está rodando
curl http://localhost:8000/api/config.php

# Deve retornar JSON com produtos
```

### CORS error

```php
// Em cada endpoint PHP, adicionar:
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
```

### Preços diferentes

1. Comparar JSONs:
   ```bash
   diff src/pages/SiteVitrine/config/site-configurator.json api/pricing.json
   ```

2. Verificar cálculos:
   - Frontend: `SiteConfigurator.vue` computed properties
   - Backend: `api/lib/pricing.php` function `compute_price()`

---

## 📝 Logs Úteis

```javascript
// Habilitar logs detalhados
localStorage.setItem('DEBUG_PRICING', 'true');

// Ver histórico de validações
PricingService.validationHistory
```

---

**Pronto para testar!** 🚀

Qualquer problema, verificar console do navegador e logs do PHP.
