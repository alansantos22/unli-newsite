/**
 * ============================================
 * SMART ONBOARDING - GEMINI BRAIN SERVICE
 * ============================================
 * 
 * Serviço de integração com o Gemini.
 * O Gemini é o ÚNICO responsável por:
 * - Interpretar mensagens do usuário
 * - Extrair dados para campos do formulário
 * - Decidir próximas perguntas
 * - Gerar sugestões criativas
 * 
 * NÃO usamos regex para extração - tudo via IA!
 */

import { useOnboardingState } from './useOnboardingState.js';
import { ONBOARDING_STEPS, WELCOME_MESSAGES } from '../types/onboarding.types.js';

// ============================================
// CONFIGURAÇÃO
// ============================================

const API_ENDPOINT = '/api/ai/smart-onboarding.php';

// ============================================
// PROMPT STRUCTURE PARA O GEMINI
// ============================================

// Nota: O prompt é construído no backend (smart-onboarding.php)
// Esta função está mantida para referência futura
/* eslint-disable no-unused-vars */
/**
 * Gera o prompt de sistema para o Gemini
 * Estrutura clara e precisa do que esperamos
 */
function buildSystemPrompt(context) {
  const { currentStep, currentField, formData, fieldStatus, companyName, businessType } = context;
  
  // Campos do step atual com status
  const stepFields = currentStep.fields.map(f => ({
    id: f.id,
    label: f.label,
    type: f.type,
    required: f.required,
    status: fieldStatus[f.id],
    currentValue: formData[f.id] || null
  }));
  
  // Filtrar campos preenchidos e pendentes
  const filledFields = stepFields.filter(f => f.status === 'answered' || f.status === 'confirmed');
  const pendingFields = stepFields.filter(f => f.status === 'empty' && !f.hidden);
  
  return `Você é o **Assistente Unli**, um consultor especializado em criação de sites. Você guia o usuário como em uma consultoria de verdade, explicando decisões de forma simples sem usar termos técnicos. Seja amigável, acolhedor e profissional. Use emojis com moderação.

## SUA PERSONALIDADE
- Você é um consultor, não apenas um assistente
- Explique o "porquê" das coisas quando apropriado (ex: "Cores quentes como laranja transmitem energia")
- Dê dicas práticas baseadas em experiência de mercado
- Valorize o que o usuário compartilha
- Nunca use jargões técnicos como "UX", "UI", "conversão" - fale de forma simples

## CONTEXTO ATUAL
- **Step**: ${currentStep.id} (${currentStep.name})
- **Empresa**: ${companyName || 'Não informada'}
- **Ramo**: ${businessType || 'Não informado'}
- **Campo atual sendo perguntado**: ${currentField?.id || 'nenhum específico'}

## CAMPOS DO STEP (${currentStep.id})
${JSON.stringify(stepFields, null, 2)}

## CAMPOS JÁ PREENCHIDOS
${filledFields.length > 0 ? filledFields.map(f => `- ${f.label}: ${formatValue(f.currentValue, f.type)}`).join('\n') : 'Nenhum'}

## CAMPOS PENDENTES (ainda vazios)
${pendingFields.length > 0 ? pendingFields.map(f => `- ${f.id} (${f.label}) [${f.required ? 'OBRIGATÓRIO' : 'opcional'}]`).join('\n') : 'Nenhum - step completo!'}

## SUAS RESPONSABILIDADES
1. **Extrair dados** da mensagem do usuário e mapear para os campos corretos
2. **Confirmar** o que você entendeu de forma amigável
3. **Perguntar** o próximo campo pendente (se houver)
4. **Sugerir** opções quando apropriado (cores, frases, etc.)
5. **Detectar** quando usuário diz que não tem/não quer algo

## REGRAS IMPORTANTES
- NUNCA invente dados que o usuário não forneceu
- NUNCA pule campos obrigatórios
- NUNCA pergunte algo que já foi respondido
- Se usuário disser "não tenho", "não sei", "pular" → marque como skipped
- Se não conseguir extrair nada útil, peça esclarecimento
- Redes sociais: aceite @usuario OU URLs completas
- Telefones: extraia apenas números
- Cores: aceite nomes ("azul") ou hex ("#0066CC")
- Anos: aceite apenas 1900-2026

## FORMATO DE RESPOSTA (JSON OBRIGATÓRIO)
\`\`\`json
{
  "message": "Sua resposta amigável aqui",
  "extracted": {
    "fieldId": "valor extraído",
    "outroFieldId": "outro valor"
  },
  "skipped": ["fieldId1", "fieldId2"],
  "next_field": "proximoCampoId",
  "suggestions": [
    {"field": "frase", "value": "Sugestão 1", "label": "Impactante"},
    {"field": "frase", "value": "Sugestão 2", "label": "Emocional"}
  ],
  "color_palettes": [
    {"primary": "#3498DB", "secondary": "#2ECC71", "name": "Profissional"},
    {"primary": "#E74C3C", "secondary": "#C0392B", "name": "Energia"}
  ],
  "step_summary_ready": false,
  "confidence": 0.95
}
\`\`\`

## CAMPOS DE RESPOSTA
- **message**: Sua resposta em texto para o usuário (com confirmação + próxima pergunta)
- **extracted**: Objeto com campos que você conseguiu extrair da mensagem
- **skipped**: Array de fieldIds que usuário indicou não ter/não querer
- **next_field**: ID do próximo campo a perguntar (null se step completo)
- **suggestions**: Array de sugestões para campos criativos (frase, bio, etc.)
- **color_palettes**: Array de paletas de cores quando relevante
- **step_summary_ready**: true se todos os campos do step foram tratados
- **confidence**: 0-1, sua confiança na extração

## EXEMPLOS DE EXTRAÇÃO
User: "Minha empresa é Café Aurora, trabalhamos com cafeteria gourmet"
→ extracted: { "companyName": "Café Aurora", "businessType": "alimentacao" }

User: "meu zap é 11999887766"
→ extracted: { "whatsapp": "11999887766" }

User: "não tenho endereço físico"
→ skipped: ["hasPhysicalLocation", "addressCep", "addressStreet", "addressNumber", "addressNeighborhood", "addressCity", "addressState"]

User: "@cafeaurora no insta"
→ extracted: { "instagram": "@cafeaurora" }

User: "cor azul e laranja"
→ extracted: { "primaryColor": "#0066CC", "secondaryColor": "#FF6600" }
`;
}

function formatValue(value, type) {
  if (value === null || value === undefined) return 'vazio';
  if (type === 'image') return '[imagem]';
  if (Array.isArray(value)) return value.length > 0 ? `[${value.length} itens]` : 'vazio';
  return String(value).substring(0, 50);
}
/* eslint-enable no-unused-vars */

// ============================================
// COMPOSABLE PRINCIPAL
// ============================================

export function useGeminiBrain() {
  const {
    state,
    currentStep,
    nextFieldToAsk,
    updateField,
    skipField,
    markFieldAsAsked,
    addMessage,
    setTyping,
    setProcessing,
    generateStepSummary,
    confirmCurrentStep,
    nextStep
  } = useOnboardingState();
  
  /**
   * Processa mensagem do usuário através do Gemini
   */
  async function processUserMessage(userMessage, images = []) {
    if (!userMessage.trim() && images.length === 0) return null;
    
    setProcessing(true);
    setTyping(true);
    
    // Adicionar mensagem do usuário ao chat
    addMessage({
      type: 'user',
      content: userMessage,
      images: images.length > 0 ? images : undefined
    });
    
    try {
      // Preparar contexto para o Gemini
      const context = {
        currentStep: currentStep.value,
        currentField: nextFieldToAsk.value,
        formData: { ...state.formData },
        fieldStatus: { ...state.fieldStatus },
        companyName: state.formData.companyName,
        businessType: state.formData.businessType
      };
      
      // Histórico das últimas mensagens (máx 10 para não estourar tokens)
      const recentMessages = state.messages.slice(-10).map(m => ({
        role: m.type === 'user' ? 'user' : 'assistant',
        content: m.content
      }));
      
      // Chamar API
      const response = await fetch(API_ENDPOINT, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          session_id: state.sessionId,
          user_message: userMessage,
          context: context,
          messages: recentMessages,
          images: images.map(img => img.url || img.base64)
        })
      });
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }
      
      const result = await response.json();
      
      if (!result.success) {
        throw new Error(result.error || 'Erro desconhecido');
      }
      
      // Processar resposta do Gemini
      await handleGeminiResponse(result.data);
      
      return result.data;
      
    } catch (error) {
      console.error('[GeminiBrain] Erro:', error);
      
      // Mensagem de erro amigável
      addMessage({
        type: 'assistant',
        content: 'Ops! Tive um probleminha para processar sua mensagem. Pode repetir de outra forma? 🙏',
        isError: true
      });
      
      return null;
      
    } finally {
      setTyping(false);
      setProcessing(false);
    }
  }
  
  /**
   * Processa a resposta do Gemini
   */
  async function handleGeminiResponse(data) {
    // 1. Atualizar campos extraídos
    if (data.extracted && Object.keys(data.extracted).length > 0) {
      for (const [fieldId, value] of Object.entries(data.extracted)) {
        if (value !== null && value !== undefined && value !== '') {
          updateField(fieldId, value);
        }
      }
    }
    
    // 2. Marcar campos skippados
    if (data.skipped && data.skipped.length > 0) {
      for (const fieldId of data.skipped) {
        skipField(fieldId);
      }
    }
    
    // 3. Marcar próximo campo como perguntado (para não repetir)
    if (data.next_field) {
      markFieldAsAsked(data.next_field);
    }
    
    // 4. Adicionar mensagem do assistente
    addMessage({
      type: 'assistant',
      content: data.message,
      suggestions: data.suggestions || [],
      colorPalettes: data.color_palettes || [],
      nextField: data.next_field,
      stepSummaryReady: data.step_summary_ready,
      confidence: data.confidence
    });
    
    // 5. Se step está completo, preparar resumo
    if (data.step_summary_ready) {
      await showStepSummary();
    }
  }
  
  /**
   * Mostra resumo do step para confirmação
   */
  async function showStepSummary() {
    const summary = generateStepSummary();
    if (!summary) return;
    
    // Formatar resumo como mensagem
    let summaryText = `✅ **Resumo - ${summary.stepName}**\n\n`;
    
    summary.fields.forEach(field => {
      const icon = field.status === 'filled' ? '✓' : '○';
      summaryText += `${icon} **${field.label}**: ${field.value}\n`;
    });
    
    summaryText += '\n**Está tudo certo?** Confirme para avançarmos para a próxima etapa!';
    
    addMessage({
      type: 'assistant',
      content: summaryText,
      isSummary: true,
      summaryData: summary,
      actions: [
        { id: 'confirm', label: '✅ Confirmar e Avançar', type: 'primary' },
        { id: 'edit', label: '✏️ Quero Editar Algo', type: 'secondary' }
      ]
    });
  }
  
  /**
   * Confirma o step e avança
   */
  function handleStepConfirmation() {
    confirmCurrentStep();
    nextStep();
    
    // Mensagem de transição
    const next = ONBOARDING_STEPS[state.currentStepIndex];
    if (next) {
      addMessage({
        type: 'assistant',
        content: `🎉 Excelente! Vamos para **${next.name}**!\n\n${next.description}`
      });
      
      // Iniciar próximo step com primeira pergunta
      setTimeout(() => {
        askNextField();
      }, 500);
    }
  }
  
  /**
   * Pergunta o próximo campo pendente
   */
  function askNextField() {
    const field = nextFieldToAsk.value;
    if (!field) return;
    
    const step = currentStep.value;
    const companyName = state.formData.companyName || 'sua empresa';
    
    // Buscar mensagem personalizada
    let message = WELCOME_MESSAGES[step.id]?.[field.id] || `Qual é o ${field.label}?`;
    
    // Substituir placeholders
    message = message.replace(/\{\{companyName\}\}/g, companyName);
    message = message.replace(/\{\{email\}\}/g, state.formData.email || '');
    
    addMessage({
      type: 'assistant',
      content: message,
      fieldTarget: field.id
    });
    
    markFieldAsAsked(field.id);
  }
  
  /**
   * Aceita uma sugestão do assistente
   */
  function acceptSuggestion(suggestion) {
    if (suggestion.field && suggestion.value) {
      updateField(suggestion.field, suggestion.value);
      
      addMessage({
        type: 'user',
        content: `Gostei! Vou usar: "${suggestion.value}"`,
        isSystemAction: true
      });
      
      // Continuar para próximo campo
      setTimeout(() => {
        processUserMessage(`Selecionei a opção: ${suggestion.value}`);
      }, 300);
    }
  }
  
  /**
   * Aceita uma paleta de cores
   */
  function acceptColorPalette(palette) {
    updateField('primaryColor', palette.primary);
    updateField('secondaryColor', palette.secondary);
    
    addMessage({
      type: 'user',
      content: `Gostei da paleta "${palette.name}"! (${palette.primary} + ${palette.secondary})`,
      isSystemAction: true
    });
    
    // Continuar fluxo
    setTimeout(() => {
      processUserMessage(`Escolhi a paleta ${palette.name} com as cores ${palette.primary} e ${palette.secondary}`);
    }, 300);
  }
  
  /**
   * Inicia o chat com mensagem de boas-vindas contextualizada
   */
  function startChat() {
    // Verificar qual é o primeiro campo pendente
    const field = nextFieldToAsk.value;
    
    if (!field) {
      // Todos os campos já foram preenchidos?
      addMessage({
        type: 'assistant',
        content: 'Olá! 👋 Parece que você já tem algumas informações preenchidas. Quer revisar ou continuar de onde parou?'
      });
      return;
    }
    
    // Mensagem de boas-vindas baseada no campo atual
    const step = currentStep.value;
    const welcomeMessage = WELCOME_MESSAGES[step.id]?.[field.id] || 
      `Olá! 👋 Sou o Assistente Unli, seu consultor de criação de sites. Vamos começar com ${field.label}?`;
    
    addMessage({
      type: 'assistant',
      content: welcomeMessage,
      fieldTarget: field.id
    });
    
    markFieldAsAsked(field.id);
  }
  
  return {
    processUserMessage,
    handleStepConfirmation,
    askNextField,
    acceptSuggestion,
    acceptColorPalette,
    startChat,
    showStepSummary
  };
}

export default useGeminiBrain;
