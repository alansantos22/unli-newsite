# 📝 Brand Profile Storage - Armazenamento do Perfil da Marca

## Visão Geral

O sistema agora salva o **"Perfil da Marca Extraído!"** como um campo separado e completo no briefing_data. Isso permite preservar todos os insights capturados durante a conversa com o consultor de marca, além dos dados mapeados para os campos do formulário.

## Estrutura do Brand Profile

### Campo `brandProfile` no briefing_data

```json
{
  "brandProfile": {
    "extractionMeta": {
      "confidence": "high|medium|low",
      "conversationTurns": 0,
      "extractedAt": "2026-02-03T10:30:00Z",
      "missingFields": [],
      "inferredFields": []
    },
    "companyInfo": {
      "name": "Unli Games",
      "niche": "Desenvolvimento de jogos RPG e jogos desafiadores",
      "targetAudience": "Fãs de jogos RPG, jogadores que buscam desafios...",
      "mainProduct": "Jogos com histórias autorais"
    },
    "brandCore": {
      "historySummary": "A Unli Games é uma desenvolvedora de jogos focada em RPGs...",
      "mission": "Criar experiências imersivas e desafiadoras através de jogos RPG...",
      "vision": "",
      "values": ["Paixão por jogos", "Narrativas autorais", "Comunidade e feedbacks"],
      "foundingYear": "",
      "founders": ""
    },
    "authorityTriggers": {
      "yearsInMarket": "15 anos aproximadamente",
      "certifications": [],
      "notableClients": [],
      "keyAchievements": "Reconhecimento do jogo Parallelium no mundo Web3...",
      "socialProofElements": ""
    },
    "differentiation": {
      "usp": "Proposta única de valor identificada",
      "uniqueMethodology": "Metodologia própria se identificada",
      "competitiveAdvantages": [],
      "marketPosition": ""
    },
    "visualIdentity": {
      "suggestedStyle": "Moderno",
      "colorVibe": "Cores que transmitem energia gaming...",
      "primaryColorSuggestion": "#ff6b35",
      "secondaryColorSuggestion": "#1a1a2e",
      "typographyMood": "Técnica e gaming",
      "layoutSuggestion": "Layout dinâmico",
      "moodKeywords": ["Gaming", "Desafio", "Imersivo"],
      "heroSuggestion": "Hero com elementos visuais de jogos",
      "specialSections": ["Portfolio de jogos", "Comunidade", "Devlog"]
    },
    "aiSuggestions": {
      "suggestedTagline": "Criando mundos, desafiando limites",
      "alternativeTaglines": [],
      "toneOfVoice": "Técnico e entusiasmado",
      "suggestedKeywords": ["jogos RPG", "desenvolvimento", "indie games"],
      "colorPaletteSuggestion": "Paleta gaming vibrante",
      "contentDirections": []
    },
    "conversationContext": {
      "chatHistory": [/* Histórico completo da conversa */],
      "keyInsights": [],
      "customerPainPoints": [],
      "businessGoals": []
    }
  }
}
```

## Onde os Dados São Salvos

### 1. **OnboardingSetup.vue** 
- Método `transformToBriefingFormat()` - adiciona o `brandProfile` completo
- Método `saveDraftToBackend()` - envia para a API

### 2. **useBrandConsultant.js**
- Método `exportToBriefingSchema()` - inclui o `brandProfile` no export

### 3. **briefing-schema.json**
- Campo `brandProfile` adicionado ao schema oficial

## Fluxo de Salvamento

```mermaid
graph TD
    A[Conversa do Consultant] --> B[Extração de Dados]
    B --> C[Modal: Perfil da Marca Extraído!]
    C --> D{Confirmar e Avançar}
    D --> E[transformToBriefingFormat()]
    E --> F[brandProfile salvo separadamente]
    F --> G[saveDraftToBackend()]
    G --> H[API: save-draft.php]
```

## Benefícios

### ✅ **Preservação Completa**
- Todos os insights extraídos são mantidos
- Nada se perde na transformação para o formulário

### ✅ **Dados Estruturados**
- Organizado por categorias lógicas
- Fácil acesso programático

### ✅ **Contexto da Conversa**
- Histórico do chat preservado
- Meta-informações sobre a extração

### ✅ **Flexibilidade Futura**
- Permite usar os dados para IA de conteúdo
- Análises e insights adicionais
- Melhorias no processo de geração

## Como Usar os Dados

### 1. **Na Geração de Conteúdo**
```javascript
// Acessar dados específicos do brand profile
const brandProfile = briefingData.brandProfile;
const mission = brandProfile.brandCore.mission;
const visualStyle = brandProfile.visualIdentity.suggestedStyle;
```

### 2. **Para Re-preenchimento**
```javascript
// Usar para popular formulários automaticamente
const companyInfo = brandProfile.companyInfo;
form.companyName = companyInfo.name;
form.niche = companyInfo.niche;
```

### 3. **Para Análise de Qualidade**
```javascript
// Verificar confiança da extração
const confidence = brandProfile.extractionMeta.confidence;
if (confidence === 'low') {
  // Solicitar revisão manual
}
```

## API Endpoints Afetados

- `POST /api/onboarding/save-draft.php` - recebe o brandProfile
- Futuros endpoints de IA podem consumir esses dados diretamente

## Compatibilidade

- ✅ **Backward Compatible** - Não quebra dados existentes
- ✅ **Opcional** - Campo só existe se houve extração via consultant
- ✅ **Incremental** - Adiciona funcionalidade sem remover existente

---

> **Nota**: Esta implementação atende ao requisito de salvar o "Perfil da Marca Extraído!" como uma propriedade separada, preservando todos os insights capturados durante a conversa consultiva para uso futuro na criação do site.