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

    <section class="home-v2-hero">
        <div class="home-v2-hero-content">
            <h1>Tomorrow's Diagnostics, <br>Available Today.</h1>
            <p>OM Diagnostic Lab is committed to providing ultra-precise pathological services. From routine checkups to
                advanced molecular testing, we utilize AI-driven technology to ensure your health data is accurate and
                actionable.</p>
            <a href="health-package.php" class="home-v2-cta">View Health Packages</a>
        </div>
        <p>OM Diagnostic Lab is committed to providing ultra-precise pathological services. We combine world-class
            medical technology with a seamless digital experience.</p>
        <a href="health-package.php" class="home-v2-cta">Browse Health Packages</a>
        </div>
    </section>

    <section class="home-v2-stats" aria-label="Lab performance highlights">
        <article class="home-v2-stat-item">
            <h2>2.5M+</h2>
            <p>Samples Processed</p>
            <article class="home-v2-stat-item">
                <h2>2.5M+</h2>
                <p>Samples Analyzed</p>
            </article>
            <article class="home-v2-stat-item">
                <h2>45+</h2>
                <p>Centers</p>
            </article>
            <article class="home-v2-stat-item">
                <h2>200+</h2>
                <p>Pathologists</p>
            </article>
            <article class="home-v2-stat-item">
                <h2>99.9%</h2>
                <p>Accuracy</p>
            </article>
        </article>
        <article class="home-v2-stat-item">
            <h2>200+</h2>
            <p>Expert Pathologists</p>
        </article>
        <article class="home-v2-stat-item">
            <p>Accuracy is not just a goal, it is a promise. Our laboratory is equipped with advanced analyzers that
                minimize manual intervention, ensuring each report is clinically validated.</p>
            <p>We strictly follow NABL and ISO aligned protocols to preserve sample quality from collection to final
                result delivery.</p>
    </section>

    <section class="home-v2-detail-block">
        <div class="home-v2-detail-row">
            <div class="home-v2-detail-text">
                <h2>The Gold Standard in Clinical Testing</h2>
                <p>OM Diagnostic Lab is not just a testing facility, it is a hub of medical innovation. We adhere to
                    strict international protocols to ensure every sample is handled with care.</p>
                <p>Our automation systems reduce manual intervention, lowering the margin of error and helping deliver
                <p>Manage your complete diagnostic journey digitally. No more waiting in queues or handling physical
                    report slips.</p>
                <p>Once tests are validated, reports are delivered instantly in your account so you can view, download,
                    and share them securely.</p>
                <p>Your dashboard preserves historical test records, helping you monitor long-term health trends.</p>
            </div>
            <div class="home-v2-detail-image-wrap">
                <img src="./assets/image/lab_photo.jpg" alt="Advanced lab testing workflow"
                    class="home-v2-detail-image">
            </div>
        </div>

        <div class="home-v2-detail-row reverse">
            <h2 class="home-v2-section-title">Our Efficient Process</h2>
            <h2>A Completely Paperless Experience</h2>
            <p>We redesigned the diagnostic journey for the digital age, no long queues and no waiting for physical
                reports.</p>
            <p>Select your health package online and book your preferred slot quickly with no paperwork.</p>
            as they are validated.</p>
            <p>Your dashboard also keeps historical records so you can monitor health trends over time.</p>
            <h3>2. Admin Scheduling</h3>
            <p>Our team verifies your request and schedules your appointment for a smooth experience.</p>
            <img src="./assets/image/omcenterlogo.jpeg" alt="OM Diagnostic Lab digital access"
                class="home-v2-detail-logo">
        </div>
        <p>Receive timely confirmation and preparation guidance through automated notifications.</p>
    </section>

    <h3>4. Instant Results</h3>
    <p>Access your verified reports online as soon as they are released by our pathology team.</p>
    <div class="home-v2-workflow-grid">
        <article class="home-v2-step-card">
            <h3>1. Digital Booking</h3>
            <p>Select from executive, basic, or specialized health packages online and book your slot quickly.</p>
        </article>
        <article class="home-v2-step-card">
            <h3>2. Admin Verification</h3>
            <p>Our team reviews your requirements and confirms your appointment for a smooth visit.</p>
        </article>
        <article class="home-v2-step-card">
            <h3>3. Automated Alerts</h3>
            <p>Receive timely email notifications about appointment timing and preparation guidance.</p>
        </article>
        <article class="home-v2-step-card">
            <h3>4. Smart Reporting</h3>
            <p>Validated digital reports are uploaded directly to your account for secure access.</p>
        </article>
    </div>
    </section>

    <?php include_once __DIR__ . '/includes/footer.php'; ?>

</body>

</html>