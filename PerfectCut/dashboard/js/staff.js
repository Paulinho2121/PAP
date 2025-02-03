// Funções do Modal
function openAddStaffModal() {
    document.getElementById('addStaffModal').classList.remove('hidden');
}

function closeAddStaffModal() {
    document.getElementById('addStaffModal').classList.add('hidden');
}

// Fechar modal se clicar fora dele
window.onclick = function(event) {
    const modal = document.getElementById('addStaffModal');
    if (event.target === modal) {
        closeAddStaffModal();
    }
}

// Função de Pesquisa e Filtro
function filterTable() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const roleFilter = document.getElementById('roleFilter').value;
    const table = document.getElementById('staffTable');
    const tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        const username = tr[i].getElementsByTagName('td')[0];
        const name = tr[i].getElementsByTagName('td')[1];
        const role = tr[i].getElementsByTagName('td')[2];

        if (username && name && role) {
            const usernameText = username.textContent || username.innerText;
            const nameText = name.textContent || name.innerText;
            const roleText = role.textContent || role.innerText;

            const matchesSearch = usernameText.toLowerCase().includes(searchInput) ||
                                nameText.toLowerCase().includes(searchInput);
            
            const matchesRole = roleFilter === 'all' || roleText === roleFilter;

            if (matchesSearch && matchesRole) {
                tr[i].style.display = '';
            } else {
                tr[i].style.display = 'none';
            }
        }
    }
}

// Função para deletar um funcionário
function deleteStaff(id) {
    Swal.fire({
        title: 'Tem certeza?',
        text: "Esta ação não pode ser revertida!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, eliminar!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('delete_Staff.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    Swal.fire({
                        title: 'Eliminado!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#3085d6'
                    }).then(() => {
                        // Remove a linha da tabela após eliminação bem-sucedida
                        const row = document.querySelector(`tr[data-staff-id="${id}"]`);
                        if (row) {
                            row.remove();
                        }
                        // Atualiza os contadores
                        updateStaffCounters();
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Erro!',
                    text: 'Ocorreu um erro ao eliminar o funcionário.',
                    icon: 'error',
                    confirmButtonColor: '#d33'
                });
                console.error('Error:', error);
            });
        }
    });
}

// Função para atualizar os contadores de staff
function updateStaffCounters() {
    const table = document.getElementById('staffTable');
    if (!table) return;

    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    let barberCount = 0;
    let adminCount = 0;

    for (let row of rows) {
        const roleCell = row.cells[2]; // Célula que contém a função
        if (roleCell.textContent.trim() === 'Barbeiro') {
            barberCount++;
        } else if (roleCell.textContent.trim() === 'Administrador') {
            adminCount++;
        }
    }

    // Atualiza os contadores no DOM
    const barberCounter = document.querySelector('.text-2xl.font-bold');
    const adminCounter = document.querySelectorAll('.text-2xl.font-bold')[1];
    
    if (barberCounter) barberCounter.textContent = barberCount;
    if (adminCounter) adminCounter.textContent = adminCount;
}