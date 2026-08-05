<?php
require_once(__DIR__ . "/../config/db.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $currentYM = date('ym');
    $prefix = "{$currentYM}J";
    $oldPrefix1 = date('y/m') . "/J/";
    $oldPrefix2 = date('ym') . "J";
    $oldPrefix3 = date('ym') . " J";

    // Strip slashes and spaces from input cardNo
    $cardNo = str_replace(['/', ' '], '', trim($_POST['cardNo'] ?? ''));

    // Parse givenDate into YYYY-MM-DD format for MySQL DATE column
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

    // Customer resolution / auto-create fallback
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
        // Create basic customer record
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
    $user = "System Admin";

    // Fallback if cardNo wasn't provided or empty
    if (empty($cardNo)) {
        $cardNo = $_SESSION['draft_jobcard_no'] ?? '';
        $cardNo = str_replace(['/', ' '], '', $cardNo);
    }

    // Atomic Insertion & Concurrency Handling Loop
    $inserted = false;
    $attempts = 0;
    $finalCardNo = $cardNo;
    $jobcardId = 0;

    while (!$inserted && $attempts < 15) {
        $attempts++;
        if (empty($finalCardNo)) {
            $cardResult = mysqli_query($conn, "SELECT cardNo FROM jobcard WHERE cardNo LIKE '$prefix%' OR cardNo LIKE '$oldPrefix1%' OR cardNo LIKE '$oldPrefix2%' OR cardNo LIKE '$oldPrefix3%' ORDER BY id DESC LIMIT 1");
            $lastNum = 0;
            if ($cardResult && $row = mysqli_fetch_assoc($cardResult)) {
                if (preg_match('/(\d+)$/', trim($row['cardNo']), $matches)) {
                    $lastNum = intval($matches[1]);
                }
            }
            $finalCardNo = $prefix . str_pad((string)($lastNum + 1), 5, '0', STR_PAD_LEFT);
        }

        $e_cardNo = mysqli_real_escape_string($conn, $finalCardNo);

        mysqli_begin_transaction($conn);

        try {
            // Check if cardNo is already taken by another concurrent user
            $checkRes = mysqli_query($conn, "SELECT id FROM jobcard WHERE cardNo = '$e_cardNo' LIMIT 1");
            if ($checkRes && mysqli_num_rows($checkRes) > 0) {
                // Card number exists, generate next sequential number for current YY/MM
                mysqli_rollback($conn);
                $cardResult = mysqli_query($conn, "SELECT cardNo FROM jobcard WHERE cardNo LIKE '$prefix%' OR cardNo LIKE '$oldPrefix1%' OR cardNo LIKE '$oldPrefix2%' OR cardNo LIKE '$oldPrefix3%' ORDER BY id DESC LIMIT 1");
                $lastNum = 0;
                if ($cardResult && $row = mysqli_fetch_assoc($cardResult)) {
                    if (preg_match('/(\d+)$/', trim($row['cardNo']), $matches)) {
                        $lastNum = intval($matches[1]);
                    }
                }
                $finalCardNo = $prefix . str_pad((string)($lastNum + 1), 5, '0', STR_PAD_LEFT);
                continue;
            }

            // Insert into jobcard table
            $custVal = ($customerId > 0) ? $customerId : "NULL";
            $jcSql = "INSERT INTO jobcard (cardNo, givenDate, customer, jobStatus, completed, delivered, actualAmountSum, quoteAmountSum, receivedAmountSum, laborCharge, createdBy, createdOn, modifiedBy, modifiedOn) 
                      VALUES ('$e_cardNo', '$givenDate', $custVal, 'New', 0, 0, 0, 0, 0, 0, '$user', '$now', '$user', '$now')";

            if (!mysqli_query($conn, $jcSql)) {
                throw new Exception("Error inserting jobcard: " . mysqli_error($conn));
            }

            $jobcardId = mysqli_insert_id($conn);

            // Insert into jobcarditems table
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

            $machVal = ($mId > 0) ? $mId : "NULL";
            $itemSql = "INSERT INTO jobcarditems (id, jobCard, machine, machineName, serialNo, issueDetails, remark, actualAmount, quoteAmount, assembledByUs, deleted, createdBy, createdOn, modifiedBy, modifiedOn) 
                        VALUES ($jobcardId, $jobcardId, $machVal, '$mName', '$serial', '$wDetails', '$remarks', 0, 0, 0, 0, '$user', '$now', '$user', '$now')";
            
            if (!mysqli_query($conn, $itemSql)) {
                throw new Exception("Error inserting jobcard item: " . mysqli_error($conn));
            }

            mysqli_commit($conn);
            $inserted = true;

        } catch (Exception $e) {
            mysqli_rollback($conn);
            // On duplicate or transaction error, increment sequence and retry
            $cardResult = mysqli_query($conn, "SELECT cardNo FROM jobcard WHERE cardNo LIKE '$prefix%' OR cardNo LIKE '$oldPrefix1%' OR cardNo LIKE '$oldPrefix2%' OR cardNo LIKE '$oldPrefix3%' ORDER BY id DESC LIMIT 1");
            $lastNum = 0;
            if ($cardResult && $row = mysqli_fetch_assoc($cardResult)) {
                if (preg_match('/(\d+)$/', trim($row['cardNo']), $matches)) {
                    $lastNum = intval($matches[1]);
                }
            }
            $finalCardNo = $prefix . str_pad((string)($lastNum + 1), 5, '0', STR_PAD_LEFT);
        }
    }

    if ($inserted) {
        // Clear session reservation after successful save
        unset($_SESSION['draft_jobcard_no']);
        unset($_SESSION['draft_jobcard_ym']);

        echo "<script>alert('Job Card Created successfully! Number: $finalCardNo'); window.location.href='create.php';</script>";
    } else {
        echo "Error saving job card. Please try again.";
    }

} else {
    header("Location: create.php");
    exit;
}
?>