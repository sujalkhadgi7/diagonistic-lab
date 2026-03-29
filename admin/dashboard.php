<?php
require_once '../src/db.php';
session_start();
if (!$_SESSION["loggedIn"]) {
  header('location: login.php');
  die;
}

// Get total users
$sql = "SELECT COUNT(*) AS user_count FROM $table[USER]";
$userCount = 0;
if (($result = $conn->query($sql)) && $result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $userCount = (int) $row['user_count'];
}

// Get total appointments
$sql = "SELECT COUNT(*) AS appointment_count FROM $table[APPOINTMENT]";
$totalAppointment = 0;
if (($result = $conn->query($sql)) && $result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $totalAppointment = (int) $row['appointment_count'];
}

// Get today's appointments
$currentDate = date('Y-m-d');
$sql = "SELECT COUNT(*) AS today_appointment FROM $table[APPOINTMENT] WHERE DATE(date) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $currentDate);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$todayAppointment = (int) $row['today_appointment'];
$stmt->close();

// Get appointments with pending reports (report IS NULL)
$sql = "SELECT COUNT(*) AS pending_reports FROM $table[APPOINTMENT] WHERE report IS NULL";
$pendingReports = 0;
if (($result = $conn->query($sql)) && $result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $pendingReports = (int) $row['pending_reports'];
}

// Get total health packages
$packagesTable = $table['PACKAGES'];
$sql = "SELECT COUNT(*) AS package_count FROM {$packagesTable}";
$totalPackages = 0;
if (($result = $conn->query($sql)) && $result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $totalPackages = (int) $row['package_count'];
}

// Get completed appointments (with reports)
$sql = "SELECT COUNT(*) AS completed FROM $table[APPOINTMENT] WHERE report IS NOT NULL";
$completedAppointments = 0;
if (($result = $conn->query($sql)) && $result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $completedAppointments = (int) $row['completed'];
}

// Calculate completion percentage
$completionPercent = $totalAppointment > 0 ? round(($completedAppointments / $totalAppointment) * 100) : 0;

// Get recent appointments (last 5)
$recentAppointments = [];
$sql = "SELECT id, patient_name, email, test_name, date, report FROM $table[APPOINTMENT] ORDER BY date DESC LIMIT 5";
if ($result = $conn->query($sql)) {
  $recentAppointments = $result->fetch_all(MYSQLI_ASSOC);
}

// Get popular packages
$popularPackages = [];
$sql = "SELECT name, category, popularity FROM {$packagesTable} ORDER BY popularity DESC LIMIT 5";
if ($result = $conn->query($sql)) {
  $popularPackages = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="admin-panel">
  <div class="sidebar">
    <h2>Admin Dashboard</h2>
    <ul>
      <li><a href="dashboard.php" class="active">Dashboard</a></li>
      <li><a href="users.php">Users</a></li>
      <li><a href="appointments.php">Appointments</a></li>
      <li><a href="health-package.php">Health Packages</a></li>
      <li><a href="reports.php">Reports</a></li>
      <li><a href="patient-results.php">Patient Results</a></li>
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </div>

  <div class="main-content">
    <div class="header">
      <h1>Welcome, <?php echo isset($_SESSION['name']) ? $_SESSION['name'] : 'Admin'; ?></h1>
      <p>Real-time overview of your diagnostic lab operations.</p>
    </div>

    <!-- Main Stats Grid -->
    <div class="dashboard-grid">
      <div class="stat-card stat-card-users">
        <div class="stat-icon">👥</div>
        <div class="stat-content">
          <h3>Total Users</h3>
          <p class="stat-number"><?php echo $userCount; ?></p>
          <p class="stat-label">Registered patients</p>
        </div>
      </div>

      <div class="stat-card stat-card-appointments">
        <div class="stat-icon">📅</div>
        <div class="stat-content">
          <h3>Total Appointments</h3>
          <p class="stat-number"><?php echo $totalAppointment; ?></p>
          <p class="stat-label">All time bookings</p>
        </div>
      </div>

      <div class="stat-card stat-card-today">
        <div class="stat-icon">✓</div>
        <div class="stat-content">
          <h3>Today's Appointments</h3>
          <p class="stat-number"><?php echo $todayAppointment; ?></p>
          <p class="stat-label">Scheduled for today</p>
        </div>
      </div>

      <div class="stat-card stat-card-pending">
        <div class="stat-icon">⏳</div>
        <div class="stat-content">
          <h3>Pending Reports</h3>
          <p class="stat-number"><?php echo $pendingReports; ?></p>
          <p class="stat-label">Awaiting results</p>
        </div>
      </div>

      <div class="stat-card stat-card-packages">
        <div class="stat-icon">📦</div>
        <div class="stat-content">
          <h3>Health Packages</h3>
          <p class="stat-number"><?php echo $totalPackages; ?></p>
          <p class="stat-label">Available packages</p>
        </div>
      </div>

      <div class="stat-card stat-card-completion">
        <div class="stat-icon">📊</div>
        <div class="stat-content">
          <h3>Completion Rate</h3>
          <p class="stat-number"><?php echo $completionPercent; ?>%</p>
          <p class="stat-label"><?php echo $completedAppointments; ?>/<?php echo $totalAppointment; ?> completed</p>
        </div>
      </div>
    </div>

    <!-- Progress Bar -->
    <div class="card dashboard-progress">
      <h2>Appointment Completion Progress</h2>
      <div class="progress-bar-container">
        <div class="progress-bar">
          <div class="progress-fill" style="width: <?php echo $completionPercent; ?>%"></div>
        </div>
        <p class="progress-label"><?php echo $completionPercent; ?>% of appointments have completed reports</p>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="dashboard-section">
      <div class="card">
        <h2>Recent Appointments</h2>
        <?php if (!empty($recentAppointments)): ?>
          <div class="activity-list">
            <?php foreach ($recentAppointments as $appt): ?>
              <div class="activity-item">
                <div class="activity-header">
                  <h4><?php echo htmlspecialchars($appt['patient_name']); ?></h4>
                  <span class="activity-status <?php echo !is_null($appt['report']) ? 'completed' : 'pending'; ?>">
                    <?php echo !is_null($appt['report']) ? 'Completed' : 'Pending'; ?>
                  </span>
                </div>
                <p class="activity-meta"><?php echo htmlspecialchars($appt['test_name']); ?> •
                  <?php echo htmlspecialchars($appt['date']); ?></p>
                <p class="activity-email"><?php echo htmlspecialchars($appt['email']); ?></p>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="no-data">No appointments yet.</p>
        <?php endif; ?>
      </div>

      <div class="card">
        <h2>Top Packages</h2>
        <?php if (!empty($popularPackages)): ?>
          <div class="package-list">
            <?php foreach ($popularPackages as $pkg): ?>
              <div class="package-item">
                <div class="package-header">
                  <h4><?php echo htmlspecialchars($pkg['name']); ?></h4>
                  <span class="package-popularity">★ <?php echo (int) $pkg['popularity']; ?></span>
                </div>
                <p class="package-category"><?php echo htmlspecialchars($pkg['category']); ?></p>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="no-data">No packages yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>

</html>