<?php require_once("../config/db.php"); ?>
<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') {
    echo "<script>alert('Access denied: insufficient privileges'); window.location='../login/dashboard.php';</script>";
    exit();
?>
<?php include("../includes/header.php"); ?>

<link
  href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap"
  rel="stylesheet">

<style>
  :root {
    --bg: #f0f2f5;
    --card: #ffffff;
    --border: #e2e8f0;
    --label: #64748b;
    --text: #1e293b;
    --accent: #2563eb;
    --green: #10b981;
    --red: #ef4444;
    --input-bg: #f8fafc;
    --radius: 8px;
    --shadow: 0 1px 3px rgba(0, 0, 0, 0.08), 0 4px 16px rgba(0, 0, 0, 0.04);
  }

  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
  }

  .page-wrapper {
    max-width: 1200px;
    margin: 24px auto;
    padding: 0 16px;
  }

  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
  }

  .page-header h2 {
    font-size: 18px;
    font-weight: 700;
    color: var(--text);
    letter-spacing: -0.02em;
  }

  .btn-back {
    font-size: 13px;
    font-weight: 600;
    color: var(--label);
    text-decoration: none;
    padding: 7px 14px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--card);
    transition: all 0.15s;
  }

  .btn-back:hover {
    background: var(--bg);
    color: var(--text);
  }

  .card {
    background: var(--card);
    border-radius: 14px;
    box-shadow: var(--shadow);
    padding: 28px;
    margin-bottom: 16px;
  }

  .main-grid {
    display: grid;
    grid-template-columns: 200px 1fr 300px;
    gap: 20px;
    align-items: start;
  }

  label {
    font-size: 12px;
    font-weight: 600;
    color: var(--label);
    display: block;
    margin-bottom: 5px;
  }

  label .add-link {
    float: right;
    font-size: 11px;
    color: var(--accent);
    cursor: pointer;
    font-weight: 600;
  }

  input[type=text],
  input[type=number],
  select {
    width: 100%;
    padding: 9px 11px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    font-size: 13px;
    font-family: 'DM Sans', sans-serif;
    color: var(--text);
    background: #fff;
    transition: border-color 0.15s, box-shadow 0.15s;
    appearance: none;
    -webkit-appearance: none;
  }

  input:focus,
  select:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
  }

  input[readonly] {
    background: var(--input-bg);
    color: var(--label);
    cursor: not-allowed;
  }

  select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%2394a3b8'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 28px;
  }

  .form-group {
    margin-bottom: 14px;
  }

  .grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }

  .grid-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 12px;
  }

  /* ── Spare Image Preview (read-only from DB) ── */
  .spare-image-box {
    border: 1px solid var(--border);
    border-radius: 12px;
    height: 200px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background: var(--input-bg);
    overflow: hidden;
    position: relative;
  }

  .spare-image-box img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 10px;
  }

  .spare-image-box .img-placeholder {
    text-align: center;
    color: #94a3b8;
  }

  .spare-image-box .img-placeholder .icon {
    font-size: 32px;
    margin-bottom: 6px;
  }

  .spare-image-box .img-placeholder span {
    font-size: 11px;
    font-weight: 600;
    display: block;
  }

  .img-label {
    margin-top: 8px;
    text-align: center;
    font-size: 11px;
    font-weight: 600;
    color: var(--label);
    background: var(--input-bg);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 4px 8px;
  }

  /* ── Top bar ── */
  .top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
  }

  .multi-stock-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--label);
    cursor: pointer;
  }

  .toggle-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--label);
  }

  .toggle {
    position: relative;
    width: 40px;
    height: 22px;
  }

  .toggle input {
    opacity: 0;
    width: 0;
    height: 0;
  }

  .slider {
    position: absolute;
    inset: 0;
    background: #cbd5e1;
    border-radius: 22px;
    cursor: pointer;
    transition: background 0.2s;
  }

  .slider:before {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    left: 3px;
    top: 3px;
    background: #fff;
    border-radius: 50%;
    transition: transform 0.2s;
  }

  input:checked+.slider {
    background: var(--green);
  }

  input:checked+.slider:before {
    transform: translateX(18px);
  }

  /* ── Section divider ── */
  .section-divider {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: var(--label);
    padding: 6px 0 10px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 14px;
  }

  /* ── Barcode ── */
  .barcode-field {
    font-family: 'DM Mono', monospace;
    text-align: center;
    font-size: 15px;
    letter-spacing: 0.05em;
  }

  #barcodePreview {
    display: none;
    margin-top: 14px;
    padding: 14px;
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 10px;
    text-align: center;
  }

  /* ── Search dropdown ── */
  .search-wrap {
    position: relative;
  }

  #spareList {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    border: 1px solid var(--border);
    background: #fff;
    border-radius: 0 0 10px 10px;
    max-height: 250px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  }

  #spareList .spare-item {
    padding: 9px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f8fafc;
    font-size: 13px;
    transition: background 0.1s;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  #spareList .spare-item:hover {
    background: #f0f7ff;
  }

  #spareList .spare-item img {
    width: 36px;
    height: 36px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid var(--border);
    flex-shrink: 0;
  }

  #spareList .spare-item .info strong {
    display: block;
    font-weight: 700;
    font-size: 13px;
  }

  #spareList .spare-item .info small {
    color: var(--label);
    font-size: 11px;
  }

  /* ── Action Buttons ── */
  .action-bar {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
    margin-top: 20px;
  }

  .btn {
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    border: none;
    font-family: 'DM Sans', sans-serif;
    transition: all 0.15s;
  }

  .btn-reset {
    background: var(--input-bg);
    color: var(--label);
    border: 1px solid var(--border);
  }

  .btn-reset:hover {
    background: #e2e8f0;
  }

  .btn-generate {
    background: #fff;
    color: var(--accent);
    border: 1px solid var(--accent);
  }

  .btn-generate:hover {
    background: #eff6ff;
  }

  .btn-submit {
    background: linear-gradient(135deg, var(--green), #059669);
    color: #fff;
    padding: 10px 36px;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
  }

  .btn-submit:hover {
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.4);
    transform: translateY(-1px);
  }

  /* ── Success Toast ── */
  .toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    background: #10b981;
    color: #fff;
    padding: 14px 22px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 14px;
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.35);
    animation: slideIn 0.3s ease;
  }

  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateX(40px);
    }

    to {
      opacity: 1;
      transform: translateX(0);
    }
  }

  /* ── Modal ── */
  .modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    z-index: 2000;
    backdrop-filter: blur(2px);
  }

  .modal-box {
    background: #fff;
    width: 380px;
    margin: 140px auto;
    padding: 28px;
    border-radius: 14px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
  }

  .modal-box h3 {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 16px;
  }

  .modal-actions {
    display: flex;
    gap: 10px;
    margin-top: 16px;
  }

  .btn-modal-cancel {
    flex: 1;
    padding: 10px;
    background: var(--input-bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
  }

  .btn-modal-save {
    flex: 1;
    padding: 10px;
    background: var(--accent);
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
  }

  @media (max-width: 1024px) {
    .main-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<?php if ($successMsg): ?>
  <div class="toast" id="toast">✅ Stock added successfully!</div>
  <script>setTimeout(() => document.getElementById('toast').remove(), 3500);</script>
<?php endif; ?>

<div class="page-wrapper">

  <div class="page-header">
    <h2>Stock Info</h2>
    <a href="list.php" class="btn-back">☰ Stocks</a>
  </div>

  <div class="card">

    <div class="top-bar">
      <label class="multi-stock-label">
        <input type="checkbox" id="multiCheck" style="width:auto;"> Add multiple stock
      </label>
      <div class="toggle-row">
        <label class="toggle" style="margin:0;">
          <input type="checkbox" id="selledToggle" name="selled" value="1">
          <span class="slider"></span>
        </label>
        Selled
      </div>
    </div>

    <form method="POST" action="insert_stock.php" id="stockForm">

      <div class="main-grid">

        <!-- LEFT: Spare Image (auto-loaded from DB) -->
        <div>
          <div class="spare-image-box" id="spareImageBox">
            <img id="spareImg" src="" alt="" style="display:none;">
            <div class="img-placeholder" id="imgPlaceholder">
              <div class="icon">🔍</div>
              <span>Search an item</span>
              <span>to load image</span>
            </div>
          </div>
          <div class="img-label" id="imgLabel">No item selected</div>
        </div>

        <!-- CENTER: Item Data -->
        <div>

          <div class="form-group">
            <label>Item Name <span style="color:var(--red)">*</span></label>
            <div class="search-wrap">
              <input type="text" id="spareSearch" name="itemName" placeholder="Search by name or part number..."
                onkeyup="searchSpare()" autocomplete="off" required>
              <div id="spareList"></div>
            </div>
            <input type="hidden" name="spare" id="spareId">
            <input type="hidden" name="sparePicture" id="sparePicture">
          </div>

          <div class="grid-2 form-group">
            <div>
              <label>Brand <span class="add-link" onclick="openModal('brandModal')">+ Add</span></label>
              <select name="brand" id="brandSelect">
                <option value="">-- Generic / None --</option>
                <?php
                $res = mysqli_query($conn, "SELECT * FROM brand ORDER BY brandName");
                while ($r = mysqli_fetch_assoc($res)) {
                  echo "<option value='{$r['id']}'>" . htmlspecialchars($r['brandName']) . "</option>";
                }
                ?>
              </select>
            </div>
            <div>
              <label>Model <span class="add-link" onclick="openModal('modelModal')">+ Add</span></label>
              <select name="model" id="modelSelect">
                <option value="">-- Standard --</option>
                <?php
                $res = mysqli_query($conn, "SELECT * FROM model ORDER BY model");
                while ($r = mysqli_fetch_assoc($res)) {
                  echo "<option value='{$r['id']}'>" . htmlspecialchars($r['model']) . "</option>";
                }
                ?>
              </select>
            </div>
          </div>

          <div class="grid-2 form-group">
            <div>
              <label>Quantity <span style="color:var(--red)">*</span></label>
              <input type="number" id="qtyInput" name="quantity" value="1" min="1" required oninput="syncQty(this)">
            </div>
            <div>
              <label>Barcode <small style="color:var(--label); font-weight:400;">System generated</small></label>
              <input type="text" name="barCode" id="barcode" class="barcode-field" readonly
                value="<?php echo htmlspecialchars($generatedBarcode); ?>">
            </div>
          </div>

          <div class="form-group">
            <label>Serial #</label>
            <input type="text" name="serialNo" id="serialNo" placeholder="Optional serial number">
          </div>

          <div class="grid-3 form-group">
            <div>
              <label>Min Qty (Reorder)</label>
              <input type="number" name="minQty" value="0" min="0">
            </div>
            <div>
              <label>Old Stock</label>
              <input type="number" id="oldStock" value="0" min="0" readonly>
            </div>
            <div>
              <label>New Stock</label>
              <input type="number" id="newStock" value="1" min="0" oninput="syncQty(this)">
            </div>
          </div>

          <div class="grid-2 form-group">
            <div>
              <label>Unit</label>
              <select name="unit">
                <option value="pcs">Pieces (pcs)</option>
                <option value="set">Set</option>
                <option value="nos">Nos</option>
                <option value="kg">Kg</option>
                <option value="ltr">Litre</option>
                <option value="mtr">Metre</option>
                <option value="box">Box</option>
              </select>
            </div>
            <div>
              <label>Machine</label>
              <select name="machine">
                <option value="">-- Select Machine --</option>
                <?php
                $res = mysqli_query($conn, "SELECT * FROM machine ORDER BY machineName");
                if ($res)
                  while ($r = mysqli_fetch_assoc($res)) {
                    echo "<option value='{$r['id']}'>" . htmlspecialchars($r['machineName']) . "</option>";
                  }
                ?>
              </select>
            </div>
          </div>

          <div class="grid-2 form-group">
            <div>
              <label>Purchase Item?</label>
              <select name="purchaseItem">
                <option value="1">Yes — Purchase</option>
                <option value="0">No — In-house</option>
              </select>
            </div>
            <div>
              <label>Warranty (Months)</label>
              <input type="number" name="warrantyInMonths" value="0" min="0">
            </div>
          </div>

        </div>

        <!-- RIGHT: Pricing -->
        <div>
          <div class="section-divider">Price Per Quantity</div>

          <div class="form-group">
            <label>Actual Price (Unit)</label>
            <input type="number" id="actual" placeholder="0.00" step="0.01" min="0">
          </div>
          <div class="form-group">
            <label>Selling Price (Unit)</label>
            <input type="number" id="selling" placeholder="0.00" step="0.01" min="0">
          </div>
          <div class="form-group">
            <label>GST %</label>
            <input type="number" id="gst" value="0" step="0.01" min="0">
          </div>

          <div class="section-divider">Total Price</div>

          <div class="grid-3 form-group">
            <div>
              <label>Actual</label>
              <input type="number" id="displayActualQty" readonly style="background:var(--input-bg); font-weight:700;"
                value="0">
            </div>
            <div>
              <label>Selling</label>
              <input type="number" id="displaySellingQty" readonly style="background:var(--input-bg); font-weight:700;"
                value="0">
            </div>
            <div>
              <label>Selled</label>
              <input type="number" id="displaySelled" readonly
                style="background:#f0fdf4; color:var(--green); font-weight:700;" value="0.00">
            </div>
          </div>

          <!-- Hidden fields for DB -->
          <input type="hidden" name="actualPricePerQty" id="tActual">
          <input type="hidden" name="actualPricePerUnit" id="actualUnit">
          <input type="hidden" name="sellingPricePerQty" id="tSelling">
          <input type="hidden" name="sellingPricePerUnit" id="sellingUnit">
          <input type="hidden" name="selledPricePerUnit" id="selledUnit">
          <input type="hidden" name="gstPercentage" id="gstHidden">
          <input type="hidden" name="availableQty" id="availableQty">

          <!-- Barcode preview -->
          <div style="margin-top:16px;">
            <button type="button" id="genBtn" onclick="generateBarcode()" <?php echo $generatedBarcode ? '' : 'disabled'; ?>
              style="width:100%; padding:9px; border-radius:8px; border:1px solid var(--accent); color:var(--accent); background:#fff; font-weight:700; font-size:13px; cursor:pointer; font-family:'DM Sans',sans-serif;">
              Show Barcode Preview
            </button>

            <div id="barcodePreview">
              <svg id="barcodeSvg" style="max-width:100%;"></svg>
              <br>
              <button type="button" onclick="printBarcode()"
                style="margin-top:8px; padding:5px 16px; border-radius:6px; background:#475569; color:#fff; border:none; cursor:pointer; font-size:11px; font-weight:700; font-family:'DM Sans',sans-serif;">
                🖨 Print Label
              </button>
            </div>
          </div>

        </div>
      </div>

      <!-- Action Bar -->
      <div class="action-bar">
        <button type="button" class="btn btn-generate" onclick="generateBarcode()">Generate</button>
        <button type="submit" class="btn btn-submit">Submit</button>
        <button type="button" class="btn btn-reset" onclick="resetForm()">Reset</button>
      </div>

    </form>
  </div>
</div>

<!-- Brand Modal -->
<div id="brandModal" class="modal-overlay">
  <div class="modal-box">
    <h3>Add New Brand</h3>
    <label>Brand Name</label>
    <input type="text" id="bname" placeholder="e.g. BOSCH">
    <div class="modal-actions">
      <button class="btn-modal-cancel" onclick="closeModal('brandModal')">Cancel</button>
      <button class="btn-modal-save" onclick="saveBrand()">Save Brand</button>
    </div>
  </div>
</div>

<!-- Model Modal -->
<div id="modelModal" class="modal-overlay">
  <div class="modal-box">
    <h3>Add New Model</h3>
    <label>Model Name</label>
    <input type="text" id="mname" placeholder="e.g. MOTOR SPARES">
    <div class="modal-actions">
      <button class="btn-modal-cancel" onclick="closeModal('modelModal')">Cancel</button>
      <button class="btn-modal-save" onclick="saveModel()">Save Model</button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>

  // ── Init ──
  window.onload = function () {
    let barcode = "<?php echo addslashes($generatedBarcode); ?>";
    if (barcode) {
      document.getElementById("barcode").value = barcode;
      document.getElementById("genBtn").disabled = false;
      showBarcode(barcode);
    }
    calc();
  };

  // ── Spare Image Loader ──
  function loadSpareImage(picturePath, itemName) {
    let img = document.getElementById("spareImg");
    let holder = document.getElementById("imgPlaceholder");
    let lbl = document.getElementById("imgLabel");

    console.log("Image path:", picturePath); // DEBUG

    if (picturePath && picturePath !== 'no-image.png') {

      // ✅ FIXED PATH
      img.src = "/uploads/spares/" + picturePath;

      img.style.display = "block";
      holder.style.display = "none";
    } else {
      img.style.display = "none";
      holder.style.display = "flex";
      holder.querySelector('span').textContent = "No image";
    }

    lbl.textContent = itemName || "—";
  }

  function clearSpareImage() {
    let img = document.getElementById("spareImg");
    let holder = document.getElementById("imgPlaceholder");
    let lbl = document.getElementById("imgLabel");
    img.src = "";
    img.style.display = "none";
    holder.style.display = "flex";
    holder.querySelector('.icon').textContent = "🔍";
    lbl.textContent = "No item selected";
  }

  // ── Sync quantity fields ──
  function syncQty(el) {
    document.getElementById("qtyInput").value = el.value;
    document.getElementById("newStock").value = el.value;
    calc();
  }

  // ── Calculation ──
  function calc() {
    let q = +document.getElementById("qtyInput").value || 0;
    let a = +document.getElementById("actual").value || 0;
    let s = +document.getElementById("selling").value || 0;
    let g = +document.getElementById("gst").value || 0;

    let totalActual = +(a * q).toFixed(2);
    let totalSelling = +(s * q).toFixed(2);
    let finalSelled = +(s + (s * g / 100)).toFixed(2);

    document.getElementById("displayActualQty").value = totalActual;
    document.getElementById("displaySellingQty").value = totalSelling;
    document.getElementById("displaySelled").value = finalSelled;

    document.getElementById("tActual").value = totalActual;
    document.getElementById("actualUnit").value = a;
    document.getElementById("tSelling").value = totalSelling;
    document.getElementById("sellingUnit").value = s;
    document.getElementById("selledUnit").value = finalSelled;
    document.getElementById("gstHidden").value = g;
    document.getElementById("availableQty").value = q;
  }

  document.querySelectorAll("#actual, #selling, #gst, #qtyInput").forEach(el => {
    el.addEventListener("input", calc);
  });

  // ── Barcode ──
  function generateBarcode() {
    let spareId = document.getElementById("spareId").value;
    if (!spareId) { alert("Please select a spare / item first."); return; }

    fetch("generate_barcode.php?spare=" + spareId)
      .then(r => r.text())
      .then(code => {
        code = code.trim();
        document.getElementById("barcode").value = code;
        showBarcode(code);
        document.getElementById("genBtn").disabled = true;
      })
      .catch(() => alert("Failed to generate barcode. Check generate_barcode.php"));
  }

  function showBarcode(code) {
    document.getElementById("barcodePreview").style.display = "block";
    JsBarcode("#barcodeSvg", code, {
      format: "CODE128", width: 2, height: 50,
      displayValue: true, fontSize: 13
    });
  }

  function printBarcode() {
    let content = document.getElementById("barcodePreview").innerHTML;
    let w = window.open('', '_blank');
    w.document.write("<html><body style='text-align:center; padding:20px;'>" + content + "</body></html>");
    w.document.close();
    w.print();
    w.close();
  }

  // ── Search Spare ──
  function searchSpare() {
    let term = document.getElementById("spareSearch").value.trim();
    if (term.length < 1) {
      document.getElementById("spareList").style.display = "none";
      return;
    }

    fetch("search_spare.php?term=" + encodeURIComponent(term))
      .then(r => r.json())
      .then(data => {
        let list = document.getElementById("spareList");
        list.innerHTML = "";

        if (!data.length) {
          list.innerHTML = '<div style="padding:12px; color:#94a3b8; font-size:13px;">No results found</div>';
          list.style.display = "block";
          return;
        }

        data.forEach(item => {
          let div = document.createElement("div");
          div.className = "spare-item";

          // thumbnail in dropdown
          let thumbSrc = (item.picture && item.picture !== 'no-image.png')
            ? "../" + item.picture
            : "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='36' height='36'%3E%3Crect width='36' height='36' fill='%23e2e8f0'/%3E%3Ctext x='50%25' y='55%25' font-size='16' text-anchor='middle' dominant-baseline='middle' fill='%2394a3b8'%3E📦%3C/text%3E%3C/svg%3E";

          div.innerHTML = `
            <img src="${thumbSrc}" alt="" onerror="this.style.display='none'">
            <div class="info">
              <strong>${item.spareName}</strong>
              <small># ${item.partNo} &middot; Rack: ${item.rackNumber}</small>
            </div>
          `;
          div.onclick = () => selectSpare(item);
          list.appendChild(div);
        });

        list.style.display = "block";
      })
      .catch(() => {
        document.getElementById("spareList").style.display = "none";
      });
  }

  function selectSpare(item) {
    document.getElementById("spareSearch").value = item.spareName;
    document.getElementById("spareId").value = item.id;
    document.getElementById("sparePicture").value = item.picture || '';
    document.getElementById("spareList").style.display = "none";
    document.getElementById("genBtn").disabled = false;

    // Load spare image into preview box
    loadSpareImage(item.picture, item.spareName);

    // Fetch existing stock qty for this spare
    fetch("get_stock_qty.php?spare=" + item.id)
      .then(r => r.json())
      .then(d => {
        let oldQty = d.qty || 0;
        document.getElementById("oldStock").value = oldQty;
        let newQty = +document.getElementById("qtyInput").value || 1;
        document.getElementById("availableQty").value = newQty + oldQty;
      })
      .catch(() => { });

    calc();
  }

  // ── Modals ──
  function openModal(id) { document.getElementById(id).style.display = 'block'; }
  function closeModal(id) { document.getElementById(id).style.display = 'none'; }

  function saveBrand() {
    let name = document.getElementById("bname").value.trim();
    if (!name) return alert("Enter a brand name.");
    fetch("save_brand.php", { method: "POST", body: new URLSearchParams({ name }) })
      .then(() => location.reload());
  }

  function saveModel() {
    let name = document.getElementById("mname").value.trim();
    if (!name) return alert("Enter a model name.");
    fetch("save_model.php", { method: "POST", body: new URLSearchParams({ name }) })
      .then(() => location.reload());
  }

  // ── Reset ──
  function resetForm() {
    document.getElementById("stockForm").reset();
    document.getElementById("spareSearch").value = "";
    document.getElementById("spareId").value = "";
    document.getElementById("oldStock").value = 0;
    document.getElementById("newStock").value = 1;
    document.getElementById("qtyInput").value = 1;
    document.getElementById("barcodePreview").style.display = "none";
    document.getElementById("genBtn").disabled = true;
    clearSpareImage();
    calc();
  }

  // ── Close dropdown on outside click ──
  document.addEventListener("click", function (e) {
    if (!e.target.closest(".search-wrap")) {
      document.getElementById("spareList").style.display = "none";
    }
  });

  // ── Close modal on overlay click ──
  document.querySelectorAll(".modal-overlay").forEach(m => {
    m.addEventListener("click", e => { if (e.target === m) m.style.display = "none"; });
  });

</script>

<?php include("../includes/footer.php"); ?>