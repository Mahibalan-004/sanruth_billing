<?php 
require_once("../config/db.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect Sales Order Header Info
    $orderNo = mysqli_real_escape_string($conn, $_POST['orderNo']);
    $orderStatus = mysqli_real_escape_string($conn, $_POST['orderStatus']);
    $orderDate = mysqli_real_escape_string($conn, $_POST['orderDate']);
    $customerPhone = mysqli_real_escape_string($conn, $_POST['customerPhone']);
    $customerName = mysqli_real_escape_string($conn, $_POST['customerName']);
    
    // Find or Create Customer
    $customerId = null;
    $res = mysqli_query($conn, "SELECT id FROM customer WHERE phoneNo1 = '$customerPhone' LIMIT 1");
    if($row = mysqli_fetch_assoc($res)) {
        $customerId = $row['id'];
    } else {
        // Create basic customer if not found
        $nowC = date('Y-m-d H:i:s');
        mysqli_query($conn, "INSERT INTO customer (active, name, phoneNo1) VALUES (1, '$customerName', '$customerPhone')");
        $customerId = mysqli_insert_id($conn);
    }
    
    // Initial Totals (Calculated from rows)
    $actualAmountSum = 0;
    $paidAmountSum = 0;
    
    $createdOn = date('Y-m-d H:i:s');
    $createdBy = "System Admin";

    // Start Transaction
    mysqli_begin_transaction($conn);

    try {
        // 1. Insert into sales table (using user's specific schema)
        $salesSql = "INSERT INTO sales (orderNo, orderDate, orderStatus, customer, actualAmountSum, paidAmountSum, createdBy, createdOn, modifiedBy, modifiedOn) 
                     VALUES ('$orderNo', '$orderDate', '$orderStatus', $customerId, 0, 0, '$createdBy', '$createdOn', '$createdBy', '$createdOn')";
        
        mysqli_query($conn, $salesSql);
        $salesId = mysqli_insert_id($conn);

        // 2. Insert into sales_items table
        if (isset($_POST['item'])) {
            foreach ($_POST['item'] as $key => $itemName) {
                if (empty($itemName)) continue;
                
                $barcode = mysqli_real_escape_string($conn, $_POST['barcode'][$key]);
                $serial = mysqli_real_escape_string($conn, $_POST['serial'][$key]);
                $rack = mysqli_real_escape_string($conn, $_POST['rack'][$key]);
                $qty = (int)$_POST['qty'][$key];
                $price = (float)$_POST['price'][$key];
                $gst = (float)$_POST['gst'][$key];
                $rowTotal = (float)$_POST['total'][$key];
                
                $itemSql = "INSERT INTO sales_items (salesId, itemName, barcode, serialNo, rackNo, quantity, pricePerQty, gstPercentage, totalPrice) 
                            VALUES ($salesId, '$itemName', '$barcode', '$serial', '$rack', $qty, $price, $gst, $rowTotal)";
                mysqli_query($conn, $itemSql);
                $actualAmountSum += $rowTotal;
            }
        }

        // 3. Insert into sales_payments table
        if (isset($_POST['paymentAmount'])) {
            foreach ($_POST['paymentAmount'] as $key => $amt) {
                $amt = (float)$amt;
                if ($amt <= 0) continue;
                
                $pDate = mysqli_real_escape_string($conn, $_POST['paymentDate'][$key]);
                $pMode = mysqli_real_escape_string($conn, $_POST['paymentMode'][$key]);
                $pRef = mysqli_real_escape_string($conn, $_POST['paymentRef'][$key]);
                
                $paymentSql = "INSERT INTO sales_payments (salesId, paymentDate, paymentMode, referenceNo, amount) 
                               VALUES ($salesId, '$pDate', '$pMode', '$pRef', $amt)";
                mysqli_query($conn, $paymentSql);
                $paidAmountSum += $amt;
            }
        }

        // 4. Update sales table with final totals
        $updateSql = "UPDATE sales SET actualAmountSum = $actualAmountSum, paidAmountSum = $paidAmountSum WHERE id = $salesId";
        mysqli_query($conn, $updateSql);

        // Commit Transaction
        mysqli_commit($conn);
        echo "<script>alert('Sales Order Created Successfully!'); window.location.href='list.php';</script>";

    } catch (Exception $e) {
        // Rollback Transaction on error
        mysqli_rollback($conn);
        echo "Error creating sales order: " . $e->getMessage();
    }

} else {
    header("Location: create.php");
    exit();
}
?>
