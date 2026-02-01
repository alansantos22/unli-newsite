# Smart Onboarding - Sistema Inteligente de Onboarding

## Visão Geral

O **Smart Onboarding** é uma reconstrução completa do sistema de onboarding conversacional da Unli. Este novo sistema resolve os problemas do assistente anterior e implementa uma arquitetura limpa com o Gemini como cérebro central.

## ⭐ Personalidade: Consultor, não Robô

O **Assistente Unli** (não mais "Jules") é um consultor especializado que:
- Explica o **PORQUÊ** de cada sugestão
- Dá dicas práticas baseadas em experiência de mercado
- Usa linguagem simples, sem jargões técnicos (nunca "UX", "UI", "conversão")
- Valoriza o que o cliente compartilha
- Usa **Flat UI Colors** - cores validadas pelo mercado de design

### Exemplo de Sugestão COM Explicação:

**Assistente:** "Vou sugerir 3 opções de frase pensando no seu público:

💡 **Sugestões:**

1. **Emocional**: "Café que aquece seu dia"
   *Cria uma conexão afetiva - pessoas associam café a momentos especiais*

2. **Profissional**: "Qualidade em cada xícara"  
   *Foca no produto e transmite confiança na entrega*

3. **Direto**: "O melhor café da região"
   *Funciona bem quando o cliente já sabe o que quer*"

## Principais Diferenças

| Problema Anterior | Nova Solução |
|-------------------|--------------|
| Regex mal feitos para extração | Gemini faz TODA a extração |
| Não identificava o passo atual | Sistema de checklist por campo |
| Campos não marcavam como respondidos | Status explícito: empty/answered/skipped/confirmed |
| Ficava repetindo perguntas | `askedOptionalFields` previne repetição |
| XP/gamificação não funcionava | Sistema completo com níveis e conquistas |
| Não salvava no backend | Salva automaticamente no banco |
| Pulava perguntas | Fluxo controlado por `nextFieldToAsk` |
| Redes sociais mal extraídas | Gemini normaliza tudo |
| Sugestões sem contexto | Todas sugestões vêm com explicação do PORQUÊ |
| "Jules" robótico | Assistente Unli como consultor especializado |
| Cores aleatórias | Flat UI Colors - paleta validada pelo mercado |

## Estrutura de Arquivos

```
src/shared/Components/SmartOnboarding/
├── SmartOnboarding.vue          # Componente principal
├── index.js                      # Exportações
│
├── components/
│   ├── ChatBubble.vue           # Bolha de mensagem
│   ├── FormPanel.vue            # Painel lateral com formulário
│   ├── FieldCard.vue            # Card de cada campo
│   ├── XPBar.vue                # Barra de XP e nível
│   ├── CEPInput.vue             # Input de CEP com busca
│   └── ImageUploadButton.vue    # Botão de upload de imagem
│
├── composables/
│   ├── useOnboardingState.js    # Estado central (checklist)
│   ├── useGeminiBrain.js        # Integração com Gemini
│   └── useHelpers.js            # CEP, telefone, sons, etc.
│
└── types/
    └── onboarding.types.js      # Tipos, constantes, steps
```

## Sistema de Checklist

Cada campo tem um status explícito:

```javascript
const FIELD_STATUS = {
  EMPTY: 'empty',        // Nunca foi respondido
  ANSWERED: 'answered',  // Usuário forneceu valor
  SKIPPED: 'skipped',    // Usuário disse que não tem
  CONFIRMED: 'confirmed' // Confirmado no resumo do step
};
```

### Como funciona:

1. O sistema sabe EXATAMENTE onde o usuário está
2. `nextFieldToAsk` retorna o próximo campo a perguntar
3. Campos já respondidos/skipados são ignorados
4. `askedOptionalFields` evita repetição de perguntas opcionais

## Fluxo de uma Mensagem

```
Usuário digita → processUserMessage()
                       ↓
              Adiciona ao chat
                       ↓
              Monta contexto para Gemini
                       ↓
              Chama API /api/ai/smart-onboarding.php
                       ↓
              Gemini retorna JSON estruturado
                       ↓
              handleGeminiResponse():
              - Atualiza campos (extracted)
              - Marca campos skipados (skipped)
              - Adiciona mensagem do bot
              - Se step completo → mostra resumo
```

## Estrutura de Resposta do Gemini

```json
{
  "message": "Mensagem amigável do bot",
  "extracted": {
    "companyName": "Café Aurora",
    "businessType": "alimentacao"
  },
  "skipped": ["hasPhysicalLocation"],
  "next_field": "frase",
  "suggestions": [
    {"field": "frase", "value": "Café que abraça", "label": "Acolhedor"}
  ],
  "color_palettes": [
    {"primary": "#3498DB", "secondary": "#2ECC71", "name": "Profissional"}
  ],
  "step_summary_ready": false,
  "confidence": 0.95
}
```

## Gamificação

### Níveis do Site

| Nível | Nome | XP Necessário | Ícone |
|-------|------|---------------|-------|
| 1 | Rascunho | 0-49 | 📝 |
| 2 | Esboço | 50-99 | ✏️ |
| 3 | Estruturado | 100-199 | 🏗️ |
| 4 | Profissional | 200-349 | 💼 |
| 5 | Premium | 350-499 | 💎 |
| 6 | Lendário | 500+ | 🚀 |

### Como ganhar XP

- Cada campo preenchido: 5-25 XP
- Completar um step: 50-100 XP
- Conquistas especiais: 10-50 XP

## Resumo por Step

Quando todos os campos de um step são tratados:

1. `step_summary_ready: true` vem do Gemini
2. `showStepSummary()` é chamado
3. Mostra resumo com todos os campos preenchidos
4. Botões: "Confirmar e Avançar" ou "Quero Editar"
5. Só avança quando usuário confirma

## Uso

```vue
<template>
  <SmartOnboarding
    :session-id="sessionId"
    :initial-data="initialData"
    @complete="handleComplete"
    @save="handleSave"
  />
</template>

<script>
import { SmartOnboarding } from '@/shared/Components/SmartOnboarding';

export default {
  components: { SmartOnboarding },
  
  methods: {
    handleComplete(formData) {
      console.log('Onboarding completo!', formData);
    },
    handleSave(formData) {
      console.log('Dados salvos', formData);
    }
  }
};
</script>
```

## Backend

O novo endpoint `/api/ai/smart-onboarding.php`:

- **Não usa regex** - tudo via Gemini
- Rate limiting: 30 req/min
- Salva automaticamente no banco
- Prompt estruturado e claro
- Safety settings menos restritivos

## Sons (Opcional)

Coloque os arquivos em `/public/sounds/`:

- `field-complete.mp3` - Ao preencher campo
- `step-complete.mp3` - Ao completar step
- `achievement.mp3` - Ao ganhar conquista
- `level-up.mp3` - Ao subir de nível
- `send.mp3` - Ao enviar mensagem
- `click.mp3` - Ao clicar em sugestão

## Próximos Passos

- [ ] Adicionar sons reais
- [ ] Implementar speech-to-text
- [ ] Criar preview ao vivo do site
- [ ] Adicionar mais conquistas
- [ ] Implementar undo/redo
- [ ] Sincronizar em tempo real com backend

## Autores

Sistema construído do zero com arquitetura limpa e gamificação.
