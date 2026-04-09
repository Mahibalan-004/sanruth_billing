<?php require_once("../config/db.php"); ?>
<?php include("../includes/header.php"); ?>

<div class="container">

    <!-- HEADER -->
    <div class="header">
        <h2>Manage Spares</h2>

        <div class="actions">
            <a href="add_spare.php" class="btn green">+ New</a>
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
                    <th>Spare Name</th>
                    <th>Part #</th>
                    <th>Rack #</th>
                    <th>Active</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $res = mysqli_query($conn,"SELECT * FROM spares ORDER BY id DESC");

                if(mysqli_num_rows($res) > 0){
                    $i=1;
                    while($row=mysqli_fetch_assoc($res)){
                ?>
                <tr>
                    <td><?= $i++ ?></td>

                    <td>
                        <a href="edit_spare.php?id=<?= $row['id'] ?>" class="link">
                            <?= $row['spareName'] ?>
                        </a>
                    </td>

                    <td><?= $row['partNo'] ?></td>

                    <td><?= $row['rackNumber'] ?></td>

                    <td>
                        <?= $row['active'] ? '<span class="yes">Yes</span>' : '<span class="no">No</span>' ?>
                    </td>

                    <td style="text-align:center;">
                        <a href="edit_spare.php?id=<?= $row['id'] ?>" class="edit-btn">✏️</a>
                    </td>
                </tr>
                <?php } } else { ?>
                    <tr>
                        <td colspan="6" class="no-data">No spare parts available</td>
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

/* STATUS */
.yes {
    color:#16a34a;
    font-weight:600;
}

.no {
    color:#dc2626;
    font-weight:600;
}

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