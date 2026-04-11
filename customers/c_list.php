<?php 
require_once("../config/db.php");
include("../includes/header.php");

// FETCH CUSTOMERS
$query = "SELECT c.*, a.city, a.line1, a.line2 FROM customer c JOIN address a ON c.address = a.id ORDER BY c.id DESC";
$result = mysqli_query($conn, $query);
?>

<div style="padding: 20px; background: #f8fafc; min-height: calc(100vh - 110px);">
    
    <!-- HEADER BAR (ERP STYLE) -->
          

        <div style="padding-right: 20px;">
            <a href="add_customer.php" style="background: #fff; color: #1e7e34; padding: 8px 18px; border-radius: 6px; font-weight: 700; text-decoration: none; font-size: 13px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: 0.2s;">
                + New Customer
            </a>
        </div>


    <!-- MAIN CONTENT CARD -->
    <div style="background: #fff; border-radius: 0 0 12px 12px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); padding: 25px; border: 1px solid #e2e8f0; border-top: none;">
        
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; min-width: 900px;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 15px; text-align: left; color: #475569; font-weight: 700; font-size: 13px; text-transform: uppercase;">#</th>
                        <th style="padding: 15px; text-align: left; color: #475569; font-weight: 700; font-size: 13px; text-transform: uppercase;">Customer ID</th>
                        <th style="padding: 15px; text-align: left; color: #475569; font-weight: 700; font-size: 13px; text-transform: uppercase;">Name</th>
                        <th style="padding: 15px; text-align: left; color: #475569; font-weight: 700; font-size: 13px; text-transform: uppercase;">Phone # Primary</th>
                        <th style="padding: 15px; text-align: left; color: #475569; font-weight: 700; font-size: 13px; text-transform: uppercase;">City</th>
                        <th style="padding: 15px; text-align: left; color: #475569; font-weight: 700; font-size: 13px; text-transform: uppercase;">Active</th>
                        <th style="padding: 15px; text-align: center; color: #475569; font-weight: 700; font-size: 13px; text-transform: uppercase;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php $i = 1; while($row = mysqli_fetch_assoc($result)): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#fbfcfe'" onmouseout="this.style.background='white'">
                                <td style="padding: 15px; color: #64748b; font-size: 14px;"><?= $i++ ?></td>
                                <td style="padding: 15px; color: #1e293b; font-weight: 600; font-size: 14px;"><?= htmlspecialchars($row['customerId'] ?? '') ?></td>
                                <td style="padding: 15px; color: #1e293b; font-weight: 600; font-size: 14px;"><?= htmlspecialchars($row['name'] ?? '') ?></td>
                                <td style="padding: 15px; color: #475569; font-size: 14px;"><?= htmlspecialchars($row['phoneNo1'] ?? '') ?></td>
                                <td style="padding: 15px; color: #475569; font-size: 14px;"><?= htmlspecialchars($row['city'] ?? '') ?: '-' ?></td>
                                <td style="padding: 15px;">
                                    <?php if ($row['active']): ?>
                                        <span style="background: #dcfce7; color: #166534; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700;">Active</span>
                                    <?php else: ?>
                                        <span style="background: #fee2e2; color: #991b1b; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700;">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 15px; text-align: center;">
                                    <div style="display: flex; justify-content: center; gap: 10px;">
                                        <a href="edit_customer.php?id=<?= $row['id'] ?>" style="background: #eff6ff; color: #2563eb; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; text-decoration: none; border: 1px solid #dbeafe;" title="Edit">📝</a>
                                        <a href="delete_customer.php?id=<?= $row['id'] ?>" style="background: #fef2f2; color: #ef4444; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; text-decoration: none; border: 1px solid #fee2e2;" title="Delete" onclick="return confirm('Ar u sure?')">🗑️</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 50px; text-align: center; color: #94a3b8; font-style: italic;">No customers found. Click "+ New Customer" to add one.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    a:hover { opacity: 0.8; transform: translateY(-1px); }
</style>

<?php include("../includes/footer.php"); ?>
