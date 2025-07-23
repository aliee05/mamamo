<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'Anna');
define('DB_PASSWORD', 'loayon');
define('DB_NAME', 'itc127-cs2a-2025-loayon');

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($link === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
date_default_timezone_set('Asia/Manila');
?>