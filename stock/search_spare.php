<?php
require_once("../config/db.php");

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only allow ADMIN to use stock-related AJAX search
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

$term = isset($_GET['term']) ? trim($_GET['term']) : '';

if ($term === '') {
    echo json_encode([]);
    exit;
}

$term = mysqli_real_escape_string($conn, $term);

$query = "SELECT 
            id,
            spareName,
            partNo,
            rackNumber,
            picture
          FROM spares
          WHERE spareName LIKE '%$term%' 
             OR partNo LIKE '%$term%'
          ORDER BY spareName ASC
          LIMIT 10";

$res = mysqli_query($conn, $query);

$data = [];

if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {

        // Fix: use 'picture' not 'image'
        if (empty($row['picture'])) {
            $row['picture'] = "no-image.png";
        }

        $data[] = $row;
    }
}

echo json_encode($data);
exit;
?>