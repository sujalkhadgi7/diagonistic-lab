<?php
require_once "./src/db.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errorMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, name, password FROM customers WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['email'] = $email;
            $_SESSION["loggedIn"] = true;
            header("Location: index.php");
            exit();
        } else {
            $errorMessage = 'Incorrect password.';
        }
    } else {
        $errorMessage = 'No account found with this email.';
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | OM Diagnostic Lab</title>
    <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>
    <?php
    $currentPage = '';
    $showNav = false;
    include_once __DIR__ . '/includes/header.php';
    ?>

    <section id="loginPage" class="section-container auth-shell">
        <section class="auth-panel" aria-labelledby="loginHeading">
            <p class="auth-kicker">Welcome Back</p>
            <h2 id="loginHeading">Sign in to your account</h2>
            <p class="auth-subtitle">Access your reports and manage your appointments in one place.</p>

            <?php if (!empty($errorMessage)): ?>
                <p class="auth-error" role="alert"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <form method="POST" class="auth-form" novalidate>
                <div class="input-group">
                    <label for="loginEmail">Email</label>
                    <input id="loginEmail" type="email" name="email" placeholder="you@example.com" required>
                </div>
                <div class="input-group">
                    <label for="loginPassword">Password</label>
                    <input id="loginPassword" type="password" name="password" placeholder="Enter your password"
                        required>
                </div>
                <button type="submit" class="btn">Login</button>
            </form>

            <p class="auth-footnote">Don't have an account? <a href="register.php">Register</a></p>
        </section>
    </section>

    <?php include_once __DIR__ . '/includes/footer.php'; ?>

</body>

</html>