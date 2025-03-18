<?php
// Define the table variable
$table = [
    'APPOINTMENT' => 'appointments', // Replace with your actual table name
    // Add other tables if necessary
];

// Include the database connection
require_once '../src/db.php';  // Ensure the path to db.php is correct

session_start();

if (!$_SESSION["loggedIn"]) {
    header('location: login.php');
    die;
}

$sql = "SELECT * FROM $table[APPOINTMENT]";
$data = $conn->query($sql);

// Handle form submission to update the appointment date
if (isset($_POST['update_appointment'])) {
    $appointmentId = $_POST['appointment_id'];
    $newAppointmentDate = $_POST['appointment_date'];

    // Update appointment in the database
    $updateSql = "UPDATE $table[APPOINTMENT] SET date = ? WHERE id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("si", $newAppointmentDate, $appointmentId);
    $stmt->execute();
    $stmt->close();

    // Get the patient's email (fetch the email using appointment ID)
    $getEmailSql = "SELECT email FROM $table[APPOINTMENT] WHERE id = ?";
    $stmt = $conn->prepare($getEmailSql);
    $stmt->bind_param("i", $appointmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $patientEmail = $row['email'];

    // Prepare the email content
    $subject = "Your Appointment is Confirmed!";
    $message = "
    <html>
    <head>
        <title>Appointment Confirmation</title>
    </head>
    <body>
        <h2>Dear Customer,</h2>
        <p>Your appointment has been successfully confirmed.</p>
        <p><strong>Appointment Date:</strong> $newAppointmentDate</p>
        <p>If you have any questions, feel free to contact us.</p>
        <p>Thank you for choosing our Diagnostic Lab!</p>
        <p>Best regards,</p>
        <p>The Diagnostic Lab Team</p>
    </body>
    </html>
    ";

    // Include the PHPMailer code from send.php
    require_once 'sendemail/send.php';  // Ensure the path is correct

    // Call PHPMailer to send the email (using your send.php code logic)
    sendAppointmentEmail($patientEmail, $subject, $message);
    
    header("Refresh: 0");  // Refresh the page after updating the appointment
}

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
        <li><a href="reports.php">Reports</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
    </nav>
  </aside>

  <main class="main-content">
    <h1>Appointments</h1>
    <div class="card">
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
                  <td>
                  <?php if (!$row["date"]): ?>
                    <button class="openModalBtn" data-appointment-id="<?php echo $row["id"]; ?>" data-current-date="<?php echo $row["date"]; ?>">Set Appointment Date</button>
                    <?php else: echo $row["date"] ?> 
                    <?php endif; ?>
                  </td>
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
    </div>
  </main>

  <!-- Modal to set appointment date -->
  <div id="appointmentModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2>Set Appointment Date</h2>
      <form action="appointments.php" method="POST">
        <input type="hidden" id="appointment_id" name="appointment_id">
        <label for="appointment_date">Choose a date and time:</label>
        <input type="datetime-local" id="appointment_date" name="appointment_date" required>
        <button type="submit" name="update_appointment">Update Appointment</button>
      </form>
    </div>
  </div>

  <script>
    // Get modal and open buttons
    var modal = document.getElementById("appointmentModal");
    var btns = document.querySelectorAll(".openModalBtn");
    var span = document.getElementsByClassName("close")[0];

    // Open modal when the button is clicked
    btns.forEach(function (btn) {
      btn.onclick = function() {
        var appointmentId = this.getAttribute("data-appointment-id");
        var currentDate = this.getAttribute("data-current-date");

        // Set the appointment ID and pre-fill current date if available
        document.getElementById("appointment_id").value = appointmentId;
        document.getElementById("appointment_date").value = currentDate || "";
        
        modal.style.display = "block";
      }
    });

    // Close modal when the close button is clicked
    span.onclick = function() {
      modal.style.display = "none";
    }

    // Close modal when clicked outside the modal content
    window.onclick = function(event) {
      if (event.target == modal) {
        modal.style.display = "none";
      }
    }
  </script>
</body>
</html>
