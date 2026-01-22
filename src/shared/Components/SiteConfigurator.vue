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
                      <span><strong>Google Analytics</strong> (configurado)</span>
                    </li>
                    <li>
                      <i class="fas fa-check-circle"></i>
                      <span><strong>Backup Automático</strong> diário</span>
                    </li>
                    <li>
                      <i class="fas fa-check-circle"></i>
                      <span><strong>CDN Global</strong> (carregamento rápido)</span>
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
                    Ver mais 6 itens técnicos
                  </span>
                  <span v-else>
                    <i class="fas fa-minus-circle"></i>
                    Mostrar menos
                  </span>
                </button>
              </div>
              
              <!-- Divider -->
              <hr class="divider">
              
              <!-- Preço com Badge do Café Inline -->
              <div class="price-display">
                <!-- Ancoragem de Preço -->
                <div class="price-anchorage">
                  <span class="label-from">De</span>
                  <span class="old-price">{{ formatOriginalPrice(installmentTotal) }}</span>
                </div>
                
                <span class="label">Equivalente a:</span>
                <div class="values">
                  <span class="final-price">{{ formatPrice(cashPrice) }}</span>
                  <span class="coffee-badge">
                    ☕ R$ {{ dailyPrice }} / dia
                  </span>
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
                        {{ isSubmitting ? 'Processando...' : `Garantir Desconto de ${formatPrice(getPixExtraSavings())}` }}
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
      selectedContentAddons: [], // ['video', 'pdf']
      customPages: [], // Array de objetos: [{ description: '', resources: { image: false, video: false, carousel: false, form: false } }]
      
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
      showAllIncludedItems: false
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
    },
    
    // Preço diário para gatilho mental do café
    dailyPrice() {
      const annual = this.cashPrice;
      const daily = annual / 365;
      return daily.toFixed(2).replace('.', ',');
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
    }
  },
  methods: {
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
        video: 'Por padrão, o site suporta apenas imagens. Ative esta opção se você precisa subir vídeos institucionais ou de produtos diretamente no site.',
        pdf: 'Habilita o envio de arquivos PDF para seus clientes baixarem. Ideal para disponibilizar cardápios, catálogos e tabelas. Sem isso, o site aceita apenas imagens e textos.'
      };
      return tooltips[key] || 'Recurso adicional';
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
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-bottom: 24px;

    @media (max-width: 768px) {
      grid-template-columns: 1fr;
    }
  }

  .package-card {
    position: relative;
    padding: 32px 24px;
    background: $white;
    border: 3px solid $gray-light;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;

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
  gap: 12px;
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
      
      .values {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 12px;
        
        .final-price {
          font-size: 2.2rem;
          font-weight: 800;
          color: $p-color;
          line-height: 1;
          
          @media (max-width: 768px) {
            font-size: 1.8rem;
          }
        }
        
        .coffee-badge {
          display: inline-flex;
          align-items: center;
          gap: 6px;
          padding: 6px 12px;
          background: linear-gradient(135deg, #6F4E37 0%, #8B4513 100%);
          color: white;
          border-radius: 20px;
          font-size: 0.85rem;
          font-weight: 600;
          box-shadow: 0 2px 8px rgba(111, 78, 55, 0.3);
          white-space: nowrap;
          
          @media (max-width: 768px) {
            font-size: 0.8rem;
            padding: 5px 10px;
          }
        }
      }
      
      .savings-text {
        display: block;
        font-size: 0.9rem;
        color: #4caf50;
        font-weight: 600;
        
        @media (max-width: 768px) {
          font-size: 0.85rem;
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
    
    .btn-toggle-items {
      width: 100%;
      margin-top: 12px;
      padding: 10px 16px;
      background: transparent;
      border: 2px dashed $gray-light;
      border-radius: 8px;
      color: $gray-medium;
      font-size: 0.85rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      
      &:hover {
        border-color: $p-color;
        color: $p-color;
        background: rgba($p-color, 0.03);
      }
      
      i {
        font-size: 0.9rem;
      }
      
      @media (max-width: 768px) {
        font-size: 0.8rem;
        padding: 8px 12px;
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
    
    .values {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 12px;
      
      .final-price {
        font-size: 2.2rem;
        font-weight: 800;
        color: $p-color;
        line-height: 1;
        
        @media (max-width: 768px) {
          font-size: 1.8rem;
        }
      }
      
      .coffee-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: linear-gradient(135deg, #6F4E37 0%, #8B4513 100%);
        color: white;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(111, 78, 55, 0.3);
        white-space: nowrap;
        
        @media (max-width: 768px) {
          font-size: 0.8rem;
          padding: 5px 10px;
        }
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
