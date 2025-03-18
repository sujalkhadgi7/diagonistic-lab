<?php
require '../src/db.php';

header("Content-Type: application/json");

if (isset($_GET['date']) && !empty($_GET['date'])) {
    $selectedDate = $_GET['date'];

    // Prepare and execute the query
    $query = $conn->prepare("SELECT * FROM $table[APPOINTMENT] WHERE date = ?");
    $query->bind_param("s", $selectedDate);
    $query->execute();
    
    $result = $query->get_result();
    $appointments = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode($appointments); // Send response in JSON format
} else {
    echo json_encode(["error" => "Invalid or missing date"]);
}

$query->close();
$conn->close();

?>