<?php
require '../includes/conexao.php';
require '../includes/auth.php';

// Se já está logado, vai direto para a lista
if (!empty($_SESSION['admin_id'])) {
    header('Location: agendamentos.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido($_POST['csrf'] ?? '')) {
        $erro = 'Sessão expirada. Tente de novo.';
    } else {
        $usuario = trim($_POST['usuario'] ?? '');
        $senha   = $_POST['senha'] ?? '';

        $busca = $pdo->prepare("SELECT id, usuario, senha_hash FROM admins WHERE usuario = ? LIMIT 1");
        $busca->execute([$usuario]);
        $admin = $busca->fetch();

        // password_verify compara a senha digitada com o hash guardado
        if ($admin && password_verify($senha, $admin['senha_hash'])) {
            session_regenerate_id(true); // novo ID de sessão após o login (evita fixação de sessão)
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_usuario'] = $admin['usuario'];
            header('Location: agendamentos.php');
            exit;
        }
        // Mensagem igual para usuário errado ou senha errada: não revela qual dos dois falhou
        $erro = 'Usuário ou senha incorretos.';
    }
}

$base = '../';
$titulo = 'Entrar | Painel';
include '../includes/cabecalho.php';
?>
<main>
  <section class="secao secao-form">
    <h1 class="titulo-pagina">Entrar no painel</h1>

    <?php if ($erro): ?>
      <div class="aviso erro" role="alert"><?= e($erro) ?></div>
    <?php endif; ?>

    <form method="post" class="formulario">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <label for="usuario">Usuário</label>
      <input type="text" id="usuario" name="usuario" required autocomplete="username">
      <label for="senha">Senha</label>
      <input type="password" id="senha" name="senha" required autocomplete="current-password">
      <button type="submit" class="botao">Entrar</button>
    </form>
  </section>
</main>
<?php include '../includes/rodape.php'; ?>
