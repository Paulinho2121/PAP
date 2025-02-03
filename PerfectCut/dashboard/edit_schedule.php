<?php
require_once '../config/database.php';

// Instanciar a classe Database e obter a conexão
$database = new Database();
$conn = $database->getConnection();


if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Obter os detalhes da marcação com JOIN
    $query = "SELECT schedules.*, services.price, services.name AS service_name 
              FROM schedules 
              JOIN services ON schedules.service_id = services.id 
              WHERE schedules.id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$schedule) {
        die("Marcação não encontrada.");
    }
} else {
    die("ID da marcação não fornecido.");
}

// Atualizar os dados após o envio do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $service_id = $_POST['service_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $status = isset($_POST['status']) ? $_POST['status'] : null;

    if (!$status) {
        die("Erro: O campo 'status' é obrigatório.");
    }

    $update_query = "UPDATE schedules 
                     SET name = :name, service_id = :service_id, date = :date, time = :time, status = :status 
                     WHERE id = :id";
    $stmt = $conn->prepare($update_query);

    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':service_id', $service_id, PDO::PARAM_INT);
    $stmt->bindValue(':date', $date);
    $stmt->bindValue(':time', $time);
    $stmt->bindValue(':status', $status);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        header("Location: schedules.php?message=Marcação atualizada com sucesso");
        exit;
    } else {
        echo "Erro ao atualizar a marcação.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Marcação</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow">
        <h1 class="text-xl font-bold mb-4">Editar Marcação</h1>
        <form method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Nome do Cliente</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($schedule['name']); ?>" required class="block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Serviço</label>
                <select name="service_id" required class="block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="1" <?php echo $schedule['service_id'] == 1 ? 'selected' : ''; ?>>Corte de Cabelo</option>
                    <option value="2" <?php echo $schedule['service_id'] == 2 ? 'selected' : ''; ?>>Barba</option>
                    <option value="3" <?php echo $schedule['service_id'] == 3 ? 'selected' : ''; ?>>Corte + Barba</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Data</label>
                <input type="date" name="date" value="<?php echo htmlspecialchars($schedule['date']); ?>" required class="block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Hora</label>
                <input type="time" name="time" value="<?php echo htmlspecialchars($schedule['time']); ?>" required class="block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Estado</label>
                <select name="status" required class="block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="scheduled" <?php echo $schedule['status'] == 'scheduled' ? 'selected' : ''; ?>>Agendado</option>
                    <option value="completed" <?php echo $schedule['status'] == 'completed' ? 'selected' : ''; ?>>Concluído</option>
                    <option value="cancelled" <?php echo $schedule['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>
            <div class="flex justify-end">
                <a href="schedules.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 mr-3">Cancelar</a>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Salvar</button>
            </div>
        </form>
    </div>
</body>
</html>
