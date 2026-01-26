<?php
/**
 * Video Upload API
 * 
 * Handles video file uploads with plan-based limits
 * POST /api/upload/videos.php
 */

// CORS - Configuração segura
require_once __DIR__ . '/../lib/cors.php';
require_once __DIR__ . '/../lib/upload-helpers.php';

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

// Configuration - Dynamic limits based on plan
$baseUploadDir = __DIR__ . '/../../uploads/';
$allowedExtensions = ['mp4', 'webm', 'ogg', 'mov', 'avi'];

// Get plan type from request
$planType = $_POST['plan_type'] ?? 'basic'; // 'basic' or 'pro'
$maxFileSize = $planType === 'pro' ? 1024 * 1024 * 1024 : 50 * 1024 * 1024; // 1GB or 50MB

// Obter nome da empresa (obrigatório para organização dos arquivos)
$companyName = $_POST['company_name'] ?? '';

if (empty($companyName)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Nome da empresa é obrigatório para organização dos arquivos.'
    ]);
    exit();
}

// Sanitizar nome da pasta (remover caracteres especiais, acentos, etc)
$companyFolder = sanitizeFolderName($companyName);

// Estrutura: uploads/{empresa}/videos/
$uploadDir = $baseUploadDir . $companyFolder . '/videos/';

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
if (!isset($_FILES['videos']) || empty($_FILES['videos']['name'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Nenhum vídeo foi enviado.'
    ]);
    exit();
}

$files = $_FILES['videos'];
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
        
        $result = processVideoUpload($fileName, $fileTmpName, $fileSize, $fileError, $uploadDir, $allowedExtensions, $maxFileSize, $companyFolder);
        
        if ($result['success']) {
            $uploadedFiles[] = $result['file'];
        } else {
            $errors[] = $result['message'];
        }
    }
} else {
    // Single file
    $result = processVideoUpload($files['name'], $files['tmp_name'], $files['size'], $files['error'], $uploadDir, $allowedExtensions, $maxFileSize, $companyFolder);
    
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
        'message' => 'Nenhum vídeo foi enviado com sucesso.',
        'errors' => $errors
    ]);
}

/**
 * Process individual video upload
 */
function processVideoUpload($fileName, $fileTmpName, $fileSize, $fileError, $uploadDir, $allowedExtensions, $maxFileSize, $companyFolder) {
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
        $planName = $maxFileSize === (1024 * 1024 * 1024) ? 'Vídeo Pro (1GB)' : 'Vídeo Básico (50MB)';
        $sizeLimit = $maxFileSize === (1024 * 1024 * 1024) ? '1GB' : '50MB';
        
        return [
            'success' => false,
            'message' => "Arquivo {$fileName} excede o tamanho máximo do plano {$planName}: {$sizeLimit}"
        ];
    }
    
    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $fileTmpName);
    finfo_close($finfo);
    
    $allowedMimeTypes = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/x-msvideo'];
    if (!in_array($mimeType, $allowedMimeTypes)) {
        return [
            'success' => false,
            'message' => "Arquivo {$fileName} não é um vídeo válido."
        ];
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
                'url' => '/uploads/' . $companyFolder . '/videos/' . $uniqueFileName,
                'size' => $fileSize,
                'type' => $fileExtension,
                'company_folder' => $companyFolder
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
