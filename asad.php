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
                            echo "<td>
                                    <a href='update-account.php?username=" . htmlspecialchars($row['username']) . "' class='btn update-btn'>
                                        <i class='fa-solid fa-pen'></i>
                                    </a>
                                    <form action='' method='POST' class='inline-form'>
                                        <input type='hidden' name='delete_username' value='" . htmlspecialchars($row['username']) . "'>
                                        <button type='submit' name='btn_confirm_delete' class='btn delete-btn'>
                                            <i class='fa-solid fa-trash'></i>
                                        </button>
                                    </form>
                                  </td>";
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
    </div>

    <div id="deleteConfirmModal" class="modal" style="<?php echo $showDeleteConfirmModal ? 'display: block;' : 'display: none;'; ?>">
        <div class="modal-content">
            <div class="modal-header">
                <span>Confirm Deletion</span>
                <i class="fa-solid fa-exclamation-circle"></i>
            </div>
            <hr>
            <p>Are you sure you want to delete this account?</p>
            <hr>
            <form action="" method="POST" class="modal-form">
                <input type="hidden" name="delete_username" value="<?php echo htmlspecialchars($accountToDelete); ?>">
                <button type="submit" name="btn_delete_yes" class="btn modal-btn-delete"><i class="fa-solid fa-trash me-2"></i> Delete</button>
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

    <footer class="dashboard-footer">
        (Copyright 2025, Loayon, Anna Marie E.)
    </footer>
</div>

<style>
/* General Layout */
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    background: linear-gradient(160deg, #e0e0e0 0%, #b0bec5 100%); /* Body gradient from logins.css */
    min-height: 100vh;
}

.content {
    margin-left: 260px; /* Matches sidebar width */
    padding: 30px;
    min-height: 100vh;
    flex-direction: column;
    gap: 30px;
}

.welcome-box {
            background: linear-gradient(90deg, #1976d2 0%, #d32f2f 100%); /* Blue-to-red gradient from tech-dashboard.php */
            color: #fff;
            padding: 20px 25px;
            width: calc(100% - 260px);
            box-sizing: border-box;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
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
    background: #fafafa; /* Matches login container */
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
    transform: translateZ(5px);
    margin-top: 100px; /* Space for fixed welcome-box */
}

/* Control Panel (Search and Actions) */
.control-panel {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, rgba(25, 118, 210, 0.1), rgba(211, 47, 47, 0.1));
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 25px;
}

.search-container {
    display: flex;
    align-items: center;
    background: #fff;
    border: 2px solid #bdbdbd;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.search-container:hover {
    transform: translateZ(10px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.search-container input {
    padding: 14px;
    width: 300px;
    border: none;
    outline: none;
    font-size: 16px;
    background: transparent;
    color: #757575;
}

.search-container button {
    background: #1976d2;
    color: #fff;
    border: none;
    padding: 14px 18px;
    cursor: pointer;
    border-radius: 0 8px 8px 0;
    transition: all 0.3s ease;
}

.search-container button:hover {
    background: #d32f2f;
    transform: translateZ(5px);
}

.action-buttons {
    display: flex;
    gap: 15px;
}

/* Buttons */
.btn {
    padding: 12px 20px;
    background: #1976d2; /* Primary blue from logins.css */
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn:hover {
    background: #42a5f5; /* Lighter blue for hover, keeps it in the blue family */
    transform: translateZ(10px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
}

.add-btn {
    background: linear-gradient(135deg, #1976d2, #42a5f5); /* Blue gradient for "Add" */
}

.add-btn:hover {
    background: #d32f2f; /* Red for contrast on hover */
}

.update-btn {
    background: #757575; /* Darker grey for "Update", distinct from base grey */
}

.update-btn:hover {
    background: #1976d2; /* Blue on hover for consistency */
}

.delete-btn {
    background: #d32f2f; /* Red for "Delete", aligns with error/warning */
}

.delete-btn:hover {
    background: #b71c1c; /* Darker red for hover, adds depth */
}

.logout-btn {
    background: linear-gradient(135deg, #d32f2f, #f44336); /* Red gradient for "Logout" */
}

.logout-btn:hover {
    background: #1976d2; /* Blue on hover for a clear shift */
}

/* Table Section */
.table-section table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 10px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
}

.table-section th {
    background: linear-gradient(135deg, #1976d2, #42a5f5);
    color: #fff;
    padding: 16px;
    font-size: 16px;
    font-weight: 600;
    text-align: center;
    border-bottom: 2px solid #42a5f5;
}

.table-section td {
    padding: 16px;
    text-align: center;
    font-size: 15px;
    color: #616161;
    background: #fafafa;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.table-section tr {
    transition: all 0.3s ease;
}

.table-section tr:hover {
    transform: translateZ(5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.inline-form {
    display: inline-flex;
    gap: 10px;
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
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background: #fafafa;
    margin: 15% auto;
    padding: 30px;
    border-radius: 10px;
    width: 400px;
    text-align: center;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
    transform: translateZ(20px);
}

.modal-header {
    color: #d32f2f;
    font-size: 24px;
    font-weight: 600;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.modal-content p {
    color: #757575;
    font-size: 16px;
    margin: 15px 0;
}

.modal-btn-delete {
    background: #d32f2f;
}

.modal-btn-delete:hover {
    background: #1976d2;
}

.modal-btn-cancel {
    background: #bdbdbd;
}

.modal-btn-cancel:hover {
    background: #d32f2f;
}

.success-modal {
    background: #fafafa;
    padding: 40px;
    border-radius: 10px;
    width: 400px;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
    transform: translateZ(20px);
}

.modal-icon {
    font-size: 60px;
    color: #1976d2;
    margin-bottom: 20px;
}

.success-modal p {
    font-size: 18px;
    color: #616161;
    margin: 0 0 25px 0;
}

.modal-btn-success {
    background: #1976d2;
}

.modal-btn-success:hover {
    background: #d32f2f;
}

/* Error Messages */
.message-error {
    background: rgba(211, 47, 47, 0.2);
    color: #d32f2f;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    text-align: center;
}

.message-error p {
    margin: 5px 0;
    font-size: 15px;
}

/* Footer */
.dashboard-footer {
    margin-top: 30px;
    bottom: 25px;
    left: 0;
    width: 100%;
    text-align: center;
    text-shadow: 0 2px 5px rgb
}

/* Typography and Reset */
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
}
</style>

<script>
document.getElementById('noButton')?.addEventListener('click', function() {
    document.getElementById('deleteConfirmModal').style.display = 'none';
});

document.getElementById('okButton')?.addEventListener('click', function() {
    window.location.href = 'accounts-management.php';
});
</script>

</body>
</html>