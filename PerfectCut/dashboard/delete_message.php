<?php
session_start();
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $query = "DELETE FROM contatos WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $id]);
        
        $_SESSION['success_message'] = "Mensagem eliminada com sucesso!";
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Erro ao eliminar a mensagem.";
    }
}

header('Location: reports.php');
exit();
?>