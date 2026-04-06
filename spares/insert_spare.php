<?php
require_once("../config/db.php");

$spareName = $_POST['spareName'];
$partNo = $_POST['partNo'];
$rack = $_POST['rackNumber'];
$active = isset($_POST['active']) ? 1 : 0;

$image = NULL;

if(!empty($_FILES['image']['name'])){
    $image = "uploads/" . time() . $_FILES['image']['name'];
    move_uploaded_file($_FILES['image']['tmp_name'], $image);
}

$query = "INSERT INTO spares(
spareName, partNo, rackNumber, active, picture
) VALUES (
'$spareName','$partNo','$rack',b'$active','$image'
)";

if(mysqli_query($conn,$query)){
    echo "<script>alert('Spare Added Successfully'); window.location='add_spare.php';</script>";
}else{
    echo mysqli_error($conn);
}
?>