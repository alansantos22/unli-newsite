# 🎫 Integração Fila Chamados - Guia Rápido

## ⚡ Configuração Rápida

### 1. Configure suas credenciais

Edite `api/config.secure.php`:

```php
// Habilitar integração
define('FILA_CHAMADOS_ENABLED', true);

// Sua API Key (obtenha em: Integração/API no Fila Chamados)
define('FILA_CHAMADOS_API_KEY', 'sua_api_key_aqui');

// URL da sua API do Fila Chamados
define('FILA_CHAMADOS_API_URL', 'https://seudominio.com/external_api.php');

// (Opcional) ID da categoria
define('FILA_CHAMADOS_CATEGORY_ID', null);
```

### 2. Teste a integração

```bash
curl http://localhost/api/test-fila-chamados.php
```

### 3. Pronto! 🎉

A cada venda aprovada, um ticket será criado automaticamente no Fila Chamados.

---

## 📚 Documentação Completa

Veja: [`docs/FILA-CHAMADOS-INTEGRATION.md`](../docs/FILA-CHAMADOS-INTEGRATION.md)

---

## 🗂️ Arquivos da Integração

- **Configuração:** `api/config.secure.php`
- **Helper:** `api/lib/fila-chamados.php`
- **Teste:** `api/test-fila-chamados.php`
- **Integração Cartão:** `api/process_payment.php`
- **Integração Webhook:** `api/webhook-payment.php`

---

## 🔧 Como Funciona

```
Cliente compra → Pagamento aprovado → 🎫 Ticket criado automaticamente
```

O ticket contém:
- ✅ Dados do cliente
- ✅ Detalhes do pedido
- ✅ Briefing completo
- ✅ Próximos passos

---

## 🐛 Problemas?

1. Verifique os logs: `tail -f /var/log/php_errors.log`
2. Procure por: `FILA CHAMADOS:`
3. Execute o teste: `api/test-fila-chamados.php`

---

**📖 Documentação API Fila Chamados:** Veja anexo `EXTERNAL_API_DOCUMENTATION.md`
