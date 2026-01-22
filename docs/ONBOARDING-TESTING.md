# 🧪 Guia de Teste do Sistema de Onboarding

Este guia mostra como testar todo o fluxo do sistema de onboarding pós-venda.

## 📋 Pré-requisitos

- ✅ Banco de dados criado (executar `database/install-onboarding.sql`)
- ✅ API configurada (`api/config.php` com credenciais corretas)
- ✅ Servidor web rodando (Apache/Nginx + PHP)
- ✅ Vue.js compilado (`npm run serve` ou `npm run build`)

## 🔧 Setup Rápido

### 1. Instalar o Banco de Dados

```bash
# MySQL
mysql -u root -p seu_banco < database/install-onboarding.sql

# Ou via phpMyAdmin:
# - Abra o phpMyAdmin
# - Selecione seu banco
# - Clique em "Importar"
# - Escolha o arquivo install-onboarding.sql
```

### 2. Verificar Configuração

Edite `api/config.php`:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'unli_newsite');  // Seu banco
define('DB_USER', 'root');          // Seu usuário
define('DB_PASS', 'sua_senha');     // Sua senha
?>
```

### 3. Criar Diretórios Necessários

```bash
# Windows (PowerShell)
New-Item -ItemType Directory -Force -Path "public\uploads\logos"
New-Item -ItemType Directory -Force -Path "api\logs"

# Linux/Mac
mkdir -p public/uploads/logos
mkdir -p api/logs
chmod 755 public/uploads/logos
chmod 755 api/logs
```

## 🧪 Teste 1: API de Validação de Token

### Teste com Postman ou cURL

O script SQL criou 3 pedidos de teste. Use o token do primeiro:

```bash
# Token de teste: a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6

# cURL (Windows PowerShell)
curl "http://localhost:8080/api/onboarding/validate.php?token=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6"

# cURL (Linux/Mac)
curl "http://localhost:8080/api/onboarding/validate.php?token=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6"
```

**Resposta Esperada:**

```json
{
  "success": true,
  "orderId": 1,
  "customerName": "João Silva",
  "email": "joao.silva@example.com",
  "status": "pendente",
  "briefing": null,
  "message": "Token válido. Pode prosseguir."
}
```

## 🧪 Teste 2: Frontend do Wizard

### Acessar o Wizard

1. Inicie o servidor de desenvolvimento:

```bash
npm run serve
```

2. Abra no navegador:

```
http://localhost:8080/setup?token=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6
```

3. Você deve ver o wizard com a **Etapa 1: Identidade Vital**

### Testar Auto-Save

1. Preencha o campo "Nome da Empresa"
2. Aguarde 2 segundos
3. Veja o indicador "💾 Salvando rascunho..." aparecer
4. Feche a aba do navegador
5. Abra novamente com o mesmo link
6. O nome da empresa deve estar preenchido!

## 🧪 Teste 3: Fluxo Completo

### Passo a Passo

1. **Etapa 1: Identidade Vital**
   - Nome: "Minha Empresa Teste"
   - Slogan: "O melhor teste do mundo"
   - Marque "Ainda não tenho logo"
   - Clique em "Próximo"

2. **Etapa 2: Contato**
   - WhatsApp: "(11) 99999-9999"
   - Instagram: "https://instagram.com/minhaempresa"
   - Endereço: "Rua Teste, 123"
   - Clique em "Próximo"

3. **Etapa 3: Conteúdo**
   - Descrição: "Nós ajudamos empresas a testar sistemas"
   - Serviço 1: "Teste Manual" / "Testamos tudo à mão"
   - Serviço 2: "Teste Automatizado" / "Testes com robôs"
   - Serviço 3: "Consultoria" / "Te ensinamos a testar"
   - Marque: "Qualidade Garantida" e "Anos de Experiência"
   - Clique em "Próximo"

4. **Etapa 4: Estilo Visual**
   - Selecione a cor azul (#0066CC)
   - Escolha "Moderno & Vibrante"
   - Clique em "Próximo"

5. **Etapa 5: Finalização**
   - Revise o resumo
   - Observações: "Este é um teste do sistema"
   - Clique em "🚀 Enviar e Iniciar Produção"

### Resultado Esperado

- ✅ Modal de sucesso aparece
- ✅ No banco de dados, o status mudou para "concluido"
- ✅ Campo `briefing_data` está preenchido com JSON completo
- ✅ Campo `completed_at` tem data/hora

### Verificar no Banco

```sql
SELECT 
  id,
  customer_name,
  onboarding_status,
  completed_at,
  JSON_EXTRACT(briefing_data, '$.companyName') as empresa
FROM orders 
WHERE onboarding_token = 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6';
```

## 🧪 Teste 4: Token Já Concluído

Use o token do pedido 3 (já concluído):

```
http://localhost:8080/setup?token=z1x2c3v4b5n6m7a8s9d0f1g2h3j4k5l6
```

**Resultado Esperado:**
- Modal de sucesso aparece imediatamente
- Mensagem: "Informações Recebidas!"
- Não permite editar novamente

## 🧪 Teste 5: Auto-Save com Rascunho Existente

Use o token do pedido 2 (com rascunho):

```
http://localhost:8080/setup?token=q2w3e4r5t6y7u8i9o0p1a2s3d4f5g6h7
```

**Resultado Esperado:**
- Campos já vêm preenchidos:
  - Nome da Empresa: "Padaria Dona Maria"
  - Slogan: "O melhor pão da região"
  - WhatsApp: "(11) 98888-7777"

## 🧪 Teste 6: Token Inválido

Acesse com um token inexistente:

```
http://localhost:8080/setup?token=tokeninvalidoparateste123456
```

**Resultado Esperado:**
- Mensagem de erro aparece
- "Token inválido ou expirado. Verifique o link no seu e-mail."

## 🧪 Teste 7: Webhook de Pagamento

### Teste Manual com cURL

```bash
# Windows PowerShell
$body = @{
  status = "paid"
  payment_id = "test_webhook_123"
  amount = 1497.00
  payment_method = "credit_card"
  customer_name = "Teste Webhook"
  customer_email = "teste@webhook.com"
  customer_phone = "(11) 88888-8888"
  plan_name = "Site Vitrine Anual"
} | ConvertTo-Json

curl -X POST http://localhost:8080/api/webhook-payment.php `
  -H "Content-Type: application/json" `
  -d $body

# Linux/Mac
curl -X POST http://localhost:8080/api/webhook-payment.php \
  -H "Content-Type: application/json" \
  -d '{
    "status": "paid",
    "payment_id": "test_webhook_123",
    "amount": 1497.00,
    "payment_method": "credit_card",
    "customer_name": "Teste Webhook",
    "customer_email": "teste@webhook.com",
    "customer_phone": "(11) 88888-8888",
    "plan_name": "Site Vitrine Anual"
  }'
```

**Resultado Esperado:**

```json
{
  "success": true,
  "processed": true
}
```

**Verificar no Banco:**

```sql
SELECT * FROM orders WHERE payment_id = 'test_webhook_123';
```

Deve ter:
- ✅ Novo pedido criado
- ✅ Status: `paid`
- ✅ Token gerado automaticamente
- ✅ Status de onboarding: `pendente`

**Verificar E-mail (se configurado):**
- Deve ter enviado e-mail para `teste@webhook.com`

## 🧪 Teste 8: Upload de Logo

1. Acesse o wizard com qualquer token pendente
2. Na Etapa 1, clique em "Clique para fazer upload"
3. Escolha uma imagem PNG ou JPG (menos de 5MB)
4. Veja o preview aparecer
5. Continue até o fim e envie
6. Verifique se a imagem foi salva:

```bash
# Windows
dir public\uploads\logos

# Linux/Mac
ls -la public/uploads/logos
```

**Verificar no Banco:**

```sql
SELECT 
  id,
  JSON_EXTRACT(briefing_data, '$.logoUrl') as logo
FROM orders 
WHERE onboarding_status = 'concluido'
ORDER BY completed_at DESC
LIMIT 1;
```

## 🧪 Teste 9: Responsividade Mobile

1. Abra o wizard no navegador
2. Pressione F12 (DevTools)
3. Clique no ícone de dispositivo móvel (ou Ctrl+Shift+M)
4. Teste com:
   - iPhone 12 Pro (390x844)
   - Samsung Galaxy S20 (360x800)
   - iPad (768x1024)

**Verificar:**
- ✅ Layout se adapta ao tamanho da tela
- ✅ Botões ficam empilhados verticalmente
- ✅ Formulário é scrollável
- ✅ Color picker funciona no touch

## 🧪 Teste 10: Performance e Segurança

### Teste de Carga

```bash
# Instalar Apache Bench (se não tiver)
# Windows: baixar do site oficial
# Linux: sudo apt-get install apache2-utils

# Teste de 100 requisições, 10 simultâneas
ab -n 100 -c 10 "http://localhost:8080/api/onboarding/validate.php?token=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6"
```

**Resultado Esperado:**
- Tempo médio < 100ms
- 0 requisições falhadas

### Teste de SQL Injection

Tente acessar com token malicioso:

```
http://localhost:8080/setup?token='; DROP TABLE orders; --
```

**Resultado Esperado:**
- ✅ Erro "Token inválido"
- ✅ Tabela `orders` intacta (não foi dropada)
- ✅ Prepared statements protegem contra injection

## ✅ Checklist Final

Antes de ir para produção, verifique:

- [ ] Banco de dados criado e tabela `orders` existe
- [ ] `api/config.php` com credenciais corretas
- [ ] Diretório `public/uploads/logos` existe e tem permissão 755
- [ ] Diretório `api/logs` existe
- [ ] API de validação responde corretamente
- [ ] Frontend carrega sem erros (F12 Console limpo)
- [ ] Auto-save funciona
- [ ] Upload de logo funciona
- [ ] E-mail está configurado (SMTP ou mail())
- [ ] Webhook de pagamento está integrado
- [ ] HTTPS configurado (obrigatório em produção!)
- [ ] Domínio correto no e-mail (substituir unli.com.br)
- [ ] Testes em mobile funcionando
- [ ] Backup do banco de dados configurado

## 🐛 Problemas Comuns

### Erro: "Token inválido"
- Verifique se o token tem 32 caracteres hexadecimais
- Confirme que existe no banco: `SELECT * FROM orders WHERE onboarding_token = 'SEU_TOKEN'`

### Wizard não carrega
- Abra F12 e veja erros no console
- Verifique se a rota `/setup` está configurada no `router.js`
- Confirme que o servidor Vue está rodando: `npm run serve`

### Auto-save não funciona
- Veja erros no F12 Network tab
- Teste a API manualmente com Postman
- Verifique permissões do banco de dados (UPDATE)

### E-mail não chega
- Verifique se a função `mail()` está habilitada no PHP
- Teste SMTP com PHPMailer para maior confiabilidade
- Olhe na pasta de spam
- Veja logs: `tail -f /var/log/mail.log` (Linux)

### Logo não faz upload
- Verifique se `public/uploads/logos` existe
- Confirme permissões: `chmod 755 public/uploads/logos`
- Veja limites no `php.ini`: `upload_max_filesize` e `post_max_size`

## 🚀 Próximos Passos

Após todos os testes passarem:

1. **Deploy em produção**
   - Configure HTTPS (obrigatório!)
   - Altere URLs no código (localhost → seu-dominio.com)
   - Configure SMTP para e-mails

2. **Integrar com checkout real**
   - Configure webhook no gateway de pagamento
   - Teste com pagamento real (valor baixo)

3. **Monitoramento**
   - Configure logs
   - Crie dashboard para visualizar conversões
   - Implemente alertas para onboardings pendentes

4. **Follow-up automatizado**
   - Crie cron job para enviar lembretes
   - E-mails nos dias 2, 5 e 7 após pagamento

---

**🎉 Parabéns! Se todos os testes passaram, seu sistema está pronto para maximizar conversões!**
