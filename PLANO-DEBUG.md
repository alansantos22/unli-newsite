# 🎯 PLANO DE AÇÃO - Debug do Erro 500

## 📌 SITUAÇÃO ATUAL
- ❌ Erro 500 ainda acontecendo
- ❌ Logs não aparecem (problema sério)
- ❌ Não sabemos onde está falhando

## 🔍 ESTRATÉGIA: Debug em Camadas

### 🥇 PRIORIDADE 1: Teste Básico do Ambiente
**Arquivo:** `debug-test.php`
**Objetivo:** Verificar se PHP/servidor funcionam

**Como testar:**
1. Upload `debug-test.php` para `/public_html/` (RAIZ, não /api)
2. Acesse: `https://unli.com.br/debug-test.php`
3. Verifique se retorna JSON com "DEBUG_SUCCESS"

**Possíveis resultados:**
- ✅ **JSON de sucesso** → Servidor/PHP OK, problema específico do order_create
- ❌ **Erro 500 vazio** → Problema de configuração do servidor
- ❌ **Erro 404** → Problema de roteamento/path
- ❌ **Erro de PHP** → Problema de sintaxe ou configuração PHP

---

### 🥈 PRIORIDADE 2: Debug do Order Create
**Arquivo:** `order_create_debug.php`
**Objetivo:** Logs detalhados de cada etapa

**Como testar:**
1. Upload `order_create_debug.php` para `/public_html/` (RAIZ, não /api)
2. Teste via navegador ou curl: `https://unli.com.br/order_create_debug.php`
3. Verifique console do navegador/resposta JSON

**O que procurar:**
- 🔍 Qual foi a última etapa executada com sucesso
- 🔍 Onde exatamente está falhando
- 🔍 Mensagem de erro específica

---

### 🥉 PRIORIDADE 3: Verificar Logs do Servidor
**Objetivo:** Ver se algum log está sendo gerado

**Onde procurar:**
```bash
# Logs do cPanel (mais comum)
/home/seunome/logs/error.log
/home/seunome/public_html/logs/error.log

# Logs do Apache
/var/log/apache2/error.log
/var/log/httpd/error_log

# Log do PHP
php -i | grep error_log
```

---

## 🚀 PRÓXIMOS PASSOS

### Se debug-test.php FUNCIONAR:
1. ✅ Servidor está OK
2. ➡️ Testar order_create_debug.php
3. ➡️ Analisar logs específicos

### Se debug-test.php FALHAR:
1. ❌ Problema de servidor/configuração
2. ➡️ Verificar permissões de arquivo (755 para pasta, 644 para arquivos)
3. ➡️ Verificar configuração PHP no cPanel
4. ➡️ Contatar suporte do Hostinger se necessário

### Se order_create_debug.php der erro específico:
1. 🔍 Analisar qual biblioteca está falhando
2. 🔍 Verificar se pricing.json existe e está válido
3. 🔍 Verificar permissões da pasta /orders

---

## 📋 CHECKLIST DE UPLOAD

Faça upload destes arquivos:

**Para a RAIZ do site (/public_html/):**
- [ ] `debug-test.php`
- [ ] `order_create_debug.php`

**Para a pasta /api (/public_html/api/):**
- [ ] `order_create.php` (versão corrigida)
- [ ] `lib/database.php` (versão corrigida)

---

## 💡 DICA IMPORTANTE

Se NENHUM log aparecer (nem mesmo do debug-test.php), o problema pode ser:
1. 🔧 **Permissões:** PHP não consegue escrever logs
2. 🔧 **Configuração:** `log_errors` desabilitado no PHP
3. 🔧 **Path errado:** Logs em local diferente do esperado
4. 🔧 **Servidor:** Web server bloqueando execução PHP

Neste caso, contate o suporte do Hostinger para verificar:
- Configuração do `error_log` no PHP
- Permissões da pasta de logs
- Se há algum bloqueio de segurança