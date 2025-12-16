<?php
session_start();
require 'includes/connection.php';

// Apenas utente autenticado
if (!isset($_SESSION['tipo_utilizador']) || $_SESSION['tipo_utilizador'] !== 'utente') {
    header('Location: login.php');
    exit;
}

$utente_id = (int)($_SESSION['utente_id'] ?? 0);
if ($utente_id <= 0) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: utente.php');
    exit;
}

$estrelas   = isset($_POST['estrelas']) ? (int)$_POST['estrelas'] : -1;
$comentario = trim($_POST['comentario'] ?? '');

if ($estrelas < 0 || $estrelas > 5) {
    $_SESSION['feedback_erro'] = 'Classificação inválida.';
    header('Location: utente.php#dar-feedback');
    exit;
}

// Buscar nome do utente
$sqlU = "SELECT nome FROM utente WHERE id = :id LIMIT 1";
$stmtU = $dbh->prepare($sqlU);
$stmtU->bindValue(':id', $utente_id, PDO::PARAM_INT);
$stmtU->execute();
$utente = $stmtU->fetch(PDO::FETCH_ASSOC);

if (!$utente) {
    $_SESSION['feedback_erro'] = 'Utente não encontrado.';
    header('Location: utente.php#dar-feedback');
    exit;
}

$sql = "INSERT INTO feedback (utente_id, nome, estrelas, comentario)
        VALUES (:utente_id, :nome, :estrelas, :comentario)";

$stmt = $dbh->prepare($sql);
$stmt->bindValue(':utente_id', $utente_id, PDO::PARAM_INT);
$stmt->bindValue(':nome', $utente['nome'], PDO::PARAM_STR);
$stmt->bindValue(':estrelas', $estrelas, PDO::PARAM_INT);

if ($comentario === '') {
    $stmt->bindValue(':comentario', null, PDO::PARAM_NULL);
} else {
    $stmt->bindValue(':comentario', $comentario, PDO::PARAM_STR);
}

if ($stmt->execute()) {
    $_SESSION['feedback_sucesso'] = 'Obrigado! O seu feedback foi registado com sucesso.';
} else {
    $_SESSION['feedback_erro'] = 'Erro ao guardar o feedback.';
}

header('Location: utente.php#dar-feedback');
exit;
