

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
        $currentPage = 'health-package';
        include __DIR__ . '/includes/header.php';
    ?>

     <div id="booking-form" class="section-container">
        <h2>Booking Appointment</h2>
        <form  action="booking-success.php" method="POST" enctype="multipart/form-data">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
            <label for="phone">Phone:</label>
            <input type="tel" id="phone" name="phone" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="package-name">Health Package:</label>
            <input type="text" id="package" name="package" value="<?php if(isset($_GET['package'])) echo $_GET['package'] ;?>" readonly />
            <button type="submit">Book Now</button>                                                                       
        </form>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
    
    </body>
</html>



 
