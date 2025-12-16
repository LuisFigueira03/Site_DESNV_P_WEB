<?php
session_start();
if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require 'includes/connection.php';

// Variável PHP para definir o item ativo na nav.php
$pagina_ativa = 'utilizadores';

// Utentes (lista)
$stmt = $dbh->prepare("
    SELECT id, nome, email, telemovel
    FROM utente
    ORDER BY nome ASC
");
$stmt->execute();
$utilizadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Utilizadores</title>
    <link rel="stylesheet" href="../src/css/output.css">
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <h1 class="text-2xl font-bold text-gray-700">Gestão de Utilizadores</h1>
            <span class="text-gray-500 hidden md:block">Pacientes e Contas Registadas</span>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6">
            <h2 class="text-3xl font-extrabold text-gray-800 mb-6">Lista de Contas de Pacientes</h2>

            <div class="mb-6">
                <div class="relative w-full max-w-sm">
                    <input type="text" id="searchInput" placeholder="Pesquisar por nome..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg shadow-sm
                                  focus:outline-none focus:ring-1 focus:ring-[#09A2AE] focus:border-[#09A2AE] sm:text-sm">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-xl overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telemóvel</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200" id="userTableBody">
                    <?php if (empty($utilizadores)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-6 text-sm text-gray-500">
                                Não existem utilizadores registados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($utilizadores as $u): ?>
                            <tr data-name="<?= htmlspecialchars(mb_strtolower($u['nome'] ?? '', 'UTF-8')) ?>">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($u['nome'] ?? '') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($u['email'] ?? '') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($u['telemovel'] ?? '') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="utilizadorDetalhe.php?id=<?= (int)$u['id'] ?>"
                                       class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                        Ver Detalhes
                                    </a>
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

    // Pesquisa por nome
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('userTableBody');

    if (searchInput && tableBody) {
        searchInput.addEventListener('keyup', function () {
            const searchTerm = this.value.toLowerCase();
            const rows = tableBody.getElementsByTagName('tr');

            Array.from(rows).forEach(row => {
                const name = (row.getAttribute('data-name') || '').toLowerCase();
                row.style.display = name.includes(searchTerm) ? '' : 'none';
            });
        });
    }
</script>

</body>
</html>
