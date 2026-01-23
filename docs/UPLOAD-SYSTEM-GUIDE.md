# Sistema de Upload de Arquivos - Guia de Uso

## Componentes Criados

Foram criados três componentes de upload especializados na pasta `src/shared/Components`:

### 1. ImageUploader.vue
Componente para upload de imagens.

**Uso:**
```vue
<template>
  <ImageUploader
    label="Fotos dos Produtos"
    :required="true"
    :multiple="true"
    :maxSizeMB="5"
    v-model="images"
    @upload-complete="handleImageUpload"
  />
</template>

<script>
import { ImageUploader } from '@/shared/Components';

export default {
  components: { ImageUploader },
  data() {
    return {
      images: []
    };
  },
  methods: {
    handleImageUpload(files) {
      console.log('Imagens enviadas:', files);
    }
  }
};
</script>
```

**Props:**
- `label` (String): Título do campo
- `required` (Boolean): Se é obrigatório
- `multiple` (Boolean): Permite múltiplos arquivos
- `maxSizeMB` (Number): Tamanho máximo em MB (padrão: 5)
- `uploadText` (String): Texto customizado do botão
- `modelValue` (Array): v-model para os arquivos

**Eventos:**
- `@update:modelValue`: Atualiza v-model
- `@files-added`: Quando arquivos são adicionados
- `@file-removed`: Quando arquivo é removido
- `@upload-complete`: Quando upload é concluído
- `@upload-error`: Quando ocorre erro

**Formatos aceitos:** PNG, JPG, GIF, WebP, SVG

---

### 2. VideoUploader.vue
Componente para upload de vídeos.

**Uso:**
```vue
<template>
  <VideoUploader
    label="Vídeos Institucionais"
    :multiple="true"
    :maxSizeMB="100"
    v-model="videos"
    @upload-complete="handleVideoUpload"
  />
</template>

<script>
import { VideoUploader } from '@/shared/Components';

export default {
  components: { VideoUploader },
  data() {
    return {
      videos: []
    };
  },
  methods: {
    handleVideoUpload(files) {
      console.log('Vídeos enviados:', files);
    }
  }
};
</script>
```

**Props:** (mesmas do ImageUploader, com maxSizeMB padrão de 100)

**Formatos aceitos:** MP4, WebM, OGG, MOV, AVI

---

### 3. PDFUploader.vue
Componente para upload de PDFs.

**Uso:**
```vue
<template>
  <PDFUploader
    label="Catálogos e Documentos"
    :multiple="true"
    :maxSizeMB="10"
    v-model="pdfs"
    @upload-complete="handlePDFUpload"
  />
</template>

<script>
import { PDFUploader } from '@/shared/Components';

export default {
  components: { PDFUploader },
  data() {
    return {
      pdfs: []
    };
  },
  methods: {
    handlePDFUpload(files) {
      console.log('PDFs enviados:', files);
    }
  }
};
</script>
```

**Props:** (mesmas do ImageUploader, com maxSizeMB padrão de 10)

**Formato aceito:** PDF apenas

---

## APIs de Upload

Foram criadas três APIs na pasta `api/upload`:

### 1. /api/upload/images.php
- **Método:** POST
- **Campo:** `images[]` (multipart/form-data)
- **Limite:** 5MB por arquivo
- **Resposta:**
```json
{
  "success": true,
  "message": "Upload realizado com sucesso.",
  "files": [
    {
      "name": "foto.jpg",
      "filename": "abc123_1234567890.jpg",
      "url": "/uploads/images/abc123_1234567890.jpg",
      "size": 2048000,
      "type": "jpg"
    }
  ],
  "errors": []
}
```

### 2. /api/upload/videos.php
- **Método:** POST
- **Campo:** `videos[]` (multipart/form-data)
- **Limite:** 100MB por arquivo

### 3. /api/upload/pdfs.php
- **Método:** POST
- **Campo:** `pdfs[]` (multipart/form-data)
- **Limite:** 10MB por arquivo

---

## Estrutura de Pastas

```
uploads/
├── images/     # Imagens enviadas
├── videos/     # Vídeos enviados
└── pdfs/       # PDFs enviados
```

**Importante:** As pastas são criadas automaticamente pelas APIs se não existirem.

---

## Integração com Formulário

Para integrar os componentes no OnboardingWizard ou qualquer formulário:

```vue
<template>
  <div class="wizard-step">
    <h2>📸 Envie suas Mídias</h2>
    
    <!-- Imagens -->
    <ImageUploader
      label="Fotos da Empresa, Produtos ou Serviços"
      :multiple="true"
      v-model="formData.images"
      @upload-complete="handleImagesUploaded"
    />
    
    <!-- Vídeos -->
    <VideoUploader
      label="Vídeos (opcional)"
      :multiple="true"
      v-model="formData.videos"
      @upload-complete="handleVideosUploaded"
    />
    
    <!-- PDFs -->
    <PDFUploader
      label="Catálogos ou Documentos (opcional)"
      :multiple="true"
      v-model="formData.pdfs"
      @upload-complete="handlePDFsUploaded"
    />
  </div>
</template>

<script>
import { ImageUploader, VideoUploader, PDFUploader } from '@/shared/Components';

export default {
  components: {
    ImageUploader,
    VideoUploader,
    PDFUploader
  },
  data() {
    return {
      formData: {
        images: [],
        videos: [],
        pdfs: []
      }
    };
  },
  methods: {
    handleImagesUploaded(files) {
      console.log('Imagens prontas:', files);
      this.autoSave();
    },
    handleVideosUploaded(files) {
      console.log('Vídeos prontos:', files);
      this.autoSave();
    },
    handlePDFsUploaded(files) {
      console.log('PDFs prontos:', files);
      this.autoSave();
    },
    autoSave() {
      // Salvar progresso do formulário
    }
  }
};
</script>
```

---

## Estrutura JSON Final

Os arquivos enviados são incluídos no JSON final conforme o schema em `api/schemas/briefing-schema.json`:

```json
{
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
    "videos": [...],
    "pdfs": [...]
  }
}
```

Veja documentação completa em `docs/BRIEFING-JSON-STRUCTURE.md`.

---

## Recursos

### Drag & Drop
Todos os componentes suportam arrastar e soltar arquivos.

### Preview
- **Imagens:** Preview visual da imagem
- **Vídeos:** Thumbnail com botão play e duração
- **PDFs:** Ícone com badge e informações do arquivo

### Validação
- Tipo de arquivo
- Tamanho máximo
- MIME type (para segurança)

### Feedback Visual
- Barra de progresso durante upload
- Mensagens de erro
- Estado de hover e drag-over

### Responsivo
Todos os componentes são totalmente responsivos e adaptados para mobile.

---

## Segurança

✅ Validação de tipo de arquivo (extensão e MIME type)
✅ Validação de tamanho máximo
✅ Nomes de arquivo únicos (hash + timestamp)
✅ Proteção contra uploads maliciosos
✅ CORS configurado nas APIs

---

## Próximos Passos

1. ✅ Componentes de upload criados
2. ✅ APIs de upload implementadas
3. ✅ Estrutura de pastas criada
4. ✅ Schema JSON definido
5. ⏳ Integrar componentes no OnboardingWizard
6. ⏳ Adicionar categorização de arquivos
7. ⏳ Implementar compressão de imagens (opcional)
8. ⏳ Adicionar preview de PDFs (opcional)

---

## Suporte

Para dúvidas ou problemas, consulte:
- `docs/BRIEFING-JSON-STRUCTURE.md` - Estrutura do JSON
- `uploads/README.md` - Informações sobre uploads
- Código fonte dos componentes em `src/shared/Components/`
