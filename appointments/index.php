<?php
// appointments/index.php
// Lists all appointments with doctor and patient NAMES.
// Filters (GET): doctor_id, patient_id, date. Each row has a status dropdown that posts to update_status.php.
// Shows a message based on ?msg= or ?error= sent by add.php and update_status.php.

require_once "../config/db.php";
$page_title = "Appointments";

// Fixed list of messages. Only these codes are accepted, so nothing from the URL is printed directly.
$success_messages = [
    "booked"         => "Appointment booked successfully.",
    "status_updated" => "Appointment status updated successfully."
];
$error_messages = [
    "not_found"      => "Appointment not found.",
    "invalid_status" => "Invalid status selected."
];

$success = (isset($_GET['msg']) && isset($success_messages[$_GET['msg']])) ? $success_messages[$_GET['msg']] : "";
$error   = (isset($_GET['error']) && isset($error_messages[$_GET['error']])) ? $error_messages[$_GET['error']] : "";

// ---- Read filters from the URL ----
$filter_doctor  = isset($_GET['doctor_id'])  ? (int) $_GET['doctor_id']  : 0;
$filter_patient = isset($_GET['patient_id']) ? (int) $_GET['patient_id'] : 0;
$filter_date    = isset($_GET['date']) ? trim($_GET['date']) : "";

// Ignore the date filter if it is not a real date in YYYY-MM-DD format
if ($filter_date != "") {
    $d = DateTime::createFromFormat("Y-m-d", $filter_date);
    if (!$d || $d->format("Y-m-d") !== $filter_date) {
        $filter_date = "";
    }
}

// ---- Build the query (the filters are added only when used) ----
$sql = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.reason, a.status,
               d.doctor_name, d.specialization, p.patient_name
        FROM appointments a
        JOIN doctors d  ON a.doctor_id  = d.doctor_id
        JOIN patients p ON a.patient_id = p.patient_id
        WHERE 1 = 1";
$types  = "";
$params = [];

if ($filter_doctor > 0) {
    $sql .= " AND a.doctor_id = ?";
    $types .= "i";
    $params[] = $filter_doctor;
}
if ($filter_patient > 0) {
    $sql .= " AND a.patient_id = ?";
    $types .= "i";
    $params[] = $filter_patient;
}
if ($filter_date != "") {
    $sql .= " AND a.appointment_date = ?";
    $types .= "s";
    $params[] = $filter_date;
}

$sql .= " ORDER BY a.appointment_date DESC, a.appointment_time ASC";

$stmt = $conn->prepare($sql);
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// ---- Data for the filter dropdowns ----
$doctors  = $conn->query("SELECT doctor_id, doctor_name FROM doctors ORDER BY doctor_name");
$patients = $conn->query("SELECT patient_id, patient_name FROM patients ORDER BY patient_name");

$statuses = ["Scheduled", "Completed", "Cancelled"];

require_once "../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Appointments</h2>
    <a href="add.php" class="btn btn-primary">+ Book Appointment</a>
</div>

<?php if ($success != "") { ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php } ?>
<?php if ($error != "") { ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php } ?>

<!-- Filters -->
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="get" action="index.php" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label for="doctor_id" class="form-label mb-1">Doctor</label>
                <select class="form-select" id="doctor_id" name="doctor_id">
                    <option value="0">All doctors</option>
                    <?php while ($doc = $doctors->fetch_assoc()) { ?>
                        <option value="<?php echo $doc['doctor_id']; ?>" <?php echo ($filter_doctor == $doc['doctor_id']) ? "selected" : ""; ?>>
                            <?php echo htmlspecialchars($doc['doctor_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label for="patient_id" class="form-label mb-1">Patient</label>
                <select class="form-select" id="patient_id" name="patient_id">
                    <option value="0">All patients</option>
                    <?php while ($pat = $patients->fetch_assoc()) { ?>
                        <option value="<?php echo $pat['patient_id']; ?>" <?php echo ($filter_patient == $pat['patient_id']) ? "selected" : ""; ?>>
                            <?php echo htmlspecialchars($pat['patient_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label for="date" class="form-label mb-1">Date</label>
                <input type="date" class="form-control" id="date" name="date" value="<?php echo htmlspecialchars($filter_date); ?>">
            </div>

            <div class="col-12 col-md-3">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="index.php" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Appointments table -->
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0 align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Doctor</th>
                    <th>Patient</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Change Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0) { ?>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <?php
                        if ($row['status'] == "Completed") {
                            $badge = "success";
                        } elseif ($row['status'] == "Cancelled") {
                            $badge = "danger";
                        } else {
                            $badge = "primary";
                        }
                        ?>
                        <tr>
                            <td><?php echo $row['appointment_id']; ?></td>
                            <td><?php echo date("d M Y", strtotime($row['appointment_date'])); ?></td>
                            <td><?php echo date("h:i A", strtotime($row['appointment_time'])); ?></td>
                            <td>
                                <?php echo htmlspecialchars($row['doctor_name']); ?><br>
                                <small class="text-muted"><?php echo htmlspecialchars($row['specialization']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['reason']); ?></td>
                            <td><span class="badge text-bg-<?php echo $badge; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                            <td>
                                <form method="post" action="update_status.php" class="d-flex gap-1">
                                    <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                                    <select name="status" class="form-select form-select-sm">
                                        <?php foreach ($statuses as $s) { ?>
                                            <option value="<?php echo $s; ?>" <?php echo ($row['status'] == $s) ? "selected" : ""; ?>><?php echo $s; ?></option>
                                        <?php } ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-warning">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No appointments found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$stmt->close();
require_once "../includes/footer.php";
?>