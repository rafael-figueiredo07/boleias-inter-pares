<?php

include '../db.php';

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

try {
    $conn = conect_db(); // Conecta à base de dados usando a função que você já tem
    $utilizador_id = $_SESSION['utilizador_id'];
    $sql = "SELECT nome FROM utilizador WHERE utilizador_id = :utilizador_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':utilizador_id', $utilizador_id, PDO::PARAM_INT);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) { // Verifica se a consulta retornou resultados
        $nome = $resultado['nome']; // Armazena o nome do utilizador
    } else {
        // Se não houver resultados, defina um valor padrão ou exiba uma mensagem de erro
        $nome = "Utilizador Desconhecido";
        // Ou redirecione para uma página de erro
        // header("Location: erro.php");
        // exit();
    }
} catch (PDOException $e) {
    exit('Erro ao buscar dados do utilizador: ' . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Boleia | Boleias Inter Pares</title>
    <link rel="icon" href="../public/imagens/LOGO.png">
    <link rel="stylesheet" href="../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../public/css/padding_top.css">
</head>

<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <?php include '../public/includes/header.php'; ?>
        </div>

        <h4 class="card-title text-center mb-4">Criar Boleia</h4>

        <form action="processar_boleia.php" method="POST">
            <div class="mb-3">
                <label for="partida" class="form-label">Ponto de Partida</label>
                <input type="text" class="form-control" id="partida" name="partida" placeholder="Ponto de Partida" required>
            </div>
            <div class="mb-3">
                <label for="ponto_intermedio" class="form-label">Ponto Intermédio</label>
                <input type="text" class="form-control" id="ponto_intermedio" name="ponto_intermedio" placeholder="Ponto Intermédio">
            </div>
            <div class="mb-3">
                <label for="destino" class="form-label">Destino</label>
                <input type="text" class="form-control" id="destino" name="destino" placeholder="Destino" required>
            </div>
            <div class="row justify-content-end">
                <div class="col-4 mb-3">
                    <label for="pessoas" class="form-label">Total de Pessoas</label>
                    <input type="number" class="form-control" id="pessoas" name="pessoas" placeholder="Total de Pessoas" required>
                </div>
                <div class="col-4 mb-3">
                    <label for="data" class="form-label">Data</label>
                    <input type="date" class="form-control" id="data" name="data" required>
                </div>
                <div class="col-4 mb-3">
                    <label for="horario" class="form-label">Horário</label>
                    <input type="time" class="form-control" id="horario" name="horario" step="300" required>
                </div>
            </div>
            <button type="submit" class="btn btn-success w-50">Criar Boleia</button>
        </form>
    </div>
    <!-- <script src="../public/js/bootstrap.bundle.min.js"></script> -->
    <script src="../public/js/criarboleia.js"></script>
</body>

<?php include '../public/includes/footer.php'; ?>