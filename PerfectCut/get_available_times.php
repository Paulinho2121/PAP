<?php
require_once './config/database.php';


class AvailableTimesService {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAvailableTimes($date) {
        try {
            // Busca horários já agendados nesta data
            $bookedQuery = "SELECT time FROM schedules WHERE date = :date AND status != 'cancelado'";
            $bookedStmt = $this->conn->prepare($bookedQuery);
            $bookedStmt->bindParam(':date', $date);
            $bookedStmt->execute();
            $bookedTimes = $bookedStmt->fetchAll(PDO::FETCH_COLUMN);

            // Busca todos os horários disponíveis da tabela
            $availableQuery = "SELECT horario FROM horarios_barbearia 
                             WHERE horario NOT IN (
                                 SELECT time FROM schedules 
                                 WHERE date = :date AND status != 'cancelado'
                             )
                             ORDER BY horario";
            $availableStmt = $this->conn->prepare($availableQuery);
            $availableStmt->bindParam(':date', $date);
            $availableStmt->execute();
            $availableTimes = $availableStmt->fetchAll(PDO::FETCH_COLUMN);

            return $availableTimes ? $availableTimes : [];

        } catch (PDOException $e) {
            error_log("Erro ao buscar horários: " . $e->getMessage());
            return [];
        }
    }
}

// Handle AJAX request
header('Content-Type: application/json');

if (!isset($_GET['date'])) {
    echo json_encode([]);
    exit();
}

try {
    $service = new AvailableTimesService();
    $availableTimes = $service->getAvailableTimes($_GET['date']);
    echo json_encode($availableTimes);
} catch (Exception $e) {
    error_log("Erro no serviço de horários: " . $e->getMessage());
    echo json_encode([]);
}
exit();
?>