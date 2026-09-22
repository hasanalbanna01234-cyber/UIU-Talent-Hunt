const slides = document.querySelectorAll(".slide");

let currentSlide = 0;

function showSlide(index){

    slides.forEach(slide=>{

        slide.classList.remove("active");

    });

    slides[index].classList.add("active");

}

setInterval(()=>{

    currentSlide++;

    if(currentSlide>=slides.length){

        currentSlide=0;

    }

    showSlide(currentSlide);

},4000);

// ===========================
// Profile Dropdown
// ===========================

const profile = document.getElementById("profile");
const profileMenu = document.getElementById("profilemenu");

profile.addEventListener("click", function (e) {

    e.stopPropagation();

    profileMenu.classList.toggle("active");
    profile.classList.toggle("active");

});

document.addEventListener("click", function () {

    profileMenu.classList.remove("active");
    profile.classList.remove("active");

});

// ===========================
// Notification Dropdown
// ===========================

const notification = document.getElementById("notification");
const notificationMenu = document.getElementById("notificationMenu");

notification.addEventListener("click", function (e) {

    e.stopPropagation();

    const opening = !notification.classList.contains("active");

    notification.classList.toggle("active");

    // Close profile menu if it is open
    profileMenu.classList.remove("active");
    profile.classList.remove("active");

    // Mark notifications as read the moment the dropdown is opened
    if (opening) {

        const badge = notification.querySelector(".notification-count");
        const unreadLabel = notification.querySelector(".notification-unread-label");

        if (badge) {
            fetch("actions/mark_notifications_read.php", { method: "POST" })
                .then(function (res) { return res.json(); })
                .then(function () {
                    badge.remove();
                    if (unreadLabel) unreadLabel.textContent = "All caught up";
                })
                .catch(function () {
                    // Silently ignore — worst case the badge stays until next page load
                });
        }
    }

});

document.addEventListener("click", function () {

    notification.classList.remove("active");

});