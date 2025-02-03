<?php
session_start();
require_once('../config/database.php');

try {
    $database = new Database();
    $conn = $database->getConnection();
} catch (Exception $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// Função para limpar inputs
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Função para registrar tentativa de login
function logLoginAttempt($conn, $user_id, $status = 'failed') {
    $login_time = date('Y-m-d H:i:s');
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $log_query = "INSERT INTO login_logs (user_id, login_time, ip_address, status) 
                  VALUES (:user_id, :login_time, :ip_address, :status)";
    $log_stmt = $conn->prepare($log_query);
    $log_stmt->bindParam(":user_id", $user_id);
    $log_stmt->bindParam(":login_time", $login_time);
    $log_stmt->bindParam(":ip_address", $ip_address);
    $log_stmt->bindParam(":status", $status);
    $log_stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = cleanInput($_POST['username']);
    $password = cleanInput($_POST['password']);
    
    // Validações básicas
    $errors = [];
    if (empty($username)) {
        $errors[] = "O campo usuário é obrigatório";
    }
    if (empty($password)) {
        $errors[] = "O campo senha é obrigatório";
    }
    
    if (empty($errors)) {
        try {
            // Busca o usuário pelo username
            $query = "SELECT id, name, username, password, status FROM users 
                     WHERE username = :username AND status = 'active'";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->execute();

            if ($stmt->rowCount() === 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verifica a senha usando password_verify
                if (password_verify($password, $user['password'])) {
                    // Login bem-sucedido
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    
                    // Atualiza último login
                    $update_query = "UPDATE users SET last_login = NOW() WHERE id = :id";
                    $update_stmt = $conn->prepare($update_query);
                    $update_stmt->bindParam(":id", $user['id']);
                    $update_stmt->execute();
                    
                    // Registra login bem-sucedido
                    logLoginAttempt($conn, $user['id'], 'success');
                    
                    header("Location: schedules.php");
                    exit;
                } else {
                    // Senha incorreta
                    logLoginAttempt($conn, $user['id'], 'failed');
                    $login_error = "Utilizador ou senha incorretos";
                }
            } else {
                $login_error = "Utilizador ou senha incorretos";
            }
        } catch(PDOException $e) {
            $login_error = "Erro ao conectar: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BarberShop Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="background">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <form method="POST" action="">
        <div class="logo">
            <h3>PERFECT CUT</h3>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($login_error)): ?>
            <div class="error-message">
                <?php echo $login_error; ?>
            </div>
        <?php endif; ?>

        <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" name="username" placeholder="Utilizador" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
        </div>

        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="Senha" required>
        </div>

        <button type="submit">Entrar</button>
    </form>
</body>
</html>