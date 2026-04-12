<template>
  <div class="hbc-page">

    <!-- Sticky Header + Category Tabs -->
    <header class="hbc-header">
      <div class="hbc-header-inner">
        <router-link to="/demo/hamburgueria" class="hbc-back" aria-label="Voltar">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        </router-link>

        <span class="hbc-logo">House<span class="hbc-logo-accent">.</span></span>

        <div class="hbc-header-right">
          <span class="hbc-header-label">Cardápio</span>
          <a :href="whatsappLink('Olá! Quero fazer um pedido!')" target="_blank" class="hbc-header-wpp" aria-label="Pedir pelo WhatsApp">
            <i class="fab fa-whatsapp"></i>
          </a>
        </div>
      </div>

      <!-- Quick filters bar -->
      <div class="hbc-quick-filters">
        <div class="hbc-quick-scroll">
          <button
            class="hbc-quick-chip"
            :class="{ active: activeQuick === null }"
            @click="activeQuick = null"
          >Todos</button>
          <button
            class="hbc-quick-chip hbc-quick-chip--hot"
            :class="{ active: activeQuick === 'destaque' }"
            @click="toggleQuick('destaque')"
          ><i class="fas fa-star"></i> Mais Pedidos</button>
          <button
            class="hbc-quick-chip hbc-quick-chip--spicy"
            :class="{ active: activeQuick === 'picante' }"
            @click="toggleQuick('picante')"
          ><i class="fas fa-pepper-hot"></i> Picantes</button>
          <button
            class="hbc-quick-chip hbc-quick-chip--new"
            :class="{ active: activeQuick === 'novo' }"
            @click="toggleQuick('novo')"
          ><i class="fas fa-seedling"></i> Novidades</button>
        </div>
      </div>

      <!-- Category Tabs -->
      <nav class="hbc-cat-nav">
        <div class="hbc-cat-scroll">
          <button
            v-for="cat in categories"
            :key="cat.id"
            class="hbc-cat-tab"
            :class="{ active: activeCategory === cat.id }"
            @click="selectCategory(cat.id)"
          >
            <i :class="cat.icon"></i>
            <span>{{ cat.name }}</span>
            <span class="hbc-cat-count">{{ countByCategory(cat.id) }}</span>
          </button>
        </div>
      </nav>
    </header>

    <!-- Items -->
    <main class="hbc-main">
      <div class="hbc-section-label">
        <span class="hbc-eyebrow">{{ activeCategoryLabel }}</span>
        <h2 class="hbc-section-title">{{ sectionTitle }}</h2>
      </div>

      <transition-group name="hbc-fade" tag="div" class="hbc-items">
        <article
          v-for="item in displayedItems"
          :key="item.id"
          class="hbc-item-card"
          :class="{ 'hbc-item-card--featured': item.badge && item.badge.type === 'destaque' }"
        >
          <div class="hbc-item-thumb">
            <img :src="item.image" :alt="item.name" loading="lazy" />
            <span v-if="item.badge" class="hbc-badge" :class="item.badge.type">{{ item.badge.text }}</span>
          </div>
          <div class="hbc-item-body">
            <h3>{{ item.name }}</h3>
            <p>{{ item.description }}</p>
            <div class="hbc-item-footer">
              <span class="hbc-price">R$&nbsp;{{ item.price }}</span>
              <a
                :href="whatsappLink(`Olá! Quero pedir: *${item.name}* - R$ ${item.price}`)"
                target="_blank"
                class="hbc-order-btn"
              >
                <i class="fab fa-whatsapp"></i> Pedir
              </a>
            </div>
          </div>
        </article>
      </transition-group>

      <div v-if="displayedItems.length === 0" class="hbc-empty">
        <i class="fas fa-utensils"></i>
        <p>Nenhum item encontrado.</p>
        <button @click="activeQuick = null">Ver todos</button>
      </div>
    </main>

    <!-- Floating order button -->
    <a
      :href="whatsappLink('Olá! Estou vendo o cardápio e quero fazer um pedido!')"
      target="_blank"
      class="hbc-float-order"
      aria-label="Fazer pedido pelo WhatsApp"
    >
      <i class="fab fa-whatsapp"></i>
      <span>Fazer Pedido</span>
    </a>

    <!-- CTA Unli -->
    <a
      :href="`https://wa.me/${unliWhatsapp}?text=${encodeURIComponent('Olá! Vi o modelo de site para hamburgueria e quero um assim para a minha empresa!')}`"
      target="_blank"
      rel="noopener noreferrer"
      class="unli-cta-bar"
    >
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
  name: 'HamburgueriaCardapio',
  data() {
    return {
      activeCategory: 'burgers',
      activeQuick: null,
      whatsappNumber: '5511999990000',
      unliWhatsapp: process.env.VUE_APP_WHATSAPP_SDR || '5511911019666',
      categories: [
        { id: 'burgers', name: 'Burgers', icon: 'fas fa-burger' },
        { id: 'combos', name: 'Combos', icon: 'fas fa-utensils' },
        { id: 'bebidas', name: 'Bebidas', icon: 'fas fa-beer-mug-empty' },
        { id: 'sobremesas', name: 'Sobremesas', icon: 'fas fa-ice-cream' },
      ],
      sectionTitles: {
        burgers: 'Nossos Burgers',
        combos: 'Combos Especiais',
        bebidas: 'Para Beber',
        sobremesas: 'Para Finalizar',
      },
      menuItems: [
        {
          id: 1, category: 'burgers',
          image: 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&h=260&fit=crop&auto=format&q=80',
          name: 'House Smash Burger',
          description: 'Blend 180g, queijo cheddar, bacon crocante, alface americana, tomate e molho especial da casa',
          price: '38,90',
          badge: { type: 'destaque', text: '⭐ Mais Pedido' }
        },
        {
          id: 2, category: 'burgers',
          image: 'https://images.unsplash.com/photo-1553979459-d2229ba7433b?w=400&h=260&fit=crop&auto=format&q=80',
          name: 'Double Cheese',
          description: 'Dois blends 120g, duplo cheddar derretido, picles crocante, cebola caramelizada e mostarda',
          price: '42,90',
          badge: null
        },
        {
          id: 3, category: 'burgers',
          image: 'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?w=400&h=260&fit=crop&auto=format&q=80',
          name: 'Vulcão Burger',
          description: 'Blend 180g, jalapeño, queijo pepper jack, maionese de alho, cebola crispy e molho picante',
          price: '44,90',
          badge: { type: 'picante', text: '🌶️ Picante' }
        },
        {
          id: 4, category: 'burgers',
          image: 'https://images.unsplash.com/photo-1520072959219-c595dc870360?w=400&h=260&fit=crop&auto=format&q=80',
          name: 'Green Smash',
          description: 'Blend 180g, guacamole fresco, queijo brie, rúcula, tomate seco e maionese de limão siciliano',
          price: '46,90',
          badge: { type: 'novo', text: '✨ Novo' }
        },
        {
          id: 5, category: 'combos',
          image: 'https://images.unsplash.com/photo-1561758033-d89a9ad46330?w=400&h=260&fit=crop&auto=format&q=80',
          name: 'Combo House Smash',
          description: 'House Smash Burger + batata frita artesanal + refrigerante 350ml',
          price: '52,90',
          badge: { type: 'destaque', text: '💰 Econômico' }
        },
        {
          id: 6, category: 'combos',
          image: 'https://images.unsplash.com/photo-1476224203421-9ac39bcb3327?w=400&h=260&fit=crop&auto=format&q=80',
          name: 'Combo Double Cheese',
          description: 'Double Cheese + onion rings + refrigerante 350ml',
          price: '58,90',
          badge: null
        },
        {
          id: 7, category: 'bebidas',
          image: 'https://images.unsplash.com/photo-1554866585-cd94860890b7?w=400&h=260&fit=crop&auto=format&q=80',
          name: 'Refrigerante Lata',
          description: 'Coca-Cola, Guaraná ou Sprite — 350ml gelado',
          price: '7,90',
          badge: null
        },
        {
          id: 8, category: 'bebidas',
          image: 'https://images.unsplash.com/photo-1535958636474-b021ee887b13?w=400&h=260&fit=crop&auto=format&q=80',
          name: 'Cerveja Artesanal',
          description: 'IPA, Witbier ou Pale Ale — garrafa 355ml',
          price: '16,90',
          badge: null
        },
        {
          id: 9, category: 'bebidas',
          image: 'https://images.unsplash.com/photo-1613478223719-2ab802602423?w=400&h=260&fit=crop&auto=format&q=80',
          name: 'Suco Natural',
          description: 'Laranja, limão, maracujá ou abacaxi — 400ml',
          price: '12,90',
          badge: null
        },
        {
          id: 10, category: 'sobremesas',
          image: 'https://images.unsplash.com/photo-1579954115545-a95591f28bfc?w=400&h=260&fit=crop&auto=format&q=80',
          name: 'Milkshake Artesanal',
          description: 'Chocolate belga, morango ou baunilha — 400ml cremoso',
          price: '22,90',
          badge: { type: 'destaque', text: '❤️ Favorito' }
        },
        {
          id: 11, category: 'sobremesas',
          image: 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=400&h=260&fit=crop&auto=format&q=80',
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
    displayedItems() {
      const base = this.filteredItems;
      if (!this.activeQuick) return base;
      return base.filter(i => i.badge && i.badge.type === this.activeQuick);
    },
    activeCategoryLabel() {
      const cat = this.categories.find(c => c.id === this.activeCategory);
      return cat ? cat.name : '';
    },
    sectionTitle() {
      return this.sectionTitles[this.activeCategory] || '';
    }
  },
  methods: {
    selectCategory(id) {
      this.activeCategory = id;
      this.activeQuick = null;
      this.$nextTick(() => {
        const main = this.$el.querySelector('.hbc-main');
        if (main) main.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    },
    toggleQuick(type) {
      this.activeQuick = this.activeQuick === type ? null : type;
    },
    countByCategory(id) {
      return this.menuItems.filter(i => i.category === id).length;
    },
    whatsappLink(message) {
      return `https://wa.me/${this.whatsappNumber}?text=${encodeURIComponent(message)}`;
    }
  }
};
</script>

<style lang="scss" scoped>
// =============================================
// CARDÁPIO — EDITORIAL SYSTEM
// Matches HamburgueriaDemo.vue design language
// =============================================
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,700;1,9..40,400&family=Playfair+Display:ital,wght@0,700;0,800;0,900;1,700;1,800&display=swap');

$hb-black:    #111111;
$hb-charcoal: #1c1c1c;
$hb-cream:    #faf6f1;
$hb-warm:     #f5efe8;
$hb-gold:     #c8963e;
$hb-gold-bg:  rgba(200, 150, 62, 0.1);
$hb-green:    #25d366;
$hb-text:     #2a2a2a;
$hb-muted:    #7a7168;
$hb-border:   rgba(0, 0, 0, 0.08);

$font-serif: 'Playfair Display', Georgia, serif;
$font-sans:  'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;

// =============================================
// PAGE
// =============================================
.hbc-page {
  font-family: $font-sans;
  background: $hb-cream;
  color: $hb-text;
  min-height: 100vh;
  padding-bottom: 120px;
  -webkit-font-smoothing: antialiased;
}

// =============================================
// STICKY HEADER
// =============================================
.hbc-header {
  position: sticky;
  top: 0;
  z-index: 100;
  background: $hb-charcoal;
}

.hbc-header-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  height: 58px;
  max-width: 860px;
  margin: 0 auto;
  gap: 16px;
}

// Back button
.hbc-back {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  border-radius: 4px;
  color: rgba(255,255,255,0.55);
  text-decoration: none;
  flex-shrink: 0;
  transition: background 0.2s, color 0.2s;

  &:hover {
    background: rgba(255,255,255,0.08);
    color: #fff;
  }
}

// Logo
.hbc-logo {
  font-family: $font-serif;
  font-size: 22px;
  font-weight: 900;
  color: #fff;
  letter-spacing: -0.5px;
}

.hbc-logo-accent {
  color: $hb-gold;
}

// Right side
.hbc-header-right {
  display: flex;
  align-items: center;
  gap: 14px;
}

.hbc-header-label {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: rgba(255,255,255,0.35);

  @media (max-width: 400px) { display: none; }
}

.hbc-header-wpp {
  background: $hb-green;
  color: #fff;
  width: 38px;
  height: 38px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  text-decoration: none;
  transition: background 0.2s;

  &:hover { background: darken($hb-green, 8%); }
}

// =============================================
// QUICK FILTERS
// =============================================
.hbc-quick-filters {
  background: rgba(0,0,0,0.25);
  border-top: 1px solid rgba(255,255,255,0.05);
  overflow: hidden;
}

.hbc-quick-scroll {
  display: flex;
  gap: 8px;
  padding: 10px 20px;
  overflow-x: auto;
  scrollbar-width: none;
  max-width: 860px;
  margin: 0 auto;

  &::-webkit-scrollbar { display: none; }
}

.hbc-quick-chip {
  background: transparent;
  border: 1px solid rgba(255,255,255,0.15);
  color: rgba(255,255,255,0.55);
  padding: 5px 14px;
  border-radius: 3px;
  font-family: $font-sans;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.5px;
  cursor: pointer;
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  flex-shrink: 0;
  transition: all 0.2s;

  i { font-size: 11px; }

  &.active,
  &:hover {
    color: #fff;
    border-color: rgba(255,255,255,0.4);
  }

  &.active {
    background: rgba(255,255,255,0.1);
    border-color: rgba(255,255,255,0.35);
  }

  &--hot.active  { background: rgba(200,150,62,0.25); border-color: $hb-gold; color: $hb-gold; }
  &--spicy.active { background: rgba(220,50,50,0.2); border-color: #e05050; color: #ffaaaa; }
  &--new.active  { background: rgba(50,200,100,0.15); border-color: #3ec878; color: #6fe8a0; }
}

// =============================================
// CATEGORY TABS
// =============================================
.hbc-cat-nav {
  background: rgba(0,0,0,0.15);
  border-top: 1px solid rgba(255,255,255,0.05);
  overflow: hidden;
}

.hbc-cat-scroll {
  display: flex;
  overflow-x: auto;
  scrollbar-width: none;
  max-width: 860px;
  margin: 0 auto;

  &::-webkit-scrollbar { display: none; }
}

.hbc-cat-tab {
  flex: 1 0 auto;
  min-width: 90px;
  background: transparent;
  border: none;
  border-bottom: 2px solid transparent;
  color: rgba(255,255,255,0.45);
  padding: 12px 16px 10px;
  font-family: $font-sans;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 3px;
  transition: color 0.25s, border-color 0.25s;
  white-space: nowrap;

  i { font-size: 14px; }

  span { line-height: 1; }

  &.active {
    color: $hb-gold;
    border-bottom-color: $hb-gold;
  }

  &:not(.active):hover {
    color: rgba(255,255,255,0.75);
  }
}

.hbc-cat-count {
  font-size: 10px !important;
  opacity: 0.5;
  font-weight: 500 !important;
}

// =============================================
// SECTION LABEL
// =============================================
.hbc-section-label {
  padding: 28px 20px 0;
  max-width: 860px;
  margin: 0 auto;
}

.hbc-eyebrow {
  display: block;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 3px;
  text-transform: uppercase;
  color: $hb-gold;
  margin-bottom: 4px;
}

.hbc-section-title {
  font-family: $font-serif;
  font-size: clamp(22px, 5vw, 30px);
  font-weight: 800;
  color: $hb-black;
  margin: 0 0 20px;
  line-height: 1.1;
}

// =============================================
// MAIN + ITEMS GRID
// =============================================
.hbc-main {
  max-width: 860px;
  margin: 0 auto;
  padding: 4px 16px 24px;
}

.hbc-items {
  display: grid;
  grid-template-columns: 1fr;
  gap: 12px;

  @media (min-width: 560px) {
    grid-template-columns: repeat(2, 1fr);
  }
}

// Individual card — horizontal (image left, text right)
.hbc-item-card {
  background: #fff;
  border-radius: 6px;
  overflow: hidden;
  display: flex;
  flex-direction: row;
  border: 1px solid $hb-border;
  transition: box-shadow 0.3s, transform 0.3s;
  text-decoration: none;

  &:hover {
    box-shadow: 0 6px 24px rgba(0,0,0,0.1);
    transform: translateY(-2px);
  }

  // Featured card — vertical layout with bigger image
  &--featured {
    flex-direction: column;

    .hbc-item-thumb {
      width: 100%;
      height: 180px;
    }

    .hbc-item-body {
      padding: 18px 20px;
    }

    h3 { font-size: 18px; }
  }

  @media (min-width: 560px) {
    &--featured {
      grid-column: span 2;
      flex-direction: row;

      .hbc-item-thumb {
        width: 42%;
        height: auto;
        min-height: 180px;
      }
    }
  }
}

.hbc-item-thumb {
  position: relative;
  flex: 0 0 38%;
  overflow: hidden;
  background: $hb-warm;

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.5s ease;
    filter: brightness(0.97);
  }

  .hbc-item-card:hover & img {
    transform: scale(1.06);
    filter: brightness(1);
  }
}

.hbc-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  font-size: 10px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 3px;
  white-space: nowrap;
  letter-spacing: 0.3px;

  &.destaque { background: rgba(200,150,62,0.95); color: #fff; }
  &.novo     { background: rgba(34,197,94,0.9); color: #fff; }
  &.picante  { background: rgba(220,50,50,0.9); color: #fff; }
}

.hbc-item-body {
  padding: 16px;
  display: flex;
  flex-direction: column;
  flex: 1;
  justify-content: space-between;
  gap: 6px;

  h3 {
    font-family: $font-serif;
    font-size: 15px;
    font-weight: 800;
    color: $hb-black;
    margin: 0;
    line-height: 1.2;
  }

  p {
    font-size: 12px;
    color: $hb-muted;
    line-height: 1.55;
    margin: 0;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
}

.hbc-item-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 8px;
  gap: 8px;
}

.hbc-price {
  font-family: $font-sans;
  font-size: 17px;
  font-weight: 700;
  color: $hb-black;
  letter-spacing: -0.3px;
}

.hbc-order-btn {
  background: $hb-green;
  color: #fff;
  padding: 7px 13px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 700;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  flex-shrink: 0;
  transition: background 0.2s, transform 0.2s;

  &:hover {
    background: darken($hb-green, 8%);
    transform: translateY(-1px);
  }
}

// =============================================
// EMPTY STATE
// =============================================
.hbc-empty {
  text-align: center;
  padding: 60px 20px;
  color: $hb-muted;

  i {
    font-size: 40px;
    display: block;
    margin-bottom: 12px;
    opacity: 0.35;
  }

  p {
    font-size: 15px;
    margin-bottom: 16px;
  }

  button {
    background: $hb-black;
    color: #fff;
    border: none;
    padding: 10px 24px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s;

    &:hover { background: $hb-gold; }
  }
}

// =============================================
// TRANSITIONS
// =============================================
.hbc-fade-enter-active,
.hbc-fade-leave-active {
  transition: opacity 0.22s ease, transform 0.22s ease;
}

.hbc-fade-enter-from,
.hbc-fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

// =============================================
// FLOAT ORDER BUTTON
// =============================================
.hbc-float-order {
  position: fixed;
  bottom: 54px;
  left: 50%;
  transform: translateX(-50%);
  background: $hb-green;
  color: #fff;
  padding: 14px 32px;
  border-radius: 4px;
  font-family: $font-sans;
  font-size: 14px;
  font-weight: 700;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 9px;
  box-shadow: 0 8px 28px rgba(37,211,102,0.4);
  z-index: 50;
  white-space: nowrap;
  letter-spacing: 0.5px;
  transition: background 0.2s, transform 0.2s, box-shadow 0.2s;

  i { font-size: 17px; }

  &:hover {
    background: darken($hb-green, 8%);
    transform: translateX(-50%) translateY(-2px);
    box-shadow: 0 10px 32px rgba(37,211,102,0.5);
  }
}

// =============================================
// UNLI CTA BAR
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
  gap: 14px;
  flex-wrap: wrap;
  background: $hb-charcoal;
  padding: 10px 20px;
  text-decoration: none;
  color: #fff;
  box-shadow: 0 -2px 16px rgba(0,0,0,0.25);
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
    gap: 6px;
    background: $hb-gold;
    color: #fff;
    padding: 7px 18px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
    i { font-size: 14px; }
  }

  @media (max-width: 480px) {
    padding: 8px 14px;
    gap: 8px;
    .unli-cta-text { font-size: 12px; }
  }
}
</style>
