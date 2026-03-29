<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit'])) {
    http_response_code(405);
    exit('Method not allowed');
}

$patientName = trim($_POST['patient-name'] ?? '');
$patientEmail = trim($_POST['patient-email'] ?? '');
$packageName = trim($_POST['package-name'] ?? '');

if ($patientName === '' || $patientEmail === '' || $packageName === '') {
    http_response_code(400);
    exit('All fields are required.');
}

$stmt = $conn->prepare('INSERT INTO appointment (name, email, package, date) VALUES (?, ?, ?, NULL)');
if (!$stmt) {
    http_response_code(500);
    exit('Failed to prepare statement.');
}

$stmt->bind_param('sss', $patientName, $patientEmail, $packageName);

if (!$stmt->execute()) {
    $stmt->close();
    http_response_code(500);
    exit('Something went wrong while saving the appointment.');
}

$stmt->close();
header('Location: ../booking-success.php');
exit;
