<?php
// jobcard/update_jobcard.php
require_once(__DIR__ . "/../config/db.php");

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$jobcardId = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($jobcardId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid Job Card ID']);
    exit;
}

// Parse givenDate
$rawDate = trim($_POST['givenDate'] ?? '');
if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $rawDate, $dMatch)) {
    $givenDate = "{$dMatch[3]}-{$dMatch[2]}-{$dMatch[1]}";
} elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate)) {
    $givenDate = $rawDate;
} else {
    $givenDate = date('Y-m-d');
}
$givenDate = mysqli_real_escape_string($conn, $givenDate);

$customerId = isset($_POST['customerId']) ? intval($_POST['customerId']) : 0;
$customerPhone = mysqli_real_escape_string($conn, trim($_POST['customerPhone'] ?? ''));
$customerName = mysqli_real_escape_string($conn, trim($_POST['customerName'] ?? ''));
$customerCity = mysqli_real_escape_string($conn, trim($_POST['city'] ?? ''));

// Customer resolution
if ($customerId > 0) {
    $res = mysqli_query($conn, "SELECT id FROM customer WHERE id = $customerId LIMIT 1");
    if (!$res || mysqli_num_rows($res) === 0) {
        $customerId = 0;
    }
}

if ($customerId === 0 && !empty($customerPhone)) {
    $res = mysqli_query($conn, "SELECT id FROM customer WHERE phoneNo1 = '$customerPhone' LIMIT 1");
    if ($res && $row = mysqli_fetch_assoc($res)) {
        $customerId = (int)$row['id'];
    }
}

if ($customerId === 0 && (!empty($customerPhone) || !empty($customerName))) {
    $last_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT customerId FROM customer WHERE customerId LIKE 'C%' ORDER BY id DESC LIMIT 1"));
    $next_num = 1;
    if ($last_row && preg_match('/(\d+)$/', $last_row['customerId'], $m)) {
        $next_num = (int)$m[1] + 1;
    }
    $genCustId = 'C' . str_pad($next_num, 7, '0', STR_PAD_LEFT);

    $now = date('Y-m-d H:i:s');
    $addrSql = "INSERT INTO address (createdOn, modifiedOn, city) VALUES ('$now', '$now', '$customerCity')";
    mysqli_query($conn, $addrSql);
    $addrId = mysqli_insert_id($conn);

    $custSql = "INSERT INTO customer (customerId, active, name, phoneNo1, address) VALUES ('$genCustId', 1, '$customerName', '$customerPhone', $addrId)";
    mysqli_query($conn, $custSql);
    $customerId = (int)mysqli_insert_id($conn);
}

$now = date('Y-m-d H:i:s');
$user = $_SESSION['user_name'] ?? "System Admin";

mysqli_begin_transaction($conn);

try {
    // 1. RESTORE previous stock quantities allocated for this job card
    $oldSparesRes = mysqli_query($conn, "
        SELECT stock, quantity 
        FROM jobcarditemspares 
        WHERE (jobCardItem = $jobcardId OR jobCardItem IN (SELECT id FROM jobcarditems WHERE jobCard = $jobcardId))
          AND deleted = 0 AND stock IS NOT NULL AND stock != ''
    ");
    if ($oldSparesRes) {
        while ($oldSp = mysqli_fetch_assoc($oldSparesRes)) {
            $stId = mysqli_real_escape_string($conn, $oldSp['stock']);
            $qty = (int)$oldSp['quantity'];
            if ($qty > 0 && !empty($stId)) {
                mysqli_query($conn, "UPDATE stock SET availableQty = availableQty + $qty WHERE id = '$stId'");
            }
        }
    }

    // 2. VALIDATE stock for newly submitted spares
    $sparesToDeduct = [];
    if (!empty($_POST['spare_stock_id']) && is_array($_POST['spare_stock_id'])) {
        foreach ($_POST['spare_stock_id'] as $idx => $stId) {
            $stId = trim($stId);
            $qty = (int)($_POST['spare_qty'][$idx] ?? 0);
            if ($qty <= 0) continue;

            $sName = mysqli_real_escape_string($conn, $_POST['spare_name'][$idx] ?? 'Spare Part');
            
            if (!empty($stId)) {
                $checkStock = mysqli_query($conn, "SELECT availableQty, itemName FROM stock WHERE id = '$stId' LIMIT 1");
                if ($checkStock && $sRow = mysqli_fetch_assoc($checkStock)) {
                    $currAvailable = (int)$sRow['availableQty'];
                    if ($qty > $currAvailable) {
                        throw new Exception("Insufficient Stock for spare: '" . $sRow['itemName'] . "'. Available: $currAvailable, Requested: $qty");
                    }
                }
            }

            $sparesToDeduct[] = [
                'stock_id' => $stId,
                'spare_id' => (int)($_POST['spare_id'][$idx] ?? 0),
                'name' => $sName,
                'barcode' => mysqli_real_escape_string($conn, $_POST['spare_barcode'][$idx] ?? ''),
                'rack' => mysqli_real_escape_string($conn, $_POST['spare_rack'][$idx] ?? ''),
                'partno' => mysqli_real_escape_string($conn, $_POST['spare_partno'][$idx] ?? ''),
                'qty' => $qty,
                'price' => (float)($_POST['spare_price'][$idx] ?? 0),
                'gst' => (float)($_POST['spare_gst'][$idx] ?? 0)
            ];
        }
    }

    // 3. Update jobcard main record
    $e_jobStatus = mysqli_real_escape_string($conn, $_POST['jobStatus'] ?? 'New');
    $e_jobCategory = mysqli_real_escape_string($conn, $_POST['jobCategory'] ?? 'Service');
    $laborCharge = (float)($_POST['laborCharge'] ?? 0);
    $paidAmount = (float)($_POST['paidAmount'] ?? 0);

    // Calculate Spares Total
    $sparesTotal = 0;
    foreach ($sparesToDeduct as $sItem) {
        $sub = $sItem['qty'] * $sItem['price'];
        $gstVal = $sub * ($sItem['gst'] / 100);
        $sparesTotal += ($sub + $gstVal);
    }
    $grandTotal = $sparesTotal + $laborCharge;

    $custVal = ($customerId > 0) ? $customerId : "NULL";

    $updateJcSql = "UPDATE jobcard 
                    SET givenDate = '$givenDate',
                        customer = $custVal,
                        jobStatus = '$e_jobStatus',
                        jobCategory = '$e_jobCategory',
                        laborCharge = $laborCharge,
                        actualAmountSum = $grandTotal,
                        receivedAmountSum = $paidAmount,
                        modifiedBy = '$user',
                        modifiedOn = '$now'
                    WHERE id = $jobcardId";

    if (!mysqli_query($conn, $updateJcSql)) {
        throw new Exception("Error updating jobcard: " . mysqli_error($conn));
    }

    // 4. Update / Insert jobcarditems
    $mId = (int)($_POST['machine'] ?? 0);
    $serial = mysqli_real_escape_string($conn, $_POST['serial'] ?? '');
    $wDetails = mysqli_real_escape_string($conn, $_POST['workDetails'] ?? 'Service');
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks'] ?? '');

    $mName = '';
    if ($mId > 0) {
        $mRes = mysqli_query($conn, "SELECT machineName FROM machine WHERE id = $mId LIMIT 1");
        if ($mRes && $mRow = mysqli_fetch_assoc($mRes)) {
            $mName = mysqli_real_escape_string($conn, $mRow['machineName']);
        }
    }

    $itemCheck = mysqli_query($conn, "SELECT id FROM jobcarditems WHERE jobCard = $jobcardId OR id = $jobcardId LIMIT 1");
    if ($itemCheck && $itemRow = mysqli_fetch_assoc($itemCheck)) {
        $itemId = $itemRow['id'];
        $updateItemSql = "UPDATE jobcarditems 
                          SET machine = " . ($mId > 0 ? $mId : "NULL") . ",
                              machineName = '$mName',
                              serialNo = '$serial',
                              issueDetails = '$wDetails',
                              remark = '$remarks',
                              actualAmount = $grandTotal,
                              modifiedBy = '$user',
                              modifiedOn = '$now'
                          WHERE id = $itemId";
        mysqli_query($conn, $updateItemSql);
    } else {
        $itemId = $jobcardId;
        $insertItemSql = "INSERT INTO jobcarditems (id, jobCard, machine, machineName, serialNo, issueDetails, remark, actualAmount, quoteAmount, assembledByUs, deleted, createdBy, createdOn, modifiedBy, modifiedOn) 
                          VALUES ($jobcardId, $jobcardId, " . ($mId > 0 ? $mId : "NULL") . ", '$mName', '$serial', '$wDetails', '$remarks', $grandTotal, 0, 0, 0, '$user', '$now', '$user', '$now')";
        mysqli_query($conn, $insertItemSql);
    }

    // 5. Delete old spares and insert new spares + DEDUCT stock
    mysqli_query($conn, "DELETE FROM jobcarditemspares WHERE jobCardItem = $itemId OR jobCardItem = $jobcardId");

    foreach ($sparesToDeduct as $sItem) {
        $spUuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
        $sub = $sItem['qty'] * $sItem['price'];
        $gstVal = $sub * ($sItem['gst'] / 100);
        $totalPrice = $sub + $gstVal;

        $spareIdVal = ($sItem['spare_id'] > 0) ? $sItem['spare_id'] : "NULL";
        $stockIdEsc = mysqli_real_escape_string($conn, $sItem['stock_id']);

        $insSpareSql = "INSERT INTO jobcarditemspares (id, jobCardItem, spares, stock, itemName, pricePerQty, quantity, gstPercentage, gstValue, totalPrice, deleted, createdBy, createdOn, modifiedBy, modifiedOn) 
                        VALUES ('$spUuid', $itemId, $spareIdVal, '$stockIdEsc', '{$sItem['name']}', {$sItem['price']}, {$sItem['qty']}, {$sItem['gst']}, $gstVal, $totalPrice, 0, '$user', '$now', '$user', '$now')";
        
        if (!mysqli_query($conn, $insSpareSql)) {
            throw new Exception("Error inserting spare item: " . mysqli_error($conn));
        }

        if (!empty($stockIdEsc)) {
            $deductSql = "UPDATE stock SET availableQty = availableQty - {$sItem['qty']} WHERE id = '$stockIdEsc'";
            mysqli_query($conn, $deductSql);
        }
    }

    mysqli_commit($conn);
    echo json_encode(['success' => true, 'message' => 'Job Card updated successfully!']);
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
?>
