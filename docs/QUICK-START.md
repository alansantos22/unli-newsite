# ⚡ Checklist de Implantação Rápida - Sistema de Onboarding

## 🎯 Objetivo
Colocar o sistema de onboarding pós-venda funcionando em **menos de 30 minutos**.

---

## ✅ Fase 1: Banco de Dados (5 minutos)

### 1.1 Executar Script SQL
```bash
mysql -u root -p seu_banco < database/install-onboarding.sql
```

**OU via phpMyAdmin:**
- Abrir phpMyAdmin
- Selecionar banco de dados
- Importar → `database/install-onboarding.sql`

### 1.2 Verificar Criação
```sql
SHOW TABLES LIKE 'orders';
SELECT COUNT(*) FROM orders;
```

**Resultado esperado:** Tabela criada, 3 registros de teste

---

## ✅ Fase 2: Configuração da API (5 minutos)

### 2.1 Editar Credenciais do Banco

Arquivo: `api/config.php`

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'unli_newsite');  // ← SEU BANCO
define('DB_USER', 'root');          // ← SEU USUÁRIO
define('DB_PASS', 'sua_senha');     // ← SUA SENHA
?>
```

### 2.2 Criar Diretórios Necessários

**Windows PowerShell:**
```powershell
New-Item -ItemType Directory -Force -Path "public\uploads\logos"
New-Item -ItemType Directory -Force -Path "api\logs"
```

**Linux/Mac:**
```bash
mkdir -p public/uploads/logos api/logs
chmod 755 public/uploads/logos api/logs
```

### 2.3 Testar API

```bash
# Substitua localhost:8080 pela sua URL
curl "http://localhost:8080/api/onboarding/validate.php?token=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6"
```

**Resultado esperado:** JSON com `"success": true`

---

## ✅ Fase 3: Frontend Vue.js (5 minutos)

### 3.1 Instalar Dependências (se necessário)

```bash
npm install
```

### 3.2 Verificar Rota

Arquivo: `src/router.js`

```javascript
{
  path: "/setup",
  name: "OnboardingWizard",
  component: () => import("./shared/Components/OnboardingWizard.vue")
}
```

**Status:** ✅ Já configurado

### 3.3 Iniciar Servidor de Desenvolvimento

```bash
npm run serve
```

### 3.4 Testar no Navegador

```
http://localhost:8080/setup?token=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6
```

**Resultado esperado:** Wizard carrega, Etapa 1 aparece

---

## ✅ Fase 4: Configuração de E-mail (10 minutos)

### 4.1 Testar Envio de E-mail Nativo

Arquivo: `api/lib/onboarding-helpers.php`

A função `sendOnboardingEmail()` já está configurada com `mail()`.

### 4.2 (RECOMENDADO) Configurar SMTP

Instale PHPMailer:

```bash
composer require phpmailer/phpmailer
```

Edite `api/lib/onboarding-helpers.php` para usar SMTP:

```php
use PHPMailer\PHPMailer\PHPMailer;

function sendOnboardingEmail($email, $name, $magicLink, $orderId, $planName = 'Site Vitrine') {
    $mail = new PHPMailer(true);
    
    // Configuração SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';  // ou seu servidor SMTP
    $mail->SMTPAuth = true;
    $mail->Username = 'seu-email@gmail.com';
    $mail->Password = 'sua-senha-app';  // Senha de app, não a senha normal
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    // Remetente e destinatário
    $mail->setFrom('noreply@unli.com.br', 'Unli Sites');
    $mail->addAddress($email, $name);
    
    // Conteúdo
    $template = file_get_contents(__DIR__ . '/emails/onboarding-magic-link.html');
    $emailBody = str_replace(
        ['{{CUSTOMER_NAME}}', '{{ORDER_ID}}', '{{PLAN_NAME}}', '{{ONBOARDING_LINK}}'],
        [$name, $orderId, $planName, $magicLink],
        $template
    );
    
    $mail->isHTML(true);
    $mail->Subject = '🚀 Vamos começar o seu site?';
    $mail->Body = $emailBody;
    
    return $mail->send();
}
```

### 4.3 Testar Envio

Crie um arquivo `api/test-email.php`:

```php
<?php
require_once 'lib/onboarding-helpers.php';

$result = sendOnboardingEmail(
    'seu-email@teste.com',
    'Teste',
    'https://unli.com.br/setup?token=teste123',
    999,
    'Teste de E-mail'
);

echo $result ? "✅ E-mail enviado!" : "❌ Erro ao enviar";
?>
```

Acesse: `http://localhost:8080/api/test-email.php`

---

## ✅ Fase 5: Integração com Checkout (5 minutos)

### 5.1 Configurar Webhook

Arquivo: `api/webhook-payment.php`

**Já está pronto!** Apenas ajuste a função `getWebhookData()` para o formato do seu gateway.

### 5.2 Configurar URL no Gateway

**Mercado Pago:**
- Painel → Integrações → Webhooks
- URL: `https://seu-dominio.com/api/webhook-payment.php`
- Eventos: `payment`

**Stripe:**
- Dashboard → Developers → Webhooks
- URL: `https://seu-dominio.com/api/webhook-payment.php`
- Eventos: `charge.succeeded`

### 5.3 Testar Webhook Manualmente

```bash
curl -X POST http://localhost:8080/api/webhook-payment.php \
  -H "Content-Type: application/json" \
  -d '{
    "status": "paid",
    "payment_id": "test_manual_123",
    "amount": 1497.00,
    "customer_name": "Teste Manual",
    "customer_email": "teste@manual.com",
    "customer_phone": "(11) 99999-9999",
    "plan_name": "Site Vitrine Anual"
  }'
```

**Resultado esperado:** 
- `{"success":true,"processed":true}`
- Novo pedido criado no banco
- E-mail enviado (se configurado)

---

## ✅ Checklist Final - Validação Completa

Execute estes testes rápidos:

### ✅ 1. Banco de Dados
```sql
SELECT COUNT(*) FROM orders; -- Deve ter pelo menos 3 registros
```

### ✅ 2. API Validação
```bash
curl "http://localhost:8080/api/onboarding/validate.php?token=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6"
# Deve retornar JSON com success: true
```

### ✅ 3. Frontend Wizard
```
http://localhost:8080/setup?token=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6
# Deve carregar o wizard
```

### ✅ 4. Auto-Save
- Preencha campo no wizard
- Aguarde 2 segundos
- Veja "💾 Salvando rascunho..."

### ✅ 5. Fluxo Completo
- Preencha todas as 5 etapas
- Clique em "Enviar e Iniciar Produção"
- Modal de sucesso aparece

### ✅ 6. Verificar no Banco
```sql
SELECT 
  id, 
  customer_name, 
  onboarding_status, 
  completed_at 
FROM orders 
WHERE onboarding_token = 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6';
-- Status deve ser 'concluido' com data em completed_at
```

### ✅ 7. E-mail (se configurado)
- Webhook manual deve enviar e-mail
- Verifique caixa de entrada (e spam)

---

## 🚀 Deploy em Produção

### Pré-Deploy Checklist

- [ ] **Banco:** Criar tabela em produção
- [ ] **Config:** Atualizar `api/config.php` com credenciais de produção
- [ ] **HTTPS:** Certificado SSL configurado (obrigatório!)
- [ ] **URLs:** Substituir `localhost` por domínio real em:
  - `api/webhook-payment.php` → função `getBaseUrl()`
  - `api/emails/onboarding-magic-link.html` → links
- [ ] **SMTP:** Configurar e-mail transacional (SendGrid/Mailgun)
- [ ] **Uploads:** Diretório `public/uploads/logos` com permissão 755
- [ ] **Logs:** Diretório `api/logs` com permissão 755
- [ ] **Teste:** Fazer pagamento teste e preencher wizard completo

### Deploy Rápido

```bash
# 1. Build do Vue.js
npm run build

# 2. Upload de arquivos
# - /dist → raiz do servidor
# - /api → raiz do servidor
# - /database → não precisa fazer upload

# 3. Configurar .htaccess (Apache) ou nginx.conf
# Para redirecionar rotas SPA
```

**Apache `.htaccess`:**
```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /
  RewriteRule ^index\.html$ - [L]
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule . /index.html [L]
</IfModule>
```

### Teste Pós-Deploy

1. Acesse: `https://seu-dominio.com/setup?token=TOKEN_DE_TESTE`
2. Faça um pagamento real (valor pequeno)
3. Verifique e-mail
4. Preencha wizard completo

---

## 📊 Monitoramento

### Queries para Dashboard

```sql
-- Total de onboardings concluídos hoje
SELECT COUNT(*) FROM orders 
WHERE onboarding_status = 'concluido' 
AND DATE(completed_at) = CURDATE();

-- Taxa de conclusão geral
SELECT 
  COUNT(*) as total_pagos,
  SUM(CASE WHEN onboarding_status = 'concluido' THEN 1 ELSE 0 END) as concluidos,
  ROUND(SUM(CASE WHEN onboarding_status = 'concluido' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as taxa_conversao
FROM orders 
WHERE payment_status = 'paid';

-- Pendentes há mais de 3 dias (enviar follow-up)
SELECT id, customer_name, email, created_at
FROM orders 
WHERE onboarding_status IN ('pendente', 'preenchendo')
AND payment_status = 'paid'
AND DATEDIFF(NOW(), created_at) > 3;
```

---

## 🎉 Pronto!

Se todos os itens acima estão ✅:

**Sistema está 100% funcional e pronto para converter clientes!**

### Próximos Passos Opcionais

1. **Analytics:** Adicionar Google Analytics nos eventos do wizard
2. **Follow-up:** Criar cron job para e-mails de lembrete
3. **Dashboard Admin:** Criar painel para visualizar briefings
4. **WhatsApp:** Integrar com API do WhatsApp Business
5. **Notificações:** Slack/Discord quando novo briefing chega

---

**Tempo total de implantação:** ~30 minutos ⚡

**Impacto esperado:** +40% na conclusão do onboarding vs. formulário tradicional 📈
