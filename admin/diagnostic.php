<?php
/**
 * DIAGNOSTIC PAGE - Check database connectivity and appointment data
 * Access via: http://yourserver/admin/diagnostic.php
 */

require_once '../src/db.php';
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic - Database Check</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #113431;
            margin-top: 0;
        }

        h2 {
            color: #0f766e;
            margin-top: 30px;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 10px;
        }

        .status {
            padding: 15px;
            margin: 15px 0;
            border-radius: 6px;
        }

        .status.success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }

        .status.error {
            background: #fee2e2;
            border-left: 4px solid #dc2626;
            color: #991b1b;
        }

        .status.warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            color: #92400e;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background: #f3f4f6;
            font-weight: 600;
            color: #113431;
        }

        tr:hover {
            background: #f9fafb;
        }

        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New';
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #0f766e;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
        }

        .button:hover {
            background: #065f46;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔍 Database Diagnostic Report</h1>
        <p><strong>Generated:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>

        <!-- Database Connection -->
        <h2>1. Database Connection</h2>
        <?php
        if (!isset($conn) || $conn->connect_error) {
            echo '<div class="status error">❌ <strong>Database Connection Failed</strong><br>';
            echo 'Error: ' . ($conn->connect_error ?? 'Connection object not initialized') . '</div>';
        } else {
            echo '<div class="status success">✓ Database connection successful</div>';
        }
        ?>

        <!-- Table Existence -->
        <h2>2. Table Verification</h2>
        <?php
        $tables = ['APPOINTMENT', 'PACKAGES', 'CUSTOMERS', 'USERS'];

        foreach ($tables as $tableKey) {
            if (!isset($table[$tableKey])) {
                echo '<div class="status warning">⚠ Table constant <code>' . $tableKey . '</code> not defined in src/constants/table.php</div>';
                continue;
            }

            $tableName = $table[$tableKey];
            $checkSql = "SHOW TABLES LIKE '$tableName'";
            $result = $conn->query($checkSql);

            if ($result && $result->num_rows > 0) {
                echo '<div class="status success">✓ Table <code>' . $tableName . '</code> exists</div>';
            } else {
                echo '<div class="status error">❌ Table <code>' . $tableName . '</code> does not exist</div>';
            }
        }
        ?>

        <!-- Appointment Table Structure -->
        <h2>3. Appointment Table Columns</h2>
        <?php
        if (isset($table['APPOINTMENT'])) {
            $tableName = $table['APPOINTMENT'];
            $result = $conn->query("DESCRIBE $tableName");

            if ($result && $result->num_rows > 0) {
                echo '<table>';
                echo '<tr><th>Column Name</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>';
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td><code>' . htmlspecialchars($row['Field']) . '</code></td>';
                    echo '<td>' . htmlspecialchars($row['Type']) . '</td>';
                    echo '<td>' . ($row['Null'] === 'YES' ? '✓' : '✗') . '</td>';
                    echo '<td>' . ($row['Key'] ?: '-') . '</td>';
                    echo '<td><code>' . ($row['Default'] ?: 'NULL') . '</code></td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
        }
        ?>

        <!-- Appointment Data Count -->
        <h2>4. Appointment Data</h2>
        <?php
        if (isset($table['APPOINTMENT'])) {
            $tableName = $table['APPOINTMENT'];

            // Total count
            $totalResult = $conn->query("SELECT COUNT(*) as count FROM $tableName");
            $totalCount = ($totalResult && $totalResult->num_rows > 0) ? $totalResult->fetch_assoc()['count'] : 0;

            // Status breakdown
            $pendingResult = $conn->query("SELECT COUNT(*) as count FROM $tableName WHERE date IS NULL");
            $pendingCount = ($pendingResult && $pendingResult->num_rows > 0) ? $pendingResult->fetch_assoc()['count'] : 0;

            $scheduledResult = $conn->query("SELECT COUNT(*) as count FROM $tableName WHERE date IS NOT NULL AND report IS NULL");
            $scheduledCount = ($scheduledResult && $scheduledResult->num_rows > 0) ? $scheduledResult->fetch_assoc()['count'] : 0;

            $completedResult = $conn->query("SELECT COUNT(*) as count FROM $tableName WHERE report IS NOT NULL");
            $completedCount = ($completedResult && $completedResult->num_rows > 0) ? $completedResult->fetch_assoc()['count'] : 0;

            if ($totalCount > 0) {
                echo '<div class="status success">✓ Found <strong>' . $totalCount . ' total appointments</strong></div>';
                echo '<table>';
                echo '<tr><th>Status</th><th>Count</th></tr>';
                echo '<tr><td>Pending (no date)</td><td>' . $pendingCount . '</td></tr>';
                echo '<tr><td>Scheduled (has date, no report)</td><td>' . $scheduledCount . '</td></tr>';
                echo '<tr><td>Completed (has report)</td><td>' . $completedCount . '</td></tr>';
                echo '</table>';
            } else {
                echo '<div class="status error">❌ <strong>No appointments found in database</strong><br>';
                echo 'You need to import the sample data: <code>src/sql/sample_data.sql</code></div>';
            }
        }
        ?>

        <!-- Sample Appointments -->
        <h2>5. Sample Appointments</h2>
        <?php
        if (isset($table['APPOINTMENT'])) {
            $tableName = $table['APPOINTMENT'];
            $result = $conn->query("SELECT id, name, email, package, date, report FROM $tableName LIMIT 5");

            if ($result && $result->num_rows > 0) {
                echo '<table>';
                echo '<tr><th>ID</th><th>Name</th><th>Email</th><th>Package</th><th>Date</th><th>Report</th></tr>';
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['package']) . '</td>';
                    echo '<td>' . (isset($row['date']) && $row['date'] ? htmlspecialchars($row['date']) : '<em>Not set</em>') . '</td>';
                    echo '<td>' . (isset($row['report']) && $row['report'] ? '✓ Yes' : '✗ No') . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
        }
        ?>

        <!-- Session Status -->
        <h2>6. Session & Login</h2>
        <?php
        if ($isLoggedIn) {
            echo '<div class="status success">✓ You are logged in as admin</div>';
        } else {
            echo '<div class="status warning">⚠ You are not logged in<br>';
            echo '<a href="login.php" class="button">Go to Login</a>';
            echo '</div>';
        }
        ?>

        <!-- Next Steps -->
        <h2>7. Next Steps</h2>
        <?php
        if ($totalCount === 0) {
            echo '<div class="status warning"><strong>Action Required:</strong><br>';
            echo '1. Import sample data using: <code>mysql -u root -p yourdb &lt; src/sql/sample_data.sql</code><br>';
            echo '2. Or run the full schema first: <code>mysql -u root -p yourdb &lt; src/sql/full_schema.sql</code><br>';
            echo '3. Then import data: <code>mysql -u root -p yourdb &lt; src/sql/sample_data.sql</code>';
            echo '</div>';
        } else {
            echo '<div class="status success"><strong>Database looks good!</strong><br>';
            echo 'You can now go to <a href="appointments.php">Appointments</a> and test fetching appointments.';
            echo '</div>';
        }
        ?>

        <!-- Test API -->
        <h2>8. Test fetch-appointments.php API</h2>
        <p>Use these URLs to test the API (requires login):</p>
        <ul>
            <li><code>admin/fetch-appointments.php?status=pending</code></li>
            <li><code>admin/fetch-appointments.php?status=scheduled</code></li>
            <li><code>admin/fetch-appointments.php?status=completed</code></li>
            <li><code>admin/fetch-appointments.php?date=2026-04-05&status=scheduled</code></li>
        </ul>
        <p style="color: #666; font-size: 0.9em;">Note: These endpoints require an active session (logged in to admin
            panel)</p>

        <a href="appointments.php" class="button">Back to Appointments</a>
    </div>
</body>

</html>