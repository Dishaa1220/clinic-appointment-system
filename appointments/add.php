<?php
// appointments/add.php
// Form to book an appointment. Doctor and patient are chosen from dropdowns.
// New appointments always start with the status "Scheduled".

require_once "../config/db.php";
$page_title = "Book Appointment";

$errors = [];
$doctor_id = 0;
$patient_id = 0;
$appointment_date = "";
$appointment_time = "";
$reason = "";

// Data for the dropdowns
$doctors  = $conn->query("SELECT doctor_id, doctor_name, specialization FROM doctors ORDER BY doctor_name");
$patients = $conn->query("SELECT patient_id, patient_name FROM patients ORDER BY patient_name");

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $doctor_id        = isset($_POST['doctor_id'])  ? (int) $_POST['doctor_id']  : 0;
    $patient_id       = isset($_POST['patient_id']) ? (int) $_POST['patient_id'] : 0;
    $appointment_date = trim($_POST['appointment_date']);
    $appointment_time = trim($_POST['appointment_time']);
    $reason           = trim($_POST['reason']);

    // Validation: doctor must exist
    if ($doctor_id <= 0) {
        $errors[] = "Please select a doctor.";
    } else {
        $stmt = $conn->prepare("SELECT doctor_id FROM doctors WHERE doctor_id = ?");
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows == 0) {
            $errors[] = "The selected doctor does not exist.";
        }
        $stmt->close();
    }

    // Validation: patient must exist
    if ($patient_id <= 0) {
        $errors[] = "Please select a patient.";
    } else {
        $stmt = $conn->prepare("SELECT patient_id FROM patients WHERE patient_id = ?");
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows == 0) {
            $errors[] = "The selected patient does not exist.";
        }
        $stmt->close();
    }

    // Validation: date must be a real date (YYYY-MM-DD) and not in the past
    if ($appointment_date == "") {
        $errors[] = "Appointment date is required.";
    } else {
        $d = DateTime::createFromFormat("Y-m-d", $appointment_date);
        if (!$d || $d->format("Y-m-d") !== $appointment_date) {
            $errors[] = "Please enter a valid appointment date.";
        } elseif ($appointment_date < date("Y-m-d")) {
            $errors[] = "Appointment date cannot be in the past.";
        }
    }

    // Validation: time must be HH:MM (24-hour)
    if ($appointment_time == "") {
        $errors[] = "Appointment time is required.";
    } elseif (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $appointment_time)) {
        $errors[] = "Please enter a valid appointment time.";
    }

    // Validation: reason
    if ($reason == "") {
        $errors[] = "Reason is required.";
    } elseif (strlen($reason) > 255) {
        $errors[] = "Reason must be 255 characters or less.";
    }

    // Save only if there are no errors (status uses the database default: Scheduled)
    if (count($errors) == 0) {
        $stmt = $conn->prepare("INSERT INTO appointments (doctor_id, patient_id, appointment_date, appointment_time, reason) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $doctor_id, $patient_id, $appointment_date, $appointment_time, $reason);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: index.php?msg=booked");
            exit;
        } else {
            $errors[] = "Could not book the appointment. Please try again.";
        }
        $stmt->close();
    }
}

require_once "../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Book Appointment</h2>
    <a href="index.php" class="btn btn-secondary">Back to Appointments</a>
</div>

<?php if ($doctors->num_rows == 0 || $patients->num_rows == 0) { ?>
    <div class="alert alert-warning">
        You need at least one doctor and one patient before booking.
        <a href="<?php echo BASE_URL; ?>doctors/add.php">Add a doctor</a> or
        <a href="<?php echo BASE_URL; ?>patients/add.php">register a patient</a>.
    </div>
<?php } ?>

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
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="doctor_id" class="form-label">Doctor</label>
                    <select class="form-select" id="doctor_id" name="doctor_id" required>
                        <option value="">-- Select Doctor --</option>
                        <?php while ($doc = $doctors->fetch_assoc()) { ?>
                            <option value="<?php echo $doc['doctor_id']; ?>" <?php echo ($doctor_id == $doc['doctor_id']) ? "selected" : ""; ?>>
                                <?php echo htmlspecialchars($doc['doctor_name'] . " (" . $doc['specialization'] . ")"); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="patient_id" class="form-label">Patient</label>
                    <select class="form-select" id="patient_id" name="patient_id" required>
                        <option value="">-- Select Patient --</option>
                        <?php while ($pat = $patients->fetch_assoc()) { ?>
                            <option value="<?php echo $pat['patient_id']; ?>" <?php echo ($patient_id == $pat['patient_id']) ? "selected" : ""; ?>>
                                <?php echo htmlspecialchars($pat['patient_name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="appointment_date" class="form-label">Date</label>
                    <input type="date" class="form-control" id="appointment_date" name="appointment_date"
                           min="<?php echo date('Y-m-d'); ?>" required value="<?php echo htmlspecialchars($appointment_date); ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="appointment_time" class="form-label">Time</label>
                    <input type="time" class="form-control" id="appointment_time" name="appointment_time"
                           required value="<?php echo htmlspecialchars($appointment_time); ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="reason" class="form-label">Reason</label>
                <input type="text" class="form-control" id="reason" name="reason"
                       maxlength="255" required value="<?php echo htmlspecialchars($reason); ?>">
            </div>

            <button type="submit" class="btn btn-primary">Book Appointment</button>
            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php require_once "../includes/footer.php"; ?>