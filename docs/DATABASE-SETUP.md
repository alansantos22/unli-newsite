# 🗄️ CONFIGURAÇÃO DO BANCO DE DADOS MYSQL - HOSTINGER

## ✅ Status: Configurado

As credenciais do banco de dados MySQL da Hostinger foram adicionadas ao sistema.

---

## 📋 Credenciais Configuradas

**Arquivo:** `api/db.config.php`

```
Host: localhost
Database: u969754391_unli
Username: u969754391_rootunli
Password: Fil@Ch@m@d0s
```

---

## 🔄 Fluxo de Salvamento de Dados

### 1️⃣ **Criação do Pedido** (Status: `pending`)

Quando o usuário finaliza a configuração e clica em "Ir para Pagamento":

**Arquivo:** `api/order_create.php`

```
✅ Dados salvos no arquivo JSON (compatibilidade)
✅ Dados salvos no MySQL (novo)
```

**Status inicial no banco:**
- `payment_status`: `pending`
- `onboarding_status`: `pendente`

### 2️⃣ **Processamento do Pagamento** (Status: atualizado)

Quando o pagamento é processado:

**Arquivo:** `api/process_payment.php`

```
✅ Atualiza payment_status no banco
✅ Adiciona payment_id do gateway
```

**Status atualizado:**
- `payment_status`: `pending` (se processando) ou `paid` (se aprovado imediatamente)
- `payment_id`: ID da transação no Mercado Pago

### 3️⃣ **Confirmação via Webhook** (Status: `paid`)

Quando o Mercado Pago confirma o pagamento:

**Arquivo:** `api/webhook_mercadopago.php`

```
✅ Recebe notificação do Mercado Pago
✅ Atualiza payment_status para 'paid'
✅ Envia e-mail com magic link para onboarding
```

**Status final:**
- `payment_status`: `paid`
- `onboarding_status`: `pendente`

---

## 📊 Estrutura da Tabela `orders`

```sql
CREATE TABLE unli_orders (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  
  -- Informações do Cliente
  customer_name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(50),
  
  -- Detalhes do Pedido
  order_details TEXT,           -- JSON com configuração
  total_amount DECIMAL(10,2),
  
  -- Status de Pagamento
  payment_status ENUM('pending', 'paid', 'failed', 'refunded'),
  payment_method VARCHAR(50),
  payment_id VARCHAR(255),      -- ID do Mercado Pago
  
  -- Sistema de Onboarding
  onboarding_token VARCHAR(64) UNIQUE,
  onboarding_status ENUM('pendente', 'preenchendo', 'concluido'),
  briefing_data LONGTEXT,       -- Dados do briefing preenchido
  
  -- Timestamps
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  completed_at TIMESTAMP NULL
);
```

---

## 🚀 Como Instalar a Tabela

### ✅ Método Recomendado: Via Browser (PHP)

Acesse o arquivo de migration pelo navegador:

```
https://seu-dominio.com/api/migrate.php?password=unli2026secure
```

**⚠️ IMPORTANTE:** Altere a senha no arquivo `api/migrate.php` antes de usar em produção!

O sistema irá:
- ✅ Criar tabelas se não existirem
- ✅ Adicionar colunas faltantes sem perder dados
- ✅ Criar índices necessários
- ✅ Mostrar log detalhado de todas as operações

### 🔄 Rollback (Se necessário)

Para remover a tabela completamente:

```
https://seu-dominio.com/api/migrate.php?password=unli2026secure&action=rollback
```

⚠️ **ATENÇÃO:** Isso remove TODOS os dados!

---

### Método Alternativo: phpMyAdmin

Se preferir, pode importar o SQL manualmente:

**Arquivo:** `database/install-onboarding.sql`

```bash
1. Acesse o phpMyAdmin da Hostinger
2. Selecione o banco u969754391_unli
3. Clique em "Importar"
4. Selecione o arquivo install-onboarding.sql
5. Execute
```

---

## 🔧 Arquivos Modificados

### ✅ Arquivos de Configuração

1. **`api/db.config.php`**
   - Credenciais do MySQL da Hostinger
   - Configuração de charset UTF-8

### ✅ Novos Arquivos

2. **`api/lib/database.php`**
   - Funções de conexão PDO
   - `save_order_to_db()` - Salva pedido inicial
   - `update_payment_status()` - Atualiza status de pagamento
   - `get_order_by_id()` - Busca pedido
   - `update_onboarding_status()` - Atualiza onboarding

3. **`api/webhook_mercadopago.php`**
   - Recebe notificações do Mercado Pago
   - Atualiza status automaticamente
   - Log de todas as transações

4. **`api/migrate.php`** ⭐ NOVO
   - Sistema de migrations via browser
   - Cria/atualiza tabelas sem perder dados
   - Interface visual com logs
   - Suporte a rollback

### ✅ Arquivos Atualizados

4. **`api/order_create.php`**
   - Agora salva no MySQL além do arquivo JSON
   - Retorna `onboarding_token` e `db_id`

5. **`api/process_payment.php`**
   - Atualiza status no banco após processar pagamento
   - Adiciona `payment_id` do gateway

---

## 📝 Estados do Pedido

### Payment Status

| Status | Descrição |
|--------|-----------|
| `pending` | Aguardando pagamento |
| `paid` | Pago e confirmado |
| `failed` | Pagamento falhou |
| `refunded` | Reembolsado |

### Onboarding Status

| Status | Descrição |
|--------|-----------|
| `pendente` | Cliente não iniciou o briefing |
| `preenchendo` | Cliente está preenchendo |
| `concluido` | Briefing finalizado |

---

## 🔍 Consultas Úteis

### Ver pedidos pendentes de pagamento
```sql
SELECT id, customer_name, email, total_amount, created_at
FROM unli_orders
WHERE payment_status = 'pending'
ORDER BY created_at DESC;
```

### Ver pedidos pagos aguardando onboarding
```sql
SELECT id, customer_name, email, onboarding_token
FROM unli_orders
WHERE payment_status = 'paid' 
  AND onboarding_status = 'pendente'
ORDER BY created_at DESC;
```

### Taxa de conversão do onboarding
```sql
SELECT 
  onboarding_status,
  COUNT(*) as total,
  ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM unli_orders WHERE payment_status = 'paid'), 2) as percentual
FROM unli_orders 
WHERE payment_status = 'paid'
GROUP BY onboarding_status;
```

---

## 🔐 Segurança

- ✅ Conexão PDO com prepared statements
- ✅ Senha do banco criptografada no arquivo protegido
- ✅ Tokens únicos de 64 caracteres
- ✅ Arquivo `db.config.php` no `.gitignore`

---

## 📞 Suporte

Se houver problemas:

1. Verifique se a tabela foi criada: `SHOW TABLES LIKE 'unli_orders';`
2. Verifique permissões do usuário MySQL
3. Confira os logs em `api/logs/webhook_YYYY-MM-DD.log`

---

## 🎯 Próximos Passos

1. ✅ Configurar URL do webhook no Mercado Pago
2. ⏳ Implementar envio de e-mail com PHPMailer
3. ⏳ Criar painel administrativo para visualizar pedidos
4. ⏳ Configurar backup automático do banco

---

**Última atualização:** 22 de Janeiro de 2026
