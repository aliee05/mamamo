<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['username'])) {
    header("location: login.php");
    exit();
}

$ticketnumber = date("YmdHis");
$showModal = false;
$errorMessages = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ticketnumber = isset($_POST['ticketnumber']) ? trim($_POST['ticketnumber']) : date("YmdHis");
    $problem = isset($_POST['problem']) ? trim($_POST['problem']) : '';
    $details = isset($_POST['details']) ? trim($_POST['details']) : '';
    $status = "Pending";
    $createdby = $_SESSION['username'];
    $datecreated = date("Y-m-d");
    $assignedto = "";
    $dateassigned = "";
    $datecompleted = "";
    $approvedby = "";
    $dateapproved = "";

    if (empty($problem)) {
        $errorMessages[] = "Problem is required.";
    }
    if (empty($details)) {
        $errorMessages[] = "Details is required.";
    }

    if (empty($errorMessages)) {
        $sql = "INSERT INTO tblticket (ticketnumber, problem, details, status, createdby, datecreated, assignedto, dateassigned, datecompleted, approvedby, dateapproved) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssssssssss", $ticketnumber, $problem, $details, $status, $createdby, $datecreated, $assignedto, $dateassigned, $datecompleted, $approvedby, $dateapproved);
            if (mysqli_stmt_execute($stmt)) {
                $sql_log = "INSERT INTO tbllogs (datelog, timelog, action, module, performedto, performedby) VALUES (?, ?, ?, ?, ?, ?)";
                if ($logStmt = mysqli_prepare($link, $sql_log)) {
                    $date = date("Y-m-d");
                    $time = date("H:i:s");
                    $action = "Add";
                    $module = "Ticket Management";
                    $performedto = $ticketnumber;
                    mysqli_stmt_bind_param($logStmt, "ssssss", $date, $time, $action, $module, $performedto, $_SESSION['username']);
                    if (mysqli_stmt_execute($logStmt)) {
                        $showModal = true;
                    } else {
                        $errorMessages[] = "ERROR: Could not log the action to tbllogs.";
                    }
                    mysqli_stmt_close($logStmt);
                } else {
                    $errorMessages[] = "ERROR: Could not prepare log insertion.";
                }
            } else {
                $errorMessages[] = "ERROR: Could not create ticket. " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt);
        } else {
            $errorMessages[] = "ERROR: Could not prepare query. " . mysqli_error($link);
        }
    }
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Ticket - AU Technical Support</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="stylesheets/add-ticket.css">
</head>
<body class="theme-body">
    <?php include 'sidebar.php'; ?>

    <div class="content">
        <div class="welcome-box">
            <span>Add Ticket</span>
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
                    <label>Ticket Number</label>
                    <input type="text" name="ticketnumber_display" value="<?php echo $ticketnumber; ?>" disabled readonly>
                    <input type="hidden" name="ticketnumber" value="<?php echo $ticketnumber; ?>">
                </div>

                <div class="form-group">
                    <label>Problem</label>
                    <select name="problem" required>
                        <option value="" <?php echo !isset($_POST['problem']) || empty($_POST['problem']) ? 'selected' : ''; ?>>Select Problem</option>
                        <option value="Hardware" <?php echo (isset($_POST['problem']) && $_POST['problem'] == 'Hardware') ? 'selected' : ''; ?>>Hardware</option>
                        <option value="Software" <?php echo (isset($_POST['problem']) && $_POST['problem'] == 'Software') ? 'selected' : ''; ?>>Software</option>
                        <option value="Connection" <?php echo (isset($_POST['problem']) && $_POST['problem'] == 'Connection') ? 'selected' : ''; ?>>Connection</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Details</label>
                    <textarea name="details" required><?php echo isset($_POST['details']) ? htmlspecialchars($_POST['details']) : ''; ?></textarea>
                </div>

                <div class="button-group">
                    <input type="submit" name="btnsubmit" value="Add">
                    <a href="ticket-management.php">Cancel</a>
                </div>
            </form>

            <div id="successModal" class="modal" style="<?php echo $showModal ? 'display: block;' : 'display: none;'; ?>">
                <div class="modal-content success-modal">
                    <div class="modal-icon">
                        <i class="fa-solid fa-check-circle"></i>
                    </div>
                    <p>Ticket Successfully Added!</p>
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

        .container {
            margin-top: 30px;
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
            background: #218838;
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