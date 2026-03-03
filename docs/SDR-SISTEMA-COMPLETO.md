# Sistema SDR / Assistente de Criação de Sites — Documentação Completa

> **Data:** 02/03/2026  
> **Escopo:** Análise completa do sistema SDR (Sales Development Representative) da Unli, incluindo fluxos de atendimento, comunicação frontend ↔ backend, integração de preços e pontos de falha.

---

## Índice

1. [Visão Geral da Arquitetura](#1-visão-geral-da-arquitetura)
2. [Mapa de Arquivos do Sistema](#2-mapa-de-arquivos-do-sistema)
3. [Fluxo 1 — SDR Pré-Venda (Chat de Vendas)](#3-fluxo-1--sdr-pré-venda-chat-de-vendas)
4. [Fluxo 2 — Checkout Direto (Sem Chat)](#4-fluxo-2--checkout-direto-sem-chat)
5. [Fluxo 3 — Consultoria de Marca Pós-Venda](#5-fluxo-3--consultoria-de-marca-pós-venda)
6. [Fluxo 4 — Onboarding / Wizard de Formulário](#6-fluxo-4--onboarding--wizard-de-formulário)
7. [Backend: Como os Preços São Calculados](#7-backend-como-os-preços-são-calculados)
8. [Backend: Como o Chat SDR Conversa com a IA](#8-backend-como-o-chat-sdr-conversa-com-a-ia)
9. [Backend: Correção de Stage e Finished](#9-backend-correção-de-stage-e-finished)
10. [Backend: Substituição de Preços Alucinados](#10-backend-substituição-de-preços-alucinados)
11. [Persistência de Dados (LocalStorage)](#11-persistência-de-dados-localstorage)
12. [Diagrama: O Que é Enviado e Recebido em Cada Chamada](#12-diagrama-o-que-é-enviado-e-recebido-em-cada-chamada)
13. [Problemas Identificados e Pontos de Falha](#13-problemas-identificados-e-pontos-de-falha)
14. [Resumo de Endpoints](#14-resumo-de-endpoints)

---

## 1. Visão Geral da Arquitetura

O sistema tem **dois fluxos completamente independentes** de chat com IA:

| Fluxo | Quando | Objetivo | API Backend | Modelo IA |
|-------|--------|----------|-------------|-----------|
| **SDR Pré-Venda** | Antes da compra | Qualificar lead, sugerir plano, fechar venda | `sdr-chat.php` | Gemini 2.0 Flash (temp=0.9) |
| **Consultoria de Marca** | Depois da compra | Coletar briefing da marca, identidade visual | `consultant-chat.php` | Gemini 2.0 Flash (temp=0.8) |

Ambos são acessíveis pela homepage (`MainPage`), mas em momentos diferentes da jornada do cliente.

### Jornada Completa do Usuário

```
Usuário chega no site
    │
    ├─ EntryDecisionHero ("Como quer começar?")
    │   │
    │   ├─ "Quero ajuda pra escolher" ─────→ SDRChatAssistant (FLUXO 1)
    │   │                                         │
    │   │                                         └─→ Checkout → Pagamento → Onboarding
    │   │
    │   └─ "Já sei o que preciso" ─────────→ Seletor de Pacotes (FLUXO 2)
    │                                             │
    │                                             └─→ QuickCheckout → Pagamento → Onboarding
    │
    └─ Após pagamento (email com magic link)
        │
        └─ /setup?token=XXX → OnboardingSetup
            │
            ├─ Fase 1: BrandConsultant (FLUXO 3)
            │       → Chat com IA para coletar identidade de marca
            │       → Extrai dados estruturados automaticamente
            │
            └─ Fase 2: DynamicOnboardingWizard (FLUXO 4)
                    → Formulário dinâmico pré-preenchido pela IA
                    → Auto-save → Submit final → Cria chamado
```

---

## 2. Mapa de Arquivos do Sistema

### Frontend (Vue.js)

| Arquivo | Função |
|---------|--------|
| `src/shared/Components/EntryDecisionHero.vue` | Tela de decisão inicial — bifurca entre chat SDR e seletor de pacotes |
| `src/shared/Components/SDRChatAssistant.vue` (~2343 linhas) | Chat SDR pré-venda com modal de checkout integrado |
| `src/shared/Components/BrandConsultant.vue` (~2246 linhas) | Chat de consultoria de marca pós-venda |
| `src/shared/Components/QuickCheckout.vue` | Checkout rápido para quem pula o chat |
| `src/shared/Components/DynamicOnboardingWizard.vue` | Wizard de formulário dinâmico |
| `src/shared/Components/SiteConfigurator.vue` | Configurador completo com preços |
| `src/core/services/PricingService.js` | Serviço dual-layer (cálculo local + validação no servidor) |
| `src/core/services/ai-rate-limiter.js` | Rate limiter frontend (10 req/dia por usuário) |
| `src/core/composables/useBrandConsultant.js` | Estado singleton reativo do BrandConsultant |
| `src/core/config/onboarding-steps.config.js` | Config dinâmica dos steps do wizard |
| `src/pages/OnboardingSetup/OnboardingSetup.vue` | Página do onboarding pós-compra |

### Backend (PHP)

| Arquivo | Função |
|---------|--------|
| `api/ai/sdr-chat.php` (843 linhas) | Endpoint do chat SDR — chama Gemini, valida stage, calcula preço real |
| `api/ai/sdr-extract-data.php` | Extrai dados estruturados da conversa SDR |
| `api/ai/consultant-chat.php` | Endpoint do chat de consultoria de marca |
| `api/ai/extract-brand-data.php` | Extrai perfil de marca da conversa pós-venda |
| `api/ai/prompts/sdr-consultant-prompt.md` (812 linhas) | System prompt do SDR — persona, regras de venda, formato JSON |
| `api/ai/prompts/consultant-system-prompt.md` | System prompt da consultoria de marca |
| `api/ai/prompts/extractor-system-prompt.md` | System prompt do extrator de dados |
| `api/lib/pricing.php` (473 linhas) | Motor de precificação — `compute_price()`, `normalize_selection()` |
| `api/pricing.json` | Fonte única de verdade para todos os preços |
| `api/price.php` | Endpoint REST para cálculo de preços (usado pelo SiteConfigurator) |
| `api/order_create.php` | Cria pedido no banco de dados |
| `api/create_preference.php` | Gera link de pagamento Pagar.me |
| `api/config.php` | Retorna configuração de preços para o frontend |
| `api/onboarding/validate.php` | Valida token magic-link e retorna dados do pedido |
| `api/onboarding/save-draft.php` | Auto-save do rascunho do briefing |
| `api/onboarding/submit.php` | Submissão final do briefing + cria chamado |

### Schemas

| Arquivo | Função |
|---------|--------|
| `api/schemas/briefing-schema.json` | Schema do briefing completo |
| `api/schemas/brand-extraction-schema.json` | Schema da extração de perfil de marca |

---

## 3. Fluxo 1 — SDR Pré-Venda (Chat de Vendas)

Este é o fluxo principal de vendas. O componente `SDRChatAssistant.vue` é um overlay fullscreen.

### 3.1 Estágios do Funil

O chat percorre 5 estágios (exibidos num tracker visual):

| Estágio | O que acontece | Quem decide o estágio |
|---------|----------------|----------------------|
| **ABERTURA** | Saudação, gerar rapport | IA (corrigido pelo backend) |
| **EXPLORACAO** | Perguntas sobre o negócio, nicho, necessidades | IA (corrigido pelo backend) |
| **PROPOSTA** | Sugerir pacote/páginas ideais | IA + Backend (se detectar menção a plano/páginas) |
| **PRECO** | Apresentar valores (sempre mensal primeiro) | IA + Backend (se detectar R$ na mensagem) |
| **FECHAMENTO** | Confirmar, direcionar ao pagamento | IA + Backend (se detectar frases de fechamento) |

**Regra crítica:** O estágio **nunca regride**. Se passou por PROPOSTA, não volta para EXPLORACAO.

### 3.2 Passo a Passo do Chat

1. **Componente monta** → trava scroll do body, foca no input
2. **Conversa salva?** → Restaura do localStorage se < 24h de idade
3. **Usuário envia mensagem** → adicionada ao array `messages[]`, salva no localStorage
4. **Frontend chama `callSDRChat()`:**
   - Simula delay de typing (1.5–2.5s aleatório)
   - Envia `POST /api/ai/sdr-chat.php`
   - Body:
     ```json
     {
       "messages": [
         { "role": "user", "content": "Tenho uma clínica de estética..." },
         { "role": "assistant", "content": "{JSON da resposta anterior}" },
         { "role": "user", "content": "Quero um site profissional" }
       ]
     }
     ```
5. **Backend processa** (detalhado na seção 8)
6. **Frontend recebe resposta** e:
   - Atualiza o estágio (só avança, nunca regride)
   - Adiciona mensagem do assistente ao chat
   - Verifica `data.clientData.needs_custom_dev` → se true, mostra botão WhatsApp (e-commerce/app)
   - Verifica `data.finished` → se true, abre modal de checkout

### 3.3 Detecção de Fechamento (3 Camadas)

O sistema tem **tripla redundância** para detectar quando a conversa deve fechar:

| Camada | Local | Como funciona |
|--------|-------|---------------|
| **1ª** | Backend `validateAndCorrectStageAndFinished()` | Analisa texto por regex: frases de fechamento + R$ + paymentMethod |
| **2ª** | Backend — flag `finished` do Gemini | O próprio modelo retorna `"finished": true` |
| **3ª** | Frontend `detectClosingMessageFallback()` | Se a mensagem contém frases de fechamento + preço + plano sugerido |

### 3.4 Modal de Checkout (dentro do SDRChatAssistant)

Quando `finished=true`:

1. Modal abre com formulário: **nome**, **email**, **WhatsApp**
2. Mostra resumo do plano (nome, páginas, add-ons)
3. Mostra preço (parcelado ou à vista, conforme paymentMethod)
4. Botão "Finalizar e Pagar"
5. **`submitDirectCheckout()`:**
   - Monta dados do pedido no mesmo formato do configurador
   - `POST /api/order_create.php` → cria pedido no banco
   - `POST /api/create_preference.php` → gera link Pagar.me
   - Redireciona para checkout da Pagar.me

### 3.5 Redirecionamento para WhatsApp

Se a IA detectar que o cliente precisa de e-commerce completo ou app:
- Seta `clientData.needs_custom_dev = true`
- Frontend mostra botão de WhatsApp (número: `5511999999999` — **PLACEHOLDER**)

---

## 4. Fluxo 2 — Checkout Direto (Sem Chat)

No `EntryDecisionHero.vue`, o usuário pode pular o chat:

1. Clica "Já sei o que preciso"
2. Vê 3 pacotes predefinidos:
   - **Essencial** (Sobre + Serviços + Contato)
   - **Autoridade** (+ Portfólio + FAQ)
   - **Ecossistema** (+ Blog + Vitrine)
3. Preços calculados **localmente** a partir do `pricingConfig` prop
4. Toggle opcional: Atendimento com Especialista (+R$169)
5. Seleção de forma de pagamento (PIX ou 12x cartão)
6. → `QuickCheckout` → `order_create.php` → `create_preference.php` → Pagar.me

---

## 5. Fluxo 3 — Consultoria de Marca Pós-Venda

Após o pagamento, o cliente recebe um email com magic link (`/setup?token=XXX`).

### 5.1 Componente: `BrandConsultant.vue`

1. **Mount** → `initializeSession()` via composable `useBrandConsultant`
2. **Sessão anterior?** → Restaura mensagens do localStorage + toast
3. **Mensagem inicial fixa** (sem API): "Olá! ...me conta sobre a sua empresa..."
4. **Progressão do chat:** Empresa → Produto → Visual → Autoridade → Diferencial

### 5.2 Cada Mensagem do Usuário

1. Adicionada ao `chatHistory[]` local + composable
2. **`callChatAPI()`:**
   - Formata mensagens com contexto do pedido
   - `POST /api/ai/consultant-chat.php`
   - Body:
     ```json
     {
       "messages": [
         { "role": "user", "content": "Minha empresa é uma clínica..." }
       ],
       "orderContext": {
         "planName": "Site Completo - Autoridade",
         "purchasedPages": ["about", "services", "contact", "portfolio", "faq"]
       }
     }
     ```
   - Response:
     ```json
     {
       "success": true,
       "response": "Que interessante! Uma clínica com foco em...",
       "finished": false
     }
     ```
3. Após loading → estado de "typing" (2.5s delay)
4. Se `finished=true` → auto-inicia extração em 1.5s

### 5.3 Extração de Dados de Marca

Disparada quando:
- IA sinaliza `finished=true`, OU
- Usuário clica "Finalizar Briefing" (aparece após 4+ mensagens)

**Processo:**
1. `POST /api/ai/extract-brand-data.php`
   - Body:
     ```json
     {
       "chat_history": [
         { "role": "user", "content": "..." },
         { "role": "assistant", "content": "..." }
       ]
     }
     ```
   - Response:
     ```json
     {
       "success": true,
       "extracted": {
         "companyInfo": { "name": "...", "niche": "...", "targetAudience": "..." },
         "brandCore": { "mission": "...", "values": [], "personality": "..." },
         "authorityTriggers": ["...", "..."],
         "differentiation": { "mainDifferential": "...", "competitors": "..." },
         "visualIdentity": {
           "primaryColor": "#...",
           "secondaryColor": "#...",
           "typography": "...",
           "layoutStyle": "...",
           "mood": "..."
         },
         "aiSuggestions": {
           "tagline": "...",
           "toneOfVoice": "...",
           "keywords": []
         }
       }
     }
     ```
2. Modal de resultados exibe tudo organizado
3. Usuário revisa e confirma (ou edita manualmente)
4. Dados mapeados para o formulário via `mapExtractedDataToForm()`
5. Emite evento `data-extracted` → OnboardingSetup transiciona para Fase 2

---

## 6. Fluxo 4 — Onboarding / Wizard de Formulário

### 6.1 Validação do Token

`GET /api/onboarding/validate.php?token=XXX`
- Valida token magic-link
- Retorna dados do pedido, páginas compradas, progresso existente, checklist de campos

### 6.2 Wizard Dinâmico

- Steps definidos em `onboarding-steps.config.js` (1435 linhas)
- Step "Identidade" sempre aparece
- Demais steps dependem das páginas compradas
- Campos pré-preenchidos pela IA (se Fase 1 foi concluída)
- Auto-save: `POST /api/onboarding/save-draft.php` (status → "preenchendo")
- Submit final: `POST /api/onboarding/submit.php`
  - Upload de logo
  - Status → "concluido"
  - Cria ticket na Fila de Chamados

---

## 7. Backend: Como os Preços São Calculados

### 7.1 Fonte de Verdade: `pricing.json`

Todos os preços vivem em `api/pricing.json`. Qualquer alteração de preço deve ser feita **apenas** neste arquivo.

**Estrutura de preços:**

| Item | Tipo | Preço |
|------|------|-------|
| Landing Page | Produto base | R$ 599 |
| Site Completo | Produto base | R$ 619 |
| Sobre Nós | Página addon | R$ 59 |
| Serviços | Página addon | R$ 119 |
| Portfólio | Página addon | R$ 159 |
| FAQ | Página addon | R$ 89 |
| Contato | Página addon | R$ 129 |
| Blog | Página addon | R$ 349 |
| Vitrine | Página addon | R$ 449 |
| Vídeo Básico | Conteúdo | R$ 35/unidade |
| Vídeo Pro | Conteúdo | R$ 120/unidade |
| PDF | Conteúdo | R$ 15 |
| Especialista | Serviço addon | R$ 169 |

### 7.2 Motor de Cálculo: `lib/pricing.php`

**Funções principais:**

1. **`load_pricing_config($path)`** — Carrega e valida `pricing.json`
2. **`normalize_selection($input, $config)`** — Sanitiza a seleção:
   - Whitelist de produtos válidos
   - Whitelist de páginas válidas
   - Whitelist de content addons
   - Remove qualquer campo não reconhecido
3. **`compute_price($selection, $config)`** — Cálculo autoritativo:
   ```
   subtotal = preço_base_produto
            + Σ(preço_página × quantidade)
            + Σ(preço_conteúdo × quantidade)
            + Σ(preço_addon_serviço)
   
   à_vista = subtotal (sem markup)
   parcelado_total = subtotal × 1.15 (15% markup gateway)
   parcela_12 = parcelado_total / 12
   ```

### 7.3 Dual-Layer: Frontend + Backend

O `PricingService.js` implementa cálculo em duas camadas:

| Camada | Quando | Precisão |
|--------|--------|----------|
| **Local** (frontend) | Sempre, para feedback instantâneo | ~99% (espelha a lógica do backend) |
| **Servidor** (`price.php`) | Quando disponível (fallback para local se falhar) | 100% (autoritativo) |

```
PricingService.calculatePrice()
    │
    ├─ calculateLocal() → resultado imediato
    │
    └─ validateOnServer() → POST /api/price.php
        │
        ├─ Sucesso → sobrescreve com preço real
        └─ Falha → mantém cálculo local
```

### 7.4 Como o SDR Chat Calcula Preços

**Diferente do configurador**, o SDR chat **NÃO chama `price.php`**. Ele faz o cálculo **dentro do próprio `sdr-chat.php`**:

```php
// sdr-chat.php (linhas 109-126)
require_once __DIR__ . '/../lib/pricing.php';
$cfg = load_pricing_config(__DIR__ . '/../pricing.json');
$formSelection = sdrPlanToFormSelection($suggestedPlan);  // Converte formato do SDR → formato do formulário
$normalizedSel = normalize_selection($formSelection, $cfg);
$realPricing = compute_price($normalizedSel, $cfg);
```

A função `sdrPlanToFormSelection()` converte o formato do SDR:
```json
// Formato SDR (o que a IA retorna)
{
  "type": "site_complete",
  "pages": ["about", "services", "contact"],
  "paymentMethod": "cartao_12x"
}

// ↓ Convertido para formato do formulário (idêntico ao SiteConfigurator)

{
  "product": "site_complete",
  "pages": { "about": 1, "services": 1, "contact": 1 },
  "content": [],
  "custom_pages": [],
  "video_basic_quantity": 0,
  "video_pro_quantity": 0,
  "service_addons": { "specialist_onboarding": false }
}
```

Depois, chama **exatamente as mesmas funções** `normalize_selection()` e `compute_price()` — garantindo que o preço seja idêntico ao do formulário.

---

## 8. Backend: Como o Chat SDR Conversa com a IA

### 8.1 Fluxo Completo de Uma Requisição

```
Frontend envia mensagens
    │
    ▼
sdr-chat.php recebe POST
    │
    ├─ 1. Rate limit (30 req/min por sessão PHP)
    ├─ 2. Valida JSON input (campo "messages" obrigatório)
    ├─ 3. Carrega system prompt (sdr-consultant-prompt.md)
    ├─ 4. Injeta dados dinâmicos no prompt:
    │      ├─ {{DATA_ATUAL}} → "Março de 2026"
    │      ├─ {{TABELA_PRECOS}} → tabela completa formatada do pricing.json
    │      ├─ {{PROMOCAO_INFO}} → regras de promoção
    │      ├─ {{PRECO_ESPECIALISTA}} → "R$ 169"
    │      ├─ {{PRECOS_VIDEOS}} → preços de vídeo do pricing.json
    │      ├─ {{PRECO_PDF}} → preço de PDF do pricing.json
    │      └─ {{RECURSOS_CUSTOM}} → preços de recursos customizados
    │
    ├─ 5. Formata mensagens para API Gemini:
    │      ├─ 1ª msg (role: user) → system prompt + lembretes críticos sobre stages
    │      ├─ 2ª msg (role: model) → confirmação de entendimento das regras
    │      └─ Histórico do chat (user ↔ model)
    │
    ├─ 6. Chama Gemini API (com retry):
    │      ├─ URL: generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash
    │      ├─ temperature: 0.9 (mais criativo)
    │      ├─ maxOutputTokens: 1500
    │      ├─ Retry: 3 tentativas com backoff exponencial
    │      └─ Retryable: 429, 500, 502, 503
    │
    ├─ 7. Parse da resposta (múltiplas estratégias):
    │      ├─ JSON direto
    │      ├─ Remove ```json ... ```
    │      ├─ Regex para extrair JSON do meio do texto
    │      ├─ Parser de profundidade de chaves { }
    │      └─ Fallback: texto puro como "message"
    │
    ├─ 8. Valida e corrige stage + finished (seção 9)
    │
    ├─ 9. Se suggestedPlan.pages existe:
    │      ├─ Converte para formato de formulário
    │      ├─ compute_price() → preço real
    │      └─ Substitui preços alucinados na mensagem (seção 10)
    │
    └─ 10. Retorna resposta final
```

### 8.2 Formato da Resposta Final

```json
{
  "success": true,
  "response": "Texto da mensagem do assistente (com preços corrigidos)",
  "stage": "PROPOSTA",
  "clientData": {
    "businessName": "Clínica Estética",
    "niche": "saude",
    "needs_custom_dev": false
  },
  "suggestedPlan": {
    "type": "site_complete",
    "name": "Essencial",
    "pages": ["about", "services", "contact"],
    "paymentMethod": "cartao_12x"
  },
  "pricing": {
    "subtotal": 926,
    "avista": 926,
    "parcelado_total": 1064.9,
    "parcela_12": 88.74,
    "breakdown": {
      "base": 619,
      "pages": { "about": 59, "services": 119, "contact": 129 },
      "content": {},
      "service_addons": {}
    }
  },
  "finished": false
}
```

### 8.3 Formato JSON Esperado do Gemini

A IA deve responder **sempre** em JSON:

```json
{
  "message": "Texto da resposta para o cliente",
  "stage": "EXPLORACAO",
  "clientData": {
    "businessName": "...",
    "niche": "...",
    "needs_custom_dev": false
  },
  "suggestedPlan": {
    "type": "site_complete",
    "name": "Autoridade",
    "pages": ["about", "services", "contact", "portfolio", "faq"],
    "paymentMethod": "cartao_12x",
    "specialistOnboarding": false
  },
  "finished": false
}
```

---

## 9. Backend: Correção de Stage e Finished

A função `validateAndCorrectStageAndFinished()` (linhas 548–655 do `sdr-chat.php`) é uma **camada de segurança** porque o Gemini **frequentemente erra** o estágio e esquece de marcar `finished=true`.

### 9.1 Regras de Correção (em ordem de prioridade)

| Prioridade | Condição | Ação |
|------------|----------|------|
| **0** (mais alta) | Mensagem contém frase de fechamento + R$ + paymentMethod | Força `FECHAMENTO` + `finished=true` |
| **0b** | Frase de fechamento + (stage anterior era PRECO ou tem R$) | Força `FECHAMENTO` + `finished=true` |
| **1** | Gemini retornou `finished=true` | Mantém `FECHAMENTO` + `finished=true` |
| **2** | Mensagem tem R$ + termos de preço (parcela/pix/mensal) | Corrige para `PRECO` |
| **3** | Mensagem menciona plano/pacote ou 2+ páginas | Corrige para `PROPOSTA` |
| **4** | Nenhum dos acima | Mantém stage original, nunca regride |

### 9.2 Frases de Fechamento Detectadas

```
"vou te direcionar", "direcionar agora", "finalizar o pedido",
"finalizar seu pedido", "direcionar para finalizar", "vamos finalizar",
"fechar o pedido", "concluir o pedido", "prosseguir para o pagamento",
"prosseguir com o pagamento", "encaminhar para o pagamento",
"qualquer dúvida, é só chamar"
```

### 9.3 Fallback no Frontend

Além do backend, o frontend tem `detectClosingMessageFallback()` que verifica:
- Mensagem contém frases de fechamento
- Mensagem contém preço (R$)
- Existe `suggestedPlan` com páginas

Se todas forem verdadeiras, força `finished=true` no frontend.

---

## 10. Backend: Substituição de Preços Alucinados

O Gemini **inventa preços** com frequência. O backend tem um sistema de regex para substituir:

### Para Pagamento Parcelado (12x cartão):

```php
// 1. Remove bold markdown dos preços (**R$ 150** → R$ 150)
$msg = preg_replace('/\*{1,2}(R\$\s*[\d.,]+)\*{1,2}/u', '$1', $msg);

// 2. Substitui "R$ XXX mensais/por mês/mês" pelo valor real
$msg = preg_replace(
    '/R\$\s*[\d.,]+\s*(mensais?|por\s*m[eê]s|\/m[eê]s|mensalmente|ao\s*m[eê]s)/ui',
    'R$ ' . $realPricing['parcela_12'] . '/mês',
    $msg
);

// 3. Substitui "XXX,XX/mês" sem "R$" explícito
$msg = preg_replace(
    '/\b[\d]+[,.][\d]+\s*(mensais?|\/m[eê]s|por\s*m[eê]s)/ui',
    'R$ ' . $realPricing['parcela_12'] . '/mês',
    $msg
);
```

### Para Pagamento À Vista (PIX):

```php
// Substitui qualquer R$ com 4+ dígitos pelo valor real
$msg = preg_replace('/R\$\s*[\d.,]{4,}/u', 'R$ ' . $realPricing['avista'], $msg);
```

### ⚠️ Risco

Este regex é **frágil**:
- Pode substituir valores que não são preços (ex: "R$ 5.000 de faturamento mensal")
- O padrão `/R\$\s*[\d.,]{4,}/u` para à vista pega qualquer valor longo após "R$"
- Se a IA formatar de forma inesperada, o regex pode falhar silenciosamente

---

## 11. Persistência de Dados (LocalStorage)

| Sistema | Chave | Conteúdo | Expiração |
|---------|-------|----------|-----------|
| SDR Chat | `unli_sdr_conversation` | Histórico completo de mensagens | 24 horas |
| Rate Limiter | `unli_ai_requests` | Contagem de requests diários | Meia-noite |
| Brand Consultant | `unli_consultant_{sessionId}_chatHistory` | Histórico do chat de marca | Sem expiração |
| Brand Consultant | `unli_consultant_{sessionId}_extractedData` | Dados extraídos da marca | Sem expiração |
| Brand Consultant | `unli_consultant_{sessionId}_sessionMeta` | Metadata da sessão | Sem expiração |

---

## 12. Diagrama: O Que é Enviado e Recebido em Cada Chamada

### Chamada 1: Chat SDR

```
┌─────────────────────────────────────────────────────────────┐
│ POST /api/ai/sdr-chat.php                                   │
├─────────── FRONTEND ENVIA ──────────────────────────────────┤
│ {                                                           │
│   "messages": [                                             │
│     { "role": "user", "content": "texto do usuário" },     │
│     { "role": "assistant", "content": "resposta anterior" } │
│   ]                                                         │
│ }                                                           │
├─────────── BACKEND FAZ ─────────────────────────────────────┤
│ 1. Injeta pricing.json nos placeholders do prompt           │
│ 2. Envia para Gemini 2.0 Flash (com retry 3x)              │
│ 3. Parseia JSON da resposta                                 │
│ 4. Corrige stage e finished                                 │
│ 5. Se tem pages → calcula preço real via lib/pricing.php    │
│ 6. Substitui preços alucinados no texto                     │
├─────────── BACKEND RETORNA ─────────────────────────────────┤
│ {                                                           │
│   "success": true,                                          │
│   "response": "texto com preços corrigidos",                │
│   "stage": "PROPOSTA",                                      │
│   "clientData": { businessName, niche, needs_custom_dev },  │
│   "suggestedPlan": { type, name, pages[], paymentMethod },  │
│   "pricing": { subtotal, avista, parcela_12, breakdown },   │
│   "finished": false                                         │
│ }                                                           │
└─────────────────────────────────────────────────────────────┘
```

### Chamada 2: Extração SDR (Manual)

```
┌─────────────────────────────────────────────────────────────┐
│ POST /api/ai/sdr-extract-data.php                           │
├─────────── ENVIA ───────────────────────────────────────────┤
│ { "messages": [ ...conversa completa... ] }                 │
├─────────── RETORNA ─────────────────────────────────────────┤
│ {                                                           │
│   "business": { name, niche, description },                 │
│   "site": { type, pages, features },                        │
│   "contact": { phone, email, address },                     │
│   "sales": { temperature, objections, urgency },            │
│   "notes": "observações gerais"                             │
│ }                                                           │
└─────────────────────────────────────────────────────────────┘
```

### Chamada 3: Chat Consultoria (Pós-Venda)

```
┌─────────────────────────────────────────────────────────────┐
│ POST /api/ai/consultant-chat.php                            │
├─────────── ENVIA ───────────────────────────────────────────┤
│ {                                                           │
│   "messages": [ ...histórico... ],                          │
│   "orderContext": {                                         │
│     "planName": "Site Completo - Autoridade",               │
│     "purchasedPages": ["about","services","contact","portfolio","faq"] │
│   }                                                         │
│ }                                                           │
├─────────── RETORNA ─────────────────────────────────────────┤
│ {                                                           │
│   "success": true,                                          │
│   "response": "texto do consultor de marca",                │
│   "finished": false                                         │
│ }                                                           │
└─────────────────────────────────────────────────────────────┘
```

### Chamada 4: Extração de Marca

```
┌─────────────────────────────────────────────────────────────┐
│ POST /api/ai/extract-brand-data.php                         │
├─────────── ENVIA ───────────────────────────────────────────┤
│ { "chat_history": [ ...conversa completa... ] }             │
├─────────── RETORNA ─────────────────────────────────────────┤
│ {                                                           │
│   "success": true,                                          │
│   "extracted": {                                            │
│     "companyInfo": { name, niche, targetAudience, ... },    │
│     "brandCore": { mission, values, personality },          │
│     "authorityTriggers": ["certificação X", "10 anos"],     │
│     "differentiation": { mainDifferential, competitors },   │
│     "visualIdentity": { primaryColor, secondaryColor, ... },│
│     "aiSuggestions": { tagline, toneOfVoice, keywords }     │
│   }                                                         │
│ }                                                           │
└─────────────────────────────────────────────────────────────┘
```

### Chamada 5: Criar Pedido + Pagamento

```
┌─────────────────────────────────────────────────────────────┐
│ POST /api/order_create.php                                  │
├─────────── ENVIA ───────────────────────────────────────────┤
│ { name, email, whatsapp, product, pages, addons,            │
│   paymentMethod, price, pricing_snapshot }                  │
├─────────── RETORNA ─────────────────────────────────────────┤
│ { success: true, order_id: 123 }                            │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ POST /api/create_preference.php                             │
├─────────── ENVIA ───────────────────────────────────────────┤
│ { order_id: 123, amount, customer, items }                  │
├─────────── RETORNA ─────────────────────────────────────────┤
│ { success: true, checkout_url: "https://pagar.me/..." }     │
└─────────────────────────────────────────────────────────────┘
```

---

## 13. Problemas Identificados e Pontos de Falha

### 🔴 Críticos

#### P1 — Preços Substituídos por Regex São Frágeis
**Onde:** `sdr-chat.php` linhas 131–160  
**Problema:** O regex `/R\$\s*[\d.,]{4,}/u` para pagamento à vista substitui **qualquer valor monetário** com 4+ caracteres numéricos após "R$". Se a IA disser "empresa com faturamento de R$ 5.000", esse valor será substituído pelo preço do plano.  
**Impacto:** Cliente pode ver preços errados em contextos narrativos.  
**Sugestão:** Limitar a substituição a frases específicas ("o investimento é R$", "o valor fica R$") em vez de substituir globalmente.

#### P2 — WhatsApp com Número Placeholder
**Onde:** `SDRChatAssistant.vue` linha ~324  
**Problema:** `whatsappCustomDev: '5511999999999'` é um placeholder.  
**Impacto:** Clientes de e-commerce/app são redirecionados para número inexistente.

#### P3 — Gemini Erra o Stage com Frequência
**Onde:** Sistema inteiro de stages  
**Problema:** São necessárias **3 camadas de detecção** (backend regex, flag da IA, fallback frontend) porque o Gemini classifica errado ~15-30% das vezes.  
**Impacto:** O tracker visual de progresso fica inconsistente, confundindo o usuário. Ex: pulando de ABERTURA direto para PRECO. Ou mostrando EXPLORACAO quando já está em PROPOSTA.

#### P4 — Texto do Promo Banner Contradiz as Regras
**Onde:** `EntryDecisionHero.vue` — banner "15% OFF se pagar no PIX"  
**Problema:** O prompt do SDR **proíbe explicitamente** mencionar "desconto de 15% no PIX" (regra 4 da tabela de preços). Mas o banner do frontend diz exatamente isso. Na realidade, PIX não tem desconto — é o cartão que tem 15% de markup.  
**Impacto:** Cliente entra no chat com expectativa de "15% OFF no PIX". A IA não confirma. Confusão e perda de confiança.

### 🟡 Moderados

#### P5 — Schemas de Extração Completamente Diferentes
**Onde:** `sdr-extract-data.php` vs `extract-brand-data.php`  
**Problema:** Os dois chats geram dados em schemas completamente diferentes. Dados extraídos pelo SDR (`business`, `site`, `contact`) não se conectam com o formato do Brand Consultant (`companyInfo`, `brandCore`, `visualIdentity`).  
**Impacto:** Dados da conversa SDR pré-venda são perdidos no pós-venda. O cliente repete informações.

#### P6 — Preço do Especialista no Modal SDR é Enganoso
**Onde:** `SDRChatAssistant.vue` — modal de checkout  
**Problema:** `specialistPrice = Math.ceil(169/12) = R$ 15/mês`. Mas o "Atendimento com Especialista" é uma taxa única de R$ 169, não mensal. O `/mês` dá a entender que é recorrente.  
**Impacto:** Cliente acha que vai pagar R$ 15/mês para sempre quando na verdade paga R$ 169 uma vez.

#### P7 — Sem Fallback se Extração de Marca Falhar Repetidamente
**Onde:** `BrandConsultant.vue`  
**Problema:** Se `extract-brand-data.php` falhar, há botão de retry. Mas se continuar falhando, não há caminhoalternativo para avançar direto ao formulário vazio.  
**Impacto:** Cliente fica preso no pós-venda sem conseguir avançar.

#### P8 — Rate Limiter Frontend vs Backend Descoordenados
**Onde:** `ai-rate-limiter.js` (10/dia) vs `sdr-chat.php` (30/min)  
**Problema:** O frontend limita a 10 requests IA por dia (localStorage). O backend limita a 30/min (sessão PHP). Um cliente pode esgotar os 10 do frontend em uma conversa longa. Além disso, o rate limiter do frontend usa localStorage (facilmente apagável).  
**Impacto:** Clientes com conversas longas são bloqueados no frontend antes de fechar negócio.

#### P9 — Tabela de Preços Massiva Inflaciona Tokens do Prompt
**Onde:** `sdr-chat.php` `formatPricingTable()` + `injectDynamicData()`  
**Problema:** A tabela injetada inclui combinações pré-calculadas, regras detalhadas, e lembretes repetidos. Junto com o prompt de 812 linhas, o system prompt é extremamente longo.  
**Impacto:** Custo de tokens elevado. Possível degradação da qualidade da resposta por contexto saturado.

### 🟢 Menores

#### P10 — ConsultoriaGamificacao é Página Isolada
**Onde:** `ConsultoriaGamificacao.vue`  
**Problema:** Preços hardcoded (R$ 16.990 / R$ 1.899/mês). Nenhuma integração com o SDR ou sistema de preços dinâmicos.  
**Impacto:** Se os preços mudarem, precisa de deploy manual.

#### P11 — Conversa SDR Expira em 24h sem Aviso
**Onde:** `SDRChatAssistant.vue` — localStorage  
**Problema:** Se o cliente volta após 24h, a conversa sumiu silenciosamente. Sem toast, sem aviso.  
**Impacto:** Experiência frustrante — cliente acha que perdeu o progresso.

---

## 14. Resumo de Endpoints

| Método | Endpoint | Rate Limit | Modelo IA | Função |
|--------|----------|------------|-----------|--------|
| POST | `/api/ai/sdr-chat.php` | 30/min (sessão PHP) | Gemini 2.0 Flash (0.9) | Chat SDR pré-venda |
| POST | `/api/ai/sdr-extract-data.php` | — | Gemini 2.0 Flash (0.3) | Extração de dados SDR |
| POST | `/api/ai/consultant-chat.php` | 30/min | Gemini 2.0 Flash (0.8) | Chat marca pós-venda |
| POST | `/api/ai/extract-brand-data.php` | 10/min | Gemini 2.0 Flash (0.3) | Extração de perfil de marca |
| POST | `/api/price.php` | — | — | Cálculo autoritativo de preço |
| POST | `/api/order_create.php` | — | — | Criar pedido no banco |
| POST | `/api/create_preference.php` | — | — | Gerar link Pagar.me |
| GET | `/api/config.php` | — | — | Config + pricing para frontend |
| GET | `/api/onboarding/validate.php` | — | — | Validar token magic-link |
| POST | `/api/onboarding/save-draft.php` | — | — | Auto-save briefing |
| POST | `/api/onboarding/submit.php` | — | — | Submissão final do briefing |

---

> **Nota:** Esta documentação reflete o estado do código em 02/03/2026. Para alterações em preços, edite **apenas** `api/pricing.json` — os valores se propagam automaticamente para o prompt do SDR, a tabela de preços, e o motor de cálculo.
