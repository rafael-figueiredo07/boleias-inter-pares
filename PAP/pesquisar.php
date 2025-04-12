<?php
require 'db.php';

session_start();

try {
    $conn = conect_db();
    $utilizador_id = isset($_SESSION['utilizador_id']) ? $_SESSION['utilizador_id'] : null;
    $sql = "SELECT nome FROM utilizador WHERE utilizador_id = :utilizador_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':utilizador_id', $utilizador_id, PDO::PARAM_INT);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado) {
        $nome = $resultado['nome'];
    } else {
        $nome = "Utilizador Desconhecido";
    }
} catch (PDOException $e) {
    exit('Erro ao buscar dados do utilizador: ' . $e->getMessage());
}

$ponto_partida = isset($_POST['partida']) ? trim($_POST['partida']) : '';
$destino = isset($_POST['destino']) ? trim($_POST['destino']) : '';
$data = isset($_POST['data']) ? $_POST['data'] : '';

// Consulta modificada para buscar tanto no ponto_partida quanto no ponto_intermedio
$sql = "SELECT * FROM boleia 
        WHERE (ponto_partida LIKE :ponto_partida OR ponto_intermedio LIKE :ponto_partida)
        AND destino LIKE :destino
        AND data = :data";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':ponto_partida', "%$ponto_partida%", PDO::PARAM_STR);
$stmt->bindValue(':destino', "%$destino%", PDO::PARAM_STR);
$stmt->bindValue(':data', $data, PDO::PARAM_STR);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisar | Boleias Inter Pares</title>
    <link rel="icon" href="public/imagens/LOGO.png">
    <link rel="stylesheet" href="public/css/bootstrap.min.css">
    <link rel="stylesheet" href="public/css/padding_top.css">
</head>
<body>
    <div class="container mt-4">
        <?php include 'public/includes/header.php'; ?>

        <h4 class="card-title text-center mb-4">Pesquisar Boleia</h4>

        <form action="pesquisar.php" method="POST">
            <div class="container">
                <div class="row justify-content-start">
                    <div class="col-3">
                        <label for="partida" class="form-label">Ponto de Partida</label>
                        <input type="text" class="form-control" id="partida" name="partida" placeholder="Ponto de Partida" required>
                    </div>
                    <div class="col-auto d-flex align-items-end">
                        <button type="button" class="btn btn-success" onclick="invertInputs()">
                            <i class="bi bi-arrow-left-right"></i>
                        </button>
                    </div>
                    <div class="col-3">
                        <label for="destino" class="form-label">Destino</label>
                        <input type="text" class="form-control" id="destino" name="destino" placeholder="Destino" required>
                    </div>
                    <div class="col-2">
                        <label for="data" class="form-label">Data</label>
                        <input type="date" class="form-control" id="data" name="data">
                    </div>
                    <div class="col-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-search"></i> Pesquisar
                        </button>
                    </div>
                    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                        <?php if (count($resultados) > 0): ?>
                            <div class="container mt-3">
                                <style>
                                    .card-hover:hover {
                                        background-color: #f0f0f0;
                                        transition: background-color 0.3s ease;
                                    }
                                </style>
                                <?php foreach ($resultados as $boleia): ?>
                                    <a href="detalhes.php?id=<?= $boleia['boleia_id'] ?>" class="text-decoration-none">
                                        <div class="card mb-3 p-3 shadow-sm card-hover">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong class="fs-5">
                                                        <?= htmlspecialchars($boleia['ponto_partida']) ?>
                                                        <?php if (!empty($boleia['ponto_intermedio'])): ?>
                                                            <i class="bi bi-arrow-right"></i> <?= htmlspecialchars($boleia['ponto_intermedio']) ?>
                                                        <?php endif; ?>
                                                        <i class="bi bi-arrow-right"></i> <?= htmlspecialchars($boleia['destino']) ?>
                                                    </strong>
                                                    <p class="text-success m-0">
                                                        <?= $boleia['total_pessoas'] ?> <?= ($boleia['total_pessoas'] == 1) ? 'Pessoa' : 'Pessoas' ?>
                                                    </p>
                                                </div>
                                                <div class="text-end">
                                                    <span class="text-muted fs-6">
                                                        <?= date("H\hi", strtotime($boleia['horario'])) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning mt-4 text-center">
                                Nenhuma boleia encontrada.
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <script src="public/js/pesquisar.js"></script>

    <?php include 'public/includes/footer.php'; ?>
</body>
</html>