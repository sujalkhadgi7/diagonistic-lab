<?php
require_once("./src/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $checkEmailSql = "SELECT id FROM $table[COSTUMERS] WHERE email = ?";
    $stmt = $conn->prepare($checkEmailSql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<p class='error-message'>Email already exists. Please use a different email or <a href='login.php'>Login here</a>.</p>";
    } else {
        // Insert new user if email does not exist
        $stmt->close();
        $sql = "INSERT INTO $table[COSTUMERS] (name, email,phone, password) VALUES (?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssss', $name, $email, $phone, $password);
        
        if ($stmt->execute()) {
            echo "<p class='success-message'>Registration successful. <a href='login.php'>Login here</a></p>";
        } else {
            echo "<p class='error-message'>Error: " . $stmt->error . "</p>";
        }
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Header */
        header {
            width: 100%;
            background-color: #4CAF50;
            padding: 15px 0;
            text-align: center;
        }

        header .logo h1 {
            color: white;
            margin: 0;
        }

        nav ul {
            list-style: none;
            padding: 0;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        nav ul li {
            display: inline;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        /* Registration Container */
        .register-container {
            background-color: white;
            width: 100%;
            max-width: 400px;
            padding: 25px;
            margin-top: 50px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            color: #333;
        }

        /* Input Fields */
        .input-group {
            margin-bottom: 15px;
        }

        .input-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .input-group input:focus {
            border-color: ##4CAF50;
            outline: none;
            box-shadow: 0px 0px 5px rgba(0, 123, 255, 0.5);
        }

        /* Register Button */
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .btn:hover {
            background-color: #4CAF50;
        }

        /* Success & Error Messages */
        .success-message {
            color: green;
            font-weight: bold;
            margin-top: 10px;
        }

        .error-message {
            color: red;
            font-weight: bold;
            margin-top: 10px;
        }

        /* Footer */
        footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>


<header>
        <div class="logo">
            <h1>OM Diagnostic Lab</h1>
        </div>
    </header>

    <div class="register-container">
        <h2>Create an Account</h2>
        <form method="POST">
            <div class="input-group">
                <input type="text" name="name" placeholder="Full Name" autocomplete="off" required>
            </div>
            <div class="input-group">
                <input type="email" name="email" placeholder="Email" autocomplete="off" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" autocomplete="off" required>
            </div>
            <div class="input-group">
                <input type="phone" name="phone" placeholder="Phone Number" autocomplete="off" required>
            </div>
            <button type="submit" class="btn">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>

    <footer>
        <p>&copy; 2024 OM Diagnostic Lab | All Rights Reserved</p>
    </footer>
</body>
</html>
