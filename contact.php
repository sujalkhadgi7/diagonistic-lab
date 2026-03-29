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
    $currentPage = 'contact';
    include_once __DIR__ . '/includes/header.php';
    ?>

    <section id="contact" class="section-container">
        <div class="contact-hero">
            <div>
                <p class="contact-kicker">Need Help Fast?</p>
                <h2>Contact OM Diagnostic Lab</h2>
                <p>Our team is available for appointment support, report guidance, and package-related questions.</p>
            </div>

            <div class="contact-actions" aria-label="Quick contact actions">
                <a class="btn" href="tel:+9779865321202">Call Now</a>
                <a class="btn btn-outline" href="mailto:info@omdiagnosticlab.com">Email Us</a>
            </div>
        </div>

        <div class="contact-grid">
            <article class="contact-card">
                <h3>Phone Numbers</h3>
                <p><a href="tel:+9771546585">01-546585</a></p>
                <p><a href="tel:+9779865321202">977-9865321202</a></p>
            </article>

            <article class="contact-card">
                <h3>Visit Us</h3>
                <p>Sanogaun, Lalitpur</p>
                <p>Nepal</p>
            </article>

            <article class="contact-card">

                <h3>Working Hours</h3>
                <p>Sun - Fri: 6:30 AM - 7:00 PM</p>
                <p>Saturday: 7:00 AM - 2:00 PM</p>
            </article>
        </div>

        <div class="contact-map" aria-label="Map location for OM Diagnostic Lab">
            <iframe title="OM Diagnostic Lab Location Map"
                src="https://maps.google.com/maps?q=Sanogaun%20Lalitpur&t=&z=13&ie=UTF8&iwloc=&output=embed"
                loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>

        <div class="contact-note" role="note">
            <strong>Tip:</strong> For faster service, keep your patient ID ready when calling.
        </div>
    </section>

    <?php include_once __DIR__ . '/includes/footer.php'; ?>

</body>

</html>
