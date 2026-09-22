<?php
// ==========================================================
// Record a Share (AJAX) — returns JSON { count }
// ==========================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$post_id = (int)($_POST['post_id'] ?? 0);

if ($post_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid post']);
    exit;
}

// Can't share your own post
$ownerStmt = $pdo->prepare("SELECT user_id FROM posts WHERE post_id = ?");
$ownerStmt->execute([$post_id]);
$post = $ownerStmt->fetch();

if (!$post) {
    http_response_code(404);
    echo json_encode(['error' => 'Post not found']);
    exit;
}
if ((int)$post['user_id'] === (int)$user_id) {
    http_response_code(403);
    echo json_encode(['error' => "You can't share your own post."]);
    exit;
}

// Only record one share per user per post, no matter how many times
// they open the share menu or which destination they pick.
$existingStmt = $pdo->prepare("SELECT share_id FROM shares WHERE post_id = ? AND user_id = ?");
$existingStmt->execute([$post_id, $user_id]);
$alreadyShared = (bool)$existingStmt->fetch();

if (!$alreadyShared) {
    $stmt = $pdo->prepare("INSERT INTO shares (post_id, user_id) VALUES (?, ?)");
    $stmt->execute([$post_id, $user_id]);
}

$count = $pdo->prepare("SELECT COUNT(*) AS c FROM shares WHERE post_id = ?");
$count->execute([$post_id]);
$total = $count->fetch()['c'];

echo json_encode(['count' => (int)$total, 'already_shared' => $alreadyShared]);
