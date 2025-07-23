<?php
require_once "config.php";
include "session-checker.php";

$showModal = false;
$errorMessages = [];

if (isset($_POST['btnsubmit'])) {
    $username = trim($_POST['txtusername']);
    $password = trim($_POST['txtpassword']);
    $usertype = trim($_POST['cmbtype']);

    if (empty($username)) {
        $errorMessages[] = "Username is required.";
    } else {
        $sql = "SELECT COUNT(*) FROM tblaccounts WHERE username = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $count);
            mysqli_stmt_fetch($stmt);
            if ($count > 0) {
                $errorMessages[] = "Username '$username' already exists.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $errorMessages[] = "ERROR: Could not verify username uniqueness.";
        }
    }

    if (empty($password)) {
        $errorMessages[] = "Password is required.";
    }

    if (empty($usertype)) {
        $errorMessages[] = "Account Type is required.";
    }

    if (empty($errorMessages)) {
        $sql = "INSERT INTO tblaccounts (username, password, usertype, status, createdby, datecreated) VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            $status = "ACTIVE";
            $date = date("Y-m-d");
            mysqli_stmt_bind_param($stmt, "ssssss", $username, $password, $usertype, $status, $_SESSION['username'], $date);
            if (mysqli_stmt_execute($stmt)) {
                $sql = "INSERT INTO tbllogs (datelog, timelog, action, module, performedto, performedby) VALUES (?, ?, ?, ?, ?, ?)";
                if ($logStmt = mysqli_prepare($link, $sql)) {
                    $time = date("H:i:s");
                    $action = "Add";
                    $module = "Account Management";
                    $performedby = $_SESSION['username'];
                    mysqli_stmt_bind_param($logStmt, "ssssss", $date, $time, $action, $module, $username, $performedby);
                    if (mysqli_stmt_execute($logStmt)) {
                        $showModal = true;
                    } else {
                        $errorMessages[] = "Log insert failed: " . mysqli_stmt_error($logStmt);
                    }
                    mysqli_stmt_close($logStmt);
                } else {
                    $errorMessages[] = "Log prepare failed: " . mysqli_error($link);
                }
            } else {
                $errorMessages[] = "ERROR: Could not add new account.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $errorMessages[] = "ERROR: Could not prepare account insertion.";
        }
    }
}
mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Account - AU Technical Support Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="theme-body">
    <?php include 'sidebar.php'; ?>

    <div class="content">
        <div class="welcome-box">
            <span>Create New Account</span>
        </div>

        <div class="container">
            <?php if (!empty($errorMessages)): ?>
                <div class="message-error">
                    <?php foreach ($errorMessages as $message): ?>
                        <p><?php echo htmlspecialchars($message); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="form-group">
                    <label for="txtusername">Username</label>
                    <input type="text" id="txtusername" name="txtusername" value="<?php echo isset($_POST['txtusername']) ? htmlspecialchars($_POST['txtusername']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="txtpassword">Password</label>
                    <input type="password" id="txtpassword" name="txtpassword" value="<?php echo isset($_POST['txtpassword']) ? htmlspecialchars($_POST['txtpassword']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="cmbtype">Account Type</label>
                    <select id="cmbtype" name="cmbtype" required>
                        <option value="">Select Account Type</option>
                        <option value="ADMINISTRATOR" <?php echo (isset($_POST['cmbtype']) && $_POST['cmbtype'] == 'ADMINISTRATOR') ? 'selected' : ''; ?>>Administrator</option>
                        <option value="TECHNICAL" <?php echo (isset($_POST['cmbtype']) && $_POST['cmbtype'] == 'TECHNICAL') ? 'selected' : ''; ?>>Technical</option>
                        <option value="STAFF" <?php echo (isset($_POST['cmbtype']) && $_POST['cmbtype'] == 'STAFF') ? 'selected' : ''; ?>>Staff</option>
                        <option value="USER" <?php echo (isset($_POST['cmbtype']) && $_POST['cmbtype'] == 'USER') ? 'selected' : ''; ?>>User</option>
                    </select>
                </div>

                <div class="button-group">
                    <input type="submit" name="btnsubmit" value="Submit">
                    <a href="accounts-management.php">Cancel</a>
                </div>
            </form>

            <div id="successModal" class="modal" style="<?php echo $showModal ? 'display: block;' : 'display: none;'; ?>">
                <div class="modal-content success-modal">
                    <div class="modal-icon">
                        <i class="fa-solid fa-check-circle"></i>
                    </div>
                    <p>User Account Successfully Created!</p>
                    <button id="okButton" class="btn modal-btn-success">OK</button>
                </div>
            </div>
        </div>
        <footer class="dashboard-footer">
            (Copyright 2025, Loayon, Anna Marie E.)
        </footer>
    </div>

    <style>
        /* Theme Variables */
        :root {
            --bg-gradient: linear-gradient(145deg, #222 0%, #444 100%);
            --text-color: #ccc;
            --shadow-color: rgba(255, 255, 255, 0.1);
            --welcome-bg: linear-gradient(135deg, #2e4057 0%, #5c1a2f 100%);
            --welcome-text: #cccccc;
            --card-front-bg: rgba(255, 255, 255, 0.05);
            --card-front-border: rgba(255, 255, 255, 0.2);
            --card-front-text: #cccccc;
            --footer-text: #999999;
            --error-bg: rgba(255, 77, 77, 0.2);
            --error-text: #ff4d4d;
        }

        body.light-mode {
            --bg-gradient: linear-gradient(145deg, #f0f0f0 0%, #d9d9d9 100%);
            --text-color: #333;
            --shadow-color: rgba(0, 0, 0, 0.2);
            --welcome-bg: linear-gradient(135deg, #1e90ff, #187bcd);
            --welcome-text: #ffffff;
            --card-front-bg: rgba(255, 255, 255, 0.9);
            --card-front-border: rgba(30, 144, 255, 0.5);
            --card-front-text: #333333;
            --footer-text: #666666;
            --error-bg: rgba(255, 77, 77, 0.1);
            --error-text: #d32f2f;
        }

        /* General Layout */
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background: var(--bg-gradient);
            min-height: 100vh;
        }

        .content {
            margin-left: 260px;
            padding: 2rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .welcome-box {
            background: var(--welcome-bg);
            color: var(--welcome-text);
            padding: 20px 25px;
            width: calc(100% - 260px);
            box-sizing: border-box;
            box-shadow: 0 10px 20px var(--shadow-color);
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            position: fixed;
            top: 55px;
            left: 260px;
            z-index: 998;
            transition: all 0.3s ease;
        }

        .welcome-box:hover {
            box-shadow: 0 15px 30px var(--shadow-color);
        }

        .container {
            max-width: 700px;
            width: 100%;
            background: var(--card-front-bg);
            border-radius: 1rem;
            box-shadow: 0 15px 30px var(--shadow-color);
            padding: 2rem;
            margin-top: 100px;
            transform: translateZ(5px);
            border: 2px solid var(--card-front-border);
        }

        /* Form Styling */
        .form-group {
            display: flex;
            aline-items: center;
            margin: 0 0 1rem 0;
            position: relative;
        }

        .form-group label {
            width: 12rem;
            margin-right: 1rem;
            font-weight: bold;
            font-size: 1.1rem;
            color: #757575;
            background: #fafafa;
            padding: 0.5rem;
            border-radius: 0.3rem;
            box-shadow: 0 5px 15px var(--shadow-color);
            transition: all 0.3s ease;
        }

        .form-group input,
        .form-group select {
            flex: 1;
            padding: 0.7rem;
            border: 2px solid #bdbdbd;
            border-radius: 0.3rem;
            font-size: 1rem;
            background: #fff;
            color: #616161;
            box-shadow: 0 5px 15px var(--shadow-color);
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #1976d2;
            outline: none;
            box-shadow: 0 8px 20px rgba(25, 118, 210, 0.2);
        }

        /* Button Group */
        .button-group {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .button-group input[type="submit"],
        .button-group a {
            background: linear-gradient(135deg, #1976d2, #42a5f5);
            color: #fff;
            border: none;
            padding: 0.9rem 2.5rem;
            font-size: 1.1rem;
            border-radius: 0.3rem;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 5px 15px var(--shadow-color);
            transition: all 0.3s ease;
        }

        .button-group input[type="submit"]:hover,
        .button-group a:hover {
            background: #d32f2f;
            transform: translateZ(10px);
            box-shadow: 0 8px 20px rgba(211, 47, 47, 0.3);
        }

        .button-group a {
            background: #bdbdbd;
        }

        .button-group a:hover {
            background: #d32f2f;
        }

        /* Error Messages */
        .message-error {
            text-align: center;
            font-size: 0.95rem;
            color: var(--error-text);
            background: var(--error-bg);
            margin: 0.3rem 0;
            padding: 0.4rem;
            border-radius: 0.5rem;
            box-shadow: 0 5px 15px var(--shadow-color);
        }

        .message-error p {
            margin: 0.1rem 0;
        }

        /* Modal Styling */
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .success-modal {
            background: #fafafa;
            margin: 15% auto;
            padding: 2rem;
            border-radius: 12px;
            width: 320px;
            text-align: center;
            box-shadow: 0 15px 30px var(--shadow-color);
            transform: translateZ(20px);
        }

        .modal-icon {
            font-size: 48px;
            color: #1976d2;
            margin-bottom: 1rem;
        }

        .success-modal p {
            font-size: 18px;
            color: #616161;
            margin: 0 0 1.5rem 0;
            font-weight: 500;
        }

        .modal-btn-success {
            background: #1976d2;
            padding: 10px 30px;
            border: none;
            color: #fff;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            box-shadow: 0 5px 15px var(--shadow-color);
            transition: all 0.3s ease;
        }

        .modal-btn-success:hover {
            background: #d32f2f;
            transform: translateZ(10px);
            box-shadow: 0 8px 20px rgba(211, 47, 47, 0.3);
        }

        .dashboard-footer {
            margin-top: 50px;
            bottom: 25px;
            left: 0;
            width: 100%;
            text-align: center;
            color: var(--footer-text);
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const savedMode = localStorage.getItem('themeMode');
            const body = document.body;

            if (savedMode === 'light') {
                body.classList.remove('dark-mode');
                body.classList.add('light-mode');
            } else {
                body.classList.remove('light-mode');
                body.classList.add('dark-mode');
            }
        });

        document.getElementById('okButton')?.addEventListener('click', function() {
            window.location.href = 'accounts-management.php';
        });
    </script>
</body>
</html>