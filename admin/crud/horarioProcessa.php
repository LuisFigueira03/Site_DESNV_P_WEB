<?php
session_start();
require __DIR__ . '/../includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../medicos.php?erro=metodoinvalido');
    exit;
}

$medicoId = (int)($_POST['medico_id'] ?? 0);
$horario  = $_POST['horario'] ?? [];

if ($medicoId <= 0 || !is_array($horario)) {
    header('Location: ../medicoGestao.php?id=' . $medicoId . '&erro=dadosinvalidos');
    exit;
}

// Aceitar apenas estes códigos
$permitidos = ['M', 'T', 'N', 'F'];

$map = [
    'seg' => $horario['seg'] ?? null,
    'ter' => $horario['ter'] ?? null,
    'qua' => $horario['qua'] ?? null,
    'qui' => $horario['qui'] ?? null,
    'sex' => $horario['sex'] ?? null,
    'sab' => $horario['sáb'] ?? ($horario['sab'] ?? null), // caso uses "sab"
    'dom' => $horario['dom'] ?? null,
];

foreach ($map as $dia => $turno) {
    if (!$turno || !in_array($turno, $permitidos, true)) {
        header('Location: ../medicoGestao.php?id=' . $medicoId . '&erro=turnoinvalido');
        exit;
    }
}

try {
    // Se existir, faz UPDATE; se não existir, faz INSERT
    $stmt = $dbh->prepare("SELECT medico_id FROM horario_medico WHERE medico_id = :id");
    $stmt->bindValue(':id', $medicoId, PDO::PARAM_INT);
    $stmt->execute();
    $existe = (bool)$stmt->fetchColumn();

    if ($existe) {
        $stmt = $dbh->prepare("
            UPDATE horario_medico
            SET seg = :seg, ter = :ter, qua = :qua, qui = :qui, sex = :sex, sab = :sab, dom = :dom
            WHERE medico_id = :medico_id
        ");
    } else {
        $stmt = $dbh->prepare("
            INSERT INTO horario_medico (medico_id, seg, ter, qua, qui, sex, sab, dom)
            VALUES (:medico_id, :seg, :ter, :qua, :qui, :sex, :sab, :dom)
        ");
    }

    $stmt->bindValue(':medico_id', $medicoId, PDO::PARAM_INT);
    $stmt->bindValue(':seg', $map['seg']);
    $stmt->bindValue(':ter', $map['ter']);
    $stmt->bindValue(':qua', $map['qua']);
    $stmt->bindValue(':qui', $map['qui']);
    $stmt->bindValue(':sex', $map['sex']);
    $stmt->bindValue(':sab', $map['sab']);
    $stmt->bindValue(':dom', $map['dom']);
    $stmt->execute();

} catch (PDOException $e) {
    header('Location: ../medicoGestao.php?id=' . $medicoId . '&erro=guardarhorario');
    exit;
}

header('Location: ../medicoGestao.php?id=' . $medicoId . '&ok=horarioguardado');
exit;
