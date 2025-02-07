<?php

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
      <li><a href="#">Dashboard</a></li>
      <li><a href="#">Users</a></li>
      <li><a href="#">Appointments</a></li>
      <li><a href="#">Health Packages</a></li>
      <li><a href="#">Reports</a></li>
      <li><a href="#">Logout</a></li>
    </ul>
  </div>

  <div class="main-content">
    <div class="header">
      <h1>Welcome, Admin</h1>
      <p>Manage your lab system efficiently.</p>
    </div>

    <div class="grid">
      <div class="card">
        <h3>Total Users</h3>
        <p>120</p>
      </div>
      <div class="card">
        <h3>Appointments Today</h3>
        <p>15</p>
      </div>
      <div class="card">
        <h3>New Reports</h3>
        <p>8</p>
      </div>
      <div class="card">
        <h3>Health Packages Sold</h3>
        <p>30</p>
      </div>
    </div>
  </div>
</body>
</html>
