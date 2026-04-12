<?php
/**
 * ============================================
 * INSTALADOR - Tabela de Leads do Webinar
 * ============================================
 *
 * Acesse pelo navegador:
 * https://seu-dominio.com/install-webinar.php?password=Unli2026secure
 *
 * Cria 1 tabela:
 * - webinar_leads  (inscrições do Webinar IA e Automação - 25/04/2026)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================
// PROTEÇÃO COM SENHA
// ============================================
$INSTALL_PASSWORD = 'Unli2026secure';

if (!isset($_GET['password']) || $_GET['password'] !== $INSTALL_PASSWORD) {
    http_response_code(401);
    die('🚫 Acesso negado! Use: install-webinar.php?password=SUA_SENHA');
}

// ============================================
// CONEXÃO COM O BANCO
// ============================================
define('DB_CONFIG_ACCESS', true);
require_once __DIR__ . '/api/db.config.php';

$logs = [];

function log_msg($msg, $type = 'info') {
    global $logs;
    $logs[] = ['msg' => $msg, 'type' => $type];
}

try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    log_msg('✅ Conectado ao banco: ' . DB_NAME, 'success');
} catch (PDOException $e) {
    die('❌ Erro de conexão: ' . $e->getMessage());
}

$prefix = defined('DB_PREFIX') ? DB_PREFIX : '';

// ============================================
// VERIFICAR SE TABELA JÁ EXISTE
// ============================================
function table_exists($pdo, $name) {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$name]);
    return $stmt->rowCount() > 0;
}

// ============================================
// CRIAR TABELA webinar_leads
// ============================================
$table     = $prefix . 'webinar_leads';
$tableOld  = 'webinar_leads'; // criada sem prefixo na primeira execução

if (table_exists($pdo, $table)) {
    log_msg("⚠️ Tabela <strong>$table</strong> já existe — pulando.", 'warning');
} elseif ($prefix && table_exists($pdo, $tableOld)) {
    // Renomeia tabela criada sem prefixo
    try {
        $pdo->exec("RENAME TABLE `$tableOld` TO `$table`");
        log_msg("✅ Tabela <strong>$tableOld</strong> renomeada para <strong>$table</strong>!", 'success');
    } catch (PDOException $e) {
        log_msg("❌ Erro ao renomear tabela: " . $e->getMessage(), 'error');
    }
} else {
    try {
        $pdo->exec("
            CREATE TABLE `$table` (
              `id`          INT(11)       NOT NULL AUTO_INCREMENT,
              `nome`        VARCHAR(200)  NOT NULL                    COMMENT 'Nome completo do inscrito',
              `email`       VARCHAR(150)  NOT NULL                    COMMENT 'E-mail do inscrito',
              `ramo`        VARCHAR(100)  NOT NULL                    COMMENT 'Ramo / segmento da empresa',
              `objetivo`    VARCHAR(200)  NOT NULL                    COMMENT 'Principal objetivo com IA',
              `cidade`      VARCHAR(100)  DEFAULT NULL                COMMENT 'Cidade',
              `estado`      VARCHAR(10)   DEFAULT NULL                COMMENT 'UF / estado',
              `pais`        VARCHAR(50)   NOT NULL DEFAULT 'Brasil'   COMMENT 'País',
              `mensagem`    TEXT          DEFAULT NULL                COMMENT 'Mensagem / dúvida opcional',
              `ip`          VARCHAR(45)   DEFAULT NULL                COMMENT 'IP de origem (IPv4 ou IPv6)',
              `user_agent`  VARCHAR(500)  DEFAULT NULL                COMMENT 'User-Agent do navegador',
              `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE  INDEX `idx_webinar_email`      (`email`),
              INDEX         `idx_webinar_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
              COMMENT='Inscrições para o Webinar de IA e Automação - 25/04/2026'
        ");
        log_msg("✅ Tabela <strong>$table</strong> criada com sucesso!", 'success');
    } catch (PDOException $e) {
        log_msg("❌ Erro ao criar <strong>$table</strong>: " . $e->getMessage(), 'error');
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Instalador · Webinar Leads</title>
<style>
  body { font-family: sans-serif; background: #0f0f0f; color: #eee; padding: 40px; }
  h1   { color: #00e5a0; }
  .log { padding: 10px 16px; margin: 6px 0; border-radius: 6px; font-size: 14px; }
  .log.success { background: rgba(0,229,160,.12); border-left: 3px solid #00e5a0; }
  .log.warning { background: rgba(255,200,0,.1);  border-left: 3px solid #ffc800; }
  .log.error   { background: rgba(255,60,60,.12); border-left: 3px solid #ff3c3c; }
  .log.info    { background: rgba(255,255,255,.05); border-left: 3px solid #555; }
  .done { margin-top: 24px; padding: 16px 20px; background: rgba(0,229,160,.08);
          border: 1px solid rgba(0,229,160,.3); border-radius: 8px; color: #00e5a0; }
</style>
</head>
<body>
<h1>🛠 Instalador · Webinar Leads</h1>
<?php foreach ($logs as $log): ?>
  <div class="log <?= $log['type'] ?>"><?= $log['msg'] ?></div>
<?php endforeach; ?>
<div class="done">
  ✅ Instalação concluída. Você pode excluir este arquivo do servidor após confirmar que a tabela foi criada.
</div>
</body>
</html>
