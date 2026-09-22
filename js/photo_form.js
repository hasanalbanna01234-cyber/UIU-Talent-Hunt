// ===============================
// Choose Photo Button
// ===============================

const choosePhotoBtn = document.getElementById("choosePhotobtn");
const photoFile = document.getElementById("photoFile");
const photoPreview = document.getElementById("photoPreview");

choosePhotoBtn.addEventListener("click", function () {

    photoFile.click();

});

// ===============================
// Photo Upload
// ===============================

photoFile.addEventListener("change", function () {

    const file = this.files[0];

    if (!file) return;

    // Allow only image files

    if (!file.type.startsWith("image/")) {

        alert("Please select a valid image file.");

        this.value = "";

        return;

    }

    // Maximum size = 10MB

    if (file.size > 10 * 1024 * 1024) {

        alert("Maximum image size is 10 MB.");

        this.value = "";

        return;

    }

    // Show success message

    document.getElementById("selectedPhoto").innerHTML =
        `<i class="fa-solid fa-circle-check"></i> ${file.name} selected successfully`;

    // File Name

    document.getElementById("fileName").textContent = file.name;

    // File Format

    const extension = file.name.split(".").pop().toUpperCase();

    document.getElementById("fileFormat").textContent = extension;

    // File Size

    const sizeMB = (file.size / (1024 * 1024)).toFixed(2);

    document.getElementById("fileSize").textContent = sizeMB + " MB";

    // Status

    document.getElementById("fileStatus").innerHTML =
        `<i class="fa-solid fa-circle-check"></i> Ready for Upload`;

    // Preview Image

    const reader = new FileReader();

    reader.onload = function (e) {

        photoPreview.src = e.target.result;

        photoPreview.style.display = "block";

    };

    reader.readAsDataURL(file);

    // Detect Resolution

    const img = new Image();

    img.onload = function () {

        document.getElementById("fileResolution").textContent =
            img.width + " × " + img.height;

    };

    img.src = URL.createObjectURL(file);

});

// ===============================
// Publish / Draft Buttons
// ===============================

const publishBtn = document.querySelector(".publish-btn");
const draftBtn = document.querySelector(".draft-btn");
const photoUploadForm = document.getElementById("photoForm");
const statusField = document.getElementById("statusField");

function validateAndSubmit(isDraft) {

    const title = document.getElementById("photoTitle").value.trim();

    const category = document.getElementById("photoCategory").value;

    const agree = document.getElementById("agree").checked;

    if (title === "") {

        alert("Please enter a photo title.");

        return;

    }

    if (category === "") {

        alert("Please select a talent category.");

        return;

    }

    if (photoFile.files.length === 0) {

        alert("Please upload a photo.");

        return;

    }

    if (!isDraft && !agree) {

        alert("Please agree to the community guidelines.");

        return;

    }

    statusField.value = isDraft ? "draft" : "published";
    photoUploadForm.submit();

}

publishBtn.addEventListener("click", function () {
    validateAndSubmit(false);
});

draftBtn.addEventListener("click", function () {
    validateAndSubmit(true);
});