<?php
// ==========================================================
// Publish a draft post — owner-only, draft -> published
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
$post_id = (int)($_POST['post_id'] ?? 0);

if ($post_id > 0) {
    // The WHERE clause enforces ownership at the database level —
    // this can never publish (or even touch) another user's post,
    // and it never creates a new post; same post_id, same media rows.
    $stmt = $pdo->prepare(
        "UPDATE posts SET status = 'published'
         WHERE post_id = ? AND user_id = ? AND status = 'draft'"
    );
    $stmt->execute([$post_id, $user_id]);
}

header("Location: ../myprofile.php?published=1#draft");
exit;
