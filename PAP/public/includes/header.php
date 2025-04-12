<?php
$dashboard_link = "/PAP/utilizadores/dashboard_user.php"; // Por padrão, leva para dashboard do utilizador
if (isset($_SESSION['perfil']) && $_SESSION['perfil'] == 1) {
    $dashboard_link = "/PAP/admin/dashboard_admin.php"; // Se for admin, muda o link
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/PAP/public/imagens/LOGO.png">
    <link rel="stylesheet" href="/PAP/public/css/bootstrap.min.css">
    <link rel="stylesheet" href="/PAP/public/css/bootstrap-icons-1.11.3/font/bootstrap-icons.css">

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
    <nav>
        <div class="fixed-top bg-white shadow-sm p-3">
            <div class="container d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <a href="/PAP/index.php" style="margin-right: 15px;">
                        <img src="/PAP/public/imagens/LOGO.png" alt="logo" style="width: 45px; height: auto;">
                    </a>
                    <h1 class="fs-3 mb-0">Boleias Inter Pares</h1>
                </div>

                <div class="d-none d-md-flex gap-2">
                    <?php if (basename($_SERVER['PHP_SELF']) !== 'pesquisar.php') { ?>
                        <a href="/PAP/pesquisar.php" class="btn btn-outline-success">
                            <i class="bi bi-search"></i> Pesquisar
                        </a>
                    <?php } ?>
                    <?php if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] != 1) { ?>
                    <a href="/PAP/utilizadores/criarboleia.php" class="btn btn-outline-success">
                        <i class="bi bi-plus-lg"></i> Criar Boleia
                    </a>
                    <?php } ?>
                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) { ?>
                        <div class="dropdown">
                            <button class="btn btn-success d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo htmlspecialchars($_SESSION['nome']); ?>
                                <i class="bi bi-caret-down ms-2"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="/PAP/perfil.php">
                                        <i class="bi bi-person"></i> Perfil
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $dashboard_link; ?>">
                                        <i class="bi bi-filter-left"></i> Dashboard
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/PAP/logout.php">
                                        <i class="bi bi-box-arrow-right"></i> Sair
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php } else { ?>
                        <a href="login.php" class="btn btn-success">
                            <i class="bi bi-box-arrow-in-left"></i> Login
                        </a>
                    <?php } ?>
                </div>

                <!-- Versão Móvel -->
                <div class="d-md-none dropdown">
                    <button class="btn btn-outline-success" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi-chevron-compact-down"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/PAP/pesquisar.php">Pesquisar</a></li>
                        <li><a class="dropdown-item" href="/PAP/utilizadores/criarboleia.php">Criar Boleia</a></li>
                        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) { ?>
                            <li><a class="dropdown-item" href="/PAP/perfil.php">Perfil</a></li>
                            <li><a class="dropdown-item" href="/PAP/utilizadores/dashboard_user.php">Dashboard</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/PAP/logout.php">Sair</a></li>
                        <?php } else { ?>
                            <li><a class="dropdown-item" href="login.php">Login</a></li>
                        <?php } ?>
                    </ul>
                </div>

                <!-- <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const dropdownButton = document.getElementById("dropdownButton");
                        const dropdownIcon = document.getElementById("dropdownIcon");

                        dropdownButton.addEventListener("shown.bs.dropdown", function() {
                            dropdownIcon.classList.replace("bi-caret-down", "bi-caret-down-fill");
                        });

                        dropdownButton.addEventListener("hidden.bs.dropdown", function() {
                            dropdownIcon.classList.replace("bi-caret-down-fill", "bi-caret-down");
                        });
                    });
                </script> -->

                <script src="/PAP/public/js/bootstrap.bundle.min.js"></script>
            </div>
        </div>
    </nav>