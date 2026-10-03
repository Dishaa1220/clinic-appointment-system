<?php
// doctors/add.php
// Form to add a new doctor. Validates input, then saves with a prepared statement.

require_once "../config/db.php";
$page_title = "Add Doctor";

$errors = [];
$doctor_name = "";
$specialization = "";
$phone = "";
$email = "";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $doctor_name    = trim($_POST['doctor_name']);
    $specialization = trim($_POST['specialization']);
    $phone          = trim($_POST['phone']);
    $email          = trim($_POST['email']);

    // Validation
    if ($doctor_name == "") {
        $errors[] = "Doctor name is required.";
    }
    if ($specialization == "") {
        $errors[] = "Specialization is required.";
    }
        if ($phone == "") {
        $errors[] = "Mobile number is required.";
    } elseif (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
        $errors[] = "Mobile number must be exactly 10 digits and start with 6, 7, 8 or 9.";
    }
    if ($email == "") {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    // Save only if there are no errors
    if (count($errors) == 0) {
        $stmt = $conn->prepare("INSERT INTO doctors (doctor_name, specialization, phone, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $doctor_name, $specialization, $phone, $email);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: index.php?msg=added");
            exit;
        } else {
            $errors[] = "Could not save the doctor. Please try again.";
        }
        $stmt->close();
    }
}

require_once "../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Add Doctor</h2>
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
        <form method="post" action="add.php">
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
                <label for="phone" class="form-label">Mobile Number</label>
                <input type="text" class="form-control" id="phone" name="phone"
                       maxlength="10" inputmode="numeric" pattern="[6-9][0-9]{9}"
                       title="10 digits, starting with 6, 7, 8 or 9"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                       required value="<?php echo htmlspecialchars($phone); ?>">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                       maxlength="100" required value="<?php echo htmlspecialchars($email); ?>">
            </div>

            <button type="submit" class="btn btn-primary">Save Doctor</button>
            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>