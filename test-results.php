<?php
require 'src/db.php';  
session_start();

if (!isset($_SESSION['email'])) {
  header("Location: 401.php");
}

$email = $_SESSION['email'];  // Get email from session

// Initialize variables
$reports = [];
$message = "Here you can check the results of your tests once they are available.";

// Query for an appointment matching the email
$stmt = $conn->prepare("SELECT * FROM appointment WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();
$stmt->close();

// If an appointment is found and it has a report, process it
if ($appointment) {
    if (!empty($appointment['report'])) {
        // Convert the comma-separated string into an array
        $reports = explode(",", $appointment['report']);
        $message = "Your appointment is found, and the test reports are available below.";
        } else {
        $message = "Your appointment is found, but no report is available yet.";
    }
} else {
    $message = "No appointment found for your account.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Test Results - OM Diagnostic Lab</title>
  <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
  <header>
    <div class="logo">
      <h1>OM Diagnostic Lab</h1>
    </div>
    <nav>
      <ul>
        <li><a href=".">Home</a></li>
        <li><a href="about.php">About Us</a></li>
        <li><a href="health-package.php">Health Packages</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="test-results.php">Test Results</a></li>
      </ul>
    </nav>
  </header>

  <!-- Test Results Section -->
  <section id="results" class="section-container">
    <h2>Test Results</h2>

    <p><?php echo $message; ?></p>

    <?php if (!empty($reports)): ?>
      <div class="report-gallery">
        <p>Your test result images:</p>
        <?php foreach ($reports as $report): ?>
          <div class="report-item">
            <img src="./uploads/<?php echo trim($report); ?>" alt="Test Report" style="max-width:100%; height:auto; margin-bottom: 10px;">
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <footer>
    <p>&copy; 2024 OM Diagnostic Lab | All Rights Reserved</p>
  </footer>
</body>
</html>
