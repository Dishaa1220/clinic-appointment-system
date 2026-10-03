<?php
// appointments/update_status.php
// Receives appointment_id and status from the dropdown in appointments/index.php (POST).
// Updates the status, then redirects back to the list. This page shows nothing.

require_once "../config/db.php";

// Only accept form submissions. If someone opens this page directly, send them back.
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    header("Location: index.php");
    exit;
}

$appointment_id = isset($_POST['appointment_id']) ? (int) $_POST['appointment_id'] : 0;
$status         = isset($_POST['status']) ? $_POST['status'] : "";

// Allowed values (must match the ENUM in the database)
$statuses = ["Scheduled", "Completed", "Cancelled"];

// 1. The status must be one of the allowed values
if (!in_array($status, $statuses)) {
    header("Location: index.php?error=invalid_status");
    exit;
}

// 2. The appointment must exist
$stmt = $conn->prepare("SELECT appointment_id FROM appointments WHERE appointment_id = ?");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$exists = $stmt->get_result()->num_rows;
$stmt->close();

if ($exists == 0) {
    header("Location: index.php?error=not_found");
    exit;
}

// 3. Update the status
$stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
$stmt->bind_param("si", $status, $appointment_id);
$stmt->execute();
$stmt->close();

header("Location: index.php?msg=status_updated");
exit;