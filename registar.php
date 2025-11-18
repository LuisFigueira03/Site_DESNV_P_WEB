<?php
session_start();

$user = 'web1';
$pass = 'web1';

try {
    $dbh = new PDO('mysql:host=localhost;dbname=web2;charset=utf8mb4', $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na ligação à base de dados.");
}

// Buscar acordos para o <select>
$acordos = [];
try {
    $stmtA = $dbh->query("SELECT id, nome FROM acordo WHERE ativo = 1 ORDER BY nome");
    $acordos = $stmtA->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
}

$erro   = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome      = trim($_POST['nome'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $telemovel = trim($_POST['telemovel'] ?? '');
    $num_utente_saude = trim($_POST['num_utente_saude'] ?? '');
    $nif       = trim($_POST['nif'] ?? '');
    $data_nascimento = $_POST['data_nascimento'] ?? null;
    $acordo_id = $_POST['acordo_id'] ?? null;

    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($nome === '' || $email === '' || $password === '' || $password2 === '') {
        $erro = 'Os campos nome, email e palavra-passe são obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Email inválido.';
    } elseif ($password !== $password2) {
        $erro = 'As palavras-passe não coincidem.';
    } else {
        // Verificar se já existe utente com o mesmo email
        $stmt = $dbh->prepare("SELECT COUNT(*) FROM utente WHERE email = :email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $jaExiste = (int) $stmt->fetchColumn();

        if ($jaExiste > 0) {
            $erro = 'Já existe um utente registado com esse email.';
        } else {
            // Insert
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO utente
                    (nome, email, telemovel, num_utente_saude, nif, data_nascimento, password_hash, email_verificado, acordo_id)
                    VALUES
                    (:nome, :email, :telemovel, :num_utente_saude, :nif, :data_nascimento, :password_hash, 0, :acordo_id)";

            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':telemovel', $telemovel !== '' ? $telemovel : null);
            $stmt->bindValue(':num_utente_saude', $num_utente_saude !== '' ? $num_utente_saude : null);
            $stmt->bindValue(':nif', $nif !== '' ? $nif : null);
            $stmt->bindValue(':data_nascimento', $data_nascimento !== '' ? $data_nascimento : null);
            $stmt->bindValue(':password_hash', $hash);
            $stmt->bindValue(':acordo_id', $acordo_id !== '' ? $acordo_id : null, PDO::PARAM_INT);

            try {
                $stmt->execute();
                $sucesso = 'Registo efetuado com sucesso. Já pode iniciar sessão.';

            } catch (PDOException $e) {
                $erro = 'Ocorreu um erro ao guardar o registo. Verifique se os dados (email, NIF, nº utente) não estão já a ser usados.';
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

<main class="min-h-screen flex items-center justify-center px-4">
  <div class="max-w-2xl w-full bg-white rounded-xl shadow-lg p-8 mt-10">

    <h1 class="text-2xl font-semibold mb-6 text-center texto-cor">
      Registo de utente
    </h1>

    <?php if ($erro): ?>
      <div class="mb-4 bg-red-100 text-red-700 px-4 py-3 rounded">
        <?= htmlspecialchars($erro) ?>
      </div>
    <?php endif; ?>

    <?php if ($sucesso): ?>
      <div class="mb-4 bg-green-100 text-green-700 px-4 py-3 rounded">
        <?= htmlspecialchars($sucesso) ?>
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
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-[#09A2AE] focus:border-[#09A2AE] px-3 py-2"
        >
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Confirmar palavra-passe *
        </label>
        <input
          type="password"
          name="password2"
          required
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-[#09A2AE] focus:border-[#09A2AE] px-3 py-2"
        >
      </div>

      <div class="md:col-span-2 mt-2">
        <button
          type="submit"
          class="w-full cor text-white py-2 rounded-md font-medium hover:opacity-90 transition"
        >
          Criar conta
        </button>
      </div>

      <div class="md:col-span-2 text-center text-sm text-gray-600 mt-2">
        Já tem conta?
        <a href="login.php" class="texto-cor font-medium hover:underline">
          Iniciar sessão
        </a>
      </div>

    </form>
  </div>
</main>

</body>
</html>
