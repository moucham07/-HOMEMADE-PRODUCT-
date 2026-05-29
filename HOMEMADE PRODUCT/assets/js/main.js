// Initialize MDBootstrap elements if needed
document.addEventListener('DOMContentLoaded', () => {
    // Mobile menu collapse on click
    const navLinks = document.querySelectorAll('.nav-link');
    const menuToggle = document.getElementById('navbarContent');
    if (menuToggle) {
        // We can use MDB's built-in collapse but sometimes we want to hide it after clicking
        // This is a simple implementation
    }

    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new mdb.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
