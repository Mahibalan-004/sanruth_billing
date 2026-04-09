<?php
require_once("../config/db.php");

$term = $_GET['term'] ?? '';

$term = mysqli_real_escape_string($conn,$term);

$query = "
SELECT st.*, sp.spareName, sp.picture, sp.partNo, sp.rackNumber
FROM stock st
LEFT JOIN spares sp ON st.spare = sp.id
WHERE 
sp.spareName LIKE '%$term%' OR
st.barCode LIKE '%$term%' OR
sp.partNo LIKE '%$term%' OR
sp.rackNumber LIKE '%$term%'
LIMIT 10
";

$res = mysqli_query($conn,$query);

$data = [];

while($row = mysqli_fetch_assoc($res)){
    $data[] = $row;
}

echo json_encode($data);