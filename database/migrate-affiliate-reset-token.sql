-- Migração: Adicionar colunas de reset de senha para afiliados
-- Executar uma única vez no banco de dados

ALTER TABLE affiliate_users
    ADD COLUMN IF NOT EXISTS reset_token VARCHAR(64) NULL DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS reset_token_expires DATETIME NULL DEFAULT NULL;

-- Índice para busca rápida por token
CREATE INDEX IF NOT EXISTS idx_affiliate_reset_token ON affiliate_users (reset_token);
