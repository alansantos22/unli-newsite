<?php
/**
 * Helper Functions for Onboarding System
 */

/**
 * Generates a unique and secure token for onboarding
 * 
 * @return string 32-character hexadecimal token
 */
function generateOnboardingToken() {
    return md5(uniqid(rand(), true));
}

/**
 * Validates token format (32 hexadecimal characters)
 * 
 * @param string $token
 * @return bool
 */
function isValidTokenFormat($token) {
    return preg_match('/^[a-f0-9]{32}$/', $token);
}

/**
 * Sends the onboarding magic link email to customer
 * 
 * @param string $email Customer email
 * @param string $name Customer name
 * @param string $magicLink The unique onboarding URL
 * @param int $orderId Order ID
 * @param string $planName Plan name
 * @param bool $hasSpecialistOnboarding Whether customer bought specialist onboarding addon
 * @return bool Success status
 */
function sendOnboardingEmail($email, $name, $magicLink, $orderId, $planName = 'Site Vitrine', $hasSpecialistOnboarding = false) {
    // Load email template (api/emails/)
    $templatePath = __DIR__ . '/../emails/onboarding-magic-link.html';
    
    if (!file_exists($templatePath)) {
        error_log("Email template not found: {$templatePath}");
        return false;
    }
    
    $template = file_get_contents($templatePath);
    
    // Gerar seção de especialista se aplicável
    $specialistSection = '';
    if ($hasSpecialistOnboarding) {
        $whatsappNumber = '5511999999999'; // Substituir pelo número real
        $whatsappLink = "https://wa.me/{$whatsappNumber}?text=" . urlencode("Olá! Comprei o site com Atendimento com Especialista. Meu pedido é #{$orderId}. Gostaria de começar o onboarding personalizado.");
        
        $specialistSection = '
                            <div style="background: linear-gradient(135deg, #d4af37, #f4e4b0); border-radius: 12px; padding: 25px; margin: 25px 0; text-align: center; border: 2px solid #d4af37;">
                                <div style="font-size: 40px; margin-bottom: 10px;">👨‍💼</div>
                                <h3 style="margin: 0 0 10px 0; color: #1a1a2e; font-size: 20px;">Atendimento com Especialista Incluso!</h3>
                                <p style="margin: 0 0 20px 0; color: #333; font-size: 15px;">
                                    Você adquiriu o <strong>Atendimento com Especialista</strong>! Isso significa que você pode fazer todo o onboarding do seu site com um de nossos especialistas humanos.
                                </p>
                                <p style="margin: 0 0 15px 0; color: #333; font-size: 14px;">
                                    <strong>📋 Seu código do pedido:</strong> #' . $orderId . '<br>
                                    <strong>🔗 Link do seu formulário:</strong> <a href="' . $magicLink . '" style="color: #0066CC;">' . $magicLink . '</a>
                                </p>
                                <a href="' . $whatsappLink . '" style="display: inline-block; background: #25D366; color: white; text-decoration: none; padding: 14px 30px; border-radius: 8px; font-size: 16px; font-weight: 700; box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);">
                                    💬 Falar com Especialista no WhatsApp
                                </a>
                                <p style="margin: 15px 0 0 0; color: #666; font-size: 13px;">
                                    Informe seu código do pedido ao entrar em contato
                                </p>
                            </div>';
    }
    
    // Replace placeholders
    $emailBody = str_replace(
        ['{{CUSTOMER_NAME}}', '{{ORDER_ID}}', '{{PLAN_NAME}}', '{{ONBOARDING_LINK}}', '{{SPECIALIST_SECTION}}'],
        [$name, $orderId, $planName, $magicLink, $specialistSection],
        $template
    );
    
    $subject = '🚀 Vamos começar o seu site? (Acesso ao Painel de Criação)';
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Unli Sites <noreply@unli.com.br>\r\n";
    $headers .= "Reply-To: renatom@unli.com.br\r\n";
    
    // Send email
    $sent = mail($email, $subject, $emailBody, $headers);
    
    if (!$sent) {
        error_log("Failed to send onboarding email to: {$email}");
    }
    
    return $sent;
}

/**
 * Sanitizes and validates briefing data
 * 
 * @param array $data Raw briefing data
 * @return array Sanitized data
 */
function sanitizeBriefingData($data) {
    $sanitized = [];
    
    // String fields
    $stringFields = ['companyName', 'slogan', 'whatsapp', 'instagram', 'facebook', 'address', 'description', 'customDifferentials', 'primaryColor', 'designStyle', 'notes'];
    
    foreach ($stringFields as $field) {
        if (isset($data[$field])) {
            $sanitized[$field] = htmlspecialchars(trim($data[$field]), ENT_QUOTES, 'UTF-8');
        }
    }
    
    // Boolean fields
    $booleanFields = ['hasNoLogo', 'noPhysicalLocation'];
    
    foreach ($booleanFields as $field) {
        if (isset($data[$field])) {
            $sanitized[$field] = (bool) $data[$field];
        }
    }
    
    // Array fields
    if (isset($data['services']) && is_array($data['services'])) {
        $sanitized['services'] = array_map(function($service) {
            return [
                'name' => htmlspecialchars(trim($service['name'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'description' => htmlspecialchars(trim($service['description'] ?? ''), ENT_QUOTES, 'UTF-8')
            ];
        }, $data['services']);
    }
    
    if (isset($data['differentials']) && is_array($data['differentials'])) {
        $sanitized['differentials'] = array_map('htmlspecialchars', $data['differentials']);
    }
    
    // Logo URL (if exists)
    if (isset($data['logoUrl'])) {
        $sanitized['logoUrl'] = filter_var($data['logoUrl'], FILTER_SANITIZE_URL);
    }
    
    return $sanitized;
}

/**
 * Validates required fields in briefing data
 * 
 * @param array $data Briefing data
 * @return array ['valid' => bool, 'errors' => array]
 */
function validateBriefingData($data) {
    $errors = [];
    
    // Required fields
    $requiredFields = [
        'companyName' => 'Nome da empresa',
        'whatsapp' => 'WhatsApp',
        'description' => 'Descrição da empresa',
        'primaryColor' => 'Cor principal',
        'designStyle' => 'Estilo de design'
    ];
    
    foreach ($requiredFields as $field => $label) {
        if (empty($data[$field])) {
            $errors[] = "{$label} é obrigatório";
        }
    }
    
    // Validate at least one service
    if (empty($data['services']) || !is_array($data['services'])) {
        $errors[] = 'É necessário cadastrar pelo menos um serviço';
    } else {
        $hasValidService = false;
        foreach ($data['services'] as $service) {
            if (!empty($service['name'])) {
                $hasValidService = true;
                break;
            }
        }
        if (!$hasValidService) {
            $errors[] = 'É necessário preencher pelo menos um serviço';
        }
    }
    
    // Validate WhatsApp format (basic)
    if (!empty($data['whatsapp'])) {
        $phoneDigits = preg_replace('/\D/', '', $data['whatsapp']);
        if (strlen($phoneDigits) < 10 || strlen($phoneDigits) > 11) {
            $errors[] = 'Formato de WhatsApp inválido';
        }
    }
    
    // Validate color format
    if (!empty($data['primaryColor']) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $data['primaryColor'])) {
        $errors[] = 'Formato de cor inválido';
    }
    
    // Validate design style
    $validStyles = ['minimal', 'corporate', 'modern'];
    if (!empty($data['designStyle']) && !in_array($data['designStyle'], $validStyles)) {
        $errors[] = 'Estilo de design inválido';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Gets order by token
 * 
 * @param mysqli $conn Database connection
 * @param string $token Onboarding token
 * @return array|null Order data or null if not found
 */
function getOrderByToken($conn, $token) {
    $stmt = $conn->prepare("
        SELECT 
            id,
            customer_name,
            email,
            phone,
            order_details,
            onboarding_status,
            briefing_data,
            created_at,
            completed_at
        FROM orders 
        WHERE onboarding_token = ? 
        LIMIT 1
    ");
    
    if (!$stmt) {
        return null;
    }
    
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    $order = $result->fetch_assoc();
    $stmt->close();
    
    // Parse JSON fields
    if (!empty($order['briefing_data'])) {
        $order['briefing_data'] = json_decode($order['briefing_data'], true);
    }
    if (!empty($order['order_details'])) {
        $order['order_details'] = json_decode($order['order_details'], true);
    }
    
    return $order;
}

/**
 * Checks if token is expired (30 days)
 * 
 * @param string $createdAt Order creation timestamp
 * @return bool True if expired
 */
function isTokenExpired($createdAt) {
    $created = strtotime($createdAt);
    $now = time();
    $daysSinceCreation = ($now - $created) / (60 * 60 * 24);
    
    return $daysSinceCreation > 30;
}

/**
 * Logs an error to file
 * 
 * @param string $message Error message
 * @param string $file File where error occurred
 */
function logError($message, $file = '') {
    $logFile = __DIR__ . '/logs/onboarding-errors.log';
    $logDir = dirname($logFile);
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$file}: {$message}\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}
