<?php
require_once("../config/db.php");
include("../includes/header.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: list.php");
    exit;
}

// Fetch Job Card
$jcRes = mysqli_query($conn, "
    SELECT j.*, c.name AS customerName, c.phoneNo1, c.id AS cust_id, a.city, a.line1, a.line2, a.zipCode
    FROM jobcard j
    LEFT JOIN customer c ON j.customer = c.id
    LEFT JOIN address a ON c.address = a.id
    WHERE j.id = $id
    LIMIT 1
");

if (!$jcRes || mysqli_num_rows($jcRes) === 0) {
    echo "<div style='padding: 30px; text-align: center; color: #ef4444; font-size: 18px;'>Job Card not found. <a href='list.php'>Back to List</a></div>";
    include("../includes/footer.php");
    exit;
}

$jobcard = mysqli_fetch_assoc($jcRes);
$cleanCardNo = str_replace(['/', ' '], '', $jobcard['cardNo']);

// Fetch Job Card Item
$itemRes = mysqli_query($conn, "SELECT * FROM jobcarditems WHERE jobCard = $id OR id = $id LIMIT 1");
$jcItem = $itemRes ? mysqli_fetch_assoc($itemRes) : [];

// Fetch Machines
$machines = mysqli_query($conn, "SELECT id, machineName FROM machine WHERE active = 1");

// Fetch Employees / Technicians
$employees = mysqli_query($conn, "SELECT id, name FROM employee WHERE active = 1 ORDER BY name ASC");

// Fetch Existing Spares for this Job Card
$sparesQuery = "
    SELECT 
        jis.*,
        s.id AS stock_id,
        s.availableQty,
        s.barCode,
        sp.partNo,
        sp.rackNumber,
        sp.picture
    FROM jobcarditemspares jis
    LEFT JOIN stock s ON jis.stock = s.id
    LEFT JOIN spares sp ON jis.spares = sp.id
    WHERE (jis.jobCardItem = {$id} OR jis.jobCardItem IN (SELECT id FROM jobcarditems WHERE jobCard = {$id}))
      AND jis.deleted = 0
";
$existingSparesRes = mysqli_query($conn, $sparesQuery);
$existingSpares = [];
if ($existingSparesRes) {
    while ($spRow = mysqli_fetch_assoc($existingSparesRes)) {
        $existingSpares[] = $spRow;
    }
}

// Convert givenDate for display
$givenDate = !empty($jobcard['givenDate']) ? $jobcard['givenDate'] : date('Y-m-d');
?>

<div style="padding: 20px; background: #f8fafc; min-height: calc(100vh - 110px);">

    <!-- HEADER BAR -->
    <div style="background: #ffffff; display: flex; align-items: center; justify-content: space-between; border-radius: 12px 12px 0 0; padding: 16px 24px; border: 1px solid #e2e8f0; border-bottom: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="display: flex; align-items: center; gap: 15px;">
            <h2 style="margin: 0; color: #0f172a; font-weight: 700; font-size: 22px;">Edit Job Card</h2>
            <span style="background: #eff6ff; color: #2563eb; padding: 5px 14px; border-radius: 20px; font-weight: 700; font-size: 14px; border: 1px solid #bfdbfe;">
                <?= htmlspecialchars($cleanCardNo) ?>
            </span>
        </div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <button type="button" onclick="location.reload()" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                🔄 Reload
            </button>
            <a href="list.php" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 13px; text-decoration: none; display: flex; align-items: center; gap: 6px;">
                📋 Back to List
            </a>
            <a href="#" onclick="window.print(); return false;" style="background: #2563eb; color: #ffffff; border: none; padding: 8px 18px; border-radius: 8px; font-weight: 600; font-size: 13px; text-decoration: none; display: flex; align-items: center; gap: 6px;">
                🖨️ Print
            </a>
        </div>
    </div>

    <!-- MAIN EDIT CONTAINER -->
    <div style="background: #ffffff; border-radius: 0 0 12px 12px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); padding: 25px; border: 1px solid #e2e8f0; border-top: none;">

        <div id="alertContainer"></div>

        <form id="editJobCardForm" onsubmit="submitJobCardEdit(event)">
            <input type="hidden" name="id" value="<?= $id ?>">

            <div style="display: grid; grid-template-columns: 1fr 340px; gap: 30px; align-items: start;">
                
                <!-- LEFT MAIN COLUMN -->
                <div>
                    
                    <!-- SECTION 1: CARD GENERAL INFO -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 25px;">
                        <h4 style="margin: 0 0 15px 0; color: #334155; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Job Card Overview</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label style="font-size: 13px;">Card # (Readonly)</label>
                                <input type="text" value="<?= htmlspecialchars($cleanCardNo) ?>" readonly style="background: #e2e8f0; font-weight: 700; color: #1e293b;">
                                <input type="hidden" name="cardNo" value="<?= htmlspecialchars($cleanCardNo) ?>">
                            </div>
                            <div class="form-group">
                                <label style="font-size: 13px;">Status <span class="required">*</span></label>
                                <select name="jobStatus" required style="height: 42px; font-weight: 600;">
                                    <?php 
                                    $statuses = ['New', 'In Progress', 'Completed', 'Delivered', 'Cancelled'];
                                    $currStatus = $jobcard['jobStatus'] ?? 'New';
                                    foreach ($statuses as $st):
                                    ?>
                                        <option value="<?= $st ?>" <?= ($currStatus == $st) ? 'selected' : '' ?>><?= $st ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label style="font-size: 13px;">Category</label>
                                <select name="jobCategory" style="height: 42px;">
                                    <?php 
                                    $categories = ['Service', 'Repair', 'General Checkup', 'Offsite', 'Replacement'];
                                    $currCat = $jobcard['jobCategory'] ?? 'Service';
                                    foreach ($categories as $cat):
                                    ?>
                                        <option value="<?= $cat ?>" <?= ($currCat == $cat) ? 'selected' : '' ?>><?= $cat ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label style="font-size: 13px;">Given Date <span class="required">*</span></label>
                                <input type="date" name="givenDate" value="<?= htmlspecialchars($givenDate) ?>" required style="height: 42px;">
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: CUSTOMER INFORMATION -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 25px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h4 style="margin: 0; color: #334155; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Customer Information</h4>
                            <button type="button" onclick="openCustomerLookupModal()" style="background: #2563eb; color: #ffffff; border: none; padding: 6px 14px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer;">
                                🔍 Search / Edit Customer
                            </button>
                        </div>
                        <input type="hidden" name="customerId" id="customerId" value="<?= htmlspecialchars($jobcard['cust_id'] ?? '') ?>">
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label style="font-size: 13px;">Phone # Primary <span class="required">*</span></label>
                                <input type="text" name="customerPhone" id="customerPhone" value="<?= htmlspecialchars($jobcard['phoneNo1'] ?? '') ?>" required placeholder="Phone Number" onfocus="openCustomerLookupModal(this.value)">
                            </div>
                            <div class="form-group">
                                <label style="font-size: 13px;">Customer Name <span class="required">*</span></label>
                                <input type="text" name="customerName" id="customerName" value="<?= htmlspecialchars($jobcard['customerName'] ?? '') ?>" required placeholder="Customer Name">
                            </div>
                            <div class="form-group">
                                <label style="font-size: 13px;">City</label>
                                <input type="text" name="city" id="city" value="<?= htmlspecialchars($jobcard['city'] ?? '') ?>" placeholder="City">
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: TAKEN SPARES TABLE -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 25px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h4 style="margin: 0; color: #334155; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Taken Spares</h4>
                            <button type="button" onclick="openSpareSearchModal()" style="background: #16a34a; color: #ffffff; border: none; padding: 7px 16px; border-radius: 6px; font-weight: 700; font-size: 12px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                + Add Spare Row
                            </button>
                        </div>

                        <div style="overflow-x: auto;">
                            <table class="spares-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                                <thead>
                                    <tr style="background: #f8fafc; color: #475569; font-size: 12px; text-transform: uppercase; border-bottom: 2px solid #e2e8f0;">
                                        <th style="padding: 10px; width: 50px;">Img</th>
                                        <th style="padding: 10px;">Spare Name</th>
                                        <th style="padding: 10px; width: 100px;">Barcode</th>
                                        <th style="padding: 10px; width: 90px;">Rack No</th>
                                        <th style="padding: 10px; width: 100px;">Part No</th>
                                        <th style="padding: 10px; width: 80px;">Qty</th>
                                        <th style="padding: 10px; width: 100px;">Price (₹)</th>
                                        <th style="padding: 10px; width: 80px;">GST %</th>
                                        <th style="padding: 10px; width: 110px;">Total (₹)</th>
                                        <th style="padding: 10px; width: 50px; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="sparesTbody">
                                    <?php if (!empty($existingSpares)): ?>
                                        <?php foreach ($existingSpares as $sIdx => $sp): 
                                            $sub = (float)$sp['quantity'] * (float)$sp['pricePerQty'];
                                            $gstVal = $sub * ((float)$sp['gstPercentage'] / 100);
                                            $rowTotal = $sub + $gstVal;
                                            $imgSrc = !empty($sp['picture']) && file_exists("../uploads/" . $sp['picture']) ? "../uploads/" . $sp['picture'] : "../img/no-image.png";
                                        ?>
                                            <tr class="spare-row">
                                                <td style="padding: 8px;">
                                                    <div style="width: 38px; height: 38px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 16px;">⚙️</div>
                                                </td>
                                                <td style="padding: 8px;">
                                                    <input type="text" name="spare_name[]" value="<?= htmlspecialchars($sp['itemName']) ?>" class="form-control spare-name" required style="height: 36px;">
                                                    <input type="hidden" name="spare_stock_id[]" value="<?= htmlspecialchars($sp['stock_id'] ?? $sp['stock'] ?? '') ?>" class="spare-stock-id">
                                                    <input type="hidden" name="spare_id[]" value="<?= htmlspecialchars($sp['spares'] ?? '') ?>" class="spare-id">
                                                    <input type="hidden" class="spare-avail-qty" value="<?= htmlspecialchars($sp['availableQty'] ?? 9999) ?>">
                                                </td>
                                                <td style="padding: 8px;"><input type="text" name="spare_barcode[]" value="<?= htmlspecialchars($sp['barCode'] ?? '-') ?>" class="form-control spare-barcode" style="height: 36px;"></td>
                                                <td style="padding: 8px;"><input type="text" name="spare_rack[]" value="<?= htmlspecialchars($sp['rackNumber'] ?? '-') ?>" class="form-control spare-rack" style="height: 36px;"></td>
                                                <td style="padding: 8px;"><input type="text" name="spare_partno[]" value="<?= htmlspecialchars($sp['partNo'] ?? '-') ?>" class="form-control spare-partno" style="height: 36px;"></td>
                                                <td style="padding: 8px;"><input type="number" name="spare_qty[]" value="<?= (int)$sp['quantity'] ?>" min="1" class="form-control spare-qty" oninput="calculateSparesTotal()" style="height: 36px; text-align: center;"></td>
                                                <td style="padding: 8px;"><input type="number" step="0.01" name="spare_price[]" value="<?= (float)$sp['pricePerQty'] ?>" min="0" class="form-control spare-price" oninput="calculateSparesTotal()" style="height: 36px;"></td>
                                                <td style="padding: 8px;"><input type="number" step="0.01" name="spare_gst[]" value="<?= (float)$sp['gstPercentage'] ?>" min="0" class="form-control spare-gst" oninput="calculateSparesTotal()" style="height: 36px; text-align: center;"></td>
                                                <td style="padding: 8px; font-weight: 700; color: #0f172a;" class="spare-row-total"><?= number_format($rowTotal, 2) ?></td>
                                                <td style="padding: 8px; text-align: center;">
                                                    <button type="button" onclick="removeSpareRow(this)" style="background: #ef4444; color: #fff; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer; font-size: 12px;">🗑️</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SECTION 4: JOB CARD ITEM DETAIL -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px;">
                        <h4 style="margin: 0 0 15px 0; color: #334155; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Job Card Item Details</h4>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                            <div class="form-group">
                                <label style="font-size: 13px;">Machine <span class="required">*</span></label>
                                <select name="machine" required style="height: 42px;">
                                    <option value="">Select Machine</option>
                                    <?php 
                                    $selectedMachine = $jcItem['machine'] ?? '';
                                    while ($m = mysqli_fetch_assoc($machines)): 
                                    ?>
                                        <option value="<?= $m['id'] ?>" <?= ($selectedMachine == $m['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($m['machineName']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label style="font-size: 13px;">Serial #</label>
                                <input type="text" name="serial" value="<?= htmlspecialchars($jcItem['serialNo'] ?? '') ?>" placeholder="Serial Number" style="height: 42px;">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                            <div class="form-group">
                                <label style="font-size: 13px;">Work Details</label>
                                <select name="workDetails" style="height: 42px;">
                                    <?php 
                                    $workOpts = ['Service', 'Repair', 'Replacement', 'Total Checkup'];
                                    $currWork = $jcItem['issueDetails'] ?? 'Service';
                                    foreach ($workOpts as $wo):
                                    ?>
                                        <option value="<?= $wo ?>" <?= ($currWork == $wo) ? 'selected' : '' ?>><?= $wo ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label style="font-size: 13px;">Technician / Employee Allocated</label>
                                <select name="employee" style="height: 42px;">
                                    <option value="">Select Technician</option>
                                    <?php 
                                    $selectedEmp = $jobcard['employee'] ?? '';
                                    if ($employees):
                                        while ($emp = mysqli_fetch_assoc($employees)): 
                                    ?>
                                            <option value="<?= $emp['id'] ?>" <?= ($selectedEmp == $emp['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($emp['name']) ?>
                                            </option>
                                    <?php 
                                        endwhile; 
                                    endif;
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="font-size: 13px;">Service / Repair Remarks</label>
                            <textarea name="remarks" placeholder="Enter detailed service/repair remarks..." style="height: 80px; resize: none;"><?= htmlspecialchars($jcItem['remark'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group" style="max-width: 250px;">
                            <label style="font-size: 13px;">Labour Charge (₹)</label>
                            <input type="number" step="0.01" name="laborCharge" id="laborCharge" value="<?= (float)($jobcard['laborCharge'] ?? 0) ?>" oninput="calculateSparesTotal()" style="height: 42px; font-weight: 700; color: #2563eb;">
                        </div>
                    </div>

                </div>

                <!-- RIGHT STICKY SUMMARY PANEL -->
                <div style="position: sticky; top: 20px;">
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);">
                        <h4 style="margin: 0 0 15px 0; color: #0f172a; font-size: 15px; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">Summary</h4>

                        <!-- BLUE: TOTAL AMOUNT -->
                        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 16px; margin-bottom: 15px;">
                            <div style="font-size: 12px; font-weight: 700; color: #1e40af; text-transform: uppercase; letter-spacing: 0.5px;">Total Amount</div>
                            <div style="font-size: 24px; font-weight: 800; color: #1e3a8a; margin-top: 4px;" id="summaryTotal">₹0.00</div>
                        </div>

                        <!-- GREEN: PAID AMOUNT -->
                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 16px; margin-bottom: 15px;">
                            <div style="font-size: 12px; font-weight: 700; color: #166534; text-transform: uppercase; letter-spacing: 0.5px;">Paid Amount</div>
                            <input type="number" step="0.01" name="paidAmount" id="paidAmount" value="<?= (float)($jobcard['receivedAmountSum'] ?? 0) ?>" oninput="calculateSparesTotal()" style="height: 38px; font-size: 18px; font-weight: 800; color: #15803d; background: #ffffff; margin-top: 6px;">
                        </div>

                        <!-- RED: BALANCE AMOUNT -->
                        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 16px; margin-bottom: 20px;">
                            <div style="font-size: 12px; font-weight: 700; color: #991b1b; text-transform: uppercase; letter-spacing: 0.5px;">Balance Amount</div>
                            <div style="font-size: 24px; font-weight: 800; color: #991b1b; margin-top: 4px;" id="summaryBalance">₹0.00</div>
                        </div>

                        <!-- SUMMARY DETAILS BREAKDOWN -->
                        <div style="font-size: 13px; color: #475569; border-top: 1px solid #f1f5f9; padding-top: 12px; display: flex; flex-direction: column; gap: 8px;">
                            <div style="display: flex; justify-content: space-between;">
                                <span>Spares Subtotal:</span>
                                <strong id="summarySparesSub">₹0.00</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span>Labour Charge:</span>
                                <strong id="summaryLabour">₹0.00</strong>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- BOTTOM BUTTONS -->
            <div style="margin-top: 30px; border-top: 1.5px solid #e2e8f0; padding-top: 20px; display: flex; justify-content: flex-end; gap: 12px;">
                <a href="#" onclick="window.print(); return false;" style="background: #475569; color: #fff; padding: 11px 24px; border-radius: 8px; font-weight: 700; text-decoration: none; font-size: 14px;">Print</a>
                <button type="reset" onclick="setTimeout(calculateSparesTotal, 100)" style="background: #94a3b8; color: #fff; border: none; padding: 11px 24px; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer;">Reset</button>
                <a href="list.php" style="background: #e2e8f0; color: #334155; padding: 11px 24px; border-radius: 8px; font-weight: 700; text-decoration: none; font-size: 14px;">Cancel</a>
                <button type="submit" id="saveJobCardBtn" style="background: #2563eb; color: #fff; border: none; padding: 11px 32px; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);">Save Changes</button>
            </div>

        </form>
    </div>
</div>

<!-- ==================== SEARCHABLE SPARE SELECTION MODAL ==================== -->
<div id="spareSearchModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 1050px; width: 95%;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                <h3 style="margin: 0; color: #0f172a; font-size: 18px; font-weight: 700;">Select Spare Part</h3>
                <input type="text" id="spareSearchInput" placeholder="Search by Spare Name, Code, Barcode, or Part No..." autocomplete="off" oninput="triggerSpareModalSearch(this.value)" style="flex: 1; max-width: 450px; height: 38px; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0 12px; font-size: 14px;">
            </div>
            <button type="button" onclick="closeSpareSearchModal()" style="background: transparent; border: none; font-size: 24px; color: #64748b; cursor: pointer; line-height: 1;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 0; overflow-y: auto; max-height: 65vh;">
            <table class="cust-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; color: #475569; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 12px;">Spare Code / Stock ID</th>
                        <th style="padding: 12px;">Spare Name</th>
                        <th style="padding: 12px;">Barcode</th>
                        <th style="padding: 12px;">Rack No</th>
                        <th style="padding: 12px;">Part No</th>
                        <th style="padding: 12px; text-align: center;">Available Stock</th>
                        <th style="padding: 12px; text-align: right;">Selling Price (₹)</th>
                        <th style="padding: 12px; text-align: center;">GST %</th>
                        <th style="padding: 12px; text-align: center; width: 90px;">Action</th>
                    </tr>
                </thead>
                <tbody id="spareModalTbody">
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 30px; color: #64748b;">Loading available spares...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== CUSTOMER LOOKUP MODAL ==================== -->
<div id="customerLookupModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 1050px; width: 95%;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                <h3 style="margin: 0; color: #0f172a; font-size: 18px; font-weight: 700;">Customer Lookup</h3>
                <input type="text" id="modalSearchInput" placeholder="Search by Phone, Name, or Customer ID..." autocomplete="off" oninput="triggerCustomerModalSearch(this.value)" style="flex: 1; max-width: 450px; height: 38px; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0 12px; font-size: 14px;">
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button" onclick="openNewCustomerModal()" style="background: #16a34a; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer;">
                    + New Customer
                </button>
                <button type="button" onclick="closeCustomerLookupModal()" style="background: transparent; border: none; font-size: 24px; color: #64748b; cursor: pointer; line-height: 1;">&times;</button>
            </div>
        </div>
        <div class="modal-body" style="padding: 0; overflow-y: auto; max-height: 65vh;">
            <table class="cust-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; color: #475569; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 12px 14px;">Customer ID</th>
                        <th style="padding: 12px 14px;">Customer Name</th>
                        <th style="padding: 12px 14px;">Address</th>
                        <th style="padding: 12px 14px;">Contact Number</th>
                        <th style="padding: 12px 14px;">WhatsApp Number</th>
                        <th style="padding: 12px 14px; text-align: center;">Active Status</th>
                        <th style="padding: 12px 14px; text-align: center; width: 80px;">Choose</th>
                        <th style="padding: 12px 14px; text-align: center; width: 80px;">Edit</th>
                    </tr>
                </thead>
                <tbody id="modalCustTbody">
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 30px; color: #64748b;">Loading customers...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== CUSTOMER EDIT / NEW MODAL ==================== -->
<div id="customerEditModal" class="modal-overlay" style="z-index: 10000;">
    <div class="modal-content" style="max-width: 650px; width: 90%;">
        <div class="modal-header">
            <h3 id="editModalTitle" style="margin: 0; color: #0f172a; font-size: 18px; font-weight: 700;">Edit Customer</h3>
            <button type="button" onclick="closeCustomerEditModal()" style="background: transparent; border: none; font-size: 24px; color: #64748b; cursor: pointer; line-height: 1;">&times;</button>
        </div>
        <form id="customerEditForm" onsubmit="saveCustomerAjax(event)">
            <div class="modal-body" style="padding: 20px;">
                <input type="hidden" name="id" id="edit_id" value="0">
                <input type="hidden" name="address_id" id="edit_address_id" value="0">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-size: 12px;">Customer ID</label>
                        <input type="text" name="customerId" id="edit_customerId" placeholder="Auto-generated if empty" style="height: 38px;">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 12px;">Customer Name <span class="required">*</span></label>
                        <input type="text" name="name" id="edit_name" required placeholder="Full Name" style="height: 38px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-size: 12px;">Primary Contact # <span class="required">*</span></label>
                        <input type="text" name="phoneNo1" id="edit_phoneNo1" required placeholder="Primary Phone" style="height: 38px;">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 12px;">Secondary Phone #</label>
                        <input type="text" name="phoneNo2" id="edit_phoneNo2" placeholder="Secondary Phone" style="height: 38px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-size: 12px;">WhatsApp Number</label>
                        <input type="text" name="whatsAppNo" id="edit_whatsAppNo" placeholder="WhatsApp Number" style="height: 38px;">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 12px;">Email ID</label>
                        <input type="email" name="emailId" id="edit_emailId" placeholder="Email Address" style="height: 38px;">
                    </div>
                </div>

                <div style="margin-bottom: 15px;">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label style="font-size: 12px;">Address Line 1</label>
                        <input type="text" name="line1" id="edit_line1" placeholder="Street Address / Line 1" style="height: 38px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label style="font-size: 12px;">Address Line 2</label>
                        <input type="text" name="line2" id="edit_line2" placeholder="Line 2 / Area" style="height: 38px;">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label style="font-size: 12px;">City</label>
                            <input type="text" name="city" id="edit_city" placeholder="City" style="height: 38px;">
                        </div>
                        <div class="form-group">
                            <label style="font-size: 12px;">Zip Code</label>
                            <input type="text" name="zipCode" id="edit_zipCode" placeholder="Zip Code" style="height: 38px;">
                        </div>
                    </div>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="active" id="edit_active" value="1" checked style="width: 18px; height: 18px; cursor: pointer;">
                    <label for="edit_active" style="margin: 0; font-size: 13px; font-weight: 600; cursor: pointer;">Active Status</label>
                </div>
            </div>
            <div style="padding: 15px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; border-radius: 0 0 12px 12px;">
                <button type="button" onclick="closeCustomerEditModal()" style="background: #94a3b8; color: #fff; border: none; padding: 9px 20px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer;">Cancel</button>
                <button type="submit" id="saveCustBtn" style="background: #2563eb; color: #fff; border: none; padding: 9px 24px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer;">Save Customer</button>
            </div>
        </form>
    </div>
</div>

<style>
    .form-group label {
        display: block;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 6px;
        font-size: 13px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea,
    .form-control {
        width: 100%;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        padding: 0 12px;
        font-size: 14px;
        transition: 0.15s ease-in-out;
    }

    .required {
        color: #ef4444;
    }

    input:focus,
    select:focus,
    textarea:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(4px);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.15s ease-out;
    }

    .modal-content {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        max-height: 90vh;
    }

    .modal-header {
        padding: 16px 20px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .cust-table tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.15s;
    }

    .cust-table tbody tr:hover {
        background: #f8fafc;
    }

    .badge-active {
        background: #dcfce7;
        color: #166534;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        display: inline-block;
    }

    .badge-inactive {
        background: #fee2e2;
        color: #991b1b;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        display: inline-block;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>

<script>
    let fetchedSpareItems = [];
    let spareSearchDebounce = null;
    let fetchedCustomers = [];
    let custSearchDebounce = null;

    document.addEventListener('DOMContentLoaded', () => {
        calculateSparesTotal();
    });

    // Calculate Real-Time Totals
    function calculateSparesTotal() {
        let sparesSubtotal = 0;
        const rows = document.querySelectorAll('#sparesTbody tr.spare-row');

        rows.forEach(row => {
            const qtyInput = row.querySelector('.spare-qty');
            const priceInput = row.querySelector('.spare-price');
            const gstInput = row.querySelector('.spare-gst');
            const totalCell = row.querySelector('.spare-row-total');

            const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
            const price = parseFloat(priceInput ? priceInput.value : 0) || 0;
            const gst = parseFloat(gstInput ? gstInput.value : 0) || 0;

            const sub = qty * price;
            const gstVal = sub * (gst / 100);
            const rowTotal = sub + gstVal;

            if (totalCell) {
                totalCell.textContent = '₹' + rowTotal.toFixed(2);
            }

            sparesSubtotal += rowTotal;
        });

        const laborChargeInput = document.getElementById('laborCharge');
        const paidAmountInput = document.getElementById('paidAmount');

        const laborCharge = parseFloat(laborChargeInput ? laborChargeInput.value : 0) || 0;
        const paidAmount = parseFloat(paidAmountInput ? paidAmountInput.value : 0) || 0;

        const grandTotal = sparesSubtotal + laborCharge;
        const balanceAmount = grandTotal - paidAmount;

        document.getElementById('summarySparesSub').textContent = '₹' + sparesSubtotal.toFixed(2);
        document.getElementById('summaryLabour').textContent = '₹' + laborCharge.toFixed(2);
        document.getElementById('summaryTotal').textContent = '₹' + grandTotal.toFixed(2);
        document.getElementById('summaryBalance').textContent = '₹' + balanceAmount.toFixed(2);
    }

    function removeSpareRow(btn) {
        const row = btn.closest('tr');
        if (row) {
            row.remove();
            calculateSparesTotal();
        }
    }

    // Spare Search Modal Controls
    function openSpareSearchModal() {
        document.getElementById('spareSearchModal').style.display = 'flex';
        const input = document.getElementById('spareSearchInput');
        input.value = '';
        setTimeout(() => input.focus(), 100);
        fetchModalSpares('');
    }

    function closeSpareSearchModal() {
        document.getElementById('spareSearchModal').style.display = 'none';
    }

    function triggerSpareModalSearch(val) {
        clearTimeout(spareSearchDebounce);
        spareSearchDebounce = setTimeout(() => {
            fetchModalSpares(val);
        }, 200);
    }

    function fetchModalSpares(query = '') {
        const tbody = document.getElementById('spareModalTbody');
        tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 25px; color: #64748b;">Searching available spares...</td></tr>';

        fetch('../spares/api_search_spares.php?query=' + encodeURIComponent(query.trim()))
            .then(res => res.json())
            .then(resData => {
                if (!resData.success || !Array.isArray(resData.data) || resData.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 30px; color: #64748b;">No matching spare parts found.</td></tr>';
                    fetchedSpareItems = [];
                    return;
                }

                fetchedSpareItems = resData.data;
                tbody.innerHTML = resData.data.map((item, idx) => `
                    <tr>
                        <td style="padding: 10px 12px; font-weight: 600; color: #334155; font-size: 12.5px;">${escapeHtml(item.stock_id || '-')}</td>
                        <td style="padding: 10px 12px; font-weight: 700; color: #0f172a; font-size: 13px;">${escapeHtml(item.spareName || '-')}</td>
                        <td style="padding: 10px 12px; color: #475569; font-size: 12.5px;">${escapeHtml(item.barCode || '-')}</td>
                        <td style="padding: 10px 12px; color: #475569; font-size: 12.5px;">${escapeHtml(item.rackNumber || '-')}</td>
                        <td style="padding: 10px 12px; color: #475569; font-size: 12.5px;">${escapeHtml(item.partNo || '-')}</td>
                        <td style="padding: 10px 12px; text-align: center; font-weight: 700; color: ${item.availableQty > 0 ? '#166534' : '#ef4444'}; font-size: 13px;">
                            ${item.availableQty}
                        </td>
                        <td style="padding: 10px 12px; text-align: right; font-weight: 700; color: #2563eb; font-size: 13px;">₹${parseFloat(item.sellingPrice || 0).toFixed(2)}</td>
                        <td style="padding: 10px 12px; text-align: center; font-size: 12.5px;">${item.gstPercentage}%</td>
                        <td style="padding: 10px 12px; text-align: center;">
                            <button type="button" onclick="selectSpareFromModal(${idx})" style="background: #16a34a; color: #fff; border: none; padding: 5px 12px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer;">
                                Select
                            </button>
                        </td>
                    </tr>
                `).join('');
            })
            .catch(err => {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 25px; color: #ef4444;">Error searching spares.</td></tr>';
            });
    }

    function selectSpareFromModal(idx) {
        const item = fetchedSpareItems[idx];
        if (!item) return;

        const tbody = document.getElementById('sparesTbody');
        const tr = document.createElement('tr');
        tr.className = 'spare-row';

        const rowTotal = parseFloat(item.sellingPrice || 0) * (1 + (parseFloat(item.gstPercentage || 0)/100));

        tr.innerHTML = `
            <td style="padding: 8px;">
                <div style="width: 38px; height: 38px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 16px;">⚙️</div>
            </td>
            <td style="padding: 8px;">
                <input type="text" name="spare_name[]" value="${escapeHtml(item.spareName)}" class="form-control spare-name" required style="height: 36px;">
                <input type="hidden" name="spare_stock_id[]" value="${escapeHtml(item.stock_id)}" class="spare-stock-id">
                <input type="hidden" name="spare_id[]" value="${escapeHtml(item.spare_id)}" class="spare-id">
                <input type="hidden" class="spare-avail-qty" value="${item.availableQty}">
            </td>
            <td style="padding: 8px;"><input type="text" name="spare_barcode[]" value="${escapeHtml(item.barCode)}" class="form-control spare-barcode" style="height: 36px;"></td>
            <td style="padding: 8px;"><input type="text" name="spare_rack[]" value="${escapeHtml(item.rackNumber)}" class="form-control spare-rack" style="height: 36px;"></td>
            <td style="padding: 8px;"><input type="text" name="spare_partno[]" value="${escapeHtml(item.partNo)}" class="form-control spare-partno" style="height: 36px;"></td>
            <td style="padding: 8px;"><input type="number" name="spare_qty[]" value="1" min="1" class="form-control spare-qty" oninput="calculateSparesTotal()" style="height: 36px; text-align: center;"></td>
            <td style="padding: 8px;"><input type="number" step="0.01" name="spare_price[]" value="${parseFloat(item.sellingPrice).toFixed(2)}" min="0" class="form-control spare-price" oninput="calculateSparesTotal()" style="height: 36px;"></td>
            <td style="padding: 8px;"><input type="number" step="0.01" name="spare_gst[]" value="${parseFloat(item.gstPercentage).toFixed(2)}" min="0" class="form-control spare-gst" oninput="calculateSparesTotal()" style="height: 36px; text-align: center;"></td>
            <td style="padding: 8px; font-weight: 700; color: #0f172a;" class="spare-row-total">₹${rowTotal.toFixed(2)}</td>
            <td style="padding: 8px; text-align: center;">
                <button type="button" onclick="removeSpareRow(this)" style="background: #ef4444; color: #fff; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer; font-size: 12px;">🗑️</button>
            </td>
        `;

        tbody.appendChild(tr);
        calculateSparesTotal();
        closeSpareSearchModal();
    }

    // Customer Lookup Modal Functions
    function openCustomerLookupModal(initialValue = '') {
        const modal = document.getElementById('customerLookupModal');
        const input = document.getElementById('modalSearchInput');
        modal.style.display = 'flex';

        if (initialValue && initialValue.trim() !== '') {
            input.value = initialValue;
        }

        setTimeout(() => input.focus(), 100);
        fetchModalCustomers(input.value);
    }

    function closeCustomerLookupModal() {
        document.getElementById('customerLookupModal').style.display = 'none';
    }

    function triggerCustomerModalSearch(val) {
        clearTimeout(custSearchDebounce);
        custSearchDebounce = setTimeout(() => {
            fetchModalCustomers(val);
        }, 200);
    }

    function fetchModalCustomers(query = '') {
        const tbody = document.getElementById('modalCustTbody');
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 25px; color: #64748b;">Searching customers...</td></tr>';

        fetch('../customers/api_search_customers.php?query=' + encodeURIComponent(query.trim()))
            .then(res => res.json())
            .then(resData => {
                if (!resData.success || !Array.isArray(resData.data) || resData.data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 35px; color: #64748b;">
                                <div style="margin-bottom: 10px; font-size: 15px;">No matching customers found</div>
                                <button type="button" onclick="openNewCustomerModal('${escapeHtml(query)}')" style="background: #16a34a; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer;">
                                    + Add New Customer
                                </button>
                            </td>
                        </tr>`;
                    fetchedCustomers = [];
                    return;
                }

                fetchedCustomers = resData.data;
                tbody.innerHTML = resData.data.map((c, idx) => `
                    <tr>
                        <td style="padding: 12px 14px; font-weight: 600; color: #334155; font-size: 13px;">${escapeHtml(c.customerId || 'C-' + c.id)}</td>
                        <td style="padding: 12px 14px; font-weight: 700; color: #0f172a; font-size: 13.5px;">${escapeHtml(c.name || '-')}</td>
                        <td style="padding: 12px 14px; color: #475569; font-size: 12.5px;">${escapeHtml(c.fullAddress || '-')}</td>
                        <td style="padding: 12px 14px; font-weight: 600; color: #2563eb; font-size: 13px;">${escapeHtml(c.phoneNo1 || '-')}</td>
                        <td style="padding: 12px 14px; color: #475569; font-size: 12.5px;">${escapeHtml(c.whatsAppNo || '-')}</td>
                        <td style="padding: 12px 14px; text-align: center;">
                            <span class="${c.active ? 'badge-active' : 'badge-inactive'}">${c.active ? 'Active' : 'Inactive'}</span>
                        </td>
                        <td style="padding: 12px 14px; text-align: center;">
                            <button type="button" onclick="chooseCustomer(${idx})" style="background: #2563eb; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer;">Choose</button>
                        </td>
                        <td style="padding: 12px 14px; text-align: center;">
                            <button type="button" onclick="openEditCustomerModal(${idx})" style="background: #f59e0b; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer;">Edit</button>
                        </td>
                    </tr>
                `).join('');
            });
    }

    function chooseCustomer(idx) {
        const c = fetchedCustomers[idx];
        if (!c) return;

        document.getElementById('customerId').value = c.id || '';
        document.getElementById('customerPhone').value = c.phoneNo1 || '';
        document.getElementById('customerName').value = c.name || '';
        document.getElementById('city').value = c.city || '';

        closeCustomerLookupModal();
    }

    function openNewCustomerModal(initialVal = '') {
        document.getElementById('editModalTitle').textContent = 'New Customer';
        document.getElementById('customerEditForm').reset();
        document.getElementById('edit_id').value = '0';
        document.getElementById('edit_address_id').value = '0';
        document.getElementById('edit_active').checked = true;

        if (initialVal) {
            if (/^\d+$/.test(initialVal.trim())) {
                document.getElementById('edit_phoneNo1').value = initialVal.trim();
            } else {
                document.getElementById('edit_name').value = initialVal.trim();
            }
        }

        document.getElementById('customerEditModal').style.display = 'flex';
    }

    function openEditCustomerModal(idx) {
        const c = fetchedCustomers[idx];
        if (!c) return;

        document.getElementById('editModalTitle').textContent = 'Edit Customer';
        document.getElementById('edit_id').value = c.id || '0';
        document.getElementById('edit_address_id').value = c.address_id || '0';
        document.getElementById('edit_customerId').value = c.customerId || '';
        document.getElementById('edit_name').value = c.name || '';
        document.getElementById('edit_phoneNo1').value = c.phoneNo1 || '';
        document.getElementById('edit_phoneNo2').value = c.phoneNo2 || '';
        document.getElementById('edit_whatsAppNo').value = c.whatsAppNo || '';
        document.getElementById('edit_emailId').value = c.emailId || '';
        document.getElementById('edit_line1').value = c.line1 || '';
        document.getElementById('edit_line2').value = c.line2 || '';
        document.getElementById('edit_city').value = c.city || '';
        document.getElementById('edit_zipCode').value = c.zipCode || '';
        document.getElementById('edit_active').checked = Boolean(c.active);

        document.getElementById('customerEditModal').style.display = 'flex';
    }

    function closeCustomerEditModal() {
        document.getElementById('customerEditModal').style.display = 'none';
    }

    function saveCustomerAjax(e) {
        e.preventDefault();
        const form = document.getElementById('customerEditForm');
        const formData = new FormData(form);

        fetch('../customers/api_save_customer.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(resData => {
            if (!resData.success) {
                alert('Error: ' + (resData.error || 'Failed to save customer'));
                return;
            }

            const savedCust = resData.customer;
            closeCustomerEditModal();
            fetchModalCustomers(document.getElementById('modalSearchInput').value);

            if (savedCust) {
                document.getElementById('customerId').value = savedCust.id;
                document.getElementById('customerPhone').value = savedCust.phoneNo1 || '';
                document.getElementById('customerName').value = savedCust.name || '';
                document.getElementById('city').value = savedCust.city || '';
                closeCustomerLookupModal();
            }
        });
    }

    // Submit Job Card Edit via AJAX with Stock Validation
    function submitJobCardEdit(e) {
        e.preventDefault();

        // 1. Client side stock validation check
        const rows = document.querySelectorAll('#sparesTbody tr.spare-row');
        let stockError = '';

        rows.forEach(row => {
            const name = row.querySelector('.spare-name') ? row.querySelector('.spare-name').value : 'Spare';
            const qty = parseInt(row.querySelector('.spare-qty') ? row.querySelector('.spare-qty').value : 0) || 0;
            const avail = parseInt(row.querySelector('.spare-avail-qty') ? row.querySelector('.spare-avail-qty').value : 9999) || 0;

            if (qty > avail) {
                stockError = `Insufficient Stock for spare: '${name}'. Available: ${avail}, Requested: ${qty}`;
            }
        });

        const alertBox = document.getElementById('alertContainer');

        if (stockError) {
            alertBox.innerHTML = `
                <div style="background: #fee2e2; border: 1.5px solid #fca5a5; color: #991b1b; padding: 14px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; justify-content: space-between;">
                    <span>⚠️ ${escapeHtml(stockError)}</span>
                    <button type="button" onclick="this.parentElement.remove()" style="background: transparent; border: none; font-size: 18px; color: #991b1b; cursor: pointer;">&times;</button>
                </div>`;
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }

        const btn = document.getElementById('saveJobCardBtn');
        btn.disabled = true;
        btn.textContent = 'Saving Changes...';

        const form = document.getElementById('editJobCardForm');
        const formData = new FormData(form);

        fetch('update_jobcard.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(resData => {
            btn.disabled = false;
            btn.textContent = 'Save Changes';

            if (!resData.success) {
                alertBox.innerHTML = `
                    <div style="background: #fee2e2; border: 1.5px solid #fca5a5; color: #991b1b; padding: 14px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; justify-content: space-between;">
                        <span>⚠️ ${escapeHtml(resData.error || 'Failed to save changes')}</span>
                        <button type="button" onclick="this.parentElement.remove()" style="background: transparent; border: none; font-size: 18px; color: #991b1b; cursor: pointer;">&times;</button>
                    </div>`;
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }

            alert('Job Card updated successfully!');
            window.location.href = 'list.php';
        })
        .catch(err => {
            btn.disabled = false;
            btn.textContent = 'Save Changes';
            alertBox.innerHTML = `
                <div style="background: #fee2e2; border: 1.5px solid #fca5a5; color: #991b1b; padding: 14px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
                    An unexpected error occurred while saving the Job Card.
                </div>`;
        });
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
</script>

<?php include("../includes/footer.php"); ?>
