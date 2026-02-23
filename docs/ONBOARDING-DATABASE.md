# Sistema de Onboarding com Magic Link

## Estrutura do Banco de Dados

### Tabela: `orders`

Esta tabela armazena os pedidos e suas informações de onboarding.

```sql
CREATE TABLE `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  
  -- Informações do Cliente
  `customer_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  
  -- Informações do Pedido
  `order_details` TEXT DEFAULT NULL COMMENT 'JSON com detalhes do plano contratado',
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_status` ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
  `payment_method` VARCHAR(50) DEFAULT NULL,
  `payment_id` VARCHAR(255) DEFAULT NULL COMMENT 'ID da transação no gateway',
  
  -- Sistema de Onboarding
  `onboarding_token` VARCHAR(64) UNIQUE NOT NULL COMMENT 'Token único para acesso ao wizard',
  `onboarding_status` ENUM('pendente', 'preenchendo', 'concluido') DEFAULT 'pendente',
  `briefing_data` LONGTEXT DEFAULT NULL COMMENT 'JSON com dados do briefing preenchido',
  
  -- Timestamps
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Data de conclusão do briefing',
  
  PRIMARY KEY (`id`),
  INDEX `idx_token` (`onboarding_token`),
  INDEX `idx_email` (`email`),
  INDEX `idx_status` (`onboarding_status`),
  INDEX `idx_payment_status` (`payment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Campos Importantes

### `onboarding_token`
- **Tipo**: VARCHAR(64)
- **Descrição**: Token único e seguro gerado após confirmação de pagamento
- **Geração**: `md5(uniqid(rand(), true))` ou UUID
- **Uso**: Enviado no Magic Link para acesso sem login
- **Exemplo**: `a8f7d6e5c4b3a2f1e0d9c8b7a6f5e4d3`

### `onboarding_status`
- **pendente**: Pagamento confirmado, aguardando preenchimento
- **preenchendo**: Cliente iniciou o preenchimento (há rascunho salvo)
- **concluido**: Briefing finalizado e enviado para produção

### `briefing_data`
- **Tipo**: LONGTEXT (JSON)
- **Descrição**: Armazena todos os dados preenchidos pelo cliente
- **Estrutura**:

```json
{
  "companyName": "Padaria Dona Maria",
  "slogan": "A melhor pizza da Mooca",
  "logoUrl": "/uploads/logos/logo_123_abc.png",
  "hasNoLogo": false,
  "whatsapp": "(11) 99999-9999",
  "instagram": "https://instagram.com/padariadona",
  "facebook": "",
  "address": "Rua das Flores, 123 - Mooca - São Paulo/SP",
  "noPhysicalLocation": false,
  "description": "Nós ajudamos famílias a ter o melhor pão fresco todos os dias",
  "services": [
    {
      "name": "Pães Artesanais",
      "description": "Pães fresquinhos assados diariamente"
    },
    {
      "name": "Bolos Caseiros",
      "description": "Bolos feitos com receitas tradicionais"
    },
    {
      "name": "Pizzas",
      "description": "As melhores pizzas da região"
    }
  ],
  "differentials": ["fast", "quality", "experience"],
  "customDifferentials": "Entrega grátis acima de R$ 50",
  "primaryColor": "#FF6B35",
  "designStyle": "modern",
  "notes": "Gostaria de destacar as promoções do dia na página inicial"
}
```

## Fluxo de Dados

### 1. Após Pagamento Confirmado

```php
// Webhook do gateway de pagamento (Pagar.me, Stripe, etc.)
$orderId = createOrder([
    'customer_name' => $customerName,
    'email' => $email,
    'total_amount' => $amount,
    'payment_status' => 'paid',
    'onboarding_token' => generateUniqueToken(),
    'onboarding_status' => 'pendente'
]);

// Enviar e-mail com Magic Link
$magicLink = "https://unli.com.br/setup?token={$token}";
sendOnboardingEmail($email, $customerName, $magicLink);
```

### 2. Cliente Acessa o Link

```javascript
// Vue.js - OnboardingWizard.vue
mounted() {
  const token = this.$route.query.token;
  
  // Valida token
  const response = await fetch(`/api/onboarding/validate.php?token=${token}`);
  const data = await response.json();
  
  if (data.status === 'concluido') {
    // Já foi preenchido
    showCompletedMessage();
  } else {
    // Carrega rascunho se existir
    loadDraft(data.briefing);
  }
}
```

### 3. Auto-Save Durante Preenchimento

```php
// API: /api/onboarding/save-draft.php
UPDATE orders 
SET 
    briefing_data = '$briefingJson',
    onboarding_status = IF(onboarding_status = 'pendente', 'preenchendo', onboarding_status),
    updated_at = NOW()
WHERE onboarding_token = '$token';
```

### 4. Submissão Final

```php
// API: /api/onboarding/submit.php
UPDATE orders 
SET 
    briefing_data = '$finalBriefingJson',
    onboarding_status = 'concluido',
    completed_at = NOW()
WHERE onboarding_token = '$token';

// Notificar equipe
sendTeamNotification($orderId, $briefingData);

// Confirmar ao cliente
sendCompletionEmail($email, $customerName);
```

## Índices e Performance

```sql
-- Buscar por token (usado em toda requisição do wizard)
INDEX `idx_token` (`onboarding_token`)

-- Filtrar pedidos pendentes para follow-up
INDEX `idx_status` (`onboarding_status`)

-- Buscar pedidos de um cliente
INDEX `idx_email` (`email`)

-- Relatórios de pagamento
INDEX `idx_payment_status` (`payment_status`)
```

## Segurança

### ✅ Implementado
- Token único e imprevisível (64 caracteres)
- Validação de token a cada requisição
- Status de "concluído" impede edições posteriores
- Prepared statements (SQL injection protection)
- CORS configurado nas APIs

### 🔒 Recomendações Adicionais
- Expiração de token após 30 dias
- Rate limiting nas APIs (prevenir abuso)
- HTTPS obrigatório em produção
- Logs de acesso ao token

## Criação da Tabela

Execute este script SQL no seu banco de dados:

```sql
-- Verificar se a tabela já existe
DROP TABLE IF EXISTS `orders`;

-- Criar tabela
CREATE TABLE `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `order_details` TEXT DEFAULT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_status` ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
  `payment_method` VARCHAR(50) DEFAULT NULL,
  `payment_id` VARCHAR(255) DEFAULT NULL,
  `onboarding_token` VARCHAR(64) UNIQUE NOT NULL,
  `onboarding_status` ENUM('pendente', 'preenchendo', 'concluido') DEFAULT 'pendente',
  `briefing_data` LONGTEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_token` (`onboarding_token`),
  INDEX `idx_email` (`email`),
  INDEX `idx_status` (`onboarding_status`),
  INDEX `idx_payment_status` (`payment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Função Helper para Gerar Token

Adicione ao seu arquivo `config.php` ou crie um `helpers.php`:

```php
<?php
/**
 * Gera um token único e seguro para onboarding
 */
function generateOnboardingToken() {
    return md5(uniqid(rand(), true));
}

/**
 * Valida formato do token
 */
function isValidTokenFormat($token) {
    return preg_match('/^[a-f0-9]{32}$/', $token);
}
?>
```

## Exemplo de Integração com Checkout

```php
<?php
// Após pagamento aprovado via webhook
require_once 'config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Criar pedido
$token = generateOnboardingToken();
$stmt = $conn->prepare("
    INSERT INTO orders (
        customer_name, 
        email, 
        phone,
        order_details,
        total_amount, 
        payment_status, 
        payment_id,
        onboarding_token,
        onboarding_status
    ) VALUES (?, ?, ?, ?, ?, 'paid', ?, ?, 'pendente')
");

$orderDetailsJson = json_encode([
    'plan' => 'Site Vitrine Anual',
    'price' => 1497.00,
    'features' => ['Domínio', 'SSL', 'E-mail', 'Suporte']
]);

$stmt->bind_param(
    'ssssdss',
    $customerName,
    $email,
    $phone,
    $orderDetailsJson,
    $amount,
    $paymentId,
    $token
);

$stmt->execute();
$orderId = $conn->insert_id;

// Enviar e-mail
$magicLink = "https://unli.com.br/setup?token={$token}";
sendOnboardingEmail($email, $customerName, $magicLink, $orderId);

echo json_encode(['success' => true, 'orderId' => $orderId]);
?>
```

## Queries Úteis para Administração

```sql
-- Ver todos os pedidos pendentes de onboarding
SELECT 
    id, 
    customer_name, 
    email, 
    onboarding_status,
    created_at
FROM orders 
WHERE onboarding_status IN ('pendente', 'preenchendo')
ORDER BY created_at DESC;

-- Ver briefings completos recentes
SELECT 
    id,
    customer_name,
    email,
    completed_at,
    JSON_EXTRACT(briefing_data, '$.companyName') as empresa,
    JSON_EXTRACT(briefing_data, '$.designStyle') as estilo
FROM orders 
WHERE onboarding_status = 'concluido'
ORDER BY completed_at DESC
LIMIT 10;

-- Estatísticas de conversão
SELECT 
    onboarding_status,
    COUNT(*) as total,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM orders), 2) as percentual
FROM orders 
WHERE payment_status = 'paid'
GROUP BY onboarding_status;
```

---

## 🚀 Próximos Passos

1. **Criar a tabela** no banco de dados MySQL
2. **Atualizar `config.php`** com as credenciais corretas
3. **Testar as APIs** individualmente com Postman ou similar
4. **Integrar com o checkout** existente
5. **Configurar envio de e-mails** (SMTP ou serviço externo)
6. **Criar painel admin** para visualizar briefings recebidos
