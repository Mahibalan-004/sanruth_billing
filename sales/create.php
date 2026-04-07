<?php 
require_once("../config/db.php");
include("../includes/header.php");

// Generate a random Order Number
$tempOrderNo = "ORD-" . strtoupper(substr(md5(time()), 0, 8));
$currentDate = date('Y-m-d');
?>

<div style="padding: 20px; background: #f8fafc; min-height: calc(100vh - 110px);">
    
    <!-- HEADER BAR -->
    <div style="background: #1e7e34; height: 50px; display: flex; align-items: center; justify-content: space-between; border-radius: 8px 8px 0 0; overflow: hidden; padding: 0 20px;">
        <div style="color: #fff; font-weight: 700; font-size: 18px;">Sales Order</div>
        <a href="list.php" style="color: #fff; text-decoration: none; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 5px;">
            <span>☰</span> Sales Orders
        </a>
    </div>

    <!-- MAIN FORM CARD -->
    <div style="background: #fff; border-radius: 0 0 12px 12px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); padding: 25px; border: 1px solid #e2e8f0; border-top: none;">
        
        <form id="salesForm" action="insert_sales.php" method="POST">
            
            <!-- ORDER HEADER -->
            <div class="form-grid-3" style="margin-bottom: 30px;">
                <div class="form-group">
                    <label>Order # <span class="system-gen">Generated</span></label>
                    <input type="text" value="<?= $tempOrderNo ?>" disabled class="disabled-input">
                    <input type="hidden" name="orderNo" value="<?= $tempOrderNo ?>">
                </div>
                <div class="form-group">
                    <label>Order Status</label>
                    <select name="orderStatus" style="width: 100%; height: 48px; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0 15px;">
                        <option value="New">New</option>
                        <option value="Pending">Pending</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Order Date <span class="required">*</span></label>
                    <input type="date" name="orderDate" value="<?= $currentDate ?>" required>
                </div>
            </div>

            <!-- CUSTOMER INFO SECTION -->
            <div style="background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 20px; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h4 style="margin: 0; color: #1e293b; font-size: 15px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Customer Info:</h4>
                    <button type="button" onclick="clearCustomer()" style="background: #f59e0b; color: #fff; border: none; padding: 6px 15px; border-radius: 6px; font-weight: 700; font-size: 11px; cursor: pointer;">Clear Customer Info</button>
                </div>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px;">
                    <!-- Left: Contact info -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Phone # Primary <span class="required">*</span></label>
                            <input type="text" name="customerPhone" id="customerPhone" placeholder="Search by Phone..." onkeyup="searchCustomer(this.value)">
                        </div>
                        <div class="form-group">
                            <label>Phone # WhatsApp</label>
                            <input type="text" name="customerWhatsApp" id="customerWhatsApp" placeholder="WhatsApp Number">
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label>Name <span class="required">*</span></label>
                            <input type="text" name="customerName" id="customerName" placeholder="Customer Full Name">
                        </div>
                    </div>
                    
                    <!-- Right: Address -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group" style="grid-column: span 2;">
                            <label>Address: Line 1</label>
                            <input type="text" name="addressLine1" id="addressLine1" placeholder="Building/Street">
                        </div>
                        <div class="form-group">
                            <label>Line 2</label>
                            <input type="text" name="addressLine2" id="addressLine2" placeholder="Locality">
                        </div>
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="city" id="city" placeholder="City">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ITEMS TABLE -->
            <div style="margin-bottom: 30px; overflow-x: auto;">
                <table id="itemsTable" style="width: 100%; border-collapse: collapse; min-width: 1000px;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 12px; width: 40px;">#</th>
                            <th style="padding: 12px; width: 80px;">Image</th>
                            <th style="padding: 12px;">Item</th>
                            <th style="padding: 12px; width: 150px;">Barcode</th>
                            <th style="padding: 12px; width: 150px;">Part/Serial #</th>
                            <th style="padding: 12px; width: 80px;">Rack #</th>
                            <th style="padding: 12px; width: 80px;">Qty</th>
                            <th style="padding: 12px; width: 110px;">Price/Qty</th>
                            <th style="padding: 12px; width: 70px;">GST %</th>
                            <th style="padding: 12px; width: 120px;">Total Price</th>
                            <th style="padding: 12px; width: 80px; text-align: center;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <!-- Rows will be added here -->
                    </tbody>
                </table>
                
                <!-- Totals Row -->
                <div style="display: flex; justify-content: flex-end; margin-top: 15px;">
                    <div style="width: 300px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-weight: 700; color: #475569;">
                            <span>Total Rs:</span>
                            <span id="grandTotal">0.00</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-weight: 700; color: #475569;">
                            <span>Paid Rs:</span>
                            <span id="totalPaid">0.00</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-weight: 700; color: #1e293b; border-top: 1px solid #cbd5e1; pt: 10px;">
                            <span>Balance Rs:</span>
                            <span id="balanceAmount">0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PAYMENT SECTION -->
            <div style="background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 20px; margin-bottom: 30px;">
                <h4 style="margin: 0 0 20px 0; color: #1e293b; font-size: 15px; font-weight: 700; text-transform: uppercase;">Payment:</h4>
                <div id="paymentsList">
                    <div class="form-grid-4 payment-row" style="margin-bottom: 15px;">
                        <div class="form-group">
                            <label>Paid On</label>
                            <input type="date" name="paymentDate[]" value="<?= $currentDate ?>">
                        </div>
                        <div class="form-group">
                            <label>Mode</label>
                            <select name="paymentMode[]" style="width: 100%; height: 48px; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0 15px;">
                                <option value="Cash">Cash</option>
                                <option value="Card">Card</option>
                                <option value="UPI">UPI</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Ref No</label>
                            <input type="text" name="paymentRef[]" placeholder="Txn ID / Ref">
                        </div>
                        <div class="form-group">
                            <label>Amount</label>
                            <input type="number" name="paymentAmount[]" value="0" class="pay-amount" onkeyup="updateTotals()">
                        </div>
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end;">
                    <button type="button" onclick="addPaymentRow()" style="background: #10b981; color: #fff; border: none; padding: 10px 25px; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer;">Add Payment</button>
                </div>
            </div>

            <!-- FOOTER ACTIONS -->
            <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 50px; border-top: 2px solid #f1f5f9; pt: 30px;">
                <button type="submit" class="prime-btn" style="background: #3b82f6;">Submit</button>
                <button type="reset" class="prime-btn" style="background: #64748b;">Reset</button>
            </div>

        </form>
    </div>
</div>

<template id="itemRowTemplate">
    <tr style="border-bottom: 1px solid #f1f5f9;">
        <td class="row-num" style="padding: 12px; font-size: 13px; color: #94a3b8; font-weight: 600;">1</td>
        <td style="padding: 12px;">
            <div style="width: 40px; height: 40px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 20px;">🖼️</div>
        </td>
        <td style="padding: 12px;">
            <input type="text" name="item[]" required style="width: 100%; height: 38px; border: 1.5px solid #e2e8f0; border-radius: 6px; padding: 0 10px;" placeholder="Search Item..." onkeyup="searchItem(this)">
        </td>
        <td style="padding: 12px;">
            <input type="text" name="barcode[]" style="width: 100%; height: 38px; border: 1.5px solid #e2e8f0; border-radius: 6px; padding: 0 10px;">
        </td>
        <td style="padding: 12px;">
            <input type="text" name="serial[]" style="width: 100%; height: 38px; border: 1.5px solid #e2e8f0; border-radius: 6px; padding: 0 10px;">
        </td>
        <td style="padding: 12px;">
            <input type="text" name="rack[]" style="width: 100%; height: 38px; border: 1.5px solid #e2e8f0; border-radius: 6px; padding: 0 10px;">
        </td>
        <td style="padding: 12px;">
            <input type="number" name="qty[]" value="1" min="1" style="width: 100%; height: 38px; border: 1.5px solid #e2e8f0; border-radius: 6px; padding: 0 10px;" onchange="updateRowTotal(this)">
        </td>
        <td style="padding: 12px;">
            <input type="number" name="price[]" value="0" style="width: 100%; height: 38px; border: 1.5px solid #e2e8f0; border-radius: 6px; padding: 0 10px;" onkeyup="updateRowTotal(this)">
        </td>
        <td style="padding: 12px;">
            <input type="number" name="gst[]" value="0" style="width: 100%; height: 38px; border: 1.5px solid #e2e8f0; border-radius: 6px; padding: 0 10px;" onkeyup="updateRowTotal(this)">
        </td>
        <td style="padding: 12px;">
            <input type="text" name="total[]" value="0.00" readonly style="width: 100%; height: 38px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0 10px; font-weight: 700; color: #475569;">
        </td>
        <td style="padding: 12px; text-align: center;">
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button type="button" onclick="removeItemRow(this)" style="color: #ef4444; background: none; border: none; font-size: 18px; cursor: pointer;">🗑️</button>
                <button type="button" onclick="addItemRow()" style="color: #334155; background: none; border: none; font-size: 18px; cursor: pointer;">➕</button>
            </div>
        </td>
    </tr>
</template>

<style>
    .form-group label { display: block; font-weight: 700; color: #1e293b; margin-bottom: 8px; font-size: 14px; }
    .form-group input, .form-group select { width: 100%; height: 48px; padding: 0 15px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 14px; transition: 0.2s; }
    .form-group input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
    .disabled-input { background: #f1f5f9; cursor: not-allowed; }
    .required { color: #ef4444; }
    .system-gen { color: #94a3b8; font-weight: 500; font-size: 12px; }
    .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
    .form-grid-4 { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 20px; }
    .prime-btn { padding: 12px 35px; border: none; border-radius: 8px; color: white; font-weight: 700; font-size: 15px; cursor: pointer; }
</style>

<script>
function addItemRow() {
    const tbody = document.getElementById('itemsBody');
    const template = document.getElementById('itemRowTemplate');
    const clone = template.content.cloneNode(true);
    tbody.appendChild(clone);
    updateRowNumbers();
}

function updateRowNumbers() {
    document.querySelectorAll('.row-num').forEach((td, index) => {
        td.textContent = index + 1;
    });
}

function removeItemRow(btn) {
    const row = btn.closest('tr');
    if (document.querySelectorAll('#itemsBody tr').length > 1) {
        row.remove();
        updateRowNumbers();
        updateTotals();
    }
}

function addPaymentRow() {
    const container = document.getElementById('paymentsList');
    const firstRow = container.querySelector('.payment-row');
    const clone = firstRow.cloneNode(true);
    clone.querySelectorAll('input').forEach(input => {
        if(input.type != 'date') input.value = '';
        if(input.classList.contains('pay-amount')) input.value = '0';
    });
    container.appendChild(clone);
}

function updateRowTotal(el) {
    const row = el.closest('tr');
    const qty = parseFloat(row.querySelector('[name="qty[]"]').value) || 0;
    const price = parseFloat(row.querySelector('[name="price[]"]').value) || 0;
    const gst = parseFloat(row.querySelector('[name="gst[]"]').value) || 0;
    
    const totalWithoutGst = qty * price;
    const gstVal = totalWithoutGst * (gst / 100);
    const total = totalWithoutGst + gstVal;
    
    row.querySelector('[name="total[]"]').value = total.toFixed(2);
    updateTotals();
}

function updateTotals() {
    let grandTotal = 0;
    document.querySelectorAll('[name="total[]"]').forEach(input => {
        grandTotal += parseFloat(input.value) || 0;
    });
    
    let totalPaid = 0;
    document.querySelectorAll('.pay-amount').forEach(input => {
        totalPaid += parseFloat(input.value) || 0;
    });
    
    document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
    document.getElementById('totalPaid').textContent = totalPaid.toFixed(2);
    document.getElementById('balanceAmount').textContent = (grandTotal - totalPaid).toFixed(2);
}

function clearCustomer() {
    ['customerPhone', 'customerWhatsApp', 'customerName', 'addressLine1', 'addressLine2', 'city'].forEach(id => {
        document.getElementById(id).value = '';
    });
}

function searchCustomer(phone) {
    if(phone.length >= 10) {
        // Here we would normally do an AJAX call
        // For now, it's a placeholder
        console.log("Searching for " + phone);
    }
}

// Init one row
window.onload = function() {
    addItemRow();
};
</script>

<?php include("../includes/footer.php"); ?>
