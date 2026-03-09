# 🚀 Sistema de Onboarding Pós-Venda com Magic Link

## 📋 Visão Geral

Este sistema implementa uma estratégia revolucionária de conversão: **Pagar Primeiro, Configurar Depois**.

### Por que isso funciona?

- ✅ **Zero fricção no checkout** - Cliente paga sem precisar preencher briefing gigante
- ✅ **Compromisso financeiro primeiro** - Depois de pagar, ele está motivado a concluir
- ✅ **Acesso sem login** - Magic Link elimina necessidade de criar conta/senha
- ✅ **Auto-save inteligente** - Cliente pode pausar e continuar quando quiser
- ✅ **Gamificação** - Wizard com barra de progresso torna preenchimento divertido

## 🎯 Fluxo Completo

```
1. Cliente escolhe plano → 2. Paga (Checkout) → 3. Recebe e-mail com Magic Link
                                                           ↓
4. Acessa wizard sem login → 5. Preenche 5 etapas → 6. Envia briefing → 7. Produção inicia
```

## 📁 Estrutura de Arquivos Criados

```
api/
├── onboarding/
│   ├── validate.php          # Valida token do Magic Link
│   ├── save-draft.php        # Auto-save do briefing
│   └── submit.php            # Submissão final
├── emails/
│   └── onboarding-magic-link.html  # Template de e-mail
└── lib/
    └── onboarding-helpers.php      # Funções auxiliares

src/
├── shared/
│   └── Components/
│       └── OnboardingWizard.vue    # Componente principal do wizard
└── router.js                        # Rota /setup adicionada

docs/
└── ONBOARDING-DATABASE.md          # Documentação do banco de dados
```

## 🎨 O Wizard (5 Etapas)

### Etapa 1: Identidade Vital
- Nome da empresa
- Slogan
- Upload de logo (ou checkbox "não tenho logo")

### Etapa 2: Contato e Localização
- WhatsApp (botão flutuante do site)
- Instagram / Facebook
- Endereço físico (com opção de ocultar)

### Etapa 3: Conteúdo
- Descrição da empresa (1 frase guiada)
- 3 principais serviços/produtos
- Diferenciais (checkboxes + campo livre)

### Etapa 4: Estilo Visual
- Cor principal da marca (color picker + presets)
- Estilo de design (3 opções visuais)

### Etapa 5: Finalização
- Resumo das informações
- Campo de observações extras
- Botão "Enviar e Iniciar Produção"

## 🔐 Sistema de Magic Link

### Como Funciona

1. **Geração do Token**
   ```php
   $token = md5(uniqid(rand(), true)); // 32 caracteres hexadecimais
   ```

2. **Link Enviado por E-mail**
   ```
   https://unli.com.br/setup?token=a8f7d6e5c4b3a2f1e0d9c8b7a6f5e4d3
   ```

3. **Validação no Frontend**
   ```javascript
   const token = this.$route.query.token;
   const response = await fetch(`/api/onboarding/validate.php?token=${token}`);
   ```

4. **Segurança**
   - Token único e imprevisível
   - Expira em 30 dias
   - Após conclusão, torna-se read-only
   - Prepared statements previnem SQL injection

## 💾 Auto-Save Inteligente

O sistema salva automaticamente conforme o cliente digita:

```javascript
// Debounce de 1 segundo
autoSave() {
  clearTimeout(this.autoSaveTimeout);
  this.autoSaveTimeout = setTimeout(() => {
    this.saveToLocalStorage();    // Backup local
    this.saveDraftToServer();      // Backup no servidor
  }, 1000);
}
```

**Benefícios:**
- Se a internet cair, dados estão no localStorage
- Se ele fechar o navegador, dados estão no servidor
- Próximo acesso carrega o rascunho automaticamente

## 📊 Banco de Dados

### Tabela: `orders`

```sql
CREATE TABLE `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `onboarding_token` VARCHAR(64) UNIQUE NOT NULL,
  `onboarding_status` ENUM('pendente', 'preenchendo', 'concluido'),
  `briefing_data` LONGTEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `completed_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_token` (`onboarding_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Estados do Onboarding

| Status | Descrição | Ações Permitidas |
|--------|-----------|------------------|
| `pendente` | Pagamento confirmado, aguardando preenchimento | Visualizar, preencher, salvar |
| `preenchendo` | Cliente iniciou o preenchimento | Continuar, salvar, finalizar |
| `concluido` | Briefing finalizado e enviado | Apenas visualização |

## 📧 E-mail Transacional

Template profissional em HTML com:

- ✨ Design responsivo
- 🎨 Gradientes modernos
- 📱 Compatível com todos os clientes de e-mail
- 🚀 CTA destacado com o Magic Link
- 📅 Linha do tempo visual do processo

### Variáveis do Template

```html
{{CUSTOMER_NAME}}      → Nome do cliente
{{ORDER_ID}}           → Número do pedido
{{PLAN_NAME}}          → Nome do plano contratado
{{ONBOARDING_LINK}}    → URL do Magic Link
```

## 🔧 Instalação e Configuração

### Passo 1: Criar Tabela no Banco

```bash
mysql -u root -p nome_do_banco < docs/database-schema.sql
```

Ou copie o SQL de [ONBOARDING-DATABASE.md](docs/ONBOARDING-DATABASE.md)

### Passo 2: Configurar Credenciais

Edite `api/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'seu_banco');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
```

### Passo 3: Criar Diretório de Uploads

```bash
mkdir -p public/uploads/logos
chmod 755 public/uploads/logos
```

### Passo 4: Testar APIs

```bash
# Testar validação (substitua TOKEN pelo token real)
curl "http://localhost/api/onboarding/validate.php?token=TOKEN"

# Deve retornar JSON com success: true
```

### Passo 5: Configurar SMTP (Opcional)

Para e-mails mais confiáveis, use serviços como:
- SendGrid
- Mailgun
- Amazon SES
- PHPMailer com SMTP

## 🔗 Integração com Checkout

### Após Pagamento Aprovado

```php
<?php
require_once 'api/config.php';
require_once 'api/lib/onboarding-helpers.php';

// Webhook do gateway de pagamento
$paymentData = json_decode(file_get_contents('php://input'), true);

if ($paymentData['status'] === 'approved') {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Gerar token único
    $token = generateOnboardingToken();
    
    // Criar pedido
    $stmt = $conn->prepare("
        INSERT INTO orders (
            customer_name, 
            email, 
            total_amount, 
            payment_status,
            onboarding_token,
            onboarding_status
        ) VALUES (?, ?, ?, 'paid', ?, 'pendente')
    ");
    
    $stmt->bind_param('ssds', 
        $paymentData['customer']['name'],
        $paymentData['customer']['email'],
        $paymentData['amount'],
        $token
    );
    
    $stmt->execute();
    $orderId = $conn->insert_id;
    
    // Enviar Magic Link
    $magicLink = "https://unli.com.br/setup?token={$token}";
    sendOnboardingEmail(
        $paymentData['customer']['email'],
        $paymentData['customer']['name'],
        $magicLink,
        $orderId,
        'Site Vitrine Anual'
    );
    
    echo json_encode(['success' => true]);
}
?>
```

## 📱 Frontend (Vue.js)

### Rota Configurada

```javascript
// router.js
{
  path: "/setup",
  name: "OnboardingWizard",
  component: () => import("./shared/Components/OnboardingWizard.vue")
}
```

### Acesso

```
https://unli.com.br/setup?token=a8f7d6e5c4b3a2f1e0d9c8b7a6f5e4d3
```

### Features do Componente

- ✅ Validação de token ao montar
- ✅ Carregamento de rascunho salvo
- ✅ Auto-save com debounce
- ✅ Transições suaves entre etapas
- ✅ Validação de campos obrigatórios
- ✅ Upload de logo com preview
- ✅ Color picker interativo
- ✅ Resumo antes de enviar
- ✅ Modal de sucesso
- ✅ 100% responsivo

## 📈 Métricas e Monitoramento

### Queries Úteis

```sql
-- Taxa de conversão do onboarding
SELECT 
    onboarding_status,
    COUNT(*) as total,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM orders WHERE payment_status = 'paid'), 2) as percentual
FROM orders 
WHERE payment_status = 'paid'
GROUP BY onboarding_status;

-- Pedidos pendentes há mais de 3 dias
SELECT 
    id,
    customer_name,
    email,
    DATEDIFF(NOW(), created_at) as dias_pendente
FROM orders 
WHERE onboarding_status = 'pendente'
AND payment_status = 'paid'
HAVING dias_pendente > 3
ORDER BY dias_pendente DESC;

-- Média de tempo para conclusão
SELECT 
    AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as horas_media
FROM orders 
WHERE onboarding_status = 'concluido';
```

## 🎯 Follow-up Automatizado (Próximo Nível)

Crie um cron job para enviar lembretes:

```php
<?php
// cron/onboarding-reminder.php
// Executar diariamente: 0 10 * * *

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Buscar pedidos pendentes há 2+ dias
$stmt = $conn->prepare("
    SELECT email, customer_name, onboarding_token 
    FROM orders 
    WHERE onboarding_status IN ('pendente', 'preenchendo')
    AND payment_status = 'paid'
    AND DATEDIFF(NOW(), created_at) >= 2
    AND DATEDIFF(NOW(), created_at) <= 7
");

$stmt->execute();
$result = $stmt->get_result();

while ($order = $result->fetch_assoc()) {
    $magicLink = "https://unli.com.br/setup?token={$order['onboarding_token']}";
    
    sendReminderEmail(
        $order['email'],
        $order['customer_name'],
        $magicLink
    );
}
?>
```

## 🐛 Troubleshooting

### Token Inválido
- Verifique se o token tem 32 caracteres hexadecimais
- Confirme que o registro existe no banco: `SELECT * FROM orders WHERE onboarding_token = 'TOKEN'`

### E-mail Não Chega
- Verifique logs do servidor: `tail -f /var/log/mail.log`
- Use SMTP ao invés de `mail()` para maior confiabilidade
- Verifique se o e-mail não está na pasta de spam

### Auto-save Não Funciona
- Abra o console do navegador (F12) e verifique erros
- Confirme que a API está respondendo: teste manualmente com Postman
- Verifique permissões do banco de dados

### Logo Não Faz Upload
- Confirme que o diretório `public/uploads/logos/` existe e tem permissão 755
- Verifique limite de upload no `php.ini`: `upload_max_filesize` e `post_max_size`

## 🚀 Melhorias Futuras

- [ ] Painel admin para visualizar todos os briefings
- [ ] Integração com Slack/Discord para notificações
- [ ] Sistema de templates pré-configurados
- [ ] Preview em tempo real do site enquanto preenche
- [ ] Vídeo tutorial embutido no wizard
- [ ] Versão multi-idioma
- [ ] Analytics do funil (onde abandonam)

## 📞 Suporte

Para dúvidas sobre implementação:
- E-mail: renatom@unli.com.br
- WhatsApp: (11) 99999-9999

---

**Desenvolvido com ❤️ para maximizar conversões e eliminar fricção no onboarding de clientes.**
