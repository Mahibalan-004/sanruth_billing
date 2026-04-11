<?php 
require_once("../config/db.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $orderNo = mysqli_real_escape_string($conn, $_POST['orderNo']);
    $orderDate = mysqli_real_escape_string($conn, $_POST['orderDate']);
    $supplierId = (int)$_POST['supplier'];
    $orderStatus = mysqli_real_escape_string($conn, $_POST['orderStatus']);
    
    $quoteAmountSum = (float)$_POST['quoteAmountSum'];
    $actualAmountSum = (float)$_POST['actualAmountSum'];
    $paidAmountSum = (float)$_POST['paidAmountSum'];
    
    $createdOn = date('Y-m-d H:i:s');
    $createdBy = "System Admin"; // Should ideally come from $_SESSION['user_name']

    $query = "INSERT INTO purchase (
                orderNo, orderDate, supplier, orderStatus, 
                quoteAmountSum, actualAmountSum, paidAmountSum, 
                createdOn, createdBy, modifiedOn, modifiedBy
              ) VALUES (
                '$orderNo', '$orderDate', $supplierId, '$orderStatus', 
                $quoteAmountSum, $actualAmountSum, $paidAmountSum, 
                '$createdOn', '$createdBy', '$createdOn', '$createdBy'
              )";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Purchase added successfully!'); window.location.href='list.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    header("Location: create.php");
    exit();
}
?>
