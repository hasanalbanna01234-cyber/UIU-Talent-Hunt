// ==============================
// Get Elements
// ==============================
const getStartedBtn = document.querySelector(".btn");
const loginContainer = document.getElementById("login");
const closeBtn = document.getElementById("closebtn");

const userID = document.getElementById("userID");
const password = document.getElementById("password");
const togglePassword = document.getElementById("togglePassword");
const loginBtn = document.getElementById("loginBtn");


// ==============================
// Reset Login Form
// ==============================
function resetLoginForm() {

    userID.value = "";
    password.value = "";

    userID.placeholder = "Student ID";
    password.placeholder = "Password";

    password.type = "password";

    togglePassword.classList.remove("fa-eye");
    togglePassword.classList.add("fa-eye-slash");
}


// ==============================
// Open Login Popup
// ==============================
getStartedBtn.addEventListener("click", function (e) {

    e.preventDefault();

    resetLoginForm();

    loginContainer.classList.add("show");

    // Prevent background scrolling
    document.body.style.overflow = "hidden";

});


// ==============================
// Close Popup
// ==============================
closeBtn.addEventListener("click", function () {

    loginContainer.classList.remove("show");

    document.body.style.overflow = "auto";

    resetLoginForm();

});


// ==============================
// Close Popup by Clicking Outside
// ==============================
loginContainer.addEventListener("click", function (e) {

    if (e.target === loginContainer) {

        loginContainer.classList.remove("show");

        document.body.style.overflow = "auto";

        resetLoginForm();

    }

});


// ==============================
// Password Show / Hide
// ==============================
togglePassword.addEventListener("click", function () {

    if (password.type === "password") {

        password.type = "text";

        togglePassword.classList.remove("fa-eye-slash");
        togglePassword.classList.add("fa-eye");

    } 
    else {

        password.type = "password";

        togglePassword.classList.remove("fa-eye");
        togglePassword.classList.add("fa-eye-slash");

    }

});


// ==============================
// Login Button
// ==============================
// The login form now submits to index.php for real, so no JS redirect
// is needed here anymore — PHP handles validation and redirects on success.