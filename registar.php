<?php
session_start();
require 'includes/connection.php';

// Buscar acordos para o <select>
$acordos = [];
try {
    $stmtA = $dbh->query("SELECT id, nome FROM acordo WHERE ativo = 1 ORDER BY nome");
    $acordos = $stmtA->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
}

// Função para validar NIF português
function validarNif($nif)
{
    if (!preg_match('/^\d{9}$/', $nif)) {
        return false;
    }
    if ($nif[0] == '0') {
        return false;
    }
    $soma = 0;
    for ($i = 0; $i < 8; $i++) {
        $soma += $nif[$i] * (9 - $i);
    }
    $resto = $soma % 11;
    $digito = ($resto < 2) ? 0 : 11 - $resto;
    return (int)$nif[8] === $digito;
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome      = trim($_POST['nome'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $telemovel = trim($_POST['telemovel'] ?? '');
    $num_utente_saude = trim($_POST['num_utente_saude'] ?? '');
    $nif       = trim($_POST['nif'] ?? '');
    $data_nascimento = $_POST['data_nascimento'] ?? '';
    $acordo_id = $_POST['acordo_id'] ?? '';

    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    $erros = [];

    // Todos são obrigatórios
    if ($nome === '' || $email === '' || $telemovel === '' || $num_utente_saude === '' ||
        $nif === '' || $data_nascimento === '' || $acordo_id === '' ||
        $password === '' || $password2 === '') {
        $erros[] = 'Preencha todos os campos obrigatórios.';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Email inválido.';
    }

    if ($telemovel !== '' && !preg_match('/^\d{9}$/', $telemovel)) {
        $erros[] = 'O telemóvel deve ter 9 dígitos.';
    }

    if ($num_utente_saude !== '' && !preg_match('/^\d{9}$/', $num_utente_saude)) {
        $erros[] = 'O nº de utente de saúde deve ter 9 dígitos.';
    }

    if ($nif !== '' && !validarNif($nif)) {
        $erros[] = 'NIF inválido.';
    }

    if ($password !== '') {
        if (strlen($password) < 8) {
            $erros[] = 'A palavra-passe deve ter pelo menos 8 caracteres.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $erros[] = 'A palavra-passe deve conter pelo menos um número.';
        }
        if (!preg_match('/[A-Za-z]/', $password)) {
            $erros[] = 'A palavra-passe deve conter pelo menos uma letra.';
        }
    }

    if ($password !== $password2) {
        $erros[] = 'As palavras-passe não coincidem.';
    }

    // Data: não pode ser futura, idade mínima 16
    $dataNascObj = DateTime::createFromFormat('Y-m-d', $data_nascimento);
    $hoje = new DateTime('today');
    if (!$dataNascObj) {
        $erros[] = 'Data de nascimento inválida.';
    } else {
        if ($dataNascObj > $hoje) {
            $erros[] = 'A data de nascimento não pode ser no futuro.';
        }
        $idade = $dataNascObj->diff($hoje)->y;
        if ($idade < 16) {
            $erros[] = 'Tem de ter pelo menos 16 anos para se registar.';
        }
    }

    // Verificar se já existe utente ou médico com o mesmo email
    if (empty($erros) && $email !== '') {
        $stmt = $dbh->prepare("
            SELECT COUNT(*) FROM (
                SELECT email FROM utente WHERE email = :email
                UNION ALL
                SELECT email FROM medico WHERE email = :email
            ) t
        ");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        if ((int)$stmt->fetchColumn() > 0) {
            $erros[] = 'Já existe um utilizador registado com esse email.';
        }
    }

    // Verificar duplicados na tabela utente (campos tipicamente UNIQUE)
    if (empty($erros)) {

        $stmt = $dbh->prepare("
            SELECT
                SUM(email = :email) AS dup_email,
                SUM(num_utente_saude = :num_utente_saude) AS dup_num_utente,
                SUM(nif = :nif) AS dup_nif,
                SUM(telemovel = :telemovel) AS dup_telemovel
            FROM utente
            WHERE email = :email
               OR num_utente_saude = :num_utente_saude
               OR nif = :nif
               OR telemovel = :telemovel
        ");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':num_utente_saude', $num_utente_saude, PDO::PARAM_STR);
        $stmt->bindValue(':nif', $nif, PDO::PARAM_STR);
        $stmt->bindValue(':telemovel', $telemovel, PDO::PARAM_STR);
        $stmt->execute();
        $dup = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($dup['dup_num_utente'])) {
            $erros[] = 'Já existe um utente registado com esse nº de utente de saúde.';
        }
        if (!empty($dup['dup_nif'])) {
            $erros[] = 'Já existe um utente registado com esse NIF.';
        }
        if (!empty($dup['dup_telemovel'])) {
            $erros[] = 'Já existe um utente registado com esse telemóvel.';
        }
        if (!empty($dup['dup_email'])) {
            $erros[] = 'Já existe um utilizador registado com esse email.';
        }
    }

    if (!empty($erros)) {
        $erro = implode('<br>', $erros);
    } else {

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $dbh->prepare("
            INSERT INTO utente
            (nome, email, telemovel, num_utente_saude, nif, data_nascimento, password_hash, email_verificado, acordo_id)
            VALUES
            (:nome, :email, :telemovel, :num_utente_saude, :nif, :data_nascimento, :password_hash, 0, :acordo_id)
        ");
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':telemovel', $telemovel);
        $stmt->bindValue(':num_utente_saude', $num_utente_saude);
        $stmt->bindValue(':nif', $nif);
        $stmt->bindValue(':data_nascimento', $data_nascimento);
        $stmt->bindValue(':password_hash', $hash);
        $stmt->bindValue(':acordo_id', $acordo_id, PDO::PARAM_INT);

        try {
            $stmt->execute();

            // ✅ Sessão iniciada automaticamente (como no login.php)
            session_regenerate_id(true);
            $novoId = (int)$dbh->lastInsertId();

            $_SESSION['tipo_utilizador'] = 'utente';
            $_SESSION['utente_id']       = $novoId;
            $_SESSION['utente_nome']     = $nome;

            // ✅ Mensagem para aparecer no index (flash)
            $_SESSION['flash_sucesso'] = 'Registo efetuado com sucesso. Sessão iniciada.';

            // ✅ Ir para o index
            header('Location: index.php');
            exit;

        } catch (PDOException $e) {

            if ($e->getCode() === '23000') {
                $erro = 'Não foi possível concluir o registo: já existe um utilizador com um dos dados introduzidos (ex.: nº utente, NIF, telemóvel ou email).';
            } else {
                $erro = 'Ocorreu um erro ao criar a conta. Tente novamente.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registar utente - Hospital da Luz</title>
  <link rel="stylesheet" href="src/css/output.css" />
  <link rel="icon" type="image/png" href="imagens/logo-sem-fundo.png" />
</head>

<body class="bg-gray-200 text-gray-900">

<?php include 'includes/navbar.php'; ?>

<main class="min-h-screen flex items-center justify-center px-4">
  <div class="max-w-2xl w-full bg-white rounded-xl shadow-lg p-8 mt-10">

    <h1 class="text-2xl font-semibold mb-6 text-center text-[#09A2AE]">
      Registo de utente
    </h1>

    <?php if ($erro): ?>
      <div class="mb-4 bg-red-100 text-red-700 px-4 py-3 rounded text-sm">
        <?= $erro ?>
      </div>
    <?php endif; ?>

    <form method="post" class="grid grid-cols-1 md:grid-cols-2 gap-4">

      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Nome completo *
        </label>
        <input
          type="text"
          name="nome"
          required
          minlength="3"
          value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-[#09A2AE] focus:border-[#09A2AE] px-3 py-2"
        >
      </div>

      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Email *
        </label>
        <input
          type="email"
          name="email"
          required
          value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-[#09A2AE] focus:border-[#09A2AE] px-3 py-2"
        >
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Telemóvel
        </label>
        <input
          type="text"
          name="telemovel"
          pattern="\d{9}"
          title="Insira 9 dígitos."
          value="<?= htmlspecialchars($_POST['telemovel'] ?? '') ?>"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-[#09A2AE] focus:border-[#09A2AE] px-3 py-2"
        >
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Nº Utente de Saúde
        </label>
        <input
          type="text"
          name="num_utente_saude"
          pattern="\d{9}"
          title="Insira 9 dígitos."
          value="<?= htmlspecialchars($_POST['num_utente_saude'] ?? '') ?>"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-[#09A2AE] focus:border-[#09A2AE] px-3 py-2"
        >
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          NIF
        </label>
        <input
          type="text"
          name="nif"
          pattern="\d{9}"
          title="Insira 9 dígitos."
          value="<?= htmlspecialchars($_POST['nif'] ?? '') ?>"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-[#09A2AE] focus:border-[#09A2AE] px-3 py-2"
        >
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Data de nascimento
        </label>
        <input
          type="date"
          name="data_nascimento"
          value="<?= htmlspecialchars($_POST['data_nascimento'] ?? '') ?>"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-[#09A2AE] focus:border-[#09A2AE] px-3 py-2"
        >
      </div>

      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Acordo / Seguro
        </label>
        <select
          name="acordo_id"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-[#09A2AE] focus:border-[#09A2AE] px-3 py-2"
        >
          <option value="">-- Selecionar --</option>
          <?php foreach ($acordos as $ac): ?>
            <option value="<?= $ac['id'] ?>"
              <?= (($_POST['acordo_id'] ?? '') == $ac['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($ac['nome']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Palavra-passe *
        </label>
        <input
          type="password"
          name="password"
          required
          minlength="8"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-[#09A2AE] focus:border-[#09A2AE] px-3 py-2"
        >
        <p class="text-xs text-gray-500 mt-1">
          Mínimo 8 caracteres, incluindo pelo menos um número.
        </p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Confirmar palavra-passe *
        </label>
        <input
          type="password"
          name="password2"
          required
          minlength="8"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-[#09A2AE] focus:border-[#09A2AE] px-3 py-2"
        >
      </div>

      <div class="md:col-span-2 mt-2">
        <button
          type="submit"
          class="w-full bg-[#09A2AE] text-white py-2 rounded-md font-medium hover:opacity-90 transition"
        >
          Criar conta
        </button>
      </div>

      <div class="md:col-span-2 text-center text-sm text-gray-600 mt-2">
        Já tem conta?
        <a href="login.php" class="text-[#09A2AE] font-medium hover:underline">
          Iniciar sessão
        </a>
      </div>

    </form>
  </div>
</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
