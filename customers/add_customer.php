<?php 
require_once("../config/db.php");
include("../includes/header.php");

// Generate a random Customer ID for display (System Generated)
$tempCustomerId = "CUS-" . strtoupper(substr(md5(time()), 0, 8));
?>

<div style="padding: 20px; background: #f8fafc; min-height: calc(100vh - 110px);">
    
    <!-- HEADER BAR (ERP STYLE) -->
    

    <!-- MAIN CONTENT CARD -->
    <div style="background: #fff; border-radius: 0 0 12px 12px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); padding: 35px; border: 1px solid #e2e8f0; border-top: none;">
        
        <form action="insert_customer.php" method="POST">
            
            <!-- SECTION 1: CUSTOMER INFO -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px;">
                <h3 style="margin: 0; color: #334155; font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                    Customer Info
                </h3>
                <div style="display: flex; align-items: center; gap: 20px;">
                    <!-- ACTIVE TOGGLE -->
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <span style="font-weight: 700; color: #64748b; font-size: 13px;">Active</span>
                        <div class="switch-container">
                            <input type="checkbox" name="active" checked id="activeSwitch" hidden>
                            <div class="switch-slider"></div>
                        </div>
                    </label>
                    <a href="c_list.php" style="text-decoration: none; color: #1e293b; font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 6px; padding: 8px 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; transition: 0.2s;">
                        <span>☰</span> Customers
                    </a>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Name <span class="required">*</span></label>
                    <input type="text" name="name" required placeholder="Full Name">
                </div>
                <div class="form-group">
                    <label>Customer ID <span class="system-gen">System Generated</span></label>
                    <input type="text" value="<?= $tempCustomerId ?>" disabled class="disabled-input">
                    <input type="hidden" name="customerId" value="<?= $tempCustomerId ?>">
                </div>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label>Phone # Primary <span class="required">*</span></label>
                    <input type="text" name="phoneNo1" required placeholder="00000 00000">
                </div>
                <div class="form-group">
                    <label>Phone # WhatsApp</label>
                    <input type="text" name="whatsAppNo" placeholder="WhatsApp Number">
                </div>
                <div class="form-group">
                    <label>Email ID</label>
                    <input type="email" name="emailId" placeholder="example@mail.com">
                </div>
            </div>

            <!-- SECTION 2: ADDRESS -->
            <div style="margin-top: 40px; margin-bottom: 25px; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px;">
                <h3 style="margin: 0; color: #334155; font-size: 18px; font-weight: 700;">Address</h3>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Line 1</label>
                    <input type="text" name="line1" placeholder="Building / Street">
                </div>
                <div class="form-group">
                    <label>Line 2</label>
                    <input type="text" name="line2" placeholder="Locality / Area">
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" placeholder="City">
                </div>
                <div class="form-group">
                    <label>Zip Code</label>
                    <input type="text" name="zipCode" placeholder="000 000">
                </div>
            </div>

            <!-- ACTIONS -->
            <div style="margin-top: 50px; display: flex; justify-content: flex-end; gap: 15px;">
                <button type="submit" class="prime-btn" style="background: #3b82f6;">Submit</button>
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
        color: #1e293b;
        margin-bottom: 10px;
        font-size: 14px;
    }
    .required { color: #ef4444; }
    .system-gen { color: #94a3b8; font-weight: 500; font-size: 12px; margin-left: 5px; }

    /* INPUTS */
    .form-group input {
        width: 100%;
        height: 48px;
        padding: 0 15px;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
        color: #334155;
        background: #fff;
        transition: 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .form-group input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
    .disabled-input {
        background: #f1f5f9 !important;
        color: #94a3b8 !important;
        cursor: not-allowed;
    }

    /* GRID LAYOUTS */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-bottom: 25px;
    }
    .form-grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 25px;
        margin-bottom: 25px;
    }

    /* SWITCH TOGGLE */
    .switch-container {
        width: 50px;
        height: 26px;
        position: relative;
    }
    .switch-slider {
        position: absolute;
        inset: 0;
        background: #e2e8f0;
        border-radius: 30px;
        transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .switch-slider::before {
        content: "";
        position: absolute;
        height: 18px;
        width: 18px;
        left: 4px;
        top: 4px;
        background: white;
        border-radius: 50%;
        transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    #activeSwitch:checked + .switch-slider { background: #8b5cf6; }
    #activeSwitch:checked + .switch-slider::before { transform: translateX(24px); }

    /* BUTTONS */
    .prime-btn {
        padding: 12px 35px;
        border: none;
        border-radius: 8px;
        color: white;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: 0.2s;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .prime-btn:hover {
        transform: translateY(-2px);
        filter: brightness(1.1);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .prime-btn:active {
        transform: translateY(0);
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .form-grid, .form-grid-3 { grid-template-columns: 1fr; }
    }
</style>

<?php include("../includes/footer.php"); ?>
