<?php
session_start();
if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'admin') {
    header('Location: ../admin/auth/login.php');
    exit;
}

require 'includes/connection.php';

// Variável para definir o item ativo na nav.php
$pagina_ativa = 'medicos';

// Buscar todos os médicos (ativos e inativos)
$sql = "
    SELECT
        m.id,
        m.nome,
        m.num_cedula,
        e.nome AS especialidade,
        m.ativo
    FROM medico m
    JOIN especialidade e ON e.id = m.especialidade_id
    ORDER BY m.nome ASC
";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar férias de todos os médicos
$feriasStmt = $dbh->prepare("
    SELECT medico_id, data_inicio, data_fim
    FROM ausencia_medico
    WHERE tipo = 'ferias' AND ativo = 1
    ORDER BY data_inicio DESC
");
$feriasStmt->execute();
$feriasRaw = $feriasStmt->fetchAll(PDO::FETCH_ASSOC);

// Organizar férias por médico
$feriasPorMedico = [];
foreach ($feriasRaw as $f) {
    $feriasPorMedico[$f['medico_id']][] = $f;
}
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

    <div id="overlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden"></div>

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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Férias Agendadas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($medicos)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-6 text-sm text-gray-500">
                                Não existem médicos registados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($medicos as $m): ?>
                            <?php
                                $ativo = (int)$m['ativo'] === 1;
                                $status = $ativo ? 'Activo' : 'Inativo';
                                $statusClasse = $ativo ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-600';
                                $ferias = $feriasPorMedico[$m['id']] ?? [];
                            ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($m['nome']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($m['especialidade']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($m['num_cedula']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClasse ?>">
                                        <?= $status ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?php if (empty($ferias)): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Sem férias</span>
                                    <?php else: ?>
                                        <?php foreach ($ferias as $f): ?>
                                            <span class="block px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 mb-1">
                                                <?= date('d/m/Y', strtotime($f['data_inicio'])) ?> - <?= date('d/m/Y', strtotime($f['data_fim'])) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="medicoGestao.php?id=<?= (int)$m['id'] ?>"
                                       class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                        Gerir
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
