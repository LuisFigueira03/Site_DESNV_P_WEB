<?php
session_start();
require 'includes/connection.php';

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

// Mensagens do feedback (para evitar "Undefined variable")
$feedback_sucesso = $_SESSION['feedback_sucesso'] ?? '';
$feedback_erro    = $_SESSION['feedback_erro'] ?? '';
unset($_SESSION['feedback_sucesso'], $_SESSION['feedback_erro']);

// Dados do utente
$sqlUt = "
    SELECT u.id,
           u.nome,
           u.email,
           u.telemovel,
           u.num_utente_saude,
           u.nif,
           u.data_nascimento,
           a.nome AS acordo_nome
    FROM utente u
    LEFT JOIN acordo a ON a.id = u.acordo_id
    WHERE u.id = :id
";
$stmtUt = $dbh->prepare($sqlUt);
$stmtUt->bindValue(':id', $utente_id, PDO::PARAM_INT);
$stmtUt->execute();
$utente = $stmtUt->fetch(PDO::FETCH_ASSOC);

if (!$utente) {
    header('Location: login.php');
    exit;
}

// Consultas do utente
$sqlCons = "
    SELECT c.id,
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
    ORDER BY c.data_consulta DESC, c.hora_inicio DESC
";
$stmt = $dbh->prepare($sqlCons);
$stmt->bindValue(':uid', $utente_id, PDO::PARAM_INT);
$stmt->execute();
$consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Separar próximas / anteriores
$hoje = date('Y-m-d');
$consultasProximas  = [];
$consultasAnteriores = [];

foreach ($consultas as $c) {
    if (($c['data_consulta'] ?? '') >= $hoje) $consultasProximas[] = $c;
    else $consultasAnteriores[] = $c;
}

// Exames do utente
$sqlEx = "
    SELECT e.id,
           e.tipo_exame,
           e.estado,
           e.observacoes,
           e.codigo,
           c.data_consulta,
           DATE_FORMAT(c.hora_inicio, '%H:%i') AS hora_inicio,
           m.nome AS medico_nome
    FROM exame e
    JOIN consulta c ON c.id = e.consulta_id
    JOIN medico m   ON m.id = c.medico_id
    WHERE c.utente_id = :uid
    ORDER BY c.data_consulta DESC, c.hora_inicio DESC
";
$stmtEx = $dbh->prepare($sqlEx);
$stmtEx->bindValue(':uid', $utente_id, PDO::PARAM_INT);
$stmtEx->execute();
$exames = $stmtEx->fetchAll(PDO::FETCH_ASSOC);

// Receitas do utente
$sqlRec = "
    SELECT r.id,
           r.data_emissao,
           r.texto,
           r.codigo,
           m.nome AS medico_nome
    FROM receita r
    JOIN medico m ON m.id = r.medico_id
    WHERE r.utente_id = :uid
    ORDER BY r.data_emissao DESC
";
$stmtRec = $dbh->prepare($sqlRec);
$stmtRec->bindValue(':uid', $utente_id, PDO::PARAM_INT);
$stmtRec->execute();
$receitas = $stmtRec->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Área do utente</title>

  <link rel="stylesheet" href="src/css/output.css"/>
  <link rel="icon" type="image/png" href="imagens/logo-sem-fundo.png" />
</head>

<body class="bg-gray-200 text-slate-800 antialiased">

<?php include 'includes/navbar.php'; ?>

<div class="min-h-screen flex flex-col">
  <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1">

    <!-- Cabeçalho e marcar -->
    <header class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-[#09A2AE]">
          Olá, <?= htmlspecialchars($utente_nome ?: ($utente['nome'] ?? '')) ?>
        </h1>
        <p class="text-slate-600 mt-1 text-sm">
          Bem-vindo à sua área de utente.
        </p>
      </div>

      <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
        <a href="marcar_consulta.php"
           class="inline-flex justify-center items-center rounded-md bg-[#09A2AE] px-4 py-2 text-sm font-semibold text-white hover:opacity-90 w-full md:w-auto">
          Marcar nova consulta
        </a>

        <a href="#dar-feedback"
           class="inline-flex justify-center items-center rounded-md border border-[#09A2AE] px-4 py-2 text-sm font-semibold text-[#09A2AE] hover:bg-[#09A2AE] hover:text-white transition w-full md:w-auto">
          Dar feedback
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
            <dd><?= htmlspecialchars($utente['nome'] ?? '') ?></dd>
          </div>

          <div>
            <dt class="font-medium text-slate-600">Email</dt>
            <dd class="break-words"><?= htmlspecialchars($utente['email'] ?? '') ?></dd>
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
            <dd><?= !empty($utente['acordo_nome']) ? htmlspecialchars($utente['acordo_nome']) : 'Particular' ?></dd>
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
                      <td class="py-2 px-2 sm:px-3 whitespace-nowrap"><?= htmlspecialchars($c['data_consulta'] ?? '') ?></td>
                      <td class="py-2 px-2 sm:px-3 whitespace-nowrap"><?= htmlspecialchars($c['hora_inicio'] ?? '') ?></td>
                      <td class="py-2 px-2 sm:px-3"><?= htmlspecialchars($c['medico_nome'] ?? '') ?></td>
                      <td class="py-2 px-2 sm:px-3"><?= htmlspecialchars($c['especialidade_nome'] ?? '') ?></td>
                      <td class="py-2 px-2 sm:px-3"><?= htmlspecialchars($c['tipo'] ?? '') ?></td>
                      <td class="py-2 px-2 sm:px-3"><?= htmlspecialchars($c['estado'] ?? '') ?></td>
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

          <?php if (empty($consultasAnteriores)): ?>
            <p class="text-sm text-slate-600">Ainda não tem consultas anteriores.</p>
          <?php else: ?>
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
                  <?php foreach ($consultasAnteriores as $c): ?>
                    <tr>
                      <td class="py-2 px-2 sm:px-3 whitespace-nowrap"><?= htmlspecialchars($c['data_consulta'] ?? '') ?></td>
                      <td class="py-2 px-2 sm:px-3 whitespace-nowrap"><?= htmlspecialchars($c['hora_inicio'] ?? '') ?></td>
                      <td class="py-2 px-2 sm:px-3"><?= htmlspecialchars($c['medico_nome'] ?? '') ?></td>
                      <td class="py-2 px-2 sm:px-3"><?= htmlspecialchars($c['especialidade_nome'] ?? '') ?></td>
                      <td class="py-2 px-2 sm:px-3"><?= htmlspecialchars($c['tipo'] ?? '') ?></td>
                      <td class="py-2 px-2 sm:px-3"><?= htmlspecialchars($c['estado'] ?? '') ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

        <!-- Exames -->
        <div class="bg-white rounded-xl shadow p-6">
          <h2 class="text-lg font-semibold mb-4">Exames</h2>

          <?php if (empty($exames)): ?>
            <p class="text-sm text-slate-600">Ainda não tem exames registados.</p>
          <?php else: ?>
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
                  <?php foreach ($exames as $e): ?>
                    <tr>
                      <td class="py-2 px-2 sm:px-3 whitespace-nowrap"><?= htmlspecialchars($e['data_consulta'] ?? '') ?></td>
                      <td class="py-2 px-2 sm:px-3 whitespace-nowrap"><?= htmlspecialchars($e['hora_inicio'] ?? '') ?></td>
                      <td class="py-2 px-2 sm:px-3"><?= htmlspecialchars($e['medico_nome'] ?? '') ?></td>
                      <td class="py-2 px-2 sm:px-3"><?= htmlspecialchars($e['tipo_exame'] ?? '') ?></td>
                      <td class="py-2 px-2 sm:px-3"><?= htmlspecialchars($e['estado'] ?? '') ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

        <!-- Receitas -->
        <div class="bg-white rounded-xl shadow p-6">
          <h2 class="text-lg font-semibold mb-4">Receitas</h2>

          <?php if (empty($receitas)): ?>
            <p class="text-sm text-slate-600">Ainda não tem receitas registadas.</p>
          <?php else: ?>
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
                  <?php foreach ($receitas as $r): ?>
                    <tr>
                      <td class="py-2 px-2 sm:px-3 whitespace-nowrap">
                        <?= htmlspecialchars($r['data_emissao'] ?? '') ?>
                      </td>
                      <td class="py-2 px-2 sm:px-3 whitespace-nowrap">
                        <?= htmlspecialchars($r['medico_nome'] ?? '') ?>
                      </td>
                      <td class="py-2 px-2 sm:px-3">
                        <?= htmlspecialchars($r['texto'] ?? '') ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

      </section>
    </div>

    <!-- FEEDBACK -->
    <section id="dar-feedback" class="mt-10">
      <div class="bg-white rounded-2xl shadow overflow-hidden">

        <div class="bg-[#09A2AE] p-6 text-center text-white">
          <h3 class="text-xl font-bold">A sua opinião é importante</h3>
          <p class="text-sm opacity-90">Ajude-nos a melhorar os nossos serviços</p>
        </div>

        <?php if ($feedback_sucesso): ?>
          <div class="m-6 bg-emerald-100 text-emerald-800 px-4 py-3 rounded">
            <?= htmlspecialchars($feedback_sucesso) ?>
          </div>
        <?php endif; ?>

        <?php if ($feedback_erro): ?>
          <div class="m-6 bg-red-100 text-red-800 px-4 py-3 rounded">
            <?= htmlspecialchars($feedback_erro) ?>
          </div>
        <?php endif; ?>

        <form action="processar_feedback.php" method="POST" class="p-6 space-y-4">

          <div>
            <label class="text-sm font-semibold">Nome</label>
            <input type="text" value="<?= htmlspecialchars($utente['nome'] ?? '') ?>" disabled
                   class="w-full px-4 py-2 bg-gray-100 border rounded">
          </div>

          <div>
            <label class="text-sm font-semibold">Classificação</label>
            <select name="estrelas" required class="w-full px-4 py-2 border rounded">
              <option value="5">5 – Excelente</option>
              <option value="4">4 – Muito Bom</option>
              <option value="3">3 – Bom</option>
              <option value="2">2 – Regular</option>
              <option value="1">1 – Fraco</option>
              <option value="0">0 – Insatisfatório</option>
            </select>
          </div>

          <div>
            <label class="text-sm font-semibold">Comentário</label>
            <textarea name="comentario" rows="4"
                      class="w-full px-4 py-2 border rounded"
                      placeholder="Conte-nos a sua experiência..."></textarea>
          </div>

          <div class="text-center">
            <button class="bg-[#09A2AE] text-white px-8 py-3 rounded-lg font-semibold hover:opacity-90">
              Enviar Feedback
            </button>
          </div>

        </form>
      </div>
    </section>

  </main>

  <?php include 'includes/footer.php'; ?>
</div>

</body>
</html>
