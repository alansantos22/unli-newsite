# 💰 Análise e Otimização de Custos - Sistema Conversacional

## 📊 Diagnóstico Atual

### Gastos de Token por Requisição (Estimativas)

**System Prompt:** ~1.500 tokens
- Prompt base: ~800 tokens
- Instruções por step: ~300-400 tokens
- Campos preenchidos JSON: ~200-400 tokens

**Histórico de Conversa:** ~100-500 tokens por mensagem
- Sem limite atual: pode crescer indefinidamente
- Mensagem média: 50-150 tokens

**Mensagem do Usuário:** ~20-300 tokens
- Limite atual: 2000 caracteres = ~500 tokens

**Resposta do Gemini:** ~200-800 tokens
- Limite configurado: 1024 tokens

### 🔴 Problemas Identificados

1. **Histórico Ilimitado** 
   - ❌ Todo o histórico é enviado em cada requisição
   - ❌ Conversa com 20 mensagens = ~4.000 tokens extras

2. **System Prompt Repetitivo**
   - ❌ Mesmo prompt enorme em cada requisição
   - ❌ Instruções por step muito verbosas

3. **Sem Limite de Input do Usuário**
   - ❌ Usuário pode escrever textos gigantes
   - ❌ Frontend não limita caracteres

4. **Campos Completos no Contexto**
   - ❌ Sempre envia todos os campos preenchidos
   - ❌ Não comprime informações já extraídas

---

## 🎯 Estratégias de Mitigação

### 1️⃣ **LIMITAR INPUT DO USUÁRIO** ⭐⭐⭐⭐⭐
**Impacto:** ALTO | **Esforço:** BAIXO

#### Backend (PHP):
```php
// Aumentar corte de 2000 para 500 caracteres
$userMessage = mb_substr($userMessage, 0, 500);
```

#### Frontend (Vue):
```javascript
// No textarea, adicionar limite visual
const MAX_CHARS = 500;

const handleInput = (e) => {
  if (e.target.value.length > MAX_CHARS) {
    e.target.value = e.target.value.slice(0, MAX_CHARS);
  }
};
```

#### UI (Contador de Caracteres):
```vue
<div class="char-counter">
  {{ userMessage.length }} / 500
</div>
```

**Economia Estimada:** 40-60% nos tokens de entrada

---

### 2️⃣ **JANELA DESLIZANTE DE HISTÓRICO** ⭐⭐⭐⭐⭐
**Impacto:** MUITO ALTO | **Esforço:** MÉDIO

#### Estratégia: Enviar apenas últimas N mensagens

```php
function buildConversationHistory(array $messages, int $maxMessages = 8): array {
    // Pegar apenas as últimas 8 mensagens (4 trocas)
    $recent = array_slice($messages, -$maxMessages);
    
    $history = [];
    foreach ($recent as $msg) {
        if ($msg['type'] === 'assistant') {
            $history[] = [
                'role' => 'model',
                'parts' => [['text' => $msg['content']]]
            ];
        } elseif (in_array($msg['type'], ['user_text', 'user_audio'])) {
            $history[] = [
                'role' => 'user',
                'parts' => [['text' => $msg['content']]]
            ];
        }
    }
    
    return $history;
}
```

**Vantagens:**
- ✅ Reduz tokens proporcionalmente ao tamanho da conversa
- ✅ Mantém contexto imediato (últimas 4 trocas são suficientes)
- ✅ IA ainda tem os dados no $formData para contexto geral

**Economia Estimada:** 70-80% em conversas longas

---

### 3️⃣ **COMPRESSÃO DO SYSTEM PROMPT** ⭐⭐⭐⭐
**Impacto:** ALTO | **Esforço:** MÉDIO

#### Reduzir Instruções por Step

**Antes:** ~300-400 tokens por step
**Depois:** ~100-150 tokens

```php
function getStepInstructions(string $step): string {
    $instructions = [
        'identity' => "Step: Identidade. Extraia: companyName, businessType, frase, primaryColor, secondaryColor, voiceTone. Sugira cores baseado no ramo. Se usuário disser 'não tenho logo', defina hasNoLogo=true.",
        
        'contact' => "Step: Contato. Extraia: whatsapp, redes sociais, endereço (se tiver). Aceite @usuario ou URLs. Se 'não tenho loja física', hasPhysicalLocation=false.",
        
        'about' => "Step: História. Reformule textos desorganizados em companyBio profissional. Extraia foundingYear se mencionado. Identifique diferenciais para companyHighlights.",
        
        'services' => "Step: Serviços. Estruture lista: services: [{name, shortDescription}]. Crie descrições profissionais. Pergunte sobre garantia.",
        
        'faq' => "Step: FAQ. Sugira 5-7 perguntas que quebram objeções de venda. Estruture: faqItems: [{question, answer}].",
        
        'finalization' => "Step: Final. Pergunte urgência (urgent/normal/relaxed), aceite URLs de inspiração. Quando completo: step_complete=true."
    ];
    
    return $instructions[$step] ?? '';
}
```

**Economia Estimada:** 50-60% no system prompt

---

### 4️⃣ **REMOVER CAMPOS JÁ PREENCHIDOS DO CONTEXTO** ⭐⭐⭐
**Impacto:** MÉDIO | **Esforço:** MÉDIO

#### Enviar apenas campos do step atual + campos que faltam

```php
function buildSystemPrompt(string $step, array $formData, string $voiceTone): string {
    $currentStepFields = getStepFields($step);
    
    // Filtrar apenas campos relevantes
    $relevantData = [];
    foreach ($currentStepFields as $field) {
        if (isset($formData[$field]) && !empty($formData[$field])) {
            $relevantData[$field] = $formData[$field];
        }
    }
    
    // Enviar apenas dados do step atual
    $contextData = json_encode($relevantData, JSON_UNESCAPED_UNICODE);
    
    // Calcular campos faltantes
    $missingFields = [];
    foreach ($currentStepFields as $field) {
        if (!isset($formData[$field]) || empty($formData[$field])) {
            $missingFields[] = $field;
        }
    }
    
    // ... resto do prompt
}
```

**Economia Estimada:** 30-40% conforme formulário é preenchido

---

### 5️⃣ **CACHING DO SYSTEM PROMPT** ⭐⭐⭐⭐
**Impacto:** ALTO | **Esforço:** BAIXO (se Gemini suportar)

#### Verificar se Gemini 2.0 Flash suporta context caching

```php
'cachedContent' => [
    'model' => 'models/gemini-2.0-flash',
    'systemInstruction' => ['parts' => [['text' => $basePrompt]]],
    'ttl' => '3600s' // 1 hora
]
```

**Economia Estimada:** 50% no prompt base (se suportado)

---

### 6️⃣ **EXPANDIR EXTRAÇÃO LOCAL** ⭐⭐⭐⭐
**Impacto:** MÉDIO-ALTO | **Esforço:** ALTO

#### Adicionar mais padrões que não precisam de IA

```php
// Anos de fundação
if (preg_match('/\b(19\d{2}|20[0-2]\d)\b/', $message, $matches)) {
    $year = (int)$matches[1];
    return [..., 'extracted_fields' => ['foundingYear' => $year]];
}

// URLs de redes sociais
if (preg_match('/(facebook\.com|fb\.com)\/([a-zA-Z0-9_\.]+)/', $message, $matches)) {
    return [..., 'extracted_fields' => ['facebook' => $matches[0]]];
}

// Confirmações + escolha de cor
if (preg_match('/\b(sim|ok|isso|correto)\b.*\b(azul|vermelho|verde)\b/i', $messageLower)) {
    // Extrair cor + confirmar campo anterior
}

// Negações simples
if (preg_match('/\b(não|nao|nada|nenhum)\b/i', $messageLower)) {
    // Pular campo ou definir como null
}
```

**Economia Estimada:** 20-30% das requisições evitadas

---

### 7️⃣ **REDUZIR maxOutputTokens** ⭐⭐⭐
**Impacto:** MÉDIO | **Esforço:** BAIXO

```php
'generationConfig' => [
    'temperature' => 0.7,
    'maxOutputTokens' => 512, // Reduzir de 1024 para 512
    'responseMimeType' => 'application/json'
]
```

**Vantagens:**
- ✅ Força respostas mais concisas
- ✅ Reduz custo de output em 50%

**Desvantagens:**
- ⚠️ Pode truncar respostas complexas (testar!)

---

### 8️⃣ **DEBOUNCE NO FRONTEND** ⭐⭐
**Impacto:** BAIXO | **Esforço:** BAIXO

#### Evitar envios acidentais enquanto usuário digita

```javascript
const debouncedSend = debounce(() => {
  sendMessage();
}, 800); // Espera 800ms após parar de digitar
```

**Economia Estimada:** 10-15% (evita envios acidentais)

---

### 9️⃣ **SUGESTÕES DE BOTÕES (REDUZ DIGITAÇÃO)** ⭐⭐⭐⭐
**Impacto:** MÉDIO | **Esforço:** MÉDIO

#### Já implementado: botões de sugestão de cores

**Expandir para:**
- ✅ Ramos de atuação (alimentação, saúde, etc.)
- ✅ Tom de voz (profissional, amigável, etc.)
- ✅ Urgência (urgente, normal, tranquilo)
- ✅ Sim/Não para confirmações

**Economia:** Mensagens de 1 token vs 50-100 tokens

---

### 🔟 **RATE LIMITING AGRESSIVO** ⭐⭐
**Impacto:** BAIXO-MÉDIO | **Esforço:** BAIXO

```php
// Reduzir de 30 para 15 mensagens/minuto
if ($_SESSION[$rateKey]['count'] > 15) {
    // ...
}
```

---

## 📈 Resumo de Economia Projetada

| Estratégia | Economia | Prioridade | Esforço |
|------------|----------|------------|---------|
| 1. Limitar Input (500 chars) | 40-60% | ⭐⭐⭐⭐⭐ | Baixo |
| 2. Janela Deslizante (8 msgs) | 70-80% | ⭐⭐⭐⭐⭐ | Médio |
| 3. Compressão do Prompt | 50-60% | ⭐⭐⭐⭐ | Médio |
| 4. Filtrar Campos Irrelevantes | 30-40% | ⭐⭐⭐ | Médio |
| 5. Context Caching | 50% | ⭐⭐⭐⭐ | Baixo* |
| 6. Expandir Extração Local | 20-30% | ⭐⭐⭐⭐ | Alto |
| 7. Reduzir maxOutputTokens | 50% output | ⭐⭐⭐ | Baixo |
| 8. Debounce | 10-15% | ⭐⭐ | Baixo |
| 9. Botões de Sugestão | 20-30% | ⭐⭐⭐⭐ | Médio |
| 10. Rate Limiting | 10-20% | ⭐⭐ | Baixo |

*Depende do suporte da API

---

## 🚀 Plano de Implementação Recomendado

### **Fase 1: Ganhos Rápidos** (1-2 horas)
1. ✅ Limitar input para 500 caracteres
2. ✅ Janela deslizante de 8 mensagens
3. ✅ Reduzir maxOutputTokens para 600
4. ✅ Adicionar contador de caracteres no UI

**Economia Total Fase 1:** ~70-80%

---

### **Fase 2: Otimização Estrutural** (2-4 horas)
5. ✅ Compactar system prompt
6. ✅ Filtrar apenas campos do step atual
7. ✅ Adicionar mais botões de sugestão
8. ✅ Expandir extração local (anos, URLs, confirmações)

**Economia Total Fase 2:** ~85-90%

---

### **Fase 3: Polimento** (1-2 horas)
9. ✅ Implementar debounce
10. ✅ Rate limiting mais agressivo
11. ✅ Investigar context caching (se disponível)

**Economia Total Fase 3:** ~90-95%

---

## 💡 Métricas para Monitorar

### Antes da Otimização
- Tokens por requisição: ~2.500-4.000
- Custo por conversa (20 msgs): ~$0.05-0.08

### Depois da Otimização (Fase 1)
- Tokens por requisição: ~600-1.000
- Custo por conversa: ~$0.01-0.02
- **Economia: 75-80%**

### Depois da Otimização (Fase 2+3)
- Tokens por requisição: ~400-700
- Custo por conversa: ~$0.008-0.015
- **Economia: 85-90%**

---

## ⚠️ Considerações Importantes

### Não Comprometer a Experiência
- ✅ 500 caracteres é suficiente para respostas normais
- ✅ 8 mensagens de histórico mantém contexto adequado
- ✅ Extração local acelera interação
- ⚠️ Testar maxOutputTokens=600 vs 512 (pode truncar)

### Testes Necessários
1. Conversa com 20+ mensagens (verificar contexto)
2. Usuário digitando textos longos (corte gracioso)
3. Respostas complexas (garantir que não trunca)
4. Extração local de novos padrões (precisão)

---

## 🔧 Arquivos a Modificar

### Fase 1:
- `api/ai/conversational-onboarding.php` (linha 86, 152, 420)
- `src/shared/Components/ConversationalOnboardingWizard.vue` (textarea)

### Fase 2:
- `api/ai/conversational-onboarding.php` (buildSystemPrompt, getStepInstructions)
- `src/core/services/conversational-ai.service.js` (adicionar botões)

### Fase 3:
- `src/shared/Components/ConversationalOnboardingWizard.vue` (debounce)
- `api/ai/conversational-onboarding.php` (rate limiting)

---

## 📝 Conclusão

Implementando apenas a **Fase 1** (ganhos rápidos), você consegue uma economia de **~75-80%** em custos de tokens, sem comprometer a experiência do usuário.

**Investimento:** 1-2 horas de desenvolvimento
**Retorno:** Redução de custo de $0.05 para $0.01 por conversa

Quer que eu implemente alguma dessas otimizações agora?
