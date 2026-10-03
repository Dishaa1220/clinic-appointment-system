<?php
// includes/header.php
// Opens the HTML page, loads Bootstrap, shows the navbar and opens the main container.
// Every page must do this BEFORE including this file:
//   require_once "config/db.php";   (use "../config/db.php" inside a subfolder)
//   $page_title = "Doctors";        (optional)

if (!isset($page_title)) {
    $page_title = "Clinic Appointment System";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($page_title); ?> - Clinic Appointment System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php require_once __DIR__ . "/navbar.php"; ?>

<div class="container my-4">