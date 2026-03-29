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

$search = trim($_GET['search'] ?? '');
$appointmentId = isset($_GET['appointment_id']) ? (int) $_GET['appointment_id'] : 0;
$rows = [];
$message = '';

if ($appointmentId > 0) {
  $sql = "SELECT id, name, email, phone, package, date, report FROM {$table['APPOINTMENT']} WHERE id = ? LIMIT 1";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('i', $appointmentId);
  $stmt->execute();
  $result = $stmt->get_result();
  $rows = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  if (count($rows) === 0) {
    $message = 'No appointment found for that patient ID.';
  }
} elseif ($search !== '') {
  $like = '%' . $search . '%';
  $sql = "SELECT id, name, email, phone, package, date, report
            FROM {$table['APPOINTMENT']}
            WHERE name LIKE ? OR email LIKE ? OR phone LIKE ?
            ORDER BY id DESC
            LIMIT 100";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('sss', $like, $like, $like);
  $stmt->execute();
  $result = $stmt->get_result();
  $rows = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  if (count($rows) === 0) {
    $message = 'No patient matched your search.';
  }
} else {
  $message = 'Search by patient name, email, phone, or open directly from the appointments list.';
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
      <p>Find a patient and view uploaded test reports.</p>
    </div>

    <div class="card">
      <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="Search by name, email, or phone"
          value="<?php echo h($search); ?>">
        <button type="submit">Search</button>
      </form>
      <p class="result-note"><?php echo h($message); ?></p>
    </div>

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
                    <span class="status-pill <?php echo $hasReports ? '' : 'empty'; ?>">
                      <?php echo $hasReports ? 'Uploaded' : 'Not Uploaded'; ?>
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
                          ?>
                          <div class="report-entry">
                            <?php if (!$isPdf): ?>
                              <img src="<?php echo h($reportUrl); ?>" alt="Patient Report">
                            <?php endif; ?>
                            <a href="<?php echo h($reportUrl); ?>" target="_blank" rel="noopener noreferrer">
                              <?php echo h($file); ?>
                            </a>
                            <?php if (!is_file($absolutePath)): ?>
                              <p class="result-note">File is listed in database but missing from uploads folder.</p>
                            <?php endif; ?>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    <?php else: ?>
                      <p class="result-note">No report uploaded for this patient.</p>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8">No data to display.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</body>

</html>