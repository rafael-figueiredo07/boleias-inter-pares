<?php
include '../db.php';

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $conn = conect_db(); // Conecta ao banco de dados

        // Captura os valores do formulário
        $ponto_partida = $_POST['partida'];
        $ponto_intermedio = !empty($_POST['ponto_intermedio']) ? $_POST['ponto_intermedio'] : NULL;
        $destino = $_POST['destino'];
        $total_pessoas = $_POST['pessoas'];
        $data = $_POST['data'];
        $horario = $_POST['horario'];

        // Preparar a consulta SQL
        $sql = "INSERT INTO boleia (ponto_partida, ponto_intermedio, destino, total_pessoas, data, horario)
                VALUES (:ponto_partida, :ponto_intermedio, :destino, :total_pessoas, :data, :horario)";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':ponto_partida', $ponto_partida, PDO::PARAM_STR);
        $stmt->bindParam(':ponto_intermedio', $ponto_intermedio, PDO::PARAM_STR);
        $stmt->bindParam(':destino', $destino, PDO::PARAM_STR);
        $stmt->bindParam(':total_pessoas', $total_pessoas, PDO::PARAM_INT);
        $stmt->bindParam(':data', $data, PDO::PARAM_STR);
        $stmt->bindParam(':horario', $horario, PDO::PARAM_STR);

        // Executa a consulta
        if ($stmt->execute()) {
            header("Location: dashboard_user.php?sucesso=1");
            exit();
        } else {
            echo "Erro ao inserir os dados.";
        }

    } catch (PDOException $e) {
        exit("Erro na base de dados: " . $e->getMessage());
    }
} else {
    echo "Método inválido.";
}
?>