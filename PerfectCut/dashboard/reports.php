<?php
session_start();
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfect Cut - Relatórios</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .side-menu {
            width: 250px;
            height: 100vh;
            background: #2D3748;
            position: fixed;
            top: 0;
            left: -250px;
            transition: left 0.3s;
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
            background: rgba(0, 0, 0, 0.5);
            display: none;
        }
        .overlay.active {
            display: block;
        }
        .toggle-btn {
            position: absolute;
            left: 20px;
            top: 20px;
            background: none;
            border: none;
            cursor: pointer;
        }
        .content-wrapper {
            margin-left: 0;
            transition: margin-left 0.3s;
        }
        .content-wrapper.shifted {
            margin-left: 250px;
        }
        .table-container {
            overflow-x: auto;
        }
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
        }
        .schedule-table th, .schedule-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .schedule-table th {
            background-color: #f4f4f4;
        }
        .truncate-text {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .bg-yellow-100 {
            background-color: #FEF3C7;
            color: #B45309;
        }
        .bg-green-100 {
            background-color: #D1FAE5;
            color: #065F46;
        }
        .action-buttons a {
            margin-right: 8px;
            text-decoration: none;
            padding: 5px;
            border-radius: 4px;
        }
        .edit-button {
            color: #1D4ED8;
        }
        .delete-button {
            color: #DC2626;
        }
    </style>
</head>
<body class="bg-gray-100">
    <button class="toggle-btn">
        <i class="fas fa-bars text-gray-600 text-xl"></i>
    </button>

    <div class="overlay"></div>

    <nav class="side-menu">
        <div class="p-4">
            <button class="close-btn text-white float-right">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="px-4 py-2">
            <h2 class="text-white text-lg font-semibold mb-4">Menu</h2>
            <ul class="space-y-2">
                <li><a href="schedules.php" class="text-gray-300 hover:text-white block py-2"><i class="fas fa-calendar-alt w-6"></i> Agendamentos</a></li>
                <li><a href="staff.php" class="text-gray-300 hover:text-white block py-2"><i class="fas fa-users w-6"></i> Funcionários</a></li>
                <li><a href="reports.php" class="text-gray-300 hover:text-white block py-2"><i class="fas fa-envelope w-6"></i> Relatórios</a></li>
                <li><a href="logout.php" class="text-gray-300 hover:text-white block py-2"><i class="fas fa-sign-out-alt w-6"></i> Sair</a></li>
            </ul>
        </div>
    </nav>

    <div class="content-wrapper">
        <body class="bg-gray-100">
            <div class="container mx-auto px-4 py-8">
                <h1 class="text-2xl font-bold text-gray-900 text-center">Relatórios de Sugestões</h1>
                <div class="table-container">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Assunto</th>
                            <th>Mensagem</th>
                            <th>Data</th>
                            <th>Estado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM contatos ORDER BY data_envio DESC";
                        $stmt = $conn->prepare($query);
                        $stmt->execute();
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $statusClass = $row['estado'] === 'não lido' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800';
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['assunto']); ?></td>
                                <td class="truncate-text"><?php echo htmlspecialchars($row['mensagem']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['data_envio'])); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($row['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="mark_as_read.php?id=<?php echo $row['id']; ?>" class="edit-button" title="Marcar como Lido"><i class="fas fa-check"></i></a>
                                        <a href="delete_message.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Tem certeza que deseja eliminar esta mensagem?');" class="delete-button" title="Eliminar"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
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

            toggleBtn.addEventListener('click', toggleMenu);
            overlay.addEventListener('click', toggleMenu);
            closeBtn.addEventListener('click', toggleMenu);
        });
    </script>
</body>
</html>
