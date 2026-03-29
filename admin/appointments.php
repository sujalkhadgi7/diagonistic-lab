<?php

// Include the database connection
require_once '../src/db.php';
session_start();

if (!isset($_SESSION["loggedIn"]) || !$_SESSION["loggedIn"]) {
    header('location: login.php');
    exit;
}

// Flash messages
$flashError = $_SESSION['flash_error'] ?? '';
$flashSuccess = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

// Handle form submission to update the appointment date
if (isset($_POST['update_appointment'])) {
    $appointmentId = $_POST['appointment_id'];
    $newAppointmentDate = $_POST['appointment_date'];

    // Update appointment in the database
    $updateSql = "UPDATE {$table['APPOINTMENT']} SET date = ? WHERE id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("si", $newAppointmentDate, $appointmentId);
    $stmt->execute();
    $stmt->close();

    // Get the patient's email
    $getEmailSql = "SELECT email FROM {$table['APPOINTMENT']} WHERE id = ?";
    $stmt = $conn->prepare($getEmailSql);
    $stmt->bind_param("i", $appointmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $patientEmail = $row['email'] ?? '';

    if ($patientEmail) {
        $subject = "Your Appointment is Confirmed!";
        $message = "
        <html>
        <head><title>Appointment Confirmation</title></head>
        <body>
            <h2>Dear Customer,</h2>
            <p>Your appointment has been successfully confirmed.</p>
            <p><strong>Appointment Date:</strong> $newAppointmentDate</p>
            <p>If you have any questions, feel free to contact us.</p>
            <p>Thank you for choosing our Diagnostic Lab!</p>
            <p>Best regards,<br>The Diagnostic Lab Team</p>
        </body>
        </html>";

        require_once 'sendemail/send.php';

        $emailError = null;
        $sent = sendAppointmentEmail($patientEmail, $subject, $message, $emailError);
        if (!$sent) {
            $_SESSION['flash_error'] = 'Email failed to send: ' . ($emailError ?: 'Unknown error');
        } else {
            $_SESSION['flash_success'] = 'Appointment updated and confirmation email sent.';
        }
    }

    header("Location: appointments.php");
    exit;
}

// Handle form submission to save a new appointment
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit"])) {
    $patientName = trim($_POST["patient-name"] ?? "");
    $patientEmail = trim($_POST["patient-email"] ?? "");
    $packageName = trim($_POST["package-name"] ?? "");

    if (!isset($conn) || $conn->connect_error) {
        die("Database connection is not available.");
    }

    if ($patientName === "" || $patientEmail === "" || $packageName === "") {
        die("All fields are required.");
    }

    $stmt = $conn->prepare(
        "INSERT INTO appointment (name, email, package, date) VALUES (?, ?, ?, NULL)"
    );

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sss", $patientName, $patientEmail, $packageName);

    if (!$stmt->execute()) {
        echo "Something went wrong: " . $stmt->error;
    } else {
        echo "Appointment saved successfully.";
    }

    $stmt->close();
}

// Get appointment statistics
$totalAppointments = 0;
$sql = "SELECT COUNT(*) AS count FROM {$table['APPOINTMENT']}";
if (($result = $conn->query($sql)) && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalAppointments = (int) $row['count'];
}

$pendingAppointments = 0;
$sql = "SELECT COUNT(*) AS count FROM {$table['APPOINTMENT']} WHERE date IS NULL";
if (($result = $conn->query($sql)) && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $pendingAppointments = (int) $row['count'];
}

$scheduledAppointments = 0;
$sql = "SELECT COUNT(*) AS count FROM {$table['APPOINTMENT']} WHERE date IS NOT NULL AND report IS NULL";
if (($result = $conn->query($sql)) && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $scheduledAppointments = (int) $row['count'];
}

$completedAppointments = 0;
$sql = "SELECT COUNT(*) AS count FROM {$table['APPOINTMENT']} WHERE report IS NOT NULL";
if (($result = $conn->query($sql)) && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $completedAppointments = (int) $row['count'];
}

// Search, filter, and sorting
$searchTerm = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$sortBy = $_GET['sort'] ?? 'recent';
$currentPage = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$itemsPerPage = 10;

// Build WHERE clause
$whereConditions = [];
$queryParams = [];
if ($searchTerm !== '') {
    $whereConditions[] = "(name LIKE ? OR email LIKE ?)";
    $queryParams[] = '%' . $searchTerm . '%';
    $queryParams[] = '%' . $searchTerm . '%';
}

if ($statusFilter === 'pending') {
    $whereConditions[] = "date IS NULL";
} elseif ($statusFilter === 'scheduled') {
    $whereConditions[] = "date IS NOT NULL AND report IS NULL";
} elseif ($statusFilter === 'completed') {
    $whereConditions[] = "report IS NOT NULL";
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
$appointments = [];
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
$appointments = $listResult->fetch_all(MYSQLI_ASSOC);
$listStmt->close();

?>
}

$stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Appointments</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="admin-panel">
    <aside class="sidebar">
        <h2>Admin Panel</h2>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="appointments.php" class="active">Appointments</a></li>
                <li><a href="health-package.php">Health Packages</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="patient-results.php">Patient Results</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <div class="header">
            <h1>Appointments Management</h1>
            <p>Track, schedule, and manage all patient appointments efficiently.</p>
        </div>

        <?php if ($flashError): ?>
            <div class="card admin-alert error">
                <p><strong>Error:</strong> <?php echo htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        <?php elseif ($flashSuccess): ?>
            <div class="card admin-alert success">
                <p><strong>Success:</strong> <?php echo htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="dashboard-grid">
            <div class="stat-card stat-card-total">
                <div class="stat-icon">📋</div>
                <div class="stat-content">
                    <h3>Total Appointments</h3>
                    <p class="stat-number"><?php echo $totalAppointments; ?></p>
                    <p class="stat-label">All appointments</p>
                </div>
            </div>

            <div class="stat-card stat-card-pending">
                <div class="stat-icon">⏳</div>
                <div class="stat-content">
                    <h3>Pending</h3>
                    <p class="stat-number"><?php echo $pendingAppointments; ?></p>
                    <p class="stat-label">Awaiting date assignment</p>
                </div>
            </div>

            <div class="stat-card stat-card-scheduled">
                <div class="stat-icon">✓</div>
                <div class="stat-content">
                    <h3>Scheduled</h3>
                    <p class="stat-number"><?php echo $scheduledAppointments; ?></p>
                    <p class="stat-label">Confirmed, awaiting results</p>
                </div>
            </div>

            <div class="stat-card stat-card-completed">
                <div class="stat-icon">✔️</div>
                <div class="stat-content">
                    <h3>Completed</h3>
                    <p class="stat-number"><?php echo $completedAppointments; ?></p>
                    <p class="stat-label">Results delivered</p>
                </div>
            </div>
        </div>

        <!-- Search and Filter Form -->
        <div class="card">
            <form method="GET" class="search-filter-form">
                <div class="search-filter-grid">
                    <div>
                        <label for="search_term">Search by Name/Email</label>
                        <input id="search_term" type="text" name="search"
                            value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Patient name or email...">
                    </div>
                    <div>
                        <label for="filter_status">Filter by Status</label>
                        <select id="filter_status" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending
                                (No Date)</option>
                            <option value="scheduled" <?php echo $statusFilter === 'scheduled' ? 'selected' : ''; ?>>
                                Scheduled (Awaiting Results)</option>
                            <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>
                                Completed (Results Delivered)</option>
                        </select>
                    </div>
                    <div>
                        <label for="sort_by">Sort By</label>
                        <select id="sort_by" name="sort">
                            <option value="recent" <?php echo $sortBy === 'recent' ? 'selected' : ''; ?>>Most Recent
                            </option>
                            <option value="name" <?php echo $sortBy === 'name' ? 'selected' : ''; ?>>Name (A-Z)</option>
                            <option value="name-desc" <?php echo $sortBy === 'name-desc' ? 'selected' : ''; ?>>Name (Z-A)
                            </option>
                            <option value="date" <?php echo $sortBy === 'date' ? 'selected' : ''; ?>>Appointment Date
                                (Earliest)</option>
                            <option value="date-desc" <?php echo $sortBy === 'date-desc' ? 'selected' : ''; ?>>Appointment
                                Date (Latest)</option>
                        </select>
                    </div>
                    <div class="search-filter-actions">
                        <button type="submit" class="search-btn">Apply Filters</button>
                        <a href="appointments.php" class="btn reset-btn">Clear All</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Summary -->
        <div class="results-summary">
            <p>Showing
                <?php echo count($appointments) > 0 ? (($currentPage - 1) * $itemsPerPage + 1) : 0; ?>–<?php echo min($currentPage * $itemsPerPage, $totalItems); ?>
                of <?php echo $totalItems; ?> appointments</p>
        </div>

        <!-- Appointments Table -->
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
                            <th>Status</th>
                            <th>Appointment Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($appointments) > 0): ?>
                            <?php foreach ($appointments as $row): ?>
                                <?php
                                $status = 'pending';
                                $statusIcon = '⏳';
                                $statusClass = 'status-pending';
                                if ($row['date'] && $row['report']) {
                                    $status = 'Completed';
                                    $statusIcon = '✔️';
                                    $statusClass = 'status-completed';
                                } elseif ($row['date']) {
                                    $status = 'Scheduled';
                                    $statusIcon = '✓';
                                    $statusClass = 'status-scheduled';
                                } else {
                                    $status = 'Pending Date';
                                    $statusIcon = '⏳';
                                    $statusClass = 'status-pending';
                                }
                                ?>
                                <tr>
                                    <td><?php echo (int) $row["id"]; ?></td>
                                    <td><?php echo htmlspecialchars($row["name"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["email"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["phone"] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row["package"]); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $statusClass; ?>">
                                            <?php echo $statusIcon . ' ' . $status; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row["date"]): ?>
                                            <?php echo htmlspecialchars($row["date"]); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Not set</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="openModalBtn edit-btn"
                                            data-appointment-id="<?php echo (int) $row["id"]; ?>"
                                            data-current-date="<?php echo htmlspecialchars($row["date"] ?? ''); ?>">
                                            Edit Date
                                        </button>
                                        <a href="patient-results.php?appointment_id=<?php echo (int) $row['id']; ?>">
                                            <button type="button" class="view-btn">View</button>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 30px;">No appointments found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1; ?>&search=<?php echo urlencode($searchTerm); ?>&status=<?php echo urlencode($statusFilter); ?>&sort=<?php echo urlencode($sortBy); ?>"
                            class="pagination-link">← Previous</a>
                    <?php else: ?>
                        <span class="pagination-link disabled">← Previous</span>
                    <?php endif; ?>

                    <div class="pagination-info">
                        Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?>
                    </div>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?>&search=<?php echo urlencode($searchTerm); ?>&status=<?php echo urlencode($statusFilter); ?>&sort=<?php echo urlencode($sortBy); ?>"
                            class="pagination-link">Next →</a>
                    <?php else: ?>
                        <span class="pagination-link disabled">Next →</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal to set appointment date -->
    <div id="appointmentModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Set Appointment Date</h2>
            <form action="appointments.php" method="POST">
                <input type="hidden" id="appointment_id" name="appointment_id">
                <label for="appointment_date">Choose a date and time:</label>
                <input type="datetime-local" id="appointment_date" name="appointment_date" required>
                <button type="submit" name="update_appointment">Update Appointment</button>
            </form>
        </div>
    </div>

    <script>
        // Get modal elements
        var modal = document.getElementById("appointmentModal");
        var btns = document.querySelectorAll(".openModalBtn");
        var span = document.getElementsByClassName("close")[0];
        var appointmentDateInput = document.getElementById("appointment_date");

        // Function to set the minimum date and time
        function setMinDateTime() {
            var now = new Date();
            var year = now.getFullYear();
            var month = String(now.getMonth() + 1).padStart(2, '0');
            var day = String(now.getDate()).padStart(2, '0');
            var hours = String(now.getHours()).padStart(2, '0');
            var minutes = String(now.getMinutes()).padStart(2, '0');

            var minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
            appointmentDateInput.min = minDateTime;
        }

        // Open modal when the button is clicked
        btns.forEach(function (btn) {
            btn.onclick = function () {
                var appointmentId = this.getAttribute("data-appointment-id");
                var currentDate = this.getAttribute("data-current-date");

                document.getElementById("appointment_id").value = appointmentId;
                document.getElementById("appointment_date").value = currentDate || "";

                setMinDateTime();  // Ensure past dates/times are disabled

                modal.style.display = "block";
            }
        });

        // Close modal when clicking "X"
        span.onclick = function () {
            modal.style.display = "none";
        }

        // Close modal when clicking outside
        window.onclick = function (event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>

</html>