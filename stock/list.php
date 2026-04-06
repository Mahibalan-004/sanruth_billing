<?php
require_once("../config/db.php");

// ================= FILTER CONDITIONS =================
$where = "WHERE 1=1";

if(!empty($_GET['item'])){
    $item = mysqli_real_escape_string($conn, $_GET['item']);
    $where .= " AND s.spareName LIKE '%$item%'";
}

if(!empty($_GET['part'])){
    $part = mysqli_real_escape_string($conn, $_GET['part']);
    $where .= " AND s.partNo LIKE '%$part%'";
}

if(!empty($_GET['barcode'])){
    $barcode = mysqli_real_escape_string($conn, $_GET['barcode']);
    $where .= " AND st.barCode LIKE '%$barcode%'";
}

if(!empty($_GET['brand'])){
    $brand = mysqli_real_escape_string($conn, $_GET['brand']);
    $where .= " AND st.brand='$brand'";
}

if(!empty($_GET['model'])){
    $model = mysqli_real_escape_string($conn, $_GET['model']);
    $where .= " AND st.model='$model'";
}

if(isset($_GET['selled']) && $_GET['selled'] != ''){
    $selled = (int)$_GET['selled'];
    $where .= " AND st.selled = b'$selled'";
}

// ================= QUERY =================
$query = "
SELECT st.*, 
s.spareName, s.partNo, s.rackNumber,
b.brandName,
m.model as modelName
FROM stock st
LEFT JOIN spares s ON st.spare = s.id
LEFT JOIN brand b ON st.brand = b.id
LEFT JOIN model m ON st.model = m.id
$where
ORDER BY st.id DESC
";

$result = mysqli_query($conn, $query);
?>

<?php include("../includes/header.php"); ?>

<div class="container-fluid" style="width: 95%; margin: auto; display: flex; gap: 30px;">

    <!-- ================= SIDEBAR FILTER ================= -->
    <div class="filter-sidebar" style="width: 280px; flex-shrink: 0; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); height: fit-content;">
        <h3 style="margin-bottom: 20px; font-weight: 700; color: #1e293b; font-size: 16px;">Filter Stock</h3>
        
        <form method="GET">
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 700; margin-bottom: 6px;">Item Name</label>
                <input type="text" name="item" value="<?= $_GET['item'] ?? '' ?>" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 14px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 700; margin-bottom: 6px;">Part Number</label>
                <input type="text" name="part" value="<?= $_GET['part'] ?? '' ?>" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 14px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 700; margin-bottom: 6px;">Barcode</label>
                <input type="text" name="barcode" value="<?= $_GET['barcode'] ?? '' ?>" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 14px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 700; margin-bottom: 6px;">Brand</label>
                <select name="brand" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 14px;">
                    <option value="">All Brands</option>
                    <?php
                    $res=mysqli_query($conn,"SELECT * FROM brand");
                    while($r=mysqli_fetch_assoc($res)){
                        $selected = ($_GET['brand'] ?? '')==$r['id'] ? "selected" : "";
                        echo "<option value='{$r['id']}' $selected>{$r['brandName']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 700; margin-bottom: 6px;">Model</label>
                <select name="model" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 14px;">
                    <option value="">All Models</option>
                    <?php
                    $res=mysqli_query($conn,"SELECT * FROM model");
                    while($r=mysqli_fetch_assoc($res)){
                        $selected = ($_GET['model'] ?? '')==$r['id'] ? "selected" : "";
                        echo "<option value='{$r['id']}' $selected>{$r['model']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 700; margin-bottom: 6px;">Sales Status</label>
                <select name="selled" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 14px;">
                    <option value="">Show All</option>
                    <option value="1" <?= (($_GET['selled'] ?? '')=='1')?'selected':'' ?>>Sold Items</option>
                    <option value="0" <?= (($_GET['selled'] ?? '')=='0')?'selected':'' ?>>In Stock Only</option>
                </select>
            </div>

            <button type="submit" style="width: 100%; padding: 12px; border: none; background: #2563eb; color: #fff; font-weight: 700; border-radius: 8px; cursor: pointer; display: block; text-align: center; margin-bottom: 10px;">Apply Filters</button>
            <a href="list.php" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; background: #fff; color: #64748b; font-weight: 700; border-radius: 8px; cursor: pointer; display: block; text-align: center; text-decoration: none; font-size: 14px;">Clear Search</a>
        </form>
    </div>

    <!-- ================= MAIN CONTENT: STOCK TABLE ================= -->
    <div class="main-content" style="flex: 1; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow-x: auto;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2 style="font-weight: 800; color: #1e293b; font-size: 20px;">Current Inventory Stocks</h2>
            <a href="add_stock.php" style="padding: 10px 20px; background: #10b981; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 13px;">+ New Stock Entry</a>
        </div>

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 12px; text-align: left; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">#</th>
                    <th style="padding: 12px; text-align: left; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Barcode Label</th>
                    <th style="padding: 12px; text-align: left; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Item / Part Details</th>
                    <th style="padding: 12px; text-align: left; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Storage</th>
                    <th style="padding: 12px; text-align: left; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Brand/Model</th>
                    <th style="padding: 12px; text-align: center; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Available</th>
                    <th style="padding: 12px; text-align: right; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Price (Sell)</th>
                    <th style="padding: 12px; text-align: center; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if(mysqli_num_rows($result) > 0) {
                    $i=1;
                    while($row=mysqli_fetch_assoc($result)){
                ?>
                <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#fbfcfe'" onmouseout="this.style.background='white'">
                    <td style="padding: 15px; color: #94a3b8; font-weight: 600;"><?= $i++ ?></td>
                    
                    <td style="padding: 15px; text-align: center;">
                        <div style="background: #fff; padding: 5px; border: 1px solid #f1f5f9; display: inline-block; border-radius: 4px;">
                            <svg id="barcode<?= $row['id'] ?>"></svg>
                        </div><br>
                        <span style="font-family: monospace; font-size: 11px; color: #475569; font-weight: 600;"><?= $row['barCode'] ?></span>
                    </td>

                    <td style="padding: 15px;">
                        <span style="display: block; font-weight: 700; color: #1e293b;"><?= $row['spareName'] ?></span>
                        <span style="display: block; font-size: 11px; color: #64748b; font-family: monospace;"><?= $row['partNo'] ?></span>
                    </td>

                    <td style="padding: 15px;">
                        <span style="background: #f1f5f9; padding: 2px 8px; border-radius: 4px; font-size: 12px; color: #475569; font-weight: 600;">Rack: <?= $row['rackNumber'] ?></span>
                    </td>

                    <td style="padding: 15px;">
                        <span style="display: block; color: #334155; font-size: 13px; font-weight: 600;"><?= $row['brandName'] ?: 'N/A' ?></span>
                        <span style="display: block; color: #64748b; font-size: 12px;"><?= $row['modelName'] ?: 'Standard' ?></span>
                    </td>

                    <td style="padding: 15px; text-align: center;">
                        <span style="font-weight: 800; font-size: 15px; color: #1e293b;"><?= $row['availableQty'] ?></span>
                    </td>

                    <td style="padding: 15px; text-align: right;">
                        <span style="font-weight: 700; color: #10b981;">₹ <?= number_format($row['selledPricePerUnit'], 2) ?></span>
                        <span style="display: block; font-size: 10px; color: #94a3b8;">Incl. GST</span>
                    </td>

                    <td style="padding: 15px; text-align: center;">
                        <?php if($row['selled']): ?>
                            <span style="background: #fee2e2; color: #ef4444; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; text-transform: uppercase;">Sold Out</span>
                        <?php else: ?>
                            <span style="background: #ecfdf5; color: #10b981; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; text-transform: uppercase;">Active</span>
                        <?php endif; ?>
                    </td>
                </tr>

                <script>
                JsBarcode("#barcode<?= $row['id'] ?>", "<?= $row['barCode'] ?>", {
                    format: "CODE128",
                    width: 1,
                    height: 30,
                    displayValue: false,
                    margin: 2
                });
                </script>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='8' style='padding: 60px; text-align: center; color: #94a3b8;'>No stock items found matching your filters.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include("../includes/footer.php"); ?>