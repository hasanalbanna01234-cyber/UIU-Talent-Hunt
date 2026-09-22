<?php
// ==========================================================
// Admin Login
// ==========================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['is_admin'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Admin credentials are intentionally fixed, as specified — there is
    // no separate admin table, and this is never mixed with the student
    // login/session system in index.php / includes/session_check.php.
    if ($username === 'admin' && $password === '1234') {
        $_SESSION['is_admin'] = true;
        header("Location: index.php");
        exit;
    }

    $error = 'Invalid admin username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | UIU Talent Hunt</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css?v=20260920">
</head>
<body>
    <div class="admin-login-wrap">
        <div class="admin-login-card">
            <img src="../images/logo.png" alt="UIU Talent Hunt">
            <h1>Admin Panel</h1>
            <p>UIU Talent Hunt — Administration</p>

            <?php if ($error): ?>
                <div class="admin-login-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="post" action="login.php">
                <div class="admin-input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="admin" required autofocus>
                </div>
                <div class="admin-input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••" required>
                </div>
                <button type="submit" class="admin-login-btn">Log In</button>
            </form>
        </div>
    </div>
</body>
</html>
