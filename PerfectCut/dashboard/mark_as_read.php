<?php
session_start();
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $query = "UPDATE contatos SET estado = 'lido' WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $id]);
        
        $_SESSION['success_message'] = "Mensagem marcada como lida com sucesso!";
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Erro ao atualizar o estado da mensagem.";
    }
}

header('Location: reports.php');
exit();
?>