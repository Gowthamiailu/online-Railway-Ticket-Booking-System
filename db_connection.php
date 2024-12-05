<?php
// Database configuration
$db_host = 'localhost';  // Usually 'localhost' for local development
$db_user = 'root';  // Your MySQL username
$db_pass = '';  // Your MySQL password
$db_name = 'test';  // Your database name

// Create a connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8 (optional, but recommended)
$conn->set_charset("utf8mb4");

// Uncomment the line below if you want to see MySQL errors (not recommended for production)
// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
