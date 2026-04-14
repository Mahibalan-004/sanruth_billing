<?php
require_once("../config/db.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: create.php");
    exit;
}

/* ── Error helper ── */
function err($msg) {
    echo "<script>alert(" . json_encode("❌ " . $msg) . "); window.history.back();</script>";
    exit;
}

/* ══════════════════════════════════════
   1. COLLECT POST DATA
══════════════════════════════════════ */
$orderNo     = trim($_POST['orderNo']     ?? '');
$orderStatus = trim($_POST['orderStatus'] ?? 'New');
$orderDate   = trim($_POST['orderDate']   ?? date('Y-m-d'));

$existingCustId  = intval($_POST['customerId']      ?? 0);
$customerPhone   = trim($_POST['customerPhone']     ?? '');
$customerName    = trim($_POST['customerName']      ?? '');
$whatsApp        = trim($_POST['customerWhatsApp']  ?? '');
$addressLine1    = trim($_POST['addressLine1']      ?? '');
$city            = trim($_POST['city']              ?? '');

/* ══════════════════════════════════════
   2. VALIDATE
══════════════════════════════════════ */
if (empty($orderNo))       err("Order number is missing");
if (empty($orderDate))     err("Order date is required");
if (empty($customerPhone)) err("Customer phone is required");
if (empty($customerName))  err("Customer name is required");
if (empty($_POST['item'])) err("Please add at least one item");

// Make sure at least one item has a name
$hasItem = false;
foreach ($_POST['item'] as $itm) {
    if (trim($itm) !== '') { $hasItem = true; break; }
}
if (!$hasItem) err("Please add at least one item");

/* ══════════════════════════════════════
   3. FIND OR CREATE CUSTOMER
══════════════════════════════════════ */
$customerId = null;

if ($existingCustId > 0) {
    // Verify the customer exists
    $chk = mysqli_prepare($conn, "SELECT id FROM customer WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($chk, "i", $existingCustId);
    mysqli_stmt_execute($chk);
    $chkRes = mysqli_stmt_get_result($chk);
    if ($chkRes && mysqli_num_rows($chkRes) > 0) {
        $customerId = $existingCustId;
        // Update customer info
        $upd = mysqli_prepare($conn, "UPDATE customer SET name=?, whatsApp=?, addressLine1=?, city=?, phoneNo1=? WHERE id=?");
        mysqli_stmt_bind_param($upd, "sssssi", $customerName, $whatsApp, $addressLine1, $city, $customerPhone, $customerId);
        mysqli_stmt_execute($upd);
    }
}

if (!$customerId) {
    // Search by phone
    $stmt = mysqli_prepare($conn, "SELECT id FROM customer WHERE phoneNo1 = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $customerPhone);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($res)) {
        $customerId = $row['id'];
        $upd = mysqli_prepare($conn, "UPDATE customer SET name=?, whatsApp=?, addressLine1=?, city=? WHERE id=?");
        mysqli_stmt_bind_param($upd, "ssssi", $customerName, $whatsApp, $addressLine1, $city, $customerId);
        mysqli_stmt_execute($upd);
    } else {
        // Create new customer
        $ins = mysqli_prepare($conn, "INSERT INTO customer (active, name, phoneNo1, whatsApp, addressLine1, city) VALUES (1,?,?,?,?,?)");
        mysqli_stmt_bind_param($ins, "sssss", $customerName, $customerPhone, $whatsApp, $addressLine1, $city);
        mysqli_stmt_execute($ins);
        $customerId = mysqli_insert_id($conn);
        if (!$customerId) err("Failed to create customer");
    }
}

/* ══════════════════════════════════════
   4. TRANSACTION
══════════════════════════════════════ */
$now       = date('Y-m-d H:i:s.') . str_pad(rand(0,999999), 6, '0', STR_PAD_LEFT); // datetime(6)
$createdBy = "System Admin";

mysqli_begin_transaction($conn);

try {

    /* 4a. Insert sales header
       Columns: orderNo, orderDate, orderStatus, customer,
                actualAmountSum, paidAmountSum,
                createdBy, createdOn, modifiedBy, modifiedOn
    */
    $stmt = mysqli_prepare($conn, "
        INSERT INTO sales
            (orderNo, orderDate, orderStatus, customer,
             actualAmountSum, paidAmountSum,
             createdBy, createdOn, modifiedBy, modifiedOn)
        VALUES (?, ?, ?, ?, 0, 0, ?, ?, ?, ?)
    ");
    // s s s i d d s s s s — but actualAmountSum/paidAmountSum are 0 so skip, just s s s i s s s s
    mysqli_stmt_bind_param($stmt, "ssisssss",
        $orderNo, $orderDate, $orderStatus, $customerId,
        $createdBy, $now, $createdBy, $now
    );
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Sales insert failed: " . mysqli_stmt_error($stmt));
    }
    $salesId = mysqli_insert_id($conn);
    if (!$salesId) throw new Exception("Could not get sales ID");

    /* 4b. Insert sales items
       salesitems columns:
         createdBy, createdOn, modifiedBy, modifiedOn,
         deleted, gstPercentage, gstValue, itemName,
         pricePerQty, quantity, serialNo, totalPrice,
         machine, spare, stock, sales
    */
    $actualSum = 0;

    $iStmt = mysqli_prepare($conn, "
        INSERT INTO salesitems
            (createdBy, createdOn, modifiedBy, modifiedOn,
             deleted,
             gstPercentage, gstValue, itemName,
             pricePerQty, quantity, serialNo, totalPrice,
             machine, spare, stock, sales)
        VALUES
            (?, ?, ?, ?,
             b'0',
             ?, ?, ?,
             ?, ?, ?, ?,
             NULL, ?, ?, ?)
    ");
    // types: s s s s  d d s  d i s d  i s i
    // = "ssss" + "dds" + "disd" + "isi" = "ssssddsdisd" + "isi"
    // total 14 params (excluding b'0' which is literal)

    foreach ($_POST['item'] as $key => $itemName) {
        $itemName = trim($itemName);
        if ($itemName === '') continue;

        $stockId  = trim($_POST['stockId'][$key]  ?? '');
        $spareId  = intval($_POST['spareId'][$key] ?? 0) ?: NULL;
        $serial   = trim($_POST['serial'][$key]   ?? '') ?: NULL;
        $qty      = max(1, intval($_POST['qty'][$key] ?? 1));
        $price    = floatval($_POST['price'][$key] ?? 0);
        $gst      = floatval($_POST['gst'][$key]   ?? 0);

        // Recalculate server-side
        $subtotal  = $qty * $price;
        $gstValue  = round($subtotal * $gst / 100, 4);
        $rowTotal  = round($subtotal + $gstValue, 4);

        $stockIdVal = ($stockId !== '') ? $stockId : NULL;

        mysqli_stmt_bind_param($iStmt, "ssssddsdisdssi",
            $createdBy, $now, $createdBy, $now,   // s s s s
            $gst, $gstValue, $itemName,             // d d s
            $price, $qty, $serial, $rowTotal,       // d i s d
            $spareId, $stockIdVal, $salesId         // i s i
        );

        if (!mysqli_stmt_execute($iStmt)) {
            throw new Exception("Item insert failed: " . mysqli_stmt_error($iStmt));
        }

        $actualSum += $rowTotal;

        // Deduct stock
        if ($stockId !== '') {
            $dq = mysqli_prepare($conn, "UPDATE stock SET availableQty = GREATEST(0, availableQty - ?) WHERE id = ?");
            mysqli_stmt_bind_param($dq, "is", $qty, $stockId);
            mysqli_stmt_execute($dq);
        }
    }

    /* 4c. Payments — store in sales_payments if table exists */
    $paidSum = 0;
    $hasPmtTable = mysqli_query($conn, "SHOW TABLES LIKE 'sales_payments'");

    if ($hasPmtTable && mysqli_num_rows($hasPmtTable) > 0 && !empty($_POST['paymentAmount'])) {
        $pStmt = mysqli_prepare($conn, "
            INSERT INTO sales_payments (salesId, paymentDate, paymentMode, referenceNo, amount)
            VALUES (?, ?, ?, ?, ?)
        ");
        foreach ($_POST['paymentAmount'] as $key => $amt) {
            $amt = floatval($amt);
            if ($amt <= 0) continue;
            $pDate = trim($_POST['paymentDate'][$key] ?? date('Y-m-d'));
            $pMode = trim($_POST['paymentMode'][$key] ?? 'Cash');
            $pRef  = trim($_POST['paymentRef'][$key]  ?? '');
            mysqli_stmt_bind_param($pStmt, "isssd", $salesId, $pDate, $pMode, $pRef, $amt);
            if (!mysqli_stmt_execute($pStmt)) {
                throw new Exception("Payment insert failed: " . mysqli_stmt_error($pStmt));
            }
            $paidSum += $amt;
        }
    } else {
        // Just sum from form for totals update
        foreach ($_POST['paymentAmount'] ?? [] as $amt) {
            $paidSum += floatval($amt);
        }
    }

    /* 4d. Update sales totals */
    $upd = mysqli_prepare($conn, "UPDATE sales SET actualAmountSum = ?, paidAmountSum = ? WHERE id = ?");
    mysqli_stmt_bind_param($upd, "ddi", $actualSum, $paidSum, $salesId);
    if (!mysqli_stmt_execute($upd)) {
        throw new Exception("Sales total update failed: " . mysqli_stmt_error($upd));
    }

    mysqli_commit($conn);
    echo "<script>alert('✅ Sales Order #" . addslashes($orderNo) . " created successfully!'); window.location.href='list.php';</script>";

} catch (Exception $e) {
    mysqli_rollback($conn);
    err($e->getMessage());
}
?>