<?php
require_once("../config/db.php");

// ================= VALIDATE ID =================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Access");
}

$id = (int)$_GET['id'];

if ($id <= 0) {
    die("Invalid Stock ID");
}

// ================= FETCH DATA =================
$query = "
SELECT st.*, 
s.spareName, s.partNo, s.rackNumber, s.picture,
b.brandName, m.model as modelName
FROM stock st
LEFT JOIN spares s ON st.spare = s.id
LEFT JOIN brand b ON st.brand = b.id
LEFT JOIN model m ON st.model = m.id
WHERE st.id = $id
LIMIT 1
";

$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Stock Not Found");
}

$data = mysqli_fetch_assoc($result);

// ================= UPDATE =================
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $qty     = isset($_POST['qty']) ? (int)$_POST['qty'] : 0;
    $actual  = isset($_POST['actual']) ? (float)$_POST['actual'] : 0;
    $selling = isset($_POST['selling']) ? (float)$_POST['selling'] : 0;

    if ($qty < 0 || $actual < 0 || $selling < 0) {
        die("Invalid input values");
    }

    $update = "
    UPDATE stock SET
        availableQty = $qty,
        actualPricePerUnit = $actual,
        selledPricePerUnit = $selling
    WHERE id = $id
    LIMIT 1
    ";

    if (mysqli_query($conn, $update)) {
        echo "<script>
            alert('Stock Updated Successfully');
            window.location='list.php';
        </script>";
        exit;
    } else {
        die("Update Failed: " . mysqli_error($conn));
    }
}
?>

<?php include("../includes/header.php"); ?>

<style>
    body {
        background: #f1f5f9;
        font-family: sans-serif;
    }

    .container {
        width: 95%;
        margin: auto;
    }

    .card {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
    }

    .grid {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 20px;
    }

    .input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
        margin-bottom: 10px;
    }

    .row {
        display: flex;
        gap: 15px;
    }

    .col {
        flex: 1;
    }

    .title {
        font-weight: 700;
        margin: 10px 0;
    }

    img {
        width: 100%;
        border-radius: 10px;
    }

    .btn {
        padding: 10px 20px;
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }
</style>

<div class="container">

    <h2>Stock Info</h2>

    <div class="card">

        <form method="POST">

            <div class="grid">

                <!-- LEFT IMAGE -->
                <div>
                    <?php if (!empty($data['picture']) && file_exists("../uploads/spares/" . $data['picture'])): ?>
                        <img src="../uploads/spares/<?= htmlspecialchars($data['picture']) ?>">
                    <?php else: ?>
                        <img src="../uploads/spares/no-image.png">
                    <?php endif; ?>
                </div>

                <!-- RIGHT FORM -->
                <div>

                    <label>Item Name *</label>
                    <input class="input" value="<?= htmlspecialchars($data['spareName']) ?>" disabled>

                    <div class="row">
                        <div class="col">
                            <label>Rack #</label>
                            <input class="input" value="<?= htmlspecialchars($data['rackNumber']) ?>" disabled>
                        </div>

                        <div class="col">
                            <label>Part #</label>
                            <input class="input" value="<?= htmlspecialchars($data['partNo']) ?>" disabled>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label>Brand</label>
                            <input class="input" value="<?= htmlspecialchars($data['brandName']) ?>" disabled>
                        </div>

                        <div class="col">
                            <label>Model</label>
                            <input class="input" value="<?= htmlspecialchars($data['modelName']) ?>" disabled>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label>Quantity *</label>
                            <input type="number" name="qty" class="input" value="<?= (int)$data['availableQty'] ?>" required>
                        </div>

                        <div class="col">
                            <label>Barcode</label>
                            <input class="input" value="<?= htmlspecialchars($data['barCode']) ?>" disabled>
                        </div>
                    </div>

                    <!-- PRICE -->
                    <div class="title">Price Per Unit</div>

                    <div class="row">
                        <div class="col">
                            <label>Actual Price</label>
                            <input type="number" step="0.01" name="actual" class="input" value="<?= (float)$data['actualPricePerUnit'] ?>" required>
                        </div>

                        <div class="col">
                            <label>Selling Price</label>
                            <input type="number" step="0.01" name="selling" class="input" value="<?= (float)$data['selledPricePerUnit'] ?>" required>
                        </div>

                        <div class="col">
                            <label>GST %</label>
                            <input class="input" value="<?= (float)($data['gstPercentage'] ?? 0) ?>" disabled>
                        </div>
                    </div>

                    <!-- TOTAL -->
                    <div class="title">Total Price</div>

                    <div class="row">
                        <div class="col">
                            <input class="input" value="<?= $data['actualPricePerUnit'] * $data['availableQty'] ?>" disabled>
                        </div>

                        <div class="col">
                            <input class="input" value="<?= $data['selledPricePerUnit'] * $data['availableQty'] ?>" disabled>
                        </div>

                        <div class="col">
                            <input class="input" value="<?= $data['selledPricePerUnit'] ?>" disabled>
                        </div>
                    </div>

                    <!-- BARCODE -->
                    <div style="margin-top:20px;">
                        <svg id="barcode"></svg>
                        <p><?= htmlspecialchars($data['barCode']) ?></p>
                        <button type="button" onclick="window.print()">Print</button>
                    </div>

                </div>
            </div>

            <br>

            <button class="btn">Update Stock</button>

        </form>

    </div>
</div>

<!-- BARCODE JS -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode/dist/JsBarcode.all.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        JsBarcode("#barcode", "<?= $data['barCode'] ?>", {
            height: 50,
            displayValue: false
        });
    });
</script>

<?php include("../includes/footer.php"); ?>