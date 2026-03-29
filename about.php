<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
    <?php
    $currentPage = 'about';
    include_once __DIR__ . '/includes/header.php';
    ?>

    <!-- About Us Section -->
    <section id="about" class="section-container">
        <h2>About Us</h2>
        <p>OM Diagnostic Lab is committed to providing comprehensive diagnostic services. We offer a wide range of
            health packages designed to meet your needs and ensure your well-being. Our team of medical experts ensures
            accurate results with state-of-the-art equipment.</p>
    </section>

    <?php include_once __DIR__ . '/includes/footer.php'; ?>

</body>

</html>