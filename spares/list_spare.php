<?php require_once("../config/db.php"); ?>

<table border="1" cellpadding="10">
<tr>
<th>ID</th>
<th>Name</th>
<th>Part No</th>
<th>Rack</th>
</tr>

<?php
$res=mysqli_query($conn,"SELECT * FROM spares");

while($row=mysqli_fetch_assoc($res)){
echo "<tr>
<td>{$row['id']}</td>
<td>{$row['spareName']}</td>
<td>{$row['partNo']}</td>
<td>{$row['rackNumber']}</td>
</tr>";
}
?>

</table>