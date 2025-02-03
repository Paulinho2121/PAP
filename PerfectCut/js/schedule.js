document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const dateInput = document.getElementById('date');
    const timeSelect = document.getElementById('time');
    const BASE_URL = window.location.pathname.includes('/admin') ? '/admin' : '';

    let bookedDates = []; // Variável para armazenar as datas bloqueadas

    // Função para buscar datas bloqueadas do PHP
    function fetchBookedDates() {
        return fetch(BASE_URL + '../get_available_times.php')
            .then(response => response.json())
            .then(data => {
                if (Array.isArray(data)) {
                    bookedDates = data;
                } else {
                    throw new Error('Formato inválido de resposta.');
                }
            })
            .catch(error => {
                console.error('Erro ao buscar datas bloqueadas:', error);
                Swal.fire({
                    title: 'Erro',
                    text: 'Não foi possível carregar as datas bloqueadas.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
    }

    // Inicializar o calendário após carregar as datas bloqueadas
    fetchBookedDates().then(() => {
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'pt-br',
            selectable: true,
            selectConstraint: {
                start: new Date().toISOString().split('T')[0],
                end: '2025-12-31'
            },
            dateClick: function(info) {
                const clickedDate = info.dateStr;
                const today = new Date().toISOString().split('T')[0];
                
                // Verificar se a data está no passado ou bloqueada
                if (clickedDate < today || bookedDates.includes(clickedDate)) {
                    Swal.fire({
                        title: 'Data Indisponível',
                        text: 'Esta data não está disponível para agendamento.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                dateInput.value = clickedDate;
                updateAvailableTimes(clickedDate);
            },
            dayCellDidMount: function(info) {
                const today = new Date().toISOString().split('T')[0];

                if (info.date < new Date(today) || bookedDates.includes(info.dateStr)) {
                    info.el.classList.add('fc-day-fully-booked');
                }
            }
        });
        calendar.render();
    });

    // Atualizar horários disponíveis ao selecionar uma data
    dateInput.addEventListener('change', function() {
        if (this.value) {
            updateAvailableTimes(this.value);
        } else {
            resetTimeSelect();
        }
    });

    function resetTimeSelect() {
        timeSelect.innerHTML = '<option value="">Selecione um horário</option>';
        timeSelect.disabled = false;
    }

    function updateAvailableTimes(date) {
        if (!date) {
            resetTimeSelect();
            return;
        }

        timeSelect.innerHTML = '<option value="">Carregando horários...</option>';
        timeSelect.disabled = true;

        const url = new URL('get_available_times.php', window.location.origin + BASE_URL);
        url.searchParams.append('date', date);

        axios.get(url.toString())
            .then(response => {
                let times = [];

                if (response.data && response.data.success === false) {
                    throw new Error(response.data.error || 'Erro ao carregar horários');
                }

                if (Array.isArray(response.data)) {
                    times = response.data;
                } else if (response.data && response.data.times) {
                    times = response.data.times;
                } else if (typeof response.data === 'object') {
                    times = Object.values(response.data).filter(time => typeof time === 'string');
                }

                timeSelect.innerHTML = '<option value="">Selecione um horário</option>';
                
                if (times.length === 0) {
                    timeSelect.innerHTML += '<option value="" disabled>Nenhum horário disponível</option>';
                } else {
                    times
                        .sort((a, b) => a.localeCompare(b))
                        .forEach(time => {
                            if (time && typeof time === 'string') {
                                const option = document.createElement('option');
                                option.value = time;
                                option.textContent = time;
                                timeSelect.appendChild(option);
                            }
                        });
                }
            })
            .catch(error => {
                console.error('Erro ao buscar horários:', error);
                timeSelect.innerHTML = '<option value="">Erro ao carregar horários</option>';
                
                let errorMessage = 'Erro ao carregar horários disponíveis.';
                if (error.response) {
                    errorMessage += ' ' + (error.response.data?.error || '');
                }

                Swal.fire({
                    title: 'Erro',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            })
            .finally(() => {
                timeSelect.disabled = false;
            });
    }
});
