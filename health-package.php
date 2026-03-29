<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include_once __DIR__ . '/src/db.php';
include_once __DIR__ . '/src/constants/table.php';
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
    $currentPage = 'health-package';
    include_once __DIR__ . '/includes/header.php';
    ?>

    <!-- Health Packages Section -->
    <section id="packages" class="section-container">
        <h2>Our Test Packages</h2>
        <form action="booking-success.php" method="POST">
            <div class="package-controls">
                <input type="search" id="packageSearch" placeholder="Search by test name or description"
                    aria-label="Search packages">
                <select id="packageCategory" aria-label="Filter by category">
                    <option value="all">All Categories</option>
                </select>
                <select id="packageSort" aria-label="Sort packages">
                    <option value="name-asc">Name: A to Z</option>
                    <option value="name-desc">Name: Z to A</option>
                    <option value="price-asc">Price: Low to High</option>
                    <option value="price-desc">Price: High to Low</option>
                    <option value="popularity-desc">Popularity</option>
                </select>
                <button type="button" id="packageReset" class="btn">Reset</button>
            </div>
            <p id="packageResultCount" aria-live="polite"></p>


            <div class="recommended-section">
                <h3>Recommended For You</h3>
                <div id="recommendedPackages" class="package-list"></div>
            </div>
            <div class="package-list">
                <?php
                $packages = [

                    ["id" => 2, "name" => "Fasting Blood Sugar", "description" => "Measures blood glucose levels after fasting for 8-10 hours. Helps in early detection of diabetes and monitoring long-term sugar control.", "pricing" => 200, "category" => "Diabetes", "tags" => ["sugar", "diabetes"], "related_packages" => [3], "popularity" => 85],

                    ["id" => 3, "name" => "Postprandial Blood Sugar", "description" => "Checks blood sugar levels after meals. Useful for understanding how the body processes glucose and managing diabetes effectively.", "pricing" => 250, "category" => "Diabetes", "tags" => ["sugar"], "related_packages" => [2], "popularity" => 75],

                    ["id" => 4, "name" => "Random Blood Sugar", "description" => "Measures blood sugar at any time of the day. Helps in quick screening and identifying abnormal glucose levels.", "pricing" => 150, "category" => "Diabetes", "tags" => ["sugar"], "related_packages" => [2, 3], "popularity" => 70],

                    ["id" => 5, "name" => "HbA1C Test", "description" => "Shows average blood sugar levels over the past 2-3 months. Helps in diagnosing and monitoring diabetes control.", "pricing" => 1200, "category" => "Diabetes", "tags" => ["diabetes"], "related_packages" => [2, 3], "popularity" => 90],

                    ["id" => 6, "name" => "Insulin Test", "description" => "Measures insulin hormone levels in blood. Helps detect insulin resistance and diabetes risk.", "pricing" => 2000, "category" => "Diabetes", "tags" => ["insulin"], "related_packages" => [5], "popularity" => 80],

                    ["id" => 7, "name" => "Thyroid Profile", "description" => "Includes T3, T4, and TSH tests to evaluate thyroid function. Helps detect hormonal imbalance and metabolism issues.", "pricing" => 1500, "category" => "Thyroid", "tags" => ["thyroid"], "related_packages" => [8, 9], "popularity" => 90],

                    ["id" => 8, "name" => "TSH Test", "description" => "Measures thyroid stimulating hormone. Helps diagnose thyroid disorders and monitor treatment.", "pricing" => 500, "category" => "Thyroid", "tags" => ["thyroid"], "related_packages" => [7], "popularity" => 80],

                    ["id" => 9, "name" => "Free T3 Test", "description" => "Measures active thyroid hormone T3. Helps assess metabolism and thyroid health.", "pricing" => 600, "category" => "Thyroid", "tags" => ["thyroid"], "related_packages" => [7], "popularity" => 70],

                    ["id" => 10, "name" => "Free T4 Test", "description" => "Measures thyroxine hormone. Helps detect thyroid imbalance and metabolic conditions.", "pricing" => 600, "category" => "Thyroid", "tags" => ["thyroid"], "related_packages" => [7], "popularity" => 70],

                    ["id" => 11, "name" => "Complete Blood Count", "description" => "Analyzes blood components like RBC, WBC, and platelets. Helps detect infections, anemia, and overall health condition.", "pricing" => 500, "category" => "General", "tags" => ["blood"], "related_packages" => [12], "popularity" => 95],

                    ["id" => 12, "name" => "ESR Test", "description" => "Measures inflammation in the body. Helps detect infections and chronic diseases.", "pricing" => 200, "category" => "General", "tags" => ["inflammation"], "related_packages" => [11], "popularity" => 70],

                    ["id" => 13, "name" => "CRP Test", "description" => "Detects inflammation and infection levels. Helps monitor immune response and disease activity.", "pricing" => 600, "category" => "General", "tags" => ["inflammation"], "related_packages" => [12], "popularity" => 80],

                    ["id" => 14, "name" => "Liver Function Test", "description" => "Evaluates liver enzymes and proteins. Helps detect liver diseases and monitor liver health.", "pricing" => 1800, "category" => "Liver", "tags" => ["liver"], "related_packages" => [15], "popularity" => 85],

                    ["id" => 15, "name" => "SGPT (ALT)", "description" => "Measures liver enzyme ALT. Helps detect liver damage and monitor liver conditions.", "pricing" => 400, "category" => "Liver", "tags" => ["liver"], "related_packages" => [14], "popularity" => 70],

                    ["id" => 16, "name" => "Kidney Function Test", "description" => "Evaluates kidney health using creatinine and urea levels. Helps detect kidney disease early.", "pricing" => 1500, "category" => "Kidney", "tags" => ["kidney"], "related_packages" => [17], "popularity" => 85],

                    ["id" => 17, "name" => "Creatinine Test", "description" => "Measures kidney filtration efficiency. Helps monitor kidney function.", "pricing" => 400, "category" => "Kidney", "tags" => ["kidney"], "related_packages" => [16], "popularity" => 75],

                    ["id" => 18, "name" => "Urea Test", "description" => "Measures waste products in blood. Helps assess kidney function and hydration status.", "pricing" => 350, "category" => "Kidney", "tags" => ["kidney"], "related_packages" => [16], "popularity" => 70],

                    ["id" => 19, "name" => "Vitamin D Test", "description" => "Measures vitamin D levels. Helps improve bone strength and immunity.", "pricing" => 2500, "category" => "Vitamin", "tags" => ["vitamin"], "related_packages" => [20], "popularity" => 85],

                    ["id" => 20, "name" => "Vitamin B12 Test", "description" => "Measures vitamin B12 levels. Helps prevent anemia and nerve damage.", "pricing" => 1800, "category" => "Vitamin", "tags" => ["vitamin"], "related_packages" => [19], "popularity" => 80],

                    ["id" => 21, "name" => "Iron Test", "description" => "Measures iron levels in blood. Helps diagnose anemia and fatigue causes.", "pricing" => 700, "category" => "Iron", "tags" => ["iron"], "related_packages" => [22], "popularity" => 75],

                    ["id" => 22, "name" => "Ferritin Test", "description" => "Measures stored iron in body. Helps detect iron deficiency or overload.", "pricing" => 1200, "category" => "Iron", "tags" => ["iron"], "related_packages" => [21], "popularity" => 78],

                    ["id" => 23, "name" => "TIBC Test", "description" => "Measures iron binding capacity. Helps evaluate iron metabolism.", "pricing" => 900, "category" => "Iron", "tags" => ["iron"], "related_packages" => [21], "popularity" => 70],

                    ["id" => 24, "name" => "Calcium Test", "description" => "Measures calcium levels. Important for bone health and muscle function.", "pricing" => 500, "category" => "General", "tags" => ["calcium"], "related_packages" => [], "popularity" => 70],

                    ["id" => 25, "name" => "Magnesium Test", "description" => "Measures magnesium levels. Helps maintain nerve and muscle health.", "pricing" => 900, "category" => "General", "tags" => ["mineral"], "related_packages" => [], "popularity" => 65],

                    ["id" => 26, "name" => "Phosphorus Test", "description" => "Measures phosphorus levels. Supports bone and kidney health.", "pricing" => 500, "category" => "General", "tags" => ["mineral"], "related_packages" => [], "popularity" => 60],

                    ["id" => 27, "name" => "Electrolyte Panel", "description" => "Measures sodium, potassium, chloride levels. Helps maintain fluid balance and nerve function.", "pricing" => 800, "category" => "General", "tags" => ["electrolyte"], "related_packages" => [], "popularity" => 80],

                    ["id" => 28, "name" => "Urine Routine Test", "description" => "Analyzes urine composition. Helps detect infections and kidney disorders.", "pricing" => 300, "category" => "General", "tags" => ["urine"], "related_packages" => [], "popularity" => 90],

                    ["id" => 29, "name" => "Amylase Test", "description" => "Measures pancreatic enzyme. Helps detect pancreatic disorders.", "pricing" => 800, "category" => "Pancreas", "tags" => ["pancreas"], "related_packages" => [30], "popularity" => 65],

                    ["id" => 30, "name" => "Lipase Test", "description" => "Measures lipase enzyme. Helps diagnose pancreatitis.", "pricing" => 1200, "category" => "Pancreas", "tags" => ["pancreas"], "related_packages" => [29], "popularity" => 65],

                    ["id" => 31, "name" => "Dengue NS1 Antigen Test", "description" => "Detects dengue virus in early stage. Helps in quick diagnosis and timely treatment during dengue outbreaks.", "pricing" => 1500, "category" => "Infection", "tags" => ["dengue"], "related_packages" => [32], "popularity" => 85],

                    ["id" => 32, "name" => "Dengue IgG/IgM Test", "description" => "Detects dengue antibodies. Useful for confirming dengue infection and monitoring recovery stage.", "pricing" => 1800, "category" => "Infection", "tags" => ["dengue"], "related_packages" => [31], "popularity" => 80],

                    ["id" => 33, "name" => "Malaria Test", "description" => "Detects malaria parasites in blood. Helps in early diagnosis and prevents complications from infection.", "pricing" => 900, "category" => "Infection", "tags" => ["malaria"], "related_packages" => [], "popularity" => 70],

                    ["id" => 34, "name" => "Typhoid Test (Widal)", "description" => "Detects typhoid infection. Helps diagnose fever causes and start proper treatment early.", "pricing" => 800, "category" => "Infection", "tags" => ["typhoid"], "related_packages" => [], "popularity" => 75],

                    ["id" => 35, "name" => "Hepatitis B Surface Antigen", "description" => "Detects Hepatitis B infection. Helps in early diagnosis and prevention of liver complications.", "pricing" => 600, "category" => "Infection", "tags" => ["hepatitis"], "related_packages" => [36], "popularity" => 85],

                    ["id" => 36, "name" => "Hepatitis C Antibody", "description" => "Detects Hepatitis C infection. Helps in early treatment and liver disease prevention.", "pricing" => 1200, "category" => "Infection", "tags" => ["hepatitis"], "related_packages" => [35], "popularity" => 75],

                    ["id" => 37, "name" => "HIV 1 & 2 Test", "description" => "Screens for HIV infection. Early detection helps in better management and prevention.", "pricing" => 1000, "category" => "Infection", "tags" => ["hiv"], "related_packages" => [], "popularity" => 85],

                    ["id" => 38, "name" => "VDRL Test", "description" => "Screens for syphilis infection. Helps in early detection and treatment of sexually transmitted infections.", "pricing" => 500, "category" => "Infection", "tags" => ["std"], "related_packages" => [], "popularity" => 70],

                    ["id" => 39, "name" => "Prolactin Test", "description" => "Measures prolactin hormone levels. Helps diagnose hormonal imbalance and fertility issues.", "pricing" => 1500, "category" => "Hormone", "tags" => ["hormone"], "related_packages" => [40], "popularity" => 70],

                    ["id" => 40, "name" => "Testosterone Test", "description" => "Measures testosterone levels. Helps assess male health, fertility, and hormonal balance.", "pricing" => 2200, "category" => "Hormone", "tags" => ["hormone"], "related_packages" => [39], "popularity" => 75],

                    ["id" => 41, "name" => "Estrogen Test", "description" => "Measures estrogen hormone. Helps evaluate reproductive health and hormonal balance in females.", "pricing" => 2000, "category" => "Hormone", "tags" => ["hormone"], "related_packages" => [], "popularity" => 70],

                    ["id" => 42, "name" => "Cortisol Test", "description" => "Measures stress hormone cortisol. Helps diagnose stress disorders and adrenal gland issues.", "pricing" => 1800, "category" => "Hormone", "tags" => ["cortisol"], "related_packages" => [], "popularity" => 75],

                    ["id" => 43, "name" => "Progesterone Test", "description" => "Measures progesterone hormone. Important for pregnancy monitoring and reproductive health.", "pricing" => 2000, "category" => "Hormone", "tags" => ["hormone"], "related_packages" => [], "popularity" => 70],

                    ["id" => 44, "name" => "FSH Test", "description" => "Measures follicle-stimulating hormone. Helps assess fertility and reproductive system health.", "pricing" => 1800, "category" => "Hormone", "tags" => ["fertility"], "related_packages" => [45], "popularity" => 70],

                    ["id" => 45, "name" => "LH Test", "description" => "Measures luteinizing hormone. Helps diagnose ovulation and fertility issues.", "pricing" => 1800, "category" => "Hormone", "tags" => ["fertility"], "related_packages" => [44], "popularity" => 70],

                    ["id" => 46, "name" => "Uric Acid Test", "description" => "Measures uric acid levels. Helps diagnose gout and joint-related issues.", "pricing" => 400, "category" => "General", "tags" => ["uric"], "related_packages" => [], "popularity" => 80],

                    ["id" => 47, "name" => "Rheumatoid Factor Test", "description" => "Detects rheumatoid arthritis. Helps diagnose joint inflammation and autoimmune disorders.", "pricing" => 800, "category" => "General", "tags" => ["arthritis"], "related_packages" => [], "popularity" => 75],

                    ["id" => 48, "name" => "Anti-CCP Test", "description" => "Confirms rheumatoid arthritis. Helps in early diagnosis and treatment planning.", "pricing" => 2500, "category" => "General", "tags" => ["arthritis"], "related_packages" => [47], "popularity" => 70],

                    ["id" => 49, "name" => "D-Dimer Test", "description" => "Measures clot formation. Helps detect blood clot disorders and deep vein thrombosis.", "pricing" => 2500, "category" => "Cardio", "tags" => ["clot"], "related_packages" => [], "popularity" => 75],

                    ["id" => 50, "name" => "Troponin I Test", "description" => "Detects heart muscle damage. Helps diagnose heart attack quickly.", "pricing" => 2000, "category" => "Cardio", "tags" => ["heart"], "related_packages" => [49], "popularity" => 85],

                    ["id" => 51, "name" => "CPK-MB Test", "description" => "Measures cardiac enzyme. Helps assess heart muscle injury.", "pricing" => 1800, "category" => "Cardio", "tags" => ["heart"], "related_packages" => [50], "popularity" => 75],

                    ["id" => 52, "name" => "Blood Group Test", "description" => "Determines blood type. Important for transfusions and medical emergencies.", "pricing" => 300, "category" => "General", "tags" => ["blood"], "related_packages" => [], "popularity" => 90],

                    ["id" => 53, "name" => "Coagulation Profile", "description" => "Measures blood clotting ability. Helps detect bleeding disorders and monitor therapy.", "pricing" => 2000, "category" => "General", "tags" => ["clot"], "related_packages" => [], "popularity" => 75],

                    ["id" => 54, "name" => "Prothrombin Time (PT)", "description" => "Measures blood clotting time. Helps assess bleeding risk and liver function.", "pricing" => 700, "category" => "General", "tags" => ["clot"], "related_packages" => [53], "popularity" => 70],

                    ["id" => 55, "name" => "INR Test", "description" => "Standardized clotting test. Helps monitor blood thinning medications.", "pricing" => 700, "category" => "General", "tags" => ["clot"], "related_packages" => [54], "popularity" => 75],

                    ["id" => 56, "name" => "Stool Routine Test", "description" => "Analyzes stool sample. Helps detect digestive issues, infections, and parasites.", "pricing" => 300, "category" => "General", "tags" => ["stool"], "related_packages" => [], "popularity" => 80],

                    ["id" => 57, "name" => "Occult Blood Test", "description" => "Detects hidden blood in stool. Helps screen for gastrointestinal bleeding.", "pricing" => 500, "category" => "General", "tags" => ["stool"], "related_packages" => [56], "popularity" => 70],

                    ["id" => 58, "name" => "PSA Test", "description" => "Measures prostate-specific antigen. Helps detect prostate issues in men.", "pricing" => 1500, "category" => "Cancer", "tags" => ["prostate"], "related_packages" => [], "popularity" => 75],

                    ["id" => 59, "name" => "CA-125 Test", "description" => "Measures tumor marker CA-125. Helps detect ovarian cancer risk.", "pricing" => 2500, "category" => "Cancer", "tags" => ["cancer"], "related_packages" => [], "popularity" => 70],

                    ["id" => 60, "name" => "CEA Test", "description" => "Measures carcinoembryonic antigen. Helps monitor certain cancers and treatment progress.", "pricing" => 2200, "category" => "Cancer", "tags" => ["cancer"], "related_packages" => [], "popularity" => 70]

                ];

                // Build mapping of package names to IDs for quick lookup
                $packageNameToId = [];
                foreach ($packages as $package) {
                    $packageNameToId[$package['name']] = $package['id'];
                }

                // Fetch user's booking history from database
                $bookingHistory = [];
                if (isset($_SESSION['email']) && !empty($_SESSION['email'])) {
                    $userEmail = $_SESSION['email'];

                    // Query appointments for this user
                    $query = "SELECT package FROM " . $table['APPOINTMENT'] . " WHERE email = ?";
                    $stmt = $conn->prepare($query);

                    if ($stmt) {
                        $stmt->bind_param("s", $userEmail);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result && $result->num_rows > 0) {
                            // Process each appointment
                            while ($row = $result->fetch_assoc()) {
                                // Split package CSV and map each to ID
                                $packageNames = array_map('trim', explode(',', $row['package']));
                                foreach ($packageNames as $packageName) {
                                    if (!empty($packageName) && isset($packageNameToId[$packageName])) {
                                        $bookingHistory[] = [
                                            'packageId' => (int) $packageNameToId[$packageName],
                                            'packageName' => $packageName
                                        ];
                                    }
                                }
                            }
                        }

                        $stmt->close();
                    }
                }

                foreach ($packages as $index => $package):
                    $id = (int) ($package['id'] ?? 0);
                    $name = htmlspecialchars((string) ($package['name'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $description = htmlspecialchars((string) ($package['description'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $category = htmlspecialchars(strtolower((string) ($package['category'] ?? '')), ENT_QUOTES, 'UTF-8');
                    $price = (int) ($package['pricing'] ?? 0);
                    $popularity = (int) ($package['popularity'] ?? 0);
                    $related = array_map('intval', (array) ($package['related_packages'] ?? []));
                    $relatedCsv = htmlspecialchars(implode(',', $related), ENT_QUOTES, 'UTF-8');
                    ?>
                    <div class="package-item" data-id="<?php echo $id; ?>" data-index="<?php echo (int) $index; ?>"
                        data-name="<?php echo strtolower($name); ?>"
                        data-description="<?php echo strtolower($description); ?>" data-category="<?php echo $category; ?>"
                        data-price="<?php echo $price; ?>" data-popularity="<?php echo $popularity; ?>"
                        data-related-packages="<?php echo $relatedCsv; ?>">
                        <h3><?php echo $name; ?></h3>
                        <p><?php echo $description; ?></p>
                        <p><strong>Price:</strong> Rs. <?php echo $price; ?></p>
                        <label>
                            <input type="checkbox" name="packages[]" value="<?php echo $name; ?>"> Select
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="booking-quickbar" id="bookingQuickBar">
                <div class="booking-quickbar-meta">
                    <strong id="selectedPackageCount">0 package selected</strong>
                    <span id="selectedPackageTotal">Total: Rs. 0</span>
                </div>
                <button type="submit" class="btn" id="quickBookButton" disabled>Book Selected Packages</button>
            </div>

            <div class="booking-form-submit">
                <button type="submit" class="btn" id="mainBookButton" disabled>Book Selected Packages</button>
            </div>
        </form>


    </section>

    <?php include_once __DIR__ . '/includes/footer.php'; ?>

    <script>
        (function () {
            const packageList = document.querySelector('.package-list');
            const recommendedContainer = document.getElementById('recommendedPackages');
            const searchInput = document.getElementById('packageSearch');
            const categorySelect = document.getElementById('packageCategory');
            const sortSelect = document.getElementById('packageSort');
            const resetButton = document.getElementById('packageReset');
            const resultCount = document.getElementById('packageResultCount');
            const selectedPackageCount = document.getElementById('selectedPackageCount');
            const selectedPackageTotal = document.getElementById('selectedPackageTotal');
            const quickBookButton = document.getElementById('quickBookButton');
            const mainBookButton = document.getElementById('mainBookButton');
            const bookingHistory = <?php echo json_encode($bookingHistory, JSON_UNESCAPED_UNICODE); ?>;

            if (!packageList || !recommendedContainer) {
                return;
            }

            const cards = Array.from(packageList.querySelectorAll('.package-item'));
            const noData = document.createElement('div');
            noData.className = 'no-package-match';
            noData.textContent = 'No packages match your filters.';

            function populateCategories() {
                if (!categorySelect) {
                    return;
                }

                const existingValues = new Set(
                    Array.from(categorySelect.options).map((option) => option.value)
                );

                const categories = new Set(
                    cards.map((card) => card.dataset.category || '').filter(Boolean)
                );

                Array.from(categories)
                    .sort((a, b) => a.localeCompare(b))
                    .forEach((categoryValue) => {
                        if (existingValues.has(categoryValue)) {
                            return;
                        }

                        const option = document.createElement('option');
                        option.value = categoryValue;
                        option.textContent = categoryValue.charAt(0).toUpperCase() + categoryValue.slice(1);
                        categorySelect.appendChild(option);
                    });
            }

            function applyFilters() {
                if (!searchInput || !categorySelect || !sortSelect) {
                    return;
                }

                const term = searchInput.value.trim().toLowerCase();
                const category = categorySelect.value;
                const sort = sortSelect.value;

                const filtered = cards.filter((card) => {
                    const name = card.dataset.name || '';
                    const description = card.dataset.description || '';
                    const cardCategory = card.dataset.category || '';

                    const matchesSearch = !term || name.includes(term) || description.includes(term);
                    const matchesCategory = category === 'all' || cardCategory === category;

                    return matchesSearch && matchesCategory;
                });

                filtered.sort((a, b) => {
                    const nameA = (a.dataset.name || '').toLowerCase();
                    const nameB = (b.dataset.name || '').toLowerCase();
                    const priceA = parseInt(a.dataset.price || '0', 10);
                    const priceB = parseInt(b.dataset.price || '0', 10);
                    const popularityA = parseInt(a.dataset.popularity || '0', 10);
                    const popularityB = parseInt(b.dataset.popularity || '0', 10);
                    const indexA = parseInt(a.dataset.index || '0', 10);
                    const indexB = parseInt(b.dataset.index || '0', 10);

                    if (sort === 'name-desc') {
                        return nameB.localeCompare(nameA);
                    }
                    if (sort === 'price-asc') {
                        return priceA - priceB;
                    }
                    if (sort === 'price-desc') {
                        return priceB - priceA;
                    }
                    if (sort === 'popularity-desc') {
                        return popularityB - popularityA;
                    }

                    // Default sort keeps the seeded order for predictable UI.
                    if (sort === 'name-asc') {
                        return nameA.localeCompare(nameB);
                    }
                    return indexA - indexB;
                });

                cards.forEach((card) => {
                    card.style.display = 'none';
                });

                filtered.forEach((card) => {
                    packageList.appendChild(card);
                    card.style.display = '';
                });

                const hasMatch = filtered.length > 0;
                if (!hasMatch && !packageList.contains(noData)) {
                    packageList.appendChild(noData);
                }
                if (hasMatch && packageList.contains(noData)) {
                    noData.remove();
                }

                if (resultCount) {
                    resultCount.textContent = `${filtered.length} package(s) shown`;
                }
            }

            function updateBookingSummary() {
                const selected = cards.filter((card) => {
                    const checkbox = card.querySelector('input[type="checkbox"]');
                    return checkbox ? checkbox.checked : false;
                });

                const count = selected.length;
                const total = selected.reduce((sum, card) => {
                    return sum + parseInt(card.dataset.price || '0', 10);
                }, 0);

                if (selectedPackageCount) {
                    selectedPackageCount.textContent = `${count} package${count === 1 ? '' : 's'} selected`;
                }

                if (selectedPackageTotal) {
                    selectedPackageTotal.textContent = `Total: Rs. ${total}`;
                }

                if (quickBookButton) {
                    quickBookButton.disabled = count === 0;
                }

                if (mainBookButton) {
                    mainBookButton.disabled = count === 0;
                }
            }

            function parsePackagesFromDom() {
                const cards = Array.from(packageList.querySelectorAll('.package-item'));

                return cards.map((card) => {
                    const relatedPackages = (card.dataset.relatedPackages || '')
                        .split(',')
                        .map((value) => parseInt(value, 10))
                        .filter((value) => Number.isInteger(value) && value > 0);

                    return {
                        id: parseInt(card.dataset.id || '0', 10),
                        name: card.querySelector('h3')?.textContent?.trim() || '',
                        description: card.querySelector('p')?.textContent?.trim() || '',
                        category: card.dataset.category || '',
                        pricing: parseInt(card.dataset.price || '0', 10),
                        relatedPackages,
                        popularity: parseInt(card.dataset.popularity || '0', 10)
                    };
                }).filter((pkg) => Number.isInteger(pkg.id) && pkg.id > 0);
            }

            function getRecommendedPackages(packages, history, limit = 5) {
                const bookedIds = new Set(history.map((item) => item.packageId));
                const bookedPackages = packages.filter((pkg) => bookedIds.has(pkg.id));

                if (!history.length || !bookedPackages.length) {
                    return [...packages]
                        .sort((a, b) => (b.popularity || 0) - (a.popularity || 0))
                        .slice(0, limit)
                        .map((pkg) => ({
                            ...pkg,
                            score: pkg.popularity || 0,
                            reasons: ['Popular package']
                        }));
                }

                const candidateMap = new Map();

                function addCandidate(pkg, points, reason, sourceBookedId) {
                    if (!pkg || bookedIds.has(pkg.id)) {
                        return;
                    }

                    if (!candidateMap.has(pkg.id)) {
                        candidateMap.set(pkg.id, {
                            ...pkg,
                            score: 0,
                            reasons: [],
                            sourceBookedIds: new Set()
                        });
                    }

                    const existing = candidateMap.get(pkg.id);
                    existing.score += points;
                    if (reason && !existing.reasons.includes(reason)) {
                        existing.reasons.push(reason);
                    }

                    if (Number.isInteger(sourceBookedId)) {
                        const before = existing.sourceBookedIds.size;
                        existing.sourceBookedIds.add(sourceBookedId);
                        if (existing.sourceBookedIds.size > before) {
                            existing.score += 15;
                        }
                    }
                }

                // 1) Related package candidates
                bookedPackages.forEach((bookedPkg) => {
                    (bookedPkg.relatedPackages || []).forEach((relatedId) => {
                        const relatedPkg = packages.find((pkg) => pkg.id === relatedId);
                        if (relatedPkg) {
                            addCandidate(
                                relatedPkg,
                                50,
                                `Related to ${bookedPkg.name}`,
                                bookedPkg.id
                            );
                        }
                    });
                });

                // 2) Same category fallback
                if (candidateMap.size < limit) {
                    bookedPackages.forEach((bookedPkg) => {
                        packages.forEach((pkg) => {
                            if (pkg.category === bookedPkg.category && !bookedIds.has(pkg.id)) {
                                addCandidate(
                                    pkg,
                                    20,
                                    `Same category as ${bookedPkg.name}`,
                                    bookedPkg.id
                                );
                            }
                        });
                    });
                }

                // 3) Popularity bonus
                candidateMap.forEach((candidate) => {
                    candidate.score += candidate.popularity || 0;
                });

                // 4) Sort and limit
                return Array.from(candidateMap.values())
                    .sort((a, b) => b.score - a.score)
                    .slice(0, limit)
                    .map((candidate) => ({
                        ...candidate,
                        sourceBookedIds: undefined
                    }));
            }

            function renderRecommendations(recommendations) {
                recommendedContainer.innerHTML = '';

                if (!recommendations.length) {
                    const emptyState = document.createElement('p');
                    emptyState.textContent = 'No recommendations available right now.';
                    recommendedContainer.appendChild(emptyState);
                    return;
                }

                recommendations.forEach((pkg) => {
                    const card = document.createElement('div');
                    card.className = 'package-item';

                    const reasons = (pkg.reasons || []).slice(0, 2).join(' | ');

                    card.innerHTML = `
                        <h3>${pkg.name}</h3>
                        <p>${pkg.description}</p>
                        <p><strong>Price:</strong> Rs. ${pkg.pricing || 0}</p>
                        <p><strong>Why recommended:</strong> ${reasons || 'Relevant to your history'}</p>
                    `;

                    recommendedContainer.appendChild(card);
                });
            }

            const packages = parsePackagesFromDom();
            const recommendations = getRecommendedPackages(packages, bookingHistory, 5);
            renderRecommendations(recommendations);
            populateCategories();

            if (searchInput && categorySelect && sortSelect) {
                searchInput.addEventListener('input', applyFilters);
                categorySelect.addEventListener('change', applyFilters);
                sortSelect.addEventListener('change', applyFilters);

                if (resetButton) {
                    resetButton.addEventListener('click', () => {
                        searchInput.value = '';
                        categorySelect.value = 'all';
                        sortSelect.value = 'name-asc';
                        applyFilters();
                    });
                }

                applyFilters();
            }

            cards.forEach((card) => {
                const checkbox = card.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.addEventListener('change', updateBookingSummary);
                }
            });

            updateBookingSummary();
        })();
    </script>
</body>

</html>