<?php 
// Cores baseadas no esquema do site
$cor_fundo_nav = '#2D3A4B';

// Variável definida em cada página (ex: $pagina_ativa = 'dashboard';)
$pagina_ativa = $pagina_ativa ?? 'dashboard'; 
?>

<nav id="sidebar" 
     class="fixed inset-y-0 left-0 transform -translate-x-full md:translate-x-0 
            transition-transform duration-300 ease-in-out w-64 text-white shadow-2xl md:shadow-lg 
            z-50 flex flex-col"
     style="background-color: <?= $cor_fundo_nav ?>;">

    <!-- Título -->
    <div class="p-6 pb-2">
        <h2 class="text-3xl font-bold" style="color: #A3A3B4;">Gestão</h2>
    </div>

    <!-- Links -->
    <div class="flex-1 overflow-y-auto p-4 space-y-2">

        <?php $isActive = ($pagina_ativa === 'dashboard') ? "bg-[#3E4C5F] text-white" : "text-gray-300 hover:bg-[#3E4C5F]"; ?>
        <a href="dashboard.php" class="flex items-center px-4 py-3 rounded-lg font-medium transition <?= $isActive ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3"/>
            </svg>
            Dashboard
        </a>

        <?php $isActive = ($pagina_ativa === 'medicos') ? "bg-[#3E4C5F] text-white" : "text-gray-300 hover:bg-[#3E4C5F]"; ?>
        <a href="medicos.php" class="flex items-center px-4 py-3 rounded-lg font-medium transition <?= $isActive ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M10 12h4m-4 0v4m-4-4h4M12 2v20"/>
            </svg>
            Gestão de Médicos
        </a>

        <?php $isActive = ($pagina_ativa === 'utilizadores') ? "bg-[#3E4C5F] text-white" : "text-gray-300 hover:bg-[#3E4C5F]"; ?>
        <a href="utilizadores.php" class="flex items-center px-4 py-3 rounded-lg font-medium transition <?= $isActive ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20v-2
                         a3 3 0 00-5.356-1.857M17 20H7
                         m4-9a4 4 0 11-8 0 4 4 0 018 0
                         m7 0a4 4 0 11-8 0 4 4 0 018 0"/>
            </svg>
            Gestão de Utilizadores
        </a>

        <?php $isActive = ($pagina_ativa === 'mensagens') ? "bg-[#3E4C5F] text-white" : "text-gray-300 hover:bg-[#3E4C5F]"; ?>
        <a href="mensagens.php" class="flex items-center px-4 py-3 rounded-lg font-medium transition <?= $isActive ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 10h8M8 14h6M21 12
                         c0 4.418-4.03 8-9 8
                         a9.77 9.77 0 01-4-.8
                         L3 20l1.2-3.6
                         A7.6 7.6 0 013 12
                         c0-4.418 4.03-8 9-8
                         s9 3.582 9 8z"/>
            </svg>
            Mensagens
        </a>

        <?php $isActive = ($pagina_ativa === 'feedback') ? "bg-[#3E4C5F] text-white" : "text-gray-300 hover:bg-[#3E4C5F]"; ?>
        <a href="feedback.php" class="flex items-center px-4 py-3 rounded-lg font-medium transition <?= $isActive ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.97
                         a1 1 0 00.95.69h4.178
                         c.969 0 1.371 1.24.588 1.81l-3.38 2.455
                         a1 1 0 00-.364 1.118l1.287 3.97
                         c.3.921-.755 1.688-1.54 1.118l-3.38-2.455
                         a1 1 0 00-1.175 0l-3.38 2.455
                         c-.784.57-1.838-.197-1.539-1.118l1.287-3.97
                         a1 1 0 00-.364-1.118L2.047 9.397
                         c-.783-.57-.38-1.81.588-1.81h4.178
                         a1 1 0 00.95-.69l1.286-3.97z"/>
            </svg>
            Feedback
        </a>

    </div>

    <!-- Logout -->
    <div class="p-4 mt-auto">
        <a href="/auth/logout.php"
           class="w-full flex items-center justify-center bg-red-600 font-semibold px-4 py-3 rounded-lg shadow-md hover:bg-red-700 transition">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7
                         m6 4v1a3 3 0 01-3 3H6
                         a3 3 0 01-3-3V7
                         a3 3 0 013-3h4
                         a3 3 0 013 3v1"/>
            </svg>
            Logout
        </a>
    </div>

</nav>
