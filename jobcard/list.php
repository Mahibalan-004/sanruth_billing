<?php
require_once("../config/db.php");
include("../includes/header.php");

$query = "
    SELECT 
        j.id,
        j.cardNo,
        j.jobStatus,
        j.givenDate,
        j.completed,
        j.delivered,
        j.laborCharge,
        j.actualAmountSum,
        c.name AS customerName,
        c.phoneNo1,
        m.machineName
    FROM jobcard j
    LEFT JOIN customer c ON j.customer = c.id
    LEFT JOIN jobcarditems ji ON j.id = ji.jobCard
    LEFT JOIN machine m ON ji.machine = m.id
    ORDER BY j.id DESC
";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error fetching job cards: " . mysqli_error($conn));
}
?>

<div style="padding: 20px; background: #f8fafc; min-height: calc(100vh - 110px);">
    
    <!-- HEADER BAR -->
    <div style="background: #ffffff; display: flex; align-items: center; justify-content: space-between; border-radius: 8px 8px 0 0; padding: 15px 20px; border: 1px solid #e2e8f0; border-bottom: none;">
        <div style="color: #1e293b; font-weight: 700; font-size: 20px;">Job Card Bills</div>
        <div style="display: flex; gap: 15px; align-items: center;">
            <a href="create.php" style="color: #1e293b; text-decoration: none; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 5px;">
                <span style="background: #1e293b; color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 12px;">+</span> New
            </a>
            <a href="#" style="color: #1e293b; text-decoration: none; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 5px;">
                <span>📥</span> Download
            </a>
            <a href="list.php" style="color: #1e293b; text-decoration: none; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 5px;">
                <span>🔄</span> Refresh
            </a>
            <a href="#" style="color: #1e293b; text-decoration: none; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 5px;">
                <span>🔽</span> Filter
            </a>
        </div>
    </div>

    <!-- MAIN LIST -->
    <div style="background: #fff; border-radius: 0 0 12px 12px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); padding: 0; border: 1px solid #e2e8f0; overflow: hidden; overflow-x: auto;">
        
        <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 1000px;">
            <thead>
                <tr style="background: #f8fafc;">
                    <th rowspan="2" style="padding: 12px 15px; border-bottom: 2px solid #e2e8f0; font-size: 14px; color: #1e293b;">#</th>
                    <th rowspan="2" style="padding: 12px 15px; border-bottom: 2px solid #e2e8f0; font-size: 14px; color: #1e293b;">Category</th>
                    <th rowspan="2" style="padding: 12px 15px; border-bottom: 2px solid #e2e8f0; font-size: 14px; color: #1e293b;">Job Card #</th>
                    <th rowspan="2" style="padding: 12px 15px; border-bottom: 2px solid #e2e8f0; font-size: 14px; color: #1e293b;">Status</th>
                    <th rowspan="2" style="padding: 12px 15px; border-bottom: 2px solid #e2e8f0; font-size: 14px; color: #1e293b;">Allocated To</th>
                    <th rowspan="2" style="padding: 12px 15px; border-bottom: 2px solid #e2e8f0; font-size: 14px; color: #1e293b;">Machine</th>
                    <th colspan="2" style="text-align: center; padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #1e293b;">Customer</th>
                    <th colspan="3" style="text-align: center; padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #1e293b;">Date</th>
                    <th colspan="2" style="text-align: center; padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #1e293b; background: #dcfce7; border-left: 1px solid #e2e8f0;">Amount</th>
                    <th rowspan="2" style="padding: 12px 15px; border-bottom: 2px solid #e2e8f0; font-size: 14px; color: #1e293b; text-align: center;">Action</th>
                </tr>
                <tr style="background: #f8fafc;">
                    <th style="padding: 12px 15px; border-bottom: 2px solid #e2e8f0; font-size: 14px; color: #1e293b; border-right: 1px solid #e2e8f0;">Name</th>
                    <th style="padding: 12px 15px; border-bottom: 2px solid #e2e8f0; font-size: 14px; color: #1e293b;">Contact #</th>
                    <th style="padding: 12px 15px; border-bottom: 2px solid #e2e8f0; font-size: 14px; color: #1e293b; border-right: 1px solid #e2e8f0;">Given</th>
                    <th style="padding: 12px 15px; border-bottom: 2px solid #e2e8f0; font-size: 14px; color: #1e293b; background: #bfdbfe; border-right: 1px solid #e2e8f0;">Completed</th>
                    <th style="padding: 12px 15px; border-bottom: 2px solid #e2e8f0; font-size: 14px; color: #1e293b; background: #bfdbfe;">Delivered</th>
                    <th style="padding: 12px 15px; border-bottom: 2px solid #e2e8f0; font-size: 14px; color: #1e293b; background: #dcfce7; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;">Labour</th>
                    <th style="padding: 12px 15px; border-bottom: 2px solid #e2e8f0; font-size: 14px; color: #1e293b; background: #dcfce7;">Billed</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $counter = 1;
                while($row = mysqli_fetch_assoc($result)): 
                    $rowBg = ($counter % 2 == 0) ? '#f8fafc' : '#ffffff';
                    
                    // Format Card No without slashes or spaces
                    $cleanCardNo = str_replace(['/', ' '], '', $row['cardNo'] ?? '');

                    // Format Dates
                    $givenDate = !empty($row['givenDate']) ? htmlspecialchars($row['givenDate']) : '-';
                    $completed = (!empty($row['completed']) && $row['completed'] != '0') ? htmlspecialchars($row['completed']) : '-';
                    $delivered = (!empty($row['delivered']) && $row['delivered'] != '0') ? htmlspecialchars($row['delivered']) : '-';
                    
                    // Format Amounts
                    $labor = number_format((float)$row['laborCharge'], 2);
                    $billed = number_format((float)$row['actualAmountSum'], 2);
                    
                    // Format Status
                    $status = htmlspecialchars($row['jobStatus']);
                    $statusDisplay = $status;
                    if ($status == 'New' || $status == 'New Job') {
                        $statusDisplay = "New<br>Job";
                    }
                ?>
                <tr style="background: <?= $rowBg ?>; border-bottom: 1px solid #e2e8f0; transition: 0.2s;">
                    <td style="padding: 12px 15px; font-size: 14px; color: #334155;"><?= $counter++ ?></td>
                    <td style="padding: 12px 15px; font-size: 14px; color: #334155;">Offsite</td>
                    <td style="padding: 12px 15px; font-size: 14px;">
                        <a href="edit.php?id=<?= $row['id'] ?>" style="color: #2563eb; text-decoration: underline; font-weight: 700;"><?= htmlspecialchars($cleanCardNo) ?></a>
                    </td>
                    <td style="padding: 0; font-size: 14px; text-align: center;">
                        <?php if($status == 'New' || $status == 'New Job'): ?>
                            <div style="background: #ef4444; color: #fff; padding: 12px 15px; height: 100%; display: flex; align-items: center; justify-content: center; min-height: 50px; font-weight: 600;">
                                <?= $statusDisplay ?>
                            </div>
                        <?php else: ?>
                            <div style="padding: 12px 15px; font-weight: 600;">
                                <?= $statusDisplay ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px 15px; font-size: 14px; color: #334155;">-</td>
                    <td style="padding: 12px 15px; font-size: 14px; color: #334155;"><?= htmlspecialchars($row['machineName'] ?? '-') ?></td>
                    <td style="padding: 12px 15px; font-size: 14px; color: #334155; border-right: 1px solid #e2e8f0;"><?= htmlspecialchars($row['customerName'] ?? '-') ?></td>
                    <td style="padding: 12px 15px; font-size: 14px; color: #334155;"><?= htmlspecialchars($row['phoneNo1'] ?? '-') ?></td>
                    <td style="padding: 12px 15px; font-size: 14px; color: #334155; border-right: 1px solid #e2e8f0;"><?= $givenDate ?></td>
                    <td style="padding: 12px 15px; font-size: 14px; color: #334155; background: #bfdbfe; border-right: 1px solid #e2e8f0;"><?= $completed ?></td>
                    <td style="padding: 12px 15px; font-size: 14px; color: #334155; background: #bfdbfe;"><?= $delivered ?></td>
                    <td style="padding: 12px 15px; font-size: 14px; color: #334155; background: #dcfce7; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;"><?= $labor ?></td>
                    <td style="padding: 12px 15px; font-size: 14px; color: #334155; background: #dcfce7;"><?= $billed ?></td>
                    <td style="padding: 12px 15px; text-align: center;">
                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm" style="background: #f59e0b; color: #ffffff; border: none; padding: 6px 14px; border-radius: 6px; font-weight: 700; text-decoration: none; font-size: 12px; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <i class="fa fa-edit"></i> Edit
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
                
                <?php if (mysqli_num_rows($result) == 0): ?>
                <tr>
                    <td colspan="14" style="padding: 30px; text-align: center; color: #64748b; font-size: 15px;">
                        No Job Cards Found.
                    </td>
                </tr>
                <?php endif; ?>
                
            </tbody>
        </table>
        
    </div>
</div>

<style>
    tbody tr:hover {
        background-color: #f1f5f9 !important;
    }
</style>

<?php include("../includes/footer.php"); ?>
