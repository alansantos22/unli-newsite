<?php
/**
 * Onboarding Token Validation API
 * 
 * Validates the magic link token and returns order information
 * GET /api/onboarding/validate.php?token={token}
 */

// CORS - Configuração segura
require_once __DIR__ . '/../lib/cors.php';

header('Content-Type: application/json');

// Carregar configuração do banco de dados
require_once __DIR__ . '/../lib/database.php';

// ============================================
// DEFINIÇÃO DOS CAMPOS DO ONBOARDING
// (Espelhado do frontend para consistência)
// ============================================

$ONBOARDING_FIELDS = [
    'identity' => [
        ['id' => 'companyName', 'required' => true],
        ['id' => 'businessType', 'required' => true],
        ['id' => 'frase', 'required' => false],
        ['id' => 'primaryColor', 'required' => false],
        ['id' => 'secondaryColor', 'required' => false],
        ['id' => 'voiceTone', 'required' => false],
        ['id' => 'logo', 'required' => false],
        ['id' => 'hasNoLogo', 'required' => false]
    ],
    'contact' => [
        ['id' => 'whatsapp', 'required' => true],
        ['id' => 'additionalPhones', 'required' => false],
        ['id' => 'email', 'required' => false],
        ['id' => 'instagram', 'required' => false],
        ['id' => 'facebook', 'required' => false],
        ['id' => 'linkedin', 'required' => false],
        ['id' => 'youtube', 'required' => false],
        ['id' => 'tiktok', 'required' => false],
        ['id' => 'hasPhysicalLocation', 'required' => false],
        ['id' => 'addressCep', 'required' => false],
        ['id' => 'addressStreet', 'required' => false],
        ['id' => 'addressNumber', 'required' => false],
        ['id' => 'addressComplement', 'required' => false],
        ['id' => 'addressNeighborhood', 'required' => false],
        ['id' => 'addressCity', 'required' => false],
        ['id' => 'addressState', 'required' => false],
        ['id' => 'businessHours', 'required' => false]
    ],
    'about' => [
        ['id' => 'aboutSectionTitle', 'required' => false],
        ['id' => 'companyBio', 'required' => false],
        ['id' => 'foundingYear', 'required' => false],
        ['id' => 'founders', 'required' => false],
        ['id' => 'aboutImage', 'required' => false],
        ['id' => 'companyHighlights', 'required' => false],
        ['id' => 'showMissionVision', 'required' => false],
        ['id' => 'mission', 'required' => false],
        ['id' => 'vision', 'required' => false],
        ['id' => 'values', 'required' => false]
    ],
    'services' => [
        ['id' => 'servicesSectionTitle', 'required' => false],
        ['id' => 'servicesIntro', 'required' => false],
        ['id' => 'services', 'required' => false],
        ['id' => 'hasGuarantee', 'required' => false],
        ['id' => 'guaranteeDetails', 'required' => false]
    ],
    'faq' => [
        ['id' => 'faqItems', 'required' => false]
    ],
    'finalization' => [
        ['id' => 'additionalNotes', 'required' => false],
        ['id' => 'urgency', 'required' => false],
        ['id' => 'inspirationUrls', 'required' => false]
    ]
];

/**
 * Verifica se um valor é considerado "preenchido"
 */
function isFieldFilled($value) {
    if ($value === null || $value === '') return false;
    if (is_array($value) && count($value) === 0) return false;
    if (is_bool($value)) return true; // Booleans são sempre considerados preenchidos
    return true;
}

/**
 * Gera o checklist de campos preenchidos baseado no briefing
 */
function generateFieldChecklist($briefingData) {
    global $ONBOARDING_FIELDS;
    
    $checklist = [];
    
    foreach ($ONBOARDING_FIELDS as $stepId => $fields) {
        $stepChecklist = [];
        
        foreach ($fields as $field) {
            $fieldId = $field['id'];
            $value = $briefingData[$fieldId] ?? null;
            $isFilled = isFieldFilled($value);
            
            // Mapear para os status do frontend
            // empty, answered, skipped, confirmed
            $status = 'empty';
            if ($isFilled) {
                $status = 'answered';
            }
            
            $stepChecklist[$fieldId] = [
                'status' => $status,
                'required' => $field['required'],
                'hasValue' => $isFilled
            ];
        }
        
        $checklist[$stepId] = $stepChecklist;
    }
    
    return $checklist;
}

/**
 * Calcula estatísticas de progresso
 */
function calculateProgressStats($fieldChecklist) {
    $totalFields = 0;
    $filledFields = 0;
    $requiredTotal = 0;
    $requiredFilled = 0;
    $stepProgress = [];
    
    foreach ($fieldChecklist as $stepId => $fields) {
        $stepTotal = 0;
        $stepFilled = 0;
        
        foreach ($fields as $fieldId => $fieldData) {
            $totalFields++;
            $stepTotal++;
            
            if ($fieldData['hasValue']) {
                $filledFields++;
                $stepFilled++;
            }
            
            if ($fieldData['required']) {
                $requiredTotal++;
                if ($fieldData['hasValue']) {
                    $requiredFilled++;
                }
            }
        }
        
        $stepProgress[$stepId] = [
            'total' => $stepTotal,
            'filled' => $stepFilled,
            'percent' => $stepTotal > 0 ? round(($stepFilled / $stepTotal) * 100) : 0
        ];
    }
    
    return [
        'totalFields' => $totalFields,
        'filledFields' => $filledFields,
        'overallPercent' => $totalFields > 0 ? round(($filledFields / $totalFields) * 100) : 0,
        'requiredTotal' => $requiredTotal,
        'requiredFilled' => $requiredFilled,
        'requiredComplete' => $requiredTotal === $requiredFilled,
        'stepProgress' => $stepProgress
    ];
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido. Use GET.'
    ]);
    exit();
}

// Get token from query string
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (empty($token)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Token não fornecido.'
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
    
    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("
        SELECT 
            id,
            email,
            customer_name,
            order_details,
            onboarding_status,
            briefing_data,
            current_step,
            created_at
        FROM " . $prefix . "orders 
        WHERE onboarding_token = ? 
        LIMIT 1
    ");
    
    if (!$stmt) {
        $errorDetail = $conn->error;
        throw new Exception('Erro ao preparar consulta: ' . $errorDetail);
    }
    
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Token inválido ou expirado. Verifique o link no seu e-mail.'
        ]);
        exit();
    }
    
    $order = $result->fetch_assoc();
    
    // Check if token is too old (optional: 30 days expiration)
    $created = strtotime($order['created_at']);
    $now = time();
    $daysSinceCreation = ($now - $created) / (60 * 60 * 24);
    
    if ($daysSinceCreation > 30) {
        http_response_code(410);
        echo json_encode([
            'success' => false,
            'message' => 'Este link expirou. Entre em contato com nosso suporte.'
        ]);
        exit();
    }
    
    // Parse briefing data if exists
    $briefingData = null;
    if (!empty($order['briefing_data'])) {
        $briefingData = json_decode($order['briefing_data'], true);
    }
    
    // Parse order details if exists
    $orderDetails = null;
    if (!empty($order['order_details'])) {
        $orderDetails = json_decode($order['order_details'], true);
    }
    
    // ============================================
    // GERAR CHECKLIST DE CAMPOS PREENCHIDOS
    // ============================================
    
    $fieldChecklist = generateFieldChecklist($briefingData);
    $progressStats = calculateProgressStats($fieldChecklist);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'orderId' => $order['id'],
        'customerName' => $order['customer_name'],
        'email' => $order['email'],
        'status' => $order['onboarding_status'],
        'briefing' => $briefingData,
        'orderDetails' => $orderDetails,
        'currentStep' => isset($order['current_step']) ? intval($order['current_step']) : 0,
        'fieldChecklist' => $fieldChecklist,
        'progressStats' => $progressStats,
        'message' => $order['onboarding_status'] === 'concluido' 
            ? 'Briefing já foi concluído.' 
            : 'Token válido. Pode prosseguir.'
    ]);
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
