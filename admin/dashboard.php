=<?php
require '../src/db.php';
session_start();
if (!$_SESSION["loggedIn"]) {
  header('location: login.php');
  die;
}

// Get total users
$sql = "SELECT COUNT(*) AS user_count FROM user";
$data = $conn->query($sql);
$userCount = 0;
if ($data->num_rows > 0) {
  $result = $data->fetch_assoc();
  $userCount = $result['user_count'];
}

// get total Appointment 
$sql = "SELECT COUNT(*) AS appointment_count FROM appointment";
$data = $conn->query($sql);
$totalAppointment = 0; 
if ($data->num_rows > 0) {
  $result = $data->fetch_assoc();
  $totalAppointment = $result['appointment_count'];
}


// Get today's appointments where report is not NULL
$currentDate = date('Y-m-d');
$sql = "SELECT COUNT(*) AS today_appointment FROM appointment WHERE DATE(date) = ? AND report IS NULL";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $currentDate);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$todayAppointment = $row['today_appointment'];


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
</head>
<body>
  <div class="sidebar">
    <h2>Admin Dashboard</h2>
    <ul>
      <li><a href="dashboard.php">Dashboard</a></li>
      <li><a href="users.php">Users</a></li>
      <li><a href="appointments.php">Appointments</a></li>
      <li><a href="health-package.php">Health Packages</a></li>
      <li><a href="reports.php">Reports</a></li>
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </div>

  <div class="main-content">
    <div class="header">
      <h1>Welcome, <?php echo $_SESSION['name']; ?></h1>
      <p>Manage your lab system efficiently.</p>
    </div>

    <div class="grid">
      <div class="card">
        <h3>Total Users</h3>
        <p><?php echo $userCount; ?></p>
      </div>
      <div class="card">
        <h3>Appointments Today</h3>
        <p><?php echo $todayAppointment; ?></p>
      </div>
      <div class="card">
        <h3>New Reports</h3>
        <p>8</p>
      </div>
      <div class="card">
        <h3>Health Packages Sold</h3>
        <p><?php echo $totalAppointment;  ?></p>
      </div>
    </div>
  </div>
</body>
</html>
