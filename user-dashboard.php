<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['username'])) {
    header("location: login-user.php");
    exit();
}

$GLOBALS['current_page'] = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AU Technical Support</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* General Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            margin: 0;
            transition: all 0.3s ease;
        }

        :root {
            --bg-gradient: linear-gradient(145deg, #222 0%, #444 100%);
            --text-color: #ccc;
            --sidebar-bg: #333;
            --topbar-bg: #444;
            --active-link-bg: #1e90ff;
            --shadow-color: rgba(255, 255, 255, 0.1);
            --welcome-bg: linear-gradient(135deg, #2e4057 0%, #5c1a2f 100%);
            --welcome-text: #cccccc;
            --card-front-bg: rgba(255, 255, 255, 0.05);
            --card-front-border: rgba(255, 255, 255, 0.2);
            --card-front-text: #cccccc;
            --card-front-icon: #ff4040;
            --card-back-bg: linear-gradient(135deg, #2e4057, #457b9d);
            --card-back-text: #cccccc;
            --card-back-icon: #ffffff;
            --footer-text: #999999;
        }

        body.light-mode {
            --bg-gradient: linear-gradient(145deg, #f0f0f0 0%, #d9d9d9 100%);
            --text-color: #333;
            --sidebar-bg: #e9ecef;
            --topbar-bg: #dee2e6;
            --active-link-bg: #1e90ff;
            --shadow-color: rgba(0, 0, 0, 0.2);
            --welcome-bg: linear-gradient(135deg, #1e90ff, #187bcd);
            --welcome-text: #ffffff;
            --card-front-bg: rgba(255, 255, 255, 0.9);
            --card-front-border: rgba(30, 144, 255, 0.5);
            --card-front-text: #333333;
            --card-front-icon: #ff4040;
            --card-back-bg: linear-gradient(135deg, #1e90ff, #42a5f5);
            --card-back-text: #ffffff;
            --card-back-icon: #ffffff;
            --footer-text: #666666;
        }

        .theme-body {
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
            width: 100%;
            box-sizing: border-box;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            position: fixed;
            top: 55px;
            left: 100px;
            z-index: 998;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }

        .welcome-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 18px 36px var(--shadow-color);
        }

        .welcome-box span {
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            letter-spacing: 1px;
        }

        .container {
            max-width: 1200px;
            width: 100%;
            margin-top: 120px;
            padding: 2rem;
            position: relative;
            z-index: 1;
            height: fit-content;
        }

        .management-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2.5rem;
            margin-top: 1rem;
        }

        .management-card {
            position: center;
            width: 100%;
            height: 220px;
            text-decoration: none;
            perspective: 1000px;
            border-radius: 16px;
            overflow: hidden;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }

        .management-card:hover {
            transform: translateY(-8px) translateZ(20px);
            box-shadow: 0 20px 40px var(--shadow-color);
        }

        .management-card .card-front,
        .management-card .card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            box-shadow: 0 10px 20px var(--shadow-color);
            transition: transform 0.5s cubic-bezier(0.25, 0.8, 0.25, 1), 
                        opacity 0.4s ease-in-out, 
                        box-shadow 0.4s ease-in-out;
        }

        .management-card .card-front {
            background: var(--card-front-bg);
            color: var(--card-front-text);
            transform: translateY(0);
            opacity: 1;
            border: 3px solid var(--card-front-border);
            z-index: 2;
        }

        .management-card .card-back {
            background: var(--card-back-bg);
            color: var(--card-back-text);
            transform: translateY(100%);
            opacity: 0;
            z-index: 1;
        }

        .management-card:hover .card-front {
            transform: translateY(-100%);
            opacity: 0;
            box-shadow: 0 5px 15px var(--shadow-color);
        }

        .management-card:hover .card-back {
            transform: translateY(0);
            opacity: 1;
            box-shadow: 0 15px 30px var(--shadow-color);
        }

        .card-icon {
            font-size: 52px;
            margin-bottom: 1.25rem;
            transition: transform 0.4s ease, color 0.3s ease;
        }

        .management-card .card-front .card-icon {
            color: var(--card-front-icon);
        }

        .management-card .card-back .card-icon {
            color: var(--card-back-icon);
        }

        .management-card:hover .card-icon {
            transform: scale(1.2) rotate(8deg);
        }

        .management-card h3 {
            font-size: 1.6rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            transition: color 0.3s ease;
        }

        .dashboard-footer {
            margin-top: 330px;
            bottom: 25px;
            left: 0;
            width: 100%;
            text-align: center;
            color: var(--footer-text);
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        @media screen and (max-width: 1200px) {
            .welcome-box {
                width: calc(100% - 270px);
                left: 270px;
            }
        }

        @media screen and (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 1.5rem;
            }

            .welcome-box {
                width: 90%;
                left: 5%;
                top: 15px;
                font-size: 24px;
            }

            .management-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 2rem;
            }
        }

        @media screen and (max-width: 480px) {
            .welcome-box {
                width: 100%;
                left: 0;
                padding: 20px;
                font-size: 20px;
                border-radius: 0;
            }

            .management-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .management-card {
                height: 200px;
            }

            .card-icon {
                font-size: 48px;
            }

            .management-card h3 {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body class="theme-body">
    <?php include 'user-sidebar.php'; ?>

    <div class="content">
        <div class="welcome-box">
            <span>Dashboard</span>
        </div>

        <div class="container">
            <div class="management-grid">
                <a href="user-equipment-management.php" class="management-card">
                    <div class="card-front">
                        <i class="fa-solid fa-wrench card-icon"></i>
                        <h3>Equipment</h3>
                    </div>
                    <div class="card-back">
                        <i class="fa-solid fa-wrench card-icon"></i>
                        <h3>Equipment</h3>
                    </div>
                </a>
                <a href="user-ticket-management.php" class="management-card">
                    <div class="card-front">
                        <i class="fa-solid fa-clipboard-list card-icon"></i>
                        <h3>Ticket</h3>
                    </div>
                    <div class="card-back">
                        <i class="fa-solid fa-clipboard-list card-icon"></i>
                        <h3>Tickets</h3>
                    </div>
                </a>
            </div>
        </div>
        <footer class="dashboard-footer">
            (Copyright 2025, Loayon, Anna Marie E.)
        </footer>
    </div>

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