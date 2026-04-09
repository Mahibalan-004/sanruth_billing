<?php 
require_once("../config/db.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Basic Transaction Logic for Job Card
    $cardNo = mysqli_real_escape_string($conn, $_POST['cardNo']);
    $givenDate = mysqli_real_escape_string($conn, $_POST['givenDate']);
    $customerPhone = mysqli_real_escape_string($conn, $_POST['customerPhone']);
    $customerName = mysqli_real_escape_string($conn, $_POST['customerName']);
    
    // Logic for Customer finding/creation
    $customerId = null;
    $res = mysqli_query($conn, "SELECT id FROM customer WHERE phoneNo1 = '$customerPhone' LIMIT 1");
    if($row = mysqli_fetch_assoc($res)) {
        $customerId = $row['id'];
    } else {
        mysqli_query($conn, "INSERT INTO customer (active, name, phoneNo1) VALUES (1, '$customerName', '$customerPhone')");
        $customerId = mysqli_insert_id($conn);
    }
    
    $now = date('Y-m-d H:i:s');
    $user = "System Admin";

    mysqli_begin_transaction($conn);

    try {
        // 1. Insert into jobcard table
        $jcSql = "INSERT INTO jobcard (cardNo, givenDate, customer, jobStatus, completed, delivered, actualAmountSum, quoteAmountSum, receivedAmountSum, laborCharge, createdBy, createdOn, modifiedBy, modifiedOn) 
                  VALUES ('$cardNo', '$givenDate', $customerId, 'New', 0, 0, 0, 0, 0, 0, '$user', '$now', '$user', '$now')";
        
        mysqli_query($conn, $jcSql);
        $jobcardId = mysqli_insert_id($conn);

        // 2. Insert into jobcard_items table
        $mId = (int)$_POST['machine'];
        $serial = mysqli_real_escape_string($conn, $_POST['serial']);
        $wDetails = mysqli_real_escape_string($conn, $_POST['workDetails']);
        $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
        
        $itemSql = "INSERT INTO jobcarditems (id, machineId, serialNo, workDetails, remarks) 
                    VALUES ($jobcardId, $mId, '$serial', '$wDetails', '$remarks')";
        mysqli_query($conn, $itemSql);

        mysqli_commit($conn);
        echo "<script>alert('Job Card Created successfully!'); window.location.href='create.php';</script>";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "Error saving job card: " . $e->getMessage();
    }

} else {
    header("Location: create.php");
}
?>
