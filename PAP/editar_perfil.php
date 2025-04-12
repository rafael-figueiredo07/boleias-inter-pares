<?php
include 'db.php';
session_start();

if (!isset($_SESSION["loggedin"])) {
    header("location: login.php");
    exit;
}

$utilizador_id = $_SESSION['utilizador_id'];
$mensagem_sucesso = $mensagem_erro = "";

// Buscar dados do utilizador
try {
    $conn = conect_db();
    $stmt = $conn->prepare("SELECT * FROM utilizador WHERE utilizador_id = ?");
    $stmt->execute([$utilizador_id]);
    $utilizador = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$utilizador) {
        header("location: perfil.php");
        exit;
    }
} catch (PDOException $e) {
    $mensagem_erro = "Erro ao carregar dados: " . $e->getMessage();
}

// Processar atualização
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dados = [
        'nome' => trim($_POST["nome"]),
        'email' => trim($_POST["email"]),
        'contacto' => trim($_POST["contacto"]),
        'morada' => trim($_POST["morada"]),
        'codigo_postal' => trim($_POST["codigo_postal"]),
        'localidade' => trim($_POST["localidade"])
    ];

    // Processar password se fornecida
    if (!empty($_POST["nova_password"])) {
        if ($_POST["nova_password"] !== $_POST["confirmar_password"]) {
            $mensagem_erro = "As passwords não coincidem!";
        } else {
            $dados['password'] = password_hash($_POST["nova_password"], PASSWORD_DEFAULT);
        }
    }

    if (empty($mensagem_erro)) {
        try {
            // Construir query dinâmica
            $campos = [];
            $valores = [];
            
            foreach ($dados as $campo => $valor) {
                $campos[] = "$campo = ?";
                $valores[] = $valor;
            }
            
            $valores[] = $utilizador_id; // Para o WHERE
            
            $sql = "UPDATE utilizador SET " . implode(", ", $campos) . " WHERE utilizador_id = ?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt->execute($valores)) {
                $_SESSION['nome'] = $dados['nome'];
                $mensagem_sucesso = "Perfil atualizado com sucesso!";
                $utilizador = array_merge($utilizador, $dados);
            }
        } catch (PDOException $e) {
            $mensagem_erro = $e->getCode() == 23000 ? 
                "Este email já está em uso." : "Erro ao atualizar: " . $e->getMessage();
        }
    }
}

// Processar upload de foto
if (!empty($_FILES['foto_perfil']['name'])) {
    require 'upload_foto.php';
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil | Boleias Inter Pares</title>
    <link rel="icon" href="public/imagens/LOGO.png">
    <link rel="stylesheet" href="public/css/bootstrap.min.css">
    <link rel="stylesheet" href="public/css/padding_top.css">
    <link rel="stylesheet" href="public/css/bootstrap-icons-1.11.3/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #198754;
            --primary-light: rgba(25, 135, 84, 0.1);
            --dark: #212529;
            --light: #f8f9fa;
            --border: #e9ecef;
        }

        body {
            background-color: #f5f7fa;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .profile-edit-card {
            max-width: 800px;
            margin: 2rem auto;
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .profile-header-minimal {
            background: white;
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid var(--border);
        }

        .avatar-wrapper {
            width: 100px;
            height: 100px;
            margin: 0 auto 1rem;
            position: relative;
            border-radius: 50%;
            background: var(--light);
        }

        .avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .avatar-edit {
            position: absolute;
            bottom: -5px;
            right: -5px;
            background: var(--primary);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid white;
        }

        .nav-tabs-custom {
            border-bottom: 1px solid var(--border);
            padding: 0 2rem;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            color: var(--dark);
            font-weight: 500;
            padding: 1rem 1.5rem;
            position: relative;
        }

        .nav-tabs-custom .nav-link.active {
            color: var(--primary);
            background: transparent;
        }

        .nav-tabs-custom .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--primary);
            border-radius: 3px 3px 0 0;
        }

        .tab-content-custom {
            padding: 2rem;
            background: white;
        }

        .form-label-custom {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .form-control-custom {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.2s;
        }

        .form-control-custom:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .btn-save {
            background: var(--primary);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--dark);
        }

        .input-group-password {
            position: relative;
        }
    </style>
</head>

<body>
    <?php include 'public/includes/header.php'; ?>

    <div class="container my-5">
        <div class="profile-edit-card">
            <!-- Cabeçalho Minimalista -->
            <div class="profile-header-minimal">
                <form action="editar_perfil.php" method="post" enctype="multipart/form-data">
                    <div class="avatar-wrapper">
                        <img src="public/imagens/perfis/<?= htmlspecialchars($utilizador['foto_perfil'] ?? 'defaultpfp.jpg') ?>"
                            class="avatar-img"
                            id="avatarPreview">
                        <label for="avatarInput" class="avatar-edit">
                            <i class="bi bi-pencil-fill"></i>
                        </label>
                        <input type="file" id="avatarInput" name="foto_perfil" accept="image/*" class="d-none">
                    </div>
                    <h4 class="mb-0"><?= htmlspecialchars($utilizador['nome']) ?></h4>
                    <p class="text-muted">Editar perfil</p>
                </form>
            </div>

            <!-- Navegação por abas -->
            <ul class="nav nav-tabs nav-tabs-custom" id="profileTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
                        <i class="bi bi-person me-2"></i>Informações
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                        <i class="bi bi-shield-lock me-2"></i>Segurança
                    </button>
                </li>
            </ul>

            <!-- Conteúdo das abas -->
            <div class="tab-content tab-content-custom" id="profileTabsContent">
                <!-- Mensagens -->
                <?php if ($mensagem_sucesso): ?>
                    <div class="alert alert-success mb-4"><?= $mensagem_sucesso ?></div>
                <?php endif; ?>
                <?php if ($mensagem_erro): ?>
                    <div class="alert alert-danger mb-4"><?= $mensagem_erro ?></div>
                <?php endif; ?>

                <!-- Aba Informações -->
                <div class="tab-pane fade show active" id="info" role="tabpanel">
                    <form action="editar_perfil.php" method="post">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nome" class="form-label-custom">Nome</label>
                                <input type="text" class="form-control form-control-custom" id="nome" name="nome"
                                    value="<?= htmlspecialchars($utilizador['nome']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label-custom">Email</label>
                                <input type="email" class="form-control form-control-custom" id="email" name="email"
                                    value="<?= htmlspecialchars($utilizador['email']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="contacto" class="form-label-custom">Contacto</label>
                                <input type="tel" class="form-control form-control-custom" id="contacto" name="contacto"
                                    value="<?= htmlspecialchars($utilizador['contacto']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="localidade" class="form-label-custom">Localidade</label>
                                <input type="text" class="form-control form-control-custom" id="localidade" name="localidade"
                                    value="<?= htmlspecialchars($utilizador['localidade'] ?? '') ?>">
                            </div>

                            <div class="col-12">
                                <label for="morada" class="form-label-custom">Morada</label>
                                <input type="text" class="form-control form-control-custom" id="morada" name="morada"
                                    value="<?= htmlspecialchars($utilizador['morada'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="codigo_postal" class="form-label-custom">Código Postal</label>
                                <input type="text" class="form-control form-control-custom" id="codigo_postal" name="codigo_postal"
                                    value="<?= htmlspecialchars($utilizador['codigo_postal'] ?? '') ?>">
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-save">
                                    <i class="bi bi-check-circle me-2"></i>Guardar Alterações
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Aba Segurança -->
                <div class="tab-pane fade" id="security" role="tabpanel">
                    <form action="editar_perfil.php" method="post">
                        <div class="alert alert-info bg-light border-0 mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            Deixe os campos em branco se não desejar alterar a password
                        </div>

                        <div class="mb-3">
                            <label for="nova_password" class="form-label-custom">Nova Password</label>
                            <div class="input-group-password">
                                <input type="password" class="form-control form-control-custom" id="nova_password" name="nova_password">
                                <i class="bi bi-eye-slash password-toggle" onclick="togglePassword('nova_password', this)"></i>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="confirmar_password" class="form-label-custom">Confirmar Password</label>
                            <div class="input-group-password">
                                <input type="password" class="form-control form-control-custom" id="confirmar_password" name="confirmar_password">
                                <i class="bi bi-eye-slash password-toggle" onclick="togglePassword('confirmar_password', this)"></i>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-save">
                            <i class="bi bi-shield-lock me-2"></i>Atualizar Segurança
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Preview da foto de perfil
        document.getElementById('avatarInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('avatarPreview').src = event.target.result;
                    e.target.closest('form').submit();
                };
                reader.readAsDataURL(file);
            }
        });

        // Alternar visibilidade da password
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace("bi-eye-slash", "bi-eye");
            } else {
                input.type = "password";
                icon.classList.replace("bi-eye", "bi-eye-slash");
            }
        }
    </script>
</body>

</html>