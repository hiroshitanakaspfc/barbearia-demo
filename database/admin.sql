-- =====================================================
-- ETAPA 5: PAINEL DO DONO
-- Execute no MySQL Workbench (com o banco barbearia_demo já criado)
-- =====================================================
USE barbearia_demo;

-- ---------- Usuários do painel ----------
CREATE TABLE admins (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  usuario    VARCHAR(30)  NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,          -- NUNCA guardamos a senha, só o hash
  criado_em  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ---------- Liberar o horário quando um agendamento for cancelado ----------
-- Problema: o UNIQUE antigo continuava bloqueando o horário mesmo após cancelar.
-- Solução: nova coluna "ocupa_horario" (1 = ocupa, NULL = livre).
-- O MySQL aceita vários NULL em chaves únicas, então cancelados não bloqueiam ninguém.
ALTER TABLE agendamentos
  ADD COLUMN ocupa_horario TINYINT NULL DEFAULT 1;

-- Cria a nova regra ANTES de apagar a antiga (a chave estrangeira precisa de um índice)
ALTER TABLE agendamentos
  ADD UNIQUE KEY horario_unico_ativo (barbeiro_id, data, hora, ocupa_horario);

ALTER TABLE agendamentos
  DROP INDEX horario_unico;
