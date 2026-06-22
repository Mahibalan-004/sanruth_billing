<?php
// Primary application DB
$conn = mysqli_connect("localhost", "root", "", "billing", 3306);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Separate connection for login DB if present; fall back to main connection
$conn_login = @mysqli_connect("localhost", "root", "", "billing_login", 3306);
if (!$conn_login) {
    $conn_login = $conn;
}
?>