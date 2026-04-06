<?php
require_once("../config/db.php");
$name=$_POST['name'];
mysqli_query($conn,"INSERT INTO brand(brandName) VALUES('$name')");
?>