<?php
// patients/index.php
// Lists all patients with an Edit button (patients cannot be deleted in this project).
// Shows a success/error message based on ?msg= or ?error= sent by add.php and edit.php.

require_once "../config/db.php";
$page_title = "Patients";

// Fixed list of messages. Only these codes are accepted, so nothing from the URL is printed directly.
$success_messages = [
    "added"   => "Patient registered successfully.",
    "updated" => "Patient updated successfully."
];
$error_messages = [
    "not_found" => "Patient not found."
];

$success = (isset($_GET['msg']) && isset($success_messages[$_GET['msg']])) ? $success_messages[$_GET['msg']] : "";
$error   = (isset($_GET['error']) && isset($error_messages[$_GET['error']])) ? $error_messages[$_GET['error']] : "";

$result = $conn->query("SELECT patient_id, patient_name, age, gender, phone, email, address FROM patients ORDER BY patient_name");

require_once "../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Patients</h2>
    <a href="add.php" class="btn btn-primary">+ Register Patient</a>
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
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0) { ?>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['patient_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                            <td><?php echo $row['age']; ?></td>
                            <td><?php echo htmlspecialchars($row['gender']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo ($row['email'] != "") ? htmlspecialchars($row['email']) : "-"; ?></td>
                            <td><?php echo ($row['address'] != "") ? htmlspecialchars($row['address']) : "-"; ?></td>
                            <td class="text-end">
                                <a href="edit.php?id=<?php echo $row['patient_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No patients found. Click "Register Patient" to create one.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>
