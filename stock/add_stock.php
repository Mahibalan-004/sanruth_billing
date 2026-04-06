<?php require_once("../config/db.php"); ?>
<?php
$generatedBarcode = $_GET['barcode'] ?? '';
?>
<!DOCTYPE html>
<html>

<head>
    <title>Add Stock</title>

    <style>
        body {
            font-family: Arial;
            background: #eef1f5;
        }

        .container {
            width: 95%;
            margin: auto;
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
        }

        /* GRID LAYOUT */
        .grid {
            display: grid;
            grid-template-columns: 220px 1fr 400px;
            gap: 20px;
        }

        /* IMAGE */
        .image-box {
            height: 180px;
            border: 1px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .image-box img {
            max-width: 100%;
            max-height: 100%;
        }

        /* FORM */
        .form-group {
            margin-bottom: 8px;
        }

        label {
            font-size: 13px;
            font-weight: bold;
        }

        input,
        select {
            width: 100%;
            padding: 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        /* INLINE ROW */
        .row {
            display: flex;
            gap: 10px;
        }

        /* RIGHT PANEL */
        .card {
            background: #f8f9fb;
            padding: 10px;
            border-radius: 6px;
        }

        .card h4 {
            margin: 5px 0;
        }

        /* BUTTONS */
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-blue {
            background: #007bff;
            color: #fff;
        }

        .btn-green {
            background: #28a745;
            color: #fff;
        }

        .btn-gray {
            background: #6c757d;
            color: #fff;
        }

        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, .6)
        }

        .modal-content {
            background: #fff;
            width: 400px;
            margin: 100px auto;
            padding: 20px
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
</head>

<body>
    <?php include("../includes/header.php"); ?>
    <div class="container">
        <h2>Add New Stock</h2>

        <form method="POST" action="insert_stock.php" enctype="multipart/form-data">

            <div class="grid">

                <!-- IMAGE -->
                <div>

                    <div class="image-box" onclick="openImageOptions()">
                        <img id="preview" src="" alt="Upload Image">
                    </div>

                    <!-- REMOVE BUTTON -->
                    <button type="button" class="btn btn-gray" id="removeBtn" onclick="removeImage()" style="display:none; margin-top:5px;">
                        Remove Image
                    </button>

                    <!-- FILE INPUT -->
                    <input type="file" name="image" id="img" accept="image/*" hidden onchange="preview(event)">

                    <!-- CAMERA INPUT -->
                    <input type="file" id="cameraInput" accept="image/*" capture="environment" hidden onchange="preview(event)">

                </div>

                <!-- CENTER FORM -->
                <div>

                    <label>Spare *</label>

                    <input type="text" id="spareSearch" placeholder="Search Spare / Part No" onkeyup="searchSpare()" autocomplete="off">

                    <input type="hidden" name="spare" id="spareId">

                    <div id="spareList" style="
    border:1px solid #ccc;
    max-height:150px;
    overflow:auto;
    display:none;
    background:#fff;
    position:absolute;
    width:300px;
    z-index:1000;
"></div>

                    <br><br>

                    <label>Rack #</label>
                    <input type="text" id="rack" readonly>

                    <label>Part #</label>
                    <input type="text" id="part" readonly>

                    <div class="row">
                        <div style="flex:1;">
                            <label>Brand <button type="button" class="btn" onclick="openModal('brandModal')">+ Add</button></label>
                            <select name="brand">
                                <option value="">Select</option>
                                <?php
                                $res = mysqli_query($conn, "SELECT * FROM brand");
                                while ($r = mysqli_fetch_assoc($res)) {
                                    echo "<option value='{$r['id']}'>{$r['brandName']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div style="flex:1;">
                            <label>Model <button class="btn" type="button" onclick="openModal('modelModal')">+ Add</button></label>
                            <select name="model">
                                <option value="">Select</option>
                                <?php
                                $res = mysqli_query($conn, "SELECT * FROM model");
                                while ($r = mysqli_fetch_assoc($res)) {
                                    echo "<option value='{$r['id']}'>{$r['model']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div><br>

                    <div class="row">
                        <div>
                            <label>Qty</label>
                            <input type="number" id="qty" value="1" name="quantity">
                        </div>

                        <div style="flex:1;">
                            <label>Barcode</label>
                            <input type="text" name="barCode" id="barcode" readonly>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div>
                            <label>Min Qty</label>
                            <input type="number" name="minQty">
                        </div>

                        <div>
                            <label>Old</label>
                            <input type="number" id="old" value="0">
                        </div>

                        <div>
                            <label>New</label>
                            <input type="number" id="new" value="1">
                        </div>

                        <div>
                            <label>Warranty</label>
                            <input type="number" name="warrantyInMonths">
                        </div>
                    </div>

                </div>

                <!-- RIGHT SIDE (PRICE PANEL) -->
                <div class="card">

                    <h4>Price Per Qty</h4>

                    <div class="row">
                        <input type="number" id="actual" placeholder="Actual">
                        <input type="number" id="selling" placeholder="Selling">
                        <input type="number" id="gst" placeholder="GST%">
                    </div>

                    <h4>Total</h4>

                    <div class="row">
                        <input type="text" id="tActual" name="actualPricePerQty" readonly>
                        <input type="text" id="tSelling" name="sellingPricePerQty" readonly>
                        <input type="text" id="selled" name="selledPricePerUnit" readonly>
                    </div>

                </div>

            </div>

            <input type="hidden" name="actualPricePerUnit" id="actualUnit">
            <input type="hidden" name="sellingPricePerUnit" id="sellingUnit">
            <input type="hidden" name="availableQty" id="availableQty">

            <br>
            <div id="barcodePreview" style="display:none; text-align:center;">
                <svg id="barcodeSvg"></svg>
                <div id="barcodeText"></div>
                <br>
                <button type="button" onclick="printBarcode()">Print</button>
            </div>
            <br>

            <button type="button" class="btn" id="genBtn" onclick="generateBarcode()" disabled>Generate</button>
            <button type="submit" class="btn btn-green">Submit</button>
            <button type="reset" class="btn btn-gray">Reset</button>

        </form>
    </div>

    <!-- MODALS -->
    <div id="brandModal" class="modal">
        <div class="modal-content">
            <input type="text" id="bname" placeholder="Brand Name">
            <button onclick="saveBrand()">Save</button>
        </div>
    </div>

    <div id="modelModal" class="modal">
        <div class="modal-content">
            <input type="text" id="mname" placeholder="Model Name">
            <button onclick="saveModel()">Save</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    <script>
        window.onload = function() {
            let barcode = "<?php echo $generatedBarcode; ?>";

            if (barcode) {
                document.getElementById("barcode").value = barcode;

                // enable generate button
                document.getElementById("genBtn").disabled = false;

                // auto show preview
                showBarcode(barcode);
            }
        };

        // ================= IMAGE UPLOAD =================
        function openImageOptions() {
            let choice = confirm("Click OK for Camera 📷\nClick Cancel for Gallery 📁");

            if (choice) {
                document.getElementById("cameraInput").click();
            } else {
                document.getElementById("img").click();
            }
        }

        function preview(e) {
            let file = e.target.files[0];
            if (!file) return;

            let reader = new FileReader();
            reader.onload = function() {
                document.getElementById("preview").src = reader.result;
                document.getElementById("removeBtn").style.display = "inline-block";
            };
            reader.readAsDataURL(file);
        }

        function removeImage() {
            document.getElementById("preview").src = "";
            document.getElementById("img").value = "";
            document.getElementById("cameraInput").value = "";
            document.getElementById("removeBtn").style.display = "none";
        }


        // ================= PRICE CALCULATION =================
        function calc() {
            let q = +document.getElementById("new").value || 0;
            let a = +document.getElementById("actual").value || 0;
            let s = +document.getElementById("selling").value || 0;
            let g = +document.getElementById("gst").value || 0;

            document.getElementById("tActual").value = (a * q).toFixed(2);
            document.getElementById("tSelling").value = (s * q).toFixed(2);
            document.getElementById("selled").value = (s + (s * g / 100)).toFixed(2);

            document.getElementById("actualUnit").value = a;
            document.getElementById("sellingUnit").value = s;
            document.getElementById("availableQty").value = q;
        }

        // trigger calculation
        document.querySelectorAll("input").forEach(i => {
            i.addEventListener("input", calc);
        });


        // ================= BARCODE =================
        function generateBarcode() {
            let code = document.getElementById("barcode").value;

            if (!code) {
                alert("Please submit first!");
                return;
            }

            showBarcode(code);

            // disable again after use
            document.getElementById("genBtn").disabled = true;
        }

        function showBarcode(code) {
            document.getElementById("barcodePreview").style.display = "block";

            JsBarcode("#barcodeSvg", code, {
                format: "CODE128",
                width: 2,
                height: 50,
                displayValue: true
            });

            document.getElementById("barcodeText").innerText = code;
        }

        function printBarcode() {
            let content = document.getElementById("barcodePreview").innerHTML;

            let w = window.open();
            w.document.write("<html><body style='text-align:center'>" + content + "</body></html>");
            w.print();
            w.close();
        }


        // ================= SPARE AUTO FILL =================
        function loadSpare(el) {
            let selected = el.options[el.selectedIndex];

            document.getElementById("rack").value = selected.getAttribute("data-rack") || "";
            document.getElementById("part").value = selected.getAttribute("data-part") || "";

            // optional auto item name
            let itemInput = document.querySelector("input[name='itemName']");
            if (itemInput) {
                itemInput.value = selected.getAttribute("data-name") || "";
            }
        }


        // ================= MODALS =================
        function openModal(id) {
            document.getElementById(id).style.display = 'block';
        }

        function saveBrand() {
            fetch("save_brand.php", {
                method: "POST",
                body: new URLSearchParams({
                    name: document.getElementById("bname").value
                })
            }).then(() => location.reload());
        }

        function saveModel() {
            fetch("save_model.php", {
                method: "POST",
                body: new URLSearchParams({
                    name: document.getElementById("mname").value
                })
            }).then(() => location.reload());
        }

        function searchSpare() {
            let term = document.getElementById("spareSearch").value;

            if (term.length < 1) {
                document.getElementById("spareList").style.display = "none";
                return;
            }

            fetch("search_spare.php?term=" + term)
                .then(res => res.json())
                .then(data => {

                    let list = document.getElementById("spareList");
                    list.innerHTML = "";

                    if (data.length === 0) {
                        list.style.display = "none";
                        return;
                    }

                    data.forEach(item => {
                        let div = document.createElement("div");
                        div.style.padding = "8px";
                        div.style.cursor = "pointer";
                        div.innerHTML = `<b>${item.spareName}</b> (${item.partNo})`;

                        div.onclick = function() {
                            selectSpare(item);
                        };

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
        }

        document.addEventListener("click", function(e) {
            if (!e.target.closest("#spareSearch")) {
                document.getElementById("spareList").style.display = "none";
            }
        });
    </script>

</body>

</html>