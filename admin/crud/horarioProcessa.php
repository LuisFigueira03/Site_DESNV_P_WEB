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

$permitidos = ['M', 'T', 'N', 'F'];

foreach ($horario as $dia => $turno) {
    if (!$turno || !in_array($turno, $permitidos, true)) {
        header('Location: ../medicoGestao.php?id=' . $medicoId . '&erro=turnoinvalido');
        exit;
    }

    // verificar se já existe este dia
    $stmt = $dbh->prepare("SELECT id FROM horario_medico WHERE medico_id = :medico_id AND dia_semana = :dia");
    $stmt->bindValue(':medico_id', $medicoId, PDO::PARAM_INT);
    $stmt->bindValue(':dia', $dia, PDO::PARAM_STR);
    $stmt->execute();
    $existe = $stmt->fetchColumn();

    if ($existe) {
        $stmt = $dbh->prepare("UPDATE horario_medico SET turno = :turno WHERE id = :id");
        $stmt->bindValue(':turno', $turno);
        $stmt->bindValue(':id', $existe, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $stmt = $dbh->prepare("INSERT INTO horario_medico (medico_id, dia_semana, turno) VALUES (:medico_id, :dia, :turno)");
        $stmt->bindValue(':medico_id', $medicoId, PDO::PARAM_INT);
        $stmt->bindValue(':dia', $dia);
        $stmt->bindValue(':turno', $turno);
        $stmt->execute();
    }
}

header('Location: ../medicoGestao.php?id=' . $medicoId . '&ok=horarioguardado');
exit;
