<?php
require_once("./src/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    $sql = "SELECT id, name, password FROM customers WHERE email='$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            session_start();
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION["loggedIn"] = true;
            header("Location: index.php");
            exit();
        } else {
            echo "<p class='error-message'>Incorrect password.</p>";
        }
    } else {
        echo "<p class='error-message'>No account found with this email.</p>";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
            background-color: #4CAF50;
            width: 100%;
            padding: 20px;
            text-align: center;
            color: white;
        }

        /* Form Container */
        .form-container {
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
            margin-bottom: 20px;
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
            border-color: #4CAF50;
            outline: none;
            box-shadow: 0px 0px 5px rgba(76, 175, 80, 0.5);
        }

        /* Error Message */
        .error-message {
            color: red;
            font-weight: bold;
            margin-top: 10px;
        }

        /* Button */
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
            background-color: #45a049;
        }

        /* Footer */
        footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }

        /* Link Styles */
        a {
            color: #4CAF50;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<header>
    <div class="logo"><h1>OM Diagnostic Lab</h1></div>
</header>

<div class="form-container">
    <h2>Login</h2>
    <form method="POST">
        <div class="input-group">
            <input type="email" name="email" placeholder="Email" required>
        </div>
        <div class="input-group">
            <input type="password" name="password" placeholder="Password" required>
        </div>
        <button type="submit" class="btn">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register</a></p>
</div>

<footer>
    <p>&copy; 2024 OM Diagnostic Lab | All Rights Reserved</p>
</footer>

</body>
</html>

