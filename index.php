<?php
require_once "./src/db.php";
session_start();
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
    $currentPage = 'home';
    include_once __DIR__ . '/includes/header.php';
    ?>

    <main class="home-page">
        <section class="hero">
            <div class="hero-content">
                <h1>Tomorrow's Diagnostics, <br>Available Today.</h1>
                <p>OM Diagnostic Lab is committed to providing ultra-precise pathological services. We combine
                    world-class medical technology with a seamless digital experience.</p>
                <a href="health-package.php" class="cta-button">Browse Health Packages</a>
            </div>
            <div class="hero-image-area">
                <img src="./assets/image/hero.png" alt="Scientist in Modern Lab" class="featured-img">
            </div>
        </section>

        <section class="stats-bar">
            <div class="stat-item">
                <h2>2.5M+</h2>
                <p>Samples Analyzed</p>
            </div>
            <div class="stat-item">
                <h2>45+</h2>
                <p>Centers</p>
            </div>
            <div class="stat-item">
                <h2>200+</h2>
                <p>Pathologists</p>
            </div>
            <div class="stat-item">
                <h2>99.9%</h2>
                <p>Accuracy</p>
            </div>
        </section>

        <section class="section-padding">
            <div class="detail-row">
                <div class="detail-text">
                    <h2>The Gold Standard in Clinical Testing</h2>
                    <p>Accuracy isn't just a goal-it's a promise. Our laboratory is equipped with high-end automated
                        analyzers that minimize human intervention, ensuring every result is clinically validated.</p>
                    <p>We strictly follow NABL and ISO protocols to manage our cold-chain logistics, ensuring sample
                        viability from collection to final report.</p>
                </div>
                <div class="detail-img">
                    <img src="./assets/image/women.png" alt="Medical Lab Technology" class="featured-img">
                </div>
            </div>

            <div class="detail-row reverse">
                <div class="detail-text">
                    <h2>A Completely Paperless Experience</h2>
                    <p>Say goodbye to the stress of physical reports. Our integrated health platform allows you to
                        manage your entire medical history in one secure place.</p>
                    <p>Once tests are processed, our system triggers an automated email. Log in to your personal
                        dashboard to view, download, or share high-resolution PDF reports instantly.</p>
                </div>
                <div class="detail-img">
                    <img src="./assets/image/hands.png" alt="Viewing Digital Health Reports" class="featured-img">
                </div>
            </div>
        </section>

        <section class="section-padding section-soft-bg">
            <h2 class="section-title">Our Efficient Process</h2>
            <div class="workflow-grid">
                <div class="step-card">
                    <h3>1. Digital Booking</h3>
                    <p>Select your health package and book online. No paperwork, no hassle.</p>
                </div>
                <div class="step-card">
                    <h3>2. Admin Scheduling</h3>
                    <p>Our team verifies your booking and schedules your slot immediately.</p>
                </div>
                <div class="step-card">
                    <h3>3. Automated Alerts</h3>
                    <p>Get a confirmation email with all instructions for your test preparation.</p>
                </div>
                <div class="step-card">
                    <h3>4. Instant Results</h3>
                    <p>Access your reports online the moment they are verified by our pathologists.</p>
                </div>
            </div>
        </section>
    </main>

    <?php include_once __DIR__ . '/includes/footer.php'; ?>

</body>

</html>