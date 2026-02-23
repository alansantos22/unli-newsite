# SDR IA Consultivo - Documentação

## Visão Geral

O **SDR IA Consultivo** é um sistema de vendas inteligente que atua como um consultor de negócios virtual. Em vez de apenas tirar pedidos, ele:

1. **Qualifica leads** através de conversa natural
2. **Oferece micro-consultoria** específica por nicho
3. **Sugere o plano ideal** baseado nas necessidades do cliente
4. **Extrai dados estruturados** para preencher o formulário automaticamente

---

## Fluxo de Compra

```
                    ┌──────────────────────┐
                    │  Site Vitrine        │
                    │  (Escolhe plano)     │
                    └──────────┬───────────┘
                               │
                               ▼
                    ┌──────────────────────┐
                    │  EntryDecisionHero   │
                    │  "Como quer começar?"│
                    └──────────┬───────────┘
                               │
              ┌────────────────┴────────────────┐
              │                                 │
              ▼                                 ▼
    ┌─────────────────┐              ┌─────────────────┐
    │ "Quero ajuda"   │              │ "Já sei o que   │
    │                 │              │    quero"       │
    └────────┬────────┘              └────────┬────────┘
             │                                │
             ▼                                ▼
    ┌─────────────────┐              ┌─────────────────┐
    │ SDRChatAssistant│              │ Package Selector│
    │ (Chat com IA)   │              │ (Escolha rápida)│
    └────────┬────────┘              └────────┬────────┘
             │                                │
             ▼                                ▼
    ┌─────────────────┐              ┌─────────────────┐
    │ Configurador    │              │ QuickCheckout   │
    │ Completo        │              │ (Direto para MP)│
    │ (Customização)  │              │                 │
    └────────┬────────┘              └────────┬────────┘
             │                                │
             └────────────────┬───────────────┘
                              ▼
                    ┌──────────────────────┐
                    │  Pagar.me             │
                    │  Checkout              │
                    └──────────────────────┘
```

---

## Arquitetura Técnica

```
┌─────────────────────────────────────────────────────────────────┐
│                      FRONTEND (Vue.js)                          │
├─────────────────────────────────────────────────────────────────┤
│  EntryDecisionHero.vue     │  SDRChatAssistant.vue              │
│  ┌─────────────────────┐   │  ┌────────────────────────────┐    │
│  │ "Quero ajuda" ───────────▶│ Chat com IA Consultiva     │    │
│  │ "Já sei o que quero"──┐ │  │ • Micro-consultoria        │    │
│  │  + Package Selector   │ │  │ • Tracking de etapas       │    │
│  └─────────────────────┘ │ │  │ • Extração de dados        │    │
│                          │ │  └────────────────────────────┘    │
│                          │ │                │                   │
│                          │ │                ▼                   │
│                          │ │  ┌────────────────────────────┐    │
│                          │ ├─▶│ SiteConfigurator           │    │
│                          │ │  │ (Formulário customização)  │    │
│                          │ │  └────────────────────────────┘    │
│                          │ │                                    │
│                          │ │  ┌────────────────────────────┐    │
│                          └───▶│ QuickCheckout              │    │
│                            │  │ (Checkout rápido direto)   │    │
│                            │  └────────────────────────────┘    │
└────────────────────────────┼────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                      BACKEND (PHP)                              │
├─────────────────────────────────────────────────────────────────┤
│  /api/ai/sdr-chat.php           /api/ai/sdr-extract-data.php    │
│  ┌─────────────────────┐        ┌─────────────────────────┐     │
│  │ Chat Consultivo     │        │ Extração de Dados       │     │
│  │ • Carrega prompt    │        │ • Shadow Prompt         │     │
│  │ • Injeta preços     │        │ • JSON estruturado      │     │
│  │ • Chama Gemini      │        │ • Validação             │     │
│  └─────────────────────┘        └─────────────────────────┘     │
│           │                              │                      │
│           └──────────────┬───────────────┘                      │
│                          ▼                                      │
│               prompts/sdr-consultant-prompt.md                  │
└─────────────────────────────────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                    GEMINI API (Google)                          │
│                    gemini-2.0-flash                             │
└─────────────────────────────────────────────────────────────────┘
```

---

## Arquivos Criados

### Backend (PHP)

| Arquivo | Descrição |
|---------|-----------|
| `api/ai/sdr-chat.php` | Endpoint principal do chat SDR |
| `api/ai/sdr-extract-data.php` | Endpoint para extração de dados estruturados |
| `api/ai/prompts/sdr-consultant-prompt.md` | Prompt do consultor humanizado |

### Frontend (Vue.js)

| Arquivo | Descrição |
|---------|-----------|
| `src/shared/Components/EntryDecisionHero.vue` | Tela de decisão inicial (Chat vs. Form) + Seletor de Pacotes |
| `src/shared/Components/SDRChatAssistant.vue` | Interface do chat com IA |
| `src/shared/Components/QuickCheckout.vue` | Checkout rápido para quem já decidiu |

---

## Endpoints da API

### POST `/api/ai/sdr-chat.php`

Endpoint principal para conversa com o SDR.

**Request:**
```json
{
  "messages": [
    { "role": "user", "content": "Tenho uma pizzaria..." }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "response": "Que legal! Para pizzarias, ter fotos...",
  "stage": "EXPLORACAO",
  "clientData": {
    "niche": "pizzaria",
    "businessName": null,
    "needs": ["delivery", "fotos"],
    "temperature": "morno"
  },
  "suggestedPlan": null,
  "finished": false
}
```

### POST `/api/ai/sdr-extract-data.php`

Extrai dados estruturados da conversa para preencher o formulário.

**Request:**
```json
{
  "conversation": [
    { "role": "user", "content": "..." },
    { "role": "assistant", "content": "..." }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "business": {
      "name": "Pizzaria do João",
      "niche": "alimentação",
      "description": "Pizzaria delivery...",
      "targetAudience": "Famílias da região"
    },
    "site": {
      "type": "site_complete",
      "suggestedPackage": "essential",
      "pages": ["about", "services", "contact"],
      "features": ["cardapio", "whatsapp"]
    },
    "sales": {
      "temperature": "quente",
      "urgency": "alta"
    }
  }
}
```

---

## O Funil de Vendas

O SDR segue um funil de 5 etapas:

### 1. ABERTURA
- Apresentação inicial
- Pergunta de qualificação única
- Identifica se é curioso ou comprador

### 2. EXPLORAÇÃO
- Descobre o nicho do cliente
- **Aplica micro-consultoria** (diferencial)
- Identifica necessidades e dores

### 3. PROPOSTA
- Sugere o pacote ideal
- Explica o modelo de assinatura
- Foca no valor, não no preço

### 4. PREÇO
- Apresenta valores com promoção
- Destaca descontos (30% + 15% PIX)
- Pergunta forma de pagamento

### 5. FECHAMENTO
- Agradece pelo tempo
- Oferece próximo passo
- Marca conversa como finalizada

---

## Micro-Consultoria por Nicho

O diferencial do SDR é dar **dicas de valor** antes de vender. Exemplos:

| Nicho | Dica de Valor |
|-------|---------------|
| Barbearia | "Botão de agendamento visível ajuda quem lembra às 23h" |
| Advogado | "Artigos sobre suas causas geram confiança antes do contato" |
| Pizzaria | "Fotos bonitas dos pratos aumentam a fome e conversão" |
| Psicóloga | "Foto profissional + abordagem escrita trazem segurança" |
| Academia | "Ambiente + depoimentos transformam visitas em matrículas" |
| Loja de Roupas | "Catálogo rápido no celular converte quem vem do Instagram" |

---

## Tratamento de Objeções

O prompt inclui respostas para objeções comuns:

- **"Está caro"** → Destaca valor agregado (hospedagem, suporte incluso)
- **"Preciso pensar"** → Oferece enviar resumo por e-mail/WhatsApp
- **"Consegue desconto?"** → Explica combo máximo (30% + 15%)
- **"Quero app tipo Uber"** → Diferencia site de software, redireciona

---

## Humanização

### Latência Artificial
O frontend adiciona delay randômico (1.5s a 2.5s) para parecer humano.

### Estrutura "Sanduíche"
Toda resposta tem 3 camadas:
1. **Empatia/Validação** - Mostra que entendeu
2. **Dica de Valor** - Micro-consultoria
3. **Pergunta do Funil** - Próximo passo

### Tom de Voz
- Linguagem natural
- Evita "Como posso ajudar?"
- Usa espelhamento
- Máximo 1-2 perguntas por vez

---

## Como Usar nos Componentes Vue

### Tela de Decisão Inicial

```vue
<template>
  <EntryDecisionHero
    @select-chat="showChat = true"
    @select-form="showWizard = true"
  />
</template>

<script>
import { EntryDecisionHero } from '@/shared/Components';

export default {
  components: { EntryDecisionHero },
  data() {
    return {
      showChat: false,
      showWizard: false
    }
  }
}
</script>
```

### Chat SDR

```vue
<template>
  <SDRChatAssistant
    v-if="showChat"
    @proceed-to-wizard="handleProceed"
    @skip-to-form="showWizard = true"
  />
</template>

<script>
import { SDRChatAssistant } from '@/shared/Components';

export default {
  components: { SDRChatAssistant },
  methods: {
    handleProceed(payload) {
      // payload.extractedData contém os dados estruturados
      // Preencher o store e ir para o wizard
      this.$store.commit('SET_EXTRACTED_DATA', payload.extractedData);
      this.showWizard = true;
    }
  }
}
</script>
```

---

## Fluxo Completo

```
┌───────────────────┐
│ EntryDecisionHero │
│ "Como quer começar?"
└─────────┬─────────┘
          │
    ┌─────┴─────┐
    ▼           ▼
┌───────┐   ┌────────┐
│ Chat  │   │ Form   │
│ SDR   │   │ Direto │
└───┬───┘   └────────┘
    │
    ▼ (após conversa)
┌───────────────────┐
│ sdr-extract-data  │
│ (Shadow Prompt)   │
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│ Wizard com dados  │
│ pré-preenchidos   │
└───────────────────┘
```

---

## Próximos Passos

1. **Integrar com Store Vue** - Popular dados extraídos no Vuex/Pinia
2. **Testes de Objeções** - Refinar prompt com cenários reais
3. **Analytics** - Rastrear taxa de conversão Chat vs. Form
4. **A/B Testing** - Testar variações do prompt
5. **Fallback de Erro** - Melhorar experiência em caso de falha da API

---

## Configuração

### Variáveis de Ambiente

Certifique-se de que `GEMINI_API_KEY` está configurada em `api/config.secure.php`:

```php
define('GEMINI_API_KEY', 'sua-api-key-aqui');
```

### Rate Limiting

O endpoint `/api/ai/sdr-chat.php` tem rate limit de:
- **30 requisições por minuto** por sessão

---

## Referências

- [Gemini API Documentation](https://ai.google.dev/docs)
- [SPIN Selling Methodology](https://www.huthwaite.com/spin-selling)
- Prompt Engineering: Chain of Thought
