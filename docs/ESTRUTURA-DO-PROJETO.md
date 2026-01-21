# 📁 ESTRUTURA DO PROJETO - UNLI STUDIO

```
unli-newsite/
│
├── 📄 package.json                    ← Dependências (GSAP, @vueuse/core adicionados)
├── 📄 babel.config.js
├── 📄 vue.config.js
├── 📄 jsconfig.json
│
├── 📚 DOCUMENTAÇÃO (NOVOS) ←←← LEIA ESTES ARQUIVOS!
│   ├── 📄 START-AQUI-CHECKLIST.md         ← ⭐ COMECE AQUI!
│   ├── 📄 TRANSFORMACAO-COMPLETA.md        ← Resumo completo
│   ├── 📄 DESIGN-SYSTEM-README.md          ← Documentação técnica
│   ├── 📄 MIGRATION-GUIDE.md               ← Guia de migração
│   └── 📄 QUICK-REFERENCE.md               ← Referência rápida
│
├── 📁 public/
│   ├── index.html                     ← Atualizar meta tags aqui
│   └── favicon.ico
│
└── 📁 src/
    │
    ├── 📄 main.js                     ← Entry point
    ├── 📄 App.vue                     ← App root
    ├── 📄 router.js                   ← Rotas
    │
    ├── 📁 assets/
    │   ├── 📁 img/
    │   │   ├── 📁 hero/               ← ⭐ ADICIONE 6 IMAGENS AQUI
    │   │   │   ├── hero-1.jpg
    │   │   │   ├── hero-2.jpg
    │   │   │   ├── hero-3.jpg
    │   │   │   ├── hero-4.jpg
    │   │   │   ├── hero-5.jpg
    │   │   │   └── hero-6.jpg
    │   │   │
    │   │   ├── 📁 services/           ← ⭐ IMAGENS DE SERVIÇOS
    │   │   │   ├── service-1.jpg
    │   │   │   ├── service-2.jpg
    │   │   │   └── service-3.jpg
    │   │   │
    │   │   ├── 📁 portfolio/          ← ⭐ IMAGENS DE PROJETOS
    │   │   │   ├── project-1.jpg
    │   │   │   ├── project-2.jpg
    │   │   │   └── project-3.jpg
    │   │   │
    │   │   ├── 📁 team/               ← ⭐ FOTO DA EQUIPE
    │   │   │   └── team-photo.jpg
    │   │   │
    │   │   └── 📁 ... (outras pastas existentes)
    │   │
    │   └── 📁 sass/
    │       ├── styles.scss
    │       │
    │       └── 📁 settings/           ← ⭐ SISTEMA ATUALIZADO
    │           ├── __colors.scss      ← ✅ ATUALIZADO
    │           ├── __fonts.scss       ← ✅ ATUALIZADO
    │           ├── __keyframes.scss   ← ✅ ATUALIZADO
    │           ├── __animation.scss
    │           ├── __base.scss
    │           ├── __breakpoints.scss
    │           └── __mixin.scss
    │
    ├── 📁 components/
    │   └── HelloWorld.vue
    │
    ├── 📁 core/
    │   ├── 📁 composables/            ← ⭐ NOVOS HOOKS VUE
    │   │   ├── 📄 index.js            ← ✨ Export centralizado
    │   │   ├── 📄 useIntersectionObserver.js  ← ✨ Scroll animations
    │   │   ├── 📄 useLazyImage.js     ← ✨ Lazy loading
    │   │   └── 📄 useCounter.js       ← ✨ Contador animado
    │   │
    │   ├── 📁 helpers/
    │   │   └── CookieHelper.js
    │   │
    │   └── 📁 store/
    │       └── store.js
    │
    ├── 📁 shared/
    │   └── 📁 Components/             ← ⭐ NOVOS COMPONENTES MODERNOS
    │       ├── 📄 index.js            ← ✨ Export centralizado
    │       │
    │       ├── 📄 HeroModern.vue      ← ✨ Hero com grid de imagens
    │       ├── 📄 LazyImage.vue       ← ✨ Imagem com lazy loading
    │       ├── 📄 ModernCard.vue      ← ✨ Card reutilizável
    │       ├── 📄 StatsSection.vue    ← ✨ Estatísticas animadas
    │       ├── 📄 ServicesSection.vue ← ✨ Seção de serviços
    │       ├── 📄 ContactSection.vue  ← ✨ Formulário de contato
    │       ├── 📄 ModernButton.vue    ← ✨ Botão moderno
    │       │
    │       └── 📁 ... (componentes existentes)
    │           ├── 📁 footer/
    │           │   ├── FooterSite.vue
    │           │   ├── Component-Footer.js
    │           │   └── Component-Footer.scss
    │           │
    │           └── 📁 header/
    │               ├── HeaderSite.vue
    │               ├── Component-Header.js
    │               └── Component-Header.scss
    │
    └── 📁 pages/
        │
        ├── 📁 MainPage/               ← ⭐ PÁGINA PRINCIPAL
        │   ├── MainPage.vue           ← Atualizar esta
        │   ├── MainPageModern.example.vue  ← ✨ EXEMPLO COMPLETO
        │   ├── Component-MainPage.js
        │   ├── Component-MainPage.scss
        │   │
        │   └── 📁 components/
        │       ├── 📁 aboutUs/
        │       ├── 📁 banner/
        │       ├── 📁 clients/
        │       ├── 📁 comments/
        │       ├── 📁 contact/
        │       ├── 📁 ourWorks/
        │       ├── 📁 WhatWeDo/
        │       └── 📁 whoIAm/
        │
        ├── 📁 Portfolio/
        │
        └── 📁 gameHub/
            └── Index.vue

```

---

## 📊 LEGENDA

- ✨ **NOVO** - Arquivo/pasta criado nesta transformação
- ✅ **ATUALIZADO** - Arquivo existente que foi modificado
- ⭐ **IMPORTANTE** - Você precisa mexer aqui

---

## 🎯 ARQUIVOS PRINCIPAIS QUE VOCÊ VAI EDITAR

### 1. MainPage.vue (ou criar novo baseado em example)
```
src/pages/MainPage/MainPage.vue
```
**O que fazer:** Substituir conteúdo antigo pelos novos componentes

### 2. Adicionar Imagens
```
src/assets/img/hero/
src/assets/img/services/
src/assets/img/portfolio/
src/assets/img/team/
```
**O que fazer:** Adicionar suas imagens otimizadas

### 3. Cores (opcional)
```
src/assets/sass/settings/__colors.scss
```
**O que fazer:** Ajustar cores de destaque se quiser

### 4. Meta Tags
```
public/index.html
```
**O que fazer:** Atualizar title, description, og:tags

---

## 🚀 FLUXO DE TRABALHO RECOMENDADO

### Passo 1: Entender
```
📖 Ler: START-AQUI-CHECKLIST.md
📖 Ler: TRANSFORMACAO-COMPLETA.md
```

### Passo 2: Ver Exemplo
```
🎯 Abrir: src/pages/MainPage/MainPageModern.example.vue
🎯 Estudar a estrutura e dados
```

### Passo 3: Preparar Assets
```
📸 Adicionar imagens em src/assets/img/
🎨 Otimizar imagens (TinyPNG)
```

### Passo 4: Implementar
```
💻 Copiar example ou migrar gradualmente
✏️ Customizar textos e dados
🔗 Conectar navegação e formulário
```

### Passo 5: Testar
```
🖥️ Testar desktop
📱 Testar mobile
🚀 Lighthouse performance
```

---

## 📦 NOVOS PACKAGES INSTALADOS

```json
{
  "dependencies": {
    "gsap": "^3.x.x",          // Animações avançadas
    "@vueuse/core": "^10.x.x"  // Composables utilities
  }
}
```

---

## 🎨 IMPORTS ÚTEIS

### Componentes
```javascript
// Opção 1: Import individual
import HeroModern from '@/shared/Components/HeroModern.vue';
import StatsSection from '@/shared/Components/StatsSection.vue';

// Opção 2: Import via index
import { HeroModern, StatsSection } from '@/shared/Components';
```

### Composables
```javascript
import { useIntersectionObserver } from '@/core/composables';
import { useLazyImage } from '@/core/composables';
import { useCounter } from '@/core/composables';
```

### SCSS
```scss
@import '@/assets/sass/settings/__colors.scss';
@import '@/assets/sass/settings/__fonts.scss';
@import '@/assets/sass/settings/__keyframes.scss';
```

---

## 🔍 ONDE ESTÁ O QUE?

| Procurando por...           | Localização                                    |
|-----------------------------|------------------------------------------------|
| Cores e gradientes          | `src/assets/sass/settings/__colors.scss`       |
| Fontes e tamanhos           | `src/assets/sass/settings/__fonts.scss`        |
| Animações                   | `src/assets/sass/settings/__keyframes.scss`    |
| Componentes modernos        | `src/shared/Components/`                       |
| Hooks/Composables           | `src/core/composables/`                        |
| Exemplo completo            | `src/pages/MainPage/MainPageModern.example.vue`|
| Documentação                | Arquivos `.md` na raiz                         |

---

## 💡 DICAS DE ORGANIZAÇÃO

### Nomear Imagens
```
hero-1.jpg, hero-2.jpg       ✅ BOM
service-gamification.jpg     ✅ BOM
IMG_20230515.jpg             ❌ RUIM
photo1.jpg                   ❌ RUIM
```

### Tamanho de Imagens
```
Hero Grid: 800x600px mínimo
Services: 600x400px mínimo
Portfolio: 1000x600px mínimo
Team: 1200x800px recomendado
```

### Formato de Imagens
```
WebP  ✅ Melhor (se suportado)
JPG   ✅ Boa compatibilidade
PNG   ⚠️ Só para transparência
GIF   ❌ Evitar (use video/WebP)
```

---

## 🎯 CHECKLIST RÁPIDO

Antes de começar:
- [ ] Li START-AQUI-CHECKLIST.md
- [ ] Entendi a estrutura de pastas
- [ ] Vi MainPageModern.example.vue
- [ ] Preparei minhas imagens

Durante desenvolvimento:
- [ ] Criei pastas de imagens
- [ ] Adicionei imagens otimizadas
- [ ] Implementei MainPage
- [ ] Configurei dados (stats, services, etc)
- [ ] Testei responsividade

Antes de deploy:
- [ ] Build sem erros
- [ ] Lighthouse > 80
- [ ] Testado em mobile
- [ ] SEO configurado

---

**Agora você sabe exatamente onde está cada coisa! 🎯**
