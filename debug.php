<?php
/**
 * ============================================
 * DEBUG REAL - CREATE PREFERENCE
 * ============================================
 * 
 * Executa e captura TUDO que pode dar errado
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 DEBUG COMPLETO</h1>";

// 1. VERIFICAR ARQUIVOS E SINTAXE
echo "<h2>📁 1. Verificação de Arquivos</h2>";
$createPrefFile = __DIR__ . '/api/create_preference.php';
$configFile = __DIR__ . '/api/config.secure.php';

if (file_exists($createPrefFile)) {
    echo "✅ create_preference.php existe (" . filesize($createPrefFile) . " bytes)<br>";
    
    // TESTAR SINTAXE PHP
    $syntaxCheck = shell_exec("php -l \"$createPrefFile\" 2>&1");
    if (strpos($syntaxCheck, 'No syntax errors') !== false) {
        echo "✅ Sintaxe PHP válida<br>";
    } else {
        echo "❌ <strong>ERRO DE SINTAXE:</strong><br><pre>$syntaxCheck</pre>";
    }
} else {
    echo "❌ create_preference.php NÃO ENCONTRADO<br>";
}

if (file_exists($configFile)) {
    echo "✅ config.secure.php existe<br>";
} else {
    echo "❌ config.secure.php NÃO ENCONTRADO<br>";
}

// 2. TESTAR CONFIGURAÇÕES
echo "<h2>⚙️ 2. Configurações</h2>";
define('SECURE_CONFIG_ACCESS', true);

if (file_exists($configFile)) {
    require_once $configFile;
    echo "✅ Config carregado<br>";
    echo "MP_ACCESS_TOKEN: " . (defined('MP_ACCESS_TOKEN') ? '✅ ' . strlen(MP_ACCESS_TOKEN) . ' chars' : '❌ Faltando') . "<br>";
    echo "DEBUG_MODE: " . (defined('DEBUG_MODE') && DEBUG_MODE ? '✅ Ativo' : '⚠️ Inativo') . "<br>";
} else {
    echo "❌ Não foi possível carregar configurações<br>";
}

// 3. EXECUTAR create_preference.php DIRETAMENTE
echo "<h2>🧪 3. Execução Direta do PHP</h2>";

$testData = [
    'order_id' => 'DEBUG-' . time(),
    'payer_name' => 'Debug User', 
    'payer_email' => 'debug@test.com',
    'payment_type' => 'avista'
];

echo "Dados de teste: <code>" . json_encode($testData) . "</code><br><br>";

// SIMULAR AMBIENTE $_SERVER
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';
$_SERVER['HTTP_ORIGIN'] = 'http://localhost:8080';

// CAPTURAR TODOS OS OUTPUTS E ERROS
ob_start();
$errors = [];

// Custom error handler
set_error_handler(function($severity, $message, $file, $line) use (&$errors) {
    $errors[] = "[$severity] $message em $file:$line";
    return false;
});

// SIMULAR php://input
$originalInput = file_get_contents('php://input');
$tempInput = json_encode($testData);

// Criar stream temporário
$inputStream = fopen('php://temp', 'r+');
fwrite($inputStream, $tempInput);
rewind($inputStream);

echo "<strong>Executando create_preference.php...</strong><br>";

try {
    // EXECUTAR O ARQUIVO
    if (file_exists($createPrefFile)) {
        include $createPrefFile;
    } else {
        throw new Exception("Arquivo não encontrado");
    }
    
    echo "✅ Execução concluída sem exceções<br>";
    
} catch (Exception $e) {
    echo "❌ <strong>EXCEPTION:</strong> " . $e->getMessage() . "<br>";
    echo "📍 Em: " . $e->getFile() . ":" . $e->getLine() . "<br>";
} catch (Error $e) {
    echo "❌ <strong>PHP ERROR:</strong> " . $e->getMessage() . "<br>";
    echo "📍 Em: " . $e->getFile() . ":" . $e->getLine() . "<br>";
}

fclose($inputStream);
$output = ob_get_clean();

// Restaurar error handler
restore_error_handler();

// MOSTRAR ERROS CAPTURADOS
if (!empty($errors)) {
    echo "<h3>🚨 Erros PHP Capturados:</h3>";
    foreach ($errors as $error) {
        echo "• $error<br>";
    }
}

// MOSTRAR OUTPUT
echo "<h3>📤 Output Capturado (" . strlen($output) . " bytes):</h3>";
if (!empty($output)) {
    echo "<pre style='background:#f0f0f0;padding:10px;border-radius:5px;max-height:300px;overflow:auto;'>";
    echo htmlspecialchars($output);
    echo "</pre>";
    
    // TESTAR JSON
    $json = json_decode($output, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ <strong>JSON VÁLIDO!</strong><br>";
        echo "<pre>" . json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    } else {
        echo "❌ <strong>JSON INVÁLIDO:</strong> " . json_last_error_msg() . "<br>";
        echo "🔍 <strong>Primeiros 200 caracteres:</strong><br>";
        echo "<code>" . htmlspecialchars(substr($output, 0, 200)) . "</code><br>";
    }
} else {
    echo "⚠️ <strong>NENHUM OUTPUT</strong> - Isso pode indicar um erro fatal<br>";
}

// 4. TESTE VIA HTTP (COMO COMPARAÇÃO)
echo "<h2>🌐 4. Teste via HTTP (para comparação)</h2>";
$ch = curl_init('http://' . $_SERVER['HTTP_HOST'] . '/api/create_preference.php');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($testData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 10,
    CURLOPT_VERBOSE => true,
    CURLOPT_STDERR => fopen('php://temp', 'rw+')
]);

$httpResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "HTTP Status: <strong>$httpCode</strong><br>";
if ($curlError) {
    echo "❌ cURL Error: $curlError<br>";
} else {
    echo "Response: <pre>" . htmlspecialchars($httpResponse) . "</pre>";
}

echo "<hr>";
echo "<h3>💡 Diagnóstico:</h3>";
if (empty($output) && !empty($httpResponse)) {
    echo "• Output direto vazio, mas HTTP funciona → Problema na simulação php://input<br>";
} elseif (!empty($errors)) {
    echo "• Erros PHP detectados → Verifique os warnings/notices acima<br>";
} elseif ($output !== $httpResponse) {
    echo "• Diferença entre execução direta e HTTP → Problema de ambiente<br>";
} else {
    echo "• Execuções consistentes → Problema pode estar no frontend<br>";
}
?>