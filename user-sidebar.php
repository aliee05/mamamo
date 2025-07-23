<?php
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<div class="d-flex">
    <nav class="sidebar shadow-lg vh-100 d-flex flex-column p-4">
        <div class="text-center mb-4">
            <img src="image/au-logo.png" alt="AU Logo" class="img-fluid mb-3 logo-3d" style="max-width: 140px;">
            <h4 class="fw-bold text-uppercase letter-spacing university-title">Arellano University</h4>
        </div>
        <ul class="nav flex-column mt-4 flex-grow-1">
            <li class="nav-item mb-3">
                <a href="user-dashboard.php" class="nav-link <?php echo ($GLOBALS['current_page'] == 'user-dashboard.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-chart-line me-3"></i> Dashboard
                </a>
            </li>
            <li class="nav-item mb-3">
                <a href="user-equipment-management.php" class="nav-link <?php echo ($GLOBALS['current_page'] == 'user-equipment-management.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-wrench me-3"></i> Equipment Management
                </a>
            </li>
            <li class="nav-item mb-3">
                <a href="user-ticket-management.php" class="nav-link <?php echo ($GLOBALS['current_page'] == 'user-ticket-management.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-clipboard-list me-3"></i> Ticket Management
                </a>
            </li>
        </ul>
        <a href="login.php" class="btn w-100 mt-auto logout-btn">
            <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Logout
        </a>
    </nav>

    <div class="w-100">
        <div class="top-bar shadow-sm d-flex align-items-center justify-content-between p-3">
            <span class="fs-5 fw-bold welcome-text">
                <i class="fa-solid fa-building-columns me-2"></i> 
                Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>: <?php echo htmlspecialchars($_SESSION['usertype']); ?>
            </span>
            <div class="d-flex align-items-center">
                <div class="home-icon">
                    <a href="user-dashboard.php" class="icon-3d"><i class="fa-solid fa-house fa-lg"></i></a>
                </div>
                <div class="profile-icon">
                    <a href="profile.php" class="icon-3d"><i class="fa-solid fa-user-gear fa-lg"></i></a>
                </div>
                <div class="notification-icon">
                    <a href="#" class="icon-3d"><i class="fa-solid fa-bell fa-lg"></i></a>
                </div>
                <div class="settings-icon">
                    <a href="#" class="icon-3d"><i class="fa-solid fa-cogs fa-lg"></i></a>
                </div>
                <div class="theme-toggle-icon">
                    <a href="#" class="icon-3d toggle-theme"><i class="fa-solid fa-circle-half-stroke fa-lg"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Theme Variables */
:root {
    --sidebar-bg: #333;
    --topbar-bg: #444;
    --main-bg: rgba(255, 255, 255, 0.05);
    --icon-color: #ccc;
    --hover-bg: rgba(255, 255, 255, 0.1);
    --active-bg: #1e90ff;
    --text-color: #ccc;
    --logout-border: #ff4040;
    --logout-hover: #ff4040;
    --university-title-color: #ccc;
    --topbar-icon-color: #ccc;
    --shadow-color: rgba(0, 0, 0, 0.3);
}

body.light-mode {
    --sidebar-bg: #e9ecef;
    --topbar-bg: #dee2e6;
    --main-bg: rgba(255, 255, 255, 0.9);
    --icon-color: #1976d2;
    --hover-bg: rgba(30, 144, 255, 0.2);
    --active-bg: #1976d2;
    --text-color: #333;
    --logout-border: #d32f2f;
    --logout-hover: #d32f2f;
    --university-title-color: #333;
    --topbar-icon-color: #333;
    --shadow-color: rgba(0, 0, 0, 0.2);
}

/* Sidebar Styles */
.sidebar {
    width: 260px;
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    background: var(--sidebar-bg);
    transition: all 0.3s ease;
    z-index: 1000;
    color: var(--text-color);
    box-shadow: 6px 0 20px var(--shadow-color);
    border-right: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar .nav-link {
    font-size: 16px;
    padding: 14px 24px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
    color: var(--text-color);
    position: relative;
    margin: 0 12px;
    background: linear-gradient(135deg, rgba(25, 118, 210, 0.1), rgba(25, 118, 210, 0.2));
}

.sidebar .nav-link i {
    font-size: 20px;
    color: var(--icon-color);
    transition: all 0.3s ease;
}

.sidebar .nav-link:hover {
    transform: translateY(-3px);
    background: var(--hover-bg);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.25);
    color: var(--text-color);
}

.sidebar .nav-link.active {
    background: var(--active-bg);
    color: var(--text-color);
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.25);
}

.sidebar .nav-link.active i {
    color: var(--text-color);
}

.university-title {
    color: var(--university-title-color);
    font-weight: 700;
    text-shadow: 1px 1px 3px var(--shadow-color);
}

.logo-3d {
    transition: all 0.3s ease;
}

.logo-3d:hover {
    transform: scale(1.08) rotate(3deg);
    filter: drop-shadow(0 6px 12px var(--shadow-color));
}

.logout-btn {
        background: linear-gradient(135deg, #d32f2f, #f44336);
    }

    .logout-btn:hover {
        background: #1976d2;
    }

.top-bar {
    background: var(--topbar-bg);
    color: var(--text-color);
    margin-left: 260px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
    transition: background 0.3s ease;
    border-bottom: 1px solid rgba(25, 118, 210, 0.3);
}

.welcome-text {
    color: var(--topbar-icon-color);
    font-weight: 600;
    text-shadow: 1px 1px 3px var(--shadow-color);
}

.top-bar .icon-3d {
    color: var(--topbar-icon-color);
    transition: all 0.3s ease;
    padding: 8px;
    margin: 0;
}

.top-bar .d-flex {
    gap: 8px;
    align-items: center;
}

.top-bar .icon-3d:hover {
    transform: scale(1.15) rotate(6deg);
    filter: drop-shadow(0 4px 8px var(--shadow-color));
}

.text-uppercase {
    text-transform: uppercase;
}

.letter-spacing {
    letter-spacing: 1.5px;
}

.main-content {
    margin-left: 260px;
    margin-top: 70px;
    min-height: calc(100vh - 70px);
    background: var(--main-bg);
    transition: all 0.3s ease;
    box-shadow: inset 0 0 10px var(--shadow-color);
}

@media screen and (max-width: 768px) {
    .sidebar {
        width: 200px;
    }
    .top-bar, .main-content {
        margin-left: 200px;
    }
}

@media screen and (max-width: 480px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: fixed;
        top: 0;
        left: -100%;
        transition: left 0.3s ease;
    }
    .sidebar.active {
        left: 0;
    }
    .top-bar, .main-content {
        margin-left: 0;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
    const initialActive = storedActive || currentPage || 'user-dashboard.php';
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === initialActive) {
            removeActive();
            link.classList.add('active');
        }
    });

    const savedMode = localStorage.getItem('themeMode');
    const body = document.body;
    const themeToggle = document.querySelector('.toggle-theme');
    const themeIcon = themeToggle.querySelector('i');

    if (savedMode === 'light') {
        body.classList.remove('dark-mode');
        body.classList.add('light-mode');
        themeIcon.classList.remove('fa-circle-half-stroke');
        themeIcon.classList.add('fa-sun');
    } else {
        body.classList.remove('light-mode');
        body.classList.add('dark-mode');
        themeIcon.classList.remove('fa-sun');
        themeIcon.classList.add('fa-moon');
    }

    themeToggle.addEventListener('click', function(e) {
        e.preventDefault();
        if (body.classList.contains('dark-mode')) {
            body.classList.remove('dark-mode');
            body.classList.add('light-mode');
            themeIcon.classList.remove('fa-moon');
            themeIcon.classList.add('fa-sun');
            localStorage.setItem('themeMode', 'light');
        } else {
            body.classList.remove('light-mode');
            body.classList.add('dark-mode');
            themeIcon.classList.remove('fa-sun');
            themeIcon.classList.add('fa-moon');
            localStorage.setItem('themeMode', 'dark');
        }
    });
});
</script>