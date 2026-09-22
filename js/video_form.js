// ===============================
// Choose Video Button
// ===============================

const chooseVideoBtn = document.getElementById("chooseVideobtn");
const videoFile = document.getElementById("videoFile");

chooseVideoBtn.addEventListener("click",function(){

    videoFile.click();

});


// ===============================
// Video Upload
// ===============================

videoFile.addEventListener("change",function(){

    const file = this.files[0];

    if(!file) return;
    
    document.getElementById("selectedVideo").innerHTML =`<i class="fa-solid fa-circle-check"></i> ${file.name}`;

    // File Name

    document.getElementById("fileName").textContent = file.name;

    // File Format

    const extension = file.name.split(".").pop().toUpperCase();

    document.getElementById("fileFormat").textContent = extension;

    // File Size

    const sizeMB = (file.size / (1024*1024)).toFixed(2);

    document.getElementById("fileSize").textContent = sizeMB + " MB";

    // Status

    document.getElementById("fileStatus").textContent = "✔ Ready for Upload";

    // Read Video Information

    const video = document.createElement("video");

    video.preload = "metadata";

    video.src = URL.createObjectURL(file);

    video.onloadedmetadata = function(){

        document.getElementById("fileDuration").textContent =

        Math.floor(video.duration) + " sec";

        if(video.videoHeight >=1080){

            document.getElementById("fileQuality").textContent = "1080p";

        }

        else if(video.videoHeight >=720){

            document.getElementById("fileQuality").textContent = "720p";

        }

        else{

            document.getElementById("fileQuality").textContent = "480p";

        }

    };

});


// ===============================
// Thumbnail Upload
// ===============================

const chooseThumbBtn = document.getElementById("chooseThumbBtn");

const thumbnailFile = document.getElementById("thumbnailfile");

const thumbnailPreview = document.getElementById("thumbnailPreview");

chooseThumbBtn.addEventListener("click",function(){

    thumbnailFile.click();

});

thumbnailFile.addEventListener("change",function(){

    const file = this.files[0];

    if(!file) return;
    
    const reader = new FileReader();

    reader.onload = function(e){

        thumbnailPreview.src = e.target.result;

        thumbnailPreview.style.display = "block";

    }

    reader.readAsDataURL(file);

});


// ===============================
// Publish / Draft Buttons
// ===============================

const publishBtn = document.querySelector(".publish-btn");
const draftBtn = document.querySelector(".draft-btn");
const videoUploadForm = document.getElementById("videoForm");
const statusField = document.getElementById("statusField");

function validateAndSubmit(isDraft) {

    const title = document.getElementById("videoTitle").value.trim();

    const category = document.getElementById("videoCategory").value;

    const agree = document.getElementById("agree").checked;

    if(title === ""){

        alert("Please enter a video title.");

        return;

    }

    if(category === ""){

        alert("Please select a talent category.");

        return;

    }

    if(videoFile.files.length === 0){

        alert("Please upload a video.");

        return;

    }

    if(!isDraft && !agree){

        alert("Please agree to the community guidelines.");

        return;

    }

    statusField.value = isDraft ? "draft" : "published";
    videoUploadForm.submit();

}

publishBtn.addEventListener("click", function(){
    validateAndSubmit(false);
});

draftBtn.addEventListener("click", function(){
    validateAndSubmit(true);
});