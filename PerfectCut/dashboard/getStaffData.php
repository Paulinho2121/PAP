<?php
require '../config/database.php'; // Inclui o arquivo de conexão

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = intval($_GET['id']); // Sanitiza o ID

    // Instancia a classe Database
    $db = new Database();
    $conn = $db->getConnection();

    try {
        // Consulta SQL
        $query = "SELECT id, name, role FROM users WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Funcionário não encontrado.']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Erro no servidor: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['message' => 'Requisição inválida.']);
}
?>
