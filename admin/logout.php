<?php
require '../includes/auth.php';

// Só aceita sair por formulário POST com token (evita que outro site deslogue o dono)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valido($_POST['csrf'] ?? '')) {
    $_SESSION = [];                 // limpa os dados da sessão
    session_destroy();              // encerra a sessão
}

header('Location: login.php');
exit;
