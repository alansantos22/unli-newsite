# 🔒 Sistema de Configuração Segura - Pagar.me

## 📋 Arquivos Criados

### 1. **api/config.secure.php** (Arquivo Principal - NÃO COMMITADO)
Contém suas credenciais reais do Pagar.me. Este arquivo é **BLOQUEADO** pelo .gitignore.

### 2. **api/config.example.php** (Template Público)
Template que pode ser commitado no Git. Desenvolvedores copiam este arquivo para criar o `config.secure.php`.

### 3. **api/.htaccess** (Proteção Apache)
Bloqueia acesso direto via HTTP aos arquivos de configuração.

---

## 🚀 Instalação Rápida (5 minutos)

### **Passo 1: Copiar o Template**
```bash
cd api
cp config.example.php config.secure.php
```

### **Passo 2: Configurar Permissões Seguras**
```bash
chmod 600 config.secure.php
```
> ⚠️ Permissão 600 = Apenas o dono do arquivo pode ler/escrever

### **Passo 3: Obter Credenciais do Pagar.me**
1. Acesse: https://dash.pagar.me/
2. Copie sua **API Key** (sk_...)
3. Copie sua **Public Key** (pk_...)

### **Passo 4: Editar config.secure.php**
Abra `api/config.secure.php` e substitua:

```php
// 🔑 SUAS CREDENCIAIS REAIS
define('PAGARME_API_KEY', 'sk_live_...'); // Cole sua API Key
define('PAGARME_PUBLIC_KEY', 'pk_live_...'); // Cole sua Public Key
```

### **Passo 5: Verificar Segurança**
```bash
# Testar se o arquivo está bloqueado
curl https://seusite.com/api/config.secure.php
# Deve retornar: 403 Forbidden ✅
```

---

## 🛡️ Camadas de Segurança Implementadas

### ✅ **Camada 1: .htaccess**
```apache
# Bloqueia acesso direto via HTTP
<FilesMatch "^(config\.secure\.php)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```

### ✅ **Camada 2: Verificação PHP**
```php
// Impede execução direta do arquivo
if (!defined('SECURE_CONFIG_ACCESS')) {
    die('🚫 Acesso negado!');
}
```

### ✅ **Camada 3: .gitignore**
```gitignore
# NUNCA fazer commit deste arquivo
api/config.secure.php
api/logs/
*.secure.php
```

### ✅ **Camada 4: Permissões Linux**
```bash
chmod 600 config.secure.php  # rw------- (apenas dono)
```

### ✅ **Camada 5: Validação de Credenciais**
O sistema verifica se você configurou tokens reais (não aceita placeholders).

### ✅ **Camada 6: Forçar HTTPS em Produção**
```php
// Bloqueia conexões HTTP em produção
if (isProduction() && !isSecureConnection()) {
    die('🚫 Use HTTPS!');
}
```

---

## 🔍 Como Funciona?

```
┌──────────────────────────────────────────────┐
│  process_payment.php (API Pública)          │
└──────────────────┬───────────────────────────┘
                   │
                   │ require_once
                   ▼
┌──────────────────────────────────────────────┐
│  config.secure.php (Protegido)              │
│  - Credenciais do Pagar.me                  │
│  - Configurações sensíveis                   │
│  - Validações de segurança                   │
└──────────────────────────────────────────────┘
         ▲
         │ BLOQUEADO por:
         │ • .htaccess
         │ • .gitignore
         │ • chmod 600
         └─ 🛡️ NENHUM HACKER CONSEGUE ACESSAR
```

---

## 🧪 Testar a Segurança

### **Teste 1: Bloquear Acesso HTTP**
Tente acessar: `https://seusite.com/api/config.secure.php`

**Resultado esperado:** `403 Forbidden` ✅

### **Teste 2: Verificar .gitignore**
```bash
git status
# config.secure.php NÃO deve aparecer na lista
```

### **Teste 3: Testar Permissões**
```bash
ls -la api/config.secure.php
# Deve mostrar: -rw------- (600)
```

### **Teste 4: Validar Credenciais**
Acesse a API: `https://seusite.com/api/process_payment.php`

Se credenciais não configuradas, retorna:
```json
{
  "success": false,
  "message": "⚠️ ERRO: Credenciais não configuradas"
}
```

---

## 📝 Modo de Desenvolvimento vs Produção

### **Desenvolvimento (DEBUG_MODE = true)**
```php
define('DEBUG_MODE', true);
define('PAGARME_API_KEY', 'sk_test_...');  // Credenciais de teste
```
- Logs detalhados no console
- Permite HTTP (não força HTTPS)
- Erros verbosos

### **Produção (DEBUG_MODE = false)**
```php
define('DEBUG_MODE', false);
define('PAGARME_API_KEY', 'sk_live_...');  // Credenciais reais
```
- Sem logs sensíveis
- **Força HTTPS** obrigatoriamente
- Erros genéricos (não vaza informações)

---

## 🔄 Deploy em Servidor

### **Opção A: Enviar arquivo manualmente**
1. Configure `config.secure.php` localmente
2. Envie via SFTP/FTP (não use Git!)
3. Configure permissões no servidor:
```bash
chmod 600 api/config.secure.php
```

### **Opção B: Criar direto no servidor**
```bash
# SSH no servidor
ssh usuario@servidor.com

# Copiar template
cd /var/www/html/api
cp config.example.php config.secure.php

# Editar com nano/vim
nano config.secure.php

# Configurar permissões
chmod 600 config.secure.php
```

---

## ⚠️ Checklist de Segurança

Antes de colocar em produção, verifique:

- [ ] `config.secure.php` existe e está configurado
- [ ] Permissões do arquivo são 600 (`chmod 600`)
- [ ] `.htaccess` está na pasta `/api`
- [ ] `.gitignore` inclui `api/config.secure.php`
- [ ] Teste de acesso HTTP retorna 403 Forbidden
- [ ] `DEBUG_MODE = false` em produção
- [ ] Credenciais são de produção (APP-..., não TEST-...)
- [ ] Site usa HTTPS (SSL configurado)
- [ ] `git status` não mostra config.secure.php

---

## 🆘 Troubleshooting

### **Erro: "config.secure.php não encontrado"**
```bash
# Solução:
cd api
cp config.example.php config.secure.php
nano config.secure.php  # Edite as credenciais
```

### **Erro: "Credenciais não configuradas"**
Você precisa substituir os placeholders pelos tokens reais do Pagar.me.

### **Erro: "Use HTTPS em produção"**
Configure SSL no seu servidor ou desative temporariamente:
```php
// Em config.secure.php (apenas para teste local)
define('DEBUG_MODE', true);
```

### **Erro 500 ao acessar API**
Verifique permissões do arquivo:
```bash
chmod 644 api/process_payment.php
chmod 600 api/config.secure.php
```

---

## 📚 Variáveis Disponíveis

Após carregar `config.secure.php`, você tem acesso a:

| Constante | Descrição | Exemplo |
|-----------|-----------|---------|
| `PAGARME_API_KEY` | Chave de API (backend) | `sk_live_...` |
| `PAGARME_PUBLIC_KEY` | Chave pública (frontend) | `pk_live_...` |
| `DEBUG_MODE` | Modo debug (true/false) | `true` |
| `PAGARME_API_URL` | URL da API | `https://api.pagar.me/core/v5` |
| `PAGARME_TIMEOUT` | Timeout de requisição (s) | `30` |
| `SSL_VERIFY_PEER` | Verificar SSL (true/false) | `true` |

---

## 🎓 Boas Práticas

### ✅ **FAZER:**
- Usar credenciais de teste (`sk_test_...`) em desenvolvimento
- Usar credenciais de produção (`sk_live_...`) apenas no servidor
- Fazer backup manual do `config.secure.php` em local seguro (não Git!)
- Rotacionar credenciais periodicamente

### ❌ **NUNCA FAZER:**
- Fazer commit do `config.secure.php`
- Deixar `DEBUG_MODE = true` em produção
- Usar mesmas credenciais em dev/prod
- Compartilhar tokens por e-mail/chat

---

## 🔗 Links Úteis

- **Credenciais Pagar.me:** https://dash.pagar.me/
- **Documentação API:** https://docs.pagar.me/
- **Gerar String Aleatória:** `openssl rand -base64 32`

---

## 📞 Suporte

Se encontrar problemas, verifique:
1. Logs do servidor (`/var/log/apache2/error.log` ou `/var/log/nginx/error.log`)
2. Logs da aplicação (`api/logs/security.log` se DEBUG_MODE ativado)
3. Console do navegador (F12) para erros de frontend

---

✅ **Sistema configurado com segurança militar!** Nenhum hacker terá acesso às suas credenciais. 🛡️
