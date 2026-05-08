/*
SQLyog Ultimate v12.4.3 (64 bit)
MySQL - 10.4.28-MariaDB : Database - lumen_db
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`lumen_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `lumen_db`;

/*Table structure for table `alertas` */

DROP TABLE IF EXISTS `alertas`;

CREATE TABLE `alertas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `situacao_id` int(10) unsigned NOT NULL DEFAULT 1,
  `descricao` varchar(255) NOT NULL,
  `data_alerta` date NOT NULL,
  `data_vencimento` date DEFAULT NULL,
  `icone` varchar(50) DEFAULT 'ph-bell',
  `cor` varchar(7) DEFAULT '#eab308',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_alertas_usuario` (`usuario_id`),
  KEY `fk_alertas_situacao` (`situacao_id`),
  CONSTRAINT `fk_alertas_situacao` FOREIGN KEY (`situacao_id`) REFERENCES `situacoes` (`id`),
  CONSTRAINT `fk_alertas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `caixinhas` */

DROP TABLE IF EXISTS `caixinhas`;

CREATE TABLE `caixinhas` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) unsigned NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `valor_meta` decimal(15,2) NOT NULL DEFAULT 0.00,
  `valor_atual` decimal(15,2) NOT NULL DEFAULT 0.00,
  `cor` varchar(7) DEFAULT '#0d6efd',
  `data_limite` date DEFAULT NULL,
  `situacao_id` int(11) unsigned DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_caixinhas_usuario_v2` (`usuario_id`),
  CONSTRAINT `fk_caixinhas_usuario_v2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Table structure for table `caixinhas_movimentacoes` */

DROP TABLE IF EXISTS `caixinhas_movimentacoes`;

CREATE TABLE `caixinhas_movimentacoes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `caixinha_id` int(10) unsigned NOT NULL,
  `tipo` enum('entrada','saida') NOT NULL,
  `valor` decimal(15,2) NOT NULL,
  `observacao` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_caixinha_mov` (`caixinha_id`),
  CONSTRAINT `fk_caixinha_movimentacao` FOREIGN KEY (`caixinha_id`) REFERENCES `caixinhas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Table structure for table `cartoes_credito` */

DROP TABLE IF EXISTS `cartoes_credito`;

CREATE TABLE `cartoes_credito` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) unsigned NOT NULL,
  `situacao_id` int(10) unsigned NOT NULL DEFAULT 1,
  `nome` varchar(50) NOT NULL,
  `limite` decimal(15,2) NOT NULL,
  `dia_vencimento` int(2) NOT NULL,
  `dia_fechamento` int(2) NOT NULL,
  `cor` varchar(7) DEFAULT '#8A05BE',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_cartoes_usuario` (`usuario_id`),
  KEY `fk_cartoes_situacao` (`situacao_id`),
  CONSTRAINT `fk_cartoes_situacao` FOREIGN KEY (`situacao_id`) REFERENCES `situacoes` (`id`),
  CONSTRAINT `fk_cartoes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Table structure for table `categorias` */

DROP TABLE IF EXISTS `categorias`;

CREATE TABLE `categorias` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) unsigned NOT NULL,
  `situacao_id` int(10) unsigned NOT NULL DEFAULT 1,
  `nome` varchar(50) NOT NULL,
  `tipo` enum('entrada','saida') NOT NULL,
  `natureza` enum('fixo','variavel') NOT NULL DEFAULT 'variavel',
  `cor` varchar(7) DEFAULT '#6c757d',
  `icone` varchar(50) DEFAULT 'ph-tag',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_categorias_usuario` (`usuario_id`),
  KEY `fk_categorias_situacao` (`situacao_id`),
  CONSTRAINT `fk_categorias_situacao` FOREIGN KEY (`situacao_id`) REFERENCES `situacoes` (`id`),
  CONSTRAINT `fk_categorias_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Table structure for table `contas_bancarias` */

DROP TABLE IF EXISTS `contas_bancarias`;

CREATE TABLE `contas_bancarias` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) unsigned NOT NULL,
  `situacao_id` int(10) unsigned NOT NULL DEFAULT 1,
  `nome` varchar(50) NOT NULL,
  `tipo` enum('corrente','poupanca','investimento','dinheiro') NOT NULL DEFAULT 'corrente',
  `saldo_atual` decimal(15,2) NOT NULL DEFAULT 0.00,
  `cor` varchar(7) DEFAULT '#1C325F',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_contas_usuario` (`usuario_id`),
  KEY `fk_contas_situacao` (`situacao_id`),
  CONSTRAINT `fk_contas_situacao` FOREIGN KEY (`situacao_id`) REFERENCES `situacoes` (`id`),
  CONSTRAINT `fk_contas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Table structure for table `fornecedores` */

DROP TABLE IF EXISTS `fornecedores`;

CREATE TABLE `fornecedores` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) unsigned NOT NULL,
  `situacao_id` int(10) unsigned NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `tipo` enum('pf','pj') DEFAULT 'pj',
  `documento` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `chave_pix` varchar(255) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_fornecedores_usuario` (`usuario_id`),
  KEY `fk_fornecedor_situacao` (`situacao_id`),
  CONSTRAINT `fk_fornecedor_situacao` FOREIGN KEY (`situacao_id`) REFERENCES `situacoes` (`id`),
  CONSTRAINT `fk_fornecedores_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Table structure for table `lancamentos` */

DROP TABLE IF EXISTS `lancamentos`;

CREATE TABLE `lancamentos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `fornecedor_id` int(10) unsigned DEFAULT NULL,
  `conta_id` int(10) unsigned DEFAULT NULL,
  `cartao_id` int(10) unsigned DEFAULT NULL,
  `categoria_id` int(10) unsigned NOT NULL,
  `situacao_id` int(10) unsigned NOT NULL DEFAULT 1,
  `descricao` varchar(255) NOT NULL,
  `data_transacao` date DEFAULT NULL,
  `valor` decimal(15,2) NOT NULL,
  `data_vencimento` date NOT NULL,
  `data_pagamento` date DEFAULT NULL,
  `tipo` enum('entrada','saida') NOT NULL,
  `status` enum('aberto','pendente','pago') NOT NULL DEFAULT 'aberto',
  `forma_pagamento` enum('dinheiro','pix','boleto','transferencia','cartao_credito') NOT NULL DEFAULT 'pix',
  `observacao` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_lancamentos_usuario` (`usuario_id`),
  KEY `fk_lancamentos_conta` (`conta_id`),
  KEY `fk_lancamentos_categoria` (`categoria_id`),
  KEY `fk_lancamentos_situacao` (`situacao_id`),
  KEY `fk_lancamentos_cartao` (`cartao_id`),
  KEY `fk_lancamentos_fornecedor` (`fornecedor_id`),
  CONSTRAINT `fk_lancamentos_cartao` FOREIGN KEY (`cartao_id`) REFERENCES `cartoes_credito` (`id`),
  CONSTRAINT `fk_lancamentos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  CONSTRAINT `fk_lancamentos_conta` FOREIGN KEY (`conta_id`) REFERENCES `contas_bancarias` (`id`),
  CONSTRAINT `fk_lancamentos_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`),
  CONSTRAINT `fk_lancamentos_situacao` FOREIGN KEY (`situacao_id`) REFERENCES `situacoes` (`id`),
  CONSTRAINT `fk_lancamentos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `planejamento_itens` */

DROP TABLE IF EXISTS `planejamento_itens`;

CREATE TABLE `planejamento_itens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `planejamento_id` int(11) NOT NULL,
  `categoria_id` int(10) unsigned NOT NULL,
  `percentual` decimal(5,2) NOT NULL DEFAULT 0.00,
  `valor_planejado` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `planejamento_id` (`planejamento_id`),
  KEY `categoria_id` (`categoria_id`),
  CONSTRAINT `planejamento_itens_ibfk_1` FOREIGN KEY (`planejamento_id`) REFERENCES `planejamentos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `planejamento_itens_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `planejamentos` */

DROP TABLE IF EXISTS `planejamentos`;

CREATE TABLE `planejamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mes` int(11) NOT NULL,
  `ano` int(11) NOT NULL,
  `receita_base` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_mes_ano` (`mes`,`ano`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `situacoes` */

DROP TABLE IF EXISTS `situacoes`;

CREATE TABLE `situacoes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `usuarios` */

DROP TABLE IF EXISTS `usuarios`;

CREATE TABLE `usuarios` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `situacao_id` int(11) unsigned NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nivel` enum('admin','usuario') NOT NULL DEFAULT 'usuario',
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_usuario_situacao` (`situacao_id`),
  CONSTRAINT `fk_usuario_situacao` FOREIGN KEY (`situacao_id`) REFERENCES `situacoes` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
