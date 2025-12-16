<?php
session_start();
require 'includes/connection.php';

// Definir o item ativo na nav.php
$pagina_ativa = 'medicos';

// Médicos + especialidade + férias ativas hoje (se existirem)
$sql = "
    SELECT
        m.id,
        m.nome,
        m.num_cedula,
        e.nome AS especialidade,
        a.data_inicio AS ferias_inicio,
        a.data_fim    AS ferias_fim
    FROM medico m
    JOIN especialidade e ON e.id = m.especialidade_id
    LEFT JOIN ausencia_medico a
      ON a.medico_id = m.id
     AND a.tipo = 'ferias'
     AND CURDATE() BETWEEN a.data_inicio AND a.data_fim
    ORDER BY m.nome ASC
";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Médicos</title>
    <link rel="icon" type="image/png" href="/imagens/logo-sem-fundo.png" />
    <link rel="stylesheet" href="../src/css/output.css">
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

            <h1 class="text-2xl font-bold text-gray-700">Gestão de Médicos</h1>
            <span class="text-gray-500 hidden md:block">Gestão de Horários e Férias</span>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6">
            <h2 class="text-3xl font-extrabold text-gray-800 mb-6">Lista de Especialistas</h2>

            <div class="mb-6 flex justify-end">
                <a href="medicoNovo.php"
                   class="inline-flex items-center bg-[#09A2AE] text-white font-semibold px-4 py-2 rounded-lg shadow-md hover:opacity-90 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Adicionar Novo Médico
                </a>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-xl overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Especialidade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cédula</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Férias</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($medicos)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-6 text-sm text-gray-500">
                                Não existem médicos registados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($medicos as $m): ?>
                            <?php
                                $temFeriasHoje = !empty($m['ferias_inicio']) && !empty($m['ferias_fim']);

                                if ($temFeriasHoje) {
                                    $statusFerias = 'De férias (' .
                                        date('d/m/Y', strtotime($m['ferias_inicio'])) .
                                        ' - ' .
                                        date('d/m/Y', strtotime($m['ferias_fim'])) .
                                    ')';
                                } else {
                                    $statusFerias = 'Sem férias';
                                }
                            ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($m['nome']) ?>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($m['especialidade']) ?>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($m['num_cedula']) ?>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?php if ($temFeriasHoje): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            <?= htmlspecialchars($statusFerias) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <?= htmlspecialchars($statusFerias) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="medicoGestao.php?id=<?= (int)$m['id'] ?>"
                                       class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                        Gerir Disponibilidade
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
</script>

</body>
</html>
