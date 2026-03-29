<?php
require_once '../src/db.php';
session_start();

if (!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn']) {
  header('location: login.php');
  exit;
}

function h($value)
{
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function normalizeJsonArray($raw, $fieldLabel, &$errors)
{
  $trimmed = trim((string) $raw);
  if ($trimmed === '') {
    return '[]';
  }

  $decoded = json_decode($trimmed, true);
  if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
    $errors[] = $fieldLabel . ' must be a valid JSON array.';
    return '[]';
  }

  return json_encode(array_values($decoded), JSON_UNESCAPED_UNICODE);
}

$packagesTable = $table['PACKAGES'];
$errors = [];
$success = '';

// Base form defaults
$form = [
  'id' => '',
  'name' => '',
  'description' => '',
  'pricing' => '',
  'category' => '',
  'tags' => '[]',
  'related_packages' => '[]',
  'popularity' => '0',
];

$isEditMode = false;
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  if ($action === 'delete') {
    $deleteId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if ($deleteId <= 0) {
      $errors[] = 'Invalid package ID for deletion.';
    } else {
      $sql = "DELETE FROM {$packagesTable} WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('i', $deleteId);
      $stmt->execute();
      $affected = $stmt->affected_rows;
      $stmt->close();

      if ($affected > 0) {
        header('Location: health-package.php?status=deleted');
        exit;
      }
      $errors[] = 'Package was not found or already deleted.';
    }
  }

  if ($action === 'create' || $action === 'update') {
    $form['id'] = trim($_POST['id'] ?? '');
    $form['name'] = trim($_POST['name'] ?? '');
    $form['description'] = trim($_POST['description'] ?? '');
    $form['pricing'] = trim($_POST['pricing'] ?? '');
    $form['category'] = trim($_POST['category'] ?? '');
    $form['tags'] = trim($_POST['tags'] ?? '[]');
    $form['related_packages'] = trim($_POST['related_packages'] ?? '[]');
    $form['popularity'] = trim($_POST['popularity'] ?? '0');

    $id = (int) $form['id'];
    $pricing = (int) $form['pricing'];
    $popularity = (int) $form['popularity'];

    if ($id <= 0) {
      $errors[] = 'Package ID must be a positive integer.';
    }
    if ($form['name'] === '') {
      $errors[] = 'Package name is required.';
    }
    if ($form['description'] === '') {
      $errors[] = 'Description is required.';
    }
    if ($pricing < 0) {
      $errors[] = 'Pricing must be 0 or higher.';
    }
    if ($form['category'] === '') {
      $errors[] = 'Category is required.';
    }
    if ($popularity < 0 || $popularity > 100) {
      $errors[] = 'Popularity must be between 0 and 100.';
    }

    $tagsJson = normalizeJsonArray($form['tags'], 'Tags', $errors);
    $relatedJson = normalizeJsonArray($form['related_packages'], 'Related packages', $errors);

    if (empty($errors)) {
      if ($action === 'create') {
        $sql = "INSERT INTO {$packagesTable} (id, name, description, pricing, category, tags, related_packages, popularity)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
          'ississsi',
          $id,
          $form['name'],
          $form['description'],
          $pricing,
          $form['category'],
          $tagsJson,
          $relatedJson,
          $popularity
        );
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
          header('Location: health-package.php?status=created');
          exit;
        }

        $errors[] = 'Could not create package. ID or Name may already exist.';
      }

      if ($action === 'update') {
        $sql = "UPDATE {$packagesTable}
                SET name = ?, description = ?, pricing = ?, category = ?, tags = ?, related_packages = ?, popularity = ?
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
          'ssisssii',
          $form['name'],
          $form['description'],
          $pricing,
          $form['category'],
          $tagsJson,
          $relatedJson,
          $popularity,
          $id
        );
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected >= 0) {
          header('Location: health-package.php?status=updated');
          exit;
        }

        $errors[] = 'Could not update package.';
      }
    }
  }
}

$status = $_GET['status'] ?? '';
if ($status === 'created') {
  $success = 'Package created successfully.';
} elseif ($status === 'updated') {
  $success = 'Package updated successfully.';
} elseif ($status === 'deleted') {
  $success = 'Package deleted successfully.';
}

if ($editId > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
  $sql = "SELECT id, name, description, pricing, category, tags, related_packages, popularity
          FROM {$packagesTable}
          WHERE id = ?
          LIMIT 1";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('i', $editId);
  $stmt->execute();
  $result = $stmt->get_result();
  $record = $result->fetch_assoc();
  $stmt->close();

  if ($record) {
    $isEditMode = true;
    $form['id'] = (string) $record['id'];
    $form['name'] = (string) $record['name'];
    $form['description'] = (string) $record['description'];
    $form['pricing'] = (string) $record['pricing'];
    $form['category'] = (string) $record['category'];
    $form['tags'] = (string) $record['tags'];
    $form['related_packages'] = (string) $record['related_packages'];
    $form['popularity'] = (string) $record['popularity'];
  } else {
    $errors[] = 'The selected package for editing was not found.';
  }
}

// Search, filter, and sorting
$searchTerm = trim($_GET['search'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');
$sortBy = $_GET['sort'] ?? 'updated';
$currentPage = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$itemsPerPage = 10;

// Build WHERE clause
$whereConditions = [];
$queryParams = [];
if ($searchTerm !== '') {
  $whereConditions[] = "name LIKE ?";
  $queryParams[] = '%' . $searchTerm . '%';
}
if ($categoryFilter !== '') {
  $whereConditions[] = "category = ?";
  $queryParams[] = $categoryFilter;
}

$whereClause = !empty($whereConditions) ? ' WHERE ' . implode(' AND ', $whereConditions) : '';

// Determine sort order
$sortMap = [
  'name' => 'name ASC',
  'name-desc' => 'name DESC',
  'price' => 'pricing ASC',
  'price-desc' => 'pricing DESC',
  'popularity' => 'popularity DESC',
  'updated' => 'updated_at DESC',
];
$orderBy = $sortMap[$sortBy] ?? $sortMap['updated'];

// Get total count
$countSql = "SELECT COUNT(*) as total FROM {$packagesTable}" . $whereClause;
$countStmt = $conn->prepare($countSql);
if (!empty($queryParams)) {
  $types = str_repeat('s', count($queryParams));
  $countStmt->bind_param($types, ...$queryParams);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$countRow = $countResult->fetch_assoc();
$totalItems = (int) $countRow['total'];
$countStmt->close();

$totalPages = ceil($totalItems / $itemsPerPage);
if ($currentPage > $totalPages && $totalPages > 0) {
  $currentPage = $totalPages;
}

$offset = ($currentPage - 1) * $itemsPerPage;

// Get paginated results
$packageRows = [];
$listSql = "SELECT id, name, category, pricing, popularity, updated_at
            FROM {$packagesTable}" . $whereClause . "
            ORDER BY " . $orderBy . "
            LIMIT ? OFFSET ?";
$listStmt = $conn->prepare($listSql);
$types = !empty($queryParams) ? str_repeat('s', count($queryParams)) . 'ii' : 'ii';
$params = array_merge($queryParams, [$itemsPerPage, $offset]);
if (!empty($params)) {
  $listStmt->bind_param($types, ...$params);
}
$listStmt->execute();
$listResult = $listStmt->get_result();
$packageRows = $listResult->fetch_all(MYSQLI_ASSOC);
$listStmt->close();

// Get unique categories for filter dropdown
$categoriesArr = [];
$catSql = "SELECT DISTINCT category FROM {$packagesTable} ORDER BY category ASC";
$catResult = $conn->query($catSql);
if ($catResult) {
  while ($catRow = $catResult->fetch_assoc()) {
    $categoriesArr[] = $catRow['category'];
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Health Packages</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="admin-panel">
  <aside class="sidebar">
    <h2>Admin Dashboard</h2>
    <ul>
      <li><a href="dashboard.php">Dashboard</a></li>
      <li><a href="users.php">Users</a></li>
      <li><a href="appointments.php">Appointments</a></li>
      <li><a href="health-package.php" class="active">Health Packages</a></li>
      <li><a href="reports.php">Reports</a></li>
      <li><a href="patient-results.php">Patient Results</a></li>
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </aside>

  <main class="main-content">
    <div class="header">
      <h1>Diagnostic Packages</h1>
      <p>Create, edit, and delete records from the diagnostic package catalog.</p>
    </div>

    <?php if ($success !== ''): ?>
      <div class="card admin-alert success">
        <p><?php echo h($success); ?></p>
      </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="card admin-alert error">
        <?php foreach ($errors as $error): ?>
          <p><?php echo h($error); ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="card">
      <h2><?php echo $isEditMode ? 'Edit Package' : 'Add New Package'; ?></h2>
      <form method="POST" class="package-admin-form">
        <input type="hidden" name="action" value="<?php echo $isEditMode ? 'update' : 'create'; ?>">

        <div class="package-admin-grid">
          <div>
            <label for="pkg_id">Package ID</label>
            <input id="pkg_id" type="number" min="1" name="id" required value="<?php echo h($form['id']); ?>"
              <?php echo $isEditMode ? 'readonly' : ''; ?>>
          </div>
          <div>
            <label for="pkg_name">Package Name</label>
            <input id="pkg_name" type="text" name="name" required value="<?php echo h($form['name']); ?>">
          </div>
          <div>
            <label for="pkg_category">Category</label>
            <input id="pkg_category" type="text" name="category" required value="<?php echo h($form['category']); ?>">
          </div>
          <div>
            <label for="pkg_pricing">Pricing</label>
            <input id="pkg_pricing" type="number" min="0" name="pricing" required value="<?php echo h($form['pricing']); ?>">
          </div>
          <div>
            <label for="pkg_popularity">Popularity (0-100)</label>
            <input id="pkg_popularity" type="number" min="0" max="100" name="popularity" required
              value="<?php echo h($form['popularity']); ?>">
          </div>
          <div>
            <label for="pkg_tags">Tags JSON</label>
            <input id="pkg_tags" type="text" name="tags" value="<?php echo h($form['tags']); ?>" placeholder='["blood", "diabetes"]'>
          </div>
          <div class="package-admin-full">
            <label for="pkg_related">Related Packages JSON</label>
            <input id="pkg_related" type="text" name="related_packages" value="<?php echo h($form['related_packages']); ?>" placeholder='[2, 3]'>
          </div>
          <div class="package-admin-full">
            <label for="pkg_description">Description</label>
            <textarea id="pkg_description" name="description" rows="4" required><?php echo h($form['description']); ?></textarea>
          </div>
        </div>

        <div class="package-admin-actions">
          <button type="submit"><?php echo $isEditMode ? 'Update Package' : 'Create Package'; ?></button>
          <?php if ($isEditMode): ?>
            <a class="btn" href="health-package.php">Cancel Edit</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <div class="card">
      <h2>Package List</h2>

      <!-- Search and Filter Form -->
      <form method="GET" class="search-filter-form">
        <div class="search-filter-grid">
          <div>
            <label for="search_term">Search by Name</label>
            <input id="search_term" type="text" name="search" value="<?php echo h($searchTerm); ?>" placeholder="Package name...">
          </div>
          <div>
            <label for="filter_category">Filter by Category</label>
            <select id="filter_category" name="category">
              <option value="">All Categories</option>
              <?php foreach ($categoriesArr as $cat): ?>
                <option value="<?php echo h($cat); ?>" <?php echo $categoryFilter === $cat ? 'selected' : ''; ?>>
                  <?php echo h($cat); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label for="sort_by">Sort By</label>
            <select id="sort_by" name="sort">
              <option value="updated" <?php echo $sortBy === 'updated' ? 'selected' : ''; ?>>Latest Updated</option>
              <option value="name" <?php echo $sortBy === 'name' ? 'selected' : ''; ?>>Name (A-Z)</option>
              <option value="name-desc" <?php echo $sortBy === 'name-desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
              <option value="price" <?php echo $sortBy === 'price' ? 'selected' : ''; ?>>Price (Low to High)</option>
              <option value="price-desc" <?php echo $sortBy === 'price-desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
              <option value="popularity" <?php echo $sortBy === 'popularity' ? 'selected' : ''; ?>>Most Popular</option>
            </select>
          </div>
          <div class="search-filter-actions">
            <button type="submit" class="search-btn">Apply Filters</button>
            <a href="health-package.php" class="btn reset-btn">Clear All</a>
          </div>
        </div>
      </form>

      <!-- Results Summary -->
      <div class="results-summary">
        <p>Showing <?php echo count($packageRows) > 0 ? (($currentPage - 1) * $itemsPerPage + 1) : 0; ?>–<?php echo min($currentPage * $itemsPerPage, $totalItems); ?> of <?php echo $totalItems; ?> packages</p>
      </div>

      <div class="table-container">
        <table class="user-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Category</th>
              <th>Pricing</th>
              <th>Popularity</th>
              <th>Updated</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($packageRows) > 0): ?>
              <?php foreach ($packageRows as $row): ?>
                <tr>
                  <td><?php echo (int) $row['id']; ?></td>
                  <td><?php echo h($row['name']); ?></td>
                  <td><?php echo h($row['category']); ?></td>
                  <td><?php echo (int) $row['pricing']; ?></td>
                  <td><?php echo (int) $row['popularity']; ?></td>
                  <td><?php echo h($row['updated_at']); ?></td>
                  <td>
                    <a href="health-package.php?edit=<?php echo (int) $row['id']; ?>">
                      <button type="button" class="edit-btn">Edit</button>
                    </a>
                    <form method="POST" class="inline-form" onsubmit="return confirm('Delete this package?');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                      <button type="submit" class="delete-btn">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7">No diagnostic packages found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination Controls -->
      <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php if ($currentPage > 1): ?>
            <a href="?page=<?php echo $currentPage - 1; ?>&search=<?php echo urlencode($searchTerm); ?>&category=<?php echo urlencode($categoryFilter); ?>&sort=<?php echo urlencode($sortBy); ?>" class="pagination-link">← Previous</a>
          <?php else: ?>
            <span class="pagination-link disabled">← Previous</span>
          <?php endif; ?>

          <div class="pagination-info">
            Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?>
          </div>

          <?php if ($currentPage < $totalPages): ?>
            <a href="?page=<?php echo $currentPage + 1; ?>&search=<?php echo urlencode($searchTerm); ?>&category=<?php echo urlencode($categoryFilter); ?>&sort=<?php echo urlencode($sortBy); ?>" class="pagination-link">Next →</a>
          <?php else: ?>
            <span class="pagination-link disabled">Next →</span>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>
</body>

</html>
