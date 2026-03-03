-- ============================================
-- Sistema de Onboarding Pós-Venda
-- Script de Instalação do Banco de Dados
-- ============================================

-- Selecionar o banco de dados
-- USE seu_banco_de_dados;

-- ============================================
-- 1. Criar tabela de pedidos (orders)
-- ============================================

DROP TABLE IF EXISTS `orders`;

CREATE TABLE `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'ID único do pedido',
  
  -- Informações do Cliente
  `customer_name` VARCHAR(255) NOT NULL COMMENT 'Nome completo do cliente',
  `email` VARCHAR(255) NOT NULL COMMENT 'E-mail do cliente',
  `phone` VARCHAR(50) DEFAULT NULL COMMENT 'Telefone/WhatsApp do cliente',
  
  -- Informações do Pedido
  `order_details` TEXT DEFAULT NULL COMMENT 'JSON com detalhes do plano contratado',
  `total_amount` DECIMAL(10,2) NOT NULL COMMENT 'Valor total pago',
  `payment_status` ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending' COMMENT 'Status do pagamento',
  `payment_method` VARCHAR(50) DEFAULT NULL COMMENT 'Método de pagamento usado',
  `payment_id` VARCHAR(255) DEFAULT NULL COMMENT 'ID da transação no gateway de pagamento',
  
  -- Sistema de Onboarding (Magic Link)
  `onboarding_token` VARCHAR(64) UNIQUE NOT NULL COMMENT 'Token único para acesso ao wizard',
  `onboarding_status` ENUM('pendente', 'preenchendo', 'concluido') DEFAULT 'pendente' COMMENT 'Status do preenchimento do briefing',
  `briefing_data` LONGTEXT DEFAULT NULL COMMENT 'JSON com dados do briefing preenchido pelo cliente',
  
  -- Timestamps
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação do pedido',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última atualização',
  `completed_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Data de conclusão do briefing',
  
  -- Chave primária
  PRIMARY KEY (`id`),
  
  -- Índices para performance
  INDEX `idx_token` (`onboarding_token`) COMMENT 'Busca rápida por token',
  INDEX `idx_email` (`email`) COMMENT 'Busca por e-mail do cliente',
  INDEX `idx_status` (`onboarding_status`) COMMENT 'Filtrar por status de onboarding',
  INDEX `idx_payment_status` (`payment_status`) COMMENT 'Filtrar por status de pagamento',
  INDEX `idx_payment_id` (`payment_id`) COMMENT 'Busca por ID de pagamento'
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela de pedidos e onboarding';

-- ============================================
-- 2. Inserir dados de teste (OPCIONAL)
-- ============================================

-- Pedido 1: Pendente de onboarding
INSERT INTO `orders` (
  `customer_name`,
  `email`,
  `phone`,
  `order_details`,
  `total_amount`,
  `payment_status`,
  `payment_method`,
  `payment_id`,
  `onboarding_token`,
  `onboarding_status`
) VALUES (
  'João Silva',
  'joao.silva@example.com',
  '(11) 99999-1111',
  '{"plan":"Site Vitrine Anual","price":1497.00,"features":["Domínio","SSL","E-mail","Suporte"]}',
  1497.00,
  'paid',
  'credit_card',
  'pay_test_123456',
  'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6', -- Token de teste
  'pendente'
);

-- Pedido 2: Em preenchimento (com rascunho)
INSERT INTO `orders` (
  `customer_name`,
  `email`,
  `phone`,
  `order_details`,
  `total_amount`,
  `payment_status`,
  `payment_method`,
  `payment_id`,
  `onboarding_token`,
  `onboarding_status`,
  `briefing_data`
) VALUES (
  'Maria Santos',
  'maria.santos@example.com',
  '(11) 99999-2222',
  '{"plan":"Site Vitrine Anual","price":1497.00,"features":["Domínio","SSL","E-mail","Suporte"]}',
  1497.00,
  'paid',
  'pix',
  'pay_test_789012',
  'q2w3e4r5t6y7u8i9o0p1a2s3d4f5g6h7', -- Token de teste
  'preenchendo',
  '{"companyName":"Padaria Dona Maria","slogan":"O melhor pão da região","whatsapp":"(11) 98888-7777"}'
);

-- Pedido 3: Concluído
INSERT INTO `orders` (
  `customer_name`,
  `email`,
  `phone`,
  `order_details`,
  `total_amount`,
  `payment_status`,
  `payment_method`,
  `payment_id`,
  `onboarding_token`,
  `onboarding_status`,
  `briefing_data`,
  `completed_at`
) VALUES (
  'Carlos Oliveira',
  'carlos.oliveira@example.com',
  '(11) 99999-3333',
  '{"plan":"Site Vitrine Anual","price":1497.00,"features":["Domínio","SSL","E-mail","Suporte"]}',
  1497.00,
  'paid',
  'credit_card',
  'pay_test_345678',
  'z1x2c3v4b5n6m7a8s9d0f1g2h3j4k5l6', -- Token de teste
  'concluido',
  '{"companyName":"Tech Solutions","slogan":"Soluções em tecnologia","whatsapp":"(11) 97777-6666","instagram":"https://instagram.com/techsolutions","description":"Oferecemos soluções tecnológicas para empresas","services":[{"name":"Consultoria","description":"Consultoria em TI"},{"name":"Suporte","description":"Suporte técnico 24/7"}],"differentials":["quality","experience","support"],"primaryColor":"#0066CC","designStyle":"corporate"}',
  NOW()
);

-- ============================================
-- 3. Queries úteis para administração
-- ============================================

-- Ver todos os pedidos pendentes
-- SELECT 
--   id, 
--   customer_name, 
--   email, 
--   onboarding_status,
--   created_at
-- FROM orders 
-- WHERE onboarding_status IN ('pendente', 'preenchendo')
-- ORDER BY created_at DESC;

-- Ver taxa de conversão do onboarding
-- SELECT 
--   onboarding_status,
--   COUNT(*) as total,
--   ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM orders WHERE payment_status = 'paid'), 2) as percentual
-- FROM orders 
-- WHERE payment_status = 'paid'
-- GROUP BY onboarding_status;

-- Ver briefings completos recentes
-- SELECT 
--   id,
--   customer_name,
--   email,
--   completed_at,
--   JSON_EXTRACT(briefing_data, '$.companyName') as empresa
-- FROM orders 
-- WHERE onboarding_status = 'concluido'
-- ORDER BY completed_at DESC
-- LIMIT 10;

-- Buscar pedido por token
-- SELECT * FROM orders WHERE onboarding_token = 'SEU_TOKEN_AQUI';

-- ============================================
-- 4. Verificar criação da tabela
-- ============================================

SHOW TABLES LIKE 'orders';
DESCRIBE orders;

-- ============================================
-- 5. Tabelas de Log de Conversas IA
-- ============================================

-- SDR Conversations (pré-venda)
DROP TABLE IF EXISTS `sdr_messages`;
DROP TABLE IF EXISTS `sdr_conversations`;

CREATE TABLE `sdr_conversations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'ID interno',
  `conversation_id` VARCHAR(36) NOT NULL COMMENT 'UUID público da conversa',
  
  -- Rastreamento do visitante
  `ip_address` VARCHAR(45) NOT NULL COMMENT 'IP do visitante (IPv4/IPv6)',
  `user_agent` VARCHAR(500) DEFAULT NULL COMMENT 'User-Agent do navegador',
  
  -- Dados do cliente (preenchidos após checkout)
  `customer_name` VARCHAR(255) DEFAULT NULL COMMENT 'Nome do cliente (atualizado no checkout)',
  `customer_email` VARCHAR(255) DEFAULT NULL COMMENT 'Email do cliente (atualizado no checkout)',
  `customer_phone` VARCHAR(50) DEFAULT NULL COMMENT 'Telefone/WhatsApp (atualizado no checkout)',
  
  -- Estado da conversa
  `last_stage` VARCHAR(30) DEFAULT 'ABERTURA' COMMENT 'Último estágio do SDR',
  `client_data` JSON DEFAULT NULL COMMENT 'Dados extraídos do cliente (negócio, nicho, etc)',
  `suggested_plan` JSON DEFAULT NULL COMMENT 'Plano sugerido pela IA',
  `finished` TINYINT(1) DEFAULT 0 COMMENT 'Se a conversa foi finalizada',
  
  -- Vínculo com pedido
  `order_id` INT(11) DEFAULT NULL COMMENT 'FK: pedido criado a partir desta conversa',
  
  -- Estatísticas
  `total_messages` INT(11) DEFAULT 0 COMMENT 'Total de mensagens na conversa',
  
  -- Timestamps
  `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Início da conversa',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última interação',
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_conversation_id` (`conversation_id`),
  INDEX `idx_ip` (`ip_address`),
  INDEX `idx_email` (`customer_email`),
  INDEX `idx_order_id` (`order_id`),
  INDEX `idx_stage` (`last_stage`),
  INDEX `idx_finished` (`finished`),
  INDEX `idx_started_at` (`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log de conversas do SDR (pré-venda)';

CREATE TABLE `sdr_messages` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'ID da mensagem',
  `conversation_id` INT(11) NOT NULL COMMENT 'FK: conversa SDR',
  
  -- Conteúdo
  `role` ENUM('user', 'assistant') NOT NULL COMMENT 'Quem enviou a mensagem',
  `content` TEXT NOT NULL COMMENT 'Conteúdo da mensagem',
  `stage` VARCHAR(30) DEFAULT NULL COMMENT 'Estágio do SDR neste momento',
  `metadata` JSON DEFAULT NULL COMMENT 'Dados extras (pricing, clientData, etc)',
  
  -- Timestamps
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Quando a mensagem foi enviada',
  
  PRIMARY KEY (`id`),
  INDEX `idx_conversation` (`conversation_id`),
  INDEX `idx_role` (`role`),
  INDEX `idx_created_at` (`created_at`),
  CONSTRAINT `fk_sdr_msg_conv` FOREIGN KEY (`conversation_id`) 
    REFERENCES `sdr_conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Mensagens individuais das conversas SDR';

-- Onboarding Conversations (pós-venda)
DROP TABLE IF EXISTS `onboarding_messages`;
DROP TABLE IF EXISTS `onboarding_conversations`;

CREATE TABLE `onboarding_conversations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'ID interno',
  `conversation_id` VARCHAR(36) NOT NULL COMMENT 'UUID público da conversa',
  
  -- Vínculo com pedido/projeto
  `order_id` INT(11) DEFAULT NULL COMMENT 'FK: pedido associado',
  `onboarding_token` VARCHAR(64) DEFAULT NULL COMMENT 'Token do magic-link de onboarding',
  
  -- Dados do cliente (já disponíveis via pedido)
  `customer_name` VARCHAR(255) DEFAULT NULL COMMENT 'Nome do cliente',
  `customer_email` VARCHAR(255) DEFAULT NULL COMMENT 'Email do cliente',
  
  -- Contexto do projeto
  `plan_name` VARCHAR(100) DEFAULT NULL COMMENT 'Nome do plano contratado',
  `purchased_pages` JSON DEFAULT NULL COMMENT 'Páginas compradas',
  
  -- Estado da conversa
  `finished` TINYINT(1) DEFAULT 0 COMMENT 'Se a conversa foi finalizada',
  `extracted_data` JSON DEFAULT NULL COMMENT 'Dados extraídos pela IA (briefing)',
  
  -- Estatísticas
  `total_messages` INT(11) DEFAULT 0 COMMENT 'Total de mensagens na conversa',
  
  -- Timestamps
  `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Início da conversa',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última interação',
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_conversation_id` (`conversation_id`),
  INDEX `idx_order_id` (`order_id`),
  INDEX `idx_token` (`onboarding_token`),
  INDEX `idx_email` (`customer_email`),
  INDEX `idx_finished` (`finished`),
  INDEX `idx_started_at` (`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log de conversas do onboarding (pós-venda)';

CREATE TABLE `onboarding_messages` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'ID da mensagem',
  `conversation_id` INT(11) NOT NULL COMMENT 'FK: conversa onboarding',
  
  -- Conteúdo
  `role` ENUM('user', 'assistant') NOT NULL COMMENT 'Quem enviou a mensagem',
  `content` TEXT NOT NULL COMMENT 'Conteúdo da mensagem',
  `metadata` JSON DEFAULT NULL COMMENT 'Dados extras',
  
  -- Timestamps
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Quando a mensagem foi enviada',
  
  PRIMARY KEY (`id`),
  INDEX `idx_conversation` (`conversation_id`),
  INDEX `idx_role` (`role`),
  INDEX `idx_created_at` (`created_at`),
  CONSTRAINT `fk_onb_msg_conv` FOREIGN KEY (`conversation_id`) 
    REFERENCES `onboarding_conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Mensagens individuais das conversas de onboarding';

-- ============================================
-- 6. Queries úteis para análise de conversas
-- ============================================

-- Ver conversas SDR recentes com estatísticas
-- SELECT 
--   c.conversation_id,
--   c.ip_address,
--   c.customer_name,
--   c.customer_email,
--   c.last_stage,
--   c.finished,
--   c.total_messages,
--   c.started_at,
--   c.order_id
-- FROM sdr_conversations c
-- ORDER BY c.started_at DESC
-- LIMIT 20;

-- Ver conversa SDR completa (todas as mensagens)
-- SELECT 
--   m.role,
--   m.content,
--   m.stage,
--   m.created_at
-- FROM sdr_messages m
-- JOIN sdr_conversations c ON c.id = m.conversation_id
-- WHERE c.conversation_id = 'UUID_AQUI'
-- ORDER BY m.created_at ASC;

-- Taxa de conversão SDR (conversas → pedidos)
-- SELECT 
--   COUNT(*) as total_conversas,
--   SUM(CASE WHEN finished = 1 THEN 1 ELSE 0 END) as finalizadas,
--   SUM(CASE WHEN order_id IS NOT NULL THEN 1 ELSE 0 END) as converteram,
--   ROUND(SUM(CASE WHEN order_id IS NOT NULL THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as taxa_conversao
-- FROM sdr_conversations;

-- Ver conversas onboarding por projeto
-- SELECT 
--   c.conversation_id,
--   c.customer_name,
--   c.plan_name,
--   c.finished,
--   c.total_messages,
--   c.started_at
-- FROM onboarding_conversations c
-- ORDER BY c.started_at DESC;

-- ============================================
-- FIM DO SCRIPT
-- ============================================

SELECT 
  '✅ Instalação concluída com sucesso!' as Status,
  (SELECT COUNT(*) FROM orders) as 'Total de Pedidos',
  (SELECT COUNT(*) FROM orders WHERE onboarding_status = 'pendente') as 'Pendentes',
  (SELECT COUNT(*) FROM orders WHERE onboarding_status = 'preenchendo') as 'Preenchendo',
  (SELECT COUNT(*) FROM orders WHERE onboarding_status = 'concluido') as 'Concluídos';
