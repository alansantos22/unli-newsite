<template>
  <div class="ai-content-renderer">
    <!-- Sobre Nós -->
    <div v-if="pageType === 'sobre_nos' && content.content" class="content-section">
      <div class="editable-field">
        <label>Título Principal (H1)</label>
        <input 
          type="text" 
          v-model="localContent.content.titulo_h1"
          @input="emitUpdate"
        />
      </div>
      
      <div class="editable-field">
        <label>Subtítulo</label>
        <input 
          type="text" 
          v-model="localContent.content.subtitulo"
          @input="emitUpdate"
        />
      </div>
      
      <div class="editable-field">
        <label>História</label>
        <textarea 
          v-model="localContent.content.historia"
          rows="6"
          @input="emitUpdate"
        ></textarea>
      </div>
      
      <div class="fields-row">
        <div class="editable-field">
          <label>Missão</label>
          <input 
            type="text" 
            v-model="localContent.content.missao"
            @input="emitUpdate"
          />
        </div>
        
        <div class="editable-field">
          <label>Visão</label>
          <input 
            type="text" 
            v-model="localContent.content.visao"
            @input="emitUpdate"
          />
        </div>
      </div>
      
      <div class="editable-field">
        <label>Valores</label>
        <div class="tags-input">
          <span 
            v-for="(valor, index) in localContent.content.valores" 
            :key="index"
            class="tag"
          >
            <input 
              type="text" 
              v-model="localContent.content.valores[index]"
              @input="emitUpdate"
            />
            <button @click="removeValor(index)" class="tag-remove">×</button>
          </span>
          <button @click="addValor" class="btn-add-tag">+ Adicionar valor</button>
        </div>
      </div>
      
      <div class="editable-field">
        <label>Diferencial em Destaque</label>
        <input 
          type="text" 
          v-model="localContent.content.diferencial_destaque"
          @input="emitUpdate"
        />
      </div>
    </div>

    <!-- Serviços -->
    <div v-else-if="pageType === 'servicos' && content.content" class="content-section">
      <div class="editable-field">
        <label>Título da Seção</label>
        <input 
          type="text" 
          v-model="localContent.content.titulo_secao"
          @input="emitUpdate"
        />
      </div>
      
      <div class="editable-field">
        <label>Subtítulo</label>
        <input 
          type="text" 
          v-model="localContent.content.subtitulo"
          @input="emitUpdate"
        />
      </div>
      
      <div class="editable-field">
        <label>Introdução</label>
        <textarea 
          v-model="localContent.content.intro"
          rows="3"
          @input="emitUpdate"
        ></textarea>
      </div>
      
      <div class="services-list">
        <h4>Serviços</h4>
        <div 
          v-for="(servico, index) in localContent.content.servicos" 
          :key="index"
          class="service-card"
        >
          <div class="service-header">
            <span class="service-icon">{{ getIconEmoji(servico.icone_sugestao) }}</span>
            <input 
              type="text" 
              v-model="servico.nome"
              placeholder="Nome do serviço"
              @input="emitUpdate"
            />
            <button @click="removeServico(index)" class="btn-remove">×</button>
          </div>
          
          <div class="service-body">
            <div class="editable-field">
              <label>Descrição Curta</label>
              <input 
                type="text" 
                v-model="servico.descricao_curta"
                @input="emitUpdate"
              />
            </div>
            
            <div class="editable-field">
              <label>Descrição Completa</label>
              <textarea 
                v-model="servico.descricao_completa"
                rows="3"
                @input="emitUpdate"
              ></textarea>
            </div>
            
            <div class="editable-field">
              <label>Benefícios</label>
              <div class="inline-tags">
                <span 
                  v-for="(beneficio, bi) in servico.beneficios" 
                  :key="bi"
                  class="inline-tag"
                >
                  <input 
                    type="text" 
                    v-model="servico.beneficios[bi]"
                    @input="emitUpdate"
                  />
                  <button @click="removeBeneficio(index, bi)" class="tag-remove">×</button>
                </span>
                <button @click="addBeneficio(index)" class="btn-add-inline">+</button>
              </div>
            </div>
          </div>
        </div>
        <button @click="addServico" class="btn-add-service">+ Adicionar Serviço</button>
      </div>
      
      <div class="fields-row">
        <div class="editable-field">
          <label>Texto do CTA</label>
          <input 
            type="text" 
            v-model="localContent.content.cta_text"
            @input="emitUpdate"
          />
        </div>
        
        <div class="editable-field">
          <label>Garantia</label>
          <input 
            type="text" 
            v-model="localContent.content.garantia"
            @input="emitUpdate"
          />
        </div>
      </div>
    </div>

    <!-- FAQ -->
    <div v-else-if="pageType === 'faq' && content.content" class="content-section">
      <div class="editable-field">
        <label>Título da Seção</label>
        <input 
          type="text" 
          v-model="localContent.content.titulo_secao"
          @input="emitUpdate"
        />
      </div>
      
      <div class="editable-field">
        <label>Subtítulo</label>
        <input 
          type="text" 
          v-model="localContent.content.subtitulo"
          @input="emitUpdate"
        />
      </div>
      
      <div class="faq-list">
        <h4>Perguntas e Respostas</h4>
        <div 
          v-for="(qa, index) in localContent.content.perguntas" 
          :key="index"
          class="faq-item"
        >
          <div class="faq-header">
            <span class="faq-number">{{ index + 1 }}</span>
            <button @click="removePergunta(index)" class="btn-remove">×</button>
          </div>
          
          <div class="editable-field">
            <label>Pergunta</label>
            <input 
              type="text" 
              v-model="qa.pergunta"
              @input="emitUpdate"
            />
          </div>
          
          <div class="editable-field">
            <label>Resposta</label>
            <textarea 
              v-model="qa.resposta"
              rows="3"
              @input="emitUpdate"
            ></textarea>
          </div>
          
          <div class="editable-field category-field">
            <label>Categoria</label>
            <select v-model="qa.categoria" @change="emitUpdate">
              <option value="preco">Preço</option>
              <option value="qualidade">Qualidade</option>
              <option value="prazo">Prazo</option>
              <option value="garantia">Garantia</option>
              <option value="processo">Processo</option>
              <option value="outros">Outros</option>
            </select>
          </div>
        </div>
        <button @click="addPergunta" class="btn-add-faq">+ Adicionar Pergunta</button>
      </div>
      
      <div class="editable-field">
        <label>CTA para Mais Dúvidas</label>
        <input 
          type="text" 
          v-model="localContent.content.cta_duvida"
          @input="emitUpdate"
        />
      </div>
    </div>

    <!-- Portfolio -->
    <div v-else-if="pageType === 'portfolio' && content.content" class="content-section">
      <div class="editable-field">
        <label>Título da Seção</label>
        <input 
          type="text" 
          v-model="localContent.content.titulo_secao"
          @input="emitUpdate"
        />
      </div>
      
      <div class="editable-field">
        <label>Subtítulo</label>
        <input 
          type="text" 
          v-model="localContent.content.subtitulo"
          @input="emitUpdate"
        />
      </div>
      
      <div class="projects-list">
        <h4>Projetos</h4>
        <div 
          v-for="(projeto, index) in localContent.content.projetos" 
          :key="index"
          class="project-card"
        >
          <div class="project-header">
            <input 
              type="text" 
              v-model="projeto.nome_projeto"
              placeholder="Nome do projeto"
              class="project-name"
              @input="emitUpdate"
            />
            <label class="destaque-toggle">
              <input 
                type="checkbox" 
                v-model="projeto.destaque"
                @change="emitUpdate"
              />
              <span>Destaque</span>
            </label>
            <button @click="removeProjeto(index)" class="btn-remove">×</button>
          </div>
          
          <div class="project-body">
            <div class="editable-field">
              <label>Segmento</label>
              <input 
                type="text" 
                v-model="projeto.segmento"
                @input="emitUpdate"
              />
            </div>
            
            <div class="editable-field">
              <label>Desafio</label>
              <textarea 
                v-model="projeto.desafio"
                rows="2"
                @input="emitUpdate"
              ></textarea>
            </div>
            
            <div class="editable-field">
              <label>Solução</label>
              <textarea 
                v-model="projeto.solucao"
                rows="2"
                @input="emitUpdate"
              ></textarea>
            </div>
            
            <div class="editable-field">
              <label>Resultado</label>
              <input 
                type="text" 
                v-model="projeto.resultado"
                @input="emitUpdate"
              />
            </div>
          </div>
        </div>
        <button @click="addProjeto" class="btn-add-project">+ Adicionar Projeto</button>
      </div>
      
      <div class="metrics-section" v-if="localContent.content.metricas_gerais">
        <h4>Métricas Gerais</h4>
        <div class="fields-row">
          <div class="editable-field">
            <label>Projetos Entregues</label>
            <input 
              type="text" 
              v-model="localContent.content.metricas_gerais.projetos_entregues"
              @input="emitUpdate"
            />
          </div>
          <div class="editable-field">
            <label>Clientes Satisfeitos</label>
            <input 
              type="text" 
              v-model="localContent.content.metricas_gerais.clientes_satisfeitos"
              @input="emitUpdate"
            />
          </div>
          <div class="editable-field">
            <label>Anos de Mercado</label>
            <input 
              type="text" 
              v-model="localContent.content.metricas_gerais.anos_mercado"
              @input="emitUpdate"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Generic / Personalizada -->
    <div v-else-if="content.content" class="content-section">
      <div class="generic-content">
        <pre class="json-preview">{{ JSON.stringify(content.content, null, 2) }}</pre>
        <p class="generic-note">
          💡 Este tipo de conteúdo usa uma estrutura personalizada. 
          Você pode copiar o JSON acima e editar conforme necessário.
        </p>
      </div>
    </div>

    <!-- No Content -->
    <div v-else class="no-content">
      <p>Nenhum conteúdo gerado ainda.</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AIContentRenderer',
  
  props: {
    content: {
      type: Object,
      required: true
    },
    pageType: {
      type: String,
      required: true
    }
  },
  
  emits: ['update'],
  
  data() {
    return {
      localContent: JSON.parse(JSON.stringify(this.content))
    };
  },
  
  watch: {
    content: {
      handler(newContent) {
        this.localContent = JSON.parse(JSON.stringify(newContent));
      },
      deep: true
    }
  },
  
  methods: {
    emitUpdate() {
      this.$emit('update', this.localContent);
    },
    
    getIconEmoji(iconName) {
      const iconMap = {
        'rocket': '🚀',
        'handshake': '🤝',
        'shield': '🛡️',
        'star': '⭐',
        'check': '✅',
        'heart': '❤️',
        'lightning': '⚡',
        'gear': '⚙️',
        'home': '🏠',
        'money': '💰',
        'clock': '⏰',
        'trophy': '🏆'
      };
      return iconMap[iconName] || '📌';
    },
    
    // Sobre Nós
    addValor() {
      if (!this.localContent.content.valores) {
        this.localContent.content.valores = [];
      }
      this.localContent.content.valores.push('Novo valor');
      this.emitUpdate();
    },
    
    removeValor(index) {
      this.localContent.content.valores.splice(index, 1);
      this.emitUpdate();
    },
    
    // Serviços
    addServico() {
      if (!this.localContent.content.servicos) {
        this.localContent.content.servicos = [];
      }
      this.localContent.content.servicos.push({
        nome: 'Novo Serviço',
        descricao_curta: '',
        descricao_completa: '',
        beneficios: ['Benefício 1'],
        icone_sugestao: 'star'
      });
      this.emitUpdate();
    },
    
    removeServico(index) {
      this.localContent.content.servicos.splice(index, 1);
      this.emitUpdate();
    },
    
    addBeneficio(serviceIndex) {
      if (!this.localContent.content.servicos[serviceIndex].beneficios) {
        this.localContent.content.servicos[serviceIndex].beneficios = [];
      }
      this.localContent.content.servicos[serviceIndex].beneficios.push('Novo benefício');
      this.emitUpdate();
    },
    
    removeBeneficio(serviceIndex, beneficioIndex) {
      this.localContent.content.servicos[serviceIndex].beneficios.splice(beneficioIndex, 1);
      this.emitUpdate();
    },
    
    // FAQ
    addPergunta() {
      if (!this.localContent.content.perguntas) {
        this.localContent.content.perguntas = [];
      }
      this.localContent.content.perguntas.push({
        pergunta: 'Nova pergunta?',
        resposta: 'Resposta aqui...',
        categoria: 'outros'
      });
      this.emitUpdate();
    },
    
    removePergunta(index) {
      this.localContent.content.perguntas.splice(index, 1);
      this.emitUpdate();
    },
    
    // Portfolio
    addProjeto() {
      if (!this.localContent.content.projetos) {
        this.localContent.content.projetos = [];
      }
      this.localContent.content.projetos.push({
        nome_projeto: 'Novo Projeto',
        segmento: '',
        desafio: '',
        solucao: '',
        resultado: '',
        destaque: false
      });
      this.emitUpdate();
    },
    
    removeProjeto(index) {
      this.localContent.content.projetos.splice(index, 1);
      this.emitUpdate();
    }
  }
};
</script>

<style scoped lang="scss">
.ai-content-renderer {
  margin-top: 1rem;
}

.content-section {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 1.5rem;
}

// Editable Fields
.editable-field {
  margin-bottom: 1rem;
  
  label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.4rem;
    color: #495057;
    font-size: 0.9rem;
  }
  
  input, textarea, select {
    width: 100%;
    padding: 0.6rem 0.8rem;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    font-size: 0.95rem;
    transition: all 0.2s;
    background: white;
    
    &:focus {
      outline: none;
      border-color: #0066CC;
      box-shadow: 0 0 0 2px rgba(0, 102, 204, 0.1);
    }
  }
  
  textarea {
    resize: vertical;
    font-family: inherit;
  }
}

.fields-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

// Tags Input
.tags-input {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  
  .tag {
    display: flex;
    align-items: center;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    overflow: hidden;
    
    input {
      border: none;
      padding: 0.4rem 0.6rem;
      width: 120px;
      font-size: 0.9rem;
      
      &:focus {
        outline: none;
      }
    }
    
    .tag-remove {
      background: none;
      border: none;
      color: #dc3545;
      padding: 0.4rem 0.6rem;
      cursor: pointer;
      font-size: 1.1rem;
      
      &:hover {
        background: #fee2e2;
      }
    }
  }
  
  .btn-add-tag {
    padding: 0.4rem 0.8rem;
    background: #e9ecef;
    border: 1px dashed #adb5bd;
    border-radius: 6px;
    color: #495057;
    cursor: pointer;
    font-size: 0.85rem;
    
    &:hover {
      background: #dee2e6;
    }
  }
}

// Services List
.services-list,
.faq-list,
.projects-list {
  margin: 1.5rem 0;
  
  h4 {
    margin-bottom: 1rem;
    color: #212529;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
}

.service-card,
.project-card {
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  margin-bottom: 1rem;
  overflow: hidden;
  
  .service-header,
  .project-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    
    .service-icon {
      font-size: 1.5rem;
    }
    
    input {
      flex: 1;
      border: none;
      background: transparent;
      font-weight: 600;
      font-size: 1rem;
      
      &:focus {
        outline: none;
      }
    }
    
    .btn-remove {
      background: #fee2e2;
      border: none;
      color: #dc3545;
      width: 28px;
      height: 28px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 1.1rem;
      
      &:hover {
        background: #fecaca;
      }
    }
  }
  
  .service-body,
  .project-body {
    padding: 1rem;
  }
}

.project-header {
  .project-name {
    flex: 1;
  }
  
  .destaque-toggle {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.85rem;
    color: #6c757d;
    cursor: pointer;
    
    input[type="checkbox"] {
      width: 16px;
      height: 16px;
    }
  }
}

.inline-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  
  .inline-tag {
    display: flex;
    align-items: center;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    
    input {
      border: none;
      background: transparent;
      padding: 0.3rem 0.5rem;
      width: 100px;
      font-size: 0.85rem;
      
      &:focus {
        outline: none;
      }
    }
    
    .tag-remove {
      background: none;
      border: none;
      color: #dc3545;
      padding: 0.3rem;
      cursor: pointer;
      
      &:hover {
        background: rgba(220, 53, 69, 0.1);
      }
    }
  }
  
  .btn-add-inline {
    width: 28px;
    height: 28px;
    border: 1px dashed #adb5bd;
    border-radius: 4px;
    background: transparent;
    color: #6c757d;
    cursor: pointer;
    font-size: 1rem;
    
    &:hover {
      background: #e9ecef;
    }
  }
}

// FAQ
.faq-item {
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1rem;
  
  .faq-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
    
    .faq-number {
      width: 28px;
      height: 28px;
      background: #0066CC;
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      font-size: 0.85rem;
    }
    
    .btn-remove {
      background: #fee2e2;
      border: none;
      color: #dc3545;
      width: 28px;
      height: 28px;
      border-radius: 6px;
      cursor: pointer;
      
      &:hover {
        background: #fecaca;
      }
    }
  }
  
  .category-field {
    select {
      max-width: 200px;
    }
  }
}

// Add Buttons
.btn-add-service,
.btn-add-faq,
.btn-add-project {
  width: 100%;
  padding: 0.75rem;
  border: 2px dashed #dee2e6;
  border-radius: 8px;
  background: transparent;
  color: #0066CC;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  
  &:hover {
    border-color: #0066CC;
    background: rgba(0, 102, 204, 0.05);
  }
}

// Metrics
.metrics-section {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #dee2e6;
  
  h4 {
    margin-bottom: 1rem;
    color: #212529;
  }
}

// Generic Content
.generic-content {
  .json-preview {
    background: #212529;
    color: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    overflow-x: auto;
    font-size: 0.85rem;
    font-family: 'Monaco', 'Consolas', monospace;
    max-height: 300px;
    overflow-y: auto;
  }
  
  .generic-note {
    margin-top: 1rem;
    padding: 0.75rem;
    background: #fff3cd;
    border-radius: 6px;
    color: #856404;
    font-size: 0.9rem;
  }
}

// No Content
.no-content {
  text-align: center;
  padding: 2rem;
  color: #6c757d;
}

// Responsive
@media (max-width: 768px) {
  .fields-row {
    grid-template-columns: 1fr;
  }
  
  .tags-input .tag input {
    width: 100px;
  }
}
</style>
