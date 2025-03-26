<?php
require '../src/db.php';
session_start();

if (!isset($_POST['appointment_id']) || !isset($_FILES['report_image'])) {
    die("Invalid request.");
}

$appointmentId = $_POST['appointment_id'];

// Process the uploaded files
if (isset($_FILES['report_image']) && count($_FILES['report_image']['name']) > 0) {
    $uploadedFilePaths = [];

    // Loop through each file
    for ($i = 0; $i < count($_FILES['report_image']['name']); $i++) {
        // Check for any errors during upload
        if ($_FILES['report_image']['error'][$i] == UPLOAD_ERR_OK) {
            $tmpName = $_FILES['report_image']['tmp_name'][$i];
            $fileName = basename($_FILES['report_image']['name'][$i]);
            // Generate a unique file name to avoid conflicts
            $newFileName = uniqid() . "_" . $fileName;
            $uploadDir = "../uploads/";  // Make sure this directory exists and is writable
            $uploadFile = $uploadDir . $newFileName;

            if (move_uploaded_file($tmpName, $uploadFile)) {
                // Add the uploaded file path to the array
                $uploadedFilePaths[] = $newFileName;
            } else {
                echo "File upload failed for file: $fileName";
                continue;
            }
        } else {
            echo "Error uploading file: " . $_FILES['report_image']['name'][$i];
        }
    }

    // If we have uploaded files, update the appointment record
    if (count($uploadedFilePaths) > 0) {
        // Store the file paths as a comma-separated string
        $dbFilePaths = implode(", ", $uploadedFilePaths);

        $sql = "UPDATE $table[APPOINTMENT] SET report = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $dbFilePaths, $appointmentId);
        $stmt->execute();
        $stmt->close();

        header("Location: reports.php");
        exit;
    } else {
        echo "No files were uploaded.";
    }
} else {
    echo "Error: No files were selected.";
}
?>
