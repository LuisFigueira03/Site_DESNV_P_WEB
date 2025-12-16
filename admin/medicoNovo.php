<?php
session_start();
if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require 'includes/connection.php';

// definir o item ativo na nav.php
$pagina_ativa = 'medicos';

// especialidades (visíveis)
$stmt = $dbh->prepare("SELECT id, nome FROM especialidade WHERE visivel = 1 ORDER BY nome ASC");
$stmt->execute();
$especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Novo Médico</title>
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

            <h1 class="text-2xl font-bold text-gray-700">Gestão de Médicos</h1>
            <span class="text-gray-500 hidden md:block">Adicionar Novo Especialista</span>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6">

            <a href="medicos.php" class="text-gray-600 hover:text-gray-800 text-sm mb-4 inline-block">
                &larr; Voltar à Lista de Médicos
            </a>

            <h2 class="text-3xl font-extrabold text-gray-800 mb-6">
                Novo Registo de Médico
            </h2>

            <div class="bg-white p-8 rounded-xl shadow-xl">

                <form action="crud/medicoProcessa.php" method="POST" class="space-y-6">

                    <div>
                        <label for="nome" class="block text-sm font-medium text-gray-700">Nome Completo</label>
                        <input type="text" id="nome" name="nome" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                      focus:outline-none focus:ring-[#09A2AE] focus:border-[#09A2AE] sm:text-sm">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="num_cedula" class="block text-sm font-medium text-gray-700">Nº de Cédula</label>
                            <input type="text" id="num_cedula" name="num_cedula" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                          focus:outline-none focus:ring-[#09A2AE] focus:border-[#09A2AE] sm:text-sm">
                        </div>

                        <div>
                            <label for="especialidade_id" class="block text-sm font-medium text-gray-700">Especialidade</label>
                            <select id="especialidade_id" name="especialidade_id" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-[#09A2AE] focus:border-[#09A2AE] sm:text-sm">
                                <option value="" disabled selected>Selecione uma especialidade</option>
                                <?php foreach ($especialidades as $esp): ?>
                                    <option value="<?= (int)$esp['id'] ?>">
                                        <?= htmlspecialchars($esp['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" id="email" name="email"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                          focus:outline-none focus:ring-[#09A2AE] focus:border-[#09A2AE] sm:text-sm">
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" id="password" name="password" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                          focus:outline-none focus:ring-[#09A2AE] focus:border-[#09A2AE] sm:text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="telemovel" class="block text-sm font-medium text-gray-700">Telemóvel</label>
                            <input type="tel" id="telemovel" name="telemovel"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                          focus:outline-none focus:ring-[#09A2AE] focus:border-[#09A2AE] sm:text-sm">
                        </div>

                        <div>
                            <label for="ativo" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="ativo" name="ativo" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                                           focus:outline-none focus:ring-[#09A2AE] focus:border-[#09A2AE] sm:text-sm">
                                <option value="1" selected>Ativo</option>
                                <option value="0">Não Ativo</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center bg-[#09A2AE] text-white font-semibold px-6 py-3 rounded-xl shadow-md hover:opacity-90 transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Adicionar Médico
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
</script>

</body>
</html>
