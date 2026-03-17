-- ============================================
-- Sistema de Medalhas e Missões de Afiliados
-- ============================================
-- Tabelas:
--   1. affiliate_badges         → Catálogo de medalhas/selos
--   2. affiliate_user_badges    → Medalhas conquistadas por afiliados
--   3. affiliate_missions       → Catálogo de missões (semanais, etc.)
--   4. affiliate_mission_progress → Progresso das missões por afiliado
-- ============================================

-- 1. Catálogo de medalhas
CREATE TABLE IF NOT EXISTS `unli_affiliate_badges` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(120) NOT NULL COMMENT 'Nome da conquista',
    `description` TEXT DEFAULT NULL COMMENT 'O que foi feito para ganhar',
    `image_url` VARCHAR(255) DEFAULT NULL COMMENT 'URL do ícone da medalha',
    `icon_emoji` VARCHAR(10) DEFAULT NULL COMMENT 'Emoji representativo',
    `type` ENUM('event', 'achievement', 'legacy') NOT NULL DEFAULT 'achievement' COMMENT 'Tipo: evento, conquista ou legado',
    `criteria_key` VARCHAR(80) DEFAULT NULL COMMENT 'Chave interna para verificação automática (ex: total_sales_10000)',
    `criteria_value` DECIMAL(12,2) DEFAULT NULL COMMENT 'Valor numérico do critério (ex: 10000.00)',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT(11) NOT NULL DEFAULT 0 COMMENT 'Ordem de exibição',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_badge_type` (`type`),
    INDEX `idx_badge_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Catálogo de medalhas e selos para afiliados';

-- 2. Medalhas conquistadas
CREATE TABLE IF NOT EXISTS `unli_affiliate_user_badges` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `affiliate_id` INT(11) NOT NULL COMMENT 'ID do afiliado',
    `badge_id` INT(11) NOT NULL COMMENT 'ID da medalha',
    `awarded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data da conquista',
    `awarded_by` VARCHAR(50) DEFAULT 'system' COMMENT 'Quem concedeu: system ou admin',
    `notes` TEXT DEFAULT NULL COMMENT 'Observação adicional',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `idx_user_badge_unique` (`affiliate_id`, `badge_id`),
    INDEX `idx_user_badge_affiliate` (`affiliate_id`),
    INDEX `idx_user_badge_badge` (`badge_id`),
    CONSTRAINT `fk_user_badges_affiliate` FOREIGN KEY (`affiliate_id`)
        REFERENCES `unli_affiliate_users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_user_badges_badge` FOREIGN KEY (`badge_id`)
        REFERENCES `unli_affiliate_badges`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Medalhas conquistadas pelos afiliados';

-- 3. Catálogo de missões
CREATE TABLE IF NOT EXISTS `unli_affiliate_missions` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(120) NOT NULL COMMENT 'Nome da missão',
    `description` TEXT DEFAULT NULL COMMENT 'Descrição detalhada',
    `icon_emoji` VARCHAR(10) DEFAULT NULL COMMENT 'Emoji representativo',
    `category` ENUM('explorer', 'lead_hunter', 'closer', 'consistency') NOT NULL COMMENT 'Categoria da missão',
    `target_value` DECIMAL(12,2) NOT NULL COMMENT 'Meta numérica a atingir',
    `target_unit` VARCHAR(30) NOT NULL DEFAULT 'count' COMMENT 'Unidade: count, currency, days',
    `reward_type` ENUM('xp', 'commission_bonus', 'badge') NOT NULL DEFAULT 'xp' COMMENT 'Tipo de recompensa',
    `reward_value` DECIMAL(8,2) DEFAULT NULL COMMENT 'Valor da recompensa (XP ou % bônus)',
    `frequency` ENUM('weekly', 'monthly', 'one_time') NOT NULL DEFAULT 'weekly' COMMENT 'Frequência de reset',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT(11) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_mission_category` (`category`),
    INDEX `idx_mission_frequency` (`frequency`),
    INDEX `idx_mission_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Catálogo de missões para afiliados';

-- 4. Progresso das missões por afiliado
CREATE TABLE IF NOT EXISTS `unli_affiliate_mission_progress` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `affiliate_id` INT(11) NOT NULL,
    `mission_id` INT(11) NOT NULL,
    `week_start` DATE NOT NULL COMMENT 'Início da semana (segunda-feira)',
    `current_value` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Progresso atual',
    `completed` TINYINT(1) NOT NULL DEFAULT 0,
    `completed_at` DATETIME DEFAULT NULL,
    `reward_claimed` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `idx_mission_progress_unique` (`affiliate_id`, `mission_id`, `week_start`),
    INDEX `idx_mission_progress_affiliate` (`affiliate_id`),
    INDEX `idx_mission_progress_week` (`week_start`),
    CONSTRAINT `fk_mission_progress_affiliate` FOREIGN KEY (`affiliate_id`)
        REFERENCES `unli_affiliate_users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_mission_progress_mission` FOREIGN KEY (`mission_id`)
        REFERENCES `unli_affiliate_missions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Progresso das missões semanais dos afiliados';
