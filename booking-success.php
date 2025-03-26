<?php
require 'src/db.php';
session_start();

// Check if user is logged in by verifying session


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escape user input for security

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    
        // Escape the user_id for security
        $user_id = mysqli_real_escape_string($conn, $user_id);
    
        // Fetch user information from the COSTUMERS table based on user_id
        $sql = "SELECT * FROM $table[COSTUMERS] WHERE id = '$user_id' LIMIT 1";
        $result = $conn->query($sql);
    
        if ($result && $result->num_rows > 0) {
            // Fetch user data from the result
            $user = $result->fetch_assoc();
            $name = $user['name'];
            $email = $user['email'];
            $phone = $user['phone'];  // Assuming phone number is stored in the table
        } else {
            echo "No user found with the provided ID.";
        }
    } else {
        header("Location: login.php");
    }   

    // Convert selected packages into a comma-separated string
    if (isset($_POST['packages']) && is_array($_POST['packages'])) {
        $packages = implode(",", $_POST['packages']);
    } else {
        $packages = "";  // If no package selected, set as an empty string
    }

    // Insert the data into the database
    $sql = "INSERT INTO $table[APPOINTMENT] (name, email, phone, package, date) 
            VALUES ('$name', '$email', '$phone', '$packages', NULL)";

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
        <?php 
            if (isset($_SESSION["user_name"])) {
                echo "<h2>Successfully filled the form</h2>";
                $name = $_SESSION["user_name"];
                echo "<h3>Thank you for your submission, $name.</h3>";
            }
        ?>
        
    </section>

    <footer>
        <p>&copy; 2024 OM Diagnostic Lab | All Rights Reserved</p>
    </footer>
</body>
</html>
