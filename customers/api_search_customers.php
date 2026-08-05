<?php
// customers/api_search_customers.php
require_once(__DIR__ . "/../config/db.php");

header('Content-Type: application/json');

$query = trim($_GET['query'] ?? $_GET['q'] ?? $_GET['phone'] ?? '');

$where = "WHERE 1=1";
if ($query !== '') {
    $q = mysqli_real_escape_string($conn, $query);
    $where .= " AND (
        c.phoneNo1 LIKE '%$q%' OR 
        c.phoneNo2 LIKE '%$q%' OR 
        c.whatsAppNo LIKE '%$q%' OR 
        c.name LIKE '%$q%' OR 
        c.customerId LIKE '%$q%' OR 
        c.id = '$q'
    )";
}

$sql = "SELECT c.id, c.customerId, c.name, c.phoneNo1, c.phoneNo2, c.whatsAppNo, c.emailId,
               c.active, a.id AS address_id, a.line1, a.line2, a.city, a.zipCode
        FROM customer c
        LEFT JOIN address a ON c.address = a.id
        $where
        ORDER BY c.id DESC
        LIMIT 50";

$res = mysqli_query($conn, $sql);

if (!$res) {
    echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    exit;
}

$customers = [];
while ($row = mysqli_fetch_assoc($res)) {
    // Active bit field in mysql comes back as string or char
    $row['active'] = ($row['active'] == 1 || $row['active'] === "\x01" || $row['active'] === '1') ? 1 : 0;
    
    // Construct readable full address
    $addrParts = array_filter([$row['line1'], $row['line2'], $row['city'], $row['zipCode']], function($val) {
        return !empty(trim($val ?? ''));
    });
    $row['fullAddress'] = !empty($addrParts) ? implode(', ', $addrParts) : '-';
    
    $customers[] = $row;
}

echo json_encode(['success' => true, 'data' => $customers]);
exit;
?>
