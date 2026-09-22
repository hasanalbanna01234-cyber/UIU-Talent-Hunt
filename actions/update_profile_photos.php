<?php
// ==========================================================
// Update Profile Photo / Cover Photo — self-service only
// ==========================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];

function save_profile_image($fieldName)
{
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // user didn't choose a new file — leave the existing one alone
    }
    if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Upload failed. Please try again.'];
    }

    $tmpPath  = $_FILES[$fieldName]['tmp_name'];
    $origName = $_FILES[$fieldName]['name'];
    $size     = $_FILES[$fieldName]['size'];
    $mime     = mime_content_type($tmpPath);
    $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

    $allowedExt  = ['jpg', 'jpeg', 'png', 'webp'];
    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
    $maxBytes    = 5 * 1024 * 1024; // 5MB

    if (!in_array($ext, $allowedExt) || !in_array($mime, $allowedMime)) {
        return ['error' => 'Please upload a JPG, PNG, or WEBP image.'];
    }
    if ($size > $maxBytes) {
        return ['error' => 'Image is too large (max 5MB).'];
    }

    $safeName = uniqid('profile_', true) . '.' . $ext;
    $destDir  = __DIR__ . '/../uploads/profiles/';
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }
    $destPath = $destDir . $safeName;

    if (!move_uploaded_file($tmpPath, $destPath)) {
        return ['error' => 'Could not save uploaded image.'];
    }

    return ['filename' => $safeName];
}

$profileResult = save_profile_image('profile_photo');
if ($profileResult && isset($profileResult['error'])) {
    header("Location: ../myprofile.php?error=" . urlencode($profileResult['error']));
    exit;
}

$coverResult = save_profile_image('cover_photo');
if ($coverResult && isset($coverResult['error'])) {
    header("Location: ../myprofile.php?error=" . urlencode($coverResult['error']));
    exit;
}

// Only update the fields the user actually chose a new file for —
// always scoped to the logged-in user's own row via the session ID.
if ($profileResult && $coverResult) {
    $stmt = $pdo->prepare("UPDATE users SET profile_image = ?, cover_image = ? WHERE user_id = ?");
    $stmt->execute([$profileResult['filename'], $coverResult['filename'], $user_id]);
} elseif ($profileResult) {
    $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE user_id = ?");
    $stmt->execute([$profileResult['filename'], $user_id]);
} elseif ($coverResult) {
    $stmt = $pdo->prepare("UPDATE users SET cover_image = ? WHERE user_id = ?");
    $stmt->execute([$coverResult['filename'], $user_id]);
}

header("Location: ../myprofile.php?profile_updated=1");
exit;
