<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>401 Unauthorized</title>
    <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>
    <?php
    $currentPage = '';
    $showNav = false;
    include_once __DIR__ . '/includes/header.php';
    ?>

    <section id="error401" class="section-container">
        <div class="error-panel">
            <p class="error-code">401</p>
            <h2>Unauthorized Access</h2>
            <p>You must be logged in to view this page.</p>
            <div class="error-actions">
                <a href="login.php" class="btn">Login</a>
                <a href="." class="btn btn-outline">Back to Home</a>
            </div>
        </div>
    </section>

    <?php include_once __DIR__ . '/includes/footer.php'; ?>

</body>

</html>
