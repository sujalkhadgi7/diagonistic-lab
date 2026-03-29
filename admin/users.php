<?php

require_once '../src/db.php';
session_start();
define('REDIRECT_PAGE', 'users.php');

// Validate session
if (!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn']) {
    header('Location: login.php');
    exit;
}

define('HEADER_LOCATION', 'Location: ');
$errors = [];

// Handle Delete Request
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id <= 0) {
        $_SESSION['flash_error'] = 'Invalid user ID.';
    } else {
        $sql = "DELETE FROM {$table['USER']} WHERE ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $_SESSION['flash_success'] = 'User deleted successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to delete user.';
        }
        $stmt->close();
    }
    header(HEADER_LOCATION . REDIRECT_PAGE);
    exit;
}

// Handle Add User Request
if (isset($_POST['add_user'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    // Validate inputs
    if (empty($name)) {
        $errors[] = 'Full name is required.';
    }
    if (empty($username) || strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters.';
    }
    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    if (!in_array($role, ['Admin', 'User'])) {
        $errors[] = 'Invalid role selected.';
    }

    // Check username uniqueness
    if (empty($errors)) {
        $checkSql = "SELECT id FROM {$table['USER']} WHERE username = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param('s', $username);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $errors[] = 'Username already exists.';
        }
        $checkStmt->close();
    }

    if (empty($errors)) {
        $sql = "INSERT INTO {$table['USER']} (Name, Gmail, username, Password, type) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssss', $name, $email, $username, $password, $role);
        if ($stmt->execute()) {
            $_SESSION['flash_success'] = 'User added successfully.';
            header(HEADER_LOCATION . REDIRECT_PAGE);
            exit;
        } else {
            $_SESSION['flash_error'] = 'Failed to add user.';
        }
        $stmt->close();
    } else {
        $_SESSION['flash_error'] = implode(' ', $errors);
        header(HEADER_LOCATION . REDIRECT_PAGE);
        exit;
    }
}

// Handle Update Request
if (isset($_POST['update_user'])) {
    $id = (int) ($_POST['user_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? '';

    // Validate inputs
    if ($id <= 0) {
        $errors[] = 'Invalid user ID.';
    }
    if (empty($name)) {
        $errors[] = 'Full name is required.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    if (!in_array($role, ['Admin', 'User'])) {
        $errors[] = 'Invalid role selected.';
    }

    if (empty($errors)) {
        $sql = "UPDATE {$table['USER']} SET Name = ?, Gmail = ?, type = ? WHERE ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssi', $name, $email, $role, $id);
        if ($stmt->execute()) {
            $_SESSION['flash_success'] = 'User updated successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to update user.';
        }
        $stmt->close();
    } else {
        $_SESSION['flash_error'] = implode(' ', $errors);
    }
    header(HEADER_LOCATION . REDIRECT_PAGE);
    exit;
}

// Pagination setup
$itemsPerPage = 10;
$currentPage = (int) ($_GET['page'] ?? 1);
if ($currentPage < 1) {
    $currentPage = 1;
}

// Search functionality
$search = '';
$searchWhere = '';
$searchParam = [];

if (!empty($_GET['search'])) {
    $search = trim($_GET['search']);
    $searchWhere = ' WHERE Name LIKE ? OR Gmail LIKE ? OR username LIKE ?';
    $searchParam = ['%' . $search . '%', '%' . $search . '%', '%' . $search . '%'];
}

// Fetch total users
$countSql = "SELECT COUNT(*) as total FROM {$table['USER']}" . $searchWhere;
$countStmt = $conn->prepare($countSql);
if (!empty($searchParam)) {
    $countStmt->bind_param('sss', ...$searchParam);
}
$countStmt->execute();
$totalUsers = $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

$totalPages = ceil($totalUsers / $itemsPerPage);
if ($currentPage > $totalPages && $totalPages > 0) {
    $currentPage = $totalPages;
}

$offset = ($currentPage - 1) * $itemsPerPage;

// Fetch users with pagination
$sql = "SELECT * FROM {$table['USER']}" . $searchWhere . " ORDER BY ID ASC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

if (!empty($searchParam)) {
    $stmt->bind_param('sssi', $searchParam[0], $searchParam[1], $searchParam[2], $itemsPerPage, $offset);
} else {
    $stmt->bind_param('ii', $itemsPerPage, $offset);
}
$stmt->execute();
$data = $stmt->get_result();
$stmt->close();

// Fetch user statistics
$statsSql = "SELECT
    COUNT(*) as total_users,
    SUM(CASE WHEN type = 'Admin' THEN 1 ELSE 0 END) as admin_count,
    SUM(CASE WHEN type = 'User' THEN 1 ELSE 0 END) as user_count
FROM {$table['USER']}";
$statsResult = $conn->query($statsSql)->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="admin-panel">

    <aside class="sidebar">
        <h2>Admin Panel</h2>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="users.php" class="active">Users</a></li>
                <li><a href="appointments.php">Appointments</a></li>
                <li><a href="health-package.php">Health Packages</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="patient-results.php">Patient Results</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <div class="header">
            <h1>Manage Users</h1>
            <p>Create, update, and maintain user access for the admin system.</p>
        </div>

        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8'); ?>
                <?php unset($_SESSION['flash_success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8'); ?>
                <?php unset($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>

        <!-- User Statistics Cards -->
        <div class="dashboard-grid">
            <div class="stat-card stat-card-users">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <h3>Total Users</h3>
                    <p class="stat-number"><?php echo $statsResult['total_users'] ?? 0; ?></p>
                </div>
            </div>

            <div class="stat-card stat-card-admin">
                <div class="stat-icon">🔑</div>
                <div class="stat-content">
                    <h3>Administrators</h3>
                    <p class="stat-number"><?php echo $statsResult['admin_count'] ?? 0; ?></p>
                </div>
            </div>

            <div class="stat-card stat-card-user">
                <div class="stat-icon">👤</div>
                <div class="stat-content">
                    <h3>Regular Users</h3>
                    <p class="stat-number"><?php echo $statsResult['user_count'] ?? 0; ?></p>
                </div>
            </div>
        </div>

        <!-- Add User Form -->
        <div class="card">
            <h2>Add New User</h2>
            <form method="POST" class="user-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" placeholder="John Doe" required minlength="2">
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="johndoe" required minlength="3">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="john@example.com" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter password" required
                            minlength="6">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" required>
                            <option value="">Select a role</option>
                            <option value="Admin">Administrator</option>
                            <option value="User">Regular User</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
            </form>
        </div>

        <!-- Search Filter Form -->
        <div class="card">
            <form method="GET" class="search-filter-form">
                <input type="text" name="search" placeholder="Search by name, email, or username..."
                    value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn btn-secondary">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="users.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="table-container">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($data->num_rows > 0): ?>
                            <?php while ($row = $data->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['ID'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($row['Name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($row['Gmail'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <span class="role-badge role-<?php echo strtolower($row['type']); ?>">
                                            <?php echo htmlspecialchars($row['type'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-warning btn-sm"
                                            onclick="editUser('<?php echo htmlspecialchars($row['ID'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($row['Name'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($row['Gmail'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($row['type'], ENT_QUOTES, 'UTF-8'); ?>')">Edit</button>
                                        <a href="users.php?delete=<?php echo htmlspecialchars($row['ID'], ENT_QUOTES, 'UTF-8'); ?>"
                                            onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                            <button type="button" class="btn btn-danger btn-sm">Delete</button>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="users.php?page=<?php echo $currentPage - 1; ?>&search=<?php echo urlencode($search); ?>"
                            class="pagination-link">← Previous</a>
                    <?php endif; ?>

                    <span class="pagination-info">Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?></span>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="users.php?page=<?php echo $currentPage + 1; ?>&search=<?php echo urlencode($search); ?>"
                            class="pagination-link">Next →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <button type="button" class="modal-close" onclick="closeEditModal()"
                onKeyPress="if(event.key==='Enter'||event.key===' ')closeEditModal()"
                aria-label="Close modal">&times;</button>
            <h2>Edit User</h2>
            <form method="POST" class="user-form">
                <input type="hidden" name="user_id" id="editUserId">
                <div class="form-group">
                    <label for="editUserName">Full Name</label>
                    <input type="text" id="editUserName" name="name" placeholder="Full Name" required minlength="2">
                </div>
                <div class="form-group">
                    <label for="editUserEmail">Email</label>
                    <input type="email" id="editUserEmail" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <label for="editUserRole">Role</label>
                    <select id="editUserRole" name="role" required>
                        <option value="Admin">Administrator</option>
                        <option value="User">Regular User</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editUser(id, name, email, role) {
            document.getElementById('editUserId').value = id;
            document.getElementById('editUserName').value = name;
            document.getElementById('editUserEmail').value = email;
            document.getElementById('editUserRole').value = role;
            document.getElementById('editUserModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeEditModal() {
            document.getElementById('editUserModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside of it
        window.onclick = function (event) {
            const modal = document.getElementById('editUserModal');
            if (event.target === modal) {
                closeEditModal();
            }
        }
    </script>

</body>

</html>
