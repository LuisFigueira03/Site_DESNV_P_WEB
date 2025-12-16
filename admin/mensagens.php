<?php
session_start();
if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require 'includes/connection.php';


$pagina_ativa = 'mensagens';

// Filtros
$estado = $_GET['estado'] ?? 'todas'; // novo | lido | respondido | todas
$q      = trim($_GET['q'] ?? '');

// Pagina
$pagina = max(1, (int)($_GET['p'] ?? 1));
$porPagina = 15;
$offset = ($pagina - 1) * $porPagina;

// WHERE dinâmico
$where = [];
$params = [];

if (in_array($estado, ['novo', 'lido', 'respondido'], true)) {
    $where[] = "cm.estado = :estado";
    $params[':estado'] = $estado;
}

if ($q !== '') {
    $where[] = "(cm.assunto LIKE :q OR cm.nome LIKE :q OR cm.email LIKE :q)";
    $params[':q'] = '%' . $q . '%';
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Total para pags
$sqlTotal = "SELECT COUNT(*) FROM contacto_mensagem cm $whereSql";
$stmt = $dbh->prepare($sqlTotal);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->execute();
$total = (int)$stmt->fetchColumn();
$totalPaginas = max(1, (int)ceil($total / $porPagina));

//  Lista de mensagens
$sql = "
    SELECT
        cm.id, cm.tipo_remetente, cm.nome, cm.email, cm.telemovel,
        cm.assunto, cm.estado, cm.created_at
    FROM contacto_mensagem cm
    $whereSql
    ORDER BY cm.created_at DESC
    LIMIT :lim OFFSET :off
";
$stmt = $dbh->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':lim', $porPagina, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);
$stmt->execute();
$mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

function badgeEstado($estado) {
    switch ($estado) {
        case 'novo': return ['Novo', 'bg-red-100 text-red-800'];
        case 'lido': return ['Lido', 'bg-yellow-100 text-yellow-800'];
        case 'respondido': return ['Respondido', 'bg-green-100 text-green-800'];
        default: return [$estado, 'bg-gray-100 text-gray-800'];
    }
}

function tipoLabel($tipo) {
    if ($tipo === 'utente') return 'Utente';
    if ($tipo === 'medico') return 'Médico';
    return 'Anónimo';
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensagens de Contacto</title>
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

            <h1 class="text-2xl font-bold text-gray-700">Mensagens</h1>
            <span class="text-gray-500 hidden md:block">Contacto</span>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6">
            <h2 class="text-3xl font-extrabold text-gray-800 mb-6">Mensagens Recebidas</h2>

            <!-- Filtros -->
            <form method="get" class="mb-6 bg-white p-4 rounded-xl shadow flex flex-col md:flex-row gap-3 md:items-end">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Pesquisar</label>
                    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>"
                           placeholder="Assunto, nome ou email..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-[#09A2AE] focus:border-[#09A2AE] text-sm">
                </div>

                <div class="w-full md:w-56">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Estado</label>
                    <select name="estado"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-[#09A2AE] focus:border-[#09A2AE] text-sm">
                        <option value="todas" <?= $estado==='todas'?'selected':''; ?>>Todas</option>
                        <option value="novo" <?= $estado==='novo'?'selected':''; ?>>Novo</option>
                        <option value="lido" <?= $estado==='lido'?'selected':''; ?>>Lido</option>
                        <option value="respondido" <?= $estado==='respondido'?'selected':''; ?>>Respondido</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="inline-flex items-center bg-[#09A2AE] text-white font-semibold px-4 py-2 rounded-lg shadow hover:opacity-90 transition text-sm">
                        Filtrar
                    </button>

                    <a href="mensagens.php"
                       class="inline-flex items-center bg-gray-200 text-gray-800 font-semibold px-4 py-2 rounded-lg hover:bg-gray-300 transition text-sm">
                        Limpar
                    </a>
                </div>
            </form>

            <!-- Tabela -->
            <div class="bg-white p-4 rounded-xl shadow-xl overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remetente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assunto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($mensagens)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-6 text-sm text-gray-500">
                                Sem mensagens para mostrar.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($mensagens as $m): ?>
                            <?php
                              [$labelEstado, $classEstado] = badgeEstado($m['estado']);
                              $rem = trim(($m['nome'] ?? '') . ' ' . (($m['email'] ?? '') ? '— ' . $m['email'] : ''));
                              $rem = $rem !== '' ? $rem : '—';
                            ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?= date('d/m/Y H:i', strtotime($m['created_at'])) ?>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <div class="font-medium"><?= htmlspecialchars(tipoLabel($m['tipo_remetente'])) ?></div>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($rem) ?></div>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                                    <?= htmlspecialchars($m['assunto']) ?>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $classEstado ?>">
                                        <?= htmlspecialchars($labelEstado) ?>
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                    <a href="mensagemDetalhe.php?id=<?= (int)$m['id'] ?>"
                                       class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagina-->
            <div class="mt-4 flex flex-wrap items-center justify-between gap-2 text-sm text-gray-600">
                <div>
                    Total: <strong><?= (int)$total ?></strong>
                    • Página <strong><?= (int)$pagina ?></strong> de <strong><?= (int)$totalPaginas ?></strong>
                </div>

                <div class="flex gap-2">
                    <?php
                      $baseParams = $_GET;
                      unset($baseParams['p']);
                      $qs = http_build_query($baseParams);
                      $qs = $qs ? ($qs . '&') : '';
                    ?>
                    <a class="px-3 py-2 rounded-md bg-gray-200 hover:bg-gray-300 <?= $pagina<=1?'pointer-events-none opacity-50':'' ?>"
                       href="mensagens.php?<?= $qs ?>p=<?= max(1, $pagina-1) ?>">← Anterior</a>

                    <a class="px-3 py-2 rounded-md bg-gray-200 hover:bg-gray-300 <?= $pagina>=$totalPaginas?'pointer-events-none opacity-50':'' ?>"
                       href="mensagens.php?<?= $qs ?>p=<?= min($totalPaginas, $pagina+1) ?>">Seguinte →</a>
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
