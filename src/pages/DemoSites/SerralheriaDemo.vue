<template>
  <div class="demo-site serralheria-demo">
    <!-- Demo Banner -->
    <div class="demo-banner">
      <i class="fas fa-eye"></i>
      Este é um modelo de demonstração — 
      <a href="/site-vitrine" class="demo-banner-link">quero um site assim para minha empresa</a>
      <span class="demo-badge">MODELO</span>
    </div>

    <!-- Header -->
    <header class="sr-header">
      <div class="sr-header-inner">
        <div class="sr-logo">
          <span class="sr-logo-icon">⚙️</span>
          <div>
            <span class="sr-logo-name">Ferro & Forma</span>
            <span class="sr-logo-tagline">Serralheria Profissional</span>
          </div>
        </div>
        <nav class="sr-nav">
          <a href="#servicos">Serviços</a>
          <a href="#portfolio">Portfólio</a>
          <a href="#sobre">Sobre</a>
          <a href="#contato">Contato</a>
        </nav>
        <a :href="whatsappLink('Olá! Gostaria de solicitar um orçamento!')" target="_blank" class="sr-header-btn">
          <i class="fab fa-whatsapp"></i> Pedir Orçamento
        </a>
      </div>
    </header>

    <!-- Hero -->
    <section class="sr-hero">
      <div class="sr-hero-inner">
        <div class="sr-hero-content">
          <span class="sr-hero-badge">
            <i class="fas fa-tools"></i>
            Orçamento em 24h
          </span>
          <h1 class="sr-hero-title">
            Soluções em ferro e aço<br>
            <span>com precisão e durabilidade</span>
          </h1>
          <p class="sr-hero-sub">
            Portões, grades, escadas, estruturas metálicas e muito mais. 
            Fabricação própria, garantia de qualidade e entrega no prazo combinado.
          </p>
          <div class="sr-hero-actions">
            <a :href="whatsappLink('Olá! Gostaria de solicitar um orçamento grátis!')" target="_blank" class="sr-btn-orange">
              <i class="fab fa-whatsapp"></i> Solicitar Orçamento Grátis
            </a>
            <a href="#portfolio" class="sr-btn-outline">
              <i class="fas fa-images"></i> Ver Portfólio
            </a>
          </div>
          <div class="sr-hero-badges">
            <span><i class="fas fa-shield-alt"></i> Garantia de 5 anos</span>
            <span><i class="fas fa-map-marker-alt"></i> Atendemos SP e região</span>
            <span><i class="fas fa-clock"></i> 15 anos de experiência</span>
          </div>
        </div>

        <div class="sr-hero-stats">
          <div class="sr-hero-stat" v-for="stat in heroStats" :key="stat.label">
            <span class="sr-stat-num">{{ stat.value }}</span>
            <span class="sr-stat-label">{{ stat.label }}</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Serviços -->
    <section id="servicos" class="sr-services-section">
      <div class="sr-container">
        <div class="sr-section-header">
          <span class="sr-section-badge">O que fazemos</span>
          <h2>Nossos Serviços</h2>
          <p>Da concepção à instalação — tudo feito com excelência</p>
        </div>

        <div class="sr-services-grid">
          <div class="sr-service-card" v-for="service in services" :key="service.id">
            <div class="sr-service-icon-wrap">
              <span class="sr-service-emoji">{{ service.emoji }}</span>
            </div>
            <h3 class="sr-service-name">{{ service.name }}</h3>
            <p class="sr-service-desc">{{ service.description }}</p>
            <ul class="sr-service-list">
              <li v-for="item in service.items" :key="item">
                <i class="fas fa-check-circle"></i> {{ item }}
              </li>
            </ul>
            <a
              :href="whatsappLink(`Olá! Gostaria de um orçamento para *${service.name}*!`)"
              target="_blank"
              class="sr-service-btn"
            >
              <i class="fab fa-whatsapp"></i> Pedir Orçamento
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Portfólio -->
    <section id="portfolio" class="sr-portfolio-section">
      <div class="sr-container">
        <div class="sr-section-header">
          <span class="sr-section-badge">Nossos Trabalhos</span>
          <h2>Portfólio</h2>
          <p>Projetos entregues com qualidade e satisfação do cliente</p>
        </div>

        <!-- Filter -->
        <div class="sr-portfolio-filters">
          <button
            v-for="f in portfolioFilters"
            :key="f.id"
            class="sr-filter-btn"
            :class="{ active: activeFilter === f.id }"
            @click="activeFilter = f.id"
          >
            {{ f.emoji }} {{ f.name }}
          </button>
        </div>

        <div class="sr-portfolio-grid">
          <div
            v-for="item in filteredPortfolio"
            :key="item.id"
            class="sr-portfolio-card"
          >
            <div class="sr-portfolio-img" :style="{ background: item.bg }">
              <span class="sr-portfolio-emoji">{{ item.emoji }}</span>
              <div class="sr-portfolio-overlay">
                <a
                  :href="whatsappLink(`Olá! Vi o projeto *${item.name}* no portfólio e gostaria de um orçamento similar!`)"
                  target="_blank"
                  class="sr-portfolio-cta"
                >
                  <i class="fab fa-whatsapp"></i> Quero igual
                </a>
              </div>
            </div>
            <div class="sr-portfolio-info">
              <span class="sr-portfolio-type">{{ item.type }}</span>
              <h4 class="sr-portfolio-name">{{ item.name }}</h4>
              <p class="sr-portfolio-desc">{{ item.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Por que nos escolher -->
    <section class="sr-differentials-section">
      <div class="sr-container">
        <div class="sr-section-header">
          <span class="sr-section-badge">Diferenciais</span>
          <h2>Por que escolher a Ferro & Forma?</h2>
        </div>
        <div class="sr-diff-grid">
          <div class="sr-diff-card" v-for="diff in differentials" :key="diff.id">
            <div class="sr-diff-icon">{{ diff.emoji }}</div>
            <h3>{{ diff.title }}</h3>
            <p>{{ diff.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Sobre -->
    <section id="sobre" class="sr-about-section">
      <div class="sr-container sr-about-grid">
        <div class="sr-about-visual">
          <div class="sr-about-badge">⚙️</div>
          <div class="sr-about-stats-grid">
            <div class="sr-about-stat" v-for="stat in aboutStats" :key="stat.label">
              <span class="sr-astat-num">{{ stat.value }}</span>
              <span class="sr-astat-label">{{ stat.label }}</span>
            </div>
          </div>
        </div>
        <div class="sr-about-text">
          <span class="sr-section-badge">Nossa História</span>
          <h2>15 anos entregando qualidade em aço</h2>
          <p>
            A Ferro & Forma foi fundada em 2010 por João Mendes, serralheiro com vocação e visão de mercado. 
            O que começou como um pequeno galpão familiar cresceu para um dos serralherias mais reconhecidas da Grande São Paulo.
          </p>
          <p>
            Investimos em equipamentos modernos de soldagem e corte a laser para entregar projetos com 
            precisão milimétrica e acabamento superior. Cada projeto é planejado, executado e instalado 
            pela nossa equipe própria — sem terceirização.
          </p>
          <div class="sr-about-certifications">
            <div class="sr-cert"><i class="fas fa-award"></i> Empresa certificada pelo CREA</div>
            <div class="sr-cert"><i class="fas fa-hard-hat"></i> NR-35 (Trabalho em Altura)</div>
            <div class="sr-cert"><i class="fas fa-fire-extinguisher"></i> Materiais homologados</div>
            <div class="sr-cert"><i class="fas fa-shield-alt"></i> 5 anos de garantia</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Depoimentos -->
    <section class="sr-testimonials-section">
      <div class="sr-container">
        <div class="sr-section-header">
          <span class="sr-section-badge">Clientes</span>
          <h2>O que nossos clientes dizem</h2>
        </div>
        <div class="sr-testimonials-grid">
          <div class="sr-testimonial" v-for="t in testimonials" :key="t.id">
            <div class="sr-testimonial-stars">
              <i class="fas fa-star" v-for="s in 5" :key="s"></i>
            </div>
            <p>"{{ t.text }}"</p>
            <div class="sr-testimonial-author">
              <span class="sr-t-avatar">{{ t.avatar }}</span>
              <div>
                <span class="sr-t-name">{{ t.name }}</span>
                <span class="sr-t-service">{{ t.service }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Contato -->
    <section id="contato" class="sr-contact-section">
      <div class="sr-container sr-contact-grid">
        <div class="sr-contact-info">
          <span class="sr-section-badge">Contato</span>
          <h2>Solicite seu orçamento</h2>
          <p>Atendemos de segunda a sábado. Orçamento gratuito e sem compromisso.</p>

          <div class="sr-contact-list">
            <div class="sr-contact-item">
              <div class="sr-ci-icon"><i class="fab fa-whatsapp"></i></div>
              <div>
                <h4>(11) 9 9999-0000</h4>
                <a :href="whatsappLink('Olá! Gostaria de um orçamento!')" target="_blank" class="sr-link">Enviar mensagem</a>
              </div>
            </div>
            <div class="sr-contact-item">
              <div class="sr-ci-icon"><i class="fas fa-map-marker-alt"></i></div>
              <div>
                <h4>Rua do Metal, 450</h4>
                <p>Distrito Industrial — São Paulo/SP</p>
              </div>
            </div>
            <div class="sr-contact-item">
              <div class="sr-ci-icon"><i class="fas fa-clock"></i></div>
              <div>
                <h4>Horário de Atendimento</h4>
                <p>Seg–Sex: 7h às 18h<br>Sábado: 7h às 12h</p>
              </div>
            </div>
            <div class="sr-contact-item">
              <div class="sr-ci-icon"><i class="fas fa-car"></i></div>
              <div>
                <h4>Visita Técnica</h4>
                <p>Vamos até você para medir e orçar sem custos</p>
              </div>
            </div>
          </div>
        </div>

        <div class="sr-contact-cta">
          <div class="sr-cta-box">
            <span class="sr-cta-icon">⚙️</span>
            <h3>Orçamento Rápido</h3>
            <p>Mande uma foto do local, as medidas aproximadas e receba seu orçamento em até 24 horas.</p>
            <a :href="whatsappLink('Olá! Gostaria de solicitar um orçamento. Vou enviar fotos e medidas!')" target="_blank" class="sr-btn-orange full">
              <i class="fab fa-whatsapp"></i> Solicitar Orçamento pelo WhatsApp
            </a>
            <div class="sr-cta-trust">
              <span><i class="fas fa-check"></i> Resposta em até 24h</span>
              <span><i class="fas fa-check"></i> Visita técnica grátis</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="sr-footer">
      <div class="sr-footer-inner">
        <span>© 2025 Ferro & Forma Serralheria · CNPJ 00.000.000/0001-00</span>
        <span class="sr-footer-credit">Site criado pela <a href="/site-vitrine" target="_blank">Unli</a></span>
      </div>
    </footer>

    <!-- WhatsApp Float -->
    <a :href="whatsappLink('Olá! Gostaria de solicitar um orçamento!')" target="_blank" class="sr-wpp-float" aria-label="Orçamento pelo WhatsApp">
      <i class="fab fa-whatsapp"></i>
    </a>

    <!-- CTA Unli - Quero esse site! -->
    <a :href="`https://wa.me/${unliWhatsapp}?text=${encodeURIComponent('Olá! Vi o modelo de site para serralheria e quero um assim para a minha empresa!')}`"
       target="_blank"
       rel="noopener noreferrer"
       class="unli-cta-bar">
      <span class="unli-cta-text">
        <strong>Gostou desse modelo?</strong> É exatamente assim que entregamos para você.
      </span>
      <span class="unli-cta-btn">
        <i class="fab fa-whatsapp"></i> Quero esse site!
      </span>
    </a>
  </div>
</template>

<script>
export default {
  name: 'SerralheriaDemo',
  data() {
    return {
      activeFilter: 'todos',
      whatsappNumber: '5511999990000',
      unliWhatsapp: process.env.VUE_APP_WHATSAPP_SDR || '5511911019666',
      heroStats: [
        { value: '15+', label: 'Anos de Experiência' },
        { value: '800+', label: 'Projetos Entregues' },
        { value: '5 anos', label: 'Garantia Inclusa' },
        { value: '4.9⭐', label: 'Nota Google' },
      ],
      services: [
        {
          id: 1, emoji: '🚪',
          name: 'Portões e Grades',
          description: 'Fabricação de portões automáticos, portões de correr, basculantes e grades de segurança para residências e comércios.',
          items: ['Portão de correr', 'Portão basculante', 'Grades para janelas', 'Grades decorativas']
        },
        {
          id: 2, emoji: '🪜',
          name: 'Escadas Metálicas',
          description: 'Escadas retas, caracol e em L com corrimão em aço inox ou pintado. Projetos residenciais e comerciais.',
          items: ['Escada reta', 'Escada caracol', 'Escada em L/U', 'Corrimão em aço inox']
        },
        {
          id: 3, emoji: '🏗️',
          name: 'Estruturas Metálicas',
          description: 'Coberturas, marquises, telhados de galpão, mezaninos e estruturas para painéis solares.',
          items: ['Coberturas metálicas', 'Marquises', 'Mezaninos', 'Pergolados']
        },
        {
          id: 4, emoji: '🪟',
          name: 'Grades e Guarda-Corpos',
          description: 'Proteção com estilo para sacadas, varandas, escadas e claraboias em residências e prédios.',
          items: ['Guarda-corpo sacada', 'Proteção de janelas', 'Fechamento de varanda', 'Corrimão escada']
        },
        {
          id: 5, emoji: '🔧',
          name: 'Portão Automático',
          description: 'Instalação completa com motor, controles remotos e sensores de segurança. Todas as marcas.',
          items: ['Motor deslizante', 'Motor basculante', 'Controles remotos', 'Manutenção preventiva']
        },
        {
          id: 6, emoji: '🏢',
          name: 'Obras Comerciais',
          description: 'Projetos especiais para comércios, indústrias e condomínios. Atendemos CNPJ e contratos anuais.',
          items: ['Galpões industriais', 'Cancelas e catracas', 'Divisórias metálicas', 'Terraços e decks']
        },
      ],
      portfolioFilters: [
        { id: 'todos', name: 'Todos', emoji: '🔍' },
        { id: 'portoes', name: 'Portões', emoji: '🚪' },
        { id: 'escadas', name: 'Escadas', emoji: '🪜' },
        { id: 'estruturas', name: 'Estruturas', emoji: '🏗️' },
        { id: 'grades', name: 'Grades', emoji: '🪟' },
      ],
      portfolio: [
        { id: 1, filter: 'portoes', emoji: '🚪', name: 'Portão de Alumínio Deslizante', type: 'Portão', description: 'Residência em Alphaville — automação completa com motor 1/4HP', bg: 'linear-gradient(135deg, #263238, #37474f)' },
        { id: 2, filter: 'escadas', emoji: '🪜', name: 'Escada Caracol Aço Inox', type: 'Escada', description: 'Cobertura duplex no Itaim Bibi — inox polido com degraus amadeirados', bg: 'linear-gradient(135deg, #455a64, #546e7a)' },
        { id: 3, filter: 'estruturas', emoji: '🏗️', name: 'Cobertura Metálica', type: 'Estrutura', description: 'Área de lazer 120m² com telha termoacústica e pergolado decorativo', bg: 'linear-gradient(135deg, #e65100, #ef6c00)' },
        { id: 4, filter: 'grades', emoji: '🪟', name: 'Guarda-Corpo Sacada', type: 'Grades', description: 'Apartamento na Mooca — vidro temperado com estrutura em aço inox escovado', bg: 'linear-gradient(135deg, #1a237e, #283593)' },
        { id: 5, filter: 'portoes', emoji: '🚪', name: 'Portão Basculante Industrial', type: 'Portão', description: 'Galpão em ABC Paulista — 6m x 4m com automação industrial', bg: 'linear-gradient(135deg, #33691e, #558b2f)' },
        { id: 6, filter: 'escadas', emoji: '🪜', name: 'Escada Reta com Corrimão', type: 'Escada', description: 'Sobrado em São Bernardo — degraus de chapa xadrez com grade lateral', bg: 'linear-gradient(135deg, #ad1457, #c2185b)' },
      ],
      differentials: [
        { id: 1, emoji: '🏭', title: 'Fabricação Própria', description: 'Tudo é fabricado no nosso galpão. Sem intermediários, sem subcontratados. Qualidade 100% controlada.' },
        { id: 2, emoji: '📐', title: 'Projeto Personalizado', description: 'Cada obra é única. Visitamos o local, fazemos o projeto sob medida e entregamos exatamente o que você precisa.' },
        { id: 3, emoji: '🛡️', title: '5 Anos de Garantia', description: 'Todo serviço tem garantia de 5 anos contra defeitos de fabricação. Segurança que dura.' },
        { id: 4, emoji: '⏱️', title: 'Prazo Respeitado', description: 'Comprometemos com data e cumprimos. Multa contratual em caso de atraso sem justificativa técnica.' },
        { id: 5, emoji: '💰', title: 'Preço Justo', description: 'Orçamento transparente e detalhado. Sem surpresas no meio da obra. O que foi combinado é o que você paga.' },
        { id: 6, emoji: '🔧', title: 'Pós-Venda Garantido', description: 'Suporte após a instalação, regulagens incluídas e prioridade em manutenção para clientes da casa.' },
      ],
      aboutStats: [
        { value: '15+', label: 'Anos de mercado' },
        { value: '800+', label: 'Obras executadas' },
        { value: '12', label: 'Profissionais' },
        { value: '4.9', label: 'Nota no Google' },
      ],
      testimonials: [
        { id: 1, avatar: '👨‍💼', name: 'Roberto A.', service: 'Portão Automático', text: 'Excelente atendimento! Fizeram o portão automático da minha empresa com qualidade absurda. Prazo cumprido e acabamento perfeito. Recomendo sem hesitar.' },
        { id: 2, avatar: '👩', name: 'Patrícia F.', service: 'Escada Metálica', text: 'Contratei a escada caracol para minha cobertura. Ficou impecável! O projeto superou minhas expectativas. Profissionais educados e pontuais.' },
        { id: 3, avatar: '👨', name: 'Carlos E.', service: 'Grades e Cobertura', text: 'Já fiz 2 obras com eles: grades de proteção e cobertura metálica. No segundo projeto, nem pesei outras propostas. Confiança total.' },
      ]
    };
  },
  computed: {
    filteredPortfolio() {
      if (this.activeFilter === 'todos') return this.portfolio;
      return this.portfolio.filter(p => p.filter === this.activeFilter);
    }
  },
  methods: {
    whatsappLink(message) {
      return `https://wa.me/${this.whatsappNumber}?text=${encodeURIComponent(message)}`;
    }
  }
};
</script>

<style lang="scss" scoped>
// =============================================
// DEMO BANNER
// =============================================
.demo-banner {
  background: linear-gradient(135deg, #1a1a1a 0%, #0d0d0d 100%);
  color: #fff;
  text-align: center;
  padding: 10px 16px;
  font-size: 13px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  flex-wrap: wrap;
  position: sticky;
  top: 0;
  z-index: 1000;

  i { color: #f97316; }

  .demo-banner-link {
    color: #f97316;
    font-weight: 700;
    text-decoration: underline;
    &:hover { color: #fb923c; }
  }

  .demo-badge {
    background: #f97316;
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 20px;
    letter-spacing: 1px;
  }
}

// =============================================
// SERRALHERIA VARIABLES
// =============================================
$sr-dark: #0f0f0f;
$sr-gray: #1c1c1c;
$sr-steel: #2c3340;
$sr-orange: #f97316;
$sr-orange-d: #ea6a0a;
$sr-yellow: #fbbf24;
$sr-light: #f5f5f5;
$sr-text: #111;
$sr-muted: #666;
$sr-white: #fff;

.serralheria-demo {
  font-family: 'Segoe UI', sans-serif;
  background: $sr-light;
  color: $sr-text;
}

.sr-container {
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 20px;
}

.sr-section-badge {
  display: inline-block;
  background: rgba($sr-orange, 0.12);
  color: $sr-orange;
  font-size: 12px;
  font-weight: 700;
  padding: 4px 14px;
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 10px;
}

.sr-section-header {
  text-align: center;
  margin-bottom: 40px;
  h2 { font-size: clamp(26px, 4vw, 38px); font-weight: 800; margin: 8px 0; }
  p { color: $sr-muted; font-size: 16px; }
}

// =============================================
// HEADER
// =============================================
.sr-header {
  background: $sr-dark;
  position: sticky;
  top: 45px;
  z-index: 999;
  border-bottom: 2px solid rgba($sr-orange, 0.2);
}

.sr-header-inner {
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 20px;
  display: flex;
  align-items: center;
  height: 64px;
  gap: 20px;
  justify-content: space-between;
}

.sr-logo {
  display: flex;
  align-items: center;
  gap: 10px;

  .sr-logo-icon { font-size: 26px; }
  .sr-logo-name { display: block; font-size: 18px; font-weight: 900; color: $sr-white; letter-spacing: 0.5px; }
  .sr-logo-tagline { display: block; font-size: 11px; color: $sr-orange; letter-spacing: 1px; text-transform: uppercase; }
}

.sr-nav {
  display: flex;
  gap: 28px;

  a {
    color: rgba(255,255,255,0.7);
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    &:hover { color: $sr-orange; }
  }

  @media (max-width: 640px) { display: none; }
}

.sr-header-btn {
  background: $sr-orange;
  color: #fff;
  padding: 10px 18px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 700;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
  &:hover { background: $sr-orange-d; }
}

// =============================================
// HERO
// =============================================
.sr-hero {
  background: linear-gradient(145deg, $sr-dark 0%, $sr-steel 60%, #3a4556 100%);
  padding: 80px 20px 60px;
  position: relative;
  overflow: hidden;

  &::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: 
      repeating-linear-gradient(45deg, transparent, transparent 40px, rgba(255,255,255,0.01) 40px, rgba(255,255,255,0.01) 80px);
    pointer-events: none;
  }
}

.sr-hero-inner {
  max-width: 1100px;
  margin: 0 auto;
  position: relative;
}

.sr-hero-content { max-width: 640px; }

.sr-hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba($sr-orange, 0.15);
  border: 1px solid rgba($sr-orange, 0.35);
  color: $sr-orange;
  font-size: 13px;
  font-weight: 700;
  padding: 6px 16px;
  border-radius: 20px;
  margin-bottom: 20px;
}

.sr-hero-title {
  font-size: clamp(32px, 5vw, 56px);
  font-weight: 900;
  color: $sr-white;
  line-height: 1.1;
  margin-bottom: 16px;

  span { color: $sr-orange; display: block; }
}

.sr-hero-sub {
  color: rgba(255,255,255,0.7);
  font-size: 16px;
  line-height: 1.7;
  margin-bottom: 32px;
}

.sr-hero-actions {
  display: flex;
  gap: 14px;
  flex-wrap: wrap;
  margin-bottom: 36px;
}

.sr-btn-orange {
  background: $sr-orange;
  color: #fff;
  padding: 14px 28px;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 700;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;

  &.full { width: 100%; justify-content: center; }
  &:hover { background: $sr-orange-d; transform: translateY(-2px); }
}

.sr-btn-outline {
  border: 2px solid rgba(255,255,255,0.25);
  color: rgba(255,255,255,0.8);
  background: transparent;
  padding: 13px 26px;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 700;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
  &:hover { border-color: rgba(255,255,255,0.5); color: $sr-white; }
}

.sr-hero-badges {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;

  span {
    color: rgba(255,255,255,0.6);
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 6px;
    i { color: $sr-orange; }
  }
}

.sr-hero-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-top: 48px;
  padding-top: 40px;
  border-top: 1px solid rgba(255,255,255,0.08);

  @media (max-width: 640px) { grid-template-columns: repeat(2, 1fr); }
}

.sr-hero-stat {
  text-align: center;

  .sr-stat-num { display: block; font-size: 28px; font-weight: 900; color: $sr-orange; }
  .sr-stat-label { display: block; font-size: 12px; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px; }
}

// =============================================
// SERVICES
// =============================================
.sr-services-section {
  padding: 80px 0;
  background: $sr-white;
}

.sr-services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 24px;
}

.sr-service-card {
  background: $sr-light;
  border-radius: 16px;
  padding: 28px;
  border: 2px solid transparent;
  transition: all 0.2s;

  &:hover {
    border-color: rgba($sr-orange, 0.3);
    transform: translateY(-4px);
    box-shadow: 0 10px 30px rgba($sr-orange, 0.08);
  }

  .sr-service-icon-wrap {
    width: 60px;
    height: 60px;
    background: rgba($sr-orange, 0.1);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;

    .sr-service-emoji { font-size: 30px; }
  }

  .sr-service-name { font-size: 18px; font-weight: 800; color: $sr-text; margin-bottom: 8px; }
  .sr-service-desc { font-size: 14px; color: $sr-muted; line-height: 1.6; margin-bottom: 16px; }

  .sr-service-list {
    list-style: none;
    padding: 0;
    margin: 0 0 20px;
    display: flex;
    flex-direction: column;
    gap: 6px;

    li {
      font-size: 13px;
      color: $sr-text;
      display: flex;
      align-items: center;
      gap: 6px;
      i { color: $sr-orange; font-size: 12px; }
    }
  }

  .sr-service-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #25d366;
    color: #fff;
    padding: 9px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    &:hover { background: #1ebe5d; }
  }
}

// =============================================
// PORTFOLIO
// =============================================
.sr-portfolio-section {
  padding: 80px 0;
  background: $sr-dark;
}

.sr-portfolio-section .sr-section-header {
  h2 { color: $sr-white; }
  p { color: rgba(255,255,255,0.5); }
}

.sr-portfolio-filters {
  display: flex;
  gap: 12px;
  justify-content: center;
  flex-wrap: wrap;
  margin-bottom: 40px;
}

.sr-filter-btn {
  background: transparent;
  border: 2px solid rgba(255,255,255,0.15);
  color: rgba(255,255,255,0.6);
  padding: 9px 20px;
  border-radius: 25px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;

  &.active, &:hover {
    background: $sr-orange;
    border-color: $sr-orange;
    color: #fff;
  }
}

.sr-portfolio-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 24px;
}

.sr-portfolio-card {
  border-radius: 16px;
  overflow: hidden;
  background: $sr-gray;
  transition: transform 0.2s;
  &:hover { transform: translateY(-4px); }
  &:hover .sr-portfolio-overlay { opacity: 1; }
}

.sr-portfolio-img {
  height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  font-size: 64px;
}

.sr-portfolio-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s;

  .sr-portfolio-cta {
    background: $sr-orange;
    color: #fff;
    padding: 11px 22px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 6px;
  }
}

.sr-portfolio-info {
  padding: 16px;

  .sr-portfolio-type { font-size: 11px; font-weight: 700; color: $sr-orange; text-transform: uppercase; letter-spacing: 1px; }
  .sr-portfolio-name { font-size: 15px; font-weight: 700; color: $sr-white; margin: 4px 0 6px; }
  .sr-portfolio-desc { font-size: 13px; color: rgba(255,255,255,0.5); line-height: 1.5; }
}

// =============================================
// DIFFERENTIALS
// =============================================
.sr-differentials-section {
  padding: 80px 0;
  background: $sr-light;
}

.sr-diff-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 24px;
}

.sr-diff-card {
  background: $sr-white;
  border-radius: 16px;
  padding: 28px;
  border-left: 4px solid $sr-orange;
  transition: transform 0.2s;
  &:hover { transform: translateY(-3px); }

  .sr-diff-icon { font-size: 36px; margin-bottom: 12px; }
  h3 { font-size: 17px; font-weight: 800; color: $sr-text; margin-bottom: 8px; }
  p { font-size: 14px; color: $sr-muted; line-height: 1.6; margin: 0; }
}

// =============================================
// ABOUT
// =============================================
.sr-about-section {
  background: $sr-steel;
  padding: 80px 0;
}

.sr-about-grid {
  display: grid;
  grid-template-columns: 1fr 1.5fr;
  gap: 60px;
  align-items: center;

  @media (max-width: 768px) { grid-template-columns: 1fr; gap: 40px; }
}

.sr-about-visual { text-align: center; }

.sr-about-badge {
  font-size: 80px;
  display: block;
  margin-bottom: 32px;
}

.sr-about-stats-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.sr-about-stat {
  background: rgba(255,255,255,0.05);
  border-radius: 12px;
  padding: 16px;
  text-align: center;

  .sr-astat-num { display: block; font-size: 24px; font-weight: 900; color: $sr-orange; }
  .sr-astat-label { display: block; font-size: 12px; color: rgba(255,255,255,0.5); margin-top: 4px; }
}

.sr-about-text {
  h2 { font-size: clamp(24px, 3vw, 34px); font-weight: 800; color: $sr-white; margin: 8px 0 18px; }
  p { color: rgba(255,255,255,0.65); font-size: 15px; line-height: 1.7; margin-bottom: 14px; }
}

.sr-about-certifications {
  margin-top: 26px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.sr-cert {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: rgba(255,255,255,0.75);
  i { color: $sr-orange; }
}

// =============================================
// TESTIMONIALS
// =============================================
.sr-testimonials-section {
  padding: 80px 0;
  background: $sr-white;
}

.sr-testimonials-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
}

.sr-testimonial {
  background: $sr-light;
  border-radius: 16px;
  padding: 28px;
  border: 1px solid rgba($sr-orange, 0.1);

  .sr-testimonial-stars { color: $sr-yellow; font-size: 14px; margin-bottom: 14px; }
  p { font-size: 14px; color: $sr-muted; line-height: 1.7; font-style: italic; margin-bottom: 18px; }
}

.sr-testimonial-author {
  display: flex;
  align-items: center;
  gap: 12px;

  .sr-t-avatar { font-size: 36px; }
  .sr-t-name { display: block; font-size: 14px; font-weight: 700; color: $sr-text; }
  .sr-t-service { display: block; font-size: 12px; color: $sr-orange; }
}

// =============================================
// CONTACT
// =============================================
.sr-contact-section {
  padding: 80px 0;
  background: $sr-light;
}

.sr-contact-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  align-items: start;

  @media (max-width: 768px) { grid-template-columns: 1fr; gap: 40px; }
}

.sr-contact-info {
  h2 { font-size: clamp(22px, 3vw, 32px); font-weight: 800; margin: 8px 0 14px; }
  p { font-size: 15px; color: $sr-muted; margin-bottom: 28px; }
}

.sr-contact-list { display: flex; flex-direction: column; gap: 20px; }

.sr-contact-item {
  display: flex;
  gap: 14px;
  align-items: flex-start;

  .sr-ci-icon {
    width: 44px;
    height: 44px;
    background: rgba($sr-orange, 0.1);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    i { color: $sr-orange; font-size: 18px; }
  }

  h4 { font-size: 14px; font-weight: 700; color: $sr-text; margin-bottom: 2px; }
  p { font-size: 13px; color: $sr-muted; margin: 0; line-height: 1.5; }

  .sr-link { color: #25d366; font-weight: 700; font-size: 13px; text-decoration: none; }
}

.sr-cta-box {
  background: $sr-dark;
  border-radius: 20px;
  padding: 36px 28px;
  text-align: center;

  .sr-cta-icon { font-size: 48px; display: block; margin-bottom: 14px; }
  h3 { font-size: 22px; font-weight: 800; color: $sr-white; margin-bottom: 12px; }
  p { font-size: 14px; color: rgba(255,255,255,0.6); line-height: 1.6; margin-bottom: 24px; }
}

.sr-cta-trust {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-top: 16px;
  flex-wrap: wrap;

  span {
    font-size: 12px;
    color: rgba(255,255,255,0.5);
    display: flex;
    align-items: center;
    gap: 5px;
    i { color: $sr-orange; }
  }
}

// =============================================
// FOOTER
// =============================================
.sr-footer {
  background: $sr-dark;
  padding: 20px;
}

.sr-footer-inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
  max-width: 1100px;
  margin: 0 auto;
  font-size: 13px;
  color: rgba(255,255,255,0.35);

  .sr-footer-credit a {
    color: $sr-orange;
    font-weight: 700;
    text-decoration: none;
    &:hover { text-decoration: underline; }
  }
}

// =============================================
// WHATSAPP FLOAT
// =============================================
.sr-wpp-float {
  position: fixed;
  bottom: 72px;
  right: 28px;
  background: #25d366;
  color: #fff;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  box-shadow: 0 4px 20px rgba(37, 211, 102, 0.4);
  text-decoration: none;
  z-index: 999;
  transition: transform 0.2s;
  &:hover { transform: scale(1.1); }
}

// =============================================
// UNLI CTA FIXED BAR
// =============================================
.unli-cta-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  flex-wrap: wrap;
  background: #e67e22;
  padding: 12px 24px;
  text-decoration: none;
  color: #fff;
  box-shadow: 0 -4px 24px rgba(0,0,0,0.25);
  transition: background 0.2s;

  &:hover { background: #d35400; }

  .unli-cta-text {
    font-size: 14px;
    font-weight: 600;
    strong { font-weight: 800; }
  }

  .unli-cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #fff;
    color: #e67e22;
    padding: 8px 20px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 700;
    white-space: nowrap;
    i { font-size: 16px; color: #25d366; }
  }

  @media (max-width: 500px) {
    padding: 10px 16px;
    gap: 10px;
    .unli-cta-text { font-size: 13px; }
  }
}
</style>
