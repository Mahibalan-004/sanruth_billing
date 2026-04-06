<?php require_once("../config/db.php"); ?>
<?php
$generatedBarcode = $_GET['barcode'] ?? '';
?>
<?php include("../includes/header.php"); ?>

<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 25px;">
        <h2>Add New Stock Entry</h2>
        <a href="list.php" style="padding:8px 16px; background:#f4f4f4; color:#333; text-decoration:none; border-radius:6px; font-weight:600; font-size:13px; border:1px solid #ddd;">&larr; View Stock List</a>
    </div>

    <form method="POST" action="insert_stock.php" enctype="multipart/form-data">

        <div class="grid">

            <!-- LEFT COLUMN: IMAGE UPLOAD -->
            <div class="column-left">
                <div class="image-box" onclick="openImageOptions()" style="background:#f8fafc; border:2px dashed #cbd5e1; height:240px; border-radius:12px; display:flex; justify-content:center; align-items:center; cursor:pointer; overflow:hidden; position:relative;">
                    <img id="preview" src="" alt="Upload Image" style="max-width:100%; max-height:100%; object-fit:contain;">
                    <div id="uploadPlaceholder" style="text-align:center; color:#64748b;">
                        <span style="font-size:32px; display:block;">+</span>
                        <span style="font-size:12px; font-weight:600;">Add Product Image</span>
                    </div>
                </div>

                <div style="margin-top:10px; display:flex; gap:10px;">
                    <button type="button" class="btn-secondary" id="removeBtn" onclick="removeImage()" style="flex:1; padding:8px; display:none; background:#ef44441a; color:#ef4444; border:1px solid #ef444433; border-radius:6px; cursor:pointer; font-weight:600; font-size:13px;">Delete Image</button>
                    <button type="button" class="btn-primary" onclick="openImageOptions()" style="flex:1; padding:8px; background:#2563eb; color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:600; font-size:13px;">Change Image</button>
                </div>

                <input type="file" name="image" id="img" accept="image/*" hidden onchange="preview(event)">
                <input type="file" id="cameraInput" accept="image/*" capture="environment" hidden onchange="preview(event)">
            </div>

            <!-- CENTER COLUMN: PRODUCT DATA -->
            <div class="column-center">
                <div class="form-section">
                    <label>Select Spare / Part # *</label>
                    <div style="position:relative;">
                        <input type="text" id="spareSearch" placeholder="Search by name or part number..." onkeyup="searchSpare()" autocomplete="off" required style="padding:10px 12px; width:100%; border:1px solid #ddd; border-radius:8px; font-size:14px;">
                        <div id="spareList" style="border:1px solid #ddd; max-height:220px; overflow:auto; display:none; background:#fff; position:absolute; width:100%; z-index:1000; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1); border-radius:0 0 8px 8px;"></div>
                    </div>
                    <input type="hidden" name="spare" id="spareId">
                </div>

                <div class="grid-2-col" style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-top:20px;">
                    <div class="form-section">
                        <label>Rack Location</label>
                        <input type="text" id="rack" readonly style="background:#f1f5f9; cursor:not-allowed; padding:10px 12px; width:100%; border:1px solid #ddd; border-radius:8px; font-weight:600; color:#475569;">
                    </div>
                    <div class="form-section">
                        <label>Part Number</label>
                        <input type="text" id="part" readonly style="background:#f1f5f9; cursor:not-allowed; padding:10px 12px; width:100%; border:1px solid #ddd; border-radius:8px; font-weight:600; color:#475569;">
                    </div>
                </div>

                <div class="grid-2-col" style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-top:20px;">
                    <div class="form-section">
                        <label>Brand <span onclick="openModal('brandModal')" style="float:right; font-size:11px; color:#2563eb; cursor:pointer;">+ Add New</span></label>
                        <select name="brand" style="padding:10px 12px; width:100%; border:1px solid #ddd; border-radius:8px; appearance:none; background:url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23CBD5E1%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22/%3E%3C/svg%3E') no-repeat right 12px center; background-size: 10px; font-weight:500;">
                            <option value="">-- Generic / None --</option>
                            <?php
                            $res = mysqli_query($conn, "SELECT * FROM brand");
                            while ($r = mysqli_fetch_assoc($res)) {
                                echo "<option value='{$r['id']}'>{$r['brandName']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-section">
                        <label>Model <span onclick="openModal('modelModal')" style="float:right; font-size:11px; color:#2563eb; cursor:pointer;">+ Add New</span></label>
                        <select name="model" style="padding:10px 12px; width:100%; border:1px solid #ddd; border-radius:8px; appearance:none; background:url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23CBD5E1%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22/%3E%3C/svg%3E') no-repeat right 12px center; background-size: 10px; font-weight:500;">
                            <option value="">-- Standard --</option>
                            <?php
                            $res = mysqli_query($conn, "SELECT * FROM model");
                            while ($r = mysqli_fetch_assoc($res)) {
                                echo "<option value='{$r['id']}'>{$r['model']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-section" style="margin-top:20px; display:flex; gap:15px; align-items:center;">
                    <div style="flex:1;">
                        <label>New Quantity In Stock</label>
                        <input type="number" id="new" value="1" name="quantity" style="padding:10px 12px; width:100%; border:1px solid #ddd; border-radius:8px; font-weight:700; font-size:18px; color:#1e293b;">
                    </div>
                    <div style="flex:1;">
                        <label>Warranty (Months)</label>
                        <input type="number" name="warrantyInMonths" value="0" style="padding:10px 12px; width:100%; border:1px solid #ddd; border-radius:8px; font-weight:700; font-size:18px; color:#1e293b;">
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: PRICING & ACTION -->
            <div class="column-right">
                <div class="card pricing-card" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:20px;">
                    <h4 style="margin-bottom:15px; color:#334155; font-size:14px; text-transform:uppercase; letter-spacing:0.05em; border-bottom:1px solid #e2e8f0; padding-bottom:8px;">Pricing Configuration</h4>

                    <div class="form-section">
                        <label>Actual Buying Price (Unit)</label>
                        <input type="number" id="actual" placeholder="0.00" style="padding:12px; border:1px solid #ddd; border-radius:8px; width:100%; font-size:16px; font-weight:600; margin-bottom:15px;">
                    </div>

                    <div class="form-section">
                        <label>Selling Price (Unit)</label>
                        <input type="number" id="selling" placeholder="0.00" style="padding:12px; border:1px solid #ddd; border-radius:8px; width:100%; font-size:16px; font-weight:600; margin-bottom:15px;">
                    </div>

                    <div class="form-section">
                        <label>GST Percentage (%)</label>
                        <input type="number" id="gst" value="18" style="padding:12px; border:1px solid #ddd; border-radius:8px; width:100%; font-size:16px; font-weight:600; margin-bottom:15px;">
                    </div>

                    <div style="background:#fff; border-radius:8px; padding:15px; border:1px solid #e2e8f0;">
                         <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span style="font-size:12px; color:#64748b; font-weight:600;">Total Actual Value:</span>
                            <span id="displayActual" style="font-size:14px; color:#334155; font-weight:700;">₹ 0.00</span>
                         </div>
                         <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span style="font-size:12px; color:#64748b; font-weight:600;">Final Sell Price:</span>
                            <span id="displaySelled" style="font-size:14px; color:#10b981; font-weight:800;">₹ 0.00</span>
                         </div>
                         <div style="font-size:10px; color:#94a3b8; text-align:center; padding-top:5px; border-top:1px dashed #e2e8f0;">
                             Includes Calculated GST
                         </div>
                    </div>
                </div>

                <div class="form-section" style="margin-top:20px;">
                    <label>Barcode Identifier</label>
                    <input type="text" name="barCode" id="barcode" readonly style="background:#f8fafc; border:1px dotted #cbd5e1; text-align:center; font-family:monospace; padding:10px; border-radius:8px; font-size:16px; width:100%; margin-bottom:10px;">
                    <button type="button" id="genBtn" onclick="generateBarcode()" disabled style="width:100%; padding:10px; border-radius:8px; border:1px solid #2563eb; color:#2563eb; background:#fff; font-weight:700; cursor:pointer;" onmouseover="this.style.background='#2563eb11'" onmouseout="this.style.background='white'">Show Barcode Preview</button>
                    
                    <div id="barcodePreview" style="display:none; text-align:center; margin-top:20px; padding:15px; background:#fff; border:1px solid #e2e8f0; border-radius:8px; box-shadow:0 10px 15px -3px rgba(0,0,0,0.05);">
                        <svg id="barcodeSvg" style="max-width:100%;"></svg>
                        <br><button type="button" onclick="printBarcode()" style="margin-top:10px; padding:5px 15px; border-radius:4px; background:#475569; color:#fff; border:none; cursor:pointer; font-weight:600; font-size:11px;">Print Label</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- PRICING RECAP (HIDDEN) -->
        <input type="hidden" name="actualPricePerQty" id="tActual">
        <input type="hidden" name="sellingPricePerQty" id="tSelling">
        <input type="hidden" name="selledPricePerUnit" id="selled">
        <input type="hidden" name="actualPricePerUnit" id="actualUnit">
        <input type="hidden" name="sellingPricePerUnit" id="sellingUnit">
        <input type="hidden" name="availableQty" id="availableQty">

        <!-- SUBMIT ACTIONS -->
        <div style="margin-top:40px; padding-top:25px; border-top:1px solid #e2e8f0; display:flex; justify-content:center; gap:20px;">
            <button type="reset" class="btn-cancel" style="padding:15px 40px; background:#f1f5f9; color:#475569; border:none; border-radius:10px; font-weight:700; font-size:16px; cursor:pointer;">Reset Form</button>
            <button type="submit" class="btn-submit" style="padding:15px 80px; background:linear-gradient(to right, #10b981, #059669); color:#fff; border:none; border-radius:10px; font-weight:700; font-size:16px; cursor:pointer; box-shadow:0 10px 15px -3px rgba(16, 185, 129, 0.3);">Add To Inventory</button>
        </div>

    </form>
</div>

<!-- MODALS -->
<div id="brandModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000;">
    <div class="modal-content" style="background:#fff; width:400px; margin:150px auto; padding:30px; border-radius:12px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
        <h3 style="margin-bottom:20px;">Add New Brand</h3>
        <input type="text" id="bname" placeholder="Brand Name" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; margin-bottom:20px;">
        <div style="display:flex; gap:10px;">
            <button onclick="document.getElementById('brandModal').style.display='none'" style="flex:1; padding:10px; border:none; background:#f1f5f9; border-radius:8px; cursor:pointer;">Cancel</button>
            <button onclick="saveBrand()" style="flex:1; padding:10px; border:none; background:#2563eb; color:#fff; border-radius:8px; cursor:pointer;">Save Brand</button>
        </div>
    </div>
</div>

<div id="modelModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000;">
    <div class="modal-content" style="background:#fff; width:400px; margin:150px auto; padding:30px; border-radius:12px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
        <h3 style="margin-bottom:20px;">Add New Model</h3>
        <input type="text" id="mname" placeholder="Model Name" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; margin-bottom:20px;">
        <div style="display:flex; gap:10px;">
            <button onclick="document.getElementById('modelModal').style.display='none'" style="flex:1; padding:10px; border:none; background:#f1f5f9; border-radius:8px; cursor:pointer;">Cancel</button>
            <button onclick="saveModel()" style="flex:1; padding:10px; border:none; background:#2563eb; color:#fff; border-radius:8px; cursor:pointer;">Save Model</button>
        </div>
    </div>
</div>

<style>
    .container { width: 95%; margin: auto; background: #fff; padding: 40px; border-radius: 16px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); }
    .grid { display: grid; grid-template-columns: 280px 1fr 340px; gap: 40px; }
    label { font-size: 13px; font-weight: 600; color: #64748b; margin-bottom: 6px; display: block; }
    h2 { font-weight: 800; color: #1e293b; letter-spacing: -0.025em; }
    @media (max-width: 1100px) { .grid { grid-template-columns: 1fr; } .column-left, .column-right { max-width: 500px; margin: auto; width: 100%; } }
</style>

<script>
    window.onload = function() {
        let barcode = "<?php echo $generatedBarcode; ?>";
        if (barcode) {
            document.getElementById("barcode").value = barcode;
            document.getElementById("genBtn").disabled = false;
            showBarcode(barcode);
        }
    };

    // IMAGE UPLOAD logic
    function openImageOptions() {
        let choice = confirm("Open Camera 📷? (Cancel for Gallery 📁)");
        if (choice) { document.getElementById("cameraInput").click(); } 
        else { document.getElementById("img").click(); }
    }

    function preview(e) {
        let file = e.target.files[0];
        if (!file) return;
        let reader = new FileReader();
        reader.onload = function() {
            document.getElementById("preview").src = reader.result;
            document.getElementById("uploadPlaceholder").style.display = "none";
            document.getElementById("removeBtn").style.display = "inline-block";
        };
        reader.readAsDataURL(file);
    }

    function removeImage() {
        document.getElementById("preview").src = "";
        document.getElementById("uploadPlaceholder").style.display = "block";
        document.getElementById("img").value = "";
        document.getElementById("cameraInput").value = "";
        document.getElementById("removeBtn").style.display = "none";
    }

    // CALCULATION logic
    function calc() {
        let q = +document.getElementById("new").value || 0;
        let a = +document.getElementById("actual").value || 0;
        let s = +document.getElementById("selling").value || 0;
        let g = +document.getElementById("gst").value || 0;

        let totalActual = (a * q).toFixed(2);
        let totalSelling = (s * q).toFixed(2);
        let finalSelled = (s + (s * g / 100)).toFixed(2);

        document.getElementById("tActual").value = totalActual;
        document.getElementById("tSelling").value = totalSelling;
        document.getElementById("selled").value = finalSelled;
        
        document.getElementById("displayActual").innerText = "₹ " + totalActual;
        document.getElementById("displaySelled").innerText = "₹ " + finalSelled;

        document.getElementById("actualUnit").value = a;
        document.getElementById("sellingUnit").value = s;
        document.getElementById("availableQty").value = q;
    }

    document.querySelectorAll("input").forEach(i => {
        i.addEventListener("input", calc);
    });

    // BARCODE logic
    function generateBarcode() {
        let code = document.getElementById("barcode").value;
        if (!code) return;
        showBarcode(code);
        document.getElementById("genBtn").disabled = true;
    }

    function showBarcode(code) {
        document.getElementById("barcodePreview").style.display = "block";
        JsBarcode("#barcodeSvg", code, { format: "CODE128", width: 2, height: 40, displayValue: true });
    }

    function printBarcode() {
        let content = document.getElementById("barcodePreview").innerHTML;
        let w = window.open();
        w.document.write("<html><body style='text-align:center'>" + content + "</body></html>");
        w.print(); w.close();
    }

    // SEARCH logic
    function searchSpare() {
        let term = document.getElementById("spareSearch").value;
        if (term.length < 1) { document.getElementById("spareList").style.display = "none"; return; }

        fetch("search_spare.php?term=" + term)
            .then(res => res.json())
            .then(data => {
                let list = document.getElementById("spareList");
                list.innerHTML = "";
                if (data.length === 0) { list.style.display = "none"; return; }

                data.forEach(item => {
                    let div = document.createElement("div");
                    div.style.padding = "12px"; div.style.cursor = "pointer"; div.style.borderBottom = "1px solid #f1f5f9";
                    div.innerHTML = `<span style="font-weight:700;">${item.spareName}</span> <span style="color:#64748b; font-size:11px;"># ${item.partNo}</span>`;
                    div.onclick = function() { selectSpare(item); };
                    list.appendChild(div);
                });
                list.style.display = "block";
            });
    }

    function selectSpare(item) {
        document.getElementById("spareSearch").value = item.spareName;
        document.getElementById("spareId").value = item.id;
        document.getElementById("rack").value = item.rackNumber;
        document.getElementById("part").value = item.partNo;
        document.getElementById("spareList").style.display = "none";
        calc();
    }

    function openModal(id) { document.getElementById(id).style.display = 'block'; }

    function saveBrand() {
        fetch("save_brand.php", { method: "POST", body: new URLSearchParams({ name: document.getElementById("bname").value }) })
        .then(() => location.reload());
    }

    function saveModel() {
        fetch("save_model.php", { method: "POST", body: new URLSearchParams({ name: document.getElementById("mname").value }) })
        .then(() => location.reload());
    }

    document.addEventListener("click", function(e) {
        if (!e.target.closest("#spareSearch")) { document.getElementById("spareList").style.display = "none"; }
    });
</script>

<?php include("../includes/footer.php"); ?>