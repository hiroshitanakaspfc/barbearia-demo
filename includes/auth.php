<?php
/*
  AUTENTICAÇÃO E SESSÃO
  Inclua este arquivo no topo de toda página do painel.
*/

// Cookie da sessão: httponly impede o JavaScript de ler; samesite reduz ataques de outros sites
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

// Bloqueia quem não fez login e manda para a tela de login
function exigir_login() {
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

// TOKEN CSRF: um código secreto por sessão, enviado junto de cada formulário.
// Impede que outro site faça o dono clicar em "cancelar" sem querer.
function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_valido($enviado) {
    return is_string($enviado) && hash_equals($_SESSION['csrf'] ?? '', $enviado);
}

// Escrever texto na página com segurança
function e($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
