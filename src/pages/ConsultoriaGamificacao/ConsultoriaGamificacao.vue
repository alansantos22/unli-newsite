<template>
  <div class="consultoria-page">
    <!-- Hero Section -->
    <section class="hero-section" data-scroll>
      <div class="hero-container">
        <div class="hero-content">
          <span class="hero-badge">
            <i class="fas fa-gamepad"></i>
            Consultoria Especializada
          </span>
          <h1 class="hero-title">
            Consultoria de Gamificação 
            <span class="highlight-text">+ UX/UI Estratégica</span>
          </h1>
          <p class="hero-description">
            Transforme produtos digitais que "parecem ferramenta" em experiências 
            envolventes que guiam o usuário naturalmente e aumentam conversão.
          </p>
          <div class="hero-stats">
            <div class="stat-item">
              <span class="stat-value">10</span>
              <span class="stat-label">Anos de Experiência</span>
            </div>
            <div class="stat-item">
              <span class="stat-value">Total</span>
              <span class="stat-label">Satisfação</span>
            </div>
            <div class="stat-item">
              <span class="stat-value">Web3</span>
              <span class="stat-label">Especialistas em</span>
            </div>
          </div>
          <div class="hero-actions">
            <a href="#contact" class="btn-primary">
              <i class="fas fa-calendar-alt"></i>
              Agendar Consultoria
            </a>
            <a href="#detalhes" class="btn-secondary">
              <i class="fas fa-info-circle"></i>
              Ver Detalhes
            </a>
          </div>
        </div>
        <div class="hero-visual">
          <!-- 
            SUGESTÃO DE IMAGEM:
            - Smartphone ou tablet mostrando interface gamificada
            - Dashboard com elementos de jogo (pontos, níveis, badges)
            - Pessoa interagindo com app gamificado
            - Mockup de interface com elementos de progresso e recompensas
            - Ou usar uma das imagens 3D/futuristas do Canva: "3D smartphone gamification"
          -->
          <img src="@/assets/img/general/image-consultoria.png" alt="Interface gamificada em smartphone - Dashboard com sistema de pontos, níveis e recompensas" class="hero-image" />
        </div>
      </div>
    </section>

    <!-- Onde Produtos Digitais Perdem Dinheiro -->
    <section id="detalhes" class="problems-section" data-scroll>
      <div class="section-container">
        <div class="section-header">
          <span class="section-badge">Problemas Reais</span>
          <h2 class="section-title">Onde Produtos Digitais <span class="highlight-text">Perdem Dinheiro</span></h2>
          <p class="section-description">
            Identifique os pontos de sangria do seu negócio e quanto isso custa
          </p>
        </div>
        <div class="problems-grid">
          <div class="problem-card" data-stagger>
            <div class="problem-icon">
              <i class="fas fa-question-circle"></i>
            </div>
            <h3 class="problem-title">Falta de Clareza</h3>
            <p class="problem-description">
              O usuário não consegue identificar o próximo passo na jornada, 
              gerando hesitação e insegurança que levam ao abandono.
            </p>
            <div class="problem-impact">
              <strong>Impacto:</strong> Até 70% de abandono nas primeiras telas
            </div>
          </div>

          <div class="problem-card" data-stagger>
            <div class="problem-icon">
              <i class="fas fa-times-circle"></i>
            </div>
            <h3 class="problem-title">Atrito na Jornada</h3>
            <p class="problem-description">
              Excesso de cliques, confusão em processos e retrabalho constante 
              criam barreiras que frustram e afastam usuários.
            </p>
            <div class="problem-impact">
              <strong>Impacto:</strong> Redução de 40% na conversão
            </div>
          </div>

          <div class="problem-card" data-stagger>
            <div class="problem-icon">
              <i class="fas fa-robot"></i>
            </div>
            <h3 class="problem-title">Experiência Mecânica</h3>
            <p class="problem-description">
              O produto "parece ferramenta", não uma experiência envolvente. 
              Falta engajamento emocional e conexão com o usuário.
            </p>
            <div class="problem-impact">
              <strong>Impacto:</strong> Baixa retenção e pouco uso recorrente
            </div>
          </div>

          <div class="problem-card" data-stagger>
            <div class="problem-icon">
              <i class="fas fa-chart-line"></i>
            </div>
            <h3 class="problem-title">Conversão Baixa</h3>
            <p class="problem-description">
              Taxa de conversão e uso recorrente ficam abaixo do potencial real, 
              impactando diretamente o ROI do produto.
            </p>
            <div class="problem-impact">
              <strong>Impacto:</strong> ROI 60% abaixo do potencial
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Método Unli -->
    <section class="methodology-section" data-scroll>
      <div class="section-container">
        <div class="section-header">
          <span class="section-badge">Nossa Metodologia</span>
          <h2 class="section-title">Método Unli: 7 Etapas, 3 Checkpoints</h2>
          <p class="section-description">
            Metodologia estruturada que garante transparência total e aprovações em momentos estratégicos
          </p>
        </div>
        <div class="methodology-timeline">
          <div class="timeline-item" data-stagger>
            <div class="timeline-number">1</div>
            <div class="timeline-content">
              <h4>Conversa Inicial</h4>
              <p>Entendimento do contexto e objetivos</p>
            </div>
          </div>
          
          <div class="timeline-item checkpoint" data-stagger>
            <div class="timeline-number">2</div>
            <div class="timeline-content">
              <h4>Reunião 1 → <strong>Aprovação #1</strong></h4>
              <p>Alinhamento de fluxos e definição de escopo</p>
            </div>
          </div>

          <div class="timeline-item" data-stagger>
            <div class="timeline-number">3</div>
            <div class="timeline-content">
              <h4>UML dos Fluxos</h4>
              <p>Documentação técnica completa</p>
            </div>
          </div>

          <div class="timeline-item checkpoint" data-stagger>
            <div class="timeline-number">4</div>
            <div class="timeline-content">
              <h4>Reunião 2 → <strong>Aprovação #2</strong></h4>
              <p>Validação final dos fluxos mapeados</p>
            </div>
          </div>

          <div class="timeline-item" data-stagger>
            <div class="timeline-number">5</div>
            <div class="timeline-content">
              <h4>Desenho UI</h4>
              <p>Criação das interfaces visuais</p>
            </div>
          </div>

          <div class="timeline-item checkpoint" data-stagger>
            <div class="timeline-number">6</div>
            <div class="timeline-content">
              <h4>Reunião 3 → <strong>Aprovação #3</strong></h4>
              <p>Revisão e validação do design</p>
            </div>
          </div>

          <div class="timeline-item" data-stagger>
            <div class="timeline-number">7</div>
            <div class="timeline-content">
              <h4>Entrega Final</h4>
              <p>Pacote completo de documentação e assets</p>
            </div>
          </div>
        </div>
        <div class="methodology-note">
          <strong>Checkpoints críticos:</strong> Aprovação #2 (fluxos UML) e Aprovação #3 (UI/UX final) 
          garantem que você tem controle total sobre o projeto antes de avançarmos.
        </div>
      </div>
    </section>

    <!-- O Que Você Recebe -->
    <section class="deliverables-section" data-scroll>
      <div class="section-container">
        <div class="section-header">
          <span class="section-badge">O Que Está Incluído</span>
          <h2 class="section-title">Consultoria Completa de Gamificação + UX/UI</h2>
          <p class="section-description">
            Tudo que você precisa para transformar seu produto digital
          </p>
        </div>
        <div class="deliverables-grid">
          <div class="deliverable-card" data-stagger>
            <div class="card-icon">
              <i class="fas fa-sitemap"></i>
            </div>
            <h3 class="card-title">Fluxos Claros</h3>
            <p class="card-description">
              Mapeamento completo da jornada do usuário através de diagramas UML 
              profissionais, eliminando ambiguidade.
            </p>
            <ul class="card-list">
              <li>Diagramas UML completos</li>
              <li>Fluxos de usuário otimizados</li>
              <li>Documentação técnica</li>
            </ul>
          </div>

          <div class="deliverable-card" data-stagger>
            <div class="card-icon">
              <i class="fas fa-gamepad"></i>
            </div>
            <h3 class="card-title">UX/UI Gamificada</h3>
            <p class="card-description">
              Interface redesenhada com princípios de gamificação que guiam o usuário 
              de forma natural e intuitiva.
            </p>
            <ul class="card-list">
              <li>Design system gamificado</li>
              <li>Interfaces finais aprovadas</li>
              <li>Experiência fluida e recompensadora</li>
            </ul>
          </div>

          <div class="deliverable-card" data-stagger>
            <div class="card-icon">
              <i class="fas fa-chart-line"></i>
            </div>
            <h3 class="card-title">Estratégias de Conversão</h3>
            <p class="card-description">
              Propostas práticas e acionáveis focadas em ativação inicial, 
              retenção e monetização sustentável.
            </p>
            <ul class="card-list">
              <li>Mecânicas de engajamento</li>
              <li>Loops de uso recorrente</li>
              <li>Sistemas de progressão</li>
            </ul>
          </div>

          <div class="deliverable-card" data-stagger>
            <div class="card-icon">
              <i class="fas fa-code"></i>
            </div>
            <h3 class="card-title">Desenvolvimento Opcional</h3>
            <p class="card-description">
              Após a consultoria, oferecemos desenvolvimento da aplicação 
              baseado nas documentações validadas.
            </p>
            <ul class="card-list">
              <li>Proposta de desenvolvimento</li>
              <li>Execução ágil e eficiente</li>
              <li>Time já alinhado com o projeto</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Por Que Escolher a Unli -->
    <section class="credentials-section" data-scroll>
      <div class="section-container">
        <div class="section-header">
          <span class="section-badge">Nossa Expertise</span>
          <h2 class="section-title">Por Que Somos Referência em Gamificação</h2>
          <p class="section-description">
            Credenciais e experiências que nos tornam únicos no mercado
          </p>
        </div>
        <div class="credentials-grid">
          <div class="credential-card highlight" data-stagger>
            <div class="credential-icon">
              <i class="fas fa-trophy"></i>
            </div>
            <div class="credential-title">Suzano Rock in Rio</div>
            <div class="credential-description">
              Projeto de gamificação apresentado no maior festival de música do mundo,
              focado em conscientização ambiental
            </div>
            <div class="credential-badge">Evento Global</div>
          </div>

          <div class="credential-card highlight" data-stagger>
            <div class="credential-icon">
              <i class="fas fa-gamepad"></i>
            </div>
            <div class="credential-title">Presença na BGS</div>
            <div class="credential-description">
              Participação ativa na maior feira de games da América Latina,
              com entrevista no Flow Games
            </div>
            <div class="credential-badge">Maior Feira Games BR</div>
          </div>

          <div class="credential-card highlight" data-stagger>
            <div class="credential-icon">
              <i class="fas fa-code"></i>
            </div>
            <div class="credential-title">Expertise Web3</div>
            <div class="credential-description">
              Desenvolvimento de projetos blockchain, NFTs e criptomoedas,
              como o marketplace Parallelium
            </div>
            <div class="credential-badge">Blockchain Expert</div>
          </div>

          <div class="credential-card" data-stagger>
            <div class="credential-icon">
              <i class="fas fa-building"></i>
            </div>
            <div class="credential-title">Clientes Corporativos</div>
            <div class="credential-description">
              Trabalhos para empresas como Suzano, Ponte Preta, Softvision
              e outras organizações de grande porte
            </div>
            <div class="credential-badge">Enterprise</div>
          </div>

          <div class="credential-card" data-stagger>
            <div class="credential-icon">
              <i class="fas fa-clock"></i>
            </div>
            <div class="credential-title">10 Anos de Mercado</div>
            <div class="credential-description">
              Década de experiência criando soluções digitais inovadoras
              e gamificação estratégica
            </div>
            <div class="credential-badge">Veteranos</div>
          </div>

          <div class="credential-card" data-stagger>
            <div class="credential-icon">
              <i class="fas fa-star"></i>
            </div>
            <div class="credential-title">Metodologia Própria</div>
            <div class="credential-description">
              Método Unli validado com 7 etapas e 3 checkpoints para
              garantir transparência total
            </div>
            <div class="credential-badge">Método Unli</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Como Funciona -->
    <section class="how-it-works-section" data-scroll>
      <div class="section-container">
        <div class="section-header">
          <span class="section-badge">Processo Simplificado</span>
          <h2 class="section-title">Como Funciona na Prática</h2>
          <p class="section-description">
            3 passos simples para transformar seu produto digital
          </p>
        </div>
        <div class="how-it-works-grid">
          <div class="step-card" data-stagger>
            <div class="step-number">01</div>
            <div class="step-icon">
              <i class="fas fa-calendar-check"></i>
            </div>
            <h3 class="step-title">Diagnóstico Gratuito</h3>
            <p class="step-description">
              Conversa inicial de 30 minutos para entender seu produto e identificar 
              oportunidades de gamificação.
            </p>
          </div>

          <div class="step-card" data-stagger>
            <div class="step-number">02</div>
            <div class="step-icon">
              <i class="fas fa-handshake"></i>
            </div>
            <h3 class="step-title">Proposta & Acordo</h3>
            <p class="step-description">
              Apresentação da proposta personalizada com cronograma, investimento 
              e metodologia detalhada.
            </p>
          </div>

          <div class="step-card" data-stagger>
            <div class="step-number">03</div>
            <div class="step-icon">
              <i class="fas fa-rocket"></i>
            </div>
            <h3 class="step-title">Execução do Projeto</h3>
            <p class="step-description">
              Implementação do Método Unli com 3 checkpoints de aprovação e 
              entrega completa da documentação.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Preços -->
    <section class="pricing-section" data-scroll>
      <div class="section-container">
        <div class="section-header">
          <span class="section-badge">Investimento</span>
          <h2 class="section-title">Planos de Consultoria</h2>
          <p class="section-description">
            Escolha a modalidade de pagamento que melhor se adapta ao seu negócio
          </p>
          <div class="urgency-alert">
            <i class="fas fa-clock"></i>
            <span><strong>Apenas 4 vagas</strong> disponíveis por mês • Oferta por tempo limitado</span>
          </div>
        </div>
        <div class="pricing-cards">
          <div class="pricing-card premium" data-stagger>
            <div class="pricing-ribbon">Melhor Valor</div>
            <div class="pricing-header">
              <div class="pricing-icon">
                <i class="fas fa-crown"></i>
              </div>
              <h3 class="pricing-title">Pagamento à Vista</h3>
              <p class="pricing-subtitle">Consultoria completa + Economia</p>
            </div>
            <div class="pricing-value-container">
              <div class="pricing-value">
                <span class="currency">R$</span>
                <span class="amount">12.000</span>
              </div>
              <div class="pricing-period">à vista</div>
              <div class="pricing-savings">
                <i class="fas fa-piggy-bank"></i>
                Economize R$ 1.500
              </div>
            </div>
            <div class="pricing-features">
              <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span>Reuniões de alinhamento e validação (3 checkpoints)</span>
              </div>
              <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span>UML completa dos fluxos do escopo</span>
              </div>
              <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span>UI/UX final aprovada para execução</span>
              </div>
              <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span>Recomendações de conversão e loop de uso</span>
              </div>
              <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span>Método Unli: 7 etapas estruturadas</span>
              </div>
              <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span>Documentação completa para desenvolvimento</span>
              </div>
            </div>
            <div class="guarantee-section">
              <div class="guarantee-item">
                <i class="fas fa-shield-alt"></i>
                <span><strong>Primeira reunião sem compromisso</strong></span>
              </div>
            </div>
            <a href="#contact" class="pricing-cta premium">
              <span class="cta-text">Agendar Diagnóstico Gratuito</span>
              <i class="fas fa-arrow-right"></i>
            </a>
          </div>

          <div class="pricing-card standard" data-stagger>
            <div class="pricing-header">
              <div class="pricing-icon">
                <i class="fas fa-credit-card"></i>
              </div>
              <h3 class="pricing-title">Parcelado</h3>
              <p class="pricing-subtitle">Facilidade de pagamento</p>
            </div>
            <div class="pricing-value-container">
              <div class="pricing-value">
                <span class="currency">R$</span>
                <span class="amount">1.350</span>
              </div>
              <div class="pricing-period">por mês</div>
              <div class="pricing-total">
                <i class="fas fa-calculator"></i>
                Total: R$ 13.500 (10x)
              </div>
            </div>
            <div class="pricing-features">
              <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span>Reuniões de alinhamento e validação (3 checkpoints)</span>
              </div>
              <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span>UML completa dos fluxos do escopo</span>
              </div>
              <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span>UI/UX final aprovada para execução</span>
              </div>
              <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span>Recomendações de conversão e loop de uso</span>
              </div>
              <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span>Método Unli: 7 etapas estruturadas</span>
              </div>
              <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span>Documentação completa para desenvolvimento</span>
              </div>
            </div>
            <a href="#contact" class="pricing-cta standard">
              <span class="cta-text">Agendar Diagnóstico Gratuito</span>
              <i class="fas fa-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Vídeo Apresentação -->
    <section class="video-section" data-scroll>
      <div class="section-container">
        <div class="section-header">
          <span class="section-badge">Conheça Nossa Expertise</span>
          <h2 class="section-title">Veja Nossa Entrevista no Flow Games</h2>
          <p class="section-description">
            Nosso CEO Alan Reis falando sobre gamificação e inovação na BGS
          </p>
        </div>
        <div class="video-wrapper">
          <iframe 
            src="https://www.youtube.com/embed/JkTkHzMdsww?si=xUrnrIyXar4Fv3rI" 
            title="Entrevista CEO - Alan Reis - Flow Games"
            frameborder="0" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
            allowfullscreen
          ></iframe>
        </div>
      </div>
    </section>

    <!-- Cases de Sucesso -->
    <section class="cases-section" data-scroll>
      <div class="section-container">
        <div class="section-header">
          <span class="section-badge">Projetos Realizados</span>
          <h2 class="section-title">Cases de Sucesso em Gamificação</h2>
          <p class="section-description">
            Conheça alguns dos projetos que desenvolvemos
          </p>
        </div>
        <div class="cases-grid">
          <div class="case-card" data-stagger>
            <div class="case-image">
              <img src="@/assets/img/workCards/suzano-game.jpeg" alt="Suzano Rock in Rio - Projeto de gamificação para conscientização ambiental com jogo interativo de reciclagem" />
            </div>
            <div class="case-content">
              <h3 class="case-title">Suzano Rock in Rio</h3>
              <p class="case-description">
                Projeto de gamificação para conscientização ambiental no Rock in Rio, 
                com jogo interativo focado em reciclagem e sustentabilidade.
              </p>
              <div class="case-tags">
                <span class="case-tag">Rock in Rio</span>
                <span class="case-tag">Sustentabilidade</span>
                <span class="case-tag">Totem Interativo</span>
              </div>
            </div>
          </div>

          <div class="case-card" data-stagger>
            <div class="case-image">
              <img src="@/assets/img/workCards/bobs-gameficacao.png" alt="Sistema de Eventos Bobs Rock in Rio - Plataforma com quizzes interativos, desafios e sistema de recompensas" />
            </div>
            <div class="case-content">
              <h3 class="case-title">Bobs Rock in Rio - Sistema de Eventos</h3>
              <p class="case-description">
                Sistema gamificado criado para o Bobs no Rock in Rio com 
                quizzes interativos, desafios e sistema de recompensas.
              </p>
              <div class="case-tags">
                <span class="case-tag">Bobs</span>
                <span class="case-tag">Rock in Rio</span>
                <span class="case-tag">Quizzes</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Formulário de Contato -->
    <ContactSection
      id="contact"
      title="Pronto para Transformar Seu Negócio?"
      description="Entre em contato e vamos discutir como a gamificação pode revolucionar seus resultados"
      :contactItems="contactData"
    />

    <!-- Contador de Tempo -->
    <div class="time-counter" data-scroll>
      <div class="counter-container">
        <div class="counter-header">
          <i class="fas fa-fire"></i>
          <h3>Oferta por Tempo Limitado</h3>
        </div>
        <div class="counter-time">
          <div class="time-box">
            <span class="time-value">{{ String(timeLeft.minutes).padStart(2, '0') }}</span>
            <span class="time-label">minutos</span>
          </div>
          <div class="time-separator">:</div>
          <div class="time-box">
            <span class="time-value">{{ String(timeLeft.seconds).padStart(2, '0') }}</span>
            <span class="time-label">segundos</span>
          </div>
        </div>
        <p class="counter-text">para garantir sua vaga na consultoria</p>
      </div>
    </div>

    <!-- WhatsApp Flutuante -->
    <a href="https://wa.me/5511968354238?text=Olá! Vi a página da consultoria de gamificação e gostaria de agendar o diagnóstico gratuito." 
       target="_blank" 
       class="whatsapp-float"
       title="Falar no WhatsApp">
      <i class="fab fa-whatsapp"></i>
      <span class="whatsapp-text">Agendar Diagnóstico</span>
    </a>
  </div>
</template>

<script>
import ContactSection from '@/shared/Components/ContactSection.vue'

export default {
  name: 'ConsultoriaGamificacao',
  components: {
    ContactSection
  },
  data() {
    return {
      timeLeft: {
        minutes: 15,
        seconds: 0
      },
      contactData: [
        {
          icon: 'fas fa-envelope',
          label: 'Email',
          value: 'contato@unlistudio.com.br',
          iconBg: 'linear-gradient(135deg, #e67e22 0%, #d35400 100%)'
        },
        {
          icon: 'fab fa-whatsapp',
          label: 'WhatsApp',
          value: '+55 (11) 96835-4238',
          iconBg: 'linear-gradient(135deg, #25d366 0%, #128c7e 100%)'
        },
        {
          icon: 'fas fa-map-marker-alt',
          label: 'Localização',
          value: 'São Paulo, Brasil',
          iconBg: 'linear-gradient(135deg, #4f7aff 0%, #3d5fd9 100%)'
        }
      ]
    }
  },
  mounted() {
    // Scroll animations and interactions
    this.initScrollAnimations()
    // Iniciar cronômetro
    this.startTimer()
  },
  methods: {
    startTimer() {
      const timer = setInterval(() => {
        if (this.timeLeft.seconds > 0) {
          this.timeLeft.seconds--
        } else if (this.timeLeft.minutes > 0) {
          this.timeLeft.minutes--
          this.timeLeft.seconds = 59
        } else {
          // Timer acabou - pode adicionar ação aqui
          clearInterval(timer)
        }
      }, 1000)
    },
    initScrollAnimations() {
      // Initialize scroll-based animations similar to other pages
      const elements = document.querySelectorAll('[data-scroll]')
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('animate-in')
          }
        })
      }, { threshold: 0.1 })

      elements.forEach(element => observer.observe(element))
    }
  }
}
</script>

<style scoped lang="scss">
@import '@/assets/sass/settings/__colors.scss';
@import '@/assets/sass/settings/__fonts.scss';
@import '@/assets/sass/settings/__breakpoints.scss';

.consultoria-page {
  background: #222222; // Mudança do branco para #222222 conforme solicitado
  color: $white;
  min-height: 100vh;
}

// Hero Section
.hero-section {
  padding: 120px 0 80px;
  background: linear-gradient(135deg, $black 0%, $gray-darkness 100%);
  overflow: hidden;
  position: relative;

  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 30% 20%, rgba($p-color, 0.1) 0%, transparent 60%);
  }
}

.hero-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  align-items: center;
  position: relative;
  z-index: 2;

  @media (max-width: $mobile) {
    grid-template-columns: 1fr;
    gap: 40px;
    text-align: center;
  }
}

.hero-content {
  .hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba($p-color, 0.1);
    border: 1px solid rgba($p-color, 0.3);
    color: $p-color;
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 24px;
  }

  .hero-title {
    font-size: 3.2rem;
    font-weight: 700;
    line-height: 1.1;
    margin-bottom: 24px;
    color: $white;

    .highlight-text {
      background: linear-gradient(135deg, $p-color 0%, $p-light 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    @media (max-width: $mobile) {
      font-size: 2.5rem;
    }
  }

  .hero-description {
    font-size: 1.2rem;
    line-height: 1.6;
    color: $gray;
    margin-bottom: 32px;
    max-width: 600px;
  }
}

.hero-stats {
  display: flex;
  gap: 32px;
  margin-bottom: 40px;

  .stat-item {
    text-align: left;

    .stat-value {
      display: block;
      font-size: 2.5rem;
      font-weight: 700;
      color: $p-color;
      line-height: 1;
    }

    .stat-label {
      font-size: 0.9rem;
      color: $gray;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
  }

  @media (max-width: $mobile) {
    justify-content: center;
    gap: 24px;
    
    .stat-item {
      text-align: center;
    }
  }
}

.hero-actions {
  display: flex;
  gap: 16px;
  align-items: center;

  @media (max-width: $mobile) {
    justify-content: center;
  }
}

.btn-primary, .btn-secondary {
  padding: 16px 32px;
  border-radius: 50px;
  font-weight: 600;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.btn-primary {
  background: linear-gradient(135deg, $p-color 0%, $p-dark 100%);
  color: $white;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba($p-color, 0.4);
  }
}

.btn-secondary {
  background: transparent;
  color: $white;
  border-color: rgba($white, 0.3);

  &:hover {
    background: rgba($white, 0.1);
    border-color: rgba($white, 0.6);
  }
}

.hero-visual {
  display: flex;
  justify-content: center;
  align-items: center;

  .hero-image {
    width: 100%;
    max-width: 500px;
    height: auto;
    border-radius: 35%;
    box-shadow: 0 20px 60px rgba($black, 0.3);
  }
}

// Sections gerais
.section-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.section-header {
  text-align: center;
  margin-bottom: 60px;

  .section-badge {
    display: inline-block;
    background: rgba($p-color, 0.1);
    border: 1px solid rgba($p-color, 0.3);
    color: $p-color;
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 16px;
  }

  .section-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 16px;
    color: $white;
  }

  .section-description {
    font-size: 1.1rem;
    color: $gray;
    max-width: 600px;
    margin: 0 auto;
  }
}

// Problems Section
.problems-section {
  padding: 100px 0;
  background: #222222;
}

.problems-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 32px;
  max-width: 1000px;
  margin: 0 auto;
}

.problem-card {
  background: rgba($white, 0.05);
  border: 1px solid rgba($white, 0.1);
  border-radius: 20px;
  padding: 40px 32px;
  transition: all 0.3s ease;
  border-left: 4px solid $p-color;
  display: flex;
  flex-direction: column;
  height: 100%;

  &:hover {
    transform: translateY(-5px);
    background: rgba($white, 0.08);
    border-color: rgba($p-color, 0.3);
    box-shadow: 0 15px 40px rgba($p-color, 0.2);
  }

  .problem-icon {
    width: 70px;
    height: 70px;
    background: rgba($error, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 24px;
    font-size: 1.8rem;
    color: $error;
    align-self: flex-start;
  }

  .problem-title {
    font-size: 1.6rem;
    font-weight: 600;
    color: $white;
    margin-bottom: 16px;
    line-height: 1.3;
  }

  .problem-description {
    color: $gray;
    line-height: 1.6;
    margin-bottom: 24px;
    flex-grow: 1;
    font-size: 1rem;
  }

  .problem-impact {
    padding: 16px 20px;
    background: rgba($error, 0.1);
    border: 1px solid rgba($error, 0.3);
    border-radius: 12px;
    color: $error;
    font-size: 0.95rem;
    font-weight: 500;
    
    strong {
      color: $white;
      display: block;
      margin-bottom: 4px;
    }
  }
}

// Methodology Section
.methodology-section {
  padding: 100px 0;
  background: linear-gradient(135deg, $gray-darkness 0%, $black 100%);
}

.methodology-timeline {
  max-width: 800px;
  margin: 0 auto;
  position: relative;
  
  &::before {
    content: '';
    position: absolute;
    left: 30px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(180deg, $p-color 0%, $p-dark 100%);
  }
}

.timeline-item {
  display: flex;
  align-items: flex-start;
  margin-bottom: 40px;
  position: relative;
  
  &.checkpoint {
    .timeline-number {
      background: linear-gradient(135deg, $p-color 0%, $p-dark 100%);
      border: 3px solid $p-light;
      transform: scale(1.2);
    }
    
    .timeline-content {
      background: rgba($p-color, 0.1);
      border: 1px solid rgba($p-color, 0.3);
    }
  }
  
  .timeline-number {
    width: 60px;
    height: 60px;
    background: #333;
    border: 2px solid rgba($white, 0.3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: $white;
    flex-shrink: 0;
    margin-right: 24px;
    position: relative;
    z-index: 2;
  }
  
  .timeline-content {
    background: rgba($white, 0.05);
    border: 1px solid rgba($white, 0.1);
    border-radius: 12px;
    padding: 20px 24px;
    flex: 1;
    
    h4 {
      font-size: 1.2rem;
      font-weight: 600;
      color: $white;
      margin-bottom: 8px;
    }
    
    p {
      color: $gray;
      margin: 0;
      line-height: 1.5;
    }
    
    strong {
      color: $p-color;
    }
  }
}

.methodology-note {
  background: rgba($p-color, 0.1);
  border: 1px solid rgba($p-color, 0.3);
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  color: $gray-light;
  margin-top: 40px;
  
  strong {
    color: $p-color;
  }
}

// Deliverables Section ajustado
.deliverables-section {
  padding: 100px 0;
  background: #222222;
}

.deliverables-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 32px;
  max-width: 1000px;
  margin: 0 auto;
}

.deliverable-card {
  background: rgba($white, 0.05);
  border: 1px solid rgba($white, 0.1);
  border-radius: 20px;
  padding: 40px 32px;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  height: 100%;

  &:hover {
    transform: translateY(-8px);
    background: rgba($white, 0.08);
    border-color: rgba($p-color, 0.3);
    box-shadow: 0 15px 40px rgba($p-color, 0.15);
  }

  .card-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, $p-color 0%, $p-dark 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 24px;
    font-size: 1.8rem;
    color: $white;
    align-self: flex-start;
  }

  .card-title {
    font-size: 1.6rem;
    font-weight: 600;
    color: $white;
    margin-bottom: 16px;
    line-height: 1.3;
  }

  .card-description {
    color: $gray;
    line-height: 1.6;
    margin-bottom: 24px;
    flex-grow: 1;
    font-size: 1rem;
  }

  .card-list {
    list-style: none;
    padding: 0;
    margin: 0;

    li {
      color: $gray-light;
      padding: 8px 0;
      position: relative;
      padding-left: 24px;
      line-height: 1.5;

      &::before {
        content: '✓';
        color: $p-color;
        position: absolute;
        left: 0;
        font-weight: 600;
        font-size: 1.1rem;
      }
    }
  }
}

// Credentials Section
.credentials-section {
  padding: 100px 0;
  background: linear-gradient(135deg, $gray-darkness 0%, $black 100%);
}

.credentials-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  max-width: 1000px;
  margin: 0 auto;
}

.credential-card {
  text-align: center;
  padding: 32px 20px;
  background: rgba($white, 0.05);
  border: 1px solid rgba($white, 0.1);
  border-radius: 20px;
  transition: all 0.4s ease;
  position: relative;
  overflow: hidden;
  
  &.highlight {
    background: rgba($p-color, 0.08);
    border-color: rgba($p-color, 0.3);
    
    &::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, $p-color 0%, $p-light 100%);
    }
  }

  &:hover {
    transform: translateY(-8px);
    background: rgba($white, 0.08);
    border-color: rgba($p-color, 0.4);
    box-shadow: 0 15px 40px rgba($p-color, 0.2);
  }

  .credential-icon {
    font-size: 2.2rem;
    color: $p-color;
    margin-bottom: 20px;
    display: flex;
    justify-content: center;
  }

  .credential-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: $white;
    margin-bottom: 16px;
  }

  .credential-description {
    font-size: 0.9rem;
    color: $gray;
    line-height: 1.6;
    margin-bottom: 16px;
  }
  
  .credential-badge {
    display: inline-block;
    background: rgba($p-color, 0.2);
    color: $p-color;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
}

// Pricing Section
.pricing-section {
  padding: 100px 0;
  background: #222222;
}

.pricing-cards {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 40px;
  max-width: 900px;
  margin: 0 auto;
}

.pricing-card {
  background: rgba($white, 0.05);
  border: 2px solid rgba($white, 0.1);
  border-radius: 24px;
  padding: 0;
  position: relative;
  transition: all 0.4s ease;
  overflow: hidden;
  
  &.premium {
    border-color: rgba($p-color, 0.5);
    background: rgba($p-color, 0.05);
    transform: scale(1.02);
    
    .pricing-ribbon {
      position: absolute;
      top: 36px;
      right: -40px;
      background: linear-gradient(135deg, $p-color 0%, $p-dark 100%);
      color: $white;
      padding: 8px 40px;
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transform: rotate(45deg);
      box-shadow: 0 4px 8px rgba($p-color, 0.3);
    }
  }
  
  &.standard {
    border-color: rgba($gray-medium, 0.3);
  }

  &:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 60px rgba($black, 0.3);
    
    &.premium {
      transform: scale(1.02) translateY(-8px);
      box-shadow: 0 25px 80px rgba($p-color, 0.3);
    }
  }

  .pricing-header {
    text-align: center;
    padding: 40px 32px 20px;
    position: relative;
    
    .pricing-icon {
      width: 60px;
      height: 60px;
      background: rgba($p-color, 0.1);
      border: 2px solid rgba($p-color, 0.3);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      color: $p-color;
      margin: 0 auto 20px;
    }

    .pricing-title {
      font-size: 1.8rem;
      font-weight: 700;
      color: $white;
      margin-bottom: 8px;
    }
    
    .pricing-subtitle {
      color: $gray;
      font-size: 0.95rem;
      margin: 0;
    }
  }

  .pricing-value-container {
    text-align: center;
    padding: 0 32px 32px;
    
    .pricing-value {
      display: flex;
      align-items: baseline;
      justify-content: center;
      gap: 4px;
      margin-bottom: 12px;

      .currency {
        font-size: 1.4rem;
        color: $gray;
        font-weight: 500;
      }

      .amount {
        font-size: 3.8rem;
        font-weight: 800;
        color: $p-color;
        line-height: 1;
      }
    }
    
    .pricing-period {
      color: $gray-light;
      font-size: 1.1rem;
      margin-bottom: 16px;
    }

    .pricing-savings, .pricing-total {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 12px 20px;
      border-radius: 12px;
      font-size: 0.9rem;
      font-weight: 600;
      
      i {
        font-size: 1rem;
      }
    }
    
    .pricing-savings {
      background: rgba($accent-green, 0.1);
      border: 1px solid rgba($accent-green, 0.3);
      color: $accent-green;
    }
    
    .pricing-total {
      background: rgba($gray-medium, 0.1);
      border: 1px solid rgba($gray-medium, 0.3);
      color: $gray-light;
    }
  }

  .pricing-features {
    padding: 0 32px 32px;

    .feature-item {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      padding: 12px 0;
      border-bottom: 1px solid rgba($white, 0.05);
      
      &:last-child {
        border-bottom: none;
      }

      i {
        color: $accent-green;
        font-size: 1.1rem;
        margin-top: 2px;
        flex-shrink: 0;
      }

      span {
        color: $gray-light;
        line-height: 1.5;
        font-size: 0.95rem;
      }
    }
  }

  .pricing-cta {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    width: calc(100% - 64px);
    margin: 0 32px 32px;
    padding: 18px 0;
    border-radius: 16px;
    text-decoration: none;
    font-weight: 700;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    
    &.premium {
      background: linear-gradient(135deg, $p-color 0%, $p-dark 100%);
      color: $white;
      box-shadow: 0 8px 25px rgba($p-color, 0.3);
      
      &:hover {
        background: linear-gradient(135deg, $p-light 0%, $p-color 100%);
        transform: translateY(-2px);
        box-shadow: 0 12px 35px rgba($p-color, 0.4);
      }
    }
    
    &.standard {
      background: rgba($gray-medium, 0.2);
      border: 2px solid rgba($gray-medium, 0.4);
      color: $white;
      
      &:hover {
        background: rgba($gray-medium, 0.3);
        border-color: rgba($gray-medium, 0.6);
        transform: translateY(-2px);
      }
    }
    
    .cta-text {
      font-size: 1.1rem;
    }
    
    i {
      transition: transform 0.3s ease;
    }
    
    &:hover i {
      transform: translateX(4px);
    }
  }
}

// Video Section
.video-section {
  padding: 100px 0;
  background: linear-gradient(135deg, $gray-darkness 0%, $black 100%);
}

.video-wrapper {
  max-width: 800px;
  margin: 0 auto;
  position: relative;
  width: 100%;
  height: 0;
  padding-bottom: 56.25%; // 16:9 aspect ratio
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba($black, 0.4);

  iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: none;
  }
}

// Cases Section
.cases-section {
  padding: 100px 0;
  background: #222222;
}

.cases-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 32px;
}

.case-card {
  background: rgba($white, 0.05);
  border: 1px solid rgba($white, 0.1);
  border-radius: 20px;
  overflow: hidden;
  transition: all 0.3s ease;

  &:hover {
    transform: translateY(-5px);
    background: rgba($white, 0.08);
    border-color: rgba($p-color, 0.3);
  }

  .case-image {
    height: 200px;
    overflow: hidden;

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }
  }

  &:hover .case-image img {
    transform: scale(1.05);
  }

  .case-content {
    padding: 24px;

    .case-title {
      font-size: 1.3rem;
      font-weight: 600;
      color: $white;
      margin-bottom: 12px;
    }

    .case-description {
      color: $gray;
      line-height: 1.6;
      margin-bottom: 16px;
    }

    .case-tags {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;

      .case-tag {
        background: rgba($p-color, 0.1);
        color: $p-color;
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 500;
        border: 1px solid rgba($p-color, 0.3);
      }
    }
  }
}

// Animations
[data-scroll] {
  opacity: 0;
  transform: translateY(30px);
  transition: all 0.6s ease;

  &.animate-in {
    opacity: 1;
    transform: translateY(0);
  }
}

[data-stagger] {
  transition-delay: calc(var(--stagger, 0) * 100ms);
}

// Responsive
@media (max-width: $mobile) {
  .section-header .section-title {
    font-size: 2rem;
  }
  
  .problems-grid,
  .deliverables-grid {
    grid-template-columns: 1fr;
    gap: 24px;
  }
  
  .pricing-cards,
  .cases-grid {
    grid-template-columns: 1fr;
  }
  
  .methodology-timeline {
    &::before {
      left: 20px;
    }
  }
  
  .timeline-item {
    .timeline-number {
      width: 40px;
      height: 40px;
      margin-right: 16px;
    }
  }
  
  .credentials-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
  }
  
  .pricing-cards {
    grid-template-columns: 1fr;
    gap: 24px;
    
    .pricing-card.premium {
      transform: none;
      
      &:hover {
        transform: translateY(-5px);
      }
    }
  }
}

// Urgency Alert
.urgency-alert {
  background: linear-gradient(135deg, #e74c3c, #c0392b);
  color: white;
  padding: 12px 20px;
  border-radius: 8px;
  text-align: center;
  margin: 30px 0;
  font-weight: 600;
  font-size: 16px;
  box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
  animation: pulse 2s infinite;

  i {
    margin-right: 8px;
    font-size: 18px;
  }
}

// ==========================================
// HOW IT WORKS SECTION - COMO FUNCIONA
// ==========================================

.how-it-works-section {
  padding: 100px 0;
  background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
  color: white;

  .section-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 40px;

    @media (max-width: 768px) {
      padding: 0 20px;
    }
  }

  .section-header {
    text-align: center;
    margin-bottom: 80px;

    .section-badge {
      display: inline-block;
      background: rgba(255, 255, 255, 0.1);
      color: #3498db;
      padding: 8px 20px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 20px;
      border: 1px solid rgba(52, 152, 219, 0.3);
    }

    .section-title {
      font-size: 48px;
      font-weight: 800;
      margin-bottom: 20px;
      color: white;

      @media (max-width: 768px) {
        font-size: 36px;
      }
    }

    .section-description {
      font-size: 18px;
      opacity: 0.9;
      max-width: 600px;
      margin: 0 auto;
      line-height: 1.6;

      @media (max-width: 768px) {
        font-size: 16px;
      }
    }
  }

  .how-it-works-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 40px;

    @media (max-width: 768px) {
      grid-template-columns: 1fr;
      gap: 30px;
    }

    .step-card:nth-child(3) {
      grid-column: 1 / -1; // Ocupa toda a largura da linha
      max-width: 600px;
      margin: 0 auto;
    }
  }

  .step-card {
    background: rgba(255, 255, 255, 0.1);
    padding: 40px 30px;
    border-radius: 20px;
    text-align: left;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;

    &:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
      background: rgba(255, 255, 255, 0.15);
    }

    &::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: linear-gradient(135deg, #3498db, #2ecc71);
    }

    .step-number {
      position: absolute;
      top: 20px;
      right: 20px;
      width: 40px;
      height: 40px;
      background: linear-gradient(135deg, #3498db, #2ecc71);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      font-weight: 700;
      color: white;
    }

    .step-icon {
      font-size: 48px;
      margin-bottom: 20px;
      color: #3498db;

      i {
        display: block;
      }
    }

    .step-title {
      font-size: 24px;
      font-weight: 700;
      margin-bottom: 15px;
      color: white;
    }

    .step-description {
      font-size: 16px;
      line-height: 1.6;
      opacity: 0.9;
      color: rgba(255, 255, 255, 0.9);
    }
  }
}

// Como Funciona Section
.how-it-works {
  padding: 100px 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;

  .section-title {
    text-align: center;
    margin-bottom: 80px;

    h2 {
      font-size: 48px;
      font-weight: 800;
      margin-bottom: 20px;
    }

    p {
      font-size: 20px;
      opacity: 0.9;
    }
  }

  .steps-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 40px;
    max-width: 1200px;
    margin: 0 auto;
  }

  .step-card {
    background: rgba(255, 255, 255, 0.1);
    padding: 40px 30px;
    border-radius: 20px;
    text-align: center;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;

    &:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    }

    .step-number {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #4f7aff, #3d5fd9);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      font-weight: 700;
      margin: 0 auto 20px;
    }

    .step-icon {
      font-size: 48px;
      margin-bottom: 20px;
      opacity: 0.9;
    }

    h3 {
      font-size: 24px;
      font-weight: 700;
      margin-bottom: 15px;
    }

    p {
      font-size: 16px;
      line-height: 1.6;
      opacity: 0.9;
    }
  }
}

// Guarantee Section
.guarantee-section {
  margin: 25px;
  padding: 20px;
  background: rgba(46, 204, 113, 0.1);
  border-radius: 12px;
  border: 2px solid #2ecc71;

  .guarantee-item {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #27ae60;
    font-size: 16px;

    i {
      font-size: 20px;
      margin-right: 10px;
    }

    strong {
      font-weight: 700;
    }
  }
}

// WhatsApp Flutuante
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
    transform: scale(1.05);
    box-shadow: 0 8px 30px rgba(37, 211, 102, 0.6);
    text-decoration: none;
    color: white;
  }

  i {
    font-size: 24px;
    margin: 0;
  }

  .whatsapp-text {
    white-space: nowrap;
    font-weight: 600;
  }
}

// Animations
@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(231, 76, 60, 0.4);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(231, 76, 60, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(231, 76, 60, 0);
  }
}

// Responsive
@media (max-width: 768px) {
  .urgency-alert {
    font-size: 14px;
    padding: 10px 15px;
  }

  .how-it-works {
    padding: 60px 0;
    
    .section-title h2 {
      font-size: 32px;
    }
    
    .step-card {
      padding: 30px 20px;
    }
  }

  .whatsapp-float {
    bottom: 20px;
    right: 20px;
    
    .whatsapp-btn {
      width: 50px;
      height: 50px;
      font-size: 24px;
    }
  }

  .guarantee-section {
    margin: 20px;
    padding: 15px;
    
    .guarantee-item {
      font-size: 14px;
      
      i {
        font-size: 18px;
      }
    }
  }
}

// ==========================================
// TIME COUNTER - CRONÔMETRO ESTILIZADO
// ==========================================

.time-counter {
  position: fixed;
  bottom: 110px;
  right: 30px;
  z-index: 999; // Menor que o WhatsApp
  opacity: 0;
  transform: translateX(100px);
  transition: all 0.8s ease;

  &.is-visible {
    opacity: 1;
    transform: translateX(0);
  }

  .counter-container {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    border-radius: 20px;
    padding: 25px 20px;
    box-shadow: 0 10px 30px rgba(231, 76, 60, 0.4);
    border: 2px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    min-width: 200px;
    animation: pulseGlow 3s ease-in-out infinite;
  }

  .counter-header {
    text-align: center;
    margin-bottom: 15px;
    
    i {
      font-size: 24px;
      color: #fff;
      margin-bottom: 8px;
      display: block;
      animation: bounce 2s infinite;
    }
    
    h3 {
      font-size: 16px;
      font-weight: 700;
      color: #fff;
      margin: 0;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
      letter-spacing: 0.5px;
    }
  }

  .counter-time {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-bottom: 12px;
  }

  .time-box {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    padding: 12px 8px;
    text-align: center;
    min-width: 50px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    
    .time-value {
      display: block;
      font-size: 24px;
      font-weight: 800;
      color: #fff;
      line-height: 1;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
      margin-bottom: 4px;
    }
    
    .time-label {
      font-size: 10px;
      color: rgba(255, 255, 255, 0.9);
      text-transform: uppercase;
      font-weight: 600;
      letter-spacing: 0.5px;
    }
  }

  .time-separator {
    font-size: 20px;
    font-weight: 800;
    color: #fff;
    animation: blink 1s infinite;
  }

  .counter-text {
    text-align: center;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
    font-weight: 500;
    line-height: 1.3;
  }
}

// Animations
@keyframes pulseGlow {
  0%, 100% {
    box-shadow: 0 10px 30px rgba(231, 76, 60, 0.4);
    transform: scale(1);
  }
  50% {
    box-shadow: 0 15px 40px rgba(231, 76, 60, 0.6);
    transform: scale(1.02);
  }
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% {
    transform: translateY(0);
  }
  40% {
    transform: translateY(-8px);
  }
  60% {
    transform: translateY(-4px);
  }
}

@keyframes blink {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.3;
  }
}

@keyframes pulseWhatsapp {
  0%, 100% {
    box-shadow: 0 6px 25px rgba(37, 211, 102, 0.4);
  }
  50% {
    box-shadow: 0 8px 35px rgba(37, 211, 102, 0.6);
  }
}

// Responsive
@media (max-width: 768px) {
  .time-counter {
    bottom: 80px;
    right: 15px;
    
    .counter-container {
      padding: 20px 15px;
      min-width: 180px;
    }
    
    .counter-header {
      i {
        font-size: 20px;
      }
      
      h3 {
        font-size: 14px;
      }
    }
    
    .time-box {
      min-width: 45px;
      padding: 10px 6px;
      
      .time-value {
        font-size: 20px;
      }
      
      .time-label {
        font-size: 9px;
      }
    }
    
    .time-separator {
      font-size: 18px;
    }
    
    .counter-text {
      font-size: 11px;
    }
  }
}
</style>