<?php
session_start();
require __DIR__ . '/../includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && ($_GET['action'] ?? '') !== 'delete') {
    header('Location: ../medicos.php?erro=metodoinvalido');
    exit;
}

// Apagar férias
if (($_GET['action'] ?? '') === 'delete') {
    $id       = (int)($_GET['id'] ?? 0);
    $medicoId = (int)($_GET['medico_id'] ?? 0);

    if ($id <= 0 || $medicoId <= 0) {
        header('Location: ../medicos.php?erro=dadosinvalidos');
        exit;
    }

    try {
        $stmt = $dbh->prepare("DELETE FROM ausencia_medico WHERE id = :id AND medico_id = :medico_id AND tipo = 'ferias'");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':medico_id', $medicoId, PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        header('Location: ../medicoGestao.php?id=' . $medicoId . '&erro=apagarfalhou');
        exit;
    }

    header('Location: ../medicoGestao.php?id=' . $medicoId . '&ok=feriasremovidas');
    exit;
}

// Criar férias
$medicoId    = (int)($_POST['medico_id'] ?? 0);
$dataInicio  = trim($_POST['data_inicio'] ?? '');
$dataFim     = trim($_POST['data_fim'] ?? '');

if ($medicoId <= 0 || $dataInicio === '' || $dataFim === '') {
    header('Location: ../medicoGestao.php?id=' . $medicoId . '&erro=faltamdados');
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataInicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataFim)) {
    header('Location: ../medicoGestao.php?id=' . $medicoId . '&erro=dataformato');
    exit;
}

if ($dataFim < $dataInicio) {
    header('Location: ../medicoGestao.php?id=' . $medicoId . '&erro=datafim');
    exit;
}

try {
    // Opcional: impedir sobreposições
    $stmt = $dbh->prepare("
        SELECT COUNT(*) 
        FROM ausencia_medico
        WHERE medico_id = :medico_id
          AND tipo = 'ferias'
          AND NOT (data_fim < :inicio OR data_inicio > :fim)
    ");
    $stmt->bindValue(':medico_id', $medicoId, PDO::PARAM_INT);
    $stmt->bindValue(':inicio', $dataInicio);
    $stmt->bindValue(':fim', $dataFim);
    $stmt->execute();

    if ((int)$stmt->fetchColumn() > 0) {
        header('Location: ../medicoGestao.php?id=' . $medicoId . '&erro=feriassobrepoem');
        exit;
    }

    $stmt = $dbh->prepare("
        INSERT INTO ausencia_medico (medico_id, tipo, data_inicio, data_fim)
        VALUES (:medico_id, 'ferias', :inicio, :fim)
    ");
    $stmt->bindValue(':medico_id', $medicoId, PDO::PARAM_INT);
    $stmt->bindValue(':inicio', $dataInicio);
    $stmt->bindValue(':fim', $dataFim);
    $stmt->execute();

} catch (PDOException $e) {
    header('Location: ../medicoGestao.php?id=' . $medicoId . '&erro=guardarferias');
    exit;
}

header('Location: ../medicoGestao.php?id=' . $medicoId . '&ok=feriasguardadas');
exit;
