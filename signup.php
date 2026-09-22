<?php
// ==============================
// Signup page + registration handling
// ==============================
session_start();
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$errors = [];
$old = ['full_name' => '', 'student_id' => '', 'email' => '', 'department' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['full_name']  = trim($_POST['full_name'] ?? '');
    $old['student_id'] = trim($_POST['student_id'] ?? '');
    $old['email']      = trim($_POST['email'] ?? '');
    $old['department'] = trim($_POST['department'] ?? '');
    $password          = $_POST['password'] ?? '';
    $confirm_password  = $_POST['confirm_password'] ?? '';

    if ($old['full_name'] === '' || $old['student_id'] === '' || $old['department'] === '' || $password === '') {
        $errors[] = "Please fill in all required fields.";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        // Look up the department by its code (CSE, EEE, etc.)
        $stmt = $pdo->prepare("SELECT department_id FROM departments WHERE department_code = ?");
        $stmt->execute([$old['department']]);
        $dept = $stmt->fetch();

        if (!$dept) {
            $errors[] = "Please select a valid department.";
        } else {
            // Check for an existing account with the same Student ID or email
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE student_id = ? OR (email <> '' AND email = ?)");
            $stmt->execute([$old['student_id'], $old['email']]);

            if ($stmt->fetch()) {
                $errors[] = "An account with this Student ID or Email already exists.";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare(
                    "INSERT INTO users (student_id, full_name, email, department_id, password_hash)
                     VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $old['student_id'],
                    $old['full_name'],
                    $old['email'],
                    $dept['department_id'],
                    $password_hash,
                ]);

                $_SESSION['user_id']   = $pdo->lastInsertId();
                $_SESSION['full_name'] = $old['full_name'];

                header("Location: dashboard.php");
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UIU Talent Hunt | Sign up</title>
    <link rel="stylesheet" href="css/signup.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="signup_form">
            <!-- logo -->
            <img src="images/logo.png" alt="UIU Talent Hunt Logo" class="signup_logo"> 
            <h2>Create Your Account</h2>
            <p>Join UIU Talent Hunt</p>

            <?php if (!empty($errors)): ?>
                <div style="color:#e74c3c; text-align:center; margin-bottom:10px;">
                    <?php foreach ($errors as $err) echo htmlspecialchars($err) . "<br>"; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="signup.php">
                <div class="input-group">
                    <label>Name</label><input type="text" name="full_name" value="<?php echo htmlspecialchars($old['full_name']); ?>" placeholder="Input Your Name" required>
                </div>

                <div class="input-group">
                    <label>ID</label><input type="text" name="student_id" value="<?php echo htmlspecialchars($old['student_id']); ?>" placeholder="011231234" required>
                </div>

                <div class="input-group">
                    <label>UIU Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($old['email']); ?>" placeholder="example@bscse.uiu.ac.bd" pattern=".+bscse\.uiu\.ac\.bd$">
                </div>

                <div class="input-group">
                    <label>Department</label>
                    <select name="department" required>
                        <option value="" hidden>Select Department</option>
                        <option value="CSE" <?php if ($old['department'] === 'CSE') echo 'selected'; ?>>CSE</option>
                        <option value="EEE" <?php if ($old['department'] === 'EEE') echo 'selected'; ?>>EEE</option>
                        <option value="CE" <?php if ($old['department'] === 'CE') echo 'selected'; ?>>CE</option>
                        <option value="ENGLISH" <?php if ($old['department'] === 'ENGLISH') echo 'selected'; ?>>English</option>
                        <option value="BBA" <?php if ($old['department'] === 'BBA') echo 'selected'; ?>>BBA</option>
                        <option value="MEDIA" <?php if ($old['department'] === 'MEDIA') echo 'selected'; ?>>Media</option>
                        <option value="PHARMACY" <?php if ($old['department'] === 'PHARMACY') echo 'selected'; ?>>Pharmacy</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>Password</label><input type="password" name="password" minlength="8" placeholder="Minimum 8 characters" required>

                </div>

                <div class="input-group">
                    <label>Confirm Password</label><input type="password" name="confirm_password" minlength="8" placeholder="Re-enter password" required><br><br>

                </div>

                <div class="button-group">
                    <button class="register-btn" type="submit">Register</button>
                    <button type="reset" class="cancel-btn" id="cancelbtn">Cancel</button>
                </div>
        </form>
        </div>
    </div>
<script src="js/signup.js"></script>
</body>
</html>