<template>
  <div class="site-configurator">
    <!-- Debug Mode Indicator -->
    <div v-if="$store.state.ConfigModule?.debug" class="debug-badge">
      <i class="fas fa-tools"></i>
      <span>DEBUG MODE</span>
      <button @click="$store.dispatch('ConfigModule/disableDebug')" class="debug-toggle">
        Desativar
      </button>
    </div>
    
    <!-- Progress Bar -->
    <div class="progress-bar">
      <div 
        v-for="(step, index) in visibleSteps" 
        :key="index"
        :class="['progress-step', { 
          active: currentStep === index + stepOffset,
          completed: currentStep > index + stepOffset 
        }]"
      >
        <div class="step-number">{{ index + 1 }}</div>
        <div class="step-label">{{ step }}</div>
      </div>
    </div>

    <!-- Configurator Body -->
    <div class="configurator-body">
      <!-- Etapa 1: Monte Seu Site (Personalização) -->
      <!-- Nota: Etapa de escolha de produto existe mas fica oculta quando initialProduct está definido -->
      <transition name="fade" mode="out-in">
        <!-- ETAPA 1: Escolha de Produto (OCULTA quando initialProduct existe) -->
        <div v-if="currentStep === 1 && !initialProduct" class="step-content step-product">
          <h2 class="step-title">Escolha Seu Produto</h2>
          <p class="step-description">
            Selecione a opção ideal para seu negócio. 
            Você poderá adicionar itens no próximo passo.
          </p>

          <div v-if="config.products" class="product-cards">
            <div
              v-for="(product, key) in config.products"
              :key="key"
              :class="['product-card', { selected: selectedProduct === key }]"
              @click="selectedProduct = key"
            >
              <div class="promo-badge">30% OFF</div>
              <div class="product-header">
                <div class="product-icon">
                  <i :class="key === 'landing' ? 'fas fa-file-alt' : 'fas fa-layer-group'"></i>
                </div>
                <h3 class="product-name">{{ product.name }}</h3>
                <div class="product-price">
                  <span class="price-original">De {{ formatOriginalPrice(product.base_price / 12) }}</span>
                  <div class="price-current">
                    <span class="price-label">A partir de</span>
                    <span class="price-value">{{ formatMonthlyPrice(product.base_price) }}</span>
                  </div>
                </div>
              </div>
              <p class="product-description">{{ product.description }}</p>
              <div class="product-action">
                <button class="btn-select">
                  <i class="fas fa-check"></i>
                  Selecionar
                </button>
              </div>
            </div>
          </div>

          <!-- Navegação Etapa 1 -->
          <div class="step-navigation">
            <button class="btn-back" disabled style="visibility: hidden;">
              <i class="fas fa-arrow-left"></i>
              Voltar
            </button>
            <button 
              class="btn-next"
              :disabled="!selectedProduct"
              @click="nextStep"
            >
              Próximo
              <i class="fas fa-arrow-right"></i>
            </button>
          </div>
        </div>

        <!-- ETAPA 2: Personalização (ou Etapa 1 visível quando initialProduct existe) -->
        <div v-else-if="currentStep === (initialProduct ? 1 : 2)" class="step-content step-addons">
          <h2 class="step-title">Monte Seu Site</h2>
          <p class="step-description">
            Adicione páginas e conteúdo conforme sua necessidade.
          </p>

          <!-- Páginas Adicionais (somente Site Completo) -->
          <div v-if="selectedProduct === 'site_complete' && config.page_addons" class="addon-section">
            <h3 class="section-title">
              <i class="fas fa-file-alt"></i>
              Páginas Pré-Definidas
            </h3>
            <div class="addon-checkboxes">
              <label
                v-for="(page, key) in config.page_addons"
                :key="key"
                class="checkbox-item"
              >
                <input
                  type="checkbox"
                  :value="key"
                  v-model="selectedPages"
                >
                <div class="checkbox-content">
                  <div class="promo-tag">-30%</div>
                  <div class="checkbox-info">
                    <span class="checkbox-name">
                      {{ page.name }}
                      <span class="addon-tooltip">
                        <i class="fas fa-info-circle"></i>
                        <span class="tooltip-text">{{ getPageTooltip(key) }}</span>
                      </span>
                    </span>
                    <div class="checkbox-price">
                      <span class="price-from">De {{ formatOriginalPrice(page.price / 12) }}</span>
                      <span class="price-to">+{{ formatMonthlyPrice(page.price) }}</span>
                    </div>
                  </div>
                  <div class="checkbox-mark">
                    <i class="fas fa-check"></i>
                  </div>
                </div>
              </label>
            </div>
          </div>

          <!-- Páginas Personalizadas -->
          <div v-if="selectedProduct === 'site_complete'" class="addon-section">
            <h3 class="section-title">
              <i class="fas fa-pen-fancy"></i>
              Páginas Personalizadas
            </h3>
            <p class="section-subtitle">
              Crie páginas exclusivas para suas necessidades específicas
            </p>

            <div class="custom-page-counter">
              <span class="counter-label">Quantas páginas personalizadas você precisa?</span>
              <div class="counter-controls">
                <button 
                  class="counter-btn"
                  :disabled="customPages.length === 0"
                  @click="removeCustomPage"
                >
                  <i class="fas fa-minus"></i>
                </button>
                <span class="counter-value">{{ customPages.length }}</span>
                <button 
                  class="counter-btn"
                  @click="addCustomPage"
                >
                  <i class="fas fa-plus"></i>
                </button>
              </div>
              <span class="counter-price">+R$ 8,25/mês por página</span>
            </div>

            <!-- Box para cada página personalizada -->
            <div
              v-for="(page, index) in customPages"
              :key="index"
              class="custom-page-box"
            >
              <div class="custom-page-header">
                <h4>Página Personalizada #{{ index + 1 }}</h4>
                <button class="remove-page-btn" @click="removeCustomPageByIndex(index)">
                  <i class="fas fa-times"></i>
                </button>
              </div>

              <div class="custom-page-description">
                <label>Descreva o que você quer nesta página:</label>
                <textarea
                  v-model="page.description"
                  maxlength="500"
                  placeholder="Ex: Página de produtos com galeria de fotos, descrições e botões de compra..."
                  rows="4"
                ></textarea>
                <span class="char-count">{{ page.description.length }}/500 caracteres</span>
              </div>

              <div class="custom-page-resources">
                <h5>Recursos para esta página:</h5>
                <div class="resources-grid">
                  <label class="resource-item">
                    <input type="checkbox" v-model="page.resources.image">
                    <div class="resource-content">
                      <i class="fas fa-image"></i>
                      <span class="resource-name">Imagens</span>
                      <span class="resource-price">+R$ 0,83/mês</span>
                    </div>
                  </label>

                  <label class="resource-item">
                    <input type="checkbox" v-model="page.resources.video">
                    <div class="resource-content">
                      <i class="fas fa-video"></i>
                      <span class="resource-name">Vídeo</span>
                      <span class="resource-price">+R$ 1,00/mês</span>
                    </div>
                  </label>

                  <label class="resource-item">
                    <input type="checkbox" v-model="page.resources.carousel">
                    <div class="resource-content">
                      <i class="fas fa-images"></i>
                      <span class="resource-name">Carrossel</span>
                      <span class="resource-price">+R$ 1,67/mês</span>
                    </div>
                  </label>

                  <label class="resource-item">
                    <input type="checkbox" v-model="page.resources.form">
                    <div class="resource-content">
                      <i class="fas fa-envelope"></i>
                      <span class="resource-name">Formulário</span>
                      <span class="resource-price">+R$ 2,50/mês</span>
                    </div>
                  </label>
                </div>
              </div>

              <div class="custom-page-total">
                <span>Total desta página:</span>
                <span class="total-value">{{ formatMonthlyPrice(calculateCustomPageTotal(page)) }}</span>
              </div>
            </div>
          </div>

          <!-- Conteúdo Pesado -->
          <div v-if="config.content_addons" class="addon-section">
            <h3 class="section-title">
              <i class="fas fa-photo-video"></i>
              Conteúdo Especial
            </h3>
            <div class="addon-toggles">
              <label
                v-for="(content, key) in config.content_addons"
                :key="key"
                class="toggle-item"
              >
                <input
                  type="checkbox"
                  :value="key"
                  v-model="selectedContentAddons"
                >
                <div class="toggle-content">
                  <div class="toggle-info">
                    <span class="toggle-name">
                      {{ content.name }}
                      <span class="addon-tooltip">
                        <i class="fas fa-info-circle"></i>
                        <span class="tooltip-text">{{ getContentTooltip(key) }}</span>
                      </span>
                      <span class="promo-inline-badge">-30%</span>
                    </span>
                    <div class="toggle-price">
                      <span class="price-from">De {{ formatOriginalPrice(content.price / 12) }}</span>
                      <span class="price-to">+{{ formatMonthlyPrice(content.price) }}</span>
                    </div>
                  </div>
                  <div class="toggle-switch">
                    <span class="switch"></span>
                  </div>
                </div>
              </label>
            </div>
          </div>

          <!-- Limites do Plano -->
          <div class="plan-info">
            <div class="info-icon">
              <i class="fas fa-info-circle"></i>
            </div>
            <div class="info-content">
              <h4>Incluído no Plano:</h4>
              <ul>
                <li><i class="fas fa-check"></i> Suporte de disponibilidade contínua</li>
                <li><i class="fas fa-check"></i> Acesso exclusivo ao nosso sistema para ajustes</li>
                <li><i class="fas fa-check"></i> Domínio grátis (.com.br)</li>
                <li><i class="fas fa-check"></i> Certificado SSL gratuito (HTTPS)</li>
                <li><i class="fas fa-check"></i> SEO básico otimizado sem custo</li>
                <li><i class="fas fa-check"></i> Entrega em até 10 dias úteis</li>
                <li><i class="fas fa-check"></i> Hospedagem anual incluída</li>
                <li><i class="fas fa-check"></i> Design responsivo para celular e desktop</li>
                <li><i class="fas fa-check"></i> Botão de WhatsApp integrado</li>
                <li><i class="fas fa-check"></i> Otimização de performance</li>
              </ul>
            </div>
          </div>

          <!-- Pergunta Final -->
          <!-- Upsell Box para Landing Page -->
          <div v-if="selectedProduct === 'landing'" class="upsell-box">
            <div class="upsell-header">
              <i class="fas fa-arrow-up"></i>
              <h3>Quer o site mais personalizado por apenas mais R$20 no ano?</h3>
            </div>
            <p class="upsell-description">
              Adicione páginas extras como Sobre, Serviços, Portfólio e muito mais!
              Transforme seu site simples em uma presença completa na web.
            </p>
            <div class="upsell-actions">
              <button class="btn-upsell-accept" @click="upgradeToComplete">
                <i class="fas fa-star"></i>
                Sim, quero o site completo
              </button>
              <button class="btn-upsell-decline" @click="proceedToCheckout">
                Não, quero um site mais simples
              </button>
            </div>
          </div>

          <!-- Pergunta de Customização para Site Completo -->
          <div v-else class="custom-question">
            <h3>Precisa de algo mais personalizado?</h3>
            <p>E-commerce, integrações avançadas, área de login, etc.</p>
            <div class="question-actions">
              <button class="btn-standard" @click="proceedToCheckout">
                Preciso apenas de um site para minha empresa
              </button>
              <button class="btn-custom" @click="openCustomForm">
                Na verdade, preciso de algo mais completo
              </button>
            </div>
            <div class="back-to-landing">
              <a @click="selectedProduct = 'landing'">
                ← Acho que prefiro a landing page simples
              </a>
            </div>
          </div>
        </div>

        <!-- Etapa 2: Dados e Arquivos (Checkout) -->
        <!-- Etapa 3 quando não há initialProduct, Etapa 2 quando há -->
        <div v-else-if="currentStep === (initialProduct ? 2 : 3)" class="step-content step-briefing">
          <h2 class="step-title">Dados e Arquivos</h2>
          <p class="step-description">
            Preencha as informações para criarmos seu site.
          </p>

          <form class="briefing-form" @submit.prevent="submitOrder">
            <!-- Dados Básicos -->
            <div class="form-section">
              <h3 class="form-section-title">Informações da Empresa</h3>
              
              <div class="form-row">
                <div class="form-group">
                  <label>Nome da Empresa *</label>
                  <input 
                    type="text" 
                    v-model="briefing.company_name"
                    placeholder="Ex: UNLI Studio"
                    required
                  >
                </div>

                <div class="form-group">
                  <label>WhatsApp *</label>
                  <input 
                    type="tel" 
                    v-model="briefing.whatsapp"
                    placeholder="(00) 00000-0000"
                    required
                  >
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>E-mail *</label>
                  <input 
                    type="email" 
                    v-model="briefing.email"
                    placeholder="contato@empresa.com"
                    required
                  >
                </div>

                <div class="form-group">
                  <label>Endereço</label>
                  <input 
                    type="text" 
                    v-model="briefing.address"
                    placeholder="Rua, Número, Cidade - UF"
                  >
                </div>
              </div>
            </div>

            <!-- Links -->
            <div class="form-section">
              <h3 class="form-section-title">Links e Redes Sociais</h3>
              
              <div class="form-row">
                <div class="form-group">
                  <label>Instagram</label>
                  <input 
                    type="url" 
                    v-model="briefing.instagram"
                    placeholder="https://instagram.com/sua_empresa"
                  >
                </div>

                <div class="form-group">
                  <label>Google Maps</label>
                  <input 
                    type="url" 
                    v-model="briefing.google_maps"
                    placeholder="Link do Google Maps"
                  >
                </div>
              </div>
            </div>

            <!-- Estilo -->
            <div class="form-section">
              <h3 class="form-section-title">Estilo Visual</h3>
              <div class="style-options">
                <label
                  v-for="style in styles"
                  :key="style.value"
                  :class="['style-card', { selected: briefing.style === style.value }]"
                >
                  <input 
                    type="radio" 
                    name="style"
                    :value="style.value"
                    v-model="briefing.style"
                  >
                  <div class="style-content">
                    <i :class="style.icon"></i>
                    <span>{{ style.label }}</span>
                  </div>
                </label>
              </div>
            </div>

            <!-- Conteúdo -->
            <div class="form-section">
              <h3 class="form-section-title">Conteúdo do Site</h3>
              
              <div class="form-group">
                <label>Texto da Página Principal *</label>
                <textarea 
                  v-model="briefing.main_content"
                  rows="6"
                  placeholder="Descreva sua empresa, serviços, diferenciais..."
                  required
                ></textarea>
                <small>Inclua: apresentação, serviços, diferenciais, CTA</small>
              </div>

              <!-- Textos de Páginas Adicionais -->
              <div 
                v-for="pageKey in validSelectedPages"
                :key="pageKey"
                class="form-group"
              >
                <label>Texto: {{ config.page_addons[pageKey].name }}</label>
                <textarea 
                  v-model="briefing.page_contents[pageKey]"
                  rows="4"
                  :placeholder="`Descreva o conteúdo para a página ${config.page_addons[pageKey].name}...`"
                ></textarea>
              </div>
            </div>

            <!-- Uploads -->
            <div class="form-section">
              <h3 class="form-section-title">Arquivos</h3>
              
              <!-- Vídeo (se selecionado) -->
              <div v-if="selectedContentAddons.includes('video')" class="form-group">
                <label>Upload de Vídeo</label>
                <input 
                  type="file" 
                  accept="video/*"
                  @change="handleVideoUpload"
                  class="file-input"
                >
                <small>Formato aceito: MP4, MOV, AVI (máx. 50MB)</small>
              </div>

              <!-- PDF (se selecionado) -->
              <div v-if="selectedContentAddons.includes('pdf')" class="form-group">
                <label>Upload de PDF</label>
                <input 
                  type="file" 
                  accept="application/pdf"
                  @change="handlePdfUpload"
                  class="file-input"
                >
                <small>Formato aceito: PDF (máx. 10MB)</small>
              </div>

              <!-- Imagens (links) -->
              <div class="form-group">
                <label>Links de Imagens</label>
                <textarea 
                  v-model="briefing.image_links"
                  rows="3"
                  placeholder="Cole os links das imagens (um por linha)"
                ></textarea>
                <small>Pode ser de Google Drive, Dropbox, etc.</small>
              </div>
            </div>

            <!-- Resumo do Pedido -->
            <div class="order-summary">
              <div class="summary-header">
                <h3>
                  <i class="fas fa-shield-check"></i>
                  Plano Anual PRO
                </h3>
              </div>
              
              <!-- Serviços Recorrentes Inclusos -->
              <div class="included-services">
                <div class="services-header">
                  <i class="fas fa-check-double"></i>
                  <h4>Serviços Contínuos Inclusos</h4>
                </div>
                <div class="service-item">
                  <i class="fas fa-check-circle"></i>
                  <span class="service-label">Hospedagem Premium</span>
                  <span class="service-tag">12 meses</span>
                </div>
                <div class="service-item">
                  <i class="fas fa-check-circle"></i>
                  <span class="service-label">Domínio .com.br ou .com</span>
                  <span class="service-tag">Incluso</span>
                </div>
                <div class="service-item">
                  <i class="fas fa-check-circle"></i>
                  <span class="service-label">Certificado SSL (HTTPS)</span>
                  <span class="service-tag">Incluso</span>
                </div>
                <div class="service-item">
                  <i class="fas fa-check-circle"></i>
                  <span class="service-label">Monitoramento 24/7</span>
                  <span class="service-tag">Incluso</span>
                </div>
                <div class="service-item">
                  <i class="fas fa-check-circle"></i>
                  <span class="service-label">Suporte Técnico</span>
                  <span class="service-tag">12 meses</span>
                </div>
                <div class="service-item">
                  <i class="fas fa-check-circle"></i>
                  <span class="service-label">Criação e Design do Site</span>
                  <span class="service-tag">Setup</span>
                </div>
              </div>
              
              <!-- Valor sem desconto -->
              <div class="original-price-banner">
                <span class="label">Sem a promoção você pagaria:</span>
                <span class="value">{{ formatOriginalTotalPrice(subtotal) }}</span>
              </div>
              
              <div class="summary-items">
                <div class="summary-item">
                  <span>{{ currentProductName }}</span>
                  <div class="item-prices">
                    <span class="price-original-item">{{ formatOriginalPrice(basePrice / 12) }}</span>
                    <span class="price-current-item">{{ formatMonthlyPrice(basePrice) }}</span>
                  </div>
                </div>
                <div 
                  v-for="pageKey in validSelectedPages"
                  :key="pageKey"
                  class="summary-item"
                >
                  <span>{{ config.page_addons[pageKey].name }}</span>
                  <div class="item-prices">
                    <span class="price-original-item">{{ formatOriginalPrice(config.page_addons[pageKey].price / 12) }}</span>
                    <span class="price-current-item">{{ formatMonthlyPrice(config.page_addons[pageKey].price) }}</span>
                  </div>
                </div>
                <div 
                  v-for="(page, index) in customPages"
                  :key="'custom-' + index"
                  class="summary-item"
                >
                  <span>Página Personalizada #{{ index + 1 }}</span>
                  <div class="item-prices">
                    <span class="price-original-item">{{ formatOriginalPrice(calculateCustomPageTotal(page) / 12) }}</span>
                    <span class="price-current-item">{{ formatMonthlyPrice(calculateCustomPageTotal(page)) }}</span>
                  </div>
                </div>
                <div 
                  v-for="addonKey in validSelectedContentAddons"
                  :key="addonKey"
                  class="summary-item"
                >
                  <span>{{ config.content_addons[addonKey].name }}</span>
                  <div class="item-prices">
                    <span class="price-original-item">{{ formatOriginalPrice(config.content_addons[addonKey].price / 12) }}</span>
                    <span class="price-current-item">{{ formatMonthlyPrice(config.content_addons[addonKey].price) }}</span>
                  </div>
                </div>
              </div>
              <div class="summary-total">
                <!-- Ancoragem de Preço: Quebrar para Mensal/Diário -->
                <div class="price-anchoring">
                  <div class="anchoring-label">💡 Seu departamento de TI por:</div>
                  <div class="anchoring-values">
                    <div class="anchoring-item">
                      <div class="value">{{ formatPrice((subtotal * 1.15) / 12) }}</div>
                      <div class="label">por mês</div>
                    </div>
                    <div class="divider"></div>
                    <div class="anchoring-item">
                      <div class="value">{{ formatPrice((subtotal * 1.15) / 365) }}</div>
                      <div class="label">por dia</div>
                    </div>
                  </div>
                  <div class="anchoring-subtext">
                    Menos que um café: sua empresa online 24/7
                  </div>
                </div>
                
                <h4 class="payment-title">Escolha a forma de pagamento:</h4>
                
                <!-- Opção 1: À Vista (PRÉ-SELECIONADA) -->
                <label 
                  class="total-row cash featured"
                  :class="{ selected: paymentMethod === 'cash' }"
                >
                  <input 
                    type="radio" 
                    value="cash" 
                    v-model="paymentMethod"
                  >
                  <div class="payment-content">
                    <div class="payment-header">
                      <div class="payment-main-info">
                        <span class="payment-label">💳 À vista (Pix/Boleto)</span>
                        <span class="badge-recommended">✨ Mais Escolhido</span>
                      </div>
                      <div class="payment-prices">
                        <span class="price-original-total">De {{ formatOriginalTotalAnnual(subtotal) }}</span>
                        <span class="price-cash">{{ formatPrice(cashPrice) }}<small>/ano</small></span>
                      </div>
                    </div>
                    <div class="payment-benefits">
                      <span class="economy-tag-cash">🔥 Economize {{ formatPrice(savingsAmount) }} agora!</span>
                      <span class="payment-note">✅ Plano ativo imediatamente após pagamento</span>
                    </div>
                  </div>
                  <div class="radio-check">
                    <i class="fas fa-check-circle"></i>
                  </div>
                </label>
                
                <!-- Opção 2: Parcelado -->
                <label 
                  class="total-row installments"
                  :class="{ selected: paymentMethod === 'installments' }"
                >
                  <input 
                    type="radio" 
                    value="installments" 
                    v-model="paymentMethod"
                  >
                  <div class="payment-content">
                    <div class="payment-header">
                      <div class="payment-main-info">
                        <span class="payment-label">💳 Parcelado no Cartão</span>
                      </div>
                      <div class="payment-prices">
                        <span class="price-installment">12x de {{ formatPrice(installmentValue) }}</span>
                        <span class="price-total-installment">(Total anual: {{ formatPrice(installmentTotal) }})</span>
                      </div>
                    </div>
                    <div class="payment-benefits">
                      <span class="economy-tag"><strong class="no-interest">Sem juros</strong> • Plano de 12 meses</span>
                      <span class="economy-tag">💰 Economize 30% vs. preço normal</span>
                    </div>
                  </div>
                  <div class="radio-check">
                    <i class="fas fa-check-circle"></i>
                  </div>
                </label>
              </div>
            </div>

            <div class="step-navigation">
              <button type="button" class="btn-back" @click="currentStep = 2">
                <i class="fas fa-edit"></i>
                Quero mudar alguma coisa
              </button>
              <button 
                type="submit" 
                class="btn-submit" 
                :class="{ 'btn-cash': paymentMethod === 'cash' }"
                :disabled="isSubmitting"
              >
                <i class="fas fa-lock"></i>
                {{ isSubmitting ? 'Processando...' : ctaText }}
              </button>
            </div>
            
            <!-- Nota sobre renovação (transparência) -->
            <div class="renewal-notice">
              <i class="fas fa-info-circle"></i>
              <div class="notice-content">
                <strong>Plano de 12 meses:</strong> 
                Todos os serviços (hospedagem, domínio, suporte e monitoramento) estão inclusos por 1 ano. 
                Você será notificado 30 dias antes do vencimento para renovar ou cancelar.
              </div>
            </div>
          </form>
        </div>
      </transition>
    </div>

    <!-- Price Sidebar (sempre visível) -->
    <div class="price-sidebar">
      <div class="sidebar-header">
        <div class="sidebar-promo-badge">30% OFF</div>
        <h3>Seu Pedido</h3>
      </div>
      
      <div class="sidebar-items">
        <div v-if="selectedProduct" class="sidebar-item">
          <span class="item-name">{{ currentProductName }}</span>
          <div class="item-price">
            <span class="price-original-small">{{ formatOriginalPrice(basePrice / 12) }}</span>
            <span class="price-current-small">{{ formatMonthlyPrice(basePrice) }}</span>
          </div>
        </div>
        
        <!-- Páginas pré-definidas -->
        <div 
          v-for="pageKey in validSelectedPages"
          :key="pageKey"
          class="sidebar-item"
        >
          <span class="item-name">{{ config.page_addons[pageKey].name }}</span>
          <div class="item-price">
            <span class="price-original-small">{{ formatOriginalPrice(config.page_addons[pageKey].price / 12) }}</span>
            <span class="price-current-small">{{ formatMonthlyPrice(config.page_addons[pageKey].price) }}</span>
          </div>
        </div>

        <!-- Páginas personalizadas -->
        <div 
          v-for="(page, index) in customPages"
          :key="'custom-' + index"
          class="sidebar-item"
        >
          <span class="item-name">Página Personalizada #{{ index + 1 }}</span>
          <div class="item-price">
            <span class="price-original-small">{{ formatOriginalPrice(calculateCustomPageTotal(page) / 12) }}</span>
            <span class="price-current-small">{{ formatMonthlyPrice(calculateCustomPageTotal(page)) }}</span>
          </div>
        </div>

        <div 
          v-for="addonKey in validSelectedContentAddons"
          :key="addonKey"
          class="sidebar-item"
        >
          <span class="item-name">{{ config.content_addons[addonKey].name }}</span>
          <div class="item-price">
            <span class="price-original-small">{{ formatOriginalPrice(config.content_addons[addonKey].price / 12) }}</span>
            <span class="price-current-small">{{ formatMonthlyPrice(config.content_addons[addonKey].price) }}</span>
          </div>
        </div>
      </div>

      <!-- Total mensal (sempre visível) -->
      <div class="sidebar-total">
        <div class="monthly-highlight">
          <span class="monthly-label">Total</span>
          <div class="total-prices">
            <span class="total-original">De {{ formatOriginalPrice(subtotal / 12) }}</span>
            <span class="total-current">{{ formatMonthlyPrice(subtotal) }}</span>
          </div>
          <span class="monthly-hint">Pagamento anual 🎉</span>
        </div>
      </div>

      <!-- Indicador de validação -->
      <div class="sidebar-validation" v-if="selectedProduct">
        <div v-if="isValidating" class="validation-status validating">
          <i class="fas fa-sync fa-spin"></i>
          <span>Validando...</span>
        </div>
        <div v-else-if="priceSource === 'server'" class="validation-status validated">
          <i class="fas fa-check-circle"></i>
          <span>Preços validados</span>
        </div>
        <div v-else class="validation-status local">
          <i class="fas fa-calculator"></i>
          <span>Cálculo local</span>
        </div>
      </div>

      <div class="sidebar-footer">
        <i class="fas fa-shield-alt"></i>
        <span>Pagamento 100% seguro</span>
      </div>
    </div>

    <!-- Modal: Orçamento Personalizado -->
    <transition name="modal">
      <div v-if="showCustomModal" class="modal-overlay" @click.self="closeCustomForm">
        <div class="modal-content">
          <button class="modal-close" @click="closeCustomForm">
            <i class="fas fa-times"></i>
          </button>
          
          <h2 class="modal-title">Orçamento Personalizado</h2>
          <p class="modal-description">
            Preencha suas informações e descreva o que você precisa. 
            Entraremos em contato em até 24h.
          </p>

          <form @submit.prevent="submitCustomRequest" class="custom-form">
            <div class="form-group">
              <label>Nome *</label>
              <input 
                type="text" 
                v-model="customRequest.name"
                placeholder="Seu nome"
                required
              >
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>WhatsApp *</label>
                <input 
                  type="tel" 
                  v-model="customRequest.whatsapp"
                  placeholder="(00) 00000-0000"
                  required
                >
              </div>

              <div class="form-group">
                <label>E-mail *</label>
                <input 
                  type="email" 
                  v-model="customRequest.email"
                  placeholder="seu@email.com"
                  required
                >
              </div>
            </div>

            <div class="form-group">
              <label>O que você precisa? *</label>
              <textarea 
                v-model="customRequest.description"
                rows="5"
                placeholder="Descreva o projeto: e-commerce, área de login, integrações, etc."
                required
              ></textarea>
            </div>

            <div class="form-group">
              <label>Prazo / Urgência</label>
              <select v-model="customRequest.urgency">
                <option value="">Selecione...</option>
                <option value="normal">Normal (até 30 dias)</option>
                <option value="urgent">Urgente (até 15 dias)</option>
                <option value="very_urgent">Muito Urgente (até 7 dias)</option>
              </select>
            </div>

            <div class="modal-actions">
              <button type="button" class="btn-cancel" @click="closeCustomForm">
                Cancelar
              </button>
              <button type="submit" class="btn-submit-modal" :disabled="isSubmittingCustom">
                <i class="fas fa-paper-plane"></i>
                {{ isSubmittingCustom ? 'Enviando...' : 'Enviar Solicitação' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
// Configuração carregada de JSON
import configData from '@/pages/SiteVitrine/config/site-configurator.json';
import PricingService from '@/core/services/PricingService';

export default {
  name: 'SiteConfigurator',
  props: {
    initialProduct: {
      type: String,
      default: null,
      validator: (value) => !value || ['landing', 'site_complete'].includes(value)
    }
  },
  data() {
    return {
      config: configData,
      currentStep: 1,
      steps: ['Personalize', 'Checkout'],
      
      // Seleções
      selectedProduct: null,
      selectedPages: [], // Array de strings: ['about', 'services', ...]
      selectedContentAddons: [], // ['video', 'pdf']
      customPages: [], // Array de objetos: [{ description: '', resources: { image: false, video: false, carousel: false, form: false } }]
      
      // Briefing
      briefing: {
        company_name: '',
        whatsapp: '',
        email: '',
        address: '',
        instagram: '',
        google_maps: '',
        style: '',
        main_content: '',
        page_contents: {},
        image_links: '',
        video_file: null,
        pdf_file: null
      },
      
      // Estilos disponíveis
      styles: [
        { value: 'modern', label: 'Moderno', icon: 'fas fa-rocket' },
        { value: 'elegant', label: 'Elegante', icon: 'fas fa-gem' },
        { value: 'tech', label: 'Tech/Cyber', icon: 'fas fa-microchip' }
      ],
      
      // Modal personalizado
      showCustomModal: false,
      customRequest: {
        name: '',
        whatsapp: '',
        email: '',
        description: '',
        urgency: ''
      },
      
      // Estados
      isSubmitting: false,
      isSubmittingCustom: false,
      
      // Pagamento
      paymentMethod: 'cash', // 'cash' ou 'installments'
      
      // Validação server-side
      serverValidatedPricing: null,
      serverAvailable: null, // null = não testado, true = online, false = offline
      isValidating: false
    };
  },
  async mounted() {
    console.log('🔍 [SiteConfigurator] Mounted - initialProduct prop:', this.initialProduct);
    console.log('🔍 [SiteConfigurator] Type of initialProduct:', typeof this.initialProduct);
    
    // Verificar se há produto pré-selecionado
    if (this.initialProduct) {
      console.log('✅ [SiteConfigurator] initialProduct existe:', this.initialProduct);
      this.selectedProduct = this.initialProduct;
      // Produto já selecionado, continuar no step 1 (personalização)
      if (this.config.products[this.initialProduct]) {
        console.log(`✅ Produto pré-selecionado: ${this.initialProduct}`);
      }
    } else {
      // Se não houver produto selecionado, redirecionar para página de vendas
      console.warn('⚠️ Nenhum produto selecionado. Redirecionando...');
      this.$router.push('/site-vitrine#planos');
      return; // Importante: parar execução aqui
    }
    
    // Verificar modo debug
    const debugMode = this.$store.state.ConfigModule?.debug;
    
    if (debugMode) {
      console.log('%c🔧 DEBUG MODE ATIVO', 'background: #ff9800; color: white; padding: 8px 16px; font-size: 14px; font-weight: bold; border-radius: 4px;');
      console.log('%c- API PHP desabilitada', 'color: #ff9800; font-weight: bold;');
      console.log('%c- Todos os cálculos são locais', 'color: #ff9800; font-weight: bold;');
      console.log('%c- Pedidos simulados (mock)', 'color: #ff9800; font-weight: bold;');
      console.log('%cPara desabilitar: $store.dispatch("ConfigModule/disableDebug")', 'color: #999; font-style: italic;');
    } else {
      console.log('%c✅ MODO PRODUÇÃO', 'background: #4caf50; color: white; padding: 8px 16px; font-size: 14px; font-weight: bold; border-radius: 4px;');
      console.log('%c- API PHP habilitada', 'color: #4caf50; font-weight: bold;');
      console.log('%c- Validações server-side ativas', 'color: #4caf50; font-weight: bold;');
    }
    
    // Testar disponibilidade da API
    this.serverAvailable = await PricingService.checkServerAvailability();
    
    if (this.serverAvailable) {
      console.info('✅ API Server disponível');
    } else {
      console.warn('🟡 API Server offline - usando cálculos locais');
    }
  },
  watch: {
    // Validar no servidor sempre que seleções mudarem
    selectedProduct() {
      this.validatePriceOnServer();
    },
    selectedPages: {
      deep: true,
      handler() {
        this.validatePriceOnServer();
      }
    },
    selectedContentAddons() {
      this.validatePriceOnServer();
    },
    customPages: {
      deep: true,
      handler() {
        this.validatePriceOnServer();
      }
    }
  },
  computed: {
    // Steps dinâmicos: mostra 2 quando initialProduct existe, 3 quando não
    visibleSteps() {
      return this.initialProduct 
        ? ['Personalize', 'Checkout']  // Pula a etapa de escolha de produto
        : ['Produto', 'Personalize', 'Checkout'];  // Mostra todas as etapas
    },
    
    // Offset para cálculo correto de steps (1 quando tem initialProduct, 0 quando não)
    stepOffset() {
      return this.initialProduct ? 1 : 1; // Sempre começa em 1
    },
    
    // Filtered arrays para evitar v-if + v-for
    validSelectedPages() {
      console.log('🔍 [validSelectedPages] Computing...');
      console.log('  - config.page_addons exists:', !!this.config.page_addons);
      console.log('  - selectedPages:', this.selectedPages);
      
      if (!this.config.page_addons) {
        console.log('  ⚠️ config.page_addons is missing!');
        return [];
      }
      
      const filtered = this.selectedPages.filter(key => {
        const exists = !!this.config.page_addons[key];
        console.log(`  - Checking key "${key}": ${exists ? '✅ exists' : '❌ NOT FOUND'}`);
        return exists;
      });
      
      console.log('  ✅ Result:', filtered);
      return filtered;
    },
    
    validSelectedContentAddons() {
      console.log('🔍 [validSelectedContentAddons] Computing...');
      console.log('  - config.content_addons exists:', !!this.config.content_addons);
      console.log('  - selectedContentAddons:', this.selectedContentAddons);
      
      if (!this.config.content_addons) {
        console.log('  ⚠️ config.content_addons is missing!');
        return [];
      }
      
      const filtered = this.selectedContentAddons.filter(key => {
        const exists = !!this.config.content_addons[key];
        console.log(`  - Checking key "${key}": ${exists ? '✅ exists' : '❌ NOT FOUND'}`);
        return exists;
      });
      
      console.log('  ✅ Result:', filtered);
      return filtered;
    },
    
    currentProductName() {
      return this.selectedProduct ? this.config.products[this.selectedProduct].name : '';
    },
    
    basePrice() {
      return this.selectedProduct ? this.config.products[this.selectedProduct].base_price : 0;
    },
    
    pagesTotal() {
      // Páginas pré-definidas (checkboxes) - usa validSelectedPages para garantir que existem
      const predefinedTotal = this.validSelectedPages.reduce((sum, key) => {
        return sum + (this.config.page_addons[key]?.price || 0);
      }, 0);

      // Páginas personalizadas
      const customTotal = this.customPages.reduce((sum, page) => {
        return sum + this.calculateCustomPageTotal(page);
      }, 0);

      return predefinedTotal + customTotal;
    },
    
    contentTotal() {
      return this.validSelectedContentAddons.reduce((sum, key) => {
        return sum + (this.config.content_addons[key]?.price || 0);
      }, 0);
    },
    
    subtotal() {
      // Se servidor validou, usar valor do servidor; senão, calcular localmente
      if (this.serverValidatedPricing && this.serverValidatedPricing.source === 'server') {
        return this.serverValidatedPricing.subtotal;
      }
      return this.basePrice + this.pagesTotal + this.contentTotal;
    },
    
    cashPrice() {
      if (this.serverValidatedPricing && this.serverValidatedPricing.source === 'server') {
        return this.serverValidatedPricing.avista;
      }
      // À vista = preço normal (sem desconto)
      return this.subtotal;
    },
    
    installmentTotal() {
      if (this.serverValidatedPricing && this.serverValidatedPricing.source === 'server') {
        return this.serverValidatedPricing.parcelado_total;
      }
      // 15% acréscimo no parcelado (taxa do gateway)
      const markup = 0.15; // 15%
      return Math.round(this.subtotal * (1 + markup) * 100) / 100;
    },
    
    installmentValue() {
      if (this.serverValidatedPricing && this.serverValidatedPricing.source === 'server') {
        return this.serverValidatedPricing.parcela_12;
      }
      // Valor de cada parcela (cálculo local)
      return Math.round((this.installmentTotal / this.config.pricing_rules.installments) * 100) / 100;
    },
    
    priceSource() {
      return this.serverValidatedPricing?.source || 'local';
    },
    
    // Economia em R$ (diferença entre valor sem desconto e à vista)
    savingsAmount() {
      // Valor original inflacionado 30% + taxa 15%
      const originalTotal = this.subtotal * 1.15 * 1.3;
      // Diferença para à vista
      return originalTotal - this.cashPrice;
    },
    
    // Texto do CTA baseado na seleção
    ctaText() {
      if (this.paymentMethod === 'cash') {
        return `🎯 Assinar Plano Anual - ${this.formatPrice(this.cashPrice)}`;
      }
      return '🎯 Assinar Plano Anual (12x sem juros)';
    }
  },
  methods: {
    // Navegação
    nextStep() {
      console.log('🔄 [nextStep] Current step:', this.currentStep);
      if (this.currentStep < 3) {
        this.currentStep++;
        console.log('✅ [nextStep] Moved to step:', this.currentStep);
      } else {
        console.log('⚠️ [nextStep] Already at final step');
      }
    },
    
    previousStep() {
      console.log('🔄 [previousStep] Current step:', this.currentStep);
      if (this.currentStep > 1) {
        this.currentStep--;
        console.log('✅ [previousStep] Moved to step:', this.currentStep);
      } else {
        console.log('⚠️ [previousStep] Already at first step');
      }
    },
    
    proceedToCheckout() {
      console.log('🔍 [PRE-CHECKOUT DEBUG] Iniciando checkout...');
      console.log('📦 Config exists:', !!this.config);
      console.log('📦 Config.page_addons exists:', !!this.config?.page_addons);
      console.log('📦 Config.content_addons exists:', !!this.config?.content_addons);
      console.log('📦 Config.page_addons keys:', this.config?.page_addons ? Object.keys(this.config.page_addons) : 'N/A');
      console.log('📦 Config.content_addons keys:', this.config?.content_addons ? Object.keys(this.config.content_addons) : 'N/A');
      
      console.log('🎯 Selected Product:', this.selectedProduct);
      console.log('🎯 Selected Pages (RAW):', this.selectedPages);
      console.log('🎯 Selected Content Addons (RAW):', this.selectedContentAddons);
      console.log('🎯 Custom Pages:', this.customPages);
      
      console.log('✅ Valid Selected Pages:', this.validSelectedPages);
      console.log('✅ Valid Selected Content Addons:', this.validSelectedContentAddons);
      
      console.log('💰 Base Price:', this.basePrice);
      console.log('💰 Pages Total:', this.pagesTotal);
      console.log('💰 Content Total:', this.contentTotal);
      console.log('💰 Subtotal:', this.subtotal);
      
      // Se initialProduct existe, vai do step 1 para 2
      // Se não existe, vai do step 2 para 3
      this.currentStep = this.initialProduct ? 2 : 3;
      console.log(`✅ [PRE-CHECKOUT DEBUG] Movido para step ${this.currentStep} (checkout)`);
    },

    // Upgrade de Landing para Site Completo
    upgradeToComplete() {
      this.selectedProduct = 'site_complete';
      // Manter na etapa 2 para adicionar páginas
    },

    // Abrir formulário de contato para projetos customizados
    openCustomForm() {
      // Redirecionar para seção de contato
      window.location.hash = 'contact';
      this.$emit('close');
    },
    
    // Páginas personalizadas
    addCustomPage() {
      this.customPages.push({
        description: '',
        resources: {
          image: false,
          video: false,
          carousel: false,
          form: false
        }
      });
    },

    removeCustomPage() {
      if (this.customPages.length > 0) {
        this.customPages.pop();
      }
    },

    removeCustomPageByIndex(index) {
      this.customPages.splice(index, 1);
    },

    calculateCustomPageTotal(page) {
      let total = 99; // Base price
      if (page.resources.image) total += 10;
      if (page.resources.video) total += 12;
      if (page.resources.carousel) total += 20;
      if (page.resources.form) total += 30;
      return total;
    },
    
    // Upload handlers
    handleVideoUpload(event) {
      const file = event.target.files[0];
      if (file && file.size <= 50 * 1024 * 1024) { // 50MB
        this.briefing.video_file = file;
      } else {
        alert('Arquivo muito grande. Máximo 50MB.');
        event.target.value = '';
      }
    },
    
    handlePdfUpload(event) {
      const file = event.target.files[0];
      if (file && file.size <= 10 * 1024 * 1024) { // 10MB
        this.briefing.pdf_file = file;
      } else {
        alert('Arquivo muito grande. Máximo 10MB.');
        event.target.value = '';
      }
    },
    
    // Validar preço no servidor (background)
    async validatePriceOnServer() {
      if (!this.selectedProduct) return;
      
      this.isValidating = true;
      
      // Debounce (evitar muitas chamadas)
      clearTimeout(this._validateTimeout);
      this._validateTimeout = setTimeout(async () => {
        const selection = {
          product: this.selectedProduct,
          pages: this.selectedPages,
          content: this.selectedContentAddons
        };
        
        const result = await PricingService.calculatePrice(selection, this.config);
        
        if (result.serverValidated) {
          this.serverValidatedPricing = result.pricing;
          this.serverAvailable = true;
          
          // Se servidor normalizou seleções, aplicar
          if (result.normalized && result.normalized !== selection) {
            console.info('🔄 Servidor normalizou seleções');
            this.selectedProduct = result.normalized.product;
            this.selectedPages = result.normalized.pages;
          }
        } else {
          this.serverValidatedPricing = result.pricing; // Usar valor local
          this.serverAvailable = false;
        }
        
        this.isValidating = false;
      }, 500); // 500ms debounce
    },
    
    // Submit final
    async submitOrder() {
      this.isSubmitting = true;
      
      try {
        // Montar dados do pedido (sem incluir preço - servidor calcula)
        const orderData = {
          product: this.selectedProduct,
          pages: this.selectedPages,
          content: this.selectedContentAddons,
          briefing: this.briefing,
          payment_method: 'avista' // TODO: adicionar seletor de forma de pagamento
        };
        
        // Tentar criar pedido no servidor
        const result = await PricingService.createOrder(orderData);
        
        if (result.ok) {
          // Sucesso: pedido criado no servidor
          console.log('✅ Pedido criado:', result.order_id);
          
          // Emitir evento com dados oficiais do servidor
          this.$emit('order-submitted', {
            ...result,
            local_pricing: {
              subtotal: this.subtotal,
              cash_price: this.cashPrice,
              installment_total: this.installmentTotal
            }
          });
          
          alert(`✅ Pedido criado com sucesso!\n\nID: ${result.order_id}\n\nEm produção, você seria redirecionado para o gateway de pagamento.`);
        } else if (result.offline) {
          // API offline: modo desenvolvimento
          console.warn('🟡 API offline - pedido simulado:', result.mockData);
          
          const mockPayload = {
            ...result.mockData,
            product: this.selectedProduct,
            pages: this.selectedPages,
            content_addons: this.selectedContentAddons,
            briefing: this.briefing,
            pricing: {
              subtotal: this.subtotal,
              cash_price: this.cashPrice,
              installment_total: this.installmentTotal,
              installment_value: this.installmentValue,
              source: 'local'
            },
            timestamp: new Date().toISOString()
          };
          
          this.$emit('order-submitted', mockPayload);
          
          alert('⚠️ Modo Desenvolvimento\n\nAPI offline - pedido simulado localmente.\n\nEm produção, o servidor validaria todos os valores.');
        } else {
          // Erro no servidor
          throw new Error(result.error || 'Erro desconhecido');
        }
      } catch (error) {
        console.error('🔴 Erro ao enviar pedido:', error);
        alert('❌ Erro ao enviar pedido. Tente novamente ou entre em contato.');
      } finally {
        this.isSubmitting = false;
      }
    },
    
    async submitCustomRequest() {
      this.isSubmittingCustom = true;
      
      try {
        const result = await PricingService.submitCustomRequest({
          ...this.customRequest,
          current_config: {
            product: this.selectedProduct,
            pages: this.selectedPages,
            content: this.selectedContentAddons
          }
        });
        
        if (result.ok) {
          this.$emit('custom-request', this.customRequest);
          alert(result.message);
          this.closeCustomForm();
        } else if (result.offline) {
          alert('⚠️ API offline\n\nEntre em contato via WhatsApp: (11) 99999-9999');
        }
      } catch (error) {
        console.error('🔴 Erro:', error);
        alert('Erro ao enviar solicitação.');
      } finally {
        this.isSubmittingCustom = false;
      }
    },
    
    // Utils
    formatPrice(value) {
      return `R$ ${value.toFixed(2).replace('.', ',')}`;
    },
    
    formatOriginalPrice(value) {
      // value já vem como valor mensal
      // Aplicar taxa de parcelamento 15% primeiro, depois inflacionar 30%
      const withInstallmentFee = value * 1.15; // Taxa do gateway
      const inflated = withInstallmentFee * 1.3; // Inflação de 30% para "De"
      return `R$ ${inflated.toFixed(2).replace('.', ',')}`;
    },
    
    formatOriginalTotalPrice(annualValue) {
      // Calcula o valor total mensal SEM nenhum desconto (com taxa de 15% e inflacionado 30%)
      const withMarkup = annualValue * 1.15;
      const monthly = withMarkup / 12;
      const inflated = monthly * 1.3;
      return `R$ ${inflated.toFixed(2).replace('.', ',')}/mês`;
    },
    
    formatOriginalTotalAnnual(annualValue) {
      // Calcula o valor total ANUAL original (com taxa de 15% e inflacionado 30%)
      const withMarkup = annualValue * 1.15;
      const inflated = withMarkup * 1.3;
      return `R$ ${inflated.toFixed(2).replace('.', ',')}`;
    },

    formatMonthlyPrice(annualValue) {
      // Adiciona 15% (taxa do cartão) e divide por 12
      // Assim o cliente vê o preço mensal parcelado desde o início
      const withMarkup = annualValue * 1.15;
      const monthly = withMarkup / 12;
      return `R$ ${monthly.toFixed(2).replace('.', ',')}/mês`;
    },
    
    // Tooltips informativos
    getPageTooltip(key) {
      const tooltips = {
        about: 'Página institucional sobre sua empresa, história, missão e valores. Uma única página completa.',
        services: 'Página apresentando todos os seus serviços/produtos. Uma única página com cards ou seções.',
        portfolio: 'Galeria de trabalhos realizados, projetos ou produtos. Uma única página com grid de imagens.',
        faq: 'Página de perguntas frequentes com accordion/expansível. Uma única página.',
        contact: 'Página de contato com formulário, mapa e informações. Uma única página.'
      };
      return tooltips[key] || 'Página adicional para seu site';
    },
    
    getContentTooltip(key) {
      const tooltips = {
        video: 'Incorporação de vídeo institucional no site (YouTube, Vimeo, ou upload direto)',
        pdf: 'Catálogo, menu, folder ou documento PDF disponível para download no site'
      };
      return tooltips[key] || 'Conteúdo adicional';
    }
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/__colors.scss';

// Seção introdutória (renderizada no pai)
.configurator-intro {
  text-align: center;
  margin-bottom: 40px;

  .section-badge {
    display: inline-block;
    padding: 8px 16px;
    background: rgba($p-color, 0.1);
    color: $p-color;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 16px;
  }

  h2 {
    font-size: 2.5rem;
    font-weight: 700;
    color: $gray-darkness;
    margin-bottom: 16px;
    line-height: 1.2;

    @media (max-width: 768px) {
      font-size: 2rem;
    }
  }

  p {
    font-size: 1.1rem;
    color: $gray-medium;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.6;

    strong {
      color: $p-color;
      font-weight: 600;
    }
  }
}

// Container principal
.site-configurator {
  position: relative;
  display: grid;
  grid-template-columns: 1fr 350px;
  gap: 40px;
  max-width: 1400px;
  margin: 0 auto;
  padding: 40px 20px;

  @media (max-width: 1024px) {
    grid-template-columns: 1fr;
  }
}

// Debug Mode Badge
.debug-badge {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 10000;
  background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%);
  color: white;
  padding: 12px 20px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 8px 24px rgba(255, 152, 0, 0.4);
  animation: pulse 2s ease-in-out infinite;
  font-weight: 600;
  font-size: 0.9rem;

  i {
    font-size: 1.1rem;
    animation: spin 3s linear infinite;
  }

  .debug-toggle {
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;

    &:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: scale(1.05);
    }
  }

  @keyframes pulse {
    0%, 100% {
      box-shadow: 0 8px 24px rgba(255, 152, 0, 0.4);
    }
    50% {
      box-shadow: 0 8px 32px rgba(255, 152, 0, 0.6);
    }
  }

  @keyframes spin {
    from {
      transform: rotate(0deg);
    }
    to {
      transform: rotate(360deg);
    }
  }

  @media (max-width: 768px) {
    top: 10px;
    right: 10px;
    padding: 8px 12px;
    font-size: 0.8rem;

    span {
      display: none;
    }
  }
}

// Progress Bar
.progress-bar {
  grid-column: 1 / -1;
  display: flex;
  justify-content: center;
  gap: 40px;
  margin-bottom: 40px;

  .progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    opacity: 0.4;
    transition: opacity 0.3s;

    &.active,
    &.completed {
      opacity: 1;
    }

    .step-number {
      width: 48px;
      height: 48px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      background: $gray-light;
      color: $gray-medium;
      font-weight: 700;
      font-size: 1.2rem;
      transition: all 0.3s;
    }

    &.active .step-number {
      background: $p-color;
      color: $white;
      box-shadow: 0 4px 14px rgba($p-color, 0.4);
    }

    &.completed .step-number {
      background: $success;
      color: $white;

      &::after {
        content: '✓';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
      }
    }

    .step-label {
      font-size: 0.9rem;
      font-weight: 600;
      color: $gray-medium;
    }
  }
}

// Body do configurador
.configurator-body {
  min-height: 600px;
}

.step-content {
  animation: fadeIn 0.3s;
}

.step-title {
  font-size: 2rem;
  font-weight: 800;
  color: $gray-darkness;
  margin-bottom: 12px;
}

.step-description {
  font-size: 1.1rem;
  color: $gray-medium;
  margin-bottom: 40px;
}

// Etapa 1: Escolha de Produto (oculta quando initialProduct existe)
.step-product {
  .product-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
    margin-bottom: 40px;
  }

  .product-card {
    padding: 32px;
    background: $white;
    border: 3px solid $gray-light;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
    
    .promo-badge {
      position: absolute;
      top: 12px;
      right: 12px;
      background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
      color: white;
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 0.75rem;
      font-weight: 700;
      box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3);
    }

    &:hover {
      border-color: $p-color;
      transform: translateY(-4px);
      box-shadow: 0 12px 32px rgba($p-color, 0.15);
    }

    &.selected {
      border-color: $p-color;
      background: linear-gradient(135deg, rgba($p-color, 0.05) 0%, rgba($p-dark, 0.02) 100%);
      box-shadow: 0 0 0 4px rgba($p-color, 0.1);
    }

    .product-header {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 12px;
      margin-bottom: 20px;

      .product-icon {
        width: 64px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: $gradient-primary;
        border-radius: 16px;

        i {
          font-size: 2rem;
          color: $white;
        }
      }

      .product-name {
        font-size: 1.5rem;
        font-weight: 700;
        color: $gray-darkness;
      }

      .product-price {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        
        .price-original {
          text-decoration: line-through;
          color: $gray-medium;
          font-size: 0.9rem;
          opacity: 0.6;
        }
        
        .price-current {
          display: flex;
          flex-direction: column;
          align-items: center;

          .price-value {
            font-size: 1.5rem;
            font-weight: 900;
            color: $p-color;
          }

          .price-label {
            font-size: 0.85rem;
            color: $gray-medium;
          }
        }
      }
    }

    .product-description {
      text-align: center;
      color: $gray-medium;
      line-height: 1.6;
      margin-bottom: 24px;
    }

    .product-action {
      .btn-select {
        width: 100%;
        padding: 12px;
        background: $p-color;
        color: $white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.3s;

        &:hover {
          background: $p-dark;
          transform: translateY(-2px);
        }
      }
    }
  }
}

// Etapa 1/2: Personalização (Add-ons)
.addon-section {
  margin-bottom: 40px;

  .section-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1.3rem;
    font-weight: 700;
    color: $gray-darkness;
    margin-bottom: 20px;

    i {
      color: $p-color;
    }
  }

  .section-subtitle {
    color: $gray-medium;
    margin-bottom: 24px;
    font-size: 1rem;
  }
}

// Checkboxes para páginas pré-definidas
.addon-checkboxes {
  display: grid;
  gap: 12px;
}

.checkbox-item {
  cursor: pointer;

  input[type="checkbox"] {
    display: none;
  }

  .checkbox-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: $white;
    border: 2px solid $gray-light;
    border-radius: 12px;
    transition: all 0.3s;
    position: relative;
    
    .promo-tag {
      position: absolute;
      top: -8px;
      right: 16px;
      background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
      color: white;
      padding: 2px 8px;
      border-radius: 8px;
      font-size: 0.65rem;
      font-weight: 700;
      box-shadow: 0 2px 6px rgba(255, 107, 107, 0.3);
    }

    &:hover {
      border-color: $p-color;
      box-shadow: 0 4px 12px rgba($p-color, 0.1);
    }
  }

  input[type="checkbox"]:checked + .checkbox-content {
    border-color: $p-color;
    background: linear-gradient(135deg, rgba($p-color, 0.05) 0%, rgba($p-dark, 0.02) 100%);

    .checkbox-mark {
      background: $p-color;
      border-color: $p-color;

      i {
        opacity: 1;
        transform: scale(1);
      }
    }
  }

  .checkbox-info {
    display: flex;
    flex-direction: column;
    gap: 4px;

    .checkbox-name {
      font-weight: 600;
      color: $gray-darkness;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .checkbox-price {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 2px;
      
      .price-from {
        text-decoration: line-through;
        color: $gray-medium;
        font-size: 0.75rem;
        opacity: 0.6;
      }
      
      .price-to {
        font-size: 0.9rem;
        color: $p-color;
        font-weight: 700;
      }
    }
  }

  .checkbox-mark {
    width: 28px;
    height: 28px;
    border: 2px solid $gray-light;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;

    i {
      color: $white;
      font-size: 0.9rem;
      opacity: 0;
      transform: scale(0.5);
      transition: all 0.3s;
    }
  }
}

// Tooltip styling (compartilhado)
.addon-tooltip {
  position: relative;
  display: inline-flex;
  align-items: center;
  cursor: help;

  i {
    color: $gray-medium;
    font-size: 0.9rem;
    transition: color 0.2s;
  }

  &:hover i {
    color: $p-color;
  }

  .tooltip-text {
    visibility: hidden;
    opacity: 0;
    position: absolute;
    bottom: 125%;
    left: 50%;
    transform: translateX(-50%);
    background: $gray-darkness;
    color: $white;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 400;
    line-height: 1.5;
    width: 280px;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: opacity 0.3s, visibility 0.3s;
    text-align: left;
    pointer-events: none;

    &::after {
      content: '';
      position: absolute;
      top: 100%;
      left: 50%;
      transform: translateX(-50%);
      border: 6px solid transparent;
      border-top-color: $gray-darkness;
    }
  }

  &:hover .tooltip-text {
    visibility: visible;
    opacity: 1;
  }
}

// Páginas personalizadas
.custom-page-counter {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px;
  background: linear-gradient(135deg, rgba($p-color, 0.05) 0%, rgba($p-dark, 0.02) 100%);
  border: 2px solid rgba($p-color, 0.2);
  border-radius: 12px;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;

  .counter-label {
    font-weight: 600;
    color: $gray-darkness;
    flex: 1;
    min-width: 200px;
  }

  .counter-controls {
    display: flex;
    align-items: center;
    gap: 16px;

    .counter-btn {
      width: 36px;
      height: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: $white;
      border: 2px solid $p-color;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s;

      i {
        color: $p-color;
      }

      &:hover:not(:disabled) {
        background: $p-color;

        i {
          color: $white;
        }
      }

      &:disabled {
        opacity: 0.4;
        cursor: not-allowed;
      }
    }

    .counter-value {
      min-width: 32px;
      text-align: center;
      font-size: 1.2rem;
      font-weight: 700;
      color: $p-color;
    }
  }

  .counter-price {
    font-size: 0.9rem;
    color: $p-color;
    font-weight: 600;
  }
}

.custom-page-box {
  background: $white;
  border: 2px solid $gray-light;
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 20px;
  transition: all 0.3s;

  &:hover {
    border-color: $p-color;
    box-shadow: 0 4px 16px rgba($p-color, 0.1);
  }

  .custom-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid $gray-lightness;

    h4 {
      font-size: 1.2rem;
      font-weight: 700;
      color: $gray-darkness;
      margin: 0;
    }

    .remove-page-btn {
      width: 32px;
      height: 32px;
      border: none;
      background: $gray-lightness;
      color: $gray-medium;
      border-radius: 8px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s;

      &:hover {
        background: #dc3545;
        color: $white;
      }
    }
  }

  .custom-page-description {
    margin-bottom: 24px;

    label {
      display: block;
      font-weight: 600;
      color: $gray-darkness;
      margin-bottom: 8px;
    }

    textarea {
      width: 100%;
      padding: 12px;
      border: 2px solid $gray-light;
      border-radius: 8px;
      font-size: 0.95rem;
      color: $gray-darkness;
      resize: vertical;
      font-family: inherit;
      transition: all 0.3s;

      &:focus {
        outline: none;
        border-color: $p-color;
        box-shadow: 0 0 0 3px rgba($p-color, 0.1);
      }

      &::placeholder {
        color: $gray-medium;
      }
    }

    .char-count {
      display: block;
      text-align: right;
      font-size: 0.85rem;
      color: $gray-medium;
      margin-top: 4px;
    }
  }

  .custom-page-resources {
    margin-bottom: 24px;

    h5 {
      font-size: 1rem;
      font-weight: 600;
      color: $gray-darkness;
      margin-bottom: 16px;
    }

    .resources-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 12px;
    }

    .resource-item {
      cursor: pointer;

      input[type="checkbox"] {
        display: none;
      }

      .resource-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 16px;
        background: $gray-lightness;
        border: 2px solid $gray-light;
        border-radius: 12px;
        transition: all 0.3s;
        gap: 8px;

        i {
          font-size: 1.5rem;
          color: $gray-medium;
          transition: all 0.3s;
        }

        .resource-name {
          font-weight: 600;
          color: $gray-darkness;
          font-size: 0.9rem;
        }

        .resource-price {
          font-size: 0.85rem;
          color: $p-color;
          font-weight: 600;
        }

        &:hover {
          border-color: $p-color;
          background: rgba($p-color, 0.05);
        }
      }

      input[type="checkbox"]:checked + .resource-content {
        border-color: $p-color;
        background: linear-gradient(135deg, rgba($p-color, 0.1) 0%, rgba($p-dark, 0.05) 100%);

        i {
          color: $p-color;
        }
      }
    }
  }

  .custom-page-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: linear-gradient(135deg, rgba($p-color, 0.05) 0%, rgba($p-dark, 0.02) 100%);
    border-radius: 12px;

    span:first-child {
      font-weight: 600;
      color: $gray-darkness;
    }

    .total-value {
      font-size: 1.3rem;
      font-weight: 700;
      color: $p-color;
    }
  }
}

.addon-grid {
  display: grid;
  gap: 16px;
}

.addon-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  background: $white;
  border: 2px solid $gray-light;
  border-radius: 12px;
  position: relative;
  
  .addon-promo-badge {
    position: absolute;
    top: -8px;
    left: 16px;
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
    padding: 3px 8px;
    border-radius: 8px;
    font-size: 0.65rem;
    font-weight: 700;
    box-shadow: 0 2px 6px rgba(255, 107, 107, 0.3);
  }

  .addon-info {
    display: flex;
    flex-direction: column;
    gap: 4px;

    .addon-name {
      font-weight: 600;
      color: $gray-darkness;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .addon-price {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 2px;
      
      .addon-price-from {
        text-decoration: line-through;
        color: $gray-medium;
        font-size: 0.75rem;
        opacity: 0.6;
      }
      
      .addon-price-to {
        font-size: 0.9rem;
        color: $p-color;
        font-weight: 700;
      }
    }
  }

  // Tooltip styling
  .addon-tooltip {
    position: relative;
    display: inline-flex;
    align-items: center;
    cursor: help;

    i {
      color: $gray-medium;
      font-size: 0.9rem;
      transition: color 0.2s;
    }

    &:hover i {
      color: $p-color;
    }

    .tooltip-text {
      visibility: hidden;
      opacity: 0;
      position: absolute;
      bottom: 125%;
      left: 50%;
      transform: translateX(-50%);
      background: $gray-darkness;
      color: $white;
      padding: 12px 16px;
      border-radius: 8px;
      font-size: 0.85rem;
      font-weight: 400;
      line-height: 1.5;
      width: 280px;
      z-index: 1000;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      transition: opacity 0.3s, visibility 0.3s;
      text-align: left;
      pointer-events: none;

      &::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 6px solid transparent;
        border-top-color: $gray-darkness;
      }
    }

    &:hover .tooltip-text {
      visibility: visible;
      opacity: 1;
    }
  }

  .addon-counter {
    display: flex;
    align-items: center;
    gap: 16px;

    .counter-btn {
      width: 36px;
      height: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: $gray-lightness;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s;

      i {
        color: $p-color;
      }

      &:hover:not(:disabled) {
        background: $p-color;

        i {
          color: $white;
        }
      }

      &:disabled {
        opacity: 0.3;
        cursor: not-allowed;
      }
    }

    .counter-value {
      min-width: 32px;
      text-align: center;
      font-size: 1.2rem;
      font-weight: 700;
      color: $gray-darkness;
    }
  }
}

.addon-toggles {
  display: flex;
  flex-direction: column;
  gap: 12px;

  .toggle-item {
    display: block;
    padding: 20px;
    background: $white;
    border: 2px solid $gray-light;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;

    &:has(input:checked) {
      border-color: $p-color;
      background: rgba($p-color, 0.05);
    }

    input[type="checkbox"] {
      display: none;
    }

    .toggle-content {
      display: flex;
      justify-content: space-between;
      align-items: center;

      .toggle-info {
        display: flex;
        flex-direction: column;
        gap: 4px;

        .toggle-name {
          font-weight: 600;
          color: $gray-darkness;
          display: flex;
          align-items: center;
          gap: 8px;
          
          .promo-inline-badge {
            display: inline-flex;
            padding: 2px 6px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            border-radius: 6px;
            font-size: 0.65rem;
            font-weight: 700;
          }
        }

        .toggle-price {
          font-size: 0.9rem;
          color: $p-color;
          font-weight: 700;
          display: flex;
          flex-direction: column;
          align-items: flex-start;
          gap: 2px;
          
          .price-from {
            text-decoration: line-through;
            color: $gray-medium;
            font-size: 0.7rem;
            opacity: 0.6;
          }
        }
      }

      .toggle-switch {
        width: 52px;
        height: 28px;
        background: $gray-light;
        border-radius: 14px;
        position: relative;
        transition: background 0.3s;

        .switch {
          position: absolute;
          width: 24px;
          height: 24px;
          background: $white;
          border-radius: 50%;
          top: 2px;
          left: 2px;
          transition: left 0.3s;
          box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
      }
    }

    input:checked ~ .toggle-content .toggle-switch {
      background: $p-color;

      .switch {
        left: 26px;
      }
    }
  }
}

.plan-info {
  display: flex;
  gap: 16px;
  padding: 20px;
  background: rgba($accent-blue, 0.05);
  border-left: 4px solid $accent-blue;
  border-radius: 12px;
  margin-bottom: 32px;

  .info-icon {
    font-size: 1.5rem;
    color: $accent-blue;
  }

  .info-content {
    h4 {
      font-size: 1rem;
      font-weight: 700;
      color: $gray-darkness;
      margin-bottom: 12px;
    }

    ul {
      list-style: none;
      padding: 0;
      margin: 0;

      li {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 0;
        color: $gray-medium;
        font-size: 0.95rem;

        i {
          color: $success;
        }
      }
    }
  }
}

// Upsell Box
.upsell-box {
  padding: 40px;
  background: linear-gradient(135deg, rgba($p-color, 0.1) 0%, rgba($accent-blue, 0.05) 100%);
  border: 2px solid rgba($p-color, 0.3);
  border-radius: 16px;
  text-align: center;
  margin-bottom: 40px;
  position: relative;
  overflow: hidden;

  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: $gradient-primary;
  }

  .upsell-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;

    i {
      font-size: 2.5rem;
      color: $p-color;
      animation: bounce 2s infinite;
    }

    h3 {
      font-size: 1.75rem;
      font-weight: 800;
      color: $gray-darkness;
      line-height: 1.3;
    }
  }

  .upsell-description {
    font-size: 1.1rem;
    color: $gray-medium;
    line-height: 1.7;
    margin-bottom: 32px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
  }

  .upsell-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;

    button {
      padding: 16px 36px;
      border: none;
      border-radius: 12px;
      font-size: 1.05rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .btn-upsell-accept {
      background: $gradient-primary;
      color: $white;
      box-shadow: 0 4px 12px rgba($p-color, 0.3);

      &:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba($p-color, 0.4);
      }
    }

    .btn-upsell-decline {
      background: $white;
      color: $gray-medium;
      border: 2px solid $gray-lightness;

      &:hover {
        background: $gray-lightness;
        border-color: $gray-medium;
      }
    }
  }

  @keyframes bounce {
    0%, 100% {
      transform: translateY(0);
    }
    50% {
      transform: translateY(-10px);
    }
  }
}

.custom-question {
  padding: 32px;
  background: $gray-lightness;
  border-radius: 16px;
  text-align: center;
  margin-bottom: 40px;

  h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: $gray-darkness;
    margin-bottom: 8px;
  }

  p {
    color: $gray-medium;
    margin-bottom: 24px;
  }

  .question-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 20px;

    button {
      padding: 14px 28px;
      border: none;
      border-radius: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
    }

    .btn-standard {
      background: $p-color;
      color: $white;

      &:hover {
        background: $p-dark;
        transform: translateY(-2px);
      }
    }

    .btn-custom {
      background: $white;
      color: $p-color;
      border: 2px solid $p-color;

      &:hover {
        background: $p-color;
        color: $white;
      }
    }
  }

  .back-to-landing {
    padding-top: 16px;
    border-top: 1px solid rgba($gray-medium, 0.2);

    a {
      font-size: 0.6rem;
      color: $gray-medium;
      cursor: pointer;
      transition: color 0.2s;

      &:hover {
        color: $p-color;
        text-decoration: underline;
      }
    }
  }
}

// Navegação
.step-navigation {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  margin-top: 40px;

  button {
    padding: 16px 32px;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s;

    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  }

  .btn-back {
    background: $white;
    color: $gray-darkness;
    border: 2px solid $gray-light;

    &:hover:not(:disabled) {
      border-color: $gray-darkness;
    }
  }

  .btn-next {
    background: $gradient-primary;
    color: $white;
    box-shadow: 0 4px 14px rgba($p-color, 0.4);

    &:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba($p-color, 0.5);
    }
  }
}

.renewal-notice {
  display: flex;
  gap: 12px;
  padding: 16px 20px;
  background: rgba($accent-blue, 0.05);
  border-left: 4px solid $accent-blue;
  border-radius: 8px;
  font-size: 0.9rem;
  line-height: 1.6;
  color: $gray-medium;
  margin-top: 24px;
  
  i {
    color: $accent-blue;
    font-size: 1.1rem;
    flex-shrink: 0;
    margin-top: 2px;
  }
  
  .notice-content {
    flex: 1;
    
    strong {
      color: $gray-darkness;
      font-weight: 600;
    }
  }
}

// Etapa 2: Briefing Form (Checkout)
.briefing-form {
  .form-section {
    margin-bottom: 40px;
    padding-bottom: 40px;
    border-bottom: 2px solid $gray-light;

    &:last-of-type {
      border-bottom: none;
    }

    .form-section-title {
      font-size: 1.3rem;
      font-weight: 700;
      color: $gray-darkness;
      margin-bottom: 24px;
    }
  }

  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;

    @media (max-width: 768px) {
      grid-template-columns: 1fr;
    }
  }

  .form-group {
    margin-bottom: 24px;

    label {
      display: block;
      font-weight: 600;
      color: $gray-darkness;
      margin-bottom: 8px;
    }

    input,
    textarea,
    select {
      width: 100%;
      padding: 14px 16px;
      font-size: 1rem;
      color: $gray-darkness;
      background: $white;
      border: 2px solid $gray-light;
      border-radius: 12px;
      transition: all 0.3s;

      &:focus {
        outline: none;
        border-color: $p-color;
        box-shadow: 0 0 0 4px rgba($p-color, 0.1);
      }
    }

    small {
      display: block;
      margin-top: 6px;
      font-size: 0.85rem;
      color: $gray-medium;
    }

    .file-input {
      padding: 12px;
      font-size: 0.95rem;
    }
  }

  .style-options {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;

    @media (max-width: 768px) {
      grid-template-columns: 1fr;
    }

    .style-card {
      padding: 24px;
      background: $white;
      border: 3px solid $gray-light;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s;
      text-align: center;

      &:hover {
        border-color: $p-color;
      }

      &.selected {
        border-color: $p-color;
        background: rgba($p-color, 0.05);
      }

      input[type="radio"] {
        display: none;
      }

      .style-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;

        i {
          font-size: 2.5rem;
          color: $p-color;
        }

        span {
          font-weight: 600;
          color: $gray-darkness;
        }
      }
    }
  }
}

.order-summary {
  padding: 32px;
  background: $gray-lightness;
  border-radius: 16px;
  margin-bottom: 40px;
  border: 2px solid rgba($p-color, 0.15);
  
  .summary-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    
    h3 {
      font-size: 1.3rem;
      font-weight: 700;
      color: $gray-darkness;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 10px;
      
      i {
        color: $p-color;
        font-size: 1.2rem;
      }
    }
    
    .promo-badge-summary {
      background: linear-gradient(135deg, $p-color 0%, $p-dark 100%);
      color: white;
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 700;
      box-shadow: 0 4px 12px rgba($p-color, 0.3);
    }
  }
  
  .included-services {
    margin-bottom: 24px;
    padding: 24px;
    background: linear-gradient(135deg, rgba($p-color, 0.05) 0%, rgba($p-dark, 0.02) 100%);
    border-radius: 12px;
    border: 2px solid rgba($p-color, 0.15);
    
    .services-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 16px;
      padding-bottom: 12px;
      border-bottom: 2px solid rgba($p-color, 0.1);
      
      i {
        color: $p-color;
        font-size: 1.1rem;
      }
      
      h4 {
        font-size: 1rem;
        font-weight: 700;
        color: $gray-darkness;
        margin: 0;
      }
    }
    
    .service-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 0;
      font-size: 0.9rem;
      color: $gray-medium;
      
      &:not(:last-child) {
        border-bottom: 1px solid rgba($gray-light, 0.5);
      }
      
      i {
        color: #10b981;
        font-size: 0.85rem;
        flex-shrink: 0;
      }
      
      .service-label {
        flex: 1;
        font-weight: 500;
        color: $gray-darkness;
      }
      
      .service-tag {
        padding: 3px 10px;
        background: rgba($p-color, 0.1);
        color: $p-color;
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
      }
    }
  }
  
  .original-price-banner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: rgba($gray-medium, 0.1);
    border-radius: 8px;
    margin-bottom: 16px;
    border-left: 4px solid $gray-medium;
    
    .label {
      font-size: 0.9rem;
      color: $gray-medium;
      font-weight: 600;
    }
    
    .value {
      font-size: 1.1rem;
      font-weight: 700;
      color: $gray-medium;
      text-decoration: line-through;
    }
  }

  .summary-items {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 2px solid $gray-light;

    .summary-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.95rem;
      color: $gray-medium;
      
      .item-prices {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 2px;
        
        .price-original-item {
          text-decoration: line-through;
          color: $gray-medium;
          font-size: 0.75rem;
          opacity: 0.7;
        }
        
        .price-current-item {
          font-weight: 600;
          color: $gray-darkness;
          font-size: 0.95rem;
        }
      }

      span:last-child {
        font-weight: 600;
        color: $gray-darkness;
      }
    }
  }

  .summary-total {
    .price-anchoring {
      text-align: center;
      padding: 20px;
      background: linear-gradient(135deg, rgba(#10b981, 0.1) 0%, rgba(#059669, 0.05) 100%);
      border-radius: 12px;
      margin-bottom: 24px;
      border: 2px dashed rgba(#10b981, 0.3);
      
      .anchoring-label {
        font-size: 0.9rem;
        color: $gray-medium;
        margin-bottom: 12px;
        font-weight: 600;
      }
      
      .anchoring-values {
        display: flex;
        gap: 24px;
        justify-content: center;
        align-items: center;
        margin-bottom: 12px;
        
        .anchoring-item {
          display: flex;
          flex-direction: column;
          align-items: center;
          
          .value {
            font-size: 1.8rem;
            font-weight: 800;
            color: #10b981;
            line-height: 1.2;
          }
          
          .label {
            font-size: 0.75rem;
            color: $gray-medium;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
          }
        }
        
        .divider {
          width: 1px;
          height: 50px;
          background: rgba($gray-medium, 0.3);
        }
      }
      
      .anchoring-subtext {
        font-size: 0.85rem;
        color: $gray-medium;
        font-style: italic;
      }
    }
    
    .payment-title {
      font-size: 1.1rem;
      font-weight: 700;
      color: $gray-darkness;
      margin-bottom: 16px;
      text-align: center;
    }
    
    .total-row {
      position: relative;
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 20px;
      border-radius: 16px;
      margin: 12px 0;
      cursor: pointer;
      transition: all 0.3s;
      border: 3px solid transparent;
      
      input[type="radio"] {
        display: none;
      }
      
      .payment-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 12px;
        
        .payment-header {
          display: flex;
          justify-content: space-between;
          align-items: flex-start;
          gap: 16px;
          
          .payment-main-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
            
            .payment-label {
              font-size: 1.1rem;
              font-weight: 700;
              color: $gray-darkness;
            }
            
            .badge-recommended {
              display: inline-flex;
              align-items: center;
              gap: 4px;
              background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
              color: #000;
              padding: 4px 10px;
              border-radius: 12px;
              font-size: 0.75rem;
              font-weight: 800;
              width: fit-content;
              box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
            }
          }
          
          .payment-prices {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
            
            .price-original-total {
              text-decoration: line-through;
              color: $gray-medium;
              font-size: 0.95rem;
              font-weight: 600;
            }
            
            .price-cash {
              font-size: 1.8rem;
              font-weight: 900;
              color: $success;
              
              small {
                font-size: 0.7rem;
                font-weight: 600;
                opacity: 0.8;
                margin-left: 4px;
              }
            }
            
            .price-installment {
              font-size: 1.5rem;
              font-weight: 800;
              color: $p-color;
            }
            
            .price-total-installment {
              font-size: 0.85rem;
              color: $gray-medium;
            }
          }
        }
        
        .payment-benefits {
          display: flex;
          flex-direction: column;
          gap: 6px;
          
          .payment-note {
            font-size: 0.85rem;
            color: $gray-medium;
            font-style: italic;
          }
        }
      }
      
      .radio-check {
        width: 28px;
        height: 28px;
        border: 2px solid $gray-light;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        
        i {
          font-size: 1.2rem;
          color: $white;
          opacity: 0;
          transition: all 0.3s;
        }
      }
      
      &.selected {
        .radio-check {
          background: $p-color;
          border-color: $p-color;
          
          i {
            opacity: 1;
          }
        }
      }

      &.installments {
        background: rgba($gray-light, 0.3);
        border-color: rgba($gray-light, 0.5);
        
        &:hover {
          background: $white;
          border-color: $p-color;
          box-shadow: 0 4px 12px rgba($p-color, 0.15);
        }
        
        &.selected {
          background: $white;
          border-color: $p-color;
          box-shadow: 0 4px 16px rgba($p-color, 0.2);
          
          .radio-check {
            background: $p-color;
            border-color: $p-color;
          }
        }

        .no-interest {
          color: $accent-green;
          font-weight: 900;
        }
      }

      &.cash {
        background: rgba($success, 0.08);
        border: 3px solid rgba($success, 0.4);
        
        &:hover {
          background: rgba($success, 0.12);
          border-color: $success;
          box-shadow: 0 6px 20px rgba($success, 0.25);
          transform: translateY(-2px);
        }
        
        &.featured {
          box-shadow: 0 6px 24px rgba($success, 0.3);
        }
        
        &.selected {
          background: rgba($success, 0.15);
          border-color: $success;
          box-shadow: 0 8px 28px rgba($success, 0.35);
          
          .radio-check {
            background: $success;
            border-color: $success;
          }
        }
        
        .economy-tag-cash {
          display: inline-flex;
          align-items: center;
          gap: 4px;
          font-size: 0.9rem;
          color: $success;
          font-weight: 800;
          background: rgba($success, 0.2);
          padding: 6px 12px;
          border-radius: 10px;
          width: fit-content;
          animation: pulse 2s ease-in-out infinite;
        }
      }
      
      .economy-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.85rem;
        color: $p-color;
        font-weight: 600;
        background: rgba($p-color, 0.1);
        padding: 4px 10px;
        border-radius: 8px;
        width: fit-content;
      }
    }
  }
}

.btn-submit {
  width: 100%;
  padding: 18px;
  background: $gradient-primary;
  color: $white;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: all 0.3s;

  &:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba($p-color, 0.5);
  }

  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
  
  &.btn-cash {
    background: linear-gradient(135deg, $success 0%, darken($success, 10%) 100%);
    font-size: 1.15rem;
    padding: 20px;
    box-shadow: 0 6px 20px rgba($success, 0.4);
    animation: pulse 2s ease-in-out infinite;
    
    &:hover:not(:disabled) {
      box-shadow: 0 10px 30px rgba($success, 0.5);
      transform: translateY(-3px);
    }
  }
}

// Price Sidebar
.price-sidebar {
  position: sticky;
  top: 100px;
  height: fit-content;
  background: $white;
  border-radius: 20px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  overflow: hidden;

  @media (max-width: 1024px) {
    display: none;
  }

  .sidebar-header {
    position: relative;
    
    .sidebar-promo-badge {
      position: absolute;
      top: 16px;
      right: 16px;
      background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
      color: white;
      padding: 6px 12px;
      border-radius: 12px;
      font-size: 0.75rem;
      font-weight: 700;
      box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
      animation: pulse 2s ease-in-out infinite;
    }
    padding: 24px;
    background: $gradient-primary;
    color: $white;

    h3 {
      font-size: 1.3rem;
      font-weight: 700;
    }
  }

  .sidebar-items {
    padding: 20px;
    max-height: 300px;
    overflow-y: auto;

    .sidebar-item {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid $gray-light;
      
      .item-price {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 2px;
        
        .price-original-small {
          text-decoration: line-through;
          color: $gray-medium;
          font-size: 0.7rem;
          opacity: 0.6;
        }
        
        .price-current-small {
          font-weight: 600;
          color: $gray-darkness;
        }
      }

      &:last-child {
        border-bottom: none;
      }

      .item-name {
        font-size: 0.95rem;
        color: $gray-medium;

        .item-qty {
          color: $p-color;
          font-weight: 600;
        }
      }

      .item-price {
        font-weight: 600;
        color: $gray-darkness;
      }
    }
  }

  .sidebar-total {
    padding: 24px 20px;
    background: linear-gradient(135deg, rgba($p-color, 0.08) 0%, rgba($p-dark, 0.05) 100%);
    border-top: 2px solid rgba($p-color, 0.2);

    .monthly-highlight {
      text-align: center;
      
      .monthly-label {
        display: block;
        font-size: 0.9rem;
        color: $gray-medium;
        margin-bottom: 12px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }
      
      .total-prices {
        display: flex;
        flex-direction: column;
        gap: 4px;
        margin-bottom: 8px;
        
        .total-original {
          text-decoration: line-through;
          color: $gray-medium;
          font-size: 1.2rem;
          opacity: 0.6;
          font-weight: 600;
        }
        
        .total-current {
          font-size: 2.5rem;
          font-weight: 900;
          background: $gradient-primary;
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
          background-clip: text;
          line-height: 1.2;
        }
      }

      .monthly-price {
        display: block;
        font-size: 2.5rem;
        font-weight: 900;
        background: $accent-green;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1.2;
        margin-bottom: 4px;
      }

      .monthly-hint {
        display: block;
        font-size: 0.8rem;
        color: $gray-medium;
        font-weight: 500;
      }
    }

    .total-line {
      display: flex;
      justify-content: space-between;
      padding: 8px 0;
      color: $gray-medium;

      &.installment {
        padding: 12px 0;
        border-top: 2px solid $gray-light;
        margin-top: 8px;

        .price-big {
          font-size: 1.3rem;
          font-weight: 800;
          color: $p-color;
        }
      }

      &.cash {
        padding: 12px 0;

        .price-big {
          font-size: 1.3rem;
          font-weight: 800;

          &.success {
            color: $success;
          }
        }
      }
    }
  }

  .sidebar-validation {
    padding: 12px 20px;
    background: rgba($gray-light, 0.3);
    border-top: 1px solid rgba($gray-light, 0.5);
    
    .validation-status {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.85rem;
      font-weight: 500;
      
      i {
        font-size: 1rem;
      }
      
      &.validating {
        color: $accent;
        
        i {
          animation: spin 1s linear infinite;
        }
      }
      
      &.validated {
        color: $success;
      }
      
      &.local {
        color: $gray-medium;
      }
    }
  }

  .sidebar-footer {
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: rgba($success, 0.1);
    color: $success;
    font-size: 0.9rem;
    font-weight: 600;

    i {
      font-size: 1.1rem;
    }
  }
}

// Modal
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 20px;
}

.modal-content {
  position: relative;
  max-width: 600px;
  width: 100%;
  background: $white;
  border-radius: 20px;
  padding: 40px;
  max-height: 90vh;
  overflow-y: auto;

  .modal-close {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: $gray-lightness;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s;

    &:hover {
      background: $gray-light;
      transform: rotate(90deg);
    }
  }

  .modal-title {
    font-size: 1.8rem;
    font-weight: 800;
    color: $gray-darkness;
    margin-bottom: 12px;
  }

  .modal-description {
    color: $gray-medium;
    margin-bottom: 32px;
    line-height: 1.6;
  }

  .custom-form {
    .form-group {
      margin-bottom: 20px;
    }

    .modal-actions {
      display: flex;
      gap: 12px;
      margin-top: 32px;

      button {
        flex: 1;
        padding: 14px;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
      }

      .btn-cancel {
        background: $white;
        color: $gray-darkness;
        border: 2px solid $gray-light;

        &:hover {
          border-color: $gray-darkness;
        }
      }

      .btn-submit-modal {
        background: $gradient-primary;
        color: $white;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;

        &:hover:not(:disabled) {
          transform: translateY(-2px);
          box-shadow: 0 8px 20px rgba($p-color, 0.4);
        }

        &:disabled {
          opacity: 0.6;
          cursor: not-allowed;
        }
      }
    }
  }
}

// Animations
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s, transform 0.3s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s;
}
.modal-enter-from, .modal-leave-to {
  opacity: 0;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}
</style>
