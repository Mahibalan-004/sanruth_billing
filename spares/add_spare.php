<?php require_once("../config/db.php"); ?>

<!DOCTYPE html>
<html>
<head>
<title>Add Spare</title>

<style>
body{ font-family:Arial; background:#eef1f5; }

.container{
    width:80%; margin:auto; background:#fff;
    padding:20px; border-radius:8px;
}

.row{ display:flex; gap:20px; align-items:center; }

.image-box{
    width:180px; height:150px;
    border:1px dashed #ccc;
    display:flex; justify-content:center; align-items:center;
    cursor:pointer;
}
.image-box img{ max-width:100%; max-height:100%; }

input{
    padding:8px; width:100%;
    border:1px solid #ccc; border-radius:5px;
}

.btn{ padding:8px 15px; border:none; cursor:pointer; }
.btn-blue{ background:#007bff; color:#fff; }
.btn-gray{ background:#6c757d; color:#fff; }
</style>

</head>

<body>

<div class="container">

<h2>Spare Info</h2>

<form method="POST" action="insert_spare.php" enctype="multipart/form-data">

<div class="row">

<!-- IMAGE -->
<div>
    <div class="image-box" onclick="openImageOptions()">
        <img id="preview">
    </div>

    <button type="button" class="btn btn-gray" id="removeBtn" onclick="removeImage()" style="display:none;">Remove</button>

    <input type="file" name="image" id="img" hidden onchange="preview(event)">
    <input type="file" id="cameraInput" accept="image/*" capture="environment" hidden onchange="preview(event)">
</div>

<!-- FORM -->
<div style="flex:1">

<label>Spare Name *</label>
<input type="text" name="spareName" required>

<br><br>

<label>Part # *</label>
<input type="text" name="partNo" required>

<br><br>

<label>Rack #</label>
<input type="text" name="rackNumber">

<br><br>

<label>
<input type="checkbox" name="active" checked> Active
</label>

</div>

</div>

<br>

<button type="submit" class="btn btn-blue">Submit</button>
<button type="reset" class="btn btn-gray">Reset</button>

</form>

</div>

<script>
function openImageOptions(){
    let choice = confirm("OK = Camera 📷\nCancel = Gallery 📁");
    if(choice){
        document.getElementById("cameraInput").click();
    }else{
        document.getElementById("img").click();
    }
}

function preview(e){
    let file=e.target.files[0];
    if(!file) return;

    let reader=new FileReader();
    reader.onload=function(){
        document.getElementById("preview").src=reader.result;
        document.getElementById("removeBtn").style.display="block";
    };
    reader.readAsDataURL(file);
}

function removeImage(){
    document.getElementById("preview").src="";
    document.getElementById("img").value="";
    document.getElementById("cameraInput").value="";
    document.getElementById("removeBtn").style.display="none";
}
</script>

</body>
</html>