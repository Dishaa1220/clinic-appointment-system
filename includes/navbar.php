<?php
// includes/navbar.php
// Top navigation bar. The current section is highlighted automatically.

$current_url = $_SERVER['SCRIPT_NAME'];

function nav_active($section, $current_url) {
    if ($section === "dashboard") {
        return (strpos($current_url, "/doctors/") === false
             && strpos($current_url, "/patients/") === false
             && strpos($current_url, "/appointments/") === false) ? "active" : "";
    }
    return (strpos($current_url, "/" . $section . "/") !== false) ? "active" : "";
}
?>
<nav class="navbar navbar-expand-md navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php">Clinic Appointment System</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo nav_active('dashboard', $current_url); ?>" href="<?php echo BASE_URL; ?>index.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo nav_active('doctors', $current_url); ?>" href="<?php echo BASE_URL; ?>doctors/index.php">Doctors</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo nav_active('patients', $current_url); ?>" href="<?php echo BASE_URL; ?>patients/index.php">Patients</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo nav_active('appointments', $current_url); ?>" href="<?php echo BASE_URL; ?>appointments/index.php">Appointments</a>
                </li>
            </ul>
        </div>
    </div>
</nav>