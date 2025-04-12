-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 17-Mar-2025 às 12:19
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `boleiasbd`
--
CREATE DATABASE IF NOT EXISTS boleiasbd;
USE boleiasbd;
-- --------------------------------------------------------

--
-- Estrutura da tabela `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contacto` varchar(9) DEFAULT NULL,
  `password` varchar(100) NOT NULL,
  `perfil` int(11) NOT NULL DEFAULT 1 COMMENT 'admin - 1; user - 2'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Extraindo dados da tabela `admin`
--

INSERT INTO `admin` (`admin_id`, `nome`, `email`, `contacto`, `password`, `perfil`) VALUES
(1, 'Administrador', 'admin@email.com', NULL, '$2y$10$460WblRlk7HX5Rd8UMcfOef/ahOTvTbypBPNt1x0lMgyu4rkA1Jeu', 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `avaliacao`
--

CREATE TABLE `avaliacao` (
  `avaliacao_id` int(11) NOT NULL,
  `nota` tinyint(4) NOT NULL CHECK (`nota` between 1 and 5),
  `comentarios` text DEFAULT NULL,
  `utilizador_id` int(11) NOT NULL,
  `boleia_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `boleia`
--

CREATE TABLE `boleia` (
  `boleia_id` int(11) NOT NULL,
  `ponto_partida` varchar(100) NOT NULL,
  `destino` varchar(100) NOT NULL,
  `total_pessoas` int(11) NOT NULL,
  `data` date NOT NULL,
  `horario` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `pesquisa_recente`
--

CREATE TABLE `pesquisa_recente` (
  `pesquisa_id` int(11) NOT NULL,
  `utilizador_id` int(11) NOT NULL,
  `ponto_partida` varchar(100) NOT NULL,
  `destino` varchar(100) NOT NULL,
  `data_pesquisa` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizador`
--

CREATE TABLE `utilizador` (
  `utilizador_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `contacto` varchar(9) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `morada` varchar(255) DEFAULT NULL,
  `codigo_postal` char(8) DEFAULT NULL,
  `localidade` varchar(160) NOT NULL,
  `password` varchar(100) NOT NULL,
  `perfil` int(11) NOT NULL DEFAULT 2 COMMENT 'admin - 1; user - 2'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Extraindo dados da tabela `utilizador`
--

INSERT INTO `utilizador` (`utilizador_id`, `nome`, `email`, `contacto`, `morada`, `codigo_postal`, `localidade`, `password`, `perfil`) VALUES
(1, 'Rafael Figueiredo', 'rafafigueiredo907@gmail.com', '961177199', 'Rua da Fonte, nº10', '3510-585', 'Viseu', '$2y$10$VuG5PraLz0olh8D6YBWr/u6Q27Al4ivdMQ55/zOlaUo9efyPxOGaO', 2);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices para tabela `avaliacao`
--
ALTER TABLE `avaliacao`
  ADD PRIMARY KEY (`avaliacao_id`),
  ADD KEY `avaliacao_ibfk_1` (`utilizador_id`),
  ADD KEY `avaliacao_ibfk_2` (`boleia_id`);

--
-- Índices para tabela `boleia`
--
ALTER TABLE `boleia`
  ADD PRIMARY KEY (`boleia_id`);

--
-- Índices para tabela `pesquisa_recente`
--
ALTER TABLE `pesquisa_recente`
  ADD PRIMARY KEY (`pesquisa_id`),
  ADD KEY `pesquisa_recente_ibfk_1` (`utilizador_id`);

--
-- Índices para tabela `utilizador`
--
ALTER TABLE `utilizador`
  ADD PRIMARY KEY (`utilizador_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `avaliacao`
--
ALTER TABLE `avaliacao`
  MODIFY `avaliacao_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `boleia`
--
ALTER TABLE `boleia`
  MODIFY `boleia_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pesquisa_recente`
--
ALTER TABLE `pesquisa_recente`
  MODIFY `pesquisa_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `utilizador`
--
ALTER TABLE `utilizador`
  MODIFY `utilizador_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `avaliacao`
--
ALTER TABLE `avaliacao`
  ADD CONSTRAINT `avaliacao_ibfk_1` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizador` (`utilizador_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `avaliacao_ibfk_2` FOREIGN KEY (`boleia_id`) REFERENCES `boleia` (`boleia_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Limitadores para a tabela `pesquisa_recente`
--
ALTER TABLE `pesquisa_recente`
  ADD CONSTRAINT `pesquisa_recente_ibfk_1` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizador` (`utilizador_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
