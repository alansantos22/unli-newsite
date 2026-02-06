# 🤖 Sistema de Geração de Conteúdo com IA

Sistema completo para geração automática de conteúdo via **Gemini 1.5 Flash** integrado ao Configurador de Sites.

## 📋 Índice

- [Arquitetura](#arquitetura)
- [Configuração](#configuração)
- [API Endpoints](#api-endpoints)
- [Componentes Vue](#componentes-vue)
- [Tipos de Página](#tipos-de-página)
- [Segurança](#segurança)
- [Custos](#custos)

---

## 🏗️ Arquitetura

```
┌─────────────────────────────────────────────────────────────────┐
│                         FRONTEND (Vue.js)                        │
├─────────────────────────────────────────────────────────────────┤
│  AIContentGenerator.vue    →   AIContentRenderer.vue            │
│       (Wizard 4 steps)            (Edição do JSON)              │
└──────────────────────────┬──────────────────────────────────────┘
                           │ POST /api/ai/generate-content.php
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│                          BACKEND (PHP)                          │
├─────────────────────────────────────────────────────────────────┤
│  ContentGeneratorFactory.php                                     │
│       ├── Seleciona System Instruction por page_type            │
│       ├── Define schema JSON para cada tipo                     │
│       └── Chama Gemini API com JSON Mode                        │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│                    GEMINI 1.5 FLASH API                         │
│              (JSON Mode + Safety Settings)                       │
└─────────────────────────────────────────────────────────────────┘
```

---

## ⚙️ Configuração

### 1. Obter API Key do Gemini

1. Acesse [Google AI Studio](https://aistudio.google.com/app/apikey)
2. Crie uma nova API Key
3. Copie a chave gerada

### 2. Configurar no Servidor

Edite `api/config.secure.php`:

```php
// ============================================
// CONFIGURAÇÕES GEMINI AI
// ============================================

define('GEMINI_API_KEY', 'SUA_API_KEY_AQUI');
define('GEMINI_MODEL', 'gemini-1.5-flash');
define('GEMINI_RATE_LIMIT', 10); // req/minuto por sessão
```

### 3. Variável de Ambiente (Alternativa mais segura)

```bash
# .env ou configuração do servidor
export GEMINI_API_KEY="sua-api-key-aqui"
```

---

## 🔌 API Endpoints

### GET `/api/ai/page-types.php`

Retorna tipos de página e tons de voz disponíveis.

**Response:**
```json
{
  "success": true,
  "page_types": {
    "sobre_nos": {
      "label": "Sobre Nós",
      "icon": "🏢",
      "description": "História, missão, visão e valores",
      "input_placeholder": "Conte a história da sua empresa...",
      "input_hint": "💡 Quanto mais detalhes, melhor!",
      "exemplo": "Comecei a empresa em 2015..."
    }
    // ... outros tipos
  },
  "voice_tones": {
    "profissional": {
      "label": "Profissional",
      "icon": "💼",
      "description": "Transmite autoridade"
    }
    // ... outros tons
  }
}
```

### POST `/api/ai/generate-content.php`

Gera conteúdo estruturado via IA.

**Request:**
```json
{
  "page_type": "sobre_nos",
  "user_input": "Comecei minha empresa de tecnologia em 2015...",
  "voice_tone": "profissional",
  "company_name": "TechSolutions",
  "niche": "Tecnologia para pequenas empresas"
}
```

**Response (sucesso):**
```json
{
  "success": true,
  "page_type": "sobre_nos",
  "content": {
    "titulo_h1": "Inovação que Transforma Negócios",
    "subtitulo": "Há 9 anos ajudando empresas a crescer",
    "historia": "A TechSolutions nasceu de um sonho...",
    "missao": "Democratizar a tecnologia...",
    "visao": "Ser referência nacional...",
    "valores": ["Inovação", "Transparência", "Excelência"],
    "diferencial_destaque": "Suporte humanizado 24/7"
  },
  "seo_metadata": {
    "meta_title": "TechSolutions | Tecnologia para PMEs",
    "meta_description": "Conheça a história da TechSolutions...",
    "keywords": ["tecnologia", "pequenas empresas", "inovação"]
  }
}
```

**Response (erro):**
```json
{
  "success": false,
  "error": "Limite de requisições excedido. Aguarde 1 minuto."
}
```

---

## 🖥️ Componentes Vue

### AIContentGenerator.vue

Wizard de 4 etapas para geração de conteúdo.

```vue
<template>
  <AIContentGenerator
    title="Crie seu conteúdo"
    subtitle="Rascunhos de texto em segundos"
    api-base-url="/api/ai"
    @content-generated="handleGenerated"
    @content-applied="handleApplied"
  />
</template>

<script>
import AIContentGenerator from '@/shared/Components/AIContentGenerator.vue';

export default {
  components: { AIContentGenerator },
  methods: {
    handleGenerated(data) {
      console.log('Conteúdo gerado:', data);
    },
    handleApplied(data) {
      console.log('Conteúdo aplicado:', data);
      // Salvar no configurador
    }
  }
};
</script>
```

### AIContentRenderer.vue

Renderiza e permite edição do conteúdo gerado.

Usado internamente pelo AIContentGenerator, mas pode ser usado standalone:

```vue
<AIContentRenderer
  :content="generatedContent"
  :page-type="pageType"
  @update="handleUpdate"
/>
```

---

## 📄 Tipos de Página Suportados

| Tipo | Label | Schema Output |
|------|-------|---------------|
| `sobre_nos` | Sobre Nós | titulo_h1, historia, missao, visao, valores |
| `servicos` | Serviços | titulo_secao, servicos[], cta_text, garantia |
| `faq` | FAQ | perguntas[{pergunta, resposta, categoria}] |
| `portfolio` | Portfólio | projetos[{desafio, solucao, resultado}] |
| `blog_noticias` | Blog | artigos[{titulo, conteudo_html, tags}] |
| `vitrine_produtos` | Vitrine | produtos[{nome, chamada_venda, specs}] |
| `depoimentos` | Depoimentos | depoimentos[{texto, autor, nota}] |
| `contato` | Contato | info_contato, canais[], frase_final |
| `personalizada` | Personalizada | blocos[{tipo, conteudo}] |

Todos incluem `seo_metadata` com meta_title, meta_description e keywords.

---

## 🔒 Segurança

### Implementações

1. **CORS Restritivo**: Apenas domínios autorizados
2. **Rate Limiting**: 10 req/minuto por sessão
3. **Input Sanitization**: strip_tags + limite de caracteres
4. **API Key protegida**: Variável de ambiente ou config.secure.php
5. **Safety Settings**: Bloqueio de conteúdo impróprio no Gemini
6. **Guardrails no Prompt**: IA retorna erro para inputs maliciosos

### Checklist de Produção

- [ ] Substituir API Key de teste por produção
- [ ] Configurar CORS apenas para seu domínio
- [ ] Implementar rate limiting com Redis (escala)
- [ ] Monitorar uso da API no Google Cloud Console
- [ ] Configurar alertas de billing

---

## 💰 Custos Estimados

### Gemini 1.5 Flash (Janeiro 2026)

| Métrica | Preço |
|---------|-------|
| Input | $0.075 / 1M tokens |
| Output | $0.30 / 1M tokens |

### Estimativa por Geração

| Item | Tokens | Custo |
|------|--------|-------|
| System Instruction | ~500 | $0.0000375 |
| User Input | ~200 | $0.000015 |
| Output (JSON) | ~800 | $0.00024 |
| **Total/geração** | | **~$0.0003** |

### Projeção Mensal

| Sites/mês | Gerações | Custo API |
|-----------|----------|-----------|
| 10 | ~50 | ~$0.015 |
| 50 | ~250 | ~$0.075 |
| 200 | ~1000 | ~$0.30 |

**Custo extremamente baixo** - cada site novo custa menos de R$0.02 em API.

---

## 🚀 Próximos Passos

1. **Cache de Respostas**: Salvar gerações no banco para reutilizar
2. **Templates Salvos**: Usuário salva textos favoritos
3. **Bulk Generation**: Gerar todas as páginas de uma vez
4. **A/B Testing**: Gerar 2 versões e deixar usuário escolher
5. **Feedback Loop**: Usuário avalia qualidade → melhora prompts

---

## 📞 Suporte

Dúvidas técnicas? Abra uma issue ou contate o time de desenvolvimento.
