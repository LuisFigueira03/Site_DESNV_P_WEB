<?php
session_start();
require '../includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medico_id = (int)($_POST['medico_id'] ?? 0);
    $ativo = isset($_POST['ativo']) && $_POST['ativo'] == 1 ? 1 : 0;

    if ($medico_id > 0) {
        $stmt = $dbh->prepare("UPDATE medico SET ativo = :ativo WHERE id = :id");
        $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
        $stmt->bindValue(':id', $medico_id, PDO::PARAM_INT);
        $stmt->execute();
    }
    header('Location: ../medicoGestao.php?id=' . $medico_id);
    exit;
}
