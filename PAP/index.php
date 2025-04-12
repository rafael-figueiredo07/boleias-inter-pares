<?php

include 'db.php';

session_start();

try {
  $conn = conect_db();
  $utilizador_id = isset($_SESSION['utilizador_id']) ? $_SESSION['utilizador_id'] : null;
  $perfil = isset($_SESSION['perfil']) ? $_SESSION['perfil'] : null;

  $nome = "Utilizador Desconhecido";

  if ($utilizador_id) {
    if ($perfil == 1) { // Se for admin, buscar na tabela `admin`
        $sql = "SELECT nome FROM admin WHERE admin_id = :utilizador_id";
    } else { // Se for utilizador, buscar na tabela `utilizador`
        $sql = "SELECT nome FROM utilizador WHERE utilizador_id = :utilizador_id";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':utilizador_id', $utilizador_id, PDO::PARAM_INT);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        $nome = $resultado['nome'];
    }
  }
} catch (PDOException $e) {
  exit('Erro ao buscar dados do utilizador: ' . $e->getMessage());
}

$ponto_partida = isset($_POST['partida']) ? trim($_POST['partida']) : '';
$destino = isset($_POST['destino']) ? trim($_POST['destino']) : '';

$sql = "SELECT * FROM boleia WHERE ponto_partida LIKE :ponto_partida AND destino LIKE :destino AND data = :data";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':ponto_partida', "%$ponto_partida%", PDO::PARAM_STR);
$stmt->bindValue(':destino', "%$destino%", PDO::PARAM_STR);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Boleias Inter Pares</title>
  <link rel="icon" href="public/imagens/LOGO.png">
  <link rel="stylesheet" href="/PAP/public/css/bootstrap.min.css">
  <link rel="stylesheet" href="public/css/padding_top.css">
  <script src="public/js/pesquisar.js"></script>

</head>

<body>
  <div class="container mt-4">
    <?php include 'public/includes/header.php'; ?>
  </div>

  <!-- Busca de boleias -->
  <section class="search-section py-5">
    <div class="container">
      <h2 class="text-center mb-4">Encontre a sua boleia</h2>
      <form class="row g-3 align-items-end">
        <div class="col-md-5">
          <input type="text" class="form-control" placeholder="De onde parte?" required>
        </div>
        <div class="col-auto">
          <button type="button" class="btn btn-success" onclick="invertInputs()">
            <i class="bi bi-arrow-left-right"></i>
          </button>
        </div>
        <div class="col-md-5">
          <input type="text" class="form-control" placeholder="Para onde vai?" required>
        </div>
        <div class="col-auto">
          <a href="/PAP/pesquisar.php" class="btn btn-success">
            <i class="bi bi-search"></i> Pesquisar
          </a>
        </div>
      </form>
    </div>
  </section>

  <!-- Secção de Destaques -->
  <section class="p-5 my-5 bg-success">
    <h2 class="text-center text-white mb-4">Viagens mais procuradas</h2>
    <div class="row">
      <div class="col-md-4">
        <div class="card shadow-sm">
          <a href="#" class="btn btn-light">
            <div class="card-body">
              <h5 class="card-title">Lisboa → Porto</h5>
              <!-- <a href="#" class="btn btn-success">Ver detalhes</a> -->
            </div>
          </a>
        </div>
      </div>
      <div class="col-md-4">
      <div class="card shadow-sm">
          <a href="#" class="btn btn-light">
            <div class="card-body">
              <h5 class="card-title">Coimbra → Faro</h5>
              <!-- <a href="#" class="btn btn-success">Ver detalhes</a> -->
            </div>
          </a>
        </div>
      </div>
      <div class="col-md-4">
      <div class="card shadow-sm">
          <a href="#" class="btn btn-light">
            <div class="card-body align-items-end">
              <h5 class="card-title">Braga → Lisboa</h5>
              <!-- <a href="#" class="btn btn-success">Ver detalhes</a> -->
            </div>
          </a>
        </div>
    </div>
  </section>

  <!-- Benefícios -->
  <section class="p-5">
    <div class="container text-center">
      <h2>Por que viajar connosco?</h2>
      <div class="row mt-4">
        <div class="col-md-4">
          <i class="bi bi-piggy-bank fs-1"></i>
          <h5 class="mt-3">Económico</h5>
          <p>Divida os custos da viagem e poupe dinheiro.</p>
        </div>
        <div class="col-md-4">
          <i class="bi bi-shield-lock fs-1"></i>
          <h5 class="mt-3">Seguro</h5>
          <p>Viaje com utilizadores verificados e bem avaliados.</p>
        </div>
        <div class="col-md-4">
          <i class="bi bi-pin-map fs-1"></i>
          <h5 class="mt-3">Fácil</h5>
          <p>Reserve a sua boleia em poucos cliques.</p>
        </div>
      </div>
    </div>
  </section>

  <?php include 'public/includes/footer.php'; ?>
