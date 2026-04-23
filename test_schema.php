<?php
require_once("config/db.php");
$res = mysqli_query($conn, "SHOW COLUMNS FROM customer");
while($row = mysqli_fetch_assoc($res)){
    print_r($row);
}
?>
