// Public Interface JavaScript
(function() {
    'use strict';
    
    // Screensaver functionality
    let screensaverTimeout;
    const SCREENSAVER_DELAY = 300000; // 5 minutes
    
    function resetScreensaver() {
        clearTimeout(screensaverTimeout);
        hideScreensaver();
        screensaverTimeout = setTimeout(showScreensaver, SCREENSAVER_DELAY);
    }
    
    function showScreensaver() {
        // Implementation for screensaver
        console.log('Screensaver activated');
    }
    
    function hideScreensaver() {
        // Implementation to hide screensaver
    }
    
    // Reset screensaver on user activity
    document.addEventListener('mousemove', resetScreensaver);
    document.addEventListener('keypress', resetScreensaver);
    document.addEventListener('touchstart', resetScreensaver);
    
    // Initialize screensaver timer
    resetScreensaver();
    
    // Form validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });
    });
    
    // Auto-refresh timeline every 60 seconds
    if (window.location.pathname.includes('index.php')) {
        setInterval(() => {
            const currentUrl = new URL(window.location.href);
            const roomId = currentUrl.searchParams.get('room_id');
            const date = currentUrl.searchParams.get('date');
            
            // Only refresh if on the same day and room
            if (!document.querySelector('.modal[style*="display: flex"]')) {
                window.location.reload();
            }
        }, 60000);
    }
    
    // Touch-friendly enhancements
    if ('ontouchstart' in window) {
        document.body.classList.add('touch-device');
    }
    
    console.log('Booking system initialized');
})();
