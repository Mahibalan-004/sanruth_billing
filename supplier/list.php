<?php
require_once("../config/db.php");
require_once("../includes/auth.php");
requireAdmin();
include("../includes/header.php");

$query = "SELECT s.*, a.city, a.line1, a.line2 FROM supplier s JOIN address a ON s.address = a.id ORDER BY s.id DESC";
$result = mysqli_query($conn, $query);
?>

<div style="padding: 20px; background: #f8fafc; min-height: calc(100vh - 110px);">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="margin: 0; color: #1e293b; font-size: 20px; font-weight: 700;">Supplier List</h3>
        <a href="add.php"
            style="background: #2563eb; color: #fff; padding: 10px 20px; border-radius: 8px; font-weight: 700; text-decoration: none; font-size: 13px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
            + New Supplier
        </a>
    </div>

    <div
        style="background: #fff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); padding: 25px; border: 1px solid #e2e8f0;">

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 15px; text-align: left; color: #64748b; font-weight: 700; font-size: 13px;">
                            #</th>
                        <th style="padding: 15px; text-align: left; color: #64748b; font-weight: 700; font-size: 13px;">
                            Name</th>
                        <th style="padding: 15px; text-align: left; color: #64748b; font-weight: 700; font-size: 13px;">
                            Contact</th>
                        <th style="padding: 15px; text-align: left; color: #64748b; font-weight: 700; font-size: 13px;">
                            Location</th>
                        <th style="padding: 15px; text-align: left; color: #64748b; font-weight: 700; font-size: 13px;">
                            Status</th>
                        <th
                            style="padding: 15px; text-align: center; color: #64748b; font-weight: 700; font-size: 13px;">
                            Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php $i = 1;
                        while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;"
                                onmouseover="this.style.background='#fbfcfe'" onmouseout="this.style.background='white'">
                                <td style="padding: 15px; color: #94a3b8; font-size: 14px;"><?= $i++ ?></td>
                                <td style="padding: 15px; color: #1e293b; font-weight: 600; font-size: 14px;">
                                    <?= htmlspecialchars($row['name'] ?? '') ?></td>
                                <td style="padding: 15px;">
                                    <div style="color: #1e293b; font-weight: 500; font-size: 14px;">
                                        <?= htmlspecialchars($row['phoneNo1'] ?? '') ?></div>
                                    <div style="color: #64748b; font-size: 12px;">
                                        <?= htmlspecialchars($row['emailId'] ?? '') ?: '-' ?></div>
                                </td>
                                <td style="padding: 15px; color: #475569; font-size: 14px;">
                                    <?= htmlspecialchars($row['city'] ?? '') ?: '-' ?></td>
                                <td style="padding: 15px;">
                                    <?php if ($row['active']): ?>
                                        <span style="color: #16a34a; font-weight: 700; font-size: 12px;">Active</span>
                                    <?php else: ?>
                                        <span style="color: #ef4444; font-weight: 700; font-size: 12px;">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 15px; text-align: center;">
                                    <div style="display: flex; justify-content: center; gap: 10px;">
                                        <a href="edit.php?id=<?= $row['id'] ?>" style="color: #2563eb; text-decoration: none;"
                                            title="Edit">✏️</a>
                                        <a href="delete.php?id=<?= $row['id'] ?>" style="color: #ef4444; text-decoration: none;"
                                            title="Delete" onclick="return confirm('Delete this supplier?')">🗑️</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="padding: 50px; text-align: center; color: #94a3b8;">No suppliers found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>