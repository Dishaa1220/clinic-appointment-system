<?php
// config/db.php
// Database connection shared by every page (MySQLi).
// XAMPP default: user = root, password = empty.

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "clinic_appointment_system";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Base URL of the project. Used for links so they work from any subfolder.
// If you rename the project folder in htdocs, change it here too.
define("BASE_URL", "/clinic_appointment_system/");