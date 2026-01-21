# 🔧 DEBUG MODE - Guia de Uso

## O Que É?

Sistema que permite desenvolver **sem rodar PHP localmente**. Quando ativado, força o uso de cálculos locais e simula respostas da API.

---

## 🎛️ Como Funciona

### ConfigModule (Vuex Store)

```javascript
// src/core/store/config/Config.js
state: {
  debug: process.env.NODE_ENV !== 'production'
}
```

**Comportamento automático:**
- ✅ **Desenvolvimento:** `debug = true` (não chama PHP)
- ✅ **Produção:** `debug = false` (usa API PHP)

---

## 🚀 Uso no Desenvolvimento

### Opção 1: Automático (Recomendado)

```bash
npm run serve
# Debug ativado automaticamente
```

**Console mostra:**
```
🔧 DEBUG MODE: API desabilitada (forçando modo local)
🔧 DEBUG MODE: Pedido simulado (API desabilitada)
```

### Opção 2: Controle Manual (DevTools)

```javascript
// Console do navegador:

// Ver estado atual
$store.state.ConfigModule.debug

// Ativar debug (forçar modo local)
$store.dispatch('ConfigModule/enableDebug')

// Desativar debug (tentar usar API)
$store.dispatch('ConfigModule/disableDebug')

// Toggle
$store.dispatch('ConfigModule/toggleDebug')
```

### Opção 3: Criar Botão Toggle

```vue
<!-- Adicionar em qualquer componente (dev) -->
<template>
  <button 
    @click="$store.dispatch('ConfigModule/toggleDebug')"
    style="position: fixed; top: 10px; right: 10px; z-index: 9999;"
  >
    Debug: {{ $store.state.ConfigModule.debug ? 'ON' : 'OFF' }}
  </button>
</template>
```

---

## 📦 Build de Produção

### Compilar para Deploy

```bash
npm run build
```

**O que acontece:**
1. `process.env.NODE_ENV` = `'production'`
2. ConfigModule detecta e seta `debug = false`
3. Build gera `dist/` com debug desabilitado
4. API será chamada normalmente em produção

### Verificar Build

```bash
# Simular produção localmente
npm install -g serve
serve -s dist

# Abrir console no navegador:
$store.state.ConfigModule.debug
// false (desabilitado)
```

---

## 🔍 Como Testar

### Cenário 1: Debug ON (Desenvolvimento)

```javascript
// 1. Verificar estado
console.log($store.state.ConfigModule);
// { debug: true, environment: 'development' }

// 2. Tentar criar pedido
// Resultado: Mock sem chamar API
// Console: "🔧 DEBUG MODE: Pedido simulado"
// Alert: "DEBUG: Pedido simulado localmente"
```

### Cenário 2: Debug OFF (Produção)

```javascript
// 1. Desativar manualmente (simular produção)
$store.dispatch('ConfigModule/disableDebug');

// 2. Verificar
console.log($store.state.ConfigModule.debug);
// false

// 3. Tentar criar pedido
// Resultado: Tenta chamar API PHP
// Se PHP offline: "API offline"
// Se PHP online: "Pedido criado: ORD-..."
```

---

## 🎯 Fluxo Completo

### Desenvolvimento (sem PHP)

```
Usuario interage
  ↓
Debug = true
  ↓
PricingService.isDebugMode() = true
  ↓
checkServerAvailability() → retorna false imediatamente
  ↓
validateOnServer() → retorna null (não chama)
  ↓
createOrder() → retorna mock { order_id: 'DEBUG-123...', debugMode: true }
  ↓
Alert: "DEBUG: Pedido simulado localmente"
  ↓
✅ Funciona 100% sem PHP
```

### Produção (com PHP)

```
Usuario interage
  ↓
Debug = false (NODE_ENV=production)
  ↓
PricingService.isDebugMode() = false
  ↓
checkServerAvailability() → testa /api/config.php
  ↓
validateOnServer() → POST /api/price.php
  ↓
createOrder() → POST /api/order_create.php
  ↓
Servidor responde: { order_id: 'ORD-...', pricing: {...} }
  ↓
✅ Valores validados pelo servidor
```

---

## 🛡️ Segurança

### O Que Debug Mode NÃO Faz

❌ Não expõe dados sensíveis  
❌ Não burla validações (servidor valida de qualquer forma)  
❌ Não permite fraude (em produção debug=false sempre)  

### O Que Debug Mode FAZ

✅ Facilita desenvolvimento local  
✅ Evita erros de CORS/404 durante dev  
✅ Permite testar UX sem backend  
✅ Automaticamente desabilitado em build  

---

## 🔧 Configuração Avançada

### Variáveis de Ambiente (.env)

```bash
# .env.development (local)
NODE_ENV=development
VUE_APP_API_URL=/api

# .env.production (build)
NODE_ENV=production
VUE_APP_API_URL=https://api.seusite.com
```

### Forçar Debug OFF em Dev

```javascript
// src/core/store/config/Config.js
state: {
  debug: false, // Forçar desabilitar mesmo em dev
  // ...
}
```

### API URL Customizada

```javascript
// Console do navegador:
$store.dispatch('ConfigModule/setApiUrl', 'http://localhost:8000/api');
```

---

## 📊 Checklist de Validação

### Desenvolvimento
- [ ] `npm run serve` → Debug ON automático
- [ ] Sidebar mostra "Cálculo local"
- [ ] Submit mostra "DEBUG: Pedido simulado"
- [ ] Console mostra "🔧 DEBUG MODE"
- [ ] Nenhuma chamada à `/api/*` (Network tab vazia)

### Produção
- [ ] `npm run build` → Gera dist/
- [ ] Deploy para servidor
- [ ] Abrir site em produção
- [ ] Console: `$store.state.ConfigModule.debug` = `false`
- [ ] Sidebar mostra "Preços validados"
- [ ] Submit chama `/api/order_create.php`
- [ ] Network tab mostra requisições à API

---

## 🐛 Troubleshooting

### "Debug mode não desliga em produção"

```bash
# Verificar NODE_ENV no build
npm run build -- --mode production

# Verificar bundle gerado
cat dist/js/app.*.js | grep "debug.*true"
# Não deve aparecer nenhum resultado
```

### "Quero testar API em desenvolvimento"

```javascript
// Console do navegador:
$store.dispatch('ConfigModule/disableDebug');

// Verificar
$store.state.ConfigModule.debug; // false

// Tentar validação
// Agora tentará chamar API
```

### "Store não está definida"

```javascript
// Verificar se ConfigModule foi importado
import store from '@/core/store/store';

// Verificar se module foi registrado
console.log(store.state.ConfigModule);
// Deve existir
```

---

## 💡 Dicas

1. **Em desenvolvimento:** Deixe debug ON (automático)
2. **Para testar API local:** Use `disableDebug()` no console
3. **Em produção:** Nunca mexa, é automático
4. **Para ver logs:** Abra DevTools → Console

---

**Resumo:**
- 🟢 **Dev:** Debug ON → Sem PHP → Sem stress
- 🔵 **Prod:** Debug OFF → Com PHP → Seguro

Pronto para desenvolver! 🚀
