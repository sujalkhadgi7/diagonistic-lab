<?php
   require_once("./src/db.php");
   session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OM Diagnostic Lab</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <style>
        .login-btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            font-size: 16px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        .login-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <h1>OM Diagnostic Lab</h1>
            <?php  
                if (isset($_SESSION["user_name"])) {
                    echo "<p>Welcome, " . $_SESSION["user_name"] . "</p>";
                } else {
                    echo '<a href="login.php" class="login-btn">Login</a>';
                }
            ?>
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
        <h2>Welcome to OM Diagnostic Lab</h2>
        <p>Providing quality health diagnostic services with a wide range of packages to meet your needs.</p>
        <div class="home-image-container">
            <img src="./assets/image/lab_photo.jpg" alt="Diagnostic Lab Image" class="home-image">
        </div>
    </section>

    <footer>
        <p>&copy; 2024 OM Diagnostic Lab | All Rights Reserved</p>
    </footer>
    
</body>
</html>
