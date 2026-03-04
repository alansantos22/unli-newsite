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
            Escolha um pacote pronto ou personalize conforme sua necessidade.
          </p>

          <!-- ========== CARDS DE PACOTES PRÉ-DEFINIDOS ========== -->
          <div class="packages-section">
            <h3 class="section-title">
              <i class="fas fa-box-open"></i>
              Qual o objetivo do seu site hoje?
            </h3>
            <p class="section-subtitle">
              Escolha um pacote pronto que atende o seu perfil ou personalize cada item abaixo:
            </p>

            <div class="package-cards">
              <!-- Pacote Essencial -->
              <div 
                :class="['package-card', { selected: isPackageSelected('essential') }]"
                @click="selectPackage('essential')"
              >
                <div class="package-icon">🏢</div>
                <h4 class="package-name">Essencial</h4>
                <p class="package-tagline">Para quem presta serviços e precisa ser encontrado</p>
                
                <div class="package-includes">
                  <div class="includes-title">Inclui:</div>
                  <ul>
                    <li><i class="fas fa-check"></i> Home</li>
                    <li><i class="fas fa-check"></i> Sobre Nós</li>
                    <li><i class="fas fa-check"></i> Serviços</li>
                    <li><i class="fas fa-check"></i> Contato</li>
                  </ul>
                </div>
                
                <div class="package-ideal">
                  <i class="fas fa-users"></i>
                  <span>Ideal para: {{ config.predefined_packages.essential.ideal_for }}</span>
                </div>
              </div>

              <!-- Pacote Autoridade -->
              <div 
                :class="['package-card recommended', { selected: isPackageSelected('authority') }]"
                @click="selectPackage('authority')"
              >
                <div class="package-icon">🚀</div>
                <h4 class="package-name">Autoridade</h4>
                <p class="package-tagline">Mostre seu trabalho e tire dúvidas para fechar contratos</p>
                
                <div class="package-includes">
                  <div class="includes-title">Tudo do Essencial +</div>
                  <ul>
                    <li><i class="fas fa-check"></i> Portfólio</li>
                    <li><i class="fas fa-check"></i> FAQ</li>
                    <li><i class="fas fa-check"></i> Depoimentos</li>
                  </ul>
                </div>
                
                <div class="package-ideal">
                  <i class="fas fa-users"></i>
                  <span>Ideal para: {{ config.predefined_packages.authority.ideal_for }}</span>
                </div>
              </div>

              <!-- Pacote Enterprise -->
              <div 
                :class="['package-card premium', { selected: isPackageSelected('enterprise') }]"
                @click="selectPackage('enterprise')"
              >
                <div class="premium-shine"></div>
                <div class="package-icon">💎</div>
                <h4 class="package-name">Ecossistema Digital</h4>
                <p class="package-tagline">Atraia tráfego com Blog e exiba seus produtos online</p>
                
                <div class="package-includes">
                  <div class="includes-title">Tudo do Autoridade +</div>
                  <ul>
                    <li><i class="fas fa-check"></i> Blog de Notícias <span class="new-badge">Novo</span></li>
                    <li><i class="fas fa-check"></i> Vitrine de Produtos <span class="new-badge">Novo</span></li>
                  </ul>
                </div>
                
                <div class="package-ideal">
                  <i class="fas fa-users"></i>
                  <span>Ideal para: {{ config.predefined_packages.enterprise.ideal_for }}</span>
                </div>
              </div>
            </div>

            <div class="custom-notice">
              <i class="fas fa-arrow-down"></i>
              <span>Prefere montar do seu jeito? Personalize os itens abaixo</span>
            </div>
          </div>

          <!-- Páginas Adicionais (somente Site Completo) -->
          <div v-if="selectedProduct === 'site_complete' && config.page_addons" class="addon-section">
            <h3 class="section-title">
              <i class="fas fa-sliders-h"></i>
              Personalize seu Pacote
            </h3>
            <p class="section-subtitle">
              {{ selectedPackage ? 'Ajuste as páginas do pacote selecionado:' : 'Selecione as páginas que deseja adicionar:' }}
            </p>
            
            <!-- Toggle Switch "Estrutura Completa" -->
            <div class="select-all-wrapper">
              <label class="toggle-select-all">
                <input
                  type="checkbox"
                  :checked="allPagesSelected"
                  @change="toggleAllPages"
                >
                <div class="toggle-content">
                  <i class="fas fa-star"></i>
                  <div class="toggle-text">
                    <strong>Ativar Estrutura Profissional Completa</strong>
                    <span>Adiciona todas as páginas essenciais para passar máxima credibilidade ao seu cliente.</span>
                  </div>
                  <div class="toggle-badge">
                    <i class="fas fa-award"></i>
                    Recomendado
                  </div>
                  <div class="toggle-switch">
                    <div class="toggle-slider"></div>
                  </div>
                </div>
              </label>
            </div>
            
            <div class="addon-checkboxes">
              <label
                v-for="(page, key) in config.page_addons"
                :key="key"
                :class="['checkbox-item', { 'premium-item': page.isPremium }]"
              >
                <input
                  type="checkbox"
                  :value="key"
                  v-model="selectedPages"
                >
                <div class="checkbox-content">
                  <div v-if="!page.isPremium" class="promo-tag">-30%</div>
                  <div v-if="page.isNew" class="new-tag">
                    <i class="fas fa-sparkles"></i> Novo
                  </div>
                  <div class="checkbox-info">
                    <span class="checkbox-name">
                      {{ page.name }}
                      <span class="addon-tooltip">
                        <i class="fas fa-info-circle"></i>
                        <span class="tooltip-text">{{ page.description || getPageTooltip(key) }}</span>
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
              <span class="counter-price">+R$ {{ customPageBasePriceMonthly.toFixed(2).replace('.', ',') }}/mês por página</span>
            </div>

            <!-- Box para cada página personalizada -->
            <div
              v-for="(page, index) in customPages"
              :key="index"
              class="custom-page-box"
            >
              <div class="custom-page-header">
                <h4>Página de Conteúdo Extra #{{ index + 1 }}</h4>
                <button class="remove-page-btn" @click="removeCustomPageByIndex(index)">
                  <i class="fas fa-times"></i>
                </button>
              </div>

              <div class="custom-page-description">
                <label>Descreva o conteúdo da sua nova página:</label>
                <p class="description-helper">
                  Este espaço é perfeito para <strong>apresentar informações detalhadas, criar ofertas temporárias ou destacar um serviço específico</strong>.<br>
                  <em>Ideal para: Landing Pages promocionais, Detalhes de um Serviço, Biografia ou Página de Vendas.</em>
                </p>
                <textarea
                  v-model="page.description"
                  maxlength="1500"
                  placeholder="Ex: Quero uma página especial para Black Friday com contagem regressiva, galeria de fotos dos produtos em promoção e formulário de contato no final..."
                  rows="4"
                ></textarea>
                <span class="char-count">{{ page.description.length }}/1500 caracteres</span>
              </div>

              <div class="custom-page-resources">
                <h5>Recursos para esta página:</h5>
                <div class="resources-grid">
                  <label class="resource-item">
                    <input type="checkbox" v-model="page.resources.image">
                    <div class="resource-content">
                      <i class="fas fa-image"></i>
                      <span class="resource-name">Imagens</span>
                      <span class="resource-price">+R$ {{ customPageResourcePrices.images?.toFixed(2).replace('.', ',') || '0,00' }}/mês</span>
                    </div>
                  </label>

                  <label class="resource-item">
                    <input type="checkbox" v-model="page.resources.video">
                    <div class="resource-content">
                      <i class="fas fa-video"></i>
                      <span class="resource-name">Vídeo</span>
                      <span class="resource-price">+R$ {{ customPageResourcePrices.video?.toFixed(2).replace('.', ',') || '0,00' }}/mês</span>
                    </div>
                  </label>

                  <label class="resource-item">
                    <input type="checkbox" v-model="page.resources.carousel">
                    <div class="resource-content">
                      <i class="fas fa-images"></i>
                      <span class="resource-name">Carrossel</span>
                      <span class="resource-price">+R$ {{ customPageResourcePrices.carousel?.toFixed(2).replace('.', ',') || '0,00' }}/mês</span>
                    </div>
                  </label>

                  <label class="resource-item">
                    <input type="checkbox" v-model="page.resources.form">
                    <div class="resource-content">
                      <i class="fas fa-envelope"></i>
                      <span class="resource-name">Formulário</span>
                      <span class="resource-price">+R$ {{ customPageResourcePrices.form?.toFixed(2).replace('.', ',') || '0,00' }}/mês</span>
                    </div>
                  </label>

                  <label class="resource-item">
                    <input type="checkbox" v-model="page.resources.testimonials">
                    <div class="resource-content">
                      <i class="fas fa-quote-right"></i>
                      <span class="resource-name">Depoimentos</span>
                      <span class="resource-price">+R$ {{ customPageResourcePrices.testimonials?.toFixed(2).replace('.', ',') || '0,00' }}/mês</span>
                    </div>
                  </label>
                </div>
              </div>

              <!-- Nota informativa -->
              <div class="custom-page-note">
                <i class="fas fa-info-circle"></i>
                <div class="note-content">
                  <strong>Precisa de um Blog ou Loja Online?</strong>
                  <p>Esses recursos exigem programação avançada de banco de dados e não se encaixam como "Página Extra". Veja nosso plano <strong>Ecossistema Digital</strong>.</p>
                </div>
              </div>

              <div class="custom-page-total">
                <span>Total desta página:</span>
                <span class="total-value">{{ formatMonthlyPrice(calculateCustomPageTotal(page)) }}</span>
              </div>
            </div>
          </div>

          <!-- Tipos de Arquivos Aceitos -->
          <div v-if="config.content_addons" class="addon-section">
            <h3 class="section-title">
              <i class="fas fa-file-upload"></i>
              Tipos de Arquivos Aceitos
            </h3>
            <div class="addon-toggles">
              <!-- Vídeo Básico -->
              <div v-if="config.content_addons.video_basic" class="toggle-item video-addon">
                <label class="video-toggle">
                  <input
                    type="checkbox"
                    value="video_basic"
                    v-model="selectedContentAddons"
                    @change="updateVideoBasicSelection"
                  >
                  <div class="toggle-content">
                    <div class="toggle-info">
                      <span class="toggle-name">
                        {{ config.content_addons.video_basic.name }}
                        <span class="addon-tooltip">
                          <i class="fas fa-info-circle"></i>
                          <span class="tooltip-text">{{ config.content_addons.video_basic.description }}</span>
                        </span>
                        <span class="promo-inline-badge">-30%</span>
                      </span>
                      <div class="toggle-price">
                        <span class="price-from">De {{ formatOriginalPrice(config.content_addons.video_basic.price_per_unit / 12) }}</span>
                        <span class="price-to">+{{ formatMonthlyPrice(config.content_addons.video_basic.price_per_unit) }}</span>
                      </div>
                    </div>
                    <div class="toggle-switch">
                      <span class="switch"></span>
                    </div>
                  </div>
                </label>
                
                <!-- Input de quantidade para Vídeo Básico -->
                <div v-if="videoBasicSelected" class="quantity-input-section">
                  <label class="quantity-label">Quantidade:</label>
                  <div class="quantity-input-wrapper">
                    <button type="button" class="quantity-btn" @click="decreaseVideoBasic" :disabled="videoBasicQuantity <= 1">-</button>
                    <input 
                      type="number" 
                      v-model.number="videoBasicQuantity"
                      :min="config.content_addons.video_basic.min_quantity"
                      :max="config.content_addons.video_basic.max_quantity"
                      class="quantity-input"
                    >
                    <button type="button" class="quantity-btn" @click="increaseVideoBasic" :disabled="videoBasicQuantity >= 10">+</button>
                  </div>
                  <div class="quantity-info">
                    <span class="unit-price">R$ {{ (config.content_addons.video_basic.price_per_unit / 12).toFixed(2).replace('.', ',') }}/mês por vídeo</span>
                    <span class="total-price">Total: R$ {{ ((config.content_addons.video_basic.price_per_unit * videoBasicQuantity) / 12).toFixed(2).replace('.', ',') }}/mês</span>
                  </div>
                </div>
              </div>

              <!-- Vídeo Pro -->
              <div v-if="config.content_addons.video_pro" class="toggle-item video-addon pro-addon">
                <label class="video-toggle">
                  <input
                    type="checkbox"
                    value="video_pro"
                    v-model="selectedContentAddons"
                    @change="updateVideoProSelection"
                  >
                  <div class="toggle-content">
                    <div class="toggle-info">
                      <span class="toggle-name">
                        {{ config.content_addons.video_pro.name }}
                        <span class="premium-badge">PRO</span>
                        <span class="addon-tooltip">
                          <i class="fas fa-info-circle"></i>
                          <span class="tooltip-text">{{ config.content_addons.video_pro.description }}</span>
                        </span>
                        <span class="promo-inline-badge">-30%</span>
                      </span>
                      <div class="toggle-price">
                        <span class="price-from">De {{ formatOriginalPrice(config.content_addons.video_pro.price_per_unit / 12) }}</span>
                        <span class="price-to">+{{ formatMonthlyPrice(config.content_addons.video_pro.price_per_unit) }}</span>
                      </div>
                    </div>
                    <div class="toggle-switch">
                      <span class="switch"></span>
                    </div>
                  </div>
                </label>
                
                <!-- Input de quantidade para Vídeo Pro -->
                <div v-if="videoProSelected" class="quantity-input-section">
                  <label class="quantity-label">Quantidade:</label>
                  <div class="quantity-input-wrapper">
                    <button type="button" class="quantity-btn" @click="decreaseVideoPro" :disabled="videoProQuantity <= 1">-</button>
                    <input 
                      type="number" 
                      v-model.number="videoProQuantity"
                      :min="config.content_addons.video_pro.min_quantity"
                      :max="config.content_addons.video_pro.max_quantity"
                      class="quantity-input"
                    >
                    <button type="button" class="quantity-btn" @click="increaseVideoPro" :disabled="videoProQuantity >= 10">+</button>
                  </div>
                  <div class="quantity-info">
                    <span class="unit-price">R$ {{ (config.content_addons.video_pro.price_per_unit / 12).toFixed(2).replace('.', ',') }}/mês por vídeo</span>
                    <span class="total-price">Total: R$ {{ ((config.content_addons.video_pro.price_per_unit * videoProQuantity) / 12).toFixed(2).replace('.', ',') }}/mês</span>
                  </div>
                </div>
              </div>

              <!-- Outros addons (PDF, etc.) -->
              <label
                v-for="(content, key) in nonVideoContentAddons"
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
                      <span class="price-from">De {{ formatOriginalPrice((content.price || content.price_per_unit) / 12) }}</span>
                      <span class="price-to">+{{ formatMonthlyPrice(content.price || content.price_per_unit) }}</span>
                    </div>
                  </div>
                  <div class="toggle-switch">
                    <span class="switch"></span>
                  </div>
                </div>
              </label>
              
              <!-- Informação sobre YouTube (aparece quando qualquer vídeo é selecionado) -->
              <div v-if="videoBasicSelected || videoProSelected" class="youtube-info-section">
                <div class="youtube-info">
                  <i class="fab fa-youtube"></i>
                  <span><strong>Dica:</strong> Vídeos do YouTube ou links de incorporação são tratados como Vídeo Básico (O Pro é cobrado a mais pelo armazenamento e mantenção do arquivo)</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Limites de Upload -->
          <div class="upload-limits-info">
            <div class="info-header">
              <i class="fas fa-cloud-upload-alt"></i>
              <span>Limites de Upload por Arquivo</span>
            </div>
            <div class="limits-grid">
              <div class="limit-item">
                <i class="fas fa-images"></i>
                <span class="limit-type">Imagens</span>
                <span class="limit-value">3MB máx</span>
                <span class="limit-note">Múltiplas</span>
              </div>
              <div class="limit-item">
                <i class="fas fa-file-pdf"></i>
                <span class="limit-type">PDFs</span>
                <span class="limit-value">15MB máx</span>
                <span class="limit-note">Múltiplos</span>
              </div>
              <div class="limit-item basic-plan">
                <i class="fas fa-video"></i>
                <span class="limit-type">Vídeo Básico</span>
                <span class="limit-value">50MB máx</span>
                <span class="limit-note">1 unidade</span>
              </div>
              <div class="limit-item pro-plan">
                <i class="fas fa-video"></i>
                <span class="limit-type">Vídeo Pro</span>
                <span class="limit-value">1GB máx</span>
                <span class="limit-note premium-note">1 unidade</span>
              </div>
            </div>
            <div class="video-plans-note">
              <i class="fas fa-info-circle"></i>
              <span>Você pode contratar múltiplas unidades de vídeo (ex: 3x Vídeo Básico + 2x Vídeo Pro)</span>
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
              <h3>Desbloqueie o Site Multi-Páginas por + R$ 1,66/mês</h3>
            </div>
            <p class="upsell-description">
              O plano Landing Page te limita a uma página. Com o upgrade de apenas <strong>R$ 20,00 anuais</strong>, você ganha estrutura para crescer (Home, Sobre, Serviços, Blog e mais). É a escolha de 95% dos clientes.
            </p>
            <div class="upsell-actions">
              <button class="btn-upsell-accept" @click="upgradeToComplete">
                <i class="fas fa-star"></i>
                Quero Liberdade Total (+ R$ 20/ano)
              </button>
              <button class="btn-upsell-decline" @click="proceedToCheckout">
                Prefiro continuar limitado à Página Única
              </button>
            </div>
          </div>

          <!-- Pergunta de Customização para Site Completo -->
          <div v-else class="custom-question">
            <h3>Vamos confirmar o escopo do seu projeto?</h3>
            <p>O Plano Anual cobre sites institucionais completos. Se você precisa de sistemas complexos, indicamos nossa consultoria.</p>
            <div class="question-actions">
              <button class="btn-standard" @click="proceedToCheckout">
                <i class="fas fa-check"></i>
                O Plano Site Profissional é o que eu preciso
              </button>
              <button class="btn-custom" @click="openCustomForm">
                <i class="fas fa-comments"></i>
                Preciso de uma Loja Virtual ou Sistema
              </button>
            </div>
            <div class="back-to-landing">
              <a @click="selectedProduct = 'landing'">
                ← Busco algo mais simples (Ver Planos de Landing Page)
              </a>
            </div>
          </div>
        </div>

        <!-- Etapa 2: Finalizar Pedido (Checkout Simplificado) -->
        <!-- Etapa 3 quando não há initialProduct, Etapa 2 quando há -->
        <div v-else-if="currentStep === (initialProduct ? 2 : 3)" class="step-content step-checkout">
          <h2 class="step-title">Finalizar Pedido</h2>
          <p class="step-description">
            Apenas mais alguns dados para processar seu pagamento.
            <strong>Você receberá um link por e-mail para configurar seu site após o pagamento.</strong>
          </p>

          <!-- Alertas de Validação -->
          <div v-if="formErrors.length > 0" class="validation-alert">
            <i class="fas fa-exclamation-triangle"></i>
            <div class="alert-content">
              <strong>Por favor, corrija os seguintes erros:</strong>
              <ul>
                <li v-for="(error, index) in formErrors" :key="index">{{ error }}</li>
              </ul>
            </div>
          </div>

          <form class="checkout-form" @submit.prevent="submitOrder">
            <!-- Informações de Contato -->
            <div class="form-section">
              <h3 class="form-section-title">
                <i class="fas fa-user"></i>
                Seus Dados para Contato
              </h3>
              
              <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <p>Enviaremos um <strong>link</strong> para este e-mail onde você poderá configurar todo o conteúdo do seu site de forma rápida e guiada.</p>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>Seu Nome Completo *</label>
                  <input 
                    type="text" 
                    v-model="briefing.customer_name"
                    placeholder="Ex: João Silva"
                    minlength="3"
                    required
                  >
                </div>

                <div class="form-group">
                  <label>E-mail *</label>
                  <input 
                    type="email" 
                    v-model="briefing.email"
                    placeholder="seu@email.com"
                    required
                  >
                  <small class="field-hint">
                    💡 Você receberá o link de configuração neste e-mail
                  </small>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>WhatsApp *</label>
                  <input 
                    type="tel" 
                    v-model="briefing.whatsapp"
                    @input="handleWhatsAppInput('briefing')"
                    placeholder="(00) 00000-0000"
                    maxlength="15"
                    required
                  >
                  <small class="field-hint">
                    Para enviarmos atualizações sobre seu pedido
                  </small>
                </div>

                <div class="form-group">
                  <label>CPF/CNPJ (opcional)</label>
                  <input 
                    type="text" 
                    v-model="briefing.document"
                    @input="handleDocumentInput"
                    placeholder="000.000.000-00"
                    maxlength="18"
                  >
                </div>
              </div>
            </div>

            <!-- Resumo do Pedido -->
            <div class="form-section order-summary-section">
              <h3 class="form-section-title">
                <i class="fas fa-receipt"></i>
                Resumo do Pedido
              </h3>

              <!-- Header Compacto com Toggle -->
              <div class="summary-header">
                <div class="product-info">
                  <h3>
                    Plano Anual PRO
                  </h3>
                  <p class="product-summary">Site + Hospedagem + Domínio + SSL</p>
                </div>
                <button 
                  type="button"
                  @click="showDetails = !showDetails" 
                  class="btn-toggle-details"
                  :class="{ open: showDetails }"
                >
                  {{ showDetails ? 'Ocultar' : 'Ver tudo' }}
                  <i class="fas fa-chevron-down"></i>
                </button>
              </div>
              
              <!-- Serviços Inclusos (Accordion) -->
              <div 
                class="included-services"
                :class="{ collapsed: !showDetails, expanded: showDetails }"
              >
                <ul>
                  <li>
                    <i class="fas fa-check-circle"></i>
                    <span><strong>Domínio</strong> .com.br ou .com</span>
                  </li>
                  <li>
                    <i class="fas fa-check-circle"></i>
                    <span><strong>Hospedagem Premium</strong> (12 meses)</span>
                  </li>
                  <li>
                    <i class="fas fa-check-circle"></i>
                    <span><strong>Certificado SSL</strong> (HTTPS)</span>
                  </li>
                  <li>
                    <i class="fas fa-check-circle"></i>
                    <span><strong>Criação e Design</strong> Profissional</span>
                  </li>
                  <li>
                    <i class="fas fa-check-circle"></i>
                    <span><strong>Suporte Técnico</strong> (12 meses)</span>
                  </li>
                  
                  <!-- Itens Expandíveis (Técnicos) -->
                  <template v-if="showAllIncludedItems">
                    <li>
                      <i class="fas fa-check-circle"></i>
                      <span><strong>Monitoramento 24/7</strong> de disponibilidade</span>
                    </li>
                    <li>
                      <i class="fas fa-check-circle"></i>
                      <span><strong>Otimização SEO</strong> básica</span>
                    </li>
                    <li>
                      <i class="fas fa-check-circle"></i>
                      <span><strong>Responsivo Mobile</strong> (todas as telas)</span>
                    </li>
                    <li>
                      <i class="fas fa-check-circle"></i>
                      <span><strong>Backup Automático</strong></span>
                    </li>
                  </template>
                </ul>
                
                <button 
                  class="btn-toggle-items"
                  @click="showAllIncludedItems = !showAllIncludedItems"
                  type="button"
                >
                  <span v-if="!showAllIncludedItems">
                    <i class="fas fa-plus-circle"></i>
                    Ver mais 3 itens técnicos
                  </span>
                  <span v-else>
                    <i class="fas fa-minus-circle"></i>
                    Mostrar menos
                  </span>
                </button>
              </div>
              
              <!-- Divider -->
              <hr class="divider">
              
              <!-- Preço Mensal / Cobrança Anual (Estratégia SaaS) -->
              <div class="price-display">
                <!-- Ancoragem de Preço Original -->
                <div class="price-anchorage">
                  <span class="label-from">De</span>
                  <span class="old-price">{{ formatOriginalPrice(installmentTotal) }}</span>
                </div>
                
                <!-- Destaque: Preço Mensal -->
                <span class="label">Por apenas:</span>
                <div class="price-display-monthly">
                  <div class="price-row">
                    <div class="main-price">
                      <span class="currency">R$</span>
                      <span class="amount">{{ monthlyEquivalent }}</span>
                      <span class="period">/mês</span>
                    </div>
                    
                    <!-- Badge Café (Alinhado à Direita) -->
                    <div class="coffee-badge-secondary">
                      ☕ R$ {{ dailyPrice }} / dia
                    </div>
                  </div>
                  
                  <!-- Transparência: Contexto de Cobrança -->
                  <div class="sub-price-context">
                    <i class="fas fa-info-circle"></i>
                    <span>Faturado em parcela única de <strong>{{ formatPrice(installmentTotal) }}</strong></span>
                  </div>
                </div>
                
                <p class="savings-text">
                  <i class="fas fa-piggy-bank"></i>
                  Você economiza <strong>{{ formatPrice(getTotalSavings()) }}</strong> escolhendo Pix!
                </p>
              </div>
              
              <!-- Seletor de Pagamento Compacto -->
              <div class="payment-selector">
                <!-- Opção Pix (PRÉ-SELECIONADA) -->
                <label class="payment-option">
                  <input 
                    type="radio" 
                    value="cash" 
                    v-model="paymentMethod"
                  >
                  <div class="payment-content">
                    <div class="radio-header">
                      <span class="radio-label">
                        <i class="fas fa-qrcode"></i> Pagar via Pix
                      </span>
                      <span class="discount-tag extra-discount">45% OFF</span>
                    </div>
                    <button 
                      type="submit"
                      class="btn-pay-now btn-pix"
                      :disabled="isSubmitting"
                    >
                      <span class="btn-main-text">
                        <i class="fas fa-lock"></i>
                        {{ isSubmitting ? 'Processando...' : 'Pagar Plano Anual (Pix)' }}
                      </span>
                      <span class="btn-sub-text">
                        Economize {{ formatPrice(getPixExtraSavings()) }} hoje
                      </span>
                    </button>
                  </div>
                </label>
                
                <!-- Opção Cartão -->
                <label class="payment-option">
                  <input 
                    type="radio" 
                    value="installments" 
                    v-model="paymentMethod"
                  >
                  <div class="payment-content">
                    <div class="radio-header">
                      <span class="radio-label">
                        <i class="fas fa-credit-card"></i> Cartão em 12x
                      </span>
                      <span class="discount-tag">30% OFF</span>
                    </div>
                    <p class="payment-details">
                      12x de {{ formatPrice(installmentValue) }} <strong>sem juros</strong>
                    </p>
                    <button 
                      type="submit"
                      class="btn-pay-now"
                      :disabled="isSubmitting"
                    >
                      <span class="btn-main-text">
                        <i class="fas fa-lock"></i>
                        {{ isSubmitting ? 'Processando...' : 'Assinar Plano Anual (12x)' }}
                      </span>
                      <span class="btn-sub-text">
                        Compra segura e protegida
                      </span>
                    </button>
                  </div>
                </label>
              </div>
            </div>

            <!-- Botão de Voltar (Navegação Simples) -->
            <div class="step-navigation-back">
              <button type="button" class="btn-back" @click="previousStep">
                <i class="fas fa-arrow-left"></i>
                Quero voltar para fazer ajustes
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

    <!-- Mobile Price Bar (fixed bottom) -->
    <div class="mobile-price-bar">
      <div class="mobile-price-content">
        <div class="mobile-price-info">
          <div class="mobile-price-total">
            <span class="mobile-price-label">Total</span>
            <div class="mobile-price-values">
              <span class="mobile-price-original">De {{ formatOriginalPrice(subtotal / 12) }}</span>
              <span class="mobile-price-current">{{ formatMonthlyPrice(subtotal) }}</span>
            </div>
          </div>
        </div>
        <button 
          v-if="!isCheckoutStep"
          class="mobile-btn-next"
          :disabled="!canProceed"
          @click="nextStep"
        >
          <span>Próximo</span>
          <i class="fas fa-arrow-right"></i>
        </button>
        <button 
          v-else
          type="button"
          class="mobile-btn-finish"
          :disabled="!canProceed"
          @click="scrollToOrderSummary"
        >
          <span>Ir para resumo do pedido</span>
          <i class="fas fa-arrow-down"></i>
        </button>
      </div>
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
          <span class="item-name">Página de Conteúdo Extra #{{ index + 1 }}</span>
          <div class="item-price">
            <span class="price-original-small">{{ formatOriginalPrice(calculateCustomPageTotal(page) / 12) }}</span>
            <span class="price-current-small">{{ formatMonthlyPrice(calculateCustomPageTotal(page)) }}</span>
          </div>
        </div>

        <!-- Vídeo Básico -->
        <div v-if="videoBasicSelected && config.content_addons.video_basic" class="sidebar-item">
          <span class="item-name">
            {{ config.content_addons.video_basic.name }}
            <small v-if="videoBasicQuantity > 1">({{ videoBasicQuantity }} unidades)</small>
          </span>
          <div class="item-price">
            <span class="price-original-small">{{ formatOriginalPrice((config.content_addons.video_basic.price_per_unit * videoBasicQuantity) / 12) }}</span>
            <span class="price-current-small">{{ formatMonthlyPrice(config.content_addons.video_basic.price_per_unit * videoBasicQuantity) }}</span>
          </div>
        </div>

        <!-- Vídeo Pro -->
        <div v-if="videoProSelected && config.content_addons.video_pro" class="sidebar-item">
          <span class="item-name">
            {{ config.content_addons.video_pro.name }}
            <small v-if="videoProQuantity > 1">({{ videoProQuantity }} unidades)</small>
          </span>
          <div class="item-price">
            <span class="price-original-small">{{ formatOriginalPrice((config.content_addons.video_pro.price_per_unit * videoProQuantity) / 12) }}</span>
            <span class="price-current-small">{{ formatMonthlyPrice(config.content_addons.video_pro.price_per_unit * videoProQuantity) }}</span>
          </div>
        </div>

        <!-- Outros addons -->
        <div 
          v-for="addonKey in validSelectedContentAddons"
          :key="addonKey"
          class="sidebar-item"
        >
          <span class="item-name">{{ config.content_addons[addonKey].name }}</span>
          <div class="item-price">
            <span class="price-original-small">{{ formatOriginalPrice((config.content_addons[addonKey].price || config.content_addons[addonKey].price_per_unit) / 12) }}</span>
            <span class="price-current-small">{{ formatMonthlyPrice(config.content_addons[addonKey].price || config.content_addons[addonKey].price_per_unit) }}</span>
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
                  @input="handleWhatsAppInput('customRequest')"
                  placeholder="(00) 00000-0000"
                  maxlength="15"
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
      selectedContentAddons: [], // ['video_basic', 'video_pro', 'pdf']
      customPages: [], // Array de objetos: [{ description: '', resources: { image: false, video: false, carousel: false, form: false } }]
      
      // Controle de vídeos (quantidades separadas)
      videoBasicQuantity: 1,
      videoProQuantity: 1,
      
      // Pacote Pré-definido selecionado
      selectedPackage: null, // 'essential', 'authority', 'enterprise' ou null (personalizado)
      
      // Briefing (Apenas dados para checkout/pagamento)
      // O briefing completo será coletado via OnboardingWizard após o pagamento
      briefing: {
        customer_name: '',
        email: '',
        whatsapp: '',
        document: '' // CPF/CNPJ opcional
      },
      
      // Validação
      formErrors: [],
      
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
      isValidating: false,
      
      // UI Control - Accordion para mobile
      showDetails: false,
      
      // UI Control - Expandir lista de itens incluídos
      showAllIncludedItems: false,
      
      // Mensagens de erro do sistema
      systemError: null
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
      // Verificar se é erro crítico (produção) ou desenvolvimento
      const debugMode = this.$store.state.ConfigModule?.debug;
      
      if (debugMode) {
        console.warn('🔧 DEBUG MODE - API desabilitada (cálculos locais permitidos)');
      } else {
        console.warn('🟡 API indisponível no mount. Retry automático será feito no checkout.');
        console.warn('🟡 Verifique: .htaccess, CORS, permissões de arquivos');
        
        // Agenda um retry silencioso após 5s
        setTimeout(async () => {
          const retryResult = await PricingService.checkServerAvailability(true);
          if (retryResult) {
            console.info('✅ API reconectada após retry automático');
            this.serverAvailable = true;
            this.systemError = '';
          }
        }, 5000);
      }
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
        // Verificar se ainda corresponde ao pacote selecionado
        this.checkIfPackageStillMatches();
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
    },
    // Limpar erros quando usuário começar a corrigir
    'briefing.customer_name'() {
      if (this.formErrors.length > 0) {
        this.formErrors = [];
      }
    },
    'briefing.email'() {
      if (this.formErrors.length > 0) {
        this.formErrors = [];
      }
    },
    'briefing.whatsapp'() {
      if (this.formErrors.length > 0) {
        this.formErrors = [];
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
    
    // Verificar se todas as páginas estão selecionadas
    allPagesSelected() {
      if (!this.config.page_addons) return false;
      const allPageKeys = Object.keys(this.config.page_addons);
      return allPageKeys.length > 0 && allPageKeys.every(key => this.selectedPages.includes(key));
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
      
      // Garantir que selectedPages seja tratado como array
      let pages = this.selectedPages;
      if (Array.isArray(pages)) {
        // Já é array, apenas filtrar
        const filtered = pages.filter(key => {
          const exists = !!this.config.page_addons[key];
          console.log(`  - Checking key "${key}": ${exists ? '✅ exists' : '❌ NOT FOUND'}`);
          return exists;
        });
        console.log('  ✅ Result:', filtered);
        return filtered;
      } else if (typeof pages === 'object' && pages !== null) {
        // É um objeto (veio do servidor), converter para array
        console.log('  ℹ️ Converting Object to Array');
        const keys = Object.keys(pages).filter(key => !!this.config.page_addons[key]);
        console.log('  ✅ Result:', keys);
        return keys;
      }
      
      console.log('  ⚠️ selectedPages is invalid type');
      return [];
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
    
    // Filtrar addons excluindo vídeo (que tem tratamento especial)
    nonVideoContentAddons() {
      if (!this.config.content_addons) return {};
      
      // eslint-disable-next-line no-unused-vars
      const { video_basic, video_pro, ...others } = this.config.content_addons;
      return others;
    },
    
    // Controle de vídeos
    videoBasicSelected() {
      return this.selectedContentAddons.includes('video_basic');
    },
    
    videoProSelected() {
      return this.selectedContentAddons.includes('video_pro');
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
      let total = 0;
      
      // Calcular outros addons (não-vídeo)
      total += this.validSelectedContentAddons
        .filter(key => key !== 'video_basic' && key !== 'video_pro') // Excluir vídeos
        .reduce((sum, key) => {
          return sum + (this.config.content_addons[key]?.price || 0);
        }, 0);
      
      // Adicionar vídeos se selecionados (com quantidade)
      if (this.videoBasicSelected && this.config.content_addons.video_basic) {
        total += this.config.content_addons.video_basic.price_per_unit * this.videoBasicQuantity;
      }
      
      if (this.videoProSelected && this.config.content_addons.video_pro) {
        total += this.config.content_addons.video_pro.price_per_unit * this.videoProQuantity;
      }
      
      return total;
    },
    
    subtotal() {
      // SEMPRE calcular localmente para feedback visual instantâneo
      const localPrice = this.basePrice + this.pagesTotal + this.contentTotal;
      return localPrice;
    },
    
    cashPrice() {
      // À vista = preço NORMAL (sem taxa) - sempre calcular localmente
      return this.subtotal;
    },
    
    installmentTotal() {
      // Parcelado = preço + 15% (taxa do gateway) - sempre calcular localmente
      const markup = 0.15; // 15%
      return Math.round(this.subtotal * (1 + markup) * 100) / 100;
    },
    
    installmentValue() {
      // Valor de cada parcela - sempre calcular localmente
      return Math.round((this.installmentTotal / this.config.pricing_rules.installments) * 100) / 100;
    },
    
    priceSource() {
      return this.serverValidatedPricing?.source || 'local';
    },
    
    // Economia em R$ (diferença entre valor parcelado e à vista)
    savingsAmount() {
      // Diferença entre parcelado e à vista (taxa de 15%)
      return this.installmentTotal - this.cashPrice;
    },
    
    // Texto do CTA baseado na seleção
    ctaText() {
      if (this.paymentMethod === 'cash') {
        return `🎯 Assinar Plano Anual - ${this.formatPrice(this.cashPrice)}`;
      }
      return '🎯 Assinar Plano Anual (12x sem juros)';
    },
    
    // Preço diário para gatilho mental do café
    dailyPrice() {
      const annual = this.cashPrice;
      const daily = annual / 365;
      return daily.toFixed(2).replace('.', ',');
    },
    
    // Preço mensal equivalente (valor com taxa de 15% do cartão)
    monthlyEquivalent() {
      // Usa o installmentValue que já tem a taxa de 15% incluída
      return this.installmentValue.toFixed(2).replace('.', ',');
    },
    
    // Verificar se alguma página personalizada tem formulário
    hasFormResource() {
      return this.customPages.some(page => page.resources.form === true);
    },
    
    // Preços mensais das páginas personalizadas (com taxa de 15%)
    customPageBasePriceMonthly() {
      const annual = this.config.custom_pages?.base_price || 0;
      const withMarkup = annual * 1.15;
      return withMarkup / 12;
    },
    
    customPageResourcePrices() {
      const resources = this.config.custom_pages?.resources || {};
      const prices = {};
      
      Object.keys(resources).forEach(key => {
        const annual = resources[key].price || 0;
        const withMarkup = annual * 1.15;
        prices[key] = withMarkup / 12;
      });
      
      return prices;
    },
    
    // Verificar se pode prosseguir para próximo step
    canProceed() {
      if (this.currentStep === 1 && !this.initialProduct) {
        return !!this.selectedProduct;
      }
      if ((this.currentStep === 2 && !this.initialProduct) || (this.currentStep === 1 && this.initialProduct)) {
        return !!this.selectedProduct || this.selectedPages.length > 0 || this.customPages.length > 0;
      }
      return true;
    },
    
    // Verifica se estamos na etapa de checkout
    isCheckoutStep() {
      return this.currentStep === (this.initialProduct ? 2 : 3);
    },
    
    // Total de steps
    totalSteps() {
      return this.initialProduct ? 2 : 3;
    }
  },
  methods: {
    // ========== SCROLL FUNCTIONS ==========
    
    scrollToOrderSummary() {
      const orderSummary = document.querySelector('.order-summary-section');
      if (orderSummary) {
        orderSummary.scrollIntoView({ 
          behavior: 'smooth', 
          block: 'start' 
        });
      }
    },
    
    // ========== CONTROLE DE VÍDEOS ==========
    
    updateVideoBasicSelection() {
      if (!this.videoBasicSelected) {
        this.videoBasicQuantity = 1;
      }
    },
    
    updateVideoProSelection() {
      if (!this.videoProSelected) {
        this.videoProQuantity = 1;
      }
    },
    
    increaseVideoBasic() {
      if (this.videoBasicQuantity < 10) {
        this.videoBasicQuantity++;
      }
    },
    
    decreaseVideoBasic() {
      if (this.videoBasicQuantity > 1) {
        this.videoBasicQuantity--;
      }
    },
    
    increaseVideoPro() {
      if (this.videoProQuantity < 10) {
        this.videoProQuantity++;
      }
    },
    
    decreaseVideoPro() {
      if (this.videoProQuantity > 1) {
        this.videoProQuantity--;
      }
    },
    
    // ========== PACOTES PRÉ-DEFINIDOS ==========
    
    // Selecionar pacote pré-definido
    selectPackage(packageKey) {
      console.log('📦 [selectPackage] Selecionando pacote:', packageKey);
      
      const packageData = this.config.predefined_packages[packageKey];
      if (!packageData) {
        console.error('❌ [selectPackage] Pacote não encontrado:', packageKey);
        return;
      }
      
      this.selectedPackage = packageKey;
      this.selectedPages = [...packageData.pages];
      
      console.log('✅ [selectPackage] Páginas selecionadas:', this.selectedPages);
      this.scrollToTop();
    },
    
    // Verificar se o pacote ainda está correspondendo às seleções
    checkIfPackageStillMatches() {
      if (!this.selectedPackage) return;
      
      const packageData = this.config.predefined_packages[this.selectedPackage];
      if (!packageData) return;
      
      // Comparar arrays ordenados
      const currentPages = [...this.selectedPages].sort();
      const packagePages = [...packageData.pages].sort();
      
      const stillMatches = 
        currentPages.length === packagePages.length &&
        currentPages.every((page, index) => page === packagePages[index]);
      
      if (!stillMatches) {
        console.log('🔄 [checkIfPackageStillMatches] Pacote não corresponde mais. Mudando para personalizado.');
        this.selectedPackage = null;
      }
    },
    
    // Verificar se um pacote está selecionado
    isPackageSelected(packageKey) {
      return this.selectedPackage === packageKey;
    },
    
    // ========== NAVEGAÇÃO E UI ==========
    
    // Scroll para o topo suave
    scrollToTop() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    },
    
    // Navegação
    nextStep() {
      console.log('🔄 [nextStep] Current step:', this.currentStep);
      if (this.currentStep < 2) { // Agora temos apenas 2 steps: Personalize e Checkout
        this.currentStep++;
        console.log('✅ [nextStep] Moved to step:', this.currentStep);
        this.scrollToTop();
      } else {
        console.log('⚠️ [nextStep] Already at final step');
      }
    },
    
    previousStep() {
      console.log('🔄 [previousStep] Current step:', this.currentStep);
      if (this.currentStep > 1) {
        this.currentStep--;
        console.log('✅ [previousStep] Moved to step:', this.currentStep);
        this.scrollToTop();
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
      this.scrollToTop();
    },

    // Upgrade de Landing para Site Completo
    upgradeToComplete() {
      this.selectedProduct = 'site_complete';
      // Manter na etapa 2 para adicionar páginas
      this.scrollToTop();
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
          form: false,
          testimonials: false
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
      // Usar preços do config (valores anuais sem taxa)
      let total = this.config.custom_pages.base_price;
      if (page.resources.image) total += this.config.custom_pages.resources.images.price;
      if (page.resources.video) total += this.config.custom_pages.resources.video.price;
      if (page.resources.carousel) total += this.config.custom_pages.resources.carousel.price;
      if (page.resources.form) total += this.config.custom_pages.resources.form.price;
      if (page.resources.testimonials) total += this.config.custom_pages.resources.testimonials.price;
      return total;
    },
    
    // Upload handlers
    handleVideoUpload(event) {
      const file = event.target.files[0];
      if (!file) return;

      // Verificar se vídeo está selecionado
      const hasVideo = this.videoSelected;
      
      if (!hasVideo || !this.selectedVideoTier) {
        console.warn('⚠️ Plano de vídeo não selecionado');
        event.target.value = '';
        return;
      }

      // Usar limite do tier selecionado
      const maxSize = this.selectedVideoTier.max_file_size;
      const planName = this.selectedVideoTier.name;
      const maxSizeText = this.selectedVideoTier.max_file_size > 100000000 ? '1GB' : '50MB';

      // Calcular tamanho do arquivo em MB ou GB para exibição
      const fileSizeBytes = file.size;
      let fileSizeDisplay;
      
      if (fileSizeBytes >= 1024 * 1024 * 1024) {
        fileSizeDisplay = `${(fileSizeBytes / (1024 * 1024 * 1024)).toFixed(2)}GB`;
      } else {
        fileSizeDisplay = `${(fileSizeBytes / (1024 * 1024)).toFixed(1)}MB`;
      }

      // Validar tamanho ANTES de aceitar o arquivo
      if (file.size <= maxSize) {
        this.briefing.video_file = file;
        console.log(`✅ Vídeo aceito no plano ${planName}:`, file.name, `(${fileSizeDisplay})`);
        
        // Feedback positivo para o usuário
        console.log(`✅ Vídeo carregado: ${file.name} (${fileSizeDisplay}) - Plano: ${planName}`);
      } else {
        // Feedback detalhado sobre o problema
        console.error(`❌ Arquivo ${file.name} (${fileSizeDisplay}) excede limite do plano ${planName} (${maxSizeText})`);
        event.target.value = '';
      }
    },
    
    handlePdfUpload(event) {
      const files = Array.from(event.target.files);
      const validFiles = [];
      const errorMessages = [];
      
      for (const file of files) {
        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(1);
        
        if (file && file.size <= 15 * 1024 * 1024) { // 15MB
          validFiles.push(file);
        } else {
          errorMessages.push(`• ${file.name}: ${fileSizeMB}MB (limite: 15MB)`);
        }
      }
      
      if (validFiles.length > 0) {
        // Se já existem arquivos, adiciona aos existentes
        if (!this.briefing.pdf_files) {
          this.briefing.pdf_files = [];
        }
        this.briefing.pdf_files = [...this.briefing.pdf_files, ...validFiles];
        
        // Feedback positivo
        const successMsg = `✅ ${validFiles.length} PDF(s) carregado(s) com sucesso!`;
        console.log(successMsg, validFiles.map(f => f.name));
      }
      
      if (errorMessages.length > 0) {
        console.error(`⚠️ Alguns arquivos PDF excederam o limite:\n\n${errorMessages.join('\n')}\n\n📊 Limite por arquivo: 15MB\n📁 Arquivos válidos foram adicionados`);
        event.target.value = '';
      }
    },

    handleImageUpload(event) {
      const files = Array.from(event.target.files);
      const validFiles = [];
      const errorMessages = [];
      
      for (const file of files) {
        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(1);
        
        if (file && file.size <= 3 * 1024 * 1024) { // 3MB
          validFiles.push(file);
        } else {
          errorMessages.push(`• ${file.name}: ${fileSizeMB}MB (limite: 3MB)`);
        }
      }
      
      if (validFiles.length > 0) {
        // Se já existem arquivos, adiciona aos existentes
        if (!this.briefing.image_files) {
          this.briefing.image_files = [];
        }
        this.briefing.image_files = [...this.briefing.image_files, ...validFiles];
        
        // Feedback positivo
        const successMsg = `✅ ${validFiles.length} imagem(ns) carregada(s) com sucesso!`;
        console.log(successMsg, validFiles.map(f => f.name));
      }
      
      if (errorMessages.length > 0) {
        alert(`⚠️ Algumas imagens excederam o limite:\n\n${errorMessages.join('\n')}\n\n📊 Limite por arquivo: 3MB\n📁 Imagens válidas foram adicionadas`);
        event.target.value = '';
      }
    },
    
    // Máscaras de formatação
    formatWhatsApp(value) {
      // Remove tudo que não é dígito
      let cleaned = value.replace(/\D/g, '');
      
      // Limita a 11 dígitos (máximo para celular brasileiro)
      cleaned = cleaned.substring(0, 11);
      
      // Aplica a máscara
      if (cleaned.length <= 2) {
        return cleaned;
      } else if (cleaned.length <= 7) {
        return `(${cleaned.slice(0, 2)}) ${cleaned.slice(2)}`;
      } else {
        return `(${cleaned.slice(0, 2)}) ${cleaned.slice(2, 7)}-${cleaned.slice(7)}`;
      }
    },
    
    formatDocument(value) {
      // Remove tudo que não é dígito
      let cleaned = value.replace(/\D/g, '');
      
      // Limita a 14 dígitos (máximo para CNPJ)
      cleaned = cleaned.substring(0, 14);
      
      // Aplica máscara de CPF (11 dígitos) ou CNPJ (14 dígitos)
      if (cleaned.length <= 11) {
        // Máscara CPF: 000.000.000-00
        if (cleaned.length <= 3) {
          return cleaned;
        } else if (cleaned.length <= 6) {
          return `${cleaned.slice(0, 3)}.${cleaned.slice(3)}`;
        } else if (cleaned.length <= 9) {
          return `${cleaned.slice(0, 3)}.${cleaned.slice(3, 6)}.${cleaned.slice(6)}`;
        } else {
          return `${cleaned.slice(0, 3)}.${cleaned.slice(3, 6)}.${cleaned.slice(6, 9)}-${cleaned.slice(9)}`;
        }
      } else {
        // Máscara CNPJ: 00.000.000/0000-00
        if (cleaned.length <= 2) {
          return cleaned;
        } else if (cleaned.length <= 5) {
          return `${cleaned.slice(0, 2)}.${cleaned.slice(2)}`;
        } else if (cleaned.length <= 8) {
          return `${cleaned.slice(0, 2)}.${cleaned.slice(2, 5)}.${cleaned.slice(5)}`;
        } else if (cleaned.length <= 12) {
          return `${cleaned.slice(0, 2)}.${cleaned.slice(2, 5)}.${cleaned.slice(5, 8)}/${cleaned.slice(8)}`;
        } else {
          return `${cleaned.slice(0, 2)}.${cleaned.slice(2, 5)}.${cleaned.slice(5, 8)}/${cleaned.slice(8, 12)}-${cleaned.slice(12)}`;
        }
      }
    },
    
    handleWhatsAppInput(field) {
      // field pode ser 'briefing' ou 'customRequest'
      const rawValue = this[field].whatsapp;
      this[field].whatsapp = this.formatWhatsApp(rawValue);
    },
    
    handleDocumentInput() {
      const rawValue = this.briefing.document;
      this.briefing.document = this.formatDocument(rawValue);
    },
    
    // Toggle para selecionar/desselecionar todas as páginas
    toggleAllPages(event) {
      if (!this.config.page_addons) return;
      
      const allPageKeys = Object.keys(this.config.page_addons);
      
      if (event.target.checked) {
        // Selecionar todas
        this.selectedPages = [...allPageKeys];
      } else {
        // Desselecionar todas
        this.selectedPages = [];
      }
      
      console.log('⚡ [toggleAllPages] Todas as páginas:', event.target.checked ? 'selecionadas' : 'desmarcadas');
    },
    
    // Validação de formulário
    validateCheckoutForm() {
      const errors = [];
      
      // Validar nome
      if (!this.briefing.customer_name || this.briefing.customer_name.trim().length < 3) {
        errors.push('Nome completo é obrigatório (mínimo 3 caracteres)');
      }
      
      // Validar email
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!this.briefing.email || !emailRegex.test(this.briefing.email)) {
        errors.push('E-mail válido é obrigatório');
      }
      
      // Validar WhatsApp (pelo menos 10 dígitos)
      const whatsappDigits = this.briefing.whatsapp.replace(/\D/g, '');
      if (!this.briefing.whatsapp || whatsappDigits.length < 10) {
        errors.push('WhatsApp válido é obrigatório (mínimo 10 dígitos)');
      }
      
      // Validar CPF/CNPJ se preenchido
      if (this.briefing.document && this.briefing.document.trim()) {
        const docDigits = this.briefing.document.replace(/\D/g, '');
        if (docDigits.length !== 11 && docDigits.length !== 14) {
          errors.push('CPF deve ter 11 dígitos ou CNPJ deve ter 14 dígitos');
        }
      }
      
      return errors;
    },
    
    // Validar preço no servidor (background)
    async validatePriceOnServer() {
      if (!this.selectedProduct) return;
      
      this.isValidating = true;
      
      // Debounce (evitar muitas chamadas)
      clearTimeout(this._validateTimeout);
      this._validateTimeout = setTimeout(async () => {
        // Converter pages de array para objeto com quantidades
        const pagesAsObject = {};
        this.selectedPages.forEach(pageKey => {
          pagesAsObject[pageKey] = 1; // Cada página selecionada tem quantidade 1
        });
        
        const selection = {
          product: this.selectedProduct,
          pages: pagesAsObject, // Enviando como objeto
          content: this.selectedContentAddons,
          custom_pages: this.customPages,
          video_basic_quantity: this.videoBasicQuantity,
          video_pro_quantity: this.videoProQuantity
        };
        
        // Log COMPLETO do que está sendo enviado
        console.log('📤 [validatePriceOnServer] ========== ENVIANDO PARA BACKEND ==========');
        console.log('📦 SELEÇÃO:', JSON.parse(JSON.stringify(selection)));
        console.log('💰 CÁLCULO LOCAL:', {
          basePrice: this.basePrice,
          pagesTotal: this.pagesTotal,
          contentTotal: this.contentTotal,
          subtotal: this.subtotal,
          cashPrice: this.cashPrice,
          installmentTotal: this.installmentTotal,
          paymentMethod: this.paymentMethod
        });
        console.log('📋 DETALHES:', {
          product: selection.product,
          pages: selection.pages, // Já é objeto, não precisa spread
          content: [...selection.content],
          custom_pages_count: selection.custom_pages.length,
          video_basic_qty: selection.video_basic_quantity,
          video_pro_qty: selection.video_pro_quantity
        });
        console.log('====================================================');
        
        const result = await PricingService.calculatePrice(selection, this.config);
        
        console.log('📥 [validatePriceOnServer] RECEBIDO do backend:', {
          serverValidated: result.serverValidated,
          pricing: result.pricing,
          normalized: result.normalized
        });
        
        if (result.serverValidated) {
          console.log('✅ Servidor validou preços:', result.pricing);
          
          // Armazenar preços validados para uso no checkout
          // MAS NÃO alterar as seleções do usuário!
          this.serverValidatedPricing = null;
          
          this.$nextTick(() => {
            this.serverValidatedPricing = {
              ...result.pricing,
              _timestamp: Date.now()
            };
            this.serverAvailable = true;
            
            console.log('💰 [validatePriceOnServer] Preços validados (sem alterar seleções):', {
              subtotal: this.serverValidatedPricing.subtotal,
              avista: this.serverValidatedPricing.avista,
              parcelado: this.serverValidatedPricing.parcelado_total
            });
          });
          
          // REMOVIDO: Não aplicar "normalização" do servidor que sobrescreve escolhas do usuário
          // O servidor só valida preços, não muda seleções!
        } else {
          console.log('⚠️ Usando cálculo local:', result.pricing);
          
          // SOLUÇÃO: Também resetar para null primeiro
          this.serverValidatedPricing = null;
          
          this.$nextTick(() => {
            // Marcar explicitamente como local para forçar reatividade
            this.serverValidatedPricing = {
              ...result.pricing,
              source: 'local',
              _timestamp: Date.now() // Força reatividade
            };
            this.serverAvailable = false;
            
            console.log('💰 [validatePriceOnServer] Cálculo local:', {
              subtotal: this.serverValidatedPricing.subtotal,
              avista: this.serverValidatedPricing.avista,
              parcelado: this.serverValidatedPricing.parcelado_total
            });
          });
        }
        
        this.isValidating = false;
      }, 500); // 500ms debounce
    },
    
    // Submit final
    async submitOrder() {
      // Validar formulário antes de enviar
      const validationErrors = this.validateCheckoutForm();
      
      if (validationErrors.length > 0) {
        this.formErrors = validationErrors;
        // Scroll para o topo para ver os erros
        this.scrollToTop();
        return;
      }
      
      // Limpar erros se validação passou
      this.formErrors = [];
      
      this.isSubmitting = true;
      
      try {
        // VALIDAR preços no servidor antes de enviar
        console.log('🔒 [submitOrder] Validando preços no servidor antes do checkout...');
        
        // Converter pages de array para objeto com quantidades
        const pagesAsObject = {};
        this.selectedPages.forEach(pageKey => {
          pagesAsObject[pageKey] = 1; // Cada página selecionada tem quantidade 1
        });
        
        const selection = {
          product: this.selectedProduct,
          pages: pagesAsObject, // Enviando como objeto
          content: this.selectedContentAddons,
          custom_pages: this.customPages,
          video_basic_quantity: this.videoBasicQuantity,
          video_pro_quantity: this.videoProQuantity
        };
        
        console.log('📤 [submitOrder] ========== VALIDAÇÃO NO SERVIDOR ==========');
        console.log('📦 Enviando:', JSON.parse(JSON.stringify(selection)));
        console.log('� DETALHAMENTO DO QUE ESTÁ SENDO ENVIADO:');
        console.log('  - product:', selection.product);
        console.log('  - pages (objeto):', selection.pages);
        console.log('  - content (array):', [...selection.content]);
        console.log('  - custom_pages:', selection.custom_pages);
        console.log('  - video_basic_quantity:', selection.video_basic_quantity);
        console.log('  - video_pro_quantity:', selection.video_pro_quantity);
        console.log('💰 Frontend calculou:', {
          subtotal: this.subtotal,
          cashPrice: this.cashPrice,
          installmentTotal: this.installmentTotal,
          paymentMethod: this.paymentMethod
        });
        
        const validationResult = await PricingService.calculatePrice(selection, this.config);
        
        console.log('📥 Backend retornou:', validationResult.pricing);
        
        // Verificar se há discrepância entre frontend e backend
        if (validationResult.serverValidated) {
          // Comparar com o preço correto baseado no método de pagamento
          const serverPrice = this.paymentMethod === 'cash' 
            ? validationResult.pricing.avista 
            : validationResult.pricing.parcelado_total;
          const localPrice = this.paymentMethod === 'cash' 
            ? this.cashPrice 
            : this.installmentTotal;
          const diff = Math.abs(serverPrice - localPrice);
          
          console.log('🔍 [submitOrder] Comparando preços:', {
            paymentMethod: this.paymentMethod,
            serverPrice: serverPrice,
            localPrice: localPrice,
            diff: diff,
            serverAvista: validationResult.pricing.avista,
            serverParcelado: validationResult.pricing.parcelado_total,
            localCash: this.cashPrice,
            localInstallment: this.installmentTotal
          });
          
          if (diff > 0.01) {
            console.error('❌ [submitOrder] DISCREPÂNCIA DE PREÇO detectada!');
            console.error('Método de pagamento:', this.paymentMethod);
            console.error('Frontend:', localPrice);
            console.error('Backend:', serverPrice);
            console.error('Diferença:', diff);
            const methodText = this.paymentMethod === 'cash' ? 'à vista' : 'parcelado';
            alert(`⚠️ Detectamos uma diferença de preço (${methodText}).\n\nFrontend: R$ ${localPrice.toFixed(2).replace('.', ',')}\nBackend: R$ ${serverPrice.toFixed(2).replace('.', ',')}\nDiferença: R$ ${diff.toFixed(2).replace('.', ',')}\n\nPor favor, revise seu pedido.`);
            this.isSubmitting = false;
            return;
          }
          
          console.log('✅ [submitOrder] Preços validados - Frontend e Backend estão sincronizados');
        }
        console.log('====================================================');
        
        // Montar dados do pedido (sem incluir preço - servidor calcula)
        const orderData = {
          product: this.selectedProduct,
          pages: pagesAsObject, // Usar o mesmo objeto que foi validado
          content: this.selectedContentAddons,
          custom_pages: this.customPages,
          video_basic_quantity: this.videoBasicQuantity,
          video_pro_quantity: this.videoProQuantity,
          briefing: this.briefing,
          payment_method: this.paymentMethod === 'cash' ? 'avista' : 'prazo'
        };
        
        // Tentar criar pedido no servidor
        const result = await PricingService.createOrder(orderData);
        
        console.log('📦 [submitOrder] Resultado do pedido:', result);
        
        if (result.ok) {
          // Sucesso: pedido criado no servidor
          console.log('✅ Pedido criado:', result.order_id);
          
          // Criar link de pagamento no Pagar.me
          console.log('💳 Criando link Pagar.me para pedido:', result.order_id);
          
          try {
            // === CONSISTÊNCIA: Dados serão carregados da ordem salva no servidor ===
            // Não precisamos enviar seleção - garantimos que os dados sejam exatamente 
            // os mesmos que foram validados e salvos na criação da ordem
            
            const preferenceData = {
              order_id: result.order_id,
              payer_name: this.briefing.customer_name || 'Cliente',
              payer_email: this.briefing.email || 'cliente@exemplo.com',
              payer_phone: (this.briefing.whatsapp || '').replace(/\D/g, ''),
              payer_document: (this.briefing.document || '').replace(/\D/g, ''),
              payment_type: this.paymentMethod === 'cash' ? 'avista' : 'prazo'
              // SEGURANÇA: selection será carregada da ordem salva para garantir consistência total
            };
            
            console.log('📦 Dados da preferência (sem seleção - vem da ordem):', preferenceData);
            
            const response = await fetch('/api/create_preference.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
              },
              body: JSON.stringify(preferenceData)
            });
            
            const preferenceResult = await response.json();
            
            console.log('📦 [submitOrder] Resultado da preferência:', preferenceResult);
            
            if (preferenceResult.success && (preferenceResult.payment_url || preferenceResult.init_point)) {
              const checkoutUrl = preferenceResult.payment_url || preferenceResult.init_point;
              console.log('✅ Link Pagar.me criado, emitindo evento para redirecionamento:', checkoutUrl);
              
              // Emitir evento COM o payment_url para o ConfiguradorPage fazer o redirecionamento
              this.$emit('order-submitted', {
                ...result,
                payment_url: checkoutUrl,
                init_point: checkoutUrl, // compatibilidade
                pagarme_order_id: preferenceResult.pagarme_order_id,
                local_pricing: {
                  subtotal: this.subtotal,
                  cash_price: this.cashPrice,
                  installment_total: this.installmentTotal
                }
              });
            } else {
              console.error('🔴 Erro ao criar link Pagar.me:', preferenceResult);
              // Emitir evento sem payment_url em caso de erro
              this.$emit('order-submitted', {
                ...result,
                preference_error: true,
                error_message: preferenceResult.message || 'Erro ao criar preferência',
                local_pricing: {
                  subtotal: this.subtotal,
                  cash_price: this.cashPrice,
                  installment_total: this.installmentTotal
                }
              });
            }
          } catch (error) {
            console.error('🔴 Erro na criação da preferência:', error);
            // Emitir evento com erro
            this.$emit('order-submitted', {
              ...result,
              preference_error: true,
              error_message: 'Erro de conexão ao criar preferência: ' + error.message,
              local_pricing: {
                subtotal: this.subtotal,
                cash_price: this.cashPrice,
                installment_total: this.installmentTotal
              }
            });
          }
        } else if (result.offline) {
          // API offline: verificar se é modo debug ou erro real
          const debugMode = this.$store.state.ConfigModule?.debug;
          
          if (debugMode) {
            // DEBUG MODE: permitir pedido mock
            console.warn('🔧 DEBUG MODE - pedido simulado:', result.mockData);
            
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
            console.info('🔧 DEBUG MODE: Pedido simulado localmente');
          } else {
            // PRODUÇÃO: API offline após retry. Mostrar erro amigável sem bloquear totalmente.
            console.error('🚨 API offline em produção após retry');
            console.error('🚨 Checkout não pôde ser completado');
            
            // Mostrar mensagem ao usuário em vez de throw silencioso
            this.systemError = '⚠️ Nosso servidor está temporariamente indisponível. Por favor, aguarde alguns segundos e tente novamente.';
            
            // Scroll para o topo para o usuário ver a mensagem
            this.scrollToTop();
          }
        } else {
          // Erro no servidor
          throw new Error(result.error || 'Erro desconhecido');
        }
      } catch (error) {
        console.error('🔴 Erro ao enviar pedido:', error);
        console.error('🔴 Falha no checkout, verifique os logs acima');
        
        // Mostrar erro para o usuário na UI
        this.systemError = '❌ Erro ao processar pedido: ' + (error.message || 'Erro desconhecido. Tente novamente.');
        console.error('❌ Erro ao processar pedido: ' + error.message);
        
        // Scroll para o topo para o usuário ver a mensagem
        this.scrollToTop();
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
          console.log('✅ Solicitação personalizada enviada:', result.message);
          this.closeCustomForm();
        } else if (result.offline) {
          console.error('⚠️ API offline\n\nEntre em contato via WhatsApp: (11) 99999-9999');
        }
      } catch (error) {
        console.error('🔴 Erro:', error);
        console.error('Erro ao enviar solicitação.');
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
    
    getOriginalPrice() {
      // Calcula o preço original antes do desconto (para ancoragem)
      // Usa o installmentTotal como base (sem desconto)
      return this.installmentTotal;
    },
    
    getTotalSavings() {
      // Economia total ao escolher Pix vs preço original
      return this.getOriginalPrice() - this.cashPrice;
    },
    
    getPixExtraSavings() {
      // Economia extra do Pix vs Cartão
      return this.installmentTotal - this.cashPrice;
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
        video: 'Sistema de upload de vídeos com opções de quantidade. De 1 vídeo básico (50MB) até 5 vídeos profissionais (1GB cada). Suporta também incorporação via YouTube/Vimeo.',
        pdf: 'Habilita o envio de múltiplos arquivos PDF para seus clientes baixarem. Ideal para disponibilizar cardápios, catálogos e tabelas. Sem isso, o site aceita apenas imagens e textos. Limite: máximo 15MB por arquivo, múltiplos arquivos permitidos.'
      };
      return tooltips[key] || 'Recurso adicional';
    }
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/__colors.scss';

// Global responsive fixes
* {
  box-sizing: border-box;
}

// Prevent horizontal overflow mas permitir sticky
html, body {
  overflow-x: hidden;
  max-width: 100vw;
}

// Garantir que o viewport permita sticky
html {
  height: 100%;
}

body {
  min-height: 100%;
  position: relative;
}

// Base responsive utilities
.configurator-container {
  width: 100%;
  max-width: 100vw;
  overflow-x: hidden;
}

// Font size adjustments for very small screens
@media (max-width: 320px) {
  * {
    font-size: 12px !important;
  }
  
  h1, h2, h3, h4, h5, h6 {
    font-size: 14px !important;
    line-height: 1.2 !important;
  }
  
  button {
    font-size: 12px !important;
    padding: 8px 12px !important;
  }
}

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

// Container principal - Mobile First
.site-configurator {
  position: relative;
  display: block;
  width: 100%;
  max-width: 100vw;
  margin: 0 auto;
  padding: 15px 10px 120px 10px; // Espaço para mobile price bar
  min-height: 100vh;

  // Garantir que todos os filhos não causem overflow
  * {
    box-sizing: border-box;
    max-width: 100%;
    overflow-wrap: break-word;
  }

  // Tablets
  @media (min-width: 768px) {
    padding: 20px 15px;
  }

  // Desktop
  @media (min-width: 1024px) {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 40px;
    max-width: 1400px;
    padding: 40px 20px;
    align-items: start; // Importante para o sticky funcionar
    min-height: 150vh; // Altura suficiente para criar scroll
    overflow: visible; // Garantir que sticky funcione
  }

  @media (min-width: 1200px) {
    padding: 40px 30px;
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

.configurator-body {
  min-height: calc(100vh - 200px);
  width: 100%;

  @media (min-width: 1024px) {
    min-height: 200vh; // Altura extra para garantir scroll
    width: 100%;
  }
}

// Progress Bar
.progress-bar {
  grid-column: 1 / -1;
  display: flex;
  justify-content: center;
  gap: 16px;
  margin-bottom: 20px;
  padding: 0 8px;
  overflow-x: auto;
  max-width: 100vw;

  @media (min-width: 480px) {
    gap: 24px;
    margin-bottom: 24px;
    padding: 0 10px;
  }

  @media (min-width: 768px) {
    gap: 40px;
    margin-bottom: 40px;
    padding: 0;
    overflow-x: visible;
  }

  @media (min-width: 1024px) {
    grid-column: 1 / -1;
  }

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
      width: 36px;
      height: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      background: $gray-light;
      color: $gray-medium;
      font-weight: 700;
      font-size: 0.9rem;
      transition: all 0.3s;

      @media (min-width: 480px) {
        width: 40px;
        height: 40px;
        font-size: 1rem;
      }

      @media (min-width: 768px) {
        width: 48px;
        height: 48px;
        font-size: 1.2rem;
      }
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
  width: 100%;
  max-width: 100%;

  @media (max-width: 768px) {
    min-height: 400px;
  }
}

.step-content {
  animation: fadeIn 0.3s;
  width: 100%;
  max-width: 100%;
  word-wrap: break-word;
  overflow-wrap: break-word;
}

.step-title {
  font-size: 1.4rem;
  font-weight: 800;
  color: $gray-darkness;
  margin-bottom: 8px;
  line-height: 1.2;
  word-wrap: break-word;

  @media (min-width: 480px) {
    font-size: 1.6rem;
    margin-bottom: 10px;
  }

  @media (min-width: 768px) {
    font-size: 2rem;
    margin-bottom: 12px;
  }
}

.step-description {
  font-size: 0.95rem;
  color: $gray-medium;
  margin-bottom: 20px;
  line-height: 1.5;
  word-wrap: break-word;

  @media (min-width: 480px) {
    font-size: 1rem;
    margin-bottom: 24px;
  }

  @media (min-width: 768px) {
    font-size: 1.1rem;
    margin-bottom: 40px;
  }
}

// Etapa 1: Escolha de Produto (oculta quando initialProduct existe)
.step-product {
  .product-cards {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
    margin-bottom: 20px;

    @media (min-width: 480px) {
      gap: 20px;
      margin-bottom: 24px;
    }

    @media (min-width: 768px) {
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 24px;
      margin-bottom: 40px;
    }

    @media (min-width: 1024px) {
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    }
  }

  .product-card {
    padding: 20px;
    background: $white;
    border: 3px solid $gray-light;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
    width: 100%;
    max-width: 100%;

    @media (min-width: 480px) {
      padding: 24px;
      border-radius: 16px;
    }

    @media (min-width: 768px) {
      padding: 32px;
      border-radius: 20px;
    }
    
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

// ========== PACOTES PRÉ-DEFINIDOS ==========
.packages-section {
  margin-bottom: 50px;

  .section-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1.5rem;
    font-weight: 700;
    color: $gray-darkness;
    margin-bottom: 12px;

    i {
      color: $p-color;
    }
  }

  .section-subtitle {
    color: $gray-medium;
    margin-bottom: 32px;
    font-size: 1rem;
    line-height: 1.6;
  }

  .package-cards {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
    margin-bottom: 20px;

    @media (min-width: 480px) {
      gap: 20px;
      margin-bottom: 24px;
    }

    @media (min-width: 768px) {
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 24px;
    }

    @media (min-width: 1024px) {
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    }
  }

  .package-card {
    position: relative;
    padding: 20px 16px;
    background: $white;
    border: 3px solid $gray-light;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    width: 100%;
    max-width: 100%;

    @media (min-width: 480px) {
      padding: 24px 20px;
      border-radius: 16px;
    }

    @media (min-width: 768px) {
      padding: 32px 24px;
      border-radius: 20px;
    }

    &::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, $gray-light, $gray-medium);
      transition: all 0.3s;
    }

    &:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
      border-color: rgba($p-color, 0.3);

      &::before {
        background: $gradient-primary;
      }
    }

    &.selected {
      border-color: $p-color;
      background: linear-gradient(135deg, rgba($p-color, 0.03) 0%, rgba($p-dark, 0.01) 100%);
      box-shadow: 0 8px 32px rgba($p-color, 0.2);

      &::before {
        background: $gradient-primary;
        height: 6px;
      }

      .package-icon {
        transform: scale(1.1);
      }
    }

    // Pacote Recomendado
    &.recommended {
      border-color: $p-color;

      &::before {
        background: linear-gradient(90deg, $p-color, $p-dark);
      }

      &:hover::before {
        background: linear-gradient(90deg, $p-color, $p-dark);
      }

      &.selected {
        border-color: $p-color;
        background: linear-gradient(135deg, rgba($p-color, 0.05) 0%, rgba($p-color, 0.02) 100%);
        box-shadow: 0 8px 32px rgba($p-color, 0.25);

        &::before {
          background: linear-gradient(90deg, $p-color, $p-dark);
        }
      }

      .recommended-badge {
        position: absolute;
        top: 16px;
        right: 16px;
        background: linear-gradient(135deg, $p-color 0%, $p-dark 100%);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 6px;
        animation: pulse 2s ease-in-out infinite;

        i {
          font-size: 0.8rem;
        }
      }
    }

    // Pacote Premium
    &.premium {
      border-color: #6b46c1;

      &::before {
        background: linear-gradient(90deg, #6b46c1, #9333ea);
      }

      &:hover::before {
        background: linear-gradient(90deg, #6b46c1, #9333ea);
      }

      &.selected {
        border-color: #6b46c1;
        background: linear-gradient(135deg, rgba(107, 70, 193, 0.05) 0%, rgba(147, 51, 234, 0.02) 100%);
        box-shadow: 0 8px 32px rgba(107, 70, 193, 0.25);

        &::before {
          background: linear-gradient(90deg, #6b46c1, #9333ea);
        }
      }

      .premium-shine {
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
        animation: shine 3s infinite;
      }

      @keyframes shine {
        0% {
          transform: translateX(-100%) translateY(-100%) rotate(45deg);
        }
        100% {
          transform: translateX(100%) translateY(100%) rotate(45deg);
        }
      }
    }

    .package-icon {
      font-size: 3rem;
      margin-bottom: 16px;
      transition: transform 0.3s;
    }

    .package-name {
      font-size: 1.4rem;
      font-weight: 800;
      color: $gray-darkness;
      margin-bottom: 8px;
    }

    .package-tagline {
      font-size: 0.95rem;
      color: $gray-medium;
      line-height: 1.5;
      margin-bottom: 24px;
      min-height: 48px;
    }

    .package-includes {
      margin-bottom: 20px;
      padding: 16px;
      background: rgba($p-color, 0.03);
      border-radius: 12px;

      .includes-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: $gray-darkness;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
      }

      ul {
        list-style: none;
        padding: 0;
        margin: 0;

        li {
          display: flex;
          align-items: center;
          gap: 10px;
          font-size: 0.95rem;
          color: $gray-medium;
          margin-bottom: 8px;

          &:last-child {
            margin-bottom: 0;
          }

          i {
            color: $p-color;
            font-size: 0.8rem;
          }

          .new-badge {
            display: inline-block;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-left: 6px;
          }
        }
      }
    }

    .package-ideal {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px;
      background: rgba($accent-blue, 0.05);
      border-radius: 10px;
      font-size: 0.85rem;
      color: $gray-medium;
      line-height: 1.4;

      i {
        color: $accent-blue;
        flex-shrink: 0;
      }

      span {
        font-weight: 500;
      }
    }
  }

  .custom-notice {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 24px 20px;
    background: transparent;
    border: none;
    font-size: 0.95rem;
    color: $gray-medium;
    font-weight: 500;
    text-align: center;

    i {
      font-size: 1.5rem;
      color: $gray-light;
      animation: bounce 2s ease-in-out infinite;
    }

    @keyframes bounce {
      0%, 100% {
        transform: translateY(0);
      }
      50% {
        transform: translateY(-8px);
      }
    }

    span {
      color: $gray-darkness;
      font-weight: 500;
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

// Toggle Switch "Estrutura Profissional Completa"
.select-all-wrapper {
  margin-bottom: 20px;
  
  .toggle-select-all {
    cursor: pointer;
    display: block;
    
    input[type="checkbox"] {
      display: none;
    }
    
    .toggle-content {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 20px 24px;
      background: linear-gradient(135deg, #fff8e1 0%, #fffbf0 100%);
      border: 3px solid #ffa726;
      border-radius: 16px;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      
      &::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        transition: left 0.6s;
      }
      
      &:hover {
        border-color: #ff9800;
        box-shadow: 0 8px 24px rgba(255, 152, 0, 0.25);
        transform: translateY(-2px);
        
        &::before {
          left: 100%;
        }
      }
      
      > i:first-child {
        font-size: 1.8rem;
        color: #ff9800;
        flex-shrink: 0;
        filter: drop-shadow(0 2px 4px rgba(255, 152, 0, 0.3));
      }
      
      .toggle-text {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
        
        strong {
          font-size: 1.15rem;
          color: $gray-darkness;
          font-weight: 800;
          letter-spacing: -0.01em;
        }
        
        span {
          font-size: 0.9rem;
          color: $gray-medium;
          line-height: 1.4;
        }
      }
      
      .toggle-badge {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
        color: white;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(255, 152, 0, 0.3);
        white-space: nowrap;
        
        i {
          font-size: 0.9rem;
        }
      }
      
      .toggle-switch {
        width: 56px;
        height: 30px;
        background: rgba(255, 255, 255, 0.6);
        border: 2px solid #ffa726;
        border-radius: 30px;
        position: relative;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        flex-shrink: 0;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        
        .toggle-slider {
          position: absolute;
          top: 2px;
          left: 2px;
          width: 22px;
          height: 22px;
          background: white;
          border-radius: 50%;
          transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
          box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }
      }
    }
    
    input[type="checkbox"]:checked + .toggle-content {
      background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
      border-color: #ff9800;
      box-shadow: 0 8px 24px rgba(255, 152, 0, 0.3);
      
      .toggle-switch {
        background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
        border-color: #ff9800;
        box-shadow: 0 2px 8px rgba(255, 152, 0, 0.4);
        
        .toggle-slider {
          left: 28px;
          background: white;
          box-shadow: 0 3px 10px rgba(255, 152, 0, 0.5);
        }
      }
    }
  }
}

// Checkboxes para páginas pré-definidas
.addon-checkboxes {
  display: grid;
  gap: 8px;

  @media (min-width: 480px) {
    gap: 10px;
  }

  @media (min-width: 768px) {
    gap: 12px;
  }
}

.checkbox-item {
  cursor: pointer;

  input[type="checkbox"] {
    display: none;
  }

  &.premium-item .checkbox-content {
    border-color: rgba(107, 70, 193, 0.3);
    background: linear-gradient(135deg, rgba(107, 70, 193, 0.02) 0%, rgba(147, 51, 234, 0.01) 100%);

    &:hover {
      border-color: rgba(107, 70, 193, 0.5);
      box-shadow: 0 4px 16px rgba(107, 70, 193, 0.15);
    }
  }

  &.premium-item input[type="checkbox"]:checked + .checkbox-content {
    border-color: #6b46c1;
    background: linear-gradient(135deg, rgba(107, 70, 193, 0.08) 0%, rgba(147, 51, 234, 0.04) 100%);

    .checkbox-mark {
      background: linear-gradient(135deg, #6b46c1 0%, #9333ea 100%);
      border-color: #6b46c1;
    }
  }

  .checkbox-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: $white;
    border: 2px solid $gray-light;
    border-radius: 8px;
    transition: all 0.3s;
    position: relative;
    width: 100%;
    max-width: 100%;
    
    @media (min-width: 480px) {
      padding: 14px 18px;
      border-radius: 10px;
    }

    @media (min-width: 768px) {
      padding: 16px 20px;
      border-radius: 12px;
    }
    
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

    .new-tag {
      position: absolute;
      top: -8px;
      left: 16px;
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: white;
      padding: 4px 10px;
      border-radius: 10px;
      font-size: 0.7rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.3px;
      box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
      display: flex;
      align-items: center;
      gap: 4px;
      animation: glow 2s ease-in-out infinite;

      i {
        font-size: 0.75rem;
      }
    }

    @keyframes glow {
      0%, 100% {
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
      }
      50% {
        box-shadow: 0 4px 16px rgba(16, 185, 129, 0.6);
      }
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
    max-width: 90vw;

    @media (max-width: 768px) {
      width: 200px;
      font-size: 0.8rem;
      padding: 8px 12px;
      max-width: 80vw;
    }

    @media (max-width: 768px) {
      width: 200px;
      font-size: 0.8rem;
      padding: 8px 12px;
      max-width: 80vw;
    }

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

    @media (max-width: 768px) {
      min-width: 100%;
      margin-bottom: 8px;
    }
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

    .description-helper {
      background: linear-gradient(135deg, rgba($accent-blue, 0.05) 0%, rgba($accent-blue, 0.02) 100%);
      border-left: 3px solid $accent-blue;
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 16px;
      font-size: 0.9rem;
      line-height: 1.6;
      color: $gray-darkness;

      strong {
        color: $accent-blue;
        font-weight: 600;
      }

      em {
        display: block;
        margin-top: 8px;
        font-size: 0.85rem;
        color: $gray-medium;
        font-style: normal;
      }
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
      height: 300px;

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

  .custom-page-note {
    display: flex;
    gap: 12px;
    padding: 16px;
    background: linear-gradient(135deg, rgba($accent-blue, 0.08) 0%, rgba($accent-blue, 0.04) 100%);
    border: 2px solid rgba($accent-blue, 0.2);
    border-radius: 12px;
    margin-bottom: 20px;

    > i {
      color: $accent-blue;
      font-size: 1.2rem;
      flex-shrink: 0;
      margin-top: 2px;
    }

    .note-content {
      flex: 1;

      strong {
        display: block;
        color: $accent-blue;
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 6px;
      }

      p {
        margin: 0;
        font-size: 0.85rem;
        color: $gray-darkness;
        line-height: 1.5;

        strong {
          display: inline;
          color: $p-color;
          font-weight: 600;
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

    @media (max-width: 768px) {
      padding: 15px;
      border-radius: 8px;
    }

    &:has(input:checked) {
      border-color: $p-color;
      background: rgba($p-color, 0.05);
    }
    
    // Estilos específicos para vídeo addon
    &.video-addon {
      padding: 0;
      cursor: default;
      
      .video-toggle {
        display: block;
        padding: 20px;
        cursor: pointer;

        @media (max-width: 768px) {
          padding: 15px;
        }
      }
      
      &:has(.video-toggle input:checked) {
        border-color: $p-color;
        background: rgba($p-color, 0.05);
      }
    }

    input[type="checkbox"] {
      display: none;
    }

    .toggle-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;

      @media (max-width: 768px) {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
      }

      .toggle-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
        flex: 1;
        min-width: 0;

        @media (max-width: 768px) {
          width: 100%;
        }

        .toggle-name {
          font-weight: 600;
          color: $gray-darkness;
          display: flex;
          align-items: center;
          gap: 8px;
          flex-wrap: wrap;
          word-wrap: break-word;
          max-width: 100%;
          
          .promo-inline-badge {
            display: inline-flex;
            padding: 2px 6px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            border-radius: 6px;
            font-size: 0.65rem;
            font-weight: 700;
            white-space: nowrap;
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
          min-width: 0;
          
          .price-from {
            text-decoration: line-through;
            color: $gray-medium;
            font-size: 0.7rem;
            opacity: 0.6;
            white-space: nowrap;
          }
        }
      }

      .toggle-switch {
        width: 52px;
        height: 28px;
        background: $gray-light;
        border-radius: 14px;
        flex-shrink: 0;

        @media (max-width: 768px) {
          align-self: flex-end;
        }
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

// Estilos para inputs de quantidade de vídeo
.quantity-input-section {
  padding: 0 20px 20px;
  border-top: 1px solid rgba($gray-light, 0.5);
  margin-top: 15px;

  @media (max-width: 768px) {
    padding: 0 15px 15px;
  }
  
  .quantity-label {
    display: block;
    font-weight: 600;
    color: $gray-darkness;
    margin: 12px 0;
    font-size: 0.9rem;

    @media (max-width: 768px) {
      font-size: 0.85rem;
      margin: 8px 0;
    }
  }
  
  .quantity-input-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;

    @media (max-width: 768px) {
      gap: 6px;
      margin-bottom: 8px;
    }
    
    .quantity-btn {
      width: 36px;
      height: 36px;
      border: 2px solid $p-color;
      background: $white;
      color: $p-color;
      border-radius: 8px;
      font-weight: 700;
      font-size: 1.1rem;
      cursor: pointer;
      transition: all 0.3s;

      @media (max-width: 768px) {
        width: 32px;
        height: 32px;
        font-size: 1rem;
      }
      
      &:hover:not(:disabled) {
        background: $p-color;
        color: $white;
      }
      
      &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
      }
    }
    
    .quantity-input {
      width: 80px;
      height: 36px;
      border: 2px solid $gray-light;
      border-radius: 8px;
      text-align: center;
      font-weight: 600;
      font-size: 1rem;

      @media (max-width: 768px) {
        width: 60px;
        height: 32px;
        font-size: 0.9rem;
      }
      
      &:focus {
        border-color: $p-color;
        outline: none;
      }
    }
  }
  
  .quantity-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 0.85rem;

    @media (max-width: 768px) {
      font-size: 0.8rem;
      gap: 2px;
    }
    
    .unit-price {
      color: $gray-medium;
    }
    
    .total-price {
      color: $p-color;
      font-weight: 700;
    }
  }
}

// Addon Pro styling
.pro-addon {
  border-color: #ff8c00 !important;
  
  &:has(input:checked) {
    border-color: #ff8c00 !important;
    background: rgba(#ff8c00, 0.05) !important;
  }
  
  .toggle-name {
    .premium-badge {
      background: linear-gradient(135deg, #ff8c00 0%, #ff7700 100%);
      color: white;
      padding: 2px 8px;
      border-radius: 6px;
      font-size: 0.6rem;
      font-weight: 700;
      margin-left: 6px;
    }
  }
  
  .toggle-price {
    color: #ff8c00 !important;
  }
  
  .toggle-switch {
    input:checked ~ .toggle-content .toggle-switch {
      background: #ff8c00 !important;
    }
  }
}

// Seção de informação do YouTube
.youtube-info-section {
  margin-top: 12px;
  
  .youtube-info {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px;
    background: rgba(#ff0000, 0.05);
    border: 1px solid rgba(#ff0000, 0.2);
    border-radius: 12px;
    font-size: 0.9rem;
    color: $gray-darkness;
    
    .fa-youtube {
      color: #ff0000;
      font-size: 1.2rem;
      flex-shrink: 0;
    }
    
    span {
      line-height: 1.4;
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
  padding: 40px;
  background: $gray-lightness;
  border-radius: 16px;
  text-align: center;
  margin-bottom: 40px;

  h3 {
    font-size: 1.75rem;
    font-weight: 800;
    color: $gray-darkness;
    margin-bottom: 12px;
    line-height: 1.3;
  }

  p {
    color: $gray-medium;
    font-size: 1.05rem;
    line-height: 1.6;
    margin-bottom: 32px;
  }

  .question-actions {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 24px;

    @media (max-width: 768px) {
      flex-direction: column;
      align-items: center;
    }

    button {
      // TAMANHO E ESPAÇAMENTO - O segredo do botão "gordinho"
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      min-height: 60px;
      padding: 0 40px;
      
      // FONTE - Negrito passa confiança
      font-size: 1.1rem;
      font-weight: 700;
      text-transform: none;
      letter-spacing: 0.5px;
      
      // FORMA
      border: none;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-sizing: border-box;

      // Largura mínima para não ficarem muito estreitos
      min-width: 420px;

      @media (max-width: 768px) {
        min-width: 100%;
        max-width: 420px;
        padding: 0 24px;
        font-size: 1rem;
      }

      i {
        font-size: 1.2rem;
        flex-shrink: 0;
      }
    }

    // ESTILO LARANJA (PRINCIPAL)
    .btn-standard {
      background: linear-gradient(135deg, #E67E22 0%, #D35400 100%);
      color: $white;
      box-shadow: 0 4px 15px rgba(230, 126, 34, 0.3);

      &:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(230, 126, 34, 0.4);
      }

      &:active {
        transform: translateY(0);
        box-shadow: 0 4px 15px rgba(230, 126, 34, 0.3);
      }
    }

    // ESTILO BRANCO (SECUNDÁRIO)
    .btn-custom {
      background: $white;
      color: #E67E22;
      border: 2px solid #E67E22;

      &:hover {
        background: #FFF5EC;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(230, 126, 34, 0.15);
      }

      &:active {
        transform: translateY(0);
      }
    }
  }

  .back-to-landing {
    padding-top: 20px;
    border-top: 1px solid rgba($gray-medium, 0.2);
    margin-top: 8px;

    a {
      font-size: 0.95rem;
      color: $gray-medium;
      cursor: pointer;
      transition: color 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 6px;

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
  gap: 12px;
  margin-top: 24px;

  @media (min-width: 480px) {
    gap: 16px;
    margin-top: 32px;
  }

  @media (min-width: 768px) {
    margin-top: 40px;
  }

  button {
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    flex: 1;
    justify-content: center;
    max-width: 200px;

    @media (min-width: 480px) {
      padding: 14px 24px;
      border-radius: 10px;
      font-size: 0.95rem;
      flex: none;
    }

    @media (min-width: 768px) {
      padding: 16px 32px;
      border-radius: 12px;
      font-size: 1rem;
      gap: 10px;
    }

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

// Navegação Simplificada (apenas botão voltar)
.step-navigation-back {
  margin-top: 24px;
  
  @media (max-width: 768px) {
    margin-top: 20px;
  }
  
  .btn-back {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    background: $white;
    color: $gray-darkness;
    border: 2px solid $gray-light;
    border-radius: 12px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    
    i {
      font-size: 0.9rem;
    }

    &:hover {
      border-color: $p-color;
      color: $p-color;
      transform: translateX(-4px);
    }
    
    @media (max-width: 768px) {
      width: 100%;
      justify-content: center;
      padding: 12px 16px;
      font-size: 0.9rem;
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

// Etapa 2: Checkout Form Simplificado (Apenas dados de pagamento)
.checkout-form {
  .validation-alert {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 20px;
    background: linear-gradient(135deg, #ffebee, #ffcdd2);
    border-left: 4px solid #f44336;
    border-radius: 12px;
    margin-bottom: 30px;
    animation: slideDown 0.3s ease;

    i {
      color: #f44336;
      font-size: 1.5rem;
      flex-shrink: 0;
      margin-top: 2px;
    }

    .alert-content {
      flex: 1;

      strong {
        display: block;
        color: #c62828;
        margin-bottom: 8px;
        font-size: 1rem;
      }

      ul {
        margin: 0;
        padding-left: 20px;
        list-style: disc;

        li {
          color: $gray-darkness;
          font-size: 0.9rem;
          line-height: 1.6;
          margin-bottom: 4px;

          &:last-child {
            margin-bottom: 0;
          }
        }
      }
    }
  }

  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .info-box {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 20px;
    background: linear-gradient(135deg, #e8f5e9, #f1f8e9);
    border-left: 4px solid #4caf50;
    border-radius: 12px;
    margin-bottom: 30px;

    i {
      color: #4caf50;
      font-size: 1.5rem;
      flex-shrink: 0;
      margin-top: 2px;
    }

    p {
      margin: 0;
      font-size: 0.95rem;
      line-height: 1.6;
      color: $gray-darkness;

      strong {
        color: #2e7d32;
      }
    }
  }

  .field-hint {
    display: block;
    margin-top: 6px;
    font-size: 0.85rem;
    color: $gray-medium;
    font-style: italic;
  }

  .form-section {
    margin-bottom: 40px;
    padding-bottom: 40px;
    border-bottom: 2px solid $gray-light;

    &.order-summary-section {
      border-bottom: none;
    }

    &:last-of-type {
      border-bottom: none;
    }

    .form-section-title {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 1.3rem;
      font-weight: 700;
      color: $gray-darkness;
      margin-bottom: 24px;

      i {
        color: $p-color;
      }
    }
  }

  .form-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;

    @media (min-width: 480px) {
      gap: 18px;
    }

    @media (min-width: 768px) {
      grid-template-columns: 1fr 1fr;
      gap: 20px;
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
      max-width: 100%;
      padding: 12px 14px;
      font-size: 0.9rem;
      color: $gray-darkness;
      background: $white;
      border: 2px solid $gray-light;
      border-radius: 8px;
      transition: all 0.3s;
      box-sizing: border-box;

      @media (min-width: 480px) {
        padding: 13px 15px;
        font-size: 0.95rem;
        border-radius: 10px;
      }

      @media (min-width: 768px) {
        padding: 14px 16px;
        font-size: 1rem;
        border-radius: 12px;
      }

      &:focus {
        outline: none;
        border-color: $p-color;
        box-shadow: 0 0 0 4px rgba($p-color, 0.1);
      }
    }

    textarea {
      min-height: 100px;
      resize: vertical;
      
      @media (min-width: 768px) {
        min-height: 120px;
      }
    }

    small {
      display: block;
      margin-top: 6px;
      font-size: 0.85rem;
      color: $gray-medium;
    }
  }

  // Estilos para a seção de resumo do pedido dentro do checkout
  .order-summary-section {
    // Header Compacto com Toggle
    .summary-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 20px;
      gap: 12px;
      position: relative;
      
      .product-info {
        flex: 1;
        
        h3 {
          font-size: 1.3rem;
          font-weight: 700;
          color: $gray-darkness;
          margin: 0 0 4px 0;
          line-height: 1.3;
          
          @media (max-width: 768px) {
            font-size: 1.1rem;
          }
        }
        
        .product-summary {
          font-size: 0.9rem;
          color: $gray-medium;
          margin: 0;
          line-height: 1.4;
          
          @media (max-width: 768px) {
            font-size: 0.85rem;
          }
        }
      }
      
      .btn-toggle-details {
        background: transparent;
        border: 2px solid $gray-light;
        color: $p-color;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 6px;
        
        &:hover {
          border-color: $p-color;
          background: rgba($p-color, 0.05);
        }
        
        i {
          font-size: 0.75rem;
          transition: transform 0.3s;
        }
        
        &.open i {
          transform: rotate(180deg);
        }
        
        @media (max-width: 768px) {
          font-size: 0.8rem;
          padding: 6px 12px;
        }
      }
    }
    
    // Accordion com animação
    .included-services {
      overflow: hidden;
      transition: max-height 0.4s ease, opacity 0.3s ease, margin 0.3s ease;
      
      &.collapsed {
        max-height: 0;
        opacity: 0;
        margin-bottom: 0;
      }
      
      &.expanded {
        max-height: 800px;
        opacity: 1;
        margin-bottom: 20px;
      }
      
      ul {
        list-style: none;
        margin: 0;
        padding: 16px 0 0 0;
        display: grid;
        gap: 10px;
        
        li {
          display: flex;
          align-items: center;
          gap: 12px;
          padding: 10px 12px;
          background: $gray-lightness;
          border-radius: 8px;
          font-size: 0.9rem;
          color: $gray-darkness;
          line-height: 1.4;
          
          @media (max-width: 768px) {
            font-size: 0.85rem;
            padding: 8px 10px;
            gap: 10px;
          }
          
          i {
            color: #4caf50;
            font-size: 1rem;
            flex-shrink: 0;
          }
          
          strong {
            color: $p-color;
            font-weight: 600;
          }
        }
      }
    }
    
    // Divider
    .divider {
      border: none;
      border-top: 2px solid $gray-light;
      margin: 20px 0;
      
      @media (max-width: 768px) {
        margin: 16px 0;
      }
    }
    
    // Preço com Badge do Café Inline
    .price-display {
      padding: 20px;
      background: linear-gradient(135deg, rgba($p-color, 0.05) 0%, rgba($p-dark, 0.02) 100%);
      border-radius: 12px;
      margin-bottom: 20px;
      
      @media (max-width: 768px) {
        padding: 16px;
        margin-bottom: 16px;
      }
      
      .label {
        display: block;
        font-size: 0.9rem;
        font-weight: 600;
        color: $gray-medium;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        
        @media (max-width: 768px) {
          font-size: 0.85rem;
        }
      }
      
      // Layout de Preço Mensal (Estratégia SaaS)
      .price-display-monthly {
        margin: 16px 0;
        
        .price-row {
          display: flex;
          align-items: center;
          justify-content: space-between;
          gap: 16px;
          margin-bottom: 12px;
          flex-wrap: wrap;
          
          @media (max-width: 768px) {
            gap: 12px;
          }
        }
        
        .main-price {
          display: flex;
          align-items: baseline;
          gap: 4px;
          
          .currency {
            font-size: 1.4rem;
            font-weight: 700;
            color: $p-color;
            
            @media (max-width: 768px) {
              font-size: 1.2rem;
            }
          }
          
          .amount {
            font-size: 3rem;
            font-weight: 900;
            color: $p-color;
            line-height: 1;
            letter-spacing: -1px;
            
            @media (max-width: 768px) {
              font-size: 2.4rem;
            }
          }
          
          .period {
            font-size: 1.2rem;
            font-weight: 600;
            color: $gray-medium;
            
            @media (max-width: 768px) {
              font-size: 1rem;
            }
          }
        }
        
        .sub-price-context {
          display: flex;
          align-items: center;
          gap: 8px;
          padding: 10px 14px;
          background: rgba($accent-blue, 0.08);
          border-left: 3px solid $accent-blue;
          border-radius: 8px;
          font-size: 0.9rem;
          color: $gray-darkness;
          line-height: 1.5;
          
          @media (max-width: 768px) {
            font-size: 0.85rem;
            padding: 8px 12px;
          }
          
          i {
            color: $accent-blue;
            font-size: 1rem;
            flex-shrink: 0;
          }
          
          strong {
            color: $p-color;
            font-weight: 700;
          }
        }
      }
      
      .coffee-badge-secondary {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: linear-gradient(135deg, #6F4E37 0%, #8B4513 100%);
        color: white;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(111, 78, 55, 0.3);
        white-space: nowrap;
        flex-shrink: 0;
        
        @media (max-width: 768px) {
          font-size: 0.7rem;
          padding: 6px 10px;
        }
        
        @media (max-width: 480px) {
          font-size: 0.65rem;
          padding: 5px 8px;
        }
      }
      
      .savings-text {
        display: block;
        font-size: 0.9rem;
        color: #4caf50;
        font-weight: 600;
        margin-top: 12px;
        
        @media (max-width: 768px) {
          font-size: 0.85rem;
        }
        
        i {
          margin-right: 4px;
        }
      }
    }
    
    // Seletor de Pagamento dentro do resumo
    .payment-selector {
      display: grid;
      gap: 12px;
      
      .payment-option {
        position: relative;
        cursor: pointer;
        
        input[type="radio"] {
          display: none;
        }
        
        .payment-content {
          padding: 16px;
          border: 2px solid $gray-light;
          border-radius: 12px;
          transition: all 0.3s;
          background: $white;
          
          @media (max-width: 768px) {
            padding: 14px;
          }
          
          &:hover {
            border-color: $p-color;
            box-shadow: 0 2px 8px rgba($p-color, 0.1);
          }
        }
        
        input[type="radio"]:checked + .payment-content {
          border-color: $p-color;
          background: linear-gradient(135deg, rgba($p-color, 0.05) 0%, rgba($p-dark, 0.02) 100%);
          box-shadow: 0 4px 12px rgba($p-color, 0.15);
          
          .btn-pay-now {
            display: flex;
          }
        }
        
        .radio-header {
          display: flex;
          align-items: center;
          justify-content: space-between;
          margin-bottom: 12px;
          
          .radio-label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: $gray-darkness;
            font-size: 1rem;
            
            @media (max-width: 768px) {
              font-size: 0.95rem;
            }
            
            i {
              font-size: 1.1rem;
              color: $p-color;
            }
          }
          
          .discount-tag {
            padding: 4px 10px;
            background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
            color: white;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
          }
        }
        
        .btn-pay-now {
          display: none;
          width: 100%;
          padding: 14px;
          background: $gradient-primary;
          color: $white;
          border: none;
          border-radius: 10px;
          font-size: 1rem;
          font-weight: 700;
          cursor: pointer;
          align-items: center;
          justify-content: center;
          gap: 8px;
          transition: all 0.3s;
          
          @media (max-width: 768px) {
            font-size: 0.95rem;
            padding: 12px;
          }
          
          i {
            font-size: 0.9rem;
          }
          
          &:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba($p-color, 0.4);
          }
          
          &:disabled {
            opacity: 0.6;
            cursor: not-allowed;
          }
        }
        
        .payment-details {
          font-size: 0.85rem;
          color: $gray-medium;
          line-height: 1.4;
          margin: 0 0 12px 0;
          
          @media (max-width: 768px) {
            font-size: 0.8rem;
          }
          
          strong {
            color: #4caf50;
            font-weight: 700;
          }
        }
      }
    }
  }
}

// Estilos para .order-summary (usado no sidebar)
.order-summary {
  padding: 24px;
  background: $white;
  border-radius: 16px;
  margin-bottom: 32px;
  border: 2px solid $gray-light;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
  
  @media (max-width: 768px) {
    padding: 20px;
    margin-bottom: 24px;
  }
  
  // Header Compacto com Toggle
  .summary-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    gap: 12px;
    position: relative;
    
    .product-info {
      flex: 1;
      
      h3 {
        font-size: 1.3rem;
        font-weight: 700;
        color: $gray-darkness;
        margin: 0 0 8px 0;
        line-height: 1.3;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        
        @media (max-width: 768px) {
          font-size: 1.1rem;
        }
        
        .badge-special-offer {
          display: inline-flex;
          align-items: center;
          gap: 4px;
          padding: 4px 10px;
          background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
          color: white;
          font-size: 0.75rem;
          font-weight: 700;
          border-radius: 6px;
          box-shadow: 0 2px 8px rgba(255, 107, 107, 0.4);
          animation: pulse 2s ease-in-out infinite;
          white-space: nowrap;
        }
      }
      
      .product-summary {
        font-size: 0.9rem;
        color: $gray-medium;
        margin: 0;
        line-height: 1.4;
        
        @media (max-width: 768px) {
          font-size: 0.85rem;
        }
      }
    }
    
    .btn-toggle-details {
      background: transparent;
      border: 2px solid $gray-light;
      color: $p-color;
      padding: 6px 14px;
      border-radius: 8px;
      font-size: 0.85rem;
      font-weight: 600;
      cursor: pointer;
      white-space: nowrap;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      gap: 6px;
      
      &:hover {
        border-color: $p-color;
        background: rgba($p-color, 0.05);
      }
      
      i {
        font-size: 0.75rem;
        transition: transform 0.3s;
      }
      
      &.open i {
        transform: rotate(180deg);
      }
      
      @media (max-width: 768px) {
        font-size: 0.8rem;
        padding: 6px 12px;
      }
    }
  }
  
  // Accordion com animação
  .included-services {
    overflow: hidden;
    transition: max-height 0.4s ease, opacity 0.3s ease, margin 0.3s ease;
    
    &.collapsed {
      max-height: 0;
      opacity: 0;
      margin-bottom: 0;
    }
    
    &.expanded {
      max-height: 800px;
      opacity: 1;
      margin-bottom: 20px;
    }
    
    ul {
      list-style: none;
      margin: 0;
      padding: 16px 0 0 0;
      display: grid;
      gap: 10px;
      
      li {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        background: $gray-lightness;
        border-radius: 8px;
        font-size: 0.9rem;
        color: $gray-darkness;
        line-height: 1.4;
        
        @media (max-width: 768px) {
          font-size: 0.85rem;
          padding: 8px 10px;
          gap: 10px;
        }
        
        i {
          color: #4caf50;
          font-size: 1rem;
          flex-shrink: 0;
        }
        
        strong {
          color: $p-color;
          font-weight: 600;
        }
      }
    }
  }
  
  // Divider
  .divider {
    border: none;
    border-top: 2px solid $gray-light;
    margin: 20px 0;
    
    @media (max-width: 768px) {
      margin: 16px 0;
    }
  }
  
  // Preço com Badge do Café Inline
  .price-display {
    padding: 24px;
    background: linear-gradient(135deg, rgba($p-color, 0.08) 0%, rgba($p-dark, 0.05) 100%);
    border-radius: 12px;
    margin-bottom: 20px;
    border: 2px solid rgba($p-color, 0.15);
    
    @media (max-width: 768px) {
      padding: 18px;
      margin-bottom: 16px;
    }
    
    .price-anchorage {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 12px;
      
      .label-from {
        font-size: 0.8rem;
        color: $gray-medium;
        font-weight: 500;
      }
      
      .old-price {
        font-size: 1.05rem;
        color: $gray-medium;
        text-decoration: line-through;
        font-weight: 600;
        opacity: 0.7;
      }
    }
    
    .label {
      display: block;
      font-size: 0.9rem;
      font-weight: 600;
      color: $gray-darkness;
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      
      @media (max-width: 768px) {
        font-size: 0.85rem;
      }
    }
    
    .annual-total {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      margin-top: 12px;
      padding: 10px 16px;
      background: rgba($accent-blue, 0.08);
      border-radius: 8px;
      font-size: 0.9rem;
      color: $gray-darkness;
      
      i {
        color: $accent-blue;
        font-size: 1rem;
      }
      
      strong {
        color: $p-color;
        font-weight: 700;
      }
      
      @media (max-width: 768px) {
        font-size: 0.85rem;
        padding: 8px 12px;
      }
    }
    
    .savings-text {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      margin-top: 16px;
      padding: 10px 16px;
      background: linear-gradient(135deg, rgba(#4caf50, 0.15) 0%, rgba(#4caf50, 0.08) 100%);
      border-radius: 8px;
      border-left: 3px solid #4caf50;
      font-size: 0.9rem;
      color: #2e7d32;
      font-weight: 700;
      
      i {
        font-size: 1rem;
      }
      
      strong {
        font-size: 1.05rem;
        color: #1b5e20;
      }
      
      @media (max-width: 768px) {
        font-size: 0.85rem;
        padding: 8px 12px;
      }
    }
  }
  
  // Seletor de Pagamento Compacto
  .payment-selector {
    display: grid;
    gap: 12px;
    
    .payment-option {
      position: relative;
      cursor: pointer;
      
      input[type="radio"] {
        display: none;
      }
      
      .payment-content {
        padding: 16px;
        border: 2px solid $gray-light;
        border-radius: 12px;
        transition: all 0.3s;
        background: $white;
        
        @media (max-width: 768px) {
          padding: 14px;
        }
        
        &:hover {
          border-color: $p-color;
          box-shadow: 0 2px 8px rgba($p-color, 0.1);
        }
      }
      
      input[type="radio"]:checked + .payment-content {
        border-color: $p-color;
        background: linear-gradient(135deg, rgba($p-color, 0.05) 0%, rgba($p-dark, 0.02) 100%);
        box-shadow: 0 4px 12px rgba($p-color, 0.15);
        
        .btn-pay-now {
          display: flex;
        }
      }
      
      .radio-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
        
        .radio-label {
          display: flex;
          align-items: center;
          gap: 10px;
          font-weight: 600;
          color: $gray-darkness;
          font-size: 1rem;
          
          @media (max-width: 768px) {
            font-size: 0.95rem;
          }
          
          i {
            font-size: 1.1rem;
            color: $p-color;
          }
        }
        
        .discount-tag {
          padding: 5px 12px;
          background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
          color: white;
          border-radius: 8px;
          font-size: 0.72rem;
          font-weight: 700;
          text-transform: uppercase;
          box-shadow: 0 3px 10px rgba(76, 175, 80, 0.4);
          animation: pulse 2s ease-in-out infinite;
          
          &.extra-discount {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            box-shadow: 0 3px 10px rgba(255, 107, 107, 0.4);
          }
        }
      }
      
      .btn-pay-now {
        display: none;
        width: 100%;
        padding: 16px 20px;
        background: $gradient-primary;
        color: $white;
        border: none;
        border-radius: 12px;
        font-size: 1.05rem;
        font-weight: 700;
        cursor: pointer;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        transition: all 0.3s;
        box-shadow: 0 4px 14px rgba($p-color, 0.35);
        
        @media (max-width: 768px) {
          font-size: 0.95rem;
          padding: 14px 16px;
        }
        
        .btn-main-text {
          display: flex;
          align-items: center;
          gap: 8px;
          font-size: 1.05rem;
          
          @media (max-width: 768px) {
            font-size: 0.95rem;
          }
          
          i {
            font-size: 1rem;
          }
        }
        
        .btn-sub-text {
          font-size: 0.8rem;
          font-weight: 500;
          opacity: 0.9;
          
          @media (max-width: 768px) {
            font-size: 0.75rem;
          }
        }
        
        &:hover:not(:disabled) {
          transform: translateY(-3px);
          box-shadow: 0 8px 20px rgba($p-color, 0.45);
        }
        
        &:disabled {
          opacity: 0.6;
          cursor: not-allowed;
        }
        
        &.btn-pix {
          background: linear-gradient(135deg, #4caf50 0%, darken(#4caf50, 10%) 100%);
          box-shadow: 0 6px 16px rgba(#4caf50, 0.45);
          animation: pulse 2s ease-in-out infinite;
          
          &:hover {
            box-shadow: 0 8px 22px rgba(#4caf50, 0.55);
          }
        }
      }
      
      .payment-details {
        font-size: 0.85rem;
        color: $gray-medium;
        line-height: 1.4;
        margin: 0 0 12px 0;
        
        @media (max-width: 768px) {
          font-size: 0.8rem;
        }
        
        strong {
          color: #4caf50;
          font-weight: 700;
        }
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

// Mobile Price Bar (fixed bottom)
.mobile-price-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(135deg, $white 0%, #f8f9ff 100%);
  border-top: 1px solid rgba($p-color, 0.2);
  box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
  z-index: 1000;
  padding: 15px;
  display: block;

  @media (min-width: 1024px) {
    display: none;
  }

  .mobile-price-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    max-width: 100%;
    gap: 15px;
  }

  .mobile-price-info {
    flex: 1;
    min-width: 0;
  }

  .mobile-price-total {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .mobile-price-label {
    font-size: 0.8rem;
    color: $gray-medium;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .mobile-price-values {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .mobile-price-original {
    font-size: 0.75rem;
    color: $gray-medium;
    text-decoration: line-through;
    opacity: 0.7;
  }

  .mobile-price-current {
    font-size: 1.1rem;
    font-weight: 800;
    color: $p-color;
  }

  .mobile-btn-next,
  .mobile-btn-finish {
    background: linear-gradient(135deg, $p-color 0%, $p-dark 100%);
    color: $white;
    border: none;
    border-radius: 12px;
    padding: 12px 20px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 120px;
    justify-content: center;
    white-space: nowrap;

    &:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba($p-color, 0.4);
    }

    &:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }

    i {
      font-size: 0.8rem;
    }
  }

  .mobile-btn-finish {
    background: linear-gradient(135deg, $success 0%, darken($success, 10%) 100%);
    
    &:hover:not(:disabled) {
      box-shadow: 0 8px 20px rgba($success, 0.4);
    }
  }

  // Responsividade específica mobile
  @media (max-width: 480px) {
    padding: 12px 10px;
    
    .mobile-price-content {
      gap: 10px;
    }
    
    .mobile-price-current {
      font-size: 1rem;
    }
    
    .mobile-btn-next,
    .mobile-btn-finish {
      padding: 10px 16px;
      font-size: 0.85rem;
      min-width: 100px;
    }
  }

  @media (max-width: 320px) {
    .mobile-price-label {
      font-size: 0.7rem;
    }
    
    .mobile-price-current {
      font-size: 0.9rem;
    }
    
    .mobile-btn-next,
    .mobile-btn-finish {
      padding: 8px 12px;
      font-size: 0.8rem;
      min-width: 80px;
    }
  }
}

// Price Sidebar
.price-sidebar {
  display: none;

  @media (min-width: 1024px) {
    display: block;
    position: -webkit-sticky;
    position: sticky;
    top: 120px;
    height: fit-content;
    max-height: calc(100vh - 40px);
    background: $white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow-y: auto;
    z-index: 100;
    align-self: start;
    flex-shrink: 0;
    width: 350px;
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

// Seção de limites de upload
.upload-limits-info {
  background: linear-gradient(135deg, #f8f9ff 0%, #e8f0ff 100%);
  border: 1px solid #e1e8f0;
  border-radius: 12px;
  padding: 20px;
  margin: 25px 0;
  
  .info-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
    color: #2d3748;
    font-weight: 600;
    
    i {
      color: #4285f4;
      font-size: 18px;
    }
  }
  
  .limits-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 15px;
  }
  
  .limit-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 15px 10px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e8f0ff;
    transition: all 0.2s ease;
    
    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(66, 133, 244, 0.1);
    }
    
    i {
      font-size: 24px;
      margin-bottom: 8px;
      color: #4285f4;
    }
    
    .limit-type {
      font-size: 14px;
      color: #4a5568;
      margin-bottom: 4px;
      font-weight: 500;
    }
    
    .limit-value {
      font-size: 12px;
      color: #718096;
      font-weight: 600;
      background: #f0f4f8;
      padding: 2px 8px;
      border-radius: 4px;
      margin-bottom: 4px;
    }
    
    .limit-note {
      font-size: 10px;
      color: #4285f4;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      
      &.premium-note {
        color: #ff6b35;
        font-weight: 600;
      }
    }
    
    &.basic-plan {
      border-left: 3px solid #4285f4;
    }
    
    &.pro-plan {
      border-left: 3px solid #ff6b35;
      background: linear-gradient(135deg, #fff5f2 0%, #ffeee8 100%);
      
      .limit-type {
        color: #d63500;
        font-weight: 600;
      }
      
      .limit-value {
        background: #ff6b35;
        color: white;
      }
    }
  }
  
  .video-plans-note {
    margin-top: 15px;
    padding: 12px 16px;
    background: linear-gradient(135deg, #e8f4fd 0%, #d4edda 100%);
    border-radius: 8px;
    border-left: 4px solid #17a2b8;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: #155724;
    
    i {
      color: #17a2b8;
      font-size: 14px;
    }
  }
}
    
.btn-toggle-items {
  width: 100% !important;
  margin-top: 16px !important;
  padding: 12px 20px !important;
  background: linear-gradient(135deg, rgba($p-color, 0.05) 0%, rgba($accent-blue, 0.03) 100%) !important;
  border: 2px solid rgba($p-color, 0.2) !important;
  border-radius: 10px !important;
  color: $p-color !important;
  font-size: 0.9rem !important;
  font-weight: 700 !important;
  cursor: pointer !important;
  transition: all 0.3s ease !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 10px !important;
  position: relative !important;
  overflow: hidden !important;
  
  // Efeito de brilho no hover
  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.5s;
  }
  
  &:hover {
    border-color: $p-color;
    background: linear-gradient(135deg, rgba($p-color, 0.1) 0%, rgba($accent-blue, 0.06) 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba($p-color, 0.15);
    
    &::before {
      left: 100%;
    }
    
    i {
      transform: scale(1.15);
    }
  }
  
  &:active {
    transform: translateY(0);
  }
  
  i {
    font-size: 1rem;
    transition: transform 0.3s ease;
  }
  
  span {
    position: relative;
    z-index: 1;
  }
  
  @media (max-width: 768px) {
    font-size: 0.85rem;
    padding: 10px 16px;
    
    i {
      font-size: 0.9rem;
    }
  }
}
</style>
