<?php
require_once '../src/db.php';
session_start();

$loginError = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login-btn'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validate input
    if (empty($username)) {
        $loginError = 'Username is required.';
    } elseif (empty($password)) {
        $loginError = 'Password is required.';
    } elseif (strlen($username) < 3) {
        $loginError = 'Invalid username format.';
    } else {
        // Use prepared statement to prevent SQL injection
        $sql = "SELECT id, username, Name, type FROM {$table['USER']} WHERE username = ? AND password = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $loginError = 'Database error. Please try again later.';
        } else {
            $stmt->bind_param('ss', $username, $password);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $_SESSION['uid'] = (int) $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['Name'];
                $_SESSION['usertype'] = $user['type'];
                $_SESSION['loggedIn'] = true;
                $stmt->close();
                header('Location: dashboard.php');
                exit;
            } else {
                $loginError = 'Invalid username or password.';
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="admin-login">
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-header">
                <h2>Admin Dashboard</h2>
                <p>Diagnostic Lab Management System</p>
            </div>

            <?php if ($loginError !== ''): ?>
                <div class="login-alert error">
                    <span class="alert-icon">⚠️</span>
                    <p>
                        <?php echo htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username"
                        value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" required
                        autocomplete="username" minlength="3">
                    <span class="input-hint">Enter your admin username</span>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required
                        autocomplete="current-password" minlength="1">
                    <span class="input-hint">Enter your admin password</span>
                </div>

                <button type="submit" name="login-btn" class="login-btn">Sign In</button>
            </form>

            <div class="login-footer">
                <p><small>© 2026 Diagnostic Lab. All rights reserved.</small></p>
            </div>
        </div>
    </div>
</body>

</html>