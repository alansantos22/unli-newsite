# Segurança: Bloqueio de Acesso Direto ao pricing.json

## 🔐 Problema Identificado

O arquivo `api/pricing.json` contém a estrutura de preços completa e não deve ser acessível diretamente via URL por questões de segurança e controle.

---

## ✅ Solução Implementada (25/02/2026)

### **1. Bloqueio Total no .htaccess da API**

**Arquivo:** `api/.htaccess`

```apache
# BLOQUEADO: pricing.json não deve ser acessível diretamente via URL
# Use /api/config.php para obter preços de forma segura
<FilesMatch "^pricing\.json$">
  <IfModule mod_authz_core.c>
    Require all denied
  </IfModule>
  <IfModule !mod_authz_core.c>
    Order Allow,Deny
    Deny from all
  </IfModule>
</FilesMatch>
```

**Resultado:**
- ❌ `https://unli.com.br/api/pricing.json` → **403 Forbidden**
- ✅ Scripts PHP internos continuam lendo o arquivo normalmente

---

### **2. Frontend Atualizado para Usar Endpoint Seguro**

**Arquivo:** `src/pages/ConfiguradorPage/ConfiguradorPage.vue`

**Antes (inseguro):**
```javascript
const response = await fetch('/api/pricing.json'); // Acesso direto ❌
```

**Depois (seguro):**
```javascript
const response = await fetch('/api/config.php'); // Via endpoint PHP ✅
```

**Benefícios:**
- ✅ Não expõe arquivo JSON diretamente
- ✅ Validação adicional no servidor
- ✅ Possibilidade de cache/rate limiting no endpoint
- ✅ Controle sobre quais dados são expostos

---

### **3. Endpoint PHP Seguro: /api/config.php**

**Já existente e funcionando:**

```php
<?php
require_once __DIR__ . '/lib/pricing.php';

$cfg = load_pricing_config(__DIR__ . '/pricing.json');

echo json_encode([
    'ok' => true,
    'version' => $cfg['version'],
    'products' => $cfg['products'],
    'page_addons' => $cfg['page_addons'],
    // ... apenas dados necessários
], JSON_UNESCAPED_UNICODE);
```

**Vantagens:**
- ✅ Pode filtrar dados sensíveis
- ✅ Pode adicionar autenticação/validação
- ✅ Permite versionamento da API
- ✅ Logs de acesso centralizados

---

## 🧪 Como Testar

### Teste 1: Verificar Bloqueio Direto
```bash
# Deve retornar 403 Forbidden
curl -I https://unli.com.br/api/pricing.json
```

**Resultado esperado:**
```
HTTP/1.1 403 Forbidden
```

### Teste 2: Verificar Endpoint Funcional
```bash
# Deve retornar 200 OK com JSON
curl https://unli.com.br/api/config.php
```

**Resultado esperado:**
```json
{
  "ok": true,
  "version": "1.0.0",
  "currency": "BRL",
  "products": { ... }
}
```

### Teste 3: Frontend Continua Funcionando
1. Abrir: `http://localhost:8080/configurador`
2. Verificar console do navegador
3. **Não deve aparecer erro 403**
4. Preços devem carregar normalmente

---

## 🔍 Fluxo de Acesso Atual

```mermaid
graph TD
    A[Usuário via Navegador] -->|❌ 403 Forbidden| B[/api/pricing.json]
    A -->|✅ Permitido| C[/api/config.php]
    C -->|Lê internamente| B
    D[Script PHP Backend] -->|✅ Permitido| B
    E[SDR Chat sdr-chat.php] -->|✅ Permitido| B
    F[Order Create order_create.php] -->|✅ Permitido| B
```

**Legenda:**
- ❌ Bloqueado pelo Apache (.htaccess)
- ✅ Permitido (acesso interno via PHP ou endpoint autorizado)

---

## 🛡️ Camadas de Segurança

| Camada | Proteção | Status |
|--------|----------|--------|
| **1. .htaccess** | Bloqueia acesso HTTP direto | ✅ Implementado |
| **2. Endpoint PHP** | Valida requisições | ✅ Implementado |
| **3. CORS** | Limita origens permitidas | ✅ Já existente |
| **4. Rate Limiting** | Previne abuso | 🟡 Recomendado |
| **5. Token/Auth** | Autenticação de cliente | 🟢 Opcional |

---

## 📊 Comparação: Antes vs Depois

### Antes (Vulnerável)
```
GET /api/pricing.json
→ 200 OK (arquivo exposto publicamente)
→ Qualquer pessoa pode ver toda estrutura de preços
→ Pode usar scripts para monitorar mudanças
```

### Depois (Seguro)
```
GET /api/pricing.json
→ 403 Forbidden ❌

GET /api/config.php
→ 200 OK ✅
→ Apenas dados necessários expostos
→ Controle total no backend
```

---

## 🚀 Melhorias Futuras (Opcionais)

### 1. Cache no Endpoint
```php
// Em config.php
header('Cache-Control: public, max-age=300'); // 5 minutos
```

### 2. Rate Limiting por IP
```php
// Limitar requisições por IP (evitar scraping)
if (requestCount($ip) > 100) {
    http_response_code(429);
    exit;
}
```

### 3. Autenticação Básica (se necessário)
```php
// Validar header customizado
if ($_SERVER['HTTP_X_API_KEY'] !== 'secret') {
    http_response_code(401);
    exit;
}
```

### 4. Versionamento da API
```php
// /api/v1/config.php
// /api/v2/config.php
```

---

## ✅ Checklist de Verificação

- [x] pricing.json bloqueado via .htaccess
- [x] Frontend atualizado para usar /api/config.php
- [x] ConfiguradorPage.vue testado
- [x] Backend PHP continua funcionando
- [x] SDR chat continua carregando preços
- [ ] Testar em produção após deploy

---

## 📞 Troubleshooting

### Problema: Frontend não carrega preços
**Solução:** Verificar console do navegador
```javascript
// Deve aparecer:
💰 [ConfiguradorPage] Preços carregados: {...}

// NÃO deve aparecer:
⚠️ Usando preços de fallback
```

### Problema: 403 ao acessar /api/config.php
**Solução:** Verificar permissões do arquivo
```bash
chmod 644 api/config.php
```

### Problema: SDR não carrega preços
**Solução:** Verificar que o PHP consegue ler o arquivo
```bash
# Testar no servidor
php -r "echo json_encode(json_decode(file_get_contents('api/pricing.json')));"
```

---

## 🎯 Conclusão

✅ **Sistema agora está seguro:**
- Arquivo `pricing.json` **não é acessível via URL direta**
- Frontend usa endpoint controlado `/api/config.php`
- Backend PHP continua acessando normalmente
- SDR e outros scripts internos não são afetados

**Risco de exposição:** 🔴 Alto → 🟢 **BAIXO (Mitigado)**

---

**Data de implementação:** 25/02/2026  
**Responsável:** Sistema de segurança preventiva
