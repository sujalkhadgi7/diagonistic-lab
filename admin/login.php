<?php
    require '../src/db.php';
    if(isset($_POST['login-btn']))
    {
        $username = $_POST['username'];
        $password = $_POST['password'];
    
        $sql = "SELECT * FROM $table[USER] WHERE username='$username' 
                AND password='$password'";
        $data = $conn->query($sql);
        if($data->num_rows > 0)
        {
            $user = $data->fetch_assoc();
            session_start();
            $_SESSION['uid'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['Name'];
            $_SESSION['usertype'] = $user['type'];
            $_SESSION['loggedIn'] = true;
            header('location:dashboard.php');
        }
        else
            echo '<script>alert("Invalid Credentials!")</script>';
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        <form  method="POST">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" name="login-btn"  class="login-btn">Login</button>
        </form>
    </div>
</body>
</html>
