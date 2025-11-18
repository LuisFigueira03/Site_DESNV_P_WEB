<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Acordos</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            fundoindex: "bg-gray-200 text-gray-900",
            principal: "#09A2AE"
          },
          fontFamily: {
            sans: ["Poppins", "sans-serif"],
          },
        },
      },
    };
  </script>
</head>

<body class="bg-gray-200 text-gray-900">

<?php include 'navbar.php'; ?>

  <!-- Hero -->
  <section class="bg-gray-200 py-16 text-center">
    <h1 class="text-4xl font-bold text-principal mb-4">Acordos Médicos</h1>
    <p class="text-lg text-gray-700 max-w-2xl mx-auto">
      Trabalhamos com várias seguradoras e acordos próprios, para garantir o melhor acesso aos nossos serviços de saúde.
    </p>
  </section>

  <!-- Acordos com Seguradoras -->
  <section class="max-w-6xl mx-auto px-6 py-16">
    <h2 class="text-2xl font-semibold text-center mb-10 border-b-2 border-principal inline-block pb-2">
      Acordos com Seguradoras
    </h2>

    <div class="flex flex-wrap justify-center items-center gap-8 mx-auto">
      <div class="relative bg-white shadow-sm rounded-2xl p-6 w-48 h-48 flex flex-col items-center justify-center hover:shadow-md transition">
        <img src="imagens/medis-logo.jpg" alt="Médis" class="h-12 mb-3 object-contain" />
        <p class="text-sm font-medium mb-10">Médis</p>
        <a href="#" class="absolute bottom-3 left-3 text-xs bg-principal text-white px-3 py-1 rounded-full hover:bg-principal/80 transition">Ver mais</a>
      </div>

      <div class="relative bg-white shadow-sm rounded-2xl p-6 w-48 h-48 flex flex-col items-center justify-center hover:shadow-md transition">
        <img src="imagens/multicare-logo.jpg" alt="MultiCare" class="h-12 mb-3 object-contain" />
        <p class="text-sm font-medium mb-10">MultiCare</p>
        <a href="#" class="absolute bottom-3 left-3 text-xs bg-principal text-white px-3 py-1 rounded-full hover:bg-principal/80 transition">Ver mais</a>
      </div>

      <div class="relative bg-white shadow-sm rounded-2xl p-6 w-48 h-48 flex flex-col items-center justify-center hover:shadow-md transition">
        <img src="imagens/adm-logo.jpg" alt="ADM" class="h-12 mb-3 object-contain" />
        <p class="text-sm font-medium mb-10">ADM</p>
        <a href="#" class="absolute bottom-3 left-3 text-xs bg-principal text-white px-3 py-1 rounded-full hover:bg-principal/80 transition">Ver mais</a>
      </div>
    </div>
  </section>

  <!-- Contacto -->
  <section class="text-center py-16">
    <h3 class="text-xl font-semibold mb-4">Tem dúvidas sobre os acordos?</h3>
    <p class="text-gray-700 mb-6">Fale connosco para confirmar se o seu seguro ou entidade tem parceria com a nossa clínica.</p>

    <a href="formulario.php" class="inline-block bg-principal text-white px-6 py-3 rounded-full font-medium hover:bg-principal/80 transition">
      Contactar Hospital da Luz - Montemor-o-Velho
    </a>
  </section>

<?php include 'footer.php'; ?>

</body>
</html>








