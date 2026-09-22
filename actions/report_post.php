<?php
// ==========================================================
// Report a Post (AJAX) — returns JSON
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
$reason  = trim($_POST['reason'] ?? '');

$allowedReasons = ['Spam', 'Inappropriate Content', 'Harassment', 'Copyright Issue', 'Other'];

if ($post_id <= 0 || !in_array($reason, $allowedReasons, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid report']);
    exit;
}

// Never trust the post_id from the client: confirm it exists, is
// actually visible (published), and isn't the reporter's own post.
$postCheck = $pdo->prepare("SELECT user_id, status FROM posts WHERE post_id = ?");
$postCheck->execute([$post_id]);
$post = $postCheck->fetch();

if (!$post) {
    http_response_code(404);
    echo json_encode(['error' => 'Post not found']);
    exit;
}
if ($post['status'] !== 'published') {
    http_response_code(403);
    echo json_encode(['error' => 'You cannot report this post']);
    exit;
}
if ((int)$post['user_id'] === (int)$user_id) {
    http_response_code(403);
    echo json_encode(['error' => "You can't report your own post"]);
    exit;
}

// One report per student per post — check first, and the UNIQUE
// key on (post_id, user_id) backs this up at the database level too.
$existingStmt = $pdo->prepare("SELECT report_id FROM reports WHERE post_id = ? AND user_id = ?");
$existingStmt->execute([$post_id, $user_id]);

if ($existingStmt->fetch()) {
    echo json_encode(['success' => true, 'already_reported' => true]);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO reports (post_id, user_id, reason) VALUES (?, ?, ?)"
    );
    $stmt->execute([$post_id, $user_id, $reason]);
} catch (PDOException $e) {
    // Race condition: another request for the same post/user landed first
    // and the UNIQUE key rejected this one — treat it the same as already
    // having reported, rather than surfacing a raw DB error.
    if ($e->getCode() === '23000') {
        echo json_encode(['success' => true, 'already_reported' => true]);
        exit;
    }
    http_response_code(500);
    echo json_encode(['error' => 'Could not submit report right now']);
    exit;
}

echo json_encode(['success' => true, 'already_reported' => false]);
