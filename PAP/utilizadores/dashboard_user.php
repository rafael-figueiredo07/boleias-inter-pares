<?php
include '../db.php';
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

$mensagem_sucesso = "";
$mensagem_erro = "";
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'boleias';

try {
    $conn = conect_db();
    $utilizador_id = $_SESSION['utilizador_id'];

    $sql = "SELECT nome FROM utilizador WHERE utilizador_id = :utilizador_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':utilizador_id', $utilizador_id, PDO::PARAM_INT);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $nome = $resultado ? $resultado['nome'] : "Utilizador Desconhecido";

    // Buscar as boleias do utilizador
    $sql_boleias = "SELECT b.boleia_id, b.ponto_partida, b.ponto_intermedio, b.destino, 
                       b.total_pessoas, b.data, b.horario
                FROM boleia b
                WHERE utilizador_id = :utilizador_id";
    $stmt_boleias = $conn->prepare($sql_boleias);
    $stmt_boleias->bindParam(':utilizador_id', $utilizador_id, PDO::PARAM_INT);
    $stmt_boleias->execute();
    $boleias = $stmt_boleias->fetchAll(PDO::FETCH_ASSOC);

    // Buscar as reservas do utilizador
    $sql_reservas = "SELECT b.boleia_id, b.ponto_partida, b.ponto_intermedio, b.destino, 
                        b.total_pessoas, b.data, b.horario, r.reserva_id
                 FROM reserva r
                 JOIN boleia b ON r.boleia_id = b.boleia_id
                 WHERE r.utilizador_id = :utilizador_id";
    $stmt_reservas = $conn->prepare($sql_reservas);
    $stmt_reservas->bindParam(':utilizador_id', $utilizador_id, PDO::PARAM_INT);
    $stmt_reservas->execute();
    $reservas = $stmt_reservas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erro ao buscar dados: ' . $e->getMessage());
}

// Processar atualização da boleia
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['atualizar_boleia'])) {
    try {
        // Capturar os dados do formulário
        $boleia_id = $_POST['id'];
        $ponto_partida = $_POST['partida'];
        $ponto_intermedio = $_POST['ponto_intermedio'];
        $destino = $_POST['destino'];
        $total_pessoas = $_POST['pessoas'];
        $data = $_POST['data'];
        $horario = $_POST['horario'];

        // Verificar se todos os dados foram recebidos
        if (empty($boleia_id) || empty($ponto_partida) || empty($destino) || empty($data) || empty($horario) || empty($total_pessoas)) {
            $mensagem_erro = "Erro: Dados incompletos!";
        } else {
            $sql_update = "UPDATE boleia 
                           SET ponto_partida = :ponto_partida, 
                               ponto_intermedio = :ponto_intermedio, 
                               destino = :destino, 
                               total_pessoas = :total_pessoas, 
                               data = :data, 
                               horario = :horario 
                           WHERE boleia_id = :boleia_id AND utilizador_id = :utilizador_id";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bindParam(':ponto_partida', $ponto_partida);
            $stmt_update->bindParam(':ponto_intermedio', $ponto_intermedio);
            $stmt_update->bindParam(':destino', $destino);
            $stmt_update->bindParam(':total_pessoas', $total_pessoas, PDO::PARAM_INT);
            $stmt_update->bindParam(':data', $data);
            $stmt_update->bindParam(':horario', $horario);
            $stmt_update->bindParam(':boleia_id', $boleia_id, PDO::PARAM_INT);
            $stmt_update->bindParam(':utilizador_id', $utilizador_id, PDO::PARAM_INT);

            if ($stmt_update->execute()) {
                $_SESSION['success_msg'] = "Boleia atualizada com sucesso!";
                header("location: dashboard_user.php");
                exit;
            } else {
                $mensagem_erro = "Erro ao atualizar a boleia!";
            }
        }
    } catch (PDOException $e) {
        $mensagem_erro = 'Erro ao atualizar: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Boleias Inter Pares</title>
    <link rel="icon" href="/PAP/public/imagens/LOGO.png">
    <link rel="stylesheet" href="/PAP/public/css/padding_top.css">
    <link rel="stylesheet" href="/PAP/public/css/bootstrap.min.css">
    <link rel="stylesheet" href="/PAP/public/css/bootstrap-icons-1.11.3/font/bootstrap-icons.css">
    <style>
        .card-hover:hover {
            background-color: #f8f9fa;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .dropdown-item:active,
        .dropdown-item:focus,
        .dropdown-item:hover {
            background-color: #198754 !important;
            color: white !important;
        }

        .nav-tabs {
            justify-content: center;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 1rem;
        }

        .nav-tabs .nav-link {
            color: #000;
            border: none;
            font-weight: normal;
        }

        .nav-tabs .nav-link.active {
            color: #198754 !important;
            font-weight: bold !important;
            background-color: transparent !important;
            border: none !important;
            border-bottom: 2px solid #198754 !important;
        }

        .nav-tabs .nav-link:hover {
            color: #198754;
            border-color: transparent;
        }

        .btn-outline-success:hover {
            background-color: #198754;
            border-color: #198754;
        }

        .tab-content {
            margin-top: 20px;
        }

        /* Estilo especial para o footer do card de reservas */
        .card-footer {
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            padding: 0.75rem 1.25rem;
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        .hover-effect:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .hover-effect {
            transition: all 0.3s ease;
        }

        .dropdown-item:active,
        .dropdown-item:focus,
        .dropdown-item:hover {
            background-color: #198754 !important;
            color: white !important;
        }

        .nav-tabs {
            justify-content: center;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 1rem;
        }

        .nav-tabs .nav-link {
            color: #000;
            border: none;
            font-weight: normal;
        }

        .nav-tabs .nav-link.active {
            color: #198754 !important;
            font-weight: bold !important;
            background-color: transparent !important;
            border: none !important;
            border-bottom: 2px solid #198754 !important;
        }

        .nav-tabs .nav-link:hover {
            color: #198754;
            border-color: transparent;
        }

        .btn-outline-success:hover {
            background-color: #198754;
            border-color: #198754;
        }

        .tab-content {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <?php include '../public/includes/header.php'; ?>
        </div>

        <h4 class="card-title text-center mb-4">Dashboard</h4>

        <!-- Mensagem de Sucesso -->
        <?php if (isset($_SESSION['success_msg'])) : ?>
            <div id="success-alert" class="alert alert-success text-center"><?= $_SESSION['success_msg']; ?></div>
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>

        <!-- Mensagem de erro -->
        <?php if (!empty($mensagem_erro)) : ?>
            <div id="error-alert" class="alert alert-danger text-center"><?= $mensagem_erro; ?></div>
        <?php endif; ?>

        <!-- Tabs Navigation - Centralizado -->
        <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $active_tab === 'boleias' ? 'active' : '' ?>" id="boleias-tab" data-bs-toggle="tab" data-bs-target="#boleias" type="button" role="tab" aria-controls="boleias" aria-selected="<?= $active_tab === 'boleias' ? 'true' : 'false' ?>">
                    Minhas Boleias
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $active_tab === 'reservas' ? 'active' : '' ?>" id="reservas-tab" data-bs-toggle="tab" data-bs-target="#reservas" type="button" role="tab" aria-controls="reservas" aria-selected="<?= $active_tab === 'reservas' ? 'true' : 'false' ?>">
                    Minhas Reservas
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="dashboardTabsContent">
            <!-- Minhas Boleias Tab -->
            <div class="tab-pane fade <?= $active_tab === 'boleias' ? 'show active' : '' ?>" id="boleias" role="tabpanel" aria-labelledby="boleias-tab">
                <div class="row">
                    <?php if (count($boleias) > 0) : ?>
                        <?php foreach ($boleias as $boleia) : ?>
                            <?php
                            $horario_display = date('H:i', strtotime($boleia['horario']));
                            $data_display = date('d/m/Y', strtotime($boleia['data']));
                            ?>
                            <div class="col-md-4">
                                <a href="../detalhes.php?id=<?= $boleia['boleia_id'] ?>" class="text-decoration-none">
                                    <div class="card mb-3 shadow-sm card-hover">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <?= htmlspecialchars($boleia['ponto_partida']) ?>
                                                <?= !empty($boleia['ponto_intermedio']) ? ' - ' . htmlspecialchars($boleia['ponto_intermedio']) : '' ?>
                                                - <?= htmlspecialchars($boleia['destino']) ?>
                                            </h5>
                                            <p class="mb-1 text-success"><?= $boleia['total_pessoas'] ?> <?= ($boleia['total_pessoas'] == 1) ? 'Pessoa' : 'Pessoas' ?></p>
                                            <p class="mb-1 text-muted">Data: <?= $data_display ?></p>
                                            <p class="mb-2 text-muted">Horário: <?= $horario_display ?></p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="text-center mt-3">
                            <img src="../public/imagens/sem_boleias.png" alt="Sem boleias" width="200" class="mb-3">
                            <h4>Ainda não tem boleias criadas</h4>
                            <p class="text-muted">Crie a sua própria boleia e partilhe o trajeto com outros utilizadores.</p>
                            <a href="criarboleia.php" class="btn btn-success px-4">
                                <i class="bi bi-plus-lg"></i> Criar Boleia
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Minhas Reservas Tab -->
            <div class="tab-pane fade <?= $active_tab === 'reservas' ? 'show active' : '' ?>" id="reservas" role="tabpanel" aria-labelledby="reservas-tab">
                <div class="row">
                    <?php if (count($reservas) > 0) : ?>
                        <?php foreach ($reservas as $reserva) : ?>
                            <?php
                            $horario_display = date('H:i', strtotime($reserva['horario']));
                            $data_display = date('d/m/Y', strtotime($reserva['data']));
                            ?>
                            <div class="col-md-4">
                                <div class="card mb-3 shadow-sm card-hover">
                                    <a href="../detalhes.php?id=<?= $reserva['boleia_id'] ?>" class="text-decoration-none">
                                        <div class="card-body">
                                            <h5 class="card-title text-dark">
                                                <?= htmlspecialchars($reserva['ponto_partida']) ?>
                                                <?= !empty($reserva['ponto_intermedio']) ? ' - ' . htmlspecialchars($reserva['ponto_intermedio']) : '' ?>
                                                - <?= htmlspecialchars($reserva['destino']) ?>
                                            </h5>
                                            <p class="mb-1 text-success"><?= $reserva['total_pessoas'] ?> <?= ($reserva['total_pessoas'] == 1) ? 'Pessoa' : 'Pessoas' ?></p>
                                            <p class="mb-1 text-muted">Data: <?= $data_display ?></p>
                                            <p class="mb-3 text-muted">Horário: <?= $horario_display ?></p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="text-center mt-3">
                            <img src="../public/imagens/sem_reservas.png" alt="Sem reservas" width="200" class="mb-3">
                            <h4>Ainda não tem reservas</h4>
                            <p class="text-muted">Explore as boleias disponíveis e reserve o seu lugar.</p>
                            <a href="../pesquisar.php" class="btn btn-success px-4">
                                <i class="bi bi-search"></i> Procurar Boleias
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Função para esconder mensagens após 3 segundos
        function hideAlerts() {
            const successAlert = document.getElementById('success-alert');
            const errorAlert = document.getElementById('error-alert');

            if (successAlert) {
                setTimeout(() => {
                    successAlert.style.transition = 'opacity 1s';
                    successAlert.style.opacity = '0';
                    setTimeout(() => successAlert.remove(), 1000);
                }, 3000); // 3 segundos
            }

            if (errorAlert) {
                setTimeout(() => {
                    errorAlert.style.transition = 'opacity 1s';
                    errorAlert.style.opacity = '0';
                    setTimeout(() => errorAlert.remove(), 1000);
                }, 3000); // 3 segundos
            }
        }

        // Executa quando a página carrega
        document.addEventListener('DOMContentLoaded', hideAlerts);
    </script>
</body>

</html>