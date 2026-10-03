<?php
// doctors/index.php
// Lists all doctors with Edit and Delete buttons.
// Shows a success/error message based on ?msg= or ?error= sent by add.php, edit.php, delete.php.

require_once "../config/db.php";
$page_title = "Doctors";

// Fixed list of messages. Only these codes are accepted, so nothing from the URL is printed directly.
$success_messages = [
    "added"   => "Doctor added successfully.",
    "updated" => "Doctor updated successfully.",
    "deleted" => "Doctor deleted successfully."
];
$error_messages = [
    "has_appointments" => "This doctor cannot be deleted because appointments exist for this doctor.",
    "not_found"        => "Doctor not found.",
    "delete_failed"    => "Could not delete the doctor. Please try again."
];

$success = (isset($_GET['msg']) && isset($success_messages[$_GET['msg']])) ? $success_messages[$_GET['msg']] : "";
$error   = (isset($_GET['error']) && isset($error_messages[$_GET['error']])) ? $error_messages[$_GET['error']] : "";

$result = $conn->query("SELECT doctor_id, doctor_name, specialization, phone, email FROM doctors ORDER BY doctor_name");

require_once "../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Doctors</h2>
    <a href="add.php" class="btn btn-primary">+ Add Doctor</a>
</div>

<?php if ($success != "") { ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php } ?>
<?php if ($error != "") { ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php } ?>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0 align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Specialization</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0) { ?>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['doctor_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['specialization']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td class="text-end">
                                <a href="edit.php?id=<?php echo $row['doctor_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="delete.php?id=<?php echo $row['doctor_id']; ?>"
                                   class="btn btn-sm btn-danger confirm-delete"
                                   data-message="Delete <?php echo htmlspecialchars($row['doctor_name'], ENT_QUOTES); ?>? This cannot be undone.">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No doctors found. Click "Add Doctor" to create one.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>