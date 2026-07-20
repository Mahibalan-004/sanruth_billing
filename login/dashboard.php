<?php
require_once("../config/db.php");
include("../includes/header.php");

// STATS
$stockCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM stock"))['c'];
$spareCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM spares"))['c'];
// Find low-stock items where available quantity is less than or equal to threshold (10)
$lowStockRes = mysqli_query($conn, "SELECT st.*, s.spareName FROM stock st LEFT JOIN spares s ON st.spare = s.id WHERE st.availableQty <= 10 ORDER BY st.availableQty ASC LIMIT 10");
$lowStockCount = $lowStockRes ? mysqli_num_rows($lowStockRes) : 0;
?>

<div class="dashboard-grid"
    style="padding: 30px; width: 95%; margin: auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px;">

    <!-- CARD 1 -->
    <div class="stat-card"
        style="background:#fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border-left: 5px solid #2563eb;">
        <h4 style="color:#64748b; font-size:12px; text-transform:uppercase; margin-bottom: 5px;">Total Stock Entries
        </h4>
        <div style="font-size:32px; font-weight:800; color:#1e293b;"><?= number_format($stockCount) ?></div>
        <div style="margin-top: 15px;">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN'): ?>
                <a href="../stock/list.php"
                    style="color:#2563eb; text-decoration:none; font-size: 13px; font-weight: 600;">Manage Inventory
                    &rarr;</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- CARD 2 -->
    <div class="stat-card"
        style="background:#fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border-left: 5px solid #10b981;">
        <h4 style="color:#64748b; font-size:12px; text-transform:uppercase; margin-bottom: 5px;">Spare Part Types</h4>
        <div style="font-size:32px; font-weight:800; color:#1e293b;"><?= number_format($spareCount) ?></div>
        <div style="margin-top: 15px;">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN'): ?>
                <a href="../spares/add_spare.php"
                    style="color:#10b981; text-decoration:none; font-size: 13px; font-weight: 600;">Browse Master Data
                    &rarr;</a>
            <?php else: ?>
                <span style="color:#64748b; font-size: 13px; font-weight: 600;">Admin only</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- CARD 3 -->
    <div class="stat-card"
        style="background:#fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border-left: 5px solid #8b5cf6;">
        <h4 style="color:#64748b; font-size:12px; text-transform:uppercase; margin-bottom: 5px;">Daily Action</h4>
        <div style="font-size:18px; font-weight:700; color:#1e293b; margin-top:5px;">Ready to Bill</div>
        <div style="margin-top: 15px;">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN'): ?>
                <a href="../stock/add_stock.php"
                    style="background:#8b5cf6; color:white; padding: 6px 12px; border-radius: 6px; text-decoration:none; font-size: 12px; font-weight: 600;">Add
                    to Stock</a>
            <?php else: ?>
                <span
                    style="display:inline-block; background:#e2e8f0; color:#475569; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;">Admin
                    only</span>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php if ($lowStockCount > 0): ?>
    <div style="width:95%; margin: 20px auto;">
        <div
            style="background:#fff; padding:20px; border-radius:12px; border-left:5px solid #f97316; box-shadow:0 4px 6px rgba(0,0,0,0.04);">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:20px;">
                <div>
                    <h3 style="margin:0 0 6px 0; color:#b45309;">Low Stock Alert</h3>
                    <div style="color:#475569; font-size:14px;">There are <strong
                            style="color:#111;"><?= $lowStockCount ?></strong> items with available quantity at or below
                        <strong>10</strong>.</div>
                </div>
                <div>
                    <a href="../stock/list.php"
                        style="background:#f97316; color:#fff; padding:8px 12px; border-radius:8px; text-decoration:none; font-weight:600;">View
                        Inventory</a>
                </div>
            </div>

            <div
                style="margin-top:12px; display:grid; grid-template-columns:repeat(auto-fit, minmax(180px,1fr)); gap:10px;">
                <?php while ($r = mysqli_fetch_assoc($lowStockRes)): ?>
                    <div style="padding:8px; border-radius:8px; background:#fff7ed; border:1px solid #ffedd5;">
                        <div style="font-weight:700; color:#92400e;"><?= htmlspecialchars($r['spareName'] ?? 'Item') ?></div>
                        <div style="font-size:13px; color:#475569;">Available: <strong><?= (int) $r['availableQty'] ?></strong>
                        </div>
                        <div style="font-size:13px; color:#475569;">Part: <?= htmlspecialchars($r['partNo'] ?? '-') ?></div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include("../includes/footer.php"); ?>