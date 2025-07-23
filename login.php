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
                session_start();
                $_SESSION['username'] = $accounts['username'];
                $_SESSION['usertype'] = $accounts['usertype'];

                // Handle "Remember Me" functionality
                if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
                    // Set cookies for 30 days
                    setcookie('remember_username', $_POST['txtusername'], time() + (30 * 24 * 60 * 60), "/");
                    setcookie('remember_password', $_POST['txtpassword'], time() + (30 * 24 * 60 * 60), "/");
                } else {
                    // Clear cookies if "Remember Me" is unchecked
                    setcookie('remember_username', '', time() - 3600, "/");
                    setcookie('remember_password', '', time() - 3600, "/");
                }

                // Redirect based on usertype
                switch (strtoupper($accounts['usertype'])) {
                    case "ADMINISTRATOR":
                        header("location: dashboard.php");
                        exit();
                    case "TECHNICAL":
                        header("location: tech-dashboard.php");
                        exit();
                    case "USER":
                        header("location: user-dashboard.php");
                        exit();
                    default:
                        $error_message = "<span class='error-message'><i class='fa-solid fa-exclamation-circle'></i> Invalid user type.</span>";
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

// Retrieve cookie values if they exist
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            /* Dark Mode Variables */
            --bg-gradient: linear-gradient(145deg, #222 0%, #444 100%);
            --container-bg: rgba(255, 255, 255, 0.1);
            --container-border: rgba(255, 255, 255, 0.15);
            --text-color: #ccc;
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
            /* Light Mode Variables */
            --bg-gradient: linear-gradient(145deg, #f0f0f0 0%, #d9d9d9 100%);
            --container-bg: rgba(255, 255, 255, 0.8);
            --container-border: rgba(0, 0, 0, 0.1);
            --text-color: #333;
            --label-color: #666;
            --input-bg: rgba(255, 255, 255, 0.9);
            --input-border: rgba(0, 0, 0, 0.2);
            --input-focus-bg: rgba(255, 255, 255, 1);
            --dropdown-bg: rgba(255, 255, 255, 0.9);
            --error-bg: rgba(255, 64, 64, 0.1);
            --error-shadow: rgba(255, 64, 64, 0.2);
            --footer-color: #666;
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
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: auto;
            position: relative;
            perspective: 1000px;
            transition: background 0.3s ease;
        }

        .login-container {
            width: 100%;
            max-width: 480px;
            background: var(--container-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--container-border);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            transform: translateZ(20px);
            transition: transform 0.4s ease, box-shadow 0.4s ease, background 0.3s ease;
        }

        .login-container:hover {
            transform: translateZ(40px);
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

        .left-panel {
            text-align: center;
            color: var(--text-color);
            margin-bottom: 30px;
            position: relative;
        }

        .menu-container {
            position: absolute;
            top: 10px;
            right: 10px;
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

        .menu-container:hover {
            background: var(--menu-hover-bg);
            transform: scale(1.1);
        }

        .menu-icon {
            font-size: 1.4rem;
            color: var(--text-color);
            transition: transform 0.3s ease;
        }

        .menu-container.active .menu-icon.fa-bars {
            display: none;
        }

        .menu-container.active .menu-icon.fa-xmark {
            display: block;
            transform: rotate(180deg);
        }

        .menu-icon.fa-xmark {
            display: none;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 50px;
            right: 0;
            background: var(--dropdown-bg);
            backdrop-filter: blur(10px);
            border-radius: 8px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            padding: 8px 0;
            min-width: 140px;
            z-index: 10;
            transition: background 0.3s ease;
        }

        .dropdown-menu.active {
            display: block;
        }

        .login-option {
            display: block;
            padding: 8px 16px;
            color: var(--text-color);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .login-option:hover {
            background: #1e90ff;
            color: #fff;
            transform: translateX(3px);
        }

        .left-panel .logo {
            width: 100px;
            margin-bottom: 20px;
            filter: drop-shadow(0 5px 10px rgba(0, 0, 0, 0.3));
            transition: transform 0.3s ease;
        }

        .left-panel .logo:hover {
            transform: scale(1.1);
        }

        .left-panel h1 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #1e90ff;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .left-panel p {
            font-size: 0.9rem;
            opacity: 0.8;
            line-height: 1.3;
        }

        .system-info {
            margin-top: 15px;
            font-size: 0.85rem;
        }

        .status i {
            color: #1e90ff;
            margin-left: 4px;
        }

        .right-panel {
            color: var(--text-color);
        }

        .right-panel h2 {
            font-size: 1.6rem;
            color: #ff4040;
            margin-bottom: 25px;
            font-weight: 600;
            text-align: center;
            position: relative;
        }

        .right-panel h2::after {
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

        @media screen and (max-width: 480px) {
            .login-container {
                max-width: 320px;
                padding: 30px;
            }

            .left-panel h1 {
                font-size: 1.5rem;
            }

            .right-panel h2 {
                font-size: 1.4rem;
            }

            .menu-container, .mode-toggle {
                width: 32px;
                height: 32px;
            }

            .dropdown-menu {
                top: 45px;
                min-width: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="mode-toggle" onclick="toggleMode()">
            <i class="fa-solid fa-moon"></i>
        </div>
        <div class="left-panel">
            <div class="menu-container" onclick="toggleMenu()">
                <i class="fa-solid fa-bars menu-icon"></i>
                <i class="fa-solid fa-xmark menu-icon"></i>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="login.php" class="login-option">Login</a>
                    <a href="register.php" class="login-option">Register</a>
                </div>
            </div>
            <img src="image/au-logo.png" alt="Arellano University Logo" class="logo">
            <h1>Arellano University</h1>
            <p>Technical Support Management System</p>
            <div class="system-info">
                <span class="status">ALL USERS <i class="fa-solid fa-circle-check"></i></span>
            </div>
        </div>
        <div class="right-panel">
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

    <script>
        // Load the saved mode from localStorage on page load
        document.addEventListener('DOMContentLoaded', () => {
            const savedMode = localStorage.getItem('themeMode');
            const body = document.body;
            const toggleIcon = document.querySelector('.mode-toggle i');

            if (savedMode === 'light') {
                body.classList.remove('dark-mode');
                body.classList.add('light-mode');
                toggleIcon.classList.replace('fa-moon', 'fa-sun');
            } else {
                body.classList.remove('light-mode');
                body.classList.add('dark-mode');
                toggleIcon.classList.replace('fa-sun', 'fa-moon');
            }

            // Adjust input group focus state based on remembered values
            document.querySelectorAll('.input-group input').forEach(input => {
                if (input.value) {
                    input.parentElement.classList.add('focused');
                }
            });
        });

        function toggleMode() {
            const body = document.body;
            const toggleIcon = document.querySelector('.mode-toggle i');

            if (body.classList.contains('dark-mode')) {
                body.classList.remove('dark-mode');
                body.classList.add('light-mode');
                toggleIcon.classList.replace('fa-moon', 'fa-sun');
                localStorage.setItem('themeMode', 'light');
            } else {
                body.classList.remove('light-mode');
                body.classList.add('dark-mode');
                toggleIcon.classList.replace('fa-sun', 'fa-moon');
                localStorage.setItem('themeMode', 'dark');
            }
        }

        function toggleMenu() {
            const menuContainer = document.querySelector('.menu-container');
            const dropdown = document.getElementById('dropdownMenu');
            menuContainer.classList.toggle('active');
            dropdown.classList.toggle('active');
        }

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

        document.querySelectorAll('.input-group input').forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.classList.add('focused');
            });
            input.addEventListener('blur', () => {
                if (!input.value) {
                    input.parentElement.classList.remove('focused');
                }
            });
        });

        document.addEventListener('click', (e) => {
            const menuContainer = document.querySelector('.menu-container');
            const dropdown = document.getElementById('dropdownMenu');
            if (!menuContainer.contains(e.target)) {
                dropdown.classList.remove('active');
                menuContainer.classList.remove('active');
            }
        });
    </script>
</body>
</html>