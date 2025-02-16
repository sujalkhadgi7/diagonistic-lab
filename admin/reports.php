<?php
require '../src/db.php';
session_start();

if (!$_SESSION["loggedIn"]) {
  header('location: login.php');
  die;
}


$currentDate = date("Y-m-d");
$sql = "SELECT * FROM $table[APPOINTMENT] WHERE DATE(date) = ? ";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $currentDate);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);





// Handle form submission to update the appointment date
if (isset($_POST['update_appointment'])) {
  $appointmentId = $_POST['appointment_id'];
  $newAppointmentDate = $_POST['appointment_date'];

  $updateSql = "UPDATE $table[APPOINTMENT] SET date = ? WHERE id = ?";
  $stmt = $conn->prepare($updateSql);
  $stmt->bind_param("si", $newAppointmentDate, $appointmentId);
  $stmt->execute();
  $stmt->close();
  header("Refresh: 0"); 
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Report</title>
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css"> <!-- Link to your existing CSS file -->
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="users.php">Users</a></li>
            <li><a href="appointments.php">Appointments</a></li>
            <li><a href="reports.php" class="active">Reports</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2>Appointment Report</h2>
        </div>

        <!-- Date Selection Form -->
        <div class="card">
          <div class="date-selector-container">
            <label for="appointmentDate">Select Date:</label>
            <input type="date" id="appointmentDate">
            <button class="btn" onclick="fetchAppointments()">Get Appointments</button>
          </div>
        </div>

        <!-- Table to Display Appointments -->
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
          <tbody id="appointmentsBody">
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
                <td colspan="6">No appointments found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
        </div>
    </div>

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
        function fetchAppointments() {

          var selectedDate = document.getElementById("appointmentDate").value;
          var tableBody = document.getElementById("appointmentsBody");
          
          tableBody.innerHTML = "";

          console.log(selectedDate)
          
          if (!selectedDate) {
              alert("Please select a date.");
                return;
            }

            fetch("fetch-appointments.php?date=" + selectedDate)
                .then(response => response.json())
                .then(data => {
                    tableBody.innerHTML = "";
                    if (data.length > 0) {
                      console.log(data);
                      
                      let row =data.map(app => {
                        return `<tr>
                            <td>${app.id}</td>
                            <td>${app.name}</td>
                            <td>${app.email}</td>
                            <td>${app.phone}</td>
                            <td>${app.package}</td>
                            <td>
                                ${!app.date ? `<button class="openModalBtn" data-appointment-id="${app.id}" data-current-date="${app.date}">Set Appointment Date</button>` : app.date}
                            </td>
                        </tr>`;})
                    tableBody.innerHTML += row;
                    } else {
                        tableBody.innerHTML = `<tr><td colspan="6" class="no-data">No appointments found</td></tr>`;
                    }
                })
                .catch(error => console.error("Error fetching data:", error));
        }

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
