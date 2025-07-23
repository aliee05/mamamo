document.addEventListener('DOMContentLoaded', () => {
    const navLinks = document.querySelectorAll('.nav-link');
    
    function removeActive() {
        navLinks.forEach(l => l.classList.remove('active'));
    }

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            removeActive();
            this.classList.add('active');
            localStorage.setItem('activeNav', this.getAttribute('href'));
        });
    });

    const currentPage = window.location.pathname.split('/').pop();
    const storedActive = localStorage.getItem('activeNav');
    const initialActive = storedActive || currentPage || 'dashboard.php';
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === initialActive) {
            removeActive();
            link.classList.add('active');
        }
    });

    const themeToggle = document.querySelector('.toggle-theme');
    const html = document.documentElement;
    const themeIcon = themeToggle.querySelector('i');
    
    themeToggle.addEventListener('click', function(e) {
        e.preventDefault();
        const currentTheme = html.getAttribute('data-theme') || 'dark';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        themeIcon.classList.toggle('fa-circle-half-stroke');
        themeIcon.classList.toggle('fa-sun');
    });

    const savedTheme = localStorage.getItem('theme') || 'dark';
    html.setAttribute('data-theme', savedTheme);
    if (savedTheme === 'light') {
        themeIcon.classList.remove('fa-circle-half-stroke');
        themeIcon.classList.add('fa-sun');
    }
});