# 📝 Dynamic Onboarding Wizard

Sistema de onboarding dinâmico que adapta as perguntas baseado no que o cliente comprou. Inclui geração de conteúdo por seções com IA.

## 🎯 Arquitetura em 2 Fases

```
┌─────────────────────────────────────────────────────────────┐
│  FASE 1: COLETA DE DADOS                                    │
│  DynamicOnboardingWizard                                    │
│                                                             │
│  ✨ Identidade → 📞 Contato → 🏢 Sobre → ⚙️ Serviços → 🚀   │
│                                                             │
│  • Campos granulares (ano, valores, etc.)                   │
│  • Botão "✨ Melhorar" em textareas                          │
│  • Limite de 500 caracteres por campo                       │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│  FASE 2: GERAÇÃO DE CONTEÚDO                                │
│  SectionContentGenerator                                    │
│                                                             │
│  🏢 Sobre Nós    [✓ Gerado]                                 │
│  ⚙️ Serviços     [Gerar]                                    │
│  ❓ FAQ          [Gerar]                                    │
│                                                             │
│  • Geração seção por seção (performático)                   │
│  • Limite: 10 gerações/dia                                  │
│  • Modal profissional ao atingir limite                     │
└─────────────────────────────────────────────────────────────┘
```

## 📁 Arquivos do Sistema

```
src/
  core/
    config/
      onboarding-steps.config.js  # Configuração de steps/campos
    services/
      ai-rate-limiter.js          # Controle de limite diário
  shared/
    Components/
      DynamicOnboardingWizard.vue  # Wizard principal (2 fases)
      DynamicField.vue             # Renderizador de campos
      AITextEnhancer.vue           # Textarea com botão IA
      SectionContentGenerator.vue  # Gerador por seções
      BaseModal.vue                # Modal reutilizável

api/
  ai/
    enhance-text.php              # Polir textos individuais
    generate-section.php          # Gerar seção (otimizado)
```

## 🚀 Como Usar

### 1. Importar o Wizard

```vue
<template>
  <DynamicOnboardingWizard
    :purchased-pages="['sobre_nos', 'servicos', 'faq']"
    :initial-data="savedData"
    session-id="abc123"
    @complete="handleComplete"
    @save="handleAutoSave"
    @error="handleError"
  />
</template>

<script>
import DynamicOnboardingWizard from '@/shared/Components/DynamicOnboardingWizard.vue';

export default {
  components: { DynamicOnboardingWizard },
  
  data() {
    return {
      savedData: {} // Dados previamente salvos
    };
  },
  
  methods: {
    handleComplete(result) {
      console.log('Onboarding completo!', result);
      // Redirecionar para próxima etapa
    },
    
    handleAutoSave(data) {
      console.log('Rascunho salvo automaticamente');
    },
    
    handleError(error) {
      console.error('Erro:', error);
    }
  }
};
</script>
```

### 2. Tipos de Páginas Disponíveis

| Page Type | Step Gerado | Campos Principais |
|-----------|-------------|-------------------|
| `sobre_nos` | 🏢 Sobre a Empresa | Ano fundação, história, valores, missão, visão |
| `servicos` | ⚙️ Seus Serviços | Lista de serviços com repetidor |
| `faq` | ❓ Perguntas Frequentes | Checkboxes + repetidor de perguntas |
| `portfolio` | 🖼️ Portfólio | Projetos com imagens e descrições |
| `vitrine_produtos` | 🛍️ Vitrine de Produtos | Lista de produtos com preços |
| `depoimentos` | ⭐ Depoimentos | Avaliações de clientes |
| `blog_noticias` | 📝 Blog/Notícias | Tópicos, palavras-chave |

### 3. Steps Fixos (Sempre Aparecem)

- **✨ Identidade da Marca** - Nome, cores, logo, tom de voz
- **📞 Contato e Localização** - WhatsApp, redes sociais, endereço
- **🚀 Finalização** - Observações, urgência, referências

## 🔧 Tipos de Campos

### Campos Básicos

```javascript
// Texto simples
{ id: 'name', type: 'text', label: 'Nome', placeholder: 'Ex: João' }

// Número
{ id: 'year', type: 'number', label: 'Ano', min: 1900, max: 2024 }

// Área de texto
{ id: 'bio', type: 'textarea', label: 'Biografia', rows: 4 }

// Seleção
{ id: 'tone', type: 'select', label: 'Tom', options: [...] }

// Checkbox
{ id: 'agree', type: 'checkbox', label: 'Aceito os termos' }
```

### Campos Especiais

```javascript
// Textarea com Melhorar com IA ✨
{
  id: 'story',
  type: 'textarea',
  label: 'Sua história',
  aiEnhance: true,  // Habilita botão IA
  aiPrompt: 'Transforme em narrativa inspiradora' // Opcional
}

// Tags / Múltiplos valores
{
  id: 'values',
  type: 'tags',
  label: 'Valores',
  maxTags: 5,
  suggestions: ['Qualidade', 'Inovação', 'Confiança']
}

// Color Picker com presets
{
  id: 'primaryColor',
  type: 'color',
  label: 'Cor principal',
  presets: [{ value: '#0066CC', name: 'Azul' }, ...]
}

// Upload
{
  id: 'logo',
  type: 'upload',
  accept: 'image/*',
  multiple: false,
  maxSize: 5 * 1024 * 1024
}
```

### Campo Repetidor (Repeater)

```javascript
{
  id: 'services',
  type: 'repeater',
  label: 'Serviços',
  addButtonText: '+ Adicionar Serviço',
  minItems: 1,
  maxItems: 10,
  fields: [
    { id: 'name', type: 'text', label: 'Nome', required: true },
    { id: 'description', type: 'textarea', label: 'Descrição', aiEnhance: true },
    { id: 'price', type: 'text', label: 'Preço' }
  ]
}
```

### Campo Condicional

```javascript
// Aparece apenas se hasAddress === true
{
  id: 'street',
  type: 'text',
  label: 'Rua',
  conditional: { field: 'hasAddress', value: true }
}

// Aparece se o array contém 'pricing'
{
  id: 'pricingDetails',
  type: 'textarea',
  conditional: { field: 'selectedCategories', contains: 'pricing' }
}
```

## ✨ Botão "Melhorar com IA"

### Frontend (AITextEnhancer.vue)

O componente `AITextEnhancer` adiciona um botão ✨ aos textareas que envia o texto para o Gemini polir.

**Props:**
- `modelValue` - Texto atual
- `aiEnhance` - Habilitar botão (default: true)
- `aiPrompt` - Instrução customizada para a IA
- `voiceTone` - Tom de voz (profissional, amigável, etc.)
- `showComparison` - Mostrar original vs melhorado lado a lado

**Eventos:**
- `@enhanced` - Quando IA retorna texto melhorado
- `@error` - Em caso de falha

### Backend (api/ai/enhance-text.php)

**Request:**
```json
POST /api/ai/enhance-text.php
{
  "text": "Nós fazemos as coisas bem legal...",
  "voice_tone": "profissional",
  "prompt": "Melhore mantendo a essência",
  "context": "texto para página Sobre Nós"
}
```

**Response:**
```json
{
  "success": true,
  "enhanced_text": "Realizamos nosso trabalho com excelência...",
  "original_length": 32,
  "enhanced_length": 45,
  "voice_tone": "profissional"
}
```

**Rate Limit:** 15 requests/minuto por sessão

## 📊 Estrutura dos Dados Coletados

O wizard gera um objeto organizado por step:

```json
{
  "identity": {
    "companyName": "Padaria Dona Maria",
    "tagline": "Pães artesanais desde 1985",
    "primaryColor": "#D4A373",
    "voiceTone": "amigavel",
    "logo": "[File Object]"
  },
  "contact": {
    "whatsapp": "(11) 99999-9999",
    "socialNetworks": [
      { "type": "instagram", "url": "https://..." }
    ],
    "hasPhysicalLocation": true,
    "addressCep": "01310-100",
    "addressStreet": "Av. Paulista",
    ...
  },
  "sobre_nos": {
    "foundingYear": 1985,
    "foundingStory": "Minha avó começou vendendo pães...",
    "companyValues": ["Tradição", "Qualidade", "Carinho"],
    ...
  },
  "servicos": {
    "services": [
      {
        "name": "Pães Artesanais",
        "targetAudience": "Famílias",
        "problem": "Falta de pães frescos na região"
      }
    ]
  },
  "finalization": {
    "additionalNotes": "Gostaria de cores quentes...",
    "urgency": "normal"
  }
}
```

## 🔄 Auto-save

O wizard salva automaticamente 2 segundos após cada alteração:

```javascript
// Configuração
<DynamicOnboardingWizard
  session-id="order_12345"
  save-endpoint="/api/onboarding/save-draft.php"
/>
```

O endpoint recebe:
```json
{
  "session_id": "order_12345",
  "current_step": 3,
  "data": { ... },
  "is_draft": true
}
```

## ➕ Adicionar Novo Tipo de Página

1. Edite `onboarding-steps.config.js`:

```javascript
export const pageSteps = {
  // ...existing...
  
  // Nova página
  nova_pagina: {
    id: 'nova_pagina',
    pageType: 'nova_pagina',
    title: '🆕 Nova Página',
    subtitle: 'Configure sua nova página',
    icon: '🆕',
    fields: [
      { id: 'campo1', type: 'text', label: 'Campo 1' },
      { id: 'campo2', type: 'textarea', label: 'Campo 2', aiEnhance: true },
      // ...
    ]
  }
};
```

2. Pronto! O wizard automaticamente incluirá esse step quando `purchasedPages` contiver `'nova_pagina'`.

## 🧪 Testando

```javascript
// No console do navegador
const wizard = document.querySelector('.dynamic-onboarding-wizard').__vue__;

// Ver steps atuais
console.log(wizard.steps);

// Ver dados coletados
console.log(wizard.formData);

// Ir para step específico
wizard.goToStep(2);
```

## 📱 Responsivo

O wizard é totalmente responsivo:
- Mobile: Steps empilhados, navegação simplificada
- Desktop: Preview lado a lado, indicadores de progresso completos

## 🎨 Customização Visual

As cores principais podem ser alteradas via SCSS variables:

```scss
// Em um arquivo de variáveis global
$wizard-primary: #3498db;
$wizard-secondary: #9b59b6;
$wizard-success: #27ae60;
$wizard-border-radius: 12px;
```

---

## � Fase 2: Geração de Conteúdo por Seções

Após a coleta de dados, o usuário entra na Fase 2 para gerar conteúdo com IA.

### Sistema de Seções por Página

| Página | Seções Disponíveis |
|--------|-------------------|
| **Sobre Nós** | Título, Parágrafo de abertura, História, Call-to-action |
| **Serviços** | Título, Descrição, Benefícios, CTA |
| **FAQ** | Dinâmico (1 seção por pergunta) |
| **Portfólio** | Título, Descrição, Projetos destacados, CTA |

### SectionContentGenerator

```vue
<template>
  <SectionContentGenerator
    page-type="sobre_nos"
    :onboarding-data="formData.sobre_nos"
    voice-tone="profissional"
    @section-update="handleSectionUpdate"
    @complete="handlePageComplete"
  />
</template>
```

### Limite de Uso (Rate Limiting)

**10 gerações por dia** - Controlado no servidor e cliente.

**Servidor (PHP):**
```php
// api/ai/generate-section.php
$sessionKey = 'ai_section_usage_' . date('Y-m-d');
$usage = $_SESSION[$sessionKey] ?? 0;
if ($usage >= 10) {
    // Retorna erro
}
```

**Cliente (JavaScript):**
```javascript
import { checkAILimit, recordAIUsage } from '@/core/services/ai-rate-limiter';

if (!checkAILimit()) {
  // Mostrar modal de limite
  return;
}

// Após uso bem-sucedido
recordAIUsage();
```

### BaseModal para Notificações

```vue
<template>
  <BaseModal
    v-model="showLimitModal"
    type="warning"
    icon="⚠️"
    title="Limite de IA Atingido"
  >
    <p>Você usou todas as 10 gerações de hoje.</p>
    <p>O limite será renovado à meia-noite.</p>
  </BaseModal>
</template>
```

### Otimização de Performance

- **Input máximo:** 500 caracteres
- **Output máximo:** 300 tokens
- **Modelo:** Gemini 1.5 Flash (mais rápido e barato)
- **Geração individual:** Uma seção por vez

---

## 📋 Checklist de Implementação

- [x] Configuração de steps por página (`onboarding-steps.config.js`)
- [x] Wizard principal (`DynamicOnboardingWizard.vue`)
- [x] Renderizador de campos (`DynamicField.vue`)
- [x] Botão Melhorar com IA (`AITextEnhancer.vue`)
- [x] Endpoint de enhance (`api/ai/enhance-text.php`)
- [x] Campos: text, number, textarea, select, checkbox, color, tags, upload, repeater
- [x] Campos condicionais
- [x] Auto-save
- [x] Rate limiting (10/dia)
- [x] Modal profissional (`BaseModal.vue`)
- [x] Geração por seções (`SectionContentGenerator.vue`)
- [x] Endpoint otimizado (`api/ai/generate-section.php`)
- [x] Controle de limite cliente (`ai-rate-limiter.js`)
- [ ] Integração com sistema de pedidos existente
- [ ] Testes E2E

---

*Última atualização: Dezembro 2024*
