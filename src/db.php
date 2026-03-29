<?php

require_once(__DIR__ . "/constants/index.php");

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port ?? 3306);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

?>