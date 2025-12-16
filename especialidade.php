<?php
require 'includes/connection.php';

if (!isset($_GET['esp'])) {
    header('Location:index.php');
    exit;
}

$espId = (int) $_GET['esp'];

$sql = 'SELECT * FROM especialidade WHERE id = :id AND visivel = 1';
$stmt = $dbh->prepare($sql);
$stmt->bindValue(':id', $espId, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() !== 1) {
    header('Location:index.php');
    exit;
}

$esp        = $stmt->fetchObject();
$nome       = $esp->nome;
$imagem     = $esp->imagem;
$informacao = $esp->informacao;   
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($nome) ?> - Clínica Saúde+</title>
    <link rel="stylesheet" href="src/css/output.css" />
    <link rel="icon" type="image/png" href="imagens/logo-sem-fundo.png" />
</head>
<body class="bg-gray-100">

<?php include 'includes/navbar.php'; ?>

    <!-- -->
    <header class="bg-[#09A2AE] text-white py-6 shadow-md">
        <h1 class="text-center text-3xl font-semibold">
            <?= htmlspecialchars($nome) ?>
        </h1>
    </header>

    <!-- Conteúdo principal  -->
    <main class="max-w-6xl mx-auto mt-10 bg-white p-10 rounded-xl shadow-md flex flex-col lg:flex-row gap-12">

        <!-- Imagem  -->
        <div class="lg:w-1/2 w-full">
            <img src="imagens/<?= htmlspecialchars($imagem) ?>" 
                 alt="Exame de <?= htmlspecialchars($nome) ?>" 
                 class="rounded-xl shadow-md w-full h-full object-cover max-h-[600px]">
        </div>

        <!-- Texto  -->
        <div class="lg:w-1/2 w-full">

            <!-- html na base de dados -->
            <?= $informacao ?>

            <!-- Botão voltar -->
            <div class="flex justify-end">
                <a href="index.php" class="text-[#09A2AE] font-semibold hover:underline text-lg">
                    ← Voltar
                </a>
            </div>
        </div>
    </main>

</body>

<?php include 'includes/footer.php'; ?>

</html>
