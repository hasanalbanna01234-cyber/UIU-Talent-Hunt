const videoCard = document.getElementById("videoCard");
const audioCard = document.getElementById("audioCard");
const photoCard = document.getElementById("photoCard");
const blogCard = document.getElementById("blogCard");

videoCard.onclick = function () {
    window.location.href = "video_form.php";
};

audioCard.onclick = function () {
    window.location.href = "audio_form.php";
};

photoCard.onclick = function () {
    window.location.href = "photo_form.php";
};

blogCard.onclick = function () {
    window.location.href = "blog_form.php";
};