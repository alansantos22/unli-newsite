# 🔄 Sistema de Migrations - PHP via Browser

Sistema inteligente de migrations para hospedagem compartilhada que não permite acesso a terminal.

---

## 🎯 Características

✅ **Execução via Browser** - Acesse uma URL e pronto  
✅ **Zero Terminal** - Perfeito para hospedagem compartilhada  
✅ **Inteligente** - Não perde dados existentes  
✅ **Seguro** - Protegido por senha  
✅ **Visual** - Interface com logs detalhados  
✅ **Rollback** - Desfaz alterações se necessário

---

## 🚀 Como Usar

### 1️⃣ **Configurar Senha**

Edite o arquivo `api/migrate.php` e altere a senha:

```php
$MIGRATION_PASSWORD = 'SUA_SENHA_AQUI';
```

### 2️⃣ **Executar Migrations**

Acesse no navegador:

```
https://seu-dominio.com/api/migrate.php?password=SUA_SENHA_AQUI
```

### 3️⃣ **Ver Resultados**

O sistema mostrará uma interface visual com:
- ✅ Log de todas as operações
- ⚠️ Avisos e erros
- 📊 Status de cada tabela/coluna

---

## 🔧 O Que o Sistema Faz

### ✅ Criação Inteligente

**Se a tabela NÃO existe:**
- Cria a tabela completa
- Adiciona todos os índices
- Configura constraints

**Se a tabela JÁ existe:**
- Verifica coluna por coluna
- Adiciona apenas as que faltam
- Não toca nos dados existentes
- Adiciona índices faltantes

### 📋 Exemplo de Execução

```
🚀 Iniciando MIGRATIONS...
🔍 Verificando tabela unli_orders...
✅ Tabela unli_orders já existe
➕ Adicionando coluna payment_id...
✅ Coluna payment_id adicionada!
📊 Adicionando índice idx_payment_id...
✅ Índice idx_payment_id adicionado!
🎉 Migrations concluídas!
```

---

## ⚠️ Rollback (Desfazer)

Se precisar remover tudo e começar do zero:

```
https://seu-dominio.com/api/migrate.php?password=SUA_SENHA&action=rollback
```

**⚠️ CUIDADO:** Isso remove a tabela e TODOS os dados!

---

## 🏗️ Estrutura da Tabela `orders`

### Colunas Criadas

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id` | INT | ID único (auto-increment) |
| `customer_name` | VARCHAR(255) | Nome do cliente |
| `email` | VARCHAR(255) | E-mail |
| `phone` | VARCHAR(50) | Telefone |
| `order_details` | TEXT | JSON com detalhes |
| `total_amount` | DECIMAL(10,2) | Valor total |
| `payment_status` | ENUM | pending/paid/failed/refunded |
| `payment_method` | VARCHAR(50) | Método de pagamento |
| `payment_id` | VARCHAR(255) | ID no gateway |
| `onboarding_token` | VARCHAR(64) | Token único (UNIQUE) |
| `onboarding_status` | ENUM | pendente/preenchendo/concluido |
| `briefing_data` | LONGTEXT | Dados do briefing |
| `created_at` | TIMESTAMP | Data de criação |
| `updated_at` | TIMESTAMP | Última atualização |
| `completed_at` | TIMESTAMP | Data de conclusão |

### Índices Criados

- `PRIMARY KEY` (id)
- `UNIQUE` (onboarding_token)
- `INDEX` (email)
- `INDEX` (onboarding_status)
- `INDEX` (payment_status)
- `INDEX` (payment_id)

---

## 🔐 Segurança

### ✅ Proteções Implementadas

1. **Senha obrigatória** - Sem senha, sem acesso
2. **Sem SQL injection** - Usa prepared statements
3. **Logs detalhados** - Rastreamento completo
4. **Confirmação de rollback** - JavaScript alert antes de apagar

### 🔒 Recomendações

1. Altere a senha padrão
2. Após executar, remova o arquivo ou bloqueie via .htaccess
3. Use apenas em ambiente seguro

---

## 📝 Adicionando Novas Migrations

Para adicionar novas colunas no futuro, edite `migrate.php`:

```php
// Adicione na array $columns_to_check:
'nova_coluna' => "VARCHAR(100) DEFAULT NULL COMMENT 'Descrição da coluna'"
```

Execute novamente e o sistema adiciona apenas o que falta!

---

## 🆘 Solução de Problemas

### ❌ Erro: Acesso negado

- Verifique se a senha está correta
- Use: `?password=SUA_SENHA`

### ❌ Erro de conexão

- Verifique `api/db.config.php`
- Confirme credenciais do banco

### ⚠️ Coluna não foi adicionada

- Verifique o log de erros
- Pode ser problema de permissões do usuário MySQL
- Tente via phpMyAdmin manualmente

---

## 🎓 Como Funciona

1. **Conecta** no banco usando PDO
2. **Verifica** se tabela existe
   - Se não: cria completa
   - Se sim: verifica coluna por coluna
3. **Adiciona** apenas o que falta
4. **Nunca** remove ou altera dados existentes
5. **Loga** tudo que fez

---

## 📞 Suporte

Se algo der errado:

1. Veja os logs na interface
2. Verifique permissões do usuário MySQL
3. Teste conexão via phpMyAdmin
4. Em último caso: use rollback e recrie

---

**Criado para:** Hospedagem compartilhada sem acesso a terminal  
**Testado em:** cPanel, Hostinger, HostGator  
**Compatível com:** MySQL 5.6+, MariaDB 10+
