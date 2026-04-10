<?php
require_once("../config/db.php");

// ================= RESPONSE HELPER =================
function error($msg) {
    echo "<script>alert('$msg'); window.history.back();</script>";
    exit;
}

// ================= GET ALL POST VALUES =================
$spare            = trim($_POST['spare']              ?? '');
$itemName         = trim($_POST['itemName']           ?? '');
$brand            = trim($_POST['brand']              ?? '');
$model            = trim($_POST['model']              ?? '');
$machine          = trim($_POST['machine']            ?? '');
$unit             = intval($_POST['unit']             ?? 0);   // ✅ int(11) in DB
$serialNo         = trim($_POST['serialNo']           ?? '');
$purchaseItem     = intval($_POST['purchaseItem']     ?? 0);   // bigint(20)
$warrantyInMonths = intval($_POST['warrantyInMonths'] ?? 0);
$selled           = isset($_POST['selled']) ? 1 : 0;          // bit(1)

$quantity         = intval($_POST['quantity']         ?? 0);
$availableQty     = intval($_POST['availableQty']     ?? 0);

$actualQty        = floatval($_POST['actualPricePerQty']    ?? 0);
$actualUnit       = floatval($_POST['actualPricePerUnit']   ?? 0);
$sellingQty       = floatval($_POST['sellingPricePerQty']   ?? 0);
$sellingUnit      = floatval($_POST['sellingPricePerUnit']  ?? 0);
$selledUnit       = floatval($_POST['selledPricePerUnit']   ?? 0);
$gstPercentage    = floatval($_POST['gstPercentage']        ?? 0);

// ================= VALIDATION =================
if (empty($spare)) {
    error("❌ Please select a spare / item properly");
}

if (empty($itemName)) {
    error("❌ Item name is required");
}

if ($quantity <= 0) {
    error("❌ Quantity must be greater than 0");
}

// ================= CHECK SPARE EXISTS =================
$spareVal = intval($spare);
$stmt = mysqli_prepare($conn, "SELECT id FROM spares WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $spareVal);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($res) == 0) {
    error("❌ Invalid spare selected. Please search and select again.");
}

// ================= UNIQUE BARCODE GENERATION =================
do {
    $barcode = rand(10000000, 99999999);
    $chk     = mysqli_prepare($conn, "SELECT id FROM stock WHERE barCode = ?");
    mysqli_stmt_bind_param($chk, "s", $barcode);
    mysqli_stmt_execute($chk);
    $chkRes  = mysqli_stmt_get_result($chk);
} while (mysqli_num_rows($chkRes) > 0);

// ================= GENERATE UNIQUE ID =================
$id = uniqid("STK");

// ================= NULL HELPERS =================
// For nullable bigint FK columns — empty selection stays NULL
$brandVal    = ($brand   !== '' && $brand   !== '0') ? intval($brand)   : NULL;
$modelVal    = ($model   !== '' && $model   !== '0') ? intval($model)   : NULL;
$machineVal  = ($machine !== '' && $machine !== '0') ? intval($machine) : NULL;
$purchaseVal = ($purchaseItem > 0)                   ? $purchaseItem    : NULL;
$serialVal   = ($serialNo !== '')                    ? $serialNo        : NULL;

// ================= INSERT (20 columns — NO image) =================
$sql = "
    INSERT INTO stock (
        id,
        spare,
        itemName,
        barCode,
        quantity,
        availableQty,
        actualPricePerQty,
        actualPricePerUnit,
        sellingPricePerQty,
        sellingPricePerUnit,
        selledPricePerUnit,
        gstPercentage,
        selled,
        brand,
        model,
        machine,
        unit,
        serialNo,
        warrantyInMonths,
        purchaseItem
    ) VALUES (
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?,
        ?, ?, ?,
        ?, ?, ?,
        ?, ?, ?, ?
    )
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    error("❌ Prepare failed: " . mysqli_error($conn));
}

// ── 20 params type string (NO spaces allowed in bind_param) ──
// s  id              varchar(255)
// i  spare           bigint(20)
// s  itemName        varchar(255)
// s  barCode         varchar(255)
// i  quantity        int(11)
// i  availableQty    int(11)
// d  actualPricePerQty     float
// d  actualPricePerUnit    float
// d  sellingPricePerQty    float
// d  sellingPricePerUnit   float
// d  selledPricePerUnit    double
// d  gstPercentage         float
// i  selled          bit(1)  → pass 0 or 1
// i  brand           bigint(20) nullable
// i  model           bigint(20) nullable
// i  machine         bigint(20) nullable
// i  unit            int(11)
// s  serialNo        varchar(255) nullable
// i  warrantyInMonths int(11)
// i  purchaseItem    bigint(20) nullable

mysqli_stmt_bind_param(
    $stmt,
    "sissiiddddddiiiiisii",
    $id,
    $spareVal,
    $itemName,
    $barcode,
    $quantity,
    $availableQty,
    $actualQty,
    $actualUnit,
    $sellingQty,
    $sellingUnit,
    $selledUnit,
    $gstPercentage,
    $selled,
    $brandVal,
    $modelVal,
    $machineVal,
    $unit,
    $serialVal,
    $warrantyInMonths,
    $purchaseVal
);

// ================= EXECUTE =================
if (mysqli_stmt_execute($stmt)) {
    header("Location: add_stock.php?success=1&barcode=" . $barcode);
    exit;
} else {
    error("❌ Database Error: " . mysqli_stmt_error($stmt));
}
?>