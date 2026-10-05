<?php
require '../includes/conexao.php';
require '../includes/auth.php';
exigir_login(); // quem não fez login é mandado para login.php

$mensagem = '';

// ---------- Ações: concluir ou cancelar (sempre por POST + token CSRF) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $id   = (int) ($_POST['id'] ?? 0);

    if (!csrf_valido($_POST['csrf'] ?? '')) {
        $mensagem = 'Sessão expirada. Recarregue a página.';
    } elseif ($id > 0 && $acao === 'concluir') {
        $up = $pdo->prepare("UPDATE agendamentos SET status = 'concluido' WHERE id = ? AND status = 'agendado'");
        $up->execute([$id]);
        $mensagem = 'Agendamento marcado como concluído.';
    } elseif ($id > 0 && $acao === 'cancelar') {
        // ocupa_horario = NULL libera o horário para novos agendamentos
        $up = $pdo->prepare("UPDATE agendamentos SET status = 'cancelado', ocupa_horario = NULL WHERE id = ? AND status = 'agendado'");
        $up->execute([$id]);
        $mensagem = 'Agendamento cancelado e horário liberado.';
    }
}

// ---------- Filtro: próximos (de hoje em diante) ou todos ----------
$filtro = ($_GET['filtro'] ?? 'proximos') === 'todos' ? 'todos' : 'proximos';

$sql = "SELECT a.id, a.data, a.hora, a.status,
               c.nome AS cliente, c.telefone,
               s.nome AS servico, b.nome AS barbeiro
        FROM agendamentos a
        JOIN clientes  c ON c.id = a.cliente_id
        JOIN servicos  s ON s.id = a.servico_id
        JOIN barbeiros b ON b.id = a.barbeiro_id";

if ($filtro === 'proximos') {
    $consulta = $pdo->prepare($sql . " WHERE a.data >= CURDATE() ORDER BY a.data, a.hora");
    $consulta->execute();
} else {
    $consulta = $pdo->prepare($sql . " ORDER BY a.data DESC, a.hora DESC");
    $consulta->execute();
}
$lista = $consulta->fetchAll();

// Formata 11999998888 como (11) 99999-8888
function formatar_telefone($t) {
    if (strlen($t) === 11) return sprintf('(%s) %s-%s', substr($t, 0, 2), substr($t, 2, 5), substr($t, 7));
    if (strlen($t) === 10) return sprintf('(%s) %s-%s', substr($t, 0, 2), substr($t, 2, 4), substr($t, 6));
    return $t;
}

$nomesStatus = ['agendado' => 'Agendado', 'concluido' => 'Concluído', 'cancelado' => 'Cancelado'];

$base = '../';
$titulo = 'Agendamentos | Painel';
include '../includes/cabecalho.php';
?>
<main>
  <section class="secao secao-painel">
    <div class="painel-topo">
      <h1 class="titulo-pagina">Agendamentos</h1>
      <form method="post" action="logout.php">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <button type="submit" class="btn-peq">Sair (<?= e($_SESSION['admin_usuario']) ?>)</button>
      </form>
    </div>

    <p class="filtros">
      <?php if ($filtro === 'proximos'): ?>
        Mostrando os próximos. <a href="?filtro=todos">Ver todos</a>
      <?php else: ?>
        Mostrando todos. <a href="?filtro=proximos">Ver só os próximos</a>
      <?php endif; ?>
    </p>

    <?php if ($mensagem): ?>
      <div class="aviso sucesso" role="status"><?= e($mensagem) ?></div>
    <?php endif; ?>

    <?php if (!$lista): ?>
      <p>Nenhum agendamento por aqui ainda.</p>
    <?php else: ?>
      <div class="tabela-wrap">
        <table class="tabela">
          <thead>
            <tr>
              <th>Data</th><th>Hora</th><th>Cliente</th><th>Serviço</th><th>Barbeiro</th><th>Status</th><th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($lista as $a): ?>
              <tr>
                <td><?= e(date('d/m/Y', strtotime($a['data']))) ?></td>
                <td><?= e(substr($a['hora'], 0, 5)) ?></td>
                <td>
                  <?= e($a['cliente']) ?><br>
                  <small><?= e(formatar_telefone($a['telefone'])) ?></small>
                </td>
                <td><?= e($a['servico']) ?></td>
                <td><?= e($a['barbeiro']) ?></td>
                <td><span class="etiqueta etiqueta-<?= e($a['status']) ?>"><?= e($nomesStatus[$a['status']] ?? $a['status']) ?></span></td>
                <td class="acoes">
                  <?php if ($a['status'] === 'agendado'): ?>
                    <form method="post">
                      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                      <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                      <button type="submit" name="acao" value="concluir" class="btn-peq">Concluir</button>
                      <button type="submit" name="acao" value="cancelar" class="btn-peq btn-perigo"
                              onclick="return confirm('Cancelar este agendamento?')">Cancelar</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</main>
<?php include '../includes/rodape.php'; ?>
