<?php
session_start();
if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require 'includes/connection.php';

// Variável para o item ativo na nav.php
$pagina_ativa = 'utilizadores';

// ID do utente
$utente_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($utente_id <= 0) {
    header('Location: utilizadores.php?erro=utilizadoraonaoencontrado');
    exit;
}

// Utente + nome do acordo
$sql = "
    SELECT u.*,
           a.nome AS nome_acordo
    FROM utente u
    LEFT JOIN acordo a ON a.id = u.acordo_id
    WHERE u.id = :id
";
$stmt = $dbh->prepare($sql);
$stmt->bindValue(':id', $utente_id, PDO::PARAM_INT);
$stmt->execute();
$utilizador = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$utilizador) {
    header('Location: utilizadores.php?erro=utilizadoraonaoencontrado');
    exit;
}

// Acordos ativos para o select
$stmt = $dbh->prepare("SELECT id, nome FROM acordo WHERE ativo = 1 ORDER BY nome ASC");
$stmt->execute();
$acordos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mensagens simples 
$erro = $_GET['erro'] ?? '';
$ok   = $_GET['ok'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerir Utilizador: <?= htmlspecialchars($utilizador['nome']) ?></title>
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

            <h1 class="text-2xl font-bold text-gray-700">Gestão de Utilizadores</h1>
            <span class="text-gray-500 hidden md:block">Detalhes da Conta</span>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6">

            <?php if ($ok): ?>
                <div class="mb-4 bg-green-100 text-green-800 px-4 py-3 rounded">
                    Alterações guardadas com sucesso.
                </div>
            <?php endif; ?>

            <?php if ($erro): ?>
                <div class="mb-4 bg-red-100 text-red-800 px-4 py-3 rounded">
                    Ocorreu um erro: <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <a href="utilizadores.php" class="text-gray-600 hover:text-gray-800 text-sm mb-4 inline-block">
                &larr; Voltar à Lista de Utilizadores
            </a>

            <h2 class="text-3xl font-extrabold text-gray-800 mb-6">
                Gerir Detalhes: <?= htmlspecialchars($utilizador['nome']) ?>
            </h2>

            <div class="bg-white p-8 rounded-xl shadow-xl">

                <form action="crud/utilizadorEditaProcessa.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="id" value="<?= (int)$utilizador['id'] ?>">

                    <!-- Dados Pessoais -->
                    <div class="border-b pb-6">
                        <h3 class="text-xl font-bold text-gray-700 mb-4">Dados Pessoais</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label for="nome" class="block text-sm font-medium text-gray-700">Nome</label>
                                <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($utilizador['nome']) ?>" required
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                              focus:outline-none focus:ring-[#09A2AE] focus:border-[#09A2AE] sm:text-sm">
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" id="email" name="email" value="<?= htmlspecialchars($utilizador['email']) ?>" required
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                              focus:outline-none focus:ring-[#09A2AE] focus:border-[#09A2AE] sm:text-sm">
                            </div>

                            <div>
                                <label for="telemovel" class="block text-sm font-medium text-gray-700">Telemóvel</label>
                                <input type="tel" id="telemovel" name="telemovel" value="<?= htmlspecialchars($utilizador['telemovel'] ?? '') ?>"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                              focus:outline-none focus:ring-[#09A2AE] focus:border-[#09A2AE] sm:text-sm">
                            </div>

                            <div>
                                <label for="data_nascimento" class="block text-sm font-medium text-gray-700">Data de Nascimento</label>
                                <input type="date" id="data_nascimento" name="data_nascimento"
                                       value="<?= htmlspecialchars($utilizador['data_nascimento'] ?? '') ?>"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                              focus:outline-none focus:ring-[#09A2AE] focus:border-[#09A2AE] sm:text-sm">
                            </div>

                            <!-- Upload da Foto -->
                            <div class="lg:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Foto do Utente</label>

                                <input
                                    id="foto"
                                    name="foto"
                                    type="file"
                                    accept="image/*"
                                    class="block w-full border border-gray-300 rounded-md cursor-pointer bg-white px-3 py-2
                                           focus:outline-none focus:ring-1 focus:ring-[#09A2AE] focus:border-[#09A2AE] sm:text-sm">

                                <?php if (!empty($utilizador['foto'])): ?>
                                    <p class="mt-2 text-xs text-gray-500">Foto atual:</p>
                                    <img src="<?= htmlspecialchars($utilizador['foto']) ?>" class="mt-2 w-40 rounded-lg shadow" alt="Foto atual">
                                <?php endif; ?>

                                <img id="preview" class="mt-3 hidden w-40 rounded-lg shadow" alt="Preview da foto">
                                <p class="mt-2 text-xs text-gray-500">Formatos permitidos: JPG, JPEG, PNG, WEBP.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Documentação e Acordo -->
                    <div class="border-b pb-6 pt-6">
                        <h3 class="text-xl font-bold text-gray-700 mb-4">Documentação e Acordo</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="num_utente_saude" class="block text-sm font-medium text-gray-700">Nº de Utente</label>
                                <input type="text" id="num_utente_saude" name="num_utente_saude"
                                       value="<?= htmlspecialchars($utilizador['num_utente_saude'] ?? '') ?>"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                              focus:outline-none focus:ring-[#09A2AE] focus:border-[#09A2AE] sm:text-sm">
                            </div>

                            <div>
                                <label for="nif" class="block text-sm font-medium text-gray-700">NIF</label>
                                <input type="text" id="nif" name="nif"
                                       value="<?= htmlspecialchars($utilizador['nif'] ?? '') ?>"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                              focus:outline-none focus:ring-[#09A2AE] focus:border-[#09A2AE] sm:text-sm">
                            </div>

                            <div>
                                <label for="acordo_id" class="block text-sm font-medium text-gray-700">Tipo de Acordo</label>
                                <select id="acordo_id" name="acordo_id"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                               focus:outline-none focus:ring-[#09A2AE] focus:border-[#09A2AE] sm:text-sm">
                                    <option value="">—</option>
                                    <?php foreach ($acordos as $a): ?>
                                        <option value="<?= (int)$a['id'] ?>" <?= ((int)($utilizador['acordo_id'] ?? 0) === (int)$a['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($a['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Segurança -->
                    <div class="pt-6">
                        <h3 class="text-xl font-bold text-gray-700 mb-4">Segurança</h3>
                        <p class="text-sm text-gray-500 mb-4">Deixe os campos vazios se não quiser alterar a palavra-passe.</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Nova Password</label>
                                <input type="password" id="password" name="password" placeholder="Nova palavra-passe"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                              focus:outline-none focus:ring-[#09A2AE] focus:border-[#09A2AE] sm:text-sm">
                            </div>

                            <div>
                                <label for="password_confirm" class="block text-sm font-medium text-gray-700">Confirmar Nova Password</label>
                                <input type="password" id="password_confirm" name="password_confirm" placeholder="Confirmar palavra-passe"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                              focus:outline-none focus:ring-[#09A2AE] focus:border-[#09A2AE] sm:text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center bg-[#09A2AE] text-white font-semibold px-6 py-3 rounded-xl shadow-md hover:opacity-90 transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                            </svg>
                            Guardar Alterações
                        </button>
                    </div>

                </form>
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

    // Preview da foto
    const inputFoto = document.getElementById('foto');
    const preview = document.getElementById('preview');

    if (inputFoto && preview) {
        inputFoto.addEventListener('change', () => {
            const file = inputFoto.files && inputFoto.files[0];
            if (!file) {
                preview.classList.add('hidden');
                preview.removeAttribute('src');
                return;
            }

            const url = URL.createObjectURL(file);
            preview.src = url;
            preview.classList.remove('hidden');

            preview.onload = () => URL.revokeObjectURL(url);
        });
    }
</script>

</body>
</html>
