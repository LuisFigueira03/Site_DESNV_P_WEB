<?php
session_start();
require 'includes/connection.php';

/* Especialidades */
$sqlEsp = "SELECT * FROM especialidade WHERE visivel = 1";
$stmtEsp = $dbh->prepare($sqlEsp);
$stmtEsp->execute();

/* Top 3 feedbacks com mais estrelas */
$sqlFb = "
    SELECT nome, estrelas, comentario
    FROM feedback
    ORDER BY estrelas DESC, criado_em DESC
    LIMIT 3
";
$stmtFb = $dbh->prepare($sqlFb);
$stmtFb->execute();
$feedbacks = $stmtFb->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <title>Hospital da Luz - Montemor-o-Velho</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="src/css/output.css">
  <link rel="icon" type="image/png" href="imagens/logo-sem-fundo.png">
</head>

<body class="bg-gray-200 text-gray-900">

<?php include 'includes/navbar.php'; ?>

<main class="max-w-7xl mx-auto px-6 mt-8">

  <!-- Imagem -->
  <img src="imagens/sala de espera.webp" class="rounded-lg shadow-lg w-full mb-8" alt="Sala de espera">

  <!-- Especialidades -->
  <section id="especialidades" class="py-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
      <?php while ($esp = $stmtEsp->fetchObject()): ?>
        <div class="bg-[#09A2AE] rounded-lg overflow-hidden text-center">
          <a href="especialidade.php?esp=<?= (int)$esp->id ?>">
            <img src="imagens/<?= htmlspecialchars($esp->imagem) ?>" class="w-full h-56 object-cover">
            <p class="text-white py-3 font-semibold text-sm"><?= htmlspecialchars($esp->nome) ?></p>
          </a>
        </div>
      <?php endwhile; ?>
    </div>
  </section>

  <!-- TESTEMUNHOS -->
  <section id="testemunhos" class="bg-gray-200 py-10">
    <div class="max-w-6xl mx-auto px-4">

      <h2 class="text-3xl font-extrabold text-center">Testemunhos</h2>
      <p class="mt-2 text-center text-slate-700">A opinião dos nossos pacientes</p>

      <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">

        <?php foreach ($feedbacks as $fb): ?>
          <div class="bg-green-600 rounded-2xl shadow p-6 text-center min-h-[220px]">

            <p class="font-semibold text-white">Nome:</p>
            <p class="text-white"><?= htmlspecialchars($fb['nome']) ?></p>

            <p class="mt-4 font-semibold text-white">Número de estrelas:</p>

            <div class="mt-1 inline-flex gap-1">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <?php if ($i <= $fb['estrelas']): ?>
                  <svg class="h-5 w-5 text-yellow-400 fill-current" viewBox="0 0 24 24">
                    <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.62L12 2 9.19 8.62 2 9.24l5.46 4.73L5.82 21z"/>
                  </svg>
                <?php else: ?>
                  <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.62L12 2 9.19 8.62 2 9.24l5.46 4.73L5.82 21z"/>
                  </svg>
                <?php endif; ?>
              <?php endfor; ?>
            </div>

            <p class="mt-4 font-semibold text-white">Comentário:</p>
            <p class="text-sm text-white">
              <?= htmlspecialchars($fb['comentario'] ?: 'Sem comentário.') ?>
            </p>

          </div>
        <?php endforeach; ?>

      </div>
    </div>
  </section>

<section id="faq" class="py-10">
    <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6 items-start">

      <div>
        <h2 class="text-3xl font-bold text-gray-800 leading-snug">
          Tem Alguma <br> Questão?
        </h2>

        <a href="formulario.php" class="inline-block mt-4 bg-[#09A2AE] text-white font-semibold px-6 py-3 rounded-xl shadow-md hover:bg-[#088C97] transition">
          Contactar
        </a>
      </div>

      <div class="bg-[#09A2AE] text-white rounded-2xl shadow-lg p-6 space-y-4">

        <div class="faq-item">
          <button class="faq-toggle flex items-start w-full text-left gap-3">
            <span class="faq-icon font-bold transform transition-transform">➤</span>
            <p>Quais são os horários de funcionamento da clínica?</p>
          </button>
          <div class="faq-content hidden mt-1 ml-6 text-sm">
            Os nossos horários são de segunda a sexta, das 09h00 às 19h00.
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-toggle flex items-start w-full text-left gap-3">
            <span class="faq-icon font-bold transform transition-transform">➤</span>
            <p>É necessário marcar consulta?</p>
          </button>
          <div class="faq-content hidden mt-1 ml-6 text-sm">
            Sim, recomendamos marcação para garantir disponibilidade.
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-toggle flex items-start w-full text-left gap-3">
            <span class="faq-icon font-bold transform transition-transform">➤</span>
            <p>Como posso marcar uma consulta?</p>
          </button>
          <div class="faq-content hidden mt-1 ml-6 text-sm">
            Pode marcar por telefone, presencialmente ou online.
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-toggle flex items-start w-full text-left gap-3">
            <span class="faq-icon font-bold transform transition-transform">➤</span>
            <p>Aceitam seguros de saúde?</p>
          </button>
          <div class="faq-content hidden mt-1 ml-6 text-sm">
            Sim, trabalhamos com várias seguradoras.
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-toggle flex items-start w-full text-left gap-3">
            <span class="faq-icon font-bold transform transition-transform">➤</span>
            <p>Qual é o procedimento para consultas de especialidade?</p>
          </button>
          <div class="faq-content hidden mt-1 ml-6 text-sm">
            Geralmente requer avaliação inicial e encaminhamento interno.
          </div>
        </div>

        <div class="faq-item">
          <button class="faq-toggle flex items-start w-full text-left gap-3">
            <span class="faq-icon font-bold transform transition-transform">➤</span>
            <p>Como posso aceder à clínica?</p>
          </button>
          <div class="faq-content hidden mt-1 ml-6 text-sm">
            Estamos no centro da cidade, com estacionamento e transportes próximos.
          </div>
        </div>
      </div>
    </div>
  </section>

</main>

<?php include 'includes/footer.php'; ?>

<script>
  document.querySelectorAll(".faq-toggle").forEach(btn => {
    btn.addEventListener("click", () => {
      const content = btn.nextElementSibling;
      const icon = btn.querySelector(".faq-icon");
      content.classList.toggle("hidden");
      icon.classList.toggle("rotate-90");
    });
  });
</script>

</body>
</html>
