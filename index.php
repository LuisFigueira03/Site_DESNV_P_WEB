<?php 
$user = 'web1';
$pass = 'web1';

try {
    $dbh = new PDO('mysql:host=localhost;dbname=web2;charset=utf8mb4', $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na ligação à base de dados.");
}

$sql  = 'SELECT * FROM especialidade WHERE visivel = 1';
$stmt = $dbh->prepare($sql);
$stmt->execute();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hospital da Luz - Montemor-o-Velho</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<style>
  .cor {
    background-color: #09A2AE;
  }
  .texto-cor {
    color: #09A2AE;
  }
</style>

<body class="bg-gray-200 text-gray-900">

<?php include 'navbar.php'; ?>

  <!-- Conteúdo -->
  <main class="max-w-7xl mx-auto px-6 mt-8">
    <img src="imagens/sala de espera.webp" alt="Sala de espera" class="rounded-lg shadow-lg w-full">

    <!-- Sobre / Equipa -->
    <section id="sobre" class="py-6 grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
      <img src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?q=80&w=900&auto=format&fit=crop" alt="Médica" class="rounded-lg shadow md:order-1 order-2"/>
      <div class="space-y-3 md:order-2 order-1">
        <h2 class="text-xl font-extrabold">A nossa missão é cuidar com excelência.</h2>
        <p class="text-sm leading-relaxed">Combinamos tecnologia de ponta com profissionais dedicados para oferecer um acompanhamento próximo e humanizado.</p>
        <p class="text-sm leading-relaxed">Disponibilizamos consultas em múltiplas especialidades, exames complementares e cuidados diferenciados para toda a família.</p>
        <p class="text-sm leading-relaxed">Estamos localizados no coração de Montemor-o-Velho, com fácil estacionamento e acessos.</p>
      </div>
    </section>

    <!-- Especialidades -->
    <section id="especialidades" class="py-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">

        <?php 
        while($esp = $stmt->fetchObject()){
            $idEsp   = $esp->id;
            $nome    = $esp->nome;
            $imagem  = $esp->imagem;
        ?>
          <div class="bg-[#09A2AE] rounded-lg text-center overflow-hidden">
            <a href="especialidade.php?esp=<?= (int)$idEsp ?>" class="block hover:opacity-90 transition">
              <div class="aspect-[3/4]">
                <img 
                  src="imagens/<?= htmlspecialchars($imagem) ?>" 
                  alt="<?= htmlspecialchars($nome) ?>" 
                  class="w-full h-full object-cover">
              </div>
              <p class="text-xs font-semibold tracking-wide py-3 text-white">
                <?= htmlspecialchars($nome) ?>
              </p>
            </a>
          </div>
        <?php 
        } 
        ?>

      </div>
    </section>

    <!-- Mapa -->
    <section class="bg-[#09A2AE] p-4 rounded-xl">
      <div class="rounded-lg overflow-hidden shadow aspect-video">
        <iframe
          class="w-full h-full border-0"
          loading="lazy"
          allowfullscreen
          referrerpolicy="no-referrer-when-downgrade"
          src="https://www.google.com/maps?q=Coimbra%20Business%20School%20ISCAC&output=embed">
        </iframe>
      </div>

      <!-- Legenda -->
      <div class="justify-center flex items-start gap-2 text-slate-700 mt-8">
        <span class="shrink-0 mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full border border-white text-white">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 22s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11Z"/>
            <circle cx="12" cy="11" r="2.5"/>
          </svg>
        </span>
        <p class="text-sm md:text-base text-white">
          <strong>Coimbra Business School | ISCAC</strong>, Quinta Agrícola, Bencanta, 3045-601 Coimbra
        </p>
      </div>
    </section>

    <!-- Testemunhos -->
    <section id="testemunhos" class="bg-gray-200 py-10">
        <div class="max-w-6xl mx-auto px-4">

            <!-- Título e subtítulo -->
            <h2 class="text-2xl md:text-3xl font-extrabold text-center">Testemunhos</h2>
            <p class="mt-3 text-center text-sm md:text-base text-slate-700">
                Conheça os depoimentos dos pacientes que confiam no nosso trabalho.
            </p>
            <p class="text-center text-sm md:text-base font-semibold text-slate-700">
                Estamos honrados em cuidar da saúde de todos!
            </p>

            <!-- Grelha -->
            <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">

                <!-- Cartão 1 -->
                <div class="bg-[#09A2AE] rounded-2xl shadow p-6 text-center min-h-[220px]">

                    <p class="font-semibold text-white">Nome:</p>
                    <p class="text-white">Luís Figueira</p>

                    <p class="mt-4 font-semibold text-white">Número de estrelas:</p>
                    <div class="mt-1 inline-flex gap-1" aria-label="Classificação: 4 de 5">

                        <!-- 4 cheias -->
                        <svg viewBox="0 0 24 24" class="h-5 w-5 text-yellow-400 fill-current" aria-hidden="true">
                            <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.62L12 2 9.19 8.62 2 9.24l5.46 4.73L5.82 21z"/>
                        </svg>
                        <svg viewBox="0 0 24 24" class="h-5 w-5 text-yellow-400 fill-current" aria-hidden="true">
                            <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.62L12 2 9.19 8.62 2 9.24l5.46 4.73L5.82 21z"/>
                        </svg>
                        <svg viewBox="0 0 24 24" class="h-5 w-5 text-yellow-400 fill-current" aria-hidden="true">
                            <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.62L12 2 9.19 8.62 2 9.24l5.46 4.73L5.82 21z"/>
                        </svg>
                        <svg viewBox="0 0 24 24" class="h-5 w-5 text-yellow-400 fill-current" aria-hidden="true">
                            <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.62L12 2 9.19 8.62 2 9.24l5.46 4.73L5.82 21z"/>
                        </svg>

                        <!-- 1 vazia -->
                        <svg viewBox="0 0 24 24" class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.62L12 2 9.19 8.62 2 9.24l5.46 4.73L5.82 21z"></path>
                        </svg>
                    </div>

                    <p class="mt-4 font-semibold text-white">Comentário:</p>
                    <p class="text-sm text-white">Excelente serviço e atendimento!</p>

                </div>

                <!-- Cartão 2 -->
                <div class="bg-[#09A2AE] rounded-2xl shadow p-6 text-center min-h-[220px]">

                    <p class="font-semibold text-white">Nome:</p>
                    <p class="text-white">Hugo Grou</p>

                    <p class="mt-4 font-semibold text-white">Número de estrelas:</p>
                    <div class="mt-1 inline-flex gap-1" aria-label="Classificação: 5 de 5">

                        <!-- 5 cheias -->
                        <svg viewBox="0 0 24 24" class="h-5 w-5 text-yellow-400 fill-current"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.62L12 2 9.19 8.62 2 9.24l5.46 4.73L5.82 21z"/></svg>
                        <svg viewBox="0 0 24 24" class="h-5 w-5 text-yellow-400 fill-current"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.62L12 2 9.19 8.62 2 9.24l5.46 4.73L5.82 21z"/></svg>
                        <svg viewBox="0 0 24 24" class="h-5 w-5 text-yellow-400 fill-current"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.62L12 2 9.19 8.62 2 9.24l5.46 4.73L5.82 21z"/></svg>
                        <svg viewBox="0 0 24 24" class="h-5 w-5 text-yellow-400 fill-current"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.62L12 2 9.19 8.62 2 9.24l5.46 4.73L5.82 21z"/></svg>
                        <svg viewBox="0 0 24 24" class="h-5 w-5 text-yellow-400 fill-current"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.62L12 2 9.19 8.62 2 9.24l5.46 4.73L5.82 21z"/></svg>

                    </div>

                    <p class="mt-4 font-semibold text-white">Comentário:</p>
                    <p class="text-sm text-white">Gostei do atendimento!</p>

                </div>

                <!-- Cartão 3 -->
                <div class="bg-[#09A2AE] rounded-2xl shadow p-6 text-center min-h-[220px]">

                    <p class="font-semibold text-white">Nome:</p>
                    <p class="text-white">Afonso Portugal</p>

                    <p class="mt-4 font-semibold text-white">Número de estrelas:</p>
                    <div class="mt-1 inline-flex gap-1" aria-label="Classificação: 3 de 5">

                        <!-- 3 cheias -->
                        <svg viewBox="0 0 24 24" class="h-5 w-5 text-yellow-400 fill-current"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.62L12 2 9.19 8.62 2 9.24l5.46 4.73L5.82 21z"/></svg>
                        <svg viewBox="0 0 24 24" class="h-5 w-5 text-yellow-400 fill-current"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.62L12 2 9.19 8.62 2 9.24l5.46 4.73L5.82 21z"/></svg>
                        <svg viewBox="0 0 24 24" class="h-5 w-5 text-yellow-400 fill-current"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.62L12 2 9.19 8.62 2 9.24l5.46 4.73L5.82 21z"/></svg>

                        <!-- 2 vazias -->
                        <svg viewBox="0 0 24 24" class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.62L12 2 9.19 8.62 2 9.24l5.46 4.73L5.82 21z"/>
                        </svg>
                        <svg viewBox="0 0 24 24" class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.62L12 2 9.19 8.62 2 9.24l5.46 4.73L5.82 21z"/>
                        </svg>

                    </div>

                    <p class="mt-4 font-semibold text-white">Comentário:</p>
                    <p class="text-sm text-white">Bom atendimento!</p>

                </div>

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

</body>

<?php include 'footer.php'; ?>

<script>
  document.querySelectorAll(".faq-toggle").forEach(btn => {
    btn.addEventListener("click", () => {
      const content = btn.nextElementSibling;
      const icon = btn.querySelector(".faq-icon");

      content.classList.toggle("hidden");
      icon.classList.toggle("rotate-90");  // roda para a direita (parece para baixo)
    });
  });
</script>

</html>
