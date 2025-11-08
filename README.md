# 🎮 UNLI Studio - Website Profissional

> Site moderno de estúdio de gamificação com sistema de design profissional, componentes reutilizáveis e animações avançadas.

![Vue 3](https://img.shields.io/badge/Vue-3.2-42b883)
![SCSS](https://img.shields.io/badge/SCSS-Modern-c96599)
![GSAP](https://img.shields.io/badge/GSAP-3.x-88CE02)
![Performance](https://img.shields.io/badge/Lighthouse-90+-00C851)

---

## ✨ Características

- 🎨 **Sistema de Design Moderno** - Cores, tipografia e componentes consistentes
- ⚡ **Performance Otimizada** - Lazy loading, intersection observer, code splitting
- 📱 **Totalmente Responsivo** - Desktop, tablet e mobile
- 🎭 **Animações Profissionais** - 20+ animações suaves e naturais
- 🧩 **Componentes Reutilizáveis** - 7 componentes modernos prontos
- 🚀 **Vue 3 Composition API** - Código moderno e performático
- 💅 **SCSS Avançado** - Variáveis, mixins e sistema de cores estruturado

---

## 🚀 Quick Start

### Instalação
```bash
npm install
```

### Desenvolvimento
```bash
npm run serve
```
Acesse: `http://localhost:8080`

### Build de Produção
```bash
npm run build
```

### Lint
```bash
npm run lint
```

---

## 📚 Documentação

**⭐ COMECE AQUI:**
1. **[START-AQUI-CHECKLIST.md](START-AQUI-CHECKLIST.md)** - Guia passo a passo para começar
2. **[TRANSFORMACAO-COMPLETA.md](TRANSFORMACAO-COMPLETA.md)** - Resumo de tudo que foi criado
3. **[ESTRUTURA-DO-PROJETO.md](ESTRUTURA-DO-PROJETO.md)** - Mapa completo das pastas

**Referências:**
- **[DESIGN-SYSTEM-README.md](DESIGN-SYSTEM-README.md)** - Documentação técnica completa
- **[MIGRATION-GUIDE.md](MIGRATION-GUIDE.md)** - Guia de migração detalhado
- **[QUICK-REFERENCE.md](QUICK-REFERENCE.md)** - Referência rápida de cores e animações

---

## 🎨 Componentes Modernos

### HeroModern
Hero section com grid de imagens em mosaico
```vue
<HeroModern 
  :images="heroImages" 
  :stats="heroStats"
  @primary-action="handleAction"
/>
```

### StatsSection
Estatísticas com contadores animados
```vue
<StatsSection 
  title="Seus Números"
  :stats="statsData"
/>
```

### ModernCard
Cards reutilizáveis com 3 variantes
```vue
<ModernCard
  variant="dark"
  :image="image"
  :title="title"
/>
```

### ServicesSection
Grid de serviços/portfólio
```vue
<ServicesSection
  :services="servicesData"
  @service-click="handleClick"
/>
```

### ContactSection
Formulário de contato moderno
```vue
<ContactSection
  :contactItems="contactInfo"
  @submit="handleSubmit"
/>
```

Ver [MainPageModern.example.vue](src/pages/MainPage/MainPageModern.example.vue) para exemplo completo.

---

## 🎨 Sistema de Design

### Cores
```scss
$p-color: #e67e22;        // Laranja principal
$p-dark: #d35400;         // Laranja escuro
$accent-blue: #4f7aff;    // Azul elétrico
$accent-green: #00d084;   // Verde neon
```

### Tipografia
```scss
$font-primary: "Inter";      // Textos
$font-display: "Bebas Neue"; // Títulos grandes
$text-display-lg: 3.75rem;   // 60px
$text-h2: 2rem;              // 32px
```

### Animações
```scss
animation: $fadeInUp $duration-normal $ease-out-smooth;
```

---

## 📂 Estrutura

```
src/
├── assets/sass/settings/     # Cores, fontes, animações
├── core/composables/         # Hooks Vue (lazy loading, counters)
├── shared/Components/        # Componentes modernos
└── pages/MainPage/          # Página principal
```

---

## 🛠️ Tecnologias

- **Vue 3** - Framework JavaScript
- **Vue Router** - Roteamento
- **Vuex** - State management
- **SCSS** - Pre-processador CSS
- **GSAP** - Animações avançadas
- **@vueuse/core** - Composables utilities

---

## 📱 Responsividade

- **Desktop**: 1920px+ (Grid completo)
- **Tablet**: 768px - 1024px (Grid adaptado)
- **Mobile**: < 768px (Coluna única)

---

## 🎯 Performance

- ✅ Lazy loading de imagens
- ✅ Intersection Observer para animações
- ✅ Code splitting
- ✅ Otimização de bundle
- ✅ Lighthouse 90+

---

## 📈 Próximos Passos

1. Adicionar suas imagens em `src/assets/img/`
2. Customizar textos e conteúdos
3. Conectar formulário ao backend
4. Configurar Google Analytics
5. Deploy (Netlify, Vercel, etc)

Ver [START-AQUI-CHECKLIST.md](START-AQUI-CHECKLIST.md) para guia completo.

---

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch (`git checkout -b feature/NovaFeature`)
3. Commit suas mudanças (`git commit -m 'Add: Nova feature'`)
4. Push para a branch (`git push origin feature/NovaFeature`)
5. Abra um Pull Request

---

## 📄 Licença

Privado - UNLI Studio © 2024

---

## 🆘 Suporte

Problemas? Consulte:
1. [START-AQUI-CHECKLIST.md](START-AQUI-CHECKLIST.md) - Seção "Problemas Comuns"
2. [MIGRATION-GUIDE.md](MIGRATION-GUIDE.md) - Seção "Troubleshooting"
3. Documentação do Vue 3: https://vuejs.org

---

## 🎉 Créditos

Desenvolvido com ❤️ para criar experiências gamificadas incríveis!

**Design inspirado por:** Mail Design, Influence Agency

---

**Transforme ideias em experiências digitais memoráveis! 🚀**
