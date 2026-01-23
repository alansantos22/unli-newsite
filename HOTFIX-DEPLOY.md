# 🔧 HOTFIX v3 - Debug Agressivo

## ⚠️ PROBLEMA: Erro 500 ainda acontecendo sem logs

Se o erro 500 continua sem mostrar logs, pode ser:
1. 🚫 Servidor web bloqueando antes do PHP executar
2. 🚫 PHP não conseguindo escrever logs
3. 🚫 Erro acontecendo em nível muito baixo
4. 🚫 Configuração de CORS/Headers problemática

## 🔍 ESTRATÉGIA DE DEBUG

### PASSO 1: Teste Básico
Faça upload do arquivo `debug-test.php` **para a RAIZ** (não em /api/) e acesse:
```
https://unli.com.br/debug-test.php
```

**O que deve mostrar:**
- ✅ Se funcionar: JSON com status "DEBUG_SUCCESS"
- ❌ Se falhar: Erro 500 ou mensagem de erro específica

### PASSO 2: Teste do Order Create
Faça upload do arquivo `order_create_debug.php` **para a RAIZ** e teste:
```bash
curl -X POST "https://unli.com.br/order_create_debug.php" \
  -H "Content-Type: application/json" \
  -d '{"product":"site_complete","pages":{"about":1},"content":["pdf"],"custom_pages":[],"video_basic_quantity":1,"video_pro_quantity":1,"payment_method":"avista","briefing":{"company_name":"Test","company_email":"test@test.com","company_phone":"123456789"}}'
```

**O que deve mostrar:**
- ✅ Se funcionar: JSON detalhado com `debug_mode: true`
- ❌ Se falhar: JSON com erro específico e stack trace completo

## 📁 ARQUIVOS DE DEBUG CRIADOS (NA RAIZ)
- ✅ `debug-test.php` - Teste básico do ambiente (RAIZ DO SITE)
- ✅ `order_create_debug.php` - Versão super verbosa do order_create (RAIZ DO SITE)

## 🚨 IMPORTANTE: PASTA /API BLOQUEADA
Como você mencionou que a pasta /api está bloqueada para acesso direto, os arquivos de debug ficam na raiz do site, mas eles apontam corretamente para os arquivos da pasta /api para testar.

## 🔄 ONDE VERIFICAR OS LOGS

### Opção 1: cPanel Error Logs
1. cPanel → Error Logs
2. Procurar por `[order_create_debug]` ou `[debug-test]`

### Opção 2: SSH/Terminal
```bash
tail -f /home/usuario/public_html/logs/error.log
# ou
tail -f /var/log/apache2/error.log
```

### Opção 3: PHP Error Log
```bash
# Descobrir onde PHP está logando:
php -i | grep error_log
```

## 🎯 O QUE PROCURAR NOS LOGS

1. **🚀 Início:** `=== INÍCIO ABSOLUTO ===`
2. **📦 Carregamento:** Mensagens de `carregado` para cada biblioteca
3. **💥 Erro:** Qualquer mensagem com `ERRO CAPTURADO`
4. **🎉 Sucesso:** `=== SUCESSO TOTAL ===`

## 📋 DIAGNÓSTICOS POSSÍVEIS

### Se `debug-test.php` falhar:
- 🔧 Problema de configuração do servidor
- 🔧 PHP não está funcionando
- 🔧 Permissões de arquivo

### Se `debug-test.php` funcionar mas `order_create_debug.php` falhar:
- 🔧 Problema em uma das bibliotecas (pricing.php, storage.php, etc.)
- 🔧 Arquivo pricing.json corrompido
- 🔧 Problema de memória/timeout

## Deploy Urgente (Escolha UMA opção)

### Opção 1: Via FTP/SFTP
```bash
# Upload apenas do arquivo modificado
scp api/lib/database.php usuario@unli.com.br:/caminho/para/api/lib/
```

### Opção 2: Via cPanel File Manager
1. Acesse cPanel > File Manager
2. Navegue até `public_html/api/lib/`
3. Upload do arquivo: `api/lib/database.php`

### Opção 3: Via Git (se configurado)
```bash
git add api/lib/database.php
git commit -m "hotfix: make database functions fail gracefully when config missing"
git push origin main
# Depois no servidor: git pull
```

### Opção 4: Copy/Paste Manual
1. Acesse o servidor via cPanel/SSH
2. Edite `api/lib/database.php`
3. Copie o conteúdo do arquivo local

## Teste Após Deploy
```bash
curl -X POST "https://unli.com.br/api/order_create.php" \
  -H "Content-Type: application/json" \
  -d '{"product":"site_complete","pages":{"about":1},"content":["pdf"],"custom_pages":[],"video_basic_quantity":1,"video_pro_quantity":1,"payment_method":"avista","briefing":{"company_name":"Test","company_email":"test@test.com","company_phone":"123456789"}}'
```

**Resultado esperado:** 
- Status 200 (não mais 500)
- JSON com `ok: true` e `order_id`
- Campo `db_saved: false` (normal, pois DB não está configurado)

## Logs Esperados
No servidor, você verá:
```
⚠️ [database] db.config.php não encontrado - operando sem banco de dados
⚠️ [database] Banco de dados não disponível - pedido salvo apenas em arquivo
```

Isso é **NORMAL** e **ESPERADO** até que você configure o MySQL.

## Próximos Passos (Não Urgente)
1. Criar arquivo `db.config.php` baseado em `db.config.example.php`
2. Configurar credenciais MySQL
3. Executar `database/install-onboarding.sql`
4. Testar novamente - `db_saved` deve retornar `true`
