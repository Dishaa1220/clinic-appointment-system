<?php
// doctors/delete.php
// Deletes a doctor, but only if the doctor has no appointments.
// This page shows nothing. It always redirects back to doctors/index.php with a message code.

require_once "../config/db.php";

$doctor_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// 1. Make sure the doctor exists
$stmt = $conn->prepare("SELECT doctor_id FROM doctors WHERE doctor_id = ?");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$doctor) {
    header("Location: index.php?error=not_found");
    exit;
}

// 2. Block the delete if this doctor has any appointments
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM appointments WHERE doctor_id = ?");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$total_appointments = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

if ($total_appointments > 0) {
    header("Location: index.php?error=has_appointments");
    exit;
}

// 3. Safe to delete. try/catch is a backup in case the database still refuses (foreign key).
try {
    $stmt = $conn->prepare("DELETE FROM doctors WHERE doctor_id = ?");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php?msg=deleted");
} catch (mysqli_sql_exception $e) {
    header("Location: index.php?error=delete_failed");
}
exit;