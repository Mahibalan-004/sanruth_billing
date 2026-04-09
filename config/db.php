<?php
$conn = mysqli_connect("localhost", "root","", "billing");

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
} else {
    // Connection successful
}
?>