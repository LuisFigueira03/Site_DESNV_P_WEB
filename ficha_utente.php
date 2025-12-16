<?php
session_start();

// Apenas médicos podem aceder
if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'medico') {
    header('Location: login.php');
    exit;
}

$medico_id   = $_SESSION['medico_id']   ?? null;
$medico_nome = $_SESSION['medico_nome'] ?? 'Médico';

if (!$medico_id) {
    header('Location: login.php');
    exit;
}

require 'includes/connection.php';

$utente_id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$consulta_id = isset($_GET['consulta']) ? (int)$_GET['consulta'] : null;

if ($utente_id <= 0) {
    die('Utente inválido.');
}

$erros   = [];
$sucesso = '';

// Função para gerar código de 24 dígitos
function gerarCodigo24()
{
    $codigo = '';
    for ($i = 0; $i < 24; $i++) {
        $codigo .= random_int(0, 9);
    }
    return $codigo;
}

// Guardar dados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $acao = $_POST['acao'] ?? '';

    // 1. Guardar ficha do utente (dados clínicos)
    if ($acao === 'guardar_ficha') {

        // Médico NÃO pode alterar sexo nem morada (ignorar sempre)
        $peso_kg        = str_replace(',', '.', $_POST['peso_kg'] ?? '');
        $altura_cm      = (int)($_POST['altura_cm'] ?? 0);
        $tipo_sanguineo = $_POST['tipo_sanguineo'] ?? '';
        $alergias       = trim($_POST['alergias'] ?? '');
        $obs_clinicas   = trim($_POST['observacoes'] ?? '');

        // Calcular IMC se possível
        $imc = null;
        if ($peso_kg > 0 && $altura_cm > 0) {
            $altura_m = $altura_cm / 100;
            $imc = $peso_kg / ($altura_m * $altura_m);
        }

        try {
            $sql = "UPDATE utente
                    SET peso_kg = :peso_kg,
                        altura_cm = :altura_cm,
                        imc = :imc,
                        tipo_sanguineo = :tipo_sanguineo,
                        alergias = :alergias,
                        observacoes = :observacoes
                    WHERE id = :id";

            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':peso_kg', $peso_kg !== '' ? $peso_kg : null);
            $stmt->bindValue(':altura_cm', $altura_cm > 0 ? $altura_cm : null, PDO::PARAM_INT);
            $stmt->bindValue(':imc', $imc !== null ? round($imc, 1) : null);
            $stmt->bindValue(':tipo_sanguineo', $tipo_sanguineo ?: null);
            $stmt->bindValue(':alergias', $alergias ?: null);
            $stmt->bindValue(':observacoes', $obs_clinicas ?: null);
            $stmt->bindValue(':id', $utente_id, PDO::PARAM_INT);
            $stmt->execute();

            $sucesso = 'Ficha do utente atualizada com sucesso.';
        } catch (PDOException $e) {
            $erros[] = 'Erro ao atualizar a ficha do utente.';
        }
    }

    // 2. Guardar observações da consulta (AGORA VEM EM HTML DO QUILL)
    if ($acao === 'guardar_observacoes' && $consulta_id) {
        $obs_consulta = trim($_POST['observacoes_consulta'] ?? '');

        try {
            $sql = "UPDATE consulta
                    SET observacoes = :obs
                    WHERE id = :id AND medico_id = :medico_id";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':obs', $obs_consulta ?: null);
            $stmt->bindValue(':id', $consulta_id, PDO::PARAM_INT);
            $stmt->bindValue(':medico_id', $medico_id, PDO::PARAM_INT);
            $stmt->execute();

            $sucesso = 'Observações da consulta guardadas.';
        } catch (PDOException $e) {
            $erros[] = 'Erro ao guardar observações da consulta.';
        }
    }

    // 3. Nova receita
    if ($acao === 'nova_receita' && $consulta_id) {
        $medicamento = trim($_POST['medicamento'] ?? '');
        $unidades    = trim($_POST['unidades'] ?? '');
        $posologia   = trim($_POST['posologia'] ?? '');

        if ($medicamento === '' || $unidades === '' || $posologia === '') {
            $erros[] = 'Preencha todos os campos da receita.';
        } else {
            $codigo = gerarCodigo24();
            $texto  = "Medicamento: {$medicamento}\nUnidades: {$unidades}\nPosologia: {$posologia}";

            try {
                $sql = "INSERT INTO receita (consulta_id, medico_id, utente_id, texto, codigo)
                        VALUES (:consulta_id, :medico_id, :utente_id, :texto, :codigo)";
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(':consulta_id', $consulta_id, PDO::PARAM_INT);
                $stmt->bindValue(':medico_id', $medico_id, PDO::PARAM_INT);
                $stmt->bindValue(':utente_id', $utente_id, PDO::PARAM_INT);
                $stmt->bindValue(':texto', $texto);
                $stmt->bindValue(':codigo', $codigo);
                $stmt->execute();

                $sucesso = 'Receita criada com sucesso. Código: ' . $codigo;
            } catch (PDOException $e) {
                $erros[] = 'Erro ao criar a receita.';
            }
        }
    }

    // 4. Novo exame
    if ($acao === 'novo_exame' && $consulta_id) {
        $tipo_exame = trim($_POST['tipo_exame'] ?? '');
        $obs_exame  = trim($_POST['observacoes_exame'] ?? '');

        if ($tipo_exame === '') {
            $erros[] = 'Indique o tipo de exame.';
        } else {
            $codigo = gerarCodigo24();

            try {
                $sql = "INSERT INTO exame (consulta_id, tipo_exame, observacoes, estado, codigo)
                        VALUES (:consulta_id, :tipo_exame, :observacoes, 'pedido', :codigo)";
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(':consulta_id', $consulta_id, PDO::PARAM_INT);
                $stmt->bindValue(':tipo_exame', $tipo_exame);
                $stmt->bindValue(':observacoes', $obs_exame ?: null);
                $stmt->bindValue(':codigo', $codigo);
                $stmt->execute();

                $sucesso = 'Exame pedido com sucesso. Código: ' . $codigo;
            } catch (PDOException $e) {
                $erros[] = 'Erro ao pedir o exame.';
            }
        }
    }

    // 5. Terminar consulta
    if ($acao === 'terminar_consulta' && $consulta_id) {
        try {
            $sql = "UPDATE consulta
                    SET estado = 'realizada'
                    WHERE id = :id AND medico_id = :medico_id";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':id', $consulta_id, PDO::PARAM_INT);
            $stmt->bindValue(':medico_id', $medico_id, PDO::PARAM_INT);
            $stmt->execute();

            $sucesso = 'Consulta terminada com sucesso.';
        } catch (PDOException $e) {
            $erros[] = 'Erro ao terminar a consulta.';
        }
    }
}


// BUSCAR DADOS DO UTENTE
$sql = "SELECT u.*,
               a.nome AS nome_acordo
        FROM utente u
        LEFT JOIN acordo a ON u.acordo_id = a.id
        WHERE u.id = :id";
$stmt = $dbh->prepare($sql);
$stmt->bindValue(':id', $utente_id, PDO::PARAM_INT);
$stmt->execute();
$utente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$utente) {
    die('Utente não encontrado.');
}

// Calcular idade
$idade = null;
if (!empty($utente['data_nascimento'])) {
    $dn = new DateTime($utente['data_nascimento']);
    $hoje = new DateTime('today');
    $idade = $dn->diff($hoje)->y;
}

// Buscar consulta atual (se houver id de consulta)
$consulta_atual = null;
if ($consulta_id) {
    $sql = "SELECT * FROM consulta
            WHERE id = :id AND utente_id = :utente_id AND medico_id = :medico_id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':id', $consulta_id, PDO::PARAM_INT);
    $stmt->bindValue(':utente_id', $utente_id, PDO::PARAM_INT);
    $stmt->bindValue(':medico_id', $medico_id, PDO::PARAM_INT);
    $stmt->execute();
    $consulta_atual = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Consultas passadas
$sql = "SELECT c.*, m.nome AS nome_medico
        FROM consulta c
        JOIN medico m ON c.medico_id = m.id
        WHERE c.utente_id = :utente_id
        ORDER BY c.data_consulta DESC, c.hora_inicio DESC
        LIMIT 20";
$stmt = $dbh->prepare($sql);
$stmt->bindValue(':utente_id', $utente_id, PDO::PARAM_INT);
$stmt->execute();
$consultas_passadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Receitas passadas
$sql = "SELECT r.*, m.nome AS nome_medico
        FROM receita r
        JOIN medico m ON r.medico_id = m.id
        WHERE r.utente_id = :utente_id
        ORDER BY r.data_emissao DESC
        LIMIT 20";
$stmt = $dbh->prepare($sql);
$stmt->bindValue(':utente_id', $utente_id, PDO::PARAM_INT);
$stmt->execute();
$receitas_passadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Exames passados
$sql = "SELECT e.*, c.data_consulta, c.hora_inicio
        FROM exame e
        JOIN consulta c ON e.consulta_id = c.id
        WHERE c.utente_id = :utente_id
        ORDER BY c.data_consulta DESC, c.hora_inicio DESC
        LIMIT 20";
$stmt = $dbh->prepare($sql);
$stmt->bindValue(':utente_id', $utente_id, PDO::PARAM_INT);
$stmt->execute();
$exames_passados = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <title>Ficha do Utente</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="imagens/logo-sem-fundo.png" />

  <link rel="stylesheet" href="src/css/output.css"/>

  <!-- Quill -->
  <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
  <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
  <style>
    #editor-observacoes .ql-container { height: 160px; }
    #editor-observacoes .ql-editor { height: 160px; overflow-y: auto; }
  </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased font-sans">

<?php include 'includes/navbar.php'; ?>

<!-- MODAL para terminar consulta -->
<div id="modalTerminar" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center">
  <div class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6 text-center">
    <h3 class="text-lg font-semibold mb-3">Tem a certeza que pretende terminar a consulta?</h3>
    <p class="text-sm text-gray-600 mb-5">
      Esta ação marcará a consulta como <strong>realizada</strong>.
    </p>

    <div class="flex justify-center gap-3">
      <button type="button"
              onclick="fecharModalTerminar()"
              class="px-4 py-2 rounded-md bg-gray-200 text-gray-800 text-sm font-medium hover:bg-gray-300">
        Cancelar
      </button>

      <form id="formTerminar" method="post">
        <input type="hidden" name="acao" value="terminar_consulta">
        <button type="submit"
                class="px-4 py-2 rounded-md bg-green-600 text-white text-sm font-medium hover:bg-green-700">
          Confirmar
        </button>
      </form>
    </div>
  </div>
</div>

<main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  <!-- Mensagens -->
  <?php if (!empty($erros)): ?>
    <div class="mb-4 bg-red-100 text-red-700 px-4 py-3 rounded text-sm">
      <?= implode('<br>', $erros) ?>
    </div>
  <?php endif; ?>

  <?php if ($sucesso): ?>
    <div class="mb-4 bg-green-100 text-green-700 px-4 py-3 rounded text-sm">
      <?= htmlspecialchars($sucesso) ?>
    </div>
  <?php endif; ?>

  <!-- Cabeçalho -->
  <header class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div class="flex items-center gap-4">
      <div class="h-20 w-20 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
        <?php if (!empty($utente['foto'])): ?>
          <img src="<?= htmlspecialchars($utente['foto']) ?>" alt="Foto do utente" class="h-full w-full object-cover">
        <?php else: ?>
          <span class="text-2xl font-bold text-[#09A2AE]">
            <?= strtoupper(substr($utente['nome'], 0, 1)) ?>
          </span>
        <?php endif; ?>
      </div>
      <div>
        <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-[#09A2AE]">
          <?= htmlspecialchars($utente['nome']) ?>
        </h1>
        <p class="text-sm text-gray-600">
          Nº Utente: <?= htmlspecialchars($utente['num_utente_saude'] ?? '—') ?>
          <?php if ($idade !== null): ?>
            • <?= $idade ?> anos
          <?php endif; ?>
        </p>
        <p class="text-sm text-gray-600">
          Plano de saúde: <?= htmlspecialchars($utente['nome_acordo'] ?? '—') ?>
        </p>
      </div>
    </div>

    <?php if ($consulta_atual): ?>
      <div class="bg-white rounded-lg shadow px-4 py-3 text-sm border border-gray-200">
        <p class="font-semibold text-[#09A2AE]">Consulta atual</p>
        <p><?= date('d/m/Y', strtotime($consulta_atual['data_consulta'])) ?> às <?= substr($consulta_atual['hora_inicio'], 0, 5) ?></p>
        <p class="text-gray-600 text-xs mt-1">Estado: <?= htmlspecialchars($consulta_atual['estado']) ?></p>
      </div>
    <?php endif; ?>
  </header>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">

    <!-- Coluna esquerda: ficha do utente -->
    <section class="lg:col-span-1 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
      <h2 class="text-lg font-semibold mb-4 text-[#09A2AE]">Dados do utente</h2>

      <form method="post" class="space-y-3">
        <input type="hidden" name="acao" value="guardar_ficha">

        <div>
          <label class="block text-xs font-semibold text-gray-500 mb-1">Nome</label>
          <input type="text" disabled
                 value="<?= htmlspecialchars($utente['nome']) ?>"
                 class="w-full bg-gray-100 rounded-md border-gray-200 px-3 py-2 text-sm">
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Data de nascimento</label>
            <input type="text" disabled
                   value="<?= $utente['data_nascimento'] ? date('d/m/Y', strtotime($utente['data_nascimento'])) : '—' ?>"
                   class="w-full bg-gray-100 rounded-md border-gray-200 px-3 py-2 text-sm">
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Sexo</label>
            <select disabled class="w-full rounded-md border-gray-200 px-2 py-2 text-sm bg-gray-100">
              <option><?= htmlspecialchars($utente['sexo'] ?? '—') ?></option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-500 mb-1">Morada</label>
          <textarea disabled rows="2"
                    class="w-full rounded-md border-gray-200 px-3 py-2 text-sm resize-none bg-gray-100"><?= htmlspecialchars($utente['morada'] ?? '') ?></textarea>
        </div>

        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Peso (kg)</label>
            <input type="number" step="0.1" name="peso_kg"
                   value="<?= htmlspecialchars($utente['peso_kg'] ?? '') ?>"
                   class="w-full rounded-md border-gray-300 px-3 py-2 text-sm">
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Altura (cm)</label>
            <input type="number" name="altura_cm"
                   value="<?= htmlspecialchars($utente['altura_cm'] ?? '') ?>"
                   class="w-full rounded-md border-gray-300 px-3 py-2 text-sm">
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">IMC</label>
            <input type="text" disabled
                   value="<?= $utente['imc'] ? number_format($utente['imc'], 1, ',', ' ') : '—' ?>"
                   class="w-full bg-gray-100 rounded-md border-gray-200 px-3 py-2 text-sm">
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Tipo sanguíneo</label>
            <input type="text" name="tipo_sanguineo" maxlength="3"
                   placeholder="ex: A+, O-"
                   value="<?= htmlspecialchars($utente['tipo_sanguineo'] ?? '') ?>"
                   class="w-full rounded-md border-gray-300 px-3 py-2 text-sm">
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Plano de saúde</label>
            <input type="text" disabled
                   value="<?= htmlspecialchars($utente['nome_acordo'] ?? '—') ?>"
                   class="w-full bg-gray-100 rounded-md border-gray-200 px-3 py-2 text-sm">
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-500 mb-1">Alergias</label>
          <textarea name="alergias" rows="2"
                    class="w-full rounded-md border-gray-300 px-3 py-2 text-sm resize-none"><?= htmlspecialchars($utente['alergias'] ?? '') ?></textarea>
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-500 mb-1">Observações clínicas</label>
          <textarea name="observacoes" rows="3"
                    class="w-full rounded-md border-gray-300 px-3 py-2 text-sm resize-none"><?= htmlspecialchars($utente['observacoes'] ?? '') ?></textarea>
        </div>

        <button type="submit"
                class="mt-3 w-full bg-[#09A2AE] text-white py-2 rounded-md font-medium hover:opacity-90 transition text-sm">
          Guardar ficha do utente
        </button>
      </form>
    </section>

    <!-- Coluna central+dta: consulta, receitas, exames -->
    <section class="lg:col-span-2 space-y-6">

      <?php if ($consulta_atual): ?>
      <!-- Bloco consulta atual -->
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h2 class="text-lg font-semibold mb-4 text-[#09A2AE]">Consulta atual</h2>

        <form id="form-observacoes-consulta" method="post" class="space-y-3">
          <input type="hidden" name="acao" value="guardar_observacoes">

          <label class="block text-xs font-semibold text-gray-500 mb-1">
            Observações da consulta
          </label>

          <!-- Editor Quill -->
          <div id="editor-observacoes" class="bg-white border border-gray-300 rounded-md"></div>

          <!-- Valor real enviado no POST -->
          <textarea id="observacoes_consulta" name="observacoes_consulta" class="hidden"></textarea>

          <button type="submit"
                  class="bg-[#09A2AE] text-white px-4 py-2 rounded-md text-sm font-medium hover:opacity-90 transition">
            Guardar observações
          </button>
        </form>

        <div class="mt-4 flex flex-wrap gap-3">
          <button type="button"
                  onclick="abrirModalTerminar()"
                  class="bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-700 transition">
            Terminar consulta
          </button>
        </div>
      </div>

      <!-- Nova receita -->
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h2 class="text-lg font-semibold mb-4 text-[#09A2AE]">Nova receita</h2>
        <form method="post" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
          <input type="hidden" name="acao" value="nova_receita">

          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Medicamento</label>
            <input type="text" name="medicamento"
                   class="w-full rounded-md border-gray-300 px-3 py-2 text-sm">
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Unidades</label>
            <input type="text" name="unidades"
                   class="w-full rounded-md border-gray-300 px-3 py-2 text-sm">
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Posologia</label>
            <input type="text" name="posologia" placeholder="ex: 1 comp. 8/8h"
                   class="w-full rounded-md border-gray-300 px-3 py-2 text-sm">
          </div>

          <div class="md:col-span-3">
            <button type="submit"
                    class="bg-[#09A2AE] text-white px-4 py-2 rounded-md text-sm font-medium hover:opacity-90 transition">
              Gravar receita
            </button>
          </div>
        </form>
      </div>

      <!-- Novo exame -->
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h2 class="text-lg font-semibold mb-4 text-[#09A2AE]">Novo exame</h2>
        <form method="post" class="space-y-3">
          <input type="hidden" name="acao" value="novo_exame">

          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Tipo de exame</label>
            <input type="text" name="tipo_exame" placeholder="ex: Raio-X, Análises, TAC…"
                   class="w-full rounded-md border-gray-300 px-3 py-2 text-sm">
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Observações</label>
            <textarea name="observacoes_exame" rows="2"
                      class="w-full rounded-md border-gray-300 px-3 py-2 text-sm resize-none"></textarea>
          </div>

          <button type="submit"
                  class="bg-[#09A2AE] text-white px-4 py-2 rounded-md text-sm font-medium hover:opacity-90 transition">
            Pedir exame
          </button>
        </form>
      </div>
      <?php endif; ?>

    </section>
  </div>

  <!-- Histórico com botões -->
  <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
    <h2 class="text-lg font-semibold text-[#09A2AE] mb-4">Histórico clínico</h2>

    <!-- Botões -->
    <div class="flex flex-wrap gap-3 mb-4">
      <button type="button"
              data-target="consultas-historico"
              class="px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
        Ver histórico de consultas
      </button>

      <button type="button"
              data-target="receitas-historico"
              class="px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
        Ver receitas
      </button>

      <button type="button"
              data-target="exames-historico"
              class="px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
        Ver exames
      </button>
    </div>

    <!-- Consultas -->
    <div id="consultas-historico" class="hidden border-t border-gray-100 pt-4 mt-2">
      <h3 class="text-sm font-semibold text-gray-700 mb-2">Consultas</h3>
      <?php if (empty($consultas_passadas)): ?>
        <p class="text-sm text-gray-500">Sem consultas registadas.</p>
      <?php else: ?>
        <ul class="divide-y divide-gray-100 text-sm">
          <?php foreach ($consultas_passadas as $c): ?>
            <li class="py-2 flex justify-between items-center">
              <div>
                <p class="font-medium">
                  <?= date('d/m/Y', strtotime($c['data_consulta'])) ?>
                  às <?= substr($c['hora_inicio'], 0, 5) ?>
                  — <?= htmlspecialchars($c['tipo']) ?>
                </p>
                <p class="text-gray-500 text-xs">
                  Dr(a). <?= htmlspecialchars($c['nome_medico']) ?> • Estado: <?= htmlspecialchars($c['estado']) ?>
                </p>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <!-- Receitas -->
    <div id="receitas-historico" class="hidden border-t border-gray-100 pt-4 mt-4">
      <h3 class="text-sm font-semibold text-gray-700 mb-2">Receitas</h3>
      <?php if (empty($receitas_passadas)): ?>
        <p class="text-sm text-gray-500">Sem receitas registadas.</p>
      <?php else: ?>
        <ul class="divide-y divide-gray-100 text-sm">
          <?php foreach ($receitas_passadas as $r): ?>
            <li class="py-2">
              <p class="font-medium">
                <?= date('d/m/Y H:i', strtotime($r['data_emissao'])) ?>
                — Código: <?= htmlspecialchars($r['codigo'] ?? '—') ?>
              </p>
              <p class="text-gray-500 whitespace-pre-line text-xs">
                <?= htmlspecialchars($r['texto']) ?>
              </p>
              <p class="text-gray-400 text-xs mt-1">
                Emitida por Dr(a). <?= htmlspecialchars($r['nome_medico']) ?>
              </p>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <!-- Exames -->
    <div id="exames-historico" class="hidden border-t border-gray-100 pt-4 mt-4">
      <h3 class="text-sm font-semibold text-gray-700 mb-2">Exames</h3>
      <?php if (empty($exames_passados)): ?>
        <p class="text-sm text-gray-500">Sem exames registados.</p>
      <?php else: ?>
        <ul class="divide-y divide-gray-100 text-sm">
          <?php foreach ($exames_passados as $e): ?>
            <li class="py-2">
              <p class="font-medium">
                <?= date('d/m/Y', strtotime($e['data_consulta'])) ?>
                — <?= htmlspecialchars($e['tipo_exame']) ?>
                (<?= htmlspecialchars($e['estado']) ?>)
              </p>
              <p class="text-gray-500 text-xs">
                Código: <?= htmlspecialchars($e['codigo'] ?? '—') ?>
              </p>
              <?php if (!empty($e['observacoes'])): ?>
                <p class="text-gray-500 text-xs mt-1">
                  <?= htmlspecialchars($e['observacoes']) ?>
                </p>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>

  <div class="mt-6 flex justify-between">
    <a href="medico.php" class="text-sm text-gray-600 hover:underline">
      ← Voltar à agenda
    </a>
  </div>

</main>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const botoes = document.querySelectorAll('button[data-target]');
    const seccoes = document.querySelectorAll('#consultas-historico, #receitas-historico, #exames-historico');

    botoes.forEach(btn => {
        btn.addEventListener('click', () => {
            const alvo = document.getElementById(btn.dataset.target);
            if (!alvo) return;

            seccoes.forEach(sec => sec !== alvo && sec.classList.add('hidden'));
            alvo.classList.toggle('hidden');
        });
    });
});

function abrirModalTerminar() {
  const modal = document.getElementById('modalTerminar');
  if (modal) modal.classList.remove('hidden');
}

function fecharModalTerminar() {
  const modal = document.getElementById('modalTerminar');
  if (modal) modal.classList.add('hidden');
}
</script>

<script>
  // Inicializar Quill para observações da consulta
  const quillObs = new Quill('#editor-observacoes', {
    theme: 'snow',
    modules: {
      toolbar: [
        ['bold', 'italic', 'underline'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['clean']
      ]
    }
  });

  // Carregar conteúdo vindo da BD
  <?php if (!empty($consulta_atual)): ?>
    quillObs.root.innerHTML = <?= json_encode($consulta_atual['observacoes'] ?? '') ?>;
  <?php endif; ?>

  // Antes de submeter, copiar HTML para o campo escondido
  const formObs = document.getElementById('form-observacoes-consulta');
  if (formObs) {
    formObs.addEventListener('submit', function () {
      document.getElementById('observacoes_consulta').value = quillObs.root.innerHTML;
    });
  }
</script>

</body>
</html>
