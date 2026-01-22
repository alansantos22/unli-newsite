# 🔐 Variáveis de Ambiente - Guia Completo

## 📋 O que são Variáveis de Ambiente?

Variáveis de ambiente são valores configuráveis que **NÃO ficam hardcoded** no código. Elas permitem:

✅ Separar credenciais do código-fonte  
✅ Diferentes configurações por ambiente (dev/prod)  
✅ Segurança (não sobem pro Git)  
✅ Facilidade de manutenção  

---

## 📁 Arquivos de Ambiente

### **.env** (Local - NÃO commitado)
Suas credenciais reais. Este arquivo está no `.gitignore` e **NUNCA** vai pro Git.

### **.env.example** (Template - Commitado)
Template público que mostra quais variáveis são necessárias, mas **sem valores reais**.

---

## 🚀 Configuração Inicial

### **Passo 1: Criar arquivo .env**

```bash
# Copiar o template
cp .env.example .env
```

### **Passo 2: Editar com suas credenciais**

Abra o arquivo `.env` e configure:

```env
# TESTE (Desenvolvimento)
VUE_APP_MERCADOPAGO_PUBLIC_KEY=TEST-fbfd5955-b86c-46e4-a7c1-f9adfb039d4a
VUE_APP_DEBUG_MODE=true
NODE_ENV=development
```

---

## 🔑 Variáveis Disponíveis

### **Frontend (Vue.js)**

| Variável | Descrição | Exemplo |
|----------|-----------|---------|
| `VUE_APP_MERCADOPAGO_PUBLIC_KEY` | Public Key do Mercado Pago | `TEST-abc123...` |
| `VUE_APP_DEBUG_MODE` | Ativar logs detalhados | `true` / `false` |
| `VUE_APP_API_BASE_URL` | URL base da API (opcional) | `/api` |
| `NODE_ENV` | Ambiente de execução | `development` / `production` |

⚠️ **IMPORTANTE:** No Vue.js, variáveis expostas ao cliente **DEVEM** começar com `VUE_APP_`

### **Backend (PHP)**

As credenciais do backend ficam em: `api/config.secure.php`

```php
define('MP_ACCESS_TOKEN', 'TEST-...');  // Nunca expor no frontend!
define('MP_PUBLIC_KEY', 'TEST-...');    // OK expor (vai pro .env)
```

---

## 🛠️ Como Usar no Código

### **No Vue.js (Frontend)**

```javascript
// Acessar variável de ambiente
const publicKey = process.env.VUE_APP_MERCADOPAGO_PUBLIC_KEY
const debug = process.env.VUE_APP_DEBUG_MODE === 'true'

// Validação
if (!publicKey) {
  console.error('❌ Public Key não configurada!')
}
```

### **No PHP (Backend)**

```php
// Carregar do arquivo config.secure.php
define('SECURE_CONFIG_ACCESS', true);
require_once 'config.secure.php';

// Usar as constantes
$token = MP_ACCESS_TOKEN;
```

---

## 🔄 Diferentes Ambientes

### **Desenvolvimento Local**

```env
# .env (local)
VUE_APP_MERCADOPAGO_PUBLIC_KEY=TEST-fbfd5955-b86c-46e4-a7c1-f9adfb039d4a
VUE_APP_DEBUG_MODE=true
NODE_ENV=development
```

### **Produção**

```env
# .env (servidor)
VUE_APP_MERCADOPAGO_PUBLIC_KEY=APP_USR-47d5fd7b-7d0d-4e1e-8408-84548c817127
VUE_APP_DEBUG_MODE=false
NODE_ENV=production
```

---

## 🚨 Segurança

### ✅ **FAZER:**

- Usar `.env` para todas as credenciais
- Adicionar `.env` no `.gitignore`
- Commitar `.env.example` (sem valores reais)
- Documentar todas as variáveis necessárias
- Usar prefixo `VUE_APP_` para variáveis do Vue

### ❌ **NUNCA FAZER:**

- Hardcodar tokens no código
- Commitar arquivo `.env` no Git
- Expor `ACCESS_TOKEN` no frontend (só `PUBLIC_KEY`)
- Usar mesmas credenciais em dev/prod
- Colocar valores reais no `.env.example`

---

## 🔍 Verificar Configuração

### **Verificar se .env existe**

```bash
ls -la .env
# Deve mostrar o arquivo
```

### **Verificar se está no .gitignore**

```bash
git status
# .env NÃO deve aparecer na lista
```

### **Testar no código**

```javascript
// No console do navegador (F12)
console.log('Public Key:', process.env.VUE_APP_MERCADOPAGO_PUBLIC_KEY)
// Deve mostrar: TEST-fbfd5955-b86c-46e4-a7c1-f9adfb039d4a
```

---

## 🐛 Troubleshooting

### **Erro: "process.env.VUE_APP_... is undefined"**

**Causas:**
1. Arquivo `.env` não existe
2. Variável não começa com `VUE_APP_`
3. Servidor não foi reiniciado após criar `.env`

**Solução:**
```bash
# 1. Verificar se .env existe
ls -la .env

# 2. Se não existir, criar
cp .env.example .env

# 3. Reiniciar servidor Vue
npm run serve
```

### **Variável não atualiza**

O Vue.js lê as variáveis apenas no **startup**. Sempre que alterar `.env`:

```bash
# Pare o servidor (Ctrl+C) e reinicie
npm run serve
```

### **Variável aparece como string**

```javascript
// ❌ ERRADO
const debug = process.env.VUE_APP_DEBUG_MODE  // retorna string "true"

// ✅ CORRETO
const debug = process.env.VUE_APP_DEBUG_MODE === 'true'  // retorna boolean
```

---

## 📦 Deploy em Produção

### **Opção 1: Variáveis no Servidor**

```bash
# SSH no servidor
ssh usuario@servidor

# Criar .env na pasta da aplicação
cd /var/www/html
nano .env
# Cole as credenciais de PRODUÇÃO
```

### **Opção 2: Variáveis de Ambiente do Sistema**

Em alguns hostings, você configura via painel:

- **Vercel/Netlify:** Settings > Environment Variables
- **Heroku:** Config Vars
- **cPanel:** PHP Variables ou .htaccess

---

## 📚 Estrutura Completa

```
unli-newsite/
├── .env                    ← Suas credenciais (NÃO commitado)
├── .env.example            ← Template (commitado)
├── .gitignore              ← Bloqueia .env
├── package.json
├── vue.config.js
│
├── api/
│   ├── config.secure.php   ← Backend credentials
│   ├── config.example.php  ← Backend template
│   └── .htaccess           ← Proteção Apache
│
└── src/
    └── shared/
        └── Components/
            └── CheckoutPayment.vue  ← Usa process.env
```

---

## ✅ Checklist

Antes de fazer commit/deploy:

- [ ] Arquivo `.env` criado e configurado
- [ ] `.env` está no `.gitignore`
- [ ] `.env.example` atualizado (sem valores reais)
- [ ] Código usa `process.env.VUE_APP_*` (não hardcode)
- [ ] Testado localmente com credenciais de teste
- [ ] `git status` não mostra `.env`
- [ ] Documentação atualizada

---

## 🔗 Referências

- **Vue.js Environment Variables:** https://cli.vuejs.org/guide/mode-and-env.html
- **Mercado Pago Credentials:** https://www.mercadopago.com.br/developers/panel/credentials
- **Segurança Best Practices:** https://owasp.org/www-project-top-ten/

---

✅ **Agora suas credenciais estão seguras e bem organizadas!** 🎉
