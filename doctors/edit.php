<?php
// doctors/edit.php
// Loads a doctor by ID, shows a pre-filled form, validates, then updates with a prepared statement.

require_once "../config/db.php";
$page_title = "Edit Doctor";

$errors = [];

// The ID comes from the link (?id=) when the page opens, and from a hidden field when the form is submitted.
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $doctor_id = isset($_POST['doctor_id']) ? (int) $_POST['doctor_id'] : 0;
} else {
    $doctor_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
}

// Make sure this doctor exists
$stmt = $conn->prepare("SELECT doctor_id, doctor_name, specialization, phone, email FROM doctors WHERE doctor_id = ?");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$doctor) {
    header("Location: index.php?error=not_found");
    exit;
}

// Start with the values saved in the database
$doctor_name    = $doctor['doctor_name'];
$specialization = $doctor['specialization'];
$phone          = $doctor['phone'];
$email          = $doctor['email'];

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    // Replace with what the user typed
    $doctor_name    = trim($_POST['doctor_name']);
    $specialization = trim($_POST['specialization']);
    $phone          = trim($_POST['phone']);
    $email          = trim($_POST['email']);

    // Validation (same rules as add.php)
    if ($doctor_name == "") {
        $errors[] = "Doctor name is required.";
    }
    if ($specialization == "") {
        $errors[] = "Specialization is required.";
    }
    if ($phone == "") {
        $errors[] = "Phone is required.";
    } elseif (!preg_match('/^[0-9]{7,15}$/', $phone)) {
        $errors[] = "Phone must contain digits only (7 to 15 digits).";
    }
    if ($email == "") {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    // Update only if there are no errors
    if (count($errors) == 0) {
        $stmt = $conn->prepare("UPDATE doctors SET doctor_name = ?, specialization = ?, phone = ?, email = ? WHERE doctor_id = ?");
        $stmt->bind_param("ssssi", $doctor_name, $specialization, $phone, $email, $doctor_id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: index.php?msg=updated");
            exit;
        } else {
            $errors[] = "Could not update the doctor. Please try again.";
        }
        $stmt->close();
    }
}

require_once "../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Edit Doctor</h2>
    <a href="index.php" class="btn btn-secondary">Back to Doctors</a>
</div>

<?php if (count($errors) > 0) { ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error) { ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="edit.php">
            <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">

            <div class="mb-3">
                <label for="doctor_name" class="form-label">Doctor Name</label>
                <input type="text" class="form-control" id="doctor_name" name="doctor_name"
                       maxlength="100" required value="<?php echo htmlspecialchars($doctor_name); ?>">
            </div>

            <div class="mb-3">
                <label for="specialization" class="form-label">Specialization</label>
                <input type="text" class="form-control" id="specialization" name="specialization"
                       maxlength="100" required value="<?php echo htmlspecialchars($specialization); ?>">
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone"
                       maxlength="15" pattern="[0-9]{7,15}" title="Digits only, 7 to 15 digits"
                       required value="<?php echo htmlspecialchars($phone); ?>">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                       maxlength="100" required value="<?php echo htmlspecialchars($email); ?>">
            </div>

            <button type="submit" class="btn btn-primary">Update Doctor</button>
            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>