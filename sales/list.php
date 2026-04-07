<?php 
require_once("../config/db.php");
include("../includes/header.php");

// Fetch Sales with Customer details
$query = "SELECT s.*, c.name as customer_name 
          FROM sales s 
          LEFT JOIN customer c ON s.customer = c.id 
          ORDER BY s.id DESC";
$result = mysqli_query($conn, $query);
?>

<div style="padding: 20px; background: #f8fafc; min-height: calc(100vh - 110px);">
    
    <!-- HEADER BAR -->
    <div style="background: #1e7e34; height: 50px; display: flex; align-items: center; justify-content: space-between; border-radius: 8px 8px 0 0; overflow: hidden; padding: 0 20px;">
        <div style="color: #fff; font-weight: 700; font-size: 18px;">Sales Orders</div>
        <a href="create.php" style="background: #fff; color: #1e7e34; padding: 8px 18px; border-radius: 6px; font-weight: 700; text-decoration: none; font-size: 13px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: 0.2s;">
            + Create New Sale
        </a>
    </div>

    <!-- MAIN CONTENT CARD -->
    <div style="background: #fff; border-radius: 0 0 12px 12px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); padding: 25px; border: 1px solid #e2e8f0; border-top: none;">
        
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; min-width: 1000px;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 15px; text-align: left; color: #475569; font-weight: 700; font-size: 13px; text-transform: uppercase;">#</th>
                        <th style="padding: 15px; text-align: left; color: #475569; font-weight: 700; font-size: 13px; text-transform: uppercase;">Order #</th>
                        <th style="padding: 15px; text-align: left; color: #475569; font-weight: 700; font-size: 13px; text-transform: uppercase;">Date</th>
                        <th style="padding: 15px; text-align: left; color: #475569; font-weight: 700; font-size: 13px; text-transform: uppercase;">Customer</th>
                        <th style="padding: 15px; text-align: left; color: #475569; font-weight: 700; font-size: 13px; text-transform: uppercase;">Status</th>
                        <th style="padding: 15px; text-align: right; color: #475569; font-weight: 700; font-size: 13px; text-transform: uppercase;">Total Amount</th>
                        <th style="padding: 15px; text-align: right; color: #475569; font-weight: 700; font-size: 13px; text-transform: uppercase;">Paid</th>
                        <th style="padding: 15px; text-align: right; color: #475569; font-weight: 700; font-size: 13px; text-transform: uppercase;">Balance</th>
                        <th style="padding: 15px; text-align: center; color: #475569; font-weight: 700; font-size: 13px; text-transform: uppercase;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php $i = 1; while($row = mysqli_fetch_assoc($result)): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#fbfcfe'" onmouseout="this.style.background='white'">
                                <td style="padding: 15px; color: #64748b; font-size: 13px;"><?= $i++ ?></td>
                                <td style="padding: 15px; color: #1e293b; font-weight: 700; font-size: 14px;"><?= htmlspecialchars($row['orderNo']) ?></td>
                                <td style="padding: 15px; color: #475569; font-size: 14px;"><?= date('d-M-Y', strtotime($row['orderDate'])) ?></td>
                                <td style="padding: 15px; color: #1e293b; font-weight: 600; font-size: 14px;"><?= htmlspecialchars($row['customer_name'] ?: 'Guest') ?></td>
                                <td style="padding: 15px;">
                                    <?php 
                                        $statusColor = '#64748b';
                                        $statusBg = '#f1f5f9';
                                        if ($row['orderStatus'] == 'Completed') { $statusColor = '#166534'; $statusBg = '#dcfce7'; }
                                        elseif ($row['orderStatus'] == 'Pending') { $statusColor = '#92400e'; $statusBg = '#fef3c7'; }
                                    ?>
                                    <span style="background: <?= $statusBg ?>; color: <?= $statusColor ?>; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 700;"><?= $row['orderStatus'] ?></span>
                                </td>
                                <td style="padding: 15px; text-align: right; color: #1e293b; font-weight: 700; font-size: 14px;"><?= number_format($row['actualAmountSum'], 2) ?></td>
                                <td style="padding: 15px; text-align: right; color: #16a34a; font-weight: 600; font-size: 14px;"><?= number_format($row['paidAmountSum'], 2) ?></td>
                                <td style="padding: 15px; text-align: right; color: #ef4444; font-weight: 700; font-size: 14px;"><?= number_format($row['actualAmountSum'] - $row['paidAmountSum'], 2) ?></td>
                                <td style="padding: 15px; text-align: center;">
                                    <div style="display: flex; justify-content: center; gap: 10px;">
                                        <a href="view.php?id=<?= $row['id'] ?>" style="background: #f8fafc; color: #1e293b; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; text-decoration: none; border: 1px solid #e2e8f0;" title="View">👁️</a>
                                        <a href="edit.php?id=<?= $row['id'] ?>" style="background: #eff6ff; color: #2563eb; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; text-decoration: none; border: 1px solid #dbeafe;" title="Edit">📝</a>
                                        <a href="delete.php?id=<?= $row['id'] ?>" style="background: #fef2f2; color: #ef4444; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; text-decoration: none; border: 1px solid #fee2e2;" title="Delete" onclick="return confirm('Ar u sure?')">🗑️</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="padding: 50px; text-align: center; color: #94a3b8; font-style: italic;">No sales orders found. Click "+ Create New Sale" to add one.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    a:hover { opacity: 0.8; transform: translateY(-1px); transition: 0.2s; }
</style>

<?php include("../includes/footer.php"); ?>
