<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OM Diagnostic Lab - Health Packages</title>
    <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>
    <?php
    $currentPage = 'health-package';
    include_once __DIR__ . '/includes/header.php';
    ?>

    <!-- Health Packages Section -->
    <section id="packages" class="section-container">
        <h2>Our Test Packages</h2>
        <form action="booking-form.php" method="GET">
            <div class="package-list">
                <?php
                $packages = [
                    "Lipid Profile" => "Test for cholesterol levels, including total cholesterol, LDL, HDL, and triglycerides.",
                    "Basic Diabetes Package" => "Includes blood sugar tests, HbA1C, and insulin resistance test to monitor diabetes.",
                    "Hemoglobin A1C" => "Test for tracking blood sugar control over the last 2-3 months, useful for diabetes management.",
                    "Complete Blood Count" => "A comprehensive test to evaluate overall health and detect a variety of disorders.",
                    "Thyroid Function Test" => "Tests to evaluate thyroid function, including TSH, T3, and T4 levels.",
                    "Liver Function Test" => "Tests for liver enzymes, bilirubin levels, and proteins to assess liver health.",
                    "Kidney Function Test" => "Tests including creatinine and urea to evaluate kidney health.",
                    "Vitamin D Test" => "Test to measure vitamin D levels and ensure proper bone health.",
                    "Iron Studies" => "Tests to check iron levels, including serum iron, ferritin, and TIBC.",
                    "Allergy Panel" => "A comprehensive test to identify common allergens affecting the body.",
                    "Cardiac Risk Markers" => "Tests to evaluate risk factors for cardiovascular diseases, including hs-CRP and homocysteine.",
                    "Hormonal Profile" => "Tests to evaluate key hormones like estrogen, testosterone, and cortisol."
                ];

                foreach ($packages as $name => $description) {
                    echo "<div class='package-item'>
                            <input type='checkbox' name='packages[]' value='$name'>
                            <label><strong>$name</strong></label>
                            <p>$description</p>
                          </div>";
                }
                ?>
            </div>
            <button type="submit" class="btn">Proceed to Booking</button>
        </form>
    </section>

    <?php include_once __DIR__ . '/includes/footer.php'; ?>

    <?php require_once './src/db.php'; ?>
</body>

</html>