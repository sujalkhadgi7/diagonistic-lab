<?php
require_once "./src/db.php";

$feedbackMessage = '';
$feedbackType = '';
$registrationFailedMessage = 'Registration failed. Please try again.';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $checkEmailSql = "SELECT id FROM $table[COSTUMERS] WHERE email = ?";
    $checkStmt = $conn->prepare($checkEmailSql);

    if ($checkStmt) {
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $feedbackType = 'error';
            $feedbackMessage = 'Email already exists. Please login with your account.';
        } else {
            // Insert new user if email does not exist
            $sql = "INSERT INTO $table[COSTUMERS] (name, email, phone, password) VALUES (?, ?, ?, ?)";
            $insertStmt = $conn->prepare($sql);

            if ($insertStmt) {
                $insertStmt->bind_param('ssss', $name, $email, $phone, $password);

                if ($insertStmt->execute()) {
                    $feedbackType = 'success';
                    $feedbackMessage = 'Registration successful. You can now login.';
                } else {
                    $feedbackType = 'error';
                    $feedbackMessage = $registrationFailedMessage;
                }

                $insertStmt->close();
            } else {
                $feedbackType = 'error';
                $feedbackMessage = $registrationFailedMessage;
            }
        }

        $checkStmt->close();
    } else {
        $feedbackType = 'error';
        $feedbackMessage = $registrationFailedMessage;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | OM Diagnostic Lab</title>
    <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>
    <?php
    $currentPage = '';
    $showNav = false;
    include_once __DIR__ . '/includes/header.php';
    ?>

    <section id="registerPage" class="section-container auth-shell">
        <section class="auth-panel" aria-labelledby="registerHeading">
            <p class="auth-kicker">Create Account</p>
            <h2 id="registerHeading">Join OM Diagnostic Lab</h2>
            <p class="auth-subtitle">Register once to book appointments faster and track reports easily.</p>

            <?php if (!empty($feedbackMessage)): ?>
                <p class="<?php echo $feedbackType === 'success' ? 'auth-success' : 'auth-error'; ?>" role="alert">
                    <?php echo htmlspecialchars($feedbackMessage, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            <?php endif; ?>

            <form method="POST" class="auth-form" novalidate>
                <div class="input-group">
                    <label for="registerName">Full Name</label>
                    <input id="registerName" type="text" name="name" placeholder="Your full name" autocomplete="name"
                        required>
                </div>
                <div class="input-group">
                    <label for="registerEmail">Email</label>
                    <input id="registerEmail" type="email" name="email" placeholder="you@example.com"
                        autocomplete="email" required>
                </div>
                <div class="input-group">
                    <label for="registerPassword">Password</label>
                    <input id="registerPassword" type="password" name="password" placeholder="Create a password"
                        autocomplete="new-password" required>
                </div>
                <div class="input-group">
                    <label for="registerPhone">Phone Number</label>
                    <input id="registerPhone" type="tel" name="phone" placeholder="98XXXXXXXX" autocomplete="tel"
                        required>
                </div>
                <button type="submit" class="btn">Register</button>
            </form>

            <p class="auth-footnote">Already have an account? <a href="login.php">Login</a></p>
        </section>
    </section>

    <?php include_once __DIR__ . '/includes/footer.php'; ?>
</body>

</html>
