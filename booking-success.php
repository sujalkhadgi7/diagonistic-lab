<?php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "jobportal";
   
   // Create connection
   $conn = new mysqli($servername, $username, $password, $dbname);
   
   // Check connection
   if ($conn->connect_error) {
       die("Connection failed: " . $conn->connect_error);
   }
   
   if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['name'])) {
       $name = mysqli_real_escape_string($conn, $_POST['name']);
       $email = mysqli_real_escape_string($conn, $_POST['email']);; // Replace with actual value or $_POST variable
       $phone = mysqli_real_escape_string($conn, $_POST['phone']);       // Replace with actual value or $_POST variable
       $package = mysqli_real_escape_string($conn, $_POST['package']);       // Replace with actual value or $_POST variable

       $sql = "INSERT INTO appointment (name, email,phone, package, date) VALUES ('$name', '$email','$phone', '$package', NULL)";

       if ($conn->query($sql) === TRUE) {
           echo "New record created successfully";
       } else {
           echo "Error: " . $sql . "<br>" . $conn->error;
       }
   }
   $conn->close();
?>


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
                <li><a href="." >Home</a></li>
                <li><a href="about.php" >About Us</a></li>
                <li><a href="health-package.php" >Health Packages</a></li>
                <li><a href="contact.php" >Contact</a></li>
                <li><a href="test-results.php" >Test Results</a></li>
            </ul>
        </nav>
    </header>

    <!-- Home Section -->
    <section id="home" class="section-container active">
        <h2>successfully filled the form</h2>
        <?php 
            $name = $_POST['name'];

            echo"<h2>$name</h2> "
        ?>
        
    </section>

    <footer>
        <p>&copy; 2024 OS Diagnostic Lab | All Rights Reserved</p>
    </footer>
    
    </body>
</html>