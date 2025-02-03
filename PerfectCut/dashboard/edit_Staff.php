<?php
require '../config/database.php'; // Inclui o arquivo de conexão

// Instanciar a classe Database e obter a conexão
$database = new Database();
$conn = $database->getConnection();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Obter os detalhes do funcionário
    $query = "SELECT * FROM users WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$staff) {
        die("Funcionário não encontrado.");
    }
} else {
    die("ID do funcionário não fornecido.");
}

// Atualizar os dados após o envio do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $role = $_POST['role'];

    $update_query = "UPDATE users SET name = :name, role = :role WHERE id = :id";
    $stmt = $conn->prepare($update_query);
    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':role', $role);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        header("Location: staff.php?message=Funcionário atualizado com sucesso");
        exit;
    } else {
        echo "Erro ao atualizar o funcionário.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Funcionário</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow">
        <h1 class="text-xl font-bold mb-4">Editar Funcionário</h1>
        <form method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Nome do Funcionário</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($staff['name']); ?>" required class="block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Função</label>
                <select name="role" required class="block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="barbeiro" <?php echo ($staff['role'] == 'barbeiro') ? 'selected' : ''; ?>>Barbeiro</option>
                    <option value="administrador" <?php echo ($staff['role'] == 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                </select>
            </div>
            <div class="flex justify-end">
                <a href="staff.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 mr-3">Cancelar</a>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Salvar</button>
            </div>
        </form>
    </div>
</body>
</html>