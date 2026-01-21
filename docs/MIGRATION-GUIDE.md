# 🔄 Guia de Migração Rápida

## Passo a Passo para Integrar o Novo Design

### 1️⃣ **Backup (Opcional mas Recomendado)**
```bash
# Criar backup da MainPage atual
cp src/pages/MainPage/MainPage.vue src/pages/MainPage/MainPage.backup.vue
```

---

### 2️⃣ **Opção A: Criar Nova MainPage do Zero**

Copie o exemplo e adapte:
```bash
cp src/pages/MainPage/MainPageModern.example.vue src/pages/MainPage/MainPage.vue
```

Depois edite `MainPage.vue` e:
1. Substitua os caminhos de imagens pelos reais
2. Ajuste textos e conteúdos
3. Implemente as funções de navegação

---

### 3️⃣ **Opção B: Migração Gradual (Recomendado)**

#### Passo 1: Atualizar imports no MainPage.vue
```vue
<script>
// Adicione estes imports
import HeroModern from '@/shared/Components/HeroModern.vue';
import StatsSection from '@/shared/Components/StatsSection.vue';
import ServicesSection from '@/shared/Components/ServicesSection.vue';
import ContactSection from '@/shared/Components/ContactSection.vue';
import LazyImage from '@/shared/Components/LazyImage.vue';

export default {
  components: {
    HeroModern,
    StatsSection,
    ServicesSection,
    ContactSection,
    LazyImage,
  },
  // ... resto do código
}
</script>
```

#### Passo 2: Substituir seções uma por uma

**Antes (Banner antigo):**
```vue
<section class="banner-old">
  <!-- código antigo -->
</section>
```

**Depois (Hero Moderno):**
```vue
<HeroModern
  badge="Gamificação Premium"
  :titleLines="['Create', 'High-Quality', 'Visual']"
  description="Seu texto aqui"
  primaryButtonText="Começar Agora"
  secondaryButtonText="Ver Portfólio"
  :images="heroImages"
  :stats="heroStats"
  @primary-action="handleStartProject"
  @secondary-action="handleViewPortfolio"
/>
```

#### Passo 3: Adicionar dados no data()
```javascript
data() {
  return {
    heroImages: [
      { src: require('@/assets/img/general/photo1.jpg'), alt: 'Work 1' },
      { src: require('@/assets/img/general/photo2.jpg'), alt: 'Work 2' },
      { src: require('@/assets/img/general/photo3.jpg'), alt: 'Work 3' },
      { src: require('@/assets/img/general/photo4.jpg'), alt: 'Work 4' },
      { src: require('@/assets/img/general/photo5.jpg'), alt: 'Work 5' },
      { src: require('@/assets/img/general/photo6.jpg'), alt: 'Work 6' },
    ],
    heroStats: [
      { value: '158', label: 'Projetos' },
      { value: '625', label: 'Clientes' },
      { value: '730', label: 'Horas de Game' },
    ],
  }
}
```

---

### 4️⃣ **Estrutura Recomendada da Nova MainPage**

```vue
<template>
  <div class="main-page">
    <!-- 1. Hero Section -->
    <HeroModern 
      :images="heroImages"
      :stats="heroStats"
    />

    <!-- 2. Stats Section -->
    <StatsSection 
      variant="light"
      title="Números que Impressionam"
      :stats="mainStats"
    />

    <!-- 3. Services Section (Dark) -->
    <ServicesSection 
      variant="dark"
      cardVariant="dark"
      badge="Nossos Serviços"
      title="O Que Fazemos"
      :services="services"
    />

    <!-- 4. About/Quote Section (Custom) -->
    <section class="about-section">
      <!-- Use o código da seção "Why Choose Us" -->
    </section>

    <!-- 5. Portfolio Section -->
    <ServicesSection 
      variant="light"
      cardVariant="light"
      badge="Portfólio"
      title="Projetos Recentes"
      :services="portfolioItems"
    />

    <!-- 6. Contact Section -->
    <ContactSection 
      title="Entre em Contato"
      :contactItems="contactInfo"
      @submit="handleContactSubmit"
    />
  </div>
</template>
```

---

### 5️⃣ **Imagens: Como Organizar**

Recomendo organizar assim:

```
src/assets/img/
├── hero/
│   ├── hero-1.jpg
│   ├── hero-2.jpg
│   ├── hero-3.jpg
│   ├── hero-4.jpg
│   ├── hero-5.jpg
│   └── hero-6.jpg
├── services/
│   ├── service-1.jpg
│   ├── service-2.jpg
│   └── service-3.jpg
├── portfolio/
│   ├── project-1.jpg
│   ├── project-2.jpg
│   └── project-3.jpg
└── team/
    └── team-photo.jpg
```

**Dica:** Use imagens de qualidade (mínimo 800x600px) e otimize antes:
- Use ferramentas como TinyPNG ou ImageOptim
- Formatos recomendados: WebP (moderno) ou JPG (compatível)

---

### 6️⃣ **Ajustar Router (se necessário)**

No `router.js`, certifique-se de que a rota está correta:

```javascript
{
  path: '/',
  name: 'Home',
  component: () => import('@/pages/MainPage/MainPage.vue')
}
```

---

### 7️⃣ **Testar Responsividade**

Teste em diferentes tamanhos:
```
Desktop: 1920x1080
Tablet: 768x1024
Mobile: 375x812
```

No Chrome DevTools: `F12` → Toggle Device Toolbar (`Ctrl+Shift+M`)

---

### 8️⃣ **Customizar Cores (Opcional)**

Se quiser ajustar as cores de destaque, edite:
`src/assets/sass/settings/__colors.scss`

```scss
// Suas cores primárias (já definidas)
$p-color: #e67e22;
$p-dark: #d35400;

// Ajuste as cores de destaque se quiser
$accent-blue: #4f7aff;    // Mude para sua preferência
$accent-green: #00d084;   // Mude para sua preferência
```

---

### 9️⃣ **Formulário de Contato: Backend**

O componente `ContactSection` emite um evento `@submit`. Conecte com seu backend:

```vue
<ContactSection @submit="handleContactSubmit" />
```

```javascript
methods: {
  async handleContactSubmit(formData) {
    try {
      const response = await fetch('SEU_ENDPOINT_AQUI', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData),
      });
      
      if (response.ok) {
        alert('Mensagem enviada com sucesso!');
      }
    } catch (error) {
      console.error('Erro ao enviar:', error);
    }
  }
}
```

---

### 🔟 **Checklist Final**

- [ ] ✅ Novos componentes importados
- [ ] ✅ Imagens adicionadas e otimizadas
- [ ] ✅ Dados (stats, services, portfolio) configurados
- [ ] ✅ Eventos (@click, @submit) implementados
- [ ] ✅ Testado no desktop
- [ ] ✅ Testado no mobile
- [ ] ✅ Formulário conectado ao backend
- [ ] ✅ Navegação funcionando
- [ ] ✅ Performance verificada (Lighthouse)

---

## 🎨 Personalização Avançada

### Trocar Animações
No componente, procure por:
```scss
animation: $fadeInUp $duration-slow $ease-out-smooth forwards;
```

Troque para outras animações:
```scss
animation: $scaleIn $duration-normal $ease-out-smooth forwards;
animation: $slideInUp $duration-slow $ease-out-smooth forwards;
animation: $bounceIn $duration-slow $ease-bounce forwards;
```

### Ajustar Velocidade
```scss
// Mais rápido
animation: $fadeInUp $duration-fast $ease-out-smooth forwards;

// Mais lento
animation: $fadeInUp $duration-slower $ease-out-smooth forwards;
```

### Mudar Cores de Cards
```vue
<!-- Card claro -->
<ModernCard variant="light" />

<!-- Card escuro -->
<ModernCard variant="dark" />

<!-- Card com gradiente -->
<ModernCard variant="gradient" />
```

---

## 🚨 Troubleshooting

### Problema: Imagens não aparecem
**Solução:** Verifique o caminho e use `require()`:
```javascript
{ src: require('@/assets/img/hero/hero-1.jpg'), alt: 'Hero' }
```

### Problema: Animações não funcionam
**Solução:** Certifique-se que os arquivos SCSS foram importados:
```scss
@import '@/assets/sass/settings/__colors.scss';
@import '@/assets/sass/settings/__fonts.scss';
@import '@/assets/sass/settings/__keyframes.scss';
```

### Problema: Componente não encontrado
**Solução:** Verifique o caminho do import:
```javascript
import HeroModern from '@/shared/Components/HeroModern.vue';
```

### Problema: Contador não anima
**Solução:** O contador anima quando entra no viewport. Role a página para ver.

---

## 📞 Precisa de Ajuda?

1. Veja o `DESIGN-SYSTEM-README.md` para documentação completa
2. Veja `MainPageModern.example.vue` para exemplo funcional
3. Teste componente por componente

---

**Boa sorte com a transformação! 🚀**
