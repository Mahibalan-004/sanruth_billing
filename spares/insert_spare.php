<?php
require_once("../config/db.php");

$spareName = $_POST['spareName'];
$partNo = $_POST['partNo'];
$rack = $_POST['rackNumber'];
$active = isset($_POST['active']) ? 1 : 0;

$image = NULL;

if(!empty($_FILES['image']['name'])){
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $image = $uploadDir . time() . "_" . $_FILES['image']['name'];
    move_uploaded_file($_FILES['image']['tmp_name'], $image);
} elseif (!empty($_POST['camera_image'])) {
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    
    $data = $_POST['camera_image'];
    // Remove "data:image/png;base64," prefix
    $data = str_replace('data:image/png;base64,', '', $data);
    $data = str_replace(' ', '+', $data);
    $imageData = base64_decode($data);
    
    $fileName = "cam_" . time() . ".png";
    $image = $uploadDir . $fileName;
    file_put_contents($image, $imageData);
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