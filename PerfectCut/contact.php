<?php 
session_start();
include_once './includes/header.php';
include_once './config/database.php';

$database = new Database();
$db = $database->getConnection();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barbearia - Contactos</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/header.css">
    <link rel="stylesheet" href="./css/contact.css">
    <link rel="stylesheet" href="./css/footer.css">
    <style>
        .alert {
            padding: 15px;
            margin: 20px 0;
            border: 1px solid transparent;
            border-radius: 4px;
            font-family: 'Montserrat', sans-serif;
            text-align: center;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success" id="alert-success">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error" id="alert-error">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <div class="contact-container">
        <div class="contact-info">
            <h2>Contactos da Barbearia</h2>
            <p><strong>Email:</strong> <a href="mailto:geral@barbeariastyle.pt">geral@barbeariastyle.pt</a></p>
            <p><strong>Telefone:</strong> <a href="tel:+351912345678">+351 912 345 678</a></p>
            <p><strong>Morada:</strong> Rua Principal, 123, 4000-001 Porto</p>
            <p>Horário de Atendimento: Qua-Dom: 9h-20h | Segunda e terça feira fechado!</p>

            <div class="map-container">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d8481.342642066813!2d-8.376573533477337!3d41.273882321387454!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd248cc05b6ceb6d%3A0x500ebbde490f360!2sPa%C3%A7os%20de%20Ferreira!5e0!3m2!1spt-PT!2spt!4v1734359497055!5m2!1spt-PT!2spt" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
        <section>
            <h2>Sugestões</h2>
            <form id="contactForm" action="contact_process.php" method="POST">
                <label for="name">Nome:</label>
                <input type="text" id="name" name="name" required>
            
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            
                <label for="subject">Assunto:</label>
                <input type="text" id="subject" name="subject" required>
            
                <label for="message">Mensagem:</label>
                <textarea id="message" name="message" rows="4" required></textarea>
            
                <input type="submit" value="Enviar Mensagem">
            </form>
        </section>
    </div>
    <script>
        // Script para esconder o alerta após alguns segundos
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.getElementById('alert-success');
            const errorAlert = document.getElementById('alert-error');

            setTimeout(() => {
                if (successAlert) successAlert.style.display = 'none';
                if (errorAlert) errorAlert.style.display = 'none';
            }, 5000);
        });
    </script>
</body>
</html>

<?php
include_once './includes/footer.php';
?>