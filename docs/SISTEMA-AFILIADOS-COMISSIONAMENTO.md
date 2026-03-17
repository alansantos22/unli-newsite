# Sistema de Afiliados e Comissionamento — Documentação Completa

> Última atualização: Março 2026

---

## Índice

1. [Visão Geral](#1-visão-geral)
2. [Arquitetura do Sistema](#2-arquitetura-do-sistema)
3. [Banco de Dados](#3-banco-de-dados)
4. [Sistema de Ligas (Tiers) e Comissões](#4-sistema-de-ligas-tiers-e-comissões)
5. [Fluxo de Cadastro do Afiliado](#5-fluxo-de-cadastro-do-afiliado)
6. [Links de Indicação e Rastreamento](#6-links-de-indicação-e-rastreamento)
7. [Cálculo de Comissão](#7-cálculo-de-comissão)
8. [Fluxo de Pagamento de Comissões](#8-fluxo-de-pagamento-de-comissões)
9. [Relação SDR × Afiliado](#9-relação-sdr--afiliado)
10. [API — Endpoints do Afiliado](#10-api--endpoints-do-afiliado)
11. [API — Endpoints do SDR](#11-api--endpoints-do-sdr)
12. [Painel do Afiliado (Frontend)](#12-painel-do-afiliado-frontend)
13. [Painel do SDR (Frontend)](#13-painel-do-sdr-frontend)
14. [Autenticação e Segurança](#14-autenticação-e-segurança)
15. [Configurações e Valores](#15-configurações-e-valores)
16. [Migrations e Setup do Banco](#16-migrations-e-setup-do-banco)
17. [Diagramas de Fluxo](#17-diagramas-de-fluxo)

---

## 1. Visão Geral

O sistema de afiliados da Unli permite que parceiros externos divulguem os serviços da empresa através de **links de indicação personalizados**. Quando um cliente realiza uma compra originada de um link de afiliado, o afiliado recebe uma **comissão percentual** sobre o valor da venda.

O sistema trabalha em conjunto com o **Painel SDR** (Sales Development Representative), que é usado pela equipe interna de vendas para registrar vendas manuais e, opcionalmente, vincular um afiliado a essas vendas.

### Principais funcionalidades

- Cadastro self-service de afiliados
- 8 ligas (tiers) com comissões progressivas de **5% a 15%**
- Rastreamento de indicações via cookie (30 dias de validade)
- Dashboard completo com estatísticas, ranking e simulador
- Painel SDR para registro manual de vendas
- Pagamento de comissões via PIX
- Autenticação JWT com rate limiting

---

## 2. Arquitetura do Sistema

```
┌──────────────────────────────────────────────────────────────────┐
│                        FRONTEND (Vue.js)                         │
├──────────────────┬───────────────────┬───────────────────────────┤
│ Painel Afiliado  │   Painel SDR      │   Site Público            │
│ /afiliados/*     │   /sdr/*          │   / (com ?ref=xxx)        │
└───────┬──────────┴────────┬──────────┴────────────┬──────────────┘
        │                   │                       │
        │ JWT Auth          │ JWT Auth              │ Cookie unli_aff
        ▼                   ▼                       ▼
┌──────────────────────────────────────────────────────────────────┐
│                          API (PHP)                                │
├──────────────────┬───────────────────┬───────────────────────────┤
│ api/affiliate/   │   api/sdr/        │   api/order_create.php    │
│  auth.php        │    auth.php       │   (checkout padrão)       │
│  dashboard.php   │    sale.php       │                           │
│  settings.php    │    clients.php    │                           │
│  referral.php    │                   │                           │
│  middleware.php   │    middleware.php  │                           │
└───────┬──────────┴────────┬──────────┴────────────┬──────────────┘
        │                   │                       │
        ▼                   ▼                       ▼
┌──────────────────────────────────────────────────────────────────┐
│                      MySQL (utf8mb4)                             │
│  affiliate_users  │  affiliate_referrals  │  affiliate_tier_history│
│  affiliate_quarterly_sales  │  orders (com campos de afiliado)   │
│  sdr_users                                                       │
└──────────────────────────────────────────────────────────────────┘
```

---

## 3. Banco de Dados

### 3.1 `affiliate_users` — Dados do Afiliado

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id` | INT(11) PK AUTO_INCREMENT | Identificador único |
| `first_name` | VARCHAR(80) NOT NULL | Primeiro nome |
| `last_name` | VARCHAR(80) NOT NULL | Sobrenome |
| `email` | VARCHAR(150) NOT NULL UNIQUE | E-mail (login) |
| `password_hash` | VARCHAR(255) NOT NULL | Senha criptografada (bcrypt) |
| `whatsapp` | VARCHAR(20) NOT NULL | Número do WhatsApp |
| `pix_key` | VARCHAR(150) DEFAULT NULL | Chave PIX para recebimento |
| `affiliate_hash` | VARCHAR(32) NOT NULL UNIQUE | Código de indicação (16 caracteres hexadecimais) |
| `tier` | VARCHAR(30) NOT NULL DEFAULT 'bronze_1' | Liga atual do afiliado |
| `commission_rate` | DECIMAL(5,2) NOT NULL DEFAULT 8.00 | Percentual de comissão atual |
| `total_sales_amount` | DECIMAL(12,2) NOT NULL DEFAULT 0.00 | Total acumulado de vendas |
| `is_active` | TINYINT(1) DEFAULT 1 | Afiliado ativo/inativo |
| `created_at` | DATETIME | Data de criação |
| `last_login` | DATETIME | Último login |
| `last_tier_check` | DATETIME | Última verificação de liga |

### 3.2 `affiliate_referrals` — Indicações e Vendas

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id` | INT(11) PK AUTO_INCREMENT | Identificador único |
| `affiliate_id` | INT(11) NOT NULL FK | Referência ao afiliado |
| `order_id` | INT(11) DEFAULT NULL FK | Referência ao pedido |
| `lead_name` | VARCHAR(200) | Nome do lead |
| `lead_email` | VARCHAR(150) | E-mail do lead |
| `lead_phone` | VARCHAR(30) | Telefone do lead |
| `status` | ENUM('lead','contacted','negotiating','closed','onboarding','completed','lost','pending') | Status da indicação |
| `sale_amount` | DECIMAL(12,2) | Valor da venda |
| `order_amount` | DECIMAL(12,2) | Valor do pedido |
| `commission_rate` | DECIMAL(5,2) | Taxa de comissão no momento da venda |
| `commission_amount` | DECIMAL(12,2) | Valor da comissão calculada |
| `commission_paid` | TINYINT(1) DEFAULT 0 | 0 = pendente, 1 = paga |
| `commission_paid_at` | DATETIME | Data do pagamento da comissão |
| `source_page` | VARCHAR(100) | Página de origem (home, consultoria, site-vitrine, sdr) |
| `notes` | TEXT | Observações internas |
| `created_at` | DATETIME | Data de criação |
| `updated_at` | DATETIME | Última atualização |

### 3.3 `affiliate_tier_history` — Histórico de Ligas

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id` | INT(11) PK AUTO_INCREMENT | Identificador único |
| `affiliate_id` | INT(11) NOT NULL FK | Referência ao afiliado |
| `old_tier` | VARCHAR(30) | Liga anterior |
| `new_tier` | VARCHAR(30) | Nova liga |
| `reason` | VARCHAR(200) | Motivo da mudança |
| `sales_amount_at_change` | DECIMAL(12,2) | Total de vendas no momento |
| `created_at` | DATETIME | Data da mudança |

### 3.4 `affiliate_quarterly_sales` — Vendas Trimestrais (Manutenção de Liga)

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id` | INT(11) PK AUTO_INCREMENT | Identificador único |
| `affiliate_id` | INT(11) NOT NULL FK | Referência ao afiliado |
| `quarter_start` | DATE | Início do trimestre |
| `quarter_end` | DATE | Fim do trimestre |
| `total_amount` | DECIMAL(12,2) NOT NULL DEFAULT 0.00 | Total vendido no período |
| `created_at` | DATETIME | Data de criação |

### 3.5 `orders` — Campos de Afiliado Adicionados

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `affiliate_id` | INT(11) DEFAULT NULL FK | ID do afiliado vinculado |
| `affiliate_hash` | VARCHAR(32) DEFAULT NULL | Hash do afiliado (referência) |

### 3.6 `sdr_users` — Equipe de Vendas

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id` | INT(11) PK AUTO_INCREMENT | Identificador único |
| `name` | VARCHAR(100) NOT NULL | Nome completo |
| `email` | VARCHAR(150) NOT NULL UNIQUE | E-mail (login) |
| `password_hash` | VARCHAR(255) NOT NULL | Senha criptografada (bcrypt) |
| `whatsapp` | VARCHAR(20) | WhatsApp |
| `is_active` | TINYINT(1) DEFAULT 1 | Ativo/inativo |
| `created_at` | DATETIME | Data de criação |
| `last_login` | DATETIME | Último login |

---

## 4. Sistema de Ligas (Tiers) e Comissões

O sistema de comissionamento é baseado em **8 ligas** (tiers). Cada liga oferece uma taxa de comissão maior, incentivando o afiliado a gerar mais vendas.

### Tabela de Ligas

| Liga | Ícone | Vendas Mínimas (acumulado) | Comissão |
|------|-------|---------------------------|----------|
| **Bronze 1** | 🥉 | R$ 0 | **5,00%** |
| **Bronze 2** | 🥉 | R$ 800 | **6,25%** |
| **Prata 1** | 🥈 | R$ 3.000 | **7,50%** |
| **Prata 2** | 🥈 | R$ 6.500 | **8,75%** |
| **Ouro 1** | 🥇 | R$ 12.000 | **10,00%** |
| **Ouro 2** | 🥇 | R$ 20.000 | **11,25%** |
| **Diamante 1** | 💎 | R$ 35.000 | **12,50%** |
| **Diamante 2** | 💎 | R$ 50.000 | **15,00%** |

### Regras de Progressão

1. **Progressão automática:** A liga é calculada com base no campo `total_sales_amount` do afiliado. Ao atingir o valor mínimo de uma liga superior, a promoção é automática.
2. **Incremento fixo:** Cada liga aumenta a comissão em **1,25 pontos percentuais**.
3. **Manutenção:** O afiliado precisa manter um volume mínimo de vendas nos últimos **3 meses** para permanecer na liga. Caso contrário, recebe um alerta de "em risco".
4. **Histórico:** Toda mudança de liga é registrada na tabela `affiliate_tier_history`.

### Exemplo de Progressão

```
Afiliado novo → Bronze 1 (5%)
  ↓ vende R$ 800 acumulados
Bronze 2 (6,25%)
  ↓ vende R$ 3.000 acumulados
Prata 1 (7,50%)
  ↓ vende R$ 6.500 acumulados
Prata 2 (8,75%)
  ... e assim por diante
```

---

## 5. Fluxo de Cadastro do Afiliado

```
1. ACESSO: Visitante acessa /afiliados/registro
   │
2. FORMULÁRIO: Preenche os campos obrigatórios
   ├─ Nome e Sobrenome (mín. 2 caracteres)
   ├─ E-mail (único no sistema)
   ├─ WhatsApp (mín. 10 dígitos)
   ├─ Chave PIX (opcional — CPF, e-mail, celular ou chave aleatória)
   ├─ Senha (mín. 6 caracteres)
   └─ Confirmação de senha
   │
3. VALIDAÇÕES (frontend):
   ├─ Formato de nome (letras unicode, espaços, hífens)
   ├─ Formato de e-mail (padrão RFC)
   ├─ Formato de WhatsApp (11+ dígitos)
   ├─ Senhas coincidem + tamanho mínimo
   └─ Rate limit: 5 tentativas falhas = bloqueio de 15min por IP
   │
4. ENVIO: POST /api/affiliate/auth.php?action=register
   │
5. PROCESSAMENTO (backend):
   ├─ Verifica se e-mail já existe
   ├─ Gera affiliate_hash único (16 caracteres hexadecimais)
   ├─ Define liga inicial: bronze_1 (5% comissão)
   ├─ Criptografa senha com bcrypt
   ├─ Insere registro em affiliate_users
   ├─ Registra mudança de liga em affiliate_tier_history
   └─ Retorna token JWT + dados do usuário
   │
6. REDIRECIONAMENTO: /afiliados/dashboard
   └─ Dashboard com estatísticas, links e simulador
```

---

## 6. Links de Indicação e Rastreamento

### Como funcionam os links

Cada afiliado possui um **código único** (`affiliate_hash`) de 16 caracteres hexadecimais. Esse código é usado como parâmetro `?ref=` nas URLs do site.

### Links disponíveis

| Página | URL de Indicação |
|--------|-----------------|
| Página Inicial | `https://unli.com.br/?ref={hash}` |
| Consultoria Gamificação | `https://unli.com.br/consultoria-gamificacao?ref={hash}` |
| Site Vitrine | `https://unli.com.br/site-vitrine?ref={hash}` |
| Configurador | `https://unli.com.br/configurador?ref={hash}` |

### Mecanismo de Rastreamento (Cookie)

O rastreamento é feito pelo composable `useAffiliateTracking.js`:

```
1. Visitante clica no link de indicação
   Ex: https://unli.com.br/?ref=a1b2c3d4e5f6g7h8
   │
2. Sistema detecta parâmetro ?ref= na URL
   │
3. Salva cookie "unli_aff" com o hash
   ├─ Nome: unli_aff
   ├─ Valor: a1b2c3d4e5f6g7h8
   ├─ Validade: 30 dias
   ├─ Path: /
   ├─ Secure: true
   └─ SameSite: Lax
   │
4. Visitante navega pelo site normalmente
   │
5. Ao realizar uma compra (checkout ou via SDR):
   ├─ Cookie é recuperado via getAffiliateRef()
   ├─ Hash é validado no banco: SELECT FROM affiliate_users WHERE affiliate_hash = ?
   ├─ Pedido (orders) recebe affiliate_id e affiliate_hash
   ├─ Registro criado em affiliate_referrals
   └─ Comissão calculada automaticamente
```

### Prioridade de Atribuição

- O **último clique** (last-click attribution) prevalece — se o visitante clicar em um novo link de afiliado, o cookie é sobrescrito.
- O cookie dura **30 dias**. Após esse período, a indicação expira.

---

## 7. Cálculo de Comissão

### Fórmula

```
comissão = valor_da_venda × (taxa_de_comissão / 100)
```

### Exemplo

| Cenário | Valor da Venda | Liga | Taxa | Comissão |
|---------|---------------|------|------|----------|
| Afiliado Bronze 1 vende site | R$ 2.000 | Bronze 1 | 5,00% | **R$ 100,00** |
| Afiliado Prata 1 vende consultoria | R$ 5.000 | Prata 1 | 7,50% | **R$ 375,00** |
| Afiliado Ouro 2 vende pacote | R$ 10.000 | Ouro 2 | 11,25% | **R$ 1.125,00** |
| Afiliado Diamante 2 vende premium | R$ 15.000 | Diamante 2 | 15,00% | **R$ 2.250,00** |

### Momento do Cálculo

A comissão é calculada **no momento da criação da venda**, utilizando a taxa de comissão vigente do afiliado. A taxa é "congelada" na `affiliate_referrals.commission_rate`, garantindo que mudanças futuras de liga não afetem comissões já registradas.

### Função de Cálculo (Backend)

```php
// api/affiliate/middleware.php
function calculate_tier($totalSalesAmount) {
    $tiers = get_tier_config();
    $currentTier = 'bronze_1';
    foreach ($tiers as $tierKey => $tierData) {
        if ($totalSalesAmount >= $tierData['min_sales']) {
            $currentTier = $tierKey;
        }
    }
    return $currentTier;
}
```

---

## 8. Fluxo de Pagamento de Comissões

### Ciclo de Vida de uma Comissão

```
VENDA CRIADA (via checkout ou SDR)
  │
  ├─ affiliate_referrals.commission_amount = valor calculado
  ├─ affiliate_referrals.commission_paid = 0 (pendente)
  └─ Status: PENDENTE
  │
  ▼
PEDIDO MARCADO COMO PAGO
  │
  ├─ POST /api/sdr/sale.php?action=mark_paid
  ├─ payment_status = 'paid'
  └─ Comissão continua pendente (aguarda ciclo de pagamento)
  │
  ▼
PAGAMENTO DA COMISSÃO (processo manual)
  │
  ├─ Admin/financeiro acessa dados das comissões pendentes
  ├─ Realiza transferência via PIX (pix_key do afiliado)
  ├─ Atualiza no banco:
  │   ├─ commission_paid = 1
  │   └─ commission_paid_at = data atual
  └─ Status: PAGA ✓
```

### Prazos de Pagamento

- **Início:** A partir do **5º dia útil** do mês
- **Prazo máximo:** Até **30 dias úteis** após a confirmação do pagamento da venda
- **Método:** Transferência via **PIX** para a chave cadastrada pelo afiliado

### Visualização no Dashboard

O afiliado pode acompanhar no dashboard:
- **Comissão Pendente:** Soma de `commission_amount` onde `commission_paid = 0`
- **Comissão Paga:** Soma de `commission_amount` onde `commission_paid = 1`

---

## 9. Relação SDR × Afiliado

O **SDR** (Sales Development Representative) é a equipe interna de vendas. O **Afiliado** é um parceiro externo. Eles se complementam:

### Diferenças

| Aspecto | Afiliado | SDR |
|---------|----------|-----|
| **Tipo** | Parceiro externo | Funcionário interno |
| **Painel** | `/afiliados/dashboard` | `/sdr/dashboard` |
| **Cria pedidos?** | Não — apenas indica | Sim — registra vendas manualmente |
| **Recebe comissão?** | Sim — percentual sobre vendas indicadas | Não — é a equipe de vendas |
| **Vincula afiliado?** | N/A | Sim — pode vincular afiliado a uma venda |
| **Progressão de liga?** | Sim — automática | N/A |
| **Pagamento** | PIX | Salário (fora do sistema) |

### Cenários de Vinculação

#### Cenário 1: Cliente veio por link de afiliado

```
Cliente clica em link → Cookie salvo (30 dias) → Navega no site
→ SDR registra venda → Cookie é recuperado automaticamente
→ Afiliado vinculado à venda → Comissão calculada
```

#### Cenário 2: SDR informa o código do afiliado manualmente

```
SDR recebe lead indicado por afiliado → Abre "Nova Venda"
→ Preenche campo "Código do Afiliado" → Sistema valida o hash
→ Afiliado vinculado à venda → Comissão calculada
```

#### Cenário 3: Vinculação posterior

```
Pedido já existe sem afiliado → SDR usa endpoint de attach
→ POST /api/affiliate/referral.php?action=attach
→ Envia order_id + affiliate_hash → Afiliado vinculado retroativamente
```

---

## 10. API — Endpoints do Afiliado

### 10.1 Autenticação (`api/affiliate/auth.php`)

#### Registro

```
POST /api/affiliate/auth.php?action=register
Content-Type: application/json

{
  "first_name": "João",
  "last_name": "Silva",
  "email": "joao@email.com",
  "password": "senha123",
  "whatsapp": "11999998888",
  "pix_key": "joao@email.com"
}

Resposta (200):
{
  "ok": true,
  "token": "eyJ...",
  "user": {
    "id": 1,
    "first_name": "João",
    "last_name": "Silva",
    "email": "joao@email.com",
    "affiliate_hash": "a1b2c3d4e5f6g7h8",
    "tier": "bronze_1",
    "tier_name": "Liga Bronze 1",
    "commission_rate": 5.00
  }
}
```

#### Login

```
POST /api/affiliate/auth.php?action=login
Content-Type: application/json

{
  "email": "joao@email.com",
  "password": "senha123"
}

Resposta (200):
{
  "ok": true,
  "token": "eyJ...",
  "user": { ... }
}
```

#### Validar Token

```
POST /api/affiliate/auth.php?action=validate
Authorization: Bearer eyJ...

Resposta (200):
{
  "ok": true,
  "user": { ... }
}
```

### 10.2 Dashboard (`api/affiliate/dashboard.php`)

Todos os endpoints requerem `Authorization: Bearer {token}`.

#### Resumo do Dashboard

```
GET /api/affiliate/dashboard.php

Resposta:
{
  "ok": true,
  "affiliate": {
    "id": 1,
    "first_name": "João",
    "last_name": "Silva",
    "hash": "a1b2c3d4e5f6g7h8",
    "tier": "prata_1",
    "tier_name": "Liga Prata 1",
    "tier_icon": "🥈",
    "commission_rate": 7.5
  },
  "stats": {
    "total_referrals": 15,
    "total_sales_closed": 8,
    "total_sales_amount": 5200.00,
    "pending_commission": 435.00,
    "paid_commission": 2100.00,
    "last_3_months_sales": 2800.00
  },
  "tier_status": {
    "at_risk": false,
    "message": null,
    "next_tier": {
      "key": "prata_2",
      "name": "Liga Prata 2",
      "icon": "🥈",
      "min_sales": 6500,
      "commission": 8.75,
      "amount_needed": 1300.00
    }
  },
  "links": [
    {
      "name": "Página Inicial",
      "url": "https://unli.com.br/?ref=a1b2c3d4e5f6g7h8",
      "page": "home"
    }
  ]
}
```

#### Listar Indicações

```
GET /api/affiliate/dashboard.php?action=referrals&page=1&status=lead

Resposta:
{
  "ok": true,
  "referrals": [ ... ],
  "pagination": {
    "page": 1,
    "per_page": 10,
    "total": 15,
    "total_pages": 2
  }
}
```

#### Ranking de Afiliados

```
GET /api/affiliate/dashboard.php?action=ranking

Resposta:
{
  "ok": true,
  "ranking": [
    { "position": 1, "name": "Maria F.", "tier": "ouro_1", "total_sales": 15000 },
    ...
  ]
}
```

#### Configuração de Ligas

```
GET /api/affiliate/dashboard.php?action=tiers

Resposta:
{
  "ok": true,
  "tiers": [
    { "key": "bronze_1", "name": "O Recruta", "min_sales": 0, "commission": 8.00, "icon": "🥉" },
    ...
  ]
}
```

### 10.3 Configurações (`api/affiliate/settings.php`)

#### Atualizar Perfil

```
POST /api/affiliate/settings.php?action=update_profile
Authorization: Bearer {token}
Content-Type: application/json

{
  "whatsapp": "11999997777",
  "pix_key": "11999997777"
}
```

#### Alterar Senha

```
POST /api/affiliate/settings.php?action=change_password
Authorization: Bearer {token}
Content-Type: application/json

{
  "current_password": "senhaAntiga",
  "new_password": "senhaNova123"
}
```

### 10.4 Indicações (`api/affiliate/referral.php`)

#### Validar Hash do Afiliado (público)

```
GET /api/affiliate/referral.php?action=validate_hash&hash=a1b2c3d4e5f6g7h8

Resposta:
{
  "ok": true,
  "valid": true,
  "affiliate_name": "João S."
}
```

#### Vincular Afiliado a Pedido (requer auth SDR)

```
POST /api/affiliate/referral.php?action=attach
Authorization: Bearer {sdr_token}
Content-Type: application/json

{
  "order_id": 123,
  "affiliate_hash": "a1b2c3d4e5f6g7h8"
}
```

---

## 11. API — Endpoints do SDR

### 11.1 Autenticação (`api/sdr/auth.php`)

#### Login

```
POST /api/sdr/auth.php?action=login
Content-Type: application/json

{
  "email": "vendedor@unli.com.br",
  "password": "senha123"
}

Resposta:
{
  "ok": true,
  "token": "eyJ...",
  "user": { "id": 1, "name": "Vendedor", "email": "vendedor@unli.com.br" }
}
```

### 11.2 Gerenciamento de Vendas (`api/sdr/sale.php`)

#### Registrar Nova Venda

```
POST /api/sdr/sale.php?action=create
Authorization: Bearer {token}
Content-Type: application/json

{
  "customer_name": "Empresa XYZ",
  "company_name": "XYZ Ltda",
  "email": "contato@xyz.com",
  "whatsapp": "11999990000",
  "selection": { ... },
  "total_amount": 5000.00,
  "payment_status": "pending",
  "affiliate_hash": "a1b2c3d4e5f6g7h8",
  "notes": "Cliente veio por indicação do João"
}
```

Se `affiliate_hash` for informado e válido:
- Pedido é criado com `affiliate_id` preenchido
- Registro em `affiliate_referrals` é criado automaticamente
- Comissão calculada com base na taxa atual do afiliado

#### Marcar Pedido como Pago

```
POST /api/sdr/sale.php?action=mark_paid
Authorization: Bearer {token}

{
  "order_id": 123,
  "payment_method_manual": "pix"
}
```

#### Enviar E-mail de Onboarding

```
POST /api/sdr/sale.php?action=send_onboarding_email
Authorization: Bearer {token}

{
  "order_id": 123
}
```

#### Atualizar Observações

```
POST /api/sdr/sale.php?action=update_notes
Authorization: Bearer {token}

{
  "order_id": 123,
  "notes": "Cliente confirmou pagamento via PIX"
}
```

### 11.3 Clientes (`api/sdr/clients.php`)

#### Listar Clientes

```
GET /api/sdr/clients.php?page=1&search=xyz&payment_status=paid
Authorization: Bearer {token}

Resposta:
{
  "ok": true,
  "clients": [ ... ],
  "pagination": { "page": 1, "per_page": 10, "total": 25 }
}
```

#### Detalhes do Cliente

```
GET /api/sdr/clients.php?id=123
Authorization: Bearer {token}

Resposta:
{
  "ok": true,
  "client": {
    "id": 123,
    "customer_name": "Empresa XYZ",
    "total_amount": 5000.00,
    "payment_status": "paid",
    "affiliate_name": "João Silva",
    ...
  }
}
```

---

## 12. Painel do Afiliado (Frontend)

### Rotas

| Rota | Componente | Descrição |
|------|-----------|-----------|
| `/afiliados` | `AffiliateLogin.vue` | Tela de login |
| `/afiliados/registro` | `AffiliateRegister.vue` | Cadastro de novo afiliado |
| `/afiliados/dashboard` | `AffiliateDashboard.vue` | Dashboard principal |

### Abas do Dashboard

#### 1. Dashboard (Visão Geral)
- Boas-vindas com nome e badge da liga
- Alerta de risco (se liga em perigo de rebaixamento)
- Barra de progresso para próxima liga
- 6 cards de estatísticas:
  - Total de indicações
  - Vendas fechadas
  - Total vendido (R$)
  - Comissão pendente (R$)
  - Comissão paga (R$)
  - Vendas dos últimos 3 meses (R$)
- Informações sobre pagamento

#### 2. Indicações
- Lista filtrada por status (lead, contacted, negotiating, closed, onboarding, completed, lost)
- Paginação (10 por página)
- Exibe: Nome, Status, E-mail, Página de origem, Data, Valor da venda, Comissão (com badge "Paga" se aplicável)

#### 3. Meus Links
- 4 links de indicação pré-gerados com o hash do afiliado
- Botão de copiar para cada link

#### 4. Simulador
- Tabela de comissões: 8 ligas × valores de exemplo
- Simulador personalizado: insira um valor e veja a comissão por liga
- Destaque na liga atual do afiliado

#### 5. Ranking
- Top 50 afiliados por total de vendas
- Medalhas para os 3 primeiros (🥇🥈🥉)
- Posição do afiliado logado destacada

#### 6. Ligas
- Cards de todas as 8 ligas com ícones, % de comissão e valor mínimo
- Liga atual destacada
- Explicação das regras de progressão

---

## 13. Painel do SDR (Frontend)

### Rotas

| Rota | Componente | Descrição |
|------|-----------|-----------|
| `/sdr` | `SDRLogin.vue` | Tela de login |
| `/sdr/dashboard` | `SDRDashboard.vue` | Dashboard principal |
| `/sdr/calculadora` | `SDRCalculadora.vue` | Calculadora de preços |
| `/sdr/nova-venda` | `SDRNovaVenda.vue` | Registrar nova venda |
| `/sdr/clientes` | `SDRClientes.vue` | Lista de clientes |
| `/sdr/cliente/:id` | `SDRClienteDetalhe.vue` | Detalhe do cliente |
| `/sdr/configuracoes` | `SDRConfiguracoes.vue` | Configurações do perfil |

### Funcionalidades Principais

- **Nova Venda:** Formulário para registrar vendas com campo opcional de código de afiliado
- **Clientes:** Lista paginada com busca e filtros por status de pagamento/onboarding
- **Detalhe do Cliente:** Visualização completa, botões para marcar como pago e enviar e-mail de onboarding
- **Calculadora:** Simulador de preços similar ao configurador público

---

## 14. Autenticação e Segurança

### JWT (JSON Web Token)

| Parâmetro | Valor |
|-----------|-------|
| Algoritmo | HS256 |
| Duração | 8 horas (28.800 segundos) |
| Secret | Definido em `api/db.config.php` (`JWT_SECRET`) |
| Armazenamento (frontend) | `sessionStorage` (limpo ao fechar navegador) |
| Escopo | Tokens separados para Afiliado e SDR |

### Rate Limiting

| Parâmetro | Valor |
|-----------|-------|
| Tipo | Baseado em IP |
| Máx. tentativas | 5 por 15 minutos |
| Duração do bloqueio | 15 minutos |
| Armazenamento | Arquivos temporários em `/tmp/` |
| Padrão | `/tmp/aff_login_{md5(ip)}.json` e `/tmp/sdr_login_{md5(ip)}.json` |

### Senhas

| Parâmetro | Valor |
|-----------|-------|
| Hash | bcrypt (`PASSWORD_BCRYPT`) |
| Cost | 10 rounds (padrão) |
| Verificação | `password_verify()` |
| Tamanho mínimo | 6 caracteres |

### CORS

- Configurado via `api/lib/cors.php`
- Headers aplicados em todas as respostas da API

---

## 15. Configurações e Valores

### Comissões (`api/affiliate/middleware.php`)

```php
$tiers = [
    'bronze_1'   => ['name' => 'O Recruta',       'icon' => '🥉', 'min_sales' => 0,     'commission' => 8.00],
    'bronze_2'   => ['name' => 'O Sobrevivente',   'icon' => '🥉', 'min_sales' => 1000,  'commission' => 9.00],
    'prata_1'    => ['name' => 'O Especialista',   'icon' => '🥈', 'min_sales' => 4000,  'commission' => 11.00],
    'prata_2'    => ['name' => 'O Estrategista',   'icon' => '🥈', 'min_sales' => 8000,  'commission' => 12.00],
    'ouro_1'     => ['name' => 'O Elite',          'icon' => '🥇', 'min_sales' => 15000, 'commission' => 13.50],
    'ouro_2'     => ['name' => 'O Influenciador',  'icon' => '🥇', 'min_sales' => 30000, 'commission' => 14.00],
    'diamante_1' => ['name' => 'O Mestre',         'icon' => '💎', 'min_sales' => 50000, 'commission' => 14.50],
    'diamante_2' => ['name' => 'A Lenda',          'icon' => '💎', 'min_sales' => 80000, 'commission' => 15.00],
];
```

### Cookie de Rastreamento (`src/core/composables/useAffiliateTracking.js`)

| Parâmetro | Valor |
|-----------|-------|
| Nome | `unli_aff` |
| Duração | 30 dias |
| Secure | `true` |
| SameSite | `Lax` |
| Path | `/` |

### Token JWT

| Parâmetro | Valor |
|-----------|-------|
| Expiração | 28.800 segundos (8 horas) |
| Algoritmo | HS256 |
| Secret | Variável `JWT_SECRET` em `db.config.php` |

---

## 16. Migrations e Setup do Banco

### Migration de Afiliados (`migrate-affiliates.php`)

```
URL: https://unli.com.br/migrate-affiliates.php?password=AffMigrate2026
```

Cria as seguintes tabelas:
- `affiliate_users`
- `affiliate_referrals`
- `affiliate_tier_history`
- `affiliate_quarterly_sales`
- Altera `orders` adicionando colunas `affiliate_id` e `affiliate_hash`

### Migration do SDR (`migrate-sdr.php`)

```
URL: https://unli.com.br/migrate-sdr.php?password=SdrMigrate2026
```

Cria:
- `sdr_users`
- Altera `orders` com colunas do SDR

Criação opcional de SDR via URL:
```
&create_sdr=1&sdr_name=Nome&sdr_email=email@unli.com.br&sdr_pass=Senha&sdr_whatsapp=5511999999
```

### SQL Direto (`database/install-affiliates.sql` e `database/install-sdr.sql`)

Arquivos SQL disponíveis para execução direta no MySQL, caso prefira não usar os scripts PHP.

---

## 17. Diagramas de Fluxo

### Fluxo Completo: Da Indicação ao Pagamento

```
┌─────────────────────────────────────────────────────────────────────┐
│                     FLUXO DE INDICAÇÃO                              │
└─────────────────────────────────────────────────────────────────────┘

  AFILIADO                    CLIENTE                    SDR / SISTEMA
  ────────                    ────────                   ─────────────
      │                           │                           │
      │  Compartilha link         │                           │
      │  ?ref=hash ──────────────>│                           │
      │                           │                           │
      │                     Cookie salvo                      │
      │                     (30 dias)                         │
      │                           │                           │
      │                     Navega no site                    │
      │                           │                           │
      │                           │ ── Faz pedido ──────────> │
      │                           │    (checkout ou SDR)      │
      │                           │                           │
      │                           │              Hash recuperado do cookie
      │                           │              ou informado pelo SDR
      │                           │                           │
      │                           │              Pedido criado com
      │                           │              affiliate_id vinculado
      │                           │                           │
      │  Comissão calculada <─────┼───────────────────────────│
      │  e registrada             │                           │
      │                           │                           │
      │                           │         Pedido marcado como pago
      │                           │                           │
      │  Dashboard atualizado     │                           │
      │  (comissão pendente)      │                           │
      │                           │                           │
      │  Pagamento via PIX  <─────┼────── Admin/Financeiro ───│
      │  (manual)                 │       processa pagamento  │
      │                           │                           │
      │  Dashboard atualizado     │                           │
      │  (comissão paga ✓)        │                           │
```

### Fluxo de Progressão de Liga

```
  BRONZE 1 ──(R$ 800)──> BRONZE 2 ──(R$ 3.000)──> PRATA 1
      │                                                │
      │                                          (R$ 6.500)
      │                                                │
      │                                           PRATA 2
      │                                                │
      │                                          (R$ 12.000)
      │                                                │
      │                                           OURO 1
      │                                                │
      │                                          (R$ 20.000)
      │                                                │
      │                                           OURO 2
      │                                                │
      │                                          (R$ 35.000)
      │                                                │
      │                                        DIAMANTE 1
      │                                                │
      │                                          (R$ 50.000)
      │                                                │
      │                                        DIAMANTE 2
      │                                           (15%)
      │
      └── Comissão inicial: 5%
          Incremento por liga: +1,25%
          Comissão máxima: 15%
```

---

## Arquivos Relevantes do Projeto

### Backend (API)

| Arquivo | Descrição |
|---------|-----------|
| `api/affiliate/auth.php` | Autenticação de afiliados (registro, login, validação) |
| `api/affiliate/dashboard.php` | Dashboard, indicações, ranking, ligas |
| `api/affiliate/settings.php` | Atualização de perfil e senha |
| `api/affiliate/referral.php` | Validação de hash e vinculação de afiliado a pedido |
| `api/affiliate/middleware.php` | Autenticação JWT, configuração de ligas, cálculo de tier |
| `api/sdr/auth.php` | Autenticação de SDRs |
| `api/sdr/sale.php` | CRUD de vendas, marcar pago, enviar onboarding |
| `api/sdr/clients.php` | Listagem e detalhes de clientes |
| `api/sdr/middleware.php` | Autenticação JWT e rate limiting do SDR |

### Frontend (Vue.js)

| Arquivo | Descrição |
|---------|-----------|
| `src/pages/AffiliatePanel/AffiliateLogin.vue` | Login do afiliado |
| `src/pages/AffiliatePanel/AffiliateRegister.vue` | Cadastro do afiliado |
| `src/pages/AffiliatePanel/AffiliateDashboard.vue` | Dashboard completo |
| `src/pages/SDRPanel/SDRLogin.vue` | Login do SDR |
| `src/pages/SDRPanel/SDRDashboard.vue` | Dashboard do SDR |
| `src/pages/SDRPanel/SDRNovaVenda.vue` | Registro de nova venda |
| `src/pages/SDRPanel/SDRClientes.vue` | Lista de clientes |
| `src/pages/SDRPanel/SDRClienteDetalhe.vue` | Detalhe do cliente |
| `src/core/composables/useAffiliateTracking.js` | Rastreamento de cookie de afiliado |

### Banco de Dados

| Arquivo | Descrição |
|---------|-----------|
| `database/install-affiliates.sql` | Schema das tabelas de afiliados |
| `database/install-sdr.sql` | Schema da tabela de SDR |
| `migrate-affiliates.php` | Script de migração (afiliados) |
| `migrate-sdr.php` | Script de migração (SDR) |
