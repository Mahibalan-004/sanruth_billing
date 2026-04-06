<?php require_once("../config/db.php"); ?>
<?php include("../includes/header.php"); ?>

<div class="container">
    <h2>Spare Parts List</h2>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Part No</th>
                <th>Rack</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $res=mysqli_query($conn,"SELECT * FROM spares");

            if(mysqli_num_rows($res) > 0) {
                while($row=mysqli_fetch_assoc($res)){
                    echo "<tr>
                        <td><strong>{$row['id']}</strong></td>
                        <td>{$row['spareName']}</td>
                        <td><code>{$row['partNo']}</code></td>
                        <td>{$row['rackNumber']}</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='4' style='text-align:center; padding: 40px; color: #64748b;'>No spare parts available.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <br>
    <a href="add_spare.php" style="display:inline-block; padding:10px 20px; background:#10b981; color:#fff; text-decoration:none; border-radius:6px; font-weight:600; font-size:14px;">+ Add New Spare</a>
</div>

<style>
    .container {
        width: 95%;
        margin: auto;
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    h2 { margin-bottom: 25px; color: #1e293b; font-weight: 700; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th { background: #f8fafc; color: #64748b; font-weight: 600; padding: 15px; text-align: left; border-bottom: 2px solid #e2e8f0; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; }
    .data-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 14px; }
    .data-table tr:hover { background: #f8fafc; }
    code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #e11d48; font-weight: 500; font-family: monospace; }
</style>

<?php include("../includes/footer.php"); ?>