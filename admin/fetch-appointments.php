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

if (empty($date)) {
    $errors[] = 'Date parameter is required.';
} elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    // Validate date format (YYYY-MM-DD)
    $errors[] = 'Invalid date format. Expected YYYY-MM-DD.';
} else {
    // Validate that the date is a valid calendar date
    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
        $errors[] = 'Invalid date. Please provide a valid calendar date.';
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

// Build query
$query = "SELECT id, name, email, phone, package, date, report FROM {$table['APPOINTMENT']} WHERE date = ?";
$params = [$date];
$paramTypes = 's';

// Add status filter if provided
if (!empty($status)) {
    if (strtolower($status) === 'pending') {
        $query .= ' AND (report IS NULL OR report = "") AND (date IS NULL OR date = "")';
    } elseif (strtolower($status) === 'scheduled') {
        $query .= ' AND (date IS NOT NULL AND date != "") AND (report IS NULL OR report = "")';
    } elseif (strtolower($status) === 'completed') {
        $query .= ' AND report IS NOT NULL AND report != ""';
    }
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
$countQuery = "SELECT COUNT(*) as total FROM {$table['APPOINTMENT']} WHERE date = ?";
$countParams = [$date];
$countParamTypes = 's';

if (!empty($status)) {
    if (strtolower($status) === 'pending') {
        $countQuery .= ' AND (report IS NULL OR report = "") AND (date IS NULL OR date = "")';
    } elseif (strtolower($status) === 'scheduled') {
        $countQuery .= ' AND (date IS NOT NULL AND date != "") AND (report IS NULL OR report = "")';
    } elseif (strtolower($status) === 'completed') {
        $countQuery .= ' AND report IS NOT NULL AND report != ""';
    }
}

$countStmt = $conn->prepare($countQuery);
if ($countStmt) {
    $countStmt->bind_param($countParamTypes, ...$countParams);
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

