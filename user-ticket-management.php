<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['username'])) {
    header("location: login-user.php");
    exit();
}

$GLOBALS['current_page'] = basename($_SERVER['PHP_SELF']);

$showDeleteConfirmModal = false;
$showDeleteSuccessModal = false;
$showDetailsModal = false;
$showUpdateConfirmModal = false;
$ticketToDelete = '';
$ticketToUpdate = '';
$ticketDetails = [];
$errorMessages = [];

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($link === false) {
    $errorMessages[] = "ERROR: Could not connect to database.";
}

if (isset($_POST['btn_confirm_delete'])) {
    $ticketToDelete = trim($_POST['delete_ticketnumber']);
    if ($link) {
        $sql = "SELECT status FROM tblticket WHERE ticketnumber = ? AND createdby = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $ticketToDelete, $_SESSION['username']);
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                $ticket = mysqli_fetch_assoc($result);
                if ($ticket && $ticket['status'] === 'CLOSED') {
                    $showDeleteConfirmModal = true;
                } else {
                    $errorMessages[] = "Only tickets with status 'CLOSED' can be deleted.";
                }
            } else {
                $errorMessages[] = "ERROR: Could not check ticket status: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $errorMessages[] = "ERROR: Could not prepare delete check query: " . mysqli_error($link);
        }
    }
}

if (isset($_POST['btn_delete_yes'])) {
    $ticketnumber = trim($_POST['delete_ticketnumber']);
    if ($link) {
        $sql = "SELECT status FROM tblticket WHERE ticketnumber = ? AND createdby = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $ticketnumber, $_SESSION['username']);
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                $ticket = mysqli_fetch_assoc($result);
                if ($ticket && $ticket['status'] === 'CLOSED') {
                    $sql = "DELETE FROM tblticket WHERE ticketnumber = ?";
                    if ($stmt = mysqli_prepare($link, $sql)) {
                        mysqli_stmt_bind_param($stmt, "s", $ticketnumber);
                        if (mysqli_stmt_execute($stmt)) {
                            $sql = "INSERT INTO tbllogs (datelog, timelog, action, module, performedto, performedby) VALUES (?, ?, ?, ?, ?, ?)";
                            if ($logStmt = mysqli_prepare($link, $sql)) {
                                $date = date("Y-m-d");
                                $time = date("H:i:s");
                                $action = "Delete";
                                $module = "Ticket Management";
                                $performedby = $_SESSION['username'];
                                mysqli_stmt_bind_param($logStmt, "ssssss", $date, $time, $action, $module, $ticketnumber, $performedby);
                                if (mysqli_stmt_execute($logStmt)) {
                                    $showDeleteSuccessModal = true;
                                } else {
                                    $errorMessages[] = "ERROR: Could not log the delete action: " . mysqli_stmt_error($logStmt);
                                }
                                mysqli_stmt_close($logStmt);
                            } else {
                                $errorMessages[] = "ERROR: Could not prepare log insertion: " . mysqli_error($link);
                            }
                        } else {
                            $errorMessages[] = "ERROR: Could not delete ticket: " . mysqli_stmt_error($stmt);
                        }
                        mysqli_stmt_close($stmt);
                    } else {
                        $errorMessages[] = "ERROR: Could not prepare delete query: " . mysqli_error($link);
                    }
                } else {
                    $errorMessages[] = "Only tickets with status 'CLOSED' can be deleted.";
                }
            } else {
                $errorMessages[] = "ERROR: Could not verify ticket status: " . mysqli_stmt_error($stmt);
            }
        } else {
            $errorMessages[] = "ERROR: Could not prepare status check query: " . mysqli_error($link);
        }
    }
}

if (isset($_POST['btn_details'])) {
    $ticketnumber = trim($_POST['details_ticketnumber']);
    if ($link) {
        $sql = "SELECT ticketnumber, problem, details, status, createdby, datecreated, assignedto, dateassigned, datecompleted, approvedby, dateapproved, last_updated FROM tblticket WHERE ticketnumber = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $ticketnumber);
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                $ticketDetails = mysqli_fetch_assoc($result) ?? [];
                if ($ticketDetails) {
                    $ticketDetails = array_map('htmlspecialchars', $ticketDetails);
                    $showDetailsModal = true;
                }
            } else {
                $errorMessages[] = "ERROR: Could not fetch ticket details: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $errorMessages[] = "ERROR: Could not prepare details query: " . mysqli_error($link);
        }
    }
}

if (isset($_POST['btn_confirm_update'])) {
    $ticketToUpdate = trim($_POST['update_ticketnumber']);
    if ($link) {
        $sql = "SELECT status FROM tblticket WHERE ticketnumber = ? AND createdby = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $ticketToUpdate, $_SESSION['username']);
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                $ticket = mysqli_fetch_assoc($result);
                if ($ticket && ($ticket['status'] === 'PENDING' || $ticket['status'] === 'ON-GOING')) {
                    $showUpdateConfirmModal = true;
                } else {
                    $errorMessages[] = "Only tickets with status 'PENDING' or 'ON-GOING' can be updated.";
                }
            } else {
                $errorMessages[] = "ERROR: Could not check ticket status: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $errorMessages[] = "ERROR: Could not prepare update check query: " . mysqli_error($link);
        }
    }
}

if (isset($_POST['btn_update_yes'])) {
    $ticketnumber = trim($_POST['update_ticketnumber']);
    if ($link) {
        $sql = "SELECT status FROM tblticket WHERE ticketnumber = ? AND createdby = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $ticketnumber, $_SESSION['username']);
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                $ticket = mysqli_fetch_assoc($result);
                if ($ticket && ($ticket['status'] === 'PENDING' || $ticket['status'] === 'ON-GOING')) {
                    header("location: user-update.php?ticketnumber=" . urlencode($ticketnumber));
                    exit();
                } else {
                    $errorMessages[] = "Only tickets with status 'PENDING' or 'ON-GOING' can be updated.";
                }
            } else {
                $errorMessages[] = "ERROR: Could not verify ticket status: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $errorMessages[] = "ERROR: Could not prepare status check query: " . mysqli_error($link);
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
    <title>Ticket Management - AU Technical Support</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="theme-body">
<?php include 'user-sidebar.php'; ?>
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
                    <input type="text" name="txtsearch" placeholder="Search Ticket">
                    <button type="submit" name="btnsearch">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
                <div class="action-buttons">
                    <a href="user-create-ticket.php" class="btn add-btn"><i class="fa-solid fa-plus"></i> Add</a>
                </div>
            </section>
            <section class="table-section">
                <?php
                $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
                if ($link === false) {
                    die("ERROR: Could not connect. " . mysqli_connect_error());
                }

                $checkTable = "SHOW TABLES LIKE 'tblticket'";
                $tableResult = mysqli_query($link, $checkTable);
                if (mysqli_num_rows($tableResult) == 0) {
                    echo "<div class='error'>ERROR: Table 'tblticket' does not exist. Please create it in the database.</div>";
                } else {
                    $sql = "SELECT ticketnumber, problem, datecreated, status FROM tblticket WHERE createdby = ? ORDER BY datecreated DESC";
                    if (isset($_POST['btnsearch'])) {
                        $search = '%' . $_POST['txtsearch'] . '%';
                        $sql = "SELECT ticketnumber, problem, datecreated, status FROM tblticket 
                                WHERE createdby = ? AND (ticketnumber LIKE ? OR problem LIKE ? OR status LIKE ?) 
                                ORDER BY datecreated DESC";
                    }

                    if ($stmt = mysqli_prepare($link, $sql)) {
                        if (isset($_POST['btnsearch'])) {
                            mysqli_stmt_bind_param($stmt, "ssss", $_SESSION['username'], $search, $search, $search);
                        } else {
                            mysqli_stmt_bind_param($stmt, "s", $_SESSION['username']);
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
                }

                mysqli_close($link);

                function buildTable($result) {
                    if (mysqli_num_rows($result) > 0) {
                        echo "<table>";
                        echo "<tr><th>Ticket Number</th><th>Problem</th><th>Date Created</th><th>Status</th><th>Actions</th></tr>";
                        while ($row = mysqli_fetch_assoc($result)) {
                            $ticketnumber = htmlspecialchars($row['ticketnumber']);
                            $problem = htmlspecialchars($row['problem']);
                            $datecreated = htmlspecialchars($row['datecreated']);
                            $status = htmlspecialchars($row['status']);
                            echo "<tr>";
                            echo "<td>$ticketnumber</td>";
                            echo "<td>$problem</td>";
                            echo "<td>$datecreated</td>";
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
                            echo "<input type='hidden' name='update_ticketnumber' value='$ticketnumber'>";
                            echo "<button type='submit' name='btn_confirm_update' class='btn update-btn'>";
                            echo "<i class='fa-solid fa-pen'></i>";
                            echo "</button>";
                            echo "</form>";
                            echo "<form action='' method='POST' class='inline-form'>";
                            echo "<input type='hidden' name='delete_ticketnumber' value='$ticketnumber'>";
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
                        echo "<div class='error'>No tickets found.</div>";
                    }
                }
                ?>
            </section>
        </form>
        <div id="deleteConfirmModal" class="modal" style="<?php echo $showDeleteConfirmModal ? 'display: block;' : 'display: none;'; ?>">
            <div class="modal-content delete-confirm-modal">
                <div class="modal-header">
                    <span>Confirm Deletion</span>
                    <i class="fa-solid fa-exclamation-circle"></i>
                </div>
                <hr>
                <p>Do you want to delete this ticket?</p>
                <hr>
                <form action="" method="POST" class="modal-form">
                    <input type="hidden" name="delete_ticketnumber" value="<?php echo htmlspecialchars($ticketToDelete); ?>">
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
                <p>Ticket Successfully Deleted!</p>
                <button id="okButton" class="btn modal-btn-success">OK</button>
            </div>
        </div>
        <div id="updateConfirmModal" class="modal" style="<?php echo $showUpdateConfirmModal ? 'display: block;' : 'display: none;'; ?>">
            <div class="modal-content update-confirm-modal">
                <div class="modal-header">
                    <span>Confirm Update</span>
                    <i class="fa-solid fa-exclamation-circle"></i>
                </div>
                <hr>
                <p>Do you want to update this ticket?</p>
                <hr>
                <form action="" method="POST" class="modal-form">
                    <input type="hidden" name="update_ticketnumber" value="<?php echo htmlspecialchars($ticketToUpdate); ?>">
                    <button type="submit" name="btn_update_yes" class="btn modal-btn-complete"><i class="fa-solid fa-pen me-2"></i> Update</button>
                    <button type="button" id="noUpdateButton" class="btn modal-btn-cancel">Cancel</button>
                </form>
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
                        <span class="details-value"><?php echo $ticketDetails['ticketnumber'] ?? 'N/A'; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Problem:</span>
                        <span class="details-value"><?php echo $ticketDetails['problem'] ?? 'N/A'; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Details:</span>
                        <span class="details-value"><?php echo $ticketDetails['details'] ?? 'N/A'; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Status:</span>
                        <span class="details-value"><?php echo $ticketDetails['status'] ?? 'N/A'; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Created By:</span>
                        <span class="details-value"><?php echo $ticketDetails['createdby'] ?? 'N/A'; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Date Created:</span>
                        <span class="details-value"><?php echo $ticketDetails['datecreated'] ?? 'N/A'; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Assigned To:</span>
                        <span class="details-value"><?php echo $ticketDetails['assignedto'] ?? 'N/A'; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Date Assigned:</span>
                        <span class="details-value"><?php echo $ticketDetails['dateassigned'] ?? 'N/A'; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Date Completed:</span>
                        <span class="details-value"><?php echo $ticketDetails['datecompleted'] ?? 'N/A'; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Approved By:</span>
                        <span class="details-value"><?php echo $ticketDetails['approvedby'] ?? 'N/A'; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Date Approved:</span>
                        <span class="details-value"><?php echo $ticketDetails['dateapproved'] ?? 'N/A'; ?></span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Last Updated:</span>
                        <span class="details-value"><?php echo $ticketDetails['last_updated'] ?? 'N/A'; ?></span>
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
,--welcome-text: #ffffff;
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
        gap: 0.5rem;
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
        flex-direction: row;
        justify-content: center;
        gap: 0.5rem;
    }

    .action-row {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
    }

    .inline-form {
        display: inline-flex;
        align-items: center;
    }

    /* Buttons */
    .logout-btn {
        background: linear-gradient(135deg, #d32f2f, #f44336);
    }

    .logout-btn:hover {
        background: #1976d2;
    }

    .add-btn {
        background: linear-gradient(135deg, #1976d2, #42a5f5);
    }

    .add-btn:hover {
        background: #d32f2f;
    }

    .details-btn {
        background: #bdbdbd;
    }

    .details-btn:hover {
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
        width: 600px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .details-container {
        text-align: left;
        margin: 20px 0;
    }

    .details-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e0e0e0;
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
        margin-top: 20px;
    }

    .modal-btn-close {
        background: linear-gradient(135deg, #1976d2, #42a5f5);
    }

    .modal-btn-close:hover {
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
        window.location.href = 'user-ticket-management.php';
    });

    document.getElementById('noUpdateButton')?.addEventListener('click', function() {
        document.getElementById('updateConfirmModal').style.display = 'none';
    });

    document.getElementById('closeDetailsButton')?.addEventListener('click', function() {
        document.getElementById('detailsModal').style.display = 'none';
    });
</script>
</body>
</html>