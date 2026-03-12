# Guia: SDR e Afiliados — Cadastro e Acesso

> Documento operacional para criar SDRs, acessar o painel SDR, criar afiliados e acessar o painel de afiliados.

---

## 📋 Pré-requisitos

Antes de tudo, as tabelas do banco de dados precisam existir.  
Execute as migrations na ordem abaixo (uma única vez):

| Passo | URL | Observação |
|-------|-----|------------|
| 1. Migration principal | `https://unli.com.br/api/migrate.php?password=Unli2026secure` | Cria tabela `orders` e tabelas de conversas |
| 2. Migration SDR | `https://unli.com.br/migrate-sdr.php?password=SdrMigrate2026` | Cria tabela `sdr_users` e altera `orders` |
| 3. Migration Afiliados | `https://unli.com.br/migrate-affiliates.php?password=AffMigrate2026` | Cria 4 tabelas de afiliados e altera `orders` |

> ⚠️ **Apague os arquivos `migrate-sdr.php` e `migrate-affiliates.php` da raiz após executar!**

---

## 1. Criar um novo SDR

Não existe cadastro público para SDR. A criação é feita via URL com parâmetros na migration.

### Passo a passo

1. Acesse a URL abaixo substituindo os dados do novo SDR:

```
https://unli.com.br/migrate-sdr.php?password=SdrMigrate2026&create_sdr=1&sdr_name=NOME&sdr_email=EMAIL&sdr_pass=SENHA&sdr_whatsapp=WHATSAPP
```

**Exemplo real:**

```
https://unli.com.br/migrate-sdr.php?password=SdrMigrate2026&create_sdr=1&sdr_name=João Silva&sdr_email=joao@unli.com.br&sdr_pass=MinhaSenh@Forte1&sdr_whatsapp=5511999887766
```

2. A página vai mostrar o resultado. Se o e-mail já existir, será ignorado.

### Campos

| Parâmetro | Obrigatório | Descrição |
|-----------|-------------|-----------|
| `sdr_name` | ✅ | Nome completo do SDR |
| `sdr_email` | ✅ | E-mail (será usado para login) |
| `sdr_pass` | ✅ | Senha (será criptografada com bcrypt) |
| `sdr_whatsapp` | Opcional | WhatsApp com código do país (ex: 5511999887766) |

---

## 2. Acessar a Área de SDR

### URL de acesso

```
https://unli.com.br/sdr
```

### Como fazer login

1. Acesse `https://unli.com.br/sdr`
2. Digite o **e-mail** e a **senha** cadastrados
3. Clique em **Entrar**
4. Você será redirecionado para o **Dashboard SDR**

### Páginas disponíveis no painel

| Página | URL | Descrição |
|--------|-----|-----------|
| Login | `/sdr` | Tela de login |
| Dashboard | `/sdr/dashboard` | Visão geral de vendas e métricas |
| Calculadora | `/sdr/calculadora` | Configurador + calculadora de preços |
| Nova Venda | `/sdr/nova-venda` | Registrar uma nova venda |
| Clientes | `/sdr/clientes` | Lista de clientes/vendas realizadas |
| Detalhe do cliente | `/sdr/cliente/:id` | Ver detalhes de um cliente específico |
| Configurações | `/sdr/configuracoes` | Alterar dados pessoais e senha |

### Observações

- O token de sessão fica em `sessionStorage` — ao fechar o navegador, precisa logar de novo.
- Todas as páginas (exceto login) exigem autenticação. Se o token expirar, o sistema redireciona automaticamente para `/sdr`.

---

## 3. Cadastro de Afiliados

O cadastro de afiliados é **aberto ao público**. Qualquer pessoa pode se registrar.

### URL de cadastro

```
https://unli.com.br/afiliados/registro
```

### Como se cadastrar

1. Acesse `https://unli.com.br/afiliados/registro`
2. Preencha o formulário:

| Campo | Obrigatório | Descrição |
|-------|-------------|-----------|
| Primeiro nome | ✅ | Mínimo 2 caracteres |
| Sobrenome | ✅ | Mínimo 2 caracteres |
| E-mail | ✅ | Será usado para login (deve ser único) |
| WhatsApp | ✅ | Mínimo 10 caracteres |
| Chave PIX | Opcional | Para receber comissões |
| Senha | ✅ | Mínimo 6 caracteres |
| Confirmar senha | ✅ | Deve ser igual à senha |

3. Clique em **Criar minha conta**
4. O sistema gera automaticamente:
   - Um **hash único** de 16 caracteres (código do afiliado)
   - Liga inicial **Bronze 1** com **5% de comissão**
5. Após o cadastro, é redirecionado automaticamente para o Dashboard

---

## 4. Acessar a Área de Afiliados

### URL de acesso

```
https://unli.com.br/afiliados
```

### Como fazer login

1. Acesse `https://unli.com.br/afiliados`
2. Digite o **e-mail** e a **senha** cadastrados
3. Clique em **Entrar**
4. Você será redirecionado para o **Dashboard de Afiliados**

### Funcionalidades do painel

| Aba | Descrição |
|-----|-----------|
| **Dashboard** | Resumo de vendas, progresso do título, métricas gerais |
| **Indicações** | Lista de todas as indicações com status (lead → fechado → concluído) |
| **Meus Links** | Links de indicação prontos para copiar e compartilhar |
| **Simulador** | Calculadora de comissões por produto e faixa de título |
| **Ranking** | Top 50 afiliados por volume de vendas |
| **Ligas** | Detalhes dos títulos e regras de progressão |

### Sistema de Ligas e Comissões

| Liga | Comissão | Vendas necessárias |
|------|----------|--------------------|
| Bronze 1 | 5,00% | R$ 0 |
| Bronze 2 | 6,25% | R$ 2.000 |
| Prata 1 | 7,50% | R$ 5.000 |
| Prata 2 | 8,75% | R$ 10.000 |
| Ouro 1 | 10,00% | R$ 18.000 |
| Ouro 2 | 11,25% | R$ 28.000 |
| Diamante 1 | 13,75% | R$ 40.000 |
| Diamante 2 | 15,00% | R$ 50.000 |

---

## Resumo de URLs

| O que | URL |
|-------|-----|
| Login SDR | `https://unli.com.br/sdr` |
| Dashboard SDR | `https://unli.com.br/sdr/dashboard` |
| Login Afiliados | `https://unli.com.br/afiliados` |
| Cadastro Afiliados | `https://unli.com.br/afiliados/registro` |
| Dashboard Afiliados | `https://unli.com.br/afiliados/dashboard` |
