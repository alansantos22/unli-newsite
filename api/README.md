# API Server-Authoritative - Site Vitrine

## 🔐 Arquitetura de Segurança

**Regra de Ouro:** O servidor é a fonte de verdade. O frontend NUNCA envia preços, apenas escolhas.

### Princípios

1. ✅ Frontend envia apenas **seleções** (produto, addons, quantidades)
2. ✅ PHP **calcula e valida** tudo
3. ✅ Checkout usa **valores do servidor**
4. ✅ **Whitelist rígida** de IDs e limites
5. ✅ **Recálculo** em toda operação crítica

---

## 📁 Estrutura de Arquivos

```
/api
  ├── config.php              # GET - Catálogo de produtos
  ├── price.php               # POST - Calcular preço
  ├── order_create.php        # POST - Criar pedido
  ├── create_preference.php   # POST - Criar pagamento Mercado Pago
  ├── validate_payment.php    # POST - Validar pagamento após sucesso
  ├── pricing.json            # Configuração oficial (source of truth)
  ├── lib/
  │   ├── pricing.php         # Core de cálculo
  │   ├── database.php        # Conexão banco de dados
  │   ├── cors.php            # CORS headers
  │   └── storage.php         # Persistência
  └── orders/                 # Pedidos salvos (JSON)
      └── ORD-*.json
```

---

## 🌐 Endpoints

### 1. GET `/api/config.php`

**Descrição:** Retorna catálogo para renderizar UI

**Response:**
```json
{
  "ok": true,
  "products": { ... },
  "page_addons": { ... },
  "content_addons": { ... },
  "pricing_rules": { ... },
  "limits": { ... }
}
```

**Uso no Frontend:**
```javascript
fetch('/api/config.php')
  .then(r => r.json())
  .then(config => {
    // Renderizar UI com catálogo
  });
```

---

### 2. POST `/api/price.php`

**Descrição:** Calcula preço oficial (source of truth)

**Request:**
```json
{
  "product": "landing",
  "pages": {
    "about": 1,
    "services": 0
  },
  "content": {
    "video": true,
    "pdf": false
  }
}
```

**Response:**
```json
{
  "ok": true,
  "normalized": {
    "product": "landing",
    "pages": { "about": 0, ... },
    "content": { "video": true, "pdf": false }
  },
  "pricing": {
    "subtotal": 678.00,
    "avista": 610.20,
    "parcelado_total": 745.80,
    "parcela_12": 62.15,
    "installments": 12,
    "breakdown": { ... }
  }
}
```

**Proteções:**
- ✅ Normaliza seleções inválidas
- ✅ Aplica limites (max páginas)
- ✅ Bloqueia páginas extras em Landing Page
- ✅ Valida tipos de produtos/addons

**Uso no Frontend:**
```javascript
// A cada clique/alteração
fetch('/api/price.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify(selections)
})
.then(r => r.json())
.then(data => {
  // Aplicar normalized e pricing retornados
  this.selectedProduct = data.normalized.product;
  this.selectedPages = data.normalized.pages;
  this.pricing = data.pricing;
});
```

---

### 3. POST `/api/order_create.php`

**Descrição:** Cria pedido e trava valores

**Request:**
```json
{
  "product": "site_complete",
  "pages": { "about": 1 },
  "content": { "video": true },
  "briefing": {
    "company_name": "UNLI Studio",
    "whatsapp": "11999999999",
    "email": "contato@unli.com",
    "style": "modern",
    "main_content": "...",
    "page_contents": { "about": "..." }
  },
  "payment_method": "12x"
}
```

**Response:**
```json
{
  "ok": true,
  "order_id": "ORD-20260121-A3F7B9C1",
  "status": "pending",
  "pricing": { ... },
  "next_step": "payment_create"
}
```

**Proteções:**
- ✅ **Recalcula preço** do zero (não confia no cliente)
- ✅ Valida briefing (campos obrigatórios)
- ✅ Sanitiza inputs
- ✅ Gera order_id único
- ✅ Salva pedido com timestamp

---

## 🔧 Integração com Mercado Pago

O pagamento é feito via **Checkout Pro** (redirect):

1. Frontend chama `POST /api/create_preference.php` com order_id
2. PHP cria preferência no Mercado Pago com valor do servidor
3. Cliente é redirecionado para Mercado Pago
4. Após pagamento, cliente volta para `/pagamento-sucesso`
5. Frontend chama `POST /api/validate_payment.php` para validar
6. PHP atualiza status do pedido no banco

---

## 🗄️ Banco de Dados (Migração)

**Atual:** JSON files em `/api/orders/`
**Produção:** MySQL

### Schema Sugerido

```sql
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id VARCHAR(50) UNIQUE NOT NULL,
  status ENUM('pending', 'awaiting_payment', 'paid', 'cancelled') DEFAULT 'pending',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  selection JSON NOT NULL,
  pricing JSON NOT NULL,
  briefing JSON NOT NULL,
  payment_method VARCHAR(20),
  payment_id VARCHAR(100),
  client_ip VARCHAR(45),
  INDEX idx_order_id (order_id),
  INDEX idx_status (status),
  INDEX idx_created (created_at DESC)
);
```

---

## 🧪 Testes

### Testar Cálculo de Preço

```bash
curl -X POST http://localhost/api/price.php \
  -H "Content-Type: application/json" \
  -d '{
    "product": "site_complete",
    "pages": {"about": 1, "services": 1},
    "content": {"video": true}
  }'
```

### Testar Criação de Pedido

```bash
curl -X POST http://localhost/api/order_create.php \
  -H "Content-Type: application/json" \
  -d '{
    "product": "landing",
    "pages": {},
    "content": {},
    "briefing": {
      "company_name": "Test Company",
      "whatsapp": "11999999999",
      "email": "test@test.com",
      "style": "modern",
      "main_content": "Lorem ipsum"
    },
    "payment_method": "avista"
  }'
```

---

## 🚀 Deploy

### Apache/Nginx

1. Copiar pasta `/api` para document root
2. Configurar CORS se frontend estiver em domínio diferente
3. Dar permissão de escrita em `/api/orders/`:
   ```bash
   chmod 755 /api/orders
   ```

### Variáveis de Ambiente

Criar `.env` ou configurar:
- `GATEWAY_API_KEY` - Token do gateway
- `DATABASE_URL` - Conexão MySQL (produção)
- `WEBHOOK_SECRET` - Validar webhooks do gateway

---

## 📊 Próximos Passos

- [ ] Integrar gateway de pagamento real
- [ ] Migrar de JSON para MySQL
- [ ] Webhook para confirmar pagamento
- [ ] Email de confirmação
- [ ] Painel admin para visualizar pedidos
- [ ] Upload de arquivos (vídeo/PDF)
- [ ] Rate limiting e captcha

---

**Versão:** 1.0.0  
**Data:** Janeiro 2026  
**Autor:** UNLI Studio
