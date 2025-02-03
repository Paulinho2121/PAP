<?php
session_start();
include_once './config/database.php';
// Conexão com o banco de dados
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $assunto = $_POST['subject'] ?? '';
    $mensagem = $_POST['message'] ?? '';


    // Preparando a query SQL
    $query = "INSERT INTO contatos (nome, email, assunto, mensagem) VALUES (:nome, :email, :assunto, :mensagem)";
    $stmt = $db->prepare($query);

    // Vinculando os parâmetros
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':assunto', $assunto);
    $stmt->bindParam(':mensagem', $mensagem);

    // Executando a query
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Mensagem enviada com sucesso!";
    } else {
        $_SESSION['error_message'] = "Erro ao enviar mensagem. Tente novamente.";
    }
} else {
    $_SESSION['error_message'] = "Método de requisição inválido.";
}

header("Location: contact.php");
exit();
?>