<?php
// patients/edit.php
// Loads a patient by ID, shows a pre-filled form, validates, then updates with a prepared statement.

require_once "../config/db.php";
$page_title = "Edit Patient";

$errors = [];

// Allowed gender values (must match the ENUM in the database)
$genders = ["Male", "Female", "Other"];

// The ID comes from the link (?id=) when the page opens, and from a hidden field when the form is submitted.
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $patient_id = isset($_POST['patient_id']) ? (int) $_POST['patient_id'] : 0;
} else {
    $patient_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
}

// Make sure this patient exists
$stmt = $conn->prepare("SELECT patient_id, patient_name, age, gender, phone, email, address FROM patients WHERE patient_id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$patient) {
    header("Location: index.php?error=not_found");
    exit;
}

// Start with the values saved in the database (NULL email/address become empty text for the form)
$patient_name = $patient['patient_name'];
$age          = $patient['age'];
$gender       = $patient['gender'];
$phone        = $patient['phone'];
$email        = ($patient['email'] !== null) ? $patient['email'] : "";
$address      = ($patient['address'] !== null) ? $patient['address'] : "";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    // Replace with what the user typed
    $patient_name = trim($_POST['patient_name']);
    $age          = trim($_POST['age']);
    $gender       = isset($_POST['gender']) ? $_POST['gender'] : "";
    $phone        = trim($_POST['phone']);
    $email        = trim($_POST['email']);
    $address      = trim($_POST['address']);

    // Validation (same rules as add.php)
    if ($patient_name == "") {
        $errors[] = "Patient name is required.";
    }
    if ($age == "") {
        $errors[] = "Age is required.";
    } elseif (!ctype_digit($age) || (int) $age < 1 || (int) $age > 120) {
        $errors[] = "Age must be a whole number between 1 and 120.";
    }
    if (!in_array($gender, $genders)) {
        $errors[] = "Please select a gender.";
    }
    if ($phone == "") {
        $errors[] = "Phone is required.";
    } elseif (!preg_match('/^[0-9]{7,15}$/', $phone)) {
        $errors[] = "Phone must contain digits only (7 to 15 digits).";
    }
    // Email is optional, but if entered it must be valid
    if ($email != "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    // Update only if there are no errors
    if (count($errors) == 0) {
        // Empty optional fields are saved as NULL
        $email_value   = ($email != "") ? $email : null;
        $address_value = ($address != "") ? $address : null;
        $age_value     = (int) $age;

        $stmt = $conn->prepare("UPDATE patients SET patient_name = ?, age = ?, gender = ?, phone = ?, email = ?, address = ? WHERE patient_id = ?");
        $stmt->bind_param("sissssi", $patient_name, $age_value, $gender, $phone, $email_value, $address_value, $patient_id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: index.php?msg=updated");
            exit;
        } else {
            $errors[] = "Could not update the patient. Please try again.";
        }
        $stmt->close();
    }
}

require_once "../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Edit Patient</h2>
    <a href="index.php" class="btn btn-secondary">Back to Patients</a>
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
            <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">

            <div class="mb-3">
                <label for="patient_name" class="form-label">Patient Name</label>
                <input type="text" class="form-control" id="patient_name" name="patient_name"
                       maxlength="100" required value="<?php echo htmlspecialchars($patient_name); ?>">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="age" class="form-label">Age</label>
                    <input type="number" class="form-control" id="age" name="age"
                           min="1" max="120" required value="<?php echo htmlspecialchars($age); ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="gender" class="form-label">Gender</label>
                    <select class="form-select" id="gender" name="gender" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($genders as $g) { ?>
                            <option value="<?php echo $g; ?>" <?php echo ($gender == $g) ? "selected" : ""; ?>><?php echo $g; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone"
                       maxlength="15" pattern="[0-9]{7,15}" title="Digits only, 7 to 15 digits"
                       required value="<?php echo htmlspecialchars($phone); ?>">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email <small class="text-muted">(optional)</small></label>
                <input type="email" class="form-control" id="email" name="email"
                       maxlength="100" value="<?php echo htmlspecialchars($email); ?>">
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Address <small class="text-muted">(optional)</small></label>
                <input type="text" class="form-control" id="address" name="address"
                       maxlength="255" value="<?php echo htmlspecialchars($address); ?>">
            </div>

            <button type="submit" class="btn btn-primary">Update Patient</button>
            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>