<?php // navbar.php ?>
<header class="w-full border-b border-gray-100 pb-2 bg-white">
    <div class="max-w-7xl mx-auto px-6 pt-4">

        <div class="flex items-center justify-between gap-4">

            <!-- Logo e Título -->
            <a href="index.php" class="flex items-center gap-3 hover:opacity-80 transition">
                <img src="imagens/logo.png" alt="Logótipo" class="h-12 w-auto">

                <div class="text-left">
                    <h1 class="text-lg font-bold text-black leading-tight">
                        HOSPITAL DA LUZ
                    </h1>
                    <p class="text-sm font-semibold text-[#09A2AE] uppercase tracking-wider -mt-1">
                        Montemor-o-Velho
                    </p>
                </div>
            </a>
            
            <!-- Menu -->
            <div class="flex flex-col items-end md:items-center">

                <input id="menu-toggle" type="checkbox" class="peer hidden md:hidden">

                <!-- Linha com menu desktop e login mobile e hamburger -->
                <div class="flex items-center gap-3">

                    <!-- MENU DESKTOP -->
                    <nav class="hidden md:flex flex-wrap gap-3 items-center">

                        <a href="index.php" class="bg-[#09A2AE] text-white px-6 py-2 rounded-full font-medium hover:bg-sky-600 transition">
                            Início
                        </a>

                        <!-- ESPECIALIDADES -->
                        <div class="relative group">
                            <button class="bg-[#09A2AE] text-white px-6 py-2 rounded-full font-medium hover:bg-sky-600 transition inline-flex items-center gap-2">
                                Especialidades
                                <span class="text-sm">▼</span>
                            </button>

                            <div class="absolute left-0 mt-2 w-56 bg-white shadow-lg rounded-xl border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <a href="especialidade.php?esp=1" class="block px-4 py-2 hover:bg-gray-100 text-sm text-black">
                                    Cardiologia
                                </a>
                                <a href="especialidade.php?esp=2" class="block px-4 py-2 hover:bg-gray-100 text-sm text-black">
                                    Dermatologia
                                </a>
                                <a href="especialidade.php?esp=3" class="block px-4 py-2 hover:bg-gray-100 text-sm text-black">
                                    Ortopedia
                                </a>
                                <a href="especialidade.php?esp=4" class="block px-4 py-2 hover:bg-gray-100 text-sm text-black">
                                    Pediatria
                                </a>
                                <a href="especialidade.php?esp=5" class="block px-4 py-2 hover:bg-gray-100 text-sm text-black">
                                    Oftalmologia
                                </a>
                            </div>
                        </div>

                        
                        <a href="acordos.php" class="bg-[#09A2AE] text-white px-6 py-2 rounded-full font-medium hover:bg-sky-600 transition">
                            Acordos
                        </a>

                        <a href="sobre_nos.php" class="bg-[#09A2AE] text-white px-6 py-2 rounded-full font-medium hover:bg-sky-600 transition">
                            Sobre nós
                        </a>

                        <!-- LOGIN EM DESKTOP -->
                        <a href="login.php" class="bg-[#09A2AE] text-white px-6 py-2 rounded-full font-medium hover:bg-sky-600 transition">
                            Login
                        </a>
                    </nav>

                    <!-- LOGIN EM MOBILE -->
                    <a href="login.php"
                       class="md:hidden bg-[#09A2AE] text-white px-4 py-2 rounded-full font-medium hover:bg-sky-600 transition">
                        Login
                    </a>

                    <!-- BOTÃO HAMBURGER para tele -->
                    <label for="menu-toggle" class="md:hidden inline-flex items-center justify-center p-2 rounded-md border border-gray-300 hover:bg-gray-100 transition cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </label>
                </div>

                <!-- MENU MOBILE  -->
                <div class="mt-3 hidden peer-checked:flex md:hidden w-full">
                    <nav class="flex flex-col gap-2 w-full">

                        <a href="index.php" class="w-full text-left bg-[#09A2AE] text-white px-4 py-2 rounded-full font-medium hover:bg-sky-600 transition">
                            Início
                        </a>

                        <!-- Especialidades: -->
                        <button
                            id="mobile-especialidades-btn"
                            type="button"
                            class="w-full text-left bg-[#09A2AE] text-white px-4 py-2 rounded-full font-medium hover:bg-sky-600 transition inline-flex items-center justify-between"
                        >
                            <span>Especialidades</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        <!-- LISTA DE ESPECIALIDADES -->
                        <div id="mobile-especialidades-list" class="mt-1 bg-gray-50 rounded-xl border border-gray-200 px-3 py-2 hidden">
                            <div class="flex flex-col gap-1">
                                <a href="especialidade.php?esp=1" class="block px-2 py-1 rounded hover:bg-gray-100 text-sm">
                                    Cardiologia
                                </a>
                                <a href="especialidade.php?esp=2" class="block px-2 py-1 rounded hover:bg-gray-100 text-sm">
                                    Dermatologia
                                </a>
                                <a href="especialidade.php?esp=3" class="block px-2 py-1 rounded hover:bg-gray-100 text-sm">
                                    Ortopedia
                                </a>
                                <a href="especialidade.php?esp=4" class="block px-2 py-1 rounded hover:bg-gray-100 text-sm">
                                    Pediatria
                                </a>
                                <a href="especialidade.php?esp=5" class="block px-2 py-1 rounded hover:bg-gray-100 text-sm">
                                    Oftalmologia
                                </a>
                            </div>
                        </div>

                        <a href="acordos.php" class="w-full text-left bg-[#09A2AE] text-white px-4 py-2 rounded-full font-medium hover:bg-sky-600 transition">
                            Acordos
                        </a>

                        <a href="sobre_nos.php" class="w-full text-left bg-[#09A2AE] text-white px-4 py-2 rounded-full font-medium hover:bg-sky-600 transition">
                            Sobre nós
                        </a>

                    </nav>
                </div>

            </div>
        </div>
    </div>
</header>

<!-- Linha azul -->
<div class="border-t-2 border-[#09A2AE]"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnEsp = document.getElementById('mobile-especialidades-btn');
    const listaEsp = document.getElementById('mobile-especialidades-list');

    if (btnEsp && listaEsp) {
        btnEsp.addEventListener('click', function () {
            listaEsp.classList.toggle('hidden');
        });
    }
});
</script>
