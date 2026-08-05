<?php
require_once("../config/db.php");
include("../includes/header.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentDate = date('d/m/Y');
$currentYM = date('ym'); // e.g. 2608 for Aug 2026

// Generate Card # formatted as 2608J00001 (without slashes or spaces)
if (empty($_SESSION['draft_jobcard_no']) || ($_SESSION['draft_jobcard_ym'] ?? '') !== $currentYM) {
    $prefix = "{$currentYM}J";
    $oldPrefix1 = date('y/m') . "/J/";
    $oldPrefix2 = date('ym') . "J";
    $oldPrefix3 = date('ym') . " J";
    
    $cardResult = mysqli_query($conn, "SELECT cardNo FROM jobcard WHERE cardNo LIKE '$prefix%' OR cardNo LIKE '$oldPrefix1%' OR cardNo LIKE '$oldPrefix2%' OR cardNo LIKE '$oldPrefix3%' ORDER BY id DESC LIMIT 1");
    $lastNum = 0;
    if ($cardResult && $row = mysqli_fetch_assoc($cardResult)) {
        $latestCardNo = trim($row['cardNo']);
        if (preg_match('/(\d+)$/', $latestCardNo, $matches)) {
            $lastNum = intval($matches[1]);
        }
    }
    $nextNum = $lastNum + 1;
    $_SESSION['draft_jobcard_no'] = $prefix . str_pad((string) $nextNum, 5, '0', STR_PAD_LEFT);
    $_SESSION['draft_jobcard_ym'] = $currentYM;
}

$tempCardNo = $_SESSION['draft_jobcard_no'];

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
                    <input type="text" value="<?= htmlspecialchars($tempCardNo) ?>" readonly style="background: #f1f5f9; font-weight: 700; color: #1e293b;">
                    <input type="hidden" name="cardNo" value="<?= htmlspecialchars($tempCardNo) ?>">
                </div>
                <div class="form-group">
                    <label>Date <span class="required">*</span></label>
                    <input type="text" name="givenDate" value="<?= $currentDate ?>" required style="background: #f1f5f9; cursor: pointer;" onclick="(this.type='date')" onblur="(this.type='text')">
                </div>
            </div>

            <!-- CUSTOMER INFO SECTION -->
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h4 style="margin: 0; color: #1e293b; font-size: 14px; font-weight: 700; text-transform: uppercase;">Customer Info:</h4>
                    <div style="display: flex; gap: 10px;">
                        <button type="button" onclick="openCustomerLookupModal()" style="background: #2563eb; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                            🔍 Search Customer
                        </button>
                        <button type="button" onclick="clearCustomer()" style="background: #f59e0b; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer;">
                            Clear Customer Info
                        </button>
                    </div>
                </div>

                <input type="hidden" name="customerId" id="customerId" value="">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Phone # Primary <span class="required">*</span></label>
                        <input type="text" name="customerPhone" id="customerPhone" placeholder="Click or type to search customer..." autocomplete="off" onfocus="openCustomerLookupModal(this.value)" onclick="openCustomerLookupModal(this.value)">
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
                        <div style="width: 100px; height: 100px; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 32px; color: #94a3b8; cursor: pointer;">
                            🖼️
                        </div>

                        <div style="flex: 1; display: flex; flex-direction: column; gap: 15px;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div class="form-group">
                                    <label>Machine <span class="required">*</span></label>
                                    <select name="machine" required style="width: 100%; height: 42px; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0 12px;">
                                        <option value="">Select Machine</option>
                                        <?php while ($m = mysqli_fetch_assoc($machines)): ?>
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

<!-- CUSTOMER LOOKUP MODAL -->
<div id="customerLookupModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 1050px; width: 95%;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                <h3 style="margin: 0; color: #0f172a; font-size: 18px; font-weight: 700;">Customer Lookup</h3>
                <input type="text" id="modalSearchInput" placeholder="Search by Phone, Name, or Customer ID..." autocomplete="off" oninput="triggerModalSearch(this.value)" style="flex: 1; max-width: 450px; height: 38px; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0 12px; font-size: 14px;">
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button" onclick="openNewCustomerModal()" style="background: #16a34a; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer;">
                    + New Customer
                </button>
                <button type="button" onclick="closeCustomerLookupModal()" style="background: transparent; border: none; font-size: 24px; color: #64748b; cursor: pointer; line-height: 1;">&times;</button>
            </div>
        </div>
        <div class="modal-body" style="padding: 0; overflow-y: auto; max-height: 65vh;">
            <table class="cust-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; color: #475569; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 12px 14px;">Customer ID</th>
                        <th style="padding: 12px 14px;">Customer Name</th>
                        <th style="padding: 12px 14px;">Address</th>
                        <th style="padding: 12px 14px;">Contact Number</th>
                        <th style="padding: 12px 14px;">WhatsApp Number</th>
                        <th style="padding: 12px 14px; text-align: center;">Active Status</th>
                        <th style="padding: 12px 14px; text-align: center; width: 80px;">Choose</th>
                        <th style="padding: 12px 14px; text-align: center; width: 80px;">Edit</th>
                    </tr>
                </thead>
                <tbody id="modalCustTbody">
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 30px; color: #64748b;">Loading customers...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- CUSTOMER EDIT / NEW MODAL -->
<div id="customerEditModal" class="modal-overlay" style="z-index: 10000;">
    <div class="modal-content" style="max-width: 650px; width: 90%;">
        <div class="modal-header">
            <h3 id="editModalTitle" style="margin: 0; color: #0f172a; font-size: 18px; font-weight: 700;">Edit Customer</h3>
            <button type="button" onclick="closeCustomerEditModal()" style="background: transparent; border: none; font-size: 24px; color: #64748b; cursor: pointer; line-height: 1;">&times;</button>
        </div>
        <form id="customerEditForm" onsubmit="saveCustomerAjax(event)">
            <div class="modal-body" style="padding: 20px;">
                <input type="hidden" name="id" id="edit_id" value="0">
                <input type="hidden" name="address_id" id="edit_address_id" value="0">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-size: 12px;">Customer ID</label>
                        <input type="text" name="customerId" id="edit_customerId" placeholder="Auto-generated if empty" style="height: 38px;">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 12px;">Customer Name <span class="required">*</span></label>
                        <input type="text" name="name" id="edit_name" required placeholder="Full Name" style="height: 38px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-size: 12px;">Primary Contact # <span class="required">*</span></label>
                        <input type="text" name="phoneNo1" id="edit_phoneNo1" required placeholder="Primary Phone" style="height: 38px;">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 12px;">Secondary Phone #</label>
                        <input type="text" name="phoneNo2" id="edit_phoneNo2" placeholder="Secondary Phone" style="height: 38px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-size: 12px;">WhatsApp Number</label>
                        <input type="text" name="whatsAppNo" id="edit_whatsAppNo" placeholder="WhatsApp Number" style="height: 38px;">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 12px;">Email ID</label>
                        <input type="email" name="emailId" id="edit_emailId" placeholder="Email Address" style="height: 38px;">
                    </div>
                </div>

                <div style="margin-bottom: 15px;">
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label style="font-size: 12px;">Address Line 1</label>
                        <input type="text" name="line1" id="edit_line1" placeholder="Street Address / Line 1" style="height: 38px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label style="font-size: 12px;">Address Line 2</label>
                        <input type="text" name="line2" id="edit_line2" placeholder="Line 2 / Area" style="height: 38px;">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label style="font-size: 12px;">City</label>
                            <input type="text" name="city" id="edit_city" placeholder="City" style="height: 38px;">
                        </div>
                        <div class="form-group">
                            <label style="font-size: 12px;">Zip Code</label>
                            <input type="text" name="zipCode" id="edit_zipCode" placeholder="Zip Code" style="height: 38px;">
                        </div>
                    </div>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="active" id="edit_active" value="1" checked style="width: 18px; height: 18px; cursor: pointer;">
                    <label for="edit_active" style="margin: 0; font-size: 13px; font-weight: 600; cursor: pointer;">Active Status</label>
                </div>
            </div>
            <div style="padding: 15px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; border-radius: 0 0 12px 12px;">
                <button type="button" onclick="closeCustomerEditModal()" style="background: #94a3b8; color: #fff; border: none; padding: 9px 20px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer;">Cancel</button>
                <button type="submit" id="saveCustBtn" style="background: #2563eb; color: #fff; border: none; padding: 9px 24px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer;">Save Customer</button>
            </div>
        </form>
    </div>
</div>

<style>
    .form-group label {
        display: block;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        padding: 0 15px;
        font-size: 14px;
        transition: 0.2s;
    }

    .form-group input {
        height: 42px;
    }

    .required {
        color: #ef4444;
    }

    input:focus,
    select:focus,
    textarea:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* MODAL STYLES */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(4px);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.15s ease-out;
    }

    .modal-content {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        max-height: 90vh;
    }

    .modal-header {
        padding: 16px 20px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .cust-table tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.15s;
    }

    .cust-table tbody tr:hover {
        background: #f8fafc;
    }

    .badge-active {
        background: #dcfce7;
        color: #166534;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        display: inline-block;
    }

    .badge-inactive {
        background: #fee2e2;
        color: #991b1b;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        display: inline-block;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>

<script>
    let fetchedCustomers = [];
    let searchDebounceTimer = null;

    function clearCustomer() {
        ['customerId', 'customerPhone', 'customerName', 'city'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
    }

    // Modal Control Functions
    function openCustomerLookupModal(initialValue = '') {
        const modal = document.getElementById('customerLookupModal');
        const input = document.getElementById('modalSearchInput');
        modal.style.display = 'flex';

        if (initialValue && initialValue.trim() !== '') {
            input.value = initialValue;
        }

        setTimeout(() => input.focus(), 100);
        fetchModalCustomers(input.value);
    }

    function closeCustomerLookupModal() {
        document.getElementById('customerLookupModal').style.display = 'none';
    }

    function triggerModalSearch(val) {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            fetchModalCustomers(val);
        }, 200);
    }

    function fetchModalCustomers(query = '') {
        const tbody = document.getElementById('modalCustTbody');
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 25px; color: #64748b;">Searching customers...</td></tr>';

        fetch('../customers/api_search_customers.php?query=' + encodeURIComponent(query.trim()))
            .then(res => res.json())
            .then(resData => {
                if (!resData.success || !Array.isArray(resData.data) || resData.data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 35px; color: #64748b;">
                                <div style="margin-bottom: 10px; font-size: 15px;">No matching customers found</div>
                                <button type="button" onclick="openNewCustomerModal('${escapeHtml(query)}')" style="background: #16a34a; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer;">
                                    + Add New Customer
                                </button>
                            </td>
                        </tr>`;
                    fetchedCustomers = [];
                    return;
                }

                fetchedCustomers = resData.data;
                tbody.innerHTML = resData.data.map((c, idx) => `
                    <tr>
                        <td style="padding: 12px 14px; font-weight: 600; color: #334155; font-size: 13px;">${escapeHtml(c.customerId || 'C-' + c.id)}</td>
                        <td style="padding: 12px 14px; font-weight: 700; color: #0f172a; font-size: 13.5px;">${escapeHtml(c.name || '-')}</td>
                        <td style="padding: 12px 14px; color: #475569; font-size: 12.5px;">${escapeHtml(c.fullAddress || '-')}</td>
                        <td style="padding: 12px 14px; font-weight: 600; color: #2563eb; font-size: 13px;">${escapeHtml(c.phoneNo1 || '-')}</td>
                        <td style="padding: 12px 14px; color: #475569; font-size: 12.5px;">${escapeHtml(c.whatsAppNo || '-')}</td>
                        <td style="padding: 12px 14px; text-align: center;">
                            <span class="${c.active ? 'badge-active' : 'badge-inactive'}">${c.active ? 'Active' : 'Inactive'}</span>
                        </td>
                        <td style="padding: 12px 14px; text-align: center;">
                            <button type="button" onclick="chooseCustomer(${idx})" style="background: #2563eb; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer; transition: 0.15s;">Choose</button>
                        </td>
                        <td style="padding: 12px 14px; text-align: center;">
                            <button type="button" onclick="openEditCustomerModal(${idx})" style="background: #f59e0b; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer; transition: 0.15s;">Edit</button>
                        </td>
                    </tr>
                `).join('');
            })
            .catch(err => {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 25px; color: #ef4444;">Error fetching customer data.</td></tr>';
            });
    }

    function chooseCustomer(idx) {
        const c = fetchedCustomers[idx];
        if (!c) return;

        document.getElementById('customerId').value = c.id || '';
        document.getElementById('customerPhone').value = c.phoneNo1 || '';
        document.getElementById('customerName').value = c.name || '';
        document.getElementById('city').value = c.city || '';

        closeCustomerLookupModal();
    }

    // New / Edit Customer Modal
    function openNewCustomerModal(initialPhoneOrName = '') {
        document.getElementById('editModalTitle').textContent = 'New Customer';
        document.getElementById('customerEditForm').reset();
        document.getElementById('edit_id').value = '0';
        document.getElementById('edit_address_id').value = '0';
        document.getElementById('edit_active').checked = true;

        if (initialPhoneOrName) {
            if (/^\d+$/.test(initialPhoneOrName.trim())) {
                document.getElementById('edit_phoneNo1').value = initialPhoneOrName.trim();
            } else {
                document.getElementById('edit_name').value = initialPhoneOrName.trim();
            }
        }

        document.getElementById('customerEditModal').style.display = 'flex';
    }

    function openEditCustomerModal(idx) {
        const c = fetchedCustomers[idx];
        if (!c) return;

        document.getElementById('editModalTitle').textContent = 'Edit Customer';
        document.getElementById('edit_id').value = c.id || '0';
        document.getElementById('edit_address_id').value = c.address_id || '0';
        document.getElementById('edit_customerId').value = c.customerId || '';
        document.getElementById('edit_name').value = c.name || '';
        document.getElementById('edit_phoneNo1').value = c.phoneNo1 || '';
        document.getElementById('edit_phoneNo2').value = c.phoneNo2 || '';
        document.getElementById('edit_whatsAppNo').value = c.whatsAppNo || '';
        document.getElementById('edit_emailId').value = c.emailId || '';
        document.getElementById('edit_line1').value = c.line1 || '';
        document.getElementById('edit_line2').value = c.line2 || '';
        document.getElementById('edit_city').value = c.city || '';
        document.getElementById('edit_zipCode').value = c.zipCode || '';
        document.getElementById('edit_active').checked = Boolean(c.active);

        document.getElementById('customerEditModal').style.display = 'flex';
    }

    function closeCustomerEditModal() {
        document.getElementById('customerEditModal').style.display = 'none';
    }

    function saveCustomerAjax(e) {
        e.preventDefault();
        const form = document.getElementById('customerEditForm');
        const formData = new FormData(form);
        const saveBtn = document.getElementById('saveCustBtn');

        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        fetch('../customers/api_save_customer.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(resData => {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Customer';

            if (!resData.success) {
                alert('Error: ' + (resData.error || 'Failed to save customer'));
                return;
            }

            const savedCust = resData.customer;
            closeCustomerEditModal();

            // Refresh search modal
            const currentSearch = document.getElementById('modalSearchInput').value;
            fetchModalCustomers(currentSearch);

            // If it was a new customer or the current selected customer, auto select into jobcard form!
            const currentFormCustId = document.getElementById('customerId').value;
            if (savedCust && (document.getElementById('edit_id').value === '0' || currentFormCustId == savedCust.id)) {
                document.getElementById('customerId').value = savedCust.id;
                document.getElementById('customerPhone').value = savedCust.phoneNo1 || '';
                document.getElementById('customerName').value = savedCust.name || '';
                document.getElementById('city').value = savedCust.city || '';
                closeCustomerLookupModal();
            }
        })
        .catch(err => {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Customer';
            alert('An error occurred while saving customer details.');
        });
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
</script>

<?php include("../includes/footer.php"); ?>