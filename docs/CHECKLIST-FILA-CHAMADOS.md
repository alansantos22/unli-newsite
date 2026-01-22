# ✅ Checklist de Implantação - Integração Fila Chamados

## 📋 Antes de Colocar em Produção

### 1. ⚙️ Configuração

- [ ] Obtive a API Key do Fila Chamados (Integração/API)
- [ ] Editei `api/config.secure.php` com a API Key real
- [ ] Configurei a URL correta do Fila Chamados
- [ ] Defini a categoria (se necessário)
- [ ] `FILA_CHAMADOS_ENABLED` está definido como `true`

### 2. 🧪 Testes

- [ ] Executei `api/test-fila-chamados.php` com sucesso
- [ ] Teste de conexão passou
- [ ] Criei um ticket de teste (opcional)
- [ ] Fiz uma compra teste e verifiquei:
  - [ ] Pagamento foi aprovado
  - [ ] Ticket foi criado no Fila Chamados
  - [ ] Ticket contém todas as informações esperadas

### 3. 🔒 Segurança

- [ ] Arquivo `api/config.secure.php` tem permissões 600
  ```bash
  chmod 600 api/config.secure.php
  ```
- [ ] Arquivo está no `.gitignore` (✅ já configurado)
- [ ] Copiei `api/.htaccess.example` para `api/.htaccess`
  ```bash
  cp api/.htaccess.example api/.htaccess
  ```
- [ ] **IMPORTANTE:** Removi ou protegi `api/test-fila-chamados.php`
  ```bash
  rm api/test-fila-chamados.php
  # OU
  chmod 000 api/test-fila-chamados.php
  ```

### 4. 📊 Monitoramento

- [ ] Configurei monitoramento de logs:
  ```bash
  tail -f /var/log/php_errors.log | grep "FILA CHAMADOS"
  ```
- [ ] Testei visualização de logs no servidor
- [ ] Configurei alertas (opcional)

### 5. 📖 Documentação

- [ ] Li a documentação completa: `docs/FILA-CHAMADOS-INTEGRATION.md`
- [ ] Equipe foi informada sobre a nova funcionalidade
- [ ] Processo de atendimento foi atualizado

### 6. 🚀 Deploy

- [ ] Fiz backup do servidor antes do deploy
- [ ] Fiz upload dos arquivos:
  - `api/config.secure.php` (com credenciais corretas)
  - `api/lib/fila-chamados.php`
  - `api/process_payment.php` (atualizado)
  - `api/webhook-payment.php` (atualizado)
- [ ] Verifiquei permissões dos arquivos
- [ ] Testei no ambiente de produção

### 7. ✅ Validação Final

- [ ] Fiz uma venda real de teste em produção
- [ ] Ticket foi criado corretamente
- [ ] Equipe recebeu notificação
- [ ] Todas as informações estão corretas no ticket
- [ ] Cliente recebeu confirmação normalmente

---

## 🆘 Em Caso de Problemas

### Desabilitar Temporariamente

Se houver problemas em produção, desabilite rapidamente:

```php
// Em api/config.secure.php
define('FILA_CHAMADOS_ENABLED', false);
```

Isso **não afeta** o processo de pagamento, apenas desabilita a criação de tickets.

### Logs de Debug

Para ativar logs detalhados:

```php
// Em api/config.secure.php
define('DEBUG_MODE', true);
```

Depois monitore:
```bash
tail -f /var/log/php_errors.log
```

### Rollback Rápido

Se precisar reverter completamente:

1. Restaure versões anteriores dos arquivos:
   - `api/process_payment.php`
   - `api/webhook-payment.php`

2. Ou comente as chamadas da função:
   ```php
   // $ticketResult = createTicketForSale($ticketData);
   ```

---

## 📞 Contatos de Suporte

- **Suporte Técnico UNLI:** [adicionar contato]
- **Suporte Fila Chamados:** [adicionar contato]

---

## 📝 Notas Adicionais

- A integração **não afeta** o fluxo de pagamento
- Se a criação de ticket falhar, o pagamento **continua funcionando** normalmente
- Falhas são registradas nos logs para análise posterior
- O sistema tenta criar ticket apenas **1 vez por venda**

---

**Data da Implantação:** ___/___/______

**Responsável:** _________________________

**Status:** [ ] Concluído  [ ] Pendente  [ ] Com problemas
