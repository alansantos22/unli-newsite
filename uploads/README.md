# Pasta de Uploads

Esta pasta contém todos os arquivos enviados pelos usuários através do formulário de onboarding.

## Estrutura

```
uploads/
├── images/     # Imagens (PNG, JPG, GIF, WebP, SVG)
├── videos/     # Vídeos (MP4, WebM, OGG, MOV, AVI)
└── pdfs/       # Documentos PDF
```

## Permissões

As pastas devem ter permissão 755 para permitir uploads:

```bash
chmod 755 uploads
chmod 755 uploads/images
chmod 755 uploads/videos
chmod 755 uploads/pdfs
```

## Segurança

- Todos os arquivos são validados antes do upload
- Nomes de arquivo são gerados com hash único
- Tamanhos máximos são respeitados
- Tipos MIME são verificados

## Limites

- **Imagens**: máx. 5MB cada
- **Vídeos**: máx. 100MB cada
- **PDFs**: máx. 10MB cada
