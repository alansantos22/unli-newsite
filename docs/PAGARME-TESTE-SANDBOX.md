# Guia de Testes - Pagar.me Sandbox

## Cartões de Teste (API V5 - Checkout)

No Pagar.me V5, o resultado da transação é controlado pelo **nome do titular do cartão**.

| Campo              | Valor                        |
|--------------------|------------------------------|
| Número             | `4000000000000010`           |
| Nome (aprovado)    | `PAID`                       |
| Nome (recusado)    | `REFUSED`                    |
| Validade           | Qualquer data futura (ex: `12/30`) |
| CVV                | Qualquer 3 dígitos (ex: `123`) |

### Outros números de cartão válidos para teste

| Bandeira    | Número               |
|-------------|----------------------|
| Visa        | `4111111111111111`   |
| Visa        | `4242424242424242`   |
| Mastercard  | `5500000000000004`   |

---

## Configuração da Chave de API

### Formato correto das chaves (API V5)

| Ambiente   | Prefixo          | Exemplo                        |
|------------|------------------|--------------------------------|
| Teste      | `sk_test_`       | `sk_test_XXXXXXXXXXXXXXXXXX`   |
| Produção   | `sk_live_`       | `sk_live_XXXXXXXXXXXXXXXXXX`   |

> ⚠️ **Atenção:** A chave atual em `api/config.secure.php` (`sk_aaeca9cac...`) não tem o prefixo correto do Pagar.me V5. Isso provavelmente é a causa do erro nos testes.

### Como obter a chave de teste correta

1. Acesse o dashboard: [https://dashboard.pagar.me](https://dashboard.pagar.me)
2. Vá em **Configurações → Chaves de API**
3. Certifique-se de estar no modo **Sandbox/Teste** (toggle no topo do dashboard)
4. Copie a chave que começa com `sk_test_`
5. Atualize o arquivo `api/config.secure.php`:

```php
define('PAGARME_SECRET_KEY', 'sk_test_SUA_CHAVE_AQUI');
```

---

## Checklist para Testes

- [ ] Dashboard do Pagar.me em modo **Sandbox**
- [ ] Chave `PAGARME_SECRET_KEY` começa com `sk_test_`
- [ ] Usar cartão de teste com nome `PAID` para aprovação
- [ ] Usar cartão de teste com nome `REFUSED` para recusa
- [ ] Verificar logs em `api/logs/` após a tentativa

---

## Cenários de Teste

| Cenário                  | Nome no Cartão | Resultado Esperado        |
|--------------------------|----------------|---------------------------|
| Pagamento aprovado       | `PAID`         | Status: `paid`            |
| Pagamento recusado       | `REFUSED`      | Status: `refused`         |
| Pagamento pendente       | `PENDING`      | Status: `pending`         |

---

## Referências

- [Documentação Pagar.me V5](https://docs.pagar.me)
- [Dashboard Pagar.me](https://dashboard.pagar.me)
- [GitHub pagarme-php](https://github.com/pagarme/pagarme-php)
