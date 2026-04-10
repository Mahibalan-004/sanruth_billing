<?php
require_once("../config/db.php");

// ================= VALIDATE ID =================
if (!isset($_GET['id']) || empty(trim($_GET['id']))) {
    die("Invalid Access");
}

$id = mysqli_real_escape_string($conn, trim($_GET['id']));

// ================= FETCH STOCK DATA =================
$query = "
    SELECT st.*,
           s.spareName, s.partNo, s.rackNumber, s.picture,
           b.brandName,
           m.model AS modelName,
           mc.machineName
    FROM stock st
    LEFT JOIN spares  s  ON st.spare   = s.id
    LEFT JOIN brand   b  ON st.brand   = b.id
    LEFT JOIN model   m  ON st.model   = m.id
    LEFT JOIN machine mc ON st.machine = mc.id
    WHERE st.id = '$id'
    LIMIT 1
";

$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Stock Not Found");
}

$data = mysqli_fetch_assoc($result);

// ================= UPDATE =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $newQty       = intval($_POST['availableQty']     ?? 0);
    $newStock     = intval($_POST['newStock']         ?? 0);
    $minQty       = intval($_POST['minQty']           ?? 0);
    $warrantyMons = intval($_POST['warrantyInMonths'] ?? 0);
    $brand        = intval($_POST['brand']            ?? 0);
    $model        = intval($_POST['model']            ?? 0);
    $serialNo     = trim($_POST['serialNo']           ?? '');
    $selled       = isset($_POST['selled']) ? 1 : 0;

    $actualUnit   = floatval($_POST['actualPricePerUnit']  ?? 0);
    $sellingUnit  = floatval($_POST['sellingPricePerUnit'] ?? 0);
    $gstPct       = floatval($_POST['gstPercentage']       ?? 0);

    // Re-calculate derived fields
    $selledUnit   = round($sellingUnit + ($sellingUnit * $gstPct / 100), 4);
    $actualQty    = round($actualUnit  * $newQty, 4);
    $sellingQty   = round($sellingUnit * $newQty, 4);

    $brandVal  = ($brand  > 0) ? $brand  : NULL;
    $modelVal  = ($model  > 0) ? $model  : NULL;
    $serialVal = ($serialNo !== '') ? $serialNo : NULL;

    $stmt = mysqli_prepare($conn, "
        UPDATE stock SET
            availableQty        = ?,
            quantity            = ?,
            actualPricePerUnit  = ?,
            actualPricePerQty   = ?,
            sellingPricePerUnit = ?,
            sellingPricePerQty  = ?,
            selledPricePerUnit  = ?,
            gstPercentage       = ?,
            brand               = ?,
            model               = ?,
            serialNo            = ?,
            warrantyInMonths    = ?,
            selled              = ?
        WHERE id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }

    // 14 params: i i d d d d d d i i s i i s
    // availableQty, quantity, actualUnit, actualQty, sellingUnit, sellingQty,
    // selledUnit, gstPct, brand, model, serialNo, warrantyMons, selled, id
    mysqli_stmt_bind_param(
        $stmt,
        "iiddddddiiisis",
        $newQty,
        $newStock,
        $actualUnit,
        $actualQty,
        $sellingUnit,
        $sellingQty,
        $selledUnit,
        $gstPct,
        $brandVal,
        $modelVal,
        $serialVal,
        $warrantyMons,
        $selled,
        $id
    );

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('✅ Stock Updated Successfully'); window.location='list.php';</script>";
        exit;
    } else {
        die("Update Failed: " . mysqli_stmt_error($stmt));
    }
}
?>
<?php include("../includes/header.php"); ?>

<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

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
    --shadow: 0 1px 3px rgba(0,0,0,0.08), 0 4px 16px rgba(0,0,0,0.04);
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); }

  .page-wrapper { max-width: 1200px; margin: 24px auto; padding: 0 16px; }

  .page-header {
    display: flex; justify-content: space-between;
    align-items: center; margin-bottom: 20px;
  }
  .page-header h2 { font-size: 18px; font-weight: 700; color: var(--text); letter-spacing: -0.02em; }

  .header-actions { display: flex; gap: 10px; align-items: center; }

  .btn-nav {
    font-size: 13px; font-weight: 600; color: var(--label);
    text-decoration: none; padding: 7px 14px;
    border: 1px solid var(--border); border-radius: var(--radius);
    background: var(--card); transition: all 0.15s; display: inline-flex; align-items: center; gap: 5px;
  }
  .btn-nav:hover { background: var(--bg); color: var(--text); }
  .btn-nav.new { background: var(--accent); color: #fff; border-color: var(--accent); }
  .btn-nav.new:hover { background: #1d4ed8; }

  .card {
    background: var(--card); border-radius: 14px;
    box-shadow: var(--shadow); padding: 28px; margin-bottom: 16px;
  }

  .main-grid {
    display: grid;
    grid-template-columns: 220px 1fr 300px;
    gap: 24px; align-items: start;
  }

  label {
    font-size: 12px; font-weight: 600; color: var(--label);
    display: block; margin-bottom: 5px;
  }

  input[type=text], input[type=number], select {
    width: 100%; padding: 9px 11px;
    border: 1px solid var(--border); border-radius: var(--radius);
    font-size: 13px; font-family: 'DM Sans', sans-serif;
    color: var(--text); background: #fff;
    transition: border-color 0.15s, box-shadow 0.15s;
    appearance: none; -webkit-appearance: none;
  }
  input:focus, select:focus {
    outline: none; border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
  }
  input:disabled, input[readonly] {
    background: var(--input-bg); color: var(--label); cursor: not-allowed;
  }

  select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%2394a3b8'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 10px center; padding-right: 28px;
  }

  .form-group { margin-bottom: 14px; }
  .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }

  /* ── Spare image ── */
  .spare-image-box {
    border: 1px solid var(--border); border-radius: 12px;
    height: 210px; overflow: hidden; background: var(--input-bg);
    display: flex; align-items: center; justify-content: center;
  }
  .spare-image-box img { max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 10px; }
  .spare-image-box .no-img { text-align: center; color: #94a3b8; }
  .spare-image-box .no-img .icon { font-size: 40px; margin-bottom: 6px; }
  .spare-image-box .no-img span { font-size: 11px; font-weight: 600; display: block; }

  .img-label {
    margin-top: 8px; text-align: center; font-size: 11px; font-weight: 600;
    color: var(--label); background: var(--input-bg); border: 1px solid var(--border);
    border-radius: 6px; padding: 4px 8px;
  }

  /* ── Top bar ── */
  .top-bar {
    display: flex; justify-content: flex-end;
    align-items: center; margin-bottom: 20px;
  }
  .toggle-row { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: var(--label); }
  .toggle { position: relative; width: 40px; height: 22px; }
  .toggle input { opacity: 0; width: 0; height: 0; }
  .slider { position: absolute; inset: 0; background: #cbd5e1; border-radius: 22px; cursor: pointer; transition: background 0.2s; }
  .slider:before { content: ''; position: absolute; width: 16px; height: 16px; left: 3px; top: 3px; background: #fff; border-radius: 50%; transition: transform 0.2s; }
  input:checked + .slider { background: var(--green); }
  input:checked + .slider:before { transform: translateX(18px); }

  /* ── Section divider ── */
  .section-divider {
    font-size: 11px; font-weight: 700; letter-spacing: 0.07em;
    text-transform: uppercase; color: var(--label);
    padding: 6px 0 10px; border-bottom: 1px solid var(--border); margin-bottom: 14px;
  }

  /* ── Barcode ── */
  .barcode-field { font-family: 'DM Mono', monospace; text-align: center; font-size: 15px; letter-spacing: 0.05em; }
  .barcode-box {
    margin-top: 14px; padding: 14px; background: #fff;
    border: 1px solid var(--border); border-radius: 10px; text-align: center;
  }

  /* ── Action Buttons ── */
  .action-bar {
    display: flex; justify-content: flex-end; gap: 10px;
    padding-top: 20px; border-top: 1px solid var(--border); margin-top: 20px;
  }
  .btn { padding: 10px 24px; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; border: none; font-family: 'DM Sans', sans-serif; transition: all 0.15s; }
  .btn-reset { background: var(--input-bg); color: var(--label); border: 1px solid var(--border); }
  .btn-reset:hover { background: #e2e8f0; }
  .btn-submit { background: linear-gradient(135deg, var(--green), #059669); color: #fff; padding: 10px 36px; box-shadow: 0 4px 12px rgba(16,185,129,0.3); }
  .btn-submit:hover { box-shadow: 0 6px 18px rgba(16,185,129,0.4); transform: translateY(-1px); }

  @media (max-width: 1024px) { .main-grid { grid-template-columns: 1fr; } }
</style>

<div class="page-wrapper">

  <div class="page-header">
    <h2>Stock Info</h2>
    <div class="header-actions">
      <a href="add_stock.php" class="btn-nav new">+ New</a>
      <a href="list.php" class="btn-nav">☰ Stocks</a>
    </div>
  </div>

  <div class="card">

    <!-- Selled toggle -->
    <div class="top-bar">
      <div class="toggle-row">
        <label class="toggle" style="margin:0;">
          <input type="checkbox" id="selledToggle" <?= $data['selled'] ? 'checked' : '' ?>>
          <span class="slider"></span>
        </label>
        Selled
      </div>
    </div>

    <form method="POST" id="editForm">
      <!-- pass selled value via hidden (checkbox outside form) -->
      <input type="hidden" name="selled" id="selledHidden" value="<?= $data['selled'] ? '1' : '0' ?>">

      <div class="main-grid">

        <!-- LEFT: Spare Image -->
        <div>
          <div class="spare-image-box">
            <?php
              $picPath = '';
              if (!empty($data['picture'])) {
                  // Try common path patterns
                  $candidates = [
                      "../uploads/spares/" . $data['picture'],
                      "../" . $data['picture'],
                      $data['picture']
                  ];
                  foreach ($candidates as $c) {
                      if (file_exists($c)) { $picPath = $c; break; }
                  }
              }
            ?>
            <?php if ($picPath): ?>
              <img src="<?= htmlspecialchars($picPath) ?>" alt="<?= htmlspecialchars($data['spareName']) ?>">
            <?php else: ?>
              <div class="no-img">
                <div class="icon">📦</div>
                <span>No Image</span>
              </div>
            <?php endif; ?>
          </div>
          <div class="img-label"><?= htmlspecialchars($data['spareName'] ?? '—') ?></div>
        </div>

        <!-- CENTER: Item Data -->
        <div>

          <div class="form-group">
            <label>Item Name <span style="color:var(--red)">*</span></label>
            <input type="text" value="<?= htmlspecialchars($data['spareName'] ?? '') ?>" disabled>
          </div>

          <div class="grid-2 form-group">
            <div>
              <label>Rack #</label>
              <input type="text" value="<?= htmlspecialchars($data['rackNumber'] ?? '') ?>" disabled>
            </div>
            <div>
              <label>Part #</label>
              <input type="text" value="<?= htmlspecialchars($data['partNo'] ?? '') ?>" disabled>
            </div>
          </div>

          <!-- Brand dropdown (editable) -->
          <div class="grid-2 form-group">
            <div>
              <label>Brand <span style="color:var(--red)">*</span></label>
              <select name="brand" id="brandSelect">
                <option value="">-- Select --</option>
                <?php
                $bRes = mysqli_query($conn, "SELECT * FROM brand ORDER BY brandName");
                while ($b = mysqli_fetch_assoc($bRes)) {
                    $sel = ($b['id'] == $data['brand']) ? 'selected' : '';
                    echo "<option value='{$b['id']}' $sel>" . htmlspecialchars($b['brandName']) . "</option>";
                }
                ?>
              </select>
            </div>
            <div>
              <label>Model <span style="color:var(--red)">*</span></label>
              <select name="model" id="modelSelect">
                <option value="">-- Select --</option>
                <?php
                $mRes = mysqli_query($conn, "SELECT * FROM model ORDER BY model");
                while ($m = mysqli_fetch_assoc($mRes)) {
                    $sel = ($m['id'] == $data['model']) ? 'selected' : '';
                    echo "<option value='{$m['id']}' $sel>" . htmlspecialchars($m['model']) . "</option>";
                }
                ?>
              </select>
            </div>
          </div>

          <div class="grid-2 form-group">
            <div>
              <label>Quantity <span style="color:var(--red)">*</span></label>
              <input type="number" name="availableQty" id="qtyInput" value="<?= intval($data['availableQty']) ?>" min="0" required oninput="calc()">
            </div>
            <div>
              <label>Barcode <small style="color:var(--label); font-weight:400;">System generated</small></label>
              <input type="text" class="barcode-field" value="<?= htmlspecialchars($data['barCode'] ?? '') ?>" disabled>
            </div>
          </div>

          <div class="form-group">
            <label>Serial #</label>
            <input type="text" name="serialNo" value="<?= htmlspecialchars($data['serialNo'] ?? '') ?>" placeholder="Optional">
          </div>

          <!-- Reorder / Stocked / Warranty -->
          <div style="display:grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap:12px;" class="form-group">
            <div>
              <label>Reorder Level</label>
              <small style="display:block; font-size:10px; color:var(--label); margin-bottom:4px;">Min Quantity</small>
              <input type="number" name="minQty" value="<?= intval($data['minQty'] ?? 0) ?>" min="0">
            </div>
            <div>
              <label>Stocked Qty</label>
              <small style="display:block; font-size:10px; color:var(--label); margin-bottom:4px;">Old Stock</small>
              <input type="number" id="oldStock" value="<?= intval($data['quantity'] ?? 0) ?>" disabled>
            </div>
            <div>
              <label>&nbsp;</label>
              <small style="display:block; font-size:10px; color:var(--label); margin-bottom:4px;">New Stock</small>
              <input type="number" name="newStock" id="newStock" value="<?= intval($data['availableQty']) ?>" min="0" oninput="syncNewStock(this)">
            </div>
            <div>
              <label>Warranty</label>
              <small style="display:block; font-size:10px; color:var(--label); margin-bottom:4px;">In Months</small>
              <input type="number" name="warrantyInMonths" value="<?= intval($data['warrantyInMonths'] ?? 0) ?>" min="0">
            </div>
          </div>

        </div>

        <!-- RIGHT: Pricing -->
        <div>
          <div class="section-divider">Price Per Quantity</div>

          <div class="form-group">
            <label>Actual Price</label>
            <input type="number" name="actualPricePerUnit" id="actual"
              value="<?= floatval($data['actualPricePerUnit']) ?>"
              step="0.01" min="0" oninput="calc()">
          </div>
          <div class="form-group">
            <label>Selling Price</label>
            <input type="number" name="sellingPricePerUnit" id="selling"
              value="<?= floatval($data['sellingPricePerUnit']) ?>"
              step="0.01" min="0" oninput="calc()">
          </div>
          <div class="form-group">
            <label>GST %</label>
            <input type="number" name="gstPercentage" id="gst"
              value="<?= floatval($data['gstPercentage'] ?? 0) ?>"
              step="0.01" min="0" oninput="calc()">
          </div>

          <div class="section-divider">Total Price</div>

          <div class="grid-3 form-group">
            <div>
              <label>Actual</label>
              <input type="number" id="displayActual" readonly
                style="background:var(--input-bg); font-weight:700;"
                value="<?= round(floatval($data['actualPricePerUnit']) * intval($data['availableQty']), 2) ?>">
            </div>
            <div>
              <label>Selling</label>
              <input type="number" id="displaySelling" readonly
                style="background:var(--input-bg); font-weight:700;"
                value="<?= round(floatval($data['sellingPricePerUnit']) * intval($data['availableQty']), 2) ?>">
            </div>
            <div>
              <label>Selled</label>
              <input type="number" id="displaySelled" readonly
                style="background:#f0fdf4; color:var(--green); font-weight:700;"
                value="<?= floatval($data['selledPricePerUnit'] ?? 0) ?>">
            </div>
          </div>

          <!-- Barcode display -->
          <div class="barcode-box">
            <svg id="barcodeSvg" style="max-width:100%;"></svg>
            <div style="font-size:11px; color:var(--label); margin-top:4px; font-family:'DM Mono',monospace;">
              <?= htmlspecialchars($data['barCode'] ?? '') ?>
            </div>
            <button type="button" onclick="printBarcode()"
              style="margin-top:8px; padding:5px 16px; border-radius:6px; background:#475569; color:#fff; border:none; cursor:pointer; font-size:11px; font-weight:700; font-family:'DM Sans',sans-serif;">
              🖨 Print
            </button>
          </div>

        </div>
      </div>

      <!-- Action Bar -->
      <div class="action-bar">
        <button type="submit" class="btn btn-submit">Submit</button>
        <button type="button" class="btn btn-reset" onclick="resetToOriginal()">Reset</button>
      </div>

    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
  // ── Original values for reset ──
  const ORIG = {
    qty:     <?= intval($data['availableQty']) ?>,
    actual:  <?= floatval($data['actualPricePerUnit']) ?>,
    selling: <?= floatval($data['sellingPricePerUnit']) ?>,
    gst:     <?= floatval($data['gstPercentage'] ?? 0) ?>
  };

  // ── Selled toggle sync ──
  document.getElementById('selledToggle').addEventListener('change', function() {
    document.getElementById('selledHidden').value = this.checked ? '1' : '0';
  });

  // ── Sync new stock → qty ──
  function syncNewStock(el) {
    document.getElementById('qtyInput').value = el.value;
    calc();
  }

  // ── Live price calculation ──
  function calc() {
    let q = +document.getElementById('qtyInput').value  || 0;
    let a = +document.getElementById('actual').value    || 0;
    let s = +document.getElementById('selling').value   || 0;
    let g = +document.getElementById('gst').value       || 0;

    document.getElementById('displayActual').value  = (a * q).toFixed(2);
    document.getElementById('displaySelling').value = (s * q).toFixed(2);
    document.getElementById('displaySelled').value  = (s + (s * g / 100)).toFixed(2);

    // keep newStock in sync
    document.getElementById('newStock').value = q;
  }

  // ── Reset to original DB values ──
  function resetToOriginal() {
    document.getElementById('qtyInput').value  = ORIG.qty;
    document.getElementById('actual').value    = ORIG.actual;
    document.getElementById('selling').value   = ORIG.selling;
    document.getElementById('gst').value       = ORIG.gst;
    document.getElementById('newStock').value  = ORIG.qty;
    calc();
  }

  // ── Barcode ──
  function printBarcode() {
    let content = document.querySelector('.barcode-box').innerHTML;
    let w = window.open('', '_blank');
    w.document.write("<html><body style='text-align:center; padding:20px;'>" + content + "</body></html>");
    w.document.close();
    w.print();
    w.close();
  }

  // ── Init ──
  window.addEventListener('DOMContentLoaded', function() {
    let code = "<?= addslashes($data['barCode'] ?? '') ?>";
    if (code) {
      JsBarcode("#barcodeSvg", code, {
        format: "CODE128", width: 2, height: 50,
        displayValue: false, fontSize: 13
      });
    }
    calc();
  });
</script>

<?php include("../includes/footer.php"); ?>