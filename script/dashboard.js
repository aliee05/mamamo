document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.sidebar .nav-link');

    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPath || href === currentPath.split('?')[0]) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
});