document.addEventListener('DOMContentLoaded', () => {
    // Hamburger menu toggle
    const hamburger = document.querySelector('.hamburger-menu');
    const navLinks = document.querySelector('.nav-links');
    
    hamburger?.addEventListener('click', () => {
        navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
    });

    // Form validation
    document.querySelectorAll('input[pattern]').forEach(input => {
        input.addEventListener('input', function() {
            const pattern = new RegExp(this.pattern);
            if (!pattern.test(this.value)) {
                this.setCustomValidity('Invalid format. Use 6 alphanumeric characters.');
            } else {
                this.setCustomValidity('');
            }
        });
    });

    // Auto-uppercase certificate IDs
    document.querySelector('input[name="certificate_id"]')?.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
});