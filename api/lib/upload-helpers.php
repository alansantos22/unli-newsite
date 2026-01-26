<?php
/**
 * ============================================
 * UPLOAD HELPERS - Funções utilitárias para upload
 * ============================================
 */

/**
 * Sanitiza o nome da pasta removendo caracteres especiais e acentos
 * Usado para criar pastas organizadas por empresa/cliente
 * 
 * @param string $name Nome a ser sanitizado
 * @return string Nome sanitizado
 */
function sanitizeFolderName($name) {
    // Converter para minúsculas
    $name = mb_strtolower($name, 'UTF-8');
    
    // Remover acentos
    $acentos = [
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ç' => 'c', 'ñ' => 'n'
    ];
    $name = strtr($name, $acentos);
    
    // Substituir espaços por hífens
    $name = preg_replace('/\s+/', '-', $name);
    
    // Remover caracteres especiais (manter apenas letras, números e hífens)
    $name = preg_replace('/[^a-z0-9\-]/', '', $name);
    
    // Remover hífens duplicados
    $name = preg_replace('/-+/', '-', $name);
    
    // Remover hífens no início e fim
    $name = trim($name, '-');
    
    // Se vazio, usar 'cliente-sem-nome'
    if (empty($name)) {
        $name = 'cliente-sem-nome';
    }
    
    return $name;
}

/**
 * Gera mensagem de erro de upload legível
 * 
 * @param int $errorCode Código de erro do upload
 * @return string Mensagem de erro
 */
function getUploadErrorMessage($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'Arquivo excede o tamanho máximo permitido.';
        case UPLOAD_ERR_PARTIAL:
            return 'Upload foi parcialmente concluído.';
        case UPLOAD_ERR_NO_FILE:
            return 'Nenhum arquivo foi enviado.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Pasta temporária não encontrada.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Falha ao escrever arquivo no disco.';
        case UPLOAD_ERR_EXTENSION:
            return 'Upload bloqueado por extensão PHP.';
        default:
            return 'Erro desconhecido no upload.';
    }
}
