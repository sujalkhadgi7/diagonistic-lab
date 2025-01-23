

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OS Diagnostic Lab</title>
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
                <li><a href="health-package.php" >Health Packages</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="test-results.php">Test Results</a></li>
            </ul>
        </nav>
    </header>

     <!-- Booking Form (Hidden by default) -->
     <div id="booking-form" class="section-container">
        <h2>Booking Appointment</h2>
        <form id="appointment-form" action="./src/appointment.php" method="POST">
            <label for="patient-name">Name:</label>
            <input type="text" id="patient-name" name="patient-name" required>
            <label for="patient-email">Email:</label>
            <input type="email" id="patient-email" name="patient-email" required>
            <label for="package-name">Health Package:</label>
            <input type="text" id="package-name" name="package-name" value="<?php if(isset($_GET['package-name'])) echo $_GET['package-name'] ;?>"  disabled />
            <!-- <label for="appointment-date">Date:</label>
            <input type="date" id="appointment-date" name="appointment-date" required> -->
            <button type="submit">Book Now</button>                                                                       
        </form>
    </div>

    <footer>
        <p>&copy; 2024 OS Diagnostic Lab | All Rights Reserved</p>
    </footer>
    
    </body>
</html>

