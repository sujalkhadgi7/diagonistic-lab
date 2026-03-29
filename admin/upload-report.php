<?php
require_once '../src/db.php';
session_start();

// Validate session
if (!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn']) {
    header('Location: login.php');
    exit;
}

// Configuration
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf']);
define('ALLOWED_MIMES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf']);
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10 MB
define('UPLOAD_DIR', realpath(__DIR__ . '/../uploads'));
define('REDIRECT_PAGE', 'reports.php');

$errors = [];
$uploadedFiles = [];

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_error'] = 'Invalid request method.';
    header('Location: ' . REDIRECT_PAGE);
    exit;
}

// Validate appointment ID
$appointmentId = isset($_POST['appointment_id']) ? (int) $_POST['appointment_id'] : 0;
if ($appointmentId <= 0) {
    $_SESSION['flash_error'] = 'Invalid appointment ID.';
    header('Location: ' . REDIRECT_PAGE);
    exit;
}

// Verify appointment exists
$checkSql = "SELECT id FROM {$table['APPOINTMENT']} WHERE id = ?";
$checkStmt = $conn->prepare($checkSql);
if (!$checkStmt) {
    $_SESSION['flash_error'] = 'Database error. Please try again.';
    header('Location: ' . REDIRECT_PAGE);
    exit;
}
$checkStmt->bind_param('i', $appointmentId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
if ($checkResult->num_rows === 0) {
    $_SESSION['flash_error'] = 'Appointment not found.';
    $checkStmt->close();
    header('Location: ' . REDIRECT_PAGE);
    exit;
}
$checkStmt->close();

// Validate file uploads
if (!isset($_FILES['report_image']) || empty($_FILES['report_image']['name'][0])) {
    $_SESSION['flash_error'] = 'No files selected. Please choose at least one file.';
    header('Location: ' . REDIRECT_PAGE);
    exit;
}

// Create uploads directory if it doesn't exist
if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0755, true)) {
    $_SESSION['flash_error'] = 'Upload directory could not be created.';
    header('Location: ' . REDIRECT_PAGE);
    exit;
}

// Process each uploaded file
$fileCount = count($_FILES['report_image']['name']);
for ($i = 0; $i < $fileCount; $i++) {
    $error = $_FILES['report_image']['error'][$i];
    $fileName = trim($_FILES['report_image']['name'][$i]);
    $tmpName = $_FILES['report_image']['tmp_name'][$i];
    $fileSize = $_FILES['report_image']['size'][$i];

    // Skip empty files
    if (empty($fileName)) {
        continue;
    }

    // Check for upload errors
    if ($error !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize.',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form MAX_FILE_SIZE.',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.',
        ];
        $errors[] = htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') . ': ' . ($errorMessages[$error] ?? 'Unknown error occurred.');
        continue;
    }

    // Validate file size
    if ($fileSize > MAX_FILE_SIZE) {
        $errors[] = htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') . ': File exceeds maximum size of 10 MB.';
        continue;
    }

    // Extract and validate file extension
    $fileInfo = pathinfo($fileName);
    $extension = strtolower($fileInfo['extension'] ?? '');

    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        $errors[] = htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') . ': File type not allowed. Allowed types: ' . implode(', ', ALLOWED_EXTENSIONS);
        continue;
    }

    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tmpName);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_MIMES)) {
        $errors[] = htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') . ': Invalid file MIME type.';
        continue;
    }

    // Sanitize and create unique filename
    $baseNameSanitized = preg_replace('/[^a-z0-9_.-]/i', '_', $fileInfo['filename']);
    $baseNameSanitized = preg_replace('/_+/', '_', $baseNameSanitized);
    $baseNameSanitized = trim($baseNameSanitized, '_');
    $newFileName = uniqid('report_', true) . '_' . $baseNameSanitized . '.' . $extension;
    $uploadPath = UPLOAD_DIR . '/' . $newFileName;

    // Move uploaded file
    if (move_uploaded_file($tmpName, $uploadPath)) {
        // @codingStandardsIgnoreLine - 0644 is standard for readable uploaded files
        chmod($uploadPath, 0644);
        $uploadedFiles[] = $newFileName;
    } else {
        $errors[] = htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') . ': Failed to save file to server.';
    }
}

// Handle errors
if (!empty($errors) && empty($uploadedFiles)) {
    $_SESSION['flash_error'] = 'No files were successfully uploaded. ' . implode(' ', $errors);
    header('Location: ' . REDIRECT_PAGE);
    exit;
}

// Update database if files were uploaded
if (!empty($uploadedFiles)) {
    // Get existing reports
    $getSql = "SELECT report FROM {$table['APPOINTMENT']} WHERE id = ?";
    $getStmt = $conn->prepare($getSql);
    $getStmt->bind_param('i', $appointmentId);
    $getStmt->execute();
    $getResult = $getStmt->get_result();
    $getRow = $getResult->fetch_assoc();
    $getStmt->close();

    // Merge with existing reports
    $existingReports = $getRow['report'] ? trim($getRow['report']) : '';
    $allReports = array_filter(array_merge(
        $existingReports ? explode(',', $existingReports) : [],
        $uploadedFiles
    ));
    $allReports = array_map('trim', array_unique($allReports));
    $reportString = implode(', ', $allReports);

    // Update appointment
    $updateSql = "UPDATE {$table['APPOINTMENT']} SET report = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    if (!$updateStmt) {
        $_SESSION['flash_error'] = 'Database error during update.';
        header('Location: ' . REDIRECT_PAGE);
        exit;
    }

    $updateStmt->bind_param('si', $reportString, $appointmentId);
    if ($updateStmt->execute()) {
        $successCount = count($uploadedFiles);
        $partialMsg = !empty($errors) ? ' ' . count($errors) . ' file(s) failed.' : '';
        $_SESSION['flash_success'] = $successCount . ' report file(s) uploaded successfully.' . $partialMsg;
    } else {
        $_SESSION['flash_error'] = 'Failed to update appointment with uploaded files.';
    }
    $updateStmt->close();
}

header('Location: ' . REDIRECT_PAGE);
exit;


