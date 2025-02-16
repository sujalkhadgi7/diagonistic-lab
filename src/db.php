<?php

require("../src/constants/index.php");


echo "<script>console.log('Debug Objects: " . { "table": $table, "DIR": __DIR__} . "' );</script>";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


?>

