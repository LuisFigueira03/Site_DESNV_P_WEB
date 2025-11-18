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

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tipo     = $_POST['tipo'] ?? '';
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($tipo !== 'utente' && $tipo !== 'medico') {
        $erro = 'Tem de escolher se é utente ou médico.';
    } elseif ($email === '' || $password === '') {
        $erro = 'Preencha o email e a palavra-passe.';
    } else {

        if ($tipo === 'utente') {
            $sql = "SELECT * FROM utente WHERE email = :email LIMIT 1";
        } else {
            $sql = "SELECT * FROM medico WHERE email = :email AND ativo = 1 LIMIT 1";
        }

        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            $erro = 'Credenciais inválidas.';
        } else {

            $hash = $userData['password_hash'];
            $login_ok = false;

            if (strlen($hash) > 0 && str_starts_with($hash, '$2y$')) {
                if (password_verify($password, $hash)) {
                    $login_ok = true;
                }
            } else {
                if ($password === $hash) {
                    $login_ok = true;
                }
            }

            if ($login_ok) {
                $_SESSION['tipo_utilizador'] = $tipo;

                if ($tipo === 'utente') {
                    $_SESSION['utente_id'] = $userData['id'];
                    $_SESSION['utente_nome'] = $userData['nome'];

                    header('Location: utente.php');
                    exit;
                }

                if ($tipo === 'medico') {
                    $_SESSION['medico_id'] = $userData['id'];
                    $_SESSION['medico_nome'] = $userData['nome'];

                    header('Location: medico.php');
                    exit;
                }

            } else {
                $erro = 'Credenciais inválidas.';
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
  <title>Login</title>
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
  <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8 mt-10">

    <h1 class="text-2xl font-semibold mb-6 text-center texto-cor">
      Iniciar sessão
    </h1>

    <?php if ($erro): ?>
      <div class="mb-4 bg-red-100 text-red-700 px-4 py-3 rounded">
        <?= htmlspecialchars($erro) ?>
      </div>
    <?php endif; ?>

    <form method="post" class="space-y-4">

      <!-- Tipo -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Sou
        </label>
        <div class="flex gap-4">
          <label class="inline-flex items-center">
            <input type="radio" name="tipo" value="utente"
                   class="h-4 w-4 text-[#09A2AE] border-gray-300"
                   <?= (($_POST['tipo'] ?? '') === 'utente') ? 'checked' : '' ?>>
            <span class="ml-2 text-sm text-gray-700">Utente</span>
          </label>
          <label class="inline-flex items-center">
            <input type="radio" name="tipo" value="medico"
                   class="h-4 w-4 text-[#09A2AE] border-gray-300"
                   <?= (($_POST['tipo'] ?? '') === 'medico') ? 'checked' : '' ?>>
            <span class="ml-2 text-sm text-gray-700">Médico</span>
          </label>
        </div>
      </div>

      <!-- Email -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Email
        </label>
        <input
          type="email"
          name="email"
          required
          value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-[#09A2AE] focus:border-[#09A2AE] px-3 py-2"
        >
      </div>

      <!-- Password -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Palavra-passe
        </label>
        <input
          type="password"
          name="password"
          required
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-[#09A2AE] focus:border-[#09A2AE] px-3 py-2"
        >
      </div>

      <button
        type="submit"
        class="w-full mt-4 cor text-white py-2 rounded-md font-medium hover:opacity-90 transition"
      >
        Entrar
      </button>

      <p class="text-sm text-center text-gray-600 mt-4">
        Ainda não tem conta?
        <a href="registar.php" class="texto-cor font-medium hover:underline">
          Registar-me
        </a>
      </p>

    </form>
  </div>
</main>

</body>
</html>
