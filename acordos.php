<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Acordos</title>
  <link rel="stylesheet" href="src/css/output.css" />
  <link rel="icon" type="image/png" href="imagens/logo-sem-fundo.png" />
</head>

<body class="bg-gray-200 text-gray-900">

<?php include 'includes/navbar.php'; ?>

<section class="bg-gray-200 py-16 text-center">
  <h1 class="text-4xl font-bold text-[#09A2AE] mb-4">Acordos Médicos</h1>
  <p class="text-lg text-gray-700 max-w-2xl mx-auto">
    Trabalhamos com várias seguradoras e acordos próprios, para garantir o melhor acesso aos nossos serviços de saúde.
  </p>
</section>

<section class="max-w-6xl mx-auto px-6 py-16">
  <h2 class="text-2xl font-semibold text-center mb-10 border-b-2 border-[#09A2AE] inline-block pb-2">
    Acordos com Seguradoras
  </h2>

  <div class="flex flex-wrap justify-center items-center gap-8 mx-auto">

    <div class="bg-white shadow-sm rounded-2xl p-6 w-48 h-48 flex flex-col items-center justify-center hover:shadow-md transition">
      <img src="imagens/medis-logo.jpg" alt="Médis" class="h-12 mb-3 object-contain" />
      <p class="text-sm font-medium">Médis</p>
    </div>

    <div class="bg-white shadow-sm rounded-2xl p-6 w-48 h-48 flex flex-col items-center justify-center hover:shadow-md transition">
      <img src="imagens/multicare-logo.jpg" alt="MultiCare" class="h-12 mb-3 object-contain" />
      <p class="text-sm font-medium">MultiCare</p>
    </div>

    <div class="bg-white shadow-sm rounded-2xl p-6 w-48 h-48 flex flex-col items-center justify-center hover:shadow-md transition">
      <img src="imagens/adm-logo.jpg" alt="ADM" class="h-12 mb-3 object-contain" />
      <p class="text-sm font-medium">ADM</p>
    </div>

  </div>
</section>

<section class="text-center py-16">
  <h3 class="text-xl font-semibold mb-4">Tem dúvidas sobre os acordos?</h3>
  <p class="text-gray-700 mb-6">
    Fale connosco para confirmar se o seu seguro ou entidade tem parceria com a nossa clínica.
  </p>

  <a href="formulario.php"
     class="inline-block bg-[#09A2AE] text-white px-6 py-3 rounded-full font-medium hover:bg-[#09A2AE]/80 transition">
    Contactar Hospital da Luz - Montemor-o-Velho
  </a>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>
