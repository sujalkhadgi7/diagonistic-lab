<?php

require '../src/db.php';
session_start();

if (!$_SESSION["loggedIn"]) {
    header('location: login.php');
    die;
}

// Handle Delete Request
if (isset($_GET["delete"])) {
    $id = $_GET["delete"];
    $sql = "DELETE FROM user WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: users.php");
    exit();
}

// Handle Add User Request
if (isset($_POST["add_user"])) {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $username= $_POST["username"];
    $password =$_POST["password"];
    $role = $_POST["role"];

    $sql = "INSERT INTO user (Name, Gmail, username, Password, type) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $name, $email, $username, $password, $role);
    $stmt->execute();
    header("Location: users.php");
    exit();
}

// Handle Update Request
if (isset($_POST["update_user"])) {
    $id = $_POST["user_id"];
    $name = $_POST["name"];
    $email = $_POST["email"];
    $role = $_POST["role"];

    $sql = "UPDATE user SET Name=?, Gmail=?, type=? WHERE ID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $name, $email, $role, $id);
    $stmt->execute();
    header("Location: users.php");
    exit();
}

// Fetch Users
$sql = "SELECT * FROM user";
$data = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
</head>
<body>

    <aside class="sidebar">
        <h2>Admin Panel</h2>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="appointments.php">Appointments</a></li>
                <li><a href="health-package.php">Health Packages</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <h1>Manage Users</h1>
        <p>Here you can manage users of the system.</p>

        <!-- Add User Form -->
        <h2>Add New User</h2>
        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="text" name="username" placeholder="username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role" required>
                <option value="Admin">Admin</option>
                <option value="User">User</option>
            </select>
            <button type="submit" name="add_user">Add User</button>
        </form>

        <div class="table-container">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($data->num_rows > 0): ?>
                    <?php while ($row = $data->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row["ID"]; ?></td>
                            <td><?php echo $row["Name"]; ?></td>
                            <td><?php echo $row["Gmail"]; ?></td>
                            <td><?php echo $row["type"]; ?></td>
                            <td>
                                <a href="users.php?delete=<?php echo $row['ID']; ?>" onclick="return confirm('Are you sure?')">
                                    <button class="delete-btn">Delete</button>
                                </a>
                                <button class="edit-btn" onclick="editUser('<?php echo $row['ID']; ?>', '<?php echo $row['Name']; ?>', '<?php echo $row['Gmail']; ?>', '<?php echo $row['type']; ?>')">Edit</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No users found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Edit User Form (Hidden by Default) -->
        <div id="editUserModal" style="display: none;">
            <h2>Edit User</h2>
            <form method="POST">
                <input type="hidden" name="user_id" id="editUserId">
                <input type="text" name="name" id="editUserName" placeholder="Full Name" required>
                <input type="email" name="email" id="editUserEmail" placeholder="Email" required>
                <select name="role" id="editUserRole" required>
                    <option value="Admin">Admin</option>
                    <option value="User">User</option>
                </select>
                <button type="submit" name="update_user">Update User</button>
                <button type="button" onclick="closeEditModal()">Cancel</button>
            </form>
        </div>

    </main>

    <script>
        function editUser(id, name, email, role) {
            document.getElementById("editUserId").value = id;
            document.getElementById("editUserName").value = name;
            document.getElementById("editUserEmail").value = email;
            document.getElementById("editUserRole").value = role;
            document.getElementById("editUserModal").style.display = "block";
        }

        function closeEditModal() {
            document.getElementById("editUserModal").style.display = "none";
        }
    </script>

</body>
</html>
