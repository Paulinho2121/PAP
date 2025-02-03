document.addEventListener('DOMContentLoaded', () => {
    // Mobile menu toggle
    const initMobileMenu = () => {
        const menuToggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('nav ul');

        if (menuToggle && nav) {
            menuToggle.addEventListener('click', () => {
                nav.classList.toggle('active');
                menuToggle.classList.toggle('active');
            });
        }
    };

    // Form validation
    const initFormValidation = () => {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
                let isValid = true;

                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('error');
                        input.setAttribute('aria-invalid', 'true');
                    } else {
                        input.classList.remove('error');
                        input.removeAttribute('aria-invalid');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    const errorMessage = document.createElement('div');
                    errorMessage.classList.add('error-message');
                    errorMessage.textContent = 'Por favor, preencha todos os campos obrigatórios.';
                    form.prepend(errorMessage);
                }
            });
        });
    };

    // Responsive Design
    const handleResponsiveness = () => {
        const isMobile = window.innerWidth <= 768;
        
        if (isMobile) {
            document.body.classList.add('mobile-view');
            
            // Adjust responsive elements
            const elements = document.querySelectorAll('.responsive-element');
            elements.forEach(el => {
                el.style.width = '100%';
                el.style.fontSize = '14px';
            });

            // Reorganize layout if needed
            const sidebar = document.querySelector('.sidebar');
            const content = document.querySelector('.content');
            if (sidebar && content) {
                content.appendChild(sidebar);
            }
        } else {
            document.body.classList.remove('mobile-view');
        }
    };

    // Initialize all functions
    initMobileMenu();
    initFormValidation();
    handleResponsiveness();

    // Responsive event listeners
    window.addEventListener('resize', handleResponsiveness);
});

// Standalone toggle function (optional)
function toggleMenu() {
    const nav = document.querySelector("nav");
    const menuToggle = document.querySelector('.menu-toggle');
    
    if (nav && menuToggle) {
        nav.classList.toggle("active");
        menuToggle.classList.toggle("active");
    }
}

// Enhanced mobile detection
function isMobileDevice() {
    return (
        window.innerWidth <= 768 ||
        /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
        ('ontouchstart' in window) ||
        (navigator.maxTouchPoints > 0) ||
        (navigator.msMaxTouchPoints > 0)
    );
}