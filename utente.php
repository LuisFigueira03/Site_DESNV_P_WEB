<?php
session_start();

// Apenas utente autenticado
if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'utente') {
    header('Location: login.php');
    exit;
}

$utente_id   = $_SESSION['utente_id'] ?? null;
$utente_nome = $_SESSION['utente_nome'] ?? '';

if (!$utente_id) {
    header('Location: login.php');
    exit;
}

// Mensagem de sucesso vinda da marcação
$sucesso_marcacao = $_SESSION['sucesso_marcacao'] ?? '';
unset($_SESSION['sucesso_marcacao']);

$user = 'web1';
$pass = 'web1';

try {
    $dbh = new PDO('mysql:host=localhost;dbname=web2;charset=utf8mb4', $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na ligação à base de dados.");
}

$sqlUt = "SELECT u.id,
                 u.nome,
                 u.email,
                 u.telemovel,
                 u.num_utente_saude,
                 u.nif,
                 u.data_nascimento,
                 a.nome AS acordo_nome
          FROM utente u
          LEFT JOIN acordo a ON a.id = u.acordo_id
          WHERE u.id = :id";
$stmtUt = $dbh->prepare($sqlUt);
$stmtUt->bindValue(':id', $utente_id, PDO::PARAM_INT);
$stmtUt->execute();
$utente = $stmtUt->fetch(PDO::FETCH_ASSOC);

if (!$utente) {
    header('Location: login.php');
    exit;
}

$sqlCons = "SELECT c.id,
                   c.data_consulta,
                   DATE_FORMAT(c.hora_inicio, '%H:%i') AS hora_inicio,
                   DATE_FORMAT(c.hora_fim, '%H:%i')   AS hora_fim,
                   c.estado,
                   c.tipo,
                   m.nome AS medico_nome,
                   e.nome AS especialidade_nome,
                   a.nome AS acordo_nome
            FROM consulta c
            JOIN medico m        ON m.id = c.medico_id
            JOIN especialidade e ON e.id = m.especialidade_id
            LEFT JOIN acordo a   ON a.id = c.acordo_id
            WHERE c.utente_id = :uid
            ORDER BY c.data_consulta DESC, c.hora_inicio DESC";
$stmt = $dbh->prepare($sqlCons);
$stmt->bindValue(':uid', $utente_id, PDO::PARAM_INT);
$stmt->execute();
$consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$consultasProximas = [];
$hoje = date('Y-m-d');

foreach ($consultas as $c) {
    if ($c['data_consulta'] >= $hoje) {
        $consultasProximas[] = $c;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Área do utente</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .cor { background-color: #09A2AE; }
    .texto-cor { color: #09A2AE; }
  </style>
</head>
<body class="bg-gray-200 text-slate-800 antialiased">

<div class="min-h-screen flex flex-col">
  <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1">

    <!-- Cabeçalho e marcar -->
    <header class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight texto-cor">
          Olá, <?= htmlspecialchars($utente_nome ?: $utente['nome']) ?>
        </h1>
        <p class="text-slate-600 mt-1 text-sm">
          Bem-vindo à sua área de utente.
        </p>
      </div>

      <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
        <a href="marcar_consulta.php"
           class="inline-flex justify-center items-center rounded-md cor px-4 py-2 text-sm font-semibold text-white hover:opacity-90 w-full md:w-auto">
          Marcar nova consulta
        </a>

        <a href="logout.php"
           class="inline-flex justify-center items-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:opacity-90 w-full md:w-auto">
          Terminar sessão
        </a>
      </div>
    </header>

    <!-- Mensagem de sucesso -->
    <?php if (!empty($sucesso_marcacao)): ?>
      <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700">
        <?= htmlspecialchars($sucesso_marcacao) ?>
      </div>
    <?php endif; ?>

    <!-- Grid principal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

      <!-- Dados do utente -->
      <section class="lg:col-span-1 bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Os seus dados</h2>

        <dl class="space-y-3 text-sm">
          <div>
            <dt class="font-medium text-slate-600">Nome</dt>
            <dd><?= htmlspecialchars($utente['nome']) ?></dd>
          </div>

          <div>
            <dt class="font-medium text-slate-600">Email</dt>
            <dd class="break-words"><?= htmlspecialchars($utente['email']) ?></dd>
          </div>

          <?php if (!empty($utente['telemovel'])): ?>
          <div>
            <dt class="font-medium text-slate-600">Telemóvel</dt>
            <dd><?= htmlspecialchars($utente['telemovel']) ?></dd>
          </div>
          <?php endif; ?>

          <?php if (!empty($utente['num_utente_saude'])): ?>
          <div>
            <dt class="font-medium text-slate-600">Nº utente de saúde</dt>
            <dd><?= htmlspecialchars($utente['num_utente_saude']) ?></dd>
          </div>
          <?php endif; ?>

          <?php if (!empty($utente['nif'])): ?>
          <div>
            <dt class="font-medium text-slate-600">NIF</dt>
            <dd><?= htmlspecialchars($utente['nif']) ?></dd>
          </div>
          <?php endif; ?>

          <?php if (!empty($utente['data_nascimento'])): ?>
          <div>
            <dt class="font-medium text-slate-600">Data de nascimento</dt>
            <dd><?= htmlspecialchars($utente['data_nascimento']) ?></dd>
          </div>
          <?php endif; ?>

          <div>
            <dt class="font-medium text-slate-600">Seguro / acordo</dt>
            <dd>
              <?= $utente['acordo_nome'] ? htmlspecialchars($utente['acordo_nome']) : 'Particular' ?>
            </dd>
          </div>
        </dl>
      </section>

      <!-- Secção direita -->
      <section class="lg:col-span-2 space-y-6">

        <!-- Próximas consultas -->
        <div class="bg-white rounded-xl shadow p-6">
          <h2 class="text-lg font-semibold mb-4">Próximas consultas</h2>

          <?php if (empty($consultasProximas)): ?>
            <p class="text-sm text-slate-600">Não tem consultas futuras marcadas.</p>
          <?php else: ?>
            <div class="overflow-x-auto">
              <table class="min-w-full text-xs sm:text-sm">
                <thead>
                  <tr class="border-b border-slate-200 bg-slate-50">
                    <th class="text-left py-2 px-2 sm:px-3">Data</th>
                    <th class="text-left py-2 px-2 sm:px-3">Hora</th>
                    <th class="text-left py-2 px-2 sm:px-3">Médico</th>
                    <th class="text-left py-2 px-2 sm:px-3">Especialidade</th>
                    <th class="text-left py-2 px-2 sm:px-3">Tipo</th>
                    <th class="text-left py-2 px-2 sm:px-3">Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($consultasProximas as $c): ?>
                    <tr class="border-b border-slate-100">
                      <td class="py-2 px-2 sm:px-3 whitespace-nowrap"><?= htmlspecialchars($c['data_consulta']) ?></td>
                      <td class="py-2 px-2 sm:px-3 whitespace-nowrap"><?= htmlspecialchars($c['hora_inicio']) ?></td>
                      <td class="py-2 px-2 sm:px-3"><?= htmlspecialchars($c['medico_nome']) ?></td>
                      <td class="py-2 px-2 sm:px-3"><?= htmlspecialchars($c['especialidade_nome']) ?></td>
                      <td class="py-2 px-2 sm:px-3"><?= htmlspecialchars(ucfirst($c['tipo'])) ?></td>
                      <td class="py-2 px-2 sm:px-3"><?= htmlspecialchars(ucfirst($c['estado'])) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

        <!-- Consultas anteriores -->
        <div class="bg-white rounded-xl shadow p-6">
          <h2 class="text-lg font-semibold mb-4">Consultas anteriores</h2>

          <div class="overflow-x-auto max-h-72 border border-slate-100 rounded-lg">
            <table class="min-w-full text-xs sm:text-sm">
              <thead class="bg-slate-50">
                <tr class="border-b border-slate-200">
                  <th class="text-left py-2 px-2 sm:px-3">Data</th>
                  <th class="text-left py-2 px-2 sm:px-3">Hora</th>
                  <th class="text-left py-2 px-2 sm:px-3">Médico</th>
                  <th class="text-left py-2 px-2 sm:px-3">Especialidade</th>
                  <th class="text-left py-2 px-2 sm:px-3">Tipo</th>
                  <th class="text-left py-2 px-2 sm:px-3">Estado</th>
                </tr>
              </thead>

              <tbody class="divide-y divide-slate-100">
                <tr>
                  <td class="py-2 px-2 sm:px-3">2024-01-10</td>
                  <td class="py-2 px-2 sm:px-3">10:30</td>
                  <td class="py-2 px-2 sm:px-3">Dr. Exemplo</td>
                  <td class="py-2 px-2 sm:px-3">Cardiologia</td>
                  <td class="py-2 px-2 sm:px-3">Presencial</td>
                  <td class="py-2 px-2 sm:px-3">Realizada</td>
                </tr>

                <tr>
                  <td class="py-2 px-2 sm:px-3">2023-12-20</td>
                  <td class="py-2 px-2 sm:px-3">15:00</td>
                  <td class="py-2 px-2 sm:px-3">Dr. Exemplo</td>
                  <td class="py-2 px-2 sm:px-3">Dermatologia</td>
                  <td class="py-2 px-2 sm:px-3">Presencial</td>
                  <td class="py-2 px-2 sm:px-3">Realizada</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Exames -->
        <div class="bg-white rounded-xl shadow p-6">
          <h2 class="text-lg font-semibold mb-4">Exames</h2>

          <div class="overflow-x-auto max-h-72 border border-slate-100 rounded-lg">
            <table class="min-w-full text-xs sm:text-sm">
              <thead class="bg-slate-50">
                <tr class="border-b border-slate-200">
                  <th class="text-left py-2 px-2 sm:px-3">Data consulta</th>
                  <th class="text-left py-2 px-2 sm:px-3">Hora</th>
                  <th class="text-left py-2 px-2 sm:px-3">Médico</th>
                  <th class="text-left py-2 px-2 sm:px-3">Tipo de exame</th>
                  <th class="text-left py-2 px-2 sm:px-3">Estado</th>
                </tr>
              </thead>

              <tbody class="divide-y divide-slate-100">
                <tr>
                  <td class="py-2 px-2 sm:px-3">2023-11-08</td>
                  <td class="py-2 px-2 sm:px-3">14:00</td>
                  <td class="py-2 px-2 sm:px-3">Dr. Exemplo</td>
                  <td class="py-2 px-2 sm:px-3">Raio-X</td>
                  <td class="py-2 px-2 sm:px-3">Concluído</td>
                </tr>

                <tr>
                  <td class="py-2 px-2 sm:px-3">2023-10-22</td>
                  <td class="py-2 px-2 sm:px-3">09:00</td>
                  <td class="py-2 px-2 sm:px-3">Dr. Exemplo</td>
                  <td class="py-2 px-2 sm:px-3">Análise sanguínea</td>
                  <td class="py-2 px-2 sm:px-3">Concluído</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Receitas -->
        <div class="bg-white rounded-xl shadow p-6">
          <h2 class="text-lg font-semibold mb-4">Receitas</h2>

          <div class="overflow-x-auto max-h-72 border border-slate-100 rounded-lg">
            <table class="min-w-full text-xs sm:text-sm">
              <thead class="bg-slate-50">
                <tr class="border-b border-slate-200">
                  <th class="text-left py-2 px-2 sm:px-3">Data emissão</th>
                  <th class="text-left py-2 px-2 sm:px-3">Médico</th>
                  <th class="text-left py-2 px-2 sm:px-3">Texto</th>
                </tr>
              </thead>

              <tbody class="divide-y divide-slate-100">
                <tr>
                  <td class="py-2 px-2 sm:px-3">2023-11-15</td>
                  <td class="py-2 px-2 sm:px-3">Dr. Exemplo</td>
                  <td class="py-2 px-2 sm:px-3">Ibuprofeno 600mg — tomar 3x ao dia.</td>
                </tr>

                <tr>
                  <td class="py-2 px-2 sm:px-3">2023-09-02</td>
                  <td class="py-2 px-2 sm:px-3">Dr. Exemplo</td>
                  <td class="py-2 px-2 sm:px-3">Vitamina D — 1 comprimido diário.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </section>
    </div>
  </main>

  <footer class="cor text-white mt-8 text-center">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 text-xs opacity-90">
      © <span id="ano"></span> Clínica da Luz — Montemor-o-Velho
    </div>
  </footer>
</div>

<script>
  document.getElementById('ano').textContent = new Date().getFullYear();
</script>

</body>
</html>
