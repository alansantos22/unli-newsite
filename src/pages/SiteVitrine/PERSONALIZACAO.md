# Guia Rápido de Personalização - Site Vitrine

## 🎯 Personalizações Comuns

### 1. Alterar Preços

**Local:** [SiteVitrine.vue](SiteVitrine.vue#L67-L72)

```vue
<!-- Hero Section -->
<span class="price-value">R$ 500<span class="price-period">/ano</span></span>

<!-- Seção de Pricing -->
<span class="price-amount">500</span>  <!-- Plano Principal -->
<span class="price-amount">199</span>  <!-- Página Extra -->
```

### 2. Atualizar Contatos

**Local:** [SiteVitrine.vue](SiteVitrine.vue#L768-L800)

```vue
<!-- WhatsApp -->
<a href="https://wa.me/5511999999999" class="info-link" target="_blank">
  (11) 99999-9999
</a>

<!-- E-mail -->
<a href="mailto:contato@empresa.com" class="info-link">
  contato@empresa.com
</a>

<!-- Horário -->
<p class="info-text">Seg a Sex: 9h às 18h</p>
<p class="info-text">Sáb: 9h às 12h</p>
```

### 3. Modificar Textos Principais

**Hero Headline:** Linha 15-17
```vue
<h1 class="hero-title">
  Seu Site Vitrine no Ar em 
  <span class="highlight-text">Até 7 Dias Úteis</span>
</h1>
```

**Hero Description:** Linha 18-21
```vue
<p class="hero-description">
  Plano anual acessível com domínio + hospedagem + SSL inclusos...
</p>
```

### 4. Adicionar/Remover Benefícios

**Local:** Linha 98-156 (Benefits Section)

Template de card:
```vue
<div class="benefit-card" data-scroll>
  <div class="benefit-icon">
    <i class="fas fa-[ICON-NAME]"></i>
  </div>
  <h3 class="benefit-title">Título do Benefício</h3>
  <p class="benefit-description">
    Descrição explicando o valor...
  </p>
</div>
```

### 5. Editar FAQ

**Local:** Linha 604-734 (FAQ Section)

Template de pergunta:
```vue
<div class="faq-item" data-scroll>
  <div class="faq-question">
    <i class="fas fa-question-circle"></i>
    <h3>Sua Pergunta Aqui?</h3>
  </div>
  <div class="faq-answer">
    Resposta detalhada com <strong>destaques</strong> importantes.
  </div>
</div>
```

### 6. Ajustar Políticas/Regras

**Local:** Linha 502-570 (Policies Section)

Exemplo:
```vue
<div class="policy-card" data-scroll>
  <div class="policy-icon">
    <i class="fas fa-clock"></i>
  </div>
  <h3 class="policy-title">Prazo de Entrega</h3>
  <p class="policy-description">
    <strong>Até 7 dias úteis</strong> após...
  </p>
  <div class="policy-note">
    Nota importante explicando detalhes...
  </div>
</div>
```

### 7. Customizar Add-ons/Upgrades

**Local:** Linha 572-602 (Upgrades Section)

Template de upgrade:
```vue
<div class="upgrade-card featured" data-scroll>
  <div class="upgrade-badge">Sem Reunião</div>
  <div class="upgrade-icon">
    <i class="fas fa-file-alt"></i>
  </div>
  <h3 class="upgrade-title">Página Extra</h3>
  <div class="upgrade-price">R$ 199/página</div>
  <p class="upgrade-description">Descrição...</p>
  <ul class="upgrade-features">
    <li><i class="fas fa-check"></i> Feature 1</li>
    <li><i class="fas fa-check"></i> Feature 2</li>
  </ul>
  <a href="#contact" class="upgrade-button">Adicionar Página</a>
</div>
```

---

## 🎨 Personalização Visual

### Cores

**Local:** `@/assets/sass/settings/__colors.scss`

```scss
// Para mudar cor principal (laranja)
$p-color: #e67e22;
$p-dark: #d35400;

// Para mudar cores de destaque
$accent-blue: #4f7aff;
$accent-purple: #9d6cff;
```

### Fontes

**Local:** [SiteVitrine.vue](SiteVitrine.vue) (linha 1155)

```scss
@import '@/assets/sass/settings/__fonts.scss';
```

Para alterar tamanhos:
```scss
.hero-title {
  font-size: 3.5rem; // Desktop
  @media (max-width: 968px) {
    font-size: 2.5rem; // Mobile
  }
}
```

---

## 🔗 Integrações

### 1. Google Analytics

Adicionar no `<script>`:
```javascript
mounted() {
  // Google Analytics
  if (typeof gtag !== 'undefined') {
    gtag('config', 'GA_MEASUREMENT_ID', {
      page_path: '/site-vitrine'
    });
  }
  
  this.initScrollAnimations();
}
```

### 2. Facebook Pixel

```javascript
mounted() {
  // Facebook Pixel
  if (typeof fbq !== 'undefined') {
    fbq('track', 'PageView');
    fbq('track', 'ViewContent', {
      content_name: 'Site Vitrine Landing Page'
    });
  }
}
```

### 3. WhatsApp Flutuante

Adicionar antes do `</template>`:
```vue
<!-- WhatsApp Float Button -->
<a 
  href="https://wa.me/5511999999999?text=Olá! Vim da página Site Vitrine" 
  class="whatsapp-float"
  target="_blank"
  rel="noopener noreferrer"
>
  <i class="fab fa-whatsapp"></i>
</a>
```

CSS:
```scss
.whatsapp-float {
  position: fixed;
  bottom: 20px;
  right: 20px;
  width: 60px;
  height: 60px;
  background: #25D366;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 14px rgba(37, 211, 102, 0.4);
  z-index: 1000;
  transition: all 0.3s ease;

  i {
    font-size: 2rem;
    color: white;
  }

  &:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(37, 211, 102, 0.6);
  }
}
```

---

## 📧 Formulário de Contato

### Backend (PHP)

Criar arquivo `contact-handler.php`:
```php
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = $data['name'];
    $email = $data['email'];
    $phone = $data['phone'];
    $interest = $data['interest'];
    $message = $data['message'];
    
    // Enviar e-mail
    $to = "contato@empresa.com";
    $subject = "Novo Lead: Site Vitrine - $interest";
    $body = "Nome: $name\nEmail: $email\nTelefone: $phone\nInteresse: $interest\n\nMensagem:\n$message";
    
    if (mail($to, $subject, $body)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erro no envio']);
    }
}
?>
```

### Vue Method (Linha 875)

```javascript
async handleSubmit() {
  this.isSubmitting = true;
  
  try {
    const response = await fetch('/api/contact-handler.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(this.form)
    });
    
    const data = await response.json();
    
    if (data.success) {
      alert('Mensagem enviada! Entraremos em contato em breve.');
      this.resetForm();
    } else {
      alert('Erro ao enviar. Tente novamente.');
    }
  } catch (error) {
    console.error('Erro:', error);
    alert('Erro na conexão. Tente novamente.');
  } finally {
    this.isSubmitting = false;
  }
}
```

---

## 🚀 Deploy

### 1. Build de Produção

```bash
npm run build
```

### 2. Configurar Rotas

Se usar Vue Router em modo history, configurar servidor:

**Nginx:**
```nginx
location / {
  try_files $uri $uri/ /index.html;
}
```

**Apache (.htaccess):**
```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /
  RewriteRule ^index\.html$ - [L]
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule . /index.html [L]
</IfModule>
```

### 3. SEO Meta Tags

Adicionar no `public/index.html`:
```html
<head>
  <title>Site Vitrine Profissional | A partir de R$ 500/ano</title>
  <meta name="description" content="Crie seu site vitrine profissional em até 7 dias úteis. Domínio, hospedagem e SSL inclusos. Plano anual acessível sem mensalidades.">
  <meta name="keywords" content="site vitrine, site profissional, criar site, site barato">
  
  <!-- Open Graph -->
  <meta property="og:title" content="Site Vitrine Profissional por R$ 500/ano">
  <meta property="og:description" content="Tenha presença online profissional com entrega rápida">
  <meta property="og:image" content="https://seusite.com/og-image.jpg">
  <meta property="og:url" content="https://seusite.com/site-vitrine">
  
  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Site Vitrine Profissional">
  <meta name="twitter:description" content="Entrega em 7 dias úteis, tudo incluso">
  <meta name="twitter:image" content="https://seusite.com/twitter-card.jpg">
</head>
```

---

## 📊 Tracking de Conversão

### Eventos para Rastrear

```javascript
methods: {
  trackEvent(action, label) {
    // Google Analytics
    if (typeof gtag !== 'undefined') {
      gtag('event', action, {
        event_category: 'Site Vitrine',
        event_label: label
      });
    }
    
    // Facebook Pixel
    if (typeof fbq !== 'undefined') {
      fbq('track', action, { content_name: label });
    }
  },
  
  handleCtaClick(cta) {
    this.trackEvent('click_cta', cta);
    // Continuar ação...
  },
  
  handleFormSubmit() {
    this.trackEvent('lead', 'contact_form');
    // Enviar formulário...
  }
}
```

Adicionar nos CTAs:
```vue
<a 
  href="#planos" 
  class="btn-primary"
  @click="trackEvent('click_cta', 'hero_quero_meu_site')"
>
  Quero Meu Site
</a>
```

---

## 🧪 Testes A/B

### Headlines para Testar

**Opção 1 (Atual):**
> Seu Site Vitrine no Ar em Até 7 Dias Úteis

**Opção 2 (Benefício):**
> Atraia Mais Clientes com Seu Site Profissional

**Opção 3 (Urgência):**
> Site Vitrine Pronto em 1 Semana - Vagas Limitadas

**Opção 4 (Prova Social):**
> Mais de 100 Empresas já Confiam em Nós

### Implementação

```javascript
data() {
  return {
    // Variação aleatória (A/B test)
    headlineVariation: Math.random() < 0.5 ? 'A' : 'B',
    headlines: {
      A: 'Seu Site Vitrine no Ar em Até 7 Dias Úteis',
      B: 'Atraia Mais Clientes com Seu Site Profissional'
    }
  }
},
computed: {
  currentHeadline() {
    return this.headlines[this.headlineVariation];
  }
}
```

```vue
<h1 class="hero-title" v-html="currentHeadline"></h1>
```

---

## ✅ Checklist Pré-Lançamento

- [ ] Preços atualizados
- [ ] Contatos corretos (WhatsApp, e-mail, horário)
- [ ] Links de redes sociais
- [ ] Imagem de mockup do hero
- [ ] Favicon configurado
- [ ] Meta tags SEO
- [ ] Google Analytics instalado
- [ ] Facebook Pixel instalado
- [ ] Formulário funcionando
- [ ] Testes em mobile
- [ ] Velocidade otimizada (< 3s)
- [ ] SSL ativo
- [ ] Links internos funcionando
- [ ] Botão WhatsApp flutuante
- [ ] Política de privacidade (link)
- [ ] Termos de uso (link)

---

## 🆘 Troubleshooting

### Animações não funcionam
**Problema:** Elementos não aparecem com scroll  
**Solução:** Verificar se `initScrollAnimations()` está sendo chamado no `mounted()`

### Formulário não envia
**Problema:** Submit não funciona  
**Solução:** 
1. Verificar método `handleSubmit()`
2. Conferir endpoint do backend
3. Checar CORS

### Layout quebrado em mobile
**Problema:** Elementos desalinhados  
**Solução:** Testar breakpoints em `@media (max-width: 968px)`

### Links âncora não funcionam
**Problema:** Scroll para seção não ocorre  
**Solução:** Verificar `initSmoothScroll()` e IDs das seções

---

**Dúvidas?** Consulte o [README principal](README.md) ou entre em contato com o time de dev.
