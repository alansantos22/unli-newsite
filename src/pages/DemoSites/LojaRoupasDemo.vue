<template>
  <div class="demo-site loja-demo">
    <!-- Demo Banner -->
    <div class="demo-banner">
      <i class="fas fa-eye"></i>
      Este é um modelo de demonstração — 
      <a href="/site-vitrine" class="demo-banner-link">quero um site assim para minha loja</a>
      <span class="demo-badge">MODELO</span>
    </div>

    <!-- Header -->
    <header class="lj-header">
      <div class="lj-header-inner">
        <div class="lj-logo">
          <span class="lj-logo-icon">👗</span>
          <div>
            <span class="lj-logo-name">Estilo Carioca</span>
            <span class="lj-logo-tagline">Moda feminina e masculina</span>
          </div>
        </div>
        <nav class="lj-nav">
          <a href="#produtos">Produtos</a>
          <a href="#sobre">Sobre</a>
          <a href="#contato">Contato</a>
        </nav>
        <a :href="whatsappLink('Olá! Vi a vitrine online e gostaria de mais informações!')" target="_blank" class="lj-header-btn">
          <i class="fab fa-whatsapp"></i> Fale Conosco
        </a>
      </div>
    </header>

    <!-- Hero Banner -->
    <section class="lj-hero">
      <div class="lj-hero-content">
        <span class="lj-badge">Nova Coleção 2025</span>
        <h1 class="lj-hero-title">Vista-se com<br><span>estilo único</span></h1>
        <p class="lj-hero-sub">Peças selecionadas para você que não abre mão de qualidade e bom gosto. A moda carioca que todo Brasil quer usar.</p>
        <div class="lj-hero-actions">
          <a href="#produtos" class="lj-btn-primary">
            <i class="fas fa-tshirt"></i> Ver Coleção Completa
          </a>
          <a :href="whatsappLink('Oi! Quero ver as novidades da loja!')" target="_blank" class="lj-btn-secondary">
            <i class="fab fa-whatsapp"></i> Consultar via WhatsApp
          </a>
        </div>
        <div class="lj-hero-tags">
          <span>✅ Entrega para todo Brasil</span>
          <span>✅ Parcelamento em até 6x</span>
          <span>✅ Troca fácil em 30 dias</span>
        </div>
      </div>
      <div class="lj-hero-visual">
        <div class="lj-hero-cards">
          <div class="lj-mini-card" v-for="item in heroShowcase" :key="item.name">
            <span class="lj-mini-emoji">{{ item.emoji }}</span>
            <span class="lj-mini-name">{{ item.name }}</span>
            <span class="lj-mini-price">R$ {{ item.price }}</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Categorias -->
    <section class="lj-categories-bar">
      <div class="lj-container">
        <div class="lj-cat-list">
          <button
            v-for="cat in categories"
            :key="cat.id"
            class="lj-cat-chip"
            :class="{ active: activeCategory === cat.id }"
            @click="activeCategory = cat.id"
          >
            {{ cat.emoji }} {{ cat.name }}
            <span class="lj-cat-count">{{ cat.count }}</span>
          </button>
        </div>
      </div>
    </section>

    <!-- Produtos -->
    <section id="produtos" class="lj-products-section">
      <div class="lj-container">
        <div class="lj-section-header">
          <span class="lj-section-badge">Vitrine</span>
          <h2>{{ currentCategoryName }}</h2>
          <p>Peças com qualidade e preço justo — direto para você</p>
        </div>

        <div class="lj-product-grid">
          <div
            v-for="product in filteredProducts"
            :key="product.id"
            class="lj-product-card"
          >
            <div class="lj-product-img" :style="{ background: product.bg }">
              <span class="lj-product-emoji">{{ product.emoji }}</span>
              <span v-if="product.tag" class="lj-product-tag" :class="product.tag.type">{{ product.tag.text }}</span>
            </div>
            <div class="lj-product-info">
              <span class="lj-product-category">{{ product.category }}</span>
              <h3 class="lj-product-name">{{ product.name }}</h3>
              <p class="lj-product-desc">{{ product.description }}</p>
              <div class="lj-product-sizes">
                <span v-for="size in product.sizes" :key="size" class="lj-size">{{ size }}</span>
              </div>
              <div class="lj-product-footer">
                <div class="lj-price-block">
                  <span v-if="product.oldPrice" class="lj-old-price">R$ {{ product.oldPrice }}</span>
                  <span class="lj-price">R$ {{ product.price }}</span>
                  <span class="lj-installment">em 6x s/ juros</span>
                </div>
                <a
                  :href="whatsappLink(`Olá! Tenho interesse na peça *${product.name}* (${product.description}). Preço: R$ ${product.price}. Vocês têm disponível?`)"
                  target="_blank"
                  class="lj-buy-btn"
                >
                  <i class="fab fa-whatsapp"></i> Comprar
                </a>
              </div>
            </div>
          </div>
        </div>

        <div class="lj-load-more">
          <a :href="whatsappLink('Olá! Quero ver mais peças da loja. O que vocês têm disponível?')" target="_blank" class="lj-btn-outline">
            <i class="fab fa-whatsapp"></i> Ver mais peças pelo WhatsApp
          </a>
        </div>
      </div>
    </section>

    <!-- Banner Promoção -->
    <section class="lj-promo-banner">
      <div class="lj-container">
        <div class="lj-promo-content">
          <span class="lj-promo-tag">🔥 Oferta Especial</span>
          <h2>Até 40% OFF na coleção de inverno</h2>
          <p>Aproveite enquanto durar o estoque. Peças selecionadas com desconto imperdível.</p>
          <a :href="whatsappLink('Oi! Quero aproveitar a promoção de até 40% OFF!')" target="_blank" class="lj-btn-white">
            <i class="fab fa-whatsapp"></i> Aproveitar Desconto
          </a>
        </div>
      </div>
    </section>

    <!-- Sobre -->
    <section id="sobre" class="lj-about-section">
      <div class="lj-container lj-about-grid">
        <div class="lj-about-text">
          <span class="lj-section-badge">Quem Somos</span>
          <h2>Tradição em moda desde 2015</h2>
          <p>
            A Estilo Carioca nasceu com uma missão simples: levar moda de qualidade com preço acessível para todo o Brasil. 
            Começamos como brechó no Bairro de Botafogo e crescemos para uma loja completa com peças selecionadas de fornecedores nacionais.
          </p>
          <p>
            Acreditamos que todo mundo merece se vestir bem — sem precisar pagar uma fortuna. 
            Nossas coleções são renovadas mensalmente, sempre acompanhando as tendências atuais.
          </p>
          <div class="lj-about-features">
            <div class="lj-feature"><i class="fas fa-check"></i> Colecionadores renovadas mensalmente</div>
            <div class="lj-feature"><i class="fas fa-check"></i> Atendimento personalizado</div>
            <div class="lj-feature"><i class="fas fa-check"></i> Entrega para todo o Brasil</div>
            <div class="lj-feature"><i class="fas fa-check"></i> Troca facilitada em 30 dias</div>
          </div>
        </div>
        <div class="lj-about-stats">
          <div class="lj-about-stat">
            <span class="lj-stat-num">10+</span>
            <span class="lj-stat-label">Anos de moda</span>
          </div>
          <div class="lj-about-stat">
            <span class="lj-stat-num">5k+</span>
            <span class="lj-stat-label">Clientes felizes</span>
          </div>
          <div class="lj-about-stat">
            <span class="lj-stat-num">200+</span>
            <span class="lj-stat-label">Peças no estoque</span>
          </div>
          <div class="lj-about-stat">
            <span class="lj-stat-num">4.8⭐</span>
            <span class="lj-stat-label">Avaliação Google</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Contato -->
    <section id="contato" class="lj-contact-section">
      <div class="lj-container">
        <div class="lj-section-header">
          <span class="lj-section-badge">Contato</span>
          <h2>Fale com a gente</h2>
          <p>Atendemos pelo WhatsApp, Instagram e na loja física</p>
        </div>
        <div class="lj-contact-grid">
          <div class="lj-contact-item">
            <i class="fas fa-map-marker-alt"></i>
            <h4>Loja Física</h4>
            <p>Av. Nossa Senhora de Copacabana, 512<br>Rio de Janeiro/RJ — CEP 22020-001</p>
          </div>
          <div class="lj-contact-item">
            <i class="fas fa-clock"></i>
            <h4>Horário</h4>
            <p>Seg–Sex: 10h às 19h<br>Sábado: 10h às 17h</p>
          </div>
          <div class="lj-contact-item">
            <i class="fab fa-whatsapp"></i>
            <h4>WhatsApp</h4>
            <p>(21) 9 9999-0000<br>
              <a :href="whatsappLink('Olá! Quero mais informações sobre a loja')" target="_blank" class="lj-contact-link">Clique para conversar</a>
            </p>
          </div>
          <div class="lj-contact-item">
            <i class="fab fa-instagram"></i>
            <h4>Instagram</h4>
            <p>@estilocarioca<br>14k seguidores</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="lj-footer">
      <div class="lj-footer-inner">
        <span>© 2025 Estilo Carioca • Todos os direitos reservados</span>
        <span class="lj-footer-credit">Site criado pela <a href="/site-vitrine" target="_blank">Unli</a></span>
      </div>
    </footer>

    <!-- WhatsApp Float -->
    <a :href="whatsappLink('Olá! Quero mais informações sobre a loja!')" target="_blank" class="lj-wpp-float" aria-label="Contato WhatsApp">
      <i class="fab fa-whatsapp"></i>
    </a>

    <!-- CTA Unli - Quero esse site! -->
    <a :href="`https://wa.me/${unliWhatsapp}?text=${encodeURIComponent('Olá! Vi o modelo de site para loja de roupas e quero um assim para a minha empresa!')}`"
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
  name: 'LojaRoupasDemo',
  data() {
    return {
      activeCategory: 'feminino',
      whatsappNumber: '5521999990000',
      unliWhatsapp: process.env.VUE_APP_WHATSAPP_SDR || '5511911019666',
      heroShowcase: [
        { emoji: '👗', name: 'Vestido Linho', price: '189,90' },
        { emoji: '👖', name: 'Calça Jeans', price: '149,90' },
        { emoji: '👠', name: 'Sandália', price: '99,90' },
      ],
      categories: [
        { id: 'feminino', name: 'Feminino', emoji: '👗', count: 8 },
        { id: 'masculino', name: 'Masculino', emoji: '👔', count: 6 },
        { id: 'acessorios', name: 'Acessórios', emoji: '👜', count: 5 },
        { id: 'calcados', name: 'Calçados', emoji: '👠', count: 5 },
      ],
      products: [
        {
          id: 1, category: 'feminino', emoji: '👗',
          bg: 'linear-gradient(135deg, #fce4ec, #f8bbd0)',
          name: 'Vestido Linho Florence',
          description: 'Linho premium, corte midi, decote V elegante',
          sizes: ['PP', 'P', 'M', 'G'],
          price: '189,90', oldPrice: '249,90',
          tag: { type: 'sale', text: '24% OFF' }
        },
        {
          id: 2, category: 'feminino', emoji: '👚',
          bg: 'linear-gradient(135deg, #e8f5e9, #c8e6c9)',
          name: 'Blusa Cropped Canelada',
          description: 'Tecido canelado, alça dupla, diversas cores',
          sizes: ['P', 'M', 'G', 'GG'],
          price: '79,90', oldPrice: null,
          tag: { type: 'new', text: 'Novo' }
        },
        {
          id: 3, category: 'feminino', emoji: '👖',
          bg: 'linear-gradient(135deg, #e3f2fd, #bbdefb)',
          name: 'Calça Wide Leg Jeans',
          description: 'Jeans premium, cintura alta, perna larga',
          sizes: ['36', '38', '40', '42', '44'],
          price: '169,90', oldPrice: '219,90',
          tag: { type: 'sale', text: '22% OFF' }
        },
        {
          id: 4, category: 'feminino', emoji: '🧥',
          bg: 'linear-gradient(135deg, #fafafa, #eeeeee)',
          name: 'Blazer Over Alfaiataria',
          description: 'Alfaiataria italiana, corte oversized clássico',
          sizes: ['P', 'M', 'G'],
          price: '289,90', oldPrice: null,
          tag: { type: 'hot', text: '🔥 Tendência' }
        },
        {
          id: 5, category: 'masculino', emoji: '👔',
          bg: 'linear-gradient(135deg, #e8eaf6, #c5cae9)',
          name: 'Camisa Social Slim',
          description: 'Algodão premium, corte slim, 5 cores',
          sizes: ['P', 'M', 'G', 'GG'],
          price: '139,90', oldPrice: '179,90',
          tag: { type: 'sale', text: '22% OFF' }
        },
        {
          id: 6, category: 'masculino', emoji: '👕',
          bg: 'linear-gradient(135deg, #fff3e0, #ffe0b2)',
          name: 'Camiseta Básica Premium',
          description: 'Algodão 100% puro fio 40, caimento perfeito',
          sizes: ['P', 'M', 'G', 'GG', 'XGG'],
          price: '59,90', oldPrice: null,
          tag: { type: 'new', text: 'Novo' }
        },
        {
          id: 7, category: 'masculino', emoji: '🩳',
          bg: 'linear-gradient(135deg, #e0f2f1, #b2dfdb)',
          name: 'Bermuda Sarja Chino',
          description: 'Sarja premium, corte reto, bolsos funcionais',
          sizes: ['38', '40', '42', '44', '46'],
          price: '119,90', oldPrice: '149,90',
          tag: { type: 'sale', text: '20% OFF' }
        },
        {
          id: 8, category: 'acessorios', emoji: '👜',
          bg: 'linear-gradient(135deg, #fce4ec, #f8bbd0)',
          name: 'Bolsa Tiracolo Couro',
          description: 'Couro sintético premium, zíper duplo, alça regulável',
          sizes: ['Único'],
          price: '149,90', oldPrice: '199,90',
          tag: { type: 'sale', text: '25% OFF' }
        },
        {
          id: 9, category: 'acessorios', emoji: '🕶️',
          bg: 'linear-gradient(135deg, #f3e5f5, #e1bee7)',
          name: 'Óculos Cat Eye Retrô',
          description: 'Armação acetato, lente UV400, vários tons',
          sizes: ['Único'],
          price: '89,90', oldPrice: null,
          tag: { type: 'hot', text: '🔥 Hit' }
        },
        {
          id: 10, category: 'calcados', emoji: '👠',
          bg: 'linear-gradient(135deg, #fce4ec, #ffccbc)',
          name: 'Scarpin Salto Bloco',
          description: 'Couro ecológico, salto 7cm, forro macio',
          sizes: ['34', '35', '36', '37', '38', '39'],
          price: '199,90', oldPrice: '259,90',
          tag: { type: 'sale', text: '23% OFF' }
        },
        {
          id: 11, category: 'calcados', emoji: '👟',
          bg: 'linear-gradient(135deg, #e8f5e9, #dcedc8)',
          name: 'Tênis Fashion Chunky',
          description: 'Solado tratorado, cabedal tecido premium, unissex',
          sizes: ['35', '36', '37', '38', '39', '40'],
          price: '249,90', oldPrice: '319,90',
          tag: { type: 'hot', text: '🔥 Tendência' }
        },
      ]
    };
  },
  computed: {
    filteredProducts() {
      return this.products.filter(p => p.category === this.activeCategory);
    },
    currentCategoryName() {
      const cat = this.categories.find(c => c.id === this.activeCategory);
      return cat ? `${cat.emoji} ${cat.name}` : 'Produtos';
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
  background: linear-gradient(135deg, #2d1b69 0%, #1a0533 100%);
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

  i { color: #f472b6; }

  .demo-banner-link {
    color: #f472b6;
    font-weight: 700;
    text-decoration: underline;
    &:hover { color: #fb7bc8; }
  }

  .demo-badge {
    background: #f472b6;
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 20px;
    letter-spacing: 1px;
  }
}

// =============================================
// LOJA THEME VARIABLES
// =============================================
$lj-pink: #e91e8c;
$lj-dark-pink: #c2185b;
$lj-light: #fdf2f8;
$lj-text: #1a0533;
$lj-muted: #6b2d6b;
$lj-white: #ffffff;

.loja-demo {
  font-family: 'Segoe UI', sans-serif;
  background: $lj-light;
  color: $lj-text;
}

.lj-container {
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 20px;
}

.lj-section-badge {
  display: inline-block;
  background: rgba($lj-pink, 0.12);
  color: $lj-pink;
  font-size: 12px;
  font-weight: 700;
  padding: 4px 14px;
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 10px;
}

.lj-section-header {
  text-align: center;
  margin-bottom: 40px;

  h2 { font-size: clamp(26px, 4vw, 38px); font-weight: 800; margin: 8px 0; }
  p { color: $lj-muted; font-size: 16px; }
}

// =============================================
// HEADER
// =============================================
.lj-header {
  background: $lj-white;
  box-shadow: 0 2px 16px rgba(233, 30, 140, 0.08);
  position: sticky;
  top: 45px;
  z-index: 999;
}

.lj-header-inner {
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 20px;
  display: flex;
  align-items: center;
  height: 64px;
  gap: 20px;
  justify-content: space-between;
}

.lj-logo {
  display: flex;
  align-items: center;
  gap: 10px;

  .lj-logo-icon { font-size: 26px; }
  .lj-logo-name { display: block; font-size: 18px; font-weight: 800; color: $lj-text; }
  .lj-logo-tagline { display: block; font-size: 11px; color: $lj-pink; }
}

.lj-nav {
  display: flex;
  gap: 28px;

  a {
    color: $lj-muted;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    &:hover { color: $lj-pink; }
  }

  @media (max-width: 640px) { display: none; }
}

.lj-header-btn {
  background: #25d366;
  color: #fff;
  padding: 10px 18px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 700;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 6px;
  &:hover { background: #1ebe5d; }
}

// =============================================
// HERO
// =============================================
.lj-hero {
  background: linear-gradient(135deg, $lj-text 0%, #4a0e5c 60%, $lj-dark-pink 100%);
  min-height: 520px;
  display: flex;
  align-items: center;
  padding: 60px 20px;
  gap: 40px;
  overflow: hidden;
}

.lj-hero-content {
  flex: 1;
  max-width: 560px;
  margin: 0 auto;
}

.lj-badge {
  display: inline-block;
  background: rgba($lj-pink, 0.25);
  border: 1px solid rgba($lj-pink, 0.5);
  color: #f9a8d4;
  font-size: 13px;
  font-weight: 700;
  padding: 6px 16px;
  border-radius: 20px;
  margin-bottom: 18px;
}

.lj-hero-title {
  font-size: clamp(34px, 5vw, 58px);
  font-weight: 900;
  color: #fff;
  line-height: 1.1;
  margin-bottom: 14px;

  span { color: #f9a8d4; display: block; }
}

.lj-hero-sub {
  color: rgba(255,255,255,0.75);
  font-size: 16px;
  line-height: 1.65;
  margin-bottom: 28px;
}

.lj-hero-actions {
  display: flex;
  gap: 14px;
  flex-wrap: wrap;
  margin-bottom: 28px;
}

.lj-btn-primary {
  background: $lj-pink;
  color: #fff;
  padding: 13px 26px;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 700;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: background 0.2s, transform 0.2s;
  &:hover { background: $lj-dark-pink; transform: translateY(-2px); }
}

.lj-btn-secondary {
  background: #25d366;
  color: #fff;
  padding: 13px 26px;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 700;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: background 0.2s, transform 0.2s;
  &:hover { background: #1ebe5d; transform: translateY(-2px); }
}

.lj-btn-outline {
  border: 2px solid #25d366;
  color: #25d366;
  background: transparent;
  padding: 12px 26px;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 700;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
  &:hover { background: #25d366; color: #fff; }
}

.lj-hero-tags {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
  span {
    color: rgba(255,255,255,0.7);
    font-size: 13px;
  }
}

.lj-hero-visual {
  flex: 0 0 auto;
  @media (max-width: 768px) { display: none; }
}

.lj-hero-cards {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.lj-mini-card {
  background: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.15);
  backdrop-filter: blur(10px);
  border-radius: 14px;
  padding: 14px 20px;
  display: flex;
  align-items: center;
  gap: 12px;
  width: 240px;

  .lj-mini-emoji { font-size: 32px; }
  .lj-mini-name { flex: 1; font-size: 14px; font-weight: 600; color: #fff; }
  .lj-mini-price { font-size: 15px; font-weight: 900; color: #f9a8d4; }
}

// =============================================
// CATEGORIES BAR
// =============================================
.lj-categories-bar {
  background: $lj-white;
  border-bottom: 1px solid rgba($lj-pink, 0.1);
  padding: 16px 0;
  position: sticky;
  top: 109px;
  z-index: 100;
}

.lj-cat-list {
  display: flex;
  gap: 12px;
  overflow-x: auto;
  padding-bottom: 4px;

  &::-webkit-scrollbar { height: 4px; }
  &::-webkit-scrollbar-thumb { background: rgba($lj-pink, 0.3); border-radius: 4px; }
}

.lj-cat-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: transparent;
  border: 2px solid rgba($lj-pink, 0.25);
  color: $lj-muted;
  padding: 8px 18px;
  border-radius: 25px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s;

  .lj-cat-count {
    background: rgba($lj-pink, 0.15);
    color: $lj-pink;
    font-size: 11px;
    font-weight: 700;
    padding: 1px 7px;
    border-radius: 10px;
  }

  &.active, &:hover {
    background: $lj-pink;
    border-color: $lj-pink;
    color: #fff;

    .lj-cat-count { background: rgba(255,255,255,0.25); color: #fff; }
  }
}

// =============================================
// PRODUCTS
// =============================================
.lj-products-section {
  padding: 60px 0 80px;
}

.lj-product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 24px;
}

.lj-product-card {
  background: $lj-white;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  transition: transform 0.2s, box-shadow 0.2s;

  &:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 36px rgba($lj-pink, 0.12);
  }
}

.lj-product-img {
  height: 180px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;

  .lj-product-emoji { font-size: 72px; }

  .lj-product-tag {
    position: absolute;
    top: 12px;
    left: 12px;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;

    &.sale { background: #fee2e2; color: #991b1b; }
    &.new { background: #d1fae5; color: #065f46; }
    &.hot { background: #fff3cd; color: #92400e; }
  }
}

.lj-product-info {
  padding: 16px;
}

.lj-product-category {
  font-size: 11px;
  font-weight: 700;
  color: $lj-pink;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.lj-product-name {
  font-size: 16px;
  font-weight: 800;
  color: $lj-text;
  margin: 4px 0 6px;
}

.lj-product-desc {
  font-size: 13px;
  color: $lj-muted;
  margin-bottom: 10px;
}

.lj-product-sizes {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
  margin-bottom: 14px;
}

.lj-size {
  border: 1px solid rgba($lj-muted, 0.3);
  color: $lj-muted;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 6px;
}

.lj-product-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.lj-price-block {
  display: flex;
  flex-direction: column;
}

.lj-old-price {
  font-size: 12px;
  color: $lj-muted;
  text-decoration: line-through;
}

.lj-price {
  font-size: 20px;
  font-weight: 900;
  color: $lj-text;
}

.lj-installment {
  font-size: 11px;
  color: $lj-muted;
}

.lj-buy-btn {
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

.lj-load-more {
  text-align: center;
  margin-top: 40px;
}

// =============================================
// PROMO BANNER
// =============================================
.lj-promo-banner {
  background: linear-gradient(135deg, $lj-dark-pink, #8b1a6b);
  padding: 60px 0;
}

.lj-promo-content {
  text-align: center;
  max-width: 600px;
  margin: 0 auto;

  .lj-promo-tag {
    display: inline-block;
    background: rgba(255,255,255,0.2);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    padding: 6px 16px;
    border-radius: 20px;
    margin-bottom: 16px;
  }

  h2 { font-size: clamp(24px, 4vw, 38px); font-weight: 900; color: #fff; margin-bottom: 12px; }
  p { color: rgba(255,255,255,0.8); font-size: 16px; margin-bottom: 28px; }
}

.lj-btn-white {
  background: #fff;
  color: $lj-dark-pink;
  padding: 14px 32px;
  border-radius: 10px;
  font-size: 16px;
  font-weight: 800;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: transform 0.2s;
  &:hover { transform: translateY(-2px); }
}

// =============================================
// ABOUT
// =============================================
.lj-about-section {
  background: $lj-white;
  padding: 80px 0;
}

.lj-about-grid {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: 60px;
  align-items: center;

  @media (max-width: 768px) { grid-template-columns: 1fr; gap: 40px; }
}

.lj-about-text {
  h2 { font-size: clamp(24px, 3vw, 34px); font-weight: 800; margin: 8px 0 18px; }
  p { color: $lj-muted; font-size: 15px; line-height: 1.7; margin-bottom: 14px; }
}

.lj-about-features {
  margin-top: 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.lj-feature {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
  color: $lj-text;
  i { color: $lj-pink; font-size: 15px; }
}

.lj-about-stats {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.lj-about-stat {
  background: $lj-light;
  border-radius: 16px;
  padding: 24px;
  text-align: center;
  border: 1px solid rgba($lj-pink, 0.1);

  .lj-stat-num { display: block; font-size: 32px; font-weight: 900; color: $lj-pink; }
  .lj-stat-label { display: block; font-size: 13px; color: $lj-muted; margin-top: 4px; }
}

// =============================================
// CONTACT
// =============================================
.lj-contact-section {
  background: $lj-light;
  padding: 80px 0;
}

.lj-contact-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
}

.lj-contact-item {
  background: $lj-white;
  border-radius: 16px;
  padding: 28px 20px;
  text-align: center;
  border: 2px solid rgba($lj-pink, 0.08);
  transition: border-color 0.2s;
  &:hover { border-color: rgba($lj-pink, 0.3); }

  i { font-size: 30px; color: $lj-pink; display: block; margin-bottom: 12px; }
  h4 { font-size: 15px; font-weight: 700; color: $lj-text; margin-bottom: 6px; }
  p { font-size: 14px; color: $lj-muted; line-height: 1.6; margin: 0; }

  .lj-contact-link { color: #25d366; font-weight: 700; text-decoration: none; }
}

// =============================================
// FOOTER
// =============================================
.lj-footer {
  background: $lj-text;
  padding: 20px;
}

.lj-footer-inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
  max-width: 1100px;
  margin: 0 auto;
  font-size: 13px;
  color: rgba(255,255,255,0.5);

  .lj-footer-credit a {
    color: #f9a8d4;
    font-weight: 700;
    text-decoration: none;
    &:hover { text-decoration: underline; }
  }
}

// =============================================
// WHATSAPP FLOAT
// =============================================
.lj-wpp-float {
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
