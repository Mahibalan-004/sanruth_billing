<?php
require_once("../config/db.php");
require_once("../includes/auth.php");
requireAdmin();
include("../includes/header.php");

// Handle Actions (Add / Update)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $brandName = mysqli_real_escape_string($conn, $_POST['brandName']);
        $now = date('Y-m-d H:i:s');
        $user = "System Admin";

        if ($_POST['action'] == 'add' && !empty($brandName)) {
            // CHECK FOR DUPLICATE
            $check = mysqli_query($conn, "SELECT id FROM brand WHERE brandName = '$brandName'");
            if (mysqli_num_rows($check) > 0) {
                echo "<script>alert('Error: This brand name already exists!'); window.location.href='list.php';</script>";
                exit();
            }
            $sql = "INSERT INTO brand (brandName, createdBy, createdOn, modifiedBy, modifiedOn) VALUES ('$brandName', '$user', '$now', '$user', '$now')";
            mysqli_query($conn, $sql);
        } elseif ($_POST['action'] == 'update' && isset($_POST['id'])) {
            $id = (int) $_POST['id'];
            // CHECK FOR DUPLICATE (excluding current ID)
            $check = mysqli_query($conn, "SELECT id FROM brand WHERE brandName = '$brandName' AND id != $id");
            if (mysqli_num_rows($check) > 0) {
                echo "<script>alert('Error: Another brand with this name already exists!'); window.location.href='list.php';</script>";
                exit();
            }
            $sql = "UPDATE brand SET brandName = '$brandName', modifiedBy = '$user', modifiedOn = '$now' WHERE id = $id";
            mysqli_query($conn, $sql);
        }
        // Redirect to avoid form resubmission
        echo "<script>window.location.href='list.php';</script>";
        exit();
    }
}

// Fetch Brands
$brands = mysqli_query($conn, "SELECT * FROM brand ORDER BY id ASC");
?>

<div class="container" style="max-width: 700px; margin: 40px auto; padding: 0 15px;">
    <h2 style="color: #1e293b; font-weight: 700; margin-bottom: 25px;">Manage Brands</h2>

    <div
        style="background: #fff; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 15px; text-align: left; width: 60px; color: #475569; font-weight: 700;">#</th>
                    <th style="padding: 15px; text-align: left; color: #475569; font-weight: 700;">Type</th>
                    <th style="padding: 15px; text-align: right; width: 150px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1;
                while ($brand = mysqli_fetch_assoc($brands)): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;"
                        onmouseover="this.style.background='#fbfcfe'" onmouseout="this.style.background='white'">
                        <td style="padding: 12px 15px; color: #64748b; font-size: 14px;"><?= $i++ ?></td>
                        <td colspan="2" style="padding: 12px 15px;">
                            <!-- Form for each row -->
                            <form action="list.php" method="POST"
                                style="display: flex; gap: 15px; width: 100%; align-items: center;">
                                <input type="hidden" name="id" value="<?= $brand['id'] ?>">
                                <input type="hidden" name="action" value="update">
                                <input type="text" name="brandName" value="<?= htmlspecialchars($brand['brandName']) ?>"
                                    required
                                    style="flex: 1; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; font-size: 14px; background: #fff; transition: 0.2s;">
                                <button type="submit"
                                    style="background: #64748b; color: #fff; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 700; font-size: 13px; cursor: pointer; transition: 0.2s;">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- ADD NEW BRAND (Footer-like row) -->
        <div style="background: #f8fafc; padding: 20px; border-top: 2px solid #e2e8f0;">
            <form action="list.php" method="POST" style="display: flex; gap: 15px; width: 100%; align-items: center;">
                <input type="hidden" name="action" value="add">
                <input type="text" name="brandName" placeholder="Brand Name" required
                    style="flex: 1; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 12px 15px; font-size: 14px; background: #fff; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">
                <button type="submit"
                    style="background: #2563eb; color: #fff; border: none; padding: 12px 35px; border-radius: 6px; font-weight: 700; font-size: 14px; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);">Add</button>
            </form>
        </div>
    </div>
</div>

<style>
    input:focus {
        outline: none;
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
    }

    button:hover {
        filter: brightness(1.1);
        transform: translateY(-1px);
    }

    button:active {
        transform: translateY(0);
    }
</style>

<?php include("../includes/footer.php"); ?>