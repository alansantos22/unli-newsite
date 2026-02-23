# ⚠️ CHECKLIST - DEPLOY EM PRODUÇÃO (MODO TESTE)

## ✅ Configurações Verificadas

### **Backend (PHP) - api/config.secure.php**
- ✅ Access Token: `TEST-5978504079023879-012208-c2b978704b59cec99e0252ecd2e09694-487474845`
- ✅ Public Key: `TEST-fbfd5955-b86c-46e4-a7c1-f9adfb039d4a`
- ✅ Debug Mode: `TRUE` (logs detalhados ativados)

### **Frontend (Vue.js) - .env**
- ✅ Public Key: `TEST-fbfd5955-b86c-46e4-a7c1-f9adfb039d4a`
- ✅ Debug Mode: `TRUE`
- ✅ Ambiente: `development`

---

## 📦 Arquivos para Deploy

### **1. Fazer Build do Frontend:**
```bash
npm run build
```
Isso gera a pasta `/dist` com arquivos estáticos.

### **2. Arquivos Backend (pasta /api):**
```
api/
├── config.secure.php       ✅ (Credenciais de TESTE)
├── db.config.php           ✅ (Configure no servidor)
├── process_payment.php     ✅
├── generate_magic_link.php ✅
├── .htaccess              ✅ (Proteção dos configs)
├── lib/                   ✅
├── onboarding/            ✅
└── emails/                ✅
```

### **3. Não fazer upload:**
```
❌ node_modules/
❌ .env (está no .gitignore)
❌ .git/
❌ src/ (apenas /dist é necessário)
```

---

## 🚀 Passos para Deploy

### **Opção A: FTP/SFTP Manual**

1. **Build do Frontend:**
   ```bash
   npm run build
   ```

2. **Estrutura no servidor:**
   ```
   public_html/
   ├── index.html         (da pasta /dist)
   ├── css/              (da pasta /dist)
   ├── js/               (da pasta /dist)
   ├── img/              (da pasta /dist)
   └── api/              (cópia da pasta /api local)
       ├── config.secure.php
       ├── db.config.php
       ├── process_payment.php
       ├── generate_magic_link.php
       └── ...
   ```

3. **Configurar no servidor:**
   ```bash
   # Conectar via SSH
   ssh usuario@seuservidor.com

   # Ir para pasta da API
   cd public_html/api

   # Configurar permissões
   chmod 600 config.secure.php
   chmod 600 db.config.php
   chmod 755 process_payment.php
   chmod 755 generate_magic_link.php

   # Criar diretórios necessários
   mkdir -p logs uploads temp
   chmod 755 logs uploads temp
   ```

4. **Editar db.config.php no servidor:**
   ```bash
   nano db.config.php
   ```
   Configure as credenciais do banco do servidor:
   ```php
   define('DB_HOST', 'localhost'); // ou IP do servidor MySQL
   define('DB_NAME', 'nome_banco_servidor');
   define('DB_USER', 'usuario_servidor');
   define('DB_PASS', 'senha_servidor');
   ```

---

### **Opção B: Git Deploy**

1. **Fazer commit (apenas arquivos permitidos):**
   ```bash
   git add .
   git commit -m "Deploy: Configuração de teste Pagar.me"
   git push origin main
   ```

2. **No servidor (via SSH):**
   ```bash
   cd /var/www/html
   git pull origin main
   npm install
   npm run build
   
   # Copiar config.secure.php manualmente (NÃO está no Git)
   nano api/config.secure.php
   # Cole o conteúdo do arquivo local
   ```

---

## 🧪 Testar no Servidor

### **1. Testar API PHP:**
```bash
# Via navegador ou curl
https://seudominio.com/api/process_payment.php
```

**Resposta esperada (erro 405):**
```json
{
  "success": false,
  "message": "Método não permitido. Use POST"
}
```
✅ Se viu isso, a API está funcionando!

### **2. Testar Proteção de Config:**
```bash
https://seudominio.com/api/config.secure.php
```

**Resposta esperada:**
```
403 Forbidden
```
✅ Perfeito! O arquivo está protegido pelo .htaccess.

### **3. Testar Frontend:**
```bash
https://seudominio.com
```
Deve carregar a página Vue.js normalmente.

---

## 💳 Testar Pagamento (Modo TESTE)

### **Cartões de Teste:**

✅ **APROVADO (Mastercard):**
- Número: `5031 4332 1540 6351`
- CVV: `123`
- Validade: `12/28`
- CPF: `123.456.789-09`

✅ **APROVADO (Visa):**
- Número: `4509 9535 6623 3704`
- CVV: `123`
- Validade: `12/28`

❌ **RECUSADO:**
- Número: `5031 7557 3453 0604`

### **Importante:**
- ⚠️ Esses cartões **NÃO COBRAM DE VERDADE**
- ⚠️ São apenas para testes
- ⚠️ Não aparecem no painel do Pagar.me (ambiente TESTE separado)

---

## 🔍 Debug no Servidor

### **Ver logs PHP:**
```bash
# SSH no servidor
tail -f api/logs/security.log
tail -f api/logs/php_errors.log
```

### **Ver logs do Apache/Nginx:**
```bash
tail -f /var/log/apache2/error.log
# ou
tail -f /var/log/nginx/error.log
```

### **Console do Navegador:**
Abra F12 e veja os logs coloridos:
- 🔍 Dados enviados
- 📥 Resposta da API
- ⚠️ Erros detalhados

---

## 📊 Verificar Transações de Teste

Acesse o painel do Pagar.me (ambiente de teste):
```
https://dash.pagar.me/
```

Lá você verá todas as transações de teste realizadas.

---

## ⚠️ Problemas Comuns

### **Erro: CORS**
Edite `api/process_payment.php` e adicione seu domínio:
```php
header('Access-Control-Allow-Origin: https://seudominio.com');
```

### **Erro: Config não encontrado**
Verifique se `config.secure.php` foi copiado para o servidor:
```bash
ls -la api/config.secure.php
```

### **Erro: Permissão negada**
Ajuste permissões:
```bash
chmod 600 api/config.secure.php
chmod 755 api/process_payment.php
```

---

## 🔄 Quando Passar para PRODUÇÃO REAL

Edite `api/config.secure.php` e `.env`:

```php
// Backend
define('PAGARME_API_KEY', 'sk_live_...');
define('PAGARME_PUBLIC_KEY', 'pk_live_...');
define('DEBUG_MODE', false); // Desligar debug
```

```env
# Frontend (.env)
VUE_APP_PAGARME_PUBLIC_KEY=pk_live_...
VUE_APP_DEBUG_MODE=false
NODE_ENV=production
```

Depois faça novo build:
```bash
npm run build
```

---

## ✅ Checklist Final

- [ ] Build do frontend gerado (`npm run build`)
- [ ] Arquivos enviados para servidor
- [ ] `config.secure.php` configurado no servidor
- [ ] `db.config.php` configurado no servidor
- [ ] Permissões corretas (600 para configs, 755 para APIs)
- [ ] `.htaccess` presente na pasta `/api`
- [ ] Teste de acesso à API funcionando
- [ ] Teste de proteção de config funcionando
- [ ] Pagamento de teste realizado com sucesso
- [ ] Logs sendo gerados corretamente

---

🎉 **Tudo pronto para testar em produção com segurança (modo TESTE)!**
