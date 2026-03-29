<?php
require_once '../src/db.php';
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Validate session
if (!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.', 'data' => null]);
    exit;
}

// Get parameters
$date = isset($_GET['date']) ? trim($_GET['date']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 50;
$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

// Validate inputs
$errors = [];
$validDate = false;

// Date is optional, but if provided must be valid
if (!empty($date)) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        // Validate date format (YYYY-MM-DD)
        $errors[] = 'Invalid date format. Expected YYYY-MM-DD.';
    } else {
        // Validate that the date is a valid calendar date
        $dateObj = DateTime::createFromFormat('Y-m-d', $date);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
            $errors[] = 'Invalid date. Please provide a valid calendar date.';
        } else {
            $validDate = true;
        }
    }
}

// Validate limit and offset
if ($limit < 1 || $limit > 100) {
    $limit = 50;
}
if ($offset < 0) {
    $offset = 0;
}

// Validate status if provided
$validStatuses = ['pending', 'scheduled', 'completed'];
if (!empty($status) && !in_array(strtolower($status), $validStatuses)) {
    $errors[] = 'Invalid status. Allowed: pending, scheduled, completed.';
}

// Return errors if any
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => implode(' ', $errors), 'data' => null]);
    exit;
}

// Build query based on parameters
$baseQuery = "SELECT id, name, email, phone, package, date, report FROM {$table['APPOINTMENT']}";
$whereConditions = [];
$params = [];
$paramTypes = '';

// Add status filter if provided
if (!empty($status)) {
    $status = strtolower($status);
    if ($status === 'pending') {
        // Pending: has no date assigned yet
        $whereConditions[] = "(date IS NULL OR date = '')";
    } elseif ($status === 'scheduled') {
        // Scheduled: has date but no report
        $whereConditions[] = "(date IS NOT NULL AND date != '' AND (report IS NULL OR report = ''))";
    } elseif ($status === 'completed') {
        // Completed: has report
        $whereConditions[] = "(report IS NOT NULL AND report != '')";
    }
}

// Add date filter if provided and valid
if ($validDate && !empty($date)) {
    $whereConditions[] = "DATE(date) = ?";
    $params[] = $date;
    $paramTypes .= 's';
}

// Build the full query
$query = $baseQuery;
if (!empty($whereConditions)) {
    $query .= ' WHERE ' . implode(' AND ', $whereConditions);
}

$query .= ' ORDER BY date DESC, id DESC LIMIT ? OFFSET ?';
$params[] = $limit;
$params[] = $offset;
$paramTypes .= 'ii';

// Prepare and execute the query
$stmt = $conn->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error. Please try again.', 'data' => null]);
    exit;
}

$stmt->bind_param($paramTypes, ...$params);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to fetch appointments.', 'data' => null]);
    $stmt->close();
    exit;
}

$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch total count for pagination
$countQuery = $baseQuery;
if (!empty($whereConditions)) {
    $countQuery .= ' WHERE ' . implode(' AND ', $whereConditions);
}

$countStmt = $conn->prepare($countQuery);
if ($countStmt) {
    if (!empty($params) && count($params) > 2) {
        // Remove limit and offset from params for count query
        $countParams = array_slice($params, 0, count($params) - 2);
        $countParamTypes = substr($paramTypes, 0, -2);
        if (!empty($countParams)) {
            $countStmt->bind_param($countParamTypes, ...$countParams);
        }
    }
    $countStmt->execute();
    $totalCount = $countStmt->get_result()->fetch_assoc()['total'];
    $countStmt->close();
} else {
    $totalCount = count($appointments);
}

// Return success response
http_response_code(200);
echo json_encode([
    'success' => true,
    'error' => null,
    'data' => [
        'appointments' => $appointments,
        'pagination' => [
            'total' => $totalCount,
            'limit' => $limit,
            'offset' => $offset,
            'returned' => count($appointments)
        ]
    ]
]);

