<?php
session_start();
if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require 'includes/connection.php';


// Médicos ativos
$totalMedicos = (int)$dbh->query("SELECT COUNT(*) FROM medico WHERE ativo = 1")->fetchColumn();

// Consultas marcadas no mês atual
$totalConsultasMes = (int)$dbh->query("
    SELECT COUNT(*)
    FROM consulta
    WHERE estado = 'marcada'
      AND MONTH(data_consulta) = MONTH(CURDATE())
      AND YEAR(data_consulta) = YEAR(CURDATE())
")->fetchColumn();

// Médicos de férias hoje (ausência tipo férias e a decorrer hoje)
$totalMedicosFeriasHoje = (int)$dbh->query("
    SELECT COUNT(DISTINCT medico_id)
    FROM ausencia_medico
    WHERE tipo = 'ferias'
      AND CURDATE() BETWEEN data_inicio AND data_fim
")->fetchColumn();

// Utentes registados
$totalUtentes = (int)$dbh->query("SELECT COUNT(*) FROM utente")->fetchColumn();

?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Painel de Administração</title>
    <link rel="icon" type="image/png" href="/imagens/logo-sem-fundo.png" />
    <link rel="stylesheet" href="../src/css/output.css">
</head>

<body class="bg-gray-100 font-sans antialiased">

<div class="flex h-screen">

    <?php require 'includes/nav.php'; ?>

    <div id="overlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden"></div>

    <div class="flex-1 flex flex-col overflow-hidden md:pl-64">

        <header class="bg-white shadow-lg p-4 flex justify-between items-center z-30">
            <button id="menu-toggle" class="md:hidden text-gray-500 hover:text-gray-700 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <h1 class="text-2xl font-bold text-gray-700">Dashboard</h1>

            <span class="text-gray-500 hidden md:block">
                Bem-vindo, <?= htmlspecialchars($_SESSION['admin_nome'] ?? 'Admin') ?>
            </span>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6">
            <h2 class="text-3xl font-extrabold text-gray-800 mb-8">Visão Geral</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

                <div class="bg-white p-6 rounded-xl shadow-xl border-l-4 border-[#09A2AE]">
                    <p class="text-sm font-medium text-gray-500">Médicos Registados</p>
                    <p class="text-4xl font-bold text-[#09A2AE] mt-2"><?= $totalMedicos ?></p>
                    <p class="text-xs text-gray-400 mt-1">Total de especialistas ativos</p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-xl border-l-4 border-yellow-500">
                    <p class="text-sm font-medium text-gray-500">Consultas Agendadas (Mês)</p>
                    <p class="text-4xl font-bold text-yellow-600 mt-2"><?= $totalConsultasMes ?></p>
                    <p class="text-xs text-gray-400 mt-1">Estado: marcadas</p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-xl border-l-4 border-red-500">
                    <p class="text-sm font-medium text-gray-500">Médicos de Férias (Hoje)</p>
                    <p class="text-4xl font-bold text-red-600 mt-2"><?= $totalMedicosFeriasHoje ?></p>
                    <p class="text-xs text-gray-400 mt-1">Ausências em vigor hoje</p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-xl border-l-4 border-green-500">
                    <p class="text-sm font-medium text-gray-500">Utilizadores na Plataforma</p>
                    <p class="text-4xl font-bold text-green-600 mt-2"><?= $totalUtentes ?></p>
                    <p class="text-xs text-gray-400 mt-1">Utentes registados</p>
                </div>

            </div>

            <div class="bg-white p-6 rounded-xl shadow-xl">
                <h3 class="text-xl font-semibold mb-4 text-gray-700">Ações Rápidas</h3>
                <div class="flex flex-wrap gap-4">
                    <a href="medicos.php"
                       class="bg-gray-100 hover:bg-gray-200 transition px-4 py-3 rounded-lg font-medium border border-gray-300">
                        Ver Lista de Médicos
                    </a>

                    <a href="medicoNovo.php"
                       class="bg-gray-100 hover:bg-gray-200 transition px-4 py-3 rounded-lg font-medium border border-gray-300">
                        Adicionar Novo Médico
                    </a>
                </div>
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

    window.addEventListener('resize', () => {
        if (!sidebar || !overlay) return;
        if (window.innerWidth >= 768 && sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.add('hidden');
        }
    });
</script>

</body>
</html>
