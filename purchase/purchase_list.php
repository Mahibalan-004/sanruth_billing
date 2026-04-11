<?php require_once("../config/db.php"); ?>
<?php include("../includes/header.php"); ?>

<div class="container">

    <!-- HEADER -->
    <div class="header">
        <h2>Manage Purchases</h2>

        <div class="actions">
            <a href="create.php" class="btn green">+ New Purchase</a>
            <button onclick="location.reload()" class="btn gray">⟳ Refresh</button>
            <button class="btn blue">⛃ Filter</button>
        </div>
    </div>

    <!-- TABLE -->
    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Order No</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Paid</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $res = mysqli_query($conn, "SELECT p.*, s.name as supplierName FROM purchase p LEFT JOIN supplier s ON p.supplier = s.id ORDER BY p.id DESC");

                if(mysqli_num_rows($res) > 0){
                    $i=1;
                    while($row=mysqli_fetch_assoc($res)){
                ?>
                <tr>
                    <td><?= $i++ ?></td>

                    <td>
                        <a href="view.php?id=<?= $row['id'] ?>" class="link">
                            <?= htmlspecialchars($row['orderNo'] ?? '') ?>
                        </a>
                    </td>

                    <td><?= htmlspecialchars($row['orderDate'] ?? '') ?></td>

                    <td><?= htmlspecialchars($row['supplierName'] ?? '') ?: 'N/A' ?></td>

                    <td>
                        <span class="status-badge <?= strtolower(htmlspecialchars($row['orderStatus'] ?? '')) ?>">
                            <?= htmlspecialchars($row['orderStatus'] ?? '') ?>
                        </span>
                    </td>

                    <td>₹<?= number_format($row['actualAmountSum'], 2) ?></td>
                    
                    <td>₹<?= number_format($row['paidAmountSum'], 2) ?></td>

                    <td style="text-align:center;">
                        <a href="edit.php?id=<?= $row['id'] ?>" class="edit-btn">✏️</a>
                    </td>
                </tr>
                <?php } } else { ?>
                    <tr>
                        <td colspan="8" class="no-data">No purchases available</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</div>

<style>
body {
    background:#f1f5f9;
    font-family: sans-serif;
}

.container {
    width:95%;
    margin:auto;
    background:#fff;
    padding:20px;
    border-radius:10px;
}

/* HEADER */
.header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:15px;
}

.actions {
    display:flex;
    gap:10px;
}

.btn {
    padding:8px 14px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-weight:600;
    text-decoration:none;
    color:#fff;
}

.green { background:#10b981; }
.gray { background:#64748b; }
.blue { background:#2563eb; }

/* TABLE */
.table-box {
    overflow:auto;
}

table {
    width:100%;
    border-collapse:collapse;
}

th {
    background:#f8fafc;
    padding:12px;
    text-align:left;
    font-size:12px;
    text-transform:uppercase;
    color:#64748b;
    border-bottom:2px solid #e2e8f0;
}

td {
    padding:14px;
    border-bottom:1px solid #f1f5f9;
}

tr:hover {
    background:#f8fafc;
}

/* LINK */
.link {
    color:#2563eb;
    text-decoration:underline;
    font-weight:500;
}

/* STATUS BADGE */
.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
}

.completed { background: #dcfce7; color: #166534; }
.pending { background: #fef9c3; color: #854d0e; }
.cancelled { background: #fee2e2; color: #991b1b; }

/* EDIT BUTTON */
.edit-btn {
    font-size:18px;
    text-decoration:none;
}

/* NO DATA */
.no-data {
    text-align:center;
    padding:30px;
    color:#64748b;
}
</style>

<?php include("../includes/footer.php"); ?>
