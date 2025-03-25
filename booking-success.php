<?php
   require 'src/db.php';
   
   if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['name'])) {
       $name = mysqli_real_escape_string($conn, $_POST['name']);
       $email = mysqli_real_escape_string($conn, $_POST['email']); 
       $phone = mysqli_real_escape_string($conn, $_POST['phone']);       
       $package = mysqli_real_escape_string($conn, $_POST['package']);      

       $sql = "INSERT INTO $table[APPOINTMENT] (name, email,phone, package, date) VALUES ('$name', '$email','$phone', '$package', NULL)";

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
    <title>OM Diagnostic Lab</title>
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
    <header>
        <div class="logo">
            <h1>OM Diagnostic Lab</h1>
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
        <p>&copy; 2024 OM Diagnostic Lab | All Rights Reserved</p>
    </footer>
    
    </body>
</html>