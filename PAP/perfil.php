<?php
include 'db.php';
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Obter ID do perfil a ser visualizado (próprio ou outro usuário)
$perfil_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['utilizador_id'];
$e_proprio_perfil = ($perfil_id == $_SESSION['utilizador_id']);

try {
    $conn = conect_db();

    // Buscar dados do perfil
    $sql = "SELECT u.*, 
                   (SELECT COUNT(*) FROM boleia WHERE utilizador_id = u.utilizador_id) as total_boleias,
                   (SELECT COUNT(*) FROM reserva WHERE utilizador_id = u.utilizador_id AND status = 'aprovada') as total_reservas
            FROM utilizador u 
            WHERE utilizador_id = :perfil_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':perfil_id', $perfil_id, PDO::PARAM_INT);
    $stmt->execute();
    $perfil = $stmt->fetch(PDO::FETCH_ASSOC);

    // Definir o nome a partir dos dados do perfil
    $nome_perfil = htmlspecialchars($perfil['nome']); // Nome do perfil a ser visualizado
    $nome_user = htmlspecialchars($_SESSION['nome']); // Nome do user logado

    // Buscar boleias do utilizador
    $sql_boleias = "SELECT * FROM boleia WHERE utilizador_id = :perfil_id ORDER BY data DESC LIMIT 3";
    $stmt_boleias = $conn->prepare($sql_boleias);
    $stmt_boleias->bindParam(':perfil_id', $perfil_id, PDO::PARAM_INT);
    $stmt_boleias->execute();
    $boleias = $stmt_boleias->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    exit('Erro ao buscar dados: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $nome ?> | Boleias Inter Pares</title>
    <link rel="icon" href="public/imagens/LOGO.png">
    <link rel="stylesheet" href="public/css/bootstrap.min.css">
    <link rel="stylesheet" href="public/css/padding_top.css">
    <link rel="stylesheet" href="public/css/bootstrap-icons-1.11.3/font/bootstrap-icons.css">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #198754, #2ecc71);
            color: white;
            padding: 2rem 0;
            border-radius: 0 0 10px 10px;
            margin-bottom: 2rem;
        }

        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .stats-card {
            border-left: 4px solid #198754;
            transition: all 0.3s;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .ride-card {
            border-left: 3px solid #198754;
            transition: all 0.3s;
        }

        .ride-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .contact-badge {
            background-color: #f8f9fa;
            border-radius: 20px;
            padding: 8px 15px;
        }

        .section-title {
            border-bottom: 2px solid #198754;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <?php include 'public/includes/header.php'; ?>

    <!-- Mensagem de Sucesso -->
    <?php if (isset($_SESSION['success_msg'])) : ?>
        <div id="success-alert" class="alert alert-success text-center"><?= $_SESSION['success_msg']; ?></div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <!-- Mensagem de Erro -->
    <?php if (!empty($mensagem_erro)) : ?>
        <div id="error-alert" class="alert alert-danger text-center"><?= $mensagem_erro; ?></div>
    <?php endif; ?>

    <div class="profile-header">
        <div class="container text-center">
            <div class="d-flex justify-content-center">
                <img src="public/imagens/<?= $perfil['foto_perfil'] ?? 'defaultpfp.jpg' ?>"
                    alt="Foto de perfil"
                    class="profile-img rounded-circle mb-3">
            </div>
            <h2 class="mb-2"><?= htmlspecialchars($perfil['nome']) ?></h2>
            <p class="mb-1">
                <i class="bi bi-geo-alt"></i>
                <?= htmlspecialchars($perfil['localidade'] ?? 'Não especificado') ?>
            </p>
            <div class="d-flex justify-content-center gap-2 mt-3">
                <?php if ($e_proprio_perfil): ?>
                    <a href="editar_perfil.php" class="btn btn-light">
                        <i class="bi bi-pencil"></i> Editar Perfil
                    </a>
                <?php else: ?>
                    <a href="mensagens.php?to=<?= $perfil_id ?>" class="btn btn-light">
                        <i class="bi bi-envelope"></i> Mensagem
                    </a>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $perfil['contacto']) ?>"
                        class="btn btn-light"
                        target="_blank">
                        <i class="bi bi-whatsapp"></i> WhatsApp
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row">
            <!-- Coluna Esquerda -->
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="section-title">
                            <i class="bi bi-info-circle"></i> Informações
                        </h5>

                        <div class="mb-3">
                            <h6><i class="bi bi-envelope text-muted"></i> Email</h6>
                            <p><?= htmlspecialchars($perfil['email']) ?></p>
                        </div>

                        <div class="mb-3">
                            <h6><i class="bi bi-telephone text-muted"></i> Contacto</h6>
                            <p><?= htmlspecialchars($perfil['contacto']) ?></p>
                        </div>

                        <div class="mb-3">
                            <h6><i class="bi bi-house text-muted"></i> Morada</h6>
                            <p><?= htmlspecialchars($perfil['morada'] ?? 'Não especificada') ?></p>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="section-title">
                            <i class="bi bi-activity"></i> Estatísticas
                        </h5>

                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="stats-card p-3">
                                    <h3 class="text-success"><?= $perfil['total_boleias'] ?></h3>
                                    <p class="text-muted mb-0">Boleias</p>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stats-card p-3">
                                    <h3 class="text-success"><?= $perfil['total_reservas'] ?></h3>
                                    <p class="text-muted mb-0">Reservas</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coluna Direita -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="section-title">
                            <i class="bi bi-car-front"></i> Últimas Boleias
                        </h5>

                        <?php if (count($boleias) > 0): ?>
                            <?php foreach ($boleias as $boleia): ?>
                                <a href="detalhes.php?id=<?= $boleia['boleia_id'] ?>"
                                    class="ride-card p-3 mb-3 rounded d-block text-decoration-none text-dark hover-effect">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="mb-1">
                                                <?= htmlspecialchars($boleia['ponto_partida']) ?>
                                                <i class="bi bi-arrow-right"></i>
                                                <?= htmlspecialchars($boleia['destino']) ?>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar"></i>
                                                <?= date('d/m/Y', strtotime($boleia['data'])) ?>
                                                &nbsp;|&nbsp;
                                                <i class="bi bi-clock"></i>
                                                <?= date('H:i', strtotime($boleia['horario'])) ?>
                                            </small>
                                        </div>
                                        <div>
                                            <span class="badge bg-success">
                                                <?= $boleia['total_pessoas'] ?> pessoas
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>

                            <div class="text-center mt-3">
                                <a href="boleias.php?user=<?= $perfil_id ?>"
                                    class="btn btn-sm btn-success">
                                    Ver todas as boleias
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-car-front" style="font-size: 2rem; color: #6c757d;"></i>
                                <p class="text-muted mt-2">Nenhuma boleia criada</p>
                                <?php if ($e_proprio_perfil): ?>
                                    <a href="criar_boleia.php" class="btn btn-sm btn-success">
                                        <i class="bi bi-plus"></i> Criar primeira boleia
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="section-title">
                            <i class="bi bi-star"></i> Avaliações
                        </h5>

                        <div class="text-center py-4">
                            <i class="bi bi-star" style="font-size: 2rem; color: #6c757d;"></i>
                            <p class="text-muted mt-2">Sistema de avaliações em breve</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Script para pré-visualização da imagem de perfil
        document.addEventListener("DOMContentLoaded", function() {
            const fileInput = document.getElementById('fotoPerfil');
            const profileImg = document.getElementById('profileImage');

            if (fileInput && profileImg) {
                fileInput.addEventListener('change', function(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            profileImg.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>

    <?php include 'public/includes/footer.php'; ?>
</body>

</html>