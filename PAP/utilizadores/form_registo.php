<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registo | Boleias Inter Pares</title>
  <link rel="icon" href="../public/imagens/LOGO.png">
  <link rel="stylesheet" href="../public/css/bootstrap.min.css">
  <link rel="stylesheet" href="../public/css/bootstrap-icons-1.11.3/font/bootstrap-icons.css">
</head>

<body>
  <div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow-sm" style="max-width: 510px; width: 100%;">

      <div class="d-flex align-items-center">

        <a href="../index.php" style="margin-right: 125px; margin-bottom: 15px;">
          <img src="../public/imagens/LOGO.png" alt="logo" style="width: 45px; height: auto;">
        </a>
        <h1 class="mb-4 fs-4">Registo</h1>
      </div>

      <form action="registo.php" method="post">
        <?php
        if (isset($_GET['erro'])) {
          echo "<p style='color: red; font-weight: bold; text-align: center;'>" . htmlspecialchars($_GET['erro']) . "</p>";
        }
        ?>
        <div class="mb-2">
          <label for="nome" class="form-label">Nome</label>
          <input type="text" class="form-control" id="nome" name="nome" placeholder="Insira o seu nome" required>
        </div>
        <div class="row mb-2">
          <div class="col-7">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Insira o seu email" required>
          </div>
          <div class="col-5">
            <label for="contacto" class="form-label">Contacto</label>
            <input type="text" class="form-control" id="contacto" name="contacto" placeholder="Insira o seu contacto" pattern="[0-9\s\-]{9}" title="Insira um número de telefone válido com 9 dígitos." required>
          </div>
        </div>
        <div class="mb-2">
          <label for="morada" class="form-label">Morada</label>
          <input type="text" class="form-control" id="morada" name="morada" placeholder="Insira a sua morada">
        </div>
        <div class="row mb-3">
          <div class="col-8">
            <label for="localidade" class="form-label">Localidade</label>
            <input type="text" class="form-control" id="localidade" name="localidade" placeholder="Insira a sua localidade" required>
          </div>
          <div class="col-4">
            <label for="codigopostal" class="form-label">Código Postal</label>
            <input type="text" class="form-control" id="codigopostal" name="codigopostal" pattern="\d{4}([ -]\d{3})?" placeholder="0000-000">
          </div>
        </div>
        <div class="mb-3 row justify-content-center">
          <div class="col">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
              <input type="password" class="form-control" id="password" name="password" placeholder="Crie uma password" required>
              <span class="input-group-text bg-white">
                <i class="bi bi-eye-fill" id="togglePassword" style="cursor: pointer;"></i>
              </span>
            </div>
          </div>
          <div class="col">
            <label for="confirm-password" class="form-label">Confirmar Password</label>
            <div class="input-group">
              <input type="password" class="form-control" id="confirm-password" name="confirm-password" placeholder="Confirme a password" required>
              <span class="input-group-text bg-white">
                <i class="bi bi-eye-fill" id="toggleConfirmPassword" style="cursor: pointer;"></i>
              </span>
            </div>
          </div>
        </div>
        <button type="submit" class="btn btn-success w-100">Registar</button>
        <div class="text-center mt-3">
          <p class="mb-0">Já tem uma conta? <a href="../login.php" class="text-decoration-none">Inicie Sessão</a></p>
        </div>
      </form>
    </div>
  </div>
  <script>
    document.getElementById("togglePassword").addEventListener("click", function() {
      let passwordField = document.getElementById("password");
      let icon = this;
      if (passwordField.type === "password") {
        passwordField.type = "text";
        icon.classList.replace("bi-eye-fill", "bi-eye-slash-fill");
      } else {
        passwordField.type = "password";
        icon.classList.replace("bi-eye-slash-fill", "bi-eye-fill");
      }
    });

    document.getElementById("toggleConfirmPassword").addEventListener("click", function() {
      let confirmPasswordField = document.getElementById("confirm-password");
      let icon = this;
      if (confirmPasswordField.type === "password") {
        confirmPasswordField.type = "text";
        icon.classList.replace("bi-eye-fill", "bi-eye-slash-fill");
      } else {
        confirmPasswordField.type = "password";
        icon.classList.replace("bi-eye-slash-fill", "bi-eye-fill");
      }
    });
  </script>
</body>

</html>