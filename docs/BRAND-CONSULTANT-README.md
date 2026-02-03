# 🎯 Unli 2.0 - Fluxo Consultivo de Marca

## Visão Geral

O **Unli 2.0** representa uma mudança de paradigma na coleta de dados de branding. Saímos de um "Interrogatório" (formulário com inputs) para uma "**Entrevista Guiada**" (consultoria conversacional).

### Benefícios

1. **Elimina o "Bloqueio do Escritor"**: É mais fácil falar sobre a empresa do que "escrever a missão"
2. **Dados mais Ricos**: Cliente fala com emoção e contexto, não apenas preenchendo campos
3. **UX Superior**: Experiência de consultoria premium
4. **Redundância Positiva**: Se o cliente fala da missão na fase da história, capturamos do mesmo jeito

---

## Arquitetura

```
┌─────────────────────────────────────────────────────────────┐
│                         FRONTEND                             │
│  ┌─────────────────┐    ┌─────────────────┐                │
│  │ BrandConsultant │───▶│ useBrandConsultant │              │
│  │     (Vue)       │    │   (Composable)      │              │
│  └────────┬────────┘    └─────────────────────┘              │
│           │                                                   │
│           ▼                                                   │
│  ┌─────────────────────────────────────────────────────────┐ │
│  │              Chat History (Context Window)               │ │
│  └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                         BACKEND                              │
│  ┌─────────────────────┐    ┌─────────────────────────────┐ │
│  │  consultant-chat.php │    │  extract-brand-data.php     │ │
│  │   (Gemini Chat)      │    │   (Gemini Extraction)       │ │
│  └──────────┬───────────┘    └──────────────┬──────────────┘ │
│             │                               │                 │
│             ▼                               ▼                 │
│  ┌─────────────────────────────────────────────────────────┐ │
│  │               Gemini 2.0 Flash API                       │ │
│  └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    JSON Estruturado                          │
│  {                                                           │
│    companyInfo, brandCore, authorityTriggers,               │
│    differentiation, aiSuggestions, contentSeeds             │
│  }                                                           │
└─────────────────────────────────────────────────────────────┘
```

---

## Estrutura de Arquivos

```
api/
├── ai/
│   ├── prompts/
│   │   ├── consultant-system-prompt.md   # Persona do Consultor
│   │   └── extractor-system-prompt.md    # Instruções do Extrator
│   ├── consultant-chat.php               # Endpoint do Chat
│   └── extract-brand-data.php            # Endpoint de Extração
├── schemas/
│   └── brand-extraction-schema.json      # JSON Schema

src/
├── shared/Components/
│   └── BrandConsultant.vue               # Componente Principal
├── core/composables/
│   └── useBrandConsultant.js             # Gerenciamento de Estado
```

---

## O Fluxo de Conversa: "Funil de Contexto"

### Fase 1: O Pitch (O Que e Para Quem)
> "Me conta um pouco sobre o seu negócio como se estivesse me apresentando ele num café. O que vocês fazem e quem vocês atendem?"

**Dados Extraídos:** Nome da empresa, Setor, Público-alvo, Produto/Serviço principal

### Fase 2: A Raiz (História e Motivação)
> "E como tudo isso começou? O que motivou a criação da empresa?"

**Dados Extraídos:** História, Missão (implícita), Fundadores, Tempo de mercado

### Fase 3: A Prova (Gatilhos de Confiança)
> "O que passa mais segurança no trabalho de vocês? São os anos de estrada, algum case famoso ou certificações?"

**Dados Extraídos:** Anos de mercado, Prêmios, Certificações, Clientes notáveis

### Fase 4: O X-Factor (Metodologia Única)
> "Vocês têm alguma metodologia própria ou jeito único de trabalhar?"

**Dados Extraídos:** Metodologia proprietária, Valores, Cultura, Diferenciais

---

## Uso do Componente

### Básico

```vue
<template>
  <BrandConsultant 
    @data-confirmed="handleDataConfirmed"
    @edit-manually="handleEditManually"
  />
</template>

<script>
import { BrandConsultant } from '@/shared/Components';

export default {
  components: { BrandConsultant },
  methods: {
    handleDataConfirmed(extractedData) {
      console.log('Dados confirmados:', extractedData);
      // Preencher formulário automaticamente
    },
    handleEditManually(extractedData) {
      // Abrir formulário para edição manual
    }
  }
};
</script>
```

### Com Composable

```javascript
import { useBrandConsultant } from '@/core/composables';

export default {
  setup() {
    const {
      formData,
      hasExtractedData,
      extractionConfidence,
      exportToBriefingSchema
    } = useBrandConsultant();

    const saveBriefing = () => {
      const briefingData = exportToBriefingSchema();
      // Enviar para API
    };

    return { formData, hasExtractedData, saveBriefing };
  }
};
```

---

## Estrutura do JSON Extraído

```typescript
interface BrandExtractionSchema {
  extractionMeta: {
    confidence: 'high' | 'medium' | 'low';
    conversationTurns: number;
    missingFields: string[];
    inferredFields: string[];
  };
  
  companyInfo: {
    name: string | null;
    niche: string;
    targetAudience: string;
    mainProduct: string;
  };
  
  brandCore: {
    historySummary: string;
    mission: string;
    vision: string | null;
    values: string[];
    foundingYear: string | null;
    founders: string | null;
  };
  
  authorityTriggers: {
    yearsInMarket: string | null;
    certifications: string[];
    notableClients: string[];
    keyAchievements: string;
    socialProofElements: string;
  };
  
  differentiation: {
    usp: string;
    uniqueMethodology: string | null;
    competitiveAdvantage: string;
    additionalContext: string | null;
  };
  
  aiSuggestions: {
    suggestedTagline: string;
    alternativeTaglines: string[];
    toneOfVoice: string;
    toneDescription: string;
    suggestedKeywords: string[];
    colorPaletteSuggestion: string;
  };
  
  contentSeeds: {
    aboutUsHook: string;
    servicesAngle: string;
    ctaSuggestion: string;
  };
}
```

---

## API Endpoints

### POST `/api/ai/consultant-chat.php`

Gerencia a conversa com o assistente.

**Request:**
```json
{
  "messages": [
    { "role": "user", "content": "Somos um estúdio de design..." }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "response": "Que interessante! E vocês focam mais em..."
}
```

### POST `/api/ai/extract-brand-data.php`

Extrai dados estruturados do histórico da conversa.

**Request:**
```json
{
  "chat_history": [
    { "role": "assistant", "content": "Olá! Me conte sobre..." },
    { "role": "user", "content": "Somos um estúdio..." }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "extracted": {
    "companyInfo": { ... },
    "brandCore": { ... },
    "aiSuggestions": { ... }
  }
}
```

---

## Mapeamento para V-Models

O composable `useBrandConsultant` faz o mapeamento automático:

| Campo Extraído | V-Model do Formulário |
|---------------|----------------------|
| `companyInfo.name` | `form.companyName` |
| `companyInfo.niche` | `form.niche` |
| `companyInfo.targetAudience` | `form.targetAudience` |
| `brandCore.historySummary` | `form.history` |
| `brandCore.mission` | `form.mission` |
| `brandCore.values` | `form.values[]` |
| `differentiation.usp` | `form.usp` |
| `differentiation.uniqueMethodology` | `form.methodology` |
| `authorityTriggers.keyAchievements` | `form.authority` |
| `aiSuggestions.suggestedTagline` | `form.slogan` |
| `aiSuggestions.toneOfVoice` | `form.toneOfVoice` |
| `aiSuggestions.suggestedKeywords` | `form.keywords[]` |

---

## Configuração

### Variáveis de Ambiente

Certifique-se de que `GEMINI_API_KEY` está configurada em `api/config.secure.php`:

```php
define('GEMINI_API_KEY', 'sua-chave-aqui');
```

### Rate Limiting

- **Chat:** 30 requisições/minuto
- **Extração:** 10 requisições/minuto

---

## Personalização dos Prompts

Os prompts podem ser editados diretamente nos arquivos:

- `api/ai/prompts/consultant-system-prompt.md` - Comportamento do consultor
- `api/ai/prompts/extractor-system-prompt.md` - Instruções de extração

### Dicas para Customização

1. **Tom de Voz:** Ajuste a seção "Tom de Voz" no prompt do consultor
2. **Fases:** Adicione ou remova fases no roteiro
3. **Campos:** Modifique o schema no prompt do extrator para capturar novos dados

---

## Troubleshooting

### Conversa muito curta
Se a IA está pulando fases, aumente a exigência de profundidade no prompt:
```
Se o usuário der uma resposta curta, faça até 2 perguntas de follow-up antes de avançar.
```

### JSON inválido na extração
Verifique se o `responseMimeType: 'application/json'` está ativo no endpoint de extração.

### Dados faltando
Revise a seção `missingFields` no JSON retornado e adicione validação no frontend.

---

## Roadmap

- [ ] Voice Input (Speech-to-Text integrado)
- [ ] Sugestões de cores baseadas no tom
- [ ] Geração automática de logo placeholder
- [ ] Integração com CRM externo
- [ ] Analytics de qualidade da conversa
