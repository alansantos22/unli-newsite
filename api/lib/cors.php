<?php
/**
 * ============================================
 * CONFIGURAÇÃO DE CORS
 * Cross-Origin Resource Sharing
 * ============================================
 * 
 * Permite requisições apenas dos domínios autorizados
 */

// Domínios permitidos
$allowedOrigins = [
    'https://unli.com.br',
    'https://www.unli.com.br'
];

// Adicionar localhost para desenvolvimento (remova em produção)
if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
    $allowedOrigins[] = 'http://localhost:8080';
    $allowedOrigins[] = 'http://localhost:3000';
    $allowedOrigins[] = 'http://127.0.0.1:8080';
}

// Obter origin da requisição
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Verificar se origin está na lista permitida
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: {$origin}");
} else {
    // Bloquear outros origins
    header('Access-Control-Allow-Origin: https://unli.com.br');
}

// Headers CORS adicionais
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400'); // Cache preflight por 24h

// Responder preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
