<template>
  <div class="onboarding-wizard">
    <!-- Progress Bar -->
    <div class="wizard-progress">
      <div class="progress-bar">
        <div 
          class="progress-fill" 
          :style="{ width: `${(currentStep / totalSteps) * 100}%` }"
        ></div>
      </div>
      <div class="progress-text">
        Etapa {{ currentStep }} de {{ totalSteps }}
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Carregando suas informações...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-container">
      <div class="error-icon">⚠️</div>
      <h2>Oops! Algo deu errado</h2>
      <p>{{ error }}</p>
      <button @click="retryLoad" class="btn-primary">Tentar Novamente</button>
    </div>

    <!-- Wizard Content -->
    <div v-else class="wizard-content">
      <transition name="fade" mode="out-in">
        <div :key="currentStep" class="wizard-step-container">
          <!-- Step 1: Identidade Vital -->
          <div v-if="currentStep === 1" class="wizard-step">
            <div class="step-header">
              <h2>✨ Identidade Vital</h2>
              <p>Vamos começar com o básico da sua empresa</p>
            </div>

            <div class="form-group">
              <label for="company-name">Nome da Empresa / Projeto *</label>
              <input
                id="company-name"
                v-model="formData.companyName"
                type="text"
                placeholder="Ex: Padaria Dona Maria"
                @input="autoSave"
                required
              />
            </div>

            <div class="form-group">
              <label for="slogan">Slogan ou Frase de Efeito</label>
              <input
                id="slogan"
                v-model="formData.slogan"
                type="text"
                placeholder="Ex: A melhor pizza da Mooca"
                @input="autoSave"
              />
            </div>

            <div class="form-group">
              <label>Logotipo</label>
              <div class="logo-upload">
                <div v-if="formData.logoPreview" class="logo-preview">
                  <img :src="formData.logoPreview" alt="Logo Preview" />
                  <button @click="removeLogo" class="btn-remove">✕</button>
                </div>
                <div v-else class="upload-area">
                  <input
                    id="logo-upload"
                    type="file"
                    accept="image/*"
                    @change="handleLogoUpload"
                    hidden
                  />
                  <label for="logo-upload" class="upload-label">
                    <div class="upload-icon">📷</div>
                    <span>Clique para fazer upload</span>
                    <small>PNG, JPG ou SVG (máx. 5MB)</small>
                  </label>
                </div>
              </div>
              <div class="form-check">
                <input
                  id="no-logo"
                  v-model="formData.hasNoLogo"
                  type="checkbox"
                  @change="autoSave"
                />
                <label for="no-logo">
                  Ainda não tenho logo (Criaremos um texto estilizado)
                </label>
              </div>
            </div>
          </div>

          <!-- Step 2: Contato e Localização -->
          <div v-if="currentStep === 2" class="wizard-step">
            <div class="step-header">
              <h2>📞 Contato e Localização</h2>
              <p>Como seus clientes vão te encontrar?</p>
            </div>

            <div class="form-group">
              <label for="whatsapp">WhatsApp Principal *</label>
              <input
                id="whatsapp"
                v-model="formData.whatsapp"
                type="tel"
                placeholder="(11) 99999-9999"
                @input="autoSave"
                required
              />
              <small class="field-hint">
                💡 Este será o botão flutuante do site
              </small>
            </div>

            <!-- Social Networks Dynamic List -->
            <div class="form-group">
              <label>Redes Sociais</label>
              <div class="social-networks-list">
                <div 
                  v-for="(social, index) in formData.socialNetworks" 
                  :key="index" 
                  class="social-network-item"
                >
                  <div class="social-network-select">
                    <select 
                      v-model="social.type" 
                      @change="autoSave"
                    >
                      <option value="" disabled>Selecione...</option>
                      <option 
                        v-for="option in availableSocialOptions(index)" 
                        :key="option.value" 
                        :value="option.value"
                      >
                        {{ option.icon }} {{ option.label }}
                      </option>
                    </select>
                  </div>
                  <div class="social-network-input">
                    <input
                      v-model="social.url"
                      type="url"
                      :placeholder="getSocialPlaceholder(social.type)"
                      @input="autoSave"
                    />
                  </div>
                  <button 
                    v-if="formData.socialNetworks.length > 1"
                    type="button" 
                    class="btn-remove-social" 
                    @click="removeSocialNetwork(index)"
                    title="Remover rede social"
                  >
                    ✕
                  </button>
                </div>
              </div>
              <button 
                type="button" 
                class="btn-add-social" 
                @click="addSocialNetwork"
                :disabled="formData.socialNetworks.length >= socialNetworkOptions.length"
              >
                <span class="add-icon">+</span> Adicionar rede social
              </button>
            </div>

            <!-- Address Section -->
            <div class="form-group">
              <div class="address-header">
                <label>Endereço Físico</label>
                <div class="switch-container">
                  <label class="switch">
                    <input
                      type="checkbox"
                      v-model="formData.noPhysicalLocation"
                      @change="handleNoPhysicalLocationChange"
                    />
                    <span class="slider"></span>
                  </label>
                  <span class="switch-label">Não tenho endereço físico</span>
                </div>
              </div>

              <div v-if="!formData.noPhysicalLocation" class="address-fields">
                <!-- CEP -->
                <div class="address-row">
                  <div class="form-field cep-field">
                    <label for="cep">CEP *</label>
                    <div class="cep-input-wrapper">
                      <input
                        id="cep"
                        v-model="formData.addressCep"
                        type="text"
                        placeholder="00000-000"
                        maxlength="9"
                        @input="handleCepInput"
                        @blur="fetchAddressByCep"
                      />
                      <div v-if="loadingCep" class="cep-loading">
                        <span class="spinner-small"></span>
                      </div>
                    </div>
                    <small v-if="cepError" class="field-error">{{ cepError }}</small>
                    <small v-else class="field-hint">💡 Digite o CEP para preencher automaticamente</small>
                  </div>
                </div>

                <!-- Street and Number -->
                <div class="address-row">
                  <div class="form-field street-field">
                    <label for="street">Rua / Logradouro *</label>
                    <input
                      id="street"
                      v-model="formData.addressStreet"
                      type="text"
                      placeholder="Rua das Flores"
                      @input="autoSave"
                    />
                  </div>
                  <div class="form-field number-field">
                    <label for="number">Número *</label>
                    <input
                      id="number"
                      v-model="formData.addressNumber"
                      type="text"
                      placeholder="123"
                      @input="autoSave"
                    />
                  </div>
                </div>

                <!-- Complement -->
                <div class="address-row">
                  <div class="form-field">
                    <label for="complement">Complemento</label>
                    <input
                      id="complement"
                      v-model="formData.addressComplement"
                      type="text"
                      placeholder="Sala 101, Bloco A..."
                      @input="autoSave"
                    />
                  </div>
                </div>

                <!-- Neighborhood and City -->
                <div class="address-row">
                  <div class="form-field">
                    <label for="neighborhood">Bairro *</label>
                    <input
                      id="neighborhood"
                      v-model="formData.addressNeighborhood"
                      type="text"
                      placeholder="Centro"
                      @input="autoSave"
                    />
                  </div>
                  <div class="form-field">
                    <label for="city">Cidade *</label>
                    <input
                      id="city"
                      v-model="formData.addressCity"
                      type="text"
                      placeholder="São Paulo"
                      @input="autoSave"
                    />
                  </div>
                </div>

                <!-- State and Country -->
                <div class="address-row">
                  <div class="form-field state-field">
                    <label for="state">Estado *</label>
                    <select
                      id="state"
                      v-model="formData.addressState"
                      @change="autoSave"
                    >
                      <option value="" disabled>Selecione...</option>
                      <option v-for="state in brazilStates" :key="state.value" :value="state.value">
                        {{ state.label }}
                      </option>
                    </select>
                  </div>
                  <div class="form-field country-field">
                    <label for="country">País</label>
                    <input
                      id="country"
                      v-model="formData.addressCountry"
                      type="text"
                      placeholder="Brasil"
                      @input="autoSave"
                      disabled
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 3: Conteúdo -->
          <div v-if="currentStep === 3" class="wizard-step">
            <div class="step-header">
              <h2>📝 Conteúdo do Site</h2>
              <p>Vamos criar o texto que vai convencer seus clientes</p>
            </div>

            <div class="form-group">
              <label for="description">O que sua empresa faz? (Em 1 frase) *</label>
              <textarea
                id="description"
                v-model="formData.description"
                rows="2"
                placeholder="Nós ajudamos empresas a..."
                @input="autoSave"
                required
              ></textarea>
            </div>

            <div class="form-group">
              <label>Quais são seus 3 principais serviços/produtos? *</label>
              <div
                v-for="(service, index) in formData.services"
                :key="index"
                class="service-item"
              >
                <div class="service-number">{{ index + 1 }}</div>
                <div class="service-fields">
                  <input
                    v-model="service.name"
                    type="text"
                    placeholder="Nome do serviço"
                    @input="autoSave"
                    required
                  />
                  <textarea
                    v-model="service.description"
                    rows="2"
                    placeholder="Breve descrição"
                    @input="autoSave"
                  ></textarea>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label>Por que escolher você? (Diferenciais)</label>
              <div class="differentials-grid">
                <div
                  v-for="diff in differentialOptions"
                  :key="diff.value"
                  class="differential-option"
                >
                  <input
                    :id="'diff-' + diff.value"
                    v-model="formData.differentials"
                    type="checkbox"
                    :value="diff.value"
                    @change="autoSave"
                  />
                  <label :for="'diff-' + diff.value">
                    <span class="check-icon">{{ diff.icon }}</span>
                    {{ diff.label }}
                  </label>
                </div>
              </div>
              <textarea
                v-model="formData.customDifferentials"
                rows="2"
                placeholder="Outros diferenciais..."
                @input="autoSave"
                class="mt-3"
              ></textarea>
            </div>
          </div>

          <!-- Step 4: Estilo Visual -->
          <div v-if="currentStep === 4" class="wizard-step">
            <div class="step-header">
              <h2>🎨 Estilo Visual</h2>
              <p>Como você quer que seu site pareça?</p>
            </div>

            <div class="form-group">
              <label>Qual a cor principal da sua marca? *</label>
              <div class="color-picker-container">
                <div class="color-presets">
                  <div
                    v-for="color in colorPresets"
                    :key="color.value"
                    class="color-preset"
                    :class="{ active: formData.primaryColor === color.value }"
                    :style="{ backgroundColor: color.value }"
                    @click="selectColor(color.value)"
                    :title="color.name"
                  ></div>
                </div>
                <div class="custom-color">
                  <label for="custom-color">Ou escolha uma cor customizada:</label>
                  <input
                    id="custom-color"
                    v-model="formData.primaryColor"
                    type="color"
                    @change="autoSave"
                  />
                  <span class="color-value">{{ formData.primaryColor }}</span>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label>Qual estilo você prefere? *</label>
              <div class="style-options">
                <div
                  v-for="style in styleOptions"
                  :key="style.value"
                  class="style-option"
                  :class="{ active: formData.designStyle === style.value }"
                  @click="selectStyle(style.value)"
                >
                  <div class="style-preview" :class="'preview-' + style.value">
                    <div class="preview-content">
                      <div class="preview-header"></div>
                      <div class="preview-body">
                        <div class="preview-line"></div>
                        <div class="preview-line short"></div>
                      </div>
                    </div>
                  </div>
                  <div class="style-info">
                    <h4>{{ style.label }}</h4>
                    <p>{{ style.description }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 5: Finalização -->
          <div v-if="currentStep === 5" class="wizard-step">
          <div class="step-header">
            <h2>Quase lá!</h2>
            <p>Última etapa antes de começarmos a criar seu site</p>
          </div>

          <div class="summary-section">
            <h3>Resumo das suas informações:</h3>
            <div class="summary-grid">
              <div class="summary-item">
                <strong>Empresa:</strong>
                <span>{{ formData.companyName }}</span>
              </div>
              <div class="summary-item">
                <strong>WhatsApp:</strong>
                <span>{{ formData.whatsapp }}</span>
              </div>
              <div class="summary-item">
                <strong>Serviços:</strong>
                <span>{{ formData.services.filter(s => s.name).length }} cadastrados</span>
              </div>
              <div class="summary-item">
                <strong>Estilo:</strong>
                <span>{{ getStyleLabel(formData.designStyle) }}</span>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="notes">Alguma observação extra?</label>
            <textarea
              id="notes"
              v-model="formData.notes"
              rows="4"
              placeholder="Conte-nos mais sobre o que você imagina para o seu site..."
              @input="autoSave"
            ></textarea>
          </div>

          <div class="final-cta">
            <div class="cta-icon">✨</div>
            <h3>Tudo pronto!</h3>
            <p>
              Clique abaixo para enviar suas informações e iniciar a produção do seu site.
              Nossa equipe receberá tudo e começará a trabalhar imediatamente!
            </p>
          </div>
        </div>
        </div>
      </transition>

      <!-- Navigation Buttons -->
      <div class="wizard-navigation">
        <button
          v-if="currentStep > 1"
          @click="previousStep"
          class="btn-secondary"
          :disabled="submitting"
        >
          ← Voltar
        </button>
        <button
          v-if="currentStep < totalSteps"
          @click="nextStep"
          class="btn-primary"
          :disabled="!canProceed"
        >
          Próximo →
        </button>
        <button
          v-if="currentStep === totalSteps"
          @click="submitForm"
          class="btn-success"
          :disabled="submitting || !canProceed"
        >
          <span v-if="!submitting">🚀 Enviar e Iniciar Produção</span>
          <span v-else>Enviando...</span>
        </button>
      </div>

      <!-- Auto-save indicator -->
      <div v-if="autoSaving" class="autosave-indicator">
        💾 Salvando rascunho...
      </div>
      <div v-else-if="lastSaved" class="autosave-indicator saved">
        ✓ Salvo {{ lastSaved }}
      </div>
    </div>

    <!-- Success Modal -->
    <div v-if="showSuccessModal" class="modal-overlay" @click="closeSuccessModal">
      <div class="modal-content success" @click.stop>
        <div class="success-icon">🎉</div>
        <h2>Informações Recebidas!</h2>
        <p>
          Obrigado por preencher o briefing! Nossa equipe já recebeu tudo e começará
          a trabalhar no seu site imediatamente.
        </p>
        <p class="success-details">
          Você receberá atualizações por e-mail conforme o desenvolvimento avança.
          <strong>Previsão de entrega: 5-7 dias úteis.</strong>
        </p>
        <button @click="closeSuccessModal" class="btn-primary">
          Entendido!
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OnboardingWizard',
  
  data() {
    return {
      // State management
      loading: true,
      error: null,
      submitting: false,
      autoSaving: false,
      lastSaved: null,
      showSuccessModal: false,
      loadingCep: false,
      cepError: null,
      
      // Wizard control
      currentStep: 1,
      totalSteps: 5,
      
      // Token from URL
      token: null,
      orderId: null,
      
      // Form data
      formData: {
        // Step 1: Identidade
        companyName: '',
        slogan: '',
        logo: null,
        logoPreview: null,
        hasNoLogo: false,
        
        // Step 2: Contato
        whatsapp: '',
        socialNetworks: [
          { type: '', url: '' }
        ],
        noPhysicalLocation: false,
        // Address fields
        addressCep: '',
        addressStreet: '',
        addressNumber: '',
        addressComplement: '',
        addressNeighborhood: '',
        addressCity: '',
        addressState: '',
        addressCountry: 'Brasil',
        
        // Step 3: Conteúdo
        description: '',
        services: [
          { name: '', description: '' },
          { name: '', description: '' },
          { name: '', description: '' }
        ],
        differentials: [],
        customDifferentials: '',
        
        // Step 4: Estilo
        primaryColor: '#0066CC',
        designStyle: 'modern',
        
        // Step 5: Final
        notes: ''
      },
      
      // Social Network Options
      socialNetworkOptions: [
        { value: 'instagram', label: 'Instagram', icon: '📸', placeholder: 'https://instagram.com/suaempresa' },
        { value: 'facebook', label: 'Facebook', icon: '👥', placeholder: 'https://facebook.com/suaempresa' },
        { value: 'linkedin', label: 'LinkedIn', icon: '💼', placeholder: 'https://linkedin.com/company/suaempresa' },
        { value: 'twitter', label: 'Twitter / X', icon: '🐦', placeholder: 'https://twitter.com/suaempresa' },
        { value: 'tiktok', label: 'TikTok', icon: '🎵', placeholder: 'https://tiktok.com/@suaempresa' },
        { value: 'youtube', label: 'YouTube', icon: '▶️', placeholder: 'https://youtube.com/@suaempresa' },
        { value: 'discord', label: 'Discord', icon: '🎮', placeholder: 'https://discord.gg/suaempresa' },
        { value: 'roblox', label: 'Roblox Community', icon: '🎲', placeholder: 'https://roblox.com/groups/suaempresa' },
        { value: 'twitch', label: 'Twitch', icon: '📺', placeholder: 'https://twitch.tv/suaempresa' },
        { value: 'pinterest', label: 'Pinterest', icon: '📌', placeholder: 'https://pinterest.com/suaempresa' },
        { value: 'telegram', label: 'Telegram', icon: '✈️', placeholder: 'https://t.me/suaempresa' },
        { value: 'other', label: 'Outro', icon: '🔗', placeholder: 'https://...' }
      ],
      
      // Brazil States
      brazilStates: [
        { value: 'AC', label: 'Acre' },
        { value: 'AL', label: 'Alagoas' },
        { value: 'AP', label: 'Amapá' },
        { value: 'AM', label: 'Amazonas' },
        { value: 'BA', label: 'Bahia' },
        { value: 'CE', label: 'Ceará' },
        { value: 'DF', label: 'Distrito Federal' },
        { value: 'ES', label: 'Espírito Santo' },
        { value: 'GO', label: 'Goiás' },
        { value: 'MA', label: 'Maranhão' },
        { value: 'MT', label: 'Mato Grosso' },
        { value: 'MS', label: 'Mato Grosso do Sul' },
        { value: 'MG', label: 'Minas Gerais' },
        { value: 'PA', label: 'Pará' },
        { value: 'PB', label: 'Paraíba' },
        { value: 'PR', label: 'Paraná' },
        { value: 'PE', label: 'Pernambuco' },
        { value: 'PI', label: 'Piauí' },
        { value: 'RJ', label: 'Rio de Janeiro' },
        { value: 'RN', label: 'Rio Grande do Norte' },
        { value: 'RS', label: 'Rio Grande do Sul' },
        { value: 'RO', label: 'Rondônia' },
        { value: 'RR', label: 'Roraima' },
        { value: 'SC', label: 'Santa Catarina' },
        { value: 'SP', label: 'São Paulo' },
        { value: 'SE', label: 'Sergipe' },
        { value: 'TO', label: 'Tocantins' }
      ],
      
      // Options
      differentialOptions: [
        { value: 'fast', label: 'Atendimento Rápido', icon: '⚡' },
        { value: 'price', label: 'Preço Justo', icon: '💰' },
        { value: 'quality', label: 'Qualidade Garantida', icon: '✨' },
        { value: 'experience', label: 'Anos de Experiência', icon: '🏆' },
        { value: 'support', label: 'Suporte 24/7', icon: '🛟' },
        { value: 'warranty', label: 'Garantia Estendida', icon: '🔒' }
      ],
      
      colorPresets: [
        { name: 'Azul Profissional', value: '#0066CC' },
        { name: 'Verde Saúde', value: '#28A745' },
        { name: 'Vermelho Energia', value: '#DC3545' },
        { name: 'Laranja Criativo', value: '#FF6B35' },
        { name: 'Roxo Inovação', value: '#6F42C1' },
        { name: 'Preto Elegante', value: '#212529' },
        { name: 'Rosa Moderno', value: '#E83E8C' },
        { name: 'Azul Claro', value: '#17A2B8' }
      ],
      
      styleOptions: [
        {
          value: 'minimal',
          label: 'Minimalista & Clean',
          description: 'Muito branco, fontes finas, espaçamento generoso'
        },
        {
          value: 'corporate',
          label: 'Corporativo & Sério',
          description: 'Azul marinho, estruturado, profissional'
        },
        {
          value: 'modern',
          label: 'Moderno & Vibrante',
          description: 'Cores fortes, degradês, animações suaves'
        }
      ],
      
      // Auto-save control
      autoSaveTimeout: null
    };
  },
  
  computed: {
    canProceed() {
      switch (this.currentStep) {
        case 1:
          return this.formData.companyName.trim() !== '';
        case 2:
          return this.formData.whatsapp.trim() !== '';
        case 3:
          return (
            this.formData.description.trim() !== '' &&
            this.formData.services.some(s => s.name.trim() !== '')
          );
        case 4:
          return (
            this.formData.primaryColor !== '' &&
            this.formData.designStyle !== ''
          );
        case 5:
          return true;
        default:
          return false;
      }
    }
  },
  
  mounted() {
    this.initializeWizard();
  },
  
  beforeUnmount() {
    if (this.autoSaveTimeout) {
      clearTimeout(this.autoSaveTimeout);
    }
  },
  
  methods: {
    async initializeWizard() {
      try {
        // Get token from URL
        const urlParams = new URLSearchParams(window.location.search);
        this.token = urlParams.get('token');
        
        if (!this.token) {
          throw new Error('Token de acesso não encontrado. Verifique o link no seu e-mail.');
        }
        
        // Validate token and load saved data
        const response = await fetch(`/api/onboarding/validate.php?token=${this.token}`);
        const data = await response.json();
        
        if (!response.ok || !data.success) {
          throw new Error(data.message || 'Token inválido ou expirado.');
        }
        
        // Check if already completed
        if (data.status === 'concluido') {
          this.showCompletedMessage();
          return;
        }
        
        this.orderId = data.orderId;
        
        // Load saved draft if exists
        if (data.briefing) {
          this.loadDraft(data.briefing);
        }
        
        this.loading = false;
      } catch (err) {
        this.error = err.message;
        this.loading = false;
      }
    },
    
    loadDraft(briefingData) {
      try {
        const draft = typeof briefingData === 'string' 
          ? JSON.parse(briefingData) 
          : briefingData;
        
        Object.keys(draft).forEach(key => {
          if (key in this.formData) {
            this.formData[key] = draft[key];
          }
        });
      } catch (err) {
        console.warn('Failed to load draft:', err);
      }
    },
    
    async retryLoad() {
      this.error = null;
      this.loading = true;
      await this.initializeWizard();
    },
    
    nextStep() {
      if (this.canProceed && this.currentStep < this.totalSteps) {
        this.currentStep++;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    },
    
    previousStep() {
      if (this.currentStep > 1) {
        this.currentStep--;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    },
    
    autoSave() {
      // Debounce auto-save
      if (this.autoSaveTimeout) {
        clearTimeout(this.autoSaveTimeout);
      }
      
      this.autoSaveTimeout = setTimeout(() => {
        this.saveToLocalStorage();
        this.saveDraftToServer();
      }, 1000);
    },
    
    saveToLocalStorage() {
      try {
        localStorage.setItem(
          `onboarding_draft_${this.token}`,
          JSON.stringify(this.formData)
        );
      } catch (err) {
        console.warn('Failed to save to localStorage:', err);
      }
    },
    
    async saveDraftToServer() {
      if (!this.token) return;
      
      this.autoSaving = true;
      
      try {
        const formDataToSend = new FormData();
        formDataToSend.append('token', this.token);
        formDataToSend.append('briefing', JSON.stringify(this.formData));
        
        await fetch('/api/onboarding/save-draft.php', {
          method: 'POST',
          body: formDataToSend
        });
        
        this.lastSaved = this.getRelativeTime();
        
        setTimeout(() => {
          this.autoSaving = false;
        }, 500);
      } catch (err) {
        console.warn('Failed to save draft to server:', err);
        this.autoSaving = false;
      }
    },
    
    async submitForm() {
      if (!this.canProceed || this.submitting) return;
      
      this.submitting = true;
      
      try {
        const formDataToSend = new FormData();
        formDataToSend.append('token', this.token);
        formDataToSend.append('briefing', JSON.stringify(this.formData));
        
        // Upload logo if exists
        if (this.formData.logo) {
          formDataToSend.append('logo', this.formData.logo);
        }
        
        const response = await fetch('/api/onboarding/submit.php', {
          method: 'POST',
          body: formDataToSend
        });
        
        const data = await response.json();
        
        if (!response.ok || !data.success) {
          throw new Error(data.message || 'Erro ao enviar informações.');
        }
        
        // Clear localStorage
        localStorage.removeItem(`onboarding_draft_${this.token}`);
        
        // Show success modal
        this.showSuccessModal = true;
      } catch (err) {
        alert('Erro ao enviar: ' + err.message);
        this.submitting = false;
      }
    },
    
    handleLogoUpload(event) {
      const file = event.target.files[0];
      if (!file) return;
      
      // Validate file size (5MB max)
      if (file.size > 5 * 1024 * 1024) {
        alert('O arquivo é muito grande. Tamanho máximo: 5MB');
        return;
      }
      
      // Validate file type
      if (!file.type.startsWith('image/')) {
        alert('Por favor, selecione uma imagem válida.');
        return;
      }
      
      this.formData.logo = file;
      
      // Create preview
      const reader = new FileReader();
      reader.onload = (e) => {
        this.formData.logoPreview = e.target.result;
        this.autoSave();
      };
      reader.readAsDataURL(file);
    },
    
    removeLogo() {
      this.formData.logo = null;
      this.formData.logoPreview = null;
      this.autoSave();
    },
    
    selectColor(color) {
      this.formData.primaryColor = color;
      this.autoSave();
    },
    
    selectStyle(style) {
      this.formData.designStyle = style;
      this.autoSave();
    },
    
    getStyleLabel(value) {
      const style = this.styleOptions.find(s => s.value === value);
      return style ? style.label : value;
    },
    
    showCompletedMessage() {
      this.loading = false;
      this.showSuccessModal = true;
    },
    
    closeSuccessModal() {
      window.location.href = '/';
    },
    
    getRelativeTime() {
      const now = new Date();
      const hours = now.getHours().toString().padStart(2, '0');
      const minutes = now.getMinutes().toString().padStart(2, '0');
      return `às ${hours}:${minutes}`;
    },
    
    // Social Networks Methods
    addSocialNetwork() {
      if (this.formData.socialNetworks.length < this.socialNetworkOptions.length) {
        this.formData.socialNetworks.push({ type: '', url: '' });
        this.autoSave();
      }
    },
    
    removeSocialNetwork(index) {
      this.formData.socialNetworks.splice(index, 1);
      this.autoSave();
    },
    
    availableSocialOptions(currentIndex) {
      const selectedTypes = this.formData.socialNetworks
        .map((s, i) => i !== currentIndex ? s.type : null)
        .filter(Boolean);
      
      return this.socialNetworkOptions.filter(
        option => !selectedTypes.includes(option.value) || option.value === 'other'
      );
    },
    
    getSocialPlaceholder(type) {
      const option = this.socialNetworkOptions.find(o => o.value === type);
      return option ? option.placeholder : 'https://...';
    },
    
    // Address Methods
    handleNoPhysicalLocationChange() {
      if (this.formData.noPhysicalLocation) {
        // Clear address fields when user selects no physical location
        this.formData.addressCep = '';
        this.formData.addressStreet = '';
        this.formData.addressNumber = '';
        this.formData.addressComplement = '';
        this.formData.addressNeighborhood = '';
        this.formData.addressCity = '';
        this.formData.addressState = '';
      }
      this.autoSave();
    },
    
    handleCepInput(event) {
      // Format CEP as user types (00000-000)
      let value = event.target.value.replace(/\D/g, '');
      if (value.length > 5) {
        value = value.slice(0, 5) + '-' + value.slice(5, 8);
      }
      this.formData.addressCep = value;
      this.cepError = null;
    },
    
    async fetchAddressByCep() {
      const cep = this.formData.addressCep.replace(/\D/g, '');
      
      if (cep.length !== 8) {
        if (cep.length > 0) {
          this.cepError = 'CEP deve ter 8 dígitos';
        }
        return;
      }
      
      this.loadingCep = true;
      this.cepError = null;
      
      try {
        const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const data = await response.json();
        
        if (data.erro) {
          this.cepError = 'CEP não encontrado';
          return;
        }
        
        // Fill address fields
        this.formData.addressStreet = data.logradouro || '';
        this.formData.addressNeighborhood = data.bairro || '';
        this.formData.addressCity = data.localidade || '';
        this.formData.addressState = data.uf || '';
        this.formData.addressCountry = 'Brasil';
        
        this.autoSave();
      } catch (err) {
        console.error('Error fetching CEP:', err);
        this.cepError = 'Erro ao buscar CEP. Tente novamente.';
      } finally {
        this.loadingCep = false;
      }
    }
  }
};
</script>

<style scoped lang="scss">
.onboarding-wizard {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem 1rem;
  min-height: 100vh;
}

// Progress Bar
.wizard-progress {
  margin-bottom: 3rem;
  
  .progress-bar {
    height: 8px;
    background: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
    
    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, #0066CC, #00A8FF);
      transition: width 0.3s ease;
    }
  }
  
  .progress-text {
    text-align: center;
    margin-top: 0.5rem;
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 500;
  }
}

// Loading & Error States
.loading-container,
.error-container {
  text-align: center;
  padding: 4rem 2rem;
  
  .spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #0066CC;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
  }
  
  .error-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
  }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

// Wizard Steps
.wizard-step {
  animation: fadeIn 0.3s ease;
  
  .step-header {
    text-align: center;
    margin-bottom: 3rem;
    
    h2 {
      font-size: 2rem;
      margin-bottom: 0.5rem;
      color: #212529;
    }
    
    p {
      color: #6c757d;
      font-size: 1.1rem;
    }
  }
}

// Form Elements
.form-group {
  margin-bottom: 2rem;
  
  label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #212529;
  }
  
  input[type="text"],
  input[type="tel"],
  input[type="url"],
  textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s;
    
    &:focus {
      outline: none;
      border-color: #0066CC;
      box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
    }
  }
  
  textarea {
    resize: vertical;
    font-family: inherit;
  }
  
  .field-hint {
    display: block;
    margin-top: 0.5rem;
    color: #6c757d;
    font-size: 0.9rem;
  }
}

.form-check {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.75rem;
  
  input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
  }
  
  label {
    margin: 0;
    font-weight: 400;
    cursor: pointer;
  }
}

// Social Networks
.social-networks-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 1rem;
  
  .social-network-item {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
    
    .social-network-select {
      flex: 0 0 180px;
      
      select {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        font-size: 1rem;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
        
        &:focus {
          outline: none;
          border-color: #0066CC;
          box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
        }
      }
    }
    
    .social-network-input {
      flex: 1;
      
      input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.2s;
        
        &:focus {
          outline: none;
          border-color: #0066CC;
          box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
        }
      }
    }
    
    .btn-remove-social {
      flex-shrink: 0;
      width: 40px;
      height: 40px;
      border: none;
      border-radius: 8px;
      background: #fee2e2;
      color: #dc3545;
      font-size: 1.2rem;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      
      &:hover {
        background: #fecaca;
        color: #b91c1c;
      }
    }
  }
}

.btn-add-social {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  border: 2px dashed #dee2e6;
  border-radius: 8px;
  background: transparent;
  color: #0066CC;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  
  .add-icon {
    font-size: 1.25rem;
    font-weight: bold;
  }
  
  &:hover:not(:disabled) {
    border-color: #0066CC;
    background: rgba(0, 102, 204, 0.05);
  }
  
  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
}

// Address Section
.address-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1rem;
  
  > label {
    margin-bottom: 0;
  }
  
  .switch-container {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    
    .switch-label {
      font-size: 0.95rem;
      color: #6c757d;
      font-weight: 500;
    }
  }
}

// Toggle Switch
.switch {
  position: relative;
  display: inline-block;
  width: 52px;
  height: 28px;
  
  input {
    opacity: 0;
    width: 0;
    height: 0;
    
    &:checked + .slider {
      background-color: #0066CC;
    }
    
    &:checked + .slider:before {
      transform: translateX(24px);
    }
  }
  
  .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.3s;
    border-radius: 28px;
    
    &:before {
      position: absolute;
      content: "";
      height: 22px;
      width: 22px;
      left: 3px;
      bottom: 3px;
      background-color: white;
      transition: 0.3s;
      border-radius: 50%;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
  }
}

// Address Fields
.address-fields {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 1.5rem;
  
  .address-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
    
    &:last-child {
      margin-bottom: 0;
    }
  }
  
  .form-field {
    flex: 1;
    
    label {
      display: block;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: #212529;
      font-size: 0.95rem;
    }
    
    input, select {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 2px solid #dee2e6;
      border-radius: 8px;
      font-size: 1rem;
      background: white;
      transition: all 0.2s;
      
      &:focus {
        outline: none;
        border-color: #0066CC;
        box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
      }
      
      &:disabled {
        background: #e9ecef;
        cursor: not-allowed;
      }
    }
    
    &.cep-field {
      flex: 0 0 160px;
    }
    
    &.number-field {
      flex: 0 0 120px;
    }
    
    &.state-field {
      flex: 0 0 200px;
    }
    
    &.country-field {
      flex: 0 0 180px;
    }
  }
}

.cep-input-wrapper {
  position: relative;
  
  input {
    padding-right: 2.5rem;
  }
  
  .cep-loading {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
  }
}

.spinner-small {
  display: inline-block;
  width: 18px;
  height: 18px;
  border: 2px solid #e9ecef;
  border-top-color: #0066CC;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.field-error {
  display: block;
  margin-top: 0.5rem;
  color: #dc3545;
  font-size: 0.875rem;
}

// Logo Upload
.logo-upload {
  .logo-preview {
    position: relative;
    display: inline-block;
    
    img {
      max-width: 200px;
      max-height: 200px;
      border: 2px solid #dee2e6;
      border-radius: 8px;
    }
    
    .btn-remove {
      position: absolute;
      top: -10px;
      right: -10px;
      width: 30px;
      height: 30px;
      border-radius: 50%;
      background: #dc3545;
      color: white;
      border: none;
      cursor: pointer;
      font-size: 1.2rem;
      line-height: 1;
      
      &:hover {
        background: #c82333;
      }
    }
  }
  
  .upload-area {
    .upload-label {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      border: 2px dashed #dee2e6;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.2s;
      
      &:hover {
        border-color: #0066CC;
        background: rgba(0, 102, 204, 0.05);
      }
      
      .upload-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
      }
      
      span {
        font-weight: 600;
        color: #0066CC;
      }
      
      small {
        display: block;
        margin-top: 0.5rem;
        color: #6c757d;
      }
    }
  }
}

// Services
.service-item {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  
  .service-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #0066CC;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
  }
  
  .service-fields {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }
}

// Differentials
.differentials-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
  
  .differential-option {
    input[type="checkbox"] {
      display: none;
      
      &:checked + label {
        background: #0066CC;
        color: white;
        border-color: #0066CC;
      }
    }
    
    label {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.75rem 1rem;
      border: 2px solid #dee2e6;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.2s;
      font-weight: 500;
      
      &:hover {
        border-color: #0066CC;
      }
      
      .check-icon {
        font-size: 1.2rem;
      }
    }
  }
}

// Color Picker
.color-picker-container {
  .color-presets {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    
    .color-preset {
      width: 50px;
      height: 50px;
      border-radius: 8px;
      cursor: pointer;
      border: 3px solid transparent;
      transition: all 0.2s;
      
      &:hover {
        transform: scale(1.1);
      }
      
      &.active {
        border-color: #212529;
        box-shadow: 0 0 0 2px white, 0 0 0 4px #212529;
      }
    }
  }
  
  .custom-color {
    display: flex;
    align-items: center;
    gap: 1rem;
    
    input[type="color"] {
      width: 60px;
      height: 40px;
      border: 2px solid #dee2e6;
      border-radius: 8px;
      cursor: pointer;
    }
    
    .color-value {
      font-family: monospace;
      font-weight: 600;
      color: #6c757d;
    }
  }
}

// Style Options
.style-options {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  
  .style-option {
    border: 2px solid #dee2e6;
    border-radius: 12px;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s;
    
    &:hover {
      border-color: #0066CC;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    &.active {
      border-color: #0066CC;
      background: rgba(0, 102, 204, 0.05);
      box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
    }
    
    .style-preview {
      height: 120px;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 1rem;
      
      &.preview-minimal {
        background: white;
        border: 1px solid #e9ecef;
        
        .preview-header {
          height: 20px;
          background: #f8f9fa;
          border-radius: 4px;
          margin-bottom: 1rem;
        }
        
        .preview-line {
          height: 8px;
          background: #e9ecef;
          border-radius: 4px;
          margin-bottom: 0.5rem;
          
          &.short {
            width: 60%;
          }
        }
      }
      
      &.preview-corporate {
        background: linear-gradient(135deg, #1e3a8a, #2563eb);
        
        .preview-header {
          height: 20px;
          background: rgba(255, 255, 255, 0.2);
          border-radius: 4px;
          margin-bottom: 1rem;
        }
        
        .preview-line {
          height: 8px;
          background: rgba(255, 255, 255, 0.3);
          border-radius: 4px;
          margin-bottom: 0.5rem;
          
          &.short {
            width: 60%;
          }
        }
      }
      
      &.preview-modern {
        background: linear-gradient(135deg, #ec4899, #8b5cf6);
        
        .preview-header {
          height: 20px;
          background: rgba(255, 255, 255, 0.2);
          border-radius: 20px;
          margin-bottom: 1rem;
        }
        
        .preview-line {
          height: 8px;
          background: rgba(255, 255, 255, 0.3);
          border-radius: 20px;
          margin-bottom: 0.5rem;
          
          &.short {
            width: 60%;
          }
        }
      }
    }
    
    .style-info {
      h4 {
        margin-bottom: 0.5rem;
        color: #212529;
      }
      
      p {
        font-size: 0.9rem;
        color: #6c757d;
        line-height: 1.4;
      }
    }
  }
}

// Summary
.summary-section {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 2rem;
  margin-bottom: 2rem;
  
  h3 {
    margin-bottom: 1.5rem;
    color: #212529;
  }
  
  .summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    
    .summary-item {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
      
      strong {
        color: #6c757d;
        font-size: 0.9rem;
      }
      
      span {
        color: #212529;
        font-weight: 500;
      }
    }
  }
}

// Final CTA
.final-cta {
  text-align: center;
  padding: 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px;
  color: white;
  margin-bottom: 2rem;
  
  .cta-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
  }
  
  h3 {
    margin-bottom: 1rem;
  }
  
  p {
    opacity: 0.95;
    line-height: 1.6;
  }
}

// Navigation
.wizard-navigation {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 1px solid #dee2e6;
  
  button {
    padding: 0.875rem 2rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    
    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
    
    &.btn-secondary {
      background: #6c757d;
      color: white;
      
      &:hover:not(:disabled) {
        background: #5a6268;
      }
    }
    
    &.btn-primary {
      background: #0066CC;
      color: white;
      margin-left: auto;
      
      &:hover:not(:disabled) {
        background: #0052a3;
      }
    }
    
    &.btn-success {
      background: linear-gradient(135deg, #28a745, #20c997);
      color: white;
      margin-left: auto;
      padding: 1rem 2.5rem;
      font-size: 1.1rem;
      
      &:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
      }
    }
  }
}

// Auto-save Indicator
.autosave-indicator {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  padding: 0.75rem 1.25rem;
  background: #212529;
  color: white;
  border-radius: 8px;
  font-size: 0.9rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  animation: slideIn 0.3s ease;
  
  &.saved {
    background: #28a745;
  }
}

// Success Modal
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1rem;
  
  .modal-content {
    background: white;
    border-radius: 16px;
    padding: 3rem 2rem;
    max-width: 500px;
    text-align: center;
    animation: modalPop 0.3s ease;
    
    .success-icon {
      font-size: 4rem;
      margin-bottom: 1.5rem;
    }
    
    h2 {
      margin-bottom: 1rem;
      color: #212529;
    }
    
    p {
      color: #6c757d;
      line-height: 1.6;
      margin-bottom: 1rem;
    }
    
    .success-details {
      background: #f8f9fa;
      padding: 1rem;
      border-radius: 8px;
      margin: 1.5rem 0;
      
      strong {
        display: block;
        margin-top: 0.5rem;
        color: #0066CC;
      }
    }
    
    button {
      margin-top: 1.5rem;
    }
  }
}

// Animations
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
  }
  to {
    transform: translateX(0);
  }
}

@keyframes modalPop {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

// Transitions
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

// Responsive
@media (max-width: 768px) {
  .onboarding-wizard {
    padding: 1rem 0.5rem;
  }
  
  .wizard-step .step-header h2 {
    font-size: 1.5rem;
  }
  
  .style-options {
    grid-template-columns: 1fr;
  }
  
  .differentials-grid {
    grid-template-columns: 1fr;
  }
  
  .wizard-navigation {
    flex-direction: column;
    
    button {
      width: 100%;
      
      &.btn-primary,
      &.btn-success {
        margin-left: 0;
      }
    }
  }
  
  .autosave-indicator {
    bottom: 1rem;
    right: 1rem;
    left: 1rem;
    text-align: center;
  }
  
  // Social Networks Responsive
  .social-networks-list .social-network-item {
    flex-direction: column;
    
    .social-network-select {
      flex: 1;
      width: 100%;
    }
    
    .social-network-input {
      width: 100%;
    }
    
    .btn-remove-social {
      align-self: flex-end;
      margin-top: -0.5rem;
    }
  }
  
  // Address Responsive
  .address-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .address-fields {
    padding: 1rem;
    
    .address-row {
      flex-direction: column;
      gap: 1rem;
    }
    
    .form-field {
      &.cep-field,
      &.number-field,
      &.state-field,
      &.country-field {
        flex: 1;
      }
    }
  }
}
</style>
