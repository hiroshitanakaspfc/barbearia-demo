# Barbearia Navalha

Site de demonstração para barbearias, com **agendamento online** e **painel de administração**. Projeto de estudo do curso de Desenvolvimento de Sistemas (SENAI), feito com HTML, CSS, PHP e MySQL, sem frameworks.

> Os nomes, preços e dados são fictícios.

## O que o sistema faz

**Para o cliente**
- Vê serviços, preços, galeria, horário de funcionamento e contato
- Agenda escolhendo serviço, barbeiro, data e horário
- Recebe aviso claro se o horário já estiver ocupado ou se algum dado estiver inválido

**Para o dono (painel em `/admin`)**
- Login com usuário e senha
- Lista de agendamentos com cliente, serviço, barbeiro, data e horário
- Marca agendamentos como concluídos ou cancelados (o cancelamento libera o horário)

## Tecnologias

- HTML5 e CSS3 (layout responsivo, sem bibliotecas)
- PHP 8 com PDO
- MySQL / MariaDB
- XAMPP para o ambiente local

## Como rodar localmente

1. Instale o [XAMPP](https://www.apachefriends.org) e inicie **Apache** e **MySQL**.
2. Copie esta pasta para `C:\xampp\htdocs\barbearia-demo`.
3. No MySQL Workbench ou no phpMyAdmin, execute, nesta ordem:
   - `database/banco.sql` (cria o banco `barbearia_demo`, as tabelas e dados de teste)
   - `database/admin.sql` (cria a tabela de administradores e ajusta a regra de horário único)
4. Copie `includes/conexao.exemplo.php` para `includes/conexao.php` e ajuste usuário e senha, se necessário (no XAMPP padrão, `root` sem senha).
5. Acesse `http://localhost/barbearia-demo/admin/criar_admin.php`, crie o usuário do painel e **apague esse arquivo em seguida**.
6. Pronto:
   - Site: `http://localhost/barbearia-demo`
   - Painel: `http://localhost/barbearia-demo/admin/login.php`

## Estrutura

```
barbearia-demo/
├── index.php              página inicial (serviços vindos do banco)
├── agendar.php            formulário de agendamento
├── admin/                 painel do dono (login, lista, logout)
├── includes/              conexão, autenticação, cabeçalho e rodapé
├── css/style.css          estilos
├── database/              scripts SQL
└── docs/                  prints e diagramas
```

## Banco de dados

Tabelas: `servicos`, `barbeiros`, `clientes`, `agendamentos` e `admins`.
Uma chave única em `agendamentos` (barbeiro, data, hora e `ocupa_horario`) impede dois agendamentos no mesmo horário diretamente no banco, e a coluna `ocupa_horario` vira `NULL` ao cancelar para liberar o horário.

## Segurança aplicada

- Validação dos dados no servidor, além da validação do navegador
- *Prepared statements* (PDO) contra SQL Injection
- Senhas com `password_hash()` / `password_verify()`
- Sessões com `session_regenerate_id()` após o login e cookie `httponly`
- Token CSRF em todos os formulários do painel
- Saída escapada com `htmlspecialchars()` contra XSS
- Mensagem de login genérica (não revela se o usuário existe)

## Melhorias planejadas

- Bloquear horários de acordo com a duração do serviço e esconder os já ocupados no formulário
- Limite de tentativas de login
- Cadastro de serviços e barbeiros pelo painel
- Padrão Post/Redirect/Get após o agendamento
- Notificação ou botão de confirmação por WhatsApp

## Prints

<!-- Salve as imagens em docs/ e troque os nomes abaixo -->
![Página inicial](docs/home.png)
![Formulário de agendamento](docs/agendar.png)
![Painel do dono](docs/painel.png)

## Autor

Seu Nome, estudante de Desenvolvimento de Sistemas no SENAI.
[LinkedIn](https://linkedin.com/in/seu-perfil) | [GitHub](https://github.com/seu-usuario)
