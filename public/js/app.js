// ================================
// FLASH MESSAGES — Disparition auto
// ================================
document.addEventListener('DOMContentLoaded', () => {

    // Fermeture automatique des alertes après 4 secondes
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });

    // ================================
    // CONFIRMATION DE SUPPRESSION
    // ================================
    const deleteForms = document.querySelectorAll('form[data-confirm]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', (e) => {
            const message = form.dataset.confirm || 'Êtes-vous sûr de vouloir supprimer cet élément ?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // ================================
    // VALIDATION DATES EN TEMPS RÉEL
    // ================================
    const startInput = document.querySelector('#reservation_startDatetime');
    const endInput = document.querySelector('#reservation_endDatetime');
    const dateError = document.querySelector('#date-error');

    if (startInput && endInput) {

        // Empêcher de réserver dans le passé
        const now = new Date().toISOString().slice(0, 16);
        startInput.min = now;
        endInput.min = now;

        const validateDates = () => {
            const start = new Date(startInput.value);
            const end = new Date(endInput.value);

            if (startInput.value && endInput.value) {
                if (end <= start) {
                    if (dateError) {
                        dateError.textContent = '❌ La date de fin doit être après la date de début.';
                        dateError.style.display = 'block';
                    }
                    endInput.style.borderColor = '#dc2626';
                    return false;
                } else {
                    if (dateError) {
                        dateError.textContent = '';
                        dateError.style.display = 'none';
                    }
                    endInput.style.borderColor = '#16a34a';
                    return true;
                }
            }
        };

        startInput.addEventListener('change', () => {
            // La fin doit être au moins après le début
            endInput.min = startInput.value;
            validateDates();
        });

        endInput.addEventListener('change', validateDates);
    }

    // ================================
    // RECHERCHE EN TEMPS RÉEL SUR LES TABLES
    // ================================
    const searchInput = document.querySelector('#table-search');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.toLowerCase();
            const rows = document.querySelectorAll('table tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    // ================================
    // NAVBAR — Menu mobile
    // ================================
    const menuToggle = document.querySelector('#menu-toggle');
    const navbarMenu = document.querySelector('.navbar-menu');

    if (menuToggle && navbarMenu) {
        menuToggle.addEventListener('click', () => {
            navbarMenu.classList.toggle('open');
        });
    }

});