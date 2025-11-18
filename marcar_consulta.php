<?php
session_start();

$pagina_retorno = 'utente.php';

// Apenas utente autenticado pode aceder a esta página
if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'utente') {
    header('Location: login.php');
    exit;
}

$utente_id = $_SESSION['utente_id'] ?? null;
if (!$utente_id) {
    header('Location: login.php');
    exit;
}

$user = 'web1';
$pass = 'web1';

try {
    $dbh = new PDO('mysql:host=localhost;dbname=web2;charset=utf8mb4', $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na ligação à base de dados.");
}

// Carregar dados do utente autenticado
$sqlUt = "SELECT id, nome, email, telemovel, num_utente_saude, acordo_id
          FROM utente
          WHERE id = :id";
$stmtUt = $dbh->prepare($sqlUt);
$stmtUt->bindValue(':id', $utente_id, PDO::PARAM_INT);
$stmtUt->execute();
$utente = $stmtUt->fetch(PDO::FETCH_ASSOC);

if (!$utente) {
  // se der erro, volta ao login
    header('Location: login.php');
    exit;
}

$erro = '';

$especialidade_id = '';
$medico_id        = '';
$acordo_id        = $utente['acordo_id'];
$local            = '';
$data_consulta    = '';
$hora_inicio      = '';
$observacoes      = '';
$consentimento    = 0;

$nome             = $utente['nome'];
$email            = $utente['email'];
$telemovel        = $utente['telemovel'];
$num_utente_saude = $utente['num_utente_saude'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $especialidade_id = $_POST['especialidade_id'] ?? '';
    $medico_id        = $_POST['medico_id'] ?? '';
    $acordo_id        = $_POST['acordo_id'] ?? $acordo_id;
    $local            = $_POST['local'] ?? '';

    $data_consulta    = $_POST['data_consulta'] ?? '';
    $hora_inicio      = $_POST['hora_inicio'] ?? '';

    $observacoes      = trim($_POST['observacoes'] ?? '');
    $consentimento    = isset($_POST['consentimento']) ? 1 : 0;

    $nome             = $utente['nome'];
    $email            = $utente['email'];
    $telemovel        = $utente['telemovel'];
    $num_utente_saude = $utente['num_utente_saude'];

    if (
        $nome === '' ||
        $email === '' ||
        !$data_consulta ||
        !$hora_inicio ||
        !$medico_id ||
        !$consentimento
    ) {
        $erro = "Preencha todos os campos obrigatórios e aceite a política de privacidade.";
    } else {

        $hora_fim = date("H:i:s", strtotime($hora_inicio . ":00 +30 minutes"));

        try {
            $dbh->beginTransaction();

            $sql = "INSERT INTO consulta
                    (medico_id,utente_id,data_consulta,hora_inicio,hora_fim,
                     estado,acordo_id,tipo,observacoes,created_at)
                    VALUES
                    (:m,:u,:d,:hi,:hf,'marcada',:ac,'presencial',:obs,NOW())";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':m',  $medico_id, PDO::PARAM_INT);
            $stmt->bindValue(':u',  $utente_id, PDO::PARAM_INT);
            $stmt->bindValue(':d',  $data_consulta);
            $stmt->bindValue(':hi', $hora_inicio . ":00");
            $stmt->bindValue(':hf', $hora_fim);

            if ($acordo_id) {
                $stmt->bindValue(':ac', $acordo_id, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':ac', null, PDO::PARAM_NULL);
            }

            $stmt->bindValue(':obs', $observacoes);
            $stmt->execute();

            $dbh->commit();

            $_SESSION['sucesso_marcacao'] = 'Marcação registada com sucesso!';

            header('Location: ' . $pagina_retorno);
            exit;

        } catch (Exception $e) {
            $dbh->rollBack();
            $erro = "Erro ao gravar a marcação.";
        }
    }
}

$especialidades = $dbh->query("SELECT id, nome FROM especialidade ORDER BY nome")
                      ->fetchAll(PDO::FETCH_ASSOC);

$medicos = $dbh->query("SELECT m.id,
                               m.nome,
                               m.especialidade_id,
                               e.nome AS esp
                        FROM medico m
                        JOIN especialidade e ON e.id = m.especialidade_id
                        WHERE m.ativo = 1
                        ORDER BY m.nome")
               ->fetchAll(PDO::FETCH_ASSOC);

$acordos = $dbh->query("SELECT id, nome FROM acordo WHERE ativo = 1 ORDER BY nome")
               ->fetchAll(PDO::FETCH_ASSOC);

$bookings = [];

$sql = "SELECT medico_id, data_consulta, DATE_FORMAT(hora_inicio, '%H:%i') AS hora
        FROM consulta
        WHERE estado IN ('marcada','realizada')";
$stmt = $dbh->query($sql);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $medicoId = (string)$row['medico_id'];
    $data     = $row['data_consulta'];
    $hora     = $row['hora'];

    if (!isset($bookings[$medicoId])) {
        $bookings[$medicoId] = [];
    }
    if (!isset($bookings[$medicoId][$data])) {
        $bookings[$medicoId][$data] = [];
    }
    $bookings[$medicoId][$data][] = $hora;
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <title>Marcação de Consulta</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .cor {
      background-color: #09A2AE;
    }
    .texto-cor {
      color: #09A2AE;
    }
  </style>
</head>
<body class="bg-gray-100 text-slate-800 antialiased">

<div class="min-h-screen flex flex-col">
  <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1">

    <header class="mb-6 flex items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight texto-cor">Marcação de Consulta</h1>
        <p class="text-slate-600 mt-1 text-sm">Preencha os dados para efetuar a sua marcação.</p>
      </div>

      <!-- Botão Voltar -->
      <a href="<?= htmlspecialchars($pagina_retorno) ?>"
         class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 bg-white hover:bg-slate-50">
        Voltar
      </a>
    </header>

    <?php if ($erro): ?>
      <div class="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
        <?= htmlspecialchars($erro) ?>
      </div>
    <?php endif; ?>

    <form method="post" action="" class="bg-white rounded-xl shadow p-6 space-y-6">

      <section>
        <h2 class="text-lg font-semibold mb-4 texto-cor">Dados da consulta</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="especialidade" class="block text-sm font-medium mb-1">Especialidade *</label>
            <select id="especialidade" name="especialidade_id"
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#09A2AE]">
              <option value="">Selecione...</option>
              <?php foreach($especialidades as $esp): ?>
                <option value="<?= $esp['id'] ?>"
                  <?= ($especialidade_id == $esp['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($esp['nome']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label for="profissional" class="block text-sm font-medium mb-1">Profissional *</label>
            <select id="profissional" name="medico_id"
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#09A2AE]">
              <option value="">Selecione...</option>
              <?php foreach($medicos as $med): ?>
                <option value="<?= $med['id'] ?>"
                        data-esp-id="<?= $med['especialidade_id'] ?>"
                        <?= ($medico_id == $med['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($med['nome']) ?> (<?= htmlspecialchars($med['esp']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label for="local" class="block text-sm font-medium mb-1">Local</label>
            <select id="local" name="local"
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#09A2AE]">
              <option value="Montemor-o-Velho" <?= ($local === 'Montemor-o-Velho') ? 'selected' : '' ?>>Montemor-o-Velho</option>
              <option value="Coimbra" <?= ($local === 'Coimbra') ? 'selected' : '' ?>>Coimbra</option>
            </select>
          </div>

          <div>
            <label for="data_consulta" class="block text-sm font-medium mb-1">Data *</label>
            <input id="data_consulta" name="data_consulta" type="date"
                   value="<?= htmlspecialchars($data_consulta) ?>"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#09A2AE]">
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Hora *</label>

            <input type="hidden" id="hora_inicio" name="hora_inicio"
                   value="<?= htmlspecialchars($hora_inicio) ?>">

            <div id="lista_horas" class="grid grid-cols-3 gap-2 text-sm">
            </div>

            <p id="nota_horas" class="mt-1 text-xs text-slate-500">
              Escolha a data e o profissional para ver horários disponíveis.
            </p>
          </div>
        </div>

        <div class="md:col-span-2 flex items-start gap-3 bg-slate-50 border border-slate-200 rounded-md p-3 mt-5">
            <input id="consentimento" name="consentimento" type="checkbox" value="1"
                   class="mt-1 text-[#09A2AE] focus:ring-[#09A2AE]"
                   <?= $consentimento ? 'checked' : '' ?>>
            <label for="consentimento" class="text-xs text-slate-600">
              Li e aceito a política de privacidade. *
            </label>
        </div>
      </section>
        <div class="flex justify-end">          
          <button type="submit"  
                  class="rounded-md px-6 py-3 cor text-white font-semibold hover:opacity-90">
            Confirmar marcação
          </button>
      </div>
      </div>
    </form>
  </main>

  <footer class="cor text-white text-center">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 text-xs opacity-90">
      © <span id="ano"></span> Clínica da Luz — Montemor-o-Velho
    </div>
  </footer>
</div>

<script>
  document.getElementById('ano').textContent = new Date().getFullYear();

  const campoEspecialidade = document.getElementById('especialidade');
  const campoMedico        = document.getElementById('profissional');

  function filtrarMedicosPorEspecialidade() {
    const idEspEscolhida = campoEspecialidade.value;
    const opcoesMedico   = campoMedico.querySelectorAll('option');

    opcoesMedico.forEach(function (opt) {
      if (opt.value === '') {
        opt.hidden = false;
        return;
      }
      const idEspDoMedico = opt.getAttribute('data-esp-id');
      opt.hidden = (idEspEscolhida && idEspDoMedico !== idEspEscolhida);
    });
  }

  campoEspecialidade.addEventListener('change', filtrarMedicosPorEspecialidade);
  filtrarMedicosPorEspecialidade();

  const bookings = <?php echo json_encode($bookings, JSON_UNESCAPED_UNICODE); ?>;

  const campoData   = document.getElementById('data_consulta');
  const listaHoras  = document.getElementById('lista_horas');
  const inputHora   = document.getElementById('hora_inicio');
  const notaHoras   = document.getElementById('nota_horas');

  const HORAS_POSSIVEIS = [
    '09:00',
    '10:00',
    '11:00',
    '14:00',
    '15:00',
    '16:00',
    '17:00'
  ];

  function actualizarHoras() {
    const medicoId = campoMedico.value;
    const data     = campoData.value;

    listaHoras.innerHTML = '';
    inputHora.value = '';

    if (!medicoId || !data) {
      notaHoras.textContent = 'Escolha a data e o profissional para ver horários disponíveis.';
      return;
    }

    const horasOcupadas = (bookings[medicoId] && bookings[medicoId][data])
                          ? bookings[medicoId][data]
                          : [];

    notaHoras.textContent = 'Clique numa hora para seleccionar.';

    HORAS_POSSIVEIS.forEach(function (horaStr) {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = horaStr;
      btn.className = 'slot px-2 py-2 rounded-md border hover:bg-slate-50 text-center';

      if (horasOcupadas.indexOf(horaStr) !== -1) {
        btn.classList.add('opacity-40', 'pointer-events-none', 'cursor-not-allowed');
        btn.title = 'Já existe consulta nesta hora.';
      }

      btn.addEventListener('click', function () {
        if (btn.classList.contains('pointer-events-none')) return;

        inputHora.value = horaStr;

        listaHoras.querySelectorAll('.slot').forEach(function (b) {
          b.classList.remove('bg-[#09A2AE]','text-white','border-[#09A2AE]');
        });

        btn.classList.add('bg-[#09A2AE]','text-white','border-[#09A2AE]');
      });

      listaHoras.appendChild(btn);
    });
  }

  campoMedico.addEventListener('change', actualizarHoras);
  campoData.addEventListener('change', actualizarHoras);

  window.addEventListener('load', function () {
    if (campoData.value && campoMedico.value) {
      actualizarHoras();
      if (inputHora.value) {
        const btns = listaHoras.querySelectorAll('.slot');
        btns.forEach(function (b) {
          if (b.textContent === inputHora.value) {
            b.classList.add('bg-[#09A2AE]','text-white','border-[#09A2AE]');
          }
        });
      }
    }
  });
</script>

</body>
</html>
