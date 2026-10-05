<?php
/*
  CABEÇALHO COMPARTILHADO
  Cada página define $titulo ANTES de incluir este arquivo.
  Se esquecer, usamos um título padrão (operador ?? = "se não existir, use isto").
*/
$titulo = $titulo ?? 'Barbearia Navalha';
// $base é o caminho até a raiz do site: '' nas páginas da raiz e '../' nas páginas da pasta admin/
$base = $base ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- htmlspecialchars protege contra código malicioso no título (boa prática desde já) -->
  <title><?= htmlspecialchars($titulo) ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?= $base ?>css/style.css">
</head>
<body>

  <header class="topo">
    <a class="logo" href="<?= $base ?>index.php">Barbearia Navalha</a>
    <nav>
      <!-- Usamos index.php#id para o menu funcionar também em OUTRAS páginas (ex.: agendar.php) -->
      <a href="<?= $base ?>index.php#servicos">Serviços</a>
      <a href="<?= $base ?>index.php#galeria">Galeria</a>
      <a href="<?= $base ?>index.php#contato">Contato</a>
      <a href="<?= $base ?>agendar.php">Agendar</a>
    </nav>
  </header>

  <div class="poste" aria-hidden="true"></div>
