<template>
  <div class="demo-site hamburgueria-demo">

    <!-- Header — Minimal, editorial -->
    <header class="hb-header" :class="{ 'hb-header--scrolled': headerScrolled }">
      <div class="hb-header-inner">
        <div class="hb-logo">
          <span class="hb-logo-name">House<span class="hb-logo-accent">.</span></span>
        </div>
        <nav class="hb-nav">
          <router-link to="/demo/hamburgueria/cardapio">Cardápio</router-link>
          <a href="#sobre">Sobre</a>
          <a href="#galeria">Galeria</a>
          <a href="#contato">Contato</a>
        </nav>
        <div class="hb-header-right">
          <div class="hb-header-hours"><i class="fas fa-clock"></i> 18h – 00h</div>
          <a :href="whatsappLink('Olá! Gostaria de fazer um pedido!')" target="_blank" class="hb-header-order">
            Fazer Pedido
          </a>
        </div>
      </div>
    </header>

    <!-- Hero — Asymmetric Editorial Layout -->
    <section class="hb-hero">
      <div class="hb-hero-inner">
        <div class="hb-hero-content">
          <div class="hb-hero-eyebrow">
            <span class="hb-eyebrow-line"></span>
            <span class="hb-eyebrow-text">Artesanais desde 2018</span>
          </div>
          <h1 class="hb-hero-title">
            Sabor que<br>
            <span class="hb-title-italic">não se esquece.</span>
          </h1>
          <p class="hb-hero-sub">Blend exclusivo, pão brioche feito na casa, ingredientes frescos do dia. Cada detalhe pensado para uma experiência real.</p>
          <router-link to="/demo/hamburgueria/cardapio" class="hb-hero-cta">
            Ver Cardápio
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </router-link>
          <div class="hb-hero-meta">
            <span><i class="fas fa-star"></i> 4.9 Google</span>
            <span class="hb-meta-dot"></span>
            <span><i class="fas fa-motorcycle"></i> Delivery</span>
            <span class="hb-meta-dot"></span>
            <span>Terça a Domingo</span>
          </div>
        </div>
        <div class="hb-hero-visual">
          <div class="hb-hero-img-main">
            <img
              src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&h=900&fit=crop&auto=format&q=85"
              alt="House Smash Burger"
            />
          </div>
          <div class="hb-hero-float-card">
            <span class="hb-float-label">Hoje</span>
            <span class="hb-float-name">House Smash</span>
            <span class="hb-float-price">R$ 38,90</span>
          </div>
        </div>
      </div>
      <!-- Organic shape separator -->
      <div class="hb-hero-wave">
        <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
          <path d="M0,60 C360,120 720,0 1080,60 C1260,90 1380,40 1440,60 L1440,120 L0,120 Z" fill="#faf6f1"/>
        </svg>
      </div>
    </section>

    <!-- Destaques — Bento Grid with Featured Card -->
    <section id="cardapio" class="hb-highlight-section">
      <div class="hb-container">
        <div class="hb-section-header hb-section-header--left">
          <span class="hb-section-eyebrow">Os mais pedidos</span>
          <h2>Destaques<br><span class="hb-heading-light">do cardápio</span></h2>
        </div>

        <div class="hb-bento-grid">
          <!-- Featured / Burger do Mês — double-sized card -->
          <div class="hb-bento-featured">
            <div class="hb-bento-featured-img">
              <img :src="featuredItems[0].image.replace('w=160&h=160', 'w=640&h=600')" :alt="featuredItems[0].name" loading="lazy" />
              <span class="hb-bento-tag">Mais Pedido</span>
            </div>
            <div class="hb-bento-featured-body">
              <h3>{{ featuredItems[0].name }}</h3>
              <p>{{ featuredItems[0].description }}</p>
              <div class="hb-bento-featured-footer">
                <span class="hb-price-editorial">R$ {{ featuredItems[0].price }}</span>
                <a :href="whatsappLink(`Olá! Quero pedir: *${featuredItems[0].name}* - R$ ${featuredItems[0].price}`)" target="_blank" class="hb-btn-order-sm">
                  <i class="fab fa-whatsapp"></i> Pedir
                </a>
              </div>
            </div>
          </div>

          <!-- Regular cards -->
          <div
            v-for="item in featuredItems.slice(1)"
            :key="item.id"
            class="hb-bento-card"
          >
            <div class="hb-bento-card-img">
              <img :src="item.image.replace('w=160&h=160', 'w=420&h=320')" :alt="item.name" loading="lazy" />
              <span v-if="item.badge" class="hb-bento-badge" :class="item.badge.type">{{ item.badge.text }}</span>
            </div>
            <div class="hb-bento-card-body">
              <h3>{{ item.name }}</h3>
              <p>{{ item.description }}</p>
              <div class="hb-bento-card-footer">
                <span class="hb-price-editorial">R$ {{ item.price }}</span>
                <a :href="whatsappLink(`Olá! Quero pedir: *${item.name}* - R$ ${item.price}`)" target="_blank" class="hb-btn-order-sm">
                  <i class="fab fa-whatsapp"></i> Pedir
                </a>
              </div>
            </div>
          </div>
        </div>

        <div class="hb-highlight-more">
          <router-link to="/demo/hamburgueria/cardapio" class="hb-btn-editorial">
            Ver Cardápio Completo
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Sobre — Overlapping Elements -->
    <section id="sobre" class="hb-about-section">
      <div class="hb-container">
        <div class="hb-about-layout">
          <div class="hb-about-visual">
            <div class="hb-about-img-wrap">
              <img
                src="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=640&h=480&fit=crop&auto=format&q=80"
                alt="Nossa cozinha artesanal"
              />
            </div>
            <div class="hb-about-stats">
              <div class="hb-stat"><span class="hb-stat-num">6+</span><span class="hb-stat-label">anos</span></div>
              <div class="hb-stat-divider"></div>
              <div class="hb-stat"><span class="hb-stat-num">4.9</span><span class="hb-stat-label">google</span></div>
              <div class="hb-stat-divider"></div>
              <div class="hb-stat"><span class="hb-stat-num">2k+</span><span class="hb-stat-label">clientes</span></div>
            </div>
          </div>
          <div class="hb-about-text">
            <span class="hb-section-eyebrow">Nossa história</span>
            <h2>Feito com paixão,<br><span class="hb-heading-light">servido com alma.</span></h2>
            <p>
              A House of Burgers nasceu de uma paixão por hambúrgueres artesanais de verdade.
              Começamos pequenos, com receitas testadas em casa e muita vontade de fazer diferente.
            </p>
            <p>
              Hoje, somos referência na cidade em sabor, qualidade e atendimento.
              Cada lanche é preparado na hora, com blend exclusivo e ingredientes frescos do dia.
            </p>
            <div class="hb-about-pillars">
              <div class="hb-pillar">
                <span class="hb-pillar-icon"><i class="fas fa-seedling"></i></span>
                <div>
                  <strong>Ingredientes frescos</strong>
                  <span>Renovados diariamente</span>
                </div>
              </div>
              <div class="hb-pillar">
                <span class="hb-pillar-icon"><i class="fas fa-bread-slice"></i></span>
                <div>
                  <strong>Pão brioche artesanal</strong>
                  <span>Feito na casa</span>
                </div>
              </div>
              <div class="hb-pillar">
                <span class="hb-pillar-icon"><i class="fas fa-fire-flame-curved"></i></span>
                <div>
                  <strong>Blend exclusivo</strong>
                  <span>Sem conservantes</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Galeria — Editorial Mosaic -->
    <section id="galeria" class="hb-gallery-section">
      <div class="hb-container">
        <div class="hb-section-header hb-section-header--center">
          <span class="hb-section-eyebrow">Nossos pratos</span>
          <h2>Feitos para<br><span class="hb-heading-light">impressionar</span></h2>
        </div>
        <div class="hb-gallery-mosaic">
          <div class="hb-mosaic-item hb-mosaic-tall">
            <img
              src="https://images.unsplash.com/photo-1571091718767-18b5b1457add?w=600&h=800&fit=crop&auto=format&q=80"
              alt="House Smash Burger destaque"
              loading="lazy"
            />
            <span class="hb-mosaic-label">Destaque da Casa</span>
          </div>
          <div class="hb-mosaic-item">
            <img
              src="https://images.unsplash.com/photo-1553979459-d2229ba7433b?w=480&h=360&fit=crop&auto=format&q=80"
              alt="Double Cheese Burger"
              loading="lazy"
            />
          </div>
          <div class="hb-mosaic-item">
            <img
              src="https://images.unsplash.com/photo-1579954115545-a95591f28bfc?w=480&h=360&fit=crop&auto=format&q=80"
              alt="Milkshake Artesanal"
              loading="lazy"
            />
          </div>
          <div class="hb-mosaic-item hb-mosaic-wide">
            <img
              src="https://images.unsplash.com/photo-1561758033-d89a9ad46330?w=800&h=400&fit=crop&auto=format&q=80"
              alt="Combo Burger e Batata"
              loading="lazy"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- Contato — Clean editorial -->
    <section id="contato" class="hb-contact-section">
      <div class="hb-container">
        <div class="hb-section-header hb-section-header--center">
          <span class="hb-section-eyebrow">Fale conosco</span>
          <h2>Onde nos<br><span class="hb-heading-light">encontrar</span></h2>
        </div>
        <div class="hb-contact-grid">
          <div class="hb-contact-card">
            <div class="hb-contact-icon"><i class="fas fa-map-marker-alt"></i></div>
            <h4>Endereço</h4>
            <p>Rua das Delícias, 247<br>Centro — São Paulo/SP</p>
          </div>
          <div class="hb-contact-card">
            <div class="hb-contact-icon"><i class="fas fa-clock"></i></div>
            <h4>Horário</h4>
            <p>Terça a Domingo<br>18h00 – 00h00</p>
          </div>
          <div class="hb-contact-card">
            <div class="hb-contact-icon"><i class="fab fa-whatsapp"></i></div>
            <h4>Pedidos</h4>
            <p>(11) 9 9999-0000<br>
              <a :href="whatsappLink('Olá! Gostaria de fazer um pedido!')" target="_blank" class="hb-wpp-link">
                Clique para pedir
              </a>
            </p>
          </div>
          <div class="hb-contact-card">
            <div class="hb-contact-icon"><i class="fab fa-instagram"></i></div>
            <h4>Instagram</h4>
            <p>@houseofburgers<br>Fotos e novidades</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Final — Bold typography -->
    <section class="hb-cta">
      <div class="hb-container">
        <h2 class="hb-cta-title">Bora<br><span class="hb-title-italic">pedir?</span></h2>
        <p>Mande seu pedido direto no WhatsApp.</p>
        <a :href="whatsappLink('Olá! Quero fazer um pedido!')" target="_blank" class="hb-btn-cta">
          <i class="fab fa-whatsapp"></i> Fazer Pedido
        </a>
      </div>
    </section>

    <!-- Footer — Minimal -->
    <footer class="hb-footer">
      <div class="hb-container hb-footer-inner">
        <span class="hb-footer-brand">House<span class="hb-logo-accent">.</span></span>
        <p>© 2025 House of Burgers · Todos os direitos reservados</p>
        <p class="hb-footer-credit">
          Site criado pela <a href="/site-vitrine" target="_blank">Unli</a>
        </p>
      </div>
    </footer>

    <!-- WhatsApp Float — Discreet -->
    <a :href="whatsappLink('Olá! Gostaria de fazer um pedido!')" target="_blank" class="hb-wpp-float" aria-label="Fazer pedido pelo WhatsApp">
      <i class="fab fa-whatsapp"></i>
    </a>

    <!-- CTA Unli -->
    <a :href="`https://wa.me/${unliWhatsapp}?text=${encodeURIComponent('Olá! Vi o modelo de site para hamburgueria e quero um assim para a minha empresa!')}`"
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
  name: 'HamburgueriaDemo',
  data() {
    return {
      headerScrolled: false,
      activeCategory: 'burgers',
      whatsappNumber: '5511999990000',
      unliWhatsapp: process.env.VUE_APP_WHATSAPP_SDR || '5511911019666',
      categories: [
        { id: 'burgers', name: 'Burgers', icon: 'fas fa-burger' },
        { id: 'combos', name: 'Combos', icon: 'fas fa-utensils' },
        { id: 'bebidas', name: 'Bebidas', icon: 'fas fa-beer-mug-empty' },
        { id: 'sobremesas', name: 'Sobremesas', icon: 'fas fa-ice-cream' },
      ],
      menuItems: [
        {
          id: 1, category: 'burgers',
          image: 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=160&h=160&fit=crop&auto=format&q=80',
          name: 'House Smash Burger',
          description: 'Blend 180g, queijo cheddar, bacon crocante, alface americana, tomate e molho especial da casa',
          price: '38,90',
          featured: true,
          badge: { type: 'destaque', text: '⭐ Mais Pedido' }
        },
        {
          id: 2, category: 'burgers',
          image: 'https://images.unsplash.com/photo-1553979459-d2229ba7433b?w=160&h=160&fit=crop&auto=format&q=80',
          name: 'Double Cheese',
          description: 'Dois blends 120g, duplo cheddar derretido, picles crocante, cebola caramelizada e mostarda',
          price: '42,90',
          badge: null
        },
        {
          id: 3, category: 'burgers',
          image: 'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?w=160&h=160&fit=crop&auto=format&q=80',
          name: 'Vulcão Burger',
          description: 'Blend 180g, jalapeño, queijo pepper jack, maionese de alho, cebola crispy e molho picante',
          price: '44,90',
          badge: { type: 'novo', text: '🌶️ Picante' }
        },
        {
          id: 4, category: 'burgers',
          image: 'https://images.unsplash.com/photo-1520072959219-c595dc870360?w=160&h=160&fit=crop&auto=format&q=80',
          name: 'Green Smash',
          description: 'Blend 180g, guacamole fresco, queijo brie, rúcula, tomate seco e maionese de limão siciliano',
          price: '46,90',
          badge: { type: 'novo', text: '✨ Novo' }
        },
        {
          id: 5, category: 'combos',
          image: 'https://images.unsplash.com/photo-1561758033-d89a9ad46330?w=160&h=160&fit=crop&auto=format&q=80',
          name: 'Combo House Smash',
          description: 'House Smash Burger + batata frita artesanal + refrigerante 350ml',
          price: '52,90',
          featured: true,
          badge: { type: 'destaque', text: '💰 Econômico' }
        },
        {
          id: 6, category: 'combos',
          image: 'https://images.unsplash.com/photo-1476224203421-9ac39bcb3327?w=160&h=160&fit=crop&auto=format&q=80',
          name: 'Combo Double Cheese',
          description: 'Double Cheese + onion rings + refrigerante 350ml',
          price: '58,90',
          badge: null
        },
        {
          id: 7, category: 'bebidas',
          image: 'https://images.unsplash.com/photo-1554866585-cd94860890b7?w=160&h=160&fit=crop&auto=format&q=80',
          name: 'Refrigerante Lata',
          description: 'Coca-Cola, Guaraná ou Sprite — 350ml gelado',
          price: '7,90',
          badge: null
        },
        {
          id: 8, category: 'bebidas',
          image: 'https://images.unsplash.com/photo-1535958636474-b021ee887b13?w=160&h=160&fit=crop&auto=format&q=80',
          name: 'Cerveja Artesanal',
          description: 'IPA, Witbier ou Pale Ale — garrafa 355ml',
          price: '16,90',
          badge: null
        },
        {
          id: 9, category: 'bebidas',
          image: 'https://images.unsplash.com/photo-1613478223719-2ab802602423?w=160&h=160&fit=crop&auto=format&q=80',
          name: 'Suco Natural',
          description: 'Laranja, limão, maracujá ou abacaxi — 400ml',
          price: '12,90',
          badge: null
        },
        {
          id: 10, category: 'sobremesas',
          image: 'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?w=160&h=160&fit=crop&auto=format&q=80',
          name: 'Milkshake Artesanal',
          description: 'Chocolate belga, morango ou baunilha — 400ml cremoso',
          price: '22,90',
          featured: true,
          badge: { type: 'destaque', text: '❤️ Favorito' }
        },
        {
          id: 11, category: 'sobremesas',
          image: 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=160&h=160&fit=crop&auto=format&q=80',
          name: 'Brownie Quente',
          description: 'Brownie de chocolate com sorvete de baunilha e calda de caramelo',
          price: '18,90',
          badge: null
        },
      ]
    };
  },
  computed: {
    filteredItems() {
      return this.menuItems.filter(i => i.category === this.activeCategory);
    },
    featuredItems() {
      return this.menuItems.filter(i => i.featured);
    }
  },
  mounted() {
    this._onScroll = () => {
      this.headerScrolled = window.scrollY > 60;
    };
    window.addEventListener('scroll', this._onScroll, { passive: true });
  },
  beforeUnmount() {
    window.removeEventListener('scroll', this._onScroll);
  },
  methods: {
    whatsappLink(message) {
      return `https://wa.me/${this.whatsappNumber}?text=${encodeURIComponent(message)}`;
    },
    largeImage(url) {
      return url.replace('w=160&h=160', 'w=420&h=280');
    }
  }
};
</script>

<style lang="scss" scoped>
// =============================================
// EDITORIAL HAMBURGUERIA — CURATED DESIGN
// Inspired by Honest Burgers / Shake Shack / Awwwards
// =============================================

// --- Google Fonts import ---
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,700;1,9..40,400&family=Playfair+Display:ital,wght@0,700;0,800;0,900;1,700;1,800&display=swap');

// --- Palette ---
$hb-black: #111111;
$hb-charcoal: #1c1c1c;
$hb-dark: #1a1a1a;
$hb-cream: #faf6f1;
$hb-warm: #f5efe8;
$hb-gold: #c8963e;
$hb-gold-light: rgba(200, 150, 62, 0.12);
$hb-red: #b5281e;
$hb-green-wpp: #25d366;
$hb-text: #2a2a2a;
$hb-muted: #7a7168;
$hb-border: rgba(0,0,0,0.08);

// --- Fonts ---
$font-serif: 'Playfair Display', Georgia, 'Times New Roman', serif;
$font-sans: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;

// --- Base ---
.hamburgueria-demo {
  font-family: $font-sans;
  color: $hb-text;
  background: $hb-cream;
  -webkit-font-smoothing: antialiased;
  overflow-x: hidden;
}

.hb-container {
  max-width: 1180px;
  margin: 0 auto;
  padding: 0 24px;
}

// --- Section Eyebrow ---
.hb-section-eyebrow {
  display: inline-block;
  font-family: $font-sans;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 2.5px;
  text-transform: uppercase;
  color: $hb-gold;
  margin-bottom: 16px;
}

// --- Section Headers ---
.hb-section-header {
  margin-bottom: 56px;

  &--left { text-align: left; }
  &--center { text-align: center; }

  h2 {
    font-family: $font-serif;
    font-size: clamp(32px, 5vw, 52px);
    font-weight: 800;
    color: $hb-black;
    line-height: 1.1;
    margin: 0;
  }
}

.hb-heading-light {
  font-weight: 700;
  color: $hb-muted;
}

// =============================================
// HEADER — Minimal, transparent → solid on scroll
// =============================================
.hb-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 999;
  transition: background 0.35s, box-shadow 0.35s, backdrop-filter 0.35s;
  background: transparent;
}

.hb-header--scrolled {
  background: rgba(26, 26, 26, 0.96);
  backdrop-filter: blur(12px);
  box-shadow: 0 1px 0 rgba(255,255,255,0.06);
}

.hb-header-inner {
  max-width: 1180px;
  margin: 0 auto;
  padding: 0 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 72px;
  gap: 32px;
}

.hb-logo {
  flex-shrink: 0;
}

.hb-logo-name {
  font-family: $font-serif;
  font-size: 24px;
  font-weight: 900;
  color: #fff;
  letter-spacing: -0.5px;
}

.hb-logo-accent {
  color: $hb-gold;
}

.hb-nav {
  display: flex;
  gap: 36px;

  a {
    color: rgba(255,255,255,0.65);
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    transition: color 0.25s;
    position: relative;

    &::after {
      content: '';
      position: absolute;
      bottom: -4px;
      left: 0;
      width: 0;
      height: 1px;
      background: $hb-gold;
      transition: width 0.3s;
    }

    &:hover {
      color: #fff;
      &::after { width: 100%; }
    }
  }

  @media (max-width: 768px) { display: none; }
}

.hb-header-right {
  display: flex;
  align-items: center;
  gap: 20px;
}

.hb-header-hours {
  color: rgba(255,255,255,0.5);
  font-size: 12px;
  font-weight: 500;
  letter-spacing: 0.3px;

  i { margin-right: 4px; font-size: 11px; }

  @media (max-width: 640px) { display: none; }
}

.hb-header-order {
  background: transparent;
  color: #fff;
  border: 1px solid rgba(255,255,255,0.25);
  padding: 9px 22px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 700;
  text-decoration: none;
  letter-spacing: 1px;
  text-transform: uppercase;
  transition: background 0.25s, border-color 0.25s;
  white-space: nowrap;

  &:hover {
    background: $hb-gold;
    border-color: $hb-gold;
  }
}

// =============================================
// HERO — Asymmetric Editorial Split
// =============================================
.hb-hero {
  position: relative;
  background: $hb-charcoal;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  overflow: hidden;
}

.hb-hero-inner {
  display: flex;
  align-items: flex-end;
  max-width: 1180px;
  margin: 0 auto;
  padding: 160px 24px 100px;
  gap: 40px;
  width: 100%;
  position: relative;
  z-index: 2;

  @media (max-width: 900px) {
    flex-direction: column;
    align-items: flex-start;
    padding: 140px 24px 80px;
  }
}

.hb-hero-content {
  flex: 1;
  max-width: 540px;
  padding-bottom: 20px;
}

.hb-hero-eyebrow {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 28px;
}

.hb-eyebrow-line {
  width: 40px;
  height: 1px;
  background: $hb-gold;
}

.hb-eyebrow-text {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 3px;
  text-transform: uppercase;
  color: $hb-gold;
}

.hb-hero-title {
  font-family: $font-serif;
  font-size: clamp(44px, 7vw, 80px);
  font-weight: 900;
  color: #fff;
  line-height: 1.0;
  margin: 0 0 24px;
  letter-spacing: -1px;
}

.hb-title-italic {
  font-style: italic;
  font-weight: 700;
  color: $hb-gold;
}

.hb-hero-sub {
  color: rgba(255,255,255,0.55);
  font-size: 16px;
  line-height: 1.7;
  margin-bottom: 36px;
  max-width: 420px;
}

.hb-hero-cta {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  background: $hb-gold;
  color: #fff;
  padding: 16px 36px;
  border-radius: 4px;
  font-size: 14px;
  font-weight: 700;
  text-decoration: none;
  letter-spacing: 1px;
  text-transform: uppercase;
  transition: background 0.25s, transform 0.25s;
  margin-bottom: 40px;

  svg {
    transition: transform 0.3s;
  }

  &:hover {
    background: darken($hb-gold, 8%);
    transform: translateY(-2px);

    svg { transform: translateX(4px); }
  }
}

.hb-hero-meta {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;

  span {
    color: rgba(255,255,255,0.4);
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 6px;

    i { color: $hb-gold; font-size: 11px; }
  }
}

.hb-meta-dot {
  width: 3px;
  height: 3px;
  border-radius: 50%;
  background: rgba(255,255,255,0.2);
}

// Hero Visual — Image bleeds to edge
.hb-hero-visual {
  flex: 0 0 45%;
  position: relative;
  align-self: stretch;

  @media (max-width: 900px) {
    width: 100%;
    flex: none;
    max-height: 400px;
  }
}

.hb-hero-img-main {
  position: relative;
  height: 100%;
  min-height: 480px;
  overflow: hidden;
  border-radius: 8px 8px 0 0;

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 8s ease;
  }

  &:hover img {
    transform: scale(1.04);
  }

  // Gradient overlay at bottom to blend
  &::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 40%;
    background: linear-gradient(to top, rgba(28,28,28,0.7) 0%, transparent 100%);
    pointer-events: none;
  }

  @media (max-width: 900px) {
    min-height: 320px;
    border-radius: 8px;
  }
}

.hb-hero-float-card {
  position: absolute;
  bottom: 30px;
  left: -20px;
  background: #fff;
  padding: 16px 22px;
  border-radius: 6px;
  box-shadow: 0 16px 48px rgba(0,0,0,0.3);
  z-index: 3;
  min-width: 160px;
  animation: floatIn 0.6s ease-out 0.5s both;

  @media (max-width: 900px) {
    bottom: 20px;
    left: 20px;
  }
}

@keyframes floatIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.hb-float-label {
  display: block;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: $hb-gold;
  margin-bottom: 4px;
}

.hb-float-name {
  display: block;
  font-family: $font-serif;
  font-size: 17px;
  font-weight: 800;
  color: $hb-black;
  margin-bottom: 2px;
}

.hb-float-price {
  display: block;
  font-size: 20px;
  font-weight: 700;
  color: $hb-red;
}

// Hero wave separator (organic shape)
.hb-hero-wave {
  position: absolute;
  bottom: -1px;
  left: 0;
  right: 0;
  z-index: 3;
  line-height: 0;

  svg {
    width: 100%;
    height: 80px;
    display: block;
  }
}

// =============================================
// DESTAQUES — Bento Grid
// =============================================
.hb-highlight-section {
  padding: 100px 0 80px;
  background: $hb-cream;
}

.hb-bento-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: auto auto;
  gap: 20px;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
}

// Featured card — occupies full left column both rows
.hb-bento-featured {
  grid-row: span 2;
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: 0 2px 20px rgba(0,0,0,0.06);
  transition: box-shadow 0.35s;

  &:hover {
    box-shadow: 0 12px 40px rgba(0,0,0,0.12);
  }

  @media (max-width: 768px) {
    grid-row: span 1;
  }
}

.hb-bento-featured-img {
  position: relative;
  flex: 1;
  min-height: 340px;
  overflow: hidden;

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.6s ease;
  }

  &:hover img { transform: scale(1.05); }
}

.hb-bento-tag {
  position: absolute;
  top: 20px;
  left: 20px;
  background: $hb-gold;
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  padding: 6px 14px;
  border-radius: 3px;
}

.hb-bento-featured-body {
  padding: 28px;

  h3 {
    font-family: $font-serif;
    font-size: 24px;
    font-weight: 800;
    color: $hb-black;
    margin: 0 0 8px;
  }

  p {
    color: $hb-muted;
    font-size: 14px;
    line-height: 1.6;
    margin: 0 0 20px;
  }
}

.hb-bento-featured-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.hb-price-editorial {
  font-family: $font-sans;
  font-size: 22px;
  font-weight: 700;
  color: $hb-black;
  letter-spacing: -0.5px;
}

.hb-btn-order-sm {
  background: $hb-green-wpp;
  color: #fff;
  padding: 10px 20px;
  border-radius: 4px;
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: background 0.2s, transform 0.2s;

  &:hover {
    background: darken($hb-green-wpp, 8%);
    transform: translateY(-1px);
  }
}

// Regular bento cards
.hb-bento-card {
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  flex-direction: row;
  box-shadow: 0 2px 20px rgba(0,0,0,0.06);
  transition: box-shadow 0.35s, transform 0.35s;

  &:hover {
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    transform: translateY(-3px);
  }
}

.hb-bento-card-img {
  position: relative;
  flex: 0 0 44%;
  overflow: hidden;

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.5s ease;
  }

  &:hover img { transform: scale(1.06); }
}

.hb-bento-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  font-size: 10px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 3px;
  white-space: nowrap;

  &.destaque { background: #fef3c7; color: #92400e; }
  &.novo { background: #d1fae5; color: #065f46; }
}

.hb-bento-card-body {
  padding: 22px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  flex: 1;

  h3 {
    font-family: $font-serif;
    font-size: 18px;
    font-weight: 800;
    color: $hb-black;
    margin: 0 0 6px;
  }

  p {
    font-size: 13px;
    color: $hb-muted;
    line-height: 1.55;
    margin: 0 0 16px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
}

.hb-bento-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.hb-highlight-more {
  text-align: center;
  margin-top: 56px;
}

.hb-btn-editorial {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: transparent;
  color: $hb-black;
  border: 1px solid $hb-black;
  padding: 14px 36px;
  border-radius: 4px;
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
  letter-spacing: 1px;
  text-transform: uppercase;
  transition: background 0.25s, color 0.25s;

  svg {
    transition: transform 0.3s;
  }

  &:hover {
    background: $hb-black;
    color: #fff;
    svg { transform: translateX(4px); }
  }
}

// =============================================
// ABOUT — Overlapping editorial layout
// =============================================
.hb-about-section {
  background: $hb-warm;
  padding: 100px 0;
}

.hb-about-layout {
  display: grid;
  grid-template-columns: 1fr 1.2fr;
  gap: 80px;
  align-items: center;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
    gap: 48px;
  }
}

.hb-about-visual {
  position: relative;
}

.hb-about-img-wrap {
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 24px 64px rgba(0,0,0,0.15);

  img {
    width: 100%;
    height: 380px;
    object-fit: cover;
    display: block;
    transition: transform 0.6s ease;
  }

  &:hover img { transform: scale(1.03); }
}

.hb-about-stats {
  display: flex;
  align-items: center;
  gap: 24px;
  background: #fff;
  padding: 20px 28px;
  border-radius: 6px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
  position: relative;
  margin-top: -32px;
  margin-left: 24px;
  margin-right: 24px;
  z-index: 2;
}

.hb-stat {
  text-align: center;
  flex: 1;
}

.hb-stat-num {
  display: block;
  font-family: $font-serif;
  font-size: 28px;
  font-weight: 900;
  color: $hb-black;
  line-height: 1;
}

.hb-stat-label {
  display: block;
  font-size: 10px;
  font-weight: 700;
  color: $hb-muted;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  margin-top: 4px;
}

.hb-stat-divider {
  width: 1px;
  height: 36px;
  background: $hb-border;
}

.hb-about-text {
  h2 {
    font-family: $font-serif;
    font-size: clamp(28px, 4vw, 42px);
    font-weight: 800;
    color: $hb-black;
    line-height: 1.15;
    margin: 0 0 24px;
  }

  p {
    color: $hb-muted;
    font-size: 15px;
    line-height: 1.8;
    margin-bottom: 16px;
  }
}

.hb-about-pillars {
  margin-top: 32px;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.hb-pillar {
  display: flex;
  align-items: center;
  gap: 14px;

  strong {
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: $hb-black;
  }

  span {
    display: flex;
    font-size: 12px;
    color: $hb-muted;
  }
}

.hb-pillar-icon {
  width: 40px;
  height: 40px;
  border-radius: 6px;
  background: $hb-gold-light;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;

  i {
    color: $hb-gold;
    font-size: 16px;
  }
}

// =============================================
// GALLERY — Editorial Mosaic
// =============================================
.hb-gallery-section {
  padding: 100px 0;
  background: $hb-charcoal;

  .hb-section-header h2 { color: #fff; }
  .hb-heading-light { color: rgba(255,255,255,0.4); }
  .hb-section-eyebrow { color: $hb-gold; }
}

.hb-gallery-mosaic {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  grid-template-rows: 260px 260px;
  gap: 8px;

  @media (max-width: 640px) {
    grid-template-columns: 1fr 1fr;
    grid-template-rows: auto;
  }
}

.hb-mosaic-item {
  position: relative;
  border-radius: 4px;
  overflow: hidden;

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.6s ease;
    filter: brightness(0.85);
  }

  &:hover img {
    transform: scale(1.06);
    filter: brightness(1);
  }

  &.hb-mosaic-tall {
    grid-row: span 2;

    @media (max-width: 640px) { grid-row: span 1; }
  }

  &.hb-mosaic-wide {
    grid-column: span 2;

    @media (max-width: 640px) { grid-column: span 1; }
  }
}

.hb-mosaic-label {
  position: absolute;
  bottom: 16px;
  left: 16px;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #fff;
  background: rgba(0,0,0,0.5);
  backdrop-filter: blur(8px);
  padding: 6px 14px;
  border-radius: 3px;
}

// =============================================
// CONTACT — Clean editorial cards
// =============================================
.hb-contact-section {
  padding: 100px 0;
  background: #fff;
}

.hb-contact-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;

  @media (max-width: 768px) {
    grid-template-columns: repeat(2, 1fr);
  }
  @media (max-width: 480px) {
    grid-template-columns: 1fr;
  }
}

.hb-contact-card {
  background: $hb-cream;
  border-radius: 8px;
  padding: 32px 24px;
  text-align: center;
  border: 1px solid transparent;
  transition: border-color 0.3s, transform 0.3s;

  &:hover {
    border-color: $hb-gold;
    transform: translateY(-4px);
  }

  h4 {
    font-family: $font-serif;
    font-size: 16px;
    font-weight: 800;
    color: $hb-black;
    margin: 0 0 10px;
  }

  p {
    font-size: 14px;
    color: $hb-muted;
    line-height: 1.6;
    margin: 0;
  }

  .hb-wpp-link {
    color: $hb-green-wpp;
    font-weight: 700;
    text-decoration: none;
    &:hover { text-decoration: underline; }
  }
}

.hb-contact-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: $hb-gold-light;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;

  i {
    font-size: 20px;
    color: $hb-gold;
  }
}

// =============================================
// CTA — Bold editorial typography
// =============================================
.hb-cta {
  background: $hb-black;
  padding: 100px 0;
  text-align: center;

  p {
    color: rgba(255,255,255,0.45);
    font-size: 16px;
    margin-bottom: 36px;
  }
}

.hb-cta-title {
  font-family: $font-serif;
  font-size: clamp(48px, 8vw, 96px);
  font-weight: 900;
  color: #fff;
  line-height: 0.95;
  margin: 0 0 16px;
  letter-spacing: -2px;
}

.hb-btn-cta {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: $hb-green-wpp;
  color: #fff;
  padding: 18px 40px;
  border-radius: 4px;
  font-size: 15px;
  font-weight: 700;
  text-decoration: none;
  letter-spacing: 0.5px;
  transition: background 0.25s, transform 0.25s;

  &:hover {
    background: darken($hb-green-wpp, 8%);
    transform: translateY(-2px);
  }
}

// =============================================
// FOOTER — Minimal
// =============================================
.hb-footer {
  background: $hb-charcoal;
  border-top: 1px solid rgba(255,255,255,0.06);
  padding: 32px 0;
}

.hb-footer-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;

  p {
    color: rgba(255,255,255,0.35);
    font-size: 12px;
    margin: 0;
  }
}

.hb-footer-brand {
  font-family: $font-serif;
  font-size: 20px;
  font-weight: 900;
  color: #fff;
}

.hb-footer-credit {
  a {
    color: $hb-gold;
    text-decoration: none;
    font-weight: 700;
    &:hover { text-decoration: underline; }
  }
}

// =============================================
// WHATSAPP FLOAT — Discreet
// =============================================
.hb-wpp-float {
  position: fixed;
  bottom: 72px;
  right: 24px;
  background: $hb-green-wpp;
  color: #fff;
  width: 54px;
  height: 54px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  box-shadow: 0 4px 16px rgba(37, 211, 102, 0.35);
  text-decoration: none;
  z-index: 999;
  transition: transform 0.25s, box-shadow 0.25s;

  &:hover {
    transform: scale(1.08);
    box-shadow: 0 6px 24px rgba(37, 211, 102, 0.5);
  }
}

// =============================================
// UNLI CTA BAR — Polished
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
  background: $hb-charcoal;
  padding: 12px 24px;
  text-decoration: none;
  color: #fff;
  box-shadow: 0 -2px 20px rgba(0,0,0,0.3);
  transition: background 0.2s;

  &:hover { background: lighten($hb-charcoal, 5%); }

  .unli-cta-text {
    font-size: 13px;
    font-weight: 500;
    color: rgba(255,255,255,0.7);
    strong { font-weight: 700; color: #fff; }
  }

  .unli-cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: $hb-gold;
    color: #fff;
    padding: 8px 20px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
    i { font-size: 15px; }
  }

  @media (max-width: 500px) {
    padding: 10px 16px;
    gap: 10px;
    .unli-cta-text { font-size: 12px; }
  }
}
</style>
