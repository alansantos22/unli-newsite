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
-- FIM DO SCRIPT
-- ============================================

SELECT 
  '✅ Instalação concluída com sucesso!' as Status,
  (SELECT COUNT(*) FROM orders) as 'Total de Pedidos',
  (SELECT COUNT(*) FROM orders WHERE onboarding_status = 'pendente') as 'Pendentes',
  (SELECT COUNT(*) FROM orders WHERE onboarding_status = 'preenchendo') as 'Preenchendo',
  (SELECT COUNT(*) FROM orders WHERE onboarding_status = 'concluido') as 'Concluídos';
