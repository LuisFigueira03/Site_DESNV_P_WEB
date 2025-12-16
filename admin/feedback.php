<?php
session_start();
if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require '../includes/connection.php';


$pagina_ativa = 'feedback'; 

$q = trim($_GET['q'] ?? '');

$sql = "
  SELECT f.id, f.nome, f.estrelas, f.comentario, f.criado_em,
         u.nome AS utente_nome, u.email AS utente_email
  FROM feedback f
  LEFT JOIN utente u ON u.id = f.utente_id
";

$params = [];
if ($q !== '') {
    $sql .= " WHERE f.nome LIKE :q OR f.comentario LIKE :q OR u.nome LIKE :q OR u.email LIKE :q ";
    $params[':q'] = '%' . $q . '%';
}

$sql .= " ORDER BY f.criado_em DESC LIMIT 200";

$stmt = $dbh->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v, PDO::PARAM_STR);
$stmt->execute();
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Feedback</title>
  <link rel="stylesheet" href="../src/css/output.css"/>
  <link rel="icon" type="image/png" href="/imagens/logo-sem-fundo.png" />
</head>
<body class="bg-gray-100 font-sans antialiased">

<div class="flex h-screen">
  <?php require 'includes/nav.php'; ?>

  <div id="overlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden transition-opacity duration-300"></div>

  <div class="flex-1 flex flex-col overflow-hidden md:pl-64">

    <header class="bg-white shadow-lg p-4 flex justify-between items-center z-30">
      <button id="menu-toggle" class="md:hidden text-gray-500 hover:text-gray-700 focus:outline-none">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
      </button>

      <h1 class="text-2xl font-bold text-gray-700">Feedback</h1>
      <span class="text-gray-500 hidden md:block">Avaliações e comentários</span>
    </header>

    <main class="flex-1 overflow-x-hidden overflow-y-auto p-6">

      <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h2 class="text-3xl font-extrabold text-gray-800">Feedback dos utentes</h2>

        <form method="get" class="w-full sm:w-auto">
          <input
            type="text"
            name="q"
            value="<?= htmlspecialchars($q) ?>"
            placeholder="Pesquisar por nome, email ou comentário..."
            class="w-full sm:w-[420px] px-4 py-2 border border-gray-300 rounded-lg shadow-sm
                   focus:outline-none focus:ring-1 focus:ring-[#09A2AE] focus:border-[#09A2AE] text-sm"
          >
        </form>
      </div>

      <div class="bg-white p-4 rounded-xl shadow-xl overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utente</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classificação</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comentário</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
          <?php if (empty($feedbacks)): ?>
            <tr>
              <td colspan="4" class="px-6 py-6 text-sm text-gray-500">Sem feedback registado.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($feedbacks as $f): ?>
              <?php
                $est = (int)$f['estrelas'];
                $badge = $est >= 4 ? 'bg-emerald-100 text-emerald-800' : ($est >= 2 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
              ?>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  <?= htmlspecialchars(date('d/m/Y H:i', strtotime($f['criado_em']))) ?>
                </td>

                <td class="px-6 py-4 text-sm text-gray-700">
                  <div class="font-semibold text-gray-900"><?= htmlspecialchars($f['nome']) ?></div>
                  <div class="text-xs text-gray-500">
                    <?= htmlspecialchars($f['utente_nome'] ?? '—') ?>
                    <?= !empty($f['utente_email']) ? ' • ' . htmlspecialchars($f['utente_email']) : '' ?>
                  </div>
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $badge ?>">
                    <?= $est ?>/5
                  </span>
                </td>

                <td class="px-6 py-4 text-sm text-gray-700">
                  <?= nl2br(htmlspecialchars($f['comentario'] ?? '—')) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

    </main>
  </div>
</div>

<script>
  const sidebar = document.getElementById('sidebar');
  const toggleButton = document.getElementById('menu-toggle');
  const overlay = document.getElementById('overlay');

  const toggleSidebar = () => {
    if (!sidebar) return;
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
  };

  if (toggleButton) toggleButton.addEventListener('click', toggleSidebar);
  if (overlay) overlay.addEventListener('click', toggleSidebar);
</script>

</body>
</html>
