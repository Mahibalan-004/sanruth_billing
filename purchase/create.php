<?php 
require_once("../config/db.php");
include("../includes/header.php");

// Fetch suppliers for the dropdown
$suppliers = mysqli_query($conn, "SELECT id, name FROM supplier WHERE active = 1 ORDER BY name ASC");
?>

<div style="padding: 20px; background: #f8fafc; min-height: calc(100vh - 110px);">
    
    <!-- HEADER BAR -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="margin: 0; color: #1e293b; font-size: 20px; font-weight: 700;">Create Purchase</h3>
        <a href="list.php" style="text-decoration: none; color: #1e293b; font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 6px;">
            <span>☰</span> Purchase List
        </a>
    </div>

    <!-- MAIN CONTENT CARD -->
    <div style="background: #fff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); padding: 30px; border: 1px solid #e2e8f0;">
        
        <form action="insert.php" method="POST">
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Order Number <span class="required">*</span></label>
                    <input type="text" name="orderNo" required placeholder="PO-001">
                </div>
                <div class="form-group">
                    <label>Order Date <span class="required">*</span></label>
                    <input type="date" name="orderDate" required value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Supplier <span class="required">*</span></label>
                    <select name="supplier" required class="form-select">
                        <option value="">Select Supplier</option>
                        <?php while($s = mysqli_fetch_assoc($suppliers)): ?>
                            <option value="<?= $s['id'] ?>"><?= $s['name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Order Status <span class="required">*</span></label>
                    <select name="orderStatus" required class="form-select">
                        <option value="Pending">Pending</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
            </div>

            <!-- AMOUNTS SECTION -->
            <div style="margin-top: 40px; margin-bottom: 20px; border-top: 1px solid #f1f5f9; padding-top: 25px;">
                <h3 style="margin: 0 0 20px 0; color: #1e293b; font-size: 18px; font-weight: 700;">Financial Details</h3>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                <div class="form-group">
                    <label>Quote Amount Sum</label>
                    <input type="number" step="0.01" name="quoteAmountSum" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Actual Amount Sum <span class="required">*</span></label>
                    <input type="number" step="0.01" name="actualAmountSum" required placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Paid Amount Sum</label>
                    <input type="number" step="0.01" name="paidAmountSum" placeholder="0.00">
                </div>
            </div>

            <!-- ACTIONS -->
            <div style="margin-top: 40px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="submit" class="prime-btn" style="background: #2563eb;">Create Purchase</button>
                <button type="reset" class="prime-btn" style="background: #64748b;">Reset</button>
            </div>

        </form>
    </div>
</div>

<style>
    /* TYPOGRAPHY */
    .form-group label {
        display: block;
        font-weight: 700;
        color: #475569;
        margin-bottom: 8px;
        font-size: 14px;
    }
    .required { color: #ef4444; }

    /* INPUTS & SELECTS */
    .form-group input, .form-select {
        width: 100%;
        height: 42px;
        padding: 0 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 14px;
        color: #334155;
        background: #fff;
        transition: 0.2s;
        font-family: inherit;
    }
    .form-group input:focus, .form-select:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    /* GRID LAYOUTS */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    /* BUTTONS */
    .prime-btn {
        padding: 10px 25px;
        border: none;
        border-radius: 6px;
        color: white;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: 0.2s;
    }
    .prime-btn:hover {
        opacity: 0.9;
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
    }
</style>

<?php include("../includes/footer.php"); ?>
