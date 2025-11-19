// Admin Panel JavaScript
(function() {
    'use strict';
    
    // Confirmation dialogs
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Auto-save draft functionality
    const forms = document.querySelectorAll('form[data-autosave]');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                const formData = new FormData(form);
                const data = Object.fromEntries(formData);
                localStorage.setItem('draft_' + form.id, JSON.stringify(data));
            });
        });
        
        // Restore draft on load
        const draft = localStorage.getItem('draft_' + form.id);
        if (draft) {
            const data = JSON.parse(draft);
            Object.keys(data).forEach(key => {
                const input = form.querySelector(`[name="${key}"]`);
                if (input) {
                    input.value = data[key];
                }
            });
        }
    });
    
    // Table sorting
    const tables = document.querySelectorAll('table[data-sortable]');
    tables.forEach(table => {
        const headers = table.querySelectorAll('th[data-sort]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                const column = this.dataset.sort;
                sortTable(table, column);
            });
        });
    });
    
    function sortTable(table, column) {
        // Basic table sorting implementation
        console.log('Sorting table by', column);
    }
    
    // Success message auto-hide
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
    
    // Mobile menu toggle
    const menuToggle = document.querySelector('[data-menu-toggle]');
    const sidebar = document.querySelector('.sidebar');
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    }
    
    console.log('Admin panel initialized');
})();
