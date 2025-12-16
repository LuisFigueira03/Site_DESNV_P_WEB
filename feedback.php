<?php
session_start();
require 'includes/connection.php';

// Apenas utente autenticado
if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'utente') {
    header('Location: login.php');
    exit;
}

$utente_id = $_SESSION['utente_id'] ?? null;

if (!$utente_id) {
    header('Location: login.php');
    exit;
}

// Mensagens de retorno
$feedback_sucesso = $_SESSION['feedback_sucesso'] ?? '';
$feedback_erro    = $_SESSION['feedback_erro'] ?? '';
unset($_SESSION['feedback_sucesso'], $_SESSION['feedback_erro']);

// Dados do utente
$sql = "SELECT nome FROM utente WHERE id = :id";
$stmt = $dbh->prepare($sql);
$stmt->bindValue(':id', $utente_id, PDO::PARAM_INT);
$stmt->execute();
$utente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$utente) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <title>Dar Feedback</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="src/css/output.css">
  <link rel="icon" type="image/png" href="imagens/logo-sem-fundo.png">
</head>

<body class="bg-gray-200 text-slate-800 antialiased">

<?php include 'includes/navbar.php'; ?>

<main class="max-w-3xl mx-auto px-4 py-10">

  <section class="bg-white rounded-2xl shadow overflow-hidden">

    <div class="bg-[#09A2AE] p-6 text-center text-white">
      <h1 class="text-2xl font-bold">A sua opinião é importante</h1>
      <p class="text-sm opacity-90 mt-1">
        Ajude-nos a melhorar os nossos serviços
      </p>
    </div>

    <?php if ($feedback_sucesso): ?>
      <div class="m-6 bg-emerald-100 text-emerald-800 px-4 py-3 rounded">
        <?= htmlspecialchars($feedback_sucesso) ?>
      </div>
    <?php endif; ?>

    <?php if ($feedback_erro): ?>
      <div class="m-6 bg-red-100 text-red-800 px-4 py-3 rounded">
        <?= htmlspecialchars($feedback_erro) ?>
      </div>
    <?php endif; ?>

    <form action="processar_feedback.php" method="POST" class="p-6 space-y-5">

      <div>
        <label class="block text-sm font-semibold mb-1">Nome</label>
        <input type="text"
               value="<?= htmlspecialchars($utente['nome']) ?>"
               disabled
               class="w-full px-4 py-2 bg-gray-100 border rounded">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Classificação</label>
        <select name="estrelas" required
                class="w-full px-4 py-2 border rounded">
          <option value="">Seleccione uma opção</option>
          <option value="5">5 – Excelente</option>
          <option value="4">4 – Muito bom</option>
          <option value="3">3 – Bom</option>
          <option value="2">2 – Regular</option>
          <option value="1">1 – Fraco</option>
          <option value="0">0 – Insatisfatório</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Comentário</label>
        <textarea name="comentario"
                  rows="4"
                  class="w-full px-4 py-2 border rounded"
                  placeholder="Conte-nos a sua experiência..."></textarea>
      </div>

      <div class="flex justify-center pt-4">
        <button type="submit"
                class="bg-[#09A2AE] text-white px-10 py-3 rounded-lg font-semibold hover:opacity-90">
          Enviar feedback
        </button>
      </div>

    </form>

  </section>

</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
