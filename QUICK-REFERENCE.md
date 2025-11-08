# 🎨 QUICK REFERENCE - Cores, Fontes e Animações

## 🎨 CORES

### Primárias (Sua Identidade)
```scss
$p-color: #e67e22;        // 🟠 Laranja principal
$p-dark: #d35400;         // 🟠 Laranja escuro
$p-light: #f39c12;        // 🟠 Laranja claro
$p-lighter: #ffeaa7;      // 🟠 Laranja muito claro
```

### Neutros
```scss
$black: #0a0a0a;          // ⚫ Preto profundo
$gray-darkness: #1a1d23;  // ⬛ Cinza quase preto
$gray-dark: #2a2d35;      // ⬛ Cinza escuro
$gray-medium: #5a5d6a;    // ⚪ Cinza médio
$gray: #9095a1;           // ⚪ Cinza claro
$gray-light: #e5e7eb;     // ⚪ Cinza muito claro
$gray-lightness: #f3f4f6; // ⚪ Cinza quase branco
$white: #ffffff;          // ⚪ Branco
```

### Accents (Destaques)
```scss
$accent-blue: #4f7aff;    // 🔵 Azul elétrico
$accent-green: #00d084;   // 🟢 Verde neon
$accent-purple: #9d6cff;  // 🟣 Roxo tech
$accent-pink: #ff6ba9;    // 🩷 Rosa vibrante
$accent-yellow: #ffd93d;  // 🟡 Amarelo brilhante
$accent-cyan: #00d9ff;    // 🔵 Ciano tech
```

### Semânticas
```scss
$success: #22c55e;        // ✅ Sucesso
$warning: #f59e0b;        // ⚠️ Aviso
$error: #ef4444;          // ❌ Erro
$info: #06b6d4;           // ℹ️ Info
```

### Gradientes
```scss
$gradient-primary: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
$gradient-accent: linear-gradient(135deg, #4f7aff 0%, #9d6cff 100%);
$gradient-dark: linear-gradient(135deg, #1a1d23 0%, #2a2d35 100%);
```

### Sombras
```scss
$shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
$shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
$shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
$shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
$shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
$shadow-glow: 0 0 20px rgba(#e67e22, 0.3);
```

---

## 📝 TIPOGRAFIA

### Fontes
```scss
$font-primary: "Inter";        // Textos gerais
$font-secondary: "Poppins";    // Títulos
$font-display: "Bebas Neue";   // Display (grande)
$font-accent: "Raleway";       // Accent
```

### Tamanhos - Display
```scss
$text-display-xl: 4.5rem;      // 72px - Super hero
$text-display-lg: 3.75rem;     // 60px - Hero
$text-display-md: 3rem;        // 48px - Sub-hero
$text-display-sm: 2.25rem;     // 36px - Section title
```

### Tamanhos - Headings
```scss
$text-h1: 2.5rem;              // 40px
$text-h2: 2rem;                // 32px
$text-h3: 1.75rem;             // 28px
$text-h4: 1.5rem;              // 24px
$text-h5: 1.25rem;             // 20px
$text-h6: 1.125rem;            // 18px
```

### Tamanhos - Body
```scss
$text-xl: 1.25rem;             // 20px - Lead text
$text-lg: 1.125rem;            // 18px - Large body
$text-base: 1rem;              // 16px - Normal body
$text-sm: 0.875rem;            // 14px - Small text
$text-xs: 0.75rem;             // 12px - Caption
$text-xxs: 0.625rem;           // 10px - Tiny
```

### Pesos
```scss
$weight-thin: 100;
$weight-light: 300;
$weight-normal: 400;
$weight-medium: 500;
$weight-semibold: 600;
$weight-bold: 700;
$weight-extrabold: 800;
$weight-black: 900;
```

### Line Heights
```scss
$leading-none: 1;
$leading-tight: 1.25;
$leading-normal: 1.5;
$leading-relaxed: 1.625;
$leading-loose: 2;
```

---

## ✨ ANIMAÇÕES

### Fade
```scss
$fadeIn           // Fade in geral
$fadeInUp         // Fade + sobe
$fadeInDown       // Fade + desce
$fadeInLeft       // Fade + esquerda
$fadeInRight      // Fade + direita
```

### Scale
```scss
$scaleIn          // Escala crescendo
$scaleUp          // Aumenta escala
$pulseGlow        // Pulsa com glow
```

### Slide
```scss
$slideInUp        // Desliza de baixo
$slideInDown      // Desliza de cima
```

### Float
```scss
$float            // Flutua (20px)
$floatSlow        // Flutua devagar (10px)
```

### Outros
```scss
$bounceIn         // Bounce ao entrar
$shake            // Tremor
$shimmer          // Brilho deslizante
$skeleton         // Loading skeleton
$spin             // Gira 360°
$elasticPulse     // Pulsa elástico
```

---

## ⏱️ DURAÇÕES

```scss
$duration-instant: 0.1s;
$duration-fast: 0.2s;
$duration-normal: 0.3s;
$duration-medium: 0.5s;
$duration-slow: 0.8s;
$duration-slower: 1.2s;
$duration-slowest: 2s;
```

---

## 🎭 EASING

```scss
$ease-in-out-smooth: cubic-bezier(0.4, 0, 0.2, 1);
$ease-bounce: cubic-bezier(0.68, -0.55, 0.265, 1.55);
$ease-elastic: cubic-bezier(0.175, 0.885, 0.32, 1.275);
```

---

## 📐 ESPAÇAMENTOS

### Border Radius
```scss
$card-radius: 1rem;           // 16px
$card-radius-lg: 1.5rem;      // 24px
```

### Seções
```scss
$section-spacing: 6rem;        // 96px (desktop)
$section-spacing-mobile: 3rem; // 48px (mobile)
```

---

## 📱 BREAKPOINTS

```scss
$breakpoint-sm: 576px;
$breakpoint-md: 768px;
$breakpoint-lg: 992px;
$breakpoint-xl: 1200px;
$breakpoint-xxl: 1400px;
```

---

## 💡 EXEMPLOS DE USO

### Background com gradiente
```scss
background: $gradient-primary;
```

### Texto com cor primária
```scss
color: $p-color;
```

### Sombra grande
```scss
box-shadow: $shadow-xl;
```

### Animação fade in up
```scss
animation: $fadeInUp $duration-normal $ease-in-out-smooth;
```

### Tipografia moderna
```scss
font-family: $font-primary;
font-size: $text-lg;
font-weight: $weight-semibold;
line-height: $leading-relaxed;
```

### Hover effect
```scss
transition: all $duration-normal $ease-in-out-smooth;

&:hover {
  transform: translateY(-4px);
  box-shadow: $shadow-2xl;
}
```

### Card moderno
```scss
background: $white;
border-radius: $card-radius-lg;
box-shadow: $shadow-lg;
padding: 2rem;
```

### Botão primário
```scss
background: $gradient-primary;
color: $white;
padding: 1rem 2rem;
border-radius: 12px;
font-weight: $weight-semibold;
box-shadow: $shadow-md;
transition: all $duration-normal $ease-in-out-smooth;

&:hover {
  transform: translateY(-2px);
  box-shadow: $shadow-xl;
}
```

---

## 🎨 COMBINAÇÕES RECOMENDADAS

### Hero Section
```scss
background: $white;
color: $gray-darkness;
h1 { 
  font-size: $text-display-lg;
  font-weight: $weight-black;
  color: $gray-darkness;
}
accent { color: $p-color; }
```

### Dark Section
```scss
background: $gray-darkness;
color: $white;
h2 {
  font-size: $text-display-md;
  color: $white;
}
```

### Card Light
```scss
background: $white;
box-shadow: $shadow-lg;
border-radius: $card-radius-lg;
```

### Card Dark
```scss
background: $gray-darkness;
color: $white;
box-shadow: $shadow-xl;
```

### Botão Destaque
```scss
background: $accent-blue;
color: $white;
box-shadow: 0 0 20px rgba($accent-blue, 0.4);
```

---

## 🔥 ATALHOS ÚTEIS

### Centralizar elemento
```scss
display: flex;
align-items: center;
justify-content: center;
```

### Grid responsivo
```scss
display: grid;
grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
gap: 2rem;
```

### Texto truncado
```scss
white-space: nowrap;
overflow: hidden;
text-overflow: ellipsis;
```

### Imagem responsiva
```scss
width: 100%;
height: auto;
object-fit: cover;
```

### Glass morphism
```scss
background: rgba($white, 0.1);
backdrop-filter: blur(10px);
border: 1px solid rgba($white, 0.2);
```

---

## 🎯 Z-INDEX

```scss
$z-dropdown: 1000;
$z-sticky: 1020;
$z-fixed: 1030;
$z-modal-backdrop: 1040;
$z-modal: 1050;
$z-tooltip: 1070;
```

---

**Cole isso ao lado do seu editor para referência rápida! 📌**
