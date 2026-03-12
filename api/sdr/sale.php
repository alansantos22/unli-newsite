<?php
/**
 * SDR Panel - Sales API
 * 
 * Endpoints:
 *   POST /api/sdr/sale.php?action=create              → Registrar nova venda
 *   POST /api/sdr/sale.php?action=mark_paid            → Marcar como pago
 *   POST /api/sdr/sale.php?action=send_onboarding_email → Enviar e-mail de onboarding
 *   POST /api/sdr/sale.php?action=update_notes          → Atualizar observações
 */

ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);

if (!defined('SECURE_CONFIG_ACCESS')) {
    define('SECURE_CONFIG_ACCESS', true);
}
$secureConfig = __DIR__ . '/../config.secure.php';
if (file_exists($secureConfig)) {
    require_once $secureConfig;
}

require_once __DIR__ . '/../lib/cors.php';

ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/../lib/pricing.php';
require_once __DIR__ . '/../lib/onboarding-helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
    exit;
}

// Autenticar SDR
$sdr = require_sdr_auth();

$action = $_GET['action'] ?? '';
$body = json_decode(file_get_contents('php://input'), true) ?: [];

switch ($action) {
    case 'create':
        handle_create($sdr, $body);
        break;
    case 'mark_paid':
        handle_mark_paid($sdr, $body);
        break;
    case 'send_onboarding_email':
        handle_send_email($sdr, $body);
        break;
    case 'update_notes':
        handle_update_notes($sdr, $body);
        break;
    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Ação inválida']);
}

// ============================================
// CRIAR VENDA
// ============================================

function handle_create($sdr, $body) {
    // Validar campos obrigatórios
    $required = ['customer_name', 'company_name', 'email', 'whatsapp'];
    $errors = [];
    foreach ($required as $field) {
        if (empty(trim($body[$field] ?? ''))) {
            $errors[] = "Campo '{$field}' é obrigatório";
        }
    }
    
    if (!filter_var($body['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'E-mail inválido';
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'errors' => $errors]);
        return;
    }
    
    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro de conexão com banco']);
        return;
    }
    
    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
    
    // Calcular preço no servidor se dados de seleção fornecidos
    $orderDetails = [];
    $totalAmount = 0;
    
    if (!empty($body['selection'])) {
        try {
            $cfg = load_pricing_config(__DIR__ . '/../pricing.json');
            $selection = normalize_selection($body['selection'], $cfg);
            $pricing = compute_price($selection, $cfg);
            
            $orderDetails = [
                'selection' => $selection,
                'pricing' => $pricing,
                'product' => $body['selection']['product'] ?? 'site_complete'
            ];
            
            // Usar preço à vista como referência
            $totalAmount = $pricing['avista'] ?? 0;
        } catch (Exception $e) {
            error_log("[SDR Sale] Erro ao calcular preço: " . $e->getMessage());
        }
    }
    
    // Se o SDR informou um valor manual, usar esse
    if (!empty($body['total_amount']) && is_numeric($body['total_amount'])) {
        $totalAmount = (float)$body['total_amount'];
    }
    
    // Gerar token de onboarding
    $token = generateOnboardingToken();
    
    // Status de pagamento
    $paymentStatus = ($body['payment_status'] ?? 'pending') === 'paid' ? 'paid' : 'pending';
    $paymentMethodManual = $body['payment_method_manual'] ?? null;
    
    // Sanitizar dados
    $customerName = htmlspecialchars(trim($body['customer_name']), ENT_QUOTES, 'UTF-8');
    $companyName = htmlspecialchars(trim($body['company_name']), ENT_QUOTES, 'UTF-8');
    $email = trim($body['email']);
    $whatsapp = preg_replace('/[^0-9+() -]/', '', trim($body['whatsapp']));
    $sdrNotes = trim($body['notes'] ?? '');

    // Verificar afiliado se hash fornecido
    $affiliateId = null;
    $affiliateHash = null;
    if (!empty($body['affiliate_hash'])) {
        $affiliateHash = preg_replace('/[^a-f0-9]/', '', strtolower(trim($body['affiliate_hash'])));
        if (strlen($affiliateHash) >= 8) {
            $affStmt = $pdo->prepare("SELECT id, commission_rate FROM {$prefix}affiliate_users WHERE affiliate_hash = ? AND is_active = 1 LIMIT 1");
            $affStmt->execute([$affiliateHash]);
            $affiliate = $affStmt->fetch(PDO::FETCH_ASSOC);
            if ($affiliate) {
                $affiliateId = (int)$affiliate['id'];
            } else {
                $affiliateHash = null;
            }
        } else {
            $affiliateHash = null;
        }
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO {$prefix}orders (
            customer_name, company_name, email, phone,
            order_details, total_amount,
            payment_status, payment_method_manual,
            onboarding_token, onboarding_status,
            sdr_id, sdr_notes,
            affiliate_id, affiliate_hash,
            created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendente', ?, ?, ?, ?, NOW(), NOW())
    ");
    
    $stmt->execute([
        $customerName,
        $companyName,
        $email,
        $whatsapp,
        json_encode($orderDetails, JSON_UNESCAPED_UNICODE),
        $totalAmount,
        $paymentStatus,
        $paymentMethodManual,
        $token,
        $sdr['sdr_id'],
        $sdrNotes,
        $affiliateId,
        $affiliateHash
    ]);
    
    $orderId = (int)$pdo->lastInsertId();

    // Se tem afiliado vinculado, criar registro de referral
    if ($affiliateId) {
        try {
            $commRate = $affiliate['commission_rate'] ?? 0.05;
            $commAmount = round($totalAmount * $commRate, 2);
            $refStmt = $pdo->prepare("
                INSERT INTO {$prefix}affiliate_referrals (affiliate_id, order_id, order_amount, commission_rate, commission_amount, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $refStmt->execute([$affiliateId, $orderId, $totalAmount, $commRate, $commAmount]);

            // Atualizar total_sales_amount do afiliado
            $pdo->prepare("UPDATE {$prefix}affiliate_users SET total_sales_amount = total_sales_amount + ? WHERE id = ?")
                ->execute([$totalAmount, $affiliateId]);
        } catch (Exception $e) {
            error_log("[SDR Sale] Erro ao registrar referral de afiliado: " . $e->getMessage());
        }
    }
    
    $baseUrl = defined('SITE_BASE_URL') ? SITE_BASE_URL : 'https://unli.com.br';
    $onboardingLink = "{$baseUrl}/setup?token={$token}";
    
    echo json_encode([
        'ok' => true,
        'order_id' => $orderId,
        'onboarding_token' => $token,
        'onboarding_link' => $onboardingLink,
        'payment_status' => $paymentStatus,
        'total_amount' => $totalAmount,
        'message' => 'Venda registrada com sucesso!'
    ], JSON_UNESCAPED_UNICODE);
}

// ============================================
// MARCAR COMO PAGO
// ============================================

function handle_mark_paid($sdr, $body) {
    $orderId = (int)($body['order_id'] ?? 0);
    
    if ($orderId <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'ID do pedido inválido']);
        return;
    }
    
    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro de conexão']);
        return;
    }
    
    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
    
    // Verificar se o pedido pertence a este SDR
    $stmt = $pdo->prepare("SELECT id, payment_status, onboarding_token, email, customer_name FROM {$prefix}orders WHERE id = ? AND sdr_id = ?");
    $stmt->execute([$orderId, $sdr['sdr_id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Pedido não encontrado']);
        return;
    }
    
    if ($order['payment_status'] === 'paid') {
        $baseUrl = defined('SITE_BASE_URL') ? SITE_BASE_URL : 'https://unli.com.br';
        echo json_encode([
            'ok' => true,
            'message' => 'Pedido já estava marcado como pago',
            'onboarding_link' => "{$baseUrl}/setup?token={$order['onboarding_token']}"
        ]);
        return;
    }
    
    // Atualizar para pago
    $paymentMethod = $body['payment_method_manual'] ?? null;
    
    $stmt = $pdo->prepare("
        UPDATE {$prefix}orders 
        SET payment_status = 'paid',
            payment_method_manual = COALESCE(?, payment_method_manual),
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$paymentMethod, $orderId]);
    
    $baseUrl = defined('SITE_BASE_URL') ? SITE_BASE_URL : 'https://unli.com.br';
    $onboardingLink = "{$baseUrl}/setup?token={$order['onboarding_token']}";
    
    echo json_encode([
        'ok' => true,
        'message' => 'Pedido marcado como pago!',
        'onboarding_link' => $onboardingLink,
        'onboarding_token' => $order['onboarding_token']
    ], JSON_UNESCAPED_UNICODE);
}

// ============================================
// ENVIAR E-MAIL DE ONBOARDING
// ============================================

function handle_send_email($sdr, $body) {
    $orderId = (int)($body['order_id'] ?? 0);
    
    if ($orderId <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'ID do pedido inválido']);
        return;
    }
    
    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro de conexão']);
        return;
    }
    
    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
    
    // Buscar pedido
    $stmt = $pdo->prepare("
        SELECT id, customer_name, email, onboarding_token, payment_status, order_details
        FROM {$prefix}orders 
        WHERE id = ? AND sdr_id = ?
    ");
    $stmt->execute([$orderId, $sdr['sdr_id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Pedido não encontrado']);
        return;
    }
    
    if ($order['payment_status'] !== 'paid') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Pagamento ainda não confirmado. Marque como pago antes de enviar o e-mail.']);
        return;
    }
    
    if (empty($order['onboarding_token'])) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Token de onboarding não gerado']);
        return;
    }
    
    // Montar link de onboarding
    $baseUrl = defined('SITE_BASE_URL') ? SITE_BASE_URL : 'https://unli.com.br';
    $magicLink = "{$baseUrl}/setup?token={$order['onboarding_token']}";
    
    // Extrair nome do plano dos detalhes
    $orderDetails = json_decode($order['order_details'] ?: '{}', true);
    $planName = $orderDetails['selection']['product'] ?? 'Site Completo';
    $planNameMap = [
        'landing' => 'Landing Page',
        'site_complete' => 'Site Completo PRO'
    ];
    $planLabel = $planNameMap[$planName] ?? $planName;
    
    // Enviar e-mail
    $sent = sendOnboardingEmail(
        $order['email'],
        $order['customer_name'],
        $magicLink,
        $order['id'],
        $planLabel,
        false
    );
    
    if ($sent) {
        // Registrar data de envio
        $stmt = $pdo->prepare("UPDATE {$prefix}orders SET onboarding_email_sent_at = NOW(), updated_at = NOW() WHERE id = ?");
        $stmt->execute([$orderId]);
        
        echo json_encode([
            'ok' => true,
            'message' => "E-mail de onboarding enviado para {$order['email']}!",
            'onboarding_link' => $magicLink
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'error' => 'Falha ao enviar e-mail. Verifique os logs do servidor.'
        ]);
    }
}

// ============================================
// ATUALIZAR OBSERVAÇÕES
// ============================================

function handle_update_notes($sdr, $body) {
    $orderId = (int)($body['order_id'] ?? 0);
    $notes = trim($body['notes'] ?? '');
    
    if ($orderId <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'ID do pedido inválido']);
        return;
    }
    
    $pdo = get_db_connection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Erro de conexão']);
        return;
    }
    
    $prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
    
    $stmt = $pdo->prepare("UPDATE {$prefix}orders SET sdr_notes = ?, updated_at = NOW() WHERE id = ? AND sdr_id = ?");
    $stmt->execute([$notes, $orderId, $sdr['sdr_id']]);
    
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Pedido não encontrado']);
        return;
    }
    
    echo json_encode(['ok' => true, 'message' => 'Observações atualizadas']);
}
