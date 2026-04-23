<?php 
require_once("../config/db.php");
include("../includes/header.php");

// Generate Card #
$tempCardNo = "JC-" . strtoupper(substr(md5(time()), 0, 8));
$currentDate = date('d/m/Y');

// Fetch Machines for dropdown
$machines = mysqli_query($conn, "SELECT id, machineName FROM machine WHERE active = 1");
?>

<div style="padding: 20px; background: #f8fafc; min-height: calc(100vh - 110px);">
    
    <!-- HEADER BAR -->
    <div style="background: #1e7e34; height: 50px; display: flex; align-items: center; justify-content: space-between; border-radius: 8px 8px 0 0; overflow: hidden; padding: 0 20px;">
        <div style="color: #fff; font-weight: 700; font-size: 18px;">Job Card</div>
        <a href="list.php" style="color: #fff; text-decoration: none; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 5px;">
            <span>☰</span> Job Cards
        </a>
    </div>

    <!-- MAIN CARD -->
    <div style="background: #fff; border-radius: 0 0 12px 12px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); padding: 25px; border: 1px solid #e2e8f0; border-top: none;">
        
        <form id="jobCardForm" action="insert_jobcard.php" method="POST">
            
            <!-- CARD INFO -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                <div class="form-group">
                    <label>Card #</label>
                    <input type="text" value="<?= $tempCardNo ?>" disabled style="background: #f1f5f9;">
                    <input type="hidden" name="cardNo" value="<?= $tempCardNo ?>">
                </div>
                <div class="form-group">
                    <label>Date <span class="required">*</span></label>
                    <input type="text" name="givenDate" value="<?= $currentDate ?>" required style="background: #f1f5f9; cursor: pointer;" onclick="(this.type='date')" onblur="(this.type='text')">
                </div>
            </div>

            <!-- CUSTOMER INFO SECTION -->
            <div style="background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 20px; margin-bottom: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h4 style="margin: 0; color: #1e293b; font-size: 14px; font-weight: 700; text-transform: uppercase;">Customer Info:</h4>
                    <button type="button" onclick="clearCustomer()" style="background: #f59e0b; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 700; font-size: 11px; cursor: pointer;">Clear Customer Info</button>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Phone # Primary <span class="required">*</span></label>
                        <input type="text" name="customerPhone" id="customerPhone" placeholder="Enter Phone Number..." onkeyup="searchCustomer(this.value)">
                    </div>
                    <div class="form-group">
                        <label>Name <span class="required">*</span></label>
                        <input type="text" name="customerName" id="customerName" placeholder="Customer Name">
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" id="city" placeholder="City">
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px;">
                <!-- JOB DETAIL SECTION -->
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px;">
                    <h4 style="margin-bottom: 25px; color: #334155; font-size: 16px; font-weight: 700;">Job Card Item #: 1</h4>
                    
                    <div style="display: flex; gap: 25px; margin-bottom: 25px;">
                        <!-- Thumbnail placeholder -->
                        <div style="width: 100px; height: 100px; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 32px; color: #94a3b8; cursor: pointer;">🖼️</div>
                        
                        <div style="flex: 1; display: flex; flex-direction: column; gap: 15px;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div class="form-group">
                                    <label>Machine <span class="required">*</span></label>
                                    <select name="machine" required style="width: 100%; height: 42px; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0 12px;">
                                        <option value="">Select Machine</option>
                                        <?php while($m = mysqli_fetch_assoc($machines)): ?>
                                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['machineName']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Serial #</label>
                                    <input type="text" name="serial" placeholder="Serial Number" style="height: 42px;">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Work Details</label>
                                <select name="workDetails" style="width: 100%; height: 42px; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0 12px;">
                                    <option value="Service">Service</option>
                                    <option value="Repair">Repair</option>
                                    <option value="Replacement">Replacement</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea name="remarks" placeholder="Enter service/repair remarks..." style="width: 100%; height: 100px; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 12px; font-size: 14px; color: #334155; resize: none;"></textarea>
                    </div>
                </div>

                <!-- RIGHT SIDE (LOGO PLACEHOLDER) -->
                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; display: flex; align-items: center; justify-content: center; padding: 10px;">
                    <img src="../img/logo.png" alt="SANRUTH MACHINES" style="max-width: 100%; height: auto; border-radius: 10px; max-height: 280px; object-fit: contain;">
                </div>
            </div>

            <div style="margin-top: 50px; display: flex; justify-content: flex-end; gap: 15px;">
                <button type="submit" style="background: #3b82f6; color: white; padding: 12px 35px; border: none; border-radius: 8px; font-weight: 700; font-size: 15px; cursor: pointer;">Submit</button>
                <button type="reset" style="background: #64748b; color: white; padding: 12px 35px; border: none; border-radius: 8px; font-weight: 700; font-size: 15px; cursor: pointer;">Reset</button>
            </div>

        </form>
    </div>
</div>

<style>
    .form-group label { display: block; font-weight: 700; color: #1e293b; margin-bottom: 8px; font-size: 14px; }
    .form-group input { width: 100%; height: 42px; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0 15px; font-size: 14px; transition: 0.2s; }
    .required { color: #ef4444; }
    input:focus, select:focus, textarea:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
</style>

<script>
function clearCustomer() {
    ['customerPhone', 'customerName', 'city'].forEach(id => document.getElementById(id).value = '');
}

function searchCustomer(phone) {
    if(phone.length >= 10) {
        // Implement auto-search logic here
    }
}
</script>

<?php include("../includes/footer.php"); ?>
