<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "ecommerce";

// MySQLi connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Logging the error for easier debugging (in production, you can log to a file)
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

// Optionally set the character set to UTF-8
$conn->set_charset("utf8");
?>
