<?php
/**
 * Onboarding Final Submission API
 * 
 * Submits the completed briefing and marks order as completed
 * POST /api/onboarding/submit.php
 */

// CORS - Configuração segura
require_once __DIR__ . '/../lib/cors.php';

header('Content-Type: application/json');

// Carregar configuração do banco de dados e helpers
require_once __DIR__ . '/../lib/database.php';
require_once __DIR__ . '/../lib/upload-helpers.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido. Use POST.'
    ]);
    exit();
}

// Get POST data
$token = isset($_POST['token']) ? trim($_POST['token']) : '';
$briefingJson = isset($_POST['briefing']) ? trim($_POST['briefing']) : '';

if (empty($token)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Token não fornecido.'
    ]);
    exit();
}

if (empty($briefingJson)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Dados do briefing não fornecidos.'
    ]);
    exit();
}

// Validate JSON
$briefingData = json_decode($briefingJson, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'JSON inválido.'
    ]);
    exit();
}

try {
    // Connect to database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception('Erro de conexão com banco de dados.');
    }
    
    $conn->set_charset('utf8mb4');
    
    // Obter prefixo da tabela
    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
    
    // Check if token exists and status is not already 'concluido'
    $stmt = $conn->prepare("
        SELECT id, onboarding_status, email, customer_name 
        FROM " . $prefix . "orders 
        WHERE onboarding_token = ? 
        LIMIT 1
    ");
    
    if (!$stmt) {
        throw new Exception('Erro ao preparar consulta.');
    }
    
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Token inválido.'
        ]);
        exit();
    }
    
    $order = $result->fetch_assoc();
    
    // Don't allow submitting if already completed
    if ($order['onboarding_status'] === 'concluido') {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'O briefing já foi finalizado anteriormente.'
        ]);
        exit();
    }
    
    // Obter nome da empresa do briefing para organização dos arquivos
    $companyName = $briefingData['companyName'] ?? $order['customer_name'] ?? 'cliente-sem-nome';
    $companyFolder = sanitizeFolderName($companyName);
    
    // Handle logo upload if present
    $logoPath = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $baseUploadDir = __DIR__ . '/../../uploads/';
        $uploadDir = $baseUploadDir . $companyFolder . '/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/svg+xml', 'image/gif'];
        $fileType = $_FILES['logo']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception('Tipo de arquivo inválido para logo.');
        }
        
        // Validate file size (5MB max)
        if ($_FILES['logo']['size'] > 5 * 1024 * 1024) {
            throw new Exception('Logo muito grande. Máximo 5MB.');
        }
        
        // Generate unique filename
        $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
            $logoPath = '/uploads/' . $companyFolder . '/' . $filename;
        }
    }
    
    // Add logo path and company folder to briefing data
    if ($logoPath) {
        $briefingData['logoUrl'] = $logoPath;
    }
    $briefingData['companyFolder'] = $companyFolder;
    
    // Re-encode with logo path
    $briefingJson = json_encode($briefingData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
    // Update order with completed briefing
    $updateStmt = $conn->prepare("
        UPDATE " . $prefix . "orders 
        SET 
            briefing_data = ?,
            onboarding_status = 'concluido',
            completed_at = NOW(),
            updated_at = NOW()
        WHERE onboarding_token = ?
    ");
    
    if (!$updateStmt) {
        throw new Exception('Erro ao preparar atualização.');
    }
    
    $updateStmt->bind_param('ss', $briefingJson, $token);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Erro ao finalizar briefing.');
    }
    
    // ============================================
    // CRIAR TICKET NO FILA CHAMADOS COM O BRIEFING
    // ============================================
    require_once __DIR__ . '/../lib/fila-chamados.php';
    
    // Carregar configuração segura para ter acesso às constantes do Fila Chamados
    if (!defined('SECURE_CONFIG_ACCESS')) {
        define('SECURE_CONFIG_ACCESS', true);
    }
    $secureConfigPath = __DIR__ . '/../config.secure.php';
    if (file_exists($secureConfigPath)) {
        require_once $secureConfigPath;
    }
    
    $ticketResult = createTicketForOnboarding($order, $briefingData);
    
    if ($ticketResult['success']) {
        error_log(sprintf(
            '✅ ONBOARDING SUBMIT: Ticket criado #%s para pedido #%s',
            $ticketResult['ticket_id'],
            $order['id']
        ));
    } else {
        error_log(sprintf(
            '⚠️ ONBOARDING SUBMIT: Falha ao criar ticket para pedido #%s - %s',
            $order['id'],
            $ticketResult['error'] ?? 'Erro desconhecido'
        ));
    }
    
    // Send confirmation email to customer
    sendCompletionEmail($order['email'], $order['customer_name']);
    
    // Send notification to admin/team
    notifyTeam($order['id'], $briefingData);
    
    echo json_encode([
        'success' => true,
        'message' => 'Briefing enviado com sucesso! Nossa equipe já recebeu suas informações.',
        'orderId' => $order['id'],
        'ticketCreated' => $ticketResult['success'] ?? false
    ]);
    
    $stmt->close();
    $updateStmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao enviar: ' . $e->getMessage()
    ]);
}

/**
 * Send completion confirmation email to customer
 */
function sendCompletionEmail($email, $name) {
    $subject = '✅ Briefing Recebido - Seu site está em produção!';
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
            .cta-button { display: inline-block; background: #0066CC; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 Briefing Recebido!</h1>
            </div>
            <div class='content'>
                <p>Olá, <strong>{$name}</strong>!</p>
                
                <p>Recebemos todas as suas informações e já começamos a trabalhar no seu site!</p>
                
                <h3>📋 Próximos Passos:</h3>
                <ul>
                    <li>✅ Briefing analisado pela equipe</li>
                    <li>🎨 Design inicial (2-3 dias)</li>
                    <li>⚙️ Desenvolvimento (3-4 dias)</li>
                    <li>🚀 Revisão e publicação</li>
                </ul>
                
                <p><strong>Previsão de entrega: 5-7 dias úteis</strong></p>
                
                <p>Você receberá um e-mail com o preview do site assim que estiver pronto para revisão!</p>
                
                <p>Se tiver alguma dúvida, responda este e-mail. Estamos aqui para ajudar!</p>
                
                <p>
                    Atenciosamente,<br>
                    <strong>Equipe Unli</strong>
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Unli Sites <noreply@unli.com.br>\r\n";
    
    mail($email, $subject, $message, $headers);
}

/**
 * Notify internal team about new completed briefing
 */
function notifyTeam($orderId, $briefingData) {
    $adminEmail = 'renatom@unli.com.br'; // Change to your admin email
    
    $subject = '🚨 Novo Briefing Completo - Pedido #' . $orderId;
    
    $companyName = isset($briefingData['companyName']) ? $briefingData['companyName'] : 'N/A';
    $whatsapp = isset($briefingData['whatsapp']) ? $briefingData['whatsapp'] : 'N/A';
    $designStyle = isset($briefingData['designStyle']) ? $briefingData['designStyle'] : 'N/A';
    
    $message = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2>Novo Briefing Completo!</h2>
        <p><strong>Pedido:</strong> #{$orderId}</p>
        <p><strong>Empresa:</strong> {$companyName}</p>
        <p><strong>WhatsApp:</strong> {$whatsapp}</p>
        <p><strong>Estilo:</strong> {$designStyle}</p>
        
        <p><a href='https://seu-painel.com/orders/{$orderId}' style='background:#0066CC; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Ver Briefing Completo</a></p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Sistema Unli <sistema@unli.com.br>\r\n";
    
    mail($adminEmail, $subject, $message, $headers);
}
