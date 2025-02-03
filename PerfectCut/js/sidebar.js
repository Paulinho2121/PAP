document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const openBtn = document.getElementById('openSidebar');
    const closeBtn = document.getElementById('closeSidebar');

    openBtn.addEventListener('click', function() {
        sidebar.classList.add('active');
    });

    closeBtn.addEventListener('click', function() {
        sidebar.classList.remove('active');
    });

    // Fechar a sidebar quando clicar fora dela
    document.addEventListener('click', function(event) {
        if (!sidebar.contains(event.target) && event.target !== openBtn) {
            sidebar.classList.remove('active');
        }
    });
});