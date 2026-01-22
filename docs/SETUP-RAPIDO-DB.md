# ⚡ Guia Rápido: Setup do Banco de Dados

## 📋 Checklist de Instalação

### ✅ 1. Configurar Credenciais (JÁ FEITO ✓)

- [x] Arquivo `api/db.config.php` configurado
- [x] Banco: `u969754391_unli`
- [x] Usuário: `u969754391_rootunli`

### ✅ 2. Alterar Senha de Segurança

Edite `api/migrate.php` linha 17:

```php
$MIGRATION_PASSWORD = 'COLOQUE_UMA_SENHA_FORTE_AQUI';
```

### ✅ 3. Executar Migrations

Acesse no navegador:

```
https://seu-dominio.com/api/migrate.php?password=SUA_SENHA
```

Você verá uma tela bonita com os logs!

### ✅ 4. Bloquear Acesso (Segurança)

Depois de rodar, edite `api/.htaccess` e descomente estas linhas:

```apache
<Files "migrate.php">
    Order Allow,Deny
    Deny from all
</Files>
```

---

## 🎯 Pronto! Agora Teste

### Fazer um Pedido Teste

1. Acesse o checkout do site
2. Configure um plano
3. Vá até o pagamento
4. ✅ Os dados serão salvos automaticamente no MySQL!

### Ver os Dados no Banco

Via phpMyAdmin:

```sql
SELECT * FROM unli_orders ORDER BY created_at DESC;
```

---

## 🔄 Futuras Atualizações

Precisa adicionar uma nova coluna? Simples:

1. Edite `api/migrate.php`
2. Adicione na array `$columns_to_check`
3. Execute novamente
4. Pronto! Nova coluna adicionada sem perder dados

---

## ❓ Problemas Comuns

### "Acesso negado"
- Esqueceu o `?password=SUA_SENHA` na URL

### "Erro de conexão"
- Verifique credenciais em `api/db.config.php`
- Teste no phpMyAdmin da Hostinger

### "Tabela já existe"
- Tudo certo! O sistema só adiciona o que falta

---

## 📞 Próximos Passos

1. ✅ Testar criação de pedidos
2. ✅ Configurar webhook do Mercado Pago
3. ✅ Testar fluxo completo de pagamento

**Documentação completa:** `docs/DATABASE-SETUP.md`
