<?php
ob_start();
session_start();
require_once './config/database.php';
include './includes/header.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

class BookingSystem {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    private function validateInput($data) {
        $sanitized = [];
        $sanitized['name'] = htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8');
        $sanitized['email'] = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $sanitized['phone'] = preg_replace('/[^0-9]/', '', $data['phone']);

        if (!preg_match('/^9[1236]\d{7}$/', $sanitized['phone'])) {
            throw new Exception("O telefone deve ser um número válido de Portugal (9 dígitos começando com 91, 92, 93 ou 96).");
        }

        $sanitized['service_id'] = filter_var($data['service'], FILTER_VALIDATE_INT);
        $sanitized['date'] = $data['date'];
        $sanitized['time'] = $data['time'];
        $sanitized['status'] = 'scheduled';

        return $sanitized;
    }

    public function checkAvailability($date, $time) {
        $query = "SELECT COUNT(*) FROM schedules WHERE date = :date AND time = :time AND status != 'cancelado'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':time', $time);
        $stmt->execute();
        return $stmt->fetchColumn() == 0;
    }

    public function isDateFullyBooked($date) {
        $query = "SELECT COUNT(*) as total_slots FROM horarios_barbearia";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $totalSlots = $stmt->fetchColumn();

        $query = "SELECT COUNT(*) as booked_slots FROM schedules 
                  WHERE date = :date AND status IN ('scheduled', 'completed')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        $bookedSlots = $stmt->fetchColumn();

        return $bookedSlots >= $totalSlots;
    }

    public function getBookedDates() {
        $query = "SELECT date FROM schedules 
                  GROUP BY date 
                  HAVING COUNT(*) >= (SELECT COUNT(*) FROM horarios_barbearia)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function bookAppointment($data) {
        $input = $this->validateInput($data);

        if (!$this->checkAvailability($input['date'], $input['time'])) {
            throw new Exception("Este horário já foi reservado.");
        }

        if ($this->isDateFullyBooked($input['date'])) {
            throw new Exception("Esta data já está completamente reservada.");
        }

        $query = "INSERT INTO schedules (name, email, phone, service_id, date, time, status) 
                  VALUES (:name, :email, :phone, :service_id, :date, :time, :status)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($input);
    }

    public function getAvailableTimes($date) {
        try {
            $query = "SELECT horario 
                    FROM horarios_barbearia 
                    WHERE horario NOT IN (
                        SELECT time FROM schedules WHERE date = :date AND status != 'cancelado'
                    )";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':date', $date);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Erro ao buscar horários: " . $e->getMessage());
            return [];
        }
    }

    public function getServices() {
        $query = "SELECT id, name, price, duration FROM services";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

ob_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingSystem = new BookingSystem();
    try {
        if ($bookingSystem->bookAppointment($_POST)) {
            $_SESSION['success_message'] = "Agendamento realizado com sucesso!";
            header("Location: schedule.php");
            exit();
        } else {
            throw new Exception("Erro ao realizar o agendamento.");
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: schedule.php");
        exit();
    }
}

$bookingSystem = new BookingSystem();
$services = $bookingSystem->getServices();
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$availableTimes = $bookingSystem->getAvailableTimes($selectedDate);
$bookedDates = $bookingSystem->getBookedDates();

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento de Corte</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="./css/schedule.css">
    <link rel="stylesheet" href="./css/footer.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .fc-day-fully-booked {
            background-color: red !important;
            color: white !important;
            cursor: not-allowed !important;
        }

    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <?php if (isset($_SESSION['success_message'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Sucesso!',
                        text: "<?= htmlspecialchars($_SESSION['success_message']) ?>",
                        icon: 'success',
                        confirmButtonText: 'Fechar'
                    });
                });
            </script>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        title: 'Erro!',
                        text: "<?= htmlspecialchars($_SESSION['error_message']) ?>",
                        icon: 'error',
                        confirmButtonText: 'Fechar'
                    });
                });
            </script>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="grid md:grid-cols-2 gap-8">
            <div>
                <div id="calendar"></div>
            </div>

            <form id="bookingForm" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="date">
                        Data
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 bg-gray-200" 
                           id="date" name="date" type="text" readonly required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                        Primeiro e Último nome
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" 
                           id="name" name="name" type="text" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        E-mail
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" 
                           id="email" name="email" type="email" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">
                        Telefone
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" 
                           id="phone" name="phone" type="tel" pattern="9[1236]\d{7}" 
                           maxlength="9" minlength="9" placeholder="Ex: 912345678" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="service">
                        Serviço
                    </label>
                    <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" 
                            id="service" name="service" required>
                        <option value="">Selecione um serviço</option>
                        <?php foreach ($services as $service): ?>
                            <option value="<?= $service['id'] ?>">
                                <?= htmlspecialchars($service['name']) ?> 
                                (€ <?= number_format($service['price'], 2) ?>, 
                                <?= $service['duration'] ?> min)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="time">
                        Horário
                    </label>
                    <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" 
                            id="time" name="time" required>
                        <option value="">Selecione um horário</option>
                        <?php foreach ($availableTimes as $time): ?>
                            <option value="<?= $time ?>"><?= $time ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" 
                            type="submit">
                        Agendar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const dateInput = document.getElementById('date');
    const timeSelect = document.getElementById('time');

    const bookedDates = <?php echo json_encode($bookedDates); ?>;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'pt-br',
        selectable: true,
        dateClick: function(info) {
            const clickedDate = info.dateStr;
            const today = new Date().toISOString().split('T')[0];

            if (clickedDate < today || bookedDates.includes(clickedDate)) {
                return;
            }

            dateInput.value = clickedDate;

            axios.get(`get_available_times.php?date=${clickedDate}`)
                .then(response => {
                    timeSelect.innerHTML = '<option value="">Selecione um horário</option>';
                    response.data.forEach(time => {
                        const option = document.createElement('option');
                        option.value = time;
                        option.textContent = time;
                        timeSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Erro ao buscar horários:', error));
        },
        dayCellDidMount: function(info) {
            const today = new Date().toISOString().split('T')[0];
            if (info.date < new Date(today) || bookedDates.includes(info.dateStr)) {
                info.el.classList.add('fc-day-fully-booked');
            }
        }
    });
    calendar.render();
});
    </script>
</body>
</html>

<?php
include './includes/footer.php';
?>