<?php
// ==========================================================
// Add Comment (AJAX) — returns JSON { count, comment }
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

$user_id      = $_SESSION['user_id'];
$post_id      = (int)($_POST['post_id'] ?? 0);
$comment_text = trim($_POST['comment_text'] ?? '');

if ($post_id <= 0 || $comment_text === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid comment']);
    exit;
}

// Make sure the post actually exists and is visible (published, or the
// commenter's own draft) before inserting — never trust the post_id
// coming from the client without checking it against the database.
$postCheck = $pdo->prepare("SELECT user_id, status FROM posts WHERE post_id = ?");
$postCheck->execute([$post_id]);
$post = $postCheck->fetch();

if (!$post) {
    http_response_code(404);
    echo json_encode(['error' => 'Post not found']);
    exit;
}
if ($post['status'] !== 'published' && (int)$post['user_id'] !== (int)$user_id) {
    http_response_code(403);
    echo json_encode(['error' => 'You cannot comment on this post']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, comment_text) VALUES (?, ?, ?)");
$stmt->execute([$post_id, $user_id, $comment_text]);

$count = $pdo->prepare("SELECT COUNT(*) AS c FROM comments WHERE post_id = ? AND status = 'active'");
$count->execute([$post_id]);
$total = $count->fetch()['c'];

$nameStmt = $pdo->prepare("SELECT full_name FROM users WHERE user_id = ?");
$nameStmt->execute([$user_id]);
$fullName = $nameStmt->fetch()['full_name'] ?? 'User';

echo json_encode([
    'count' => (int)$total,
    'comment' => [
        'full_name' => $fullName,
        'comment_text' => $comment_text,
    ],
]);
