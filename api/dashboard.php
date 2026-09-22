<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please log in to view the dashboard.']);
    exit;
}

require_once __DIR__ . '/../config/db.php';
$userId = (int) $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT p.post_id, p.title, p.description, p.talent_type, p.created_at,
    u.full_name AS author_name,
    (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.post_id) AS like_count,
    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id AND c.status = 'active') AS comment_count,
    (SELECT COUNT(*) FROM shares s WHERE s.post_id = p.post_id) AS share_count
    FROM posts p JOIN users u ON u.user_id = p.user_id
    WHERE p.status = 'published' ORDER BY p.created_at DESC LIMIT 20");
$stmt->execute();
echo json_encode(['posts' => $stmt->fetchAll()]);