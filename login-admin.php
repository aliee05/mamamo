<?php
if(isset($_POST['btnlogin'])) {
    require_once "config.php";
    $sql = "SELECT * FROM tblaccounts WHERE username = ? AND password = ? AND status = 'ACTIVE'";
    if($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $_POST['txtusername'], $_POST['txtpassword']);
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if(mysqli_num_rows($result) > 0) {
                $accounts = mysqli_fetch_array($result, MYSQLI_ASSOC);
                if ($accounts['usertype'] === 'ADMINISTRATOR') {
                    session_start();
                    $_SESSION['username'] = $accounts['username'];
                    $_SESSION['usertype'] = $accounts['usertype'];
                    header("location: dashboard.php");
                    exit();
                } else {
                    $error_message = "<span class='error-message'><i class='fa-solid fa-exclamation-circle'></i>Only Administrators can log in here.</span>";
                }
            } else {
                $error_message = "<span class='error-message'><i class='fa-solid fa-exclamation-circle'></i> Incorrect login details or account is inactive.</span>";
            }
        } else {
            $error_message = "<span class='error-message'><i class='fa-solid fa-database'></i> Error on the login statement.</span>";
        }
    } else {
        $error_message = "<span class='error-message'><i class='fa-solid fa-gears'></i> Error on the login statement.</span>";
    }
}
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
    <link rel="stylesheet" href="stylesheets/logins.css">
</head>
<body>
    <div class="login-container">
        <div class="left-panel">
            <div class="menu-container" onclick="toggleMenu()">
                <i class="fa-solid fa-bars menu-icon"></i>
                <i class="fa-solid fa-xmark menu-icon"></i>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="login-admin.php" class="login-option">Administrator</a>
                    <a href="login-tech.php" class="login-option">Technical</a>
                    <a href="login-user.php" class="login-option">User</a>
                </div>
            </div>
            <img src="image/au-logo.png" alt="Arellano University Logo" class="logo">
            <h1>Arellano University</h1>
            <p>Technical Support Management System</p>
            <div class="system-info">
                <span class="status">ADMINISTRATOR <i class="fa-solid fa-circle-check"></i></span>
            </div>
        </div>
        <div class="right-panel">
            <h2>Login</h2>
            <?php if(isset($error_message)) { echo $error_message; } ?>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" autocomplete="off">
                <div class="input-group">
                    <input type="text" name="txtusername" id="username" required>
                    <label for="username">Username</label>
                </div>
                <div class="input-group password-group">
                    <input type="password" name="txtpassword" id="password" required>
                    <label for="password">Password</label>
                    <i class="fa-solid fa-eye toggle-password" onclick="togglePassword()"></i>
                </div>
                <div class="options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember"> Remember me
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
        function toggleMenu() {
            const menuContainer = document.querySelector('.menu-container');
            const dropdown = document.getElementById('dropdownMenu');
            menuContainer.classList.toggle('active');
            dropdown.classList.toggle('active');
        }

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password');
            if(passwordInput.type === 'password') {
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
                if(!input.value) {
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