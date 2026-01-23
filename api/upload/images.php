<?php
/**
 * Image Upload API
 * 
 * Handles image file uploads
 * POST /api/upload/images.php
 */

// CORS - Configuração segura
require_once __DIR__ . '/../lib/cors.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido. Use POST.'
    ]);
    exit();
}

// Configuration
$uploadDir = __DIR__ . '/../../uploads/images/';
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
$maxFileSize = 3 * 1024 * 1024; // 3MB

// Create upload directory if it doesn't exist
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao criar diretório de upload.'
        ]);
        exit();
    }
}

// Check if files were uploaded
if (!isset($_FILES['images']) || empty($_FILES['images']['name'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Nenhuma imagem foi enviada.'
    ]);
    exit();
}

$files = $_FILES['images'];
$uploadedFiles = [];
$errors = [];

// Handle multiple files
if (is_array($files['name'])) {
    $fileCount = count($files['name']);
    
    for ($i = 0; $i < $fileCount; $i++) {
        $fileName = $files['name'][$i];
        $fileTmpName = $files['tmp_name'][$i];
        $fileSize = $files['size'][$i];
        $fileError = $files['error'][$i];
        
        $result = processImageUpload($fileName, $fileTmpName, $fileSize, $fileError, $uploadDir, $allowedExtensions, $maxFileSize);
        
        if ($result['success']) {
            $uploadedFiles[] = $result['file'];
        } else {
            $errors[] = $result['message'];
        }
    }
} else {
    // Single file
    $result = processImageUpload($files['name'], $files['tmp_name'], $files['size'], $files['error'], $uploadDir, $allowedExtensions, $maxFileSize);
    
    if ($result['success']) {
        $uploadedFiles[] = $result['file'];
    } else {
        $errors[] = $result['message'];
    }
}

// Response
if (count($uploadedFiles) > 0) {
    echo json_encode([
        'success' => true,
        'message' => 'Upload realizado com sucesso.',
        'files' => $uploadedFiles,
        'errors' => $errors
    ]);
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Nenhuma imagem foi enviada com sucesso.',
        'errors' => $errors
    ]);
}

/**
 * Process individual image upload
 */
function processImageUpload($fileName, $fileTmpName, $fileSize, $fileError, $uploadDir, $allowedExtensions, $maxFileSize) {
    // Check for upload errors
    if ($fileError !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'message' => "Erro no upload de {$fileName}: " . getUploadErrorMessage($fileError)
        ];
    }
    
    // Validate file extension
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedExtensions)) {
        return [
            'success' => false,
            'message' => "Tipo de arquivo não permitido: {$fileName}. Use: " . implode(', ', $allowedExtensions)
        ];
    }
    
    // Validate file size
    if ($fileSize > $maxFileSize) {
        return [
            'success' => false,
            'message' => "Arquivo {$fileName} excede o tamanho máximo de " . ($maxFileSize / 1024 / 1024) . "MB"
        ];
    }
    
    // Validate image
    if ($fileExtension !== 'svg') {
        $imageInfo = getimagesize($fileTmpName);
        if ($imageInfo === false) {
            return [
                'success' => false,
                'message' => "Arquivo {$fileName} não é uma imagem válida."
            ];
        }
    }
    
    // Generate unique filename
    $uniqueFileName = uniqid() . '_' . time() . '.' . $fileExtension;
    $targetPath = $uploadDir . $uniqueFileName;
    
    // Move uploaded file
    if (move_uploaded_file($fileTmpName, $targetPath)) {
        return [
            'success' => true,
            'file' => [
                'name' => $fileName,
                'filename' => $uniqueFileName,
                'url' => '/uploads/images/' . $uniqueFileName,
                'size' => $fileSize,
                'type' => $fileExtension
            ]
        ];
    } else {
        return [
            'success' => false,
            'message' => "Erro ao salvar arquivo {$fileName}."
        ];
    }
}

/**
 * Get human-readable upload error message
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
