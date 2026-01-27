<template>
  <div class="faq-builder">
    <!-- Intro Text with AI Enhance -->
    <div class="faq-intro-section" v-if="showIntro">
      <label class="section-label">Texto de introdução do FAQ (opcional)</label>
      <div class="intro-textarea-wrapper">
        <textarea
          v-model="introText"
          :placeholder="introPlaceholder"
          rows="2"
          class="intro-textarea"
        />
        <button
          type="button"
          class="ai-enhance-btn"
          :class="{ 'is-loading': isEnhancingIntro }"
          :disabled="isEnhancingIntro || introText.length < 10"
          @click="enhanceIntro"
        >
          <span v-if="isEnhancingIntro" class="spinner-small"></span>
          <span v-else>✨</span>
          Melhorar com IA
        </button>
      </div>
    </div>

    <!-- Suggestion Tags (Chips) -->
    <div class="suggestion-tags-section">
      <p class="tags-label">
        <span class="tags-icon">💡</span>
        Precisa de ideias? Clique para adicionar:
      </p>
      <div class="tags-grid">
        <button
          v-for="tag in availableTags"
          :key="tag.id"
          type="button"
          class="suggestion-tag"
          :class="{ 'is-used': isTagUsed(tag.id) }"
          :disabled="isTagUsed(tag.id)"
          @click="addFromTag(tag)"
        >
          <span class="tag-icon">{{ tag.icon }}</span>
          <span class="tag-label">{{ tag.label }}</span>
          <span v-if="isTagUsed(tag.id)" class="tag-check">✓</span>
        </button>
      </div>
    </div>

    <!-- FAQ Items List -->
    <div class="faq-items-section">
      <h4 class="items-title">
        <span>📋</span>
        Perguntas do seu FAQ
        <span class="items-count">({{ faqItems.length }})</span>
      </h4>

      <TransitionGroup name="faq-list" tag="div" class="faq-items-list">
        <div
          v-for="(item, index) in faqItems"
          :key="item.id"
          class="faq-card"
          :class="{ 
            'is-expanded': expandedCard === item.id,
            'is-enhancing': item.isEnhancing 
          }"
        >
          <!-- Card Header (Always Visible) -->
          <div 
            class="card-header"
            @click="toggleCard(item.id)"
          >
            <div class="header-left">
              <span class="card-number">#{{ index + 1 }}</span>
              <span class="card-question-preview">
                {{ item.question || 'Nova pergunta...' }}
              </span>
            </div>
            <div class="header-actions">
              <span 
                v-if="item.answer && item.answer.length > 0" 
                class="status-badge has-answer"
              >
                ✓ Respondida
              </span>
              <span v-else class="status-badge pending">
                Pendente
              </span>
              <button
                type="button"
                class="action-btn expand-btn"
                :class="{ 'is-expanded': expandedCard === item.id }"
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
              </button>
              <button
                type="button"
                class="action-btn delete-btn"
                @click.stop="removeItem(index)"
                title="Remover pergunta"
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="18" y1="6" x2="6" y2="18"></line>
                  <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
              </button>
            </div>
          </div>

          <!-- Card Body (Expandable) -->
          <Transition name="expand">
            <div v-if="expandedCard === item.id" class="card-body">
              <!-- Question Input -->
              <div class="field-group">
                <label class="field-label">
                  <span class="label-icon">❓</span>
                  Pergunta
                </label>
                <input
                  type="text"
                  v-model="item.question"
                  :placeholder="item.questionPlaceholder || 'Ex: Vocês atendem aos finais de semana?'"
                  class="question-input"
                  @input="emitUpdate"
                />
              </div>

              <!-- Answer Input with AI Button -->
              <div class="field-group">
                <label class="field-label">
                  <span class="label-icon">💬</span>
                  Resposta
                  <span class="label-hint">(escreva de forma resumida)</span>
                </label>
                <div class="answer-wrapper">
                  <textarea
                    v-model="item.answer"
                    :placeholder="item.answerPlaceholder || 'Ex: Sim, funcionamos sábado das 9h às 13h...'"
                    rows="3"
                    class="answer-textarea"
                    @input="emitUpdate"
                  />
                  <button
                    type="button"
                    class="ai-rewrite-btn"
                    :class="{ 'is-loading': item.isEnhancing }"
                    :disabled="item.isEnhancing || !item.answer || item.answer.length < 5"
                    @click="enhanceAnswer(index)"
                  >
                    <span v-if="item.isEnhancing" class="spinner-small"></span>
                    <span v-else class="btn-icon">✨</span>
                    <span class="btn-text">{{ item.isEnhancing ? 'Reescrevendo...' : 'Reescrever Profissionalmente' }}</span>
                  </button>
                </div>
                <p class="field-tip">
                  💡 Escreva a resposta de qualquer jeito, depois clique no botão para a IA polir o texto.
                </p>
              </div>
            </div>
          </Transition>
        </div>
      </TransitionGroup>

      <!-- Add Custom Question Button -->
      <button
        type="button"
        class="add-question-btn"
        @click="addEmptyItem"
      >
        <span class="btn-icon">+</span>
        Adicionar outra pergunta personalizada
      </button>
    </div>

    <!-- AI Credits Hint -->
    <p class="ai-credits-hint">
      <span>🤖</span>
      A IA reescreve seu texto de forma profissional — você fornece os fatos, ela cuida do estilo.
    </p>
  </div>
</template>

<script>
export default {
  name: 'FaqBuilder',

  props: {
    modelValue: {
      type: Object,
      default: () => ({
        introText: '',
        items: []
      })
    },
    showIntro: {
      type: Boolean,
      default: true
    },
    introPlaceholder: {
      type: String,
      default: 'Reunimos aqui as principais dúvidas dos nossos clientes...'
    },
    voiceTone: {
      type: String,
      default: 'profissional'
    },
    maxItems: {
      type: Number,
      default: 15
    },
    apiEndpoint: {
      type: String,
      default: '/api/ai/enhance-text.php'
    }
  },

  emits: ['update:modelValue'],

  data() {
    return {
      introText: '',
      faqItems: [],
      expandedCard: null,
      isEnhancingIntro: false,
      
      // Suggestion tags configuration
      suggestionTags: [
        {
          id: 'payment',
          icon: '💳',
          label: 'Formas de Pagamento',
          question: 'Quais são as formas de pagamento aceitas?',
          questionPlaceholder: 'Quais são as formas de pagamento aceitas?',
          answerPlaceholder: 'Ex: Aceitamos pix com 5% de desconto, cartão em até 12x...'
        },
        {
          id: 'delivery',
          icon: '🚚',
          label: 'Prazo de Entrega',
          question: 'Qual o prazo de entrega?',
          questionPlaceholder: 'Qual o prazo de entrega?',
          answerPlaceholder: 'Ex: O prazo varia de 3 a 15 dias dependendo da região...'
        },
        {
          id: 'exchange',
          icon: '🔄',
          label: 'Política de Troca',
          question: 'Como funciona a política de trocas e devoluções?',
          questionPlaceholder: 'Como funciona a política de trocas e devoluções?',
          answerPlaceholder: 'Ex: Aceitamos trocas em até 30 dias, produto deve estar lacrado...'
        },
        {
          id: 'warranty',
          icon: '🛡️',
          label: 'Garantia',
          question: 'O produto/serviço tem garantia?',
          questionPlaceholder: 'O produto/serviço tem garantia?',
          answerPlaceholder: 'Ex: Sim, todos os produtos têm garantia de 1 ano...'
        },
        {
          id: 'location',
          icon: '📍',
          label: 'Localização',
          question: 'Onde vocês ficam localizados?',
          questionPlaceholder: 'Onde vocês ficam localizados?',
          answerPlaceholder: 'Ex: Estamos na Rua X, nº 123, Centro...'
        },
        {
          id: 'hours',
          icon: '⏰',
          label: 'Horário de Funcionamento',
          question: 'Qual o horário de atendimento?',
          questionPlaceholder: 'Qual o horário de atendimento?',
          answerPlaceholder: 'Ex: Segunda a sexta das 8h às 18h, sábado das 9h às 13h...'
        },
        {
          id: 'process',
          icon: '📋',
          label: 'Como Funciona',
          question: 'Como funciona o processo de compra/contratação?',
          questionPlaceholder: 'Como funciona o processo de compra/contratação?',
          answerPlaceholder: 'Ex: Primeiro você entra em contato, depois fazemos um orçamento...'
        },
        {
          id: 'support',
          icon: '📞',
          label: 'Suporte',
          question: 'Como entrar em contato para suporte?',
          questionPlaceholder: 'Como entrar em contato para suporte?',
          answerPlaceholder: 'Ex: Você pode ligar para (11) 1234-5678 ou enviar email...'
        }
      ]
    };
  },

  computed: {
    availableTags() {
      return this.suggestionTags;
    }
  },

  watch: {
    modelValue: {
      immediate: true,
      deep: true,
      handler(newVal) {
        if (newVal) {
          this.introText = newVal.introText || '';
          this.faqItems = (newVal.items || []).map((item, index) => ({
            id: item.id || `faq-${Date.now()}-${index}`,
            question: item.question || '',
            answer: item.answer || '',
            tagId: item.tagId || null,
            questionPlaceholder: item.questionPlaceholder || '',
            answerPlaceholder: item.answerPlaceholder || '',
            isEnhancing: false
          }));
        }
      }
    },
    introText() {
      this.emitUpdate();
    }
  },

  methods: {
    isTagUsed(tagId) {
      return this.faqItems.some(item => item.tagId === tagId);
    },

    addFromTag(tag) {
      const newItem = {
        id: `faq-${Date.now()}`,
        question: tag.question,
        answer: '',
        tagId: tag.id,
        questionPlaceholder: tag.questionPlaceholder,
        answerPlaceholder: tag.answerPlaceholder,
        isEnhancing: false
      };
      
      this.faqItems.push(newItem);
      this.expandedCard = newItem.id;
      this.emitUpdate();
      
      // Scroll to the new item
      this.$nextTick(() => {
        const element = document.querySelector(`[data-faq-id="${newItem.id}"]`);
        if (element) {
          element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      });
    },

    addEmptyItem() {
      const newItem = {
        id: `faq-${Date.now()}`,
        question: '',
        answer: '',
        tagId: null,
        questionPlaceholder: 'Ex: Vocês atendem aos finais de semana?',
        answerPlaceholder: 'Escreva a resposta de forma resumida...',
        isEnhancing: false
      };
      
      this.faqItems.push(newItem);
      this.expandedCard = newItem.id;
      this.emitUpdate();
    },

    removeItem(index) {
      this.faqItems.splice(index, 1);
      this.emitUpdate();
    },

    toggleCard(itemId) {
      this.expandedCard = this.expandedCard === itemId ? null : itemId;
    },

    emitUpdate() {
      const output = {
        introText: this.introText,
        items: this.faqItems.map(item => ({
          id: item.id,
          question: item.question,
          answer: item.answer,
          tagId: item.tagId
        }))
      };
      this.$emit('update:modelValue', output);
    },

    async enhanceIntro() {
      if (!this.introText || this.introText.length < 10) return;
      
      this.isEnhancingIntro = true;
      
      try {
        const response = await fetch(this.apiEndpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            text: this.introText,
            context: 'texto de introdução para seção de FAQ de site institucional',
            voiceTone: this.voiceTone,
            instruction: 'Reescreva este texto de introdução de FAQ de forma profissional, acolhedora e clara. Mantenha o sentido original.'
          })
        });
        
        const data = await response.json();
        
        if (data.success && data.enhanced) {
          this.introText = data.enhanced;
          this.emitUpdate();
        }
      } catch (error) {
        console.error('Error enhancing intro:', error);
      } finally {
        this.isEnhancingIntro = false;
      }
    },

    async enhanceAnswer(index) {
      const item = this.faqItems[index];
      if (!item || !item.answer || item.answer.length < 5) return;
      
      item.isEnhancing = true;
      
      try {
        const response = await fetch(this.apiEndpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            text: item.answer,
            context: `resposta para pergunta de FAQ: "${item.question}"`,
            voiceTone: this.voiceTone,
            instruction: 'Reescreva esta resposta de FAQ de forma profissional, clara e completa. O usuário escreveu um rascunho com os fatos principais. Mantenha todas as informações factuais (números, prazos, valores) exatamente como foram escritos, apenas melhore a redação e clareza.'
          })
        });
        
        const data = await response.json();
        
        if (data.success && data.enhanced) {
          item.answer = data.enhanced;
          this.emitUpdate();
        }
      } catch (error) {
        console.error('Error enhancing answer:', error);
      } finally {
        item.isEnhancing = false;
      }
    }
  }
};
</script>

<style lang="scss" scoped>
.faq-builder {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

// ===== INTRO SECTION =====
.faq-intro-section {
  .section-label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
  }
  
  .intro-textarea-wrapper {
    position: relative;
    
    .intro-textarea {
      width: 100%;
      padding: 1rem;
      padding-right: 160px;
      border: 2px solid #e5e7eb;
      border-radius: 12px;
      font-size: 0.95rem;
      line-height: 1.5;
      resize: none;
      transition: border-color 0.2s, box-shadow 0.2s;
      background: #f0fdf4;
      
      &:focus {
        outline: none;
        border-color: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
      }
    }
    
    .ai-enhance-btn {
      position: absolute;
      bottom: 0.75rem;
      right: 0.75rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1rem;
      background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 0.85rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      
      &:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
      }
      
      &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
      }
      
      &.is-loading {
        pointer-events: none;
      }
    }
  }
}

// ===== SUGGESTION TAGS =====
.suggestion-tags-section {
  background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
  padding: 1.25rem;
  border-radius: 16px;
  
  .tags-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #6b7280;
    margin-bottom: 1rem;
    
    .tags-icon {
      font-size: 1.1rem;
    }
  }
  
  .tags-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
  }
  
  .suggestion-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
    
    &:hover:not(:disabled) {
      background: #eff6ff;
      border-color: #3b82f6;
      color: #1d4ed8;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }
    
    &.is-used {
      background: #f0fdf4;
      border-color: #10b981;
      color: #059669;
      cursor: default;
      
      .tag-check {
        color: #10b981;
        font-weight: 700;
      }
    }
    
    .tag-icon {
      font-size: 1rem;
    }
  }
}

// ===== FAQ ITEMS SECTION =====
.faq-items-section {
  .items-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 1rem;
    
    .items-count {
      color: #9ca3af;
      font-weight: 400;
    }
  }
}

.faq-items-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 1rem;
}

// ===== FAQ CARD =====
.faq-card {
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.2s;
  
  &:hover {
    border-color: #d1d5db;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  }
  
  &.is-expanded {
    border-color: #3b82f6;
    box-shadow: 0 4px 16px rgba(59, 130, 246, 0.1);
  }
  
  &.is-enhancing {
    opacity: 0.8;
    pointer-events: none;
  }
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  cursor: pointer;
  transition: background 0.2s;
  
  &:hover {
    background: #f9fafb;
  }
  
  .header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    min-width: 0;
  }
  
  .card-number {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: #f3f4f6;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 700;
    color: #6b7280;
    flex-shrink: 0;
  }
  
  .card-question-preview {
    font-size: 0.95rem;
    font-weight: 500;
    color: #374151;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  
  .header-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-shrink: 0;
  }
  
  .status-badge {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    
    &.has-answer {
      background: #dcfce7;
      color: #15803d;
    }
    
    &.pending {
      background: #fef3c7;
      color: #b45309;
    }
  }
  
  .action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: transparent;
    border: none;
    border-radius: 8px;
    color: #9ca3af;
    cursor: pointer;
    transition: all 0.2s;
    
    &:hover {
      background: #f3f4f6;
      color: #6b7280;
    }
    
    &.expand-btn {
      svg {
        transition: transform 0.2s;
      }
      
      &.is-expanded svg {
        transform: rotate(180deg);
      }
    }
    
    &.delete-btn:hover {
      background: #fee2e2;
      color: #dc2626;
    }
  }
}

.card-body {
  padding: 0 1.25rem 1.25rem;
  border-top: 1px solid #e5e7eb;
  
  .field-group {
    margin-top: 1.25rem;
  }
  
  .field-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    
    .label-icon {
      font-size: 1rem;
    }
    
    .label-hint {
      font-weight: 400;
      color: #9ca3af;
    }
  }
  
  .question-input {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.2s;
    
    &:focus {
      outline: none;
      border-color: #3b82f6;
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
  }
  
  .answer-wrapper {
    position: relative;
  }
  
  .answer-textarea {
    width: 100%;
    padding: 1rem;
    padding-bottom: 3.5rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.95rem;
    line-height: 1.5;
    resize: none;
    transition: all 0.2s;
    
    &:focus {
      outline: none;
      border-color: #3b82f6;
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
  }
  
  .ai-rewrite-btn {
    position: absolute;
    bottom: 0.75rem;
    right: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    
    &:hover:not(:disabled) {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
    }
    
    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
    
    .btn-icon {
      font-size: 1rem;
    }
  }
  
  .field-tip {
    margin-top: 0.5rem;
    font-size: 0.8rem;
    color: #9ca3af;
  }
}

// ===== ADD BUTTON =====
.add-question-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  padding: 1rem;
  background: transparent;
  border: 2px dashed #d1d5db;
  border-radius: 12px;
  color: #6b7280;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  
  &:hover {
    border-color: #3b82f6;
    color: #3b82f6;
    background: #eff6ff;
  }
  
  .btn-icon {
    font-size: 1.25rem;
  }
}

// ===== AI CREDITS HINT =====
.ai-credits-hint {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: linear-gradient(135deg, #faf5ff 0%, #f0f9ff 100%);
  border-radius: 10px;
  font-size: 0.85rem;
  color: #6b7280;
  text-align: center;
}

// ===== ANIMATIONS =====
.spinner-small {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

// Transition for expand/collapse
.expand-enter-active,
.expand-leave-active {
  transition: all 0.3s ease;
  overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
  opacity: 0;
  max-height: 0;
  padding-top: 0;
  padding-bottom: 0;
}

// Transition for list items
.faq-list-enter-active,
.faq-list-leave-active {
  transition: all 0.3s ease;
}

.faq-list-enter-from,
.faq-list-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}

.faq-list-move {
  transition: transform 0.3s ease;
}

// ===== RESPONSIVE =====
@media (max-width: 640px) {
  .suggestion-tags-section {
    padding: 1rem;
  }
  
  .tags-grid {
    gap: 0.5rem;
  }
  
  .suggestion-tag {
    padding: 0.5rem 0.75rem;
    font-size: 0.85rem;
    
    .tag-label {
      display: none;
    }
    
    .tag-icon {
      font-size: 1.2rem;
    }
    
    // Show label on larger phones
    @media (min-width: 400px) {
      .tag-label {
        display: inline;
      }
    }
  }
  
  .card-header {
    padding: 0.875rem 1rem;
    
    .header-left {
      gap: 0.5rem;
    }
    
    .status-badge {
      display: none;
    }
  }
  
  .card-body {
    padding: 0 1rem 1rem;
    
    .ai-rewrite-btn {
      position: static;
      width: 100%;
      justify-content: center;
      margin-top: 0.75rem;
      padding: 0.75rem 1rem;
    }
  }
  
  .answer-textarea {
    padding-bottom: 1rem !important;
  }
}
</style>
