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
  $stmtUpdate = $conn->prepare($updateSql);
  $stmtUpdate->bind_param("si", $newAppointmentDate, $appointmentId);
  $stmtUpdate->execute();
  $stmtUpdate->close();
  header("Refresh: 0");
}

// Handle report upload
if (isset($_POST['update_report'])) {
  $appointmentId = $_POST['appointment_id'];

  // Handle file upload
  $uploadedFiles = [];
  if (!empty($_FILES['report_images']['name'][0])) {
    $fileCount = count($_FILES['report_images']['name']);
    
    for ($i = 0; $i < $fileCount; $i++) {
      $fileTmpPath = $_FILES['report_images']['tmp_name'][$i];
      $fileName = $_FILES['report_images']['name'][$i];
      $filePath = "../uploads/" . $fileName;

      // Move uploaded files to the "uploads" folder
      if (move_uploaded_file($fileTmpPath, $filePath)) {
        $uploadedFiles[] = $fileName;
      }
    }

    if (count($uploadedFiles) > 0) {
      $filePaths = implode(",", $uploadedFiles); // store file paths as comma-separated list
      $updateSql = "UPDATE $table[APPOINTMENT] SET report = ? WHERE id = ?";
      $stmtUpdate = $conn->prepare($updateSql);
      $stmtUpdate->bind_param("si", $filePaths, $appointmentId);
      $stmtUpdate->execute();
      $stmtUpdate->close();
      header("Refresh: 0");
    }
  }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Appointment Report</title>
  <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
  <!-- Modal CSS (add if not in your admin-dashboard.css) -->
  <style>
    .modal {
      display: none; 
      position: fixed;
      z-index: 9999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.5);
    }
    .modal-content {
      background-color: #fff;
      margin: 15% auto;
      padding: 20px;
      border: 1px solid #888;
      width: 300px;
      border-radius: 5px;
    }
    .close {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }
    .close:hover {
      color: #000;
    }
    .file-upload input[type="file"] {
      padding: 5px;
    }
  </style>
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
              <th>Report</th>
            </tr>
          </thead>
          <tbody id="appointmentsBody">
            <?php if (count($data) > 0): ?>
              <?php foreach ($data as $row): ?>
                <tr>
                  <td><?php echo $row["id"]; ?></td>
                  <td><?php echo $row["name"]; ?></td>
                  <td><?php echo $row["email"]; ?></td>
                  <td><?php echo $row["phone"]; ?></td>
                  <td><?php echo $row["package"]; ?></td>
                  <td>
                    <button class="openModalBtn" data-appointment-id="<?php echo $row["id"]; ?>" data-current-date="<?php echo $row["date"]; ?>">
                      <?php echo $row["report"] ? "View/Change Report" : "Upload Report"; ?>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
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

  <!-- Modal for Uploading/View Reports -->
  <div id="appointmentModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2>Upload / View Report</h2>
      <form action="" method="POST" enctype="multipart/form-data">
        <!-- Hidden field to pass the appointment id -->
        <input type="hidden" id="appointment_id" name="appointment_id">
        
        <!-- This div will be updated by JavaScript -->
        <div id="reportDisplay"></div>
        
        <button type="submit" name="update_report">Save Report</button>
      </form>
    </div>
  </div>

  <script>
    function fetchAppointments() {
      var selectedDate = document.getElementById("appointmentDate").value;
      var tableBody = document.getElementById("appointmentsBody");

      tableBody.innerHTML = "";

      if (!selectedDate) {
        alert("Please select a date.");
        return;
      }

      fetch("fetch-appointments.php?date=" + selectedDate)
        .then(response => response.json())
        .then(data => {
          tableBody.innerHTML = "";
          if (data.length > 0) {
            let rows = data.map(app => {
              return `<tr>
                <td>${app.id}</td>
                <td>${app.name}</td>
                <td>${app.email}</td>
                <td>${app.phone}</td>
                <td>${app.package}</td>
                <td>
                  ${!app.report ? `<button class="openModalBtn" data-appointment-id="${app.id}">Set Report</button>` : `<button class="openModalBtn" data-appointment-id="${app.id}" data-report="${app.report}">View Report</button>`}
                </td>
              </tr>`;
            }).join('');
            tableBody.innerHTML = rows;
            attachModalEvents();
          } else {
            tableBody.innerHTML = `<tr><td colspan="6" class="no-data">No appointments found</td></tr>`;
          }
        })
        .catch(error => console.error("Error fetching data:", error));
    }

    function attachModalEvents() {
      var modal = document.getElementById("appointmentModal");
      var btns = document.querySelectorAll(".openModalBtn");
      btns.forEach(function(btn) {
        btn.onclick = function() {
          var appointmentId = this.getAttribute("data-appointment-id");
          var report = this.getAttribute("data-report"); // may be empty or "null"
          document.getElementById("appointment_id").value = appointmentId;

          if (report && report.trim() !== "" && report !== "null") {
            var reports = report.split(",");
            var reportDisplayHtml = "<p>Current Reports:</p>";
            reports.forEach(function(file) {
              reportDisplayHtml += `<img src="../uploads/${file}" alt="Report Image" style="max-width:100%; height:auto; margin-bottom:10px;">`;
            });
            reportDisplayHtml += `
              <p>Change Reports (optional):</p>
              <input type="file" name="report_images[]" accept="image/*" multiple>
            `;
            document.getElementById("reportDisplay").innerHTML = reportDisplayHtml;
          } else {
            document.getElementById("reportDisplay").innerHTML = `
              <p>Upload Reports:</p>
              <input type="file" name="report_images[]" accept="image/*" multiple>
            `;
          }
          modal.style.display = "block";
        };
      });
    }

    // Attach events on page load
    attachModalEvents();

    // Close modal when the close button is clicked
    var span = document.getElementsByClassName("close")[0];
    span.onclick = function() {
      document.getElementById("appointmentModal").style.display = "none";
    };

    // Close modal when clicking outside the modal content
    window.onclick = function(event) {
      var modal = document.getElementById("appointmentModal");
      if (event.target == modal) {
        modal.style.display = "none";
      }
    };
  </script>

</body>
</html>
