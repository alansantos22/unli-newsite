# 🔐 Arquitetura Dual-Layer: Client-Side + Server-Authoritative

## Visão Geral

Sistema híbrido que combina **cálculos locais** (feedback imediato) com **validação server-side** (segurança).

### Princípios

✅ **Frontend:** Calcula localmente para UX fluida  
✅ **Backend:** Valida e aplica como fonte de verdade  
✅ **Fallback:** Funciona offline (modo desenvolvimento)  
✅ **Zero Trust:** Cliente nunca envia preços, apenas escolhas  

---

## 🎯 Fluxo de Dados

### 1. Interação do Usuário (Tempo Real)

```
Usuário clica "+Sobre Nós"
  ↓
Frontend calcula localmente (< 1ms)
  ↓
Atualiza UI imediatamente
  ↓
Debounce 500ms
  ↓
Envia seleções para servidor (background)
```

**Vantagem:** Usuário vê preço atualizar instantaneamente.

### 2. Validação em Background

```javascript
// SiteConfigurator.vue - watchers
watch: {
  selectedProduct() { this.validatePriceOnServer(); },
  selectedPages() { this.validatePriceOnServer(); },
  selectedContentAddons() { this.validatePriceOnServer(); }
}
```

**POST** `/api/price.php`
```json
{
  "product": "site_complete",
  "pages": { "about": 1 },
  "content": ["video"]
}
```

**Resposta:**
```json
{
  "ok": true,
  "normalized": { "product": "site_complete", "pages": {...} },
  "pricing": {
    "subtotal": 1077.00,
    "avista": 969.30,
    "parcelado_total": 1184.70,
    "parcela_12": 98.73,
    "source": "server"
  }
}
```

### 3. Sincronização

```javascript
// PricingService.js
if (result.serverValidated) {
  // Usar valores do servidor
  this.serverValidatedPricing = result.pricing;
  
  // Se servidor normalizou seleções, aplicar
  if (result.normalized !== selection) {
    this.selectedProduct = result.normalized.product;
  }
} else {
  // Servidor offline: usar valores locais
  this.serverValidatedPricing = localPricing;
}
```

---

## 🔒 Proteções de Segurança

### No Frontend (SiteConfigurator.vue)

```javascript
// ❌ Nunca envia preço
const orderData = {
  product: this.selectedProduct,
  pages: this.selectedPages,
  content: this.selectedContentAddons,
  briefing: this.briefing,
  payment_method: 'avista'
  // ❌ SEM: pricing: { ... }
};
```

### No Backend (price.php)

```php
// 1. Normaliza seleções (whitelist)
$selection = normalize_selection($body, $cfg);

// 2. RECALCULA preço (não confia no cliente)
$pricing = compute_price($selection, $cfg);

// 3. Retorna valores oficiais
return [
  'normalized' => $selection, // Corrigido pelo servidor
  'pricing' => $pricing       // Calculado pelo servidor
];
```

### Validações Aplicadas

- ✅ **Whitelist de produtos:** Apenas `landing` ou `site_complete`
- ✅ **Limites de páginas:** Max 5 páginas extras
- ✅ **Bloqueio de páginas extras em Landing Page**
- ✅ **Sanitização de inputs:** `strip_tags`, `filter_var`
- ✅ **Validação de email/whatsapp**

---

## 🧪 Modo Desenvolvimento (Sem PHP)

### Como Funciona

1. **Frontend tenta conectar à API**
   ```javascript
   this.serverAvailable = await PricingService.checkServerAvailability();
   ```

2. **Se API não responder:**
   ```javascript
   if (this.serverAvailable === false) {
     console.warn('🟡 API offline - usando cálculos locais');
     return {
       pricing: localPricing,
       serverValidated: false
     };
   }
   ```

3. **Indicador Visual:**
   - 🟢 **Validado:** Ícone verde "Preços validados"
   - 🔵 **Validando...:** Spinner azul
   - ⚪ **Cálculo local:** Ícone cinza "Cálculo local"

### Submit sem Servidor

```javascript
async createOrder(orderData) {
  if (this.serverAvailable === false) {
    return {
      ok: false,
      offline: true,
      mockData: {
        order_id: 'MOCK-' + Date.now(),
        status: 'pending_api'
      }
    };
  }
}
```

**Resultado:**  
⚠️ Alert: "Modo Desenvolvimento - API offline - pedido simulado localmente"

---

## 📊 Comparação: Local vs Server

### Cálculo Local (Frontend)

```javascript
// computed properties
subtotal() {
  return this.basePrice + this.pagesTotal + this.contentTotal;
}

cashPrice() {
  const discount = 0.10; // 10%
  return this.subtotal * (1 - discount);
}
```

**Usado para:**
- ✅ Feedback visual imediato
- ✅ Modo desenvolvimento
- ✅ Fallback se servidor offline

### Cálculo Server (Backend)

```php
// lib/pricing.php
function compute_price($selection, $cfg) {
  $subtotal = $basePrice;
  
  // Páginas adicionais (com validação)
  foreach ($selection['pages'] as $key => $qty) {
    $subtotal += $pageAddons[$key]['price'] * $qty;
  }
  
  // Aplicar regras oficiais
  $avista = $subtotal * 0.90;
  $parcelado = $subtotal * 1.10;
  
  return compact('subtotal', 'avista', 'parcelado');
}
```

**Usado para:**
- ✅ **Fonte de verdade** em produção
- ✅ Validação final antes de checkout
- ✅ Criação de pagamento no gateway

---

## 🎬 Cenários de Uso

### Cenário 1: API Online (Produção)

```
1. Usuário seleciona "Landing Page"
   → Frontend calcula: R$ 599,00
   → Servidor valida: R$ 599,00
   ✅ Sincronizado

2. Usuário adiciona "Vídeo"
   → Frontend calcula: R$ 678,00
   → Servidor valida: R$ 678,00
   ✅ Sincronizado

3. Usuário clica "Finalizar Pedido"
   → POST /api/order_create.php
   → Servidor RECALCULA: R$ 678,00
   → Cria order_id: ORD-20260121-A3F7B9C1
   ✅ Pedido criado

4. Redireciona para pagamento
   → POST /api/payment_create.php
   → Gateway recebe: R$ 610,20 (à vista)
   ✅ Checkout seguro
```

### Cenário 2: API Offline (Desenvolvimento)

```
1. Usuário seleciona "Site Completo"
   → Frontend calcula: R$ 799,00
   → Servidor não responde
   ⚠️ Indicador: "Cálculo local"

2. Usuário adiciona páginas
   → Frontend calcula: R$ 1.077,00
   → Servidor não responde
   ⚠️ Ainda em modo local

3. Usuário clica "Finalizar Pedido"
   → PricingService.createOrder()
   → Detecta API offline
   → Retorna mockData
   ⚠️ Alert: "Modo Desenvolvimento"

4. Emite evento com dados locais
   → console.log() no pai
   ✅ Fluxo completo (sem backend)
```

### Cenário 3: Discrepância Detectada

```
Frontend calcula: R$ 1.200,00
Servidor valida: R$ 1.077,00
  ↓
console.warn('⚠️ DISCREPÂNCIA', {
  local: 1200,
  server: 1077,
  diff: 123
})
  ↓
Frontend APLICA valor do servidor
  ↓
UI atualiza para R$ 1.077,00
✅ Servidor venceu (como deve ser)
```

---

## 🚀 Deploy e Configuração

### Produção (Com PHP)

```bash
# 1. Copiar API para servidor
cp -r api/ /var/www/html/

# 2. Dar permissões
chmod 755 /var/www/html/api
chmod 755 /var/www/html/api/orders

# 3. Configurar CORS (se necessário)
# Em config.php, price.php, etc:
header('Access-Control-Allow-Origin: https://seusite.com');
```

### Desenvolvimento (Sem PHP)

```bash
# Frontend roda normalmente
npm run serve

# API não está rodando
# → Frontend usa cálculos locais
# → Indicador mostra "Cálculo local"
# → Submit cria pedido mock
```

---

## 🔧 Troubleshooting

### Problema: Preços diferentes entre local e servidor

```javascript
// Verificar no console:
console.warn('⚠️ DISCREPÂNCIA', {
  local: this.subtotal,
  server: this.serverValidatedPricing.subtotal
});
```

**Causa comum:**
- ❌ `pricing.json` desatualizado no frontend
- ❌ `config/site-configurator.json` diferente de `api/pricing.json`

**Solução:**
- ✅ Usar **single source of truth:** `api/pricing.json`
- ✅ Frontend carrega via `GET /api/config.php` (em produção)

### Problema: API sempre offline

```javascript
// Testar manualmente:
fetch('/api/config.php')
  .then(r => r.json())
  .then(console.log);
```

**Verificar:**
- ❌ Servidor PHP rodando?
- ❌ Caminho correto? (`/api/config.php` ou `/api/config`)
- ❌ CORS bloqueando?

---

## 📈 Próximos Passos

- [ ] Migrar de JSON para MySQL
- [ ] Integrar gateway de pagamento real
- [ ] Webhook para confirmar pagamento
- [ ] Fazer frontend carregar config do servidor (`GET /api/config.php`)
- [ ] Rate limiting e CAPTCHA
- [ ] Logs e analytics de pedidos

---

**Versão:** 1.0.0  
**Data:** Janeiro 2026  
**Status:** ✅ Dual-layer implementado
