<?php
require '../src/db.php';
session_start();

if (!$_SESSION["loggedIn"]) {
  header('location: login.php');
  die;
}

$sql = "SELECT * FROM appointment";
$data = $conn->query($sql);


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
<aside class="sidebar">
      <h2>Admin Panel</h2>
      <nav>
        <ul>
          <li><a href="dashboard.php">Dashboard</a></li>
          <li><a href="users.php">Users</a></li>
          <li><a href="appointments.php">Appointments</a></li>
          <li><a href="health-package.php">Health Packages</a></li>
          <li><a href="report.php">Reports</a></li>
          <li><a href="logout.php">Logout</a></li>
        </ul>
      </nav>
    </aside>

    <main class="main-content">
      <h1>Appointments</h1>
      <div class="card">
        <p>Here you can view and manage appointments made by users.</p>

        <div class="table-container">
          <table class="user-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Package</th>
                <th>Appointment Date</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($data->num_rows > 0): ?>
                <?php while ($row = $data->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo $row["id"]; ?></td>
                    <td><?php echo $row["name"]; ?></td>
                    <td><?php echo $row["email"]; ?></td>
                    <td><?php echo $row["phone"]; ?></td>
                    <td><?php echo $row["package"]; ?></td>
                    <td><?php echo $row["date"]; ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5">No appointments found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
</body>
</html>
