<?php
$titulo = 'Barbearia Navalha';
include 'includes/cabecalho.php'; // traz o topo e o menu

require 'includes/conexao.php'; // cria a variável $pdo

/*
  Agora os serviços vêm do banco de dados.
  query() executa o SELECT; fetchAll() devolve todas as linhas como um array,
  o mesmo formato que usávamos antes. Por isso o foreach lá embaixo quase não muda.
*/
$consulta = $pdo->query("SELECT nome, duracao_min, preco FROM servicos WHERE ativo = 1 ORDER BY id");
$servicos = $consulta->fetchAll();
?>

<main>
  <section class="hero" id="inicio">
    <h1>Corte e barba com hora marcada.</h1>
    <p>Atendimento sem fila, de terça a sábado. Escolha o serviço e o horário em poucos toques.</p>
    <a class="botao" href="agendar.php">Agendar horário</a>
  </section>

  <section class="secao" id="servicos">
    <h2>Serviços e preços</h2>
    <ul class="lista-servicos">
      <?php foreach ($servicos as $s): ?>
        <li>
          <span><?= htmlspecialchars($s['nome']) ?></span>
          <span><?= (int) $s['duracao_min'] ?> min</span>
          <!-- number_format(valor, casas, separador decimal, separador de milhar) -->
          <strong>R$ <?= number_format($s['preco'], 2, ',', '.') ?></strong>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>

  <section class="secao" id="galeria">
    <h2>Nossos trabalhos</h2>
    <div class="galeria">
      <div class="foto">Foto 1</div>
      <div class="foto">Foto 2</div>
      <div class="foto">Foto 3</div>
      <div class="foto">Foto 4</div>
    </div>
  </section>

  <section class="secao" id="contato">
    <h2>Horário e contato</h2>
    <p>Terça a sexta, das 9h às 19h<br>Sábado, das 8h às 17h<br>Domingo e segunda: fechado</p>
    <p>Rua Exemplo, 123, Centro</p>
    <a class="botao" href="https://wa.me/5500000000000?text=Ol%C3%A1!%20Quero%20agendar%20um%20hor%C3%A1rio.">Chamar no WhatsApp</a>
  </section>
</main>

<?php include 'includes/rodape.php'; // traz o rodapé e fecha o HTML ?>
