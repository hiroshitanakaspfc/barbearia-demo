-- =====================================================
-- BANCO DE DADOS DA DEMO: barbearia_demo
-- Como usar: phpMyAdmin > aba SQL > cole tudo > Executar
-- =====================================================

CREATE DATABASE IF NOT EXISTS barbearia_demo
  CHARACTER SET utf8mb4          -- aceita acentos e emojis
  COLLATE utf8mb4_unicode_ci;

USE barbearia_demo;

-- ---------- SERVIÇOS ----------
CREATE TABLE servicos (
  id          INT AUTO_INCREMENT PRIMARY KEY,   -- número único, criado automaticamente
  nome        VARCHAR(80)   NOT NULL,
  duracao_min INT           NOT NULL,           -- duração em minutos
  preco       DECIMAL(8,2)  NOT NULL,           -- ex.: 45.00
  ativo       TINYINT(1)    NOT NULL DEFAULT 1  -- 1 = aparece no site, 0 = escondido
);

-- ---------- BARBEIROS ----------
CREATE TABLE barbeiros (
  id    INT AUTO_INCREMENT PRIMARY KEY,
  nome  VARCHAR(80) NOT NULL,
  ativo TINYINT(1)  NOT NULL DEFAULT 1
);

-- ---------- CLIENTES ----------
CREATE TABLE clientes (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  nome     VARCHAR(100) NOT NULL,
  telefone VARCHAR(20)  NOT NULL,
  email    VARCHAR(120) NULL
);

-- ---------- AGENDAMENTOS ----------
CREATE TABLE agendamentos (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id  INT  NOT NULL,
  barbeiro_id INT  NOT NULL,
  servico_id  INT  NOT NULL,
  data        DATE NOT NULL,
  hora        TIME NOT NULL,
  status      ENUM('agendado','concluido','cancelado') NOT NULL DEFAULT 'agendado',
  criado_em   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  -- Chaves estrangeiras: só aceitam IDs que existem nas outras tabelas
  FOREIGN KEY (cliente_id)  REFERENCES clientes(id),
  FOREIGN KEY (barbeiro_id) REFERENCES barbeiros(id),
  FOREIGN KEY (servico_id)  REFERENCES servicos(id),

  -- Impede dois agendamentos do mesmo barbeiro no mesmo dia e horário
  UNIQUE KEY horario_unico (barbeiro_id, data, hora)
);

-- =====================================================
-- DADOS DE TESTE (fictícios)
-- =====================================================
INSERT INTO servicos (nome, duracao_min, preco) VALUES
  ('Corte masculino', 40, 45.00),
  ('Barba',           30, 35.00),
  ('Corte + barba',   60, 70.00),
  ('Sobrancelha',     15, 15.00);

INSERT INTO barbeiros (nome) VALUES
  ('Rafael'),
  ('Diego');

INSERT INTO clientes (nome, telefone) VALUES
  ('Cliente Teste', '(00) 90000-0000');

INSERT INTO agendamentos (cliente_id, barbeiro_id, servico_id, data, hora) VALUES
  (1, 1, 1, CURDATE() + INTERVAL 1 DAY, '10:00:00');

-- Teste para fazer depois: tente rodar o INSERT acima de novo.
-- O MySQL vai recusar por causa do UNIQUE KEY (horário já ocupado).
-- Para conferir os dados: SELECT * FROM servicos;
