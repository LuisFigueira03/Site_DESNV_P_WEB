<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location:../medicoNovo.php?erro=metodoinvalido');
    exit;
}

require '../includes/connection.php';

// 1) Receber e limpar
$nome            = trim($_POST['nome'] ?? '');
$num_cedula      = trim($_POST['num_cedula'] ?? '');
$email           = trim($_POST['email'] ?? '');
$password        = $_POST['password'] ?? '';
$telemovel       = trim($_POST['telemovel'] ?? '');
$especialidadeId = (int)($_POST['especialidade_id'] ?? 0);
$ativo           = isset($_POST['ativo']) ? (int)$_POST['ativo'] : 1;

// 2) Validação
if ($nome === '' || $num_cedula === '' || $password === '' || $especialidadeId <= 0) {
    header('Location:../medicoNovo.php?erro=faltamdados');
    exit;
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location:../medicoNovo.php?erro=emailinvalido');
    exit;
}

if ($ativo !== 0 && $ativo !== 1) {
    $ativo = 1;
}

// 3) Verificar se especialidade existe
$stmt = $dbh->prepare("SELECT COUNT(*) FROM especialidade WHERE id = :id");
$stmt->execute([':id' => $especialidadeId]);
if ((int)$stmt->fetchColumn() === 0) {
    header('Location:../medicoNovo.php?erro=especialidadeinvalida');
    exit;
}

// 4) Verificar cédula duplicada
$stmt = $dbh->prepare("SELECT COUNT(*) FROM medico WHERE num_cedula = :cedula");
$stmt->execute([':cedula' => $num_cedula]);
if ((int)$stmt->fetchColumn() > 0) {
    header('Location:../medicoNovo.php?erro=cedulajaexiste');
    exit;
}

// 5) Hash da password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $sql = "
        INSERT INTO medico (nome, num_cedula, email, password_hash, telemovel, especialidade_id, ativo)
        VALUES (:nome, :cedula, :email, :ph, :telemovel, :esp_id, :ativo)
    ";

    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':nome', $nome);
    $stmt->bindValue(':cedula', $num_cedula);
    $stmt->bindValue(':email', $email !== '' ? $email : null);
    $stmt->bindValue(':ph', $password_hash);
    $stmt->bindValue(':telemovel', $telemovel !== '' ? $telemovel : null);
    $stmt->bindValue(':esp_id', $especialidadeId, PDO::PARAM_INT);
    $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);

    $stmt->execute();

    header('Location:../medicos.php?ok=criado');
    exit;

} catch (PDOException $e) {
    // Em produção não mostres $e->getMessage() ao utilizador
    header('Location:../medicoNovo.php?erro=criacaodb');
    exit;
}
