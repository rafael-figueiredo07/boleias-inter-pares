<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registo Admin | Boleias Inter Pares</title>
  <link rel="icon" href="../public/imagens/LOGO.png">
  <link rel="stylesheet" href="../public/css/bootstrap.min.css">
</head>

<body>
  <div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow-sm" style="max-width: 500px; width: 100%;">
      <div class="d-flex align-items-center">
        <a href="index.php" style="margin-right: 125px; margin-bottom: 15px;">
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
        <div class="mb-3">
          <label for="nome" class="form-label">Nome</label>
          <input type="text" class="form-control" id="nome" name="nome" placeholder="Insira o seu nome" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Insira o seu email" required>
        </div>
        <div class="mb-3 row">
          <div class="col">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Crie uma password" required>
          </div>
          <div class="col">
            <label for="confirm-password" class="form-label">Confirmar Password</label>
            <input type="password" class="form-control" id="confirm-password" name="confirm-password" placeholder="Confirme a password" required>
          </div>
        </div>
        <button type="submit" class="btn btn-success w-100">Registar</button>
      </form>
      <div class="text-center mt-3">
          <p class="mb-0">Já tem uma conta? <a href="../login.php" class="text-decoration-none">Inicie Sessão</a></p>
      </div>
    </div>
  </div>
</body>

</html>