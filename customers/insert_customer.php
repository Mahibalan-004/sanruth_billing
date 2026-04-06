<?php 
require_once("../config/db.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect Address Data
    $line1 = mysqli_real_escape_string($conn, $_POST['line1']);
    $line2 = mysqli_real_escape_string($conn, $_POST['line2']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $zipCode = mysqli_real_escape_string($conn, $_POST['zipCode']);
    $createdOn = date('Y-m-d H:i:s');
    $createdBy = "System Admin"; // Normally from session
    
    // Insert Address
    $addressQuery = "INSERT INTO address (line1, line2, city, zipCode, createdOn, createdBy, modifiedOn, modifiedBy) 
                     VALUES ('$line1', '$line2', '$city', '$zipCode', '$createdOn', '$createdBy', '$createdOn', '$createdBy')";
    
    if (mysqli_query($conn, $addressQuery)) {
        $addressId = mysqli_insert_id($conn);
        
        // Collect Customer Data
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $customerId = mysqli_real_escape_string($conn, $_POST['customerId']);
        $phoneNo1 = mysqli_real_escape_string($conn, $_POST['phoneNo1']);
        $whatsAppNo = mysqli_real_escape_string($conn, $_POST['whatsAppNo']);
        $emailId = mysqli_real_escape_string($conn, $_POST['emailId']);
        $active = isset($_POST['active']) ? 1 : 0;
        
        // Insert Customer
        $customerQuery = "INSERT INTO customer (active, customerId, emailId, name, phoneNo1, phoneNo2, whatsAppNo, address) 
                          VALUES ($active, '$customerId', '$emailId', '$name', '$phoneNo1', '', '$whatsAppNo', $addressId)";
        
        if (mysqli_query($conn, $customerQuery)) {
            echo "<script>alert('Customer Added Successfully!'); window.location.href='c_list.php';</script>";
        } else {
            echo "Error adding customer: " . mysqli_error($conn);
        }
    } else {
        echo "Error adding address: " . mysqli_error($conn);
    }
} else {
    header("Location: add_customer.php");
    exit();
}
?>
