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

function parseReportFiles($reportValue)
{
  $files = [];
  $rawItems = explode(',', (string) $reportValue);

  foreach ($rawItems as $item) {
    $file = basename(trim($item));
    if ($file === '') {
      continue;
    }

    if (!preg_match('/^[a-z0-9._-]+\.(jpg|jpeg|png|gif|webp|pdf)$/i', $file)) {
      continue;
    }

    $files[] = $file;
  }

  return array_values(array_unique($files));
}

// Get parameters
$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$sortBy = $_GET['sort'] ?? 'recent';
$currentPage = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$appointmentId = isset($_GET['appointment_id']) ? (int) $_GET['appointment_id'] : 0;
$itemsPerPage = 10;

// Get patient statistics
$totalPatients = 0;
$sql = "SELECT COUNT(*) AS count FROM {$table['APPOINTMENT']}";
if (($result = $conn->query($sql)) && $result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $totalPatients = (int) $row['count'];
}

$patientsWithReports = 0;
$sql = "SELECT COUNT(*) AS count FROM {$table['APPOINTMENT']} WHERE report IS NOT NULL AND report != ''";
if (($result = $conn->query($sql)) && $result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $patientsWithReports = (int) $row['count'];
}

$patientsWithoutReports = $totalPatients - $patientsWithReports;

$rows = [];
$message = '';

// Build WHERE clause
$whereConditions = [];
$queryParams = [];

if ($appointmentId > 0) {
  $whereConditions[] = "id = ?";
  $queryParams[] = $appointmentId;
} elseif ($search !== '') {
  $like = '%' . $search . '%';
  $whereConditions[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
  $queryParams[] = $like;
  $queryParams[] = $like;
  $queryParams[] = $like;
}

if ($statusFilter === 'uploaded') {
  $whereConditions[] = "report IS NOT NULL AND report != ''";
} elseif ($statusFilter === 'pending') {
  $whereConditions[] = "report IS NULL OR report = ''";
}

$whereClause = !empty($whereConditions) ? ' WHERE ' . implode(' AND ', $whereConditions) : '';

// Determine sort order
$sortMap = [
  'name' => 'name ASC',
  'name-desc' => 'name DESC',
  'date' => 'date ASC',
  'date-desc' => 'date DESC',
  'recent' => 'id DESC',
];
$orderBy = $sortMap[$sortBy] ?? $sortMap['recent'];

// Get total count
$countSql = "SELECT COUNT(*) as total FROM {$table['APPOINTMENT']}" . $whereClause;
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
if ($appointmentId > 0 || $search !== '' || $statusFilter !== '') {
  $listSql = "SELECT id, name, email, phone, package, date, report FROM {$table['APPOINTMENT']}" . $whereClause . "
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
  $rows = $listResult->fetch_all(MYSQLI_ASSOC);
  $listStmt->close();

  if (count($rows) === 0) {
    if ($appointmentId > 0) {
      $message = 'No appointment found for that patient ID.';
    } else {
      $message = 'No patients matched your search.';
    }
  }
} else {
  $message = 'Search by patient name, email, or phone, or open directly from the appointments list.';
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Patient Test Results</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="admin-panel">
  <aside class="sidebar">
    <h2>Admin Panel</h2>
    <nav>
      <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="users.php">Users</a></li>
        <li><a href="appointments.php">Appointments</a></li>
        <li><a href="health-package.php">Health Packages</a></li>
        <li><a href="reports.php">Reports</a></li>
        <li><a href="patient-results.php" class="active">Patient Results</a></li>
        <li><a href="logout.php">Logout</a></li>
      </ul>
    </nav>
  </aside>

  <main class="main-content">
    <div class="header">
      <h1>Patient Test Results</h1>
      <p>Search and view patient test reports and results.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="dashboard-grid">
      <div class="stat-card stat-card-total">
        <div class="stat-icon">👥</div>
        <div class="stat-content">
          <h3>Total Patients</h3>
          <p class="stat-number"><?php echo $totalPatients; ?></p>
          <p class="stat-label">All patients</p>
        </div>
      </div>

      <div class="stat-card stat-card-completed">
        <div class="stat-icon">✔️</div>
        <div class="stat-content">
          <h3>With Reports</h3>
          <p class="stat-number"><?php echo $patientsWithReports; ?></p>
          <p class="stat-label">Reports uploaded</p>
        </div>
      </div>

      <div class="stat-card stat-card-pending">
        <div class="stat-icon">⏳</div>
        <div class="stat-content">
          <h3>Pending Reports</h3>
          <p class="stat-number"><?php echo $patientsWithoutReports; ?></p>
          <p class="stat-label">Awaiting reports</p>
        </div>
      </div>
    </div>

    <!-- Search and Filter Form -->
    <div class="card">
      <form method="GET" class="search-filter-form">
        <div class="search-filter-grid">
          <div>
            <label for="search_term">Search Patient</label>
            <input id="search_term" type="text" name="search" value="<?php echo h($search); ?>"
              placeholder="Name, email, or phone...">
          </div>
          <div>
            <label for="filter_status">Report Status</label>
            <select id="filter_status" name="status">
              <option value="">All Statuses</option>
              <option value="uploaded" <?php echo $statusFilter === 'uploaded' ? 'selected' : ''; ?>>With Reports</option>
              <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending Reports
              </option>
            </select>
          </div>
          <div>
            <label for="sort_by">Sort By</label>
            <select id="sort_by" name="sort">
              <option value="recent" <?php echo $sortBy === 'recent' ? 'selected' : ''; ?>>Most Recent</option>
              <option value="name" <?php echo $sortBy === 'name' ? 'selected' : ''; ?>>Name (A-Z)</option>
              <option value="name-desc" <?php echo $sortBy === 'name-desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
              <option value="date" <?php echo $sortBy === 'date' ? 'selected' : ''; ?>>Appointment Date (Earliest)
              </option>
              <option value="date-desc" <?php echo $sortBy === 'date-desc' ? 'selected' : ''; ?>>Appointment Date (Latest)
              </option>
            </select>
          </div>
          <div class="search-filter-actions">
            <button type="submit" class="search-btn">Search</button>
            <a href="patient-results.php" class="btn reset-btn">Clear All</a>
          </div>
        </div>
      </form>
    </div>

    <!-- Results Summary -->
    <div class="results-summary">
      <p><?php echo h($message); ?></p>
    </div>

    <!-- Results Table -->
    <div class="card">
      <div class="table-container">
        <table class="user-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Package</th>
              <th>Date</th>
              <th>Report Status</th>
              <th>Reports</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($rows) > 0): ?>
              <?php foreach ($rows as $row): ?>
                <?php
                $files = parseReportFiles($row['report'] ?? '');
                $hasReports = count($files) > 0;
                ?>
                <tr>
                  <td><?php echo (int) $row['id']; ?></td>
                  <td><?php echo h($row['name']); ?></td>
                  <td><?php echo h($row['email']); ?></td>
                  <td><?php echo h($row['phone']); ?></td>
                  <td><?php echo h($row['package']); ?></td>
                  <td><?php echo h($row['date'] ?: 'Not scheduled'); ?></td>
                  <td>
                    <?php
                    $files = parseReportFiles($row['report'] ?? '');
                    $hasReports = count($files) > 0;
                    ?>
                    <span class="status-badge <?php echo $hasReports ? 'status-completed' : 'status-pending'; ?>">
                      <?php echo $hasReports ? '✔️ Uploaded' : '⏳ Pending'; ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($hasReports): ?>
                      <div class="report-list">
                        <?php foreach ($files as $file): ?>
                          <?php
                          $isPdf = preg_match('/\.pdf$/i', $file) === 1;
                          $reportUrl = '../uploads/' . rawurlencode($file);
                          $absolutePath = __DIR__ . '/../uploads/' . $file;
                          $fileExists = is_file($absolutePath);
                          ?>
                          <div class="report-entry <?php echo !$fileExists ? 'missing' : ''; ?>">
                            <?php if (!$isPdf && $fileExists): ?>
                              <img src="<?php echo h($reportUrl); ?>" alt="Patient Report" class="report-preview">
                            <?php elseif ($isPdf): ?>
                              <span class="pdf-icon">📄</span>
                            <?php endif; ?>
                            <a href="<?php echo h($reportUrl); ?>" target="_blank" rel="noopener noreferrer"
                              class="report-link">
                              <?php echo h($file); ?>
                            </a>
                            <?php if (!$fileExists): ?>
                              <span class="file-missing">Missing</span>
                            <?php endif; ?>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    <?php else: ?>
                      <span class="text-muted">No reports</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" style="text-align: center; padding: 30px;">No data to display.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination Controls -->
      <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php if ($currentPage > 1): ?>
            <a href="?page=<?php echo $currentPage - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>&sort=<?php echo urlencode($sortBy); ?>"
              class="pagination-link">← Previous</a>
          <?php else: ?>
            <span class="pagination-link disabled">← Previous</span>
          <?php endif; ?>

          <div class="pagination-info">
            Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?>
          </div>

          <?php if ($currentPage < $totalPages): ?>
            <a href="?page=<?php echo $currentPage + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>&sort=<?php echo urlencode($sortBy); ?>"
              class="pagination-link">Next →</a>
          <?php else: ?>
            <span class="pagination-link disabled">Next →</span>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>
</body>

</html>