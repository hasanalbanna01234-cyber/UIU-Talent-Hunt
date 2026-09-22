/* =========================================
   UIU TALENT HUNT - MY PROFILE JS
========================================= */

/* Note: the navbar profile dropdown (click to open/close the
   Settings/Logout menu) is already wired up by js/dashboard.js,
   which this page also loads — no need to duplicate it here. */


/* =========================================
   PUBLISHED / DRAFT TABS
========================================= */

const postTabs = document.querySelectorAll(".post-tab");
const publishedGrid = document.getElementById("publishedPostsGrid");
const draftGrid = document.getElementById("draftPostsGrid");

postTabs.forEach(function (tab) {
    tab.addEventListener("click", function () {
        postTabs.forEach(function (t) { t.classList.remove("active"); });
        tab.classList.add("active");

        const target = tab.getAttribute("data-tab");

        if (target === "draft") {
            publishedGrid.style.display = "none";
            draftGrid.style.display = "grid";
        } else {
            publishedGrid.style.display = "grid";
            draftGrid.style.display = "none";
        }
    });
});

// If we just published a draft (redirected back with #draft), land on the Draft tab
if (window.location.hash === "#draft") {
    const draftTab = document.querySelector('.post-tab[data-tab="draft"]');
    if (draftTab) draftTab.click();
}


/* =========================================
   EDIT PROFILE MODAL
========================================= */

const editProfileBtn = document.getElementById("editProfileBtn");
const editProfileOverlay = document.getElementById("editProfileOverlay");
const editProfileClose = document.getElementById("editProfileClose");

if (editProfileBtn && editProfileOverlay) {

    editProfileBtn.addEventListener("click", function () {
        editProfileOverlay.classList.add("active");
    });

    editProfileClose.addEventListener("click", function () {
        editProfileOverlay.classList.remove("active");
    });

    editProfileOverlay.addEventListener("click", function (e) {
        if (e.target === editProfileOverlay) {
            editProfileOverlay.classList.remove("active");
        }
    });

    // Live preview of the chosen photo before it's uploaded
    const profilePhotoInput = document.getElementById("profilePhotoInput");
    const profilePhotoPreview = document.getElementById("profilePhotoPreview");
    const coverPhotoInput = document.getElementById("coverPhotoInput");
    const coverPhotoPreview = document.getElementById("coverPhotoPreview");

    function previewFile(input, imgEl) {
        input.addEventListener("change", function () {
            const file = input.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function (e) {
                imgEl.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    if (profilePhotoInput && profilePhotoPreview) previewFile(profilePhotoInput, profilePhotoPreview);
    if (coverPhotoInput && coverPhotoPreview) previewFile(coverPhotoInput, coverPhotoPreview);
}


/* =========================================
   SHARE PROFILE BUTTON (copies the profile link — separate
   from the per-post share-btn handled in feed_actions.js)
========================================= */

const shareProfileButton = document.querySelector(".share-profile-btn");

if (shareProfileButton) {

    shareProfileButton.addEventListener("click", function () {

        const profileUrl = window.location.href;

        if (navigator.clipboard) {

            navigator.clipboard.writeText(profileUrl)
                .then(function () {
                    const toast = document.getElementById("inlineToast");
                    if (toast) {
                        toast.textContent = "Profile link copied!";
                        toast.classList.add("show");
                        setTimeout(function () { toast.classList.remove("show"); }, 2500);
                    }
                })
                .catch(function () {});

        }

    });

}
