<?php
require_once "config.php";
session_start();

if (!isset($_SESSION['username']) || strtoupper($_SESSION['usertype']) !== "TECHNICAL") {
    header("location: login-tech.php");
    exit();
}

$showModal = false;
$errorMessages = [];

if (isset($_POST['btnsubmit'])) {
    $assetnumber = trim($_POST['txtasset']);
    $serialnumber = trim($_POST['txtserial']);
    $type = trim($_POST['cmbtype']);
    $manufacturer = trim($_POST['txtmanufacturer']);
    $yearmodel = trim($_POST['txtyear']);
    $description = trim($_POST['txtdesc']);
    $branch = trim($_POST['cmbbranch']);
    $department = trim($_POST['cmbdepartment']);
    $status = trim($_POST['status']);

    if (empty($assetnumber)) {
        $errorMessages[] = "Asset Number is required.";
    } else {
        $sql = "SELECT COUNT(*) FROM tblequipments WHERE assetnumber = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $assetnumber);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $count);
            mysqli_stmt_fetch($stmt);
            if ($count > 0) {
                $errorMessages[] = "Asset Number '$assetnumber' already exists.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $errorMessages[] = "ERROR: Could not verify Asset Number uniqueness.";
        }
    }

    if (empty($serialnumber)) {
        $errorMessages[] = "Serial Number is required.";
    } else {
        $sql = "SELECT COUNT(*) FROM tblequipments WHERE serialnumber = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $serialnumber);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $count);
            mysqli_stmt_fetch($stmt);
            if ($count > 0) {
                $errorMessages[] = "Serial Number '$serialnumber' already exists.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $errorMessages[] = "ERROR: Could not verify Serial Number uniqueness.";
        }
    }

    if (empty($type)) {
        $errorMessages[] = "Type is required.";
    }

    if (empty($manufacturer)) {
        $errorMessages[] = "Manufacturer is required.";
    }

    if (empty($yearmodel)) {
        $errorMessages[] = "Year Model is required.";
    } else {
        if (!is_numeric($yearmodel)) {
            $errorMessages[] = "Year Model should be numeric.";
        } else {
            if (strlen($yearmodel) != 4) {
                $errorMessages[] = "Year Model should contain 4 numbers only.";
            }
        }
    }

    if (empty($description)) {
        $errorMessages[] = "Description is required.";
    }

    if (empty($branch)) {
        $errorMessages[] = "Branch is required.";
    }

    if (empty($department)) {
        $errorMessages[] = "Department is required.";
    }

    if (empty($status)) {
        $errorMessages[] = "Status is required.";
    }

    if (empty($errorMessages)) {
        $sql = "INSERT INTO tblequipments (assetnumber, serialnumber, type, manufacturer, yearmodel, description, branch, department, status, createdBy, datecreated) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if ($insertStmt = mysqli_prepare($link, $sql)) {
            $date = date("Y-m-d");
            mysqli_stmt_bind_param($insertStmt, "sssssssssss", $assetnumber, $serialnumber, $type, $manufacturer, $yearmodel, $description, $branch, $department, $status, $_SESSION['username'], $date);
            if (mysqli_stmt_execute($insertStmt)) {
                $sql = "INSERT INTO tbllogs (datelog, timelog, action, module, performedto, performedby) VALUES (?, ?, ?, ?, ?, ?)";
                if ($logStmt = mysqli_prepare($link, $sql)) {
                    $time = date("H:i:s");
                    $action = "Add";
                    $module = "Equipment Management";
                    mysqli_stmt_bind_param($logStmt, "ssssss", $date, $time, $action, $module, $assetnumber, $_SESSION['username']);
                    if (mysqli_stmt_execute($logStmt)) {
                        $showModal = true;
                    } else {
                        $errorMessages[] = "ERROR: Could not log the action.";
                    }
                    mysqli_stmt_close($logStmt);
                } else {
                    $errorMessages[] = "ERROR: Could not prepare log insertion.";
                }
            } else {
                $errorMessages[] = "ERROR: Could not add new equipment.";
            }
            mysqli_stmt_close($insertStmt);
        } else {
            $errorMessages[] = "ERROR: Could not prepare equipment insertion.";
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
    <title>Create Equipment - AU Technical Support</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="theme-body">
    <?php include 'tech-sidebar.php'; ?>

    <div class="content">
        <div class="welcome-box">
            <span>Create Equipment</span>
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
                    <label>Asset Number</label>
                    <input type="text" name="txtasset" value="<?php echo isset($_POST['txtasset']) ? htmlspecialchars($_POST['txtasset']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>Serial Number</label>
                    <input type="text" name="txtserial" value="<?php echo isset($_POST['txtserial']) ? htmlspecialchars($_POST['txtserial']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>Type</label>
                    <select name="cmbtype" required>
                        <option value="">Select Equipment Type</option>
                        <?php 
                        $types = ['Monitor', 'CPU', 'Keyboard', 'Mouse', 'AVR', 'MAC', 'Printer', 'Projector']; 
                        foreach ($types as $t): ?>
                            <option value="<?php echo $t; ?>" <?php echo (isset($_POST['cmbtype']) && $_POST['cmbtype'] == $t) ? 'selected' : ''; ?>><?php echo $t; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Manufacturer</label>
                    <input type="text" name="txtmanufacturer" value="<?php echo isset($_POST['txtmanufacturer']) ? htmlspecialchars($_POST['txtmanufacturer']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>Year Model</label>
                    <input type="text" name="txtyear" value="<?php echo isset($_POST['txtyear']) ? htmlspecialchars($_POST['txtyear']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="txtdesc" required><?php echo isset($_POST['txtdesc']) ? htmlspecialchars($_POST['txtdesc']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label>Branch</label>
                    <select name="cmbbranch" required>
                        <option value="">Select Branch</option>
                        <?php 
                        $branches = [
                            'Juan Sumulong Campus (AU Legarda/Main)', 
                            'Jose Abad Santos Campus (AU Pasay)', 
                            'Arellano School of Law', 
                            'Andres Bonifacio Campus (AU Pasig)', 
                            'Jose Rizal Campus (AU Malabon)', 
                            'Plaridel Campus (AU Mandaluyong)', 
                            'Apolinario Mabini Campus (AU Pasay)', 
                            'Elisa Esguerra Campus (AU Malabon)'
                        ]; 
                        foreach ($branches as $b): ?>
                            <option value="<?php echo $b; ?>" <?php echo (isset($_POST['cmbbranch']) && $_POST['cmbbranch'] == $b) ? 'selected' : ''; ?>><?php echo $b; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Department</label>
                    <select name="cmbdepartment" required>
                        <option value="">Select Department</option>
                        <?php 
                        $departments = ['Finance', 'Marketing', 'Engineering', 'Hospitality', 'College of Medical Laboratory Science', 'Computer Studies', 'Education', 'Nursing', 'School of Business Admin', 'Arts', 'Management', 'Psychology', 'Academic track', 'Early Childhood Education', 'Marketing']; 
                        foreach ($departments as $d): ?>
                            <option value="<?php echo $d; ?>" <?php echo (isset($_POST['cmbdepartment']) && $_POST['cmbdepartment'] == $d) ? 'selected' : ''; ?>><?php echo $d; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <div class="radio-group">
                        <label><input type="radio" name="status" value="Working" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Working') || !isset($_POST['status']) ? 'checked' : ''; ?>> Working</label>
                        <label><input type="radio" name="status" value="On-repair" <?php echo (isset($_POST['status']) && $_POST['status'] == 'On-repair') ? 'checked' : ''; ?>> On-repair</label>
                        <label><input type="radio" name="status" value="Retired" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Retired') ? 'checked' : ''; ?>> Retired</label>
                    </div>
                </div>

                <div class="button-group">
                    <input type="submit" name="btnsubmit" value="Add">
                    <a href="tech-equipment-management.php">Cancel</a>
                </div>
            </form>

            <div id="successModal" class="modal" style="<?php echo $showModal ? 'display: block;' : 'display: none;'; ?>">
                <div class="modal-content success-modal">
                    <div class="modal-icon">
                        <i class="fa-solid fa-check-circle"></i>
                    </div>
                    <p>Equipment Successfully Added!</p>
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
            align-items: center;
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
        .form-group select,
        .form-group textarea {
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

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #1976d2;
            outline: none;
            box-shadow: 0 8px 20px rgba(25, 118, 210, 0.2);
        }

        .radio-group {
            display: flex;
            gap: 1rem;
            flex: 1;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            font-weight: normal;
            font-size: 1rem;
            color: #616161;
            background: none;
            padding: 0;
            box-shadow: none;
            transition: color 0.3s ease;
        }

        .radio-group label:hover {
            color: #1976d2;
        }

        .radio-group input[type="radio"] {
            margin-right: 0.5rem;
            accent-color: #1976d2;
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
            window.location.href = 'tech-equipment-management.php';
        });
    </script>
</body>
</html>