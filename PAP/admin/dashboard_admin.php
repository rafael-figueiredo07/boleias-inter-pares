<?php

include '../db.php';

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

try {
    $conn = conect_db(); // Conecta à base de dados
    $admin_id = $_SESSION['utilizador_id'];
    $sql = "SELECT nome FROM admin WHERE admin_id = :admin_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) { // Verifica se a consulta retornou resultados
        $nome = $resultado['nome']; // Armazena o nome do utilizador
    } else {
        // Se não houver resultados, defina um valor padrão ou exiba uma mensagem de erro
        $nome = "Admin Desconhecido";
        // Ou redirecione para uma página de erro
        // header("Location: erro.php");
        // exit();
    }
} catch (PDOException $e) {
    exit('Erro ao buscar dados do admin: ' . $e->getMessage());
}

try {
    // Buscar todos os utilizadores
    $sql = "
        SELECT u.utilizador_id, u.nome, u.email, u.contacto, u.morada, u.codigo_postal, u.localidade, 
               (SELECT COUNT(*) FROM boleia b WHERE b.utilizador_id = u.utilizador_id) AS total_boleias
        FROM utilizador u
        ORDER BY u.nome
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $utilizadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    exit('Erro ao buscar utilizadores: ' . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | Boleias Inter Pares</title>
    <link rel="icon" href="/PAP/public/imagens/LOGO.png">
    <!--<link rel="stylesheet" href="../public/css/bootstrap.min.css"> -->
    <link rel="stylesheet" href="../public/css/padding_top.css">
    <style>
        .dropdown-item:active,
        .dropdown-item:focus,
        .dropdown-item:hover {
            background-color: #198754 !important;
            color: white !important;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center">
            <?php include '../public/includes/header.php'; ?>
        </div>

        <h4 class="card-title text-center mb-4">Dashboard</h4>

        <div class="row">
            <?php
            if (count($utilizadores) > 0) {
                foreach ($utilizadores as $utilizador) {
                    echo '
                        <div class="col-md-4">
                            <div class="card mb-3 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">' . htmlspecialchars($utilizador['nome']) . '</h5>
                                    <p class="mb-1 text-success">Boleias: <strong>' . $utilizador['total_boleias'] . '</strong></p>
                                    <p class="mb-1 text-muted">Email: ' . htmlspecialchars($utilizador['email']) . '</p>
                                    <p class="mb-2 text-muted">Contacto: ' . htmlspecialchars($utilizador['contacto']) . '</p>
                                    <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#modal' . $utilizador['utilizador_id'] . '">Ver Detalhes</button>
                                </div>
                            </div>
                        </div>

                        <!-- Modal para Ver/Editar Utilizador -->
                        <div class="modal fade" id="modal' . $utilizador['utilizador_id'] . '" tabindex="-1" aria-labelledby="modalLabel' . $utilizador['utilizador_id'] . '" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel' . $utilizador['utilizador_id'] . '">Detalhes do Utilizador</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="editar_utilizador.php" method="POST">
                                            <input type="hidden" name="utilizador_id" value="' . $utilizador['utilizador_id'] . '">
                                            <div class="mb-3">
                                                <label class="form-label">Nome</label>
                                                <input type="text" class="form-control" name="nome" value="' . htmlspecialchars($utilizador['nome']) . '" disabled>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" name="email" value="' . htmlspecialchars($utilizador['email']) . '" disabled>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col">
                                                    <label class="form-label">Contacto</label>
                                                    <input type="text" class="form-control" name="contacto" value="' . htmlspecialchars($utilizador['contacto']) . '" disabled>
                                                </div>
                                                <div class="col">
                                                    <label class="form-label">Morada</label>
                                                    <input type="text" class="form-control" name="morada" value="' . htmlspecialchars($utilizador['morada']) . '" disabled>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col">
                                                    <label class="form-label">Código Postal</label>
                                                    <input type="text" class="form-control" name="codigo_postal" value="' . htmlspecialchars($utilizador['codigo_postal']) . '" disabled>
                                                </div>
                                                <div class="col">
                                                    <label class="form-label">Localidade</label>
                                                    <input type="text" class="form-control" name="localidade" value="' . htmlspecialchars($utilizador['localidade']) . '" disabled>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                ' . ($utilizador['total_boleias'] > 0 ? 
                                                    '<a href="ver_boleias.php?utilizador_id=' . $utilizador['utilizador_id'] . '" class="btn btn-success">Ver Boleias</a>' 
                                                    : '<div></div>') . '
                                                <a href="eliminar_utilizador.php?id=' . $utilizador['utilizador_id'] . '" class="btn btn-danger">
                                                    <i class="bi bi-trash3"></i> Eliminar
                                                </a>
                                            </div>
                                            <div class="d-flex justify-content-end">
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                echo '
                <div class="text-center mt-3">
                    <img src="../public/imagens/sem_utilizadores.png" alt="Sem utilizadores" width="200" class="mb-3">
                    <h4>Ainda não existem utilizadores registados</h4>
                    <p class="text-muted">Registe novos utilizadores para começar a gerir.</p>
                    <a href="criar_utilizador.php" class="btn btn-success px-4">
                        <i class="bi bi-plus-lg"></i> Criar Utilizador
                    </a>
                </div>
                ';
            }
            ?>
        </div>
    </div>
    <!-- <script src="../public/js/bootstrap.bundle.min.js"></script> -->