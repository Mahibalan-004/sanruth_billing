<?php
require_once("../config/db.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    echo "<script>alert('Access denied: insufficient privileges'); window.location='../login/dashboard.php';</script>";
    exit();
}

// ================= PAGINATION =================
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1)
    $page = 1;
$offset = ($page - 1) * $limit;

// ================= FILTER =================
$where = "WHERE 1=1";

if (!empty($_GET['item'])) {
    $item = mysqli_real_escape_string($conn, $_GET['item']);
    $where .= " AND s.spareName LIKE '%$item%'";
}

if (!empty($_GET['part'])) {
    $part = mysqli_real_escape_string($conn, $_GET['part']);
    $where .= " AND s.partNo LIKE '%$part%'";
}

if (!empty($_GET['barcode'])) {
    $barcode = mysqli_real_escape_string($conn, $_GET['barcode']);
    $where .= " AND st.barCode LIKE '%$barcode%'";
}

// ================= COUNT =================
$countQuery = "SELECT COUNT(*) as total 
FROM stock st 
LEFT JOIN spares s ON st.spare=s.id 
$where";

$countResult = mysqli_query($conn, $countQuery);
$totalRows = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRows / $limit);

// ================= DATA =================
$query = "
SELECT 
    st.*, 
    s.spareName, 
    s.partNo, 
    s.rackNumber,
    b.brandName, 
    m.model as modelName
FROM stock st
LEFT JOIN spares s ON st.spare = s.id
LEFT JOIN brand b ON st.brand = b.id
LEFT JOIN model m ON st.model = m.id
$where
ORDER BY st.id DESC
LIMIT $limit OFFSET $offset
";

$result = mysqli_query($conn, $query);
$rows = [];

while ($r = mysqli_fetch_assoc($result)) {
    $rows[] = $r;
}

// ================= QUERY STRING =================
$queryParams = $_GET;
unset($queryParams['page']);
$queryString = http_build_query($queryParams);
?>

<?php include("../includes/header.php"); ?>

<style>
    body {
        background: #f8fafc;
        font-family: sans-serif;
    }

    .table-box {
        background: #fff;
        padding: 15px;
        border-radius: 10px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background: #f1f5f9;
        font-size: 12px;
        text-transform: uppercase;
        padding: 10px;
        color: #64748b;
    }

    td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
    }

    .barcode-box {
        height: 40px;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }

    .qty {
        background: #dbeafe;
        text-align: center;
        font-weight: bold;
    }

    .price {
        background: #dcfce7;
    }

    .sold {
        background: #bbf7d0;
    }

    .edit-btn {
        text-decoration: none;
        font-size: 18px;
        padding: 5px;
    }

    .edit-btn:hover {
        color: #2563eb;
    }

    .pagination {
        margin-top: 10px;
        display: flex;
        justify-content: space-between;
    }

    .pagination a {
        padding: 6px 10px;
        background: #e2e8f0;
        border-radius: 5px;
        text-decoration: none;
    }

    .pagination a.active {
        background: #2563eb;
        color: #fff;
    }
</style>

<div style="width:95%;margin:auto;">

    <h2>Manage Stocks</h2>

    <div class="table-box">

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Barcode</th>
                    <th>Item Name</th>
                    <th>Part #</th>
                    <th>Rack #</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>Avail. Qty</th>
                    <th>Selling</th>
                    <th>Sold</th>
                    <th>GST %</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php if (count($rows)):
                    $i = $offset + 1; ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>

                            <td><?= $i++ ?></td>

                            <td>
                                <div class="barcode-box">
                                    <svg id="barcode<?= (int) $row['id'] ?>"></svg>
                                </div>
                                <?= htmlspecialchars($row['barCode']) ?>
                            </td>

                            <td><?= htmlspecialchars($row['spareName']) ?></td>
                            <td><?= htmlspecialchars($row['partNo']) ?></td>
                            <td><?= htmlspecialchars($row['rackNumber']) ?></td>
                            <td><?= htmlspecialchars($row['brandName']) ?></td>
                            <td><?= htmlspecialchars($row['modelName']) ?></td>

                            <td class="qty"><?= (int) $row['availableQty'] ?></td>

                            <td class="price"><?= number_format($row['sellingPricePerUnit'], 2) ?></td>
                            <td class="sold"><?= number_format($row['selledPricePerUnit'], 2) ?></td>

                            <td><?= number_format($row['gstPercentage'] ?? 0, 2) ?></td>

                            <td><?= ($row['selled']) ? 'Yes' : 'No' ?></td>

                            <td>
                                <!-- ✅ FIXED EDIT LINK -->
                                <a href="edit_stock.php?id=<?= urlencode($row['id']) ?>" class="edit-btn">
                                    ✏️
                                </a>
                            </td>

                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="13">No Data Found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- PAGINATION -->
        <div class="pagination">

            <div>
                Showing <?= $offset + 1 ?> - <?= min($offset + $limit, $totalRows) ?> of <?= $totalRows ?>
            </div>

            <div>
                <a href="?<?= $queryString ?>&page=1">First</a>
                <a href="?<?= $queryString ?>&page=<?= max(1, $page - 1) ?>">Prev</a>
                <a class="active"><?= $page ?></a>
                <a href="?<?= $queryString ?>&page=<?= min($totalPages, $page + 1) ?>">Next</a>
                <a href="?<?= $queryString ?>&page=<?= $totalPages ?>">Last</a>
            </div>

        </div>

    </div>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode/dist/JsBarcode.all.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        <?php foreach ($rows as $row): ?>
            JsBarcode("#barcode<?= (int) $row['id'] ?>", "<?= $row['barCode'] ?>", {
                format: "CODE128",
                width: 1,
                height: 30,
                displayValue: false
            });
        <?php endforeach; ?>
    });
</script>

<?php include("../includes/footer.php"); ?>