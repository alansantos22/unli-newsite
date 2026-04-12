<?php
/**
 * ============================================
 * MIGRATION: Reset de Senha dos Afiliados
 * ============================================
 *
 * Acesse: https://unli.com.br/migrate-affiliate-reset.php?password=AffReset2026
 *
 * O que faz:
 *   - Adiciona coluna reset_token em affiliate_users
 *   - Adiciona coluna reset_token_expires em affiliate_users
 *   - Cria índice idx_affiliate_reset_token
 *
 * ⚠️ APAGUE ESTE ARQUIVO APÓS EXECUTAR!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================
// PROTEÇÃO COM SENHA
// ============================================
$MIGRATION_PASSWORD = 'AffReset2026';

if (!isset($_GET['password']) || $_GET['password'] !== $MIGRATION_PASSWORD) {
    http_response_code(401);
    die('🚫 Acesso negado! Use: migrate-affiliate-reset.php?password=AffReset2026');
}

// ============================================
// CONEXÃO
// ============================================
define('DB_CONFIG_ACCESS', true);
$config_path = __DIR__ . '/api/db.config.php';

if (!file_exists($config_path)) {
    die('❌ Arquivo api/db.config.php não encontrado!');
}

require_once $config_path;

try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('❌ Erro de conexão: ' . htmlspecialchars($e->getMessage()));
}

// ============================================
// HELPERS
// ============================================
$logs = [];

function log_msg($msg, $type = 'info') {
    global $logs;
    if ($type === 'success')      $icon = '✅';
    elseif ($type === 'warning') $icon = '⚠️';
    elseif ($type === 'error')   $icon = '❌';
    else                         $icon = 'ℹ️';
    $logs[] = ['type' => $type, 'msg' => "{$icon} {$msg}"];
}

function column_exists($pdo, $table, $column) {
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
        $stmt->execute([$column]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

function index_exists($pdo, $table, $indexName) {
    try {
        $stmt = $pdo->prepare("SHOW INDEX FROM `{$table}` WHERE Key_name = ?");
        $stmt->execute([$indexName]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

function table_exists($pdo, $table) {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$table]);
    return $stmt->rowCount() > 0;
}

$prefix = defined('DB_PREFIX') ? DB_PREFIX : '';
$tbl    = $prefix . 'affiliate_users';

// ============================================
// VERIFICAR TABELA-BASE
// ============================================
log_msg("Verificando tabela {$tbl}...");

$abort = false;
if (!table_exists($pdo, $tbl)) {
    log_msg("Tabela {$tbl} não existe! Execute primeiro migrate-affiliates.php.", 'error');
    $abort = true;
}

if (!$abort) {
log_msg("Tabela {$tbl} encontrada.", 'success');

// ============================================
// 1. COLUNA reset_token
// ============================================
log_msg("Verificando coluna reset_token...");

if (column_exists($pdo, $tbl, 'reset_token')) {
    log_msg("Coluna reset_token já existe — pulando.", 'warning');
} else {
    try {
        $pdo->exec("ALTER TABLE `{$tbl}` ADD COLUMN `reset_token` VARCHAR(64) NULL DEFAULT NULL COMMENT 'Token de redefinição de senha'");
        log_msg("Coluna reset_token criada!", 'success');
    } catch (PDOException $e) {
        log_msg("Erro ao criar reset_token: " . $e->getMessage(), 'error');
    }
}

// ============================================
// 2. COLUNA reset_token_expires
// ============================================
log_msg("Verificando coluna reset_token_expires...");

if (column_exists($pdo, $tbl, 'reset_token_expires')) {
    log_msg("Coluna reset_token_expires já existe — pulando.", 'warning');
} else {
    try {
        $pdo->exec("ALTER TABLE `{$tbl}` ADD COLUMN `reset_token_expires` DATETIME NULL DEFAULT NULL COMMENT 'Expiração do token de reset'");
        log_msg("Coluna reset_token_expires criada!", 'success');
    } catch (PDOException $e) {
        log_msg("Erro ao criar reset_token_expires: " . $e->getMessage(), 'error');
    }
}

// ============================================
// 3. ÍNDICE idx_affiliate_reset_token
// ============================================
log_msg("Verificando índice idx_affiliate_reset_token...");

if (index_exists($pdo, $tbl, 'idx_affiliate_reset_token')) {
    log_msg("Índice idx_affiliate_reset_token já existe — pulando.", 'warning');
} else {
    try {
        $pdo->exec("CREATE INDEX `idx_affiliate_reset_token` ON `{$tbl}` (`reset_token`)");
        log_msg("Índice idx_affiliate_reset_token criado!", 'success');
    } catch (PDOException $e) {
        log_msg("Erro ao criar índice: " . $e->getMessage(), 'error');
    }
}

log_msg("Migration concluída!", 'success');
} // end if (!$abort)

// ============================================
// RENDER
// ============================================
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Migration: Reset de Senha — Afiliados</title>
<style>
  *, *::before, *::after { box-sizing: border-box; }
  body {
    margin: 0; padding: 40px 20px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: #0D1117; color: #e6edf3;
    min-height: 100vh;
  }
  .card {
    max-width: 640px; margin: 0 auto;
    background: #161B22; border: 1px solid #30363d;
    border-radius: 12px; overflow: hidden;
  }
  .card-header {
    background: linear-gradient(135deg, #238636, #2ea043);
    padding: 28px 32px;
  }
  .card-header h1 { margin: 0 0 6px; font-size: 22px; color: #fff; }
  .card-header p  { margin: 0; font-size: 13px; color: rgba(255,255,255,.7); }
  .card-body { padding: 28px 32px; }
  .log-item {
    padding: 10px 14px; border-radius: 8px; margin-bottom: 8px;
    font-size: 14px; display: flex; align-items: flex-start; gap: 10px;
  }
  .log-item.success { background: rgba(35,134,54,.15); border: 1px solid rgba(35,134,54,.3); color: #3fb950; }
  .log-item.warning { background: rgba(187,128,9,.15);  border: 1px solid rgba(187,128,9,.3);  color: #d29922; }
  .log-item.error   { background: rgba(248,81,73,.15);  border: 1px solid rgba(248,81,73,.3);  color: #f85149; }
  .log-item.info    { background: rgba(56,139,253,.1);  border: 1px solid rgba(56,139,253,.2); color: #58a6ff; }
  .warning-box {
    margin-top: 24px; padding: 16px 18px; border-radius: 8px;
    background: rgba(187,128,9,.12); border: 1px solid rgba(187,128,9,.35);
    color: #d29922; font-size: 13px; line-height: 1.6;
  }
  .warning-box strong { display: block; margin-bottom: 4px; font-size: 14px; }
</style>
</head>
<body>
<div class="card">
  <div class="card-header">
    <h1>🔑 Migration: Reset de Senha — Afiliados</h1>
    <p>Adiciona suporte a redefinição de senha no painel de afiliados</p>
  </div>
  <div class="card-body">
    <?php foreach ($logs as $log): ?>
      <div class="log-item <?= htmlspecialchars($log['type']) ?>">
        <?= htmlspecialchars($log['msg']) ?>
      </div>
    <?php endforeach; ?>

    <div class="warning-box">
      <strong>⚠️ Importante</strong>
      Após confirmar que a migration rodou corretamente, <strong>apague este arquivo</strong>
      do servidor para evitar execuções não autorizadas.
    </div>
  </div>
</div>
</body>
</html>
