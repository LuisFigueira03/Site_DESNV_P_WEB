<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sobre Nós - Clínica da Luz</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-200 text-gray-900">

<?php include 'navbar.php'; ?>

  <!-- CONTEÚDO -->
  <main class="max-w-7xl mx-auto px-6 mt-16">

    <!-- SOBRE A CLÍNICA -->
    <section class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center mb-20">

      <!-- Imagem -->
      <div class="rounded-xl overflow-hidden shadow-lg">
        <img src="imagens/clinica.jpg" alt="Imagem da Clínica" class="w-full h-full object-cover">
      </div>

      <!-- Texto -->
      <div>
        <h2 class="text-3xl font-extrabold text-[#09A2AE] mb-4">Sobre a Nossa Clínica</h2>

        <p class="text-base leading-relaxed mb-4 text-[#4A4A4A]">
          A Clínica da Luz Montemor-o-Velho oferece cuidados de saúde de excelência,
          unindo tecnologia avançada com um atendimento próximo, humano e acessível.
        </p>

        <p class="text-base leading-relaxed mb-4 text-[#4A4A4A]">
          Aqui, cada paciente recebe um acompanhamento personalizado, garantindo conforto,
          confiança e um serviço de alto nível.
        </p>

        <p class="text-base leading-relaxed text-[#4A4A4A]">
          O nosso compromisso é cuidar do bem-estar de toda a comunidade de forma profissional
          e dedicada.
        </p>
      </div>

    </section>

    <!-- EQUIPA -->
    <section class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center mb-24">

      <!-- Imagem -->
      <div class="rounded-xl overflow-hidden shadow-lg order-2 md:order-1">
        <img src="imagens/equipa.jpg" alt="Equipa Clínica" class="w-full h-full object-cover">
      </div>

      <!-- Texto -->
      <div class="order-1 md:order-2">
        <h2 class="text-3xl font-extrabold text-[#09A2AE] mb-4">A Nossa Equipa</h2>

        <p class="text-base leading-relaxed mb-4 text-[#4A4A4A]">
          A nossa equipa é composta por médicos, enfermeiros e técnicos altamente qualificados,
          todos com grande experiência em diversas áreas da saúde.
        </p>

        <p class="text-base leading-relaxed mb-4 text-[#4A4A4A]">
          Trabalhamos em conjunto para garantir um atendimento eficiente, acolhedor e centrado
          nas necessidades de cada paciente.
        </p>

        <p class="text-base leading-relaxed text-[#4A4A4A]">
          Acreditamos que um bom ambiente de trabalho resulta sempre num melhor cuidado para si.
        </p>
      </div>

    </section>

  </main>

<?php include 'footer.php'; ?>

</body>
</html>

