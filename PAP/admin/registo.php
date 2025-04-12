
<?php

require '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Captura os dados do formulário
  $nome = trim($_POST["nome"]);
  $email = trim($_POST["email"]);
  $password = trim($_POST["password"]);

  // Verifica se todos os campos estão preenchidos
  if (!empty($nome) && !empty($email) > 0) {
    try {
      // Conecta ao banco de dados
      $link = conect_db('');

      $param_password = password_hash($password, PASSWORD_DEFAULT);


      $sql = "INSERT INTO admin (nome, email, password, perfil) VALUES (:nome, :email, :password, :perfil)";
      $stmt = $link->prepare($sql);
      $data = [
        ":nome" => $nome,
        ":email" => $email,
        ":password" => $param_password,
        ":perfil" => 1

      ];
      if ($stmt->execute($data)) {
        echo "Dados inseridos com sucesso!";
        header("location: dashboard_admin.php");
      } else {
        echo "Erro ao adicionar a dados.";
      }
    } catch (PDOException $e) {
      if ($e->getCode() == 23000) { // Código de erro para violação de UNIQUE
        $erro = "Esse email já está registado!";
        header("Location: form_registo.php?erro=" . urlencode($erro));
        exit; // Impede a execução do restante do código

      } else {
        header("Location: form_registo.php?erro=" . urlencode("Erro: " . $e->getMessage()));
        exit;
      }
    }
  }
}
unset($link);
?>