# Fix: Sugestões Mostrando Apenas ":"

## 🐛 Problema

Quando o bot envia sugestões, a UI mostra apenas ":" em vez do conteúdo real das sugestões.

**Sintomas:**
- Cabeçalho "Sugestão da IA:" aparece corretamente
- Boxes de sugestões são renderizados (estrutura OK)
- Mas o conteúdo mostra apenas ":" em vez do texto sugerido

## 🔍 Diagnóstico

O problema está em **duas camadas**:

### 1. Backend (PHP) - Instruções incompletas
O prompt do Gemini tinha estrutura de sugestões, mas **não tinha instruções claras** sobre:
- QUANDO enviar sugestões
- QUAL estrutura usar (`{field, value}`)
- QUAIS campos aceitam sugestões

### 2. Frontend - Sem logs de debug
Não havia logs para verificar:
- O que está vindo da API
- Como os dados estão sendo estruturados
- Se o componente está recebendo dados corretos

## ✅ Solução Implementada

### Backend: `api/ai/conversational-onboarding.php`

**Adicionado no prompt (linhas 218-246):**

```php
"suggestions": [
  {"field": "frase", "value": "Opcao 1 sugerida"},
  {"field": "frase", "value": "Opcao 2 sugerida"}
],

SUGESTOES (quando enviar):
- Envie 2-3 alternativas quando o usuario der resposta vaga
- Campos que aceitam sugestoes: frase, companyBio, mission, vision, services (shortDescription)
- Estrutura: {"field": "nomeDoCampo", "value": "texto alternativo sugerido"}
- Exemplo: usuario diz "ajude-me com a frase" -> retorne 3 frases no array suggestions
- Se usuario der resposta clara e especifica, deixe suggestions = []
```

**Adicionado log de debug (linha 419):**
```php
error_log("🤖 [Gemini Response] Raw text: " . substr($generatedText, 0, 200));
if ($result && isset($result['suggestions'])) {
    error_log("🔍 [Gemini Response] Suggestions: " . json_encode($result['suggestions']));
}
```

### Frontend: `conversational-ai.service.js`

**Adicionado log antes de adicionar mensagem (linha 627):**
```javascript
if (response.suggestions && response.suggestions.length > 0) {
  console.log('🔍 [Service] Sugestões da API:', response.suggestions);
}
```

### Frontend: `ChatMessage.vue`

**Adicionado log no setup (linhas 126-132):**
```javascript
if (props.message.suggestions && props.message.suggestions.length > 0) {
  console.log('🔍 [ChatMessage] Sugestões recebidas:', props.message.suggestions);
  props.message.suggestions.forEach((sug, idx) => {
    console.log(`  [${idx}] field: "${sug.field}", value: "${sug.value}"`);
  });
}
```

## 🧪 Como Testar

### 1. Limpar cache e recarregar
```bash
# No terminal
npm run serve
```

### 2. Teste no onboarding conversacional

**Cenário 1: Pedir ajuda com frase**
```
Usuário: "ajude-me com a frase"
Esperado: Bot retorna 2-3 frases sugeridas na UI
```

**Cenário 2: Resposta vaga sobre biografia**
```
Usuário: "não sei o que escrever sobre a empresa"
Esperado: Bot sugere 2-3 textos de biografia
```

**Cenário 3: Resposta clara e específica**
```
Usuário: "A frase é: Transformando sonhos em realidade"
Esperado: Sem sugestões (campo já preenchido)
```

### 3. Verificar logs

**Console do navegador (F12):**
```
🔍 [Service] Sugestões da API: [{field: "frase", value: "..."}]
🔍 [ChatMessage] Sugestões recebidas: [{...}]
  [0] field: "frase", value: "Sua solução completa..."
  [1] field: "frase", value: "Inovação que transforma..."
```

**Logs do PHP (arquivo error_log do servidor):**
```
🤖 [Gemini Response] Raw text: {"success":true,"assistant_message":"Que legal!...
🔍 [Gemini Response] Suggestions: [{"field":"frase","value":"..."}]
```

## 🎯 Estrutura Esperada

**API Response (PHP → JS):**
```json
{
  "success": true,
  "assistant_message": "Legal! Vou sugerir algumas frases...",
  "extracted_fields": {},
  "suggestions": [
    {
      "field": "frase",
      "value": "Transformando ideias em resultados concretos"
    },
    {
      "field": "frase",
      "value": "Excelência e inovação em cada projeto"
    }
  ]
}
```

**Message Object (Vue component):**
```javascript
{
  type: 'assistant',
  content: 'Legal! Vou sugerir algumas frases...',
  suggestions: [
    { field: 'frase', value: 'Transformando ideias em resultados concretos' },
    { field: 'frase', value: 'Excelência e inovação em cada projeto' }
  ],
  timestamp: 1234567890
}
```

## 🔧 Troubleshooting

### Sugestões ainda mostram ":"

**Verificar:**
1. ✅ Logs aparecem no console? Se não, o problema está no backend
2. ✅ Estrutura do array está correta? `[{field, value}]`
3. ✅ Gemini está retornando suggestions? Verificar error_log do PHP
4. ✅ Template do ChatMessage.vue está correto? (já está OK)

**Se logs mostram `suggestions: []`:**
- Gemini não entendeu que deveria sugerir
- Testar com prompts mais explícitos: "me dê 3 opções de frase"

**Se logs mostram estrutura estranha:**
```javascript
// ❌ Errado
suggestions: ["opcao1", "opcao2"]

// ✅ Correto
suggestions: [
  {field: "frase", value: "opcao1"},
  {field: "frase", value: "opcao2"}
]
```
→ Problema no parse do PHP, verificar `callGeminiAPI()`

## 📝 Próximos Passos

Se o problema persistir após essas mudanças:

1. **Adicionar fallback visual**
   - Se `suggestion.value` estiver vazio, mostrar `suggestion.field`
   - Ou esconder a suggestion se não tiver value

2. **Validar no backend**
   - Antes de retornar, verificar se todas suggestions têm field e value
   - Filtrar suggestions inválidas

3. **Melhorar prompt**
   - Adicionar mais exemplos de suggestions bem formatadas
   - Reforçar que value deve ser texto completo, não apenas ":"

## 📊 Status

- ✅ Backend: Instruções de sugestões adicionadas
- ✅ Backend: Logs de debug implementados
- ✅ Frontend: Logs no service implementados  
- ✅ Frontend: Logs no component implementados
- ⏳ **Aguardando teste do usuário**

---

**Última atualização:** {{TIMESTAMP}}
**Arquivos modificados:**
- `api/ai/conversational-onboarding.php`
- `src/core/services/conversational-ai.service.js`
- `src/shared/Components/ConversationalOnboarding/ChatMessage.vue`
