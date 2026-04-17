-- ============================================
-- Webinar Leads - Instalação
-- ============================================
-- Data: 2026-04-11
-- Descrição: Tabela para armazenar os cadastros
--            da landing page do Webinar (25/04/2026)

CREATE TABLE IF NOT EXISTS `webinar_leads` (
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
  `ref_code`    VARCHAR(64)   DEFAULT NULL                COMMENT 'Código do afiliado indicador (?ref=)',
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE  INDEX `idx_webinar_email`      (`email`),
  INDEX         `idx_webinar_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Inscrições para o Webinar de IA e Automação - 25/04/2026';

-- Migração: adiciona coluna ref_code caso a tabela já exista sem ela
ALTER TABLE `webinar_leads`
  ADD COLUMN IF NOT EXISTS `ref_code` VARCHAR(64) DEFAULT NULL
    COMMENT 'Código do afiliado indicador (?ref=)' AFTER `user_agent`;

-- Migração: salvar lead progressivo (lead pode ser salvo parcialmente antes de completar)
ALTER TABLE `webinar_leads`
  MODIFY COLUMN `ramo`     VARCHAR(100) DEFAULT NULL COMMENT 'Ramo / segmento da empresa',
  MODIFY COLUMN `objetivo` VARCHAR(200) DEFAULT NULL COMMENT 'Principal objetivo com IA',
  ADD COLUMN IF NOT EXISTS `completed` TINYINT(1) NOT NULL DEFAULT 0
    COMMENT '1 = preencheu todas as etapas; 0 = lead parcial' AFTER `ref_code`;
