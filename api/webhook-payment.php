<?php
/**
 * Webhook Example - Payment Gateway Integration
 * 
 * This file demonstrates how to integrate the onboarding system
 * with a payment gateway webhook (Mercado Pago, Stripe, etc.)
 * 
 * Place this file where your payment gateway can access it.
 * Configure the webhook URL in your payment gateway dashboard.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/onboarding-helpers.php';

// Log the webhook request
logWebhookRequest();

// Get webhook data
$webhookData = getWebhookData();

if (!$webhookData) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid webhook data']);
    exit();
}

try {
    // Connect to database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception('Database connection failed');
    }
    
    $conn->set_charset('utf8mb4');
    
    // Process based on payment status
    switch ($webhookData['status']) {
        case 'approved':
        case 'paid':
            handlePaymentApproved($conn, $webhookData);
            break;
            
        case 'refunded':
        case 'cancelled':
            handlePaymentCancelled($conn, $webhookData);
            break;
            
        default:
            // Ignore other statuses
            break;
    }
    
    $conn->close();
    
    // Return success response
    http_response_code(200);
    echo json_encode(['success' => true, 'processed' => true]);
    
} catch (Exception $e) {
    logError("Webhook error: " . $e->getMessage(), __FILE__);
    
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

/**
 * Handle approved payment
 */
function handlePaymentApproved($conn, $webhookData) {
    // Check if order already exists
    $existingOrder = findOrderByPaymentId($conn, $webhookData['payment_id']);
    
    if ($existingOrder) {
        // Order already processed
        return;
    }
    
    // Generate unique onboarding token
    $token = generateOnboardingToken();
    
    // Prepare order details
    $orderDetails = [
        'plan' => $webhookData['plan_name'] ?? 'Site Vitrine',
        'price' => $webhookData['amount'],
        'payment_method' => $webhookData['payment_method'] ?? 'unknown',
        'features' => $webhookData['features'] ?? []
    ];
    
    $orderDetailsJson = json_encode($orderDetails, JSON_UNESCAPED_UNICODE);
    
    // Insert order
    $stmt = $conn->prepare("
        INSERT INTO orders (
            customer_name,
            email,
            phone,
            order_details,
            total_amount,
            payment_status,
            payment_method,
            payment_id,
            onboarding_token,
            onboarding_status
        ) VALUES (?, ?, ?, ?, ?, 'paid', ?, ?, ?, 'pendente')
    ");
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement');
    }
    
    $stmt->bind_param(
        'ssssdsss',
        $webhookData['customer_name'],
        $webhookData['customer_email'],
        $webhookData['customer_phone'],
        $orderDetailsJson,
        $webhookData['amount'],
        $webhookData['payment_method'],
        $webhookData['payment_id'],
        $token
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to insert order: ' . $stmt->error);
    }
    
    $orderId = $conn->insert_id;
    $stmt->close();
    
    // Generate magic link
    $magicLink = getBaseUrl() . "/setup?token={$token}";
    
    // Send onboarding email
    $emailSent = sendOnboardingEmail(
        $webhookData['customer_email'],
        $webhookData['customer_name'],
        $magicLink,
        $orderId,
        $orderDetails['plan']
    );
    
    if (!$emailSent) {
        logError("Failed to send onboarding email to: {$webhookData['customer_email']}", __FILE__);
    }
    
    // Log success
    error_log("Order #{$orderId} created successfully. Token: {$token}");
}

/**
 * Handle cancelled/refunded payment
 */
function handlePaymentCancelled($conn, $webhookData) {
    $stmt = $conn->prepare("
        UPDATE orders 
        SET 
            payment_status = ?,
            updated_at = NOW()
        WHERE payment_id = ?
    ");
    
    $status = $webhookData['status'] === 'refunded' ? 'refunded' : 'failed';
    
    $stmt->bind_param('ss', $status, $webhookData['payment_id']);
    $stmt->execute();
    $stmt->close();
}

/**
 * Find existing order by payment ID
 */
function findOrderByPaymentId($conn, $paymentId) {
    $stmt = $conn->prepare("
        SELECT id 
        FROM orders 
        WHERE payment_id = ? 
        LIMIT 1
    ");
    
    $stmt->bind_param('s', $paymentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $exists = $result->num_rows > 0;
    $stmt->close();
    
    return $exists;
}

/**
 * Get webhook data based on payment gateway
 * Adapt this function to your specific payment gateway
 */
function getWebhookData() {
    // Get raw POST data
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);
    
    if (!$data) {
        return null;
    }
    
    // EXAMPLE: Mercado Pago format
    // Adapt this to your payment gateway structure
    if (isset($data['type']) && $data['type'] === 'payment') {
        return [
            'status' => $data['data']['status'] ?? 'unknown',
            'payment_id' => $data['data']['id'] ?? null,
            'amount' => $data['data']['transaction_amount'] ?? 0,
            'payment_method' => $data['data']['payment_method_id'] ?? 'unknown',
            'customer_name' => $data['data']['payer']['name'] ?? '',
            'customer_email' => $data['data']['payer']['email'] ?? '',
            'customer_phone' => $data['data']['payer']['phone']['number'] ?? '',
            'plan_name' => $data['data']['description'] ?? 'Site Vitrine',
            'features' => []
        ];
    }
    
    // EXAMPLE: Stripe format
    // Uncomment and adapt if using Stripe
    /*
    if (isset($data['type']) && $data['type'] === 'charge.succeeded') {
        return [
            'status' => 'approved',
            'payment_id' => $data['data']['object']['id'],
            'amount' => $data['data']['object']['amount'] / 100, // Stripe uses cents
            'payment_method' => $data['data']['object']['payment_method'],
            'customer_name' => $data['data']['object']['billing_details']['name'],
            'customer_email' => $data['data']['object']['billing_details']['email'],
            'customer_phone' => $data['data']['object']['billing_details']['phone'],
            'plan_name' => $data['data']['object']['description'],
            'features' => []
        ];
    }
    */
    
    // GENERIC format (for testing)
    // Use this structure when calling manually
    return [
        'status' => $data['status'] ?? 'unknown',
        'payment_id' => $data['payment_id'] ?? uniqid('test_'),
        'amount' => $data['amount'] ?? 0,
        'payment_method' => $data['payment_method'] ?? 'test',
        'customer_name' => $data['customer_name'] ?? '',
        'customer_email' => $data['customer_email'] ?? '',
        'customer_phone' => $data['customer_phone'] ?? '',
        'plan_name' => $data['plan_name'] ?? 'Site Vitrine',
        'features' => $data['features'] ?? []
    ];
}

/**
 * Get base URL of the application
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return "{$protocol}://{$host}";
}

/**
 * Log webhook request for debugging
 */
function logWebhookRequest() {
    $logFile = __DIR__ . '/logs/webhook-requests.log';
    $logDir = dirname($logFile);
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
    $rawData = file_get_contents('php://input');
    
    $logMessage = "\n========================================\n";
    $logMessage .= "[{$timestamp}] {$method} Request\n";
    $logMessage .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
    $logMessage .= "User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . "\n";
    $logMessage .= "Data: {$rawData}\n";
    $logMessage .= "========================================\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}
