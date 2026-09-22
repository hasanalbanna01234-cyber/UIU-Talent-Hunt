<?php
// ==============================
// Login page + login handling
// ==============================
session_start();
require_once 'config/db.php';

// Already logged in? Skip straight to dashboard.
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$loginError = '';
$openLogin = false; // whether to auto-open the login popup on page load

// A redirect from session_check.php (expired/invalid session) or similar
// arrives as a GET param — show it the same way a failed login would.
if (!empty($_GET['error']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $loginError = $_GET['error'];
    $openLogin = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $openLogin = true;
    $student_id = trim($_POST['student_id'] ?? '');
    $password   = $_POST['password'] ?? '';

    if ($student_id === '' || $password === '') {
        $loginError = "Please enter Student ID and Password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['status'] !== 'active') {
                $loginError = "Your account is inactive. Please contact admin.";
            } else {
                $_SESSION['user_id']       = $user['user_id'];
                $_SESSION['full_name']     = $user['full_name'];
                $_SESSION['profile_image'] = $user['profile_image'];
                header("Location: dashboard.php");
                exit;
            }
        } else {
            $loginError = "Invalid Student ID or Password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UIU Talent Hunt | Login</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">


</head>
<body>
    
    <!-- Welcome page html start -->
    <div class="container">
        <div class="welcome">
            <img src="images/logo2.png" alt="UIU Talent Hunt logo" class="logo">
            <h2>Discover Hidden Talents of UIU</h2>
            <p>Showcase your skills, inspire others, and become one of the top talents of United International University</p>
            <a href="#" class="btn">Get Started
                <i class="fa-solid fa-arrow-right-long"></i>
            </a>
        </div>
    </div>
    <!-- Welcome page html end -->

    <!-- feature card html start -->
     <div class="feature-wrapper">
        <div class="feature-card">
            <div class="feature">
                <div class="icon orange">
                    <i class="fa-solid fa-trophy"></i>
                </div>
                <div class="text">
                    <h3>Showcase Your Talent</h3>
                    <p>Share your skills with the UIU community.</p>
                </div>
            </div>

            <div class="feature">
                <div class="icon navy">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="text">
                    <h3>Get Recognized</h3>
                    <p>Gain likes and valuable recognition.</p>
                </div>
            </div>

            <div class="feature">
                <div class="icon orange">
                    <i class="fa-regular fa-star"></i>
                </div>
                <div class="text">
                    <h3>Inspire Others</h3>
                    <p>Motivate and empower fellow talents.</p>
                </div>
            </div>

            <div class="feature">
                <div class="icon navy">
                    <i class="fa-solid fa-medal"></i>
                </div>
                <div class="text">
                    <h3>Climb the Leaderboard</h3>
                    <p>Compete and become a top talent.</p>
                </div>
            </div>
        </div>
     </div>
    <!-- feature card html end -->

    <!-- login part html start -->
    <div class="login_container<?php echo $openLogin ? ' show' : ''; ?>" id="login">
        <div class="login_card">
            <button class="close_btn" id="closebtn">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <img src="images/logo.png" alt="UIU Talent Hunt Logo" class="login_logo">
            <h2>Join UIU Talents</h2>
            <p>Login to continue your journey with us</p>

            <?php if ($loginError): ?>
                <p style="color:#e74c3c; text-align:center; margin:-10px 0 15px;"><?php echo htmlspecialchars($loginError); ?></p>
            <?php endif; ?>

            <form method="post" action="index.php">
                <div class="input-box">
                    <i class="fa-solid fa-id-card"></i>
                    <input type="text" name="student_id" id="userID" placeholder="Student ID" required>
                </div>

                <div class="input-box">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" id="password" placeholder="Password" required>
                    <i class="fa-solid fa-eye-slash eye" id="togglePassword"></i>
                </div>

                <a href="#" class="forgot">Forgot Password?</a>

                <button class="loginbtn" type="submit" name="login_submit" value="1" id="loginBtn">Login</button>
            </form>

            <p class="register-txt">
                New to Hunt?
                <a href="signup.php">Create an account</a>
            </p>
        </div>
    </div>

<script src="js/index.js"></script>
</body>
</html>