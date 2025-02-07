<?php

require '../src/db.php';
session_start();

if (!$_SESSION["loggedIn"]) {
  header('location: login.php');
  die;
}

  $sql = "SELECT * FROM user";
  $data = $conn->query($sql);
  if($data->num_rows > 0){
    $users = $data->fetch_assoc();
  }

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users</title>
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
      <h1>Manage Users</h1>
      <p>Here you can manage users of the system.</p>

      <div class="table-container">
          <table class="user-table">
            <thead>
              <tr>
                <th>User ID</th>
                <th>Name</th>
                
                <th>Email</th>
                <th>Role</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
            <?php if ($data->num_rows > 0): ?>
                <?php while ($row = $data->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo $row["ID"]; ?></td>
                    <td><?php echo $row["Name"]; ?></td>
                    <td><?php echo $row["Gmail"]; ?></td>
                    <td><?php echo $row["type"]; ?></td>
                    <td>
                      <button class="delete-btn">Delete</button>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5">No users found.</td>
                </tr>
              <?php endif; ?>

              <!-- <tr>
                <td>1</td>
                <td>John Doe</td>
                <td>john.doe@example.com</td>
                <td>User</td>
                <td><button class="delete-btn">Delete</button></td>
              </tr>
              <tr>
                <td>2</td>
                <td>Jane Smith</td>
                <td>jane.smith@example.com</td>
                <td>Admin</td>
                <td><button class="delete-btn">Delete</button></td>
              </tr>
              <tr>
                <td>3</td>
                <td>Robert Brown</td>
                <td>robert.brown@example.com</td>
                <td>User</td>
                <td><button class="delete-btn">Delete</button></td>
              </tr> -->
            </tbody>
          </table>
        </div> 


    </main>
</body>
</html>

