# 🤖 Assistente Conversacional de Onboarding - Unli

## 📋 Visão Geral

Transformar o processo de onboarding de um formulário tradicional para uma **experiência de consultoria gamificada** com um assistente de IA (Gemini 2.0 Flash), onde o usuário "conversa" naturalmente e vê seu site sendo construído em tempo real.

### Conceito Principal: "Jornada de Construção"

```
┌─────────────────────────────────────────────────────────────────┐
│  ANTES: Formulário burocrático                                   │
│  ❌ Campos frios, sem contexto                                   │
│  ❌ Erros interrompem o fluxo                                    │
│  ❌ Abandono alto                                                │
└─────────────────────────────────────────────────────────────────┘

                              ▼

┌─────────────────────────────────────────────────────────────────┐
│  DEPOIS: Consultoria Interativa + Gamificação                   │
│                                                                  │
│  ┌─────────────────────────┐  ┌─────────────────────────────┐   │
│  │  💬 CHAT COM JULES      │  │  ⚡ CONSTRUÇÃO DO SITE      │   │
│  │                         │  │                              │   │
│  │  🤖 "E aí! Bora criar   │  │  ┌────────────────────────┐  │   │
│  │  algo incrível? Me      │  │  │ ✨ TechSol          ✓ │  │   │
│  │  conta sobre você!"     │  │  │ 💼 Tecnologia       ✓ │  │   │
│  │                         │  │  │ 📝 Tagline      [━━▒] │  │   │
│  │  👤 "Somos a TechSol,   │  │  │ 🎨 Cores        [   ] │  │   │
│  │  fazemos TI..."         │  │  └────────────────────────┘  │   │
│  │                         │  │                              │   │
│  │  🤖 "Show! Olha só o    │  │  ⚡ POTENCIAL: ████░░ 68%   │   │
│  │  que já capturei! →"    │  │  🏆 +15 XP Identidade       │   │
│  │                         │  │                              │   │
│  │  [🎤] [💬 Digite...]    │  │  🎯 Próximo: Definir cores  │   │
│  └─────────────────────────┘  └─────────────────────────────┘   │
│                                                                  │
│  ═══════════════════════════════════════════════════════════════ │
│  🚀 Identidade ──▶ 📞 Contato ──▶ 🏢 História ──▶ ⚙️ Serviços   │
└─────────────────────────────────────────────────────────────────┘
```

### O Diferencial: "Mesa de Edição" no Final

```
┌─────────────────────────────────────────────────────────────────┐
│                    🏁 MESA DE EDIÇÃO FINAL                       │
│                                                                  │
│  "Analisei suas respostas e seu site tem 95% de potencial       │
│   de conversão! Revise os cards abaixo e ajuste o que quiser."  │
│                                                                  │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌───────────┐  │
│  │ ✅ IDENTIDADE│ │ ✅ CONTATO  │ │ ⚠️ HISTÓRIA │ │ ✅ SERVIÇOS│  │
│  │             │ │             │ │             │ │           │  │
│  │ TechSol    │ │ 11 99999... │ │ [Editar]    │ │ 3 items   │  │
│  │ Tecnologia │ │ @techsol   │ │ Pendente!   │ │           │  │
│  │ █ █        │ │ São Paulo  │ │             │ │           │  │
│  └─────────────┘ └─────────────┘ └─────────────┘ └───────────┘  │
│                                                                  │
│  ⚠️ 1 pendência: Texto "Sobre Nós" está vazio                   │
│                                                                  │
│             [ 🚀 PUBLICAR SITE ] (desabilitado)                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎯 Objetivos

1. **Reduzir fricção**: Usuário fala naturalmente, IA extrai os dados
2. **Aumentar conversão**: Processo mais envolvente = menos abandono
3. **Manter controle**: Formulário disponível para revisão/edição manual
4. **Flexibilidade**: Suporte a texto E áudio
5. **Inteligência**: IA sugere melhorias, cores, textos profissionais
6. **Gamificação**: "Nível de Poder do Site" + Conquistas mantêm engajamento
7. **Validação no Final**: Sem interrupções durante a conversa

---

## 📁 Arquivos Criados

```
src/
  core/
    services/
      conversational-ai.service.js     ✅ # Estado global + lógica do chat
      speech-to-text.service.js        ✅ # Transcrição de áudio (Web Speech API)
      
  shared/
    Components/
      ConversationalOnboardingWizard.vue  ✅ # Componente principal
      ConversationalOnboarding/
        ChatMessage.vue                ✅ # Mensagem do chat
        FormPreview.vue                ✅ # Preview do formulário lateral
        PreviewField.vue               ✅ # Campo individual com shimmer
        ReviewCard.vue                 ✅ # Card da Mesa de Edição

api/
  ai/
    conversational-onboarding.php      ✅ # Endpoint principal (Gemini)
    suggest-colors.php                 ✅ # Sugestão de cores por ramo

docs/
  CONVERSATIONAL-ONBOARDING-ASSISTANT.md ✅ # Este documento
```

---

## 🎮 Sistema de Gamificação

### Barra de "Potencial do Site"

```
⚡ POTENCIAL DE CONVERSÃO DO SEU SITE

    0%                    50%                   100%
    ├──────────────────────┼──────────────────────┤
    ████████████████████████░░░░░░░░░░░░░░░░░░░░░░
                           ▲
                          68%
    
    🎯 Para atingir 100%:
    • Adicione uma imagem da equipe (+8%)
    • Complete a história da empresa (+12%)
    • Defina cores personalizadas (+12%)
```

### Sistema de Conquistas (Achievements)

| Conquista | Trigger | XP |
|-----------|---------|-----|
| 🎨 **Identidade Desbloqueada** | Preencher nome + ramo + tagline | +15 |
| 📞 **Conexão Estabelecida** | WhatsApp + 1 rede social | +10 |
| 📖 **História Masterizada** | Texto sobre a empresa | +20 |
| ⚙️ **Catálogo de Serviços** | 3+ serviços cadastrados | +15 |
| 🎯 **FAQ Estratégico** | 5+ perguntas frequentes | +10 |
| 💎 **Perfil Completo** | 100% preenchido | +30 |
| 🚀 **Pronto pro Lançamento** | Validação final aprovada | +50 |

### Níveis do Site

```javascript
const siteLevels = [
  { level: 1, name: 'Rascunho', minXP: 0, icon: '📝' },
  { level: 2, name: 'Básico', minXP: 25, icon: '🌱' },
  { level: 3, name: 'Estruturado', minXP: 50, icon: '🏗️' },
  { level: 4, name: 'Profissional', minXP: 75, icon: '💼' },
  { level: 5, name: 'Premium', minXP: 100, icon: '💎' },
  { level: 6, name: 'Lendário', minXP: 150, icon: '🚀' }
];
```

### Pop-up de Conquista

```vue
<!-- AchievementPopup.vue -->
<template>
  <transition name="achievement-slide">
    <div v-if="show" class="achievement-popup">
      <div class="achievement-glow"></div>
      <span class="achievement-icon">{{ achievement.icon }}</span>
      <div class="achievement-info">
        <span class="achievement-label">Conquista Desbloqueada!</span>
        <h4 class="achievement-name">{{ achievement.name }}</h4>
      </div>
      <span class="achievement-xp">+{{ achievement.xp }} XP</span>
    </div>
  </transition>
</template>
```

---

## 🏗️ Arquitetura Proposta

### Fluxo Geral

```
┌────────────────────────────────────────────────────────────────────┐
│                     INTERFACE HÍBRIDA                              │
├────────────────────────────────────────────────────────────────────┤
│                                                                    │
│  ┌──────────────────────┐    ┌──────────────────────────────────┐  │
│  │   CHAT ASSISTANT     │    │     FORMULÁRIO (REVISÃO)         │  │
│  │   (Lado Esquerdo)    │    │     (Lado Direito - Desktop)     │  │
│  │                      │    │                                  │  │
│  │  🤖 Mensagem IA      │───▶│  [Nome: TechSol        ✓]       │  │
│  │  👤 Mensagem User    │    │  [Ramo: Tecnologia     ✓]       │  │
│  │                      │    │  [Tagline: ...         ✓]       │  │
│  │  ┌────────────────┐  │    │  [Cor: █ #0066CC       ]       │  │
│  │  │ Input mensagem │  │    │                                  │  │
│  │  │ 🎤  💬  📎     │  │    │  ⚠️ Campos pendentes: 2          │  │
│  │  └────────────────┘  │    │                                  │  │
│  └──────────────────────┘    └──────────────────────────────────┘  │
│                                                                    │
│  [◀ Voltar] ─── Passo 1 de 8 ─── [Próximo ▶] ou [Modo Formulário]  │
└────────────────────────────────────────────────────────────────────┘
```

### Componentes do Sistema

```
src/
  shared/
    Components/
      ConversationalOnboarding/
        ConversationalOnboardingWizard.vue    # Componente principal (híbrido)
        ChatInterface.vue                      # Interface do chat
        ChatMessage.vue                        # Mensagem individual
        AudioRecorder.vue                      # Gravador de áudio
        FormPreview.vue                        # Preview do formulário lateral
        FieldConfirmation.vue                  # Confirmação inline de campos

  core/
    services/
      conversational-ai.service.js            # Lógica de extração/conversa
      speech-to-text.service.js               # Transcrição de áudio (Web Speech API)
      field-extractor.service.js              # Extrai campos da mensagem

api/
  ai/
    conversational-onboarding.php             # Endpoint principal do chat
    extract-fields.php                        # Extrai dados de texto livre
    suggest-improvements.php                  # Sugere melhorias
```

---

## 📝 Planejamento por Step

### 1️⃣ Step: Identidade da Marca (CONVERSÁVEL)

#### Campos a Coletar:
| Campo | Pode ser dito? | Como coletar? |
|-------|----------------|---------------|
| `companyName` | ✅ Sim | Extrair do texto |
| `businessType` | ✅ Sim | Mapear para opções |
| `tagline` | ✅ Sim | Extrair e sugerir melhor |
| `primaryColor` | ⚠️ Parcial | Nome/hex OU mostrar picker |
| `secondaryColor` | ⚠️ Parcial | Nome/hex OU sugestão IA |
| `voiceTone` | ✅ Sim | Perguntar estilo |
| `logo` | ❌ Não | Mostrar upload |
| `hasNoLogo` | ✅ Sim | "Não tenho logo ainda" |

#### Script do Assistente:

```
ABERTURA:
🤖 "Olá! Sou o assistente da Unli e vou te ajudar a criar seu site. 
    Vamos começar com a identidade da sua marca. 
    
    Me conta: qual o nome da sua empresa, o que vocês fazem 
    e se tiver, uma frase que resume seu negócio?"

APÓS RESPOSTA:
🤖 "Show! Deixa eu anotar aqui:
    
    ✅ Nome: [EXTRAÍDO]
    ✅ Ramo: [MAPEADO]  
    ✅ Frase: [EXTRAÍDO ou SUGERIDO]
    
    Tudo certo ou quer ajustar algo?"

CORES:
🤖 "Agora as cores! Você já tem cores definidas para sua marca?
    Pode me dizer o nome (tipo 'azul marinho') ou o código hex.
    
    💡 Se não tiver, posso sugerir com base no seu ramo!"

    [Opção A: Usuário diz cor]
    [Opção B: Mostrar color picker]
    [Opção C: IA sugere baseado no ramo]

TOM DE VOZ:
🤖 "Como você quer que os textos do site soem?
    
    👔 Profissional e sério
    🤝 Amigável e próximo  
    😎 Descontraído e jovem
    💎 Sofisticado e premium
    🔧 Técnico e detalhado
    🚀 Inspirador e motivacional
    
    Qual combina mais com você?"

LOGO:
🤖 "Você já tem um logotipo? Se tiver, pode enviar aqui!
    Se ainda não tem, não se preocupe - criaremos um texto 
    estilizado com o nome da empresa."
    
    [Botão Upload] [Checkbox: Ainda não tenho]
```

---

### 2️⃣ Step: Contato e Localização (CONVERSÁVEL)

#### Campos a Coletar:
| Campo | Pode ser dito? | Como coletar? |
|-------|----------------|---------------|
| `whatsapp` | ✅ Sim | Extrair número |
| `additionalPhones` | ✅ Sim | Extrair múltiplos |
| `socialNetworks` | ✅ Sim | Extrair URLs/@ |
| `hasPhysicalLocation` | ✅ Sim | "Tenho loja/escritório" |
| `endereço completo` | ✅ Sim | Extrair ou CEP |
| `businessHours` | ✅ Sim | Extrair horário |

#### Script do Assistente:

```
ABERTURA:
🤖 "Agora vamos aos contatos! Como os clientes podem te encontrar?
    
    Me passa:
    • Seu WhatsApp principal
    • Suas redes sociais (@instagram, facebook, etc)
    • Se você tem endereço físico"

APÓS RESPOSTA:
🤖 "Anotei! Confirma se está tudo certo:
    
    📱 WhatsApp: (11) 99999-9999
    📸 Instagram: @suaempresa
    📍 Endereço: Sim, vou perguntar
    
    Qual o CEP ou endereço completo?"

HORÁRIO:
🤖 "E o horário de funcionamento? Por exemplo:
    'Segunda a sexta das 9h às 18h, sábado até 13h'"
```

---

### 3️⃣ Step: Sobre a Empresa (CONVERSÁVEL)

#### Campos a Coletar:
| Campo | Pode ser dito? | Como coletar? |
|-------|----------------|---------------|
| `aboutSectionTitle` | ✅ Sim | Sugestões |
| `companyBio` | ✅ Sim | Fala livre → IA polir |
| `foundingYear` | ✅ Sim | Extrair |
| `founders` | ✅ Sim | Extrair |
| `aboutImage` | ❌ Não | Upload |
| `companyHighlights` | ✅ Sim | Extrair → sugerir |
| `mission/vision/values` | ✅ Sim | Fala livre → IA criar |

#### Script do Assistente:

```
ABERTURA:
🤖 "Agora a parte mais legal: a história da sua empresa! 🏢
    
    Me conta livremente:
    • Quando começou?
    • Como surgiu a ideia?
    • O que te motiva?
    • Quais são seus diferenciais?
    
    Pode falar à vontade, eu transformo em texto profissional!"

APÓS RESPOSTA LIVRE:
🤖 "Adorei a história! Com base no que você me disse, 
    criei este texto profissional:
    
    ───────────────────────────────
    'A [Empresa] nasceu em [ano] da paixão de [fundador] 
    por [área]. Desde então, já [conquista] e se tornou 
    referência em [diferencial]...'
    ───────────────────────────────
    
    Gostou ou quer que eu ajuste algo?"

DIFERENCIAIS:
🤖 "Identifiquei esses diferenciais no que você disse:
    
    🏆 10 anos de mercado
    ⭐ Atendimento personalizado  
    🛡️ Garantia de 90 dias
    
    Quer adicionar mais algum?"

MISSÃO/VISÃO:
🤖 "Quer incluir Missão, Visão e Valores no site?
    Se quiser, me conta o que te move e eu crio pra você!"
```

---

### 4️⃣ Step: Serviços (CONVERSÁVEL)

#### Script do Assistente:

```
ABERTURA:
🤖 "Vamos aos seus serviços! 🛠️
    
    Me lista o que vocês fazem. Pode ser simples:
    'Fazemos instalação elétrica, manutenção preventiva, 
    projetos residenciais e comerciais...'
    
    Eu estruturo tudo certinho!"

APÓS LISTA:
🤖 "Entendi! Vou criar cards para cada serviço:
    
    ⚡ Instalação Elétrica
       'Instalações seguras para residências e empresas...'
       
    🔧 Manutenção Preventiva  
       'Evite problemas com manutenção regular...'
    
    Quer adicionar descrições, preços ou imagens?"

PREÇOS:
🤖 "Sobre preços, você quer:
    
    1️⃣ Não mostrar preço (mais comum)
    2️⃣ Mostrar 'A partir de R$...'
    3️⃣ Preço fixo
    4️⃣ 'Sob consulta'"
```

---

### 5️⃣ Step: FAQ (CONVERSÁVEL)

```
ABERTURA:
🤖 "Agora as perguntas frequentes! Isso ajuda muito na conversão.
    
    Quais dúvidas seus clientes mais perguntam?
    
    💡 Ou, se preferir, me conta seu nicho que eu sugiro 
    perguntas que quebram objeções de venda!"

SE USUÁRIO DER NICHO:
🤖 "Para uma [clínica odontológica], sugiro essas perguntas:
    
    ❓ Vocês aceitam convênio?
    ❓ Como funciona a primeira consulta?
    ❓ Qual o tempo de tratamento para clareamento?
    ❓ Vocês atendem emergências?
    
    Quer usar essas ou prefere outras?"
```

---

### 6️⃣ Step: Configuração de Contato (SEMI-CONVERSÁVEL)

```
🤖 "Quase lá! Sobre o formulário de contato do site:
    
    Além de Nome e E-mail (que são obrigatórios), 
    você quer pedir mais alguma informação?
    
    Opções comuns:
    📱 Telefone/WhatsApp
    💬 Mensagem
    🏢 Nome da empresa
    📍 Cidade
    
    Me diz quais você quer!"

BOTÃO WHATSAPP:
🤖 "Quer um botão flutuante de WhatsApp no site?
    É aquele botãozinho verde que fica no canto da tela.
    A maioria dos clientes adora! 💬"
```

---

### 7️⃣ Step: Finalização (CONVERSÁVEL)

```
🤖 "Estamos quase prontos! 🎉
    
    Tem mais alguma observação, preferência ou detalhe 
    que você quer me contar?
    
    Por exemplo:
    • Cores específicas que não quer
    • Estilo de design preferido
    • Sites de inspiração
    
    💡 Também pode enviar imagens de referência!"

URGÊNCIA:
🤖 "E por último: qual a urgência do projeto?
    
    🏃 Urgente (até 3 dias)
    📅 Normal (até 7 dias)  
    🧘 Tranquilo (pode demorar)"
```

---

## 🛠️ Implementação Técnica

### 1. Estrutura de Mensagens

```javascript
// conversational-ai.service.js

const messageTypes = {
  ASSISTANT: 'assistant',      // Mensagem do assistente
  USER_TEXT: 'user_text',      // Texto digitado
  USER_AUDIO: 'user_audio',    // Áudio transcrito
  FIELD_CONFIRM: 'field_confirm', // Confirmação de campo
  SUGGESTION: 'suggestion',    // Sugestão de melhoria
  UPLOAD_REQUEST: 'upload',    // Pedido de upload
  FORM_UPDATE: 'form_update',  // Atualização silenciosa do form
};

// Estrutura de uma mensagem
const message = {
  id: 'msg_001',
  type: 'assistant',
  content: 'Qual o nome da sua empresa?',
  timestamp: Date.now(),
  fields: [], // Campos extraídos/atualizados
  suggestions: [], // Sugestões da IA
  actions: [ // Botões de ação rápida
    { label: 'Não sei ainda', action: 'skip' },
    { label: 'Me ajuda a criar', action: 'ai_suggest' }
  ]
};
```

### 2. Extração de Campos com Gemini

```php
// api/ai/extract-fields.php

$systemPrompt = <<<PROMPT
Você é um assistente que extrai informações de textos em português brasileiro para preencher formulários.

CAMPOS DISPONÍVEIS NO STEP ATUAL:
- companyName: Nome da empresa
- businessType: Ramo (mapear para: alimentacao, saude, beleza, educacao, tecnologia, etc)
- tagline: Frase de destaque
- whatsapp: Número com DDD
- instagram: @ ou URL
- foundingYear: Ano numérico

TAREFA: Extraia os campos do texto do usuário.

REGRAS:
1. Retorne JSON com os campos encontrados
2. Se não encontrou, não inclua o campo
3. Normalize dados (telefone: só números, ano: só 4 dígitos)
4. Para businessType, mapeie para os valores do enum

EXEMPLO:
Input: "Minha empresa é a Pizzaria do João, abri em 2018, meu whats é 11 99999-8888"

Output:
{
  "extracted": {
    "companyName": "Pizzaria do João",
    "businessType": "alimentacao",
    "foundingYear": 2018,
    "whatsapp": "11999998888"
  },
  "confidence": {
    "companyName": 0.95,
    "businessType": 0.90,
    "foundingYear": 1.0,
    "whatsapp": 1.0
  },
  "suggestions": {
    "tagline": "Sabor artesanal desde 2018"
  }
}
PROMPT;
```

### 3. Transcrição de Áudio

```javascript
// speech-to-text.service.js

export class SpeechToTextService {
  constructor() {
    this.recognition = null;
    this.isSupported = 'webkitSpeechRecognition' in window || 'SpeechRecognition' in window;
  }
  
  async transcribe() {
    return new Promise((resolve, reject) => {
      if (!this.isSupported) {
        // Fallback: usar API do Google ou enviar áudio para backend
        reject(new Error('Speech recognition não suportado'));
        return;
      }
      
      const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      this.recognition = new SpeechRecognition();
      this.recognition.lang = 'pt-BR';
      this.recognition.continuous = false;
      this.recognition.interimResults = false;
      
      this.recognition.onresult = (event) => {
        const transcript = event.results[0][0].transcript;
        resolve(transcript);
      };
      
      this.recognition.onerror = (event) => {
        reject(event.error);
      };
      
      this.recognition.start();
    });
  }
  
  stop() {
    if (this.recognition) {
      this.recognition.stop();
    }
  }
}
```

### 4. Componente Vue Principal

```vue
<!-- ConversationalOnboardingWizard.vue (estrutura) -->

<template>
  <div class="conversational-wizard" :class="{ 'split-view': showFormPreview }">
    
    <!-- ÁREA DE CHAT -->
    <section class="chat-section">
      <header class="chat-header">
        <div class="step-info">
          <span class="step-badge">Passo {{ currentStep + 1 }}/{{ totalSteps }}</span>
          <h2>{{ currentStepTitle }}</h2>
        </div>
        <button @click="toggleFormPreview" class="btn-toggle-form">
          {{ showFormPreview ? 'Ocultar' : 'Ver' }} Formulário
        </button>
      </header>
      
      <!-- MENSAGENS -->
      <div class="chat-messages" ref="messagesContainer">
        <ChatMessage 
          v-for="msg in messages" 
          :key="msg.id"
          :message="msg"
          @action="handleMessageAction"
          @field-edit="openFieldEditor"
        />
        
        <!-- Typing indicator -->
        <div v-if="isTyping" class="typing-indicator">
          <span></span><span></span><span></span>
        </div>
      </div>
      
      <!-- INPUT AREA -->
      <footer class="chat-input-area">
        <!-- Quick Actions (context-aware) -->
        <div v-if="quickActions.length" class="quick-actions">
          <button 
            v-for="action in quickActions" 
            :key="action.id"
            @click="handleQuickAction(action)"
            class="quick-action-btn"
          >
            {{ action.icon }} {{ action.label }}
          </button>
        </div>
        
        <!-- Input Field -->
        <div class="input-wrapper">
          <textarea 
            v-model="userInput"
            @keydown.enter.exact="sendMessage"
            placeholder="Digite sua mensagem..."
            rows="1"
          />
          
          <div class="input-actions">
            <button @click="startRecording" class="btn-audio" :class="{ recording: isRecording }">
              🎤
            </button>
            <button @click="sendMessage" class="btn-send" :disabled="!userInput.trim()">
              ➤
            </button>
          </div>
        </div>
        
        <!-- Upload Area (quando necessário) -->
        <div v-if="showUploadArea" class="upload-area">
          <input type="file" ref="fileInput" @change="handleFileUpload" :accept="currentUploadAccept" />
          <button @click="$refs.fileInput.click()">📎 Anexar arquivo</button>
        </div>
      </footer>
    </section>
    
    <!-- PREVIEW DO FORMULÁRIO (Desktop) -->
    <aside v-if="showFormPreview" class="form-preview-section">
      <FormPreview 
        :form-data="formData"
        :current-step="currentStepId"
        :fields="currentStepFields"
        @field-click="focusField"
        @field-edit="editFieldManually"
      />
    </aside>
    
  </div>
</template>
```

---

## 🎨 Tratamento de Campos Especiais

### Cores (Color Picker)

```javascript
// Estratégia para cores:

const colorHandling = {
  // 1. Usuário diz nome da cor
  fromName: {
    'azul': '#0066CC',
    'azul marinho': '#000080',
    'verde': '#28A745',
    'vermelho': '#DC3545',
    // ... mapeamento completo
  },
  
  // 2. Usuário diz hexadecimal
  fromHex: (text) => {
    const hexMatch = text.match(/#[0-9A-Fa-f]{6}/);
    return hexMatch ? hexMatch[0] : null;
  },
  
  // 3. IA sugere baseado no ramo
  suggestByBusiness: {
    'saude': { primary: '#28A745', secondary: '#17A2B8', reason: 'Verde transmite saúde e cuidado' },
    'tecnologia': { primary: '#0066CC', secondary: '#6F42C1', reason: 'Azul transmite confiança e inovação' },
    'alimentacao': { primary: '#FF6B35', secondary: '#FFC107', reason: 'Cores quentes abrem o apetite' },
    'beleza': { primary: '#E83E8C', secondary: '#6F42C1', reason: 'Tons sofisticados para estética' },
    'juridico': { primary: '#212529', secondary: '#0066CC', reason: 'Cores sóbrias transmitem seriedade' },
    // ...
  },
  
  // 4. Mostrar picker visual inline no chat
  showPicker: true,
};
```

**No chat:**
```
🤖 "Sobre as cores da sua marca, você:

    1️⃣ Já tem cores definidas? Me diz (ex: 'azul marinho' ou '#003366')
    
    2️⃣ Quer que eu sugira? Com base em [Tecnologia], recomendo:
        █ Azul Profissional (#0066CC) - transmite confiança
        █ Roxo Inovação (#6F42C1) - como secundária
    
    3️⃣ Prefere escolher visualmente?"
    
    [🎨 Abrir seletor de cores]
```

### Upload de Arquivos

```javascript
// Quando IA pede upload, mostra área inline:

const uploadMessages = {
  logo: {
    message: "Você tem um logotipo? Pode enviar aqui! Aceito PNG, JPG ou SVG.",
    accept: 'image/*',
    fallback: { 
      label: 'Ainda não tenho logo',
      field: 'hasNoLogo',
      value: true
    }
  },
  aboutImage: {
    message: "Que tal uma foto da equipe, fachada ou ambiente de trabalho? Gera muita confiança!",
    accept: 'image/*',
    fallback: { 
      label: 'Prefiro não incluir imagem',
      field: 'skipAboutImage',
      value: true
    }
  }
};
```

---

## 📊 Fluxo de Estados

```
┌─────────────────────────────────────────────────────────────────┐
│                     MÁQUINA DE ESTADOS                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  [IDLE] ──────▶ [WAITING_INPUT] ──────▶ [PROCESSING]           │
│     │               │                        │                  │
│     │               │ user sends             │ Gemini API       │
│     │               │ text/audio             │ response         │
│     │               ▼                        ▼                  │
│     │         [RECORDING]              [EXTRACTING]             │
│     │           (audio)                  (fields)               │
│     │               │                        │                  │
│     │               │ transcribed            │ fields found     │
│     │               ▼                        ▼                  │
│     │         [TRANSCRIBING]          [CONFIRMING]              │
│     │               │                   (show extracted)        │
│     │               └───────────▶────────────┘                  │
│     │                                        │                  │
│     │                                        │ user confirms    │
│     │                                        ▼                  │
│     │                                 [UPDATING_FORM]           │
│     │                                        │                  │
│     │                                        │ check if step    │
│     │                                        │ is complete      │
│     │                                        ▼                  │
│     │                              [NEXT_QUESTION] ───▶ [IDLE]  │
│     │                                   or                      │
│     │                              [STEP_COMPLETE]              │
│     │                                        │                  │
│     └────────────────────────────────────────┘                  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔌 API Endpoints

### 1. `POST /api/ai/conversational-onboarding.php`

**Request:**
```json
{
  "session_id": "onb_12345",
  "step": "identity",
  "messages": [
    { "role": "user", "content": "Minha empresa é a TechSol, somos de TI..." }
  ],
  "current_form_data": {
    "companyName": null,
    "businessType": null
  },
  "voice_tone": "profissional"
}
```

**Response:**
```json
{
  "success": true,
  "assistant_message": "Perfeito! Já anotei aqui:\n\n✅ Nome: TechSol\n✅ Ramo: Tecnologia\n\nQual o tom de voz da sua marca?",
  "extracted_fields": {
    "companyName": "TechSol",
    "businessType": "tecnologia"
  },
  "suggestions": {
    "tagline": "Soluções tecnológicas para o seu negócio"
  },
  "next_question": {
    "field": "voiceTone",
    "question": "Qual o tom de voz da sua marca?",
    "options": ["profissional", "amigavel", "descontraido"]
  },
  "step_progress": 0.4,
  "pending_fields": ["primaryColor", "voiceTone", "logo"]
}
```

### 2. `POST /api/ai/suggest-colors.php`

```json
// Request
{
  "business_type": "tecnologia",
  "company_name": "TechSol"
}

// Response
{
  "success": true,
  "suggestions": [
    {
      "primary": "#0066CC",
      "secondary": "#6F42C1",
      "name": "Tech Moderno",
      "reason": "Azul transmite confiança, roxo inovação"
    },
    {
      "primary": "#212529",
      "secondary": "#17A2B8",
      "name": "Tech Sóbrio",
      "reason": "Preto elegante com toque tecnológico"
    }
  ]
}
```

---

## 📱 Responsividade

### Desktop (>= 1024px)
- Layout split: Chat (60%) | Form Preview (40%)
- Form preview sempre visível (colapsável)

### Tablet (768px - 1023px)
- Chat full width
- Form preview em modal/drawer lateral

### Mobile (< 768px)
- Chat full width
- Form preview em bottom sheet
- Botão flutuante "📋 Ver Formulário"

---

## ⚡ Otimizações de Performance

1. **Streaming de respostas**: Usar streaming do Gemini para resposta gradual
2. **Debounce de typing**: Aguardar 500ms antes de enviar
3. **Cache de prompts**: Prompts do sistema em cache
4. **Lazy load**: Carregar AudioRecorder só quando necessário
5. **IndexedDB**: Salvar histórico de chat localmente

---

## 🎯 Métricas de Sucesso

| Métrica | Antes (Form) | Meta (Chat) |
|---------|--------------|-------------|
| Taxa de conclusão | 65% | 85%+ |
| Tempo médio | 12 min | 8 min |
| Satisfação (NPS) | 7.2 | 9.0+ |
| Campos em branco | 35% | 10% |

---

## 📅 Fases de Implementação

### Fase 1 - MVP (1-2 semanas)
- [ ] Componente básico de chat
- [ ] Integração com Gemini para extração
- [ ] Steps: Identidade + Contato
- [ ] Form preview lateral
- [ ] Apenas texto (sem áudio)

### Fase 2 - Completo (2-3 semanas)
- [ ] Todos os steps com scripts
- [ ] Transcrição de áudio (Web Speech API)
- [ ] Sugestões de cores baseadas no ramo
- [ ] Upload inline no chat
- [ ] Persistência do histórico

### Fase 3 - Polish (1 semana)
- [ ] Animações e micro-interações
- [ ] Modo "apenas formulário" (fallback)
- [ ] Testes A/B chat vs form
- [ ] Analytics de uso

---

## 🔧 Dependências

```json
{
  "novas_dependencias": {
    "vue": "existente",
    "@vueuse/core": "^10.x (composables úteis)",
    "marked": "^11.x (render markdown nas mensagens)",
    "dompurify": "^3.x (sanitizar HTML)"
  },
  "apis_externas": {
    "gemini": "2.0 Flash (já integrado)",
    "web_speech_api": "nativo do browser"
  }
}
```

---

## 📝 Conclusão

Este sistema transforma o onboarding de uma tarefa burocrática em uma **conversa natural**, mantendo:

1. ✅ **Controle total** - Formulário disponível para edição
2. ✅ **Flexibilidade** - Texto ou áudio
3. ✅ **Inteligência** - IA extrai, sugere e melhora
4. ✅ **Experiência premium** - Diferencial de mercado

O resultado é um processo mais **humano**, **rápido** e com **maior taxa de conversão**.

---

*Última atualização: Janeiro 2026*
