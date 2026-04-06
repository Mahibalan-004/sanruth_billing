<?php
require_once("../config/db.php");

$term = $_GET['term'];

$query = "SELECT * FROM spares 
WHERE spareName LIKE '%$term%' 
OR partNo LIKE '%$term%' 
LIMIT 10";

$res = mysqli_query($conn, $query);

$data = [];

while($row = mysqli_fetch_assoc($res)){
    $data[] = $row;
}

echo json_encode($data);    