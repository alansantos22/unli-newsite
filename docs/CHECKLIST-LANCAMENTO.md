# ✅ Checklist Pré-Lançamento - Site Vitrine

## 📋 STATUS GERAL

**Página:** `/site-vitrine`  
**Versão:** 1.0.0  
**Data de Criação:** Janeiro 2026  
**Status Técnico:** ✅ Sem erros de compilação  

---

## 🔧 CONFIGURAÇÃO INICIAL

### 1. Informações de Contato

- [ ] **WhatsApp atualizado**
  - Arquivo: `SiteVitrine.vue` linha 768
  - Formato: `https://wa.me/55DDDNUMBER`
  - Atual: `5511999999999` ⚠️ PLACEHOLDER

- [ ] **E-mail atualizado**
  - Arquivo: `SiteVitrine.vue` linha 780
  - Atual: `contato@empresa.com` ⚠️ PLACEHOLDER

- [ ] **Horário de atendimento**
  - Arquivo: `SiteVitrine.vue` linha 788-790
  - Atual: "Seg a Sex: 9h às 18h" ✓ REVISAR

- [ ] **WhatsApp da Calculadora**
  - Arquivo: `PriceCalculator.vue` linha 412
  - Sincronizar com WhatsApp principal

---

### 2. Preços e Valores

- [ ] **Plano base confirmado**
  - Hero: R$ 500/ano (linha 67-72)
  - Pricing: R$ 500/ano (linha 362-450)
  - ✓ Valores consistentes

- [ ] **Página Extra confirmada**
  - Preço: R$ 199/página (linha 443)
  - Calculadora: R$ 199 (PriceCalculator.vue linha 99)
  - ✓ Valores consistentes

- [ ] **Add-ons precificados**
  - SEO: R$ 200 ⚠️ CONFIRMAR
  - Idioma: R$ 300 ⚠️ CONFIRMAR
  - Integrações: R$ 150 ⚠️ CONFIRMAR
  - Copy: R$ 250 ⚠️ CONFIRMAR

---

### 3. Conteúdo e Imagens

- [ ] **Imagem do mockup (Hero)**
  - Caminho: `@/assets/img/general/site-mockup.png`
  - Status: ⚠️ PLACEHOLDER - Adicionar imagem real
  - Tamanho recomendado: 1200x800px
  - Formato: PNG com transparência

- [ ] **Favicon configurado**
  - Local: `public/favicon.ico`
  - Status: ✓ Verificar se está personalizado

- [ ] **Logo da empresa**
  - Atualizar em header/footer se necessário

- [ ] **Imagens de background**
  - Hero background funcionando
  - Verificar otimização (WebP)

---

### 4. Textos e Copy

- [ ] **Nome da empresa**
  - Procurar por "empresa", "UNLI", "Studio"
  - Substituir por nome real onde necessário

- [ ] **Garantia/Políticas**
  - Linha 427: "Site no ar ou seu dinheiro de volta"
  - ⚠️ VALIDAR com jurídico/financeiro

- [ ] **Prazo de entrega**
  - "7 dias úteis" - confirmar viabilidade
  - Está em: Hero, Timeline, Pricing, FAQ

- [ ] **Estatísticas**
  - Hero Stats (linha 22-34) - atualizar dados reais
  - Considerar adicionar prova social

---

## 🔌 INTEGRAÇÕES

### 1. Analytics

- [ ] **Google Analytics 4**
  ```javascript
  // Adicionar no mounted() ou main.js
  gtag('config', 'GA_MEASUREMENT_ID', {
    page_path: '/site-vitrine'
  });
  ```
  - ⚠️ Substituir `GA_MEASUREMENT_ID`

- [ ] **Teste de rastreamento**
  - Abrir página
  - Verificar no GA Real-Time

---

### 2. Facebook Pixel

- [ ] **Pixel instalado**
  ```javascript
  // Adicionar no mounted() ou main.js
  fbq('track', 'PageView');
  fbq('track', 'ViewContent', {
    content_name: 'Site Vitrine Landing'
  });
  ```
  - ⚠️ Configurar ID do Pixel

- [ ] **Teste de eventos**
  - Usar Facebook Pixel Helper (extensão)
  - Verificar PageView firing

---

### 3. Formulário de Contato

- [ ] **Backend endpoint configurado**
  - Método: `handleSubmit()` linha 875
  - Endpoint: ⚠️ DEFINIR URL da API
  - Formato: POST JSON

- [ ] **E-mails transacionais**
  - Confirmação para cliente
  - Notificação para equipe
  - Template criado

- [ ] **Teste de envio**
  - Preencher formulário
  - Verificar recebimento
  - Testar validação de campos

---

### 4. Gateway de Pagamento

- [ ] **Stripe/Mercado Pago**
  - API Keys configuradas
  - Webhook endpoint
  - Teste de checkout

- [ ] **Fluxo de pagamento**
  - Botão "Contratar Agora" → ?
  - ⚠️ DEFINIR: Modal, página externa, ou WhatsApp?

---

## 🎨 DESIGN E UX

### 1. Responsividade

- [ ] **Mobile (< 768px)**
  - Testar em iPhone, Android
  - Verificar menu, botões, forms
  - CTA sticky funcionando

- [ ] **Tablet (768-1199px)**
  - Grid ajustado
  - Imagens redimensionadas

- [ ] **Desktop (1200px+)**
  - Layout otimizado
  - Espaçamentos corretos

---

### 2. Performance

- [ ] **Lighthouse Score**
  - Performance: > 90
  - Accessibility: > 90
  - Best Practices: > 90
  - SEO: > 90

- [ ] **Imagens otimizadas**
  - Formato WebP quando possível
  - Lazy loading implementado
  - Sizes responsivos

- [ ] **Fontes carregadas**
  - Font Awesome funcionando
  - Google Fonts (se usado)

---

### 3. Animações

- [ ] **Scroll animations**
  - Elementos aparecem ao rolar
  - `data-scroll` funcionando
  - Intersection Observer ativo

- [ ] **Hover effects**
  - Cards com hover
  - Botões com transição
  - Links com feedback

---

## 🔒 SEGURANÇA E CONFORMIDADE

### 1. LGPD / Privacidade

- [ ] **Política de Privacidade**
  - Link no footer
  - Página criada
  - Conformidade LGPD

- [ ] **Termos de Uso**
  - Link no footer
  - Condições claras
  - Renovação anual explicada

- [ ] **Cookies**
  - Banner de consentimento
  - Opt-in para marketing
  - Documentação de uso

---

### 2. Validações

- [ ] **Formulário de contato**
  - Campos obrigatórios
  - Validação de e-mail
  - Validação de telefone
  - Proteção contra spam (reCAPTCHA?)

- [ ] **Calculadora**
  - Limites de páginas (max 10)
  - Validação de inputs
  - Tratamento de erros

---

## 🌐 SEO

### 1. Meta Tags

- [ ] **Title tag**
  ```html
  <title>Site Vitrine Profissional | A partir de R$ 500/ano</title>
  ```
  - Arquivo: `public/index.html` ou route meta

- [ ] **Meta description**
  ```html
  <meta name="description" content="Crie seu site vitrine profissional em até 7 dias úteis. Domínio, hospedagem e SSL inclusos por R$ 500/ano.">
  ```

- [ ] **Keywords**
  ```html
  <meta name="keywords" content="site vitrine, criar site, site profissional, site barato, site para empresa">
  ```

---

### 2. Open Graph (Redes Sociais)

- [ ] **OG Tags**
  ```html
  <meta property="og:title" content="Site Vitrine por R$ 500/ano">
  <meta property="og:description" content="Presença online profissional com entrega rápida">
  <meta property="og:image" content="URL_DA_IMAGEM_OG">
  <meta property="og:url" content="https://seusite.com/site-vitrine">
  <meta property="og:type" content="website">
  ```

- [ ] **Twitter Card**
  ```html
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Site Vitrine Profissional">
  <meta name="twitter:description" content="R$ 500/ano com tudo incluso">
  <meta name="twitter:image" content="URL_DA_IMAGEM_TWITTER">
  ```

- [ ] **Teste de preview**
  - Facebook Debugger
  - Twitter Card Validator
  - LinkedIn Post Inspector

---

### 3. Estrutura

- [ ] **Heading hierarchy**
  - H1 único por página ✓
  - H2, H3 em ordem lógica ✓
  - Semântica correta

- [ ] **Alt text em imagens**
  - Linha 57: "Exemplo de Site Vitrine" ✓
  - Verificar todas as imagens

- [ ] **Links internos**
  - Smooth scroll funcionando ✓
  - IDs corretos (#planos, #como-funciona, etc.)

---

## 🚀 DEPLOY

### 1. Build

- [ ] **Compilar para produção**
  ```bash
  npm run build
  ```
  - Sem erros de build
  - Tamanho dos chunks otimizado

- [ ] **Testar build localmente**
  ```bash
  npm run serve
  ```

---

### 2. Servidor

- [ ] **Configuração do servidor**
  - Nginx ou Apache configurado
  - Redirecionamento de rotas (SPA)
  - Gzip/Brotli compressão

- [ ] **SSL ativo**
  - Certificado válido
  - HTTPS forçado
  - Mixed content resolvido

- [ ] **Domínio configurado**
  - DNS apontando
  - Propagação completa
  - WWW redirecionando

---

### 3. Monitoramento

- [ ] **Uptime monitoring**
  - UptimeRobot ou similar
  - Alertas configurados

- [ ] **Error tracking**
  - Sentry ou LogRocket
  - Captura de erros JS

- [ ] **Backup automático**
  - Banco de dados (se houver)
  - Arquivos estáticos

---

## 🧪 TESTES

### 1. Funcional

- [ ] **Navegação**
  - Todos os links funcionam
  - Menu responsivo
  - Smooth scroll

- [ ] **Formulário**
  - Envio com sucesso
  - Validações funcionando
  - Mensagens de erro/sucesso

- [ ] **Calculadora** (se integrada)
  - Cálculo correto
  - Gatilhos "Sob Proposta"
  - WhatsApp message builder

---

### 2. Cross-browser

- [ ] **Chrome** (última versão)
- [ ] **Firefox** (última versão)
- [ ] **Safari** (macOS e iOS)
- [ ] **Edge** (última versão)
- [ ] **Opera** (opcional)

---

### 3. Dispositivos

- [ ] **iPhone** (Safari)
- [ ] **Android** (Chrome)
- [ ] **iPad**
- [ ] **Desktop 1920x1080**
- [ ] **Laptop 1366x768**

---

## 📊 PÓS-LANÇAMENTO

### Primeira Semana

- [ ] **Monitorar analytics**
  - Tráfego
  - Bounce rate
  - Tempo na página

- [ ] **Coletar feedback**
  - Primeiros visitantes
  - Amigos/família
  - Beta testers

- [ ] **Ajustes rápidos**
  - Corrigir bugs encontrados
  - Melhorar copy se necessário

---

### Primeiro Mês

- [ ] **Análise de heatmap**
  - Hotjar ou Clarity
  - Identificar pontos de fricção

- [ ] **A/B test inicial**
  - Headline hero
  - CTA principal

- [ ] **Adicionar depoimentos**
  - Primeiros clientes
  - Screenshots de sucesso

---

## 📝 NOTAS FINAIS

### Prioridade Alta 🔴
- Atualizar contatos (WhatsApp, e-mail)
- Confirmar preços
- Adicionar imagem real do mockup
- Configurar formulário backend

### Prioridade Média 🟡
- Implementar GA e Pixel
- Otimizar SEO (meta tags)
- Configurar gateway de pagamento
- Adicionar política de privacidade

### Prioridade Baixa 🟢
- Integrar calculadora (já criada, aguarda fase 2)
- Criar sequência de e-mails
- Implementar chat
- A/B tests avançados

---

## ✅ APROVAÇÃO FINAL

**Revisor:** _________________  
**Data:** ____/____/______  
**Aprovado para produção:** [ ] SIM  [ ] NÃO

**Observações:**
```
_________________________________________________________________
_________________________________________________________________
_________________________________________________________________
```

---

**LEMBRETE:** Este checklist deve ser revisado antes de cada deploy importante!

**Última atualização:** Janeiro 2026  
**Versão do checklist:** 1.0
