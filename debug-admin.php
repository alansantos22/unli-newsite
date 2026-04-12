<?php
/**
 * Debug Admin Endpoints - Testa os endpoints admin DE VERDADE
 * Mostra erros reais em vez de "Erro interno"
 * 
 * Uso: https://unli.com.br/debug-admin.php?key=DebugAdmin2026
 * 
 * REMOVA APOS USO!
 */
$key = isset($_GET['key']) ? $_GET['key'] : '';
if ($key !== 'DebugAdmin2026') {
    http_response_code(403);
    die('Acesso negado. Use ?key=DebugAdmin2026');
}

// MOSTRAR TODOS OS ERROS
ini_set('display_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');
echo '<html><head><title>Debug Admin</title>';
echo '<style>body{font-family:monospace;padding:20px;background:#111;color:#eee;font-size:13px}';
echo '.ok{color:#0f0}.err{color:#f44}.warn{color:#ff0}.section{color:#0af;margin-top:20px;font-size:15px;font-weight:bold}';
echo 'pre{background:#222;padding:10px;border-radius:4px;overflow-x:auto;max-height:300px;overflow-y:auto}';
echo '</style></head><body>';
echo '<h2>Debug Admin Endpoints</h2>';

function msg($text, $class = 'ok') {
    echo "<div class=\"$class\">" . htmlspecialchars($text) . "</div>\n";
    ob_flush();
    flush();
}

// =============================================
// TESTE 1: Include chain
// =============================================
echo '<div class="section">1. Testando cadeia de includes</div>';

try {
    if (!defined('SECURE_CONFIG_ACCESS')) {
        define('SECURE_CONFIG_ACCESS', true);
    }
    $secureConfig = __DIR__ . '/api/config.secure.php';
    if (file_exists($secureConfig)) {
        require_once $secureConfig;
        msg('config.secure.php: OK');
    } else {
        msg('config.secure.php: NAO ENCONTRADO', 'warn');
    }
} catch (Exception $e) {
    msg('config.secure.php ERRO: ' . $e->getMessage(), 'err');
}

try {
    require_once __DIR__ . '/api/lib/cors.php';
    msg('cors.php: OK');
} catch (Exception $e) {
    msg('cors.php ERRO: ' . $e->getMessage(), 'err');
}

try {
    if (!defined('DB_CONFIG_ACCESS')) {
        define('DB_CONFIG_ACCESS', true);
    }
    require_once __DIR__ . '/api/db.config.php';
    msg('db.config.php: OK');
} catch (Exception $e) {
    msg('db.config.php ERRO: ' . $e->getMessage(), 'err');
}

try {
    require_once __DIR__ . '/api/lib/database.php';
    msg('database.php: OK');
} catch (Exception $e) {
    msg('database.php ERRO: ' . $e->getMessage(), 'err');
}

try {
    require_once __DIR__ . '/api/sdr/middleware.php';
    msg('sdr/middleware.php: OK');
} catch (Exception $e) {
    msg('sdr/middleware.php ERRO: ' . $e->getMessage(), 'err');
}

// =============================================
// TESTE 2: Admin middleware
// =============================================
echo '<div class="section">2. Testando admin/middleware.php</div>';

try {
    // middleware.php define require_admin_auth, admin_audit_log, etc
    require_once __DIR__ . '/api/admin/middleware.php';
    msg('admin/middleware.php: OK');
    
    // Verificar funcoes
    $funcs = array('require_admin_auth', 'admin_audit_log', 'check_admin_login_rate_limit');
    foreach ($funcs as $f) {
        if (function_exists($f)) {
            msg("  funcao $f(): OK");
        } else {
            msg("  funcao $f(): NAO DEFINIDA", 'err');
        }
    }
} catch (Exception $e) {
    msg('admin/middleware.php ERRO: ' . $e->getMessage(), 'err');
}

// =============================================
// TESTE 3: DB Connection + Queries
// =============================================
echo '<div class="section">3. Testando conexao e queries</div>';

try {
    $pdo = get_db_connection();
    if (!$pdo) {
        msg('get_db_connection() retornou NULL', 'err');
    } else {
        msg('Conexao: OK - ' . $pdo->query('SELECT VERSION()')->fetchColumn());
    }
} catch (Exception $e) {
    msg('DB ERRO: ' . $e->getMessage(), 'err');
}

$prefix = defined('DB_PREFIX') ? DB_PREFIX : '';

// =============================================
// TESTE 4: Simular list_clients query COMPLETA
// =============================================
echo '<div class="section">4. Simulando list_clients (mesma query do endpoint)</div>';

try {
    $page = 1;
    $limit = 10;
    $offset = 0;
    
    // Verificar customer_email
    $colCheck = $pdo->prepare("SHOW COLUMNS FROM {$prefix}orders LIKE 'customer_email'");
    $colCheck->execute();
    $hasEmail = $colCheck->fetch() ? true : false;
    msg('customer_email existe: ' . ($hasEmail ? 'SIM' : 'NAO'));
    
    $emailCol = $hasEmail ? 'o.customer_email,' : '';
    $sql = "SELECT o.id, o.customer_name, $emailCol o.company_name, o.total_amount, o.payment_status, o.payment_method_manual, o.sdr_id, o.affiliate_id, o.created_at, s.name as sdr_name, CONCAT(a.first_name, ' ', a.last_name) as affiliate_name FROM {$prefix}orders o LEFT JOIN {$prefix}sdr_users s ON s.id = o.sdr_id LEFT JOIN {$prefix}affiliate_users a ON a.id = o.affiliate_id ORDER BY o.created_at DESC LIMIT $limit OFFSET $offset";
    
    msg('SQL: ' . $sql);
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    msg('Resultado: ' . count($rows) . ' linhas');
    if (count($rows) > 0) {
        echo '<pre>' . htmlspecialchars(json_encode($rows[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
    }
} catch (Exception $e) {
    msg('ERRO list_clients: ' . $e->getMessage(), 'err');
}

// =============================================
// TESTE 5: Simular login (gerar token de teste)
// =============================================
echo '<div class="section">5. Testando JWT + Admin auth</div>';

try {
    // Buscar admin
    $stmt = $pdo->prepare("SELECT id, name, email, role, is_active FROM {$prefix}admin_users WHERE is_active = 1 LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if (!$admin) {
        msg('NENHUM admin ativo encontrado!', 'err');
    } else {
        msg('Admin encontrado: #' . $admin['id'] . ' ' . $admin['name'] . ' (' . $admin['role'] . ')');
        
        // Criar token JWT de teste
        $token = jwt_create(array(
            'admin_id' => (int)$admin['id'],
            'role' => $admin['role'],
            'scope' => 'admin'
        ), 3600);
        msg('JWT criado: ' . substr($token, 0, 50) . '...');
        
        // Validar token
        $payload = jwt_validate($token);
        if ($payload) {
            msg('JWT validado OK: admin_id=' . $payload['admin_id'] . ', role=' . $payload['role']);
        } else {
            msg('JWT validacao FALHOU!', 'err');
        }
    }
} catch (Exception $e) {
    msg('ERRO JWT: ' . $e->getMessage(), 'err');
}

// =============================================
// TESTE 6: Testar include dos arquivos admin
// =============================================
echo '<div class="section">6. Testando parse dos arquivos admin (sem executar)</div>';

$adminFiles = array(
    'auth.php',
    'dashboard.php', 
    'users.php',
    'finance.php',
    'gamification.php'
);

foreach ($adminFiles as $file) {
    $path = __DIR__ . '/api/admin/' . $file;
    $content = file_get_contents($path);
    
    // Check for PHP 7.4+ syntax issues
    $issues = array();
    
    // Arrow functions fn() => - PHP 7.4+
    if (preg_match('/\bfn\s*\(/', $content)) {
        $issues[] = 'Usa fn() arrow function (requer PHP 7.4+)';
    }
    
    // match() expression - PHP 8.0+
    if (preg_match('/\bmatch\s*\(/', $content)) {
        $issues[] = 'Usa match() expression (requer PHP 8.0+)';
    }
    
    // Null coalescing assignment ??= - PHP 7.4+
    if (strpos($content, '??=') !== false) {
        $issues[] = 'Usa ??= operador (requer PHP 7.4+)';
    }
    
    // Typed properties - PHP 7.4+
    if (preg_match('/\b(public|private|protected)\s+(int|string|float|bool|array)\s+\$/', $content)) {
        $issues[] = 'Usa typed properties (requer PHP 7.4+)';
    }
    
    // Trailing comma in function calls - PHP 7.3+
    if (preg_match('/,\s*\)/', $content)) {
        $issues[] = 'Trailing comma em chamada de funcao (requer PHP 7.3+)';
    }
    
    // Named arguments - PHP 8.0+
    if (preg_match('/\w+:\s*\$/', $content)) {
        $issues[] = 'Possivel named argument (requer PHP 8.0+)';
    }
    
    if (empty($issues)) {
        msg("$file: OK (compativel PHP 7.2)");
    } else {
        msg("$file: PROBLEMAS ENCONTRADOS!", 'err');
        foreach ($issues as $issue) {
            msg("  - $issue", 'err');
        }
    }
}

// =============================================
// TESTE 7: Fazer HTTP request real ao endpoint
// =============================================
echo '<div class="section">7. HTTP Request real ao auth.php (login)</div>';

try {
    $loginUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/api/admin/auth.php?action=login';
    msg('URL: ' . $loginUrl);
    
    // Tentar via curl
    if (function_exists('curl_init')) {
        $ch = curl_init($loginUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Origin: https://unli.com.br'
        ));
        // Enviar credenciais de teste (intencionalmente erradas para ver resposta)
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
            'email' => 'test@test.com',
            'password' => 'wrongpass'
        )));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            msg('cURL erro: ' . $curlError, 'err');
        } else {
            msg("HTTP $httpCode");
            msg('Response: ' . substr($response, 0, 500));
            
            if ($httpCode === 500) {
                msg('ENDPOINT RETORNOU 500 - O ERRO E REAL!', 'err');
            } elseif ($httpCode === 401) {
                msg('401 = Login endpoint FUNCIONA (credenciais erradas, esperado)', 'ok');
            }
        }
    } else {
        msg('cURL nao disponivel', 'warn');
    }
} catch (Exception $e) {
    msg('ERRO HTTP: ' . $e->getMessage(), 'err');
}

// =============================================
// TESTE 8: Testar users.php via HTTP
// =============================================
echo '<div class="section">8. HTTP Request real ao users.php (list_clients)</div>';

try {
    // Primeiro gerar token valido
    $stmt = $pdo->prepare("SELECT id, role FROM {$prefix}admin_users WHERE is_active = 1 LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        $token = jwt_create(array(
            'admin_id' => (int)$admin['id'],
            'role' => $admin['role'],
            'scope' => 'admin'
        ), 3600);
        
        $usersUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/api/admin/panel-mgmt.php?action=list_clients';
        msg('URL: ' . $usersUrl);
        msg('Token: ' . substr($token, 0, 40) . '...');
        
        if (function_exists('curl_init')) {
            $ch = curl_init($usersUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
                'Origin: https://unli.com.br'
            ));
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                msg('cURL erro: ' . $curlError, 'err');
            } else {
                msg("HTTP $httpCode");
                if ($httpCode === 200) {
                    msg('list_clients FUNCIONA!', 'ok');
                    $decoded = json_decode($response, true);
                    if ($decoded && isset($decoded['ok'])) {
                        msg('ok=' . ($decoded['ok'] ? 'true' : 'false') . ', dados=' . (isset($decoded['data']) ? count($decoded['data']) . ' registros' : 'N/A'));
                    }
                } else {
                    msg('FALHOU com HTTP ' . $httpCode, 'err');
                }
                echo '<pre>' . htmlspecialchars(substr($response, 0, 1000)) . '</pre>';
            }
        }
    } else {
        msg('Sem admin ativo para gerar token', 'err');
    }
} catch (Exception $e) {
    msg('ERRO: ' . $e->getMessage(), 'err');
}

// =============================================
// TESTE 9: Testar dashboard.php via HTTP
// =============================================
echo '<div class="section">9. HTTP Request real ao dashboard.php (overview)</div>';

try {
    if ($admin) {
        $token = jwt_create(array(
            'admin_id' => (int)$admin['id'],
            'role' => $admin['role'],
            'scope' => 'admin'
        ), 3600);
        
        $dashUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/api/admin/dashboard.php?action=overview';
        
        if (function_exists('curl_init')) {
            $ch = curl_init($dashUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
                'Origin: https://unli.com.br'
            ));
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                msg('cURL erro: ' . $curlError, 'err');
            } else {
                msg("HTTP $httpCode");
                if ($httpCode === 200) {
                    msg('dashboard overview FUNCIONA!', 'ok');
                } else {
                    msg('FALHOU com HTTP ' . $httpCode, 'err');
                }
                echo '<pre>' . htmlspecialchars(substr($response, 0, 1000)) . '</pre>';
            }
        }
    }
} catch (Exception $e) {
    msg('ERRO: ' . $e->getMessage(), 'err');
}

// =============================================
// TESTE 10: Ultimos erros REAIS no log PHP
// =============================================
echo '<div class="section">10. Ultimos erros REAIS no log PHP (excluindo mensagens de config)</div>';

$logPaths = array(
    '/home/u969754391/.logs/error_log_unli_com_br',
    ini_get('error_log')
);

foreach ($logPaths as $logPath) {
    if (!$logPath || !file_exists($logPath)) continue;
    
    msg('Log: ' . $logPath);
    
    $lines = file($logPath, FILE_IGNORE_NEW_LINES);
    if (!$lines) {
        msg('Nao foi possivel ler', 'warn');
        continue;
    }
    
    // Filtrar apenas erros reais (excluir mensagens de config com check mark)
    $realErrors = array();
    $lastN = array_slice($lines, -200);
    foreach ($lastN as $line) {
        // Pular mensagens de config (falsos positivos)
        if (strpos($line, 'Configura') !== false && strpos($line, 'carregadas') !== false) {
            continue;
        }
        // Pular linhas vazias
        if (trim($line) === '') continue;
        
        // Incluir linhas com indicadores de erro real
        if (strpos($line, 'Fatal') !== false 
            || strpos($line, 'Error') !== false 
            || strpos($line, 'Warning') !== false
            || strpos($line, 'Parse error') !== false
            || strpos($line, 'Uncaught') !== false
            || strpos($line, 'ADMIN-') !== false
            || strpos($line, 'Stack trace') !== false
            || strpos($line, 'thrown in') !== false
            || strpos($line, 'failed to') !== false
            || strpos($line, 'Cannot') !== false
            || strpos($line, 'Undefined') !== false
        ) {
            $realErrors[] = $line;
        }
    }
    
    if (empty($realErrors)) {
        msg('Nenhum erro REAL encontrado nas ultimas 200 linhas', 'warn');
        msg('(Todas as entradas sao mensagens de carregamento de config)', 'warn');
    } else {
        msg(count($realErrors) . ' erros reais encontrados:', 'err');
        echo '<pre>';
        foreach (array_slice($realErrors, -30) as $err) {
            echo htmlspecialchars($err) . "\n";
        }
        echo '</pre>';
    }
    
    break;
}

// =============================================
// BOTAO EXPORTAR
// =============================================
echo '<div class="section" style="margin-top:30px">Resultado completo acima. Faca screenshot ou copie.</div>';
echo '</body></html>';
