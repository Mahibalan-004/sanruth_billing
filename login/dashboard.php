<?php
require_once("../config/db.php");
include("../includes/header.php");

// STATS
$stockCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM stock"))['c'];
$spareCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM spares"))['c'];
$lowStockRes = mysqli_query($conn, "SELECT * FROM stock WHERE availableQty < quantity LIMIT 5"); // If quantity field means minQty?
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
            <a href="../spares/list_spare.php"
                style="color:#10b981; text-decoration:none; font-size: 13px; font-weight: 600;">Browse Master Data
                &rarr;</a>
        </div>
    </div>

    <!-- CARD 3 -->
    <div class="stat-card"
        style="background:#fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border-left: 5px solid #8b5cf6;">
        <h4 style="color:#64748b; font-size:12px; text-transform:uppercase; margin-bottom: 5px;">Daily Action</h4>
        <div style="font-size:18px; font-weight:700; color:#1e293b; margin-top:5px;">Ready to Bill</div>
        <div style="margin-top: 15px;">
            <a href="../stock/add_stock.php"
                style="background:#8b5cf6; color:white; padding: 6px 12px; border-radius: 6px; text-decoration:none; font-size: 12px; font-weight: 600;">Add
                to Stock</a>
        </div>
    </div>

</div>

<div style="width: 95%; margin: 0 auto 50px; padding: 0 30px;">
    <h3 style="margin-bottom: 20px; color:#334155;">Quick Menu</h3>
    <div
        style="background:#fff; padding: 40px; border-radius: 16px; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px;">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN'): ?>
            <a href="../stock/add_stock.php"
                style="text-align:center; padding: 20px; border: 1px solid #f1f5f9; border-radius: 12px; text-decoration:none; transition: transform 0.2s;"
                onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <span style="font-size:30px; display:block; margin-bottom:10px;">📦</span>
                <span style="color:#475569; font-weight:600;">Add Stock</span>
            </a>
        <?php endif; ?>
        <a href="../spares/add_spare.php"
            style="text-align:center; padding: 20px; border: 1px solid #f1f5f9; border-radius: 12px; text-decoration:none; transition: transform 0.2s;"
            onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <span style="font-size:30px; display:block; margin-bottom:10px;">⚙️</span>
            <span style="color:#475569; font-weight:600;">New Spare</span>
        </a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN'): ?>
            <a href="../stock/list.php"
                style="text-align:center; padding: 20px; border: 1px solid #f1f5f9; border-radius: 12px; text-decoration:none; transition: transform 0.2s;"
                onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <span style="font-size:30px; display:block; margin-bottom:10px;">📑</span>
                <span style="color:#475569; font-weight:600;">Inventory List</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<?php include("../includes/footer.php"); ?>