<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "billing", 3306);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error( ));
}
?>