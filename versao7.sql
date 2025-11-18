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

-- A despejar estrutura para tabela web2.ausencia_medico
CREATE TABLE IF NOT EXISTS `ausencia_medico` (
  `id` int NOT NULL AUTO_INCREMENT,
  `medico_id` int NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `tipo` enum('ferias','baixa','formacao','outro') DEFAULT 'outro',
  PRIMARY KEY (`id`),
  KEY `idx_ausencia_medico_data` (`medico_id`,`data_inicio`,`data_fim`),
  CONSTRAINT `fk_ausencia_medico` FOREIGN KEY (`medico_id`) REFERENCES `medico` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- A despejar dados para tabela web2.ausencia_medico: ~2 rows (aproximadamente)
INSERT INTO `ausencia_medico` (`id`, `medico_id`, `data_inicio`, `data_fim`, `motivo`, `tipo`) VALUES
	(1, 1, '2025-03-10', '2025-03-15', 'Férias', 'ferias'),
	(2, 5, '2025-04-05', '2025-04-06', 'Congresso Médico', 'formacao');

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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- A despejar dados para tabela web2.consulta: ~12 rows (aproximadamente)
INSERT INTO `consulta` (`id`, `medico_id`, `utente_id`, `data_consulta`, `hora_inicio`, `hora_fim`, `estado`, `acordo_id`, `tipo`, `observacoes`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, '2025-03-20', '10:00:00', '11:00:00', 'marcada', 2, 'presencial', 'Consulta de rotina', '2025-11-14 21:13:28', NULL),
	(2, 1, 2, '2025-03-20', '11:00:00', '12:00:00', 'marcada', 1, 'presencial', 'Avaliação de tensão arterial', '2025-11-14 21:13:28', NULL),
	(3, 4, 3, '2025-03-22', '14:00:00', '15:00:00', 'marcada', 4, 'presencial', 'Queixas dermatológicas', '2025-11-14 21:13:28', NULL),
	(4, 9, 4, '2025-11-21', '10:00:00', '10:30:00', 'marcada', 1, 'presencial', 'efefe', '2025-11-14 21:32:26', NULL),
	(6, 9, 6, '2025-11-21', '09:00:00', '09:30:00', 'marcada', NULL, 'presencial', '', '2025-11-15 04:07:44', NULL),
	(7, 9, 7, '2025-11-21', '11:00:00', '11:30:00', 'marcada', 1, 'presencial', '', '2025-11-15 04:12:42', NULL),
	(12, 9, 4, '2025-11-21', '14:00:00', '14:30:00', 'marcada', NULL, 'presencial', 'dede', '2025-11-17 13:38:35', NULL),
	(13, 7, 8, '2025-11-21', '09:00:00', '09:30:00', 'marcada', NULL, 'presencial', '', '2025-11-18 20:45:02', NULL),
	(16, 9, 8, '2025-11-21', '15:00:00', '15:30:00', 'marcada', NULL, 'presencial', '', '2025-11-18 20:48:01', NULL),
	(18, 7, 8, '2025-11-21', '14:00:00', '14:30:00', 'marcada', NULL, 'presencial', '', '2025-11-18 20:51:24', NULL),
	(19, 2, 8, '2025-11-21', '17:00:00', '17:30:00', 'marcada', NULL, 'presencial', '', '2025-11-18 22:15:49', NULL),
	(20, 8, 8, '2025-11-20', '16:00:00', '16:30:00', 'marcada', NULL, 'presencial', '', '2025-11-18 22:16:13', NULL);

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
  `estado` enum('pedido','marcado','realizado','cancelado') DEFAULT 'pedido',
  PRIMARY KEY (`id`),
  KEY `fk_exame_consulta` (`consulta_id`),
  CONSTRAINT `fk_exame_consulta` FOREIGN KEY (`consulta_id`) REFERENCES `consulta` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- A despejar dados para tabela web2.exame: ~3 rows (aproximadamente)
INSERT INTO `exame` (`id`, `consulta_id`, `tipo_exame`, `observacoes`, `estado`) VALUES
	(1, 1, 'ECG', 'Avaliação de dores no peito', 'pedido'),
	(2, 2, 'Análise ao sangue', 'Check-up geral', 'pedido'),
	(3, 3, 'Raio-X do joelho', 'Suspeita de entorse', 'pedido');

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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- A despejar dados para tabela web2.medico: ~10 rows (aproximadamente)
INSERT INTO `medico` (`id`, `nome`, `num_cedula`, `email`, `password_hash`, `telemovel`, `especialidade_id`, `ativo`) VALUES
	(1, 'Dr. João Martins', '12345', 'joao.martins@clinica.pt', 'teste', '912345678', 1, 1),
	(2, 'Dra. Ana Silva', '23456', 'ana.silva@clinica.pt', 'teste', '913456789', 1, 1),
	(3, 'Dr. Pedro Rocha', '34567', 'pedro.rocha@clinica.pt', 'teste', '914567890', 2, 1),
	(4, 'Dra. Marta Sousa', '45678', 'marta.sousa@clinica.pt', '', '915678901', 2, 1),
	(5, 'Dr. Luís Almeida', '56789', 'luis.almeida@clinica.pt', '', '916789012', 3, 1),
	(6, 'Dra. Sofia Pinto', '67890', 'sofia.pinto@clinica.pt', '', '917890123', 3, 1),
	(7, 'Dr. Rui Pedro', '78901', 'rui.pedro@clinica.pt', '', '918901234', 4, 1),
	(8, 'Dra. Carla Neves', '89012', 'carla.neves@clinica.pt', '', '919012345', 4, 1),
	(9, 'Dr. Hugo Santos', '90123', 'hugo.santos@clinica.pt', '', '910123456', 5, 1),
	(10, 'Dra. Rita Andrade', '01234', 'rita.andrade@clinica.pt', '', '911234567', 5, 1);

-- A despejar estrutura para tabela web2.receita
CREATE TABLE IF NOT EXISTS `receita` (
  `id` int NOT NULL AUTO_INCREMENT,
  `consulta_id` int NOT NULL,
  `medico_id` int NOT NULL,
  `utente_id` int NOT NULL,
  `data_emissao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `texto` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_receita_consulta` (`consulta_id`),
  KEY `fk_receita_medico` (`medico_id`),
  KEY `fk_receita_utente` (`utente_id`),
  CONSTRAINT `fk_receita_consulta` FOREIGN KEY (`consulta_id`) REFERENCES `consulta` (`id`),
  CONSTRAINT `fk_receita_medico` FOREIGN KEY (`medico_id`) REFERENCES `medico` (`id`),
  CONSTRAINT `fk_receita_utente` FOREIGN KEY (`utente_id`) REFERENCES `utente` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- A despejar dados para tabela web2.receita: ~3 rows (aproximadamente)
INSERT INTO `receita` (`id`, `consulta_id`, `medico_id`, `utente_id`, `data_emissao`, `texto`) VALUES
	(1, 1, 1, 1, '2025-03-20 11:00:00', 'Atorvastatina 20mg – tomar 1 comprimido à noite'),
	(2, 2, 1, 2, '2025-03-20 12:00:00', 'Ibuprofeno 400mg – tomar de 8/8 horas após refeições'),
	(3, 3, 4, 3, '2025-03-22 15:00:00', 'Pomada dermatológica – aplicar 2 vezes por dia');

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_utente_email` (`email`),
  UNIQUE KEY `uq_utente_num_utente` (`num_utente_saude`),
  UNIQUE KEY `uq_utente_nif` (`nif`),
  KEY `fk_utente_acordo` (`acordo_id`),
  CONSTRAINT `fk_utente_acordo` FOREIGN KEY (`acordo_id`) REFERENCES `acordo` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- A despejar dados para tabela web2.utente: ~7 rows (aproximadamente)
INSERT INTO `utente` (`id`, `nome`, `email`, `telemovel`, `num_utente_saude`, `nif`, `data_nascimento`, `password_hash`, `email_verificado`, `acordo_id`, `created_at`, `updated_at`) VALUES
	(1, 'Maria Ferreira', 'maria.ferreira@mail.pt', '921111111', '123456789', '245678901', '1985-02-10', 'teste', 0, 2, '2025-11-14 21:13:28', '2025-11-18 20:28:42'),
	(2, 'José Oliveira', 'jose.oliveira@mail.pt', '922222222', '987654321', '198765432', '1990-07-25', 'teste', 0, 1, '2025-11-14 21:13:28', '2025-11-18 20:28:43'),
	(3, 'Carla Mendes', 'carla.mendes@mail.pt', '923333333', NULL, NULL, '2000-12-14', 'teste', 0, 4, '2025-11-14 21:13:28', '2025-11-18 20:28:44'),
	(4, 'efeefefefefe', 'clashclashclashw@gmail.com', '999999999', '122121212121', NULL, NULL, 'teste', 0, 1, '2025-11-14 21:32:26', '2025-11-18 20:28:44'),
	(6, 'luis', 'a2022160309@alumni.iscac.pt', '999999999', 'dededede', NULL, NULL, 'teste', 0, NULL, '2025-11-15 04:07:44', '2025-11-18 20:28:45'),
	(7, 'deedede', 'wfefefefefe@gmal.com', '999999999', '', NULL, NULL, 'teste', 0, 1, '2025-11-15 04:12:42', '2025-11-18 20:28:45'),
	(8, 'ola', 'ola@gmail.com', '2', '12', '123', '2025-11-05', '$2y$10$TJcjDc8N5Q71y/amTzlqtO8tLNGifA7k4NlaGlZgPkArNIUGDTmri', 0, NULL, '2025-11-18 20:30:41', NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
