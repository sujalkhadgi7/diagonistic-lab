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

    <section id="packages" class="section-container">

        <div class="recommended-section" id="recommendedSection">
            <div class="section-head">
                <div>
                    <h3 id="recommendedTitle">Recommended For You</h3>
                    <p id="recommendedSubtitle" class="section-subtitle">
                        Based on your previous bookings.
                    </p>
                </div>
            </div>
            <div id="recommendedPackages" class="package-list recommended-list"></div>
        </div>


        <h2>Our Test Packages</h2>
        <p class="section-subtitle">
            Search, filter, compare, and book the right diagnostic packages for your needs.
        </p>

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

            <div class="package-result-meta">
                <p id="packageResultCount" aria-live="polite"></p>
            </div>



            <div id="allPackagesList" class="package-list">
                <?php
                $packages = [];
                $packageTableName = preg_replace('/\W/', '', (string) ($table['PACKAGES'] ?? 'diagnostic_packages'));
                if (!empty($packageTableName)) {
                    $packageSql = "SELECT id, name, description, pricing, category, tags, related_packages, popularity FROM `{$packageTableName}` ORDER BY id ASC";
                    $packageResult = $conn->query($packageSql);

                    if ($packageResult && $packageResult->num_rows > 0) {
                        while ($row = $packageResult->fetch_assoc()) {
                            $decodedTags = json_decode((string) ($row['tags'] ?? '[]'), true);
                            $decodedRelated = json_decode((string) ($row['related_packages'] ?? '[]'), true);

                            $tags = is_array($decodedTags) ? array_values(array_map('strval', $decodedTags)) : [];
                            $relatedPackages = is_array($decodedRelated)
                                ? array_values(array_filter(array_map('intval', $decodedRelated), fn($value) => $value > 0))
                                : [];

                            $packages[] = [
                                'id' => (int) ($row['id'] ?? 0),
                                'name' => (string) ($row['name'] ?? ''),
                                'description' => (string) ($row['description'] ?? ''),
                                'pricing' => (int) ($row['pricing'] ?? 0),
                                'category' => (string) ($row['category'] ?? ''),
                                'tags' => $tags,
                                'related_packages' => $relatedPackages,
                                'popularity' => (int) ($row['popularity'] ?? 0),
                            ];
                        }
                    }
                }

                if (empty($packages)) {
                    echo '<div class="no-package-match">There is no any test report right now.</div>';
                }

                // Build mapping of package names to IDs
                $packageNameToId = [];
                foreach ($packages as $package) {
                    $packageNameToId[$package['name']] = $package['id'];
                }

                // Fetch user's booking history from database
                $bookingHistory = [];
                if (isset($_SESSION['email']) && !empty($_SESSION['email'])) {
                    $userEmail = $_SESSION['email'];

                    $query = "SELECT package FROM " . $table['APPOINTMENT'] . " WHERE email = ?";
                    $stmt = $conn->prepare($query);

                    if ($stmt) {
                        $stmt->bind_param("s", $userEmail);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
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
                    $displayCategory = htmlspecialchars((string) ($package['category'] ?? ''), ENT_QUOTES, 'UTF-8');
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
                        <h3>
                            <?php echo $name; ?>
                        </h3>
                        <p>
                            <?php echo $description; ?>
                        </p>

                        <div class="package-meta">
                            <span class="package-category">
                                <?php echo $displayCategory; ?>
                            </span>
                            <span class="package-price">Rs.
                                <?php echo $price; ?>
                            </span>
                        </div>

                        <label class="package-select">
                            <input type="checkbox" name="packages[]" value="<?php echo $name; ?>">
                            <span>Select package</span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="booking-quickbar" id="bookingQuickBar">
                <div class="booking-quickbar-meta">
                    <strong id="selectedPackageCount">0 packages selected</strong>
                    <span id="selectedPackageTotal">Total: Rs. 0</span>
                </div>
                <button type="submit" class="btn" id="quickBookButton" disabled>
                    Book Selected Packages
                </button>
            </div>
        </form>
    </section>

    <?php include_once __DIR__ . '/includes/footer.php'; ?>

    <script>
        (function () {
            const packageList = document.getElementById('allPackagesList');
            const recommendedContainer = document.getElementById('recommendedPackages');
            const recommendedSection = document.getElementById('recommendedSection');
            const recommendedTitle = document.getElementById('recommendedTitle');
            const recommendedSubtitle = document.getElementById('recommendedSubtitle');
            const searchInput = document.getElementById('packageSearch');
            const categorySelect = document.getElementById('packageCategory');
            const sortSelect = document.getElementById('packageSort');
            const resetButton = document.getElementById('packageReset');
            const resultCount = document.getElementById('packageResultCount');
            const selectedPackageCount = document.getElementById('selectedPackageCount');
            const selectedPackageTotal = document.getElementById('selectedPackageTotal');
            const quickBookButton = document.getElementById('quickBookButton');
            const inlineBookButton = document.getElementById('inlineBookButton');
            const bookingHistory = <?php echo json_encode($bookingHistory, JSON_UNESCAPED_UNICODE); ?>;

            if (!packageList || !recommendedContainer) {
                return;
            }

            const cards = Array.from(packageList.querySelectorAll('.package-item'));
            const noData = document.createElement('div');
            noData.className = 'no-package-match';
            noData.textContent = 'No packages match your filters.';

            if (!cards.length) {
                if (recommendedSection) {
                    recommendedSection.style.display = 'none';
                }

                if (resultCount) {
                    resultCount.textContent = 'There is no any test report right now.';
                }

                [searchInput, categorySelect, sortSelect, resetButton, quickBookButton].forEach((element) => {
                    if (element) {
                        element.disabled = true;
                    }
                });

                return;
            }

            const cardMap = new Map();
            cards.forEach((card) => {
                const id = parseInt(card.dataset.id || '0', 10);
                if (id) {
                    cardMap.set(id, card);
                }
            });

            function parsePackagesFromDom() {
                return cards
                    .map((card) => {
                        const relatedPackages = (card.dataset.relatedPackages || '')
                            .split(',')
                            .map((value) => parseInt(value, 10))
                            .filter((value) => Number.isInteger(value) && value > 0);

                        return {
                            id: parseInt(card.dataset.id || '0', 10),
                            index: parseInt(card.dataset.index || '0', 10),
                            name: card.querySelector('h3')?.textContent?.trim() || '',
                            description: card.querySelector('p')?.textContent?.trim() || '',
                            category: card.dataset.category || '',
                            pricing: parseInt(card.dataset.price || '0', 10),
                            popularity: parseInt(card.dataset.popularity || '0', 10),
                            relatedPackages
                        };
                    })
                    .filter((pkg) => Number.isInteger(pkg.id) && pkg.id > 0);
            }

            const packages = parsePackagesFromDom();

            function populateCategories() {
                if (!categorySelect) {
                    return;
                }

                const existingValues = new Set(
                    Array.from(categorySelect.options).map((option) => option.value)
                );

                const categories = Array.from(
                    new Set(packages.map((pkg) => pkg.category).filter(Boolean))
                ).sort((a, b) => a.localeCompare(b));

                categories.forEach((categoryValue) => {
                    if (existingValues.has(categoryValue)) {
                        return;
                    }

                    const option = document.createElement('option');
                    option.value = categoryValue;
                    option.textContent = categoryValue.charAt(0).toUpperCase() + categoryValue.slice(1);
                    categorySelect.appendChild(option);
                });
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

                if (inlineBookButton) {
                    inlineBookButton.disabled = count === 0;
                }
            }

            function getRecommendedPackages(allPackages, history, limit) {
                const bookedIds = new Set(history.map((item) => item.packageId));
                const bookedPackages = allPackages.filter((pkg) => bookedIds.has(pkg.id));

                if (!history.length || !bookedPackages.length) {
                    return [...allPackages]
                        .sort((a, b) => (b.popularity || 0) - (a.popularity || 0))
                        .slice(0, limit)
                        .map((pkg) => ({
                            ...pkg,
                            score: pkg.popularity || 0
                        }));
                }

                const candidateMap = new Map();

                function addCandidate(pkg, points, sourceBookedId) {
                    if (!pkg || bookedIds.has(pkg.id)) {
                        return;
                    }

                    if (!candidateMap.has(pkg.id)) {
                        candidateMap.set(pkg.id, {
                            ...pkg,
                            score: 0,
                            sourceBookedIds: new Set()
                        });
                    }

                    const existing = candidateMap.get(pkg.id);
                    existing.score += points;

                    if (Number.isInteger(sourceBookedId)) {
                        const beforeSize = existing.sourceBookedIds.size;
                        existing.sourceBookedIds.add(sourceBookedId);
                        if (existing.sourceBookedIds.size > beforeSize) {
                            existing.score += 15;
                        }
                    }
                }

                bookedPackages.forEach((bookedPkg) => {
                    (bookedPkg.relatedPackages || []).forEach((relatedId) => {
                        const relatedPkg = allPackages.find((pkg) => pkg.id === relatedId);
                        if (relatedPkg) {
                            addCandidate(relatedPkg, 50, bookedPkg.id);
                        }
                    });
                });

                if (candidateMap.size < limit) {
                    bookedPackages.forEach((bookedPkg) => {
                        allPackages.forEach((pkg) => {
                            if (pkg.category === bookedPkg.category && !bookedIds.has(pkg.id)) {
                                addCandidate(pkg, 20, bookedPkg.id);
                            }
                        });
                    });
                }

                candidateMap.forEach((candidate) => {
                    candidate.score += candidate.popularity || 0;
                });

                return Array.from(candidateMap.values())
                    .sort((a, b) => b.score - a.score)
                    .slice(0, limit);
            }

            function selectPackageById(packageId) {
                const mainCard = cardMap.get(packageId);
                if (!mainCard) {
                    return;
                }

                const mainCheckbox = mainCard.querySelector('input[type="checkbox"]');
                if (mainCheckbox && !mainCheckbox.checked) {
                    mainCheckbox.checked = true;
                    updateBookingSummary();
                }

                mainCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                mainCard.classList.add('package-highlight');
                setTimeout(() => {
                    mainCard.classList.remove('package-highlight');
                }, 1200);
            }

            function renderRecommendations(recommendations) {
                recommendedContainer.innerHTML = '';

                if (!recommendations.length) {
                    recommendedSection.style.display = 'none';
                    return;
                }

                recommendedSection.style.display = '';
                const hasHistory = bookingHistory.length > 0;
                recommendedTitle.textContent = hasHistory ? 'Recommended For You' : 'Popular Packages';
                recommendedSubtitle.textContent = hasHistory
                    ? 'Based on your previous bookings.'
                    : 'Trending tests patients book most often.';

                recommendations.forEach((pkg) => {
                    const card = document.createElement('div');
                    card.className = 'package-item';
                    card.innerHTML = `
                        <h3>${pkg.name}</h3>
                        <p>${pkg.description}</p>
                        <div class="package-meta">
                            <span class="package-category">${pkg.category}</span>
                            <span class="package-price">Rs. ${pkg.pricing || 0}</span>
                        </div>
                        <label class="package-select">
                            <input type="checkbox" class="recommend-select" data-package-id="${pkg.id}">
                            <span>Select package</span>
                        </label>
                    `;

                    const recommendCheckbox = card.querySelector('.recommend-select');
                    if (recommendCheckbox) {
                        const mainCard = cardMap.get(pkg.id);
                        const mainCheckbox = mainCard ? mainCard.querySelector('input[type="checkbox"]') : null;

                        if (mainCheckbox) {
                            recommendCheckbox.checked = mainCheckbox.checked;

                            recommendCheckbox.addEventListener('change', () => {
                                mainCheckbox.checked = recommendCheckbox.checked;
                                updateBookingSummary();
                                if (recommendCheckbox.checked) {
                                    selectPackageById(pkg.id);
                                }
                            });

                            mainCheckbox.addEventListener('change', () => {
                                recommendCheckbox.checked = mainCheckbox.checked;
                            });
                        }
                    }

                    recommendedContainer.appendChild(card);
                });
            }

            function applyFilters() {
                const term = searchInput ? searchInput.value.trim().toLowerCase() : '';
                const category = categorySelect ? categorySelect.value : 'all';
                const sort = sortSelect ? sortSelect.value : 'name-asc';

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

                    if (sort === 'name-desc') return nameB.localeCompare(nameA);
                    if (sort === 'price-asc') return priceA - priceB;
                    if (sort === 'price-desc') return priceB - priceA;
                    if (sort === 'popularity-desc') return popularityB - popularityA;
                    if (sort === 'name-asc') return nameA.localeCompare(nameB);
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
                    resultCount.textContent = `${filtered.length} package${filtered.length === 1 ? '' : 's'} shown`;
                }
            }

            populateCategories();
            renderRecommendations(getRecommendedPackages(packages, bookingHistory, 3));
            applyFilters();
            updateBookingSummary();

            if (searchInput) searchInput.addEventListener('input', applyFilters);
            if (categorySelect) categorySelect.addEventListener('change', applyFilters);
            if (sortSelect) sortSelect.addEventListener('change', applyFilters);

            if (resetButton) {
                resetButton.addEventListener('click', () => {
                    if (searchInput) searchInput.value = '';
                    if (categorySelect) categorySelect.value = 'all';
                    if (sortSelect) sortSelect.value = 'name-asc';
                    applyFilters();
                });
            }

            cards.forEach((card) => {
                const checkbox = card.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.addEventListener('change', updateBookingSummary);
                }
            });
        })();
    </script>
</body>

</html>
