<?php
session_start();

require 'db.php';

// Verifica se o utilizador/admin já está logado
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
  if ($_SESSION["perfil"] == 1) {
    header("location: admin/dashboard_admin.php"); // Admin
  } else {
    header("location: utilizadores/dashboard_user.php"); // Utilizador
  }
  exit;
}

$login = $password = "";
$login_err = $password_err = "";

// Processa os dados do formulário quando o formulário é enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Valida o email/contacto
  if (empty(trim($_POST["login"]))) {
    $login_err = "Insira um email válido.";
  } else {
    $login = trim($_POST["login"]);
  }

  // Valida a password
  if (empty(trim($_POST["password"]))) {
    $password_err = "Insira a password.";
  } else {
    $password = trim($_POST["password"]);
  }

  // Verifica se não há erros de validação
  if (empty($login_err) && empty($password_err)) {
    try {
      $link = conect_db();

      // Consulta para verificar se o email existe
      $sql = "
      SELECT utilizador_id, nome, email, contacto, password, perfil, 'user' AS tipo
      FROM utilizador
      WHERE email = :login OR contacto = :login
      UNION
      SELECT admin_id AS utilizador_id, nome, email, '' AS contacto, password, 1 AS perfil, 'admin' AS tipo
      FROM admin WHERE email = :login";
      $stmt = $link->prepare($sql);
      $stmt->bindParam(":login", $login, PDO::PARAM_STR);
      $stmt->execute();

      // Verifica se o email existe
      if ($stmt->rowCount() == 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $utilizador_id = $row["utilizador_id"];
        $hashed_password = $row["password"];
        $perfil = $row["perfil"];

        // Verifica a password
        if (password_verify($password, $hashed_password)) {
          // Password correta, inicia a sessão
          $_SESSION["loggedin"] = true;
          $_SESSION["utilizador_id"] = $utilizador_id;
          $_SESSION["perfil"] = $perfil;
          $_SESSION["nome"] = $row["nome"];

          // Redireciona conforme o perfil
          if ($perfil == 1) {
            header("location: admin/dashboard_admin.php"); // Redireciona para o painel de admin
          } else {
            header("location: utilizadores/dashboard_user.php"); // Redireciona para o painel do utilizador
          }
          exit;
        } else {
          // Password incorreta
          $login_err = "A password está errada!";
        }
      } else {
        // Email não encontrado
        $login_err = "O email/contacto está errado!";
      }

      // Fecha a declaração
      unset($stmt);
    } catch (PDOException $e) {
      die("ERROR: Ocorreu um erro no comando $sql. " . $e->getMessage());
    }

    // Fecha a conexão
    unset($link);
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Boleias Inter Pares</title>
  <link rel="icon" href="public/imagens/LOGO.png">
  <link rel="stylesheet" href="public/css/bootstrap.min.css">
  <link rel="stylesheet" href="public/css/bootstrap-icons-1.11.3/font/bootstrap-icons.css">
</head>

<body>
  <div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow-sm" style="max-width: 400px; width: 100%;">
      <div class="d-flex align-items-center">
        <a href="index.php" style="margin-right: 100px; margin-bottom: 15px;">
          <img src="public/imagens/LOGO.png" alt="logo" style="width: 45px; height: auto;">
        </a>
        <h1 class="text-center mb-4 fs-4">Login</h1>
      </div>
      <?php
      if (!empty($login_err)) {
        echo "<p style='color: red; font-weight: bold; text-align: center;'>" . htmlspecialchars($login_err) . "</p>";
      }
      ?>
      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <!-- Campo do Email ou Contacto -->
        <div class="mb-3">
          <label for="login" class="form-label">Email ou Contacto</label>  
          <input
            type="text"
            class="form-control"
            id="login"
            name="login"
            placeholder="Insira o email ou contacto"
            pattern="^(?:\d{9}|\w+@\w+\.\w{2,3})$"
            title="Insira um email válido ou um número de telemóvel (9 dígitos)."
            required>
        </div>
        <!-- Campo da Password -->
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <div class="input-group">
            <input type="password" class="form-control" id="password" name="password" placeholder="Insira a password" required>
            <span class="input-group-text bg-white">
              <i class="bi bi-eye-fill" id="eyeIcon" style="cursor: pointer;"></i>
            </span>
          </div>
        </div>

        <!-- Botão Login -->
        <button type="submit" class="btn btn-success w-100">Entrar</button>
      </form>

      <div class="text-center mt-3">
        <p class="mb-0">Não tem uma conta? <a href="utilizadores/form_registo.php" class="text-decoration-none">Registe-se</a></p>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const passwordInput = document.getElementById("password");
      const eyeIcon = document.getElementById("eyeIcon");

      eyeIcon.addEventListener("click", function() {
        if (passwordInput.type === "password") {
          passwordInput.type = "text";
          eyeIcon.classList.replace("bi-eye-fill", "bi-eye-slash-fill");
        } else {
          passwordInput.type = "password";
          eyeIcon.classList.replace("bi-eye-slash-fill", "bi-eye-fill");
        }
      });
    });
  </script>
</body>

</html>