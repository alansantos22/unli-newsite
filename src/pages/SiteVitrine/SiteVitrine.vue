<template>
  <div class="site-vitrine-page">
    <!-- Hero Section -->
    <section class="hero-section" data-scroll-skip>
      <div class="hero-background">
        <div class="hero-overlay"></div>
      </div>
      <div class="hero-container">
        <div class="hero-content">
          <span class="hero-badge">
            <i class="fas fa-star"></i>
            Plano Anual Completo
          </span>
          <h1 class="hero-title">
            Seu site profissional<br>
            <span class="highlight-text">no ar em até 10 dias</span>
          </h1>
          <p class="hero-description">
            Plano anual completo com <strong>domínio + hospedagem + SSL</strong>. Presença digital com padrão profissional, 
            para sua empresa transmitir confiança e receber contatos todos os dias.
          </p>
          
          <div class="hero-price-highlight">
            <div class="price-box">
              <span class="price-label">Investimento Anual</span>
              <span class="price-value">R$ 77,87<span class="price-period">/Mês</span></span>
              <span class="price-subtext">ou R$ 779/ano à vista</span>
            </div>
          </div>

          <div class="hero-actions">
            <a v-if="isManualMode" :href="whatsappLink('Quero criar meu site')" target="_blank" class="btn-primary">
              <i class="fab fa-whatsapp"></i>
              Criar meu site agora
            </a>
            <router-link v-else to="/configurador?plan=site_complete" class="btn-primary">
              <i class="fas fa-rocket"></i>
              Criar meu site agora
            </router-link>
            <a href="#como-funciona" class="btn-secondary">
              <i class="fas fa-play-circle"></i>
              Como Funciona
            </a>
          </div>

          <div class="hero-trust">
            <div class="trust-item">
              <i class="fas fa-check-circle"></i>
              <span>Visual profissional e responsivo (mobile-first)</span>
            </div>
            <div class="trust-item">
              <i class="fas fa-file-alt"></i>
              <span>Páginas essenciais: Home, Serviços, Sobre e Contato</span>
            </div>
            <div class="trust-item">
              <i class="fas fa-edit"></i>
              <span>Você edita textos quando quiser pelo sistema</span>
            </div>
          </div>
        </div>

        <div class="hero-visual">
          <div class="mockup-container">
            <div class="mockup-glow"></div>
            <picture>
              <source :srcset="require('@/assets/img/criacao-sites/notebook.webp')" type="image/webp">
              <img 
                src="@/assets/img/criacao-sites/notebook.png" 
                alt="Mockup de site profissional" 
                class="mockup-image"
                width="600"
                height="400"
                loading="eager"
                fetchpriority="high"
              />
            </picture>
          </div>
        </div>
      </div>
    </section>

    <!-- Modelos em Destaque -->
    <section class="demo-models-section" data-scroll>
      <div class="section-container">
        <div class="section-header">
          <span class="section-badge">Modelos Prontos</span>
          <h2 class="section-title">Veja como pode ficar o seu site</h2>
          <p class="section-description">
            Clique em qualquer modelo abaixo e explore um site completo e funcional — é exatamente assim que entregamos.
          </p>
        </div>

        <div class="demo-models-grid">
          <!-- Hamburgueria -->
          <a href="/demo/hamburgueria" target="_blank" class="demo-model-card" data-scroll>
            <div class="demo-model-preview hamburgueria-preview">
              <div class="preview-browser">
                <div class="browser-bar-icons">
                  <i class="fas fa-globe"></i>
                </div>
                <div class="browser-url">demo/hamburgueria</div>
              </div>
              <div class="preview-content hb-preview-content">
                <div class="preview-header-bar">🍔 House of Burgers</div>
                <div class="preview-hero-mini hb-hero-mini">
                  <span class="preview-emoji">🍔</span>
                  <div class="preview-text-lines">
                    <span class="line-title"></span>
                    <span class="line-sub"></span>
                    <span class="line-btn wpp"></span>
                  </div>
                </div>
                <div class="preview-cards-row">
                  <div class="preview-card-mini p1">🍔</div>
                  <div class="preview-card-mini p2">🥤</div>
                  <div class="preview-card-mini p3">🍟</div>
                </div>
              </div>
            </div>
            <div class="demo-model-info">
              <div class="demo-model-tag hamburgueria-tag">
                <i class="fas fa-utensils"></i> Cardápio Online
              </div>
              <h3 class="demo-model-title">Hamburgueria com Pedidos pelo WhatsApp</h3>
              <p class="demo-model-desc">Cardápio interativo por categoria, cada item com botão direto para o WhatsApp do negócio.</p>
              <span class="demo-model-cta">
                <i class="fas fa-eye"></i> Ver modelo completo
              </span>
            </div>
          </a>

          <!-- Loja de Roupas -->
          <a href="/demo/loja-roupas" target="_blank" class="demo-model-card" data-scroll>
            <div class="demo-model-preview loja-preview">
              <div class="preview-browser">
                <div class="browser-bar-icons">
                  <i class="fas fa-globe"></i>
                </div>
                <div class="browser-url">demo/loja-roupas</div>
              </div>
              <div class="preview-content lj-preview-content">
                <div class="preview-header-bar lj-header-bar">👗 Estilo Carioca</div>
                <div class="preview-hero-mini lj-hero-mini">
                  <span class="preview-emoji">👗</span>
                  <div class="preview-text-lines">
                    <span class="line-title"></span>
                    <span class="line-sub"></span>
                    <span class="line-btn pink"></span>
                  </div>
                </div>
                <div class="preview-products-row">
                  <div class="preview-product lp1">👗<span>R$ 189</span></div>
                  <div class="preview-product lp2">👖<span>R$ 149</span></div>
                  <div class="preview-product lp3">👠<span>R$ 99</span></div>
                </div>
              </div>
            </div>
            <div class="demo-model-info">
              <div class="demo-model-tag loja-tag">
                <i class="fas fa-tshirt"></i> Vitrine de Produtos
              </div>
              <h3 class="demo-model-title">Loja de Roupas com Vitrine Completa</h3>
              <p class="demo-model-desc">Grade de produtos por categoria com preços, tamanhos e compra via WhatsApp.</p>
              <span class="demo-model-cta">
                <i class="fas fa-eye"></i> Ver modelo completo
              </span>
            </div>
          </a>

          <!-- Clínica Estética -->
          <a href="/demo/clinica-estetica" target="_blank" class="demo-model-card" data-scroll>
            <div class="demo-model-preview clinica-preview">
              <div class="preview-browser">
                <div class="browser-bar-icons">
                  <i class="fas fa-globe"></i>
                </div>
                <div class="browser-url">demo/clinica-estetica</div>
              </div>
              <div class="preview-content cl-preview-content">
                <div class="preview-header-bar cl-header-bar">✨ Bella Skin</div>
                <div class="preview-hero-mini cl-hero-mini">
                  <span class="preview-emoji">💆‍♀️</span>
                  <div class="preview-text-lines">
                    <span class="line-title"></span>
                    <span class="line-sub"></span>
                    <span class="line-btn rose"></span>
                  </div>
                </div>
                <div class="preview-services-row">
                  <div class="preview-service-mini cs1">🧴</div>
                  <div class="preview-service-mini cs2">💆</div>
                  <div class="preview-service-mini cs3">⭐</div>
                </div>
              </div>
            </div>
            <div class="demo-model-info">
              <div class="demo-model-tag clinica-tag">
                <i class="fas fa-spa"></i> Clínica / Saúde
              </div>
              <h3 class="demo-model-title">Clínica de Estética com Agendamento</h3>
              <p class="demo-model-desc">Serviços com preços, resultados antes/depois, depoimentos e agendamento via WhatsApp.</p>
              <span class="demo-model-cta">
                <i class="fas fa-eye"></i> Ver modelo completo
              </span>
            </div>
          </a>

          <!-- Serralheria -->
          <a href="/demo/serralheria" target="_blank" class="demo-model-card" data-scroll>
            <div class="demo-model-preview serralheria-preview">
              <div class="preview-browser">
                <div class="browser-bar-icons">
                  <i class="fas fa-globe"></i>
                </div>
                <div class="browser-url">demo/serralheria</div>
              </div>
              <div class="preview-content sr-preview-content">
                <div class="preview-header-bar sr-header-bar">⚙️ Ferro & Forma</div>
                <div class="preview-hero-mini sr-hero-mini">
                  <span class="preview-emoji">🔧</span>
                  <div class="preview-text-lines">
                    <span class="line-title"></span>
                    <span class="line-sub"></span>
                    <span class="line-btn orange"></span>
                  </div>
                </div>
                <div class="preview-services-row">
                  <div class="preview-service-mini ss1">🚪</div>
                  <div class="preview-service-mini ss2">🪜</div>
                  <div class="preview-service-mini ss3">🏗️</div>
                </div>
              </div>
            </div>
            <div class="demo-model-info">
              <div class="demo-model-tag serralheria-tag">
                <i class="fas fa-tools"></i> Prestador de Serviços
              </div>
              <h3 class="demo-model-title">Serralheria com Portfólio e Orçamento</h3>
              <p class="demo-model-desc">Serviços detalhados, portfólio filtrado por categoria e solicitação de orçamento via WhatsApp.</p>
              <span class="demo-model-cta">
                <i class="fas fa-eye"></i> Ver modelo completo
              </span>
            </div>
          </a>
        </div>

        <div class="demo-models-footer">
          <p class="demo-models-note">
            <i class="fas fa-info-circle"></i>
            Esses são modelos de demonstração. Seu site será personalizado com as cores, textos e imagens da sua empresa.
          </p>
          <a v-if="isManualMode" :href="whatsappLink('Quero criar meu site')" target="_blank" class="btn-primary">
            <i class="fab fa-whatsapp"></i>
            Quero um site profissional
          </a>
          <router-link v-else to="/configurador?plan=site_complete" class="btn-primary">
            <i class="fas fa-rocket"></i>
            Quero um site profissional
          </router-link>
        </div>
      </div>
    </section>

    <!-- Credibilidade se decide em segundos -->
    <section class="benefits-section" data-scroll>
      <div class="section-container">
        <div class="section-header">
          <span class="section-badge">Dados Comprovados</span>
          <h2 class="section-title">Credibilidade se decide em segundos</h2>
          <p class="section-description">
            A primeira impressão do seu site determina se um cliente confia ou abandona sua empresa.
          </p>
        </div>

        <div class="benefits-grid">
          <div class="benefit-card" data-scroll>
            <div class="benefit-icon">
              <i class="fas fa-eye"></i>
            </div>
            <h3 class="benefit-title">46,1% avaliam pela aparência</h3>
            <p class="benefit-description">
              <strong>46,1%</strong> das pessoas avaliam a credibilidade de uma empresa com base no <strong>visual do site</strong> 
              (layout, tipografia, cores).
            </p>
            <p class="benefit-source">
              <a href="https://simson.net/ref/2002/stanfordPTL.pdf" target="_blank" rel="noopener">
                <i class="fas fa-external-link-alt"></i> Fonte: Stanford Web Credibility Research
              </a>
            </p>
          </div>

          <div class="benefit-card" data-scroll>
            <div class="benefit-icon">
              <i class="fas fa-bolt"></i>
            </div>
            <h3 class="benefit-title">50 milissegundos para impressionar</h3>
            <p class="benefit-description">
              A primeira impressão do visual do seu site acontece em cerca de <strong>50 milissegundos (0,05s)</strong>. 
              Você tem menos de um piscar de olhos para transmitir profissionalismo.
            </p>
            <p class="benefit-source">
              <a href="https://www.tandfonline.com/doi/abs/10.1080/01449290500330448" target="_blank" rel="noopener">
                <i class="fas fa-external-link-alt"></i> Fonte: Taylor & Francis Online
              </a>
            </p>
          </div>

          <div class="benefit-card" data-scroll>
            <div class="benefit-icon">
              <i class="fas fa-times-circle"></i>
            </div>
            <h3 class="benefit-title">38% abandonam por design ruim</h3>
            <p class="benefit-description">
              <strong>38%</strong> das pessoas param de engajar com um site se o conteúdo ou layout for 
              <strong>pouco atraente</strong>. Design importa para conversão.
            </p>
            <p class="benefit-source">
              <a href="https://newflux.fr/files/content/dam/adobe/en/max/2015/pdfs/state-of-content-oct.pdf" target="_blank" rel="noopener">
                <i class="fas fa-external-link-alt"></i> Fonte: Adobe State of Content
              </a>
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Prova Social / Números -->
    <section class="social-proof-section" data-scroll>
      <div class="section-container">
        <div class="stats-grid">
          <div class="stat-item" data-scroll>
            <div class="stat-number">5+</div>
            <div class="stat-label">Anos de empresa</div>
          </div>
          <div class="stat-item" data-scroll>
            <div class="stat-number">100+</div>
            <div class="stat-label">Projetos entregues</div>
          </div>
          <div class="stat-item" data-scroll>
            <div class="stat-number">10</div>
            <div class="stat-label">Dias para entrega</div>
          </div>
          <div class="stat-item" data-scroll>
            <div class="stat-number">24/7</div>
            <div class="stat-label">Monitoramento ativo</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Feito pela Unli -->
    <section class="includes-section" data-scroll>
      <div class="section-container">
        <div class="section-header">
          <span class="section-badge">Autoridade</span>
          <h2 class="section-title">Não se aventure com freelas: contrate uma empresa com 5 anos de entrega</h2>
          <p class="section-description">
            A Unli tem <strong>5 anos de empresa</strong> e experiência real entregando projetos para negócios exigentes. 
            Aqui, você contrata um <strong>processo validado</strong>, com padrão de qualidade e previsibilidade de entrega.
          </p>
        </div>

        <div class="includes-content">
          <div class="includes-visual">
            <div class="feature-showcase">
              <div class="showcase-item active">
                <i class="fas fa-laptop-code"></i>
                <span>Site Personalizado</span>
              </div>
              <div class="showcase-item">
                <i class="fas fa-mobile-alt"></i>
                <span>100% Responsivo</span>
              </div>
              <div class="showcase-item">
                <i class="fas fa-globe"></i>
                <span>Domínio Incluso</span>
              </div>
            </div>
          </div>

          <div class="includes-list">
            <div class="include-item" data-scroll>
              <div class="include-icon">
                <i class="fas fa-check-circle"></i>
              </div>
              <div class="include-content">
                <h4 class="include-title">Domínio + Hospedagem + SSL Inclusos</h4>
                <p class="include-description">
                  Seu próprio endereço na internet (ex: suaempresa.com.br), servidor rápido e seguro, 
                  e certificado SSL (cadeado HTTPS) inclusos. <strong>Sem surpresas na fatura.</strong>
                </p>
              </div>
            </div>

            <div class="include-item" data-scroll>
              <div class="include-icon">
                <i class="fas fa-check-circle"></i>
              </div>
              <div class="include-content">
                <h4 class="include-title">Design profissional e responsivo</h4>
                <p class="include-description">
                  Layout moderno, 100% responsivo (funciona perfeitamente em celulares, tablets e desktops) 
                  e adaptado à identidade visual da sua marca.
                </p>
              </div>
            </div>

            <div class="include-item" data-scroll>
              <div class="include-icon">
                <i class="fas fa-check-circle"></i>
              </div>
              <div class="include-content">
                <h4 class="include-title">Estrutura completa (seções essenciais)</h4>
                <p class="include-description">
                  Seções estratégicas: Home, Serviços, Sobre e Contato/WhatsApp. 
                  Estrutura completa para apresentar sua empresa profissionalmente.
                </p>
              </div>
            </div>

            <div class="include-item" data-scroll>
              <div class="include-icon">
                <i class="fas fa-check-circle"></i>
              </div>
              <div class="include-content">
                <h4 class="include-title">Publicação e configurações técnicas</h4>
                <p class="include-description">
                  DNS configurado, integração WhatsApp. 
                  Tudo pronto para uso imediato.
                </p>
              </div>
            </div>

            <div class="include-item" data-scroll>
              <div class="include-icon">
                <i class="fas fa-check-circle"></i>
              </div>
              <div class="include-content">
                <h4 class="include-title">Painel administrativo para editar conteúdo</h4>
                <p class="include-description">
                  Você tem acesso completo ao painel para editar textos, preços e informações livremente, quando quiser. 
                  Alterações complexas de design/estrutura ficam para nossa revisão anual inclusa.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Como Funciona -->
    <section id="como-funciona" class="process-section" data-scroll>
      <div class="section-container">
        <div class="section-header">
          <span class="section-badge">Processo</span>
          <h2 class="section-title">Simples do início ao fim</h2>
          <p class="section-description">
            Sem atrito, sem burocracia. Você contrata, nós entregamos.
          </p>
        </div>

        <div class="process-timeline">
          <div class="timeline-item" data-scroll>
            <div class="timeline-number">1</div>
            <div class="timeline-content">
              <h3 class="timeline-title">Você escolhe o modelo e preenche as informações</h3>
              <p class="timeline-description">
                Acesse o configurador, selecione um modelo e preenche os dados da sua empresa. 
                Simples e direto.
              </p>
            </div>
            <div class="timeline-icon">
              <i class="fas fa-cog"></i>
            </div>
          </div>

          <div class="timeline-item" data-scroll>
            <div class="timeline-number">2</div>
            <div class="timeline-content">
              <h3 class="timeline-title">Nós produzimos o site no padrão Unli</h3>
              <p class="timeline-description">
                Nossa equipe desenvolve o site seguindo o padrão de qualidade e design profissional 
                validado pela Unli.
              </p>
            </div>
            <div class="timeline-icon">
              <i class="fas fa-code"></i>
            </div>
          </div>

          <div class="timeline-item" data-scroll>
            <div class="timeline-number">3</div>
            <div class="timeline-content">
              <h3 class="timeline-title">Publicamos e deixamos tudo funcionando no seu domínio</h3>
              <p class="timeline-description">
                Configuramos DNS, emails, SSL e deixamos tudo pronto. 
                Seu site está no ar, profissional e seguro.
              </p>
            </div>
            <div class="timeline-icon">
              <i class="fas fa-rocket"></i>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Planos e Preços -->
    <section id="planos" class="pricing-section" data-scroll>
      <div class="section-container">
        <div class="section-header">
          <span class="section-badge">Investimento</span>
          <h2 class="section-title">Escolha o ponto de partida ideal para sua empresa</h2>
          <p class="section-description">
            Comece com uma Landing Page ou monte um site completo. Todos com renovação anual e suporte incluso.
          </p>
        </div>

        <div class="pricing-cards" ref="pricingCards">
          <!-- Plano Básico -->
          <div class="pricing-card" data-scroll>
            <div class="promo-badge-card">30% OFF</div>
            <div class="pricing-header">
              <h3 class="pricing-name">Landing Page (Página Única)</h3>
              <div class="pricing-price">
                <div class="price-original">De R$ 97,02/mês</div>
                <div class="price-wrapper">
                  <span class="price-currency">R$</span>
                  <span class="price-amount">74,62</span>
                  <span class="price-period">/mês</span>
                </div>
              </div>
              <p class="pricing-description">ou R$ 779 à vista</p>
            </div>

            <ul class="pricing-features">
              <li><i class="fas fa-check"></i> <strong>Domínio + Hospedagem + SSL</strong></li>
              <li><i class="fas fa-check"></i> <strong>Design profissional e responsivo</strong></li>
              <li><i class="fas fa-check"></i> <strong>Página única</strong></li>
              <li><i class="fas fa-check"></i> <strong>Integração WhatsApp</strong></li>
              <li><i class="fas fa-check"></i> <strong>Publicação completa</strong></li>
                <li><i class="fas fa-check"></i> <strong>Área do cliente para editar seus conteúdos</strong></li>
            </ul>

            <a v-if="isManualMode" :href="whatsappLink('Landing Page (Página Única) - R$ 74,62/mês')" target="_blank" class="pricing-button secondary">
              <i class="fab fa-whatsapp"></i>
              Quero minha Landing Page
            </a>
            <router-link v-else to="/configurador?plan=landing" class="pricing-button secondary">
              <i class="fas fa-rocket"></i>
              Quero minha Landing Page
            </router-link>

            <div class="pricing-guarantee">
              <i class="fas fa-shield-alt"></i>
              <span>Entrega em até 10 dias úteis</span>
            </div>
          </div>

          <!-- Site Mais Personalizado - POPULAR -->
          <div class="pricing-card featured" data-scroll>
            <div class="pricing-badge">Mais Popular</div>
            <div class="promo-badge-card">30% OFF</div>
            <div class="pricing-header">
              <h3 class="pricing-name">Site Multi-Páginas (Expansível)</h3>
              <div class="pricing-price">
                <div class="price-original">De R$ 100,26/mês</div>
                <span class="price-text">A partir de</span>
                <div class="price-wrapper">
                  <span class="price-currency">R$</span>
                  <span class="price-amount">77,12</span>
                  <span class="price-period">/mês</span>
                </div>
              </div>
              <p class="pricing-description">ou R$ 805 à vista</p>
            </div>

            <ul class="pricing-features">
              <li class="included"><i class="fas fa-plus-circle"></i> <strong>Tudo do plano básico +</strong></li>
              <li><i class="fas fa-check"></i> Escolha quantas páginas precisar</li>
              <li><i class="fas fa-check"></i> Adicione vídeos e documentos</li>
              <li><i class="fas fa-check"></i> Personalize o visual</li>
              <li><i class="fas fa-check"></i> Configurador interativo</li>
              <li><i class="fas fa-check"></i> Formulários para contato</li>
            </ul>

            <a v-if="isManualMode" :href="whatsappLink('Site Multi-Páginas (Expansível) - A partir de R$ 77,12/mês')" target="_blank" class="pricing-button">
              <i class="fab fa-whatsapp"></i>
              Configurar meu site
            </a>
            <router-link v-else to="/configurador?plan=site_complete" class="pricing-button">
              <i class="fas fa-cog"></i>
              Configurar meu site
            </router-link>

            <div class="pricing-guarantee">
              <i class="fas fa-shield-alt"></i>
              <span>Entrega em até 10 dias úteis</span>
            </div>
          </div>

          <!-- Projeto Sob Medida -->
          <div class="pricing-card custom" data-scroll>
            <div class="pricing-header">
              <h3 class="pricing-name">Projeto Sob Medida</h3>
              <div class="pricing-price">
                <span class="price-text">Sob Proposta</span>
              </div>
              <p class="pricing-description">Para necessidades mais complexas</p>
            </div>

            <ul class="pricing-features">
              <li class="included"><i class="fas fa-plus-circle"></i> <strong>Tudo das opções anteriores +</strong></li>
              <li><i class="fas fa-check"></i> E-commerce completo</li>
              <li><i class="fas fa-check"></i> Área do cliente / Login</li>
              <li><i class="fas fa-check"></i> Integrações complexas</li>
              <li><i class="fas fa-check"></i> Backend / Painel admin</li>
              <li><i class="fas fa-check"></i> Funcionalidades customizadas</li>
              <li><i class="fas fa-check"></i> Alterações ilimitadas*</li>
            </ul>

            <p class="pricing-custom-note">* Dentro do escopo acordado em contrato</p>

            <a :href="whatsappLink('Projeto Sob Medida - Sob Proposta')" target="_blank" class="pricing-button">
              <i class="fab fa-whatsapp"></i>
              Consultar Especialistas
            </a>

            <div class="pricing-guarantee">
              <i class="fas fa-shield-alt"></i>
              <span>Prazo sob proposta</span>
            </div>
          </div>
        </div>
        <p class="pricing-scroll-hint">
          <i class="fas fa-arrows-alt-h"></i>
          Deslize para ver todos os planos
        </p>
      </div>
    </section>

    <!-- Regras do Plano -->
    <section class="policies-section" data-scroll>
      <div class="section-container">
        <div class="section-header">
          <span class="section-badge">Transparência</span>
          <h2 class="section-title">Como garantimos entrega em 10 dias e preço acessível</h2>
          <p class="section-description">
            Nosso processo otimizado permite entregar sites de alta qualidade 
            rapidamente e com o melhor custo-benefício do mercado.
          </p>
        </div>

        <div class="policies-grid">
          <div class="policy-card" data-scroll>
            <div class="policy-icon">
              <i class="fas fa-clock"></i>
            </div>
            <h3 class="policy-title">Prazo de Entrega</h3>
            <p class="policy-description">
              <strong>Até 10 dias úteis</strong> após confirmação do pagamento 
              e recebimento completo dos materiais (textos, imagens e dados).
            </p>
            <div class="policy-note">
              A produção inicia após pagamento confirmado para garantir 
              segurança do processo e reserva de agenda.
            </div>
          </div>

          <div class="policy-card" data-scroll>
            <div class="policy-icon">
              <i class="fas fa-shield-alt"></i>
            </div>
            <h3 class="policy-title">Garantia de Site Online</h3>
            <p class="policy-description">
              <strong>Monitoramos seu site</strong> para garantir que ele esteja sempre no ar. 
              Qualquer instabilidade técnica, erro de carregamento ou problema de hospedagem é por nossa conta.
            </p>
            <div class="policy-note">
              💡 Foque no seu negócio, nós cuidamos da parte técnica. 
              Suporte prioritário para garantir seu site funcionando 24/7.
            </div>
          </div>

          <div class="policy-card" data-scroll>
            <div class="policy-icon">
              <i class="fas fa-sync-alt"></i>
            </div>
            <h3 class="policy-title">Atualização Anual Garantida</h3>
            <p class="policy-description">
              Para manter seu site sempre atual, garantimos <strong>uma atualização completa 
              de conteúdo por ano</strong>. Atualize textos, fotos, horários e informações de contato.
            </p>
            <div class="policy-note">
              💡 Pequenos ajustes de contato (telefone, e-mail, redes sociais) podem 
              ser solicitados a qualquer momento. Para atualizações urgentes, temos pacotes Express super acessíveis.
            </div>
          </div>

          <div class="policy-card" data-scroll>
            <div class="policy-icon">
              <i class="fas fa-bullseye"></i>
            </div>
            <h3 class="policy-title">Foco em Alta Conversão</h3>
            <p class="policy-description">
              Desenvolvemos <strong>Landing Pages otimizadas</strong> (Single Page) focadas 
              exclusivamente em apresentar seu negócio e converter visitantes em clientes. Sem distrações.
            </p>
            <div class="policy-note">
              💡 Precisa de e-commerce, área de membros ou múltiplas páginas? 
              Temos planos específicos para necessidades avançadas.
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section class="faq-section" data-scroll>
      <div class="section-container">
        <div class="section-header">
          <span class="section-badge">Dúvidas Frequentes</span>
          <h2 class="section-title">Perguntas e Respostas</h2>
          <p class="section-description">
            Tire suas dúvidas sobre o plano, processo e o que está incluso.
          </p>
        </div>

        <div class="faq-list">
          <div 
            v-for="(faq, index) in faqs" 
            :key="index" 
            class="faq-item" 
            :class="{ open: openFaqs.includes(index) }"
          >
            <button class="faq-question" @click="toggleFaq(index)" :aria-expanded="String(openFaqs.includes(index))">
              <i class="fas fa-question-circle"></i>
              <h3>{{ faq.question }}</h3>
              <i class="fas fa-chevron-down faq-chevron" aria-hidden="true"></i>
            </button>
            <div class="faq-answer">
              <div class="faq-answer-inner" v-html="faq.answer"></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Final -->
    <section class="cta-section" data-scroll>
      <div class="cta-container">
        <div class="cta-content">
          <span class="cta-badge">
            <i class="fas fa-star"></i>
            Processo Validado
          </span>
          <h2 class="cta-title">
            Sua empresa merece transmitir profissionalismo online
          </h2>
          <p class="cta-description">
            Clientes decidem em segundos se confiam ou não. Não deixe um site amador prejudicar sua imagem. 
            Contrate um processo com padrão de qualidade e entrega previsível.
          </p>
          
          <div class="cta-features">
            <div class="cta-feature">
              <i class="fas fa-bolt"></i>
              <span>Entrega em até 10 dias</span>
            </div>
            <div class="cta-feature">
              <i class="fas fa-dollar-sign"></i>
              <span>R$ 779/ano (~R$ 64,92/mês)</span>
            </div>
            <div class="cta-feature">
              <i class="fas fa-building"></i>
              <span>+ de 5 anos de experiência</span>
            </div>
          </div>

          <div class="cta-actions">
            <a v-if="isManualMode" :href="whatsappLink('Quero criar meu site')" target="_blank" class="btn-primary large">
              <i class="fab fa-whatsapp"></i>
              Quero meu site
            </a>
            <router-link v-else to="/configurador?plan=site_complete" class="btn-primary large">
              <i class="fas fa-rocket"></i>
              Quero meu site
            </router-link>
            <a :href="whatsappLink('Falar com Especialista')" target="_blank" class="btn-secondary large">
              <i class="fab fa-whatsapp"></i>
              Falar com Especialista
            </a>
          </div>

          <div class="cta-guarantee">
            <i class="fas fa-check-circle"></i>
            <span>Tudo incluso: domínio, hospedagem, SSL, site e publicação.</span>
          </div>
        </div>
      </div>
    </section>

    <!-- WhatsApp Flutuante -->
    <a :href="`https://wa.me/${whatsappSDR}?text=${encodeURIComponent('Olá! Vi a página de Site Vitrine e gostaria de saber mais sobre os planos.' + affiliateSuffix)}`" 
       class="whatsapp-float"
       target="_blank"
       rel="noopener noreferrer"
       aria-label="Contato via WhatsApp">
      <i class="fab fa-whatsapp"></i>
      <span class="whatsapp-text">Fale Conosco</span>
    </a>
  </div>
</template>

<script>
export default {
  name: 'SiteVitrine',
  data() {
    return {
      openFaqs: [],
      faqs: [
        {
          question: 'Posso cancelar?',
          answer: 'Sim. Você pode cancelar em até <strong>7 dias</strong> após a compra e receber reembolso integral, desde que o site ainda não tenha sido publicado. O estorno é processado em até <strong>30 dias</strong> após o cancelamento, a depender da empresa de pagamentos.'
        },
        {
          question: 'Isso é um site completo?',
          answer: 'Sim! É um site vitrine profissional <strong>single page</strong> (uma página) com todas as seções necessárias: apresentação, serviços, diferenciais, prova social, FAQ e contato. Ideal para empresas que precisam de presença online rápida e eficiente.'
        },
        {
          question: 'Posso pedir um e-commerce?',
          answer: 'E-commerce não está incluído no plano de site vitrine. Sites com carrinho de compras, checkout e gestão de pedidos são <strong>projetos sob proposta</strong> devido à complexidade. Entre em contato para orçamento personalizado.'
        },
        {
          question: 'Vocês fazem os textos do site?',
          answer: 'O plano inclui desenvolvimento com os <strong>textos fornecidos por você</strong>. Se precisar de ajuda com copywriting, podemos indicar <strong>parceiros sob consulta</strong>. Guiamos você com checklist do que precisa enviar.'
        },
        {
          question: 'Quanto tempo leva para ficar pronto?',
          answer: '<strong>Até 10 dias úteis</strong> após confirmação do pagamento e envio completo dos materiais. O prazo começa quando você envia textos, imagens e dados solicitados no checklist.'
        },
        {
          question: 'Preciso ter domínio próprio?',
          answer: 'Não! O <strong>domínio está incluso</strong> no plano anual. Você escolhe o nome (.com.br ou .com) e nós cuidamos do registro e configuração. Tudo incluso, sem custos extras.'
        },
        {
          question: 'O site funciona no celular?',
          answer: 'Sim! Todos os sites são <strong>100% responsivos</strong>, ou seja, funcionam perfeitamente em celulares, tablets e computadores. Design adaptado para cada dispositivo.'
        },
        {
          question: 'Posso adicionar mais páginas depois?',
          answer: 'Sim! Use nosso <strong>configurador automatizado</strong> para adicionar quantas páginas precisar. Escolha o que deseja, preencha os dados e pronto. Sem reunião necessária.'
        },
        {
          question: 'O que é "suporte de disponibilidade"?',
          answer: 'É o suporte técnico para manter seu site <strong>sempre online e funcionando</strong>. Cuidamos de instabilidades, quedas e erros técnicos. Mudanças de conteúdo/layout são tratadas como alterações (1 inclusa por ano).'
        },
        {
          question: 'E depois de 1 ano, preciso renovar?',
          answer: 'Sim. O plano é <strong>anual</strong> e inclui domínio, hospedagem e SSL. Após 1 ano, você renova para manter o site no ar. Avisamos com antecedência e o processo é simples.'
        }
      ]
    };
  },
  computed: {
    isManualMode() {
      return process.env.VUE_APP_MANUAL_MODE === 'true';
    },
    whatsappSDR() {
      return process.env.VUE_APP_WHATSAPP_SDR || '5511911019666';
    },
    whatsappDisplay() {
      return this.whatsappSDR.replace(/^55(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
    },
    affiliateSuffix() {
      const match = document.cookie.match(/(?:^|; )unli_aff=([a-f0-9]{16})/);
      return match ? `\n\n\ud83d\udcce Indicado pelo afiliado: ${match[1]}` : '';
    }
  },
  mounted() {
    this.initScrollAnimations();
    this.initSmoothScroll();
    // Defer layout-triggering scroll to avoid forced reflow during render
    if ('requestIdleCallback' in window) {
      requestIdleCallback(() => this.scrollPricingToFeatured());
    } else {
      setTimeout(() => this.scrollPricingToFeatured(), 200);
    }
  },
  beforeUnmount() {
    // Cleanup IntersectionObserver
    if (this._scrollObserver) {
      this._scrollObserver.disconnect();
      this._scrollObserver = null;
    }
    // Cleanup smooth scroll listeners
    if (this._smoothScrollHandler && this.$el) {
      this.$el.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.removeEventListener('click', this._smoothScrollHandler);
      });
    }
  },
  methods: {
    whatsappLink(planName) {
      const affSuffix = this.affiliateSuffix;
      const msg = `Olá! Tenho interesse no pacote *${planName}*. Gostaria de mais informações.${affSuffix}`;
      return `https://wa.me/${this.whatsappSDR}?text=${encodeURIComponent(msg)}`;
    },
    // Scroll carrossel de preços até o card popular (meio) no mobile
    scrollPricingToFeatured() {
      this.$nextTick(() => {
        if (window.innerWidth <= 768 && this.$refs.pricingCards) {
          const grid = this.$refs.pricingCards;
          const featured = grid.querySelector('.pricing-card.featured');
          if (featured) {
            const gridRect = grid.getBoundingClientRect();
            const cardRect = featured.getBoundingClientRect();
            const offset = cardRect.left - gridRect.left - (gridRect.width - cardRect.width) / 2 + grid.scrollLeft;
            grid.scrollLeft = offset;
          }
        }
      });
    },

    toggleFaq(index) {
      const i = this.openFaqs.indexOf(index);
      if (i === -1) {
        this.openFaqs.push(index);
      } else {
        this.openFaqs.splice(i, 1);
      }
    },

    initScrollAnimations() {
      const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
      };

      this._scrollObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('animate-in');
            this._scrollObserver.unobserve(entry.target);
          }
        });
      }, observerOptions);

      this.$el.querySelectorAll('[data-scroll]').forEach(el => {
        this._scrollObserver.observe(el);
      });
    },
    
    initSmoothScroll() {
      this._smoothScrollHandler = function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      };
      this.$el.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', this._smoothScrollHandler);
      });
    }
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/__colors.scss';
@import '@/assets/sass/settings/__fonts.scss';
@import '@/assets/sass/settings/__breakpoints.scss';
@import '@/assets/sass/settings/__mixin.scss';

// ==========================================
// PÁGINA SITE VITRINE - SALES PAGE
// ==========================================

.site-vitrine-page {
  width: 100%;
}

// Animation utilities
[data-scroll] {
  opacity: 0;
  transform: translateY(30px);
  transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);

  &.animate-in {
    opacity: 1;
    transform: translateY(0);
  }
}

// Hero is always visible (no fade-in delay = faster LCP)
[data-scroll-skip] {
  opacity: 1;
  transform: none;
}

// ==========================================
// HERO SECTION
// ==========================================
.hero-section {
  position: relative;
  min-height: 100vh;
  display: flex;
  align-items: center;
  padding: 120px 20px 80px;
  overflow: hidden;
  background: linear-gradient(135deg, $gray-darkness 0%, $black 100%);

  .hero-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;

    .hero-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: radial-gradient(circle at 20% 50%, rgba($p-color, 0.1) 0%, transparent 50%);
    }
  }

  .hero-container {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
    position: relative;
    z-index: 1;

    @media (max-width: 968px) {
      grid-template-columns: 1fr;
      text-align: center;
    }
  }

  .hero-content {
    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 20px;
      background: rgba($p-color, 0.1);
      border: 1px solid rgba($p-color, 0.3);
      border-radius: 50px;
      color: $p-color;
      font-size: 0.9rem;
      font-weight: 600;
      margin-bottom: 24px;

      i {
        font-size: 1rem;
      }
    }

    .hero-title {
      font-size: 3.5rem;
      font-weight: 800;
      color: $white;
      line-height: 1.2;
      margin-bottom: 24px;

      .highlight-text {
        color: $p-color;
        position: relative;
        display: inline-block;
      }

      @media (max-width: 968px) {
        font-size: 2.5rem;
      }
    }

    .hero-description {
      font-size: 1.25rem;
      color: $gray;
      line-height: 1.8;
      margin-bottom: 32px;
      max-width: 560px;

      @media (max-width: 968px) {
        margin: 0 auto 32px;
      }
    }

    .hero-price-highlight {
      margin-bottom: 40px;

      .price-box {
        position: relative;
        
        .promo-badge {
          position: absolute;
          top: -12px;
          right: -12px;
          background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
          color: white;
          padding: 6px 14px;
          border-radius: 20px;
          font-size: 0.85rem;
          font-weight: 700;
          box-shadow: 0 4px 12px rgba(255, 107, 107, 0.4);
          animation: pulse 2s ease-in-out infinite;
        }
        display: inline-flex;
        flex-direction: column;
        padding: 24px 32px;
        background: linear-gradient(135deg, rgba($p-color, 0.1) 0%, rgba($p-dark, 0.05) 100%);
        border: 2px solid rgba($p-color, 0.3);
        border-radius: 16px;
        text-align: center;

        .price-label {
          font-size: 0.9rem;
          color: $gray;
          font-weight: 500;
          margin-bottom: 8px;
        }

        .price-value {
          font-size: 3rem;
          font-weight: 900;
          color: $p-color;
          line-height: 1;

          .price-period {
            font-size: 1.5rem;
            font-weight: 600;
            color: $gray;
          }
        }

        .price-subtext {
          font-size: 0.9rem;
          color: $gray-light;
          margin-top: 8px;
        }
      }
    }

    .hero-actions {
      display: flex;
      gap: 16px;
      margin-bottom: 40px;

      @media (max-width: 968px) {
        justify-content: center;
        flex-wrap: wrap;
      }

      .btn-primary,
      .btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 16px 32px;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;

        i {
          font-size: 1.1rem;
        }
      }

      .btn-primary {
        background: $gradient-primary;
        color: $white;
        box-shadow: 0 4px 14px rgba($p-color, 0.4);

        &:hover {
          transform: translateY(-2px);
          box-shadow: 0 8px 24px rgba($p-color, 0.5);
        }
      }

      .btn-secondary {
        background: rgba($white, 0.1);
        color: $white;
        border: 2px solid rgba($white, 0.2);

        &:hover {
          background: rgba($white, 0.15);
          border-color: rgba($white, 0.3);
        }
      }
    }

    .hero-trust {
      display: flex;
      gap: 32px;
      padding-top: 24px;
      border-top: 1px solid rgba($white, 0.1);

      @media (max-width: 968px) {
        justify-content: center;
      }

      .trust-item {
        display: flex;
        align-items: center;
        gap: 10px;
        color: $gray;
        font-size: 0.9rem;

        i {
          font-size: 1.2rem;
          color: $p-color;
        }
      }
    }
  }

  .hero-visual {
    display: flex;
    justify-content: center;
    align-items: center;

    .mockup-container {
      position: relative;
      width: 100%;
      max-width: 600px;

      .mockup-glow {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 120%;
        height: 120%;
        background: radial-gradient(circle, rgba($p-color, 0.3) 0%, transparent 70%);
        filter: blur(60px);
        animation: pulse 4s ease-in-out infinite;
      }

      .mockup-image {
        position: relative;
        width: 100%;
        height: auto;
        filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.5));
      }

      .mockup-placeholder {
        position: relative;
        width: 100%;
        min-height: 400px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba($white, 0.1) 0%, rgba($gray-light, 0.1) 100%);
        border: 2px dashed rgba($white, 0.3);
        border-radius: 20px;
        padding: 60px;

        i {
          font-size: 6rem;
          color: rgba($white, 0.4);
          margin-bottom: 20px;
        }

        p {
          font-size: 1.1rem;
          color: rgba($white, 0.6);
          text-align: center;
        }
      }
    }
  }
}

@keyframes pulse {
  0%, 100% { opacity: 0.6; transform: translate(-50%, -50%) scale(1); }
  50% { opacity: 1; transform: translate(-50%, -50%) scale(1.1); }
}

@keyframes pulse-badge {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

// ==========================================
// BENEFITS SECTION
// ==========================================
.benefits-section {
  padding: 100px 20px;
  background: $white;

  .benefits-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 32px;
    margin-top: 60px;
  }

  .benefit-card {
    padding: 40px;
    background: $white;
    border: 2px solid $gray-light;
    border-radius: 16px;
    transition: all 0.3s ease;

    &:hover {
      border-color: $p-color;
      transform: translateY(-8px);
      box-shadow: $shadow-xl;
    }

    .benefit-icon {
      width: 64px;
      height: 64px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, $p-color 0%, $p-dark 100%);
      border-radius: 16px;
      margin-bottom: 24px;

      i {
        font-size: 1.8rem;
        color: $white;
      }
    }

    .benefit-title {
      font-size: 1.5rem;
      font-weight: 700;
      color: $gray-darkness;
      margin-bottom: 16px;
    }

    .benefit-description {
      font-size: 1rem;
      color: $gray-medium;
      line-height: 1.7;
      margin-bottom: 16px;
    }

    .benefit-source {
      padding-top: 16px;
      border-top: 1px solid $gray-light;
      margin-top: 16px;

      a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
        color: $gray-medium;
        text-decoration: none;
        transition: all 0.3s ease;

        i {
          font-size: 0.75rem;
        }

        &:hover {
          color: $p-color;
          text-decoration: underline;
        }
      }
    }
  }
}

// ==========================================
// SOCIAL PROOF SECTION
// ==========================================
.social-proof-section {
  padding: 60px 20px;
  background: linear-gradient(135deg, $gray-darkness 0%, $black 100%);

  .stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 40px;
    max-width: 900px;
    margin: 0 auto;
    text-align: center;

    @media (max-width: 768px) {
      grid-template-columns: repeat(2, 1fr);
      gap: 32px;
    }
  }

  .stat-item {
    .stat-number {
      font-size: 3rem;
      font-weight: 900;
      color: $p-color;
      line-height: 1.2;
      margin-bottom: 8px;

      @media (max-width: 768px) {
        font-size: 2.2rem;
      }
    }

    .stat-label {
      font-size: 1rem;
      font-weight: 500;
      color: $gray;
      letter-spacing: 0.02em;

      @media (max-width: 768px) {
        font-size: 0.9rem;
      }
    }
  }
}

// ==========================================
// INCLUDES SECTION
// ==========================================
.includes-section {
  padding: 100px 20px;
  background: $gray-lightness;

  .includes-content {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 60px;
    margin-top: 60px;
    align-items: start;

    @media (max-width: 968px) {
      grid-template-columns: 1fr;
    }
  }

  .includes-visual {
    position: sticky;
    top: 120px;

    .feature-showcase {
      background: $white;
      padding: 40px;
      border-radius: 20px;
      box-shadow: $shadow-lg;

      .showcase-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
        margin-bottom: 16px;
        background: $gray-lightness;
        border-radius: 12px;
        transition: all 0.3s ease;

        &:last-child {
          margin-bottom: 0;
        }

        &.active {
          background: linear-gradient(135deg, rgba($p-color, 0.1) 0%, rgba($p-dark, 0.05) 100%);
          border-left: 4px solid $p-color;
        }

        i {
          font-size: 1.8rem;
          color: $p-color;
        }

        span {
          font-size: 1rem;
          font-weight: 600;
          color: $gray-darkness;
        }
      }
    }
  }

  .includes-list {
    .include-item {
      display: flex;
      gap: 24px;
      margin-bottom: 32px;

      &:last-child {
        margin-bottom: 0;
      }

      .include-icon {
        flex-shrink: 0;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: $p-color;
        border-radius: 12px;

        i {
          font-size: 1.5rem;
          color: $white;
        }
      }

      .include-content {
        .include-title {
          font-size: 1.25rem;
          font-weight: 700;
          color: $gray-darkness;
          margin-bottom: 8px;
        }

        .include-description {
          font-size: 1rem;
          color: $gray-medium;
          line-height: 1.7;
        }
      }
    }
  }
}

// ==========================================
// PROCESS SECTION
// ==========================================
.process-section {
  padding: 100px 20px;
  background: $white;

  .process-timeline {
    max-width: 900px;
    margin: 60px auto 0;
    position: relative;

    &::before {
      content: '';
      position: absolute;
      left: 30px;
      top: 0;
      bottom: 0;
      width: 2px;
      background: linear-gradient(180deg, $p-color 0%, $p-dark 100%);

      @media (max-width: 768px) {
        left: 20px;
      }
    }
  }

  .timeline-item {
    display: grid;
    grid-template-columns: 60px 1fr 80px;
    gap: 32px;
    margin-bottom: 60px;
    position: relative;

    @media (max-width: 768px) {
      grid-template-columns: 40px 1fr;
      gap: 20px;

      .timeline-icon {
        display: none;
      }
    }

    &:last-child {
      margin-bottom: 0;
    }

    .timeline-number {
      width: 60px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: $gradient-primary;
      border-radius: 50%;
      font-size: 1.5rem;
      font-weight: 800;
      color: $white;
      box-shadow: 0 4px 14px rgba($p-color, 0.4);
      position: relative;
      z-index: 1;

      @media (max-width: 768px) {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
      }
    }

    .timeline-content {
      padding: 24px;
      background: $white;
      border: 2px solid $gray-light;
      border-radius: 16px;
      transition: all 0.3s ease;

      .timeline-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: $gray-darkness;
        margin-bottom: 12px;
      }

      .timeline-description {
        font-size: 1rem;
        color: $gray-medium;
        line-height: 1.7;
      }
    }

    .timeline-icon {
      width: 80px;
      height: 80px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: $gray-lightness;
      border-radius: 16px;

      i {
        font-size: 2rem;
        color: $p-color;
      }
    }

    &:hover .timeline-content {
      border-color: $p-color;
      box-shadow: $shadow-lg;
    }
  }
}

// ==========================================
// PRICING SECTION
// ==========================================
.pricing-section {
  padding: 100px 20px;
  background: linear-gradient(135deg, $gray-darkness 0%, $black 100%);

  .section-header {
    .section-title,
    .section-description {
      color: $white;
    }
  }

  .pricing-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 32px;
    margin-top: 60px;
    max-width: 1200px;
    margin-left: auto;
    margin-right: auto;
    align-items: stretch;
  }

  .pricing-card {
    background: $white;
    border-radius: 20px;
    padding: 40px;
    position: relative;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    
    .promo-badge-card {
      position: absolute;
      top: 16px;
      right: 16px;
      background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
      color: white;
      padding: 6px 12px;
      border-radius: 16px;
      font-size: 0.8rem;
      font-weight: 700;
      box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
      z-index: 1;
    }

    &.featured {
      border: 3px solid $p-color;
      box-shadow: 0 0 40px rgba($p-color, 0.3);
      transform: scale(1.05);

      .pricing-badge {
        position: absolute;
        top: -16px;
        left: 50%;
        transform: translateX(-50%);
        background: $gradient-primary;
        color: $white;
        padding: 8px 24px;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: 600;
        box-shadow: 0 4px 14px rgba($p-color, 0.4);
      }
    }

    &.addon {
      border: 2px solid $accent-blue;

      .pricing-price {
        .price-amount {
          color: $accent-blue;
        }
      }
    }

    &.custom {
      border: 2px solid $accent-purple;

      .pricing-price {
        .price-text {
          color: $accent-purple;
          font-size: 2rem;
          font-weight: 700;
        }
      }
    }

    &:hover {
      transform: translateY(-8px);
      box-shadow: $shadow-2xl;
    }

    .pricing-header {
      text-align: center;
      margin-bottom: 32px;
      padding-bottom: 24px;
      border-bottom: 2px solid $gray-light;

      .pricing-name {
        font-size: 1.8rem;
        font-weight: 700;
        color: $gray-darkness;
        margin-bottom: 16px;
      }

      .pricing-price {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        margin-bottom: 16px;
        
        .price-original {
          text-decoration: line-through;
          color: $gray-medium;
          font-size: 1.3rem;
          font-weight: 600;
          opacity: 0.6;
        }

        .price-text {
          font-size: 0.9rem;
          color: $gray-medium;
          font-weight: 500;
          margin-bottom: 4px;
        }
        
        .price-wrapper {
          display: flex;
          align-items: baseline;
          justify-content: center;
        }

        .price-currency {
          font-size: 1.5rem;
          font-weight: 600;
          color: $gray-medium;
          margin-right: 4px;
        }

        .price-amount {
          font-size: 4rem;
          font-weight: 900;
          color: $p-color;
          line-height: 1;
        }

        .price-period {
          font-size: 1.2rem;
          font-weight: 600;
          color: $gray-medium;
          margin-left: 4px;
        }
      }

      .pricing-description {
        font-size: 1rem;
        color: $gray-medium;
      }
    }

    .pricing-features {
      list-style: none;
      padding: 0;
      margin: 0 0 32px 0;
      flex-grow: 1;

      li {
        display: flex;
        align-items: start;
        gap: 12px;
        padding: 12px 0;
        font-size: 1rem;
        color: $gray-medium;

        i {
          flex-shrink: 0;
          margin-top: 2px;
          font-size: 1.2rem;
          color: $p-color;
        }

        &.included {
          color: $p-color;
          font-weight: 600;
          padding-bottom: 16px;
          margin-bottom: 8px;
          border-bottom: 2px solid rgba($p-color, 0.2);

          i {
            color: $p-color;
            font-size: 1.3rem;
          }

          strong {
            color: $p-color;
          }
        }
      }
    }

    .pricing-button {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      width: 100%;
      padding: 16px;
      background: $gradient-primary;
      color: $white;
      border: none;
      border-radius: 12px;
      font-size: 1.1rem;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
      cursor: pointer;

      &:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba($p-color, 0.5);
      }

      &.secondary {
        background: $white;
        color: $p-color;
        border: 2px solid $p-color;

        &:hover {
          background: $p-color;
          color: $white;
        }
      }
    }

    .pricing-guarantee {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      margin-top: 24px;
      padding-top: 24px;
      border-top: 1px solid $gray-light;
      font-size: 0.9rem;
      color: $gray-medium;

      i {
        color: $p-color;
      }
    }

    .pricing-custom-note {
      font-size: 0.85rem;
      color: $gray-medium;
      font-style: italic;
      margin-top: 12px;
      padding: 12px;
      background: rgba($accent-purple, 0.05);
      border-radius: 8px;
      border-left: 3px solid $accent-purple;
    }
  }
}

// ==========================================
// POLICIES SECTION
// ==========================================
.policies-section {
  padding: 100px 20px;
  background: $white;

  .policies-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
    gap: 32px;
    margin-top: 60px;
  }

  .policy-card {
    padding: 32px;
    background: $gray-lightness;
    border-radius: 16px;
    border: 2px solid transparent;
    transition: all 0.3s ease;

    &:hover {
      border-color: $p-color;
      background: $white;
      box-shadow: $shadow-lg;
    }

    .policy-icon {
      width: 56px;
      height: 56px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: $white;
      border-radius: 12px;
      margin-bottom: 20px;

      i {
        font-size: 1.8rem;
        color: $p-color;
      }
    }

    .policy-title {
      font-size: 1.3rem;
      font-weight: 700;
      color: $gray-darkness;
      margin-bottom: 16px;
    }

    .policy-description {
      font-size: 1rem;
      color: $gray-medium;
      line-height: 1.7;
      margin-bottom: 16px;

      strong {
        color: $p-color;
        font-weight: 600;
      }
    }

    .policy-note {
      padding: 12px 16px;
      background: rgba($p-color, 0.05);
      border-left: 3px solid $p-color;
      border-radius: 8px;
      font-size: 0.9rem;
      color: $gray-medium;
      line-height: 1.6;
    }
  }
}

// ==========================================
// UPGRADES SECTION
// ==========================================
.upgrades-section {
  padding: 100px 20px;
  background: $gray-lightness;

  .upgrades-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 32px;
    margin-top: 60px;
  }

  .upgrade-card {
    padding: 40px;
    background: $white;
    border: 2px solid $gray-light;
    border-radius: 20px;
    transition: all 0.3s ease;
    position: relative;

    &.featured {
      border-color: $p-color;
      box-shadow: 0 0 30px rgba($p-color, 0.2);

      .upgrade-badge {
        position: absolute;
        top: -12px;
        right: 24px;
        background: $gradient-primary;
        color: $white;
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
      }
    }

    &.highlight {
      border-color: $accent-purple;
      background: linear-gradient(135deg, rgba($accent-purple, 0.05) 0%, transparent 100%);
    }

    &:hover {
      transform: translateY(-8px);
      box-shadow: $shadow-xl;
    }

    .upgrade-icon {
      width: 64px;
      height: 64px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, $p-color 0%, $p-dark 100%);
      border-radius: 16px;
      margin-bottom: 24px;

      i {
        font-size: 2rem;
        color: $white;
      }
    }

    .upgrade-title {
      font-size: 1.5rem;
      font-weight: 700;
      color: $gray-darkness;
      margin-bottom: 12px;
    }

    .upgrade-price {
      font-size: 1.8rem;
      font-weight: 800;
      color: $p-color;
      margin-bottom: 16px;
    }

    .upgrade-description {
      font-size: 1rem;
      color: $gray-medium;
      line-height: 1.7;
      margin-bottom: 20px;
    }

    .upgrade-features {
      list-style: none;
      padding: 0;
      margin: 0 0 24px 0;

      li {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        font-size: 0.95rem;
        color: $gray-medium;

        i {
          font-size: 1rem;
          color: $p-color;
        }
      }
    }

    .upgrade-details {
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-bottom: 24px;
      padding: 16px;
      background: $gray-lightness;
      border-radius: 12px;

      span {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        color: $gray-medium;

        i {
          color: $p-color;
        }
      }
    }

    .upgrade-note {
      padding: 12px 16px;
      background: rgba($accent-purple, 0.05);
      border-left: 3px solid $accent-purple;
      border-radius: 8px;
      font-size: 0.9rem;
      color: $gray-medium;
      line-height: 1.6;
      margin-bottom: 24px;
    }

    .upgrade-button {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      width: 100%;
      padding: 14px;
      background: $gradient-primary;
      color: $white;
      border: none;
      border-radius: 12px;
      font-size: 1rem;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;

      &:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba($p-color, 0.4);
      }

      &.secondary {
        background: $white;
        color: $p-color;
        border: 2px solid $p-color;

        &:hover {
          background: $p-color;
          color: $white;
        }
      }
    }
  }
}

// ==========================================
// FAQ SECTION
// ==========================================
.faq-section {
  padding: 100px 20px;
  background: $white;

  .faq-list {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-top: 60px;

    @media (max-width: 968px) {
      grid-template-columns: 1fr;
    }
  }

  .faq-item {
    padding: 24px 32px;
    background: $gray-lightness;
    border-radius: 16px;
    border: 2px solid transparent;
    transition: all 0.3s ease;
    align-self: start;

    &:hover {
      border-color: rgba($p-color, 0.3);
    }

    &.open {
      border-color: $p-color;
      background: $white;
      box-shadow: $shadow-lg;

      .faq-question .faq-chevron {
        transform: rotate(180deg);
      }

      .faq-answer {
        max-height: 300px;
        opacity: 1;
        margin-top: 16px;
      }
    }

    .faq-question {
      display: flex;
      align-items: center;
      gap: 16px;
      cursor: pointer;
      background: none;
      border: none;
      padding: 0;
      width: 100%;
      text-align: left;
      font-family: inherit;

      i {
        flex-shrink: 0;
        font-size: 1.5rem;
        color: $p-color;
      }

      h3 {
        font-size: 1.15rem;
        font-weight: 700;
        color: $gray-darkness;
        flex: 1;
        margin: 0;
      }

      .faq-chevron {
        font-size: 1rem;
        color: $gray-medium;
        transition: transform 0.3s ease;
      }
    }

    .faq-answer {
      max-height: 0;
      opacity: 0;
      overflow: hidden;
      transition: max-height 0.4s ease, opacity 0.3s ease, margin-top 0.3s ease;
      margin-top: 0;

      .faq-answer-inner {
        padding-left: 40px;
        font-size: 1rem;
        color: $gray-medium;
        line-height: 1.8;

        :deep(strong) {
          color: $p-color;
          font-weight: 600;
        }

        @media (max-width: 968px) {
          padding-left: 0;
        }
      }
    }
  }
}

// ==========================================
// CTA SECTION
// ==========================================
.cta-section {
  padding: 100px 20px;
  background: linear-gradient(135deg, $p-color 0%, $p-dark 100%);
  position: relative;
  overflow: hidden;

  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></svg>');
    background-size: 50px 50px;
    opacity: 0.3;
  }

  .cta-container {
    max-width: 900px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
  }

  .cta-content {
    text-align: center;

    .cta-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 20px;
      background: rgba($white, 0.2);
      border: 1px solid rgba($white, 0.3);
      border-radius: 50px;
      color: $white;
      font-size: 0.9rem;
      font-weight: 600;
      margin-bottom: 24px;
    }

    .cta-title {
      font-size: 3rem;
      font-weight: 800;
      color: $white;
      line-height: 1.3;
      margin-bottom: 24px;

      @media (max-width: 768px) {
        font-size: 2rem;
      }
    }

    .cta-description {
      font-size: 1.25rem;
      color: rgba($white, 0.9);
      line-height: 1.8;
      margin-bottom: 40px;
    }

    .cta-features {
      display: flex;
      justify-content: center;
      gap: 32px;
      margin-bottom: 40px;
      flex-wrap: wrap;

      .cta-feature {
        display: flex;
        align-items: center;
        gap: 10px;
        color: $white;
        font-size: 1rem;
        font-weight: 500;

        i {
          font-size: 1.2rem;
        }
      }
    }

    .cta-actions {
      display: flex;
      justify-content: center;
      gap: 16px;
      margin-bottom: 32px;
      flex-wrap: wrap;

      .btn-primary,
      .btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 18px 40px;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.3s ease;

        &.large {
          padding: 20px 48px;
        }
      }

      .btn-primary {
        background: $white;
        color: $p-color;

        &:hover {
          transform: translateY(-4px);
          box-shadow: 0 12px 32px rgba($black, 0.3);
        }
      }

      .btn-secondary {
        background: transparent;
        color: $white;
        border: 2px solid $white;

        &:hover {
          background: $white;
          color: $p-color;
        }
      }
    }

    .cta-guarantee {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      color: rgba($white, 0.9);
      font-size: 1rem;

      i {
        font-size: 1.3rem;
      }
    }
  }
}

// ==========================================
// SECTION UTILITIES
// ==========================================
.section-container {
  max-width: 1200px;
  margin: 0 auto;
}

.section-header {
  text-align: center;
  margin-bottom: 60px;

  .section-badge {
    display: inline-block;
    padding: 8px 20px;
    background: rgba($p-color, 0.1);
    border: 1px solid rgba($p-color, 0.3);
    border-radius: 50px;
    color: $p-color;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 16px;
  }

  .section-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: $gray-darkness;
    line-height: 1.3;
    margin-bottom: 16px;

    @media (max-width: 768px) {
      font-size: 2rem;
    }
  }

  .section-description {
    font-size: 1.1rem;
    color: $gray-medium;
    line-height: 1.8;
    max-width: 700px;
    margin: 0 auto;
  }
}

// ==========================================
// CUSTOM SITE SECTION
// ==========================================
.custom-site-section {
  padding: 100px 20px;
  background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);

  .custom-site-card {
    max-width: 700px;
    margin: 60px auto 0;
    padding: 48px;
    background: $white;
    border: 2px solid rgba($p-color, 0.15);
    border-radius: 20px;
    box-shadow: $shadow-lg;
    text-align: center;
    transition: all 0.3s ease;

    @media (max-width: 768px) {
      padding: 32px 24px;
    }

    &:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 40px rgba($p-color, 0.2);
    }

    .card-icon {
      width: 80px;
      height: 80px;
      margin: 0 auto 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: $gradient-primary;
      border-radius: 20px;
      box-shadow: 0 8px 24px rgba($p-color, 0.3);

      i {
        font-size: 2.5rem;
        color: $white;
      }
    }

    .card-title {
      font-size: 2rem;
      font-weight: 800;
      color: $gray-darkness;
      margin-bottom: 16px;

      @media (max-width: 768px) {
        font-size: 1.6rem;
      }
    }

    .card-price {
      font-size: 2.5rem;
      font-weight: 900;
      background: $gradient-primary;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 24px;

      @media (max-width: 768px) {
        font-size: 2rem;
      }
    }

    .card-description {
      font-size: 1.1rem;
      color: $gray-medium;
      line-height: 1.7;
      margin-bottom: 32px;
    }

    .card-features {
      list-style: none;
      padding: 0;
      margin: 0 0 40px 0;
      text-align: left;

      li {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        font-size: 1rem;
        color: $gray-medium;
        border-bottom: 1px solid $gray-lightness;

        &:last-child {
          border-bottom: none;
        }

        i {
          flex-shrink: 0;
          color: $success;
          font-size: 1.2rem;
        }
      }
    }

    .card-button {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      padding: 16px 48px;
      background: $gradient-primary;
      color: $white;
      border: none;
      border-radius: 12px;
      font-size: 1.1rem;
      font-weight: 700;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.3s ease;

      i {
        transition: transform 0.3s ease;
      }

      &:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba($p-color, 0.5);

        i {
          transform: translateX(4px);
        }
      }
    }
  }
}

// ==========================================
// WHATSAPP FLUTUANTE
// ==========================================
.whatsapp-float {
  position: fixed !important;
  bottom: 30px !important;
  right: 30px !important;
  z-index: 1001 !important;
  display: flex !important;
  align-items: center;
  gap: 12px;
  background: linear-gradient(135deg, #25d366, #128c7e);
  color: white !important;
  text-decoration: none;
  padding: 15px 20px;
  border-radius: 50px;
  box-shadow: 0 6px 25px rgba(37, 211, 102, 0.4);
  transition: all 0.3s ease;
  animation: pulseWhatsapp 3s infinite;
  font-weight: 600;
  font-size: 16px;
  opacity: 1 !important;
  visibility: visible !important;

  &:hover {
    transform: scale(1.1) translateY(-5px);
    box-shadow: 0 8px 30px rgba(37, 211, 102, 0.6);
    text-decoration: none;
  }

  i {
    font-size: 24px;
    animation: bounce 2s infinite;
  }

  .whatsapp-text {
    @media (max-width: 768px) {
      display: none;
    }
  }
}

@keyframes pulseWhatsapp {
  0%, 100% {
    box-shadow: 0 6px 25px rgba(37, 211, 102, 0.4);
  }
  50% {
    box-shadow: 0 8px 35px rgba(37, 211, 102, 0.7);
  }
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% {
    transform: translateY(0);
  }
  40% {
    transform: translateY(-5px);
  }
  60% {
    transform: translateY(-3px);
  }
}

// Hint de carrossel – oculto no desktop
.pricing-scroll-hint {
  display: none;
}

// ==========================================
// MOBILE RESPONSIVE OVERRIDES (<= 768px)
// ==========================================
@media (max-width: 768px) {

  // ----- Espaço seguro para botão WhatsApp flutuante -----
  .site-vitrine-page {
    padding-bottom: 96px;
  }

  // ----- Reduzir padding vertical em todas as seções -----
  .benefits-section,
  .includes-section,
  .process-section,
  .pricing-section,
  .policies-section,
  .faq-section,
  .cta-section,
  .contact-section,
  .custom-site-section,
  .upgrades-section {
    padding-top: 60px;
    padding-bottom: 60px;
    padding-left: 16px;
    padding-right: 16px;
  }

  // ----- Padding lateral consistente em todas as seções -----
  .section-container {
    padding: 0 4px;
  }

  .section-header {
    padding: 0;
    margin-bottom: 40px;

    .section-title {
      font-size: 1.75rem;
      line-height: 1.35;
    }

    .section-description {
      font-size: 1rem;
    }
  }

  // ----- Hero Section -----
  .hero-section {
    padding: 96px 16px 56px;

    .hero-container {
      gap: 32px;
    }

    .hero-title {
      font-size: 2.1rem;
    }

    .hero-description {
      font-size: 1rem;
      line-height: 1.75;
    }

    // Mais espaço entre botões e lista de benefícios
    .hero-actions {
      flex-direction: column;
      gap: 12px;
      margin-bottom: 48px;

      .btn-primary,
      .btn-secondary {
        width: 100%;
        justify-content: center;
        padding: 15px 20px;
        font-size: 1rem;
      }
    }

    // Trust items em coluna no mobile
    .hero-trust {
      flex-direction: column;
      gap: 14px;
      padding-top: 28px;

      .trust-item {
        font-size: 0.88rem;
      }
    }

    // Mockup: esconder no mobile - ocupa muito espaço e distrai
    .hero-visual {
      display: none;
    }
  }

  // ----- Pricing: Carrossel horizontal com scroll-snap -----
  .pricing-section {
    // NÃO usar overflow: clip - corta os cards
    // Deixar overflow padrão na section
    
    .section-container {
      // Container precisa permitir que o carrossel escape
      overflow: visible;
      max-width: none;
    }

    .section-header {
      .section-title,
      .section-description {
        color: $white;
      }
    }

    .pricing-cards {
      // Carrossel escapa do container via margin negativo
      display: flex;
      flex-direction: row;
      margin-left: calc(-50vw + 50%);
      margin-right: calc(-50vw + 50%);
      width: 100vw;
      overflow-x: auto;
      scroll-snap-type: x mandatory;
      -webkit-overflow-scrolling: touch;
      scrollbar-width: none;
      gap: 12px;
      padding: 16px 10vw 28px;
      margin-top: 32px;

      &::-webkit-scrollbar {
        display: none;
      }
    }

    .pricing-card {
      // Card menor = peek dos vizinhos (100vw - padding - gap)
      flex: 0 0 80vw;
      max-width: 80vw;
      scroll-snap-align: center;
      padding: 28px 20px;

      // Featured fica visualmente destacado mas sem mudar ordem
      &.featured {
        transform: scale(1.02);
        box-shadow: 0 12px 40px rgba($p-color, 0.35), 0 0 0 2px $p-color;
        z-index: 2;

        &:hover {
          transform: scale(1.02) translateY(-4px);
        }
      }

      &:hover {
        transform: translateY(-4px);
      }
    }

    // Dica de carrossel visível apenas no mobile
    .pricing-scroll-hint {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-top: 16px;
      color: rgba($white, 0.5);
      font-size: 0.82rem;
      letter-spacing: 0.04em;

      i {
        font-size: 1rem;
        opacity: 0.7;
      }
    }
  }

  // ----- Includes: remover sticky que causava sobreposição -----
  .includes-section {
    .includes-visual {
      position: static !important;
      top: auto !important;
    }

    // Garante height: auto nos containers de include
    .include-item {
      height: auto;
      overflow: visible;

      .include-content {
        height: auto;
        overflow: visible;
      }
    }
  }

  // ----- Policy Cards (Garantia/Prazo): centralizar ícone -----
  .policies-section {
    .policies-grid {
      grid-template-columns: 1fr;
    }

    .policy-card {
      text-align: center;
      padding: 28px 20px;

      .policy-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 20px;

        i {
          font-size: 2rem;
        }
      }

      // Nota de texto alinhada à esquerda dentro do card centralizado
      .policy-note {
        text-align: left;
      }
    }
  }

  // ----- CTA: botões full-width -----
  .cta-section {
    .cta-description {
      font-size: 1rem;
    }

    .cta-features {
      flex-direction: column;
      gap: 14px;
      align-items: flex-start;
    }

    .cta-actions {
      flex-direction: column;

      .btn-primary.large,
      .btn-secondary.large {
        padding: 16px 24px;
        width: 100%;
        justify-content: center;
      }
    }
  }

  // ----- FAQ: padding interno menor -----
  .faq-section {
    .faq-list {
      grid-template-columns: 1fr;
    }

    .faq-item {
      padding: 18px 16px;
    }
  }

  // ----- Benefits: padding interno -----
  .benefits-section {
    .benefit-card {
      padding: 28px 20px;
    }
  }

  // ----- Processo: espaçamento -----
  .process-section {
    .timeline-item {
      gap: 16px;

      .timeline-content {
        padding: 20px 16px;

        .timeline-title {
          font-size: 1.15rem;
        }
      }
    }
  }

  // ----- WhatsApp flutuante: margem segura em telas pequenas -----
  .whatsapp-float {
    bottom: 20px !important;
    right: 16px !important;
    padding: 12px 16px;
    font-size: 14px;

    i {
      font-size: 20px;
    }
  }
}

// Extra-small phones (<= 420px)
@media (max-width: 420px) {
  .pricing-section .pricing-card {
    flex: 0 0 88vw;
  }

  .hero-section .hero-title {
    font-size: 1.85rem;
  }

  .hero-section .hero-price-highlight .price-box .price-value {
    font-size: 2.4rem;
  }
}

// ==========================================
// DEMO MODELS SECTION
// ==========================================
.demo-models-section {
  padding: 80px 0 60px;
  background: $gray-lightness;

  .section-header {
    margin-bottom: 48px;
  }
}

.demo-models-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 28px;

  @media (max-width: 900px) {
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
  }

  @media (max-width: 600px) {
    grid-template-columns: 1fr;
  }
}

.demo-model-card {
  background: #fff;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 4px 24px rgba(0,0,0,0.07);
  text-decoration: none;
  color: inherit;
  display: flex;
  flex-direction: column;
  transition: transform 0.25s, box-shadow 0.25s;
  border: 2px solid transparent;

  &:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 48px rgba(0,0,0,0.14);
    border-color: rgba($p-color, 0.35);

    .demo-model-cta {
      color: $p-dark;
      gap: 8px;
    }
  }
}

// ---- Mini browser frame ----
.demo-model-preview {
  width: 100%;
  border-bottom: 1px solid rgba(0,0,0,0.06);
  overflow: hidden;
}

.preview-browser {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f1f3f5;
  padding: 7px 12px;
  border-bottom: 1px solid #e0e0e0;

  .browser-bar-icons {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;

    i {
      font-size: 11px;
      color: $p-color;
    }
  }

  .browser-url {
    background: #fff;
    border-radius: 4px;
    padding: 2px 10px;
    font-size: 10px;
    color: #666;
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    border: 1px solid #e0e0e0;
  }
}

.preview-content {
  padding: 0;
  height: 190px;
  overflow: hidden;
  position: relative;
}

// --- Hamburgueria preview ---
.hb-preview-content {
  background: linear-gradient(135deg, #1a0a00, #3d1c02);

  .preview-header-bar {
    background: rgba(0,0,0,0.4);
    color: #f39c12;
    font-size: 11px;
    font-weight: 700;
    padding: 6px 12px;
  }

  .preview-hero-mini {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;

    .preview-emoji { font-size: 36px; flex-shrink: 0; }

    .preview-text-lines {
      display: flex;
      flex-direction: column;
      gap: 5px;
      flex: 1;

      .line-title {
        height: 10px;
        width: 80%;
        background: rgba(255,255,255,0.6);
        border-radius: 4px;
      }
      .line-sub {
        height: 7px;
        width: 60%;
        background: rgba(255,255,255,0.3);
        border-radius: 4px;
      }
      .line-btn {
        height: 22px;
        width: 100px;
        border-radius: 6px;
        &.wpp { background: #25d366; }
      }
    }
  }

  .preview-cards-row {
    display: flex;
    gap: 8px;
    padding: 0 14px;

    .preview-card-mini {
      background: rgba(255,255,255,0.1);
      border-radius: 10px;
      width: 60px;
      height: 48px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
    }
  }
}

// --- Loja preview ---
.lj-preview-content {
  background: linear-gradient(135deg, #fdf2f8, #fce7f3);

  .lj-header-bar {
    background: #fff;
    color: #c2185b;
    font-size: 11px;
    font-weight: 700;
    padding: 6px 12px;
    border-bottom: 1px solid rgba(233,30,140,0.1);
  }

  .lj-hero-mini {
    background: linear-gradient(135deg, #1a0533, #4a0e5c);
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;

    .preview-emoji { font-size: 32px; flex-shrink: 0; }

    .preview-text-lines {
      display: flex;
      flex-direction: column;
      gap: 5px;
      flex: 1;

      .line-title { height: 10px; width: 80%; background: rgba(255,255,255,0.6); border-radius: 4px; }
      .line-sub { height: 7px; width: 55%; background: rgba(255,255,255,0.3); border-radius: 4px; }
      .line-btn.pink { height: 22px; width: 90px; background: #e91e8c; border-radius: 6px; }
    }
  }

  .preview-products-row {
    display: flex;
    gap: 8px;
    padding: 8px 14px;

    .preview-product {
      flex: 1;
      background: #fff;
      border-radius: 10px;
      padding: 8px 6px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 4px;
      font-size: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.07);

      span { font-size: 9px; font-weight: 700; color: #1a0533; }
    }
  }
}

// --- Clínica preview ---
.cl-preview-content {
  background: linear-gradient(135deg, #fdf9ff, #fce7f3);

  .cl-header-bar {
    background: #fff;
    color: #be185d;
    font-size: 11px;
    font-weight: 700;
    padding: 6px 12px;
    border-bottom: 1px solid rgba(236,72,153,0.1);
  }

  .cl-hero-mini {
    background: linear-gradient(135deg, #fce7f3, #f9d0e6);
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;

    .preview-emoji { font-size: 32px; flex-shrink: 0; }

    .preview-text-lines {
      display: flex;
      flex-direction: column;
      gap: 5px;
      flex: 1;

      .line-title { height: 10px; width: 80%; background: rgba(30,10,46,0.4); border-radius: 4px; }
      .line-sub { height: 7px; width: 55%; background: rgba(30,10,46,0.2); border-radius: 4px; }
      .line-btn.rose { height: 22px; width: 90px; background: #ec4899; border-radius: 6px; }
    }
  }

  .preview-services-row {
    display: flex;
    gap: 8px;
    padding: 8px 14px;

    .preview-service-mini {
      flex: 1;
      background: #fff;
      border-radius: 10px;
      padding: 8px 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    }
  }
}

// --- Serralheria preview ---
.sr-preview-content {
  background: #0f0f0f;

  .sr-header-bar {
    background: rgba(255,255,255,0.05);
    color: #f97316;
    font-size: 11px;
    font-weight: 700;
    padding: 6px 12px;
    border-bottom: 1px solid rgba(249,115,22,0.2);
  }

  .sr-hero-mini {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;

    .preview-emoji { font-size: 32px; flex-shrink: 0; }

    .preview-text-lines {
      display: flex;
      flex-direction: column;
      gap: 5px;
      flex: 1;

      .line-title { height: 10px; width: 80%; background: rgba(255,255,255,0.6); border-radius: 4px; }
      .line-sub { height: 7px; width: 55%; background: rgba(255,255,255,0.3); border-radius: 4px; }
      .line-btn.orange { height: 22px; width: 100px; background: #f97316; border-radius: 6px; }
    }
  }

  .preview-services-row {
    display: flex;
    gap: 8px;
    padding: 0 14px;

    .preview-service-mini {
      flex: 1;
      background: rgba(255,255,255,0.06);
      border-radius: 10px;
      padding: 10px 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      border: 1px solid rgba(255,255,255,0.08);
    }
  }
}

// ---- Card info area ----
.demo-model-info {
  padding: 20px 22px 22px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}

.demo-model-tag {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 700;
  padding: 3px 12px;
  border-radius: 20px;
  width: fit-content;
  text-transform: uppercase;
  letter-spacing: 0.5px;

  &.hamburgueria-tag { background: #fff3cd; color: #92400e; }
  &.loja-tag { background: #fce7f3; color: #be185d; }
  &.clinica-tag { background: #ffd6eb; color: #9d174d; }
  &.serralheria-tag { background: #fff0e6; color: #c2410c; }
}

.demo-model-title {
  font-size: 16px;
  font-weight: 800;
  color: $gray-darkness;
  margin: 0;
  line-height: 1.3;
}

.demo-model-desc {
  font-size: 13px;
  color: $gray-medium;
  line-height: 1.6;
  margin: 0;
  flex: 1;
}

.demo-model-cta {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 700;
  color: $p-color;
  margin-top: 4px;
  transition: gap 0.2s, color 0.2s;

  i { font-size: 12px; }
}

.demo-models-footer {
  margin-top: 40px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 20px;
  padding: 24px 28px;
  background: rgba($p-color, 0.06);
  border-radius: 16px;
  border: 1px solid rgba($p-color, 0.2);

  .demo-models-note {
    font-size: 14px;
    color: $gray-medium;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    max-width: 560px;

    i { color: $p-color; flex-shrink: 0; }
  }

  @media (max-width: 640px) {
    flex-direction: column;
    text-align: center;

    .demo-models-note { justify-content: center; }
  }
}

</style>

