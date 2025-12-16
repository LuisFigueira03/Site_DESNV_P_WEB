<?php
session_start();
if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require 'includes/connection.php';

// Variável para definir o item ativo na nav.php
$pagina_ativa = 'medicos';

// ID do médico
$medico_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($medico_id <= 0) {
    header('Location: medicos.php?erro=mediconaoencontrado');
    exit;
}

// dados do médico + especialidade
$sql = "
    SELECT m.id, m.nome, e.nome AS especialidade
    FROM medico m
    JOIN especialidade e ON m.especialidade_id = e.id
    WHERE m.id = :id
";
$stmt = $dbh->prepare($sql);
$stmt->bindValue(':id', $medico_id, PDO::PARAM_INT);
$stmt->execute();
$medico = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$medico) {
    header('Location: medicos.php?erro=mediconaoencontrado');
    exit;
}

// Opções de turno (mantém-se como tens no formulário)
$turnos = [
    'Manhã (08:00 - 13:00)' => 'M',
    'Tarde (14:00 - 19:00)' => 'T',
    'Noite (20:00 - 01:00)' => 'N',
    'Folga' => 'F'
];

// Dias da semana
$dias_semana = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];

// férias (ausências tipo "ferias") do médico
$sql = "
    SELECT id, data_inicio, data_fim
    FROM ausencia_medico
    WHERE medico_id = :medico_id
      AND tipo = 'ferias'
    ORDER BY data_inicio DESC
";
$stmt = $dbh->prepare($sql);
$stmt->bindValue(':medico_id', $medico_id, PDO::PARAM_INT);
$stmt->execute();
$ferias_agendadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerir Disponibilidade: <?= htmlspecialchars($medico['nome']) ?></title>
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

            <h1 class="text-2xl font-bold text-gray-700">Gestão de Disponibilidade</h1>
            <span class="text-gray-500 hidden md:block"><?= htmlspecialchars($medico['nome']) ?></span>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6">

            <a href="medicos.php" class="text-gray-600 hover:text-gray-800 text-sm mb-4 inline-block">
                &larr; Voltar à Lista de Médicos
            </a>

            <h2 class="text-3xl font-extrabold text-gray-800 mb-6">
                Gerir <?= htmlspecialchars($medico['nome']) ?>
                <span class="text-xl font-medium text-gray-500 block sm:inline-block">
                    (<?= htmlspecialchars($medico['especialidade']) ?>)
                </span>
            </h2>

            <div class="bg-white p-8 rounded-xl shadow-xl space-y-12">

                <!-- Horário Semanal -->
                <div class="border-b pb-6">
                    <h3 class="text-2xl font-bold text-gray-700 mb-4">Horário Semanal</h3>
                    <p class="text-sm text-gray-500 mb-6">
                        Defina o padrão semanal de trabalho. Os códigos de turno (M/T/N/F) serão usados para agendamentos.
                    </p>

                    <form action="crud/horarioProcessa.php" method="POST">
                        <input type="hidden" name="medico_id" value="<?= (int)$medico['id'] ?>">

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                <tr>
                                    <?php foreach ($dias_semana as $dia): ?>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <?= htmlspecialchars($dia) ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                                </thead>

                                <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <?php foreach ($dias_semana as $dia): ?>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <select name="horario[<?= strtolower(substr($dia, 0, 3)) ?>]" required
                                                    class="mt-1 block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm
                                                           focus:outline-none focus:ring-[#09A2AE] focus:border-[#09A2AE]
                                                           text-center text-xs sm:text-sm">
                                                <?php foreach ($turnos as $nome_turno => $codigo_turno): ?>
                                                    <option value="<?= htmlspecialchars($codigo_turno) ?>" <?= $codigo_turno === 'F' ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($nome_turno) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end pt-6">
                            <button type="submit"
                                    class="inline-flex items-center bg-[#09A2AE] text-white font-semibold px-6 py-3 rounded-xl shadow-md hover:opacity-90 transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                </svg>
                                Guardar Horário Padrão
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Férias e Indisponibilidade -->
                <div class="pt-6 border-t">
                    <h3 class="text-2xl font-bold text-gray-700 mb-4">Férias e Indisponibilidade</h3>
                    <p class="text-sm text-gray-500 mb-6">Marque períodos em que o médico estará totalmente indisponível.</p>

                    <form action="crud/feriasProcessa.php" method="POST"
                          class="flex flex-col sm:flex-row gap-4 mb-8 bg-gray-50 p-4 rounded-lg border">
                        <input type="hidden" name="medico_id" value="<?= (int)$medico['id'] ?>">

                        <div class="flex-1">
                            <label for="data_inicio" class="block text-xs font-medium text-gray-700">Data de Início</label>
                            <input type="date" id="data_inicio" name="data_inicio" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                          focus:outline-none focus:ring-[#09A2AE] focus:border-[#09A2AE] sm:text-sm">
                        </div>

                        <div class="flex-1">
                            <label for="data_fim" class="block text-xs font-medium text-gray-700">Data de Fim</label>
                            <input type="date" id="data_fim" name="data_fim" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                          focus:outline-none focus:ring-[#09A2AE] focus:border-[#09A2AE] sm:text-sm">
                        </div>

                        <div class="sm:self-end">
                            <button type="submit"
                                    class="w-full sm:w-auto inline-flex items-center bg-red-600 text-white font-semibold px-4 py-2 rounded-lg shadow-md hover:bg-red-700 transition h-[42px] mt-4 sm:mt-0">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Adicionar Indisponibilidade
                            </button>
                        </div>
                    </form>

                    <h4 class="text-lg font-semibold text-gray-600 mb-3">Períodos Agendados:</h4>

                    <div class="border rounded-lg overflow-hidden">
                        <?php if (empty($ferias_agendadas)): ?>
                            <p class="p-4 text-gray-500">Nenhum período de férias agendado.</p>
                        <?php else: ?>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Início</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fim</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Ação</th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($ferias_agendadas as $ferias): ?>
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($ferias['data_inicio']) ?>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($ferias['data_fim']) ?>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="crud/feriasProcessa.php?action=delete&id=<?= (int)$ferias['id'] ?>&medico_id=<?= (int)$medico['id'] ?>"
                                               class="text-red-600 hover:text-red-900">
                                                Remover
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

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
</script>

</body>
</html>
