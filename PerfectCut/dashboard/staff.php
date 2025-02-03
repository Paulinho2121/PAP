<?php

// Inclui a classe Database

require_once '../config/database.php';
$database = new Database(); // Instanciar a classe Database
$conn = $database->getConnection(); // Obter a conexão ao banco de dados

// Inicia a sessão
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Buscar todos os usuários
$query = "SELECT * FROM users ORDER BY created_at DESC";
$stmt = $conn->query($query);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar total de barbeiros e admins
$query_count = "SELECT 
    SUM(CASE WHEN role = 'Administrador' THEN 1 ELSE 0 END) as admin_count,
    SUM(CASE WHEN role = 'Barbeiro' THEN 1 ELSE 0 END) as barber_count
    FROM users";
$stmt_count = $conn->query($query_count);
$counts = $stmt_count->fetch(PDO::FETCH_ASSOC);

// Processar adição de novo staff
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_staff'])) {
    // Validar se todos os campos necessários estão preenchidos
    $required_fields = ['username', 'password', 'name', 'role', 'email'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        $_SESSION['error_message'] = "Os seguintes campos são obrigatórios: " . implode(', ', $missing_fields);
        header("Location: staff.php");
        exit;
    }
    
    // Se passou pela validação, pegar os valores dos campos
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $name = trim($_POST['name']);
    $role = trim($_POST['role']);
    $email = trim($_POST['email']);
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Por favor, insira um email válido.";
        header("Location: staff.php");
        exit;
    }
    
    // Hash da senha
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Query de inserção
    $insert_query = "INSERT INTO users (username, password, name, role, email, status, created_at, updated_at) 
                     VALUES (:username, :password, :name, :role, :email, 'active', NOW(), NOW())";
    
    $stmt = $conn->prepare($insert_query);
    
    try {
        $result = $stmt->execute([
            ':username' => $username,
            ':password' => $hashed_password,
            ':name' => $name,
            ':role' => $role,
            ':email' => $email
        ]);
        
        if ($result) {
            $_SESSION['success_message'] = "Funcionário adicionado com sucesso!";
        } else {
            $_SESSION['error_message'] = "Erro ao adicionar funcionário. Por favor, tente novamente.";
        }
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            if (strpos($e->getMessage(), 'username')) {
                $_SESSION['error_message'] = "Este nome de usuário já está em uso.";
            } elseif (strpos($e->getMessage(), 'email')) {
                $_SESSION['error_message'] = "Este email já está em uso.";
            } else {
                $_SESSION['error_message'] = "Erro ao adicionar funcionário: " . $e->getMessage();
            }
        } else {
            $_SESSION['error_message'] = "Erro ao adicionar funcionário: " . $e->getMessage();
        }
    }
    
    header("Location: staff.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfect Cut - Funcionários</title>
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

        .container.mx-auto.px-4.py-8 {
            margin-top: 7rem;
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
            <h1 class="text-2xl font-bold text-gray-900">Gestão de Funcionários</h1>
        </div>
        <div class="mb-4"></div> <!-- Add this line to create spacing between titles -->
        <div class="flex justify-between items-center mb-6">
            <button onclick="document.getElementById('addStaffModal').style.display='block'" 
                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-plus mr-2"></i>Adicionar Funcionário
            </button>
        </div>

            <!-- Add Modal for Staff Form -->
            <div id="addStaffModal" class="fixed z-50 inset-0 overflow-y-auto hidden" style="display:none;">
                <div class="flex items-center justify-center min-height-100vh pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity">
                        <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
                    </div>
                    <div class="inline-block align-center bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" role="dialog"                             aria-modal="true">
                        <form method="POST" action="staff.php" class="p-6">
                            <h2 class="text-xl font-bold mb-4">Adicionar Novo Funcionário</h2>
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="username">Utilizador</label>
                                <input required type="text" name="username" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Nome(Primeiro e ultimo)</label>
                                <input required type="text" name="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Palavra Pass</label>
                                <input required type="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                                <input required type="email" name="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="role">Função</label>
                                <select required name="role" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                    <option value="Barber">Barbeiro</option>
                                    <option value="Administrator">Administrador</option>
                                </select>
                            </div>

                            <div class="flex items-center justify-between">
                                <input type="submit" name="add_staff" value="Adicionar" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                <button type="button" onclick="document.getElementById('addStaffModal').style.display='none'" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <!-- Staff Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Barbers Count -->
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-cut text-blue-500 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">Barbeiros</h3>
                        <p class="text-2xl font-bold"><?php echo $counts['barber_count']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Admins Count -->
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <i class="fas fa-user-shield text-purple-500 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">Administradores</h3>
                        <p class="text-2xl font-bold"><?php echo $counts['admin_count']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff List -->
        <div class="table-container">
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Utilizador</th>
                        <th>Nome</th>
                        <th>Função</th>
                        <th>Criado em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT username, name, role, created_at, id FROM users ORDER BY created_at DESC";
                    $stmt = $conn->prepare($query);
                    $stmt->execute();

                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['role']); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="edit_Staff.php?id=<?php echo $row['id']; ?>" 
                                       class="action-button edit-button" 
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_Staff.php?id=<?php echo $row['id']; ?>" 
                                       onclick="return confirm('Tem certeza?');"
                                       class="action-button delete-button" 
                                       title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
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

            window.addEventListener('resize', function() {
                if (window.innerWidth <= 768 && sidebar.classList.contains('active')) {
                    toggleMenu();
                }
            });
        });
    </script>
</body>
</html>