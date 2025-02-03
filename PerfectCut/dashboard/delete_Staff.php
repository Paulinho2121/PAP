<?php
require_once '../config/database.php';
$database = new Database();
$conn = $database->getConnection();
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $staff_id = intval($_GET['id']);

    try {
        // Prepare and execute delete query
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $staff_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            // Set success message
            $_SESSION['success_message'] = "Funcionário eliminado com sucesso.";
        } else {
            // Set error message
            $_SESSION['error_message'] = "Erro ao eliminar o funcionário.";
        }
    } catch (PDOException $e) {
        // Log error and set error message
        error_log("Erro ao eliminar funcionário: " . $e->getMessage());
        $_SESSION['error_message'] = "Erro ao processar a eliminação.";
    }

    // Redirect back to staff page
    header("Location: staff.php");
    exit;
} else {
    // If no ID provided, redirect with error
    $_SESSION['error_message'] = "ID de funcionário inválido.";
    header("Location: staff.php");
    exit;
}
?>