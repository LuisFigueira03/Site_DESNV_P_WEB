-- --------------------------------------------------------
-- Anfitrião:                    127.0.0.1
-- Versão do servidor:           8.4.3 - MySQL Community Server - GPL
-- SO do servidor:               Win64
-- HeidiSQL Versão:              12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- A despejar estrutura da base de dados para web2
CREATE DATABASE IF NOT EXISTS `web2` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `web2`;

-- A despejar estrutura para tabela web2.acordo
CREATE TABLE IF NOT EXISTS `acordo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- A despejar dados para tabela web2.acordo: ~4 rows (aproximadamente)
INSERT INTO `acordo` (`id`, `nome`, `ativo`) VALUES
	(1, 'ADSE', 1),
	(2, 'Médis', 1),
	(3, 'Multicare', 1),
	(4, 'Particular', 1);

-- A despejar estrutura para tabela web2.administracao
CREATE TABLE IF NOT EXISTS `administracao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- A despejar dados para tabela web2.administracao: ~3 rows (aproximadamente)
INSERT INTO `administracao` (`id`, `nome`, `email`, `password_hash`) VALUES
	(1, '2', '2@admin.pt', '2'),
	(2, 'Beatriz Rocha', 'beatriz.rocha@admin.pt', 'teste'),
	(3, 'Joao Silva', 'joao.silva@admin.pt', 'teste');

-- A despejar estrutura para tabela web2.ausencia_medico
CREATE TABLE IF NOT EXISTS `ausencia_medico` (
  `id` int NOT NULL AUTO_INCREMENT,
  `medico_id` int NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `tipo` enum('ferias','baixa','formacao','outro') DEFAULT 'outro',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_ausencia_medico_data` (`medico_id`,`data_inicio`,`data_fim`),
  CONSTRAINT `fk_ausencia_medico` FOREIGN KEY (`medico_id`) REFERENCES `medico` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- A despejar dados para tabela web2.ausencia_medico: ~2 rows (aproximadamente)
INSERT INTO `ausencia_medico` (`id`, `medico_id`, `data_inicio`, `data_fim`, `motivo`, `tipo`, `ativo`) VALUES
	(6, 2, '2026-01-10', '2026-01-14', NULL, 'ferias', 0),
	(7, 2, '2026-01-11', '2026-01-13', NULL, 'ferias', 0),
	(8, 12, '2026-01-13', '2026-01-22', NULL, 'ferias', 0),
	(9, 2, '2026-01-09', '2026-01-16', NULL, 'ferias', 1),
	(10, 15, '2026-01-22', '2026-01-24', NULL, 'ferias', 1);

-- A despejar estrutura para tabela web2.consulta
CREATE TABLE IF NOT EXISTS `consulta` (
  `id` int NOT NULL AUTO_INCREMENT,
  `medico_id` int NOT NULL,
  `utente_id` int NOT NULL,
  `data_consulta` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL,
  `estado` enum('marcada','cancelada','realizada','faltou') DEFAULT 'marcada',
  `acordo_id` int DEFAULT NULL,
  `tipo` enum('presencial','video','telefone') DEFAULT 'presencial',
  `observacoes` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_consulta_medico_slot` (`medico_id`,`data_consulta`,`hora_inicio`),
  KEY `fk_consulta_acordo` (`acordo_id`),
  KEY `idx_consulta_medico_data` (`medico_id`,`data_consulta`),
  KEY `idx_consulta_utente_data` (`utente_id`,`data_consulta`),
  CONSTRAINT `fk_consulta_acordo` FOREIGN KEY (`acordo_id`) REFERENCES `acordo` (`id`),
  CONSTRAINT `fk_consulta_medico` FOREIGN KEY (`medico_id`) REFERENCES `medico` (`id`),
  CONSTRAINT `fk_consulta_utente` FOREIGN KEY (`utente_id`) REFERENCES `utente` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- A despejar dados para tabela web2.consulta: ~17 rows (aproximadamente)
INSERT INTO `consulta` (`id`, `medico_id`, `utente_id`, `data_consulta`, `hora_inicio`, `hora_fim`, `estado`, `acordo_id`, `tipo`, `observacoes`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, '2025-03-20', '10:00:00', '11:00:00', 'realizada', 2, 'presencial', 'Consulta de rotina', '2025-11-14 21:13:28', '2026-01-12 02:22:14'),
	(2, 1, 2, '2025-03-20', '11:00:00', '12:00:00', 'realizada', 1, 'presencial', 'Avaliação de tensão arterial', '2025-11-14 21:13:28', '2026-01-12 02:22:58'),
	(3, 4, 3, '2025-03-22', '14:00:00', '15:00:00', 'realizada', 4, 'presencial', 'Queixas dermatológicas', '2025-11-14 21:13:28', '2026-01-12 02:22:18'),
	(4, 9, 4, '2025-11-21', '10:00:00', '10:30:00', 'realizada', 1, 'presencial', 'efefe', '2025-11-14 21:32:26', '2026-01-12 02:22:16'),
	(6, 9, 6, '2025-11-21', '09:00:00', '09:30:00', 'realizada', NULL, 'presencial', '', '2025-11-15 04:07:44', '2026-01-12 02:22:55'),
	(7, 9, 7, '2025-11-21', '11:00:00', '11:30:00', 'realizada', 1, 'presencial', '', '2025-11-15 04:12:42', '2026-01-12 02:22:27'),
	(12, 9, 4, '2025-11-21', '14:00:00', '14:30:00', 'realizada', NULL, 'presencial', 'dede', '2025-11-17 13:38:35', '2026-01-12 02:22:22'),
	(13, 7, 8, '2025-11-21', '09:00:00', '09:30:00', 'realizada', NULL, 'presencial', '', '2025-11-18 20:45:02', '2026-01-12 02:22:51'),
	(16, 9, 8, '2025-11-21', '15:00:00', '15:30:00', 'realizada', NULL, 'presencial', '', '2025-11-18 20:48:01', '2026-01-12 02:22:47'),
	(18, 7, 8, '2025-11-21', '14:00:00', '14:30:00', 'realizada', NULL, 'presencial', '', '2025-11-18 20:51:24', '2026-01-12 02:22:45'),
	(19, 2, 8, '2025-11-21', '17:00:00', '17:30:00', 'realizada', NULL, 'presencial', '', '2025-11-18 22:15:49', '2026-01-12 02:23:00'),
	(20, 8, 8, '2025-11-20', '16:00:00', '16:30:00', 'realizada', NULL, 'presencial', '', '2025-11-18 22:16:13', '2026-01-12 02:22:42'),
	(21, 4, 8, '2025-11-05', '11:00:00', '11:30:00', 'realizada', NULL, 'presencial', '', '2025-11-20 16:41:06', '2026-01-12 02:22:40'),
	(22, 4, 8, '2025-12-04', '16:00:00', '16:30:00', 'realizada', NULL, 'presencial', '<ul><li>efefefefeefe</li></ul>', '2025-12-06 03:26:52', '2025-12-16 20:21:42'),
	(23, 4, 8, '2025-11-30', '11:00:00', '11:30:00', 'realizada', NULL, 'presencial', '', '2025-12-06 03:27:28', '2026-01-12 02:22:37'),
	(24, 8, 8, '2025-12-18', '11:00:00', '11:30:00', 'realizada', NULL, 'presencial', '', '2025-12-15 14:43:30', '2026-01-12 02:22:35'),
	(25, 4, 3, '2025-12-17', '16:00:00', '16:30:00', 'realizada', 4, 'presencial', '', '2025-12-16 21:27:10', '2026-01-12 02:22:32'),
	(26, 12, 8, '2026-01-01', '16:00:00', '16:30:00', 'realizada', NULL, 'presencial', '', '2026-01-10 20:25:50', '2026-01-12 02:22:30'),
	(27, 1, 15, '2026-01-13', '16:00:00', '16:30:00', 'marcada', 3, 'presencial', '<p>TEste</p>', '2026-01-12 01:19:02', '2026-01-12 19:28:56'),
	(28, 1, 15, '2025-03-12', '11:00:00', '11:30:00', 'realizada', 3, 'presencial', '<p>Paciente apresenta queixas de dor ligeira na lombar após atividade física. Sem febre ou outros sintomas associados. Recomenda-se alongamento e hidratação adequada.</p>', '2026-01-12 01:20:13', '2026-01-12 02:22:24'),
	(29, 6, 8, '2026-01-14', '11:00:00', '11:30:00', 'marcada', NULL, 'presencial', '', '2026-01-12 15:53:32', NULL),
	(31, 12, 8, '2026-01-31', '10:00:00', '10:30:00', 'marcada', NULL, 'presencial', '', '2026-01-12 17:54:30', NULL),
	(32, 12, 15, '2026-01-15', '10:00:00', '10:30:00', 'marcada', 3, 'presencial', '', '2026-01-12 19:32:47', NULL),
	(33, 15, 8, '2026-01-16', '16:00:00', '16:30:00', 'marcada', NULL, 'presencial', '', '2026-01-12 22:51:51', NULL),
	(34, 15, 8, '2026-01-16', '15:00:00', '15:30:00', 'marcada', NULL, 'presencial', '', '2026-01-12 22:52:07', NULL);

-- A despejar estrutura para tabela web2.contacto_mensagem
CREATE TABLE IF NOT EXISTS `contacto_mensagem` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_remetente` enum('anonimo','utente','medico') NOT NULL DEFAULT 'anonimo',
  `utente_id` int DEFAULT NULL,
  `medico_id` int DEFAULT NULL,
  `nome` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telemovel` varchar(20) DEFAULT NULL,
  `assunto` varchar(200) NOT NULL,
  `mensagem_html` mediumtext NOT NULL,
  `estado` enum('novo','lido','respondido') NOT NULL DEFAULT 'novo',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_contacto_utente` (`utente_id`),
  KEY `fk_contacto_medico` (`medico_id`),
  CONSTRAINT `fk_contacto_medico` FOREIGN KEY (`medico_id`) REFERENCES `medico` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_contacto_utente` FOREIGN KEY (`utente_id`) REFERENCES `utente` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- A despejar dados para tabela web2.contacto_mensagem: ~1 rows (aproximadamente)
INSERT INTO `contacto_mensagem` (`id`, `tipo_remetente`, `utente_id`, `medico_id`, `nome`, `email`, `telemovel`, `assunto`, `mensagem_html`, `estado`, `created_at`) VALUES
	(1, 'medico', NULL, 4, 'Dra. Marta Sousa', '1@1', '915678901', 'Esclarecimento de dúvida interna', '<p>Sou médico da clínica e utilizo este formulário para esclarecer uma dúvida relacionada com o funcionamento do site/sistema. \nAgradecia, por favor, o contacto da equipa responsável para obter os devidos esclarecimentos.\n\nFico a aguardar resposta.</p>', 'respondido', '2025-12-16 19:43:29');

-- A despejar estrutura para tabela web2.especialidade
CREATE TABLE IF NOT EXISTS `especialidade` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text,
  `imagem` varchar(255) NOT NULL,
  `informacao` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `visivel` tinyint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- A despejar dados para tabela web2.especialidade: ~5 rows (aproximadamente)
INSERT INTO `especialidade` (`id`, `nome`, `descricao`, `imagem`, `informacao`, `visivel`) VALUES
	(1, 'Cardiologia', 'Doenças do coração', 'cardiologia.jpg', '<h2 class="text-2xl font-semibold mb-4 text-[#09A2AE]">O que é a Cardiologia?</h2>\r\n<p class="text-gray-700 leading-relaxed mb-6 text-lg">\r\n    A cardiologia é a especialidade médica dedicada ao diagnóstico, prevenção e \r\n    tratamento de doenças relacionadas com o coração e sistema cardiovascular.\r\n</p>\r\n\r\n<p class="text-gray-900 leading-relaxed mb-10 text-lg">\r\n    Entre as doenças tratadas pela Cardiologia estão, entre outras: arritmias cardíacas, \r\n    aterosclerose, cardiopatias congénitas do adulto, doença coronária, doença valvular, \r\n    enfarte do miocárdio, hipertensão arterial, insuficiência cardíaca e miocardiopatias.\r\n</p>\r\n\r\n<h2 class="text-2xl font-semibold mb-4 text-[#09A2AE]">Principais serviços:</h2>\r\n<ul class="list-disc pl-6 text-gray-900 mb-6 text-lg space-y-2">\r\n    <li>Consulta de avaliação cardíaca</li>\r\n    <li>Eletrocardiograma (ECG)</li>\r\n    <li>Ecocardiograma</li>\r\n    <li>Prova de esforço</li>\r\n    <li>Acompanhamento de hipertensão</li>\r\n</ul>\r\n\r\n<h2 class="text-2xl font-semibold mb-4 text-[#09A2AE]">Quando devo procurar um cardiologista?</h2>\r\n<p class="text-gray-900 leading-relaxed mb-10 text-lg">\r\n    Dor no peito, falta de ar, palpitações, tonturas ou histórico familiar de doenças cardíacas.\r\n</p>\r\n', 1),
	(2, 'Dermatologia', 'Pele e anexos cutâneos', 'dermatologia.jpg', '<h2 class="text-2xl font-semibold mb-4 text-[#09A2AE]">O que é a Dermatologia?</h2>\r\n<p class="text-gray-700 leading-relaxed mb-6 text-lg">\r\n    A dermatologia dedica-se ao diagnóstico e tratamento de doenças da pele, cabelo e unhas.\r\n</p>\r\n\r\n<p class="text-gray-900 leading-relaxed mb-10 text-lg">\r\n    Entre as condições mais comuns estão: acne, psoríase, eczema, alergias cutâneas, infecções da pele e avaliação de sinais.\r\n</p>\r\n\r\n<h2 class="text-2xl font-semibold mb-4 text-[#09A2AE]">Principais serviços:</h2>\r\n<ul class="list-disc pl-6 text-gray-900 mb-6 text-lg space-y-2">\r\n    <li>Consulta dermatológica</li>\r\n    <li>Dermatoscopia</li>\r\n    <li>Tratamento de acne</li>\r\n    <li>Tratamento de manchas</li>\r\n    <li>Avaliação de sinais e lesões</li>\r\n</ul>\r\n\r\n<h2 class="text-2xl font-semibold mb-4 text-[#09A2AE]">Quando devo procurar um dermatologista?</h2>\r\n<p class="text-gray-900 leading-relaxed mb-10 text-lg">\r\n    Em caso de manchas novas, alterações em sinais, comichão persistente, queda excessiva de cabelo ou problemas crónicos de pele.\r\n</p>\r\n', 1),
	(3, 'Ortopedia', 'Sistema músculo-esquelético', 'ortopedia.png', '<h2 class="text-2xl font-semibold mb-4 text-[#09A2AE]">O que é a Ortopedia?</h2>\r\n<p class="text-gray-700 leading-relaxed mb-6 text-lg">\r\n    A ortopedia é a especialidade responsável por diagnosticar e tratar doenças e lesões dos ossos, articulações, músculos, tendões e ligamentos.\r\n</p>\r\n\r\n<p class="text-gray-900 leading-relaxed mb-10 text-lg">\r\n    Inclui fraturas, dores articulares, lesões desportivas, problemas da coluna, deformações e doenças degenerativas.\r\n</p>\r\n\r\n<h2 class="text-2xl font-semibold mb-4 text-[#09A2AE]">Principais serviços:</h2>\r\n<ul class="list-disc pl-6 text-gray-900 mb-6 text-lg space-y-2">\r\n    <li>Consulta ortopédica</li>\r\n    <li>Imobilizações e acompanhamento de fraturas</li>\r\n    <li>Tratamento de lesões desportivas</li>\r\n    <li>Infiltrações</li>\r\n    <li>Reabilitação e fisioterapia</li>\r\n</ul>\r\n\r\n<h2 class="text-2xl font-semibold mb-4 text-[#09A2AE]">Quando devo procurar um ortopedista?</h2>\r\n<p class="text-gray-900 leading-relaxed mb-10 text-lg">\r\n    Dor persistente, inflamação, limitação de movimento, traumas recentes ou suspeita de fratura.\r\n</p>\r\n', 1),
	(4, 'Pediatria', 'Saúde infantil', 'pediatria.jpg', '<h2 class="text-2xl font-semibold mb-4 text-[#09A2AE]">O que é a Pediatria?</h2>\r\n<p class="text-gray-700 leading-relaxed mb-6 text-lg">\r\n    A pediatria acompanha a saúde de bebés, crianças e adolescentes, assegurando o seu crescimento e desenvolvimento saudável.\r\n</p>\r\n\r\n<p class="text-gray-900 leading-relaxed mb-10 text-lg">\r\n    Abrange vacinação, infeções infantis, nutrição, alergias, desenvolvimento motor, emocional e vigilância do bem-estar geral.\r\n</p>\r\n\r\n<h2 class="text-2xl font-semibold mb-4 text-[#09A2AE]">Principais serviços:</h2>\r\n<ul class="list-disc pl-6 text-gray-900 mb-6 text-lg space-y-2">\r\n    <li>Consulta de pediatria</li>\r\n    <li>Vacinação</li>\r\n    <li>Monitorização do crescimento</li>\r\n    <li>Tratamento de infeções</li>\r\n    <li>Aconselhamento nutricional e comportamental</li>\r\n</ul>\r\n\r\n<h2 class="text-2xl font-semibold mb-4 text-[#09A2AE]">Quando devo procurar um pediatra?</h2>\r\n<p class="text-gray-900 leading-relaxed mb-10 text-lg">\r\n    Febre persistente, dificuldades respiratórias, sintomas prolongados, alterações no comportamento ou dúvidas sobre crescimento e vacinação.\r\n</p>\r\n', 1),
	(5, 'Oftalmologia', 'Diagnóstico e tratamento das doenças dos olhos', 'oftalmologista.jpg', '<h2 class="text-2xl font-semibold mb-4 text-[#09A2AE]">O que é a Oftalmologia?</h2>\r\n<p class="text-gray-700 leading-relaxed mb-6 text-lg">\r\n    A oftalmologia dedica-se ao estudo, diagnóstico e tratamento das doenças dos olhos e do sistema visual.\r\n</p>\r\n\r\n<p class="text-gray-900 leading-relaxed mb-10 text-lg">\r\n    Abrange condições como miopia, astigmatismo, cataratas, glaucoma, alergias oculares, infecções e doenças da retina.\r\n</p>\r\n\r\n<h2 class="text-2xl font-semibold mb-4 text-[#09A2AE]">Principais serviços:</h2>\r\n<ul class="list-disc pl-6 text-gray-900 mb-6 text-lg space-y-2">\r\n    <li>Consulta oftalmológica</li>\r\n    <li>Avaliação da visão e prescrição de óculos</li>\r\n    <li>Exame da retina</li>\r\n    <li>Tratamento de infecções oculares</li>\r\n    <li>Acompanhamento de glaucoma e catarata</li>\r\n</ul>\r\n\r\n<h2 class="text-2xl font-semibold mb-4 text-[#09A2AE]">Quando devo procurar um oftalmologista?</h2>\r\n<p class="text-gray-900 leading-relaxed mb-10 text-lg">\r\n    Visão turva, dores oculares, olho vermelho persistente, sensibilidade à luz ou alterações súbitas da visão.\r\n</p>\r\n', 1);

-- A despejar estrutura para tabela web2.exame
CREATE TABLE IF NOT EXISTS `exame` (
  `id` int NOT NULL AUTO_INCREMENT,
  `consulta_id` int NOT NULL,
  `tipo_exame` varchar(150) NOT NULL,
  `observacoes` text,
  `codigo` varchar(24) NOT NULL,
  `estado` enum('pedido','marcado','realizado','cancelado') DEFAULT 'pedido',
  PRIMARY KEY (`id`),
  KEY `fk_exame_consulta` (`consulta_id`),
  CONSTRAINT `fk_exame_consulta` FOREIGN KEY (`consulta_id`) REFERENCES `consulta` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- A despejar dados para tabela web2.exame: ~6 rows (aproximadamente)
INSERT INTO `exame` (`id`, `consulta_id`, `tipo_exame`, `observacoes`, `codigo`, `estado`) VALUES
	(1, 1, 'ECG', 'Avaliação de dores no peito', '', 'pedido'),
	(2, 2, 'Análise ao sangue', 'Check-up geral', '', 'pedido'),
	(3, 3, 'Raio-X do joelho', 'Suspeita de entorse', '', 'pedido'),
	(5, 22, 'raio-x', 'CAbrça', '920829887564041337713753', 'pedido'),
	(8, 28, 'Análise de sangue completa', 'Verificar níveis de hemoglobina e glicose', '891141564643320171785741', 'pedido'),
	(9, 27, 'RAio-X', NULL, '392378246947817023853554', 'pedido');

-- A despejar estrutura para tabela web2.feedback
CREATE TABLE IF NOT EXISTS `feedback` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utente_id` int NOT NULL,
  `nome` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estrelas` tinyint unsigned NOT NULL,
  `comentario` text COLLATE utf8mb4_unicode_ci,
  `criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_feedback_utente` (`utente_id`),
  KEY `idx_feedback_data` (`criado_em`),
  CONSTRAINT `fk_feedback_utente` FOREIGN KEY (`utente_id`) REFERENCES `utente` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A despejar dados para tabela web2.feedback: ~4 rows (aproximadamente)
INSERT INTO `feedback` (`id`, `utente_id`, `nome`, `estrelas`, `comentario`, `criado_em`) VALUES
	(5, 8, 'João Oliveira', 5, 'Hospital bem organizado, com profissionais atentos e competentes. Instalações limpas e funcionais, ambiente seguro e tempos de espera aceitáveis.', '2026-01-11 20:20:57'),
	(6, 3, 'Carla Mendes', 4, 'Hospital moderno e funcional, com equipa competente e atenciosa. Instalações limpas e organizadas, proporcionando conforto e segurança aos utentes.', '2026-01-12 02:21:04'),
	(7, 1, 'Maria Ferreira', 3, 'Hospital razoável, com atendimento cordial, embora por vezes demorado. Instalações limpas, mas algumas áreas carecem de manutenção e atualização.', '2026-01-12 02:27:59'),
	(8, 15, 'Luis Carlos Nobre Figueira', 5, 'teste', '2026-01-12 19:34:42');

-- A despejar estrutura para tabela web2.horario_medico
CREATE TABLE IF NOT EXISTS `horario_medico` (
  `id` int NOT NULL AUTO_INCREMENT,
  `medico_id` int NOT NULL,
  `dia_semana` enum('seg','ter','qua','qui','sex','sab','dom') NOT NULL,
  `turno` enum('M','T','N','F') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_medico_dia` (`medico_id`,`dia_semana`),
  CONSTRAINT `horario_medico_ibfk_1` FOREIGN KEY (`medico_id`) REFERENCES `medico` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- A despejar dados para tabela web2.horario_medico: ~7 rows (aproximadamente)
INSERT INTO `horario_medico` (`id`, `medico_id`, `dia_semana`, `turno`) VALUES
	(1, 15, 'seg', 'M'),
	(2, 15, 'ter', 'T'),
	(3, 15, 'qua', 'T'),
	(4, 15, 'qui', 'T'),
	(5, 15, 'sex', 'T'),
	(6, 15, 'sab', 'T'),
	(7, 15, 'dom', 'N');

-- A despejar estrutura para tabela web2.medico
CREATE TABLE IF NOT EXISTS `medico` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `num_cedula` varchar(50) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `telemovel` varchar(20) DEFAULT NULL,
  `especialidade_id` int NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_medico_cedula` (`num_cedula`),
  KEY `fk_medico_especialidade` (`especialidade_id`),
  CONSTRAINT `fk_medico_especialidade` FOREIGN KEY (`especialidade_id`) REFERENCES `especialidade` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- A despejar dados para tabela web2.medico: ~15 rows (aproximadamente)
INSERT INTO `medico` (`id`, `nome`, `num_cedula`, `email`, `password_hash`, `telemovel`, `especialidade_id`, `ativo`) VALUES
	(1, 'Dr. João Martins', '12345', 'joao.martins@clinica.pt', 'teste', '912345678', 1, 1),
	(2, 'Dra. Ana Silva', '23456', 'ana.silva@clinica.pt', 'teste', '913456789', 1, 1),
	(3, 'Dr. Pedro Rocha', '34567', 'pedro.rocha@clinica.pt', 'teste', '914567890', 2, 1),
	(4, 'Dra. Marta Sousa', '45678', '1@1', '1', '915678901', 2, 1),
	(5, 'Dr. Luís Almeida', '56789', 'luis.almeida@clinica.pt', '', '916789012', 3, 1),
	(6, 'Dra. Sofia Pinto', '67890', 'sofia.pinto@clinica.pt', '', '917890123', 3, 1),
	(7, 'Dr. Rui Pedro', '78901', 'rui.pedro@clinica.pt', '', '918901234', 4, 1),
	(8, 'Dra. Carla Neves', '89012', 'carla.neves@clinica.pt', '', '919012345', 4, 1),
	(9, 'Dr. Hugo Santos', '90123', 'hugo.santos@clinica.pt', '', '910123456', 5, 1),
	(10, 'Dra. Rita Andrade', '01234', 'rita.andrade@clinica.pt', '', '911234567', 5, 1),
	(11, 'Dr. João Silveiro', '1234111', 'olagfgfgfg@gmail.com', '$2y$10$jqlOC5Lm5K2AzIfVNu6p2ONHsbKCPzf3OkdYXeVSOah.zD14ngSBS', '222220911', 2, 1),
	(12, 'Dr. Afonso Portugal', '122333', 'afonsoportugal@gmail.com', 'teste', '909090914', 1, 1),
	(13, 'Dr. Pedro Rodrigues', '1234111111', 'pedrorodrigues@gmail.com', '$2y$10$PVe4upO2A.gtxvDzJz8aWumpWcTtn5zWKL8TlyLJG7yjvhW7lpxYi', '912345676', 4, 0),
	(14, 'Luis Fogo', '123', 'luisfogo@gmail.com', '$2y$10$2UY8.4WASsiLZBNgk90gtOH7kcpAw2J.sbfO2sMTnq5z8hZ959uIi', '999999999', 2, 1),
	(15, 'Ricardo Pereia', '12977', 'ricardinhopereira@gmail.com', '$2y$10$z5SivA8BLOWxgTgb69qFdul/IzNOlIdfjZlUI0rxrYianQI5lFVhu', '934186921', 1, 1);

-- A despejar estrutura para tabela web2.receita
CREATE TABLE IF NOT EXISTS `receita` (
  `id` int NOT NULL AUTO_INCREMENT,
  `consulta_id` int NOT NULL,
  `medico_id` int NOT NULL,
  `utente_id` int NOT NULL,
  `data_emissao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `texto` text NOT NULL,
  `codigo` varchar(24) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_receita_consulta` (`consulta_id`),
  KEY `fk_receita_medico` (`medico_id`),
  KEY `fk_receita_utente` (`utente_id`),
  CONSTRAINT `fk_receita_consulta` FOREIGN KEY (`consulta_id`) REFERENCES `consulta` (`id`),
  CONSTRAINT `fk_receita_medico` FOREIGN KEY (`medico_id`) REFERENCES `medico` (`id`),
  CONSTRAINT `fk_receita_utente` FOREIGN KEY (`utente_id`) REFERENCES `utente` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- A despejar dados para tabela web2.receita: ~6 rows (aproximadamente)
INSERT INTO `receita` (`id`, `consulta_id`, `medico_id`, `utente_id`, `data_emissao`, `texto`, `codigo`) VALUES
	(1, 1, 1, 1, '2025-03-20 11:00:00', 'Atorvastatina 20mg – tomar 1 comprimido à noite', ''),
	(2, 2, 1, 2, '2025-03-20 12:00:00', 'Ibuprofeno 400mg – tomar de 8/8 horas após refeições', ''),
	(3, 3, 4, 3, '2025-03-22 15:00:00', 'Pomada dermatológica – aplicar 2 vezes por dia', ''),
	(4, 22, 4, 8, '2025-12-06 03:31:31', 'Medicamento: teste\nUnidades: a\nPosologia: a', '817579555128004254782468'),
	(5, 28, 1, 15, '2026-01-12 01:27:11', 'Medicamento: Ibuprofeno 400mg\nUnidades: 20 comprimidos\nPosologia: 1 comprimido a cada 8 horas durante 5 dias', '344868750060178440925689'),
	(6, 27, 1, 15, '2026-01-12 19:29:14', 'Medicamento: etste\nUnidades: 12\nPosologia: 8h', '540293634433008992804849');

-- A despejar estrutura para tabela web2.utente
CREATE TABLE IF NOT EXISTS `utente` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telemovel` varchar(20) DEFAULT NULL,
  `num_utente_saude` varchar(20) DEFAULT NULL,
  `nif` varchar(9) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email_verificado` tinyint(1) NOT NULL DEFAULT '0',
  `acordo_id` int DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `sexo` enum('M','F','Outro') DEFAULT NULL,
  `morada` varchar(255) DEFAULT NULL,
  `peso_kg` decimal(5,2) DEFAULT NULL,
  `altura_cm` int DEFAULT NULL,
  `imc` decimal(5,2) DEFAULT NULL,
  `tipo_sanguineo` varchar(3) DEFAULT NULL,
  `alergias` text,
  `observacoes` text,
  `foto` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_utente_email` (`email`),
  UNIQUE KEY `uq_utente_num_utente` (`num_utente_saude`),
  UNIQUE KEY `uq_utente_nif` (`nif`),
  KEY `fk_utente_acordo` (`acordo_id`),
  CONSTRAINT `fk_utente_acordo` FOREIGN KEY (`acordo_id`) REFERENCES `acordo` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- A despejar dados para tabela web2.utente: ~13 rows (aproximadamente)
INSERT INTO `utente` (`id`, `nome`, `email`, `telemovel`, `num_utente_saude`, `nif`, `data_nascimento`, `password_hash`, `email_verificado`, `acordo_id`, `created_at`, `updated_at`, `sexo`, `morada`, `peso_kg`, `altura_cm`, `imc`, `tipo_sanguineo`, `alergias`, `observacoes`, `foto`) VALUES
	(1, 'Maria Ferreira', 'maria.ferreira@mail.pt', '921111111', '123456789', '245678901', '1985-02-10', 'teste', 0, 2, '2025-11-14 21:13:28', '2025-11-18 20:28:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(2, 'José Oliveira', 'jose.oliveira@mail.pt', '922222222', '987654321', '198765432', '1990-07-25', 'teste', 0, 1, '2025-11-14 21:13:28', '2025-11-18 20:28:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(3, 'Carla Mendes', 'carla.mendes@mail.pt', '923333333', NULL, NULL, '2000-12-14', 'teste', 0, 4, '2025-11-14 21:13:28', '2025-12-16 21:25:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'imagens/utentes/utente_3_d0a7fbf535daa767.jpeg'),
	(4, 'efeefefefefe', 'clashclashclashw@gmail.com', '999999999', '122121212121', NULL, NULL, 'teste', 0, 1, '2025-11-14 21:32:26', '2025-11-18 20:28:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(6, 'luis', 'a2022160309@alumni.iscac.pt', '999999999', 'dededede', NULL, NULL, 'teste', 0, NULL, '2025-11-15 04:07:44', '2025-11-18 20:28:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(7, 'deedede', 'wfefefefefe@gmal.com', '999999999', '', NULL, NULL, 'teste', 0, 1, '2025-11-15 04:12:42', '2025-11-18 20:28:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(8, 'João Oliveira', 'ola@gmail.com', '947179463', '12', '123', '2000-11-05', '$2y$10$TJcjDc8N5Q71y/amTzlqtO8tLNGifA7k4NlaGlZgPkArNIUGDTmri', 0, NULL, '2025-11-18 20:30:41', '2026-01-12 17:19:48', 'F', 'Ruas', 12.00, 16, 468.80, 'efe', 'efe', 'efe', NULL),
	(9, 'ede', 'eded@gmail.com', '987456375', NULL, NULL, NULL, '$2y$10$orqCJPOR//KGiaMwIMGwIepQSJpAgvBK1XaBX21LOz6u1OGxpHFwi', 0, NULL, '2025-12-06 02:28:37', '2026-01-12 17:19:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(11, 'João Silva11', 'ola1234@gmail.com', '988888888', '988888888', '123456789', '1978-12-29', '$2y$10$iKz2wkD5o8g2pjbMVsLslOzpwiDzNcqDbA8MO/GHsbgqD6/0OeY5i', 0, 4, '2025-12-16 20:27:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(12, 'João Silva11', 'ola123444@gmail.com', '988888884', '988888848', '245813691', '1978-12-29', '$2y$10$PW/6nnrKWJz.6BYLnxBHVe2woBfYZiHuR.SbuXlIhJq1jWUsUFKHu', 0, 2, '2025-12-16 20:36:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(13, 'João Silvade', 'ola1231114@gmail.com', '909020911', '909020911', '312000456', '2002-01-29', '$2y$10$iPXOJZI4HXaTvjIDk7TGSO7.E9UPbJNGFfnes9LMGPqPy4WsXZQpm', 0, 1, '2025-12-16 20:43:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(14, 'João Silva1234', 'ola909090@gmail.com', '919090911', '919090911', '501234560', '1991-03-07', '$2y$10$1oGR/3ZG1tYUuFntwSD7E.OGJS3EX8NLuL2ZPEfbp5oTgGxHk9VdK', 0, 1, '2025-12-16 20:46:08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
	(15, 'Luis Carlos Nobre Figueira', 'luisfigueira@gmail.com', '912345671', '909020913', '156422297', '2004-01-01', 'teste', 0, 3, '2026-01-12 00:50:13', '2026-01-12 19:30:20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
