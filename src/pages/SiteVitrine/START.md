# 🎯 Site Vitrine - Página de Vendas Profissional

## 📍 Acesso Rápido

**URL Local:** `http://localhost:8080/site-vitrine`  
**Produção:** `https://seudominio.com/site-vitrine`

---

## 🚀 Start Rápido

### 1. Ver a Página
```bash
cd unli-newsite
npm run serve
# Acessar: http://localhost:8080/site-vitrine
```

### 2. Personalizar (Primeiros Passos)
```bash
# 1. Atualizar contatos
#    Arquivo: src/pages/SiteVitrine/SiteVitrine.vue
#    Linhas: 768 (WhatsApp), 780 (E-mail)

# 2. Confirmar preços
#    Linhas: 67-72 (Hero), 362-450 (Pricing)

# 3. Adicionar imagem real
#    Caminho: src/assets/img/general/site-mockup.png
```

### 3. Deploy
```bash
npm run build
# Upload da pasta dist/ para servidor
```

---

## 📚 Documentação Completa

| Documento | Descrição | Link |
|-----------|-----------|------|
| **INDEX** | Visão geral e sumário executivo | [INDEX.md](INDEX.md) |
| **README** | Estrutura detalhada da página | [README.md](README.md) |
| **PERSONALIZACAO** | Como editar textos, preços e contatos | [PERSONALIZACAO.md](PERSONALIZACAO.md) |
| **CALCULADORA-INTEGRACAO** | Como adicionar calculadora de preços | [CALCULADORA-INTEGRACAO.md](CALCULADORA-INTEGRACAO.md) |
| **ESTRATEGIAS-CONVERSAO** | Marketing, CRO e otimizações | [ESTRATEGIAS-CONVERSAO.md](ESTRATEGIAS-CONVERSAO.md) |
| **CHECKLIST-LANCAMENTO** | Itens pré-lançamento | [CHECKLIST-LANCAMENTO.md](CHECKLIST-LANCAMENTO.md) |

---

## 📁 Estrutura de Arquivos

```
src/pages/SiteVitrine/
├── SiteVitrine.vue                    # ⭐ Página principal (867 linhas)
├── INDEX.md                           # Sumário executivo
├── README.md                          # Documentação técnica
├── PERSONALIZACAO.md                  # Guia de edição
├── CALCULADORA-INTEGRACAO.md          # Como integrar calculadora
├── ESTRATEGIAS-CONVERSAO.md           # Marketing e CRO
├── CHECKLIST-LANCAMENTO.md            # Pré-lançamento
└── START.md                           # ← Você está aqui

src/shared/Components/
└── PriceCalculator.vue                # 🧮 Calculadora interativa
```

---

## 🎯 O Que Esta Página Faz?

Vende **sites vitrine** (single page) como produto low-ticket de entrada com:

### Produto Principal
- **Plano:** Site Vitrine Anual
- **Preço:** R$ 500/ano
- **Inclui:** Domínio + Hospedagem + SSL + Site personalizado
- **Prazo:** 7 dias úteis

### Upsells
- **Página Extra:** R$ 199/página (sem reunião)
- **Add-ons:** SEO, Idioma, Integrações (sob consulta)
- **Projetos Custom:** Sob proposta (e-commerce, backend, etc.)

---

## 🔥 Features Principais

### ✅ Página de Vendas
- 10 seções otimizadas para conversão
- Hero impactante com preço em destaque
- Benefits, Processo, Pricing, FAQ
- 100% responsivo (mobile-first)
- Animações scroll-based

### 🧮 Calculadora de Preços (Componente Separado)
- Cálculo em tempo real
- 3 tipos de site + páginas extras
- Add-ons selecionáveis
- Detecção automática de "Sob Proposta"
- Integração WhatsApp

### 📊 Tracking
- Google Analytics pronto
- Facebook Pixel pronto
- Eventos customizados

---

## ⚙️ Configuração Mínima (5 min)

### 1. Contatos
```vue
<!-- WhatsApp: linha 768 -->
<a href="https://wa.me/5511999999999">  ← MUDAR

<!-- E-mail: linha 780 -->
<a href="mailto:contato@empresa.com">   ← MUDAR
```

### 2. Preços
```vue
<!-- Hero: linha 67 -->
<span class="price-value">R$ 500</span>  ← AJUSTAR

<!-- Pricing: linha 362 -->
<span class="price-amount">500</span>    ← AJUSTAR
```

### 3. Imagem
```
Adicionar: src/assets/img/general/site-mockup.png
Tamanho: 1200x800px (PNG)
```

---

## 🧭 Navegação dos Documentos

### Para Desenvolvedores
1. Leia [README.md](README.md) - Entender estrutura
2. Leia [PERSONALIZACAO.md](PERSONALIZACAO.md) - Como editar
3. Consulte [CHECKLIST-LANCAMENTO.md](CHECKLIST-LANCAMENTO.md) - Antes do deploy

### Para Marketing/Vendas
1. Leia [INDEX.md](INDEX.md) - Visão geral do negócio
2. Leia [ESTRATEGIAS-CONVERSAO.md](ESTRATEGIAS-CONVERSAO.md) - Otimizações
3. Use [CALCULADORA-INTEGRACAO.md](CALCULADORA-INTEGRACAO.md) - Quando integrar

### Para Gestores
1. Leia [INDEX.md](INDEX.md) - Sumário executivo
2. Revise [CHECKLIST-LANCAMENTO.md](CHECKLIST-LANCAMENTO.md) - Aprovar lançamento
3. Acompanhe métricas em [INDEX.md](INDEX.md#-métricas-de-sucesso)

---

## 🎓 Próximos Passos

### Esta Semana
1. ⚠️ **Atualizar contatos e preços** (5 min)
2. ⚠️ **Adicionar imagem real** (10 min)
3. ⚠️ **Testar formulário** (15 min)
4. ✅ **Deploy para produção**

### Próxima Semana
1. Configurar Google Analytics
2. Configurar Facebook Pixel
3. Integrar gateway de pagamento
4. Adicionar política de privacidade

### Próximo Mês
1. Integrar calculadora na página
2. Criar sequência de e-mails
3. Adicionar chat WhatsApp flutuante
4. Implementar primeiro A/B test

---

## 📊 Métricas de Sucesso

| KPI | Meta | Status |
|-----|------|--------|
| Taxa de Conversão | 2-5% | 🎯 A medir |
| Bounce Rate | < 40% | 🎯 A medir |
| Tempo na Página | > 3 min | 🎯 A medir |
| Leads/Mês | 10-50 | 🎯 A medir |

**Acompanhar em:** Google Analytics → Conversões

---

## 🆘 Precisa de Ajuda?

### Dúvida Técnica
- Consulte [README.md](README.md) ou [PERSONALIZACAO.md](PERSONALIZACAO.md)
- Procure no código por comentários explicativos
- Abra issue no repositório

### Dúvida de Negócio
- Consulte [INDEX.md](INDEX.md) - Projeções e modelo
- Consulte [ESTRATEGIAS-CONVERSAO.md](ESTRATEGIAS-CONVERSAO.md) - Marketing

### Dúvida de Deploy
- Consulte [CHECKLIST-LANCAMENTO.md](CHECKLIST-LANCAMENTO.md)
- Revise seção "Deploy" em [PERSONALIZACAO.md](PERSONALIZACAO.md)

---

## 💡 Dica Importante

> **Esta página está 95% pronta!**  
> Faltam apenas ajustes de contatos, preços e imagens.  
> O resto (estrutura, animações, responsividade, copy) já está otimizado.

**Não reinvente a roda:** Use os documentos como guia e personalize apenas o necessário.

---

## ✅ Status do Projeto

- [x] Página de vendas completa
- [x] Calculadora de preços criada
- [x] Documentação detalhada
- [x] Design responsivo
- [x] Sem erros de compilação
- [ ] Contatos atualizados ⚠️
- [ ] Imagens reais adicionadas ⚠️
- [ ] Backend do formulário ⚠️
- [ ] Deploy em produção ⏳

---

## 🚀 Comando para Começar

```bash
# Abrir no VS Code
code src/pages/SiteVitrine/SiteVitrine.vue

# Iniciar servidor
npm run serve

# Acessar página
# http://localhost:8080/site-vitrine
```

---

**Bom trabalho!** 🎉

Para qualquer dúvida, comece lendo os documentos na ordem sugerida acima.

---

**Desenvolvido por:** UNLI Studio  
**Versão:** 1.0.0  
**Data:** Janeiro 2026  
**Licença:** Proprietário
