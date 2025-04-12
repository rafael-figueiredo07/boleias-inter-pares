<?php

include 'db.php';
session_start();

// Inicializar todas as variáveis
$boleia = null;
$reservas = [];
$reservas_aprovadas = [];
$reservas_pendentes = [];
$minha_reserva = null;
$vagas_disponiveis = 0;
$mensagem_erro = "";
$horario_display = "";
$data_display = "";

// Verificar autenticação
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Verificar se o ID da boleia foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("location: utilizadores/dashboard_user.php");
    exit;
}

$boleia_id = (int)$_GET['id'];
$utilizador_id = $_SESSION['utilizador_id'];

try {
    $conn = conect_db();

    // Buscar nome do utilizador atual
    $sql = "SELECT nome FROM utilizador WHERE utilizador_id = :utilizador_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':utilizador_id', $utilizador_id, PDO::PARAM_INT);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $nome = $resultado ? $resultado['nome'] : "Utilizador Desconhecido";

    //     // Buscar foto do condutor
    //     if ($boleia['utilizador_id'] !== $utilizador_id) {
    //         $sql_condutor = "SELECT foto_perfil FROM utilizador WHERE utilizador_id = :condutor_id";
    //         $stmt_condutor = $conn->prepare($sql_condutor);
    //         $stmt_condutor->bindParam(':condutor_id', $boleia['utilizador_id'], PDO::PARAM_INT);
    //         $stmt_condutor->execute();
    //         $perfil_condutor = $stmt_condutor->fetch(PDO::FETCH_ASSOC);
    //     }

    //     // Buscar detalhes da boleia
    $sql_boleia = "SELECT b.boleia_id, b.utilizador_id, b.ponto_partida, b.ponto_intermedio, 
                          b.destino, b.total_pessoas, b.data, b.horario, 
                          u.nome AS nome_condutor, u.contacto 
                   FROM boleia b
                   JOIN utilizador u ON b.utilizador_id = u.utilizador_id
                   WHERE b.boleia_id = :boleia_id";

    $stmt_boleia = $conn->prepare($sql_boleia);
    $stmt_boleia->bindParam(':boleia_id', $boleia_id, PDO::PARAM_INT);
    $stmt_boleia->execute();
    $boleia = $stmt_boleia->fetch(PDO::FETCH_ASSOC);

    if (!$boleia) {
        $_SESSION['error_msg'] = "Boleia não encontrada!";
        header("location: utilizadores/dashboard_user.php");
        exit;
    }

    // Buscar reservas desta boleia (atualizada)
    // $sql_reservas = "SELECT r.reserva_id, r.status, u.utilizador_id, u.nome, u.contacto, u.foto_perfil 
    $sql_reservas = "SELECT r.reserva_id, r.status, u.utilizador_id, u.nome, u.contacto
                 FROM reserva r
                 JOIN utilizador u ON r.utilizador_id = u.utilizador_id
                 WHERE r.boleia_id = :boleia_id
                 ORDER BY r.data_reserva DESC";
    $stmt_reservas = $conn->prepare($sql_reservas);
    $stmt_reservas->bindParam(':boleia_id', $boleia_id, PDO::PARAM_INT);
    $stmt_reservas->execute();
    $reservas = $stmt_reservas->fetchAll(PDO::FETCH_ASSOC);

    // Separar reservas aprovadas e pendentes
    $reservas_aprovadas = array_filter($reservas, function ($r) {
        return $r['status'] == 'aprovada';
    });

    $reservas_pendentes = array_filter($reservas, function ($r) {
        return $r['status'] == 'pendente';
    });

    // Verificar se o utilizador já reservou esta boleia
    $sql_verifica_reserva = "SELECT reserva_id, status FROM reserva 
                            WHERE boleia_id = :boleia_id AND utilizador_id = :utilizador_id";
    $stmt_verifica = $conn->prepare($sql_verifica_reserva);
    $stmt_verifica->bindParam(':boleia_id', $boleia_id, PDO::PARAM_INT);
    $stmt_verifica->bindParam(':utilizador_id', $utilizador_id, PDO::PARAM_INT);
    $stmt_verifica->execute();
    $minha_reserva = $stmt_verifica->fetch(PDO::FETCH_ASSOC);

    // Calcular vagas disponíveis
    $vagas_disponiveis = $boleia['total_pessoas'] - count($reservas_aprovadas);

    // Formatando datas e horários
    $horario_display = date('H:i', strtotime($boleia['horario']));
    $data_display = date('d/m/Y', strtotime($boleia['data']));

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
                $_SESSION['error_msg'] = "Erro: Dados incompletos!";
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
                    header("Location: detalhes.php?id=" . $boleia_id);
                    exit;
                } else {
                    $_SESSION['error_msg'] = "Erro ao atualizar a boleia!";
                }
            }
        } catch (PDOException $e) {
            $_SESSION['error_msg'] = 'Erro ao atualizar: ' . $e->getMessage();
        }
    }

    // Processar reserva se o formulário foi submetido
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['reservar']) && !$minha_reserva) {
            $sql_insert = "INSERT INTO reserva (boleia_id, utilizador_id, status) 
                           VALUES (:boleia_id, :utilizador_id, 'pendente')";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bindParam(':boleia_id', $boleia_id, PDO::PARAM_INT);
            $stmt_insert->bindParam(':utilizador_id', $utilizador_id, PDO::PARAM_INT);

            if ($stmt_insert->execute()) {
                $_SESSION['success_msg'] = "Pedido de reserva enviado com sucesso! Aguarde a aprovação do condutor.";
                header("Location: detalhes.php?id=" . $boleia_id);
                exit;
            } else {
                $mensagem_erro = "Erro ao solicitar reserva!";
            }
        }

        // Processar cancelamento de reserva
        if (isset($_POST['cancelar_reserva']) && $minha_reserva) {
            $sql_delete = "DELETE FROM reserva 
                          WHERE boleia_id = :boleia_id AND utilizador_id = :utilizador_id";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bindParam(':boleia_id', $boleia_id, PDO::PARAM_INT);
            $stmt_delete->bindParam(':utilizador_id', $utilizador_id, PDO::PARAM_INT);

            if ($stmt_delete->execute()) {
                $_SESSION['success_msg'] = "Reserva cancelada com sucesso!";
                header("Location: detalhes.php?id=" . $boleia_id);
                exit;
            } else {
                $mensagem_erro = "Erro ao cancelar a reserva!";
            }
        }

        // Processar aprovação/recusa pelo condutor
        if (isset($_POST['acao_reserva']) && $boleia['utilizador_id'] == $utilizador_id) {
            $reserva_id = $_POST['reserva_id'];
            $acao = $_POST['acao_reserva'];

            $sql_update = "UPDATE reserva SET status = :status 
                          WHERE reserva_id = :reserva_id AND boleia_id = :boleia_id";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bindParam(':status', $acao);
            $stmt_update->bindParam(':reserva_id', $reserva_id, PDO::PARAM_INT);
            $stmt_update->bindParam(':boleia_id', $boleia_id, PDO::PARAM_INT);

            if ($stmt_update->execute()) {
                $acao_msg = $acao == 'aprovada' ? 'aprovada' : 'recusada';
                $_SESSION['success_msg'] = "Reserva $acao_msg com sucesso!";
                header("Location: detalhes.php?id=" . $boleia_id);
                exit;
            } else {
                $mensagem_erro = "Erro ao processar a ação!";
            }
        }
    }
} catch (PDOException $e) {
    $_SESSION['error_msg'] = 'Erro ao buscar dados: ' . $e->getMessage();
    header("location: utilizadores/dashboard_user.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Boleia | Boleias Inter Pares</title>
    <link rel="icon" href="/PAP/public/imagens/LOGO.png">
    <link rel="stylesheet" href="public/css/padding_top.css">
    <link rel="stylesheet" href="/PAP/public/css/bootstrap.min.css">
    <link rel="stylesheet" href="/PAP/public/css/bootstrap-icons-1.11.3/font/bootstrap-icons.css">
    <style>
        .journey-path {
            position: relative;
            padding-left: 30px;
            margin-bottom: 20px;
        }

        .journey-path::before {
            content: "";
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #198754;
        }

        .path-point {
            position: relative;
            margin-bottom: 15px;
        }

        .path-point::before {
            content: "";
            position: absolute;
            left: -20px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #198754;
        }

        .path-point:last-child::before {
            background: #dc3545;
        }

        .driver-card {
            border-left: 3px solid #198754;
        }

        .passenger-card {
            border-left: 3px solid #0d6efd;
        }

        .map-container {
            height: 300px;
            background-color: #f8f9fa;
            border-radius: 8px;
            overflow: hidden;
        }

        .badge-vagas {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }

        .alert-message {
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .top-alert {
            position: fixed;
            top: 70px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            min-width: 300px;
            text-align: center;
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        #topAlert {
            margin: 1rem auto;
            max-width: 800px;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hover-effect:hover {
            background-color: #f8f9fa !important;
            transition: background-color 0.2s ease;
            opacity: 1 !important;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <?php include 'public/includes/header.php'; ?>

        <!-- Mensagens de status -->
        <?php if (isset($_SESSION['success_msg'])) : ?>
            <div class="alert alert-success alert-dismissible fade show text-center" id="topAlert">
                <?= $_SESSION['success_msg']; ?>
            </div>
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_msg'])) : ?>
            <div class="alert alert-danger alert-dismissible fade show text-center" id="topAlert">
                <?= $_SESSION['error_msg']; ?>
            </div>
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>

        <div class="row">
            <!-- Coluna esquerda - Detalhes da viagem -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Detalhes do Percurso</h5>
                        <?php if ($boleia['utilizador_id'] === $utilizador_id): ?>
                            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#editarBoleiaModal">
                                <i class="bi bi-pencil"></i> Editar Boleia
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="journey-path">
                            <div class="path-point">
                                <h5 class="text-success">Partida: <?= htmlspecialchars($boleia['ponto_partida']) ?></h5>
                                <p class="text-muted mb-2"><?= $data_display ?> às <?= $horario_display ?></p>
                            </div>

                            <?php if (!empty($boleia['ponto_intermedio'])): ?>
                                <div class="path-point">
                                    <h6 class="text-primary">Ponto Intermédio: <?= htmlspecialchars($boleia['ponto_intermedio']) ?></h6>
                                </div>
                            <?php endif; ?>

                            <div class="path-point">
                                <h5 class="text-danger">Destino: <?= htmlspecialchars($boleia['destino']) ?></h5>
                            </div>
                        </div>

                        <!-- Vagas disponíveis -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <span class="badge bg-<?= ($vagas_disponiveis > 0) ? 'success' : 'danger' ?> badge-vagas">
                                    <i class="bi bi-people"></i> <?= $vagas_disponiveis ?> Vagas disponíveis
                                </span>
                                <span class="ms-2 text-muted">(Total: <?= $boleia['total_pessoas'] ?>)</span>
                            </div>

                            <?php if ($boleia['utilizador_id'] !== $utilizador_id): ?>
                                <?php if ($vagas_disponiveis > 0): ?>
                                    <?php if ($minha_reserva): ?>
                                        <?php if ($minha_reserva['status'] == 'pendente'): ?>
                                            <div class="alert alert-warning py-1 mb-0">
                                                <i class="bi bi-hourglass"></i> A aguardar aprovação
                                            </div>
                                        <?php else: ?>
                                            <form method="POST" style="display: inline;">
                                                <button type="submit" name="cancelar_reserva" class="btn btn-danger">
                                                    <i class="bi bi-x-circle"></i> Cancelar Reserva
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <form method="POST" style="display: inline;">
                                            <button type="submit" name="reservar" class="btn btn-success">
                                                <i class="bi bi-check-circle"></i> Solicitar Reserva
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Cheio</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Mapa (simulado) -->
                        <div class="map-container mb-3 d-flex align-items-center justify-content-center">
                            <div class="text-center">
                                <i class="bi bi-map" style="font-size: 2rem; color: #6c757d;"></i>
                                <p class="mt-2 text-muted">Mapa do percurso</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coluna direita -->
            <div class="col-lg-4">
                <!-- Card do Condutor (apenas para não condutores) -->
                <?php if ($boleia['utilizador_id'] !== $utilizador_id): ?>
                    <div class="card shadow-sm driver-card mb-4">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-person-badge"></i> Condutor</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <!-- <div class="flex-shrink-0"> -->
                                    <!-- <img src="public/imagens/<?= $perfil_condutor['foto_perfil'] ?? 'defaultpfp.jpg' ?>"
                                        class="rounded-circle"
                                        width="60"
                                        height="60"
                                        alt="Foto do condutor"> -->
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5><?= htmlspecialchars($boleia['nome_condutor']) ?></h5>
                                    <p class="text-muted mb-1">
                                        <i class="bi bi-telephone"></i> <?= htmlspecialchars($boleia['contacto']) ?>
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $boleia['contacto']) ?>"
                                            class="ms-2 text-success"
                                            target="_blank"
                                            title="Contactar via WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    </p>
                                </div>
                                <div class="mt-2">
                                    <a href="perfil.php?id=<?= $boleia['utilizador_id'] ?>"
                                        class="btn btn-sm btn-light w-100">
                                        <i class="bi bi-box-arrow-up-right"></i> Perfil
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Sistema de Reservas (visível para todos) -->
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-people"></i> Sistema de Reservas</h5>
                            <span class="badge bg-light text-dark"><?= count($reservas_aprovadas) ?>/<?= $boleia['total_pessoas'] ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Reservas Aprovadas -->
                        <h6 class="text-success"><i class="bi bi-check-circle"></i> Reservas Confirmadas</h6>
                        <?php foreach ($reservas_aprovadas as $reserva): ?>
                            <div class="list-group-item border-0 py-2">
                                <div class="d-flex align-items-center">
                                    <!-- <div class="flex-shrink-0">
                                        <img src="public/imagens/<?= $reserva['foto_perfil'] ?? 'defaultpfp.jpg' ?>"
                                            class="rounded-circle"
                                            width="50"
                                            height="50"
                                            alt="Foto do passageiro">
                                    </div> -->
                                    <div class="flex-grow-1 ms-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="perfil.php?id=<?= $reserva['utilizador_id'] ?>"
                                                class="text-decoration-none text-dark w-100 hover-effect p-2 rounded d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1 d-flex align-items-center gap-2">
                                                        <?= htmlspecialchars($reserva['nome']) ?>
                                                    </h6>
                                                    <small class="text-muted">
                                                        <i class="bi bi-telephone"></i> <?= htmlspecialchars($reserva['contacto']) ?>
                                                    </small>
                                                </div>
                                                <i class="bi bi-chevron-right ms-2"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($boleia['utilizador_id'] === $utilizador_id): ?>
                                    <form method="POST" class="ms-2">
                                        <input type="hidden" name="reserva_id" value="<?= $reserva['reserva_id'] ?>">
                                        <button type="submit" name="acao_reserva" value="recusada" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                    </div>
                <?php endforeach; ?>
                </div>


                <!-- Reservas Pendentes (apenas visível para o condutor) -->
                <?php if ($boleia['utilizador_id'] === $utilizador_id && count($reservas_pendentes) > 0): ?>
                    <h6 class="text-warning mt-4"><i class="bi bi-hourglass"></i> Pedidos Pendentes</h6>
                    <div class="list-group">
                        <?php foreach ($reservas_pendentes as $reserva): ?>
                            <div class="list-group-item border-0 py-2">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-person" style="font-size: 1.5rem; color: #fd7e14;"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1"><?= htmlspecialchars($reserva['nome']) ?></h6>
                                        <small class="text-muted"><i class="bi bi-telephone"></i> <?= htmlspecialchars($reserva['contacto']) ?></small>
                                    </div>
                                    <form method="POST" class="ms-2">
                                        <input type="hidden" name="reserva_id" value="<?= $reserva['reserva_id'] ?>">
                                        <button type="submit" name="acao_reserva" value="aprovada" class="btn btn-sm btn-success me-1">
                                            <i class="bi bi-check"></i>
                                        </button>
                                        <button type="submit" name="acao_reserva" value="recusada" class="btn btn-sm btn-danger">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    </div>

    <?php include 'public/includes/footer.php'; ?>

    <!-- Modal de Edição -->
    <div class="modal fade" id="editarBoleiaModal" tabindex="-1" aria-labelledby="editarBoleiaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="editarBoleiaModalLabel">
                        <i class="bi bi-pencil-square me-2"></i>Editar Boleia
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="id" value="<?= $boleia['boleia_id'] ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Ponto de Partida</label>
                            <input type="text" class="form-control" name="partida" value="<?= htmlspecialchars($boleia['ponto_partida']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ponto Intermédio</label>
                            <input type="text" class="form-control" name="ponto_intermedio" value="<?= htmlspecialchars($boleia['ponto_intermedio']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Destino</label>
                            <input type="text" class="form-control" name="destino" value="<?= htmlspecialchars($boleia['destino']) ?>" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label">Total de Pessoas</label>
                                <input type="number" class="form-control" name="pessoas" value="<?= htmlspecialchars($boleia['total_pessoas']) ?>" required>
                            </div>
                            <div class="col">
                                <label class="form-label">Data</label>
                                <input type="date" class="form-control" name="data" value="<?= htmlspecialchars($boleia['data']) ?>" required>
                            </div>
                            <div class="col">
                                <label class="form-label">Horário</label>
                                <input type="time" class="form-control" name="horario" value="<?= date('H:i', strtotime($boleia['horario'])) ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </button>
                        <button type="submit" name="atualizar_boleia" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i>Guardar Alterações
                        </button>
                    </div>
                </form>
            </div>
            </form>
        </div>

        <script>
            // Esconder apenas o alerta do topo após 3 segundos
            setTimeout(() => {
                const topAlert = document.getElementById('topAlert');
                if (topAlert) {
                    topAlert.style.transition = 'opacity 1s';
                    topAlert.style.opacity = '0';
                    setTimeout(() => topAlert.remove(), 1000);
                }
            }, 3000);
        </script>

</body>

</html>