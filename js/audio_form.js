// ===============================
// Choose Audio Button
// ===============================

const chooseAudioBtn = document.getElementById("chooseAudiobtn");
const audioFile = document.getElementById("audioFile");

chooseAudioBtn.addEventListener("click", function () {
    audioFile.click();
});


// ===============================
// Audio Upload
// ===============================

audioFile.addEventListener("change", function () {

    const file = this.files[0];

    if (!file) return;

    // Show selected audio name

    document.getElementById("selectedAudio").innerHTML =`<i class="fa-solid fa-circle-check"></i> ${file.name} selected successfully`;

    // File Name

    document.getElementById("fileName").textContent = file.name;

    // Format

    const extension = file.name.split(".").pop().toUpperCase();

    document.getElementById("fileFormat").textContent = extension;

    // File Size

    const sizeMB = (file.size / (1024 * 1024)).toFixed(2);

    document.getElementById("fileSize").textContent = sizeMB + " MB";

    // Status

    document.getElementById("fileStatus").textContent = "✔ Ready for Upload";

    // Read Audio Metadata

    const audio = document.createElement("audio");

    audio.preload = "metadata";

    audio.src = URL.createObjectURL(file);

    audio.onloadedmetadata = function () {

        // Duration

        const duration = Math.floor(audio.duration);

        const minutes = Math.floor(duration / 60);
        const seconds = duration % 60;

        document.getElementById("fileDuration").textContent =
            `${minutes}m ${seconds}s`;

        // Estimated Bitrate

        const bitrate = Math.round((file.size * 8) / audio.duration / 1000);

        document.getElementById("fileBitrate").textContent =
            bitrate + " kbps";

        URL.revokeObjectURL(audio.src);

    };

});


// ===============================
// Thumbnail Upload
// ===============================

const chooseThumbBtn = document.getElementById("chooseThumbBtn");
const thumbnailFile = document.getElementById("thumbnailfile");
const thumbnailPreview = document.getElementById("thumbnailPreview");

chooseThumbBtn.addEventListener("click", function () {

    thumbnailFile.click();

});

thumbnailFile.addEventListener("change", function () {

    const file = this.files[0];

    if (!file) return;

    const reader = new FileReader();

    reader.onload = function (e) {

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
const audioUploadForm = document.getElementById("audioForm");
const statusField = document.getElementById("statusField");

function validateAndSubmit(isDraft) {

    const title = document.getElementById("audioTitle").value.trim();

    const category = document.getElementById("audioCategory").value;

    const agree = document.getElementById("agree").checked;

    if (title === "") {

        alert("Please enter an audio title.");

        return;

    }

    if (category === "") {

        alert("Please select a talent category.");

        return;

    }

    if (audioFile.files.length === 0) {

        alert("Please upload an audio file.");

        return;

    }

    if (thumbnailFile.files.length === 0) {

        alert("Please upload a cover image.");

        return;

    }

    if (!isDraft && !agree) {

        alert("Please agree to the community guidelines.");

        return;

    }

    statusField.value = isDraft ? "draft" : "published";
    audioUploadForm.submit();

}

publishBtn.addEventListener("click", function () {
    validateAndSubmit(false);
});

draftBtn.addEventListener("click", function () {
    validateAndSubmit(true);
});