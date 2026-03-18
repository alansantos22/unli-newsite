-- ============================================
-- Sistema Super Admin - Torre de Controle
-- ============================================
-- Data: 2026-03-17
-- Descrição: Tabela de administradores do sistema
--            com controle de acesso separado

-- ============================================
-- 1. Tabela de usuários admin
-- ============================================

CREATE TABLE IF NOT EXISTS `unli_admin_users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL COMMENT 'Nome completo do admin',
  `email` VARCHAR(150) NOT NULL UNIQUE COMMENT 'E-mail (usado para login)',
  `password_hash` VARCHAR(255) NOT NULL COMMENT 'Senha em bcrypt',
  `role` ENUM('super_admin', 'manager', 'viewer') NOT NULL DEFAULT 'manager' COMMENT 'Nível de acesso',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=ativo, 0=desativado',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
  COMMENT='Administradores do sistema com acesso ao painel Super Admin';

-- ============================================
-- 2. Log de auditoria de ações administrativas
-- ============================================

CREATE TABLE IF NOT EXISTS `unli_admin_audit_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `admin_id` INT(11) NOT NULL COMMENT 'ID do admin que executou a ação',
  `action` VARCHAR(100) NOT NULL COMMENT 'Tipo de ação (ex: tier_override, commission_paid, user_blocked)',
  `target_type` VARCHAR(50) DEFAULT NULL COMMENT 'Tipo do alvo: affiliate, sdr, order, mission, badge',
  `target_id` INT(11) DEFAULT NULL COMMENT 'ID do registro afetado',
  `old_value` TEXT DEFAULT NULL COMMENT 'Valor anterior (JSON)',
  `new_value` TEXT DEFAULT NULL COMMENT 'Valor novo (JSON)',
  `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IP do admin',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_audit_admin` (`admin_id`),
  INDEX `idx_audit_action` (`action`),
  INDEX `idx_audit_target` (`target_type`, `target_id`),
  INDEX `idx_audit_date` (`created_at`),
  CONSTRAINT `fk_audit_admin` FOREIGN KEY (`admin_id`) REFERENCES `unli_admin_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
  COMMENT='Log de auditoria de todas as ações administrativas';

-- ============================================
-- 3. Criar primeiro Super Admin
-- ============================================
-- Para gerar hash de senha use:
-- php -r "echo password_hash('sua_senha_aqui', PASSWORD_BCRYPT);"
--
-- Exemplo (ALTERE antes de executar!):
-- INSERT INTO `unli_admin_users` (`name`, `email`, `password_hash`, `role`) VALUES
-- ('Alan Santos', 'admin@unli.com.br', '$2y$10$GERE_UM_HASH_REAL_AQUI', 'super_admin');
