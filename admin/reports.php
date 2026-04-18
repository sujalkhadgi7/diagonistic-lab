<?php
require_once '../src/db.php';
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

function deleteReportFiles($reportCsv, $uploadDir)
{
  if (empty($reportCsv)) {
    return;
  }

  $files = array_filter(array_map('trim', explode(',', $reportCsv)));
  foreach ($files as $file) {
    $safeName = basename($file);
    $fullPath = rtrim($uploadDir, '/') . '/' . $safeName;
    if (is_file($fullPath)) {
      @unlink($fullPath);
    }
  }
}

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
  $removeExistingReport = isset($_POST['remove_existing_report']) && $_POST['remove_existing_report'] === '1';
  $uploadDir = "../uploads/";

  if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
  }

  $existingReportCsv = '';
  $existingSql = "SELECT report FROM $table[APPOINTMENT] WHERE id = ?";
  $stmtExisting = $conn->prepare($existingSql);
  $stmtExisting->bind_param("i", $appointmentId);
  $stmtExisting->execute();
  $existingResult = $stmtExisting->get_result();
  if ($existingRow = $existingResult->fetch_assoc()) {
    $existingReportCsv = $existingRow['report'] ?? '';
  }
  $stmtExisting->close();

  // Handle file upload
  $uploadedFiles = [];
  if (!empty($_FILES['report_images']['name'][0])) {
    $fileCount = count($_FILES['report_images']['name']);

    for ($i = 0; $i < $fileCount; $i++) {
      $fileTmpPath = $_FILES['report_images']['tmp_name'][$i];
      $fileName = basename($_FILES['report_images']['name'][$i]);
      $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

      // Accept only common image formats.
      $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
      if (!in_array($fileExt, $allowedExtensions)) {
        continue;
      }

      $newFileName = uniqid('report_', true) . '.' . $fileExt;
      $filePath = $uploadDir . $newFileName;

      // Move uploaded files to the "uploads" folder
      if (move_uploaded_file($fileTmpPath, $filePath)) {
        $uploadedFiles[] = $newFileName;
      }
    }
  }

  if ($removeExistingReport && empty($uploadedFiles) && !empty($existingReportCsv)) {
    deleteReportFiles($existingReportCsv, $uploadDir);
    $clearSql = "UPDATE $table[APPOINTMENT] SET report = NULL WHERE id = ?";
    $stmtClear = $conn->prepare($clearSql);
    $stmtClear->bind_param("i", $appointmentId);
    $stmtClear->execute();
    $stmtClear->close();
    header("Refresh: 0");
  }

  if (!empty($uploadedFiles)) {
    // Replacing reports should remove old files from disk.
    if (!empty($existingReportCsv)) {
      deleteReportFiles($existingReportCsv, $uploadDir);
    }

    $filePaths = implode(",", array_values(array_unique($uploadedFiles)));
    $updateSql = "UPDATE $table[APPOINTMENT] SET report = ? WHERE id = ?";
    $stmtUpdate = $conn->prepare($updateSql);
    $stmtUpdate->bind_param("si", $filePaths, $appointmentId);
    $stmtUpdate->execute();
    $stmtUpdate->close();
    header("Refresh: 0");
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Appointment Report</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="admin-panel">

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Admin Panel</h2>
    <ul>
      <li><a href="dashboard.php">Dashboard</a></li>
      <li><a href="users.php">Users</a></li>
      <li><a href="appointments.php">Appointments</a></li>
      <li><a href="health-package.php">Health Packages</a></li>
      <li><a href="reports.php" class="active">Reports</a></li>
      <li><a href="patient-results.php">Patient Results</a></li>
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
                    <button class="openModalBtn" data-appointment-id="<?php echo $row["id"]; ?>"
                      data-current-date="<?php echo $row["date"]; ?>"
                      data-report="<?php echo htmlspecialchars($row["report"] ?? '', ENT_QUOTES); ?>">
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
      <form id="reportForm" action="" method="POST" enctype="multipart/form-data">
        <!-- Hidden field to pass the appointment id -->
        <input type="hidden" id="appointment_id" name="appointment_id">

        <!-- This div will be updated by JavaScript -->
        <div id="reportDisplay"></div>

        <button type="submit" name="update_report">Save Report</button>
      </form>
    </div>
  </div>

  <script>
    function buildUploaderHtml(labelText, isRequired) {
      return `
        <p>${labelText}</p>
        <input type="file" name="report_images[]" accept="image/*" multiple ${isRequired ? 'required' : ''}>
        <p class="upload-help">Tip: You can select multiple images at once (Cmd/Ctrl + click).</p>
        <div id="selectedImagePreview" class="selected-image-preview"></div>
      `;
    }

    function renderSelectedImages(files) {
      var previewRoot = document.getElementById("selectedImagePreview");
      if (!previewRoot) {
        return;
      }

      previewRoot.innerHTML = "";

      if (!files || files.length === 0) {
        return;
      }

      var title = document.createElement("p");
      title.textContent = "Selected images:";
      previewRoot.appendChild(title);

      for (var i = 0; i < files.length; i++) {
        var file = files[i];

        if (!file.type || file.type.indexOf("image/") !== 0) {
          continue;
        }

        var item = document.createElement("div");
        item.className = "selected-image-item";

        var img = document.createElement("img");
        img.className = "report-preview-image";
        img.alt = "Selected report image";

        var name = document.createElement("p");
        name.textContent = file.name;

        var objectUrl = URL.createObjectURL(file);
        img.src = objectUrl;
        img.onload = function () {
          URL.revokeObjectURL(this.src);
        };

        item.appendChild(img);
        item.appendChild(name);
        previewRoot.appendChild(item);
      }
    }

    function fetchAppointments() {
      var selectedDate = document.getElementById("appointmentDate").value;
      var tableBody = document.getElementById("appointmentsBody");

      tableBody.innerHTML = "";

      if (!selectedDate) {
        alert("Please select a date.");
        return;
      }

      fetch("fetch-appointments.php?date=" + encodeURIComponent(selectedDate))
        .then(response => response.json())
        .then(payload => {
          tableBody.innerHTML = "";
          if (!payload.success) {
            tableBody.innerHTML = `<tr><td colspan="6" class="no-data">${payload.error || "Failed to fetch appointments"}</td></tr>`;
            return;
          }

          const appointments = (payload.data && payload.data.appointments) ? payload.data.appointments : [];

          if (appointments.length > 0) {
            let rows = appointments.map(app => {
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
        .catch(() => {
          tableBody.innerHTML = `<tr><td colspan="6" class="no-data">Error fetching appointments</td></tr>`;
        });
    }

    function attachModalEvents() {
      var modal = document.getElementById("appointmentModal");
      var btns = document.querySelectorAll(".openModalBtn");
      btns.forEach(function (btn) {
        btn.onclick = function () {
          var appointmentId = this.getAttribute("data-appointment-id");
          var report = this.getAttribute("data-report"); // may be empty or "null"
          document.getElementById("appointment_id").value = appointmentId;

          if (report && report.trim() !== "" && report !== "null") {
            var reports = report.split(",");
            var reportDisplayHtml = "<p>Current Reports:</p>";
            reports.forEach(function (file) {
              reportDisplayHtml += `<img src="../uploads/${file}" alt="Report Image" class="report-preview-image">`;
            });
            reportDisplayHtml += `
              <label>
                <input type="checkbox" id="remove_existing_report" name="remove_existing_report" value="1">
                Remove current reports
              </label>
            `;
            reportDisplayHtml += buildUploaderHtml("Change Reports (optional):", false);
            document.getElementById("reportDisplay").innerHTML = reportDisplayHtml;
          } else {
            document.getElementById("reportDisplay").innerHTML = buildUploaderHtml("Upload Reports:", true);
          }
          modal.style.display = "block";
        };
      });
    }

    document.addEventListener("change", function (event) {
      if (event.target && event.target.name === "report_images[]") {
        renderSelectedImages(event.target.files);
      }
    });

    document.getElementById("reportForm").addEventListener("submit", function (event) {
      var removeCheckbox = document.getElementById("remove_existing_report");
      if (removeCheckbox && removeCheckbox.checked) {
        var confirmRemove = confirm("Are you sure you want to remove the current report files? This cannot be undone.");
        if (!confirmRemove) {
          event.preventDefault();
        }
      }
    });

    // Attach events on page load
    attachModalEvents();

    // Close modal when the close button is clicked
    var span = document.getElementsByClassName("close")[0];
    span.onclick = function () {
      document.getElementById("appointmentModal").style.display = "none";
    };

    // Close modal when clicking outside the modal content
    window.onclick = function (event) {
      var modal = document.getElementById("appointmentModal");
      if (event.target == modal) {
        modal.style.display = "none";
      }
    };
  </script>

</body>

</html>