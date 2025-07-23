<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['username'])) {
    header("location: login.php");
    exit();
}

$showDeleteConfirmModal = false;
$showDeleteSuccessModal = false;
$accountToDelete = '';
$errorMessages = [];

if (isset($_POST['btn_confirm_delete'])) {
    $accountToDelete = trim($_POST['delete_username']);
    $showDeleteConfirmModal = true;
}

if (isset($_POST['btn_delete_yes'])) {
    $username = trim($_POST['delete_username']);
    $sql = "DELETE FROM tblaccounts WHERE username = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        if (mysqli_stmt_execute($stmt)) {
            $sql = "INSERT INTO tbllogs (datelog, timelog, action, module, performedto, performedby) VALUES (?, ?, ?, ?, ?, ?)";
            if ($logStmt = mysqli_prepare($link, $sql)) {
                $date = date("Y-m-d");
                $time = date("H:i:s");
                $action = "Delete";
                $module = "Account Management";
                $performedby = $_SESSION['username'];
                mysqli_stmt_bind_param($logStmt, "ssssss", $date, $time, $action, $module, $username, $performedby);
                if (!mysqli_stmt_execute($logStmt)) {
                    $errorMessages[] = "Log insert failed: " . mysqli_stmt_error($logStmt);
                }
                mysqli_stmt_close($logStmt);
            } else {
                $errorMessages[] = "Log prepare failed: " . mysqli_error($link);
            }
            $showDeleteSuccessModal = empty($errorMessages);
        } else {
            $errorMessages[] = "Delete failed: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        $errorMessages[] = "Delete prepare failed: " . mysqli_error($link);
    }
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Management - AU Technical Support</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="theme-body">
<?php include 'sidebar.php'; ?>
<div class="content">
    <div class="welcome-box">
        <span class="welcome-text"><b>Account Management</b></span>
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
            <section class="control-panel">
                <div class="search-container">
                    <input type="text" name="txtsearch" placeholder="Search Account">
                    <button type="submit" name="btnsearch">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
                <div class="action-buttons">
                    <a href="create-account.php" class="btn add-btn"><i class="fa-solid fa-user-plus"></i> Add</a>
                    <a href="login.php" class="btn logout-btn"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
                </div>
            </section>
            <section class="table-section">
                <?php
                $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
                if ($link === false) {
                    die("ERROR: Could not connect. " . mysqli_connect_error());
                }

                $sql = "SELECT username, usertype, status, createdby, datecreated FROM tblaccounts ORDER BY username";
                if (isset($_POST['btnsearch'])) {
                    $search = '%' . $_POST['txtsearch'] . '%';
                    $sql = "SELECT username, usertype, status, createdby, datecreated FROM tblaccounts 
                            WHERE username LIKE ? OR usertype LIKE ? ORDER BY username";
                }

                if ($stmt = mysqli_prepare($link, $sql)) {
                    if (isset($_POST['btnsearch'])) {
                        mysqli_stmt_bind_param($stmt, 'ss', $search, $search);
                    }
                    if (mysqli_stmt_execute($stmt)) {
                        $result = mysqli_stmt_get_result($stmt);
                        buildTable($result);
                    } else {
                        echo "<div class='error'>ERROR: Could not execute query: " . mysqli_stmt_error($stmt) . "</div>";
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    echo "<div class='error'>ERROR: Could not prepare query: " . mysqli_error($link) . "</div>";
                }

                mysqli_close($link);

                function buildTable($result) {
                    if (mysqli_num_rows($result) > 0) {
                        echo "<table>";
                        echo "<tr><th>Username</th><th>Account Type</th><th>Status</th><th>Created By</th><th>Date Created</th><th>Actions</th></tr>";
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['usertype']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['createdby']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['datecreated']) . "</td>";
                            echo "<td>";
                            echo "<div class='action-container'>";
                            echo "<div class='action-row'>";
                            echo "<a href='update-account.php?username=" . htmlspecialchars($row['username']) . "' class='btn update-btn'>";
                            echo "<i class='fa-solid fa-pen'></i>";
                            echo "</a>";
                            echo "<form action='' method='POST' class='inline-form'>";
                            echo "<input type='hidden' name='delete_username' value='" . htmlspecialchars($row['username']) . "'>";
                            echo "<button type='submit' name='btn_confirm_delete' class='btn delete-btn'>";
                            echo "<i class='fa-solid fa-trash'></i>";
                            echo "</button>";
                            echo "</form>";
                            echo "</div>";
                            echo "</div>";
                            echo "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    } else {
                        echo "<div class='error'>No records found.</div>";
                    }
                }
                ?>
            </section>
        </form>
        <div id="deleteConfirmModal" class="modal" style="<?php echo $showDeleteConfirmModal ? 'display: block;' : 'display: none;'; ?>">
            <div class="modal-content complete-confirm-modal">
                <div class="modal-header">
                    <span>Confirm Deletion</span>
                    <i class="fa-solid fa-exclamation-circle"></i>
                </div>
                <hr>
                <p>Are you sure you want to delete this account?</p>
                <hr>
                <form action="" method="POST" class="modal-form">
                    <input type="hidden" name="delete_username" value="<?php echo htmlspecialchars($accountToDelete); ?>">
                    <button type="submit" name="btn_delete_yes" class="btn modal-btn-complete"><i class="fa-solid fa-trash me-2"></i> Delete</button>
                    <button type="button" id="noButton" class="btn modal-btn-cancel">Cancel</button>
                </form>
            </div>
        </div>
        <div id="deleteSuccessModal" class="modal" style="<?php echo $showDeleteSuccessModal ? 'display: block;' : 'display: none;'; ?>">
            <div class="modal-content success-modal">
                <div class="modal-icon">
                    <i class="fa-solid fa-check-circle"></i>
                </div>
                <p>Account Successfully Deleted!</p>
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
        max-width: 1200px;
        width: 100%;
        background: var(--card-front-bg);
        border-radius: 1rem;
        box-shadow: 0 15px 30px var(--shadow-color);
        padding: 2rem;
        margin-top: 100px;
        transform: translateZ(5px);
        border: 2px solid var(--card-front-border);
    }

    /* Control Panel (Search and Actions) */
    .control-panel {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .search-container {
        display: flex;
        align-items: center;
        background: #fff;
        border: 2px solid #bdbdbd;
        border-radius: 0.3rem;
        box-shadow: 0 5px 15px var(--shadow-color);
        transition: all 0.3s ease;
        width: 400px;
    }

    .search-container:hover {
        border-color: #1976d2;
        box-shadow: 0 8px 20px rgba(25, 118, 210, 0.2);
    }

    .search-container input {
        padding: 0.7rem;
        width: 100%;
        border: none;
        outline: none;
        font-size: 1rem;
        color: #616161;
        background: transparent;
    }

    .search-container input:focus {
        color: #1976d2;
    }

    .search-container button {
        background: linear-gradient(135deg, #1976d2, #42a5f5);
        color: #fff;
        border: none;
        padding: 0.7rem 1rem;
        cursor: pointer;
        border-radius: 0 0.3rem 0.3rem 0;
        transition: all 0.3s ease;
    }

    .search-container button:hover {
        background: #d32f2f;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
    }

    /* Buttons */
    .btn {
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, #1976d2, #42a5f5);
        color: #fff;
        border: none;
        border-radius: 0.3rem;
        font-size: 0.95rem;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 5px 15px var(--shadow-color);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn:hover {
        background: #d32f2f;
        transform: translateZ(5px);
        box-shadow: 0 8px 20px rgba(211, 47, 47, 0.3);
    }

    .add-btn {
        background: linear-gradient(135deg, #1976d2, #42a5f5);
    }

    .add-btn:hover {
        background: #d32f2f;
    }

    .update-btn {
        background: #bdbdbd;
    }

    .update-btn:hover {
        background: #d32f2f;
    }

    .delete-btn {
        background: linear-gradient(135deg, #d32f2f, #f44336);
    }

    .delete-btn:hover {
        background: #b71c1c;
    }

    .logout-btn {
        background: linear-gradient(135deg, #d32f2f, #f44336);
    }

    .logout-btn:hover {
        background: #1976d2;
    }

    /* Table Section */
    .table-section table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.5rem;
        color: var(--card-front-text);
    }

    .table-section th {
        background: linear-gradient(135deg, #1976d2, #42a5f5);
        color: #fff;
        padding: 1rem;
        font-size: 1rem;
        font-weight: 600;
        text-align: center;
        border-bottom: 2px solid var(--card-front-border);
    }

    .table-section td {
        padding: 1rem;
        text-align: center;
        font-size: 0.95rem;
        background: rgba(255, 255, 255, 0.1);
        box-shadow: 0 3px 6px var(--shadow-color);
        border-radius: 0.3rem;
    }

    .table-section tr {
        transition: all 0.3s ease;
    }

    .table-section tr:hover {
        transform: translateY(-2px);
        background: rgba(255, 255, 255, 0.2);
    }

    .action-container {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
    }

    .action-row {
        display: flex;
        gap: 0.5rem;
    }

    .inline-form {
        display: inline-flex;
        align-items: center;
    }

    /* Modals */
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content {
        background: #fafafa;
        margin: 15% auto;
        padding: 2rem;
        border-radius: 12px;
        width: 420px;
        text-align: center;
        box-shadow: 0 15px 30px var(--shadow-color);
        transform: translateZ(20px);
    }

    .modal-header {
        color: #d32f2f;
        font-size: 1.5rem;
        font-weight: 600;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .modal-content p {
        color: #616161;
        font-size: 1.1rem;
        margin: 1rem 0;
    }

    .modal-btn-complete {
        background: linear-gradient(135deg, #1976d2, #42a5f5);
    }

    .modal-btn-complete:hover {
        background: #d32f2f;
    }

    .modal-btn-cancel {
        background: #bdbdbd;
    }

    .modal-btn-cancel:hover {
        background: #d32f2f;
    }

    .success-modal {
        width: 320px;
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

    .error {
        text-align: center;
        font-size: 1rem;
        color: var(--error-text);
        background: var(--error-bg);
        padding: 0.7rem;
        border-radius: 0.5rem;
        box-shadow: 0 5px 15px var(--shadow-color);
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

    document.getElementById('noButton')?.addEventListener('click', function() {
        document.getElementById('deleteConfirmModal').style.display = 'none';
    });

    document.getElementById('okButton')?.addEventListener('click', function() {
        window.location.href = 'accounts-management.php';
    });
</script>
</body>
</html>