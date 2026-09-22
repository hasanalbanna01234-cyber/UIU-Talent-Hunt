<?php
// ==========================================================
// Get Comments for a post (AJAX) — returns JSON { comments: [...] }
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

$post_id = (int)($_GET['post_id'] ?? 0);

if ($post_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid post']);
    exit;
}

// Only the post's own comments are ever returned, and only if the post
// is published or belongs to the requesting user (their own draft).
$postCheck = $pdo->prepare("SELECT user_id, status FROM posts WHERE post_id = ?");
$postCheck->execute([$post_id]);
$post = $postCheck->fetch();

if (!$post) {
    http_response_code(404);
    echo json_encode(['error' => 'Post not found']);
    exit;
}
if ($post['status'] !== 'published' && (int)$post['user_id'] !== (int)$_SESSION['user_id']) {
    http_response_code(403);
    echo json_encode(['error' => 'You cannot view comments on this post']);
    exit;
}

$stmt = $pdo->prepare(
    "SELECT c.comment_text, c.created_at, u.full_name, u.profile_image
     FROM comments c
     JOIN users u ON u.user_id = c.user_id
     WHERE c.post_id = ? AND c.status = 'active'
     ORDER BY c.created_at ASC"
);
$stmt->execute([$post_id]);
$rows = $stmt->fetchAll();

function comment_time_ago($datetime)
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hr ago';
    if ($diff < 604800) return floor($diff / 86400) . ' day(s) ago';
    return date('M j, Y', strtotime($datetime));
}

$comments = array_map(function ($row) {
    return [
        'full_name'    => $row['full_name'],
        'profile_image' => $row['profile_image'] ? 'uploads/profiles/' . $row['profile_image'] : 'images/p4.jpeg',
        'comment_text' => $row['comment_text'],
        'time_ago'     => comment_time_ago($row['created_at']),
    ];
}, $rows);

echo json_encode(['comments' => $comments]);
