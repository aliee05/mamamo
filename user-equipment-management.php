<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['username'])) {
    header("location: login-user.php");
    exit();
}

$errorMessages = [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Management - AU Technical Support</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="theme-body">
<?php include 'user-sidebar.php'; ?>
<div class="content">
    <div class="welcome-box">
        <span class="welcome-text"><b>Equipment Management</b></span>
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
                    <input type="text" name="txtsearch" placeholder="Search Equipment">
                    <button type="submit" name="btnsearch">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
            </section>
            <section class="table-section">
                <?php
                $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
                if ($link === false) {
                    die("ERROR: Could not connect. " . mysqli_connect_error());
                }

                function buildTable($result) {
                    if (mysqli_num_rows($result) > 0) {
                        echo "<table>";
                        echo "<tr><th>Asset Number</th><th>Serial Number</th><th>Type</th><th>Branch</th><th>Status</th><th>Created By</th></tr>";
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['assetnumber']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['serialnumber']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['type']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['branch']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['createdby']) . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    } else {
                        echo "<div class='error'>No records found.</div>";
                    }
                }

                $sql = "SELECT assetnumber, serialnumber, type, branch, status, createdby FROM tblequipments ORDER BY assetnumber";
                if (isset($_POST['btnsearch'])) {
                    $search = '%' . $_POST['txtsearch'] . '%';
                    $sql = "SELECT assetnumber, serialnumber, type, branch, status, createdby FROM tblequipments 
                            WHERE assetnumber LIKE ? OR serialnumber LIKE ? OR type LIKE ? OR branch LIKE ?
                            ORDER BY assetnumber";
                }

                if ($stmt = mysqli_prepare($link, $sql)) {
                    if (isset($_POST['btnsearch'])) {
                        mysqli_stmt_bind_param($stmt, 'ssss', $search, $search, $search, $search);
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
                ?>
            </section>
        </form>
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
        justify-content: flex-start;
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
</script>
</body>
</html>