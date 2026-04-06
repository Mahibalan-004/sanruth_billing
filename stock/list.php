<?php
require_once("../config/db.php");

// ================= FILTER CONDITIONS =================
$where = "WHERE 1=1";

if(!empty($_GET['item'])){
    $item = $_GET['item'];
    $where .= " AND s.spareName LIKE '%$item%'";
}

if(!empty($_GET['part'])){
    $part = $_GET['part'];
    $where .= " AND s.partNo LIKE '%$part%'";
}

if(!empty($_GET['barcode'])){
    $barcode = $_GET['barcode'];
    $where .= " AND st.barCode LIKE '%$barcode%'";
}

if(!empty($_GET['brand'])){
    $brand = $_GET['brand'];
    $where .= " AND st.brand='$brand'";
}

if(!empty($_GET['model'])){
    $model = $_GET['model'];
    $where .= " AND st.model='$model'";
}

if(isset($_GET['selled']) && $_GET['selled'] != ''){
    $selled = $_GET['selled'];
    $where .= " AND st.selled = b'$selled'";
}

// ================= QUERY =================
$query = "
SELECT st.*, 
s.spareName, s.partNo, s.rackNumber,
b.brandName,
m.model
FROM stock st
LEFT JOIN spares s ON st.spare = s.id
LEFT JOIN brand b ON st.brand = b.id
LEFT JOIN model m ON st.model = m.id
$where
ORDER BY st.id DESC
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
<title>Stock List</title>

<style>
body{font-family:Arial;}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    padding:10px;
    border-bottom:1px solid #ddd;
    text-align:left;
}

th{
    background:#f5f5f5;
}

.filter-box{
    width:300px;
    padding:15px;
    border:1px solid #ccc;
}
</style>
</head>

<body>

<h2>Manage Stocks</h2>

<div style="display:flex; gap:20px;">

<!-- ================= FILTER ================= -->
<form method="GET" class="filter-box">

<label>Item Name</label>
<input type="text" name="item" value="<?= $_GET['item'] ?? '' ?>">

<br><br>

<label>Part #</label>
<input type="text" name="part" value="<?= $_GET['part'] ?? '' ?>">

<br><br>

<label>Barcode</label>
<input type="text" name="barcode" value="<?= $_GET['barcode'] ?? '' ?>">

<br><br>

<label>Brand</label>
<select name="brand">
<option value="">Select</option>
<?php
$res=mysqli_query($conn,"SELECT * FROM brand");
while($r=mysqli_fetch_assoc($res)){
    $selected = ($_GET['brand'] ?? '')==$r['id'] ? "selected" : "";
    echo "<option value='{$r['id']}' $selected>{$r['brandName']}</option>";
}
?>
</select>

<br><br>

<label>Model</label>
<select name="model">
<option value="">Select</option>
<?php
$res=mysqli_query($conn,"SELECT * FROM model");
while($r=mysqli_fetch_assoc($res)){
    $selected = ($_GET['model'] ?? '')==$r['id'] ? "selected" : "";
    echo "<option value='{$r['id']}' $selected>{$r['model']}</option>";
}
?>
</select>

<br><br>

<label>Selled</label>
<select name="selled">
<option value="">All</option>
<option value="1" <?= (($_GET['selled'] ?? '')=='1')?'selected':'' ?>>Yes</option>
<option value="0" <?= (($_GET['selled'] ?? '')=='0')?'selected':'' ?>>No</option>
</select>

<br><br>

<button type="submit">Apply</button>
<a href="list.php">Clear</a>

</form>


<!-- ================= TABLE ================= -->
<div style="flex:1">

<table>

<tr>
<th>#</th>
<th>Barcode</th>
<th>Item Name</th>
<th>Part #</th>
<th>Rack</th>
<th>Brand</th>
<th>Model</th>
<th>Available Qty</th>
<th>Selling</th>
<th>Selled</th>
<th>Status</th>
</tr>

<?php
$i=1;
while($row=mysqli_fetch_assoc($result)){
?>

<tr>
<td><?= $i++ ?></td>

<td>
<svg id="barcode<?= $row['id'] ?>"></svg><br>
<?= $row['barCode'] ?>
</td>

<td><?= $row['spareName'] ?></td>
<td><?= $row['partNo'] ?></td>
<td><?= $row['rackNumber'] ?></td>
<td><?= $row['brandName'] ?></td>
<td><?= $row['model'] ?></td>
<td><?= $row['availableQty'] ?></td>
<td><?= $row['sellingPricePerUnit'] ?></td>
<td><?= $row['selledPricePerUnit'] ?></td>
<td><?= $row['selled'] ? 'Yes' : 'No' ?></td>

</tr>

<script>
JsBarcode("#barcode<?= $row['id'] ?>", "<?= $row['barCode'] ?>", {
    format: "CODE128",
    width: 1.5,
    height: 40,
    displayValue: false
});
</script>

<?php } ?>

</table>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

</body>
</html>