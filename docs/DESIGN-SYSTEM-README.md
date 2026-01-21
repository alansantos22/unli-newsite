# 🎮 UNLI - Sistema de Design Moderno

Sistema de design profissional para sites de gamificação com componentes Vue 3, animações GSAP e lazy loading.

## 🚀 Novos Componentes Criados

### 1. **HeroModern** - Hero Section com Grid de Imagens
```vue
<HeroModern
  badge="Gamificação Premium"
  :titleLines="['Create', 'High-Quality', 'Visual']"
  description="Sua descrição aqui"
  primaryButtonText="Começar Agora"
  secondaryButtonText="Ver Portfólio"
  :images="heroImages"
  :stats="heroStats"
  @primary-action="handleAction"
  @secondary-action="handleAction"
/>
```

**Props:**
- `badge` - Texto do badge superior
- `titleLines` - Array de linhas do título
- `description` - Descrição do hero
- `images` - Array de objetos `{ src, alt }`
- `stats` - Array de estatísticas `{ value, label }`

---

### 2. **LazyImage** - Imagem com Lazy Loading
```vue
<LazyImage 
  src="/path/to/image.jpg" 
  alt="Descrição da imagem"
/>
```

**Features:**
- Lazy loading automático com Intersection Observer
- Skeleton loader durante carregamento
- Tratamento de erro com placeholder
- Transição suave ao carregar

---

### 3. **ModernCard** - Card Reutilizável
```vue
<ModernCard
  variant="light"
  image="/path/to/image.jpg"
  category="Gamificação"
  title="Título do Card"
  description="Descrição do serviço"
  actionText="Learn More"
  @click="handleClick"
/>
```

**Variants:**
- `light` - Fundo branco
- `dark` - Fundo escuro
- `gradient` - Gradiente colorido

---

### 4. **StatsSection** - Seção de Estatísticas com Contadores Animados
```vue
<StatsSection
  variant="light"
  title="Números que Impressionam"
  description="Descrição das estatísticas"
  :stats="statsData"
/>
```

**Stats Format:**
```javascript
stats: [
  {
    value: 250,
    label: 'Projetos Entregues',
    icon: '🎮',
    suffix: '+',
    iconBg: 'linear-gradient(135deg, #e67e22 0%, #d35400 100%)',
    decimals: 0, // opcional
  }
]
```

---

### 5. **ServicesSection** - Seção de Serviços/Grid de Cards
```vue
<ServicesSection
  variant="dark"
  cardVariant="dark"
  badge="Nossos Serviços"
  title="The Services We Offer"
  description="Descrição dos serviços"
  :services="servicesData"
  @service-click="handleClick"
/>
```

---

### 6. **ContactSection** - Formulário de Contato Moderno
```vue
<ContactSection
  title="Share Your Idea"
  description="Entre em contato conosco"
  :contactItems="contactInfo"
  :includePhone="true"
  submitText="Enviar Mensagem"
  @submit="handleSubmit"
/>
```

**Contact Items Format:**
```javascript
contactItems: [
  {
    icon: '📧',
    label: 'Email',
    value: 'contact@unli.com',
    iconBg: 'linear-gradient(135deg, #4f7aff 0%, #3d5fd9 100%)',
  }
]
```

---

## 🎨 Sistema de Cores Atualizado

Arquivo: `src/assets/sass/settings/__colors.scss`

### Cores Primárias (Mantidas)
```scss
$p-color: #e67e22;        // Laranja principal
$p-dark: #d35400;         // Laranja escuro
$p-light: #f39c12;        // Laranja claro
```

### Cores Neutras Modernas
```scss
$black: #0a0a0a;
$gray-darkness: #1a1d23;
$white: #ffffff;
```

### Cores de Destaque
```scss
$accent-blue: #4f7aff;
$accent-green: #00d084;
$accent-purple: #9d6cff;
```

---

## ✨ Animações Modernas

Arquivo: `src/assets/sass/settings/__keyframes.scss`

### Novas Animações Disponíveis
```scss
// Fade
$fadeIn, $fadeInUp, $fadeInDown, $fadeInLeft, $fadeInRight

// Scale
$scaleIn, $scaleUp, $pulseGlow

// Slide
$slideInUp, $slideInDown

// Float
$float, $floatSlow

// Outros
$shimmer, $skeleton, $bounceIn, $shake
```

### Durações
```scss
$duration-instant: 0.1s;
$duration-fast: 0.2s;
$duration-normal: 0.3s;
$duration-medium: 0.5s;
$duration-slow: 0.8s;
```

### Easing Functions
```scss
$ease-in-out-smooth: cubic-bezier(0.4, 0, 0.2, 1);
$ease-bounce: cubic-bezier(0.68, -0.55, 0.265, 1.55);
```

---

## 📝 Tipografia

Arquivo: `src/assets/sass/settings/__fonts.scss`

### Fontes Importadas
- **Inter** - Fonte principal (textos)
- **Poppins** - Fonte secundária (títulos)
- **Bebas Neue** - Display (títulos grandes)
- **Raleway** - Accent

### Tamanhos de Texto
```scss
// Display (Hero, Títulos Grandes)
$text-display-xl: 4.5rem;    // 72px
$text-display-lg: 3.75rem;   // 60px
$text-display-md: 3rem;      // 48px

// Headings
$text-h1: 2.5rem;            // 40px
$text-h2: 2rem;              // 32px
$text-h3: 1.75rem;           // 28px

// Body
$text-base: 1rem;            // 16px
$text-lg: 1.125rem;          // 18px
$text-sm: 0.875rem;          // 14px
```

---

## 🔧 Composables (Hooks Vue)

### 1. useIntersectionObserver
Observa quando elemento entra no viewport

```javascript
import { useIntersectionObserver } from '@/core/composables/useIntersectionObserver';

const { targetRef, isVisible } = useIntersectionObserver({
  threshold: 0.1,
  rootMargin: '0px',
  triggerOnce: true,
});
```

### 2. useLazyImage
Lazy loading de imagens

```javascript
import { useLazyImage } from '@/core/composables/useLazyImage';

const { targetRef, currentSrc, isLoaded, isLoading, hasError } = useLazyImage(imageSrc);
```

### 3. useCounter
Contador animado para estatísticas

```javascript
import { useCounter } from '@/core/composables/useCounter';

const { targetRef, displayValue } = useCounter(endValue, {
  duration: 2000,
  startValue: 0,
  decimals: 0,
});
```

---

## 📦 Dependências Instaladas

```json
{
  "gsap": "^3.x.x",
  "@vueuse/core": "^10.x.x"
}
```

---

## 🎯 Como Usar

### 1. Ver Exemplo Completo
Veja o arquivo: `src/pages/MainPage/MainPageModern.example.vue`

### 2. Importar Componentes
```vue
<script>
import HeroModern from '@/shared/Components/HeroModern.vue';
import StatsSection from '@/shared/Components/StatsSection.vue';
import ServicesSection from '@/shared/Components/ServicesSection.vue';
import ContactSection from '@/shared/Components/ContactSection.vue';
import ModernCard from '@/shared/Components/ModernCard.vue';
import LazyImage from '@/shared/Components/LazyImage.vue';
</script>
```

### 3. Usar no Template
```vue
<template>
  <div>
    <HeroModern :images="heroImages" />
    <StatsSection :stats="stats" />
    <ServicesSection :services="services" />
    <ContactSection @submit="handleSubmit" />
  </div>
</template>
```

---

## 🎨 Boas Práticas

### 1. **Lazy Loading**
Sempre use `LazyImage` para imagens:
```vue
<LazyImage src="/path/to/image.jpg" alt="Description" />
```

### 2. **Animações ao Scroll**
Todos os componentes já têm animações integradas via Intersection Observer

### 3. **Responsividade**
Todos os componentes são totalmente responsivos:
- Desktop: Grid completo
- Tablet: Layout adaptado
- Mobile: Coluna única

### 4. **Cores e Gradientes**
Use as variáveis SCSS:
```scss
background: $gradient-primary;
color: $p-color;
box-shadow: $shadow-xl;
```

### 5. **Tipografia**
```scss
font-family: $font-primary;
font-size: $text-lg;
font-weight: $weight-semibold;
```

---

## 🚀 Performance

- ✅ Lazy loading de imagens
- ✅ Intersection Observer para animações
- ✅ Contadores animados só quando visíveis
- ✅ Transitions CSS otimizadas
- ✅ Grid responsivo com CSS Grid
- ✅ Skeleton loaders

---

## 📱 Breakpoints

```scss
$breakpoint-sm: 576px;
$breakpoint-md: 768px;
$breakpoint-lg: 992px;
$breakpoint-xl: 1200px;
$breakpoint-xxl: 1400px;
```

---

## 🎯 Próximos Passos

1. ✅ Sistema de cores moderno
2. ✅ Animações profissionais
3. ✅ Componentes reutilizáveis
4. ✅ Lazy loading
5. ✅ Scroll animations
6. ⏳ Integrar com MainPage existente
7. ⏳ Adicionar imagens reais
8. ⏳ Conectar formulário com backend

---

## 💡 Dicas

- Use `variant="dark"` para seções com fundo escuro
- Combine cores de ícones com `iconBg`
- Estatísticas animam automaticamente ao entrar no viewport
- Cards têm hover effects automáticos
- Todos os componentes emitem eventos que você pode capturar

---

**Desenvolvido com ❤️ para criar experiências gamificadas incríveis!**
