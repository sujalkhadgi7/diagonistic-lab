

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

     <div id="booking-form" class="section-container">
        <h2>Booking Appointment</h2>
        <form  action="booking-success.php" method="POST" enctype="multipart/form-data">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
            <label for="phone">Phone:</label>
            <input type="tel" id="phone" name="phone" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="package-name">Health Package:</label>
            <input type="text" id="package" name="package" value="<?php if(isset($_GET['package'])) echo $_GET['package'] ;?>" readonly />
            <button type="submit">Book Now</button>                                                                       
        </form>
    </div>

    <footer>
        <p>&copy; 2024 OS Diagnostic Lab | All Rights Reserved</p>
    </footer>
    
    </body>
</html>



 
