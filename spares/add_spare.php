<?php require_once("../config/db.php"); ?>
<?php include("../includes/header.php"); ?>

<div class="container">
    <h2>Add Spare Part</h2>

    <form method="POST" action="insert_spare.php" enctype="multipart/form-data" id="spareForm">
        <div class="row">
            <!-- IMAGE -->
            <div style="width: 220px; flex-shrink: 0;">
                <div class="image-box" onclick="openImageOptions()" style="width:100%; height:180px; border:2px dashed #cbd5e1; display:flex; flex-direction:column; justify-content:center; align-items:center; cursor:pointer; background:#f8fafc; border-radius:12px; overflow:hidden; position:relative;">
                    <img id="preview" style="max-width:100%; max-height:100%; object-fit:contain; display:none;">
                    <div id="imagePlaceholder" style="text-align:center; color:#64748b;">
                        <span style="font-size:32px; display:block;">🖼️</span>
                        <span style="font-size:12px; font-weight:600;">No Image Selected</span>
                    </div>
                </div>

                <!-- CHOICE BUTTONS -->
                <div style="margin-top:15px; display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                    <button type="button" class="choice-btn" onclick="openCameraModal()" style="padding:10px; background:#fff; border:1px solid #e2e8f0; border-radius:8px; cursor:pointer; display:flex; flex-direction:column; align-items:center; transition:0.2s;">
                        <span style="font-size:20px;">📷</span>
                        <span style="font-size:11px; font-weight:700; color:#475569; margin-top:4px;">Camera</span>
                    </button>
                    <button type="button" class="choice-btn" onclick="document.getElementById('img').click()" style="padding:10px; background:#fff; border:1px solid #e2e8f0; border-radius:8px; cursor:pointer; display:flex; flex-direction:column; align-items:center; transition:0.2s;">
                        <span style="font-size:20px;">📁</span>
                        <span style="font-size:11px; font-weight:700; color:#475569; margin-top:4px;">Gallery</span>
                    </button>
                </div>

                <button type="button" id="removeBtn" onclick="removeImage()" style="display:none; width:100%; margin-top:10px; padding:10px; border:none; border-radius:8px; background:#fee2e2; color:#ef4444; font-weight:700; font-size:13px; cursor:pointer;">Delete Image</button>

                <!-- Hidden Inputs -->
                <input type="file" name="image" id="img" accept="image/*" capture="environment" hidden onchange="preview(event)">
                <input type="hidden" name="camera_image" id="camera_image">
            </div>

            <!-- FORM -->
            <div style="flex:1; margin-left: 20px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Spare Name *</label>
                <input type="text" name="spareName" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">

                <br><br>

                <label style="display:block; font-weight:bold; margin-bottom:5px;">Part # *</label>
                <input type="text" name="partNo" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">

                <br><br>

                <label style="display:block; font-weight:bold; margin-bottom:5px;">Rack #</label>
                <input type="text" name="rackNumber" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">

                <br><br>

                <label style="display:inline-block; font-weight:bold;">
                    <input type="checkbox" name="active" checked> Active
                </label>
            </div>
        </div>

        <br>
        <button type="submit" class="btn btn-blue" style="padding:10px 20px; background:#2563eb; color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:600;">Submit Spare</button>
        <button type="reset" class="btn btn-gray" onclick="resetFormImage()" style="padding:10px 20px; background:#6b7280; color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:600; margin-left:10px;">Reset</button>
    </form>
</div>

<!-- CAMERA MODAL -->
<div id="cameraModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.75); z-index:9999; justify-content:center; align-items:center;">
    <div style="background:#fff; width:90%; max-width:700px; padding:20px; border-radius:14px; position:relative; box-shadow:0 10px 30px rgba(0,0,0,0.3);">
        <button type="button" onclick="closeCameraModal()" style="position:absolute; top:10px; right:10px; border:none; background:#ef4444; color:#fff; border-radius:8px; padding:8px 12px; cursor:pointer; font-weight:700;">X</button>

        <h3 style="margin:0 0 15px 0; color:#1e293b;">Open Camera</h3>

        <div style="display:flex; flex-direction:column; align-items:center; gap:15px;">
            <video id="cameraVideo" autoplay playsinline style="width:100%; max-width:600px; height:360px; background:#000; border-radius:12px; object-fit:cover;"></video>
            <canvas id="cameraCanvas" style="display:none;"></canvas>

            <div style="display:flex; gap:10px; flex-wrap:wrap; justify-content:center;">
                <button type="button" onclick="startCamera()" style="padding:10px 18px; background:#2563eb; color:#fff; border:none; border-radius:8px; cursor:pointer; font-weight:600;">Start Camera</button>
                <button type="button" onclick="capturePhoto()" style="padding:10px 18px; background:#16a34a; color:#fff; border:none; border-radius:8px; cursor:pointer; font-weight:600;">Capture</button>
                <button type="button" onclick="closeCameraModal()" style="padding:10px 18px; background:#64748b; color:#fff; border:none; border-radius:8px; cursor:pointer; font-weight:600;">Close</button>
            </div>

            <p style="font-size:13px; color:#64748b; text-align:center; margin:0;">
                Camera permission allow pannanum. Desktop / laptop-la browser support irundha open aagum.
            </p>
        </div>
    </div>
</div>

<style>
    .container {
        width: 90%;
        max-width: 800px;
        margin: auto;
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .row { display: flex; align-items: flex-start; }
    h2 { margin-bottom: 25px; color: #1e293b; font-weight: 700; }
    .choice-btn:hover { border-color: #2563eb !important; background: #eff6ff !important; }
</style>

<script>
let stream = null;

function openImageOptions() {
    let choice = confirm("OK = Camera 📷\nCancel = Gallery 📁");
    if (choice) {
        openCameraModal();
    } else {
        document.getElementById("img").click();
    }
}

function preview(e) {
    let file = e.target.files[0];
    if (!file) return;

    document.getElementById("camera_image").value = "";

    let reader = new FileReader();
    reader.onload = function() {
        document.getElementById("preview").src = reader.result;
        document.getElementById("preview").style.display = "block";
        document.getElementById("imagePlaceholder").style.display = "none";
        document.getElementById("removeBtn").style.display = "block";
    };
    reader.readAsDataURL(file);
}

function openCameraModal() {
    document.getElementById("cameraModal").style.display = "flex";
    startCamera();
}

async function startCamera() {
    // Check for Secure Context
    const isSecure = window.location.protocol === 'https:' || 
                     window.location.hostname === 'localhost' || 
                     window.location.hostname === '127.0.0.1';

    if (!isSecure) {
        let helpMsg = "Camera access is BLOCKED because you are using HTTP instead of HTTPS.\n\n";
        helpMsg += "Solutions:\n";
        helpMsg += "1. Use 'localhost' instead of your domain name.\n";
        helpMsg += "2. Use HTTPS for your domain.\n";
        helpMsg += "3. For Chrome: Go to 'chrome://flags/#unsafely-treat-insecure-origin-as-secure', add your URL, and Enable it.\n\n";
        helpMsg += "Would you like to try the mobile fallback (opens camera app)?";
        
        if (confirm(helpMsg)) {
            closeCameraModal();
            document.getElementById("img").click();
        }
        return;
    }

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        alert("Your browser doesn't support camera access or it's restricted.");
        return;
    }

    try {
        stopCamera();
        const video = document.getElementById("cameraVideo");

        const constraints = {
            video: {
                width: { ideal: 1280 },
                height: { ideal: 720 },
                facingMode: "environment" 
            },
            audio: false
        };

        try {
            stream = await navigator.mediaDevices.getUserMedia(constraints);
        } catch (e) {
            console.log("Retrying with simple video constraint...");
            stream = await navigator.mediaDevices.getUserMedia({ video: true });
        }

        video.srcObject = stream;
        
        video.onloadedmetadata = () => {
            video.play().catch(e => console.error("Video play failed:", e));
        };

    } catch (err) {
        alert("Camera Error: " + err.name + " - " + err.message);
        console.error(err);
    }
}

function capturePhoto() {
    const video = document.getElementById("cameraVideo");
    const canvas = document.getElementById("cameraCanvas");
    const previewImg = document.getElementById("preview");

    if (!stream) {
        alert("First camera start pannunga.");
        return;
    }

    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;

    const ctx = canvas.getContext("2d");
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    const imageData = canvas.toDataURL("image/png");

    previewImg.src = imageData;
    previewImg.style.display = "block";
    document.getElementById("imagePlaceholder").style.display = "none";
    document.getElementById("removeBtn").style.display = "block";

    document.getElementById("camera_image").value = imageData;
    document.getElementById("img").value = "";

    closeCameraModal();
}

function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
}

function closeCameraModal() {
    stopCamera();
    document.getElementById("cameraModal").style.display = "none";
}

function removeImage() {
    document.getElementById("preview").src = "";
    document.getElementById("preview").style.display = "none";
    document.getElementById("imagePlaceholder").style.display = "block";
    document.getElementById("img").value = "";
    document.getElementById("camera_image").value = "";
    document.getElementById("removeBtn").style.display = "none";
}

function resetFormImage() {
    setTimeout(() => {
        removeImage();
        closeCameraModal();
    }, 50);
}

window.addEventListener("beforeunload", stopCamera);
</script>

<?php include("../includes/footer.php"); ?>