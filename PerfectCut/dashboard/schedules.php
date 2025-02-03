<?php
ob_start();
require_once '../config/database.php';
$database = new Database();
$conn = $database->getConnection();
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Configure error handling for production
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log');

// Define status texts
$statusTexts = [
    'scheduled' => 'Agendado',
    'completed' => 'Concluído',
    'cancelled' => 'Cancelado'
];

// Fetch schedules with service information
try {
    $query = "SELECT s.*, srv.name as service_name, srv.price 
              FROM schedules s 
              LEFT JOIN services srv ON s.service_id = srv.id 
              ORDER BY s.date DESC, s.time ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching schedules: " . $e->getMessage());
    $schedules = [];
}

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfect Cut - Agendamentos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .table-container {
            overflow-x: auto;
            margin: 1rem 0;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }
        
        .schedule-table {
            width: 100%;
            min-width: 900px;
            background: white;
            border-collapse: separate;
            border-spacing: 0;
        }

        .schedule-table th {
            background: #f9fafb;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e5e7eb;
        }

        .schedule-table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #e5e7eb;
            color: #1f2937;
        }

        .schedule-table tr:last-child td {
            border-bottom: none;
        }

        .schedule-table tr:hover {
            background-color: #f9fafb;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            text-align: center;
            display: inline-block;
            min-width: 100px;
        }

        .status-scheduled {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-completed {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .action-buttons {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-start;
        }

        .action-button {
            padding: 0.5rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }

        .edit-button {
            color: #2563eb;
        }

        .delete-button {
            color: #dc2626;
        }

        .edit-button:hover {
            background-color: #dbeafe;
        }

        .delete-button:hover {
            background-color: #fee2e2;
        }

        .price-column {
            text-align: right;
            font-family: monospace;
            font-size: 0.975rem;
        }

        .toggle-btn {
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1000;
            padding: 0.5rem;
            background-color: #ffffff;
            border-radius: 0.375rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .toggle-btn.active {
            left: 260px;
        }

        .side-menu {
            position: fixed;
            top: 0;
            left: -250px;
            height: 100vh;
            width: 250px;
            background-color: #1f2937;
            transition: all 0.3s ease;
            z-index: 999;
        }

        .side-menu.active {
            left: 0;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 998;
        }

        .overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .content-wrapper {
            transition: all 0.3s ease;
            margin-left: 0;
        }

        .content-wrapper.shifted {
            margin-left: 250px;
        }

        @media (max-width: 768px) {
            .content-wrapper.shifted {
                margin-left: 0;
            }
        }

        /* adiciona uma margem superior ao elemento que contém o título */
        .container.mx-auto.px-4.py-8 {
            margin-top: 7rem; /* ajuste a altura para o seu gosto */
        }

        .flex.justify-between.items-center.mb-6 {
            justify-content: center;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Botão Toggle -->
    <button class="toggle-btn">
        <i class="fas fa-bars text-gray-600 text-xl"></i>
    </button>

    <!-- Overlay -->
    <div class="overlay"></div>

    <!-- Sidebar -->
    <nav class="side-menu">
        <div class="p-4">
            <button class="close-btn text-white float-right">
                <i class="fas fa-times"></i>
            </button>
            <div class="clear-both"></div>
        </div>
        <div class="px-4 py-2">
            <h2 class="text-white text-lg font-semibold mb-4">Menu</h2>
            <ul class="space-y-2">
                <li>
                    <a href="schedules.php" class="text-gray-300 hover:text-white block py-2">
                        <i class="fas fa-calendar-alt w-6"></i>
                        Agendamentos
                    </a>
                </li>
                <li>
                    <a href="staff.php" class="text-gray-300 hover:text-white block py-2">
                        <i class="fas fa-users w-6"></i>
                        Funcionários
                    </a>
                </li>
                <li>
                    <a href="reports.php" class="text-gray-300 hover:text-white block py-2">
                        <i class="fas fa-envelope w-6"></i>
                        Relatórios
                    </a>
                </li>
                <li>
                    <a href="logout.php" class="text-gray-300 hover:text-white block py-2">
                        <i class="fas fa-sign-out-alt w-6"></i>
                        Sair
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Conteúdo Principal -->
    <div class="content-wrapper">
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Agendamentos</h1>
            </div>

            <div class="table-container">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Contacto</th>
                            <th>Serviço</th>
                            <th>Data e Hora</th>
                            <th>Preço</th>
                            <th>Estado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($schedules)): ?>
                            <?php foreach ($schedules as $schedule): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($schedule['name']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['service_name']); ?></td>
                                    <td>
                                        <?php 
                                            $date = new DateTime($schedule['date'] . ' ' . $schedule['time']);
                                            echo $date->format('d/m/Y H:i'); 
                                        ?>
                                    </td>
                                    <td class="price-column">€<?php echo number_format($schedule['price'], 2); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $schedule['status']; ?>">
                                            <?php echo $statusTexts[$schedule['status']] ?? ucfirst($schedule['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_schedule.php?id=<?php echo $schedule['id']; ?>" 
                                               class="action-button edit-button" 
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_schedule.php?id=<?php echo $schedule['id']; ?>" 
                                               onclick="return confirm('Tem certeza que deseja eliminar esta marcação?');"
                                               class="action-button delete-button" 
                                               title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-gray-500">
                                    Nenhum agendamento encontrado
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.querySelector('.toggle-btn');
            const sidebar = document.querySelector('.side-menu');
            const overlay = document.querySelector('.overlay');
            const content = document.querySelector('.content-wrapper');
            const closeBtn = document.querySelector('.close-btn');

            function toggleMenu() {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
                toggleBtn.classList.toggle('active');
                content.classList.toggle('shifted');
            }

            if (toggleBtn) toggleBtn.addEventListener('click', toggleMenu);
            if (overlay) overlay.addEventListener('click', toggleMenu);
            if (closeBtn) closeBtn.addEventListener('click', toggleMenu);

            // Fechar menu no resize da janela se estiver em visualização mobile
            window.addEventListener('resize', function() {
                if (window.innerWidth <= 768 && sidebar.classList.contains('active')) {
                    toggleMenu();
                }
            });
        });
    </script>
</body>
</html>