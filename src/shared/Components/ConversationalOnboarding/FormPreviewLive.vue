<template>
  <div class="form-preview-live">
    <!-- Header -->
    <header class="preview-header">
      <div class="header-content">
        <h3 class="preview-title">📋 Seu Site</h3>
        <span class="preview-subtitle">Cards Vivos - Atualiza em tempo real</span>
      </div>
      <div class="header-stats">
        <span class="stat" :class="{ 'is-complete': overallProgress === 100 }">
          {{ Math.round(overallProgress) }}% completo
        </span>
      </div>
    </header>
    
    <!-- Cards Vivos -->
    <div class="live-cards-container">
      <!-- Card Identidade -->
      <LiveCard
        id="identity"
        type="identity"
        title="Identidade da Marca"
        icon="🎨"
        :form-data="formData"
        :current-step-id="currentStepId"
        :fields="identityFields"
        :required-fields="['companyName', 'businessType']"
        :shimmering-fields="shimmeringFields"
        :recently-filled-fields="recentlyFilledFields"
        :answered-fields="answeredFields"
        :validation-errors="validationErrors"
        image-field="logo"
        image-field-label="seu logotipo"
        @field-edit="handleFieldEdit"
        @field-focus="handleFieldFocus"
        @image-upload="handleImageUpload"
        @image-remove="handleImageRemove"
        @edit-feedback="handleEditFeedback"
      />
      
      <!-- Card Contato -->
      <LiveCard
        id="contact"
        type="contact"
        title="Informações de Contato"
        icon="📞"
        :form-data="formData"
        :current-step-id="currentStepId"
        :fields="contactFields"
        :required-fields="['whatsapp']"
        :shimmering-fields="shimmeringFields"
        :recently-filled-fields="recentlyFilledFields"
        :answered-fields="answeredFields"
        :validation-errors="validationErrors"
        @field-edit="handleFieldEdit"
        @field-focus="handleFieldFocus"
        @edit-feedback="handleEditFeedback"
      />
      
      <!-- Card Configuração de Leads -->
      <LiveCard
        id="leads"
        type="leads"
        title="Formulário de Contato"
        icon="📨"
        :form-data="formData"
        :current-step-id="currentStepId"
        :fields="leadsFields"
        :required-fields="['leadEmail']"
        :shimmering-fields="shimmeringFields"
        :recently-filled-fields="recentlyFilledFields"
        :answered-fields="answeredFields"
        :validation-errors="validationErrors"
        @field-edit="handleFieldEdit"
        @field-focus="handleFieldFocus"
        @edit-feedback="handleEditFeedback"
      />
      
      <!-- Card Sobre -->
      <LiveCard
        id="about"
        type="about"
        title="Sobre a Empresa"
        icon="🏢"
        :form-data="formData"
        :current-step-id="currentStepId"
        :fields="aboutFields"
        :required-fields="[]"
        :shimmering-fields="shimmeringFields"
        :recently-filled-fields="recentlyFilledFields"
        :answered-fields="answeredFields"
        :validation-errors="validationErrors"
        image-field="aboutImage"
        image-field-label="foto da empresa"
        @field-edit="handleFieldEdit"
        @field-focus="handleFieldFocus"
        @image-upload="handleImageUpload"
        @image-remove="handleImageRemove"
        @edit-feedback="handleEditFeedback"
      />
      
      <!-- Card Serviços -->
      <LiveCard
        id="services"
        type="services"
        title="Serviços"
        icon="⚙️"
        :form-data="formData"
        :current-step-id="currentStepId"
        :fields="servicesFields"
        :required-fields="[]"
        :shimmering-fields="shimmeringFields"
        :recently-filled-fields="recentlyFilledFields"
        :answered-fields="answeredFields"
        :validation-errors="validationErrors"
        @field-edit="handleFieldEdit"
        @field-focus="handleFieldFocus"
        @edit-feedback="handleEditFeedback"
      >
        <template #fields>
          <!-- Serviços têm layout customizado -->
          <div class="services-list">
            <div v-if="!formData.services?.length" class="empty-state">
              <span class="empty-icon">📦</span>
              <span class="empty-text">Nenhum serviço cadastrado</span>
            </div>
            <div 
              v-for="(service, index) in formData.services?.slice(0, 3)" 
              :key="service.id || index"
              class="service-item"
            >
              <span class="service-bullet">•</span>
              <span class="service-name">{{ service.name }}</span>
            </div>
            <span v-if="formData.services?.length > 3" class="more-items">
              +{{ formData.services.length - 3 }} mais serviços
            </span>
          </div>
        </template>
      </LiveCard>
      
      <!-- Card Portfólio -->
      <LiveCard
        id="portfolio"
        type="portfolio"
        title="Portfólio"
        icon="🖼️"
        :form-data="formData"
        :current-step-id="currentStepId"
        :fields="portfolioFields"
        :required-fields="[]"
        :shimmering-fields="shimmeringFields"
        :recently-filled-fields="recentlyFilledFields"
        :answered-fields="answeredFields"
        :validation-errors="validationErrors"
        @field-edit="handleFieldEdit"
        @field-focus="handleFieldFocus"
        @edit-feedback="handleEditFeedback"
      >
        <template #fields>
          <div class="portfolio-list">
            <div v-if="!formData.projects?.length" class="empty-state">
              <span class="empty-icon">🖼️</span>
              <span class="empty-text">Nenhum projeto cadastrado</span>
            </div>
            <div 
              v-for="(project, index) in formData.projects?.slice(0, 3)" 
              :key="project.id || index"
              class="portfolio-item"
            >
              <span class="portfolio-bullet">•</span>
              <span class="portfolio-name">{{ project.title || project.name }}</span>
            </div>
            <span v-if="formData.projects?.length > 3" class="more-items">
              +{{ formData.projects.length - 3 }} mais projetos
            </span>
          </div>
        </template>
      </LiveCard>
      
      <!-- Card FAQ -->
      <LiveCard
        id="faq"
        type="faq"
        title="Perguntas Frequentes"
        icon="❓"
        :form-data="formData"
        :current-step-id="currentStepId"
        :fields="faqFields"
        :required-fields="[]"
        :shimmering-fields="shimmeringFields"
        :recently-filled-fields="recentlyFilledFields"
        :answered-fields="answeredFields"
        :validation-errors="validationErrors"
        @field-edit="handleFieldEdit"
        @field-focus="handleFieldFocus"
        @edit-feedback="handleEditFeedback"
      >
        <template #fields>
          <div class="faq-list">
            <div v-if="!formData.faqItems?.length" class="empty-state">
              <span class="empty-icon">💬</span>
              <span class="empty-text">Nenhuma pergunta cadastrada</span>
            </div>
            <div 
              v-for="(item, index) in formData.faqItems?.slice(0, 2)" 
              :key="item.id || index"
              class="faq-item"
            >
              <span class="faq-question">{{ item.question }}</span>
            </div>
            <span v-if="formData.faqItems?.length > 2" class="more-items">
              +{{ formData.faqItems.length - 2 }} mais perguntas
            </span>
          </div>
        </template>
      </LiveCard>
      
      <!-- Card Depoimentos (opcional) -->
      <LiveCard
        v-if="showTestimonialsCard"
        id="testimonials"
        type="testimonials"
        title="Depoimentos"
        icon="⭐"
        :form-data="formData"
        :current-step-id="currentStepId"
        :fields="testimonialsFields"
        :required-fields="[]"
        :shimmering-fields="shimmeringFields"
        :recently-filled-fields="recentlyFilledFields"
        :answered-fields="answeredFields"
        :validation-errors="validationErrors"
        @field-edit="handleFieldEdit"
        @field-focus="handleFieldFocus"
        @edit-feedback="handleEditFeedback"
      >
        <template #fields>
          <div class="testimonials-list">
            <div v-if="!formData.testimonials?.length" class="empty-state">
              <span class="empty-icon">💬</span>
              <span class="empty-text">Nenhum depoimento cadastrado</span>
            </div>
            <div 
              v-for="(testimonial, index) in formData.testimonials?.slice(0, 2)" 
              :key="testimonial.id || index"
              class="testimonial-item"
            >
              <span class="testimonial-rating">{{ '⭐'.repeat(testimonial.rating || 5) }}</span>
              <span class="testimonial-author">{{ testimonial.authorName }}</span>
            </div>
            <span v-if="formData.testimonials?.length > 2" class="more-items">
              +{{ formData.testimonials.length - 2 }} mais depoimentos
            </span>
          </div>
        </template>
      </LiveCard>
      
      <!-- Card Blog (opcional) -->
      <LiveCard
        v-if="showBlogCard"
        id="blog"
        type="blog"
        title="Blog / Notícias"
        icon="📝"
        :form-data="formData"
        :current-step-id="currentStepId"
        :fields="blogFields"
        :required-fields="[]"
        :shimmering-fields="shimmeringFields"
        :recently-filled-fields="recentlyFilledFields"
        :answered-fields="answeredFields"
        :validation-errors="validationErrors"
        @field-edit="handleFieldEdit"
        @field-focus="handleFieldFocus"
        @edit-feedback="handleEditFeedback"
      />
      
      <!-- Card Vitrine/Produtos (opcional) -->
      <LiveCard
        v-if="showShowcaseCard"
        id="showcase"
        type="showcase"
        title="Vitrine de Produtos"
        icon="🛍️"
        :form-data="formData"
        :current-step-id="currentStepId"
        :fields="showcaseFields"
        :required-fields="[]"
        :shimmering-fields="shimmeringFields"
        :recently-filled-fields="recentlyFilledFields"
        :answered-fields="answeredFields"
        :validation-errors="validationErrors"
        @field-edit="handleFieldEdit"
        @field-focus="handleFieldFocus"
        @edit-feedback="handleEditFeedback"
      >
        <template #fields>
          <div class="products-list">
            <div v-if="!formData.products?.length" class="empty-state">
              <span class="empty-icon">📦</span>
              <span class="empty-text">Nenhum produto cadastrado</span>
            </div>
            <div 
              v-for="(product, index) in formData.products?.slice(0, 3)" 
              :key="product.id || index"
              class="product-item"
            >
              <span class="product-bullet">•</span>
              <span class="product-name">{{ product.name }}</span>
              <span v-if="product.price" class="product-price">{{ product.price }}</span>
            </div>
            <span v-if="formData.products?.length > 3" class="more-items">
              +{{ formData.products.length - 3 }} mais produtos
            </span>
          </div>
        </template>
      </LiveCard>
      
      <!-- Card Vídeos (opcional) -->
      <LiveCard
        v-if="showVideoCard"
        id="video"
        type="video"
        title="Vídeos"
        icon="🎬"
        :form-data="formData"
        :current-step-id="currentStepId"
        :fields="videoFields"
        :required-fields="[]"
        :shimmering-fields="shimmeringFields"
        :recently-filled-fields="recentlyFilledFields"
        :answered-fields="answeredFields"
        :validation-errors="validationErrors"
        @field-edit="handleFieldEdit"
        @field-focus="handleFieldFocus"
        @edit-feedback="handleEditFeedback"
      />
      
      <!-- Card Documentos PDF (opcional) -->
      <LiveCard
        v-if="showDocumentsCard"
        id="documents"
        type="documents"
        title="Documentos PDF"
        icon="📄"
        :form-data="formData"
        :current-step-id="currentStepId"
        :fields="documentsFields"
        :required-fields="[]"
        :shimmering-fields="shimmeringFields"
        :recently-filled-fields="recentlyFilledFields"
        :answered-fields="answeredFields"
        :validation-errors="validationErrors"
        @field-edit="handleFieldEdit"
        @field-focus="handleFieldFocus"
        @edit-feedback="handleEditFeedback"
      />
      
      <!-- Card Finalização -->
      <LiveCard
        id="finalization"
        type="finalization"
        title="Finalização"
        icon="🚀"
        :form-data="formData"
        :current-step-id="currentStepId"
        :fields="finalizationFields"
        :required-fields="[]"
        :shimmering-fields="shimmeringFields"
        :recently-filled-fields="recentlyFilledFields"
        :answered-fields="answeredFields"
        :validation-errors="validationErrors"
        @field-edit="handleFieldEdit"
        @field-focus="handleFieldFocus"
        @edit-feedback="handleEditFeedback"
      />
    </div>
    
    <!-- Indicador de Step Atual -->
    <div class="current-step-indicator">
      <span class="indicator-pulse"></span>
      <span class="indicator-text">Conversando sobre: <strong>{{ currentStepName }}</strong></span>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';
import LiveCard from './LiveCard.vue';

// Mapeamento de business types
const businessTypeLabels = {
  alimentacao: '🍔 Alimentação',
  saude: '🏥 Saúde',
  beleza: '💅 Beleza',
  educacao: '📚 Educação',
  tecnologia: '💻 Tecnologia',
  construcao: '🏗️ Construção',
  moda: '👗 Moda',
  servicos: '🔧 Serviços',
  juridico: '⚖️ Jurídico',
  eventos: '🎉 Eventos',
  turismo: '✈️ Turismo',
  imoveis: '🏡 Imóveis',
  automotivo: '🚗 Automotivo',
  pets: '🐾 Pets',
  comercio: '🏪 Comércio',
  industria: '🏭 Indústria',
  outro: '📦 Outro'
};

// Mapeamento de urgência
const urgencyLabels = {
  urgent: '🔥 Urgente',
  normal: '📅 Normal',
  relaxed: '🧘 Tranquilo'
};

// Mapeamento de steps para nomes
const stepNames = {
  identity: 'Identidade da Marca',
  contact: 'Contato',
  leads: 'Configuração de Leads',
  about: 'Sobre a Empresa',
  services: 'Serviços',
  portfolio: 'Portfólio',
  faq: 'Perguntas Frequentes',
  testimonials: 'Depoimentos',
  blog: 'Blog / Notícias',
  showcase: 'Vitrine de Produtos',
  video: 'Vídeos',
  documents: 'Documentos PDF',
  finalization: 'Finalização'
};

export default {
  name: 'FormPreviewLive',
  
  components: {
    LiveCard
  },
  
  props: {
    formData: {
      type: Object,
      required: true
    },
    // Detalhes do pedido para cálculo preciso de progresso
    orderDetails: {
      type: Object,
      default: () => ({ selection: { pages: {}, content: {} } }) 
    },
    currentStepId: {
      type: String,
      default: ''
    },
    shimmeringFields: {
      type: Array,
      default: () => []
    },
    recentlyFilledFields: {
      type: Array,
      default: () => []
    },
    answeredFields: {
      type: Array,
      default: () => []
    },
    validationErrors: {
      type: Object,
      default: () => ({})
    }
  },
  
  emits: [
    'field-click',
    'field-edit', 
    'image-upload',
    'image-remove',
    'edit-feedback',
    'send-chat-message'
  ],
  
  setup(props, { emit }) {
    // ============================================
    // COMPUTED - Definição de campos por card
    // ============================================
    
    const identityFields = computed(() => [
      { id: 'companyName', label: 'Nome', placeholder: 'Nome da empresa' },
      { 
        id: 'businessType', 
        label: 'Ramo',
        editable: false,
        formatter: (val) => businessTypeLabels[val] || val
      },
      { id: 'frase', label: 'Frase', placeholder: 'Slogan ou frase de efeito' },
      { id: 'voiceTone', label: 'Tom de Voz', placeholder: 'Formal, descontraído...' }
    ]);
    
    const contactFields = computed(() => [
      { id: 'whatsapp', label: 'WhatsApp', placeholder: '(11) 99999-9999' },
      { id: 'email', label: 'E-mail', placeholder: 'contato@empresa.com' },
      { 
        id: 'socialNetworks', 
        label: 'Redes Sociais',
        type: 'socialNetworks',  // Tipo especial para edição customizada
        editable: true,  // AGORA EDITÁVEL
        placeholder: 'Clique para adicionar redes sociais',
        formatter: (val) => {
          // Se o campo foi perguntado/respondido, usar o valor atual
          if (props.answeredFields.includes('socialNetworks')) {
            if (!val || (Array.isArray(val) && val.length === 0)) {
              return '❌ Não possui redes sociais';
            }
          } else {
            // Se ainda não foi perguntado
            if (!val) return '⏳ Ainda não perguntado';
          }
          
          if (!Array.isArray(val)) {
            // Se veio como string (ex: "@unligames"), mostrar mesmo assim
            if (typeof val === 'string' && val.trim()) return val;
            return '❌ Não possui redes sociais';
          }
          if (val.length === 0) return '❌ Não possui redes sociais';
          // Mostrar as redes configuradas
          return val.map(r => {
            const icon = {
              instagram: '📸',
              linkedin: '💼',
              twitter: '🐦',
              facebook: '📘',
              youtube: '🎬',
              tiktok: '🎵'
            }[r.type] || '🔗';
            return `${icon} ${r.url}`;
          }).join(' • ');
        }
      },
      {
        id: 'hasPhysicalLocation',
        label: 'Endereço',
        type: 'addressToggle',  // Tipo especial para toggle + campos de endereço
        editable: true,  // AGORA EDITÁVEL
        labelOn: 'Possui endereço físico',
        labelOff: 'Não possui (100% online)',
        formatter: (val) => {
          // Se foi respondido explicitamente como false = 100% online
          if (props.answeredFields.includes('hasPhysicalLocation') && val === false) {
            return '🌐 100% Online';
          }
          // Se ainda não foi perguntado
          if (!props.answeredFields.includes('hasPhysicalLocation')) {
            return '⏳ Ainda não perguntado';
          }
          // Se tem endereço, mostrar formatado
          if (val === true) {
            const { addressStreet, addressNumber, addressCity, addressState, addressCep } = props.formData;
            if (addressCity && addressState) {
              let addr = `📍 ${addressCity}, ${addressState}`;
              if (addressStreet) addr = `📍 ${addressStreet}${addressNumber ? ', ' + addressNumber : ''} - ${addressCity}/${addressState}`;
              if (addressCep) addr += ` (${addressCep})`;
              return addr;
            }
            return '📍 Com endereço físico (aguardando dados)';
          }
          return '⏳ Ainda não perguntado';
        }
      },
      // Campos de endereço individuais (visíveis quando hasPhysicalLocation = true)
      {
        id: 'addressCep',
        label: 'CEP',
        placeholder: '00000-000',
        hidden: !props.formData.hasPhysicalLocation
      },
      {
        id: 'addressStreet',
        label: 'Rua',
        placeholder: 'Nome da rua',
        hidden: !props.formData.hasPhysicalLocation
      },
      {
        id: 'addressNumber',
        label: 'Número',
        placeholder: '123',
        hidden: !props.formData.hasPhysicalLocation
      },
      {
        id: 'addressComplement',
        label: 'Complemento',
        placeholder: 'Sala 101, Bloco A...',
        hidden: !props.formData.hasPhysicalLocation
      },
      {
        id: 'addressNeighborhood',
        label: 'Bairro',
        placeholder: 'Bairro',
        hidden: !props.formData.hasPhysicalLocation
      },
      {
        id: 'addressCity',
        label: 'Cidade',
        placeholder: 'Cidade',
        hidden: !props.formData.hasPhysicalLocation
      },
      {
        id: 'addressState',
        label: 'Estado',
        placeholder: 'UF',
        hidden: !props.formData.hasPhysicalLocation
      },
      {
        id: 'businessHours',
        label: 'Horário',
        placeholder: 'Ex: Seg-Sex 9h às 18h',
        formatter: (val) => {
          if (!props.answeredFields.includes('businessHours')) {
            return '⏳ Ainda não perguntado';
          }
          if (!val) return '❌ Não possui horário definido';
          return `🕐 ${val}`;
        }
      }
    ]);
    
    const leadsFields = computed(() => [
      { id: 'leadEmail', label: 'E-mail de Leads', placeholder: 'contato@empresa.com' },
      { id: 'leadEmailCC', label: 'E-mail Cópia', placeholder: 'vendas@empresa.com' },
      {
        id: 'formFields',
        label: 'Campos do Formulário',
        editable: false,
        formatter: (val) => {
          if (!val || typeof val !== 'object') return 'Padrão';
          const enabled = Object.values(val).filter(f => f?.enabled).length;
          return `${enabled + 2} campos ativos`; // +2 para nome e email (fixos)
        }
      },
      {
        id: 'whatsappFloatingEnabled',
        label: 'WhatsApp Flutuante',
        editable: false,
        formatter: (val) => val ? '✅ Ativado' : '❌ Desativado'
      }
    ]);
    
    const aboutFields = computed(() => [
      { id: 'foundingYear', label: 'Fundação', placeholder: 'Ano de fundação' },
      { id: 'companyBio', label: 'História', placeholder: 'Conte a história da empresa' },
      {
        id: 'companyHighlights',
        label: 'Diferenciais',
        editable: false,
        formatter: (val) => {
          if (!val || !Array.isArray(val)) return 'Não definidos';
          return `${val.length} diferencial(is)`;
        }
      }
    ]);
    
    const servicesFields = computed(() => [
      {
        id: 'services',
        label: 'Serviços',
        editable: false,
        formatter: (val) => {
          if (!val || !Array.isArray(val)) return 'Nenhum serviço';
          return `${val.length} serviço(s) cadastrado(s)`;
        }
      }
    ]);
    
    const portfolioFields = computed(() => [
      {
        id: 'projects',
        label: 'Projetos',
        editable: false,
        formatter: (val) => {
          if (!val || !Array.isArray(val)) return 'Nenhum projeto';
          return `${val.length} projeto(s) cadastrado(s)`;
        }
      },
      {
        id: 'bigClients',
        label: 'Clientes',
        editable: false,
        formatter: (val) => {
          if (!val || !Array.isArray(val) || val.length === 0) return 'Não informados';
          return val.slice(0, 3).join(', ') + (val.length > 3 ? '...' : '');
        }
      }
    ]);
    
    const faqFields = computed(() => [
      {
        id: 'faqItems',
        label: 'Perguntas',
        editable: false,
        formatter: (val) => {
          if (!val || !Array.isArray(val)) return 'Nenhuma pergunta';
          return `${val.length} pergunta(s)`;
        }
      }
    ]);
    
    const finalizationFields = computed(() => [
      {
        id: 'urgency',
        label: 'Urgência',
        editable: false,
        formatter: (val) => urgencyLabels[val] || 'Não definida'
      },
      { id: 'additionalNotes', label: 'Observações', placeholder: 'Notas adicionais' }
    ]);
    
    // ============================================
    // CAMPOS DOS STEPS OPCIONAIS
    // ============================================
    
    const testimonialsFields = computed(() => [
      {
        id: 'testimonials',
        label: 'Depoimentos',
        editable: false,
        formatter: (val) => {
          if (!val || !Array.isArray(val) || val.length === 0) return 'Nenhum depoimento';
          return `${val.length} depoimento(s)`;
        }
      },
      { id: 'averageRating', label: 'Nota Média', placeholder: 'Ex: 4.9' },
      { id: 'totalReviews', label: 'Total de Avaliações', placeholder: 'Ex: 200+' }
    ]);
    
    const blogFields = computed(() => [
      {
        id: 'blogPurpose',
        label: 'Objetivo',
        editable: false,
        formatter: (val) => {
          const purposes = { seo: '🔍 SEO', authority: '🏆 Autoridade', news: '📰 Notícias', education: '📚 Educação' };
          return purposes[val] || 'Não definido';
        }
      },
      {
        id: 'mainTopics',
        label: 'Temas',
        editable: false,
        formatter: (val) => {
          if (!val || !Array.isArray(val) || val.length === 0) return 'Não definidos';
          return val.slice(0, 3).join(', ') + (val.length > 3 ? '...' : '');
        }
      },
      {
        id: 'postFrequency',
        label: 'Frequência',
        editable: false,
        formatter: (val) => {
          const freqs = { weekly: 'Semanal', biweekly: 'Quinzenal', monthly: 'Mensal', sporadic: 'Esporádico' };
          return freqs[val] || 'Não definida';
        }
      }
    ]);
    
    const showcaseFields = computed(() => [
      {
        id: 'products',
        label: 'Produtos',
        editable: false,
        formatter: (val) => {
          if (!val || !Array.isArray(val) || val.length === 0) return 'Nenhum produto';
          return `${val.length} produto(s)`;
        }
      },
      { id: 'storeIntro', label: 'Apresentação', placeholder: 'Texto de apresentação' },
      { id: 'shippingInfo', label: 'Frete', placeholder: 'Informações de entrega' }
    ]);
    
    const videoFields = computed(() => [
      { id: 'videoIntro', label: 'Uso dos Vídeos', placeholder: 'Como você quer usar os vídeos' },
      {
        id: 'videos',
        label: 'Vídeos',
        editable: false,
        formatter: (val) => {
          if (!val || !Array.isArray(val) || val.length === 0) return 'Nenhum vídeo';
          return `${val.length} vídeo(s) para upload`;
        }
      },
      {
        id: 'youtubeVideos',
        label: 'YouTube',
        editable: false,
        formatter: (val) => {
          if (!val || !Array.isArray(val) || val.length === 0) return 'Nenhum link';
          return `${val.length} link(s) do YouTube`;
        }
      }
    ]);
    
    const documentsFields = computed(() => [
      {
        id: 'documentPurpose',
        label: 'Tipos',
        editable: false,
        formatter: (val) => {
          if (!val || !Array.isArray(val) || val.length === 0) return 'Não definidos';
          return `${val.length} tipo(s)`;
        }
      },
      {
        id: 'documents',
        label: 'Documentos',
        editable: false,
        formatter: (val) => {
          if (!val || !Array.isArray(val) || val.length === 0) return 'Nenhum documento';
          return `${val.length} documento(s)`;
        }
      },
      { id: 'downloadPageTitle', label: 'Título da Página', placeholder: 'Downloads' }
    ]);
    
    // ============================================
    // CONDIÇÕES DE EXIBIÇÃO DOS CARDS OPCIONAIS
    // ============================================
    
    // Por enquanto sempre mostramos se tem dados, futuramente baseado em purchasedPages
    const showTestimonialsCard = computed(() => {
      return props.formData.testimonials?.length > 0 || 
             props.formData.testimonialsIntro ||
             props.currentStepId === 'testimonials';
    });
    
    const showBlogCard = computed(() => {
      return props.formData.blogPurpose || 
             props.formData.mainTopics?.length > 0 ||
             props.currentStepId === 'blog';
    });
    
    const showShowcaseCard = computed(() => {
      return props.formData.products?.length > 0 || 
             props.formData.storeIntro ||
             props.currentStepId === 'showcase';
    });
    
    const showVideoCard = computed(() => {
      return props.formData.videos?.length > 0 || 
             props.formData.youtubeVideos?.length > 0 ||
             props.formData.videoIntro ||
             props.currentStepId === 'video';
    });
    
    const showDocumentsCard = computed(() => {
      return props.formData.documents?.length > 0 || 
             props.formData.documentPurpose?.length > 0 ||
             props.currentStepId === 'documents';
    });
    
    // Nome do step atual
    const currentStepName = computed(() => {
      return stepNames[props.currentStepId] || 'Onboarding';
    });
    
    // Progresso geral - Cálculo inteligente baseado em módulos comprados
    const overallProgress = computed(() => {
      // 1. Definição dos Módulos Comprados
      // Se não tiver orderDetails, assume padrão (tudo ativo exceto opcionais raros)
      const pages = props.orderDetails?.selection?.pages || { 
        about: 1, services: 1, portfolio: 1, faq: 1, contact: 1, blog: 0, showcase: 0 
      };
      // eslint-disable-next-line no-unused-vars
      const content = props.orderDetails?.selection?.content || { 
        video_basic: false, pdf: false 
      };

      // 2. Mapeamento de Campos por Módulo com Pesos
      // Peso 3: Crítico / Peso 2: Importante / Peso 1: Opcional
      const modules = [
        {
          id: 'identity',
          active: true, // Sempre ativo
          fields: [
            { key: 'companyName', weight: 3 },
            { key: 'businessType', weight: 3 },
            { key: 'primaryColor', weight: 2 },
            { key: 'frase', weight: 1 },
            { key: 'logo', weight: 2, alt: 'hasNoLogo' } // aceita logo OU hasNoLogo
          ]
        },
        {
          id: 'contact',
          active: true,
          fields: [
            { key: 'whatsapp', weight: 3 },
            { key: 'email', weight: 1 },
            { key: 'socialNetworks', weight: 1, isArray: true }
          ]
        },
        {
          id: 'about',
          active: !!pages.about,
          fields: [
            { key: 'companyBio', weight: 3 },
            { key: 'foundingYear', weight: 1 }
          ]
        },
        {
          id: 'services',
          active: !!pages.services,
          fields: [
            { key: 'services', weight: 3, isArray: true, min: 1 } // min 1 item
          ]
        },
        {
          id: 'portfolio',
          active: !!pages.portfolio,
          fields: [
            { key: 'projects', weight: 2, isArray: true }
          ]
        },
        {
          id: 'faq',
          active: !!pages.faq,
          fields: [
            { key: 'faqItems', weight: 2, isArray: true }
          ]
        },
        {
          id: 'blog',
          active: !!pages.blog,
          fields: [
            { key: 'blogPurpose', weight: 2 },
            { key: 'mainTopics', weight: 2, isArray: true }
          ]
        },
        {
          id: 'showcase',
          active: !!pages.showcase,
          fields: [
            { key: 'products', weight: 3, isArray: true }
          ]
        }
      ];

      let totalPoints = 0;
      let earnedPoints = 0;

      modules.forEach(mod => {
        if (!mod.active) return; // Pula módulos não comprados

        mod.fields.forEach(field => {
          totalPoints += field.weight;

          const val = props.formData[field.key];
          let isFilled = false;

          if (field.isArray) {
            // Para arrays (serviços, produtos), verifica se tem itens
            isFilled = Array.isArray(val) && val.length >= (field.min || 1);
          } else if (field.alt) {
            // Para campos com alternativa (Logo OU Sem Logo)
            const altVal = props.formData[field.alt];
            isFilled = (val && val !== '') || (altVal && altVal !== false);
          } else {
            // Campos normais
            isFilled = val && val !== '';
          }

          if (isFilled) {
            earnedPoints += field.weight;
          }
        });
      });

      if (totalPoints === 0) return 0;
      return Math.min(100, Math.round((earnedPoints / totalPoints) * 100));
    });
    
    // ============================================
    // METHODS
    // ============================================
    
    function handleFieldEdit(fieldId, value) {
      emit('field-edit', fieldId, value);
    }
    
    function handleFieldFocus(fieldId) {
      emit('field-click', fieldId);
    }
    
    function handleImageUpload(data) {
      emit('image-upload', data);
    }
    
    function handleImageRemove(data) {
      emit('image-remove', data);
    }
    
    function handleEditFeedback(data) {
      emit('edit-feedback', data);
      
      // Emitir mensagem para o chat
      emit('send-chat-message', `Perfeito, alteração salva no campo "${data.label}"!`);
    }
    
    return {
      // Fields config
      identityFields,
      contactFields,
      leadsFields,
      aboutFields,
      servicesFields,
      portfolioFields,
      faqFields,
      finalizationFields,
      
      // Campos dos steps opcionais
      testimonialsFields,
      blogFields,
      showcaseFields,
      videoFields,
      documentsFields,
      
      // Condições de exibição dos cards opcionais
      showTestimonialsCard,
      showBlogCard,
      showShowcaseCard,
      showVideoCard,
      showDocumentsCard,
      
      // Computed
      currentStepName,
      overallProgress,
      
      // Methods
      handleFieldEdit,
      handleFieldFocus,
      handleImageUpload,
      handleImageRemove,
      handleEditFeedback
    };
  }
};
</script>

<style lang="scss" scoped>
// ============================================
// FORM PREVIEW LIVE - Container Principal
// ============================================

.form-preview-live {
  display: flex;
  flex-direction: column;
  height: 100%;
  padding: 1rem;
  overflow-y: auto;
}

// ============================================
// HEADER
// ============================================

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.25rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.header-content {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.preview-title {
  font-size: 1.125rem;
  font-weight: 700;
  margin: 0;
  color: #fff;
}

.preview-subtitle {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.5);
}

.header-stats {
  .stat {
    display: inline-block;
    padding: 0.375rem 0.75rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 2rem;
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.7);
    
    &.is-complete {
      background: rgba(34, 197, 94, 0.15);
      color: #86efac;
    }
  }
}

// ============================================
// CARDS CONTAINER
// ============================================

.live-cards-container {
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
  flex: 1;
}

// ============================================
// CUSTOM CARD CONTENT (Services, FAQ)
// ============================================

.services-list,
.faq-list {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.empty-state {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.02);
  border-radius: 0.5rem;
  
  .empty-icon {
    font-size: 1rem;
    opacity: 0.5;
  }
  
  .empty-text {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.4);
    font-style: italic;
  }
}

.service-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.375rem 0;
  
  .service-bullet {
    color: #6366f1;
  }
  
  .service-name {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.8);
  }
}

.faq-item {
  padding: 0.5rem;
  background: rgba(255, 255, 255, 0.02);
  border-radius: 0.375rem;
  
  .faq-question {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.7);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
}

.more-items {
  font-size: 0.75rem;
  color: #a5b4fc;
  padding: 0.25rem 0;
}

// ============================================
// CURRENT STEP INDICATOR
// ============================================

.current-step-indicator {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  margin-top: auto;
  padding: 1rem;
  background: rgba(99, 102, 241, 0.1);
  border: 1px solid rgba(99, 102, 241, 0.2);
  border-radius: 0.75rem;
}

.indicator-pulse {
  width: 8px;
  height: 8px;
  background: #6366f1;
  border-radius: 50%;
  animation: pulse-glow 1.5s ease-in-out infinite;
}

.indicator-text {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.7);
  
  strong {
    color: #a5b4fc;
  }
}

// ============================================
// ANIMATIONS
// ============================================

@keyframes pulse-glow {
  0%, 100% { 
    opacity: 1;
    box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.4);
  }
  50% { 
    opacity: 0.6;
    box-shadow: 0 0 0 8px rgba(99, 102, 241, 0);
  }
}

// ============================================
// SCROLLBAR (aplicado ao container principal)
// ============================================

.form-preview-live {
  &::-webkit-scrollbar {
    width: 4px;
  }

  &::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.02);
  }

  &::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 2px;
    
    &:hover {
      background: rgba(255, 255, 255, 0.2);
    }
  }
}

// ============================================
// RESPONSIVE
// ============================================

@media (max-width: 768px) {
  .form-preview-live {
    padding: 0.75rem;
  }
  
  .preview-header {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .live-cards-container {
    gap: 0.75rem;
  }
}
</style>
