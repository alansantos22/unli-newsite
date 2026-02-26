# ✅ Checklist: Atualização de Preços

**Use esta lista sempre que modificar valores no `api/pricing.json`**

---

## 📋 ANTES DE ATUALIZAR

- [ ] Identificar qual valor será alterado (produto base, página, add-on, serviço)
- [ ] Documentar o motivo da mudança (promoção, ajuste de custos, reposicionamento)
- [ ] Avaliar impacto no cálculo dos pacotes predefinidos (Essencial, Autoridade, Ecossistema)

---

## 🔧 ARQUIVOS QUE PRECISAM SER VERIFICADOS

### Backend (Prioridade CRÍTICA)
- [ ] **`api/pricing.json`** — ✏️ Atualizar valor aqui (fonte de verdade)
- [ ] **`api/lib/pricing.php`** — ⚠️ Se mudou LÓGICA de cálculo (não apenas valores)
- [ ] Testar endpoint: `curl http://localhost/api/price.php -X POST -d '{"product":"site_complete","pages":{"about":true}}'`

### Prompts de IA (Prioridade ALTA)
- [ ] **`api/ai/prompts/sdr-consultant-prompt.md`**
  - [ ] Linhas ~14 (Atendimento com Especialista)
  - [ ] Linhas ~169 (Página Extra, cobranças extras)
  - [ ] Linhas ~181-213 (Vídeos, PDFs, recursos customizados)
  - [ ] Nota: Pacotes predefinidos são **injetados dinamicamente** — não precisa editar
- [ ] **`api/ai/prompts/consultant-system-prompt.md`** — ✅ Sem preços hardcoded
- [ ] **`api/ai/prompts/extractor-system-prompt.md`** — ✅ Sem preços hardcoded

### Frontend (Prioridade MÉDIA)
- [ ] **`src/core/services/PricingService.js`** — ⚠️ Se mudou LÓGICA de cálculo
  - Método `calculateLocal()` deve espelhar `api/lib/pricing.php > compute_price()`
- [ ] **Carregar `pricing.json` automaticamente** — Frontend busca via `/api/config.php` ✅

### Documentação (Prioridade BAIXA)
- [ ] **`README.md`** — Se preços estão mencionados em exemplos
- [ ] **`docs/APRESENTACAO-EXECUTIVA.md`** — ⚠️ Documento DEPRECATED (não atualizar)
- [ ] **`docs/QUICK-START.md`** — Se há exemplos de valores
- [ ] **`docs/CONFIGURADOR-README.md`** — Se menciona preços específicos

---

## 🧪 TESTES OBRIGATÓRIOS

### Teste 1: Verificar tabela de preços injetada na IA
```bash
# Simular conversa com chatbot SDR e verificar se valores estão corretos
# Inspecionar resposta do endpoint /api/ai/sdr-chat.php
```

### Teste 2: Validar cálculo no checkout
```bash
# 1. Abrir configurador: http://localhost:8080/configurador
# 2. Selecionar pacote + páginas
# 3. Verificar valor final exibido
# 4. Prosseguir para checkout
# 5. Confirmar que pricing está correto no backend
```

### Teste 3: Validar dual-layer (Frontend vs Backend)
```bash
# 1. Abrir DevTools Console
# 2. Fazer seleção no configurador
# 3. Verificar se há warning de divergência entre local/server no console
```

### Teste 4: Criar pedido completo
```bash
# 1. Preencher briefing
# 2. Escolher pagamento (à vista / parcelado)
# 3. Verificar order_id gerado
# 4. Checar logs: api/logs/order_*.log
# 5. Confirmar valores corretos no pedido
```

---

## 📊 VALIDAÇÃO AUTOMATIZADA (RECOMENDADO)

### Script de Validação (A IMPLEMENTAR)
```bash
npm run validate-pricing
```

**O que deve fazer:**
1. Ler `api/pricing.json`
2. Extrair valores hardcoded dos prompts `.md` via regex
3. Comparar e reportar divergências
4. Testar cálculo frontend vs backend com entrada aleatória

---

## 🚀 DEPLOY

- [ ] Commitar mudanças com mensagem descritiva:
  ```bash
  git commit -m "chore(pricing): atualiza preço de [ITEM] para R$ [VALOR] - [MOTIVO]"
  ```
- [ ] Testar em staging/homologação primeiro
- [ ] Verificar se há cache de `pricing.json` no servidor (limpar se necessário)
- [ ] Monitorar logs por 24h após deploy para detectar erros de cálculo

---

## ⚠️ CASOS ESPECIAIS

### Se adicionar NOVO tipo de produto/addon
- [ ] Atualizar schema do `pricing.json`
- [ ] Adicionar suporte em `api/lib/pricing.php > normalize_selection()`
- [ ] Adicionar cálculo em `api/lib/pricing.php > compute_price()`
- [ ] Atualizar frontend `PricingService.js`
- [ ] Adicionar ao prompt SDR (ou injetar dinamicamente)
- [ ] Atualizar UI do configurador

### Se mudar LÓGICA de desconto/promoção
- [ ] Atualizar `pricing_rules` no `pricing.json`
- [ ] Verificar se `formatPricingTable()` em `sdr-chat.php` precisa ajuste
- [ ] Atualizar texto da promoção no prompt SDR (variável `{{PROMOCAO_INFO}}`)
- [ ] Comunicar ao time de marketing/vendas

### Se remover produto/addon
- [ ] ⚠️ **CUIDADO:** Pedidos antigos podem referenciá-lo
- [ ] Manter no `pricing.json` mas adicionar flag `"deprecated": true`
- [ ] Remover do prompt SDR e do configurador (esconder da UI)
- [ ] Adicionar validação no backend para rejeitar em novos pedidos

---

## 📞 CONTATO

**Dúvidas?** Consultar:
- [ANALISE-PRICING-VULNERABILITIES.md](./ANALISE-PRICING-VULNERABILITIES.md) — Análise completa do sistema
- [DUAL-LAYER-ARCHITECTURE.md](./DUAL-LAYER-ARCHITECTURE.md) — Arquitetura de validação dupla
- [ESTRUTURA-DO-PROJETO.md](./ESTRUTURA-DO-PROJETO.md) — Visão geral do sistema

**Problemas após atualização?**
- Verificar logs: `api/logs/`
- Limpar cache do navegador
- Recarregar `pricing.json` no servidor (reiniciar PHP-FPM se necessário)

---

**Última atualização:** 25/02/2026  
**Versão:** 1.0
