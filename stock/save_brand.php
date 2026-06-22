<?php
require_once("../config/db.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only allow ADMIN to add brands
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$name = $_POST['name'] ?? '';
$name = mysqli_real_escape_string($conn, $name);
mysqli_query($conn, "INSERT INTO brand(brandName) VALUES('$name')");
echo json_encode(['success' => true]);
?>