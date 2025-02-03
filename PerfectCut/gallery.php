<?php 
include_once './includes/header.php';
include_once './config/database.php';

$database = new Database();
$db = $database->getConnection();
?>

<link rel="stylesheet" href="./css/header.css">
<link rel="stylesheet" href="./css/gallery.css">
<link rel="stylesheet" href="./css/footer.css">

<div class="container">
    <h1>O Nosso Trabalho</h1>
    
    <div class="carousel-container">
        <div class="carousel-track">
            <?php 
            $images = [
                'img1.jpg',
                'img2.jpg',
                'img3.jpg',
            ];

            foreach ($images as $image): 
                $imagePath = "images/" . $image;
            ?>
            <div class="carousel-slide">
                <img src="<?= $imagePath ?>" alt="Trabalho Perfect Cut">
            </div>
            <?php endforeach; ?>
        </div>
        
        <button class="carousel-button prev">&lt;</button>
        <button class="carousel-button next">&gt;</button>
        
        <div class="carousel-dots">
            <?php foreach ($images as $index => $image): ?>
            <div class="carousel-dot <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>"></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const track = document.querySelector('.carousel-track');
    const slides = document.querySelectorAll('.carousel-slide');
    const dots = document.querySelectorAll('.carousel-dot');
    const prevButton = document.querySelector('.carousel-button.prev');
    const nextButton = document.querySelector('.carousel-button.next');
    
    let currentIndex = 0;
    const slideCount = slides.length;
    
    // Atualiza a posição do carrossel
    function updateCarousel() {
        track.style.transform = `translateX(-${currentIndex * 100}%)`;
        
        // Atualiza os dots
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentIndex);
        });
    }
    
    // Handlers para os botões
    prevButton.addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + slideCount) % slideCount;
        updateCarousel();
    });
    
    nextButton.addEventListener('click', () => {
        currentIndex = (currentIndex + 1) % slideCount;
        updateCarousel();
    });
    
    // Handler para os dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentIndex = index;
            updateCarousel();
        });
    });
    
    // Autoplay (opcional)
    setInterval(() => {
        currentIndex = (currentIndex + 1) % slideCount;
        updateCarousel();
    }, 5000);  // Muda a cada 5 segundos
});
</script>

<?php 
include_once './includes/footer.php';
?>