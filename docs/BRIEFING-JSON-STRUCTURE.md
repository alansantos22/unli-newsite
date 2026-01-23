# Estrutura JSON do Formulário de Briefing

Este documento define a estrutura JSON completa que será enviada via ticket após o usuário preencher o formulário de onboarding.

## Visão Geral

O JSON é dividido em seções que correspondem às etapas do formulário, além de metadados e informações sobre mídias enviadas.

## Estrutura Principal

```json
{
  "briefingSchema": { ... },
  "metadata": { ... }
}
```

## Seções do Briefing

### 1. Identidade Vital
Informações básicas da empresa/projeto.

```json
"identidadeVital": {
  "companyName": "Nome da empresa",
  "slogan": "Slogan ou frase de efeito",
  "logo": {
    "uploaded": true,
    "url": "/uploads/images/abc123.png",
    "filename": "abc123.png"
  },
  "hasNoLogo": false
}
```

### 2. Contato e Localização
Dados de contato e redes sociais.

```json
"contatoLocalizacao": {
  "whatsapp": "(11) 99999-9999",
  "instagram": "https://instagram.com/empresa",
  "facebook": "https://facebook.com/empresa",
  "email": "contato@empresa.com",
  "address": "Rua das Flores, 123",
  "noPhysicalLocation": false
}
```

### 3. Conteúdo
Textos e descrições do site.

```json
"conteudo": {
  "description": "Descrição breve do negócio",
  "services": [
    {
      "name": "Serviço 1",
      "description": "Descrição do serviço"
    }
  ],
  "about": "Texto sobre a empresa",
  "differentials": ["Diferencial 1", "Diferencial 2"]
}
```

### 4. Estilo Visual
Preferências de design e cores.

```json
"estiloVisual": {
  "primaryColor": "#00d4aa",
  "secondaryColor": "#2d3748",
  "style": "moderno",
  "inspiration": "URL ou descrição de inspiração"
}
```

### 5. Funcionalidades
Features e seções que o site deve ter.

```json
"funcionalidades": {
  "sections": ["hero", "services", "about", "contact"],
  "features": {
    "whatsappButton": true,
    "contactForm": true,
    "map": false,
    "gallery": true,
    "testimonials": false,
    "blog": false,
    "ecommerce": false
  }
}
```

### 6. Mídia e Conteúdo
Arquivos enviados pelo usuário.

```json
"midiaConteudo": {
  "images": [
    {
      "name": "foto-produto.jpg",
      "filename": "abc123_1234567890.jpg",
      "url": "/uploads/images/abc123_1234567890.jpg",
      "size": 2048000,
      "type": "jpg",
      "category": "produtos",
      "description": "Foto do produto principal"
    }
  ],
  "videos": [
    {
      "name": "apresentacao.mp4",
      "filename": "xyz789_1234567890.mp4",
      "url": "/uploads/videos/xyz789_1234567890.mp4",
      "size": 15728640,
      "type": "mp4",
      "duration": 120,
      "category": "institucional",
      "description": "Vídeo de apresentação"
    }
  ],
  "pdfs": [
    {
      "name": "catalogo.pdf",
      "filename": "def456_1234567890.pdf",
      "url": "/uploads/pdfs/def456_1234567890.pdf",
      "size": 5242880,
      "category": "documentos",
      "description": "Catálogo de produtos"
    }
  ]
}
```

### 7. SEO
Otimização para mecanismos de busca.

```json
"seo": {
  "keywords": ["palavra-chave-1", "palavra-chave-2"],
  "metaDescription": "Descrição para SEO",
  "targetAudience": "Público-alvo"
}
```

### 8. Observações
Informações adicionais e preferências.

```json
"observacoes": {
  "additionalInfo": "Informações extras",
  "preferences": "Preferências específicas",
  "deadline": "2026-02-15",
  "priority": "normal"
}
```

## Metadados

```json
"metadata": {
  "formVersion": "1.0.0",
  "submittedAt": "2026-01-22T15:30:00Z",
  "lastModified": "2026-01-22T15:25:00Z",
  "completionPercentage": 100,
  "status": "submitted"
}
```

### Status Possíveis
- `draft`: Rascunho em progresso
- `completed`: Formulário completo mas não enviado
- `submitted`: Formulário enviado com sucesso
- `in-review`: Em análise pela equipe
- `approved`: Aprovado e em desenvolvimento

## Categorias de Mídia

### Imagens
- `logo`: Logo da empresa
- `produtos`: Fotos de produtos
- `equipe`: Fotos da equipe
- `instalacoes`: Fotos do estabelecimento
- `portfolio`: Trabalhos realizados
- `geral`: Imagens gerais

### Vídeos
- `institucional`: Vídeos institucionais
- `produtos`: Demonstração de produtos
- `depoimentos`: Vídeos de clientes
- `tour`: Tour virtual
- `geral`: Vídeos gerais

### PDFs
- `documentos`: Documentos gerais
- `catalogos`: Catálogos de produtos
- `apresentacoes`: Apresentações
- `certificados`: Certificados e documentos oficiais

## Exemplo Completo

Veja o arquivo `briefing-schema.json` para a estrutura completa com todos os campos.

## Uso no Desenvolvimento

Este JSON será:
1. Montado progressivamente conforme o usuário preenche o formulário
2. Salvo automaticamente (auto-save) em cada alteração
3. Enviado completo ao final via API de submit
4. Anexado ao ticket no sistema de fila de chamados
5. Utilizado pela equipe de desenvolvimento para criar o site

## Validação

Antes do envio, todos os campos marcados como obrigatórios devem ser validados:
- `companyName` (obrigatório)
- `whatsapp` (obrigatório)
- `description` (obrigatório)
- `services` (mínimo 1, recomendado 3)

## Versionamento

A estrutura do schema é versionada para permitir evolução sem quebrar integrações existentes.

Versão atual: **1.0.0**
