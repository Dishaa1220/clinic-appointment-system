<?php
// index.php
// Dashboard: total doctors, total patients, today's appointment count.

require_once "config/db.php";
$page_title = "Dashboard";

// Count queries have no user input, so plain query() is safe here.
$total_doctors  = $conn->query("SELECT COUNT(*) AS total FROM doctors")->fetch_assoc()['total'];
$total_patients = $conn->query("SELECT COUNT(*) AS total FROM patients")->fetch_assoc()['total'];

// Today's date comes from the server, not from the user.
$today = date("Y-m-d");
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = ?");
$stmt->bind_param("s", $today);
$stmt->execute();
$todays_appointments = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

require_once "includes/header.php";
?>

<h2 class="mb-4">Dashboard</h2>

<div class="row g-3">
    <div class="col-12 col-md-4">
        <div class="card text-bg-primary shadow-sm">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Total Doctors</h6>
                <h1 class="display-4"><?php echo $total_doctors; ?></h1>
                <a href="<?php echo BASE_URL; ?>doctors/index.php" class="link-light">View doctors</a>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card text-bg-success shadow-sm">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Total Patients</h6>
                <h1 class="display-4"><?php echo $total_patients; ?></h1>
                <a href="<?php echo BASE_URL; ?>patients/index.php" class="link-light">View patients</a>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card text-bg-warning shadow-sm">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Today's Appointments (<?php echo date("d M Y"); ?>)</h6>
                <h1 class="display-4"><?php echo $todays_appointments; ?></h1>
                <a href="<?php echo BASE_URL; ?>appointments/index.php?date=<?php echo $today; ?>" class="link-dark">View today's list</a>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="<?php echo BASE_URL; ?>appointments/add.php" class="btn btn-primary">Book Appointment</a>
    <a href="<?php echo BASE_URL; ?>patients/add.php" class="btn btn-outline-success">Register Patient</a>
    <a href="<?php echo BASE_URL; ?>doctors/add.php" class="btn btn-outline-primary">Add Doctor</a>
</div>

<?php require_once "includes/footer.php"; ?>