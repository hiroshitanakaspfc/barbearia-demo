<?php
require 'includes/conexao.php';

// Atalho para escrever texto na página com segurança (evita código malicioso)
function e($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

// ---------- Dados para preencher os selects do formulário ----------
$servicos  = $pdo->query("SELECT id, nome, duracao_min, preco FROM servicos WHERE ativo = 1 ORDER BY id")->fetchAll();
$barbeiros = $pdo->query("SELECT id, nome FROM barbeiros WHERE ativo = 1 ORDER BY nome")->fetchAll();

// ---------- Horários disponíveis para escolha: 09:00 até 18:30, de 30 em 30 min ----------
$horarios = [];
for ($h = 9; $h <= 18; $h++) {
    $horarios[] = sprintf('%02d:00', $h);
    $horarios[] = sprintf('%02d:30', $h);
}

$erros   = [];
$sucesso = null;
$campos  = ['nome' => '', 'telefone' => '', 'servico_id' => '', 'barbeiro_id' => '', 'data' => '', 'hora' => ''];

// ---------- Só processa quando o formulário foi enviado (método POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1) Pega os dados enviados (trim remove espaços sobrando)
    foreach ($campos as $chave => $vazio) {
        $campos[$chave] = trim($_POST[$chave] ?? '');
    }
    $telefone = preg_replace('/\D/', '', $campos['telefone']); // deixa só os números

    // 2) VALIDAÇÃO no servidor (nunca confie só no que o navegador valida)
    if (mb_strlen($campos['nome']) < 2 || mb_strlen($campos['nome']) > 100) {
        $erros[] = 'Informe seu nome (entre 2 e 100 letras).';
    }
    if (strlen($telefone) < 10 || strlen($telefone) > 11) {
        $erros[] = 'Informe um telefone com DDD, por exemplo (11) 91234-5678.';
    }

    // O serviço e o barbeiro escolhidos precisam existir na lista
    $servico = null;
    foreach ($servicos as $s) {
        if ($s['id'] == $campos['servico_id']) { $servico = $s; }
    }
    $barbeiro = null;
    foreach ($barbeiros as $b) {
        if ($b['id'] == $campos['barbeiro_id']) { $barbeiro = $b; }
    }
    if (!$servico)  { $erros[] = 'Escolha um serviço.'; }
    if (!$barbeiro) { $erros[] = 'Escolha um barbeiro.'; }

    // Data: precisa ser válida, não pode ser passada e a barbearia fecha domingo e segunda
    $data = DateTime::createFromFormat('Y-m-d', $campos['data']);
    if (!$data || $data->format('Y-m-d') !== $campos['data']) {
        $erros[] = 'Escolha uma data válida.';
    } else {
        $hoje = new DateTime('today');
        $diaSemana = (int) $data->format('N'); // 1 = segunda ... 7 = domingo
        if ($data < $hoje) {
            $erros[] = 'A data não pode ser no passado.';
        } elseif ($diaSemana === 1 || $diaSemana === 7) {
            $erros[] = 'Fechamos aos domingos e segundas. Escolha de terça a sábado.';
        }
    }

    // Hora: precisa estar na lista; aos sábados o último horário é 16:30
    if (!in_array($campos['hora'], $horarios, true)) {
        $erros[] = 'Escolha um horário da lista.';
    } elseif ($data && $data->format('N') == 6 && $campos['hora'] > '16:30') {
        $erros[] = 'Aos sábados o último horário é 16:30.';
    } elseif ($data && $data->format('Y-m-d') === date('Y-m-d') && $campos['hora'] <= date('H:i')) {
        $erros[] = 'Esse horário de hoje já passou.';
    }

    // 3) Se está tudo certo, grava no banco
    if (!$erros) {
        try {
            $pdo->beginTransaction(); // ou grava tudo, ou não grava nada

            // Procura o cliente pelo telefone; se não existir, cadastra
            $busca = $pdo->prepare("SELECT id FROM clientes WHERE telefone = ? LIMIT 1");
            $busca->execute([$telefone]);
            $clienteId = $busca->fetchColumn();

            if (!$clienteId) {
                $novo = $pdo->prepare("INSERT INTO clientes (nome, telefone) VALUES (?, ?)");
                $novo->execute([$campos['nome'], $telefone]);
                $clienteId = $pdo->lastInsertId();
            }

            // PREPARED STATEMENT: os "?" recebem os valores separados do SQL (protege contra SQL Injection)
            $agendar = $pdo->prepare(
                "INSERT INTO agendamentos (cliente_id, barbeiro_id, servico_id, data, hora)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $agendar->execute([$clienteId, $barbeiro['id'], $servico['id'], $campos['data'], $campos['hora'] . ':00']);

            $pdo->commit();

            $sucesso = [
                'servico'  => $servico['nome'],
                'barbeiro' => $barbeiro['nome'],
                'data'     => $data->format('d/m/Y'),
                'hora'     => $campos['hora'],
            ];
            $campos = array_map(fn($v) => '', $campos); // limpa o formulário

        } catch (PDOException $ex) {
            $pdo->rollBack();
            // Código 1062 = violação do UNIQUE KEY horario_unico (horário já ocupado)
            if (($ex->errorInfo[1] ?? 0) == 1062) {
                $erros[] = 'Esse horário já foi reservado com esse barbeiro. Escolha outro.';
            } else {
                $erros[] = 'Não foi possível agendar agora. Tente novamente em instantes.';
            }
        }
    }
}

$titulo = 'Agendar horário | Barbearia Navalha';
include 'includes/cabecalho.php';
?>

<main>
  <section class="secao secao-form">
    <h1 class="titulo-pagina">Agendar horário</h1>

    <?php if ($sucesso): ?>
      <div class="aviso sucesso" role="status">
        <strong>Agendamento confirmado!</strong><br>
        <?= e($sucesso['servico']) ?> com <?= e($sucesso['barbeiro']) ?>,
        dia <?= e($sucesso['data']) ?> às <?= e($sucesso['hora']) ?>.
      </div>
    <?php endif; ?>

    <?php if ($erros): ?>
      <div class="aviso erro" role="alert">
        <strong>Corrija antes de continuar:</strong>
        <ul>
          <?php foreach ($erros as $erro): ?>
            <li><?= e($erro) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" action="agendar.php" class="formulario">

      <label for="nome">Nome</label>
      <input type="text" id="nome" name="nome" value="<?= e($campos['nome']) ?>" maxlength="100" required>

      <label for="telefone">Telefone com DDD</label>
      <input type="tel" id="telefone" name="telefone" value="<?= e($campos['telefone']) ?>" placeholder="(11) 91234-5678" required>

      <label for="servico_id">Serviço</label>
      <select id="servico_id" name="servico_id" required>
        <option value="">Escolha um serviço</option>
        <?php foreach ($servicos as $s): ?>
          <option value="<?= (int) $s['id'] ?>" <?= $campos['servico_id'] == $s['id'] ? 'selected' : '' ?>>
            <?= e($s['nome']) ?> (<?= (int) $s['duracao_min'] ?> min, R$ <?= number_format($s['preco'], 2, ',', '.') ?>)
          </option>
        <?php endforeach; ?>
      </select>

      <label for="barbeiro_id">Barbeiro</label>
      <select id="barbeiro_id" name="barbeiro_id" required>
        <option value="">Escolha um barbeiro</option>
        <?php foreach ($barbeiros as $b): ?>
          <option value="<?= (int) $b['id'] ?>" <?= $campos['barbeiro_id'] == $b['id'] ? 'selected' : '' ?>>
            <?= e($b['nome']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label for="data">Data</label>
      <!-- min impede escolher dias passados no navegador (o PHP confere de novo) -->
      <input type="date" id="data" name="data" value="<?= e($campos['data']) ?>" min="<?= date('Y-m-d') ?>" required>

      <label for="hora">Horário</label>
      <select id="hora" name="hora" required>
        <option value="">Escolha um horário</option>
        <?php foreach ($horarios as $h): ?>
          <option value="<?= $h ?>" <?= $campos['hora'] === $h ? 'selected' : '' ?>><?= $h ?></option>
        <?php endforeach; ?>
      </select>

      <button type="submit" class="botao">Confirmar agendamento</button>
    </form>
  </section>
</main>

<?php include 'includes/rodape.php'; ?>
