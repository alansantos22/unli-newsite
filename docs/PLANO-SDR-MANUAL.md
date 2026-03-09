# Plano: Sistema SDR Manual + Feature Flag WhatsApp

> **Data:** 09/03/2026  
> **Objetivo:** Substituir o fluxo automatizado de pagamento por um fluxo manual com SDRs reais via WhatsApp, permitindo começar a vender imediatamente sem depender de gateways de pagamento.

---

## 1. Visão Geral da Mudança

### Antes (Fluxo Automatizado)
```
Cliente → Configurador/Chat IA → Checkout → Pagar.me → Webhook → Email Magic Link → Onboarding
```

### Depois (Fluxo Manual com SDR)
```
Cliente → Configurador/Chat IA → WhatsApp (mensagem personalizada) → SDR Atende → Negocia → SDR registra venda no painel → Gera link de onboarding → Envia por e-mail
```

### Feature Flag
Uma única variável de ambiente controla qual fluxo está ativo:
```env
VUE_APP_MANUAL_MODE=true   # true = WhatsApp + SDR Manual | false = Checkout Pagar.me
```

---

## 2. Componentes do Sistema

### 2.1 Feature Flag (Frontend)

**Arquivo:** `.env` / `.env.example`  
**Variável:** `VUE_APP_MANUAL_MODE`

| Valor | Comportamento |
|-------|---------------|
| `true` | Ao finalizar configuração, redireciona para WhatsApp com mensagem personalizada |
| `false` | Fluxo atual: checkout → Pagar.me → webhook → onboarding |

**Pontos de integração no frontend (onde a flag atua):**

| Componente | Comportamento com `MANUAL_MODE=true` |
|------------|---------------------------------------|
| `SDRChatAssistant.vue` | Ao terminar o chat, ao invés de abrir modal de checkout, gera link WhatsApp com resumo do pacote |
| `QuickCheckout.vue` | Botão "Pagar" vira "Falar com Especialista" → abre WhatsApp |
| `SiteConfigurator.vue` | Etapa final: ao invés de ir para pagamento, abre WhatsApp |
| `ConfiguradorPage.vue` | CTA principal muda de "Pagar" para "Conversar no WhatsApp" |

---

### 2.2 Redirecionamento para WhatsApp

**Formato do link:**
```
https://wa.me/5511XXXXXXXXX?text={mensagem_codificada}
```

**Modelo da mensagem:**
```
Olá! 👋 Acabei de montar meu site na Unli!

📦 *Pacote escolhido:* {nome_pacote}
📄 *Páginas:* {lista_paginas}
💰 *Valor estimado:* R$ {valor} (à vista) ou 12x de R$ {parcela}

Gostaria de finalizar minha compra!
```

**Implementação:** Um composable `useWhatsAppRedirect.js` que:
- Recebe os dados da configuração/pacote selecionado
- Monta a mensagem formatada
- Gera o link `wa.me` com `encodeURIComponent`
- Abre em nova aba

**Configuração do número:**
```env
VUE_APP_WHATSAPP_SDR=5511968354238
```

---

### 2.3 Painel SDR (Login + Dashboard)

**Rota:** `/sdr` (sem link visível no site — acesso direto por URL)

#### 2.3.1 Sistema de Autenticação

**Tabela MySQL:** `sdr_users`
```sql
CREATE TABLE sdr_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,       -- password_hash(PASSWORD_BCRYPT)
    whatsapp VARCHAR(20),
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME NULL
);
```

**API: `api/sdr/auth.php`**
- `POST /api/sdr/auth.php?action=login` → Recebe `{email, password}`, retorna JWT token
- `POST /api/sdr/auth.php?action=validate` → Valida JWT, retorna dados do SDR
- JWT armazenado no `sessionStorage` do navegador
- Token expira em 8 horas (jornada de trabalho)
- Middleware de autenticação reutilizável: `api/sdr/middleware.php`

**Segurança:**
- Senhas em bcrypt (`password_hash()`)
- JWT com HMAC-SHA256 (usando `JWT_SECRET` já existente no `db.config.php`)
- Rate limiting: 5 tentativas de login por 15 min por IP
- Sem registro público — SDRs criados manualmente via SQL ou script CLI

#### 2.3.2 Dashboard (Vue)

**Rota:** `/sdr/dashboard`  
**Componente:** `src/pages/SDRPanel/SDRDashboard.vue`

**Telas do Painel:**

```
/sdr                → SDRLogin.vue (tela de login)
/sdr/dashboard      → SDRDashboard.vue (menu principal)
/sdr/calculadora    → SDRCalculadora.vue (formulário de cálculo)
/sdr/clientes       → SDRClientes.vue (lista de clientes do SDR)
/sdr/cliente/:id    → SDRClienteDetalhe.vue (detalhe do cliente)
/sdr/nova-venda     → SDRNovaVenda.vue (registrar venda)
```

**Guarda de rota:** Middleware no Vue Router que:
- Verifica se existe token JWT válido no `sessionStorage`
- Se não existe ou expirou → redireciona para `/sdr`
- Injeta dados do SDR no contexto da rota

---

### 2.4 Funcionalidades do Painel SDR

#### 2.4.1 Calculadora de Preços (`/sdr/calculadora`)

- Reutiliza a mesma lógica do `SiteConfigurator.vue` / `PriceCalculator.vue`
- Diferença: **não tem etapa de checkout/pagamento**
- SDR seleciona: produto base, páginas extras, add-ons
- Sistema calcula preço em tempo real (usando `api/price.php`)
- Exibe: preço à vista, parcelado 12x, valor da parcela
- Botão "Registrar Venda com estes valores" → vai para `SDRNovaVenda.vue` com dados preenchidos

#### 2.4.2 Registrar Venda / Cadastrar Cliente (`/sdr/nova-venda`)

**Formulário:**
| Campo | Tipo | Obrigatório |
|-------|------|-------------|
| Nome do cliente | text | ✅ |
| Nome da empresa | text | ✅ |
| E-mail | email | ✅ |
| WhatsApp | tel | ✅ |
| Pacote/configuração | (vem da calculadora ou seleção manual) | ✅ |
| Método de pagamento | select (PIX, Cartão, Boleto, Outro) | ✅ |
| Status do pagamento | select (Pendente, Pago) | ✅ |
| Observações | textarea | ❌ |

**API: `api/sdr/sale.php`**
- `POST /api/sdr/sale.php?action=create` → Cria pedido + cliente
  - Salva na tabela `orders` existente (mesma estrutura)
  - Adiciona campo `sdr_id` referenciando quem vendeu
  - Se status = "Pago" → gera `onboarding_token` imediatamente
  - Retorna `order_id`, `onboarding_token`, `onboarding_link`

#### 2.4.3 Marcar como Pago + Gerar Link de Onboarding

**Na tela de detalhe do cliente (`/sdr/cliente/:id`):**
- Botão "Marcar como Pago" (se status = pendente)
- Ao clicar: `POST /api/sdr/sale.php?action=mark_paid`
  - Atualiza `payment_status` = `paid`
  - Gera `onboarding_token` (se ainda não existir)
  - Retorna link de onboarding: `https://unli.com.br/setup?token=XXX`

#### 2.4.4 Enviar Link de Onboarding por E-mail

**Na tela de detalhe do cliente:**
- Botão "Enviar E-mail de Onboarding"
- `POST /api/sdr/sale.php?action=send_onboarding_email`
  - Reutiliza o template `api/emails/onboarding-magic-link.html` já existente
  - Envia e-mail com `mail()` do PHP (já funciona na Hostinger)
  - Registra data/hora do envio

#### 2.4.5 Lista de Clientes (`/sdr/clientes`)

**Tabela com colunas:**
| Coluna | Fonte |
|--------|-------|
| Nome do cliente | `orders.customer_name` |
| Empresa | `orders.order_details->company_name` |
| E-mail | `orders.email` |
| WhatsApp | `orders.phone` |
| Pacote | `orders.order_details->product` |
| Status pagamento | `orders.payment_status` |
| Status onboarding | `orders.onboarding_status` |
| Data | `orders.created_at` |

**Filtros:**
- Por status de pagamento (Pendente / Pago)
- Por status de onboarding (Pendente / Preenchendo / Concluído)

**API: `api/sdr/clients.php`**
- `GET /api/sdr/clients.php` → Lista clientes do SDR logado
  - Filtro automático por `sdr_id` do token JWT
  - Paginação: 20 por página
  - Busca por nome/email

#### 2.4.6 Detalhe do Cliente (`/sdr/cliente/:id`)

- Dados pessoais (nome, empresa, email, WhatsApp)
- Pacote contratado (detalhamento)
- Preço negociado
- Status do pagamento (com botão de marcar como pago)
- Link de onboarding (com botão de copiar + enviar por e-mail)
- Status do onboarding
- Observações/notas do SDR

---

### 2.5 Fluxo do Configurador com `MANUAL_MODE=true`

Quando o feature flag está ativo e o cliente finaliza a configuração no site público:

```
1. Cliente configura site no formulário
2. Ao clicar "Finalizar" → abre WhatsApp com mensagem personalizada
3. SDR recebe a mensagem no WhatsApp Business
4. SDR abre /sdr/calculadora e monta o mesmo pacote
5. SDR confirma valor com o cliente
6. SDR vai em /sdr/nova-venda e cadastra a venda
7. Cliente faz pagamento (PIX/transferência/etc direto com SDR)
8. SDR marca como "Pago"
9. Sistema gera link de onboarding
10. SDR clica "Enviar E-mail" → cliente recebe link
11. Cliente preenche o onboarding (fluxo existente)
```

---

## 3. Alterações no Banco de Dados

### 3.1 Nova tabela: `sdr_users`
```sql
CREATE TABLE sdr_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    whatsapp VARCHAR(20),
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3.2 Alteração na tabela `orders`
```sql
ALTER TABLE orders 
    ADD COLUMN sdr_id INT NULL AFTER payment_id,
    ADD COLUMN company_name VARCHAR(200) NULL AFTER customer_name,
    ADD COLUMN payment_method_manual VARCHAR(50) NULL AFTER payment_status,
    ADD COLUMN sdr_notes TEXT NULL,
    ADD COLUMN onboarding_email_sent_at DATETIME NULL,
    ADD FOREIGN KEY (sdr_id) REFERENCES sdr_users(id) ON DELETE SET NULL;
```

---

## 4. Estrutura de Arquivos (Novos/Alterados)

### Novos Arquivos

```
📁 Frontend (Vue)
├── src/pages/SDRPanel/
│   ├── SDRLogin.vue              # Tela de login
│   ├── SDRDashboard.vue          # Dashboard principal
│   ├── SDRCalculadora.vue        # Calculadora de preços
│   ├── SDRClientes.vue           # Lista de clientes
│   ├── SDRClienteDetalhe.vue     # Detalhe do cliente
│   └── SDRNovaVenda.vue          # Formulário de nova venda
├── src/core/composables/
│   ├── useSDRAuth.js             # Gerencia autenticação SDR
│   └── useWhatsAppRedirect.js    # Gera link WhatsApp personalizado
│
📁 Backend (PHP)
├── api/sdr/
│   ├── auth.php                  # Login/validação JWT
│   ├── middleware.php             # Middleware de autenticação
│   ├── clients.php               # CRUD de clientes
│   ├── sale.php                  # Criar venda, marcar pago, enviar email
│   └── dashboard.php             # Estatísticas (opcional)
│
📁 Database
├── database/install-sdr.sql      # Script de criação da tabela sdr_users + ALTER orders
```

### Arquivos Alterados

```
📝 Alterados
├── .env.example                  # + VUE_APP_MANUAL_MODE, VUE_APP_WHATSAPP_SDR
├── src/router.js                 # + rotas /sdr/*
├── src/shared/Components/SDRChatAssistant.vue   # + condicional WhatsApp
├── src/shared/Components/QuickCheckout.vue      # + condicional WhatsApp
├── src/pages/ConfiguradorPage/ConfiguradorPage.vue  # + condicional WhatsApp
```

---

## 5. Fases de Implementação

### Fase 1: Infraestrutura Base
1. **Feature flag** no `.env` + `.env.example`
2. **Composable** `useWhatsAppRedirect.js`
3. **Script SQL** `install-sdr.sql` (tabela `sdr_users` + alteração `orders`)
4. **Middleware PHP** `api/sdr/middleware.php` (autenticação JWT)
5. **API de autenticação** `api/sdr/auth.php`

### Fase 2: Painel SDR (Backend)
6. **API de clientes** `api/sdr/clients.php`
7. **API de vendas** `api/sdr/sale.php` (criar, marcar pago, enviar email)

### Fase 3: Painel SDR (Frontend)
8. **SDRLogin.vue** — Tela de login
9. **SDRDashboard.vue** — Menu principal
10. **SDRCalculadora.vue** — Reutiliza lógica de pricing
11. **SDRNovaVenda.vue** — Formulário de registro de venda
12. **SDRClientes.vue** — Lista de clientes
13. **SDRClienteDetalhe.vue** — Detalhe + ações (pagar, enviar email, link)
14. **Rotas no Vue Router** — Todas as rotas `/sdr/*`

### Fase 4: Integração Feature Flag
15. **SDRChatAssistant.vue** — Condicional de WhatsApp no fim do chat
16. **QuickCheckout.vue** — Botão WhatsApp ao invés de Pagar
17. **ConfiguradorPage.vue** — CTA final condicional

### Fase 5: Testes e Deploy
18. Testar fluxo completo: configurar → WhatsApp → SDR cadastra → marca pago → envia email → onboarding
19. Criar primeiro usuário SDR no banco de dados
20. Deploy em produção

---

## 6. Considerações Técnicas

### Compatibilidade com Hostinger
- ✅ PHP 7.4+ (disponível)
- ✅ MySQL (já em uso)
- ✅ `mail()` nativo (já funciona para emails)
- ✅ Sem dependências externas novas
- ✅ JWT implementado manualmente (HMAC-SHA256) sem biblioteca — usa `hash_hmac()` nativo
- ✅ Sem necessidade de composer para JWT simples

### Segurança
- Senhas com `password_hash(PASSWORD_BCRYPT)`
- JWT com tempo de expiração (8h)
- Rate limiting no login
- SDR só vê seus próprios clientes (`WHERE sdr_id = ?`)
- CORS configurado para domínio unli.com.br
- Prepared statements em todas as queries (já padrão do projeto)

### O que NÃO muda
- API de pricing (`price.php`) — reutilizada pelo painel SDR
- Fluxo de onboarding (`/setup`, BrandConsultant, DynamicOnboardingWizard) — intocado
- Template de e-mail — reutilizado
- Tabela `orders` — mantida, apenas colunas adicionadas
- Quando `MANUAL_MODE=false` — tudo funciona exatamente como antes

---

## 7. Resumo Executivo

| Item | Detalhe |
|------|---------|
| **Nova variável de ambiente** | `VUE_APP_MANUAL_MODE`, `VUE_APP_WHATSAPP_SDR` |
| **Novos arquivos Vue** | 6 componentes de página + 2 composables |
| **Novos arquivos PHP** | 4 endpoints de API + 1 middleware |
| **Novo SQL** | 1 tabela (`sdr_users`) + ALTER na `orders` |
| **Componentes alterados** | 3-4 componentes existentes (condicional por feature flag) |
| **Fases de implementação** | 5 fases, ~20 tarefas |
| **Dependências novas** | Nenhuma — 100% PHP nativo + Vue existente |
| **Risco de regressão** | Baixo — feature flag isola completamente os dois fluxos |
