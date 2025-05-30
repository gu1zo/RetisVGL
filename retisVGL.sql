-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Tempo de geração: 30/05/2025 às 17:02
-- Versão do servidor: 11.7.2-MariaDB-ubu2404
-- Versão do PHP: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `retisVGL`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `alteracoes`
--

CREATE TABLE `alteracoes` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `alteracao` varchar(255) NOT NULL,
  `data` datetime NOT NULL,
  `id_usuario_criador` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `backbone`
--

CREATE TABLE `backbone` (
  `evento_id` int(11) NOT NULL,
  `backbone` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cidades`
--

CREATE TABLE `cidades` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `massiva` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `comentarios`
--

CREATE TABLE `comentarios` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `comentario` varchar(1500) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `data` datetime NOT NULL,
  `id_usuario_criador` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `eventos`
--

CREATE TABLE `eventos` (
  `id` int(11) NOT NULL,
  `tipo` enum('evento','manutencao','emergencial','backbone') NOT NULL,
  `protocolo` int(11) NOT NULL,
  `dataInicio` datetime DEFAULT NULL,
  `dataFim` datetime DEFAULT NULL,
  `regional` varchar(99) NOT NULL,
  `observacao` varchar(3000) DEFAULT NULL,
  `status` enum('em analise','reagendado','pendente','em execucao','concluido') NOT NULL DEFAULT 'em execucao',
  `email` tinyint(1) NOT NULL DEFAULT 0,
  `clientes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]',
  `id_usuario_criador` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `evento_conclusao`
--

CREATE TABLE `evento_conclusao` (
  `evento_id` int(11) NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `forca_maior` tinyint(1) NOT NULL,
  `comentario` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `fila`
--

CREATE TABLE `fila` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `codsercli` varchar(255) NOT NULL,
  `protocolo_sz` varchar(255) NOT NULL,
  `numero` varchar(255) NOT NULL,
  `cpf_cnpj` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `fila_emails`
--

CREATE TABLE `fila_emails` (
  `id` int(11) NOT NULL,
  `protocolo` int(11) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `vars` text DEFAULT NULL,
  `cliente_email` varchar(255) DEFAULT NULL,
  `cliente_nome` varchar(255) DEFAULT NULL,
  `status` enum('pendente','enviado','erro') DEFAULT 'pendente',
  `enviado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs_callback`
--

CREATE TABLE `logs_callback` (
  `id` int(11) NOT NULL,
  `protocolo` varchar(255) NOT NULL,
  `protocolo_sz` varchar(255) NOT NULL,
  `error` varchar(500) NOT NULL,
  `status` enum('concluido','novo') NOT NULL DEFAULT 'novo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `manutencoes`
--

CREATE TABLE `manutencoes` (
  `evento_id` int(11) NOT NULL,
  `dataPrevista` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `massivas`
--

CREATE TABLE `massivas` (
  `id` int(11) NOT NULL,
  `protocolo_sz` varchar(255) NOT NULL,
  `numero` varchar(255) NOT NULL,
  `id_massiva` int(11) DEFAULT NULL,
  `codsercli` varchar(255) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `cpf_cnpj` varchar(255) NOT NULL,
  `avisado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pontos_acesso`
--

CREATE TABLE `pontos_acesso` (
  `id` int(11) NOT NULL,
  `codigo` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `latitude` varchar(255) NOT NULL,
  `longitude` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pontos_acesso_afetados`
--

CREATE TABLE `pontos_acesso_afetados` (
  `evento_id` int(11) NOT NULL,
  `ponto_acesso_codigo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `proatividade`
--

CREATE TABLE `proatividade` (
  `id` int(11) NOT NULL,
  `protocolo` varchar(45) NOT NULL,
  `data` datetime NOT NULL,
  `regional` varchar(45) NOT NULL,
  `host` varchar(45) NOT NULL,
  `id_usuario_criador` int(11) NOT NULL,
  `observacao` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `setor` varchar(255) NOT NULL,
  `privilegio` enum('admin','normal') NOT NULL,
  `recovery_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `alteracoes`
--
ALTER TABLE `alteracoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evento_id` (`evento_id`),
  ADD KEY `id_usuario_criador` (`id_usuario_criador`);

--
-- Índices de tabela `backbone`
--
ALTER TABLE `backbone`
  ADD PRIMARY KEY (`evento_id`),
  ADD KEY `evento_id` (`evento_id`);

--
-- Índices de tabela `cidades`
--
ALTER TABLE `cidades`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evento_id` (`evento_id`),
  ADD KEY `id_usuario_criador` (`id_usuario_criador`);

--
-- Índices de tabela `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario_criador` (`id_usuario_criador`);

--
-- Índices de tabela `evento_conclusao`
--
ALTER TABLE `evento_conclusao`
  ADD PRIMARY KEY (`evento_id`);

--
-- Índices de tabela `fila`
--
ALTER TABLE `fila`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `fila_emails`
--
ALTER TABLE `fila_emails`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `logs_callback`
--
ALTER TABLE `logs_callback`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `manutencoes`
--
ALTER TABLE `manutencoes`
  ADD PRIMARY KEY (`evento_id`);

--
-- Índices de tabela `massivas`
--
ALTER TABLE `massivas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `pontos_acesso`
--
ALTER TABLE `pontos_acesso`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Índices de tabela `pontos_acesso_afetados`
--
ALTER TABLE `pontos_acesso_afetados`
  ADD KEY `ponto_acesso_codigo` (`ponto_acesso_codigo`),
  ADD KEY `evento_id` (`evento_id`);

--
-- Índices de tabela `proatividade`
--
ALTER TABLE `proatividade`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario_criador` (`id_usuario_criador`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `alteracoes`
--
ALTER TABLE `alteracoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cidades`
--
ALTER TABLE `cidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `fila`
--
ALTER TABLE `fila`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `fila_emails`
--
ALTER TABLE `fila_emails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `logs_callback`
--
ALTER TABLE `logs_callback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `massivas`
--
ALTER TABLE `massivas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pontos_acesso`
--
ALTER TABLE `pontos_acesso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `proatividade`
--
ALTER TABLE `proatividade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `alteracoes`
--
ALTER TABLE `alteracoes`
  ADD CONSTRAINT `alteracoes_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `alteracoes_ibfk_2` FOREIGN KEY (`id_usuario_criador`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `backbone`
--
ALTER TABLE `backbone`
  ADD CONSTRAINT `backbone_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `comentarios`
--
ALTER TABLE `comentarios`
  ADD CONSTRAINT `comentarios_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `comentarios_ibfk_2` FOREIGN KEY (`id_usuario_criador`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `eventos_ibfk_1` FOREIGN KEY (`id_usuario_criador`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `evento_conclusao`
--
ALTER TABLE `evento_conclusao`
  ADD CONSTRAINT `evento_conclusao_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `manutencoes`
--
ALTER TABLE `manutencoes`
  ADD CONSTRAINT `manutencoes_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `pontos_acesso_afetados`
--
ALTER TABLE `pontos_acesso_afetados`
  ADD CONSTRAINT `pontos_acesso_afetados_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pontos_acesso_afetados_ibfk_2` FOREIGN KEY (`ponto_acesso_codigo`) REFERENCES `pontos_acesso` (`codigo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `proatividade`
--
ALTER TABLE `proatividade`
  ADD CONSTRAINT `proatividade_ibfk_1` FOREIGN KEY (`id_usuario_criador`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
