-- ============================================
-- Sistema SDR Manual - Painel de Vendas
-- Script de Instalação do Banco de Dados
-- ============================================
-- Data: 2026-03-09
-- Descrição: Cria tabela de usuários SDR e
--            adiciona colunas necessárias na tabela orders

-- ============================================
-- 1. Criar tabela de usuários SDR
-- ============================================

CREATE TABLE IF NOT EXISTS `sdr_users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL COMMENT 'Nome do SDR',
  `email` VARCHAR(150) NOT NULL UNIQUE COMMENT 'E-mail (usado para login)',
  `password_hash` VARCHAR(255) NOT NULL COMMENT 'Senha em bcrypt',
  `whatsapp` VARCHAR(20) DEFAULT NULL COMMENT 'WhatsApp do SDR',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=ativo, 0=desativado',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_sdr_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Usuários SDR do painel de vendas';

-- ============================================
-- 2. Adicionar colunas na tabela orders
-- ============================================

-- ID do SDR que registrou a venda (NULL = venda automática/online)
ALTER TABLE `orders` ADD COLUMN `sdr_id` INT(11) DEFAULT NULL COMMENT 'SDR que registrou a venda' AFTER `payment_id`;

-- Nome da empresa (separado do nome do cliente)
ALTER TABLE `orders` ADD COLUMN `company_name` VARCHAR(200) DEFAULT NULL COMMENT 'Nome da empresa' AFTER `customer_name`;

-- Método de pagamento manual (PIX, Cartão, Boleto, Outro)
ALTER TABLE `orders` ADD COLUMN `payment_method_manual` VARCHAR(50) DEFAULT NULL COMMENT 'Método de pagamento (manual)' AFTER `payment_status`;

-- Notas do SDR sobre a venda
ALTER TABLE `orders` ADD COLUMN `sdr_notes` TEXT DEFAULT NULL COMMENT 'Observações do SDR';

-- Data/hora do envio do e-mail de onboarding
ALTER TABLE `orders` ADD COLUMN `onboarding_email_sent_at` DATETIME DEFAULT NULL COMMENT 'Quando o email de onboarding foi enviado';

-- Foreign key do SDR
ALTER TABLE `orders` ADD CONSTRAINT `fk_orders_sdr` FOREIGN KEY (`sdr_id`) REFERENCES `sdr_users`(`id`) ON DELETE SET NULL;

-- Índice para busca por SDR
ALTER TABLE `orders` ADD INDEX `idx_sdr_id` (`sdr_id`);

-- ============================================
-- 3. Criar primeiro SDR (ALTERE OS DADOS!)
-- ============================================
-- Senha padrão: trocar123
-- Hash bcrypt de 'trocar123': $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- ⚠️ TROQUE A SENHA após o primeiro login ou gere um novo hash

-- INSERT INTO `sdr_users` (`name`, `email`, `password_hash`, `whatsapp`) VALUES
-- ('Nome do SDR', 'sdr@unli.com.br', '$2y$10$GERE_UM_HASH_REAL_AQUI', '5511968354238');

-- Para gerar hash de senha use:
-- php -r "echo password_hash('sua_senha_aqui', PASSWORD_BCRYPT);"
