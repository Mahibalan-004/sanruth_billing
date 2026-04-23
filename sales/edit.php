<?php
require_once("../config/db.php");
include("../includes/header.php");

$salesId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($salesId <= 0) {
    echo "<script>alert('Invalid Sales ID'); window.location.href='list.php';</script>";
    exit;
}

// Fetch sales & customer
$query = "
    SELECT s.*, c.name as customer_name, c.phoneNo1, c.whatsAppNo 
    FROM sales s 
    LEFT JOIN customer c ON s.customer = c.id 
    WHERE s.id = $salesId
";
$res = mysqli_query($conn, $query);
if (!$res || mysqli_num_rows($res) == 0) {
    echo "<script>alert('Sales Order not found'); window.location.href='list.php';</script>";
    exit;
}
$sale = mysqli_fetch_assoc($res);

// Fetch items
$iQuery = "SELECT * FROM salesitems WHERE sales = $salesId AND deleted = 0";
$iRes = mysqli_query($conn, $iQuery);
$items = [];
while ($row = mysqli_fetch_assoc($iRes)) {
    $items[] = $row;
}

// Fetch payments conditionally
$payments = [];
$chkPmt = mysqli_query($conn, "SHOW TABLES LIKE 'sales_payments'");
if ($chkPmt && mysqli_num_rows($chkPmt) > 0) {
    $pQuery = "SELECT * FROM sales_payments WHERE salesId = $salesId ORDER BY id ASC";
    $pRes = mysqli_query($conn, $pQuery);
    if ($pRes) {
        while ($row = mysqli_fetch_assoc($pRes)) {
            $payments[] = $row;
        }
    }
}
?>

<style>
  :root {
    --border: #e2e8f0; --label: #334155; --text: #1e293b;
    --muted: #64748b; --accent: #2563eb; --green: #16a34a;
    --red: #ef4444; --bg: #f1f5f9; --card: #fff;
    --header-blue: #1e40af; --radius: 6px;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); font-size: 14px; color: var(--text); }

  .top-bar { background: var(--header-blue); height: 46px; display: flex; align-items: center; justify-content: space-between; padding: 0 20px; }
  .top-bar .title { color: #fff; font-weight: 700; font-size: 17px; }
  .top-bar a { color: #fff; text-decoration: none; font-size: 13px; font-weight: 600; }

  .form-wrap { background: var(--card); border: 1px solid var(--border); padding: 18px 18px 0; }
  .section { border: 1px solid var(--border); border-radius: 6px; margin-bottom: 14px; overflow: hidden; }

  label { font-size: 12px; font-weight: 600; color: var(--muted); display: block; margin-bottom: 4px; }
  .req { color: var(--red); }

  input[type=text], input[type=number], input[type=date], select {
    width: 100%; height: 38px; padding: 0 10px;
    border: 1px solid var(--border); border-radius: var(--radius);
    font-size: 13px; font-family: inherit; color: var(--text);
    background: #fff; transition: border-color 0.12s;
    appearance: none; -webkit-appearance: none;
  }
  input:focus, select:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37,99,235,0.08); }
  input:disabled { background: #f8fafc; color: var(--muted); cursor: default; }
  input[readonly] { background: #f8fafc; color: var(--muted); }
  select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%2394a3b8'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 9px center; padding-right: 26px;
  }
  .form-group { margin-bottom: 12px; }
  .g3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }
  .g4 { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 14px; }

  .cust-action-bar { display: flex; justify-content: flex-end; gap: 16px; padding: 8px 14px; border-bottom: 1px solid var(--border); background: #fafafa; }
  .cust-action-bar button { background: none; border: none; cursor: pointer; font-family: inherit; font-size: 13px; font-weight: 700; color: var(--text); padding: 2px 0; }
  .cust-action-bar button.close-lnk { color: var(--muted); font-weight: 600; }
  .cust-fields { padding: 14px; border-bottom: 1px solid var(--border); }
  .cust-table-wrap { overflow-x: auto; max-height: 260px; overflow-y: auto; }
  .cust-table { width: 100%; border-collapse: collapse; font-size: 12px; min-width: 700px; }
  .cust-table th { padding: 8px 10px; background: #f8fafc; font-weight: 700; color: var(--muted); text-align: left; border-bottom: 2px solid var(--border); white-space: nowrap; position: sticky; top: 0; }
  .cust-table td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; font-size: 12px; }
  .cust-table tbody tr:hover td { background: #f0f7ff; cursor: pointer; }
  .sel-btn { padding: 3px 12px; background: none; border: 1px solid var(--accent); color: var(--accent); border-radius: 4px; font-weight: 600; font-size: 11px; cursor: pointer; }
  .sel-btn:hover { background: var(--accent); color: #fff; }

  .items-wrap { overflow-x: auto; }
  .items-tbl { width: 100%; border-collapse: collapse; min-width: 860px; }
  .items-tbl th { padding: 9px 8px; background: #f8fafc; font-size: 12px; font-weight: 700; color: var(--muted); text-align: left; border-bottom: 2px solid var(--border); white-space: nowrap; }
  .items-tbl td { padding: 5px 6px; border-bottom: 1px solid #f8fafc; vertical-align: middle; }
  .td-in { width: 100%; height: 34px; padding: 0 8px; border: 1px solid var(--border); border-radius: 5px; font-size: 13px; font-family: inherit; color: var(--text); background: #fff; }
  .td-in:focus { outline: none; border-color: var(--accent); }
  .td-in[readonly], .td-in:disabled { background: #f8fafc; color: var(--muted); }

  .row-img { width: 36px; height: 36px; border-radius: 5px; border: 1px solid var(--border); background: #f8fafc; display: flex; align-items: center; justify-content: center; font-size: 15px; overflow: hidden; }
  
  .totals-wrap { display: flex; justify-content: flex-end; padding: 10px 14px 14px; }
  .totals-box { width: 250px; background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; padding: 12px 14px; }
  .tot-row { display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; color: var(--muted); margin-bottom: 7px; }
  .tot-row.final { border-top: 1px solid #cbd5e1; padding-top: 8px; margin-top: 4px; color: var(--text); font-size: 14px; border-bottom: none !important;}
  .tot-row span:last-child { color: var(--text); }
  .tot-row.final span:last-child { color: #e11d48; }

  .pay-section { border: 1px solid var(--border); border-radius: 6px; margin-bottom: 14px; }
  .pay-hdr { padding: 9px 14px; border-bottom: 1px solid var(--border); font-weight: 700; font-size: 13px; background: #fafafa; }

  .action-bar { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 0 18px; border-top: 1px solid var(--border); }
  .btn { padding: 9px 22px; border: none; border-radius: 6px; font-weight: 700; font-size: 13px; cursor: pointer; font-family: inherit; transition: all 0.12s; }
  .btn-blue { background: var(--accent); color: #fff; } .btn-blue:hover { background: #1d4ed8; }
  .btn-green { background: var(--green); color: #fff; } .btn-green:hover { background: #15803d; }
  .icon-btn { background: none; border: none; cursor: pointer; padding: 3px 5px; border-radius: 4px; font-size: 15px; }
  .icon-btn:hover { background: #fee2e2; }
  .icon-btn.add-btn:hover { background: #f0f9ff; }

  /* Stock Modal */
  .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.38); z-index: 4000; }
  .modal-overlay.open { display: flex; align-items: flex-start; justify-content: center; padding-top: 55px; }
  .stock-modal { background: #fff; border-radius: 10px; box-shadow: 0 20px 60px rgba(0,0,0,0.22); width: 96%; max-width: 1050px; max-height: 78vh; display: flex; flex-direction: column; overflow: hidden; }
  .sm-header { padding: 10px 14px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 10px; background: #fafafa; }
  .sm-header input { flex: 1; height: 38px; padding: 0 12px; border: 1.5px solid var(--accent); border-radius: 6px; font-size: 13px; }
  .sm-header input:focus { outline: none; }
  .sm-body { overflow-y: auto; flex: 1; }
  .sm-footer { padding: 9px; text-align: center; border-top: 1px solid var(--border); background: #fafafa; }
  .sm-footer button { background: none; border: none; cursor: pointer; color: #e11d48; font-weight: 700; font-size: 13px; }

  .stk-tbl { width: 100%; border-collapse: collapse; font-size: 13px; }
  .stk-tbl th { padding: 8px 10px; background: #f8fafc; font-weight: 700; color: var(--muted); text-align: left; border-bottom: 2px solid var(--border); white-space: nowrap; position: sticky; top: 0; z-index: 1;}
  .stk-tbl td { padding: 9px 10px; border-bottom: 1px solid #f1f5f9; }
  .stk-tbl tbody tr:hover td { background: #f0f7ff; }
  .no-res { text-align: center; padding: 30px; color: var(--muted); font-size: 13px; }
</style>

<div class="top-bar">
  <span class="title">Edit Sales Order #<?= htmlspecialchars($sale['orderNo']) ?></span>
  <a href="list.php">☰ Back to List</a>
</div>

<div class="form-wrap">
  <form id="salesForm" method="POST" action="update_sales.php">
    <input type="hidden" name="salesId" value="<?= $salesId ?>">
    <input type="hidden" name="orderNo" value="<?= htmlspecialchars($sale['orderNo']) ?>">
    <input type="hidden" name="customerId" id="customerId" value="<?= htmlspecialchars($sale['customer']) ?>">

    <!-- ORDER HEADER -->
    <div class="section" style="margin-top:14px;">
      <div style="padding:14px;">
        <div class="g3">
          <div class="form-group">
            <label>Order #</label>
            <input type="text" value="<?= htmlspecialchars($sale['orderNo']) ?>" disabled>
          </div>
          <div class="form-group">
            <label>Order Status</label>
            <select name="orderStatus">
              <option value="New" <?= $sale['orderStatus'] == 'New' ? 'selected' : '' ?>>New</option>
              <option value="Pending" <?= $sale['orderStatus'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
              <option value="Completed" <?= $sale['orderStatus'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
            </select>
          </div>
          <div class="form-group">
            <label>Order Date <span class="req">*</span></label>
            <input type="date" name="orderDate" value="<?= htmlspecialchars(date('Y-m-d', strtotime($sale['orderDate']))) ?>" required>
          </div>
        </div>
      </div>
    </div>

    <!-- CUSTOMER SECTION -->
    <div class="section">
      <div class="cust-action-bar">
        <button type="button" onclick="newCustomerMode()">Change / Search Customer</button>
      </div>
      <div class="cust-fields">
        <div class="g3">
          <div class="form-group" style="margin:0;">
            <label>Phone # Primary <span class="req">*</span></label>
            <input type="text" name="customerPhone" id="customerPhone" autocomplete="off" oninput="searchCust()" required value="<?= htmlspecialchars($sale['phoneNo1'] ?? '') ?>">
          </div>
          <div class="form-group" style="margin:0;">
            <label>Phone # WhatsApp</label>
            <input type="text" name="customerWhatsApp" id="customerWhatsApp" value="<?= htmlspecialchars($sale['whatsAppNo'] ?? '') ?>">
          </div>
          <div class="form-group" style="margin:0;">
            <label>Name <span class="req">*</span></label>
            <input type="text" name="customerName" id="customerName" required value="<?= htmlspecialchars($sale['customer_name'] ?? '') ?>">
          </div>
        </div>
      </div>
      <div id="custResults" style="display:none;">
        <div class="cust-table-wrap">
          <table class="cust-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Contact #</th>
                <th>WhatsApp #</th>
                <th></th>
              </tr>
            </thead>
            <tbody id="custTbody"></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ITEMS TABLE -->
    <div class="section">
      <div class="items-wrap">
        <table class="items-tbl">
          <thead>
            <tr>
              <th style="width:32px;">#</th>
              <th style="width:44px;">Image</th>
              <th>Item</th>
              <th style="width:120px;">Barcode</th>
              <th style="width:110px;">Part/Serial #</th>
              <th style="width:72px;">Rack #</th>
              <th style="width:58px;">Qty</th>
              <th style="width:95px;">Price/Qty</th>
              <th style="width:62px;">GST %</th>
              <th style="width:100px;">Total Price</th>
              <th style="width:60px;"></th>
            </tr>
          </thead>
          <tbody id="itemsBody">
            <?php foreach ($items as $idx => $item): ?>
            <tr>
              <td class="row-num" style="font-size:12px; color:#94a3b8; font-weight:700; text-align:center;"><?= $idx + 1 ?></td>
              <td><div class="row-img">📷</div></td>
              <td style="min-width:140px;">
                <input type="text" name="item[]" class="td-in item-inp" value="<?= htmlspecialchars($item['itemName']) ?>" readonly onclick="openModal(this)" style="cursor:pointer;">
                <input type="hidden" name="itemId[]" value="<?= $item['id'] ?>">
                <input type="hidden" name="stockId[]" class="h-stock" value="<?= $item['stock'] ?>">
                <input type="hidden" name="spareId[]" class="h-spare" value="<?= $item['spare'] ?>">
              </td>
              <td><input type="text"   name="barcode[]" class="td-in" readonly></td>
              <td><input type="text"   name="serial[]"  class="td-in" value="<?= htmlspecialchars($item['serialNo'] ?? '') ?>" readonly></td>
              <td><input type="text"   name="rack[]"    class="td-in" readonly></td>
              <td><input type="number" name="qty[]"   value="<?= intval($item['quantity']) ?>" min="1" class="td-in" oninput="calcRow(this)"></td>
              <td><input type="number" name="price[]" value="<?= floatval($item['pricePerQty']) ?>" step="0.01" class="td-in" oninput="calcRow(this)"></td>
              <td><input type="number" name="gst[]"   value="<?= floatval($item['gstPercentage']) ?>" step="0.01" class="td-in" oninput="calcRow(this)"></td>
              <td><input type="text"   name="total[]" value="<?= floatval($item['totalPrice']) ?>" readonly class="td-in" style="font-weight:700; color:#475569; background:#f8fafc;"></td>
              <td style="text-align:center; white-space:nowrap;">
                <button type="button" class="icon-btn" onclick="delRow(this)">🗑️</button>
                <button type="button" class="icon-btn add-btn" onclick="addRow()">➕</button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="totals-wrap">
        <div class="totals-box">
          <div class="tot-row"><span>Total Rs:</span><span id="grandTotal"><?= number_format($sale['actualAmountSum'], 2) ?></span></div>
          <div class="tot-row"><span>Paid Rs:</span><span id="totalPaid"><?= number_format($sale['paidAmountSum'], 2) ?></span></div>
          <div class="tot-row final"><span>Balance Rs:</span><span id="balanceAmt"><?= number_format($sale['actualAmountSum'] - $sale['paidAmountSum'], 2) ?></span></div>
        </div>
      </div>
    </div>

    <!-- PAYMENT -->
    <div class="pay-section">
      <div class="pay-hdr">Payment:</div>
      <div style="padding:14px;">
        <div id="paymentsList">
        <?php foreach ($payments as $pmt): ?>
          <div class="g4 pay-row" style="margin-bottom:10px; align-items:end;">
            <input type="hidden" name="paymentId[]" value="<?= $pmt['id'] ?>">
            <div class="form-group" style="margin:0;"><label>Paid On</label><input type="date" name="paymentDate[]" class="p-date" value="<?= date('Y-m-d', strtotime($pmt['paymentDate'])) ?>"></div>
            <div class="form-group" style="margin:0;">
              <label>Mode</label>
              <select name="paymentMode[]">
                <option value="Cash" <?= $pmt['paymentMode']=='Cash'?'selected':'' ?>>Cash</option>
                <option value="Card" <?= $pmt['paymentMode']=='Card'?'selected':'' ?>>Card</option>
                <option value="UPI" <?= $pmt['paymentMode']=='UPI'?'selected':'' ?>>UPI</option>
                <option value="Cheque" <?= $pmt['paymentMode']=='Cheque'?'selected':'' ?>>Cheque</option>
              </select>
            </div>
            <div class="form-group" style="margin:0;"><label>Ref No</label><input type="text" name="paymentRef[]" value="<?= htmlspecialchars($pmt['referenceNo'] ?? '') ?>"></div>
            <div class="form-group" style="margin:0; display:flex; gap:6px; align-items:flex-end;">
              <div style="flex:1;"><label>Amount</label><input type="number" name="paymentAmount[]" value="<?= floatval($pmt['amount']) ?>" min="0" step="0.01" class="pay-amt" oninput="calcTotals()"></div>
              <button type="button" onclick="delPayRow(this)" style="height:38px; width:34px; background:#fef2f2; border:1px solid #fecaca; border-radius:5px; cursor:pointer; font-size:13px; flex-shrink:0;">🗑️</button>
            </div>
          </div>
        <?php endforeach; ?>
        </div>
        <div style="text-align:right; margin-top:8px;">
          <button type="button" class="btn btn-green" onclick="addPayRow()">+ Add Payment</button>
        </div>
      </div>
    </div>

    <input type="hidden" name="grandTotal" id="hGT" value="<?= floatval($sale['actualAmountSum']) ?>">
    <input type="hidden" name="paidTotal"  id="hPT" value="<?= floatval($sale['paidAmountSum']) ?>">

    <div class="action-bar">
      <button type="submit" class="btn btn-blue">Update Sale</button>
    </div>
  </form>
</div>

<!-- STOCK MODAL -->
<div class="modal-overlay" id="stockModal">
  <div class="stock-modal">
    <div class="sm-header">
      <span>Stocks:</span>
      <input type="text" id="stkInput"
        placeholder="Type to Search Stock..."
        oninput="fetchStocks()" autocomplete="off">
    </div>
    <div class="sm-body">
      <table class="stk-tbl">
        <thead>
          <tr>
            <th>Name</th>
            <th>Avil Qty</th>
            <th>Per Unit</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="stkBody">
          <tr><td colspan="4" class="no-res">Type to search...</td></tr>
        </tbody>
      </table>
    </div>
    <div class="sm-footer">
      <button type="button" onclick="closeModal()">Close Details</button>
    </div>
  </div>
</div>

<template id="rowTpl">
  <tr>
    <td class="row-num" style="font-size:12px; color:#94a3b8; font-weight:700; text-align:center;"></td>
    <td><div class="row-img">📷</div></td>
    <td style="min-width:140px;">
      <input type="text" name="item[]" class="td-in item-inp"
        placeholder="Click to set item..." readonly onclick="openModal(this)" style="cursor:pointer;">
      <input type="hidden" name="itemId[]" value="0">
      <input type="hidden" name="stockId[]" class="h-stock">
      <input type="hidden" name="spareId[]" class="h-spare">
    </td>
    <td><input type="text"   name="barcode[]" class="td-in" readonly></td>
    <td><input type="text"   name="serial[]"  class="td-in" readonly></td>
    <td><input type="text"   name="rack[]"    class="td-in" readonly></td>
    <td><input type="number" name="qty[]"   value="1" min="1" class="td-in" oninput="calcRow(this)"></td>
    <td><input type="number" name="price[]" value="0" step="0.01" class="td-in" oninput="calcRow(this)"></td>
    <td><input type="number" name="gst[]"   value="0" step="0.01" class="td-in" oninput="calcRow(this)"></td>
    <td><input type="text"   name="total[]" value="0.00" readonly class="td-in" style="font-weight:700; color:#475569; background:#f8fafc;"></td>
    <td style="text-align:center; white-space:nowrap;">
      <button type="button" class="icon-btn" onclick="delRow(this)">🗑️</button>
      <button type="button" class="icon-btn add-btn" onclick="addRow()">➕</button>
    </td>
  </tr>
</template>

<template id="payTpl">
  <div class="g4 pay-row" style="margin-bottom:10px; align-items:end;">
    <input type="hidden" name="paymentId[]" value="0">
    <div class="form-group" style="margin:0;"><label>Paid On</label><input type="date" name="paymentDate[]" class="p-date"></div>
    <div class="form-group" style="margin:0;">
      <label>Mode</label>
      <select name="paymentMode[]">
        <option value="Cash">Cash</option>
        <option value="Card">Card</option>
        <option value="UPI">UPI</option>
        <option value="Cheque">Cheque</option>
      </select>
    </div>
    <div class="form-group" style="margin:0;"><label>Ref No</label><input type="text" name="paymentRef[]" placeholder="Txn / Ref"></div>
    <div class="form-group" style="margin:0; display:flex; gap:6px; align-items:flex-end;">
      <div style="flex:1;"><label>Amount</label><input type="number" name="paymentAmount[]" value="0" min="0" step="0.01" class="pay-amt" oninput="calcTotals()"></div>
      <button type="button" onclick="delPayRow(this)" style="height:38px; width:34px; background:#fef2f2; border:1px solid #fecaca; border-radius:5px; cursor:pointer; font-size:13px; flex-shrink:0;">🗑️</button>
    </div>
  </div>
</template>

<script>
const TODAY = '<?= date('Y-m-d') ?>';

function addRow() {
  const t = document.getElementById('rowTpl');
  document.getElementById('itemsBody').appendChild(t.content.cloneNode(true));
  renum();
}
function delRow(btn) {
  if (document.querySelectorAll('#itemsBody tr').length <= 1) return;
  btn.closest('tr').remove(); renum(); calcTotals();
}
function renum() {
  document.querySelectorAll('#itemsBody tr').forEach((r,i) => r.querySelector('.row-num').textContent = i+1);
}
function calcRow(el) {
  const r = el.closest('tr');
  const q = parseFloat(r.querySelector('[name="qty[]"]').value) || 0;
  const p = parseFloat(r.querySelector('[name="price[]"]').value) || 0;
  const g = parseFloat(r.querySelector('[name="gst[]"]').value) || 0;
  const s = q * p;
  r.querySelector('[name="total[]"]').value = (s + s*g/100).toFixed(2);
  calcTotals();
}

function calcTotals() {
  let gt=0, pd=0;
  document.querySelectorAll('[name="total[]"]').forEach(i => gt += parseFloat(i.value)||0);
  document.querySelectorAll('.pay-amt').forEach(i => pd += parseFloat(i.value)||0);
  document.getElementById('grandTotal').textContent = gt.toFixed(2);
  document.getElementById('totalPaid').textContent  = pd.toFixed(2);
  document.getElementById('balanceAmt').textContent = (gt-pd).toFixed(2);
  document.getElementById('hGT').value = gt.toFixed(2);
  document.getElementById('hPT').value = pd.toFixed(2);
}

function addPayRow() {
  const t = document.getElementById('payTpl');
  const c = t.content.cloneNode(true);
  c.querySelector('.p-date').value = TODAY;
  document.getElementById('paymentsList').appendChild(c);
}
function delPayRow(btn) {
  // if (document.querySelectorAll('#paymentsList .pay-row').length <= 1) return;
  btn.closest('.pay-row').remove(); calcTotals();
}

let activeInput = null, stkTimer = null;
function openModal(inp) {
  activeInput = inp;
  document.getElementById('stockModal').classList.add('open');
  const si = document.getElementById('stkInput');si.value = '';
  document.getElementById('stkBody').innerHTML = '<tr><td colspan="4" class="no-res">Type to search stocks...</td></tr>';
  setTimeout(() => si.focus(), 60);
}
function closeModal() { document.getElementById('stockModal').classList.remove('open'); activeInput = null; }

function fetchStocks() {
  clearTimeout(stkTimer);
  const q = document.getElementById('stkInput').value.trim();
  const tb = document.getElementById('stkBody');
  if (!q) { tb.innerHTML='<tr><td colspan="4" class="no-res">Type to search...</td></tr>'; return; }
  stkTimer = setTimeout(() => {
    fetch('search_stock.php?term=' + encodeURIComponent(q))
      .then(r => r.json())
      .then(data => {
        if (!Array.isArray(data) || !data.length) {
          tb.innerHTML = '<tr><td colspan="4" class="no-res">No results</td></tr>'; return;
        }
        tb.dataset.items = JSON.stringify(data);
        tb.innerHTML = data.map((item, idx) => `
          <tr onclick="pickStock(${idx})" style="cursor:pointer;">
            <td style="font-weight:600;">${escH(item.spareName)}</td>
            <td><strong>${item.availableQty}</strong></td>
            <td>${item.sellingPricePerUnit||0}</td>
            <td><button type="button" class="sel-btn">Select</button></td>
          </tr>`).join('');
      }).catch(()=>{});
  }, 260);
}

function pickStock(idx) {
  const tb = document.getElementById('stkBody');
  const item = JSON.parse(tb.dataset.items||'[]')[idx];
  if (!item || !activeInput) return;
  const row = activeInput.closest('tr');
  activeInput.value = item.spareName;
  row.querySelector('.h-stock').value = item.stockId || '';
  row.querySelector('.h-spare').value = item.spareId || '';
  row.querySelector('[name="barcode[]"]').value = item.barCode || '';
  row.querySelector('[name="serial[]"]').value = item.serialNo || item.partNo || '';
  row.querySelector('[name="rack[]"]').value = item.rackNumber || '';
  row.querySelector('[name="price[]"]').value = parseFloat(item.selledPricePerUnit) || parseFloat(item.sellingPricePerUnit) || 0;
  row.querySelector('[name="gst[]"]').value = parseFloat(item.gstPercentage) || 0;
  calcRow(row.querySelector('[name="price[]"]'));
  closeModal();
}

let custTimer = null;
function searchCust() {
  clearTimeout(custTimer);
  const ph = document.getElementById('customerPhone').value.trim();
  if (ph.length < 3) { document.getElementById('custResults').style.display='none'; return; }
  custTimer = setTimeout(() => {
    fetch('search_customer.php?phone=' + encodeURIComponent(ph))
      .then(r => r.json())
      .then(data => {
        const res = document.getElementById('custResults');
        const tb  = document.getElementById('custTbody');
        if (!data.length) { res.style.display='none'; return; }
        tb.dataset.items = JSON.stringify(data);
        tb.innerHTML = data.map((c,i) => `
          <tr onclick="pickCust(${i})">
            <td>${i+1}</td>
            <td style="font-weight:600;">${escH(c.name||'')}</td>
            <td>${escH(c.phoneNo1||'')}</td>
            <td>${escH(c.whatsApp||'')}</td>
            <td><button type="button" class="sel-btn">Select</button></td>
          </tr>`).join('');
        res.style.display = 'block';
      }).catch(()=>{});
  }, 300);
}
function pickCust(idx) {
  const tb = document.getElementById('custTbody');
  const c  = JSON.parse(tb.dataset.items||'[]')[idx];
  if (!c) return;
  document.getElementById('customerId').value = c.id || '';
  document.getElementById('customerPhone').value = c.phoneNo1 || '';
  document.getElementById('customerWhatsApp').value = c.whatsApp || '';
  document.getElementById('customerName').value = c.name || '';
  document.getElementById('custResults').style.display = 'none';
}

function escH(s) {
  if (s===null||s===undefined) return '';
  return String(s).replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'})[m]);
}

window.addEventListener('DOMContentLoaded', () => { 
  if (document.querySelectorAll('#itemsBody tr').length === 0) { addRow(); }
  if (document.querySelectorAll('#paymentsList .pay-row').length === 0) { addPayRow(); }
  calcTotals();
});
</script>

<?php include("../includes/footer.php"); ?>
