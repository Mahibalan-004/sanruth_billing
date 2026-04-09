<?php require_once("../config/db.php"); ?>
<?php include("../includes/header.php"); ?>

<?php
// ================= FILTER =================
$where = "WHERE 1=1";

$name = $_GET['name'] ?? '';

if(!empty($name)){
    $safe = mysqli_real_escape_string($conn,$name);
    $where .= " AND machineName LIKE '%$safe%'";
}

// ================= DATA =================
$query = "SELECT * FROM machine $where ORDER BY id DESC";
$res = mysqli_query($conn,$query);
?>

<div class="container">

    <!-- HEADER -->
    <div class="header">
        <h2>Manage Machines</h2>

        <div class="actions">
            <a href="add_machine.php" class="btn green">+ New</a>
            <button onclick="location.reload()" class="btn gray">⟳ Refresh</button>
            <button onclick="openFilter()" class="btn blue">⛃ Filter</button>
        </div>
    </div>

    <!-- TABLE -->
    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Active</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>

            <tbody>
            <?php
            if(mysqli_num_rows($res)>0){
                $i=1;
                while($row=mysqli_fetch_assoc($res)){
            ?>
                <tr>
                    <td><?= $i++ ?></td>

                    <td>
                        <a href="edit_machine.php?id=<?= $row['id'] ?>" class="link">
                            <?= $row['machineName'] ?>
                        </a>
                    </td>

                    <td>
                        <?= $row['active'] ? '<span class="yes">Yes</span>' : '<span class="no">No</span>' ?>
                    </td>

                    <td style="text-align:center;">
                        <a href="edit_machine.php?id=<?= $row['id'] ?>" class="edit-btn">✏️</a>
                    </td>
                </tr>
            <?php }} else { ?>
                <tr><td colspan="4" class="no-data">No Data Found</td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

</div>

<!-- FILTER DRAWER -->
<div id="filterDrawer" class="filter-drawer">
    <h3>Filter</h3>

    <form method="GET">

        <label>Machine Name</label>
        <input type="text" name="name" value="<?= $name ?>" class="input">

        <br>

        <button class="btn blue">Apply</button>
        <a href="list_machine.php" class="btn yellow">Clear</a>
        <button type="button" onclick="closeFilter()" class="btn gray">Cancel</button>

    </form>
</div>

<div id="overlay" class="overlay" onclick="closeFilter()"></div>

<style>
body{background:#f1f5f9;font-family:sans-serif;}

.container{width:95%;margin:auto;background:#fff;padding:20px;border-radius:10px;}

.header{display:flex;justify-content:space-between;margin-bottom:15px;}

.actions{display:flex;gap:10px;}

.btn{
    padding:8px 14px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    color:#fff;
    text-decoration:none;
}

.green{background:#10b981;}
.gray{background:#64748b;}
.blue{background:#2563eb;}
.yellow{background:#f59e0b;}

.table-box{overflow:auto;}

table{width:100%;border-collapse:collapse;}

th{
    background:#f8fafc;
    padding:12px;
    font-size:12px;
    text-transform:uppercase;
    color:#64748b;
}

td{
    padding:14px;
    border-bottom:1px solid #e2e8f0;
}

.link{color:#2563eb;text-decoration:underline;}

.yes{color:#16a34a;font-weight:600;}
.no{color:#dc2626;font-weight:600;}

.edit-btn{font-size:18px;text-decoration:none;}

.no-data{text-align:center;padding:30px;color:#64748b;}

/* FILTER DRAWER */
.filter-drawer{
    position:fixed;
    top:0;
    right:-300px;
    width:280px;
    height:100%;
    background:#fff;
    padding:20px;
    box-shadow:-5px 0 15px rgba(0,0,0,0.2);
    transition:0.3s;
    z-index:1000;
}

.filter-drawer.active{
    right:0;
}

.overlay{
    position:fixed;
    top:0;left:0;
    width:100%;height:100%;
    background:rgba(0,0,0,0.3);
    display:none;
}

.overlay.active{display:block;}

.input{
    width:100%;
    padding:10px;
    border:1px solid #ddd;
    border-radius:6px;
    margin-bottom:10px;
}
</style>

<script>
function openFilter(){
    document.getElementById("filterDrawer").classList.add("active");
    document.getElementById("overlay").classList.add("active");
}
function closeFilter(){
    document.getElementById("filterDrawer").classList.remove("active");
    document.getElementById("overlay").classList.remove("active");
}
</script>

<?php include("../includes/footer.php"); ?>