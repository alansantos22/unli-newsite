<template>
  <div class="demo-site clinica-demo">
    <!-- Demo Banner -->
    <div class="demo-banner">
      <i class="fas fa-eye"></i>
      Este é um modelo de demonstração — 
      <a href="/site-vitrine" class="demo-banner-link">quero um site assim para minha clínica</a>
      <span class="demo-badge">MODELO</span>
    </div>

    <!-- Header -->
    <header class="cl-header">
      <div class="cl-header-inner">
        <div class="cl-logo">
          <span class="cl-logo-icon">✨</span>
          <div>
            <span class="cl-logo-name">Bella Skin</span>
            <span class="cl-logo-tagline">Clínica de Estética</span>
          </div>
        </div>
        <nav class="cl-nav">
          <a href="#servicos">Serviços</a>
          <a href="#resultados">Resultados</a>
          <a href="#depoimentos">Depoimentos</a>
          <a href="#contato">Contato</a>
        </nav>
        <a :href="whatsappLink('Olá! Gostaria de agendar uma avaliação gratuita!')" target="_blank" class="cl-header-btn">
          <i class="fab fa-whatsapp"></i> Agendar Avaliação
        </a>
      </div>
    </header>

    <!-- Hero -->
    <section class="cl-hero">
      <div class="cl-hero-bg-dots"></div>
      <div class="cl-hero-content">
        <div class="cl-hero-text">
          <span class="cl-hero-badge">
            <i class="fas fa-star"></i>
            Avaliação gratuita
          </span>
          <h1 class="cl-hero-title">
            Realce sua beleza<br>
            <span>com cuidados de elite</span>
          </h1>
          <p class="cl-hero-sub">
            Tratamentos estéticos avançados com tecnologia de ponta e produtos premium. 
            Cada procedimento é personalizado para o seu tipo de pele.
          </p>
          <div class="cl-hero-actions">
            <a :href="whatsappLink('Olá! Gostaria de agendar uma avaliação gratuita!')" target="_blank" class="cl-btn-primary">
              <i class="fab fa-whatsapp"></i> Agendar Avaliação Grátis
            </a>
            <a href="#servicos" class="cl-btn-outline">
              <i class="fas fa-spa"></i> Ver Tratamentos
            </a>
          </div>
          <div class="cl-hero-trust">
            <div class="cl-trust-item">
              <i class="fas fa-user-md"></i>
              <span>Profissionais certificados</span>
            </div>
            <div class="cl-trust-item">
              <i class="fas fa-certificate"></i>
              <span>Produtos importados premium</span>
            </div>
            <div class="cl-trust-item">
              <i class="fas fa-shield-alt"></i>
              <span>Ambiente esterilizado</span>
            </div>
          </div>
        </div>
        <div class="cl-hero-card-area">
          <div class="cl-hero-card">
            <div class="cl-hero-card-icon">💆‍♀️</div>
            <div class="cl-hero-card-text">
              <span class="cl-hero-card-label">Mais queriho</span>
              <span class="cl-hero-card-name">Limpeza de Pele Profunda</span>
              <span class="cl-hero-card-detail">Resultado visível desde a 1ª sessão</span>
            </div>
          </div>
          <div class="cl-hero-card">
            <div class="cl-hero-card-icon">⭐</div>
            <div class="cl-hero-card-text">
              <span class="cl-hero-card-label">Avaliação</span>
              <span class="cl-hero-card-name">4.9 / 5.0 no Google</span>
              <span class="cl-hero-card-detail">+320 avaliações de clientes</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Serviços -->
    <section id="servicos" class="cl-services-section">
      <div class="cl-container">
        <div class="cl-section-header">
          <span class="cl-section-badge">Tratamentos</span>
          <h2>Nossos Serviços</h2>
          <p>Do básico ao mais avançado — cuidamos de você por inteiro</p>
        </div>

        <!-- Tabs -->
        <div class="cl-service-tabs">
          <button
            v-for="tab in serviceTabs"
            :key="tab.id"
            class="cl-tab"
            :class="{ active: activeTab === tab.id }"
            @click="activeTab = tab.id"
          >
            {{ tab.icon }} {{ tab.name }}
          </button>
        </div>

        <div class="cl-services-grid">
          <div
            v-for="service in filteredServices"
            :key="service.id"
            class="cl-service-card"
          >
            <div class="cl-service-icon">{{ service.emoji }}</div>
            <div class="cl-service-body">
              <h3 class="cl-service-name">{{ service.name }}</h3>
              <p class="cl-service-desc">{{ service.description }}</p>
              <div class="cl-service-details">
                <span class="cl-service-duration"><i class="fas fa-clock"></i> {{ service.duration }}</span>
                <span class="cl-service-sessions"><i class="fas fa-calendar-check"></i> {{ service.sessions }}</span>
              </div>
              <div class="cl-service-footer">
                <div class="cl-service-price">
                  <span class="cl-price-label">A partir de</span>
                  <span class="cl-price-value">R$ {{ service.price }}</span>
                </div>
                <a
                  :href="whatsappLink(`Olá! Tenho interesse no tratamento *${service.name}*. Gostaria de agendar!`)"
                  target="_blank"
                  class="cl-schedule-btn"
                >
                  <i class="fab fa-whatsapp"></i> Agendar
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Resultados / Antes e Depois (visual)-->
    <section id="resultados" class="cl-results-section">
      <div class="cl-container">
        <div class="cl-section-header">
          <span class="cl-section-badge">Transformações</span>
          <h2>Resultados reais</h2>
          <p>Veja como nossos clientes transformaram a pele</p>
        </div>

        <div class="cl-results-grid">
          <div class="cl-result-card" v-for="result in results" :key="result.id">
            <div class="cl-result-imgs">
              <div class="cl-result-before">
                <span class="cl-result-label before">Antes</span>
                <div class="cl-result-img-placeholder" :style="{ background: result.beforeBg }">
                  <span>{{ result.emoji }}</span>
                </div>
              </div>
              <div class="cl-result-arrow"><i class="fas fa-arrow-right"></i></div>
              <div class="cl-result-after">
                <span class="cl-result-label after">Depois</span>
                <div class="cl-result-img-placeholder glow" :style="{ background: result.afterBg }">
                  <span>{{ result.emojiAfter }}</span>
                </div>
              </div>
            </div>
            <div class="cl-result-info">
              <h4>{{ result.treatment }}</h4>
              <p>{{ result.description }}</p>
              <span class="cl-result-tag">{{ result.sessions }}</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Depoimentos -->
    <section id="depoimentos" class="cl-testimonials-section">
      <div class="cl-container">
        <div class="cl-section-header">
          <span class="cl-section-badge">Depoimentos</span>
          <h2>O que nossas clientes dizem</h2>
        </div>

        <div class="cl-testimonials-grid">
          <div class="cl-testimonial-card" v-for="t in testimonials" :key="t.id">
            <div class="cl-testimonial-stars">
              <i class="fas fa-star" v-for="s in 5" :key="s"></i>
            </div>
            <p class="cl-testimonial-text">"{{ t.text }}"</p>
            <div class="cl-testimonial-author">
              <div class="cl-testimonial-avatar">{{ t.avatar }}</div>
              <div>
                <span class="cl-author-name">{{ t.name }}</span>
                <span class="cl-author-treatment">{{ t.treatment }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Contato -->
    <section id="contato" class="cl-contact-section">
      <div class="cl-container cl-contact-grid">
        <div class="cl-contact-info">
          <span class="cl-section-badge">Fale Conosco</span>
          <h2>Agende sua consulta</h2>
          <p>Nossa equipe está pronta para te receber e criar um protocolo totalmente personalizado para você.</p>

          <div class="cl-contact-items">
            <div class="cl-contact-item">
              <div class="cl-ci-icon"><i class="fas fa-map-marker-alt"></i></div>
              <div>
                <h4>Endereço</h4>
                <p>Rua das Flores, 380 — Sala 12<br>Jardins — São Paulo/SP</p>
              </div>
            </div>
            <div class="cl-contact-item">
              <div class="cl-ci-icon"><i class="fas fa-clock"></i></div>
              <div>
                <h4>Horário</h4>
                <p>Seg–Sex: 9h às 20h<br>Sábado: 9h às 17h</p>
              </div>
            </div>
            <div class="cl-contact-item">
              <div class="cl-ci-icon"><i class="fab fa-whatsapp"></i></div>
              <div>
                <h4>WhatsApp</h4>
                <p>(11) 9 9999-0000</p>
              </div>
            </div>
          </div>
        </div>

        <div class="cl-contact-cta">
          <div class="cl-cta-card">
            <div class="cl-cta-emoji">✨</div>
            <h3>Avaliação Gratuita</h3>
            <p>Agende uma consulta de avaliação sem compromisso. Nossos especialistas vão analisar sua pele e montar o protocolo ideal.</p>
            <ul class="cl-cta-list">
              <li><i class="fas fa-check"></i> Análise de pele completa</li>
              <li><i class="fas fa-check"></i> Protocolo personalizado</li>
              <li><i class="fas fa-check"></i> Orçamento transparente</li>
              <li><i class="fas fa-check"></i> Sem pressão de venda</li>
            </ul>
            <a :href="whatsappLink('Olá! Gostaria de agendar uma avaliação gratuita!')" target="_blank" class="cl-btn-primary full">
              <i class="fab fa-whatsapp"></i> Agendar Avaliação Grátis
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="cl-footer">
      <div class="cl-footer-inner">
        <span>© 2025 Bella Skin Clínica de Estética • CNPJ 00.000.000/0001-00</span>
        <span class="cl-footer-credit">Site criado pela <a href="/site-vitrine" target="_blank">Unli</a></span>
      </div>
    </footer>

    <!-- WhatsApp Float -->
    <a :href="whatsappLink('Olá! Gostaria de agendar uma avaliação!')" target="_blank" class="cl-wpp-float" aria-label="Agendar pelo WhatsApp">
      <i class="fab fa-whatsapp"></i>
    </a>

    <!-- CTA Unli - Quero esse site! -->
    <a :href="`https://wa.me/${unliWhatsapp}?text=${encodeURIComponent('Olá! Vi o modelo de site para clínica de estética e quero um assim para a minha empresa!')}`"
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
  name: 'ClinicaEsteticaDemo',
  data() {
    return {
      activeTab: 'pele',
      whatsappNumber: '5511999990000',
      unliWhatsapp: process.env.VUE_APP_WHATSAPP_SDR || '5511911019666',
      serviceTabs: [
        { id: 'pele', name: 'Pele', icon: '✨' },
        { id: 'corpo', name: 'Corpo', icon: '💆' },
        { id: 'estetica', name: 'Estética Facial', icon: '💎' },
        { id: 'depilacao', name: 'Depilação', icon: '🌸' },
      ],
      services: [
        {
          id: 1, tab: 'pele', emoji: '🧴',
          name: 'Limpeza de Pele Profunda',
          description: 'Remoção de cravos, poros dilatados e excesso de oleosidade com esfoliação enzymatic e extração manual.',
          duration: '90 min', sessions: '1 sessão', price: '190,00'
        },
        {
          id: 2, tab: 'pele', emoji: '💧',
          name: 'Hidratação Intensiva',
          description: 'Protocolo com ácido hialurônico, vitamina C e peptídeos para pele seca e sem viço.',
          duration: '60 min', sessions: '3-5 sessões', price: '250,00'
        },
        {
          id: 3, tab: 'pele', emoji: '🌟',
          name: 'Microagulhamento',
          description: 'Estímulo de colágeno para cicatrizes, poros e rugas com radiofrequência associada.',
          duration: '75 min', sessions: '4-6 sessões', price: '380,00'
        },
        {
          id: 4, tab: 'corpo', emoji: '💆‍♀️',
          name: 'Drenagem Linfática',
          description: 'Técnica manual para reduzir inchaço, melhorar circulação e eliminar toxinas.',
          duration: '50 min', sessions: '10 sessões', price: '120,00'
        },
        {
          id: 5, tab: 'corpo', emoji: '🔥',
          name: 'Criolipólise',
          description: 'Eliminação definitiva de gordura localizada por congelamento. Abdômen, flancos e culote.',
          duration: '90 min', sessions: '1-2 sessões', price: '650,00'
        },
        {
          id: 6, tab: 'corpo', emoji: '⚡',
          name: 'Radiofrequência Corporal',
          description: 'Firmeza e elasticidade da pele do corpo. Ideal para flacidez pós-emagrecimento.',
          duration: '60 min', sessions: '8-10 sessões', price: '180,00'
        },
        {
          id: 7, tab: 'estetica', emoji: '💋',
          name: 'Design de Sobrancelha',
          description: 'Modelagem personalizada com henna, linha ou cera para realçar seu olhar.',
          duration: '45 min', sessions: '1 sessão', price: '80,00'
        },
        {
          id: 8, tab: 'estetica', emoji: '👁️',
          name: 'Extensão de Cílios',
          description: 'Fios premium de mink ou seda para olhar marcante e longo durável.',
          duration: '2h', sessions: '1 sessão', price: '180,00'
        },
        {
          id: 9, tab: 'depilacao', emoji: '🌸',
          name: 'Depilação a Laser',
          description: 'Tecnologia Alexandrita para remoção permanente de pelos. Todas as áreas.',
          duration: '30-60 min', sessions: '6-8 sessões', price: '150,00'
        },
      ],
      results: [
        {
          id: 1,
          treatment: 'Limpeza de Pele',
          description: 'Redução visível de poros e oleosidade logo na 1ª sessão',
          sessions: '1 sessão',
          emoji: '😶', emojiAfter: '😊',
          beforeBg: 'linear-gradient(135deg, #d4a5a5, #c08080)',
          afterBg: 'linear-gradient(135deg, #fce4ec, #f8bbd0)',
        },
        {
          id: 2,
          treatment: 'Microagulhamento',
          description: 'Melhora de cicatrizes de acne e textura da pele em 4 sessões',
          sessions: '4 sessões',
          emoji: '😔', emojiAfter: '🥰',
          beforeBg: 'linear-gradient(135deg, #c4a882, #b8956a)',
          afterBg: 'linear-gradient(135deg, #fef3e2, #fde8c8)',
        },
        {
          id: 3,
          treatment: 'Criolipólise',
          description: 'Redução de medidas e gordura localizada na região abdominal',
          sessions: '2 sessões',
          emoji: '😐', emojiAfter: '😍',
          beforeBg: 'linear-gradient(135deg, #b0b8c4, #8090a0)',
          afterBg: 'linear-gradient(135deg, #e8f5e9, #c8e6c9)',
        },
      ],
      testimonials: [
        {
          id: 1,
          text: 'Fiquei apaixonada com os resultados da limpeza de pele! Minha pele ficou completamente renovada. A equipe é super profissional e o ambiente é lindo.',
          name: 'Amanda S.',
          treatment: 'Limpeza de Pele',
          avatar: '👩'
        },
        {
          id: 2,
          text: 'Fiz o microagulhamento para minhas cicatrizes e ficou incrível! Depois de 4 sessões a diferença é absurda. Nunca me senti tão bem com minha pele.',
          name: 'Carla M.',
          treatment: 'Microagulhamento',
          avatar: '👩‍🦱'
        },
        {
          id: 3,
          text: 'A drenagem linfática mudou minha vida! Reduzi o inchaço que me incomodava há anos. Profissionais atenciosos, ambiente acolhedor. Super indico!',
          name: 'Fernanda L.',
          treatment: 'Drenagem Linfática',
          avatar: '👩‍🦰'
        },
      ]
    };
  },
  computed: {
    filteredServices() {
      return this.services.filter(s => s.tab === this.activeTab);
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
  background: linear-gradient(135deg, #4a1942 0%, #2d0a3d 100%);
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

  i { color: #f9a8d4; }

  .demo-banner-link {
    color: #f9a8d4;
    font-weight: 700;
    text-decoration: underline;
    &:hover { color: #fbbf24; }
  }

  .demo-badge {
    background: #f9a8d4;
    color: #4a1942;
    font-size: 10px;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 20px;
    letter-spacing: 1px;
  }
}

// =============================================
// CLINICA VARIABLES
// =============================================
$cl-rose: #be185d;
$cl-pink: #ec4899;
$cl-light-pink: #fce7f3;
$cl-gold: #d4af7a;
$cl-light: #fdf9ff;
$cl-text: #1e0a2e;
$cl-muted: #716082;
$cl-white: #ffffff;

.clinica-demo {
  font-family: 'Segoe UI', sans-serif;
  background: $cl-light;
  color: $cl-text;
}

.cl-container {
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 20px;
}

.cl-section-badge {
  display: inline-block;
  background: rgba($cl-pink, 0.12);
  color: $cl-pink;
  font-size: 12px;
  font-weight: 700;
  padding: 4px 14px;
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 10px;
}

.cl-section-header {
  text-align: center;
  margin-bottom: 40px;
  h2 { font-size: clamp(26px, 4vw, 38px); font-weight: 800; margin: 8px 0; }
  p { color: $cl-muted; font-size: 16px; }
}

// =============================================
// HEADER
// =============================================
.cl-header {
  background: $cl-white;
  box-shadow: 0 2px 20px rgba($cl-pink, 0.07);
  position: sticky;
  top: 45px;
  z-index: 999;
}

.cl-header-inner {
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 20px;
  display: flex;
  align-items: center;
  height: 64px;
  gap: 20px;
  justify-content: space-between;
}

.cl-logo {
  display: flex;
  align-items: center;
  gap: 10px;

  .cl-logo-icon { font-size: 24px; }
  .cl-logo-name { display: block; font-size: 20px; font-weight: 800; color: $cl-text; font-style: italic; }
  .cl-logo-tagline { display: block; font-size: 11px; color: $cl-muted; letter-spacing: 1px; }
}

.cl-nav {
  display: flex;
  gap: 24px;

  a {
    color: $cl-muted;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    &:hover { color: $cl-pink; }
  }

  @media (max-width: 768px) { display: none; }
}

.cl-header-btn {
  background: #25d366;
  color: #fff;
  padding: 10px 18px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 700;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
  &:hover { background: #1ebe5d; }
}

// =============================================
// HERO
// =============================================
.cl-hero {
  background: linear-gradient(145deg, #fdf2f8 0%, #fce7f3 40%, #fff0f6 100%);
  padding: 80px 20px;
  position: relative;
  overflow: hidden;

  &::before {
    content: '';
    position: absolute;
    top: -100px;
    right: -100px;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba($cl-pink, 0.08) 0%, transparent 70%);
    pointer-events: none;
  }
}

.cl-hero-bg-dots {
  position: absolute;
  inset: 0;
  background-image: radial-gradient(rgba($cl-pink, 0.06) 1px, transparent 1px);
  background-size: 30px 30px;
  pointer-events: none;
}

.cl-hero-content {
  max-width: 1100px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  gap: 60px;
  position: relative;

  @media (max-width: 768px) { flex-direction: column; gap: 40px; }
}

.cl-hero-text { flex: 1; }

.cl-hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba($cl-pink, 0.12);
  border: 1px solid rgba($cl-pink, 0.25);
  color: $cl-rose;
  font-size: 13px;
  font-weight: 700;
  padding: 6px 16px;
  border-radius: 20px;
  margin-bottom: 20px;

  i { color: $cl-gold; }
}

.cl-hero-title {
  font-size: clamp(32px, 5vw, 56px);
  font-weight: 900;
  color: $cl-text;
  line-height: 1.1;
  margin-bottom: 16px;

  span { color: $cl-pink; display: block; }
}

.cl-hero-sub {
  color: $cl-muted;
  font-size: 16px;
  line-height: 1.7;
  margin-bottom: 32px;
  max-width: 480px;
}

.cl-hero-actions {
  display: flex;
  gap: 14px;
  flex-wrap: wrap;
  margin-bottom: 32px;
}

.cl-btn-primary {
  background: $cl-pink;
  color: #fff;
  padding: 14px 28px;
  border-radius: 12px;
  font-size: 15px;
  font-weight: 700;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;

  &.full { width: 100%; justify-content: center; }
  &:hover { background: $cl-rose; transform: translateY(-2px); }
}

.cl-btn-outline {
  border: 2px solid rgba($cl-pink, 0.4);
  color: $cl-rose;
  background: transparent;
  padding: 13px 26px;
  border-radius: 12px;
  font-size: 15px;
  font-weight: 700;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
  &:hover { background: rgba($cl-pink, 0.08); border-color: $cl-pink; }
}

.cl-hero-trust {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
}

.cl-trust-item {
  display: flex;
  align-items: center;
  gap: 8px;
  color: $cl-muted;
  font-size: 13px;
  i { color: $cl-gold; font-size: 14px; }
}

.cl-hero-card-area {
  flex: 0 0 auto;
  display: flex;
  flex-direction: column;
  gap: 16px;

  @media (max-width: 768px) { width: 100%; }
}

.cl-hero-card {
  background: $cl-white;
  border-radius: 16px;
  padding: 18px 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: 0 4px 20px rgba($cl-pink, 0.1);
  width: 300px;

  @media (max-width: 768px) { width: 100%; }

  .cl-hero-card-icon { font-size: 36px; }

  .cl-hero-card-label { display: block; font-size: 11px; font-weight: 700; color: $cl-pink; text-transform: uppercase; }
  .cl-hero-card-name { display: block; font-size: 15px; font-weight: 700; color: $cl-text; }
  .cl-hero-card-detail { display: block; font-size: 12px; color: $cl-muted; }
}

// =============================================
// SERVICES
// =============================================
.cl-services-section {
  padding: 80px 0;
  background: $cl-white;
}

.cl-service-tabs {
  display: flex;
  gap: 12px;
  justify-content: center;
  flex-wrap: wrap;
  margin-bottom: 40px;
}

.cl-tab {
  background: transparent;
  border: 2px solid rgba($cl-pink, 0.25);
  color: $cl-muted;
  padding: 10px 22px;
  border-radius: 30px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;

  &.active, &:hover {
    background: $cl-pink;
    border-color: $cl-pink;
    color: #fff;
  }
}

.cl-services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 24px;
}

.cl-service-card {
  background: $cl-light;
  border-radius: 20px;
  overflow: hidden;
  border: 1px solid rgba($cl-pink, 0.1);
  display: flex;
  flex-direction: column;
  transition: transform 0.2s, box-shadow 0.2s;

  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 30px rgba($cl-pink, 0.12);
  }

  .cl-service-icon {
    background: linear-gradient(135deg, rgba($cl-pink, 0.08), rgba($cl-pink, 0.04));
    padding: 28px;
    font-size: 48px;
    text-align: center;
  }

  .cl-service-body { padding: 20px; flex: 1; display: flex; flex-direction: column; }

  .cl-service-name { font-size: 17px; font-weight: 800; color: $cl-text; margin-bottom: 8px; }
  .cl-service-desc { font-size: 14px; color: $cl-muted; line-height: 1.6; flex: 1; margin-bottom: 12px; }

  .cl-service-details {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;

    span { font-size: 12px; color: $cl-muted; display: flex; align-items: center; gap: 5px; }
    i { color: $cl-pink; }
  }

  .cl-service-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 14px;
    border-top: 1px solid rgba($cl-pink, 0.1);
  }

  .cl-price-label { display: block; font-size: 11px; color: $cl-muted; }
  .cl-price-value { display: block; font-size: 20px; font-weight: 900; color: $cl-rose; }

  .cl-schedule-btn {
    background: #25d366;
    color: #fff;
    padding: 9px 16px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    &:hover { background: #1ebe5d; }
  }
}

// =============================================
// RESULTS
// =============================================
.cl-results-section {
  padding: 80px 0;
  background: $cl-light-pink;
}

.cl-results-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 28px;
}

.cl-result-card {
  background: $cl-white;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba($cl-pink, 0.08);
}

.cl-result-imgs {
  display: flex;
  align-items: center;
  padding: 24px 20px 16px;
  gap: 12px;
}

.cl-result-before, .cl-result-after {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.cl-result-label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  padding: 2px 10px;
  border-radius: 20px;

  &.before { background: #fee2e2; color: #991b1b; }
  &.after { background: #d1fae5; color: #065f46; }
}

.cl-result-img-placeholder {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;

  &.glow { box-shadow: 0 0 20px rgba($cl-pink, 0.3); }
}

.cl-result-arrow {
  color: $cl-pink;
  font-size: 20px;
}

.cl-result-info {
  padding: 0 20px 20px;

  h4 { font-size: 16px; font-weight: 700; color: $cl-text; margin-bottom: 6px; }
  p { font-size: 13px; color: $cl-muted; line-height: 1.5; margin-bottom: 10px; }
}

.cl-result-tag {
  background: rgba($cl-pink, 0.1);
  color: $cl-rose;
  font-size: 12px;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 20px;
}

// =============================================
// TESTIMONIALS
// =============================================
.cl-testimonials-section {
  padding: 80px 0;
  background: $cl-white;
}

.cl-testimonials-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
}

.cl-testimonial-card {
  background: $cl-light;
  border-radius: 20px;
  padding: 28px;
  border: 1px solid rgba($cl-pink, 0.1);

  .cl-testimonial-stars { color: $cl-gold; margin-bottom: 14px; font-size: 14px; }
  .cl-testimonial-text { font-size: 14px; color: $cl-muted; line-height: 1.7; margin-bottom: 20px; font-style: italic; }
}

.cl-testimonial-author {
  display: flex;
  align-items: center;
  gap: 12px;

  .cl-testimonial-avatar { font-size: 36px; }
  .cl-author-name { display: block; font-size: 15px; font-weight: 700; color: $cl-text; }
  .cl-author-treatment { display: block; font-size: 12px; color: $cl-pink; }
}

// =============================================
// CONTACT
// =============================================
.cl-contact-section {
  padding: 80px 0;
  background: $cl-light;
}

.cl-contact-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  align-items: start;

  @media (max-width: 768px) { grid-template-columns: 1fr; gap: 40px; }
}

.cl-contact-info {
  h2 { font-size: clamp(24px, 3vw, 34px); font-weight: 800; color: $cl-text; margin: 8px 0 16px; }
  p { font-size: 15px; color: $cl-muted; line-height: 1.7; margin-bottom: 28px; }
}

.cl-contact-items { display: flex; flex-direction: column; gap: 20px; }

.cl-contact-item {
  display: flex;
  align-items: flex-start;
  gap: 14px;

  .cl-ci-icon {
    width: 44px;
    height: 44px;
    background: rgba($cl-pink, 0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    i { color: $cl-pink; font-size: 18px; }
  }

  h4 { font-size: 14px; font-weight: 700; color: $cl-text; margin-bottom: 4px; }
  p { font-size: 14px; color: $cl-muted; line-height: 1.5; margin: 0; }
}

.cl-cta-card {
  background: $cl-white;
  border-radius: 24px;
  padding: 36px 28px;
  box-shadow: 0 8px 40px rgba($cl-pink, 0.1);
  text-align: center;
  border: 2px solid rgba($cl-pink, 0.1);

  .cl-cta-emoji { font-size: 48px; margin-bottom: 16px; }
  h3 { font-size: 22px; font-weight: 800; color: $cl-text; margin-bottom: 12px; }
  p { font-size: 14px; color: $cl-muted; line-height: 1.6; margin-bottom: 20px; }
}

.cl-cta-list {
  list-style: none;
  padding: 0;
  margin: 0 0 24px;
  text-align: left;
  display: flex;
  flex-direction: column;
  gap: 8px;

  li {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: $cl-text;
    i { color: $cl-pink; }
  }
}

// =============================================
// FOOTER
// =============================================
.cl-footer {
  background: $cl-text;
  padding: 20px;
}

.cl-footer-inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
  max-width: 1100px;
  margin: 0 auto;
  font-size: 13px;
  color: rgba(255,255,255,0.4);

  .cl-footer-credit a {
    color: #f9a8d4;
    font-weight: 700;
    text-decoration: none;
    &:hover { text-decoration: underline; }
  }
}

// =============================================
// WHATSAPP FLOAT
// =============================================
.cl-wpp-float {
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
