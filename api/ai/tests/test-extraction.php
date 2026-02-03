<?php
/**
 * ============================================
 * SCRIPT DE TESTE - Brand Data Extraction
 * Executa testes locais do endpoint de extração
 * ============================================
 * 
 * USO:
 * php api/ai/tests/test-extraction.php [cenário]
 * 
 * Cenários disponíveis:
 *   1 = Conversa Completa - Estúdio de Design
 *   2 = Conversa Curta - E-commerce de Moda
 *   3 = Conversa Técnica - Consultoria de TI
 */

// Configurar ambiente
define('SECURE_CONFIG_ACCESS', true);
require_once __DIR__ . '/../../config.secure.php';

// Carregar payloads de teste
$testPayloadsPath = __DIR__ . '/extraction-test-payloads.json';
$testData = json_decode(file_get_contents($testPayloadsPath), true);

if (!$testData) {
    die("❌ Erro ao carregar payloads de teste\n");
}

// Selecionar cenário
$scenario = isset($argv[1]) ? (int)$argv[1] : 1;
$scenarioIndex = $scenario - 1;

if (!isset($testData['test_scenarios'][$scenarioIndex])) {
    die("❌ Cenário inválido. Use 1, 2 ou 3.\n");
}

$testCase = $testData['test_scenarios'][$scenarioIndex];

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  🧪 TESTE DE EXTRAÇÃO - UNLI 2.0                             ║\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";
echo "║  Cenário: " . str_pad($testCase['name'], 50) . "║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Mostrar conversa resumida
echo "📝 CONVERSA DE TESTE:\n";
echo str_repeat("-", 60) . "\n";
foreach ($testCase['chat_history'] as $msg) {
    $role = $msg['role'] === 'assistant' ? '🤖 Unli' : '👤 Cliente';
    $content = mb_substr($msg['content'], 0, 80);
    if (mb_strlen($msg['content']) > 80) $content .= '...';
    echo "{$role}: {$content}\n";
}
echo str_repeat("-", 60) . "\n\n";

// Carregar extrator
require_once __DIR__ . '/../extract-brand-data.php';

// Simular a extração (chamada direta à função)
echo "⏳ Enviando para API Gemini...\n\n";

try {
    // Obter API Key
    $geminiApiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : getenv('GEMINI_API_KEY');
    
    if (!$geminiApiKey) {
        throw new Exception('GEMINI_API_KEY não configurada');
    }
    
    // Carregar prompt do extrator
    $extractorPromptPath = __DIR__ . '/../prompts/extractor-system-prompt.md';
    $extractorPrompt = file_exists($extractorPromptPath) 
        ? file_get_contents($extractorPromptPath) 
        : "Extraia os dados para JSON.";
    
    // Formatar conversa
    $conversationText = "=== INÍCIO DA CONVERSA ===\n\n";
    foreach ($testCase['chat_history'] as $msg) {
        $role = $msg['role'] === 'assistant' ? 'Consultor Unli' : 'Cliente';
        $conversationText .= "**{$role}:** {$msg['content']}\n\n";
    }
    $conversationText .= "=== FIM DA CONVERSA ===";
    
    // Chamar API
    $startTime = microtime(true);
    $rawJson = callGeminiExtraction($geminiApiKey, $extractorPrompt, $conversationText);
    $endTime = microtime(true);
    
    $duration = round(($endTime - $startTime) * 1000);
    echo "✅ Resposta recebida em {$duration}ms\n\n";
    
    // Parsear JSON
    $extractedData = parseExtractedJson($rawJson);
    
    // Mostrar resultados
    echo "📊 RESULTADO DA EXTRAÇÃO:\n";
    echo str_repeat("=", 60) . "\n\n";
    
    // Metadata
    $confidence = $extractedData['extractionMeta']['confidence'] ?? 'N/A';
    $confidenceEmoji = $confidence === 'high' ? '🟢' : ($confidence === 'medium' ? '🟡' : '🔴');
    echo "📈 Confiança: {$confidenceEmoji} {$confidence}\n";
    echo "📝 Campos Inferidos: " . implode(', ', $extractedData['extractionMeta']['inferredFields'] ?? []) . "\n";
    echo "⚠️  Campos Faltando: " . implode(', ', $extractedData['extractionMeta']['missingFields'] ?? []) . "\n\n";
    
    // Company Info
    echo "🏢 EMPRESA:\n";
    echo "   Nome: " . ($extractedData['companyInfo']['name'] ?? 'N/A') . "\n";
    echo "   Nicho: " . ($extractedData['companyInfo']['niche'] ?? 'N/A') . "\n";
    echo "   Público: " . ($extractedData['companyInfo']['targetAudience'] ?? 'N/A') . "\n\n";
    
    // Brand Core
    echo "💡 ESSÊNCIA DA MARCA:\n";
    echo "   Missão: " . ($extractedData['brandCore']['mission'] ?? 'N/A') . "\n";
    echo "   Valores: " . implode(', ', $extractedData['brandCore']['values'] ?? []) . "\n\n";
    
    // Differentiation
    echo "⭐ DIFERENCIAÇÃO:\n";
    echo "   USP: " . ($extractedData['differentiation']['usp'] ?? 'N/A') . "\n";
    echo "   Metodologia: " . ($extractedData['differentiation']['uniqueMethodology'] ?? 'N/A') . "\n\n";
    
    // AI Suggestions
    echo "✨ SUGESTÕES DA IA:\n";
    echo "   Tagline: \"" . ($extractedData['aiSuggestions']['suggestedTagline'] ?? 'N/A') . "\"\n";
    echo "   Tom de Voz: " . ($extractedData['aiSuggestions']['toneOfVoice'] ?? 'N/A') . "\n";
    echo "   Keywords: " . implode(', ', $extractedData['aiSuggestions']['suggestedKeywords'] ?? []) . "\n\n";
    
    echo str_repeat("=", 60) . "\n";
    echo "✅ TESTE CONCLUÍDO COM SUCESSO!\n\n";
    
    // Salvar JSON completo
    $outputPath = __DIR__ . '/last-extraction-result.json';
    file_put_contents($outputPath, json_encode($extractedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "💾 JSON completo salvo em: {$outputPath}\n\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n\n";
    exit(1);
}
