<?php
require '../src/db.php';
session_start();

if (!isset($_POST['appointment_id']) || !isset($_FILES['report_image'])) {
    die("Invalid request.");
}

$appointmentId = $_POST['appointment_id'];
// Process the uploaded file
if ($_FILES['report_image']['error'] == UPLOAD_ERR_OK) {
    $tmpName = $_FILES['report_image']['tmp_name'];
    $fileName = basename($_FILES['report_image']['name']);
    // You may want to create a unique file name to avoid collisions:
    $newFileName = uniqid() . "_" . $fileName;
    $uploadDir = "../uploads/";  // Make sure this directory exists and is writable
    $uploadFile = $uploadDir . $newFileName;


    if (move_uploaded_file($tmpName, $uploadFile)) {
        // Update the appointment record with the new file path
        $sql = "UPDATE $table[APPOINTMENT] SET report = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $dbFilePath = $newFileName;
        $stmt->bind_param("si", $dbFilePath, $appointmentId);
        $stmt->execute();
        $stmt->close();
        header("Location: reports.php");
        exit;
    } else {
        echo "File upload failed.";
    }
} else {
    echo "Error uploading file.";
}
?>