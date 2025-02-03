<?php
require_once '../config/database.php';

// Instanciar a classe Database e obter a conexão
$database = new Database();
$conn = $database->getConnection();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Garantir que o ID seja um número inteiro

    $query = "DELETE FROM schedules WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        header("Location: schedules.php?message=Marcação excluída com sucesso");
        exit;
    } else {
        echo "Erro ao excluir a marcação.";
    }
} else {
    echo "ID da marcação não fornecido.";
}
?>


