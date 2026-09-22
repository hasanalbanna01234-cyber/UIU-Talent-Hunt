<?php
// ==========================================================
// Toggle Like (AJAX) — returns JSON { liked, count }
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

// Can't like your own post
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
    echo json_encode(['error' => "You can't like your own post."]);
    exit;
}

// Does a like already exist from this user on this post?
$stmt = $pdo->prepare("SELECT like_id FROM likes WHERE post_id = ? AND user_id = ?");
$stmt->execute([$post_id, $user_id]);
$existing = $stmt->fetch();

if ($existing) {
    $del = $pdo->prepare("DELETE FROM likes WHERE like_id = ?");
    $del->execute([$existing['like_id']]);
    $liked = false;
} else {
    $ins = $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
    $ins->execute([$post_id, $user_id]);
    $liked = true;
}

$count = $pdo->prepare("SELECT COUNT(*) AS c FROM likes WHERE post_id = ?");
$count->execute([$post_id]);
$total = $count->fetch()['c'];

echo json_encode(['liked' => $liked, 'count' => (int)$total]);
