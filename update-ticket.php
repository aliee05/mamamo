<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['username'])) {
    header("location: login.php");
    exit();
}

if (!isset($_GET['ticketnumber'])) {
    echo "<script>alert('No ticket selected!'); window.location.href='ticket-management.php';</script>";
    exit();
}

$ticketnumber = $_GET['ticketnumber'];
$ticketUpdated = false;
$errorMessages = [];

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($link === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$sql = "SELECT ticketnumber, problem, details FROM tblticket WHERE ticketnumber = ?";
if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $ticketnumber);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $ticket = mysqli_fetch_assoc($result);
    if (!$ticket) {
        echo "<script>alert('Ticket not found!'); window.location.href='ticket-management.php';</script>";
        exit();
    }
    mysqli_stmt_close($stmt);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $problem = trim($_POST['problem']);
    $details = trim($_POST['details']);
    $updatedby = $_SESSION['username'];

    if (empty($problem)) {
        $errorMessages[] = "Problem is required.";
    }
    if (empty($details)) {
        $errorMessages[] = "Details are required.";
    }

    if (empty($errorMessages)) {
        $sql = "UPDATE tblticket SET problem = ?, details = ?, last_updated = NOW() WHERE ticketnumber = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sss", $problem, $details, $ticketnumber);
            if (mysqli_stmt_execute($stmt)) {
                $sql_log = "INSERT INTO tbllogs (datelog, timelog, action, module, performedto, performedby) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                if ($stmt_log = mysqli_prepare($link, $sql_log)) {
                    $date = date("Y-m-d");
                    $time = date("H:i:s");
                    $action = "Update";
                    $module = "Ticket Management";
                    mysqli_stmt_bind_param($stmt_log, "ssssss", $date, $time, $action, $module, $ticketnumber, $updatedby);
                    mysqli_stmt_execute($stmt_log);
                    mysqli_stmt_close($stmt_log);
                    $ticketUpdated = true;
                } else {
                    $errorMessages[] = "ERROR: Could not log the update action.";
                }
            } else {
                $errorMessages[] = "ERROR: Could not update ticket.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $errorMessages[] = "ERROR: Could not prepare update query.";
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
    <title>Update Ticket - AU Technical Support</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="stylesheets/update-ticket.css">
</head>
<body class="theme-body">
    <?php include 'sidebar.php'; ?>

    <div class="content">
        <div class="welcome-box">
            <span>Update Ticket</span>
        </div>

        <div class="container">
            <?php if (!empty($errorMessages)): ?>
                <div class="message-error">
                    <?php foreach ($errorMessages as $message): ?>
                        <p><?php echo htmlspecialchars($message); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Ticket Number</label>
                    <input type="text" name="ticketnumber" value="<?php echo htmlspecialchars($ticket['ticketnumber']); ?>" disabled readonly>
                </div>

                <div class="form-group">
                    <label>Problem</label>
                    <select name="problem" required>
                        <option value="">Select Problem Type</option>
                        <option value="Hardware" <?php echo (isset($_POST['problem']) ? $_POST['problem'] : $ticket['problem']) == 'Hardware' ? 'selected' : ''; ?>>Hardware</option>
                        <option value="Software" <?php echo (isset($_POST['problem']) ? $_POST['problem'] : $ticket['problem']) == 'Software' ? 'selected' : ''; ?>>Software</option>
                        <option value="Connection" <?php echo (isset($_POST['problem']) ? $_POST['problem'] : $ticket['problem']) == 'Connection' ? 'selected' : ''; ?>>Connection</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Details</label>
                    <textarea name="details" required><?php echo isset($_POST['details']) ? htmlspecialchars($_POST['details']) : htmlspecialchars($ticket['details']); ?></textarea>
                </div>

                <div class="button-group">
                    <input type="submit" name="btn_save" value="Save">
                    <a href="ticket-management.php">Cancel</a>
                </div>
            </form>

            <div id="successModal" class="modal" style="<?php echo $ticketUpdated ? 'display: block;' : 'display: none;'; ?>">
                <div class="modal-content success-modal">
                    <div class="modal-icon">
                        <i class="fa-solid fa-check-circle"></i>
                    </div>
                    <p>Ticket Successfully Updated!</p>
                    <button id="okButton" class="btn modal-btn-success">OK</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .welcome-box {
            background: var(--welcome-bg, #00695C);
            color: white;
            padding: 14px 20px;
            width: calc(100% - 260px);
            margin: 0;
            box-sizing: border-box;
            border-radius: 0;
            font-weight: 500;
            font-size: 20px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            position: fixed;
            top: 60px;
            left: 260px;
            z-index: 998;
        }

        [data-theme="light"] .welcome-box {
            background: #26A69A;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }

        .success-modal {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            margin: 15% auto;
            padding: 2rem;
            border-radius: 12px;
            width: 320px !important;
            text-align: center;
            border: none;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.2), 
                        0 0 25px rgba(40, 167, 69, 0.5),
                        inset 0 2px 6px rgba(255, 255, 255, 0.6);
        }

        .success-modal p {
            font-size: 18px;
            color: #ffffff;
            margin: 0 0 1.5rem 0;
            font-weight: 500;
            text-shadow: none;
        }

        .modal-icon {
            font-size: 48px;
            color: #28a745;
            margin-bottom: 1rem;
        }

        .modal-btn-success {
            background: #28a745 !important;
            padding: 10px 30px;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .modal-btn-success:hover {
            background: #218838 !important;
            transform: translateY(-2px);
        }
    </style>

    <script>
        document.getElementById('okButton')?.addEventListener('click', function() {
            window.location.href = 'ticket-management.php';
        });
    </script>
</body>
</html>