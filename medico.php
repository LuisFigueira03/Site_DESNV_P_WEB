<?php
session_start();

// Verificar se é médico está autenticado
if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'medico') {
    header('Location: login.php');
    exit;
}

$medico_id   = $_SESSION['medico_id']   ?? null;
$medico_nome = $_SESSION['medico_nome'] ?? 'Médico';

if (!$medico_id) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <title>Área do Médico</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .cor {
      background-color: #09A2AE;
    }
    .texto-cor {
      color: #09A2AE;
    }
  </style>
</head>
<body class="bg-gray-100 text-gray-900 antialiased">

<?php include 'navbar.php'; ?>

<main class="min-h-screen flex items-center justify-center px-4">
  <div class="max-w-xl w-full bg-white rounded-xl shadow-lg p-8 mt-10 text-center">

    <h1 class="text-2xl md:text-3xl font-semibold mb-4 texto-cor">
      Área do Médico
    </h1>

    <p class="text-sm text-gray-600 mb-6">
      Olá, <span class="font-medium"><?= htmlspecialchars($medico_nome) ?></span>
    </p>

    <div class="border border-dashed border-gray-300 rounded-lg px-6 py-8">
      <p class="text-lg font-semibold mb-2">
        Página em desenvolvimento
      </p>
      <p class="text-sm text-gray-600">
        Esta área ainda se encontra em desenvolvimento. Em breve poderá gerir consultas,
        utentes e outras funcionalidades específicas para médicos.
      </p>
    </div>

    <div class="mt-8 flex justify-center gap-3">
      <a href="index.php"
         class="px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
        Página inicial
      </a>
      <a href="logout.php"
         class="px-4 py-2 rounded-md cor text-white text-sm font-medium hover:opacity-90">
        Terminar sessão
      </a>
    </div>

  </div>
</main>

<?php include 'footer.php'; ?>

</body>
</html>
