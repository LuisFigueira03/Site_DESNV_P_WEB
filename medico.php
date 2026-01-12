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

require 'includes/connection.php';

$data_selecionada = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');
if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $data_selecionada)) {
    $data_selecionada = date('Y-m-d');
}
$dia_anterior = date('Y-m-d', strtotime($data_selecionada . ' -1 day'));
$dia_seguinte = date('Y-m-d', strtotime($data_selecionada . ' +1 day'));

$sql = "SELECT 
            c.id AS consulta_id, 
            DATE_FORMAT(c.hora_inicio, '%H:%i') as hora,
            c.estado, 
            c.tipo,
            c.observacoes,
            u.id AS utente_id, 
            u.nome AS nome_utente,
            u.num_utente_saude
        FROM consulta c 
        JOIN utente u ON c.utente_id = u.id 
        WHERE c.medico_id = :medico_id 
          AND c.data_consulta = :data_consulta
          AND c.estado != 'cancelada' 
        ORDER BY c.hora_inicio ASC";

try {
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':medico_id', $medico_id, PDO::PARAM_INT);
    $stmt->bindValue(':data_consulta', $data_selecionada);
    $stmt->execute();
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $consultas = [];
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <title>Área do Médico</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="imagens/logo-sem-fundo.png" />
  <link rel="stylesheet" href="src/css/output.css" />
</head>
<body class="bg-gray-50 text-gray-900 antialiased font-sans">

<?php include 'includes/navbar.php'; ?>

<main class="min-h-screen py-10 px-4">
  <div class="max-w-4xl mx-auto">

    <!-- Cabeçalho e Navegação de Data -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6 flex flex-col md:flex-row justify-between items-center gap-4 border border-gray-100">
        <div>
            <h1 class="text-2xl font-bold text-[#09A2AE]">Minha Agenda</h1>
            <p class="text-sm text-gray-500">Dr(a). <?= htmlspecialchars($medico_nome) ?></p>
        </div>
        
        <!-- Controlo de Datas -->
        <div class="flex items-center bg-gray-100 rounded-lg p-1 shadow-inner">
            <a href="?data=<?= $dia_anterior ?>" class="p-2 hover:bg-gray-200 rounded-md text-gray-600 transition" title="Dia Anterior">
                <!-- Ícone Seta Esquerda -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </a>
            
            <form action="" method="GET" class="mx-2 flex items-center">
                <input type="date" 
                       name="data" 
                       value="<?= $data_selecionada ?>" 
                       onchange="this.form.submit()"
                       class="bg-transparent border-none text-gray-700 font-bold text-lg focus:ring-0 text-center cursor-pointer outline-none">
            </form>

            <a href="?data=<?= $dia_seguinte ?>" class="p-2 hover:bg-gray-200 rounded-md text-gray-600 transition" title="Dia Seguinte">
                <!-- Ícone Seta Direita -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
    </div>

    <!-- Lista de Consultas -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#09A2AE]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Consultas para <?= date('d/m/Y', strtotime($data_selecionada)) ?>
            </h2>
            <span class="text-xs font-semibold bg-blue-50 text-blue-600 px-2 py-1 rounded-full">
                <?= count($consultas) ?> marcações
            </span>
        </div>

        <?php if (empty($consultas)): ?>
            <!-- Estado Vazio -->
            <div class="p-12 text-center flex flex-col items-center justify-center">
                <div class="bg-gray-50 p-4 rounded-full mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Agenda livre</h3>
                <p class="text-gray-500 mt-1">Não existem consultas marcadas para este dia.</p>
            </div>
        <?php else: ?>
            <!-- Lista de Consultas -->
            <div class="divide-y divide-gray-100">
                <?php foreach ($consultas as $consulta): ?>
                    <div class="p-4 md:p-6 hover:bg-gray-50 transition flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        
                        <!-- Bloco Esquerdo: Hora e Info -->
                        <div class="flex items-center gap-4 w-full md:w-auto">
                            <!-- Hora -->
                            <div class="flex flex-col items-center justify-center bg-teal-50 text-[#09A2AE] rounded-lg h-16 w-16 md:h-20 md:w-20 min-w-[4rem] border border-teal-100">
                                <span class="text-xl md:text-2xl font-bold"><?= htmlspecialchars($consulta['hora']) ?></span>
                            </div>
                            
                            <!-- Dados Utente -->
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-800">
                                    <?= htmlspecialchars($consulta['nome_utente']) ?>
                                </h3>
                                <div class="text-sm text-gray-500 space-y-1">
                                    <p class="flex items-center gap-1">
                                        <span class="font-medium text-gray-600">Número de Utente:</span> 
                                        <?= htmlspecialchars($consulta['num_utente_saude'] ?? 'Não disponível') ?>
                                    </p>
                                    <p class="flex items-center gap-1">
                                        <span class="inline-block w-2 h-2 rounded-full 
                                            <?= $consulta['estado'] === 'realizada' ? 'bg-green-400' : 'bg-blue-400' ?>">
                                        </span>
                                        <?= ucfirst(htmlspecialchars($consulta['tipo'])) ?> 
                                        <span class="text-xs text-gray-400">(<?= htmlspecialchars($consulta['estado']) ?>)</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Bloco Direito: Botão -->
                        <div class="w-full md:w-auto flex justify-end">
                            <a href="ficha_utente.php?id=<?= $consulta['utente_id'] ?>&consulta=<?= $consulta['consulta_id'] ?>" 
                               class="group w-full md:w-auto flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg border border-transparent bg-[#09A2AE] text-white text-sm font-medium hover:bg-[#078a94] shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#09A2AE]">
                                <span>Ver Ficha</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

  </div>
</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
