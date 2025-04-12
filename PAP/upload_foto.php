<?php
session_start();
require 'db.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_perfil'])) {
    $utilizador_id = $_SESSION['utilizador_id'];
    $pasta_upload = 'public/imagens/perfis/';
    
    // Verifica se a pasta existe, se não, cria
    if (!file_exists($pasta_upload)) {
        mkdir($pasta_upload, 0777, true);
    }

    $extensao = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
    $nome_arquivo = 'perfil_' . $utilizador_id . '_' . time() . '.' . $extensao;
    $caminho_completo = $pasta_upload . $nome_arquivo;

    // Validação da imagem
    $tipo_permitido = ['image/jpeg', 'image/png', 'image/gif'];
    if (in_array($_FILES['foto_perfil']['type'], $tipo_permitido)) {
        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $caminho_completo)) {
            // Atualiza no banco de dados
            try {
                $conn = conect_db();
                $sql = "UPDATE utilizador SET foto_perfil = :foto WHERE utilizador_id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':foto', $nome_arquivo);
                $stmt->bindParam(':id', $utilizador_id);
                
                if ($stmt->execute()) {
                    $_SESSION['success_msg'] = "Foto de perfil atualizada com sucesso!";
                } else {
                    $_SESSION['error_msg'] = "Erro ao atualizar no banco de dados";
                }
            } catch (PDOException $e) {
                $_SESSION['error_msg'] = "Erro no banco de dados: " . $e->getMessage();
            }
        } else {
            $_SESSION['error_msg'] = "Erro ao mover o arquivo";
        }
    } else {
        $_SESSION['error_msg'] = "Formato de arquivo inválido. Use JPEG, PNG ou GIF";
    }
    
    header("Location: perfil.php");
    exit;
}
?>