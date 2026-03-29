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

    <!-- Health Packages Section -->
    <section id="packages" class="section-container">
        <h2>Our Test Packages</h2>
        <form action="booking-success.php" method="POST">
            <div class="package-list">
                <?php
                $packages = [
                    "Lipid Profile" => [
                        "description" => "Test for cholesterol levels, including total cholesterol, LDL, HDL, and triglycerides.",
                        "pricing" => 2200
                    ],

                    "Basic Diabetes Package" => [
                        "description" => "Includes blood sugar tests, HbA1C, and insulin resistance test to monitor diabetes.",
                        "pricing" => 2800
                    ],

                    "Hemoglobin A1C" => [
                        "description" => "Test for tracking blood sugar control over the last 2-3 months, useful for diabetes management.",
                        "pricing" => 1200
                    ],

                    "Complete Blood Count" => [
                        "description" => "A comprehensive test to evaluate overall health and detect a variety of disorders.",
                        "pricing" => 600
                    ],

                    "Thyroid Function Test" => [
                        "description" => "Tests to evaluate thyroid function, including TSH, T3, and T4 levels.",
                        "pricing" => 2000
                    ],

                    "Liver Function Test" => [
                        "description" => "Tests for liver enzymes, bilirubin levels, and proteins to assess liver health.",
                        "pricing" => 1800
                    ],

                    "Kidney Function Test" => [
                        "description" => "Tests including creatinine and urea to evaluate kidney health.",
                        "pricing" => 1500
                    ],

                    "Vitamin D Test" => [
                        "description" => "Test to measure vitamin D levels and ensure proper bone health.",
                        "pricing" => 2500
                    ],

                    "Iron Studies" => [
                        "description" => "Tests to check iron levels, including serum iron, ferritin, and TIBC.",
                        "pricing" => 2200
                    ],

                    "Allergy Panel" => [
                        "description" => "A comprehensive test to identify common allergens affecting the body.",
                        "pricing" => 3500
                    ],

                    "Cardiac Risk Markers" => [
                        "description" => "Tests to evaluate risk factors for cardiovascular diseases, including hs-CRP and homocysteine.",
                        "pricing" => 3000
                    ],

                    "Hormonal Profile" => [
                        "description" => "Tests to evaluate key hormones like estrogen, testosterone, and cortisol.",
                        "pricing" => 4000
                    ]
                ];

                foreach ($packages as $name => $description) {
                    echo "
                    <div class='package-item'>
                        <h3>$name</h3>
                        <p>$description</p>
                        <input type='checkbox' name='packages[]' value='$name'> Select
                    </div>
                    ";
                }
                ?>
            </div>
            <button type="submit" class="btn">Book Selected Packages</button>
        </form>
    </section>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <?php require './src/db.php'; ?>

</body>

</html>