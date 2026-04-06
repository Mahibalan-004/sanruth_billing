<?php
require_once("../config/db.php");

// ================= VALIDATION =================
$spare = $_POST['spare'] ?? '';
$quantity = $_POST['quantity'] ?? 0;
$availableQty = $_POST['availableQty'] ?? 0;

if(empty($spare)){
    die("❌ Please select spare properly");
}

// check spare exists (FK safety)
$check = mysqli_query($conn, "SELECT id FROM spares WHERE id='$spare'");
if(mysqli_num_rows($check) == 0){
    die("❌ Invalid spare selected");
}


// ================= BARCODE GENERATION =================
// ensure unique barcode (1000–9999)
do {
    $barcode = rand(1000, 9999);
    $res = mysqli_query($conn, "SELECT id FROM stock WHERE barCode='$barcode'");
} while(mysqli_num_rows($res) > 0);


// ================= INSERT =================
$id = uniqid("STK");

$query = "INSERT INTO stock(
    id, spare, barCode, quantity, availableQty,
    actualPricePerQty, actualPricePerUnit,
    sellingPricePerQty, sellingPricePerUnit,
    selledPricePerUnit, selled
) VALUES (
    '$id',
    '$spare',
    '$barcode',
    '$quantity',
    '$availableQty',
    '{$_POST['actualPricePerQty']}',
    '{$_POST['actualPricePerUnit']}',
    '{$_POST['sellingPricePerQty']}',
    '{$_POST['sellingPricePerUnit']}',
    '{$_POST['selledPricePerUnit']}',
    b'0'
)";


// ================= EXECUTION =================
if(mysqli_query($conn, $query)){

    // redirect same page with barcode
    header("Location: add_stock.php?success=1&barcode=".$barcode);
    exit;

}else{
    echo "❌ Error: " . mysqli_error($conn);
}
?>