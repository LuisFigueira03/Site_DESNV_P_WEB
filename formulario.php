<?php
session_start();
require 'includes/connection.php';

$erro = '';
$sucesso = '';

$tipo      = $_SESSION['tipo_utilizador'] ?? null;
$utente_id = $_SESSION['utente_id'] ?? null;
$medico_id = $_SESSION['medico_id'] ?? null;

$logado = ($tipo === 'utente' && !empty($utente_id)) || ($tipo === 'medico' && !empty($medico_id));

$dadosLogado = null;

// Buscar dados do utilizador autenticado
if ($logado) {
    if ($tipo === 'utente') {
        $stmt = $dbh->prepare("SELECT nome, email, telemovel FROM utente WHERE id = :id LIMIT 1");
        $stmt->bindValue(':id', (int)$utente_id, PDO::PARAM_INT);
        $stmt->execute();
        $dadosLogado = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($tipo === 'medico') {
        $stmt = $dbh->prepare("SELECT nome, email, telemovel FROM medico WHERE id = :id LIMIT 1");
        $stmt->bindValue(':id', (int)$medico_id, PDO::PARAM_INT);
        $stmt->execute();
        $dadosLogado = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$dadosLogado) {
        $logado = false;
        $tipo = null;
        $utente_id = null;
        $medico_id = null;
    }
}

// Submissão do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $assunto = trim($_POST['assunto'] ?? '');
    $mensagem_html = trim($_POST['mensagem'] ?? '');

    if ($assunto === '') {
        $erro = 'Preencha o assunto.';
    } elseif ($mensagem_html === '' || $mensagem_html === '<p><br></p>') {
        $erro = 'Escreva a mensagem.';
    } else {

        if ($logado) {
            $nome = $dadosLogado['nome'] ?? null;
            $email = $dadosLogado['email'] ?? null;
            $telemovel = $dadosLogado['telemovel'] ?? null;
            $tipo_remetente = $tipo;
        } else {
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telemovel = trim($_POST['telemovel'] ?? '');

            if ($nome === '' || $email === '') {
                $erro = 'Preencha o nome e o email.';
            }

            $tipo_remetente = 'anonimo';
        }

        if ($erro === '') {

            $db_utente_id = null;
            $db_medico_id = null;

            if ($tipo_remetente === 'utente') $db_utente_id = (int)$utente_id;
            if ($tipo_remetente === 'medico') $db_medico_id = (int)$medico_id;

            $sql = "INSERT INTO contacto_mensagem
                      (tipo_remetente, utente_id, medico_id, nome, email, telemovel, assunto, mensagem_html)
                    VALUES
                      (:tipo_remetente, :utente_id, :medico_id, :nome, :email, :telemovel, :assunto, :mensagem_html)";

            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':tipo_remetente', $tipo_remetente, PDO::PARAM_STR);

            $db_utente_id === null
                ? $stmt->bindValue(':utente_id', null, PDO::PARAM_NULL)
                : $stmt->bindValue(':utente_id', $db_utente_id, PDO::PARAM_INT);

            $db_medico_id === null
                ? $stmt->bindValue(':medico_id', null, PDO::PARAM_NULL)
                : $stmt->bindValue(':medico_id', $db_medico_id, PDO::PARAM_INT);

            $stmt->bindValue(':nome', $nome !== '' ? $nome : null, $nome !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':email', $email !== '' ? $email : null, $email !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':telemovel', $telemovel !== '' ? $telemovel : null, $telemovel !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':assunto', $assunto, PDO::PARAM_STR);
            $stmt->bindValue(':mensagem_html', $mensagem_html, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $sucesso = 'Mensagem enviada com sucesso.';
                $_POST = [];
            } else {
                $erro = 'Erro ao enviar a mensagem.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contacto – Clínica da Luz</title>

  <link rel="stylesheet" href="src/css/output.css">
  <link rel="icon" type="image/png" href="imagens/logo-sem-fundo.png">

  <!-- Quill -->
  <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
  <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

  <style>
    #editor .ql-container { height: 160px; }
    #editor .ql-editor { height: 160px; overflow-y: auto; }
  </style>
</head>

<body class="bg-gray-200 text-gray-900">

<?php include 'includes/navbar.php'; ?>

<main class="flex justify-center items-center py-20 px-4">
  <form id="form-contacto" method="POST"
        class="bg-white w-full max-w-2xl rounded-xl shadow-xl p-10 space-y-6">

    <div class="text-center">
      <h1 class="text-3xl font-bold text-[#09A2AE]">Fale Connosco</h1>
      <p class="text-sm text-gray-600 mt-1">Envie-nos uma mensagem ou dúvida.</p>
    </div>

    <?php if ($erro): ?>
      <div class="bg-red-100 text-red-700 px-4 py-3 rounded">
        <?= htmlspecialchars($erro) ?>
      </div>
    <?php endif; ?>

    <?php if ($sucesso): ?>
      <div class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-4 py-3 rounded">
        <?= htmlspecialchars($sucesso) ?>
      </div>
    <?php endif; ?>

    <?php if (!$logado): ?>
      <div>
        <label class="block text-sm font-medium mb-1">Nome Completo</label>
        <input name="nome" type="text" required
               value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"
               class="w-full p-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#09A2AE] outline-none">
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input name="email" type="email" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               class="w-full p-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#09A2AE] outline-none">
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Telemóvel</label>
        <input name="telemovel" type="tel"
               value="<?= htmlspecialchars($_POST['telemovel'] ?? '') ?>"
               class="w-full p-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#09A2AE] outline-none">
      </div>
    <?php else: ?>
      <div class="text-sm text-gray-600 bg-gray-50 border border-gray-200 rounded-lg p-3">
        <strong><?= htmlspecialchars($dadosLogado['nome']) ?></strong>
        (<?= htmlspecialchars($dadosLogado['email']) ?>)
      </div>
    <?php endif; ?>

    <div>
      <label class="block text-sm font-medium mb-1">Assunto</label>
      <input name="assunto" type="text" required
             value="<?= htmlspecialchars($_POST['assunto'] ?? '') ?>"
             class="w-full p-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#09A2AE] outline-none">
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Mensagem</label>
      <div id="editor" class="bg-white border border-gray-300 rounded-lg"></div>
      <textarea id="mensagem" name="mensagem" class="hidden"></textarea>
    </div>

    <button type="submit"
            class="w-full bg-white text-[#09A2AE] border border-[#09A2AE] font-semibold py-3 rounded-full hover:bg-[#09A2AE] hover:text-white transition">
      Enviar Mensagem
    </button>

  </form>
</main>

<?php include 'includes/footer.php'; ?>

<script>
  const quill = new Quill('#editor', {
    theme: 'snow',
    modules: {
      toolbar: [
        ['bold', 'italic', 'underline'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['clean']
      ]
    }
  });

  <?php if (!empty($_POST['mensagem'])): ?>
    quill.root.innerHTML = <?= json_encode($_POST['mensagem']) ?>;
  <?php endif; ?>

  document.getElementById('form-contacto').addEventListener('submit', function () {
    document.getElementById('mensagem').value = quill.root.innerHTML;
  });
</script>

</body>
</html>
