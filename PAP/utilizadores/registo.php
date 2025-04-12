
<?php

require '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Captura os dados do formulário
  $nome = trim($_POST["nome"]);
  $email = trim($_POST["email"]);
  $contacto = trim($_POST["contacto"]);
  $morada = trim($_POST["morada"]);
  $codigo_postal = trim($_POST["codigopostal"]);
  $localidade = trim($_POST["localidade"]);
  $password = trim($_POST["password"]);

  // Verifica se todos os campos estão preenchidos
  if (!empty($nome) && !empty($email) > 0) {
    try {
      // Conecta ao banco de dados
      $link = conect_db('');

      $param_password = password_hash($password, PASSWORD_DEFAULT);


      $sql = "INSERT INTO utilizador (nome, email, contacto, morada, codigo_postal, localidade, password, perfil) VALUES (:nome, :email, :contacto, :morada, :codigo_postal, :localidade, :password, :perfil)";
      $stmt = $link->prepare($sql);
      $data = [
        ":nome" => $nome,
        ":email" => $email,
        ":contacto" => $contacto,
        ":morada" => $morada,
        ":localidade" => $localidade,
        ":codigo_postal" => $codigo_postal,
        ":password" => $param_password,
        ":perfil" => 2
      ];

      if ($stmt->execute($data)) {
        // Atualiza com a foto de perfil padrão
        $ultimo_id = $link->lastInsertId(); // Usando $link em vez de $conn
        $sql_foto = "UPDATE utilizador SET foto_perfil = :foto WHERE utilizador_id = :id";
        $stmt_foto = $link->prepare($sql_foto);
        $stmt_foto->bindValue(':foto', 'default.jpg');
        $stmt_foto->bindValue(':id', $ultimo_id);

        if ($stmt_foto->execute()) {
          $_SESSION['success_msg'] = "Registo concluído com sucesso!";
          header("location: dashboard_user.php");
          exit;
        } else {
          throw new PDOException("Erro ao definir foto padrão");
        }
      } else {
        echo "Erro ao adicionar os dados.";
      }
    } catch (PDOException $e) {
      if ($e->getCode() == 23000) {
        $erro = "Esse email já está registado!";
        header("Location: form_registo.php?erro=" . urlencode($erro));
        exit;
      } else {
        header("Location: form_registo.php?erro=" . urlencode("Erro: " . $e->getMessage()));
        exit;
      }
    }
  } else {
    header("Location: form_registo.php?erro=" . urlencode("Preencha todos os campos obrigatórios"));
    exit;
  }
}
unset($link);
?>