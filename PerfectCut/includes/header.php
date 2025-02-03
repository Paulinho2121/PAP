<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfect Cut Barbearia</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        /* Header e Botão */
        header {
            top: 0;
            left: 0;
            width: 100%;
            padding: 20px 40px;
            background-color: black;
            z-index: 100;
            display: flex;
            align-items: center;
        }

        .menu-btn {
            background-color: transparent; /* Fundo transparente */
            border: none;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            font-size: 1.5rem;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: -300px;
            width: 300px;
            height: 100vh;
            background-color: black;
            transition: left 0.3s ease;
            z-index: 1000;
            padding-top: 2rem;
        }

        .sidebar.active {
            left: 0;
        }

        .close-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background-color: transparent; /* Fundo transparente */
            border: none;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #666;
            font-size: 1.5rem;
        }

        .sidebar-header {
            padding: 1rem 2rem;
            text-align: left;
        }

        .sidebar-header img {
            height: 75px; /* Aumentado o tamanho do logo */
            width: auto;
        }

        .sidebar-menu {
            list-style: none;
            padding: 2rem;
        }

        .sidebar-menu li {
            margin: 1.5rem 0;
        }

        .sidebar-menu a {
            color: #666;
            text-decoration: none;
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }

        .sidebar-menu a:hover {
            color: #FF4500;
        }

        /* Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 999;
        }

        .overlay.active {
            display: block;
        }

        
    </style>
</head>
<body>
    <header>
        <button class="menu-btn" aria-label="Abrir menu">☰</button>
    </header>

    <div class="sidebar">
        <button class="close-btn" aria-label="Fechar menu">✕</button>
        <div class="sidebar-header">
            <img src="./images/logo.png" alt="Logo">
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php">Início</a></li>
            <li><a href="schedule.php">Agendar</a></li>
            <li><a href="gallery.php">Galeria</a></li>
            <li><a href="contact.php">Contacto</a></li>
            <li><a href="../dashboard/schedules.php">Administração</a></li>
        </ul>
    </div>

    <div class="overlay"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuBtn = document.querySelector('.menu-btn');
            const sidebar = document.querySelector('.sidebar');
            const closeBtn = document.querySelector('.close-btn');
            const overlay = document.querySelector('.overlay');

            function toggleSidebar() {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            }

            function closeSidebar() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }

            menuBtn.addEventListener('click', toggleSidebar);
            closeBtn.addEventListener('click', closeSidebar);
            overlay.addEventListener('click', closeSidebar);
        });
    </script>


</body>
</html>
