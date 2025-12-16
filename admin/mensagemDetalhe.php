<?php
session_start();
if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require 'includes/connection.php';

$pagina_ativa = 'mensagens';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: mensagens.php');
    exit;
}

// Atualizar estado (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novoEstado = $_POST['estado'] ?? '';
    if (in_array($novoEstado, ['novo','lido','respondido'], true)) {
        $stmt = $dbh->prepare("UPDATE contacto_mensagem SET estado = :e WHERE id = :id");
        $stmt->bindValue(':e', $novoEstado, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
    header('Location: mensagemDetalhe.php?id=' . $id);
    exit;
}

// Mensagem
$stmt = $dbh->prepare("SELECT * FROM contacto_mensagem WHERE id = :id LIMIT 1");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$m = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$m) {
    header('Location: mensagens.php');
    exit;
}

// Se estiver "novo", marcar automaticamente como "lido"
if ($m['estado'] === 'novo') {
    $stmt = $dbh->prepare("UPDATE contacto_mensagem SET estado = 'lido' WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $m['estado'] = 'lido';
}

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

// Sanitização simples (para evitar scripts acidentais)
// Ideal: usar um sanitizador robusto (HTML Purifier), mas isto já ajuda bastante.
function sanitizarHtmlBasico($html) {
    // remove <script>...</script>
    $html = preg_replace('~<\s*script[^>]*>.*?<\s*/\s*script\s*>~is', '', $html);
    // remove on*="..." e on*='...'
    $html = preg_replace('~\son\w+\s*=\s*(["\']).*?\1~is', '', $html);
    // bloqueia javascript: em href/src
    $html = preg_replace('~(href|src)\s*=\s*(["\'])\s*javascript:.*?\2~is', '$1=$2#$2', $html);
    return $html;
}

[$labelEstado, $classEstado] = badgeEstado($m['estado']);
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensagem #<?= (int)$m['id'] ?></title>
    <link rel="icon" type="image/png" href="/imagens/logo-sem-fundo.png" />
    <link rel="stylesheet" href="src/css/output.css"/>
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

            <h1 class="text-2xl font-bold text-gray-700">Mensagem</h1>
            <span class="text-gray-500 hidden md:block">Detalhe</span>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6">

            <a href="mensagens.php" class="text-gray-600 hover:text-gray-800 text-sm mb-4 inline-block">
                &larr; Voltar às mensagens
            </a>

            <div class="bg-white rounded-xl shadow-xl p-6 space-y-5">

                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-extrabold text-gray-800">
                            <?= htmlspecialchars($m['assunto']) ?>
                        </h2>

                        <div class="mt-2 text-sm text-gray-600 space-y-1">
                            <div><strong>ID:</strong> #<?= (int)$m['id'] ?></div>
                            <div><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($m['created_at'])) ?></div>
                            <div><strong>Tipo:</strong> <?= htmlspecialchars(tipoLabel($m['tipo_remetente'])) ?></div>
                            <div><strong>Nome:</strong> <?= htmlspecialchars($m['nome'] ?? '—') ?></div>
                            <div><strong>Email:</strong> <?= htmlspecialchars($m['email'] ?? '—') ?></div>
                            <div><strong>Telemóvel:</strong> <?= htmlspecialchars($m['telemovel'] ?? '—') ?></div>
                        </div>
                    </div>

                    <div class="flex flex-col items-start md:items-end gap-2">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $classEstado ?>">
                            <?= htmlspecialchars($labelEstado) ?>
                        </span>

                        <form method="post" class="flex gap-2 items-center">
                            <select name="estado"
                                    class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-[#09A2AE] focus:border-[#09A2AE]">
                                <option value="novo" <?= $m['estado']==='novo'?'selected':''; ?>>Novo</option>
                                <option value="lido" <?= $m['estado']==='lido'?'selected':''; ?>>Lido</option>
                                <option value="respondido" <?= $m['estado']==='respondido'?'selected':''; ?>>Respondido</option>
                            </select>
                            <button type="submit"
                                    class="bg-[#09A2AE] text-white font-semibold px-4 py-2 rounded-lg hover:opacity-90 transition text-sm">
                                Guardar
                            </button>
                        </form>
                    </div>
                </div>

                <hr>

                <div>
                    <h3 class="text-lg font-bold text-gray-700 mb-3">Mensagem</h3>
                    <div class="prose max-w-none bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <?= sanitizarHtmlBasico($m['mensagem_html']) ?>
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
