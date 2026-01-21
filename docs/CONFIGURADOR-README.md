# Configurador Estilo "Subway" - Site Vitrine

> **Filosofia**: Poucas telas, escolhas rápidas, preço sempre visível, checkout direto. Qualquer coisa fora do padrão vira conversa personalizada (lead form).

---

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Fluxo em 3 Etapas](#fluxo-em-3-etapas)
3. [Precificação (12x vs À Vista)](#precificação-12x-vs-à-vista)
4. [Configuração JSON](#configuração-json)
5. [Integração](#integração)
6. [Customização](#customização)
7. [Eventos e Payload](#eventos-e-payload)

---

## Visão Geral

O **SiteConfigurator.vue** é um configurador visual de 3 etapas que permite ao cliente montar seu site de forma guiada, com preço transparente e checkout direto.

### Características Principais

✅ **3 etapas lineares** (Produto → Personalize → Checkout)  
✅ **Preço sempre visível** (sidebar fixa com resumo)  
✅ **12x com 10% acréscimo** vs **À vista com 10% desconto**  
✅ **Configuração via JSON** (preços, produtos, add-ons)  
✅ **Formulário de briefing completo** (elimina reuniões)  
✅ **Opção de orçamento personalizado** (captura leads complexos)  
✅ **Upload apenas de Vídeo e PDF** (imagens via links)  
✅ **Responsivo e acessível**

---

## Fluxo em 3 Etapas

### Etapa 1: Escolha do Produto

**Cards disponíveis:**

- **Landing Page**: Página única focada em conversão (R$ 599)
- **Site Completo**: Site institucional com múltiplas páginas (R$ 799)

**Elementos:**
- Descrição clara ao lado de cada card
- Preço base visível
- Mensagem de segurança: "Você ainda poderá adicionar itens no próximo passo"

**Ação:**
Ao selecionar um produto, o usuário avança para a Etapa 2.

---

### Etapa 2: Monte Seu Site

**Seção 1: Páginas Adicionais** (somente Site Completo)

Contador de páginas com botões +/- para:
- Sobre Nós (R$ 199)
- Serviços (R$ 249)
- Portfólio (R$ 299)
- FAQ (R$ 179)
- Contato (R$ 149)

**Seção 2: Conteúdo Especial**

Toggles para adicionar:
- Vídeo (+R$ 79)
- PDF (+R$ 49)

**Seção 3: Limites do Plano** (informativo)

Card com informações incluídas:
- ✅ Suporte de disponibilidade
- ✅ 1 alteração de conteúdo por ano
- ✅ Entrega em até 7 dias úteis

**Seção 4: Pergunta Final**

> "Precisa de algo mais personalizado?"  
> (E-commerce, integrações avançadas, área de login, etc.)

**Opções:**
- **Não, vamos ao checkout** → Continua para Etapa 3
- **Sim, quero orçamento personalizado** → Abre modal de lead form

**Modal de Orçamento Personalizado:**
- Nome
- WhatsApp
- E-mail
- O que você precisa? (texto)
- Prazo/urgência (select)

**Ação:**
Ao clicar em "Continuar", avança para Etapa 3.

---

### Etapa 3: Dados e Arquivos (Briefing)

**Formulário completo com:**

#### Informações da Empresa
- Nome da Empresa *
- WhatsApp *
- E-mail *
- Endereço (opcional)

#### Links e Redes Sociais
- Instagram
- Google Maps

#### Estilo Visual
Escolha entre:
- 🚀 Moderno
- 💎 Elegante
- 🖥️ Tech/Cyber

#### Conteúdo do Site
- Texto da página principal * (textarea grande)
- Textos das páginas adicionais (conforme selecionadas)

#### Arquivos
- **Upload de Vídeo** (se selecionado, aceita video/*, máx. 50MB)
- **Upload de PDF** (se selecionado, aceita PDF, máx. 10MB)
- **Links de Imagens** (textarea com links, um por linha)

#### Resumo do Pedido
Tabela com:
- Produto base
- Páginas adicionais (quantidade e preço)
- Conteúdo especial (vídeo/PDF)
- **Subtotal**
- **12x de R$ XX,XX** (10% acréscimo)
- **R$ XX,XX à vista** (10% desconto)

**Ação:**
Ao clicar em "Finalizar Pedido", emite evento `order-submitted` com payload completo.

---

## Precificação (12x vs À Vista)

### Regras

| Modalidade | Cálculo | Exibição |
|------------|---------|----------|
| **À vista** | `subtotal * 0.90` | R$ XXX,XX à vista |
| **12x** | `subtotal * 1.10 / 12` | 12x de R$ XX,XX |

### Fórmulas Implementadas

```javascript
// Subtotal = produto base + páginas + conteúdo
const subtotal = basePrice + pagesTotal + contentTotal;

// À vista (10% desconto)
const cashPrice = Math.round(subtotal * 0.90 * 100) / 100;

// Parcelado total (10% acréscimo)
const installmentTotal = Math.round(subtotal * 1.10 * 100) / 100;

// Valor da parcela (12x)
const installmentValue = Math.round((installmentTotal / 12) * 100) / 100;
```

### Exibição

**Sidebar (sempre visível):**
```
Subtotal: R$ 1.347,00
12x no cartão: R$ 123,65
À vista: R$ 1.212,30
```

**Resumo final:**
```
12x de R$ 123,65 ← destaque em laranja
R$ 1.212,30 à vista ← destaque em verde
```

---

## Configuração JSON

### Estrutura do Arquivo

**Localização:** `src/pages/SiteVitrine/config/site-configurator.json`

```json
{
  "version": "1.0.0",
  "currency": "BRL",
  "products": {
    "landing": {
      "name": "Landing Page",
      "base_price": 599,
      "description": "Página única focada em conversão..."
    },
    "site_complete": {
      "name": "Site Completo",
      "base_price": 799,
      "description": "Site institucional com mais páginas..."
    }
  },
  "page_addons": {
    "about": { "name": "Sobre Nós", "price": 199 },
    "services": { "name": "Serviços", "price": 249 },
    "portfolio": { "name": "Portfólio", "price": 299 },
    "faq": { "name": "FAQ", "price": 179 },
    "contact": { "name": "Contato", "price": 149 }
  },
  "content_addons": {
    "video": {
      "name": "Vídeo",
      "price": 79,
      "allowed_uploads": ["video/*"]
    },
    "pdf": {
      "name": "PDF",
      "price": 49,
      "allowed_uploads": ["application/pdf"]
    }
  },
  "pricing_rules": {
    "cash_discount_percent": 10,
    "installments_12_markup_percent": 10,
    "installments": 12
  }
}
```

### Como Atualizar

#### Mudar Preços
```json
"landing": {
  "base_price": 699  // Era 599
}
```

#### Adicionar Nova Página
```json
"testimonials": {
  "name": "Depoimentos",
  "price": 199
}
```

#### Mudar Regras de Pagamento
```json
"pricing_rules": {
  "cash_discount_percent": 15,  // 15% de desconto
  "installments_12_markup_percent": 8  // 8% de acréscimo
}
```

---

## Integração

### 1. Importar no Componente Pai

```vue
<script>
import { SiteConfigurator } from '@/shared/Components';

export default {
  components: {
    SiteConfigurator
  }
}
</script>
```

### 2. Usar no Template

```vue
<template>
  <div class="page">
    <SiteConfigurator 
      @order-submitted="handleOrder"
      @custom-request="handleCustomRequest"
    />
  </div>
</template>
```

### 3. Tratar Eventos

```javascript
methods: {
  handleOrder(orderPayload) {
    console.log('Pedido recebido:', orderPayload);
    
    // Enviar para backend
    this.$http.post('/api/orders', orderPayload)
      .then(response => {
        // Redirecionar para gateway de pagamento
        window.location.href = response.data.payment_url;
      });
  },
  
  handleCustomRequest(customData) {
    console.log('Orçamento personalizado:', customData);
    
    // Enviar para CRM/Email
    this.$http.post('/api/custom-requests', customData)
      .then(() => {
        alert('Solicitação enviada! Entraremos em contato.');
      });
  }
}
```

---

## Customização

### Cores e Estilos

O componente usa variáveis do design system:

```scss
// Primária (laranja)
$p-color: #e67e22;
$p-dark: #d35400;

// Sucesso (verde)
$success: #27ae60;

// Accent (azul)
$accent-blue: #4f7aff;

// Tons de cinza
$gray-darkness: #1a1d23;
$gray-medium: #7a7d85;
$gray-light: #e8e8e8;
$white: #ffffff;
```

Para mudar, edite: `src/assets/sass/settings/__colors.scss`

### Textos e Microcopy

**Suporte:**
```html
"Suporte de disponibilidade (site fora do ar)"
```

**Alteração:**
```html
"1 alteração de conteúdo por ano"
```

**Entrega:**
```html
"Entrega em até 7 dias úteis"
```

Para mudar, busque por essas strings no template do componente.

### Validações de Upload

**Vídeo:**
```javascript
if (file && file.size <= 50 * 1024 * 1024) { // 50MB
  this.briefing.video_file = file;
}
```

**PDF:**
```javascript
if (file && file.size <= 10 * 1024 * 1024) { // 10MB
  this.briefing.pdf_file = file;
}
```

---

## Eventos e Payload

### Evento: `order-submitted`

**Quando é emitido:**  
Ao clicar em "Finalizar Pedido" na Etapa 3

**Payload:**
```javascript
{
  product: "site_complete",
  pages: {
    about: 1,
    services: 1,
    portfolio: 0,
    faq: 1,
    contact: 0
  },
  content_addons: ["video"],
  briefing: {
    company_name: "UNLI Studio",
    whatsapp: "11999999999",
    email: "contato@unli.com.br",
    address: "São Paulo - SP",
    instagram: "https://instagram.com/unli",
    google_maps: "https://maps.google.com/...",
    style: "modern",
    main_content: "Texto da página principal...",
    page_contents: {
      about: "Texto sobre nós...",
      services: "Texto serviços...",
      faq: "Texto FAQ..."
    },
    image_links: "https://drive.google.com/...",
    video_file: File,
    pdf_file: null
  },
  pricing: {
    subtotal: 1347,
    cash_price: 1212.30,
    installment_total: 1481.70,
    installment_value: 123.48,
    installments: 12
  },
  timestamp: "2026-01-21T14:30:00.000Z"
}
```

### Evento: `custom-request`

**Quando é emitido:**  
Ao submeter formulário de orçamento personalizado

**Payload:**
```javascript
{
  name: "João Silva",
  whatsapp: "11999999999",
  email: "joao@empresa.com",
  description: "Preciso de e-commerce com gateway de pagamento...",
  urgency: "urgent",
  current_config: {
    product: "site_complete",
    pages: { about: 1 },
    content: ["video"]
  }
}
```

---

## Checklist de Implementação

### 1. Configuração Inicial
- [x] Criar `SiteConfigurator.vue`
- [x] Criar `site-configurator.json`
- [x] Exportar componente em `index.js`
- [ ] Importar em página de destino (ex: SiteVitrine.vue)

### 2. Backend / API
- [ ] Criar endpoint `/api/orders` (receber pedidos)
- [ ] Criar endpoint `/api/custom-requests` (receber leads)
- [ ] Configurar envio de emails (confirmação, notificação)
- [ ] Integrar com gateway de pagamento
- [ ] Implementar upload de arquivos (vídeo/PDF)

### 3. Gateway de Pagamento
- [ ] Escolher gateway (Stripe, Pagar.me, MercadoPago, etc.)
- [ ] Configurar checkout com 2 opções:
  - Parcelado (12x com acréscimo)
  - À vista (com desconto)
- [ ] Testar fluxo completo de pagamento
- [ ] Configurar webhooks para confirmação

### 4. Tracking e Analytics
- [ ] Adicionar eventos do Google Analytics
- [ ] Adicionar eventos do Facebook Pixel
- [ ] Rastrear conversões por etapa:
  - Etapa 1 completada
  - Etapa 2 completada
  - Checkout iniciado
  - Pagamento concluído

### 5. Testes
- [ ] Testar em mobile (iPhone, Android)
- [ ] Testar em tablet
- [ ] Testar em desktop (Chrome, Firefox, Safari)
- [ ] Testar uploads de arquivos
- [ ] Testar cálculos de preço
- [ ] Testar formulário de lead personalizado

### 6. Deploy
- [ ] Atualizar preços no JSON (se necessário)
- [ ] Revisar textos e microcopy
- [ ] Configurar variáveis de ambiente (API URLs)
- [ ] Deploy em produção
- [ ] Testar em produção

---

## Próximos Passos

### Melhorias Futuras

1. **Salvamento Automático**
   - Salvar progresso no localStorage
   - Permitir retomar configuração depois

2. **Cupons de Desconto**
   - Campo para inserir cupom
   - Validação e aplicação de desconto

3. **Upsells Inteligentes**
   - Sugerir páginas baseado no produto
   - "Quem escolheu X também adicionou Y"

4. **Preview em Tempo Real**
   - Mostrar mockup do site sendo montado
   - Pré-visualização do estilo escolhido

5. **Calculadora de ROI**
   - "Seu site pode gerar R$ X em leads por mês"
   - Dados baseados em benchmarks

6. **Chat de Suporte**
   - WhatsApp ou Intercom integrado
   - Tirar dúvidas durante configuração

---

## Suporte

**Dúvidas?**  
Entre em contato: contato@unli.com.br

**Documentação Relacionada:**
- [README.md](./README.md) - Estrutura geral do projeto
- [PERSONALIZACAO.md](./PERSONALIZACAO.md) - Como personalizar landing page
- [ESTRATEGIAS-CONVERSAO.md](./ESTRATEGIAS-CONVERSAO.md) - Estratégias de conversão

---

**Versão:** 1.0.0  
**Última atualização:** Janeiro 2026  
**Autor:** UNLI Studio
