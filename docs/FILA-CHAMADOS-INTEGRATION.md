# 🎫 Integração com Fila Chamados

Sistema de criação automática de tickets no **Fila Chamados** para cada venda realizada no site.

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Como Funciona](#como-funciona)
3. [Configuração](#configuração)
4. [Arquivos Modificados](#arquivos-modificados)
5. [Teste da Integração](#teste-da-integração)
6. [Troubleshooting](#troubleshooting)
7. [Segurança](#segurança)

---

## 🎯 Visão Geral

A cada venda confirmada (pagamento aprovado), o sistema **automaticamente cria um ticket** no Fila Chamados contendo:

- ✅ Dados do cliente (nome, email, telefone)
- ✅ Detalhes do pedido (ID, produto, valor)
- ✅ Informações de pagamento (método, ID da transação)
- ✅ Briefing completo do cliente
- ✅ Próximos passos sugeridos

**Benefícios:**
- 📌 Nenhuma venda é perdida ou esquecida
- 📌 Equipe é notificada instantaneamente
- 📌 Todas as informações centralizadas em um ticket
- 📌 Histórico completo de cada venda

---

## ⚙️ Como Funciona

### Fluxo de Integração

```
1. Cliente faz a compra
         ↓
2. Pagamento é processado (Mercado Pago)
         ↓
3. Pagamento APROVADO?
         ├─ SIM → 🎫 Cria ticket no Fila Chamados
         └─ NÃO → Apenas registra no banco
```

### Pontos de Integração

A criação de tickets acontece em **2 momentos**:

#### 1. **Pagamento Direto com Cartão** (`process_payment.php`)
   - Quando o cartão é aprovado instantaneamente
   - Status: `approved`
   - Ticket criado imediatamente

#### 2. **Webhook de Confirmação** (`webhook-payment.php`)
   - Para pagamentos PIX ou confirmações assíncronas
   - Status: `paid` / `approved`
   - Ticket criado ao receber confirmação

---

## 🔧 Configuração

### Passo 1: Obter API Key do Fila Chamados

1. Faça login no Fila Chamados como **Owner do time**
2. Acesse **Integração / API** no menu lateral
3. Copie a **API Key** exibida

### Passo 2: Configurar o Sistema

Edite o arquivo `api/config.secure.php`:

```php
// ============================================
// CONFIGURAÇÕES FILA CHAMADOS - INTEGRAÇÃO
// ============================================

// ✅ ATIVO: Habilitar criação automática de tickets
define('FILA_CHAMADOS_ENABLED', true);

// ⚠️ SUBSTITUA pela sua API Key real
define('FILA_CHAMADOS_API_KEY', 'sua_api_key_aqui');

// URL da API do Fila Chamados (ajuste para seu domínio)
define('FILA_CHAMADOS_API_URL', 'https://seudominio.com/external_api.php');

// ID da categoria (opcional)
define('FILA_CHAMADOS_CATEGORY_ID', null); // ou número da categoria
```

### Passo 3: Verificar Instalação

Execute o script de teste:

```bash
curl http://localhost/api/test-fila-chamados.php
```

Ou acesse pelo navegador:
```
http://localhost/api/test-fila-chamados.php
```

**Resposta esperada:**
```json
{
    "test": "Configuration Check",
    "fila_chamados_enabled": true,
    "api_key_configured": true,
    "api_url_configured": true,
    "category_id": null
}
```

---

## 📁 Arquivos Modificados

### ✨ Novos Arquivos

| Arquivo | Descrição |
|---------|-----------|
| `api/lib/fila-chamados.php` | Helper com funções de integração |
| `api/test-fila-chamados.php` | Script de teste da integração |
| `docs/FILA-CHAMADOS-INTEGRATION.md` | Esta documentação |

### ✏️ Arquivos Modificados

| Arquivo | Alteração |
|---------|-----------|
| `api/config.secure.php` | Adicionadas constantes de configuração |
| `api/process_payment.php` | Integração ao aprovar pagamento com cartão |
| `api/webhook-payment.php` | Integração ao receber webhook de confirmação |

---

## 🧪 Teste da Integração

### Teste 1: Verificar Configuração

```bash
curl http://localhost/api/test-fila-chamados.php
```

Valida se as configurações estão corretas.

### Teste 2: Criar Ticket de Teste

Descomente o bloco de teste no arquivo `api/test-fila-chamados.php` (linha 56):

```php
/*
echo "=== CREATE TEST TICKET ===\n";
// ... código de teste ...
*/
```

Remova os comentários `/*` e `*/`, salve e execute novamente:

```bash
curl http://localhost/api/test-fila-chamados.php
```

Isso criará um ticket de teste real no Fila Chamados.

### Teste 3: Simular Venda Real

Faça uma compra de teste no sistema e verifique:

1. ✅ Pagamento aprovado no Mercado Pago
2. ✅ Ticket criado no Fila Chamados
3. ✅ Email de notificação recebido pela equipe

---

## 🔍 Estrutura do Ticket Criado

### Assunto
```
🎉 Nova Venda - Site Vitrine (#ORDER_12345)
```

### Conteúdo

#### 👤 Dados do Cliente
- Nome completo
- Email
- Telefone

#### 📦 Detalhes do Pedido
- ID do Pedido
- Produto/Plano contratado
- Valor pago
- Forma de pagamento
- ID da transação
- Data/Hora

#### 🎨 Especificações do Produto
- Número de páginas
- Tipo de conteúdo (cliente/unli)

#### 📝 Briefing do Cliente
- Nome da empresa
- Ramo de atividade
- Descrição do negócio
- Público-alvo
- Objetivos
- Possui logo?
- Preferências de cores
- Sites de referência

#### ⏭️ Próximos Passos
1. Confirmar recebimento do pagamento
2. Entrar em contato com o cliente
3. Iniciar desenvolvimento
4. Agendar reunião de apresentação

---

## 🐛 Troubleshooting

### Problema: "API Key not configured"

**Causa:** API Key não foi configurada ou ainda contém o valor padrão.

**Solução:**
1. Obtenha a API Key no Fila Chamados
2. Edite `api/config.secure.php`
3. Substitua `'SUA_API_KEY_AQUI'` pela chave real

### Problema: "Connection failed: cURL error"

**Causa:** Servidor não consegue se conectar com o Fila Chamados.

**Soluções:**
1. Verifique se a URL da API está correta
2. Confirme que o Fila Chamados está acessível
3. Verifique firewall/bloqueios de rede
4. Confirme que cURL está habilitado no PHP

### Problema: "Invalid API Key (401)"

**Causa:** API Key está incorreta ou expirou.

**Solução:**
1. Confirme que copiou a chave completa
2. Regere a API Key no Fila Chamados se necessário
3. Atualize em `config.secure.php`

### Problema: Ticket não é criado mas pagamento funciona

**Causa:** Integração pode estar desabilitada ou falhando silenciosamente.

**Diagnóstico:**
1. Verifique logs do PHP: `tail -f /var/log/php_errors.log`
2. Procure por mensagens `FILA CHAMADOS:`
3. Execute o script de teste

**Possíveis causas:**
- `FILA_CHAMADOS_ENABLED` está `false`
- Erro de conexão ignorado
- Email inválido no pedido

### Problema: Rate Limit Exceeded (429)

**Causa:** Muitas requisições em pouco tempo (limite: 5 por segundo).

**Solução:**
- Aguarde alguns segundos
- Verifique se não há loop infinito
- Considere implementar fila de processamento

---

## 🔒 Segurança

### ⚠️ Importante: Proteja suas Credenciais

1. **NUNCA faça commit** do arquivo `config.secure.php` no Git
2. **Mantenha permissões restritas**: `chmod 600 api/config.secure.php`
3. **Use HTTPS** em produção
4. **Remova o arquivo de teste** em produção: `api/test-fila-chamados.php`

### Proteções Implementadas

- ✅ API Key armazenada em arquivo protegido
- ✅ Validação de configuração antes de usar
- ✅ Erros não expõem credenciais
- ✅ Logs detalhados apenas em modo debug
- ✅ Timeout de conexão configurado
- ✅ SSL verificado por padrão

### .htaccess Sugerido

Crie ou edite `api/.htaccess`:

```apache
# Bloquear acesso a arquivos sensíveis
<FilesMatch "^(config\.secure\.php|test-fila-chamados\.php)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Permitir acesso apenas a arquivos específicos
<FilesMatch "\.(php)$">
    Order allow,deny
    Allow from all
</FilesMatch>
```

---

## 📊 Logs e Monitoramento

### Logs Disponíveis

#### 1. **Sucesso na Criação de Ticket**
```
✅ FILA CHAMADOS: Ticket #123 criado para pedido #ORDER_456 (cliente@email.com)
```

#### 2. **Falha na Criação**
```
❌ FILA CHAMADOS: Falha ao criar ticket para pedido #ORDER_456 - Invalid API Key
```

#### 3. **Integração Desabilitada**
```
(nenhum log - operação pulada silenciosamente)
```

### Visualizar Logs

#### Linux/Mac:
```bash
tail -f /var/log/apache2/error.log
```

#### Windows (XAMPP):
```
C:\xampp\apache\logs\error.log
```

#### PHP-FPM:
```bash
tail -f /var/log/php-fpm/error.log
```

---

## 🎓 Exemplos de Uso

### Desabilitar Temporariamente

```php
// Em config.secure.php
define('FILA_CHAMADOS_ENABLED', false);
```

### Usar Categoria Específica

```php
// Em config.secure.php
define('FILA_CHAMADOS_CATEGORY_ID', 5); // ID da categoria "Vendas"
```

### Integração Customizada

Se precisar criar tickets em outros momentos:

```php
require_once __DIR__ . '/lib/fila-chamados.php';

$orderData = [
    'customer_name' => 'Cliente X',
    'customer_email' => 'cliente@email.com',
    'order_id' => 'ORD_123',
    'plan_name' => 'Site Premium',
    'amount' => 2500.00,
    'payment_method' => 'pix',
    'payment_id' => 'MP_XYZ789'
];

$result = createTicketForSale($orderData);

if ($result['success']) {
    echo "Ticket #{$result['ticket_id']} criado!";
}
```

---

## 📞 Suporte

Para dúvidas ou problemas:

1. **Consulte os logs** do PHP e Apache
2. **Execute o script de teste** para diagnóstico
3. **Verifique a documentação** do Fila Chamados
4. **Revise as configurações** em `config.secure.php`

---

## 📝 Changelog

### v1.0.0 (Janeiro 2026)
- ✅ Integração inicial com Fila Chamados
- ✅ Criação automática de tickets em vendas
- ✅ Suporte a pagamento cartão e PIX
- ✅ Logs detalhados e tratamento de erros
- ✅ Script de teste incluído
- ✅ Documentação completa

---

**✨ Integração desenvolvida para UNLI - Sistema de Vendas**
