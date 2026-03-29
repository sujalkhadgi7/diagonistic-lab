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

        <?php
        $packages = [
            "Lipid Profile" => [
                "description" => "Test for cholesterol levels, including total cholesterol, LDL, HDL, and triglycerides.",
                "pricing" => 2200,
                "category" => "Cardio"
            ],
            "Basic Diabetes Package" => [
                "description" => "Includes blood sugar tests, HbA1C, and insulin resistance test to monitor diabetes.",
                "pricing" => 2800,
                "category" => "Diabetes"
            ],
            "Hemoglobin A1C" => [
                "description" => "Test for tracking blood sugar control over the last 2-3 months, useful for diabetes management.",
                "pricing" => 1200,
                "category" => "Diabetes"
            ],
            "Complete Blood Count" => [
                "description" => "A comprehensive test to evaluate overall health and detect a variety of disorders.",
                "pricing" => 600,
                "category" => "Blood"
            ],
            "Thyroid Function Test" => [
                "description" => "Tests to evaluate thyroid function, including TSH, T3, and T4 levels.",
                "pricing" => 2000,
                "category" => "Hormone"
            ],
            "Liver Function Test" => [
                "description" => "Tests for liver enzymes, bilirubin levels, and proteins to assess liver health.",
                "pricing" => 1800,
                "category" => "Organ"
            ],
            "Kidney Function Test" => [
                "description" => "Tests including creatinine and urea to evaluate kidney health.",
                "pricing" => 1500,
                "category" => "Organ"
            ],
            "Vitamin D Test" => [
                "description" => "Test to measure vitamin D levels and ensure proper bone health.",
                "pricing" => 2500,
                "category" => "Nutrition"
            ],
            "Iron Studies" => [
                "description" => "Tests to check iron levels, including serum iron, ferritin, and TIBC.",
                "pricing" => 2200,
                "category" => "Nutrition"
            ],
            "Allergy Panel" => [
                "description" => "A comprehensive test to identify common allergens affecting the body.",
                "pricing" => 3500,
                "category" => "Allergy"
            ],
            "Cardiac Risk Markers" => [
                "description" => "Tests to evaluate risk factors for cardiovascular diseases, including hs-CRP and homocysteine.",
                "pricing" => 3000,
                "category" => "Cardio"
            ],
            "Hormonal Profile" => [
                "description" => "Tests to evaluate key hormones like estrogen, testosterone, and cortisol.",
                "pricing" => 4000,
                "category" => "Hormone"
            ]
        ];
        ?>

        <form action="booking-success.php" method="POST">
            <div class="package-toolbar">
                <div class="field">
                    <label for="packageSearch">Search package</label>
                    <input id="packageSearch" type="text" placeholder="Type name or keyword">
                </div>
                <div class="field">
                    <label for="packageCategory">Filter category</label>
                    <select id="packageCategory">
                        <option value="all">All Categories</option>
                        <option value="Cardio">Cardio</option>
                        <option value="Diabetes">Diabetes</option>
                        <option value="Blood">Blood</option>
                        <option value="Hormone">Hormone</option>
                        <option value="Organ">Organ</option>
                        <option value="Nutrition">Nutrition</option>
                        <option value="Allergy">Allergy</option>
                    </select>
                </div>
                <div class="field">
                    <label for="packageSort">Sort by</label>
                    <select id="packageSort">
                        <option value="name-asc">Name (A-Z)</option>
                        <option value="name-desc">Name (Z-A)</option>
                        <option value="price-asc">Price (Low to High)</option>
                        <option value="price-desc">Price (High to Low)</option>
                    </select>
                </div>
            </div>

            <div class="package-list">
                <?php foreach ($packages as $name => $package): ?>
                    <?php
                    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
                    $safeDescription = htmlspecialchars($package['description'], ENT_QUOTES, 'UTF-8');
                    $price = (int) $package['pricing'];
                    $safeCategory = htmlspecialchars($package['category'], ENT_QUOTES, 'UTF-8');
                    ?>
                    <div class="package-item"
                        data-name="<?php echo htmlspecialchars(strtolower($name), ENT_QUOTES, 'UTF-8'); ?>"
                        data-description="<?php echo htmlspecialchars(strtolower($package['description']), ENT_QUOTES, 'UTF-8'); ?>"
                        data-price="<?php echo $price; ?>"
                        data-category="<?php echo htmlspecialchars(strtolower($package['category']), ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="package-meta">
                            <h3><?php echo $safeName; ?></h3>
                            <span class="package-category"><?php echo $safeCategory; ?></span>
                        </div>
                        <p><?php echo $safeDescription; ?></p>
                        <div class="package-meta">
                            <span class="package-price">NPR <?php echo number_format($price); ?></span>
                            <label>
                                <input type="checkbox" name="packages[]" value="<?php echo $safeName; ?>"> Select
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn">Book Selected Packages</button>
        </form>
    </section>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script>
        (function () {
            const searchInput = document.getElementById('packageSearch');
            const categorySelect = document.getElementById('packageCategory');
            const sortSelect = document.getElementById('packageSort');
            const packageList = document.querySelector('.package-list');

            if (!searchInput || !categorySelect || !sortSelect || !packageList) {
                return;
            }

            const cards = Array.from(packageList.querySelectorAll('.package-item'));
            const noData = document.createElement('div');
            noData.className = 'no-package-match';
            noData.textContent = 'No packages match your filter.';

            function applyFilters() {
                const term = searchInput.value.trim().toLowerCase();
                const category = categorySelect.value;
                const sort = sortSelect.value;

                const filtered = cards.filter((card) => {
                    const name = card.dataset.name || '';
                    const description = card.dataset.description || '';
                    const cardCategory = card.dataset.category || '';

                    const matchesSearch = !term || name.includes(term) || description.includes(term);
                    const matchesCategory = category === 'all' || cardCategory === category.toLowerCase();

                    return matchesSearch && matchesCategory;
                });

                filtered.sort((a, b) => {
                    const nameA = (a.dataset.name || '').toLowerCase();
                    const nameB = (b.dataset.name || '').toLowerCase();
                    const priceA = parseInt(a.dataset.price || '0', 10);
                    const priceB = parseInt(b.dataset.price || '0', 10);

                    if (sort === 'name-desc') {
                        return nameB.localeCompare(nameA);
                    }
                    if (sort === 'price-asc') {
                        return priceA - priceB;
                    }
                    if (sort === 'price-desc') {
                        return priceB - priceA;
                    }
                    return nameA.localeCompare(nameB);
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
            }

            searchInput.addEventListener('input', applyFilters);
            categorySelect.addEventListener('change', applyFilters);
            sortSelect.addEventListener('change', applyFilters);
            applyFilters();
        })();
    </script>
</body>

</html>