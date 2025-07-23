<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['username'])) {
    header("location: login-user.php");
    exit();
}

$GLOBALS['current_page'] = basename($_SERVER['PHP_SELF']);

// Initialize variables
$errorMessages = [];
$successMessage = "";
$userDetails = [];

// Fetch user details from the database
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($link === false) {
    $errorMessages[] = "ERROR: Could not connect to database.";
} else {
    $sql = "SELECT username, email FROM tblusers WHERE username = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $_SESSION['username']);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $userDetails = mysqli_fetch_assoc($result) ?? [];
            if ($userDetails) {
                $userDetails = array_map('htmlspecialchars', $userDetails);
            } else {
                $errorMessages[] = "ERROR: User not found.";
            }
        } else {
            $errorMessages[] = "ERROR: Could not fetch user details: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        $errorMessages[] = "ERROR: Could not prepare query: " . mysqli_error($link);
    }
}

// Handle profile update
if (isset($_POST['btn_update'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate email
    if (empty($email)) {
        $errorMessages[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessages[] = "Invalid email format.";
    }

    // Validate password if provided
    if (!empty($password) || !empty($confirm_password)) {
        if ($password !== $confirm_password) {
            $errorMessages[] = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $errorMessages[] = "Password must be at least 6 characters long.";
        }
    }

    // If no errors, proceed with update
    if (empty($errorMessages)) {
        if ($link) {
            if (!empty($password)) {
                // Update both email and password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE tblusers SET email = ?, password = ? WHERE username = ?";
                if ($stmt = mysqli_prepare($link, $sql)) {
                    mysqli_stmt_bind_param($stmt, "sss", $email, $hashed_password, $_SESSION['username']);
                    if (mysqli_stmt_execute($stmt)) {
                        $successMessage = "Profile updated successfully!";
                        $userDetails['email'] = $email; // Update displayed email
                    } else {
                        $errorMessages[] = "ERROR: Could not update profile: " . mysqli_stmt_error($stmt);
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $errorMessages[] = "ERROR: Could not prepare update query: " . mysqli_error($link);
                }
            } else {
                // Update only email
                $sql = "UPDATE tblusers SET email = ? WHERE username = ?";
                if ($stmt = mysqli_prepare($link, $sql)) {
                    mysqli_stmt_bind_param($stmt, "ss", $email, $_SESSION['username']);
                    if (mysqli_stmt_execute($stmt)) {
                        $successMessage = "Profile updated successfully!";
                        $userDetails['email'] = $email; // Update displayed email
                    } else {
                        $errorMessages[] = "ERROR: Could not update profile: " . mysqli_stmt_error($stmt);
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $errorMessages[] = "ERROR: Could not prepare update query: " . mysqli_error($link);
                }
            }
        }
    }
}

if ($link) {
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - AU Technical Support</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="theme-body">
    <?php include 'user-sidebar.php'; ?>

    <div class="content">
        <div class="welcome-box">
            <span>Profile</span>
        </div>

        <div class="container">
            <?php if (!empty($errorMessages)): ?>
                <div class="message-error">
                    <?php foreach ($errorMessages as $message): ?>
                        <p><?php echo htmlspecialchars($message); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($successMessage)): ?>
                <div class="message-success">
                    <p><?php echo htmlspecialchars($successMessage); ?></p>
                </div>
            <?php endif; ?>

            <div class="profile-card">
                <div class="profile-header">
                    <i class="fa-solid fa-user-circle profile-icon"></i>
                    <h2>User Profile</h2>
                </div>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="profile-form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo $userDetails['username'] ?? ''; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo $userDetails['email'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">New Password (Leave blank to keep current)</label>
                        <input type="password" id="password" name="password" placeholder="Enter new password">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="btn_update" class="btn update-btn">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* General Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
        }

        .theme-body {
            background: #fafafa; /* Matches user-dashboard.php */
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
            background: linear-gradient(90deg, #1976d2 0%, #d32f2f 100%); /* Blue-to-red gradient from dashboard.php */
            color: #fff;
            padding: 20px 25px;
            width: calc(100% - 260px);
            box-sizing: border-box;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            position: fixed;
            top: 138px;
            left: 260px;
            z-index: 998;
            transition: all 0.3s ease;
        }

        .welcome-box:hover {
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        .container {
            max-width: 1200px;
            width: 100%;
            margin-top: 100px;
            padding: 2rem;
        }

        /* Profile Card */
        .profile-card {
            background: #fff; /* White background from user-dashboard.php */
            border: 2px solid #1976d2; /* Blue border from user-dashboard.php */
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
            transition: transform 0.3s ease;
        }

        .profile-card:hover {
            transform: translateY(-5px) scale(1.03);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        .profile-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2rem;
        }

        .profile-icon {
            font-size: 64px;
            color: #d32f2f; /* Red icon color from user-dashboard.php */
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
        }

        .profile-card:hover .profile-icon {
            transform: scale(1.15) rotate(5deg);
        }

        .profile-header h2 {
            font-size: 2rem;
            font-weight: 600;
            color: #616161;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        /* Form Styles */
        .profile-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            font-size: 1.1rem;
            font-weight: 500;
            color: #616161;
        }

        .form-group input {
            padding: 12px;
            font-size: 1rem;
            border: 2px solid #b0bec5;
            border-radius: 8px;
            outline: none;
            transition: all 0.3s ease;
            color: #616161;
        }

        .form-group input:focus {
            border-color: #1976d2;
            box-shadow: 0 0 8px rgba(25, 118, 210, 0.3);
        }

        .form-group input[readonly] {
            background: #f5f5f5;
            cursor: not-allowed;
        }

        .form-actions {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }

        .btn {
            padding: 12px 30px;
            background: linear-gradient(135deg, #1976d2, #42a5f5); /* Blue gradient from user-dashboard.php */
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .update-btn:hover {
            background: linear-gradient(135deg, #d32f2f, #ff5252); /* Red gradient for hover */
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        /* Messages */
        .message-error {
            background: rgba(255, 82, 82, 0.9);
            color: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            text-align: center;
            animation: slideIn 0.3s ease;
        }

        .message-success {
            background: rgba(40, 167, 69, 0.9);
            color: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            text-align: center;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .message-error p, .message-success p {
            margin: 5px 0;
            font-size: 15px;
        }
    </style>
</body>
</html>