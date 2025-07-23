<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['username']) || strtoupper($_SESSION['usertype']) !== "TECHNICAL") {
    header("location: login-tech.php");
    exit();
}

$GLOBALS['current_page'] = basename($_SERVER['PHP_SELF']);

$showCompleteConfirmModal = false;
$showCompleteSuccessModal = false;
$showDetailsModal = false;
$ticketToComplete = '';
$ticketDetails = [];
$errorMessages = [];

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($link === false) {
    die("Could not connect. " . mysqli_connect_error());
}

if (isset($_POST['btn_confirm_complete'])) {
    $ticketToComplete = trim($_POST['complete_ticketnumber']);
    $sql = "SELECT status, assignedto FROM tblticket WHERE ticketnumber = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $ticketToComplete);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $ticket = mysqli_fetch_assoc($result);
            if ($ticket && $ticket['assignedto'] === $_SESSION['username'] && $ticket['status'] === 'ON-GOING') {
                $showCompleteConfirmModal = true;
            } else {
                $errorMessages[] = "Only ON-GOING tickets assigned to you can be completed.";
            }
        }
        mysqli_stmt_close($stmt);
    }
}

if (isset($_POST['btn_complete_yes'])) {
    $ticketnumber = trim($_POST['complete_ticketnumber']);
    $sql = "UPDATE tblticket SET status = 'WAITING FOR APPROVAL', datecompleted = NOW(), last_updated = NOW() WHERE ticketnumber = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $ticketnumber);
        if (mysqli_stmt_execute($stmt)) {
            $sql_log = "INSERT INTO tbllogs (datelog, timelog, action, module, performedto, performedby) VALUES (?, ?, ?, ?, ?, ?)";
            if ($stmt_log = mysqli_prepare($link, $sql_log)) {
                $date = date("Y-m-d");
                $time = date("H:i:s");
                $action = "Complete";
                $module = "Ticket Management";
                $performedby = $_SESSION['username'];
                mysqli_stmt_bind_param($stmt_log, "ssssss", $date, $time, $action, $module, $ticketnumber, $performedby);
                mysqli_stmt_execute($stmt_log);
                mysqli_stmt_close($stmt_log);
                $showCompleteSuccessModal = true;
            } else {
                $errorMessages[] = "ERROR: Could not log the complete action.";
            }
        } else {
            $errorMessages[] = "ERROR: Could not complete ticket.";
        }
        mysqli_stmt_close($stmt);
    }
}

if (isset($_POST['btn_details'])) {
    $ticketnumber = trim($_POST['details_ticketnumber']);
    $sql = "SELECT * FROM tblticket WHERE ticketnumber = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $ticketnumber);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $ticketDetails = mysqli_fetch_assoc($result);
            if ($ticketDetails) {
                $ticketDetails = array_map('htmlspecialchars', $ticketDetails);
            }
            $showDetailsModal = true;
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Management - AU Technical Support</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="theme-body">
<?php include 'tech-sidebar.php'; ?>
<div class="content">
    <div class="welcome-box">
        <span class="welcome-text"><b>Ticket Management</b></span>
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
                    <input type="text" name="txtsearch" placeholder="Search by Ticket Number, Problem, or Status">
                    <button type="submit" name="btnsearch">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
            </section>
            <section class="table-section">
                <?php
                $username = $_SESSION['username'];
                $sql = "SELECT ticketnumber, problem, datecreated, time, status FROM tblticket WHERE assignedto = ? ORDER BY datecreated DESC";
                if (isset($_POST['btnsearch'])) {
                    $search = '%' . trim($_POST['txtsearch']) . '%';
                    $sql = "SELECT ticketnumber, problem, datecreated, time, status FROM tblticket 
                            WHERE assignedto = ? AND (ticketnumber LIKE ? OR problem LIKE ? OR status LIKE ?) 
                            ORDER BY datecreated DESC";
                }
                if ($stmt = mysqli_prepare($link, $sql)) {
                    if (isset($_POST['btnsearch'])) {
                        mysqli_stmt_bind_param($stmt, "ssss", $username, $search, $search, $search);
                    } else {
                        mysqli_stmt_bind_param($stmt, "s", $username);
                    }
                    if (mysqli_stmt_execute($stmt)) {
                        $result = mysqli_stmt_get_result($stmt);
                        if (mysqli_num_rows($result) > 0) {
                            echo "<table>";
                            echo "<tr><th>Ticket Number</th><th>Problem</th><th>Date</th><th>Time</th><th>Status</th><th>Actions</th></tr>";
                            while ($row = mysqli_fetch_assoc($result)) {
                                $ticketnumber = htmlspecialchars($row['ticketnumber']);
                                $problem = htmlspecialchars($row['problem']);
                                $datecreated = htmlspecialchars($row['datecreated']);
                                $time = date("h:i A", strtotime($row['time']));
                                $status = htmlspecialchars($row['status']);
                                echo "<tr>";
                                echo "<td>$ticketnumber</td>";
                                echo "<td>$problem</td>";
                                echo "<td>$datecreated</td>";
                                echo "<td>$time</td>";
                                echo "<td>$status</td>";
                                echo "<td>";
                                echo "<div class='action-container'>";
                                echo "<div class='action-row'>";
                                echo "<form action='' method='POST' class='inline-form'>";
                                echo "<input type='hidden' name='details_ticketnumber' value='$ticketnumber'>";
                                echo "<button type='submit' name='btn_details' class='btn details-btn'>";
                                echo "<i class='fa-solid fa-info-circle'></i>";
                                echo "</button>";
                                echo "</form>";
                                echo "<form action='' method='POST' class='inline-form'>";
                                echo "<input type='hidden' name='complete_ticketnumber' value='$ticketnumber'>";
                                echo "<button type='submit' name='btn_confirm_complete' class='btn complete-btn'>";
                                echo "<i class='fa-solid fa-check'></i>";
                                echo "</button>";
                                echo "</form>";
                                echo "</div>";
                                echo "</div>";
                                echo "</td>";
                                echo "</tr>";
                            }
                            echo "</table>";
                        } else {
                            echo "<div class='error'>No tickets found.</div>";
                        }
                    }
                    mysqli_stmt_close($stmt);
                }
                mysqli_close($link);
                ?>
            </section>
        </form>
        <div id="completeConfirmModal" class="modal" style="<?php echo $showCompleteConfirmModal ? 'display: block;' : 'display: none;'; ?>">
            <div class="modal-content complete-confirm-modal">
                <div class="modal-header">
                    <span>Confirm Completion</span>
                    <i class="fa-solid fa-exclamation-circle"></i>
                </div>
                <hr>
                <p>Do you want to complete this ticket?</p>
                <hr>
                <form action="" method="POST" class="modal-form">
                    <input type="hidden" name="complete_ticketnumber" value="<?php echo htmlspecialchars($ticketToComplete); ?>">
                    <button type="submit" name="btn_complete_yes" class="btn modal-btn-complete"><i class="fa-solid fa-check me-2"></i> Complete</button>
                    <button type="button" id="noCompleteButton" class="btn modal-btn-cancel">Cancel</button>
                </form>
            </div>
        </div>
        <div id="completeSuccessModal" class="modal" style="<?php echo $showCompleteSuccessModal ? 'display: block;' : 'display: none;'; ?>">
            <div class="modal-content success-modal">
                <div class="modal-icon">
                    <i class="fa-solid fa-check-circle"></i>
                </div>
                <p>Ticket Successfully Completed!</p>
                <button id="okButton" class="btn modal-btn-success">OK</button>
            </div>
        </div>
        <div id="detailsModal" class="modal" style="<?php echo $showDetailsModal ? 'display: block;' : 'display: none;'; ?>">
            <div class="modal-content details-modal">
                <div class="modal-header">
                    <span>Ticket Details</span>
                    <i class="fa-solid fa-info-circle"></i>
                </div>
                <hr>
                <div class="details-container">
                    <div class="details-row">
                        <span class="details-label">Ticket Number:</span>
                        <span class="details-value"><?php echo $ticketDetails['ticketnumber'] ?? ''; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Problem:</span>
                        <span class="details-value"><?php echo $ticketDetails['problem'] ?? ''; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Details:</span>
                        <span class="details-value"><?php echo $ticketDetails['details'] ?? ''; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Status:</span>
                        <span class="details-value"><?php echo $ticketDetails['status'] ?? ''; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Time:</span>
                        <span class="details-value"><?php echo $ticketDetails['time'] ? date("h:i A", strtotime($ticketDetails['time'])) : ''; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Created By:</span>
                        <span class="details-value"><?php echo $ticketDetails['createdby'] ?? ''; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Date Created:</span>
                        <span class="details-value"><?php echo $ticketDetails['datecreated'] ?? ''; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Assigned To:</span>
                        <span class="details-value"><?php echo $ticketDetails['assignedto'] ?? ''; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Date Assigned:</span>
                        <span class="details-value"><?php echo $ticketDetails['dateassigned'] ?? ''; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Date Completed:</span>
                        <span class="details-value"><?php echo $ticketDetails['datecompleted'] ?? ''; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Approved By:</span>
                        <span class="details-value"><?php echo $ticketDetails['approvedby'] ?? ''; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Date Approved:</span>
                        <span class="details-value"><?php echo $ticketDetails['dateapproved'] ?? ''; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Last Updated:</span>
                        <span class="details-value"><?php echo $ticketDetails['last_updated'] ?? ''; ?></span>
                    </div>
                </div>
                <hr>
                <div class="button-container">
                    <button id="closeDetailsButton" class="btn modal-btn-close">Close</button>
                </div>
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

    /* Control Panel (Search) */
    .control-panel {
        display: flex;
        justify-content: center;
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

    .details-btn {
        background: #bdbdbd;
    }

    .details-btn:hover {
        background: #d32f2f;
    }

    .complete-btn {
        background: linear-gradient(135deg, #1976d2, #42a5f5);
    }

    .complete-btn:hover {
        background: #d32f2f;
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

    .details-modal {
        width: 620px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .details-container {
        text-align: left;
        margin: 1.5rem 0;
    }

    .details-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid #eceff1;
    }

    .details-label {
        font-weight: 600;
        color: #757575;
        width: 40%;
    }

    .details-value {
        color: #616161;
        width: 60%;
        word-wrap: break-word;
    }

    .button-container {
        display: flex;
        justify-content: center;
        margin-top: 1.5rem;
    }

    .modal-btn-close {
        background: linear-gradient(135deg, #1976d2, #42a5f5);
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

    .modal-btn-close:hover {
        background: #d32f2f;
        transform: translateZ(10px);
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

    document.getElementById('noCompleteButton')?.addEventListener('click', function() {
        document.getElementById('completeConfirmModal').style.display = 'none';
    });
    document.getElementById('okButton')?.addEventListener('click', function() {
        window.location.href = 'tech-ticket-management.php';
    });
    document.getElementById('closeDetailsButton')?.addEventListener('click', function() {
        document.getElementById('detailsModal').style.display = 'none';
    });
</script>
</body>
</html>