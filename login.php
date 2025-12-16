<?php
session_start();

require 'includes/connection.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $erro = 'Preencha o email e a palavra-passe.';
    } else {

        $userData = null;

        // 1) Procurar primeiro como utente
        $sqlUtente = "SELECT id, nome, password_hash, 'utente' AS tipo
                      FROM utente
                      WHERE email = :email
                      LIMIT 1";
        $stmt = $dbh->prepare($sqlUtente);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2) Se não encontrou, procurar como médico ativo
        if (!$userData) {
            $sqlMedico = "SELECT id, nome, password_hash, 'medico' AS tipo
                          FROM medico
                          WHERE email = :email AND ativo = 1
                          LIMIT 1";
            $stmt = $dbh->prepare($sqlMedico);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // 3) Se não encontrou, procurar como administrador
        if (!$userData) {
            $sqlAdmin = "SELECT id, nome, password_hash, 'admin' AS tipo
                         FROM administracao
                         WHERE email = :email
                         LIMIT 1";
            $stmt = $dbh->prepare($sqlAdmin);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$userData) {
            $erro = 'Credenciais inválidas.';
        } else {

            $hash = (string)($userData['password_hash'] ?? '');
            $login_ok = false;

            // Verificar se parece um hash bcrypt ($2y$...)
            $isBcrypt = (strlen($hash) > 0 && substr($hash, 0, 4) === '$2y$');

            if ($isBcrypt) {
                if (password_verify($password, $hash)) {
                    $login_ok = true;
                }
            } else {
                // fallback para passwords guardadas "em claro" (ex: "teste")
                if ($password === $hash) {
                    $login_ok = true;
                }
            }

            if ($login_ok) {

                $tipo = $userData['tipo'];
                $_SESSION['tipo_utilizador'] = $tipo;

                if ($tipo === 'utente') {
                    $_SESSION['utente_id']   = (int)$userData['id'];
                    $_SESSION['utente_nome'] = $userData['nome'];
                    header('Location: index.php');
                    exit;
                }

                if ($tipo === 'medico') {
                    $_SESSION['medico_id']   = (int)$userData['id'];
                    $_SESSION['medico_nome'] = $userData['nome'];
                    header('Location: medico.php');
                    exit;
                }

                if ($tipo === 'admin') {
                    $_SESSION['admin_id']   = (int)$userData['id'];
                    $_SESSION['admin_nome'] = $userData['nome'];
                    header('Location: admin/dashboard.php');
                    exit;
                }

                // Se por algum motivo vier outro tipo:
                $erro = 'Tipo de utilizador inválido.';
            } else {
                $erro = 'Credenciais inválidas.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <link rel="stylesheet" href="src/css/output.css" />
  <link rel="icon" type="image/png" href="imagens/logo-sem-fundo.png" />
</head>

<body class="bg-gray-200 text-gray-900">

<?php include 'includes/navbar.php'; ?>

<main class="min-h-screen flex items-center justify-center px-4">
  <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8 mt-10">

    <h1 class="text-2xl font-semibold mb-6 text-center text-[#09A2AE]">
      Iniciar sessão
    </h1>

    <?php if ($erro): ?>
      <div class="mb-4 bg-red-100 text-red-700 px-4 py-3 rounded">
        <?= htmlspecialchars($erro) ?>
      </div>
    <?php endif; ?>

    <form method="post" class="space-y-4">

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input
          type="email"
          name="email"
          required
          value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-[#09A2AE] focus:border-[#09A2AE] px-3 py-2"
        >
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Palavra-passe</label>
        <input
          type="password"
          name="password"
          required
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-[#09A2AE] focus:border-[#09A2AE] px-3 py-2"
        >
      </div>

      <button
        type="submit"
        class="w-full mt-4 bg-[#09A2AE] text-white py-2 rounded-md font-medium hover:opacity-90 transition"
      >
        Entrar
      </button>

      <p class="text-sm text-center text-gray-600 mt-4">
        Ainda não tem conta?
        <a href="registar.php" class="text-[#09A2AE] font-medium hover:underline">
          Registar-me
        </a>
      </p>

    </form>
  </div>
</main>

<?php include 'includes/footer.php'; ?>
</body>
</html>
