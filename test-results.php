<?php
require 'src/db.php';  // Adjust the path as needed

// Initialize variables
$report = null;
$message = "Here you can check the results of your tests once they are available.";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get input values
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Query for an appointment matching the email and phone
    $stmt = $conn->prepare("SELECT * FROM appointment WHERE email = ? AND phone = ? LIMIT 1");
    $stmt->bind_param("ss", $email, $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointment = $result->fetch_assoc();
    $stmt->close();

    // If an appointment is found and it has a report, use that report
    if ($appointment && !empty($appointment['report'])) {
        $report = $appointment['report'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Test Results - OS Diagnostic Lab</title>
  <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
  <header>
    <div class="logo">
      <h1>OS Diagnostic Lab</h1>
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


    <!-- Form for Email and Phone Input -->
    <form method="POST" action="test-results.php" class="result-form">
      <label for="email">Email:</label>
      <input type="email" name="email" id="email" required>
      
      <label for="phone">Phone Number:</label>
      <input type="text" name="phone" id="phone" required>
      
      <button type="submit">Check Test Results</button>
    </form>
    
    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
      <?php if ($report): ?>
        <div class="report">
          <p>Your test result is available:</p>
          <img src="./uploads/<?php echo $report; ?>" alt="Test Result Report" style="max-width:100%; height:auto;">
        </div>
      <?php else: ?>
        <p><?php echo $message; ?></p>
      <?php endif; ?>
    <?php else: ?>
      <p><?php echo $message; ?></p>
    <?php endif; ?>

    
  </section>

  <footer>
    <p>&copy; 2024 OS Diagnostic Lab | All Rights Reserved</p>
  </footer>
</body>
</html>
