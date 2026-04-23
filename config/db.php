<?php
$conn = mysqli_connect("localhost", "root", "", "billing", 3306);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>