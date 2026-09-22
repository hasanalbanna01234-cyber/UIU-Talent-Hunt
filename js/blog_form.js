// =========================================
// Select Elements
// =========================================

const choosePhotoBtn = document.getElementById("choosePhotobtn");
const photoFile = document.getElementById("photoFile");
const photoPreview = document.getElementById("photoPreview");

const blogContent = document.getElementById("blogContent");

const wordCount = document.getElementById("wc");
const characterCount = document.getElementById("cc");
const publishBtn = document.querySelector(".publish-btn");


// =========================================
// Choose Cover Image
// =========================================

choosePhotoBtn.addEventListener("click", function () {

    photoFile.click();

});


// =========================================
// Upload Cover Image
// =========================================

photoFile.addEventListener("change", function () {

    const file = this.files[0];

    if (!file) return;

    // Allowed image formats

    const allowedTypes = [

        "image/jpeg",
        "image/png",
        "image/webp"

    ];

    if (!allowedTypes.includes(file.type)) {

        alert("Only JPG, JPEG, PNG and WEBP images are allowed.");

        this.value = "";

        return;

    }

    // 5 MB Limit

    if (file.size > 5 * 1024 * 1024) {

        alert("Maximum image size is 5 MB.");

        this.value = "";

        return;

    }

    // Preview

    const reader = new FileReader();

    reader.onload = function (e) {

        photoPreview.src = e.target.result;

        photoPreview.style.display = "block";

    };

    reader.readAsDataURL(file);

    // Selected Image

    document.getElementById("selectedPhoto").innerHTML =
        `<i class="fa-solid fa-circle-check"></i> ${file.name} selected successfully`;

});


// =========================================
// Blog Statistics
// =========================================

blogContent.addEventListener("input", function () {

    const text = this.value.trim();

    const characters = text.length;

    const words = text === "" ? 0 : text.split(/\s+/).length;

    wordCount.textContent = words;

    characterCount.textContent = characters;


    const status = document.getElementById("fileStatus");

    if (words > 0) {

        status.textContent = "✔ Ready for Publishing";

    }

    else {

        status.textContent = "Waiting for content";

    }

});


// =========================================
// Publish / Draft Validation
// =========================================

const draftBtn = document.querySelector(".draft-btn");
const blogUploadForm = document.getElementById("blogForm");
const statusField = document.getElementById("statusField");

function validateAndSubmit(isDraft) {

    const title = document.getElementById("blogTitle").value.trim();

    const category = document.getElementById("blogCategory").value;

    const content = blogContent.value.trim();

    const agree = document.getElementById("agree").checked;

    if (title === "") {

        alert("Please enter a blog title.");

        return;

    }

    if (category === "") {

        alert("Please select a category.");

        return;

    }

    if (content === "") {

        alert("Please write your blog.");

        return;

    }

    if (photoFile.files.length === 0) {

        alert("Please upload a cover image.");

        return;

    }

    if (!isDraft && !agree) {

        alert("Please agree to the community guidelines.");

        return;

    }

    statusField.value = isDraft ? "draft" : "published";
    blogUploadForm.submit();

}

publishBtn.addEventListener("click", function () {
    validateAndSubmit(false);
});

draftBtn.addEventListener("click", function () {
    validateAndSubmit(true);
});