# Calculadora de Preços - Guia de Integração

## 📊 Visão Geral

A **PriceCalculator** é um componente interativo que permite aos visitantes calcular em tempo real o investimento do projeto, aumentando transparência e conversão.

---

## 🚀 Como Integrar na Página Site Vitrine

### 1. Importar o Componente

No arquivo [SiteVitrine.vue](SiteVitrine.vue), adicione ao `<script>`:

```javascript
import { PriceCalculator } from '@/shared/Components';

export default {
  name: 'SiteVitrine',
  components: {
    PriceCalculator
  },
  // ... resto do código
}
```

### 2. Adicionar à Template

Adicione entre as seções "Planos e Preços" e "Regras do Plano":

```vue
<!-- Planos e Preços -->
<section id="planos" class="pricing-section" data-scroll>
  <!-- ... conteúdo existente ... -->
</section>

<!-- NOVA SEÇÃO: Calculadora -->
<section id="calculadora" class="calculator-section" data-scroll>
  <div class="section-container">
    <div class="section-header">
      <span class="section-badge">Personalize</span>
      <h2 class="section-title">Ou Monte Seu Plano Personalizado</h2>
      <p class="section-description">
        Configure exatamente o que você precisa e veja o preço em tempo real.
      </p>
    </div>
    
    <PriceCalculator 
      @contract="handleContract"
      @request-proposal="handleRequestProposal"
    />
  </div>
</section>

<!-- Regras do Plano -->
<section class="policies-section" data-scroll>
  <!-- ... conteúdo existente ... -->
</section>
```

### 3. Adicionar Métodos de Callback

No `<script>`, adicione os métodos:

```javascript
methods: {
  // ... métodos existentes ...
  
  handleContract(data) {
    console.log('Contratar:', data);
    // Opções de implementação:
    
    // 1. Redirecionar para checkout
    this.$router.push({
      name: 'Checkout',
      params: { plan: data }
    });
    
    // 2. Abrir modal de checkout
    this.$emit('open-checkout-modal', data);
    
    // 3. Enviar para WhatsApp
    const message = `Quero contratar: ${data.type}, Total: R$ ${data.total}`;
    window.open(`https://wa.me/5511999999999?text=${encodeURIComponent(message)}`, '_blank');
    
    // 4. Scroll para formulário
    const formSection = document.querySelector('#contact');
    if (formSection) {
      formSection.scrollIntoView({ behavior: 'smooth' });
    }
  },
  
  handleRequestProposal(data) {
    console.log('Solicitar proposta:', data);
    // Opções de implementação:
    
    // 1. Abrir modal customizado
    this.$emit('open-proposal-modal', data);
    
    // 2. Pré-preencher formulário de contato
    this.form = {
      ...this.form,
      interest: 'projeto-customizado',
      message: this.buildProposalMessage(data)
    };
    
    // Scroll para formulário
    const formSection = document.querySelector('#contact');
    if (formSection) {
      formSection.scrollIntoView({ behavior: 'smooth' });
    }
  },
  
  buildProposalMessage(data) {
    let message = 'Olá! Gostaria de solicitar uma proposta customizada.\n\n';
    message += `Tipo de site: ${data.type}\n`;
    
    if (data.extraPages > 0) {
      message += `Páginas extras: ${data.extraPages}\n`;
    }
    
    if (data.addons && data.addons.length > 0) {
      message += `Add-ons: ${data.addons.join(', ')}\n`;
    }
    
    if (data.complexFeatures && data.complexFeatures.length > 0) {
      message += `\nFuncionalidades especiais:\n`;
      data.complexFeatures.forEach(f => {
        message += `- ${f}\n`;
      });
    }
    
    return message;
  }
}
```

### 4. Estilo da Seção (Opcional)

Se quiser estilizar a seção wrapper:

```scss
.calculator-section {
  padding: 100px 20px;
  background: $white;
  
  .section-header {
    margin-bottom: 40px;
  }
}
```

---

## ⚙️ Propriedades e Eventos

### Props (Futuras - Customização)

```javascript
props: {
  // Preços customizados
  basePrice: {
    type: Number,
    default: 500
  },
  pagePrice: {
    type: Number,
    default: 199
  },
  
  // WhatsApp padrão
  whatsappNumber: {
    type: String,
    default: '5511999999999'
  },
  
  // Add-ons customizados
  customAddons: {
    type: Array,
    default: () => []
  }
}
```

### Eventos Emitidos

| Evento | Payload | Descrição |
|--------|---------|-----------|
| `contract` | `{ type, extraPages, addons, total }` | Usuário clicou em "Contratar Agora" |
| `request-proposal` | `{ type, extraPages, addons, complexFeatures }` | Usuário solicitou proposta customizada |

---

## 🎯 Lógica de Negócio

### Gatilhos "Sob Proposta"

A calculadora automaticamente detecta quando um projeto é complexo e exige proposta:

**Condições:**
1. Tipo de site selecionado é "Customizado"
2. OU alguma funcionalidade complexa está marcada:
   - E-commerce
   - Painel administrativo
   - Área do cliente com login
   - Integrações avançadas
   - Sistema de pagamentos
   - Automações e workflows

**Comportamento:**
- Preço total muda para "Sob Proposta"
- Botão principal muda de "Contratar Agora" para "Solicitar Proposta"
- Alert aparece explicando o motivo

### Cálculo de Preço

```javascript
totalPrice = basePrice + (extraPages * 199) + sum(selectedAddons)

// Exemplos:
// Site Vitrine + 0 páginas + 0 add-ons = R$ 500
// Site Vitrine + 2 páginas + SEO = R$ 500 + R$ 398 + R$ 200 = R$ 1.098
// Multi-páginas + 1 página + SEO + Idioma = R$ 800 + R$ 199 + R$ 200 + R$ 300 = R$ 1.499
```

### Limites

- **Páginas extras:** Máximo 10 (hard limit no componente)
- **Add-ons:** Ilimitado (usuário pode selecionar todos)
- **Funcionalidades complexas:** Qualquer seleção = "Sob Proposta"

---

## 📱 Responsividade

O componente é totalmente responsivo:

### Desktop (> 768px)
- Grid de 3 colunas para tipos de site
- Add-ons em coluna única
- Checkboxes em 2 colunas
- Preço grande e destaque

### Mobile (< 768px)
- Grid de 1 coluna
- Elementos empilhados
- Preço reduzido (3rem)
- Botões full-width

---

## 🎨 Personalização Visual

### Cores

O componente usa variáveis do design system:

```scss
$p-color: #e67e22;        // Primária (laranja)
$p-dark: #d35400;         // Primária escura
$accent-purple: #9d6cff;  // Alerta "Sob Proposta"
$gray-lightness: #f3f4f6; // Fundo
$gray-darkness: #1a1d23;  // Texto
```

### Customizar Ícones

Alterar ícones Font Awesome no data():

```javascript
siteTypes: [
  {
    id: 'basic',
    name: 'Site Vitrine',
    icon: 'fas fa-laptop', // ← Mudar aqui
    price: 500
  }
]
```

---

## 📊 Tracking e Analytics

### Google Analytics

O componente já trackeia automaticamente:

**Eventos:**
- `calculator_change` - Quando usuário altera tipo/páginas/add-ons
- `click_contract` - Botão "Contratar Agora"
- `request_proposal` - Botão "Solicitar Proposta"
- `contact_whatsapp` - Botão WhatsApp

**Implementação:**
```javascript
trackCalculatorEvent(action, value) {
  if (typeof gtag !== 'undefined') {
    gtag('event', action, {
      event_category: 'Price Calculator',
      value: value
    });
  }
}
```

### Facebook Pixel

Evento `AddToCart` é disparado ao clicar em "Contratar":

```javascript
if (typeof fbq !== 'undefined') {
  fbq('track', 'AddToCart', { 
    value: this.totalPrice, 
    currency: 'BRL' 
  });
}
```

### Hotjar / Heatmaps

Para analisar interação:
- Quais tipos de site são mais selecionados
- Quantas páginas extras em média
- Add-ons mais populares
- Taxa de "Sob Proposta" vs "Contratar"

---

## 🔗 Integrações Futuras

### 1. API de Checkout

```javascript
async handleContract(data) {
  try {
    const response = await fetch('/api/checkout', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        plan: data.type,
        extraPages: data.extraPages,
        addons: data.addons,
        total: data.total
      })
    });
    
    const { checkoutUrl } = await response.json();
    window.location.href = checkoutUrl; // Stripe/Mercado Pago
  } catch (error) {
    console.error('Erro no checkout:', error);
  }
}
```

### 2. CRM / Lead Capture

```javascript
async handleRequestProposal(data) {
  // Enviar para CRM (RD Station, HubSpot, Pipedrive)
  await fetch('/api/crm/lead', {
    method: 'POST',
    body: JSON.stringify({
      source: 'Price Calculator',
      type: 'proposal_request',
      data: data
    })
  });
  
  // Redirecionar para form
  this.$router.push('/proposta-customizada');
}
```

### 3. Cupom de Desconto

Adicionar prop e lógica:

```javascript
props: {
  couponCode: String,
  couponDiscount: Number // % ou valor fixo
},
computed: {
  totalPrice() {
    let total = this.basePrice + this.extraPagesPrice + this.addonsPrice;
    
    if (this.couponCode && this.couponDiscount) {
      // % desconto
      total = total * (1 - this.couponDiscount / 100);
    }
    
    return total;
  }
}
```

---

## 🧪 Testes

### Cenários para Testar

1. **Site Vitrine básico**
   - Selecionar "Site Vitrine"
   - Não adicionar nada
   - Verificar total = R$ 500

2. **Multi-páginas com extras**
   - Selecionar "Multi-páginas"
   - Adicionar 3 páginas extras
   - Verificar total = R$ 800 + (3 × R$ 199) = R$ 1.397

3. **Add-ons combinados**
   - Selecionar "Site Vitrine"
   - Adicionar SEO + Idioma Extra
   - Verificar total = R$ 500 + R$ 200 + R$ 300 = R$ 1.000

4. **Gatilho "Sob Proposta"**
   - Selecionar qualquer tipo
   - Marcar "E-commerce"
   - Verificar: preço = "Sob Proposta", botão muda

5. **WhatsApp message builder**
   - Configurar site complexo
   - Clicar "Falar no WhatsApp"
   - Verificar mensagem formatada corretamente

---

## 📝 Copy Sugerido

### Seção Header (antes da calculadora)

```
📊 Monte Seu Plano Ideal

Cada negócio é único. Use nossa calculadora para personalizar 
seu site e ver o investimento em tempo real. Transparência 
total, sem surpresas.
```

### Call-to-Action

Se quiser adicionar CTAs extras:

```vue
<div class="calculator-cta-intro">
  <h3>Prefere um plano pronto?</h3>
  <p>Confira nosso plano Site Vitrine por R$ 500/ano</p>
  <a href="#planos" class="btn-outline">Ver Planos Prontos</a>
</div>
```

---

## ✅ Checklist de Implementação

- [ ] Importar PriceCalculator no SiteVitrine.vue
- [ ] Adicionar componente à template
- [ ] Implementar método `handleContract()`
- [ ] Implementar método `handleRequestProposal()`
- [ ] Testar todos os cenários de cálculo
- [ ] Verificar responsividade mobile
- [ ] Configurar tracking (GA + Pixel)
- [ ] Atualizar número WhatsApp
- [ ] Testar integração com formulário
- [ ] Deploy e teste em produção

---

## 🆘 Troubleshooting

### Preço não atualiza
**Problema:** Computed properties não reagem  
**Solução:** Verificar se variáveis estão em `data()` e são reativas

### Botão "Contratar" não funciona
**Problema:** Método callback não definido  
**Solução:** Adicionar `handleContract()` aos methods do componente pai

### WhatsApp não abre
**Problema:** Número incorreto ou formato errado  
**Solução:** Formato: `55` + DDD + número (sem espaços/parênteses)

### Estilo quebrado
**Problema:** Variáveis SCSS não reconhecidas  
**Solução:** Verificar se `@import` está no `<style scoped>`

---

**Desenvolvido por:** UNLI Studio  
**Componente:** PriceCalculator.vue  
**Versão:** 1.0.0
