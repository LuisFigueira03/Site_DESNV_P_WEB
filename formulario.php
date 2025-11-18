<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contacto – Clínica da Luz</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-200 text-gray-900">

<?php include 'navbar.php'; ?>



  <!-- CONTEÚDO -->
  <main class="flex justify-center items-center py-20 px-4">

    <!-- Formulário centrado — largura aumentada -->
    <form action="#" method="POST"
          class="bg-white w-full max-w-2xl mx-auto rounded-xl shadow-xl p-10 space-y-6">

      <!-- Título -->
      <div class="text-center">
        <h2 class="text-3xl font-bold text-[#09A2AE]">Fale Connosco</h2>
        <p class="text-sm text-gray-600 mt-1">Envie-nos uma mensagem ou dúvida.</p>
      </div>

      <!-- Nome -->
      <div>
        <label class="block text-sm font-medium mb-1">Nome Completo</label>
        <input type="text" required
               class="w-full p-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#09A2AE] outline-none">
      </div>

      <!-- Email -->
      <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input type="email" required
               class="w-full p-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#09A2AE] outline-none">
      </div>

      <!-- Telemóvel -->
      <div>
        <label class="block text-sm font-medium mb-1">Telemóvel</label>
        <input type="tel"
               class="w-full p-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#09A2AE] outline-none">
      </div>

      <!-- Assunto -->
      <div>
        <label class="block text-sm font-medium mb-1">Assunto</label>
        <input type="text" required
               class="w-full p-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#09A2AE] outline-none">
      </div>

      <!-- Mensagem -->
      <div>
        <label class="block text-sm font-medium mb-1">Mensagem</label>
        <textarea rows="5" required
                  class="w-full p-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#09A2AE] outline-none"></textarea>
      </div>

      <!-- Botão -->
      <button type="submit"
              class="w-full bg-white text-[#09A2AE] border border-[#09A2AE] font-semibold py-3 rounded-full hover:bg-[#09A2AE] hover:text-white transition">
        Enviar Mensagem
      </button>

    </form>

  </main>

<?php include 'footer.php'; ?>

</body>
</html>

