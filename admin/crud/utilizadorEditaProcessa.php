<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location:../utilizadores.php?erro=metodoinvalido');
    exit;
}

require '../includes/connection.php';

$utente_id          = (int)($_POST['id'] ?? 0);
$nome               = trim($_POST['nome'] ?? '');
$email              = trim($_POST['email'] ?? '');
$telemovel           = trim($_POST['telemovel'] ?? '');
$data_nascimento     = trim($_POST['data_nascimento'] ?? '');
$num_utente_saude    = trim($_POST['num_utente_saude'] ?? '');
$nif                = trim($_POST['nif'] ?? '');
$acordo_id           = ($_POST['acordo_id'] ?? '') === '' ? null : (int)$_POST['acordo_id'];

$password            = $_POST['password'] ?? '';
$password_confirm    = $_POST['password_confirm'] ?? '';

if ($utente_id <= 0 || $nome === '' || $email === '') {
    header('Location:../utilizadorDetalhe.php?id=' . $utente_id . '&erro=faltamdados');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location:../utilizadorDetalhe.php?id=' . $utente_id . '&erro=emailinvalido');
    exit;
}

// Buscar utente atual (existe? + foto atual)
$stmt = $dbh->prepare("SELECT id, foto FROM utente WHERE id = :id");
$stmt->execute([':id' => $utente_id]);
$utenteAtual = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$utenteAtual) {
    header('Location:../utilizadores.php?erro=utilizadoraonaoencontrado');
    exit;
}

$novoCaminhoFoto = $utenteAtual['foto'] ?? null;

// ---------- UPLOAD DE FOTO (opcional) ----------
if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {

    if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        header('Location:../utilizadorDetalhe.php?id=' . $utente_id . '&erro=envioimagem');
        exit;
    }

    $permitidas = ['jpg','jpeg','png','webp'];
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $permitidas, true)) {
        header('Location:../utilizadorDetalhe.php?id=' . $utente_id . '&erro=imagemformatoinvalido');
        exit;
    }

    // Limite de tamanho: 3MB
    if ($_FILES['foto']['size'] > 3 * 1024 * 1024) {
        header('Location:../utilizadorDetalhe.php?id=' . $utente_id . '&erro=imagemgrande');
        exit;
    }

    $dir = dirname(__DIR__, 2) . '/imagens/utentes';    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $nomeFicheiro = 'utente_' . $utente_id . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $destinoAbs = $dir . '/' . $nomeFicheiro;

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $destinoAbs)) {
        header('Location:../utilizadorDetalhe.php?id=' . $utente_id . '&erro=guardaimagem');
        exit;
    }

    // Caminho relativo guardado na BD
    $novoCaminhoFoto = 'imagens/utentes/' . $nomeFicheiro;
}

// ---------- PASSWORD (opcional) ----------
$password_hash = null;
$vaiMudarPassword = false;

if ($password !== '' || $password_confirm !== '') {
    if ($password === '' || $password_confirm === '') {
        header('Location:../utilizadorDetalhe.php?id=' . $utente_id . '&erro=passwordincompleta');
        exit;
    }
    if ($password !== $password_confirm) {
        header('Location:../utilizadorDetalhe.php?id=' . $utente_id . '&erro=passwordnaocoincide');
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $vaiMudarPassword = true;
}

try {
    // Evitar duplicado de email
    $stmt = $dbh->prepare("SELECT COUNT(*) FROM utente WHERE email = :email AND id <> :id");
    $stmt->execute([':email' => $email, ':id' => $utente_id]);
    if ((int)$stmt->fetchColumn() > 0) {
        header('Location:../utilizadorDetalhe.php?id=' . $utente_id . '&erro=emailjaexiste');
        exit;
    }

    // Evitar duplicado de Nº utente (se preenchido)
    if ($num_utente_saude !== '') {
        $stmt = $dbh->prepare("SELECT COUNT(*) FROM utente WHERE num_utente_saude = :n AND id <> :id");
        $stmt->execute([':n' => $num_utente_saude, ':id' => $utente_id]);
        if ((int)$stmt->fetchColumn() > 0) {
            header('Location:../utilizadorDetalhe.php?id=' . $utente_id . '&erro=numutentejaexiste');
            exit;
        }
    }

    // Evitar duplicado de NIF (se preenchido)
    if ($nif !== '') {
        $stmt = $dbh->prepare("SELECT COUNT(*) FROM utente WHERE nif = :nif AND id <> :id");
        $stmt->execute([':nif' => $nif, ':id' => $utente_id]);
        if ((int)$stmt->fetchColumn() > 0) {
            header('Location:../utilizadorDetalhe.php?id=' . $utente_id . '&erro=nifjaexiste');
            exit;
        }
    }

    $sql = "
        UPDATE utente
        SET nome = :nome,
            email = :email,
            telemovel = :telemovel,
            data_nascimento = :data_nascimento,
            num_utente_saude = :num_utente_saude,
            nif = :nif,
            acordo_id = :acordo_id,
            foto = :foto
            " . ($vaiMudarPassword ? ", password_hash = :ph" : "") . "
        WHERE id = :id
    ";

    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':nome', $nome);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':telemovel', $telemovel !== '' ? $telemovel : null);
    $stmt->bindValue(':data_nascimento', $data_nascimento !== '' ? $data_nascimento : null);
    $stmt->bindValue(':num_utente_saude', $num_utente_saude !== '' ? $num_utente_saude : null);
    $stmt->bindValue(':nif', $nif !== '' ? $nif : null);

    if ($acordo_id !== null) {
        $stmt->bindValue(':acordo_id', $acordo_id, PDO::PARAM_INT);
    } else {
        $stmt->bindValue(':acordo_id', null, PDO::PARAM_NULL);
    }

    $stmt->bindValue(':foto', $novoCaminhoFoto !== '' ? $novoCaminhoFoto : null);

    if ($vaiMudarPassword) {
        $stmt->bindValue(':ph', $password_hash);
    }

    $stmt->bindValue(':id', $utente_id, PDO::PARAM_INT);
    $stmt->execute();

    header('Location:../utilizadorDetalhe.php?id=' . $utente_id . '&ok=guardado');
    exit;

} catch (PDOException $e) {
    header('Location:../utilizadorDetalhe.php?id=' . $utente_id . '&erro=guardardb');
    exit;
}
