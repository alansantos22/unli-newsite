# 🔒 Configuração de CORS - Cross-Origin Resource Sharing

## ✅ Domínios Permitidos

A API agora aceita requisições **apenas** dos seguintes domínios:

- ✅ `https://unli.com.br`
- ✅ `https://www.unli.com.br`

## 🛡️ Segurança Implementada

### Antes (INSEGURO)
```php
header('Access-Control-Allow-Origin: *'); // ❌ Qualquer site podia acessar
```

### Agora (SEGURO)
```php
// Apenas domínios na whitelist
require_once __DIR__ . '/lib/cors.php';
```

## 📋 Arquivos Atualizados

Todos os endpoints da API agora usam CORS seguro:

### ✅ Principais
- `api/process_payment.php`
- `api/order_create.php`
- `api/payment_create.php`
- `api/price.php`
- `api/config.php`

### ✅ Onboarding
- `api/onboarding/validate.php`
- `api/onboarding/submit.php`
- `api/onboarding/save-draft.php`

## 🔧 Configuração Central

**Arquivo:** `api/lib/cors.php`

```php
// Domínios permitidos
$allowedOrigins = [
    'https://unli.com.br',
    'https://www.unli.com.br'
];
```

## 🧪 Modo de Desenvolvimento

Em modo DEBUG, localhost também é permitido:

```php
// Em config.secure.php ou similar
define('DEBUG_MODE', true); // Apenas em desenvolvimento!
```

Isso adiciona automaticamente:
- `http://localhost:8080`
- `http://localhost:3000`
- `http://127.0.0.1:8080`

⚠️ **IMPORTANTE:** Em produção, certifique-se que `DEBUG_MODE` está `false`!

## 🌐 Headers CORS Enviados

```http
Access-Control-Allow-Origin: https://unli.com.br
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With
Access-Control-Allow-Credentials: true
Access-Control-Max-Age: 86400
```

## ✅ Requisições Preflight (OPTIONS)

O sistema responde automaticamente a requisições OPTIONS do browser:

```
OPTIONS /api/order_create.php
→ 200 OK (sem body)
```

## 🧪 Testando CORS

### ✅ Teste 1: Do domínio permitido

```javascript
// Em https://unli.com.br
fetch('https://seu-servidor.com/api/price.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ product: 'vitrine' })
})
// ✅ Funciona!
```

### ❌ Teste 2: De outro domínio

```javascript
// Em https://outro-site.com
fetch('https://seu-servidor.com/api/price.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ product: 'vitrine' })
})
// ❌ CORS Error: Not allowed
```

## 📝 Adicionando Novos Domínios

Se precisar adicionar mais domínios no futuro:

1. Edite `api/lib/cors.php`
2. Adicione na array `$allowedOrigins`:

```php
$allowedOrigins = [
    'https://unli.com.br',
    'https://www.unli.com.br',
    'https://novo-dominio.com' // Novo domínio
];
```

3. Salve e pronto!

## 🚨 Erros Comuns

### Erro: "CORS policy: No 'Access-Control-Allow-Origin'"

**Causa:** Tentativa de acesso de domínio não autorizado

**Solução:** 
- Verifique se está acessando de `https://unli.com.br` ou `https://www.unli.com.br`
- Em desenvolvimento, ative `DEBUG_MODE`

### Erro: "Preflight request doesn't pass"

**Causa:** OPTIONS request bloqueado

**Solução:** 
- Verifique se `cors.php` está sendo incluído
- Certifique-se que headers estão sendo enviados antes de qualquer output

## 🔐 Boas Práticas

✅ **Faça:**
- Mantenha lista de domínios curta e específica
- Use HTTPS em produção
- Desative DEBUG_MODE em produção
- Monitore logs de tentativas de acesso

❌ **Não Faça:**
- Usar `*` em produção
- Adicionar domínios HTTP em produção
- Deixar DEBUG_MODE ativo em produção
- Expor APIs sem autenticação

## 📊 Impacto na Performance

- ✅ Cache de preflight: 24 horas (`Access-Control-Max-Age: 86400`)
- ✅ Mínimo overhead: verificação simples de array
- ✅ Sem consultas ao banco de dados

## 🎯 Benefícios de Segurança

1. **Previne CSRF** - Cross-Site Request Forgery
2. **Controla acesso** - Apenas sites autorizados
3. **Reduz abuso** - Dificulta scraping/bots
4. **Compliance** - Segue boas práticas web

---

**Data de implementação:** 22 de Janeiro de 2026  
**Versão:** 1.0
