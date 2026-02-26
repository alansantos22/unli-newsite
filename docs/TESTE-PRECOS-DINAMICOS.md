# TESTE: Verificação de Preços Dinâmicos SDR

## Data do Teste
25/02/2026

## Objetivo
Verificar se TODOS os preços no prompt SDR são injetados dinamicamente do `pricing.json` e se o frontend consegue acessar o arquivo.

---

## ✅ CHECKLIST DE VERIFICAÇÃO

### Backend - Injeção Dinâmica
- [x] **pricing.json** é a fonte única de verdade
- [x] Função `injectDynamicData()` carrega valores do pricing.json
- [x] Placeholders substituídos:
  - [x] `{{PRECO_ESPECIALISTA}}` - Atendimento com Especialista
  - [x] `{{PRECOS_VIDEOS}}` - Vídeo Básico e Vídeo Pro
  - [x] `{{PRECO_PDF}}` - Suporte a PDFs
  - [x] `{{RECURSOS_CUSTOM}}` - Recursos de custom_pages
  - [x] `{{TABELA_PRECOS}}` - Tabela completa de pacotes

### Frontend - Acesso ao pricing.json
- [x] Removida regra que bloqueava arquivos .json no `public/.htaccess`
- [x] Adicionada regra explícita permitindo `pricing.json` no `public/.htaccess`
- [x] Regra de permissão mantida no `api/.htaccess`

### Prompt SDR - Valores Removidos
- [x] R$ 169 (Especialista) → `{{PRECO_ESPECIALISTA}}`
- [x] R$ 35 (Vídeo Básico) → `{{PRECOS_VIDEOS}}`
- [x] R$ 120 (Vídeo Pro) → `{{PRECOS_VIDEOS}}`
- [x] R$ 15 (PDF) → `{{PRECO_PDF}}`
- [x] R$ 35, R$ 20, R$ 18 (Recursos custom) → `{{RECURSOS_CUSTOM}}`
- [x] Exemplos com valores hardcoded → `[VALOR CALCULADO]`
- [x] R$ 199/página → Removido (direcionado para tabela)

### Valores Mantidos (Analogias Ilustrativas)
- [ ] R$ 100k (E-commerce - analogia do carro) - **OK manter** (não é preço real)
- [ ] R$ 50k (Vitrine - analogia do carro) - **OK manter** (não é preço real)

---

## 🧪 TESTES MANUAIS NECESSÁRIOS

### Teste 1: Verificar Injeção Dinâmica
```bash
# 1. Modificar um valor no pricing.json (ex: especialista de 169 para 200)
# 2. Fazer uma requisição ao SDR chat
# 3. Verificar se a IA menciona R$ 200 (novo valor)
# 4. Reverter mudança
```

**Comando de teste:**
```bash
curl -X POST http://localhost/api/ai/sdr-chat.php \
  -H "Content-Type: application/json" \
  -d '{"messages":[{"role":"user","content":"Olá, quero um site"}]}'
```

**Verificar:**
- [ ] Response contém valores corretos do pricing.json
- [ ] Não há valores hardcoded de R$ 169, R$ 35, R$ 120, etc.

### Teste 2: Acesso ao pricing.json pelo Frontend
```bash
# Verificar se o arquivo é acessível via HTTP
curl -I https://unli.com.br/api/pricing.json
```

**Resultado esperado:**
```
HTTP/1.1 200 OK
Content-Type: application/json
```

**Resultado anterior (BUG):**
```
HTTP/1.1 403 Forbidden
```

### Teste 3: Configurador Frontend
1. Abrir: `http://localhost:8080/configurador?plan=site_complete`
2. Verificar console do navegador
3. **Não deve aparecer:**
   - ❌ `GET https://unli.com.br/api/pricing.json 403 (Forbidden)`
   - ❌ `⚠️ [ConfiguradorPage] Usando preços de fallback`
4. **Deve aparecer:**
   - ✅ Preços carregados corretamente do servidor
   - ✅ Calculadora funcionando

### Teste 4: Chat SDR com Preço Real
1. Abrir chatbot SDR
2. Solicitar: "Quero o plano Ecossistema Digital com Atendimento com Especialista"
3. **Verificar que a IA menciona:**
   - ✅ Valor do pacote: `R$ [VALOR DA TABELA]` (não R$ 132 hardcoded)
   - ✅ Especialista: `R$ [pricing.json service_addons.specialist_onboarding.price]`
   - ✅ Contexto anual: "assinatura anual de R$ X/mês"

### Teste 5: Regressão - Backend Ainda É Autoridade
1. Frontend envia preço manipulado no request
2. Backend recalcula do zero usando `compute_price()`
3. **Verificar:**
   - ✅ Preço final é calculado pelo backend (não confia no cliente)
   - ✅ `order_id` salvo com preço correto

---

## 🐛 PROBLEMAS CORRIGIDOS

### Problema 1: Valores Hardcoded no Prompt
**Causa:** Prompt tinha valores fixos (R$ 169, R$ 35, etc.) que desatualizavam

**Correção:**
- Substituídos por placeholders: `{{PRECO_ESPECIALISTA}}`, etc.
- Função `injectDynamicData()` popula com valores do `pricing.json`

### Problema 2: Frontend 403 ao Acessar pricing.json
**Causa:** Regra no `public/.htaccess` bloqueava TODOS os arquivos `.json`

**Correção:**
```apache
# ANTES (bloqueava tudo):
<FilesMatch "\.(env|git|gitignore|md|json)$">
  Order allow,deny
  Deny from all
</FilesMatch>

# DEPOIS (permite pricing.json):
<FilesMatch "\.(env|git|gitignore|md)$">
  Order allow,deny
  Deny from all
</FilesMatch>

<Files "pricing.json">
  Order allow,deny
  Allow from all
</Files>
```

---

## 📈 STATUS FINAL

**✅ Sistema de Preços 100% Dinâmico:**
- Backend: ✅ Única fonte de verdade (`pricing.json`)
- SDR Chat: ✅ Injeta valores dinamicamente
- Frontend: ✅ Pode acessar `pricing.json`
- Prompt: ✅ Sem hardcoded (exceto analogias ilustrativas)

**⚠️ Ação Manual Necessária:**
- Testar em produção (https://unli.com.br)
- Deploy do .htaccess atualizado
- Verificar logs da IA após mudança

---

## 🔍 MONITORAMENTO PÓS-DEPLOY

Após deploy em produção, verificar por 24h:

1. **Logs do SDR** (`api/logs/sdr-chat.log`):
   - ✅ Nenhum erro de "placeholder não substituído"
   - ✅ Valores corretos sendo usados

2. **Logs de Erro do Apache/Nginx**:
   - ❌ Nenhum 403 em `pricing.json`
   - ✅ 200 OK ao acessar

3. **Feedback dos Clientes**:
   - ❌ Nenhuma reclamação de "preço diferente"
   - ✅ Conversão normal do funil

---

**Última atualização:** 25/02/2026  
**Responsável:** Sistema automatizado de verificação
