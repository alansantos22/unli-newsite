-- ============================================
-- Sistema de Afiliados - Instalação
-- ============================================
-- Data: 2026-03-12
-- Descrição: Tabelas para o sistema de afiliados
--            com tracking de vendas, comissões e ranking

-- ============================================
-- 1. Tabela de afiliados
-- ============================================

CREATE TABLE IF NOT EXISTS `affiliate_users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(80) NOT NULL COMMENT 'Primeiro nome',
  `last_name` VARCHAR(80) NOT NULL COMMENT 'Sobrenome',
  `email` VARCHAR(150) NOT NULL UNIQUE COMMENT 'E-mail (usado para login)',
  `password_hash` VARCHAR(255) NOT NULL COMMENT 'Senha em bcrypt',
  `whatsapp` VARCHAR(20) NOT NULL COMMENT 'WhatsApp do afiliado',
  `pix_key` VARCHAR(150) DEFAULT NULL COMMENT 'Chave PIX para receber comissões',
  `affiliate_hash` VARCHAR(32) NOT NULL UNIQUE COMMENT 'Hash único do afiliado (usado nos links)',
  `tier` VARCHAR(30) NOT NULL DEFAULT 'bronze_1' COMMENT 'Título/liga atual',
  `commission_rate` DECIMAL(5,2) NOT NULL DEFAULT 5.00 COMMENT 'Percentual de comissão atual',
  `total_sales_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Montante total vendido (acumulado)',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=ativo, 0=desativado',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` DATETIME DEFAULT NULL,
  `last_tier_check` DATETIME DEFAULT NULL COMMENT 'Última verificação de manutenção de título',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_affiliate_email` (`email`),
  UNIQUE INDEX `idx_affiliate_hash` (`affiliate_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
  COMMENT='Usuários afiliados do sistema de indicação';

-- ============================================
-- 2. Tabela de vendas/indicações do afiliado
-- ============================================

CREATE TABLE IF NOT EXISTS `affiliate_referrals` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `affiliate_id` INT(11) NOT NULL COMMENT 'ID do afiliado que indicou',
  `order_id` INT(11) DEFAULT NULL COMMENT 'ID do pedido na tabela orders (quando fechado)',
  `lead_name` VARCHAR(200) DEFAULT NULL COMMENT 'Nome do lead indicado',
  `lead_email` VARCHAR(150) DEFAULT NULL COMMENT 'Email do lead',
  `lead_phone` VARCHAR(30) DEFAULT NULL COMMENT 'Telefone do lead',
  `status` ENUM('lead', 'contacted', 'negotiating', 'closed', 'onboarding', 'completed', 'lost') 
    NOT NULL DEFAULT 'lead' COMMENT 'Status do progresso',
  `sale_amount` DECIMAL(12,2) DEFAULT NULL COMMENT 'Valor da venda (quando fechada)',
  `commission_rate` DECIMAL(5,2) DEFAULT NULL COMMENT 'Taxa de comissão no momento da venda',
  `commission_amount` DECIMAL(12,2) DEFAULT NULL COMMENT 'Valor da comissão a pagar',
  `commission_paid` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=pendente, 1=paga',
  `commission_paid_at` DATETIME DEFAULT NULL COMMENT 'Data do pagamento da comissão',
  `source_page` VARCHAR(100) DEFAULT NULL COMMENT 'Página de origem (home, consultoria, site-vitrine)',
  `notes` TEXT DEFAULT NULL COMMENT 'Observações',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_affiliate_referrals_affiliate` (`affiliate_id`),
  INDEX `idx_affiliate_referrals_order` (`order_id`),
  INDEX `idx_affiliate_referrals_status` (`status`),
  CONSTRAINT `fk_referrals_affiliate` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliate_users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_referrals_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
  COMMENT='Indicações e vendas dos afiliados';

-- ============================================
-- 3. Tabela de histórico de títulos
-- ============================================

CREATE TABLE IF NOT EXISTS `affiliate_tier_history` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `affiliate_id` INT(11) NOT NULL,
  `old_tier` VARCHAR(30) NOT NULL,
  `new_tier` VARCHAR(30) NOT NULL,
  `reason` VARCHAR(200) DEFAULT NULL COMMENT 'Motivo da mudança (promoção, rebaixamento por inatividade, etc)',
  `sales_amount_at_change` DECIMAL(12,2) DEFAULT NULL COMMENT 'Montante de vendas no momento da mudança',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tier_history_affiliate` (`affiliate_id`),
  CONSTRAINT `fk_tier_history_affiliate` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliate_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
  COMMENT='Histórico de mudanças de título dos afiliados';

-- ============================================
-- 4. Adicionar coluna affiliate_id na orders
-- ============================================

ALTER TABLE `orders` ADD COLUMN `affiliate_id` INT(11) DEFAULT NULL COMMENT 'Afiliado que indicou o cliente' AFTER `sdr_id`;
ALTER TABLE `orders` ADD COLUMN `affiliate_hash` VARCHAR(32) DEFAULT NULL COMMENT 'Hash do afiliado (para registro manual)' AFTER `affiliate_id`;
ALTER TABLE `orders` ADD INDEX `idx_affiliate_id` (`affiliate_id`);
ALTER TABLE `orders` ADD CONSTRAINT `fk_orders_affiliate` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliate_users`(`id`) ON DELETE SET NULL;

-- ============================================
-- 5. Tabela para vendas trimestrais (manutenção de título)
-- ============================================

CREATE TABLE IF NOT EXISTS `affiliate_quarterly_sales` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `affiliate_id` INT(11) NOT NULL,
  `quarter_start` DATE NOT NULL COMMENT 'Início do trimestre',
  `quarter_end` DATE NOT NULL COMMENT 'Fim do trimestre',
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Total vendido no trimestre',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_quarterly_affiliate` (`affiliate_id`, `quarter_start`),
  CONSTRAINT `fk_quarterly_affiliate` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliate_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
  COMMENT='Registro de vendas trimestrais para manutenção de título';
