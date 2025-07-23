<?php
if (isset($_POST['btnlogin'])) {
    require_once "config.php";
    $sql = "SELECT * FROM tblaccounts WHERE username = ? AND password = ? AND status = 'ACTIVE'";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $_POST['txtusername'], $_POST['txtpassword']);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($result) > 0) {
                $accounts = mysqli_fetch_array($result, MYSQLI_ASSOC);
                $_SESSION['username'] = $accounts['username'];
                $_SESSION['usertype'] = $accounts['usertype'];

                // Handle "Remember Me" functionality
                if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
                    setcookie('remember_username', $_POST['txtusername'], time() + (30 * 24 * 60 * 60), "/");
                    setcookie('remember_password', $_POST['txtpassword'], time() + (30 * 24 * 60 * 60), "/");
                } else {
                    setcookie('remember_username', '', time() - 3600, "/");
                    setcookie('remember_password', '', time() - 3600, "/");
                }
            } else {
                $error_message = "<span class='error-message'><i class='fa-solid fa-exclamation-circle'></i> Incorrect login details or account is inactive.</span>";
            }
        } else {
            $error_message = "<span class='error-message'><i class='fa-solid fa-database'></i> Error on the login statement.</span>";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "<span class='error-message'><i class='fa-solid fa-gears'></i> Error preparing the login statement.</span>";
    }
    mysqli_close($link);
}

$remembered_username = isset($_COOKIE['remember_username']) ? htmlspecialchars($_COOKIE['remember_username']) : '';
$remembered_password = isset($_COOKIE['remember_password']) ? htmlspecialchars($_COOKIE['remember_password']) : '';
$remember_checked = !empty($remembered_username) ? 'checked' : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Technical Support Management System - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
            --bg-gradient: linear-gradient(145deg, #222 0%, #444 100%);
            --container-bg: rgba(255, 255, 255, 0.1);
            --container-border: rgba(255, 255, 255, 0.15);
            --label-color: #999;
            --input-bg: rgba(255, 255, 255, 0.05);
            --input-border: rgba(255, 255, 255, 0.2);
            --input-focus-bg: rgba(255, 255, 255, 0.1);
            --dropdown-bg: rgba(51, 51, 51, 0.9);
            --error-bg: rgba(255, 64, 64, 0.2);
            --error-shadow: rgba(255, 64, 64, 0.3);
            --footer-color: #999;
            --menu-bg: rgba(255, 255, 255, 0.15);
            --menu-hover-bg: rgba(255, 255, 255, 0.25);
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
            --bg-gradient: linear-gradient(145deg, #f0f0f0 0%, #d9d9d9 100%);
            --container-bg: rgba(255, 255, 255, 0.8);
            --container-border: rgba(0, 0, 0, 0.1);
            --label-color: #333;
            --input-bg: rgba(255, 255, 255, 0.9);
            --input-border: rgba(0, 0, 0, 0.2);
            --input-focus-bg: rgba(255, 255, 255, 1);
            --dropdown-bg: rgba(255, 255, 255, 0.9);
            --error-bg: rgba(255, 64, 64, 0.1);
            --error-shadow: rgba(255, 64, 64, 0.2);
            --footer-color: #444;
            --menu-bg: rgba(0, 0, 0, 0.1);
            --menu-hover-bg: rgba(0, 0, 0, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            background: var(--bg-gradient);
            transition: background 0.3s ease;
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

        /* Login Container Styles */
        .login-container {
            width: 100%;
            max-width: 480px;
            background: var(--container-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--container-border);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            transition: transform 0.4s ease, box-shadow 0.4s ease, background 0.3s ease;
            margin: auto;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .login-container:hover {
            transform: translate(-50%, -50%) translateZ(40px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.6);
        }

        .mode-toggle {
            position: absolute;
            top: 10px;
            left: 10px;
            width: 36px;
            height: 36px;
            background: var(--menu-bg);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .mode-toggle:hover {
            background: var(--menu-hover-bg);
            transform: scale(1.1);
        }

        .mode-toggle i {
            font-size: 1.4rem;
            color: var(--text-color);
        }

        .login-form {
            color: var(--text-color);
        }

        .login-form h2 {
            font-size: 1.6rem;
            color: #ff4040;
            margin-bottom: 25px;
            font-weight: 600;
            text-align: center;
            position: relative;
        }

        .login-form h2::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 2px;
            background: #1e90ff;
        }

        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .input-group input {
            width: 100%;
            padding: 12px 18px;
            border: 1px solid var(--input-border);
            background: var(--input-bg);
            color: var(--text-color);
            font-size: 0.95rem;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .input-group.focused input,
        .input-group input:focus {
            outline: none;
            border-color: #1e90ff;
            background: var(--input-focus-bg);
            box-shadow: 0 8px 20px rgba(30, 144, 255, 0.3);
            transform: translateY(-2px);
        }

        .input-group label {
            position: absolute;
            top: 50%;
            left: 18px;
            font-size: 0.95rem;
            color: var(--label-color);
            transform: translateY(-50%);
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .input-group.focused label,
        .input-group input:focus ~ label,
        .input-group input:not(:placeholder-shown) ~ label {
            top: -8px;
            left: 12px;
            font-size: 0.75rem;
            color: #1e90ff;
            background: var(--dropdown-bg);
            padding: 2px 6px;
            border-radius: 4px;
        }

        .password-group {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--label-color);
            cursor: pointer;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .toggle-password:hover {
            color: #1e90ff;
            transform: translateY(-50%) scale(1.1);
        }

        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 0.85rem;
        }

        .remember-me {
            color: var(--label-color);
            display: flex;
            align-items: center;
        }

        .remember-me input {
            margin-right: 6px;
        }

        .forgot-link {
            color: #1e90ff;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .forgot-link:hover {
            color: #ff4040;
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #1e90ff, #187bcd);
            color: #fff;
            border: none;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(30, 144, 255, 0.4);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .login-btn:hover {
            background: linear-gradient(135deg, #187bcd, #1e90ff);
            box-shadow: 0 8px 20px rgba(30, 144, 255, 0.6);
            transform: translateY(-2px);
        }

        .login-btn i {
            transition: transform 0.3s ease;
        }

        .login-btn:hover i {
            transform: translateX(5px);
        }

        .register-link {
            margin-top: 20px;
            text-align: center;
            font-size: 0.85rem;
        }

        .register-link a {
            color: #1e90ff;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .register-link a:hover {
            color: #ff4040;
            text-decoration: underline;
        }

        .error-message {
            background: var(--error-bg);
            color: #ff4040;
            padding: 10px 14px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            border-radius: 6px;
            box-shadow: 0 5px 15px var(--error-shadow);
            display: flex;
            align-items: center;
            gap: 6px;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-4px); }
            75% { transform: translateX(4px); }
        }

        .login-footer {
            position: fixed;
            bottom: 15px;
            left: 0;
            width: 100%;
            text-align: center;
            color: var(--footer-color);
            font-size: 0.8rem;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        @media screen and (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .top-bar, .main-content {
                margin-left: 200px;
            }
            .login-container {
                max-width: 400px;
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
            .login-container {
                max-width: 320px;
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['username'])): ?>
        <div class="d-flex">
            <nav class="sidebar shadow-lg vh-100 d-flex flex-column p-4">
                <div class="text-center mb-4">
                    <img src="image/au-logo.png" alt="AU Logo" class="img-fluid mb-3 logo-3d" style="max-width: 140px;">
                    <h4 class="fw-bold text-uppercase letter-spacing university-title">Arellano University</h4>
                </div>
                <ul class="nav flex-column mt-4 flex-grow-1">
                    <li class="nav-item mb-3">
                        <a href="dashboard.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                            <i class="fa-solid fa-chart-line me-3"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item mb-3">
                        <a href="accounts-management.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'accounts-management.php') ? 'active' : ''; ?>">
                            <i class="fa-solid fa-user-shield me-3"></i> Account Management
                        </a>
                    </li>
                    <li class="nav-item mb-3">
                        <a href="equipment-management.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'equipment-management.php') ? 'active' : ''; ?>">
                            <i class="fa-solid fa-wrench me-3"></i> Equipment Management
                        </a>
                    </li>
                    <li class="nav-item mb-3">
                        <a href="ticket-management.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'ticket-management.php') ? 'active' : ''; ?>">
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
                            <a href="dashboard.php" class="icon-3d"><i class="fa-solid fa-house fa-lg"></i></a>
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
    <?php else: ?>
        <div class="login-container">
            <div class="mode-toggle" onclick="toggleMode()">
                <i class="fa-solid fa-moon"></i>
            </div>
            <div class="left-panel text-center">
                <img src="image/au-logo.png" alt="Arellano University Logo" class="logo mb-3">
                <h1 class="fw-bold text-uppercase">Arellano University</h1>
                <p>Technical Support Management System</p>
                <div class="system-info mt-3">
                    <span class="status">ALL USERS <i class="fa-solid fa-circle-check"></i></span>
                </div>
            </div>
            <div class="login-form">
                <h2>Login</h2>
                <?php if (isset($error_message)) { echo $error_message; } ?>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" autocomplete="off">
                    <div class="input-group">
                        <input type="text" name="txtusername" id="username" value="<?php echo $remembered_username; ?>" required>
                        <label for="username">Username</label>
                    </div>
                    <div class="input-group password-group">
                        <input type="password" name="txtpassword" id="password" value="<?php echo $remembered_password; ?>" required>
                        <label for="password">Password</label>
                        <i class="fa-solid fa-eye toggle-password" onclick="togglePassword()"></i>
                    </div>
                    <div class="options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember" <?php echo $remember_checked; ?>> Remember me
                        </label>
                        <a href="forgot-password.php" class="forgot-link">Forgot Password?</a>
                    </div>
                    <button type="submit" name="btnlogin" class="login-btn">
                        <span>Sign In</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>
                <div class="register-link">
                    <p>New user? <a href="register.php">Create Account</a></p>
                </div>
            </div>
        </div>
        <footer class="login-footer">
            <p>© <?php echo date('Y'); ?> Arellano University. All rights reserved.</p>
        </footer>
    <?php endif; ?>

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
            const initialActive = storedActive || currentPage || 'dashboard.php';
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === initialActive) {
                    removeActive();
                    link.classList.add('active');
                }
            });

            const savedMode = localStorage.getItem('themeMode');
            const body = document.body;
            const themeToggles = document.querySelectorAll('.toggle-theme, .mode-toggle');
            const themeIcons = document.querySelectorAll('.toggle-theme i, .mode-toggle i');

            if (savedMode === 'light') {
                body.classList.remove('dark-mode');
                body.classList.add('light-mode');
                themeIcons.forEach(icon => {
                    icon.classList.remove('fa-moon', 'fa-circle-half-stroke');
                    icon.classList.add('fa-sun');
                });
            } else {
                body.classList.remove('light-mode');
                body.classList.add('dark-mode');
                themeIcons.forEach(icon => {
                    icon.classList.remove('fa-sun', 'fa-circle-half-stroke');
                    icon.classList.add('fa-moon');
                });
            }

            themeToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (body.classList.contains('dark-mode')) {
                        body.classList.remove('dark-mode');
                        body.classList.add('light-mode');
                        themeIcons.forEach(icon => {
                            icon.classList.remove('fa-moon');
                            icon.classList.add('fa-sun');
                        });
                        localStorage.setItem('themeMode', 'light');
                    } else {
                        body.classList.remove('light-mode');
                        body.classList.add('dark-mode');
                        themeIcons.forEach(icon => {
                            icon.classList.remove('fa-sun');
                            icon.classList.add('fa-moon');
                        });
                        localStorage.setItem('themeMode', 'dark');
                    }
                });
            });

            document.querySelectorAll('.input-group input').forEach(input => {
                if (input.value) {
                    input.parentElement.classList.add('focused');
                }
                input.addEventListener('focus', () => {
                    input.parentElement.classList.add('focused');
                });
                input.addEventListener('blur', () => {
                    if (!input.value) {
                        input.parentElement.classList.remove('focused');
                    }
                });
            });
        });

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>